<?php

namespace App\Exports\Sheets\Unit;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class RisikoResidualKuantitatifSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStrictNullComparison
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
        return 'Risiko Residual Kuantitatif';
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
                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Nama BUMN');
                $sheet->setCellValue('C1', 'No Risiko');
                $sheet->setCellValue('D1', 'Peristiwa Risiko');
                $sheet->setCellValue('E1', 'Risiko Residual');

                $sheet->setCellValue('E2', 'Asumsi Perhitungan Dampak');
                $sheet->setCellValue('I2', 'Nilai Dampak');
                $sheet->setCellValue('M2', 'Skala Dampak BUMN');
                $sheet->setCellValue('Q2', 'Nilai Probabilitas');
                $sheet->setCellValue('U2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue('Y2', 'Eksposur Risiko');
                $sheet->setCellValue('AC2', 'Skala Risiko BUMN');
                $sheet->setCellValue('AG2', 'Level Risiko BUMN');

                $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
                $startColumns = ['E', 'I', 'M', 'Q', 'U', 'Y', 'AC', 'AG'];

                foreach ($startColumns as $startCol) {
                    $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startCol);
                    foreach ($quarters as $index => $quarter) {
                        $currentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + $index);
                        $sheet->setCellValue($currentCol . '3', $quarter);
                    }
                }

                $mergeColumns = ['A', 'B', 'C', 'D'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}3");
                }

                $sheet->mergeCells('E1:AJ1');
                $sheet->mergeCells('E2:H2');
                $sheet->mergeCells('I2:L2');
                $sheet->mergeCells('M2:P2');
                $sheet->mergeCells('Q2:T2');
                $sheet->mergeCells('U2:X2');
                $sheet->mergeCells('Y2:AB2');
                $sheet->mergeCells('AC2:AF2');
                $sheet->mergeCells('AG2:AJ2');

                $headerStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '9BC2E6']],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
                ];
                $sheet->getStyle('A1:AJ3')->applyFromArray($headerStyle);

                $sheet->getStyle('E2:AJ2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBDBDB']]
                ]);

                $sheet->getStyle('E3:AJ3')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
                ]);

                if ($risikosCount > 0) {
                    $maxDataRow = 3 + $risikosCount;
                    $sheet->getStyle('A4:AJ' . $maxDataRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                        'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true]
                    ]);
                    $this->applyLevelRisikoColoring($sheet, $maxDataRow);
                }

                $col = 'A';
                while ($col !== 'AK') {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                    $col++;
                }
            },
        ];
    }

    private function applyLevelRisikoColoring($sheet, $maxRow)
    {
        $levelRisikoColumns = ['AG', 'AH', 'AI', 'AJ'];
        for ($row = 4; $row <= $maxRow; $row++) {
            foreach ($levelRisikoColumns as $column) {
                $cellValue = $sheet->getCell($column . $row)->getValue();
                $backgroundColor = $this->getLevelRisikoBackgroundColor($cellValue);
                if ($backgroundColor) {
                    $sheet->getStyle($column . $row)->applyFromArray([
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $backgroundColor]]
                    ]);
                }
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

                'asumsi_perhitungan_dampak_q1' => $analisa->asumsi_perhitungan_dampak_residual_q1 ?? '-',
                'asumsi_perhitungan_dampak_q2' => $analisa->asumsi_perhitungan_dampak_residual_q2 ?? '-',
                'asumsi_perhitungan_dampak_q3' => $analisa->asumsi_perhitungan_dampak_residual_q3 ?? '-',
                'asumsi_perhitungan_dampak_q4' => $analisa->asumsi_perhitungan_dampak_residual_q4 ?? '-',

                'nilai_dampak_q1' => $this->formatCurrency($analisa->nilai_dampak_residual_q1 ?? 0),
                'nilai_dampak_q2' => $this->formatCurrency($analisa->nilai_dampak_residual_q2 ?? 0),
                'nilai_dampak_q3' => $this->formatCurrency($analisa->nilai_dampak_residual_q3 ?? 0),
                'nilai_dampak_q4' => $this->formatCurrency($analisa->nilai_dampak_residual_q4 ?? 0),

                'skala_dampak_q1' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q1, $analisa->skalaDampakResidualQ1Obj),
                'skala_dampak_q2' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q2, $analisa->skalaDampakResidualQ2Obj),
                'skala_dampak_q3' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q3, $analisa->skalaDampakResidualQ3Obj),
                'skala_dampak_q4' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q4, $analisa->skalaDampakResidualQ4Obj),

                'nilai_probabilitas_q1' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q1 ?? 0),
                'nilai_probabilitas_q2' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q2 ?? 0),
                'nilai_probabilitas_q3' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q3 ?? 0),
                'nilai_probabilitas_q4' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q4 ?? 0),

                'skala_probabilitas_q1' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ1),
                'skala_probabilitas_q2' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ2),
                'skala_probabilitas_q3' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ3),
                'skala_probabilitas_q4' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ4),

                'eksposur_risiko_q1' => $this->formatCurrency($analisa->eksposur_risiko_residual_q1 ?? 0),
                'eksposur_risiko_q2' => $this->formatCurrency($analisa->eksposur_risiko_residual_q2 ?? 0),
                'eksposur_risiko_q3' => $this->formatCurrency($analisa->eksposur_risiko_residual_q3 ?? 0),
                'eksposur_risiko_q4' => $this->formatCurrency($analisa->eksposur_risiko_residual_q4 ?? 0),

                'skala_risiko_q1' => $analisa->skala_risiko_residual_q1 ?? '-',
                'skala_risiko_q2' => $analisa->skala_risiko_residual_q2 ?? '-',
                'skala_risiko_q3' => $analisa->skala_risiko_residual_q3 ?? '-',
                'skala_risiko_q4' => $analisa->skala_risiko_residual_q4 ?? '-',

                'level_risiko_q1' => $analisa->level_risiko_residual_q1 ?? '-',
                'level_risiko_q2' => $analisa->level_risiko_residual_q2 ?? '-',
                'level_risiko_q3' => $analisa->level_risiko_residual_q3 ?? '-',
                'level_risiko_q4' => $analisa->level_risiko_residual_q4 ?? '-',
            ];

            $exportData->push($rowData);
            $nomorUrut++;
        }

        return $exportData;
    }

    private function formatCurrency($value)
    {
        // Bersihkan karakter non-numerik jika data berbentuk string (misal spasi atau teks nyasar)
        if (is_string($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        
        // Jika kosong atau bukan angka, kembalikan integer mutlak 0
        if ($value === '' || $value === null || !is_numeric($value)) {
            return 0; 
        }

        return (float) $value;
    }

    private function formatPercentage($value) { return $value . '%'; }
    private function formatSkalaDampak($skala, $obj) { return $skala ? ($obj ? $skala . ' - ' . $obj->deskripsi : $skala) : '-'; }
    private function formatSkalaProbabilitas($obj) { return $obj ? ($obj->skala ? $obj->tingkat . ' - ' . $obj->skala : $obj->tingkat) : '-'; }
}
