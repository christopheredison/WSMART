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
    private $statusPublish;

    public function __construct(array $costCenters, $bulan, $tahun, $statusPublish = 'all')
    {
        $this->costCenters = $costCenters;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->statusPublish = $statusPublish;
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

        // Definisikan tanggal batas akhir bulan dari periode cutoff terpilih (jam 23:59:59)
        $cutoffDate = Carbon::create($this->tahun, $this->bulan, 1)->endOfMonth()->format('Y-m-d 23:59:59');

        $filterUpToPeriod = function ($q) {
            if ($this->bulan && $this->tahun) {
                // Kolom month bertipe teks, sehingga "<=" dibandingkan secara
                // leksikal ("2" > "10"). Pakai daftar bulan agar Oktober–Desember
                // tidak kehilangan data monitoring.
                $bulanSampai = range(1, (int) $this->bulan);
                $q->where(function($query) use ($bulanSampai) {
                    // Ambil tahun-tahun sebelumnya, ATAU tahun yang sama tapi bulan <= bulan terpilih
                    $query->where('tahun', '<', $this->tahun)
                          ->orWhere(function($subQuery) use ($bulanSampai) {
                              $subQuery->where('tahun', $this->tahun)
                                       ->whereIn('month', $bulanSampai);
                          });
                });
            }

            $this->applyMonitoringStatusFilter($q);
        };

        $query = ProjectRisk::with([
            'project',
            'sasaranProyek',
            'peristiwaRisiko',
            'penyebabRisikoProjects.perlakuanPenyebabRisiko.perlakuanPenyebabMonitorings' => function ($q) use ($filterUpToPeriod) {
                $q->whereHas('projectMonitoring', $filterUpToPeriod)
                  ->with('projectMonitoring');
            },
            'perlakuanDampakRisikos.perlakuanDampakMonitorings' => function ($q) use ($filterUpToPeriod) {
                $q->whereHas('projectMonitoring', $filterUpToPeriod)
                  ->with('projectMonitoring');
            },
            'dampakRisikoProjects',
            'projectRiskAnalisa.skalaDampakObj',
            'projectRiskAnalisa.skalaProbabilitas',
            'projectRiskMonitorings' => function ($q) use ($filterUpToPeriod) {
                $filterUpToPeriod($q);
                $q->orderBy('tahun', 'desc')->orderBy('month', 'desc')->orderBy('id', 'desc');
            },
            'kriProjects.kriProjectMonitorings' => function ($q) use ($filterUpToPeriod) {
                $q->whereHas('projectMonitoring', $filterUpToPeriod)
                  ->with('projectMonitoring')
                  ->orderBy('id', 'desc');
            }
        ]);

        $query->where('status', 6)->whereNull('deleted_at');

        // Project aktif pada periode export berdasarkan masa_pelaksanaan_end nya
        $hariIni = Carbon::today()->toDateString();
        $query->whereHas('project', function($q) use ($hariIni) {
            $q->whereNotNull('masa_pelaksanaan_end')
              ->whereDate('masa_pelaksanaan_end', '>=', $hariIni);
        });

        // =========================================================================
        // PERBAIKAN QUERY: Menyesuaikan penarikan data risiko dengan logika cutoff
        // =========================================================================
        $query->where(function ($queryScope) use ($cutoffDate) {
            $queryScope->where(function ($q1) use ($cutoffDate) {
                // Kondisi 1: is_closed = 0 && created_at <= periode cutoff
                $q1->where('is_closed', 0)
                   ->where('created_at', '<=', $cutoffDate);
            })->orWhere(function ($q2) use ($cutoffDate) {
                // Kondisi 2: is_closed = 1 && created_at <= periode cutoff && updated_at > periode cutoff
                $q2->where('is_closed', 1)
                   ->where('created_at', '<=', $cutoffDate)
                   ->where('updated_at', '>', $cutoffDate);
            });
        });

        if (!in_array('all', $this->costCenters)) {
            $query->whereIn('unit_id', $this->costCenters);
        }

        $risks = $query->get();

        $unitMapping = Unit::whereNotNull('cost_center')->pluck('name', 'cost_center');

        $data = new Collection();
        $no = 1;

        // Penyeragaman bulan monitoring bersifat per proyek, bukan satu bulan untuk seluruh laporan.
        // Contoh: Proyek B published terakhir lengkap di Juni → Juni; Proyek C lengkap di Agustus → Agustus.
        foreach ($risks->groupBy('project_id') as $projectRisks) {
            $snapshot = $this->resolveUniformMonitoringPeriod($projectRisks);

            if ($snapshot) {
                $snapshotEnd = $snapshot['end'];
                $projectRisks = $projectRisks->filter(function ($risk) use ($snapshotEnd) {
                    return $risk->created_at && Carbon::parse($risk->created_at)->lte($snapshotEnd);
                });
            }

            if ($projectRisks->isEmpty()) {
                continue;
            }

            [$rencanaBiayaTotal, $realisasiBiayaTotal] = $this->sumProjectBiaya($projectRisks, $snapshot);

            foreach ($projectRisks as $risk) {
            $project = $risk->project;
            if (!$project) continue;

            $namaDivisi = $unitMapping[$project->cost_center_parent] ?? '-';
            $meta = $project->meta ?? [];
            $profitCenter = $project->profit_center ?? ($meta['profit_center'] ?? null);

            $hasilUsaha = app(\App\Services\ProjectHasilUsahaSyncService::class)
                ->getLatestForProject($project, true);
            $lspValue = $hasilUsaha?->lsp_review ?? 0;

            $jenisKontrak = empty($meta['jenis_kontrak_name']) ? '-' : (is_array($meta['jenis_kontrak_name']) ? implode(', ', $meta['jenis_kontrak_name']) : $meta['jenis_kontrak_name']);
            $caraPembayaran = empty($meta['pembayaran_name']) ? '-' : (is_array($meta['pembayaran_name']) ? implode(', ', $meta['pembayaran_name']) : $meta['pembayaran_name']);

            $riskLimit = ($project->nk ?? 0) * 0.03;
            $peristiwaText = ($risk->peristiwa_risiko_id == 0) ? $risk->rencana_kegiatan : ($risk->peristiwaRisiko->title ?? '-');

            $statusKriList = [];
            foreach ($risk->kriProjects as $kri) {
                $lastMon = $this->childMonitoringForPeriod($kri->kriProjectMonitorings, $snapshot);
                $statusKri = '-';
                if ($lastMon) {
                    switch ((int)$lastMon->status_kri_terkini) {
                        case 1: $statusKri = 'Aman'; break;
                        case 2: $statusKri = 'Siaga'; break;
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

                    $lastMon = $this->childMonitoringForPeriod($plk->perlakuanPenyebabMonitorings, $snapshot);
                    $realisasiPerlakuanStr .= "Penyebab $noItem: " . ($lastMon?->deskripsi_perlakuan_risiko ?? '-') . "\n";
                    if($plk->pic) $picStr[] = "Penyebab $noItem: " . $plk->pic;

                    $biayaPenyebab += (float) ($plk->biaya_perlakuan_risiko ?? 0);
                    $realBiayaPenyebab += (float) ($lastMon?->realisasi_biaya_perlakuan_risiko ?? 0);
                }
            }

            foreach($risk->perlakuanDampakRisikos as $idx => $pd) {
                $noList = $idx + 1;
                $perlakuanDampakStr .= $noList . '. ' . $pd->rencana_perlakuan_risiko . "\n";

                $lastMon = $this->childMonitoringForPeriod($pd->perlakuanDampakMonitorings, $snapshot);
                $realisasiPerlakuanStr .= "Dampak $noList: " . ($lastMon?->deskripsi_perlakuan_risiko ?? '-') . "\n";
                if($pd->pic) $picStr[] = "Dampak $noList: " . $pd->pic;

                $biayaDampak += (float) ($pd->biaya_perlakuan_risiko ?? 0);
                $realBiayaDampak += (float) ($lastMon?->realisasi_biaya_perlakuan_risiko ?? 0);
            }

            $penyebabList = $risk->penyebabRisikoProjects->map(fn($item, $k) => ($k + 1) . '. ' . $item->penyebab_risiko)->implode("\n");
            $dampakList = $risk->dampakRisikoProjects->map(fn($item, $k) => ($k + 1) . '. ' . $item->dampak_risiko)->implode("\n");

            $analisa = $risk->projectRiskAnalisa;

            $lastMonitoring = $this->monitoringForPeriod($risk->projectRiskMonitorings, $snapshot);

            // Default realisasi = Inherent.
            // Ini dipakai ketika belum ada bulan monitoring yang seragam terpublish sampai periode cutoff.
            $realisasiDampak = $analisa->nilai_dampak ?? 0;
            $realisasiEksposur = $analisa->eksposur_risiko ?? 0;
            $levelResidualRealisasi = ($analisa->level_risiko ?? '-') . ' - ' . ($analisa->skala_risiko ?? 0);
            $periodeMonitoringText = 'Belum ada monitoring';

            if ($lastMonitoring) {
                $realisasiDampak = $lastMonitoring->nilai_dampak ?? 0;
                $realisasiEksposur = $lastMonitoring->eksposure_risiko ?? 0;

                if ($lastMonitoring->level_risiko) {
                    $levelResidualRealisasi = $lastMonitoring->level_risiko . ' - ' . ($lastMonitoring->skala_risiko ?? 0);
                }

                if ($lastMonitoring->month && $lastMonitoring->tahun) {
                    $bulanStr = $namaBulan[(int)$lastMonitoring->month] ?? $lastMonitoring->month;
                    $periodeMonitoringText = $bulanStr . ' ' . $lastMonitoring->tahun;
                }
            } else if ($snapshot) {
                $bulanStr = $namaBulan[(int) $snapshot['month']] ?? $snapshot['month'];
                $periodeMonitoringText = $bulanStr . ' ' . $snapshot['tahun'];
            }

            // =========================================================================
            // PERBAIKAN LOGIKA STATUS KONSOLIDASI: Penentuan status Open / Closed
            // =========================================================================
            $statusTeks = $risk->formatStatusRisikoForExport(
                $this->tahun ? (int) $this->tahun : null,
                $this->bulan ? (int) $this->bulan : null
            );

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

                // Old
                // trim($realisasiPerlakuanStr) ?: '-',
                // ($realBiayaPenyebab + $realBiayaDampak),
                // $lastMonitoring?->nilai_dampak ?? 0,
                // $lastMonitoring?->eksposure_risiko ?? 0,

                // $levelResidualRealisasi,

                // New
                trim($realisasiPerlakuanStr) ?: '-',
                ($realBiayaPenyebab + $realBiayaDampak),
                $realisasiDampak,
                $realisasiEksposur,

                $levelResidualRealisasi,

                $statusTeks, // Menggunakan variabel status hasil pengecekan cutoff dinamis
                ((float) $risk->efektivitas_perlakuan_risiko >= 0) ? 'Efektif' : 'Tidak Efektif',
                $periodeMonitoringText,
            ];

            $data->push($row);
            }
        }

        return $data;
    }

    /**
     * Filter status monitoring sesuai opsi export.
     * Published = status 100.
     */
    private function applyMonitoringStatusFilter($q): void
    {
        if ($this->statusPublish === 'unpublished') {
            $q->where(function ($sq) {
                $sq->where('status', '!=', 100)
                  ->where(function ($sub) {
                      $sub->where('is_approved', 0)->orWhere('is_approved', false)->orWhereNull('is_approved');
                  });
            });
            return;
        }

        if ($this->statusPublish === 'all') {
            return;
        }

        $q->where('status', 100);
    }

    private function monitoringMatchesFilter($monitoring): bool
    {
        if ($this->statusPublish === 'unpublished') {
            return (int) $monitoring->status !== 100
                && !($monitoring->is_approved == 1 || $monitoring->is_approved === true);
        }

        if ($this->statusPublish === 'all') {
            return true;
        }

        return (int) $monitoring->status === 100;
    }

    /**
     * Cari bulan monitoring seragam dari data risiko proyek itu sendiri.
     * Setiap proyek dievaluasi terpisah: ambil bulan terbaru (<= periode laporan)
     * di mana SEMUA risiko proyek yang sudah ada di bulan itu sudah terpublish (status 100).
     * Proyek lain tidak mempengaruhi hasil bulan ini.
     */
    private function resolveUniformMonitoringPeriod(Collection $projectRisks): ?array
    {
        if (!$this->bulan || !$this->tahun || $projectRisks->isEmpty()) {
            return null;
        }

        $cursor = Carbon::create((int) $this->tahun, (int) $this->bulan, 1)->startOfMonth();
        $earliestCreated = $projectRisks
            ->filter(fn ($risk) => !empty($risk->created_at))
            ->min('created_at');

        if (!$earliestCreated) {
            return null;
        }

        $earliest = Carbon::parse($earliestCreated)->startOfMonth();

        while ($cursor->gte($earliest)) {
            $month = (int) $cursor->month;
            $year = (int) $cursor->year;
            $endOfMonth = $cursor->copy()->endOfMonth();

            $existingRisks = $projectRisks->filter(function ($risk) use ($endOfMonth) {
                return $risk->created_at && Carbon::parse($risk->created_at)->lte($endOfMonth);
            });

            $allPublishedInMonth = $existingRisks->isNotEmpty()
                && $existingRisks->every(function ($risk) use ($month, $year) {
                    return $this->monitoringForMonth($risk->projectRiskMonitorings, $month, $year) !== null;
                });

            if ($allPublishedInMonth) {
                return [
                    'month' => $month,
                    'tahun' => $year,
                    'end' => $endOfMonth,
                ];
            }

            $cursor->subMonth();
        }

        return null;
    }

    private function monitoringForMonth(?Collection $monitorings, int $month, int $year)
    {
        if (!$monitorings || $monitorings->isEmpty()) {
            return null;
        }

        return $monitorings
            ->filter(function ($monitoring) use ($month, $year) {
                return (int) $monitoring->month === $month
                    && (int) $monitoring->tahun === $year
                    && $this->monitoringMatchesFilter($monitoring);
            })
            ->sortByDesc('id')
            ->first();
    }

    private function monitoringForPeriod(?Collection $monitorings, ?array $snapshot)
    {
        if (!$snapshot) {
            return null;
        }

        return $this->monitoringForMonth($monitorings, (int) $snapshot['month'], (int) $snapshot['tahun']);
    }

    private function childMonitoringForPeriod(?Collection $monitorings, ?array $snapshot)
    {
        if (!$snapshot || !$monitorings || $monitorings->isEmpty()) {
            return null;
        }

        return $monitorings
            ->filter(function ($monitoring) use ($snapshot) {
                $parent = $monitoring->projectMonitoring;
                if (!$parent) {
                    return false;
                }

                return (int) $parent->month === (int) $snapshot['month']
                    && (int) $parent->tahun === (int) $snapshot['tahun']
                    && $this->monitoringMatchesFilter($parent);
            })
            ->sortByDesc('id')
            ->first();
    }

    private function sumProjectBiaya(Collection $projectRisks, ?array $snapshot): array
    {
        $rencana = 0;
        $realisasi = 0;

        foreach ($projectRisks as $risk) {
            foreach ($risk->penyebabRisikoProjects as $penyebab) {
                foreach ($penyebab->perlakuanPenyebabRisiko as $perlakuan) {
                    $rencana += $perlakuan->biaya_perlakuan_risiko ?? 0;
                    $lastMon = $this->childMonitoringForPeriod($perlakuan->perlakuanPenyebabMonitorings, $snapshot);
                    $realisasi += $lastMon?->realisasi_biaya_perlakuan_risiko ?? 0;
                }
            }

            foreach ($risk->perlakuanDampakRisikos as $perlakuanDampak) {
                $rencana += $perlakuanDampak->biaya_perlakuan_risiko ?? 0;
                $lastMon = $this->childMonitoringForPeriod($perlakuanDampak->perlakuanDampakMonitorings, $snapshot);
                $realisasi += $lastMon?->realisasi_biaya_perlakuan_risiko ?? 0;
            }
        }

        return [$rencana, $realisasi];
    }
}
