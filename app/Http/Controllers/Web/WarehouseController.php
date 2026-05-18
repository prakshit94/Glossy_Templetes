<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Warehouse::with(['village'])->withCount('stocks');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('code', 'like', "%$s%")
                  ->orWhere('address', 'like', "%$s%")
                  ->orWhere('address_line_1', 'like', "%$s%")
                  ->orWhere('village_name', 'like', "%$s%")
                  ->orWhere('city', 'like', "%$s%")
                  ->orWhere('pincode', 'like', "%$s%")
                  ->orWhere('state', 'like', "%$s%");
            });
        }

        $warehouses = $query->paginate(15)->withQueryString();
        
        $stats = [
            'total' => Warehouse::count(),
            'active' => Warehouse::where('status', 'active')->count(),
            'inactive' => Warehouse::where('status', 'inactive')->count(),
            'total_stock_value' => 0, // Placeholder for future logic
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('inventory.warehouses.partials.table', compact('warehouses'))->render(),
                'stats' => $stats
            ]);
        }

        return view('inventory.warehouses.index', compact('warehouses', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventory.warehouses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'address' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'village_id' => 'nullable|exists:villages,id',
            'village_name' => 'nullable|string|max:255',
            'post_office' => 'nullable|string|max:255',
            'taluka' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',
            'is_default' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_default'] = $request->has('is_default');
        
        if (empty($data['address']) && !empty($data['address_line_1'])) {
            $data['address'] = $data['address_line_1'] . (!empty($data['city']) ? ', ' . $data['city'] : '');
        }

        if ($data['is_default']) {
            Warehouse::query()->update(['is_default' => false]);
        }

        Warehouse::create($data);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $warehouse = Warehouse::with(['village'])->findOrFail($id);

        $perPage = (int) $request->input('perPage', 15);
        if (!in_array($perPage, [5, 10, 15, 20, 50], true)) {
            $perPage = 15;
        }

        $query = $warehouse->stocks()->with(['product.category', 'warehouse'])->whereHas('product');

        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            if ($status === 'low_stock') {
                $query->whereRaw('quantity - reserved_qty <= 10')->where('quantity', '>', 0);
            } elseif ($status === 'out_of_stock') {
                $query->whereRaw('quantity - reserved_qty <= 0');
            } elseif ($status === 'available') {
                $query->whereRaw('quantity - reserved_qty > 0');
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('product', function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('sku', 'like', "%$s%");
            });
        }

        $stocks = $query->paginate($perPage)->withQueryString();

        $statsBaseQuery = $warehouse->stocks()->whereHas('product');

        $stats = [
            'total'            => (clone $statsBaseQuery)->count(),
            'low_stock'        => (clone $statsBaseQuery)->whereRaw('quantity - reserved_qty <= 10')->where('quantity', '>', 0)->count(),
            'out_of_stock'     => (clone $statsBaseQuery)->whereRaw('quantity - reserved_qty <= 0')->count(),
            'total_reserved'   => (float) (clone $statsBaseQuery)->sum('reserved_qty'),
            'total_dispatched' => (float) (clone $statsBaseQuery)->sum('dispatched_qty'),
            'total_qty'        => (float) (clone $statsBaseQuery)->sum('quantity'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('inventory.partials.table', compact('stocks'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('inventory.warehouses.show', compact('warehouse', 'stocks', 'stats'));
    }

    public function edit(string $id)
    {
        $warehouse = Warehouse::with(['village'])->findOrFail($id);
        return view('inventory.warehouses.edit', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function getStock(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $stock = \App\Models\Stock::where('warehouse_id', $id)
            ->with('product:id,name,sku')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->product_id => [
                    'quantity' => $item->quantity,
                    'name' => $item->product->name,
                    'sku' => $item->product->sku
                ]];
            });

        return response()->json($stock);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code,' . $id,
            'address' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'village_id' => 'nullable|exists:villages,id',
            'village_name' => 'nullable|string|max:255',
            'post_office' => 'nullable|string|max:255',
            'taluka' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'status' => 'required|in:active,inactive',
            'is_default' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_default'] = $request->has('is_default');
        
        if (empty($data['address']) && !empty($data['address_line_1'])) {
            $data['address'] = $data['address_line_1'] . (!empty($data['city']) ? ', ' . $data['city'] : '');
        }

        if ($data['is_default']) {
            Warehouse::where('id', '!=', $warehouse->id)->update(['is_default' => false]);
        }

        $warehouse->update($data);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        if ($warehouse->stocks()->count() > 0) {
            return redirect()->route('warehouses.index')->with('error', 'Cannot delete warehouse with existing stock.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }
}

