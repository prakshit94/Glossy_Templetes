<?php

namespace App\Observers;

use App\Models\Shipment;
use App\Services\OrderDeliveryTrackingService;

class ShipmentObserver
{
    public function saved(Shipment $shipment): void
    {
        if ($shipment->order_id) {
            app(OrderDeliveryTrackingService::class)->syncShipment($shipment, 'Shipment information updated.');
        }
    }
}
