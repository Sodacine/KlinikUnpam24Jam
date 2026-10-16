<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    use HasFactory;

    // 7 Status Alur Antrian (termasuk Dilewati)
    public const STATUS_MENUNGGU_SUSTER    = 'menunggu_suster';
    public const STATUS_MENUNGGU_DOKTER    = 'menunggu_dokter';
    public const STATUS_PEMERIKSAAN_DOKTER = 'pemeriksaan_dokter';
    public const STATUS_MENUNGGU_APOTEK    = 'menunggu_apotek';
    public const STATUS_SIAP_DIAMBIL       = 'siap_diambil';
    public const STATUS_SELESAI            = 'selesai';
    public const STATUS_DILEWATI           = 'dilewati'; // Pasien tidak hadir, bisa dipanggil ulang

    protected $fillable = [
        'queue_code',
        'queue_number',
        'queue_date',
        'patient_id',
        'polyclinic_id',
        'doctor_id',
        'status',
        'complaint',
        'disease',
        'diagnosis',
        'prescription',
        'doctor_notes',
        'pharmacist_notes',
        'needs_prescription_revision',
        'prescription_replacement_reason',
        'prescription_replacement_requested_at',
        'nurse_verified_at',
        'doctor_started_at',
        'doctor_finished_at',
        'pharmacist_verified_at',
        'completed_at',
        'skipped_at',
        'skip_count',
        'expired_at',
    ];

    protected $casts = [
        'queue_date'                            => 'date',
        'needs_prescription_revision'           => 'boolean',
        'prescription_replacement_requested_at' => 'datetime',
        'nurse_verified_at'                     => 'datetime',
        'doctor_started_at'                     => 'datetime',
        'doctor_finished_at'                    => 'datetime',
        'pharmacist_verified_at'                => 'datetime',
        'completed_at'                          => 'datetime',
        'skipped_at'                            => 'datetime',
        'expired_at'                            => 'datetime',
    ];

    /**
     * Relasi ke Pasien
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Relasi ke Poliklinik
     */
    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class, 'polyclinic_id');
    }

    /**
     * Relasi ke Dokter yang memeriksa
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Label Status yang ramah pengguna
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_SUSTER    => 'Menunggu Panggilan Suster',
            self::STATUS_MENUNGGU_DOKTER    => 'Menuju Ruangan Dokter',
            self::STATUS_PEMERIKSAAN_DOKTER => 'Pemeriksaan Dokter',
            self::STATUS_MENUNGGU_APOTEK    => 'Resep Sedang Diproses Apotek',
            self::STATUS_SIAP_DIAMBIL       => 'Obat Siap di Loket Apotek',
            self::STATUS_SELESAI            => 'Pelayanan Selesai',
            self::STATUS_DILEWATI           => 'Dilewati (Tidak Hadir)',
            default                         => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Nomor tahapan (1 - 6)
     */
    public function getStatusStepAttribute(): int
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_SUSTER    => 1,
            self::STATUS_MENUNGGU_DOKTER    => 2,
            self::STATUS_PEMERIKSAAN_DOKTER => 3,
            self::STATUS_MENUNGGU_APOTEK    => 4,
            self::STATUS_SIAP_DIAMBIL       => 5,
            self::STATUS_SELESAI            => 6,
            self::STATUS_DILEWATI           => 0,
            default                         => 1,
        };
    }

    /**
     * Badge CSS Class untuk Tailwind
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_SUSTER    => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
            self::STATUS_MENUNGGU_DOKTER    => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800',
            self::STATUS_PEMERIKSAAN_DOKTER => 'bg-indigo-100 text-indigo-800 border-indigo-300 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800',
            self::STATUS_MENUNGGU_APOTEK    => 'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800',
            self::STATUS_SIAP_DIAMBIL       => 'bg-teal-100 text-teal-800 border-teal-300 dark:bg-teal-950/50 dark:text-teal-300 dark:border-teal-800',
            self::STATUS_SELESAI            => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
            self::STATUS_DILEWATI           => 'bg-rose-100 text-rose-800 border-rose-300',
            default                         => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    /**
     * Instruksi langsung untuk pasien di dashboard
     */
    public function getInstructionAttribute(): string
    {
        $poli = $this->polyclinic?->name ?? 'Poliklinik';
        $ruang = $this->polyclinic?->floor_room ?? 'Ruang Dokter';
        $dokter = $this->doctor?->name ?? 'Dokter Pemeriksa';

        return match ($this->status) {
            self::STATUS_MENUNGGU_SUSTER =>
                "Nomor antrian Anda telah terbit. Mohon menunggu di Ruang Tunggu Utama untuk dipanggil dan diverifikasi oleh Suster jaga.",

            self::STATUS_MENUNGGU_DOKTER =>
                "Suster telah memverifikasi antrian Anda! Silakan langsung menuju ke {$poli} ({$ruang}) untuk menunggu dipanggil oleh {$dokter}.",

            self::STATUS_PEMERIKSAAN_DOKTER =>
                "Saat ini Anda sedang dalam sesi konsultasi & pemeriksaan bersama {$dokter} di {$ruang}.",

            self::STATUS_MENUNGGU_APOTEK =>
                $this->needs_prescription_revision
                    ? "Apoteker sedang berkoordinasi dengan Dokter untuk penyesuaian resep obat Anda. Mohon ditunggu sebentar."
                    : "Pemeriksaan selesai. Dokter telah meneruskan resep obat Anda ke Apotek. Mohon menunggu Apoteker menyiapkan obat Anda.",

            self::STATUS_SIAP_DIAMBIL =>
                "Obat Anda telah siap! Silakan langsung menuju ke LOKET APOTEK untuk mengambil obat dan mendengarkan instruksi pemakaian.",

            self::STATUS_SELESAI =>
                "Obat telah diambil dan seluruh rangkaian pelayanan selesai. Catatan diagnosa & resep telah resmi tersimpan dalam Riwayat Medis Anda. Semoga lekas sembuh!",

            self::STATUS_DILEWATI =>
                "Antrian Anda sementara dilewati karena Anda tidak hadir saat dipanggil. Silakan hubungi Suster untuk dipanggil ulang. Antrian akan otomatis hangus dalam 24 jam.",

            default => "Silakan perhatikan petunjuk staf klinik.",
        };
    }

    // Scopes
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('queue_date', today());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_SELESAI, self::STATUS_DILEWATI]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    public function scopeSkipped(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DILEWATI);
    }

    /**
     * Cek apakah antrian sudah expire (> 24 jam dari created_at)
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expired_at && now()->isAfter($this->expired_at);
    }
}
