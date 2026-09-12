<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'pay_period',
        'base_salary',
        'bonus',
        'overtime_pay',
        'deductions',
        'net_salary',
        'status',
        'is_anomaly',
        'anomaly_severity',
        'anomaly_type',
        'anomaly_explanation',
        'expected_min',
        'expected_max',
        'tenant_id',
    ];

    protected $casts = [
        'is_anomaly' => 'boolean',
        'base_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'expected_min' => 'decimal:2',
        'expected_max' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
