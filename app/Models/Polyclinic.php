<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Polyclinic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'floor_room',
        'description',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Dokter yang bertugas di poliklinik ini
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(User::class, 'polyclinic_id')->where('role', 'dokter');
    }

    /**
     * Semua antrian di poliklinik ini
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'polyclinic_id');
    }

    /**
     * Antrian hari ini di poliklinik ini
     */
    public function todayQueues(): HasMany
    {
        return $this->hasMany(Queue::class, 'polyclinic_id')->whereDate('queue_date', today());
    }

    /**
     * Antrian aktif (belum selesai)
     */
    public function activeQueues(): HasMany
    {
        return $this->hasMany(Queue::class, 'polyclinic_id')
            ->whereDate('queue_date', today())
            ->where('status', '!=', 'selesai');
    }
}
