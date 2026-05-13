<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_no', 'type', 'party_id', 'order_date', 'total_amount', 
        'tax_amount', 'discount_amount', 'net_amount', 'status', 'warehouse_id'
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
}
