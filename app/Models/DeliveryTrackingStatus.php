<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryTrackingStatus extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_terminal',
        'is_active',
    ];

    protected $casts = [
        'is_terminal' => 'boolean',
        'is_active' => 'boolean',
    ];
}
