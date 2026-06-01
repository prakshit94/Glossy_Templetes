<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_no',
        'reference_no',
        'transaction_date',
        'description',
        'status',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function entries()
    {
        return $this->hasMany(AccountingEntry::class, 'transaction_id');
    }

    public function getNumberAttribute(): string
    {
        return $this->transaction_no;
    }

    public function getReferenceAttribute(): ?string
    {
        return $this->reference_no;
    }

    public function getPostedAtAttribute()
    {
        return $this->transaction_date;
    }

    public function getTypeAttribute(): string
    {
        return $this->status;
    }

    public function getAccountNameAttribute(): string
    {
        return $this->entries->pluck('ledger.name')->filter()->implode(' / ') ?: '—';
    }

    public function getAmountAttribute(): float
    {
        return (float) $this->entries->sum('debit');
    }
}
