<?php

namespace App\Exports\Sheets\Project;

use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\ProjectHasilUsaha;
use App\Models\KRIProjectMonitoring;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class ResumeProjectSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithEvents
{
    private $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function title(): string
    {
        return 'Resume Project';
    }

    public function headings(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- 1. CONFIGURATION ---
                $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(5);

                // --- 2. RESUME PROYEK (TOP SECTION) ---
                $project = Project::with(['divisi'])->find($this->projectId);
                $meta = $project->meta ?? [];
                $riskLimit = ($project->nk ?? 0) * 0.03;

                // Logic Ambil Laba Setelah Pajak (LSP)
                $hasilUsaha = ProjectHasilUsaha::where('project_id', $this->projectId)
                                ->orderBy('period', 'desc')
                                ->first();
                $lspValue = $hasilUsaha ? $hasilUsaha->lsp_review : 0;

                // Hitung Agregat Biaya Total
                $risks = ProjectRisk::with([
                                'penyebabRisikoProjects.perlakuanPenyebabRisiko.perlakuanPenyebabMonitorings',
                                'dampakRisikoProjects.perlakuanDampakRisikos.perlakuanDampakMonitorings',
                                'perlakuanDampakRisikos.perlakuanDampakMonitorings'
                            ])
                            ->where('project_id', $this->projectId)->get();

                // Rencana
                $rencanaBiayaPenyebab = $risks->flatMap(fn($r) => $r->penyebabRisikoProjects)
                                            ->flatMap(fn($p) => $p->perlakuanPenyebabRisiko)
                                            ->sum('biaya_perlakuan_risiko');

                $rencanaBiayaDampak = $risks->flatMap(fn($r) => $r->perlakuanDampakRisikos)
                                            ->sum('biaya_perlakuan_risiko');

                // Realisasi
                $realisasiBiayaPenyebab = $risks->flatMap(fn($r) => $r->penyebabRisikoProjects)
                                              ->flatMap(fn($p) => $p->perlakuanPenyebabRisiko)
                                              ->sum(function($p) {
                                                  return $p->perlakuanPenyebabMonitorings->sortByDesc('id')->first()->realisasi_biaya_perlakuan_risiko ?? 0;
                                              });

                $realisasiBiayaDampak = $risks->flatMap(fn($r) => $r->perlakuanDampakRisikos)
                                              ->sum(function($pd) {
                                                  return $pd->perlakuanDampakMonitorings->sortByDesc('id')->first()->realisasi_biaya_perlakuan_risiko ?? 0;
                                              });

                $rencanaBiayaTotal = $rencanaBiayaPenyebab + $rencanaBiayaDampak;
                $realisasiBiayaTotal = $realisasiBiayaPenyebab + $realisasiBiayaDampak;

                // Insert Row
                $sheet->insertNewRowBefore(1, 15);

                $dataResume = [
                    'B1'  => ['Label' => 'Nama Proyek:', 'Value' => $project->project_name ?? '-'],
                    'B2'  => ['Label' => 'Divisi Operasi:', 'Value' => $project->divisi->name ?? '-'],
                    'B3'  => ['Label' => 'Nilai OK:', 'Value' => $this->formatCurrency($project->nk)],
                    'B4'  => ['Label' => 'Laba Setelah Pajak:', 'Value' => $this->formatCurrency($lspValue)],
                    'B5'  => ['Label' => 'Biaya Perlakuan Risiko Sesuai RKP:', 'Value' => ''],
                    'B6'  => ['Label' => 'Rencana Biaya Perlakuan Risiko:', 'Value' => $this->formatCurrency($rencanaBiayaTotal)],
                    'B7'  => ['Label' => 'Realisasi Biaya Perlakuan Risiko:', 'Value' => $this->formatCurrency($realisasiBiayaTotal)],
                    'B8'  => ['Label' => 'Tipe Kontrak:', 'Value' => $meta['jenis_kontrak_name'] ?? '-'],
                    'B9'  => ['Label' => 'Cara Pembayaran:', 'Value' => $meta['pembayaran_name'] ?? '-'],
                    'B10' => ['Label' => 'Batasan Biaya Perlakuan Risiko:', 'Value' => ''],
                    'B11' => ['Label' => 'Nilai Batasan Risiko:', 'Value' => $this->formatCurrency($riskLimit)],
                ];

                foreach ($dataResume as $cell => $data) {
                    $sheet->setCellValue($cell, $data['Label']);
                    $sheet->setCellValue('C' . substr($cell, 1), $data['Value']);
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                }

                // --- 3. HEADER TABEL (ROW 14-15) ---
                $sheet->setCellValue('A14', 'No');
                $sheet->setCellValue('B14', 'Sasaran');
                $sheet->setCellValue('C14', 'Peristiwa Risiko');
                $sheet->setCellValue('D14', 'Deskripsi Peristiwa Risiko');
                $sheet->setCellValue('E14', 'Deskripsi KRI');

                $sheet->setCellValue('F14', 'Status KRI (Threshold)');
                $sheet->mergeCells('F14:H14');

                // --- KOLOM BARU (REALISASI KRI) ---
                $sheet->setCellValue('I14', 'Nilai Realisasi KRI');
                $sheet->setCellValue('J14', 'Status Realisasi KRI');
                // ----------------------------------

                // GESER KOLOM SETELAHNYA (SEBELUMNYA I JADI K, dst)
                $sheet->setCellValue('K14', 'Penyebab Risiko');
                $sheet->setCellValue('L14', 'Penjelasan Dampak Risiko');

                $sheet->setCellValue('M14', 'Analisa Inheren');
                $sheet->mergeCells('M14:O14');

                $sheet->setCellValue('P14', 'Analisa Residual Rencana');
                $sheet->mergeCells('P14:Y14');

                $sheet->setCellValue('Z14', 'Analisa Residual Realisasi');
                $sheet->mergeCells('Z14:AF14');

                $sheet->setCellValue('AG14', 'Status');
                $sheet->setCellValue('AH14', 'Efektivitas Perlakuan Risiko');

                // Merge Vertical Header
                // Tambahkan I dan J ke array merge vertical
                foreach(['A','B','C','D','E','I','J','K','L','AG','AH'] as $col){
                    $sheet->mergeCells("{$col}14:{$col}15");
                }

                $sheet->setCellValue('F15', 'Aman');
                $sheet->setCellValue('G15', 'Waspada');
                $sheet->setCellValue('H15', 'Bahaya');

                // Analisa Inheren (Geser ke M-O)
                $sheet->setCellValue('M15', 'Dampak Risiko Kuantitatif Inheren');
                $sheet->setCellValue('N15', 'Eksposure Risiko Inheren');
                $sheet->setCellValue('O15', 'Level Risiko Inheren');

                // Analisa Residual Rencana (Geser ke P-Y)
                $sheet->setCellValue('P15', 'Perlakuan Risiko Penyebab');
                $sheet->setCellValue('Q15', 'Perlakuan Risiko Dampak');
                $sheet->setCellValue('R15', 'Biaya Perlakuan Risiko Penyebab');
                $sheet->setCellValue('S15', 'Biaya Perlakuan Risiko Dampak');
                $sheet->setCellValue('T15', 'Dampak Risiko Kuantitatif Rencana');
                $sheet->setCellValue('U15', 'Eksposure Residual Rencana');
                $sheet->setCellValue('V15', 'Level Residual Rencana');
                $sheet->setCellValue('W15', 'Waktu Mulai');
                $sheet->setCellValue('X15', 'Waktu Selesai');
                $sheet->setCellValue('Y15', 'Penanggung Jawab');

                // Analisa Residual Realisasi (Geser ke Z-AF)
                $sheet->setCellValue('Z15', 'Perlakuan Risiko Penyebab');
                $sheet->setCellValue('AA15', 'Perlakuan Risiko Dampak');
                $sheet->setCellValue('AB15', 'Realisasi Biaya Perlakuan Risiko Penyebab');
                $sheet->setCellValue('AC15', 'Realisasi Biaya Perlakuan Risiko Dampak');
                $sheet->setCellValue('AD15', 'Dampak Risiko Kuantitatif Rupiah');
                $sheet->setCellValue('AE15', 'Eksposure Residual Realisasi');
                $sheet->setCellValue('AF15', 'Level Residual Realisasi');

                // --- 4. STYLING HEADER COLORS ---
                $headerBaseStyle = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ];
                // Sesuaikan range kolom terakhir jadi AH
                $sheet->getStyle('A14:AH15')->applyFromArray($headerBaseStyle);

                $setColor = function($range, $colorHex) use ($sheet) {
                    $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($colorHex);
                };

                $cGrey   = 'D9D9D9';
                $cBlue   = '9BC2E6';
                $cGreen  = 'C6E0B4';
                $cOrange = 'F4B084';
                $cPink   = 'FF99CC';
                $cWhite  = 'FFFFFF';

                $setColor('A14:A15', $cGrey);
                $setColor('B14:B15', $cBlue);
                $setColor('C14:C15', $cGreen);
                $setColor('D14:D15', $cGreen);
                $setColor('E14:E15', $cGreen);

                $setColor('F15', '92D050');
                $setColor('G15', 'FFFF00');
                $setColor('H15', 'FF0000');
                
                // Warna Kolom Baru (I & J) - Samakan dengan D/E (Green) atau bedakan dikit
                $setColor('I14:J15', $cGreen); 

                // Shift Warna kolom lainnya
                $setColor('K14:L15', $cGreen); // Ex I-J
                $setColor('M15', $cOrange);    // Ex K
                $setColor('N15', $cPink);      // Ex L
                $setColor('O15', $cPink);      // Ex M

                $setColor('P14:Y14', $cWhite); // Ex N-W
                $setColor('P15:Q15', $cGreen); 
                $setColor('R15:S15', $cOrange);
                $setColor('T15', $cOrange);
                $setColor('U15:V15', $cPink);
                $setColor('W15:X15', $cGrey);
                $setColor('Y15', $cGreen);

                $setColor('Z14:AF14', $cWhite); // Ex X-AD
                $setColor('Z15:AA15', $cGreen);
                $setColor('AB15:AC15', $cOrange);
                $setColor('AD15', $cOrange);
                $setColor('AE15:AF15', $cPink);

                $setColor('AG14:AG15', $cBlue); // Ex AE
                $setColor('AH14:AH15', $cPink); // Ex AF

                // --- 5. STYLING DATA (START FROM ROW 16) ---
                $highestRow = $sheet->getHighestRow();

                if ($highestRow > 15) {
                    // Range Data sampai AH
                    $sheet->getStyle('A16:AH' . $highestRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);

                    $this->applyThresholdColoring($sheet, $highestRow);
                    
                    // Kolom Level Inheren geser ke O
                    $this->applyLevelColoring($sheet, 'O', 16, $highestRow); 
                    // Kolom Level Residual Rencana geser ke V
                    $this->applyLevelColoring($sheet, 'V', 16, $highestRow);
                    // Kolom Level Residual Realisasi geser ke AF
                    $this->applyLevelColoring($sheet, 'AF', 16, $highestRow);
                    // Kolom Status geser ke AG
                    $this->applyStatusColoring($sheet, 'AG', 16, $highestRow);
                }

                // Autosize loop (Updated ranges)
                foreach (range('B', 'Z') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
                // Handle AA to AH
                foreach (['AA','AB','AC','AD','AE','AF','AG','AH'] as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

                // Update kolom yang butuh width fix (Text panjang)
                // C, D, E, I, J (New), K, L (Old I,J), dst...
                // Mari kita set spesifik kolom teks panjang yang baru
                // K (Penyebab), L (Dampak), P (Perlakuan), Q, Z, AA, Y
                $longTextCols = ['C', 'D', 'E', 'K', 'L', 'P', 'Q', 'Z', 'AA', 'Y'];
                foreach ($longTextCols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth(35);
                }
            }
        ];
    }

    private function applyThresholdColoring($sheet, $maxRow) {
        for ($row = 16; $row <= $maxRow; $row++) {
            foreach (['F' => '92D050', 'G' => 'FFFF00', 'H' => 'FF0000'] as $col => $color) {
                $val = $sheet->getCell($col . $row)->getValue();
                if ($val !== null && (string)$val !== '' && trim((string)$val) !== '-') {
                    $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
                }
            }
        }
    }

    private function applyLevelColoring($sheet, $col, $startRow, $endRow)
    {
        for ($row = $startRow; $row <= $endRow; $row++) {
            $valFull = strtolower(trim($sheet->getCell($col . $row)->getValue()));
            $valParts = explode('-', $valFull);
            $val = trim($valParts[0]);

            $color = null;
            if ($val == 'low') $color = '92D050';
            elseif ($val == 'low to moderate') $color = 'C6E0B4';
            elseif ($val == 'moderate') $color = 'FFFF00';
            elseif ($val == 'moderate to high') $color = 'FFC000';
            elseif ($val == 'high') $color = 'FF0000';

            if ($color) {
                $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
            }
        }
    }

    private function applyStatusColoring($sheet, $col, $startRow, $endRow)
    {
        for ($row = $startRow; $row <= $endRow; $row++) {
            $val = strtolower(trim($sheet->getCell($col . $row)->getValue()));
            if ($val === 'open') {
                $sheet->getStyle($col . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => '006400']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6E0B4']]
                ]);
            } elseif ($val === 'closed') {
                $sheet->getStyle($col . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => '8B0000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4CCCC']]
                ]);
            }
        }
    }

    public function collection()
    {
        // PERBAIKAN: Load relasi Monitorings untuk KRI (Nested)
        $risks = ProjectRisk::with([
            'sasaranProyek',
            'peristiwaRisiko',
            'kriProjects.kriProjectMonitorings', // Load monitoring KRI
            'penyebabRisikoProjects.perlakuanPenyebabRisiko.perlakuanPenyebabMonitorings',
            'perlakuanDampakRisikos.perlakuanDampakMonitorings',
            'dampakRisikoProjects',
            'projectRiskAnalisa'
        ])
        ->where('project_id', $this->projectId)
        ->get();

        $data = new Collection();
        $no = 1;

        foreach ($risks as $risk) {

            $peristiwaText = ($risk->peristiwa_risiko_id == 0)
                            ? $risk->rencana_kegiatan
                            : ($risk->peristiwaRisiko->title ?? '-');

            $kriDesc = $risk->kriProjects->pluck('kri')->implode("\n") ?: '-';
            $amanVal = $risk->kriProjects->map(fn($k) => $k->batas_aman !== null ? $k->batas_aman : '-')->implode("\n");
            $waspadaVal = $risk->kriProjects->map(fn($k) => $k->batas_waspada !== null ? $k->batas_waspada : '-')->implode("\n");
            $bahayaVal = $risk->kriProjects->map(fn($k) => $k->batas_bahaya !== null ? $k->batas_bahaya : '-')->implode("\n");

            // --- LOGIC BARU: REALISASI KRI ---
            $realisasiNilaiList = [];
            $realisasiStatusList = [];

            foreach ($risk->kriProjects as $kri) {
                // Ambil monitoring terakhir (berdasarkan ID desc)
                $lastMon = $kri->kriProjectMonitorings->sortByDesc('id')->first();
                
                // Ambil Nilai
                $realisasiNilaiList[] = $lastMon->nilai_kri_terkini ?? '-';

                // Mapping Status
                $statusText = '-';
                if ($lastMon) {
                    switch ((int)$lastMon->status_kri_terkini) {
                        case 1: $statusText = 'Aman'; break;
                        case 2: $statusText = 'Waspada'; break;
                        case 3: $statusText = 'Bahaya'; break;
                        default: $statusText = '-';
                    }
                }
                $realisasiStatusList[] = $statusText;
            }

            $realisasiNilaiStr = implode("\n", $realisasiNilaiList);
            $realisasiStatusStr = implode("\n", $realisasiStatusList);
            // ---------------------------------

            // --- DATA PENYEBAB ---
            $perlakuanPenyebabStr = '';
            $realisasiPenyebabStr = '';
            $picPenyebabStr = [];
            $biayaPenyebab = 0;
            $realBiayaPenyebab = 0;

            foreach($risk->penyebabRisikoProjects as $idxPenyebab => $penyebab) {
                $noPenyebab = $idxPenyebab + 1;
                foreach($penyebab->perlakuanPenyebabRisiko as $idxPerlakuan => $perlakuan) {
                    $noHierarki = $noPenyebab . '.' . ($idxPerlakuan + 1);
                    $perlakuanPenyebabStr .= $noHierarki . ' ' . $perlakuan->rencana_perlakuan_risiko . "\n";

                    $lastMon = $perlakuan->perlakuanPenyebabMonitorings->sortByDesc('id')->first();
                    $realDesc = $lastMon->deskripsi_perlakuan_risiko ?? '-';
                    $realisasiPenyebabStr .= $noHierarki . ' ' . $realDesc . "\n";

                    if($perlakuan->pic) $picPenyebabStr[] = $noHierarki . ' ' . $perlakuan->pic;

                    $biayaPenyebab += $perlakuan->biaya_perlakuan_risiko;
                    $realBiayaPenyebab += $lastMon->realisasi_biaya_perlakuan_risiko ?? 0;
                }
            }

            // --- DATA DAMPAK ---
            $perlakuanDampakStr = '';
            $realisasiDampakStr = '';
            $picDampakStr = [];
            $biayaDampak = 0;
            $realBiayaDampak = 0;

            foreach($risk->perlakuanDampakRisikos as $idx => $pd) {
                $noList = $idx + 1;
                $perlakuanDampakStr .= $noList . '. ' . $pd->rencana_perlakuan_risiko . "\n";

                $lastMon = $pd->perlakuanDampakMonitorings->sortByDesc('id')->first();
                $realDesc = $lastMon->deskripsi_perlakuan_risiko ?? '-';
                $realisasiDampakStr .= $noList . '. ' . $realDesc . "\n";

                if($pd->pic) $picDampakStr[] = $noList . '. ' . $pd->pic;

                $biayaDampak += $pd->biaya_perlakuan_risiko;
                $realBiayaDampak += $lastMon->realisasi_biaya_perlakuan_risiko ?? 0;
            }

            $finalPic = "";
            if (!empty($picPenyebabStr)) {
                $finalPic .= "PIC Penyebab:\n" . implode("\n", $picPenyebabStr);
            }
            if (!empty($picDampakStr)) {
                if ($finalPic !== "") $finalPic .= "\n\n";
                $finalPic .= "PIC Dampak:\n" . implode("\n", $picDampakStr);
            }
            if ($finalPic === "") $finalPic = "-";

            $penyebabList = $risk->penyebabRisikoProjects->map(fn($item, $k) => ($k + 1) . '. ' . $item->penyebab_risiko)->implode("\n");
            $dampakList = $risk->dampakRisikoProjects->map(fn($item, $k) => ($k + 1) . '. ' . $item->dampak_risiko)->implode("\n");

            $analisa = $risk->projectRiskAnalisa;
            $levelInheren = ($analisa->level_risiko ?? '-') . ' - ' . ($analisa->skala_risiko ?? 0);
            $levelResidual = ($analisa->level_risiko_residual ?? '-') . ' - ' . ($analisa->skala_risiko_residual ?? 0);

            $row = [
                'no' => $no++,
                'sasaran' => $risk->sasaranProyek->kpi_desc ?? $risk->target_capaian_kinerja ?? '-',
                'peristiwa' => $peristiwaText,
                'desc_peristiwa' => $risk->deskripsi_peristiwa_risiko ?? '-',
                'desc_kri' => $kriDesc,
                'aman' => $amanVal === '' ? '-' : $amanVal,
                'waspada' => $waspadaVal === '' ? '-' : $waspadaVal,
                'bahaya' => $bahayaVal === '' ? '-' : $bahayaVal,
                
                // DATA BARU DISINI
                'realisasi_kri_nilai' => $realisasiNilaiStr ?: '-',
                'realisasi_kri_status' => $realisasiStatusStr ?: '-',

                'penyebab' => $penyebabList ?: '-',
                'penjelasan_dampak' => $dampakList ?: '-',

                'dampak_inheren' => $this->formatCurrency($analisa->nilai_dampak ?? 0),
                'eksposur_inheren' => $this->formatCurrency($analisa->eksposur_risiko ?? 0),
                'level_inheren' => $levelInheren,

                'perlakuan_penyebab' => trim($perlakuanPenyebabStr) ?: '-',
                'perlakuan_dampak' => trim($perlakuanDampakStr) ?: '-',
                'biaya_penyebab' => $this->formatCurrency($biayaPenyebab),
                'biaya_dampak' => $this->formatCurrency($biayaDampak),
                'dampak_residual' => $this->formatCurrency($analisa->nilai_dampak_residual ?? 0),
                'eksposur_residual' => $this->formatCurrency($analisa->eksposur_risiko_residual ?? 0),
                'level_residual' => $levelResidual,
                'mulai' => $risk->perkiraan_waktu_terpapar_risiko_mulai ? Carbon::parse($risk->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y') : '-',
                'selesai' => $risk->perkiraan_waktu_terpapar_risiko_akhir ? Carbon::parse($risk->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y') : '-',
                'pic' => $finalPic,

                'realisasi_penyebab' => trim($realisasiPenyebabStr) ?: '-',
                'realisasi_dampak' => trim($realisasiDampakStr) ?: '-',
                'realisasi_biaya_penyebab' => $this->formatCurrency($realBiayaPenyebab),
                'realisasi_biaya_dampak' => $this->formatCurrency($realBiayaDampak),
                'dampak_realisasi_rp' => $this->formatCurrency($analisa->nilai_dampak_residual ?? 0), // Biasanya ada field beda, tapi saya ikut code existing
                'eksposur_realisasi' => $this->formatCurrency($analisa->eksposur_risiko_residual ?? 0),
                'level_realisasi' => $levelResidual,

                'status' => $risk->is_closed ? 'Closed' : 'Open',
                'efektivitas' => $this->calculateEfektifitas($risk),
            ];

            $data->push($row);
        }

        return $data;
    }

    private function calculateEfektifitas($risk)
    {
        $nilai = (float) $risk->efektivitas_perlakuan_risiko;
        return $nilai > 0 ? 'Efektif' : 'Tidak Efektif';
    }

    private function formatCurrency($value)
    {
        return 'Rp' . number_format((float)$value, 0, ',', '.');
    }
}