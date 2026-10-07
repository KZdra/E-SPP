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

class Kelas extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, BelongsToUnit;

    protected $table = 'kelas';

    protected $fillable = [
        'unit_sekolah_id',
        'nama_kelas',
        'tingkat',
        'wali_kelas_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    public function activeSiswas(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas_id')->where('status', 'aktif');
    }

    public function tarifSpps(): HasMany
    {
        return $this->hasMany(TarifSpp::class, 'kelas_id');
    }
}
