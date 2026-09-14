<?php

namespace App\Exports;

use App\Exports\Support\ExportColumnHelper;
use App\Exports\Sheets\Unit\Concerns\SupportsUnitColumnExport;
use App\Models\IdentifikasiRisiko;
use App\Models\Periode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class LaporanRiskRegisterBaruExport implements FromCollection, WithEvents, ShouldAutoSize, WithColumnFormatting
{
    use SupportsUnitColumnExport;

    protected int $periodeId;
    protected array $unitIds;
    protected $bulan;
    protected $tahun;
    protected $quarterTarget;
    
    protected $mergeRanges = [];
    protected $mergeTaksonomiRanges = [];
    protected $statusKriByRow = [];

    public function __construct(
        int $periodeId,
        array $unitIds,
        $bulan = null,
        bool $includeUnitColumn = false,
        string $unitColumnLabel = 'Nama Divisi'
    ) {
        $this->periodeId = $periodeId;
        $this->unitIds = $unitIds;
        $this->bulan = $bulan;
        $this->includeUnitColumn = $includeUnitColumn;
        $this->unitColumnLabel = $unitColumnLabel;

        $this->quarterTarget = $bulan ? (int) ceil($bulan / 3) : 4;

        // Tahun periode dipakai bersama $bulan untuk menilai status Open/Closed
        // pada bulan laporan, sama seperti badge di halaman monitoring.
        $this->tahun = optional(Periode::find($periodeId))->tahun;
    }

    public function columnFormats(): array
    {
        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return $this->shiftColumnFormats([
            'Q' => $currencyFormat, 
            'S' => $currencyFormat, 
            'T' => $currencyFormat, 
            'Y' => $currencyFormat, 
            'Z' => $currencyFormat, 
            'AE' => $currencyFormat, 
            'AF' => $currencyFormat, 
            'AK' => $currencyFormat, 
        ]);
    }

    public function collection()
    {
        $qTarget = $this->quarterTarget;

        $risikos = IdentifikasiRisiko::with([
            'unit',
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
        ->whereIn('unit_id', $this->unitIds)
        ->orderBy('unit_id')
        ->orderBy('taksonomi_risiko_id', 'asc')
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

            $inherentNilaiProb = $analisis?->nilai_probabilitas ?? 0;
            $inherentTktProb   = $analisis?->skalaProbabilitas?->tingkat ?? '-';

            $resNilai     = $analisis?->{"nilai_dampak_residual_q{$qTarget}"} ?? 0;
            $resDampak    = $analisis?->{"skala_dampak_residual_q{$qTarget}"} ?? '-';
            $resNilaiProb = $analisis?->{"nilai_probabilitas_residual_q{$qTarget}"} ?? 0;
            $resTktProb   = $analisis?->{"skalaProbabilitasResidualQ{$qTarget}"}?->tingkat ?? '-';
            $resLevel     = $analisis?->{"level_risiko_residual_q{$qTarget}"} ?? '-';
            $resEksposur  = $analisis?->{"eksposur_risiko_residual_q{$qTarget}"} ?? 0;

            $realisasiNilaiProb = $monitoring?->nilai_probabilitas ?? 0;
            $realisasiTktProb   = $monitoring?->skalaProbabilitas?->tingkat ?? '-';

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
                $exportData->push($this->prependUnit([
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
                    'inherent_nilai'      => $this->formatUang($analisis?->nilai_dampak ?? 0),
                    'inherent_dampak'     => $analisis?->skala_dampak ?? '-',
                    'inherent_nilai_prob' => $inherentNilaiProb . '%',
                    'inherent_tkt_prob'   => $inherentTktProb,
                    'inherent_level'      => $analisis?->level_risiko ?? '-',
                    'inherent_eksposur'   => $this->formatUang($analisis?->eksposur_risiko ?? 0),
                    'residual_nilai'      => $this->formatUang($resNilai),
                    'residual_dampak'     => $resDampak,
                    'residual_nilai_prob' => $resNilaiProb . '%',
                    'residual_tkt_prob'   => $resTktProb,
                    'residual_level'      => $resLevel,
                    'residual_eksposur'   => $this->formatUang($resEksposur),
                    'realisasi_nilai'      => $this->formatUang($monitoring?->nilai_dampak ?? 0),
                    'realisasi_dampak'     => $monitoring?->skala_dampak ?? '-',
                    'realisasi_nilai_prob' => $realisasiNilaiProb . '%',
                    'realisasi_tkt_prob'   => $realisasiTktProb,
                    'realisasi_level'      => $monitoring?->level_risiko ?? '-',
                    'realisasi_eksposur'   => $this->formatUang($monitoring?->eksposure_risiko ?? $monitoring?->eksposur_risiko ?? 0),
                    'peluang_ra' => trim($peluangRaText) ?: '-',
                    'peluang_ri' => trim($peluangRiText) ?: '-',
                    'nilai_peluang_ra' => trim($nilaiPeluangRaText) ?: '-',
                    'nilai_peluang_ri' => trim($nilaiPeluangRiText) ?: '-',
                    'status_risiko' => $risiko->formatStatusRisikoForExport(
                        $this->tahun ? (int) $this->tahun : null,
                        $this->bulan ? (int) $this->bulan : null
                    ),
                ], $risiko));
                $currentRow++;
            } else {
                $isFirstRowOfGroup = true;
                foreach ($kriList as $kri) {
                    $kriMonitoring = $kri->kriUnitMonitorings->first();

                    $statusKri = $kriMonitoring ? $kriMonitoring->status_kri_terkini : null;
                    $statusKriText = $this->formatStatusKri($statusKri);
                    if (in_array((int) $statusKri, [1, 2, 3], true)) {
                        $this->statusKriByRow[$currentRow] = (int) $statusKri;
                    }

                    $efektivitas = '-';
                    if ($statusKri == 1) { $efektivitas = 'Efektif'; } 
                    elseif ($statusKri == 2 || $statusKri == 3) { $efektivitas = 'Tidak Efektif'; }

                    $pengendalian = null;
                    if ($monitoring && $monitoring->pengendalians) {
                        $pengendalian = $monitoring->pengendalians->where('kri_id', $kri->id)->first();
                    }

                    $exportData->push($this->prependUnit([
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

                        'inherent_nilai'      => $isFirstRowOfGroup ? $this->formatUang($analisis?->nilai_dampak ?? 0) : null,
                        'inherent_dampak'     => $isFirstRowOfGroup ? ($analisis?->skala_dampak ?? '-') : '',
                        'inherent_nilai_prob' => $isFirstRowOfGroup ? ($inherentNilaiProb . '%') : '',
                        'inherent_tkt_prob'   => $isFirstRowOfGroup ? $inherentTktProb : '',
                        'inherent_level'      => $isFirstRowOfGroup ? ($analisis?->level_risiko ?? '-') : '',
                        'inherent_eksposur'   => $isFirstRowOfGroup ? $this->formatUang($analisis?->eksposur_risiko ?? 0) : null,

                        'residual_nilai'      => $isFirstRowOfGroup ? $this->formatUang($resNilai) : null,
                        'residual_dampak'     => $isFirstRowOfGroup ? $resDampak : '',
                        'residual_nilai_prob' => $isFirstRowOfGroup ? ($resNilaiProb . '%') : '',
                        'residual_tkt_prob'   => $isFirstRowOfGroup ? $resTktProb : '',
                        'residual_level'      => $isFirstRowOfGroup ? $resLevel : '',
                        'residual_eksposur'   => $isFirstRowOfGroup ? $this->formatUang($resEksposur) : null,

                        'realisasi_nilai'      => $isFirstRowOfGroup ? $this->formatUang($monitoring?->nilai_dampak ?? 0) : null,
                        'realisasi_dampak'     => $isFirstRowOfGroup ? ($monitoring?->skala_dampak ?? '-') : '',
                        'realisasi_nilai_prob' => $isFirstRowOfGroup ? ($realisasiNilaiProb . '%') : '',
                        'realisasi_tkt_prob'   => $isFirstRowOfGroup ? $realisasiTktProb : '',
                        'realisasi_level'      => $isFirstRowOfGroup ? ($monitoring?->level_risiko ?? '-') : '',
                        'realisasi_eksposur'   => $isFirstRowOfGroup ? $this->formatUang($monitoring?->eksposure_risiko ?? $monitoring?->eksposur_risiko ?? 0) : null,

                        'peluang_ra'       => $isFirstRowOfGroup ? (trim($peluangRaText) ?: '-') : '',
                        'peluang_ri'       => $isFirstRowOfGroup ? (trim($peluangRiText) ?: '-') : '',
                        'nilai_peluang_ra' => $isFirstRowOfGroup ? (trim($nilaiPeluangRaText) ?: '-') : '',
                        'nilai_peluang_ri' => $isFirstRowOfGroup ? (trim($nilaiPeluangRiText) ?: '-') : '',
                        'status_risiko'     => $isFirstRowOfGroup ? $risiko->formatStatusRisikoForExport(
                            $this->tahun ? (int) $this->tahun : null,
                            $this->bulan ? (int) $this->bulan : null
                        ) : '',
                    ], $risiko, $isFirstRowOfGroup));

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
                $c = fn (string $col) => $this->c($col);
                $lastCol = $this->includeUnitColumn ? 'AQ' : 'AP';
                
                $sheet->insertNewRowBefore(1, 2);
                
                $sheet->getRowDimension(1)->setRowHeight(75);
                $sheet->getRowDimension(2)->setRowHeight(25);

                $this->setUnitColumnHeader($sheet);

                $sheet->setCellValue($c('A') . '1', 'No');
                $sheet->setCellValue($c('B') . '1', 'Taksonomi/ Kategori Risiko');
                $sheet->setCellValue($c('C') . '1', 'Peristiwa Risiko');
                $sheet->setCellValue($c('D') . '1', 'Penyebab');
                $sheet->setCellValue($c('E') . '1', 'Dampak');
                $sheet->setCellValue($c('F') . '1', 'Parameter/ KRI');
                $sheet->setCellValue($c('G') . '1', 'Tren Parameter');
                $sheet->setCellValue($c('H') . '1', 'Metode Pengukuran');
                $sheet->setCellValue($c('I') . '1', 'Unit');
                $sheet->setCellValue($c('J') . '1', 'Ambang Batas');
                $sheet->setCellValue($c('M') . '1', 'Aktual'); 
                
                $sheet->setCellValue($c('N') . '1', 'Status');
                $sheet->setCellValue($c('O') . '1', 'Efektivitas Pengendalian Risiko');
                
                $sheet->setCellValue($c('P') . '1', 'Pengendalian Parameter/KRI');
                $sheet->setCellValue($c('R') . '1', 'Realisasi Pengendalian');
                
                $sheet->setCellValue($c('T') . '1', 'Inherent');
                $sheet->setCellValue($c('Z') . '1', "Residual Quarter (Q{$this->quarterTarget})");
                $sheet->setCellValue($c('AF') . '1', 'Realisasi Month-Current');
                $sheet->setCellValue($c('AL') . '1', 'Peluang');
                $sheet->setCellValue($c('AN') . '1', 'Nilai Peluang');
                $sheet->setCellValue($c('AP') . '1', 'Status Risiko');

                $sheet->setCellValue($c('J') . '2', 'Risk Limit');
                $sheet->setCellValue($c('K') . '2', 'Risk Appetite');
                $sheet->setCellValue($c('L') . '2', 'Risk Tolerance');
                
                $sheet->setCellValue($c('M') . '2', 'Month-Current'); 
                
                $sheet->setCellValue($c('P') . '2', 'Rencana Pengendalian');
                $sheet->setCellValue($c('Q') . '2', 'Biaya Pengendalian');
                $sheet->setCellValue($c('R') . '2', 'Realisasi Pengendalian');
                $sheet->setCellValue($c('S') . '2', 'Biaya Pengendalian');
                
                $starts = ['T', 'Z', 'AF'];
                foreach ($starts as $col) {
                    $cIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($c($col));
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex) . '2', 'Nilai Dampak (Rp)');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+1) . '2', 'Tingkat Dampak');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+2) . '2', 'Nilai Probabilitas');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+3) . '2', 'Tingkat Probabilitas');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+4) . '2', 'Level Risiko');
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIndex+5) . '2', 'Eksposure Risiko');
                }

                $sheet->setCellValue($c('AL') . '2', 'Ra');
                $sheet->setCellValue($c('AM') . '2', 'Ri');
                $sheet->setCellValue($c('AN') . '2', 'Ra');
                $sheet->setCellValue($c('AO') . '2', 'Ri');

                $singles = ['A','B','C','D','E','F','G','H','I','N','O','AP'];
                foreach ($singles as $col) {
                    $sheet->mergeCells($c($col) . '1:' . $c($col) . '2');
                }

                $sheet->mergeCells($c('J') . '1:' . $c('L') . '1');   
                $sheet->mergeCells($c('P') . '1:' . $c('Q') . '1');   
                $sheet->mergeCells($c('R') . '1:' . $c('S') . '1');   
                $sheet->mergeCells($c('T') . '1:' . $c('Y') . '1');   
                $sheet->mergeCells($c('Z') . '1:' . $c('AE') . '1');  
                $sheet->mergeCells($c('AF') . '1:' . $c('AK') . '1'); 
                $sheet->mergeCells($c('AL') . '1:' . $c('AM') . '1'); 
                $sheet->mergeCells($c('AN') . '1:' . $c('AO') . '1'); 

                $sheet->getStyle('A1:' . $lastCol . $sheet->getHighestRow())->getFont()->setName('Arial');

                $sheet->getStyle('A1:' . $c('S') . '2')->applyFromArray([
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

                $sheet->getStyle($c('T') . '1:' . $lastCol . '2')->applyFromArray([
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

                if (!empty($this->mergeRanges)) {
                    $columnsToMerge = [];
                    if ($this->includeUnitColumn) {
                        $columnsToMerge[] = 'B';
                    }
                    foreach (['A', 'C', 'D', 'E', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP'] as $col) {
                        $columnsToMerge[] = $c($col);
                    }
                    
                    foreach ($this->mergeRanges as $range) {
                        foreach ($columnsToMerge as $targetCol) {
                            $sheet->mergeCells("{$targetCol}{$range['start']}:{$targetCol}{$range['end']}");
                        }
                    }
                }

                if (!empty($this->mergeTaksonomiRanges)) {
                    foreach ($this->mergeTaksonomiRanges as $tRange) {
                        if ($tRange['start'] !== $tRange['end']) {
                            $sheet->mergeCells($c('B') . "{$tRange['start']}:" . $c('B') . "{$tRange['end']}");
                        }
                    }
                }

                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 3) {
                    $sheet->getStyle('A3:' . $lastCol . $lastRow)->applyFromArray([
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
                    
                    $sheet->getStyle($c('A') . '3:' . $c('A') . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle($c('B') . '3:' . $c('B') . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle($c('J') . '3:' . $c('O') . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    
                    $centerCols = ['V', 'W', 'X', 'AB', 'AC', 'AD', 'AH', 'AI', 'AJ', 'AL', 'AM', 'AN', 'AO', 'AP'];
                    foreach ($centerCols as $cCol) {
                        $sheet->getStyle($c($cCol) . '3:' . $c($cCol) . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    $rightCols = ['Q', 'S', 'T', 'Y', 'Z', 'AE', 'AF', 'AK'];
                    foreach ($rightCols as $rCol) {
                        $sheet->getStyle($c($rCol) . '3:' . $c($rCol) . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    $this->appendCurrencyTotalRow(
                        $sheet,
                        3,
                        $lastRow,
                        'AP',
                        ['T', 'Y', 'Z', 'AE', 'AF', 'AK'],
                        'E'
                    );
                }

                foreach (range('A', 'Z') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }
                for ($i = 27; $i <= ($this->includeUnitColumn ? 43 : 42); $i++) {
                    $colStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($colStr)->setAutoSize(true);
                }
                
                $sheet->getColumnDimension($c('D'))->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension($c('E'))->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension($c('AL'))->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension($c('AM'))->setAutoSize(false)->setWidth(35);
                $sheet->getColumnDimension($c('N'))->setAutoSize(false)->setWidth(42);

                $sheet->getCell($c('N') . '1')->setValue($this->buildStatusLegendRichText());
                $this->applyStatusKriCellStyles($sheet);
            },
        ];
    }

    private function formatStatusKri($statusKri): string
    {
        return $this->getStatusLabel((int) $statusKri);
    }

    private function getStatusLabel(int $statusKri): string
    {
        return match ($statusKri) {
            1 => 'Aman',
            2 => 'Siaga',
            3 => 'Bahaya',
            default => '-',
        };
    }

    private function getStatusColor(int $statusKri): string
    {
        return match ($statusKri) {
            1 => '00B050',
            2 => 'FFC000',
            3 => 'FF0000',
            default => '000000',
        };
    }

    private function buildStatusLegendRichText(): RichText
    {
        $richText = new RichText();

        $title = $richText->createTextRun("Status\n");
        $title->getFont()->setBold(true)->setColor(new Color('FFFFFF'))->setName('Arial');

        $this->appendStatusLegendLine($richText, 1, " Aman (Aktual < Risk Limit)\n");
        $this->appendStatusLegendLine($richText, 2, " Siaga (Risk Limit < Aktual < Risk Tolerance)\n");
        $this->appendStatusLegendLine($richText, 3, ' Bahaya > Risk Tolerance');

        return $richText;
    }

    private function appendStatusLegendLine(RichText $richText, int $statusKri, string $suffix): void
    {
        $circle = $richText->createTextRun('●');
        $circle->getFont()
            ->setColor(new Color($this->getStatusColor($statusKri)))
            ->setName('Arial')
            ->setBold(true);

        $text = $richText->createTextRun($suffix);
        $text->getFont()
            ->setColor(new Color('FFFFFF'))
            ->setName('Arial')
            ->setBold(true);
    }

    private function applyStatusKriCellStyles($sheet): void
    {
        foreach ($this->statusKriByRow as $row => $statusKri) {
            $richText = new RichText();

            $circle = $richText->createTextRun('● ');
            $circle->getFont()
                ->setColor(new Color($this->getStatusColor($statusKri)))
                ->setName('Arial')
                ->setSize(14)
                ->setBold(true);

            $text = $richText->createTextRun($this->getStatusLabel($statusKri));
            $text->getFont()
                ->setColor(new Color('000000'))
                ->setName('Arial');

            $sheet->getCell($this->c('N') . "{$row}")->setValue($richText);
        }
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
