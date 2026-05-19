<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderReturnItem extends Model
{
    protected $table = 'return_items';

    protected $fillable = [
        'return_id',
        'order_item_id',
        'quantity',
    ];

    public function orderReturn()
    {
        return $this->belongsTo(OrderReturn::class, 'return_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
