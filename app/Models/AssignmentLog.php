<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentLog extends Model
{
    protected $fillable = ['assignment_id','actor_id','action','from_status','to_status','note','ip'];
    public function assignment(){ return $this->belongsTo(Assignment::class); }
    public function actor(){ return $this->belongsTo(User::class,'actor_id'); }

    public function report()     { return $this->belongsTo(Report::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
    public function logs()       { return $this->hasMany(AssignmentLog::class); }
}
