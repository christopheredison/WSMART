<?php

namespace App\Exports;

use App\Models\IdentifikasiRisiko;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanRiskRegisterBaruExport implements FromCollection, WithEvents, ShouldAutoSize
{
    protected $periodeId;
    protected $unitId;
    protected $bulan;
    protected $quarterTarget;

    public function __construct(int $periodeId, int $unitId, $bulan = null)
    {
        $this->periodeId = $periodeId;
        $this->unitId = $unitId;
        $this->bulan = $bulan;

        // Tentukan Quarter secara dinamis berdasarkan bulan monitoring yang dipilih
        $this->quarterTarget = $bulan ? (int) ceil($bulan / 3) : 4;
    }

    public function collection()
    {
        $qTarget = $this->quarterTarget;

        $risikos = IdentifikasiRisiko::with([
            'taksonomiRisiko',
            'penyebabRisiko',
            'dampakRisikos',
            'kris.kriUnitMonitorings' => function($q) {
                $q->whereHas('unitRiskMonitoring', function($sq) {
                    $sq->where('status', 100)->where('is_approved', 1);
                    if ($this->bulan) {
                        $sq->where('month', '<=', $this->bulan);
                    }
                })->orderBy('id', 'desc');
            },
            'riskAnalysis.skalaDampakObj',
            'riskAnalysis.skalaProbabilitas',
            "riskAnalysis.skalaDampakResidualQ{$qTarget}Obj",
            "riskAnalysis.skalaProbabilitasResidualQ{$qTarget}",
            'monitoringRisikos' => function($query) {
                $query->where('status', 100)->where('is_approved', 1);
                if ($this->bulan) {
                    $query->where('month', '<=', $this->bulan);
                }
                $query->orderBy('month', 'desc')->orderBy('id', 'desc');
            },
            'monitoringRisikos.pengendalians',
            'monitoringRisikos.skalaProbabilitas',
            'opportunities'
        ])
        ->where('periode_id', $this->periodeId)
        ->where('unit_id', $this->unitId)
        ->get();

        $exportData = new Collection();
        $no = 1;

        foreach ($risikos as $risiko) {
            // 1. Mapping Penyebab & Dampak
            $penyebabText = '';
            if ($risiko->penyebabRisiko) {
                foreach ($risiko->penyebabRisiko as $idx => $p) {
                    $penyebabText .= ($idx + 1) . '. ' . ($p->penyebab_risiko ?? '-') . "\n";
                }
            }

            $dampakText = '';
            if ($risiko->dampakRisikos) {
                foreach ($risiko->dampakRisikos as $idx => $d) {
                    $dampakText .= ($idx + 1) . '. ' . ($d->dampak_risiko ?? '-') . "\n";
                }
            }

            // 2. Mengambil Indikator Utama
            $kri = $risiko->kris->first();
            $kriMonitoring = $kri ? $kri->kriUnitMonitorings->first() : null;
            $monitoring = $risiko->monitoringRisikos->first();
            $pengendalian = ($monitoring && $monitoring->pengendalians) ? $monitoring->pengendalians->first() : null;
            $analisis = $risiko->riskAnalysis;

            // Efektivitas Pengendalian
            $statusKri = $kriMonitoring ? $kriMonitoring->status_kri_terkini : null;
            $efektivitas = '-';
            if ($statusKri == 1) { $efektivitas = 'Efektif'; } 
            elseif ($statusKri == 2 || $statusKri == 3) { $efektivitas = 'Tidak Efektif'; }

            // 3. Mapping Data Inherent
            $inherentNilaiProb = $analisis->nilai_probabilitas ?? 0;
            $inherentTktProb   = optional($analisis->skalaProbabilitas)->tingkat ?? '-';

            // 4. Mapping Data Residual (Quarter dinamis)
            $resNilai     = $analisis->{"nilai_dampak_residual_q{$qTarget}"} ?? 0;
            $resDampak    = $analisis->{"skala_dampak_residual_q{$qTarget}"} ?? '-';
            $resNilaiProb = $analisis->{"nilai_probabilitas_residual_q{$qTarget}"} ?? 0;
            $resTktProb   = optional($analisis->{"skalaProbabilitasResidualQ{$qTarget}"})->tingkat ?? '-';
            $resLevel     = $analisis->{"level_risiko_residual_q{$qTarget}"} ?? '-';
            $resEksposur  = $analisis->{"eksposur_risiko_residual_q{$qTarget}"} ?? 0;

            // 5. Mapping Data Realisasi Month-Current
            $realisasiNilaiProb = $monitoring->nilai_probabilitas ?? 0;
            $realisasiTktProb   = optional($monitoring->skalaProbabilitas)->tingkat ?? '-';

            // 6. Mapping Peluang & Nilai Peluang
            $peluangRaText = '';
            $peluangRiText = '';
            $nilaiPeluangRaText = '';
            $nilaiPeluangRiText = '';

            if ($risiko->opportunities) { 
                foreach ($risiko->opportunities as $idx => $opp) {
                    $counter = $idx + 1;
                    $peluangRaText .= $counter . '. ' . ($opp->penjelasan_peluang_rencana ?? '-') . "\n";
                    $peluangRiText .= $counter . '. ' . ($opp->penjelasan_peluang_realisasi ?? '-') . "\n";
                    
                    $nRa = $opp->nilai_peluang_rencana;
                    $nRi = $opp->nilai_peluang_realisasi;
                    
                    $nilaiPeluangRaText .= $counter . '. ' . (is_numeric($nRa) && $nRa != 0 ? 'Rp ' . number_format($nRa, 0, ',', '.') : ($nRa == 0 ? '-' : ($nRa ?? '-'))) . "\n";
                    $nilaiPeluangRiText .= $counter . '. ' . (is_numeric($nRi) && $nRi != 0 ? 'Rp ' . number_format($nRi, 0, ',', '.') : ($nRi == 0 ? '-' : ($nRi ?? '-'))) . "\n";
                }
            }

            $exportData->push([
                'no' => $no++,
                'taksonomi' => $risiko->taksonomiRisiko->nama ?? '-',
                'peristiwa' => $risiko->peristiwa_risiko ?? '-',
                'penyebab'  => trim($penyebabText) ?: '-',
                'dampak'    => trim($dampakText) ?: '-',
                'parameter_kri'   => $kri->kri ?? '-',
                'tren_parameter'  => $kri->tren_parameter ?? '-',
                'metode_ukur'     => $kri->metode_pengukuran ?? '-',
                'unit'            => $kri->satuan_kri ?? '-',
                
                // Ambang Batas
                'risk_limit'     => $kri->batas_aman ?? '-',
                'risk_appetite'  => $kri->batas_waspada ?? '-',
                'risk_tolerance' => $kri->batas_bahaya ?? '-',
                
                // Aktual & Efektivitas
                'aktual_bulan' => $kriMonitoring->nilai_kri_terkini ?? '-',
                'efektivitas'  => $efektivitas,

                // Rencana & Realisasi Pengendalian
                'rencana_pengendalian' => $pengendalian->rencana_pengendalian ?? '-',
                'biaya_rencana'        => $this->formatUang($pengendalian->biaya_rencana_pengendalian ?? 0),
                'realisasi_pengendalian'=> $pengendalian->realisasi_pengendalian ?? '-',
                'biaya_realisasi'      => $this->formatUang($pengendalian->biaya_realisasi_pengendalian ?? 0),

                // INHERENT (Menggunakan formatNilaiDampak)
                'inherent_nilai'      => $this->formatNilaiDampak($analisis->nilai_dampak ?? 0),
                'inherent_dampak'     => $analisis->skala_dampak ?? '-',
                'inherent_nilai_prob' => $inherentNilaiProb . '%',
                'inherent_tkt_prob'   => $inherentTktProb,
                'inherent_level'      => $analisis->level_risiko ?? '-',
                'inherent_eksposur'   => $this->formatUang($analisis->eksposur_risiko ?? 0),

                // RESIDUAL QUARTER (Menggunakan formatNilaiDampak)
                'residual_nilai'      => $this->formatNilaiDampak($resNilai),
                'residual_dampak'     => $resDampak,
                'residual_nilai_prob' => $resNilaiProb . '%',
                'residual_tkt_prob'   => $resTktProb,
                'residual_level'      => $resLevel,
                'residual_eksposur'   => $this->formatUang($resEksposur),

                // REALISASI MONTH-CURRENT (Menggunakan formatNilaiDampak)
                'realisasi_nilai'      => $this->formatNilaiDampak($monitoring->nilai_dampak ?? 0),
                'realisasi_dampak'     => $monitoring->skala_dampak ?? '-',
                'realisasi_nilai_prob' => $realisasiNilaiProb . '%',
                'realisasi_tkt_prob'   => $realisasiTktProb,
                'realisasi_level'      => $monitoring->level_risiko ?? '-',
                'realisasi_eksposur'   => $this->formatUang($monitoring->eksposure_risiko ?? $monitoring->eksposur_risiko ?? 0),

                // PELUANG
                'peluang_ra' => trim($peluangRaText) ?: '-',
                'peluang_ri' => trim($peluangRiText) ?: '-',

                // NILAI PELUANG
                'nilai_peluang_ra' => trim($nilaiPeluangRaText) ?: '-',
                'nilai_peluang_ri' => trim($nilaiPeluangRiText) ?: '-',
            ]);
        }

        return $exportData;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->insertNewRowBefore(1, 2);
                
                // Header Parent
                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Taksonomi/ Kategori Risiko');
                $sheet->setCellValue('C1', 'Peristiwa Risiko');
                $sheet->setCellValue('D1', 'Penyebab');
                $sheet->setCellValue('E1', 'Dampak');
                $sheet->setCellValue('F1', 'Parameter/ KRI');
                $sheet->setCellValue('G1', 'Tren Parameter');
                $sheet->setCellValue('H1', 'Metode Pengukuran');
                $sheet->setCellValue('I1', 'Unit');
                $sheet->setCellValue('J1', 'Ambang Batas');
                $sheet->setCellValue('M1', 'Aktual');
                $sheet->setCellValue('O1', 'Pengendalian Parameter/KRI');
                $sheet->setCellValue('Q1', 'Realisasi Pengendalian');
                $sheet->setCellValue('S1', 'Inherent');
                $sheet->setCellValue('Y1', "Residual Quarter (Q{$this->quarterTarget})");
                $sheet->setCellValue('AE1', 'Realisasi Month-Current');
                $sheet->setCellValue('AK1', 'Peluang');
                $sheet->setCellValue('AM1', 'Nilai Peluang');

                // Header Child
                $sheet->setCellValue('J2', 'Risk Limit');
                $sheet->setCellValue('K2', 'Risk Appetite');
                $sheet->setCellValue('L2', 'Risk Tolerance');
                $sheet->setCellValue('M2', 'Month-Current');
                $sheet->setCellValue('N2', 'Efektivitas Pengendalian Risiko');
                $sheet->setCellValue('O2', 'Rencana Pengendalian');
                $sheet->setCellValue('P2', 'Biaya Pengendalian');
                $sheet->setCellValue('Q2', 'Realisasi Pengendalian');
                $sheet->setCellValue('R2', 'Biaya Pengendalian');
                
                $starts = ['S', 'Y', 'AE'];
                foreach ($starts as $col) {
                    $cIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($col);
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex) . '2', 'Nilai Dampak (Rp)');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+1) . '2', 'Tingkat Dampak');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+2) . '2', 'Nilai Probabilitas');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+3) . '2', 'Tingkat Probabilitas');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+4) . '2', 'Level Risiko');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+5) . '2', 'Eksposure Risiko');
                }

                $sheet->setCellValue('AK2', 'Ra');
                $sheet->setCellValue('AL2', 'Ri');
                $sheet->setCellValue('AM2', 'Ra');
                $sheet->setCellValue('AN2', 'Ri');

                // Merge Cells
                $singles = ['A','B','C','D','E','F','G','H','I'];
                foreach($singles as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                $sheet->mergeCells('J1:L1');
                $sheet->mergeCells('M1:N1');
                $sheet->mergeCells('O1:P1');
                $sheet->mergeCells('Q1:R1');
                $sheet->mergeCells('S1:X1');
                $sheet->mergeCells('Y1:AD1');
                $sheet->mergeCells('AE1:AJ1');
                $sheet->mergeCells('AK1:AL1');
                $sheet->mergeCells('AM1:AN1');

                // Styling Area Header
                $sheet->getStyle('A1:AN2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
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
                ]);

                // Styling Data Body
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 3) {
                    $sheet->getStyle('A3:AN' . $lastRow)->applyFromArray([
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ]
                        ]
                    ]);
                    
                    $sheet->getStyle('A3:A'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('J3:N'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    $centerCols = ['U', 'V', 'AA', 'AB', 'AG', 'AH', 'AK', 'AL', 'AM', 'AN'];
                    foreach($centerCols as $cCol) {
                        $sheet->getStyle($cCol.'3:'.$cCol.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                foreach (range('A', 'Z') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
                foreach (range('A', 'N') as $col) { $sheet->getColumnDimension('A'.$col)->setAutoSize(true); }
                
                $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension('E')->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension('AK')->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension('AL')->setAutoSize(false)->setWidth(35);
            },
        ];
    }

    private function formatUang($value)
    {
        if (is_string($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        return (float) ($value ?: 0);
    }

    /**
     * Helper baru untuk merubah nilai dampak 0 atau null menjadi '-'
     */
    private function formatNilaiDampak($value)
    {
        if ($value === null || $value === '' || $value == 0) {
            return '-';
        }
        if (is_string($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        return (float)$value == 0 ? '-' : (float)$value;
    }
}