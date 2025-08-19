<?php
namespace App\Imports\Sheets;

use App\Imports\ProjectTenderImport;
use App\Models\PenyebabRisikoProject;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class PenyebabRisikoSheetImport implements ToCollection, WithHeadingRow
{
    private $parent;

    public function __construct(ProjectTenderImport $parent)
    {
        $this->parent = $parent;
    }

    public function collection(Collection $rows)
    {
        $currentPenyebab = null;

        foreach ($rows as $index => $row) {
            if ($index == 0) {
                continue;
            }
            
            $rowNumber = $index + 2;

            try {
                if (!empty(trim($row['no_risiko'])) && !empty(trim($row['penyebab_risiko']))) {
                    $noRisiko = trim($row['no_risiko']);
                    
                    $projectRiskId = $this->parent->riskMapping[$noRisiko] ?? null;

                    if (!$projectRiskId) {
                        $this->parent->failedCount++;
                        $this->parent->failedRows[] = "Penyebab Risiko Baris $rowNumber: No Risiko '$noRisiko' tidak ditemukan di sheet 'Risiko Tender'.";
                        $currentPenyebab = null;
                        continue;
                    }

                    $currentPenyebab = PenyebabRisikoProject::create([
                        'risiko_id' => $projectRiskId,
                        'penyebab_risiko' => trim($row['penyebab_risiko']),
                    ]);
                }

                if (!empty(trim($row['perlakuan_risiko']))) {
                    if ($currentPenyebab) {
                        $currentPenyebab->perlakuanPenyebabRisiko()->create([
                            'penyebab_risiko_id' => $currentPenyebab->id,
                            'rencana_perlakuan_risiko' => trim($row['perlakuan_risiko']),
                            'biaya_perlakuan_risiko' => $this->parent->cleanRupiah($row['biaya_perlakuan'] ?? 0),
                        ]);
                    } else {
                        $this->parent->failedCount++;
                        $this->parent->failedRows[] = "Penyebab Risiko Baris $rowNumber: Perlakuan Risiko ditemukan tanpa ada 'Penyebab Risiko' yang valid di atasnya.";
                    }
                }
                
            } catch (\Exception $e) {
                $this->parent->failedCount++;
                $this->parent->failedRows[] = "Penyebab Risiko Baris $rowNumber: Terjadi error - " . $e->getMessage();
                Log::error("Import Error on Penyebab Risiko Row $rowNumber: " . $e->getMessage());
                $currentPenyebab = null;
            }
        }
    }
}