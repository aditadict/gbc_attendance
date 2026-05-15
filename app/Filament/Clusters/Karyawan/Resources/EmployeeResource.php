<?php

namespace App\Filament\Clusters\Karyawan\Resources;

use App\Filament\Clusters\Karyawan\KaryawanCluster;
use App\Filament\Clusters\Karyawan\Resources\EmployeeResource\Pages;
use App\Filament\Clusters\Karyawan\Resources\EmployeeResource\RelationManagers\DetailRelationManager;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $cluster = KaryawanCluster::class;

    protected static ?string $navigationLabel = 'Data Karyawan';

    protected static ?string $modelLabel = 'Karyawan';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Karyawan')->schema([
                Grid::make(2)->schema([
                    TextInput::make('employee_number')
                        ->label('Nomor Karyawan')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Telepon')
                        ->tel()
                        ->maxLength(20),
                    DatePicker::make('join_date')
                        ->label('Tanggal Bergabung')
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y'),
                    Select::make('status')
                        ->label('Status')
                        ->options(['active' => 'Aktif', 'inactive' => 'Tidak Aktif'])
                        ->required()
                        ->default('active'),
                ]),
            ]),

            Section::make('Penempatan')->schema([
                Grid::make(2)->schema([
                    Select::make('branch_id')
                        ->label('Cabang')
                        ->options(Branch::with('company')->get()->mapWithKeys(
                            fn ($b) => [$b->id => "{$b->company->name} — {$b->name}"]
                        ))
                        ->searchable()
                        ->required(),
                    Select::make('work_type')
                        ->label('Tipe Kerja')
                        ->options(['WFO' => 'WFO', 'WFH' => 'WFH', 'Hybrid' => 'Hybrid'])
                        ->required()
                        ->default('WFO'),
                    Select::make('department_id')
                        ->label('Departemen')
                        ->options(Department::with('company')->get()->mapWithKeys(
                            fn ($d) => [$d->id => "{$d->company->name} — {$d->name}"]
                        ))
                        ->searchable()
                        ->required()
                        ->live(),
                    Select::make('position_id')
                        ->label('Jabatan')
                        ->options(fn (Get $get) => Position::where('department_id', $get('department_id'))
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_number')->label('No. Karyawan')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('branch.name')->label('Cabang')->searchable()->sortable(),
                TextColumn::make('department.name')->label('Departemen')->searchable(),
                TextColumn::make('position.name')->label('Jabatan')->searchable(),
                TextColumn::make('work_type')->label('Tipe')->badge()
                    ->color(fn ($state) => match ($state) {
                        'WFO' => 'info',
                        'WFH' => 'success',
                        'Hybrid' => 'warning',
                    }),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($s) => $s === 'active' ? 'Aktif' : 'Tidak Aktif')
                    ->color(fn ($s) => $s === 'active' ? 'success' : 'danger'),
                TextColumn::make('join_date')->label('Tgl Bergabung')->date('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch_id')->label('Cabang')->options(Branch::pluck('name', 'id')),
                SelectFilter::make('department_id')->label('Departemen')->options(Department::pluck('name', 'id')),
                SelectFilter::make('work_type')->label('Tipe Kerja')->options(['WFO' => 'WFO', 'WFH' => 'WFH', 'Hybrid' => 'Hybrid']),
                SelectFilter::make('status')->label('Status')->options(['active' => 'Aktif', 'inactive' => 'Tidak Aktif']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelationManagers(): array
    {
        return [DetailRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
