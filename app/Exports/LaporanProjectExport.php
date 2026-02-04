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
        $sheets = [
            new ProfilRisikoSheet($this->projectIds),
            new RisikoInherentKuantitatifSheet($this->projectIds),
            new RisikoInherentKualitatifSheet($this->projectIds),
            new RisikoResidualKuantitatifSheet($this->projectIds),
            new RisikoResidualKualitatifSheet($this->projectIds),
            new RencanaPerlakuanRisikoSheet($this->projectIds),
            new RealisasiResidualSheet($this->projectIds),
        ];

        return $sheets;
    }
}
