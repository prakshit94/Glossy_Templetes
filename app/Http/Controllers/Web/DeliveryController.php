<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Driver;
use App\Models\Transport;
use App\Models\Shipment;
use App\Models\ShipmentTrackingEvent;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['shipment.order.party', 'driver.user', 'transport']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->whereHas('shipment', function($shp) use ($s) {
                    $shp->where('shipment_no', 'like', "%$s%")
                        ->orWhereHas('order', function($o) use ($s) {
                            $o->where('order_no', 'like', "%$s%")
                              ->orWhereHas('party', function($p) use ($s) {
                                  $p->where('firstname', 'like', "%$s%")
                                    ->orWhere('lastname', 'like', "%$s%");
                              });
                        });
                })
                ->orWhereHas('driver.user', function($u) use ($s) {
                    $u->where('name', 'like', "%$s%");
                })
                ->orWhereHas('transport', function($t) use ($s) {
                    $t->where('name', 'like', "%$s%")
                      ->orWhere('vehicle_number', 'like', "%$s%");
                });
            });
        }

        $perPage = $request->input('perPage', 10);
        $records = $query->latest()->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Delivery::count(),
            'pending' => Delivery::where('status', 'pending')->count(),
            'out_for_delivery' => Delivery::where('status', 'out_for_delivery')->count(),
            'delivered' => Delivery::where('status', 'delivered')->count(),
            'failed' => Delivery::where('status', 'failed')->count(),
        ];

        // Fetch pending shipments (shipments that don't have a delivery yet, or aren't delivered)
        $availableShipments = Shipment::where('status', '!=', 'delivered')
            ->whereDoesntHave('deliveries', function($q) {
                $q->whereIn('status', ['out_for_delivery', 'delivered']);
            })
            ->with(['order.party'])
            ->get();

        $drivers = Driver::where('status', 'available')->with('user')->get();
        $transports = Transport::where('status', 'available')->get();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('delivery.partials.table', compact('records'))->render(),
                'stats' => $stats
            ]);
        }

        return view('delivery.index', [
            'moduleKey' => 'delivery',
            'moduleTitle' => 'Delivery',
            'moduleIcon' => 'truck-2',
            'records' => $records,
            'stats' => $stats,
            'availableShipments' => $availableShipments,
            'drivers' => $drivers,
            'transports' => $transports,
        ]);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'shipment_ids' => 'required|array',
            'shipment_ids.*' => 'exists:shipments,id',
            'driver_id' => 'required|exists:drivers,id',
            'transport_id' => 'required|exists:transports,id',
        ]);

        $driver = Driver::findOrFail($request->driver_id);
        $transport = Transport::findOrFail($request->transport_id);

        foreach ($request->shipment_ids as $shipmentId) {
            $shipment = Shipment::findOrFail($shipmentId);

            // Create Delivery Record
            Delivery::create([
                'shipment_id' => $shipment->id,
                'driver_id' => $driver->id,
                'transport_id' => $transport->id,
                'status' => 'out_for_delivery',
            ]);

            // Update Shipment status
            $shipment->update(['status' => 'in_transit']);

            // Create tracking event
            ShipmentTrackingEvent::create([
                'shipment_id' => $shipment->id,
                'event_name' => 'Dispatched / Out for Delivery',
                'location' => $shipment->order && $shipment->order->warehouse ? $shipment->order->warehouse->name : 'Warehouse',
                'description' => "Assigned to driver {$driver->name} using transport vehicle {$transport->name} ({$transport->vehicle_number}).",
                'occurred_at' => now(),
            ]);
        }

        // Update Driver and Transport status
        $driver->update(['status' => 'busy']);
        $transport->update(['status' => 'on_delivery']);

        return back()->with('success', 'Order shipments successfully assigned to transport and driver. Out for delivery!');
    }

    public function markDelivered(Request $request, $id, InventoryService $inventoryService)
    {
        $delivery = Delivery::findOrFail($id);
        $shipment = $delivery->shipment;

        if (!$shipment) {
            return back()->with('error', 'Shipment record not found for this delivery.');
        }

        $order = $shipment->order;
        if (!$order) {
            return back()->with('error', 'Order record not found for this shipment.');
        }

        // Complete delivery
        $delivery->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        // Release driver and transport
        if ($delivery->driver) {
            $delivery->driver->update(['status' => 'available']);
        }
        if ($delivery->transport) {
            $delivery->transport->update(['status' => 'available']);
        }

        // Add tracking event
        ShipmentTrackingEvent::create([
            'shipment_id' => $shipment->id,
            'event_name' => 'Delivered',
            'location' => $delivery->destination,
            'description' => "Order delivered successfully by driver {$delivery->driver_name}.",
            'occurred_at' => now(),
        ]);

        // Transition Order and Shipment to delivered status
        try {
            $inventoryService->deliverOrder($order);
        } catch (\Exception $e) {
            // Fallback in case of inventory service restrictions
            $shipment->update(['status' => 'delivered', 'delivered_at' => now()]);
            $order->update(['status' => 'delivered']);
        }

        return back()->with('success', 'Order shipment successfully delivered and completed!');
    }

    public function destroy(Delivery $delivery)
    {
        // Release driver and transport if deleting
        if ($delivery->driver) {
            $delivery->driver->update(['status' => 'available']);
        }
        if ($delivery->transport) {
            $delivery->transport->update(['status' => 'available']);
        }

        $delivery->delete();
        return back()->with('success', 'Delivery assignment deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No deliveries selected.');

        $deliveries = Delivery::whereIn('id', $ids)->get();
        foreach ($deliveries as $delivery) {
            if ($delivery->driver) {
                $delivery->driver->update(['status' => 'available']);
            }
            if ($delivery->transport) {
                $delivery->transport->update(['status' => 'available']);
            }
            $delivery->delete();
        }

        return back()->with('success', count($ids) . ' deliveries deleted successfully.');
    }
}
