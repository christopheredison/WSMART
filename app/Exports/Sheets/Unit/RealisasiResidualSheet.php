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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class RealisasiResidualSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStrictNullComparison, WithColumnFormatting
{
    use SupportsUnitColumnExport;

    private $risikos;
    private $bulan;

    public function __construct(Collection $risikos, $bulan = null, bool $includeUnitColumn = false, string $unitColumnLabel = 'Nama Divisi')
    {
        $this->risikos = $risikos;
        $this->bulan = $bulan;
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

    public function title(): string
    {
        return 'Realisasi Residual';
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

                $namaBulanList = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $teksBulan = $this->bulan ? ' (' . $namaBulanList[(int)$this->bulan] . ')' : '';

                $this->setUnitColumnHeader($sheet, 2);

                $sheet->setCellValue($c('A') . '1', 'Jenis Data');
                $sheet->setCellValue($c('B') . '1', 'No');
                $sheet->setCellValue($c('C') . '1', 'Nama BUMN');
                $sheet->setCellValue($c('D') . '1', 'No Risiko');
                $sheet->setCellValue($c('E') . '1', 'Peristiwa Risiko');
                $sheet->setCellValue($c('F') . '1', 'Realisasi Risiko Residual' . $teksBulan);

                $sheet->setCellValue($c('M') . '1', 'Nilai Efektivitas');
                $sheet->setCellValue($c('N') . '1', 'Efektifitas Perlakuan Risiko');

                $sheet->setCellValue($c('F') . '2', 'Nilai Dampak');
                $sheet->setCellValue($c('G') . '2', 'Skala Dampak BUMN');
                $sheet->setCellValue($c('H') . '2', 'Nilai Probabilitas');
                $sheet->setCellValue($c('I') . '2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue($c('J') . '2', 'Eksposur Risiko');
                $sheet->setCellValue($c('K') . '2', 'Skala Risiko BUMN');
                $sheet->setCellValue($c('L') . '2', 'Level Risiko BUMN');

                $mergeColumns = ['A', 'B', 'C', 'D', 'E'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$c($col)}1:{$c($col)}2");
                }

                $sheet->mergeCells($c('F') . '1:' . $c('L') . '1');
                $sheet->mergeCells($c('M') . '1:' . $c('M') . '2');
                $sheet->mergeCells($c('N') . '1:' . $c('N') . '2');

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
                $sheet->getStyle('A1:' . $c('N') . '2')->applyFromArray($headerStyle);

                $subHeaderStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBDBDB']
                    ]
                ];
                $sheet->getStyle($c('F') . '2:' . $c('L') . '2')->applyFromArray($subHeaderStyle);

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

                if ($lastRow > 2) {
                    $dataStartRow = 3;
                    $dataRange = 'A3:' . $c('N') . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);

                    $centerCols = ['A', 'B', 'D', 'G', 'H', 'I', 'K', 'L', 'M', 'N'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$c($col)}3:{$c($col)}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    $this->applyLevelRisikoColoring($sheet, $lastRow);
                    $lastRow = $this->appendCurrencyTotalRow($sheet, $dataStartRow, $lastRow, 'N', ['F', 'J'], 'E');
                }

                foreach (range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                if ($this->includeUnitColumn) {
                    $sheet->getColumnDimension($c('N'))->setAutoSize(true);
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
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
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
        $exportData = new Collection();
        $nomorUrut = 1;

        foreach ($this->risikos as $risiko) {
            $analisa = $risiko->riskAnalysis;
            $monitoring = $risiko->monitoringRisikos->first();

            if (!$analisa) {
                $exportData->push($this->prependUnit([
                    'jenis_data' => '-', 'no' => $nomorUrut, 'nama_bumn' => 'PT Wijaya Karya (Persero) Tbk',
                    'no_risiko' => $nomorUrut, 'peristiwa_risiko' => $risiko->peristiwa_risiko ?? '-',
                    'nilai_dampak' => 0, 'skala_dampak' => '-',
                    'nilai_probabilitas' => '-', 'skala_probabilitas' => '-', 'eksposur_risiko' => 0,
                    'skala_risiko' => '-', 'level_risiko' => '-', 'nilai_efektivitas' => '-', 'efektifitas_perlakuan' => '-'
                ], $risiko));
                $nomorUrut++;
                continue;
            }

            $jenisData = ucfirst(strtolower($analisa->kategori_dampak ?? 'Kuantitatif'));
            $efektivitasNilai = $monitoring->efektivitas_perlakuan_risiko ?? $risiko->efektivitas_perlakuan_risiko;

            $rowData = [
                'jenis_data' => $jenisData,
                'no' => $nomorUrut,
                'nama_bumn' => 'PT Wijaya Karya (Persero) Tbk',
                'no_risiko' => $nomorUrut,
                'peristiwa_risiko' => $risiko->peristiwa_risiko ?? '-',

                'nilai_dampak' => $this->formatCurrency($monitoring->nilai_dampak ?? 0),
                'skala_dampak' => $monitoring ? $this->formatSkalaDampak($monitoring->skala_dampak, $monitoring->skalaDampakObj) : '-',
                'nilai_probabilitas' => $monitoring ? $this->formatPercentage($monitoring->nilai_probabilitas ?? 0) : '-',
                'skala_probabilitas' => $monitoring ? $this->formatSkalaProbabilitas($monitoring->skalaProbabilitas) : '-',
                'eksposur_risiko' => $this->formatCurrency($monitoring->eksposur_risiko ?? ($monitoring->eksposure_risiko ?? 0)),
                'skala_risiko' => $monitoring->skala_risiko ?? '-',
                'level_risiko' => $monitoring->level_risiko ?? '-',

                'nilai_efektivitas' => $efektivitasNilai ? $efektivitasNilai . '%' : '-',
                'efektifitas_perlakuan' => $this->calculateEfektifitasVal($efektivitasNilai),
            ];

            $exportData->push($this->prependUnit($rowData, $risiko));
            $nomorUrut++;
        }

        return $exportData;
    }

    private function calculateEfektifitasVal($nilaiEfektivitas)
    {
        if ($nilaiEfektivitas === null || $nilaiEfektivitas === '') return '-';
        return ((float) $nilaiEfektivitas >= 0) ? 'Efektif' : 'Tidak Efektif';
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

    private function formatPercentage($value)
    {
        if ($value === null || $value === '') return '-';
        return $value . '%';
    }

    private function formatSkalaDampak($skala, $obj = null)
    {
        if (!$skala) return '-';
        $deskripsi = $obj->deskripsi ?? '';
        return $deskripsi ? $skala . ' - ' . $deskripsi : $skala;
    }

    private function formatSkalaProbabilitas($obj)
    {
        if (!$obj) return '-';
        $tingkat = $obj->tingkat ?? '-';
        $skala = $obj->skala ?? '';

        if ($tingkat !== '-' && $skala) {
            return $tingkat . ' - ' . $skala;
        }
        return $tingkat;
    }
}
