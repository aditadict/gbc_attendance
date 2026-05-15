<?php

namespace App\Filament\Clusters\MasterData\Resources;

use App\Filament\Clusters\MasterData\MasterDataCluster;
use App\Filament\Clusters\MasterData\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $cluster = MasterDataCluster::class;

    protected static ?string $navigationLabel = 'Hari Libur';

    protected static ?string $modelLabel = 'Hari Libur';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('Nama Hari Libur')
                ->required()
                ->maxLength(255),
            DatePicker::make('date')
                ->label('Tanggal')
                ->required()
                ->native(false)
                ->displayFormat('d M Y'),
            Select::make('type')
                ->label('Tipe')
                ->options([
                    'national' => 'Nasional',
                    'company' => 'Perusahaan',
                ])
                ->required()
                ->default('national'),
            Toggle::make('is_recurring')
                ->label('Berulang Setiap Tahun')
                ->helperText('Aktifkan jika hari libur ini berlaku setiap tahun pada tanggal yang sama.')
                ->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('date')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'national' ? 'Nasional' : 'Perusahaan')
                    ->color(fn ($state) => $state === 'national' ? 'danger' : 'warning'),
                IconColumn::make('is_recurring')->label('Berulang')->boolean(),
            ])
            ->defaultSort('date', 'asc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options(['national' => 'Nasional', 'company' => 'Perusahaan']),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
