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
            'class' => 'mw-10r',
        ],
        'project_id' => [
            'label' => 'Proyek',
            'data' => 'project.project_name',
            'name' => 'projects.project_name',
            'render' => '(data, type, row) => row.project?.project_name || "-"',
            'orderable' => true,
            'searchable' => true,
            'class' => 'fw-bold mw-15r',
        ],
        'ok' => [
            'label' => 'Nilai OK Total',
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
            'class' => 'text-nowrap',
        ],
        'tanggal_selesai' => [
            'label' => 'Tanggal Selesai',
            'data' => 'tanggal_selesai_display',
            'name' => 'projects.meta->bast1',
            'render' => '(data, type, row) => {
                const bast1 = row.project?.meta?.bast1;
                if (!bast1 || bast1 === "-" || bast1 === "") return "-";

                // Coba parsing tanggal
                const date = new Date(bast1 + "T00:00:00");
                if(isNaN(date.getTime())) return bast1; // Jika bukan format tanggal valid, tampilkan as-is

                return date.toLocaleDateString("id-ID", { day:"numeric", month:"short", year:"numeric" });
            }',
            'orderable' => true,
            'searchable' => true,
            'class' => 'text-nowrap',
        ],
        'status_proyek' => [
            'label' => 'Status Proyek',
            'data' => 'status_proyek',
            'name' => 'projects.masa_pelaksanaan_end',
            'orderable' => true,
            'searchable' => false,
            'class' => 'text-center',
        ],
        'kategori_mth_high' => [
            'label' => 'Moderate to High & High',
            'data' => 'jumlah_risiko_khusus',
            'name' => 'jumlah_risiko_khusus',
            'orderable' => true,
            'searchable' => false,
            'class' => 'text-center',
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
        $currentYear = (int) date('Y');
        $tahun = request()->input('filters.tahun', request()->query('tahun', $currentYear));
        $month = request()->input('filters.month', request()->query('month', date('n')));

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
            'filters' => array_merge(request()->input('filters', []), [
                'tahun' => $tahun,
                'month' => $month
            ])
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
        $this->callbackQuery = function ($query) use ($userProjectIds, $unitProjectIds, $allProjectIds, $user, $u_step, $levelId, $is_mr, $tahun, $month) {
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
                'projects.masa_pelaksanaan_end',
                'units.name as divisi_name',
            ]);

            // Subquery untuk Jumlah Risiko (agar bisa disort)
            $query->selectSub(function ($q) {
                $q->from('project_risks')
                  ->whereColumn('project_periode_lists.id', 'project_risks.project_periode_list_id')
                  ->whereNull('deleted_at')
                  ->selectRaw('count(*)');
            }, 'project_risks_count');

            $query->selectSub(function ($q) {
                $q->from('project_risks')
                  ->whereColumn('project_periode_lists.id', 'project_risks.project_periode_list_id')
                  ->whereNull('deleted_at')
                  ->whereIn('level_risiko', ['Moderate to High', 'High'])
                  ->selectRaw('count(*)');
            }, 'jumlah_risiko_khusus');

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

                // Old Code
                // if ($monTargetStatus > 0) {
                //     // Subquery ID monitoring terakhir (Global Reference)
                //     $latestMonIdSql = "
                //         SELECT MAX(sub_prm.id)
                //         FROM project_risk_monitorings sub_prm
                //         JOIN project_risks sub_pr ON sub_pr.id = sub_prm.risiko_id
                //         WHERE sub_pr.project_periode_list_id = project_periode_lists.id
                //     ";

                //     // Subquery untuk Referensi Tahun & Bulan
                //     $refYearSql = "(SELECT ref_prm.tahun FROM project_risk_monitorings ref_prm WHERE ref_prm.id = ($latestMonIdSql))";
                //     $refMonthSql = "(SELECT ref_prm.month FROM project_risk_monitorings ref_prm WHERE ref_prm.id = ($latestMonIdSql))";

                //     $monActionNeededSql = "EXISTS (
                //         SELECT 1
                //         FROM project_risk_monitorings prm
                //         JOIN project_risks pr ON pr.id = prm.risiko_id
                //         WHERE pr.project_periode_list_id = project_periode_lists.id
                //           AND pr.is_closed IS FALSE

                //           -- Pastikan Waktu (Bulan/Tahun) Sesuai Referensi Terakhir
                //           AND prm.tahun = $refYearSql
                //           AND prm.month = $refMonthSql

                //           -- --- PERBAIKAN PENTING: FILTER HANYA ID TERAKHIR PER RISIKO ---
                //           AND prm.id = (
                //               SELECT MAX(sub_prm.id)
                //               FROM project_risk_monitorings sub_prm
                //               WHERE sub_prm.risiko_id = pr.id
                //               AND sub_prm.tahun = prm.tahun
                //               AND sub_prm.month = prm.month
                //           )
                //           -- -----------------------------------------------------------

                //           AND prm.status = {$monTargetStatus}
                //           AND prm.is_approved IS FALSE
                //     )";
                // }

                if ($monTargetStatus > 0) {
                    $monActionNeededSql = "EXISTS (
                        SELECT 1
                        FROM project_risk_monitorings prm
                        JOIN project_risks pr ON pr.id = prm.risiko_id
                        WHERE pr.project_periode_list_id = project_periode_lists.id
                          AND pr.is_closed IS FALSE
                          AND prm.tahun = '{$tahun}'
                          AND prm.month = '{$month}'
                          AND prm.id = (
                              SELECT MAX(sub_prm.id)
                              FROM project_risk_monitorings sub_prm
                              WHERE sub_prm.risiko_id = pr.id
                              AND sub_prm.tahun = '{$tahun}'
                              AND sub_prm.month = '{$month}'
                          )
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

            // Status Proyek
            $dataTable->addColumn('status_proyek', function ($row) {
                if (empty($row->masa_pelaksanaan_end)) {
                    return '<span class="badge bg-danger">Tidak Aktif</span>';
                }

                $endDate = \Carbon\Carbon::parse($row->masa_pelaksanaan_end)->startOfDay();
                $today = \Carbon\Carbon::today();

                if ($endDate->greaterThanOrEqualTo($today)) {
                    return '<span class="badge bg-success">Aktif</span>';
                } else {
                    return '<span class="badge bg-danger">Tidak Aktif</span>';
                }
            });

            $dataTable->orderColumn('status_proyek', function ($query, $order) {
                $query->orderBy('projects.masa_pelaksanaan_end', $order);
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

            // 3.1 Sort Tanggal Selesai
            $dataTable->orderColumn('projects.meta->bast1', function ($query, $order) {
                // Di-cast ke date agar pengurutan waktu akurat
                $query->orderByRaw("(projects.meta->>'bast1')::date {$order} NULLS LAST");
            });

            // 4. Sort Nilai Risiko (Pastikan merujuk ke tabel utama agar tidak ambigu)
            $dataTable->orderColumn('skala_risiko', function ($query, $order) {
                $query->orderByRaw("CAST(NULLIF(CAST(project_periode_lists.skala_risiko AS TEXT), '') AS NUMERIC) $order NULLS LAST");
            });

            // 5. Sort Jumlah Risiko (Subquery alias)
            $dataTable->orderColumn('project_risks_count', function ($query, $order) {
                $query->orderBy('project_risks_count', $order);
            });

            $dataTable->orderColumn('jumlah_risiko_khusus', function ($query, $order) {
                $query->orderBy('jumlah_risiko_khusus', $order);
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

            // Filter untuk Tanggal Selesai (Diambil dari meta->bast1)
            $dataTable->filterColumn('projects.meta->bast1', function ($query, $keyword) {
                $query->whereRaw("projects.meta->>'bast1' ILIKE ?", ["%{$keyword}%"]);
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

            $dataTable->rawColumns(['status_risiko_html', 'status_monitoring_html', 'status_proyek']);

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
            $monitoringRouteBase = route('projects.monitorings.index', ['project' => ':id']);
            $script = "let elT=document.querySelector('#table-filter [name=tahun]'),elM=document.querySelector('#table-filter [name=month]'),t=elT?elT.value:new Date().getFullYear(),m=elM?elM.value:(new Date().getMonth()+1),q=Math.ceil(m/3);window.location.href='{$monitoringRouteBase}?tahun='+t+'&quarter='+q+'&month='+m;";

            $this->tableActions[] = [
                'btn_icon' => true,
                'label' => '<span class="bx bx-radar" title="Monitoring"></span>',
                'action' => 'script',
                'script' => $script,
                'active_state' => '(data, type, row) => row.has_monitoring',
                'title' => 'Monitoring'
            ];
            $this->tableLegend[] = ['icon' => '<span class="bx bx-radar"></span>', 'label' => 'Monitoring'];
        }

        if (Gate::check('project_led_list')) {
            $this->tableActions[] = [
                'btn_icon' => true,
                'label' => '<span class="bx bx-dock-bottom" title="Loss Event"></span>',
                'action' => 'link',
                'url' => urldecode(route('project-led.index-by-project', ['projectId' => ':project_id'])),

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
                    text: "Proses ini akan menghitung ulang Analisa, Peta Risiko Terkini, dan Efektivitas secara keseluruhan.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "Ya, Hitung ulang",
                    cancelButtonText: "Batal",
                    reverseButtons: true,
                    showCloseButton: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Sedang Memproses...',
                            html: 'Mohon tunggu, jangan tutup halaman ini.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "$recalculateRoute".replace(":id", $(this).data('id')),
                            type: "GET",
                            success: function(response) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Berhasil!",
                                    text: response.message || "Data berhasil dihitung ulang.",
                                    confirmButtonText: "OK"
                                }).then(() => {
                                    $('.ajax-datatable').DataTable().ajax.reload(null, false);
                                });
                            },
                            error: function(xhr, status, error) {
                                let errorMsg = "Terjadi kesalahan pada server saat menghitung ulang data.";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    icon: "error",
                                    title: "Gagal!",
                                    text: errorMsg,
                                    confirmButtonText: "Tutup"
                                });
                            }
                        });
                    }
                });
            JS,
        ];

        $this->extraViewData['showKamusRisikoButton'] = true;

        $this->extraScripts[] = <<<JS
            <script>
            $(document).ready(function() {
                // Saat dropdown tahun atau bulan diubah, update URL dan reload DataTable
                $('select[name="filters[tahun]"], select[name="filters[month]"]').on('change', function() {
                    const t = $('select[name="filters[tahun]"]').val();
                    const m = $('select[name="filters[month]"]').val();

                    if (!t || !m) return;

                    const url = new URL(window.location.href);
                    url.searchParams.set('tahun', t);
                    url.searchParams.set('month', m);
                    window.history.pushState({path: url.href}, '', url.href);

                    // Reload Datatable
                    if (typeof $('.ajax-datatable').DataTable === 'function') {
                        $('.ajax-datatable').DataTable().ajax.reload();
                    } else {
                        location.reload();
                    }
                });
            });
            </script>
        JS;

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

        // Array Tahun (Current s/d Current+5)
        $currentYear = (int) date('Y');
        $optionTahuns = [];
        for ($i = $currentYear; $i <= $currentYear + 5; $i++) {
            $optionTahuns[$i] = $i;
        }

        // Array Bulan (1-12)
        $optionMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Ambil value dari request
        $tahun = request()->input('filters.tahun', request()->query('tahun', $currentYear));
        $month = request()->input('filters.month', request()->query('month', date('n')));

        $this->availableFilters = [
            'divisi' => [
                'label' => 'Filter Divisi',
                'type' => 'select',
                'classWrapper' => 'col-md-2',
                'parameters' => ['divisi', $divisiOptions, null, ['class' => 'form-select select2', 'placeholder' => 'Semua Divisi']],
                'handler' => function($query, $key, $value) {
                    if (!empty($value)) {
                        $query->where('projects.cost_center_parent', $value);
                    }
                }
            ],
            'status_proyek' => [
                'label' => 'Status Proyek',
                'type' => 'select',
                'classWrapper' => 'col-md-2',
                'parameters' => [
                    'status_proyek',
                    [
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                    ],
                    null,
                    [
                        'class' => 'form-select select2',
                        'placeholder' => 'Semua Status Proyek'
                    ]
                ],
                'handler' => function($query, $key, $value) {
                    if (empty($value)) return;

                    if ($value === 'aktif') {
                        $query->whereDate('projects.masa_pelaksanaan_end', '>=', \Carbon\Carbon::today());
                    } elseif ($value === 'tidak_aktif') {
                        $query->where(function($q) {
                            $q->whereDate('projects.masa_pelaksanaan_end', '<', \Carbon\Carbon::today())
                              ->orWhereNull('projects.masa_pelaksanaan_end');
                        });
                    }
                }
            ],
            'status_risiko' => [
                'label' => 'Status Risiko',
                'type' => 'select',
                'classWrapper' => 'col-md-2',
                'parameters' => [
                    'status_risiko',
                    [
                        'draft' => 'Draft / Input Risiko', // Status 1
                        'revisi' => 'Perlu Revisi', // Status 5, 9, 10 (Merah)
                        'verification' => 'Proses Verifikasi',     // Status 2, 3, 4
                        'active' => 'Published',             // Finish = true
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

                    // Pengecekan absolut apakah sebuah Project sudah SELESAI
                    $isSelesaiSql = "(
                        EXISTS (SELECT 1 FROM data_batches db WHERE db.id = $latestBatchSql AND db.finish IS TRUE)
                        OR (
                            (SELECT COUNT(*) FROM project_risks pr WHERE pr.project_periode_list_id = project_periode_lists.id AND pr.deleted_at IS NULL) > 0
                            AND
                            (SELECT COUNT(*) FROM project_risks pr WHERE pr.project_periode_list_id = project_periode_lists.id AND pr.status != 6 AND pr.deleted_at IS NULL) = 0
                        )
                    )";

                    if ($value === 'active') {
                        // Hanya Selesai
                        $query->whereRaw($isSelesaiSql);
                    } elseif ($value === 'draft') {
                        // Draft TAPI Bukan Selesai
                        $query->whereRaw("NOT $isSelesaiSql")
                              ->whereRaw("(
                                  NOT EXISTS (SELECT 1 FROM data_batches db WHERE db.project_id = project_periode_lists.project_id AND db.type = 2)
                                  OR EXISTS (SELECT 1 FROM data_batches db WHERE db.id = $latestBatchSql AND db.finish IS FALSE AND db.status IN (0, 1))
                              )")
                              ->whereRaw("(SELECT COUNT(*) FROM project_risks pr WHERE pr.project_periode_list_id = project_periode_lists.id AND pr.deleted_at IS NULL) > 0");
                    } elseif ($value === 'revisi') {
                        // Revisi TAPI Bukan Selesai
                        $query->whereRaw("NOT $isSelesaiSql")
                              ->whereRaw("EXISTS (
                                  SELECT 1 FROM data_batches db
                                  WHERE db.id = $latestBatchSql
                                  AND db.finish IS FALSE
                                  AND db.status IN (5, 9, 10)
                              )");
                    } elseif ($value === 'verification') {
                        // Verifikasi TAPI Bukan Selesai
                        $query->whereRaw("NOT $isSelesaiSql")
                              ->whereRaw("EXISTS (
                                  SELECT 1 FROM data_batches db
                                  WHERE db.id = $latestBatchSql
                                  AND db.finish IS FALSE
                                  AND db.status IN (2, 3, 4, 7, 8)
                              )");
                    }
                }
            ],
            'status_monitoring' => [
                'label' => 'Status Monitoring',
                'type' => 'select',
                'classWrapper' => 'col-md-2',
                'parameters' => [
                    'status_monitoring',
                    [
                        'empty' => 'Belum Dimonitor',
                        'draft' => 'Draft / Input Monitoring',
                        'verification' => 'Proses Verifikasi',
                        'active' => 'Selesai',
                    ],
                    null,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Semua Status Monitoring'
                    ]
                ],
                'handler' => function($query, $key, $value) use ($tahun, $month) {
                    if (empty($value)) return;

                    // Query dasar untuk memfilter monitoring pada project, tahun, dan bulan terkait
                    // Hanya melihat risiko yang masih OPEN (is_closed = false) dan belum dihapus
                    $baseJoinAndCondition = "
                        FROM project_risk_monitorings prm
                        JOIN project_risks pr ON pr.id = prm.risiko_id
                        WHERE pr.project_periode_list_id = project_periode_lists.id
                          AND pr.is_closed = false
                          AND pr.deleted_at IS NULL
                          AND prm.tahun = '{$tahun}'
                          AND prm.month = '{$month}'
                    ";

                    // Filter untuk mengambil data monitoring TERAKHIR per masing-masing risiko
                    $latestPerRiskCondition = "
                        AND prm.id = (
                            SELECT MAX(sub.id)
                            FROM project_risk_monitorings sub
                            WHERE sub.risiko_id = prm.risiko_id
                              AND sub.tahun = '{$tahun}'
                              AND sub.month = '{$month}'
                        )
                    ";

                    if ($value === 'empty') {
                        // Belum dimonitor: TIDAK ADA data monitoring sama sekali untuk bulan/tahun ini
                        $query->whereRaw("NOT EXISTS (SELECT 1 $baseJoinAndCondition)");
                    } elseif ($value === 'draft') {
                        // Draft: Ada MINIMAL 1 risiko yang latest monitoring-nya berstatus Draft (1)
                        $query->whereRaw("EXISTS (SELECT 1 $baseJoinAndCondition $latestPerRiskCondition AND prm.status = 1)");
                    } elseif ($value === 'verification') {
                        // Proses Verifikasi: Ada MINIMAL 1 risiko yang latest monitoring-nya berstatus Verifikasi (2, 3, 4, 5)
                        $query->whereRaw("EXISTS (SELECT 1 $baseJoinAndCondition $latestPerRiskCondition AND prm.status IN (2, 3, 4, 5))");
                    } elseif ($value === 'active') {
                        // Selesai: ADA monitoring, DAN TIDAK ADA SATUPUN monitoring (terakhir per risiko) yang belum disetujui (status bukan 100)
                        $query->whereRaw("EXISTS (SELECT 1 $baseJoinAndCondition)")
                              ->whereRaw("NOT EXISTS (SELECT 1 $baseJoinAndCondition $latestPerRiskCondition AND prm.status != 100)");
                    }
                }
            ],
            // 'status_monitoring' => [
            //     'label' => 'Status Monitoring',
            //     'type' => 'select',
            //     'classWrapper' => 'col-md-2',
            //     'parameters' => [
            //         'status_monitoring',
            //         [
            //             'empty' => 'Belum Dimonitor',
            //             'draft' => 'Draft / Input Monitoring', // Status 1
            //             'verification' => 'Proses Verifikasi',     // Status 2, 3, 4, 5
            //             'active' => 'Selesai',           // Status 100 atau is_approved
            //         ],
            //         null,
            //         [
            //             'class' => 'form-select',
            //             'placeholder' => 'Semua Status Monitoring'
            //         ]
            //     ],
            //     // Old Code
            //     // 'handler' => function($query, $key, $value) {
            //     //     if (empty($value)) return;
            //     //     $latestMonSql = "(SELECT MAX(prm2.id) FROM project_risk_monitorings prm2 JOIN project_risks pr2 ON pr2.id = prm2.risiko_id WHERE pr2.project_periode_list_id = project_periode_lists.id)";

            //     //     if ($value === 'empty') {
            //     //         // Tidak ada data monitoring
            //     //         $query->whereRaw("NOT EXISTS (SELECT 1 FROM project_risk_monitorings prm JOIN project_risks pr ON pr.id = prm.risiko_id WHERE pr.project_periode_list_id = project_periode_lists.id)");
            //     //     } elseif ($value === 'active') {
            //     //         // Status 100 ATAU is_approved = true
            //     //         $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND (prm.status = 100 OR prm.is_approved IS TRUE))");
            //     //     } elseif ($value === 'draft') {
            //     //         // Status 1 (Drafting) dan belum approved
            //     //         $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND prm.status = 1 AND prm.is_approved IS FALSE)");
            //     //     } elseif ($value === 'verification') {
            //     //         // Status 2, 3, 4, 5 dan belum approved
            //     //         $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND prm.status IN (2, 3, 4, 5) AND prm.is_approved IS FALSE)");
            //     //     }
            //     // }
            //     'handler' => function($query, $key, $value) use ($tahun, $month) {
            //         if (empty($value)) return;

            //         // $latestMonSql = "(SELECT MAX(prm2.id) FROM project_risk_monitorings prm2 JOIN project_risks pr2 ON pr2.id = prm2.risiko_id WHERE pr2.project_periode_list_id = project_periode_lists.id AND prm2.tahun = '{$tahun}' AND prm2.month = '{$month}')";

            //         // if ($value === 'empty') {
            //         //     $query->whereRaw("NOT EXISTS (SELECT 1 FROM project_risk_monitorings prm JOIN project_risks pr ON pr.id = prm.risiko_id WHERE pr.project_periode_list_id = project_periode_lists.id AND prm.tahun = '{$tahun}' AND prm.month = '{$month}')");
            //         // } elseif ($value === 'active') {
            //         //     $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND (prm.status = 100 OR prm.is_approved IS TRUE))");
            //         // } elseif ($value === 'draft') {
            //         //     $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND prm.status = 1 AND prm.is_approved IS FALSE)");
            //         // } elseif ($value === 'verification') {
            //         //     $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND prm.status IN (2, 3, 4, 5) AND prm.is_approved IS FALSE)");
            //         // }

            //         // Query dasar untuk memfilter monitoring pada project, tahun, dan bulan terkait
            //         // Hanya melihat risiko yang masih OPEN (is_closed = false) dan belum dihapus
            //         $baseJoinAndCondition = "
            //             FROM project_risk_monitorings prm
            //             JOIN project_risks pr ON pr.id = prm.risiko_id
            //             WHERE pr.project_periode_list_id = project_periode_lists.id
            //               AND pr.is_closed = false
            //               AND pr.deleted_at IS NULL
            //               AND prm.tahun = '{$tahun}'
            //               AND prm.month = '{$month}'
            //         ";

            //         // Filter untuk mengambil data monitoring TERAKHIR per masing-masing risiko
            //         $latestPerRiskCondition = "
            //             AND prm.id = (
            //                 SELECT MAX(sub.id)
            //                 FROM project_risk_monitorings sub
            //                 WHERE sub.risiko_id = prm.risiko_id
            //                   AND sub.tahun = '{$tahun}'
            //                   AND sub.month = '{$month}'
            //             )
            //         ";

            //         if ($value === 'empty') {
            //             // Belum dimonitor: TIDAK ADA data monitoring sama sekali untuk bulan/tahun ini
            //             $query->whereRaw("NOT EXISTS (SELECT 1 $baseJoinAndCondition)");
            //         } elseif ($value === 'draft') {
            //             // Draft: Ada MINIMAL 1 risiko yang latest monitoring-nya berstatus Draft (1)
            //             $query->whereRaw("EXISTS (SELECT 1 $baseJoinAndCondition $latestPerRiskCondition AND prm.status = 1 AND prm.is_approved IS FALSE)");
            //         } elseif ($value === 'verification') {
            //             // Proses Verifikasi: Ada MINIMAL 1 risiko yang latest monitoring-nya berstatus Verifikasi (2, 3, 4, 5)
            //             $query->whereRaw("EXISTS (SELECT 1 $baseJoinAndCondition $latestPerRiskCondition AND prm.status IN (2, 3, 4, 5) AND prm.is_approved IS FALSE)");
            //         } elseif ($value === 'active') {
            //             // Selesai: ADA monitoring, DAN TIDAK ADA SATUPUN monitoring (terakhir per risiko) yang belum di-approve (status != 100)
            //             $query->whereRaw("EXISTS (SELECT 1 $baseJoinAndCondition)")
            //                   ->whereRaw("NOT EXISTS (SELECT 1 $baseJoinAndCondition $latestPerRiskCondition AND (prm.status != 100 AND prm.is_approved IS FALSE))");
            //         }
            //     }
            // ],
            // FILTER TAHUN BARU
            'tahun' => [
                'label' => 'Tahun Monitoring',
                'type' => 'select',
                'classWrapper' => 'col-md-2',
                'parameters' => [
                    'tahun',
                    $optionTahuns,
                    $tahun,
                    ['class' => 'form-select select2']
                ],
                'handler' => function ($query, $key, $value) {
                    // Handled outside (di dalam subquery monitoring)
                },
            ],
            // FILTER BULAN BARU
            'month' => [
                'label' => 'Bulan Monitoring',
                'type' => 'select',
                'classWrapper' => 'col-md-2',
                'parameters' => [
                    'month',
                    $optionMonths,
                    $month,
                    ['class' => 'form-select select2']
                ],
                'handler' => function ($query, $key, $value) {
                    // Handled outside (di dalam subquery monitoring)
                },
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

    // Old Code
    // private function checkMonitoringActionNeeded($row, $user, $u_step, $levelId)
    // {
    //     // 1. Ambil Acuan Waktu (Bulan/Tahun) dari data paling akhir di project ini
    //     $referenceMon = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
    //         $q->where('project_periode_list_id', $row->id);
    //     })->orderBy('id', 'desc')->first();

    //     if (!$referenceMon) return false;

    //     // 2. Tentukan Target Status
    //     $targetStatus = 0;
    //     if ($levelId == 6) $targetStatus = 1;      // Inputter
    //     elseif ($levelId == 7) $targetStatus = 2;  // ROP
    //     elseif ($levelId == 1 && !$user->unit->unit_mr) $targetStatus = 3; // ROD
    //     elseif ($levelId == 1 && $user->unit->unit_mr) $targetStatus = 4;  // RO MR
    //     elseif ($levelId == 2 && $user->unit->unit_mr) $targetStatus = 5;  // ROW MR

    //     if ($targetStatus === 0) return false;

    //     // 3. Cek Action Needed
    //     // Logic: Cari Risiko Aktif -> Ambil Monitoring Terakhirnya di Bulan Itu -> Cek Statusnya
    //     $hasAction = ProjectRisk::where('project_periode_list_id', $row->id)
    //         ->where('is_closed', false) // Risiko Masih Open
    //         ->whereHas('projectRiskMonitorings', function($q) use ($referenceMon, $targetStatus) {
    //             $q->where('tahun', $referenceMon->tahun)
    //               ->where('month', $referenceMon->month)

    //               // --- PERBAIKAN PENTING DI SINI ---
    //               // Pastikan hanya mengecek Monitoring dengan ID Terakhir (Latest) untuk risiko ini
    //               ->whereRaw('id = (
    //                   SELECT MAX(sub.id)
    //                   FROM project_risk_monitorings as sub
    //                   WHERE sub.risiko_id = project_risk_monitorings.risiko_id
    //                   AND sub.tahun = ?
    //                   AND sub.month = ?
    //               )', [$referenceMon->tahun, $referenceMon->month])
    //               // ---------------------------------

    //               ->where('status', $targetStatus)
    //               ->where('is_approved', false);
    //         })
    //         ->exists();

    //     return $hasAction && $this->userHasAccessToProject($user, $row);
    // }

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
    //     // 1. Cari Acuan Periode dari Monitoring Terakhir (Global Last ID)
    //     $referenceMon = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
    //         $q->where('project_periode_list_id', $row->id);
    //     })->orderBy('id', 'desc')->first();

    //     if (!$referenceMon) {
    //         return '<span class="badge bg-light text-dark border border-dark">Belum Dimonitor</span>';
    //     }

    //     // 2. Ambil Raw History pada Bulan Tersebut (Untuk Risiko Aktif Saja)
    //     $rawMonitorings = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
    //             $q->where('project_periode_list_id', $row->id)
    //               ->where('is_closed', false); // Hanya risiko yang masih Open
    //         })
    //         ->where('tahun', $referenceMon->tahun)
    //         ->where('month', $referenceMon->month)
    //         ->get();

    //     // 3. FILTER: Ambil Hanya Data TERBARU per Risiko (PENTING AGAR TIDAK STUCK)
    //     $currentMonitorings = $rawMonitorings
    //         ->groupBy('risiko_id')
    //         ->map(function ($items) {
    //             return $items->sortByDesc('id')->first();
    //         });

    //     // --- LOGIC PENENTUAN STATUS ---
    //     $countTotal = $currentMonitorings->count();
    //     $countApproved = $currentMonitorings->where('status', 100)->count();

    //     // Cek Flag Status
    //     $hasDraft = $currentMonitorings->where('status', 1)->isNotEmpty();
    //     // Asumsi Revisi: Status 1 DAN flag is_revision true
    //     $hasRevision = $currentMonitorings->where('status', 1)->where('is_revision', true)->isNotEmpty();

    //     $hasVerifROP = $currentMonitorings->where('status', 2)->isNotEmpty();
    //     $hasVerifROD = $currentMonitorings->where('status', 3)->isNotEmpty();
    //     $hasVerifROMR = $currentMonitorings->where('status', 4)->isNotEmpty();
    //     $hasVerifROWMR = $currentMonitorings->where('status', 5)->isNotEmpty();

    //     $redirectUrl = route('projects.monitorings.index', ['project' => $row->id]);

    //     $pulseDot = '
    //     <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
    //     <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

    //     $isMyMonTurn = false;
    //     $statusLabel = '';
    //     $badgeColor = 'bg-info';
    //     $labelPosisi = 'Verifikasi';
    //     $tooltipText = '';

    //     // --- SKENARIO 1: SELESAI ---
    //     if ($countTotal > 0 && $countTotal === $countApproved) {
    //         // Mapping nama bulan agar lebih mudah dibaca
    //         $namaBulan = [
    //             1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    //             5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    //             9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    //         ];
    //         $bulanStr = $namaBulan[(int)$referenceMon->month] ?? $referenceMon->month;

    //         $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: Selesai</div>';
    //         return '<div class="d-flex flex-column align-items-start">
    //                     <span class="badge bg-success" data-bs-toggle="tooltip" title="Status: Monitoring Bulan '.$bulanStr.' Disetujui">Selesai ('.$bulanStr.')</span>
    //                     '.$positionHtml.'
    //                 </div>';
    //     }

    //     // --- SKENARIO 2: INPUTTER (LEVEL 6) ---
    //     if ($levelId == 6 && $hasDraft) {
    //         $isMyMonTurn = true;
    //         if ($hasRevision) {
    //             $statusLabel = 'Perlu Revisi';
    //             $badgeColor = 'bg-danger border border-danger text-white';
    //             $labelPosisi = 'Dikembalikan ke Risk Officer Proyek';
    //             $tooltipText = 'Status: Dikembalikan. Mohon perbaiki data risiko sesuai catatan.';
    //         } else {
    //             $statusLabel = 'Draft / Input Monitoring';
    //             $badgeColor = 'bg-info border border-info text-white';
    //             $labelPosisi = 'Risk Officer Proyek';
    //             $tooltipText = 'Status: Draft Monitoring. Mohon lengkapi data.';
    //         }
    //     }

    //     // --- SKENARIO 3: VERIFIKATOR ---
    //     elseif ($levelId == 7 && $hasVerifROP) {
    //         $isMyMonTurn = true;
    //         $statusLabel = 'Perlu Verifikasi';
    //         $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
    //         $labelPosisi = 'Risk Owner Proyek';
    //     }
    //     elseif ($levelId == 1 && !$user->unit->unit_mr && $hasVerifROD) {
    //         $isMyMonTurn = true;
    //         $statusLabel = 'Perlu Verifikasi';
    //         $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
    //         $labelPosisi = 'Risk Officer Divisi';
    //     }
    //     elseif ($levelId == 1 && $user->unit->unit_mr && $hasVerifROMR) {
    //         $isMyMonTurn = true;
    //         $statusLabel = 'Perlu Verifikasi';
    //         $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
    //         $labelPosisi = 'Risk Officer MR';
    //     }
    //     elseif ($levelId == 2 && $user->unit->unit_mr && $hasVerifROWMR) {
    //         $isMyMonTurn = true;
    //         $statusLabel = 'Perlu Verifikasi';
    //         $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
    //         $labelPosisi = 'Risk Owner MR';
    //     }

    //     // Set Tooltip default untuk Verifikator jika belum di-set di Inputter
    //     if ($isMyMonTurn && empty($tooltipText)) {
    //         $tooltipText = 'Klik untuk verifikasi monitoring: ' . $labelPosisi;
    //     }

    //     // --- RENDER HTML ---
    //     // 1. Tentukan Label Posisi (Jika View Only / Menunggu)
    //     if (!$isMyMonTurn) {
    //         if ($hasDraft) $labelPosisi = ($hasRevision) ? 'Dikembalikan ke Risk Officer Proyek' : 'Risk Officer Proyek';
    //         elseif ($hasVerifROP) $labelPosisi = 'Risk Owner Proyek';
    //         elseif ($hasVerifROD) $labelPosisi = 'Risk Officer Divisi';
    //         elseif ($hasVerifROMR) $labelPosisi = 'Risk Officer MR';
    //         elseif ($hasVerifROWMR) $labelPosisi = 'Risk Owner MR';

    //         $tooltipText = 'Posisi saat ini: ' . $labelPosisi;
    //     }

    //     $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: ' . $labelPosisi . '</div>';

    //     // 2. Jika Giliran User & Punya Akses (Tombol Aktif)
    //     if ($isMyMonTurn && $this->userHasAccessToProject($user, $row)) {
    //         return '
    //             <div class="d-flex flex-column align-items-start">
    //                 <a href="'.$redirectUrl.'" class="text-decoration-none">
    //                     <span class="badge '.$badgeColor.' cursor-pointer position-relative"
    //                           data-bs-toggle="tooltip"
    //                           title="'.$tooltipText.'">
    //                         <i class="bx bx-radar bx-flashing me-1"></i> '.$statusLabel.'
    //                         '.$pulseDot.'
    //                     </span>
    //                 </a>
    //                 '.$positionHtml.'
    //             </div>';
    //     }

    //     // 3. Jika Menunggu / View Only (Tombol Pasif)
    //     else {
    //         return '
    //             <div class="d-flex flex-column align-items-start">
    //                 <div class="d-inline-block position-relative"
    //                     data-bs-toggle="tooltip"
    //                     title="'.$tooltipText.'">
    //                     <span class="badge bg-info bg-opacity-10 text-info border border-info">
    //                         <i class="bx bx-radar me-1"></i> Proses Monitoring
    //                     </span>
    //                 </div>
    //                 '.$positionHtml.'
    //             </div>';
    //     }
    // }

    private function checkMonitoringActionNeeded($row, $user, $u_step, $levelId)
    {
        // Ambil filter tahun & bulan dari request
        $tahun = request()->input('filters.tahun', request()->query('tahun', date('Y')));
        $month = request()->input('filters.month', request()->query('month', date('n')));

        $targetStatus = 0;
        if ($levelId == 6) $targetStatus = 1;      // Inputter
        elseif ($levelId == 7) $targetStatus = 2;  // ROP
        elseif ($levelId == 1 && !$user->unit->unit_mr) $targetStatus = 3; // ROD
        elseif ($levelId == 1 && $user->unit->unit_mr) $targetStatus = 4;  // RO MR
        elseif ($levelId == 2 && $user->unit->unit_mr) $targetStatus = 5;  // ROW MR

        if ($targetStatus === 0) return false;

        $hasAction = ProjectRisk::where('project_periode_list_id', $row->id)
            ->where('is_closed', false)
            ->whereHas('projectRiskMonitorings', function($q) use ($tahun, $month, $targetStatus) {
                $q->where('tahun', $tahun)
                  ->where('month', $month)
                  ->whereRaw('id = (
                      SELECT MAX(sub.id)
                      FROM project_risk_monitorings as sub
                      WHERE sub.risiko_id = project_risk_monitorings.risiko_id
                      AND sub.tahun = ?
                      AND sub.month = ?
                  )', [$tahun, $month])
                  ->where('status', $targetStatus)
                  ->where('is_approved', false);
            })
            ->exists();

        return $hasAction && $this->userHasAccessToProject($user, $row);
    }

    private function generateMonitoringStatus($row, $user, $u_step, $levelId)
    {
        // Ambil filter tahun & bulan dari request
        $tahun = request()->input('filters.tahun', request()->query('tahun', date('Y')));
        $month = request()->input('filters.month', request()->query('month', date('n')));

        // Ambil Raw History pada Bulan & Tahun Tersebut
        $rawMonitorings = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
                $q->where('project_periode_list_id', $row->id)
                  ->where('is_closed', false);
            })
            ->where('tahun', $tahun)
            ->where('month', $month)
            ->get();

        if ($rawMonitorings->isEmpty()) {
            return '<span class="badge bg-light text-dark border border-dark">Belum Dimonitor</span>';
        }

        // Ambil Hanya Data TERBARU per Risiko di Bulan Tersebut
        $currentMonitorings = $rawMonitorings
            ->groupBy('risiko_id')
            ->map(function ($items) {
                return $items->sortByDesc('id')->first();
            });

        $countTotal = $currentMonitorings->count();
        $countApproved = $currentMonitorings->where('status', 100)->count();

        $hasDraft = $currentMonitorings->where('status', 1)->isNotEmpty();
        $hasRevision = $currentMonitorings->where('status', 1)->where('is_revision', true)->isNotEmpty();

        $hasVerifROP = $currentMonitorings->where('status', 2)->isNotEmpty();
        $hasVerifROD = $currentMonitorings->where('status', 3)->isNotEmpty();
        $hasVerifROMR = $currentMonitorings->where('status', 4)->isNotEmpty();
        $hasVerifROWMR = $currentMonitorings->where('status', 5)->isNotEmpty();

        $redirectUrl = route('projects.monitorings.index', [
            'project' => $row->id,
            'tahun' => $tahun,
            'month' => $month
        ]);

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
            $namaBulan = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];
            $bulanStr = $namaBulan[(int)$month] ?? $month;

            $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: Selesai</div>';
            return '<div class="d-flex flex-column align-items-start">
                        <span class="badge bg-success" data-bs-toggle="tooltip" title="Status: Monitoring Bulan '.$bulanStr.' '.$tahun.' Disetujui">Selesai ('.$bulanStr.' '.$tahun.')</span>
                        '.$positionHtml.'
                    </div>';
        }

        // --- SKENARIO 2: INPUTTER (LEVEL 6) ---
        if ($levelId == 6 && $hasDraft) {
            $isMyMonTurn = true;
            if ($hasRevision) {
                $statusLabel = 'Perlu Revisi';
                $badgeColor = 'bg-danger border border-danger text-white';
                $labelPosisi = 'Dikembalikan ke Risk Officer Proyek';
                $tooltipText = 'Status: Dikembalikan. Mohon perbaiki data risiko sesuai catatan.';
            } else {
                $statusLabel = 'Draft / Input Monitoring';
                $badgeColor = 'bg-info border border-info text-white';
                $labelPosisi = 'Risk Officer Proyek';
                $tooltipText = 'Status: Draft Monitoring. Mohon lengkapi data.';
            }
        }
        // --- SKENARIO 3: VERIFIKATOR ---
        elseif ($levelId == 7 && $hasVerifROP) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
            $labelPosisi = 'Risk Owner Proyek';
        }
        elseif ($levelId == 1 && !$user->unit->unit_mr && $hasVerifROD) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
            $labelPosisi = 'Risk Officer Divisi';
        }
        elseif ($levelId == 1 && $user->unit->unit_mr && $hasVerifROMR) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
            $labelPosisi = 'Risk Officer MR';
        }
        elseif ($levelId == 2 && $user->unit->unit_mr && $hasVerifROWMR) {
            $isMyMonTurn = true;
            $statusLabel = 'Perlu Verifikasi';
            $badgeColor = 'bg-warning text-dark border border-warning shadow-sm';
            $labelPosisi = 'Risk Owner MR';
        }

        if ($isMyMonTurn && empty($tooltipText)) {
            $tooltipText = 'Klik untuk verifikasi monitoring: ' . $labelPosisi;
        }

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

        // 2. Jika Giliran User & Punya Akses
        if ($isMyMonTurn && $this->userHasAccessToProject($user, $row)) {
            return '
                <div class="d-flex flex-column align-items-start">
                    <a href="'.$redirectUrl.'" class="text-decoration-none">
                        <span class="badge '.$badgeColor.' cursor-pointer position-relative"
                              data-bs-toggle="tooltip"
                              title="'.$tooltipText.'">
                            <i class="bx bx-radar bx-flashing me-1"></i> '.$statusLabel.'
                            '.$pulseDot.'
                        </span>
                    </a>
                    '.$positionHtml.'
                </div>';
        }
        // 3. View Only
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
                    $query->orderBy('tahun', 'desc');
                    $query->orderBy('month', 'desc');
                    $query->orderBy('id', 'desc');
                    $query->with('skalaProbabilitas');
                },
                'projectRisks.projectRiskMonitorings.skalaDampakObj',
                'projectRisks.projectRiskMonitorings.skalaProbabilitas',
                'projectRisks.penyebabRisikoProjects.perlakuanPenyebabRisiko.perlakuanPenyebabMonitorings',
                'projectRisks.perlakuanDampakRisikos.perlakuanDampakMonitorings',
            ])
            ->findOrFail($resource);

        $status = request()->query('status');

        $allRisks = $projectPeriode->projectRisks;
        $risks = $allRisks;

        if ($status === 'open') {
            $risks = $risks->where('is_closed', false);
        } elseif ($status === 'closed') {
            $risks = $risks->where('is_closed', true);
        }

        $sortedRisks = $risks->sortByDesc(function ($risk) {
            return $risk->projectRiskAnalisa?->skala_risiko ?? -1;
        });

        $projectPeriode->setRelation('projectRisks', $sortedRisks);

        $tahunMonitorings = $projectPeriode->projectRisks->pluck('projectRiskMonitorings')->flatten()->pluck('tahun')->unique()->filter()->toArray();
        $tahunMonitorings[] = $projectPeriode->created_at?->format('Y') ?? date('Y');
        sort($tahunMonitorings);

        $minTahun = (!empty($tahunMonitorings)) ? min($tahunMonitorings) : date('Y');
        $maxTahun = (!empty($tahunMonitorings)) ? max($tahunMonitorings) : date('Y');

        $tahunMonitorings = [];
        for ($tahun = $minTahun; $tahun <= $maxTahun; $tahun++) {
            $tahunMonitorings[] = $tahun;
        }

        $formattedCurrentRiskMaps = [];
        $riskRealisasiData = [];

        foreach ($projectPeriode->projectRisks as $projectRisk) {
            $currentState = [
                'skala_dampak' => $projectRisk->projectRiskAnalisa?->skala_dampak,
                'skala_probabilitas' => $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->tingkat,
                'nilai_dampak' => $projectRisk->projectRiskAnalisa?->nilai_dampak,
                'skala_dampak_desc' => $projectRisk->projectRiskAnalisa?->skalaDampakObj?->deskripsi,
                'nilai_probabilitas' => $projectRisk->projectRiskAnalisa?->nilai_probabilitas,
                'skala_probabilitas_desc' => $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->skala,
                'nilai_risiko' => $projectRisk->projectRiskAnalisa?->skala_risiko,
                'level_risiko' => $projectRisk->projectRiskAnalisa?->level_risiko,
            ];

            // Ganti keyBy menjadi groupBy lalu ambil first()
            $monitorings = $projectRisk->projectRiskMonitorings->groupBy(function($item) {
                return $item->tahun . '-' . $item->month;
            })->map(function($group) {
                return $group->first();
            });

            foreach ($tahunMonitorings as $tahun) {
                for ($month = 1; $month <= 12; $month++) {
                    $key = $tahun . '-' . $month;

                    if (isset($monitorings[$key])) {
                        $m = $monitorings[$key];
                        $currentState = [
                            'skala_dampak' => $m->skala_dampak,
                            'skala_probabilitas' => $m->skalaProbabilitas?->tingkat,
                            'nilai_dampak' => $m->nilai_dampak,
                            'skala_dampak_desc' => $m->skalaDampakObj?->deskripsi,
                            'nilai_probabilitas' => $m->nilai_probabilitas,
                            'skala_probabilitas_desc' => $m->skalaProbabilitas?->skala,
                            'nilai_risiko' => $m->skala_risiko,
                            'level_risiko' => $m->level_risiko,
                        ];
                    }

                    $mapData = $currentState;
                    $mapData['tahun'] = $tahun;
                    $mapData['month'] = $month;
                    $mapData['quarter'] = ceil($month / 3);
                    $formattedCurrentRiskMaps[$projectRisk->id][$tahun][] = $mapData;

                    $riskRealisasiData[$projectRisk->id][$key] = $currentState;
                }
            }
        }

        $project = $projectPeriode->project;
        $meta = $project->meta ?? [];
        $riskLimit = ($project->nk ?? 0) * 0.03; // 3% dari Nilai Kontrak (NK)

        // Cari LSP (Laba Setelah Pajak)
        $hasilUsaha = \App\Models\ProjectHasilUsaha::where('profit_center', $meta['profit_center'] ?? null)
                        ->orderBy('period', 'desc')
                        ->first();
        $lspValue = $hasilUsaha ? $hasilUsaha->lsp_review : 0;

        // Hitung Rencana dan Realisasi Biaya
        $rencanaBiayaTotal = 0;
        $realisasiBiayaTotal = 0;

        foreach ($allRisks as $risk) {
            foreach ($risk->penyebabRisikoProjects as $penyebab) {
                foreach ($penyebab->perlakuanPenyebabRisiko as $perlakuan) {
                    $rencanaBiayaTotal += $perlakuan->biaya_perlakuan_risiko ?? 0;
                    $lastMon = $perlakuan->perlakuanPenyebabMonitorings->sortByDesc('id')->first();
                    $realisasiBiayaTotal += $lastMon?->realisasi_biaya_perlakuan_risiko ?? 0;
                }
            }
            foreach ($risk->perlakuanDampakRisikos as $perlakuanDampak) {
                $rencanaBiayaTotal += $perlakuanDampak->biaya_perlakuan_risiko ?? 0;
                $lastMon = $perlakuanDampak->perlakuanDampakMonitorings->sortByDesc('id')->first();
                $realisasiBiayaTotal += $lastMon?->realisasi_biaya_perlakuan_risiko ?? 0;
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
                    ['class' => 'form-control', 'readonly' => true, 'required' => true]
                ],
            ],
            [
                'name' => 'project_name',
                'type' => 'text',
                'label' => 'Nama Project',
                'parameters' => [
                    'project_name',
                    $projectPeriode->project->project_name,
                    ['class' => 'form-control', 'readonly' => true, 'required' => true]
                ],
            ],
            [
                'name' => 'biaya_perlakuan_risiko_rkp',
                'type' => 'text',
                'label' => 'Biaya Perlakuan Risiko Sesuai RKP',
                'parameters' => [
                    'biaya_perlakuan_risiko_rkp',
                    number_format((float)($project->biaya_perlakuan_risiko_rkp ?? 0), 2, ',', ''),
                    [
                        'class' => 'form-control inputmask-general',
                        'placeholder' => 'Masukkan Nilai RKP',
                    ]
                ],
            ],
            [
                'name' => 'risk_limit',
                'type' => 'text',
                'label' => 'Risk Limit',
                'parameters' => [
                    'risk_limit',
                    number_format((float)$riskLimit, 2, ',', ''),
                    [
                        'class' => 'form-control inputmask-general',
                        'readonly' => true,
                        'disabled' => true,
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
        $canEditProject = $this->userHasAccessToProject($user, $projectPeriode);

        return view('project-periode.show', compact('projectPeriode', 'tahunMonitorings', 'formattedCurrentRiskMaps', 'riskRealisasiData', 'editFields', 'riskMaps', 'user', 'project', 'meta', 'riskLimit', 'lspValue', 'rencanaBiayaTotal', 'realisasiBiayaTotal', 'canEditProject'));
    }

    public function recalculate($resource)
    {
        $projectPeriode = $this->model::findOrfail($resource);

        $projectPeriode->recalculateAllRisks();
        // $projectPeriode->recalculateAnalisa();
        // $projectPeriode->refreshNilai();

        return response()->json([
            'message' => 'Data berhasil dihitung ulang',
        ]);
    }
}
