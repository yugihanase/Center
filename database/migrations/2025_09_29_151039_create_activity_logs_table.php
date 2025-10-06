<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // กันพลาดถ้ามีใครเผลอสร้างไว้แล้ว
        if (Schema::hasTable('activity_logs')) return;

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // ใครเป็นคนทำ (nullable เผื่อระบบ/cron)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // เหตุการณ์และสิ่งที่เกี่ยวข้อง
            $table->string('event', 64)->index();
            $table->string('subject_type')->nullable();              // morph type
            $table->unsignedBigInteger('subject_id')->nullable();    // morph id

            // คำอธิบาย และรายละเอียดเสริม (JSON)
            $table->string('description', 512)->nullable();
            $table->json('properties')->nullable();

            // บริบทคำขอ
            $table->string('ip', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('url', 2048)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();

            // เวลาเกิดเหตุ (แยกจาก created_at เพื่อบันทึกเวลาจริงของ event)
            $table->timestamp('performed_at')->useCurrent();

            $table->timestamps();

            // ดัชนีช่วยค้น
            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'event']);
            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
