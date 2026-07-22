<?php

namespace App\Exports\Sheets\Unit;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class RisikoInherentKuantitatifSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStrictNullComparison
{
    private $risikos;

    public function __construct(Collection $risikos)
    {
        $this->risikos = $risikos;
    }

    private function getFilteredRisikos()
    {
        return $this->risikos->filter(function ($risiko) {
            return $risiko->riskAnalysis && $risiko->riskAnalysis->kategori_dampak === 'Kuantitatif';
        })->values();
    }

    public function title(): string
    {
        return 'Risiko Inherent Kuantitatif';
    }

    public function headings(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        $risikosCount = $this->getFilteredRisikos()->count();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($risikosCount) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 2);

                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Nama BUMN');
                $sheet->setCellValue('C1', 'No Risiko');
                $sheet->setCellValue('D1', 'Peristiwa Risiko');
                $sheet->setCellValue('E1', 'Risiko Inherent');

                $sheet->setCellValue('E2', 'Asumsi Perhitungan Dampak');
                $sheet->setCellValue('F2', 'Nilai Dampak');
                $sheet->setCellValue('G2', 'Skala Dampak BUMN');
                $sheet->setCellValue('H2', 'Nilai Probabilitas');
                $sheet->setCellValue('I2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue('J2', 'Eksposur Risiko');
                $sheet->setCellValue('K2', 'Skala Risiko BUMN');
                $sheet->setCellValue('L2', 'Level Risiko BUMN');

                $mergeColumns = ['A', 'B', 'C', 'D'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }
                $sheet->mergeCells('E1:L1');

                $headerStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '9BC2E6']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('A1:L2')->applyFromArray($headerStyle);

                $subHeaderStyle = [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBDBDB']
                    ]
                ];
                $sheet->getStyle('E2:L2')->applyFromArray($subHeaderStyle);

                if ($risikosCount > 0) {
                    $maxDataRow = 2 + $risikosCount;
                    $sheet->getStyle('A3:L' . $maxDataRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ],
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ]
                    ]);

                    $this->applyLevelRisikoColoring($sheet, $maxDataRow);
                }

                foreach (range('A', 'L') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function applyLevelRisikoColoring($sheet, $maxRow)
    {
        for ($row = 3; $row <= $maxRow; $row++) {
            $cellValue = $sheet->getCell('L' . $row)->getValue();
            $backgroundColor = $this->getLevelRisikoBackgroundColor($cellValue);

            if ($backgroundColor) {
                $sheet->getStyle('L' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $backgroundColor]
                    ]
                ]);
            }
        }
    }

    private function getLevelRisikoBackgroundColor($levelRisiko)
    {
        if (!$levelRisiko || $levelRisiko === '-') return null;

        switch (strtolower(trim($levelRisiko))) {
            case 'low': return '92D050';
            case 'low to moderate': return 'C6E0B4';
            case 'moderate': return 'FFFF00';
            case 'moderate to high': return 'FFC000';
            case 'high': return 'FF0000';
            default: return null;
        }
    }

    public function collection()
    {
        $risikos = $this->getFilteredRisikos();
        $exportData = new Collection();
        $nomorUrut = 1;

        foreach ($risikos as $risiko) {
            $analisa = $risiko->riskAnalysis;

            $rowData = [
                'no' => $nomorUrut,
                'nama_bumn' => 'PT Wijaya Karya (Persero) Tbk',
                'no_risiko' => $nomorUrut,
                'peristiwa_risiko' => $risiko->peristiwa_risiko ?? '-',
                'asumsi_perhitungan_dampak' => $analisa->asumsi_perhitungan_dampak ?? '-',
                'nilai_dampak' => $this->formatCurrency($analisa->nilai_dampak ?? 0),
                'skala_dampak' => $this->formatSkalaDampak($analisa),
                'nilai_probabilitas' => $this->formatPercentage($analisa->nilai_probabilitas ?? 0),
                'skala_probabilitas' => $this->formatSkalaProbabilitas($analisa),
                'eksposur_risiko' => $this->formatCurrency($analisa->eksposur_risiko ?? 0),
                'skala_risiko' => $analisa->skala_risiko ?? '-',
                'level_risiko' => $analisa->level_risiko ?? '-',
            ];

            $exportData->push($rowData);
            $nomorUrut++;
        }

        return $exportData;
    }

    private function formatCurrency($value)
    {
        if (is_string($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        
        if ($value === '' || $value === null || !is_numeric($value)) {
            return 0; 
        }

        return (float) $value;
    }

    private function formatPercentage($value) { return $value . '%'; }

    private function formatSkalaDampak($analisa)
    {
        $skala = $analisa->skala_dampak ?? '-';
        $deskripsi = optional($analisa->skalaDampakObj)->deskripsi ?? '';
        return ($skala !== '-' && $deskripsi) ? $skala . ' - ' . $deskripsi : $skala;
    }

    private function formatSkalaProbabilitas($analisa)
    {
        $tingkat = optional($analisa->skalaProbabilitas)->tingkat ?? '-';
        $skala = optional($analisa->skalaProbabilitas)->skala ?? '';
        return ($tingkat !== '-' && $skala) ? $tingkat . ' - ' . $skala : $tingkat;
    }
}
