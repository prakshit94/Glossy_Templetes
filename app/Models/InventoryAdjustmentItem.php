<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustmentItem extends Model
{
    protected $fillable = [
        'adjustment_id',
        'product_id',
        'current_qty',
        'new_qty',
        'difference',
    ];

    protected $casts = [
        'current_qty' => 'float',
        'new_qty'     => 'float',
        'difference'  => 'float',
    ];

    public function adjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class, 'adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
