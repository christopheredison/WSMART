<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectRiskMonitoring;

class CalculateEfektivitasMonitoringSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Memulai perhitungan efektivitas risiko...');

        ProjectRiskMonitoring::with(['risiko.projectRiskAnalisa'])
            ->chunk(200, function ($monitorings) {
                foreach ($monitorings as $monitoring) {

                    // 1. Cek Data Analisa (Inherent & Target)
                    $analisa = $monitoring->risiko->projectRiskAnalisa ?? null;
                    if (!$analisa) continue;

                    // 2. Cek Data Realisasi (Monitoring)
                    // Jika belum ada skala risiko realisasi (masih draft/kosong), skip.
                    if (empty($monitoring->skala_risiko)) {
                        continue;
                    }

                    $level_inherent = (float) $analisa->skala_risiko;
                    $level_rencana  = (float) $analisa->skala_risiko_residual;
                    $level_realisasi = (float) $monitoring->skala_risiko;

                    $penyebut = $level_inherent - $level_rencana;
                    $efektivitas = 0.0;

                    if ($penyebut != 0) {
                        $pembilang = $level_rencana - $level_realisasi;
                        $efektivitas = ($pembilang / $penyebut) * 100;
                    } else {
                        if ($level_realisasi <= $level_rencana) {
                            $efektivitas = 100;
                        } else {
                            $efektivitas = 0;
                        }
                    }

                    $monitoring->update([
                        'efektivitas_perlakuan_risiko' => round($efektivitas, 2)
                    ]);
                }
            });

        $this->command->info('Perhitungan efektivitas selesai!');
    }
}
