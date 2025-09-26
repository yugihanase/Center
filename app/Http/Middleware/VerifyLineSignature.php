<?php
// app/Http/Middleware/VerifyLineSignature.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyLineSignature
{
    public function handle(Request $request, Closure $next)
    {
        $channelSecret = config('services.line_bot.channel_secret');
        $signature     = $request->header('X-Line-Signature');

        // คำนวณ HMAC-SHA256 ของ raw body แล้ว base64_encode
        $computed = base64_encode(hash_hmac('sha256', $request->getContent(), $channelSecret, true));

        if (!$signature || !hash_equals($computed, $signature)) {
            return response('Invalid signature', 400);
        }
        return $next($request);
    }
}