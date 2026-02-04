<?php

namespace App\Exports\Sheets\Project;

use App\Models\ProjectRisk;
use App\Models\PenyebabRisikoProject;
use App\Models\OpsiPerlakuanRisiko;
use App\Models\JenisRencanaPerlakuanRisiko;
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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RencanaPerlakuanRisikoSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    private $projectIds;
    private $timelineData = [];
    private $totalRows = 0;
    private $opsiPerlakuan = [];
    private $jenisRencana = [];

    public function __construct(array $projectIds)
    {
        $this->projectIds = $projectIds;

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

                // Header utama
                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Nama Project');
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

                // Sub-header bulan
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

                // Merge sel vertikal untuk kolom A-L
                for ($col = 'A'; $col <= 'L'; $col++) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge header Timeline
                $sheet->mergeCells('M1:X1');

                // Style untuk header utama
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
                $sheet->getStyle('A1:X2')->applyFromArray($headerStyle);

                // Style untuk sub-header bulan
                $monthHeaderStyle = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBDBDB']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('M2:X2')->applyFromArray($monthHeaderStyle);

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

                // Dapatkan baris terakhir SETELAH data collection ditulis
                $lastRow = $sheet->getHighestRow();

                // Terapkan border ke seluruh data jika ada baris (baris > 2)
                if ($lastRow > 2) {
                    $maxDataRow = $lastRow; // Ini adalah baris data terakhir yang sebenarnya

                    // Terapkan border dan style alignment ke semua sel data
                    $sheet->getStyle('A3:X' . $maxDataRow)->applyFromArray($dataStyle);

                    // Format teks untuk kolom Kode Penyebab Risiko (Kolom D)
                    $sheet->getStyle('D3:D' . $maxDataRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                    // Terapkan pewarnaan timeline
                    $this->applyTimelineColoring($sheet, $maxDataRow);

                    // (Tambahan) Atur perataan tengah untuk kolom tertentu
                    $centerCols = ['A', 'C', 'D', 'J', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}3:{$col}{$maxDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                foreach (range('A', 'X') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    /**
     * Apply timeline coloring
     */
    private function applyTimelineColoring($sheet, $maxRow)
    {
        foreach ($this->timelineData as $rowIndex => $monthsData) {
            $actualRow = $rowIndex + 3; // Data dimulai dari baris 3
            if ($actualRow <= $maxRow) {
                for ($month = 0; $month < 12; $month++) {
                    if (isset($monthsData[$month]) && $monthsData[$month] === true) {
                        $columnLetter = chr(77 + $month); // M=77, N=78, dst.
                        $sheet->getStyle($columnLetter . $actualRow)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '5B9BD5']
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000']
                                ]
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
        $risikos = ProjectRisk::with([
            'projectPeriodeList.project',
            'penyebabRisikoProjects.opsiPerlakuanRisiko',
            'penyebabRisikoProjects.jenisRencanaPerlakuanRisiko',
            'penyebabRisikoProjects.perlakuanPenyebabRisiko.lastMonitoring'
        ])
            // ->where('project_id', $this->projectId)
            ->whereIn('project_id', $this->projectIds)
            ->get()
            ->sortByDesc('projectRiskAnalisa.skala_risiko');

        $exportData = new Collection();
        $nomorUrutRisiko = 1;
        $currentRowIndex = 0;

        foreach ($risikos as $risiko) {
            $project = $risiko->projectPeriodeList->project;
            $penyebabRisikos = $risiko->penyebabRisikoProjects;

            if ($penyebabRisikos->isEmpty()) {
                $timelineMonths = array_fill(0, 12, '');
                $timelineBooleans = array_fill(0, 12, false);

                $rowData = [
                    'no' => $nomorUrutRisiko,
                    'nama_project' => $project->project_name ?? '-',
                    'no_risiko' => $nomorUrutRisiko,
                    'kode_penyebab_risiko' => '-',
                    'penyebab_risiko' => '-',
                    'opsi_perlakuan_risiko' => '-',
                    'jenis_rencana_perlakuan_risiko' => '-',
                    'rencana_perlakuan_risiko' => '-',
                    'output_perlakuan_risiko' => '-',
                    'biaya_perlakuan_risiko' => '-',
                    'jenis_program_rkap' => '-',
                    'pic' => '-',
                ];

                $finalRow = array_merge($rowData, $timelineMonths);
                $exportData->push($finalRow);
                $this->timelineData[$currentRowIndex] = $timelineBooleans;
                $currentRowIndex++;
            } else {
                $isFirstRowOfGroup = true;
                $nomorUrutPenyebab = 1;

                foreach ($penyebabRisikos as $penyebab) {
                    $perlakuanRisikos = $penyebab->perlakuanPenyebabRisiko;

                    if (!$perlakuanRisikos->isEmpty()) {
                        $isFirstPerlakuan = true;

                        foreach ($perlakuanRisikos as $perlakuan) {
                            $timelineMonths = array_fill(0, 12, '');
                            $timelineBooleans = array_fill(0, 12, false);

                            // Proses rentang timeline dari perlakuan
                            $start = $perlakuan->timeline_perlakuan_risiko_start;
                            $end = $perlakuan->timeline_perlakuan_risiko_end;

                            if ($start && $end) {
                                try {
                                    $startDate = Carbon::parse($start);
                                    $endDate = Carbon::parse($end);

                                    $period = CarbonPeriod::create($startDate, '1 month', $endDate);

                                    foreach ($period as $date) {
                                        $monthIndex = (int)$date->format('n') - 1; // Jan=0, Des=11
                                        if ($monthIndex >= 0 && $monthIndex < 12) {
                                            $timelineBooleans[$monthIndex] = true;
                                        }
                                    }
                                } catch (\Exception $e) {
                                    // Tangani error parsing tanggal
                                }
                            }

                            $lastMonitoring = $perlakuan->perlakuanPenyebabMonitorings()->orderBy('created_at', 'desc')->first();
                            $rowData = [
                                'no' => ($isFirstRowOfGroup && $isFirstPerlakuan) ? $nomorUrutRisiko : '',
                                'nama_project' => ($isFirstRowOfGroup && $isFirstPerlakuan) ? ($project->project_name ?? '-') : '',
                                'no_risiko' => ($isFirstRowOfGroup && $isFirstPerlakuan) ? $nomorUrutRisiko : '',
                                'kode_penyebab_risiko' => $isFirstPerlakuan ? "'" . $nomorUrutRisiko . '.' . $nomorUrutPenyebab : '',
                                'penyebab_risiko' => $isFirstPerlakuan ? $penyebab->penyebab_risiko ?? '-' : '',
                                'opsi_perlakuan_risiko' => $this->opsiPerlakuan[$perlakuan->opsi_perlakuan_risiko] ?? '-',
                                'jenis_rencana_perlakuan_risiko' => $this->jenisRencana[$perlakuan->jenis_rencana_perlakuan_risiko] ?? '-',
                                'rencana_perlakuan_risiko' => $perlakuan->rencana_perlakuan_risiko ?? '-',
                                'output_perlakuan_risiko' => $perlakuan->output_perlakuan_risiko ?? '-',
                                'biaya_perlakuan_risiko' => $this->formatRupiah($perlakuan->biaya_perlakuan_risiko),
                                'jenis_program_rkap' => $lastMonitoring->jenis_program_rkap ?? '-',
                                'pic' => $perlakuan->pic ?? '-',
                            ];

                            $finalRow = array_merge($rowData, $timelineMonths);
                            $exportData->push($finalRow);
                            $this->timelineData[$currentRowIndex] = $timelineBooleans;
                            $currentRowIndex++;

                            $isFirstPerlakuan = false;
                        }
                    }

                    $isFirstRowOfGroup = false;
                    $nomorUrutPenyebab++;
                }
            }

            $nomorUrutRisiko++;
        }

        $this->totalRows = $exportData->count();

        return $exportData;
    }

    /**
     * Format nilai rupiah
     */
    private function formatRupiah($value)
    {
        if ($value === null || $value === 0) {
            return '-';
        }
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
