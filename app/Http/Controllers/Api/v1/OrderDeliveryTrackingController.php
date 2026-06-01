<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\OrderDeliveryTracking;
use App\Services\OrderDeliveryTrackingService;
use Illuminate\Http\Request;

class OrderDeliveryTrackingController extends Controller
{
    public function index(Request $request, OrderDeliveryTrackingService $trackingService)
    {
        $query = $trackingService->filteredQuery([
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'driver_id' => $request->get('driver_id'),
            'courier' => $request->get('courier'),
            'dispatch_type' => $request->get('dispatch_type'),
            'status' => $request->filled('status') ? explode(',', $request->get('status')) : [],
            'search' => $request->get('search'),
        ]);

        return response()->json([
            'metrics' => $trackingService->summaryMetrics(clone $query),
            'data' => $query->latest('last_status_at')->paginate((int) $request->get('perPage', 15)),
        ]);
    }

    public function show(OrderDeliveryTracking $tracking)
    {
        return response()->json($tracking->load([
            'order.party',
            'shipment.events',
            'delivery.driver.user',
            'delivery.transport',
            'histories.user',
        ]));
    }

    public function performance(Request $request, OrderDeliveryTrackingService $trackingService)
    {
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        return response()->json([
            'partners' => $trackingService->partnerPerformance($filters),
            'couriers' => $trackingService->courierPerformance($filters),
            'monthly' => $trackingService->monthlyPerformance($filters),
        ]);
    }
}
