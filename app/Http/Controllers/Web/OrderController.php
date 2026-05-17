<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Warehouse;
use App\Models\Party;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['party', 'warehouse'])->withCount('items');

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where('order_no', 'like', "%$s%")
                  ->orWhereHas('party', function ($q) use ($s) {
                      $q->where('name', 'like', "%$s%");
                  });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTERS
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            $statuses = array_filter(array_map('trim', explode(',', $request->status)));
            $query->whereIn('status', $statuses);
        }

        /*
        |--------------------------------------------------------------------------
        | GEOGRAPHIC FILTERS
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | STATS & DATA
        |--------------------------------------------------------------------------
        */
        $stats = [
            'total'      => (clone $query)->count(),
            'pending'    => (clone $query)->where('status', 'pending')->count(),
            'processing' => (clone $query)->where('status', 'processing')->count(),
            'shipped'    => (clone $query)->where('status', 'shipped')->count(),
        ];

        $perPage = (int) $request->get('perPage', 10);
        $orders  = $query->latest()->paginate($perPage)->withQueryString();

        $statusesList = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];

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
            'type'                  => 'required|in:sale,purchase',
            'party_id'              => 'required|exists:parties,id',
            'warehouse_id'          => 'required|exists:warehouses,id',
            'order_date'            => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit_price'    => 'required|numeric|min:0',
        ]);

        $party = Party::findOrFail($request->party_id);
        if ($request->type === 'sale' && $party->type !== 'customer') {
            return back()->withInput()->with('error', 'Sale orders require a customer party.');
        }
        if ($request->type === 'purchase' && $party->type !== 'supplier') {
            return back()->withInput()->with('error', 'Purchase orders require a supplier party.');
        }

        try {
            $orderService->createOrder([
                'type'         => $request->type,
                'party_id'     => $request->party_id,
                'warehouse_id' => $request->warehouse_id,
                'order_date'   => $request->order_date,
                'items'        => $request->items,
            ]);
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', collect($e->errors())->flatten()->first() ?? 'Failed to create order.');
        }

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    public function edit(Order $order)
    {
        // Seamlessly route customer orders to the Customer Profile cart interface
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
            'warehouse',
            'items.product',
            'creator',
            'updater',
            'shippingAddress.village',
            'billingAddress.village',
            'shipments',
        ])->findOrFail($id);

        $services = \App\Models\Service::active()->get();

        return view('orders.show', compact('order', 'services'));
    }

    /*
    |--------------------------------------------------------------------------
    | Status Transitions – all go through InventoryService
    |--------------------------------------------------------------------------
    */

    public function confirm(string $id, InventoryService $inventoryService)
    {
        $order = Order::findOrFail($id);

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
            return back()->with('error', 'Only confirmed or processing orders can be shipped.');
        }

        $request->validate([
            'carrier_name' => 'nullable|string|max:255',
            'tracking_no' => 'nullable|string|max:255',
        ]);

        try {
            $inventoryService->shipOrder($order, $request->carrier_name, $request->tracking_no);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first() ?? 'Unable to ship order.');
        }

        return back()->with('success', 'Order shipped and inventory updated.');
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

    public function markDelivered(string $id, OrderService $orderService)
    {
        $order = Order::findOrFail($id);

        if (!in_array($order->status, ['shipped', 'processing'])) {
            return back()->with('error', 'Only shipped or processing orders can be marked as delivered.');
        }

        $orderService->updateStatus($order, 'delivered');

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

    /*
    |--------------------------------------------------------------------------
    | Bulk Status – ONLY safe transitions (no inventory side-effects)
    | Inventory-impacting transitions (confirm, ship, cancel) must use
    | their dedicated endpoints above, never this bulk route.
    |--------------------------------------------------------------------------
    */
    public function bulkStatus(Request $request, InventoryService $inventoryService, OrderService $orderService)
    {
        $request->validate([
            'ids'    => 'required|json',
            'status' => 'required|string|in:pending,confirmed,processing,shipped,delivered,cancelled,returned',
        ]);

        $ids = json_decode($request->ids);
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No orders selected.');
        }

        $targetStatus = $request->status;
        $count = 0;

        try {
            \DB::transaction(function() use ($ids, $targetStatus, $inventoryService, $orderService, &$count) {
                $orders = Order::with('items')->whereIn('id', $ids)->get();

                foreach ($orders as $order) {
                    if ($order->status === $targetStatus) {
                        continue;
                    }

                    // ─── FORWARD TRANSITIONS ───
                    if ($targetStatus === 'confirmed' && $order->status === 'pending') {
                        $inventoryService->confirmOrder($order);
                        $count++;
                    } elseif ($targetStatus === 'processing' && $order->status === 'confirmed') {
                        $orderService->updateStatus($order, 'processing');
                        $count++;
                    } elseif ($targetStatus === 'shipped' && in_array($order->status, ['confirmed', 'processing'])) {
                        $inventoryService->shipOrder($order, null, null);
                        $count++;
                    } elseif ($targetStatus === 'delivered' && in_array($order->status, ['shipped', 'processing'])) {
                        $orderService->updateStatus($order, 'delivered');
                        $count++;
                    } elseif ($targetStatus === 'cancelled' && !in_array($order->status, ['delivered', 'cancelled', 'returned'])) {
                        $inventoryService->cancelOrder($order);
                        $count++;
                    }

                    // ─── REVERT TRANSITIONS ───
                    elseif ($targetStatus === 'pending' && in_array($order->status, ['confirmed', 'processing', 'cancelled'])) {
                        if (in_array($order->status, ['confirmed', 'processing']) && $order->type === 'sale' && $order->warehouse_id) {
                            foreach ($order->items as $item) {
                                $stock = $inventoryService->getStock((int)$item->product_id, (int)$order->warehouse_id);
                                $releaseQty = min((float)$item->quantity, (float)$stock->reserved_qty);
                                if ($releaseQty > 0) {
                                    \DB::table('stocks')->where('id', $stock->id)->update([
                                        'reserved_qty' => (float)$stock->reserved_qty - $releaseQty
                                    ]);
                                    \App\Models\StockReservation::where('order_id', $order->id)
                                        ->where('product_id', $item->product_id)
                                        ->where('warehouse_id', $order->warehouse_id)
                                        ->where('status', 'active')
                                        ->update(['status' => 'cancelled']);
                                }
                            }
                        }
                        $order->update(['status' => 'pending']);
                        $count++;
                    } elseif ($targetStatus === 'confirmed' && $order->status === 'processing') {
                        $order->update(['status' => 'confirmed']);
                        $count++;
                    } elseif ($targetStatus === 'processing' && $order->status === 'shipped') {
                        $shipments = \App\Models\Shipment::where('order_id', $order->id)->get();
                        foreach ($shipments as $shp) {
                            \App\Models\ShipmentTrackingEvent::where('shipment_id', $shp->id)->delete();
                            $shp->delete();
                        }
                        if ($order->type === 'sale' && $order->warehouse_id) {
                            foreach ($order->items as $item) {
                                $stock = $inventoryService->getStock((int)$item->product_id, (int)$order->warehouse_id);
                                \DB::table('stocks')->where('id', $stock->id)->update([
                                    'quantity' => (float)$stock->quantity + (float)$item->quantity,
                                    'reserved_qty' => (float)$stock->reserved_qty + (float)$item->quantity,
                                    'dispatched_qty' => max(0.0, (float)$stock->dispatched_qty - (float)$item->quantity),
                                ]);
                                \App\Models\StockReservation::where('order_id', $order->id)
                                    ->where('product_id', $item->product_id)
                                    ->where('warehouse_id', $order->warehouse_id)
                                    ->update(['status' => 'active']);
                            }
                        } elseif ($order->type === 'purchase' && $order->warehouse_id) {
                            foreach ($order->items as $item) {
                                $stock = $inventoryService->getStock((int)$item->product_id, (int)$order->warehouse_id);
                                \DB::table('stocks')->where('id', $stock->id)->update([
                                    'quantity' => max(0.0, (float)$stock->quantity - (float)$item->quantity),
                                ]);
                            }
                        }
                        $order->update(['status' => 'processing']);
                        $count++;
                    } elseif ($targetStatus === 'shipped' && $order->status === 'delivered') {
                        $order->update(['status' => 'shipped']);
                        $count++;
                    }
                }
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing status update: ' . $e->getMessage());
        }

        return back()->with('success', $count . ' orders updated successfully.');
    }

    public function destroy(Order $order)
    {
        if (!in_array($order->status, ['pending', 'cancelled'])) {
            return back()->with('error', 'Only pending or cancelled orders can be deleted.');
        }
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted.');
    }
}
