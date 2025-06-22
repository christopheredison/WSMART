<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function refreshNilai()
    {
        $this->load([
            'projectRisks.projectRiskAnalisa',
            'projectRisks.projectRiskMonitorings' => function ($query) {
                $query->orderBy('id', 'desc');
            },
        ]);

        $currentData = [];

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

        $skalaRisikoQ1 = round(collect($currentData['risks'])->avg('nilai_q1'));
        $levelRisikoQ1 = $riskmaps[$skalaRisikoQ1];
        $skalaRisikoQ2 = round(collect($currentData['risks'])->avg('nilai_q2'));
        $levelRisikoQ2 = $riskmaps[$skalaRisikoQ2];
        $skalaRisikoQ3 = round(collect($currentData['risks'])->avg('nilai_q3'));
        $levelRisikoQ3 = $riskmaps[$skalaRisikoQ3];
        $skalaRisikoQ4 = round(collect($currentData['risks'])->avg('nilai_q4'));
        $levelRisikoQ4 = $riskmaps[$skalaRisikoQ4];

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
        if ($risk_limit <= 0) {
            return 5; // Jika risk limit 0 atau negatif, default ke High (5)
        }

        $persentase = ($nilai_dampak / $risk_limit) * 100;

        if ($persentase <= 20) return 1; // Low
        if ($persentase > 20 && $persentase <= 40) return 2; // Low to Moderate
        if ($persentase > 40 && $persentase <= 60) return 3; // Moderate
        if ($persentase > 60 && $persentase <= 80) return 4; // Moderate to High
        return 5; // High
    }

    public function recalculateAnalisa($risk_limit)
    {
        $projectRisks = ProjectRisk::where('periode_id', $this->periode_id)
            ->where('project_id', $this->project_id)
            ->where('id', '!=', request()->route('risk')) // Kecualikan ID yang sedang diproses
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
}
