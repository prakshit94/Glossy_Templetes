<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Party;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['party', 'warehouse', 'invoice', 'items.product'])->withCount('items');

        if ($request->filled('search')) {

    $s = trim($request->search);

    $query->where(function ($subQuery) use ($s) {

        // Search by order number
        $subQuery->where('order_no', 'LIKE', "%{$s}%")

            // Search in party table
            ->orWhereHas('party', function ($q) use ($s) {

                $q->where('firstname', 'LIKE', "%{$s}%")
                    ->orWhere('lastname', 'LIKE', "%{$s}%")
                    ->orWhere('company_name', 'LIKE', "%{$s}%")
                    ->orWhere('phone', 'LIKE', "%{$s}%");

            });

    });
}

        if ($request->filled('status')) {
            $statuses = $this->expandOrderStatusFilter($request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('product')) {
            $productIds = array_filter(array_map('intval', explode(',', $request->product)));
            if (!empty($productIds)) {
                $query->whereHas('items', function ($q) use ($productIds) {
                    $q->whereIn('product_id', $productIds);
                });
            }
        }

        if ($request->filled('fulfillment')) {
            if ($request->fulfillment === 'unfulfillable') {
                $query->where('status', 'pending')
                      ->whereHas('items', function ($q) {
                          $q->whereRaw('quantity > (IFNULL((SELECT SUM(quantity - reserved_qty) FROM stocks WHERE stocks.product_id = order_items.product_id), 0))');
                      });
            } elseif ($request->fulfillment === 'fulfillable') {
                $query->where(function ($query) {
                    $query->whereIn('status', ['confirmed', 'processing'])
                          ->orWhere(function ($q) {
                              $q->where('status', 'pending')
                                ->whereDoesntHave('items', function ($iq) {
                                    $iq->whereRaw('quantity > (IFNULL((SELECT SUM(quantity - reserved_qty) FROM stocks WHERE stocks.product_id = order_items.product_id), 0))');
                                });
                          });
                });
            }
        }

        if ($request->filled('state') || $request->filled('district') || $request->filled('taluka')) {
            $query->whereHas('shippingAddress.village', function ($q) use ($request) {
                if ($request->filled('state')) {
                    $q->whereIn('state_name', array_map('trim', explode(',', $request->state)));
                }
                if ($request->filled('district')) {
                    $q->whereIn('district_name', array_map('trim', explode(',', $request->district)));
                }
                if ($request->filled('taluka')) {
                    $q->whereIn('taluka_name', array_map('trim', explode(',', $request->taluka)));
                }
            });
        }

        $stats = [
            'total'         => (clone $query)->count(),
            'pending'       => (clone $query)->where('status', 'pending')->count(),
            'processing'    => (clone $query)->where('status', 'processing')->count(),
            'ready_to_ship' => (clone $query)->where('status', 'ready_to_ship')->count(),
            'dispatched'    => (clone $query)->whereIn('status', ['dispatched', 'shipped'])->count(),
        ];

        $perPage = (int) $request->get('perPage', 10);
        $orders  = $query->latest()->paginate($perPage)->withQueryString();

        $statusesList = ['pending', 'confirmed', 'processing', 'ready_to_ship', 'dispatched', 'delivered', 'cancelled', 'returned'];

        $productsList = Product::where('status', 'active')->orderBy('name')->get(['id', 'name', 'sku']);

        $statesList = \Illuminate\Support\Facades\Cache::remember('geo_states', 3600, function () {
            return \App\Models\Village::distinct()->pluck('state_name')->filter()->sort()->values();
        });

        $districtsList = \Illuminate\Support\Facades\Cache::remember('geo_districts_' . $request->state, 3600, function () use ($request) {
            return \App\Models\Village::when($request->filled('state'), function ($q) use ($request) {
                $q->whereIn('state_name', array_map('trim', explode(',', $request->state)));
            })->distinct()->pluck('district_name')->filter()->sort()->values();
        });

        $talukasList = \Illuminate\Support\Facades\Cache::remember('geo_talukas_' . $request->district, 3600, function () use ($request) {
            return \App\Models\Village::when($request->filled('district'), function ($q) use ($request) {
                $q->whereIn('district_name', array_map('trim', explode(',', $request->district)));
            })->distinct()->pluck('taluka_name')->filter()->sort()->values();
        });

        $services = \App\Models\Service::active()->get();

        if ($request->ajax()) {
            return response()->json([
                'table'     => view('orders.partials.table', compact('orders', 'services'))->render(),
                'districts' => $districtsList,
                'talukas'   => $talukasList,
                'stats'     => $stats,
            ]);
        }

        return view('orders.index', compact(
            'orders',
            'stats',
            'statusesList',
            'productsList',
            'statesList',
            'districtsList',
            'talukasList',
            'services'
        ));
    }

    public function create()
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        $parties    = Party::where('status', 'active')->get();
        $products   = Product::where('status', 'active')->where('is_sku_enabled', true)->get();
        return view('orders.create', compact('warehouses', 'parties', 'products'));
    }

    public function store(Request $request, OrderService $orderService)
    {
        $items = collect($request->input('items', []))
            ->filter(fn($item) => !empty($item['product_id']) && isset($item['quantity']) && isset($item['unit_price']))
            ->values()
            ->all();

        $request->merge(['items' => $items]);

        $request->validate([
            'type'               => 'required|in:sale,purchase',
            'party_id'           => 'required|exists:parties,id',
            'warehouse_id'       => 'required|exists:warehouses,id',
            'order_date'         => 'required|date',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $party = Party::findOrFail($request->party_id);
        if ($request->type === 'sale' && $party->type !== 'customer') {
            return back()->withInput()->with('error', 'Sale orders require a customer party.');
        }
        if ($request->type === 'purchase' && $party->type !== 'supplier') {
            return back()->withInput()->with('error', 'Purchase orders require a supplier party.');
        }

        try {
            $order = $orderService->createOrder([
                'type'         => $request->type,
                'party_id'     => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'order_date'   => $request->order_date,
                'items'        => $request->items,
            ]);
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', collect($e->errors())->flatten()->first() ?? 'Failed to create order.');
        }

        activity('orders')
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->withProperties(['type' => $order->type, 'party_id' => $order->party_id])
            ->log("Order #{$order->order_no} created ({$order->type})");

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    public function edit(Order $order)
    {
        if ($order->type === 'sale' && $order->party && $order->party->type === 'customer') {
            return redirect()->route('customers.show', [
                'customer'   => $order->party_id,
                'edit_order' => $order->id,
            ]);
        }

        if (view()->exists('orders.edit')) {
            $warehouses = Warehouse::where('status', 'active')->get();
            $parties    = Party::where('status', 'active')->get();
            $products   = Product::where('status', 'active')->where('is_sku_enabled', true)->get();
            return view('orders.edit', compact('order', 'warehouses', 'parties', 'products'));
        }

        return redirect()->route('orders.show', $order)
            ->with('info', 'Edit functionality is integrated into the detail view.');
    }

    public function show(string $id)
    {
        $order = Order::with([
            'party',
            'warehouse.village',
            'items.product',
            'creator',
            'updater',
            'shippingAddress.village',
            'billingAddress.village',
            'shipments',
            'invoice',
            'payments'
        ])->findOrFail($id);

        $services = \App\Models\Service::active()->get();

        return view('orders.show', compact('order', 'services'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Status Transitions – all go through InventoryService / OrderService
    // ─────────────────────────────────────────────────────────────────────────

    public function confirm(string $id, InventoryService $inventoryService)
    {
        $order = Order::with('items')->findOrFail($id);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be confirmed.');
        }

        foreach ($order->items as $item) {
            $available = $inventoryService->getAvailableQty($item->product_id, $order->warehouse_id);
            if ($item->quantity > $available) {
                return back()->with('error', 'Insufficient on-hand stock to confirm this order.');
            }
        }

        try {
            $inventoryService->confirmOrder($order);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to confirm order.');
        }

        activity('orders')
            ->performedOn($order->fresh())
            ->causedBy(auth()->user())
            ->log("Order #{$order->order_no} confirmed — stock reserved");

        return back()->with('success', 'Order confirmed and stock reserved.');
    }

    public function ship(string $id, Request $request, InventoryService $inventoryService)
    {
        $order = Order::findOrFail($id);

        if (!in_array($order->status, ['confirmed', 'processing'])) {
            return back()->with('error', 'Only confirmed or processing orders can be marked as ready to ship.');
        }

        $request->validate([
            'carrier_name' => 'nullable|string|max:255',
            'tracking_no'  => 'nullable|string|max:255',
        ]);

        try {
            $inventoryService->readyToShipOrder($order, $request->carrier_name, $request->tracking_no);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to mark order as ready to ship.');
        }

        activity('orders')
            ->performedOn($order->fresh())
            ->causedBy(auth()->user())
            ->withProperties(array_filter(['carrier' => $request->carrier_name, 'tracking' => $request->tracking_no]))
            ->log("Order #{$order->order_no} marked as ready to ship");

        return back()->with('success', 'Order marked as ready to ship.');
    }

    public function dispatch(string $id, InventoryService $inventoryService)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'ready_to_ship') {
            return back()->with('error', 'Only orders in ready to ship status can be dispatched.');
        }

        try {
            $inventoryService->dispatchOrder($order);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to dispatch order.');
        }

        activity('orders')
            ->performedOn($order->fresh())
            ->causedBy(auth()->user())
            ->log("Order #{$order->order_no} dispatched — inventory deducted");

        return back()->with('success', 'Order dispatched and inventory updated.');
    }

    public function markProcessing(string $id, OrderService $orderService)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed orders can be moved to processing.');
        }

        $orderService->updateStatus($order, 'processing');

        activity('orders')
            ->performedOn($order->fresh())
            ->causedBy(auth()->user())
            ->log("Order #{$order->order_no} moved to processing");

        return back()->with('success', 'Order moved to processing.');
    }

    public function markDelivered(string $id, InventoryService $inventoryService)
    {
        $order = Order::findOrFail($id);

        if (!in_array($order->status, Order::inTransitStatuses(), true)) {
            return back()->with('error', 'Only dispatched orders can be marked as delivered.');
        }

        try {
            $inventoryService->deliverOrder($order);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to mark order as delivered.');
        }

        activity('orders')
            ->performedOn($order->fresh())
            ->causedBy(auth()->user())
            ->log("Order #{$order->order_no} marked as delivered");

        return back()->with('success', 'Order marked as delivered.');
    }

    public function cancel(string $id, InventoryService $inventoryService)
    {
        $order = Order::findOrFail($id);

        try {
            $inventoryService->cancelOrder($order);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to cancel order.');
        }

        activity('orders')
            ->performedOn($order->fresh())
            ->causedBy(auth()->user())
            ->log("Order #{$order->order_no} cancelled — stock reservation released");

        return back()->with('success', 'Order cancelled and stock released.');
    }

    public function receipt(string $id, OrderService $orderService)
    {
        $order = $orderService->getOrderForReceipt((int) $id);
        return view('orders.receipt', compact('order'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Bulk Status
    //  SSOT: all inventory-impacting transitions now route through services.
    //  No raw DB::table('stocks') writes remain here.
    // ─────────────────────────────────────────────────────────────────────────

    public function bulkStatus(Request $request, InventoryService $inventoryService, OrderService $orderService)
    {
        $request->validate([
            'ids'    => 'required|json',
            'status' => 'required|string|in:pending,confirmed,processing,ready_to_ship,dispatched,delivered,cancelled,returned',
        ]);

        $rawIds = json_decode($request->ids, true);
        if (!is_array($rawIds) || empty($rawIds)) {
            return back()->with('error', 'No orders selected.');
        }

        // Sanitise: ensure all IDs are positive integers
        $ids = array_values(array_filter(array_map('intval', $rawIds), fn($id) => $id > 0));
        if (empty($ids)) {
            return back()->with('error', 'No valid order IDs provided.');
        }

        $targetStatus = $request->status;
        $count        = 0;
        $errors       = [];

        try {
            \DB::transaction(function () use ($ids, $targetStatus, $inventoryService, $orderService, &$count, &$errors) {
                $orders = Order::with(['items', 'shipments'])->whereIn('id', $ids)->get();

                foreach ($orders as $order) {
                    if ($order->status === $targetStatus) {
                        continue;
                    }

                    try {
                        // ─── FORWARD TRANSITIONS ───────────────────────────
                        if ($targetStatus === 'confirmed' && $order->status === 'pending') {
                            foreach ($order->items as $item) {
                                $available = $inventoryService->getAvailableQty($item->product_id, $order->warehouse_id);
                                if ($item->quantity > $available) {
                                    throw ValidationException::withMessages(['error' => 'Insufficient on-hand stock to confirm this order.']);
                                }
                            }
                            $inventoryService->confirmOrder($order);
                            $count++;
                        } elseif ($targetStatus === 'processing' && $order->status === 'confirmed') {
                            $orderService->updateStatus($order, 'processing');
                            $count++;
                        } elseif ($targetStatus === 'ready_to_ship' && in_array($order->status, ['confirmed', 'processing'])) {
                            $inventoryService->readyToShipOrder($order, null, null);
                            $count++;
                        } elseif ($targetStatus === 'dispatched' && $order->status === 'ready_to_ship') {
                            $inventoryService->dispatchOrder($order);
                            $count++;
                        } elseif ($targetStatus === 'delivered' && in_array($order->status, Order::inTransitStatuses(), true)) {
                            $inventoryService->deliverOrder($order);
                            $count++;
                        } elseif ($targetStatus === 'cancelled' && !in_array($order->status, ['delivered', 'cancelled', 'returned'])) {
                            $inventoryService->cancelOrder($order);
                            $count++;

                        // ─── REVERT TRANSITIONS (via InventoryService SSOT) ─
                        } elseif ($targetStatus === 'pending' && in_array($order->status, ['confirmed', 'processing', 'cancelled', 'ready_to_ship'])) {
                            $inventoryService->revertOrderToPending($order);
                            $count++;
                        } elseif ($targetStatus === 'confirmed' && $order->status === 'processing') {
                            // Processing → Confirmed: no stock change, just status
                            $order->update(['status' => 'confirmed', 'updated_by' => auth()->id()]);
                            $count++;
                        } elseif ($targetStatus === 'ready_to_ship' && $order->status === 'dispatched') {
                            $inventoryService->revertOrderToProcessing($order);
                            $count++;
                        } elseif ($targetStatus === 'processing' && $order->status === 'ready_to_ship') {
                            $inventoryService->revertOrderToProcessing($order);
                            $count++;
                        } elseif ($targetStatus === 'ready_to_ship' && $order->status === 'delivered') {
                            $order->update(['status' => 'ready_to_ship', 'updated_by' => auth()->id()]);
                            $count++;
                        } elseif ($targetStatus === 'dispatched' && $order->status === 'delivered') {
                            $inventoryService->revertDeliveredToDispatched($order);
                            $count++;
                        }
                    } catch (ValidationException $e) {
                        $errors[] = "Order #{$order->order_no}: " . collect($e->errors())->flatten()->first();
                    }
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing status update: ' . $e->getMessage());
        }

        if ($count > 0) {
            activity('orders')
                ->causedBy(auth()->user())
                ->withProperties(['ids' => $ids, 'target_status' => $targetStatus, 'count' => $count])
                ->log("Bulk status update: {$count} orders moved to '{$targetStatus}'");
        }

        $msg = $count . ' orders updated successfully.';
        if (!empty($errors)) {
            $msg .= ' Skipped: ' . implode('; ', $errors);
        }

        return back()->with($count > 0 ? 'success' : 'error', $msg);
    }

    public function destroy(Order $order)
    {
        if (!in_array($order->status, ['pending', 'cancelled'])) {
            return back()->with('error', 'Only pending or cancelled orders can be deleted.');
        }

        activity('orders')
            ->causedBy(auth()->user())
            ->withProperties(['order_no' => $order->order_no])
            ->log("Order #{$order->order_no} deleted");

        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Invoice & PDF — all invoice creation via InvoiceService (SSOT)
    // ─────────────────────────────────────────────────────────────────────────

    public function downloadInvoice(string $id, InvoiceService $invoiceService)
    {
        try {
            $order   = Order::with(['invoices'])->findOrFail($id);
            $invoice = $invoiceService->generateForOrder($order);
            $invoice->load(['order.warehouse.village', 'order.items.product', 'order.party', 'order.billingAddress.village', 'order.shippingAddress.village']);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.pdf.invoice', compact('invoice'))->setPaper('a5', 'portrait');
            return $pdf->download("invoice-{$invoice->invoice_no}.pdf");
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error (Invoice): ' . $e->getMessage());
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }

    public function generateInvoice(string $id, InvoiceService $invoiceService)
    {
        try {
            $order   = Order::findOrFail($id);
            $existing = $invoiceService->findForOrder($order);

            if ($existing) {
                return back()->with('error', 'Invoice already exists for this order.');
            }

            $invoiceService->generateForOrder($order);
            return back()->with('success', 'Invoice generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error generating invoice: ' . $e->getMessage());
        }
    }

    public function downloadReceipt(string $id)
    {
        try {
            $order = Order::with(['warehouse.village', 'items.product', 'party', 'shippingAddress.village', 'billingAddress.village'])->findOrFail($id);
            $pdf   = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.pdf.cod', compact('order'))->setPaper('a5', 'portrait');
            return $pdf->download("receipt-{$order->order_no}.pdf");
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error (Receipt): ' . $e->getMessage());
            return back()->with('error', 'Could not generate PDF: ' . $e->getMessage());
        }
    }

    public function bulkPrint(Request $request, InvoiceService $invoiceService)
    {
        $validated = $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'exists:orders,id',
            'type'   => 'required|in:invoice,cod',
        ]);

        $orders = Order::whereIn('id', $validated['ids'])
            ->with(['warehouse.village', 'items.product', 'party', 'billingAddress.village', 'shippingAddress.village', 'invoices'])
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'No orders selected.');
        }

        if ($validated['type'] === 'invoice') {
            $invoices = new \Illuminate\Database\Eloquent\Collection();

            foreach ($orders as $order) {
                // SSOT: use InvoiceService instead of direct Invoice::create
                $invoice = $invoiceService->generateForOrder($order);
                $invoice->setRelation('order', $order);
                $invoices->push($invoice);
            }

            $invoices->load(['order.warehouse.village', 'order.party', 'order.billingAddress.village', 'order.shippingAddress.village', 'order.items.product']);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.pdf.bulk_invoice', compact('invoices'))->setPaper('a5', 'portrait');
            return $pdf->download('bulk-invoices-' . now()->format('YmdHis') . '.pdf');

        } elseif ($validated['type'] === 'cod') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.pdf.bulk_cod', compact('orders'))->setPaper('a5', 'portrait');
            return $pdf->download('bulk-cod-' . now()->format('YmdHis') . '.pdf');
        }

        return back()->with('error', 'Invalid print type.');
    }

    /**
     * Expand status filter: "dispatched" includes legacy "shipped" rows until fully migrated.
     *
     * @return list<string>
     */
    private function expandOrderStatusFilter(string $statusCsv): array
    {
        $statuses = array_filter(array_map('trim', explode(',', $statusCsv)));

        if (in_array('dispatched', $statuses, true)) {
            $statuses[] = 'shipped';
        }

        return array_values(array_unique($statuses));
    }
}
