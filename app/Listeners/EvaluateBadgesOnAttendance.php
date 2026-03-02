<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use App\Services\BadgeService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * يُشغّل محرك الشارات بعد كل تسجيل حضور.
 */
class EvaluateBadgesOnAttendance implements ShouldQueue
{
    public string $queue = 'badges';

    public function __construct(private readonly BadgeService $badgeService) {}

    public function handle(AttendanceRecorded $event): void
    {
        $this->badgeService->evaluateBadges($event->log->user);
    }
}
