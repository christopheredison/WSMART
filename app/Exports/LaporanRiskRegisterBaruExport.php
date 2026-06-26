<?php

namespace App\Exports;

use App\Models\IdentifikasiRisiko;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanRiskRegisterBaruExport implements FromCollection, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    protected $periodeId;
    protected $unitId;
    protected $bulan;
    protected $quarterTarget;
    
    protected $mergeRanges = [];
    protected $mergeTaksonomiRanges = [];

    public function __construct(int $periodeId, int $unitId, $bulan = null)
    {
        $this->periodeId = $periodeId;
        $this->unitId = $unitId;
        $this->bulan = $bulan;

        $this->quarterTarget = $bulan ? (int) ceil($bulan / 3) : 4;
    }

    public function columnFormats(): array
    {
        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return [
            'Q' => $currencyFormat, 
            'S' => $currencyFormat, 
            'T' => $currencyFormat, 
            'Y' => $currencyFormat, 
            'Z' => $currencyFormat, 
            'AE' => $currencyFormat, 
            'AF' => $currencyFormat, 
            'AK' => $currencyFormat, 
        ];
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
        ->orderBy('taksonomi_risiko_id', 'asc') // MAPPING ORDERING DI SINI
        ->get();

        $exportData = new Collection();
        $no = 1;
        $currentRow = 3; // Data dimulai dari baris ke-3 Excel

        // Variabel bantuan untuk tracking merge Taksonomi
        $lastTaksonomiId = null;
        $taksonomiStartRow = 3;

        foreach ($risikos as $index => $risiko) {
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

            $monitoring = $risiko->monitoringRisikos->first();
            $analisis = $risiko->riskAnalysis;

            $inherentNilaiProb = $analisis->nilai_probabilitas ?? 0;
            $inherentTktProb   = optional($analisis->skalaProbabilitas)->tingkat ?? '-';

            $resNilai     = $analisis->{"nilai_dampak_residual_q{$qTarget}"} ?? 0;
            $resDampak    = $analisis->{"skala_dampak_residual_q{$qTarget}"} ?? '-';
            $resNilaiProb = $analisis->{"nilai_probabilitas_residual_q{$qTarget}"} ?? 0;
            $resTktProb   = optional($analisis->{"skalaProbabilitasResidualQ{$qTarget}"})->tingkat ?? '-';
            $resLevel     = $analisis->{"level_risiko_residual_q{$qTarget}"} ?? '-';
            $resEksposur  = $analisis->{"eksposur_risiko_residual_q{$qTarget}"} ?? 0;

            $realisasiNilaiProb = $monitoring->nilai_probabilitas ?? 0;
            $realisasiTktProb   = optional($monitoring->skalaProbabilitas)->tingkat ?? '-';

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

            $kriList = $risiko->kris ?? collect();
            $kriCount = max(1, $kriList->count());

            // Hitung baris awal risiko saat ini sebelum loop KRI
            $risikoStartRow = $currentRow;

            // Simpan range merge internal untuk KRI / Peristiwa Risiko dkk
            if ($kriCount > 1) {
                $this->mergeRanges[] = [
                    'start' => $currentRow,
                    'end'   => $currentRow + $kriCount - 1
                ];
            }

            // Logika Evaluasi Merging Kolom Taksonomi (Kolom B)
            $currentTaksonomiId = $risiko->taksonomi_risiko_id ?? 'empty';
            
            if ($index === 0) {
                $lastTaksonomiId = $currentTaksonomiId;
                $taksonomiStartRow = $currentRow;
            } elseif ($currentTaksonomiId !== $lastTaksonomiId) {
                // Jika taksonomi berganti, kunci koordinat baris taksonomi sebelumnya
                $taksonomiEndRow = $currentRow - 1;
                if ($taksonomiEndRow >= $taksonomiStartRow) {
                    $this->mergeTaksonomiRanges[] = [
                        'start' => $taksonomiStartRow,
                        'end'   => $taksonomiEndRow
                    ];
                }
                // Reset tracker untuk taksonomi baru
                $lastTaksonomiId = $currentTaksonomiId;
                $taksonomiStartRow = $currentRow;
            }

            if ($kriList->isEmpty()) {
                $exportData->push([
                    'no' => $no,
                    'taksonomi' => $risiko->taksonomiRisiko->nama ?? '-',
                    'peristiwa' => $risiko->peristiwa_risiko ?? '-',
                    'penyebab'  => trim($penyebabText) ?: '-',
                    'dampak'    => trim($dampakText) ?: '-',
                    'parameter_kri'   => '-',
                    'tren_parameter'  => '-',
                    'metode_ukur'     => '-',
                    'unit'            => '-',
                    'risk_limit'     => '-',
                    'risk_appetite'  => '-',
                    'risk_tolerance' => '-',
                    'aktual_bulan' => '-',
                    'status_kri'   => '-', 
                    'efektivitas'  => '-',
                    'rencana_pengendalian' => '-',
                    'biaya_rencana'        => 0,
                    'realisasi_pengendalian'=> '-',
                    'biaya_realisasi'      => 0,
                    'inherent_nilai'      => $this->formatUang($analisis->nilai_dampak ?? 0),
                    'inherent_dampak'     => $analisis->skala_dampak ?? '-',
                    'inherent_nilai_prob' => $inherentNilaiProb . '%',
                    'inherent_tkt_prob'   => $inherentTktProb,
                    'inherent_level'      => $analisis->level_risiko ?? '-',
                    'inherent_eksposur'   => $this->formatUang($analisis->eksposur_risiko ?? 0),
                    'residual_nilai'      => $this->formatUang($resNilai),
                    'residual_dampak'     => $resDampak,
                    'residual_nilai_prob' => $resNilaiProb . '%',
                    'residual_tkt_prob'   => $resTktProb,
                    'residual_level'      => $resLevel,
                    'residual_eksposur'   => $this->formatUang($resEksposur),
                    'realisasi_nilai'      => $this->formatUang($monitoring->nilai_dampak ?? 0),
                    'realisasi_dampak'     => $monitoring->skala_dampak ?? '-',
                    'realisasi_nilai_prob' => $realisasiNilaiProb . '%',
                    'realisasi_tkt_prob'   => $realisasiTktProb,
                    'realisasi_level'      => $monitoring->level_risiko ?? '-',
                    'realisasi_eksposur'   => $this->formatUang($monitoring->eksposure_risiko ?? $monitoring->eksposur_risiko ?? 0),
                    'peluang_ra' => trim($peluangRaText) ?: '-',
                    'peluang_ri' => trim($peluangRiText) ?: '-',
                    'nilai_peluang_ra' => trim($nilaiPeluangRaText) ?: '-',
                    'nilai_peluang_ri' => trim($nilaiPeluangRiText) ?: '-',
                ]);
                $currentRow++;
            } else {
                $isFirstRowOfGroup = true;
                foreach ($kriList as $kri) {
                    $kriMonitoring = $kri->kriUnitMonitorings->first();

                    $statusKriText = '-';
                    $statusKri = $kriMonitoring ? $kriMonitoring->status_kri_terkini : null;
                    if ($statusKri == 1) { $statusKriText = 'Aman'; }
                    elseif ($statusKri == 2) { $statusKriText = 'Siaga'; }
                    elseif ($statusKri == 3) { $statusKriText = 'Bahaya'; }

                    $efektivitas = '-';
                    if ($statusKri == 1) { $efektivitas = 'Efektif'; } 
                    elseif ($statusKri == 2 || $statusKri == 3) { $efektivitas = 'Tidak Efektif'; }

                    $pengendalian = null;
                    if ($monitoring && $monitoring->pengendalians) {
                        $pengendalian = $monitoring->pengendalians->where('kri_id', $kri->id)->first();
                    }

                    $exportData->push([
                        'no'        => $isFirstRowOfGroup ? $no : '',
                        'taksonomi' => $risiko->taksonomiRisiko->nama ?? '-', // PERBAIKAN: Selalu isi teks taksonomi agar kalau di-merge text-nya tidak hilang
                        'peristiwa' => $isFirstRowOfGroup ? ($risiko->peristiwa_risiko ?? '-') : '',
                        'penyebab'  => $isFirstRowOfGroup ? (trim($penyebabText) ?: '-') : '',
                        'dampak'    => $isFirstRowOfGroup ? (trim($dampakText) ?: '-') : '',
                        
                        'parameter_kri'   => $kri->kri ?? '-',
                        'tren_parameter'  => $kri->tren_parameter ?? '-',
                        'metode_ukur'     => $kri->metode_pengukuran ?? '-',
                        'unit'            => $kri->satuan_kri ?? '-',
                        'risk_limit'      => $this->formatKriBatas($kri->batas_aman),
                        'risk_appetite'   => $this->formatKriBatas($kri->batas_waspada),
                        'risk_tolerance'  => $this->formatKriBatas($kri->batas_bahaya),
                        'aktual_bulan'    => $this->formatKriBatas($kriMonitoring->nilai_kri_terkini ?? null),
                        
                        'status_kri'      => $statusKriText,
                        'efektivitas'     => $efektivitas,

                        'rencana_pengendalian' => $pengendalian->rencana_pengendalian ?? '-',
                        'biaya_rencana'        => $this->formatUang($pengendalian->biaya_rencana_pengendalian ?? 0),
                        'realisasi_pengendalian'=> $pengendalian->realisasi_pengendalian ?? '-',
                        'biaya_realisasi'      => $this->formatUang($pengendalian->biaya_realisasi_pengendalian ?? 0),

                        'inherent_nilai'      => $isFirstRowOfGroup ? $this->formatUang($analisis->nilai_dampak ?? 0) : null,
                        'inherent_dampak'     => $isFirstRowOfGroup ? ($analisis->skala_dampak ?? '-') : '',
                        'inherent_nilai_prob' => $isFirstRowOfGroup ? ($inherentNilaiProb . '%') : '',
                        'inherent_tkt_prob'   => $isFirstRowOfGroup ? $inherentTktProb : '',
                        'inherent_level'      => $isFirstRowOfGroup ? ($analisis->level_risiko ?? '-') : '',
                        'inherent_eksposur'   => $isFirstRowOfGroup ? $this->formatUang($analisis->eksposur_risiko ?? 0) : null,

                        'residual_nilai'      => $isFirstRowOfGroup ? $this->formatUang($resNilai) : null,
                        'residual_dampak'     => $isFirstRowOfGroup ? $resDampak : '',
                        'residual_nilai_prob' => $isFirstRowOfGroup ? ($resNilaiProb . '%') : '',
                        'residual_tkt_prob'   => $isFirstRowOfGroup ? $resTktProb : '',
                        'residual_level'      => $isFirstRowOfGroup ? $resLevel : '',
                        'residual_eksposur'   => $isFirstRowOfGroup ? $this->formatUang($resEksposur) : null,

                        'realisasi_nilai'      => $isFirstRowOfGroup ? $this->formatUang($monitoring->nilai_dampak ?? 0) : null,
                        'realisasi_dampak'     => $isFirstRowOfGroup ? ($monitoring->skala_dampak ?? '-') : '',
                        'realisasi_nilai_prob' => $isFirstRowOfGroup ? ($realisasiNilaiProb . '%') : '',
                        'realisasi_tkt_prob'   => $isFirstRowOfGroup ? $realisasiTktProb : '',
                        'realisasi_level'      => $isFirstRowOfGroup ? ($monitoring->level_risiko ?? '-') : '',
                        'realisasi_eksposur'   => $isFirstRowOfGroup ? $this->formatUang($monitoring->eksposure_risiko ?? $monitoring->eksposur_risiko ?? 0) : null,

                        'peluang_ra'       => $isFirstRowOfGroup ? (trim($peluangRaText) ?: '-') : '',
                        'peluang_ri'       => $isFirstRowOfGroup ? (trim($peluangRiText) ?: '-') : '',
                        'nilai_peluang_ra' => $isFirstRowOfGroup ? (trim($nilaiPeluangRaText) ?: '-') : '',
                        'nilai_peluang_ri' => $isFirstRowOfGroup ? (trim($nilaiPeluangRiText) ?: '-') : '',
                    ]);

                    $isFirstRowOfGroup = false;
                    $currentRow++;
                }
            }
            $no++;
        }

        // Kunci taksonomi terakhir setelah looping selesai
        if ($currentRow - 1 >= $taksonomiStartRow) {
            $this->mergeTaksonomiRanges[] = [
                'start' => $taksonomiStartRow,
                'end'   => $currentRow - 1
            ];
        }

        return $exportData;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->insertNewRowBefore(1, 2);
                
                $sheet->getRowDimension(1)->setRowHeight(75);
                $sheet->getRowDimension(2)->setRowHeight(25);
                
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
                
                $sheet->setCellValue('N1', "Status\n🟢 Aman (Aktual < Risk Limit)\n🟡 Siaga (Risk Limit < Aktual < Risk Tolerance)\n🔴 Bahaya > Risk Tolerance");
                $sheet->setCellValue('O1', 'Efektivitas Pengendalian Risiko');
                
                $sheet->setCellValue('P1', 'Pengendalian Parameter/KRI');
                $sheet->setCellValue('R1', 'Realisasi Pengendalian');
                
                $sheet->setCellValue('T1', 'Inherent');
                $sheet->setCellValue('Z1', "Residual Quarter (Q{$this->quarterTarget})");
                $sheet->setCellValue('AF1', 'Realisasi Month-Current');
                $sheet->setCellValue('AL1', 'Peluang');
                $sheet->setCellValue('AN1', 'Nilai Peluang');

                $sheet->setCellValue('J2', 'Risk Limit');
                $sheet->setCellValue('K2', 'Risk Appetite');
                $sheet->setCellValue('L2', 'Risk Tolerance');
                
                $sheet->setCellValue('M2', 'Month-Current'); 
                
                $sheet->setCellValue('P2', 'Rencana Pengendalian');
                $sheet->setCellValue('Q2', 'Biaya Pengendalian');
                $sheet->setCellValue('R2', 'Realisasi Pengendalian');
                $sheet->setCellValue('S2', 'Biaya Pengendalian');
                
                $starts = ['T', 'Z', 'AF'];
                foreach ($starts as $col) {
                    $cIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($col);
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex) . '2', 'Nilai Dampak (Rp)');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+1) . '2', 'Tingkat Dampak');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+2) . '2', 'Nilai Probabilitas');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+3) . '2', 'Tingkat Probabilitas');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+4) . '2', 'Level Risiko');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+5) . '2', 'Eksposure Risiko');
                }

                $sheet->setCellValue('AL2', 'Ra');
                $sheet->setCellValue('AM2', 'Ri');
                $sheet->setCellValue('AN2', 'Ra');
                $sheet->setCellValue('AO2', 'Ri');

                // Merge Cells Vertikal Header
                $singles = ['A','B','C','D','E','F','G','H','I','N','O'];
                foreach($singles as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge Cells Horizontal Header
                $sheet->mergeCells('J1:L1');   
                $sheet->mergeCells('P1:Q1');   
                $sheet->mergeCells('R1:S1');   
                $sheet->mergeCells('T1:Y1');   
                $sheet->mergeCells('Z1:AE1');  
                $sheet->mergeCells('AF1:AK1'); 
                $sheet->mergeCells('AL1:AM1'); 
                $sheet->mergeCells('AN1:AO1'); 

                // SET FONT GLOBAL ARIAL
                $sheet->getStyle('A1:AO' . $sheet->getHighestRow())->getFont()->setName('Arial');

                // STYLING BLOCK 1: MERAH (#C00000)
                $sheet->getStyle('A1:S2')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C00000'] 
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF'] 
                        ]
                    ]
                ]);

                // STYLING BLOCK 2: BIRU TUA
                $sheet->getStyle('T1:AO2')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '002060'] 
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF']
                        ]
                    ]
                ]);

                // 1. MERGE CELLS BODY DINAMIS (KRI & Info Risiko Utama)
                if (!empty($this->mergeRanges)) {
                    // PERBAIKAN: Keluarkan 'B' (Taksonomi) dari list ini karena punya aturan merge-nya tersendiri
                    $columnsToMerge = [
                        'A', 'C', 'D', 'E',                                     
                        'T', 'U', 'V', 'W', 'X', 'Y',                 
                        'Z', 'AA', 'AB', 'AC', 'AD', 'AE',           
                        'AF', 'AG', 'AH', 'AI', 'AJ', 'AK',          
                        'AL', 'AM', 'AN', 'AO'                                      
                    ];
                    
                    foreach ($this->mergeRanges as $range) {
                        foreach ($columnsToMerge as $col) {
                            $sheet->mergeCells("{$col}{$range['start']}:{$col}{$range['end']}");
                        }
                    }
                }

                // 2. PERBAIKAN: MERGE CELLS KHUSUS KOLOM TAKSONOMI (KOLOM B)
                if (!empty($this->mergeTaksonomiRanges)) {
                    foreach ($this->mergeTaksonomiRanges as $tRange) {
                        if ($tRange['start'] !== $tRange['end']) {
                            $sheet->mergeCells("B{$tRange['start']}:B{$tRange['end']}");
                        }
                    }
                }

                // Styling Data Body
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 3) {
                    $sheet->getStyle('A3:AO' . $lastRow)->applyFromArray([
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP, 
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'A6A6A6'] 
                            ]
                        ]
                    ]);
                    
                    $sheet->getStyle('A3:A'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('B3:B'.$lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER); // Set taksonomi di tengah vertikal agar rapi
                    $sheet->getStyle('J3:O'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    $centerCols = ['V', 'W', 'X', 'AB', 'AC', 'AD', 'AH', 'AI', 'AJ', 'AL', 'AM', 'AN', 'AO'];
                    foreach($centerCols as $cCol) {
                        $sheet->getStyle($cCol.'3:'.$cCol.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    $rightCols = ['Q', 'S', 'T', 'Y', 'Z', 'AE', 'AF', 'AK'];
                    foreach($rightCols as $rCol) {
                        $sheet->getStyle($rCol.'3:'.$rCol.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                }

                foreach (range('A', 'Z') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
                for ($i = 27; $i <= 41; $i++) {
                    $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($colStr)->setAutoSize(true);
                }
                
                $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension('E')->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension('AL')->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension('AM')->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension('N')->setAutoSize(false)->setWidth(42);
            },
        ];
    }

    private function formatUang($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_string($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        return (float) ($value ?: 0);
    }

    private function formatKriBatas($value)
    {
        if ($value === null || $value === '') return '-';

        $trimmed = trim((string) $value);

        if (preg_match('/^-?\d+([.,]\d+)?$/', $trimmed)) {
            $normalized = str_replace(',', '.', $trimmed);
            
            $decimals = 0;
            if (strpos($normalized, '.') !== false) {
                $decimals = strlen(substr($normalized, strpos($normalized, '.') + 1));
            }
            
            return number_format((float)$normalized, $decimals, ',', '.');
        }

        return $value;
    }
}