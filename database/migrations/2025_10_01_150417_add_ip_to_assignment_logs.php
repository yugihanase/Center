<?php
// database/migrations/2025_10_01_000001_add_ip_to_assignment_logs.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assignment_logs', function (Blueprint $table) {
            $table->string('ip', 45)->nullable()->after('note');
            $table->index('ip');
        });
    }

    public function down(): void
    {
        Schema::table('assignment_logs', function (Blueprint $table) {
            $table->dropIndex(['ip']);
            $table->dropColumn('ip');
        });
    }
};

