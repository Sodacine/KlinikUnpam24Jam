<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambah kolom untuk fitur Skip Pasien Tidak Hadir & Auto-Expire 24 Jam
     */
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            // Timestamp kapan antrian di-skip oleh suster
            $table->timestamp('skipped_at')->nullable()->after('completed_at');
            // Berapa kali pasien di-skip (untuk tracking)
            $table->unsignedTinyInteger('skip_count')->default(0)->after('skipped_at');
            // Timestamp otomatis kadaluarsa (24 jam dari created_at)
            $table->timestamp('expired_at')->nullable()->after('skip_count');
        });

        // Modifikasi enum status untuk menambah nilai 'dilewati' (khusus MySQL/MariaDB)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE queues MODIFY COLUMN status ENUM(
                'menunggu_suster',
                'menunggu_dokter',
                'pemeriksaan_dokter',
                'menunggu_apotek',
                'siap_diambil',
                'selesai',
                'dilewati'
            ) NOT NULL DEFAULT 'menunggu_suster'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan enum tanpa 'dilewati'
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE queues MODIFY COLUMN status ENUM(
                'menunggu_suster',
                'menunggu_dokter',
                'pemeriksaan_dokter',
                'menunggu_apotek',
                'siap_diambil',
                'selesai'
            ) NOT NULL DEFAULT 'menunggu_suster'");
        }

        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn(['skipped_at', 'skip_count', 'expired_at']);
        });
    }
};
