<?php

namespace App\Exports\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportColumnHelper
{
    public static function shiftCol(string $col, int $offset): string
    {
        if ($offset === 0) {
            return $col;
        }

        return Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($col) + $offset);
    }

    /** @param string[] $cols */
    public static function shiftCols(array $cols, int $offset): array
    {
        return array_map(fn (string $col) => self::shiftCol($col, $offset), $cols);
    }

    public static function shiftRange(string $range, int $offset): string
    {
        if ($offset === 0 || !str_contains($range, ':')) {
            return $range;
        }

        [$start, $end] = explode(':', $range, 2);
        preg_match('/^([A-Z]+)(\d+)$/', $start, $startMatch);
        preg_match('/^([A-Z]+)(\d+)$/', $end, $endMatch);

        if (!$startMatch || !$endMatch) {
            return $range;
        }

        return self::shiftCol($startMatch[1], $offset) . $startMatch[2]
            . ':'
            . self::shiftCol($endMatch[1], $offset) . $endMatch[2];
    }

    public static function unitNameFromRisiko($risiko): string
    {
        return optional($risiko->unit)->name ?? '-';
    }

    /**
     * Sisipkan nama unit/divisi/AP sebagai kolom ke-2 (setelah field pertama, biasanya No/Jenis Data).
     */
    public static function prependUnitField(array $row, $risiko, bool $includeUnitColumn, bool $show = true): array
    {
        if (!$includeUnitColumn) {
            return $row;
        }

        if (empty($row)) {
            return ['nama_unit' => $show ? self::unitNameFromRisiko($risiko) : ''];
        }

        $keys = array_keys($row);
        $firstKey = $keys[0];

        return array_merge(
            [$firstKey => $row[$firstKey]],
            ['nama_unit' => $show ? self::unitNameFromRisiko($risiko) : ''],
            array_slice($row, 1, null, true)
        );
    }
}
