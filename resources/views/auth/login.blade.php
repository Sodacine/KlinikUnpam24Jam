@extends('layouts.app')

@section('title', 'Masuk - Klinik Unpam 24 Jam')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex flex-col justify-center py-8 sm:px-6 lg:px-8 bg-gradient-to-b from-slate-50 via-teal-50/20 to-slate-100">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        
        <!-- Badge Header -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-unpam-700 via-unpam-600 to-teal-400 text-white shadow-xl shadow-unpam-600/25 mb-4">
                <i data-lucide="cross" class="w-9 h-9 stroke-[2.5]"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Klinik Unpam 24 Jam
            </h1>
            <p class="mt-2 text-sm text-slate-600 max-w-sm mx-auto">
                Satu Pintu Masuk Layanan Antrian & Rekam Medis Digital
            </p>
        </div>

        <!-- Kartu Login -->
        <div class="mt-8 bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-100">
            
            <div class="mb-6 pb-4 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800">Masuk ke Akun Anda</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Sistem mendeteksi peran Anda secara otomatis (Pasien, Suster, Dokter, atau Apoteker).
                </p>
            </div>

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Input Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email / ID
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                            placeholder="nama@unpam.ac.id">
                    </div>
                </div>

                <!-- Input Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Kata Sandi
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-unpam-600 hover:text-unpam-800 hover:underline transition-colors">
                            Lupa kata sandi?
                        </a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="block w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i data-lucide="eye" id="toggleIcon" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-unpam-600 focus:ring-unpam-500 border-slate-300">
                        <span class="text-xs text-slate-600 font-medium">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-unpam-700 via-unpam-600 to-teal-600 hover:from-unpam-800 hover:to-teal-700 shadow-lg shadow-unpam-600/30 transition-all active:scale-[0.99]">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Masuk
                    </button>
                </div>
            </form>

            <!-- Registrasi Pasien Link -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-600">
                    Belum memiliki akun pasien?
                    <a href="{{ route('register') }}" class="font-bold text-unpam-700 hover:text-unpam-800 hover:underline">
                        Daftar Pasien Baru
                    </a>
                </p>
            </div>



        </div>


    </div>
</div>

<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }

    function togglePasswordVisibility() {
        const passInput = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            passInput.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }
</script>
@endsection
