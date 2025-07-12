<?php

namespace App\Exports\Sheets\Unit;

use App\Models\IdentifikasiRisiko;
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

class RisikoInherentKualitatifSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    private $periodeId;
    private $unitId;

    public function __construct(int $periodeId, int $unitId)
    {
        $this->periodeId = $periodeId;
        $this->unitId = $unitId;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Risiko Inherent Kualitatif';
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
                $sheet->setCellValue('D1', 'Peristiwa Risiko');
                $sheet->setCellValue('E1', 'Risiko Inherent'); // Header gabungan

                // Row 2: Sub-header untuk Risiko Inherent
                $sheet->setCellValue('E2', 'Penjelasan Dampak Kualitatif');
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

                // Merge sel header "Risiko Inherent" secara horizontal (dari E1 sampai L1)
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
                $risikosCount = \App\Models\IdentifikasiRisiko::where('periode_id', $this->periodeId)
                    ->where('unit_id', $this->unitId)
                    ->whereHas('riskAnalysis', function ($query) {
                        $query->where('kategori_dampak', 'Kualitatif');
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
                return 'C6E0B4'; // Hijau Muda
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
        // Ambil data utama dan urutkan berdasarkan skala risiko tertinggi
        $risikos = IdentifikasiRisiko::with([
                'unit',
                'peristiwaRisiko',
                'riskAnalysis.skalaDampakObj',
                'riskAnalysis.skalaProbabilitas'
            ])
            ->where('periode_id', $this->periodeId)
            ->where('unit_id', $this->unitId)
            ->whereHas('riskAnalysis', function ($query) {
                $query->where('kategori_dampak', 'Kualitatif');
            })
            ->get()
            ->sortByDesc('riskAnalysis.skala_risiko');

        $exportData = new Collection();
        $nomorUrut = 1;

        foreach ($risikos as $risiko) {
            $analisa = $risiko->riskAnalysis;
            
            $rowData = [
                'no' => $nomorUrut,
                'nama_bumn' => 'PT Wijaya Karya (Persero) Tbk',
                'no_risiko' => $nomorUrut,
                'peristiwa_risiko' => $risiko->peristiwa_risiko ?? '-',
                'penjelasan_dampak_kualitatif' => $analisa->deskripsi_dampak ?? $risiko->deskripsi_dampak ?? '-',
                'nilai_dampak' => 'Rp0', // Untuk kualitatif selalu Rp0
                'skala_dampak' => $this->formatSkalaDampak($analisa),
                'nilai_probabilitas' => $this->formatPercentage($analisa->nilai_probabilitas ?? 0),
                'skala_probabilitas' => $this->formatSkalaProbabilitas($analisa),
                'eksposur_risiko' => $this->formatCurrency($analisa->eksposur_risiko ?? 0),
                'skala_risiko' => $analisa->skala_risiko ?? '-',
                'level_risiko' => $analisa->level_risiko ?? '-',
            ];

            $exportData->push($rowData);
            $nomorUrut++;
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

    /**
     * Format percentage
     */
    private function formatPercentage($value)
    {
        return $value . '%';
    }

    /**
     * Format skala dampak dengan deskripsi
     */
    private function formatSkalaDampak($analisa)
    {
        $skala = $analisa->skala_dampak ?? '-';
        $deskripsi = optional($analisa->skalaDampakObj)->deskripsi ?? '';
        
        if ($skala !== '-' && $deskripsi) {
            return $skala . ' - ' . $deskripsi;
        }
        
        return $skala;
    }

    /**
     * Format skala probabilitas dengan deskripsi
     */
    private function formatSkalaProbabilitas($analisa)
    {
        $tingkat = optional($analisa->skalaProbabilitas)->tingkat ?? '-';
        $skala = optional($analisa->skalaProbabilitas)->skala ?? '';
        
        if ($tingkat !== '-' && $skala) {
            return $tingkat . ' - ' . $skala;
        }
        
        return $tingkat;
    }
}