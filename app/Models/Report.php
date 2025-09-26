<?php

// app/Models/Report.php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        return $q->where(function ($w) use ($term) {
            $w->where('device_address', 'like', "%{$term}%")
              ->orWhere('device_list', 'like', "%{$term}%")
              ->orWhere('detail', 'like', "%{$term}%")
              ->orWhereHas('user', function ($u) use ($term) {
                  $u->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
              });
        });
    }

    /** กรองสถานะ */
    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        $status = trim((string) $status);
        return $status === '' ? $q : $q->where('status', $status);
    }

    /** กรองช่วงวันที่จาก created_at */
    public function scopeDateRange(Builder $q, ?string $from, ?string $to): Builder
    {
        if ($from) $q->whereDate('created_at', '>=', $from);
        if ($to)   $q->whereDate('created_at', '<=', $to);
        return $q;
    }

    // app/Models/Report.php
    public function images() {
        return $this->hasMany(\App\Models\ReportImage::class);
    }

}

