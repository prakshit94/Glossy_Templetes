<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['name', 'code', 'address', 'state', 'status', 'is_default', 'is_active'];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
