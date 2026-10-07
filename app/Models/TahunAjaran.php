<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\BelongsToUnit;

class TahunAjaran extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, BelongsToUnit;

    protected $table = 'tahun_ajarans';

    protected $fillable = [
        'unit_sekolah_id',
        'tahun',
        'semester',
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

    public function tarifSpps(): HasMany
    {
        return $this->hasMany(TarifSpp::class, 'tahun_ajaran_id');
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'tahun_ajaran_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
