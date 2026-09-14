<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IdentifikasiRisiko;
use App\Models\Unit;
use App\Models\Periode;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\PeristiwaRisiko;
use App\Models\DataBatch;
use App\Models\Tck;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\JenisKontrolEksisting;
use App\Models\PenilaianEfektivitasKontrol;
use App\Models\SkalaProbabilitas;
use App\Models\RiskMap;
use App\Models\AreaDampak;
use App\Models\RiskLimitPeriode;
use App\Models\PerlakuanPenyebabRisikoUnit;
use App\Models\Jabatan;
use App\Models\MasterKRI;
use App\Models\KontrolEksisting;
use App\Models\TaksonomiRisiko;
use App\Models\PerlakuanDampakRisikoUnit;
use App\Models\DataBatchNotes;
use App\Models\RiskNote;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CorporateRiskController extends Controller
{
    // public function index(Request $request)
    // {
    //     // Ambil periode_id dari parameter URL
    //     $periodeId = $request->query('pid');
    //     $unitId = $request->query('unit_id');

    //     // Jika tidak ada parameter periode, gunakan periode aktif
    //     if (!$periodeId) {
    //         $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
    //         $periodeId = $activePeriode ? $activePeriode->id : null;
    //     }

    //     // Ambil data periode yang dipilih
    //     $selectedPeriode = Periode::find($periodeId);

    //     // Query untuk risiko utama (main)
    //     $risikoMainQuery = IdentifikasiRisiko::with([
    //         'unit',
    //         'user',
    //         'periode',
    //         'kategoriRisiko',
    //         'jenisRisiko',
    //         'peristiwaRisiko',
    //         'riskAnalysis',
    //     ])
    //     ->whereIn('status_risiko', [
    //         IdentifikasiRisiko::STATUS_RISIKO_MAIN,
    //         IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION
    //     ]);

    //     // Query untuk risiko corporate
    //     $risikoCorporateQuery = IdentifikasiRisiko::with([
    //         'unit',
    //         'user',
    //         'periode',
    //         'kategoriRisiko',
    //         'jenisRisiko',
    //         'peristiwaRisiko',
    //         'riskAnalysis',
    //     ])
    //     ->where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_CORPORATE);

    //     // Filter berdasarkan periode jika ada
    //     if ($periodeId) {
    //         $risikoMainQuery->where('periode_id', $periodeId);
    //         $risikoCorporateQuery->where('periode_id', $periodeId);
    //     }

    //     // Filter berdasarkan unit jika ada
    //     if ($unitId) {
    //         $risikoMainQuery->where('unit_id', $unitId);
    //         $risikoCorporateQuery->where('unit_id', $unitId);
    //     }

    //     // Ambil data risiko
    //     $risikoMain = $risikoMainQuery->get();
    //     $risikoCorporate = $risikoCorporateQuery->get();

    //     // Urutkan risiko berdasarkan status, skala risiko, dan eksposur risiko
    //     $risikoMain = $risikoMain->sortBy(function($risk) {
    //         // Prioritaskan risiko rekomendasi korporat
    //         $statusPriority = $risk->status_risiko == IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION ? 0 : 1;

    //         // Kemudian urutkan berdasarkan skala risiko (nilai lebih tinggi lebih dulu)
    //         $skalaRisiko = -1 * ($risk->riskAnalysis->skala_risiko ?? 0);

    //         // Jika skala risiko sama, urutkan berdasarkan eksposur risiko (nilai lebih tinggi lebih dulu)
    //         $eksposurRisiko = -1 * ($risk->riskAnalysis->eksposur_risiko ?? 0);

    //         return [$statusPriority, $skalaRisiko, $eksposurRisiko];
    //     })->values();

    //     // Urutkan risiko corporate berdasarkan skala risiko dan eksposur risiko
    //     $risikoCorporate = $risikoCorporate->sortBy(function($risk) {
    //         // Urutkan berdasarkan skala risiko (nilai lebih tinggi lebih dulu)
    //         $skalaRisiko = -1 * ($risk->riskAnalysis->skala_risiko ?? 0);

    //         // Jika skala risiko sama, urutkan berdasarkan eksposur risiko (nilai lebih tinggi lebih dulu)
    //         $eksposurRisiko = -1 * ($risk->riskAnalysis->eksposur_risiko ?? 0);

    //         return [$skalaRisiko, $eksposurRisiko];
    //     })->values();

    //     // Data untuk filter
    //     $unit = Unit::pluck('name', 'id');
    //     $periodes = Periode::orderBy('tahun', 'desc')->pluck('tahun', 'id');
    //     $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
    //     $jenisRisiko = JenisRisiko::pluck('title','id');

    //     // Cek status DataBatch untuk menentukan apakah tombol ranking dan jadikan risiko corporate ditampilkan
    //     $dataBatch = null;
    //     if ($periodeId) {
    //         $dataBatch = DataBatch::where('periode_id', $periodeId)
    //             ->where('type', 1) // type = 1 untuk unit/divisi
    //             ->orderBy('batch', 'desc')
    //             ->first();
    //     }

    //     //$showRankingButton = $dataBatch && $dataBatch->status == DataBatch::STATUS_UTAMA;
    //     //$showCorporateButton = $dataBatch && $dataBatch->status == DataBatch::STATUS_VERIFIKASI_CORPORATE;

    //     $showRankingButton = false;
    //     $showCorporateButton = false;

    //     if ($selectedPeriode) {
    //         // Jika status_progress null, maka bisa ranking
    //         $showRankingButton = is_null($selectedPeriode->status_progress);
    //         // Jika status_progress = 1, maka verifikasi corporate
    //         $showCorporateButton = $selectedPeriode->status_progress == 1;
    //     }

    //     return view('corporate-risk.index', compact(
    //         'risikoMain',
    //         'risikoCorporate',
    //         'unit',
    //         'periodes',
    //         'peristiwaRisiko',
    //         'jenisRisiko',
    //         'selectedPeriode',
    //         'unitId',
    //         'showRankingButton',
    //         'showCorporateButton'
    //     ));
    // }

    public function updateToCorporate(Request $request)
    {
        // Validasi request
        $request->validate([
            'selected_risks' => 'required|array',
            'selected_risks.*' => 'exists:identifikasi_risikos,id',
            'periode_id' => 'required|exists:periodes,id'
        ]);

        // Ambil risiko yang dipilih
        $selectedRisks = $request->input('selected_risks', []);

        if (empty($selectedRisks)) {
            return redirect()->route('corporate-risk.index')
                ->with('error', 'Tidak ada risiko yang dipilih');
        }

        // Ambil risiko yang akan diupdate
        $risikos = IdentifikasiRisiko::whereIn('id', $selectedRisks)->get();

        // Update status risiko menjadi corporate dan simpan status sebelumnya
        foreach ($risikos as $risiko) {
            $risiko->update([
                'previous_status_risiko' => $risiko->status_risiko,
                'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_CORPORATE
            ]);
        }

        return redirect()->route('corporate-risk.index', ['pid' => $request->periode_id])
            ->with('success', 'Risiko utama berhasil diubah menjadi risiko corporate');
    }

    public function rankingRisiko(Request $request)
    {
        // Validasi request
        $request->validate([
            'periode_id' => 'required|exists:periodes,id'
        ]);

        $periodeId = $request->periode_id;

        // Ambil semua risiko utama untuk periode yang dipilih
        $risikoMain = IdentifikasiRisiko::where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_MAIN)
            ->where('periode_id', $periodeId)
            ->with('riskAnalysis')
            ->get();

        // Filter risiko berdasarkan kategori dampak
        $quantitativeRisks = $risikoMain->filter(function($risk) {
            return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kuantitatif';
        });

        $qualitativeRisks = $risikoMain->filter(function($risk) {
            return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kualitatif';
        });

        // Hitung rata-rata eksposur risiko untuk risiko kuantitatif
        if ($quantitativeRisks->count() > 0) {
            $avgExposure = $quantitativeRisks->avg(function($risk) {
                return $risk->riskAnalysis->eksposur_risiko ?? 0;
            });

            // Update risiko kuantitatif yang nilainya di atas rata-rata
            foreach ($quantitativeRisks as $risk) {
                if (($risk->riskAnalysis->eksposur_risiko ?? 0) > $avgExposure) {
                    $risk->update([
                        'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION
                    ]);
                }
            }
        }

        // Update risiko kualitatif yang nilai risikonya >= 20
        foreach ($qualitativeRisks as $risk) {
            if (($risk->riskAnalysis->skala_risiko ?? 0) >= 20) {
                $risk->update([
                    'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION
                ]);
            }
        }

        // Update status DataBatch menjadi STATUS_VERIFIKASI_CORPORATE (7)
        // $dataBatch = DataBatch::where('periode_id', $periodeId)
        //     ->where('type', 1) // type = 1 untuk unit/divisi
        //     ->orderBy('batch', 'desc')
        //     ->first();

        // if ($dataBatch) {
        //     $dataBatch->update([
        //         'status' => DataBatch::STATUS_VERIFIKASI_CORPORATE
        //     ]);
        // }

        //update status_progress di periode
        Periode::where('id', $periodeId)
            ->update([
                'status_progress' => 1
            ]);

        return redirect()->route('corporate-risk.index', ['pid' => $periodeId])
            ->with('success', 'Ranking risiko berhasil dilakukan. Risiko yang memenuhi kriteria telah dijadikan risiko corporate.');
    }

    public function revertFromCorporate(Request $request)
    {
        // Validasi request
        $request->validate([
            'risk_id' => 'required|exists:identifikasi_risikos,id',
            'periode_id' => 'required|exists:periodes,id'
        ]);

        $riskId = $request->input('risk_id');
        $periodeId = $request->input('periode_id');

        // Ambil risiko yang akan dikembalikan
        $risiko = IdentifikasiRisiko::find($riskId);

        if (!$risiko) {
            return redirect()->route('corporate-risk.index', ['pid' => $periodeId])
                ->with('error', 'Risiko tidak ditemukan');
        }

        // Kembalikan ke status sebelumnya jika ada, jika tidak kembalikan ke STATUS_RISIKO_MAIN
        $previousStatus = $risiko->previous_status_risiko ?? IdentifikasiRisiko::STATUS_RISIKO_MAIN;

        $risiko->update([
            'status_risiko' => $previousStatus,
            'previous_status_risiko' => null
        ]);

        return redirect()->route('corporate-risk.index', ['pid' => $periodeId])
            ->with('success', 'Risiko berhasil dikembalikan ke status sebelumnya');
    }

    public function confirmCorporateRisks(Request $request)
    {
        // Validasi request
        $request->validate([
            'periode_id' => 'required|exists:periodes,id'
        ]);

        $periodeId = $request->periode_id;

        // Update status DataBatch menjadi STATUS_FINISH (8)
        // $dataBatch = DataBatch::where('periode_id', $periodeId)
        //     ->where('type', 1) // type = 1 untuk unit/divisi
        //     ->orderBy('batch', 'desc')
        //     ->first();

        // if ($dataBatch) {
        //     $dataBatch->update([
        //         'status' => DataBatch::STATUS_FINISH
        //     ]);
        // }

        Periode::where('id', $periodeId)
            ->update([
                'status_progress' => 2
            ]);

        // Update is_proyek menjadi 1 untuk risiko dengan status_risiko corporate
        IdentifikasiRisiko::where('periode_id', $periodeId)
            ->where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_CORPORATE)
            ->update(['is_corporate' => 1]);

        return redirect()->route('corporate-risk.index', ['pid' => $periodeId])
            ->with('success', 'Konfirmasi risiko corporate berhasil dilakukan.');
    }

    public function RiskPeriodeList(Request $request)
    {
        $tableLegend = [
            ['icon' => '<span class="bx bx-show"></span>', 'label' => 'View'],
            ['icon' => '<span class="bx bx-list-check"></span>', 'label' => 'Risk Register'],
            ['icon' => '<span class="bx bx-radar"></span>', 'label' => 'Monitoring'],
            ['icon' => '<span class="bx bx-dock-bottom"></span>', 'label' => 'Loss Event'],
        ];

        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();

        $selectedPeriodeId = $request->query('pid') ?? ($activePeriode?->id);
        $selectedPeriode = $selectedPeriodeId ? Periode::find($selectedPeriodeId) : null;
        $selectedMonth = $request->query('month') ?? date('n');

        $dataToDisplay = collect();
        $displayUnits = Unit::where('unit_type_id', 4)->get();
        $units = $displayUnits->pluck('name', 'id');

        $user = auth()->user();
        $levelId = $user->level_id;

        foreach ($displayUnits as $unit) {
            if ($selectedPeriode) {
                // 1. Hitung Total Risiko Korporat
                $riskCount = IdentifikasiRisiko::where('unit_id', $unit->id)
                    ->where('periode_id', $selectedPeriode->id)
                    ->count();

                // 2. Ambil Batch Terakhir
                $lastBatch = DataBatch::where('unit_id', $unit->id)
                    ->where('periode_id', $selectedPeriode->id)
                    ->where('type', 1) // Type 1 jika menggunakan tabel batch divisi/korporat
                    ->orderBy('batch', 'desc')
                    ->first();

                // 3. Ambil Monitoring Terakhir berdasarkan bulan
                $latestMon = \App\Models\UnitRiskMonitoring::whereHas('identifikasiRisiko', function($q) use ($unit, $selectedPeriode) {
                        $q->where('unit_id', $unit->id)->where('periode_id', $selectedPeriode->id);
                    })
                    ->where('month', $selectedMonth)
                    ->orderBy('id', 'desc')
                    ->first();

                $tmpData = [
                    'unit' => $unit,
                    'periode' => $selectedPeriode,
                    'risk_count' => $riskCount,
                    'last_batch' => $lastBatch,
                    'latest_mon' => $latestMon,
                    'selected_month' => $selectedMonth
                ];

                $dataToDisplay->push([
                    'unit' => $unit->name,
                    'periode' => $selectedPeriode,
                    'risk_status_html' => $this->generateCorpRiskStatusHtml($tmpData, $levelId),
                    'mon_status_html' => $this->generateCorpMonStatusHtml($tmpData, $levelId)
                ]);
            }
        }

        return view('corporate-risk.risk-period-list', compact(
          'periodes',
          'activePeriode',
          'selectedPeriode',
          'selectedMonth',
          'tableLegend',
          'dataToDisplay',
          'units',
        ));
    }

    private function generateCorpRiskStatusHtml($item, $levelId)
    {
        $lastBatch = $item['last_batch'];
        $unitId = $item['unit']->id;
        $periodeId = $item['periode']->id;
        $user = auth()->user();
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = DataBatch::resolveCorporateUserStep((int) $levelId, $is_mr);
        $u_step = $verificationData['u_step'];

        if ($item['risk_count'] == 0) {
            return '<span class="badge bg-light text-dark border border-dark">Tidak Aktif</span>';
        }

        $allRisksStatus = IdentifikasiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->where('unit_type_id', 4)
            ->pluck('status')
            ->toArray();

        $totalRisk = count($allRisksStatus);
        $publishedCount = count(array_filter($allRisksStatus, fn($s) => $s == IdentifikasiRisiko::STATUS_PUBLISHED));

        if (($lastBatch && $lastBatch->finish) || ($totalRisk > 0 && $totalRisk === $publishedCount)) {
            $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: Selesai</div>';

            return '<div class="d-flex flex-column align-items-start">
                        <span class="badge bg-success" data-bs-toggle="tooltip" title="Status: Published / Selesai">Published</span>
                        '.$positionHtml.'
                    </div>';
        }

        $batchStep = $lastBatch ? (int) $lastBatch->step_verification : 0;
        $batchStatus = $lastBatch ? (int) $lastBatch->status : DataBatch::STATUS_PROSES;

        $flow = DataBatch::getCorporateApprovalFlow();
        $stepLabels = [];
        foreach ($flow['steps'] as $step => $config) {
            $stepLabels[(int) $step] = $config['label'];
        }
        if (!isset($stepLabels[0])) {
            $stepLabels[0] = 'Risk Officer MR (Draft)';
        } else {
            $stepLabels[0] = $stepLabels[0] . ' (Draft)';
        }

        $currentLabel = $stepLabels[$batchStep] ?? 'Verifikator';
        if ($batchStatus == DataBatch::STATUS_REVISI) {
            $currentLabel = 'Dikembalikan ke ' . ($stepLabels[0] ?? 'Risk Officer MR');
        }

        $positionHtml = '
        <div class="mt-2 text-dark fw-bold" style="font-size: 11px;">
            Posisi: ' . $currentLabel . '
        </div>';

        $isMyTurn = false;
        if ($levelId == 1 && $is_mr) {
            if (in_array($batchStatus, [DataBatch::STATUS_PROSES, DataBatch::STATUS_REVISI, 0], true)) {
                $isMyTurn = true;
            }
        } elseif ($batchStatus != DataBatch::STATUS_REVISI && $u_step == $batchStep && $u_step > 0) {
            $isMyTurn = true;
        }

        $pulseDot = '
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        $redirectUrl = route('corporate-risk.index', ['pid' => $periodeId]);

        if ($isMyTurn) {
            if ($levelId == 1 && $is_mr) {
                if ($batchStatus == DataBatch::STATUS_REVISI) {
                    return '
                    <div class="d-flex flex-column align-items-start">
                        <a href="'.$redirectUrl.'" class="text-decoration-none align-self-center">
                            <span class="badge bg-danger cursor-pointer border border-danger text-white position-relative"
                                  data-bs-toggle="tooltip"
                                  title="Status: Dikembalikan. Mohon perbaiki data risiko sesuai catatan.">
                                <i class="bx bx-undo me-1"></i> Perlu Revisi
                                '.$pulseDot.'
                            </span>
                        </a>
                        '.$positionHtml.'
                    </div>';
                }

                return '
                <div class="d-flex flex-column align-items-start">
                    <a href="'.$redirectUrl.'" class="text-decoration-none align-self-center">
                        <span class="badge bg-info cursor-pointer border border-info text-white position-relative"
                              data-bs-toggle="tooltip"
                              title="Status: Draft. Silakan lengkapi dan ajukan.">
                            Draft / Input Risiko
                            '.$pulseDot.'
                        </span>
                    </a>
                    '.$positionHtml.'
                </div>';
            }

            return '
            <div class="d-flex flex-column align-items-start">
                <a href="'.$redirectUrl.'" class="text-decoration-none align-self-center">
                    <span class="badge bg-warning text-dark border border-warning shadow-sm cursor-pointer position-relative"
                          data-bs-toggle="tooltip"
                          title="Klik untuk verifikasi: '.$currentLabel.'">
                        <i class="bx bx-error-circle bx-flashing me-1"></i> Perlu Verifikasi
                        '.$pulseDot.'
                    </span>
                </a>
                '.$positionHtml.'
            </div>';
        }

        if (in_array($batchStatus, [DataBatch::STATUS_REVISI, DataBatch::STATUS_PROSES, 0], true)) {
            return '
            <div class="d-flex flex-column align-items-start">
                <div class="d-inline-block position-relative"
                    data-bs-toggle="tooltip"
                    title="Posisi saat ini: '.$currentLabel.'">
                    <span class="badge bg-info bg-opacity-10 text-info border border-info">
                        <i class="bx bx-info-circle me-1"></i> Draft / Input Risiko
                    </span>
                </div>
                '.$positionHtml.'
            </div>';
        }

        return '
        <div class="d-flex flex-column align-items-start">
            <div class="d-inline-block position-relative"
                data-bs-toggle="tooltip"
                title="Posisi saat ini: '.$currentLabel.'">
                <span class="badge bg-info bg-opacity-10 text-info border border-info">
                    <i class="bx bx-time-five me-1"></i> Proses Validasi
                </span>
            </div>
            '.$positionHtml.'
        </div>';
    }

    private function generateCorpMonStatusHtml($item, $levelId)
    {
        $unitId = $item['unit']->id;
        $periodeId = $item['periode']->id;
        $selectedMonth = $item['selected_month'];

        $rawMonitorings = \App\Models\UnitRiskMonitoring::whereHas('identifikasiRisiko', function($q) use ($unitId, $periodeId) {
                $q->where('unit_id', $unitId)
                  ->where('periode_id', $periodeId)
                  ->where('is_closed', false);
            })
            ->where('month', $selectedMonth)
            ->get();

        if ($rawMonitorings->isEmpty()) {
            return '<span class="badge bg-light text-dark border border-dark">Belum Dimonitor</span>';
        }

        $currentMonitorings = $rawMonitorings->groupBy('identifikasi_risiko_id')->map(fn($items) => $items->sortByDesc('id')->first());

        $countTotal = $currentMonitorings->count();
        $countApproved = $currentMonitorings->where('status', 100)->count();

        $hasDraft = $currentMonitorings->where('status', 1)->isNotEmpty();

        if ($countTotal > 0 && $countTotal === $countApproved) {
            $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
            $bulanStr = $namaBulan[(int)$selectedMonth] ?? $selectedMonth;

            return '<div class="d-flex flex-column align-items-center">
                        <span class="badge bg-success">Selesai ('.$bulanStr.')</span>
                        <div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Disetujui</div>
                    </div>';
        }

        $labelPosisi = $hasDraft ? 'Draft Monitoring' : 'Proses Verifikasi';

        return '
        <div class="d-flex flex-column align-items-center">
            <span class="badge bg-info bg-opacity-10 text-info border border-info">
                <i class="bx bx-radar me-1"></i> Sedang Proses
            </span>
            <div class="mt-2 text-dark fw-bold" style="font-size: 11px;">'.$labelPosisi.'</div>
        </div>';
    }

    public function riskPeriodeDashboard(Request $request, $period)
    {
        $user    = request()->user()->load('unit');
        $unit    = Unit::where('unit_type_id', 4)->first();
        $periode = Periode::find($period);

        $targetUnitId = null;
        $targetUnitId = $unit->id;

        $targetUnit = Unit::find($targetUnitId);

        if (!$targetUnit) {
            abort(404, 'Unit tidak ditemukan.');
        }

        $risikos = IdentifikasiRisiko::where('periode_id', $period)
            ->where('unit_type_id', 4)
            ->with([
                'riskAnalysis.skalaDampakObj',
                'riskAnalysis.skalaProbabilitas',
                'riskAnalysis.skalaDampakResidualQ1Obj',
                'riskAnalysis.skalaProbabilitasResidualQ1',
                'riskAnalysis.skalaDampakResidualQ2Obj',
                'riskAnalysis.skalaProbabilitasResidualQ2',
                'riskAnalysis.skalaDampakResidualQ3Obj',
                'riskAnalysis.skalaProbabilitasResidualQ3',
                'riskAnalysis.skalaDampakResidualQ4Obj',
                'riskAnalysis.skalaProbabilitasResidualQ4',
            ])
            ->get();

        $formattedCurrentRiskMaps = [];
        foreach ($risikos as $risiko) {
            $currentValue = $risiko->currentRiskMapsMonth['inherent'] ?? [];
            for ($month = 1; $month <= 12; $month++) {
                if ($nextValue = ($risiko->currentRiskMapsMonth[$month] ?? null)) {
                    $currentValue = $nextValue;
                }

                $currentValue['quarter'] = ceil($month / 3);
                $currentValue['month'] = $month;

                $formattedCurrentRiskMaps[$risiko->id][] = $currentValue;
            }
        }

        $riskResidualData = $this->buildRiskResidualData($risikos);

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        return view('corporate-risk.risk-period-dashboard', compact('user', 'periode', 'risikos', 'riskMaps', 'formattedCurrentRiskMaps', 'riskResidualData', 'targetUnit'));
    }

    public function index(Request $request)
    {
        $corpUnit = Unit::where('unit_type_id', 4)->orderBy('id')->first();
        $unitId = $corpUnit?->id ?? 1;

        $periodeId = $request->query('pid');
        $batchNotes = null;
        if (!$periodeId) {
            $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
            $periodeId = $activePeriode ? $activePeriode->id : null;
        }

        $selectedPeriode = Periode::find($periodeId);
        $user = auth()->user();
        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        $approvalFlow = DataBatch::getCorporateApprovalFlow();
        $min_verification = (int) $approvalFlow['min_verification'];
        $verificationData = DataBatch::resolveCorporateUserStep($levelId, $is_mr);
        $u_step = $verificationData['u_step'];
        $user_verification = $verificationData['user_verification'];
        $step_order = $u_step;

        $dataBatch = DataBatch::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->where('type', DataBatch::TYPE_RISK_REGISTER)
            ->where('finish', false)
            ->orderBy('batch', 'desc')
            ->first();

        if (!$dataBatch) {
            $lastBatch = DataBatch::where('unit_id', $unitId)
                ->where('periode_id', $periodeId)
                ->where('type', DataBatch::TYPE_RISK_REGISTER)
                ->orderBy('batch', 'desc')
                ->first();

            $dataBatch = DataBatch::create([
                'unit_id' => $unitId,
                'periode_id' => $periodeId,
                'type' => DataBatch::TYPE_RISK_REGISTER,
                'status' => DataBatch::STATUS_PROSES,
                'batch' => $lastBatch ? $lastBatch->batch + 1 : 1,
                'step_verification' => 0,
                'finish' => false,
            ]);
        }
        $status = $dataBatch->status;

        $risikoQuery = IdentifikasiRisiko::with([
            'unit',
            'user',
            'periode',
            'kategoriRisiko',
            'jenisRisiko',
            'peristiwaRisiko',
            'riskAnalysis',
        ])->where('unit_type_id', 4);

        if ($periodeId) {
            $risikoQuery->where('periode_id', $periodeId);
        }

        if ($unitId) {
            $risikoQuery->where('unit_id', $unitId);
        }

        $risiko = $risikoQuery
            ->join('risk_analyses', 'identifikasi_risikos.id', '=', 'risk_analyses.risiko_id')
            ->orderBy('risk_analyses.skala_risiko', 'desc')
            ->orderBy('risk_analyses.eksposur_risiko', 'desc')
            ->select('identifikasi_risikos.*')
            ->get();

        $unit = Unit::where('unit_type_id', 1)->pluck('name', 'id');
        $unitChild = Unit::where('parent_id', '!=', null)->pluck('name', 'id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
        $jenisRisiko = JenisRisiko::pluck('title', 'id');

        $pending_risk = 0;
        $draft_risk = IdentifikasiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->where('unit_type_id', 4)
            ->where(function ($query) {
                $query->whereIn('status', [IdentifikasiRisiko::STATUS_INPUT_DATA, IdentifikasiRisiko::STATUS_REJECTED])
                    ->orWhereNull('status');
            })->count();

        if ($dataBatch) {
            $batchNotes = DataBatchNotes::where('data_batch_id', $dataBatch->id)
                ->where('step_order', $step_order)
                ->where('unread', 1)
                ->first();
        }

        if ($step_order > 0 && $dataBatch->step_verification == $step_order) {
            $pending_risk = IdentifikasiRisiko::where('unit_id', $unitId)
                ->where('periode_id', $periodeId)
                ->where('unit_type_id', 4)
                ->where('step_verification', $step_order)
                ->whereNotIn('status', [
                    IdentifikasiRisiko::STATUS_TERVERIFIKASI,
                    IdentifikasiRisiko::STATUS_PUBLISHED,
                ])
                ->count();
        } elseif ($dataBatch && $dataBatch->status == DataBatch::STATUS_REVISI) {
            $pending_risk = IdentifikasiRisiko::where('unit_id', $unitId)
                ->where('periode_id', $periodeId)
                ->where('unit_type_id', 4)
                ->where('status_progress', IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED)
                ->count();
        }

        $avgQuantitativeExposure = null;
        $quantitativeRisks = $risiko->filter(function ($risk) {
            return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kuantitatif';
        });

        if ($quantitativeRisks->count() > 0) {
            $avgQuantitativeExposure = $quantitativeRisks->avg(function ($risk) {
                return $risk->riskAnalysis->eksposur_risiko ?? 0;
            });
        }

        $summaryInfo = null;
        $escalationConfig = [
            'show' => false,
            'label' => 'Kirim Risiko',
            'disabled' => true,
            'route' => route('corporate-risk.send'),
            'parameters' => [
                'unit_id' => $unitId,
                'periode_id' => $periodeId,
                'send_type' => 'send',
            ],
        ];

        $ownerLabel = $approvalFlow['steps'][$min_verification]['label'] ?? 'Risk Owner MR';
        $canActOnCorporate = $is_mr || ($user->unit_id == $unitId);

        if ($canActOnCorporate) {
            // Risk Officer MR (Step 0)
            if ($step_order == 0 && ($verificationData['config']['can_send'] ?? false)) {
                if (in_array($status, [DataBatch::STATUS_PROSES, DataBatch::STATUS_REVISI])) {
                    $escalationConfig['show'] = true;

                    if ($status == DataBatch::STATUS_REVISI) {
                        $escalationConfig['label'] = 'Kirim Perbaikan';
                        $escalationConfig['parameters']['send_type'] = 'rev';

                        if ($pending_risk > 0) {
                            $summaryInfo = [
                                'type' => 'danger',
                                'icon' => 'bx-undo',
                                'message' => "Terdapat <strong>{$pending_risk}</strong> risiko yang <strong>dikembalikan (revisi)</strong>. Silahkan perbaiki data.",
                            ];
                        } else {
                            $summaryInfo = [
                                'type' => 'success',
                                'icon' => 'bx-check-double',
                                'message' => "Seluruh perbaikan telah selesai. Silahkan klik tombol <strong>Kirim Perbaikan</strong> untuk melanjutkan ke {$ownerLabel}.",
                            ];
                        }
                        $escalationConfig['disabled'] = false;
                    } else {
                        if ($draft_risk > 0) {
                            $summaryInfo = [
                                'type' => 'success',
                                'icon' => 'bx-check-double',
                                'message' => "Data risiko siap dikirim. Silahkan klik tombol <strong>Kirim Risiko</strong> untuk melanjutkan ke {$ownerLabel}.",
                            ];
                            $escalationConfig['disabled'] = false;
                        } else {
                            $summaryInfo = [
                                'type' => 'info',
                                'icon' => 'bx-info-circle',
                                'message' => 'Belum ada data risiko. Silahkan tambah risiko baru.',
                            ];
                            $escalationConfig['disabled'] = true;
                        }
                    }
                }
            }
            // Risk Owner MR (Step final)
            elseif ($step_order > 0 && $dataBatch->step_verification == $step_order && !$dataBatch->finish) {
                if ($status != DataBatch::STATUS_REVISI) {
                    $escalationConfig['show'] = true;
                    $escalationConfig['label'] = 'Publish Risiko';
                    $escalationConfig['parameters']['send_type'] = 'mainrisk';

                    if ($pending_risk > 0) {
                        $summaryInfo = [
                            'type' => 'warning',
                            'icon' => 'bxs-error-circle',
                            'message' => "Terdapat <strong>{$pending_risk}</strong> risiko belum diverifikasi.",
                        ];
                        $escalationConfig['disabled'] = true;
                    } else {
                        $summaryInfo = [
                            'type' => 'success',
                            'icon' => 'bx-check-double',
                            'message' => 'Semua terverifikasi. Siap Publish.',
                        ];
                        $escalationConfig['disabled'] = false;
                    }
                }
            }
        }

        $tableLegend = [
            [
                'icon' => '<span class="bx bx-show-alt"></span>',
                'label' => 'View',
            ],
            [
                'icon' => '<span class="bx bx-message-square-edit"></span>',
                'label' => 'Edit',
            ],
            [
                'icon' => '<span class="bx bx-analyse text-warning"></span>',
                'label' => 'Analisa',
            ],
            [
                'icon' => '<span class="bx bx-task text-primary"></span>',
                'label' => 'Perencanaan',
            ],
            [
                'icon' => '<span class="bx bx-trash text-danger"></span>',
                'label' => 'Hapus',
            ],
            [
                'icon' => '<span class="bx bx-check-shield text-success"></span>',
                'label' => 'Verifikasi',
            ],
            [
                'icon' => '<span class="bx bx-comment-dots"></span>',
                'label' => 'Catatan',
            ],
            [
                'icon' => '<span class="badge bg-primary">!</span>',
                'label' => 'Rekomendasi Risiko',
            ],
        ];

        $is_unit_mr = true;
        $unitExpired = false;

        return view('corporate-risk.index', compact(
            'risiko',
            'unit',
            'peristiwaRisiko',
            'status',
            'selectedPeriode',
            'jenisRisiko',
            'levelId',
            'avgQuantitativeExposure',
            'unitId',
            'tableLegend',
            'pending_risk',
            'min_verification',
            'step_order',
            'u_step',
            'user_verification',
            'dataBatch',
            'batchNotes',
            'draft_risk',
            'summaryInfo',
            'escalationConfig',
            'is_unit_mr',
            'unitExpired',
            'approvalFlow',
        ));
    }

    public function create(Request $request)
    {
        $periodeId = $request->query('pid') ?? Periode::where('status', Periode::STATUS_ACTIVE)->value('id');
        $selectedPeriode = Periode::find($periodeId);

        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        $masterKris = MasterKRI::get();
        $kontrolEksistings = KontrolEksisting::get();

        $units = Unit::where('unit_type_id', 1)->orderBy('name')->get();
        $apUnits = Unit::where('unit_type_id', 2)->orderBy('name')->get();
        $taksonomiRisikos = TaksonomiRisiko::all();

        // Ambil Risiko Divisi (unit_type_id = 1) yang sudah final untuk dipilih
        // $divisiRisks = IdentifikasiRisiko::where('unit_type_id', 1)
        //     ->where('status', IdentifikasiRisiko::STATUS_PUBLISHED)
        //     ->with(['unit', 'kategoriRisiko', 'riskAnalysis'])
        //     ->get();

        return view('corporate-risk.create', compact(
            'kategoriRisiko', 'jenisKontrolEksistings',
            'penilaianEfektifitasKontrols',
            'jenisRisiko',
            'selectedPeriode',
            // 'divisiRisks',
            'units',
            'apUnits',
            'masterKris',
            'kontrolEksistings',
            'taksonomiRisikos',
        ));
    }

    public function topDown(Request $request)
    {
        $user = auth()->user();
        $unitId = $user->unit_id;

        $tck = Tck::where('unit_id', $unitId)->pluck('title', 'id');
        if ($tck->isEmpty()) {
            $parentUnitId = Unit::where('id', $unitId)->value('parent_id');

            if ($parentUnitId) {
                $tck = Tck::where('unit_id', $parentUnitId)->pluck('title', 'id');
            }
        }

        $masterKris = MasterKRI::get();
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $peristiwaRisikos = PeristiwaRisiko::get();
        $areaDampak = AreaDampak::pluck('type','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        $units = Unit::whereIn('unit_type_id', [1, 2])->get();
        $periodes = Periode::where('status', 'active')->get();
        $selectedPeriode = Periode::where('status', 'active')->first();

        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $kontrolEksistings = KontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        // Mendapatkan unit (divisi) saat ini
        $unit = Unit::find($unitId);

        return view('corporate-risk.top-down', compact(
          'kategoriRisiko',
          'peristiwaRisikos',
          'masterKris',
          'jenisKontrolEksistings',
          'penilaianEfektifitasKontrols',
          'kontrolEksistings',
          'areaDampak',
          'jenisRisiko',
          'tck',
          'periodes',
          'units',
          'selectedPeriode',
        ));
    }

    public function storeTopDown(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            //'target_capaian_kinerja' => 'required|exists:tcks,id',
            //'peristiwa_risiko_id' => 'required|exists:peristiwa_risikos,id',
            'target_capaian_kinerja' => 'required|string',
            // 'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'wbs' => 'nullable|string',
            'dampak_risiko' => 'required|array|min:1',
            'dampak_risiko.*' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            //'master_kri_id' => 'nullable|array',
            //'master_kri_id.*' => 'nullable|exists:master_kri,id',
            'key_risk_indicator' => 'nullable|array',
            'key_risk_indicator.*' => 'nullable|string',
            'satuan_kri' => 'nullable|array',
            'satuan_kri.*' => 'nullable|string',
            'batas_aman' => 'nullable|array',
            'batas_aman.*' => 'nullable|string',
            'batas_waspada' => 'nullable|array',
            'batas_waspada.*' => 'nullable|string',
            'batas_bahaya' => 'nullable|array',
            'batas_bahaya.*' => 'nullable|string',
            'jenis_kontrol_eksisting_id' => 'nullable|exists:jenis_kontrol_eksistings,id',
            //'kontrol_eksisting_id' => 'nullable|array',
            //'kontrol_eksisting_id.*' => 'nullable|exists:kontrol_eksistings,id',
            //'kontrol_eksisting' => 'required|string',
            'kontrol_eksisting' => 'required|array',  // Ubah menjadi array
            'kontrol_eksisting.*' => 'required|string', // Validasi setiap item
            'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'unit_id' => 'required|exists:units,id',
        ]);

        $peristiwa_risiko = $request->peristiwa_risiko;
        $unitId = $request->unit_id;

        // Pengecekan tidak boleh ada peristiwa risiko yang sama di periode dan unit yang sama
        $existingRisk = IdentifikasiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $request->periode_id)
            ->where('peristiwa_risiko', $peristiwa_risiko)
            ->first();

        if ($existingRisk) {
            return response()->json([
                'message' => 'Peristiwa risiko "' . $peristiwa_risiko . '" sudah ada untuk unit ini pada periode yang sama. Silakan gunakan peristiwa risiko yang berbeda atau edit data yang sudah ada.',
                'redirect' => 'back'
            ], 422);
        }

        try {
            // Konversi format tanggal
            $waktuMulai = null;
            $waktuSelesai = null;

            if ($request->perkiraan_waktu_mulai_terpapar_risiko) {
                $waktuMulai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_mulai_terpapar_risiko)->format('Y-m-d');
            }

            if ($request->perkiraan_waktu_selesai_terpapar_risiko) {
                $waktuSelesai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_selesai_terpapar_risiko)->format('Y-m-d');
            }

            // Simpan data risiko
            $identifikasiRisiko = new IdentifikasiRisiko();
            $identifikasiRisiko->periode_id = $request->periode_id;
            // $identifikasiRisiko->tck_id = $request->target_capaian_kinerja;
            // $tck = Tck::where('id', $request->target_capaian_kinerja)->first();

            // if ($tck) {
            //     $identifikasiRisiko->target_capaian_kinerja = $tck->title;
            // }
            $identifikasiRisiko->target_capaian_kinerja = $request->target_capaian_kinerja;

            // $identifikasiRisiko->jenis_risiko_id = $request->jenis_risiko_id;
            // $jenisRisiko = \App\Models\JenisRisiko::find($request->jenis_risiko_id);
            // if ($jenisRisiko) {
            //     $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            // }
            $identifikasiRisiko->jenis_risiko_id = 0;
            $identifikasiRisiko->kategori_risiko_id = 0;

            $identifikasiRisiko->peristiwa_risiko = $request->peristiwa_risiko;
            $identifikasiRisiko->deskripsi_peristiwa_risiko = $request->deskripsi_peristiwa_risiko;
            $identifikasiRisiko->wbs = $request->wbs;
            $identifikasiRisiko->jenis_kontrol_eksisting_id = $request->jenis_kontrol_eksisting_id;
            //$identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            $identifikasiRisiko->penilaian_efektifitas_kontrol = $request->penilaian_efektifitas_kontrol;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
            $identifikasiRisiko->user_id = auth()->id();
            $identifikasiRisiko->unit_id = $unitId;
            $unit = Unit::find($unitId);
            if ($unit) {
                $identifikasiRisiko->unit_type_id = $unit->unit_type_id;
            }
            else{
                $identifikasiRisiko->unit_type_id = 2;
            }

            $identifikasiRisiko->status = 1;
            $identifikasiRisiko->status_progress = 1;
            $identifikasiRisiko->save();

            // Simpan kontrol eksisting ke model KontrolEksisting
            if ($request->has('kontrol_eksisting') && is_array($request->kontrol_eksisting)) {
                foreach ($request->kontrol_eksisting as $kontrolEksisting) {
                    if (!empty($kontrolEksisting)) {
                        $identifikasiRisiko->kontrolEksistings()->create([
                            'risiko_id' => $identifikasiRisiko->id,
                            'peristiwa_risiko_id' => null,
                            'kontrol_eksisting' => $kontrolEksisting,
                        ]);
                    }
                }
            }

            // Simpan dampak risiko
            if ($request->has('dampak_risiko') && is_array($request->dampak_risiko)) {
                foreach ($request->dampak_risiko as $dampak) {
                    $identifikasiRisiko->dampakRisikos()->create([
                        'risiko_id' => $identifikasiRisiko->id,
                        'dampak_risiko' => $dampak,
                    ]);
                }
            }

            // Simpan penyebab risiko
            if ($request->has('penyebab_risiko') && is_array($request->penyebab_risiko)) {
                foreach ($request->penyebab_risiko as $penyebab) {
                    if (!empty($penyebab)) {
                        $identifikasiRisiko->penyebabRisiko()->create([
                            'penyebab_risiko' => $penyebab,
                            'risiko_id' => $identifikasiRisiko->id,
                        ]);
                    }
                }
            }

            // Simpan KRI
            if ($request->has('key_risk_indicator') && is_array($request->key_risk_indicator)) {
                for ($i = 0; $i < count($request->key_risk_indicator); $i++) {
                    if (!empty($request->key_risk_indicator[$i])) {
                        $identifikasiRisiko->kris()->create([
                            'kri_id' => 0,
                            'risiko_id' => $identifikasiRisiko->id,
                            'kri' => $request->key_risk_indicator[$i],
                            'satuan_kri' => $request->satuan_kri[$i] ?? null,
                            'batas_aman' => $request->batas_aman[$i] ?? null,
                            'batas_waspada' => $request->batas_waspada[$i] ?? null,
                            'batas_bahaya' => $request->batas_bahaya[$i] ?? null,
                        ]);
                    }
                }
            }

            $identifikasiRisiko->riskAnalysis()->create([]);
            $identifikasiRisiko->rencanaPerlakuanRisiko()->create([]);

            $redirectRoute = '';

            if ($unit->unit_type_id == 2) {
                $redirectRoute = route('risk-register-ap.index', [
                    'pid' => $identifikasiRisiko->periode_id,
                    'unit_id' => $identifikasiRisiko->unit_id
                ]);
            } else {
                $redirectRoute = route('risk-register-unit.index', [
                    'pid' => $identifikasiRisiko->periode_id,
                    'unit_id' => $identifikasiRisiko->unit_id
                ]);
            }

            return response()->json([
                'message' => 'Data risiko berhasil dibuat',
                'redirect' => $redirectRoute
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDivisionRisks(Unit $unit)
    {
        $divisiRisks = IdentifikasiRisiko::where('unit_id', $unit->id)
            ->where('unit_type_id', 1)
            ->where('status', IdentifikasiRisiko::STATUS_PUBLISHED)
            ->where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_MAIN)
            ->with(['unit', 'kategoriRisiko', 'riskAnalysis', 'penyebabRisiko'])
            ->get();

        return response()->json([
            'html' => view('corporate-risk._ajax_risk_options', compact('divisiRisks'))->render()
        ]);
    }

    public function getApRisks(Unit $unit)
    {
        $apRisks = IdentifikasiRisiko::where('unit_id', $unit->id)
            ->where('unit_type_id', 2)
            ->where('status', IdentifikasiRisiko::STATUS_PUBLISHED)
            ->where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_MAIN)
            ->with(['unit', 'kategoriRisiko', 'riskAnalysis', 'penyebabRisiko'])
            ->get();

        return response()->json([
            'html' => view('corporate-risk._ajax_risk_options', ['divisiRisks' => $apRisks])->render()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            // 'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'dampak_risiko' => 'required|array|min:1',
            'dampak_risiko.*' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            'key_risk_indicator' => 'nullable|array',
            'key_risk_indicator.*' => 'nullable|string',
            'satuan_kri.*' => 'nullable|string',
            'batas_aman.*' => 'nullable|string',
            'batas_waspada.*' => 'nullable|string',
            'batas_bahaya.*' => 'nullable|string',
            'kontrol_eksisting' => 'required|array',
            'kontrol_eksisting.*' => 'required|string',
            'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'divisi_risk_ids' => 'nullable|array',
            'divisi_risk_ids.*' => 'exists:identifikasi_risikos,id',
            'ap_risk_ids' => 'nullable|array',
            'ap_risk_ids.*' => 'exists:identifikasi_risikos,id'
        ]);

        try {
            $waktuMulai = $request->perkiraan_waktu_mulai_terpapar_risiko ? Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_mulai_terpapar_risiko)->format('Y-m-d') : null;
            $waktuSelesai = $request->perkiraan_waktu_selesai_terpapar_risiko ? Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_selesai_terpapar_risiko)->format('Y-m-d') : null;

            $identifikasiRisiko = new IdentifikasiRisiko();
            $identifikasiRisiko->fill($validated);
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
            $identifikasiRisiko->user_id = auth()->id();
            $identifikasiRisiko->unit_type_id = 4; // KORPORAT
            $identifikasiRisiko->unit_id = 1;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            $identifikasiRisiko->taksonomi_risiko_id = $request->taksonomi_risiko_id;
            $identifikasiRisiko->threshold_risk_limit = $this->cleanRupiah($request->threshold_risk_limit ?? 0);
            $identifikasiRisiko->threshold_risk_appetite = $this->cleanRupiah($request->threshold_risk_appetite ?? 0);
            $identifikasiRisiko->threshold_risk_tolerance = $this->cleanRupiah($request->threshold_risk_tolerance ?? 0);

            // $jenisRisiko = JenisRisiko::find($request->jenis_risiko_id);
            // if ($jenisRisiko) {
            //     $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            // }
            $identifikasiRisiko->jenis_risiko_id = 0;
            $identifikasiRisiko->kategori_risiko_id = 0;

            $identifikasiRisiko->save();

            if ($request->has('param_nama')) {
                foreach ($request->param_nama as $idx => $nama) {
                    if(!empty($nama)) {
                        $identifikasiRisiko->parameterRisikos()->create([
                            'nama' => $nama,
                            'formula' => $request->param_formula[$idx] ?? '',
                            'satuan' => $request->param_satuan[$idx] ?? '',
                        ]);
                    }
                }
            }

            // Simpan dampak risiko
            if ($request->has('dampak_risiko') && is_array($request->dampak_risiko)) {
                foreach ($request->dampak_risiko as $dampak) {
                    $identifikasiRisiko->dampakRisikos()->create([
                        'risiko_id' => $identifikasiRisiko->id,
                        'dampak_risiko' => $dampak,
                    ]);
                }
            }

            // Simpan penyebab risiko
            if ($request->has('penyebab_risiko') && is_array($request->penyebab_risiko)) {
                foreach ($request->penyebab_risiko as $penyebab) {
                    if (!empty($penyebab)) {
                        $identifikasiRisiko->penyebabRisiko()->create([
                            'penyebab_risiko' => $penyebab,
                            'risiko_id' => $identifikasiRisiko->id,
                        ]);
                    }
                }
            }

            // Simpan KRI
            if ($request->has('key_risk_indicator') && is_array($request->key_risk_indicator)) {
                for ($i = 0; $i < count($request->key_risk_indicator); $i++) {
                    if (!empty($request->key_risk_indicator[$i])) {
                        $identifikasiRisiko->kris()->create([
                            'kri_id' => 0, // Karena tidak menggunakan master_kri_id lagi
                            'risiko_id' => $identifikasiRisiko->id,
                            'kri' => $request->key_risk_indicator[$i],
                            'satuan_kri' => $request->satuan_kri[$i] ?? null,
                            'batas_aman' => $request->batas_aman[$i] ?? null,
                            'batas_waspada' => $request->batas_waspada[$i] ?? null,
                            'batas_bahaya' => $request->batas_bahaya[$i] ?? null,
                        ]);
                    }
                }
            }

            if ($request->has('kontrol_eksisting')) {
                foreach ($request->kontrol_eksisting as $kontrol) {
                    if (!empty($kontrol)) $identifikasiRisiko->kontrolEksistings()->create(['kontrol_eksisting' => $kontrol]);
                }
            }

            if ($request->has('divisi_risk_ids')) {
                $identifikasiRisiko->divisiRisks()->attach($request->divisi_risk_ids);
            }

            if ($request->has('ap_risk_ids')) {
                $identifikasiRisiko->apRisks()->sync($request->ap_risk_ids);
            }

            $identifikasiRisiko->riskAnalysis()->create([]);
            $identifikasiRisiko->rencanaPerlakuanRisiko()->create([]);

            $action = $request->input('action', 'save');
            if ($action === 'savenext') {
                return response()->json([
                    'message' => 'Data risiko korporat berhasil disimpan',
                    'redirect' => route('corporate-risk.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                return response()->json([
                    'message' => 'Data risiko korporat berhasil disimpan',
                    'redirect' => route('corporate-risk.index', ['pid' => $request->periode_id])
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $identifikasiRisiko = IdentifikasiRisiko::with([
            'parameterRisikos',
            'kontrolEksistings',
            'dampakRisikos',
            'penyebabRisiko',
            'kris',
            'divisiRisks.unit',
            'divisiRisks.riskAnalysis',
            'divisiRisks.penyebabRisiko',
            'apRisks.unit',
            'apRisks.riskAnalysis',
            'apRisks.penyebabRisiko',
        ])->findOrFail($id);

        $selectedPeriode = Periode::find($identifikasiRisiko->periode_id);

        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        $masterKris = MasterKRI::get();
        $kontrolEksistings = KontrolEksisting::get();
        $taksonomiRisikos = TaksonomiRisiko::all();

        // Ambil daftar Divisi untuk filter modal
        $units = Unit::where('unit_type_id', 1)->orderBy('name')->get();
        $apUnits = Unit::where('unit_type_id', 2)->orderBy('name')->get();

        // Ambil semua risiko divisi untuk modal (akan difilter oleh AJAX)
        $divisiRisks = collect();

        return view('corporate-risk.edit', compact(
            'identifikasiRisiko', 'kategoriRisiko', 'jenisKontrolEksistings',
            'penilaianEfektifitasKontrols', 'jenisRisiko', 'selectedPeriode',
            'divisiRisks', 'units', 'apUnits', 'masterKris', 'kontrolEksistings', 'taksonomiRisikos'
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            // 'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'dampak_risiko' => 'required|array|min:1',
            'dampak_risiko.*' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            'key_risk_indicator' => 'nullable|array',
            'key_risk_indicator.*' => 'nullable|string',
            'kontrol_eksisting' => 'required|array',
            'kontrol_eksisting.*' => 'required|string',
            'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'divisi_risk_ids' => 'nullable|array',
            'divisi_risk_ids.*' => 'exists:identifikasi_risikos,id',
            'ap_risk_ids' => 'nullable|array',
            'ap_risk_ids.*' => 'exists:identifikasi_risikos,id',
        ]);

        try {
            $identifikasiRisiko = IdentifikasiRisiko::findOrFail($id);
            $waktuMulai = $request->perkiraan_waktu_mulai_terpapar_risiko ? Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_mulai_terpapar_risiko)->format('Y-m-d') : null;
            $waktuSelesai = $request->perkiraan_waktu_selesai_terpapar_risiko ? Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_selesai_terpapar_risiko)->format('Y-m-d') : null;
            $dataToUpdate = \Illuminate\Support\Arr::except($validated, ['divisi_risk_ids', 'ap_risk_ids', 'penyebab_risiko', 'key_risk_indicator', 'kontrol_eksisting']);

            $identifikasiRisiko->fill($dataToUpdate);
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            $identifikasiRisiko->taksonomi_risiko_id = $request->taksonomi_risiko_id;
            $identifikasiRisiko->threshold_risk_limit = $this->cleanRupiah($request->threshold_risk_limit);
            $identifikasiRisiko->threshold_risk_appetite = $this->cleanRupiah($request->threshold_risk_appetite);
            $identifikasiRisiko->threshold_risk_tolerance = $this->cleanRupiah($request->threshold_risk_tolerance);

            // $jenisRisiko = JenisRisiko::find($request->jenis_risiko_id);
            // if ($jenisRisiko) {
            //     $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            // }
            $identifikasiRisiko->jenis_risiko_id = 0;
            $identifikasiRisiko->kategori_risiko_id = 0;

            $identifikasiRisiko->save();

            $savedParamIds = [];
            if ($request->has('param_nama')) {
                foreach ($request->param_nama as $key => $nama) {
                    $dataParam = [
                        'nama' => $nama,
                        'formula' => $request->param_formula[$key] ?? '',
                        'satuan' => $request->param_satuan[$key] ?? '',
                    ];

                    $paramId = $request->parameter_risiko_id[$key] ?? null;
                    $exist = $identifikasiRisiko->parameterRisikos()->find($paramId);

                    if ($exist) {
                        $exist->update($dataParam);
                        $savedParamIds[] = $exist->id;
                    } else {
                        $newParam = $identifikasiRisiko->parameterRisikos()->create($dataParam);
                        $savedParamIds[] = $newParam->id;
                    }
                }
            }
            $identifikasiRisiko->parameterRisikos()->whereNotIn('id', $savedParamIds)->delete();

            $dampakRisikoIds = [];
            foreach ($request->dampak_risiko as $dampakRisikoId => $dampakRisiko) {
                $exist = $identifikasiRisiko->dampakRisikos()->where('id', $dampakRisikoId)->first();

                if ($exist) {
                    $exist->update([
                        'dampak_risiko' => $dampakRisiko,
                    ]);
                } else {
                    $exist = $identifikasiRisiko->dampakRisikos()->create([
                        'dampak_risiko' => $dampakRisiko
                    ]);
                }
                $dampakRisikoIds[] = $exist->id;
            }
            $identifikasiRisiko->dampakRisikos()->whereNotIn('id', $dampakRisikoIds)->delete();

            $penyebabRisikoIds = [];
            foreach ($request->penyebab_risiko as $penyebabRisikoId => $penyebabRisiko) {
                $exist = $identifikasiRisiko->penyebabRisiko()->where('id', $penyebabRisikoId)->first();
                if ($exist) {
                    $exist->update([
                        'penyebab_risiko' => $penyebabRisiko,
                    ]);
                } else {
                    $exist = $identifikasiRisiko->penyebabRisiko()->create([
                        'penyebab_risiko' => $penyebabRisiko,
                    ]);
                }
                $penyebabRisikoIds[] = $exist->id;
            }
            $identifikasiRisiko->penyebabRisiko()->whereNotIn('id', $penyebabRisikoIds)->delete();

            $savedKriIds = [];
            foreach ($request->key_risk_indicator as $key => $kri) {
                $kriData = [
                    'kri_id' => 0,
                    'kri' => $kri,
                    'satuan_kri' => $request->satuan_kri[$key] ?? '',
                    'batas_aman' => $request->batas_aman[$key] ?? '',
                    'batas_waspada' => $request->batas_waspada[$key] ?? '',
                    'batas_bahaya' => $request->batas_bahaya[$key] ?? '',
                ];

                $existKri = $identifikasiRisiko->kris()->find($key);

                if ($existKri) {
                    $existKri->update($kriData);
                    $savedKriIds[] = $existKri->id;
                } else {
                    $newKri = $identifikasiRisiko->kris()->create($kriData);
                    $savedKriIds[] = $newKri->id;
                }
            }
            $identifikasiRisiko->kris()->whereNotIn('id', $savedKriIds)->delete();

            $identifikasiRisiko->kontrolEksistings()->delete();
            if ($request->has('kontrol_eksisting')) {
                foreach ($request->kontrol_eksisting as $kontrol) {
                    if (!empty($kontrol)) $identifikasiRisiko->kontrolEksistings()->create(['kontrol_eksisting' => $kontrol]);
                }
            }

            $identifikasiRisiko->divisiRisks()->sync($request->divisi_risk_ids ?? []);
            $identifikasiRisiko->apRisks()->sync($request->ap_risk_ids ?? []);

            $action = $request->input('action', 'save');
            if ($action === 'savenext') {
                return response()->json([
                    'message' => 'Data risiko korporat berhasil diperbarui',
                    'redirect' => route('corporate-risk.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                return response()->json([
                    'message' => 'Data risiko korporat berhasil diperbarui',
                    'redirect' => route('corporate-risk.index', ['pid' => $request->periode_id])
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function view($id)
    {
        $user    = request()->user()->load('unit');
        $risikos = IdentifikasiRisiko::where('id', $id)
            ->with([
              'taksonomiRisiko',
              'dampakRisikos',
              'penyebabRisikos',
              'parameterRisikos',
              'riskAnalysis.skalaDampakObj',
              'riskAnalysis.skalaProbabilitas',
              'riskAnalysis.skalaDampakResidualQ1Obj',
              'riskAnalysis.skalaProbabilitasResidualQ1',
              'riskAnalysis.skalaDampakResidualQ2Obj',
              'riskAnalysis.skalaProbabilitasResidualQ2',
              'riskAnalysis.skalaDampakResidualQ3Obj',
              'riskAnalysis.skalaProbabilitasResidualQ3',
              'riskAnalysis.skalaDampakResidualQ4Obj',
              'riskAnalysis.skalaProbabilitasResidualQ4',
              'projectRisks.project',
              'projectRisks.projectRiskAnalisa',
              'divisiRisks.unit',
              'divisiRisks.riskAnalysis',
              'apRisks.unit',
              'apRisks.riskAnalysis'
            ])
            ->get();

        $risiko = $risikos->first();

        $currentRiskMaps = $risikos->pluck('currentRiskMaps');
        $formattedCurrentRiskMaps = [];

        foreach ($risikos as $idx => $risk) {
            $getFallbackValue = function($targetQuarter) use ($risk) {
                if (isset($risk->current_risk_maps[$targetQuarter]) &&
                    !is_null($risk->current_risk_maps[$targetQuarter]['skala_dampak']) &&
                    !is_null($risk->current_risk_maps[$targetQuarter]['skala_probabilitas'])) {
                    return $risk->current_risk_maps[$targetQuarter];
                }

                for ($q = $targetQuarter - 1; $q >= 1; $q--) {
                    if (isset($risk->current_risk_maps[$q]) &&
                        !is_null($risk->current_risk_maps[$q]['skala_dampak']) &&
                        !is_null($risk->current_risk_maps[$q]['skala_probabilitas'])) {
                        return $risk->current_risk_maps[$q];
                    }
                }

                if (isset($risk->current_risk_maps['inherent']) &&
                    !is_null($risk->current_risk_maps['inherent']['skala_dampak']) &&
                    !is_null($risk->current_risk_maps['inherent']['skala_probabilitas'])) {
                    return $risk->current_risk_maps['inherent'];
                }

                if ($risk->riskAnalysis) {
                    return [
                        'skala_dampak' => $risk->riskAnalysis->skala_dampak,
                        'skala_probabilitas' => $risk->riskAnalysis->skala_probabilitas->tingkat ?? null,
                        'skala_risiko' => $risk->riskAnalysis->skala_risiko,
                        'level_risiko' => $risk->riskAnalysis->level_risiko,
                    ];
                }

                return null;
            };

            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $currentValue = $getFallbackValue($quarter);

                if ($currentValue &&
                    !is_null($currentValue['skala_dampak']) &&
                    !is_null($currentValue['skala_probabilitas'])) {
                    $currentValue['quarter'] = $quarter;
                    $formattedCurrentRiskMaps[$risk->id][] = $currentValue;
                }
            }
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        $risk_tolerance = 0;
        $risk_limit = 0;
        $risiko = $risikos->first();

        if ($risiko->riskAnalysis && $risiko->riskAnalysis->kategori_dampak == 'Kuantitatif') {
            $unit = $risiko->unit;
            $periode = $risiko->periode;

            $riskLimitPeriode = RiskLimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
            if ($riskLimitPeriode) {
                $risk_limit = $riskLimitPeriode->risk_limit;
                $risk_tolerance = $riskLimitPeriode->risk_limit;
            }
        }

        $riskResidualData = $this->buildRiskResidualData($risikos);

        return view('corporate-risk.view', compact('user', 'risikos', 'risiko', 'riskMaps', 'formattedCurrentRiskMaps', 'riskResidualData', 'risk_limit', 'risk_tolerance'));
    }

    public function analisa(Request $request, $riskRegisterId)
    {
        $identifikasiRisiko = IdentifikasiRisiko::with([
            'penyebabRisiko',
            'kris',
            'riskAnalysis',
            'peristiwaRisiko',
            'unit',
            'periode'
        ])->findOrFail($riskRegisterId);

        $user = $request->user();

        // Cek akses
        if (!(Gate::check('risk_register_edit') || $identifikasiRisiko->user_id == $user->id)) {
            abort(403);
        }

        $unit = $identifikasiRisiko->unit;
        $periode = $identifikasiRisiko->periode;

        // Ambil data analisa jika sudah ada
        $analisa = $identifikasiRisiko->riskAnalysis;
        if (!$analisa) {
            // Jika belum ada, buat baru
            $analisa = $identifikasiRisiko->riskAnalysis()->create([]);
        }

        // Ambil data skala probabilitas
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();

        // Ambil data risk map
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        // Ambil data area dampak
        $areas = AreaDampak::with('details')->get();
        $groupedAreas = $areas->groupBy('risk_category');

        $risk_tolerance = 0;
        $risk_limit = 0;

        // $strategiRisiko = StrategiRisiko::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
        // if ($strategiRisiko) {
        //     $totalAnggaranUnit = $strategiRisiko->total_anggaran_unit;
        //     $riskLimitPercentage = config('risk_limit.percentage');
        //     $risk_limit = $totalAnggaranUnit * $riskLimitPercentage / 100;

        //     $totalOtherIdentifikasiRisiko = IdentifikasiRisiko::where('unit_id', $unit->id)
        //         ->where('periode_id', $periode->id)
        //         ->count();

        //     if ($totalOtherIdentifikasiRisiko > 0) {
        //         $risk_limit = $risk_limit / $totalOtherIdentifikasiRisiko;
        //     }
        // }

        $riskLimitPeriode = RiskLimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
        if ($riskLimitPeriode) {
            $risk_limit = $riskLimitPeriode->risk_limit;
            $risk_tolerance = $riskLimitPeriode->risk_limit;
            // $totalOtherIdentifikasiRisiko = IdentifikasiRisiko::where('unit_id', $unit->id)
            //     ->where('periode_id', $periode->id)
            //     ->whereHas('riskAnalysis', function($query) {
            //         $query->where('kategori_dampak', 'Kuantitatif');
            //     })
            //     ->count();

            // if ($totalOtherIdentifikasiRisiko > 0) {
            //     $risk_limit = $risk_limit / $totalOtherIdentifikasiRisiko;
            // }
        }

        $autoCalculate = true; // Flag untuk menentukan apakah perhitungan harus dilakukan secara otomatis

        return view('corporate-risk.analisa', compact(
            'identifikasiRisiko',
            'unit',
            'periode',
            'analisa',
            'skalaProbabilitas',
            'riskMaps',
            'areas',
            'groupedAreas',
            'risk_tolerance',
            'risk_limit',
            'autoCalculate'
        ));
    }


    public function doAnalisa(Request $request, $riskRegisterId)
    {
        $identifikasiRisiko = IdentifikasiRisiko::findOrFail($riskRegisterId);

        $user = $request->user();

        // Cek akses
        // if (!(Gate::check('risk_register_edit') || $identifikasiRisiko->user_id == $user->id)) {
        //     abort(403);
        // }

        $request->merge([
            'nilai_dampak_residual'              => $this->cleanRupiah($request->nilai_dampak_residual_q4),
            'nilai_probabilitas_residual'        => $request->nilai_probabilitas_residual_q4,
            'skala_dampak_residual'              => $request->skala_dampak_residual_q4,
            'skala_dampak_residual_hidden'       => $request->skala_dampak_residual_q4,
            'deskripsi_dampak_residual'          => $request->deskripsi_dampak_residual_q4,
            'asumsi_perhitungan_dampak_residual' => $request->asumsi_perhitungan_dampak_residual_q4,

            'nilai_dampak_residual_q1' => $this->cleanRupiah($request->nilai_dampak_residual_q1),
            'nilai_dampak_residual_q2' => $this->cleanRupiah($request->nilai_dampak_residual_q2),
            'nilai_dampak_residual_q3' => $this->cleanRupiah($request->nilai_dampak_residual_q3),
            'nilai_dampak_residual_q4' => $this->cleanRupiah($request->nilai_dampak_residual_q4),
            'nilai_dampak' => $this->cleanRupiah($request->nilai_dampak),
        ]);

        $validationRules = [
            'kategori_dampak'                    => 'required|string|in:Kualitatif,Kuantitatif',
            'area_dampak'                        => 'nullable|string',
            //'risk_limit'                         => 'required|numeric',
            'nilai_dampak'                       => 'required',
            'nilai_probabilitas'                 => 'required|numeric',
            'skala_dampak'                       => 'required_if:kategori_dampak,Kualitatif|integer',
            'skala_dampak_hidden'                => 'required_if:kategori_dampak,Kualitatif|integer',
            'deskripsi_dampak'                   => 'nullable|string',
            'asumsi_perhitungan_dampak'          => 'nullable|string',
        ];

        for ($i = 1; $i <= 4; $i++) {
            $validationRules['nilai_dampak_residual_q' . $i] = 'nullable';
            $validationRules['nilai_probabilitas_residual_q' . $i] = 'nullable|numeric';
            $validationRules['skala_dampak_residual_q' . $i] = 'nullable|integer';
            $validationRules['skala_dampak_residual_q' . $i . '_hidden'] = 'nullable|integer';
            $validationRules['deskripsi_dampak_residual_q' . $i] = 'nullable|string';
        }

        // Validasi input
        $validated = $request->validate($validationRules);
        $unit = $identifikasiRisiko->unit;
        $periode = $identifikasiRisiko->periode;
        $riskLimitPeriode = RiskLimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();

        // Skala dampak mengikuti pilihan user dari payload (tidak dihitung ulang di server)
        // if ($request->kategori_dampak == 'Kuantitatif') {
        //
        //     $risk_limit = 0;
        //
        //
        //     if ($riskLimitPeriode) {
        //         $risk_limit = $riskLimitPeriode->risk_limit;
        //         $risk_tolerance = $riskLimitPeriode->risk_limit;
        //     }
        //
        //     $toMerge = [
        //         'skala_dampak' => $this->calculateSkalaDampak($request->nilai_dampak * 100 / $risk_limit),
        //     ];
        //     for ($i = 1; $i <= 4; $i++) {
        //         $calculateSkala = $this->calculateSkalaDampak($request->{'nilai_dampak_residual_q' . $i} * 100 / $risk_limit);
        //         $toMerge['skala_dampak_residual_q' . $i] = $calculateSkala;
        //     }
        //
        //     $request->merge($toMerge);
        // }

        // validate q4 < q3 < q2 < q1 < inherent
        $lastValues = [
            'nilai_dampak' => $request->nilai_dampak,
            'nilai_probabilitas' => $request->nilai_probabilitas,
            'skala_dampak' => $request->skala_dampak,
        ];
        for ($i = 0; $i < 4; $i++) {
            if ($request->{'nilai_dampak_residual_q' . ($i + 1)} > $lastValues['nilai_dampak']) {
                return response()->json([
                    'message' => 'Nilai dampak residual q' . ($i + 1) . ' tidak boleh lebih besar dari ' . ($i ? 'q' . $i : 'inherent'),
                ], 422);
            }
            $lastValues['nilai_dampak'] = $request->{'nilai_dampak_residual_q' . ($i + 1)};

            if ($request->{'nilai_probabilitas_residual_q' . ($i + 1)} > $lastValues['nilai_probabilitas']) {
                return response()->json([
                    'message' => 'Nilai probabilitas residual q' . ($i + 1) . ' tidak boleh lebih besar dari ' . ($i ? 'q' . $i : 'inherent'),
                ], 422);
            }
            $lastValues['nilai_probabilitas'] = $request->{'nilai_probabilitas_residual_q' . ($i + 1)};

            // Skala dampak residual mengikuti pilihan user (validasi batas dinonaktifkan)
            $lastValues['skala_dampak'] = $request->{'skala_dampak_residual_q' . ($i + 1)};
        }

        $nilai_dampak = $this->cleanRupiah($request->nilai_dampak);
        $nilai_dampak_residual = $this->cleanRupiah($request->nilai_dampak_residual);

        // Ambil atau buat data analisa
        $analisa = $identifikasiRisiko->riskAnalysis;
        if (!$analisa) {
            $analisa = $identifikasiRisiko->riskAnalysis()->create([]);
        }

        // Ambil data risk map
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        // Update data analisa
        //$analisa->update($validated);
        $toUpdate = [
            'skala_probabilitas_id' => null, // calculated [Done]
            'area_dampak' => $request->area_dampak ?? null,
            'kategori_dampak' => $request->kategori_dampak,
            'deskripsi_dampak' => $request->deskripsi_dampak,
            'deskripsi_dampak_residual' => $request->deskripsi_dampak_residual,
            'asumsi_perhitungan_dampak' => $request->asumsi_perhitungan_dampak,
            'asumsi_perhitungan_dampak_residual' => $request->asumsi_perhitungan_dampak_residual,
            'nilai_dampak' => null, // calculated [Done]
            'nilai_probabilitas' => $request->nilai_probabilitas,
            'skala_probabilitas_residual_id' => null, // calculated [Done]
            'nilai_dampak_residual' => null, // calculated [Done]
            'nilai_probabilitas_residual' => $request->nilai_probabilitas_residual,
        ];

        for ($i = 1; $i <= 4; $i++) {
            $toUpdate['deskripsi_dampak_residual_q' . $i] = $request->{'deskripsi_dampak_residual_q' . $i};
            $toUpdate['asumsi_perhitungan_dampak_residual_q' . $i] = $request->{'asumsi_perhitungan_dampak_residual_q' . $i};
            $toUpdate['skala_probabilitas_residual_id_q' . $i] = null;
            $toUpdate['nilai_dampak_residual_q' . $i] = null;
            $toUpdate['nilai_probabilitas_residual_q' . $i] = $request->{'nilai_probabilitas_residual_q' . $i};
        }

        $analisa->update($toUpdate);

        $toUpdate = [
            'nilai_probabilitas' => $request->nilai_probabilitas,
            'skala_dampak' => $request->skala_dampak_hidden ?? $request->skala_dampak,
            'nilai_dampak' => $nilai_dampak,
            //'risk_limit' => $request->risk_limit,
            //'risk_tolerance' => null, // calculated [Done]
        ];

        $tingkatSkalaProbabilitas = SkalaProbabilitas::getSkalaByValue($request->nilai_probabilitas);
        if (!$tingkatSkalaProbabilitas) {
            return response()->json([
                'message' => 'Nilai probabilitas tidak valid untuk skala probabilitas yang tersedia',
            ], 422);
        }
        $toUpdate['skala_probabilitas_id'] = $tingkatSkalaProbabilitas->id;

        $riskMap = $riskMaps[$toUpdate['skala_dampak'] . '-' . $tingkatSkalaProbabilitas->tingkat] ?? null;
        if (!$riskMap) {
            return response()->json([
                'message' => 'Tidak ada data risk map untuk skala dampak dan probabilitas yang dipilih',
            ], 422);
        }
        $toUpdate['skala_risiko'] = $riskMap->nilai_risiko;
        $toUpdate['level_risiko'] = $riskMap->level_risiko;

        $riskLimitValue = $riskLimitPeriode?->risk_limit ?: 0;

        //$toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * $toUpdate['nilai_probabilitas'];
        if ($request->kategori_dampak == 'Kualitatif') {
            // Untuk kualitatif, gunakan skala dampak * skala probabilitas
            // Rumus: skalaDampak * (1/100) * (nilaiProbabilitas / 100) * riskTolerance
            $toUpdate['eksposur_risiko'] = floatval($toUpdate['skala_dampak']) * (1/100) * (floatval($toUpdate['nilai_probabilitas']) / 100) * $riskLimitValue;
        } else {
            // Untuk kuantitatif, gunakan nilai dampak * probabilitas
            // Rumus: (nilaiDampak * nilaiProbabilitas) / 100
            $toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * ($toUpdate['nilai_probabilitas'] / 100);
        }

        for ($i = 1; $i <= 4; $i++) {
            $nilaiProbResidual = $request->{'nilai_probabilitas_residual_q' . $i};

            // Lanjutkan perhitungan hanya jika ada nilai probabilitas di kuartal ini
            if (!is_null($nilaiProbResidual) && $nilaiProbResidual !== '') {
                if ($request->kategori_dampak == 'Kualitatif') {
                    $skalaDampakResidual = $request->{'skala_dampak_residual_q' . $i};
                    $toUpdate['eksposur_risiko_residual_q' . $i] = floatval($skalaDampakResidual) * (1/100) * (floatval($nilaiProbResidual) / 100) * $riskLimitValue;
                } else { // Kategori Kuantitatif
                    $nilaiDampakResidual = $request->{'nilai_dampak_residual_q' . $i};
                    $toUpdate['eksposur_risiko_residual_q' . $i] = $nilaiDampakResidual * ($nilaiProbResidual / 100);
                }
            } else {
                // Jika tidak ada nilai probabilitas, set eksposur ke null
                $toUpdate['eksposur_risiko_residual_q' . $i] = null;
            }
        }

        $tingkatSkalaProbabilitasResiduals = [];
        for ($i = 1; $i <= 4; $i++) {
            $riskMapResidual = null;

            $tingkatSkalaProbabilitasResiduals[$i] = SkalaProbabilitas::getSkalaByValue($request->{'nilai_probabilitas_residual_q' . $i});
            $toUpdate['skala_probabilitas_residual_id_q' . $i] = optional($tingkatSkalaProbabilitasResiduals[$i])->id;
            $toUpdate['nilai_probabilitas_residual_q' . $i] = $request->{'nilai_probabilitas_residual_q' . $i};
            $toUpdate['skala_dampak_residual_q' . $i] = $request->{'skala_dampak_residual_q' . $i};
            $toUpdate['nilai_dampak_residual_q' . $i] = $request->{'nilai_dampak_residual_q' . $i};

            if ($toUpdate['skala_dampak_residual_q' . $i] && $tingkatSkalaProbabilitasResiduals[$i]) {
                $riskMapResidual = $riskMaps[$toUpdate['skala_dampak_residual_q' . $i] . '-' . $tingkatSkalaProbabilitasResiduals[$i]->tingkat] ?? null;
                if (!$riskMapResidual) {
                    return response()->json([
                        'message' => 'Tidak ada data risk map untuk skala dampak residual dan probabilitas residual yang dipilih',
                    ], 422);
                }
            }

            if ($riskMapResidual) {
                $toUpdate['skala_risiko_residual_q' . $i] = $riskMapResidual->nilai_risiko;
                $toUpdate['level_risiko_residual_q' . $i] = $riskMapResidual->level_risiko;
            } else {
                $toUpdate['skala_risiko_residual_q' . $i] = null;
                $toUpdate['level_risiko_residual_q' . $i] = null;
            }
        }

        $toUpdate['skala_probabilitas_residual_id'] = $toUpdate['skala_probabilitas_residual_id_q4'];
        $toUpdate['nilai_probabilitas_residual'] = $toUpdate['nilai_probabilitas_residual_q4'];
        $toUpdate['skala_dampak_residual'] = $toUpdate['skala_dampak_residual_q4'];
        $toUpdate['nilai_dampak_residual'] = $toUpdate['nilai_dampak_residual_q4'];
        $toUpdate['skala_risiko_residual'] = $toUpdate['skala_risiko_residual_q4'];
        $toUpdate['level_risiko_residual'] = $toUpdate['level_risiko_residual_q4'];

        $analisa->update($toUpdate);

        $identifikasiRisiko->update([
            'skala_risiko' => $toUpdate['skala_risiko'],
            'level_risiko' => $toUpdate['level_risiko'],
        ]);

        return response()->json([
            'message' => 'Analisa risiko berhasil disimpan',
            'redirect' => route('corporate-risk.index', ['pid' => $identifikasiRisiko->periode_id])
        ]);
    }

    public function perencanaan(Request $request, $riskRegisterId)
    {
        $identifikasiRisiko = IdentifikasiRisiko::with([
            'penyebabRisiko.perlakuanPenyebabRisiko',
            'dampakRisikos.perlakuanDampakRisikos',
            'kris',
            'riskAnalysis',
            'peristiwaRisiko',
            'unit',
            'periode'
        ])->findOrFail($riskRegisterId);

        $analisa = $identifikasiRisiko->riskAnalysis;

        return view('corporate-risk.perencanaan', compact('identifikasiRisiko', 'analisa'));
    }

    public function doPerencanaan(Request $request, $riskRegisterId)
    {
        $validated = $request->validate([
            'penyebab_risiko_id' => 'required|exists:penyebab_risikos,id',
            'rencana_perlakuan_risiko' => 'required|string',
            'output_perlakuan_risiko' => 'required|string',
            'biaya_perlakuan_risiko' => 'required|numeric|min:0',
            'pic' => 'required',
            'timeline_mulai_perlakuan_risiko' => 'required',
            'timeline_selesai_perlakuan_risiko' => 'required',
            'opsi_perlakuan_risiko' => 'required|exists:opsi_perlakuan_risikos,id',
            // 'jenis_rencana_perlakuan_risiko' => 'required|exists:jenis_rencana_perlakuan_risikos,id',
        ]);

        $startDate = $validated['timeline_mulai_perlakuan_risiko'] ?? null;
        $endDate = $validated['timeline_selesai_perlakuan_risiko']?? null;

        if (!$endDate) {
            $endDate = $startDate;
        }

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Format tanggal tidak valid. Pastikan rentang tanggal dipilih dengan benar.',
            ], 422);
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            return response()->json([
                'message' => 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.',
            ], 422);
        }

        $jabatan = Jabatan::find($validated['pic']);
        $jabatan_name = "-";
        if($jabatan){
            $jabatan_name = $jabatan->name;
        }

        $perlakuan = PerlakuanPenyebabRisikoUnit::create([
            'penyebab_risiko_id' => $validated['penyebab_risiko_id'],
            'rencana_perlakuan_risiko' => $validated['rencana_perlakuan_risiko'],
            'output_perlakuan_risiko' => $validated['output_perlakuan_risiko'],
            'biaya_perlakuan_risiko' => $validated['biaya_perlakuan_risiko'],
            'pic' => $jabatan_name,
            'pic_jabatan_id' => $validated['pic'],
            'timeline_perlakuan_risiko_start' => $startDate,
            'timeline_perlakuan_risiko_end' => $endDate,
            'opsi_perlakuan_risiko' => $validated['opsi_perlakuan_risiko'],
            // 'jenis_rencana_perlakuan_risiko' => $validated['jenis_rencana_perlakuan_risiko'],
        ]);

        return response()->json([
            'message' => 'Rencana untuk penyebab risiko berhasil ditambahkan!',
        ]);
    }

    public function hapusRencanaPerlakuan($riskRegisterId, $id) {
        try {
            $perlakuan = PerlakuanPenyebabRisikoUnit::findOrFail($id);
            $perlakuan->delete();

            return response()->json([
                'message' => 'Rencana perlakuan berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data. '.$e->getMessage()
            ], 500);
        }
    }

    public function editRencanaPerlakuan($riskRegisterId, $id)
    {
        $perlakuan = PerlakuanPenyebabRisikoUnit::with('penyebabRisiko')->findOrFail($id);

        return response()->json([
            'id' => $perlakuan->id,
            'penyebab_risiko_id' => $perlakuan->penyebab_risiko_id,
            'penyebab_risiko' => $perlakuan->penyebabRisiko->penyebab_risiko ?? null, // Dapatkan nama penyebab risiko
            'rencana_perlakuan_risiko' => $perlakuan->rencana_perlakuan_risiko,
            'output_perlakuan_risiko' => $perlakuan->output_perlakuan_risiko,
            'opsi_perlakuan_risiko' => $perlakuan->opsi_perlakuan_risiko,
            // 'jenis_rencana_perlakuan_risiko' => $perlakuan->jenis_rencana_perlakuan_risiko,
            'biaya_perlakuan_risiko' => $perlakuan->biaya_perlakuan_risiko,
            'pic' => $perlakuan->pic,
            'pic_jabatan_id' => $perlakuan->pic_jabatan_id,
            'timeline_perlakuan_risiko_start' => $perlakuan->timeline_perlakuan_risiko_start ? $perlakuan->timeline_perlakuan_risiko_start->format('d/m/Y') : null,
            'timeline_perlakuan_risiko_end' => $perlakuan->timeline_perlakuan_risiko_end ? $perlakuan->timeline_perlakuan_risiko_end->format('d/m/Y') : null,
        ]);
    }

    public function updateRencanaPerlakuan(Request $request, $riskRegisterId, $id)
    {
        $validated = $request->validate([
            'xrencana_perlakuan_risiko' => 'required',
            'xoutput_perlakuan_risiko' => 'required',
            'xopsi_perlakuan_risiko' => 'required',
            // 'xjenis_rencana_perlakuan_risiko' => 'required',
            'xbiaya_perlakuan_risiko' => 'required|numeric',
            'xpic' => 'required',
            'xtimeline_mulai_perlakuan_risiko' => 'required',
            'xtimeline_selesai_perlakuan_risiko' => 'required',
        ]);

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $validated['xtimeline_mulai_perlakuan_risiko']);
            $endDate = Carbon::createFromFormat('d/m/Y', $validated['xtimeline_selesai_perlakuan_risiko']);

            if ($startDate > $endDate) {
                return response()->json([
                    'message' => 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.'
                ], 422);
            }

            $jabatan = Jabatan::find($validated['xpic']);
            $jabatan_name = "-";
            if($jabatan){
                $jabatan_name = $jabatan->name;
            }

            $data = [
                'rencana_perlakuan_risiko' => $validated['xrencana_perlakuan_risiko'],
                'output_perlakuan_risiko' => $validated['xoutput_perlakuan_risiko'],
                'opsi_perlakuan_risiko' => $validated['xopsi_perlakuan_risiko'],
                // 'jenis_rencana_perlakuan_risiko' => $validated['xjenis_rencana_perlakuan_risiko'],
                'biaya_perlakuan_risiko' => $validated['xbiaya_perlakuan_risiko'],
                'pic' => $jabatan_name,
                'pic_jabatan_id' => $validated['xpic'],
                'timeline_perlakuan_risiko_start' => $startDate->format('Y-m-d'),
                'timeline_perlakuan_risiko_end' => $endDate->format('Y-m-d'),
            ];

            $perlakuan = PerlakuanPenyebabRisikoUnit::findOrFail($id);

            $perlakuan->update($data);

            return response()->json([
                'message' => 'Rencana perlakuan berhasil diperbarui.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function simpanRencanaPerlakuanDampak(Request $request)
    {
        $request->validate([
            'risiko_id' => 'required|exists:identifikasi_risikos,id',
            'dampak_risiko_id' => 'required|exists:dampak_risiko_units,id',
            'rencana_perlakuan_risiko' => 'required',
            'output_perlakuan_risiko' => 'required',
            'biaya_perlakuan_risiko' => 'required|numeric',
            'pic' => 'required',
            'opsi_perlakuan_risiko' => 'required|exists:opsi_perlakuan_risikos,id',
            'timeline_mulai_perlakuan_risiko' => 'required',
            'timeline_selesai_perlakuan_risiko' => 'required',
        ]);

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $request->timeline_mulai_perlakuan_risiko)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $request->timeline_selesai_perlakuan_risiko)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Format tanggal tidak valid. Pastikan rentang tanggal dipilih dengan benar.',
            ], 422);
        }

        $jabatan = Jabatan::find($request->pic);

        PerlakuanDampakRisikoUnit::create([
            'risiko_id' => $request->risiko_id,
            'dampak_risiko_id' => $request->dampak_risiko_id,
            'rencana_perlakuan_risiko' => $request->rencana_perlakuan_risiko,
            'output_perlakuan_risiko' => $request->output_perlakuan_risiko,
            'biaya_perlakuan_risiko' => $request->biaya_perlakuan_risiko,
            'pic' => $jabatan?->name ?? '-',
            'pic_jabatan_id' => $request->pic,
            'divisi_terkait' => $request->divisi_terkait ?? [],
            'opsi_perlakuan_risiko' => $request->opsi_perlakuan_risiko,
            'timeline_perlakuan_risiko_start' => $startDate,
            'timeline_perlakuan_risiko_end' => $endDate,
        ]);

        return response()->json(['message' => 'Rencana Perlakuan Dampak berhasil ditambahkan!']);
    }

    public function editRencanaPerlakuanDampak($id)
    {
        $perlakuan = PerlakuanDampakRisikoUnit::with('risiko', 'dampakRisikoUnit')->findOrFail($id);

        return response()->json([
            'id' => $perlakuan->id,
            'risiko_id' => $perlakuan->risiko_id,
            'dampak_risiko_id' => $perlakuan->dampak_risiko_id,
            'deskripsi_dampak' => $perlakuan->dampakRisikoUnit->dampak_risiko ?? null,
            'rencana_perlakuan_risiko' => $perlakuan->rencana_perlakuan_risiko,
            'output_perlakuan_risiko' => $perlakuan->output_perlakuan_risiko,
            'opsi_perlakuan_risiko' => $perlakuan->opsi_perlakuan_risiko,
            'biaya_perlakuan_risiko' => $perlakuan->biaya_perlakuan_risiko,
            'pic_jabatan_id' => $perlakuan->pic_jabatan_id,
            'timeline_perlakuan_risiko_start' => $perlakuan->timeline_perlakuan_risiko_start ? $perlakuan->timeline_perlakuan_risiko_start->format('d/m/Y') : null,
            'timeline_perlakuan_risiko_end' => $perlakuan->timeline_perlakuan_risiko_end ? $perlakuan->timeline_perlakuan_risiko_end->format('d/m/Y') : null,
        ]);
    }

    public function updateRencanaPerlakuanDampak(Request $request, $id)
    {
        $validated = $request->validate([
            'xd_rencana_perlakuan_risiko' => 'required',
            'xd_output_perlakuan_risiko' => 'required',
            'xd_opsi_perlakuan_risiko' => 'required|exists:opsi_perlakuan_risikos,id',
            'xd_biaya_perlakuan_risiko' => 'required|numeric',
            'xd_pic' => 'required',
            'xd_divisi_terkait' => 'nullable|array',
            'xd_timeline_mulai_perlakuan_risiko' => 'required',
            'xd_timeline_selesai_perlakuan_risiko' => 'required',
        ]);

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $validated['xd_timeline_mulai_perlakuan_risiko'])->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $validated['xd_timeline_selesai_perlakuan_risiko'])->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Format tanggal tidak valid. Pastikan rentang tanggal dipilih dengan benar.',
            ], 422);
        }

        $perlakuan = PerlakuanDampakRisikoUnit::findOrFail($id);
        $jabatan = Jabatan::find($validated['xd_pic']);

        $perlakuan->update([
            'rencana_perlakuan_risiko' => $validated['xd_rencana_perlakuan_risiko'],
            'output_perlakuan_risiko' => $validated['xd_output_perlakuan_risiko'],
            'opsi_perlakuan_risiko' => $validated['xd_opsi_perlakuan_risiko'],
            'biaya_perlakuan_risiko' => $validated['xd_biaya_perlakuan_risiko'],
            'pic' => $jabatan?->name ?? '-',
            'pic_jabatan_id' => $validated['xd_pic'],
            'divisi_terkait' => $request->xd_divisi_terkait ?? [],
            'timeline_perlakuan_risiko_start' => $startDate,
            'timeline_perlakuan_risiko_end' => $endDate,
        ]);

        return response()->json(['message' => 'Rencana perlakuan dampak berhasil diperbarui.']);
    }

    public function hapusRencanaPerlakuanDampak($id)
    {
        try {
            $perlakuan = PerlakuanDampakRisikoUnit::findOrFail($id);
            $perlakuan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Rencana perlakuan dampak berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function cleanRupiah($value) {
        return (float) str_replace(['Rp', '.', ','], ['', '', ''], $value);
    }

    protected function calculateSkalaDampak($percentage) {
        if ($percentage <= 20) return 1; // Low
        if ($percentage > 20 && $percentage <= 40) return 2; // Low To Moderate
        if ($percentage > 40 && $percentage <= 60) return 3; // Moderate
        if ($percentage > 60 && $percentage <= 80) return 4; // Moderate To High
        return 5; // High
    }

    public function send(Request $request)
    {
        $user = auth()->user();
        $corpUnit = Unit::where('unit_type_id', 4)->orderBy('id')->first();
        $unit_id = $request->unit_id ?: ($corpUnit?->id ?? 1);
        $periode_id = $request->periode_id;
        $send_type = $request->send_type ?? 'send';
        $targetLink = route('corporate-risk.index', ['pid' => $periode_id]);

        if (!$periode_id) {
            return redirect()->route('corporate-risk.index')->with('error', 'Periode tidak ditemukan');
        }

        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = DataBatch::resolveCorporateUserStep($user->level_id, $is_mr);
        $step_order = $verificationData['u_step'];
        $finalStep = $verificationData['final_step'];

        if ($send_type !== 'mainrisk') {
            $risikos = IdentifikasiRisiko::with([
                'riskAnalysis',
                'penyebabRisikos.perlakuanPenyebabRisikoUnit',
                'dampakRisikos',
                'perlakuanDampakRisikos',
            ])
                ->where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where('unit_type_id', 4)
                ->where(function ($query) {
                    $query->where('status', '!=', IdentifikasiRisiko::STATUS_PUBLISHED)
                        ->orWhereNull('status');
                })
                ->where('is_closed', 0)
                ->get();

            if ($risikos->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada risiko yang dapat dikirim.');
            }

            $errBelumAnalisa = [];
            $errBelumAdaPerlakuanPenyebab = [];
            $errBelumAdaDampak = [];
            $errBelumAdaPerlakuanDampak = [];

            foreach ($risikos as $risiko) {
                $deskripsi = $risiko->peristiwa_risiko;
                if ($risiko->peristiwaRisiko) {
                    $deskripsi = $risiko->peristiwaRisiko->title;
                }

                if (!$risiko->riskAnalysis) {
                    $errBelumAnalisa[] = $deskripsi;
                }

                if ($risiko->penyebabRisikos->isNotEmpty()) {
                    foreach ($risiko->penyebabRisikos as $penyebab) {
                        if ($penyebab->perlakuanPenyebabRisikoUnit->isEmpty()) {
                            $errBelumAdaPerlakuanPenyebab[] = $deskripsi;
                            break;
                        }
                    }
                }

                if ($risiko->dampakRisikos->isEmpty()) {
                    $errBelumAdaDampak[] = $deskripsi;
                } else {
                    $impactIds = $risiko->dampakRisikos->pluck('id')->toArray();
                    $treatedImpactIds = $risiko->perlakuanDampakRisikos->pluck('dampak_risiko_id')->toArray();
                    if (!empty(array_diff($impactIds, $treatedImpactIds))) {
                        $errBelumAdaPerlakuanDampak[] = $deskripsi;
                    }
                }
            }

            $pesanError = '';
            if (!empty($errBelumAnalisa)) {
                $pesanError .= '<strong>Risiko berikut belum dianalisa:</strong><ul>';
                foreach ($errBelumAnalisa as $d) { $pesanError .= "<li>$d</li>"; }
                $pesanError .= '</ul>';
            }
            if (!empty($errBelumAdaPerlakuanPenyebab)) {
                $pesanError .= '<strong>Risiko berikut belum memiliki rencana perlakuan penyebab:</strong><ul>';
                foreach ($errBelumAdaPerlakuanPenyebab as $d) { $pesanError .= "<li>$d</li>"; }
                $pesanError .= '</ul>';
            }
            if (!empty($errBelumAdaDampak)) {
                $pesanError .= '<strong>Risiko berikut belum memiliki daftar dampak:</strong><ul>';
                foreach ($errBelumAdaDampak as $d) { $pesanError .= "<li>$d</li>"; }
                $pesanError .= '</ul>';
            }
            if (!empty($errBelumAdaPerlakuanDampak)) {
                $pesanError .= '<strong>Risiko berikut belum memiliki rencana perlakuan dampak:</strong><ul>';
                foreach ($errBelumAdaPerlakuanDampak as $d) { $pesanError .= "<li>$d</li>"; }
                $pesanError .= '</ul>';
            }

            if (!empty($pesanError)) {
                $pesanError .= 'Silahkan lengkapi data tersebut terlebih dahulu.';
                return redirect()->route('corporate-risk.index', ['pid' => $periode_id])->with('error', $pesanError);
            }
        }

        $dataBatch = DataBatch::where('unit_id', $unit_id)
            ->where('periode_id', $periode_id)
            ->where('type', DataBatch::TYPE_RISK_REGISTER)
            ->orderBy('batch', 'desc')
            ->first();

        if ($send_type == 'mainrisk') {
            if ($dataBatch) {
                $dataBatch->update([
                    'status' => DataBatch::STATUS_FINISH,
                    'step_verification' => $step_order,
                    'finish' => true,
                ]);

                DataBatch::create([
                    'unit_id' => $unit_id,
                    'periode_id' => $periode_id,
                    'type' => DataBatch::TYPE_RISK_REGISTER,
                    'batch' => $dataBatch->batch + 1,
                    'status' => DataBatch::STATUS_PROSES,
                    'step_verification' => 0,
                    'finish' => false,
                ]);
            }

            IdentifikasiRisiko::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where('unit_type_id', 4)
                ->update([
                    'status' => IdentifikasiRisiko::STATUS_PUBLISHED,
                    'step_verification' => $step_order,
                    'published_at' => now(),
                ]);

            $this->sendNotificationCustom('RO_MR', $unit_id, 'Risiko Korporat Dipublish', 'Risiko korporat telah dipublish oleh Risk Owner MR.', $targetLink, 'bx bx-check-shield');

            return redirect()->route('corporate-risk.index', ['pid' => $periode_id])
                ->with('success', 'Risiko korporat berhasil dipublish');
        }

        if ($send_type == 'rev') {
            if (!$dataBatch) {
                return redirect()->back()->with('error', 'Data batch tidak ditemukan.');
            }

            $dataBatch->update([
                'status' => DataBatch::STATUS_VERIFIKASI,
                'step_verification' => $finalStep,
                'finish' => false,
            ]);

            $risikoToRevise = IdentifikasiRisiko::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where('unit_type_id', 4)
                ->whereIn('status', [
                    IdentifikasiRisiko::STATUS_INPUT_DATA,
                    IdentifikasiRisiko::STATUS_REJECTED,
                ])->get();

            $catatanPerbaikan = $request->catatan_perbaikan ?? 'Tidak ada catatan tambahan';

            foreach ($risikoToRevise as $risk) {
                $risk->update([
                    'status' => IdentifikasiRisiko::STATUS_DIKIRIM,
                    'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW,
                    'step_verification' => $finalStep,
                ]);

                if (!empty($request->catatan_perbaikan)) {
                    RiskNote::create([
                        'risiko_id' => $risk->id,
                        'type' => 1,
                        'status' => 3,
                        'notes' => $catatanPerbaikan,
                        'user_id' => auth()->id(),
                    ]);
                }
            }

            if ($request->filled('catatan_perbaikan')) {
                DataBatchNotes::create([
                    'data_batch_id' => $dataBatch->id,
                    'notes' => $request->catatan_perbaikan,
                    'step_order' => $dataBatch->step_verification,
                    'user_id' => auth()->id(),
                ]);
            }

            $this->sendNotificationCustom('RW_MR', $unit_id, 'Perbaikan Risiko Korporat Dikirim', 'Risk Officer MR telah mengirimkan perbaikan. Catatan: ' . $catatanPerbaikan, $targetLink, 'bx bx-refresh');

            return redirect()->route('corporate-risk.index', ['pid' => $periode_id])
                ->with('success', 'Perbaikan risiko berhasil dikirim untuk diverifikasi.');
        }

        if (!$dataBatch) {
            $dataBatch = DataBatch::create([
                'periode_id' => $periode_id,
                'type' => DataBatch::TYPE_RISK_REGISTER,
                'unit_id' => $unit_id,
                'batch' => 1,
                'status' => DataBatch::STATUS_VERIFIKASI,
                'step_verification' => $finalStep,
                'finish' => false,
            ]);
        } elseif (!$dataBatch->finish) {
            if ($dataBatch->step_verification == null || $dataBatch->step_verification < 1) {
                if ($dataBatch->status != DataBatch::STATUS_PROSES) {
                    return redirect()->route('corporate-risk.index', ['pid' => $periode_id])
                        ->with('error', 'Masih ada data batch risiko yang sedang berproses.');
                }

                $dataBatch->update([
                    'status' => DataBatch::STATUS_VERIFIKASI,
                    'step_verification' => $finalStep,
                    'finish' => false,
                ]);
            }
        } elseif ($dataBatch->finish) {
            $dataBatch = DataBatch::create([
                'periode_id' => $periode_id,
                'type' => DataBatch::TYPE_RISK_REGISTER,
                'unit_id' => $unit_id,
                'batch' => $dataBatch->batch + 1,
                'status' => DataBatch::STATUS_VERIFIKASI,
                'step_verification' => $finalStep,
                'finish' => false,
            ]);
        }

        IdentifikasiRisiko::where('unit_id', $unit_id)
            ->where('periode_id', $periode_id)
            ->where('unit_type_id', 4)
            ->where(function ($query) {
                $query->where('status', IdentifikasiRisiko::STATUS_INPUT_DATA)
                    ->orWhere('status', IdentifikasiRisiko::STATUS_REJECTED)
                    ->orWhereNull('status');
            })
            ->update([
                'status' => IdentifikasiRisiko::STATUS_DIKIRIM,
                'status_risiko' => 1,
                'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW,
                'step_verification' => $finalStep,
            ]);

        $this->sendNotificationCustom('RW_MR', $unit_id, 'Menunggu Verifikasi', 'Terdapat data risiko korporat baru yang butuh verifikasi Anda.', $targetLink, 'bx bx-bell');

        return redirect()->route('corporate-risk.index', ['pid' => $periode_id])
            ->with('success', 'Pengiriman risiko berhasil dilakukan. Risiko telah dikirim untuk diverifikasi.');
    }

    public function verifikasi(Request $request, $riskRegisterId)
    {
        $user = auth()->user();
        $identifikasiRisiko = IdentifikasiRisiko::findOrFail($riskRegisterId);
        $unit_id = $identifikasiRisiko->unit_id;
        $periode_id = $identifikasiRisiko->periode_id;
        $targetLink = route('corporate-risk.index', ['pid' => $periode_id]);

        $dataBatch = DataBatch::where('unit_id', $unit_id)
            ->where('periode_id', $periode_id)
            ->where('type', DataBatch::TYPE_RISK_REGISTER)
            ->orderBy('batch', 'desc')
            ->first();

        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = DataBatch::resolveCorporateUserStep($user->level_id, $is_mr);
        $step_order = $verificationData['u_step'];

        if ($step_order == 0 || !$dataBatch || $dataBatch->step_verification != $step_order) {
            return redirect()->route('corporate-risk.index', ['pid' => $periode_id])
                ->with('error', 'Anda tidak memiliki hak untuk melakukan verifikasi risiko');
        }

        $validated = $request->validate([
            'catatan_verifikasi' => 'required|string',
            'status_verifikasi' => 'required|in:terima,tolak',
        ]);

        try {
            if (!Gate::check('risk_register_verification')) {
                return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melakukan verifikasi risiko');
            }

            if ($validated['status_verifikasi'] === 'terima') {
                $identifikasiRisiko->update([
                    'status' => IdentifikasiRisiko::STATUS_TERVERIFIKASI,
                    'status_progress' => IdentifikasiRisiko::PROGRESS_ON_ACCEPTED,
                    'status_risiko' => 1,
                    'step_verification' => $step_order,
                ]);
            } else {
                $identifikasiRisiko->update([
                    'status' => IdentifikasiRisiko::STATUS_REJECTED,
                    'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED,
                    'step_verification' => 0,
                ]);

                $dataBatch->update(['status' => DataBatch::STATUS_REVISI]);
                $this->sendNotificationCustom('RO_MR', $unit_id, 'Risiko Korporat Ditolak', 'Risiko ditolak dan dikembalikan untuk revisi. Catatan: ' . $validated['catatan_verifikasi'], $targetLink, 'bx bx-x-circle');
            }

            RiskNote::create([
                'risiko_id' => $identifikasiRisiko->id,
                'type' => 1,
                'user_id' => auth()->id(),
                'status' => $validated['status_verifikasi'] === 'terima' ? 1 : 2,
                'notes' => $validated['catatan_verifikasi'],
            ]);

            return redirect()->route('corporate-risk.index', ['pid' => $periode_id])
                ->with('success', 'Verifikasi risiko berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memverifikasi risiko: ' . $e->getMessage());
        }
    }

    public function bulkVerifikasi(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status_verifikasi' => 'required|in:terima,tolak',
            'catatan_verifikasi' => 'required|string',
        ]);

        $user = auth()->user();
        $firstRisk = IdentifikasiRisiko::find($request->ids[0]);
        $unit_id = $firstRisk ? $firstRisk->unit_id : null;
        $periode_id = $firstRisk ? $firstRisk->periode_id : null;
        $targetLink = route('corporate-risk.index', ['pid' => $periode_id]);

        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = DataBatch::resolveCorporateUserStep($user->level_id, $is_mr);
        $step_order = $verificationData['u_step'];

        $dataBatch = DataBatch::where('unit_id', $unit_id)
            ->where('periode_id', $periode_id)
            ->where('type', DataBatch::TYPE_RISK_REGISTER)
            ->orderBy('batch', 'desc')
            ->first();

        if ($step_order == 0 || !$dataBatch || $dataBatch->step_verification != $step_order) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak untuk melakukan verifikasi risiko');
        }

        DB::beginTransaction();
        try {
            foreach ($request->ids as $id) {
                $risk = IdentifikasiRisiko::find($id);
                if (!$risk) {
                    continue;
                }

                if ($request->status_verifikasi === 'terima') {
                    $risk->update([
                        'status' => IdentifikasiRisiko::STATUS_TERVERIFIKASI,
                        'status_progress' => IdentifikasiRisiko::PROGRESS_ON_ACCEPTED,
                        'status_risiko' => 1,
                        'step_verification' => $step_order,
                    ]);
                } else {
                    $risk->update([
                        'status' => IdentifikasiRisiko::STATUS_REJECTED,
                        'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED,
                        'step_verification' => 0,
                    ]);
                }

                RiskNote::create([
                    'risiko_id' => $risk->id,
                    'type' => 1,
                    'user_id' => $user->id,
                    'status' => $request->status_verifikasi === 'terima' ? 1 : 2,
                    'notes' => $request->catatan_verifikasi,
                ]);
            }

            if ($request->status_verifikasi === 'tolak') {
                $dataBatch->update(['status' => DataBatch::STATUS_REVISI]);
                $this->sendNotificationCustom('RO_MR', $unit_id, 'Risiko Korporat Ditolak', 'Beberapa risiko ditolak dan dikembalikan untuk revisi. Catatan: ' . $request->catatan_verifikasi, $targetLink, 'bx bx-x-circle');
            }

            DB::commit();
            return redirect()->route('corporate-risk.index', ['pid' => $periode_id])
                ->with('success', 'Bulk verifikasi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal bulk verifikasi: ' . $e->getMessage());
        }
    }

    public function getRiskNotes(Request $request, $risk)
    {
        try {
            $notes = RiskNote::with('user')
                ->where('risiko_id', $risk)
                ->where('type', 1)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($notes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil data catatan.'], 500);
        }
    }

    private function sendNotificationCustom($target, $unitId, $title, $message, $link, $icon)
    {
        $users = collect();

        if ($target === 'RO_MR') {
            $users = User::permission('mr_notification_division')
                ->where('level_id', 1)
                ->whereHas('unit', function ($q) {
                    $q->where('unit_mr', 1);
                })->get();
        } elseif ($target === 'RW_MR') {
            $users = User::permission('mr_notification_division')
                ->where('level_id', 2)
                ->whereHas('unit', function ($q) {
                    $q->where('unit_mr', 1);
                })->get();
        }

        foreach ($users as $notifUser) {
            Notification::create([
                'user_id' => $notifUser->id,
                'title' => $title,
                'message' => $message,
                'icon' => $icon,
                'link' => $link,
                'read_at' => null,
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $identifikasiRisiko = IdentifikasiRisiko::findOrFail($id);

            if (!Gate::check('risk_register_delete') && $identifikasiRisiko->user_id != auth()->id()) {
                return redirect()->route('corporate-risk.index')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
            }

            $identifikasiRisiko->delete();

            return redirect()->route('corporate-risk.index')->with('success', 'Data risiko berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('corporate-risk.index')->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    private function buildRiskResidualData($risikos): array
    {
        $riskResidualData = [];

        foreach ($risikos as $risiko) {
            $riskAnalysis = $risiko->riskAnalysis;
            if (!$riskAnalysis) {
                continue;
            }

            $riskResidualData[$risiko->id] = [
                1 => [
                    'nilai_dampak' => $riskAnalysis->nilai_dampak_residual_q1,
                    'skala_dampak' => $riskAnalysis->skalaDampakResidualQ1Obj ? "({$riskAnalysis->skalaDampakResidualQ1Obj->tingkat}) {$riskAnalysis->skalaDampakResidualQ1Obj->deskripsi}" : '-',
                    'nilai_prob'   => $riskAnalysis->nilai_probabilitas_residual_q1,
                    'skala_prob'   => $riskAnalysis->skalaProbabilitasResidualQ1 ? "({$riskAnalysis->skalaProbabilitasResidualQ1->tingkat}) {$riskAnalysis->skalaProbabilitasResidualQ1->skala}" : '-',
                    'skala_risiko' => $riskAnalysis->skala_risiko_residual_q1,
                    'level_risiko' => $riskAnalysis->level_risiko_residual_q1,
                ],
                2 => [
                    'nilai_dampak' => $riskAnalysis->nilai_dampak_residual_q2,
                    'skala_dampak' => $riskAnalysis->skalaDampakResidualQ2Obj ? "({$riskAnalysis->skalaDampakResidualQ2Obj->tingkat}) {$riskAnalysis->skalaDampakResidualQ2Obj->deskripsi}" : '-',
                    'nilai_prob'   => $riskAnalysis->nilai_probabilitas_residual_q2,
                    'skala_prob'   => $riskAnalysis->skalaProbabilitasResidualQ2 ? "({$riskAnalysis->skalaProbabilitasResidualQ2->tingkat}) {$riskAnalysis->skalaProbabilitasResidualQ2->skala}" : '-',
                    'skala_risiko' => $riskAnalysis->skala_risiko_residual_q2,
                    'level_risiko' => $riskAnalysis->level_risiko_residual_q2,
                ],
                3 => [
                    'nilai_dampak' => $riskAnalysis->nilai_dampak_residual_q3,
                    'skala_dampak' => $riskAnalysis->skalaDampakResidualQ3Obj ? "({$riskAnalysis->skalaDampakResidualQ3Obj->tingkat}) {$riskAnalysis->skalaDampakResidualQ3Obj->deskripsi}" : '-',
                    'nilai_prob'   => $riskAnalysis->nilai_probabilitas_residual_q3,
                    'skala_prob'   => $riskAnalysis->skalaProbabilitasResidualQ3 ? "({$riskAnalysis->skalaProbabilitasResidualQ3->tingkat}) {$riskAnalysis->skalaProbabilitasResidualQ3->skala}" : '-',
                    'skala_risiko' => $riskAnalysis->skala_risiko_residual_q3,
                    'level_risiko' => $riskAnalysis->level_risiko_residual_q3,
                ],
                4 => [
                    'nilai_dampak' => $riskAnalysis->nilai_dampak_residual_q4,
                    'skala_dampak' => $riskAnalysis->skalaDampakResidualQ4Obj ? "({$riskAnalysis->skalaDampakResidualQ4Obj->tingkat}) {$riskAnalysis->skalaDampakResidualQ4Obj->deskripsi}" : '-',
                    'nilai_prob'   => $riskAnalysis->nilai_probabilitas_residual_q4,
                    'skala_prob'   => $riskAnalysis->skalaProbabilitasResidualQ4 ? "({$riskAnalysis->skalaProbabilitasResidualQ4->tingkat}) {$riskAnalysis->skalaProbabilitasResidualQ4->skala}" : '-',
                    'skala_risiko' => $riskAnalysis->skala_risiko_residual_q4,
                    'level_risiko' => $riskAnalysis->level_risiko_residual_q4,
                ],
            ];
        }

        return $riskResidualData;
    }
}
