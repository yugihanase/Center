<?php
// database/migrations/2025_09_25_000002_create_stocks_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // รายการ
            $table->foreignId('stock_category_id')  // หมวดหมู่
                  ->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('unit', 32);             // หน่วยนับ
            $table->unsignedInteger('qty_open')->default(0); // ยอดตั้งต้น
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stocks'); }
};

