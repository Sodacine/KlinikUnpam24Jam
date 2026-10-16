@extends('layouts.app')

@section('title', 'Lupa Kata Sandi Pasien - Klinik Unpam 24 Jam')

@section('content')
<div class="min-h-[calc(100vh-140px)] flex flex-col justify-center py-8 sm:px-6 lg:px-8 bg-gradient-to-b from-slate-50 via-teal-50/20 to-slate-100">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        
        <!-- Header -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-600 via-teal-600 to-unpam-600 text-white shadow-xl shadow-unpam-600/25 mb-4">
                <i data-lucide="key-round" class="w-8 h-8 stroke-[2.2]"></i>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 mb-2">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-amber-600"></i>
                Verifikasi Identitas NIK Pasien
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Lupa Kata Sandi
            </h1>
            <p class="mt-2 text-sm text-slate-600 max-w-sm mx-auto">
                Samakan Email dan Nomor Induk Kependudukan (NIK) terdaftar untuk mereset kata sandi Anda secara langsung.
            </p>
        </div>

        <!-- Kartu Reset Password -->
        <div class="mt-8 bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-100">
            
            <div class="mb-6 p-4 rounded-2xl bg-teal-50/80 border border-teal-100 flex items-start gap-3">
                <div class="p-1.5 bg-unpam-600 rounded-xl text-white mt-0.5 shrink-0">
                    <i data-lucide="info" class="w-4 h-4"></i>
                </div>
                <p class="text-xs text-teal-900 leading-relaxed">
                    Demi keamanan rekam medis, sistem akan mencocokkan <strong class="font-bold">Email</strong> dan <strong class="font-bold">16 digit NIK KTP</strong> Anda sebelum menerapkan kata sandi baru.
                </p>
            </div>

            <form action="{{ route('password.update-nik') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Input Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email Pasien <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border @error('email') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs placeholder:text-slate-400"
                            placeholder="nama@email.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Input NIK -->
                <div>
                    <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Induk Kependudukan (NIK) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="id-card" class="w-4 h-4"></i>
                        </div>
                        <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required maxlength="16" pattern="[0-9]{16}" inputmode="numeric"
                            class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border @error('nik') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs placeholder:text-slate-400"
                            placeholder="16 Digit NIK sesuai KTP">
                    </div>
                    @error('nik')
                        <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Kata Sandi Baru -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input type="password" id="password" name="password" required minlength="6"
                            class="block w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border @error('password') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs placeholder:text-slate-400"
                            placeholder="Minimal 6 karakter">
                        <button type="button" onclick="toggleFieldVisibility('password', 'toggleIcon1')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i data-lucide="eye" id="toggleIcon1" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Konfirmasi Kata Sandi Baru -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Ulangi Kata Sandi Baru <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock-keyhole" class="w-4 h-4"></i>
                        </div>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                            class="block w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                            placeholder="Ketik ulang kata sandi baru">
                        <button type="button" onclick="toggleFieldVisibility('password_confirmation', 'toggleIcon2')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i data-lucide="eye" id="toggleIcon2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-unpam-700 via-unpam-600 to-teal-600 hover:from-unpam-800 hover:to-teal-700 shadow-lg shadow-unpam-600/30 transition-all active:scale-[0.99]">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Verifikasi NIK & Ubah Kata Sandi
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-unpam-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    Kembali Masuk
                </a>
                <a href="{{ route('register') }}" class="font-bold text-unpam-700 hover:text-unpam-800 hover:underline">
                    Daftar Pasien Baru
                </a>
            </div>

        </div>

    </div>
</div>

<script>
    function toggleFieldVisibility(fieldId, iconId) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }
</script>
@endsection
