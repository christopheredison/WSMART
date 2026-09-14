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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class RisikoInherentKualitatifSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStrictNullComparison, WithColumnFormatting
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
            'F' => $currencyFormat,
            'J' => $currencyFormat,
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
        return 'Risiko Inherent Kualitatif';
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
                $sheet->insertNewRowBefore(1, 2);

                $this->setUnitColumnHeader($sheet, 2);

                $sheet->setCellValue($c('A') . '1', 'No');
                $sheet->setCellValue($c('B') . '1', 'Nama BUMN');
                $sheet->setCellValue($c('C') . '1', 'No Risiko');
                $sheet->setCellValue($c('D') . '1', 'Peristiwa Risiko');
                $sheet->setCellValue($c('E') . '1', 'Risiko Inherent');

                $sheet->setCellValue($c('E') . '2', 'Penjelasan Dampak Kualitatif');
                $sheet->setCellValue($c('F') . '2', 'Nilai Dampak');
                $sheet->setCellValue($c('G') . '2', 'Skala Dampak BUMN');
                $sheet->setCellValue($c('H') . '2', 'Nilai Probabilitas');
                $sheet->setCellValue($c('I') . '2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue($c('J') . '2', 'Eksposur Risiko');
                $sheet->setCellValue($c('K') . '2', 'Skala Risiko BUMN');
                $sheet->setCellValue($c('L') . '2', 'Level Risiko BUMN');

                $mergeColumns = ['A', 'B', 'C', 'D'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$c($col)}1:{$c($col)}2");
                }
                $sheet->mergeCells($c('E') . '1:' . $c('L') . '1');

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
                $sheet->getStyle('A1:' . $c('L') . '2')->applyFromArray($headerStyle);

                $sheet->getStyle($c('E') . '2:' . $c('L') . '2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBDBDB']]
                ]);

                $dataStartRow = 3;
                $lastRow = $sheet->getHighestRow();

                if ($lastRow >= $dataStartRow) {
                    $sheet->getStyle('A3:' . $c('L') . $lastRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                        'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true]
                    ]);
                    $this->applyLevelRisikoColoring($sheet, $lastRow);
                    $this->appendCurrencyTotalRow($sheet, $dataStartRow, $lastRow, 'L', ['F', 'J'], 'E');
                }

                foreach (range('A', 'L') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                if ($this->includeUnitColumn) {
                    $sheet->getColumnDimension($c('L'))->setAutoSize(true);
                }
            },
        ];
    }

    private function applyLevelRisikoColoring($sheet, $maxRow)
    {
        $column = $this->c('L');
        for ($row = 3; $row <= $maxRow; $row++) {
            $cellValue = $sheet->getCell($column . $row)->getValue();
            $backgroundColor = $this->getLevelRisikoBackgroundColor($cellValue);
            if ($backgroundColor) {
                $sheet->getStyle($column . $row)->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $backgroundColor]]
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
                'deskripsi_dampak' => $analisa->deskripsi_dampak ?? $risiko->deskripsi_dampak ?? '-',
                'nilai_dampak' => 0,
                'skala_dampak' => $this->formatSkalaDampak($analisa),
                'nilai_probabilitas' => $this->formatPercentage($analisa->nilai_probabilitas ?? 0),
                'skala_probabilitas' => $this->formatSkalaProbabilitas($analisa),
                'eksposur_risiko' => $this->formatCurrency($analisa->eksposur_risiko ?? 0),
                'skala_risiko' => $analisa->skala_risiko ?? '-',
                'level_risiko' => $analisa->level_risiko ?? '-',
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

    private function formatSkalaDampak($analisa) {
        $skala = $analisa->skala_dampak ?? '-';
        $deskripsi = optional($analisa->skalaDampakObj)->deskripsi ?? '';
        return ($skala !== '-' && $deskripsi) ? $skala . ' - ' . $deskripsi : $skala;
    }

    private function formatSkalaProbabilitas($analisa) {
        $tingkat = optional($analisa->skalaProbabilitas)->tingkat ?? '-';
        $skala = optional($analisa->skalaProbabilitas)->skala ?? '';
        return ($tingkat !== '-' && $skala) ? $tingkat . ' - ' . $skala : $tingkat;
    }
}
