<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages\{
    AttendancePage,
    MyProfilePage,
    MyBadgesPage,
    MyReportsPage,
    LeaveRequestPage,
    InboxPage,
    CircularsPage,
    WhistleblowerPage,
};
use App\Filament\App\Widgets\{
    MyAttendanceTodayWidget,
    MyPointsWidget,
    MyStreakWidget,
};
use Filament\Http\Middleware\{Authenticate, DisableBladeIconComponents, DispatchServingFilamentEvent};
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\{AddQueuedCookiesToResponse, EncryptCookies};
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\{AuthenticateSession, StartSession};
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->login()
            ->colors([
                'primary' => Color::hex('#D4A841'),
                'gray'    => Color::Slate,
            ])
            ->darkMode(true)
            ->brandName('صرح — بوابة الموظف')
            ->brandLogo(asset('images/logo.svg'))
            ->favicon(asset('images/favicon.ico'))
            ->pages([
                AttendancePage::class,
                MyProfilePage::class,
                MyBadgesPage::class,
                MyReportsPage::class,
                LeaveRequestPage::class,
                InboxPage::class,
                CircularsPage::class,
                WhistleblowerPage::class,
            ])
            ->widgets([
                MyAttendanceTodayWidget::class,
                MyPointsWidget::class,
                MyStreakWidget::class,
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
            ->viteTheme('resources/css/filament/app/theme.css');
    }
}
