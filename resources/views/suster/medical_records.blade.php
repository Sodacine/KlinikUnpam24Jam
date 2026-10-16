@extends('layouts.app')

@section('title', 'Arsip Riwayat Medis Semua Pasien - Suster Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <a href="{{ route('suster.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-unpam-700 mb-2 transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    Kembali ke Monitor Antrian
                </a>
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200">
                        <i data-lucide="folder-clock" class="w-3.5 h-3.5"></i>
                        Arsip Medis Terpadu
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mt-1">
                    Riwayat Medis Seluruh Pasien
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Akses rekam medis lengkap dari seluruh poliklinik dengan fitur pencarian dan filter bertingkat (umum & spesifik).
                </p>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-4 py-2 rounded-2xl bg-white text-xs font-bold text-slate-700 border border-slate-200 shadow-2xs">
                    Total Rekam Medis: <strong class="text-sky-700">{{ $totalRecords }}</strong>
                </span>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- FILTER FLEKSIBEL (SPESIFIK & UMUM)         -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-md p-6 mb-8">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-sky-600"></i>
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700">Filter & Pencarian Riwayat Medis</h2>
                </div>
                <span class="text-[11px] text-slate-400">Gunakan filter spesifik atau pencarian umum</span>
            </div>

            <form action="{{ route('suster.riwayat-medis') }}" method="GET" class="space-y-4">
                
                <!-- Baris 1: Pencarian Kata Kunci Umum -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Pencarian Kata Kunci Umum (Fleksibel)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="block w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs placeholder:text-slate-400"
                            placeholder="Ketik apa saja: nama pasien, NIK, penyakit, keluhan, hasil diagnosa, nomor tiket, atau nama dokter...">
                    </div>
                </div>

                <!-- Baris 2: Filter Spesifik (Nama Pasien, Poliklinik, Dokter) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    
                    <!-- Nama Pasien Spesifik -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Nama Pasien Spesifik
                        </label>
                        <input type="text" name="patient_name" value="{{ request('patient_name') }}"
                            class="block w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs placeholder:text-slate-400"
                            placeholder="Contoh: Rangga">
                    </div>

                    <!-- Poliklinik -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Poliklinik
                        </label>
                        <select name="polyclinic_id" class="block w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs">
                            <option value="">Semua Poliklinik</option>
                            @foreach($polyclinics as $poli)
                                <option value="{{ $poli->id }}" {{ request('polyclinic_id') == $poli->id ? 'selected' : '' }}>
                                    {{ $poli->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dokter Pemeriksa -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Dokter Pemeriksa
                        </label>
                        <select name="doctor_id" class="block w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs">
                            <option value="">Semua Dokter</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                                    {{ $doc->name }} ({{ $doc->polyclinic?->name ?? 'Dokter Klinik' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <!-- Baris 3: Filter Waktu / Tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    
                    <!-- Tanggal Spesifik (1 Hari) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Tanggal Spesifik
                        </label>
                        <input type="date" name="date" value="{{ request('date') }}"
                            class="block w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs">
                    </div>

                    <!-- Rentang: Dari Tanggal -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Atau Rentang Dari Tanggal
                        </label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="block w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs">
                    </div>

                    <!-- Rentang: Sampai Tanggal -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                            Sampai Tanggal
                        </label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="block w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs">
                    </div>

                </div>

                <!-- Tombol Aksi Filter -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        Menampilkan hasil pencarian berdasarkan kriteria di atas
                    </span>

                    <div class="flex items-center gap-2">
                        @if(request()->hasAny(['search', 'patient_name', 'polyclinic_id', 'doctor_id', 'date', 'date_from', 'date_to']))
                            <a href="{{ route('suster.riwayat-medis') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                                Reset Filter
                            </a>
                        @endif

                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-sm transition-all active:scale-95">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            Terapkan Filter Pencarian
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- ========================================== -->
        <!-- TABEL HASIL ARSIP RIWAYAT MEDIS           -->
        <!-- ========================================== -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-lg shadow-slate-200/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Arsip Rekam Medis</h2>
                    <p class="text-xs text-slate-500">Seluruh kunjungan pasien yang telah berstatus selesai</p>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                    Ditemukan: {{ $records->total() }} Data
                </span>
            </div>

            @if($records->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3 border border-slate-100">
                        <i data-lucide="file-search" class="w-7 h-7 text-sky-400"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Tidak Ditemukan Riwayat Medis</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Tidak ada catatan rekam medis yang sesuai dengan kriteria filter yang Anda terapkan. Silakan ubah atau reset filter pencarian.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4">Tanggal & Kode</th>
                                <th class="py-3.5 px-4">Nama Pasien & NIK</th>
                                <th class="py-3.5 px-4">Poliklinik Tujuan</th>
                                <th class="py-3.5 px-4">Dokter Pemeriksa</th>
                                <th class="py-3.5 px-4">Penyakit & Diagnosa</th>
                                <th class="py-3.5 px-4">Waktu Selesai</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($records as $rec)
                                <tr class="hover:bg-sky-50/30 transition-colors">
                                    
                                    <!-- Tanggal & Kode Tiket -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="font-mono font-black text-slate-900 text-sm block">{{ $rec->queue_code }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $rec->queue_date->format('d/m/Y') }}</span>
                                    </td>

                                    <!-- Pasien -->
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-slate-900 text-sm">{{ $rec->patient->name }}</div>
                                        <div class="text-[11px] text-slate-500">
                                            NIK: {{ $rec->patient->nik ?? '-' }} &bull; {{ $rec->patient->gender === 'L' ? 'L' : 'P' }}
                                        </div>
                                    </td>

                                    <!-- Poliklinik -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="font-bold text-unpam-800 block">{{ $rec->polyclinic->name }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $rec->polyclinic->floor_room }}</span>
                                    </td>

                                    <!-- Dokter -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="font-semibold text-slate-800 block">{{ $rec->doctor?->name ?? 'Dokter Jaga' }}</span>
                                    </td>

                                    <!-- Penyakit & Diagnosa -->
                                    <td class="py-4 px-4 max-w-xs">
                                        <strong class="text-slate-900 block text-xs">{{ $rec->disease ?? '-' }}</strong>
                                        <span class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                                            {{ $rec->diagnosis ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- Waktu Selesai -->
                                    <td class="py-4 px-4 whitespace-nowrap text-slate-500 text-[11px]">
                                        {{ $rec->completed_at?->format('H:i') ?? '-' }} WIB
                                    </td>

                                    <!-- Tombol Aksi Detail -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <a href="{{ route('suster.riwayat-medis.show', $rec) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-sky-700 hover:text-sky-800 bg-sky-50 hover:bg-sky-100 border border-sky-200 transition-colors">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            Detail Rekam Medis
                                        </a>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $records->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
