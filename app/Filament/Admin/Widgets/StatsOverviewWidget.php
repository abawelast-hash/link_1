<?php

namespace App\Filament\Admin\Widgets;

use App\Models\{AttendanceLog, User, Branch, WhistleblowerReport};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today         = today()->toDateString();
        $totalUsers    = User::where('is_active', true)->count();
        $presentToday  = AttendanceLog::whereDate('attendance_date', $today)
                            ->where('status', '!=', 'absent')->count();
        $lateToday     = AttendanceLog::whereDate('attendance_date', $today)
                            ->where('status', 'late')->count();
        $openReports   = WhistleblowerReport::whereNotIn('status', ['resolved', 'closed'])->count();

        $attendanceRate = $totalUsers > 0
            ? round($presentToday / $totalUsers * 100, 1)
            : 0;

        return [
            Stat::make('إجمالي الموظفين', number_format($totalUsers))
                ->description('موظف نشط')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('الحضور اليوم', "{$presentToday} / {$totalUsers}")
                ->description("نسبة الحضور: {$attendanceRate}%")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($attendanceRate >= 80 ? 'success' : 'warning'),

            Stat::make('المتأخرون اليوم', $lateToday)
                ->descriptionIcon('heroicon-m-clock')
                ->color($lateToday > 0 ? 'warning' : 'success'),

            Stat::make('البلاغات المفتوحة', $openReports)
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($openReports > 0 ? 'danger' : 'success'),
        ];
    }
}
