<?php

namespace App\Filament\Clusters\Absensi\Resources\AttendanceResource\Pages;

use App\Filament\Clusters\Absensi\Resources\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_manual'] = true;
        $data['created_by'] = Auth::id();

        return $data;
    }
}
