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

    public function __construct(array $projectIds)
    {
        $this->projectIds = $projectIds;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        if (count($this->projectIds) === 1) {
            $singleProjectId = $this->projectIds[array_key_first($this->projectIds)];
            $sheets[] = new ResumeProjectSheet($singleProjectId);
        }

        $sheets[] = new ProfilRisikoSheet($this->projectIds);
        $sheets[] = new RisikoInherentKuantitatifSheet($this->projectIds);
        $sheets[] = new RisikoInherentKualitatifSheet($this->projectIds);
        $sheets[] = new RisikoResidualKuantitatifSheet($this->projectIds);
        $sheets[] = new RisikoResidualKualitatifSheet($this->projectIds);
        $sheets[] = new RencanaPerlakuanRisikoSheet($this->projectIds);
        $sheets[] = new RealisasiResidualSheet($this->projectIds);

        return $sheets;
    }
}
