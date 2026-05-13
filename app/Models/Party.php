<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends Model
{
    use SoftDeletes;

    protected $table = 'parties';

    protected $fillable = [
        'name',
        'type',
        'email',
        'phone',
        'gst_no',
        'pan_no',
        'tax_no',
        'credit_limit',
        'credit_days',
        'status',
        'is_active',
        'account_type_id',
    ];
}
