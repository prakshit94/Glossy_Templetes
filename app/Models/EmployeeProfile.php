<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'employee_code',
        'designation',
        'joining_date',
        'salary',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function payroll()
    {
        return $this->hasMany(Payroll::class, 'employee_id');
    }

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? $this->employee_code;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    public function getRoleAttribute(): ?string
    {
        return $this->designation;
    }

    public function getHiredAtAttribute()
    {
        return $this->joining_date;
    }
}
