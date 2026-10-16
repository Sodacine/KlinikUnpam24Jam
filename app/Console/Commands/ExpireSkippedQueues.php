<?php

namespace App\Console\Commands;

use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireSkippedQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:expire-skipped
                            {--dry-run : Tampilkan antrian yang akan di-expire tanpa menghapusnya}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus otomatis antrian berstatus "dilewati" yang sudah melewati 24 jam (expired_at)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        // Cari antrian dilewati yang sudah expire
        $expiredQueues = Queue::where('status', Queue::STATUS_DILEWATI)
            ->where('expired_at', '<=', $now)
            ->get();

        if ($expiredQueues->isEmpty()) {
            $this->info('Tidak ada antrian dilewati yang perlu di-expire.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("Mode DRY-RUN: {$expiredQueues->count()} antrian akan di-expire:");
            foreach ($expiredQueues as $q) {
                $this->line("  - {$q->queue_code} | {$q->patient->name} | Expired: {$q->expired_at}");
            }
            return self::SUCCESS;
        }

        // Hapus antrian yang sudah expire (soft delete tidak dipakai, langsung delete dari tabel antrian aktif)
        // Atau bisa juga tandai sebagai selesai dengan catatan expired
        $count = 0;
        foreach ($expiredQueues as $q) {
            $q->delete();
            $count++;
            $this->line("  ✓ Dihapus: {$q->queue_code} ({$q->patient->name})");
        }

        $this->info("Berhasil menghapus {$count} antrian yang telah kadaluarsa (>24 jam).");

        return self::SUCCESS;
    }
}
