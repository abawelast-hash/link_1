<?php

namespace App\Services;

use App\Models\{User, Branch, AttendanceLog, MonthlyReport};
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * إحصائيات الحضور اليومية للفرع.
     */
    public function dailyBranchStats(Branch $branch, string $date): array
    {
        $logs = AttendanceLog::where('branch_id', $branch->id)
            ->whereDate('attendance_date', $date)
            ->with('user')
            ->get();

        return [
            'total'    => $logs->count(),
            'on_time'  => $logs->where('status', 'on_time')->count(),
            'late'     => $logs->where('status', 'late')->count(),
            'absent'   => $logs->where('status', 'absent')->count(),
            'excused'  => $logs->where('status', 'excused')->count(),
            'rate'     => $logs->count() > 0
                ? round($logs->where('status', 'on_time')->count() / $logs->count() * 100, 1)
                : 0,
        ];
    }

    /**
     * لوحة المنافسة — ترتيب الموظفين في الفرع.
     */
    public function branchLeaderboard(Branch $branch): Collection
    {
        return User::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->get(['id', 'name', 'total_points', 'avatar']);
    }

    /**
     * ترتيب الفروع على مستوى الشركة.
     */
    public function companyLeaderboard(): Collection
    {
        return Branch::where('is_active', true)
            ->orderBy('total_points', 'desc')
            ->get(['id', 'name', 'code', 'city', 'total_points', 'level']);
    }

    /**
     * إحصائيات موظف لشهر معين.
     */
    public function userMonthlyStats(User $user, int $year, int $month): array
    {
        $logs = AttendanceLog::where('user_id', $user->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get();

        return [
            'present'       => $logs->whereIn('status', ['on_time', 'late'])->count(),
            'on_time'       => $logs->where('status', 'on_time')->count(),
            'late'          => $logs->where('status', 'late')->count(),
            'absent'        => $logs->where('status', 'absent')->count(),
            'total_points'  => $logs->sum('points_earned'),
            'total_deduction' => $logs->sum('financial_deduction'),
            'overtime_hours'  => $logs->sum('overtime_hours'),
        ];
    }

    /**
     * كشف الشذوذات — موظفون لم يسجلوا حضوراً.
     */
    public function detectMissingCheckIns(Branch $branch, string $date): Collection
    {
        $presentIds = AttendanceLog::where('branch_id', $branch->id)
            ->whereDate('attendance_date', $date)
            ->pluck('user_id');

        return User::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->whereNotIn('id', $presentIds)
            ->get(['id', 'name', 'employee_id']);
    }
}
