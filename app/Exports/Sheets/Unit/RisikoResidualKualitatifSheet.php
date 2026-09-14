<?php

namespace App\Exports\Sheets\Unit;

use App\Exports\Sheets\Unit\Concerns\SupportsUnitColumnExport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RisikoResidualKualitatifSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStrictNullComparison, WithColumnFormatting
{
    use SupportsUnitColumnExport;

    private $risikos;

    public function __construct(Collection $risikos, bool $includeUnitColumn = false, string $unitColumnLabel = 'Nama Divisi')
    {
        $this->risikos = $risikos;
        $this->includeUnitColumn = $includeUnitColumn;
        $this->unitColumnLabel = $unitColumnLabel;
    }

    public function columnFormats(): array
    {
        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return $this->shiftColumnFormats([
            'I' => $currencyFormat,
            'J' => $currencyFormat,
            'K' => $currencyFormat,
            'L' => $currencyFormat,
            'Y' => $currencyFormat,
            'Z' => $currencyFormat,
            'AA' => $currencyFormat,
            'AB' => $currencyFormat,
        ]);
    }

    private function getFilteredRisikos()
    {
        return $this->risikos->filter(function ($risiko) {
            return $risiko->riskAnalysis && $risiko->riskAnalysis->kategori_dampak === 'Kualitatif';
        })->values();
    }

    public function title(): string
    {
        return 'Risiko Residual Kualitatif';
    }

    public function headings(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $c = fn (string $col) => $this->c($col);
                $sheet->insertNewRowBefore(1, 3);

                $this->setUnitColumnHeader($sheet, 3);

                $sheet->setCellValue($c('A') . '1', 'No');
                $sheet->setCellValue($c('B') . '1', 'Nama BUMN');
                $sheet->setCellValue($c('C') . '1', 'No Risiko');
                $sheet->setCellValue($c('D') . '1', 'Peristiwa Risiko');
                $sheet->setCellValue($c('E') . '1', 'Risiko Residual');

                $sheet->setCellValue($c('E') . '2', 'Deskripsi Dampak');
                $sheet->setCellValue($c('I') . '2', 'Nilai Dampak');
                $sheet->setCellValue($c('M') . '2', 'Skala Dampak BUMN');
                $sheet->setCellValue($c('Q') . '2', 'Nilai Probabilitas');
                $sheet->setCellValue($c('U') . '2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue($c('Y') . '2', 'Eksposur Risiko');
                $sheet->setCellValue($c('AC') . '2', 'Skala Risiko BUMN');
                $sheet->setCellValue($c('AG') . '2', 'Level Risiko BUMN');

                $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
                $startColumns = ['E', 'I', 'M', 'Q', 'U', 'Y', 'AC', 'AG'];

                foreach ($startColumns as $startCol) {
                    $colIndex = Coordinate::columnIndexFromString($c($startCol));
                    foreach ($quarters as $index => $quarter) {
                        $currentCol = Coordinate::stringFromColumnIndex($colIndex + $index);
                        $sheet->setCellValue($currentCol . '3', $quarter);
                    }
                }

                $mergeColumns = ['A', 'B', 'C', 'D'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$c($col)}1:{$c($col)}3");
                }

                $sheet->mergeCells($c('E') . '1:' . $c('AJ') . '1');
                $sheet->mergeCells($c('E') . '2:' . $c('H') . '2');
                $sheet->mergeCells($c('I') . '2:' . $c('L') . '2');
                $sheet->mergeCells($c('M') . '2:' . $c('P') . '2');
                $sheet->mergeCells($c('Q') . '2:' . $c('T') . '2');
                $sheet->mergeCells($c('U') . '2:' . $c('X') . '2');
                $sheet->mergeCells($c('Y') . '2:' . $c('AB') . '2');
                $sheet->mergeCells($c('AC') . '2:' . $c('AF') . '2');
                $sheet->mergeCells($c('AG') . '2:' . $c('AJ') . '2');

                $headerStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '9BC2E6']],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
                ];
                $sheet->getStyle('A1:' . $c('AJ') . '3')->applyFromArray($headerStyle);

                $sheet->getStyle($c('E') . '2:' . $c('AJ') . '2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBDBDB']]
                ]);

                $sheet->getStyle($c('E') . '3:' . $c('AJ') . '3')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
                ]);

                $dataStartRow = 4;
                $lastRow = $sheet->getHighestRow();

                if ($lastRow >= $dataStartRow) {
                    $sheet->getStyle('A4:' . $c('AJ') . $lastRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                        'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true]
                    ]);
                    $this->applyLevelRisikoColoring($sheet, $lastRow);
                    $this->appendCurrencyTotalRow(
                        $sheet,
                        $dataStartRow,
                        $lastRow,
                        'AJ',
                        ['I', 'J', 'K', 'L', 'Y', 'Z', 'AA', 'AB'],
                        'D'
                    );
                }

                $lastColIndex = Coordinate::columnIndexFromString($c('AJ'));
                for ($i = 1; $i <= $lastColIndex; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
                }
            },
        ];
    }

    private function applyLevelRisikoColoring($sheet, $maxRow)
    {
        $levelRisikoColumns = array_map(fn (string $col) => $this->c($col), ['AG', 'AH', 'AI', 'AJ']);
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

                'deskripsi_dampak_q1' => $analisa->deskripsi_dampak_residual_q1 ?? '-',
                'deskripsi_dampak_q2' => $analisa->deskripsi_dampak_residual_q2 ?? '-',
                'deskripsi_dampak_q3' => $analisa->deskripsi_dampak_residual_q3 ?? '-',
                'deskripsi_dampak_q4' => $analisa->deskripsi_dampak_residual_q4 ?? '-',

                'nilai_dampak_q1' => 0,
                'nilai_dampak_q2' => 0,
                'nilai_dampak_q3' => 0,
                'nilai_dampak_q4' => 0,

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

            $exportData->push($this->prependUnit($rowData, $risiko));
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
    private function formatSkalaDampak($skala, $obj) { return $skala ? ($obj ? $skala . ' - ' . $obj->deskripsi : $skala) : '-'; }
    private function formatSkalaProbabilitas($obj) { return $obj ? ($obj->skala ? $obj->tingkat . ' - ' . $obj->skala : $obj->tingkat) : '-'; }
}
