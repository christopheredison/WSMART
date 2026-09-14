<?php

namespace App\Exports;

use App\Models\IdentifikasiRisiko;
use App\Models\Periode;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\Unit\ProfilRisikoSheet;
use App\Exports\Sheets\Unit\RisikoInherentKuantitatifSheet;
use App\Exports\Sheets\Unit\RisikoInherentKualitatifSheet;
use App\Exports\Sheets\Unit\RisikoResidualKuantitatifSheet;
use App\Exports\Sheets\Unit\RisikoResidualKualitatifSheet;
use App\Exports\Sheets\Unit\RencanaPerlakuanRisikoSheet;
use App\Exports\Sheets\Unit\RealisasiResidualSheet;

class LaporanUnitExport implements WithMultipleSheets
{
    protected int $periodeId;
    protected array $unitIds;
    protected $bulan;
    protected $tahun;
    protected bool $includeUnitColumn;
    protected string $unitColumnLabel;

    public function __construct(
        int $periodeId,
        array $unitIds,
        $bulan = null,
        bool $includeUnitColumn = false,
        string $unitColumnLabel = 'Nama Divisi'
    ) {
        $this->periodeId = $periodeId;
        $this->unitIds = $unitIds;
        $this->bulan = $bulan;
        $this->includeUnitColumn = $includeUnitColumn;
        $this->unitColumnLabel = $unitColumnLabel;

        $periode = Periode::find($periodeId);
        $this->tahun = $periode ? $periode->tahun : null;
    }

    public function sheets(): array
    {
        $semuaRisiko = IdentifikasiRisiko::with([
            'unit',
            'periode',
            'kategoriRisiko',
            'jenisRisiko',
            'peristiwaRisiko',
            'penyebabRisiko.perlakuanPenyebabRisikoUnit.perlakuanPenyebabUnitMonitorings' => function ($q) {
                $q->whereHas('unitRiskMonitoring', function ($sq) {
                    $sq->where('status', 100)->where('is_approved', 1);
                    if ($this->bulan) {
                        $sq->where('month', '<=', $this->bulan);
                    }
                });
                $q->orderBy('id', 'desc');
            },
            'dampakRisikos.perlakuanDampakRisikos.perlakuanDampakMonitorings' => function ($q) {
                $q->whereHas('unitRiskMonitoring', function ($sq) {
                    $sq->where('status', 100)->where('is_approved', 1);
                    if ($this->bulan) {
                        $sq->where('month', '<=', $this->bulan);
                    }
                });
                $q->orderBy('id', 'desc');
            },
            'kris.kriUnitMonitorings' => function ($q) {
                $q->whereHas('unitRiskMonitoring', function ($sq) {
                    $sq->where('status', 100)->where('is_approved', 1);
                    if ($this->bulan) {
                        $sq->where('month', '<=', $this->bulan);
                    }
                });
                $q->orderBy('id', 'desc');
            },
            'kontrolEksistings',
            'jenisKontrolEksisting',
            'penilaianEfektifitasKontrol',
            'riskAnalysis.skalaDampakObj',
            'riskAnalysis.skalaProbabilitas',
            'riskAnalysis.areaDampakObj',
            'riskAnalysis.skalaDampakResidualQ1Obj',
            'riskAnalysis.skalaProbabilitasResidualQ1',
            'riskAnalysis.skalaDampakResidualQ2Obj',
            'riskAnalysis.skalaProbabilitasResidualQ2',
            'riskAnalysis.skalaDampakResidualQ3Obj',
            'riskAnalysis.skalaProbabilitasResidualQ3',
            'riskAnalysis.skalaDampakResidualQ4Obj',
            'riskAnalysis.skalaProbabilitasResidualQ4',
            'monitoringRisikos' => function ($query) {
                $query->where('status', 100)->where('is_approved', 1);
                if ($this->bulan) {
                    $query->where('month', '<=', $this->bulan);
                }
                $query->orderBy('month', 'desc')->orderBy('id', 'desc');
            },
            'monitoringRisikos.skalaProbabilitas',
            'monitoringRisikos.skalaDampakObj',
        ])
        ->where('periode_id', $this->periodeId)
        ->whereIn('unit_id', $this->unitIds)
        ->get()
        ->sortBy([
            fn ($risiko) => optional($risiko->unit)->name ?? '',
            fn ($risiko) => -(float) (optional($risiko->riskAnalysis)->skala_risiko ?? 0),
        ])
        ->values();

        $sheetArgs = [$semuaRisiko, $this->bulan, $this->includeUnitColumn, $this->unitColumnLabel];

        return [
            new ProfilRisikoSheet(...$sheetArgs),
            new RisikoInherentKuantitatifSheet($semuaRisiko, $this->includeUnitColumn, $this->unitColumnLabel),
            new RisikoInherentKualitatifSheet($semuaRisiko, $this->includeUnitColumn, $this->unitColumnLabel),
            new RisikoResidualKuantitatifSheet($semuaRisiko, $this->includeUnitColumn, $this->unitColumnLabel),
            new RisikoResidualKualitatifSheet($semuaRisiko, $this->includeUnitColumn, $this->unitColumnLabel),
            new RencanaPerlakuanRisikoSheet($semuaRisiko, $this->includeUnitColumn, $this->unitColumnLabel),
            new RealisasiResidualSheet(...$sheetArgs),
        ];
    }
}
