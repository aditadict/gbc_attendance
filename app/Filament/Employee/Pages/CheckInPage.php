<?php

namespace App\Filament\Employee\Pages;

use App\Models\Attendance;
use App\Services\LocationService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class CheckInPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected string $view = 'filament.employee.pages.check-in-page';

    protected static ?string $navigationLabel = 'Absen Hari Ini';

    protected static ?string $title = 'Absen Hari Ini';

    protected static ?int $navigationSort = 1;

    public float $latitude = 0;
    public float $longitude = 0;
    public string $workTypeChoice = 'WFO';

    public function getEmployee()
    {
        return Auth::user()->employee()->with('branch')->first();
    }

    public function getTodayAttendance()
    {
        $employee = $this->getEmployee();
        if (! $employee) {
            return null;
        }

        return Attendance::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();
    }

    public function checkIn(): void
    {
        $employee = $this->getEmployee();

        if (! $employee) {
            Notification::make()->title('Profil karyawan tidak ditemukan.')->danger()->send();

            return;
        }

        $existing = $this->getTodayAttendance();

        if ($existing && $existing->check_in_at) {
            Notification::make()->title('Anda sudah melakukan check-in hari ini.')->warning()->send();

            return;
        }

        $workType = $employee->work_type === 'Hybrid' ? $this->workTypeChoice : $employee->work_type;
        $locationStatus = 'wfh';
        $branch = $employee->branch;

        if ($workType === 'WFO') {
            if ($this->latitude == 0 && $this->longitude == 0) {
                Notification::make()->title('Lokasi belum didapatkan. Izinkan akses lokasi di browser Anda.')->danger()->send();

                return;
            }

            $withinRadius = LocationService::isWithinRadius(
                $this->latitude, $this->longitude,
                (float) $branch->latitude, (float) $branch->longitude,
                $branch->radius
            );

            if (! $withinRadius) {
                $distance = round(LocationService::distance(
                    $this->latitude, $this->longitude,
                    (float) $branch->latitude, (float) $branch->longitude
                ));
                Notification::make()
                    ->title("Anda berada di luar radius cabang ({$distance} m dari {$branch->radius} m).")
                    ->danger()
                    ->send();

                return;
            }

            $locationStatus = 'inside_radius';
        }

        $now = Carbon::now();
        $workStart = Carbon::today()->setTimeFromTimeString($branch->work_start_time);
        $lateThreshold = $workStart->copy()->addMinutes($branch->late_tolerance_minutes);
        $lateMinutes = 0;
        $status = 'present';

        if ($now->gt($lateThreshold)) {
            $lateMinutes = (int) $now->diffInMinutes($workStart);
            $status = 'late';
        }

        $attendance = $existing ?? new Attendance(['employee_id' => $employee->id, 'date' => today()]);
        $attendance->fill([
            'work_type' => $workType,
            'check_in_at' => $now,
            'check_in_latitude' => $this->latitude ?: null,
            'check_in_longitude' => $this->longitude ?: null,
            'check_in_location_status' => $locationStatus,
            'status' => $status,
            'late_minutes' => $lateMinutes,
        ]);
        $attendance->save();

        $message = $status === 'late'
            ? "Check-in berhasil. Anda terlambat {$lateMinutes} menit."
            : 'Check-in berhasil. Selamat bekerja!';

        Notification::make()->title($message)
            ->color($status === 'late' ? 'warning' : 'success')
            ->send();
    }

    public function checkOut(): void
    {
        $employee = $this->getEmployee();
        $attendance = $this->getTodayAttendance();

        if (! $attendance || ! $attendance->check_in_at) {
            Notification::make()->title('Anda belum melakukan check-in hari ini.')->warning()->send();

            return;
        }

        if ($attendance->check_out_at) {
            Notification::make()->title('Anda sudah melakukan check-out hari ini.')->warning()->send();

            return;
        }

        $attendance->update([
            'check_out_at' => now(),
            'check_out_latitude' => $this->latitude ?: null,
            'check_out_longitude' => $this->longitude ?: null,
        ]);

        Notification::make()->title('Check-out berhasil. Sampai jumpa!')->success()->send();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
