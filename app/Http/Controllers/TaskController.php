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

        foreach ($projects as $ppl) {
            $projectId = $ppl->project_id;

            // --- A. ANALISA BATCH STATUS ---
            $dataBatch = DataBatch::where('project_id', $projectId)->where('type', 2)->orderBy('batch', 'desc')->first();
            $batchStatus = $dataBatch ? $dataBatch->status : DataBatch::STATUS_PROSES;
            $batchStep = $dataBatch ? $dataBatch->step_verification : 0;

            $isFinished = ($batchStatus == DataBatch::STATUS_FINISH);
            $isRevision = in_array($batchStatus, [DataBatch::STATUS_REVISI, DataBatch::STATUS_REJECTED_FROM_MR]);

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
            $risks = ProjectRisk::where('project_periode_list_id', $ppl->id)->where('is_closed', 0)->get();
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
                $isMyTurn = ($u_step == $batchStep) || ($u_step == 2 && $batchStatus == DataBatch::STATUS_REJECTED_FROM_MR);
                $isValidStatus = in_array($batchStatus, [DataBatch::STATUS_KIRIM, DataBatch::STATUS_VERIFIKASI, DataBatch::STATUS_REJECTED_FROM_MR]);

                if ($isMyTurn && $isValidStatus && !$isFinished) {
                    $riskActionCount = $risks->where('step_verification', $u_step)->whereIn('status', [2, 3, 7, 8])->count();
                    if($riskActionCount > 0) {
                        $riskActionLabel = ($batchStatus == DataBatch::STATUS_REJECTED_FROM_MR) ? 'Dikembalikan MR' : 'Perlu Verifikasi';
                        if ($batchStatus == DataBatch::STATUS_REJECTED_FROM_MR) $isRiskUrgent = true;
                    }
                }
            }

            // --- C. MONITORING STATS (UPDATED LOGIC) ---
            // Ambil SEMUA data monitoring tahun ini untuk project ini sekaligus (Eager Loading manual)
            // Agar tidak query berulang di dalam loop bulan
            $allMonitorings = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($ppl) {
                    $q->where('project_periode_list_id', $ppl->id)
                      ->where('is_closed', 0); // Hanya monitoring untuk risiko aktif
                })
                ->where('tahun', $currentYear)
                ->get();

            $monitoringSummary = [];
            $monitoringActionCount = 0;
            $isMonUrgent = false;

            // Logic Target Status Monitoring (Untuk Verifikator)
            // Mapping: 2=RO Project, 3=RO Divisi, 4=RO MR, 5=ROW MR
            $targetMonStatus = 0;
            if ($levelId == 7) $targetMonStatus = 2;
            else if ($levelId == 1 && !$is_mr) $targetMonStatus = 3;
            else if ($levelId == 1 && $is_mr) $targetMonStatus = 4;
            else if ($levelId == 2 && $is_mr) $targetMonStatus = 5;

            $quarterMap = [1 => [1, 2, 3], 2 => [4, 5, 6], 3 => [7, 8, 9], 4 => [10, 11, 12]];

            foreach ($quarterMap as $q => $months) {
                $monthData = [];
                foreach ($months as $m) {
                    // Filter Koleksi (Bukan Query DB lagi) untuk performa
                    $monsInMonth = $allMonitorings->where('month', $m);

                    $statusM = 'empty';
                    $countPendingM = 0;
                    $monthName = date('M', mktime(0, 0, 0, $m, 10));

                    // Hanya proses bulan yang sudah lewat atau bulan ini (Opsional, tapi disini kita proses semua sesuai data)

                    if ($levelId == 6) {
                        // --- LOGIC INPUTTER ---
                        $countCreated = $monsInMonth->count();

                        // 1. Cek Revisi (Urgent)
                        // Status 1 (Draft/Revisi) DAN flag is_revision = true
                        $revisiCount = $monsInMonth->where('status', 1)->where('is_revision', true)->count();

                        // 2. Cek Draft (Pending)
                        // a. Status 1 DAN is_revision = false
                        $draftCount = $monsInMonth->where('status', 1)->where('is_revision', false)->count();

                        // b. Belum dibuat sama sekali (Total Risiko Aktif - Jumlah Monitoring Bulan ini)
                        // Jika risiko ada 5, tapi monitoring baru dibuat 3, berarti 2 belum started.
                        $unstartedCount = ($totalActiveRisks > 0) ? ($totalActiveRisks - $countCreated) : 0;
                        if ($unstartedCount < 0) $unstartedCount = 0; // Jaga-jaga

                        if ($revisiCount > 0) {
                            $statusM = 'revision';
                            $countPendingM = $revisiCount;
                            $isMonUrgent = true;
                        } elseif (($draftCount + $unstartedCount) > 0) {
                            $statusM = 'draft';
                            $countPendingM = $draftCount + $unstartedCount;
                        } elseif ($countCreated > 0 && $countCreated == $totalActiveRisks) {
                             $statusM = 'process'; // Sudah dikirim semua
                        }

                    } else {
                        // --- LOGIC VERIFIKATOR ---
                        if ($targetMonStatus > 0) {
                            // Hitung yang statusnya == Target Level User DAN belum approved
                            $pendingVerify = $monsInMonth->where('status', $targetMonStatus)
                                ->where('is_approved', false)
                                ->count();

                            // Cek jika ada pengembalian dari atas (Urgent)
                            // Jika user level 7 (target 2), cek jika status 2 tapi is_revision=true (dikembalikan level 3)
                            $returnedCount = $monsInMonth->where('status', $targetMonStatus)
                                ->where('is_revision', true)
                                ->count();

                            if ($returnedCount > 0) {
                                $statusM = 'revision'; // Merah (Dikembalikan dari atas)
                                $countPendingM = $returnedCount;
                                $isMonUrgent = true;
                            } elseif ($pendingVerify > 0) {
                                $statusM = 'pending'; // Kuning (Menunggu Verifikasi)
                                $countPendingM = $pendingVerify;
                            } elseif ($monsInMonth->count() > 0) {
                                $statusM = 'process'; // Sudah lewat / belum sampai
                            }
                        }
                    }

                    // Hanya masukkan jika ada data atau jika Inputter (untuk memperlihatkan slot kosong)
                    // Disini kita tampilkan semua bulan yang ada datanya
                    if ($monsInMonth->count() > 0 || ($levelId == 6 && $statusM == 'draft' && $totalActiveRisks > 0)) {
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

        // Sorting
        usort($taskList, function ($a, $b) {
            $priority = ['urgent' => 3, 'pending' => 2, 'safe' => 1];
            return $priority[$b['status_category']] <=> $priority[$a['status_category']];
        });

        return view('tasks.index', compact('taskList'));
    }

    public function getCount()
    {
        $user = Auth::user();
        $levelId = $user->level_id;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        // 1. Ambil ID Project Periode yang bisa diakses user
        // Kita gunakan pluck ID saja agar query ringan
        $projectPeriodeIds = ProjectPeriodeList::query()
            ->when(!Gate::check('project_admin_access'), function ($query) use ($user) {
                // Filter akses user (sesuaikan dengan logic permission project Anda)
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

        // 2. Hitung Actionable Items untuk Risk Register
        $riskCount = 0;

        if ($levelId == 6) {
            // === RISK OFFICER PROJECT ===
            // Hitung: Draft (1) + Revisi (5)
            $riskCount = ProjectRisk::whereIn('project_periode_list_id', $projectPeriodeIds)
                ->where('is_closed', 0)
                ->whereIn('status', [
                    ProjectRisk::STATUS_INPUT_DATA, // 1
                    ProjectRisk::STATUS_REJECTED    // 5
                ])
                ->count();
        } else {
            // === VERIFIKATOR ===
            // Tentukan step user (u_step)
            $u_step = 0;
            if ($levelId == 7) $u_step = 1; // Risk Owner Project
            else if ($levelId == 1) $u_step = $is_mr ? 3 : 2; // RO Divisi / RO MR
            else if ($levelId == 2 && $is_mr) $u_step = 4; // Risk Owner MR

            if ($u_step > 0) {
                // Hitung risiko yang step_verification-nya ada di user ini
                $riskCount = ProjectRisk::whereIn('project_periode_list_id', $projectPeriodeIds)
                    ->where('is_closed', 0)
                    ->where('step_verification', $u_step)
                    ->whereIn('status', [
                        ProjectRisk::STATUS_DIKIRIM, // 2
                        ProjectRisk::STATUS_TUNGGU_VERIFIKASI, // 3
                        ProjectRisk::STATUS_REJECTED_FROM_OFFICER_MR, // 7
                        ProjectRisk::STATUS_REJECTED_FROM_OWNER_MR  // 8
                    ])
                    ->count();
            }
        }

        // 3. Hitung Actionable Items untuk Monitoring
        $monitoringCount = 0;
        $currentYear = date('Y');

        // Base Query untuk Monitoring (Filter Project & Tahun)
        $monQuery = ProjectRiskMonitoring::query()
            ->where('tahun', $currentYear)
            ->whereHas('projectRisk', function($q) use ($projectPeriodeIds) {
                $q->whereIn('project_periode_list_id', $projectPeriodeIds)
                  ->where('is_closed', 0);
            });

        if ($levelId == 6) {
            // === RISK OFFICER PROJECT ===
            // Hitung: Draft Baru (1) ATAU Revisi (is_revision = true & belum approved)
            // Note: Biasanya status revisi tetap 1 atau kembali ke 1.
            $monitoringCount = $monQuery->where(function($q) {
                $q->where('status', ProjectRiskMonitoring::STATUS_DRAFT_REVISI); // 1
            })->count();
        } else {
            // === VERIFIKATOR ===
            // Tentukan Target Status Monitoring yang harus diverifikasi user ini
            // Mapping Status Monitoring:
            // 2=RO Project, 3=RO Divisi, 4=RO MR, 5=ROW MR

            $targetStatus = 0;
            if ($levelId == 7) $targetStatus = ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_PROJECT; // 2
            else if ($levelId == 1 && !$is_mr) $targetStatus = ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI; // 3
            else if ($levelId == 1 && $is_mr) $targetStatus = ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR; // 4
            else if ($levelId == 2 && $is_mr) $targetStatus = ProjectRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR; // 5

            if ($targetStatus > 0) {
                $monitoringCount = $monQuery
                    ->where('status', $targetStatus)
                    ->where('is_approved', false) // Belum disetujui
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
