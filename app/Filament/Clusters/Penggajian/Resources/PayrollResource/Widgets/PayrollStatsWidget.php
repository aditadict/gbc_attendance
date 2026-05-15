<?php

namespace App\Filament\Clusters\Penggajian\Resources\PayrollResource\Widgets;

use App\Models\Payroll;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayrollStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $month = now()->month;
        $year  = now()->year;

        $base = Payroll::query()->where('period_month', $month)->where('period_year', $year);

        $totalRecords  = (clone $base)->count();
        $draftCount    = (clone $base)->where('status', 'draft')->count();
        $finalizedCount = (clone $base)->where('status', 'finalized')->count();
        $paidCount     = (clone $base)->where('status', 'paid')->count();
        $totalNetPaid  = (clone $base)->where('status', 'paid')->sum('net_salary');
        $totalNetAll   = (clone $base)->sum('net_salary');

        $periodLabel = now()->translatedFormat('F Y');

        return [
            Stat::make('Total Payroll ' . now()->format('M Y'), $totalRecords . ' karyawan')
                ->description('Draft: ' . $draftCount . ' | Finalisasi: ' . $finalizedCount . ' | Dibayar: ' . $paidCount)
                ->color('gray'),

            Stat::make('Belum Dibayar', $finalizedCount . ' karyawan')
                ->description('Status Finalisasi — siap dibayarkan')
                ->color('info'),

            Stat::make('Sudah Dibayar', $paidCount . ' karyawan')
                ->description('Total: Rp ' . number_format((float) $totalNetPaid, 0, ',', '.'))
                ->color('success'),

            Stat::make('Total Penggajian ' . now()->format('M Y'), 'Rp ' . number_format((float) $totalNetAll, 0, ',', '.'))
                ->description('Seluruh status termasuk draft')
                ->color('warning'),
        ];
    }
}
