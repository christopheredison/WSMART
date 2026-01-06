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
        'const_center_parent' => [
            'label' => 'Divisi',
            'data' => 'project.cost_center_parent',
            'name' => 'cost_center_parent',
            'render' => '(data, type, row) => row.project?.divisi?.name || "-"',
            'orderable' => false,
            'searchable' => false,
        ],
        'project_id' => [
            'label' => 'Proyek',
            'data' => 'project.project_name',
            'name' => 'projects.project_name',
            'render' => '(data, type, row) => row.project?.project_name || "-"',
            'orderable' => false,
            'searchable' => true,
            'class' => 'fw-bold',
        ],
        'ok' => [
            'label' => 'Nilai OK',
            'data' => 'project.meta.omset',
            'name' => 'omset',
            'render' => '(data, type, row) => row.project?.meta?.omset ? Intl.NumberFormat(\'id-ID\').format(row.project.meta.omset) : "-"',
            'orderable' => false,
            'searchable' => true,
        ],
        'tanggal_mulai' => [
            'label' => 'Tanggal Mulai',
            'data' => 'project.meta.tanggal_mulai',
            'name' => 'tanggal_mulai',
            'render' => '(data, type, row) => {
                if (!row.project?.meta?.tanggal_mulai) return "-";
                const date = new Date(row.project.meta.tanggal_mulai);
                const options = { day: "numeric", month: "short", year: "numeric" };
                return date.toLocaleDateString("id-ID", options);
            }',
            'orderable' => false,
            'searchable' => true,
            'class' => 'mw-10r',
        ],
        'skala_risiko' => [
            'label' => 'Nilai Risiko',
            'data' => 'skala_risiko',
            'render' => <<<JS
                (data) => data ? Intl.NumberFormat('id-ID').format(data) : '-'
                JS,
            'orderable' => false,
            'searchable' => true,
        ],
        'project_risks_count' => [
            'label' => 'Jumlah Risiko',
            'data' => 'project_risks_count',
            'orderable' => false,
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
            'withCount' => ['projectRisks'],
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

        // --- CALLBACK QUERY ---
        $this->callbackQuery = function ($query) use ($userProjectIds, $unitProjectIds, $allProjectIds, $user, $u_step, $levelId) {
            // Join tabel projects
            $query->join('projects', 'project_periode_lists.project_id', '=', 'projects.id');

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

            // --- SORTING ---
            if (!request()->has('order')) {
                $query->reorder();

                // LOGIKA SORTING PRIORITAS:
                // Cek apakah project ini sedang menunggu tindakan user ini?
                // Logic: Ada data_batch aktif yang step_verification == u_step
                // Atau kasus reject MR (step 2 & status 9)

                $actionNeededSql = "0";

                if ($levelId == 6) { // Inputter
                    // Inputter butuh aksi jika Batch Revisi (5) atau Rejected MR (9) atau Draft (1)
                    $actionNeededSql = "EXISTS (
                        SELECT 1 FROM data_batches db
                        WHERE db.project_id = project_periode_lists.project_id
                        AND db.type = 2
                        AND db.finish = 0
                        AND (db.status = 5 OR db.status = 9 OR db.status = 1)
                        ORDER BY db.batch DESC LIMIT 1
                    )";
                } elseif ($u_step > 0) { // Verifikator
                    // Verifikator butuh aksi jika Step Batch == U_Step
                    $rejectMrCondition = ($u_step == 2) ? "OR db.status = 9" : "";

                    $actionNeededSql = "EXISTS (
                        SELECT 1 FROM data_batches db
                        WHERE db.project_id = project_periode_lists.project_id
                        AND db.type = 2
                        AND db.finish = 0
                        AND (db.step_verification = {$u_step} {$rejectMrCondition})
                        ORDER BY db.batch DESC LIMIT 1
                    )";
                }

                $userIdList = $userProjectIds->isNotEmpty() ? $userProjectIds->join(',') : '0';
                $allIdList = $allProjectIds->isNotEmpty() ? $allProjectIds->join(',') : '0';

                // URUTAN PRIORITAS:
                // 1. Project Saya & Butuh Action
                // 2. Project Saya (Normal)
                // 3. Project Unit/Divisi
                // 4. Sisanya
                $query->orderByRaw("
                    CASE
                        WHEN project_periode_lists.project_id IN ({$userIdList}) AND $actionNeededSql THEN 1
                        WHEN project_periode_lists.project_id IN ({$userIdList}) THEN 2
                        WHEN project_periode_lists.project_id IN ({$allIdList}) THEN 3
                        ELSE 4
                    END ASC
                ");

                $query->orderBy('project_periode_lists.updated_at', 'desc');
            }
        };

        // // --- CALLBACK QUERY ---
        // $this->callbackQuery = function ($query) use ($userProjectIds, $unitProjectIds, $allProjectIds, $user) {
        //     // Join tabel projects (Wajib untuk sorting/filtering)
        //     $query->join('projects', 'project_periode_lists.project_id', '=', 'projects.id');

        //     // PERBAIKAN SORTING:
        //     // Logika custom order (Prioritas Project & Updated At) HANYA dijalankan
        //     // jika User TIDAK sedang melakukan sorting lewat kolom tabel.
        //     if (!request()->has('order')) {

        //         $query->reorder(); // Reset default order model

        //         if ($allProjectIds->isNotEmpty()) {
        //             $userIdList = $userProjectIds->join(',');
        //             $allIdList = $allProjectIds->join(',');

        //             // Logika Sorting Custom (User Projects > Unit Projects > Others)
        //             if ($userProjectIds->isNotEmpty() && $unitProjectIds->isNotEmpty()) {
        //                 $query->orderByRaw("
        //                     CASE
        //                         WHEN project_periode_lists.project_id IN ({$userIdList}) THEN 1
        //                         WHEN project_periode_lists.project_id IN ({$allIdList}) THEN 2
        //                         ELSE 3
        //                     END ASC
        //                 ");
        //             } elseif ($allProjectIds->isNotEmpty()) {
        //                 $query->orderByRaw("
        //                     CASE
        //                         WHEN project_periode_lists.project_id IN ({$allIdList}) THEN 1
        //                         ELSE 2
        //                     END ASC
        //                 ");
        //             }
        //         }

        //         // Default Secondary Sort
        //         $query->orderBy('project_periode_lists.updated_at', 'desc');
        //     }

        //     // Filter permission tetap dijalankan (tidak di dalam if)
        //     if (!Gate::check('project_periode_view')) {
        //         if (in_array($user->level_id ?? 0, [6, 7])) {
        //             $query->whereIn('project_periode_lists.project_id', $userProjectIds);
        //         } else if ($user->unit) {
        //             $query->whereIn('project_periode_lists.project_id', $allProjectIds);
        //         } else {
        //             $query->whereIn('project_periode_lists.project_id', $userProjectIds);
        //         }
        //     }
        // };

        // --- DATATABLE CALLBACK ---
        $this->datatableCallback = function ($dataTable) use ($user, $u_step, $levelId) {
            // Render Status Risiko
            $dataTable->addColumn('status_risiko_html', function ($row) use ($user, $u_step, $levelId) {
                return $this->generateRiskStatus($row, $user, $u_step, $levelId);
            });

            // Render Status Monitoring
            $dataTable->addColumn('status_monitoring_html', function ($row) use ($user, $u_step, $levelId) {
                return $this->generateMonitoringStatus($row, $user, $u_step, $levelId);
            });

            // 1. Sorting & Filter untuk Kode Project (JSON)
            // Gunakan alias 'profit_center' sesuai 'name' di tableColumns
            $dataTable->orderColumn('profit_center', function ($query, $order) {
                $query->orderByRaw("projects.meta->>'profit_center' $order");
            });
            $dataTable->filterColumn('profit_center', function($query, $keyword) {
                $query->whereRaw("projects.meta->>'profit_center' ILIKE ?", ["%{$keyword}%"]);
            });

            // 2. Sorting & Filter untuk Nama Project
            // Key ini bisa pakai 'project.project_name' (data) atau 'projects.project_name' (name)
            $dataTable->orderColumn('project.project_name', function ($query, $order) {
                $query->orderBy('projects.project_name', $order);
            });
            $dataTable->filterColumn('project.project_name', function($query, $keyword) {
                $query->whereRaw("projects.project_name ILIKE ?", ["%{$keyword}%"]);
            });

            // 3. Sorting & Filter untuk Nilai OK / Omset
            // Gunakan alias 'omset'
            $dataTable->orderColumn('omset', function ($query, $order) {
                $query->orderByRaw("CAST(COALESCE(projects.meta->>'omset', '0') AS NUMERIC) $order");
            });
            $dataTable->filterColumn('omset', function($query, $keyword) {
                $query->whereRaw("projects.meta->>'omset' ILIKE ?", ["%{$keyword}%"]);
            });

            // 4. Sorting & Filter untuk Tanggal Mulai
            // Gunakan alias 'tanggal_mulai'
            $dataTable->orderColumn('tanggal_mulai', function ($query, $order) {
                $query->orderByRaw("CAST(NULLIF(projects.meta->>'tanggal_mulai', '') AS DATE) $order NULLS LAST");
            });
            $dataTable->filterColumn('tanggal_mulai', function($query, $keyword) {
                $query->whereRaw("projects.meta->>'tanggal_mulai' ILIKE ?", ["%{$keyword}%"]);
            });

            // --- FILTERING ---
            // Pencarian Kode Project (JSON)
            $dataTable->filterColumn('project.meta.profit_center', function($query, $keyword) {
                $query->whereRaw("projects.meta->>'profit_center' ILIKE ?", ["%{$keyword}%"]);
            });

            // Kolom Button Actions
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

    private function generateRiskStatus($row, $user, $u_step, $levelId)
    {
        // 1. Cek Data Kosong
        if ($row->project_risks_count == 0) {
            return '<span class="badge bg-light text-muted border">Tidak Aktif</span>';
        }

        // Ambil data batch terakhir dari eager loading
        $lastBatch = $row->project->dataBatches->sortByDesc('id')->first();

        // 2. Cek Aktif (Published)
        if ($lastBatch && $lastBatch->finish) {
            return '<span class="badge bg-success">Aktif</span>';
        }

        // 3. Logic Proses (Eskalasi)
        $batchStep = $lastBatch ? $lastBatch->step_verification : 0;
        $batchStatus = $lastBatch ? $lastBatch->status : 1;

        // Label Mapping (Sesuaikan dengan workflow)
        $stepLabels = [
            0 => 'Input Draft',
            1 => 'Risk Owner Project',
            2 => 'Risk Officer Divisi',
            3 => 'Risk Officer MR',
            4 => 'Risk Owner MR',
        ];

        $currentLabel = $stepLabels[$batchStep] ?? 'Verifikator';
        if ($batchStatus == 9) $currentLabel = 'Dikembalikan MR (Ke Divisi)'; // Kasus khusus

        // Cek Apakah Giliran Saya?
        $isMyTurn = false;

        if ($levelId == 6) { // Inputter
            // Giliran inputter jika status Draft (1) atau Revisi (5/9)
            if (in_array($batchStatus, [1, 5, 9])) $isMyTurn = true;
        } else {
            // Giliran verifikator jika step cocok ATAU kasus reject MR (user di step 2 status 9)
            if (($u_step == $batchStep) || ($u_step == 2 && $batchStatus == 9)) {
                $isMyTurn = true;
            }
        }

        // TAMPILAN
        if ($isMyTurn && $user->hasProject($row)) {
            // Highlight (Kuning + Animasi)
            return '
            <div class="d-inline-block position-relative" data-bs-toggle="tooltip" title="Posisi: '.$currentLabel.'">
                <span class="badge bg-warning text-dark border border-warning shadow-sm">
                    <i class="bx bx-error-circle bx-flashing me-1"></i> Perlu Verifikasi
                </span>
            </div>';
        } else {
            // Standard (Biru - Sedang Proses di orang lain)
            return '
            <div class="d-inline-block position-relative" data-bs-toggle="tooltip" title="Menunggu: '.$currentLabel.'">
                <span class="badge bg-info bg-opacity-10 text-info border border-info">
                    <i class="bx bx-time-five me-1"></i> Proses Validasi
                </span>
            </div>';
        }
    }

    private function generateMonitoringStatus($row, $user, $u_step, $levelId)
    {
        // Query manual monitoring terakhir (karena di eager load index sudah dilimit)
        // Kita gunakan query sederhana agar performa tetap terjaga
        $latestMon = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($row) {
            $q->where('project_periode_list_id', $row->id);
        })
        ->orderBy('id', 'desc')
        ->first();

        if (!$latestMon) {
            return '<span class="badge bg-light text-muted border">Belum Dimonitor</span>';
        }

        // Logic Status Monitoring
        // 100 = Published (Aktif)
        if ($latestMon->status == 100) {
            return '<span class="badge bg-success">Aktif</span>';
        }

        // Label Mapping Monitoring
        // 1=Draft, 2=ROWP, 3=RO Divisi, 4=RO MR, 5=ROW MR
        $monLabels = [
            1 => 'Drafting',
            2 => 'Risk Owner Project',
            3 => 'Risk Officer Divisi',
            4 => 'Risk Officer MR',
            5 => 'Risk Owner MR'
        ];
        $posLabel = $monLabels[$latestMon->status] ?? 'Verifikasi';

        // Cek Giliran Saya (Monitoring)
        $isMyMonTurn = false;

        if ($levelId == 6) {
            // Inputter: Status 1 (Draft/Revisi)
            if ($latestMon->status == 1) $isMyMonTurn = true;
        } else {
            // Verifikator: Mapping Target
            $target = 0;
            if ($levelId == 7) $target = 2;
            elseif ($levelId == 1 && !$user->unit->unit_mr) $target = 3;
            elseif ($levelId == 1 && $user->unit->unit_mr) $target = 4;
            elseif ($levelId == 2 && $user->unit->unit_mr) $target = 5;

            // Giliran jika status == target DAN belum approved
            if ($latestMon->status == $target && !$latestMon->is_approved) {
                $isMyMonTurn = true;
            }
        }

        // TAMPILAN
        if ($isMyMonTurn && $user->hasProject($row)) {
            $msg = ($levelId == 6) ? "Input Monitoring" : "Verifikasi Mon.";
            return '
            <div class="d-inline-block position-relative" data-bs-toggle="tooltip" title="Posisi: '.$posLabel.'">
                <span class="badge bg-warning text-dark border border-warning shadow-sm">
                    <i class="bx bx-radar bx-flashing me-1"></i> '.$msg.'
                </span>
            </div>';
        } else {
            return '
            <div class="d-inline-block position-relative" data-bs-toggle="tooltip" title="Posisi: '.$posLabel.'">
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
                $user_verification = "Risk Officer Manajemen Risiko";
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
