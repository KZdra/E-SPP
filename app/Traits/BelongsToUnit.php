<?php

namespace App\Traits;

use App\Models\UnitSekolah;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToUnit
{
    /**
     * Relationship to UnitSekolah
     */
    public function unitSekolah(): BelongsTo
    {
        return $this->belongsTo(UnitSekolah::class, 'unit_sekolah_id');
    }

    /**
     * Scope query to a specific unit or current user's unit.
     */
    public function scopeForUnit(Builder $query, $unitId = null): Builder
    {
        if ($unitId !== null && $unitId !== '') {
            return $query->where($this->getTable() . '.unit_sekolah_id', $unitId);
        }

        // Auto-filter for non-superadmin users who belong to a specific unit
        $user = auth()->user();
        if ($user && !empty($user->unit_sekolah_id) && !$user->hasRole('Admin Yayasan')) {
            return $query->where($this->getTable() . '.unit_sekolah_id', $user->unit_sekolah_id);
        }

        return $query;
    }
}
