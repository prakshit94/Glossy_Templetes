<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'total'    => InventoryAdjustment::count(),
            'approved' => InventoryAdjustment::where('status', 'approved')->count(),
            'pending'  => InventoryAdjustment::where('status', 'pending')->count(),
            'rejected' => InventoryAdjustment::where('status', 'rejected')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('inventory.adjustments.partials.table', compact('adjustments'))->render(),
                'stats' => $stats,
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
        $products   = Product::where('status', 'active')->get();
        return view('inventory.adjustments.create', compact('warehouses', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * BUG FIX: Original used \DB::transaction wrapping inventoryService->getStock(),
     * which opens its own nested transaction. Fixed: snapshot current qty without a
     * write-transaction, store the adjustment record cleanly.
     *
     * BUG FIX: uniqid() replaced with Str::random() to eliminate collision risk.
     */
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id'         => 'required|exists:warehouses,id',
            'reason'               => 'required|string|max:255',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.new_qty'      => 'required|numeric|min:0',
        ]);

        $warehouseId = (int) $request->warehouse_id;

        $adjustment = \DB::transaction(function () use ($request, $warehouseId) {
            $referenceNo = 'ADJ-' . strtoupper(Str::random(10));

            $adjustment = InventoryAdjustment::create([
                'reference_no' => $referenceNo,
                'warehouse_id' => $warehouseId,
                'adjusted_by'  => auth()->id(),
                'reason'       => $request->reason,
                'status'       => 'pending',
            ]);

            foreach ($request->items as $item) {
                // Read current qty without opening a write-transaction
                $currentQty = (float) (Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->value('quantity') ?? 0);

                $adjustment->items()->create([
                    'product_id'  => $item['product_id'],
                    'current_qty' => $currentQty,
                    'new_qty'     => $item['new_qty'],
                    'difference'  => (float) $item['new_qty'] - $currentQty,
                ]);
            }

            return $adjustment;
        });

        activity('inventory')
            ->performedOn($adjustment)
            ->causedBy(auth()->user())
            ->withProperties(['reference_no' => $adjustment->reference_no, 'warehouse_id' => $warehouseId])
            ->log("Stock adjustment {$adjustment->reference_no} created (pending approval)");

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
        $products   = Product::where('status', 'active')->get();
        return view('inventory.adjustments.edit', compact('adjustment', 'warehouses', 'products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * BUG FIX: Same nested-transaction issue fixed as in store().
     */
    public function update(Request $request, string $id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);

        if ($adjustment->status !== 'pending') {
            return redirect()->route('adjustments.index')->with('error', 'Only pending adjustments can be edited.');
        }

        $request->validate([
            'warehouse_id'         => 'required|exists:warehouses,id',
            'reason'               => 'required|string|max:255',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.new_qty'      => 'required|numeric|min:0',
        ]);

        $warehouseId = (int) $request->warehouse_id;

        \DB::transaction(function () use ($request, $adjustment, $warehouseId) {
            $adjustment->update([
                'warehouse_id' => $warehouseId,
                'reason'       => $request->reason,
            ]);

            $adjustment->items()->delete();

            foreach ($request->items as $item) {
                $currentQty = (float) (Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->value('quantity') ?? 0);

                $adjustment->items()->create([
                    'product_id'  => $item['product_id'],
                    'current_qty' => $currentQty,
                    'new_qty'     => $item['new_qty'],
                    'difference'  => (float) $item['new_qty'] - $currentQty,
                ]);
            }
        });

        activity('inventory')
            ->performedOn($adjustment->fresh())
            ->causedBy(auth()->user())
            ->withProperties(['reference_no' => $adjustment->reference_no])
            ->log("Stock adjustment {$adjustment->reference_no} updated");

        return redirect()->route('adjustments.index')->with('success', 'Stock adjustment updated successfully.');
    }

    public function approve(string $id, InventoryService $inventoryService)
    {
        $adjustment = InventoryAdjustment::with('items')->findOrFail($id);
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Invalid status.');
        }

        try {
            $inventoryService->applyAdjustment($adjustment);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to approve adjustment.');
        }

        activity('inventory')
            ->performedOn($adjustment->fresh())
            ->causedBy(auth()->user())
            ->withProperties(['reference_no' => $adjustment->reference_no, 'items_count' => $adjustment->items->count()])
            ->log("Stock adjustment {$adjustment->reference_no} approved — stock levels updated");

        return back()->with('success', 'Adjustment approved and stock updated.');
    }

    public function reject(string $id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Invalid status.');
        }

        $adjustment->update(['status' => 'rejected']);

        activity('inventory')
            ->performedOn($adjustment)
            ->causedBy(auth()->user())
            ->withProperties(['reference_no' => $adjustment->reference_no])
            ->log("Stock adjustment {$adjustment->reference_no} rejected");

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

        activity('inventory')
            ->causedBy(auth()->user())
            ->withProperties(['reference_no' => $adjustment->reference_no])
            ->log("Stock adjustment {$adjustment->reference_no} deleted");

        $adjustment->delete();
        return redirect()->route('adjustments.index')->with('success', 'Adjustment deleted.');
    }
}
