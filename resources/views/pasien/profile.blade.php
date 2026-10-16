@extends('layouts.app')

@section('title', 'Profil & Pengaturan Akun Pasien - Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Top Bar -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1.5">
                    <a href="{{ route('pasien.dashboard') }}" class="hover:text-unpam-700 transition-colors">Dashboard Pasien</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span class="text-slate-800 font-semibold">Pengaturan Profil & Sandi</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                    Profil Saya & Keamanan Akun
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Kelola data identitas rekam medis pasien, informasi kontak, dan kata sandi akun Anda.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('pasien.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-2xs transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- KOLOM KIRI: Kartu Ringkasan Pasien & Status -->
            <div class="space-y-6">
                
                <!-- Identitas Pasien -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 rounded-full bg-unpam-50/80 pointer-events-none"></div>

                    <div class="flex flex-col items-center text-center">
                        <!-- Avatar Inisial -->
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-unpam-700 via-unpam-600 to-teal-400 text-white flex items-center justify-center text-2xl font-black shadow-lg shadow-unpam-600/25 mb-4">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>

                        <h2 class="text-lg font-bold text-slate-900 leading-snug">
                            {{ $user->name }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5 font-mono">
                            {{ $user->email }}
                        </p>

                        <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                Akun Pasien
                            </span>
                            @if(!empty($user->nik))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200" title="NIK Terdaftar">
                                    <i data-lucide="id-card" class="w-3.5 h-3.5"></i>
                                    NIK Terdaftar
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Harap lengkapi NIK">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                    NIK Belum Diisi
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Statistik Kunjungan Pasien -->
                    <div class="mt-6 pt-6 border-t border-slate-100 grid grid-cols-2 gap-3 text-center">
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="text-xl font-extrabold text-slate-900">{{ $totalVisits }}</div>
                            <div class="text-[11px] font-semibold text-slate-500 mt-0.5">Total Antrian</div>
                        </div>
                        <div class="p-3 rounded-2xl bg-teal-50 border border-teal-100">
                            <div class="text-xl font-extrabold text-unpam-700">{{ $completedVisits }}</div>
                            <div class="text-[11px] font-semibold text-teal-800 mt-0.5">Rekam Medis</div>
                        </div>
                    </div>

                    <!-- Meta Pasien -->
                    <div class="mt-6 pt-5 border-t border-slate-100 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                Terdaftar Sejak:
                            </span>
                            <span class="font-bold text-slate-800">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                No. Telepon / WA:
                            </span>
                            <span class="font-bold text-slate-800">{{ $user->phone ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span class="flex items-center gap-1.5 text-slate-400">
                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                Jenis Kelamin:
                            </span>
                            <span class="font-bold text-slate-800">
                                {{ $user->gender === 'L' ? 'Laki-laki' : ($user->gender === 'P' ? 'Perempuan' : '-') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tips Keamanan & Informasi NIK -->
                <div class="rounded-3xl p-6 bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-md relative overflow-hidden">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-xl bg-white/10 text-teal-300">
                            <i data-lucide="shield-alert" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Pentingnya NIK Pasien</h3>
                            <p class="text-xs text-slate-300 mt-1.5 leading-relaxed">
                                NIK digunakan oleh sistem Klinik Unpam 24 Jam sebagai kunci validasi data rekam medis dan pemulihan mandiri jika Anda lupa kata sandi.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-white/10 text-[11px] text-slate-400">
                        Pastikan NIK Anda sesuai dengan yang tertera di KTP elektronik asli.
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: Form Edit Profil & Ganti Password -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- CARD 1: Edit Data Diri & Identitas -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
                    
                    <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-unpam-50 text-unpam-700 flex items-center justify-center border border-unpam-100">
                                <i data-lucide="user-pen" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Biodata Diri & Kontak</h2>
                                <p class="text-xs text-slate-500">Perbarui nama lengkap, email, NIK, dan alamat tempat tinggal</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('pasien.profile.update') }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <!-- Nama Lengkap & Email (2 Kolom) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Nama Lengkap Pasien <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="user" class="w-4 h-4"></i>
                                    </div>
                                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                        class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border @error('name') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs placeholder:text-slate-400"
                                        placeholder="Nama Lengkap sesuai KTP">
                                </div>
                                @error('name')
                                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Alamat Email <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="mail" class="w-4 h-4"></i>
                                    </div>
                                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
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
                        </div>

                        <!-- NIK & Tanggal Lahir (2 Kolom) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                        Nomor Induk Kependudukan (NIK)
                                    </label>
                                    <span class="text-[10px] text-slate-400 font-medium">16 Digit KTP</span>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="id-card" class="w-4 h-4"></i>
                                    </div>
                                    <input type="text" id="nik" name="nik" value="{{ old('nik', $user->nik) }}" maxlength="16" pattern="[0-9]{16}" inputmode="numeric"
                                        class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border @error('nik') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs placeholder:text-slate-400"
                                        placeholder="16 Digit NIK KTP Anda">
                                </div>
                                @error('nik')
                                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="birth_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Tanggal Lahir
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="calendar" class="w-4 h-4"></i>
                                    </div>
                                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}"
                                        class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border @error('birth_date') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs">
                                </div>
                                @error('birth_date')
                                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Jenis Kelamin & No Telepon (2 Kolom) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="gender" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Jenis Kelamin
                                </label>
                                <div class="relative">
                                    <select id="gender" name="gender" class="block w-full px-3.5 py-2.5 text-sm rounded-xl border @error('gender') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L" {{ old('gender', $user->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('gender', $user->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                @error('gender')
                                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    No. WhatsApp / HP
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="phone" class="w-4 h-4"></i>
                                    </div>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                        class="block w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border @error('phone') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs placeholder:text-slate-400"
                                        placeholder="081234567890">
                                </div>
                                @error('phone')
                                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Alamat Domisili -->
                        <div>
                            <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Alamat Domisili Lengkap
                            </label>
                            <textarea id="address" name="address" rows="3"
                                class="block w-full px-3.5 py-2.5 text-sm rounded-xl border @error('address') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs placeholder:text-slate-400"
                                placeholder="Contoh: Jl. Surya Kencana No. 22, Pamulang Barat, Tangerang Selatan">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Action Button -->
                        <div class="pt-3 flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-unpam-600 hover:bg-unpam-700 shadow-md shadow-unpam-600/25 transition-all active:scale-[0.99]">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                Simpan Perubahan Profil
                            </button>
                        </div>
                    </form>

                </div>

                <!-- CARD 2: Ganti Kata Sandi Akun -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
                    
                    <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-100">
                                <i data-lucide="lock-keyhole" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Ubah Kata Sandi</h2>
                                <p class="text-xs text-slate-500">Pastikan menggunakan kombinasi kata sandi yang aman dan tidak mudah ditebak</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('pasien.password.update') }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <!-- Kata Sandi Saat Ini -->
                        <div>
                            <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                                </div>
                                <input type="password" id="current_password" name="current_password" required
                                    class="block w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border @error('current_password') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs placeholder:text-slate-400"
                                    placeholder="Masukkan kata sandi lama Anda">
                                <button type="button" onclick="toggleFieldVisibility('current_password', 'toggleOld')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                    <i data-lucide="eye" id="toggleOld" class="w-4 h-4"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Kata Sandi Baru & Konfirmasi (2 Kolom) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Kata Sandi Baru <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i data-lucide="lock" class="w-4 h-4"></i>
                                    </div>
                                    <input type="password" id="new_password" name="password" required minlength="6"
                                        class="block w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border @error('password') border-rose-400 focus:ring-rose-400 focus:border-rose-400 @else border-slate-300 focus:ring-unpam-500 focus:border-unpam-500 @enderror focus:ring-2 transition-colors shadow-2xs placeholder:text-slate-400"
                                        placeholder="Minimal 6 karakter">
                                    <button type="button" onclick="toggleFieldVisibility('new_password', 'toggleNew')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" id="toggleNew" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

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
                                    <button type="button" onclick="toggleFieldVisibility('password_confirmation', 'toggleConfirm')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" id="toggleConfirm" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-3 flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-md transition-all active:scale-[0.99]">
                                <i data-lucide="key" class="w-4 h-4 text-teal-400"></i>
                                Perbarui Kata Sandi
                            </button>
                        </div>
                    </form>

                </div>

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
