<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentTrackingEvent;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with(['order.party', 'events'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('shipment_no', 'like', "%$s%")
                  ->orWhere('tracking_no', 'like', "%$s%")
                  ->orWhereHas('order', function($o) use ($s) {
                      $o->where('order_no', 'like', "%$s%")
                        ->orWhereHas('party', function($p) use ($s) {
                            $p->where('name', 'like', "%$s%");
                        });
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $stats = [
            'total'      => (clone $query)->count(),
            'pending'    => (clone $query)->where('status', 'pending')->count(),
            'in_transit' => (clone $query)->where('status', 'in_transit')->count(),
            'delivered'  => (clone $query)->where('status', 'delivered')->count(),
            'shipped'    => (clone $query)->where('status', 'shipped')->count(),
            'failed'     => (clone $query)->where('status', 'failed')->count(),
        ];

        $shipments = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('order-tracking.partials.table', compact('shipments'))->render(),
                'stats' => $stats
            ]);
        }

        return view('order-tracking.index', compact('shipments', 'stats'));
    }

    public function show($id)
    {
        $shipment = Shipment::with(['order.party', 'order.items.product', 'events' => function($q) {
            $q->latest('occurred_at');
        }])->findOrFail($id);

        return view('order-tracking.show', compact('shipment'));
    }

    public function storeEvent(Request $request, $id)
    {
        $request->validate([
            'event_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'occurred_at' => 'required|date',
            'update_shipment_status' => 'nullable|string',
        ]);

        $shipment = Shipment::findOrFail($id);

        ShipmentTrackingEvent::create([
            'shipment_id' => $shipment->id,
            'event_name' => $request->event_name,
            'location' => $request->location,
            'description' => $request->description,
            'occurred_at' => $request->occurred_at,
        ]);

        if ($request->filled('update_shipment_status')) {
            $shipment->update(['status' => $request->update_shipment_status]);
            
            if ($request->update_shipment_status === 'delivered') {
                $shipment->update(['delivered_at' => $request->occurred_at]);
                $shipment->order->update(['status' => 'delivered']);
            }
        }

        return back()->with('success', 'Tracking event added successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,shipped,in_transit,delivered,failed',
            'carrier_name' => 'nullable|string|max:255',
            'tracking_no' => 'nullable|string|max:255',
        ]);

        $shipment = Shipment::findOrFail($id);
        $shipment->update($request->only(['status', 'carrier_name', 'tracking_no']));

        if ($request->status === 'delivered') {
            $shipment->update(['delivered_at' => now()]);
            $shipment->order->update(['status' => 'delivered']);
        }

        return back()->with('success', 'Shipment status updated.');
    }
}
