@extends('layouts.app')

@section('title', 'Dashboard Dokter - Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Banner Dokter -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10 mb-8 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 mb-3">
                        <i data-lucide="stethoscope" class="w-3.5 h-3.5"></i>
                        Stasiun Dokter Poliklinik
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        {{ $doctor->name }}
                    </h1>
                    <p class="text-slate-300 text-sm mt-1">
                        Poliklinik Bertugas: <strong class="text-white">{{ $doctor->polyclinic?->name ?? 'Dokter Umum Klinik' }}</strong> 
                        &bull; {{ $doctor->polyclinic?->floor_room ?? 'Ruang Dokter' }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="px-4 py-2 rounded-2xl bg-white/10 text-xs font-bold backdrop-blur border border-white/10">
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- PASIEN SEDANG DI DALAM RUANGAN            -->
        <!-- ========================================== -->
        @if($currentPatient)
            <div class="bg-gradient-to-br from-indigo-50 via-white to-blue-50 border-2 border-indigo-500 rounded-3xl p-6 sm:p-8 shadow-xl shadow-indigo-500/10 mb-8 relative">
                <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-indigo-200/80 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-600"></span>
                        </span>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-indigo-900 bg-indigo-100 px-3 py-1 rounded-full border border-indigo-300">
                            Sesi Pemeriksaan Aktif Sedang Berlangsung
                        </span>
                    </div>
                    <span class="text-xs text-indigo-600 font-mono">
                        Mulai periksa: {{ $currentPatient->doctor_started_at?->format('H:i') }} WIB
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nomor Antrian & Pasien</span>
                        <div class="text-4xl font-black font-mono text-indigo-950 mt-1 mb-1">
                            {{ $currentPatient->queue_code }}
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $currentPatient->patient->name }}</h3>
                        <p class="text-xs text-slate-500">
                            {{ $currentPatient->patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }} &bull; NIK: {{ $currentPatient->patient->nik ?? '-' }}
                        </p>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-indigo-100 shadow-2xs">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">Keluhan Utama</span>
                        <p class="text-xs text-slate-800 italic leading-relaxed">
                            "{{ $currentPatient->complaint }}"
                        </p>
                    </div>

                    <div class="text-right">
                        <a href="{{ route('dokter.periksa', $currentPatient) }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 transition-all active:scale-95">
                            <i data-lucide="clipboard-edit" class="w-4 h-4"></i>
                            Input Diagnosa & Resep Obat &raquo;
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- ================================================== -->
        <!-- PERMINTAAN PERGANTIAN RESEP DARI APOTEKER         -->
        <!-- ================================================== -->
        @if(isset($revisionRequests) && $revisionRequests->isNotEmpty())
            <div class="bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-amber-500/10 border-2 border-amber-400 rounded-3xl p-6 sm:p-7 shadow-lg shadow-amber-500/10 mb-8 relative">
                <div class="flex items-center justify-between pb-4 border-b border-amber-200/80 mb-5">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 bg-amber-500 text-white rounded-xl shadow-xs animate-bounce">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-amber-950">
                                Permintaan Penggantian Resep Obat dari Apoteker
                            </h2>
                            <p class="text-xs text-amber-800">
                                Stok obat di farmasi habis atau membutuhkan alternatif. Harap resepkan ulang obat pasien berikut:
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-bold bg-amber-200 text-amber-900 px-3 py-1 rounded-full border border-amber-300">
                        {{ $revisionRequests->count() }} Pengajuan
                    </span>
                </div>

                <div class="space-y-4">
                    @foreach($revisionRequests as $req)
                        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-amber-200 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="space-y-2 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-black text-sm text-indigo-900 bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-200">
                                        {{ $req->queue_code }}
                                    </span>
                                    <h3 class="font-bold text-slate-900 text-sm">{{ $req->patient->name }}</h3>
                                    <span class="text-slate-400 text-xs">&bull; Diagnosa: <strong>{{ $req->disease }}</strong></span>
                                </div>

                                <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-200/80 text-xs text-amber-900">
                                    <span class="font-bold text-amber-800 block text-[11px] mb-0.5 flex items-center gap-1">
                                        <i data-lucide="message-square-warning" class="w-3.5 h-3.5 text-amber-600"></i>
                                        Alasan & Saran dari Apoteker:
                                    </span>
                                    "{{ $req->prescription_replacement_reason }}"
                                </div>

                                <div class="text-[11px] text-slate-500">
                                    <span class="font-semibold">Resep Sebelumnya:</span>
                                    <span class="font-mono text-slate-700">{{ Str::limit($req->prescription, 90) }}</span>
                                </div>
                            </div>

                            <div class="flex md:flex-col items-end justify-between gap-2 shrink-0">
                                <button type="button" onclick="openResubmitModal('{{ $req->id }}', '{{ $req->queue_code }}', '{{ $req->patient->name }}', '{{ addslashes($req->prescription_replacement_reason) }}', '{{ addslashes($req->prescription) }}')" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-md shadow-amber-600/20 transition-all active:scale-95">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    Resepkan Ulang Obat &raquo;
                                </button>
                                <span class="text-[10px] text-slate-400 font-mono">
                                    {{ $req->prescription_replacement_requested_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- DAFTAR PASIEN MENUNGGU DI DEPAN RUANGAN   -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-lg shadow-slate-200/30 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Pasien Menunggu di Depan Ruangan</h2>
                    <p class="text-xs text-slate-500">Pasien yang telah diverifikasi oleh Suster dan siap dipanggil masuk</p>
                </div>
                <span class="text-xs font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                    Menunggu: {{ $waitingPatients->count() }} Pasien
                </span>
            </div>

            @if($waitingPatients->isEmpty())
                <div class="py-14 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3 border border-slate-100">
                        <i data-lucide="check-check" class="w-7 h-7 text-emerald-500"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Tidak Ada Pasien Mengantre</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Semua pasien yang telah diverifikasi suster sudah ditangani atau belum ada antrian baru.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4 text-center w-16">No</th>
                                <th class="py-3.5 px-4">Kode Tiket</th>
                                <th class="py-3.5 px-4">Nama Pasien</th>
                                <th class="py-3.5 px-4">Keluhan Pasien</th>
                                <th class="py-3.5 px-4">Waktu Verifikasi Suster</th>
                                <th class="py-3.5 px-4 text-right">Aksi Panggilan Masuk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($waitingPatients as $wp)
                                <tr class="hover:bg-blue-50/40 transition-colors">
                                    <td class="py-4 px-4 text-center font-bold text-slate-400">
                                        #{{ $wp->queue_number }}
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="font-mono text-base font-black text-slate-900 block">{{ $wp->queue_code }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-slate-900 text-sm">{{ $wp->patient->name }}</div>
                                        <span class="text-slate-400 text-[11px]">
                                            {{ $wp->patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }} &bull; NIK: {{ $wp->patient->nik ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 max-w-sm">
                                        <p class="text-xs text-slate-700 italic bg-slate-50 p-2 rounded-xl border border-slate-100">
                                            "{{ $wp->complaint }}"
                                        </p>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap text-slate-500">
                                        {{ $wp->nurse_verified_at?->format('H:i') ?? '-' }} WIB
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" onclick="callQueueVoice('{{ $wp->queue_code }}', '{{ $wp->polyclinic->name }}')" title="Panggil Antrian" class="p-2 rounded-xl text-slate-600 hover:text-indigo-700 bg-slate-100 hover:bg-indigo-50 border border-slate-200 transition-colors">
                                                <i data-lucide="volume-2" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Tombol Pindah Poli -->
                                            <button type="button" onclick="openTransferModal('{{ $wp->id }}', '{{ $wp->queue_code }}', '{{ $wp->patient->name }}', '{{ $wp->polyclinic_id }}', '{{ $wp->polyclinic->name }}')" title="Pindahkan Pasien ke Poli Lain" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-all">
                                                <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                                                Pindah Poli
                                            </button>

                                            <form action="{{ route('dokter.antrian.masuk', $wp) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-all active:scale-95">
                                                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                                                    Panggil Masuk Ruangan &raquo;
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- PASIEN SELESAI DIPERIKSA HARI INI         -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-md shadow-slate-200/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Riwayat Pemeriksaan Dokter Hari Ini</h2>
                    <p class="text-xs text-slate-500">Pasien yang telah didiagnosa dan resepnya telah diteruskan ke Apotek</p>
                </div>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Selesai Diperiksa: {{ $handledToday->count() }}
                </span>
            </div>

            @if($handledToday->isEmpty())
                <div class="py-10 text-center text-xs text-slate-400">
                    Belum ada pemeriksaan yang diselesaikan hari ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3 px-4">Kode & Jam</th>
                                <th class="py-3 px-4">Pasien</th>
                                <th class="py-3 px-4">Penyakit Teridentifikasi</th>
                                <th class="py-3 px-4">Diagnosa Medis</th>
                                <th class="py-3 px-4">Resep Obat</th>
                                <th class="py-3 px-4 text-center">Status Alur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($handledToday as $ht)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="font-mono font-bold text-slate-900 block">{{ $ht->queue_code }}</span>
                                        <span class="text-slate-400 text-[11px]">{{ $ht->doctor_finished_at?->format('H:i') }} WIB</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-800 whitespace-nowrap">
                                        {{ $ht->patient->name }}
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-900">
                                        {{ $ht->disease }}
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 line-clamp-2 max-w-xs">
                                        {{ $ht->diagnosis }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-[11px] text-slate-700 max-w-xs whitespace-pre-line line-clamp-2">
                                        {{ $ht->prescription }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $ht->status_badge_class }}">
                                            {{ $ht->status_label }}
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
<!-- MODAL PINDAH POLIKLINIK                   -->
<!-- ========================================== -->
<div id="modalPindahPoli" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-8">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-amber-100 rounded-xl text-amber-700">
                    <i data-lucide="arrow-right-left" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pindahkan Poli Pasien</h3>
                    <p class="text-xs text-slate-500">Pasien salah pilih poli & butuh rujukan poli lain</p>
                </div>
            </div>
            <button type="button" onclick="closeTransferModal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="transferPoliForm" method="POST" action="">
            @csrf

            <!-- Info Pasien -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 mb-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kode Antrian Saat Ini</span>
                    <span id="transferQueueCode" class="font-mono font-black text-xs text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-200"></span>
                </div>
                <div id="transferPatientName" class="font-bold text-sm text-slate-900"></div>
                <div class="text-xs text-slate-500 mt-0.5">
                    Poli Asal: <strong id="transferSourcePoli" class="text-slate-700"></strong>
                </div>
            </div>

            <!-- Pilih Poli Tujuan -->
            <div class="mb-5">
                <label for="target_polyclinic_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Pilih Poliklinik Tujuan Baru <span class="text-rose-500">*</span>
                </label>
                <select id="target_polyclinic_id" name="target_polyclinic_id" required class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-amber-300 bg-amber-50/20 focus:ring-2 focus:ring-amber-500 shadow-2xs font-semibold text-slate-800">
                    <option value="">Pilih Poliklinik Tujuan</option>
                    @foreach($polyclinics as $poli)
                        <option value="{{ $poli->id }}">
                            {{ $poli->name }} ({{ $poli->floor_room }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                    Pasien otomatis mendapatkan nomor antrian baru di poli tujuan. Antrian lama di poli ini akan ditandai selesai/dipindah.
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeTransferModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-md shadow-amber-600/20 transition-all active:scale-95">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Pindahkan Pasien Sekarang
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL RESEPKAN ULANG OBAT (DOKTER)        -->
<!-- ========================================== -->
<div id="modalResepkanUlang" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-8">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-indigo-100 rounded-xl text-indigo-700">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Resepkan Ulang Obat Pasien</h3>
                    <p class="text-xs text-slate-500">Penyesuaian resep berdasarkan saran Apoteker</p>
                </div>
            </div>
            <button type="button" onclick="closeResubmitModal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="resubmitPrescriptionForm" method="POST" action="">
            @csrf

            <!-- Info Pasien -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 mb-4 text-xs">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-bold text-slate-400 uppercase text-[10px]">Nomor Antrian</span>
                    <span id="resubmitQueueCode" class="font-mono font-black text-xs text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-200"></span>
                </div>
                <div id="resubmitPatientName" class="font-bold text-sm text-slate-900 mb-2"></div>

                <!-- Alasan dari Apoteker -->
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-900 mb-2">
                    <span class="font-bold block text-[11px] mb-0.5 text-amber-800">Catatan / Saran Apoteker:</span>
                    <span id="resubmitApotekerReason" class="font-medium text-xs leading-relaxed"></span>
                </div>

                <div class="text-[11px] text-slate-500">
                    <span class="font-semibold block mb-0.5">Resep Lama Anda:</span>
                    <span id="resubmitOldPrescription" class="font-mono text-slate-700 bg-white p-2 rounded-lg border border-slate-200 block whitespace-pre-line max-h-24 overflow-y-auto"></span>
                </div>
            </div>

            <!-- Textarea Resep Obat Baru -->
            <div class="mb-4">
                <label for="resubmitPrescriptionInput" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5 flex items-center justify-between">
                    <span>Resep Obat Pengganti / Baru <span class="text-rose-500">*</span></span>
                    <span class="text-[10px] text-indigo-600 font-normal">Diteruskan ke Apoteker</span>
                </label>
                <textarea id="resubmitPrescriptionInput" name="prescription" rows="5" required
                    class="block w-full px-3.5 py-2.5 text-xs font-mono rounded-xl border-2 border-indigo-200 bg-indigo-50/20 focus:ring-2 focus:ring-indigo-500 shadow-2xs font-medium text-slate-900 leading-relaxed"></textarea>
            </div>

            <!-- Catatan Tambahan Dokter (Opsional) -->
            <div class="mb-5">
                <label for="resubmitDoctorNotes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan untuk Apoteker (Opsional)
                </label>
                <input type="text" id="resubmitDoctorNotes" name="doctor_notes"
                    class="block w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 shadow-2xs placeholder:text-slate-400"
                    placeholder="Contoh: Disetujui diganti Cefadroxil 500mg, dosis sama 3x1.">
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeResubmitModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Kirim Resep Baru ke Apotek
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openTransferModal(queueId, queueCode, patientName, sourcePoliId, sourcePoliName) {
        const form = document.getElementById('transferPoliForm');
        form.action = `/dokter/antrian/${queueId}/pindah-poli`;

        document.getElementById('transferQueueCode').innerText = queueCode;
        document.getElementById('transferPatientName').innerText = patientName;
        document.getElementById('transferSourcePoli').innerText = sourcePoliName;

        // Nonaktifkan opsi poli saat ini pada dropdown
        const select = document.getElementById('target_polyclinic_id');
        select.value = '';
        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (opt.value === sourcePoliId) {
                opt.disabled = true;
                opt.innerText = opt.innerText.replace(' (Poli Saat Ini)', '') + ' (Poli Saat Ini)';
            } else {
                opt.disabled = false;
                opt.innerText = opt.innerText.replace(' (Poli Saat Ini)', '');
            }
        }

        document.getElementById('modalPindahPoli').classList.remove('hidden');
    }

    function closeTransferModal() {
        document.getElementById('modalPindahPoli').classList.add('hidden');
    }

    function openResubmitModal(queueId, queueCode, patientName, apotekerReason, oldPrescription) {
        const form = document.getElementById('resubmitPrescriptionForm');
        form.action = `/dokter/antrian/${queueId}/resepkan-ulang`;

        document.getElementById('resubmitQueueCode').innerText = queueCode;
        document.getElementById('resubmitPatientName').innerText = patientName;
        document.getElementById('resubmitApotekerReason').innerText = apotekerReason;
        document.getElementById('resubmitOldPrescription').innerText = oldPrescription;
        document.getElementById('resubmitPrescriptionInput').value = oldPrescription;

        document.getElementById('modalResepkanUlang').classList.remove('hidden');
    }

    function closeResubmitModal() {
        document.getElementById('modalResepkanUlang').classList.add('hidden');
    }
</script>
@endsection
