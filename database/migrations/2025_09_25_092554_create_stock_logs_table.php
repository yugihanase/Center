<?php
// database/migrations/2025_09_25_000003_create_stock_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['in','out']);      // เข้า/ออก
            $table->unsignedInteger('qty');               // จำนวน
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['stock_id','direction']);
        });
    }
    public function down(): void { Schema::dropIfExists('stock_logs'); }
};
