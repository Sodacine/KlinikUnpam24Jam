<?php

namespace Tests\Feature;

use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueExpirationAndSkipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test: Suster verifikasi menetapkan expired_at 24 jam, bisa di-skip dan di-recall.
     */
    public function test_nurse_can_skip_and_recall_queue_with_24_hour_expiry(): void
    {
        $nurse = User::where('role', User::ROLE_SUSTER)->first();
        $patient = User::where('role', User::ROLE_PASIEN)->first();
        $poli = Polyclinic::first();
        $doctor = User::where('role', User::ROLE_DOKTER)->first();

        $queue = Queue::create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $poli->id,
            'queue_code' => 'TEST-01',
            'queue_number' => 1,
            'queue_date' => today(),
            'complaint' => 'Demam tinggi',
            'status' => Queue::STATUS_MENUNGGU_SUSTER,
        ]);

        $this->actingAs($nurse);

        // 1. Verifikasi oleh suster -> expired_at di-set 24 jam
        $response = $this->post(route('suster.antrian.verifikasi', $queue), [
            'doctor_id' => $doctor->id,
        ]);
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_MENUNGGU_DOKTER, $queue->status);
        $this->assertNotNull($queue->nurse_verified_at);
        $this->assertNotNull($queue->expired_at);
        $this->assertTrue($queue->expired_at->isFuture());

        // 2. Suster skip pasien tak kunjung datang
        $response = $this->post(route('suster.antrian.skip', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_DILEWATI, $queue->status);
        $this->assertNotNull($queue->skipped_at);

        // 3. Pasien datang kembali, suster panggil ulang (recall)
        $response = $this->post(route('suster.antrian.recall', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_MENUNGGU_DOKTER, $queue->status);
    }

    /**
     * Test: Dokter dapat melewati pasien tak kunjung masuk dan memanggil ulang (recall).
     */
    public function test_doctor_can_skip_and_recall_waiting_patient(): void
    {
        $doctor = User::where('role', User::ROLE_DOKTER)->whereNotNull('polyclinic_id')->first();
        $patient = User::where('role', User::ROLE_PASIEN)->first();

        $queue = Queue::create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $doctor->polyclinic_id,
            'doctor_id' => $doctor->id,
            'queue_code' => 'DOK-01',
            'queue_number' => 2,
            'queue_date' => today(),
            'complaint' => 'Batuk dan sesak napas',
            'status' => Queue::STATUS_MENUNGGU_DOKTER,
            'nurse_verified_at' => now(),
            'expired_at' => now()->addHours(24),
        ]);

        $this->actingAs($doctor);

        // 1. Dokter skip pasien tidak kunjung hadir saat dipanggil
        $response = $this->post(route('dokter.antrian.skip', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_DILEWATI, $queue->status);
        $this->assertNotNull($queue->skipped_at);

        // 2. Dokter panggil ulang pasien saat hadir
        $response = $this->post(route('dokter.antrian.recall', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_MENUNGGU_DOKTER, $queue->status);
    }

    /**
     * Test: Apoteker dapat melewati pasien yang belum kunjung mengambil obat dan memanggil ulang.
     */
    public function test_pharmacist_can_skip_and_recall_ready_prescription(): void
    {
        $pharmacist = User::where('role', User::ROLE_APOTEKER)->first();
        $doctor = User::where('role', User::ROLE_DOKTER)->first();
        $patient = User::where('role', User::ROLE_PASIEN)->first();
        $poli = Polyclinic::first();

        $queue = Queue::create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $poli->id,
            'doctor_id' => $doctor->id,
            'queue_code' => 'APT-01',
            'queue_number' => 3,
            'queue_date' => today(),
            'complaint' => 'Sakit kepala',
            'disease' => 'Migrain Akut',
            'prescription' => 'Paracetamol 500mg 3x1',
            'status' => Queue::STATUS_SIAP_DIAMBIL,
            'nurse_verified_at' => now()->subHours(2),
            'doctor_finished_at' => now()->subHour(),
            'pharmacist_verified_at' => now()->subMinutes(30),
            'expired_at' => now()->addHours(24),
        ]);

        $this->actingAs($pharmacist);

        // 1. Apoteker skip antrian karena pasien tak kunjung datang mengambil obat
        $response = $this->post(route('apoteker.antrian.skip', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_DILEWATI, $queue->status);
        $this->assertNotNull($queue->skipped_at);

        // 2. Pasien akhirnya tiba di loket apotek, apoteker panggil ulang
        $response = $this->post(route('apoteker.antrian.recall', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_SIAP_DIAMBIL, $queue->status);
    }

    /**
     * Test: Antrian yang SUDAH diverifikasi dokter (menunggu_apotek) tetap dapat di-skip oleh apoteker dan dokter.
     */
    public function test_can_skip_queue_after_doctor_verification(): void
    {
        $pharmacist = User::where('role', User::ROLE_APOTEKER)->first();
        $doctor = User::where('role', User::ROLE_DOKTER)->first();
        $patient = User::where('role', User::ROLE_PASIEN)->first();
        $poli = Polyclinic::first();

        // Pasien sudah selesai diperiksa dokter, resep masuk ke farmasi (menunggu_apotek)
        $queue = Queue::create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $poli->id,
            'doctor_id' => $doctor->id,
            'queue_code' => 'APT-02',
            'queue_number' => 10,
            'queue_date' => today(),
            'complaint' => 'Radang tenggorokan',
            'disease' => 'Faringitis',
            'prescription' => 'Amoxicillin 500mg 3x1',
            'status' => Queue::STATUS_MENUNGGU_APOTEK,
            'nurse_verified_at' => now()->subHours(2),
            'doctor_finished_at' => now()->subMinutes(15),
            'expired_at' => now()->addHours(24),
        ]);

        // 1. Apoteker lewati pasien yang tak kunjung hadir ke apotek setelah dokter verifikasi
        $this->actingAs($pharmacist);
        $response = $this->post(route('apoteker.antrian.skip', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_DILEWATI, $queue->status);

        // 2. Apoteker panggil ulang pasien saat hadir
        $response = $this->post(route('apoteker.antrian.recall', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_MENUNGGU_APOTEK, $queue->status);

        // 3. Dokter juga dapat melewati pasien yang sudah diverifikasi jika pasien hilang
        $this->actingAs($doctor);
        $response = $this->post(route('dokter.antrian.skip', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_DILEWATI, $queue->status);
    }

    /**
     * Test: Suster dapat melewati antrian yang sudah diverifikasi (menuju dokter atau setelah dokter).
     */
    public function test_suster_can_skip_verified_queue(): void
    {
        $nurse = User::where('role', User::ROLE_SUSTER)->first();
        $patient = User::where('role', User::ROLE_PASIEN)->first();
        $poli = Polyclinic::first();

        $queue = Queue::create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $poli->id,
            'queue_code' => 'SUS-01',
            'queue_number' => 11,
            'queue_date' => today(),
            'complaint' => 'Pusing',
            'disease' => 'Vertigo',
            'prescription' => 'Betahistine 6mg',
            'status' => Queue::STATUS_MENUNGGU_APOTEK,
            'nurse_verified_at' => now()->subHours(1),
            'doctor_finished_at' => now()->subMinutes(10),
            'expired_at' => now()->addHours(24),
        ]);

        $this->actingAs($nurse);

        // Suster lewati antrian yang sudah diverifikasi dokter
        $response = $this->post(route('suster.antrian.skip', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_DILEWATI, $queue->status);

        // Suster panggil ulang
        $response = $this->post(route('suster.antrian.recall', $queue));
        $response->assertRedirect();

        $queue->refresh();
        $this->assertEquals(Queue::STATUS_MENUNGGU_APOTEK, $queue->status);
    }

    /**
     * Test: Antrian yang ditinggalkan dan melewati 24 jam otomatis hangus (terhapus)
     * dan pasien dapat mendaftar antrian baru.
     */
    public function test_abandoned_queue_auto_expires_after_24_hours(): void
    {
        $patient = User::factory()->create([
            'role' => User::ROLE_PASIEN,
            'email' => 'pasien.clean@unpam.ac.id',
        ]);
        $poli = Polyclinic::first();

        // Antrian yang sudah melewati batas waktu 24 jam (misal 26 jam lalu)
        $expiredQueue = Queue::create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $poli->id,
            'queue_code' => 'EXP-01',
            'queue_number' => 4,
            'queue_date' => Carbon::yesterday(),
            'complaint' => 'Nyeri perut',
            'status' => Queue::STATUS_MENUNGGU_DOKTER,
            'nurse_verified_at' => now()->subHours(25),
            'expired_at' => now()->subHour(), // Sudah lewat 1 jam dari batas 24 jam
        ]);

        $this->assertTrue($expiredQueue->is_expired);

        // Jalankan auto cleanup
        $deletedCount = Queue::autoExpireOutdatedQueues();
        $this->assertGreaterThanOrEqual(1, $deletedCount);

        // Pastikan record terhapus dari database
        $this->assertDatabaseMissing('queues', [
            'id' => $expiredQueue->id,
        ]);

        // Pasien sekarang bebas mendaftar antrian baru tanpa terhalang tiket aktif lama
        $this->actingAs($patient);
        $response = $this->post(route('pasien.antrian.store'), [
            'polyclinic_id' => $poli->id,
            'complaint' => 'Flu dan demam baru',
        ]);
        $response->assertRedirect(route('pasien.dashboard'));
        $this->assertDatabaseHas('queues', [
            'patient_id' => $patient->id,
            'complaint' => 'Flu dan demam baru',
        ]);
    }

    /**
     * Test: Artisan command queue:expire-skipped berhasil membersihkan antrian kadaluarsa.
     */
    public function test_artisan_command_expires_outdated_queues(): void
    {
        $patient = User::where('role', User::ROLE_PASIEN)->first();
        $poli = Polyclinic::first();

        $queue = Queue::create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $poli->id,
            'queue_code' => 'CMD-01',
            'queue_number' => 5,
            'queue_date' => Carbon::yesterday(),
            'complaint' => 'Sakit gigi',
            'status' => Queue::STATUS_DILEWATI,
            'skipped_at' => now()->subHours(25),
            'expired_at' => now()->subHours(1),
        ]);

        $this->artisan('queue:expire-skipped')
            ->expectsOutputToContain('antrian yang telah kadaluarsa (>24 jam)')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('queues', [
            'id' => $queue->id,
        ]);
    }

    /**
     * Test: Endpoint polling pasien mendeteksi antrian hangus dan menghapusnya.
     */
    public function test_pasien_polling_detects_and_cleans_expired_queue(): void
    {
        $patient = User::where('role', User::ROLE_PASIEN)->first();
        $poli = Polyclinic::first();

        $queue = Queue::create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $poli->id,
            'queue_code' => 'POLL-01',
            'queue_number' => 6,
            'queue_date' => Carbon::yesterday(),
            'complaint' => 'Pusing',
            'status' => Queue::STATUS_DILEWATI,
            'nurse_verified_at' => now()->subHours(26),
            'expired_at' => now()->subHours(2),
        ]);

        $this->actingAs($patient);

        $response = $this->getJson("/pasien/antrian/status/{$queue->id}");
        $response->assertOk()
            ->assertJson([
                'is_expired' => true,
                'is_finished' => true,
            ]);

        $this->assertDatabaseMissing('queues', [
            'id' => $queue->id,
        ]);
    }
}
