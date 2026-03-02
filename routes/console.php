<?php

use App\Jobs\RecalculateMonthlyReportsJob;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes (Scheduler)
|--------------------------------------------------------------------------
|
| يُستخدَم بواسطة: php artisan schedule:run
| يجب إضافة هذا الأمر في Cron Job على Hostinger:
|   * * * * * cd /home/u307296675/sarh && php artisan schedule:run >> /dev/null 2>&1
|
*/

// ── إعادة احتساب التقارير الشهرية ─────────────────────────────────────────
// تنفَّذ في اليوم الأول من كل شهر عند الساعة 00:30
Schedule::job(new RecalculateMonthlyReportsJob(now()->year, now()->month))
    ->monthlyOn(1, '00:30')
    ->name('monthly-reports-recalculate')
    ->withoutOverlapping()
    ->onSuccess(function () {
        logger('✅ RecalculateMonthlyReportsJob انتهت بنجاح.');
    });

// ── كشف تسجيلات الحضور المفقودة يومياً ─────────────────────────────────────
// تنفَّذ كل يوم عمل عند الساعة 10:00 صباحاً
Schedule::call(function () {
    app(AnalyticsService::class)->detectMissingCheckIns(now()->toDate());
})
    ->weekdays()
    ->dailyAt('10:00')
    ->name('detect-missing-check-ins')
    ->withoutOverlapping();
