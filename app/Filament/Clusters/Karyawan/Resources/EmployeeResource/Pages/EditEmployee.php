<?php

namespace App\Filament\Clusters\Karyawan\Resources\EmployeeResource\Pages;

use App\Filament\Clusters\Karyawan\Resources\EmployeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
