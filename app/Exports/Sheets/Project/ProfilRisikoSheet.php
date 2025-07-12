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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class ProfilRisikoSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithEvents
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
        return 'Profil Risiko';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return []; // Headings di-generate melalui event
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
                $sheet->setCellValue('C1', 'Kode BUMN');
                $sheet->setCellValue('D1', 'Sasaran KBUMN');
                $sheet->setCellValue('E1', 'Nama Project');
                $sheet->setCellValue('F1', 'Sasaran Risiko');
                $sheet->setCellValue('G1', 'No Risiko');
                $sheet->setCellValue('H1', 'Peristiwa Risiko');
                $sheet->setCellValue('I1', 'Deskripsi Peristiwa Risiko');
                $sheet->setCellValue('J1', 'No Penyebab Risiko');
                $sheet->setCellValue('K1', 'Kode Penyebab Risiko');
                $sheet->setCellValue('L1', 'Penyebab Risiko');
                $sheet->setCellValue('M1', 'Key Risk Indicator');
                $sheet->setCellValue('N1', 'Unit Satuan KRI');
                $sheet->setCellValue('O1', 'Kategori Treshold KRI');
                $sheet->setCellValue('R1', 'Jenis Eksisting Kontrol');
                $sheet->setCellValue('S1', 'Kontrol Eksisting');
                $sheet->setCellValue('T1', 'Penilaian Efektivitas Kontrol');
                $sheet->setCellValue('U1', 'Kategori Dampak');
                $sheet->setCellValue('V1', 'Deskripsi Dampak');
                $sheet->setCellValue('W1', 'Perkiraan Waktu Terpapar Risiko');

                // Row 2: Sub-header untuk Kategori Treshold KRI
                $sheet->setCellValue('O2', 'Aman');
                $sheet->setCellValue('P2', 'Waspada');
                $sheet->setCellValue('Q2', 'Bahaya');

                // Merge sel header
                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'R', 'S', 'T', 'U', 'V', 'W'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }
                $sheet->mergeCells('O1:Q1');

                // Style utama untuk seluruh header (A1:W2) - Biru Muda
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
                $sheet->getStyle('A1:W2')->applyFromArray($headerStyle);

                // Style khusus untuk sub-header KRI (menimpa warna background)
                // Aman (O2) - Hijau
                $sheet->getStyle('O2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');

                // Waspada (P2) - Kuning
                $sheet->getStyle('P2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

                // Bahaya (Q2) - Merah
                $sheet->getStyle('Q2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');

                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                ];

                // Terapkan border ke semua baris data
                $highestRow = $sheet->getHighestRow();
                if ($highestRow > 2) {
                    $sheet->getStyle('A3:W' . $highestRow)->applyFromArray($dataStyle);
                    
                    // Set format text untuk kolom Kode Penyebab Risiko
                    $sheet->getStyle('K3:K' . $highestRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                    // Terapkan pewarnaan KRI pada data
                    $this->applyThresholdColoring($sheet, $highestRow);
                }

                // Auto size semua kolom
                foreach (range('A', 'W') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    /**
     * Apply coloring untuk Kategori Threshold KRI pada data
     */
    private function applyThresholdColoring($sheet, $maxRow)
    {
        // Mulai dari row 3 (setelah header)
        for ($row = 3; $row <= $maxRow; $row++) {
            // Kolom O (Aman) - Hijau
            $amanValue = $sheet->getCell('O' . $row)->getValue();
            if ($amanValue && $amanValue !== '-') {
                $sheet->getStyle('O' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
            }
            
            // Kolom P (Waspada) - Kuning
            $waspadaValue = $sheet->getCell('P' . $row)->getValue();
            if ($waspadaValue && $waspadaValue !== '-') {
                $sheet->getStyle('P' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
            }
            
            // Kolom Q (Bahaya) - Merah
            $bahayaValue = $sheet->getCell('Q' . $row)->getValue();
            if ($bahayaValue && $bahayaValue !== '-') {
                $sheet->getStyle('Q' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');
            }
        }
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $risikos = ProjectRisk::with([
            'projectPeriodeList.project',
            'projectRiskAnalisa',
            'penyebabRisikoProjects',
            'kriProjects',
            'jenisKontrolEksisting',
            'projectKontrolEksistings',
            'penilaianEfektivitasKontrolObj',
        ])
        ->where('project_id', $this->projectId)
        ->get()
        ->sortByDesc('projectRiskAnalisa.skala_risiko');

        $exportData = new Collection();
        $nomorUrutRisiko = 1;

        foreach ($risikos as $risiko) {
            $penyebabRisikos = $risiko->penyebabRisikoProjects;
            $kriProjects = $risiko->kriProjects;

            if ($penyebabRisikos->isEmpty() && $kriProjects->isEmpty()) {
                $rowData = $this->createRowData($risiko, null, null, true, $nomorUrutRisiko, 0);
                $exportData->push($rowData);
            } else {
                $isFirstRowOfGroup = true;
                $nomorUrutPenyebab = 1;

                if ($penyebabRisikos->isEmpty()) {
                    foreach ($kriProjects as $kri) {
                        $rowData = $this->createRowData($risiko, null, $kri, $isFirstRowOfGroup, $nomorUrutRisiko, 0);
                        $exportData->push($rowData);
                        $isFirstRowOfGroup = false;
                    }
                } elseif ($kriProjects->isEmpty()) {
                    foreach ($penyebabRisikos as $penyebab) {
                        $rowData = $this->createRowData($risiko, $penyebab, null, $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab);
                        $exportData->push($rowData);
                        $isFirstRowOfGroup = false;
                        $nomorUrutPenyebab++;
                    }
                } else {
                    foreach ($penyebabRisikos as $penyebab) {
                        $isFirstKRIOfPenyebab = true;
                        foreach ($kriProjects as $kri) {
                            $rowData = $this->createRowData($risiko, $penyebab, $kri, $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab, $isFirstKRIOfPenyebab);
                            $exportData->push($rowData);
                            $isFirstRowOfGroup = false;
                            $isFirstKRIOfPenyebab = false;
                        }
                        $nomorUrutPenyebab++;
                    }
                }
            }
            $nomorUrutRisiko++;
        }

        return $exportData;
    }
    
    /**
     * Create row data untuk setiap baris
     */
    private function createRowData($risiko, $penyebab, $kri, $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab, $isFirstKRIOfPenyebab = true)
    {
        $project = $risiko->projectPeriodeList->project;
        $analisa = $risiko->projectRiskAnalisa;
        
        return [
            'no' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
            'nama_bumn' => $isFirstRowOfGroup ? 'PT Wijaya Karya (Persero) Tbk' : '',
            'kode_bumn' => $isFirstRowOfGroup ? '-' : '',
            'sasaran_kbumn' => $isFirstRowOfGroup ? '-' : '',
            'nama_project' => $isFirstRowOfGroup ? ($project->project_name ?? '-') : '',
            'sasaran_risiko' => $isFirstRowOfGroup ? ($risiko->target_capaian_kinerja ?? '-') : '',
            'no_risiko' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
            'peristiwa_risiko' => $isFirstRowOfGroup ? ($risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-') : '',
            'deskripsi_peristiwa_risiko' => $isFirstRowOfGroup ? ($risiko->deskripsi_peristiwa_risiko ?? '-') : '',
            'no_penyebab_risiko' => ($penyebab && $isFirstKRIOfPenyebab) ? $nomorUrutRisiko : '',
            'kode_penyebab_risiko' => ($penyebab && $isFirstKRIOfPenyebab) ? "'" . $nomorUrutRisiko . '.' . $nomorUrutPenyebab : '',
            'penyebab_risiko' => ($penyebab && $isFirstKRIOfPenyebab) ? ($penyebab->penyebab_risiko ?? '-') : '',
            'key_risk_indicator' => $kri ? ($kri->kri ?? '-') : '-',
            'unit_satuan_kri' => $kri ? ($kri->satuan_kri ?? '-') : '-',
            'kategori_treshold_aman' => $kri ? ($kri->batas_aman ?? '-') : '-',
            'kategori_treshold_waspada' => $kri ? ($kri->batas_waspada ?? '-') : '-',
            'kategori_treshold_bahaya' => $kri ? ($kri->batas_bahaya ?? '-') : '-',
            'jenis_eksisting_kontrol' => ($penyebab && $isFirstKRIOfPenyebab) ? (optional($risiko->jenisKontrolEksisting)->jenis_kontrol ?? '-') : '',
            'kontrol_eksisting' => ($penyebab && $isFirstKRIOfPenyebab) ? ($this->getKontrolEksisting($risiko) ?? '-') : '',
            'penilaian_efektivitas_kontrol' => ($penyebab && $isFirstKRIOfPenyebab) ? (optional($risiko->penilaianEfektivitasKontrolObj)->efektivitas_kontrol ?? '-') : '',
            'kategori_dampak' => $isFirstRowOfGroup ? ($analisa->kategori_dampak ?? '-') : '',
            'deskripsi_dampak' => $isFirstRowOfGroup ? ($analisa->deskripsi_dampak ?? '-') : '',
            'perkiraan_waktu_terpapar' => $isFirstRowOfGroup ? $this->formatWaktuTerpapar($risiko) : '',
        ];
    }
    
    /**
     * Get kontrol eksisting
     */
    private function getKontrolEksisting($risiko)
    {
        $kontrolEksistings = $risiko->projectKontrolEksistings;
        if ($kontrolEksistings->isNotEmpty()) {
            return $kontrolEksistings->pluck('kontrol_eksisting_desc')->implode('; ');
        }
        return '-';
    }

    /**
     * Format waktu terpapar risiko
     */
    private function formatWaktuTerpapar($risiko)
    {
        $awal = $risiko->perkiraan_waktu_terpapar_risiko_mulai;
        $akhir = $risiko->perkiraan_waktu_terpapar_risiko_akhir;
        
        if ($awal && $akhir) {
            $awalFormatted = Carbon::parse($awal)->format('j F Y');
            $akhirFormatted = Carbon::parse($akhir)->format('j F Y');
            return $awalFormatted . ' - ' . $akhirFormatted;
        }
        
        return $awal ? Carbon::parse($awal)->format('j F Y') : ($akhir ? Carbon::parse($akhir)->format('j F Y') : '-');
    }
}