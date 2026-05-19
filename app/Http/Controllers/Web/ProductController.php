<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Warehouse;
use App\Models\UnitOfMeasure;
use App\Models\TaxRate;
use App\Models\HsnCode;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['category', 'brand', 'taxRate', 'hsnCode'])
            ->withSum('stocks as total_stock', 'quantity')
            ->withSum('stocks as total_reserved', 'reserved_qty')
            ->withSum('stocks as total_dispatched', 'dispatched_qty');

        if ($request->input('filter') === 'trashed') {
            $query->onlyTrashed();
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%")
                  ->orWhere('barcode', 'like', "%$s%");
            });
        }

        if ($request->filled('status')) {
            $statuses = array_filter(array_map('trim', explode(',', $request->status)));
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('category')) {
            $categories = array_filter(array_map('trim', explode(',', $request->category)));
            $query->whereHas('category', function($q) use ($categories) {
                $q->whereIn('slug', $categories);
            });
        }

        $query->latest();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'out_of_stock' => Product::whereRaw('IFNULL((select sum(quantity - reserved_qty) from stocks where product_id = products.id), 0) <= 0')->count(),
            'low_stock' => Product::whereRaw('IFNULL((select sum(quantity - reserved_qty) from stocks where product_id = products.id), 0) <= min_stock_level')->whereRaw('IFNULL((select sum(quantity - reserved_qty) from stocks where product_id = products.id), 0) > 0')->count(),
        ];

        // dynamic lists
        $categoriesList = Category::whereNull('parent_id')->get()->map(function($c) {
            return ['slug' => $c->slug, 'name' => $c->name];
        });
        
        $statusList = [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'out_of_stock', 'label' => 'Out of Stock'],
        ];

        $perPage = (int) $request->input('perPage', 10);
        $products = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('products.partials.table', compact('products'))->render(),
                'categoriesList' => $categoriesList,
                'statusList' => $statusList,
                'stats' => $stats
            ]);
        }

        return view('products.index', compact('products', 'stats', 'categoriesList', 'statusList'));
    }

    public function searchApi(Request $request)
    {
        $query = Product::with(['category', 'brand', 'taxRate'])
            ->withSum('stocks', 'quantity')
            ->withSum('stocks', 'reserved_qty')
            ->withSum('stocks', 'dispatched_qty')
            ->withSum(['orderItems as pending_orders_qty' => function ($q) use ($request) {
                $q->whereHas('order', function ($o) use ($request) {
                    $o->where('status', 'pending');
                    if ($request->filled('exclude_order_id')) {
                        $o->where('id', '!=', $request->exclude_order_id);
                    }
                });
            }], 'quantity');

        // Status filter — only active by default, unless explicitly asking for all
        $statusFilter = $request->input('status', 'active');
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        // SKU Enabled filter - only enabled by default
        if ($request->input('include_disabled') !== 'true') {
            $query->where('is_sku_enabled', true);
        }

        // Text search
        if ($request->filled('q')) {
            $s = $request->q;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%")
                  ->orWhere('barcode', 'like', "%$s%");
            });
        }

        // Stock availability filter (uses true available = qty - reserved - pending)
        $stockFilter = $request->input('stock', '');
        $excludeSql = "";
        if ($request->filled('exclude_order_id')) {
            $excId = (int) $request->exclude_order_id;
            $excludeSql = " AND orders.id != " . $excId;
        }

        if ($stockFilter === 'available') {
            $query->where(function ($q) use ($excludeSql) {
                $q->whereRaw('(IFNULL((SELECT SUM(quantity - reserved_qty) FROM stocks WHERE stocks.product_id = products.id), 0) - IFNULL((SELECT SUM(quantity) FROM order_items JOIN orders ON orders.id = order_items.order_id WHERE order_items.product_id = products.id AND orders.status = \'pending\' AND orders.deleted_at IS NULL' . $excludeSql . '), 0)) > 0')
                  ->orWhere('allow_overselling', true);
            });
        } elseif ($stockFilter === 'out_of_stock') {
            $query->where(function ($q) use ($excludeSql) {
                $q->whereRaw('(IFNULL((SELECT SUM(quantity - reserved_qty) FROM stocks WHERE stocks.product_id = products.id), 0) - IFNULL((SELECT SUM(quantity) FROM order_items JOIN orders ON orders.id = order_items.order_id WHERE order_items.product_id = products.id AND orders.status = \'pending\' AND orders.deleted_at IS NULL' . $excludeSql . '), 0)) <= 0')
                  ->where('allow_overselling', false);
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $perPage    = min((int) $request->input('perPage', 15), 100);
        $paginator  = $query->latest()->paginate($perPage)->withQueryString();

        $data = $paginator->through(function ($p) {
            $totalQty      = (float) ($p->stocks_sum_quantity ?? 0);
            $reservedQty   = (float) ($p->stocks_sum_reserved_qty ?? 0);
            $dispatchedQty = (float) ($p->stocks_sum_dispatched_qty ?? 0);
            $pendingQty    = (float) ($p->pending_orders_qty ?? 0);

            // TRUE raw remaining = on-hand minus what is already reserved for confirmed orders and pending orders
            $rawAvailable  = $totalQty - $reservedQty - $pendingQty;
            $netAvailable  = max(0.0, $rawAvailable);

            // When overselling is allowed, any physical deficit ($rawAvailable < 0) correctly deducts from overselling_qty limit
            if ($p->allow_overselling) {
                $maxAllowedQty = max(0.0, $rawAvailable + (float) ($p->overselling_qty ?: 999));
            } else {
                $maxAllowedQty = $netAvailable;
            }

            return [
                'id'               => $p->id,
                'name'             => $p->name,
                'sku'              => $p->sku,
                'barcode'          => $p->barcode,
                'selling_price'    => $p->selling_price,
                'purchase_price'   => $p->purchase_price,
                'mrp'              => $p->mrp,
                'image_url'        => $p->image_path ? asset('storage/' . $p->image_path) : null,
                'stock_qty'        => $totalQty,        // total physical on-hand
                'reserved_qty'     => $reservedQty,     // held for confirmed orders
                'pending_qty'      => $pendingQty,      // held for pending orders
                'dispatched_qty'   => $dispatchedQty,   // already shipped (running total)
                'available_stock'  => $maxAllowedQty,   // what can be sold NOW (ceiling)
                'physical_available' => $netAvailable,  // true physical remaining
                'overselling_qty'  => (int) ($p->overselling_qty ?? 0),
                'allow_overselling'  => (bool) $p->allow_overselling,
                'status'           => $p->status,
                'category'         => $p->category?->name,
                'category_id'      => $p->category_id,
                'brand'            => $p->brand?->name,
                'tax_rate'         => $p->taxRate?->rate,
                'tax_label'        => $p->taxRate?->name,
                'min_stock_level'  => $p->min_stock_level ?? 0,
                'weight'           => $p->weight,
                'is_sku_enabled'   => (bool) $p->is_sku_enabled,
                'default_discount' => (float) ($p->default_discount ?? 0), // pre-set product discount %
                'default_discount_type' => $p->default_discount_type ?? 'percent', // 'percent' or 'flat'
            ];
        });

        return response()->json([
            'data'         => $data->items(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'from'         => $paginator->firstItem(),
            'to'           => $paginator->lastItem(),
        ]);
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $brands = Brand::all();
        $warehouses = Warehouse::all();
        $uoms = UnitOfMeasure::all();
        $taxRates = TaxRate::where('status', 'active')->get();
        $hsnCodes = HsnCode::where('status', 'active')->get();
        $attributes = ProductAttribute::where('status', 'active')->with('values')->get();
        
        return view('products.create', compact('categories', 'brands', 'warehouses', 'uoms', 'taxRates', 'hsnCodes', 'attributes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'barcode' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'uom_id' => 'nullable|exists:units_of_measure,id',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'hsn_code_id' => 'nullable|exists:hsn_codes,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'status' => 'required|string',
            'allow_overselling' => 'boolean',
            'manage_stock' => 'boolean',
            'batch_tracking' => 'boolean',
            'expiry_tracking' => 'boolean',
            'application_instructions' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'attributes' => 'nullable|array',
            'overselling_qty' => 'required_if:allow_overselling,1|nullable|integer|min:0',
            'weight' => 'nullable|string|max:255',
            'is_sku_enabled' => 'nullable|boolean',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'default_discount_type' => 'nullable|in:percent,flat',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['allow_overselling'] = $request->has('allow_overselling');
        $data['manage_stock'] = $request->has('manage_stock');
        $data['batch_tracking'] = $request->has('batch_tracking');
        $data['expiry_tracking'] = $request->has('expiry_tracking');
        $data['overselling_qty'] = $request->input('overselling_qty', 0);
        $data['min_stock_level'] = $request->input('min_stock_level', 0);
        $data['is_sku_enabled'] = $request->has('is_sku_enabled');
        
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        if ($request->filled('attributes')) {
            $product->attributeValues()->sync($request->input('attributes'));
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'taxRate', 'hsnCode', 'stocks.warehouse', 'attributeValues.attribute']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $brands = Brand::all();
        $warehouses = Warehouse::all();
        $uoms = UnitOfMeasure::all();
        $taxRates = TaxRate::where('status', 'active')->get();
        $hsnCodes = HsnCode::where('status', 'active')->get();
        $attributes = ProductAttribute::where('status', 'active')->with('values')->get();
        $selectedAttributes = $product->attributeValues->pluck('id')->toArray();

        return view('products.edit', compact('product', 'categories', 'brands', 'warehouses', 'uoms', 'taxRates', 'hsnCodes', 'attributes', 'selectedAttributes'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'uom_id' => 'nullable|exists:units_of_measure,id',
            'default_warehouse_id' => 'nullable|exists:warehouses,id',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'hsn_code_id' => 'nullable|exists:hsn_codes,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'status' => 'required|string',
            'allow_overselling' => 'boolean',
            'manage_stock' => 'boolean',
            'batch_tracking' => 'boolean',
            'expiry_tracking' => 'boolean',
            'application_instructions' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'attributes' => 'nullable|array',
            'overselling_qty' => 'required_if:allow_overselling,1|nullable|integer|min:0',
            'weight' => 'nullable|string|max:255',
            'is_sku_enabled' => 'nullable|boolean',
            'default_discount' => 'nullable|numeric|min:0|max:100',
            'default_discount_type' => 'nullable|in:percent,flat',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['allow_overselling'] = $request->has('allow_overselling');
        $data['manage_stock'] = $request->has('manage_stock');
        $data['batch_tracking'] = $request->has('batch_tracking');
        $data['expiry_tracking'] = $request->has('expiry_tracking');
        $data['overselling_qty'] = $request->input('overselling_qty', 0);
        $data['min_stock_level'] = $request->input('min_stock_level', 0);
        $data['is_sku_enabled'] = $request->has('is_sku_enabled');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        if ($request->has('attributes')) {
            $product->attributeValues()->sync($request->input('attributes'));
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product moved to trash.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (is_array($ids) && count($ids) > 0) {
            Product::whereIn('id', $ids)->delete();
        }
        return back()->with('success', 'Selected products deleted.');
    }

    public function restore(string $id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        return back()->with('success', 'Product restored successfully.');
    }

    public function forceDelete(string $id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->forceDelete();

        return back()->with('success', 'Product permanently deleted.');
    }
}
