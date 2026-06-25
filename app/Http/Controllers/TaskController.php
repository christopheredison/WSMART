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
        $user->load('projects', 'unit');

        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        $isProjectUser = in_array($levelId, [6, 7]);

        $search = $request->query('q');
        $scope  = $request->query('scope', 'all');
        $currentYear = date('Y');

        $activePeriode = Periode::where('status', 'active')->first();
        $activePeriodeId = $activePeriode ? $activePeriode->id : null;

        $taskList = [];
        $pendingItems = [];

        $stats = [
            'project' => ['total' => 0, 'pending' => 0, 'approved' => 0],
            'units'   => []
        ];

        // 0. PREPARE PROJECT IDS
        $userProjectIds = $user->projects->pluck('id');
        $unitProjectIds = collect([]);
        if ($user->unit && Gate::check('can_access_project_under_division')) {
            $unitProjectIds = $user->unit->projects()->pluck('id');
        }
        $allProjectIds = $userProjectIds->merge($unitProjectIds)->unique();

        // 1. LOGIC PROJECT
        if ($scope == 'all' || $scope == 'project') {
            $projectFlow = [
                1 => ['label' => 'Drafting',   'role' => 'Risk Officer Project'],
                2 => ['label' => 'Review',     'role' => 'Risk Owner Project'],
                3 => ['label' => 'Verifikasi', 'role' => 'Risk Officer Divisi'],
                4 => ['label' => 'Validasi',   'role' => 'Risk Officer MR'],
                5 => ['label' => 'Finalisasi', 'role' => 'Risk Owner MR']
            ];

            $projects = ProjectPeriodeList::with(['project', 'periode'])
                ->where(function ($q) use ($user, $allProjectIds) {
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
                // Pastikan user punya akses spesifik
                if (!$this->userHasAccessToProject($user, $ppl)) {
                     continue; // Skip jika tidak punya akses view/action
                }

                $taskList[] = $this->processProjectTask($ppl, $user, $levelId, $is_mr, $currentYear, $projectFlow, $stats, $pendingItems);
            }
        }

        // 2. LOGIC DIVISI (Tetap orisinal)
        if (!$isProjectUser && ($scope == 'all' || $scope == 'divisi') && $activePeriodeId) {
            $units = Unit::where('unit_type_id', 1)
                ->when(!Gate::check('view_all_division'), function($q) use ($user) {
                    $q->where('id', $user->unit_id);
                })
                ->when($search, function($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%');
                })
                ->get();

            foreach ($units as $unit) {
                if ($unit->unit_mr) {
                    $divisiFlow = [
                        1 => ['label' => 'Drafting',   'role' => 'Risk Officer MR'],
                        2 => ['label' => 'Finalisasi', 'role' => 'Risk Owner MR']
                    ];
                } else {
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

        usort($taskList, function ($a, $b) {
            $priority = ['urgent' => 3, 'pending' => 2, 'safe' => 1];
            return $priority[$b['status_category']] <=> $priority[$a['status_category']];
        });

        $divisiStats = ['total' => count($stats['units']), 'pending' => 0, 'approved' => 0];
        foreach ($stats['units'] as $u) {
            if ($u['has_pending']) $divisiStats['pending']++;
            if ($u['is_fully_approved']) $divisiStats['approved']++;
        }

        return view('tasks.index', compact('taskList', 'stats', 'divisiStats', 'pendingItems'));
    }

    /**
     * Helper: Cek Akses User ke Project (Sync dengan Logic ProjectPeriodeListController)
     */
    private function userHasAccessToProject($user, $ppl)
    {
        // 1. Cek assignment langsung
        if ($user->hasProject($ppl)) {
            return true;
        }
        // 2. Cek permission Admin
        if (Gate::check('project_admin_access')) {
            return true;
        }
        // 3. Cek permission Divisi & Kesamaan Cost Center
        if (Gate::check('can_access_project_under_division')) {
            if ($user->unit && $ppl->project && $ppl->project->cost_center_parent == $user->unit->cost_center) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper: Tentukan Step User (Sync dengan Logic ProjectPeriodeListController)
     */
    private function getUserStep($levelId, $is_mr)
    {
        if ($levelId == 7) return 1; // ROP
        if ($levelId == 1) return $is_mr ? 3 : 2; // ROD / RO MR
        if ($levelId == 2 && $is_mr) return 4; // ROW MR
        return 0; // Inputter or Others
    }

    /**
     * Helper: Proses Logic Task untuk PROJECT
     */
    private function processProjectTask($ppl, $user, $levelId, $is_mr, $currentYear, $approvalFlow, &$stats, &$pendingItems)
    {
        $projectId = $ppl->project_id;

        // --- 1. SETUP VARIABEL DASAR ---
        $u_step = $this->getUserStep($levelId, $is_mr);
        $projectUrl = route('projects.risks.index', ['project' => $ppl->id]);

        // Ambil Data Batch Terakhir
        $dataBatch = DataBatch::where('project_id', $projectId)->where('type', 2)->orderBy('batch', 'desc')->first();
        $batchStatus = $dataBatch ? $dataBatch->status : DataBatch::STATUS_PROSES;
        $batchStep = $dataBatch ? $dataBatch->step_verification : 0;
        $isFinished = ($batchStatus == DataBatch::STATUS_FINISH);
        $isRevision = in_array($batchStatus, [DataBatch::STATUS_REVISI, DataBatch::STATUS_REJECTED_FROM_OFFICER_MR, DataBatch::STATUS_REJECTED_FROM_OWNER_MR]);

        // Visual Stepper
        $currentUiStep = 1;
        if ($isFinished) $currentUiStep = 6;
        elseif ($dataBatch) $currentUiStep = ($batchStep == 0) ? 1 : $batchStep + 1;

        $stepsVisual = [];
        foreach ($approvalFlow as $key => $info) {
            $statusColor = 'pending';
            if ($key < $currentUiStep) $statusColor = 'completed';
            elseif ($key == $currentUiStep) $statusColor = $isRevision ? 'rejected' : 'current';
            $stepsVisual[] = ['label' => $info['label'], 'role' => $info['role'], 'status'=> $statusColor];
        }

        // --- 2. LOGIK RISK REGISTER (SINKRON DENGAN REFERENCE) ---
        $isMyTurnRisk = false;

        if ($levelId == 6) {
            // Inputter: Status 1 (Draft) atau 5 (Revisi)
            if (in_array($batchStatus, [1, 5]) || !$dataBatch) {
                $isMyTurnRisk = true;
            }
        } else {
            // Verifikator
            if (($u_step == $batchStep) ||
                ($u_step == 2 && $batchStatus == 9) ||  // Reject ke Divisi
                ($u_step == 3 && $batchStatus == 10)) { // Reject ke MR
                $isMyTurnRisk = true;
            }
        }

        // Hitung Statistik Risiko
        $risks = ProjectRisk::where('project_periode_list_id', $ppl->id)->whereNull('deleted_at')->get();
        $totalActiveRisks = $risks->count();
        $riskStats = [
            'total' => $totalActiveRisks,
            'draft' => $risks->where('status', 1)->count(),
            'pending' => $risks->whereIn('status', [2, 3])->count(),
            'revision' => $risks->where('status', 5)->count(),
            'published' => $risks->where('status', 6)->count(),
            'rejected_mr' => $risks->whereIn('status', [7, 8])->count(),
        ];

        // Tentukan Action Count & Label
        $riskActionCount = 0;
        $riskActionLabel = '';
        $isRiskUrgent = false;

        if ($isMyTurnRisk && !$isFinished) {
            if ($levelId == 6) {
                if ($batchStatus == 5) {
                    $count = $risks->where('status', 5)->count();
                    $riskActionCount = ($count == 0) ? 1 : $count; // Jika count 0, tetap tampilkan 1 untuk memicu aksi submit batch
                    $riskActionLabel = 'Perlu Revisi';
                    $isRiskUrgent = true;
                } else {
                    $count = $risks->where('status', 1)->count();
                    if ($count == 0 && $totalActiveRisks == 0) $count = 1;
                    $riskActionCount = ($count == 0) ? 1 : $count;
                    $riskActionLabel = 'Draft / Input Risiko';
                }
            } else {
                $count = $risks->where('step_verification', $u_step)
                             ->whereIn('status', [2, 3, 7, 8])
                             ->count();
                // Walaupun count 0, jika batch sedang di tahap verifikator, mereka harus proses batch tersebut
                $riskActionCount = ($count == 0) ? 1 : $count;
                $riskActionLabel = ($isRevision) ? 'Dikembalikan (Review)' : 'Perlu Verifikasi';
                if ($isRevision) $isRiskUrgent = true;
            }
        }

        // --- 3. LOGIK MONITORING (SINKRON DENGAN REFERENCE) ---
        $monitoringActionCount = 0;
        $isMonUrgent = false;
        $monitoringSummary = [];

        // Setup target status untuk Action Check
        $monTargetStatus = 0;
        if ($levelId == 6) $monTargetStatus = 1;
        elseif ($levelId == 7) $monTargetStatus = 2;
        elseif ($levelId == 1 && !$is_mr) $monTargetStatus = 3;
        elseif ($levelId == 1 && $is_mr) $monTargetStatus = 4;
        elseif ($levelId == 2 && $is_mr) $monTargetStatus = 5;

        // Ambil Data Monitoring Raw
        $allMonitorings = ProjectRiskMonitoring::with('projectRisk')
            ->whereHas('projectRisk', function($q) use ($ppl) {
                $q->where('project_periode_list_id', $ppl->id)->where('is_closed', 0);
            })
            ->where('tahun', $currentYear)
            ->get();

        $quarterMap = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];

        foreach ($quarterMap as $q => $months) {
            $monthData = [];
            foreach ($months as $m) {
                $rawMons = $allMonitorings->where('month', $m);
                // Ambil UNIQUE LATEST per Risiko khusus di bulan/tahun tersebut
                $monsInMonth = $rawMons->sortByDesc('id')->unique('risiko_id');

                $statusM = 'empty';
                $countPendingM = 0;
                $monthName = date('M', mktime(0, 0, 0, $m, 10));

                if ($monsInMonth->count() > 0) {
                    $statusM = 'process';

                    // Cek Aksi untuk bulan ini
                    if ($monTargetStatus > 0) {
                        $actionableItems = $monsInMonth->filter(function($mon) use ($monTargetStatus) {
                            return $mon->status == $monTargetStatus && !$mon->is_approved;
                        });

                        if ($actionableItems->count() > 0) {
                            $countPendingM = $actionableItems->count();
                            $monitoringActionCount += $countPendingM;

                            if ($levelId == 6) {
                                if ($actionableItems->filter(fn($mon) => $mon->is_revision > 0)->count() > 0) {
                                    $statusM = 'revision';
                                    $isMonUrgent = true;
                                } else {
                                    $statusM = 'draft';
                                }
                            } else {
                                $statusM = 'pending';
                            }
                        }
                    }
                }

                if ($statusM !== 'empty') {
                    $monthData[] = [
                        'month_num' => $m, 'month_name' => $monthName,
                        'status' => $statusM, 'count' => $countPendingM ?: $monsInMonth->count(),
                        'link' => route('projects.monitorings.index', ['project' => $ppl->id, 'tahun' => $currentYear, 'month' => $m])
                    ];
                }
            }
            $monitoringSummary[$q] = $monthData;
        }

        // --- 4. DATA COMPILATION ---
        $totalAction = ($isMyTurnRisk ? $riskActionCount : 0) + $monitoringActionCount;
        $statusCategory = ($isRiskUrgent || $isMonUrgent) ? 'urgent' : ($totalAction > 0 ? 'pending' : 'safe');

        $stats['project']['total']++;
        if ($isFinished) $stats['project']['approved']++;
        elseif ($totalAction > 0) $stats['project']['pending']++;

        if ($isMyTurnRisk && $riskActionCount > 0) {
            $pendingItems[] = [
                'type' => 'Risk Register',
                'project_name' => $ppl->project->project_name,
                'unit_name' => $ppl->project->divisi->name ?? '-',
                'description' => $riskActionLabel,
                'link' => $projectUrl,
                'count' => $riskActionCount . ' Risiko'
            ];
        }

        // Add Monitoring to Modal List
        foreach ($monitoringSummary as $q => $months) {
            foreach ($months as $mon) {
                if (in_array($mon['status'], ['pending', 'revision', 'draft']) && $mon['count'] > 0) {
                    $pendingItems[] = [
                        'type' => 'Monitoring Q' . $q,
                        'project_name' => $ppl->project->project_name,
                        'unit_name' => $ppl->project->divisi->name ?? '-',
                        'description' => $mon['month_name'] . ': ' . ucfirst($mon['status']),
                        'link' => $mon['link'],
                        'count' => $mon['count'] . ' Item'
                    ];
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
            'risk_action' => ['count' => ($isMyTurnRisk ? $riskActionCount : 0), 'label' => $riskActionLabel, 'link' => $projectUrl],
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
        $dataBatch = DataBatch::where('unit_id', $unit->id)->where('periode_id', $activePeriodeId)->where('type', 1)->orderBy('batch', 'desc')->first();
        $batchStatus = $dataBatch ? $dataBatch->status : DataBatch::STATUS_PROSES;
        $batchStep = $dataBatch ? $dataBatch->step_verification : 0;
        $isFinished = ($batchStatus == DataBatch::STATUS_FINISH);
        $isRevision = in_array($batchStatus, [DataBatch::STATUS_REVISI, DataBatch::STATUS_REJECTED_FROM_OFFICER_MR]);

        $maxStep = count($divisiFlow);
        $currentUiStep = 1;
        if ($isFinished) $currentUiStep = $maxStep + 1;
        elseif ($dataBatch) $currentUiStep = ($batchStep == 0) ? 1 : ($batchStep > $maxStep ? $maxStep : $batchStep + 1);

        $stepsVisual = [];
        foreach ($divisiFlow as $key => $info) {
            $statusColor = 'pending';
            if ($isFinished || $key < $currentUiStep) $statusColor = 'completed';
            elseif ($key == $currentUiStep) $statusColor = $isRevision ? 'rejected' : 'current';
            $stepsVisual[] = ['label' => $info['label'], 'role' => $info['role'], 'status' => $statusColor];
        }

        $risks = IdentifikasiRisiko::where('unit_id', $unit->id)->where('periode_id', $activePeriodeId)->get();
        $totalActiveRisks = $risks->count();

        $riskStats = [
            'total' => $totalActiveRisks,
            'draft' => $risks->where('status', 1)->count(),
            'pending' => $risks->whereIn('status', [2, 3])->count(),
            'revision' => $risks->where('status', 5)->count(),
            'published' => $risks->where('status', 6)->count(),
            'rejected_mr' => 0
        ];

        $riskActionCount = 0;
        $riskActionLabel = '';
        $riskLink = route('risk-register-unit.index', ['unit_id' => $unit->id, 'pid' => $activePeriodeId]);
        $isRiskUrgent = false;

        $u_step = 0;
        if ($unit->unit_mr) {
            if ($levelId == 1 && $is_mr) $u_step = 0;
            elseif ($levelId == 2 && $is_mr) $u_step = 3;
        } else {
            if ($levelId == 1 && !$is_mr) $u_step = 0;
            elseif ($levelId == 2 && !$is_mr) $u_step = 1;
            elseif ($levelId == 1 && $is_mr) $u_step = 2;
            elseif ($levelId == 2 && $is_mr) $u_step = 3;
        }

        if ($u_step == 0) {
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
            if ($dataBatch && $dataBatch->step_verification == $u_step && !$isFinished && !$isRevision) {
                $riskActionCount = $risks->where('step_verification', $u_step)
                    ->whereIn('status', [IdentifikasiRisiko::STATUS_DIKIRIM, IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI])
                    ->count();
                if ($riskActionCount > 0) $riskActionLabel = 'Perlu Verifikasi';
            }
        }

        $allMonitorings = UnitRiskMonitoring::with('identifikasiRisiko')
            ->whereHas('identifikasiRisiko', function($q) use ($unit, $activePeriodeId) {
                $q->where('unit_id', $unit->id)->where('periode_id', $activePeriodeId);
            })
            ->get();

        $monitoringSummary = [];
        $monitoringActionCount = 0;
        $isMonUrgent = false;
        $quarterMap = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];

        foreach ($quarterMap as $q => $months) {
            $monthData = [];
            foreach ($months as $m) {
                $rawMons = $allMonitorings->where('month', $m);
                $latestMons = $rawMons->sortByDesc('id')->unique('identifikasi_risiko_id');
                $monsInMonth = $latestMons->filter(fn($mon) => $mon->identifikasiRisiko && $mon->identifikasiRisiko->is_closed == 0);

                $statusM = 'empty';
                $countPendingM = 0;
                $monthName = date('M', mktime(0, 0, 0, $m, 10));
                $isFutureMonth = ($m > date('n'));

                if ($u_step == 0) {
                    $countCreated = $monsInMonth->count();
                    $revisiCount = $monsInMonth->filter(fn($mon) => $mon->is_revision > 0)->count();
                    $activeRisksCount = IdentifikasiRisiko::where('unit_id', $unit->id)->where('periode_id', $activePeriodeId)->where('is_closed', 0)->count();
                    $unstarted = max(0, $activeRisksCount - $countCreated);

                    if ($revisiCount > 0) {
                        $statusM = 'revision'; $countPendingM = $revisiCount; $isMonUrgent = true;
                    } elseif ($unstarted > 0 && !$isFutureMonth) {
                        $statusM = 'draft'; $countPendingM = $unstarted;
                    } elseif ($countCreated > 0) {
                        $statusM = 'process';
                    }
                } else {
                    $targetStatus = 0;
                    if ($unit->unit_mr) {
                        if ($u_step == 3) $targetStatus = 4;
                    } else {
                        if ($u_step == 1) $targetStatus = 2;
                        if ($u_step == 2) $targetStatus = 3;
                        if ($u_step == 3) $targetStatus = 4;
                    }

                    if ($targetStatus > 0) {
                        $pendingVerify = $monsInMonth
                          ->where('status', $targetStatus)
                          ->where('is_approved', false)
                          ->count();
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

        $totalAction = $riskActionCount + $monitoringActionCount;
        $statusCategory = ($isRiskUrgent || $isMonUrgent) ? 'urgent' : ($totalAction > 0 ? 'pending' : 'safe');

        if (!isset($stats['units'][$unit->name])) {
            $stats['units'][$unit->name] = ['has_pending' => false, 'is_fully_approved' => true];
        }
        if ($totalAction > 0) {
            $stats['units'][$unit->name]['has_pending'] = true;
            $stats['units'][$unit->name]['is_fully_approved'] = false;
        } elseif (!$isFinished) {
            $stats['units'][$unit->name]['is_fully_approved'] = false;
        }

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

    /**
     * API untuk Hitung Jumlah Badge (Navbar/Sidebar)
     * DISINKRONKAN DENGAN LOGIC PROJECTPERIODE LIST
     */
    public function getCount()
    {
        $user = Auth::user();
        $user->load('projects', 'unit');

        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $u_step = $this->getUserStep($levelId, $is_mr);

        $userProjectIds = $user->projects->pluck('id');
        $unitProjectIds = collect([]);
        if ($user->unit && Gate::check('can_access_project_under_division')) {
            $unitProjectIds = $user->unit->projects()->pluck('id');
        }
        $allProjectIds = $userProjectIds->merge($unitProjectIds)->unique();

        $projects = ProjectPeriodeList::with(['project.dataBatches' => function($q) {
                $q->where('type', 2)->orderBy('batch', 'desc')->limit(1);
            }])
            ->where(function ($q) use ($user, $allProjectIds) {
                if (!Gate::check('project_admin_access')) {
                    $q->whereHas('project', function($p) use ($allProjectIds) {
                        $p->whereIn('id', $allProjectIds);
                    });
                }
            })
            ->get();

        $riskCount = 0;
        $monitoringCount = 0;
        $currentYear = date('Y');

        $monTargetStatus = 0;
        if ($levelId == 6) $monTargetStatus = 1;
        elseif ($levelId == 7) $monTargetStatus = 2;
        elseif ($levelId == 1 && !$is_mr) $monTargetStatus = 3;
        elseif ($levelId == 1 && $is_mr) $monTargetStatus = 4;
        elseif ($levelId == 2 && $is_mr) $monTargetStatus = 5;

        foreach ($projects as $ppl) {
            if (!$this->userHasAccessToProject($user, $ppl)) continue;

            // --- A. HITUNG RISK ---
            $lastBatch = $ppl->project->dataBatches->first();
            $isRiskActionNeeded = false;

            if ($lastBatch && !$lastBatch->finish) {
                $batchStatus = $lastBatch->status;
                $batchStep = $lastBatch->step_verification;

                if ($levelId == 6) {
                    if (in_array($batchStatus, [1, 5])) $isRiskActionNeeded = true;
                } else {
                    if (($u_step == $batchStep) ||
                        ($u_step == 2 && $batchStatus == 9) ||
                        ($u_step == 3 && $batchStatus == 10)) {
                        $isRiskActionNeeded = true;
                    }
                }
            } elseif (!$lastBatch && $levelId == 6) {
                 $isRiskActionNeeded = true;
            }

            if ($isRiskActionNeeded) {
                $qRisk = ProjectRisk::where('project_periode_list_id', $ppl->id)
                                    ->where('is_closed', 0)
                                    ->whereNull('deleted_at');

                if ($levelId == 6) {
                    $count = $qRisk->whereIn('status', [1, 5])->count();
                    $riskCount += ($count == 0) ? 1 : $count; // Tetap 1 jika menuntut submit
                } else {
                    $count = $qRisk->where('step_verification', $u_step)->whereIn('status', [2, 3, 7, 8])->count();
                    $riskCount += ($count == 0) ? 1 : $count; // Sama dengan di atas, menuntut action approval batch
                }
            }

            // --- B. HITUNG MONITORING ---
            if ($monTargetStatus > 0) {
                // Tarik semua status target di tahun ini (Bukan sekadar reference bulan terakhir saja)
                $monItems = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($ppl) {
                        $q->where('project_periode_list_id', $ppl->id)->where('is_closed', 0);
                    })
                    ->where('tahun', $currentYear)
                    ->where('status', $monTargetStatus)
                    ->where('is_approved', false)
                    ->get();

                // Filter Unique Latest ID per Risk, per Bulan
                $validMonItems = $monItems->filter(function($item) {
                    $latestId = ProjectRiskMonitoring::where('risiko_id', $item->risiko_id)
                        ->where('tahun', $item->tahun)
                        ->where('month', $item->month)
                        ->max('id');
                    return $item->id == $latestId;
                });

                $monitoringCount += $validMonItems->count();
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
