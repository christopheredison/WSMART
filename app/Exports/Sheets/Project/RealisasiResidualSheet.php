<?php

namespace App\Exports\Sheets\Project;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RealisasiResidualSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithColumnFormatting, WithStrictNullComparison
{
    public function columnFormats(): array
    {
        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return [
            'G' => $currencyFormat, // Nilai Dampak
            'K' => $currencyFormat, // Eksposur Risiko
        ];
    }

    protected $risikos;

    public function __construct(Collection $risikos)
    {
        $this->risikos = $risikos;
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
                $dataStartRow = 3;
                $currencyCols = ['G', 'K'];

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
                if ($lastRow >= $dataStartRow) {
                    $this->forceNumericCurrencyCells($sheet, $currencyCols, $dataStartRow, $lastRow);

                    $dataRange = 'A' . $dataStartRow . ':O' . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);

                    // Set rata tengah horizontal untuk kolom-kolom tertentu
                    $centerCols = ['A', 'B', 'D', 'H', 'I', 'J', 'L', 'M', 'N', 'O'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    $this->applyLevelRisikoColoring($sheet, $lastRow);

                    // Tambah baris TOTAL
                    $totalRow = $lastRow + 1;
                    $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                    $sheet->mergeCells('A' . $totalRow . ':F' . $totalRow);
                    $sheet->setCellValue('G' . $totalRow, "=SUM(G{$dataStartRow}:G{$lastRow})");
                    $sheet->setCellValue('K' . $totalRow, "=SUM(K{$dataStartRow}:K{$lastRow})");

                    $sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF2CC'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';
                    $sheet->getStyle('G' . $totalRow)->getNumberFormat()->setFormatCode($currencyFormat);
                    $sheet->getStyle('K' . $totalRow)->getNumberFormat()->setFormatCode($currencyFormat);
                }

                foreach (range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function forceNumericCurrencyCells($sheet, array $columns, int $startRow, int $endRow): void
    {
        foreach ($columns as $col) {
            for ($row = $startRow; $row <= $endRow; $row++) {
                $raw = $sheet->getCell($col . $row)->getValue();
                if (is_string($raw)) {
                    $raw = preg_replace('/[^0-9.\-]/', '', $raw);
                }
                $sheet->setCellValueExplicit(
                    $col . $row,
                    (float) ($raw ?: 0),
                    DataType::TYPE_NUMERIC
                );
            }
        }
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
        $risikos = $this->risikos->sortByDesc('projectRiskAnalisa.skala_risiko');

        $exportData = new Collection();
        $nomorUrut = 1;

        foreach ($risikos as $risiko) {
            $project = $risiko->projectPeriodeList->project;
            $analisa = $risiko->projectRiskAnalisa;
            $monitoring = $risiko->projectRiskMonitorings->first();

            if (!$analisa) {
                $rowData = [
                    'jenis_data' => '-',
                    'no' => $nomorUrut,
                    'nama_project' => $project->project_name ?? '-',
                    'no_risiko' => $nomorUrut,
                    'peristiwa_risiko' => $risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-',
                    'asumsi_perhitungan_dampak' => '-',
                    'nilai_dampak' => 0,
                    'skala_dampak' => '-',
                    'nilai_probabilitas' => '-',
                    'skala_probabilitas' => '-',
                    'eksposur_risiko' => 0,
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

            $efektivitasNilai = $monitoring?->efektivitas_perlakuan_risiko ?? $risiko->efektivitas_perlakuan_risiko;

            if ($monitoring) {
                $nilaiDampakRealisasi = $monitoring->nilai_dampak;
                $eksposurRealisasi = $monitoring->eksposure_risiko;
                $skalaDampakRealisasi = $monitoring->skala_dampak;
                $nilaiProbabilitasRealisasi = $monitoring->nilai_probabilitas;
                $skalaProbabilitasRealisasi = $monitoring->skalaProbabilitas?->tingkat ?? '-';
                $skalaRisikoRealisasi = $monitoring->skala_risiko;
                $levelRisikoRealisasi = $monitoring->level_risiko;
            } else {
                // Belum pernah ada monitoring publish: realisasi mengikuti nilai inherent
                $nilaiDampakRealisasi = $analisa->nilai_dampak ?? 0;
                $eksposurRealisasi = $analisa->eksposur_risiko ?? 0;
                $skalaDampakRealisasi = $analisa->skala_dampak;
                $nilaiProbabilitasRealisasi = $analisa->nilai_probabilitas ?? 0;
                $skalaProbabilitasRealisasi = $analisa->skalaProbabilitas?->tingkat ?? '-';
                $skalaRisikoRealisasi = $analisa->skala_risiko ?? '-';
                $levelRisikoRealisasi = $analisa->level_risiko ?? '-';
            }

            $rowData = [
                'jenis_data' => $jenisData,
                'no' => $nomorUrut,
                'nama_project' => $project->project_name ?? '-',
                'no_risiko' => $nomorUrut,
                'peristiwa_risiko' => $risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-',
                'asumsi_perhitungan_dampak' => $analisa->asumsi_perhitungan_dampak_residual ?? '-',
                'nilai_dampak' => $this->formatCurrency($nilaiDampakRealisasi ?? 0),
                'skala_dampak' => $skalaDampakRealisasi ?? $this->formatSkalaDampak($analisa),
                'nilai_probabilitas' => $this->formatPercentage($nilaiProbabilitasRealisasi ?? 0),
                'skala_probabilitas' => $skalaProbabilitasRealisasi,
                'eksposur_risiko' => $this->formatCurrency($eksposurRealisasi ?? 0),
                'skala_risiko' => $skalaRisikoRealisasi ?? '-',
                'level_risiko' => $levelRisikoRealisasi ?? '-',
                'nilai_efektivitas' => $efektivitasNilai ? $efektivitasNilai . '%' : '-',
                'efektifitas_perlakuan' => $this->calculateEfektifitasVal($efektivitasNilai),
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
            return 0;
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
    private function calculateEfektifitasVal($nilaiEfektivitas)
    {
        $nilai = (float) $nilaiEfektivitas;
        return $nilai >= 0 ? 'Efektif' : 'Tidak Efektif';
    }

    /**
     * Format currency to Rupiah
     */
    private function formatCurrency($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_string($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        return (float) ($value ?: 0);
    }

    /**
     * Format percentage
     */
    private function formatPercentage($value)
    {
        return $value . '%';
    }
}
