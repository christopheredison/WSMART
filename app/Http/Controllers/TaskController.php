<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProjectPeriodeList;
use App\Models\ProjectRisk;
use App\Models\ProjectRiskMonitoring;
use App\Models\DataBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        $search = $request->query('q');
        $scope  = $request->query('scope', 'all');
        $currentYear = date('Y');

        // Approval Flow (Stepper UI)
        $approvalFlow = [
            1 => ['label' => 'Drafting',   'role' => 'Risk Officer Project'],
            2 => ['label' => 'Review',     'role' => 'Risk Owner Project'],
            3 => ['label' => 'Verifikasi', 'role' => 'Risk Officer Divisi'],
            4 => ['label' => 'Validasi',   'role' => 'Risk Officer MR'],
            5 => ['label' => 'Finalisasi', 'role' => 'Risk Owner MR']
        ];

        // 1. Query Project
        $projects = ProjectPeriodeList::with(['project', 'periode'])
            ->where(function ($q) use ($user) {
                if (!Gate::check('project_admin_access')) {
                    $q->whereHas('project', function($p) use ($user) {
                        if ($user->unit) {
                            $p->where('cost_center_parent', $user->unit->cost_center);
                        }
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

        $taskList = [];

        $stats = [
            'project' => ['total' => 0, 'pending' => 0, 'approved' => 0],
            'units'   => []
        ];

        $pendingItems = [];

        foreach ($projects as $ppl) {
            $projectId = $ppl->project_id;

            // --- A. ANALISA BATCH STATUS ---
            $dataBatch = DataBatch::where('project_id', $projectId)->where('type', 2)->orderBy('batch', 'desc')->first();
            $batchStatus = $dataBatch ? $dataBatch->status : DataBatch::STATUS_PROSES;
            $batchStep = $dataBatch ? $dataBatch->step_verification : 0;

            $isFinished = ($batchStatus == DataBatch::STATUS_FINISH);
            $isRevision = in_array($batchStatus, [DataBatch::STATUS_REVISI, DataBatch::STATUS_REJECTED_FROM_OFFICER_MR]);

            // UI Stepper Logic
            $currentUiStep = 1;
            if ($isFinished) $currentUiStep = 6;
            elseif ($dataBatch) $currentUiStep = ($batchStep == 0) ? 1 : $batchStep + 1;

            $stepsVisual = [];
            foreach ($approvalFlow as $key => $info) {
                $statusColor = 'pending';
                if ($key < $currentUiStep) $statusColor = 'completed';
                elseif ($key == $currentUiStep) $statusColor = $isRevision ? 'rejected' : 'current';
                $stepsVisual[] = ['label' => $info['label'], 'role'  => $info['role'], 'status'=> $statusColor];
            }

            // --- B. RISK REGISTER STATS & ACTION ---
            // Ambil Risiko Aktif (Is Closed = 0)
            $risks = ProjectRisk::where('project_periode_list_id', $ppl->id)
              // ->where('is_closed', 0)
              ->get();
            $totalActiveRisks = $risks->count();

            $riskStats = [
                'total' => $totalActiveRisks,
                'draft' => $risks->where('status', 1)->count(),
                'pending' => $risks->whereIn('status', [2, 3])->count(),
                'revision' => $risks->where('status', 5)->count(),
                'published' => $risks->where('status', 6)->count(),
                'rejected_mr' => $risks->whereIn('status', [7, 8])->count(),
            ];

            $riskActionCount = 0;
            $riskActionLabel = '';
            $riskLink = route('projects.risks.index', ['project' => $ppl->id]);
            $isRiskUrgent = false;

            // Logic Action Risk Register (Sesuai kode sebelumnya)
            $u_step = 0;
            if ($levelId == 7) $u_step = 1;
            else if ($levelId == 1) $u_step = $is_mr ? 3 : 2;
            else if ($levelId == 2 && $is_mr) $u_step = 4;

            if ($levelId == 6) {
                if ($batchStatus == DataBatch::STATUS_REVISI) {
                    $riskActionCount = $risks->where('status', 5)->count();
                    $riskActionLabel = 'Perlu Revisi';
                    $isRiskUrgent = true;
                    if ($riskActionCount == 0) { $riskActionCount = 1; $riskActionLabel = 'Siap Kirim Perbaikan'; $isRiskUrgent = false; }
                } elseif ($batchStatus == DataBatch::STATUS_PROSES || !$dataBatch) {
                    $riskActionCount = $risks->where('status', 1)->count();
                    $riskActionLabel = 'Draft Belum Dikirim';
                }
            } else {
                $isMyTurn = ($u_step == $batchStep) || ($u_step == 2 && $batchStatus == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR);
                $isValidStatus = in_array($batchStatus, [DataBatch::STATUS_KIRIM, DataBatch::STATUS_VERIFIKASI, DataBatch::STATUS_REJECTED_FROM_OFFICER_MR]);

                if ($isMyTurn && $isValidStatus && !$isFinished) {
                    $riskActionCount = $risks->where('step_verification', $u_step)->whereIn('status', [2, 3, 7, 8])->count();
                    if($riskActionCount > 0) {
                        $riskActionLabel = ($batchStatus == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR) ? 'Dikembalikan MR' : 'Perlu Verifikasi';
                        if ($batchStatus == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR) $isRiskUrgent = true;
                    }
                }
            }

            // --- C. MONITORING STATS (UPDATED LOGIC: LATEST DATA & TIME VALIDATION) ---
            $allMonitorings = ProjectRiskMonitoring::with('projectRisk')
                ->whereHas('projectRisk', function($q) use ($ppl) {
                    $q->where('project_periode_list_id', $ppl->id);
                    // Note: Kita tidak filter is_closed di query utama monitoring
                    // agar data historis tetap bisa ditarik jika perlu,
                    // TAPI kita filter ketat saat perhitungan di bawah.
                })
                ->where('tahun', $currentYear)
                ->orderBy('id', 'desc')
                ->get();

            $monitoringSummary = [];
            $monitoringActionCount = 0;
            $isMonUrgent = false;
            $currentMonth = (int) date('n'); // Bulan saat ini (1-12)

            // Hitung Total Risiko Aktif (Induk)
            // Pastikan ini HANYA menghitung yang is_closed = 0
            $activeRisksCollection = ProjectRisk::where('project_periode_list_id', $ppl->id)
                ->where('is_closed', 0)
                ->get();
            $totalActiveRisks = $activeRisksCollection->count();

            // Mapping Target Verifikator
            $targetMonStatus = 0;
            if ($levelId == 7) $targetMonStatus = 2;
            else if ($levelId == 1 && !$is_mr) $targetMonStatus = 3;
            else if ($levelId == 1 && $is_mr) $targetMonStatus = 4;
            else if ($levelId == 2 && $is_mr) $targetMonStatus = 5;

            $quarterMap = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];

            foreach ($quarterMap as $q => $months) {
                $monthData = [];
                foreach ($months as $m) {
                    // --- STEP 1: PREPARE DATA BERSIH ---
                    $rawMons = $allMonitorings->where('month', $m);

                    // Filter Monitoring:
                    // 1. Ambil unik berdasarkan risiko_id (handle duplikat)
                    // 2. HANYA ambil monitoring yang risiko induknya AKTIF (is_closed = 0)
                    $monsInMonth = $rawMons->unique('risiko_id')
                        ->filter(function($mon) {
                            return $mon->projectRisk && $mon->projectRisk->is_closed == 0;
                        });

                    $statusM = 'empty';
                    $countPendingM = 0;
                    $monthName = date('M', mktime(0, 0, 0, $m, 10));
                    $isFutureMonth = ($m > $currentMonth);

                    if ($levelId == 6) {
                        // --- LOGIC INPUTTER ---

                        // Count Created HANYA dari monitoring yang valid (Active Risk)
                        $countCreated = $monsInMonth->count();

                        $revisiCount = $monsInMonth->where('status', 1)->where('is_revision', true)->count();
                        $draftCount = $monsInMonth->where('status', 1)->where('is_revision', false)->count();

                        // Perhitungan Unstarted yang PASTI AKURAT
                        // (Total Risiko Aktif) - (Monitoring Risiko Aktif yg sudah dibuat)
                        $unstartedCount = ($totalActiveRisks > 0) ? ($totalActiveRisks - $countCreated) : 0;
                        if ($unstartedCount < 0) $unstartedCount = 0;

                        if ($revisiCount > 0) {
                            $statusM = 'revision';
                            $countPendingM = $revisiCount;
                            $isMonUrgent = true;
                        }
                        elseif (($draftCount + $unstartedCount) > 0) {
                            $statusM = 'draft';

                            if ($isFutureMonth) {
                                // Masa Depan: Info (Biru) atau Empty
                                if ($draftCount > 0) {
                                    $statusM = 'process';
                                    $countPendingM = 0;
                                } else {
                                    $statusM = 'empty';
                                    $countPendingM = 0;
                                }
                            } else {
                                // Bulan Ini/Lalu: Hitung "Hutang" Inputan
                                $countPendingM = $draftCount + $unstartedCount;
                            }
                        }
                        elseif ($countCreated > 0 && $countCreated >= $totalActiveRisks) {
                            $statusM = 'process';
                        }

                    } else {
                        // --- LOGIC VERIFIKATOR ---
                        if ($targetMonStatus > 0) {
                            // Hitung dari $monsInMonth yang sudah bersih dari risiko closed
                            $pendingVerify = $monsInMonth->where('status', $targetMonStatus)
                                ->where('is_approved', false)
                                ->count();

                            $returnedCount = $monsInMonth->where('status', $targetMonStatus)
                                ->where('is_revision', true)
                                ->count();

                            if ($returnedCount > 0) {
                                $statusM = 'revision';
                                $countPendingM = $returnedCount;
                                $isMonUrgent = true;
                            } elseif ($pendingVerify > 0) {
                                $statusM = 'pending';
                                $countPendingM = $pendingVerify;
                            } elseif ($monsInMonth->count() > 0) {
                                $statusM = 'process';
                            }
                        }
                    }

                    // Tampilkan slot
                    $showItem = $monsInMonth->count() > 0;
                    if ($levelId == 6 && !$isFutureMonth && $totalActiveRisks > 0) {
                        $showItem = true;
                    }

                    if ($showItem && $statusM !== 'empty') {
                        $monthData[] = [
                            'month_num' => $m,
                            'month_name' => $monthName,
                            'status' => $statusM,
                            'count' => $countPendingM > 0 ? $countPendingM : $monsInMonth->count(),
                            'link' => route('projects.monitorings.index', ['project' => $ppl->id, 'quarter' => $q, 'month' => $m, 'tahun' => $currentYear])
                        ];
                        $monitoringActionCount += $countPendingM;
                    }
                }
                $monitoringSummary[$q] = $monthData;
            }

            // --- STATUS CATEGORY ---
            $totalAction = $riskActionCount + $monitoringActionCount;
            $statusCategory = 'safe';
            if ($isRiskUrgent || $isMonUrgent) $statusCategory = 'urgent';
            elseif ($totalAction > 0) $statusCategory = 'pending';

            $stats['project']['total']++;

            if ($isFinished) {
                $stats['project']['approved']++;
            } elseif ($riskActionCount > 0 || $monitoringActionCount > 0) {
                $stats['project']['pending']++;
            }

            // 2. Statistik Divisi (Mengelompokkan berdasarkan Unit Name)
            $unitName = $ppl->project->divisi->name ?? 'Unassigned';
            if (!isset($stats['units'][$unitName])) {
                $stats['units'][$unitName] = ['has_pending' => false, 'is_fully_approved' => true];
            }

            // Jika ada action di project ini, maka Unit ini dianggap memiliki pending
            if ($riskActionCount > 0 || $monitoringActionCount > 0) {
                $stats['units'][$unitName]['has_pending'] = true;
                $stats['units'][$unitName]['is_fully_approved'] = false;
            } elseif (!$isFinished) {
                // Jika belum finish tapi tidak pending (misal: menunggu orang lain), maka belum approved fully
                $stats['units'][$unitName]['is_fully_approved'] = false;
            }

            // 3. Populate Data untuk Modal (List Menunggu Persetujuan)
            // A. Cek Risk Register
            if ($riskActionCount > 0) {
                $pendingItems[] = [
                    'type' => 'Risk Register',
                    'project_name' => $ppl->project->project_name,
                    'unit_name' => $unitName,
                    'description' => $riskActionLabel, // Contoh: "Perlu Verifikasi"
                    'link' => $riskLink,
                    'count' => $riskActionCount . ' Risiko'
                ];
            }

            // B. Cek Monitoring
            if ($monitoringActionCount > 0) {
                // Kita ambil detail bulan apa saja yang pending dari data $monitoringSummary yang sudah dibuat sebelumnya
                foreach ($monitoringSummary as $q => $months) {
                    foreach ($months as $mon) {
                        // Cek status yang memerlukan aksi user (Pending/Revisi/Draft)
                        if (in_array($mon['status'], ['pending', 'revision', 'draft'])) {
                            $pendingItems[] = [
                                'type' => 'Monitoring Q' . $q,
                                'project_name' => $ppl->project->project_name,
                                'unit_name' => $unitName,
                                'description' => $mon['month_name'] . ': ' . ucfirst($mon['status']),
                                'link' => $mon['link'],
                                'count' => $mon['count'] . ' Item'
                            ];
                        }
                    }
                }
            }

            $taskList[] = [
                'id' => $ppl->id,
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

        $divisiStats = ['total' => count($stats['units']), 'pending' => 0, 'approved' => 0];
        foreach ($stats['units'] as $u) {
            if ($u['has_pending']) $divisiStats['pending']++;
            if ($u['is_fully_approved']) $divisiStats['approved']++;
        }

        // Sorting
        usort($taskList, function ($a, $b) {
            $priority = ['urgent' => 3, 'pending' => 2, 'safe' => 1];
            return $priority[$b['status_category']] <=> $priority[$a['status_category']];
        });

        return view('tasks.index', compact('taskList', 'stats', 'divisiStats', 'pendingItems'));
    }

    public function getCount()
    {
        $user = Auth::user();
        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        // 1. Ambil ID Project Periode yang bisa diakses user
        $projectPeriodeIds = ProjectPeriodeList::query()
            ->when(!Gate::check('project_admin_access'), function ($query) use ($user) {
                $query->whereHas('project', function($p) use ($user) {
                    if ($user->unit) {
                        $p->where('cost_center_parent', $user->unit->cost_center);
                    }
                });
            })
            ->pluck('id');

        if ($projectPeriodeIds->isEmpty()) {
            return response()->json(['count' => 0]);
        }

        // 2. Hitung Actionable Items untuk Risk Register (GROUP BY PROJECT)
        $riskCount = 0;

        if ($levelId == 6) {
            // === RISK OFFICER PROJECT ===
            $riskCount = ProjectRisk::whereIn('project_periode_list_id', $projectPeriodeIds)
                ->where('is_closed', 0)
                ->whereIn('status', [
                    ProjectRisk::STATUS_INPUT_DATA, // 1
                    ProjectRisk::STATUS_REJECTED    // 5
                ])
                ->distinct()
                ->pluck('project_periode_list_id')
                ->count();
        } else {
            // === VERIFIKATOR ===
            $u_step = 0;
            if ($levelId == 7) $u_step = 1;
            else if ($levelId == 1) $u_step = $is_mr ? 3 : 2;
            else if ($levelId == 2 && $is_mr) $u_step = 4;

            if ($u_step > 0) {
                $riskCount = ProjectRisk::whereIn('project_periode_list_id', $projectPeriodeIds)
                    ->where('is_closed', 0)
                    ->where('step_verification', $u_step)
                    ->whereIn('status', [
                        ProjectRisk::STATUS_DIKIRIM, // 2
                        ProjectRisk::STATUS_TUNGGU_VERIFIKASI, // 3
                        ProjectRisk::STATUS_REJECTED_FROM_OFFICER_MR, // 7
                        ProjectRisk::STATUS_REJECTED_FROM_OWNER_MR  // 8
                    ])
                    ->distinct()
                    ->pluck('project_periode_list_id')
                    ->count();
            }
        }

        // 3. Hitung Actionable Items untuk Monitoring (GROUP BY PROJECT + MONTH)
        $monitoringCount = 0;
        $currentYear = date('Y');

        // Base Query (Join ProjectRisk untuk Grouping)
        $monQuery = ProjectRiskMonitoring::query()
            ->join('project_risks', 'project_risk_monitorings.risiko_id', '=', 'project_risks.id')
            ->where('project_risk_monitorings.tahun', $currentYear)
            ->whereIn('project_risks.project_periode_list_id', $projectPeriodeIds)
            ->where('project_risks.is_closed', 0)
            ->whereNull('project_risks.deleted_at');

        if ($levelId == 6) {
            // === RISK OFFICER PROJECT ===
            $monitoringCount = $monQuery->where(function($q) {
                $q->where('project_risk_monitorings.status', ProjectRiskMonitoring::STATUS_DRAFT_REVISI); // 1
            })
            ->select('project_risks.project_periode_list_id', 'project_risk_monitorings.month')
            ->distinct()
            ->get()
            ->count();
        } else {
            // === VERIFIKATOR ===
            $targetStatus = 0;
            if ($levelId == 7) $targetStatus = ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_PROJECT; // 2
            else if ($levelId == 1 && !$is_mr) $targetStatus = ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI; // 3
            else if ($levelId == 1 && $is_mr) $targetStatus = ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR; // 4
            else if ($levelId == 2 && $is_mr) $targetStatus = ProjectRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR; // 5

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

        // 4. Hitung Total & Return
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
