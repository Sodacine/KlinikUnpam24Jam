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
        $today = Carbon::today();

        // 1. Pasien yang menunggu verifikasi & peracikan resep dokter
        $pendingPrescriptions = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->whereDate('queue_date', $today)
            ->where('status', Queue::STATUS_MENUNGGU_APOTEK)
            ->orderBy('doctor_finished_at')
            ->get();

        // 2. Pasien yang resepnya sudah siap dan dipanggil menuju loket apotek
        $readyToPickup = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->whereDate('queue_date', $today)
            ->where('status', Queue::STATUS_SIAP_DIAMBIL)
            ->orderBy('pharmacist_verified_at')
            ->get();

        // 3. Obat yang sudah diserahkan (siklus selesai) hari ini
        $completedToday = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->whereDate('queue_date', $today)
            ->where('status', Queue::STATUS_SELESAI)
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        // Statistik Cepat Apotek
        $stats = [
            'pending'   => $pendingPrescriptions->count(),
            'ready'     => $readyToPickup->count(),
            'completed' => Queue::whereDate('queue_date', $today)->where('status', Queue::STATUS_SELESAI)->count(),
        ];

        return view('apoteker.dashboard', compact(
            'pendingPrescriptions',
            'readyToPickup',
            'completedToday',
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
            'status'                 => Queue::STATUS_SIAP_DIAMBIL,
            'pharmacist_notes'       => $validated['pharmacist_notes'] ?? 'Obat telah disiapkan lengkap sesuai resep dokter. Silakan menuju Loket Apotek.',
            'pharmacist_verified_at' => Carbon::now(),
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
            'needs_prescription_revision'           => true,
            'prescription_replacement_reason'       => $validated['prescription_replacement_reason'],
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
            'status'       => Queue::STATUS_SELESAI,
            'completed_at' => Carbon::now(),
        ]);

        return back()->with('success', "Obat telah berhasil diserahkan kepada {$queue->patient->name} ({$queue->queue_code})! Seluruh siklus antrian SELESAI dan data resmi tersimpan sebagai Riwayat Medis.");
    }
}
