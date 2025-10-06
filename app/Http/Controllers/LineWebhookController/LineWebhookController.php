<?php
// app/Http/Controllers/LineWebhookController.php
namespace App\Http\Controllers;

use App\Models\LineUser;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LineWebhookController extends Controller
{
    public function __construct(private LineMessagingService $line) {}

    public function handle(Request $request)
    {
        foreach ($request->input('events', []) as $event) {
            $type     = data_get($event, 'type');
            $userId   = data_get($event, 'source.userId');
            $replyTok = data_get($event, 'replyToken');

            if (!$userId) continue;

            // ดึงโปรไฟล์จาก LINE
            $profile = $this->line->getProfile($userId); // displayName, pictureUrl, statusMessage

            // upsert ลง line_users
            $lineUser = LineUser::updateOrCreate(
                ['line_user_id' => $userId],
                [
                    'display_name'  => $profile['displayName'] ?? null,
                    'avatar'        => $profile['pictureUrl'] ?? null,
                    'status_message'=> $profile['statusMessage'] ?? null,
                    'is_following'  => $type !== 'unfollow',
                    'last_event_at' => Carbon::now(),
                ]
            );

            // กรณี unfollow ให้บันทึกเวลาถอนเพื่อน
            if ($type === 'unfollow') {
                $lineUser->update([
                    'is_following'  => false,
                    'unfollowed_at' => Carbon::now(),
                ]);
                continue; // ไม่ต้อง reply
            }

            // ตอบตัวอย่างเมื่อเป็นข้อความ
            if ($type === 'message' && data_get($event, 'message.type') === 'text') {
                $text = trim((string) data_get($event, 'message.text'));
                $this->line->reply($replyTok, [
                    ['type' => 'text', 'text' => "คุณพิมพ์: {$text}"],
                ]);
            }

            // ต้อนรับเมื่อ follow
            if ($type === 'follow') {
                $this->line->reply($replyTok, [
                    ['type' => 'text', 'text' => "ขอบคุณที่เพิ่มเราเป็นเพื่อน! พิมพ์ 'help' เพื่อดูเมนู"],
                ]);
            }
            if ($type === 'message' && data_get($event, 'message.type') === 'text') {
            $text = trim((string) data_get($event, 'message.text'));

            // ถ้าผู้ใช้พิมพ์ "แจ้งซ่อม"
            if ($text === 'แจ้งซ่อม') {
                $this->line->reply($replyTok, [[
                    'type' => 'template',
                    'altText' => 'เมนูแจ้งซ่อม',
                    'template' => [
                        'type' => 'buttons',
                        'thumbnailImageUrl' => 'https://picsum.photos/600/400?random=1', // เอาออกได้
                        'title' => 'เมนูแจ้งซ่อม',
                        'text' => 'เลือกการทำรายการ',
                        'actions' => [
                            // ส่งข้อความกลับเข้าแชต (ให้บอทไปจับคีย์เวิร์ดขั้นต่อไป)
                            [
                                'type' => 'message',
                                'label' => 'สร้างคำขอใหม่',
                                'text'  => 'สร้างคำขอใหม่',
                            ],
                            // เปิดหน้า LIFF (ถ้ามี)
                            [
                                'type' => 'uri',
                                'label' => 'กรอกฟอร์ม (LIFF)',
                                'uri'   => 'https://liff.line.me/1655109480-wJLvOE9g'.env('LINE_LIFF_ID_REPAIR').'/repair',
                            ],
                            // ส่งข้อมูลแบบ postback เข้า Webhook
                            [
                                'type' => 'postback',
                                'label' => 'เช็คสถานะล่าสุด',
                                'data'  => 'action=last_status',
                                'displayText' => 'เช็คสถานะล่าสุด',
                            ],
                        ],
                    ],
                ]]);
                // อย่าลืม return เพื่อไม่ให้ไปตก default echo
                return response()->json(['ok' => true]);
            }

            // กรณีอื่น ๆ (default)
            $this->line->reply($replyTok, [['type' => 'text', 'text' => "คุณพิมพ์: {$text}"]]);
        }

        }

        return response()->json(['ok' => true]);
    }
}
