<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ledger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'opening_balance',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];

    public function entries()
    {
        return $this->hasMany(AccountingEntry::class);
    }

    public function getBalanceAttribute(): float
    {
        $debit = (float) $this->entries_sum_debit;
        $credit = (float) $this->entries_sum_credit;

        return (float) $this->opening_balance + $debit - $credit;
    }
}
