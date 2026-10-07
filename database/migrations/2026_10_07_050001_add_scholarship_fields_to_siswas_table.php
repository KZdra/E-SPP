<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->enum('kategori_spp', ['reguler', 'beasiswa', 'keringanan', 'yatim'])->default('reguler')->after('telepon_wali');
            $table->enum('diskon_tipe', ['persen', 'nominal'])->default('persen')->after('kategori_spp');
            $table->decimal('diskon_nilai', 12, 2)->default(0)->after('diskon_tipe');
            $table->string('catatan_keringanan')->nullable()->after('diskon_nilai');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['kategori_spp', 'diskon_tipe', 'diskon_nilai', 'catatan_keringanan']);
        });
    }
};
