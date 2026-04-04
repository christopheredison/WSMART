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
    protected $periodeId;
    protected $unitId;
    protected $bulan;
    protected $tahun;

    public function __construct(int $periodeId, int $unitId, $bulan = null)
    {
        $this->periodeId = $periodeId;
        $this->unitId = $unitId;
        $this->bulan = $bulan;

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
            'penyebabRisiko.perlakuanPenyebabRisiko.lastMonitoring' => function($q) {
                if ($this->bulan && $this->tahun) {
                    $q->whereHas('perlakuanPenyebabMonitorings', function($sq) {
                        $sq->where('month', $this->bulan)->where('tahun', $this->tahun);
                    });
                }
            },
            'dampakRisikos.perlakuanDampakRisikos.lastMonitoring' => function($q) {
                if ($this->bulan && $this->tahun) {
                    $q->whereHas('perlakuanDampakMonitorings', function($sq) {
                        $sq->where('month', $this->bulan)->where('tahun', $this->tahun);
                    });
                }
            },
            'kris',
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
            'monitoringRisikos' => function($query) {
                if ($this->bulan && $this->tahun) {
                    $query->where('month', $this->bulan)->where('tahun', $this->tahun);
                }
                $query->orderBy('tahun', 'desc')->orderBy('month', 'desc')->orderBy('id', 'desc');
            },
            'monitoringRisikos.skalaProbabilitas',
            'monitoringRisikos.skalaDampakObj'
        ])
        ->where('periode_id', $this->periodeId)
        ->where('unit_id', $this->unitId)
        ->get()
        ->sortByDesc('riskAnalysis.skala_risiko');

        $sheets = [
            new ProfilRisikoSheet($semuaRisiko),
            new RisikoInherentKuantitatifSheet($semuaRisiko),
            new RisikoInherentKualitatifSheet($semuaRisiko),
            new RisikoResidualKuantitatifSheet($semuaRisiko),
            new RisikoResidualKualitatifSheet($semuaRisiko),
            new RencanaPerlakuanRisikoSheet($semuaRisiko),
            new RealisasiResidualSheet($semuaRisiko),
        ];

        return $sheets;
    }
}
