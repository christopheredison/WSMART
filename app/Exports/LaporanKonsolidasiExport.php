<?php

namespace App\Exports;

use App\Models\ProjectRisk;
use App\Models\ProjectHasilUsaha;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class LaporanKonsolidasiExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    private $costCenters;
    private $bulan;
    private $tahun;

    public function __construct(array $costCenters, $bulan, $tahun)
    {
        $this->costCenters = $costCenters;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function headings(): array
    {
        return [
            'NO.', 'Nama Proyek', 'Kode SAP', 'Divisi Operasi', 'Nilai OK Total',
            'Nilai OK Porsi', 'Laba Setelah Pajak', 'Biaya Perlakuan Risiko Sesuai RKP',
            'Rencana Biaya Perlakuan Risiko', 'Realisasi Biaya Perlakuan Risiko',
            'Tipe Kontrak', 'Cara Pembayaran', 'Batas Perlakuan Risiko', 'Nilai Batas Risiko',
            'Sasaran', 'Risiko (Deskripsi Peristiwa)', 'Peristiwa Risiko', 'Penyebab Risiko',
            'KRI', 'Penjelasan Dampak Risiko', 'Dampak Risiko Kuantitatif Rupiah Inheren',
            'Eksposur Risiko Inheren', 'Level Risiko Inheren',
            'Rencana Perlakuan Risiko Penyebab', 'Rencana Perlakuan Risiko Dampak',
            'Biaya Perlakuan Risiko Penyebab', 'Biaya Perlakuan Risiko Dampak',
            'Dampak Risiko Residual Rencana', 'Eksposur Risiko Residual Rencana', 'Level Residual Rencana',
            'Waktu Mulai', 'Waktu Selesai', 'Penanggung Jawab', 'Realisasi Perlakuan Risiko',
            'Realisasi Biaya Perlakuan Risiko', 'Dampak Risiko Realisasi',
            'Eksposur Risiko Residual Realisasi', 'Level Residual Realisasi',
            'Status', 'Efektivitas Perlakuan Risiko', 'Periode Realisasi Monitoring'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'AO';
                $highestRow = $sheet->getHighestRow();

                $headerStyle = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ];
                $sheet->getStyle('A1:'.$lastCol.'1')->applyFromArray($headerStyle);
                $sheet->getRowDimension(1)->setRowHeight(40);

                $setColor = function($range, $colorHex) use ($sheet) {
                    $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($colorHex);
                };

                $cWhite = 'FFFFFF';
                $cBlue = '9BC2E6';
                $cGreen = 'C6E0B4';
                $cOrange = 'F4B084';
                $cViolet = 'D9E1F2';
                $cGrey = 'D9D9D9';

                $setColor('A1:N1', $cWhite);

                $setColor('O1:P1', $cBlue);
                $setColor('Q1:T1', $cGreen);
                $setColor('U1', $cOrange);
                $setColor('V1:W1', $cViolet);
                $setColor('X1:Y1', $cGreen);
                $setColor('Z1:AA1', $cOrange);
                $setColor('AB1', $cOrange);
                $setColor('AC1:AD1', $cViolet);
                $setColor('AE1:AF1', $cGrey);
                $setColor('AG1:AH1', $cGreen);
                $setColor('AI1:AJ1', $cOrange);
                $setColor('AK1:AL1', $cViolet);
                $setColor('AM1', $cBlue);
                $setColor('AN1', $cViolet);
                $setColor('AO1', $cGrey);

                if ($highestRow > 1) {
                    $sheet->getStyle('A2:'.$lastCol.$highestRow)->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);
                }

                foreach (range('A', 'Z') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
                foreach (['AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN', 'AO'] as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

                $longCols = ['B', 'P', 'R', 'S', 'T', 'X', 'Y', 'AH', 'AG'];
                foreach ($longCols as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth(40);
                }
            }
        ];
    }

    public function collection()
    {
        // Pindahkan array bulan ke dalam function agar tidak terjadi error undefined variable
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // 1. Buat Closure: "Ambil monitoring sampai dengan bulan & tahun yang dipilih dengan status Publish"
        $filterUpToPeriod = function($q) {
            if ($this->bulan && $this->tahun) {
                $q->where(function($query) {
                    // Ambil tahun-tahun sebelumnya, ATAU tahun yang sama tapi bulan <= bulan terpilih
                    $query->where('tahun', '<', $this->tahun)
                          ->orWhere(function($subQuery) {
                              $subQuery->where('tahun', $this->tahun)
                                       ->where('month', '<=', $this->bulan);
                          });
                });
            }
            // Pastikan status publish / disetujui
            $q->where(function($sq) {
                $sq->where('status', 100)->orWhere('is_approved', 1)->orWhere('is_approved', true);
            });
        };

        // 2. Terapkan closure ke dalam eager loading
        $query = ProjectRisk::with([
            'project',
            'sasaranProyek',
            'peristiwaRisiko',
            'penyebabRisikoProjects.perlakuanPenyebabRisiko.perlakuanPenyebabMonitorings' => function($q) use ($filterUpToPeriod) {
                $q->whereHas('projectMonitoring', $filterUpToPeriod);
            },
            'perlakuanDampakRisikos.perlakuanDampakMonitorings' => function($q) use ($filterUpToPeriod) {
                $q->whereHas('projectMonitoring', $filterUpToPeriod);
            },
            'dampakRisikoProjects',
            'projectRiskAnalisa.skalaDampakObj',
            'projectRiskAnalisa.skalaProbabilitas',
            'projectRiskMonitorings' => function($q) use ($filterUpToPeriod) {
                $filterUpToPeriod($q);
                // Wajib diurutkan ke yang paling baru agar saat fungsi ->first() dipanggil, yang ditarik adalah bulan terakhir/latest.
                $q->orderBy('tahun', 'desc')->orderBy('month', 'desc')->orderBy('id', 'desc');
            },
            'kriProjects.kriProjectMonitorings' => function($q) use ($filterUpToPeriod) {
                $q->whereHas('projectMonitoring', $filterUpToPeriod)->orderBy('id', 'desc');
            }
        ]);

        // 3. Menampilkan yang memiliki laporan publish sampai batas bulan.
        $query->whereHas('projectRiskMonitorings', $filterUpToPeriod);

        $query->whereHas('project', function($q) {
            $q->whereDate('masa_pelaksanaan_end', '>=', \Carbon\Carbon::today())
              ->orWhereNull('masa_pelaksanaan_end');
        });

        if (!in_array('all', $this->costCenters)) {
            $query->whereIn('unit_id', $this->costCenters);
        }

        $risks = $query->get();

        $unitMapping = Unit::whereNotNull('cost_center')->pluck('name', 'cost_center');

        $data = new Collection();
        $no = 1;

        foreach ($risks as $risk) {
            $project = $risk->project;
            if (!$project) continue;

            $namaDivisi = $unitMapping[$project->cost_center_parent] ?? '-';
            $meta = $project->meta ?? [];
            $profitCenter = $project->profit_center ?? ($meta['profit_center'] ?? null);

            $hasilUsaha = ProjectHasilUsaha::where('profit_center', $profitCenter)->orderBy('period', 'desc')->first();
            $lspValue = $hasilUsaha ? $hasilUsaha->lsp_review : 0;

            $jenisKontrak = empty($meta['jenis_kontrak_name']) ? '-' : (is_array($meta['jenis_kontrak_name']) ? implode(', ', $meta['jenis_kontrak_name']) : $meta['jenis_kontrak_name']);
            $caraPembayaran = empty($meta['pembayaran_name']) ? '-' : (is_array($meta['pembayaran_name']) ? implode(', ', $meta['pembayaran_name']) : $meta['pembayaran_name']);

            $rencanaBiayaTotal = 0;
            $realisasiBiayaTotal = 0;

            foreach ($project->projectRisks ?? [$risk] as $r) {
                foreach ($r->penyebabRisikoProjects as $p) {
                    foreach ($p->perlakuanPenyebabRisiko as $plk) {
                        $rencanaBiayaTotal += $plk->biaya_perlakuan_risiko ?? 0;
                        // Nullsafe operator ?-> ditambahkan agar jika kosong tidak mengembalikan Exception Error
                        $realisasiBiayaTotal += $plk->perlakuanPenyebabMonitorings->sortByDesc('id')->first()?->realisasi_biaya_perlakuan_risiko ?? 0;
                    }
                }
                foreach ($r->perlakuanDampakRisikos as $pld) {
                    $rencanaBiayaTotal += $pld->biaya_perlakuan_risiko ?? 0;
                    $realisasiBiayaTotal += $pld->perlakuanDampakMonitorings->sortByDesc('id')->first()?->realisasi_biaya_perlakuan_risiko ?? 0;
                }
            }

            $riskLimit = ($project->nk ?? 0) * 0.03;
            $peristiwaText = ($risk->peristiwa_risiko_id == 0) ? $risk->rencana_kegiatan : ($risk->peristiwaRisiko->title ?? '-');

            $statusKriList = [];
            foreach ($risk->kriProjects as $kri) {
                $lastMon = $kri->kriProjectMonitorings->sortByDesc('id')->first();
                $statusKri = '-';
                if ($lastMon) {
                    switch ((int)$lastMon->status_kri_terkini) {
                        case 1: $statusKri = 'Aman'; break;
                        case 2: $statusKri = 'Waspada'; break;
                        case 3: $statusKri = 'Bahaya'; break;
                    }
                }
                $statusKriList[] = $statusKri;
            }
            $kriFinal = implode("\n", $statusKriList) ?: '-';

            $perlakuanPenyebabStr = '';
            $perlakuanDampakStr = '';
            $realisasiPerlakuanStr = '';
            $picStr = [];

            $biayaPenyebab = 0;
            $realBiayaPenyebab = 0;
            $biayaDampak = 0;
            $realBiayaDampak = 0;

            foreach($risk->penyebabRisikoProjects as $idx => $penyebab) {
                foreach($penyebab->perlakuanPenyebabRisiko as $i => $plk) {
                    $noItem = ($idx+1).'.'.($i+1);
                    $perlakuanPenyebabStr .= $noItem . ' ' . $plk->rencana_perlakuan_risiko . "\n";

                    $lastMon = $plk->perlakuanPenyebabMonitorings->sortByDesc('id')->first();
                    $realisasiPerlakuanStr .= "Penyebab $noItem: " . ($lastMon?->deskripsi_perlakuan_risiko ?? '-') . "\n";
                    if($plk->pic) $picStr[] = "Penyebab $noItem: " . $plk->pic;

                    $biayaPenyebab += (float) ($plk->biaya_perlakuan_risiko ?? 0);
                    $realBiayaPenyebab += (float) ($lastMon?->realisasi_biaya_perlakuan_risiko ?? 0);
                }
            }

            foreach($risk->perlakuanDampakRisikos as $idx => $pd) {
                $noList = $idx + 1;
                $perlakuanDampakStr .= $noList . '. ' . $pd->rencana_perlakuan_risiko . "\n";

                $lastMon = $pd->perlakuanDampakMonitorings->sortByDesc('id')->first();
                $realisasiPerlakuanStr .= "Dampak $noList: " . ($lastMon?->deskripsi_perlakuan_risiko ?? '-') . "\n";
                if($pd->pic) $picStr[] = "Dampak $noList: " . $pd->pic;

                $biayaDampak += (float) ($pd->biaya_perlakuan_risiko ?? 0);
                $realBiayaDampak += (float) ($lastMon?->realisasi_biaya_perlakuan_risiko ?? 0);
            }

            $penyebabList = $risk->penyebabRisikoProjects->map(fn($item, $k) => ($k + 1) . '. ' . $item->penyebab_risiko)->implode("\n");
            $dampakList = $risk->dampakRisikoProjects->map(fn($item, $k) => ($k + 1) . '. ' . $item->dampak_risiko)->implode("\n");

            $analisa = $risk->projectRiskAnalisa;

            // Memanggil relasi monitoring, akan otomats mengambil 'first' / terbaru karena sudah di-ORDER BY 'desc' di Eager Load
            $lastMonitoring = $risk->projectRiskMonitorings->first();

            $levelResidualRealisasi = '-';
            $periodeMonitoringText = '-';

            if ($lastMonitoring) {
                if ($lastMonitoring->level_risiko) {
                    $levelResidualRealisasi = $lastMonitoring->level_risiko . ' - ' . ($lastMonitoring->skala_risiko ?? 0);
                }

                // Set text "Bulan Tahun" (Contoh: "Februari 2024")
                if ($lastMonitoring->month && $lastMonitoring->tahun) {
                    $bulanStr = $namaBulan[(int)$lastMonitoring->month] ?? $lastMonitoring->month;
                    $periodeMonitoringText = $bulanStr . ' ' . $lastMonitoring->tahun;
                }
            }

            $row = [
                $no++,
                $project->project_name ?? '-',
                $profitCenter ?? '-',
                $namaDivisi,
                $project->nk ?? 0,
                $project->nilai_ok_porsi ?? 0,
                $lspValue,

                $project->biaya_perlakuan_risiko_rkp ? (float) $project->biaya_perlakuan_risiko_rkp : 0,
                $rencanaBiayaTotal ? (float) $rencanaBiayaTotal : 0,
                $realisasiBiayaTotal ? (float) $realisasiBiayaTotal : 0,

                $jenisKontrak,
                $caraPembayaran,
                $project->batasan_biaya_perlakuan_risiko ?? 0,
                $riskLimit,

                $risk->sasaranProyek->kpi_desc ?? $risk->target_capaian_kinerja ?? '-',
                $risk->deskripsi_peristiwa_risiko ?? '-',
                $peristiwaText,
                $penyebabList ?: '-',

                trim($kriFinal),

                $dampakList ?: '-',
                $analisa->nilai_dampak ?? 0,
                $analisa->eksposur_risiko ?? 0,
                ($analisa->level_risiko ?? '-') . ' - ' . ($analisa->skala_risiko ?? 0),

                trim($perlakuanPenyebabStr) ?: '-',
                trim($perlakuanDampakStr) ?: '-',
                $biayaPenyebab,
                $biayaDampak,
                $analisa->nilai_dampak_residual ?? 0,
                $analisa->eksposur_risiko_residual ?? 0,
                ($analisa->level_risiko_residual ?? '-') . ' - ' . ($analisa->skala_risiko_residual ?? 0),

                $risk->perkiraan_waktu_terpapar_risiko_mulai ? Carbon::parse($risk->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y') : '-',
                $risk->perkiraan_waktu_terpapar_risiko_akhir ? Carbon::parse($risk->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y') : '-',
                implode("\n", $picStr) ?: '-',

                trim($realisasiPerlakuanStr) ?: '-',
                ($realBiayaPenyebab + $realBiayaDampak),
                $lastMonitoring?->nilai_dampak ?? 0,
                $lastMonitoring?->eksposure_risiko ?? 0,

                $levelResidualRealisasi,

                $risk->is_closed ? 'Closed' : 'Open',
                ((float) $risk->efektivitas_perlakuan_risiko >= 0) ? 'Efektif' : 'Tidak Efektif',
                $periodeMonitoringText,
            ];

            $data->push($row);
        }

        return $data;
    }
}
