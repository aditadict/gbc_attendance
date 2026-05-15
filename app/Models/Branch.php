<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'company_id', 'name', 'code', 'address', 'phone',
        'latitude', 'longitude', 'radius',
        'work_start_time', 'work_end_time', 'late_tolerance_minutes',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'radius' => 'integer',
        'late_tolerance_minutes' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
