<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'last_logout_at')) {
                $table->timestamp('last_logout_at')->nullable()->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(false)->after('last_logout_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_online'))      $table->dropColumn('is_online');
            if (Schema::hasColumn('users', 'last_logout_at')) $table->dropColumn('last_logout_at');
            if (Schema::hasColumn('users', 'last_login_at'))  $table->dropColumn('last_login_at');
        });
    }
};
