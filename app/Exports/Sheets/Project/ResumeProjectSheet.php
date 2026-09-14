<?php

namespace App\Exports\Sheets\Project;

use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\ProjectHasilUsaha;
use App\Services\ProjectHasilUsahaSyncService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class ResumeProjectSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithEvents, WithColumnFormatting, WithStrictNullComparison
{
    private $projectId;
    private $bulan;
    private $tahun;
    private $cachedRisks;

    public function __construct($projectId, $bulan, $tahun)
    {
        $this->projectId = $projectId;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function columnFormats(): array
    {
        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return [
            'M' => $currencyFormat,  // Dampak Inheren
            'N' => $currencyFormat,  // Eksposur Inheren
            'R' => $currencyFormat,  // Biaya Perlakuan Penyebab
            'S' => $currencyFormat,  // Biaya Perlakuan Dampak
            'T' => $currencyFormat,  // Dampak Residual
            'U' => $currencyFormat,  // Eksposur Residual
            'AB' => $currencyFormat, // Realisasi Biaya Penyebab
            'AC' => $currencyFormat, // Realisasi Biaya Dampak
            'AD' => $currencyFormat, // Dampak Realisasi
            'AE' => $currencyFormat, // Eksposure Realisasi
        ];
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
                $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(5);

                $project = Project::with(['divisi'])->find($this->projectId);
                $meta = $project->meta ?? [];

                // Ambil RKP langsung dari Project
                $biayaRkp = $project->biaya_perlakuan_risiko_rkp ?? 0;
                $riskLimit = ($project->nk ?? 0) * 0.03;

                $profitCenter = $project->profit_center ?? ($meta['profit_center'] ?? null);
                $jenisKontrak = empty($meta['jenis_kontrak_name'])
                                ? '-'
                                : (is_array($meta['jenis_kontrak_name']) ? implode(', ', $meta['jenis_kontrak_name']) : $meta['jenis_kontrak_name']);

                $caraPembayaran = empty($meta['pembayaran_name'])
                                ? '-'
                                : (is_array($meta['pembayaran_name']) ? implode(', ', $meta['pembayaran_name']) : $meta['pembayaran_name']);

                $hasilUsaha = app(ProjectHasilUsahaSyncService::class)->getLatestForProject($project, true);
                $lspValue = $hasilUsaha?->lsp_review ?? 0;

                $risks = $this->getRisks();
                $rencanaBiayaTotal = 0;
                $realisasiBiayaTotal = 0;

                foreach ($risks as $risk) {
                    [$rencanaPenyebab, $realisasiPenyebab] = $this->sumBiayaPenyebab($risk);
                    [$rencanaDampak, $realisasiDampak] = $this->sumBiayaDampak($risk);
                    $rencanaBiayaTotal += $rencanaPenyebab + $rencanaDampak;
                    $realisasiBiayaTotal += $realisasiPenyebab + $realisasiDampak;
                }

                // Butuh 16 Baris untuk informasi (Tabel digeser ke 18-19)
                $sheet->insertNewRowBefore(1, 19);

                // Format currency Excel (sama seperti LaporanRiskRegisterBaruExport)
                $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

                // Nama Bulan Helper
                $bulanNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                $namaBulan = $bulanNames[(int)$this->bulan] ?? '-';

                // Status Proyek Logic
                $statusProyek = '-';
                if ($project && $project->masa_pelaksanaan_end) {
                    $endDate = Carbon::parse($project->masa_pelaksanaan_end)->startOfDay();
                    $today = Carbon::today();
                    $statusProyek = $endDate->gte($today) ? 'Aktif' : 'Tidak aktif';
                }

                $dataResume = [
                    'B1'  => ['Label' => 'Nama Proyek:', 'Value' => $project->project_name ?? '-'],
                    'B2'  => ['Label' => 'Divisi Operasi:', 'Value' => $project->divisi->name ?? '-'],
                    'B3'  => ['Label' => 'Tipe Kontrak:', 'Value' => $jenisKontrak],
                    'B4'  => ['Label' => 'Cara Pembayaran:', 'Value' => $caraPembayaran],

                    'B5'  => ['Label' => 'Laba Setelah Pajak:', 'Value' => $this->formatCurrency($lspValue), 'currency' => true],
                    'B6'  => ['Label' => 'Nilai OK Total:', 'Value' => $this->formatCurrency($project->nk ?? 0), 'currency' => true],
                    'B7'  => ['Label' => 'Nilai OK Porsi:', 'Value' => $this->formatCurrency($project->nilai_ok_porsi ?? 0), 'currency' => true],

                    'B8'  => ['Label' => 'Risk Limit:', 'Value' => $this->formatCurrency($riskLimit), 'currency' => true],
                    'B9'  => ['Label' => 'Nilai Batasan Risiko:', 'Value' => $this->formatCurrency($riskLimit), 'currency' => true],

                    'B10' => ['Label' => 'Biaya Perlakuan Risiko Sesuai RKP:', 'Value' => $this->formatCurrency($biayaRkp), 'currency' => true],
                    'B11' => ['Label' => 'Rencana Biaya Perlakuan Risiko:', 'Value' => $this->formatCurrency($rencanaBiayaTotal), 'currency' => true],
                    'B12' => ['Label' => 'Realisasi Biaya Perlakuan Risiko:', 'Value' => $this->formatCurrency($realisasiBiayaTotal), 'currency' => true],
                    'B13' => ['Label' => 'Batasan Biaya Perlakuan Risiko:', 'Value' => $this->formatCurrency($project->batasan_biaya_perlakuan_risiko ?? 0), 'currency' => true],

                    // PENAMBAHAN KOLOM BARU DI BAWAH BATASAN BIAYA PERLAKUAN RISIKO
                    'B14' => ['Label' => 'Kode SAP:', 'Value' => $profitCenter ?? '-'],
                    'B15' => ['Label' => 'Periode Pelaporan:', 'Value' => $namaBulan . ' ' . $this->tahun],
                    'B16' => ['Label' => 'Status Project:', 'Value' => $statusProyek],
                ];

                foreach ($dataResume as $cell => $data) {
                    $sheet->setCellValue($cell, $data['Label']);
                    $valueCell = 'C' . substr($cell, 1);
                    $sheet->setCellValue($valueCell, $data['Value']);
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                    if (!empty($data['currency'])) {
                        $sheet->getStyle($valueCell)->getNumberFormat()->setFormatCode($currencyFormat);
                    }
                }

                // Header & Styling Logic (Tetap sama, disingkat visualisasi)
                $sheet->setCellValue('A18', 'No');
                $sheet->setCellValue('B18', 'Sasaran');
                $sheet->setCellValue('C18', 'Peristiwa Risiko');
                $sheet->setCellValue('D18', 'Deskripsi Peristiwa Risiko');
                $sheet->setCellValue('E18', 'Deskripsi KRI');

                $sheet->setCellValue('F18', 'Status KRI (Threshold)');
                $sheet->mergeCells('F18:H18');

                $sheet->setCellValue('I18', 'Nilai Realisasi KRI');
                $sheet->setCellValue('J18', 'Status Realisasi KRI');

                $sheet->setCellValue('K18', 'Penyebab Risiko');
                $sheet->setCellValue('L18', 'Penjelasan Dampak Risiko');

                $sheet->setCellValue('M18', 'Analisa Inheren');
                $sheet->mergeCells('M18:O18');

                $sheet->setCellValue('P18', 'Analisa Residual Rencana');
                $sheet->mergeCells('P18:Y18');

                $sheet->setCellValue('Z18', 'Analisa Residual Realisasi');
                $sheet->mergeCells('Z18:AF18');

                $sheet->setCellValue('AG18', 'Status');
                $sheet->setCellValue('AH18', 'Efektivitas Perlakuan Risiko');

                // Merge Vertical Header (Row 18 & 19)
                foreach(['A','B','C','D','E','I','J','K','L','AG','AH'] as $col){
                    $sheet->mergeCells("{$col}18:{$col}19");
                }

                $sheet->setCellValue('F19', 'Aman');
                $sheet->setCellValue('G19', 'Siaga');
                $sheet->setCellValue('H19', 'Bahaya');

                // Analisa Inheren
                $sheet->setCellValue('M19', 'Dampak Risiko Kuantitatif Inheren');
                $sheet->setCellValue('N19', 'Eksposure Risiko Inheren');
                $sheet->setCellValue('O19', 'Level Risiko Inheren');

                // Analisa Residual Rencana
                $sheet->setCellValue('P19', 'Perlakuan Risiko Penyebab');
                $sheet->setCellValue('Q19', 'Perlakuan Risiko Dampak');
                $sheet->setCellValue('R19', 'Biaya Perlakuan Risiko Penyebab');
                $sheet->setCellValue('S19', 'Biaya Perlakuan Risiko Dampak');
                $sheet->setCellValue('T19', 'Dampak Risiko Kuantitatif Rencana');
                $sheet->setCellValue('U19', 'Eksposure Residual Rencana');
                $sheet->setCellValue('V19', 'Level Residual Rencana');
                $sheet->setCellValue('W19', 'Waktu Mulai');
                $sheet->setCellValue('X19', 'Waktu Selesai');
                $sheet->setCellValue('Y19', 'Penanggung Jawab');

                // Analisa Residual Realisasi
                $sheet->setCellValue('Z19', 'Perlakuan Risiko Penyebab');
                $sheet->setCellValue('AA19', 'Perlakuan Risiko Dampak');
                $sheet->setCellValue('AB19', 'Realisasi Biaya Perlakuan Risiko Penyebab');
                $sheet->setCellValue('AC19', 'Realisasi Biaya Perlakuan Risiko Dampak');
                $sheet->setCellValue('AD19', 'Dampak Risiko Kuantitatif Rupiah');
                $sheet->setCellValue('AE19', 'Eksposure Residual Realisasi');
                $sheet->setCellValue('AF19', 'Level Residual Realisasi');

                // --- 4. STYLING HEADER COLORS ---
                $headerBaseStyle = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ];
                $sheet->getStyle('A18:AH19')->applyFromArray($headerBaseStyle);

                $setColor = function($range, $colorHex) use ($sheet) {
                    $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($colorHex);
                };

                $cGrey   = 'D9D9D9';
                $cBlue   = '9BC2E6';
                $cGreen  = 'C6E0B4';
                $cOrange = 'F4B084';
                $cPink   = 'FF99CC';
                $cWhite  = 'FFFFFF';

                $setColor('A18:A19', $cGrey);
                $setColor('B18:B19', $cBlue);
                $setColor('C18:E19', $cGreen);

                $setColor('F19', '92D050');
                $setColor('G19', 'FFFF00');
                $setColor('H19', 'FF0000');

                $setColor('I18:J19', $cGreen);

                $setColor('K18:L19', $cGreen);
                $setColor('M19', $cOrange);
                $setColor('N19:O19', $cPink);

                $setColor('P18:Y18', $cWhite);
                $setColor('P19:Q19', $cGreen);
                $setColor('R19:T19', $cOrange);
                $setColor('U19:V19', $cPink);
                $setColor('W19:X19', $cGrey);
                $setColor('Y19', $cGreen);

                $setColor('Z18:AF18', $cWhite);
                $setColor('Z19:AA19', $cGreen);
                $setColor('AB19:AD19', $cOrange);
                $setColor('AE19:AF19', $cPink);

                $setColor('AG18:AG19', $cBlue);
                $setColor('AH18:AH19', $cPink);

                // --- 5. STYLING DATA (START FROM ROW 20) ---
                $highestRow = $sheet->getHighestRow();
                $dataStartRow = 20;
                $currencyCols = ['M', 'N', 'R', 'S', 'T', 'U', 'AB', 'AC', 'AD', 'AE'];

                if ($highestRow >= $dataStartRow) {
                    // Pastikan semua kolom rupiah tersimpan sebagai angka
                    foreach ($currencyCols as $col) {
                        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                            $raw = $sheet->getCell($col . $row)->getValue();
                            if (is_string($raw)) {
                                $raw = preg_replace('/[^0-9.\-]/', '', $raw);
                            }
                            $sheet->setCellValueExplicit(
                                $col . $row,
                                (float) ($raw ?: 0),
                                DataType::TYPE_NUMERIC
                            );
                        }
                    }

                    $sheet->getStyle('A20:AH' . $highestRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);

                    $this->applyThresholdColoring($sheet, $highestRow);

                    $this->applyLevelColoring($sheet, 'O', 20, $highestRow);
                    $this->applyLevelColoring($sheet, 'V', 20, $highestRow);
                    $this->applyLevelColoring($sheet, 'AF', 20, $highestRow);
                    $this->applyStatusColoring($sheet, 'AG', 20, $highestRow);

                    // Baris TOTAL nilai rupiah
                    $totalRow = $highestRow + 1;
                    $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                    $sheet->mergeCells('A' . $totalRow . ':L' . $totalRow);
                    foreach ($currencyCols as $col) {
                        $sheet->setCellValue($col . $totalRow, "=SUM({$col}{$dataStartRow}:{$col}{$highestRow})");
                    }

                    $sheet->getStyle('A' . $totalRow . ':AH' . $totalRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF2CC'],
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                    $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';
                    foreach ($currencyCols as $col) {
                        $sheet->getStyle($col . $totalRow)->getNumberFormat()->setFormatCode($currencyFormat);
                    }
                }

                // Autosize loop
                foreach (range('B', 'Z') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
                foreach (['AA','AB','AC','AD','AE','AF','AG','AH'] as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

                // Set manual width untuk text panjang
                $longTextCols = ['C', 'D', 'E', 'K', 'L', 'P', 'Q', 'Z', 'AA', 'Y'];
                foreach ($longTextCols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth(35);
                }
            }
        ];
    }

    private function applyThresholdColoring($sheet, $maxRow) {
        for ($row = 19; $row <= $maxRow; $row++) {
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
            } elseif (str_starts_with($val, 'closed')) {
                $sheet->getStyle($col . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => '8B0000']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4CCCC']]
                ]);
            }
        }
    }

    public function collection()
    {
        $risks = $this->getRisks();

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

            // --- REALISASI KRI ---
            $realisasiNilaiList = [];
            $realisasiStatusList = [];

            foreach ($risk->kriProjects as $kri) {
                // Relasi sudah menerapkan fallback order desc dan batasan periode terpilih
                $lastMon = $kri->kriProjectMonitorings->first();
                $realisasiNilaiList[] = $lastMon->nilai_kri_terkini ?? '-';

                $statusText = '-';
                if ($lastMon) {
                    switch ((int)$lastMon->status_kri_terkini) {
                        case 1: $statusText = 'Aman'; break;
                        case 2: $statusText = 'Siaga'; break;
                        case 3: $statusText = 'Bahaya'; break;
                        default: $statusText = '-';
                    }
                }
                $realisasiStatusList[] = $statusText;
            }

            $realisasiNilaiStr = implode("\n", $realisasiNilaiList);
            $realisasiStatusStr = implode("\n", $realisasiStatusList);

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

                    $lastMon = $this->latestPerlakuanUntilPeriod($perlakuan->perlakuanPenyebabMonitorings);
                    $realDesc = $lastMon?->deskripsi_perlakuan_risiko ?? '-';
                    $realisasiPenyebabStr .= $noHierarki . ' ' . $realDesc . "\n";

                    if($perlakuan->pic) $picPenyebabStr[] = $noHierarki . ' ' . $perlakuan->pic;

                    $biayaPenyebab += $perlakuan->biaya_perlakuan_risiko ?? 0;
                    $realBiayaPenyebab += $lastMon?->realisasi_biaya_perlakuan_risiko ?? 0;
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

                $lastMon = $this->latestPerlakuanUntilPeriod($pd->perlakuanDampakMonitorings);
                $realDesc = $lastMon?->deskripsi_perlakuan_risiko ?? '-';
                $realisasiDampakStr .= $noList . '. ' . $realDesc . "\n";

                if($pd->pic) $picDampakStr[] = $noList . '. ' . $pd->pic;

                $biayaDampak += $pd->biaya_perlakuan_risiko ?? 0;
                $realBiayaDampak += $lastMon?->realisasi_biaya_perlakuan_risiko ?? 0;
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

            // Realisasi: monitoring publish terakhir, atau nilai inherent jika belum pernah publish
            $lastMonitoring = $risk->projectRiskMonitorings->first();
            $analisa = $risk->projectRiskAnalisa;

            $levelInheren = ($analisa->level_risiko ?? '-') . ' - ' . ($analisa->skala_risiko ?? 0);
            $levelResidual = ($analisa->level_risiko_residual ?? '-') . ' - ' . ($analisa->skala_risiko_residual ?? 0);

            $dampakRealisasiRP = $lastMonitoring?->nilai_dampak ?? ($analisa->nilai_dampak ?? 0);
            $eksposureRealisasi = $lastMonitoring?->eksposure_risiko ?? ($analisa->eksposur_risiko ?? 0);

            $levelRealisasiText = $levelInheren;
            if ($lastMonitoring) {
                $levelRealisasiText = ($lastMonitoring->level_risiko ?? '-') . ' - ' . ($lastMonitoring->skala_risiko ?? 0);
            }

            $row = [
                'no' => $no++,
                'sasaran' => $risk->sasaranProyek->kpi_desc ?? $risk->target_capaian_kinerja ?? '-',
                'peristiwa' => $peristiwaText,
                'desc_peristiwa' => $risk->deskripsi_peristiwa_risiko ?? '-',
                'desc_kri' => $kriDesc,
                'aman' => $amanVal === '' ? '-' : $amanVal,
                'waspada' => $waspadaVal === '' ? '-' : $waspadaVal,
                'bahaya' => $bahayaVal === '' ? '-' : $bahayaVal,

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
                'dampak_realisasi_rp' => $this->formatCurrency($dampakRealisasiRP),
                'eksposure_realisasi' => $this->formatCurrency($eksposureRealisasi),
                'level_realisasi' => $levelRealisasiText,

                'status' => $risk->formatStatusRisikoForExport(
                    $this->tahun ? (int) $this->tahun : null,
                    $this->bulan ? (int) $this->bulan : null
                ),
                'efektivitas' => $this->calculateEfektifitas($risk),
            ];

            $data->push($row);
        }

        return $data;
    }

    private function calculateEfektifitas($risk)
    {
        $nilai = (float) $risk->efektivitas_perlakuan_risiko;
        return $nilai >= 0 ? 'Efektif' : 'Tidak Efektif';
    }

    private function getRisks()
    {
        if ($this->cachedRisks !== null) {
            return $this->cachedRisks;
        }

        $filterPublished = $this->filterUpToPeriod(true);

        $this->cachedRisks = ProjectRisk::with([
            'sasaranProyek',
            'peristiwaRisiko',
            'penyebabRisikoProjects.perlakuanPenyebabRisiko.perlakuanPenyebabMonitorings.projectMonitoring',
            'perlakuanDampakRisikos.perlakuanDampakMonitorings.projectMonitoring',
            'dampakRisikoProjects',
            'projectRiskAnalisa',
            'projectRiskMonitorings' => function ($q) use ($filterPublished) {
                $filterPublished($q);
                $q->orderBy('tahun', 'desc')
                    ->orderBy('month', 'desc')
                    ->orderBy('id', 'desc');
            },
            'kriProjects.kriProjectMonitorings' => function ($q) use ($filterPublished) {
                $q->whereHas('projectMonitoring', $filterPublished)->orderBy('id', 'desc');
            }
        ])
        ->where('project_id', $this->projectId)
        ->get();

        return $this->cachedRisks;
    }

    private function filterUpToPeriod(bool $publishedOnly = true): \Closure
    {
        return function ($q) use ($publishedOnly) {
            if ($this->bulan && $this->tahun) {
                // Kolom month bertipe teks, sehingga "<=" dibandingkan secara
                // leksikal ("2" > "10"). Pakai daftar bulan agar Oktober–Desember
                // tidak kehilangan data monitoring.
                $bulanSampai = range(1, (int) $this->bulan);
                $q->where(function ($query) use ($bulanSampai) {
                    $query->where('tahun', '<', $this->tahun)
                          ->orWhere(function ($subQuery) use ($bulanSampai) {
                              $subQuery->where('tahun', $this->tahun)
                                      ->whereIn('month', $bulanSampai);
                          });
                });
            }

            if ($publishedOnly) {
                $q->where(function ($sq) {
                    $sq->where('status', 100)->orWhere('is_approved', 1)->orWhere('is_approved', true);
                });
            }
        };
    }

    /**
     * Realisasi biaya mengikuti lastMonitoring di halaman detail monitoring:
     * child terakhir per perlakuan, termasuk yang belum publish.
     */
    private function latestPerlakuanUntilPeriod($monitorings)
    {
        if (!$monitorings || $monitorings->isEmpty()) {
            return null;
        }

        return $monitorings
            ->sortByDesc(function ($m) {
                $parent = $m->projectMonitoring;

                return sprintf(
                    '%04d%02d%010d',
                    (int) ($parent->tahun ?? 0),
                    (int) ($parent->month ?? 0),
                    (int) $m->id
                );
            })
            ->first();
    }

    private function sumBiayaPenyebab($risk): array
    {
        $rencana = 0;
        $realisasi = 0;

        foreach ($risk->penyebabRisikoProjects as $penyebab) {
            foreach ($penyebab->perlakuanPenyebabRisiko as $perlakuan) {
                $rencana += $perlakuan->biaya_perlakuan_risiko ?? 0;
                $lastMon = $this->latestPerlakuanUntilPeriod($perlakuan->perlakuanPenyebabMonitorings);
                $realisasi += (float) ($lastMon?->realisasi_biaya_perlakuan_risiko ?? 0);
            }
        }

        return [$rencana, $realisasi];
    }

    private function sumBiayaDampak($risk): array
    {
        $rencana = 0;
        $realisasi = 0;

        foreach ($risk->perlakuanDampakRisikos as $perlakuanDampak) {
            $rencana += $perlakuanDampak->biaya_perlakuan_risiko ?? 0;
            $lastMon = $this->latestPerlakuanUntilPeriod($perlakuanDampak->perlakuanDampakMonitorings);
            $realisasi += (float) ($lastMon?->realisasi_biaya_perlakuan_risiko ?? 0);
        }

        return [$rencana, $realisasi];
    }

    private function formatCurrency($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_string($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        return (float) ($value ?: 0);
    }
}
