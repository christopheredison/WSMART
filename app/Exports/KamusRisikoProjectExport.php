<?php

namespace App\Exports;

use App\Models\KamusRisikoProject;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KamusRisikoProjectExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $filters;
    private $rowNumber = 0;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = KamusRisikoProject::query()->with([
            'project',
            'projectRisk.peristiwaRisiko',
            'projectRisk.jenisRisiko.kategoriRisiko',
            'projectRisk.projectRiskAnalisa',
            'projectRisk.projectRiskMonitoring',
            'projectRisk.penyebabRisikoProjects.perlakuanPenyebabRisiko.perlakuanPenyebabMonitorings',
            'projectRisk.dampakRisikoProjects',
            'projectRisk.perlakuanDampakRisikos.perlakuanDampakMonitorings'
        ]);

        // Terapkan filter dari request
        if (!empty($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }
        if (!empty($this->filters['peristiwa_risiko_id'])) {
            $query->whereHas('projectRisk', fn($q) => $q->where('peristiwa_risiko_id', $this->filters['peristiwa_risiko_id']));
        }
        if (!empty($this->filters['jenis_risiko_id'])) {
            $query->whereHas('projectRisk', fn($q) => $q->where('jenis_risiko_id', $this->filters['jenis_risiko_id']));
        }
        if (!empty($this->filters['level_risiko'])) {
            $query->whereHas('projectRisk', fn($q) => $q->where('level_risiko', $this->filters['level_risiko']));
        }
        if (!empty($this->filters['deskripsi_risiko'])) {
            $query->whereHas('projectRisk', fn($q) => $q->where('deskripsi_peristiwa_risiko', 'like', '%' . $this->filters['deskripsi_risiko'] . '%'));
        }
        if (!empty($this->filters['efektivitas'])) {
            $query->whereHas('projectRisk', function ($q) {
                if ($this->filters['efektivitas'] == 'efektif') {
                    $q->where('efektivitas_perlakuan_risiko', '>', 0);
                } elseif ($this->filters['efektivitas'] == 'tidak_efektif') {
                    $q->where('efektivitas_perlakuan_risiko', '<=', 0);
                }
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Proyek',
            'Standarisasi Risiko',
            'Peristiwa Risiko',
            'Penyebab Risiko',
            'Dampak Risiko',
            'Dampak Risiko Kuantitatif Rupiah Inheren',
            'Perlakuan Risiko Rencana',
            'Biaya Perlakuan Risiko Rencana',
            'Dampak Risiko Kuantitatif Rupiah Rencana',
            'Perlakuan Risiko Realisasi',
            'Realisasi Biaya Perlakuan Risiko',
            'Dampak Risiko Kuantitatif Rupiah Realisasi',
            'Efektifitas Perlakuan Risiko',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $kategori = $row->projectRisk?->jenisRisiko?->kategoriRisiko?->title ?? 'N/A';
        $jenis = $row->projectRisk?->jenisRisiko?->title ?? 'N/A';

        // 1. Penyebab Risiko
        $penyebabArr = [];
        if ($row->projectRisk && $row->projectRisk->penyebabRisikoProjects) {
            foreach ($row->projectRisk->penyebabRisikoProjects as $idx => $item) {
                $penyebabArr[] = ($idx + 1) . '. ' . $item->penyebab_risiko;
            }
        }
        $penyebabStr = empty($penyebabArr) ? '-' : implode("\n", $penyebabArr);

        // 2. Dampak Risiko
        $dampakArr = [];
        if ($row->projectRisk && $row->projectRisk->dampakRisikoProjects) {
            foreach ($row->projectRisk->dampakRisikoProjects as $idx => $item) {
                $dampakArr[] = ($idx + 1) . '. ' . $item->dampak_risiko;
            }
        }
        $dampakStr = empty($dampakArr) ? '-' : implode("\n", $dampakArr);

        // 3. Perlakuan Rencana & Biaya Rencana
        $perlakuanRencanaPenyebab = [];
        $perlakuanRencanaDampak = [];
        $biayaRencanaPenyebab = 0;
        $biayaRencanaDampak = 0;

        if ($row->projectRisk && $row->projectRisk->penyebabRisikoProjects) {
            foreach ($row->projectRisk->penyebabRisikoProjects as $idxP => $p) {
                foreach ($p->perlakuanPenyebabRisiko as $idxPerl => $perl) {
                    $perlakuanRencanaPenyebab[] = ($idxP + 1) . '.' . ($idxPerl + 1) . ' ' . $perl->rencana_perlakuan_risiko;
                    $biayaRencanaPenyebab += $perl->biaya_perlakuan_risiko;
                }
            }
        }
        if ($row->projectRisk && $row->projectRisk->perlakuanDampakRisikos) {
            foreach ($row->projectRisk->perlakuanDampakRisikos as $idx => $pd) {
                $perlakuanRencanaDampak[] = ($idx + 1) . '. ' . $pd->rencana_perlakuan_risiko;
                $biayaRencanaDampak += $pd->biaya_perlakuan_risiko;
            }
        }

        $perlakuanRencanaStr = "Penyebab:\n" . (empty($perlakuanRencanaPenyebab) ? '-' : implode("\n", $perlakuanRencanaPenyebab)) . "\n\nDampak:\n" . (empty($perlakuanRencanaDampak) ? '-' : implode("\n", $perlakuanRencanaDampak));
        $biayaRencanaStr = "Penyebab: Rp " . number_format($biayaRencanaPenyebab, 0, ',', '.') . "\nDampak: Rp " . number_format($biayaRencanaDampak, 0, ',', '.');

        // 4. Perlakuan Realisasi & Biaya Realisasi
        $perlakuanRealisasiPenyebab = [];
        $perlakuanRealisasiDampak = [];
        $biayaRealisasiPenyebab = 0;
        $biayaRealisasiDampak = 0;

        if ($row->projectRisk && $row->projectRisk->penyebabRisikoProjects) {
            foreach ($row->projectRisk->penyebabRisikoProjects as $idxP => $p) {
                foreach ($p->perlakuanPenyebabRisiko as $idxPerl => $perl) {
                    $lastMon = $perl->perlakuanPenyebabMonitorings->sortByDesc('id')->first();
                    $realDesc = $lastMon->deskripsi_perlakuan_risiko ?? '-';
                    $perlakuanRealisasiPenyebab[] = ($idxP + 1) . '.' . ($idxPerl + 1) . ' ' . $realDesc;
                    $biayaRealisasiPenyebab += $lastMon->realisasi_biaya_perlakuan_risiko ?? 0;
                }
            }
        }
        if ($row->projectRisk && $row->projectRisk->perlakuanDampakRisikos) {
            foreach ($row->projectRisk->perlakuanDampakRisikos as $idx => $pd) {
                $lastMon = $pd->perlakuanDampakMonitorings->sortByDesc('id')->first();
                $realDesc = $lastMon->deskripsi_perlakuan_risiko ?? '-';
                $perlakuanRealisasiDampak[] = ($idx + 1) . '. ' . $realDesc;
                $biayaRealisasiDampak += $lastMon->realisasi_biaya_perlakuan_risiko ?? 0;
            }
        }

        $perlakuanRealisasiStr = "Penyebab:\n" . (empty($perlakuanRealisasiPenyebab) ? '-' : implode("\n", $perlakuanRealisasiPenyebab)) . "\n\nDampak:\n" . (empty($perlakuanRealisasiDampak) ? '-' : implode("\n", $perlakuanRealisasiDampak));
        $biayaRealisasiStr = "Penyebab: Rp " . number_format($biayaRealisasiPenyebab, 0, ',', '.') . "\nDampak: Rp " . number_format($biayaRealisasiDampak, 0, ',', '.');

        // Efektivitas
        $efektivitas = $row->projectRisk?->efektivitas_perlakuan_risiko;
        $efektivitasStr = is_null($efektivitas) ? '-' : $efektivitas . '%';

        return [
            $this->rowNumber,
            $row->project?->project_name ?? '-',
            $kategori . ' - ' . $jenis,
            $row->projectRisk?->peristiwaRisiko?->title ?? '-',
            $penyebabStr,
            $dampakStr,
            $row->projectRisk?->projectRiskAnalisa?->nilai_dampak ?? 0,
            $perlakuanRencanaStr,
            $biayaRencanaStr,
            $row->projectRisk?->projectRiskAnalisa?->nilai_dampak_residual ?? 0,
            $perlakuanRealisasiStr,
            $biayaRealisasiStr,
            $row->projectRisk?->projectRiskMonitoring?->nilai_dampak ?? 0,
            $efektivitasStr,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn(); // Ini akan menjadi 'N' (kolom ke 14)
                $cellRange = 'A1:' . $highestCol . $highestRow;

                // 1. Aplikasikan border ke semua sel dan set WrapText untuk baris yang ada \n
                $sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                ]);

                // 2. Styling Header (Bold, Center, Warna Latar)
                $sheet->getStyle('A1:' . $highestCol . '1')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                ]);

                // Fungsi untuk set background color hex
                $setColor = function($cols, $colorHex) use ($sheet) {
                    foreach ($cols as $col) {
                        $sheet->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($colorHex);
                    }
                };

                // Pengaturan Warna berdasarkan visual gambar (Selang-seling Hijau dan Kuning/Oranye)
                $colorGreen = '92D050'; // Hijau
                $colorYellow = 'FFC000'; // Kuning Oranye

                // Kolom Hijau
                $setColor(['A', 'B', 'C', 'D', 'E', 'H', 'K'], $colorGreen);
                // Kolom Kuning
                $setColor(['F', 'G', 'I', 'J', 'L', 'M', 'N'], $colorYellow);

                // Lebarkan manual beberapa kolom teks yang panjang supaya rapi
                $longColumns = ['E', 'F', 'H', 'I', 'K', 'L'];
                foreach ($longColumns as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth(45);
                }

                // Format currency (Rp) untuk kolom G, J, M (Nilai Dampak Inheren, Rencana, Realisasi)
                $currencyFormat = '_-"Rp"* #,##0_-;-"Rp"* #,##0_-;_-"Rp"* "-"_-;_-@_-';
                $sheet->getStyle('G2:G' . $highestRow)->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle('J2:J' . $highestRow)->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle('M2:M' . $highestRow)->getNumberFormat()->setFormatCode($currencyFormat);
            }
        ];
    }
}
