<?php
// app/Http/Controllers/NotifyController.php
namespace App\Http\Controllers;

use App\Models\LineUser;
use App\Services\LineMessagingService;

class NotifyController extends Controller
{
    public function jobFinished(LineMessagingService $line, int $userId)
    {
        $lu = LineUser::where('user_id', $userId)
            ->where('is_following', true)
            ->first();

        if ($lu) {
            $line->push($lu->line_user_id, [
                ['type' => 'text', 'text' => 'งานของคุณเสร็จสิ้นแล้ว 🎉'],
            ]);
        }
        return back();
    }
}