<?php

namespace App\Filament\Clusters\Karyawan\Resources\EmployeeResource\Pages;

use App\Filament\Clusters\Karyawan\Resources\EmployeeResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createAccount')
                ->label('Buat Akun Login')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->visible(fn () => $this->record->user_id === null)
                ->form([
                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->confirmed(),
                    TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password')
                        ->password()
                        ->revealable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $employee = $this->record;

                    $user = User::create([
                        'name'     => $employee->name,
                        'email'    => $employee->email,
                        'password' => bcrypt($data['password']),
                    ]);

                    $employee->update(['user_id' => $user->id]);

                    Notification::make()
                        ->title('Akun login berhasil dibuat untuk ' . $employee->name)
                        ->success()
                        ->send();
                }),

            Action::make('resetPassword')
                ->label('Reset Password')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->visible(fn () => $this->record->user_id !== null)
                ->form([
                    TextInput::make('password')
                        ->label('Password Baru')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->confirmed(),
                    TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password Baru')
                        ->password()
                        ->revealable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->user->update([
                        'password' => bcrypt($data['password']),
                    ]);

                    Notification::make()
                        ->title('Password berhasil direset.')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
