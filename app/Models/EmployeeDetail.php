<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDetail extends Model
{
    protected $fillable = [
        'employee_id', 'nik', 'birth_place', 'birth_date',
        'gender', 'blood_type', 'religion', 'marital_status', 'address',
        'emergency_contact_name', 'emergency_contact_phone',
        'bank_name', 'bank_account_number', 'bank_account_name', 'photo',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
