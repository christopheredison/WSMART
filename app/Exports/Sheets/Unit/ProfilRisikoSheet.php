<?php

namespace App\Exports\Sheets\Unit;

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
    private $risikos;
    private $bulan;

    public function __construct(Collection $risikos, $bulan = null)
    {
        $this->risikos = $risikos;
        $this->bulan = $bulan;
    }

    public function title(): string
    {
        return 'Profil Risiko';
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
                $sheet->insertNewRowBefore(1, 2);

                // Mapping nama bulan
                $namaBulanList = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $teksBulan = $this->bulan ? ' (' . $namaBulanList[(int)$this->bulan] . ')' : '';

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
                
                // Nilai Aktual dan Status KRI berdiri sendiri (tidak di bawah Treshold KRI)
                $sheet->setCellValue('S1', 'Nilai Aktual' . $teksBulan); 
                $sheet->setCellValue('T1', 'Status KRI');

                $sheet->setCellValue('U1', 'Jenis Eksisting Kontrol');
                $sheet->setCellValue('V1', 'Kontrol Eksisting');
                $sheet->setCellValue('W1', 'Penilaian Efektivitas Kontrol');
                $sheet->setCellValue('X1', 'Kategori Dampak');
                $sheet->setCellValue('Y1', 'Deskripsi Dampak');
                $sheet->setCellValue('Z1', 'Perkiraan Waktu Terpapar Risiko');
                $sheet->setCellValue('AA1', 'Status Risiko');

                // Row 2: Sub-header HANYA untuk Kategori Treshold KRI
                $sheet->setCellValue('P2', 'Aman');
                $sheet->setCellValue('Q2', 'Waspada');
                $sheet->setCellValue('R2', 'Bahaya');

                // Merge sel header vertikal untuk kolom yang tidak punya sub-header 
                // (Termasuk S dan T karena sekarang berdiri sendiri)
                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge sel header "Kategori Treshold KRI" secara horizontal hanya P1 sampai R1
                $sheet->mergeCells('P1:R1');

                // Atur style untuk header utama (Row 1 & 2)
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
                $sheet->getStyle('A1:AA2')->applyFromArray($headerStyle);

                // Style khusus untuk sub-header Kategori Threshold KRI
                $sheet->getStyle('P2')->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '92D050']]]);
                $sheet->getStyle('Q2')->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]]);
                $sheet->getStyle('R2')->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF0000']]]);

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
                    $dataRange = 'A3:AA' . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);

                    $sheet->getStyle('L3:L' . $lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                    $centerCols = ['A', 'C', 'H', 'K', 'L', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'W', 'X', 'AA'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}3:{$col}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }

                    $this->applyThresholdColoring($sheet, $lastRow);
                }

                // Auto size column
                foreach (range('A', 'Z') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('AA')->setAutoSize(true);
            },
        ];
    }

    private function applyThresholdColoring($sheet, $maxRow)
    {
        for ($row = 3; $row <= $maxRow; $row++) {
            // Aman
            $amanValue = $sheet->getCell('P' . $row)->getValue();
            if ($amanValue !== null && $amanValue !== '-') {
                $sheet->getStyle('P' . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '92D050']]]);
            }
            // Waspada
            $waspadaValue = $sheet->getCell('Q' . $row)->getValue();
            if ($waspadaValue !== null && $waspadaValue !== '-') {
                $sheet->getStyle('Q' . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]]);
            }
            // Bahaya
            $bahayaValue = $sheet->getCell('R' . $row)->getValue();
            if ($bahayaValue !== null && $bahayaValue !== '-') {
                $sheet->getStyle('R' . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF0000']]]);
            }
            // Status KRI Terkini (Kolom T)
            $statusKriValue = $sheet->getCell('T' . $row)->getValue();
            if ($statusKriValue === 'Aman') {
                $sheet->getStyle('T' . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '92D050']]]);
            } elseif ($statusKriValue === 'Waspada') {
                $sheet->getStyle('T' . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]]);
            } elseif ($statusKriValue === 'Bahaya') {
                $sheet->getStyle('T' . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF0000']]]);
            }
        }
    }

    public function collection()
    {
        $exportData = new Collection();
        $nomorUrutRisiko = 1;

        foreach ($this->risikos as $risiko) {
            $penyebabList = $risiko->penyebabRisiko ?? collect();
            $kriList = $risiko->kris ?? collect();

            $jumlahPenyebab = $penyebabList->count();
            $jumlahKRI = $kriList->count();

            if ($jumlahPenyebab === 0 && $jumlahKRI === 0) {
                $exportData->push($this->createRowData($risiko, null, null, collect(), true, $nomorUrutRisiko, 0, true));
            } else {
                $isFirstRowOfGroup = true;

                if ($jumlahPenyebab === 0) {
                    foreach ($kriList as $kri) {
                        $exportData->push($this->createRowData($risiko, null, $kri, collect(), $isFirstRowOfGroup, $nomorUrutRisiko, 0, true));
                        $isFirstRowOfGroup = false;
                    }
                } elseif ($jumlahKRI === 0) {
                    $nomorUrutPenyebab = 1;
                    foreach ($penyebabList as $penyebab) {
                        $exportData->push($this->createRowData($risiko, $penyebab, null, collect(), $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab, true));
                        $isFirstRowOfGroup = false;
                        $nomorUrutPenyebab++;
                    }
                } else {
                    $nomorUrutPenyebab = 1;
                    foreach ($penyebabList as $penyebab) {
                        $isFirstKRIOfPenyebab = true;
                        foreach ($kriList as $kri) {
                            $exportData->push($this->createRowData($risiko, $penyebab, $kri, collect(), $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab, $isFirstKRIOfPenyebab));
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

    private function createRowData($risiko, $penyebab, $kri, $kontrolList, $isFirstRowOfGroup, $nomorUrutRisiko, $nomorUrutPenyebab, $isFirstKRIOfPenyebab = true)
    {
        $kategoriT2T3 = (optional($risiko->kategoriRisiko)->title ?? '') . ' - ' . (optional($risiko->jenisRisiko)->title ?? '-');
        $statusRisiko = isset($risiko->is_closed) ? ($risiko->is_closed ? 'Closed' : 'Open') : 'Open';

        // Penarikan Data Monitoring KRI Terakhir
        $kriMonitoring = $kri ? $kri->kriUnitMonitorings->first() : null;
        $nilaiAktual = $kriMonitoring ? $kriMonitoring->nilai_kri_terkini : '-';
        
        $statusKriText = '-';
        if ($kriMonitoring && $kriMonitoring->status_kri_terkini) {
            switch ($kriMonitoring->status_kri_terkini) {
                case 1: $statusKriText = 'Aman'; break;
                case 2: $statusKriText = 'Waspada'; break;
                case 3: $statusKriText = 'Bahaya'; break;
            }
        }

        return [
            'no' => $isFirstRowOfGroup ? $nomorUrutRisiko : '',
            'nama_bumn' => $isFirstRowOfGroup ? 'PT Wijaya Karya (Persero) Tbk' : '',
            'kode_bumn' => '',
            'sasaran_bumn' => $isFirstRowOfGroup ? ($risiko->target_capaian_kinerja ?? '-') : '',
            'sasaran_kbumn' => '',
            'kategori_risiko_bumn' => $isFirstRowOfGroup ? $kategoriT2T3 : '',
            'kategori_risiko_t2_t3' => $isFirstRowOfGroup ? (optional($risiko->jenisRisiko)->title ?? '-') : '',
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
            'nilai_aktual' => $kri ? $nilaiAktual : '-',
            'status_kri' => $kri ? $statusKriText : '-',
            'jenis_eksisting_kontrol' => ($penyebab && $isFirstKRIOfPenyebab) ? (optional($risiko->jenisKontrolEksisting)->jenis_kontrol ?? '-') : '',
            'kontrol_eksisting' => ($penyebab && $isFirstKRIOfPenyebab) ? ($risiko->kontrol_eksisting ?? '-') : '',
            'penilaian_efektivitas_kontrol' => ($penyebab && $isFirstKRIOfPenyebab) ? (optional($risiko->penilaianEfektifitasKontrol)->efektivitas_kontrol ?? '-') : '',
            'kategori_dampak' => $isFirstRowOfGroup ? (optional($risiko->riskAnalysis)->kategori_dampak ?? '-') : '',
            'deskripsi_dampak' => $isFirstRowOfGroup ? (optional($risiko->riskAnalysis)->deskripsi_dampak ?? '-') : '',
            'perkiraan_waktu_terpapar' => $isFirstRowOfGroup ? $this->formatWaktuTerpapar($risiko) : '',
            'status_risiko' => $isFirstRowOfGroup ? $statusRisiko : '',
        ];
    }

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
