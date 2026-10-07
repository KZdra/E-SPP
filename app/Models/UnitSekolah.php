<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class UnitSekolah extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'unit_sekolahs';

    protected $fillable = [
        'kode_unit',
        'nama_unit',
        'jenjang',
        'alamat',
        'telepon',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'unit_sekolah_id');
    }

    public function tahunAjarans(): HasMany
    {
        return $this->hasMany(TahunAjaran::class, 'unit_sekolah_id');
    }

    public function activeTahunAjaran()
    {
        return $this->hasOne(TahunAjaran::class, 'unit_sekolah_id')->where('is_active', true);
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'unit_sekolah_id');
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'unit_sekolah_id');
    }

    public function tarifSpps(): HasMany
    {
        return $this->hasMany(TarifSpp::class, 'unit_sekolah_id');
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'unit_sekolah_id');
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'unit_sekolah_id');
    }
}
