<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shipment_id',
        'driver_id',
        'transport_id',
        'status',
        'delivered_at',
        'proof_of_delivery',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function getDeliveryNumberAttribute(): string
    {
        return 'DLV-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getNumberAttribute(): string
    {
        return $this->delivery_number;
    }

    public function getDestinationAttribute(): string
    {
        if ($this->shipment && $this->shipment->order) {
            $order = $this->shipment->order;
            if ($order->shippingAddress) {
                $addr = $order->shippingAddress;
                return $addr->address_line_1 . ', ' . $addr->city . ' ' . $addr->pincode;
            }
            return $order->shipping_address ?? 'Customer Address';
        }
        return 'No Destination';
    }

    public function getAddressAttribute(): string
    {
        return $this->destination;
    }

    public function getWindowAttribute(): string
    {
        return '9 AM - 6 PM';
    }

    public function getSlotAttribute(): string
    {
        return $this->window;
    }

    public function getDriverNameAttribute(): string
    {
        return $this->driver ? $this->driver->name : 'Unassigned';
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function transport()
    {
        return $this->belongsTo(Transport::class);
    }

    public function verificationLogs()
    {
        return $this->hasMany(DeliveryVerificationLog::class)->latest();
    }
}
