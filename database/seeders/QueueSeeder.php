<?php

namespace Database\Seeders;

use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pasien1 = User::where('email', 'pasien@unpam.ac.id')->first();
        $pasien2 = User::where('email', 'anisa@unpam.ac.id')->first();

        $drUmum = User::where('email', 'dokter.umum@unpam.ac.id')->first();
        $drGigi = User::where('email', 'dokter.gigi@unpam.ac.id')->first();
        $drAnak = User::where('email', 'dokter.anak@unpam.ac.id')->first();

        $poliUmum = Polyclinic::where('code', 'UMU')->first();
        $poliGigi = Polyclinic::where('code', 'GGI')->first();
        $poliAnak = Polyclinic::where('code', 'ANK')->first();

        if (! $pasien1 || ! $drUmum || ! $poliUmum) {
            return;
        }

        // 1. Riwayat Medis 1: Pasien 1 di Poli Umum (1 bulan lalu - Selesai)
        Queue::updateOrCreate(
            ['queue_code' => 'UMU-001', 'queue_date' => Carbon::now()->subDays(28)->toDateString()],
            [
                'queue_number' => 1,
                'patient_id' => $pasien1->id,
                'polyclinic_id' => $poliUmum->id,
                'doctor_id' => $drUmum->id,
                'status' => Queue::STATUS_SELESAI,
                'complaint' => 'Demam tinggi 3 hari disertai flu, batuk berdahak, dan badan pegal-pegal.',
                'disease' => 'Infeksi Saluran Pernapasan Akut (ISPA) & Faringitis Akut',
                'diagnosis' => 'Suhu 38.6°C, faring hiperemis (+), tonsil T1-T1 tenang, rhonki (-). Terindikasi ISPA viral dengan infeksi sekunder.',
                'prescription' => "1. Paracetamol 500mg - 3x1 tablet sesudah makan (bila demam)\n2. Amoxicillin 500mg - 3x1 kaplet sesudah makan (habiskan)\n3. Ambroxol 30mg - 3x1 tablet sesudah makan\n4. Vitamin C 500mg - 1x1 tablet pagi hari",
                'doctor_notes' => 'Banyak minum air hangat minimal 2 liter/hari, istirahat total 3 hari, hindari minuman dingin dan gorengan.',
                'pharmacist_notes' => 'Obat telah diserahkan dan diedukasi cara pemakaian. Antibiotik wajib dihabiskan.',
                'nurse_verified_at' => Carbon::now()->subDays(28)->setTime(8, 30),
                'doctor_started_at' => Carbon::now()->subDays(28)->setTime(8, 45),
                'doctor_finished_at' => Carbon::now()->subDays(28)->setTime(9, 10),
                'pharmacist_verified_at' => Carbon::now()->subDays(28)->setTime(9, 25),
                'completed_at' => Carbon::now()->subDays(28)->setTime(9, 35),
            ]
        );

        // 2. Riwayat Medis 2: Pasien 1 di Poli Gigi (2 minggu lalu - Selesai)
        Queue::updateOrCreate(
            ['queue_code' => 'GGI-001', 'queue_date' => Carbon::now()->subDays(14)->toDateString()],
            [
                'queue_number' => 1,
                'patient_id' => $pasien1->id,
                'polyclinic_id' => $poliGigi->id,
                'doctor_id' => $drGigi->id,
                'status' => Queue::STATUS_SELESAI,
                'complaint' => 'Gigi geraham bawah kanan berlubang dan ngilu saat minum dingin atau makan manis.',
                'disease' => 'Pulpitis Reversibel pada Gigi 46',
                'diagnosis' => 'Kavitas profunda pada gigi molar 1 kanan bawah (46), tes sondasi (+), perkusi (-). Dilakukan penumpatan sementara dan aplikasi bahan pelindung pulpa.',
                'prescription' => "1. Asam Mefenamat 500mg - 3x1 kaplet sesudah makan (bila sakit)\n2. Obat Kumur Klorheksidin 0.2% - 2x sehari setelah sikat gigi",
                'doctor_notes' => 'Kontrol kembali 1 minggu untuk evaluasi dan penumpatan resin komposit permanen.',
                'pharmacist_notes' => 'Obat telah diserahkan. Pasien diedukasi cara penggunaan obat kumur.',
                'nurse_verified_at' => Carbon::now()->subDays(14)->setTime(10, 15),
                'doctor_started_at' => Carbon::now()->subDays(14)->setTime(10, 30),
                'doctor_finished_at' => Carbon::now()->subDays(14)->setTime(11, 00),
                'pharmacist_verified_at' => Carbon::now()->subDays(14)->setTime(11, 15),
                'completed_at' => Carbon::now()->subDays(14)->setTime(11, 20),
            ]
        );

        // 3. Antrian Hari Ini 1: Pasien 2 di Poli Umum (Status: Menunggu Suster)
        Queue::updateOrCreate(
            ['queue_code' => 'UMU-001', 'queue_date' => Carbon::today()->toDateString()],
            [
                'queue_number' => 1,
                'patient_id' => $pasien2->id,
                'polyclinic_id' => $poliUmum->id,
                'status' => Queue::STATUS_MENUNGGU_SUSTER,
                'complaint' => 'Pusing berputar sejak pagi, mual dan nafsu makan berkurang.',
                'created_at' => Carbon::now()->subMinutes(25),
            ]
        );

        // 4. Antrian Hari Ini 2: Pasien 1 di Poli Umum (Status: Menunggu Dokter)
        Queue::updateOrCreate(
            ['queue_code' => 'UMU-002', 'queue_date' => Carbon::today()->toDateString()],
            [
                'queue_number' => 2,
                'patient_id' => $pasien1->id,
                'polyclinic_id' => $poliUmum->id,
                'doctor_id' => $drUmum->id,
                'status' => Queue::STATUS_MENUNGGU_DOKTER,
                'complaint' => 'Asam lambung kambuh, nyeri ulu hati dan perut kembung.',
                'nurse_verified_at' => Carbon::now()->subMinutes(10),
                'created_at' => Carbon::now()->subMinutes(40),
            ]
        );
    }
}
