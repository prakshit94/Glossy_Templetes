<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryAdjustment;
use App\Models\Warehouse;
use App\Models\Product;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InventoryAdjustment::with(['warehouse', 'user'])->withCount('items');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('reference_no', 'like', "%$s%")
                  ->orWhere('reason', 'like', "%$s%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $adjustments = $query->latest()->paginate(15)->withQueryString();
        
        $stats = [
            'total' => InventoryAdjustment::count(),
            'approved' => InventoryAdjustment::where('status', 'approved')->count(),
            'pending' => InventoryAdjustment::where('status', 'pending')->count(),
            'rejected' => InventoryAdjustment::where('status', 'rejected')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('inventory.adjustments.partials.table', compact('adjustments'))->render(),
                'stats' => $stats
            ]);
        }

        return view('inventory.adjustments.index', compact('adjustments', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        return view('inventory.adjustments.create', compact('warehouses', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.new_qty' => 'required|numeric|min:0',
        ]);

        \DB::transaction(function() use ($request) {
            $adjustment = InventoryAdjustment::create([
                'reference_no' => 'ADJ-' . strtoupper(uniqid()),
                'warehouse_id' => $request->warehouse_id,
                'adjusted_by' => auth()->id(),
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                // Fetch current qty for reference (usually from Stock model)
                $currentStock = \App\Models\Stock::where('warehouse_id', $request->warehouse_id)
                    ->where('product_id', $item['product_id'])
                    ->first();
                
                $currentQty = $currentStock ? $currentStock->quantity : 0;
                $difference = $item['new_qty'] - $currentQty;

                $adjustment->items()->create([
                    'product_id' => $item['product_id'],
                    'current_qty' => $currentQty,
                    'new_qty' => $item['new_qty'],
                    'difference' => $difference,
                ]);
            }
        });

        return redirect()->route('adjustments.index')->with('success', 'Stock adjustment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $adjustment = InventoryAdjustment::with(['warehouse', 'user', 'items.product'])->findOrFail($id);
        return view('inventory.adjustments.show', compact('adjustment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $adjustment = InventoryAdjustment::with('items')->findOrFail($id);
        
        if ($adjustment->status !== 'pending') {
            return redirect()->route('adjustments.index')->with('error', 'Only pending adjustments can be edited.');
        }

        $warehouses = Warehouse::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        return view('inventory.adjustments.edit', compact('adjustment', 'warehouses', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);
        
        if ($adjustment->status !== 'pending') {
            return redirect()->route('adjustments.index')->with('error', 'Only pending adjustments can be edited.');
        }

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.new_qty' => 'required|numeric|min:0',
        ]);

        \DB::transaction(function() use ($request, $adjustment) {
            $adjustment->update([
                'warehouse_id' => $request->warehouse_id,
                'reason' => $request->reason,
            ]);

            $adjustment->items()->delete();

            foreach ($request->items as $item) {
                $currentStock = \App\Models\Stock::where('warehouse_id', $request->warehouse_id)
                    ->where('product_id', $item['product_id'])
                    ->first();
                
                $currentQty = $currentStock ? $currentStock->quantity : 0;
                $difference = $item['new_qty'] - $currentQty;

                $adjustment->items()->create([
                    'product_id' => $item['product_id'],
                    'current_qty' => $currentQty,
                    'new_qty' => $item['new_qty'],
                    'difference' => $difference,
                ]);
            }
        });

        return redirect()->route('adjustments.index')->with('success', 'Stock adjustment updated successfully.');
    }

    public function approve(string $id)
    {
        $adjustment = InventoryAdjustment::with('items')->findOrFail($id);
        if ($adjustment->status !== 'pending') return back()->with('error', 'Invalid status.');

        \DB::transaction(function() use ($adjustment) {
            foreach ($adjustment->items as $item) {
                $stock = \App\Models\Stock::firstOrCreate(
                    ['warehouse_id' => $adjustment->warehouse_id, 'product_id' => $item->product_id],
                    ['quantity' => 0]
                );
                
                // Update to the new quantity exactly
                $stock->update(['quantity' => $item->new_qty]);
            }
            $adjustment->update(['status' => 'approved']);
        });

        return back()->with('success', 'Adjustment approved and stock updated.');
    }

    public function reject(string $id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);
        if ($adjustment->status !== 'pending') return back()->with('error', 'Invalid status.');

        $adjustment->update(['status' => 'rejected']);
        return back()->with('success', 'Adjustment rejected.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be deleted.');
        }
        $adjustment->delete();
        return redirect()->route('adjustments.index')->with('success', 'Adjustment deleted.');
    }
}
