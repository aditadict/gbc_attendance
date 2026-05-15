<?php

namespace App\Filament\Clusters\Organisasi\Resources\DepartmentResource\Pages;

use App\Filament\Clusters\Organisasi\Resources\DepartmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
