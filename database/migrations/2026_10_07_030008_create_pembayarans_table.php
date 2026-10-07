<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_sekolah_id')->constrained('unit_sekolahs')->cascadeOnDelete();
            $table->string('kode_transaksi', 50)->unique();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Petugas TU / Bendahara
            $table->date('tgl_bayar');
            $table->decimal('total_bayar', 12, 2);
            $table->enum('metode_bayar', ['tunai', 'transfer'])->default('tunai');
            $table->string('bank_tujuan', 50)->nullable();
            $table->string('nomor_referensi', 100)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for fast query & reporting
            $table->index(['unit_sekolah_id', 'tgl_bayar']);
            $table->index(['siswa_id', 'tgl_bayar']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
