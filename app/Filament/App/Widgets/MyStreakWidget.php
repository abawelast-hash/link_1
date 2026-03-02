<?php
namespace App\Filament\App\Widgets;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\AttendanceLog;
class MyStreakWidget extends BaseWidget {
    protected static ?int $sort = 3;
    protected function getStats(): array {
        $user  = auth()->user();
        $logs  = AttendanceLog::where('user_id', $user->id)
            ->where('attendance_date', '>=', now()->subDays(30))
            ->orderBy('attendance_date', 'desc')
            ->get();
        $streak = 0;
        foreach ($logs as $log) {
            if ($log->status === 'on_time') $streak++;
            else break;
        }
        $branchRank = \App\Models\User::where('branch_id', $user->branch_id)
            ->where('total_points', '>', $user->total_points)->count() + 1;
        return [
            Stat::make('??????? ???????', "{$streak} ??? ??????")
                ->color($streak >= 7 ? 'success' : ($streak >= 3 ? 'warning' : 'gray'))
                ->descriptionIcon('heroicon-m-fire'),
            Stat::make('?????? ?? ?????', "#{$branchRank}")
                ->color('info')->descriptionIcon('heroicon-m-chart-bar'),
        ];
    }
}
