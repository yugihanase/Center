<?php
// database/migrations/2025_09_24_000000_create_line_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('line_users', function (Blueprint $table) {
            $table->id();

            // ผูกกับ users (อาจว่างได้ ถ้ายังไม่แม็ปกับผู้ใช้ในระบบ)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()   // อ้างอิงตาราง users
                  ->nullOnDelete();  // ลบ user แล้ว set null

            // ข้อมูลจาก LINE OA
            $table->string('line_user_id')->unique();   // Uxxxxxxxxxxxxxxxxxxxx
            $table->string('display_name')->nullable();
            $table->string('avatar')->nullable();       // pictureUrl
            $table->string('status_message')->nullable();
            $table->string('language', 8)->nullable();  // ถ้าดึงได้จาก LIFF/อื่น ๆ

            // สถานะความสัมพันธ์กับ OA
            $table->boolean('is_following')->default(true); // follow/unfollow
            $table->timestamp('last_event_at')->nullable(); // ล่าสุดที่ส่ง event มา
            $table->timestamp('last_seen_at')->nullable();  // ใช้เองตาม logic
            $table->timestamp('unfollowed_at')->nullable();

            // เผื่อ metadata อื่น ๆ/payload
            $table->json('meta')->nullable();

            $table->timestamps();

            // ช่วยค้นเร็วขึ้น
            $table->index(['is_following', 'last_event_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('line_users');
    }
};

