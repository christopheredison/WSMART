<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IdentifikasiRisiko;
use Illuminate\Support\Facades\Log;

class SyncClosedAtUnitRisks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unit-risks:sync-closed-at';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set closed_at pada identifikasi_risikos berdasarkan updated_at dari unit_risk_monitorings terakhir (month tertinggi, id terbaru) dan mencatatkan log perubahannya.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses sinkronisasi closed_at untuk Unit Risks (Divisi/Anper)...');

        // Buat custom logger on-the-fly agar rapi
        $logger = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/sync_unit_risks_closed_at.log'),
        ]);

        $logger->info("=== START SYNC UNIT RISKS: " . now() . " ===");

        // Hitung total data untuk progress bar
        $totalData = IdentifikasiRisiko::where('is_closed', true)->orWhere('is_closed', 1)->count();

        if ($totalData === 0) {
            $this->info('Tidak ada data risiko divisi/anper dengan status is_closed = true.');
            $logger->info("=== END SYNC: Tidak ada data yang diproses ===");
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalData);
        $bar->start();

        $updatedCount = 0;
        $skippedCount = 0;

        // Menggunakan chunkById agar aman untuk data skala besar
        IdentifikasiRisiko::where(function ($query) {
                $query->where('is_closed', true)
                        ->orWhere('is_closed', 1);
            })
            ->chunkById(100, function ($risks) use ($bar, $logger, &$updatedCount, &$skippedCount) {
                foreach ($risks as $risk) {
                    
                    // Ambil data monitoring dengan month tertinggi, jika sama ambil id terbaru
                    $latestMonitoring = $risk->monitoringRisikos()
                        ->orderBy('month', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($latestMonitoring) {
                        $closedAtValue = $latestMonitoring->updated_at;

                        // Tulis log perubahan detail dalam format array / object data monitoring
                        $logger->info("Update Unit Risk ID: {$risk->id}", [
                            'risk_id' => $risk->id,
                            'peristiwa_risiko' => $risk->peristiwa_risiko ?? $risk->deskripsi_peristiwa_risiko,
                            'last_monitoring' => [
                                'monitoring_id' => $latestMonitoring->id,
                                'quarter' => $latestMonitoring->quarter,
                                'month' => $latestMonitoring->month,
                                'tahun' => $latestMonitoring->tahun ?? ($latestMonitoring->identifikasiRisiko->periode->tahun ?? null),
                                'updated_at' => $latestMonitoring->updated_at->toDateTimeString(),
                            ],
                            'closed_at' => $closedAtValue->toDateTimeString()
                        ]);

                        $risk->closed_at = $closedAtValue;
                        $risk->saveQuietly(); // Mencegah pembaruan pada kolom updated_at bawaan tabel risiko
                        
                        $updatedCount++;
                    } else {
                        // Catat log jika tidak ditemukan objek monitorings pasangannya
                        $logger->warning("Skip Unit Risk ID: {$risk->id}", [
                            'risk_id' => $risk->id,
                            'peristiwa_risiko' => $risk->peristiwa_risiko ?? $risk->deskripsi_peristiwa_risiko,
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
        $this->info("Proses selesai! Berhasil memperbarui {$updatedCount} risiko divisi/anper.");
        if ($skippedCount > 0) {
            $this->warn("Terdapat {$skippedCount} data yang di-skip karena tidak ada data monitoring.");
        }
        $this->line("Detail perubahan dapat dilihat pada file: <comment>storage/logs/sync_unit_risks_closed_at.log</comment>");

        return Command::SUCCESS;
    }
}