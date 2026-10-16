<?php

namespace Database\Seeders;

use App\Models\Polyclinic;
use Illuminate\Database\Seeder;

class PolyclinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $polyclinics = [
            [
                'name' => 'Poli Anak',
                'code' => 'ANK',
                'floor_room' => 'Gedung Utama - Lt. 1 (Ruang 101)',
                'description' => 'Pelayanan kesehatan komprehensif untuk bayi, balita, dan anak-anak dengan dokter spesialis anak berpengalaman.',
                'icon' => 'face-smile',
                'is_active' => true,
            ],
            [
                'name' => 'Poli Umum',
                'code' => 'UMU',
                'floor_room' => 'Gedung Utama - Lt. 1 (Ruang 102)',
                'description' => 'Pelayanan medis umum, pemeriksaan kesehatan dasar, serta penanganan keluhan primer 24 jam.',
                'icon' => 'heart-pulse',
                'is_active' => true,
            ],
            [
                'name' => 'Poli Kandungan',
                'code' => 'KND',
                'floor_room' => 'Gedung Utama - Lt. 2 (Ruang 201)',
                'description' => 'Pelayanan kesehatan ibu hamil, USG kandungan, fertilitas, dan pemeriksaan kesehatan reproduksi.',
                'icon' => 'user-group',
                'is_active' => true,
            ],
            [
                'name' => 'Poli Gigi',
                'code' => 'GGI',
                'floor_room' => 'Gedung Utama - Lt. 2 (Ruang 202)',
                'description' => 'Pelayanan kesehatan gigi dan mulut, penambalan, pencabutan, pembersihan karang gigi (scaling), dan konsultasi ortodonti.',
                'icon' => 'sparkles',
                'is_active' => true,
            ],
        ];

        foreach ($polyclinics as $poli) {
            Polyclinic::updateOrCreate(
                ['code' => $poli['code']],
                $poli
            );
        }
    }
}
