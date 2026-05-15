<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'quantity',
        'reserved_qty',
        'dispatched_qty',
        'committed_qty',
        'in_transit_qty',
        'status',
    ];

    protected $casts = [
        'quantity'        => 'decimal:4',
        'reserved_qty'    => 'decimal:4',
        'dispatched_qty'  => 'decimal:4',
        'committed_qty'   => 'decimal:4',
        'in_transit_qty'  => 'decimal:4',
    ];

    // ─── Computed Attributes ───────────────────────────────────────────────

    /**
     * Available qty = physical stock − reserved stock.
     * This is what can actually be sold/allocated right now.
     */
    public function getAvailableQtyAttribute(): float
    {
        return max(0.0, (float) $this->quantity - (float) $this->reserved_qty);
    }

    // ─── Relationships ─────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class, 'product_id', 'product_id')
                    ->where('warehouse_id', $this->warehouse_id);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id', 'product_id')
                    ->where('warehouse_id', $this->warehouse_id);
    }
}
