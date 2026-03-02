<?php

namespace App\Services;

use App\Models\{User, Branch, AttendanceLog, MonthlyReport};
use Illuminate\Support\Facades\DB;

class FinancialReportingService
{
    /**
     * توليد أو تحديث التقرير المالي الشهري لموظف.
     */
    public function generateMonthlyReport(User $user, int $year, int $month): MonthlyReport
    {
        $logs = AttendanceLog::where('user_id', $user->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->get();

        $workingDays    = $this->getWorkingDays($year, $month);
        $presentDays    = $logs->whereIn('status', ['on_time', 'late'])->count();
        $lateDays       = $logs->where('status', 'late')->count();
        $absentDays     = max(0, $workingDays - $presentDays - $logs->where('status', 'excused')->count());
        $excusedDays    = $logs->where('status', 'excused')->count();
        $overtimeHours  = round($logs->sum('overtime_hours'), 2);
        $totalDeduction = round($logs->sum('financial_deduction'), 2);
        $baseSalary     = $user->hourly_rate * 8 * $workingDays; // 8 ساعات × أيام العمل
        $netSalary      = max(0, $baseSalary - $totalDeduction);
        $totalPoints    = $logs->sum('points_earned');

        return MonthlyReport::updateOrCreate(
            ['user_id' => $user->id, 'year' => $year, 'month' => $month],
            [
                'branch_id'            => $user->branch_id,
                'working_days'         => $workingDays,
                'present_days'         => $presentDays,
                'late_days'            => $lateDays,
                'absent_days'          => $absentDays,
                'excused_days'         => $excusedDays,
                'total_overtime_hours' => $overtimeHours,
                'total_deduction'      => $totalDeduction,
                'base_salary'          => $baseSalary,
                'net_salary'           => $netSalary,
                'total_points'         => $totalPoints,
                'generated_at'         => now(),
            ]
        );
    }

    /**
     * توليد تقارير شهرية لجميع موظفي الفرع.
     */
    public function generateBranchMonthlyReports(Branch $branch, int $year, int $month): int
    {
        $count = 0;

        User::where('branch_id', $branch->id)
            ->where('is_active', true)
            ->each(function (User $user) use ($year, $month, &$count) {
                $this->generateMonthlyReport($user, $year, $month);
                $count++;
            });

        return $count;
    }

    /**
     * حساب إجمالي خسائر الفرع لشهر معين.
     */
    public function branchMonthlyDeductions(Branch $branch, int $year, int $month): array
    {
        $reports = MonthlyReport::where('branch_id', $branch->id)
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        return [
            'total_deductions'    => $reports->sum('total_deduction'),
            'total_base_salary'   => $reports->sum('base_salary'),
            'total_net_salary'    => $reports->sum('net_salary'),
            'total_overtime_hours' => $reports->sum('total_overtime_hours'),
            'employees_count'     => $reports->count(),
        ];
    }

    /**
     * عدد أيام العمل الفعلية في شهر (الجمعة + السبت عطل افتراضياً).
     */
    private function getWorkingDays(int $year, int $month): int
    {
        $start   = now()->setDate($year, $month, 1)->startOfDay();
        $end     = $start->copy()->endOfMonth();
        $days    = 0;

        while ($start->lte($end)) {
            // 5 = الجمعة، 6 = السبت
            if (! in_array($start->dayOfWeek, [5, 6])) {
                $days++;
            }
            $start->addDay();
        }

        return $days;
    }
}
