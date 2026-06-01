<?php

namespace App\Observers;

use App\Models\OrderReturn;
use App\Services\OrderDeliveryTrackingService;

class OrderReturnObserver
{
    public function saved(OrderReturn $return): void
    {
        app(OrderDeliveryTrackingService::class)->syncReturn($return);
    }
}
