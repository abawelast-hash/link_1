<?php
namespace App\Filament\App\Widgets;
use App\Models\AttendanceLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
class MyAttendanceTodayWidget extends BaseWidget {
    protected static ?int $sort = 1;
    protected function getStats(): array {
        $user = auth()->user();
        $today = today()->toDateString();
        $log = AttendanceLog::where('user_id', $user->id)->whereDate('attendance_date', $today)->first();
        $monthLogs = AttendanceLog::where('user_id', $user->id)->whereYear('attendance_date', now()->year)->whereMonth('attendance_date', now()->month)->get();
        return [
            Stat::make('???? ?????', $log ? $log->status_label : ' ?? ???? ???')
                ->color($log ? match($log->status) { 'on_time'=>'success', 'late'=>'warning', default=>'danger' } : 'gray'),
            Stat::make('???? ?????? ??? ?????', $monthLogs->whereIn('status', ['on_time','late'])->count())
                ->description('?? ' . now()->daysInMonth . ' ??? ???')
                ->color('info'),
            Stat::make('?????? ????????', number_format($monthLogs->sum('financial_deduction'), 2) . ' ????')
                ->color($monthLogs->sum('financial_deduction') > 0 ? 'danger' : 'success'),
        ];
    }
}
