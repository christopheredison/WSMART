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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RencanaPerlakuanRisikoSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    private $periodeId;
    private $unitId;
    private $opsiPerlakuan = [];
    private $jenisRencana = [];
    private $timelineData = []; // Store timeline data for coloring

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

                // Atur style untuk header utama (Row 1) - Biru
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

                // Style khusus untuk sub-header bulan (row 2 kolom M-X) - Background abu-abu
                $monthHeaderStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBDBDB']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('M2:X2')->applyFromArray($monthHeaderStyle);

                $lastRow = $sheet->getHighestRow();

                // Style untuk data (border dan wrap text)
                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, // Rata atas
                        'wrapText' => true, // Wrap text untuk data
                    ],
                ];

                // Terapkan border hanya jika ada data (baris > 2)
                if ($lastRow > 2) {
                    // Terapkan style border dan wrap text ke semua data
                    $dataRange = 'A3:X' . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);

                    // Set format text untuk kolom Kode Penyebab Risiko (Kolom D)
                    $sheet->getStyle('D3:D' . $lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                    
                    // Set rata tengah horizontal untuk kolom-kolom tertentu
                    $centerCols = ['A', 'C', 'D', 'J', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}3:{$col}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }
                    
                    // Tambahkan pewarnaan timeline berdasarkan data
                    // Gunakan $lastRow, bukan $maxDataRow
                    $this->applyTimelineColoring($sheet, $lastRow);
                }

                foreach (range('A', 'X') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    /**
     * Apply timeline coloring berdasarkan data yang tersimpan
     */
    private function applyTimelineColoring($sheet, $maxRow)
    {
        foreach ($this->timelineData as $rowIndex => $monthsData) {
            $actualRow = $rowIndex + 3; // Data mulai dari row 3
            if ($actualRow <= $maxRow) {
                for ($month = 0; $month < 12; $month++) {
                    if (isset($monthsData[$month]) && $monthsData[$month] === true) {
                        $columnLetter = chr(77 + $month); // M = 77, N = 78, etc.
                        $sheet->getStyle($columnLetter . $actualRow)->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '5B9BD5'] // Blue background
                            ]
                        ]);
                    }
                }
            }
        }
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
        $currentRowIndex = 0; // Track row index for timeline coloring

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
                $timelineMonths = array_fill(0, 12, ''); // Empty timeline
                $finalRow = array_merge($rowData, $timelineMonths);
                $exportData->push($finalRow);
                
                // Store empty timeline data
                $this->timelineData[$currentRowIndex] = array_fill(0, 12, false);
                $currentRowIndex++;
            } else {
                // Logika untuk risiko dengan perlakuan
                $isFirstRowOfGroup = true;
                $nomorUrutPerlakuan = 1;

                foreach ($allPerlakuan as $perlakuan) {
                    $timelineStart = $perlakuan->timeline_perlakuan_risiko_start;
                    $timelineEnd = $perlakuan->timeline_perlakuan_risiko_end;
                    $timelineMonths = array_fill(0, 12, ''); // Empty by default
                    $timelineBooleans = array_fill(0, 12, false); // For coloring

                    // Isi timeline berdasarkan periode start-end
                    if ($timelineStart && $timelineEnd) {
                        $period = CarbonPeriod::create($timelineStart, '1 month', $timelineEnd);
                        foreach ($period as $date) {
                            $monthIndex = (int)$date->format('n') - 1; // index 0-11
                            if (isset($timelineMonths[$monthIndex])) {
                                $timelineMonths[$monthIndex] = ''; // No text, just background color
                                $timelineBooleans[$monthIndex] = true; // Mark for coloring
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

                    // Store timeline data for coloring
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

    /**
     * Format currency to Rupiah
     */
    private function formatCurrency($value)
    {
        if ($value == 0) return 'Rp0';
        return 'Rp' . number_format($value, 0, ',', '.');
    }
}