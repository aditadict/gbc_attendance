<?php

namespace App\Filament\Clusters\MasterData\Resources;

use App\Filament\Clusters\MasterData\MasterDataCluster;
use App\Filament\Clusters\MasterData\Resources\SalaryComponentResource\Pages;
use App\Models\SalaryComponent;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalaryComponentResource extends Resource
{
    protected static ?string $model = SalaryComponent::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $navigationLabel = 'Komponen Gaji';

    protected static ?string $modelLabel = 'Komponen Gaji';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informasi Komponen')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->label('Nama Komponen')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20)
                        ->helperText('Contoh: BASIC, THR, BPJS'),
                    Select::make('type')
                        ->label('Tipe')
                        ->options([
                            'base'      => 'Gaji Pokok',
                            'allowance' => 'Tunjangan',
                            'deduction' => 'Potongan',
                        ])
                        ->required()
                        ->native(false),
                    Grid::make(2)->schema([
                        Toggle::make('is_taxable')
                            ->label('Kena Pajak (PPh21)')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                ]),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->rows(2)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable()->badge()->color('gray'),
                TextColumn::make('name')->label('Nama Komponen')->searchable()->sortable(),
                TextColumn::make('type')->label('Tipe')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'base'      => 'Gaji Pokok',
                        'allowance' => 'Tunjangan',
                        'deduction' => 'Potongan',
                        default     => '-',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'base'      => 'info',
                        'allowance' => 'success',
                        'deduction' => 'danger',
                        default     => 'gray',
                    }),
                IconColumn::make('is_taxable')->label('Kena Pajak')->boolean(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('employeeSalaryComponents_count')
                    ->label('Dipakai')
                    ->counts('employeeSalaryComponents')
                    ->suffix(' karyawan')
                    ->color('gray'),
            ])
            ->defaultSort('type')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'base'      => 'Gaji Pokok',
                        'allowance' => 'Tunjangan',
                        'deduction' => 'Potongan',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options(['1' => 'Aktif', '0' => 'Tidak Aktif']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSalaryComponents::route('/'),
            'create' => Pages\CreateSalaryComponent::route('/create'),
            'edit'   => Pages\EditSalaryComponent::route('/{record}/edit'),
        ];
    }
}
