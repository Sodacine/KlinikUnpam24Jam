@extends('layouts.app')

@section('title', 'Dashboard Apoteker - Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Banner Apoteker -->
        <div class="bg-gradient-to-r from-teal-900 via-unpam-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-teal-950/10 mb-8 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/20 text-teal-300 border border-teal-400/30 mb-3">
                        <i data-lucide="pill" class="w-3.5 h-3.5"></i>
                        Stasiun Farmasi & Loket Obat
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Instalasi Farmasi Klinik Unpam 24 Jam
                    </h1>
                    <p class="text-teal-200/80 text-sm mt-1">
                        Verifikasi resep obat dokter, instruksikan pasien menuju loket, dan serahkan obat untuk menyelesaikan siklus rekam medis.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="px-4 py-2 rounded-2xl bg-white/10 text-xs font-bold backdrop-blur border border-white/10">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                    <a href="{{ route('queue.monitor') }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-800 bg-white hover:bg-slate-100 shadow-sm transition-all">
                        <i data-lucide="tv" class="w-3.5 h-3.5 text-unpam-600"></i>
                        Layar Display
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Statistik Farmasi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="p-5 rounded-3xl bg-purple-50/70 border-2 border-purple-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-700 block">Resep Menunggu Verifikasi</span>
                    <div class="text-3xl font-black text-purple-950 mt-1">{{ $stats['pending'] }}</div>
                    <span class="text-[11px] text-purple-600 font-medium">Perlu diracik/disiapkan</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-purple-200/60 text-purple-700 flex items-center justify-center">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="p-5 rounded-3xl bg-teal-50/70 border-2 border-teal-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-700 block">Obat Siap di Loket</span>
                    <div class="text-3xl font-black text-teal-950 mt-1">{{ $stats['ready'] }}</div>
                    <span class="text-[11px] text-teal-600 font-medium">Pasien menuju loket apotek</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-200/60 text-teal-700 flex items-center justify-center">
                    <i data-lucide="bell-ring" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="p-5 rounded-3xl bg-rose-50/70 border-2 border-rose-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700 block">Dilewati / Tak Hadir</span>
                    <div class="text-3xl font-black text-rose-950 mt-1">{{ $stats['skipped'] }}</div>
                    <span class="text-[11px] text-rose-600 font-medium">Hangus dalam 24 jam</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-200/60 text-rose-700 flex items-center justify-center">
                    <i data-lucide="user-x" class="w-6 h-6"></i>
                </div>
            </div>

            <div class="p-5 rounded-3xl bg-emerald-50/70 border-2 border-emerald-200 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 block">Obat Diserahkan Hari Ini</span>
                    <div class="text-3xl font-black text-emerald-950 mt-1">{{ $stats['completed'] }}</div>
                    <span class="text-[11px] text-emerald-600 font-medium">Siklus antrian selesai & archive</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-200/60 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-8">
            
            <!-- =================================================== -->
            <!-- KOLOM 1: RESEP MENUNGGU VERIFIKASI (menunggu_apotek) -->
            <!-- =================================================== -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-lg shadow-slate-200/30 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-purple-50/30">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500 animate-ping"></span>
                            <h2 class="text-base font-bold text-slate-900">1. Resep Masuk dari Dokter</h2>
                        </div>
                        <span class="text-xs font-bold text-purple-700 bg-purple-100 px-3 py-1 rounded-full border border-purple-200">
                            {{ $pendingPrescriptions->count() }} Resep Menunggu
                        </span>
                    </div>

                    @if($pendingPrescriptions->isEmpty())
                        <div class="py-16 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3 border border-slate-100">
                                <i data-lucide="inbox" class="w-7 h-7 text-purple-400"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-700">Tidak Ada Resep Menunggu</h3>
                            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                Saat dokter menyelesaikan pemeriksaan pasien, resep obat akan otomatis muncul di panel ini.
                            </p>
                        </div>
                    @else
                        <div class="p-6 space-y-6 divide-y divide-slate-100">
                            @foreach($pendingPrescriptions as $queue)
                                <div class="pt-6 first:pt-0">
                                    
                                    <!-- Header Resep -->
                                    <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                                        <div class="flex items-center gap-3">
                                            <span class="px-3.5 py-1.5 rounded-xl bg-purple-100 text-purple-900 font-mono font-black text-base border border-purple-200">
                                                {{ $queue->queue_code }}
                                            </span>
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-900">{{ $queue->patient->name }}</h3>
                                                <span class="text-[11px] text-slate-500">
                                                    Poli: <strong class="text-unpam-800">{{ $queue->polyclinic->name }}</strong> &bull; Dokter: {{ $queue->doctor?->name }}
                                                </span>
                                            </div>
                                        </div>

                                        <span class="text-xs text-slate-400 font-mono">
                                            Selesai periksa: {{ $queue->doctor_finished_at?->format('H:i') }} WIB
                                        </span>
                                    </div>

                                    <!-- Diagnosa Singkat -->
                                    <div class="mb-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-100">
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Diagnosa Dokter:</span>
                                        <strong class="text-slate-800">{{ $queue->disease }}</strong> &mdash; <span class="text-slate-600">{{ $queue->diagnosis }}</span>
                                    </div>

                                    <!-- Kotak Resep Obat -->
                                    <div class="mb-4">
                                        <span class="text-xs font-bold uppercase tracking-wider text-purple-900 block mb-1.5 flex items-center gap-1.5">
                                            <i data-lucide="pill" class="w-3.5 h-3.5 text-purple-600"></i>
                                            Rincian Resep Obat yang Ditulis Dokter:
                                        </span>
                                        <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-200 text-xs font-mono text-slate-900 whitespace-pre-line leading-relaxed shadow-2xs">
                                            {{ $queue->prescription }}
                                        </div>
                                    </div>

                                    <!-- Status Jika Sedang Menunggu Revisi Dokter -->
                                    @if($queue->needs_prescription_revision)
                                        <div class="mb-4 p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-xs">
                                            <div class="flex items-center gap-2 font-bold text-amber-900 mb-1">
                                                <i data-lucide="clock-alert" class="w-4 h-4 text-amber-600"></i>
                                                Menunggu Peresepan Ulang dari {{ $queue->doctor?->name ?? 'Dokter' }}
                                            </div>
                                            <p class="text-amber-800 text-[11px] leading-relaxed">
                                                <strong>Alasan / Catatan Apoteker:</strong> "{{ $queue->prescription_replacement_reason }}"
                                            </p>
                                            <span class="text-[10px] text-amber-600 font-mono block mt-1">
                                                Diajukan pada: {{ $queue->prescription_replacement_requested_at?->format('H:i') }} WIB
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Form Verifikasi Apoteker -->
                                    <form action="{{ route('apoteker.antrian.verifikasi-resep', $queue) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1">
                                                Catatan Apoteker / Instruksi Penyerahan (Opsional)
                                            </label>
                                            <input type="text" name="pharmacist_notes" value="Obat telah lengkap diracik sesuai resep dokter. Silakan menuju Loket Apotek."
                                                class="block w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 shadow-2xs text-slate-700">
                                        </div>

                                        <div class="flex items-center justify-between pt-1 gap-2 flex-wrap">
                                            <div class="flex items-center gap-2">
                                                <button type="button" onclick="callQueueVoice('{{ $queue->queue_code }}', 'Loket Apotek Farmasi')" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors">
                                                    <i data-lucide="volume-2" class="w-3.5 h-3.5"></i>
                                                    Panggil Pasien
                                                </button>

                                                <!-- Tombol Ajukan Penggantian Obat -->
                                                <button type="button" onclick="openReplacementModal('{{ $queue->id }}', '{{ $queue->queue_code }}', '{{ $queue->patient->name }}', '{{ addslashes($queue->prescription) }}')" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-bold text-amber-800 bg-amber-100/70 hover:bg-amber-200 border border-amber-300 transition-colors">
                                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-amber-700"></i>
                                                    {{ $queue->needs_prescription_revision ? 'Update Pengajuan Obat' : 'Ajukan Ganti Obat' }}
                                                </button>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button type="button" onclick="document.getElementById('formSkipResep{{ $queue->id }}').submit()"
                                                        title="Lewati jika pasien tak kunjung hadir ke apotek (Hangus dalam 24 Jam)"
                                                        class="inline-flex items-center gap-1 px-3 py-2.5 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                                                    <i data-lucide="skip-forward" class="w-3.5 h-3.5"></i>
                                                    Lewati
                                                </button>

                                                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 shadow-md shadow-teal-600/20 transition-all active:scale-95">
                                                    <i data-lucide="check-check" class="w-4 h-4"></i>
                                                    Verifikasi Resep &amp; Panggil ke Loket Apotek &raquo;
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Form Lewati untuk Resep yang Tak Kunjung Diambil -->
                                    <form id="formSkipResep{{ $queue->id }}" action="{{ route('apoteker.antrian.skip', $queue) }}" method="POST" class="hidden"
                                          data-confirm="Lewati antrian {{ $queue->queue_code }} ({{ $queue->patient->name }}) karena tak kunjung hadir ke Apotek? Antrian dapat dipanggil ulang dalam 24 jam sebelum hangus otomatis."
                                          data-confirm-type="warning"
                                          data-confirm-title="Lewati Pasien Belum Hadir"
                                          data-confirm-ok="Ya, Lewati">
                                        @csrf
                                    </form>

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- =================================================== -->
            <!-- KOLOM 2: SIAP DIAMBIL DI LOKET (siap_diambil)        -->
            <!-- =================================================== -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl border-2 border-teal-500/40 shadow-xl shadow-teal-500/10 overflow-hidden sticky top-24">
                    <div class="px-6 py-4 border-b border-teal-100 flex items-center justify-between bg-teal-50/50">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-pulse"></span>
                            <h2 class="text-base font-bold text-slate-900">2. Siap Diambil di Loket</h2>
                        </div>
                        <span class="text-xs font-bold text-teal-800 bg-teal-100 px-3 py-1 rounded-full border border-teal-200">
                            {{ $readyToPickup->count() }} Pasien
                        </span>
                    </div>

                    @if($readyToPickup->isEmpty())
                        <div class="py-16 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3 border border-slate-100">
                                <i data-lucide="check" class="w-7 h-7 text-teal-500"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-700">Tidak Ada Antrian di Loket</h3>
                            <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">
                                Resep yang telah diverifikasi akan berstatus "Siap Diambil" dan muncul di loket ini.
                            </p>
                        </div>
                    @else
                        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                            @foreach($readyToPickup as $ready)
                                <div class="p-5 rounded-2xl bg-gradient-to-br from-teal-50/50 via-white to-slate-50 border border-teal-200 shadow-sm">
                                    
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="px-3 py-1 rounded-xl bg-teal-600 text-white font-mono font-black text-sm shadow-xs">
                                                {{ $ready->queue_code }}
                                            </span>
                                            <span class="font-bold text-slate-900 text-sm">{{ $ready->patient->name }}</span>
                                        </div>

                                        <button type="button" onclick="callQueueVoice('{{ $ready->queue_code }}', 'Loket Apotek')" title="Panggil ke Loket" class="p-2 rounded-xl text-teal-700 bg-teal-100 hover:bg-teal-200 transition-colors">
                                            <i data-lucide="volume-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>

                                    <div class="text-[11px] text-slate-600 mb-3 space-y-1">
                                        <div>Poli Asal: <strong class="text-slate-800">{{ $ready->polyclinic->name }}</strong></div>
                                        <div class="font-mono text-slate-700 bg-white p-2 rounded-lg border border-slate-200 line-clamp-2">
                                            {{ $ready->prescription }}
                                        </div>
                                    </div>

                                     <form id="formSerahkan{{ $ready->id }}" action="{{ route('apoteker.antrian.serahkan-obat', $ready) }}" method="POST">
                                        @csrf
                                        <button type="button"
                                                data-btn-confirm="Konfirmasi bahwa obat telah diserahkan kepada {{ $ready->patient->name }}. Siklus antrian akan selesai dan masuk ke Riwayat Medis."
                                                data-confirm-title="Serahkan Obat"
                                                data-confirm-type="success"
                                                data-confirm-ok="Ya, Serahkan Sekarang"
                                                data-form-id="formSerahkan{{ $ready->id }}"
                                                class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all active:scale-95">
                                            <i data-lucide="badge-check" class="w-4 h-4"></i>
                                            Serahkan Obat &amp; Selesaikan Siklus Antrian &#10003;
                                        </button>
                                    </form>

                                    <!-- Tombol Lewati jika Pasien Tak Kunjung Datang Mengambil Obat -->
                                    <div class="mt-2.5 pt-2.5 border-t border-teal-100">
                                        <form action="{{ route('apoteker.antrian.skip', $ready) }}" method="POST"
                                              data-confirm="Lewati antrian {{ $ready->queue_code }} ({{ $ready->patient->name }}) karena tak kunjung mengambil obat? Antrian dapat dipanggil ulang dalam 24 jam sebelum hangus otomatis."
                                              data-confirm-type="warning"
                                              data-confirm-title="Lewati Pasien Belum Ambil Obat"
                                              data-confirm-ok="Ya, Lewati">
                                            @csrf
                                            <button type="submit" title="Lewati jika pasien tak kunjung datang ke loket (Hangus dalam 24 Jam)"
                                                    class="w-full flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                                                <i data-lucide="skip-forward" class="w-3.5 h-3.5"></i>
                                                Lewati – Pasien Tak Kunjung Datang
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- BAGIAN: ANTRIAN OBAT DILEWATI / BELUM DIAMBIL PASIEN     -->
        <!-- ======================================================== -->
        @if($skippedPrescriptions->isNotEmpty())
            <div class="bg-white rounded-3xl border border-rose-200/80 shadow-md shadow-rose-200/20 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-rose-100 bg-rose-50/50 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
                            <i data-lucide="user-x" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Antrian Obat Dilewati (Tak Kunjung Diambil)</h2>
                            <p class="text-xs text-slate-500">Pasien yang belum datang ke loket apotek. Antrian otomatis hangus dalam 24 jam jika tidak kunjung diambil.</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-rose-800 bg-rose-100 px-3 py-1 rounded-full border border-rose-200">
                        {{ $skippedPrescriptions->count() }} Antrian Dilewati
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-rose-50/30 border-b border-rose-100 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4 text-center">No</th>
                                <th class="py-3.5 px-4">Kode Tiket</th>
                                <th class="py-3.5 px-4">Pasien</th>
                                <th class="py-3.5 px-4">Poli & Dokter</th>
                                <th class="py-3.5 px-4">Resep Obat</th>
                                <th class="py-3.5 px-4">Waktu Dilewati</th>
                                <th class="py-3.5 px-4">Batas Waktu Hangus</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($skippedPrescriptions as $sq)
                                <tr class="hover:bg-rose-50/30 transition-colors">
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                        #{{ $sq->queue_number }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="font-mono text-sm font-bold text-slate-900">{{ $sq->queue_code }}</span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $sq->patient->name }}</div>
                                        <span class="text-slate-400 text-[11px]">
                                            {{ $sq->patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }} &bull; NIK: {{ $sq->patient->nik ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="font-semibold text-slate-800 block">{{ $sq->polyclinic->name }}</span>
                                        <span class="text-slate-400 text-[11px]">{{ $sq->doctor?->name }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700 max-w-xs whitespace-pre-line line-clamp-2">
                                        {{ $sq->prescription }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap text-slate-500">
                                        {{ $sq->skipped_at?->format('H:i, d/m') ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($sq->expired_at && now()->isBefore($sq->expired_at))
                                            <span class="inline-flex items-center gap-1 font-semibold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                Hangus {{ $sq->expired_at->diffForHumans() }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">Kadaluarsa</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <form action="{{ route('apoteker.antrian.recall', $sq) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-amber-500 hover:bg-amber-600 shadow-sm transition-all active:scale-95">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                Panggil Ulang Pasien
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- BAGIAN 3: OBAT SELESAI DISERAHKAN HARI INI -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-md shadow-slate-200/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Riwayat Penyerahan Obat Hari Ini (Siklus Selesai)</h2>
                    <p class="text-xs text-slate-500">Pasien yang telah mengambil obat dan data resmi tersimpan di Riwayat Medis</p>
                </div>
                <span class="text-xs font-bold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-200">
                    Selesai: {{ $completedToday->count() }} Pasien
                </span>
            </div>

            @if($completedToday->isEmpty())
                <div class="py-10 text-center text-xs text-slate-400">
                    Belum ada penyerahan obat yang diselesaikan hari ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3 px-4">Kode & Jam Selesai</th>
                                <th class="py-3 px-4">Pasien</th>
                                <th class="py-3 px-4">Poli & Dokter</th>
                                <th class="py-3 px-4">Diagnosa Penyakit</th>
                                <th class="py-3 px-4">Resep Obat yang Diberikan</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($completedToday as $c)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="font-mono font-bold text-slate-900 block">{{ $c->queue_code }}</span>
                                        <span class="text-slate-400 text-[11px]">{{ $c->completed_at?->format('H:i') }} WIB</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">
                                        {{ $c->patient->name }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="font-semibold text-slate-800 block">{{ $c->polyclinic->name }}</span>
                                        <span class="text-slate-400 text-[11px]">{{ $c->doctor?->name }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-900">
                                        {{ $c->disease }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700 max-w-xs whitespace-pre-line line-clamp-2">
                                        {{ $c->prescription }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            &check; Resmi Riwayat Medis
                                        </span>
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

<!-- ========================================== -->
<!-- MODAL AJUKAN PENGGANTIAN RESEP OBAT       -->
<!-- ========================================== -->
<div id="modalPenggantianObat" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-8">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-amber-100 rounded-xl text-amber-700">
                    <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pengajuan Penggantian Resep Obat</h3>
                    <p class="text-xs text-slate-500">Kirim permintaan resep alternatif ke Dokter</p>
                </div>
            </div>
            <button type="button" onclick="closeReplacementModal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="replacementForm" method="POST" action="">
            @csrf

            <!-- Info Pasien & Resep Saat Ini -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 mb-4 text-xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-slate-400 uppercase text-[10px]">Kode Antrian</span>
                    <span id="replacementQueueCode" class="font-mono font-black text-purple-700 bg-purple-50 px-2 py-0.5 rounded-lg border border-purple-200"></span>
                </div>
                <div id="replacementPatientName" class="font-bold text-sm text-slate-900 mb-2"></div>
                
                <span class="font-bold text-slate-500 uppercase text-[10px] block mb-1">Resep Saat Ini:</span>
                <div id="replacementCurrentPrescription" class="font-mono text-slate-700 bg-white p-2.5 rounded-xl border border-slate-200 whitespace-pre-line max-h-28 overflow-y-auto"></div>
            </div>

            <!-- Input Alasan / Rekomendasi Penggantian -->
            <div class="mb-5">
                <label for="prescription_replacement_reason" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Alasan & Rekomendasi Obat Pengganti untuk Dokter <span class="text-rose-500">*</span>
                </label>
                <textarea id="prescription_replacement_reason" name="prescription_replacement_reason" rows="3" required
                    class="block w-full px-3.5 py-2.5 text-xs rounded-xl border border-amber-300 bg-amber-50/20 focus:ring-2 focus:ring-amber-500 shadow-2xs font-medium text-slate-900 placeholder:text-slate-400"
                    placeholder="Contoh: Stok Amoxicillin 500mg di apotek sedang kosong/habis. Disarankan ganti dengan Cefadroxil 500mg atau Erythromycin 500mg."></textarea>
                <p class="text-[11px] text-slate-400 mt-1">
                    Pesan ini akan langsung muncul di Dashboard Dokter bersangkutan agar dokter dapat meresepkan ulang.
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeReplacementModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-md shadow-amber-600/20 transition-all active:scale-95">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Pengajuan ke Dokter
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openReplacementModal(queueId, queueCode, patientName, currentPrescription) {
        const form = document.getElementById('replacementForm');
        form.action = `/apoteker/antrian/${queueId}/ajukan-penggantian-obat`;

        document.getElementById('replacementQueueCode').innerText = queueCode;
        document.getElementById('replacementPatientName').innerText = patientName;
        document.getElementById('replacementCurrentPrescription').innerText = currentPrescription;

        document.getElementById('modalPenggantianObat').classList.remove('hidden');
    }

    function closeReplacementModal() {
        document.getElementById('modalPenggantianObat').classList.add('hidden');
    }
</script>
@endsection
