<?php

namespace App\Filament\Clusters\Organisasi\Resources\BranchResource\Pages;

use App\Filament\Clusters\Organisasi\Resources\BranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBranches extends ListRecords
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
