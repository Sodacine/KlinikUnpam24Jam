@extends('layouts.app')

@section('title', 'Manajemen Tenaga Medis - Klinik Unpam 24 Jam')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <a href="{{ route('suster.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-unpam-700 mb-2 transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    Kembali ke Antrian
                </a>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Manajemen Akun Tenaga Medis
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Hak akses Suster untuk menambahkan dan mengelola akun Suster, Dokter, dan Apoteker Klinik Unpam 24 Jam.
                </p>
            </div>

            <button type="button" onclick="document.getElementById('modalTambahStaf').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-md shadow-sky-600/20 transition-all active:scale-95">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Tambah Tenaga Medis Baru
            </button>
        </div>

        <!-- Tabel Daftar Staf Medis -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-lg shadow-slate-200/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Daftar Tenaga Medis Terdaftar</h2>
                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                    Total: {{ $staffMembers->count() }} Staf
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4">Nama & Gelar</th>
                            <th class="py-3.5 px-4">Email Login</th>
                            <th class="py-3.5 px-4 text-center">Peran (Role)</th>
                            <th class="py-3.5 px-4">Shifting Ruangan / Loket Jaga (1 Ruangan 1 Petugas)</th>
                            <th class="py-3.5 px-4">Kontak & NIK</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($staffMembers as $staf)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                        {{ $staf->name }}
                                        @if($staf->id === auth()->id())
                                            <span class="text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-full border border-sky-200">
                                                Akun Anda
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-slate-400 text-[11px]">Terdaftar sejak {{ $staf->created_at->format('d M Y') }}</span>
                                </td>

                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="font-mono text-slate-700 font-semibold">{{ $staf->email }}</span>
                                </td>

                                <td class="py-4 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                        @if($staf->isSuster()) bg-sky-100 text-sky-800 border border-sky-200
                                        @elseif($staf->isDokter()) bg-indigo-100 text-indigo-800 border border-indigo-200
                                        @elseif($staf->isApoteker()) bg-amber-100 text-amber-800 border border-amber-200
                                        @endif">
                                        {{ $staf->role_label }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 whitespace-nowrap">
                                    <form action="{{ route('suster.staff.update-shift', $staf) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="relative min-w-[240px]">
                                            @if($staf->isDokter())
                                                {{-- Dropdown Poliklinik untuk Dokter --}}
                                                <select name="polyclinic_id" onchange="this.form.submit()" 
                                                    class="block w-full py-2 pl-3 pr-8 text-xs font-semibold rounded-xl border {{ $staf->polyclinic_id ? 'border-indigo-400 bg-indigo-50/70 text-indigo-950 focus:ring-indigo-500' : 'border-slate-300 bg-slate-50 text-slate-500' }} shadow-2xs transition-colors cursor-pointer">
                                                    <option value="" {{ is_null($staf->polyclinic_id) ? 'selected' : '' }}>
                                                        -- OFF / Tidak Bertugas --
                                                    </option>
                                                    @foreach($polyclinics as $poli)
                                                        <option value="{{ $poli->id }}" {{ $staf->polyclinic_id == $poli->id ? 'selected' : '' }}>
                                                            {{ $poli->name }} ({{ $poli->floor_room }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif($staf->isApoteker())
                                                {{-- Dropdown Loket Farmasi untuk Apoteker --}}
                                                <select name="work_station" onchange="this.form.submit()" 
                                                    class="block w-full py-2 pl-3 pr-8 text-xs font-semibold rounded-xl border {{ $staf->work_station ? 'border-teal-400 bg-teal-50/70 text-teal-950 focus:ring-teal-500' : 'border-slate-300 bg-slate-50 text-slate-500' }} shadow-2xs transition-colors cursor-pointer">
                                                    <option value="" {{ is_null($staf->work_station) ? 'selected' : '' }}>
                                                        -- OFF / Tidak Bertugas --
                                                    </option>
                                                    @foreach(\App\Models\User::APOTEKER_STATIONS as $key => $label)
                                                        <option value="{{ $key }}" {{ $staf->work_station === $key ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif($staf->isSuster())
                                                {{-- Dropdown Loket Pendaftaran & Triase untuk Suster --}}
                                                <select name="work_station" onchange="this.form.submit()" 
                                                    class="block w-full py-2 pl-3 pr-8 text-xs font-semibold rounded-xl border {{ $staf->work_station ? 'border-sky-400 bg-sky-50/70 text-sky-950 focus:ring-sky-500' : 'border-slate-300 bg-slate-50 text-slate-500' }} shadow-2xs transition-colors cursor-pointer">
                                                    <option value="" {{ is_null($staf->work_station) ? 'selected' : '' }}>
                                                        -- OFF / Tidak Bertugas --
                                                    </option>
                                                    @foreach(\App\Models\User::SUSTER_STATIONS as $key => $label)
                                                        <option value="{{ $key }}" {{ $staf->work_station === $key ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>
                                    </form>

                                    @if($staf->shift_location_label)
                                        <span class="text-[10px] text-emerald-700 font-bold block mt-1 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                            Aktif Jaga: {{ $staf->shift_location_label }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400 font-semibold block mt-1 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300 inline-block"></span>
                                            Status: OFF (Tidak Bertugas)
                                        </span>
                                    @endif
                                </td>

                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="text-slate-800 block font-mono">{{ $staf->phone ?? '-' }}</span>
                                    <span class="text-slate-400 text-[11px] block">NIK: {{ $staf->nik ?? '-' }}</span>
                                </td>

                                <td class="py-4 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        {{-- Tombol Edit --}}
                                        <button type="button"
                                                onclick="openEditModal({{ $staf->id }}, '{{ route('suster.staff.edit', $staf) }}')"
                                                class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Edit Data Staf">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        @if($staf->id !== auth()->id())
                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('suster.staff.destroy', $staf) }}" method="POST" class="inline"
                                                  data-confirm="Hapus akun tenaga medis {{ $staf->name }}? Tindakan ini tidak dapat dibatalkan."
                                                  data-confirm-type="danger"
                                                  data-confirm-title="Hapus Akun Staf"
                                                  data-confirm-ok="Ya, Hapus Sekarang">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Staf">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-300 text-[11px] italic">Aktif Login</span>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL TAMBAH TENAGA MEDIS BARU            -->
<!-- ========================================== -->
<div id="modalTambahStaf" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 my-8">
        
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-sky-100 rounded-xl text-sky-700">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Tambah Akun Tenaga Medis</h3>
                    <p class="text-xs text-slate-500">Pendaftaran akun Suster, Dokter, atau Apoteker</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalTambahStaf').classList.add('hidden')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('suster.staff.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Role Medis (Suster, Dokter, Apoteker) -->
            <div>
                <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Peran / Posisi Tenaga Medis <span class="text-rose-500">*</span>
                </label>
                <select id="role" name="role" required onchange="handleRoleChange(this.value)" class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs font-semibold">
                    <option value="">Pilih Posisi Medis</option>
                    <option value="suster">Suster / Perawat</option>
                    <option value="dokter">Dokter</option>
                    <option value="apoteker">Apoteker / Farmasi</option>
                </select>
            </div>

            <!-- Penugasan Shift / Ruangan / Loket Berdasarkan Role -->
            <div id="poliSelectionGroup" class="hidden">
                <label id="polyclinicLabel" for="polyclinic_id" class="block text-xs font-bold uppercase tracking-wider text-indigo-700 mb-1.5">
                    Ruangan Poliklinik (Dokter) <span id="poliRequiredAsterisk" class="text-rose-500">*</span>
                </label>
                <select id="polyclinic_id" name="polyclinic_id" class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-indigo-300 bg-indigo-50/30 focus:ring-2 focus:ring-indigo-500 shadow-2xs font-semibold text-slate-800">
                    <option value="">-- Pilih Poliklinik (1 Dokter 1 Poli) --</option>
                    @foreach($polyclinics as $poli)
                        <option value="{{ $poli->id }}">{{ $poli->name }} ({{ $poli->floor_room }})</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Satu ruangan poli hanya dapat diisi oleh 1 dokter aktif.</p>
            </div>

            <!-- Penugasan Loket Apotek (Apoteker) -->
            <div id="apotekSelectionGroup" class="hidden">
                <label for="work_station_apotek" class="block text-xs font-bold uppercase tracking-wider text-teal-700 mb-1.5">
                    Loket Farmasi & Apotek (Apoteker Jaga)
                </label>
                <select id="work_station_apotek" name="work_station_apotek" class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-teal-300 bg-teal-50/30 focus:ring-2 focus:ring-teal-500 shadow-2xs font-semibold text-slate-800">
                    <option value="">-- OFF / Tidak Bertugas --</option>
                    @foreach(\App\Models\User::APOTEKER_STATIONS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Satu loket apotek hanya dapat diisi oleh 1 apoteker jaga.</p>
            </div>

            <!-- Penugasan Loket / Tempat Suster -->
            <div id="susterSelectionGroup" class="hidden">
                <label for="work_station_suster" class="block text-xs font-bold uppercase tracking-wider text-sky-700 mb-1.5">
                    Loket Pendaftaran & Triase (Suster Jaga)
                </label>
                <select id="work_station_suster" name="work_station_suster" class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-sky-300 bg-sky-50/30 focus:ring-2 focus:ring-sky-500 shadow-2xs font-semibold text-slate-800">
                    <option value="">-- OFF / Tidak Bertugas --</option>
                    @foreach(\App\Models\User::SUSTER_STATIONS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Satu loket suster hanya dapat diisi oleh 1 perawat/suster jaga.</p>
            </div>

            <!-- Nama Lengkap & Gelar -->
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Lengkap & Gelar <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" required
                    class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs placeholder:text-slate-400"
                    placeholder="Contoh: dr. Ahmad Fauzi, Sp.A atau Siti Rahma, S.Kep">
            </div>

            <!-- Email & Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Alamat Email Login <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                        class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs placeholder:text-slate-400"
                        placeholder="staf@unpam.ac.id">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi Awal <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required minlength="6"
                        class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs placeholder:text-slate-400"
                        placeholder="Minimal 6 karakter">
                </div>
            </div>

            <!-- No HP & NIK -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Induk Kependudukan (NIK)
                    </label>
                    <input type="text" id="nik" name="nik" maxlength="20"
                        class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs font-mono"
                        placeholder="16 digit NIK">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        No. Telepon / WhatsApp
                    </label>
                    <input type="tel" id="phone" name="phone"
                        class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs"
                        placeholder="081234567890">
                </div>
            </div>

            <!-- Jenis Kelamin -->
            <div>
                <label for="gender" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Jenis Kelamin
                </label>
                <select id="gender" name="gender" class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 shadow-2xs">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>

            <!-- Tombol Submit & Batal -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalTambahStaf').classList.add('hidden')" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-md shadow-sky-600/20 transition-all active:scale-95">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Simpan Akun Tenaga Medis
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ========================================== -->
<!-- MODAL EDIT TENAGA MEDIS                   -->
<!-- ========================================== -->
<div id="modalEditStaf" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 my-8">

        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-indigo-100 rounded-xl text-indigo-700">
                    <i data-lucide="user-pen" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Edit Data Tenaga Medis</h3>
                    <p id="editModalSubtitle" class="text-xs text-slate-500">Perbarui nama, kontak, atau password</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="editStafForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Nama Lengkap -->
            <div>
                <label for="edit_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Nama Lengkap &amp; Gelar <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="edit_name" name="name" required
                    class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs"
                    placeholder="Contoh: dr. Ahmad Fauzi, Sp.A">
            </div>

            <!-- Email -->
            <div>
                <label for="edit_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Alamat Email Login <span class="text-rose-500">*</span>
                </label>
                <input type="email" id="edit_email" name="email" required
                    class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs font-mono"
                    placeholder="staf@unpam.ac.id">
            </div>

            <!-- No HP & NIK -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="edit_nik" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        NIK
                    </label>
                    <input type="text" id="edit_nik" name="nik" maxlength="20"
                        class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs font-mono"
                        placeholder="16 digit NIK">
                </div>
                <div>
                    <label for="edit_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        No. Telepon / WA
                    </label>
                    <input type="tel" id="edit_phone" name="phone"
                        class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs"
                        placeholder="081234567890">
                </div>
            </div>

            <!-- Jenis Kelamin -->
            <div>
                <label for="edit_gender" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Jenis Kelamin
                </label>
                <select id="edit_gender" name="gender"
                    class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-2xs">
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>

            <!-- Reset Password (Opsional) -->
            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200">
                <label for="edit_password" class="block text-xs font-bold uppercase tracking-wider text-amber-700 mb-1.5">
                    <i data-lucide="key-round" class="w-3.5 h-3.5 inline-block mr-1"></i>
                    Ganti Password (Kosongkan jika tidak ingin ubah)
                </label>
                <input type="password" id="edit_password" name="password" minlength="6"
                    class="block w-full px-3.5 py-2.5 text-sm rounded-xl border border-amber-300 bg-white focus:ring-2 focus:ring-amber-500 shadow-2xs"
                    placeholder="Minimal 6 karakter baru...">
                <p class="text-[11px] text-amber-600 mt-1">Biarkan kosong jika password tidak perlu diubah.</p>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all active:scale-95">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function handleRoleChange(role) {
        const poliGroup = document.getElementById('poliSelectionGroup');
        const poliSelect = document.getElementById('polyclinic_id');
        const apotekGroup = document.getElementById('apotekSelectionGroup');
        const apotekSelect = document.getElementById('work_station_apotek');
        const susterGroup = document.getElementById('susterSelectionGroup');
        const susterSelect = document.getElementById('work_station_suster');

        // Reset display
        poliGroup.classList.add('hidden');
        poliSelect.required = false;
        apotekGroup.classList.add('hidden');
        susterGroup.classList.add('hidden');

        if (role === 'dokter') {
            poliGroup.classList.remove('hidden');
            poliSelect.required = true;
        } else if (role === 'apoteker') {
            apotekGroup.classList.remove('hidden');
            apotekSelect.name = 'work_station';
        } else if (role === 'suster') {
            susterGroup.classList.remove('hidden');
            susterSelect.name = 'work_station';
        }
    }

    // ===== Edit Staff Modal =====
    const EDIT_UPDATE_BASE = '{{ url('suster/staff') }}';

    async function openEditModal(userId, editUrl) {
        const modal = document.getElementById('modalEditStaf');
        const form  = document.getElementById('editStafForm');
        const subtitle = document.getElementById('editModalSubtitle');

        // Set form action ke PUT endpoint
        form.action = EDIT_UPDATE_BASE + '/' + userId;

        // Reset password field
        document.getElementById('edit_password').value = '';

        // Fetch data staf via JSON endpoint
        try {
            const res = await fetch(editUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            document.getElementById('edit_name').value   = data.name   ?? '';
            document.getElementById('edit_email').value  = data.email  ?? '';
            document.getElementById('edit_phone').value  = data.phone  ?? '';
            document.getElementById('edit_nik').value    = data.nik    ?? '';
            document.getElementById('edit_gender').value = data.gender ?? '';

            const roleMap = { suster: 'Suster / Perawat', dokter: 'Dokter', apoteker: 'Apoteker' };
            subtitle.textContent = (roleMap[data.role] ?? data.role) + ' · ' + data.name;
        } catch(err) {
            console.error('Gagal load data staf:', err);
        }

        modal.classList.remove('hidden');
        document.getElementById('edit_name').focus();
        if (window.lucide) window.lucide.createIcons();
    }

    function closeEditModal() {
        document.getElementById('modalEditStaf').classList.add('hidden');
    }
</script>
@endsection
