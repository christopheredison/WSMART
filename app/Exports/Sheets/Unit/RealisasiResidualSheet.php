<?php

namespace App\Exports\Sheets\Unit;

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

class RealisasiResidualSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStrictNullComparison
{
    private $risikos;
    private $bulan;

    public function __construct(Collection $risikos, $bulan = null)
    {
        $this->risikos = $risikos;
        $this->bulan = $bulan;
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
                $sheet->insertNewRowBefore(1, 2);

                $namaBulanList = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $teksBulan = $this->bulan ? ' (' . $namaBulanList[(int)$this->bulan] . ')' : '';

                // Row 1: Header utama
                $sheet->setCellValue('A1', 'Jenis Data');
                $sheet->setCellValue('B1', 'No');
                $sheet->setCellValue('C1', 'Nama BUMN');
                $sheet->setCellValue('D1', 'No Risiko');
                $sheet->setCellValue('E1', 'Peristiwa Risiko');
                $sheet->setCellValue('F1', 'Realisasi Risiko Residual' . $teksBulan);

                $sheet->setCellValue('M1', 'Nilai Efektivitas');
                $sheet->setCellValue('N1', 'Efektifitas Perlakuan Risiko');

                // Row 2: Sub-header
                $sheet->setCellValue('F2', 'Nilai Dampak');
                $sheet->setCellValue('G2', 'Skala Dampak BUMN');
                $sheet->setCellValue('H2', 'Nilai Probabilitas');
                $sheet->setCellValue('I2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue('J2', 'Eksposur Risiko');
                $sheet->setCellValue('K2', 'Skala Risiko BUMN');
                $sheet->setCellValue('L2', 'Level Risiko BUMN');

                $mergeColumns = ['A', 'B', 'C', 'D', 'E'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                $sheet->mergeCells('F1:L1');
                $sheet->mergeCells('M1:M2');
                $sheet->mergeCells('N1:N2');

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
                $sheet->getStyle('A1:N2')->applyFromArray($headerStyle);

                $subHeaderStyle = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBDBDB']
                    ]
                ];
                $sheet->getStyle('F2:L2')->applyFromArray($subHeaderStyle);

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
                    $dataRange = 'A3:N' . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);

                    $centerCols = ['A', 'B', 'D', 'G', 'H', 'I', 'K', 'L', 'M', 'N'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}3:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    $this->applyLevelRisikoColoring($sheet, $lastRow);
                }

                foreach (range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function applyLevelRisikoColoring($sheet, $maxRow)
    {
        $column = 'L';
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
                $exportData->push([
                    'jenis_data' => '-', 'no' => $nomorUrut, 'nama_bumn' => 'PT Wijaya Karya (Persero) Tbk',
                    'no_risiko' => $nomorUrut, 'peristiwa_risiko' => $risiko->peristiwa_risiko ?? '-',
                    'nilai_dampak' => 0, 'skala_dampak' => '-', 
                    'nilai_probabilitas' => '-', 'skala_probabilitas' => '-', 'eksposur_risiko' => 0,
                    'skala_risiko' => '-', 'level_risiko' => '-', 'nilai_efektivitas' => '-', 'efektifitas_perlakuan' => '-'
                ]);
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

            $exportData->push($rowData);
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
