@extends('layouts.app')

@section('title', 'Pemeriksaan Pasien: ' . $queue->queue_code . ' - Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <a href="{{ route('dokter.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-indigo-700 mb-2 transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    Kembali ke Dashboard Dokter
                </a>
                <div class="flex items-center gap-3">
                    <span class="px-3.5 py-1 rounded-xl bg-indigo-100 text-indigo-900 font-mono font-black text-lg border border-indigo-200">
                        {{ $queue->queue_code }}
                    </span>
                    <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        Pemeriksaan Medis: {{ $queue->patient->name }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('dokter.antrian.skip', $queue) }}" method="POST" class="inline"
                      data-confirm="Lewati antrian {{ $queue->queue_code }} ({{ $queue->patient->name }}) karena pasien tak kunjung hadir atau meninggalkan sesi konsultasi? Antrian dapat dipanggil ulang dalam 24 jam sebelum hangus otomatis."
                      data-confirm-type="warning"
                      data-confirm-title="Lewati Pasien"
                      data-confirm-ok="Ya, Lewati">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 shadow-xs transition-all active:scale-95">
                        <i data-lucide="skip-forward" class="w-4 h-4 text-rose-600"></i>
                        Pasien Tak Hadir? Lewati
                    </button>
                </form>

                <button type="button" onclick="document.getElementById('modalPindahPoli').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-300 shadow-xs transition-all active:scale-95">
                    <i data-lucide="arrow-right-left" class="w-4 h-4 text-amber-600"></i>
                    Salah Poli? Pindahkan Pasien
                </button>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
                    <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                    Sesi Konsultasi Aktif
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- ========================================== -->
            <!-- KOLOM KIRI: BIODATA & RIWAYAT TERDAHULU   -->
            <!-- ========================================== -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Kartu Biodata Pasien -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-md p-6">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>Identitas Pasien</span>
                        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                    </h2>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Nama Lengkap:</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $queue->patient->name }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">NIK:</span>
                            <span class="font-mono text-slate-800">{{ $queue->patient->nik ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Jenis Kelamin:</span>
                            <span class="text-slate-800 font-semibold">{{ $queue->patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">No. WhatsApp / HP:</span>
                            <span class="text-slate-800 font-mono">{{ $queue->patient->phone ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Poliklinik Tujuan:</span>
                            <span class="font-bold text-unpam-800">{{ $queue->polyclinic->name }}</span>
                        </div>
                    </div>

                    <!-- Keluhan Pasien Saat Mendaftar -->
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 block mb-1">
                            Keluhan Utama Saat Mendaftar:
                        </span>
                        <p class="text-xs text-slate-800 bg-amber-50/60 p-3 rounded-2xl border border-amber-200 leading-relaxed italic">
                            "{{ $queue->complaint }}"
                        </p>
                    </div>
                </div>

                <!-- Kartu Riwayat Medis Pasien Terdahulu -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-md p-6">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                        <div>
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Riwayat Medis Pasien Terdahulu</h2>
                            <p class="text-[11px] text-slate-500">Referensi diagnosa & resep obat masa lalu</p>
                        </div>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                            {{ $pastMedicalRecords->count() }} Kunjungan
                        </span>
                    </div>

                    @if($pastMedicalRecords->isEmpty())
                        <div class="py-8 text-center text-xs text-slate-400">
                            <i data-lucide="history" class="w-6 h-6 mx-auto mb-1 text-slate-300"></i>
                            Pasien ini belum memiliki riwayat medis terdahulu di sistem (Kunjungan Baru).
                        </div>
                    @else
                        <div class="space-y-3 max-h-96 overflow-y-auto pr-1">
                            @foreach($pastMedicalRecords as $past)
                                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs">
                                    <div class="flex items-center justify-between font-bold text-slate-800 mb-1">
                                        <span class="text-unpam-800">{{ $past->polyclinic->name }}</span>
                                        <span class="text-[11px] text-slate-400 font-normal">{{ $past->completed_at?->format('d M Y') ?? $past->queue_date->format('d M Y') }}</span>
                                    </div>
                                    <div class="text-slate-900 font-semibold mb-1">
                                        {{ $past->disease }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mb-2">
                                        Diagnosa: {{ $past->diagnosis }}
                                    </div>
                                    <div class="p-2 rounded-xl bg-white border border-slate-200 font-mono text-[10px] text-slate-700 whitespace-pre-line leading-relaxed">
                                        <strong>Resep:</strong><br>{{ $past->prescription }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            <!-- ========================================== -->
            <!-- KOLOM KANAN: FORM INPUT DIAGNOSA & RESEP  -->
            <!-- ========================================== -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xl shadow-slate-200/40 p-6 sm:p-8">
                    
                    <div class="mb-6 pb-4 border-b border-slate-100">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-900 border border-indigo-200 mb-2">
                            <i data-lucide="file-pen" class="w-3.5 h-3.5"></i>
                            Lembar Catatan Medis & Resep Obat
                        </span>
                        <h2 class="text-xl font-extrabold text-slate-900">
                            Input Diagnosa & Resep Pasien
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Setelah formulir ini disubmit, data resep akan otomatis diteruskan secara real-time ke Apoteker untuk diracik dan disiapkan.
                        </p>
                    </div>

                    <form action="{{ route('dokter.antrian.selesai-periksa', $queue) }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- 1. Penyakit (Disease) -->
                        <div>
                            <label for="disease" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                1. Nama Penyakit / Masalah Klinis <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="disease" name="disease" value="{{ old('disease') }}" required
                                class="block w-full px-4 py-3 text-sm rounded-2xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs font-semibold text-slate-900 placeholder:text-slate-400"
                                placeholder="Contoh: Faringitis Akut & Rhinitis Alergi / Dispepsia Akut">
                            <p class="text-[11px] text-slate-500 mt-1">
                                Tuliskan nama diagnosis penyakit utama atau sindrom klinis.
                            </p>
                        </div>

                        <!-- 2. Diagnosa (Diagnosis) -->
                        <div>
                            <label for="diagnosis" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                2. Hasil Pemeriksaan Fisik & Diagnosa Medis Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="diagnosis" name="diagnosis" rows="4" required
                                class="block w-full px-4 py-3 text-sm rounded-2xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs placeholder:text-slate-400 leading-relaxed"
                                placeholder="Contoh: Suhu 38.2°C, Tekanan Darah 120/80 mmHg, denyut nadi 82x/menit. Faring hiperemis (+), tonsil tenang T1/T1. Paru vesikuler tanpa wheezing/rhonki. Terindikasi infeksi saluran napas atas...">{{ old('diagnosis') }}</textarea>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Catat tanda vital, hasil pemeriksaan fisik, dan dasar penegakan diagnosa.
                            </p>
                        </div>

                        <!-- 3. Resep Obat (Prescription - Text / List) -->
                        <div>
                            <label for="prescription" class="block text-xs font-bold uppercase tracking-wider text-indigo-900 mb-1.5 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="pill" class="w-4 h-4 text-indigo-600"></i>
                                    3. Resep Obat untuk Pasien (Format Teks/List) <span class="text-rose-500">*</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-normal">Diteruskan langsung ke Apoteker</span>
                            </label>
                            <textarea id="prescription" name="prescription" rows="5" required
                                class="block w-full px-4 py-3 text-sm font-mono rounded-2xl border-2 border-indigo-200 bg-indigo-50/20 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs placeholder:text-slate-400 leading-relaxed text-slate-900"
                                placeholder="Tuliskan daftar obat beserta dosis dan aturan pakai, contoh:&#10;1. Paracetamol 500mg - 3x1 tablet sesudah makan (bila demam/nyeri)&#10;2. Amoxicillin 500mg - 3x1 kaplet sesudah makan (habiskan 5 hari)&#10;3. Cetirizine 10mg - 1x1 tablet malam hari&#10;4. Antasida Doen - 3x1 tablet kunyah 1 jam sebelum makan">{{ old('prescription') }}</textarea>
                            <p class="text-[11px] text-indigo-700 mt-1 font-medium">
                                Masukkan nama obat, dosis, frekuensi, dan aturan minum. Apoteker akan meracik obat sesuai daftar ini.
                            </p>
                        </div>

                        <!-- 4. Catatan Dokter / Anjuran (Doctor Notes) -->
                        <div>
                            <label for="doctor_notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                4. Catatan / Anjuran Khusus Dokter (Opsional)
                            </label>
                            <textarea id="doctor_notes" name="doctor_notes" rows="2"
                                class="block w-full px-4 py-2.5 text-sm rounded-2xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs placeholder:text-slate-400"
                                placeholder="Contoh: Istirahat cukup, hindari makanan pedas dan asam, banyak minum air hangat minimal 2L/hari. Kontrol ulang 3 hari jika belum membaik.">{{ old('doctor_notes') }}</textarea>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <a href="{{ route('dokter.dashboard') }}" class="px-5 py-3 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700 transition-colors">
                                Batalkan / Kembali
                            </a>

                            <button type="button"
                                    data-btn-confirm="Pemeriksaan akan ditandai selesai dan data diagnosa serta resep langsung diteruskan ke Apotek. Lanjutkan?"
                                    data-confirm-title="Selesai Pemeriksaan"
                                    data-confirm-type="success"
                                    data-confirm-ok="Ya, Teruskan ke Apotek"
                                    class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl text-sm font-bold text-white bg-gradient-to-r from-indigo-700 via-indigo-600 to-sky-600 hover:from-indigo-800 hover:to-sky-700 shadow-lg shadow-indigo-600/30 transition-all active:scale-95">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Selesai & Teruskan Resep ke Apotek &raquo;
                            </button>
                        </div>

                    </form>

                </div>
            </div>

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
                    <h3 class="text-base font-bold text-slate-900">Pindahkan Pasien ke Poli Lain</h3>
                    <p class="text-xs text-slate-500">Pasien salah pilih poli & butuh dialihkan</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalPindahPoli').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('dokter.antrian.pindah-poli', $queue) }}">
            @csrf

            <!-- Info Pasien Saat Ini -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 mb-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kode Tiket Saat Ini</span>
                    <span class="font-mono font-black text-xs text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-200">{{ $queue->queue_code }}</span>
                </div>
                <div class="font-bold text-sm text-slate-900">{{ $queue->patient->name }}</div>
                <div class="text-xs text-slate-500 mt-0.5">
                    Poli Saat Ini: <strong class="text-slate-700">{{ $queue->polyclinic->name }}</strong>
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
                        @if($poli->id != $queue->polyclinic_id)
                            <option value="{{ $poli->id }}">
                                {{ $poli->name }} ({{ $poli->floor_room }})
                            </option>
                        @endif
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                    Sistem akan otomatis membuat tiket antrian baru di poli tujuan. Sesi periksa di poli ini akan dihentikan dan ditandai dialihkan.
                </p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalPindahPoli').classList.add('hidden')" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="button"
                        data-btn-confirm="Pasien akan dipindahkan ke poliklinik lain dan mendapat nomor antrian baru di sana. Lanjutkan?"
                        data-confirm-title="Pindah Poliklinik"
                        data-confirm-type="warning"
                        data-confirm-ok="Ya, Pindahkan"
                        class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-md shadow-amber-600/20 transition-all active:scale-95">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Pindahkan Pasien Sekarang
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
