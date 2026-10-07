<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_sekolah_id')->constrained('unit_sekolahs')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->string('nis', 30);
            $table->string('nisn', 30)->nullable();
            $table->string('nama', 150);
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->string('nama_wali', 150)->nullable();
            $table->string('telepon_wali', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->enum('status', ['aktif', 'lulus', 'pindah'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['unit_sekolah_id', 'nis']);
            $table->index(['unit_sekolah_id', 'kelas_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
