<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'license_number',
        'phone',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user ? $this->user->name : 'Unknown Driver';
    }

    public function getVehicleAttribute(): string
    {
        $latestDelivery = $this->deliveries()->with('transport')->latest()->first();
        return $latestDelivery && $latestDelivery->transport ? $latestDelivery->transport->vehicle_number : 'Not Assigned';
    }

    public function getLicenseExpiresAtAttribute(): string
    {
        return now()->addYears(2)->toIso8601String();
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
