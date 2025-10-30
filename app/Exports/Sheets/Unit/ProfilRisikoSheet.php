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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use Carbon\Carbon;

class ProfilRisikoSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithEvents
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
                $sheet->setCellValue('B1', 'Nama BUMN');
                $sheet->setCellValue('C1', 'Kode BUMN');
                $sheet->setCellValue('D1', 'Sasaran BUMN');
                $sheet->setCellValue('E1', 'Sasaran KBUMN');
                $sheet->setCellValue('F1', 'Kategori Risiko BUMN');
                $sheet->setCellValue('G1', 'Kategori Risiko T2 & T3 KBUMN');
                $sheet->setCellValue('H1', 'No Risiko');
                $sheet->setCellValue('I1', 'Peristiwa Risiko');
                $sheet->setCellValue('J1', 'Deskripsi Peristiwa Risiko');
                $sheet->setCellValue('K1', 'No Penyebab Risiko');
                $sheet->setCellValue('L1', 'Kode Penyebab Risiko');
                $sheet->setCellValue('M1', 'Penyebab Risiko');
                $sheet->setCellValue('N1', 'Key Risk Indicator');
                $sheet->setCellValue('O1', 'Unit Satuan KRI');
                $sheet->setCellValue('P1', 'Kategori Treshold KRI');
                $sheet->setCellValue('S1', 'Jenis Eksisting Kontrol');
                $sheet->setCellValue('T1', 'Kontrol Eksisting');
                $sheet->setCellValue('U1', 'Penilaian Efektivitas Kontrol');
                $sheet->setCellValue('V1', 'Kategori Dampak');
                $sheet->setCellValue('W1', 'Deskripsi Dampak');
                $sheet->setCellValue('X1', 'Perkiraan Waktu Terpapar Risiko');

                // Row 2: Sub-header untuk Kategori Treshold KRI
                $sheet->setCellValue('P2', 'Aman');
                $sheet->setCellValue('Q2', 'Waspada');
                $sheet->setCellValue('R2', 'Bahaya');

                // Merge sel header vertikal untuk kolom yang tidak punya sub-header
                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'S', 'T', 'U', 'V', 'W', 'X'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge sel header "Kategori Treshold KRI" secara horizontal (dari P1 sampai R1)
                $sheet->mergeCells('P1:R1');

                // Atur style untuk header utama (Row 1 & 2) - Biru
                $headerStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
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
                $sheet->getStyle('A1:X2')->applyFromArray($headerStyle);

                // Style khusus untuk sub-header Kategori Threshold KRI
                // Aman (P2) - Hijau
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
                $sheet->getStyle('P2')->applyFromArray($amanStyle);

                // Waspada (Q2) - Kuning
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
                $sheet->getStyle('Q2')->applyFromArray($waspadaStyle);

                // Bahaya (R2) - Merah
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
                $sheet->getStyle('R2')->applyFromArray($bahayaStyle);

                $lastRow = $sheet->getHighestRow();

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

                if ($lastRow > 2) {
                    // Terapkan style border dan wrap text ke semua data
                    $dataRange = 'A3:X' . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);

                    // Set format text untuk kolom Kode Penyebab Risiko (Kolom L)
                    $sheet->getStyle('L3:L' . $lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                    
                    // Set rata tengah horizontal untuk kolom-kolom tertentu
                    $centerCols = ['A', 'C', 'H', 'K', 'L', 'O', 'P', 'Q', 'R', 'S', 'U', 'V'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}3:{$col}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }

                    // Tambahkan pewarnaan background untuk kolom Kategori Threshold KRI
                    // Gunakan $lastRow, bukan $maxDataRow
                    $this->applyThresholdColoring($sheet, $lastRow);
                }

                foreach (range('A', 'X') as $column) {
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
            // Kolom P (Aman) - Hijau
            $amanValue = $sheet->getCell('P' . $row)->getValue();
            if ($amanValue !== null && $amanValue !== '-') {
                $sheet->getStyle('P' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '92D050']
                    ]
                ]);
            }
            
            // Kolom Q (Waspada) - Kuning
            $waspadaValue = $sheet->getCell('Q' . $row)->getValue();
            if ($waspadaValue !== null && $waspadaValue !== '-') {
                $sheet->getStyle('Q' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00']
                    ]
                ]);
            }
            
            // Kolom R (Bahaya) - Merah
            $bahayaValue = $sheet->getCell('R' . $row)->getValue();
            if ($bahayaValue !== null && $bahayaValue !== '-') {
                $sheet->getStyle('R' . $row)->applyFromArray([
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
        $risikos = IdentifikasiRisiko::with([
                'unit',
                'riskAnalysis',
                'kategoriRisiko',
                'jenisRisiko',
                'peristiwaRisiko',
                'penyebabRisiko',
                'kris',
                'penilaianEfektifitasKontrol',
            ])
            ->where('periode_id', $this->periodeId)
            ->where('unit_id', $this->unitId)
            ->get()
            ->sortByDesc('riskAnalysis.skala_risiko');

        $exportData = new Collection();
        $nomorUrutRisiko = 1;

        foreach ($risikos as $risiko) {
            $jumlahPenyebab = $risiko->penyebabRisiko->count();
            $jumlahKRI = $risiko->kris->count();
            
            if ($jumlahPenyebab === 0 && $jumlahKRI === 0) {
                $rowData = [
                    'no' => $nomorUrutRisiko,
                    'nama_bumn' => 'PT Wijaya Karya (Persero) Tbk',
                    'kode_bumn' => '',
                    'sasaran_bumn' => $risiko->target_capaian_kinerja ?? '-',
                    'sasaran_kbumn' => '',
                    'kategori_risiko_bumn' => optional($risiko->kategoriRisiko)->title ?? '-',
                    'kategori_risiko_t2_t3' => $risiko->kategoriRisiko->title . ' - ' . $risiko->jenisRisiko->title,
                    'no_risiko' => $nomorUrutRisiko,
                    'peristiwa_risiko' => $risiko->peristiwa_risiko ?? '-',
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
                    'kategori_dampak' => optional($risiko->riskAnalysis)->kategori_dampak ?? '-',
                    'deskripsi_dampak' => $risiko->deskripsi_dampak ?? '-',
                    'perkiraan_waktu_terpapar' => $this->formatWaktuTerpapar($risiko),
                ];
                
                $exportData->push($rowData);
            } else {
                // Buat kombinasi penyebab risiko dengan KRI
                $isFirstRowOfGroup = true;
                
                if ($jumlahPenyebab === 0) {
                    // Jika tidak ada penyebab tapi ada KRI
                    foreach ($risiko->kris as $kri) {
                        $rowData = $this->createRowData($risiko, null, $kri, collect(), $isFirstRowOfGroup, $nomorUrutRisiko, 0, true);
                        $exportData->push($rowData);
                        $isFirstRowOfGroup = false;
                    }
                } elseif ($jumlahKRI === 0) {
                    // Jika ada penyebab tapi tidak ada KRI
                    $nomorUrutPenyebab = 1;
                    foreach ($risiko->penyebabRisiko as $penyebab) {
                        $rowData = $this->createRowData($risiko, $penyebab, null, collect(), $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab, true);
                        $exportData->push($rowData);
                        $isFirstRowOfGroup = false;
                        $nomorUrutPenyebab++;
                    }
                } else {
                    // Jika ada penyebab dan ada KRI - buat kombinasi
                    $nomorUrutPenyebab = 1;
                    foreach ($risiko->penyebabRisiko as $penyebab) {
                        $isFirstKRIOfPenyebab = true;
                        
                        foreach ($risiko->kris as $kri) {
                            $rowData = $this->createRowData($risiko, $penyebab, $kri, collect(), $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab, $isFirstKRIOfPenyebab);
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
    private function createRowData($risiko, $penyebab, $kri, $kontrolList, $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab, $isFirstKRIOfPenyebab = true)
    {
        $kategoriT2T3 = (optional($risiko->kategoriRisiko)->title ?? '') . ' - ' . (optional($risiko->jenisRisiko)->title ?? '-');

        return [
            'no' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
            'nama_bumn' => $isFirstRowOfGroup ? 'PT Wijaya Karya (Persero) Tbk' : '',
            'kode_bumn' => '',
            'sasaran_bumn' => $isFirstRowOfGroup ? ($risiko->target_capaian_kinerja ?? '-') : '',
            'sasaran_kbumn' => '',
            'kategori_risiko_bumn' => $isFirstRowOfGroup ? $kategoriT2T3 : '',
            'kategori_risiko_t2_t3' => $isFirstRowOfGroup ? ($risiko->jenisRisiko->title ?? '-') : '',
            'no_risiko' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
            'peristiwa_risiko' => $isFirstRowOfGroup ? ($risiko->peristiwa_risiko ?? '-') : '',
            'deskripsi_peristiwa_risiko' => $isFirstRowOfGroup ? ($risiko->deskripsi_peristiwa_risiko ?? '-') : '',
            'no_penyebab_risiko' => ($penyebab && $isFirstKRIOfPenyebab) ? $nomorUrutPenyebab : '',
            'kode_penyebab_risiko' => ($penyebab && $isFirstKRIOfPenyebab) ? ($nomorUrutRisiko . '.' . $nomorUrutPenyebab) : '',
            'penyebab_risiko' => ($penyebab && $isFirstKRIOfPenyebab) ? ($penyebab->penyebab_risiko ?? '-') : '',
            'key_risk_indicator' => $kri ? ($kri->kri ?? '-') : '-',
            'unit_satuan_kri' => $kri ? ($kri->satuan_kri ?? '-') : '-',
            'kategori_treshold_aman' => $kri ? ($kri->batas_aman ?? '-') : '-',
            'kategori_treshold_waspada' => $kri ? ($kri->batas_waspada ?? '-') : '-',
            'kategori_treshold_bahaya' => $kri ? ($kri->batas_bahaya ?? '-') : '-',
            'jenis_eksisting_kontrol' => ($penyebab && $isFirstKRIOfPenyebab) ? (optional($risiko->jenisKontrolEksisting)->jenis_kontrol ?? '-') : '',
            'kontrol_eksisting' => ($penyebab && $isFirstKRIOfPenyebab) ? ($risiko->kontrol_eksisting ?? '-') : '',
            'penilaian_efektivitas_kontrol' => ($penyebab && $isFirstKRIOfPenyebab) ? (optional($risiko->penilaianEfektifitasKontrol)->efektivitas_kontrol ?? '-') : '',
            'kategori_dampak' => $isFirstRowOfGroup ? (optional($risiko->riskAnalysis)->kategori_dampak ?? '-') : '',
            'deskripsi_dampak' => $isFirstRowOfGroup ? (optional($risiko->riskAnalysis)->deskripsi_dampak ?? '-') : '',
            'perkiraan_waktu_terpapar' => $isFirstRowOfGroup ? $this->formatWaktuTerpapar($risiko) : '',
        ];
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