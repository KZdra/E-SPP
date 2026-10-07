<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('jurusan_id')->nullable()->after('unit_sekolah_id')->constrained('jurusans')->nullOnDelete();
            $table->index(['unit_sekolah_id', 'jurusan_id']);
        });

        Schema::table('tarif_spps', function (Blueprint $table) {
            $table->foreignId('jurusan_id')->nullable()->after('tahun_ajaran_id')->constrained('jurusans')->nullOnDelete();
            $table->index(['unit_sekolah_id', 'tahun_ajaran_id', 'jurusan_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tarif_spps', function (Blueprint $table) {
            $table->dropForeign(['jurusan_id']);
            $table->dropColumn('jurusan_id');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['jurusan_id']);
            $table->dropColumn('jurusan_id');
        });
    }
};
