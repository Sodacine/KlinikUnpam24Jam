@extends('layouts.app')

@section('title', 'Pendaftaran Pasien Baru - Klinik Unpam 24 Jam')

@section('content')
<div class="py-8 sm:px-6 lg:px-8 bg-gradient-to-b from-slate-50 via-teal-50/20 to-slate-100 min-h-[calc(100vh-140px)] flex items-center justify-center">
    <div class="max-w-2xl w-full mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-unpam-100 text-unpam-800 border border-unpam-200 mb-3">
                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                Registrasi Pasien
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                Pendaftaran Pasien Baru
            </h1>
            <p class="mt-1.5 text-sm text-slate-600">
                Lengkapi biodata diri Anda untuk mulai mengambil nomor antrian dan mengakses rekam medis digital.
            </p>
        </div>

        <!-- Form Registrasi -->
        <div class="bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-100">
            <form action="{{ route('register') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Nama Lengkap -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap Pasien <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                            placeholder="Contoh: Rangga Permana">
                    </div>
                </div>

                <!-- NIK & Tanggal Lahir (Grid 2 Kolom) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nomor Induk Kependudukan (NIK)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="id-card" class="w-4 h-4"></i>
                            </div>
                            <input type="text" id="nik" name="nik" value="{{ old('nik') }}" maxlength="16"
                                class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                                placeholder="16 Digit NIK KTP">
                        </div>
                    </div>

                    <div>
                        <label for="birth_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tanggal Lahir
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                                class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs">
                        </div>
                    </div>
                </div>

                <!-- Jenis Kelamin & No Telepon -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="gender" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Jenis Kelamin
                        </label>
                        <select id="gender" name="gender" class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            No. WhatsApp / HP
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="phone" class="w-4 h-4"></i>
                            </div>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                                placeholder="081234567890">
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                            placeholder="nama@email.com">
                    </div>
                </div>

                <!-- Alamat Domisili -->
                <div>
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Domisili
                    </label>
                    <textarea id="address" name="address" rows="2"
                        class="block w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                        placeholder="Contoh: Jl. Surya Kencana No. 1, Pamulang Barat">{{ old('address') }}</textarea>
                </div>

                <!-- Password & Konfirmasi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kata Sandi <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input type="password" id="password" name="password" required minlength="6"
                                class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                                placeholder="Minimal 6 karakter">
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Ulangi Kata Sandi <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="lock-keyhole" class="w-4 h-4"></i>
                            </div>
                            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                                class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                                placeholder="Ketik ulang kata sandi">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-unpam-700 via-unpam-600 to-teal-600 hover:from-unpam-800 hover:to-teal-700 shadow-lg shadow-unpam-600/30 transition-all active:scale-[0.99]">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Daftar & Masuk Dashboard Pasien
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-600">
                    Sudah memiliki akun?
                    <a href="{{ route('login') }}" class="font-bold text-unpam-700 hover:text-unpam-800 hover:underline">
                        Masuk di Sini
                    </a>
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
