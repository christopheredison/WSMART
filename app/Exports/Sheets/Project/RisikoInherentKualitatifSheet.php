<?php

namespace App\Exports\Sheets\Project;

use App\Models\ProjectRisk;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RisikoInherentKualitatifSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    public function columnFormats(): array
    {
        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return [
            'F' => $currencyFormat,
            'J' => $currencyFormat,
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
        return 'Risiko Inherent Kualitatif';
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
                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Nama Project');
                $sheet->setCellValue('C1', 'No Risiko');
                $sheet->setCellValue('D1', 'Peristiwa Risiko');
                $sheet->setCellValue('E1', 'Risiko Inherent');

                // Row 2: Sub-header untuk Risiko Inherent
                $sheet->setCellValue('E2', 'Penjelasan Dampak Kualitatif');
                $sheet->setCellValue('F2', 'Nilai Dampak');
                $sheet->setCellValue('G2', 'Skala Dampak BUMN');
                $sheet->setCellValue('H2', 'Nilai Probabilitas');
                $sheet->setCellValue('I2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue('J2', 'Eksposur Risiko');
                $sheet->setCellValue('K2', 'Skala Risiko BUMN');
                $sheet->setCellValue('L2', 'Level Risiko BUMN');

                // Merge sel header
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:D2');
                $sheet->mergeCells('E1:L1');

                // Style untuk header utama (biru muda)
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
                $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

                // Style untuk sub-header (abu-abu)
                $subHeaderStyle = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBDBDB']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('A2:L2')->applyFromArray($subHeaderStyle);
                $sheet->getStyle('A1:D1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('9BC2E6');


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

                $highestRow = $sheet->getHighestRow();
                if ($highestRow > 2) { // Cek jika ada data di bawah header
                    // Terapkan border ke semua sel data
                    $sheet->getStyle('A3:L' . $highestRow)->applyFromArray($dataStyle);

                    // Panggil fungsi pewarnaan background
                    $this->applyLevelRisikoColoring($sheet, $highestRow);
                }

                foreach (range('A', 'L') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    /**
     * Apply coloring untuk Level Risiko BUMN berdasarkan nilai level risiko
     */
    private function applyLevelRisikoColoring($sheet, $maxRow)
    {
        // Kolom Level Risiko BUMN (L)
        $levelRisikoColumn = 'L';

        // Mulai dari row 3 (setelah header)
        for ($row = 3; $row <= $maxRow; $row++) {
            $cellValue = $sheet->getCell($levelRisikoColumn . $row)->getValue();
            $backgroundColor = $this->getLevelRisikoBackgroundColor($cellValue);

            if ($backgroundColor) {
                $sheet->getStyle($levelRisikoColumn . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $backgroundColor]
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
        $risikos = $this->risikos->filter(function ($risiko) {
            return optional($risiko->projectRiskAnalisa)->kategori_dampak === 'Kualitatif';
        })->sortByDesc('projectRiskAnalisa.skala_risiko');

        $exportData = new Collection();
        $nomorUrut = 1;

        foreach ($risikos as $risiko) {
            $project = $risiko->projectPeriodeList->project;
            $analisa = $risiko->projectRiskAnalisa;

            $rowData = [
                'no' => $nomorUrut,
                'nama_project' => $project->project_name ?? '-',
                'no_risiko' => $nomorUrut,
                'peristiwa_risiko' => $risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-',
                'deskripsi_dampak' => $analisa->deskripsi_dampak ?? '-',
                'nilai_dampak' => $this->formatCurrency($analisa->nilai_dampak),
                'skala_dampak_bumn' => $this->formatSkalaDampak($analisa),
                'nilai_probabilitas' => $this->formatPercentage($analisa->nilai_probabilitas),
                'skala_probabilitas_bumn' => $this->formatSkalaProbabilitas($analisa),
                'eksposur_risiko' => $this->formatCurrency($analisa->eksposur_risiko),
                'skala_risiko_bumn' => $analisa->skala_risiko ?? '-',
                'level_risiko_bumn' => $analisa->level_risiko ?? '-'
            ];

            $exportData->push($rowData);
            $nomorUrut++;
        }

        return $exportData;
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

    /**
     * Format skala dampak dengan deskripsi
     */
    private function formatSkalaDampak($analisa)
    {
        $skala = $analisa->skala_dampak ?? '-';
        $deskripsi = optional($analisa->skalaDampakObj)->deskripsi ?? '';

        if ($skala !== '-' && $deskripsi) {
            return $skala . ' - ' . $deskripsi;
        }

        return $skala;
    }

    /**
     * Format skala probabilitas dengan deskripsi
     */
    private function formatSkalaProbabilitas($analisa)
    {
        $tingkat = optional($analisa->skalaProbabilitas)->tingkat ?? '-';
        $skala = optional($analisa->skalaProbabilitas)->skala ?? '';

        if ($tingkat !== '-' && $skala) {
            return $tingkat . ' - ' . $skala;
        }

        return $tingkat;
    }
}
