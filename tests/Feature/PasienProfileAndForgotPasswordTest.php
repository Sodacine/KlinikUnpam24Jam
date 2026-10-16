<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasienProfileAndForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test: Guest dapat mengakses halaman Lupa Password
     */
    public function test_guest_can_view_forgot_password_page(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Lupa Kata Sandi');
        $response->assertSee('Nomor Induk Kependudukan (NIK)');
    }

    /**
     * Test: Pasien berhasil reset password dengan menyamakan NIK yang terdaftar
     */
    public function test_patient_can_reset_password_with_matching_nik(): void
    {
        $patient = User::where('email', 'pasien@unpam.ac.id')->first();
        $this->assertNotNull($patient->nik);

        $response = $this->post(route('password.update-nik'), [
            'email' => $patient->email,
            'nik' => $patient->nik,
            'password' => 'katasandibaru123',
            'password_confirmation' => 'katasandibaru123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $patient->refresh();
        $this->assertTrue(Hash::check('katasandibaru123', $patient->password));

        // Verifikasi pasien bisa login dengan kata sandi baru
        $loginResponse = $this->post(route('login'), [
            'email' => $patient->email,
            'password' => 'katasandibaru123',
        ]);
        $loginResponse->assertRedirect(route('pasien.dashboard'));
    }

    /**
     * Test: Reset password gagal jika NIK tidak cocok dengan data rekam medis pasien
     */
    public function test_patient_cannot_reset_password_with_mismatched_nik(): void
    {
        $patient = User::where('email', 'pasien@unpam.ac.id')->first();
        $oldPasswordHash = $patient->password;

        $response = $this->post(route('password.update-nik'), [
            'email' => $patient->email,
            'nik' => '3674000000000000', // NIK salah
            'password' => 'katasandibaru123',
            'password_confirmation' => 'katasandibaru123',
        ]);

        $response->assertSessionHasErrors(['nik']);

        $patient->refresh();
        $this->assertEquals($oldPasswordHash, $patient->password);
    }

    /**
     * Test: Reset password dengan NIK ditolak untuk akun selain Pasien (misal Suster)
     */
    public function test_non_patient_cannot_reset_password_via_patient_nik_flow(): void
    {
        $suster = User::where('email', 'suster@unpam.ac.id')->first();

        $response = $this->post(route('password.update-nik'), [
            'email' => $suster->email,
            'nik' => $suster->nik,
            'password' => 'susterbaru123',
            'password_confirmation' => 'susterbaru123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test: Pasien dapat membuka halaman Profil & Keamanan Akun
     */
    public function test_patient_can_view_profile_page(): void
    {
        $patient = User::where('email', 'pasien@unpam.ac.id')->first();
        $this->actingAs($patient);

        $response = $this->get(route('pasien.profile'));

        $response->assertStatus(200);
        $response->assertSee('Profil Saya');
        $response->assertSee($patient->name);
        $response->assertSee($patient->email);
    }

    /**
     * Test: Pasien dapat memperbarui nama, NIK, kontak, dan alamat domisili
     */
    public function test_patient_can_update_profile_information(): void
    {
        $patient = User::where('email', 'pasien@unpam.ac.id')->first();
        $this->actingAs($patient);

        $response = $this->put(route('pasien.profile.update'), [
            'name' => 'Rangga Permana Update',
            'email' => 'rangga.baru@unpam.ac.id',
            'phone' => '089988776655',
            'nik' => '3674012304990001',
            'birth_date' => '1999-04-23',
            'gender' => 'L',
            'address' => 'Jl. Siliwangi No. 100, Pamulang',
        ]);

        $response->assertRedirect(route('pasien.profile'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'name' => 'Rangga Permana Update',
            'email' => 'rangga.baru@unpam.ac.id',
            'phone' => '089988776655',
            'address' => 'Jl. Siliwangi No. 100, Pamulang',
        ]);
    }

    /**
     * Test: Pasien dapat mengganti kata sandi dengan kata sandi saat ini yang benar
     */
    public function test_patient_can_change_password_with_valid_current_password(): void
    {
        $patient = User::where('email', 'pasien@unpam.ac.id')->first();
        $this->actingAs($patient);

        $response = $this->put(route('pasien.password.update'), [
            'current_password' => 'password',
            'password' => 'gantipassword123',
            'password_confirmation' => 'gantipassword123',
        ]);

        $response->assertRedirect(route('pasien.profile'));
        $response->assertSessionHas('success');

        $patient->refresh();
        $this->assertTrue(Hash::check('gantipassword123', $patient->password));
    }

    /**
     * Test: Ganti kata sandi ditolak jika kata sandi saat ini salah
     */
    public function test_patient_cannot_change_password_with_invalid_current_password(): void
    {
        $patient = User::where('email', 'pasien@unpam.ac.id')->first();
        $this->actingAs($patient);

        $response = $this->put(route('pasien.password.update'), [
            'current_password' => 'passwordsalah',
            'password' => 'gantipassword123',
            'password_confirmation' => 'gantipassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);

        $patient->refresh();
        $this->assertTrue(Hash::check('password', $patient->password));
    }
}
