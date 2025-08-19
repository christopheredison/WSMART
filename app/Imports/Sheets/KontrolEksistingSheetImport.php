<?php
namespace App\Imports\Sheets;

use App\Imports\ProjectTenderImport;
use App\Models\ProjectKontrolEksisting;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KontrolEksistingSheetImport implements ToCollection, WithHeadingRow
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

            if (empty($noRisiko) || empty($row['kontrol_eksisting'])) {
                // $this->parent->skippedCount++;
                continue;
            }

            try {
                $projectRiskId = $this->parent->riskMapping[$noRisiko] ?? null;
                if (!$projectRiskId) {
                    $this->parent->failedCount++;
                    $this->parent->failedRows[] = "Kontrol Eksisting Baris $rowNumber: No Risiko '$noRisiko' tidak ditemukan di sheet Risiko Tender.";
                    continue;
                }

                ProjectKontrolEksisting::create([
                    'project_risk_id' => $projectRiskId,
                    'kontrol_eksisting_desc' => trim($row['kontrol_eksisting']),
                ]);
            } catch (\Exception $e) {
                $this->parent->failedCount++;
                $this->parent->failedRows[] = "Kontrol Eksisting Baris $rowNumber: " . $e->getMessage();
            }
        }
    }
}