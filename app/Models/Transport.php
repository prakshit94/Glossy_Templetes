<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'vehicle_number',
        'type',
        'capacity_weight',
        'status',
    ];

    // Accessors to support table view attributes cleanly
    public function getVehicleAttribute(): string
    {
        return $this->vehicle_number;
    }

    public function getVehiclePlateAttribute(): string
    {
        return $this->vehicle_number;
    }

    public function getRouteNameAttribute(): string
    {
        return $this->type ?? 'Local Route';
    }

    public function getScheduleAttribute(): string
    {
        return 'Daily (Cap: ' . number_format($this->capacity_weight, 0) . 'kg)';
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
