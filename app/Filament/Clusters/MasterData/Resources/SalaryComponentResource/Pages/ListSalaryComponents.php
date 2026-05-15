<?php

namespace App\Filament\Clusters\MasterData\Resources\SalaryComponentResource\Pages;

use App\Filament\Clusters\MasterData\Resources\SalaryComponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalaryComponents extends ListRecords
{
    protected static string $resource = SalaryComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
