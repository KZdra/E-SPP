<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\BelongsToUnit;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity, BelongsToUnit;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'unit_sekolah_id',
        'username',
        'name',
        'email',
        'phone',
        'status_aktif',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status_aktif' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['username', 'name', 'email', 'phone', 'unit_sekolah_id', 'status_aktif'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'user_id');
    }

    public function kelasWali(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    // Role helper methods
    public function isYayasan(): bool
    {
        return $this->hasRole('Admin Yayasan');
    }

    public function isKepsek(): bool
    {
        return $this->hasRole('Kepala Sekolah');
    }

    public function isTU(): bool
    {
        return $this->hasRole('Petugas TU');
    }

    public function isSiswa(): bool
    {
        return $this->hasRole('Siswa');
    }

    // AdminLTE user menu integration
    public function adminlte_image(): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0D8ABC&color=fff';
    }

    public function adminlte_desc(): string
    {
        $role = $this->roles->first()?->name ?? 'User';
        $unit = $this->unitSekolah?->nama_unit ?? 'Yayasan (Pusat)';
        return "{$role} - {$unit}";
    }

    public function adminlte_profile_url(): string
    {
        return route('home');
    }
}
