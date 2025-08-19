<?php
namespace App\Imports\Sheets;

use App\Imports\ProjectTenderImport;
use App\Models\ProjectRisk;
use App\Models\KRIProject;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KriSheetImport implements ToCollection, WithHeadingRow
{
    private $parent;

    public function __construct(ProjectTenderImport $parent)
    {
        $this->parent = $parent;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if ($index == 0) {
                continue; // lanjut ke baris ke-3
            }

            $rowNumber = $index + 2;
            $noRisiko = trim($row['no_risiko'] ?? '');
            
            if (empty($noRisiko) || empty($row['key_risk_indicator'])) {
                // $this->parent->skippedCount++;
                continue;
            }

            try {
                $projectRiskId = $this->parent->riskMapping[$noRisiko] ?? null;
                if (!$projectRiskId) {
                    $this->parent->failedCount++;
                    $this->parent->failedRows[] = "KRI Baris $rowNumber: No Risiko '$noRisiko' tidak ditemukan di sheet Risiko Tender.";
                    continue;
                }

                KRIProject::create([
                    'risiko_id' => $projectRiskId,
                    'kri'           => trim($row['key_risk_indicator']),
                    'satuan_kri'    => trim($row['satuan_kri']),
                    'batas_aman'    => trim($row['treshold_kri_aman']),
                    'batas_waspada' => trim($row['treshold_kri_hati_hati']),
                    'batas_bahaya'  => trim($row['treshold_kri_bahaya']),
                ]);
            } catch (\Exception $e) {
                $this->parent->failedCount++;
                $this->parent->failedRows[] = "KRI Baris $rowNumber: " . $e->getMessage();
                Log::error("Import Error on KRI Row $rowNumber for No Risiko '$noRisiko': " . $e->getMessage());
            }
        }
    }
}