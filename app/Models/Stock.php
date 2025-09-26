<?php
// app/Models/Stock.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['name','stock_category_id','unit','qty_open'];
    protected $casts = [
        'qty_open' => 'integer',
    ];

    // โชว์ฟิลด์คำนวณ (อย่าใช้ชื่อชนกับ alias ของ withSum)
    protected $appends = ['book_qty', 'remain', 'added_qty_int', 'used_qty_int'];

    public function category()
    {
        return $this->belongsTo(StockCategory::class, 'stock_category_id');
    }

    public function logs()
    {
        return $this->hasMany(StockLog::class);
    }

    /** รวมรับเข้า (int) — ใช้ค่าที่ withSum ใส่มาเป็นหลัก */
    public function getAddedQtyIntAttribute(): int
    {
        // อ่านค่าจาก attributes ตรง ๆ (ไม่ผ่าน accessor เพื่อกัน recursion)
        $raw = $this->getRawOriginal('added_qty');
        if ($raw !== null) return (int)$raw;

        // Fallback (หลีกเลี่ยงหากเป็น list ใหญ่)
        return (int)$this->logs()->where('direction','in')->sum('qty');
    }

    /** รวมเบิกออก (int) — ใช้ค่าที่ withSum ใส่มาเป็นหลัก */
    public function getUsedQtyIntAttribute(): int
    {
        $raw = $this->getRawOriginal('used_qty');
        if ($raw !== null) return (int)$raw;

        return (int)$this->logs()->where('direction','out')->sum('qty');
    }

    /** ยอดคงคลัง = ตั้งต้น + รับเข้า */
    public function getBookQtyAttribute(): int
    {
        return (int)$this->qty_open + $this->added_qty_int;
    }

    /** คงเหลือ = (ตั้งต้น + เข้า) - ออก */
    public function getRemainAttribute(): int
    {
        return $this->book_qty - $this->used_qty_int;
    }
}
