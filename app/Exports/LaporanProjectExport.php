<?php

namespace App\Exports;

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

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        if (count($this->projectIds) === 1) {
            $singleProjectId = $this->projectIds[array_key_first($this->projectIds)];
            $sheets[] = new ResumeProjectSheet($singleProjectId, $this->bulan, $this->tahun);
        }

        $sheets[] = new ProfilRisikoSheet($this->projectIds, $this->bulan, $this->tahun);
        // $sheets[] = new RisikoInherentKuantitatifSheet($this->projectIds, $this->bulan, $this->tahun);
        // $sheets[] = new RisikoInherentKualitatifSheet($this->projectIds, $this->bulan, $this->tahun);
        // $sheets[] = new RisikoResidualKuantitatifSheet($this->projectIds, $this->bulan, $this->tahun);
        // $sheets[] = new RisikoResidualKualitatifSheet($this->projectIds, $this->bulan, $this->tahun);
        // $sheets[] = new RencanaPerlakuanRisikoSheet($this->projectIds, $this->bulan, $this->tahun);
        // $sheets[] = new RealisasiResidualSheet($this->projectIds, $this->bulan, $this->tahun);

        return $sheets;
    }
}
