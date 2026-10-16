<?php

namespace App\Http\Controllers;

use App\Models\Polyclinic;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class QueueDisplayController extends Controller
{
    /**
     * Layar Monitor Display Antrian Publik Klinik Unpam 24 Jam
     */
    public function index(): View
    {
        // Bersihkan antrian yang sudah kadaluarsa (>24 jam)
        Queue::autoExpireOutdatedQueues();

        $polyclinics = Polyclinic::where('is_active', true)->get();
        $today = Carbon::today();

        // Antrian yang sedang dipanggil / diperiksa per poliklinik
        $activePerPoli = [];
        foreach ($polyclinics as $poli) {
            $current = Queue::with('doctor')
                ->where('polyclinic_id', $poli->id)
                ->whereDate('queue_date', $today)
                ->whereIn('status', [Queue::STATUS_MENUNGGU_DOKTER, Queue::STATUS_PEMERIKSAAN_DOKTER])
                ->latest('updated_at')
                ->first();

            $totalWaiting = Queue::where('polyclinic_id', $poli->id)
                ->whereDate('queue_date', $today)
                ->whereIn('status', [Queue::STATUS_MENUNGGU_SUSTER, Queue::STATUS_MENUNGGU_DOKTER])
                ->count();

            $activePerPoli[$poli->id] = [
                'current' => $current,
                'totalWaiting' => $totalWaiting,
            ];
        }

        // Antrian di Loket Apotek
        $pharmacyReady = Queue::with('patient')
            ->whereDate('queue_date', $today)
            ->where('status', Queue::STATUS_SIAP_DIAMBIL)
            ->latest('pharmacist_verified_at')
            ->get();

        return view('display.monitor', compact('polyclinics', 'activePerPoli', 'pharmacyReady'));
    }

    /**
     * Data JSON untuk Live Refresh Monitor
     */
    public function liveData(): JsonResponse
    {
        // Bersihkan antrian yang sudah kadaluarsa (>24 jam)
        Queue::autoExpireOutdatedQueues();

        $polyclinics = Polyclinic::where('is_active', true)->get();
        $today = Carbon::today();

        $data = [];
        foreach ($polyclinics as $poli) {
            $current = Queue::with('doctor')
                ->where('polyclinic_id', $poli->id)
                ->whereDate('queue_date', $today)
                ->whereIn('status', [Queue::STATUS_MENUNGGU_DOKTER, Queue::STATUS_PEMERIKSAAN_DOKTER])
                ->latest('updated_at')
                ->first();

            $totalWaiting = Queue::where('polyclinic_id', $poli->id)
                ->whereDate('queue_date', $today)
                ->whereIn('status', [Queue::STATUS_MENUNGGU_SUSTER, Queue::STATUS_MENUNGGU_DOKTER])
                ->count();

            $data[] = [
                'poli_id' => $poli->id,
                'poli_name' => $poli->name,
                'current_code' => $current?->queue_code ?? '-',
                'status_label' => $current?->status_label ?? 'Menunggu Pasien',
                'doctor_name' => $current?->doctor?->name ?? 'Dokter Jaga',
                'waiting_count' => $totalWaiting,
            ];
        }

        $pharmacyReady = Queue::whereDate('queue_date', $today)
            ->where('status', Queue::STATUS_SIAP_DIAMBIL)
            ->latest('pharmacist_verified_at')
            ->pluck('queue_code');

        return response()->json([
            'polyclinics' => $data,
            'pharmacy_ready' => $pharmacyReady,
            'updated_at' => Carbon::now()->format('H:i:s'),
        ]);
    }
}
