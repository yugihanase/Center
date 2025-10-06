<?php
// database/migrations/2025_09_29_000001_add_assign_fields_to_reports.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_to')->nullable()->index()->after('id');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('accepted_at')->nullable(); // ช่างกดรับงาน
            $table->timestamp('started_at')->nullable();  // เริ่มทำ
            $table->timestamp('completed_at')->nullable(); // เสร็จงาน
            $table->timestamp('due_at')->nullable();
            $table->string('priority', 20)->default('normal'); // low|normal|high|urgent
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn([
                'assigned_to','assigned_at','accepted_at','started_at',
                'completed_at','due_at','priority'
            ]);
        });
    }
};

