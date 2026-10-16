<?php

namespace App\Http\Controllers;

use App\Models\Polyclinic;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasienController extends Controller
{
    /**
     * Dashboard Pasien
     */
    public function dashboard(): View
    {
        $user = Auth::user();

        // 4 Poliklinik resmi klinik Unpam 24 Jam
        $polyclinics = Polyclinic::where('is_active', true)->get();

        // Cari antrian aktif pasien hari ini (yang belum selesai)
        $activeQueue = Queue::with(['polyclinic', 'doctor'])
            ->where('patient_id', $user->id)
            ->whereDate('queue_date', Carbon::today())
            ->where('status', '!=', Queue::STATUS_SELESAI)
            ->latest()
            ->first();

        // Hitung estimasi antrian di depan jika ada antrian aktif
        $waitingCountAhead = 0;
        if ($activeQueue) {
            $waitingCountAhead = Queue::where('polyclinic_id', $activeQueue->polyclinic_id)
                ->whereDate('queue_date', Carbon::today())
                ->where('id', '<', $activeQueue->id)
                ->where('status', '!=', Queue::STATUS_SELESAI)
                ->count();
        }

        // 3 Riwayat medis terakhir pasien
        $recentMedicalRecords = Queue::with(['polyclinic', 'doctor'])
            ->where('patient_id', $user->id)
            ->where('status', Queue::STATUS_SELESAI)
            ->orderByDesc('completed_at')
            ->limit(3)
            ->get();

        return view('pasien.dashboard', compact(
            'user',
            'polyclinics',
            'activeQueue',
            'waitingCountAhead',
            'recentMedicalRecords'
        ));
    }

    /**
     * Proses Pembuatan Tiket Antrian Baru
     */
    public function storeQueue(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Cek apakah pasien sudah punya antrian aktif hari ini yang belum selesai
        $existingQueue = Queue::where('patient_id', $user->id)
            ->whereDate('queue_date', Carbon::today())
            ->where('status', '!=', Queue::STATUS_SELESAI)
            ->first();

        if ($existingQueue) {
            return back()->with('error', "Anda masih memiliki antrian aktif ({$existingQueue->queue_code}) di {$existingQueue->polyclinic->name}. Selesaikan terlebih dahulu sebelum membuat antrian baru.");
        }

        $validated = $request->validate([
            'polyclinic_id' => ['required', 'exists:polyclinics,id'],
            'complaint'     => ['required', 'string', 'max:1000'],
        ], [
            'polyclinic_id.required' => 'Silakan pilih salah satu poliklinik.',
            'polyclinic_id.exists'   => 'Poliklinik yang dipilih tidak valid.',
            'complaint.required'     => 'Keluhan utama wajib diisi agar tenaga medis dapat mempersiapkan penanganan.',
        ]);

        $polyclinic = Polyclinic::findOrFail($validated['polyclinic_id']);

        // Hitung nomor urut harian untuk poli tersebut
        $today = Carbon::today();
        $lastQueue = Queue::where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', $today)
            ->orderByDesc('queue_number')
            ->first();

        $nextNumber = $lastQueue ? ($lastQueue->queue_number + 1) : 1;
        $queueCode = sprintf('%s-%03d', $polyclinic->code, $nextNumber);

        $queue = Queue::create([
            'queue_code'    => $queueCode,
            'queue_number'  => $nextNumber,
            'queue_date'    => $today,
            'patient_id'    => $user->id,
            'polyclinic_id' => $polyclinic->id,
            'status'        => Queue::STATUS_MENUNGGU_SUSTER,
            'complaint'     => $validated['complaint'],
        ]);

        return redirect()->route('pasien.dashboard')
            ->with('success', "Tiket antrian Anda berhasil dibuat dengan nomor {$queue->queue_code}. Silakan tunggu panggilan dari Suster.");
    }

    /**
     * Batalkan Antrian (hanya jika masih menunggu_suster)
     */
    public function cancelQueue(Queue $queue): RedirectResponse
    {
        $user = Auth::user();

        if ($queue->patient_id !== $user->id) {
            return back()->with('error', 'Akses tidak sah.');
        }

        if ($queue->status !== Queue::STATUS_MENUNGGU_SUSTER) {
            return back()->with('error', 'Antrian tidak dapat dibatalkan karena sudah dalam proses penanganan medis.');
        }

        $queue->delete();

        return redirect()->route('pasien.dashboard')
            ->with('info', 'Tiket antrian Anda telah dibatalkan.');
    }

    /**
     * Endpoint API JSON untuk Live Polling Status Antrian
     */
    public function getQueueStatus(Queue $queue): JsonResponse
    {
        $user = Auth::user();

        if ($queue->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $queue->load(['polyclinic', 'doctor']);

        $waitingCountAhead = Queue::where('polyclinic_id', $queue->polyclinic_id)
            ->whereDate('queue_date', $queue->queue_date)
            ->where('id', '<', $queue->id)
            ->where('status', '!=', Queue::STATUS_SELESAI)
            ->count();

        return response()->json([
            'id'                => $queue->id,
            'queue_code'        => $queue->queue_code,
            'status'            => $queue->status,
            'status_label'      => $queue->status_label,
            'status_step'       => $queue->status_step,
            'status_badge'      => $queue->status_badge_class,
            'instruction'       => $queue->instruction,
            'polyclinic_name'   => $queue->polyclinic->name,
            'floor_room'        => $queue->polyclinic->floor_room,
            'doctor_name'       => $queue->doctor?->name,
            'waiting_ahead'     => $waitingCountAhead,
            'is_finished'       => $queue->status === Queue::STATUS_SELESAI,
        ]);
    }

    /**
     * Daftar Riwayat Medis Pasien
     */
    public function medicalRecords(Request $request): View
    {
        $user = Auth::user();

        $query = Queue::with(['polyclinic', 'doctor'])
            ->where('patient_id', $user->id)
            ->where('status', Queue::STATUS_SELESAI);

        // Filter pencarian (hanya penyakit, diagnosa, atau kode antrian)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('disease', 'like', "%{$search}%")
                  ->orWhere('diagnosis', 'like', "%{$search}%")
                  ->orWhere('queue_code', 'like', "%{$search}%");
            });
        }

        // Filter poli
        if ($poliId = $request->input('polyclinic_id')) {
            $query->where('polyclinic_id', $poliId);
        }

        $records = $query->orderByDesc('completed_at')->paginate(10)->withQueryString();
        $polyclinics = Polyclinic::all();

        return view('pasien.medical_records', compact('user', 'records', 'polyclinics'));
    }

    /**
     * Detail Satu Rekam Medis Pasien
     */
    public function showMedicalRecord(Queue $queue): View|RedirectResponse
    {
        $user = Auth::user();

        if ($queue->patient_id !== $user->id) {
            return redirect()->route('pasien.riwayat-medis')->with('error', 'Akses tidak sah.');
        }

        if ($queue->status !== Queue::STATUS_SELESAI) {
            return redirect()->route('pasien.riwayat-medis')->with('error', 'Data ini belum berstatus selesai.');
        }

        $queue->load(['polyclinic', 'doctor']);

        return view('pasien.medical_record_detail', compact('queue'));
    }
}
