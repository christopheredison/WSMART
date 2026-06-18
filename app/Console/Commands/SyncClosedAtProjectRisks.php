<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectRisk;
use Illuminate\Support\Facades\Log;

class SyncClosedAtProjectRisks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project-risks:sync-closed-at';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set closed_at pada project_risks berdasarkan updated_at monitoring terakhir (month tertinggi) untuk risiko yang is_closed = true dan mencatatkan log perubahannya.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses sinkronisasi closed_at untuk Project Risks...');

        // Buat custom logger on-the-fly agar rapi
        $logger = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/sync_project_risks_closed_at.log'),
        ]);

        $logger->info("=== START SYNC PROJECT RISKS: " . now() . " ===");

        // PERBAIKAN 1: Tambahkan grouping closure pada count agar hasilnya valid
        $totalData = ProjectRisk::where(function ($query) {
            $query->where('is_closed', true)
                  ->orWhere('is_closed', 1);
        })->count();
        
        if ($totalData === 0) {
            $this->info('Tidak ada data risiko proyek dengan status is_closed = true.');
            $logger->info("=== END SYNC: Tidak ada data yang diproses ===");
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalData);
        $bar->start();

        $updatedCount = 0;
        $skippedCount = 0;

        // PERBAIKAN 2: Bungkus where & orWhere menggunakan fungsi closure di sini
        ProjectRisk::where(function ($query) {
                $query->where('is_closed', true)
                      ->orWhere('is_closed', 1);
            })
            ->chunkById(100, function ($risks) use ($bar, $logger, &$updatedCount, &$skippedCount) {
                foreach ($risks as $risk) {
                    
                    // Ambil data monitoring bulanan terakhir
                    $latestMonitoring = $risk->projectRiskMonitorings()
                        ->orderBy('month', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($latestMonitoring) {
                        $closedAtValue = $latestMonitoring->updated_at;

                        // Tulis log perubahan detail sebelum melakukan simpan data
                        $logger->info("Update Project Risk ID: {$risk->id}", [
                            'risk_id' => $risk->id,
                            'peristiwa_risiko' => $risk->deskripsi_peristiwa_risiko ?? $risk->rencana_kegiatan,
                            'last_monitoring' => [
                                'monitoring_id' => $latestMonitoring->id,
                                'quarter' => $latestMonitoring->quarter,
                                'month' => $latestMonitoring->month,
                                'tahun' => $latestMonitoring->tahun,
                                'updated_at' => $latestMonitoring->updated_at->toDateTimeString(),
                            ],
                            'closed_at' => $closedAtValue->toDateTimeString()
                        ]);

                        $risk->closed_at = $closedAtValue;
                        $risk->saveQuietly(); 
                        
                        $updatedCount++;
                    } else {
                        $logger->warning("Skip Project Risk ID: {$risk->id}", [
                            'risk_id' => $risk->id,
                            'peristiwa_risiko' => $risk->deskripsi_peristiwa_risiko ?? $risk->rencana_kegiatan,
                            'reason' => 'Tidak memiliki data monitoring pelaporan sama sekali.'
                        ]);
                        $skippedCount++;
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        
        $logger->info("=== END SYNC: Berhasil update {$updatedCount} data, skip {$skippedCount} data ===");

        $this->newLine(2);
        $this->info("Proses selesai! Berhasil memperbarui {$updatedCount} risiko proyek.");
        if ($skippedCount > 0) {
            $this->warn("Terdapat {$skippedCount} data yang di-skip karena tidak ada data monitoring.");
        }
        $this->line("Detail perubahan dapat dilihat pada file: <comment>storage/logs/sync_project_risks_closed_at.log</comment>");
        
        return Command::SUCCESS;
    }
}