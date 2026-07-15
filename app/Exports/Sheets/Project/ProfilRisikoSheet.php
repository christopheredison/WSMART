<?php

namespace App\Exports\Sheets\Project;

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

    // Sekarang menggunakan Collection $risikos
    public function __construct(Collection $risikos)
    {
        $this->risikos = $risikos;
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

                // Row 1: Header utama
                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Nama BUMN');
                $sheet->setCellValue('C1', 'Kode BUMN');
                $sheet->setCellValue('D1', 'Nama Project');
                $sheet->setCellValue('E1', 'Sasaran BUMN');
                $sheet->setCellValue('F1', 'Sasaran KBUMN');
                $sheet->setCellValue('G1', 'Kategori Risiko BUMN');
                $sheet->setCellValue('H1', 'Kategori Risiko T2 & T3 KBUMN');
                $sheet->setCellValue('I1', 'No Risiko');
                $sheet->setCellValue('J1', 'Peristiwa Risiko');
                $sheet->setCellValue('K1', 'Deskripsi Peristiwa Risiko');

                $sheet->setCellValue('L1', 'No Penyebab Risiko');
                $sheet->setCellValue('M1', 'Kode Penyebab Risiko');
                $sheet->setCellValue('N1', 'Penyebab Risiko');

                $sheet->setCellValue('O1', 'Key Risk Indicator');
                $sheet->setCellValue('P1', 'Unit Satuan KRI');

                $sheet->setCellValue('Q1', 'Kategori Treshold KRI');

                $sheet->setCellValue('T1', 'Jenis Eksisting Kontrol');
                $sheet->setCellValue('U1', 'Kontrol Eksisting');
                $sheet->setCellValue('V1', 'Penilaian Efektivitas Kontrol');
                $sheet->setCellValue('W1', 'Kategori Dampak');

                // PERUBAHAN: Dampak Risiko
                $sheet->setCellValue('X1', 'No Dampak Risiko');
                $sheet->setCellValue('Y1', 'Kode Dampak Risiko');
                $sheet->setCellValue('Z1', 'Dampak Risiko');

                $sheet->setCellValue('AA1', 'Perkiraan Waktu Terpapar Risiko');
                // TAMBAHAN: Status Risiko
                $sheet->setCellValue('AB1', 'Status Risiko');

                // Row 2: Sub-header untuk Kategori Treshold KRI
                $sheet->setCellValue('Q2', 'Aman');
                $sheet->setCellValue('R2', 'Waspada');
                $sheet->setCellValue('S2', 'Bahaya');

                // Merge sel header vertikal (tanpa sub-header)
                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Merge Kategori Treshold KRI
                $sheet->mergeCells('Q1:S1');

                // Style untuk header utama
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
                $sheet->getStyle('A1:AB2')->applyFromArray($headerStyle);

                // Warna Treshold KRI
                $sheet->getStyle('Q2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
                $sheet->getStyle('R2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
                $sheet->getStyle('S2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');

                $lastRow = $sheet->getHighestRow();

                if ($lastRow > 2) {
                    $sheet->getStyle('A3:AB' . $lastRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                        'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);

                    // Format Text untuk Kode Penyebab (M) & Kode Dampak (Y)
                    $sheet->getStyle('M3:M' . $lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                    $sheet->getStyle('Y3:Y' . $lastRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                    // Rata tengah
                    $centerCols = ['A', 'C', 'I', 'L', 'M', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Y', 'AA', 'AB'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}3:{$col}{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }

                    $this->applyThresholdColoring($sheet, $lastRow);
                }

                foreach (range('A', 'Z') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                $sheet->getColumnDimension('AA')->setAutoSize(true);
                $sheet->getColumnDimension('AB')->setAutoSize(true);
            },
        ];
    }

    private function applyThresholdColoring($sheet, $maxRow)
    {
        for ($row = 3; $row <= $maxRow; $row++) {
            foreach (['Q' => '92D050', 'R' => 'FFFF00', 'S' => 'FF0000'] as $col => $color) {
                $val = $sheet->getCell($col . $row)->getValue();
                if ($val && $val !== '-') {
                    $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($color);
                }
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
            $dampakList = $risiko->dampakRisikos ?? collect();

            $maxRows = max($penyebabList->count(), $kriList->count(), $dampakList->count(), 1);

            for ($i = 0; $i < $maxRows; $i++) {
                $p = $penyebabList->get($i);
                $k = $kriList->get($i);
                $d = $dampakList->get($i);

                $exportData->push($this->createRowData($risiko, $p, $k, $d, $i == 0, $nomorUrutRisiko, $i + 1));
            }
            $nomorUrutRisiko++;
        }

        return $exportData;
    }

    private function createRowData($risiko, $p, $k, $d, $isFirstRowOfGroup, $noRisiko, $subNo)
    {
        $kategoriT2T3 = (optional($risiko->kategoriRisiko)->title ?? '') . ' - ' . (optional($risiko->jenisRisiko)->title ?? '-');

        $statusRisiko = '-';
        if ($isFirstRowOfGroup) {
            $statusRisiko = $risiko->is_closed ? 'Closed' : 'Open';
        }

        return [
            'no' => $isFirstRowOfGroup ? $noRisiko : '',
            'nama_bumn' => $isFirstRowOfGroup ? 'PT Wijaya Karya (Persero) Tbk' : '',
            'kode_bumn' => $isFirstRowOfGroup ? '-' : '',
            'nama_project' => $isFirstRowOfGroup ? (optional(optional($risiko->projectPeriodeList)->project)->project_name ?? '-') : '',
            'sasaran_bumn' => $isFirstRowOfGroup ? ($risiko->target_capaian_kinerja ?? '-') : '',
            'sasaran_kbumn' => $isFirstRowOfGroup ? '-' : '',

            'kategori_risiko_bumn' => $isFirstRowOfGroup ? (optional($risiko->kategoriRisiko)->title ?? '-') : '',
            'kategori_risiko_t2_t3' => $isFirstRowOfGroup ? $kategoriT2T3 : '',

            'no_risiko' => $isFirstRowOfGroup ? $noRisiko : '',
            'peristiwa_risiko' => $isFirstRowOfGroup ? ($risiko->peristiwa_risiko ?? '-') : '',
            'deskripsi_peristiwa_risiko' => $isFirstRowOfGroup ? ($risiko->deskripsi_peristiwa_risiko ?? '-') : '',

            'no_penyebab_risiko' => $p ? $noRisiko : '',
            'kode_penyebab_risiko' => $p ? "'" . $noRisiko . '.' . $subNo : '',
            'penyebab_risiko' => $p->penyebab_risiko ?? '',

            'key_risk_indicator' => $k->kri ?? '',
            'unit_satuan_kri' => $k->satuan_kri ?? '',
            'kategori_treshold_aman' => $k->batas_aman ?? '',
            'kategori_treshold_waspada' => $k->batas_waspada ?? '',
            'kategori_treshold_bahaya' => $k->batas_bahaya ?? '',

            'jenis_eksisting_kontrol' => $isFirstRowOfGroup ? (optional($risiko->jenisKontrolEksisting)->jenis_kontrol ?? '-') : '',
            'kontrol_eksisting' => $isFirstRowOfGroup ? ($risiko->kontrol_eksisting ?? '-') : '',
            'penilaian_efektivitas_kontrol' => $isFirstRowOfGroup ? (optional($risiko->penilaianEfektifitasKontrol)->efektivitas_kontrol ?? '-') : '',
            'kategori_dampak' => $isFirstRowOfGroup ? (optional($risiko->riskAnalysis)->kategori_dampak ?? '-') : '',

            // Kolom Dampak Baru
            'no_dampak' => $d ? $noRisiko : '',
            'kode_dampak' => $d ? "'" . $noRisiko . '.' . $subNo : '',
            'dampak' => $d->dampak_risiko ?? '',

            'perkiraan_waktu_terpapar' => $isFirstRowOfGroup ? $this->formatWaktuTerpapar($risiko) : '',
            'status_risiko' => $statusRisiko,
        ];
    }

    private function formatWaktuTerpapar($risiko)
    {
        $awal = $risiko->perkiraan_waktu_terpapar_risiko_mulai;
        $akhir = $risiko->perkiraan_waktu_terpapar_risiko_akhir;

        if ($awal && $akhir) {
            return Carbon::parse($awal)->format('j F Y') . ' - ' . Carbon::parse($akhir)->format('j F Y');
        }
        return $awal ? Carbon::parse($awal)->format('j F Y') : ($akhir ? Carbon::parse($akhir)->format('j F Y') : '-');
    }
}
