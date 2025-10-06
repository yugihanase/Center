<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('report_id')->constrained()->cascadeOnDelete(); // งานแจ้งซ่อม
            $t->foreignId('technician_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();

            $t->string('status')->default('มอบหมาย'); // มอบหมาย|กำลังดำเนินการ|เสร็จสิ้น|ยกเลิก
            $t->unsignedTinyInteger('priority')->default(3); // 1=สูงสุด … 5=ต่ำ
            $t->timestamp('eta')->nullable();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->text('note')->nullable();

            $t->timestamps();

            $t->index(['report_id']);
            $t->index(['technician_id']);
            $t->index(['status']);
        });

        Schema::create('assignment_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action');        // assign|reassign|status_change|note
            $t->string('from_status')->nullable();
            $t->string('to_status')->nullable();
            $t->text('note')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('assignment_logs');
        Schema::dropIfExists('assignments');
    }
};

