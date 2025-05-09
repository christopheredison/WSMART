<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\Periode;
use App\Models\Project;
use App\Models\ProjectLocation;
use App\Models\ProjectPeriodeList;
use App\Models\ProjectSektor;
use App\Models\ProjectDivisi;
use App\Models\ProjectType;
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
        'project_id' => [
            'label' => 'Proyek',
            'data' => 'project.project_name',
            'render' => '(data, type, row) => row.project?.project_name || "-"',
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

        $this->createFields = [
            [
                'name' => 'project_id',
                'type' => 'select',
                'label' => 'Proyek',
                'parameters' => [
                    'project_id',
                    $projectOptions,
                    '',
                    [
                        'class' => 'form-control select2-modal',
                        'placeholder' => 'Pilih Proyek',
                        'required' => true,
                    ]
                ],
            ],
        ];

        // $this->availableFilters = [
        //     'periode_id' => [
        //         'label' => 'Periode',
        //         'type' => 'select',
        //         'parameters' => [
        //             'periode_id',
        //             $periodeOptions,
        //             '',
        //             [
        //                 'class' => 'form-select',
        //                 'placeholder' => 'Periode',
        //             ]
        //         ],
        //     ],
        // ];

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
        $tahunMonitorings[] = $projectPeriode->periode->tahun;
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
                'name' => 'project_divisi_id',
                'type' => 'select',
                'label' => 'Divisi',
                'parameters' => [
                    'project_divisi_id',
                    ProjectDivisi::select('id', 'divisi_name')->orderBy('divisi_name')->get()->pluck('divisi_name', 'id')->toArray(),
                    $projectPeriode->project->project_divisi_id,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Divisi',
                        'id' => 'project_divisi_id',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'project_sektor_id',
                'type' => 'select',
                'label' => 'Konstruksi Spesifik',
                'parameters' => [
                    'project_sektor_id',
                    ProjectSektor::select('id', 'sektor_name')->orderBy('sektor_name')->get()->pluck('sektor_name', 'id')->toArray(),
                    $projectPeriode->project->project_sektor_id,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Konstruksi Spesifik',
                        'id' => 'project_sektor_id',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'tender_status',
                'type' => 'select',
                'label' => 'Status Proyek',
                'parameters' => [
                    'tender_status',
                    __('project.tender_statuses'),
                    $projectPeriode->project->tender_status,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Status Proyek',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'project_type_id',
                'type' => 'select',
                'label' => 'Jenis Proyek',
                'parameters' => [
                    'project_type_id',
                    ProjectType::select('id', 'name')->orderBy('name')->get()->pluck('name', 'id')->toArray(),
                    $projectPeriode->project->project_type_id,
                    [
                        'class' => 'form-select select2-modal',
                        'placeholder' => 'Pilih Jenis Proyek',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'owner',
                'type' => 'text',
                'label' => 'Owner',
                'parameters' => [
                    'owner',
                    $projectPeriode->project->owner,
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Nama Owner',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'owner_category',
                'type' => 'select',
                'label' => 'Kategori Owner',
                'parameters' => [
                    'owner_category',
                    __('project.owner_categories'),
                    $projectPeriode->project->owner_category,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Kategori Owner',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'sumber_dana',
                'type' => 'select',
                'label' => 'Sumber Dana',
                'parameters' => [
                    'sumber_dana',
                    __('project.sumber_danas'),
                    $projectPeriode->project->sumber_dana,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Sumber Dana',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'project_location_id',
                'type' => 'select',
                'label' => 'Lokasi Proyek',
                'parameters' => [
                    'project_location_id',
                    ProjectLocation::select('id', 'location')->orderBy('location')->get()->pluck('location', 'id')->toArray(),
                    $projectPeriode->project->project_location_id,
                    [
                        'class' => 'form-select select2-modal',
                        'placeholder' => 'Pilih Lokasi Proyek',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'jenis_kontrak',
                'type' => 'select',
                'label' => 'Jenis Kontrak',
                'parameters' => [
                    'jenis_kontrak',
                    __('project.jenis_kontraks'),
                    $projectPeriode->project->jenis_kontrak,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Jenis Kontrak',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'cara_pembayaran',
                'type' => 'select',
                'label' => 'Cara Pembayaran',
                'parameters' => [
                    'cara_pembayaran',
                    __('project.cara_pembayarans'),
                    $projectPeriode->project->cara_pembayaran,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Cara Pembayaran',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'scope_pekerjaan',
                'type' => 'select',
                'label' => 'Scope Pekerjaan',
                'parameters' => [
                    'scope_pekerjaan',
                    __('project.scope_pekerjaans'),
                    $projectPeriode->project->scope_pekerjaan,
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Scope Pekerjaan',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'nk_ppn',
                'type' => 'text',
                'label' => 'Nilai Kontrak - PPN (Rp)',
                'parameters' => [
                    'nk_ppn',
                    $projectPeriode->project->nk_ppn,
                    [
                        'class' => 'form-control inputmask-general',
                        'placeholder' => 'Masukkan Nilai Kontrak PPN',
                        //'required' => true,
                    ]
                ],
            ],            
            [
                'name' => 'nk',
                'type' => 'text',
                'label' => 'Nilai Kontrak Addendum (Rp)',
                'parameters' => [
                    'nk',
                    $projectPeriode->project->nk,
                    [
                        'class' => 'form-control inputmask-general',
                        'placeholder' => 'Masukkan Nilai Kontrak',
                        //'required' => true,
                    ]
                ],
            ],
            // [
            //     'name' => 'masa_pelaksanaan',
            //     'type' => 'text',
            //     'label' => 'Masa Pelaksanaan',
            //     'parameters' => [
            //         'masa_pelaksanaan',
            //         $projectPeriode->project->masa_pelaksanaan,
            //         [
            //             'class' => 'form-control flatpickr-range',
            //             'placeholder' => 'Masukkan Masa Pelaksanaan',
            //             //'required' => true,
            //         ]
            //     ],
            // ]
            [
                'name' => 'masa_pelaksanaan_awal',
                'type' => 'text',
                'label' => 'Awal Masa Pelaksanaan',
                'parameters' => [
                    'masa_pelaksanaan_awal',
                    $projectPeriode->project->display_masa_pelaksanaan_start,
                    [
                        'class' => 'form-control flatpickr',
                        'placeholder' => 'Masukkan Awal Masa Pelaksanaan',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'masa_pelaksanaan_akhir',
                'type' => 'text',
                'label' => 'Akhir Masa Pelaksanaan',
                'parameters' => [
                    'masa_pelaksanaan_akhir',
                    $projectPeriode->project->display_masa_pelaksanaan_end,
                    [
                        'class' => 'form-control flatpickr',
                        'placeholder' => 'Masukkan Akhir Masa Pelaksanaan',
                        //'required' => true,
                    ]
                ],
            ],
        ];

        if($projectPeriode->project->type==1){
            $editFields = array_merge($editFields, [
                [
                    'name' => 'rapt',
                    'type' => 'text',
                    'label' => 'RAPT (Rp)',
                    'parameters' => [
                        'rapt',
                        $projectPeriode->project->rapt,
                        [
                            'class' => 'form-control inputmask-general',
                            'placeholder' => 'Masukkan RAPT (Rp)',
                            //'required' => true,
                            'step' => '0.01',
                        ]
                    ],
                ],
                [
                    'name' => 'rapt_persentase',
                    'type' => 'number',
                    'label' => 'RAPT (%)',
                    'parameters' => [
                        'rapt_persentase',
                        $projectPeriode->project->rapt_persentase,
                        [
                            'class' => 'form-control',
                            'placeholder' => 'Masukkan RAPT (%)',
                            //'required' => true,
                            'max' => 100,
                            'min' => 0,
                        ]
                    ],
                ],
            ]);
        }
        else{
            $editFields = array_merge($editFields, [
                [
                    'name' => 'rapk',
                    'type' => 'text',
                    'label' => 'RAPK (Rp)',
                    'parameters' => [
                        'rapk',
                        $projectPeriode->project->rapk,
                        [
                            'class' => 'form-control inputmask-general',
                            'placeholder' => 'Masukkan RAPK (Rp)',
                            //'required' => true,
                            'step' => '0.01',
                        ]
                    ],
                ],
                [
                    'name' => 'rapk_persentase',
                    'type' => 'text',
                    'label' => 'RAPK (%)',
                    'parameters' => [
                        'rapk_persentase',
                        $projectPeriode->project->rapk_persentase,
                        [
                            'class' => 'form-control',
                            'placeholder' => 'Masukkan RAPK (%)',
                            'readonly' => true,
                        ]
                    ],
                ],
                [
                    'name' => 'rapk_0_10_rp',
                    'type' => 'text',
                    'label' => 'RAPK Hold Point I (Rp)',
                    'parameters' => [
                        'rapk_0_10_rp',
                        $projectPeriode->project->rapk_0_10_rp,
                        [
                            'class' => 'form-control inputmask-general',
                            'placeholder' => 'Masukkan RAPK Hold Point 0-10% (Rp)',
                            //'required' => true,
                            'step' => '0.01',
                        ]
                    ],
                ],
                [
                    'name' => 'rapk_0_10_persen',
                    'type' => 'number',
                    'label' => 'RAPK Hold Point I (%)',
                    'parameters' => [
                        'rapk_0_10_persen',
                        $projectPeriode->project->rapk_0_10_persen,
                        [
                            'class' => 'form-control',
                            'placeholder' => 'Masukkan RAPK Hold Point 0-10% (%)',
                            //'required' => true,
                            'max' => 100,
                            'min' => 0,
                        ]
                    ],
                ],
                [
                    'name' => 'rapk_30_50_rp',
                    'type' => 'text',
                    'label' => 'RAPK Hold Point II (Rp)',
                    'parameters' => [
                        'rapk_30_50_rp',
                        $projectPeriode->project->rapk_30_50_rp,
                        [
                            'class' => 'form-control inputmask-general',
                            'placeholder' => 'Masukkan RAPK Hold Point 30-50% (Rp)',
                            //'required' => true,
                            'step' => '0.01',
                        ]
                    ],
                ],
                [
                    'name' => 'rapk_30_50_persen',
                    'type' => 'number',
                    'label' => 'RAPK Hold Point II (%)',
                    'parameters' => [
                        'rapk_30_50_persen',
                        $projectPeriode->project->rapk_30_50_persen,
                        [
                            'class' => 'form-control',
                            'placeholder' => 'Masukkan RAPK Hold Point 30-50% (%)',
                            //'required' => true,
                            'max' => 100,
                            'min' => 0,
                        ]
                    ],
                ],
                [
                    'name' => 'rapk_70_90_rp',
                    'type' => 'text',
                    'label' => 'RAPK Hold Point III (Rp)',
                    'parameters' => [
                        'rapk_70_90_rp',
                        $projectPeriode->project->rapk_70_90_rp,
                        [
                            'class' => 'form-control inputmask-general',
                            'placeholder' => 'Masukkan RAPK Hold Point 70-90% (Rp)',
                            //'required' => true,
                            'step' => '0.01',
                        ]
                    ],
                ],
                [
                    'name' => 'rapk_70_90_persen',
                    'type' => 'number',
                    'label' => 'RAPK Hold Point III (%)',
                    'parameters' => [
                        'rapk_70_90_persen',
                        $projectPeriode->project->rapk_70_90_persen,
                        [
                            'class' => 'form-control',
                            'placeholder' => 'Masukkan RAPK Hold Point 70-90% (%)',
                            //'required' => true,
                            'max' => 100,
                            'min' => 0,
                        ]
                    ],
                ],
                [
                    'name' => 'rapk_100_rp',
                    'type' => 'text',
                    'label' => 'RAPK Hold Point IV (Rp)',
                    'parameters' => [
                        'rapk_100_rp',
                        $projectPeriode->project->rapk_100_rp,
                        [
                            'class' => 'form-control inputmask-general',
                            'placeholder' => 'Masukkan RAPK Hold Point 100% (Rp)',
                            //'required' => true,
                            'step' => '0.01',
                        ]
                    ],
                ],
                [
                    'name' => 'rapk_100_persen',
                    'type' => 'number',
                    'label' => 'RAPK Hold Point IV (%)',
                    'parameters' => [
                        'rapk_100_persen',
                        $projectPeriode->project->rapk_100_persen,
                        [
                            'class' => 'form-control',
                            'placeholder' => 'Masukkan RAPK Hold Point 100% (%)',
                            //'required' => true,
                            'max' => 100,
                            'min' => 0,
                        ]
                    ],
                ],
            ]);
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        return view('project-periode.show', compact('projectPeriode', 'tahunMonitorings', 'formattedCurrentRiskMaps', 'editFields', 'riskMaps'));
    }
}
