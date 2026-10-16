<?php

namespace Database\Seeders;

use App\Models\Polyclinic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $polis = Polyclinic::all()->keyBy('code');

        // 1. Akun Suster (Nurse)
        User::updateOrCreate(
            ['email' => 'suster@unpam.ac.id'],
            [
                'name' => 'Siti Rahmawati, S.Kep',
                'password' => Hash::make('password'),
                'role' => 'suster',
                'phone' => '081234567801',
                'nik' => '3674012304900001',
                'gender' => 'P',
                'address' => 'Jl. Surya Kencana No. 1, Pamulang, Tangerang Selatan',
            ]
        );

        // 2. Akun Dokter Spesialis & Umum
        // Dokter Umum
        User::updateOrCreate(
            ['email' => 'dokter.umum@unpam.ac.id'],
            [
                'name' => 'dr. Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'dokter',
                'polyclinic_id' => $polis['UMU']?->id,
                'phone' => '081234567802',
                'nik' => '3674012304850002',
                'gender' => 'L',
                'address' => 'Jl. Pajajaran No. 15, Pamulang, Tangerang Selatan',
            ]
        );

        // Dokter Poli Anak
        User::updateOrCreate(
            ['email' => 'dokter.anak@unpam.ac.id'],
            [
                'name' => 'dr. Sarah Amanda, Sp.A',
                'password' => Hash::make('password'),
                'role' => 'dokter',
                'polyclinic_id' => $polis['ANK']?->id,
                'phone' => '081234567803',
                'nik' => '3674012304880003',
                'gender' => 'P',
                'address' => 'Komplek Unpam Residence No. B-4, Tangerang Selatan',
            ]
        );

        // Dokter Poli Kandungan
        User::updateOrCreate(
            ['email' => 'dokter.kandungan@unpam.ac.id'],
            [
                'name' => 'dr. Maya Indah, Sp.OG',
                'password' => Hash::make('password'),
                'role' => 'dokter',
                'polyclinic_id' => $polis['KND']?->id,
                'phone' => '081234567804',
                'nik' => '3674012304860004',
                'gender' => 'P',
                'address' => 'Jl. Raya Puspiptek No. 8, Serpong',
            ]
        );

        // Dokter Poli Gigi
        User::updateOrCreate(
            ['email' => 'dokter.gigi@unpam.ac.id'],
            [
                'name' => 'drg. Reza Pratama',
                'password' => Hash::make('password'),
                'role' => 'dokter',
                'polyclinic_id' => $polis['GGI']?->id,
                'phone' => '081234567805',
                'nik' => '3674012304910005',
                'gender' => 'L',
                'address' => 'Jl. Siliwangi No. 20, Pondok Benda, Pamulang',
            ]
        );

        // 3. Akun Apoteker (Pharmacist)
        User::updateOrCreate(
            ['email' => 'apoteker@unpam.ac.id'],
            [
                'name' => 'Apt. Dewi Lestari, S.Farm',
                'password' => Hash::make('password'),
                'role' => 'apoteker',
                'phone' => '081234567806',
                'nik' => '3674012304920006',
                'gender' => 'P',
                'address' => 'Jl. Benda Barat 11 No. 5, Pamulang 2',
            ]
        );

        // 4. Akun Pasien Default (Untuk pengujian)
        User::updateOrCreate(
            ['email' => 'pasien@unpam.ac.id'],
            [
                'name' => 'Rangga Permana',
                'password' => Hash::make('password'),
                'role' => 'pasien',
                'phone' => '081234567890',
                'nik' => '3674012304990001',
                'birth_date' => '1999-04-23',
                'gender' => 'L',
                'address' => 'Jl. Surya Kencana No. 22, Pamulang Barat, Tangerang Selatan',
            ]
        );

        User::updateOrCreate(
            ['email' => 'anisa@unpam.ac.id'],
            [
                'name' => 'Anisa Putri Maharani',
                'password' => Hash::make('password'),
                'role' => 'pasien',
                'phone' => '081298765432',
                'nik' => '3674012305010002',
                'birth_date' => '2001-05-15',
                'gender' => 'P',
                'address' => 'Jl. Villa Pamulang Blok CC No. 12',
            ]
        );
    }
}
