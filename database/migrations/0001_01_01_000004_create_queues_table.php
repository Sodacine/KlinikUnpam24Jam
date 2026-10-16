<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('queue_code', 20); // e.g. UMU-001, ANK-001
            $table->unsignedInteger('queue_number'); // 1, 2, 3...
            $table->date('queue_date'); // tanggal antrian
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('polyclinic_id')->constrained('polyclinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();

            // Tahapan State Machine Antrian
            $table->enum('status', [
                'menunggu_suster',
                'menunggu_dokter',
                'pemeriksaan_dokter',
                'menunggu_apotek',
                'siap_diambil',
                'selesai',
                'dilewati',
            ])->default('menunggu_suster');

            // Data Medis (Diisi Pasien & Dokter)
            $table->text('complaint')->nullable(); // Keluhan pasien saat mendaftar
            $table->text('disease')->nullable(); // Penyakit (diisi Dokter)
            $table->text('diagnosis')->nullable(); // Diagnosa (diisi Dokter)
            $table->text('prescription')->nullable(); // Resep Obat teks/list (diisi Dokter)
            $table->text('doctor_notes')->nullable(); // Catatan tambahan Dokter
            $table->text('pharmacist_notes')->nullable(); // Catatan Apoteker saat verifikasi obat

            // Timestamps audit tiap perpindahan state
            $table->timestamp('nurse_verified_at')->nullable();
            $table->timestamp('doctor_started_at')->nullable();
            $table->timestamp('doctor_finished_at')->nullable();
            $table->timestamp('pharmacist_verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Index untuk kecepatan query antrian harian & riwayat pasien
            $table->index(['queue_date', 'status']);
            $table->index(['patient_id', 'status']);
            $table->index(['polyclinic_id', 'queue_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
