<?php

namespace App\Exports\Sheets\Unit;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RealisasiResidualSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    private $risikos;

    public function __construct(Collection $risikos)
    {
        $this->risikos = $risikos;
    }

    public function title(): string
    {
        return 'Realisasi Residual';
    }

    public function headings(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        $risikosCount = $this->risikos->count();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($risikosCount) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'Jenis Data');
                $sheet->setCellValue('B1', 'No');
                $sheet->setCellValue('C1', 'Nama BUMN');
                $sheet->setCellValue('D1', 'No Risiko');
                $sheet->setCellValue('E1', 'Peristiwa Risiko');
                $sheet->setCellValue('F1', 'Realisasi Risiko Residual');

                $sheet->setCellValue('AL1', 'Nilai Efektivitas');
                $sheet->setCellValue('AM1', 'Efektifitas Perlakuan Risiko');

                $sheet->setCellValue('F2', 'Asumsi Perhitungan Dampak');
                $sheet->setCellValue('J2', 'Nilai Dampak');
                $sheet->setCellValue('N2', 'Skala Dampak BUMN');
                $sheet->setCellValue('R2', 'Nilai Probabilitas');
                $sheet->setCellValue('V2', 'Skala Probabilitas BUMN');
                $sheet->setCellValue('Z2', 'Eksposur Risiko');
                $sheet->setCellValue('AD2', 'Skala Risiko BUMN');
                $sheet->setCellValue('AH2', 'Level Risiko BUMN');

                $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
                $startColumns = ['F', 'J', 'N', 'R', 'V', 'Z', 'AD', 'AH'];

                foreach ($startColumns as $startCol) {
                    $colIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startCol);
                    foreach ($quarters as $index => $quarter) {
                        $currentCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + $index);
                        $sheet->setCellValue($currentCol . '3', $quarter);
                    }
                }

                $mergeColumns = ['A', 'B', 'C', 'D', 'E'];
                foreach ($mergeColumns as $col) {
                    $sheet->mergeCells("{$col}1:{$col}3");
                }

                $sheet->mergeCells('F1:AK1');
                $sheet->mergeCells('AL1:AL3');
                $sheet->mergeCells('AM1:AM3');

                $sheet->mergeCells('F2:I2');
                $sheet->mergeCells('J2:M2');
                $sheet->mergeCells('N2:Q2');
                $sheet->mergeCells('R2:U2');
                $sheet->mergeCells('V2:Y2');
                $sheet->mergeCells('Z2:AC2');
                $sheet->mergeCells('AD2:AG2');
                $sheet->mergeCells('AH2:AK2');

                $headerStyle = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '9BC2E6']],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
                ];
                $sheet->getStyle('A1:AM3')->applyFromArray($headerStyle);

                $sheet->getStyle('F2:AK2')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBDBDB']]
                ]);

                $sheet->getStyle('F3:AK3')->applyFromArray([
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']]
                ]);

                if ($risikosCount > 0) {
                    $maxDataRow = 3 + $risikosCount;
                    $sheet->getStyle('A4:AM' . $maxDataRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                        'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP, 'wrapText' => true]
                    ]);

                    $centerCols = ['A', 'B', 'D', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM'];
                    foreach ($centerCols as $col) {
                        $sheet->getStyle("{$col}4:{$col}{$maxDataRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    }

                    $this->applyLevelRisikoColoring($sheet, $maxDataRow);
                }

                foreach (range('A', 'AM') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function applyLevelRisikoColoring($sheet, $maxRow)
    {
        $levelRisikoColumns = ['AH', 'AI', 'AJ', 'AK'];
        for ($row = 4; $row <= $maxRow; $row++) {
            foreach ($levelRisikoColumns as $column) {
                $cellValue = $sheet->getCell($column . $row)->getValue();
                $backgroundColor = $this->getLevelRisikoBackgroundColor($cellValue);
                if ($backgroundColor) {
                    $sheet->getStyle($column . $row)->applyFromArray([
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $backgroundColor]]
                    ]);
                }
            }
        }
    }

    private function getLevelRisikoBackgroundColor($levelRisiko)
    {
        if (!$levelRisiko || $levelRisiko === '-') return null;
        switch (strtolower(trim($levelRisiko))) {
            case 'low': return '92D050';
            case 'low to moderate': return 'C6E0B4';
            case 'moderate': return 'FFFF00';
            case 'moderate to high': return 'FFC000';
            case 'high': return 'FF0000';
            default: return null;
        }
    }

    public function collection()
    {
        $exportData = new Collection();
        $nomorUrut = 1;

        foreach ($this->risikos as $risiko) {
            $analisa = $risiko->riskAnalysis;
            if (!$analisa) continue;

            $jenisData = ucfirst(strtolower($analisa->kategori_dampak ?? 'Kuantitatif'));

            $rowData = [
                'jenis_data' => $jenisData,
                'no' => $nomorUrut,
                'nama_bumn' => 'PT Wijaya Karya (Persero) Tbk',
                'no_risiko' => $nomorUrut,
                'peristiwa_risiko' => $risiko->peristiwa_risiko ?? '-',

                'asumsi_perhitungan_dampak_q1' => $this->getAsumsiPerhitunganDampak($analisa, 'q1'),
                'asumsi_perhitungan_dampak_q2' => $this->getAsumsiPerhitunganDampak($analisa, 'q2'),
                'asumsi_perhitungan_dampak_q3' => $this->getAsumsiPerhitunganDampak($analisa, 'q3'),
                'asumsi_perhitungan_dampak_q4' => $this->getAsumsiPerhitunganDampak($analisa, 'q4'),

                'nilai_dampak_q1' => $this->getNilaiDampak($analisa, 'q1'),
                'nilai_dampak_q2' => $this->getNilaiDampak($analisa, 'q2'),
                'nilai_dampak_q3' => $this->getNilaiDampak($analisa, 'q3'),
                'nilai_dampak_q4' => $this->getNilaiDampak($analisa, 'q4'),

                'skala_dampak_q1' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q1, $analisa->skalaDampakResidualQ1Obj),
                'skala_dampak_q2' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q2, $analisa->skalaDampakResidualQ2Obj),
                'skala_dampak_q3' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q3, $analisa->skalaDampakResidualQ3Obj),
                'skala_dampak_q4' => $this->formatSkalaDampak($analisa->skala_dampak_residual_q4, $analisa->skalaDampakResidualQ4Obj),

                'nilai_probabilitas_q1' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q1 ?? 0),
                'nilai_probabilitas_q2' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q2 ?? 0),
                'nilai_probabilitas_q3' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q3 ?? 0),
                'nilai_probabilitas_q4' => $this->formatPercentage($analisa->nilai_probabilitas_residual_q4 ?? 0),

                'skala_probabilitas_q1' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ1),
                'skala_probabilitas_q2' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ2),
                'skala_probabilitas_q3' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ3),
                'skala_probabilitas_q4' => $this->formatSkalaProbabilitas($analisa->skalaProbabilitasResidualQ4),

                'eksposur_risiko_q1' => $this->formatCurrency($analisa->eksposur_risiko_residual_q1 ?? 0),
                'eksposur_risiko_q2' => $this->formatCurrency($analisa->eksposur_risiko_residual_q2 ?? 0),
                'eksposur_risiko_q3' => $this->formatCurrency($analisa->eksposur_risiko_residual_q3 ?? 0),
                'eksposur_risiko_q4' => $this->formatCurrency($analisa->eksposur_risiko_residual_q4 ?? 0),

                'skala_risiko_q1' => $analisa->skala_risiko_residual_q1 ?? '-',
                'skala_risiko_q2' => $analisa->skala_risiko_residual_q2 ?? '-',
                'skala_risiko_q3' => $analisa->skala_risiko_residual_q3 ?? '-',
                'skala_risiko_q4' => $analisa->skala_risiko_residual_q4 ?? '-',

                'level_risiko_q1' => $analisa->level_risiko_residual_q1 ?? '-',
                'level_risiko_q2' => $analisa->level_risiko_residual_q2 ?? '-',
                'level_risiko_q3' => $analisa->level_risiko_residual_q3 ?? '-',
                'level_risiko_q4' => $analisa->level_risiko_residual_q4 ?? '-',

                'nilai_efektivitas' => $risiko->efektivitas_perlakuan_risiko ? $risiko->efektivitas_perlakuan_risiko . '%' : '-',
                'efektifitas_perlakuan' => $this->calculateEfektifitas($risiko),
            ];

            $exportData->push($rowData);
            $nomorUrut++;
        }

        return $exportData;
    }

    private function getAsumsiPerhitunganDampak($analisa, $quarter) { return $analisa->kategori_dampak === 'Kualitatif' ? 'Sesuai dengan data di Risiko Inherent Kualitatif' : ($analisa->{'asumsi_perhitungan_dampak_residual_' . $quarter} ?? '-'); }
    private function getNilaiDampak($analisa, $quarter) { return $analisa->kategori_dampak === 'Kualitatif' ? 'Rp0' : $this->formatCurrency($analisa->{'nilai_dampak_residual_' . $quarter} ?? 0); }

    private function calculateEfektifitas($risiko) {
        return ((float) $risiko->efektivitas_perlakuan_risiko >= 0) ? 'Efektif' : 'Tidak Efektif';
    }

    private function formatCurrency($value) { return $value == 0 ? 'Rp0' : 'Rp' . number_format($value, 0, ',', '.'); }
    private function formatPercentage($value) { return $value . '%'; }
    private function formatSkalaDampak($skala, $obj) { return $skala ? ($obj ? $skala . ' - ' . $obj->deskripsi : $skala) : '-'; }
    private function formatSkalaProbabilitas($obj) { return $obj ? ($obj->skala ? $obj->tingkat . ' - ' . $obj->skala : $obj->tingkat) : '-'; }
}
