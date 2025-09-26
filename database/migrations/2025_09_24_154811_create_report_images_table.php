<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_report_images_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('report_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('path');           // storage path
            $table->string('original_name');  // ชื่อไฟล์เดิม
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size_kb')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('report_images'); }
};
