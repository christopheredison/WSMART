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

    public function __construct(?array $projectIds, $bulan = null, $tahun = null)
    {
        $this->projectIds = $projectIds;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Monitoring yang dipakai: publish terakhir sampai bulan terpilih.
        // Jika bulan terpilih belum publish, fallback ke bulan sebelumnya yang sudah publish.
        $filterUpToPeriod = function($q) {
            if ($this->bulan && $this->tahun) {
                // Kolom month bertipe teks, sehingga "<=" dibandingkan secara
                // leksikal ("2" > "10"). Pakai daftar bulan agar Oktober–Desember
                // tidak kehilangan data monitoring.
                $bulanSampai = range(1, (int) $this->bulan);
                $q->where(function($query) use ($bulanSampai) {
                    $query->where('tahun', '<', $this->tahun)
                          ->orWhere(function($subQuery) use ($bulanSampai) {
                              $subQuery->where('tahun', $this->tahun)
                                      ->whereIn('month', $bulanSampai);
                          });
                });
            }
            $q->where(function($sq) {
                $sq->where('status', 100)->orWhere('is_approved', 1)->orWhere('is_approved', true);
            });
        };

        // QUERY MASTER
        $semuaRisikoQuery = ProjectRisk::with([
            'projectPeriodeList.project',
            'peristiwaRisiko',
            'projectRiskAnalisa.skalaDampakObj',
            'projectRiskAnalisa.skalaProbabilitas',
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
            'penilaianEfektivitasKontrolObj',
            'projectRiskMonitorings' => function($query) use ($filterUpToPeriod) {
                $filterUpToPeriod($query);
                // Pastikan yang ditarik adalah bulan terbaru/terakhir yang memenuhi syarat
                $query->orderBy('tahun', 'desc')->orderBy('month', 'desc')->orderBy('id', 'desc');
            },
            'projectRiskMonitorings.skalaProbabilitas',
            'projectRiskMonitorings.skalaDampakObj'
        ])
        ->whereNull('deleted_at');

        // Optimasi: jika projectIds null artinya "all projects", hindari whereIn ribuan ID.
        if (is_array($this->projectIds)) {
            $semuaRisikoQuery->whereIn('project_id', $this->projectIds);
        }

        $semuaRisiko = $semuaRisikoQuery->get();

        if (is_array($this->projectIds) && count($this->projectIds) === 1) {
            $singleProjectId = $this->projectIds[array_key_first($this->projectIds)];
            $sheets[] = new ResumeProjectSheet($singleProjectId, $this->bulan, $this->tahun);
        }

        $sheets[] = new ProfilRisikoSheet($semuaRisiko, $this->bulan, $this->tahun);
        $sheets[] = new RisikoInherentKuantitatifSheet($semuaRisiko);
        // $sheets[] = new RisikoInherentKualitatifSheet($semuaRisiko);
        $sheets[] = new RisikoResidualKuantitatifSheet($semuaRisiko);
        // $sheets[] = new RisikoResidualKualitatifSheet($semuaRisiko);
        $sheets[] = new RencanaPerlakuanRisikoSheet($semuaRisiko);
        $sheets[] = new RealisasiResidualSheet($semuaRisiko);

        return $sheets;
    }
}
