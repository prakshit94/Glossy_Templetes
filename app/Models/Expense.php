<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category',
        'amount',
        'date',
        'user_id',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNumberAttribute(): string
    {
        return 'EXP-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getVendorNameAttribute(): string
    {
        return $this->user?->name ?? 'Internal';
    }
}
