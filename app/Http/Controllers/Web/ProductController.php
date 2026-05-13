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
        $query = Product::query()->with(['category', 'brand', 'taxRate', 'hsnCode']);

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
            'out_of_stock' => Product::where('status', 'out_of_stock')->count(),
            'low_stock' => Product::whereRaw('min_stock_level > (select sum(quantity) from stocks where product_id = products.id)')->count(),
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
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['allow_overselling'] = $request->has('allow_overselling');
        $data['manage_stock'] = $request->has('manage_stock');
        $data['batch_tracking'] = $request->has('batch_tracking');
        $data['expiry_tracking'] = $request->has('expiry_tracking');
        $data['overselling_qty'] = $request->input('overselling_qty', 0);
        $data['min_stock_level'] = $request->input('min_stock_level', 0);
        
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
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['allow_overselling'] = $request->has('allow_overselling');
        $data['manage_stock'] = $request->has('manage_stock');
        $data['batch_tracking'] = $request->has('batch_tracking');
        $data['expiry_tracking'] = $request->has('expiry_tracking');
        $data['overselling_qty'] = $request->input('overselling_qty', 0);
        $data['min_stock_level'] = $request->input('min_stock_level', 0);

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
