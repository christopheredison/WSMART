<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UnitRiskMonitoring;
use App\Models\RisklimitPeriode;
use Illuminate\Support\Facades\Log;

class RecalculateEksposurRisiko extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risk:recalculate-eksposur';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hitung ulang eksposur risiko realisasi yang bernilai 0 dan catat perubahannya.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai perhitungan ulang Eksposur Risiko Realisasi...');

        // Buat custom logger on-the-fly agar rapi dan tidak bercampur di laravel.log
        $logger = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/recalculate_eksposur_realisasi.log'),
        ]);

        $logger->info("=== START RECALCULATION: " . now() . " ===");

        // Mengambil total data untuk progress bar
        $totalData = UnitRiskMonitoring::count();
        $bar = $this->output->createProgressBar($totalData);
        $bar->start();

        $updatedCount = 0;

        // Gunakan chunkById agar aman untuk data yang banyak (mencegah memory limit)
        UnitRiskMonitoring::with(['identifikasiRisiko.riskAnalysis', 'identifikasiRisiko.unit', 'identifikasiRisiko.periode'])
            ->chunkById(100, function ($monitorings) use ($bar, $logger, &$updatedCount) {
                
                foreach ($monitorings as $monitoring) {
                    $risk = $monitoring->identifikasiRisiko;
                    
                    // Skip jika relasi data tidak lengkap
                    if (!$risk || !$risk->riskAnalysis) {
                        $bar->advance();
                        continue;
                    }

                    $kategoriDampak = $risk->riskAnalysis->kategori_dampak;
                    $riskLimit = 0;

                    // Ambil Risk Limit dari periode jika Kualitatif
                    if ($kategoriDampak === 'Kualitatif') {
                        $riskLimitPeriode = RisklimitPeriode::where('unit_id', $risk->unit_id)
                            ->where('periode_id', $risk->periode_id)
                            ->first();
                        
                        $riskLimit = $riskLimitPeriode ? $riskLimitPeriode->risk_limit : 0;
                    }

                    $oldValue = $monitoring->eksposure_risiko; // Pastikan penulisan kolom di DB sesuai (pakai 'e')
                    $newValue = 0;

                    // Kalkulasi ulang berdasarkan rumus yang benar
                    if ($kategoriDampak === 'Kualitatif') {
                        $newValue = floatval($monitoring->skala_dampak) * (1/100) * (floatval($monitoring->nilai_probabilitas) / 100) * $riskLimit;
                    } elseif ($kategoriDampak === 'Kuantitatif') {
                        $newValue = floatval($monitoring->nilai_dampak) * (floatval($monitoring->nilai_probabilitas) / 100);
                    }

                    // Hanya update dan log jika nilainya memang berbeda (supaya efisien)
                    if (strval($oldValue) !== strval($newValue)) {
                        $logger->info("Update Monitoring ID: {$monitoring->id}", [
                            'risk_id' => $risk->id,
                            'peristiwa_risiko' => $risk->peristiwa_risiko,
                            'kategori_dampak' => $kategoriDampak,
                            'risk_limit_dipakai' => $riskLimit,
                            'eksposure_risiko_lama' => $oldValue,
                            'eksposure_risiko_baru' => $newValue
                        ]);

                        $monitoring->update(['eksposure_risiko' => $newValue]);
                        $updatedCount++;
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $logger->info("=== END RECALCULATION: Total {$updatedCount} data diupdate ===");

        $this->newLine(2);
        $this->info("Proses selesai! Berhasil memperbarui {$updatedCount} baris data.");
        $this->line("Silakan cek file log di <comment>storage/logs/recalculate_eksposur_realisasi.log</comment> untuk melihat detail perubahannya.");
    }
}