<?php

namespace App\Exports;

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

    public function __construct(int $periodeId, int $unitId)
    {
        $this->periodeId = $periodeId;
        $this->unitId = $unitId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [
            new ProfilRisikoSheet($this->periodeId, $this->unitId),
            new RisikoInherentKuantitatifSheet($this->periodeId, $this->unitId),
            new RisikoInherentKualitatifSheet($this->periodeId, $this->unitId),
            new RisikoResidualKuantitatifSheet($this->periodeId, $this->unitId),
            new RisikoResidualKualitatifSheet($this->periodeId, $this->unitId),
            new RencanaPerlakuanRisikoSheet($this->periodeId, $this->unitId),
            new RealisasiResidualSheet($this->periodeId, $this->unitId),
        ];

        return $sheets;
    }
}