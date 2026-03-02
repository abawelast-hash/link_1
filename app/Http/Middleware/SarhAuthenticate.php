<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;

/**
 * Middleware مخصصة لنظام صرح الإتقان.
 *
 * تُعيد التوجيه إلى صفحة تسجيل الدخول الخاصة ببوابة الموظف
 * بدلاً من المسار الافتراضي /login.
 */
class SarhAuthenticate extends Authenticate
{
    protected function redirectTo(\Illuminate\Http\Request $request): ?string
    {
        return $request->expectsJson() ? null : route('filament.app.auth.login');
    }
}
