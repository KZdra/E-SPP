<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurusans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_sekolah_id')->constrained('unit_sekolahs')->cascadeOnDelete();
            $table->string('kode_jurusan', 20); // e.g. RPL, TKJ, TKR, AKL
            $table->string('nama_jurusan', 150); // e.g. Rekayasa Perangkat Lunak
            $table->string('bidang_keahlian', 150)->nullable(); // e.g. Teknologi Informasi dan Komunikasi
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['unit_sekolah_id', 'kode_jurusan']);
            $table->index(['unit_sekolah_id', 'nama_jurusan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurusans');
    }
};
