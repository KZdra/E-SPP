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

class Tagihan extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, BelongsToUnit;

    protected $table = 'tagihans';

    protected $fillable = [
        'unit_sekolah_id',
        'siswa_id',
        'tahun_ajaran_id',
        'bulan',
        'tahun',
        'nominal',
        'nominal_terbayar',
        'status',
        'jatuh_tempo',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'nominal' => 'decimal:2',
        'nominal_terbayar' => 'decimal:2',
        'jatuh_tempo' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function pembayaranDetails(): HasMany
    {
        return $this->hasMany(PembayaranDetail::class, 'tagihan_id');
    }

    public function getNamaBulanAttribute(): string
    {
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $bulanList[$this->bulan] ?? "Bulan {$this->bulan}";
    }

    public function getPeriodeAttribute(): string
    {
        return "{$this->nama_bulan} {$this->tahun}";
    }

    public function getSisaBayarAttribute(): float
    {
        return max(0, (float)$this->nominal - (float)$this->nominal_terbayar);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'belum_lunas');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'lunas');
    }
}
