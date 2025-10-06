<?php

// app/Models/Report.php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    public const CLOSED_STATUSES = ['เสร็จสิ้น', 'ยกเลิก'];
    
    public function scopeActive($q)
    {
        return $q->whereNotIn('status', self::CLOSED_STATUSES);
        // ถ้ามี completed_at ก็เพิ่ม ->whereNull('completed_at')
    }
    
    protected $fillable = [
        'device_address','device_list','detail','status',
        'assigned_to','assigned_at','accepted_at','started_at',
        'completed_at','due_at','priority'
    ];

    protected $casts = [
        'assigned_at'=>'datetime',
        'accepted_at'=>'datetime',
        'started_at'=>'datetime',
        'completed_at'=>'datetime',
        'due_at'=>'datetime',
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

    public function technician(): BelongsTo {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** งานที่ถูกมอบหมายให้ user คนนี้ */
    public function scopeAssignedTo(Builder $q, int $userId): Builder {
        return $q->where('assigned_to', $userId);
    }

    /** งานที่ยังไม่ถูกมอบหมาย (คิวว่างสำหรับช่างไปกดรับ) */
    public function scopeUnassigned(Builder $q): Builder {
        return $q->whereNull('assigned_to')
                 ->whereIn('status', ['รอดำเนินการ','กำลังดำเนินการ']);
    }

    /** งานที่ยังต้องทำ (ของช่างคนนั้น) */
    public function scopeMyOpen(Builder $q, int $userId): Builder {
        return $q->where('assigned_to', $userId)
                 ->whereIn('status', ['รอดำเนินการ','กำลังดำเนินการ']);
    }

    public function currentAssignment()
    {
        // assignment ที่ยังเปิดอยู่ เอาตัวล่าสุด (ต้องมี created_at หรือ id auto-increment)
        return $this->hasOne(Assignment::class)
            ->whereIn('status', Assignment::OPEN_STATUSES)
            ->latestOfMany();
    }

    public function latestAssignment()
    {
        // assignment ล่าสุด (แม้จะปิดแล้ว)
        return $this->hasOne(Assignment::class)->latestOfMany();
    }

    public function technicians()
    {
        return $this->belongsToMany(User::class, 'report_technician', 'report_id', 'technician_id')
            ->withPivot(['role','assigned_by','assigned_at','assignment_status','finished_at','note'])
            ->withTimestamps();
    }

    // ตัวช่วยดึงหัวหน้าช่าง (ถ้ามี)
    public function leadTechnician()
    {
        return $this->technicians()->wherePivot('role','lead')->wherePivot('assignment_status','active');
    }
}

