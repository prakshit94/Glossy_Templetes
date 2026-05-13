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
        $query = Warehouse::withCount('stocks');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('code', 'like', "%$s%")
                  ->orWhere('address', 'like', "%$s%")
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
            'state' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'is_default' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_default'] = $request->has('is_default');

        Warehouse::create($data);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $warehouse = Warehouse::with(['stocks.product'])->findOrFail($id);
        return view('inventory.warehouses.show', compact('warehouse'));
    }

    public function edit(string $id)
    {
        $warehouse = Warehouse::findOrFail($id);
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
            'state' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'is_default' => 'boolean',
        ]);

        $data = $request->all();
        $data['is_default'] = $request->has('is_default');

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
