<?php
// database/migrations/2025_10_03_000000_create_technicians_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 50)->unique();     // รหัสพนักงาน
            $table->string('name');                              // ชื่อ-สกุล
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->enum('role', ['technician','lead'])->default('technician'); // ช่าง/หัวหน้า
            $table->string('department')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // ถ้ามีบัญชีในตาราง users
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['role','is_active']);
            $table->index(['name']);
        });

        // ทางเลือก: ถ้าต้องการผูก users ด้วยรหัสพนักงาน
        if (!Schema::hasColumn('users', 'employee_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('employee_code', 50)->nullable()->unique()->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'employee_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['employee_code']);
                $table->dropColumn('employee_code');
            });
        }
        Schema::dropIfExists('technicians');
    }
};
