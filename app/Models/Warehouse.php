<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['name', 'code', 'location', 'status', 'is_active'];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
