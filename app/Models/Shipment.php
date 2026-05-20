<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'shipment_no',
        'order_id',
        'carrier_name',
        'tracking_no',
        'status',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function events()
    {
        return $this->hasMany(ShipmentTrackingEvent::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
