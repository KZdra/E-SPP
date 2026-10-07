<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_sekolah_id')->constrained('unit_sekolahs')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->tinyInteger('bulan'); // 1 s/d 12 (1=Januari, 7=Juli, dll)
            $table->smallInteger('tahun'); // e.g. 2026
            $table->decimal('nominal', 12, 2);
            $table->decimal('nominal_terbayar', 12, 2)->default(0);
            $table->enum('status', ['belum_lunas', 'lunas'])->default('belum_lunas');
            $table->date('jatuh_tempo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for performance & scalability
            $table->index(['siswa_id', 'bulan', 'tahun', 'status']);
            $table->index(['unit_sekolah_id', 'tahun_ajaran_id', 'status']);
            $table->index(['unit_sekolah_id', 'status', 'tahun', 'bulan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
