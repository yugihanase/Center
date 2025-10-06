<?php
// app/Support/activity.php
use App\Models\ActivityLog;

if (! function_exists('activity_log')) {
    /**
     * บันทึกกิจกรรม
     */
    function activity_log(array $data): ActivityLog
    {
        $request = request();

        return ActivityLog::create([
            'user_id'     => auth()->id(),
            'event'       => $data['event'] ?? 'event',
            'subject_type'=> $data['subject'] ? get_class($data['subject']) : null,
            'subject_id'  => $data['subject']->getKey() ?? null,
            'description' => $data['description'] ?? null,
            'properties'  => $data['properties'] ?? null,
            'ip'          => $data['ip'] ?? ($request?->ip()),
            'user_agent'  => $data['user_agent'] ?? ($request?->userAgent()),
            'method'      => $data['method'] ?? ($request?->method()),
            'url'         => $data['url'] ?? ($request?->fullUrl()),
            'status_code' => $data['status_code'] ?? null,
            'performed_at'=> now(),
        ]);
    }
}
