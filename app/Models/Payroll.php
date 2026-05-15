<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'period_month', 'period_year',
        'basic_salary', 'total_allowance', 'total_deduction',
        'late_deduction', 'bonus', 'net_salary',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'basic_salary'    => 'decimal:2',
        'total_allowance' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'late_deduction'  => 'decimal:2',
        'bonus'           => 'decimal:2',
        'net_salary'      => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Payroll $payroll): void {
            $payroll->net_salary =
                (float) $payroll->basic_salary
                + (float) $payroll->total_allowance
                + (float) $payroll->bonus
                - (float) $payroll->total_deduction
                - (float) $payroll->late_deduction;
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($months[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }
}
