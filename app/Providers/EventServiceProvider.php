<?php

namespace App\Providers;

use App\Events\AttendanceRecorded;
use App\Events\AnomalyDetected;
use App\Events\BadgeAwarded;
use App\Listeners\EvaluateBadgesOnAttendance;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * ربط الأحداث بمستمعيها.
     *
     * @var array<string, list<string>>
     */
    protected $listen = [
        // حدث تسجيل الحضور → تقييم الشارات
        AttendanceRecorded::class => [
            EvaluateBadgesOnAttendance::class,
        ],

        // حدث الشاذات → (قابل للتوسعة لاحقاً)
        AnomalyDetected::class => [],

        // حدث منح الشارة → (قابل للتوسعة لاحقاً)
        BadgeAwarded::class => [],

        // حدث تسجيل مستخدم جديد → إرسال تحقق البريد
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
