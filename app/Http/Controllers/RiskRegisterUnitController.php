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
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\RiskDivisiProject;
use App\Models\TaksonomiRisiko;
use App\Models\UnitRiskMonitoring;
use Illuminate\Support\Facades\DB;
use App\Models\PerlakuanDampakRisikoUnit;

class RiskRegisterUnitController extends Controller
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
        $viewAllDivision = Gate::check('view_all_division');

        if ($viewAllDivision) {
            $allowedUnitIds = Unit::where('unit_type_id', 1)->pluck('id');
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
        ])->where('unit_type_id', 1);

        if ($periodeId) $risikoQuery->where('periode_id', $periodeId);
        if ($unitId) $risikoQuery->where('unit_id', $unitId);

        $risiko = $risikoQuery
            ->join('risk_analyses', 'identifikasi_risikos.id', '=', 'risk_analyses.risiko_id')
            ->orderBy('risk_analyses.skala_risiko', 'desc')
            ->orderBy('risk_analyses.eksposur_risiko', 'desc')
            ->select('identifikasi_risikos.*')
            ->get();

        // 5. Data Filter View
        $unit = Unit::where('unit_type_id', 1)->pluck('name', 'id');
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
            'route' => route('risk-register-unit.send'),
            'parameters' => ['unit_id' => $unitId, 'periode_id' => $periodeId, 'send_type' => 'send']
        ];

        $today = Carbon::today();
        $isStillValid = !$selectedUnit?->valid_to || ($selectedUnit?->valid_to && ($selectedUnit->valid_to->isSameDay($today) || $selectedUnit->valid_to->isAfter($today)));
        $unitExpired = !$isStillValid;

        if (!$unitExpired && ($unitId == auth()->user()->unit_id || $viewAllDivision)) {

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
                                $summaryInfo = ['type' => 'danger', 'icon' => 'bx-undo', 'message' => "Ada <strong>{$pending_risk}</strong> risiko revisi. Silahkan edit."];
                                $escalationConfig['disabled'] = false;
                            } else {
                                $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Perbaikan selesai. Klik tombol di kanan atas."];
                                $escalationConfig['disabled'] = false;
                            }
                        } else {
                            if ($draft_risk > 0) {
                                $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Data siap dikirim."];
                                $escalationConfig['disabled'] = false;
                            } else {
                                $summaryInfo = ['type' => 'info', 'icon' => 'bx-info-circle', 'message' => "Belum ada data risiko."];
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
                            $summaryInfo = ['type' => 'danger', 'icon' => 'bx-undo', 'message' => "Perbaiki <strong>{$pending_risk}</strong> risiko revisi."];
                            $escalationConfig['disabled'] = false;
                        } else {
                            $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Perbaikan selesai."];
                            $escalationConfig['disabled'] = false;
                        }
                    } elseif ($status == DataBatch::STATUS_PROSES) {
                        if ($draft_risk > 0) {
                            $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Data siap dikirim."];
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
                            $summaryInfo = ['type' => 'warning', 'icon' => 'bxs-error-circle', 'message' => "Verifikasi <strong>{$pending_risk}</strong> risiko tersisa."];
                            $escalationConfig['disabled'] = true;
                        } else {
                            $summaryInfo = ['type' => 'success', 'icon' => 'bx-check-double', 'message' => "Siap dilanjutkan."];
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

        return view('risk-register-unit.index', compact(
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
        $taksonomiRisikos = TaksonomiRisiko::all();

        // Mendapatkan unit (divisi) saat ini
        $unit = Unit::find($unitId);

        // Mendapatkan daftar proyek yang berada di bawah divisi ini
        $projects = [];
        $projectRisks = [];

        if ($unit) {
            // Mendapatkan proyek berdasarkan cost_center_parent yang sama dengan cost_center unit
            $projects = Project::where('cost_center_parent', $unit->cost_center)->get();

            // Jika ada proyek, ambil risiko proyek yang memenuhi kriteria
            if ($projects->isNotEmpty()) {
                $projectIds = $projects->pluck('id')->toArray();

                // Ambil risiko proyek dengan status_risiko = 3 dan status = 6 (PUBLISHED)
                $projectRisks = ProjectRisk::whereIn('project_id', $projectIds)
                    ->where('status_risiko', 3)
                    ->where('status', ProjectRisk::STATUS_PUBLISHED)
                    ->with(['project', 'kategoriRisiko', 'projectRiskAnalisa'])
                    ->get();
            }
        }

        return view('risk-register-unit.create',compact('kategoriRisiko','peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings','areaDampak','jenisRisiko','tck','selectedPeriode', 'projects', 'projectRisks', 'taksonomiRisikos'));
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
        $user = auth()->user();
        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        // Ambil data verifikasi user saat ini
        $verificationData = $this->getUserVerificationStep($levelId, $is_mr);
        $u_step = $verificationData['u_step'];

        $viewAllDivision = Gate::check('view_all_division');
        $dataToDisplay = collect();

        // Query dasar untuk unit
        $unitQuery = Unit::where('unit_type_id', 1);
        $units = [];
        if (!$viewAllDivision) {
            $unitQuery->where('id', $user->unit_id);
            $units = Unit::where('unit_type_id', 1)->where('id', $user->unit_id)->pluck('name', 'id');
        } else {
            $units = Unit::where('unit_type_id', 1)->pluck('name', 'id');
        }
        $displayUnits = $unitQuery->get();

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
                    ->orderBy('id', 'desc')
                    ->first();

                $tmpData = [
                    'unit' => $unit,
                    'periode' => $selectedPeriode,
                    'risk_count' => $riskCount,
                    'last_batch' => $lastBatch,
                    'latest_mon' => $latestMon,
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

        return view('risk-register-unit.risk-period-list', compact(
          'periodes',
          'activePeriode',
          'selectedPeriode',
          'tableLegend',
          'dataToDisplay',
          'viewAllDivision',
          'units',
        ));
    }

    public function riskPeriodeDashboard(Request $request, $period)
    {
        $user    = request()->user()->load('unit');
        $periode = Periode::find($period);

        $targetUnitId = null;

        if (Gate::check('view_all_division') && $request->has('unit_id')) {
            $targetUnitId = $request->input('unit_id');
        } else {
            $targetUnitId = $user->unit_id;
        }

        $targetUnit = Unit::find($targetUnitId);

        if (!$targetUnit) {
            abort(404, 'Unit tidak ditemukan.');
        }

        $status = request()->query('status');

        $risikosQuery = IdentifikasiRisiko::where('periode_id', $period)
        ->where('unit_id', $targetUnit->id)
        ->with('riskAnalysis');

        if ($status === 'open') {
            $risikosQuery->where('is_closed', 0);
        } elseif ($status === 'closed') {
            $risikosQuery->where('is_closed', 1);
        }
        $risikos = $risikosQuery->get();

        $currentRiskMaps = $risikos->pluck('currentRiskMapsMonth');
        $formattedCurrentRiskMaps = [];
        foreach ($risikos as $idx => $risiko) {
            $currentValue = $risiko->currentRiskMapsMonth['inherent'];
            for ($month = 1; $month <= 12; $month++) {
                if ($nextValue = ($risiko->currentRiskMapsMonth[$month] ?? null)) {
                    $currentValue = $nextValue;
                }

                $currentValue['quarter'] = ceil($month / 3);
                $currentValue['month'] = $month;

                $formattedCurrentRiskMaps[$risiko->id][] = $currentValue;
            }
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        return view('risk-register-unit.risk-period-dashboard', compact('user', 'periode', 'risikos', 'riskMaps', 'formattedCurrentRiskMaps', 'targetUnit'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            // 'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'wbs' => 'nullable|string',
            'dampak_risiko' => 'required|array|min:1',
            'dampak_risiko.*' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
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
            // 'jenis_kontrol_eksisting_id' => 'nullable|exists:jenis_kontrol_eksistings,id',
            //'kontrol_eksisting_id' => 'nullable|array',
            //'kontrol_eksisting_id.*' => 'nullable|exists:kontrol_eksistings,id',
            //'kontrol_eksisting' => 'required|string',
            'kontrol_eksisting' => 'required|array',
            'kontrol_eksisting.*' => 'required|string',
            // 'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $peristiwa_risiko = $request->peristiwa_risiko;
        $unitId = auth()->user()->unit_id;
        if ($request->has('unit_id') && Gate::check('view_all_division')) {
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
            $identifikasiRisiko->target_capaian_kinerja = $request->target_capaian_kinerja;

            // Hilangkan jenis risiko dan kategori risiko
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
            // $identifikasiRisiko->taksonomi_risiko_id = $request->taksonomi_risiko_id;
            // $identifikasiRisiko->threshold_risk_limit = $this->cleanRupiah($request->threshold_risk_limit ?? 0);
            // $identifikasiRisiko->threshold_risk_appetite = $this->cleanRupiah($request->threshold_risk_appetite ?? 0);
            // $identifikasiRisiko->threshold_risk_tolerance = $this->cleanRupiah($request->threshold_risk_tolerance ?? 0);

            // Simpan kontrol eksisting
            // if ($request->has('kontrol_eksisting_id') && is_array($request->kontrol_eksisting_id)) {
            //     // Ubah array menjadi string dengan pemisah koma
            //     $kontrolEksisting = implode(',', $request->kontrol_eksisting_id);
            //     // Simpan ke field kontrol_eksisting
            //     $identifikasiRisiko->kontrol_eksisting = $kontrolEksisting;
            // }
            $identifikasiRisiko->save();

            // if ($request->has('param_nama')) {
            //     foreach ($request->param_nama as $idx => $nama) {
            //         if(!empty($nama)) {
            //             $identifikasiRisiko->parameterRisikos()->create([
            //                 'nama' => $nama,
            //                 'formula' => $request->param_formula[$idx] ?? '',
            //                 'satuan' => $request->param_satuan[$idx] ?? '',
            //             ]);
            //         }
            //     }
            // }

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

            $identifikasiRisiko->riskAnalysis()->create([]);
            $identifikasiRisiko->rencanaPerlakuanRisiko()->create([]);

            // Simpan relasi dengan risiko proyek jika ada
            if ($request->has('project_risk_ids') && is_array($request->project_risk_ids)) {
                foreach ($request->project_risk_ids as $projectRiskId) {
                    RiskDivisiProject::create([
                        'identifikasi_risiko_id' => $identifikasiRisiko->id,
                        'project_risk_id' => $projectRiskId
                    ]);
                }
            }

            // Tentukan redirect berdasarkan action
            $action = $request->input('action', 'save');

            if ($action === 'savenext') {
                // Redirect ke halaman analisis risiko
                return response()->json([
                    'message' => 'Data risiko berhasil disimpan',
                    'redirect' => route('risk-register-unit.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                // Redirect ke halaman index
                return response()->json([
                    'message' => 'Data risiko berhasil disimpan',
                    'redirect' => route('risk-register-unit.index', ['pid' => $request->periode_id, 'unit_id' => $unitId])
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

        return view('risk-register-unit.analisa', compact(
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

        return view('risk-register-unit.perencanaan', compact('identifikasiRisiko', 'analisa'));
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
        $validated = $request->validate([
            'risiko_id' => 'required|exists:identifikasi_risikos,id',
            'dampak_risiko_id' => 'required|exists:dampak_risiko_units,id',
            'rencana_perlakuan_risiko' => 'required',
            'output_perlakuan_risiko' => 'required',
            'biaya_perlakuan_risiko' => 'required|numeric',
            'pic' => 'required',
            'opsi_perlakuan_risiko' => 'required',
            'timeline_mulai_perlakuan_risiko' => 'required',
            'timeline_selesai_perlakuan_risiko' => 'required',
        ]);

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
            'timeline_perlakuan_risiko_start' => Carbon::createFromFormat('d/m/Y', $request->timeline_mulai_perlakuan_risiko)->format('Y-m-d'),
            'timeline_perlakuan_risiko_end' => Carbon::createFromFormat('d/m/Y', $request->timeline_selesai_perlakuan_risiko)->format('Y-m-d'),
        ]);

        return response()->json(['message' => 'Rencana Perlakuan Dampak berhasil ditambahkan!']);
    }

    public function editRencanaPerlakuanDampak($id)
    {
        $perlakuan = PerlakuanDampakRisikoUnit::with('risiko', 'dampakRisikoUnit')->findOrFail($id);

        return response()->json([
            'id'                => $perlakuan->id,
            'risiko_id'         => $perlakuan->risiko_id,
            'dampak_risiko_id' => $perlakuan->dampak_risiko_id,
            'deskripsi_dampak'  => $perlakuan->dampakRisikoUnit->dampak_risiko,
            'rencana_perlakuan_risiko'           => $perlakuan->rencana_perlakuan_risiko,
            'output_perlakuan_risiko'            => $perlakuan->output_perlakuan_risiko,
            'opsi_perlakuan_risiko'              => $perlakuan->opsi_perlakuan_risiko,
            'biaya_perlakuan_risiko'             => $perlakuan->biaya_perlakuan_risiko,
            'pic_jabatan_id'            => $perlakuan->pic_jabatan_id,
            'timeline_perlakuan_risiko_start'         => $perlakuan->timeline_perlakuan_risiko_start ? $perlakuan->timeline_perlakuan_risiko_start->format('d/m/Y') : null,
            'timeline_perlakuan_risiko_end'       => $perlakuan->timeline_perlakuan_risiko_end ? $perlakuan->timeline_perlakuan_risiko_end->format('d/m/Y') : null,
        ]);
    }

    public function updateRencanaPerlakuanDampak(Request $request, $id)
    {
        $validated = $request->validate([
            'xd_output_perlakuan_risiko' => 'required',
            'xd_output_perlakuan_risiko'  => 'required',
            'xd_opsi_perlakuan_risiko'    => 'required',
            'xd_biaya_perlakuan_risiko'   => 'required|numeric',
            'xd_pic'                      => 'required',
            'xd_divisi_terkait'           => 'nullable|array',
            'xd_timeline_mulai_perlakuan_risiko'   => 'required',
            'xd_timeline_selesai_perlakuan_risiko' => 'required',
        ]);

        $perlakuan = PerlakuanDampakRisikoUnit::findOrFail($id);
        $jabatan = Jabatan::find($request->xpic);

        $perlakuan->update([
            'rencana_perlakuan_risiko' => $validated['xd_output_perlakuan_risiko'],
            'output_perlakuan_risiko'  => $validated['xd_output_perlakuan_risiko'],
            'opsi_perlakuan_risiko'    => $validated['xd_opsi_perlakuan_risiko'],
            'biaya_perlakuan_risiko'   => $validated['xd_biaya_perlakuan_risiko'],
            'pic'                      => $jabatan?->name ?? '-',
            'pic_jabatan_id'           => $validated['xd_pic'],
            'divisi_terkait'           => $request->xd_divisi_terkait ?? [],
            'timeline_perlakuan_risiko_start' => Carbon::createFromFormat('d/m/Y', $validated['xd_timeline_mulai_perlakuan_risiko'])->format('Y-m-d'),
            'timeline_perlakuan_risiko_end'   => Carbon::createFromFormat('d/m/Y', $validated['xd_timeline_selesai_perlakuan_risiko'])->format('Y-m-d'),
        ]);

        return response()->json(['message' => 'Rencana perlakuan dampak berhasil diperbarui.']);
    }

    public function hapusRencanaPerlakuanDampak($id)
    {
        try {
            $perlakuan = PerlakuanDampakRisikoUnit::findOrFail($id);

            // Eksekusi penghapusan
            $perlakuan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Rencana perlakuan dampak berhasil dihapus.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
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
        if ($request->kategori_dampak == 'Kuantitatif') {

            $risk_limit = 0;


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

            $toMerge = [
                'skala_dampak' => $this->calculateSkalaDampak($request->nilai_dampak * 100 / $risk_limit),
            ];
            //echo "risk limit = " . $risk_limit . "\n";
            for ($i = 1; $i <= 4; $i++) {
                //echo "nilai_dampak_residual_q" . $i . " = " . $request->{'nilai_dampak_residual_q' . $i} . "\n";
                $calculateSkala = $this->calculateSkalaDampak($request->{'nilai_dampak_residual_q' . $i} * 100 / $risk_limit);
                //echo "skala dampak residual q" . $i . " = " . $calculateSkala . "\n";
                $toMerge['skala_dampak_residual_q' . $i] = $calculateSkala;
            }

            $request->merge($toMerge);
        }

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

            if ($request->{'skala_dampak_residual_q' . ($i + 1)} > $lastValues['skala_dampak']) {
                return response()->json([
                    'message' => 'Skala dampak residual q' . ($i + 1) . ' tidak boleh lebih besar dari ' . ($i ? 'q' . $i : 'inherent'),
                ], 422);
            }
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
            // Untuk kualitatif, gunakan skala dampak * skala probabilitas
            // Rumus: skalaDampak * (1/100) * (nilaiProbabilitas / 100) * riskTolerance
            $toUpdate['eksposur_risiko'] = floatval($toUpdate['skala_dampak']) * (1/100) * (floatval($toUpdate['nilai_probabilitas']) / 100) * ($riskLimitPeriode->risk_limit ?: 0);
        } else {
            // Untuk kuantitatif, gunakan nilai dampak * probabilitas
            // Rumus: (nilaiDampak * nilaiProbabilitas) / 100
            $toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * $toUpdate['nilai_probabilitas'];
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
            'redirect' => route('risk-register-unit.index', ['pid' => $identifikasiRisiko->periode_id])
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
        $taksonomiRisikos = TaksonomiRisiko::all();

        // Mendapatkan unit (divisi) saat ini
        $unit = Unit::find($unitId);

        // Mendapatkan daftar proyek yang berada di bawah divisi ini
        $projects = [];
        $projectRisks = [];

        if ($unit) {
            // Mendapatkan proyek berdasarkan cost_center_parent yang sama dengan cost_center unit
            $projects = Project::where('cost_center_parent', $unit->cost_center)->get();

            // Jika ada proyek, ambil risiko proyek yang memenuhi kriteria
            if ($projects->isNotEmpty()) {
                $projectIds = $projects->pluck('id')->toArray();

                // Ambil risiko proyek dengan status_risiko = 3 dan status = 6 (PUBLISHED)
                $projectRisks = ProjectRisk::whereIn('project_id', $projectIds)
                    ->where('status_risiko', 3)
                    ->where('status', ProjectRisk::STATUS_PUBLISHED)
                    ->with(['project', 'kategoriRisiko', 'projectRiskAnalisa', 'penyebabRisikoProjects'])
                    ->get();
            }
        }

        return view('risk-register-unit.edit', compact(
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
            'selectedPeriode',
            'projects',
            'projectRisks',
            'taksonomiRisikos'
        ));
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            // 'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'wbs' => 'nullable|string',
            'dampak_risiko' => 'required|array|min:1',
            'dampak_risiko.*' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
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
            // 'jenis_kontrol_eksisting_id' => 'nullable|exists:jenis_kontrol_eksistings,id',
            'kontrol_eksisting' => 'required|array',
            'kontrol_eksisting.*' => 'required|string',
            // 'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
        ]);

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
            $identifikasiRisiko->target_capaian_kinerja = $request->target_capaian_kinerja;

            // Hilangkan jenis risiko dan kategori risiko
            // $identifikasiRisiko->jenis_risiko_id = $request->jenis_risiko_id;
            // $jenisRisiko = \App\Models\JenisRisiko::find($request->jenis_risiko_id);
            // if ($jenisRisiko) {
            //     $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            // }

            $identifikasiRisiko->peristiwa_risiko = $request->peristiwa_risiko;
            $identifikasiRisiko->deskripsi_peristiwa_risiko = $request->deskripsi_peristiwa_risiko;
            $identifikasiRisiko->wbs = $request->wbs;
            // $identifikasiRisiko->jenis_kontrol_eksisting_id = $request->jenis_kontrol_eksisting_id;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            // $identifikasiRisiko->penilaian_efektifitas_kontrol = $request->penilaian_efektifitas_kontrol;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
            // $identifikasiRisiko->taksonomi_risiko_id = $request->taksonomi_risiko_id;
            // $identifikasiRisiko->threshold_risk_limit = $this->cleanRupiah($request->threshold_risk_limit);
            // $identifikasiRisiko->threshold_risk_appetite = $this->cleanRupiah($request->threshold_risk_appetite);
            // $identifikasiRisiko->threshold_risk_tolerance = $this->cleanRupiah($request->threshold_risk_tolerance);

            $identifikasiRisiko->save();

            // $savedParamIds = [];
            // if ($request->has('param_nama')) {
            //     foreach ($request->param_nama as $key => $nama) {
            //         $dataParam = [
            //             'nama' => $nama,
            //             'formula' => $request->param_formula[$key] ?? '',
            //             'satuan' => $request->param_satuan[$key] ?? '',
            //         ];

            //         $paramId = $request->parameter_risiko_id[$key] ?? null;
            //         $exist = $identifikasiRisiko->parameterRisikos()->find($paramId);

            //         if ($exist) {
            //             $exist->update($dataParam);
            //             $savedParamIds[] = $exist->id;
            //         } else {
            //             $newParam = $identifikasiRisiko->parameterRisikos()->create($dataParam);
            //             $savedParamIds[] = $newParam->id;
            //         }
            //     }
            // }
            // $identifikasiRisiko->parameterRisikos()->whereNotIn('id', $savedParamIds)->delete();

            // Hapus kontrol eksisting lama dan buat yang baru
            $identifikasiRisiko->kontrolEksistings()->delete();

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

            // // Hapus penyebab risiko lama dan buat yang baru
            // $identifikasiRisiko->penyebabRisiko()->delete();

            // // Simpan penyebab risiko
            // if ($request->has('penyebab_risiko') && is_array($request->penyebab_risiko)) {
            //     foreach ($request->penyebab_risiko as $penyebab) {
            //         if (!empty($penyebab)) {
            //             $identifikasiRisiko->penyebabRisiko()->create([
            //                 'penyebab_risiko' => $penyebab,
            //                 'risiko_id' => $identifikasiRisiko->id,
            //             ]);
            //         }
            //     }
            // }

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

            // Hapus relasi project risk lama dan buat yang baru
            $identifikasiRisiko->projectRisks()->detach();

            // Simpan project risk yang dipilih
            if ($request->has('project_risk_ids') && is_array($request->project_risk_ids)) {
                foreach ($request->project_risk_ids as $projectRiskId) {
                    if (!empty($projectRiskId)) {
                        $identifikasiRisiko->projectRisks()->attach($projectRiskId);
                    }
                }
            }

            // Tentukan redirect berdasarkan action
            $action = $request->input('action', 'save');

            if ($action === 'savenext') {
                // Redirect ke halaman analisis risiko
                return response()->json([
                    'message' => 'Data risiko berhasil diperbarui',
                    'redirect' => route('risk-register-unit.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                // Redirect ke halaman index
                return response()->json([
                    'message' => 'Data risiko berhasil diperbarui',
                    'redirect' => route('risk-register-unit.index', ['pid' => $request->periode_id])
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
            if (!Gate::check('risk_register_delete') && $identifikasiRisiko->user_id != auth()->id()) {
                return redirect()->route('risk-register-unit.index')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
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

            return redirect()->route('risk-register-unit.index')->with('success', 'Data risiko berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('risk-register-unit.index')->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    public function send(Request $request)
    {
        // 1. Setup Data Awal
        $user = auth()->user();
        $unit_id = $request->unit_id;
        $periode_id = $request->periode_id;

        if (!$unit_id) {
            return redirect()->route('risk-register-unit.index', [
                'pid' => $periode_id,
                'unit_id' => $user->unit_id
            ])->with('error', 'Unit belum dipilih. Silahkan pilih unit terlebih dahulu.');
        }

        if (!$periode_id) {
            return redirect()->route('risk-register-unit.index', [
                'unit_id' => $unit_id
            ])->with('error', 'Periode tidak ditemukan');
        }

        $level_id = $user->level_id;
        $send_type = $request->send_type ?? 'send';

        // 2. VALIDASI KELENGKAPAN DATA (Mirip Project)
        // Hanya validasi jika bukan 'mainrisk' (Publish) atau bisa juga divalidasi saat publish tergantung kebutuhan
        if ($send_type !== 'mainrisk') {
            // Ambil risiko yang aktif (bukan closed) dan belum published
            $risikos = IdentifikasiRisiko::with([
                'riskAnalysis',
                'penyebabRisikos.perlakuanPenyebabRisikoUnit',
                'dampakRisikos',
                'perlakuanDampakRisikos'
            ])
            ->where('unit_id', $unit_id)
            ->where('periode_id', $periode_id)
            ->where(function ($query) {
                $query->where('status', '!=', 6)
                      ->orWhereNull('status');
            })
            ->where('is_closed', 0)
            ->get();

            if ($risikos->isEmpty()) {
                 // Jika tidak ada data sama sekali tapi mau kirim (kasus jarang, biasanya draft 0)
                 // Biarkan lewat atau return error tergantung logic, disini kita return error jika kosong
                 // Kecuali jika memang batch sudah jalan.
                return redirect()->back()->with('error', 'Tidak ada risiko yang dapat dikirim.');
            }

            // Array Penampung Error
            $errBelumAnalisa = [];
            $errBelumAdaPerlakuanPenyebab = [];
            $errBelumAdaDampak = [];
            $errBelumAdaPerlakuanDampak = [];

            foreach ($risikos as $risiko) {
                // Ambil deskripsi untuk pesan error
                $deskripsi = $risiko->peristiwa_risiko;
                if ($risiko->peristiwaRisiko) {
                    $deskripsi = $risiko->peristiwaRisiko->title;
                }

                // A. Cek Analisa Risiko
                if (!$risiko->riskAnalysis) {
                    $errBelumAnalisa[] = $deskripsi;
                }

                // B. Cek Perlakuan Penyebab
                // Jika penyebab ada, perlakuan harus ada
                if ($risiko->penyebabRisikos->isNotEmpty()) {
                    foreach ($risiko->penyebabRisikos as $penyebab) {
                        if ($penyebab->perlakuanPenyebabRisikoUnit->isEmpty()) {
                            $errBelumAdaPerlakuanPenyebab[] = $deskripsi;
                            break;
                        }
                    }
                }

                // C. Cek Dampak Risiko (Wajib ada minimal 1 dampak)
                if ($risiko->dampakRisikos->isEmpty()) {
                    $errBelumAdaDampak[] = $deskripsi;
                } else {
                    // D. Cek Perlakuan Dampak
                    // Logika: Setiap ID Dampak harus punya ID Perlakuan yang sesuai
                    // Di model Unit, PerlakuanDampak ada di IdentifikasiRisiko (hasMany) dengan foreign key dampak_risiko_id

                    $impactIds = $risiko->dampakRisikos->pluck('id')->toArray();
                    $treatedImpactIds = $risiko->perlakuanDampakRisikos->pluck('dampak_risiko_id')->toArray();

                    // Cek apakah ada Impact ID yang tidak ada di Treated Impact IDs
                    $untreatedImpacts = array_diff($impactIds, $treatedImpactIds);

                    if (!empty($untreatedImpacts)) {
                        $errBelumAdaPerlakuanDampak[] = $deskripsi;
                    }
                }
            }

            // Susun Pesan Error HTML
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

            // Jika ada error, redirect back
            if (!empty($pesanError)) {
                $pesanError .= 'Silahkan lengkapi data tersebut terlebih dahulu.';
                return redirect()->route('risk-register-unit.index', [
                    'pid' => $periode_id,
                    'unit_id' => $unit_id
                ])->with('error', $pesanError);
            }
        }

        // 3. Logic Batch & Step (Original Logic)
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

                // Buat batch baru untuk periode berikutnya (opsional, tergantung flow)
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

            // Tetapkan project risk utama
            IdentifikasiRisiko::determineMainRisks($unit_id, $periode_id);

            return redirect()->route('risk-register-unit.index', [
                'pid' => $periode_id,
                'unit_id' => $unit_id
            ])->with('success', 'Risiko berhasil dipublish');
        }

        // --- SKENARIO 2: KIRIM BIASA / REVISI ---
        else {
            // Cek Batch
            $dataBatch = DataBatch::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where('type', 1)
                ->orderBy('batch', 'desc')
                ->first();

            // A. LOGIC REVISI (Kirim Perbaikan)
            if ($send_type == 'rev') {
                if ($dataBatch->step_verification == 1) {
                    // Jika revisi dari tahap awal, kirim balik ke status Dikirim
                    $dataBatch->update([
                        'status' => DataBatch::STATUS_KIRIM,
                        'finish' => false
                    ]);
                    $update_status = IdentifikasiRisiko::STATUS_DIKIRIM;
                } else {
                    // Jika revisi dari tahap tengah, kirim ke status Tunggu Verifikasi
                    $dataBatch->update([
                        'status' => DataBatch::STATUS_VERIFIKASI,
                        'finish' => false
                    ]);
                    $update_status = IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI;
                }
                $dataBatch->refresh();

                // Update risiko yang statusnya REJECTED atau INPUT menjadi DIKIRIM/VERIFIKASI
                IdentifikasiRisiko::where('unit_id', $unit_id)
                    ->where('periode_id', $periode_id)
                    ->whereIn('status', [
                        IdentifikasiRisiko::STATUS_INPUT_DATA,
                        IdentifikasiRisiko::STATUS_REJECTED
                    ])
                    ->update([
                        'status' => $update_status,
                        'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW
                    ]);

                // Isi Batch Notes jika ada
                if ($request->has('catatan_perbaikan') && !empty($request->catatan_perbaikan)) {
                    DataBatchNotes::create([
                        'data_batch_id' => $dataBatch->id,
                        'notes' => $request->catatan_perbaikan,
                        'step_order' => $dataBatch->step_verification,
                        'user_id' => auth()->id()
                    ]);
                }

                return redirect()->route('risk-register-unit.index', [
                    'pid' => $periode_id,
                    'unit_id' => $unit_id
                ])->with('success', 'Perbaikan risiko berhasil dilakukan. Risiko telah dikirim untuk diverifikasi.');
            }

            // B. LOGIC KIRIM BARU / LANJUT STEP
            else {
                // Buat Batch Baru jika belum ada
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
                    // Jika batch ada dan belum finish
                    if ($dataBatch->step_verification == null || $dataBatch->step_verification < 1) {
                        // Pengiriman Pertama Kali dari Draft
                        if ($dataBatch->status != DataBatch::STATUS_PROSES) {
                            return redirect()->route('risk-register-unit.index', ['pid' => $periode_id, 'unit_id' => $unit_id])
                                ->with('error', 'Masih ada data batch risiko yang sedang berproses.');
                        }
                        else {
                            if ($unit->unit_mr) {
                                // Khusus Divisi MR: Langsung ke Step 3 (Officer MR -> Owner MR)
                                $dataBatch->update([
                                    'status' => DataBatch::STATUS_VERIFIKASI,
                                    'step_verification' => 3,
                                    'finish' => false
                                ]);

                                // Update Risiko
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
                                // Divisi Biasa: Ke Step 1
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
                        // Lanjut ke Step Berikutnya (Verifikasi Berjenjang)
                        if ($step_order >= $min_verification) {
                            // Jika sudah di level akhir verifikasi sebelum publish
                            $dataBatch->update([
                                'status' => DataBatch::STATUS_RANKING,
                                'finish' => false
                            ]);
                            $dataBatch->refresh();

                            // Logic Rekomendasi Risiko (Sama seperti kode asli)
                            $verifiedRisks = IdentifikasiRisiko::where('unit_id', $unit_id)
                                ->where('periode_id', $periode_id)
                                ->where('status', IdentifikasiRisiko::STATUS_TERVERIFIKASI)
                                ->with('riskAnalysis')
                                ->get();

                            if($step_order >= $min_verification){
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

                                    // Tandai risiko kuantitatif yang nilainya di atas rata-rata
                                    foreach ($quantitativeRisks as $risk) {
                                        if (($risk->riskAnalysis->eksposur_risiko ?? 0) > $avgExposure) {
                                            $risk->update([
                                                'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_RECOMMENDATION
                                            ]);
                                        }
                                    }
                                }

                                // Untuk risiko kualitatif, tandai yang nilai risikonya >= 20
                                foreach ($qualitativeRisks as $risk) {
                                    if (($risk->riskAnalysis->skala_risiko ?? 0) >= 20) {
                                        $risk->update([
                                            'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_RECOMMENDATION
                                        ]);
                                    }
                                }
                            }
                        }
                        else {
                            // Naik Step
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
                    // Jika Batch sebelumnya sudah finish, buat batch baru
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

                // Update Status Risiko Masal (Untuk Pengiriman Awal)
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

                return redirect()->route('risk-register-unit.index', [
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
            return redirect()->route('risk-register-unit.index')
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
            return redirect()->route('risk-register-unit.index', [
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
        // $risikos = IdentifikasiRisiko::where('id', $id)
        //     ->with('riskAnalysis')
        //     ->get();

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

            $riskLimitPeriode = RisklimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
            if ($riskLimitPeriode) {
                $risk_limit = $riskLimitPeriode->risk_limit;
                $risk_tolerance = $riskLimitPeriode->risk_limit;
            }
        }

        // dd($risk_limit, $risk_tolerance);
        return view('risk-register-unit.view', compact('user', 'risikos', 'risiko', 'riskMaps', 'formattedCurrentRiskMaps', 'risk_limit', 'risk_tolerance'));
    }

    public function confirmCorporateRisks(Request $request)
    {
        // Validasi request
        $request->validate([
            'periode_id' => 'required|exists:periodes,id'
        ]);

        $periodeId = $request->periode_id;
        $unitId = Unit::where('unit_type_id', 4)->first()?->id;

        // Update status DataBatch menjadi STATUS_FINISH (8)
        $dataBatch = DataBatch::where('periode_id', $periodeId)
            ->where('unit_id', $unitId)
            ->where('type', 1)
            ->orderBy('batch', 'desc')
            ->first();

        if ($dataBatch) {
            $dataBatch->update([
                'status' => DataBatch::STATUS_FINISH,
                'finish' => true,
            ]);

            DataBatch::create([
                'unit_id' => $unitId,
                'periode_id' => $periodeId,
                'type' => 1,
                'batch' => $dataBatch->batch + 1,
                'status' => DataBatch::STATUS_FINISH,
                'step_verification' => 0,
                'finish' => false,
            ]);
        }
        // Update is_proyek menjadi 1 untuk risiko dengan status_risiko corporate
        IdentifikasiRisiko::where('periode_id', $periodeId)
            ->where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_CORPORATE)
            ->update(['is_proyek' => 1]);

        return redirect()->route('corporate-risk.index', ['pid' => $periodeId])
            ->with('success', 'Konfirmasi risiko corporate berhasil dilakukan.');
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
                $user_verification = "Risk Owner Divisi";
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
            $user_verification = "Risk Officer Divisi";
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
            'status_verifikasi' => 'required|in:terima,tolak',
            'catatan_verifikasi' => 'required|string',
        ]);

        $user = auth()->user();

        DB::beginTransaction();
        try {
            foreach ($request->ids as $id) {
                $risk = IdentifikasiRisiko::findOrFail($id);
                $this->processVerificationLogic($risk, $request->status_verifikasi, $request->catatan_verifikasi, $user);
            }
            DB::commit();
            return response()->json(['message' => 'Berhasil memverifikasi ' . count($request->ids) . ' risiko divisi.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    private function processVerificationLogic($risk, $status, $catatan, $user)
    {
        $unit = Unit::find($risk->unit_id);
        $is_unit_mr = $unit->unit_mr == 1;

        $verificationData = $this->getUserVerificationStep($user->level_id, $is_unit_mr);
        $u_step = $verificationData['u_step'];

        // Min Verification: Unit MR = 1, Biasa = 3
        $min_verification = $is_unit_mr ? 1 : 3;

        $dataBatch = DataBatch::where('unit_id', $risk->unit_id)
            ->where('periode_id', $risk->periode_id)
            ->where('type', 1)->orderBy('batch', 'desc')->first();

        if ($status === 'terima') {
            // Jika user di step terakhir (siap publish)
            if($u_step >= $min_verification){
                $risk->update([
                    'status' => IdentifikasiRisiko::STATUS_TERVERIFIKASI, // Status 4 (Verified/Ready to Publish)
                    'status_progress' => IdentifikasiRisiko::PROGRESS_ON_ACCEPTED,
                    'step_verification' => $u_step
                ]);
            } else {
                // Lanjut ke verifikator berikutnya (Unit Biasa)
                $risk->update([
                    'status' => IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI,
                    'step_verification' => $u_step + 1
                ]);
            }
            $noteStatus = 1;
        } else {
            // DITOLAK -> REVISI
            $risk->update([
                'status' => IdentifikasiRisiko::STATUS_REJECTED,
                'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED
            ]);

            if($dataBatch) $dataBatch->update(['status' => DataBatch::STATUS_REVISI]);
            $noteStatus = 2;
        }

        RiskNote::create([
            'risiko_id' => $risk->id,
            'type' => 1,
            'status' => $noteStatus,
            'notes' => $catatan,
            'user_id' => $user->id,
        ]);
    }

    private function checkIsMyTurnRisk($batch, $u_step, $levelId) {
        if (!$batch || $batch->finish) return false;
        if ($levelId == 1 && !$u_step) return in_array($batch->status, [DataBatch::STATUS_PROSES, DataBatch::STATUS_REVISI]);
        return ($batch->step_verification == $u_step);
    }

    // Helper untuk cek giliran verifikasi Monitoring
    private function checkIsMyTurnMon($mon, $levelId, $is_mr) {
        if (!$mon || $mon->status == 100) return false;
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

        if ($item['risk_count'] == 0) {
            return '<span class="badge bg-light text-dark border border-dark">Tidak Aktif</span>';
        }

        // 2. Logika Cek Published (Batch Finish ATAU Semua Item berstatus 6)
        // Ambil status semua risiko untuk unit & periode ini
        $allRisksStatus = IdentifikasiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->pluck('status')
            ->toArray();

        $totalRisk = count($allRisksStatus);
        $publishedCount = count(array_filter($allRisksStatus, fn($s) => $s == IdentifikasiRisiko::STATUS_PUBLISHED));

        // Jika batch sudah finish ATAU (risiko ada dan semuanya sudah published)
        if (($lastBatch && $lastBatch->finish) || ($totalRisk > 0 && $totalRisk === $publishedCount)) {
            return '<span class="badge bg-success">Published</span>';
        }

        $batchStep = $lastBatch ? $lastBatch->step_verification : 0;
        $batchStatus = $lastBatch ? $lastBatch->status : 1;

        $stepLabels = [
            0 => 'Risk Officer Divisi (Draft)',
            1 => 'Risk Owner Divisi',
            2 => 'Risk Officer MR',
            3 => 'Risk Owner MR',
        ];

        $currentPos = $stepLabels[$batchStep] ?? 'Verifikator';
        if ($batchStatus == DataBatch::STATUS_REVISI) $currentPos = "Revisi: Kembali ke " . ($stepLabels[0]);

        $pulse = '<span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
                  <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        $redirectUrl = route('risk-register-unit.index', ['pid' => $item['periode']->id, 'unit_id' => $item['unit']->id]);

        // LOGIKA TAMPILAN JIKA GILIRAN USER INI
        if ($item['is_my_turn_risk']) {
            // A. Jika User adalah Officer Divisi (Drafter/Level 1) dan batch di step 0
            if ($levelId == 1 && $batchStep == 0) {
                return '<a href="'.$redirectUrl.'" class="text-decoration-none">
                            <span class="badge bg-info text-white position-relative" data-bs-toggle="tooltip"
                                  title="Status: Draft/Revisi. Mohon lengkapi atau perbaiki data risiko.">
                                Draft / Perlu Revisi '.$pulse.'
                            </span>
                        </a>';
            }
            // B. Jika User adalah Verifikator (Owner Divisi / MR)
            else {
                return '<a href="'.$redirectUrl.'" class="text-decoration-none">
                            <span class="badge bg-warning text-dark position-relative" data-bs-toggle="tooltip"
                                  title="Klik untuk verifikasi: '.$currentPos.'">
                                <i class="bx bx-error-circle bx-flashing me-1"></i> Perlu Verifikasi '.$pulse.'
                            </span>
                        </a>';
            }
        }

        // KONDISI 2: BUKAN GILIRAN SAYA (Tapi saya punya peran dalam alur ini)
        // Cek apakah levelId user termasuk dalam alur (Level 1 atau 2)
        if (in_array($levelId, [1, 2])) {
            return '<div class="d-inline-block position-relative" data-bs-toggle="tooltip" title="Posisi saat ini: '.$currentPos.'">
                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                            <i class="bx bx-time-five me-1"></i> Proses Validasi
                        </span>
                    </div>';
        }

        // TAMPILAN MENUNGGU (Waiting)
        return '<span class="badge bg-info bg-opacity-10 text-info border border-info position-relative" data-bs-toggle="tooltip" title="Posisi saat ini: '.$currentPos.'">
                <i class="bx bx-info-circle me-1"></i> Sedang Diproses
            </span>';
    }

    private function generateMonitoringStatusHtml($item, $levelId, $is_mr)
    {
        $latestMon = $item['latest_mon'];

        if (!$latestMon) {
            return '<span class="badge bg-light text-dark border border-dark">Belum Dimonitor</span>';
        }

        if ($latestMon->status == 100) {
            return '<span class="badge bg-success">Selesai</span>';
        }

        $monLabels = [
            1 => 'Risk Officer Divisi (Drafting)',
            2 => 'Risk Owner Divisi',
            3 => 'Risk Officer MR',
            4 => 'Risk Owner MR'
        ];
        $currentPos = $monLabels[$latestMon->status] ?? 'Verifikasi';

        $pulse = '<span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
                  <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        $redirectUrl = route('risk-register-unit.monitorings.index', ['period' => $item['periode']->id, 'unit_id' => $item['unit']->id]);

        if ($item['is_my_turn_mon']) {
            // A. Jika User adalah Officer Divisi (Drafter Monitoring)
            if ($levelId == 1 && !$is_mr) {
                return '<a href="'.$redirectUrl.'" class="text-decoration-none">
                            <span class="badge bg-info text-white position-relative" data-bs-toggle="tooltip"
                                  title="Status: Draft Monitoring. Mohon lengkapi atau perbaiki data.">
                                Draft / Perlu Revisi '.$pulse.'
                            </span>
                        </a>';
            }
            // B. Jika User adalah Verifikator Monitoring
            else {
                return '<a href="'.$redirectUrl.'" class="text-decoration-none">
                            <span class="badge bg-warning text-dark position-relative" data-bs-toggle="tooltip"
                                  title="Klik untuk verifikasi monitoring: '.$currentPos.'">
                                <i class="bx bx-radar bx-flashing me-1"></i> Verifikasi Mon. '.$pulse.'
                            </span>
                        </a>';
            }
        }

        // KONDISI 2: BUKAN GILIRAN SAYA (Tapi masuk alur verifikasi)
        if (in_array($levelId, [1, 2])) {
            return '<div class="d-inline-block position-relative" data-bs-toggle="tooltip" title="Posisi saat ini: '.$currentPos.'">
                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                            <i class="bx bx-radar me-1"></i> Proses Monitoring
                        </span>
                    </div>';
        }

        // KONDISI 3: USER LUAR / HANYA VIEW
        return '<span class="badge bg-info bg-opacity-10 text-info border border-info position-relative" data-bs-toggle="tooltip" title="Posisi saat ini: '.$currentPos.'">
                    <i class="bx bx-radar me-1"></i> Sedang Diproses
                </span>';
    }
}
