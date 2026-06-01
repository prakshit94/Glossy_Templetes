<?php

namespace App\Observers;

use App\Models\Delivery;
use App\Services\OrderDeliveryTrackingService;

class DeliveryObserver
{
    public function saved(Delivery $delivery): void
    {
        app(OrderDeliveryTrackingService::class)->syncDelivery($delivery, 'Delivery assignment updated.');
    }
}
