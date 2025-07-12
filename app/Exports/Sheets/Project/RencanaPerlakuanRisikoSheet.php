<?php

namespace App\Exports\Sheets\Project;

use App\Models\ProjectRisk;
use App\Models\PenyebabRisikoProject;
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

class RencanaPerlakuanRisikoSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    private $projectId;

    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
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
                $sheet->setCellValue('B1', 'Nama Project');
                $sheet->setCellValue('C1', 'No Risiko');
                $sheet->setCellValue('D1', 'Peristiwa Risiko');
                $sheet->setCellValue('E1', 'No Penyebab Risiko');
                $sheet->setCellValue('F1', 'Kode Penyebab Risiko');
                $sheet->setCellValue('G1', 'Penyebab Risiko');
                $sheet->setCellValue('H1', 'Opsi Perlakuan Risiko');
                $sheet->setCellValue('I1', 'Jenis Rencana Perlakuan Risiko');
                $sheet->setCellValue('J1', 'Rencana Perlakuan Risiko');
                $sheet->setCellValue('K1', 'Output Perlakuan Risiko');
                $sheet->setCellValue('L1', 'Biaya Perlakuan Risiko');
                $sheet->setCellValue('M1', 'PIC');
                $sheet->setCellValue('N1', 'Timeline Perlakuan Risiko');

                // Merge sel header vertikal untuk semua kolom
                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Hapus row 2 karena tidak diperlukan
                $sheet->removeRow(2);

                // Atur style untuk header utama
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
                $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                
                // Hitung jumlah data aktual untuk border yang tepat
                $penyebabCount = PenyebabRisikoProject::whereHas('risiko', function ($query) {
                    $query->where('project_periode_list_id', $this->projectId);
                })->count();
                
                // Border hanya untuk row yang berisi data
                if ($penyebabCount > 0) {
                    $maxDataRow = 1 + $penyebabCount; // Row 1 (header) + jumlah data aktual
                    $sheet->getStyle('A2:N' . $maxDataRow)->applyFromArray($dataStyle);
                }

                foreach (range('A', 'N') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        // Ambil data risiko dengan penyebab risiko
        $risikos = ProjectRisk::with([
            'projectPeriodeList.project',
            'penyebabRisikoProjects.opsiPerlakuanRisiko',
            'penyebabRisikoProjects.jenisRencanaPerlakuanRisiko',
            'peristiwaRisiko'
        ])
            ->where('project_id', $this->projectId)
            ->get()
            ->sortByDesc('projectRiskAnalisa.skala_risiko');

        $exportData = new Collection();
        $nomorUrutRisiko = 1;

        foreach ($risikos as $risiko) {
            $project = $risiko->projectPeriodeList->project;
            $penyebabRisikos = $risiko->penyebabRisikoProjects;

            if ($penyebabRisikos->isEmpty()) {
                // Jika tidak ada penyebab risiko, tetap tampilkan data risiko
                $rowData = [
                    'no' => $nomorUrutRisiko,
                    'nama_project' => $project->project_name ?? '-',
                    'no_risiko' => $nomorUrutRisiko,
                    'peristiwa_risiko' => $risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-',
                    'no_penyebab_risiko' => '-',
                    'kode_penyebab_risiko' => '-',
                    'penyebab_risiko' => '-',
                    'opsi_perlakuan_risiko' => '-',
                    'jenis_rencana_perlakuan_risiko' => '-',
                    'rencana_perlakuan_risiko' => '-',
                    'output_perlakuan_risiko' => '-',
                    'biaya_perlakuan_risiko' => '-',
                    'pic' => '-',
                    'timeline_perlakuan_risiko' => '-'
                ];
                
                $exportData->push($rowData);
            } else {
                // Jika ada penyebab risiko
                $isFirstRowOfGroup = true;
                $nomorUrutPenyebab = 1;

                foreach ($penyebabRisikos as $penyebab) {
                    $rowData = [
                        'no' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
                        'nama_project' => $isFirstRowOfGroup ? ($project->project_name ?? '-') : '',
                        'no_risiko' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
                        'peristiwa_risiko' => $isFirstRowOfGroup ? ($risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-') : '',
                        'no_penyebab_risiko' => $nomorUrutPenyebab,
                        'kode_penyebab_risiko' => "'" . $nomorUrutRisiko . '.' . $nomorUrutPenyebab,
                        'penyebab_risiko' => $penyebab->penyebab_risiko ?? '-',
                        'opsi_perlakuan_risiko' => optional($penyebab->opsiPerlakuanRisiko)->opsi ?? '-',
                        'jenis_rencana_perlakuan_risiko' => optional($penyebab->jenisRencanaPerlakuanRisiko)->jenis ?? '-',
                        'rencana_perlakuan_risiko' => $penyebab->rencana_perlakuan_risiko ?? '-',
                        'output_perlakuan_risiko' => $penyebab->output_perlakuan_risiko ?? '-',
                        'biaya_perlakuan_risiko' => $this->formatRupiah($penyebab->biaya_perlakuan_risiko),
                        'pic' => $penyebab->pic ?? '-',
                        'timeline_perlakuan_risiko' => $this->formatTimeline($penyebab->timeline_perlakuan_risiko)
                    ];

                    $exportData->push($rowData);
                    $isFirstRowOfGroup = false;
                    $nomorUrutPenyebab++;
                }
            }
            
            $nomorUrutRisiko++;
        }

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

    /**
     * Format timeline perlakuan risiko
     */
    private function formatTimeline($timeline)
    {
        if (!$timeline) {
            return '-';
        }

        // Jika timeline berupa array atau string yang dipisah koma
        if (is_array($timeline)) {
            $timelineArray = $timeline;
        } else {
            $timelineArray = explode(',', $timeline);
        }

        // Mapping untuk quarter
        $quarterMapping = [
            '1' => 'Q1',
            '2' => 'Q2', 
            '3' => 'Q3',
            '4' => 'Q4'
        ];

        $formattedTimeline = [];
        foreach ($timelineArray as $period) {
            $period = trim($period);
            if (isset($quarterMapping[$period])) {
                $formattedTimeline[] = $quarterMapping[$period];
            } elseif ($period) {
                $formattedTimeline[] = $period;
            }
        }

        return !empty($formattedTimeline) ? implode(', ', $formattedTimeline) : '-';
    }
}