<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Imports\Sheets\RisikoTenderSheetImport;
use App\Imports\Sheets\PenyebabRisikoSheetImport;
use App\Imports\Sheets\KriSheetImport;
use App\Imports\Sheets\KontrolEksistingSheetImport;

class ProjectTenderImport implements WithMultipleSheets
{
    public int $successCount = 0;
    public int $skippedCount = 0;
    public int $failedCount = 0;
    public array $skippedRows = [];
    public array $failedRows = [];
    public array $riskMapping = [];
    public $projectPeriodeList;
    public $user;
    public $riskMaps;
    public $skalaProbabilitas;

    public function __construct($projectPeriodeList, $user)
    {
        $this->projectPeriodeList = $projectPeriodeList;
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            'Risiko Tender' => new RisikoTenderSheetImport($this),
            'Penyebab Risiko' => new PenyebabRisikoSheetImport($this),
            'KRI' => new KriSheetImport($this),
            'Kontrol Eksisting' => new KontrolEksistingSheetImport($this),
        ];
    }

    // Helper methods
    public function cleanRupiah($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        return (float) str_replace(['Rp', '.', ',', ' '], ['', '', '.', ''], $value);
    }

    public function hitungSkalaDampak($nilai_dampak, $risk_limit)
    {
        if ($risk_limit <= 0) return 5;
        $persentase = ($nilai_dampak / $risk_limit) * 100;
        if ($persentase <= 20) return 1;
        if ($persentase <= 40) return 2;
        if ($persentase <= 60) return 3;
        if ($persentase <= 80) return 4;
        return 5;
    }

    // Getter methods
    public function getSuccessCount(): int { return $this->successCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
    public function getFailedCount(): int { return $this->failedCount; }
    public function getSkippedRows(): array { return $this->skippedRows; }
    public function getFailedRows(): array { return $this->failedRows; }
}