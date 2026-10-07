<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_sekolah_id')->nullable()->after('id')->constrained('unit_sekolahs')->nullOnDelete();
            $table->string('phone', 30)->nullable()->after('email');
            $table->boolean('status_aktif')->default(true)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_sekolah_id']);
            $table->dropColumn(['unit_sekolah_id', 'phone', 'status_aktif']);
        });
    }
};
