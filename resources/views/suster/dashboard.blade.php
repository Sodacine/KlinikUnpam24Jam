@extends('layouts.app')

@section('title', 'Dashboard Suster - Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Dashboard Suster -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200 mb-2">
                    <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                    Stasiun Suster & Triase Pasien
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Monitoring Antrian Pasien
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Verifikasi kedatangan pasien, atur alokasi dokter poliklinik, dan panggil pasien secara digital.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('suster.staff') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-sm transition-all">
                    <i data-lucide="user-cog" class="w-4 h-4 text-sky-400"></i>
                    Manajemen Staf Tenaga Medis
                </a>
                <a href="{{ route('queue.monitor') }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-2xs transition-all">
                    <i data-lucide="tv" class="w-4 h-4 text-unpam-600"></i>
                    Layar Display
                </a>
            </div>
        </div>

        <!-- 7 Statistik Ringkasan Alur Antrian -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-8">
            
            <a href="{{ route('suster.dashboard') }}" class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs hover:border-unpam-500 transition-colors">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Hari Ini</span>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
                <span class="text-[10px] text-slate-500 mt-0.5 block">Semua antrian</span>
            </a>

            <a href="{{ route('suster.dashboard', ['status' => 'menunggu_suster']) }}" class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200 shadow-xs hover:border-amber-400 transition-colors">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 block">Menunggu Suster</span>
                <div class="text-2xl font-black text-amber-900 mt-1">{{ $stats['menunggu_suster'] }}</div>
                <span class="text-[10px] text-amber-600 mt-0.5 block font-semibold">Perlu Verifikasi &raquo;</span>
            </a>

            <a href="{{ route('suster.dashboard', ['status' => 'menunggu_dokter']) }}" class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200 shadow-xs hover:border-blue-400 transition-colors">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 block">Menuju Dokter</span>
                <div class="text-2xl font-black text-blue-900 mt-1">{{ $stats['menunggu_dokter'] }}</div>
                <span class="text-[10px] text-blue-600 mt-0.5 block">Di depan Poli</span>
            </a>

            <a href="{{ route('suster.dashboard', ['status' => 'pemeriksaan_dokter']) }}" class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-200 shadow-xs hover:border-indigo-400 transition-colors">
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 block">Diperiksa Dokter</span>
                <div class="text-2xl font-black text-indigo-900 mt-1">{{ $stats['pemeriksaan_dokter'] }}</div>
                <span class="text-[10px] text-indigo-600 mt-0.5 block">Dalam ruangan</span>
            </a>

            <a href="{{ route('suster.dashboard', ['status' => 'menunggu_apotek']) }}" class="p-4 rounded-2xl bg-purple-50/60 border border-purple-200 shadow-xs hover:border-purple-400 transition-colors">
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700 block">Resep di Apotek</span>
                <div class="text-2xl font-black text-purple-900 mt-1">{{ $stats['menunggu_apotek'] + $stats['siap_diambil'] }}</div>
                <span class="text-[10px] text-purple-600 mt-0.5 block">Farmasi obat</span>
            </a>

            <a href="{{ route('suster.dashboard', ['status' => 'selesai']) }}" class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 shadow-xs hover:border-emerald-400 transition-colors">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 block">Selesai</span>
                <div class="text-2xl font-black text-emerald-900 mt-1">{{ $stats['selesai'] }}</div>
                <span class="text-[10px] text-emerald-600 mt-0.5 block">Masuk rekam medis</span>
            </a>

            <a href="{{ route('suster.dashboard', ['status' => 'dilewati']) }}" class="p-4 rounded-2xl bg-rose-50/60 border border-rose-200 shadow-xs hover:border-rose-400 transition-colors">
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700 block">Dilewati</span>
                <div class="text-2xl font-black text-rose-900 mt-1">{{ $stats['dilewati'] }}</div>
                <span class="text-[10px] text-rose-600 mt-0.5 block font-semibold">Bisa dipanggil ulang</span>
            </a>

        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs mb-6">
            <form action="{{ route('suster.dashboard') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                
                <!-- Search Input -->
                <div class="sm:col-span-2 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="block w-full pl-10 pr-4 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs placeholder:text-slate-400"
                        placeholder="Cari kode tiket, nama pasien, atau NIK...">
                </div>

                <!-- Filter Poli -->
                <div>
                    <select name="polyclinic_id" onchange="this.form.submit()" class="block w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs">
                        <option value="">Semua Poliklinik (4 Poli)</option>
                        @foreach($polyclinics as $poli)
                            <option value="{{ $poli->id }}" {{ request('polyclinic_id') == $poli->id ? 'selected' : '' }}>
                                {{ $poli->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="flex items-center gap-2">
                    <select name="status" onchange="this.form.submit()" class="block w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs">
                        <option value="">Semua Status</option>
                        <option value="menunggu_suster" {{ request('status') === 'menunggu_suster' ? 'selected' : '' }}>1. Menunggu Suster</option>
                        <option value="menunggu_dokter" {{ request('status') === 'menunggu_dokter' ? 'selected' : '' }}>2. Menuju Dokter</option>
                        <option value="pemeriksaan_dokter" {{ request('status') === 'pemeriksaan_dokter' ? 'selected' : '' }}>3. Pemeriksaan Dokter</option>
                        <option value="menunggu_apotek" {{ request('status') === 'menunggu_apotek' ? 'selected' : '' }}>4. Menunggu Apotek</option>
                        <option value="siap_diambil" {{ request('status') === 'siap_diambil' ? 'selected' : '' }}>5. Siap Diambil</option>
                        <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>6. Selesai</option>
                        <option value="dilewati" {{ request('status') === 'dilewati' ? 'selected' : '' }}>⏭ Dilewati (Tidak Hadir)</option>
                    </select>

                    @if(request('search') || request('polyclinic_id') || request('status'))
                        <a href="{{ route('suster.dashboard') }}" class="px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors whitespace-nowrap">
                            Reset
                        </a>
                    @endif
                </div>

            </form>
        </div>

        <!-- Tabel Antrian Hari Ini -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-lg shadow-slate-200/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Antrian Pasien Hari Ini</h2>
                    <p class="text-xs text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                    Total: {{ $queues->total() }} Antrian
                </span>
            </div>

            @if($queues->isEmpty())
                <div class="py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-3 border border-slate-100">
                        <i data-lucide="inbox" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Belum Ada Antrian Pasien</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                        Antrian yang didaftarkan oleh pasien akan muncul di sini secara berurutan.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-4 text-center w-16">No</th>
                                <th class="py-3.5 px-4">Kode & Jam</th>
                                <th class="py-3.5 px-4">Pasien & Kontak</th>
                                <th class="py-3.5 px-4">Poliklinik & Keluhan</th>
                                <th class="py-3.5 px-4">Dokter Jaga</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi Suster</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($queues as $q)
                                <tr class="hover:bg-slate-50/80 transition-colors {{ $q->status === 'menunggu_suster' ? 'bg-amber-50/30' : '' }}">
                                    
                                    <!-- Urutan -->
                                    <td class="py-4 px-4 text-center font-bold text-slate-400">
                                        #{{ $q->queue_number }}
                                    </td>

                                    <!-- Kode Tiket & Waktu -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="font-mono text-sm font-black text-slate-900 block">{{ $q->queue_code }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $q->created_at->format('H:i') }} WIB</span>
                                    </td>

                                    <!-- Pasien -->
                                    <td class="py-4 px-4">
                                        <div class="font-bold text-slate-900 text-sm">{{ $q->patient->name }}</div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                            @if($q->patient->gender)
                                                <span>{{ $q->patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                                <span>&bull;</span>
                                            @endif
                                            <span>{{ $q->patient->phone ?? 'Tanpa HP' }}</span>
                                        </div>
                                    </td>

                                    <!-- Poli & Keluhan -->
                                    <td class="py-4 px-4 max-w-xs">
                                        <span class="font-bold text-unpam-800 block">{{ $q->polyclinic->name }}</span>
                                        <span class="text-[11px] text-slate-600 italic line-clamp-2 mt-0.5">
                                            "{{ $q->complaint }}"
                                        </span>
                                    </td>

                                    <!-- Dokter Dialokasikan -->
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($q->doctor)
                                            <span class="font-semibold text-slate-800 block">{{ $q->doctor->name }}</span>
                                            <span class="text-[10px] text-emerald-600 font-medium">&check; Terverifikasi Suster</span>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">Belum dialokasikan</span>
                                        @endif
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-4 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $q->status_badge_class }}">
                                            {{ $q->status_label }}
                                        </span>
                                    </td>

                                    <!-- Aksi Suster -->
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            
                                            <!-- Tombol Panggil Suara (Semua Status) -->
                                            <button type="button" onclick="callQueueVoice('{{ $q->queue_code }}', '{{ $q->polyclinic->name }}')" title="Panggil Antrian Pasien" class="p-2 rounded-xl text-slate-600 hover:text-sky-700 bg-slate-100 hover:bg-sky-50 border border-slate-200 hover:border-sky-300 transition-colors">
                                                <i data-lucide="volume-2" class="w-4 h-4"></i>
                                            </button>

                                            @if($q->status === \App\Models\Queue::STATUS_MENUNGGU_SUSTER)
                                                {{-- === MENUNGGU SUSTER: Tombol Verifikasi + Skip --}}
                                                <!-- Tombol Pindah Poli oleh Suster -->
                                                <button type="button" onclick="openSusterTransferModal('{{ $q->id }}', '{{ $q->queue_code }}', '{{ $q->patient->name }}', '{{ $q->polyclinic_id }}', '{{ $q->polyclinic->name }}')" title="Pindahkan Pasien ke Poli Lain" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-all">
                                                    <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                                                    Pindah Poli
                                                </button>

                                                <form action="{{ route('suster.antrian.verifikasi', $q) }}" method="POST" class="inline">
                                                    @csrf
                                                    
                                                    <!-- Dropdown Pilih Dokter Spesifik (Opsional) -->
                                                    @php
                                                        $availableDoctors = $q->polyclinic->doctors;
                                                    @endphp

                                                    @if($availableDoctors->count() > 1)
                                                        <select name="doctor_id" class="px-2 py-1 text-xs rounded-lg border border-slate-300 mr-1 focus:ring-sky-500">
                                                            @foreach($availableDoctors as $doc)
                                                                <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($availableDoctors->count() === 1)
                                                        <input type="hidden" name="doctor_id" value="{{ $availableDoctors->first()->id }}">
                                                    @endif

                                                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-sm transition-all active:scale-95">
                                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                                        Verifikasi &raquo;
                                                    </button>
                                                </form>

                                                {{-- Tombol Skip Pasien Tidak Hadir --}}
                                                <form action="{{ route('suster.antrian.skip', $q) }}" method="POST" class="inline"
                                                      data-confirm="Lewati antrian {{ $q->queue_code }} ({{ $q->patient->name }})? Pasien dapat dipanggil ulang dalam 24 jam."
                                                      data-confirm-type="warning"
                                                      data-confirm-title="Lewati Antrian"
                                                      data-confirm-ok="Ya, Lewati">
                                                    @csrf
                                                    <button type="submit" title="Lewati – Pasien Tidak Hadir"
                                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors">
                                                        <i data-lucide="skip-forward" class="w-3.5 h-3.5"></i>
                                                        Lewati
                                                    </button>
                                                </form>

                                            @elseif($q->status === \App\Models\Queue::STATUS_DILEWATI)
                                                {{-- === DILEWATI: Tombol Panggil Ulang + Info Expired --}}
                                                <div class="flex flex-col items-end gap-1">
                                                    @if($q->expired_at && now()->isBefore($q->expired_at))
                                                        <form action="{{ route('suster.antrian.recall', $q) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-orange-500 hover:bg-orange-600 shadow-sm transition-all active:scale-95">
                                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                                Panggil Ulang
                                                            </button>
                                                        </form>
                                                        <span class="text-[10px] text-rose-500 font-medium">
                                                            Hangus: {{ $q->expired_at->format('H:i, d/m') }}
                                                        </span>
                                                    @else
                                                        <span class="text-[10px] text-slate-400 italic">Antrian kadaluarsa</span>
                                                    @endif
                                                </div>

                                            @else
                                                {{-- === STATUS LAIN: Tampilkan info verifikasi --}}
                                                <span class="text-[11px] text-slate-400 font-medium">
                                                    Diverifikasi: {{ $q->nurse_verified_at?->format('H:i') ?? '-' }}
                                                </span>
                                            @endif

                                        </div>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $queues->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL PINDAH POLIKLINIK (SUSTER)          -->
<!-- ========================================== -->
<div id="modalSusterPindahPoli" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-8">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-amber-100 rounded-xl text-amber-700">
                    <i data-lucide="arrow-right-left" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pindahkan Poli Pasien</h3>
                    <p class="text-xs text-slate-500">Pasien salah pilih poli sebelum diverifikasi</p>
                </div>
            </div>
            <button type="button" onclick="closeSusterTransferModal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="susterTransferPoliForm" method="POST" action="">
            @csrf

            <!-- Info Pasien -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 mb-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kode Antrian Saat Ini</span>
                    <span id="susterTransferQueueCode" class="font-mono font-black text-xs text-sky-700 bg-sky-50 px-2 py-0.5 rounded-lg border border-sky-200"></span>
                </div>
                <div id="susterTransferPatientName" class="font-bold text-sm text-slate-900"></div>
                <div class="text-xs text-slate-500 mt-0.5">
                    Poli Saat Ini: <strong id="susterTransferSourcePoli" class="text-slate-700"></strong>
                </div>
            </div>

            <!-- Pilih Poli Tujuan -->
            <div class="mb-5">
                <label for="suster_target_polyclinic_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Pilih Poliklinik Tujuan Baru <span class="text-rose-500">*</span>
                </label>
                <select id="suster_target_polyclinic_id" name="target_polyclinic_id" required class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-amber-300 bg-amber-50/20 focus:ring-2 focus:ring-amber-500 shadow-2xs font-semibold text-slate-800">
                    <option value="">Pilih Poliklinik Tujuan</option>
                    @foreach($polyclinics as $poli)
                        <option value="{{ $poli->id }}">
                            {{ $poli->name }} ({{ $poli->floor_room }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                    Sistem akan membatalkan tiket lama dan otomatis menerbitkan tiket antrian baru di poli tujuan. Status antrian tetap <em>Menunggu Suster</em> agar dapat diverifikasi di poli baru.
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeSusterTransferModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
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

<script>
    function openSusterTransferModal(queueId, queueCode, patientName, sourcePoliId, sourcePoliName) {
        const form = document.getElementById('susterTransferPoliForm');
        form.action = `/suster/antrian/${queueId}/pindah-poli`;

        document.getElementById('susterTransferQueueCode').innerText = queueCode;
        document.getElementById('susterTransferPatientName').innerText = patientName;
        document.getElementById('susterTransferSourcePoli').innerText = sourcePoliName;

        const select = document.getElementById('suster_target_polyclinic_id');
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

        document.getElementById('modalSusterPindahPoli').classList.remove('hidden');
    }

    function closeSusterTransferModal() {
        document.getElementById('modalSusterPindahPoli').classList.add('hidden');
    }
</script>
@endsection
