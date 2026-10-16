@extends('layouts.app')

@section('title', 'Riwayat Medis Pasien - Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <a href="{{ route('pasien.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-unpam-700 mb-2 transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    Kembali ke Dashboard
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Riwayat Medis & Rekam Kesehatan
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Arsip digital diagnosa, resep obat, dan anjuran dokter dari seluruh kunjungan Anda di Klinik Unpam 24 Jam.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-unpam-50 text-unpam-800 border border-unpam-200 text-xs font-bold">
                    <i data-lucide="user-check" class="w-3.5 h-3.5 mr-1.5"></i>
                    Pasien: {{ $user->name }}
                </span>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs mb-6">
            <form action="{{ route('pasien.riwayat-medis') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="block w-full pl-10 pr-4 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 shadow-2xs placeholder:text-slate-400"
                        placeholder="Cari diagnosa, nama penyakit, keluhan, atau kode antrian...">
                </div>

                <div class="w-full sm:w-60">
                    <select name="polyclinic_id" onchange="this.form.submit()" class="block w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-unpam-500 focus:border-unpam-500 shadow-2xs">
                        <option value="">Semua Poliklinik</option>
                        @foreach($polyclinics as $poli)
                            <option value="{{ $poli->id }}" {{ request('polyclinic_id') == $poli->id ? 'selected' : '' }}>
                                {{ $poli->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-unpam-600 hover:bg-unpam-700 text-white transition-colors shadow-2xs">
                        Filter
                    </button>
                    @if(request('search') || request('polyclinic_id'))
                        <a href="{{ route('pasien.riwayat-medis') }}" class="px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Daftar Riwayat Medis -->
        @if($records->isEmpty())
            <div class="bg-white rounded-3xl border border-dashed border-slate-200 p-12 text-center shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3 border border-slate-100">
                    <i data-lucide="file-x" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Tidak Ditemukan Riwayat Medis</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    @if(request('search') || request('polyclinic_id'))
                        Tidak ada riwayat medis yang cocok dengan kriteria filter pencarian Anda. Coba kata kunci lain.
                    @else
                        Anda belum memiliki riwayat pemeriksaan medis yang telah selesai di klinik ini.
                    @endif
                </p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($records as $rec)
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-md shadow-slate-200/30 p-6 transition-all hover:border-unpam-300">
                        
                        <!-- Top Header Record -->
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 rounded-xl bg-unpam-100 text-unpam-900 font-mono font-black text-sm border border-unpam-200">
                                    {{ $rec->queue_code }}
                                </span>
                                <div>
                                    <span class="text-sm font-extrabold text-slate-900">{{ $rec->polyclinic->name }}</span>
                                    <span class="text-xs text-slate-500 block">
                                        Pemeriksa: <strong class="text-slate-700">{{ $rec->doctor?->name ?? 'Dokter Jaga' }}</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span>{{ $rec->completed_at?->translatedFormat('d F Y - H:i') ?? $rec->queue_date->format('d/m/Y') }} WIB</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Selesai
                                </span>
                            </div>
                        </div>

                        <!-- Konten Medis (Diagnosa & Anjuran, Tanpa Resep Obat) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-4">
                            
                            <!-- Keluhan & Penyakit -->
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                    Keluhan Awal Pasien
                                </span>
                                <p class="text-xs text-slate-700 italic bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    "{{ $rec->complaint ?? '-' }}"
                                </p>

                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3 mb-1">
                                    Penyakit Teridentifikasi
                                </span>
                                <p class="text-sm font-bold text-slate-900">
                                    {{ $rec->disease ?? '-' }}
                                </p>
                            </div>

                            <!-- Diagnosa Klinis & Catatan Dokter -->
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                                    Hasil Diagnosa Medis
                                </span>
                                <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line bg-teal-50/50 p-2.5 rounded-xl border border-teal-100">
                                    {{ $rec->diagnosis ?? '-' }}
                                </p>

                                @if($rec->doctor_notes)
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mt-3 mb-1">
                                        Catatan / Anjuran Dokter
                                    </span>
                                    <p class="text-xs text-slate-600 italic">
                                        {{ $rec->doctor_notes }}
                                    </p>
                                @endif
                            </div>

                            <!-- Status Pelayanan Farmasi (Tanpa Menampilkan Rincian Resep Obat) -->
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-teal-700 block mb-1 flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                    Status Penyerahan Obat
                                </span>
                                <div class="text-xs text-slate-700 bg-slate-50 p-2.5 rounded-xl border border-slate-200 leading-relaxed">
                                    <p class="font-semibold text-slate-800">Obat Selesai Diserahkan</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Seluruh obat telah disiapkan dan diserahkan di Loket Apotek Klinik Unpam.</p>
                                </div>

                                @if($rec->pharmacist_notes)
                                    <p class="text-[11px] text-teal-800 mt-2 bg-teal-50 p-2 rounded-lg border border-teal-200">
                                        <strong>Petunjuk Pemakaian Apotek:</strong> {{ $rec->pharmacist_notes }}
                                    </p>
                                @endif
                            </div>

                        </div>

                        <!-- Footer Aksi -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] text-slate-400">
                                Diverifikasi Apotek: {{ $rec->pharmacist_verified_at?->format('H:i') ?? '-' }} WIB
                            </span>
                            <a href="{{ route('pasien.riwayat-medis.show', $rec) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-unpam-700 hover:text-unpam-800 bg-unpam-50 hover:bg-unpam-100 border border-unpam-200 transition-colors">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                Cetak Resume Medis Resmi
                            </a>
                        </div>

                    </div>
                @endforeach

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $records->links() }}
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
