<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'department_id', 'position_id',
        'employee_number', 'name', 'email', 'phone',
        'work_type', 'join_date', 'status',
    ];

    protected $casts = [
        'join_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance(): HasOne
    {
        return $this->hasOne(Attendance::class)->whereDate('date', today());
    }

    public function salaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public function activeSalaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class)
            ->where('effective_date', '<=', today())
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', today()));
    }
}
