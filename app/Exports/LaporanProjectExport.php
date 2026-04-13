<?php

namespace App\Exports;

use App\Models\ProjectRisk;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\Project\ProfilRisikoSheet;
use App\Exports\Sheets\Project\RisikoInherentKuantitatifSheet;
use App\Exports\Sheets\Project\RisikoInherentKualitatifSheet;
use App\Exports\Sheets\Project\RisikoResidualKuantitatifSheet;
use App\Exports\Sheets\Project\RisikoResidualKualitatifSheet;
use App\Exports\Sheets\Project\RencanaPerlakuanRisikoSheet;
use App\Exports\Sheets\Project\RealisasiResidualSheet;
use App\Exports\Sheets\Project\ResumeProjectSheet;

class LaporanProjectExport implements WithMultipleSheets
{
    protected $projectIds;
    protected $bulan;
    protected $tahun;

    public function __construct(array $projectIds, $bulan = null, $tahun = null)
    {
        $this->projectIds = $projectIds;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function sheets(): array
    {
        $sheets = [];

        // QUERY MASTER
        $semuaRisiko = ProjectRisk::with([
            'wbsMaster',
            'projectPeriodeList.project',
            'peristiwaRisiko',
            'projectRiskAnalisa.skalaDampakObj',
            'projectRiskAnalisa.skalaProbabilitas',
            'projectRiskAnalisa.areaDampakObj',
            'projectRiskAnalisa.skalaDampakResidualObj',
            'projectRiskAnalisa.skalaProbabilitasResidual',
            'penyebabRisikoProjects.perlakuanPenyebabRisiko.lastMonitoring' => function($q) {
                if ($this->bulan && $this->tahun) {
                    $q->whereHas('projectMonitoring', function($sq) {
                        $sq->where('month', $this->bulan)->where('tahun', $this->tahun)->where('status', 100);
                    });
                }
            },
            'perlakuanDampakRisikos.lastMonitoring' => function($q) {
                if ($this->bulan && $this->tahun) {
                    $q->whereHas('projectMonitoring', function($sq) {
                        $sq->where('month', $this->bulan)->where('tahun', $this->tahun)->where('status', 100);
                    });
                }
            },
            'kriProjects.kriProjectMonitorings',
            'dampakRisikoProjects',
            'jenisKontrolEksisting',
            'projectKontrolEksistings',
            'penilaianEfektivitasKontrolObj',
            'projectRiskMonitorings' => function($query) {
                if ($this->bulan && $this->tahun) {
                    $query->where('month', $this->bulan)->where('tahun', $this->tahun);
                }
                $query->where('status', 100)
                      ->orderBy('tahun', 'desc')->orderBy('month', 'desc')->orderBy('id', 'desc');
            },
            'projectRiskMonitorings.skalaProbabilitas',
            'projectRiskMonitorings.skalaDampakObj'
        ])
        ->whereHas('projectRiskMonitorings', function($query) {
            if ($this->bulan && $this->tahun) {
                $query->where('month', $this->bulan)->where('tahun', $this->tahun);
            }
            $query->where('status', 100);
        })
        ->whereIn('project_id', $this->projectIds)
        ->get();

        if (count($this->projectIds) === 1) {
            $singleProjectId = $this->projectIds[array_key_first($this->projectIds)];
            $sheets[] = new ResumeProjectSheet($singleProjectId, $this->bulan, $this->tahun);
        }

        $sheets[] = new ProfilRisikoSheet($semuaRisiko);
        $sheets[] = new RisikoInherentKuantitatifSheet($semuaRisiko);
        // $sheets[] = new RisikoInherentKualitatifSheet($semuaRisiko);
        $sheets[] = new RisikoResidualKuantitatifSheet($semuaRisiko);
        // $sheets[] = new RisikoResidualKualitatifSheet($semuaRisiko);
        $sheets[] = new RencanaPerlakuanRisikoSheet($semuaRisiko);
        $sheets[] = new RealisasiResidualSheet($semuaRisiko);

        return $sheets;
    }
}
