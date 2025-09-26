<?php
// app/Models/StockCategory.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model {
    protected $fillable = ['name'];
    public function stocks() { return $this->hasMany(Stock::class); }
}
