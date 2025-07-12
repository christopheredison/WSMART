<?php

namespace App\Exports\Sheets\Project;

use App\Models\ProjectRisk;
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

class RisikoResidualKuantitatifSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
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
        return 'Risiko Residual Kuantitatif';
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
                $sheet->setCellValue('E1', 'Risiko Residual');

                // Row 2: Sub-header untuk Risiko Residual
                $sheet->setCellValue('E2', 'Asumsi Perhitungan Dampak');
                $sheet->setCellValue('F2', 'Nilai Dampak');
                $sheet->setCellValue('G2', 'Skala Dampak BUMN');
                $sheet->setCellValue('H2', 'Nilai Probabilitas');
                $sheet->setCellValue('I2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue('J2', 'Eksposur Risiko');
                $sheet->setCellValue('K2', 'Skala Risiko BUMN');
                $sheet->setCellValue('L2', 'Level Risiko BUMN');

                // Merge sel header vertikal untuk kolom yang tidak punya sub-header (A-D)
                $mergeColumns = ['A', 'B', 'C', 'D'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge sel header "Risiko Residual" secara horizontal (dari E1 sampai L1)
                $sheet->mergeCells('E1:L1');

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
                $sheet->getStyle('A1:L2')->applyFromArray($headerStyle);

                // Style khusus untuk sub-header (row 2 kolom E-L) - Background abu-abu
                $subHeaderStyle = [
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
                $sheet->getStyle('E2:L2')->applyFromArray($subHeaderStyle);

                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                
                // Hitung jumlah data aktual untuk border yang tepat
                $risikosCount = ProjectRisk::where('project_periode_list_id', $this->projectId)
                    ->whereHas('projectRiskAnalisa', function ($query) {
                        $query->where('kategori_dampak', 'Kuantitatif');
                    })
                    ->count();
                
                // Border hanya untuk row yang berisi data (header + data aktual)
                if ($risikosCount > 0) {
                    $maxDataRow = 2 + $risikosCount; // Row 2 (header) + jumlah data aktual
                    $sheet->getStyle('A3:L' . $maxDataRow)->applyFromArray($dataStyle);
                    
                    // Tambahkan pewarnaan background untuk Level Risiko BUMN
                    $this->applyLevelRisikoColoring($sheet, $maxDataRow);
                }

                foreach (range('A', 'L') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    /**
     * Apply coloring untuk Level Risiko BUMN berdasarkan nilai level risiko
     */
    private function applyLevelRisikoColoring($sheet, $maxRow)
    {
        // Kolom Level Risiko BUMN (L)
        $levelRisikoColumn = 'L';
        
        // Mulai dari row 3 (setelah header)
        for ($row = 3; $row <= $maxRow; $row++) {
            $cellValue = $sheet->getCell($levelRisikoColumn . $row)->getValue();
            $backgroundColor = $this->getLevelRisikoBackgroundColor($cellValue);
            
            if ($backgroundColor) {
                $sheet->getStyle($levelRisikoColumn . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $backgroundColor]
                    ]
                ]);
            }
        }
    }

    /**
     * Get background color berdasarkan level risiko
     */
    private function getLevelRisikoBackgroundColor($levelRisiko)
    {
        if (!$levelRisiko || $levelRisiko === '-') {
            return null;
        }
        
        $levelRisiko = strtolower(trim($levelRisiko));
        
        switch ($levelRisiko) {
            case 'low':
                return '92D050'; // Hijau Tua
            case 'low to moderate':
                return 'C5E0B4'; // Hijau Muda
            case 'moderate':
                return 'FFFF00'; // Kuning
            case 'moderate to high':
                return 'FFC000'; // Orange
            case 'high':
                return 'FF0000'; // Merah
            default:
                return null;
        }
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        // Ambil data risiko dengan kategori dampak kuantitatif
        $risikos = ProjectRisk::with([
            'projectPeriodeList.project',
            'projectRiskAnalisa.skalaDampakResidualObj',
            'projectRiskAnalisa.skalaProbabilitasResidual',
            'peristiwaRisiko'
        ])
            ->where('project_id', $this->projectId)
            ->whereHas('projectRiskAnalisa', function ($query) {
                $query->where('kategori_dampak', 'Kuantitatif');
            })
            ->get()
            ->sortByDesc('projectRiskAnalisa.skala_risiko_residual');

        $exportData = new Collection();
        $nomorUrut = 1;

        foreach ($risikos as $risiko) {
            $project = $risiko->projectPeriodeList->project;
            $analisa = $risiko->projectRiskAnalisa;

            $rowData = [
                'no' => $nomorUrut,
                'nama_project' => $project->project_name ?? '-',
                'no_risiko' => $nomorUrut,
                'peristiwa_risiko' => $risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-',
                'asumsi_perhitungan_dampak' => $analisa->asumsi_perhitungan_dampak_residual ?? '-',
                'nilai_dampak' => $this->formatRupiah($analisa->nilai_dampak_residual),
                'skala_dampak_bumn' => $this->formatSkalaDampakResidual($analisa),
                'nilai_probabilitas' => $this->formatPercentage($analisa->nilai_probabilitas_residual),
                'skala_probabilitas_bumn' => $this->formatSkalaProbabilitasResidual($analisa),
                'eksposur_risiko' => $this->formatRupiah($analisa->eksposur_risiko_residual),
                'skala_risiko_bumn' => $analisa->skala_risiko_residual ?? '-',
                'level_risiko_bumn' => $analisa->level_risiko_residual ?? '-'
            ];

            $exportData->push($rowData);
            $nomorUrut++;
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
        if ($value === null) {
            return '-';
        }
        return $value . '%';
    }

    /**
     * Format skala dampak residual dengan deskripsi
     */
    private function formatSkalaDampakResidual($analisa)
    {
        if (!$analisa->skala_dampak_residual) {
            return '-';
        }
        
        $deskripsi = optional($analisa->skalaDampakResidualObj)->deskripsi;
        if ($deskripsi) {
            return '(' . $analisa->skala_dampak_residual . ') ' . $deskripsi;
        }
        
        return $analisa->skala_dampak_residual;
    }

    /**
     * Format skala probabilitas residual dengan tingkat dan skala
     */
    private function formatSkalaProbabilitasResidual($analisa)
    {
        if (!$analisa->skalaProbabilitasResidual) {
            return '-';
        }
        
        $tingkat = $analisa->skalaProbabilitasResidual->tingkat;
        $skala = $analisa->skalaProbabilitasResidual->skala;
        
        if ($tingkat && $skala) {
            return '(' . $tingkat . ') ' . $skala;
        }
        
        return $tingkat ?: $skala ?: '-';
    }
}