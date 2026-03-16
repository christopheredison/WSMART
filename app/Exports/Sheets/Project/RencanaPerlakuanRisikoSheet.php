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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class RencanaPerlakuanRisikoSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    private $risikos;
    private $timelineData = [];
    private $opsiPerlakuan = [];
    private $startYear;
    private $endYear;
    private $totalMonths;

    public function __construct(Collection $risikos)
    {
        $this->risikos = $risikos;
        $this->opsiPerlakuan = \App\Models\OpsiPerlakuanRisiko::pluck('opsi_perlakuan_risiko', 'id')->toArray();
        $this->calculateYearRange();
    }

    private function calculateYearRange()
    {
        $dates = collect();

        foreach ($this->risikos as $risk) {
            foreach ($risk->penyebabRisikoProjects as $p) {
                foreach ($p->perlakuanPenyebabRisiko as $per) {
                    if ($per->timeline_perlakuan_risiko_start) $dates->push(Carbon::parse($per->timeline_perlakuan_risiko_start)->year);
                    if ($per->timeline_perlakuan_risiko_end) $dates->push(Carbon::parse($per->timeline_perlakuan_risiko_end)->year);
                }
            }
            foreach ($risk->perlakuanDampakRisikos as $perD) {
                if ($perD->timeline_perlakuan_risiko_start) $dates->push(Carbon::parse($perD->timeline_perlakuan_risiko_start)->year);
                if ($perD->timeline_perlakuan_risiko_end) $dates->push(Carbon::parse($perD->timeline_perlakuan_risiko_end)->year);
            }
        }

        $this->startYear = $dates->min() ?? date('Y');
        $this->endYear = $dates->max() ?? date('Y');
        $this->totalMonths = (($this->endYear - $this->startYear) + 1) * 12;
    }

    public function title(): string { return 'Rencana Perlakuan Risiko'; }

    public function headings(): array { return []; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 2);

                // Kolom Statis A - K
                $headers = [
                    'A' => 'No', 'B' => 'Nama Project', 'C' => 'No Risiko', 'D' => 'Tipe Perlakuan',
                    'E' => 'Objek Perlakuan', 'F' => 'Opsi Perlakuan Risiko',
                    'G' => 'Rencana Perlakuan Risiko', 'H' => 'Output Perlakuan Risiko',
                    'I' => 'Biaya Perlakuan Risiko', 'J' => 'Progress Perlakuan Risiko', 'K' => 'PIC'
                ];

                foreach ($headers as $col => $val) {
                    $sheet->setCellValue($col . '1', $val);
                    $sheet->mergeCells("{$col}1:{$col}2");
                }

                // Header Dinamis Tahun & Bulan mulai dari kolom L (index 12)
                $currentColNum = 12;
                for ($y = $this->startYear; $y <= $this->endYear; $y++) {
                    $startColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColNum);
                    $endColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColNum + 11);

                    // Set Tahun
                    $sheet->setCellValue($startColLetter . '1', 'Tahun ' . $y);
                    $sheet->mergeCells($startColLetter . '1:' . $endColLetter . '1');

                    // Set Bulan 1-12
                    for ($m = 1; $m <= 12; $m++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColNum);
                        $sheet->setCellValue($colLetter . '2', $m);
                        $currentColNum++;
                    }
                }

                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColNum - 1);

                // Styling
                $style = [
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '9BC2E6']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                ];
                $sheet->getStyle('A1:' . $lastColLetter . '2')->applyFromArray($style);

                $lastRow = $sheet->getHighestRow();
                if ($lastRow > 2) {
                    $sheet->getStyle('A3:' . $lastColLetter . $lastRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true]
                    ]);
                    $this->applyTimelineColoring($sheet, $lastRow);
                }
            },
        ];
    }

    private function applyTimelineColoring($sheet, $maxRow) {
        foreach ($this->timelineData as $rowIndex => $activeMonths) {
            $row = $rowIndex + 3;
            foreach ($activeMonths as $mIdx) {
                // mIdx adalah index bulan dari startYear (0 = Jan thn pertama)
                // Kolom dimulai dari L (index 12)
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(12 + $mIdx);
                $sheet->getStyle($colLetter . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('5B9BD5');
            }
        }
    }

    public function collection()
    {
        $exportData = new Collection();
        $noUrut = 1;
        $currentRow = 0;

        foreach ($this->risikos as $risiko) {
            $projectName = $risiko->projectPeriodeList->project->project_name ?? '-';

            // 1. Perlakuan terhadap Penyebab
            foreach ($risiko->penyebabRisikoProjects as $penyebab) {
                foreach ($penyebab->perlakuanPenyebabRisiko as $perlakuan) {
                    $exportData->push($this->mapPerlakuan($noUrut, $projectName, $risiko, 'Penyebab', $penyebab->penyebab_risiko, $perlakuan, $currentRow));
                    $currentRow++;
                }
            }

            // 2. Perlakuan terhadap Dampak
            foreach ($risiko->perlakuanDampakRisikos as $perlakuanDampak) {
                $exportData->push($this->mapPerlakuan($noUrut, $projectName, $risiko, 'Dampak', '-', $perlakuanDampak, $currentRow));
                $currentRow++;
            }

            $noUrut++;
        }
        return $exportData;
    }

    private function mapPerlakuan($no, $project, $risiko, $tipe, $objek, $perlakuan, $rowIdx)
    {
        $activeMonthIndexes = [];
        $start = $perlakuan->timeline_perlakuan_risiko_start;
        $end = $perlakuan->timeline_perlakuan_risiko_end;

        if ($start && $end) {
            $startDate = Carbon::parse($start);
            $endDate = Carbon::parse($end);

            // Hitung index bulan relatif terhadap startYear
            $startMonthIdx = (($startDate->year - $this->startYear) * 12) + ($startDate->month - 1);
            $endMonthIdx = (($endDate->year - $this->startYear) * 12) + ($endDate->month - 1);

            for ($i = $startMonthIdx; $i <= $endMonthIdx; $i++) {
                if ($i >= 0 && $i < $this->totalMonths) {
                    $activeMonthIndexes[] = $i;
                }
            }
        }
        $this->timelineData[$rowIdx] = $activeMonthIndexes;

        $data = [
            'no' => $no,
            'project' => $project,
            'no_risiko' => $no,
            'tipe' => $tipe,
            'objek' => $objek,
            'opsi' => $this->opsiPerlakuan[$perlakuan->opsi_perlakuan_risiko] ?? '-',
            'rencana' => $perlakuan->rencana_perlakuan_risiko ?? '-',
            'output' => $perlakuan->output_perlakuan_risiko ?? '-',
            'biaya' => $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : '-',
            'progress' => ($perlakuan->lastMonitoring->progress_rencana_perlakuan_risiko ?? 0) . '%',
            'pic' => $perlakuan->pic ?? '-',
        ];

        // Tambahkan placeholder kosong sebanyak total bulan agar border excel terbentuk
        for ($i = 0; $i < $this->totalMonths; $i++) {
            $data['month_' . $i] = '';
        }

        return $data;
    }
}
