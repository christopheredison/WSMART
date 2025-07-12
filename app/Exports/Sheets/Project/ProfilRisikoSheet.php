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
                $sheet->setCellValue('B1', 'Nama Project');
                $sheet->setCellValue('C1', 'Kode Project');
                $sheet->setCellValue('D1', 'Sasaran Risiko');
                $sheet->setCellValue('E1', 'No Risiko');
                $sheet->setCellValue('F1', 'Peristiwa Risiko');
                $sheet->setCellValue('G1', 'Deskripsi Peristiwa Risiko');
                $sheet->setCellValue('H1', 'No Penyebab Risiko');
                $sheet->setCellValue('I1', 'Kode Penyebab Risiko');
                $sheet->setCellValue('J1', 'Penyebab Risiko');
                $sheet->setCellValue('K1', 'Key Risk Indicator');
                $sheet->setCellValue('L1', 'Unit Satuan KRI');
                $sheet->setCellValue('M1', 'Kategori Treshold KRI');
                $sheet->setCellValue('P1', 'Jenis Eksisting Kontrol');
                $sheet->setCellValue('Q1', 'Kontrol Eksisting');
                $sheet->setCellValue('R1', 'Penilaian Efektivitas Kontrol');
                $sheet->setCellValue('S1', 'Kategori Dampak');
                $sheet->setCellValue('T1', 'Deskripsi Dampak');
                $sheet->setCellValue('U1', 'Perkiraan Waktu Terpapar Risiko');

                // Row 2: Sub-header untuk Kategori Treshold KRI
                $sheet->setCellValue('M2', 'Aman');
                $sheet->setCellValue('N2', 'Waspada');
                $sheet->setCellValue('O2', 'Bahaya');

                // Merge sel header vertikal untuk kolom yang tidak punya sub-header
                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'P', 'Q', 'R', 'S', 'T', 'U'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge sel header "Kategori Treshold KRI" secara horizontal (dari M1 sampai O1)
                $sheet->mergeCells('M1:O1');

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
                $sheet->getStyle('A1:U2')->applyFromArray($headerStyle);

                // Style khusus untuk sub-header Kategori Threshold KRI
                // Aman (M2) - Hijau
                $amanStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '92D050']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('M2')->applyFromArray($amanStyle);

                // Waspada (N2) - Kuning
                $waspadaStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('N2')->applyFromArray($waspadaStyle);

                // Bahaya (O2) - Merah
                $bahayaStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FF0000']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];
                $sheet->getStyle('O2')->applyFromArray($bahayaStyle);

                $dataStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ];

                // Hitung jumlah data aktual untuk border yang tepat
                $risikosCount = ProjectRisk::where('project_periode_list_id', $this->projectId)->count();
                
                // Estimasi jumlah row berdasarkan data risiko dan relasi
                $estimatedRows = $risikosCount * 3; // Asumsi rata-rata 3 row per risiko
                
                // Border hanya untuk row yang berisi data (header + data aktual)
                if ($risikosCount > 0) {
                    $maxDataRow = 2 + $estimatedRows; // Row 2 (header) + estimasi data
                    $sheet->getStyle('A3:U' . $maxDataRow)->applyFromArray($dataStyle);
                    
                    // Set format text untuk kolom Kode Penyebab Risiko
                    $sheet->getStyle('I3:I' . $maxDataRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                    
                    // Tambahkan pewarnaan timeline berdasarkan data
                    $this->applyThresholdColoring($sheet, $maxDataRow);
                }

                foreach (range('A', 'U') as $column) {
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
            // Kolom M (Aman) - Hijau
            $amanValue = $sheet->getCell('M' . $row)->getValue();
            if ($amanValue && $amanValue !== '-') {
                $sheet->getStyle('M' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '92D050']
                    ]
                ]);
            }
            
            // Kolom N (Waspada) - Kuning
            $waspadaValue = $sheet->getCell('N' . $row)->getValue();
            if ($waspadaValue && $waspadaValue !== '-') {
                $sheet->getStyle('N' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00']
                    ]
                ]);
            }
            
            // Kolom O (Bahaya) - Merah
            $bahayaValue = $sheet->getCell('O' . $row)->getValue();
            if ($bahayaValue && $bahayaValue !== '-') {
                $sheet->getStyle('O' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FF0000']
                    ]
                ]);
            }
        }
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        // Ambil data utama dan urutkan berdasarkan skala risiko tertinggi
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
            $project = $risiko->projectPeriodeList->project;
            $analisa = $risiko->projectRiskAnalisa;
            
            // Kumpulkan semua penyebab risiko
            $penyebabRisikos = $risiko->penyebabRisikoProjects;
            $kriProjects = $risiko->kriProjects;

            if ($penyebabRisikos->isEmpty() && $kriProjects->isEmpty()) {
                $rowData = [
                    'no' => $nomorUrutRisiko,
                    'nama_project' => $project->project_name ?? '-',
                    'kode_project' => $project->project_code ?? '-',
                    'sasaran_risiko' => $risiko->target_capaian_kinerja ?? '-',
                    'no_risiko' => $nomorUrutRisiko,
                    'peristiwa_risiko' => $risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-',
                    'deskripsi_peristiwa_risiko' => $risiko->deskripsi_peristiwa_risiko ?? '-',
                    'no_penyebab_risiko' => '-',
                    'kode_penyebab_risiko' => '-',
                    'penyebab_risiko' => '-',
                    'key_risk_indicator' => '-',
                    'unit_satuan_kri' => '-',
                    'kategori_treshold_aman' => '-',
                    'kategori_treshold_waspada' => '-',
                    'kategori_treshold_bahaya' => '-',
                    'jenis_eksisting_kontrol' => '-',
                    'kontrol_eksisting' => '-',
                    'penilaian_efektivitas_kontrol' => '-',
                    'kategori_dampak' => $analisa->kategori_dampak ?? '-',
                    'deskripsi_dampak' => $analisa->deskripsi_dampak ?? '-',
                    'perkiraan_waktu_terpapar' => $this->formatWaktuTerpapar($risiko),
                ];
                
                $exportData->push($rowData);
            } else {
                // Logika untuk risiko dengan penyebab dan/atau KRI
                $isFirstRowOfGroup = true;
                $nomorUrutPenyebab = 1;

                if ($penyebabRisikos->isEmpty()) {
                    // Jika tidak ada penyebab tapi ada KRI
                    foreach ($kriProjects as $kri) {
                        $rowData = $this->createRowData($risiko, null, $kri, $isFirstRowOfGroup, $nomorUrutRisiko, 0, true);
                        $exportData->push($rowData);
                        $isFirstRowOfGroup = false;
                    }
                } elseif ($kriProjects->isEmpty()) {
                    // Jika ada penyebab tapi tidak ada KRI
                    foreach ($penyebabRisikos as $penyebab) {
                        $rowData = $this->createRowData($risiko, $penyebab, null, $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab, true);
                        $exportData->push($rowData);
                        $isFirstRowOfGroup = false;
                        $nomorUrutPenyebab++;
                    }
                } else {
                    // Jika ada penyebab dan ada KRI - buat kombinasi
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
            'nama_project' => $isFirstRowOfGroup ? ($project->project_name ?? '-') : '',
            'kode_project' => $isFirstRowOfGroup ? ($project->project_code ?? '-') : '',
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
        
        return $awal ?: ($akhir ?: '-');
    }
}