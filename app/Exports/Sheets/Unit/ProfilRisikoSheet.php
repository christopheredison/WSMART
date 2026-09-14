<?php

namespace App\Exports\Sheets\Unit;

use App\Exports\Sheets\Unit\Concerns\SupportsUnitColumnExport;
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
    use SupportsUnitColumnExport;

    private $risikos;
    private $bulan;
    private $tahun;

    public function __construct(Collection $risikos, $bulan = null, bool $includeUnitColumn = false, string $unitColumnLabel = 'Nama Divisi')
    {
        $this->risikos = $risikos;
        $this->bulan = $bulan;
        $this->includeUnitColumn = $includeUnitColumn;
        $this->unitColumnLabel = $unitColumnLabel;

        // Tahun periode dipakai bersama $bulan untuk menilai status Open/Closed
        // pada bulan laporan, sama seperti badge di halaman monitoring.
        $this->tahun = optional(optional($risikos->first())->periode)->tahun;
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
                $c = fn (string $col) => $this->c($col);
                $sheet->insertNewRowBefore(1, 2);

                $namaBulanList = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $teksBulan = $this->bulan ? ' (' . $namaBulanList[(int)$this->bulan] . ')' : '';

                $this->setUnitColumnHeader($sheet, 2);

                $sheet->setCellValue($c('A') . '1', 'No');
                $sheet->setCellValue($c('B') . '1', 'Nama BUMN');
                $sheet->setCellValue($c('C') . '1', 'Kode BUMN');
                $sheet->setCellValue($c('D') . '1', 'Sasaran BUMN');
                $sheet->setCellValue($c('E') . '1', 'Sasaran KBUMN');
                $sheet->setCellValue($c('F') . '1', 'Kategori Risiko BUMN');
                $sheet->setCellValue($c('G') . '1', 'Kategori Risiko T2 & T3 KBUMN');
                $sheet->setCellValue($c('H') . '1', 'No Risiko');
                $sheet->setCellValue($c('I') . '1', 'Peristiwa Risiko');
                $sheet->setCellValue($c('J') . '1', 'Deskripsi Peristiwa Risiko');
                $sheet->setCellValue($c('K') . '1', 'No Penyebab Risiko');
                $sheet->setCellValue($c('L') . '1', 'Kode Penyebab Risiko');
                $sheet->setCellValue($c('M') . '1', 'Penyebab Risiko');
                $sheet->setCellValue($c('N') . '1', 'Key Risk Indicator');
                $sheet->setCellValue($c('O') . '1', 'Unit Satuan KRI');
                $sheet->setCellValue($c('P') . '1', 'Kategori Treshold KRI');

                $sheet->setCellValue($c('S') . '1', 'Nilai Aktual' . $teksBulan);
                $sheet->setCellValue($c('T') . '1', 'Status KRI');

                $sheet->setCellValue($c('U') . '1', 'Jenis Eksisting Kontrol');
                $sheet->setCellValue($c('V') . '1', 'Kontrol Eksisting');
                $sheet->setCellValue($c('W') . '1', 'Penilaian Efektivitas Kontrol');
                $sheet->setCellValue($c('X') . '1', 'Kategori Dampak');
                $sheet->setCellValue($c('Y') . '1', 'Deskripsi Dampak');
                $sheet->setCellValue($c('Z') . '1', 'Perkiraan Waktu Terpapar Risiko');
                $sheet->setCellValue($c('AA') . '1', 'Status Risiko');

                $sheet->setCellValue($c('P') . '2', 'Aman');
                $sheet->setCellValue($c('Q') . '2', 'Siaga');
                $sheet->setCellValue($c('R') . '2', 'Bahaya');

                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$c($col)}1:{$c($col)}2");
                }

                $sheet->mergeCells($c('P') . '1:' . $c('R') . '1');

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
                $sheet->getStyle('A1:' . $c('AA') . '2')->applyFromArray($headerStyle);

                $sheet->getStyle($c('P') . '2')->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '92D050']]]);
                $sheet->getStyle($c('Q') . '2')->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]]);
                $sheet->getStyle($c('R') . '2')->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF0000']]]);

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
                    $dataRange = 'A3:' . $c('AA') . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray($dataStyle);

                    $sheet->getStyle($c('L') . '3:' . $c('L') . $lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                    $centerCols = ['A', 'C', 'H', 'K', 'L', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'W', 'X', 'AA'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$c($col)}3:{$c($col)}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }

                    $this->applyThresholdColoring($sheet, $lastRow);
                }

                foreach (range('A', 'Z') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('AA')->setAutoSize(true);
                if ($this->includeUnitColumn) {
                    $sheet->getColumnDimension($c('AA'))->setAutoSize(true);
                }
            },
        ];
    }

    private function applyThresholdColoring($sheet, $maxRow)
    {
        for ($row = 3; $row <= $maxRow; $row++) {
            $amanValue = $sheet->getCell($this->c('P') . $row)->getValue();
            if ($amanValue !== null && $amanValue !== '-') {
                $sheet->getStyle($this->c('P') . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '92D050']]]);
            }

            $waspadaValue = $sheet->getCell($this->c('Q') . $row)->getValue();
            if ($waspadaValue !== null && $waspadaValue !== '-') {
                $sheet->getStyle($this->c('Q') . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]]);
            }

            $bahayaValue = $sheet->getCell($this->c('R') . $row)->getValue();
            if ($bahayaValue !== null && $bahayaValue !== '-') {
                $sheet->getStyle($this->c('R') . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF0000']]]);
            }

            $statusKriValue = $sheet->getCell($this->c('T') . $row)->getValue();
            if ($statusKriValue === 'Aman') {
                $sheet->getStyle($this->c('T') . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '92D050']]]);
            } elseif ($statusKriValue === 'Siaga' || $statusKriValue === 'Waspada') {
                $sheet->getStyle($this->c('T') . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]]);
            } elseif ($statusKriValue === 'Bahaya') {
                $sheet->getStyle($this->c('T') . $row)->applyFromArray(['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF0000']]]);
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
        $statusRisiko = $risiko->formatStatusRisikoForExport(
            $this->tahun ? (int) $this->tahun : null,
            $this->bulan ? (int) $this->bulan : null
        );

        $kriMonitoring = $kri ? $kri->kriUnitMonitorings->first() : null;
        $nilaiAktual = $kriMonitoring ? $kriMonitoring->nilai_kri_terkini : '-';

        $statusKriText = '-';
        if ($kriMonitoring && $kriMonitoring->status_kri_terkini) {
            switch ($kriMonitoring->status_kri_terkini) {
                case 1: $statusKriText = 'Aman'; break;
                case 2: $statusKriText = 'Siaga'; break;
                case 3: $statusKriText = 'Bahaya'; break;
            }
        }

        $row = [
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

        return $this->prependUnit($row, $risiko, $isFirstRowOfGroup);
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
