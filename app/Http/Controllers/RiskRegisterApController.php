<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\IdentifikasiRisiko;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Periode;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\PeristiwaRisiko;
use App\Models\Tck;
use App\Models\JenisKontrolEksisting;
use App\Models\PenilaianEfektivitasKontrol;
use App\Models\Draft;
use App\Models\MasterKRI;
use App\Models\KontrolEksisting;
use App\Models\AreaDampak;
use App\Models\PenyebabRisiko;
use App\Models\KRI;
use App\Models\PerlakuanPenyebabRisikoUnit;
use App\Models\SkalaProbabilitas;
use App\Models\RiskMap;
use App\Models\StrategiRisiko;
use App\Models\RiskLimitPeriode;
use App\Models\Jabatan;
use App\Models\DataBatch;
use App\Models\RiskNote;
use App\Models\ApprovalLog;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\DataBatchNotes;
use Illuminate\Support\Facades\DB;
use App\Models\PerlakuanDampakRisikoUnit;
use App\Models\UnitRiskMonitoring;
use App\Models\RiskContext;

class RiskRegisterApController extends Controller
{
    public function index(Request $request)
    {
        $unitId = null;
        $periodeId = $request->query('pid');
        $batchNotes = null;

        // 1. Setup Periode
        if (!$periodeId) {
            $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
            $periodeId = $activePeriode ? $activePeriode->id : null;
        }
        $selectedPeriode = Periode::find($periodeId);

        // 2. Setup User & Unit Permissions
        $user = auth()->user();
        $is_mr = $user->unit->unit_mr == 1;
        $levelId = $user->level_id;
        $apAdmin = Gate::check('ap_admin');

        if ($apAdmin) {
            $allowedUnitIds = Unit::where('unit_type_id', 2)->pluck('id');
            $requestedUnitId = (int) $request->query('unit_id');
            $unitId = ($requestedUnitId && $allowedUnitIds->contains($requestedUnitId))
                        ? $requestedUnitId
                        : ($allowedUnitIds->contains($user->unit_id) ? $user->unit_id : $allowedUnitIds->first());
        } else {
            $unitId = $user->unit_id;
        }

        // 3. Setup Data Batch & Logic Unit MR
        $selectedUnit = Unit::find($unitId);
        $is_unit_mr = $selectedUnit->unit_mr == 1;

        // Cek sudah ada Risk Context belum
        $riskContext = RiskContext::where('unit_id', $selectedUnit->id)->first();
        if (!$riskContext || $riskContext->status != RiskContext::STATUS_VERIFIED) {
            return redirect()->route('risk-register-ap.periods')->with('error', 'Silahkan buat Risk Context terlebih dahulu pada Anak Perusahaan ' . $selectedUnit->name . '.');
        }

        // --- LOGIC: Tentukan Min Verification ---
        // Unit MR = 1 (Karena Step 0 Drafter -> Step 1 Owner MR [Final])
        // Unit Biasa = 3 (Step 0 -> Step 1 -> Step 2 -> Step 3 Owner MR [Final])
        $min_verification = $is_unit_mr ? 1 : 3;

        $dataBatch = DataBatch::where('unit_id', $unitId)
                      ->where('periode_id', $periodeId)
                      ->where('type', 1)
                      ->where('finish', false)
                      ->orderBy('batch', 'desc')
                      ->first();

        if(!$dataBatch){
            $lastBatch = DataBatch::where('unit_id', $unitId)
                            ->where('periode_id', $periodeId)
                            ->where('type', 1)
                            ->orderBy('batch', 'desc')
                            ->first();

            $dataBatch = DataBatch::create([
                'unit_id' => $unitId,
                'periode_id' => $periodeId,
                'type' => 1,
                'status' => DataBatch::STATUS_PROSES,
                'batch' => $lastBatch ? $lastBatch->batch + 1 : 1,
                'step_verification' => 0, // Selalu mulai dari 0 (Drafter)
                'finish' => false
            ]);
        }
        $status = $dataBatch->status;

        // 4. Query Data Risiko
        $risikoQuery = IdentifikasiRisiko::with([
            'unit', 'user', 'periode', 'kategoriRisiko',
            'jenisRisiko', 'peristiwaRisiko', 'riskAnalysis',
        ])->where('unit_type_id', 2);

        if ($periodeId) $risikoQuery->where('periode_id', $periodeId);
        if ($unitId) $risikoQuery->where('unit_id', $unitId);

        $risiko = $risikoQuery
            ->join('risk_analyses', 'identifikasi_risikos.id', '=', 'risk_analyses.risiko_id')
            ->orderBy('risk_analyses.skala_risiko', 'desc')
            ->orderBy('risk_analyses.eksposur_risiko', 'desc')
            ->select('identifikasi_risikos.*')
            ->get();

        // 5. Data Filter View
        $unit = Unit::where('unit_type_id', 2)->pluck('name', 'id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
        $jenisRisiko = JenisRisiko::pluck('title','id');

        // 6. Logic Verification Steps
        $verificationData = $this->getUserVerificationStep($levelId, $is_mr, $selectedUnit->unit_mr);
        $u_step = $verificationData['u_step'];
        $user_verification = $verificationData['user_verification'];
        $step_order = $u_step;

        // 7. Hitung Pending Risk & Draft
        $pending_risk = 0;
        $draft_risk = IdentifikasiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->where(function ($query) {
                $query->whereIn('status', [IdentifikasiRisiko::STATUS_INPUT_DATA, IdentifikasiRisiko::STATUS_REJECTED])
                  ->orWhereNull('status');
            })->count();

        // Cek Batch Notes
        if ($dataBatch) {
            $batchNotes = DataBatchNotes::where('data_batch_id', $dataBatch->id)
                ->where('step_order', $step_order)
                ->where('unread', 1)
                ->first();
        }

        // Logic hitung pending risk (Verifikator)
        // Unit MR: Verifikator di step 1. Unit Biasa: di step > 0
        if (($is_unit_mr && $step_order > 0) || (!$is_unit_mr && $step_order > 0)) {
            // Cek apakah batch juga ada di step yang sama dengan user
            if ($dataBatch->step_verification == $step_order) {
                $pending_risk = IdentifikasiRisiko::where('unit_id', $unitId)
                    ->where('periode_id', $periodeId)
                    ->where('step_verification', $step_order)
                    ->whereNotIn('status', [
                        IdentifikasiRisiko::STATUS_TERVERIFIKASI,
                        IdentifikasiRisiko::STATUS_PUBLISHED
                    ])
                    ->count();
            }
        }
        // Logic untuk Risk Officer/Inputter (Drafter - Step 0)
        else {
            if ($dataBatch && $dataBatch->status == DataBatch::STATUS_REVISI) {
                $pending_risk = IdentifikasiRisiko::where('unit_id', $unitId)
                        ->where('periode_id', $periodeId)
                        ->where('status_progress', IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED)
                        ->count();
            }
        }

        // 8. Hitung Rata-rata Eksposur
        $avgQuantitativeExposure = null;
        $quantitativeRisks = $risiko->filter(function($risk) {
            return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kuantitatif';
        });
        if ($quantitativeRisks->count() > 0) {
            $avgQuantitativeExposure = $quantitativeRisks->avg(function($risk) {
                return $risk->riskAnalysis->eksposur_risiko ?? 0;
            });
        }

        // 9. Logic Summary & Escalation
        $summaryInfo = null;
        $escalationConfig = [
            'show' => false, 'label' => 'Kirim Risiko', 'disabled' => true,
            'route' => route('risk-register-ap.send'),
            'parameters' => ['unit_id' => $unitId, 'periode_id' => $periodeId, 'send_type' => 'send']
        ];

        $today = Carbon::today();
        $isStillValid = !$selectedUnit?->valid_to || ($selectedUnit?->valid_to && ($selectedUnit->valid_to->isSameDay($today) || $selectedUnit->valid_to->isAfter($today)));
        $unitExpired = !$isStillValid;

        if (!$unitExpired && ($unitId == auth()->user()->unit_id || $apAdmin)) {

            // --- LOGIC KHUSUS UNIT MR ---
            if ($is_unit_mr) {
                // A. RISK OFFICER MR (Level 1 / Step 0) -> DRAFTER
                if ($levelId == 1) {
                    if (in_array($status, [DataBatch::STATUS_PROSES, DataBatch::STATUS_REVISI])) {
                        $escalationConfig['show'] = true;

                        if ($status == DataBatch::STATUS_REVISI) {
                            $escalationConfig['label'] = 'Kirim Perbaikan';
                            $escalationConfig['parameters']['send_type'] = 'rev';

                            if ($pending_risk > 0) {
                                $summaryInfo = ['type' => 'danger', 'icon' => 'bx-undo', 'message' => "Terdapat <strong>{$pending_risk}</strong> risiko yang <strong>dikembalikan (revisi)</strong>. Silahkan perbaiki data."];
                                $escalationConfig['disabled'] = false;
                            } else {
                                $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Seluruh perbaikan telah selesai. Silahkan klik tombol <strong>Kirim Perbaikan</strong> untuk melanjutkan ke Risk Owner Divisi."];
                                $escalationConfig['disabled'] = false;
                            }
                        } else {
                            if ($draft_risk > 0) {
                                $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Data risiko siap dikirim. Silahkan klik tombol <strong>Kirim Risiko</strong> untuk melanjutkan ke Risk Owner Divisi."];

                                $escalationConfig['disabled'] = false;
                            } else {
                                $summaryInfo = ['type' => 'info', 'icon' => 'bx-info-circle', 'message' => "Belum ada data risiko. Silahkan tambah risiko baru."];
                                $escalationConfig['disabled'] = true;
                            }
                        }
                    }
                }
                // B. RISK OWNER MR (Level 2 / Step 1) -> VERIFIKATOR & PUBLISHER
                elseif ($levelId == 2) {
                    if ($dataBatch->step_verification == 3 && $status != DataBatch::STATUS_REVISI && !$dataBatch->finish) {
                        $escalationConfig['show'] = true;
                        $escalationConfig['label'] = 'Publish Risiko';
                        $escalationConfig['parameters']['send_type'] = 'mainrisk';

                        if ($pending_risk > 0) {
                            $summaryInfo = ['type' => 'warning', 'icon' => 'bxs-error-circle', 'message' => "Terdapat <strong>{$pending_risk}</strong> risiko belum diverifikasi."];
                            $escalationConfig['disabled'] = true;
                        } else {
                            $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Semua terverifikasi. Siap Publish."];
                            $escalationConfig['disabled'] = false;
                        }
                    }
                }
            }
            // --- LOGIC UNIT BIASA (3 STEP) ---
            else {
                // A. TAHAP INPUT (Step 0)
                if ($step_order == 0) {
                    $escalationConfig['show'] = true;
                    if ($status == DataBatch::STATUS_REVISI) {
                        $escalationConfig['label'] = 'Kirim Perbaikan';
                        $escalationConfig['parameters']['send_type'] = 'rev';
                        if ($pending_risk > 0) {
                            $summaryInfo = ['type' => 'danger', 'icon' => 'bx-undo', 'message' => "Terdapat <strong>{$pending_risk}</strong> risiko yang <strong>dikembalikan (revisi)</strong>. Silahkan perbaiki data."];
                            $escalationConfig['disabled'] = false;
                        } else {
                            $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Seluruh perbaikan telah selesai. Silahkan klik tombol <strong>Kirim Perbaikan</strong>."];
                            $escalationConfig['disabled'] = false;
                        }
                    } elseif ($status == DataBatch::STATUS_PROSES) {
                        if ($draft_risk > 0) {
                            $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Data risiko siap dikirim. Silahkan klik tombol <strong>Kirim Risiko</strong> untuk melanjutkan ke Risk Owner Divisi."];
                            $escalationConfig['disabled'] = false;
                        } else {
                            $escalationConfig['disabled'] = true;
                        }
                    } else {
                        $escalationConfig['show'] = false;
                    }
                }
                // B. TAHAP VERIFIKASI
                elseif ($step_order > 0) {
                    if ($step_order == $dataBatch->step_verification) {
                        $escalationConfig['show'] = true;
                        if ($step_order >= $min_verification) {
                            $escalationConfig['label'] = 'Publish Risiko';
                            $escalationConfig['parameters']['send_type'] = 'mainrisk';
                        } else {
                            // Label next step manual mapping, bisa diperbagus
                            $escalationConfig['label'] = "Kirim Lanjut";
                        }

                        if ($pending_risk > 0) {
                            $summaryInfo = ['type' => 'warning', 'icon' => 'bxs-error-circle', 'message' => "Terdapat <strong>{$pending_risk}</strong> risiko belum diverifikasi."];
                            $escalationConfig['disabled'] = true;
                        } else if ($step_order >= $min_verification) {
                            $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Semua terverifikasi. Siap Publish."];
                            $escalationConfig['disabled'] = false;
                        } else {
                            $summaryInfo = [
                              'type' => 'success',
                              'icon' => 'bx-check-double',
                              'message' => "Seluruh risiko telah diverifikasi. Silahkan klik tombol <strong>Kirim Risiko</strong> untuk melanjutkan."
                            ];
                            $escalationConfig['disabled'] = false;
                        }
                    }
                    if ($status === DataBatch::STATUS_REVISI) {
                        $escalationConfig['show'] = false;
                        $summaryInfo = null;
                    }
                }
            }
        }

        $tableLegend = [
            [
              'icon' => '<span class="bx bx-show-alt"></span>',
              'label' => 'View'
            ],
            [
              'icon' => '<span class="bx bx-message-square-edit"></span>',
              'label' => 'Edit'
            ],
            [
              'icon' => '<span class="bx bx-analyse text-warning"></span>',
              'label' => 'Analisa'
            ],
            [
              'icon' => '<span class="bx bx-task text-primary"></span>',
              'label' => 'Perencanaan'
            ],
            [
              'icon' => '<span class="bx bx-trash text-danger"></span>',
              'label' => 'Hapus'
            ],
            [
              'icon' => '<span class="bx bx-comment-dots"></span>',
              'label' => 'Catatan'
            ],
            [
              'icon' => '<span class="badge bg-primary">!</span>',
              'label' => 'Rekomendasi Risiko'
            ],
        ];

        // Hitung status expired divisi berdasarkan valid_to unit
        $currentUnit = Unit::find($unitId);
        $today = Carbon::today();
        $isStillValid = !$currentUnit?->valid_to || ($currentUnit?->valid_to && ($currentUnit->valid_to->isSameDay($today) || $currentUnit->valid_to->isAfter($today)));
        $unitExpired = !$isStillValid;

        $levelId = auth()->user()->level_id;

        return view('risk-register-ap.index', compact(
            'risiko',
            'unit',
            'peristiwaRisiko',
            'status',
            'selectedPeriode',
            'jenisRisiko',
            'pending_risk',
            'min_verification',
            'step_order',
            'u_step',
            'user_verification',
            'dataBatch',
            'batchNotes',
            'levelId',
            'avgQuantitativeExposure',
            'unitId',
            'tableLegend',
            'unitExpired',
            'levelId',
            'draft_risk',
            'summaryInfo',
            'escalationConfig',
            'is_unit_mr',
        ));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $unitId = $user->unit_id;
        // ambil pid di query string, fallback ke aktif
        $periodeId = $request->query('pid')
        ?? Periode::where('status', Periode::STATUS_ACTIVE)->value('id');

        $selectedPeriode = Periode::find($periodeId);

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
        //$periode = Periode::where('status','active')->first();

        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $kontrolEksistings = KontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        return view('risk-register-ap.create',compact('kategoriRisiko','peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings','areaDampak','jenisRisiko','tck','selectedPeriode'));
    }

    public function RiskPeriodeList(Request $request)
    {
        $tableLegend = [
            [
                'icon' => '<span class="bx bx-show"></span>',
                'label' => 'View'
            ],
            [
                'icon' => '<span class="bx bx-list-check"></span>',
                'label' => 'Risk Register'
            ],
            [
                'icon' => '<span class="bx bx-radar"></span>',
                'label' => 'Monitoring'
            ],
            [
                'icon' => '<span class="bx bx-dock-bottom"></span>',
                'label' => 'Loss Event'
            ],
            [
                'icon' => '<span class="bx bx-target-lock"></span>',
                'label' => 'Risk Context'
            ],
        ];

        // Ambil semua data periode (untuk dropdown filter)
        $periodes = Periode::orderBy('tahun', 'desc')->get();

        // Ambil periode aktif jika ada dan set sebagai default pilihan
        $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
        $selectedPeriodeId = $request->query('pid') ?? ($activePeriode?->id);
        $selectedPeriode = $selectedPeriodeId ? Periode::find($selectedPeriodeId) : null;
        $selectedMonth = $request->query('month') ?? date('n');
        $user = auth()->user();
        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        // Ambil data step verifikasi (Pastikan method ini ada di controller/trait Anda)
        $verificationData = $this->getUserVerificationStep($levelId, $is_mr);
        $u_step = $verificationData['u_step'];

        $apAdmin = Gate::check('ap_admin');
        $dataToDisplay = collect();
        $units = [];
        $displayUnits = collect();

        if ($apAdmin) {
            $units = Unit::where('unit_type_id', 2)->pluck('name', 'id');
            $displayUnits = Unit::where('unit_type_id', 2)->get();
        } else {
            $units = Unit::where('unit_type_id', 2)->where('id', $user->unit_id)->pluck('name', 'id');
            $userUnit = Unit::find($user->unit_id);

            if ($userUnit && $userUnit->unit_type_id == 2) {
                $displayUnits = collect([$userUnit]);
            }
        }

        // Loop data untuk display
        foreach ($displayUnits as $unit) {
            if ($selectedPeriode) {
                $today = \Carbon\Carbon::today();

                // 1. Status Kelayakan Unit (Valid/Expired)
                $isValid = ((is_null($unit->valid_to)) || $unit->valid_to->isAfter($today) || $unit->valid_to->isSameDay($today));
                $unitStatus = $isValid ? 'valid' : 'expired';

                // 2. Hitung Total Risiko
                $riskCount = IdentifikasiRisiko::where('unit_id', $unit->id)
                    ->where('periode_id', $selectedPeriode->id)
                    ->count();

                // 3. Ambil Batch Risiko Terakhir
                $lastBatch = DataBatch::where('unit_id', $unit->id)
                    ->where('periode_id', $selectedPeriode->id)
                    ->where('type', 1)
                    ->orderBy('batch', 'desc')
                    ->first();

                // 4. Ambil Monitoring Terakhir
                $latestMon = UnitRiskMonitoring::whereHas('identifikasiRisiko', function($q) use ($unit, $selectedPeriode) {
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
                    'selected_month' => $selectedMonth,
                ];

                $tmpData['is_my_turn_risk'] = $this->checkIsMyTurnRisk($lastBatch, $u_step, $levelId);
                $tmpData['is_my_turn_mon'] = $this->checkIsMyTurnMon($latestMon, $levelId, $is_mr);

                $dataToDisplay->push([
                    'unit' => $unit,
                    'periode' => $selectedPeriode,
                    'unit_status' => $unitStatus,
                    'risk_count' => $riskCount,
                    'risk_status_html' => $this->generateRiskStatusHtml($tmpData, $u_step, $levelId),
                    'mon_status_html' => $this->generateMonitoringStatusHtml($tmpData, $levelId, $is_mr)
                ]);
            }
        }

        return view('risk-register-ap.risk-period-list', compact(
            'periodes',
            'activePeriode',
            'selectedPeriode',
            'tableLegend',
            'dataToDisplay',
            'apAdmin',
            'units',
            'selectedMonth',
        ));
    }

    public function riskPeriodeDashboard(Request $request, $period)
    {
        $user    = request()->user()->load('unit');
        $periode = Periode::find($period);

        $targetUnitId = null;

        if (Gate::check('ap_admin') && $request->has('unit_id')) {
            $targetUnitId = $request->input('unit_id');
        } else {
            $targetUnitId = $user->unit_id;
        }

        $targetUnit = Unit::find($targetUnitId);

        if (!$targetUnit) {
            abort(404, 'Unit tidak ditemukan.');
        }

        $status = request()->query('status');

        // Eager load relasi untuk Inherent & Residual Q1-Q4
        $risikosQuery = IdentifikasiRisiko::where('periode_id', $period)
        ->where('unit_id', $targetUnit->id)
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
        ]);

        if ($status === 'open') {
            $risikosQuery->where('is_closed', 0);
        } elseif ($status === 'closed') {
            $risikosQuery->where('is_closed', 1);
        }
        $risikos = $risikosQuery->get();

        $formattedCurrentRiskMaps = [];
        $riskRealisasiData = [];
        $riskResidualData = [];

        foreach ($risikos as $idx => $risiko) {
            $currentValue = $risiko->currentRiskMapsMonth['inherent'] ?? [];
            $riskAnalysis = $risiko->riskAnalysis;

            // 1. Siapkan Data Residual per Kuartal (Q1 - Q4)
            if ($riskAnalysis) {
                $riskResidualData[$risiko->id] = [
                    1 => [ // Q1
                        'nilai_dampak' => $riskAnalysis->nilai_dampak_residual_q1,
                        'skala_dampak' => $riskAnalysis->skalaDampakResidualQ1Obj ? "({$riskAnalysis->skalaDampakResidualQ1Obj->tingkat}) {$riskAnalysis->skalaDampakResidualQ1Obj->deskripsi}" : '-',
                        'nilai_prob'   => $riskAnalysis->nilai_probabilitas_residual_q1,
                        'skala_prob'   => $riskAnalysis->skalaProbabilitasResidualQ1 ? "({$riskAnalysis->skalaProbabilitasResidualQ1->tingkat}) {$riskAnalysis->skalaProbabilitasResidualQ1->skala}" : '-',
                        'skala_risiko' => $riskAnalysis->skala_risiko_residual_q1,
                        'level_risiko' => $riskAnalysis->level_risiko_residual_q1,
                    ],
                    2 => [ // Q2
                        'nilai_dampak' => $riskAnalysis->nilai_dampak_residual_q2,
                        'skala_dampak' => $riskAnalysis->skalaDampakResidualQ2Obj ? "({$riskAnalysis->skalaDampakResidualQ2Obj->tingkat}) {$riskAnalysis->skalaDampakResidualQ2Obj->deskripsi}" : '-',
                        'nilai_prob'   => $riskAnalysis->nilai_probabilitas_residual_q2,
                        'skala_prob'   => $riskAnalysis->skalaProbabilitasResidualQ2 ? "({$riskAnalysis->skalaProbabilitasResidualQ2->tingkat}) {$riskAnalysis->skalaProbabilitasResidualQ2->skala}" : '-',
                        'skala_risiko' => $riskAnalysis->skala_risiko_residual_q2,
                        'level_risiko' => $riskAnalysis->level_risiko_residual_q2,
                    ],
                    3 => [ // Q3
                        'nilai_dampak' => $riskAnalysis->nilai_dampak_residual_q3,
                        'skala_dampak' => $riskAnalysis->skalaDampakResidualQ3Obj ? "({$riskAnalysis->skalaDampakResidualQ3Obj->tingkat}) {$riskAnalysis->skalaDampakResidualQ3Obj->deskripsi}" : '-',
                        'nilai_prob'   => $riskAnalysis->nilai_probabilitas_residual_q3,
                        'skala_prob'   => $riskAnalysis->skalaProbabilitasResidualQ3 ? "({$riskAnalysis->skalaProbabilitasResidualQ3->tingkat}) {$riskAnalysis->skalaProbabilitasResidualQ3->skala}" : '-',
                        'skala_risiko' => $riskAnalysis->skala_risiko_residual_q3,
                        'level_risiko' => $riskAnalysis->level_risiko_residual_q3,
                    ],
                    4 => [ // Q4
                        'nilai_dampak' => $riskAnalysis->nilai_dampak_residual_q4,
                        'skala_dampak' => $riskAnalysis->skalaDampakResidualQ4Obj ? "({$riskAnalysis->skalaDampakResidualQ4Obj->tingkat}) {$riskAnalysis->skalaDampakResidualQ4Obj->deskripsi}" : '-',
                        'nilai_prob'   => $riskAnalysis->nilai_probabilitas_residual_q4,
                        'skala_prob'   => $riskAnalysis->skalaProbabilitasResidualQ4 ? "({$riskAnalysis->skalaProbabilitasResidualQ4->tingkat}) {$riskAnalysis->skalaProbabilitasResidualQ4->skala}" : '-',
                        'skala_risiko' => $riskAnalysis->skala_risiko_residual_q4,
                        'level_risiko' => $riskAnalysis->level_risiko_residual_q4,
                    ],
                ];
            }

            // 2. Siapkan Data Peta Risiko & Realisasi per Bulan
            for ($month = 1; $month <= 12; $month++) {
                if ($nextValue = ($risiko->currentRiskMapsMonth[$month] ?? null)) {
                    $currentValue = $nextValue;
                }

                $currentValue['quarter'] = ceil($month / 3);
                $currentValue['month'] = $month;

                $formattedCurrentRiskMaps[$risiko->id][] = $currentValue;

                // Format Data Realisasi untuk Tabel
                $riskRealisasiData[$risiko->id][$month] = [
                    'nilai_dampak'       => $currentValue['nilai_dampak'] ?? null,
                    'skala_dampak'       => $currentValue['skala_dampak'] ?? null,
                    'skala_dampak_desc'       => $currentValue['skala_dampak_obj']['deskripsi'] ?? null,
                    'nilai_probabilitas' => $currentValue['nilai_probabilitas'] ?? null,
                    'skala_probabilitas' => $currentValue['skala_probabilitas'] ?? null,
                    'skala_probabilitas_desc' => $currentValue['skala_probabilitas_obj']['skala'] ?? null,
                    'nilai_risiko'       => $currentValue['skala_risiko'] ?? null,
                    'level_risiko'       => $currentValue['level_risiko'] ?? null,
                ];
            }
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        return view('risk-register-ap.risk-period-dashboard', compact('user', 'periode', 'risikos', 'riskMaps', 'formattedCurrentRiskMaps', 'riskRealisasiData', 'riskResidualData', 'targetUnit'));
    }

    public function store(Request $request)
    {
        $rules = [
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            // 'wbs' => 'nullable|string', // Opsional sesuai kebutuhan

            // Validasi Array (Minimal 1 baris)
            'dampak_risiko' => 'required|array|min:1',
            'dampak_risiko.*' => 'required|string',

            'penyebab_risiko' => 'required|array|min:1',
            'penyebab_risiko.*' => 'required|string',

            'kontrol_eksisting' => 'required|array|min:1',
            'kontrol_eksisting.*' => 'required|string',

            // Validasi KRI (Array)
            'key_risk_indicator' => 'required|array|min:1',
            'key_risk_indicator.*' => 'required|string',
            'satuan_kri.*' => 'required|string',
            'batas_aman.*' => 'required',
            'batas_waspada.*' => 'required',
            'batas_bahaya.*' => 'required',

            'perkiraan_waktu_mulai_terpapar_risiko' => 'required|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'required|date_format:d/m/Y',
            'unit_id' => 'nullable|exists:units,id',
        ];

        // 2. Custom Error Messages (Bahasa Indonesia)
        $messages = [
            'periode_id.required' => 'Periode wajib dipilih.',
            'target_capaian_kinerja.required' => 'Sasaran Risiko wajib diisi.',
            'peristiwa_risiko.required' => 'Peristiwa Risiko wajib diisi.',
            'deskripsi_peristiwa_risiko.required' => 'Deskripsi detail peristiwa risiko wajib diisi.',

            'dampak_risiko.required' => 'Mohon masukkan minimal satu Dampak Risiko.',
            'dampak_risiko.*.required' => 'Dampak risiko tidak boleh ada yang kosong.',

            'penyebab_risiko.required' => 'Mohon masukkan minimal satu Penyebab Risiko.',
            'penyebab_risiko.*.required' => 'Penyebab risiko tidak boleh ada yang kosong.',

            'kontrol_eksisting.required' => 'Mohon masukkan minimal satu Kontrol Eksisting.',
            'kontrol_eksisting.*.required' => 'Kontrol eksisting tidak boleh ada yang kosong.',

            'key_risk_indicator.required' => 'Mohon masukkan minimal satu Key Risk Indicator (KRI).',
            'key_risk_indicator.*.required' => 'Nama KRI wajib diisi.',
            'satuan_kri.*.required' => 'Satuan wajib diisi.',
            'batas_aman.*.required' => 'Batas Aman wajib diisi.',
            'batas_waspada.*.required' => 'Batas Waspada wajib diisi.',
            'batas_bahaya.*.required' => 'Batas Bahaya wajib diisi.',

            'perkiraan_waktu_mulai_terpapar_risiko.required' => 'Tanggal mulai terpapar risiko wajib diisi.',
            'perkiraan_waktu_mulai_terpapar_risiko.date_format' => 'Format tanggal mulai salah (harus d/m/Y).',
            'perkiraan_waktu_selesai_terpapar_risiko.required' => 'Tanggal selesai terpapar risiko wajib diisi.',
            'perkiraan_waktu_selesai_terpapar_risiko.date_format' => 'Format tanggal selesai salah (harus d/m/Y).',
        ];

        // Jalankan Validasi
        $validated = $request->validate($rules, $messages);

        $peristiwa_risiko = $request->peristiwa_risiko;
        $unitId = auth()->user()->unit_id;
        if ($request->has('unit_id') && Gate::check('ap_admin')) {
            $unitId = $request->unit_id;
        }

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
            $identifikasiRisiko->target_capaian_kinerja = $this->cleanInput($request->target_capaian_kinerja);

            // Hilangkan jenis risiko dan kategori risiko
            // $identifikasiRisiko->jenis_risiko_id = $request->jenis_risiko_id;
            // $jenisRisiko = \App\Models\JenisRisiko::find($request->jenis_risiko_id);
            // if ($jenisRisiko) {
            //     $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            // }
            $identifikasiRisiko->jenis_risiko_id = 0;
            $identifikasiRisiko->kategori_risiko_id = 0;

            $identifikasiRisiko->peristiwa_risiko = $this->cleanInput($request->peristiwa_risiko);
            $identifikasiRisiko->deskripsi_peristiwa_risiko = $this->cleanInput($request->deskripsi_peristiwa_risiko);
            $identifikasiRisiko->wbs = $request->wbs;
            // $identifikasiRisiko->jenis_kontrol_eksisting_id = $request->jenis_kontrol_eksisting_id;
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
            $identifikasiRisiko->step_verification = 0;

            // Simpan kontrol eksisting
            // if ($request->has('kontrol_eksisting_id') && is_array($request->kontrol_eksisting_id)) {
            //     // Ubah array menjadi string dengan pemisah koma
            //     $kontrolEksisting = implode(',', $request->kontrol_eksisting_id);
            //     // Simpan ke field kontrol_eksisting
            //     $identifikasiRisiko->kontrol_eksisting = $kontrolEksisting;
            // }
            $identifikasiRisiko->save();

            // Simpan kontrol eksisting ke model KontrolEksisting
            if ($request->has('kontrol_eksisting') && is_array($request->kontrol_eksisting)) {
                foreach ($request->kontrol_eksisting as $kontrolEksisting) {
                    if (!empty($kontrolEksisting)) {
                        $identifikasiRisiko->kontrolEksistings()->create([
                            'risiko_id' => $identifikasiRisiko->id,
                            'peristiwa_risiko_id' => null,
                            'kontrol_eksisting' => $this->cleanInput($kontrolEksisting),
                        ]);
                    }
                }
            }

            // Buat analisa dan perencanaan
            $identifikasiRisiko->riskAnalysis()->create([]);
            $identifikasiRisiko->rencanaPerlakuanRisiko()->create([]);

            // Simpan dampak risiko
            if ($request->has('dampak_risiko') && is_array($request->dampak_risiko)) {
                foreach ($request->dampak_risiko as $dampak) {
                    $identifikasiRisiko->dampakRisikos()->create([
                        'risiko_id' => $identifikasiRisiko->id,
                        'dampak_risiko' => $this->cleanInput($dampak),
                    ]);
                }
            }

            // Simpan penyebab risiko
            if ($request->has('penyebab_risiko') && is_array($request->penyebab_risiko)) {
                foreach ($request->penyebab_risiko as $penyebab) {
                    if (!empty($penyebab)) {
                        $identifikasiRisiko->penyebabRisiko()->create([
                            'penyebab_risiko' => $this->cleanInput($penyebab),
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
                            'kri' => $this->cleanInput($request->key_risk_indicator[$i]),
                            'satuan_kri' => $request->satuan_kri[$i] ?? null,
                            'batas_aman' => $request->batas_aman[$i] ?? null,
                            'batas_waspada' => $request->batas_waspada[$i] ?? null,
                            'batas_bahaya' => $request->batas_bahaya[$i] ?? null,
                        ]);
                    }
                }
            }

            // Tentukan redirect berdasarkan action
            $action = $request->input('action', 'save');

            if ($action === 'savenext') {
                // Redirect ke halaman analisis risiko
                return response()->json([
                    'message' => 'Data risiko berhasil disimpan',
                    'redirect' => route('risk-register-ap.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                // Redirect ke halaman index
                return response()->json([
                    'message' => 'Data risiko berhasil disimpan',
                    'redirect' => route('risk-register-ap.index', ['pid' => $request->periode_id, 'unit_id' => $unitId])
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
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

        $riskLimitPeriode = RisklimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
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

        return view('risk-register-ap.analisa', compact(
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

        return view('risk-register-ap.perencanaan', compact('identifikasiRisiko', 'analisa'));
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

    private function cleanRupiah($value) {
        return (float) str_replace(['Rp', '.', ','], ['', '', ''], $value);
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
            $validationRules['skala_dampak_residual_hidden_q' . $i] = 'nullable|integer';
            $validationRules['deskripsi_dampak_residual_q' . $i] = 'nullable|string';
        }

        // Validasi input
        $validated = $request->validate($validationRules);
        $unit = $identifikasiRisiko->unit;
        $periode = $identifikasiRisiko->periode;
        $riskLimitPeriode = RisklimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();

        // [HIDE] Calculate Skala Dampak base on Nilai Dampak
        // if ($request->kategori_dampak == 'Kuantitatif') {

        //     $risk_limit = 0;

        //     if ($riskLimitPeriode) {
        //         $risk_limit = $riskLimitPeriode->risk_limit;
        //         $risk_tolerance = $riskLimitPeriode->risk_limit;
        //         // $totalOtherIdentifikasiRisiko = IdentifikasiRisiko::where('unit_id', $unit->id)
        //         //     ->where('periode_id', $periode->id)
        //         //     ->whereHas('riskAnalysis', function($query) {
        //         //         $query->where('kategori_dampak', 'Kuantitatif');
        //         //     })
        //         //     ->count();

        //         // if ($totalOtherIdentifikasiRisiko > 0) {
        //         //     $risk_limit = $risk_limit / $totalOtherIdentifikasiRisiko;
        //         // }
        //     }

        //     $toMerge = [
        //         'skala_dampak' => $this->calculateSkalaDampak($request->nilai_dampak * 100 / $risk_limit),
        //     ];
        //     for ($i = 1; $i <= 4; $i++) {
        //         $calculateSkala = $this->calculateSkalaDampak($request->{'nilai_dampak_residual_q' . $i} * 100 / $risk_limit);
        //         $toMerge['skala_dampak_residual_q' . $i] = $calculateSkala;
        //     }

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

            // [HIDE] Validation skala dampak
            // if ($request->{'skala_dampak_residual_q' . ($i + 1)} > $lastValues['skala_dampak']) {
            //     return response()->json([
            //         'message' => 'Skala dampak residual q' . ($i + 1) . ' tidak boleh lebih besar dari ' . ($i ? 'q' . $i : 'inherent'),
            //     ], 422);
            // }
            $lastValues['skala_dampak'] = $request->{'skala_dampak_residual_q' . ($i + 1)};
        }

        $xrisk_limit = $this->cleanRupiah($request->_risk_limit);
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
            'skala_dampak' => $request->skala_dampak_hidden,
            'nilai_dampak' => $nilai_dampak,
            //'risk_limit' => $request->risk_limit,
            //'risk_tolerance' => null, // calculated [Done]
        ];

        $tingkatSkalaProbabilitas = SkalaProbabilitas::getSkalaByValue($request->nilai_probabilitas);
        $toUpdate['skala_probabilitas_id'] = $tingkatSkalaProbabilitas->id;

        $riskMap = $riskMaps[$toUpdate['skala_dampak'] . '-' . $tingkatSkalaProbabilitas->tingkat] ?? null;
        if (!$riskMap) {
            return response()->json([
                'message' => 'Tidak ada data risk map untuk skala dampak dan probabilitas yang dipilih',
            ], 422);
        }
        $toUpdate['skala_risiko'] = $riskMap->nilai_risiko;
        $toUpdate['level_risiko'] = $riskMap->level_risiko;

        //$toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * $toUpdate['nilai_probabilitas'];
        if ($request->kategori_dampak == 'Kualitatif') {
            // Rumus: skalaDampak * (1/100) * (nilaiProbabilitas / 100) * riskTolerance
            $toUpdate['eksposur_risiko'] = floatval($toUpdate['skala_dampak']) * (1/100) * (floatval($toUpdate['nilai_probabilitas']) / 100) * ($riskLimitPeriode->risk_limit ?: 0);
        } else {
            // Rumus: (nilaiDampak * nilaiProbabilitas) / 100
            $toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * ($toUpdate['nilai_probabilitas'] / 100);
        }

        for ($i = 1; $i <= 4; $i++) {
            $nilaiProbResidual = $request->{'nilai_probabilitas_residual_q' . $i};

            // Lanjutkan perhitungan hanya jika ada nilai probabilitas di kuartal ini
            if (!is_null($nilaiProbResidual) && $nilaiProbResidual !== '') {
                if ($request->kategori_dampak == 'Kualitatif') {
                    $skalaDampakResidual = $request->{'skala_dampak_residual_q' . $i};
                    $toUpdate['eksposur_risiko_residual_q' . $i] = floatval($skalaDampakResidual) * (1/100) * (floatval($nilaiProbResidual) / 100) * ($riskLimitPeriode->risk_limit ?: 0);
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
            'redirect' => route('risk-register-ap.index', ['pid' => $identifikasiRisiko->periode_id])
        ]);
    }

    protected function calculateSkalaDampak($percentage) {
        if ($percentage <= 20) return 1; // Low
        if ($percentage > 20 && $percentage <= 40) return 2; // Low To Moderate
        if ($percentage > 40 && $percentage <= 60) return 3; // Moderate
        if ($percentage > 60 && $percentage <= 80) return 4; // Moderate To High
        return 5; // High
    }

    public function edit($id)
    {
        $user = auth()->user();
        $unitId = $user->unit_id;

        // Ambil data identifikasi risiko
        $identifikasiRisiko = IdentifikasiRisiko::with([
          'dampakRisikos',
          'kontrolEksistings',
          'penyebabRisiko',
          'kris',
          'parameterRisikos',
          'dampakRisikos',
        ])->findOrFail($id);

        // Ambil periode yang dipilih
        $selectedPeriode = Periode::find($identifikasiRisiko->periode_id);

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

        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $kontrolEksistings = KontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        return view('risk-register-ap.edit', compact(
            'identifikasiRisiko',
            'kategoriRisiko',
            'peristiwaRisikos',
            'masterKris',
            'jenisKontrolEksistings',
            'penilaianEfektifitasKontrols',
            'kontrolEksistings',
            'areaDampak',
            'jenisRisiko',
            'tck',
            'selectedPeriode'
        ));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $rules = [
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'wbs_id' => 'nullable|exists:w_b_s,id',

            // Array Validation
            'dampak_risiko' => 'required|array|min:1',
            'dampak_risiko.*' => 'required|string',

            'penyebab_risiko' => 'required|array|min:1',
            'penyebab_risiko.*' => 'required|string',

            'kontrol_eksisting' => 'required|array|min:1',
            'kontrol_eksisting.*' => 'required|string',

            // KRI Validation
            'key_risk_indicator' => 'required|array|min:1',
            'key_risk_indicator.*' => 'required|string',
            'satuan_kri.*' => 'required|string',
            'batas_aman.*' => 'required',
            'batas_waspada.*' => 'required',
            'batas_bahaya.*' => 'required',

            'perkiraan_waktu_mulai_terpapar_risiko' => 'required|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'required|date_format:d/m/Y',
        ];

        $messages = [
            'periode_id.required' => 'Periode wajib dipilih.',
            'target_capaian_kinerja.required' => 'Sasaran Risiko wajib diisi.',
            'peristiwa_risiko.required' => 'Peristiwa Risiko wajib diisi.',
            'deskripsi_peristiwa_risiko.required' => 'Deskripsi detail wajib diisi.',

            'dampak_risiko.required' => 'Minimal satu Dampak Risiko wajib diisi.',
            'dampak_risiko.*.required' => 'Dampak risiko tidak boleh kosong.',

            'penyebab_risiko.required' => 'Minimal satu Penyebab Risiko wajib diisi.',
            'penyebab_risiko.*.required' => 'Penyebab risiko tidak boleh kosong.',

            'kontrol_eksisting.required' => 'Minimal satu Kontrol Eksisting wajib diisi.',
            'kontrol_eksisting.*.required' => 'Kontrol eksisting tidak boleh kosong.',

            'key_risk_indicator.*.required' => 'Nama KRI wajib diisi.',
            'satuan_kri.*.required' => 'Satuan wajib diisi.',
            'batas_aman.*.required' => 'Batas Aman wajib diisi.',
            'batas_waspada.*.required' => 'Batas Waspada wajib diisi.',
            'batas_bahaya.*.required' => 'Batas Bahaya wajib diisi.',

            'perkiraan_waktu_mulai_terpapar_risiko.required' => 'Tanggal mulai wajib diisi.',
            'perkiraan_waktu_selesai_terpapar_risiko.required' => 'Tanggal selesai wajib diisi.',
        ];

        $validated = $request->validate($rules, $messages);
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

            // Ambil data risiko yang akan diupdate
            $identifikasiRisiko = IdentifikasiRisiko::findOrFail($id);

            // Update data risiko
            $identifikasiRisiko->periode_id = $request->periode_id;
            $identifikasiRisiko->target_capaian_kinerja = $this->cleanInput($request->target_capaian_kinerja);

            // Hilangkan jenis risiko dan kategori risiko
            // $identifikasiRisiko->jenis_risiko_id = $request->jenis_risiko_id;
            // $jenisRisiko = \App\Models\JenisRisiko::find($request->jenis_risiko_id);
            // if ($jenisRisiko) {
            //     $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            // }

            $identifikasiRisiko->peristiwa_risiko = $this->cleanInput($request->peristiwa_risiko);
            $identifikasiRisiko->deskripsi_peristiwa_risiko = $this->cleanInput($request->deskripsi_peristiwa_risiko);
            $identifikasiRisiko->wbs = $request->wbs;
            // $identifikasiRisiko->jenis_kontrol_eksisting_id = $request->jenis_kontrol_eksisting_id;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            // $identifikasiRisiko->penilaian_efektifitas_kontrol = $request->penilaian_efektifitas_kontrol;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;

            $identifikasiRisiko->save();

            // Hapus kontrol eksisting lama dan buat yang baru
            $identifikasiRisiko->kontrolEksistings()->delete();

            // Simpan kontrol eksisting ke model KontrolEksisting
            if ($request->has('kontrol_eksisting') && is_array($request->kontrol_eksisting)) {
                foreach ($request->kontrol_eksisting as $kontrolEksisting) {
                    if (!empty($kontrolEksisting)) {
                        $identifikasiRisiko->kontrolEksistings()->create([
                            'risiko_id' => $identifikasiRisiko->id,
                            'peristiwa_risiko_id' => null,
                            'kontrol_eksisting' => $this->cleanInput($kontrolEksisting),
                        ]);
                    }
                }
            }

            $dampakRisikoIds = [];
            foreach ($request->dampak_risiko as $dampakRisikoId => $dampakRisiko) {
                $exist = $identifikasiRisiko->dampakRisikos()->where('id', $dampakRisikoId)->first();

                if ($exist) {
                    $exist->update([
                        'dampak_risiko' => $this->cleanInput($dampakRisiko),
                    ]);
                } else {
                    $exist = $identifikasiRisiko->dampakRisikos()->create([
                        'dampak_risiko' => $this->cleanInput($dampakRisiko),
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
                        'penyebab_risiko' => $this->cleanInput($penyebabRisiko),
                    ]);
                } else {
                    $exist = $identifikasiRisiko->penyebabRisiko()->create([
                        'penyebab_risiko' => $this->cleanInput($penyebabRisiko),
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

            // Tentukan redirect berdasarkan action
            $action = $request->input('action', 'save');

            if ($action === 'savenext') {
                // Redirect ke halaman analisis risiko
                return response()->json([
                    'message' => 'Data risiko berhasil diperbarui',
                    'redirect' => route('risk-register-ap.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                // Redirect ke halaman index
                return response()->json([
                    'message' => 'Data risiko berhasil diperbarui',
                    'redirect' => route('risk-register-ap.index', ['pid' => $request->periode_id])
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Cari data identifikasi risiko
            $identifikasiRisiko = IdentifikasiRisiko::findOrFail($id);

            // Cek apakah user memiliki akses untuk menghapus
            if (!Gate::check('risk_register_delete')) {
                return redirect()->route('risk-register-ap.index')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
            }

            // Hapus data terkait
            // Hapus kontrol eksisting
            // $identifikasiRisiko->kontrolEksistings()->delete();

            // // Hapus penyebab risiko
            // $identifikasiRisiko->penyebabRisiko()->delete();

            // // Hapus KRI
            // $identifikasiRisiko->kris()->delete();

            // // Hapus analisis risiko jika ada
            // if ($identifikasiRisiko->riskAnalysis) {
            //     $identifikasiRisiko->riskAnalysis->delete();
            // }

            // Hapus data identifikasi risiko
            $identifikasiRisiko->delete();

            return redirect()->route('risk-register-ap.index')->with('success', 'Data risiko berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('risk-register-ap.index')->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    public function send(Request $request)
    {
        // 1. Setup Data Awal
        $user = auth()->user();
        $unit_id = $request->unit_id;
        $periode_id = $request->periode_id;

        if (!$unit_id) {
            return redirect()->route('risk-register-ap.index', [
                'pid' => $periode_id,
                'unit_id' => $user->unit_id
            ])->with('error', 'Anak Perusahaan belum dipilih. Silahkan pilih terlebih dahulu.');
        }

        if (!$periode_id) {
            return redirect()->route('risk-register-ap.index', [
                'unit_id' => $unit_id
            ])->with('error', 'Periode tidak ditemukan');
        }

        $level_id = $user->level_id;
        $send_type = $request->send_type ?? 'send';
        // Definisikan target link untuk notifikasi
        $targetLink = route('risk-register-ap.index', ['pid' => $periode_id, 'unit_id' => $unit_id]);

        // 2. VALIDASI KELENGKAPAN DATA
        if ($send_type !== 'mainrisk') {
            $risikos = IdentifikasiRisiko::with([
                'riskAnalysis',
                'penyebabRisikos.perlakuanPenyebabRisiko',
                'dampakRisikos',
                'perlakuanDampakRisikos'
            ])
            ->where('unit_id', $unit_id)
            ->where('periode_id', $periode_id)
            ->where('unit_type_id', 2)
            ->where(function ($query) {
                $query->where('status', '!=', 6)
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
                        if ($penyebab->perlakuanPenyebabRisiko->isEmpty()) {
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
                    $untreatedImpacts = array_diff($impactIds, $treatedImpactIds);

                    if (!empty($untreatedImpacts)) {
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
                return redirect()->route('risk-register-ap.index', [
                    'pid' => $periode_id,
                    'unit_id' => $unit_id
                ])->with('error', $pesanError);
            }
        }

        // 3. Logic Batch & Step
        $unit = Unit::find($unit_id);
        $min_verification = 3;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $is_unit_mr = $unit->unit_mr == 1;

        $verificationData = $this->getUserVerificationStep($level_id, $is_mr, $unit->unit_mr);
        $u_step = $verificationData['u_step'];
        $step_order = $u_step;

        // --- SKENARIO 1: PUBLISH (MAIN RISK) ---
        if ($send_type == 'mainrisk') {
            $dataBatch = DataBatch::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where('type', 1)
                ->orderBy('batch', 'desc')
                ->first();

            if ($dataBatch) {
                $dataBatch->update([
                    'status' => DataBatch::STATUS_FINISH,
                    'step_verification' => $step_order,
                    'finish' => true
                ]);
                $dataBatch->refresh();

                DataBatch::create([
                    'unit_id' => $unit_id,
                    'periode_id' => $periode_id,
                    'type' => 1,
                    'batch' => $dataBatch->batch + 1,
                    'status' => DataBatch::STATUS_PROSES,
                    'step_verification' => 0,
                    'finish' => false,
                ]);
            }

            IdentifikasiRisiko::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->update([
                    'status' => IdentifikasiRisiko::STATUS_PUBLISHED,
                    'step_verification' => $step_order
                ]);

            IdentifikasiRisiko::determineMainRisks($unit_id, $periode_id);

            // NOTIFIKASI: Beritahu Officer dan Owner Anak Perusahaan bahwa risiko sudah di Publish
            $this->sendNotificationCustom('RO_AP', $unit_id, 'Risiko Dipublish', 'Risiko Anak Perusahaan Anda telah dipublish oleh MR.', $targetLink, 'bx bx-check-shield');
            $this->sendNotificationCustom('RW_AP', $unit_id, 'Risiko Dipublish', 'Risiko Anak Perusahaan Anda telah dipublish oleh MR.', $targetLink, 'bx bx-check-shield');

            return redirect()->route('risk-register-ap.index', [
                'pid' => $periode_id,
                'unit_id' => $unit_id
            ])->with('success', 'Risiko berhasil dipublish');
        }
        // --- SKENARIO 2: KIRIM BIASA / REVISI ---
        else {
            $dataBatch = DataBatch::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where('type', 1)
                ->orderBy('batch', 'desc')
                ->first();

            // A. LOGIC REVISI (Kirim Perbaikan)
            if ($send_type == 'rev') {
                if ($dataBatch->step_verification == 1) {
                    $dataBatch->update([
                        'status' => DataBatch::STATUS_KIRIM,
                        'finish' => false
                    ]);
                    $update_status = IdentifikasiRisiko::STATUS_DIKIRIM;
                } else {
                    $dataBatch->update([
                        'status' => DataBatch::STATUS_VERIFIKASI,
                        'finish' => false
                    ]);
                    $update_status = IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI;
                }
                $dataBatch->refresh();

                $risikoToRevise = IdentifikasiRisiko::where('unit_id', $unit_id)
                    ->where('periode_id', $periode_id)
                    ->whereIn('status', [
                        IdentifikasiRisiko::STATUS_INPUT_DATA,
                        IdentifikasiRisiko::STATUS_REJECTED
                    ])->get();

                $catatanPerbaikan = $request->catatan_perbaikan ?? 'Tidak ada catatan tambahan';

                foreach ($risikoToRevise as $risk) {
                    $risk->update([
                        'status' => $update_status,
                        'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW,
                        'step_verification' => 1,
                    ]);

                    // TAMBAHAN: Simpan ke RiskNote per Risiko saat Officer Kirim Perbaikan
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

                if ($request->has('catatan_perbaikan') && !empty($request->catatan_perbaikan)) {
                    DataBatchNotes::create([
                        'data_batch_id' => $dataBatch->id,
                        'notes' => $request->catatan_perbaikan,
                        'step_order' => $dataBatch->step_verification,
                        'user_id' => auth()->id()
                    ]);
                }

                // NOTIFIKASI: Kirim ke Verifikator yang bersangkutan
                $targetNotif = '';
                if ($dataBatch->step_verification == 1) $targetNotif = 'RW_AP';
                elseif ($dataBatch->step_verification == 2) $targetNotif = 'RO_MR';
                elseif ($dataBatch->step_verification >= 3) $targetNotif = 'RW_MR';

                if ($targetNotif) {
                    $msg = 'Risk Officer AP telah mengirimkan perbaikan. Catatan: ' . $catatanPerbaikan;
                    $this->sendNotificationCustom($targetNotif, $unit_id, 'Perbaikan Risiko Dikirim', $msg, $targetLink, 'bx bx-refresh');
                }

                return redirect()->route('risk-register-ap.index', [
                    'pid' => $periode_id,
                    'unit_id' => $unit_id
                ])->with('success', 'Perbaikan risiko berhasil dilakukan. Risiko telah dikirim untuk diverifikasi.');
            }
            // B. LOGIC KIRIM BARU / LANJUT STEP
            else {
                if (!$dataBatch) {
                    $batch = 1;
                    $dataBatch = DataBatch::create([
                        'periode_id' => $periode_id,
                        'type' => 1,
                        'unit_id' => $unit_id,
                        'batch' => $batch,
                        'status' => DataBatch::STATUS_KIRIM,
                        'step_verification' => 1,
                        'finish' => false
                    ]);
                }
                else if (!$dataBatch->finish) {
                    if ($dataBatch->step_verification == null || $dataBatch->step_verification < 1) {
                        if ($dataBatch->status != DataBatch::STATUS_PROSES) {
                            return redirect()->route('risk-register-ap.index', ['pid' => $periode_id, 'unit_id' => $unit_id])
                                ->with('error', 'Masih ada data batch risiko yang sedang berproses.');
                        }
                        else {
                            if ($unit->unit_mr) {
                                $dataBatch->update([
                                    'status' => DataBatch::STATUS_VERIFIKASI,
                                    'step_verification' => 3,
                                    'finish' => false
                                ]);

                                IdentifikasiRisiko::where('unit_id', $unit_id)
                                    ->where('periode_id', $periode_id)
                                    ->where(function ($query) {
                                        $query->where('status', IdentifikasiRisiko::STATUS_INPUT_DATA)
                                              ->orWhereNull('status');
                                    })
                                    ->update([
                                        'status' => IdentifikasiRisiko::STATUS_DIKIRIM,
                                        'status_risiko' => 1,
                                        'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW,
                                        'step_verification' => 3
                                    ]);
                            } else {
                                $dataBatch->update([
                                    'status' => DataBatch::STATUS_KIRIM,
                                    'step_verification' => 1,
                                    'finish' => false
                                ]);
                            }
                            $dataBatch->refresh();
                        }
                    }
                    else {
                        if ($step_order >= $min_verification) {
                            $dataBatch->update([
                                'status' => DataBatch::STATUS_RANKING,
                                'finish' => false
                            ]);
                            $dataBatch->refresh();

                            $verifiedRisks = IdentifikasiRisiko::where('unit_id', $unit_id)
                                ->where('periode_id', $periode_id)
                                ->where('status', IdentifikasiRisiko::STATUS_TERVERIFIKASI)
                                ->with('riskAnalysis')
                                ->get();

                            $quantitativeRisks = $verifiedRisks->filter(function($risk) {
                                return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kuantitatif';
                            });

                            $qualitativeRisks = $verifiedRisks->filter(function($risk) {
                                return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kualitatif';
                            });

                            if ($quantitativeRisks->count() > 0) {
                                $avgExposure = $quantitativeRisks->avg(function($risk) {
                                    return $risk->riskAnalysis->eksposur_risiko ?? 0;
                                });

                                foreach ($quantitativeRisks as $risk) {
                                    if (($risk->riskAnalysis->eksposur_risiko ?? 0) > $avgExposure) {
                                        $risk->update([
                                            'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_RECOMMENDATION
                                        ]);
                                    }
                                }
                            }

                            foreach ($qualitativeRisks as $risk) {
                                if (($risk->riskAnalysis->skala_risiko ?? 0) >= 20) {
                                    $risk->update([
                                        'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_RECOMMENDATION
                                    ]);
                                }
                            }
                        }
                        else {
                            $dataBatch->update([
                                'status' => DataBatch::STATUS_VERIFIKASI,
                                'step_verification' => $dataBatch->step_verification + 1,
                                'finish' => false
                            ]);
                            $dataBatch->refresh();
                        }
                    }
                }
                else {
                    if ($dataBatch->finish) {
                        $batch = $dataBatch->batch + 1;
                        $dataBatch = DataBatch::create([
                            'periode_id' => $periode_id,
                            'type' => 1,
                            'unit_id' => $unit_id,
                            'batch' => $batch,
                            'status' => DataBatch::STATUS_KIRIM,
                            'step_verification' => 1,
                            'finish' => false
                        ]);
                    }
                }

                if ($dataBatch->status <= DataBatch::STATUS_KIRIM) {
                    IdentifikasiRisiko::where('unit_id', $unit_id)
                        ->where('periode_id', $periode_id)
                        ->where(function ($query) {
                            $query->where('status', IdentifikasiRisiko::STATUS_INPUT_DATA)
                                  ->orWhereNull('status');
                        })
                        ->update([
                            'status' => IdentifikasiRisiko::STATUS_DIKIRIM,
                            'status_risiko' => 1,
                            'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW,
                            'step_verification' => 1
                        ]);
                }

                // NOTIFIKASI: Eskalasi (Kirim ke tahap berikutnya)
                $targetNotif = '';
                if ($dataBatch->step_verification == 1) $targetNotif = 'RW_AP'; // Step 1: Ke Owner AP
                elseif ($dataBatch->step_verification == 2) $targetNotif = 'RO_MR'; // Step 2: Ke Officer MR
                elseif ($dataBatch->step_verification >= 3) $targetNotif = 'RW_MR'; // Step 3: Ke Owner MR

                if ($targetNotif) {
                    $this->sendNotificationCustom($targetNotif, $unit_id, 'Menunggu Verifikasi', 'Terdapat data risiko baru yang butuh verifikasi Anda.', $targetLink, 'bx bx-bell');
                }

                return redirect()->route('risk-register-ap.index', [
                    'pid' => $periode_id,
                    'unit_id' => $unit_id
                ])->with('success', 'Pengiriman risiko berhasil dilakukan. Risiko telah dikirim untuk diverifikasi.');
            }
        }
    }

    public function verifikasi(Request $request, $riskRegisterId)
    {
        $user = auth()->user();
        //$unit_id = $user->unit_id;
        $level_id = $user->level_id;
        // Cari data identifikasi risiko
        $identifikasiRisiko = IdentifikasiRisiko::findOrFail($riskRegisterId);
        $unit_id = $identifikasiRisiko->unit_id;
        $periode_id = $identifikasiRisiko->periode_id;
        $targetLink = route('risk-register-ap.index', ['pid' => $periode_id, 'unit_id' => $unit_id]);

        // $appFlow = $this->getFlowData($unit_id, $level_id);
        // $step_order = $appFlow['step_order'];
        // $min_verification = $appFlow['min_verification'];
        // $approval_step_id = $appFlow['approval_step_id'];

        //batch perlu tahu bahwa masih ada risiko yang dikembalikan sehingga batch menjadi on revision
        $dataBatch = DataBatch::where('unit_id', $identifikasiRisiko->unit_id)
        ->where('periode_id', $identifikasiRisiko->periode_id)
        ->where('type', 1) // type = 1 untuk unit/divisi
        ->orderBy('batch', 'desc')
        ->first();

        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = $this->getUserVerificationStep($level_id, $is_mr);
        $u_step = $verificationData['u_step'];
        $user_verification = $verificationData['user_verification'];
        $step_order = $u_step;
        $min_verification = 3;

        if ($step_order == 0 || $dataBatch->step_verification != $step_order) {
            return redirect()->route('risk-register-ap.index')
                ->with('error', 'Anda tidak memiliki hak untuk melakukan verifikasi risiko');
        }

        // Validasi input
        $validated = $request->validate([
            'catatan_verifikasi' => 'required|string',
            'status_verifikasi' => 'required|in:terima,tolak',
        ]);

        // dd($step_order, $verificationData);
        try {
            // Cek apakah user memiliki izin verifikator_risiko
            if (!Gate::check('risk_register_verification')) {
                return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melakukan verifikasi risiko');
            }

            // catat log disini
            Log::channel('verification')->info('Verifikasi risiko dengan ID: ' . $identifikasiRisiko->id . ' oleh user dengan ID: ' . auth()->id());
            Log::channel('verification')->info('Step Order: ' . $step_order);
            Log::channel('verification')->info('Min Verification: ' . $min_verification);

            // Update status risiko berdasarkan hasil verifikasi
            if ($validated['status_verifikasi'] === 'terima') {
                //catat log disini
                Log::channel('verification')->info('Verifikasi risiko dengan ID: ' . $identifikasiRisiko->id . ' diterima oleh user dengan ID: ' . auth()->id());

                //pengecekan jika step_order yang dimiliki level_id adalah sama dengan min_verification
                if($step_order >= $min_verification){
                    // Jika diterima, update status menjadi terverifikasi
                    $identifikasiRisiko->update([
                        'status' => IdentifikasiRisiko::STATUS_TERVERIFIKASI,
                        'status_progress' => IdentifikasiRisiko::PROGRESS_ON_ACCEPTED,
                        'status_risiko' => 1, //valid
                        'step_verification' => $step_order
                    ]);
                }
                else{
                    // Jika diterima, update status menjadi terverifikasi
                    $identifikasiRisiko->update([
                        'status' => IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI, //3
                        'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW, //1
                        'step_verification' => $step_order + 1
                    ]);
                }

                //disini maka akan simpan approval logs
                // ApprovalLog::create([
                //     'risk_id' => $riskRegisterId,
                //     'approval_step_id' => $approval_step_id,
                //     'type' => 1, // 1 untuk unit
                //     'step_order' => $step_order,
                //     'approved_by' => auth()->id(),
                //     'approved_at' => now(),
                // ]);

                //semua batch notes perlu diupdate sudah read jadi unread menjadi false
                $batchNotes = DataBatchNotes::where('data_batch_id', $dataBatch->id)
                    ->where('step_order', $step_order)
                    ->update([
                        'unread' => false
                    ]);
            }
            else {//ditolak
                //catat log disini
                Log::channel('verification')->info('Verifikasi risiko dengan ID: ' . $identifikasiRisiko->id . ' ditolak oleh user dengan ID: ' . auth()->id());

                // Jika ditolak, kembalikan ke status input data
                $identifikasiRisiko->update([
                    'status' => IdentifikasiRisiko::STATUS_REJECTED,
                    'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED // Kembali ke input data
                ]);

                $dataBatch->update([
                    'status' => DataBatch::STATUS_REVISI,
                ]);

                //semua batch notes perlu diupdate sudah read jadi unread menjadi false
                $batchNotes = DataBatchNotes::where('data_batch_id', $dataBatch->id)
                    ->where('step_order', $step_order)
                    ->update([
                        'unread' => false
                    ]);

                // Notifikasi kembalikan ke Drafter (Risk Officer Divisi) DAN Risk Owner Divisi
                $msg = 'Risiko ditolak dan dikembalikan untuk revisi. Catatan: ' . $validated['catatan_verifikasi'];
                $this->sendNotificationCustom('RO_AP', $unit_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');

                // Beritahu RW_AP agar bisa memonitor officer-nya
                $msgOwner = 'Terdapat risiko dari divisi Anda yang ditolak dan dikembalikan ke Drafter. Catatan: ' . $validated['catatan_verifikasi'];
                $this->sendNotificationCustom('RW_AP', $unit_id, 'Risiko Ditolak', $msgOwner, $targetLink, 'bx bx-x-circle');
            }

            // Simpan catatan verifikasi ke RiskNote
            $riskNote = new RiskNote([
                'risiko_id' => $riskRegisterId,
                'type' => 1, // 1 = unit/divisi
                'status' => $validated['status_verifikasi'] === 'terima' ? 1 : 2, // 1 = verifikasi, 2 = revisi/tolak
                'notes' => $validated['catatan_verifikasi'],
                'user_id' => auth()->id(),
            ]);

            $riskNote->save();

            // Redirect dengan pesan sukses
            return redirect()->route('risk-register-ap.index', [
                'pid' => $identifikasiRisiko->periode_id,
                'unit_id' => $identifikasiRisiko->unit_id
            ])
                ->with('success', 'Verifikasi risiko berhasil dilakukan');

        } catch (\Exception $e) {
            // Tangani kesalahan
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memverifikasi risiko: ' . $e->getMessage());
        }
    }

    /**
     * Mendapatkan step_order berdasarkan unit_id, periode_id, dan level_id
     *
     * @param int $unit_id ID unit
     * @param int $periode_id ID periode
     * @param int $level_id ID level
     * @return int step_order
     */
    private function getFlowData($unit_id, $level_id)
    {
        $step_order = 0;
        $min_verification = 3;
        $approval_step_id = null;

        // Jika bukan risk owner atau level_id null, kembalikan 0
        if ($level_id == 1 || $level_id == null) {
            return [
                'step_order' => $step_order,
                'min_verification' => $min_verification,
                'approval_step_id' => $approval_step_id
            ];
        }

        $approvalFlow = ApprovalFlow::where('unit_id', $unit_id)
            ->whereNull('project_id')
            ->first();

        if ($approvalFlow) {
            $min_verification = $approvalFlow->min_verification;
            $approvalStep = ApprovalStep::where('approval_flow_id', $approvalFlow->id)
                ->where('level_id', $level_id)
                ->first();
            if ($approvalStep) {
                $step_order = $approvalStep->step_order;
                $approval_step_id = $approvalStep->id;
            } else {
                //default step order
                if ($level_id == 2) {
                    $step_order = 1;
                } else if ($level_id == 3) {
                    $step_order = 2;
                } else if ($level_id == 4) {
                    $step_order = 3;
                }
            }
        } else {
            //default step order jika tidak ada approval flow
            if ($level_id == 2) {
                $step_order = 1;
            } else if ($level_id == 3) {
                $step_order = 2;
            } else if ($level_id == 4) {
                $step_order = 3;
            }
        }

        return [
            'step_order' => $step_order,
            'min_verification' => $min_verification,
            'approval_step_id' => $approval_step_id
        ];
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
              'riskAnalysis',
              'projectRisks.project',
              'projectRisks.projectRiskAnalisa',
              'projectRisks.penyebabRisikoProjects',
              'monitoringRisikos.skalaProbabilitas',
              'monitoringRisikos.skalaDampakObj',
              'monitoringRisikos.kriUnitMonitorings.keyRiskIndicator',
              'monitoringRisikos.perlakuanPenyebabMonitorings.perlakuanPenyebabRisikoUnit.penyebabRisiko',
              'monitoringRisikos.perlakuanDampakMonitorings.perlakuanDampak.dampakRisikoUnit',
              'monitoringRisikos.perlakuanPenyebabRisikoDocuments',
            ])
            ->get();

        $risiko = $risikos->first();

        $historyMonitorings = $risiko->monitoringRisikos->sortByDesc('id');

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

            $riskLimitPeriode = RisklimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
            if ($riskLimitPeriode) {
                $risk_limit = $riskLimitPeriode->risk_limit;
                $risk_tolerance = $riskLimitPeriode->risk_limit;
            }
        }

        return view('risk-register-ap.view', compact('user', 'risikos', 'risiko', 'riskMaps', 'formattedCurrentRiskMaps', 'risk_limit', 'risk_tolerance', 'historyMonitorings'));
    }

    private function getUserVerificationStep($level_id, $is_mr = false, $unit_mr = false)
    {
        $u_step = 0;
        $user_verification = "";

        if($level_id == 2) { // RO Divisi
            if($is_mr) { // RO Divisi MR
                $u_step = 3; // step verifikasi user
                $user_verification = "Risk Owner Manajemen Risiko";
            } else {
                $u_step = 1; // step verifikasi user
                $user_verification = "Risk Owner Anak Perusahaan";
            }
        }
        else if($level_id == 1 && $is_mr) { // RO MR
            if ($unit_mr) {
              $u_step = 0; // step kirim risiko khusus divisi MR
            } else {
              $u_step = 2; // step verifikasi user
            }
            $user_verification = "Risk Officer MR";
        }
        else{
            $u_step = 0;
            $user_verification = "Risk Officer Anak Perusahaan";
        }

        return [
            'u_step' => $u_step,
            'user_verification' => $user_verification
        ];
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

    public function bulkVerifikasi(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:identifikasi_risikos,id',
            'status_verifikasi' => 'required|in:terima,tolak',
            'catatan_verifikasi' => 'required|string',
        ]);

        $user = auth()->user();
        $jumlahData = count($request->ids);

        if($jumlahData == 0) {
            return response()->json(['message' => 'Tidak ada data yang dipilih'], 400);
        }

        // Cari data risiko pertama untuk acuan Unit dan Periode
        $firstRisk = IdentifikasiRisiko::find($request->ids[0]);
        $unit_id = $firstRisk->unit_id;
        $periode_id = $firstRisk->periode_id;
        $targetLink = route('risk-register-ap.index', ['pid' => $periode_id, 'unit_id' => $unit_id]);

        $unit = Unit::find($unit_id);
        $is_unit_mr = $unit ? ($unit->unit_mr == 1) : false;
        $min_verification = $is_unit_mr ? 1 : 3;

        $is_user_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        // Panggil helper verifikasi dengan 3 parameter
        $verificationData = $this->getUserVerificationStep($user->level_id, $is_user_mr, $is_unit_mr);
        $u_step = $verificationData['u_step'];

        DB::beginTransaction();
        try {
            // Cek Data Batch sekali saja di luar loop
            $dataBatch = DataBatch::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where('type', 1)
                ->orderBy('batch', 'desc')
                ->first();

            // Proteksi jika step user tidak sesuai dengan posisi batch
            if ($u_step == 0 || !$dataBatch || $dataBatch->step_verification != $u_step) {
                return response()->json(['message' => 'Anda tidak memiliki hak untuk melakukan verifikasi pada tahap ini.'], 403);
            }

            // Proses Update Risiko via Loop
            foreach ($request->ids as $id) {
                $risk = IdentifikasiRisiko::find($id);
                $this->processVerificationLogic($risk, $request->status_verifikasi, $request->catatan_verifikasi, $user, $u_step, $min_verification);
            }

            // Update status Batch Data CUKUP SEKALI di luar loop
            if ($request->status_verifikasi === 'tolak') {
                $dataBatch->update(['status' => DataBatch::STATUS_REVISI]);

                DataBatchNotes::where('data_batch_id', $dataBatch->id)
                    ->where('step_order', $u_step)
                    ->update(['unread' => false]);
            } else {
                // Jika Diterima, hapus status unread pada catatan sebelumnya
                DataBatchNotes::where('data_batch_id', $dataBatch->id)
                    ->where('step_order', $u_step)
                    ->update(['unread' => false]);
            }

            DB::commit();

            // if ($request->status_verifikasi === 'terima') {
            //     if ($u_step >= $min_verification) {
            //         // Notifikasi Final Approve
            //         $msg = "{$jumlahData} Risiko Anak Perusahaan disetujui penuh & menunggu Publish. Catatan: " . $request->catatan_verifikasi;
            //         $this->sendNotificationCustom('RO_AP', $unit_id, 'Verifikasi Masal Diterima', $msg, $targetLink, 'bx bx-check-double');
            //         $this->sendNotificationCustom('RW_AP', $unit_id, 'Verifikasi Masal Diterima', $msg, $targetLink, 'bx bx-check-double');
            //     } else {
            //         // Notifikasi Naik Step Verifikasi
            //         $nextStep = $u_step + 1;
            //         $targetNotif = '';
            //         if ($nextStep == 1) $targetNotif = 'RW_AP';
            //         if ($nextStep == 2) $targetNotif = 'RO_MR';
            //         if ($nextStep == 3) $targetNotif = 'RW_MR';

            //         if ($targetNotif) {
            //             $msg = "{$jumlahData} Risiko Anak Perusahaan telah lolos ke tahap Anda. Catatan: " . $request->catatan_verifikasi;
            //             $this->sendNotificationCustom($targetNotif, $unit_id, 'Verifikasi Masal Lanjutan', $msg, $targetLink, 'bx bx-info-circle');
            //         }
            //     }
            // } else {
            //     // Notifikasi Ditolak / Revisi
            //     $msg = "{$jumlahData} Risiko Anak Perusahaan ditolak secara masal. Catatan: " . $request->catatan_verifikasi;
            //     $this->sendNotificationCustom('RO_AP', $unit_id, 'Verifikasi Masal Ditolak', $msg, $targetLink, 'bx bx-x-circle');

            //     $msgOwner = "{$jumlahData} Risiko dari Anak Perusahaan Anda ditolak dan dikembalikan ke Drafter. Catatan: " . $request->catatan_verifikasi;
            //     $this->sendNotificationCustom('RW_AP', $unit_id, 'Verifikasi Masal Ditolak', $msgOwner, $targetLink, 'bx bx-x-circle');
            // }

            return response()->json(['message' => 'Berhasil memverifikasi ' . count($request->ids) . ' risiko Anak Perusahaan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }

    private function processVerificationLogic($risk, $status, $catatan, $user, $u_step, $min_verification)
    {
        if ($status === 'terima') {
            if($u_step >= $min_verification){
                $risk->update([
                    'status' => IdentifikasiRisiko::STATUS_TERVERIFIKASI,
                    'status_progress' => IdentifikasiRisiko::PROGRESS_ON_ACCEPTED,
                    'status_risiko' => 1, // SET AKTIF/VALID
                    'step_verification' => $u_step
                ]);
            } else {
                $risk->update([
                    'status' => IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI,
                    'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW,
                    'step_verification' => $u_step + 1
                ]);
            }
            $noteStatus = 1; // 1 = Terima
        } else {
            $risk->update([
                'status' => IdentifikasiRisiko::STATUS_REJECTED,
                'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED
            ]);
            $noteStatus = 2; // 2 = Tolak/Revisi
        }

        RiskNote::create([
            'risiko_id' => $risk->id,
            'type' => 1, // 1 untuk tabel risiko operasional/AP
            'status' => $noteStatus,
            'notes' => $catatan,
            'user_id' => $user->id,
        ]);
    }

    private function checkIsMyTurnRisk($batch, $u_step, $levelId) {
        if (!$batch || $batch->finish) return false;

        $userUnitId = auth()->user()->unit_id;
        $batchUnitId = $batch->unit_id;

        // Level 1: Inputter (Risk Officer Divisi)
        if ($levelId == 1 && $userUnitId == $batchUnitId) {
            return in_array($batch->status, [DataBatch::STATUS_PROSES, DataBatch::STATUS_REVISI, 0, 1, 5]);
        } else {
            // Jika statusnya REVISI (5), maka BUKAN giliran Verifikator
            if ($batch->status == DataBatch::STATUS_REVISI || $batch->status == 5) return false;
            return ($batch->step_verification == $u_step);
        }
    }

    // Helper untuk cek giliran verifikasi Monitoring
    private function checkIsMyTurnMon($mon, $levelId, $is_mr) {
        if (!$mon || $mon->status == 100 || $mon->is_approved) return false;
        $target = match (true) {
            $levelId == 1 && !$is_mr => 1, // Officer Divisi
            $levelId == 2 && !$is_mr => 2, // Owner Divisi
            $levelId == 1 && $is_mr  => 3, // Officer MR
            $levelId == 2 && $is_mr  => 4, // Owner MR
            default => 0
        };
        return ($mon->status == $target);
    }

    private function generateRiskStatusHtml($item, $u_step, $levelId)
    {
        $lastBatch = $item['last_batch'];
        $unitId = $item['unit']->id;
        $periodeId = $item['periode']->id;
        $userUnitId = auth()->user()->unit_id; // Ambil Unit ID user yang sedang login

        // 1. Cek Data Kosong
        if ($item['risk_count'] == 0) {
            return '<span class="badge bg-light text-dark border border-dark">Tidak Aktif</span>';
        }

        // 2. Logika Cek Published (Batch Finish ATAU Semua Item berstatus 6)
        $allRisksStatus = IdentifikasiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->pluck('status')
            ->toArray();

        $totalRisk = count($allRisksStatus);
        $publishedCount = count(array_filter($allRisksStatus, fn($s) => $s == IdentifikasiRisiko::STATUS_PUBLISHED));

        // Jika batch sudah finish ATAU (risiko ada dan semuanya sudah published)
        if (($lastBatch && $lastBatch->finish) || ($totalRisk > 0 && $totalRisk === $publishedCount)) {
            $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: Selesai</div>';

            return '<div class="d-flex flex-column align-items-start">
                        <span class="badge bg-success" data-bs-toggle="tooltip" title="Status: Published / Selesai">Published</span>
                        '.$positionHtml.'
                    </div>';
        }

        // 3. Logic Proses
        $batchStep = $lastBatch ? $lastBatch->step_verification : 0;
        $batchStatus = $lastBatch ? $lastBatch->status : 1;

        // Label Posisi
        $stepLabels = [
            0 => 'Risk Officer Anak Perusahaan (Draft)',
            1 => 'Risk Owner Anak Perusahaan',
            2 => 'Risk Officer MR',
            3 => 'Risk Owner MR',
        ];
        $currentLabel = $stepLabels[$batchStep] ?? 'Verifikator';

        // Jika status revisi (dikembalikan)
        if ($batchStatus == 5) {
            $currentLabel = 'Dikembalikan ke Risk Officer Anak Perusahaan';
        }

        // Subtitle Posisi
        $positionHtml = '
        <div class="mt-2 text-dark fw-bold" style="font-size: 11px;">
            Posisi: ' . $currentLabel . '
        </div>';

        // Cek Giliran
        $isMyTurn = false;
        // Jika user adalah level 1 dan di unit yang sama (Drafter)
        if ($levelId == 1 && $userUnitId == $unitId) {
            if (in_array($batchStatus, [1, 5, 0])) $isMyTurn = true;
        } else {
            // Verifikator
            if ($batchStatus != 5 && $u_step == $batchStep) {
                $isMyTurn = true;
            }
        }

        $pulseDot = '
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        $redirectUrl = route('risk-register-ap.index', ['pid' => $periodeId, 'unit_id' => $unitId]);

        // --- RENDER HTML ---

        // JIKA GILIRAN USER YANG BERSANGKUTAN
        if ($isMyTurn) {
            // --- KONDISI KHUSUS INPUTTER (Drafter Level 1) ---
            if ($levelId == 1 && $userUnitId == $unitId) {
                // KASUS REVISI (Status 5) -> MERAH HANYA UNTUK USER DI UNIT YANG SAMA
                if ($batchStatus == 5) {
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
                // KASUS DRAFT / BUKAN DI UNIT YANG SAMA -> BIRU (INFO)
                else {
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
            } else {
                // --- KONDISI VERIFIKATOR ---
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
        }

        // JIKA MENUNGGU (BUKAN GILIRANNYA / VIEW ONLY)

        // Jika posisinya sedang di-Revisi, tampilkan info sedang di Officer Divisi
        if ($batchStatus == 5 || $batchStatus == 1 || $batchStatus == 0) {
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

        // Jika posisinya sedang di Verifikator
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

    private function generateMonitoringStatusHtml($item, $levelId, $is_mr)
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

        // Jika tidak ada data sama sekali di bulan tersebut
        if ($rawMonitorings->isEmpty()) {
            return '<span class="badge bg-light text-dark border border-dark">Belum Dimonitor</span>';
        }

        // 2. FILTER: Ambil Hanya Data TERBARU per Risiko di bulan itu
        $currentMonitorings = $rawMonitorings
            ->groupBy('identifikasi_risiko_id')
            ->map(function ($items) {
                return $items->sortByDesc('id')->first();
            });

        // --- LOGIKA PENENTUAN STATUS KESELURUHAN ---
        $countTotal = $currentMonitorings->count();
        $countApproved = $currentMonitorings->where('status', 100)->count();

        $hasDraft = $currentMonitorings->where('status', 1)->isNotEmpty();
        $hasRevision = $currentMonitorings->where('status', 1)->where('is_revision', true)->isNotEmpty();
        $hasVerifROD = $currentMonitorings->where('status', 2)->isNotEmpty();
        $hasVerifROMR = $currentMonitorings->where('status', 3)->isNotEmpty();
        $hasVerifROWMR = $currentMonitorings->where('status', 4)->isNotEmpty();

        $redirectUrl = route('risk-register-ap.monitorings.index', [
            'period' => $periodeId,
            'unit_id' => $unitId,
            'month' => $selectedMonth
        ]);

        $pulseDot = '
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        $isMyMonTurn = false;
        $statusLabel = '';
        $badgeColor = 'bg-info';
        $labelPosisi = 'Verifikasi';
        $tooltipText = '';

        // --- SKENARIO 1: SELESAI SEMUA ---
        if ($countTotal > 0 && $countTotal === $countApproved) {
            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $bulanStr = $namaBulan[(int)$selectedMonth] ?? $selectedMonth;

            $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: Selesai</div>';
            return '<div class="d-flex flex-column align-items-start">
                        <span class="badge bg-success" data-bs-toggle="tooltip" title="Status: Monitoring Bulan '.$bulanStr.' Disetujui">Selesai ('.$bulanStr.')</span>
                        '.$positionHtml.'
                    </div>';
        }

        // --- SKENARIO 2: INPUTTER ---
        $is_unit_mr = $item['unit']->unit_mr == 1;
        if ($levelId == 1 && (!$is_mr || ($is_mr && $is_unit_mr)) && $hasDraft) {
            $isMyMonTurn = true;
            if ($hasRevision) {
                $statusLabel = 'Perlu Revisi';
                $badgeColor = 'bg-danger border border-danger text-white';
                $labelPosisi = 'Dikembalikan ke Risk Officer Anak Perusahaan';
                $tooltipText = 'Status: Dikembalikan. Mohon perbaiki data monitoring sesuai catatan.';
            } else {
                $statusLabel = 'Draft / Input Monitoring';
                $badgeColor = 'bg-info border border-info text-white';
                $labelPosisi = 'Risk Officer Anak Perusahaan';
                $tooltipText = 'Status: Draft Monitoring. Mohon lengkapi data.';
            }
        }
        // --- SKENARIO 3: VERIFIKATOR ---
        elseif ($levelId == 2 && !$is_mr && $hasVerifROD) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
            $labelPosisi = 'Risk Owner Anak Perusahaan';
        }
        elseif ($levelId == 1 && $is_mr && $hasVerifROMR) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
            $labelPosisi = 'Risk Officer MR';
        }
        elseif ($levelId == 2 && $is_mr && $hasVerifROWMR) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
            $labelPosisi = 'Risk Owner MR';
        }

        if ($isMyMonTurn && empty($tooltipText)) {
            $tooltipText = 'Klik untuk verifikasi monitoring: ' . $labelPosisi;
        }

        // --- RENDER HTML ---
        if (!$isMyMonTurn) {
            if ($hasDraft) $labelPosisi = ($hasRevision) ? 'Dikembalikan ke Risk Officer Anak Perusahaan' : 'Risk Officer Anak Perusahaan';
            elseif ($hasVerifROD) $labelPosisi = 'Risk Owner Anak Perusahaan';
            elseif ($hasVerifROMR) $labelPosisi = 'Risk Officer MR';
            elseif ($hasVerifROWMR) $labelPosisi = 'Risk Owner MR';

            $tooltipText = 'Posisi saat ini: ' . $labelPosisi;
        }

        $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: ' . $labelPosisi . '</div>';

        if ($isMyMonTurn) {
            return '
                <div class="d-flex flex-column align-items-start">
                    <a href="'.$redirectUrl.'" class="text-decoration-none align-self-center">
                        <span class="badge '.$badgeColor.' cursor-pointer position-relative" data-bs-toggle="tooltip" title="'.$tooltipText.'">
                            <i class="bx bx-radar bx-flashing me-1"></i> '.$statusLabel.'
                            '.$pulseDot.'
                        </span>
                    </a>
                    '.$positionHtml.'
                </div>';
        } else {
            return '
                <div class="d-flex flex-column align-items-start">
                    <div class="d-inline-block position-relative" data-bs-toggle="tooltip" title="'.$tooltipText.'">
                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                            <i class="bx bx-radar me-1"></i> Proses Monitoring
                        </span>
                    </div>
                    '.$positionHtml.'
                </div>';
        }
    }

    private function cleanInput($value)
    {
        if (empty($value)) return $value;

        // Regex ini berarti: GANTI semua karakter YANG BUKAN (^) a-z, A-Z, 0-9, spasi, dan simbol2 standar DENGAN string kosong.
        // Simbol yang dibolehkan: . , - _ ( ) / %
        return preg_replace('/[^a-zA-Z0-9\s\.\,\-\_\(\)\/\%]/', '', $value);
    }

    /**
     * Helper untuk mengirim notifikasi berdasarkan Role dan Unit
     * Target: RO_AP, RW_AP, RO_MR, RW_MR
     */
    private function sendNotificationCustom($target, $unitId, $title, $message, $link, $icon)
    {
        $users = collect();

        if ($target === 'RO_AP') {
            // Risk Officer AP: level 1, di unit yang sama
            $users = User::where('level_id', 1)->where('unit_id', $unitId)->get();
        }
        elseif ($target === 'RW_AP') {
            // Risk Owner AP: level 2, di unit yang sama
            $users = User::where('level_id', 2)->where('unit_id', $unitId)->get();
        }
        elseif ($target === 'RO_MR') {
            // Risk Officer MR: level 1, unit_mr = 1
            $users = User::where('level_id', 1)->whereHas('unit', function($q) {
                $q->where('unit_mr', 1);
            })->get();
        }
        elseif ($target === 'RW_MR') {
            // Risk Owner MR: level 2, unit_mr = 1
            $users = User::where('level_id', 2)->whereHas('unit', function($q) {
                $q->where('unit_mr', 1);
            })->get();
        }

        foreach ($users as $user) {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'title'   => $title,
                'message' => $message,
                'icon'    => $icon,
                'link'    => $link,
                'read_at' => null,
            ]);
        }
    }
}
