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
    protected $projectId;

    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            new ProfilRisikoSheet($this->projectId),
            new RisikoInherentKuantitatifSheet($this->projectId),
            new RisikoInherentKualitatifSheet($this->projectId),
            new RisikoResidualKuantitatifSheet($this->projectId),
            new RisikoResidualKualitatifSheet($this->projectId),
            new RencanaPerlakuanRisikoSheet($this->projectId),
            new RealisasiResidualSheet($this->projectId),
        ];

        return $sheets;
    }
}