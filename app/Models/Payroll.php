<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;

    protected $table = 'payroll';

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'basic_salary',
        'bonus',
        'deductions',
        'net_salary',
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_id');
    }

    public function getPeriodAttribute(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('M Y');
    }

    public function getEmployeeCountAttribute(): int
    {
        return 1;
    }

    public function getGrossAttribute(): float
    {
        return (float) $this->basic_salary + (float) $this->bonus;
    }

    public function getNetAttribute(): float
    {
        return (float) $this->net_salary;
    }

    public function getPaidAtAttribute()
    {
        return $this->status === 'paid' ? $this->updated_at : null;
    }
}
