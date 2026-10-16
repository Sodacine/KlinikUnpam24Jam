<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Suster & Apoteker kini bisa diassign ke 1 ruangan/poli (sistem shifting).
     * Field polyclinic_id sudah ada di users, hanya perlu digunakan untuk semua role medis.
     * Migration ini tidak perlu mengubah schema karena kolom polyclinic_id sudah ada.
     * Namun kita perlu memastikan constraint sudah benar.
     */
    public function up(): void
    {
        // polyclinic_id sudah ada di tabel users sejak awal.
        // Migration ini sebagai marker bahwa fitur shifting diaktifkan untuk suster & apoteker.
        // Tidak ada perubahan schema yang diperlukan.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset polyclinic_id untuk suster & apoteker (optional cleanup)
        DB::table('users')
            ->whereIn('role', ['suster', 'apoteker'])
            ->update(['polyclinic_id' => null]);
    }
};
