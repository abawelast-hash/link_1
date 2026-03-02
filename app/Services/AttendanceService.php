<?php

namespace App\Services;

use App\Events\AttendanceRecorded;
use App\Models\{AttendanceLog, User, Shift};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        private readonly GeofencingService $geofencingService
    ) {}

    // ──────────────────────────────────────────
    // تسجيل الدخول
    // ──────────────────────────────────────────

    public function checkIn(
        User  $user,
        float $latitude,
        float $longitude,
        bool  $isManual = false
    ): array {
        $today = now()->toDateString();

        // منع التسجيل المزدوج
        $existing = AttendanceLog::where('user_id', $user->id)
            ->whereDate('attendance_date', $today)
            ->whereNotNull('check_in_at')
            ->first();

        if ($existing) {
            return [
                'success' => false,
                'message' => 'تم تسجيل حضورك مسبقاً اليوم.',
                'log'     => $existing,
            ];
        }

        $shift    = $user->currentShift();
        $branch   = $user->branch;

        // التحقق الجغرافي
        $geoResult = ['within_geofence' => true, 'distance_meters' => 0];
        if (! $isManual && $branch && ! $user->isGodMode()) {
            $geoResult = $this->geofencingService->validatePosition($branch, $latitude, $longitude);

            if (! $geoResult['within_geofence']) {
                return [
                    'success'  => false,
                    'message'  => "أنت خارج نطاق الفرع ({$geoResult['distance_meters']} متر). الحد الأقصى المسموح: {$geoResult['allowed_radius']} متر.",
                    'distance' => $geoResult['distance_meters'],
                ];
            }

            // كشف السفر المستحيل
            $this->geofencingService->detectImpossibleTravel($user, $latitude, $longitude);
        }

        [$status, $delayMinutes, $deduction] = $this->calculateStatus($shift, $user);
        $points = $this->calculatePoints($status);

        return DB::transaction(function () use (
            $user, $latitude, $longitude, $today,
            $shift, $geoResult, $status, $delayMinutes, $deduction, $points, $isManual
        ) {
            $log = AttendanceLog::create([
                'user_id'             => $user->id,
                'branch_id'           => $user->branch_id,
                'shift_id'            => $shift?->id,
                'attendance_date'     => $today,
                'check_in_at'         => now(),
                'check_in_lat'        => $latitude,
                'check_in_lng'        => $longitude,
                'check_in_distance'   => $geoResult['distance_meters'],
                'status'              => $status,
                'delay_minutes'       => $delayMinutes,
                'financial_deduction' => $deduction,
                'points_earned'       => $points,
                'is_manual'           => $isManual,
            ]);

            // تحديث نقاط الموظف
            if ($points !== 0) {
                $user->adjustPoints(
                    $points,
                    'attendance',
                    "حضور {$today} — {$log->status_label}",
                    $log
                );
            }

            event(new AttendanceRecorded($log));

            return [
                'success' => true,
                'message' => 'تم تسجيل حضورك بنجاح.',
                'log'     => $log,
                'points'  => $points,
            ];
        });
    }

    // ──────────────────────────────────────────
    // تسجيل الخروج
    // ──────────────────────────────────────────

    public function checkOut(
        User  $user,
        float $latitude,
        float $longitude
    ): array {
        $log = AttendanceLog::where('user_id', $user->id)
            ->whereDate('attendance_date', now()->toDateString())
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->first();

        if (! $log) {
            return ['success' => false, 'message' => 'لا يوجد تسجيل دخول اليوم.'];
        }

        $overtimeHours = 0;
        if ($log->shift_id && $log->check_in_at) {
            $shiftEnd  = Carbon::parse($log->attendance_date->format('Y-m-d') . ' ' . $log->shift->end_time);
            $checkOut  = now();
            $overtime  = max(0, $checkOut->diffInMinutes($shiftEnd));
            $overtimeHours = round($overtime / 60, 2);
        }

        $log->update([
            'check_out_at'   => now(),
            'check_out_lat'  => $latitude,
            'check_out_lng'  => $longitude,
            'overtime_hours' => $overtimeHours,
        ]);

        return ['success' => true, 'message' => 'تم تسجيل انصرافك بنجاح.', 'log' => $log];
    }

    // ──────────────────────────────────────────
    // حسابات الحالة والنقاط
    // ──────────────────────────────────────────

    private function calculateStatus(?Shift $shift, User $user): array
    {
        if (! $shift) {
            return ['on_time', 0, 0.0];
        }

        $shiftStart  = Carbon::parse(now()->format('Y-m-d') . ' ' . $shift->start_time);
        $gracePeriod = $shiftStart->copy()->addMinutes($shift->grace_minutes ?? 10);
        $now         = now();

        if ($now->lte($gracePeriod)) {
            return ['on_time', 0, 0.0];
        }

        $delayMinutes = (int) $now->diffInMinutes($shiftStart);
        $deduction    = round(($delayMinutes / 60) * (float) $user->hourly_rate, 2);

        return ['late', $delayMinutes, $deduction];
    }

    private function calculatePoints(string $status): int
    {
        return match ($status) {
            'on_time' => (int) \App\Models\CompetitionSetting::getValue('points_on_time', 10),
            'late'    => (int) \App\Models\CompetitionSetting::getValue('points_late', 0),
            'absent'  => (int) \App\Models\CompetitionSetting::getValue('points_absent', -5),
            default   => 0,
        };
    }
}
