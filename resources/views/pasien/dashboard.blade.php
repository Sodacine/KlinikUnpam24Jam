@extends('layouts.app')

@section('title', 'Dashboard Pasien - Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-unpam-800 via-unpam-700 to-teal-700 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-unpam-900/10 mb-8 relative overflow-hidden">
            <div class="absolute -right-8 -bottom-8 w-60 h-60 rounded-full bg-white/5 pointer-events-none"></div>
            <div class="absolute right-40 -top-10 w-40 h-40 rounded-full bg-teal-400/10 pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/15 text-teal-100 border border-white/20 mb-3">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        Portal Pasien Klinik Unpam 24 Jam
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Halo, {{ $user->name }}!
                    </h1>
                    <p class="text-teal-100 text-sm mt-1 max-w-xl">
                        Selamat datang di sistem antrian digital dan riwayat rekam medis terpadu. Pantau antrian Anda secara real-time dan akses riwayat kesehatan kapan saja.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('pasien.profile') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 backdrop-blur transition-all">
                        <i data-lucide="user-cog" class="w-4 h-4"></i>
                        Edit Profil & Sandi
                    </a>
                    <a href="{{ route('pasien.riwayat-medis') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 backdrop-blur transition-all">
                        <i data-lucide="file-heart" class="w-4 h-4"></i>
                        Lihat Riwayat Medis
                    </a>
                </div>
            </div>
        </div>

        <!-- KARTU DATA DIRI PASIEN SINGKAT -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-unpam-700 via-unpam-600 to-teal-400 text-white flex items-center justify-center text-lg font-black shadow-md shadow-unpam-600/20 shrink-0">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm sm:text-base font-bold text-slate-900">{{ $user->name }}</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                            Pasien Terdaftar
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-1 text-xs text-slate-500">
                        <span class="flex items-center gap-1 font-mono">
                            <i data-lucide="id-card" class="w-3.5 h-3.5 text-slate-400"></i>
                            NIK: {{ $user->nik ?? 'Belum diisi' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                            {{ $user->phone ?? 'Belum ada telepon' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                            {{ $user->email }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="shrink-0">
                <a href="{{ route('pasien.profile') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-unpam-700 bg-unpam-50 hover:bg-unpam-100 border border-unpam-200 transition-colors">
                    <i data-lucide="user-pen" class="w-3.5 h-3.5"></i>
                    Ubah Profil & Sandi
                </a>
            </div>
        </div>

        @if($activeQueue)
            <!-- ========================================== -->
            <!-- TIKET ANTRIAN AKTIF PASIEN (REAL-TIME)    -->
            <!-- ========================================== -->
            <div id="activeQueueContainer" class="bg-white rounded-3xl border-2 border-unpam-500/30 shadow-xl shadow-slate-200/50 p-6 sm:p-8 mb-8 relative">
                
                <!-- Live Indicator Ribbon -->
                <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                            Tiket Antrian Aktif Hari Ini
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <span id="liveTimestamp" class="text-xs text-slate-400 font-mono">
                            Terakhir dicek: {{ now()->format('H:i:s') }}
                        </span>
                        @if($activeQueue->status === \App\Models\Queue::STATUS_MENUNGGU_SUSTER)
                            <form action="{{ route('pasien.antrian.cancel', $activeQueue) }}" method="POST" class="inline"
                                  data-confirm="Apakah Anda yakin ingin membatalkan antrian ini? Anda harus mendaftar ulang jika membatalkan."
                                  data-confirm-type="danger"
                                  data-confirm-title="Batalkan Antrian"
                                  data-confirm-ok="Ya, Batalkan">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                    Batalkan Antrian
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Nomor Antrian & Detail Poli -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 py-6 items-center">
                    
                    <!-- Kotak Nomor Tiket -->
                    <div class="text-center lg:text-left bg-gradient-to-br from-unpam-50 via-teal-50/50 to-white p-6 rounded-3xl border border-unpam-100">
                        <span class="text-xs font-bold uppercase tracking-wider text-unpam-700">Nomor Antrian Anda</span>
                        <div id="queueCodeDisplay" class="text-5xl sm:text-6xl font-black tracking-tight text-slate-900 mt-2 mb-1 font-mono">
                            {{ $activeQueue->queue_code }}
                        </div>
                        <p class="text-xs text-slate-500">
                            Urutan ke-{{ $activeQueue->queue_number }} pada {{ $activeQueue->queue_date->format('d M Y') }}
                        </p>

                        <div class="mt-4 pt-4 border-t border-unpam-100 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Antrian di depan Anda:</span>
                            <span id="queueAheadDisplay" class="font-bold text-slate-800 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                                {{ $waitingCountAhead }} Pasien
                            </span>
                        </div>
                    </div>

                    <!-- Informasi Poliklinik & Ruangan -->
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Poliklinik Tujuan</span>
                            <h3 id="poliNameDisplay" class="text-xl font-extrabold text-slate-900 mt-0.5">
                                {{ $activeQueue->polyclinic->name }}
                            </h3>
                            <p id="poliRoomDisplay" class="text-xs text-unpam-700 font-semibold flex items-center gap-1.5 mt-1">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                {{ $activeQueue->polyclinic->floor_room }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Dokter Bertugas</span>
                            <p id="doctorNameDisplay" class="text-sm font-bold text-slate-800 flex items-center gap-1.5 mt-0.5">
                                <i data-lucide="stethoscope" class="w-4 h-4 text-slate-400"></i>
                                {{ $activeQueue->doctor?->name ?? 'Akan ditentukan saat verifikasi Suster' }}
                            </p>
                        </div>

                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Keluhan yang Didaftarkan</span>
                            <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200 mt-1">
                                "{{ $activeQueue->complaint }}"
                            </p>
                        </div>
                    </div>

                    <!-- Box Status Saat Ini & Instruksi -->
                    <div class="bg-slate-900 text-white p-6 rounded-3xl shadow-lg shadow-slate-900/10">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Pemeriksaan</span>
                        <div class="mt-2">
                            <span id="statusBadgeDisplay" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border {{ $activeQueue->status_badge_class }}">
                                {{ $activeQueue->status_label }}
                            </span>
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-teal-400 flex items-center gap-1">
                                <i data-lucide="bell-ring" class="w-3.5 h-3.5"></i>
                                Instruksi Pasien:
                            </span>
                            <p id="instructionDisplay" class="text-xs text-slate-300 mt-1.5 leading-relaxed">
                                {{ $activeQueue->instruction }}
                            </p>
                        </div>
                    </div>

                </div>

                <!-- 6 Tahap Alur Antrian (State Machine Stepper) -->
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 text-center sm:text-left">
                        Progres Alur Antrian Pasien (6 Tahap)
                    </span>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-center" id="stepperContainer">
                        
                        <!-- Step 1: Menunggu Suster -->
                        <div class="step-item p-3 rounded-2xl border transition-all {{ $activeQueue->status_step >= 1 ? ($activeQueue->status_step == 1 ? 'bg-amber-50 border-amber-300 text-amber-900 shadow-xs' : 'bg-emerald-50 border-emerald-200 text-emerald-800') : 'bg-slate-50 border-slate-200 text-slate-400' }}" data-step="1">
                            <div class="w-7 h-7 mx-auto rounded-full flex items-center justify-center text-xs font-bold mb-1.5 {{ $activeQueue->status_step > 1 ? 'bg-emerald-600 text-white' : ($activeQueue->status_step == 1 ? 'bg-amber-500 text-white animate-pulse' : 'bg-slate-200 text-slate-600') }}">
                                @if($activeQueue->status_step > 1) <i data-lucide="check" class="w-3.5 h-3.5"></i> @else 1 @endif
                            </div>
                            <span class="text-xs font-bold block">1. Menunggu Suster</span>
                            <span class="text-[10px] text-slate-500 block mt-0.5">Ruang Tunggu</span>
                        </div>

                        <!-- Step 2: Menunggu Dokter -->
                        <div class="step-item p-3 rounded-2xl border transition-all {{ $activeQueue->status_step >= 2 ? ($activeQueue->status_step == 2 ? 'bg-blue-50 border-blue-300 text-blue-900 shadow-xs' : 'bg-emerald-50 border-emerald-200 text-emerald-800') : 'bg-slate-50 border-slate-200 text-slate-400' }}" data-step="2">
                            <div class="w-7 h-7 mx-auto rounded-full flex items-center justify-center text-xs font-bold mb-1.5 {{ $activeQueue->status_step > 2 ? 'bg-emerald-600 text-white' : ($activeQueue->status_step == 2 ? 'bg-blue-600 text-white animate-pulse' : 'bg-slate-200 text-slate-600') }}">
                                @if($activeQueue->status_step > 2) <i data-lucide="check" class="w-3.5 h-3.5"></i> @else 2 @endif
                            </div>
                            <span class="text-xs font-bold block">2. Menuju Dokter</span>
                            <span class="text-[10px] text-slate-500 block mt-0.5">Depan Poli</span>
                        </div>

                        <!-- Step 3: Pemeriksaan Dokter -->
                        <div class="step-item p-3 rounded-2xl border transition-all {{ $activeQueue->status_step >= 3 ? ($activeQueue->status_step == 3 ? 'bg-indigo-50 border-indigo-300 text-indigo-900 shadow-xs' : 'bg-emerald-50 border-emerald-200 text-emerald-800') : 'bg-slate-50 border-slate-200 text-slate-400' }}" data-step="3">
                            <div class="w-7 h-7 mx-auto rounded-full flex items-center justify-center text-xs font-bold mb-1.5 {{ $activeQueue->status_step > 3 ? 'bg-emerald-600 text-white' : ($activeQueue->status_step == 3 ? 'bg-indigo-600 text-white animate-pulse' : 'bg-slate-200 text-slate-600') }}">
                                @if($activeQueue->status_step > 3) <i data-lucide="check" class="w-3.5 h-3.5"></i> @else 3 @endif
                            </div>
                            <span class="text-xs font-bold block">3. Pemeriksaan</span>
                            <span class="text-[10px] text-slate-500 block mt-0.5">Di Ruang Dokter</span>
                        </div>

                        <!-- Step 4: Menunggu Apotek -->
                        <div class="step-item p-3 rounded-2xl border transition-all {{ $activeQueue->status_step >= 4 ? ($activeQueue->status_step == 4 ? 'bg-purple-50 border-purple-300 text-purple-900 shadow-xs' : 'bg-emerald-50 border-emerald-200 text-emerald-800') : 'bg-slate-50 border-slate-200 text-slate-400' }}" data-step="4">
                            <div class="w-7 h-7 mx-auto rounded-full flex items-center justify-center text-xs font-bold mb-1.5 {{ $activeQueue->status_step > 4 ? 'bg-emerald-600 text-white' : ($activeQueue->status_step == 4 ? 'bg-purple-600 text-white animate-pulse' : 'bg-slate-200 text-slate-600') }}">
                                @if($activeQueue->status_step > 4) <i data-lucide="check" class="w-3.5 h-3.5"></i> @else 4 @endif
                            </div>
                            <span class="text-xs font-bold block">4. Racik Obat</span>
                            <span class="text-[10px] text-slate-500 block mt-0.5">Apotek Menyiapkan</span>
                        </div>

                        <!-- Step 5: Siap Diambil -->
                        <div class="step-item p-3 rounded-2xl border transition-all {{ $activeQueue->status_step >= 5 ? ($activeQueue->status_step == 5 ? 'bg-teal-50 border-teal-300 text-teal-900 shadow-xs' : 'bg-emerald-50 border-emerald-200 text-emerald-800') : 'bg-slate-50 border-slate-200 text-slate-400' }}" data-step="5">
                            <div class="w-7 h-7 mx-auto rounded-full flex items-center justify-center text-xs font-bold mb-1.5 {{ $activeQueue->status_step > 5 ? 'bg-emerald-600 text-white' : ($activeQueue->status_step == 5 ? 'bg-teal-600 text-white animate-pulse' : 'bg-slate-200 text-slate-600') }}">
                                @if($activeQueue->status_step > 5) <i data-lucide="check" class="w-3.5 h-3.5"></i> @else 5 @endif
                            </div>
                            <span class="text-xs font-bold block">5. Siap Diambil</span>
                            <span class="text-[10px] text-slate-500 block mt-0.5">Ke Loket Apotek</span>
                        </div>

                        <!-- Step 6: Selesai -->
                        <div class="step-item p-3 rounded-2xl border transition-all {{ $activeQueue->status_step == 6 ? 'bg-emerald-100 border-emerald-300 text-emerald-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-400' }}" data-step="6">
                            <div class="w-7 h-7 mx-auto rounded-full flex items-center justify-center text-xs font-bold mb-1.5 {{ $activeQueue->status_step == 6 ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                                @if($activeQueue->status_step == 6) <i data-lucide="check" class="w-3.5 h-3.5"></i> @else 6 @endif
                            </div>
                            <span class="text-xs font-bold block">6. Selesai</span>
                            <span class="text-[10px] text-slate-500 block mt-0.5">Masuk Rekam Medis</span>
                        </div>

                    </div>
                </div>

            </div>
        @else
            <!-- ========================================== -->
            <!-- FORM AMBIL TIKET ANTRIAN BARU             -->
            <!-- ========================================== -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-200/40 p-6 sm:p-8 mb-8">
                <div class="max-w-3xl mb-6">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-unpam-100 text-unpam-800 border border-unpam-200 mb-2">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                        Pendaftaran Antrian Baru
                    </span>
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">
                        Pilih Poliklinik & Ambil Nomor Antrian
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Klinik Unpam 24 Jam menyediakan 4 poliklinik spesialis dan umum. Silakan pilih poliklinik yang sesuai dengan keluhan kesehatan Anda.
                    </p>
                </div>

                <form action="{{ route('pasien.antrian.store') }}" method="POST">
                    @csrf

                    <!-- Pilihan 4 Poliklinik (Strictly 4) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        @foreach($polyclinics as $poli)
                            <label class="relative flex flex-col p-5 rounded-2xl border-2 border-slate-200 hover:border-unpam-500 cursor-pointer transition-all hover:shadow-md bg-white has-[:checked]:border-unpam-600 has-[:checked]:bg-unpam-50/40 has-[:checked]:shadow-md group">
                                <input type="radio" name="polyclinic_id" value="{{ $poli->id }}" required class="sr-only peer" {{ old('polyclinic_id') == $poli->id ? 'checked' : '' }}>
                                
                                <div class="flex items-center justify-between mb-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 peer-checked:bg-unpam-600 group-has-[:checked]:bg-unpam-600 group-has-[:checked]:text-white text-slate-600 flex items-center justify-center transition-colors">
                                        @if($poli->code === 'ANK')
                                            <i data-lucide="baby" class="w-5 h-5"></i>
                                        @elseif($poli->code === 'UMU')
                                            <i data-lucide="stethoscope" class="w-5 h-5"></i>
                                        @elseif($poli->code === 'KND')
                                            <i data-lucide="heart" class="w-5 h-5"></i>
                                        @elseif($poli->code === 'GGI')
                                            <i data-lucide="smile" class="w-5 h-5"></i>
                                        @else
                                            <i data-lucide="activity" class="w-5 h-5"></i>
                                        @endif
                                    </div>
                                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 group-has-[:checked]:bg-unpam-200 group-has-[:checked]:text-unpam-900">
                                        {{ $poli->code }}
                                    </span>
                                </div>

                                <h3 class="text-base font-bold text-slate-900 group-has-[:checked]:text-unpam-800">
                                    {{ $poli->name }}
                                </h3>
                                <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                                    {{ $poli->description }}
                                </p>

                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                    <span class="font-semibold text-slate-600">{{ $poli->floor_room }}</span>
                                    <span class="text-unpam-600 font-bold group-has-[:checked]:block hidden">Terpilih &check;</span>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Input Keluhan Utama -->
                    <div class="mb-6">
                        <label for="complaint" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Keluhan / Gejala Utama yang Dirasakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="complaint" name="complaint" rows="3" required
                            class="block w-full px-4 py-3 text-sm rounded-2xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 transition-colors shadow-2xs placeholder:text-slate-400"
                            placeholder="Deskripsikan keluhan Anda secara singkat, misalnya: Demam sejak kemarin, sakit tenggorokan, dan pusing...">{{ old('complaint') }}</textarea>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Keluhan ini akan dibaca oleh Suster dan Dokter untuk menyiapkan penanganan medis terbaik.
                        </p>
                    </div>

                    <!-- Tombol Ambil Tiket -->
                    <div class="flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-unpam-700 via-unpam-600 to-teal-600 hover:from-unpam-800 hover:to-teal-700 shadow-lg shadow-unpam-600/30 transition-all active:scale-[0.99]">
                            <i data-lucide="ticket" class="w-4 h-4"></i>
                            Ambil Nomor Tiket Antrian Sekarang
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- RINGKASAN RIWAYAT MEDIS TERAKHIR           -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-lg shadow-slate-200/30 p-6 sm:p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Riwayat Medis Pasien</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Catatan diagnosa dan resep dari kunjungan klinik Anda terdahulu</p>
                </div>
                <a href="{{ route('pasien.riwayat-medis') }}" class="text-xs font-bold text-unpam-700 hover:text-unpam-800 hover:underline flex items-center gap-1">
                    Lihat Semua Riwayat
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            @if($recentMedicalRecords->isEmpty())
                <div class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="folder-clock" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Belum Ada Riwayat Medis</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Setelah Anda menyelesaikan pemeriksaan dengan dokter dan mengambil obat di apotek, riwayat medis Anda akan tercatat otomatis di sini.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3 px-4">Tanggal & Kode</th>
                                <th class="py-3 px-4">Poliklinik & Dokter</th>
                                <th class="py-3 px-4">Penyakit / Diagnosa</th>
                                <th class="py-3 px-4">Catatan / Anjuran Dokter</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($recentMedicalRecords as $record)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="font-bold text-slate-900 font-mono block">{{ $record->queue_code }}</span>
                                        <span class="text-slate-500 text-[11px]">{{ $record->completed_at?->format('d M Y, H:i') ?? $record->queue_date->format('d M Y') }}</span>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="font-bold text-unpam-800 block">{{ $record->polyclinic->name }}</span>
                                        <span class="text-slate-500 text-[11px]">{{ $record->doctor?->name ?? 'Dokter Klinik' }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="font-bold text-slate-800 block">{{ $record->disease ?? '-' }}</span>
                                        <span class="text-slate-500 text-[11px] line-clamp-1">{{ $record->diagnosis ?? '-' }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="text-slate-600 text-[11px] line-clamp-2 italic">{{ $record->doctor_notes ?? 'Pemeriksaan selesai, obat telah diserahkan di apotek.' }}</span>
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('pasien.riwayat-medis.show', $record) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-unpam-50 hover:bg-unpam-100 text-unpam-700 font-semibold border border-unpam-200 transition-colors">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            Detail Resume
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</div>

@push('scripts')
@if($activeQueue)
<script>
    // Live Auto Polling untuk status antrian pasien
    const queueId = {{ $activeQueue->id }};
    let currentStep = {{ $activeQueue->status_step }};

    function pollQueueStatus() {
        fetch(`/pasien/antrian/status/${queueId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) return;

                document.getElementById('liveTimestamp').innerText = 'Terakhir dicek: ' + new Date().toLocaleTimeString('id-ID');
                document.getElementById('instructionDisplay').innerText = data.instruction;
                document.getElementById('queueAheadDisplay').innerText = data.waiting_ahead + ' Pasien';
                
                if (data.doctor_name) {
                    document.getElementById('doctorNameDisplay').innerHTML = `<i data-lucide="stethoscope" class="w-4 h-4 text-slate-400"></i> ${data.doctor_name}`;
                }

                // Cek jika status berubah
                if (data.status_step !== currentStep) {
                    // Bunyikan pemberitahuan suara jika status bergeser ke panggilan dokter atau apotek
                    if (data.status_step === 2 || data.status_step === 5) {
                        if (window.callQueueVoice) {
                            window.callQueueVoice(data.queue_code, data.polyclinic_name);
                        }
                    }

                    // Reload halaman jika status selesai atau berubah signifikan untuk memperbarui stepper
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }

                lucide.createIcons();
            })
            .catch(err => console.log('Polling queue status error:', err));
    }

    // Interval polling setiap 6 detik
    setInterval(pollQueueStatus, 6000);
</script>
@endif
@endpush
@endsection
