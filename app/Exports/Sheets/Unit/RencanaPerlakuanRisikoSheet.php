<?php

namespace App\Exports\Sheets\Unit;

use App\Models\OpsiPerlakuanRisiko;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class RencanaPerlakuanRisikoSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStrictNullComparison
{
    private $risikos;
    private $opsiPerlakuan = [];
    private $timelineData = [];

    public function __construct(Collection $risikos)
    {
        $this->risikos = $risikos;
        $this->loadOpsiPerlakuan();
    }

    private function loadOpsiPerlakuan()
    {
        $opsiPerlakuanData = OpsiPerlakuanRisiko::all();
        foreach ($opsiPerlakuanData as $opsi) {
            $this->opsiPerlakuan[$opsi->id] = $opsi->opsi_perlakuan_risiko ?? '-';
        }
    }

    public function title(): string
    {
        return 'Rencana Perlakuan Risiko';
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

                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Nama BUMN');
                $sheet->setCellValue('C1', 'No Risiko');
                $sheet->setCellValue('D1', 'Tipe Perlakuan');
                $sheet->setCellValue('E1', 'Kode Risiko (Penyebab/Dampak)');
                $sheet->setCellValue('F1', 'Penyebab/Dampak Risiko');
                $sheet->setCellValue('G1', 'Opsi Perlakuan Risiko');
                $sheet->setCellValue('H1', 'Rencana Perlakuan Risiko');
                $sheet->setCellValue('I1', 'Output Perlakuan Risiko');
                $sheet->setCellValue('J1', 'Biaya Perlakuan Risiko');
                $sheet->setCellValue('K1', 'Progress Perlakuan Risiko');
                $sheet->setCellValue('L1', 'PIC');
                $sheet->setCellValue('M1', 'Timeline');

                $sheet->setCellValue('M2', '1');
                $sheet->setCellValue('N2', '2');
                $sheet->setCellValue('O2', '3');
                $sheet->setCellValue('P2', '4');
                $sheet->setCellValue('Q2', '5');
                $sheet->setCellValue('R2', '6');
                $sheet->setCellValue('S2', '7');
                $sheet->setCellValue('T2', '8');
                $sheet->setCellValue('U2', '9');
                $sheet->setCellValue('V2', '10');
                $sheet->setCellValue('W2', '11');
                $sheet->setCellValue('X2', '12');

                for ($col = 'A'; $col <= 'L'; $col++) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                $sheet->mergeCells('M1:X1');

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
                $sheet->getStyle('A1:X2')->applyFromArray($headerStyle);

                $sheet->getStyle('M2:X2')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBDBDB']
                    ]
                ]);

                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 2) {
                    $sheet->getStyle('A3:X' . $lastRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                        'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);

                    $sheet->getStyle('E3:E' . $lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                    $centerCols = ['A', 'C', 'D', 'E', 'G', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}3:{$col}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }

                    $this->applyTimelineColoring($sheet, $lastRow);
                }

                foreach (range('A', 'X') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function applyTimelineColoring($sheet, $maxRow)
    {
        foreach ($this->timelineData as $rowIndex => $monthsData) {
            $actualRow = $rowIndex + 3;
            if ($actualRow <= $maxRow) {
                for ($month = 0; $month < 12; $month++) {
                    if (isset($monthsData[$month]) && $monthsData[$month] === true) {
                        $columnLetter = chr(77 + $month);
                        $sheet->getStyle($columnLetter . $actualRow)->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '5B9BD5']
                            ]
                        ]);
                    }
                }
            }
        }
    }

    public function collection()
    {
        $exportData = new Collection();
        $nomorUrutRisiko = 1;
        $currentRowIndex = 0;

        foreach ($this->risikos as $risiko) {
            $perlakuanPenyebab = $risiko->penyebabRisiko->flatMap(function ($penyebab) {
                return $penyebab->perlakuanPenyebabRisiko->map(function ($p) use ($penyebab) {
                    $p->tipe_perlakuan = 'Penyebab';
                    $p->nama_referensi = $penyebab->penyebab_risiko;
                    return $p;
                });
            });

            $perlakuanDampak = $risiko->dampakRisikos->flatMap(function ($dampak) {
                return $dampak->perlakuanDampakRisikos->map(function ($p) use ($dampak) {
                    $p->tipe_perlakuan = 'Dampak';
                    $p->nama_referensi = $dampak->dampak_risiko;
                    return $p;
                });
            });

            $allPerlakuan = collect()->merge($perlakuanPenyebab)->merge($perlakuanDampak);

            if ($allPerlakuan->isEmpty()) {
                $rowData = [
                    'no' => $nomorUrutRisiko,
                    'nama_bumn' => 'PT Wijaya Karya (Persero) Tbk',
                    'no_risiko' => $nomorUrutRisiko,
                    'tipe_perlakuan' => '-',
                    'kode_referensi' => '-',
                    'nama_referensi' => '-',
                    'opsi_perlakuan' => '-',
                    'rencana_perlakuan' => '-',
                    'output_perlakuan' => '-',
                    'biaya_perlakuan' => '0',
                    'progress' => '-',
                    'pic' => '-',
                ];
                $timelineMonths = array_fill(0, 12, '');
                $exportData->push(array_merge($rowData, $timelineMonths));

                $this->timelineData[$currentRowIndex] = array_fill(0, 12, false);
                $currentRowIndex++;
            } else {
                $isFirstRowOfGroup = true;
                $nomorUrutPerlakuan = 1;

                foreach ($allPerlakuan as $perlakuan) {
                    $timelineStart = $perlakuan->timeline_perlakuan_risiko_start;
                    $timelineEnd = $perlakuan->timeline_perlakuan_risiko_end;
                    $timelineMonths = array_fill(0, 12, '');
                    $timelineBooleans = array_fill(0, 12, false);

                    if ($timelineStart && $timelineEnd) {
                        $period = CarbonPeriod::create($timelineStart, '1 month', $timelineEnd);
                        foreach ($period as $date) {
                            $monthIndex = (int)$date->format('n') - 1;
                            if (isset($timelineMonths[$monthIndex])) {
                                $timelineBooleans[$monthIndex] = true;
                            }
                        }
                    }

                    $progress = '-';
                    if ($perlakuan->tipe_perlakuan == 'Penyebab' && $perlakuan->lastMonitoring) {
                        $progress = ($perlakuan->lastMonitoring->progress_perlakuan_risiko ?? 0) . '%';
                    } elseif ($perlakuan->tipe_perlakuan == 'Dampak' && $perlakuan->lastMonitoring) {
                        $progress = ($perlakuan->lastMonitoring->progress_perlakuan_risiko ?? 0) . '%';
                    }

                    $rowData = [
                        'no' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
                        'nama_bumn' => $isFirstRowOfGroup ? 'PT Wijaya Karya (Persero) Tbk' : '',
                        'no_risiko' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
                        'tipe_perlakuan' => $perlakuan->tipe_perlakuan,
                        'kode_referensi' => "'" . $nomorUrutRisiko . '.' . $nomorUrutPerlakuan,
                        'nama_referensi' => $perlakuan->nama_referensi ?? '-',
                        'opsi_perlakuan' => $this->opsiPerlakuan[$perlakuan->opsi_perlakuan_risiko] ?? '-',
                        'rencana_perlakuan' => $perlakuan->rencana_perlakuan_risiko ?? '-',
                        'output_perlakuan' => $perlakuan->output_perlakuan_risiko ?? '-',
                        'biaya_perlakuan' => $this->formatCurrency($perlakuan->biaya_perlakuan_risiko ?? 0),
                        'progress' => $progress,
                        'pic' => $perlakuan->pic ?? '-',
                    ];

                    $exportData->push(array_merge($rowData, $timelineMonths));
                    $this->timelineData[$currentRowIndex] = $timelineBooleans;
                    $currentRowIndex++;

                    $isFirstRowOfGroup = false;
                    $nomorUrutPerlakuan++;
                }
            }
            $nomorUrutRisiko++;
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
}
