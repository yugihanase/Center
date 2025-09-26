<?php
// app/Models/LineUser.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'line_user_id',
        'display_name',
        'avatar',
        'status_message',
        'language',
        'is_following',
        'last_event_at',
        'last_seen_at',
        'unfollowed_at',
        'meta',
    ];

    protected $casts = [
        'is_following'  => 'boolean',
        'last_event_at' => 'datetime',
        'last_seen_at'  => 'datetime',
        'unfollowed_at' => 'datetime',
        'meta'          => 'array',
    ];

    // ความสัมพันธ์กับ users (หนึ่งต่อหนึ่ง/หลายต่อหนึ่งก็ได้ แต่ปกติ one-to-one)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
