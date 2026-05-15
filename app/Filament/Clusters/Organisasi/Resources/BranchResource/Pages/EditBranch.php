<?php

namespace App\Filament\Clusters\Organisasi\Resources\BranchResource\Pages;

use App\Filament\Clusters\Organisasi\Resources\BranchResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
