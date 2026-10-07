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

class Jurusan extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, BelongsToUnit;

    protected $table = 'jurusans';

    protected $fillable = [
        'unit_sekolah_id',
        'kode_jurusan',
        'nama_jurusan',
        'bidang_keahlian',
        'keterangan',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'jurusan_id');
    }

    public function tarifSpps(): HasMany
    {
        return $this->hasMany(TarifSpp::class, 'jurusan_id');
    }
}
