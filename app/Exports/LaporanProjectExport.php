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

        // Buat Closure Filter untuk Cutoff Bulan & Status Publish
        $filterUpToPeriod = function($q) {
            if ($this->bulan && $this->tahun) {
                $q->where(function($query) {
                    // Ambil tahun sebelumnya ATAU tahun yang sama dengan bulan <= bulan terpilih
                    $query->where('tahun', '<', $this->tahun)
                          ->orWhere(function($subQuery) {
                              $subQuery->where('tahun', $this->tahun)
                                      ->where('month', '<=', $this->bulan);
                          });
                });
            }
            // Filter hanya yang sudah ter-publish
            $q->where(function($sq) {
                $sq->where('status', 100)->orWhere('is_approved', 1)->orWhere('is_approved', true);
            });
        };

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
            'penyebabRisikoProjects.perlakuanPenyebabRisiko.lastMonitoring' => function($q) use ($filterUpToPeriod) {
                $q->whereHas('projectMonitoring', $filterUpToPeriod);
            },
            'perlakuanDampakRisikos.lastMonitoring' => function($q) use ($filterUpToPeriod) {
                $q->whereHas('projectMonitoring', $filterUpToPeriod);
            },
            'kriProjects.kriProjectMonitorings' => function($q) use ($filterUpToPeriod) {
                $q->whereHas('projectMonitoring', $filterUpToPeriod)->orderBy('id', 'desc');
            },
            'dampakRisikoProjects',
            'jenisKontrolEksisting',
            'projectKontrolEksistings',
            'penilaianEfektivitasKontrolObj',
            'projectRiskMonitorings' => function($query) use ($filterUpToPeriod) {
                $filterUpToPeriod($query);
                // Pastikan yang ditarik adalah bulan terbaru/terakhir yang memenuhi syarat
                $query->orderBy('tahun', 'desc')->orderBy('month', 'desc')->orderBy('id', 'desc');
            },
            'projectRiskMonitorings.skalaProbabilitas',
            'projectRiskMonitorings.skalaDampakObj'
        ])
        ->whereHas('projectRiskMonitorings', $filterUpToPeriod)
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
