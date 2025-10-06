<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
        \App\Providers\AuthServiceProvider::class,
        \App\Providers\EventServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // ตั้งชื่อ (alias) สำหรับมิดเดิลแวร์ที่ใช้ใน routes
        $middleware->alias([
            'admin.only' => \App\Http\Middleware\AdminOnly::class,
            'technician' => \App\Http\Middleware\EnsureTechnician::class,
            // 'local.autologin' => \App\Http\Middleware\LocalAutoLogin::class,
        ]);

        // ถ้าต้องปรับสแต็ก web/api เอง สามารถใช้:
        // $middleware->web( ... );
        // $middleware->api( ... );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
