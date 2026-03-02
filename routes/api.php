<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| مسارات API الداخلية لنظام صرح الإتقان v3.0.
| جميع المسارات محمية بـ sanctum token.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    // معلومات المستخدم المسجَّل
    Route::get('/user', fn (Request $request) => $request->user());

    // صحة النظام
    Route::get('/ping', fn () => response()->json(['pong' => now()->toISOString()]));

});
