<?php

namespace App\Http\Controllers;

use App\Models\Polyclinic;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DokterController extends Controller
{
    /**
     * Dashboard Dokter: Antrian Pasien Menunggu di Poliklinik
     */
    public function dashboard(): View
    {
        $doctor = Auth::user();
        $today = Carbon::today();

        // Ambil antrian di poli dokter atau yang dialokasikan ke dokter ini
        $baseQuery = Queue::with(['patient', 'polyclinic'])
            ->whereDate('queue_date', $today);

        if ($doctor->polyclinic_id) {
            $baseQuery->where('polyclinic_id', $doctor->polyclinic_id);
        } else {
            $baseQuery->where('doctor_id', $doctor->id);
        }

        // Pasien yang sedang menunggu di luar ruangan dokter (sudah diverifikasi suster)
        $waitingPatients = (clone $baseQuery)
            ->where('status', Queue::STATUS_MENUNGGU_DOKTER)
            ->orderBy('queue_number')
            ->get();

        // Pasien yang sedang berada di dalam ruangan pemeriksaan dokter ini
        $currentPatient = (clone $baseQuery)
            ->where('status', Queue::STATUS_PEMERIKSAAN_DOKTER)
            ->where('doctor_id', $doctor->id)
            ->first();

        // Pasien yang sudah selesai diperiksa oleh dokter ini hari ini
        $handledToday = (clone $baseQuery)
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', [Queue::STATUS_MENUNGGU_APOTEK, Queue::STATUS_SIAP_DIAMBIL, Queue::STATUS_SELESAI])
            ->latest('doctor_finished_at')
            ->get();

        // Pasien yang diajukan penggantian resep obat oleh Apoteker (stok habis/revisi)
        $revisionRequests = (clone $baseQuery)
            ->where('doctor_id', $doctor->id)
            ->where('status', Queue::STATUS_MENUNGGU_APOTEK)
            ->where('needs_prescription_revision', true)
            ->latest('prescription_replacement_requested_at')
            ->get();

        // Semua poli aktif untuk fitur pindah poli pasien
        $polyclinics = Polyclinic::where('is_active', true)->get();

        return view('dokter.dashboard', compact(
            'doctor',
            'waitingPatients',
            'currentPatient',
            'revisionRequests',
            'handledToday',
            'polyclinics'
        ));
    }

    /**
     * Verifikasi Masuk Ruangan Pemeriksaan
     * Mengubah status: menunggu_dokter -> pemeriksaan_dokter
     */
    public function startExamination(Queue $queue): RedirectResponse
    {
        $doctor = Auth::user();

        if ($queue->status !== Queue::STATUS_MENUNGGU_DOKTER) {
            return back()->with('error', "Antrian {$queue->queue_code} tidak dapat dimulai karena statusnya sudah {$queue->status_label}.");
        }

        // Cek apakah dokter sedang menangani pasien lain yang belum selesai disubmit
        $ongoing = Queue::where('doctor_id', $doctor->id)
            ->where('status', Queue::STATUS_PEMERIKSAAN_DOKTER)
            ->where('id', '!=', $queue->id)
            ->first();

        if ($ongoing) {
            return redirect()->route('dokter.periksa', $ongoing)
                ->with('warning', "Anda masih memiliki sesi pemeriksaan aktif dengan pasien {$ongoing->patient->name} ({$ongoing->queue_code}). Selesaikan terlebih dahulu.");
        }

        $queue->update([
            'status'            => Queue::STATUS_PEMERIKSAAN_DOKTER,
            'doctor_id'         => $doctor->id,
            'doctor_started_at' => Carbon::now(),
        ]);

        return redirect()->route('dokter.periksa', $queue)
            ->with('success', "Pasien {$queue->patient->name} ({$queue->queue_code}) telah masuk ke ruang pemeriksaan. Silakan lakukan diagnosa.");
    }

    /**
     * Halaman Input Diagnosa & Resep Obat Pasien
     */
    public function examinationForm(Queue $queue): View|RedirectResponse
    {
        $doctor = Auth::user();

        // Pasien harus berstatus pemeriksaan_dokter
        if ($queue->status !== Queue::STATUS_PEMERIKSAAN_DOKTER) {
            return redirect()->route('dokter.dashboard')
                ->with('error', "Pasien tidak dalam status pemeriksaan dokter.");
        }

        $queue->load(['patient', 'polyclinic']);

        // Riwayat Medis terdahulu pasien untuk referensi dokter
        $pastMedicalRecords = Queue::with(['polyclinic', 'doctor'])
            ->where('patient_id', $queue->patient_id)
            ->where('id', '!=', $queue->id)
            ->where('status', Queue::STATUS_SELESAI)
            ->orderByDesc('completed_at')
            ->get();

        $polyclinics = Polyclinic::where('is_active', true)->get();

        return view('dokter.periksa', compact('queue', 'pastMedicalRecords', 'polyclinics'));
    }

    /**
     * Submit Diagnosa & Resep Obat
     * Mengubah status: pemeriksaan_dokter -> menunggu_apotek
     * Meneruskan data ke Apoteker
     */
    public function submitExamination(Request $request, Queue $queue): RedirectResponse
    {
        $doctor = Auth::user();

        if ($queue->status !== Queue::STATUS_PEMERIKSAAN_DOKTER) {
            return redirect()->route('dokter.dashboard')
                ->with('error', "Status antrian tidak valid untuk disubmit.");
        }

        $validated = $request->validate([
            'disease'      => ['required', 'string', 'max:255'],
            'diagnosis'    => ['required', 'string'],
            'prescription' => ['required', 'string'],
            'doctor_notes' => ['nullable', 'string'],
        ], [
            'disease.required'      => 'Nama penyakit / keluhan klinis wajib diisi.',
            'diagnosis.required'    => 'Hasil diagnosa dokter wajib diisi.',
            'prescription.required' => 'Resep obat wajib diisi untuk diteruskan ke Apotek.',
        ]);

        $queue->update([
            'disease'            => $validated['disease'],
            'diagnosis'          => $validated['diagnosis'],
            'prescription'       => $validated['prescription'],
            'doctor_notes'       => $validated['doctor_notes'] ?? null,
            'status'             => Queue::STATUS_MENUNGGU_APOTEK,
            'doctor_finished_at' => Carbon::now(),
            'needs_prescription_revision' => false,
            'prescription_replacement_reason' => null,
        ]);

        return redirect()->route('dokter.dashboard')
            ->with('success', "Pemeriksaan pasien {$queue->patient->name} ({$queue->queue_code}) BERHASIL diselesaikan! Resep obat telah diteruskan ke Apotek.");
    }

    /**
     * Dokter Meresepkan Ulang Resep Obat Pasien
     * Berdasarkan pengajuan penggantian obat dari Apoteker (stok habis / alternatif).
     */
    public function resubmitPrescription(Request $request, Queue $queue): RedirectResponse
    {
        $doctor = Auth::user();

        if ($queue->status !== Queue::STATUS_MENUNGGU_APOTEK || !$queue->needs_prescription_revision) {
            return back()->with('error', "Antrian {$queue->queue_code} tidak dalam status membutuhkan peresepan ulang.");
        }

        $validated = $request->validate([
            'prescription' => ['required', 'string'],
            'doctor_notes' => ['nullable', 'string'],
        ], [
            'prescription.required' => 'Resep obat pengganti / baru wajib diisi.',
        ]);

        $queue->update([
            'prescription'                => $validated['prescription'],
            'doctor_notes'                => $validated['doctor_notes'] ?? $queue->doctor_notes,
            'needs_prescription_revision' => false, // Reset flag pengajuan
            'doctor_finished_at'          => Carbon::now(), // Update waktu peresepan terbaru
        ]);

        return redirect()->route('dokter.dashboard')
            ->with('success', "Resep obat untuk pasien {$queue->patient->name} ({$queue->queue_code}) BERHASIL diresepkan ulang & diteruskan kembali ke Apoteker!");
    }

    /**
     * Pindahkan Pasien ke Poliklinik Lain
     * Antrian baru dibuat otomatis di poli tujuan dengan nomor antrian berikutnya.
     * Antrian lama (di poli asal) diubah statusnya menjadi 'selesai' (dibatalkan dokter).
     */
    public function transferPolyclinic(Request $request, Queue $queue): RedirectResponse
    {
        $doctor = Auth::user();

        if (!in_array($queue->status, [Queue::STATUS_MENUNGGU_DOKTER, Queue::STATUS_MENUNGGU_SUSTER, Queue::STATUS_PEMERIKSAAN_DOKTER])) {
            return back()->with('error', "Antrian {$queue->queue_code} tidak dapat dipindah poli karena statusnya sudah {$queue->status_label}.");
        }

        $validated = $request->validate([
            'target_polyclinic_id' => ['required', 'exists:polyclinics,id', 'different:polyclinic_id'],
        ], [
            'target_polyclinic_id.required'  => 'Pilih poli tujuan terlebih dahulu.',
            'target_polyclinic_id.different' => 'Poli tujuan harus berbeda dari poli saat ini.',
        ]);

        $targetPoli = Polyclinic::findOrFail($validated['target_polyclinic_id']);

        // Cek ada dokter di poli tujuan
        $targetDoctor = \App\Models\User::where('role', 'dokter')
            ->where('polyclinic_id', $targetPoli->id)
            ->first();

        if (!$targetDoctor) {
            return back()->with('error', "Pindah poli GAGAL: Tidak ada dokter yang bertugas di {$targetPoli->name}. Pilih poli lain yang memiliki dokter aktif.");
        }

        // Generate nomor antrian baru untuk poli tujuan
        $today = Carbon::today();
        $lastQueue = Queue::whereDate('queue_date', $today)
            ->where('polyclinic_id', $targetPoli->id)
            ->max('queue_number');
        $newQueueNumber = ($lastQueue ?? 0) + 1;
        $newQueueCode   = $targetPoli->code . '-' . str_pad($newQueueNumber, 3, '0', STR_PAD_LEFT);

        // Buat antrian baru di poli tujuan
        $newQueue = Queue::create([
            'queue_code'    => $newQueueCode,
            'queue_number'  => $newQueueNumber,
            'queue_date'    => $today,
            'patient_id'    => $queue->patient_id,
            'polyclinic_id' => $targetPoli->id,
            'doctor_id'     => $targetDoctor->id,
            'status'        => Queue::STATUS_MENUNGGU_DOKTER,
            'complaint'     => $queue->complaint,
            'nurse_verified_at' => Carbon::now(),
        ]);

        // Tandai antrian lama sebagai sudah dipindah (cancelled by doctor)
        $queue->update([
            'status'        => Queue::STATUS_DILEWATI,
            'doctor_notes'  => "[DIPINDAH DOKTER {$doctor->name} ke {$targetPoli->name} - Antrian Baru: {$newQueueCode}]",
            'skipped_at'    => Carbon::now(),
            'expired_at'    => Carbon::now(), // langsung expire
        ]);

        return redirect()->route('dokter.dashboard')
            ->with('success', "Pasien {$queue->patient->name} BERHASIL dipindah ke {$targetPoli->name}! Antrian baru: {$newQueueCode} (Dokter: {$targetDoctor->name}).");
    }
}
