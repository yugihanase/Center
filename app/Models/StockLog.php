<?php
// app/Models/StockLog.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StockLog extends Model {
    protected $fillable = ['stock_id','direction','qty','note','user_id'];
    public function stock() { return $this->belongsTo(Stock::class); }
    public function user() { return $this->belongsTo(User::class); }
}
