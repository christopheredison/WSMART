<?php

namespace App\Exports\Sheets\Project;

use App\Models\Project;
use App\Models\ProjectRisk;
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
                // Set Width Kolom A jadi kecil (Untuk No)
                $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(5);

                // --- 2. RESUME PROYEK (TOP SECTION) ---
                $project = Project::with(['divisi'])->find($this->projectId);
                $meta = $project->meta ?? [];

                // Hitung Agregat Biaya
                $risks = ProjectRisk::with(['penyebabRisikoProjects.perlakuanPenyebabRisiko'])
                            ->where('project_id', $this->projectId)->get();

                $rencanaBiayaTotal = $risks->flatMap(fn($r) => $r->penyebabRisikoProjects)
                                        ->flatMap(fn($p) => $p->perlakuanPenyebabRisiko)
                                        ->sum('biaya_perlakuan_risiko');

                // Logic Total Realisasi (Diambil dari sum realisasi_biaya_perlakuan_risiko jika ada, atau placeholder)
                $realisasiBiayaTotal = $risks->flatMap(fn($r) => $r->penyebabRisikoProjects)
                                          ->flatMap(fn($p) => $p->perlakuanPenyebabRisiko)
                                           ->sum('realisasi_biaya_perlakuan_risiko'); // Asumsi nama kolom

                $sheet->insertNewRowBefore(1, 14);

                // Mapping Resume (Digeser ke Kolom B)
                $dataResume = [
                    'B1' => ['Label' => 'Nama Proyek:', 'Value' => $project->project_name ?? '-'],
                    'B2' => ['Label' => 'Divisi Operasi:', 'Value' => $project->divisi->name ?? '-'],
                    'B3' => ['Label' => 'Nilai OK Total:', 'Value' => $this->formatCurrency($project->nk)],
                    'B4' => ['Label' => 'Nilai OK Porsi:', 'Value' => ''],
                    'B5' => ['Label' => 'Laba Setelah Pajak:', 'Value' => ''],
                    'B6' => ['Label' => 'Biaya Perlakuan Risiko Sesuai RKP:', 'Value' => ''],
                    'B7' => ['Label' => 'Rencana Biaya Perlakuan Risiko:', 'Value' => $this->formatCurrency($rencanaBiayaTotal)],
                    'B8' => ['Label' => 'Realisasi Biaya Perlakuan Risiko:', 'Value' => $this->formatCurrency($realisasiBiayaTotal)],
                    'B9' => ['Label' => 'Tipe Kontrak:', 'Value' => $meta['jenis_kontrak_id'] ?? '-'],
                    'B10' => ['Label' => 'Cara Pembayaran:', 'Value' => $meta['pembayaran_name'] ?? '-'],
                    'B11' => ['Label' => 'Batasan Biaya Perlakuan Risiko:', 'Value' => ''],
                    'B12' => ['Label' => 'Nilai Batasan Risiko:', 'Value' => ''],
                ];

                foreach ($dataResume as $cell => $data) {
                    $sheet->setCellValue($cell, $data['Label']);
                    $sheet->setCellValue('C' . substr($cell, 1), $data['Value']);
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                }

                // --- 3. HEADER TABEL (ROW 14-15) ---

                // Row 14: Parent Headers
                $sheet->setCellValue('A14', 'No');
                $sheet->setCellValue('B14', 'Sasaran');
                $sheet->setCellValue('C14', 'Peristiwa Risiko');
                $sheet->setCellValue('D14', 'Penyebab Risiko');
                $sheet->setCellValue('E14', 'Penjelasan Dampak Risiko');

                // Group 1: Analisa Inheren
                $sheet->setCellValue('F14', 'Analisa Inheren');
                $sheet->mergeCells('F14:H14');

                // Group 2: Analisa Residual Rencana
                $sheet->setCellValue('I14', 'Analisa Residual Rencana');
                $sheet->mergeCells('I14:P14'); // Perlakuan, Biaya, Dampak, Eksposur, Level, Waktu(2), PIC

                // Group 3: Analisa Residual Realisasi
                $sheet->setCellValue('Q14', 'Analisa Residual Realisasi');
                $sheet->mergeCells('Q14:U14'); // Realisasi Perlakuan, Realisasi Biaya, Dampak, Eksposur, Level

                // Others
                $sheet->setCellValue('V14', 'Status');
                $sheet->setCellValue('W14', 'Efektivitas Perlakuan Risiko');

                // Merge Vertical (Row 14-15) untuk kolom yg tidak punya anak
                foreach(['A','B','C','D','E','V','W'] as $col){
                    $sheet->mergeCells("{$col}14:{$col}15");
                }

                // Row 15: Child Headers
                // Child Analisa Inheren
                $sheet->setCellValue('F15', 'Dampak Risiko Kuantitatif Inheren');
                $sheet->setCellValue('G15', 'Eksposure Inheren');
                $sheet->setCellValue('H15', 'Level Risiko Inheren');

                // Child Residual Rencana
                $sheet->setCellValue('I15', 'Perlakuan Risiko');
                $sheet->setCellValue('J15', 'Biaya Perlakuan Risiko');
                $sheet->setCellValue('K15', 'Dampak Risiko Residual Rencana');
                $sheet->setCellValue('L15', 'Eksposure Residual Rencana');
                $sheet->setCellValue('M15', 'Level Residual Rencana');

                // Waktu Rencana (Parent kecil di dalam Residual Rencana)
                $sheet->setCellValue('N14', 'Waktu'); // Timpa merge I14:P14 logika sedikit? Tidak, header Waktu ada di bawah I14
                // Koreksi: Header "Waktu" ada di row 14? Tidak, "Waktu" biasanya sub-header.
                // Sesuai prompt "Analisa Residual Rencana punya child kolom Waktu -> Mulai, Selesai"
                // Jadi kita split I14:P14. Mari kita tata ulang Row 14 agar rapi.
                // I14 Label: Analisa Residual Rencana. Range: I14:P14.
                // Row 15 Labels:
                $sheet->setCellValue('N15', 'Mulai');
                $sheet->setCellValue('O15', 'Selesai');
                // Agar ada label "Waktu" di atas Mulai/Selesai, tapi di bawah "Analisa Residual Rencana":
                // Excel standard tidak support 3 row header mudah di sini tanpa geser.
                // Kita taruh "Waktu Mulai" dan "Waktu Selesai" langsung di row 15 untuk simplifikasi,
                // atau "Waktu" di N15-O15 tapi row data mulai 16.
                // Kita ikuti prompt: "Waktu -> punya child kolom: 1. Mulai 2. Selesai".
                // Kita label saja di Row 15: "Waktu Mulai" dan "Waktu Selesai".

                $sheet->setCellValue('P15', 'Penanggung Jawab');

                // Child Residual Realisasi
                $sheet->setCellValue('Q15', 'Realisasi Perlakuan Risiko');
                $sheet->setCellValue('R15', 'Realisasi Biaya Perlakuan Risiko');
                $sheet->setCellValue('S15', 'Dampak Risiko Kuantitatif Rupiah');
                $sheet->setCellValue('T15', 'Eksposure Residual Realisasi');
                $sheet->setCellValue('U15', 'Level Residual Realisasi');

                // Styling Header
                $headerStyle = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9BC2E6']]
                ];
                $sheet->getStyle('A14:W15')->applyFromArray($headerStyle);

                // Sub-header coloring (Grey)
                $sheet->getStyle('F15:H15')->getFill()->getStartColor()->setARGB('DBDBDB');
                $sheet->getStyle('I15:P15')->getFill()->getStartColor()->setARGB('DBDBDB');
                $sheet->getStyle('Q15:U15')->getFill()->getStartColor()->setARGB('DBDBDB');

                // --- 4. STYLING DATA & CONDITIONAL FORMATTING ---
                $highestRow = $sheet->getHighestRow();
                if ($highestRow > 15) {
                    $sheet->getStyle('A16:W' . $highestRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);

                    // Warnai Cell Level Risiko (Inheren, Rencana, Realisasi)
                    $this->applyLevelColoring($sheet, 'H', 16, $highestRow); // Level Inheren
                    $this->applyLevelColoring($sheet, 'M', 16, $highestRow); // Level Residual Rencana
                    $this->applyLevelColoring($sheet, 'U', 16, $highestRow); // Level Residual Realisasi

                    // Warnai Status (Column V)
                    $this->applyStatusColoring($sheet, 'V', 16, $highestRow);
                }

                // Auto Size Columns
                foreach (range('B', 'W') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Fix width untuk kolom teks panjang agar wrap text bekerja rapi
                $longTextCols = ['C', 'D', 'E', 'I', 'Q', 'P'];
                foreach ($longTextCols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth(35);
                }
                $sheet->getColumnDimension('W')->setAutoSize(false)->setWidth(20);
            }
        ];
    }

    private function applyLevelColoring($sheet, $col, $startRow, $endRow)
    {
        for ($row = $startRow; $row <= $endRow; $row++) {
            $val = strtolower(trim($sheet->getCell($col . $row)->getValue()));
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
                $style = [
                    'font' => ['color' => ['rgb' => '006400']], // Dark Green Text
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6E0B4']] // Light Green BG
                ];
                $sheet->getStyle($col . $row)->applyFromArray($style);
            } elseif ($val === 'closed') {
                $style = [
                    'font' => ['color' => ['rgb' => '8B0000']], // Dark Red Text
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4CCCC']] // Light Red BG
                ];
                $sheet->getStyle($col . $row)->applyFromArray($style);
            }
        }
    }

    public function collection()
    {
        $risks = ProjectRisk::with([
            'sasaranProyek',
            'peristiwaRisiko',
            'penyebabRisikoProjects.perlakuanPenyebabRisiko',
            'dampakRisikoProjects',
            'projectRiskAnalisa'
        ])
        ->where('project_id', $this->projectId)
        ->get();

        $data = new Collection();
        $no = 1;

        foreach ($risks as $risk) {

            $perlakuanStr = '';
            $picStr = '';
            $realisasiPerlakuanStr = '';

            $totalBiayaRencana = 0;
            $totalBiayaRealisasi = 0;

            foreach($risk->penyebabRisikoProjects as $idxPenyebab => $penyebab) {
                $noPenyebab = $idxPenyebab + 1;

                foreach($penyebab->perlakuanPenyebabRisiko as $idxPerlakuan => $perlakuan) {
                    $noHierarki = $noPenyebab . '.' . ($idxPerlakuan + 1);

                    $perlakuanStr .= $noHierarki . ' ' . $perlakuan->rencana_perlakuan_risiko . "\n";
                    $picStr .= $noHierarki . ' ' . ($perlakuan->pic ?? '-') . "\n";
                    $realisasiText = $perlakuan->realisasi_perlakuan_risiko ?? '-';
                    $realisasiPerlakuanStr .= $noHierarki . ' ' . $realisasiText . "\n";

                    $totalBiayaRencana += $perlakuan->biaya_perlakuan_risiko;
                    $totalBiayaRealisasi += $perlakuan->realisasi_biaya_perlakuan_risiko ?? 0;
                }
            }

            $penyebabList = $risk->penyebabRisikoProjects->map(fn($item, $k) => ($k + 1) . '. ' . $item->penyebab_risiko)->implode("\n");
            $dampakList = $risk->dampakRisikoProjects->map(fn($item, $k) => ($k + 1) . '. ' . $item->dampak_risiko)->implode("\n");

            $analisa = $risk->projectRiskAnalisa;

            $row = [
                'no' => $no++,
                'sasaran' => $risk->sasaranProyek->kpi_desc ?? $risk->target_capaian_kinerja ?? '-',
                'peristiwa' => $risk->peristiwaRisiko->title ?? $risk->deskripsi_peristiwa_risiko ?? '-',
                'penyebab' => $penyebabList ?: '-',
                'penjelasan_dampak' => $dampakList ?: '-',

                // Analisa Inheren
                'dampak_inheren' => $this->formatCurrency($analisa->nilai_dampak ?? 0),
                'eksposur_inheren' => $this->formatCurrency($analisa->eksposur_risiko ?? 0),
                'level_inheren' => $analisa->level_risiko ?? '-',

                // Analisa Residual Rencana
                'perlakuan' => trim($perlakuanStr) ?: '-',
                'biaya' => $this->formatCurrency($totalBiayaRencana),
                'dampak_residual' => $this->formatCurrency($analisa->nilai_dampak_residual ?? 0),
                'eksposur_residual' => $this->formatCurrency($analisa->eksposur_risiko_residual ?? 0),
                'level_residual' => $analisa->level_risiko_residual ?? '-',
                'mulai' => $risk->perkiraan_waktu_terpapar_risiko_mulai ? Carbon::parse($risk->perkiraan_waktu_terpapar_risiko_mulai)->format('d-M-Y') : '-',
                'selesai' => $risk->perkiraan_waktu_terpapar_risiko_akhir ? Carbon::parse($risk->perkiraan_waktu_terpapar_risiko_akhir)->format('d-M-Y') : '-',
                'pic' => trim($picStr) ?: '-',

                // Analisa Residual Realisasi
                'realisasi_perlakuan' => trim($realisasiPerlakuanStr) ?: '-',
                'realisasi_biaya' => $this->formatCurrency($totalBiayaRealisasi),
                'dampak_realisasi' => $this->formatCurrency($analisa->nilai_dampak_residual ?? 0),
                'eksposur_realisasi' => $this->formatCurrency($analisa->eksposur_risiko_residual ?? 0),
                'level_realisasi' => $analisa->level_risiko_residual ?? '-',

                // Status & Efektivitas
                'status' => $risk->is_closed ? 'Closed' : 'Open',
                'efektivitas' => $this->calculateEfektifitas($risk),
            ];

            $data->push($row);
        }

        return $data;
    }

    private function calculateEfektifitas($risk)
    {
        // if (!$risk->is_closed) {
        //     return 'Belum Ditutup';
        // }

        $nilai = (float) $risk->efektivitas_perlakuan_risiko;
        return $nilai > 0 ? 'Efektif' : 'Tidak Efektif';
    }

    private function formatCurrency($value)
    {
        return 'Rp' . number_format((float)$value, 0, ',', '.');
    }
}
