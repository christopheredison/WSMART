<?php

namespace App\Exports\Sheets\Project;

use App\Models\ProjectRisk;
use App\Models\ProjectRiskAnalisa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class RealisasiResidualSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    private $projectIds;

    public function __construct(array $projectIds)
    {
        $this->projectIds = $projectIds;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Realisasi Residual';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * Mendaftarkan event untuk memanipulasi sheet.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 2);

                // Row 1: Header utama
                $sheet->setCellValue('A1', 'Jenis Data');
                $sheet->setCellValue('B1', 'No');
                $sheet->setCellValue('C1', 'Nama Project');
                $sheet->setCellValue('D1', 'No Risiko');
                $sheet->setCellValue('E1', 'Peristiwa Risiko');
                $sheet->setCellValue('F1', 'Realisasi Risiko Residual');

                $sheet->setCellValue('N1', 'Nilai Efektivitas');
                $sheet->setCellValue('O1', 'Efektifitas Perlakuan Risiko');

                // Row 2: Sub-header kategori realisasi
                $sheet->setCellValue('F2', 'Asumsi Perhitungan Dampak');
                $sheet->setCellValue('G2', 'Nilai Dampak');
                $sheet->setCellValue('H2', 'Skala Dampak');
                $sheet->setCellValue('I2', 'Nilai Probabilitas');
                $sheet->setCellValue('J2', 'Skala Probabilitas');
                $sheet->setCellValue('K2', 'Eksposur Risiko');
                $sheet->setCellValue('L2', 'Skala Risiko');
                $sheet->setCellValue('M2', 'Level Risiko');

                // Merge sel header vertikal untuk kolom A-E (2 level)
                $mergeColumns = ['A', 'B', 'C', 'D', 'E'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge sel header "Realisasi Risiko Residual" secara horizontal (dari F1 sampai M1)
                $sheet->mergeCells('F1:M1');

                // Merge sel header "Nilai Efektivitas"
                $sheet->mergeCells('N1:N2');
                // Merge sel header "Efektifitas Perlakuan Risiko"
                $sheet->mergeCells('O1:O2');

                // Atur style untuk header utama (Row 1-2) - Biru
                $headerStyle = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '9BC2E6']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('A1:O2')->applyFromArray($headerStyle);

                // Style khusus untuk sub-header kategori (F2-M2) - Background abu-abu (DBDBDB)
                $subHeaderStyle = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBDBDB'] // GRAY BACKGROUND
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('F2:M2')->applyFromArray($subHeaderStyle);

                $lastRow = $sheet->getHighestRow();

                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                ];

                // Terapkan border hanya jika ada data (baris > 2)
                if ($lastRow > 2) {
                    $dataRange = 'A3:O' . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);

                    // Set rata tengah horizontal untuk kolom-kolom tertentu
                    // (Kolom M: Level Risiko, N: Nilai Efektivitas, O: Efektifitas)
                    $centerCols = ['A', 'B', 'D', 'H', 'I', 'J', 'L', 'M', 'N', 'O'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}3:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Gunakan $lastRow
                    $this->applyLevelRisikoColoring($sheet, $lastRow);
                }

                foreach (range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    /**
     * Apply coloring untuk Level Risiko berdasarkan nilai level risiko
     */
    private function applyLevelRisikoColoring($sheet, $maxRow)
    {
        // Kolom Level Risiko Residual (M)
        $column = 'M';

        // Mulai dari row 3 (setelah header)
        for ($row = 3; $row <= $maxRow; $row++) {
            $cellValue = $sheet->getCell($column . $row)->getValue();
            $backgroundColor = $this->getLevelRisikoBackgroundColor($cellValue);

            if ($backgroundColor) {
                $sheet->getStyle($column . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $backgroundColor]
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);
            }
        }
    }

    /**
     * Get background color berdasarkan level risiko
     */
    private function getLevelRisikoBackgroundColor($levelRisiko)
    {
        if (!$levelRisiko || $levelRisiko === '-') {
            return null;
        }

        $levelRisiko = strtolower(trim($levelRisiko));

        switch ($levelRisiko) {
            case 'low':
                return '92D050'; // Hijau Tua
            case 'low to moderate':
                return 'C6E0B4'; // Hijau Muda
            case 'moderate':
                return 'FFFF00'; // Kuning
            case 'moderate to high':
                return 'FFC000'; // Orange
            case 'high':
                return 'FF0000'; // Merah
            default:
                return null;
        }
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $risikos = ProjectRisk::with([
            'projectPeriodeList.project',
            'peristiwaRisiko',
            'projectRiskAnalisa.skalaProbabilitasResidual',
            'projectRiskAnalisa.skalaDampakResidualObj',
        ])
            // ->where('project_id', $this->projectId)
            ->whereIn('project_id', $this->projectIds)
            ->get()
            ->sortByDesc('projectRiskAnalisa.skala_risiko');

        $exportData = new Collection();
        $nomorUrut = 1;

        foreach ($risikos as $risiko) {
            $project = $risiko->projectPeriodeList->project;
            $analisa = $risiko->projectRiskAnalisa;

            if (!$analisa) {
                $rowData = [
                    'jenis_data' => '-',
                    'no' => $nomorUrut,
                    'nama_project' => $project->project_name ?? '-',
                    'no_risiko' => $nomorUrut,
                    'peristiwa_risiko' => $risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-',
                    'asumsi_perhitungan_dampak' => '-',
                    'nilai_dampak' => '-',
                    'skala_dampak' => '-',
                    'nilai_probabilitas' => '-',
                    'skala_probabilitas' => '-',
                    'eksposur_risiko' => '-',
                    'skala_risiko' => '-',
                    'level_risiko' => '-',
                    'nilai_efektivitas' => '-',
                    'efektifitas_perlakuan' => '-',
                ];

                $exportData->push($rowData);
                $nomorUrut++;
                continue;
            }

            // Tentukan jenis data berdasarkan kategori dampak
            $jenisData = ucfirst(strtolower($analisa->kategori_dampak ?? 'Kuantitatif'));

            $rowData = [
                'jenis_data' => $jenisData,
                'no' => $nomorUrut,
                'nama_project' => $project->project_name ?? '-',
                'no_risiko' => $nomorUrut,
                'peristiwa_risiko' => $risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-',
                'asumsi_perhitungan_dampak' => $analisa->asumsi_perhitungan_dampak_residual ?? '-',
                'nilai_dampak' => $this->formatNilaiDampak($analisa),
                'skala_dampak' => $this->formatSkalaDampak($analisa),
                'nilai_probabilitas' => $this->formatPercentage($analisa->nilai_probabilitas_residual ?? 0),
                'skala_probabilitas' => $this->formatSkalaProbabilitas($analisa),
                'eksposur_risiko' => $this->formatCurrency($analisa->eksposur_risiko_residual ?? 0),
                'skala_risiko' => $analisa->skala_risiko_residual ?? '-',
                'level_risiko' => $analisa->level_risiko_residual ?? '-',
                'nilai_efektivitas' => $risiko->efektivitas_perlakuan_risiko ? $risiko->efektivitas_perlakuan_risiko . '%' : '-',
                'efektifitas_perlakuan' => $this->calculateEfektifitas($analisa, $risiko),
            ];

            $exportData->push($rowData);
            $nomorUrut++;
        }

        return $exportData;
    }

    /**
     * Format nilai dampak berdasarkan kategori
     */
    private function formatNilaiDampak($analisa)
    {
        if ($analisa->kategori_dampak === 'Kualitatif') {
            return 'Rp0';
        }
        return $this->formatCurrency($analisa->nilai_dampak_residual ?? 0);
    }

    /**
     * Format skala dampak dengan deskripsi
     */
    private function formatSkalaDampak($analisa)
    {
        if (!$analisa->skala_dampak_residual) return '-';

        $deskripsi = optional($analisa->skalaDampakResidualObj)->deskripsi ?? '';

        if ($deskripsi) {
            return $analisa->skala_dampak_residual . ' - ' . $deskripsi;
        }

        return $analisa->skala_dampak_residual;
    }

    /**
     * Format skala probabilitas dengan deskripsi
     */
    private function formatSkalaProbabilitas($analisa)
    {
        if (!$analisa->skalaProbabilitasResidual) return '-';

        $tingkat = $analisa->skalaProbabilitasResidual->tingkat ?? '-';
        $skala = $analisa->skalaProbabilitasResidual->skala ?? '';

        if ($tingkat !== '-' && $skala) {
            return $tingkat . ' - ' . $skala;
        }

        return $tingkat;
    }

    /**
     * Calculate efektifitas perlakuan risiko
     */
    private function calculateEfektifitas($analisa, $risiko)
    {
        // 1. Cek apakah risiko sudah ditutup (closed)
        // if (!$risiko->is_closed) {
        //     return 'Belum Ditutup';
        // }

        // 2. Jika sudah ditutup, cek nilai efektivitas dari tabel ProjectRisk ($risiko)
        $nilaiEfektivitas = (float) $risiko->efektivitas_perlakuan_risiko;

        if ($nilaiEfektivitas > 0) {
            return 'Efektif';
        } else {
            // Ini akan mencakup nilai <= 0 (termasuk 0, negatif, atau null)
            return 'Tidak Efektif';
        }
    }

    /**
     * Format currency to Rupiah
     */
    private function formatCurrency($value)
    {
        if ($value == 0) return 'Rp0';
        return 'Rp' . number_format($value, 0, ',', '.');
    }

    /**
     * Format percentage
     */
    private function formatPercentage($value)
    {
        return $value . '%';
    }
}
