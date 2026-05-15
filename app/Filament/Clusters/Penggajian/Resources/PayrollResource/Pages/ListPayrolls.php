<?php

namespace App\Filament\Clusters\Penggajian\Resources\PayrollResource\Pages;

use App\Filament\Clusters\Penggajian\Resources\PayrollResource;
use App\Filament\Clusters\Penggajian\Resources\PayrollResource\Widgets\PayrollStatsWidget;
use App\Models\Payroll;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Input Payroll')];
    }

    protected function getHeaderWidgets(): array
    {
        return [PayrollStatsWidget::class];
    }

    public function getTabs(): array
    {
        $month = now()->month;
        $year  = now()->year;

        return [
            'all' => Tab::make('Semua')
                ->badge(Payroll::query()->count()),

            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge(Payroll::query()->where('status', 'draft')->count())
                ->badgeColor('warning'),

            'finalized' => Tab::make('Finalisasi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'finalized'))
                ->badge(Payroll::query()->where('status', 'finalized')->count())
                ->badgeColor('info'),

            'paid' => Tab::make('Sudah Dibayar')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paid'))
                ->badge(Payroll::query()->where('status', 'paid')->count())
                ->badgeColor('success'),

            'this_month' => Tab::make('Bulan Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('period_month', $month)->where('period_year', $year))
                ->badge(Payroll::query()->where('period_month', $month)->where('period_year', $year)->count()),
        ];
    }
}
