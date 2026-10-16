<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApotekerController extends Controller
{
    /**
     * Dashboard Apoteker: Antrian Resep & Loket Obat
     */
    public function dashboard(): View
    {
        // Bersihkan antrian yang sudah kadaluarsa (>24 jam)
        Queue::autoExpireOutdatedQueues();

        $today = Carbon::today();

        // 1. Pasien yang menunggu verifikasi & peracikan resep dokter
        $pendingPrescriptions = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->where('status', Queue::STATUS_MENUNGGU_APOTEK)
            ->where(function ($query) use ($today) {
                $query->whereDate('queue_date', $today)
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('expired_at')
                            ->where('expired_at', '>', now());
                    });
            })
            ->orderBy('doctor_finished_at')
            ->get();

        // 2. Pasien yang resepnya sudah siap dan dipanggil menuju loket apotek
        $readyToPickup = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->where('status', Queue::STATUS_SIAP_DIAMBIL)
            ->where(function ($query) use ($today) {
                $query->whereDate('queue_date', $today)
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('expired_at')
                            ->where('expired_at', '>', now());
                    });
            })
            ->orderBy('pharmacist_verified_at')
            ->get();

        // 3. Obat yang sudah diserahkan (siklus selesai) hari ini
        $completedToday = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->whereDate('queue_date', $today)
            ->where('status', Queue::STATUS_SELESAI)
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        // 4. Pasien yang dilewati apotek karena tak kunjung mengambil obat (bisa dipanggil ulang selama 24 jam)
        $skippedPrescriptions = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->where('status', Queue::STATUS_DILEWATI)
            ->whereNotNull('doctor_finished_at')
            ->where(function ($query) use ($today) {
                $query->whereDate('queue_date', $today)
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('expired_at')
                            ->where('expired_at', '>', now());
                    });
            })
            ->latest('skipped_at')
            ->get();

        // Statistik Cepat Apotek
        $stats = [
            'pending' => $pendingPrescriptions->count(),
            'ready' => $readyToPickup->count(),
            'skipped' => $skippedPrescriptions->count(),
            'completed' => Queue::whereDate('queue_date', $today)->where('status', Queue::STATUS_SELESAI)->count(),
        ];

        return view('apoteker.dashboard', compact(
            'pendingPrescriptions',
            'readyToPickup',
            'completedToday',
            'skippedPrescriptions',
            'stats'
        ));
    }

    /**
     * Verifikasi Resep Dokter oleh Apoteker
     * Mengubah status: menunggu_apotek -> siap_diambil
     * Memberitahu pasien menuju loket apotek
     */
    public function verifyPrescription(Request $request, Queue $queue): RedirectResponse
    {
        if ($queue->status !== Queue::STATUS_MENUNGGU_APOTEK) {
            return back()->with('error', "Status antrian {$queue->queue_code} bukan menunggu apotek.");
        }

        $validated = $request->validate([
            'pharmacist_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $queue->update([
            'status' => Queue::STATUS_SIAP_DIAMBIL,
            'pharmacist_notes' => $validated['pharmacist_notes'] ?? 'Obat telah disiapkan lengkap sesuai resep dokter. Silakan menuju Loket Apotek.',
            'pharmacist_verified_at' => Carbon::now(),
            'expired_at' => Carbon::now()->addHours(24), // Auto-expire 24 jam jika obat tak kunjung diambil
        ]);

        return back()->with('success', "Resep pasien {$queue->patient->name} ({$queue->queue_code}) BERHASIL diverifikasi! Status berubah menjadi SIAP DIAMBIL. Pasien diarahkan menuju Loket Apotek.");
    }

    /**
     * Apoteker Mengajukan Penggantian Resep Obat ke Dokter
     * Karena alasan stok obat habis atau perlu alternatif antibiotik/obat lain.
     */
    public function requestPrescriptionReplacement(Request $request, Queue $queue): RedirectResponse
    {
        if ($queue->status !== Queue::STATUS_MENUNGGU_APOTEK) {
            return back()->with('error', "Status antrian {$queue->queue_code} bukan menunggu apotek.");
        }

        $validated = $request->validate([
            'prescription_replacement_reason' => ['required', 'string', 'max:1000'],
        ], [
            'prescription_replacement_reason.required' => 'Harap isi alasan dan rekomendasi penggantian obat untuk Dokter.',
        ]);

        $queue->update([
            'needs_prescription_revision' => true,
            'prescription_replacement_reason' => $validated['prescription_replacement_reason'],
            'prescription_replacement_requested_at' => Carbon::now(),
        ]);

        $doctorName = $queue->doctor ? $queue->doctor->name : 'Dokter';

        return back()->with('success', "Pengajuan penggantian resep untuk {$queue->patient->name} ({$queue->queue_code}) BERHASIL dikirim ke {$doctorName}! Dokter akan menerima notifikasi untuk meresepkan ulang obat.");
    }

    /**
     * Penyerahan Obat Selesai
     * Mengubah status: siap_diambil -> selesai
     * Menyelesaikan siklus antrian & data resmi menjadi Riwayat Medis
     */
    public function completeDispensing(Request $request, Queue $queue): RedirectResponse
    {
        if ($queue->status !== Queue::STATUS_SIAP_DIAMBIL) {
            return back()->with('error', "Status antrian {$queue->queue_code} bukan siap diambil.");
        }

        $queue->update([
            'status' => Queue::STATUS_SELESAI,
            'completed_at' => Carbon::now(),
        ]);

        return back()->with('success', "Obat telah berhasil diserahkan kepada {$queue->patient->name} ({$queue->queue_code})! Seluruh siklus antrian SELESAI dan data resmi tersimpan sebagai Riwayat Medis.");
    }

    /**
     * Apoteker Melewati Pasien yang Tak Kunjung Mengambil Obat di Loket
     * Status: siap_diambil/menunggu_apotek -> dilewati (Auto-expire 24 jam)
     */
    public function skipQueue(Queue $queue): RedirectResponse
    {
        if (! in_array($queue->status, [Queue::STATUS_SIAP_DIAMBIL, Queue::STATUS_MENUNGGU_APOTEK])) {
            return back()->with('error', "Antrian {$queue->queue_code} tidak dapat dilewati dari loket apotek.");
        }

        $queue->update([
            'status' => Queue::STATUS_DILEWATI,
            'skipped_at' => Carbon::now(),
            'skip_count' => $queue->skip_count + 1,
            'expired_at' => Carbon::now()->addHours(24), // Auto-expire 24 jam
        ]);

        return back()->with('warning', "Antrian obat {$queue->queue_code} ({$queue->patient->name}) telah DILEWATI karena pasien tak kunjung mengambil obat di loket. Pasien memiliki waktu 24 jam hingga {$queue->expired_at->format('d/m/Y H:i')} WIB sebelum antrian hangus otomatis.");
    }

    /**
     * Apoteker Memanggil Ulang Pasien yang Datang Mengambil Obat Setelah Dilewati
     * Status: dilewati -> siap_diambil
     */
    public function recallQueue(Queue $queue): RedirectResponse
    {
        if ($queue->status !== Queue::STATUS_DILEWATI) {
            return back()->with('error', "Antrian {$queue->queue_code} tidak berstatus 'Dilewati'.");
        }

        // Cek apakah sudah expired (> 24 jam)
        if ($queue->expired_at && Carbon::now()->isAfter($queue->expired_at)) {
            $queue->delete();

            return back()->with('error', "Antrian {$queue->queue_code} sudah kadaluarsa/hangus (lebih dari 24 jam). Pasien harus mengambil nomor antrian baru.");
        }

        $targetStatus = $queue->getPreviousStatusBeforeSkip();

        $queue->update([
            'status' => $targetStatus,
            'skipped_at' => null,
        ]);

        return back()->with('success', "Antrian obat {$queue->queue_code} ({$queue->patient->name}) berhasil DIPANGGIL ULANG ke Loket Apotek!");
    }
}
