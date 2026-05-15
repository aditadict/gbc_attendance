<?php

namespace App\Filament\Clusters\Penggajian\Resources\PayrollResource\Pages;

use App\Filament\Clusters\Penggajian\Resources\PayrollResource;
use App\Models\Payroll;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn () => $this->record->status === 'finalized'),
        ];
    }

    protected function beforeSave(): void
    {
        /** @var Payroll $record */
        $record = $this->record;

        if ($record->status === 'finalized' && ! isset($this->data['status'])) {
            return;
        }

        $oldStatus = $record->getOriginal('status');
        $newStatus = $this->data['status'] ?? $oldStatus;

        $order = ['draft' => 0, 'finalized' => 1, 'paid' => 2];

        if (($order[$newStatus] ?? 0) < ($order[$oldStatus] ?? 0)) {
            Notification::make()
                ->title('Status tidak dapat dikembalikan ke tahap sebelumnya.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
