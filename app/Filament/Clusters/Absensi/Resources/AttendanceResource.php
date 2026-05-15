<?php

namespace App\Filament\Clusters\Absensi\Resources;

use App\Filament\Clusters\Absensi\AbsensiCluster;
use App\Filament\Clusters\Absensi\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $cluster = AbsensiCluster::class;

    protected static ?string $navigationLabel = 'Data Absensi';

    protected static ?string $modelLabel = 'Absensi';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Absensi')->schema([
                Grid::make(2)->schema([
                    Select::make('employee_id')
                        ->label('Karyawan')
                        ->options(Employee::where('status', 'active')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    DatePicker::make('date')
                        ->label('Tanggal')
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->default(today()),
                    Select::make('work_type')
                        ->label('Tipe Kerja')
                        ->options(['WFO' => 'WFO', 'WFH' => 'WFH'])
                        ->required()
                        ->default('WFO'),
                    Select::make('status')
                        ->label('Status')
                        ->options(['present' => 'Hadir', 'late' => 'Terlambat', 'absent' => 'Tidak Hadir'])
                        ->required()
                        ->default('present'),
                    TextInput::make('late_minutes')
                        ->label('Keterlambatan (menit)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                    Toggle::make('late_compensated')
                        ->label('Sudah Dikompensasi')
                        ->default(false),
                ]),
            ]),

            Section::make('Check-in')->schema([
                Grid::make(3)->schema([
                    TimePicker::make('check_in_at')
                        ->label('Jam Check-in')
                        ->seconds(false),
                    TextInput::make('check_in_latitude')->label('Latitude')->numeric(),
                    TextInput::make('check_in_longitude')->label('Longitude')->numeric(),
                ]),
            ]),

            Section::make('Check-out')->schema([
                Grid::make(3)->schema([
                    TimePicker::make('check_out_at')
                        ->label('Jam Check-out')
                        ->seconds(false),
                    TextInput::make('check_out_latitude')->label('Latitude')->numeric(),
                    TextInput::make('check_out_longitude')->label('Longitude')->numeric(),
                ]),
            ]),

            Textarea::make('notes')->label('Catatan')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')->label('Karyawan')->searchable()->sortable(),
                TextColumn::make('employee.branch.name')->label('Cabang')->searchable(),
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
                IconColumn::make('is_manual')->label('Manual')->boolean(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(['present' => 'Hadir', 'late' => 'Terlambat', 'absent' => 'Tidak Hadir']),
                SelectFilter::make('work_type')
                    ->label('Tipe Kerja')
                    ->options(['WFO' => 'WFO', 'WFH' => 'WFH']),
                Filter::make('date')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal')->native(false),
                        DatePicker::make('until')->label('Sampai Tanggal')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn ($q) => $q->whereDate('date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']))
                    ),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
