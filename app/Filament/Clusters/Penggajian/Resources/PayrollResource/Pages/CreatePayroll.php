<?php

namespace App\Filament\Clusters\Penggajian\Resources\PayrollResource\Pages;

use App\Filament\Clusters\Penggajian\Resources\PayrollResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreatePayroll extends CreateRecord
{
    protected static string $resource = PayrollResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function beforeCreate(): void
    {
        $data = $this->data;

        $exists = \App\Models\Payroll::query()
            ->where('employee_id', $data['employee_id'])
            ->where('period_month', $data['period_month'])
            ->where('period_year', $data['period_year'])
            ->exists();

        if ($exists) {
            $months   = \App\Filament\Clusters\Penggajian\Resources\PayrollResource::getMonths();
            $employee = \App\Models\Employee::find($data['employee_id']);
            $period   = ($months[$data['period_month']] ?? $data['period_month']) . ' ' . $data['period_year'];

            throw ValidationException::withMessages([
                'data.employee_id' => "Payroll untuk {$employee?->name} periode {$period} sudah ada.",
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
