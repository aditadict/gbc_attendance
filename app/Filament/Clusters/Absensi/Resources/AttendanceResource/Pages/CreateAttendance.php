<?php

namespace App\Filament\Clusters\Absensi\Resources\AttendanceResource\Pages;

use App\Filament\Clusters\Absensi\Resources\AttendanceResource;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_manual'] = true;
        $data['created_by'] = Auth::id();

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
