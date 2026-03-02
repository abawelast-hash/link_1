<?php

namespace App\Providers;

use App\Models\{AttendanceLog, User};
use App\Policies\{AttendanceLogPolicy, UserPolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class          => UserPolicy::class,
        AttendanceLog::class => AttendanceLogPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // ──────────────────────────────────────────
        // God Mode — تجاوز مطلق لكل الصلاحيات
        // ──────────────────────────────────────────
        Gate::before(function (User $user, string $ability) {
            if ($user->security_level >= 10 || $user->is_super_admin) {
                return true;
            }
        });

        // ──────────────────────────────────────────
        // البوابات المخصصة
        // ──────────────────────────────────────────

        /** قبو البلاغات المشفرة */
        Gate::define('access-whistleblower-vault', function (User $user) {
            return $user->security_level >= 10;
        });

        /** تجاوز السياج الجغرافي */
        Gate::define('bypass-geofence', function (User $user) {
            return $user->security_level >= 10;
        });

        /** إدارة إعدادات المنافسة */
        Gate::define('manage-competition', function (User $user) {
            return $user->security_level >= 10;
        });

        /** تعديل نقاط الموظفين يدوياً */
        Gate::define('adjust-points', function (User $user) {
            return $user->security_level >= 10;
        });

        /** إرسال تعاميم لجميع الفروع */
        Gate::define('send-company-circulars', function (User $user) {
            return $user->security_level >= 7;
        });

        /** اعتماد/رفض طلبات الإجازة */
        Gate::define('review-leaves', function (User $user) {
            return $user->security_level >= 4;
        });
    }
}
