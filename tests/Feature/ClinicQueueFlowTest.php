<?php

namespace Tests\Feature;

use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicQueueFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test: Login satu pintu mendeteksi role secara otomatis dan redirect ke dashboard yang sesuai
     */
    public function test_single_door_login_redirects_to_correct_dashboard_per_role(): void
    {
        // 1. Suster
        $response = $this->post('/login', [
            'email' => 'suster@unpam.ac.id',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('suster.dashboard'));
        $this->post('/logout');

        // 2. Dokter Umum
        $response = $this->post('/login', [
            'email' => 'dokter.umum@unpam.ac.id',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('dokter.dashboard'));
        $this->post('/logout');

        // 3. Apoteker
        $response = $this->post('/login', [
            'email' => 'apoteker@unpam.ac.id',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('apoteker.dashboard'));
        $this->post('/logout');

        // 4. Pasien
        $response = $this->post('/login', [
            'email' => 'pasien@unpam.ac.id',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('pasien.dashboard'));
        $this->post('/logout');
    }

    /**
     * Test: Alur Lengkap State Machine Antrian 6 Tahap
     */
    public function test_complete_queue_state_machine_cycle(): void
    {
        // Ambil data user
        $pasien = User::where('email', 'anisa@unpam.ac.id')->first();
        $suster = User::where('email', 'suster@unpam.ac.id')->first();
        $dokter = User::where('email', 'dokter.umum@unpam.ac.id')->first();
        $apoteker = User::where('email', 'apoteker@unpam.ac.id')->first();
        $poli = Polyclinic::where('code', 'UMU')->first();

        // Hapus antrian aktif untuk anisa agar bersih
        Queue::where('patient_id', $pasien->id)->delete();

        // TAHAP 1: Pasien Mengambil Nomor Antrian (menunggu_suster)
        $this->actingAs($pasien);
        $response = $this->post(route('pasien.antrian.store'), [
            'polyclinic_id' => $poli->id,
            'complaint' => 'Demam dan sakit tenggorokan.',
        ]);
        $response->assertRedirect(route('pasien.dashboard'));

        $queue = Queue::where('patient_id', $pasien->id)
            ->whereDate('queue_date', Carbon::today())
            ->where('status', Queue::STATUS_MENUNGGU_SUSTER)
            ->first();
        $this->assertNotNull($queue);
        $this->assertEquals(Queue::STATUS_MENUNGGU_SUSTER, $queue->status);

        // TAHAP 2: Suster Memverifikasi Antrian (menunggu_suster -> menunggu_dokter)
        $this->actingAs($suster);
        $response = $this->post(route('suster.antrian.verifikasi', $queue), [
            'doctor_id' => $dokter->id,
        ]);
        $response->assertSessionHas('success');

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_MENUNGGU_DOKTER, $queue->status);
        $this->assertEquals($dokter->id, $queue->doctor_id);
        $this->assertNotNull($queue->nurse_verified_at);

        // TAHAP 3: Dokter Memanggil Masuk Ruangan (menunggu_dokter -> pemeriksaan_dokter)
        $this->actingAs($dokter);
        $response = $this->post(route('dokter.antrian.masuk', $queue));
        $response->assertRedirect(route('dokter.periksa', $queue));

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_PEMERIKSAAN_DOKTER, $queue->status);
        $this->assertNotNull($queue->doctor_started_at);

        // TAHAP 4: Dokter Input Diagnosa & Resep (pemeriksaan_dokter -> menunggu_apotek)
        $response = $this->post(route('dokter.antrian.selesai-periksa', $queue), [
            'disease' => 'Faringitis Akut',
            'diagnosis' => 'Faring hiperemis (+), suhu 38.0C',
            'prescription' => "1. Paracetamol 500mg 3x1\n2. Amoxicillin 500mg 3x1",
            'doctor_notes' => 'Banyak minum air hangat.',
        ]);
        $response->assertRedirect(route('dokter.dashboard'));

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_MENUNGGU_APOTEK, $queue->status);
        $this->assertEquals('Faringitis Akut', $queue->disease);
        $this->assertNotNull($queue->doctor_finished_at);

        // TAHAP 5: Apoteker Memverifikasi Resep (menunggu_apotek -> siap_diambil)
        $this->actingAs($apoteker);
        $response = $this->post(route('apoteker.antrian.verifikasi-resep', $queue), [
            'pharmacist_notes' => 'Obat siap diserahkan.',
        ]);
        $response->assertSessionHas('success');

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_SIAP_DIAMBIL, $queue->status);
        $this->assertNotNull($queue->pharmacist_verified_at);

        // TAHAP 6: Apoteker Menyerahkan Obat (siap_diambil -> selesai)
        $response = $this->post(route('apoteker.antrian.serahkan-obat', $queue));
        $response->assertSessionHas('success');

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_SELESAI, $queue->status);
        $this->assertNotNull($queue->completed_at);

        // Validasi: Data resmi menjadi Riwayat Medis Pasien (TANPA resep obat)
        $this->actingAs($pasien);
        $response = $this->get(route('pasien.riwayat-medis'));
        $response->assertStatus(200);
        $response->assertSee('Faringitis Akut');
        $response->assertSee($queue->queue_code);
        $response->assertDontSee('Paracetamol 500mg'); // Pasien TIDAK dapat melihat resep obat

        // Detail Riwayat Medis juga tidak menampilkan resep obat
        $detailResponse = $this->get(route('pasien.riwayat-medis.show', $queue));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Faringitis Akut');
        $detailResponse->assertDontSee('Paracetamol 500mg'); // Pasien TIDAK dapat melihat resep obat
    }

    /**
     * Test: Suster dapat menambahkan staf medis baru (Suster, Dokter, Apoteker)
     */
    public function test_suster_can_create_new_medical_staff(): void
    {
        $suster = User::where('email', 'suster@unpam.ac.id')->first();
        $poli = Polyclinic::where('code', 'ANK')->first();

        $this->actingAs($suster);

        $response = $this->post(route('suster.staff.store'), [
            'name' => 'dr. Baru Ditambahkan, Sp.A',
            'email' => 'dokter.baru@unpam.ac.id',
            'password' => 'password123',
            'role' => 'dokter',
            'polyclinic_id' => $poli->id,
            'phone' => '081122334455',
            'gender' => 'L',
        ]);

        $response->assertRedirect(route('suster.staff'));
        $this->assertDatabaseHas('users', [
            'email' => 'dokter.baru@unpam.ac.id',
            'role' => 'dokter',
        ]);
    }

    /**
     * Test: Suster dapat melihat arsip riwayat medis semua pasien dan memfilter secara spesifik & umum
     */
    public function test_suster_can_view_and_filter_all_medical_records(): void
    {
        $suster = User::where('email', 'suster@unpam.ac.id')->first();
        $this->actingAs($suster);

        // 1. Akses halaman arsip riwayat medis
        $response = $this->get(route('suster.riwayat-medis'));
        $response->assertStatus(200);
        $response->assertSee('Riwayat Medis Seluruh Pasien');

        // 2. Pencarian umum (kata kunci)
        $searchResponse = $this->get(route('suster.riwayat-medis', ['search' => 'Rangga']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Rangga Permana');

        // 3. Filter spesifik nama pasien & poli
        $poli = Polyclinic::where('code', 'UMU')->first();
        $filterResponse = $this->get(route('suster.riwayat-medis', [
            'patient_name' => 'Rangga',
            'polyclinic_id' => $poli->id,
        ]));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Poli Umum');

        // 4. Detail riwayat medis untuk suster
        $sampleCompleted = Queue::where('status', Queue::STATUS_SELESAI)->first();
        $detailResponse = $this->get(route('suster.riwayat-medis.show', $sampleCompleted));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($sampleCompleted->queue_code);
        $detailResponse->assertSee($sampleCompleted->patient->name);
    }

    /**
     * Test: Apoteker dapat mengajukan ganti obat ke Dokter, dan Dokter dapat meresepkan ulang
     */
    public function test_apoteker_can_request_prescription_replacement_and_doctor_can_resubmit(): void
    {
        $pasien = User::where('email', 'anisa@unpam.ac.id')->first();
        $dokter = User::where('email', 'dokter.umum@unpam.ac.id')->first();
        $apoteker = User::where('email', 'apoteker@unpam.ac.id')->first();
        $poli = Polyclinic::where('code', 'UMU')->first();

        // Buat antrian pada status menunggu_apotek dengan resep multiline
        $queue = Queue::create([
            'queue_code' => 'UMU-TEST',
            'queue_number' => 99,
            'queue_date' => Carbon::today(),
            'patient_id' => $pasien->id,
            'polyclinic_id' => $poli->id,
            'doctor_id' => $dokter->id,
            'status' => Queue::STATUS_MENUNGGU_APOTEK,
            'complaint' => 'Radang amandel',
            'disease' => 'Tonsilitis',
            'diagnosis' => 'Pembengkakan tonsil T2/T2',
            'prescription' => "1. Paracetamol 500mg 3x1 tablet\n2. Amoxicillin 500mg 3x1 kaplet",
            'doctor_finished_at' => Carbon::now(),
            'expired_at' => Carbon::now()->addHours(24),
        ]);

        // 1. Apoteker login dan melihat dashboard
        $this->actingAs($apoteker);
        $dashResponse = $this->get(route('apoteker.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Ajukan Ganti Obat');
        $dashResponse->assertSee($queue->queue_code);

        // 2. Apoteker mengajukan penggantian obat
        $replaceResponse = $this->post(route('apoteker.antrian.ajukan-penggantian-obat', $queue), [
            'prescription_replacement_reason' => 'Stok Amoxicillin 500mg kosong di apotek. Mohon ganti Cefadroxil 500mg.',
        ]);
        $replaceResponse->assertSessionHas('success');

        $queue->refresh();
        $this->assertTrue($queue->needs_prescription_revision);
        $this->assertEquals('Stok Amoxicillin 500mg kosong di apotek. Mohon ganti Cefadroxil 500mg.', $queue->prescription_replacement_reason);
        $this->assertNotNull($queue->prescription_replacement_requested_at);

        // Dashboard apoteker sekarang menampilkan label "Update Pengajuan Obat"
        $dashResponse2 = $this->get(route('apoteker.dashboard'));
        $dashResponse2->assertSee('Update Pengajuan Obat');
        $dashResponse2->assertSee('Menunggu Peresepan Ulang dari');

        // 3. Dokter login dan melihat notifikasi penggantian obat di dashboardnya
        $this->actingAs($dokter);
        $dokterDash = $this->get(route('dokter.dashboard'));
        $dokterDash->assertStatus(200);
        $dokterDash->assertSee('Permintaan Penggantian Resep Obat dari Apoteker');
        $dokterDash->assertSee('Stok Amoxicillin 500mg kosong');

        // 4. Dokter meresepkan ulang obat
        $newPrescription = "1. Paracetamol 500mg 3x1 tablet\n2. Cefadroxil 500mg 2x1 kapsul (pengganti Amoxicillin)";
        $resubmitResponse = $this->post(route('dokter.antrian.resepkan-ulang', $queue), [
            'prescription' => $newPrescription,
            'doctor_notes' => 'Disetujui penggantian ke Cefadroxil.',
        ]);
        $resubmitResponse->assertRedirect(route('dokter.dashboard'));
        $resubmitResponse->assertSessionHas('success');

        $queue->refresh();
        $this->assertFalse($queue->needs_prescription_revision);
        $this->assertEquals($newPrescription, $queue->prescription);
        $this->assertEquals('Disetujui penggantian ke Cefadroxil.', $queue->doctor_notes);

        // 5. Apoteker melihat resep terbaru dan sekarang dapat memverifikasinya
        $this->actingAs($apoteker);
        $verifyResponse = $this->post(route('apoteker.antrian.verifikasi-resep', $queue), [
            'pharmacist_notes' => 'Obat telah disiapkan lengkap dengan Cefadroxil.',
        ]);
        $verifyResponse->assertSessionHas('success');

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_SIAP_DIAMBIL, $queue->status);
    }
}
