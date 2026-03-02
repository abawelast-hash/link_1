<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Resources\{
    UserResource,
    BranchResource,
    AttendanceLogResource,
    LeaveRequestResource,
    CircularResource,
    WhistleblowerReportResource,
    BadgeResource,
    MonthlyReportResource,
    AnomalyLogResource,
};
use App\Filament\Admin\Widgets\{
    StatsOverviewWidget,
    AttendanceTodayWidget,
    CompanyLeaderboardWidget,
};
use Filament\Http\Middleware\{Authenticate, DisableBladeIconComponents, DispatchServingFilamentEvent};
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\{AddQueuedCookiesToResponse, EncryptCookies};
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\{AuthenticateSession, StartSession};
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary'  => Color::hex('#D4A841'), // ذهبي
                'gray'     => Color::Slate,
                'info'     => Color::Sky,
                'success'  => Color::Emerald,
                'warning'  => Color::Amber,
                'danger'   => Color::Rose,
            ])
            ->darkMode(true)        // وضع داكن افتراضياً (Navy)
            ->brandName('صرح الإتقان')
            ->brandLogo(asset('images/logo.svg'))
            ->favicon(asset('images/favicon.ico'))
            ->navigationGroups([
                NavigationGroup::make('الموارد البشرية')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make('التقارير والمالية')
                    ->icon('heroicon-o-chart-bar'),
                NavigationGroup::make('التواصل')
                    ->icon('heroicon-o-megaphone'),
                NavigationGroup::make('الأمان')
                    ->icon('heroicon-o-shield-check')
                    ->collapsed(),
            ])
            ->resources([
                UserResource::class,
                BranchResource::class,
                AttendanceLogResource::class,
                LeaveRequestResource::class,
                CircularResource::class,
                WhistleblowerReportResource::class,
                BadgeResource::class,
                MonthlyReportResource::class,
                AnomalyLogResource::class,
            ])
            ->widgets([
                StatsOverviewWidget::class,
                AttendanceTodayWidget::class,
                CompanyLeaderboardWidget::class,
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
