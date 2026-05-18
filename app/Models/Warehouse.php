<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name', 'company_name', 'gstin', 'phone', 'code', 'address', 'address_line_1', 'address_line_2',
        'village_id', 'village_name', 'post_office', 'taluka', 'city',
        'state', 'pincode', 'status', 'is_default', 'is_active'
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}

