<?php

namespace App\Exports\Sheets\Unit;

use App\Models\IdentifikasiRisiko;
use App\Models\OpsiPerlakuanRisiko;
use App\Models\JenisRencanaPerlakuanRisiko;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RencanaPerlakuanRisikoSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    private $periodeId;
    private $unitId;
    private $opsiPerlakuan = [];
    private $jenisRencana = [];

    public function __construct(int $periodeId, int $unitId)
    {
        $this->periodeId = $periodeId;
        $this->unitId = $unitId;
      
        $this->loadOpsiPerlakuan();
        $this->loadJenisRencana();
    }

    /**
     * Load opsi perlakuan risiko dari database
     */
    private function loadOpsiPerlakuan()
    {
        $opsiPerlakuanData = OpsiPerlakuanRisiko::all();
        foreach ($opsiPerlakuanData as $opsi) {
            $this->opsiPerlakuan[$opsi->id] = $opsi->opsi_perlakuan_risiko ?? '-';
        }
    }

    /**
     * Load jenis rencana perlakuan risiko dari database
     */
    private function loadJenisRencana()
    {
        $jenisRencanaData = JenisRencanaPerlakuanRisiko::all();
        foreach ($jenisRencanaData as $jenis) {
            $this->jenisRencana[$jenis->id] = $jenis->jenis_rencana_perlakuan_risiko ?? '-';
        }
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Rencana Perlakuan Risiko';
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
                $sheet->setCellValue('B1', 'Nama BUMN');
                $sheet->setCellValue('C1', 'No Risiko');
                $sheet->setCellValue('D1', 'Kode Penyebab Risiko');
                $sheet->setCellValue('E1', 'Penyebab Risiko');
                $sheet->setCellValue('F1', 'Opsi Perlakuan Risiko');
                $sheet->setCellValue('G1', 'Jenis Rencana Perlakuan Risiko');
                $sheet->setCellValue('H1', 'Rencana Perlakuan Risiko');
                $sheet->setCellValue('I1', 'Output Perlakuan Risiko');
                $sheet->setCellValue('J1', 'Biaya Perlakuan Risiko');
                $sheet->setCellValue('K1', 'Jenis Program Dalam RKAP');
                $sheet->setCellValue('L1', 'PIC');
                $sheet->setCellValue('M1', 'Timeline');

                // Row 2: Sub-header bulan untuk Timeline
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

                // Merge sel header vertikal untuk kolom A-L (dari row 1 ke row 2)
                for ($col = 'A'; $col <= 'L'; $col++) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge sel header "Timeline" secara horizontal (dari M1 sampai X1)
                $sheet->mergeCells('M1:X1');

                // Atur style untuk semua header
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

                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                
                $risikosCount = \App\Models\IdentifikasiRisiko::where('periode_id', $this->periodeId)
                    ->where('unit_id', $this->unitId)
                    ->count();
                
                $maxRow = max(100, $risikosCount * 5 + 10);
                $sheet->getStyle('A3:X' . $maxRow)->applyFromArray($dataStyle);

                $this->addTimelineConditionalFormatting($sheet, $maxRow);
            },
        ];
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        // Ambil data utama dan urutkan berdasarkan skala risiko tertinggi
        $risikos = IdentifikasiRisiko::with([
            'unit',
            'riskAnalysis',
            'penyebabRisiko.perlakuanPenyebabRisiko.lastMonitoring'
        ])
            ->where('periode_id', $this->periodeId)
            ->where('unit_id', $this->unitId)
            ->get()
            ->sortByDesc('riskAnalysis.skala_risiko');

        $exportData = new Collection();
        $nomorUrutRisiko = 1;

        foreach ($risikos as $risiko) {
            // Kumpulkan semua perlakuan dari semua penyebab risiko
            $allPerlakuan = $risiko->penyebabRisiko->flatMap(function ($penyebab) {
                return $penyebab->perlakuanPenyebabRisiko;
            });

            if ($allPerlakuan->isEmpty()) {
                $rowData = [
                    'no' => $nomorUrutRisiko,
                    'nama_bumn' => 'PT Wijaya Karya (Persero) Tbk',
                    'no_risiko' => $nomorUrutRisiko,
                    'kode_penyebab_risiko' => '-',
                    'penyebab_risiko' => '-',
                    'opsi_perlakuan' => '-',
                    'jenis_rencana' => '-',
                    'rencana_perlakuan' => '-',
                    'output_perlakuan' => '-',
                    'biaya_perlakuan' => '-',
                    'jenis_program_rkap' => '-',
                    'pic' => '-',
                ];
                $timelineMonths = array_fill(0, 12, '');
                $finalRow = array_merge($rowData, $timelineMonths);
                $exportData->push($finalRow);
            } else {
                // Logika untuk risiko dengan perlakuan
                $jumlahPerlakuan = $allPerlakuan->count();
                $isFirstRowOfGroup = true;
                $nomorUrutPerlakuan = 1;

                foreach ($allPerlakuan as $perlakuan) {
                    $timelineStart = $perlakuan->timeline_perlakuan_risiko_start;
                    $timelineEnd = $perlakuan->timeline_perlakuan_risiko_end;
                    $timelineMonths = array_fill(0, 12, '');

                    // Isi timeline berdasarkan periode start-end
                    if ($timelineStart && $timelineEnd) {
                        $period = CarbonPeriod::create($timelineStart, '1 month', $timelineEnd);
                        foreach ($period as $date) {
                            $monthIndex = (int)$date->format('n') - 1; // index 0-11
                            if (isset($timelineMonths[$monthIndex])) {
                                $timelineMonths[$monthIndex] = 'V'; // Tanda centang
                            }
                        }
                    }

                    $rowData = [
                        'no' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
                        'nama_bumn' => $isFirstRowOfGroup ? 'PT Wijaya Karya (Persero) Tbk' : '',
                        'no_risiko' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
                        'kode_penyebab_risiko' => $nomorUrutRisiko . '.' . $nomorUrutPerlakuan,
                        'penyebab_risiko' => optional($perlakuan->penyebabRisiko)->penyebab_risiko ?? '-',
                        'opsi_perlakuan' => $this->opsiPerlakuan[$perlakuan->opsi_perlakuan_risiko] ?? '-',
                        'jenis_rencana' => $this->jenisRencana[$perlakuan->jenis_rencana_perlakuan_risiko] ?? '-',
                        'rencana_perlakuan' => $perlakuan->rencana_perlakuan_risiko ?? '-',
                        'output_perlakuan' => $perlakuan->output_perlakuan_risiko ?? '-',
                        'biaya_perlakuan' => $this->formatCurrency($perlakuan->biaya_perlakuan_risiko ?? 0),
                        'jenis_program_rkap' => optional($perlakuan->lastMonitoring)->jenis_program_rkap ?? '-',
                        'pic' => $perlakuan->pic ?? '-',
                    ];

                    $finalRow = array_merge($rowData, $timelineMonths);
                    $exportData->push($finalRow);

                    $isFirstRowOfGroup = false;
                    $nomorUrutPerlakuan++;
                }
            }
            $nomorUrutRisiko++;
        }

        return $exportData;
    }

    private function addTimelineConditionalFormatting($sheet, $maxRow)
    {
        $timelineRange = "M3:X{$maxRow}";
        
        $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $conditional->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL);
        $conditional->addCondition('"V"');
        
        // Set style untuk cell yang berisi "V"
        $conditional->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('5B9BD5');
            
        $conditional->getStyle()->getFont()->getColor()->setRGB('FFFFFF'); // Text putih untuk kontras
        
        $conditionalStyles = [$conditional];
        $sheet->getStyle($timelineRange)->setConditionalStyles($conditionalStyles);
    }

    /**
     * Format currency to Rupiah
     */
    private function formatCurrency($value)
    {
        if ($value == 0) return 'Rp0';
        return 'Rp' . number_format($value, 0, ',', '.');
    }
}