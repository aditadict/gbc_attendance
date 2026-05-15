<?php

namespace App\Filament\Clusters\Absensi\Resources\AttendanceResource\Pages;

use App\Filament\Clusters\Absensi\Resources\AttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
