<?php
// app/Models/Technician.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Technician extends Model
{
    protected $fillable = [
        'employee_code','name','phone','email','role','department','is_active','user_id','notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ค้นหาเร็ว ๆ
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $like = "%{$term}%";
        return $q->where(function($w) use ($like) {
            $w->where('employee_code','like',$like)
              ->orWhere('name','like',$like)
              ->orWhere('phone','like',$like)
              ->orWhere('email','like',$like)
              ->orWhere('department','like',$like);
        });
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeLeads(Builder $q): Builder
    {
        return $q->where('role','lead');
    }
}
