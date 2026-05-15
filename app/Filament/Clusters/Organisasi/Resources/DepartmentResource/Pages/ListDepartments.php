<?php

namespace App\Filament\Clusters\Organisasi\Resources\DepartmentResource\Pages;

use App\Filament\Clusters\Organisasi\Resources\DepartmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
