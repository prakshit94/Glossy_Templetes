<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartyAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'party_id',
        'label',
        'address_line_1',
        'address_line_2',
        'village_id',
        'city',
        'state',
        'pincode',
        'status',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function party()
    {
        return $this->belongsTo(Customer::class, 'party_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
