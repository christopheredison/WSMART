<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProjectPeriodeList extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'periode_id',
        'unit_id',
        'risk_limit',
        'skala_risiko',
        'level_risiko',
        'skala_risiko_residual',
        'level_risiko_residual',
        'additional_data',
    ];

    protected $casts = [
        'additional_data' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function projectRisks()
    {
        return $this->hasMany(ProjectRisk::class);
    }

    public function dataBatches()
    {
        return $this->hasMany(DataBatch::class, 'project_id', 'project_id');
    }

    public function latestDataBatch()
    {
        return $this->hasOne(DataBatch::class, 'project_id', 'project_id')
            ->ofMany([
                'batch' => 'max',
                'id' => 'max',
            ], function ($query) {
                $query->where('type', 2)
                    ->whereColumn('periode_id', 'project_periode_lists.periode_id');
            });
    }

    public function recalculateAllRisks()
    {
        // Gunakan NK project yang terbaru
        $nk = (float) ($this->project->nk ?? 0);
        $risk_limit = $nk * 0.03;

        Log::info("ProjectPeriodeList ID: {$this->id}, Project ID: {$this->project_id}");
        Log::info("Nilai Kontrak (NK): {$nk} | Risk Limit (3%): {$risk_limit}");

        // 1. Hitung ulang Analisa (Inherent & Residual)
        $this->recalculateAnalisa($risk_limit);

        // 2. Hitung ulang Monitoring (Realisasi)
        $this->recalculateMonitorings($risk_limit);

        // 3. Refresh akumulasi nilai periode
        $this->refreshNilai();

        Log::info("=== END RECALCULATE ALL RISKS ===");
    }

    public function refreshNilai()
    {
        $this->load([
            'projectRisks.projectRiskAnalisa',
            'projectRisks.projectRiskMonitorings' => function ($query) {
                $query->orderBy('id', 'desc');
            },
        ]);

        $currentData = [
            'risks' => []
        ];

        // inherent
        $avg = $this->projectRisks->avg('skala_risiko');
        if (!is_null($avg)) {
            $risikos = $this->projectRisks;

            $skalaRisiko = round($risikos->average('skala_risiko'));
            $levelRisiko = RiskMap::where('nilai_risiko', $skalaRisiko)->value('level_risiko');
        }

        // residual
        $avg = $this->projectRisks->avg('projectRiskAnalisa.skala_risiko_residual');
        if (!is_null($avg)) {
            $risikos = $this->projectRisks;

            $skalaRisikoResidual = round($risikos->average('projectRiskAnalisa.skala_risiko_residual'));
            $levelRisikoResidual = RiskMap::where('nilai_risiko', $skalaRisikoResidual)->value('level_risiko');
        }

        $riskmaps = RiskMap::select('nilai_risiko', 'level_risiko')->pluck('level_risiko', 'nilai_risiko');

        foreach ($this->projectRisks as $projectRisk) {
            $currentNilai = $projectRisk->skala_risiko;
            for ($i = 1; $i <= 4; $i++) {
                if ($newNilai = $projectRisk->projectRiskMonitorings->where('quarter', $i)->first()?->skala_risiko) {
                    $currentNilai = $newNilai;
                }

                $currentData['risks'][$projectRisk->id]['nilai_q' . $i] = $currentNilai;
            }
        }

        $skalaRisikoQ1 = round(collect($currentData['risks'])->avg('nilai_q1') ?? 0);
        $levelRisikoQ1 = $riskmaps[$skalaRisikoQ1] ?? null;

        $skalaRisikoQ2 = round(collect($currentData['risks'])->avg('nilai_q2') ?? 0);
        $levelRisikoQ2 = $riskmaps[$skalaRisikoQ2] ?? null;

        $skalaRisikoQ3 = round(collect($currentData['risks'])->avg('nilai_q3') ?? 0);
        $levelRisikoQ3 = $riskmaps[$skalaRisikoQ3] ?? null;

        $skalaRisikoQ4 = round(collect($currentData['risks'])->avg('nilai_q4') ?? 0);
        $levelRisikoQ4 = $riskmaps[$skalaRisikoQ4] ?? null;

        $currentData['summary'] = [
            'skala_risiko_q1' => $skalaRisikoQ1,
            'level_risiko_q1' => $levelRisikoQ1,
            'skala_risiko_q2' => $skalaRisikoQ2,
            'level_risiko_q2' => $levelRisikoQ2,
            'skala_risiko_q3' => $skalaRisikoQ3,
            'level_risiko_q3' => $levelRisikoQ3,
            'skala_risiko_q4' => $skalaRisikoQ4,
            'level_risiko_q4' => $levelRisikoQ4,
        ];

        $this->update([
            'skala_risiko' => $skalaRisiko ?? null,
            'level_risiko' => $levelRisiko ?? null,
            'skala_risiko_residual' => $skalaRisikoResidual ?? null,
            'level_risiko_residual' => $levelRisikoResidual ?? null,
            'additional_data' => [
                'data_risiko' => $currentData,
            ],
        ]);
    }

    public function hitungSkalaDampak($nilai_dampak, $risk_limit)
    {
        $limit = (float) $risk_limit;
        $dampak = (float) $nilai_dampak;

        if ($risk_limit <= 0) {
            return 5; // Jika risk limit 0 atau negatif, default ke High (5)
        }

        $persentase = ($dampak / $limit) * 100;

        if ($persentase <= 20) return 1; // Low
        if ($persentase > 20 && $persentase <= 40) return 2; // Low to Moderate
        if ($persentase > 40 && $persentase <= 60) return 3; // Moderate
        if ($persentase > 60 && $persentase <= 80) return 4; // Moderate to High
        return 5; // High
    }

    public function recalculateAnalisa($risk_limit = 0)
    {
        if (!$risk_limit) {
            $risk_limit = ($this->project->nk ?? 0) * 0.03;
        }

        $projectRisks = ProjectRisk::where('periode_id', 0)
            ->where('project_id', $this->project_id)
            ->where('id', '!=', request()->route('risk'))
            ->whereHas('projectRiskAnalisa', function ($query) {
                $query->where('kategori_dampak', 'Kuantitatif');
            })
            ->get();

        foreach ($projectRisks as $projectRisk) {
            $analisa = $projectRisk->projectRiskAnalisa;

            if ($analisa) {
                $nilai_dampak = $analisa->nilai_dampak;
                $nilai_dampak_residual = $analisa->nilai_dampak_residual;

                $skala_dampak_baru = $this->hitungSkalaDampak($nilai_dampak, $risk_limit);
                $skala_dampak_residual_baru = $this->hitungSkalaDampak($nilai_dampak_residual, $risk_limit);
                Log::info("Memproses Risk ID: {$projectRisk->id} | nilai_dampak: {$nilai_dampak} | nilai_dampak_residual: {$nilai_dampak_residual} | skala_dampak_baru: {$skala_dampak_baru} | skala_dampak_residual_baru: {$skala_dampak_residual_baru}");

                $tingkatSkalaProbabilitas = SkalaProbabilitas::getSkalaByValue($analisa->nilai_probabilitas);

                $tingkatSkalaProbabilitasResidual = SkalaProbabilitas::getSkalaByValue($analisa->nilai_probabilitas_residual);

                $riskMaps = RiskMap::get()->keyBy(function($item) {
                    return $item->skala_dampak . '-' . $item->skala_probabilitas;
                });

                $riskMap = $riskMaps[$skala_dampak_baru . '-' . $tingkatSkalaProbabilitas->tingkat] ?? null;

                $riskMapResidual = $riskMaps[$skala_dampak_residual_baru . '-' . $tingkatSkalaProbabilitasResidual->tingkat] ?? null;

                $skala_risiko_baru  = $riskMap->nilai_risiko ?? 1;
                $level_risiko_baru = $riskMap->level_risiko ?? 'Low';

                $skala_risiko_res_baru  = $riskMapResidual->nilai_risiko ?? 1;
                $level_risiko_res_baru = $riskMapResidual->level_risiko ?? 'Low';

                $analisa->update([
                    'skala_dampak' => $skala_dampak_baru,
                    'skala_dampak_residual' => $skala_dampak_residual_baru,
                    'skala_risiko' => $skala_risiko_baru,
                    'level_risiko' => $level_risiko_baru,
                    'skala_risiko_residual' => $skala_risiko_res_baru,
                    'level_risiko_residual' => $level_risiko_res_baru,
                ]);

                $projectRisk->update([
                    'skala_risiko' => $skala_risiko_baru,
                    'level_risiko' => $level_risiko_baru,
                ]);
            }
        }
    }

    public function recalculateMonitorings($risk_limit)
    {
        Log::info("--- START RECALCULATE MONITORINGS ---");

        $risk_limit = (float) $risk_limit;

        $projectRisks = ProjectRisk::where('periode_id', 0)
            ->where('project_id', $this->project_id)
            ->with(['projectRiskAnalisa', 'projectRiskMonitorings'])
            ->get();

        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        foreach ($projectRisks as $projectRisk) {
            $analisa = $projectRisk->projectRiskAnalisa;

            if (!$analisa) {
                Log::info("Risk ID: {$projectRisk->id} dilewati karena tidak memiliki Analisa.");
                continue;
            }

            $skala_risiko_inherent = (float) $analisa->skala_risiko;
            $skala_risiko_rencana = (float) $analisa->skala_risiko_residual;
            $selisih_inherent_rencana = $skala_risiko_inherent - $skala_risiko_rencana;

            Log::info("Memproses Risk ID: {$projectRisk->id} | Kategori: {$analisa->kategori_dampak}");

            $lastMonitoringEfektivitas = 0.0;

            foreach ($projectRisk->projectRiskMonitorings as $monitoring) {
                Log::info("  > Monitoring ID: {$monitoring->id} (Bulan: {$monitoring->month}, Tahun: {$monitoring->tahun})");

                $nilaiDampak = (float) $monitoring->nilai_dampak;
                $nilaiProbabilitas = (float) $monitoring->nilai_probabilitas;

                // --- 1. Tentukan Skala Dampak Baru ---
                if ($analisa->kategori_dampak === 'Kuantitatif') {
                    $skala_dampak_baru = $this->hitungSkalaDampak($nilaiDampak, $risk_limit);
                } else {
                    $skala_dampak_baru = (int) $monitoring->skala_dampak;
                }
                $monitoring->skala_dampak = $skala_dampak_baru;

                // --- 2. Tentukan Skala Probabilitas Baru ---
                $skalaProbObj = \App\Models\SkalaProbabilitas::getSkalaByValue($nilaiProbabilitas);
                $tingkat = $skalaProbObj ? $skalaProbObj->tingkat : 1;
                $monitoring->skala_probabilitas_id = $tingkat;

                // --- 3. Cari Skala Risiko & Level Risiko di RiskMap ---
                $keyMap = $skala_dampak_baru . '-' . $tingkat;
                $riskMap = $riskMaps[$keyMap] ?? null;

                $monitoring->skala_risiko = $riskMap->nilai_risiko ?? 1;
                $monitoring->level_risiko = $riskMap->level_risiko ?? 'Low';

                // --- 4. Hitung Ulang Eksposur Risiko ---
                if ($analisa->kategori_dampak === 'Kuantitatif') {
                    $monitoring->eksposure_risiko = $nilaiDampak * ($nilaiProbabilitas / 100);
                } else {
                    $monitoring->eksposure_risiko = $skala_dampak_baru * (1/100) * $nilaiProbabilitas * $risk_limit;
                }

                // --- 5. HITUNG EFEKTIVITAS ---
                $efektivitas = 0.0;
                $skala_risiko_realisasi = (float) $monitoring->skala_risiko;

                if ($selisih_inherent_rencana != 0) {
                    $efektivitas = (($skala_risiko_rencana - $skala_risiko_realisasi) / $selisih_inherent_rencana) * 100;
                }

                $monitoring->efektivitas_perlakuan_risiko = round($efektivitas, 2);
                $monitoring->save();

                $lastMonitoringEfektivitas = round($efektivitas, 2);
            }

            if ($projectRisk->projectRiskMonitorings->isNotEmpty()) {
                $projectRisk->update([
                    'efektivitas_perlakuan_risiko' => $lastMonitoringEfektivitas
                ]);
            }
        }

        Log::info("--- END RECALCULATE MONITORINGS ---");
    }
}
