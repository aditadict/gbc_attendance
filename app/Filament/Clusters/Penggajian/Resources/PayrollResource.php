<?php

namespace App\Filament\Clusters\Penggajian\Resources;

use App\Filament\Clusters\Penggajian\PenggajianCluster;
use App\Filament\Clusters\Penggajian\Resources\PayrollResource\Pages;
use App\Models\Employee;
use App\Models\Payroll;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $cluster = PenggajianCluster::class;

    protected static ?string $navigationLabel = 'Input Payroll';

    protected static ?string $modelLabel = 'Payroll';

    protected static ?string $pluralModelLabel = 'Data Payroll';

    protected static array $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public static function getMonths(): array
    {
        return static::$months;
    }

    protected static array $statusOptions = [
        'draft'     => 'Draft',
        'finalized' => 'Finalisasi',
        'paid'      => 'Sudah Dibayar',
    ];

    protected static array $statusColors = [
        'draft'     => 'warning',
        'finalized' => 'info',
        'paid'      => 'success',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Periode & Karyawan')->schema([
                Grid::make(3)->schema([
                    Select::make('period_month')
                        ->label('Bulan')
                        ->options(static::$months)
                        ->default(now()->month)
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('employee_id', null)),

                    TextInput::make('period_year')
                        ->label('Tahun')
                        ->numeric()
                        ->default(now()->year)
                        ->minValue(2020)
                        ->maxValue(2099)
                        ->required()
                        ->live(debounce: 800)
                        ->afterStateUpdated(fn (Set $set) => $set('employee_id', null)),

                    Select::make('employee_id')
                        ->label('Karyawan')
                        ->options(function ($get) {
                            $month = $get('period_month');
                            $year  = $get('period_year');

                            $taken = $month && $year
                                ? \App\Models\Payroll::query()
                                    ->where('period_month', $month)
                                    ->where('period_year', $year)
                                    ->pluck('employee_id')
                                    ->toArray()
                                : [];

                            return Employee::query()
                                ->where('status', 'active')
                                ->whereNotIn('id', $taken)
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                        ->required()
                        ->live()
                        ->disabledOn('edit')
                        ->helperText(fn ($get) => $get('period_month') && $get('period_year')
                            ? 'Hanya karyawan yang belum memiliki payroll di periode ini yang ditampilkan.'
                            : 'Pilih bulan dan tahun terlebih dahulu.')
                        ->afterStateUpdated(fn (Set $set, ?int $state) => static::fillSalaryFields($set, $state)),
                ]),
            ]),

            Section::make('Komponen Gaji')->schema([
                Grid::make(2)->schema([
                    TextInput::make('basic_salary')
                        ->label('Gaji Pokok (Rp)')
                        ->numeric()->prefix('Rp')->default(0)->minValue(0)->required()
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn (Set $set, $get) => static::computeNet($set, $get)),

                    TextInput::make('total_allowance')
                        ->label('Total Tunjangan (Rp)')
                        ->numeric()->prefix('Rp')->default(0)->minValue(0)->required()
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn (Set $set, $get) => static::computeNet($set, $get)),

                    TextInput::make('total_deduction')
                        ->label('Total Potongan (Rp)')
                        ->numeric()->prefix('Rp')->default(0)->minValue(0)->required()
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn (Set $set, $get) => static::computeNet($set, $get)),

                    TextInput::make('late_deduction')
                        ->label('Potongan Keterlambatan (Rp)')
                        ->numeric()->prefix('Rp')->default(0)->minValue(0)->required()
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn (Set $set, $get) => static::computeNet($set, $get)),

                    TextInput::make('bonus')
                        ->label('Bonus / Insentif (Rp)')
                        ->numeric()->prefix('Rp')->default(0)->minValue(0)->required()
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn (Set $set, $get) => static::computeNet($set, $get)),

                    Placeholder::make('net_salary_preview')
                        ->label('Gaji Bersih (Preview)')
                        ->content(function ($get): string {
                            $net = (float) $get('basic_salary')
                                + (float) $get('total_allowance')
                                + (float) $get('bonus')
                                - (float) $get('total_deduction')
                                - (float) $get('late_deduction');

                            return 'Rp ' . number_format($net, 0, ',', '.');
                        }),
                ]),
            ]),

            Section::make('Status & Catatan')->schema([
                Grid::make(2)->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(static::$statusOptions)
                        ->default('draft')
                        ->required()
                        ->native(false),

                    TextInput::make('net_salary')
                        ->label('Gaji Bersih Tersimpan (Rp)')
                        ->prefix('Rp')
                        ->disabled()
                        ->dehydrated()
                        ->numeric()
                        ->visibleOn('edit'),
                ]),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    protected static function fillSalaryFields(Set $set, ?int $employeeId): void
    {
        if (! $employeeId) {
            return;
        }

        $employee = Employee::find($employeeId);

        if (! $employee) {
            return;
        }

        $components = $employee->activeSalaryComponents()->with('salaryComponent')->get();

        $basic     = $components->filter(fn ($c) => $c->salaryComponent?->type === 'base')->sum('amount');
        $allowance = $components->filter(fn ($c) => $c->salaryComponent?->type === 'allowance')->sum('amount');
        $deduction = $components->filter(fn ($c) => $c->salaryComponent?->type === 'deduction')->sum('amount');

        $set('basic_salary', (float) $basic);
        $set('total_allowance', (float) $allowance);
        $set('total_deduction', (float) $deduction);
    }

    protected static function computeNet(Set $set, $get): void
    {
        $net = (float) $get('basic_salary')
            + (float) $get('total_allowance')
            + (float) $get('bonus')
            - (float) $get('total_deduction')
            - (float) $get('late_deduction');

        $set('net_salary', $net);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('period_month')
                    ->label('Periode')
                    ->formatStateUsing(fn (mixed $state, Payroll $record) => (static::$months[$state] ?? $state) . ' ' . $record->period_year)
                    ->sortable(),
                TextColumn::make('basic_salary')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize(Sum::make()->label('Total')->money('IDR')),
                TextColumn::make('total_allowance')
                    ->label('Tunjangan')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize(Sum::make()->label('Total')->money('IDR')),
                TextColumn::make('total_deduction')
                    ->label('Potongan')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize(Sum::make()->label('Total')->money('IDR')),
                TextColumn::make('net_salary')
                    ->label('Gaji Bersih')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->summarize(Sum::make()->label('Total Gaji Bersih')->money('IDR')),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => static::$statusOptions[$state] ?? $state)
                    ->color(fn (string $state) => static::$statusColors[$state] ?? 'gray'),
                TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('period_year', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(static::$statusOptions),
                SelectFilter::make('period_month')
                    ->label('Bulan')
                    ->options(static::$months),
                SelectFilter::make('employee_id')
                    ->label('Karyawan')
                    ->relationship('employee', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn (Payroll $record) => $record->status !== 'draft'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit'   => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['employee.branch', 'createdBy']);
    }
}
