<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDeliveryTracking extends Model
{
    protected $fillable = [
        'order_id',
        'shipment_id',
        'delivery_id',
        'parcel_id',
        'dispatch_type',
        'driver_id',
        'transport_id',
        'service_id',
        'courier_provider_name',
        'tracking_number',
        'vehicle_number',
        'current_status',
        'dispatch_date',
        'delivered_date',
        'returned_date',
        'last_status_at',
        'last_remarks',
    ];

    protected $casts = [
        'dispatch_date' => 'datetime',
        'delivered_date' => 'datetime',
        'returned_date' => 'datetime',
        'last_status_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function transport()
    {
        return $this->belongsTo(Transport::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function histories()
    {
        return $this->hasMany(OrderDeliveryTrackingHistory::class)->latest('changed_at');
    }

    public function getAssignedNameAttribute(): string
    {
        if ($this->dispatch_type === 'lmd') {
            return $this->driver?->name ?? 'Unassigned Partner';
        }

        return $this->courier_provider_name ?: 'Unassigned Courier';
    }

    public function getAverageDeliveryHoursAttribute(): ?float
    {
        if (!$this->dispatch_date || !$this->delivered_date) {
            return null;
        }

        return round($this->dispatch_date->floatDiffInHours($this->delivered_date), 2);
    }
}
