<?php

// app/Models/Report.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'device_address',
        'device_list',
        'detail',
        'user_id',
    ];

    // ความสัมพันธ์ (ถ้าใช้ระบบล็อกอิน)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

