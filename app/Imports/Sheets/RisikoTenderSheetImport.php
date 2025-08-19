<?php
namespace App\Imports\Sheets;

use App\Imports\ProjectTenderImport;
use App\Models\JenisRisiko;
use App\Models\ProjectRisk;
use App\Models\ProjectRiskAnalisa;
use App\Models\RiskMap;
use App\Models\SkalaProbabilitas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;

class RisikoTenderSheetImport implements ToCollection, WithHeadingRow
{
    private $parent;

    public function __construct(ProjectTenderImport $parent)
    {
        $this->parent = $parent;
    }

    public function collection(Collection $rows)
    {
        $riskMaps = RiskMap::all()->keyBy(function ($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        $projectId = $this->parent->projectPeriodeList->project_id;
        $existingDescriptions = ProjectRisk::where('project_id', $projectId)
                                      ->pluck('deskripsi_peristiwa_risiko')
                                      ->map(fn($item) => strtolower(trim($item)))
                                      ->flip();

        foreach ($rows as $index => $row) {
            if ($index == 0) {
                continue; // lanjut ke baris ke-3
            }

            $rowNumber = $index + 2;
            $noRisiko = trim($row['no'] ?? '');

            if (empty($noRisiko)) {
                // $this->parent->skippedCount++;
                // $this->parent->skippedRows[] = "Baris $rowNumber: Dilewati karena kolom 'No' kosong.";
                continue;
            }

            try {
                // 1. Ambil & Validasi Data Awal
                $jenisRisiko = JenisRisiko::find(trim($row['taksonomi']));
                if (!$jenisRisiko) {
                    $this->parent->failedCount++;
                    $this->parent->failedRows[] = "Risiko Tender Baris $rowNumber: Taksonomi ID '{$row['taksonomi']}' tidak ditemukan.";
                    continue;
                }

                // 2. Validasi Deskripsi Peristiwa Risiko tidak boleh duplikat
                $deskripsiPeristiwaRisiko = trim($row['peristiwa_risiko']);
                $deskripsiUntukCek = strtolower($deskripsiPeristiwaRisiko);

                if (isset($existingDescriptions[$deskripsiUntukCek])) {
                    $this->parent->skippedCount++;
                    $this->parent->skippedRows[] = "Risiko Tender Baris $rowNumber: Deskripsi '{$deskripsiPeristiwaRisiko}' sudah ada, data dilewati.";
                    continue;
                }

                // 3. Buat ProjectRisk
                $projectRisk = ProjectRisk::create([
                    'project_periode_list_id' => $this->parent->projectPeriodeList->id,
                    'project_id' => $this->parent->projectPeriodeList->project_id,
                    'user_id' => $this->parent->user->id,
                    'unit_id' => $this->parent->user->unit_id,
                    'unit_type_id' => $this->parent->user->unit_type_id,
                    'periode_id' => 0,
                    'peristiwa_risiko_id' => trim($row['standarisasi_risiko']),
                    'deskripsi_peristiwa_risiko' => trim($row['peristiwa_risiko']),
                    'wbs' => trim($row['wbs']),
                    'jenis_risiko_id' => $jenisRisiko->id,
                    'kategori_risiko_id' => $jenisRisiko->kategori_risiko_id,
                    'perkiraan_waktu_terpapar_risiko_mulai' => isset($row['waktu_mulai_terpapar_risiko']) ? Date::excelToDateTimeObject($row['waktu_mulai_terpapar_risiko'])->format('Y-m-d') : null,
                    'perkiraan_waktu_terpapar_risiko_akhir' => isset($row['waktu_akhir_terpapar_risiko']) ? Date::excelToDateTimeObject($row['waktu_akhir_terpapar_risiko'])->format('Y-m-d') : null,
                    'target_capaian_kinerja' => '',
                    'jenis_kontrol_eksisting_id' => 0,
                    'penilaian_efektifitas_kontrol' => 0,
                    'kontrol_eksisting' => '',
                ]);

                // Simpan mapping untuk sheet lain
                $this->parent->riskMapping[$noRisiko] = $projectRisk->id;

                // 4. Buat atau Update ProjectRiskAnalisa
                $nilaiDampakInherent = $this->parent->cleanRupiah($row['dampak_risiko_inherent_kuantitatif'] ?? 0);
                $kategoriDampak = $nilaiDampakInherent > 0 ? ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF : ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF;
                
                $risk_limit = ($this->parent->projectPeriodeList->project->meta['omset'] ?? 0) * 0.03;
                $risk_tolerance = 0;
                
                // Inherent
                $skalaDampakInherent = $kategoriDampak == 'Kuantitatif' ? $this->parent->hitungSkalaDampak($nilaiDampakInherent, $risk_limit) : trim($row['tingkat_dampak_risiko_inherent']);
                $nilaiProbabilitasInherent = trim($row['nilai_probabilitas_inherent']);
                $skalaProbabilitasInherent = SkalaProbabilitas::getSkalaByValue($nilaiProbabilitasInherent);
                $riskMapInherent = $riskMaps->get($skalaDampakInherent . '-' . $skalaProbabilitasInherent?->tingkat);
                
                $eksposurRisikoInherent = 0;
                $kualitatifRiskLimit = 0;
                if ($kategoriDampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF) {
                    $eksposurRisikoInherent = $nilaiDampakInherent * ($nilaiProbabilitasInherent / 100);
                } elseif ($kategoriDampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF) {
                    $kualitatifRiskLimit = (1 / 100) * $risk_tolerance;
                    $eksposurRisikoInherent = $skalaDampakInherent * ($nilaiProbabilitasInherent / 100) * $kualitatifRiskLimit;
                }

                // Residual
                $nilaiDampakResidual = $this->parent->cleanRupiah($row['dampak_risiko_residual'] ?? 0);
                $skalaDampakResidual = $kategoriDampak == 'Kuantitatif' ? $this->parent->hitungSkalaDampak($nilaiDampakResidual, $risk_limit) : trim($row['tingkat_dampak_risiko_residual']);
                $nilaiProbabilitasResidual = trim($row['nilai_probabilitas_residual']);
                $skalaProbabilitasResidual = SkalaProbabilitas::getSkalaByValue($nilaiProbabilitasResidual);
                $riskMapResidual = $riskMaps->get($skalaDampakResidual . '-' . $skalaProbabilitasResidual?->tingkat);
                
                $eksposurRisikoResidual = 0;
                if ($kategoriDampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF) {
                    $eksposurRisikoResidual = $nilaiDampakResidual * ($nilaiProbabilitasResidual / 100);
                } elseif ($kategoriDampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF) {
                    $kualitatifRiskLimit = (1 / 100) * $risk_tolerance;
                    $eksposurRisikoResidual = $skalaDampakResidual * ($nilaiProbabilitasResidual / 100) * $kualitatifRiskLimit;
                }
                
                $analisaData = [
                    'kategori_dampak' => $kategoriDampak,
                    'risk_limit' => $kategoriDampak === 'Kuantitatif' ? $risk_limit : ($kualitatifRiskLimit ?? 0),
                    'deskripsi_dampak' => trim($row['dampak_risiko_inherent_kualitatif']),
                    'nilai_dampak' => $nilaiDampakInherent,
                    'asumsi_perhitungan_dampak' => trim($row['formula_perhitungan_dampak_risiko_inherent']),
                    'nilai_probabilitas' => $nilaiProbabilitasInherent,
                    'skala_dampak' => $skalaDampakInherent,
                    'skala_probabilitas_id' => $skalaProbabilitasInherent?->id,
                    'skala_risiko' => $riskMapInherent?->nilai_risiko,
                    'level_risiko' => $riskMapInherent?->level_risiko,
                    'eksposur_risiko' => $eksposurRisikoInherent,

                    'deskripsi_dampak_residual' => trim($row['penjelasan_dampak_risiko_residual']),
                    'nilai_dampak_residual' => $nilaiDampakResidual,
                    'nilai_probabilitas_residual' => $nilaiProbabilitasResidual,
                    'skala_dampak_residual' => $skalaDampakResidual,
                    'skala_probabilitas_residual_id' => $skalaProbabilitasResidual?->id,
                    'skala_risiko_residual' => $riskMapResidual?->nilai_risiko,
                    'level_risiko_residual' => $riskMapResidual?->level_risiko,
                    'eksposur_risiko_residual' => $eksposurRisikoResidual,
                ];

                $projectRisk->projectRiskAnalisa()->create($analisaData);
                $projectRisk->update(['skala_risiko' => $riskMapInherent?->nilai_risiko, 'level_risiko' => $riskMapInherent?->level_risiko]);
                
                $this->parent->successCount++;

            } catch (\Exception $e) {
                $this->parent->failedCount++;
                $this->parent->failedRows[] = "Risiko Tender Baris $rowNumber: " . $e->getMessage();
                Log::error("Import Error on Risiko Tender Row $rowNumber: " . $e->getMessage());
            }
        }
    }
}