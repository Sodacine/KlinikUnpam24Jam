@extends('layouts.app')

@section('title', 'Detail Rekam Medis: ' . $queue->queue_code . ' - Suster Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Navigasi -->
        <div class="flex items-center justify-between gap-4 mb-6 print:hidden">
            <a href="{{ route('suster.riwayat-medis') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-sky-700 transition-colors">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                Kembali ke Arsip Riwayat Medis
            </a>

            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-sm transition-all">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    Cetak Rekam Medis
                </button>
            </div>
        </div>

        <!-- Dokumen Rekam Medis Lengkap -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-200/50 p-8 sm:p-12 print:border-none print:shadow-none print:p-0">
            
            <!-- Kop Surat Resmi Klinik Unpam 24 Jam -->
            <div class="flex items-center justify-between pb-6 border-b-2 border-slate-800 mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-unpam-700 text-white flex items-center justify-center font-black text-2xl print:border print:border-black">
                        <i data-lucide="cross" class="w-8 h-8 stroke-[2.5]"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">
                            Klinik Unpam 24 Jam
                        </h2>
                        <p class="text-xs text-slate-600 font-medium">
                            Pusat Pelayanan Kesehatan & Kedokteran Terpadu Universitas Pamulang
                        </p>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Jl. Surya Kencana No. 1, Pamulang Barat, Tangerang Selatan, Banten | Telp: (021) 741-2566
                        </p>
                    </div>
                </div>

                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-slate-100 text-slate-800 text-xs font-bold rounded-md font-mono border border-slate-300">
                        {{ $queue->queue_code }}
                    </span>
                    <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-wider font-semibold">
                        Arsip Staf Keperawatan
                    </p>
                </div>
            </div>

            <!-- Judul Dokumen -->
            <div class="text-center mb-8">
                <h1 class="text-lg font-black text-slate-900 uppercase tracking-wider underline">
                    Lembar Rekam Medis Elektronik Pasien
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Tanggal Pelayanan: {{ $queue->completed_at?->translatedFormat('d F Y') ?? $queue->queue_date->translatedFormat('d F Y') }}
                </p>
            </div>

            <!-- I. Identitas Pasien -->
            <div class="mb-8">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3 pb-1 border-b border-slate-100">
                    I. Identitas Pasien
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block">Nama Lengkap:</span>
                        <strong class="text-slate-900 text-sm">{{ $queue->patient->name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">NIK:</span>
                        <span class="text-slate-800 font-mono">{{ $queue->patient->nik ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Jenis Kelamin:</span>
                        <span class="text-slate-800">{{ $queue->patient->gender === 'L' ? 'Laki-laki' : ($queue->patient->gender === 'P' ? 'Perempuan' : '-') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">No. Telepon / WhatsApp:</span>
                        <span class="text-slate-800 font-mono">{{ $queue->patient->phone ?? '-' }}</span>
                    </div>
                </div>
                @if($queue->patient->address)
                    <div class="mt-2 text-xs">
                        <span class="text-slate-400 block">Alamat Pasien:</span>
                        <span class="text-slate-700">{{ $queue->patient->address }}</span>
                    </div>
                @endif
            </div>

            <!-- II. Kunjungan Poliklinik & Keluhan -->
            <div class="mb-8">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3 pb-1 border-b border-slate-100">
                    II. Kunjungan Poliklinik & Keluhan Pasien
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mb-3">
                    <div>
                        <span class="text-slate-400 block">Poliklinik Tujuan:</span>
                        <strong class="text-slate-900 text-sm">{{ $queue->polyclinic->name }}</strong>
                        <span class="text-slate-500 block text-[11px]">{{ $queue->polyclinic->floor_room }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Dokter Pemeriksa:</span>
                        <strong class="text-slate-900 text-sm">{{ $queue->doctor?->name ?? 'Dokter Jaga Klinik' }}</strong>
                    </div>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
                    <span class="text-slate-400 block text-[11px] uppercase font-bold mb-0.5">Keluhan Awal Pasien Saat Pendaftaran:</span>
                    <p class="text-slate-800 italic">"{{ $queue->complaint }}"</p>
                </div>
            </div>

            <!-- III. Penilaian Medis, Diagnosa & Penyakit -->
            <div class="mb-8">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3 pb-1 border-b border-slate-100">
                    III. Penilaian Medis & Diagnosa Dokter
                </h3>
                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-slate-400 block font-semibold mb-0.5">Penyakit Teridentifikasi (ICD/Klinis):</span>
                        <div class="p-3 bg-sky-50/60 rounded-xl border border-sky-200 text-sm font-bold text-slate-900">
                            {{ $queue->disease }}
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold mb-0.5">Diagnosa Klinis & Hasil Pemeriksaan Fisik:</span>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-slate-800 whitespace-pre-line leading-relaxed">
                            {{ $queue->diagnosis }}
                        </div>
                    </div>
                    @if($queue->doctor_notes)
                        <div>
                            <span class="text-slate-400 block font-semibold mb-0.5">Catatan & Anjuran Dokter:</span>
                            <div class="p-3 bg-amber-50/50 rounded-xl border border-amber-200 text-slate-700 italic">
                                {{ $queue->doctor_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- IV. Terapi Medis & Resep Obat (Akses Tenaga Medis Suster) -->
            <div class="mb-8">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3 pb-1 border-b border-slate-100">
                    IV. Terapi Obat yang Diberikan (Catatan Farmasi)
                </h3>
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs font-mono text-slate-800 whitespace-pre-line leading-relaxed mb-3">
                    {{ $queue->prescription ?? 'Tidak ada resep obat.' }}
                </div>
                @if($queue->pharmacist_notes)
                    <p class="text-xs text-teal-800 bg-teal-50 p-2.5 rounded-xl border border-teal-200">
                        <strong>Catatan Apoteker:</strong> {{ $queue->pharmacist_notes }}
                    </p>
                @endif
            </div>

            <!-- V. Timeline Audit Siklus Pelayanan -->
            <div class="mb-12">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3 pb-1 border-b border-slate-100">
                    V. Riwayat Jejak Waktu Pelayanan (Audit Trail)
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-center text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">1. Ambil Tiket</span>
                        <strong class="text-slate-800 block mt-1">{{ $queue->created_at->format('H:i') }} WIB</strong>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">2. Verifikasi Suster</span>
                        <strong class="text-slate-800 block mt-1">{{ $queue->nurse_verified_at?->format('H:i') ?? '-' }} WIB</strong>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">3. Periksa Dokter</span>
                        <strong class="text-slate-800 block mt-1">{{ $queue->doctor_started_at?->format('H:i') ?? '-' }} WIB</strong>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">4. Verifikasi Resep</span>
                        <strong class="text-slate-800 block mt-1">{{ $queue->pharmacist_verified_at?->format('H:i') ?? '-' }} WIB</strong>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 col-span-2 sm:col-span-1">
                        <span class="text-[10px] text-emerald-700 block uppercase font-bold">5. Selesai Obat</span>
                        <strong class="text-emerald-900 block mt-1">{{ $queue->completed_at?->format('H:i') ?? '-' }} WIB</strong>
                    </div>
                </div>
            </div>

            <!-- Tanda Tangan & Pengesahan -->
            <div class="grid grid-cols-2 gap-8 text-center text-xs pt-8 border-t border-slate-200">
                <div>
                    <span class="text-slate-400 block mb-1">Diverifikasi oleh Suster Jaga</span>
                    <div class="h-16 flex items-center justify-center text-slate-300 font-serif italic text-sm">
                        (Tanda Tangan Elektronik)
                    </div>
                    <strong class="text-slate-800 block">Staf Keperawatan Klinik Unpam</strong>
                    <span class="text-slate-400 text-[10px]">Waktu: {{ $queue->nurse_verified_at?->format('H:i') ?? '-' }} WIB</span>
                </div>

                <div>
                    <span class="text-slate-400 block mb-1">Dokter Pemeriksa</span>
                    <div class="h-16 flex items-center justify-center text-slate-300 font-serif italic text-sm">
                        (Tanda Tangan Elektronik)
                    </div>
                    <strong class="text-slate-900 block underline">{{ $queue->doctor?->name ?? 'Dokter Pemeriksa' }}</strong>
                    <span class="text-slate-400 text-[10px]">SIP: Dokter Resmi Klinik Unpam</span>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
