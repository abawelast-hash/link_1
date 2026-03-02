<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:     __DIR__ . '/../routes/web.php',
        api:     __DIR__ . '/../routes/api.php',
        console: __DIR__ . '/../routes/console.php',
        health:  '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ثق بجميع الوكلاء على Hostinger (وراء Reverse Proxy)
        $middleware->trustProxies(at: '*');

        // إضافة middleware التحقق من المصادقة لبوابة الموظف
        $middleware->alias([
            'sarh.auth' => \App\Http\Middleware\SarhAuthenticate::class,
        ]);
    })
    ->withProviders([
        // تسجيل مزودي الخدمات الإضافيين
        App\Providers\EventServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
