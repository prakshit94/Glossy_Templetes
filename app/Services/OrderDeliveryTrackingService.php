<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderDeliveryTracking;
use App\Models\OrderReturn;
use App\Models\Service;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderDeliveryTrackingService
{
    public const FINAL_STATUSES = ['delivered', 'returned', 'lost', 'cancelled'];
    public const PENDING_STATUSES = ['created', 'ready_for_dispatch', 'dispatched', 'in_transit', 'out_for_delivery', 'return_initiated'];

    public function syncOrder(Order $order, ?string $remarks = null): OrderDeliveryTracking
    {
        $order->loadMissing(['shipments.deliveries.driver.user', 'shipments.deliveries.transport', 'shipments.events']);
        $shipment = $order->shipments->sortByDesc('created_at')->first();

        return $shipment
            ? $this->syncShipment($shipment, $remarks)
            : $this->upsertTracking($order, null, null, $this->mapOrderStatus($order->status), $remarks);
    }

    public function syncShipment(Shipment $shipment, ?string $remarks = null): OrderDeliveryTracking
    {
        $shipment->loadMissing(['order', 'deliveries.driver.user', 'deliveries.transport']);
        $delivery = $shipment->deliveries->sortByDesc('created_at')->first();

        return $this->upsertTracking(
            $shipment->order,
            $shipment,
            $delivery,
            $this->mapShipmentStatus($shipment),
            $remarks
        );
    }

    public function syncDelivery(Delivery $delivery, ?string $remarks = null): ?OrderDeliveryTracking
    {
        $delivery->loadMissing(['shipment.order', 'driver.user', 'transport']);

        if (!$delivery->shipment?->order) {
            return null;
        }

        return $this->upsertTracking(
            $delivery->shipment->order,
            $delivery->shipment,
            $delivery,
            $this->mapDeliveryStatus($delivery),
            $remarks
        );
    }

    public function syncReturn(OrderReturn $return, ?string $remarks = null): ?OrderDeliveryTracking
    {
        $return->loadMissing(['order.shipments.deliveries.driver.user', 'order.shipments.deliveries.transport']);

        if (!$return->order) {
            return null;
        }

        $shipment = $return->order->shipments->sortByDesc('created_at')->first();
        $delivery = $shipment?->deliveries?->sortByDesc('created_at')->first();
        $status = $return->status === 'completed' ? 'returned' : 'return_initiated';

        return $this->upsertTracking(
            $return->order,
            $shipment,
            $delivery,
            $status,
            $remarks ?? "Return {$return->return_no} status: {$return->status}"
        );
    }

    public function updateStatus(OrderDeliveryTracking $tracking, string $status, ?string $remarks = null): OrderDeliveryTracking
    {
        return DB::transaction(function () use ($tracking, $status, $remarks) {
            $tracking = OrderDeliveryTracking::lockForUpdate()->findOrFail($tracking->id);
            $this->writeHistory($tracking, $status, $remarks);
            $updates = [
                'current_status' => $status,
                'last_status_at' => now(),
                'last_remarks' => $remarks,
            ];

            if ($status === 'delivered' && !$tracking->delivered_date) {
                $updates['delivered_date'] = now();
            }
            if ($status === 'returned' && !$tracking->returned_date) {
                $updates['returned_date'] = now();
            }

            $tracking->update($updates);

            return $tracking->refresh();
        });
    }

    public function filteredQuery(array $filters = []): Builder
    {
        return OrderDeliveryTracking::query()
            ->with(['order.party', 'shipment', 'delivery', 'driver.user', 'transport', 'histories.user'])
            ->when(!empty($filters['date_from']), fn ($q) => $q->whereDate('dispatch_date', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn ($q) => $q->whereDate('dispatch_date', '<=', $filters['date_to']))
            ->when(!empty($filters['driver_id']), fn ($q) => $q->where('driver_id', $filters['driver_id']))
            ->when(!empty($filters['courier']), fn ($q) => $q->where('courier_provider_name', $filters['courier']))
            ->when(!empty($filters['dispatch_type']), fn ($q) => $q->where('dispatch_type', $filters['dispatch_type']))
            ->when(!empty($filters['status']), fn ($q) => $q->whereIn('current_status', (array) $filters['status']))
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $search = trim($filters['search']);
                $q->where(function ($inner) use ($search) {
                    $inner->where('tracking_number', 'like', "%{$search}%")
                        ->orWhere('parcel_id', 'like', "%{$search}%")
                        ->orWhere('vehicle_number', 'like', "%{$search}%")
                        ->orWhere('courier_provider_name', 'like', "%{$search}%")
                        ->orWhereHas('order', fn ($order) => $order->where('order_no', 'like', "%{$search}%"))
                        ->orWhereHas('order.party', function ($party) use ($search) {
                            $party->where('firstname', 'like', "%{$search}%")
                                ->orWhere('lastname', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            });
    }

    public function summaryMetrics(Builder $query): array
    {
        $total = (clone $query)->count();
        $delivered = (clone $query)->where('current_status', 'delivered')->count();
        $returned = (clone $query)->where('current_status', 'returned')->count();
        $failed = (clone $query)->where('current_status', 'delivery_failed')->count();
        $pending = (clone $query)->whereIn('current_status', self::PENDING_STATUSES)->count();

        return [
            'total' => $total,
            'delivered' => $delivered,
            'returned' => $returned,
            'pending' => $pending,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($delivered / $total) * 100, 2) : 0,
            'return_rate' => $total > 0 ? round(($returned / $total) * 100, 2) : 0,
        ];
    }

    public function partnerPerformance(array $filters = [])
    {
        return $this->filteredQuery($filters)
            ->where('dispatch_type', 'lmd')
            ->whereNotNull('driver_id')
            ->selectRaw('driver_id,
                COUNT(*) as total_orders,
                SUM(current_status = ?) as delivered_orders,
                SUM(current_status = ?) as returned_orders,
                SUM(current_status = ?) as failed_deliveries,
                SUM(current_status IN (?, ?, ?, ?, ?, ?)) as pending_deliveries,
                AVG(CASE WHEN delivered_date IS NOT NULL AND dispatch_date IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, dispatch_date, delivered_date) END) as avg_delivery_minutes',
                ['delivered', 'returned', 'delivery_failed', ...self::PENDING_STATUSES])
            ->groupBy('driver_id')
            ->with('driver.user')
            ->orderByDesc('delivered_orders')
            ->get();
    }

    public function courierPerformance(array $filters = [])
    {
        return $this->filteredQuery($filters)
            ->where('dispatch_type', 'courier')
            ->whereNotNull('courier_provider_name')
            ->selectRaw('courier_provider_name,
                COUNT(*) as total_orders,
                SUM(current_status = ?) as delivered_orders,
                SUM(current_status = ?) as returned_orders,
                SUM(current_status = ?) as failed_deliveries,
                SUM(current_status IN (?, ?, ?, ?, ?, ?)) as pending_deliveries,
                AVG(CASE WHEN delivered_date IS NOT NULL AND dispatch_date IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, dispatch_date, delivered_date) END) as avg_delivery_minutes',
                ['delivered', 'returned', 'delivery_failed', ...self::PENDING_STATUSES])
            ->groupBy('courier_provider_name')
            ->orderByDesc('delivered_orders')
            ->get();
    }

    public function monthlyPerformance(array $filters = [])
    {
        return $this->filteredQuery($filters)
            ->whereNotNull('dispatch_date')
            ->selectRaw("DATE_FORMAT(dispatch_date, '%Y-%m') as month,
                dispatch_type,
                COALESCE(CAST(driver_id AS CHAR), courier_provider_name, 'Unassigned') as handler_key,
                MAX(courier_provider_name) as courier_provider_name,
                MAX(driver_id) as driver_id,
                COUNT(*) as assigned_orders,
                SUM(current_status = ?) as delivered_orders,
                SUM(current_status = ?) as returned_orders,
                SUM(current_status IN (?, ?, ?, ?, ?, ?)) as pending_orders",
                ['delivered', 'returned', ...self::PENDING_STATUSES])
            ->groupBy('month', 'dispatch_type', 'handler_key')
            ->orderByDesc('month')
            ->limit(200)
            ->get();
    }

    public function backfillMissing(int $limit = 500): int
    {
        $count = 0;

        Shipment::with(['order', 'deliveries.driver.user', 'deliveries.transport'])
            ->whereHas('order')
            ->whereDoesntHave('deliveryTracking')
            ->latest()
            ->limit($limit)
            ->get()
            ->each(function (Shipment $shipment) use (&$count) {
                $this->syncShipment($shipment, 'Backfilled from existing shipment data.');
                $count++;
            });

        Order::whereDoesntHave('deliveryTracking')
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'ready_to_ship', 'dispatched', 'shipped', 'delivered', 'cancelled', 'returned'])
            ->latest()
            ->limit($limit)
            ->get()
            ->each(function (Order $order) use (&$count) {
                $this->syncOrder($order, 'Backfilled from existing order data.');
                $count++;
            });

        return $count;
    }

    private function upsertTracking(
        ?Order $order,
        ?Shipment $shipment,
        ?Delivery $delivery,
        string $status,
        ?string $remarks = null
    ): OrderDeliveryTracking {
        if (!$order) {
            throw new \InvalidArgumentException('Order is required for delivery tracking.');
        }

        return DB::transaction(function () use ($order, $shipment, $delivery, $status, $remarks) {
            $tracking = OrderDeliveryTracking::where('order_id', $order->id)->lockForUpdate()->first();
            $dispatchType = $this->resolveDispatchType($shipment, $delivery);
            $providerName = $shipment?->carrier_name;
            $serviceId = $providerName ? Service::where('name', $providerName)->value('id') : null;
            $changedAt = $this->resolveChangedAt($status, $shipment, $delivery);

            $payload = [
                'shipment_id' => $shipment?->id,
                'delivery_id' => $delivery?->id,
                'parcel_id' => $shipment?->shipment_no,
                'dispatch_type' => $dispatchType,
                'driver_id' => $delivery?->driver_id,
                'transport_id' => $delivery?->transport_id,
                'service_id' => $serviceId,
                'courier_provider_name' => $providerName,
                'tracking_number' => $shipment?->tracking_no,
                'vehicle_number' => $delivery?->transport?->vehicle_number,
                'dispatch_date' => $this->resolveDispatchDate($shipment, $delivery),
                'delivered_date' => $this->resolveDeliveredDate($shipment, $delivery, $status),
                'returned_date' => $status === 'returned' ? now() : null,
                'last_status_at' => $changedAt,
                'last_remarks' => $remarks,
            ];

            if (!$tracking) {
                $tracking = OrderDeliveryTracking::create(array_merge($payload, [
                    'order_id' => $order->id,
                    'current_status' => $status,
                ]));
                $this->writeHistory($tracking, $status, $remarks, null, $changedAt, false);
                return $tracking->refresh();
            }

            $previousStatus = $tracking->current_status;
            $payload['current_status'] = $status;

            if ($tracking->delivered_date && empty($payload['delivered_date'])) {
                unset($payload['delivered_date']);
            }
            if ($tracking->returned_date && empty($payload['returned_date'])) {
                unset($payload['returned_date']);
            }

            $tracking->update($payload);

            if ($previousStatus !== $status) {
                $this->writeHistory($tracking, $status, $remarks, $previousStatus, $changedAt);
            }

            return $tracking->refresh();
        });
    }

    private function writeHistory(
        OrderDeliveryTracking $tracking,
        string $newStatus,
        ?string $remarks = null,
        ?string $previousStatus = null,
        ?Carbon $changedAt = null,
        bool $useCurrentAsPrevious = true
    ): void {
        $tracking->histories()->create([
            'order_id' => $tracking->order_id,
            'previous_status' => $previousStatus ?? ($useCurrentAsPrevious ? $tracking->current_status : null),
            'new_status' => $newStatus,
            'updated_by' => auth()->id(),
            'remarks' => $remarks,
            'changed_at' => $changedAt ?? now(),
        ]);
    }

    private function resolveDispatchType(?Shipment $shipment, ?Delivery $delivery): string
    {
        if ($delivery?->driver_id) {
            return 'lmd';
        }
        if ($shipment?->carrier_name || $shipment?->tracking_no) {
            return 'courier';
        }

        return 'unknown';
    }

    private function mapOrderStatus(string $status): string
    {
        return match ($status) {
            'pending', 'confirmed', 'processing' => 'created',
            'ready_to_ship' => 'ready_for_dispatch',
            'dispatched', 'shipped' => 'dispatched',
            'delivered' => 'delivered',
            'returned' => 'returned',
            'cancelled' => 'cancelled',
            default => 'created',
        };
    }

    private function mapShipmentStatus(Shipment $shipment): string
    {
        return match ($shipment->status) {
            'pending' => 'ready_for_dispatch',
            'shipped' => 'dispatched',
            'in_transit' => 'in_transit',
            'delivered' => 'delivered',
            'failed' => 'delivery_failed',
            default => $this->mapOrderStatus($shipment->order?->status ?? 'pending'),
        };
    }

    private function mapDeliveryStatus(Delivery $delivery): string
    {
        return match ($delivery->status) {
            'pending' => 'dispatched',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'failed' => 'delivery_failed',
            default => $this->mapShipmentStatus($delivery->shipment),
        };
    }

    private function resolveDispatchDate(?Shipment $shipment, ?Delivery $delivery): ?Carbon
    {
        return $delivery?->created_at ?? $shipment?->shipped_at ?? $shipment?->created_at;
    }

    private function resolveDeliveredDate(?Shipment $shipment, ?Delivery $delivery, string $status): ?Carbon
    {
        if ($status !== 'delivered') {
            return null;
        }

        return $delivery?->delivered_at ?? $shipment?->delivered_at ?? now();
    }

    private function resolveChangedAt(string $status, ?Shipment $shipment, ?Delivery $delivery): Carbon
    {
        if ($status === 'delivered') {
            return $delivery?->delivered_at ?? $shipment?->delivered_at ?? now();
        }

        return now();
    }
}
