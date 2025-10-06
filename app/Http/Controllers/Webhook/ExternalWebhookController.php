<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessExternalEvent;

class ExternalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $provider = 'systemA';
        $signature = $request->header('X-Signature') ?? '';
        $secret = config('services.systemA.webhook_secret');

        // ตรวจ HMAC-SHA256 ลายเซ็น
        $computed = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));
        if (!hash_equals($computed, $signature)) {
            Log::warning('Webhook signature mismatch');
            return response()->json(['ok'=>false], 401);
        }

        $payload = $request->json()->all();
        $eventId = data_get($payload, 'id') ?? (string) \Str::uuid();
        $eventType = data_get($payload, 'type', 'unknown');

        // เก็บลง incoming_events (กันซ้ำ)
        $eventRowId = DB::table('incoming_events')->upsert([[
            'provider'   => $provider,
            'event_id'   => $eventId,
            'event_type' => $eventType,
            'signature'  => $signature,
            'payload'    => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]], uniqueBy: ['provider','event_id'], update: ['updated_at']);

        // ส่งเข้า Queue ประมวลผลจริง
        ProcessExternalEvent::dispatch($provider, $eventId);

        // ตอบ 200 เร็ว ๆ
        return response()->json(['ok'=>true]);
    }
}

class ExternalWebhookController extends Controller
{
    //
}
