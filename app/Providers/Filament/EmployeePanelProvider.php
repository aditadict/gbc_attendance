<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Employee\Pages\AttendanceHistoryPage;
use App\Filament\Employee\Pages\CheckInPage;
use App\Filament\Employee\Widgets\MonthlyAttendanceSummaryWidget;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class EmployeePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('employee')
            ->path('employee')
            ->viteTheme('resources/css/filament/employee/theme.css')
            ->login()
            ->colors([
                'primary' => Color::Red,
            ])
            ->brandLogo(url('https://globalbahteracollege.com/wp-content/uploads/2023/09/A.-Full-Color-Logo.png'))
            ->brandLogoHeight('5rem')
            ->favicon(url('https://globalbahteracollege.com/wp-content/uploads/2023/09/A.-Full-Color-Logo.png'))
            ->darkMode(false)
            ->pages([
                CheckInPage::class,
                AttendanceHistoryPage::class,
            ])
            ->widgets([
                MonthlyAttendanceSummaryWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full);
    }
}
