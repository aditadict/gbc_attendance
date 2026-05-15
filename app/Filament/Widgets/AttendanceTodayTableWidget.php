<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AttendanceTodayTableWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Absensi Hari Ini';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::with(['employee.branch', 'employee.department', 'employee.position'])
                    ->whereDate('date', today())
                    ->orderByDesc('check_in_at')
            )
            ->columns([
                TextColumn::make('employee.name')->label('Karyawan')->searchable()->sortable(),
                TextColumn::make('employee.branch.name')->label('Cabang')->searchable(),
                TextColumn::make('employee.department.name')->label('Departemen'),
                TextColumn::make('work_type')->label('Tipe')->badge()
                    ->color(fn ($state) => $state === 'WFO' ? 'info' : 'success'),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'present' => 'Hadir',
                        'late'    => 'Terlambat',
                        'absent'  => 'Tidak Hadir',
                        default   => '-',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'present' => 'success',
                        'late'    => 'warning',
                        'absent'  => 'danger',
                        default   => 'gray',
                    }),
                TextColumn::make('check_in_at')->label('Check-in')->time('H:i')
                    ->placeholder('—'),
                TextColumn::make('check_out_at')->label('Check-out')->time('H:i')
                    ->placeholder('—'),
                TextColumn::make('late_minutes')->label('Terlambat')
                    ->formatStateUsing(fn (mixed $state) => $state > 0 ? "{$state} mnt" : '—')
                    ->color(fn (mixed $state) => $state > 0 ? 'warning' : 'gray'),
            ])
            ->paginated([10, 25, 50]);
    }
}
