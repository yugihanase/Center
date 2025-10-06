<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cache;
use App\Models\ActivityLog;

class LogUserLogout
{
    public function handle(Logout $event): void
    {
        $u = $event->user;
        if (! $u) return;

        // ถ้ามีหลาย guard แล้วต้องการเฉพาะ web ค่อยปลดคอมเมนต์
        // if (!empty($event->guard) && $event->guard !== 'web') return;

        // 1) Dedupe แบบอะตอมมิก (กันซ้ำข้าม process/worker)
        // อย่าใช้ session()->getId() ตอน logout เพราะบาง flow มันรีเซสชั่นแล้วเป็นค่าว่าง
        $key = 'dedupe:logout:'.$u->id.':'.(request()->ip() ?? 'noip');
        if (! Cache::add($key, true, now()->addSeconds(20))) {
            return; // มีเคสซ้ำในช่วง 20 วิ — ข้าม
        }

        // 2) กันซ้ำแบบหน้าต่างเวลา (เหมือน login)
        $window = now()->subSeconds(3);
        $exists = ActivityLog::where('event', 'logout')
            ->where('subject_type', $u->getMorphClass())
            ->where('subject_id', $u->getKey())
            ->where('performed_at', '>=', $window)
            ->exists();
        if ($exists) return;

        // 3) อัปเดตสถานะผู้ใช้
        $u->forceFill([
            'last_logout_at' => now(),
            'is_online'      => false,
        ])->save();

        // 4) เขียนล็อก (เลือกทางใดทางหนึ่ง)
        if (function_exists('activity_log')) {
            activity_log([
                'event'        => 'logout',
                'subject'      => $u,
                'description'  => "ผู้ใช้ {$u->name} ออกจากระบบ",
                'properties'   => [
                    'ip'         => request()->ip(),
                    'ua'         => request()->userAgent(),
                    'method'     => request()->method(),
                    'url'        => request()->fullUrl(),
                ],
                'performed_at' => now(),
            ]);
        } else {
            ActivityLog::create([
                'user_id'      => $u->id,
                'event'        => 'logout',
                'subject_type' => $u->getMorphClass(),
                'subject_id'   => $u->getKey(),
                'description'  => "ผู้ใช้ {$u->name} ออกจากระบบ",
                'ip'           => request()->ip(),
                'user_agent'   => request()->userAgent(),
                'method'       => request()->method(),
                'url'          => request()->fullUrl(),
                'performed_at' => now(),
            ]);
        }
    }
}
