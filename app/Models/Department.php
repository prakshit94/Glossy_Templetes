<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'manager_id',
        'status',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(EmployeeProfile::class);
    }

    public function getCodeAttribute(): string
    {
        return 'DEPT-' . str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }
}
