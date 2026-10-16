<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_PASIEN = 'pasien';

    public const ROLE_SUSTER = 'suster';

    public const ROLE_DOKTER = 'dokter';

    public const ROLE_APOTEKER = 'apoteker';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'polyclinic_id',
        'work_station',
        'phone',
        'nik',
        'birth_date',
        'gender',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    // Role check helpers
    public function isPasien(): bool
    {
        return $this->role === 'pasien';
    }

    public function isSuster(): bool
    {
        return $this->role === 'suster';
    }

    public function isDokter(): bool
    {
        return $this->role === 'dokter';
    }

    public function isApoteker(): bool
    {
        return $this->role === 'apoteker';
    }

    /**
     * Mendapatkan nama route dashboard berdasarkan role
     */
    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            'suster' => 'suster.dashboard',
            'dokter' => 'dokter.dashboard',
            'apoteker' => 'apoteker.dashboard',
            default => 'pasien.dashboard',
        };
    }

    /**
     * Label role dalam bahasa Indonesia
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'pasien' => 'Pasien',
            'suster' => 'Suster / Perawat',
            'dokter' => 'Dokter',
            'apoteker' => 'Apoteker',
            default => ucfirst($this->role),
        };
    }

    /**
     * Daftar pilihan stasiun kerja / loket tetap untuk Suster dan Apoteker
     */
    public const SUSTER_STATIONS = [
        'loket_suster_1' => 'Loket Pendaftaran',
    ];

    public const APOTEKER_STATIONS = [
        'loket_apotek_1' => 'Loket Farmasi & Apotek',
    ];

    /**
     * Mendapatkan label penempatan shift / ruangan aktif
     */
    public function getShiftLocationLabelAttribute(): ?string
    {
        if ($this->role === 'dokter') {
            return $this->polyclinic ? "{$this->polyclinic->name} ({$this->polyclinic->floor_room})" : null;
        }

        if ($this->role === 'suster') {
            return self::SUSTER_STATIONS[$this->work_station] ?? null;
        }

        if ($this->role === 'apoteker') {
            return self::APOTEKER_STATIONS[$this->work_station] ?? null;
        }

        return null;
    }

    /**
     * Poliklinik (khusus Dokter)
     */
    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class, 'polyclinic_id');
    }

    /**
     * Antrian yang dibuat oleh pasien ini
     */
    public function patientQueues(): HasMany
    {
        return $this->hasMany(Queue::class, 'patient_id');
    }

    /**
     * Riwayat medis pasien (antrian yang sudah berstatus 'selesai')
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(Queue::class, 'patient_id')
            ->where('status', 'selesai')
            ->orderByDesc('completed_at');
    }

    /**
     * Antrian/pemeriksaan yang ditangani oleh dokter ini
     */
    public function doctorQueues(): HasMany
    {
        return $this->hasMany(Queue::class, 'doctor_id');
    }
}
