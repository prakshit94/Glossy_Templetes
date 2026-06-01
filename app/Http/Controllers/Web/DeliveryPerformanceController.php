<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTrackingStatus;
use App\Models\Driver;
use App\Models\OrderDeliveryTracking;
use App\Services\OrderDeliveryTrackingService;
use Illuminate\Http\Request;

class DeliveryPerformanceController extends Controller
{
    public function __construct(private OrderDeliveryTrackingService $trackingService)
    {
    }

    public function index(Request $request)
    {
        $this->trackingService->backfillMissing();

        $filters = $this->filters($request);
        $query = $this->trackingService->filteredQuery($filters);
        $metrics = $this->trackingService->summaryMetrics(clone $query);
        $trackings = $query->latest('last_status_at')->paginate((int) $request->get('perPage', 15))->withQueryString();

        $partnerPerformance = $this->trackingService->partnerPerformance($filters);
        $courierPerformance = $this->trackingService->courierPerformance($filters);
        $monthlyPerformance = $this->trackingService->monthlyPerformance($filters);
        $statuses = DeliveryTrackingStatus::where('is_active', true)->orderBy('sort_order')->get();
        $drivers = Driver::with('user')->orderBy('id')->get();
        $couriers = OrderDeliveryTracking::whereNotNull('courier_provider_name')
            ->distinct()
            ->orderBy('courier_provider_name')
            ->pluck('courier_provider_name');

        return view('delivery-performance.index', compact(
            'metrics',
            'trackings',
            'partnerPerformance',
            'courierPerformance',
            'monthlyPerformance',
            'statuses',
            'drivers',
            'couriers',
            'filters'
        ));
    }

    public function partner(Request $request, Driver $driver)
    {
        $filters = array_merge($this->filters($request), ['driver_id' => $driver->id, 'dispatch_type' => 'lmd']);
        $query = $this->trackingService->filteredQuery($filters);
        $metrics = $this->trackingService->summaryMetrics(clone $query);
        $trackings = $query->latest('last_status_at')->paginate((int) $request->get('perPage', 15))->withQueryString();

        return view('delivery-performance.partner', compact('driver', 'metrics', 'trackings', 'filters'));
    }

    public function courier(Request $request, string $provider)
    {
        $provider = urldecode($provider);
        $filters = array_merge($this->filters($request), ['courier' => $provider, 'dispatch_type' => 'courier']);
        $query = $this->trackingService->filteredQuery($filters);
        $metrics = $this->trackingService->summaryMetrics(clone $query);
        $trackings = $query->latest('last_status_at')->paginate((int) $request->get('perPage', 15))->withQueryString();

        return view('delivery-performance.courier', compact('provider', 'metrics', 'trackings', 'filters'));
    }

    private function filters(Request $request): array
    {
        $statuses = array_filter(array_map('trim', explode(',', (string) $request->get('status', ''))));

        return [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'driver_id' => $request->get('driver_id'),
            'courier' => $request->get('courier'),
            'dispatch_type' => $request->get('dispatch_type'),
            'status' => $statuses,
            'search' => $request->get('search'),
        ];
    }
}
