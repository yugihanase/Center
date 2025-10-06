<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id','event','subject_type','subject_id','description',
        'properties','ip','user_agent','method','url','status_code','performed_at'
    ];

    protected $casts = [
        'properties'  => 'array',
        'performed_at'=> 'datetime',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function subject() { return $this->morphTo(); }
}
