<?php

namespace App\Filament\Employee\Pages;

use App\Models\Attendance;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;

class AttendanceHistoryPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected string $view = 'filament.employee.pages.attendance-history-page';

    protected static ?string $navigationLabel = 'Riwayat Absensi';

    protected static ?string $title = 'Riwayat Absensi';

    protected static ?int $navigationSort = 2;

    public function table(Table $table): Table
    {
        $employee = Auth::user()->employee;

        return $table
            ->query(
                Attendance::where('employee_id', $employee?->id ?? 0)
            )
            ->columns([
                TextColumn::make('date')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('work_type')->label('Tipe')->badge()
                    ->color(fn ($s) => $s === 'WFO' ? 'info' : 'success'),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($s) => match ($s) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                    })
                    ->color(fn ($s) => match ($s) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                    }),
                TextColumn::make('check_in_at')->label('Check-in')->time('H:i'),
                TextColumn::make('check_out_at')->label('Check-out')->time('H:i'),
                TextColumn::make('late_minutes')->label('Terlambat')->suffix(' mnt')
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),
                IconColumn::make('late_compensated')->label('Kompensasi')->boolean(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(['present' => 'Hadir', 'late' => 'Terlambat', 'absent' => 'Tidak Hadir']),
                Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('Dari')->native(false),
                        DatePicker::make('until')->label('Sampai')->native(false),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'], fn ($q) => $q->whereDate('date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']))
                    ),
            ]);
    }
}
