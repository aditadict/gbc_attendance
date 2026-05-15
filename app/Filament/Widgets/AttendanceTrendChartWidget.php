<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;

class AttendanceTrendChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Tren Absensi 7 Hari Terakhir';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '220px';

    protected function getData(): array
    {
        $days    = collect(range(6, 0))->map(fn ($i) => today()->subDays($i));
        $labels  = $days->map(fn ($d) => $d->translatedFormat('D, d M'))->toArray();

        $present = [];
        $late    = [];
        $absent  = [];

        foreach ($days as $day) {
            $rows = Attendance::query()->whereDate('date', $day)->get();
            $present[] = $rows->where('status', 'present')->count();
            $late[]    = $rows->where('status', 'late')->count();
            $absent[]  = $rows->where('status', 'absent')->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Hadir',
                    'data'            => $present,
                    'borderColor'     => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.1)',
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Terlambat',
                    'data'            => $late,
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245,158,11,0.1)',
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Tidak Hadir',
                    'data'            => $absent,
                    'borderColor'     => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.1)',
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
