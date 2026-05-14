<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_no', 'type', 'party_id', 'order_date', 'total_amount', 
        'tax_amount', 'discount_amount', 'net_amount', 'status', 'warehouse_id',
        'shipping_address_id', 'billing_address_id', 'billing_address', 'shipping_address'
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class, 'party_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(PartyAddress::class, 'shipping_address_id');
    }

    public function billingAddress()
    {
        return $this->belongsTo(PartyAddress::class, 'billing_address_id');
    }
}
