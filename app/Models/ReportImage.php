<?php
// app/Models/ReportImage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportImage extends Model
{
    protected $fillable = ['report_id','path','original_name','mime','size_kb'];

    public function report() {
        return $this->belongsTo(Report::class);
    }

    // ช่วยให้เรียก url ได้สะดวก
    public function url(): string {
        return asset('storage/'.$this->path);
    }
}
