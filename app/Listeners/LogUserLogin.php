<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Support\activity;
use Illuminate\Support\Carbon;
use App\Models\ActivityLog;
//use App\Services\LineMessagingService;

class LogUserLogin
{
    /*public function __construct(
        private LineMessagingService $line // ถ้าอยากส่งแจ้งเตือน LINE
    ) {}*/

    public function handle(Login $event): void
    {
        $u = $event->user;
        $window = \Illuminate\Support\Carbon::now()->subSeconds(3);

        $exists = ActivityLog::where('event', 'login')
            ->where('subject_type', $u->getMorphClass())
            ->where('subject_id', $u->getKey())
            ->where('performed_at', '>=', $window)
            ->exists();
        if ($exists) return;

        $props = [
            'ip'         => request()->ip(),
            'user_agent' => request()->userAgent(),
            'method'     => request()->method(),
            'url'        => request()->fullUrl(),
            'guard'      => $event->guard ?? null,
        ];

        ActivityLog::create([
            'user_id'      => $u->id,
            'event'        => 'login',
            'subject_type' => $u->getMorphClass(),
            'subject_id'   => $u->getKey(),
            'description'  => "ผู้ใช้ {$u->name} เข้าระบบ",
            'ip'           => $props['ip'],
            'user_agent'   => $props['user_agent'],
            'method'       => $props['method'],
            'url'          => $props['url'],
            'performed_at' => now(),
            'properties'   => $props,              // ★ ใส่ properties เสมอ
        ]);
    }

}
