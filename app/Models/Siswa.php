<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\BelongsToUnit;

class Siswa extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, BelongsToUnit;

    protected $table = 'siswas';

    protected $fillable = [
        'unit_sekolah_id',
        'kelas_id',
        'nis',
        'nisn',
        'nama',
        'jenis_kelamin',
        'nama_wali',
        'telepon_wali',
        'alamat',
        'status',
        'kategori_spp',
        'diskon_tipe',
        'diskon_nilai',
        'catatan_keringanan',
    ];

    protected $casts = [
        'diskon_nilai' => 'float',
    ];

    /**
     * Hitung nominal SPP akhir setelah memperhitungkan beasiswa/keringanan siswa
     */
    public function hitungNominalSetelahDiskon(float $nominalDasar): float
    {
        if ($this->kategori_spp === 'yatim') {
            return 0; // Gratis 100% untuk anak yatim piatu
        }

        if ($this->diskon_nilai <= 0) {
            return $nominalDasar;
        }

        if ($this->diskon_tipe === 'persen') {
            $potongan = $nominalDasar * ($this->diskon_nilai / 100);
            return max(0, $nominalDasar - $potongan);
        }

        if ($this->diskon_tipe === 'nominal') {
            return max(0, $nominalDasar - $this->diskon_nilai);
        }

        return $nominalDasar;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'siswa_id');
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'siswa_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }
}
