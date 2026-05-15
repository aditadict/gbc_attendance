<?php

namespace App\Filament\Clusters\Organisasi\Resources;

use App\Filament\Clusters\Organisasi\OrganisasiCluster;
use App\Filament\Clusters\Organisasi\Resources\PositionResource\Pages;
use App\Models\Department;
use App\Models\Position;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static ?string $cluster = OrganisasiCluster::class;

    protected static ?string $navigationLabel = 'Jabatan';

    protected static ?string $modelLabel = 'Jabatan';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('department_id')
                ->label('Departemen')
                ->options(Department::with('company')->get()->mapWithKeys(
                    fn ($d) => [$d->id => "{$d->company->name} — {$d->name}"]
                ))
                ->searchable()
                ->required(),
            TextInput::make('name')
                ->label('Nama Jabatan')
                ->required()
                ->maxLength(255),
            TextInput::make('code')
                ->label('Kode')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('department.company.name')->label('Perusahaan')->searchable()->sortable(),
                TextColumn::make('department.name')->label('Departemen')->searchable()->sortable(),
                TextColumn::make('name')->label('Jabatan')->searchable()->sortable(),
                TextColumn::make('code')->label('Kode'),
                TextColumn::make('employees_count')->label('Karyawan')->counts('employees')->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPositions::route('/'),
            'create' => Pages\CreatePosition::route('/create'),
            'edit' => Pages\EditPosition::route('/{record}/edit'),
        ];
    }
}
