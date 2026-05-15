<?php

namespace App\Filament\Clusters\Organisasi\Resources\CompanyResource\Pages;

use App\Filament\Clusters\Organisasi\Resources\CompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
