<?php

namespace App\Filament\Clusters\MasterData\Resources\HolidayResource\Pages;

use App\Filament\Clusters\MasterData\Resources\HolidayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHolidays extends ListRecords
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
