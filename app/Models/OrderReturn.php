<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderReturn extends Model
{
    use SoftDeletes;
    
    protected $table = 'returns';

    protected $fillable = [
        'return_no',
        'order_id',
        'reason',
        'refund_amount',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(OrderReturnItem::class, 'return_id');
    }
}
