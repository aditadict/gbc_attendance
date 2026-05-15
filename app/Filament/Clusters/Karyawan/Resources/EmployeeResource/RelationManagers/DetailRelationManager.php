<?php

namespace App\Filament\Clusters\Karyawan\Resources\EmployeeResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DetailRelationManager extends RelationManager
{
    protected static string $relationship = 'detail';

    protected static ?string $title = 'Detail Pribadi';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Identitas')->schema([
                Grid::make(2)->schema([
                    TextInput::make('nik')->label('NIK')->maxLength(20),
                    TextInput::make('birth_place')->label('Tempat Lahir')->maxLength(100),
                    DatePicker::make('birth_date')->label('Tanggal Lahir')->native(false)->displayFormat('d M Y'),
                    Select::make('gender')->label('Jenis Kelamin')->options(['male' => 'Laki-laki', 'female' => 'Perempuan']),
                    TextInput::make('blood_type')->label('Golongan Darah')->maxLength(5),
                    Select::make('religion')->label('Agama')->options([
                        'Islam' => 'Islam',
                        'Kristen' => 'Kristen',
                        'Katolik' => 'Katolik',
                        'Hindu' => 'Hindu',
                        'Buddha' => 'Buddha',
                        'Konghucu' => 'Konghucu',
                    ]),
                    Select::make('marital_status')->label('Status Pernikahan')->options([
                        'single' => 'Belum Menikah',
                        'married' => 'Menikah',
                        'divorced' => 'Cerai Hidup',
                        'widowed' => 'Cerai Mati',
                    ]),
                ]),
                Textarea::make('address')->label('Alamat')->rows(3)->columnSpanFull(),
            ]),

            Section::make('Kontak Darurat')->schema([
                Grid::make(2)->schema([
                    TextInput::make('emergency_contact_name')->label('Nama Kontak Darurat')->maxLength(255),
                    TextInput::make('emergency_contact_phone')->label('Telepon Kontak Darurat')->tel()->maxLength(20),
                ]),
            ]),

            Section::make('Informasi Bank')->schema([
                Grid::make(3)->schema([
                    TextInput::make('bank_name')->label('Nama Bank')->maxLength(100),
                    TextInput::make('bank_account_number')->label('Nomor Rekening')->maxLength(30),
                    TextInput::make('bank_account_name')->label('Nama Pemilik Rekening')->maxLength(255),
                ]),
            ]),

            Section::make('Foto')->schema([
                FileUpload::make('photo')
                    ->label('Foto Karyawan')
                    ->image()
                    ->directory('employees/photos')
                    ->avatar()
                    ->nullable(),
            ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('nik')->label('NIK'),
            TextColumn::make('birth_date')->label('Tgl Lahir')->date('d M Y'),
            TextColumn::make('gender')->label('L/P')->formatStateUsing(fn ($s) => $s === 'male' ? 'L' : 'P'),
            TextColumn::make('marital_status')->label('Status Nikah'),
            TextColumn::make('bank_name')->label('Bank'),
            TextColumn::make('bank_account_number')->label('No. Rekening'),
        ]);
    }
}
