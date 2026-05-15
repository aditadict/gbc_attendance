<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryComponent extends Model
{
    protected $fillable = [
        'employee_id', 'salary_component_id', 'amount', 'effective_date', 'end_date', 'notes',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'effective_date' => 'date',
        'end_date'       => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }
}
