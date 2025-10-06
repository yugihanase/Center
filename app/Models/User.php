<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * ฟิลด์ที่ให้กรอกแบบ mass-assign ได้
     * เพิ่มคอลัมน์ที่คุณมีจริงในตาราง users เท่านั้น
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        // ถ้ามีคอลัมน์เหล่านี้จริงใน DB ค่อยเติม
        // 'is_online', 'last_login_at', 'last_logout_at',
    ];

    /**
     * ฟิลด์ที่ต้องซ่อนไม่ serialize ออกไป
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting ชนิดข้อมูล
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'last_logout_at'    => 'datetime',
        'is_online'         => 'boolean',
    ];

    public function lineUser()
    {
        return $this->hasOne(\App\Models\LineUser::class);
    }

    /**
     * เส้นทางหน้าแรกตามบทบาท
     */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin'      => route('admin.dashboard'),
            'technician' => route('technician.jobs.index'),
            default      => route('report.follow'),
        };
    }

    // งานที่ "ผู้ใช้คนนี้" รับผิดชอบในฐานะช่าง
    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Assignment::class, 'technician_id');
    }

    public function assignedJobs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Assignment::class, 'assigned_by');
    }

    // เฉพาะผู้ใช้ที่เป็นช่าง
    public function scopeTechnicians($q)
    {
        return $q->where('role','technician');
    }

    public function assignedReports()
    {
        return $this->belongsToMany(Report::class, 'report_technician', 'technician_id', 'report_id')
            ->withPivot(['role','assigned_by','assigned_at','assignment_status','finished_at','note'])
            ->withTimestamps();
    }
}
