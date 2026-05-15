<?php

namespace App\Filament\Clusters\Organisasi\Resources;

use App\Filament\Clusters\Organisasi\OrganisasiCluster;
use App\Filament\Clusters\Organisasi\Resources\DepartmentResource\Pages;
use App\Models\Company;
use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $cluster = OrganisasiCluster::class;

    protected static ?string $navigationLabel = 'Departemen';

    protected static ?string $modelLabel = 'Departemen';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('company_id')
                ->label('Perusahaan')
                ->options(Company::pluck('name', 'id'))
                ->searchable()
                ->required(),
            TextInput::make('name')
                ->label('Nama Departemen')
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
                TextColumn::make('company.name')->label('Perusahaan')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('code')->label('Kode'),
                TextColumn::make('positions_count')->label('Jabatan')->counts('positions')->sortable(),
                TextColumn::make('employees_count')->label('Karyawan')->counts('employees')->sortable(),
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
