<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // อื่น ๆ ...
    }

    public function boot(): void
    {
        // ... โค้ดเดิมของคุณก่อนหน้า

        // หลัง "สมัครสมาชิก" เสร็จ: ส่งผู้ใช้ไปยังหน้า home ของบทบาท
        $this->app->singleton(RegisterResponseContract::class, function () {
            return new class implements RegisterResponseContract {
                public function toResponse($request): RedirectResponse
                {
                    $user = $request->user();
                    // ต้องมีเมธอดนี้ใน User model (ดูด้านล่าง)
                    return redirect()->intended($user->homeRoute());
                }
            };
        });
    }
}
