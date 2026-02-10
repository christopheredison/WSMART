<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\Periode;
use App\Models\Project;
use App\Models\ProjectPeriodeList;
use App\Models\ProjectRisk;
use App\Models\ProjectRiskMonitoring;
use App\Models\DataBatch;
use App\Models\RiskMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class ProjectPeriodeListController extends BasicCRUDController
{
    protected $model = ProjectPeriodeList::class;
    protected $basePermission = 'project_periode';
    protected $resourceName = 'Proyek';
    protected $baseRoute = 'project-periode-list.';
    protected $userProjectIdsx = [];

    protected $tableColumns = [
        'project_code' => [
            'label' => 'Kode',
            'data' => 'project.meta.profit_center',
            'name' => 'profit_center',
            'render' => '(data, type, row) => row.project?.meta?.profit_center || "-"',
            'orderable' => true,
            'searchable' => true,
        ],
        'cost_center_parent' => [
            'label' => 'Divisi',
            'data' => 'project.cost_center_parent',
            'name' => 'cost_center_parent',
            'render' => '(data, type, row) => row.project?.divisi?.name || "-"',
            'orderable' => true,
            'searchable' => true,
        ],
        'project_id' => [
            'label' => 'Proyek',
            'data' => 'project.project_name',
            'name' => 'projects.project_name',
            'render' => '(data, type, row) => row.project?.project_name || "-"',
            'orderable' => true,
            'searchable' => true,
            'class' => 'fw-bold',
        ],
        'ok' => [
            'label' => 'Nilai OK',
            'data' => 'nk',
            'name' => 'projects.nk',
            'render' => '(data, type, row) => row.nk ? Intl.NumberFormat(\'id-ID\').format(row.nk) : "-"',
            'orderable' => true,
            'searchable' => true,
        ],
        'tanggal_mulai' => [
            'label' => 'Tanggal Mulai',
            'data' => 'tanggal_mulai',
            'name' => 'projects.tanggal_mulai',
            'render' => '(data, type, row) => {
                if (!row.tanggal_mulai) return "-";
                const date = new Date(row.tanggal_mulai + "T00:00:00");
                return date.toLocaleDateString("id-ID", { day:"numeric", month:"short", year:"numeric" });
            }',
            'orderable' => true,
            'searchable' => true,
            'class' => 'mw-10r',
        ],
        'skala_risiko' => [
            'label' => 'Nilai Risiko',
            'data' => 'skala_risiko',
            'name' => 'skala_risiko',
            'render' => <<<JS
                (data) => data ? Intl.NumberFormat('id-ID').format(data) : '-'
                JS,
            'orderable' => true,
            'searchable' => true,
        ],
        'project_risks_count' => [
            'label' => 'Jumlah Risiko',
            'data' => 'project_risks_count',
            'name' => 'project_risks_count',
            'orderable' => true,
            'searchable' => false,
        ],
        'status_risiko_html' => [
            'label' => 'Status Risiko',
            'data' => 'status_risiko_html',
            'orderable' => false,
            'searchable' => false,
        ],
        'status_monitoring_html' => [
            'label' => 'Status Monitoring',
            'data' => 'status_monitoring_html',
            'orderable' => false,
            'searchable' => false,
        ],
    ];

    private function userHasAccessToProject($user, $row)
    {
        // 1. Cek assignment langsung (logic lama)
        if ($user->hasProject($row)) {
            return true;
        }

        // 2. Cek permission Admin
        if (Gate::check('project_admin_access')) {
            return true;
        }

        // 3. Cek permission Divisi & Kesamaan Cost Center
        if (Gate::check('can_access_project_under_division')) {
            // Pastikan user punya unit, project ada, dan cost center sama
            if ($user->unit && $row->project && $row->project->cost_center_parent == $user->unit->cost_center) {
                return true;
            }
        }

        return false;
    }

    public function index() {
        request()->merge([
            'append' => ['project.divisi', 'project.projectSektor'],
            'with' => [
                'project',
                'projectRisks' => function($q) {
                    $q->select('id', 'project_periode_list_id', 'status', 'is_closed', 'step_verification');
                },
                'project.dataBatches' => function($q) {
                    $q->where('type', 2)->orderBy('batch', 'desc')->limit(1);
                }
            ],
        ]);

        $user = request()->user();
        $user->load('projects', 'unit');

        $userProjectIds = $user->projects->pluck('id');
        $this->userProjectIdsx = $user->projects->pluck('id')->toArray();

        $unitProjectIds = collect([]);
        // if ($user->unit && !in_array($user->level_id ?? 0, [6, 7])) {
        //     $unitProjectIds = $user->unit->projects()->pluck('id');
        // }

        if ($user->unit && Gate::check('can_access_project_under_division')) {
            $unitProjectIds = $user->unit->projects()->pluck('id');
        }

        $allProjectIds = $userProjectIds->merge($unitProjectIds)->unique();

        // --- 1. Tentukan Step User Saat Ini (Untuk Highlight & Sorting) ---
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = $this->getUserVerificationStep($user->level_id, $is_mr);
        $u_step = $verificationData['u_step'];
        $levelId = $user->level_id;

        $this->setupAvailableFilters();

        // --- 2. CALLBACK QUERY UTAMA  ---
        $this->callbackQuery = function ($query) use ($userProjectIds, $unitProjectIds, $allProjectIds, $user, $u_step, $levelId, $is_mr) {
            // Join tabel projects
            $query->join('projects', 'project_periode_lists.project_id', '=', 'projects.id');
            // Join tabel units untuk Divisi
            $query->leftJoin('units', 'projects.cost_center_parent', '=', 'units.cost_center');

            $query->select([
                'project_periode_lists.*',
                'projects.project_name',
                'projects.cost_center_parent',
                'projects.nk',
                'projects.tanggal_mulai',
                'units.name as divisi_name', // Ambil nama divisi untuk sorting
            ]);

            // Subquery untuk Jumlah Risiko (agar bisa disort)
            $query->selectSub(function ($q) {
                $q->from('project_risks')
                  ->whereColumn('project_periode_lists.id', 'project_risks.project_periode_list_id')
                  ->whereNull('deleted_at')
                  ->selectRaw('count(*)');
            }, 'project_risks_count');

            if (!Gate::check('project_admin_access')) {
                if (Gate::check('can_access_project_under_division')) {
                    $query->whereIn('project_periode_lists.project_id', $allProjectIds);
                } else {
                    $query->whereIn('project_periode_lists.project_id', $userProjectIds);
                }
            }

            // --- FILTERING HAK AKSES ---
            // if (!Gate::check('project_periode_view')) {
            //     if (in_array($user->level_id ?? 0, [6, 7])) {
            //         $query->whereIn('project_periode_lists.project_id', $userProjectIds);
            //     } else if ($user->unit) {
            //         $query->whereIn('project_periode_lists.project_id', $allProjectIds);
            //     } else {
            //         $query->whereIn('project_periode_lists.project_id', $userProjectIds);
            //     }
            // }

            // DEFAULT ORDERING hanya saat tidak ada sorting dari user
            if (!request()->has('order')) {
                // Subquery Batch Terakhir
                $latestBatchIdSql = "(SELECT MAX(sub_db.id) FROM data_batches sub_db WHERE sub_db.project_id = project_periode_lists.project_id AND sub_db.type = 2)";

                // 1. Logic SQL: Apakah Risiko Butuh Aksi?
                $riskActionNeededSql = "FALSE";

                if ($levelId == 6) {
                    // INPUTTER (Level 6):
                    // Action Needed jika: Batch Draft (1) ATAU Revisi (5)
                    $riskActionNeededSql = "EXISTS (
                        SELECT 1 FROM data_batches db
                        WHERE db.id = $latestBatchIdSql
                        AND db.finish IS FALSE
                        AND db.status IN (1, 5)
                    )";
                } elseif ($u_step > 0) {
                    // VERIFIKATOR:
                    // Action Needed jika:
                    // A. Step batch SAMA dengan step user (u_step)
                    // B. ATAU Kasus Reject Khusus (Step 2 urus status 9, Step 3 urus status 10)

                    $orCondition = "FALSE";
                    if ($u_step == 2) $orCondition = "db.status = 9";  // Reject dr MR ke Divisi
                    if ($u_step == 3) $orCondition = "db.status = 10"; // Reject dr Owner MR ke Officer MR

                    $riskActionNeededSql = "EXISTS (
                        SELECT 1 FROM data_batches db
                        WHERE db.id = $latestBatchIdSql
                        AND db.finish IS FALSE
                        AND (
                            db.step_verification = {$u_step}
                            OR ($orCondition)
                        )
                    )";
                }

                // 2. Logic SQL: Apakah Monitoring Butuh Aksi?
                $monActionNeededSql = "FALSE";
                $monTargetStatus = 0;

                // Tentukan Target Status berdasarkan Level User
                if ($levelId == 6) $monTargetStatus = 1; // Inputter
                elseif ($levelId == 7) $monTargetStatus = 2; // ROP
                elseif ($levelId == 1 && !$is_mr) $monTargetStatus = 3; // ROD
                elseif ($levelId == 1 && $is_mr) $monTargetStatus = 4; // RO MR
                elseif ($levelId == 2 && $is_mr) $monTargetStatus = 5; // ROW MR

                if ($monTargetStatus > 0) {
                    // Subquery ID monitoring terakhir (Global Reference)
                    $latestMonIdSql = "
                        SELECT MAX(sub_prm.id)
                        FROM project_risk_monitorings sub_prm
                        JOIN project_risks sub_pr ON sub_pr.id = sub_prm.risiko_id
                        WHERE sub_pr.project_periode_list_id = project_periode_lists.id
                    ";

                    // Subquery untuk Referensi Tahun & Bulan
                    $refYearSql = "(SELECT ref_prm.tahun FROM project_risk_monitorings ref_prm WHERE ref_prm.id = ($latestMonIdSql))";
                    $refMonthSql = "(SELECT ref_prm.month FROM project_risk_monitorings ref_prm WHERE ref_prm.id = ($latestMonIdSql))";

                    $monActionNeededSql = "EXISTS (
                        SELECT 1
                        FROM project_risk_monitorings prm
                        JOIN project_risks pr ON pr.id = prm.risiko_id
                        WHERE pr.project_periode_list_id = project_periode_lists.id
                          AND pr.is_closed IS FALSE

                          -- Pastikan Waktu (Bulan/Tahun) Sesuai Referensi Terakhir
                          AND prm.tahun = $refYearSql
                          AND prm.month = $refMonthSql

                          -- --- PERBAIKAN PENTING: FILTER HANYA ID TERAKHIR PER RISIKO ---
                          AND prm.id = (
                              SELECT MAX(sub_prm.id)
                              FROM project_risk_monitorings sub_prm
                              WHERE sub_prm.risiko_id = pr.id
                              AND sub_prm.tahun = prm.tahun
                              AND sub_prm.month = prm.month
                          )
                          -- -----------------------------------------------------------

                          AND prm.status = {$monTargetStatus}
                          AND prm.is_approved IS FALSE
                    )";
                }

                $userIdList = $userProjectIds->isNotEmpty() ? $userProjectIds->join(',') : '0';

                // 3. APPLY ORDERING
                // Perubahan: Menghapus pengecekan 'IN userIdList' pada Prioritas 1
                // Agar semua yang butuh verifikasi (baik project sendiri atau project bawahan) naik ke atas.

                // $query->orderByRaw("
                //     CASE
                //         -- PRIORITAS 1: 'Perlu Verifikasi' / 'Perlu Revisi' / 'Draft' (My Turn)
                //         WHEN ($riskActionNeededSql OR $monActionNeededSql) THEN 1

                //         -- PRIORITAS 2: 'Proses Validasi' (Project Saya tapi menunggu orang lain)
                //         WHEN project_periode_lists.project_id IN ({$userIdList}) THEN 2

                //         -- PRIORITAS 3: Project Lainnya (View Only / Divisi)
                //         ELSE 3
                //     END ASC
                // ");

                $query->orderByRaw("
                    CASE
                        -- PRIORITAS 1: Action Needed (Risk Register)
                        WHEN ($riskActionNeededSql) THEN 1

                        -- PRIORITAS 2: Action Needed (Monitoring)
                        WHEN ($monActionNeededSql) THEN 2

                        -- PRIORITAS 3: Project Saya (Proses Validasi / Menunggu)
                        WHEN project_periode_lists.project_id IN ({$userIdList}) THEN 3

                        -- PRIORITAS 4: Project Lainnya
                        ELSE 4
                    END ASC
                ");

                // Secondary Sort: Yang baru diupdate di atas
                $query->orderBy('project_periode_lists.updated_at', 'desc');
            }
        };

        // --- DATATABLE CALLBACK ---
        $this->datatableCallback = function ($dataTable) use ($userProjectIds, $allProjectIds, $user, $u_step, $levelId, $is_mr) {
            // Render Status Risiko
            $dataTable->addColumn('status_risiko_html', function ($row) use ($user, $u_step, $levelId) {
                return $this->generateRiskStatus($row, $user, $u_step, $levelId);
            });

            // Render Status Monitoring
            $dataTable->addColumn('status_monitoring_html', function ($row) use ($user, $u_step, $levelId) {
                return $this->generateMonitoringStatus($row, $user, $u_step, $levelId);
            });

            // Format Nilai OK
            $dataTable->editColumn('ok', function ($row) {
                return $row->nk ? number_format($row->nk, 0, ',', '.') : "-";
            });

            // Format Tanggal Mulai
            $dataTable->editColumn('tanggal_mulai', function ($row) {
                return $row->tanggal_mulai ? \Carbon\Carbon::parse($row->tanggal_mulai)->toDateString() : null;
            });

            // --- SORTING ---
            // 1. Sort Proyek
            $dataTable->orderColumn('projects.project_name', function ($query, $order) {
                $query->orderByRaw("LOWER(projects.project_name) $order");
            });

            // 2. Sort Profit Center (Kode)
            $dataTable->orderColumn('profit_center', function ($query, $order) {
                $query->orderByRaw("projects.meta->>'profit_center' $order");
            });

            // 3. Sort Divisi (Nama Unit)
            $dataTable->orderColumn('cost_center_parent', function ($query, $order) {
                $query->orderBy('units.name', $order);
            });

            // 4. Sort Nilai OK (NK)
            $dataTable->orderColumn('projects.nk', function ($query, $order) {
                $query->orderByRaw("projects.nk::numeric {$order} NULLS LAST");
            });

            // 3. Sort Tanggal Mulai
            $dataTable->orderColumn('projects.tanggal_mulai', function ($query, $order) {
                $query->orderByRaw("projects.tanggal_mulai::date {$order} NULLS LAST");
            });

            // 4. Sort Nilai Risiko (Pastikan merujuk ke tabel utama agar tidak ambigu)
            $dataTable->orderColumn('skala_risiko', function ($query, $order) {
                $query->orderByRaw("CAST(NULLIF(CAST(project_periode_lists.skala_risiko AS TEXT), '') AS NUMERIC) $order NULLS LAST");
            });

            // 5. Sort Jumlah Risiko (Subquery alias)
            $dataTable->orderColumn('project_risks_count', function ($query, $order) {
                $query->orderBy('project_risks_count', $order);
            });

            // --- Filtering Kolom Global/Spesifik ---
            $dataTable->filterColumn('profit_center', function($query, $keyword) {
                $query->whereRaw("projects.meta->>'profit_center' ilike ?", ["%{$keyword}%"]);
            });

            $dataTable->filterColumn('projects.project_name', function($query, $keyword) {
                $query->where('projects.project_name', 'ilike', "%{$keyword}%");
            });

            // Definisikan filterColumn untuk 'divisi' jika search bar Datatable mengetik nama divisi
            $dataTable->filterColumn('cost_center_parent', function($query, $keyword) { // Diperbaiki dari const_center_parent
                $query->whereHas('project.divisi', function($q) use($keyword) {
                    $q->where('name', 'ilike', "%{$keyword}%");
                });
            });

            // Filter untuk Nilai OK (nk) - Cast to TEXT for Postgres
            $dataTable->filterColumn('projects.nk', function ($query, $keyword) {
                $query->whereRaw("projects.nk::text ILIKE ?", ["%{$keyword}%"]);
            });

            // Filter untuk Nilai Risiko (skala_risiko) - Cast to TEXT for Postgres
            $dataTable->filterColumn('skala_risiko', function($query, $keyword) {
                $query->whereRaw("CAST(project_periode_lists.skala_risiko AS TEXT) ilike ?", ["%{$keyword}%"]);
            });

            // Filter untuk Tanggal Mulai - Cast to TEXT for Postgres
            $dataTable->filterColumn('projects.tanggal_mulai', function ($query, $keyword) {
                $query->whereRaw("projects.tanggal_mulai::text ILIKE ?", ["%{$keyword}%"]);
            });

            $dataTable->addColumn('action_needed', function ($row) use ($user, $u_step, $levelId) {
                $riskStatus = $this->checkRiskActionNeeded($row, $user, $u_step, $levelId);
                $monStatus = $this->checkMonitoringActionNeeded($row, $user, $u_step, $levelId);
                return ($riskStatus || $monStatus);
            });

            // Kolom Button Actions Permissions
            $dataTable->addColumn('has_view', function ($data) use ($user) {
                return Gate::check('project_periode_view') ||
                    $user->hasProject($data) ||
                    ($user->unit && $data->project && $data->project->cost_center_parent == $user->unit->cost_center);
            });
            $dataTable->addColumn('has_risk_register', function ($data) use ($user) {
                return Gate::check('project_admin_access') ||
                    $user->hasProject($data) ||
                    ($user->unit && $data->project && $data->project->cost_center_parent == $user->unit->cost_center);
            });
            $dataTable->addColumn('has_monitoring', function ($data) use ($user) {
                return Gate::check('project_admin_access') ||
                    $user->hasProject($data) ||
                    ($user->unit && $data->project && $data->project->cost_center_parent == $user->unit->cost_center);
            });
            $dataTable->addColumn('has_risk_context', function ($data) use ($user) {
                return Gate::check('project_admin_access') ||
                    $user->hasProject($data) ||
                    ($user->unit && $data->project && $data->project->cost_center_parent == $user->unit->cost_center);
            });
            $dataTable->addColumn('has_calculate', function () {
                return Gate::check('project_risk_recalculate');
            });

            $dataTable->rawColumns(['status_risiko_html', 'status_monitoring_html']);

            // --- DEFAULT ORDERING (PRIORITAS) ---
            // $dataTable->order(function ($query) use ($user, $levelId, $u_step, $is_mr, $userProjectIds, $allProjectIds) {
            //     if (!request()->has('order')) {
            //         $riskActionNeededSql = "FALSE";
            //         $latestBatchIdSql = "(SELECT MAX(sub_db.id) FROM data_batches sub_db WHERE sub_db.project_id = project_periode_lists.project_id AND sub_db.type = 2)";

            //         if ($levelId == 6) { // Inputter
            //             $riskActionNeededSql = "EXISTS (SELECT 1 FROM data_batches db WHERE db.id = $latestBatchIdSql AND db.finish IS FALSE AND (db.status = 5 OR db.status = 1))";
            //         } elseif ($u_step > 0) { // Verifikator
            //             $rejectConditions = "";
            //             if ($u_step == 2) $rejectConditions = "OR db.status = 9";
            //             elseif ($u_step == 3) $rejectConditions = "OR db.status = 10";

            //             $riskActionNeededSql = "EXISTS (SELECT 1 FROM data_batches db WHERE db.id = $latestBatchIdSql AND db.finish IS FALSE AND (db.step_verification = {$u_step} {$rejectConditions}))";
            //         }

            //         $monActionNeededSql = "FALSE";
            //         $monTargetStatus = 0;
            //         if ($levelId == 6) $monTargetStatus = 1;
            //         elseif ($levelId == 7) $monTargetStatus = 2;
            //         elseif ($levelId == 1 && !$is_mr) $monTargetStatus = 3;
            //         elseif ($levelId == 1 && $is_mr) $monTargetStatus = 4;
            //         elseif ($levelId == 2 && $is_mr) $monTargetStatus = 5;

            //         if ($monTargetStatus > 0) {
            //             $monActionNeededSql = "EXISTS (SELECT 1 FROM project_risk_monitorings prm JOIN project_risks pr ON pr.id = prm.risiko_id WHERE pr.project_periode_list_id = project_periode_lists.id AND prm.id = (SELECT MAX(sub_prm.id) FROM project_risk_monitorings sub_prm JOIN project_risks sub_pr ON sub_pr.id = sub_prm.risiko_id WHERE sub_pr.project_periode_list_id = project_periode_lists.id) AND prm.status = {$monTargetStatus} AND prm.is_approved IS FALSE)";
            //         }

            //         $userIdList = $userProjectIds->isNotEmpty() ? $userProjectIds->join(',') : '0';
            //         $allIdList = $allProjectIds->isNotEmpty() ? $allProjectIds->join(',') : '0';

            //         $query->orderByRaw("
            //             CASE
            //                 WHEN project_periode_lists.project_id IN ({$userIdList}) AND ($riskActionNeededSql OR $monActionNeededSql) THEN 1
            //                 WHEN project_periode_lists.project_id IN ({$userIdList}) THEN 2
            //                 WHEN project_periode_lists.project_id IN ({$allIdList}) THEN 3
            //                 ELSE 4
            //             END ASC
            //         ");

            //         $query->orderBy('project_periode_lists.updated_at', 'desc');
            //     }
            // });
        };

        $projectOptions = Project::select('id', 'project_name')->orderBy('project_name');
        if (!Gate::check('project_admin_access')) {
            $projectOptions->whereIn('id', $userProjectIds);
        }
        $projectOptions = $projectOptions->get()->pluck('project_name', 'id')->toArray();

        $this->tableLegend= [];

        if (Gate::check('project_periode_view')) {
            $this->tableActions[] = [
                'btn_icon' => true,
                'label' => '<span class="bx bx-show" title="View"></span>',
                'action' => 'link',
                'url' => route('project-periode-list.show', ':id'),
                'active_state' => '(data, type, row) => row.has_view',
                'title' => 'View Project'
            ];
            $this->tableLegend[] = ['icon' => '<span class="bx bx-show-alt"></span>', 'label' => 'View Project'];
        }

        if (Gate::check('project_risk_list')) {
            $this->tableActions[] = [
                'btn_icon' => true,
                'label' => '<span class="bx bx-list-check" title="Risk Register"></span>',
                'action' => 'link',
                'url' => route('projects.risks.index', ['project' => ':id']),
                'active_state' => '(data, type, row) => row.has_risk_register',
                'title' => 'Risk Register'
            ];
            $this->tableLegend[] = ['icon' => '<span class="bx bx-list-check"></span>', 'label' => 'Risk Register'];
        }

        if (Gate::check('project_monitoring_list')) {
            $this->tableActions[] = [
                'btn_icon' => true,
                'label' => '<span class="bx bx-radar" title="Monitoring"></span>',
                'action' => 'link',
                'url' => route('projects.monitorings.index', ['project' => ':id']),
                'active_state' => '(data, type, row) => row.has_monitoring',
                'title' => 'Monitoring'
            ];
            $this->tableLegend[] = ['icon' => '<span class="bx bx-radar"></span>', 'label' => 'Monitoring'];
        }

        if (Gate::check('project_led_list')) {
            $ledRoute = route('project-led.index-by-project', ['projectId' => ':id']);
            $this->tableActions[] = [
                'btn_icon' => true,
                'label' => '<span class="bx bx-dock-bottom" title="Loss Event"></span>',
                'action' => 'script',
                'script' => <<<JS
                projectData = fetchedData[\$(this).data('id')];window.location.href = "$ledRoute".replace(':id', projectData.project_id);
                JS,
                'title' => 'Loss Event'
            ];
            $this->tableLegend[] = ['icon' => '<span class="bx bx-dock-bottom"></span>', 'label' => 'Loss Event'];
        }

        if (Gate::check('project_risk_context')) {
            $this->tableActions[] = [
                'btn_icon' => true,
                'label' => '<span class="bx bx-target-lock" title="Risk Context"></span>',
                'action' => 'link',
                'url' => route('project-risk-context.index-by-project-periode', ['projectId' => ':id']),
                'active_state' => '(data, type, row) => row.has_risk_context',
                'title' => 'Risk Context'
            ];
            $this->tableLegend[] = ['icon' => '<span class="bx bx-target-lock"></span>', 'label' => 'Risk Context'];
        }

        $recalculateRoute = route('project-periode-list.recalculate', ':id');
        $this->tableActions[] = [
            'label' => '<i class="bx bx-refresh"></i>',
            'title' => 'Recalculate',
            'btn_icon' => 'btn-input-icon',
            'icon' => 'bx bx-refresh',
            'active_state' => '(data, type, row) => row?.has_calculate',
            'action' => 'script',
            'script' => <<<JS
                Swal.fire({
                    title: "Hitung ulang data?",
                    text: "Proses ini akan menghitung ulang Analisa dan Peta Risiko Terkini",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "Ya, Hitung ulang",
                    cancelButtonText: "Batal",
                    reverseButtons: true,
                    showCloseButton: true,
                    showConfirmButton: true,
                    showCancelButton: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "$recalculateRoute".replace(":id", $(this).data('id')),
                            type: "GET",
                            success: function(response) {
                                Swal.fire("Berhasil", "Data berhasil dihitung ulang", "success");
                                $('.ajax-datatable').DataTable().ajax.reload();
                            }
                        });
                    }
                });
            JS,
        ];

        $this->extraViewData['showKamusRisikoButton'] = true;

        return parent::index();
    }

    private function setupAvailableFilters() {
        $divisiOptions = \App\Models\Project::query()
            ->join('units', 'projects.cost_center_parent', '=', 'units.cost_center')
            ->select('units.name', 'units.cost_center')
            ->distinct()
            ->orderBy('units.name', 'asc')
            ->pluck('units.name', 'units.cost_center')
            ->toArray();

        $this->availableFilters = [
            'divisi' => [
                'label' => 'Filter Divisi',
                'type' => 'select',
                'classWrapper' => 'col-4',
                'parameters' => ['divisi', $divisiOptions, null, ['class' => 'form-select select2', 'placeholder' => 'Semua Divisi']],
                'handler' => function($query, $key, $value) {
                    if (!empty($value)) {
                        $query->where('projects.cost_center_parent', $value);
                    }
                }
            ],
            // --- UPDATE FILTER STATUS RISIKO ---
            'status_risiko' => [
                'label' => 'Status Risiko',
                'type' => 'select',
                'parameters' => [
                    'status_risiko',
                    [
                        'draft' => 'Draft / Input Risiko', // Status 1
                        'revisi' => 'Perlu Revisi / Dikembalikan', // Status 5, 9, 10 (Merah)
                        'verification' => 'Proses Verifikasi',     // Status 2, 3, 4
                        'active' => 'Selesai',             // Finish = true
                    ],
                    null,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Semua Status Risiko'
                    ]
                ],
                'handler' => function($query, $key, $value) {
                    if (empty($value)) return;

                    // Subquery Batch Terakhir (Untuk Draft, Revisi, Verifikasi)
                    $latestBatchSql = "(SELECT MAX(db2.id) FROM data_batches db2 WHERE db2.project_id = project_periode_lists.project_id AND db2.type = 2)";

                    if ($value === 'active') {
                        // LOGIKA BARU: Selesai jika punya risiko DAN tidak ada satupun risiko yang statusnya BUKAN 6
                        $query->whereRaw("
                            (SELECT COUNT(*) FROM project_risks pr WHERE pr.project_periode_list_id = project_periode_lists.id AND pr.deleted_at IS NULL) > 0
                            AND
                            (SELECT COUNT(*) FROM project_risks pr WHERE pr.project_periode_list_id = project_periode_lists.id AND pr.status != 6 AND pr.deleted_at IS NULL) = 0
                        ");
                    } elseif ($value === 'draft') {
                        // LOGIKA BARU: Batch Status 1 ATAU 0, TAPI Kecualikan yang sudah 'Selesai' (semua status 6)
                        // Menggunakan kondisi: Masih ada minimal 1 risiko yang statusnya BUKAN 6
                        $query->whereRaw("EXISTS (
                            SELECT 1 FROM data_batches db
                            WHERE db.id = $latestBatchSql
                            AND db.finish IS FALSE
                            AND db.status IN (0, 1)
                        )")
                        ->whereRaw("(SELECT COUNT(*) FROM project_risks pr WHERE pr.project_periode_list_id = project_periode_lists.id AND pr.status != 6 AND pr.deleted_at IS NULL) > 0");
                    } elseif ($value === 'revisi') {
                        // Logic Batch: Status 5, 9, 10
                        $query->whereRaw("EXISTS (
                            SELECT 1 FROM data_batches db
                            WHERE db.id = $latestBatchSql
                            AND db.finish IS FALSE
                            AND db.status IN (5, 9, 10)
                        )");
                    } elseif ($value === 'verification') {
                        // Logic Batch: Status 2, 3, 4, 7, 8
                        $query->whereRaw("EXISTS (
                            SELECT 1 FROM data_batches db
                            WHERE db.id = $latestBatchSql
                            AND db.finish IS FALSE
                            AND db.status IN (2, 3, 4, 7, 8)
                        )");
                    }
                }
            ],
            // --- UPDATE FILTER STATUS MONITORING ---
            'status_monitoring' => [
                'label' => 'Status Monitoring',
                'type' => 'select',
                'parameters' => [
                    'status_monitoring',
                    [
                        'empty' => 'Belum Dimonitor',
                        'draft' => 'Draft / Input Monitoring', // Status 1
                        'verification' => 'Proses Verifikasi',     // Status 2, 3, 4, 5
                        'active' => 'Disetujui',           // Status 100 atau is_approved
                    ],
                    null,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Semua Status Monitoring'
                    ]
                ],
                'handler' => function($query, $key, $value) {
                    if (empty($value)) return;
                    $latestMonSql = "(SELECT MAX(prm2.id) FROM project_risk_monitorings prm2 JOIN project_risks pr2 ON pr2.id = prm2.risiko_id WHERE pr2.project_periode_list_id = project_periode_lists.id)";

                    if ($value === 'empty') {
                        // Tidak ada data monitoring
                        $query->whereRaw("NOT EXISTS (SELECT 1 FROM project_risk_monitorings prm JOIN project_risks pr ON pr.id = prm.risiko_id WHERE pr.project_periode_list_id = project_periode_lists.id)");
                    } elseif ($value === 'active') {
                        // Status 100 ATAU is_approved = true
                        $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND (prm.status = 100 OR prm.is_approved IS TRUE))");
                    } elseif ($value === 'draft') {
                        // Status 1 (Drafting) dan belum approved
                        $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND prm.status = 1 AND prm.is_approved IS FALSE)");
                    } elseif ($value === 'verification') {
                        // Status 2, 3, 4, 5 dan belum approved
                        $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND prm.status IN (2, 3, 4, 5) AND prm.is_approved IS FALSE)");
                    }
                }
            ],
        ];
    }

    private function checkRiskActionNeeded($row, $user, $u_step, $levelId)
    {
        $lastBatch = $row->project->dataBatches->sortByDesc('id')->first();
        if ($lastBatch && $lastBatch->finish) return false;

        $batchStep = $lastBatch ? $lastBatch->step_verification : 0;
        $batchStatus = $lastBatch ? $lastBatch->status : 1;
        $isMyTurn = false;

        if ($levelId == 6) {
            if (in_array($batchStatus, [1, 5])) $isMyTurn = true;
        } else {
            if (($u_step == $batchStep) ||
                ($u_step == 2 && $batchStatus == 9) ||
                ($u_step == 3 && $batchStatus == 10)) {
                $isMyTurn = true;
            }
        }
        // return $isMyTurn && $user->hasProject($row);
        return $isMyTurn && $this->userHasAccessToProject($user, $row);
    }

    // private function checkMonitoringActionNeeded($row, $user, $u_step, $levelId)
    // {
    //     // Query sederhana untuk kebutuhan flag
    //     $latestMon = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
    //         $q->where('project_periode_list_id', $row->id);
    //     })->orderBy('id', 'desc')->first();

    //     // if (!$latestMon || $latestMon->status == 100) return false;
    //     if (!$latestMon || $latestMon->status == 100 || $latestMon->is_approved) return false;

    //     $isMyMonTurn = false;
    //     if ($levelId == 6) {
    //         if ($latestMon->status == 1) $isMyMonTurn = true;
    //     } else {
    //         $target = 0;
    //         if ($levelId == 7) $target = 2;
    //         elseif ($levelId == 1 && !$user->unit->unit_mr) $target = 3;
    //         elseif ($levelId == 1 && $user->unit->unit_mr) $target = 4;
    //         elseif ($levelId == 2 && $user->unit->unit_mr) $target = 5;

    //         if ($latestMon->status == $target && !$latestMon->is_approved) {
    //             $isMyMonTurn = true;
    //         }
    //     }
    //     // return $isMyMonTurn && $user->hasProject($row);
    //     return $isMyMonTurn && $this->userHasAccessToProject($user, $row);
    // }

    private function checkMonitoringActionNeeded($row, $user, $u_step, $levelId)
    {
        // 1. Ambil Acuan Waktu (Bulan/Tahun) dari data paling akhir di project ini
        $referenceMon = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
            $q->where('project_periode_list_id', $row->id);
        })->orderBy('id', 'desc')->first();

        if (!$referenceMon) return false;

        // 2. Tentukan Target Status
        $targetStatus = 0;
        if ($levelId == 6) $targetStatus = 1;      // Inputter
        elseif ($levelId == 7) $targetStatus = 2;  // ROP
        elseif ($levelId == 1 && !$user->unit->unit_mr) $targetStatus = 3; // ROD
        elseif ($levelId == 1 && $user->unit->unit_mr) $targetStatus = 4;  // RO MR
        elseif ($levelId == 2 && $user->unit->unit_mr) $targetStatus = 5;  // ROW MR

        if ($targetStatus === 0) return false;

        // 3. Cek Action Needed
        // Logic: Cari Risiko Aktif -> Ambil Monitoring Terakhirnya di Bulan Itu -> Cek Statusnya
        $hasAction = ProjectRisk::where('project_periode_list_id', $row->id)
            ->where('is_closed', false) // Risiko Masih Open
            ->whereHas('projectRiskMonitorings', function($q) use ($referenceMon, $targetStatus) {
                $q->where('tahun', $referenceMon->tahun)
                  ->where('month', $referenceMon->month)

                  // --- PERBAIKAN PENTING DI SINI ---
                  // Pastikan hanya mengecek Monitoring dengan ID Terakhir (Latest) untuk risiko ini
                  ->whereRaw('id = (
                      SELECT MAX(sub.id)
                      FROM project_risk_monitorings as sub
                      WHERE sub.risiko_id = project_risk_monitorings.risiko_id
                      AND sub.tahun = ?
                      AND sub.month = ?
                  )', [$referenceMon->tahun, $referenceMon->month])
                  // ---------------------------------

                  ->where('status', $targetStatus)
                  ->where('is_approved', false);
            })
            ->exists();

        return $hasAction && $this->userHasAccessToProject($user, $row);
    }

    private function generateRiskStatus($row, $user, $u_step, $levelId)
    {
        // 1. Cek Data Kosong
        if ($row->project_risks_count == 0) {
            return '<span class="badge bg-light text-dark border border-dark">Tidak Aktif</span>';
        }

        // Ambil data batch terakhir
        $lastBatch = $row->project->dataBatches->sortByDesc('id')->first();
        $allRisks = $row->projectRisks;
        $totalRisk = $allRisks->count();
        $publishedCount = $allRisks->where('status', ProjectRisk::STATUS_PUBLISHED)->count();

        // 2. Cek Aktif (Published)
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
            0 => 'Risk Officer Proyek',
            1 => 'Risk Owner Proyek',
            2 => 'Risk Officer Divisi',
            3 => 'Risk Officer MR',
            4 => 'Risk Owner MR',
        ];
        $currentLabel = $stepLabels[$batchStep] ?? 'Verifikator';
        if ($batchStatus == 5) $currentLabel = 'Dikembalikan ke Officer Proyek';
        if ($batchStatus == 9) $currentLabel = 'Dikembalikan ke Officer Divisi';
        if ($batchStatus == 10) $currentLabel = 'Dikembalikan ke Officer MR';

        // Subtitle Posisi
        $positionHtml = '
        <div class="mt-2 text-dark fw-bold" style="font-size: 11px;">
            Posisi: ' . $currentLabel . '
        </div>';

        // Cek Giliran
        $isMyTurn = false;
        if ($levelId == 6) { // Inputter
            if (in_array($batchStatus, [1, 5])) $isMyTurn = true;
        } else { // Verifikator
            if (($u_step == $batchStep) || ($u_step == 2 && $batchStatus == 9) || ($u_step == 3 && $batchStatus == 10)) {
                $isMyTurn = true;
            }
        }

        $pulseDot = '
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        // --- RENDER ---

        if ($isMyTurn && $this->userHasAccessToProject($user, $row)) {
            $redirectUrl = route('projects.risks.index', ['project' => $row->id]);

            // --- KONDISI KHUSUS INPUTTER (LEVEL 6) ---
            if ($levelId == 6) {

                // 1. KASUS REVISI (Status 5) -> MERAH (DANGER)
                if ($batchStatus == 5) {
                    return '
                    <div class="d-flex flex-column align-items-start">
                        <a href="'.$redirectUrl.'" class="text-decoration-none">
                            <span class="badge bg-danger cursor-pointer border border-danger text-white position-relative"
                                  data-bs-toggle="tooltip"
                                  title="Status: Dikembalikan. Mohon perbaiki data risiko sesuai catatan.">
                                Perlu Revisi
                                '.$pulseDot.'
                            </span>
                        </a>
                        '.$positionHtml.'
                    </div>';
                }

                // 2. KASUS DRAFT (Status 1) -> BIRU (INFO)
                else {
                    return '
                    <div class="d-flex flex-column align-items-start">
                        <a href="'.$redirectUrl.'" class="text-decoration-none">
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
                // --- KONDISI VERIFIKATOR (LEVEL LAIN) ---
                return '
                <div class="d-flex flex-column align-items-start">
                    <a href="'.$redirectUrl.'" class="text-decoration-none">
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
        } else {
            // --- MENUNGGU ---
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
    }

    // private function generateMonitoringStatus($row, $user, $u_step, $levelId)
    // {
    //     $latestMon = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
    //         $q->where('project_periode_list_id', $row->id);
    //     })->orderBy('id', 'desc')->first();

    //     if (!$latestMon) {
    //         return '<span class="badge bg-light text-dark border border-dark">Belum Dimonitor</span>';
    //     }

    //     if ($latestMon->status == 100 || $latestMon->is_approved) {
    //         $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: Disetujui</div>';

    //         return '<div class="d-flex flex-column align-items-start">
    //                     <span class="badge bg-success" data-bs-toggle="tooltip" title="Status: Monitoring Disetujui">Aktif</span>
    //                     '.$positionHtml.'
    //                 </div>';
    //     }

    //     // Label Posisi
    //     $monLabels = [
    //         1 => 'Risk Officer Proyek',
    //         2 => 'Risk Owner Project',
    //         3 => 'Risk Officer Divisi',
    //         4 => 'Risk Officer MR',
    //         5 => 'Risk Owner MR'
    //     ];
    //     $posLabel = $monLabels[$latestMon->status] ?? 'Verifikasi';

    //     $positionHtml = '
    //     <div class="mt-2 text-dark fw-bold" style="font-size: 11px;">
    //         Posisi: ' . $posLabel . '
    //     </div>';

    //     // Logic Giliran
    //     $isMyMonTurn = false;
    //     if ($levelId == 6) {
    //         if ($latestMon->status == 1) $isMyMonTurn = true;
    //     } else {
    //         $target = 0;
    //         if ($levelId == 7) $target = 2;
    //         elseif ($levelId == 1 && !$user->unit->unit_mr) $target = 3;
    //         elseif ($levelId == 1 && $user->unit->unit_mr) $target = 4;
    //         elseif ($levelId == 2 && $user->unit->unit_mr) $target = 5;

    //         if (
    //             $latestMon->status == $target
    //             && !$latestMon->is_approved
    //           ) {
    //             $isMyMonTurn = true;
    //         }
    //     }

    //     $pulseDot = '
    //     <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
    //     <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

    //     // --- RENDER ---

    //     if ($isMyMonTurn && $this->userHasAccessToProject($user, $row)) {
    //         $redirectUrl = route('projects.monitorings.index', ['project' => $row->id]);

    //         if ($levelId == 6) {
    //             // Inputter (Draft Monitoring)
    //             // Hanya status "Draft" (Biru), tulisan revisi dihilangkan
    //             return '
    //             <div class="d-flex flex-column align-items-start">
    //                 <a href="'.$redirectUrl.'" class="text-decoration-none">
    //                     <span class="badge bg-info cursor-pointer border border-info text-white position-relative"
    //                           data-bs-toggle="tooltip"
    //                           title="Status: Draft Monitoring. Mohon lengkapi data.">
    //                         Draft / Input Monitoring
    //                         '.$pulseDot.'
    //                     </span>
    //                 </a>
    //                 '.$positionHtml.'
    //             </div>';
    //         } else {
    //             // Verifikator
    //             return '
    //             <div class="d-flex flex-column align-items-start">
    //                 <a href="'.$redirectUrl.'" class="text-decoration-none">
    //                     <span class="badge bg-warning text-dark border border-warning shadow-sm cursor-pointer position-relative"
    //                           data-bs-toggle="tooltip"
    //                           title="Klik untuk verifikasi monitoring: '.$posLabel.'">
    //                         <i class="bx bx-radar bx-flashing me-1"></i> Verifikasi Mon.
    //                         '.$pulseDot.'
    //                     </span>
    //                 </a>
    //                 '.$positionHtml.'
    //             </div>';
    //         }
    //     } else {
    //         return '
    //         <div class="d-flex flex-column align-items-start">
    //             <div class="d-inline-block position-relative"
    //                 data-bs-toggle="tooltip"
    //                 title="Posisi saat ini: '.$posLabel.'">
    //                 <span class="badge bg-info bg-opacity-10 text-info border border-info">
    //                     <i class="bx bx-radar me-1"></i> Proses Monitoring
    //                 </span>
    //             </div>
    //             '.$positionHtml.'
    //         </div>';
    //     }
    // }

    private function generateMonitoringStatus($row, $user, $u_step, $levelId)
    {
        // 1. Cari Acuan Periode dari Monitoring Terakhir (Global Last ID)
        $referenceMon = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
            $q->where('project_periode_list_id', $row->id);
        })->orderBy('id', 'desc')->first();

        if (!$referenceMon) {
            return '<span class="badge bg-light text-dark border border-dark">Belum Dimonitor</span>';
        }

        // 2. Ambil Raw History pada Bulan Tersebut (Untuk Risiko Aktif Saja)
        $rawMonitorings = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
                $q->where('project_periode_list_id', $row->id)
                  ->where('is_closed', false); // Hanya risiko yang masih Open
            })
            ->where('tahun', $referenceMon->tahun)
            ->where('month', $referenceMon->month)
            ->get();

        // 3. FILTER: Ambil Hanya Data TERBARU per Risiko (PENTING AGAR TIDAK STUCK)
        $currentMonitorings = $rawMonitorings
            ->groupBy('risiko_id')
            ->map(function ($items) {
                return $items->sortByDesc('id')->first();
            });

        // --- LOGIC PENENTUAN STATUS ---
        $countTotal = $currentMonitorings->count();
        $countApproved = $currentMonitorings->where('status', 100)->count();

        // Cek Flag Status
        $hasDraft = $currentMonitorings->where('status', 1)->isNotEmpty();
        // Asumsi Revisi: Status 1 DAN flag is_revision true
        $hasRevision = $currentMonitorings->where('status', 1)->where('is_revision', true)->isNotEmpty();

        $hasVerifROP = $currentMonitorings->where('status', 2)->isNotEmpty();
        $hasVerifROD = $currentMonitorings->where('status', 3)->isNotEmpty();
        $hasVerifROMR = $currentMonitorings->where('status', 4)->isNotEmpty();
        $hasVerifROWMR = $currentMonitorings->where('status', 5)->isNotEmpty();

        $redirectUrl = route('projects.monitorings.index', ['project' => $row->id]);

        $pulseDot = '
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        $isMyMonTurn = false;
        $statusLabel = '';
        $badgeColor = 'bg-info';
        $labelPosisi = 'Verifikasi';
        $tooltipText = '';

        // --- SKENARIO 1: SELESAI ---
        if ($countTotal > 0 && $countTotal === $countApproved) {
            $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: Disetujui</div>';
            return '<div class="d-flex flex-column align-items-start">
                        <span class="badge bg-success" data-bs-toggle="tooltip" title="Status: Monitoring Bulan '.$referenceMon->month.' Disetujui">Selesai</span>
                        '.$positionHtml.'
                    </div>';
        }

        // --- SKENARIO 2: INPUTTER (LEVEL 6) ---
        if ($levelId == 6 && $hasDraft) {
            $isMyMonTurn = true;
            if ($hasRevision) {
                $statusLabel = 'Perlu Revisi';
                $badgeColor = 'bg-danger';
                $labelPosisi = 'Dikembalikan ke Risk Officer Proyek';
                $tooltipText = 'Status: Dikembalikan. Mohon perbaiki data risiko sesuai catatan.';
            } else {
                $statusLabel = 'Draft / Input Monitoring';
                $badgeColor = 'bg-info';
                $labelPosisi = 'Risk Officer Proyek';
                $tooltipText = 'Status: Draft Monitoring. Mohon lengkapi data.';
            }
        }

        // --- SKENARIO 3: VERIFIKATOR ---
        elseif ($levelId == 7 && $hasVerifROP) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark';
            $labelPosisi = 'Risk Owner Proyek';
        }
        elseif ($levelId == 1 && !$user->unit->unit_mr && $hasVerifROD) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark';
            $labelPosisi = 'Risk Officer Divisi';
        }
        elseif ($levelId == 1 && $user->unit->unit_mr && $hasVerifROMR) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark';
            $labelPosisi = 'Risk Officer MR';
        }
        elseif ($levelId == 2 && $user->unit->unit_mr && $hasVerifROWMR) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark';
            $labelPosisi = 'Risk Owner MR';
        }

        // Set Tooltip default untuk Verifikator jika belum di-set di Inputter
        if ($isMyMonTurn && empty($tooltipText)) {
            $tooltipText = 'Klik untuk verifikasi monitoring: ' . $labelPosisi;
        }

        // --- RENDER HTML ---
        // 1. Tentukan Label Posisi (Jika View Only / Menunggu)
        if (!$isMyMonTurn) {
            if ($hasDraft) $labelPosisi = ($hasRevision) ? 'Dikembalikan ke Risk Officer Proyek' : 'Risk Officer Proyek';
            elseif ($hasVerifROP) $labelPosisi = 'Risk Owner Proyek';
            elseif ($hasVerifROD) $labelPosisi = 'Risk Officer Divisi';
            elseif ($hasVerifROMR) $labelPosisi = 'Risk Officer MR';
            elseif ($hasVerifROWMR) $labelPosisi = 'Risk Owner MR';

            $tooltipText = 'Posisi saat ini: ' . $labelPosisi;
        }

        $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: ' . $labelPosisi . '</div>';

        // 2. Jika Giliran User & Punya Akses (Tombol Aktif)
        if ($isMyMonTurn && $this->userHasAccessToProject($user, $row)) {
            return '
                <div class="d-flex flex-column align-items-start">
                    <a href="'.$redirectUrl.'" class="text-decoration-none">
                        <span class="badge '.$badgeColor.' cursor-pointer border shadow-sm position-relative"
                              data-bs-toggle="tooltip"
                              title="'.$tooltipText.'">
                            <i class="bx bx-radar bx-flashing me-1"></i> '.$statusLabel.'
                            '.$pulseDot.'
                        </span>
                    </a>
                    '.$positionHtml.'
                </div>';
        }

        // 3. Jika Menunggu / View Only (Tombol Pasif)
        else {
            return '
                <div class="d-flex flex-column align-items-start">
                    <div class="d-inline-block position-relative"
                        data-bs-toggle="tooltip"
                        title="'.$tooltipText.'">
                        <span class="badge bg-info bg-opacity-10 text-info border border-info">
                            <i class="bx bx-radar me-1"></i> Proses Monitoring
                        </span>
                    </div>
                    '.$positionHtml.'
                </div>';
        }
    }

    private function getUserVerificationStep($level_id, $is_mr = false)
    {
        $u_step = 0;
        $user_verification = "";

        if($level_id == 7) { // ROWP
            $u_step = 1;
            $user_verification = "Risk Owner Proyek";
        }
        else if($level_id == 1) { // RO Divisi
            if($is_mr) { // RO Divisi MR
                $u_step = 3;
                $user_verification = "Risk Officer MR";
            } else {
                $u_step = 2;
                $user_verification = "Risk Officer Divisi";
            }
        }
        else if($level_id == 2 && $is_mr) { // ROW MR
            $u_step = 4;
            $user_verification = "Risk Owner MR";
        }

        return [
            'u_step' => $u_step,
            'user_verification' => $user_verification
        ];
    }

    public function store(Request $request)
    {
        $periode = Periode::where('status', 'active')->first();
        $request->merge([
            'unit_id' => $request->user()->unit_id,
            'periode_id' => $periode->id
        ]);
        return parent::store($request);
    }

    public function show($resource) {
        $projectPeriode = ProjectPeriodeList::with([
                'project.projectDivisi',
                'project.projectSektor',
                'periode',
                'projectRisks.peristiwaRisiko',
                'projectRisks.projectRiskAnalisa.skalaDampakObj',
                'projectRisks.projectRiskAnalisa.skalaDampakResidualObj',
                'projectRisks.projectRiskAnalisa.skalaProbabilitas',
                'projectRisks.projectRiskAnalisa.skalaProbabilitasResidual',
                'projectRisks.projectRiskMonitorings' => function($query) {
                    $query->orderBy('id', 'desc');
                    $query->with('skalaProbabilitas');
                },
            ])
            ->findOrFail($resource);

        $status = request()->query('status');
        $risks = $projectPeriode->projectRisks;

        if ($status === 'open') {
            $risks = $risks->where('is_closed', false);
        } elseif ($status === 'closed') {
            $risks = $risks->where('is_closed', true);
        }

        $sortedRisks = $risks->sortByDesc(function ($risk) {
            return $risk->projectRiskAnalisa?->skala_risiko ?? -1;
        });

        $projectPeriode->setRelation('projectRisks', $sortedRisks);

        $projectPeriode->projectRisks->each(function($projectRisk) {
            $projectRisk->append('currentRiskMapsMonth');
        });

        $tahunMonitorings = $projectPeriode->projectRisks->pluck('projectRiskMonitorings')->flatten()->pluck('tahun')->unique()->toArray();
        $tahunMonitorings[] = $projectPeriode->created_at?->format('Y') ?? date('Y');
        sort($tahunMonitorings);

        $minTahun = (!empty($tahunMonitorings)) ? min($tahunMonitorings) : date('Y');
        $maxTahun = (!empty($tahunMonitorings)) ? max($tahunMonitorings) : date('Y');

        $tahunMonitorings = [];
        for ($tahun = $minTahun; $tahun <= $maxTahun; $tahun++) {
            $tahunMonitorings[] = $tahun;
        }

        $currentRiskMaps = $projectPeriode->projectRisks->pluck('currentRiskMapsMonth');
        $formattedCurrentRiskMaps = [];
        foreach ($projectPeriode->projectRisks as $idx => $projectRisk) {
            $currentValue = $projectRisk->currentRiskMaps['inherent'];
            foreach ($tahunMonitorings as $tahun) {
                for ($month = 1; $month <= 12; $month++) {
                    if ($nextValue = ($projectRisk->currentRiskMapsMonth[$tahun . '-' . $month] ?? null)) {
                        $currentValue = $nextValue;
                    }
                    $currentValue['tahun'] = $tahun;
                    $currentValue['quarter'] = ceil($month / 3);
                    $currentValue['month'] = $month;

                    $formattedCurrentRiskMaps[$projectRisk->id][$tahun][] = $currentValue;
                }
            }
        }

        $editFields = [
            [
                'name' => 'project_code',
                'type' => 'text',
                'label' => 'Kode Project',
                'parameters' => [
                    'project_code',
                    $projectPeriode->project->project_code,
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Kode Project',
                        'readonly' => true,
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'project_name',
                'type' => 'text',
                'label' => 'Nama Project',
                'parameters' => [
                    'project_name',
                    $projectPeriode->project->project_name,
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Nama Project',
                        'readonly' => true,
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'risk_limit',
                'type' => 'text',
                'label' => 'Risk Limit',
                'parameters' => [
                    'risk_limit',
                    ($projectPeriode->project->nk ?? 0) * 0.03,
                    [
                        'class' => 'form-control inputmask-general',
                        'placeholder' => 'Masukkan Risk Limit',
                        'required' => true,
                        'step' => '0.01',
                        'autocomplete' => 'off',
                        'readonly' => true,
                        'disabled' => true,
                    ]
                ],
            ],
            [
                'name' => 'batas_nilai',
                'type' => 'text',
                'label' => 'Batas Nilai',
                'parameters' => [
                    'batas_nilai',
                    $projectPeriode->project->batas_nilai,
                    [
                        'class' => 'form-control inputmask-general',
                        'placeholder' => 'Masukkan Batas Nilai',
                        'required' => true,
                        'step' => '0.01',
                        'autocomplete' => 'off',
                    ]
                ],
            ],
        ];

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        $user = Auth()->user();
        return view('project-periode.show', compact('projectPeriode', 'tahunMonitorings', 'formattedCurrentRiskMaps', 'editFields', 'riskMaps', 'user'));
    }

    public function recalculate($resource)
    {
        $projectPeriode = $this->model::findOrfail($resource);

        $projectPeriode->recalculateAnalisa();
        $projectPeriode->refreshNilai();

        return response()->json([
            'message' => 'Data berhasil dihitung ulang',
        ]);
    }
}
