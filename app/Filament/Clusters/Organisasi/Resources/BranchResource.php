<?php

namespace App\Filament\Clusters\Organisasi\Resources;

use App\Fields\LeafletMapField;
use App\Filament\Clusters\Organisasi\OrganisasiCluster;
use App\Filament\Clusters\Organisasi\Resources\BranchResource\Pages;
use App\Models\Branch;
use App\Models\Company;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $cluster = OrganisasiCluster::class;

    protected static ?string $navigationLabel = 'Cabang';

    protected static ?string $modelLabel = 'Cabang';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Cabang')->schema([
                Grid::make(2)->schema([
                    Select::make('company_id')
                        ->label('Perusahaan')
                        ->options(Company::pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('name')
                        ->label('Nama Cabang')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    TextInput::make('phone')
                        ->label('Telepon')
                        ->tel()
                        ->maxLength(20),
                    TextInput::make('address')
                        ->label('Alamat')
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            ]),

            Section::make('Lokasi & Radius')->schema([
                LeafletMapField::make('location')
                    ->label('Lokasi Cabang')
                    ->live()
                    ->afterStateHydrated(function (Set $set, $record) {
                        if (!$record) return;
                        $lat = $record->latitude ?? 0;
                        $lng = $record->longitude ?? 0;
                        if ($lat && $lng) {
                            $set('location', ['lat' => $lat, 'lng' => $lng]);
                        }
                    })
                    ->afterStateUpdated(function ($state, Set $set) {
                        $set('latitude',  $state['lat'] ?? null);
                        $set('longitude', $state['lng'] ?? null);
                    })
                    ->columnSpanFull(),

                Grid::make(3)->schema([
                    TextInput::make('latitude')
                        ->label('Latitude')
                        ->numeric()
                        ->required()
                        ->readOnly(),
                    TextInput::make('longitude')
                        ->label('Longitude')
                        ->numeric()
                        ->required()
                        ->readOnly(),
                    TextInput::make('radius')
                        ->label('Radius (meter)')
                        ->numeric()
                        ->required()
                        ->default(100)
                        ->minValue(10)
                        ->suffix('m'),
                ]),
            ]),

            Section::make('Jam Kerja')->schema([
                Grid::make(3)->schema([
                    TimePicker::make('work_start_time')
                        ->label('Jam Masuk')
                        ->required()
                        ->seconds(false)
                        ->default('08:00'),
                    TimePicker::make('work_end_time')
                        ->label('Jam Pulang')
                        ->required()
                        ->seconds(false)
                        ->default('17:00'),
                    TextInput::make('late_tolerance_minutes')
                        ->label('Toleransi Keterlambatan')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->suffix('menit'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')->label('Perusahaan')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Cabang')->searchable()->sortable(),
                TextColumn::make('code')->label('Kode'),
                TextColumn::make('radius')->label('Radius')->suffix(' m'),
                TextColumn::make('work_start_time')->label('Jam Masuk'),
                TextColumn::make('work_end_time')->label('Jam Pulang'),
                TextColumn::make('late_tolerance_minutes')->label('Toleransi')->suffix(' mnt'),
                TextColumn::make('employees_count')
                    ->label('Karyawan')
                    ->counts('employees')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('company_id')
                    ->label('Perusahaan')
                    ->options(Company::pluck('name', 'id')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
