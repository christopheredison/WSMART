<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\Periode;
use App\Models\Project;
use App\Models\ProjectPeriodeList;
use App\Models\RiskMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
            'render' => '(data, type, row) => row.project?.meta?.profit_center || "-"',
        ],
        'project_id' => [
            'label' => 'Proyek',
            'data' => 'project.project_name',
            'render' => '(data, type, row) => row.project?.project_name || "-"',
        ],
        'ok' => [
            'label' => 'Nilai OK',
            'data' => 'project.meta.omset',
            'render' => '(data, type, row) => row.project?.meta?.omset ? Intl.NumberFormat(\'id-ID\').format(row.project.meta.omset) : "-"',
        ],
        'tanggal_mulai' => [
            'label' => 'Tanggal Mulai',
            'data' => 'project.meta.tanggal_mulai',
            'render' => '(data, type, row) => {
                if (!row.project?.meta?.tanggal_mulai) return "-";
                const date = new Date(row.project.meta.tanggal_mulai);
                const options = { day: "numeric", month: "short", year: "numeric" };
                return date.toLocaleDateString("id-ID", options);
            }',
        ],
        'skala_risiko' => [
            'label' => 'Nilai Risiko',
            'data' => 'skala_risiko',
            'render' => <<<JS
                (data) => data ? Intl.NumberFormat('id-ID').format(data) : '-'
                JS,
        ],
        'project_risks_count' => [
            'label' => 'Jumlah Risiko',
            'data' => 'project_risks_count',
            'orderable' => false,
            'searchable' => false,
        ],
        'status' => [
            'label' => 'Status',
            'data' => 'status',
            'orderable' => false,
            'searchable' => false,
            'render' => 'data => "Proses"',
        ],
    ];

    public function index() {
        request()->merge([
            'append' => ['project.projectDivisi', 'project.projectSektor'],
            'withCount' => ['projectRisks'],
        ]);
        
        $periodeOptions = Periode::select('id', 'tahun')->orderBy('tahun')->get()->pluck('tahun', 'id')->toArray();

        $user = request()->user();
        $user->load('projects');
        
        $userProjectIds = $user->projects->pluck('id');
        $this->userProjectIdsx = $user->projects->pluck('id')->toArray();

        $this->callbackQuery = function ($query) use ($userProjectIds) {
            if (!Gate::check('project_periode_view')) {
                $query->whereIn('project_id', $userProjectIds);
            }
        };

        $this->datatableCallback = function ($dataTable) use ($user) {
            $dataTable->addColumn('has_view', function ($data) use ($user) {
                return Gate::check('project_periode_view') || $user->hasProject($data);
            });
            $dataTable->addColumn('has_risk_register', function ($data) use ($user) {
                return Gate::check('project_admin_access') || $user->hasProject($data);
            });
            $dataTable->addColumn('has_monitoring', function ($data) use ($user) {
                return Gate::check('project_admin_access') || $user->hasProject($data);
            });
        };

        $projectOptions = Project::select('id', 'project_name')->orderBy('project_name');

        if (!Gate::check('project_admin_access')) {
            $projectOptions->whereIn('id', $userProjectIds);
        }

        $projectOptions = $projectOptions->get()->pluck('project_name', 'id')->toArray();

        if (Gate::check('project_periode_view')) {
            $this->tableActions[] = [
                'label' => 'View',
                'action' => 'link',
                'url' => route('project-periode-list.show', ':id'),
                'active_state' => '(data, type, row) => row.has_view',
            ];
        }

        if (Gate::check('project_risk_list')) {
            $this->tableActions[] = [
                'label' => 'Risk Register',
                'action' => 'link',
                'url' => route('projects.risks.index', ['project' => ':id']),
                'active_state' => '(data, type, row) => row.has_risk_register',
            ];
        }

        if (Gate::check('project_monitoring_list')) {
            $this->tableActions[] = [
                'label' => 'Monitoring',
                'action' => 'link',
                'url' => route('projects.monitorings.index', ['project' => ':id']),
                'active_state' => '(data, type, row) => row.has_monitoring',
            ];
        }

        return parent::index();
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
                'projectRisks.projectRiskAnalisa',
                'projectRisks.projectRiskMonitorings' => function($query) {
                    $query->orderBy('id', 'desc');
                    $query->with('skalaProbabilitas');
                },
            ])
            ->findOrFail($resource);

        $projectPeriode->projectRisks->each(function($projectRisk) {
            $projectRisk->append('currentRiskMaps');
        });

        $tahunMonitorings = $projectPeriode->projectRisks->pluck('projectRiskMonitorings')->flatten()->pluck('tahun')->unique()->toArray();
        $tahunMonitorings[] = $projectPeriode->periode?->tahun;
        sort($tahunMonitorings);
        $minTahun = min($tahunMonitorings);
        $maxTahun = max($tahunMonitorings);
        $tahunMonitorings = [];
        for ($tahun = $minTahun; $tahun <= $maxTahun; $tahun++) {
            $tahunMonitorings[] = $tahun;
        }

        $currentRiskMaps = $projectPeriode->projectRisks->pluck('currentRiskMaps');
        $formattedCurrentRiskMaps = [];
        foreach ($projectPeriode->projectRisks as $idx => $projectRisk) {
            $currentValue = $projectRisk->currentRiskMaps['inherent'];
            foreach ($tahunMonitorings as $tahun) {
                for ($quarter = 1; $quarter <= 4; $quarter++) {
                    if ($nextValue = ($projectRisk->currentRiskMaps[$tahun . '-' . $quarter] ?? null)) {
                        $currentValue = $nextValue;
                    }

                    $currentValue['tahun'] = $tahun;
                    $currentValue['quarter'] = $quarter;

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
                    $projectPeriode->risk_limit,
                    [
                        'class' => 'form-control inputmask-general',
                        'placeholder' => 'Masukkan Risk Limit',
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

        return view('project-periode.show', compact('projectPeriode', 'tahunMonitorings', 'formattedCurrentRiskMaps', 'editFields', 'riskMaps'));
    }
}
