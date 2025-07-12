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

class RealisasiResidualSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
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
        return 'Realisasi Residual';
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
                $sheet->insertNewRowBefore(1, 3);

                // Row 1: Header utama
                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Nama Project');
                $sheet->setCellValue('C1', 'No Risiko');
                $sheet->setCellValue('D1', 'Peristiwa Risiko');
                $sheet->setCellValue('E1', 'No Penyebab Risiko');
                $sheet->setCellValue('F1', 'Kode Penyebab Risiko');
                $sheet->setCellValue('G1', 'Penyebab Risiko');
                $sheet->setCellValue('H1', 'Rencana Perlakuan Risiko');
                $sheet->setCellValue('I1', 'Biaya Perlakuan Risiko');
                $sheet->setCellValue('J1', 'Progress Rencana Perlakuan Risiko');
                $sheet->setCellValue('R1', 'Realisasi Biaya Perlakuan Risiko');

                // Row 2: Sub-header untuk Progress dan Realisasi Biaya (Q1-Q4)
                $progressColumns = ['J', 'K', 'L', 'M'];
                $realisasiColumns = ['R', 'S', 'T', 'U'];
                
                for ($i = 0; $i < 4; $i++) {
                    $quarter = 'Q' . ($i + 1);
                    $sheet->setCellValue($progressColumns[$i] . '2', $quarter);
                    $sheet->setCellValue($realisasiColumns[$i] . '2', $quarter);
                }

                // Merge sel header vertikal untuk kolom yang tidak punya sub-header (A-I)
                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge sel header "Progress Rencana Perlakuan Risiko" secara horizontal (dari J1 sampai M1)
                $sheet->mergeCells('J1:M1');

                // Merge sel header "Realisasi Biaya Perlakuan Risiko" secara horizontal (dari R1 sampai U1)
                $sheet->mergeCells('R1:U1');

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
                $sheet->getStyle('A1:U2')->applyFromArray($headerStyle);

                // Style khusus untuk sub-header Progress - Hijau
                $progressSubHeaderStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C5E0B4']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('J2:M2')->applyFromArray($progressSubHeaderStyle);

                // Style khusus untuk sub-header Realisasi Biaya - Orange
                $realisasiSubHeaderStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC000']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('R2:U2')->applyFromArray($realisasiSubHeaderStyle);

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
                    $maxDataRow = 2 + $penyebabCount; // Row 2 (header) + jumlah data aktual
                    $sheet->getStyle('A3:U' . $maxDataRow)->applyFromArray($dataStyle);
                }

                foreach (range('A', 'U') as $column) {
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
            'penyebabRisikoProjects',
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
                    'rencana_perlakuan_risiko' => '-',
                    'biaya_perlakuan_risiko' => '-',
                    'progress_q1' => '-',
                    'progress_q2' => '-',
                    'progress_q3' => '-',
                    'progress_q4' => '-',
                    'realisasi_biaya_q1' => '-',
                    'realisasi_biaya_q2' => '-',
                    'realisasi_biaya_q3' => '-',
                    'realisasi_biaya_q4' => '-'
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
                        'rencana_perlakuan_risiko' => $penyebab->rencana_perlakuan_risiko ?? '-',
                        'biaya_perlakuan_risiko' => $this->formatRupiah($penyebab->biaya_perlakuan_risiko),
                        'progress_q1' => $this->formatPercentage($penyebab->progress_rencana_perlakuan_risiko_q1),
                        'progress_q2' => $this->formatPercentage($penyebab->progress_rencana_perlakuan_risiko_q2),
                        'progress_q3' => $this->formatPercentage($penyebab->progress_rencana_perlakuan_risiko_q3),
                        'progress_q4' => $this->formatPercentage($penyebab->progress_rencana_perlakuan_risiko_q4),
                        'realisasi_biaya_q1' => $this->formatRupiah($penyebab->realisasi_biaya_perlakuan_risiko_q1),
                        'realisasi_biaya_q2' => $this->formatRupiah($penyebab->realisasi_biaya_perlakuan_risiko_q2),
                        'realisasi_biaya_q3' => $this->formatRupiah($penyebab->realisasi_biaya_perlakuan_risiko_q3),
                        'realisasi_biaya_q4' => $this->formatRupiah($penyebab->realisasi_biaya_perlakuan_risiko_q4)
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
     * Format percentage
     */
    private function formatPercentage($value)
    {
        if ($value === null || $value === 0) {
            return '-';
        }
        return $value . '%';
    }
}