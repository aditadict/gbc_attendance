<?php

namespace App\Filament\Clusters\Karyawan\Resources\EmployeeResource\Pages;

use App\Filament\Clusters\Karyawan\Resources\EmployeeResource;
use App\Models\EmployeeDetail;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function afterCreate(): void
    {
        // Auto-create empty detail record so RelationManager works immediately
        EmployeeDetail::firstOrCreate(['employee_id' => $this->record->id]);
    }
}
