<?php

namespace App\Http\Controllers;

use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SusterController extends Controller
{
    /**
     * Dashboard Suster: Pemantauan Semua Antrian
     */
    public function dashboard(Request $request): View
    {
        // Bersihkan antrian yang sudah kadaluarsa (>24 jam)
        Queue::autoExpireOutdatedQueues();

        $today = Carbon::today();

        // Query antrian hari ini atau antrian aktif belum expired (<24 jam)
        $query = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->where(function ($q) use ($today) {
                $q->whereDate('queue_date', $today)
                    ->orWhere(function ($sub) {
                        $sub->where('status', '!=', Queue::STATUS_SELESAI)
                            ->whereNotNull('expired_at')
                            ->where('expired_at', '>', now());
                    });
            });

        // Filter Poliklinik
        if ($poliId = $request->input('polyclinic_id')) {
            $query->where('polyclinic_id', $poliId);
        }

        // Filter Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter Pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('queue_code', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        $queues = $query->orderBy('queue_number')->paginate(15)->withQueryString();

        // Statistik Cepat Hari Ini
        $stats = [
            'total' => Queue::whereDate('queue_date', $today)->count(),
            'menunggu_suster' => Queue::whereDate('queue_date', $today)->where('status', Queue::STATUS_MENUNGGU_SUSTER)->count(),
            'menunggu_dokter' => Queue::whereDate('queue_date', $today)->where('status', Queue::STATUS_MENUNGGU_DOKTER)->count(),
            'pemeriksaan_dokter' => Queue::whereDate('queue_date', $today)->where('status', Queue::STATUS_PEMERIKSAAN_DOKTER)->count(),
            'menunggu_apotek' => Queue::whereDate('queue_date', $today)->where('status', Queue::STATUS_MENUNGGU_APOTEK)->count(),
            'siap_diambil' => Queue::whereDate('queue_date', $today)->where('status', Queue::STATUS_SIAP_DIAMBIL)->count(),
            'selesai' => Queue::whereDate('queue_date', $today)->where('status', Queue::STATUS_SELESAI)->count(),
            'dilewati' => Queue::whereDate('queue_date', $today)->where('status', Queue::STATUS_DILEWATI)->count(),
        ];

        $polyclinics = Polyclinic::with('doctors')->where('is_active', true)->get();

        return view('suster.dashboard', compact('queues', 'stats', 'polyclinics'));
    }

    /**
     * Skip Antrian Pasien (Tidak Hadir Saat Dipanggil)
     * Status: menunggu_suster/menunggu_dokter -> dilewati
     * Pasien bisa dipanggil ulang oleh suster, akan otomatis hangus setelah 24 jam
     */
    public function skipQueue(Queue $queue): RedirectResponse
    {
        if (in_array($queue->status, [Queue::STATUS_SELESAI, Queue::STATUS_DILEWATI])) {
            return back()->with('error', "Antrian {$queue->queue_code} tidak dapat di-skip.");
        }

        $queue->update([
            'status' => Queue::STATUS_DILEWATI,
            'skipped_at' => Carbon::now(),
            'skip_count' => $queue->skip_count + 1,
            'expired_at' => Carbon::now()->addHours(24), // Auto-expire 24 jam
        ]);

        return back()->with('warning', "Antrian {$queue->queue_code} ({$queue->patient->name}) telah DILEWATI. Pasien dapat dipanggil ulang selama 24 jam ke depan, hingga {$queue->expired_at->format('d/m/Y H:i')} WIB sebelum hangus otomatis.");
    }

    /**
     * Panggil Ulang Antrian yang Dilewati
     * Mengembalikan status ke tahap sebelum di-skip
     */
    public function recallQueue(Queue $queue): RedirectResponse
    {
        if ($queue->status !== Queue::STATUS_DILEWATI) {
            return back()->with('error', "Antrian {$queue->queue_code} tidak berstatus 'Dilewati', tidak bisa dipanggil ulang.");
        }

        // Cek apakah sudah expired
        if ($queue->expired_at && Carbon::now()->isAfter($queue->expired_at)) {
            $queue->delete();

            return back()->with('error', "Antrian {$queue->queue_code} sudah kadaluarsa/hangus (lebih dari 24 jam). Pasien harus mendaftar ulang.");
        }

        $targetStatus = $queue->getPreviousStatusBeforeSkip();

        $queue->update([
            'status' => $targetStatus,
            'skipped_at' => null, // Reset skip timestamp
        ]);

        $queue->refresh();

        return back()->with('success', "Antrian {$queue->queue_code} ({$queue->patient->name}) berhasil DIPANGGIL ULANG! Pasien kembali ke status '{$queue->status_label}'.");
    }

    /**
     * Verifikasi Antrian Pasien oleh Suster
     * Mengubah status: menunggu_suster -> menunggu_dokter
     */
    public function verifyQueue(Request $request, Queue $queue): RedirectResponse
    {
        if ($queue->status !== Queue::STATUS_MENUNGGU_SUSTER) {
            return back()->with('error', "Antrian {$queue->queue_code} tidak dapat diverifikasi lagi karena statusnya sudah {$queue->status_label}.");
        }

        $validated = $request->validate([
            'doctor_id' => ['nullable', 'exists:users,id'],
        ]);

        // Cek apakah ada dokter yang bertugas di poli ini (sistem shifting)
        $doctorId = $validated['doctor_id'] ?? null;
        if (! $doctorId) {
            $defaultDoctor = User::where('role', 'dokter')
                ->where('polyclinic_id', $queue->polyclinic_id)
                ->first();
            $doctorId = $defaultDoctor?->id;
        }

        // Blokir verifikasi jika tidak ada dokter yang bertugas di poli ini
        if (! $doctorId) {
            $poliName = $queue->polyclinic->name;

            return back()->with('error', "Verifikasi GAGAL: Tidak ada dokter yang sedang bertugas di {$poliName}. Harap atur jadwal shift dokter ke poli ini terlebih dahulu melalui Manajemen Staf.");
        }

        // Cek apakah ada apoteker yang sedang bertugas / aktif di loket apotek
        $activeApoteker = User::where('role', 'apoteker')
            ->where(function ($q) {
                $q->whereNotNull('work_station')
                    ->orWhereNotNull('polyclinic_id');
            })
            ->first();

        // Blokir verifikasi jika belum ada apoteker yang bertugas
        if (! $activeApoteker) {
            return back()->with('error', 'Verifikasi GAGAL: Dokter sudah ada, namun belum ada Apoteker yang bertugas di Loket Farmasi/Apotek. Harap tempatkan Apoteker di Manajemen Staf agar resep obat nantinya dapat dilayani.');
        }

        $queue->update([
            'status' => Queue::STATUS_MENUNGGU_DOKTER,
            'doctor_id' => $doctorId,
            'nurse_verified_at' => Carbon::now(),
            'expired_at' => Carbon::now()->addHours(24), // Auto-expire 24 jam jika pasien tidak kunjung datang ke dokter
        ]);

        $queue->refresh();
        $doctorName = $queue->doctor?->name ? " → Dialokasikan ke {$queue->doctor->name}" : '';

        return back()->with('success', "Antrian {$queue->queue_code} ({$queue->patient->name}) BERHASIL diverifikasi! Pasien menuju {$queue->polyclinic->name} ({$queue->polyclinic->floor_room}){$doctorName}.");
    }

    /**
     * Suster Memindahkan Pasien ke Poliklinik Lain Sebelum Verifikasi
     * Jika poli yang dipilih pasien salah / tidak sesuai dengan keluhan.
     */
    public function transferPolyclinic(Request $request, Queue $queue): RedirectResponse
    {
        if ($queue->status !== Queue::STATUS_MENUNGGU_SUSTER) {
            return back()->with('error', "Antrian {$queue->queue_code} tidak dapat dipindah oleh suster karena statusnya sudah {$queue->status_label}.");
        }

        $validated = $request->validate([
            'target_polyclinic_id' => ['required', 'exists:polyclinics,id', 'different:polyclinic_id'],
        ], [
            'target_polyclinic_id.required' => 'Pilih poli tujuan terlebih dahulu.',
            'target_polyclinic_id.different' => 'Poli tujuan harus berbeda dari poli saat ini.',
        ]);

        $targetPoli = Polyclinic::findOrFail($validated['target_polyclinic_id']);

        // Generate nomor antrian baru untuk poli tujuan
        $today = Carbon::today();
        $lastQueue = Queue::whereDate('queue_date', $today)
            ->where('polyclinic_id', $targetPoli->id)
            ->max('queue_number');
        $newQueueNumber = ($lastQueue ?? 0) + 1;
        $newQueueCode = $targetPoli->code.'-'.str_pad($newQueueNumber, 3, '0', STR_PAD_LEFT);

        // Buat antrian baru di poli tujuan (tetap berstatus menunggu_suster)
        $newQueue = Queue::create([
            'queue_code' => $newQueueCode,
            'queue_number' => $newQueueNumber,
            'queue_date' => $today,
            'patient_id' => $queue->patient_id,
            'polyclinic_id' => $targetPoli->id,
            'status' => Queue::STATUS_MENUNGGU_SUSTER,
            'complaint' => $queue->complaint,
        ]);

        // Tandai antrian lama sebagai sudah dipindah
        $susterName = auth()->user()->name;
        $queue->update([
            'status' => Queue::STATUS_DILEWATI,
            'doctor_notes' => "[DIPINDAH SUSTER {$susterName} ke {$targetPoli->name} - Antrian Baru: {$newQueueCode}]",
            'skipped_at' => Carbon::now(),
            'expired_at' => Carbon::now(), // langsung expire
        ]);

        return redirect()->route('suster.dashboard')
            ->with('success', "Pasien {$queue->patient->name} BERHASIL dipindahkan ke {$targetPoli->name}! Nomor tiket antrian baru: {$newQueueCode}. Silakan lakukan verifikasi pada antrian baru.");
    }

    /**
     * Manajemen Staf Tenaga Medis: Daftar Akun Staf
     */
    public function staffIndex(): View
    {
        $staffMembers = User::with('polyclinic')
            ->whereIn('role', ['suster', 'dokter', 'apoteker'])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        $polyclinics = Polyclinic::where('is_active', true)->get();

        return view('suster.staff', compact('staffMembers', 'polyclinics'));
    }

    /**
     * Simpan Staf Tenaga Medis Baru
     */
    public function staffStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:suster,dokter,apoteker'],
            'polyclinic_id' => [
                'nullable',
                Rule::requiredIf($request->role === 'dokter'),
                'exists:polyclinics,id',
            ],
            'work_station' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20', 'unique:users,nik'],
            'gender' => ['nullable', 'in:L,P'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'role.required' => 'Pilih role tenaga medis.',
            'polyclinic_id.required' => 'Dokter wajib dipilihkan Poliklinik tempat bertugas.',
            'nik.unique' => 'NIK ini sudah terdaftar.',
        ]);

        // Cek aturan 1 ruangan / loket = 1 tenaga medis
        if ($validated['role'] === 'dokter' && ! empty($validated['polyclinic_id'])) {
            $occupied = User::where('role', 'dokter')
                ->where('polyclinic_id', $validated['polyclinic_id'])
                ->first();
            if ($occupied) {
                $poli = Polyclinic::find($validated['polyclinic_id']);

                return back()->withInput()->with('error', "Ruangan {$poli->name} sudah diisi oleh Dokter {$occupied->name}. Satu ruangan poli hanya bisa ditempati oleh 1 dokter!");
            }
        } elseif (in_array($validated['role'], ['suster', 'apoteker']) && ! empty($validated['work_station'])) {
            $occupied = User::where('role', $validated['role'])
                ->where('work_station', $validated['work_station'])
                ->first();
            if ($occupied) {
                $stationName = $validated['role'] === 'suster'
                    ? (User::SUSTER_STATIONS[$validated['work_station']] ?? $validated['work_station'])
                    : (User::APOTEKER_STATIONS[$validated['work_station']] ?? $validated['work_station']);

                return back()->withInput()->with('error', "Loket {$stationName} sudah diisi oleh {$occupied->name}. Satu loket hanya bisa diisi oleh 1 orang bertugas!");
            }
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'polyclinic_id' => $validated['polyclinic_id'] ?? null,
            'work_station' => $validated['work_station'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'nik' => $validated['nik'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        return redirect()->route('suster.staff')
            ->with('success', "Akun tenaga medis {$validated['name']} ({$validated['role']}) berhasil ditambahkan!");
    }

    /**
     * Update Shift / Ruangan Penugasan Tenaga Medis Langsung dari Dropdown Tabel
     * Menerapkan aturan: 1 ruangan/loket hanya untuk 1 tenaga medis (tidak boleh double)
     */
    public function updateStaffShift(Request $request, User $user): RedirectResponse
    {
        // Pastikan hanya tenaga medis
        if (! in_array($user->role, ['suster', 'dokter', 'apoteker'])) {
            return back()->with('error', 'Hanya tenaga medis yang dapat diatur jadwal shifting ruangannya.');
        }

        // DOKTER: Shift berdasarkan Poliklinik (polyclinic_id)
        if ($user->role === 'dokter') {
            $validated = $request->validate([
                'polyclinic_id' => ['nullable', 'exists:polyclinics,id'],
            ]);

            $newPoliId = $validated['polyclinic_id'] ?? null;

            // Jika memilih ruangan (bukan OFF), cek apakah ruangan tersebut sudah ada dokter lain
            if ($newPoliId) {
                $occupied = User::where('role', 'dokter')
                    ->where('id', '!=', $user->id)
                    ->where('polyclinic_id', $newPoliId)
                    ->first();

                if ($occupied) {
                    $targetPoli = Polyclinic::find($newPoliId);

                    return back()->with('error', "Penempatan GAGAL: Ruangan {$targetPoli->name} sudah ditempati oleh Dokter {$occupied->name}. Satu ruangan poliklinik hanya dapat ditempati oleh 1 dokter! Ubah status dokter sebelumnya menjadi OFF terlebih dahulu.");
                }
            }

            $user->update([
                'polyclinic_id' => $newPoliId,
            ]);

            $user->refresh();
            $label = $user->polyclinic ? $user->polyclinic->name : 'OFF / Tidak Bertugas';

            return redirect()->route('suster.staff')
                ->with('success', "Jadwal shift Dokter {$user->name} berhasil diperbarui: {$label}.");
        }

        // SUSTER atau APOTEKER: Shift berdasarkan work_station (Loket)
        $validated = $request->validate([
            'work_station' => ['nullable', 'string', 'max:50'],
        ]);

        $newStation = $validated['work_station'] ?? null;

        // Jika memilih loket (bukan OFF), cek apakah loket tersebut sudah ada yang bertugas
        if ($newStation) {
            $occupied = User::where('role', $user->role)
                ->where('id', '!=', $user->id)
                ->where('work_station', $newStation)
                ->first();

            if ($occupied) {
                $stationName = $user->role === 'suster'
                    ? (User::SUSTER_STATIONS[$newStation] ?? $newStation)
                    : (User::APOTEKER_STATIONS[$newStation] ?? $newStation);

                $roleTitle = $user->role === 'suster' ? 'Suster' : 'Apoteker';

                return back()->with('error', "Penempatan GAGAL: {$stationName} sudah ditempati oleh {$roleTitle} {$occupied->name}. Satu loket hanya dapat diisi oleh 1 petugas jaga! Ubah status petugas sebelumnya menjadi OFF terlebih dahulu.");
            }
        }

        $user->update([
            'work_station' => $newStation,
        ]);

        $user->refresh();
        $label = $user->shift_location_label ?? 'OFF / Tidak Bertugas';

        return redirect()->route('suster.staff')
            ->with('success', "Penugasan loket jaga untuk {$user->name} ({$user->role_label}) berhasil diubah ke: {$label}.");
    }

    /**
     * Edit data staf (return JSON untuk modal AJAX)
     */
    public function staffEdit(User $user): JsonResponse
    {
        if (! in_array($user->role, ['suster', 'dokter', 'apoteker'])) {
            return response()->json(['error' => 'Aksi tidak valid.'], 403);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'nik' => $user->nik,
            'gender' => $user->gender,
        ]);
    }

    /**
     * Simpan Perubahan Data Staf (Nama, Email, No HP, NIK, Jenis Kelamin, Password opsional)
     */
    public function staffUpdate(Request $request, User $user): RedirectResponse
    {
        if (! in_array($user->role, ['suster', 'dokter', 'apoteker'])) {
            return back()->with('error', 'Aksi tidak valid.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'nik' => ['nullable', 'string', 'max:20', Rule::unique('users', 'nik')->ignore($user->id)],
            'gender' => ['nullable', 'in:L,P'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'nik.unique' => 'NIK ini sudah terdaftar pada akun lain.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'nik' => $validated['nik'] ?? null,
            'gender' => $validated['gender'] ?? null,
        ];

        // Perbarui password hanya jika diisi
        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('suster.staff')
            ->with('success', "Data tenaga medis {$user->name} ({$user->role_label}) berhasil diperbarui.");
    }

    /**
     * Hapus Staf Tenaga Medis
     */
    public function staffDestroy(User $user): RedirectResponse
    {
        // Cegah menghapus akun sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cegah menghapus pasien dari menu staf
        if ($user->role === 'pasien') {
            return back()->with('error', 'Aksi tidak valid.');
        }

        $name = $user->name;
        $role = $user->role_label;
        $user->delete();

        return redirect()->route('suster.staff')
            ->with('success', "Akun tenaga medis {$name} ({$role}) berhasil dihapus.");
    }

    /**
     * Arsip Semua Riwayat Medis Pasien (Akses Suster)
     * Dengan filter fleksibel (spesifik & umum): nama pasien, tanggal/rentang tanggal, poli, dokter, keluhan, diagnosa
     */
    public function medicalRecords(Request $request): View
    {
        $query = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->where('status', Queue::STATUS_SELESAI);

        // 1. Pencarian Kata Kunci Umum / Fleksibel (Bisa cari nama pasien, NIK, penyakit, diagnosa, no antrian, dokter)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('queue_code', 'like', "%{$search}%")
                    ->orWhere('disease', 'like', "%{$search}%")
                    ->orWhere('diagnosis', 'like', "%{$search}%")
                    ->orWhere('complaint', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('doctor', function ($dq) use ($search) {
                        $dq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Filter Nama Pasien Spesifik
        if ($patientName = $request->input('patient_name')) {
            $query->whereHas('patient', function ($pq) use ($patientName) {
                $pq->where('name', 'like', "%{$patientName}%");
            });
        }

        // 3. Filter Poliklinik
        if ($poliId = $request->input('polyclinic_id')) {
            $query->where('polyclinic_id', $poliId);
        }

        // 4. Filter Dokter Pemeriksa
        if ($doctorId = $request->input('doctor_id')) {
            $query->where('doctor_id', $doctorId);
        }

        // 5. Filter Tanggal Spesifik atau Rentang Tanggal
        if ($date = $request->input('date')) {
            $query->whereDate('queue_date', $date);
        } else {
            if ($dateFrom = $request->input('date_from')) {
                $query->whereDate('queue_date', '>=', $dateFrom);
            }
            if ($dateTo = $request->input('date_to')) {
                $query->whereDate('queue_date', '<=', $dateTo);
            }
        }

        $records = $query->orderByDesc('completed_at')->paginate(15)->withQueryString();

        $polyclinics = Polyclinic::where('is_active', true)->get();
        $doctors = User::where('role', 'dokter')->orderBy('name')->get();

        $totalRecords = Queue::where('status', Queue::STATUS_SELESAI)->count();

        return view('suster.medical_records', compact(
            'records',
            'polyclinics',
            'doctors',
            'totalRecords'
        ));
    }

    /**
     * Detail Rekam Medis Pasien untuk Suster
     */
    public function showMedicalRecord(Queue $queue): View|RedirectResponse
    {
        if ($queue->status !== Queue::STATUS_SELESAI) {
            return redirect()->route('suster.riwayat-medis')->with('error', 'Antrian ini belum berstatus selesai.');
        }

        $queue->load(['patient', 'polyclinic', 'doctor']);

        return view('suster.medical_record_detail', compact('queue'));
    }
}
