<?php

namespace App\Filament\Clusters\MasterData\Resources\SalaryComponentResource\Pages;

use App\Filament\Clusters\MasterData\Resources\SalaryComponentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalaryComponent extends EditRecord
{
    protected static string $resource = SalaryComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
