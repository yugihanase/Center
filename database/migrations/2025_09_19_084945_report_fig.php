<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('device_address'); // ที่อยู่อุปกรณ์
            $table->string('device_list');    // รายการอุปกรณ์
            $table->text('detail');           // แจ้งรายละเอียด
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
