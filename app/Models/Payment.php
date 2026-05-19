<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_no',
        'invoice_id',
        'order_id',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_date',
        'status',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        $syncInvoice = function ($payment) {
            if ($payment->invoice_id) {
                $invoice = $payment->invoice;
                $paid = $invoice->payments()->where('status', 'completed')->sum('amount');
                if ($paid >= $invoice->net_amount) {
                    $invoice->update(['status' => 'paid']);
                } elseif ($paid > 0) {
                    $invoice->update(['status' => 'partially_paid']);
                } else {
                    $invoice->update(['status' => 'unpaid']);
                }
            }
        };

        static::saved($syncInvoice);
        static::deleted($syncInvoice);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
