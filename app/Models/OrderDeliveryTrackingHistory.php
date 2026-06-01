<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDeliveryTrackingHistory extends Model
{
    protected $fillable = [
        'order_delivery_tracking_id',
        'order_id',
        'previous_status',
        'new_status',
        'updated_by',
        'remarks',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function tracking()
    {
        return $this->belongsTo(OrderDeliveryTracking::class, 'order_delivery_tracking_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
