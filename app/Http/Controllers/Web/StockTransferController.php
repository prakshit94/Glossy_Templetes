<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse'])->withCount('items');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('transfer_no', 'like', "%$s%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transfers = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'    => StockTransfer::count(),
            'pending'  => StockTransfer::where('status', 'sent')->count(),
            'received' => StockTransfer::where('status', 'received')->count(),
            'draft'    => StockTransfer::where('status', 'draft')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'table' => view('inventory.transfers.partials.table', compact('transfers'))->render(),
                'stats' => $stats,
            ]);
        }

        return view('inventory.transfers.index', compact('transfers', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        $products   = Product::where('status', 'active')->get();
        return view('inventory.transfers.create', compact('warehouses', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * BUG FIX: Replaced uniqid() with Str::random() to eliminate collision risk.
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse_id'      => 'required|exists:warehouses,id',
            'to_warehouse_id'        => 'required|exists:warehouses,id|different:from_warehouse_id',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:0.01',
        ]);

        $transfer = \DB::transaction(function () use ($request) {
            $transfer = StockTransfer::create([
                'transfer_no'       => 'TRF-' . strtoupper(Str::random(10)),
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id'   => $request->to_warehouse_id,
                'status'            => 'draft',
            ]);

            foreach ($request->items as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }

            return $transfer;
        });

        activity('inventory')
            ->performedOn($transfer)
            ->causedBy(auth()->user())
            ->withProperties([
                'transfer_no'       => $transfer->transfer_no,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id'   => $request->to_warehouse_id,
            ])
            ->log("Stock transfer {$transfer->transfer_no} created (draft)");

        return redirect()->route('transfers.index')->with('success', 'Stock transfer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transfer = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product'])->findOrFail($id);
        return view('inventory.transfers.show', compact('transfer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $transfer   = StockTransfer::with('items')->findOrFail($id);
        $warehouses = Warehouse::where('status', 'active')->get();
        $products   = Product::where('status', 'active')->get();
        return view('inventory.transfers.edit', compact('transfer', 'warehouses', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== 'draft') {
            return redirect()->route('transfers.index')->with('error', 'Only draft transfers can be edited.');
        }

        $request->validate([
            'from_warehouse_id'  => 'required|exists:warehouses,id',
            'to_warehouse_id'    => 'required|exists:warehouses,id|different:from_warehouse_id',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
        ]);

        \DB::transaction(function () use ($request, $transfer) {
            $transfer->update([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id'   => $request->to_warehouse_id,
            ]);

            $transfer->items()->delete();

            foreach ($request->items as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }
        });

        activity('inventory')
            ->performedOn($transfer->fresh())
            ->causedBy(auth()->user())
            ->withProperties(['transfer_no' => $transfer->transfer_no])
            ->log("Stock transfer {$transfer->transfer_no} updated");

        return redirect()->route('transfers.index')->with('success', 'Stock transfer updated successfully.');
    }

    public function send(string $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== 'draft') {
            return back()->with('error', 'Invalid status.');
        }

        $transfer->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        activity('inventory')
            ->performedOn($transfer)
            ->causedBy(auth()->user())
            ->withProperties(['transfer_no' => $transfer->transfer_no])
            ->log("Stock transfer {$transfer->transfer_no} marked as sent");

        return back()->with('success', 'Transfer marked as sent.');
    }

    public function receive(string $id, InventoryService $inventoryService)
    {
        $transfer = StockTransfer::with('items')->findOrFail($id);

        if ($transfer->status !== 'sent') {
            return back()->with('error', 'Invalid status.');
        }

        try {
            $inventoryService->receiveTransfer($transfer);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to receive transfer.');
        }

        // Activity log is written inside InventoryService::receiveTransfer

        return back()->with('success', 'Transfer received and stock updated.');
    }

    public function cancel(string $id, InventoryService $inventoryService)
    {
        $transfer = StockTransfer::findOrFail($id);

        if (!in_array($transfer->status, ['draft', 'sent'])) {
            return back()->with('error', 'Only draft or sent transfers can be cancelled.');
        }

        try {
            $inventoryService->cancelSentTransfer($transfer);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to cancel transfer.');
        }

        activity('inventory')
            ->performedOn($transfer->fresh())
            ->causedBy(auth()->user())
            ->withProperties(['transfer_no' => $transfer->transfer_no, 'previous_status' => $transfer->status])
            ->log("Stock transfer {$transfer->transfer_no} cancelled");

        return back()->with('success', 'Transfer cancelled.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== 'draft') {
            return back()->with('error', 'Only draft transfers can be deleted.');
        }

        activity('inventory')
            ->causedBy(auth()->user())
            ->withProperties(['transfer_no' => $transfer->transfer_no])
            ->log("Stock transfer {$transfer->transfer_no} deleted");

        $transfer->delete();
        return redirect()->route('transfers.index')->with('success', 'Transfer deleted.');
    }
}
