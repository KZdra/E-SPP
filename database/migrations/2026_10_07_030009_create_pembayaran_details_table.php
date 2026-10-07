<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayarans')->cascadeOnDelete();
            $table->foreignId('tagihan_id')->constrained('tagihans')->cascadeOnDelete();
            $table->decimal('nominal_dibayar', 12, 2);
            $table->timestamps();

            $table->index(['pembayaran_id', 'tagihan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_details');
    }
};
