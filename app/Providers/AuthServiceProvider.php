<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider; // <-- สำคัญ!
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // \App\Models\Report::class => \App\Policies\ReportPolicy::class,
    ];

    public function boot(): void
    {
        // ถ้าต้องการค้ำชั่วคราวให้ admin ผ่านทุก ability ให้ปลดคอมเมนต์บรรทัดล่างนี้
        // Gate::before(fn (User $user, ?string $ability = null) => $user->role === 'admin' ? true : null);

        Gate::define('manage-reports', fn (User $user) => $user->role === 'admin');
        Gate::define('manage-stock',   fn (User $user) => $user->role === 'admin');
        Gate::define('dispatch-jobs', fn($user) => in_array($user->role, ['admin','dispatcher']));

    }
}
