<?php

namespace App\Filament\Clusters\Absensi\Resources\AttendanceResource\Pages;

use App\Filament\Clusters\Absensi\Resources\AttendanceResource;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $date = Carbon::parse($data['date']);

        if (!empty($data['check_in_at'])) {
            $t = Carbon::parse($data['check_in_at']);
            $data['check_in_at'] = $date->copy()->setTime($t->hour, $t->minute)->toDateTimeString();
        }

        if (!empty($data['check_out_at'])) {
            $t = Carbon::parse($data['check_out_at']);
            $data['check_out_at'] = $date->copy()->setTime($t->hour, $t->minute)->toDateTimeString();
        }

        return $data;
    }
}
