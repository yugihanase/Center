<?php

if (! function_exists('activity_log')) {
    function activity_log(array $data): void
    {
        \App\Models\ActivityLog::create([
            'user_id'      => $data['user_id'] ?? optional(auth()->user())->id,
            'event'        => $data['event'] ?? 'log',
            'subject_type' => optional($data['subject'])->getMorphClass(),
            'subject_id'   => optional($data['subject'])->getKey(),
            'description'  => $data['description'] ?? null,
            'properties'   => $data['properties'] ?? [],
            'ip'           => $data['ip'] ?? request()->ip(),
            'user_agent'   => $data['user_agent'] ?? request()->userAgent(),
            'method'       => $data['method'] ?? request()->method(),
            'url'          => $data['url'] ?? request()->fullUrl(),
            'status_code'  => $data['status_code'] ?? null,
            'performed_at' => $data['performed_at'] ?? now(),
        ]);
    }
}
