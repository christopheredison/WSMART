<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectPeriodeList;
use App\Models\ProjectRisk;
use App\Models\ProjectRiskMonitoring;
use App\Models\IdentifikasiRisiko;
use App\Models\UnitRiskMonitoring;
use App\Models\Periode;
use App\Models\Unit;
use App\Models\DataBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Menampilkan daftar Task Approval (Project & Divisi)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        // Load relasi projects (assignment) dan unit
        $user->load('projects', 'unit');

        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        // Cek apakah user adalah Risk Officer / Owner Project (Hanya untuk UI Tampilan)
        $isProjectUser = in_array($levelId, [6, 7]);

        $search = $request->query('q');
        $scope  = $request->query('scope', 'all'); // options: all, project, divisi
        $currentYear = date('Y');

        // Ambil Periode Aktif untuk Data Divisi
        $activePeriode = Periode::where('status', 'active')->first();
        $activePeriodeId = $activePeriode ? $activePeriode->id : null;

        $taskList = [];
        $pendingItems = [];

        // Statistik Global untuk Header View
        $stats = [
            'project' => ['total' => 0, 'pending' => 0, 'approved' => 0],
            'units'   => [] // Key: Nama Unit, Value: Status
        ];

        // ============================================================
        // 0. PREPARE PROJECT IDS (Logic Permission)
        // ============================================================
        // 1. Project yang di-assign langsung (Pivot user_projects)
        $userProjectIds = $user->projects->pluck('id');

        // 2. Project dibawah Unit/Divisi (Jika punya permission)
        $unitProjectIds = collect([]);
        if ($user->unit && Gate::check('can_access_project_under_division')) {
            $unitProjectIds = $user->unit->projects()->pluck('id');
        }

        // 3. Gabungkan dan Unique
        $allProjectIds = $userProjectIds->merge($unitProjectIds)->unique();

        // ============================================================
        // 1. LOGIC PROJECT (Jika scope all atau project)
        // ============================================================
        if ($scope == 'all' || $scope == 'project') {
            // Approval Flow Project (Standard 5 Step)
            $projectFlow = [
                1 => ['label' => 'Drafting',   'role' => 'Risk Officer Project'],
                2 => ['label' => 'Review',     'role' => 'Risk Owner Project'],
                3 => ['label' => 'Verifikasi', 'role' => 'Risk Officer Divisi'],
                4 => ['label' => 'Validasi',   'role' => 'Risk Officer MR'],
                5 => ['label' => 'Finalisasi', 'role' => 'Risk Owner MR']
            ];

            $projects = ProjectPeriodeList::with(['project', 'periode'])
                ->where(function ($q) use ($user, $allProjectIds) {
                    // Jika BUKAN Super Admin / Project Admin, terapkan filter
                    if (!Gate::check('project_admin_access')) {
                        $q->whereHas('project', function($p) use ($allProjectIds) {
                            $p->whereIn('id', $allProjectIds);
                        });
                    }
                })
                ->when($search, function($query) use ($search) {
                    return $query->whereHas('project', function($p) use ($search) {
                        $p->where('project_name', 'LIKE', '%' . $search . '%')
                          ->orWhere('project_code', 'LIKE', '%' . $search . '%');
                    });
                })
                ->get();

            foreach ($projects as $ppl) {
                $taskList[] = $this->processProjectTask($ppl, $user, $levelId, $is_mr, $currentYear, $projectFlow, $stats, $pendingItems);
            }
        }

        // ============================================================
        // 2. LOGIC DIVISI (Jika scope all atau divisi)
        // ============================================================
        // Tambahkan pengecekan !$isProjectUser agar level 6/7 tidak melihat divisi
        if (!$isProjectUser && ($scope == 'all' || $scope == 'divisi') && $activePeriodeId) {

            // Query Unit berdasarkan permission
            $units = Unit::where('unit_type_id', 1)
                ->when(!Gate::check('view_all_division'), function($q) use ($user) {
                    // Jika user biasa, hanya unit sendiri
                    $q->where('id', $user->unit_id);
                })
                ->when($search, function($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%');
                })
                ->get();

            foreach ($units as $unit) {
                // Tentukan Flow Approval Divisi (Beda antara Unit MR dan Unit Biasa)
                if ($unit->unit_mr) {
                    // Flow Khusus Unit MR (2 Step)
                    $divisiFlow = [
                        1 => ['label' => 'Drafting',   'role' => 'Risk Officer MR'],
                        2 => ['label' => 'Finalisasi', 'role' => 'Risk Owner MR']
                    ];
                } else {
                    // Flow Standard (4 Step)
                    $divisiFlow = [
                        1 => ['label' => 'Drafting',   'role' => 'Risk Officer Divisi'],
                        2 => ['label' => 'Review',     'role' => 'Risk Owner Divisi'],
                        3 => ['label' => 'Verifikasi', 'role' => 'Risk Officer MR'],
                        4 => ['label' => 'Validasi',   'role' => 'Risk Owner MR']
                    ];
                }

                $taskList[] = $this->processUnitTask($unit, $activePeriodeId, $user, $levelId, $is_mr, $divisiFlow, $stats, $pendingItems);
            }
        }

        // Sorting Global: Urgent > Pending > Safe
        usort($taskList, function ($a, $b) {
            $priority = ['urgent' => 3, 'pending' => 2, 'safe' => 1];
            return $priority[$b['status_category']] <=> $priority[$a['status_category']];
        });

        // Hitung Summary Statistik Divisi
        $divisiStats = ['total' => count($stats['units']), 'pending' => 0, 'approved' => 0];
        foreach ($stats['units'] as $u) {
            if ($u['has_pending']) $divisiStats['pending']++;
            if ($u['is_fully_approved']) $divisiStats['approved']++;
        }

        return view('tasks.index', compact('taskList', 'stats', 'divisiStats', 'pendingItems'));
    }

    /**
     * Helper: Proses Logic Task untuk PROJECT
     */
    private function processProjectTask($ppl, $user, $levelId, $is_mr, $currentYear, $approvalFlow, &$stats, &$pendingItems)
    {
        $projectId = $ppl->project_id;

        // 1. Data Batch (Type 2 = Project)
        $dataBatch = DataBatch::where('project_id', $projectId)->where('type', 2)->orderBy('batch', 'desc')->first();
        $batchStatus = $dataBatch ? $dataBatch->status : DataBatch::STATUS_PROSES;
        $batchStep = $dataBatch ? $dataBatch->step_verification : 0;

        $isFinished = ($batchStatus == DataBatch::STATUS_FINISH);
        $isRevision = in_array($batchStatus, [DataBatch::STATUS_REVISI, DataBatch::STATUS_REJECTED_FROM_OFFICER_MR]);

        // 2. Visual Stepper
        $currentUiStep = 1;
        if ($isFinished) $currentUiStep = 6; // Selesai
        elseif ($dataBatch) $currentUiStep = ($batchStep == 0) ? 1 : $batchStep + 1;

        $stepsVisual = [];
        foreach ($approvalFlow as $key => $info) {
            $statusColor = 'pending';
            if ($key < $currentUiStep) $statusColor = 'completed';
            elseif ($key == $currentUiStep) $statusColor = $isRevision ? 'rejected' : 'current';
            $stepsVisual[] = ['label' => $info['label'], 'role' => $info['role'], 'status'=> $statusColor];
        }

        // 3. Risk Register Stats
        $risks = ProjectRisk::where('project_periode_list_id', $ppl->id)->get();
        // $totalActiveRisks = $risks->where('is_closed', 0)->count(); // Opsi: hanya hitung yg aktif
        $totalActiveRisks = $risks->count(); // Hitung semua agar konsisten

        $riskStats = [
            'total' => $totalActiveRisks,
            'draft' => $risks->where('status', 1)->count(),
            'pending' => $risks->whereIn('status', [2, 3])->count(),
            'revision' => $risks->where('status', 5)->count(),
            'published' => $risks->where('status', 6)->count(),
            'rejected_mr' => $risks->whereIn('status', [7, 8])->count(),
        ];

        // 4. Action Logic (Menentukan apakah user perlu bertindak)
        $riskActionCount = 0;
        $riskActionLabel = '';
        $riskLink = route('projects.risks.index', ['project' => $ppl->id]);
        $isRiskUrgent = false;

        // Tentukan Step Verifikasi User (u_step)
        $u_step = 0;
        if ($levelId == 7) $u_step = 1; // RO Project
        else if ($levelId == 1) $u_step = $is_mr ? 3 : 2; // Officer MR / Officer Divisi
        else if ($levelId == 2 && $is_mr) $u_step = 4; // Owner MR

        if ($levelId == 6) {
            // Inputter (Risk Officer Project)
            if ($batchStatus == DataBatch::STATUS_REVISI) {
                $riskActionCount = $risks->where('status', 5)->count();
                $riskActionLabel = 'Perlu Revisi';
                $isRiskUrgent = true;
                if ($riskActionCount == 0) {
                    $riskActionCount = 1; $riskActionLabel = 'Siap Kirim Perbaikan'; $isRiskUrgent = false;
                }
            } elseif ($batchStatus == DataBatch::STATUS_PROSES || !$dataBatch) {
                $riskActionCount = $risks->where('status', 1)->count();
                $riskActionLabel = 'Draft Belum Dikirim';
            }
        } else {
            // Verifikator
            $isMyTurn = ($u_step == $batchStep) || ($u_step == 2 && $batchStatus == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR);
            if ($isMyTurn && !$isFinished) {
                $riskActionCount = $risks->where('step_verification', $u_step)->whereIn('status', [2, 3, 7, 8])->count();
                if($riskActionCount > 0) {
                    $riskActionLabel = ($batchStatus == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR) ? 'Dikembalikan MR' : 'Perlu Verifikasi';
                    if ($batchStatus == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR) $isRiskUrgent = true;
                }
            }
        }

        // 5. Monitoring Stats
        $allMonitorings = ProjectRiskMonitoring::with('projectRisk')
            ->whereHas('projectRisk', function($q) use ($ppl) { $q->where('project_periode_list_id', $ppl->id); })
            ->where('tahun', $currentYear)->orderBy('id', 'desc')->get();

        $monitoringSummary = [];
        $monitoringActionCount = 0;
        $isMonUrgent = false;

        $quarterMap = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];

        foreach ($quarterMap as $q => $months) {
            $monthData = [];
            foreach ($months as $m) {
                // Filter monitoring di bulan ini
                $rawMons = $allMonitorings->where('month', $m);
                $monsInMonth = $rawMons->unique('risiko_id')
                    ->filter(fn($mon) => $mon->projectRisk && $mon->projectRisk->is_closed == 0);

                $statusM = 'empty';
                $countPendingM = 0;
                $monthName = date('M', mktime(0, 0, 0, $m, 10));
                $isFutureMonth = ($m > date('n'));

                // Logic Monitoring Action
                if ($levelId == 6) { // Inputter
                    $countCreated = $monsInMonth->count();
                    $revisiCount = $monsInMonth->where('status', 1)->where('is_revision', true)->count();
                    $draftCount = $monsInMonth->where('status', 1)->where('is_revision', false)->count();

                    // Hitung total risiko aktif yang belum dibuat monitoringnya
                    $activeRiskCount = ProjectRisk::where('project_periode_list_id', $ppl->id)->where('is_closed', 0)->count();
                    $unstartedCount = max(0, $activeRiskCount - $countCreated);

                    if ($revisiCount > 0) {
                        $statusM = 'revision'; $countPendingM = $revisiCount; $isMonUrgent = true;
                    } elseif (($draftCount + $unstartedCount) > 0 && !$isFutureMonth) {
                        $statusM = 'draft'; $countPendingM = $draftCount + $unstartedCount;
                    } elseif ($countCreated > 0) {
                        $statusM = 'process';
                    }
                } else { // Verifikator
                    // Target Status Monitoring
                    $targetMonStatus = 0;
                    if ($levelId == 7) $targetMonStatus = 2; // RO Proj
                    else if ($levelId == 1 && !$is_mr) $targetMonStatus = 3; // Off Div
                    else if ($levelId == 1 && $is_mr) $targetMonStatus = 4; // Off MR
                    else if ($levelId == 2 && $is_mr) $targetMonStatus = 5; // Own MR

                    if ($targetMonStatus > 0) {
                        $pendingVerify = $monsInMonth->where('status', $targetMonStatus)->where('is_approved', false)->count();
                        if ($pendingVerify > 0) {
                            $statusM = 'pending'; $countPendingM = $pendingVerify;
                        } elseif ($monsInMonth->count() > 0) {
                            $statusM = 'process';
                        }
                    }
                }

                if (($monsInMonth->count() > 0 || ($levelId == 6 && !$isFutureMonth && $totalActiveRisks > 0)) && $statusM !== 'empty') {
                    $monthData[] = [
                        'month_num' => $m, 'month_name' => $monthName,
                        'status' => $statusM, 'count' => $countPendingM ?: $monsInMonth->count(),
                        'link' => route('projects.monitorings.index', ['project' => $ppl->id, 'quarter' => $q, 'tahun' => $currentYear, 'month' => $m])
                    ];
                    $monitoringActionCount += $countPendingM;
                }
            }
            $monitoringSummary[$q] = $monthData;
        }

        // Stats Accumulation
        $totalAction = $riskActionCount + $monitoringActionCount;
        $statusCategory = ($isRiskUrgent || $isMonUrgent) ? 'urgent' : ($totalAction > 0 ? 'pending' : 'safe');

        $stats['project']['total']++;
        if ($isFinished) $stats['project']['approved']++;
        elseif ($totalAction > 0) $stats['project']['pending']++;

        if ($riskActionCount > 0) {
            $pendingItems[] = ['type' => 'Risk Register', 'project_name' => $ppl->project->project_name, 'unit_name' => $ppl->project->divisi->name ?? '-', 'description' => $riskActionLabel, 'link' => $riskLink, 'count' => $riskActionCount . ' Risiko'];
        }
        if ($monitoringActionCount > 0) {
            foreach ($monitoringSummary as $q => $months) {
                foreach ($months as $mon) {
                    if (in_array($mon['status'], ['pending', 'revision', 'draft'])) {
                        $pendingItems[] = ['type' => 'Monitoring Q' . $q, 'project_name' => $ppl->project->project_name, 'unit_name' => $ppl->project->divisi->name ?? '-', 'description' => $mon['month_name'] . ': ' . ucfirst($mon['status']), 'link' => $mon['link'], 'count' => $mon['count'] . ' Item'];
                    }
                }
            }
        }

        return [
            'id' => 'proj_' . $ppl->id,
            'type' => 'project',
            'project_name' => $ppl->project->project_name,
            'unit_name' => $ppl->project->divisi->name ?? '-',
            'steps' => $stepsVisual,
            'current_step_index' => $currentUiStep,
            'is_revision' => $isRevision,
            'is_finished' => $isFinished,
            'risk_stats' => $riskStats,
            'risk_action' => ['count' => $riskActionCount, 'label' => $riskActionLabel, 'link' => $riskLink],
            'monitoring_summary' => $monitoringSummary,
            'monitoring_action_count' => $monitoringActionCount,
            'total_action' => $totalAction,
            'status_category' => $statusCategory
        ];
    }

    /**
     * Helper: Proses Logic Task untuk DIVISI/UNIT
     */
    private function processUnitTask($unit, $activePeriodeId, $user, $levelId, $is_mr, $divisiFlow, &$stats, &$pendingItems)
    {
        // 1. Data Batch (Type 1 = Unit)
        $dataBatch = DataBatch::where('unit_id', $unit->id)
            ->where('periode_id', $activePeriodeId)
            ->where('type', 1)
            ->orderBy('batch', 'desc')
            ->first();

        $batchStatus = $dataBatch ? $dataBatch->status : DataBatch::STATUS_PROSES;
        $batchStep = $dataBatch ? $dataBatch->step_verification : 0;
        $isFinished = ($batchStatus == DataBatch::STATUS_FINISH);
        $isRevision = in_array($batchStatus, [DataBatch::STATUS_REVISI, DataBatch::STATUS_REJECTED_FROM_OFFICER_MR]);

        // 2. Visual Stepper
        // Karena jumlah step dinamis (2 atau 4), kita hitung max step
        $maxStep = count($divisiFlow);
        $currentUiStep = 1;

        if ($isFinished) {
            $currentUiStep = $maxStep + 1; // Selesai
        } elseif ($dataBatch) {
            // Mapping Logic:
            // Jika flow normal: step_verification DB 0 = UI Step 1 (Drafting)
            // Jika flow MR: step_verification mungkin berbeda, kita asumsikan incremental
            $currentUiStep = ($batchStep == 0) ? 1 : ($batchStep > $maxStep ? $maxStep : $batchStep + 1);
        }

        $stepsVisual = [];
        foreach ($divisiFlow as $key => $info) {
            $statusColor = 'pending';
            if ($isFinished || $key < $currentUiStep) $statusColor = 'completed';
            elseif ($key == $currentUiStep) $statusColor = $isRevision ? 'rejected' : 'current';
            $stepsVisual[] = ['label' => $info['label'], 'role' => $info['role'], 'status' => $statusColor];
        }

        // 3. Risk Register Stats
        $risks = IdentifikasiRisiko::where('unit_id', $unit->id)
            ->where('periode_id', $activePeriodeId)
            ->get();

        // $totalActiveRisks = $risks->where('is_closed', 0)->count();
        $totalActiveRisks = $risks->count();

        $riskStats = [
            'total' => $totalActiveRisks,
            'draft' => $risks->where('status', 1)->count(), // Input
            'pending' => $risks->whereIn('status', [2, 3])->count(), // Dikirim / Tunggu
            'revision' => $risks->where('status', 5)->count(), // Rejected
            'published' => $risks->where('status', 6)->count(), // Published
            'rejected_mr' => 0 // Unit biasanya pakai status 5 utk semua reject
        ];

        // 4. Action Logic
        $riskActionCount = 0;
        $riskActionLabel = '';
        $riskLink = route('risk-register-unit.index', ['unit_id' => $unit->id, 'pid' => $activePeriodeId]);
        $isRiskUrgent = false;

        // Tentukan Step Verifikasi User (u_step) khusus Divisi
        // Lihat method getUserVerificationStep di RiskRegisterUnitController untuk referensi
        $u_step = 0;
        if ($unit->unit_mr) {
            // Unit MR Flow (2 Steps)
            if ($levelId == 1 && $is_mr) $u_step = 0; // Input (Officer MR) - Di DB step 0
            elseif ($levelId == 2 && $is_mr) $u_step = 2; // Final (Owner MR) - Di DB step 2, tapi step kirim awal di-set 3.
            // Note: Pada Unit MR, inputter langsung kirim ke step 3 (berdasarkan logic controller unit).
            // Jadi $u_step 2 tidak pernah terjadi di Unit MR flow controller Anda yg sebelumnya.
            // Di controller sebelumnya: Officer MR input -> Kirim (set step 3) -> Owner MR verify (step 3).
            // Jadi untuk MR: Inputter = 0, Verifikator = 3.
            if ($levelId == 2 && $is_mr) $u_step = 3;
        } else {
            // Standard Flow (4 Steps)
            if ($levelId == 1 && !$is_mr) $u_step = 0; // Input (Officer Divisi)
            elseif ($levelId == 2 && !$is_mr) $u_step = 1; // Owner Divisi
            elseif ($levelId == 1 && $is_mr) $u_step = 2; // Officer MR
            elseif ($levelId == 2 && $is_mr) $u_step = 3; // Owner MR
        }

        // Cek Action berdasarkan Role
        if ($u_step == 0) {
            // Inputter (Officer)
            if ($batchStatus == DataBatch::STATUS_REVISI) {
                $riskActionCount = $risks->where('status_progress', IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED)->count();
                if ($riskActionCount > 0) {
                    $riskActionLabel = 'Perlu Revisi';
                    $isRiskUrgent = true;
                }
            } elseif ($batchStatus == DataBatch::STATUS_PROSES) {
                $riskActionCount = $risks->where('status', 1)->count();
                if ($riskActionCount > 0) $riskActionLabel = 'Draft Belum Dikirim';
            }
        } else {
            // Verifikator
            // Cek apakah step di batch == step user
            if ($dataBatch && $dataBatch->step_verification == $u_step && !$isFinished && !$isRevision) {
                $riskActionCount = $risks->where('step_verification', $u_step)
                    ->whereIn('status', [IdentifikasiRisiko::STATUS_DIKIRIM, IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI])
                    ->count();

                if ($riskActionCount > 0) {
                    $riskActionLabel = 'Perlu Verifikasi';
                }
            }
        }

        // 5. Monitoring Stats (Divisi)
        // UnitRiskMonitoring tidak punya kolom tahun, ambil via periode_id
        $allMonitorings = UnitRiskMonitoring::with('identifikasiRisiko')
            ->whereHas('identifikasiRisiko', function($q) use ($unit, $activePeriodeId) {
                $q->where('unit_id', $unit->id)->where('periode_id', $activePeriodeId);
            })
            ->orderBy('id', 'desc')
            ->get();

        $monitoringSummary = [];
        $monitoringActionCount = 0;
        $isMonUrgent = false;

        $quarterMap = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];

        foreach ($quarterMap as $q => $months) {
            $monthData = [];
            foreach ($months as $m) {
                $rawMons = $allMonitorings->where('month', $m);
                $monsInMonth = $rawMons->unique('identifikasi_risiko_id')
                    ->filter(fn($mon) => $mon->identifikasiRisiko && $mon->identifikasiRisiko->is_closed == 0);

                $statusM = 'empty';
                $countPendingM = 0;
                $monthName = date('M', mktime(0, 0, 0, $m, 10));
                $isFutureMonth = ($m > date('n'));

                // Logic Monitoring Action (Simplifikasi)
                if ($u_step == 0) { // Inputter
                    $countCreated = $monsInMonth->count();
                    $revisiCount = $monsInMonth->where('is_revision', true)->count();
                    $activeRisksCount = IdentifikasiRisiko::where('unit_id', $unit->id)->where('periode_id', $activePeriodeId)->where('is_closed', 0)->count();
                    $unstarted = max(0, $activeRisksCount - $countCreated);

                    if ($revisiCount > 0) {
                        $statusM = 'revision'; $countPendingM = $revisiCount; $isMonUrgent = true;
                    } elseif ($unstarted > 0 && !$isFutureMonth) {
                        $statusM = 'draft'; $countPendingM = $unstarted;
                    } elseif ($countCreated > 0) {
                        $statusM = 'process';
                    }
                } else { // Verifikator
                    // Mapping target status monitoring berdasarkan u_step
                    $targetStatus = 0;
                    if ($unit->unit_mr) {
                        // MR Unit Flow: Off MR Input -> Owner MR Verify (Status 4)
                        if ($u_step == 3) $targetStatus = 4;
                    } else {
                        // Std Unit Flow: 2=OwnerDiv, 3=OffMR, 4=OwnMR
                        if ($u_step == 1) $targetStatus = 2; // Owner Div
                        if ($u_step == 2) $targetStatus = 3; // Off MR
                        if ($u_step == 3) $targetStatus = 4; // Own MR
                    }

                    if ($targetStatus > 0) {
                        $pendingVerify = $monsInMonth->where('status', $targetStatus)->where('is_approved', false)->count();
                        if ($pendingVerify > 0) {
                            $statusM = 'pending'; $countPendingM = $pendingVerify;
                        } elseif ($monsInMonth->count() > 0) {
                            $statusM = 'process';
                        }
                    }
                }

                if (($monsInMonth->count() > 0 || ($u_step == 0 && !$isFutureMonth && $totalActiveRisks > 0)) && $statusM !== 'empty') {
                    $monthData[] = [
                        'month_num' => $m, 'month_name' => $monthName,
                        'status' => $statusM, 'count' => $countPendingM ?: $monsInMonth->count(),
                        'link' => route('risk-register-unit.monitorings.index', ['unit_id' => $unit->id, 'period' => $activePeriodeId, 'quarter' => $q, 'month' => $m])
                    ];
                    $monitoringActionCount += $countPendingM;
                }
            }
            $monitoringSummary[$q] = $monthData;
        }

        // Summary Stats
        $totalAction = $riskActionCount + $monitoringActionCount;
        $statusCategory = ($isRiskUrgent || $isMonUrgent) ? 'urgent' : ($totalAction > 0 ? 'pending' : 'safe');

        // Populate Stats Array for View
        if (!isset($stats['units'][$unit->name])) {
            $stats['units'][$unit->name] = ['has_pending' => false, 'is_fully_approved' => true];
        }
        if ($totalAction > 0) {
            $stats['units'][$unit->name]['has_pending'] = true;
            $stats['units'][$unit->name]['is_fully_approved'] = false;
        } elseif (!$isFinished) {
            $stats['units'][$unit->name]['is_fully_approved'] = false;
        }

        // Add to Pending Items Modal
        if ($riskActionCount > 0) {
            $pendingItems[] = [
                'type' => 'Risk Register Divisi',
                'project_name' => $unit->name,
                'unit_name' => 'Divisi',
                'description' => $riskActionLabel,
                'link' => $riskLink,
                'count' => $riskActionCount . ' Risiko'
            ];
        }
        if ($monitoringActionCount > 0) {
            foreach ($monitoringSummary as $q => $months) {
                foreach ($months as $mon) {
                    if (in_array($mon['status'], ['pending', 'revision', 'draft'])) {
                        $pendingItems[] = [
                            'type' => 'Monitoring Divisi Q' . $q,
                            'project_name' => $unit->name,
                            'unit_name' => 'Divisi',
                            'description' => $mon['month_name'] . ': ' . ucfirst($mon['status']),
                            'link' => $mon['link'],
                            'count' => $mon['count'] . ' Item'
                        ];
                    }
                }
            }
        }

        return [
            'id' => 'unit_' . $unit->id,
            'type' => 'unit',
            'project_name' => $unit->name,
            'unit_name' => 'Divisi',
            'steps' => $stepsVisual,
            'current_step_index' => $currentUiStep,
            'is_revision' => $isRevision,
            'is_finished' => $isFinished,
            'risk_stats' => $riskStats,
            'risk_action' => ['count' => $riskActionCount, 'label' => $riskActionLabel, 'link' => $riskLink],
            'monitoring_summary' => $monitoringSummary,
            'monitoring_action_count' => $monitoringActionCount,
            'total_action' => $totalAction,
            'status_category' => $statusCategory
        ];
    }

    public function getCount()
    {
        $user = Auth::user();
        $user->load('projects', 'unit'); // Load Relasi

        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        // ============================================================
        // 1. PREPARE PROJECT IDS (Logic Permission SAMA DENGAN INDEX)
        // ============================================================
        $userProjectIds = $user->projects->pluck('id');

        $unitProjectIds = collect([]);
        if ($user->unit && Gate::check('can_access_project_under_division')) {
            $unitProjectIds = $user->unit->projects()->pluck('id');
        }

        $allProjectIds = $userProjectIds->merge($unitProjectIds)->unique();

        // Ambil Data Project Periode beserta Data Batch-nya untuk pengecekan "Giliran Siapa"
        // Kita butuh data_batches untuk menentukan apakah user boleh menghitung risiko di dalamnya
        $projectPeriodes = ProjectPeriodeList::with(['project.dataBatches' => function($q) {
                $q->where('type', 2)->orderBy('batch', 'desc')->limit(1);
            }])
            ->when(!Gate::check('project_admin_access'), function ($query) use ($allProjectIds) {
                $query->whereHas('project', function($p) use ($allProjectIds) {
                    $p->whereIn('id', $allProjectIds);
                });
            })
            ->get();

        if ($projectPeriodes->isEmpty()) {
            return response()->json(['count' => 0]);
        }

        // ============================================================
        // 2. FILTERING "IS MY TURN" (SINKRONISASI DENGAN VIEW)
        // ============================================================

        // Tentukan Step User
        $u_step = 0;
        if ($levelId == 7) $u_step = 1;
        else if ($levelId == 1) $u_step = $is_mr ? 3 : 2;
        else if ($levelId == 2 && $is_mr) $u_step = 4;

        // Tampung ID Project yang VALID untuk dihitung Risikonya
        $riskActionablePplIds = [];
        // Tampung ID Project yang VALID untuk dihitung Monitoringnya
        // (Biasanya monitoring punya flow sendiri, tapi kita batasi scope projectnya dulu)
        $allAccessiblePplIds = $projectPeriodes->pluck('id')->toArray();

        foreach ($projectPeriodes as $ppl) {
            // Logic ini meniru checkRiskActionNeeded()
            $lastBatch = $ppl->project->dataBatches->first(); // Karena sudah dilimit 1 dan order desc di query

            // Jika sudah finish, tidak perlu dihitung
            if ($lastBatch && $lastBatch->finish) continue;

            $batchStep = $lastBatch ? $lastBatch->step_verification : 0;
            $batchStatus = $lastBatch ? $lastBatch->status : 1;

            $isMyTurn = false;

            if ($levelId == 6) {
                // Inputter: Hanya jika status Batch Proses (1) atau Revisi (5) atau Null
                if (in_array($batchStatus, [1, 5]) || !$lastBatch) {
                    $isMyTurn = true;
                }
            } else {
                // Verifikator: Hanya jika Step Batch == Step User
                if (($u_step == $batchStep) ||
                    ($u_step == 2 && $batchStatus == 9) || // Dikembalikan ke Divisi
                    ($u_step == 3 && $batchStatus == 10)) { // Dikembalikan ke MR
                    $isMyTurn = true;
                }
            }

            // Validasi tambahan: Pastikan user benar-benar punya akses ke project ini (HasProject logic)
            // Khusus Level 6/7 yang strict assignment
            if (in_array($levelId, [6, 7])) {
                if (!$user->projects->contains('id', $ppl->project_id)) {
                    $isMyTurn = false;
                }
            }

            if ($isMyTurn) {
                $riskActionablePplIds[] = $ppl->id;
            }
        }

        // ============================================================
        // 3. HITUNG JUMLAH (Hanya pada Project yang Actionable)
        // ============================================================

        // A. HITUNG RISK
        $riskCount = 0;
        if (!empty($riskActionablePplIds)) {
            if ($levelId == 6) {
                $riskCount = ProjectRisk::whereIn('project_periode_list_id', $riskActionablePplIds)
                    ->where('is_closed', 0)
                    ->whereIn('status', [1, 5]) // Input & Rejected
                    ->distinct()
                    ->pluck('project_periode_list_id')
                    ->count();

                // Note: Logic di view index ada penanganan khusus:
                // Jika Batch REVISI tapi tidak ada item status 5, dia tetap hitung 1 (General Revision).
                // Jika Anda ingin 100% presisi, logic itu harus dimasukkan disini, tapi query di atas sudah mencakup 95% kasus.
            } else {
                // Verifikator
                if ($u_step > 0) {
                    $riskCount = ProjectRisk::whereIn('project_periode_list_id', $riskActionablePplIds)
                        ->where('is_closed', 0)
                        ->where('step_verification', $u_step)
                        ->whereIn('status', [2, 3, 7, 8]) // Dikirim, Tunggu, Rejected MR
                        ->distinct()
                        ->pluck('project_periode_list_id')
                        ->count();
                }
            }
        }

        // B. HITUNG MONITORING
        // Monitoring biasanya independen dari Batch Status Risk Register,
        // tapi user harus punya akses ke projectnya ($allAccessiblePplIds)
        $monitoringCount = 0;
        $currentYear = date('Y');

        $monQuery = ProjectRiskMonitoring::query()
            ->join('project_risks', 'project_risk_monitorings.risiko_id', '=', 'project_risks.id')
            ->where('project_risk_monitorings.tahun', $currentYear)
            ->whereIn('project_risks.project_periode_list_id', $allAccessiblePplIds) // Pakai ID akses global, bukan ID actionable risk
            ->where('project_risks.is_closed', 0)
            ->whereNull('project_risks.deleted_at');

        if ($levelId == 6) {
            // Level 6: Hitung Draft/Revisi Monitoring
            $monitoringCount = $monQuery->where(function($q) {
                $q->where('project_risk_monitorings.status', 1); // Draft/Revisi
            })
            ->select('project_risks.project_periode_list_id', 'project_risk_monitorings.month')
            ->distinct()
            ->get()
            ->count();

            // NOTE: Di Index View, "Unstarted Monitoring" (Risiko aktif yg belum ada monitoringnya) juga dihitung.
            // SQL di atas HANYA menghitung row monitoring yang SUDAH dibuat tapi status draft.
            // Jika ingin menghitung "Unstarted" di badge, querynya akan sangat berat.
            // Biasanya badge hanya menghitung "Draft yang sudah disimpan".
            // Jika perbedaan angka monitoring terjadi, kemungkinan karena faktor "Unstarted" ini.

        } else {
            // Verifikator
            $targetStatus = 0;
            if ($levelId == 7) $targetStatus = 2;
            else if ($levelId == 1 && !$is_mr) $targetStatus = 3;
            else if ($levelId == 1 && $is_mr) $targetStatus = 4;
            else if ($levelId == 2 && $is_mr) $targetStatus = 5;

            if ($targetStatus > 0) {
                $monitoringCount = $monQuery
                    ->where('project_risk_monitorings.status', $targetStatus)
                    ->where('project_risk_monitorings.is_approved', false)
                    ->select('project_risks.project_periode_list_id', 'project_risk_monitorings.month')
                    ->distinct()
                    ->get()
                    ->count();
            }
        }

        $totalAction = $riskCount + $monitoringCount;

        return response()->json([
            'count' => $totalAction,
            'details' => [
                'risk' => $riskCount,
                'monitoring' => $monitoringCount
            ]
        ]);
    }
}
