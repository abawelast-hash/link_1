<?php

namespace App\Services;

use App\Events\BadgeAwarded;
use App\Models\{User, Badge, UserBadge, AttendanceLog};
use Carbon\Carbon;

/**
 * محرك الشارات — يفحص شروط الشارات ويمنحها تلقائياً.
 */
class BadgeService
{
    /**
     * فحص وتوزيع جميع الشارات المستحقة لمستخدم بعد تسجيل الحضور.
     */
    public function evaluateBadges(User $user): void
    {
        $badges = Badge::where('is_active', true)->get();

        foreach ($badges as $badge) {
            if ($this->isEligible($user, $badge)) {
                $this->awardBadge($user, $badge);
            }
        }
    }

    /**
     * التحقق من أهلية الحصول على الشارة.
     */
    public function isEligible(User $user, Badge $badge): bool
    {
        $params  = $badge->condition_params ?? [];
        $period  = now()->format('Y-m');

        // منع التكرار في نفس الشهر
        $alreadyAwarded = UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->where('period', $period)
            ->exists();

        if ($alreadyAwarded) {
            return false;
        }

        return match ($badge->condition_type) {
            'streak_days'       => $this->checkStreak($user, (int) ($params['days'] ?? 7)),
            'monthly_no_late'   => $this->checkMonthlyNoLate($user),
            'monthly_no_absent' => $this->checkMonthlyNoAbsent($user),
            'monthly_overtime'  => $this->checkMonthlyOvertime($user, (float) ($params['hours'] ?? 50)),
            'manual'            => false, // تُمنح يدوياً فقط
            default             => false,
        };
    }

    /**
     * منح شارة للمستخدم وتسجيل النقاط.
     */
    public function awardBadge(User $user, Badge $badge): UserBadge
    {
        $userBadge = UserBadge::create([
            'user_id'    => $user->id,
            'badge_id'   => $badge->id,
            'awarded_at' => now()->toDateString(),
            'period'     => now()->format('Y-m'),
        ]);

        if ($badge->points_reward > 0) {
            $user->adjustPoints(
                $badge->points_reward,
                'badge',
                "شارة: {$badge->name}",
                $userBadge
            );
        }

        event(new BadgeAwarded($user, $badge));

        return $userBadge;
    }

    // ──────────────────────────────────────────
    // شروط الشارات
    // ──────────────────────────────────────────

    /** سلسلة أيام متتالية بدون غياب أو تأخير */
    private function checkStreak(User $user, int $days): bool
    {
        $logs = AttendanceLog::where('user_id', $user->id)
            ->where('attendance_date', '>=', now()->subDays($days)->toDateString())
            ->orderBy('attendance_date')
            ->get();

        if ($logs->count() < $days) {
            return false;
        }

        return $logs->every(fn($log) => $log->status === 'on_time');
    }

    /** شهر بدون تأخير — "سيد الانضباط" */
    private function checkMonthlyNoLate(User $user): bool
    {
        return ! AttendanceLog::where('user_id', $user->id)
            ->whereYear('attendance_date', now()->year)
            ->whereMonth('attendance_date', now()->month)
            ->where('status', 'late')
            ->exists();
    }

    /** شهر بدون غياب — "صفر خسائر" */
    private function checkMonthlyNoAbsent(User $user): bool
    {
        return ! AttendanceLog::where('user_id', $user->id)
            ->whereYear('attendance_date', now()->year)
            ->whereMonth('attendance_date', now()->month)
            ->where('status', 'absent')
            ->exists();
    }

    /** ساعات إضافية تراكمية — "موفر التكاليف" */
    private function checkMonthlyOvertime(User $user, float $hours): bool
    {
        $total = AttendanceLog::where('user_id', $user->id)
            ->whereYear('attendance_date', now()->year)
            ->whereMonth('attendance_date', now()->month)
            ->sum('overtime_hours');

        return $total >= $hours;
    }
}
