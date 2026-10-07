<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarif_spps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_sekolah_id')->constrained('unit_sekolahs')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete(); // nullable jika berlaku umum 1 unit
            $table->decimal('nominal', 12, 2);
            $table->enum('kategori', ['reguler', 'beasiswa', 'khusus'])->default('reguler');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_sekolah_id', 'tahun_ajaran_id', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif_spps');
    }
};
