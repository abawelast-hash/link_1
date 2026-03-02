<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| جميع مسارات لوحتي Filament (admin / app) مسجَّلة تلقائياً
| عبر AdminPanelProvider و AppPanelProvider.
|
| هذا الملف يُسجِّل مسارات المساعدة الإضافية فقط.
|
*/

// إعادة التوجيه من الجذر إلى بوابة الموظف
Route::get('/', fn () => redirect('/app'));

// صحة النظام — للاستخدام من Hostinger Health Check
Route::get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'system'  => 'صرح الإتقان v3.0',
        'server'  => now()->toDateTimeString(),
    ]);
})->name('health');
