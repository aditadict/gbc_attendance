<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryComponent extends Model
{
    protected $fillable = [
        'name', 'code', 'type', 'is_taxable', 'is_active', 'description',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function employeeSalaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'base'      => 'Gaji Pokok',
            'allowance' => 'Tunjangan',
            'deduction' => 'Potongan',
            default     => '-',
        };
    }
}
