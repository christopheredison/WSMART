<?php

namespace App\Exports\Sheets\Unit\Concerns;

use App\Exports\Support\ExportColumnHelper;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait SupportsUnitColumnExport
{
    protected bool $includeUnitColumn = false;
    protected string $unitColumnLabel = 'Nama Divisi';

    protected function unitOffset(): int
    {
        return $this->includeUnitColumn ? 1 : 0;
    }

    /** Geser kolom base (B dst.) saat kolom nama unit ada di B; kolom A tetap. */
    protected function c(string $col): string
    {
        if (!$this->includeUnitColumn || $col === 'A') {
            return $col;
        }

        return ExportColumnHelper::shiftCol($col, 1);
    }

    protected function shiftRange(string $range): string
    {
        return ExportColumnHelper::shiftRange($range, $this->unitOffset());
    }

    protected function prependUnit(array $row, $risiko, bool $show = true): array
    {
        return ExportColumnHelper::prependUnitField($row, $risiko, $this->includeUnitColumn, $show);
    }

    protected function setUnitColumnHeader($sheet, int $rowSpan = 2): void
    {
        if (!$this->includeUnitColumn) {
            return;
        }

        $sheet->setCellValue('B1', $this->unitColumnLabel);
        if ($rowSpan > 1) {
            $sheet->mergeCells("B1:B{$rowSpan}");
        }
    }

    protected function shiftColumnFormats(array $formats): array
    {
        if (!$this->includeUnitColumn) {
            return $formats;
        }

        $shifted = [];
        foreach ($formats as $col => $format) {
            $shifted[$this->c($col)] = $format;
        }

        return $shifted;
    }

    protected function forceNumericCurrencyCells($sheet, array $baseColumns, int $startRow, int $endRow): void
    {
        foreach ($baseColumns as $baseCol) {
            $col = $this->c($baseCol);
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

    protected function appendCurrencyTotalRow(
        $sheet,
        int $dataStartRow,
        int $maxDataRow,
        string $lastBaseCol,
        array $sumBaseCols,
        string $mergeToBaseCol = 'E'
    ): int {
        if ($maxDataRow < $dataStartRow) {
            return $maxDataRow;
        }

        $totalRow = $maxDataRow + 1;
        $lastCol = $this->c($lastBaseCol);

        $this->forceNumericCurrencyCells($sheet, $sumBaseCols, $dataStartRow, $maxDataRow);

        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $sheet->mergeCells('A' . $totalRow . ':' . $this->c($mergeToBaseCol) . $totalRow);

        foreach ($sumBaseCols as $baseCol) {
            $col = $this->c($baseCol);
            $sheet->setCellValue($col . $totalRow, "=SUM({$col}{$dataStartRow}:{$col}{$maxDataRow})");
        }

        $sheet->getStyle('A' . $totalRow . ':' . $lastCol . $totalRow)->applyFromArray([
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
        foreach ($sumBaseCols as $baseCol) {
            $sheet->getStyle($this->c($baseCol) . $totalRow)->getNumberFormat()->setFormatCode($currencyFormat);
        }

        return $totalRow;
    }
}
