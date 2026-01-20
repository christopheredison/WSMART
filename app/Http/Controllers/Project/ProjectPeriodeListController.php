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
            'orderable' => false,
            'searchable' => true,
        ],
        'cost_center_parent' => [
            'label' => 'Divisi',
            'data' => 'project.cost_center_parent',
            'name' => 'cost_center_parent',
            'render' => '(data, type, row) => row.project?.divisi?.name || "-"',
            'orderable' => false,
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
            'name' => 'nk',
            'render' => '(data, type, row) => row.nk ? Intl.NumberFormat(\'id-ID\').format(row.nk) : "-"',
            'orderable' => true,
            'searchable' => true,
        ],
        'tanggal_mulai' => [
            'label' => 'Tanggal Mulai',
            'data' => 'tanggal_mulai',
            'name' => 'tanggal_mulai',
            'render' => '(data, type, row) => {
                if (!row.tanggal_mulai) return "-";
                const date = new Date(row.tanggal_mulai);
                const options = { day: "numeric", month: "short", year: "numeric" };
                return date.toLocaleDateString("id-ID", options);
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
        if ($user->unit && !in_array($user->level_id ?? 0, [6, 7])) {
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

            // --- FILTERING HAK AKSES ---
            if (!Gate::check('project_periode_view')) {
                if (in_array($user->level_id ?? 0, [6, 7])) {
                    $query->whereIn('project_periode_lists.project_id', $userProjectIds);
                } else if ($user->unit) {
                    $query->whereIn('project_periode_lists.project_id', $allProjectIds);
                } else {
                    $query->whereIn('project_periode_lists.project_id', $userProjectIds);
                }
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
                if (!$row->tanggal_mulai) return "-";
                return \Carbon\Carbon::parse($row->tanggal_mulai)->translatedFormat('d M Y');
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
            $dataTable->orderColumn('nk', function ($query, $order) {
                $query->orderByRaw("CAST(NULLIF(projects.nk, '') AS NUMERIC) $order NULLS LAST");
            });

            // 3. Sort Tanggal Mulai
            $dataTable->orderColumn('tanggal_mulai', function ($query, $order) {
                $query->orderByRaw("CAST(NULLIF(projects.tanggal_mulai, '') AS DATE) $order NULLS LAST");
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
            $dataTable->filterColumn('nk', function($query, $keyword) {
                $query->whereRaw("CAST(projects.nk AS TEXT) ilike ?", ["%{$keyword}%"]);
            });

            // Filter untuk Nilai Risiko (skala_risiko) - Cast to TEXT for Postgres
            $dataTable->filterColumn('skala_risiko', function($query, $keyword) {
                $query->whereRaw("CAST(project_periode_lists.skala_risiko AS TEXT) ilike ?", ["%{$keyword}%"]);
            });

            // Filter untuk Tanggal Mulai - Cast to TEXT for Postgres
            $dataTable->filterColumn('tanggal_mulai', function($query, $keyword) {
                $query->whereRaw("CAST(projects.tanggal_mulai AS TEXT) ilike ?", ["%{$keyword}%"]);
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

            $dataTable->rawColumns(['status_risiko_html', 'status_monitoring_html']);

            // --- DEFAULT ORDERING (PRIORITAS) ---
            $dataTable->order(function ($query) use ($user, $levelId, $u_step, $is_mr, $userProjectIds, $allProjectIds) {
                if (!request()->has('order')) {
                    $riskActionNeededSql = "FALSE";
                    $latestBatchIdSql = "(SELECT MAX(sub_db.id) FROM data_batches sub_db WHERE sub_db.project_id = project_periode_lists.project_id AND sub_db.type = 2)";

                    if ($levelId == 6) { // Inputter
                        $riskActionNeededSql = "EXISTS (SELECT 1 FROM data_batches db WHERE db.id = $latestBatchIdSql AND db.finish IS FALSE AND (db.status = 5 OR db.status = 1))";
                    } elseif ($u_step > 0) { // Verifikator
                        $rejectConditions = "";
                        if ($u_step == 2) $rejectConditions = "OR db.status = 9";
                        elseif ($u_step == 3) $rejectConditions = "OR db.status = 10";

                        $riskActionNeededSql = "EXISTS (SELECT 1 FROM data_batches db WHERE db.id = $latestBatchIdSql AND db.finish IS FALSE AND (db.step_verification = {$u_step} {$rejectConditions}))";
                    }

                    $monActionNeededSql = "FALSE";
                    $monTargetStatus = 0;
                    if ($levelId == 6) $monTargetStatus = 1;
                    elseif ($levelId == 7) $monTargetStatus = 2;
                    elseif ($levelId == 1 && !$is_mr) $monTargetStatus = 3;
                    elseif ($levelId == 1 && $is_mr) $monTargetStatus = 4;
                    elseif ($levelId == 2 && $is_mr) $monTargetStatus = 5;

                    if ($monTargetStatus > 0) {
                        $monActionNeededSql = "EXISTS (SELECT 1 FROM project_risk_monitorings prm JOIN project_risks pr ON pr.id = prm.risiko_id WHERE pr.project_periode_list_id = project_periode_lists.id AND prm.id = (SELECT MAX(sub_prm.id) FROM project_risk_monitorings sub_prm JOIN project_risks sub_pr ON sub_pr.id = sub_prm.risiko_id WHERE sub_pr.project_periode_list_id = project_periode_lists.id) AND prm.status = {$monTargetStatus} AND prm.is_approved IS FALSE)";
                    }

                    $userIdList = $userProjectIds->isNotEmpty() ? $userProjectIds->join(',') : '0';
                    $allIdList = $allProjectIds->isNotEmpty() ? $allProjectIds->join(',') : '0';

                    $query->orderByRaw("
                        CASE
                            WHEN project_periode_lists.project_id IN ({$userIdList}) AND ($riskActionNeededSql OR $monActionNeededSql) THEN 1
                            WHEN project_periode_lists.project_id IN ({$userIdList}) THEN 2
                            WHEN project_periode_lists.project_id IN ({$allIdList}) THEN 3
                            ELSE 4
                        END ASC
                    ");

                    $query->orderBy('project_periode_lists.updated_at', 'desc');
                }
            });
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
            'status_risiko' => [
                'label' => 'Status Risiko',
                'type' => 'select',
                'parameters' => [
                    'status_risiko',
                    [
                        'draft' => 'Draft / Revisi',
                        'verification' => 'Sedang Verifikasi',
                        'active' => 'Aktif / Final',
                    ],
                    null,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Semua Status Risiko'
                    ]
                ],
                'handler' => function($query, $key, $value) {
                    if (empty($value)) return;
                    $latestBatchSql = "(SELECT MAX(db2.id) FROM data_batches db2 WHERE db2.project_id = project_periode_lists.project_id AND db2.type = 2)";

                    if ($value === 'active') {
                        $query->whereRaw("EXISTS (SELECT 1 FROM data_batches db WHERE db.id = $latestBatchSql AND db.finish IS TRUE)");
                    } elseif ($value === 'draft') {
                        $query->whereRaw("EXISTS (SELECT 1 FROM data_batches db WHERE db.id = $latestBatchSql AND db.finish IS FALSE AND db.status IN (1, 5, 9, 10))");
                    } elseif ($value === 'verification') {
                        $query->whereRaw("EXISTS (SELECT 1 FROM data_batches db WHERE db.id = $latestBatchSql AND db.finish IS FALSE AND db.status IN (2, 3, 4))");
                    }
                }
            ],
            'status_monitoring' => [
                'label' => 'Status Monitoring',
                'type' => 'select',
                'parameters' => [
                    'status_monitoring',
                    [
                        'empty' => 'Belum Dimonitor',
                        'draft' => 'Draft / Revisi',
                        'verification' => 'Sedang Verifikasi',
                        'active' => 'Aktif / Final',
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
                        $query->whereRaw("NOT EXISTS (SELECT 1 FROM project_risk_monitorings prm JOIN project_risks pr ON pr.id = prm.risiko_id WHERE pr.project_periode_list_id = project_periode_lists.id)");
                    } elseif ($value === 'active') {
                        $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND prm.status = 100)");
                    } elseif ($value === 'draft') {
                        $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND prm.status = 1 AND prm.status != 100)");
                    } elseif ($value === 'verification') {
                        $query->whereRaw("EXISTS (SELECT 1 FROM project_risk_monitorings prm WHERE prm.id = $latestMonSql AND prm.status IN (2, 3, 4, 5))");
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
        return $isMyTurn && $user->hasProject($row);
    }

    private function checkMonitoringActionNeeded($row, $user, $u_step, $levelId)
    {
        // Query sederhana untuk kebutuhan flag
        $latestMon = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
            $q->where('project_periode_list_id', $row->id);
        })->orderBy('id', 'desc')->first();

        if (!$latestMon || $latestMon->status == 100) return false;

        $isMyMonTurn = false;
        if ($levelId == 6) {
            if ($latestMon->status == 1) $isMyMonTurn = true;
        } else {
            $target = 0;
            if ($levelId == 7) $target = 2;
            elseif ($levelId == 1 && !$user->unit->unit_mr) $target = 3;
            elseif ($levelId == 1 && $user->unit->unit_mr) $target = 4;
            elseif ($levelId == 2 && $user->unit->unit_mr) $target = 5;

            if ($latestMon->status == $target && !$latestMon->is_approved) {
                $isMyMonTurn = true;
            }
        }
        return $isMyMonTurn && $user->hasProject($row);
    }

    private function generateRiskStatus($row, $user, $u_step, $levelId)
    {
        // 1. Cek Data Kosong
        if ($row->project_risks_count == 0) {
            return '<span class="badge bg-light text-dark border border-dark">Tidak Aktif</span>';
        }

        // Ambil data batch terakhir
        $lastBatch = $row->project->dataBatches->sortByDesc('id')->first();

        // Kita ambil status dari relasi projectRisks yang sudah di-load di index()
        $allRisks = $row->projectRisks;
        $totalRisk = $allRisks->count();
        $publishedCount = $allRisks->where('status', ProjectRisk::STATUS_PUBLISHED)->count();

        // 2. Cek Aktif (Published)
        if (($lastBatch && $lastBatch->finish) || ($totalRisk > 0 && $totalRisk === $publishedCount)) {
            return '<span class="badge bg-success">Published</span>';
        }

        // 3. Logic Proses (Eskalasi)
        $batchStep = $lastBatch ? $lastBatch->step_verification : 0;
        $batchStatus = $lastBatch ? $lastBatch->status : 1;

        // Label Mapping
        $stepLabels = [
            0 => 'Input Draft',
            1 => 'Risk Owner Project',
            2 => 'Risk Officer Divisi',
            3 => 'Risk Officer MR',
            4 => 'Risk Owner MR',
        ];

        $currentLabel = $stepLabels[$batchStep] ?? 'Verifikator';
        if ($batchStatus == 9) $currentLabel = 'Dikembalikan Officer MR (Ke Divisi)';
        if ($batchStatus == 10) $currentLabel = 'Dikembalikan Owner MR (Ke Officer MR)';

        // Cek Apakah Giliran Saya?
        $isMyTurn = false;

        if ($levelId == 6) { // Inputter
            if (in_array($batchStatus, [1, 5])) $isMyTurn = true;
        } else { // Verifikator
            if (($u_step == $batchStep) ||
                ($u_step == 2 && $batchStatus == 9) ||
                ($u_step == 3 && $batchStatus == 10)) {
                $isMyTurn = true;
            }
        }

        // Variabel Dot Pulse (Merah)
        $pulseDot = '
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        // --- LOGIKA TAMPILAN ---

        // KONDISI 1: Giliran Saya (Action Needed)
        if ($isMyTurn && $user->hasProject($row)) {
            $redirectUrl = route('projects.risks.index', ['project' => $row->id]);

            // A. Khusus Inputter (Level 6) -> Tampilan Solid Biru (Draft/Revisi)
            if ($levelId == 6) {
                return '
                <a href="'.$redirectUrl.'" class="text-decoration-none">
                    <span class="badge bg-info cursor-pointer border border-info text-white position-relative"
                        data-bs-toggle="tooltip"
                        title="Status: Draft/Revisi. Mohon lengkapi atau perbaiki data risiko.">
                        Draft / Perlu Revisi
                        '.$pulseDot.'
                    </span>
                </a>';
            }

            // B. Verifikator (Level Lain) -> Tampilan Solid Kuning (Verifikasi)
            else {
                return '
                <a href="'.$redirectUrl.'" class="text-decoration-none">
                    <span class="badge bg-warning text-dark border border-warning shadow-sm cursor-pointer position-relative"
                        data-bs-toggle="tooltip" title="Klik untuk verifikasi: '.$currentLabel.'">
                        <i class="bx bx-error-circle bx-flashing me-1"></i> Perlu Verifikasi
                        '.$pulseDot.'
                    </span>
                </a>';
            }
        }

        // KONDISI 2: Bukan Giliran Saya (Waiting) -> Tampilan Soft (Transparan)
        else {
            return '
            <div class="d-inline-block position-relative" data-bs-toggle="tooltip" title="Posisi saat ini: '.$currentLabel.'">
                <span class="badge bg-info bg-opacity-10 text-info border border-info">
                    <i class="bx bx-time-five me-1"></i> Proses Validasi
                </span>
            </div>';
        }
    }

    private function generateMonitoringStatus($row, $user, $u_step, $levelId)
    {
        // Query monitoring terakhir
        $latestMon = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
            $q->where('project_periode_list_id', $row->id);
        })
        ->orderBy('id', 'desc')
        ->first();

        if (!$latestMon) {
            return '<span class="badge bg-light text-dark border border-dark">Belum Dimonitor</span>';
        }

        if ($latestMon->status == 100) {
            return '<span class="badge bg-success">Aktif</span>';
        }

        // Label Mapping
        $monLabels = [
            1 => 'Drafting',
            2 => 'Risk Owner Project',
            3 => 'Risk Officer Divisi',
            4 => 'Risk Officer MR',
            5 => 'Risk Owner MR'
        ];
        $posLabel = $monLabels[$latestMon->status] ?? 'Verifikasi';

        // Cek Giliran Saya
        $isMyMonTurn = false;

        if ($levelId == 6) {
            if ($latestMon->status == 1) $isMyMonTurn = true;
        } else {
            $target = 0;
            if ($levelId == 7) $target = 2;
            elseif ($levelId == 1 && !$user->unit->unit_mr) $target = 3;
            elseif ($levelId == 1 && $user->unit->unit_mr) $target = 4;
            elseif ($levelId == 2 && $user->unit->unit_mr) $target = 5;

            if ($latestMon->status == $target && !$latestMon->is_approved) {
                $isMyMonTurn = true;
            }
        }

        // Variabel Dot Pulse (Merah)
        $pulseDot = '
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        // --- LOGIKA TAMPILAN MONITORING ---

        // KONDISI 1: Giliran Saya (Action Needed)
        if ($isMyMonTurn && $user->hasProject($row)) {
            $redirectUrl = route('projects.monitorings.index', ['project' => $row->id]);

            // A. Khusus Inputter (Level 6) -> Tampilan Solid Biru (Draft/Revisi)
            if ($levelId == 6) {
                return '
                <a href="'.$redirectUrl.'" class="text-decoration-none">
                    <span class="badge bg-info cursor-pointer border border-info text-white position-relative"
                        data-bs-toggle="tooltip"
                        title="Status: Draft Monitoring. Mohon lengkapi data.">
                        Draft / Perlu Revisi
                        '.$pulseDot.'
                    </span>
                </a>';
            }

            // B. Verifikator (Level Lain) -> Tampilan Solid Kuning (Verifikasi)
            else {
                return '
                <a href="'.$redirectUrl.'" class="text-decoration-none">
                    <span class="badge bg-warning text-dark border border-warning shadow-sm cursor-pointer position-relative"
                        data-bs-toggle="tooltip" title="Klik untuk verifikasi monitoring: '.$posLabel.'">
                        <i class="bx bx-radar bx-flashing me-1"></i> Verifikasi Mon.
                        '.$pulseDot.'
                    </span>
                </a>';
            }
        }

        // KONDISI 2: Bukan Giliran Saya (Waiting) -> Tampilan Soft (Transparan)
        else {
            return '
            <div class="d-inline-block position-relative" data-bs-toggle="tooltip" title="Posisi saat ini: '.$posLabel.'">
                <span class="badge bg-info bg-opacity-10 text-info border border-info">
                    <i class="bx bx-radar me-1"></i> Proses Monitoring
                </span>
            </div>';
        }
    }

    private function getUserVerificationStep($level_id, $is_mr = false)
    {
        $u_step = 0;
        $user_verification = "";

        if($level_id == 7) { // ROWP
            $u_step = 1;
            $user_verification = "Risk Owner Project";
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
            $risks = $risks->where('is_closed', 0);
        } elseif ($status === 'closed') {
            $risks = $risks->where('is_closed', 1);
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
                    ($projectPeriode->project->meta['omset'] ?? 0) * 0.03,
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
}
