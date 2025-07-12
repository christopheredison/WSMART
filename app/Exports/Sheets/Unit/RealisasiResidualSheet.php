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

class RealisasiResidualSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
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
        return 'Realisasi Residual';
    }

    /**
     * Return array kosong karena kita handle header secara manual di registerEvents
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
                
                // Insert 3 row baru di atas untuk header bertingkat
                $sheet->insertNewRowBefore(1, 3);

                // Row 1: Header utama
                $sheet->setCellValue('A1', 'Jenis Data');
                $sheet->setCellValue('B1', 'No');
                $sheet->setCellValue('C1', 'Nama BUMN');
                $sheet->setCellValue('D1', 'No Risiko');
                $sheet->setCellValue('E1', 'Peristiwa Risiko');
                $sheet->setCellValue('F1', 'Realisasi Risiko Residual'); // Header gabungan utama
                $sheet->setCellValue('AL1', 'Efektifitas Perlakuan Risiko'); // Kolom terakhir

                // Row 2: Sub-header kategori realisasi
                $sheet->setCellValue('F2', 'Asumsi Perhitungan Dampak');
                $sheet->setCellValue('J2', 'Nilai Dampak');
                $sheet->setCellValue('N2', 'Skala Dampak BUMN');
                $sheet->setCellValue('R2', 'Nilai Probabilitas');
                $sheet->setCellValue('V2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue('Z2', 'Eksposur Risiko');
                $sheet->setCellValue('AD2', 'Skala Risiko BUMN');
                $sheet->setCellValue('AH2', 'Level Risiko BUMN');

                // Row 3: Sub-header quarter (Q1-Q4 untuk setiap kategori)
                $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
                $startColumns = ['F', 'J', 'N', 'R', 'V', 'Z', 'AD', 'AH']; // Starting column for each category
                
                foreach ($startColumns as $startCol) {
                    $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startCol);
                    foreach ($quarters as $index => $quarter) {
                        $currentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + $index);
                        $sheet->setCellValue($currentCol . '3', $quarter);
                    }
                }

                // Merge sel header vertikal untuk kolom A-E (3 level)
                $mergeColumns = ['A', 'B', 'C', 'D', 'E'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}3");
                }

                // Merge sel header "Realisasi Risiko Residual" secara horizontal (F1 sampai AK1) - 32 kolom
                $sheet->mergeCells('F1:AK1');

                // Merge sel header "Efektifitas Perlakuan Risiko" secara vertikal (AL1 sampai AL3)
                $sheet->mergeCells('AL1:AL3');

                // Merge sel header kategori di level 2 (masing-masing 4 kolom untuk Q1-Q4)
                $sheet->mergeCells('F2:I2'); // Asumsi Perhitungan Dampak
                $sheet->mergeCells('J2:M2'); // Nilai Dampak
                $sheet->mergeCells('N2:Q2'); // Skala Dampak BUMN
                $sheet->mergeCells('R2:U2'); // Nilai Probabilitas
                $sheet->mergeCells('V2:Y2'); // Skala Probabilitas BUMN
                $sheet->mergeCells('Z2:AC2'); // Eksposur Risiko
                $sheet->mergeCells('AD2:AG2'); // Skala Risiko BUMN
                $sheet->mergeCells('AH2:AK2'); // Level Risiko BUMN (4 kolom untuk Q1-Q4)

                // Row 3 tidak perlu merge karena sudah diisi quarter individual

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
                $sheet->getStyle('A1:AL3')->applyFromArray($headerStyle);

                // Style khusus untuk sub-header kategori (row 2) - Background abu-abu
                $kategoriHeaderStyle = [
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
                $sheet->getStyle('F2:AK2')->applyFromArray($kategoriHeaderStyle);

                // Style khusus untuk sub-header Q1-Q4 (row 3) - Background putih
                $quarterHeaderStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFFFF']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('F3:AK3')->applyFromArray($quarterHeaderStyle);

                // Tambahkan border untuk semua data yang akan terisi
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
                    ->count();
                
                // Border hanya untuk row yang berisi data (header + data aktual)
                if ($risikosCount > 0) {
                    $maxDataRow = 3 + $risikosCount; // Row 3 (header) + jumlah data aktual
                    $sheet->getStyle('A4:AL' . $maxDataRow)->applyFromArray($dataStyle);
                    
                    // Tambahkan pewarnaan background untuk Level Risiko BUMN
                    $this->applyLevelRisikoColoring($sheet, $maxDataRow);
                }
                
                foreach (range('A', 'AL') as $column) {
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
        // Kolom Level Risiko BUMN (AH, AI, AJ, AK untuk Q1-Q4)
        $levelRisikoColumns = ['AH', 'AI', 'AJ', 'AK'];
        
        // Mulai dari row 4 (setelah header)
        for ($row = 4; $row <= $maxRow; $row++) {
            foreach ($levelRisikoColumns as $column) {
                $cellValue = $sheet->getCell($column . $row)->getValue();
                $backgroundColor = $this->getLevelRisikoBackgroundColor($cellValue);
                
                if ($backgroundColor) {
                    $sheet->getStyle($column . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $backgroundColor]
                        ]
                    ]);
                }
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
                'riskAnalysis.skalaDampakResidualQ1Obj',
                'riskAnalysis.skalaDampakResidualQ2Obj',
                'riskAnalysis.skalaDampakResidualQ3Obj',
                'riskAnalysis.skalaDampakResidualQ4Obj',
                'riskAnalysis.skalaProbabilitasResidualQ1',
                'riskAnalysis.skalaProbabilitasResidualQ2',
                'riskAnalysis.skalaProbabilitasResidualQ3',
                'riskAnalysis.skalaProbabilitasResidualQ4'
            ])
            ->where('periode_id', $this->periodeId)
            ->where('unit_id', $this->unitId)
            ->get()
            ->sortByDesc('riskAnalysis.skala_risiko');

        $exportData = new Collection();
        $nomorUrut = 1;

        foreach ($risikos as $risiko) {
            $analisa = $risiko->riskAnalysis;
            
            if (!$analisa) {
                continue; // Skip jika tidak ada risk analysis
            }

            // Tentukan jenis data berdasarkan kategori dampak
            $jenisData = ucfirst(strtolower($analisa->kategori_dampak ?? 'Kuantitatif'));
            
            $rowData = [
                'jenis_data' => $jenisData,
                'no' => $nomorUrut,
                'nama_bumn' => 'PT PP Persero Tbk',
                'no_risiko' => $nomorUrut,
                'peristiwa_risiko' => $risiko->peristiwa_risiko ?? '-',
                
                // Asumsi Perhitungan Dampak Q1-Q4
                'asumsi_perhitungan_dampak_q1' => $this->getAsumsiPerhitunganDampak($analisa, 'q1'),
                'asumsi_perhitungan_dampak_q2' => $this->getAsumsiPerhitunganDampak($analisa, 'q2'),
                'asumsi_perhitungan_dampak_q3' => $this->getAsumsiPerhitunganDampak($analisa, 'q3'),
                'asumsi_perhitungan_dampak_q4' => $this->getAsumsiPerhitunganDampak($analisa, 'q4'),
                
                // Nilai Dampak Q1-Q4
                'nilai_dampak_q1' => $this->getNilaiDampak($analisa, 'q1'),
                'nilai_dampak_q2' => $this->getNilaiDampak($analisa, 'q2'),
                'nilai_dampak_q3' => $this->getNilaiDampak($analisa, 'q3'),
                'nilai_dampak_q4' => $this->getNilaiDampak($analisa, 'q4'),
                
                // Skala Dampak Q1-Q4
                'skala_dampak_q1' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q1, $analisa->skalaDampakResidualQ1Obj),
                'skala_dampak_q2' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q2, $analisa->skalaDampakResidualQ2Obj),
                'skala_dampak_q3' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q3, $analisa->skalaDampakResidualQ3Obj),
                'skala_dampak_q4' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q4, $analisa->skalaDampakResidualQ4Obj),
                
                // Nilai Probabilitas Q1-Q4
                'nilai_probabilitas_q1' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q1 ?? 0),
                'nilai_probabilitas_q2' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q2 ?? 0),
                'nilai_probabilitas_q3' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q3 ?? 0),
                'nilai_probabilitas_q4' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q4 ?? 0),
                
                // Skala Probabilitas Q1-Q4
                'skala_probabilitas_q1' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ1),
                'skala_probabilitas_q2' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ2),
                'skala_probabilitas_q3' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ3),
                'skala_probabilitas_q4' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ4),
                
                // Eksposur Risiko Q1-Q4
                'eksposur_risiko_q1' => $this->formatCurrency($analisa->eksposur_risiko_residual_q1 ?? 0),
                'eksposur_risiko_q2' => $this->formatCurrency($analisa->eksposur_risiko_residual_q2 ?? 0),
                'eksposur_risiko_q3' => $this->formatCurrency($analisa->eksposur_risiko_residual_q3 ?? 0),
                'eksposur_risiko_q4' => $this->formatCurrency($analisa->eksposur_risiko_residual_q4 ?? 0),
                
                // Skala Risiko Q1-Q4
                'skala_risiko_q1' => $analisa->skala_risiko_residual_q1 ?? '-',
                'skala_risiko_q2' => $analisa->skala_risiko_residual_q2 ?? '-',
                'skala_risiko_q3' => $analisa->skala_risiko_residual_q3 ?? '-',
                'skala_risiko_q4' => $analisa->skala_risiko_residual_q4 ?? '-',
                
                // Level Risiko Q1-Q4
                'level_risiko_q1' => $analisa->level_risiko_residual_q1 ?? '-',
                'level_risiko_q2' => $analisa->level_risiko_residual_q2 ?? '-',
                'level_risiko_q3' => $analisa->level_risiko_residual_q3 ?? '-',
                'level_risiko_q4' => $analisa->level_risiko_residual_q4 ?? '-',
                
                // Efektifitas Perlakuan Risiko
                'efektifitas_perlakuan' => $this->calculateEfektifitas($analisa),
            ];

            $exportData->push($rowData);
            $nomorUrut++;
        }

        return $exportData;
    }

    /**
     * Get asumsi perhitungan dampak berdasarkan kategori dan quarter
     */
    private function getAsumsiPerhitunganDampak($analisa, $quarter)
    {
        if ($analisa->kategori_dampak === 'Kualitatif') {
            return 'Sesuai dengan data di Risiko Inherent Kualitatif';
        }
        
        return $analisa->{'asumsi_perhitungan_dampak_residual_' . $quarter} ?? '-';
    }

    /**
     * Get nilai dampak berdasarkan kategori dan quarter
     */
    private function getNilaiDampak($analisa, $quarter)
    {
        if ($analisa->kategori_dampak === 'Kualitatif') {
            return 'Rp0';
        }
        
        return $this->formatCurrency($analisa->{'nilai_dampak_residual_' . $quarter} ?? 0);
    }

    /**
     * Calculate efektifitas perlakuan risiko
     */
    private function calculateEfektifitas($analisa)
    {
        // Logic: Jika mencapai residual target (misalnya Q4 level low) maka Efektif
        // Untuk sementara, anggap efektif jika ada data level risiko Q4
        $levelRisikoQ4 = $analisa->level_risiko_residual_q4;
        
        if ($levelRisikoQ4 && in_array(strtolower($levelRisikoQ4), ['low', 'low to moderate'])) {
            return 'Efektif';
        }
        
        return 'Tidak Efektif';
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
    private function formatSkalaDampak($skala, $skalaDampakObj)
    {
        if (!$skala) return '-';
        
        $deskripsi = optional($skalaDampakObj)->deskripsi ?? '';
        
        if ($deskripsi) {
            return $skala . ' - ' . $deskripsi;
        }
        
        return $skala;
    }

    /**
     * Format skala probabilitas dengan deskripsi
     */
    private function formatSkalaProbabilitas($skalaProbabilitasObj)
    {
        if (!$skalaProbabilitasObj) return '-';
        
        $tingkat = $skalaProbabilitasObj->tingkat ?? '-';
        $skala = $skalaProbabilitasObj->skala ?? '';
        
        if ($tingkat !== '-' && $skala) {
            return $tingkat . ' - ' . $skala;
        }
        
        return $tingkat;
    }
}