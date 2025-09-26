<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class LineMessagingService
{
    public function __construct(private string $token) {}

    public function getProfile(string $userId): array
    {
        $res = Http::withToken($this->token)
            ->get("https://api.line.me/v2/bot/profile/{$userId}");
        return $res->successful() ? $res->json() : [];
    }

    public function replyText(string $replyToken, string $text): void
    {
        Http::withToken($this->token)
            ->post('https://api.line.me/v2/bot/message/reply', [
                'replyToken' => $replyToken,
                'messages' => [['type' => 'text', 'text' => $text]],
            ]);
    }
}

