<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\ProjectPeriodeList;
use App\Models\ProjectRisk;
use App\Models\ProjectRiskAnalisa;
use App\Models\RiskMap;
use App\Models\SkalaDampak;
use App\Models\SkalaProbabilitas;
use App\Models\Project;
use App\Models\Level;
use App\Models\ProjectRiskMonitoring;
use App\Models\RiskMonitoringNote;
use App\Models\Unit;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\KamusRisikoProject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProjectRiskMonitoringController extends BasicCRUDController
{
    protected $model = ProjectRisk::class;
    protected $basePermission = 'project_monitoring';
    protected $resourceName = 'Monitoring Risiko';
    protected $baseRoute = 'projects.monitorings.';
    protected $editType = 'link';

    public function index() {
        $this->baseRouteParams = ['project' => request()->route('project')];
        $projectPeriode = ProjectPeriodeList::with('project', 'projectRisks.peristiwaRisiko')->findOrfail(request()->route('project'));
        $cb = fn ($fn) => $fn;
        $this->indexSubtitle = $projectPeriode->project->project_name;

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriode))) {
            abort(403);
        }

        $userLevel = Auth::user()->level_id;
        $quarter = request()->input('quarter', request()->input('filters.quarter', 1));
        $tahun = request()->input('tahun', request()->input('filters.tahun', date('Y')));

        $defaultMonth = '1';
        if ($quarter == 2) $defaultMonth = '4';
        if ($quarter == 3) $defaultMonth = '7';
        if ($quarter == 4) $defaultMonth = '10';
        $month = request()->input('month', request()->input('filters.month', $defaultMonth));

        $this->callbackQuery = function ($query) use ($projectPeriode, $quarter, $tahun, $month) {
          $query->where('project_periode_list_id', $projectPeriode->id)
                ->with(['peristiwaRisiko', 'projectRiskAnalisa', 'projectRiskAnalisa.risiko', 'projectRiskAnalisa.skalaProbabilitas', 'projectRiskAnalisa.skalaProbabilitasResidual', 'projectRiskMonitoring.skalaProbabilitas', 'projectRiskMonitoring' => function ($q) use ($quarter, $tahun, $month) {
                  $q->where('quarter', $quarter)
                    ->where('tahun', $tahun)
                    ->where('month', $month)
                    ->orderBy('id', 'desc');
              }]);
        };

        // Mapping Level Verifikator
        $levelNames = Level::whereIn('id', [7, 1, 2])->pluck('name', 'id');
        $verificatorMap = [
            ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_PROJECT => $levelNames[7] ? 'Risk Owner Project' :  'Risk Owner Project', // 2
            ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI => $levelNames[1] ? 'Risk Officer Divisi' : 'Risk Officer Divisi', // 3
            ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR => 'Risk Officer Divisi MR', // 4
            ProjectRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR => $levelNames[2] ?'Risk Owner Divisi MR' :  'Risk Owner Divisi MR', // 5
        ];


        $this->tableColumns = [
            'quarter' => [
                'label' => 'Periode Monitoring',
                'orderable' => false,
                'searchable' => false,
                'render' => <<<JS
                    function (data) {
                        return 'Quarter ' + $('#table-filter select[name="quarter"]').val();
                    }
                JS,
            ],
            'peristiwa_risiko' => [
                'label' => 'Peristiwa Risiko',
                'data' => 'peristiwaRisiko.title',
                'render' => '(data, type, row) => row.peristiwa_risiko?.title || "-"',
            ],
            'deskripsi_peristiwa_risiko' => [
                'label' => 'Deskripsi Peristiwa Risiko',
                'data' => 'deskripsi_peristiwa_risiko',
                'sortable' => false,
                'searchable' => true,
            ],
            'nilai_dampak' => [
                'label' => 'Nilai Dampak Residual',
                'data' => 'projectRiskAnalisa.nilai_dampak_residual',
                'sortable' => true,
                'searchable' => false,
                'render' => '(data, type, row) => "Rp" + Intl.NumberFormat("id-ID").format(row.project_risk_analisa?.nilai_dampak_residual) || "-"',
            ],
            'skala_dampak' => [
                'label' => 'Skala Dampak Residual',
                'data' => 'projectRiskAnalisa.skala_dampak_residual',
                'sortable' => true,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_analisa?.skala_dampak_residual || "-"',
            ],
            'skala_probabilitas' => [
                'label' => 'Skala Probabilitas Residual',
                'data' => 'projectRiskAnalisa.skalaProbabilitasResidual.tingkat',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_analisa?.skala_probabilitas_residual?.tingkat || "-"',
            ],
            'skala_risiko' => [
                'label' => 'Skala Risiko Residual',
                'data' => 'projectRiskAnalisa.skala_risiko_residual',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_analisa?.skala_risiko_residual || "-"',
            ],
            'skala_dampak_monitoring' => [
                'label' => 'Skala Dampak Monitoring',
                'data' => 'projectRiskMonitoring.skala_dampak',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_monitoring?.skala_dampak || "-"',
            ],
            'skala_probabilitas_monitoring' => [
                'label' => 'Skala Probabilitas Monitoring',
                'data' => 'projectRiskMonitoring.skalaProbabilitas.tingkat',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_monitoring?.skala_probabilitas?.tingkat || "-"',
            ],
            'skala_risiko_monitoring' => [
                'label' => 'Skala Risiko Monitoring',
                'data' => 'projectRiskMonitoring.skala_risiko',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_monitoring?.skala_risiko || "-"',
            ],
            'is_closed' => [
                'label' => 'Status',
                'data' => 'projectRiskAnalisa.risiko.is_closed',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_analisa?.risiko?.is_closed ? `<div class="badge bg-danger rounded-pill px-2 mt-auto">
                  Closed
                </div>` : `<div class="badge bg-success rounded-pill px-2 mt-auto">
                  Open
                </div>`',
            ],
            'status_monitoring' => [
                'label' => 'Status Monitoring',
                'render' => '(data, type, row) => {
                    if (row.is_closed) return `<div class="badge text-danger bg-danger-subtle">Dihentikan</div>`;
                    if (!row.project_risk_monitoring) return `<div class="badge bg-light text-dark">Belum Dimonitor</div>`;
                    
                    const status = row.project_risk_monitoring.status;
                    const isRevision = row.project_risk_monitoring.is_revision;
                    const isApproved = row.project_risk_monitoring.is_approved;
                    const verificatorMap = '.json_encode($verificatorMap).';

                    if (status === '.ProjectRiskMonitoring::STATUS_DRAFT_REVISI.') {
                        return isRevision ? `<div class="badge bg-danger">Revisi</div>` : `<div class="badge bg-warning">Draft</div>`;
                    }
                    if (verificatorMap[status]) {
                        const verificatorName = verificatorMap[status];
                        if(isApproved){
                            return `<div class="badge bg-info">Terverifikasi ${verificatorName}</div>`;
                        } else {
                            return `<div class="badge border border-info text-info">Menunggu Verifikasi ${verificatorName}</div>`;
                        }
                    }
                    if (status == '.ProjectRiskMonitoring::STATUS_PUBLISHED.') {
                        return `<div class="badge bg-primary">Selesai</div>`;
                    }
                    return "-";
                }',
            ],
        ];

        $this->tableActions = [];

        if (Gate::check('project_monitoring_view')) {
            $showRoute = route('projects.monitorings.show', ['project' => request()->route('project'), 'monitoring' => ':id', 'quarter' => ':quarter', 'tahun' => ':tahun', 'month' => ':month']);
            $this->tableActions[] = [
                'label' => 'View',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$showRoute".replace(':id', $(this).data('id')).replace('%3Aquarter', $('#table-filter select[name="quarter"]').val()).replace('%3Atahun', $('#table-filter select[name="tahun"]').val()).replace('%3Amonth', $('#table-filter select[name="month"]').val());
                JS,
                'active_state' => '(data, type, row) => row.project_risk_monitoring !== null'
            ];
        }

        if (Gate::check('project_monitoring_edit') && $userLevel == 6) {
            $monitoringRoute = route('projects.monitorings.edit', ['project' => request()->route('project'), 'monitoring' => ':id', 'quarter' => ':quarter', 'tahun' => ':tahun', 'month' => ':month']);
            $this->tableActions[] = [
                'label' => 'Monitoring',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$monitoringRoute".replace(':id', $(this).data('id')).replace('%3Aquarter', $('#table-filter select[name="quarter"]').val()).replace('%3Atahun', $('#table-filter select[name="tahun"]').val()).replace('%3Amonth', $('#table-filter select[name="month"]').val());
                JS,
                'active_state' => '(data, type, row) => true',
            ];

            $this->tableActions[] = [
                'label' => 'Change to LED',
                'btn_icon' => false,
                'action' => 'change_to_led',
                'active_state' => '(data, type, row) => row.is_closed != 1',
                'extra_attrs' => [ 'style' => 'font-size: 14px; font-weight: 400;' ]
            ];
        } 

        $hasVerificationMr = Gate::allows('verification_mr');
        $isUnitMr = (bool) $user->unit?->unit_mr;
        $verificatorLevels = [7, 1, 2];
        if (in_array($userLevel, $verificatorLevels)) {
            $this->tableActions[] = [
                'label' => 'Verifikasi',
                'btn_class' => 'btn-warning btn-sm',
                'action' => 'script',
                'script' => "showVerifikasiModal(__MONITORING_ID__, '__RISK_TITLE__', '__RISK_DESC__')",
                'active_state' => '(data, type, row) => {
                    if (row.is_closed) return false;

                    const monitoring = row.project_risk_monitoring;
                    if (!monitoring || monitoring.is_approved) return false;
                    
                    const userLevel = ' . $user->level_id . ';
                    const hasVerificationMr = ' . ($hasVerificationMr ? 'true' : 'false') . ';
                    const user = '.json_encode($user->load('unit')).';
                    const project = '.json_encode($projectPeriode->project).';
                    const status = monitoring.status;
                    
                    if (userLevel == 7 && status == '.ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_PROJECT.') return true;
                    if (userLevel == 1 && status == '.ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI.' && user.unit && user.unit.cost_center == project.cost_center_parent) return true;
                    if (userLevel == 1 && status == '.ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR.' && user.unit && user.unit.unit_mr == 1 && hasVerificationMr) return true;
                    if (userLevel == 2 && status == '.ProjectRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR.' && hasVerificationMr) return true;
                    
                    return false;
                }',
                'extra_attrs' => [ 'data-monitoring-id' => '__MONITORING_ID__', 'data-title' => '__RISK_TITLE__', 'data-desc' => '__RISK_DESC__']
            ];
        }

        $this->tableActions[] = [
            'label' => 'Catatan',
            'btn_class' => 'btn-outline-secondary',
            'action' => 'script',
            'script' => 'showCatatanModal($(this).data("id"))',
            'active_state' => '(data, type, row) => row.project_risk_monitoring !== null',
        ];

        $this->cardFooter = $this->generateFooter($projectPeriode, $user, compact('quarter', 'tahun', 'month'));

        $this->extraViewData['isProjectMonitoringPage'] = true;
        $this->extraViewData['currentUserLevel'] = $userLevel;
        $this->extraViewData['showVerifikasiModal'] = in_array($userLevel, $verificatorLevels);
        $this->extraViewData['showCatatanModal'] = true;

        $peristiwaRisikos = $projectPeriode->projectRisks->map(function($projectRisk) {
            return $projectRisk->peristiwaRisiko;
        })->flatten()->unique('id');

        $tahunOptions = (int) $projectPeriode->created_at?->format('Y') ?? date('Y');
        $optionTahuns = [];
        for ($i = $tahunOptions; $i <= $tahunOptions + 9; $i++) {
            $optionTahuns[$i] = $i;
        }

        $this->availableFilters = [
            'peristiwa_risiko_id' => [
                'label' => 'Peristiwa Risiko',
                'type' => 'select',
                'parameters' => [
                    'peristiwa_risiko_id',
                    ['' => 'Semua Peristiwa Risiko'] + $peristiwaRisikos->pluck('title', 'id')->toArray(),
                    '',
                    [
                        'class' => 'form-select select2',
                    ]
                ],
            ],
            'tahun' => [
                'label' => 'Tahun',
                'type' => 'select',
                'parameters' => [
                    'tahun',
                    $optionTahuns,
                    '',
                    [
                        'class' => 'form-select select2',
                    ]
                ],
                'handler' => function ($query, $key, $value) {
                    // handled outside
                },
            ],
            'quarter' => [
                'label' => 'Quarter',
                'type' => 'select',
                'parameters' => [
                    'quarter',
                    [
                        1 => 'Monitoring Quarter 1',
                        2 => 'Monitoring Quarter 2',
                        3 => 'Monitoring Quarter 3',
                        4 => 'Monitoring Quarter 4',
                    ],
                    '',
                    [
                        'class' => 'form-select select2 js-select-hide-search',
                    ]
                ],
                'handler' => function ($query, $key, $value) {
                    // handled outside
                },
            ],
            'month' => [
                'label' => 'Bulan',
                'type' => 'select',
                'parameters' => [
                    'month',
                    [],
                    '',
                    [
                        'class' => 'form-select select2 js-select-hide-search',
                    ]
                ],
                'handler' => function ($query, $key, $value) {
                    // handled outside
                },
            ],
        ];

        $this->extraScripts[] = $this->getFilterScripts();

        return parent::index();
    }

    public function edit($resource)
    {
        $projectPeriode = ProjectPeriodeList::findOrfail(request()->route('project'));
        $tahun = request()->tahun ?: date('Y');
        $user = request()->user();
        $month = request()->month ?: '';

        // if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriode))) {
        //     abort(403);
        // }

        $quarter = request()->quarter ?: 1;
        $projectRisk = $projectPeriode->projectRisks()
            ->with([
                'projectRiskAnalisa',
                'peristiwaRisiko',
                'kriProjects' => function ($query) use ($quarter, $tahun, $month) {
                    $query->select('k_r_i_projects.*', 'id as status_kri_terkini', 'id as nilai_kri_terkini');
                    $query->with('kriProjectMonitorings', function ($query) use ($quarter, $tahun, $month) {
                        $query->with('projectMonitoring')->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun, $month) {
                            $query->where('quarter', $quarter)
                                ->where('tahun', $tahun)
                                ->where('month', $month);
                        });
                    });
                },
                'penyebabRisikoProjects.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $tahun, $month) {
                        $query->select('perlakuan_penyebab_risikos.*', 'id as deskripsi_perlakuan_risiko', 'id as jenis_program_rkap_id', 'id as jenis_program_rkap', 'id as timeline_perlakuan_risiko', 'id as progress_rencana_perlakuan_risiko', 'id as realisasi_biaya_perlakuan_risiko');
                        $query->with(['lastMonitoring' => function ($query) use ($quarter, $tahun, $month) {
                            $query->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun, $month) {
                                $query->where('quarter', $quarter)
                                    ->where('tahun', $tahun)
                                    ->where('month', $month);
                            });
                        }]);
                        $query->with([
                            'documents',
                            'perlakuanPenyebabMonitorings' => function ($query) use ($quarter, $tahun, $month) {
                                $query->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun, $month) {
                                    $query->where('quarter', $quarter)
                                        ->where('tahun', $tahun)
                                        ->where('month', $month);
                                })->with('projectMonitoring');
                            },
                        ]);
                },
                'projectRiskMonitoring' => function ($query) use ($quarter, $tahun, $month) {
                    $query->where('quarter', $quarter)
                        ->where('tahun', $tahun)
                        ->where('month', $month);
                },
            ])
            ->findOrFail(request()->route('monitoring'));

        $analisa = $projectRisk->projectRiskAnalisa;
        $namaRisikoLengkap = $projectRisk->peristiwaRisiko->title;
        if (!empty($projectRisk->deskripsi_peristiwa_risiko)) {
            $namaRisikoLengkap .= ' - ' . $projectRisk->deskripsi_peristiwa_risiko;
        }

        // Validasi Analisa Risiko
        if (!$analisa) {
            return redirect()->route('projects.monitorings.index', ['project' => $projectPeriode->id])
                ->with('error', 'Risiko "' . $namaRisikoLengkap . '" belum dianalisa. Harap lengkapi analisa risiko terlebih dahulu.');
        }

        $requiredAnalisaFields = [
            'kategori_dampak', 'nilai_dampak', 'nilai_probabilitas', 'skala_dampak',
            'nilai_dampak_residual', 'nilai_probabilitas_residual', 'skala_dampak_residual'
        ];

        foreach ($requiredAnalisaFields as $field) {
            if (is_null($analisa->{$field})) {
                return redirect()->route('projects.monitorings.index', ['project' => $projectPeriode->id])
                    ->with('error', 'Analisa untuk risiko "' . $namaRisikoLengkap . '" belum lengkap. Harap lengkapi semua field analisa inheren dan residual.');
            }
        }
        
        // Validasi Rencana Perlakuan Risiko
        $penyebabRisikos = $projectRisk->penyebabRisikoProjects;
        
        if ($penyebabRisikos->isEmpty()) {
            return redirect()->route('projects.monitorings.index', ['project' => $projectPeriode->id])
                ->with('error', 'Risiko "' . $namaRisikoLengkap . '" belum memiliki data penyebab dan rencana perlakuan.');
        }
        
        $hasValidPerlakuan = false;
        foreach ($penyebabRisikos as $penyebab) {
            if (!empty($penyebab->penyebab_risiko) && $penyebab->perlakuanPenyebabRisiko->isNotEmpty()) {
                foreach ($penyebab->perlakuanPenyebabRisiko as $perlakuan) {
                    if (!empty($perlakuan->rencana_perlakuan_risiko)) {
                        $hasValidPerlakuan = true;
                        break 2;
                    }
                }
            }
        }

        if (!$hasValidPerlakuan) {
            return redirect()->route('projects.monitorings.index', ['project' => $projectPeriode->id])
                ->with('error', 'Risiko "' . $namaRisikoLengkap . '" harus memiliki minimal satu penyebab dengan rencana perlakuan yang sudah diisi.');
        }

        $peristiwaRisiko = $projectRisk->peristiwaRisiko;
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });


        //hitung risk limit dan tolerance
        $risk_tolerance = 0;
        $risk_limit = 0;
        $sum_risk = ProjectRisk::where('periode_id', $projectPeriode->periode_id)
            ->where('project_id', $projectPeriode->project_id)
            ->whereHas('projectRiskAnalisa', function ($query) {
                $query->where('kategori_dampak', 'Kuantitatif');
            })
            ->count();
        //dd($sum_risk);
        $project = $projectPeriode->project;
        //dd($project->type);
        if($project->type==2){
            // $risk_tolerance = $project->rapk_100_rp 
            //     ?? $project->rapk_70_90_rp 
            //     ?? $project->rapk_30_50_rp 
            //     ?? $project->rapk_0_10_rp 
            //     ?? $project->rapk 
            //     ?? 0;
            $risk_tolerance = array_filter([
                $project->rapk_100_rp, 
                $project->rapk_70_90_rp, 
                $project->rapk_30_50_rp, 
                $project->rapk_0_10_rp, 
                $project->rapk
            ], function ($value) {
                return $value !== null && $value != 0;
            });
            
            $risk_tolerance = reset($risk_tolerance) ?: 0;
            
            $risk_tolerance = 2/100 *($risk_tolerance);
            //dd($risk_tolerance);
        }
        else if($project->type==1){
            $risk_tolerance = $project->rapt ?? 0;
            $risk_tolerance = (2*$risk_tolerance/100);
        }
        else{
            $risk_tolerance = 0;
        }
        
        //$risk_limit = $projectPeriode->risk_limit;
        $risk_limit = ($projectPeriode->project->meta['omset'] ?? 0) * 0.03;

        return view('project-monitorings.edit', [
            'projectPeriode' => $projectPeriode,
            'project' => $projectPeriode->project,
            'periode' => $projectPeriode->periode,
            'peristiwaRisiko' => $peristiwaRisiko,
            'projectRisk' => $projectRisk,
            'projectRiskAnalisa' => $projectRisk->projectRiskAnalisa,
            'penyebabRisikoProjects' => $projectRisk->penyebabRisikoProjects,
            'kriProjects' => $projectRisk->kriProjects,
            'riskMonitoring' => $projectRisk->projectRiskMonitoring,
            'skalaDampaks' => SkalaDampak::pluck('deskripsi', 'tingkat'),
            'riskMaps' => $riskMaps,
            'skalaProbabilitas' => $skalaProbabilitas,
            'quarter' => $quarter,
            'tahun' => $tahun,
            'month' => $month,
            'risk_tolerance' => $risk_tolerance,
            'risk_limit' => $risk_limit
        ]);
    }

    public function show($resource)
    {
        $projectPeriode = ProjectPeriodeList::findOrfail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriode))) {
            abort(403);
        }

        $quarter = request()->input('quarter') ?: 1;
        $tahun = request()->input('tahun') ?: date('Y');
        $month = request()->input('month') ?: '';
        $projectRisk = $projectPeriode->projectRisks()
            ->with([
                'peristiwaRisiko',
                'penyebabRisikoProjects',
                'kriProjects' => function ($query) use ($quarter, $tahun, $month) {
                    $query->with('kriProjectMonitorings', function ($query) use ($quarter, $tahun, $month) {
                        $query->with('projectMonitoring')->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun, $month) {
                            $query->where('quarter', $quarter)
                                ->where('tahun', $tahun)
                                ->where('month', $month);
                        });
                    });
                },
                'kriProjects',
                'penyebabRisikoProjects.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $tahun, $month) {
                        $query->select('perlakuan_penyebab_risikos.*', 'id as deskripsi_perlakuan_risiko', 'id as jenis_program_rkap', 'id as timeline_perlakuan_risiko');
                        // $query->with(['lastMonitoring' => function ($query) use ($quarter, $tahun) {
                        //     $query->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun) {
                        //         $query->where('quarter', $quarter)
                        //             ->where('tahun', $tahun);
                        //     });
                        // }
                        //]);
                        $query->with([
                            'documents',
                            'perlakuanPenyebabMonitorings' => function ($query) use ($quarter, $tahun, $month) {
                                $query->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun, $month) {
                                    $query->where('quarter', $quarter)
                                        ->where('tahun', $tahun)
                                        ->where('month', $month);
                                })->with('projectMonitoring');
                            },
                        ]);
                },
            ])
            ->findOrFail(request()->route('monitoring'));

        $peristiwaRisiko = $projectRisk->peristiwaRisiko;
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });
        $projectMonitoring = $projectRisk->projectRiskMonitoring()->where('quarter', $quarter)->where('tahun', $tahun)->where('month', $month)->first();

        $files = $projectMonitoring?->perlakuanPenyebabRisikoDocuments->groupBy('perlakuan_penyebab_risiko_id') ?: [];

        return view('project-monitorings.show', [
            'projectPeriode' => $projectPeriode,
            'project' => $projectPeriode->project,
            'periode' => $projectPeriode->periode,
            'peristiwaRisiko' => $peristiwaRisiko,
            'projectRisk' => $projectRisk,
            'projectRiskAnalisa' => $projectRisk->projectRiskAnalisa,
            'penyebabRisikoProjects' => $projectRisk->penyebabRisikoProjects,
            'kriProjects' => $projectRisk->kriProjects,
            'riskMonitoring' => $projectMonitoring,
            'skalaDampaks' => SkalaDampak::pluck('deskripsi', 'tingkat'),
            'riskMaps' => $riskMaps,
            'skalaProbabilitas' => $skalaProbabilitas,
            'quarter' => $quarter,
            'tahun' => $tahun,
            'files' => $files,
            'month' => $month,
        ]);
    }

    public function update(Request $request, $resource) {
        $quarter = request()->input('quarter') ?: 1;
        $tahun = request()->input('tahun') ?: date('Y');
        $month = request()->input('month') ?: '';
        $projectPeriode = ProjectPeriodeList::findOrfail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriode))) {
            abort(403);
        }

        $projectRisk = $projectPeriode->projectRisks()
            ->with('peristiwaRisiko', 'penyebabRisikoProjects', 'penyebabRisikoProjects.perlakuanPenyebabRisiko', 'kriProjects', 'projectRiskAnalisa')
            ->findOrFail(request()->route('monitoring'));
        $toCreate = [
            'quarter' => $quarter,
            'tahun' => $tahun,
            'nilai_dampak' => str_replace(['Rp', '.', ' '], '', ($request->realisasi_nilai_dampak ?: 0)),
            'skala_dampak' => $request->realisasi_skala_dampak ?? $request->realisasi_skala_dampak_hidden,
            'nilai_probabilitas' => $request->realisasi_nilai_probabilitas,
            'skala_probabilitas_id' => null,
            'skala_risiko' => $request->realisasi_skala_risiko ?? $request->realisasi_skala_risiko_hidden,
            'level_risiko' => $request->realisasi_level_risiko ?? $request->realisasi_level_risiko_hidden,
            'eksposure_risiko' => null,
            'month' => $month,
        ];

        //dd($toCreate);
        if ($request->realisasi_nilai_probabilitas) {
            $tingkatSkalaProbabilitas = SkalaProbabilitas::getSkalaByValue($request->realisasi_nilai_probabilitas);

            if (!$tingkatSkalaProbabilitas) {
                return response()->json([
                    'message' => 'Tidak ada data skala probabilitas yang sesuai',
                ], 422);
            }
            $toCreate['skala_probabilitas_id'] = $tingkatSkalaProbabilitas->id;
            $riskMaps = RiskMap::get()->keyBy(function($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });
    
            $riskMap = $riskMaps[$toCreate['skala_dampak'] . '-' . $tingkatSkalaProbabilitas->tingkat] ?? null;
            if (!$riskMap) {
                return response()->json([
                    'message' => 'Tidak ada data risk map untuk skala dampak dan probabilitas yang dipilih',
                ], 422);
            }
    
            $toCreate['skala_risiko'] = $riskMap->nilai_risiko;
            $toCreate['level_risiko'] = $riskMap->level_risiko;
        } else {
            $toCreate['skala_probabilitas_id'] = null;
            $toCreate['skala_risiko'] = null;
            $toCreate['level_risiko'] = null;
        }


        //perhitungan eksposur risiko
        if ($projectRisk->projectRiskAnalisa?->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF) {
            $toCreate['eksposure_risiko'] = floatval($toCreate['skala_dampak']) * (1/100) * floatval($toCreate['nilai_probabilitas']) * ($projectRisk->projectRiskAnalisa?->risk_limit ?: 0);
        } elseif ($projectRisk->projectRiskAnalisa?->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF) {
            $toCreate['eksposure_risiko'] = floatval($toCreate['nilai_dampak']) * floatval($toCreate['nilai_probabilitas']) / 100;
        }

        $perlakuanPenyebabRequests = json_decode($request->perlakuan_penyebab_risikos, true);
        $kriProjectRequests = json_decode($request->kri_projects, true);

        if ($request->is_closed == '1') {
            // Gabungkan data untuk validasi
            $validationData = $toCreate;
            $validationData['perlakuan_penyebab'] = $perlakuanPenyebabRequests;
            $validationData['kri_projects'] = $kriProjectRequests;

            $validator = Validator::make($validationData, [
                // Validasi data monitoring utama
                'nilai_dampak' => 'required',
                'skala_dampak' => 'required',
                'nilai_probabilitas' => 'required',
                'skala_probabilitas_id' => 'required',
                'skala_risiko' => 'required',
                'level_risiko' => 'required',

                // Validasi data perlakuan (harus ada dan array)
                'perlakuan_penyebab' => 'present|array',
                'perlakuan_penyebab.*.progress_rencana_perlakuan_risiko' => 'required',
                'perlakuan_penyebab.*.realisasi_biaya_perlakuan_risiko' => 'required',
                'perlakuan_penyebab.*.deskripsi_perlakuan_risiko' => 'required',
                'perlakuan_penyebab.*.timeline_perlakuan_risiko' => 'required|array|min:1',

                // Validasi data KRI (harus ada dan array)
                'kri_projects' => 'present|array',
                'kri_projects.*.status_kri_terkini' => 'required',
                'kri_projects.*.nilai_kri_terkini' => 'required',
            ], [
                // Custom messages
                'nilai_dampak.required' => 'Realisasi Nilai Dampak wajib diisi untuk menutup risiko.',
                'skala_dampak.required' => 'Realisasi Skala Dampak wajib diisi untuk menutup risiko.',
                'nilai_probabilitas.required' => 'Realisasi Nilai Probabilitas wajib diisi untuk menutup risiko.',
                'skala_probabilitas_id.required' => 'Realisasi Nilai Probabilitas tidak valid.',
                'skala_risiko.required' => 'Realisasi Skala Risiko wajib diisi untuk menutup risiko.',
                'level_risiko.required' => 'Realisasi Level Risiko wajib diisi untuk menutup risiko.',
                
                'perlakuan_penyebab.*.progress_rencana_perlakuan_risiko.required' => 'Progress Rencana Perlakuan wajib diisi untuk semua penyebab.',
                'perlakuan_penyebab.*.realisasi_biaya_perlakuan_risiko.required' => 'Realisasi Biaya Perlakuan wajib diisi untuk semua penyebab.',
                'perlakuan_penyebab.*.deskripsi_perlakuan_risiko.required' => 'Deskripsi Perlakuan wajib diisi untuk semua penyebab.',
                'perlakuan_penyebab.*.timeline_perlakuan_risiko.required' => 'Timeline Perlakuan wajib diisi untuk semua penyebab.',

                'kri_projects.*.status_kri_terkini.required' => 'Status KRI Terkini wajib diisi untuk semua KRI.',
                'kri_projects.*.nilai_kri_terkini.required' => 'Nilai KRI Terkini wajib diisi untuk semua KRI.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Gagal menutup risiko. Harap lengkapi semua data monitoring.',
                    'errors' => $validator->errors()
                ], 422);
            }
        }

        $projectMonitoring = $projectRisk->projectRiskMonitoring()->create($toCreate);

        foreach ($perlakuanPenyebabRequests as $id => $perlakuanPenyebabRequest) {
            if (is_string($perlakuanPenyebabRequest['timeline_perlakuan_risiko'])) {
                $perlakuanPenyebabRequest['timeline_perlakuan_risiko'] = explode(' - ', $perlakuanPenyebabRequest['timeline_perlakuan_risiko']);
            }
            
            if ($perlakuanPenyebabRequest['timeline_perlakuan_risiko'] && count($perlakuanPenyebabRequest['timeline_perlakuan_risiko']) === 1) {
                $perlakuanPenyebabRequest['timeline_perlakuan_risiko'][] = $perlakuanPenyebabRequest['timeline_perlakuan_risiko'][0];
            }
            $toCreate = [
                'perlakuan_penyebab_id' => $id,
                'progress_rencana_perlakuan_risiko' => $perlakuanPenyebabRequest['progress_rencana_perlakuan_risiko'] ?? null,
                'realisasi_biaya_perlakuan_risiko' => $perlakuanPenyebabRequest['realisasi_biaya_perlakuan_risiko'] ?? null,
                'deskripsi_perlakuan_risiko' => $perlakuanPenyebabRequest['deskripsi_perlakuan_risiko'] ?? null,
                'jenis_program_rkap' => $perlakuanPenyebabRequest['jenis_program_rkap'] ?? null,
                'jenis_program_rkap_id' => $perlakuanPenyebabRequest['jenis_program_rkap_id'] ?? null,
                'timeline_perlakuan_risiko_start' => ($perlakuanPenyebabRequest['timeline_perlakuan_risiko'][0] ?? '') ? DateTime::createFromFormat('d/m/Y', $perlakuanPenyebabRequest['timeline_perlakuan_risiko'][0])->format('Y-m-d') : null,
                'timeline_perlakuan_risiko_end' => ($perlakuanPenyebabRequest['timeline_perlakuan_risiko'][1] ?? '') ? DateTime::createFromFormat('d/m/Y', $perlakuanPenyebabRequest['timeline_perlakuan_risiko'][1])->format('Y-m-d') : null,
            ];
            $projectMonitoring->perlakuanPenyebabMonitorings()->create($toCreate);

            if ($documentFiles = $request->{'document_file_' . $id}) {
                $documentDescriptions = json_decode($request->input('document_description_' . $id, '[]'), true) ?: [];
                foreach ($documentFiles as $idx => $documentFile) {
                    $storeFile = $documentFile->store('project-monitoring-documents');
                    $projectMonitoring->perlakuanPenyebabRisikoDocuments()->create([
                        'perlakuan_penyebab_risiko_id' => $id,
                        'user_id' => request()->user()->id,
                        'file_name' => $documentFile->getClientOriginalName(),
                        'file_path' => $storeFile,
                        'mimetype' => $documentFile->getClientMimeType(),
                        'description' => $documentDescriptions[$idx] ?? '',
                    ]);
                }
            }
        }

        foreach ($kriProjectRequests as $id => $kriProjectRequest) {
            $toCreate = [
                'kri_project_id' => $id,
                'status_kri_terkini' => $kriProjectRequest['status_kri_terkini'],
                'nilai_kri_terkini' => $kriProjectRequest['nilai_kri_terkini'],
            ];
            $projectMonitoring->kriProyekMonitorings()->create($toCreate);
        }

        $projectRisk->refreshRealisasi();
        $projectPeriode->refreshNilai();

        if ($request->is_closed == '1') {
            $efektivitas = 0.0; 

            $analisa = $projectRisk->projectRiskAnalisa;
            $skala_risiko_inherent = (float) optional($analisa)->skala_risiko;
            $skala_risiko_rencana = (float) optional($analisa)->skala_risiko_residual;
            $skala_risiko_realisasi = (float) ($request->realisasi_skala_risiko ?? $request->realisasi_skala_risiko_hidden ?? 0);

            $selisih_inherent_rencana = $skala_risiko_inherent - $skala_risiko_rencana;

            // Hindari pembagian dengan nol
            if ($selisih_inherent_rencana != 0) {
                $efektivitas = ($skala_risiko_rencana - $skala_risiko_realisasi) / $selisih_inherent_rencana;
            }

            $projectRisk->update([
                'is_closed' => true,
                'efektivitas_perlakuan_risiko' => $efektivitas
            ]);

            KamusRisikoProject::updateOrCreate(
                ['project_risk_id' => $projectRisk->id],
                ['project_id' => $projectRisk->project_id],
            );
        }

        return response()->json([
            'message' => 'Data berhasil disimpan',
        ]);
    }

    private function generateFooter($projectPeriode, $user, $filters)
    {
        extract($filters);
        $userLevel = $user->level_id;
        $hasVerificationMr = Gate::allows('verification_mr');
        $isUnitMr = (bool) $user->unit?->unit_mr;
    
        $latestMonitorings = collect([]);
        $riskIds = $projectPeriode->projectRisks()->pluck('id');
        if($riskIds->isNotEmpty()){
            $latestMonitoringIds = ProjectRiskMonitoring::select(DB::raw('MAX(id) as last_id'))
                ->whereIn('risiko_id', $riskIds)
                ->where('quarter', $quarter)
                ->where('tahun', $tahun)
                ->where('month', $month)
                ->groupBy('risiko_id')
                ->pluck('last_id');
            if($latestMonitoringIds->isNotEmpty()){
                $latestMonitorings = ProjectRiskMonitoring::whereIn('id', $latestMonitoringIds)->with('projectRisk')->get();
            }
        }
        
        $buttonText = '';
        $params = [];
        $disabled = 'disabled';
        $allApproved = $latestMonitorings->isNotEmpty() && $latestMonitorings->every(function ($monitoring) {
            return $monitoring->is_approved || $monitoring->projectRisk?->is_closed;
        });
    
        switch ($userLevel) {
            case 6:
                $openRiskIds = $projectPeriode->projectRisks()->where('is_closed', false)->pluck('id');
                $allRisksMonitored = $openRiskIds->isNotEmpty() && $openRiskIds->diff($latestMonitorings->pluck('risiko_id'))->isEmpty();
                $hasItemsToSend = $latestMonitorings->where('status', ProjectRiskMonitoring::STATUS_DRAFT_REVISI)->isNotEmpty();
                if ($allRisksMonitored && $hasItemsToSend) {
                    $buttonText = 'Kirim ke Risk Owner Project';
                    $params = ['status_dari' => ProjectRiskMonitoring::STATUS_DRAFT_REVISI, 'status_ke' => ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_PROJECT];
                    $disabled = '';
                }
                break;
    
            case 7:
                $unitName = 'Divisi';
                if($projectPeriode->project->cost_center_parent) {
                    $unit = Unit::where('cost_center', $projectPeriode->project->cost_center_parent)->first();
                    if($unit) $unitName = $unit->name;
                }
                $buttonText = "Kirim ke Risk Officer {$unitName}";
                $params = ['status_dari' => ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_PROJECT, 'status_ke' => ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI];
                $disabled = $allApproved ? '' : 'disabled';
                break;
    
            case 1:
                if ($user->unit && $user->unit->cost_center == $projectPeriode->project->cost_center_parent) {
                    $buttonText = 'Kirim ke Risk Officer Divisi MR';
                    $params = ['status_dari' => ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI, 'status_ke' => ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR];
                    $disabled = $allApproved ? '' : 'disabled';
                }
                elseif ($user->unit && $isUnitMr && $hasVerificationMr) {
                    $buttonText = 'Kirim ke Risk Owner Divisi MR';
                    $params = ['status_dari' => ProjectRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR, 'status_ke' => ProjectRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR];
                    $disabled = $allApproved ? '' : 'disabled';
                }
                break;
            
            case 2:
                // Tombol aktif JIKA semua item yang menunggu verifikasi level ini (status 5) sudah di-approve
                if ($isUnitMr && $hasVerificationMr) {
                    $monitoringsForThisStep = $latestMonitorings->where('status', ProjectRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR);
                
                    $allApproved = $monitoringsForThisStep->isNotEmpty() &&  $monitoringsForThisStep->every(function ($monitoring) {
                        return $monitoring->is_approved || $monitoring->risiko?->is_closed;
                    });
    
                    $buttonText = 'Terima Semua Monitoring';
                    $params = ['status_dari' => ProjectRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR, 'status_ke' => ProjectRiskMonitoring::STATUS_PUBLISHED, 'final' => true];
                    $disabled = $allApproved ? '' : 'disabled';
                }
                break;
        }
    
        if ($buttonText) {
            return "<div>" . $this->buildEskalasiForm($buttonText, 'projects.monitorings.send.all', $projectPeriode->id, $params, $disabled) . "</div>";
        }
    
        return null;
    }

    private function buildEskalasiForm($buttonText, $routeName, $projectId, $params, $disabled)
    {
        $route = route($routeName, ['project' => $projectId]);
        $csrf = csrf_token();
        $quarter = request()->input('filters.quarter', 1);
        $tahun = request()->input('filters.tahun', date('Y'));
        $month = request()->input('filters.month', '1');
        $statusDari = $params['status_dari'];
        $statusKe = $params['status_ke'];
        $isFinal = $params['final'] ?? false;
        $uniqueId = "form-eskalasi-{$statusDari}";

        return <<<HTML
            <form id="{$uniqueId}" action="{$route}" method="POST" class="d-inline-block">
                <input type="hidden" name="_token" value="{$csrf}">
                <input type="hidden" name="quarter" value="{$quarter}">
                <input type="hidden" name="tahun" value="{$tahun}">
                <input type="hidden" name="month" value="{$month}">
                <input type="hidden" name="status_dari" value="{$statusDari}">
                <input type="hidden" name="status_ke" value="{$statusKe}">
                <input type="hidden" name="is_final" value="{$isFinal}">

                <button type="button" class="btn btn-info btn-arrow-right" onclick="submitEskalasiForm('{$uniqueId}', '{$buttonText}')" {$disabled}>{$buttonText}</button>
            </form>
        HTML;
    }

    private function getFilterScripts()
    {
        return <<<'HTML'
            <script>
            function submitEskalasiForm(formId, actionText) {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: `Apakah Anda yakin ingin melakukan "${actionText}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('#' + formId);
                        const url = form.attr('action');
                        const data = form.serialize();

                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: data,
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                }).then(() => {
                                    $('.ajax-datatable').DataTable().ajax.reload();
                                    form.find('button').prop('disabled', true);
                                });
                            },
                            error: function(xhr) {
                                const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: errorMsg,
                                });
                            }
                        });
                    }
                });
            }

            $(document).ready(function() {
                $('#table-filter select[name="quarter"]').on('change', function() {
                    const quarter = $(this).val();
                    if (quarter) {
                        const allMonths = {
                            '1': {'1': 'Januari', '2': 'Februari', '3': 'Maret'},
                            '2': {'4': 'April', '5': 'Mei', '6': 'Juni'},
                            '3': {'7': 'Juli', '8': 'Agustus', '9': 'September'},
                            '4': {'10': 'Oktober', '11': 'November', '12': 'Desember'},
                        };
                        $('#table-filter select[name="month"]').empty();
                        const months = allMonths[quarter];
                        $.each(months, function(key, value) {
                            $('#table-filter select[name="month"]').append('<option value="' + key + '">' + value + '</option>');
                        });
                    } else {
                        $('#table-filter select[name="month"]').empty();
                        $('#table-filter select[name="month"]').append('<option value="">Semua Bulan</option>');
                    }
                }).change();
            });
            </script>
        HTML;
    }

    public function sendAllMonitoring(Request $request, ProjectPeriodeList $project)
    {
        $validated = $request->validate([
            'quarter' => 'required|integer', 
            'tahun' => 'required|integer', 
            'month' => 'required|integer',
            'status_dari' => 'required|integer', 
            'status_ke' => 'required|integer',
            'is_final' => 'nullable|boolean',
        ]);
        
        $riskIds = $project->projectRisks()->pluck('id');

        $latestMonitoringIds = ProjectRiskMonitoring::select(DB::raw('MAX(id) as last_id'))
            ->whereIn('risiko_id', $riskIds)
            ->where('quarter', $validated['quarter'])->where('tahun', $validated['tahun'])->where('month', $validated['month'])
            ->groupBy('risiko_id')->pluck('last_id');

        $query = ProjectRiskMonitoring::whereIn('id', $latestMonitoringIds)->where('status', $validated['status_dari']);
        
        if ($validated['status_dari'] > ProjectRiskMonitoring::STATUS_DRAFT_REVISI) {
            $query->where('is_approved', true);
        }
        
        // Reset is_approved ke false untuk level verifikasi berikutnya
        $updateData = [
            'status' => $validated['status_ke'],
            'is_approved' => $validated['is_final'] ?? false,
        ];

        $updated = $query->update($updateData);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Monitoring berhasil dieskalasi.']);
        }

        return response()->json(['success' => false, 'message' => 'Tidak ada monitoring yang memenuhi syarat untuk dieskalasi.'], 422);
    }

    public function verifyMonitoring(Request $request, ProjectPeriodeList $project, ProjectRiskMonitoring $monitoring)
    {
        $validated = $request->validate([
            'status_verifikasi' => 'required|in:terima,tolak',
            'notes' => 'required_if:status_verifikasi,tolak|nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($validated, $monitoring) {
            if ($validated['status_verifikasi'] == 'terima') {
                $monitoring->update(['is_approved' => true]);
            } else {
                // Jika ditolak, status kembali ke 1 (Draft/Revisi), dan is_revision ditandai true
                $monitoring->update([
                    'status' => ProjectRiskMonitoring::STATUS_DRAFT_REVISI,
                    'is_approved' => false,
                    'is_revision' => true,
                ]);
            }

            RiskMonitoringNote::create([
                'risiko_id' => $monitoring->risiko_id, 'type' => 2, 'user_id' => Auth::id(),
                'status' => $validated['status_verifikasi'] == 'terima' ? 1 : 0,
                'notes' => $validated['notes'],
                'quarter' => $monitoring->quarter, 'month' => $monitoring->month, 'year' => $monitoring->tahun,
            ]);
        });

        return back()->with('success', 'Verifikasi berhasil disimpan.');
    }

    public function getNotes($project, $riskId, Request $request)
    {
        $risk = ProjectRisk::find($riskId);
        if (!$risk) {
            return response()->json(['message' => 'Risk not found'], 404);
        }

        $notes = RiskMonitoringNote::where('risiko_id', $risk->id)
            ->where('type', 2)
            ->where('quarter', $request->query('quarter'))
            ->where('month', $request->query('month'))
            ->where('year', $request->query('tahun'))
            ->with('user:id,name')
            ->latest()
            ->get();
            
        return response()->json($notes);
    }
}
