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
    public function __construct()
    {
        $this->middleware('permission:orders.view')->only(['index', 'show', 'storeVerification', 'bulkExport']);
        $this->middleware('permission:orders.create')->only(['create', 'store', 'bulkImport', 'bulkImportTemplate']);
        $this->middleware('permission:orders.edit')->only(['edit']);
        $this->middleware('permission:orders.delete')->only(['destroy']);
        $this->middleware('permission:orders.confirm')->only(['confirm']);
        $this->middleware('permission:orders.ship')->only(['ship']);
        $this->middleware('permission:orders.dispatch')->only(['dispatch']);
        $this->middleware('permission:orders.processing')->only(['markProcessing']);
        $this->middleware('permission:orders.deliver')->only(['markDelivered']);
        $this->middleware('permission:orders.cancel')->only(['cancel']);
        $this->middleware('permission:orders.invoice_pdf')->only(['downloadInvoice']);
        $this->middleware('permission:orders.generate_invoice')->only(['generateInvoice']);
        $this->middleware('permission:orders.cod')->only(['downloadReceipt']);
        $this->middleware('permission:orders.receipt')->only(['receipt']);
        $this->middleware('permission:orders.bulk_status')->only(['bulkStatus', 'bulkStoreVerification']);
        $this->middleware('permission:orders.bulk_print')->only(['bulkPrint']);
        $this->middleware('permission:orders.revert_status')->only(['revertStatus']);
    }

    public function index(Request $request)
    {
        $query = Order::with(['party', 'warehouse', 'invoice', 'items.product', 'shipments','creator'])->withCount('items');

        $user = auth()->user();
        if ($user && !$user->hasAnyRole(['Super Admin', 'Admin']) && !$user->can('view_all_order')) {
            $query->where('created_by', $user->id);
        }

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

        if ($request->filled('status') && auth()->user()->can('orders.filter_status')) {
            $requestedStatuses = array_filter(array_map('trim', explode(',', $request->status)));
            $hasFutureOrder = in_array('future_order', $requestedStatuses, true);
            $hasPending     = in_array('pending', $requestedStatuses, true);

            // Strip virtual statuses; expand dispatched → include shipped
            $realStatuses = array_values(array_filter($requestedStatuses, fn($s) => !in_array($s, ['future_order', 'pending'])));
            if (in_array('dispatched', $realStatuses, true)) {
                $realStatuses[] = 'shipped';
                $realStatuses   = array_values(array_unique($realStatuses));
            }

            $query->where(function ($q) use ($hasFutureOrder, $hasPending, $realStatuses) {
                $first = true;

                if ($hasFutureOrder) {
                    $q->where(function ($sub) {
                        $sub->where('status', 'pending')->where('is_draft', true);
                    });
                    $first = false;
                }

                if ($hasPending) {
                    $method = $first ? 'where' : 'orWhere';
                    $q->$method(function ($sub) {
                        $sub->where('status', 'pending')
                            ->where(function ($s2) {
                                $s2->where('is_draft', false)->orWhereNull('is_draft');
                            });
                    });
                    $first = false;
                }

                if (!empty($realStatuses)) {
                    $method = $first ? 'whereIn' : 'orWhereIn';
                    $q->$method('status', $realStatuses);
                }
            });
        }

        if ($request->filled('product') && auth()->user()->can('orders.filter_product')) {
            $productIds = array_filter(array_map('intval', explode(',', $request->product)));
            if (!empty($productIds)) {
                $query->whereHas('items', function ($q) use ($productIds) {
                    $q->whereIn('product_id', $productIds);
                });
            }
        }

        if ($request->filled('fulfillment') && auth()->user()->can('orders.filter_fulfillment')) {
            if ($request->fulfillment === 'unfulfillable') {
                $query->where('status', 'pending')
                      ->whereHas('items', function ($q) {
                          $q->whereRaw('quantity > (IFNULL((SELECT SUM(quantity - reserved_qty) FROM stocks WHERE stocks.product_id = order_items.product_id AND stocks.warehouse_id = orders.warehouse_id AND stocks.deleted_at IS NULL), 0))');
                      });
            } elseif ($request->fulfillment === 'fulfillable') {
                $query->where(function ($query) {
                    $query->whereIn('status', ['confirmed', 'processing'])
                          ->orWhere(function ($q) {
                              $q->where('status', 'pending')
                                ->whereDoesntHave('items', function ($iq) {
                                    $iq->whereRaw('quantity > (IFNULL((SELECT SUM(quantity - reserved_qty) FROM stocks WHERE stocks.product_id = order_items.product_id AND stocks.warehouse_id = orders.warehouse_id AND stocks.deleted_at IS NULL), 0))');
                                });
                          });
                });
            }
        }

        if ($request->filled('state') || $request->filled('district') || $request->filled('taluka')) {
            $query->whereHas('shippingAddress.village', function ($q) use ($request) {
                if ($request->filled('state') && auth()->user()->can('orders.filter_state')) {
                    $q->whereIn('state_name', array_map('trim', explode(',', $request->state)));
                }
                if ($request->filled('district') && auth()->user()->can('orders.filter_district')) {
                    $q->whereIn('district_name', array_map('trim', explode(',', $request->district)));
                }
                if ($request->filled('taluka') && auth()->user()->can('orders.filter_taluka')) {
                    $q->whereIn('taluka_name', array_map('trim', explode(',', $request->taluka)));
                }
            });
        }

        if ($request->filled('carrier') && auth()->user()->can('orders.filter_carrier')) {
            $carriers = array_filter(array_map('trim', explode(',', $request->carrier)));
            if (!empty($carriers)) {
                $query->whereHas('shipments', function ($q) use ($carriers) {
                    $q->whereIn('carrier_name', $carriers);
                });
            }
        }

        if ($request->filled('from_date') && auth()->user()->can('orders.filter_date')) {
            $query->whereDate('order_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date') && auth()->user()->can('orders.filter_date')) {
            $query->whereDate('order_date', '<=', $request->to_date);
        }

        $stats = [
            'total'         => (clone $query)->count(),
            'future_order'  => (clone $query)->where('status', 'pending')->where('is_draft', true)->count(),
            'pending'       => (clone $query)->where('status', 'pending')->where(function($q){ $q->where('is_draft', false)->orWhereNull('is_draft'); })->count(),
            'confirmed'     => (clone $query)->where('status', 'confirmed')->count(),
            'processing'    => (clone $query)->where('status', 'processing')->count(),
            'ready_to_ship' => (clone $query)->where('status', 'ready_to_ship')->count(),
            'dispatched'    => (clone $query)->whereIn('status', ['dispatched', 'shipped'])->count(),
            'delivered'     => (clone $query)->where('status', 'delivered')->count(),
            'cancelled'     => (clone $query)->where('status', 'cancelled')->count(),
        ];

        $perPage = (int) $request->get('perPage', 15);
        $sortDate = 'desc';
        if (auth()->user()->can('orders.filter_sort')) {
            $sortDate = $request->get('sort_date', 'desc') === 'asc' ? 'asc' : 'desc';
        }
        $orders  = $query->orderBy('order_date', $sortDate)->paginate($perPage)->withQueryString();

        $statusesList = ['pending', 'confirmed', 'processing', 'ready_to_ship', 'dispatched', 'delivered', 'cancelled', 'returned', 'future_order'];

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
        $drivers = \App\Models\Driver::whereIn('status', ['available', 'busy'])->get();
        $transports = \App\Models\Transport::whereIn('status', ['available', 'on_delivery'])->get();
        $carriersList = $services->pluck('name')->filter()->sort()->values();

        if ($request->ajax()) {
            return response()->json([
                'table'     => view('orders.partials.table', compact('orders', 'services'))->render(),
                'districts' => $districtsList,
                'talukas'   => $talukasList,
                'stats'     => $stats,
                'carriers'  => $carriersList,
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
            'services',
            'drivers',
            'transports',
            'carriersList'
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
            'shippingAddress.village.services',
            'billingAddress.village.services',
            'shipments.events',
            'invoice',
            'payments',
            'verificationLogs.user'
        ])->findOrFail($id);

        $services = \App\Models\Service::active()->get();

        return view('orders.show', compact('order', 'services'));
    }

    public function storeVerification(Request $request, string $id, InventoryService $inventoryService, OrderService $orderService)
    {
        $order = Order::with('shipments')->findOrFail($id);
        $outcomes = array_keys(\App\Models\OrderVerificationLog::OUTCOMES);

        $validated = $request->validate([
            'outcome'     => 'required|in:' . implode(',', $outcomes),
            'remark'      => 'nullable|string|max:2000',
            'follow_up_at'=> 'nullable|date',
        ]);

        $log = \App\Models\OrderVerificationLog::create([
            'order_id'    => $order->id,
            'outcome'     => $validated['outcome'],
            'remark'      => $validated['remark'] ?? null,
            'follow_up_at'=> $validated['follow_up_at'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        // ── Shipment tracking event ──────────────────────────────────────────
        $shipment = $order->shipments->first();
        if ($shipment) {
            $parts = ['Order verification: ' . $log->outcome_label];
            if (!empty($validated['remark'])) {
                $parts[] = $validated['remark'];
            }
            if (!empty($validated['follow_up_at'])) {
                $parts[] = 'Follow-up: ' . \Carbon\Carbon::parse($validated['follow_up_at'])->format('M d, Y h:i A');
            }
            $parts[] = 'By: ' . (auth()->user()->name ?? 'Staff');

            \App\Models\ShipmentTrackingEvent::create([
                'shipment_id' => $shipment->id,
                'event_name'  => 'Order Verification',
                'location'    => $order->warehouse ? $order->warehouse->name : 'Warehouse',
                'description' => implode(' | ', $parts),
                'occurred_at' => now(),
            ]);
        }

        activity('orders')
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->withProperties(['outcome' => $log->outcome])
            ->log("Order #{$order->order_no} verification call logged: {$log->outcome_label}");

        // ── Outcome-driven status transitions ────────────────────────────────
        $statusChangedMessage = '';

        try {
            switch ($validated['outcome']) {
                case 'customer_confirmed':
                    if ($order->status === 'pending') {
                        $inventoryService->confirmOrder($order);
                        $statusChangedMessage = ' Order confirmed and stock reserved.';
                    }
                    break;

                case 'mark_processing':
                    if ($order->status === 'confirmed') {
                        $orderService->updateStatus($order, 'processing');
                        $statusChangedMessage = ' Order moved to processing.';
                    }
                    break;

                case 'dispatch_order':
                    if ($order->status === 'ready_to_ship') {
                        $inventoryService->dispatchOrder($order);
                        $statusChangedMessage = ' Order dispatched and inventory updated.';
                    }
                    break;

                case 'mark_delivered':
                    if (in_array($order->status, Order::inTransitStatuses(), true)) {
                        $inventoryService->deliverOrder($order);
                        $statusChangedMessage = ' Order marked as delivered.';
                    }
                    break;

                case 'cancel_order':
                    if (!in_array($order->status, array_merge(['delivered', 'cancelled', 'returned'], Order::inTransitStatuses()), true)) {
                        $inventoryService->cancelOrder($order);
                        $statusChangedMessage = ' Order cancelled and stock released.';
                    }
                    break;
            }
        } catch (ValidationException $e) {
            return back()->with('error', 'Call logged, but status transition failed: ' . (collect($e->errors())->flatten()->first() ?? 'Unknown error.'));
        }

        return back()->with('success', 'Verification call logged.' . $statusChangedMessage);
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

        try {
            $inventoryService->confirmOrder($order);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to confirm order.');
        }

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

        return back()->with('success', 'Order dispatched and inventory updated.');
    }

    public function markProcessing(string $id, OrderService $orderService)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed orders can be moved to processing.');
        }

        $orderService->updateStatus($order, 'processing');

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
            $orders = Order::with(['items', 'shipments'])->whereIn('id', $ids)->get();

            foreach ($orders as $order) {
                if ($order->status === $targetStatus) {
                    continue;
                }

                try {
                    // ─── FORWARD TRANSITIONS ───────────────────────────
                    if ($targetStatus === 'confirmed' && $order->status === 'pending') {
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
                        activity('orders')
                            ->performedOn($order)
                            ->causedBy(auth()->user())
                            ->log("Order #{$order->order_no} reverted to pending");
                        $count++;
                    } elseif ($targetStatus === 'confirmed' && $order->status === 'processing') {
                        // Processing → Confirmed: no stock change, just status
                        $order->update(['status' => 'confirmed', 'updated_by' => auth()->id()]);
                        activity('orders')
                            ->performedOn($order)
                            ->causedBy(auth()->user())
                            ->log("Order #{$order->order_no} reverted to confirmed");
                        $count++;
                    } elseif ($targetStatus === 'ready_to_ship' && $order->status === 'dispatched') {
                        $inventoryService->revertOrderToProcessing($order);
                        activity('orders')
                            ->performedOn($order)
                            ->causedBy(auth()->user())
                            ->log("Order #{$order->order_no} reverted to ready to ship");
                        $count++;
                    } elseif ($targetStatus === 'processing' && $order->status === 'ready_to_ship') {
                        $inventoryService->revertOrderToProcessing($order);
                        activity('orders')
                            ->performedOn($order)
                            ->causedBy(auth()->user())
                            ->log("Order #{$order->order_no} reverted to processing");
                        $count++;

                    } elseif ($targetStatus === 'dispatched' && $order->status === 'delivered') {
                        $inventoryService->revertDeliveredToDispatched($order);
                        $count++;
                    }
                } catch (ValidationException $e) {
                    $errors[] = "Order #{$order->order_no}: " . collect($e->errors())->flatten()->first();
                }
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing status update: ' . $e->getMessage());
        }

        $msg = $count . ' orders updated successfully.';
        if (!empty($errors)) {
            $msg .= ' Skipped: ' . implode('; ', $errors);
        }

        return back()->with($count > 0 ? 'success' : 'error', $msg);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Bulk Verification + Status
    //  Logs a verification call for EVERY selected order, then delegates the
    //  actual status transitions to the existing bulkStatus() SSOT.
    // ─────────────────────────────────────────────────────────────────────────

    public function bulkStoreVerification(Request $request, InventoryService $inventoryService, OrderService $orderService)
    {
        $outcomes = array_keys(\App\Models\OrderVerificationLog::OUTCOMES);

        $validated = $request->validate([
            'ids'          => 'required|json',
            'status'       => 'required|string|in:pending,confirmed,processing,ready_to_ship,dispatched,delivered,cancelled,returned',
            'outcome'      => 'required|in:' . implode(',', $outcomes),
            'remark'       => 'nullable|string|max:2000',
            'follow_up_at' => 'nullable|date',
        ]);

        $rawIds = json_decode($validated['ids'], true);
        if (!is_array($rawIds) || empty($rawIds)) {
            return back()->with('error', 'No orders selected.');
        }
        $ids = array_values(array_filter(array_map('intval', $rawIds), fn($id) => $id > 0));
        if (empty($ids)) {
            return back()->with('error', 'No valid order IDs provided.');
        }

        $orders = Order::with(['items', 'shipments', 'warehouse'])->whereIn('id', $ids)->get();

        // ── Log a verification call for every selected order ─────────────────
        foreach ($orders as $order) {
            $log = \App\Models\OrderVerificationLog::create([
                'order_id'     => $order->id,
                'outcome'      => $validated['outcome'],
                'remark'       => $validated['remark'] ?? null,
                'follow_up_at' => $validated['follow_up_at'] ?? null,
                'created_by'   => auth()->id(),
            ]);

            // Attach to shipment tracking if a shipment exists
            $shipment = $order->shipments->first();
            if ($shipment) {
                $parts = ['Bulk verification: ' . $log->outcome_label];
                if (!empty($validated['remark'])) {
                    $parts[] = $validated['remark'];
                }
                if (!empty($validated['follow_up_at'])) {
                    $parts[] = 'Follow-up: ' . \Carbon\Carbon::parse($validated['follow_up_at'])->format('M d, Y h:i A');
                }
                $parts[] = 'By: ' . (auth()->user()->name ?? 'Staff');

                \App\Models\ShipmentTrackingEvent::create([
                    'shipment_id' => $shipment->id,
                    'event_name'  => 'Order Verification',
                    'location'    => $order->warehouse ? $order->warehouse->name : 'Warehouse',
                    'description' => implode(' | ', $parts),
                    'occurred_at' => now(),
                ]);
            }

            activity('orders')
                ->performedOn($order)
                ->causedBy(auth()->user())
                ->withProperties(['outcome' => $log->outcome])
                ->log("Order #{$order->order_no} bulk verification call logged: {$log->outcome_label}");
        }

        // ── Delegate the actual status transitions to the existing bulkStatus ─
        // We re-use the same request so all validation and SSOT logic is identical.
        return $this->bulkStatus($request, $inventoryService, $orderService);
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

    public function bulkExport(Request $request)
    {
        $query = Order::with(['party', 'warehouse', 'items.product', 'shipments']);

        $user = auth()->user();
        if ($user && !$user->hasAnyRole(['Super Admin', 'Admin']) && !$user->can('view_all_order')) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($subQuery) use ($s) {
                $subQuery->where('order_no', 'LIKE', "%{$s}%")
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
                          $q->whereRaw('quantity > (IFNULL((SELECT SUM(quantity - reserved_qty) FROM stocks WHERE stocks.product_id = order_items.product_id AND stocks.warehouse_id = orders.warehouse_id AND stocks.deleted_at IS NULL), 0))');
                      });
            } elseif ($request->fulfillment === 'fulfillable') {
                $query->where(function ($query) {
                    $query->whereIn('status', ['confirmed', 'processing'])
                          ->orWhere(function ($q) {
                              $q->where('status', 'pending')
                                ->whereDoesntHave('items', function ($iq) {
                                    $iq->whereRaw('quantity > (IFNULL((SELECT SUM(quantity - reserved_qty) FROM stocks WHERE stocks.product_id = order_items.product_id AND stocks.warehouse_id = orders.warehouse_id AND stocks.deleted_at IS NULL), 0))');
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

        $orders = $query->orderBy('id')->get();

        $filename = 'orders-export-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'order_no',
                'type',
                'status',
                'order_date',
                'party_name',
                'party_phone',
                'warehouse_code',
                'warehouse_name',
                'carrier_name',
                'tracking_no',
                'total_items'
            ]);

            foreach ($orders as $order) {
                $shipment = $order->shipments->first();
                $partyName = $order->party ? trim($order->party->firstname . ' ' . $order->party->lastname . ' ' . $order->party->company_name) : '';
                fputcsv($out, [
                    $order->order_no,
                    $order->type,
                    $order->status,
                    $order->order_date,
                    $partyName,
                    $order->party?->phone,
                    $order->warehouse?->code,
                    $order->warehouse?->name,
                    $shipment?->carrier_name,
                    $shipment?->tracking_no,
                    $order->items->count(),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function bulkImport(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $isPreview = $request->boolean('preview');

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            if ($isPreview) return response()->json(['error' => 'Unable to read uploaded file.'], 400);
            return back()->with('error', 'Unable to read uploaded file.');
        }

        $firstRow = fgetcsv($handle);
        if ($firstRow === false) {
            fclose($handle);
            if ($isPreview) return response()->json(['error' => 'CSV file is empty.'], 400);
            return back()->with('error', 'CSV file is empty.');
        }

        $normalized = array_map(fn($v) => strtolower(trim((string)$v)), $firstRow);
        $hasHeader = in_array('order_no', $normalized, true);

        $updated = 0;
        $skipped = 0;
        $previewData = [];

        $extractByHeader = function (array $row, array $header, array $keys): ?string {
            foreach ($keys as $key) {
                $index = array_search($key, $header, true);
                if ($index !== false) {
                    return isset($row[$index]) ? trim((string)$row[$index]) : null;
                }
            }
            return null;
        };

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if ($hasHeader) {
                    $orderNo = $extractByHeader($row, $normalized, ['order_no']);
                    $carrierName = $extractByHeader($row, $normalized, ['carrier_name']);
                    $trackingNo = $extractByHeader($row, $normalized, ['tracking_no']);
                } else {
                    $orderNo = trim((string)($row[0] ?? ''));
                    $carrierName = trim((string)($row[1] ?? ''));
                    $trackingNo = trim((string)($row[2] ?? ''));
                }

                if (!$orderNo) {
                    continue;
                }

                $order = Order::with(['party', 'shipments'])->where('order_no', $orderNo)->first();

                if ($isPreview) {
                    $shipment = $order ? $order->shipments->first() : null;
                    $previewData[] = [
                        'order_no' => $orderNo,
                        'csv_carrier' => $carrierName ?: 'N/A',
                        'csv_tracking' => $trackingNo ?: 'N/A',
                        'is_valid' => $order && $order->status === 'processing',
                        'customer' => $order ? ($order->party ? trim($order->party->firstname . ' ' . $order->party->lastname) : 'N/A') : 'Not Found',
                        'current_status' => $order ? $order->status : 'Not Found',
                        'upcoming_status' => ($order && $order->status === 'processing') ? 'ready_to_ship' : 'N/A',
                        'existing_carrier' => $shipment && $shipment->carrier_name ? $shipment->carrier_name : 'N/A',
                        'existing_tracking' => $shipment && $shipment->tracking_no ? $shipment->tracking_no : 'N/A',
                    ];
                    continue;
                }

                if (!$order || $order->status !== 'processing') {
                    $skipped++;
                    continue;
                }

                $inventoryService->readyToShipOrder($order, $carrierName, $trackingNo);
                $updated++;
            }
            
            if ($isPreview) {
                \Illuminate\Support\Facades\DB::rollBack();
                fclose($handle);
                return response()->json(['preview' => $previewData]);
            }
            
            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            fclose($handle);
            if ($isPreview) {
                return response()->json(['error' => 'Error processing CSV: ' . $e->getMessage()], 400);
            }
            return back()->with('error', 'Error processing CSV: ' . $e->getMessage());
        }

        fclose($handle);

        $message = "Orders import completed. Updated {$updated} order(s).";
        if ($skipped > 0) {
            $message .= " Skipped {$skipped} invalid/non-processing row(s).";
        }

        return back()->with('success', $message);
    }

    public function bulkImportTemplate()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['order_no', 'carrier_name', 'tracking_no']);
            fputcsv($out, ['ORD-000001', 'FedEx', 'FDX123456789']);
            fclose($out);
        }, 'orders-shipping-import-template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function revertStatus(
    Request $request,
    Order $order,
    InventoryService $inventoryService
) {
    abort_unless(
        auth()->user()->can('orders.revert_status'),
        403
    );

    $request->validate([
        'status' => 'required|string',
    ]);

    $targetStatus = $request->status;

    // confirmed -> pending
    if (
        $targetStatus === 'pending' &&
        in_array($order->status, [
            'confirmed',
            'processing',
            'cancelled',
            'ready_to_ship'
        ])
    ) {

        $inventoryService->revertOrderToPending($order);
    }

    // processing -> confirmed
    elseif (
        $targetStatus === 'confirmed' &&
        $order->status === 'processing'
    ) {

        $order->update([
            'status' => 'confirmed',
            'updated_by' => auth()->id(),
        ]);
    }

    // ready_to_ship -> processing
    elseif (
        $targetStatus === 'processing' &&
        $order->status === 'ready_to_ship'
    ) {

        $inventoryService->revertOrderToProcessing($order);
    }

    // dispatched -> ready_to_ship  (was previously missing — bug fix)
    elseif (
        $targetStatus === 'ready_to_ship' &&
        in_array($order->status, Order::inTransitStatuses(), true)
    ) {

        $inventoryService->revertOrderToProcessing($order);
    }

    // delivered -> dispatched
    elseif (
        $targetStatus === 'dispatched' &&
        $order->status === 'delivered'
    ) {

        $inventoryService->revertDeliveredToDispatched($order);
    }

    return back()->with(
        'success',
        'Order reverted successfully.'
    );
}
}
