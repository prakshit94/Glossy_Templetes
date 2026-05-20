<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryVerificationLog;
use App\Models\Driver;
use App\Models\Transport;
use App\Models\Shipment;
use App\Models\ShipmentTrackingEvent;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DeliveryController extends Controller
{
    public static function verificationPayload(Delivery $delivery): array
    {
        $order = $delivery->shipment?->order;
        $party = $order?->party;
        $shipAddr = $order?->shippingAddress;
        $billAddr = $order?->billingAddress;

        $formatAddress = function ($addr) {
            if (!$addr) {
                return null;
            }
            $v = $addr->village;
            return [
                'label' => $addr->label,
                'line1' => $addr->address_line_1,
                'line2' => $addr->address_line_2,
                'village' => $v?->village_name ?? $addr->city,
                'post_office' => $v?->post_so_name,
                'taluka' => $v?->taluka_name,
                'district' => $v?->district_name,
                'state' => $v?->state_name ?? $addr->state,
                'pincode' => $v?->pincode ?? $addr->pincode,
                'country' => $addr->country ?? 'India',
            ];
        };

        $phones = [];
        if ($party) {
            foreach ([
                'Primary' => $party->phone,
                'Alternate' => $party->alternatemobile,
                'Phone 2' => $party->phone_number_2,
                'Relative' => $party->relative_mobile ?? $party->relative_phone,
            ] as $label => $num) {
                if ($num) {
                    $phones[] = ['label' => $label, 'value' => $num];
                }
            }
        }

        $history = $delivery->verificationLogs->map(fn ($log) => [
            'outcome' => $log->outcome,
            'outcome_label' => $log->outcome_label,
            'remark' => $log->remark,
            'follow_up_at' => $log->follow_up_at?->format('M d, Y h:i A'),
            'created_at' => $log->created_at->format('M d, Y h:i A'),
            'user_name' => $log->user?->name,
        ])->values()->all();

        return [
            'id' => $delivery->id,
            'delivery_no' => $delivery->delivery_number,
            'shipment_no' => $delivery->shipment?->shipment_no ?? '—',
            'order_no' => $order?->order_no ?? '—',
            'status' => str_replace('_', ' ', $delivery->status),
            'driver_name' => $delivery->driver_name,
            'vehicle' => $delivery->transport
                ? $delivery->transport->name . ' (' . $delivery->transport->vehicle_number . ')'
                : '—',
            'party_name' => $party?->name ?? ($party?->company_name ?? '—'),
            'phones' => $phones,
            'emails' => array_values(array_filter([$party?->email])),
            'order_amount' => $order ? '₹' . number_format((float) $order->net_amount, 2) : '—',
            'order_date' => $order?->order_date?->format('M d, Y') ?? '—',
            'shipping' => $formatAddress($shipAddr),
            'billing' => $formatAddress($billAddr),
            'legacy_shipping' => $order?->shipping_address,
            'history' => $history,
        ];
    }

    protected function verificationPayloadsFor(Collection $deliveries): array
    {
        return $deliveries->mapWithKeys(
            fn (Delivery $delivery) => [$delivery->id => self::verificationPayload($delivery)]
        )->all();
    }

    public function index(Request $request)
    {
        $query = Delivery::with([
            'shipment.order.party',
            'shipment.order.shippingAddress.village',
            'shipment.order.billingAddress.village',
            'shipment.order.items',
            'driver.user',
            'transport',
            'verificationLogs.user',
        ]);

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

        $verificationPayloads = $this->verificationPayloadsFor($records->getCollection());

        if ($request->ajax()) {
            return response()->json([
                'table' => view('delivery.partials.table', compact('records'))->render(),
                'stats' => $stats,
                'verificationPayloads' => $verificationPayloads,
            ]);
        }

        return view('delivery.index', [
            'moduleKey' => 'delivery',
            'moduleTitle' => 'Delivery',
            'moduleIcon' => 'truck-2',
            'records' => $records,
            'stats' => $stats,
            'verificationPayloads' => $verificationPayloads,
            'availableShipments' => $availableShipments,
            'drivers' => $drivers,
            'transports' => $transports,
        ]);
    }

    public function assign(Request $request, InventoryService $inventoryService)
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

            // If the order is currently ready_to_ship, dispatch it to trigger stock mutations
            if ($shipment->order && $shipment->order->status === 'ready_to_ship') {
                $inventoryService->dispatchOrder($shipment->order);
            }

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

    public function storeVerification(Request $request, Delivery $delivery)
    {
        $outcomes = array_keys(DeliveryVerificationLog::OUTCOMES);

        $validated = $request->validate([
            'outcome' => 'required|in:' . implode(',', $outcomes),
            'remark' => 'nullable|string|max:2000',
            'follow_up_at' => 'nullable|date',
        ]);

        $log = DeliveryVerificationLog::create([
            'delivery_id' => $delivery->id,
            'outcome' => $validated['outcome'],
            'remark' => $validated['remark'] ?? null,
            'follow_up_at' => $validated['follow_up_at'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $shipment = $delivery->shipment;
        $returnNo = null;

        if ($validated['outcome'] === 'return_order') {
            $order = $shipment?->order;
            if (!$order) {
                return back()->with('error', 'Order not found for this delivery. Cannot create return request.');
            }

            $reason = trim($validated['remark'] ?? '');
            if ($reason === '') {
                $reason = 'Return requested via delivery verification (' . $delivery->delivery_number . ')';
            } else {
                $reason .= ' — Delivery verification (' . $delivery->delivery_number . ')';
            }

            try {
                $orderReturn = app(OrderReturnController::class)->createRequestedReturn($order, $reason);
                $returnNo = $orderReturn->return_no;
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->with(
                    'error',
                    collect($e->errors())->flatten()->first() ?? 'Could not create return request.'
                );
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        if ($shipment) {
            $label = $log->outcome_label;
            $parts = ["Delivery verification: {$label}"];
            if (!empty($validated['remark'])) {
                $parts[] = $validated['remark'];
            }
            if (!empty($validated['follow_up_at'])) {
                $parts[] = 'Follow-up: ' . \Carbon\Carbon::parse($validated['follow_up_at'])->format('M d, Y h:i A');
            }
            if ($returnNo) {
                $parts[] = "Return request created: {$returnNo}";
            }
            $parts[] = 'By: ' . (auth()->user()->name ?? 'Staff');

            ShipmentTrackingEvent::create([
                'shipment_id' => $shipment->id,
                'event_name' => 'Delivery Verification',
                'location' => $delivery->destination,
                'description' => implode(' | ', $parts),
                'occurred_at' => now(),
            ]);
        }

        $message = 'Verification call logged successfully.';
        if ($returnNo) {
            $message = "Verification logged and return request {$returnNo} created (visible on Returns).";
        }

        return back()->with('success', $message);
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
