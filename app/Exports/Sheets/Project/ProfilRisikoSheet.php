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
    private $projectIds;

    public function __construct(array $projectIds)
    {
        $this->projectIds = $projectIds;
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
                $sheet->setCellValue('D1', 'Sasaran KBUMN');
                $sheet->setCellValue('E1', 'Nama Project');
                $sheet->setCellValue('F1', 'Sasaran Risiko');
                $sheet->setCellValue('G1', 'No Risiko');
                $sheet->setCellValue('H1', 'Peristiwa Risiko');
                $sheet->setCellValue('I1', 'Deskripsi Peristiwa Risiko');
                $sheet->setCellValue('J1', 'WBS');
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

                // --- KOLOM BARU DAMPAK ---
                $sheet->setCellValue('W1', 'No Dampak Risiko');
                $sheet->setCellValue('X1', 'Kode Dampak Risiko');
                $sheet->setCellValue('Y1', 'Dampak Risiko');

                $sheet->setCellValue('Z1', 'Perkiraan Waktu Terpapar Risiko');
                $sheet->setCellValue('AA1', 'Status Risiko');

                // Row 2: Sub-header untuk Kategori Treshold KRI
                $sheet->setCellValue('P2', 'Aman');
                $sheet->setCellValue('Q2', 'Waspada');
                $sheet->setCellValue('R2', 'Bahaya');

                // Merge sel header (Sampai AA)
                $mergeColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                $sheet->mergeCells('P1:R1');

                // Style Header (Update range ke AA)
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
                $sheet->getStyle('A1:AA2')->applyFromArray($headerStyle);

                // Warna Treshold
                $sheet->getStyle('P2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('92D050');
                $sheet->getStyle('Q2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
                $sheet->getStyle('R2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF0000');

                $highestRow = $sheet->getHighestRow();
                if ($highestRow > 2) {
                    $sheet->getStyle('A3:AA' . $highestRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                        'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);
                    $sheet->getStyle('L3:L' . $highestRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                    $sheet->getStyle('X3:X' . $highestRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                    $this->applyThresholdColoring($sheet, $highestRow);
                }

                foreach (range('A', 'Z') as $column) { $sheet->getColumnDimension($column)->setAutoSize(true); }
                $sheet->getColumnDimension('AA')->setAutoSize(true);
            },
        ];
    }

    private function applyThresholdColoring($sheet, $maxRow) {
        for ($row = 3; $row <= $maxRow; $row++) {
            foreach (['P' => '92D050', 'Q' => 'FFFF00', 'R' => 'FF0000'] as $col => $color) {
                $val = $sheet->getCell($col . $row)->getValue();
                if ($val && $val !== '-') {
                    $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($color);
                }
            }
        }
    }

    public function collection()
    {
        $risikos = ProjectRisk::with([
            'wbsMaster', 'projectPeriodeList.project', 'projectRiskAnalisa',
            'penyebabRisikoProjects', 'kriProjects', 'dampakRisikoProjects',
            'jenisKontrolEksisting', 'projectKontrolEksistings', 'penilaianEfektivitasKontrolObj',
        ])->whereIn('project_id', $this->projectIds)->get()->sortByDesc('projectRiskAnalisa.skala_risiko');

        $exportData = new Collection();
        $noRisiko = 1;

        foreach ($risikos as $risiko) {
            $penyebab = $risiko->penyebabRisikoProjects;
            $kris = $risiko->kriProjects;
            $dampaks = $risiko->dampakRisikoProjects;

            $maxRows = max($penyebab->count(), $kris->count(), $dampaks->count(), 1);

            for ($i = 0; $i < $maxRows; $i++) {
                $p = $penyebab->get($i);
                $k = $kris->get($i);
                $d = $dampaks->get($i);

                $exportData->push($this->createRowData($risiko, $p, $k, $d, $i == 0, $noRisiko, $i + 1));
            }
            $noRisiko++;
        }
        return $exportData;
    }

    private function createRowData($risiko, $p, $k, $d, $isFirst, $noRisiko, $subNo)
    {
        return [
            'no' => $isFirst ? $noRisiko : '',
            'nama_bumn' => $isFirst ? 'PT Wijaya Karya (Persero) Tbk' : '',
            'kode_bumn' => $isFirst ? '-' : '',
            'sasaran_kbumn' => $isFirst ? '-' : '',
            'nama_project' => $isFirst ? ($risiko->projectPeriodeList->project->project_name ?? '-') : '',
            'sasaran_risiko' => $isFirst ? ($risiko->target_capaian_kinerja ?? '-') : '',
            'no_risiko' => $isFirst ? $noRisiko : '',
            'peristiwa' => $isFirst ? ($risiko->peristiwaRisiko->title ?? $risiko->deskripsi_peristiwa_risiko ?? '-') : '',
            'desc_peristiwa' => $isFirst ? ($risiko->deskripsi_peristiwa_risiko ?? '-') : '',
            'wbs' => $isFirst ? ($risiko->wbsMaster->name ?? '-') : '',
            'no_penyebab' => $p ? $noRisiko : '',
            'kode_penyebab' => $p ? "'" . $noRisiko . '.' . $subNo : '',
            'penyebab' => $p->penyebab_risiko ?? '',
            'kri' => $k->kri ?? '',
            'satuan_kri' => $k->satuan_kri ?? '',
            'aman' => $k->batas_aman ?? '',
            'waspada' => $k->batas_waspada ?? '',
            'bahaya' => $k->batas_bahaya ?? '',
            'jenis_kontrol' => $isFirst ? (optional($risiko->jenisKontrolEksisting)->jenis_kontrol ?? '-') : '',
            'kontrol' => $isFirst ? $this->getKontrolEksisting($risiko) : '',
            'efektivitas' => $isFirst ? (optional($risiko->penilaianEfektivitasKontrolObj)->efektivitas_kontrol ?? '-') : '',
            'kat_dampak' => $isFirst ? ($risiko->projectRiskAnalisa->kategori_dampak ?? '-') : '',

            // --- DATA DAMPAK ---
            'no_dampak' => $d ? $noRisiko : '',
            'kode_dampak' => $d ? "'" . $noRisiko . '.' . $subNo : '',
            'dampak' => $d->dampak_risiko ?? '',

            'waktu' => $isFirst ? $this->formatWaktuTerpapar($risiko) : '',
            'status' => $isFirst ? ($risiko->is_closed ? 'Closed' : 'Open') : '',
        ];
    }

    private function getKontrolEksisting($risiko) {
        return $risiko->projectKontrolEksistings->isNotEmpty() ? $risiko->projectKontrolEksistings->pluck('kontrol_eksisting_desc')->implode('; ') : '-';
    }

    private function formatWaktuTerpapar($risiko) {
        $awal = $risiko->perkiraan_waktu_terpapar_risiko_mulai;
        $akhir = $risiko->perkiraan_waktu_terpapar_risiko_akhir;
        if ($awal && $akhir) return Carbon::parse($awal)->format('j F Y') . ' - ' . Carbon::parse($akhir)->format('j F Y');
        return $awal ? Carbon::parse($awal)->format('j F Y') : ($akhir ? Carbon::parse($akhir)->format('j F Y') : '-');
    }
}
