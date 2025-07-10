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

class RisikoResidualKuantitatifSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
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
                $sheet->insertNewRowBefore(1, 3);

                // Row 1: Header utama
                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Nama BUMN');
                $sheet->setCellValue('C1', 'No Risiko');
                $sheet->setCellValue('D1', 'Peristiwa Risiko');
                $sheet->setCellValue('E1', 'Risiko Residual');

                // Row 2: Sub-header kategori
                $sheet->setCellValue('E2', 'Asumsi Perhitungan Dampak'); // Perbedaan dengan Kualitatif
                $sheet->setCellValue('I2', 'Nilai Dampak');
                $sheet->setCellValue('M2', 'Skala Dampak BUMN');
                $sheet->setCellValue('Q2', 'Nilai Probabilitas');
                $sheet->setCellValue('U2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue('Y2', 'Eksposur Risiko');
                $sheet->setCellValue('AC2', 'Skala Risiko BUMN');
                $sheet->setCellValue('AG2', 'Level Risiko BUMN');

                // Row 3: Sub-header quarter (Q1-Q4 untuk setiap kategori)
                $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
                $startColumns = ['E', 'I', 'M', 'Q', 'U', 'Y', 'AC', 'AG']; // Starting column for each category
                
                foreach ($startColumns as $startCol) {
                    $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startCol);
                    foreach ($quarters as $index => $quarter) {
                        $currentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + $index);
                        $sheet->setCellValue($currentCol . '3', $quarter);
                    }
                }

                // Merge sel header vertikal untuk kolom A-D (3 level)
                $mergeColumns = ['A', 'B', 'C', 'D'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}3");
                }

                // Merge sel header "Risiko Residual" secara horizontal (E1 sampai AJ1) - 32 kolom
                $sheet->mergeCells('E1:AJ1');

                // Merge sel header kategori di level 2 (masing-masing 4 kolom untuk Q1-Q4)
                $sheet->mergeCells('E2:H2'); // Asumsi Perhitungan Dampak
                $sheet->mergeCells('I2:L2'); // Nilai Dampak
                $sheet->mergeCells('M2:P2'); // Skala Dampak BUMN
                $sheet->mergeCells('Q2:T2'); // Nilai Probabilitas
                $sheet->mergeCells('U2:X2'); // Skala Probabilitas BUMN
                $sheet->mergeCells('Y2:AB2'); // Eksposur Risiko
                $sheet->mergeCells('AC2:AF2'); // Skala Risiko BUMN
                $sheet->mergeCells('AG2:AJ2'); // Level Risiko BUMN

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
                $sheet->getStyle('A1:AJ3')->applyFromArray($headerStyle);

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
                    ->whereHas('riskAnalysis', function ($query) {
                        $query->where('kategori_dampak', 'Kuantitatif');
                    })
                    ->count();
                
                $maxRow = max(50, $risikosCount + 10);
                $sheet->getStyle('A4:AJ' . $maxRow)->applyFromArray($dataStyle);
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
            ->whereHas('riskAnalysis', function ($query) {
                $query->where('kategori_dampak', 'Kuantitatif');
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
                'peristiwa_risiko' => $risiko->peristiwaRisiko->title ?? '-',
                
                // Asumsi Perhitungan Dampak Q1-Q4
                'asumsi_perhitungan_dampak_q1' => $analisa->asumsi_perhitungan_dampak_residual_q1 ?? '-',
                'asumsi_perhitungan_dampak_q2' => $analisa->asumsi_perhitungan_dampak_residual_q2 ?? '-',
                'asumsi_perhitungan_dampak_q3' => $analisa->asumsi_perhitungan_dampak_residual_q3 ?? '-',
                'asumsi_perhitungan_dampak_q4' => $analisa->asumsi_perhitungan_dampak_residual_q4 ?? '-',
                
                // Nilai Dampak Q1-Q4
                'nilai_dampak_q1' => $this->formatCurrency($analisa->nilai_dampak_residual_q1 ?? 0),
                'nilai_dampak_q2' => $this->formatCurrency($analisa->nilai_dampak_residual_q2 ?? 0),
                'nilai_dampak_q3' => $this->formatCurrency($analisa->nilai_dampak_residual_q3 ?? 0),
                'nilai_dampak_q4' => $this->formatCurrency($analisa->nilai_dampak_residual_q4 ?? 0),
                
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