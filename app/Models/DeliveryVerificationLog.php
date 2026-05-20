<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryVerificationLog extends Model
{
    public const OUTCOMES = [
        'call_not_picked' => 'Call Not Picked',
        'not_available_at_home' => 'Not Available at Home',
        'customer_confirmed' => 'Customer Confirmed Delivery',
        'reschedule_delivery' => 'Reschedule Delivery',
        'next_followup_call' => 'Next Follow-up Call',
        'cancel_order' => 'Cancel Order',
        'return_order' => 'Return Order',
        'wrong_number' => 'Wrong Number',
        'other' => 'Other',
    ];

    protected $fillable = [
        'delivery_id',
        'outcome',
        'remark',
        'follow_up_at',
        'created_by',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getOutcomeLabelAttribute(): string
    {
        return self::OUTCOMES[$this->outcome] ?? ucfirst(str_replace('_', ' ', $this->outcome));
    }
}
