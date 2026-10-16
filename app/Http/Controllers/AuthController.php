<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan Halaman Login Tunggal (Single Door)
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->getDashboardRoute());
        }

        return view('auth.login');
    }

    /**
     * Proses Login & Deteksi Role Otomatis
     * Tanpa pilihan dropdown atau radio button sama sekali
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Pengalihan otomatis berdasarkan role hasil deteksi kredensial
            return redirect()->intended(route($user->getDashboardRoute()))
                ->with('success', "Selamat datang kembali, {$user->name}! Anda masuk sebagai {$user->role_label}.");
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai.',
            ]);
    }

    /**
     * Tampilkan Halaman Registrasi Khusus Pasien
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->getDashboardRoute());
        }

        return view('auth.register');
    }

    /**
     * Proses Registrasi Pasien Baru
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'phone' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'digits_between:16,16'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:L,P'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'nik.digits_between' => 'NIK harus 16 digit angka.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'pasien', // Otomatis role pasien
            'phone' => $validated['phone'] ?? null,
            'nik' => $validated['nik'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('pasien.dashboard')
            ->with('success', "Pendaftaran berhasil! Selamat datang di Klinik Unpam 24 Jam, {$user->name}.");
    }

    /**
     * Logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar dari sistem Klinik Unpam 24 Jam.');
    }

    /**
     * Tampilkan Halaman Lupa Password Pasien Berbasis Validasi NIK
     */
    public function showForgotPasswordForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->getDashboardRoute());
        }

        return view('auth.forgot_password');
    }

    /**
     * Proses Reset Password Pasien dengan Verifikasi Kecocokan NIK
     */
    public function resetPasswordWithNik(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'nik' => ['required', 'string', 'digits_between:16,16'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'nik.required' => 'Nomor Induk Kependudukan (NIK) wajib diisi.',
            'nik.digits_between' => 'NIK harus berupa 16 digit angka sesuai KTP.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email', 'nik'))
                ->withErrors(['email' => 'Akun dengan alamat email ini tidak ditemukan di sistem Klinik Unpam.']);
        }

        if (! $user->isPasien()) {
            return back()
                ->withInput($request->only('email', 'nik'))
                ->withErrors(['email' => 'Fitur pemulihan kata sandi mandiri via NIK hanya berlaku untuk akun Pasien. Tenaga medis harap hubungi bagian administrasi klinik.']);
        }

        if (empty($user->nik)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['nik' => 'Akun pasien Anda belum memiliki NIK yang terdaftar. Harap verifikasi identitas ke meja pendaftaran klinik terlebih dahulu.']);
        }

        if (trim((string) $user->nik) !== trim((string) $validated['nik'])) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['nik' => 'Nomor NIK yang dimasukkan tidak cocok dengan NIK pada data rekam medis pasien ini.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('login')
            ->with('success', "Kata sandi untuk pasien {$user->name} berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.");
    }
}
