<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'technician_id',
        'assigned_by',
        'status',
        'priority',
        'eta',
        'started_at',
        'finished_at',
        'note',
    ];

    protected $casts = [
        'eta'         => 'datetime',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    /** สถานะที่ถือว่า "เปิดอยู่" */
    public const OPEN_STATUSES = ['มอบหมาย','กำลังดำเนินการ'];

    // ---------- Scopes ----------
    public function scopeOpen($q)
    {
        return $q->whereIn('status', self::OPEN_STATUSES);
    }

    // (เผื่ออยากใช้งาน) ปิดงาน
    public function scopeClosed($q)
    {
        return $q->whereIn('status', ['เสร็จสิ้น','ยกเลิก']);
    }

    // ---------- Relations ----------
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function logs()
    {
        return $this->hasMany(AssignmentLog::class);
    }
}
