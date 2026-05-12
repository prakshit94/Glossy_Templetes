<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'reference_no',
        'warehouse_id',
        'adjusted_by',
        'reason',
        'status',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function items()
    {
        return $this->hasMany(InventoryAdjustmentItem::class, 'adjustment_id');
    }
}
