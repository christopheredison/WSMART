<?php

namespace App\Console\Commands;

use App\Services\ProjectHasilUsahaSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncProjectHasilUsaha extends Command
{
    protected $signature = 'projects:sync-hasil-usaha {--period= : Periode YYYYMM, default bulan berjalan} {--force : Paksa ambil ulang meski data sudah ada}';

    protected $description = 'Sinkronisasi otomatis data hasil usaha / LSP proyek dari API WIKA';

    public function handle(ProjectHasilUsahaSyncService $syncService): int
    {
        $period = $this->option('period') ?: now()->format('Ym');
        $force = (bool) $this->option('force');

        Log::channel('sync_wika_log')->info('[Scheduler Sync Hasil Usaha] Proses dimulai.', [
            'period' => $period,
            'force' => $force,
        ]);

        $this->info("Mulai sinkronisasi hasil usaha proyek periode {$period}...");

        $summary = $syncService->syncAll($period, $force);

        $message = sprintf(
            'Sinkronisasi hasil usaha selesai. Berhasil: %d, Diperbarui dari kosong: %d, Dilewati: %d, Gagal: %d.',
            $summary['success'],
            $summary['refreshed_empty'],
            $summary['skipped'],
            $summary['failed']
        );

        Log::channel('sync_wika_log')->info('[Scheduler Sync Hasil Usaha] ' . $message, $summary);
        $this->info($message);

        if (!empty($summary['failed_rows'])) {
            Log::channel('sync_wika_log')->warning('[Scheduler Sync Hasil Usaha] Detail kegagalan.', [
                'failed_rows' => $summary['failed_rows'],
            ]);
        }

        return self::SUCCESS;
    }
}
