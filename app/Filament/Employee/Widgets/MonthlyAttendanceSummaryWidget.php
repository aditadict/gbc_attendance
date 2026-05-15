<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MonthlyAttendanceSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = null;

    protected function getStats(): array
    {
        $employee = Auth::user()?->employee;

        if (! $employee) {
            return [];
        }

        $year  = now()->year;
        $month = now()->month;

        $base = Attendance::query()
            ->where('employee_id', '=', $employee->id)
            ->whereYear('date', '=', $year)
            ->whereMonth('date', '=', $month);

        $present     = (clone $base)->where('status', '=', 'present')->count();
        $late        = (clone $base)->where('status', '=', 'late')->count();
        $absent      = (clone $base)->where('status', '=', 'absent')->count();
        $compensated = (clone $base)->where('late_compensated', '=', true)->count();
        $monthLabel  = now()->translatedFormat('F Y');

        return [
            Stat::make('Hadir', $present)
                ->description("Tepat waktu · {$monthLabel}")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Terlambat', $late)
                ->description("{$compensated} sudah dikompensasi")
                ->descriptionIcon('heroicon-m-clock')
                ->color($late > 0 ? 'warning' : 'success'),

            Stat::make('Tidak Hadir', $absent)
                ->description("Total ketidakhadiran bulan ini")
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($absent > 0 ? 'danger' : 'success'),

            Stat::make('Total Kehadiran', $present + $late)
                ->description('Dari ' . now()->daysInMonth . ' hari kerja')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
        ];
    }
}
