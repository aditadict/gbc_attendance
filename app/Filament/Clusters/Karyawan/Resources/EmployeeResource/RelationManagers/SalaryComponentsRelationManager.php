<?php

namespace App\Filament\Clusters\Karyawan\Resources\EmployeeResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SalaryComponentsRelationManager extends RelationManager
{
    protected static string $relationship = 'salaryComponents';

    protected static ?string $title = 'Komponen Gaji';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('salary_component_id')
                ->label('Komponen')
                ->relationship('salaryComponent', 'name')
                ->getOptionLabelFromRecordUsing(fn(Model $record) => "[{$record->code}] {$record->name}")
                ->searchable()
                ->preload()
                ->required(),
            // ->disabledOn('edit'),
            TextInput::make('amount')
                ->label('Jumlah (Rp)')
                ->numeric()
                ->required()
                ->minValue(0)
                ->prefix('Rp'),
            DatePicker::make('effective_date')
                ->label('Berlaku Mulai')
                ->required()
                ->native(false)
                ->displayFormat('d M Y')
                ->default(today()),
            DatePicker::make('end_date')
                ->label('Berlaku Sampai')
                ->native(false)
                ->displayFormat('d M Y')
                ->helperText('Kosongkan jika masih berlaku'),
            Textarea::make('notes')
                ->label('Catatan')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('salary_component_id')
            ->columns([
                TextColumn::make('salaryComponent.code')
                    ->label('Kode')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('salaryComponent.name')
                    ->label('Komponen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('salaryComponent.type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'base'      => 'Gaji Pokok',
                        'allowance' => 'Tunjangan',
                        'deduction' => 'Potongan',
                        default     => '-',
                    })
                    ->color(fn(string $state) => match ($state) {
                        'base'      => 'info',
                        'allowance' => 'success',
                        'deduction' => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn(Model $record) => $record->salaryComponent?->type === 'deduction' ? 'danger' : 'success'),
                TextColumn::make('effective_date')
                    ->label('Berlaku Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Berlaku Sampai')
                    ->date('d M Y')
                    ->placeholder('Masih berlaku'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->getStateUsing(fn(Model $record) => $record->effective_date <= today()
                        && ($record->end_date === null || $record->end_date >= today()))
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Komponen'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}