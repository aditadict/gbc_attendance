<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceTodayStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalActive  = Employee::query()->where('status', '=', 'active')->count();
        $todayRecords = Attendance::query()->whereDate('date', today());

        $present = (clone $todayRecords)->where('status', '=', 'present')->count();
        $late    = (clone $todayRecords)->where('status', '=', 'late')->count();
        $absent  = (clone $todayRecords)->where('status', '=', 'absent')->count();
        $checkedIn = $present + $late;
        $notYet  = max(0, $totalActive - $checkedIn - $absent);

        return [
            Stat::make('Total Karyawan Aktif', $totalActive)
                ->description('Terdaftar di sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),

            Stat::make('Hadir Hari Ini', $checkedIn)
                ->description("{$present} tepat waktu · {$late} terlambat")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Belum Absen', $notYet)
                ->description('Dari ' . $totalActive . ' karyawan aktif')
                ->descriptionIcon('heroicon-m-clock')
                ->color($notYet > 0 ? 'warning' : 'success'),

            Stat::make('Tidak Hadir', $absent)
                ->description('Absensi manual HR')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($absent > 0 ? 'danger' : 'success'),
        ];
    }
}
