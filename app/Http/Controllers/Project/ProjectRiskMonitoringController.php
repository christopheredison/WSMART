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
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectRiskMonitoringController extends BasicCRUDController
{
    protected $model = ProjectRisk::class;
    protected $basePermission = 'project_monitoring';
    protected $resourceName = 'Monitoring Risiko';
    protected $baseRoute = 'projects.monitorings.';
    protected $editType = 'link';

    public function index() {
        $this->baseRouteParams = ['project' => request()->route('project')];
        $projectPeriode = ProjectPeriodeList::with('projectRisks.peristiwaRisiko')->findOrfail(request()->route('project'));
        $cb = fn ($fn) => $fn;

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriode))) {
            abort(403);
        }

        $this->callbackQuery = function ($query) use ($projectPeriode) {
            $query->where('project_periode_list_id', $projectPeriode->id)
                ->with(['peristiwaRisiko', 'projectRiskAnalisa', 'projectRiskAnalisa.skalaProbabilitas', 'projectRiskAnalisa.skalaProbabilitasResidual', 'projectRiskMonitoring.skalaProbabilitas', 'projectRiskMonitoring' => function ($query) {
                    $query->where('quarter', request()->input('filters.quarter') ?: 1)
                        ->where('tahun', request()->input('filters.tahun') ?: date('Y'));
                }]);
        };

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
            /*
            'nilai_dampak' => [
                'label' => 'Nilai Dampak Inherent',
                'data' => 'projectRiskAnalisa.nilai_dampak',
                'sortable' => true,
                'searchable' => false,
                'render' => '(data, type, row) => Intl.NumberFormat().format(row.project_risk_analisa?.nilai_dampak) || "-"',
            ],
            'skala_dampak' => [
                'label' => 'Skala Dampak Inherent',
                'data' => 'projectRiskAnalisa.skala_dampak',
                'sortable' => true,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_analisa?.skala_dampak || "-"',
            ],
            'skala_probabilitas' => [
                'label' => 'Skala Probabilitas Inherent',
                'data' => 'projectRiskAnalisa.skalaProbabilitas.tingkat',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_analisa?.skala_probabilitas?.tingkat || "-"',
            ],
            'skala_risiko' => [
                'label' => 'Skala Risiko Inherent',
                'data' => 'projectRiskAnalisa.skala_risiko',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.project_risk_analisa?.skala_risiko || "-"',
            ],
            */
            'nilai_dampak' => [
                'label' => 'Nilai Dampak Residual',
                'data' => 'projectRiskAnalisa.nilai_dampak_residual',
                'sortable' => true,
                'searchable' => false,
                'render' => '(data, type, row) => Intl.NumberFormat().format(row.project_risk_analisa?.nilai_dampak_residual) || "-"',
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
        ];

        if (Gate::check('project_monitoring_view')) {
            $showRoute = route('projects.monitorings.show', ['project' => request()->route('project'), 'monitoring' => ':id', 'quarter' => ':quarter', 'tahun' => ':tahun']);
            $this->tableActions[] = [
                'label' => 'View',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$showRoute".replace(':id', $(this).data('id')).replace('%3Aquarter', $('#table-filter select[name="quarter"]').val()).replace('%3Atahun', $('#table-filter select[name="tahun"]').val());
                JS,
            ];
        }

        if (Gate::check('project_monitoring_edit')) {
            $monitoringRoute = route('projects.monitorings.edit', ['project' => request()->route('project'), 'monitoring' => ':id', 'quarter' => ':quarter', 'tahun' => ':tahun']);
            $this->tableActions[] = [
                'label' => 'Monitoring',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$monitoringRoute".replace(':id', $(this).data('id')).replace('%3Aquarter', $('#table-filter select[name="quarter"]').val()).replace('%3Atahun', $('#table-filter select[name="tahun"]').val());
                JS,
            ];
        }
        /*
        if (Gate::check('project_monitoring_document_list')) {
            $documentRoute = route('projects.monitorings.documents.index', ['monitoring' => ':id', 'quarter' => ':quarter']);
            $this->tableActions[] = [
                'label' => 'Upload Dokumen',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$documentRoute".replace(':id', $(this).data('id')).replace(':quarter', $('#table-filter select[name="quarter"]').val());
                JS,
            ];
        }
        */

        $peristiwaRisikos = $projectPeriode->projectRisks->map(function($projectRisk) {
            return $projectRisk->peristiwaRisiko;
        })->flatten()->unique('id');

        $tahun = $projectPeriode->periode->tahun;
        $optionTahuns = [];
        for ($i = $tahun; $i <= $tahun + 9; $i++) {
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
                        'class' => 'form-select',
                    ]
                ],
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
                        'class' => 'form-select',
                    ]
                ],
                'handler' => function ($query, $key, $value) {
                    // handled outside
                },
            ],
            'tahun' => [
                'label' => 'Tahun',
                'type' => 'select',
                'parameters' => [
                    'tahun',
                    $optionTahuns,
                    '',
                    [
                        'class' => 'form-select',
                    ]
                ],
                'handler' => function ($query, $key, $value) {
                    // handled outside
                },
            ],
        ];

        return parent::index();
    }

    public function edit($resource)
    {
        $projectPeriode = ProjectPeriodeList::findOrfail(request()->route('project'));
        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriode))) {
            abort(403);
        }

        $quarter = request()->input('quarter') ?: 1;
        $tahun = request()->input('tahun') ?: date('Y');
        $projectRisk = $projectPeriode->projectRisks()
            ->with([
                'peristiwaRisiko',
                'penyebabRisikoProjects',
                'kriProjects.kriProjectMonitorings' => function ($query) {
                    $query->with('projectMonitoring');
                },
                'kriProjects',
                'penyebabRisikoProjects.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $tahun) {
                        $query->select('perlakuan_penyebab_risikos.*', 'id as deskripsi_perlakuan_risiko', 'id as jenis_program_rkap_id', 'id as jenis_program_rkap', 'id as timeline_perlakuan_risiko');
                        // $query->with(['lastMonitoring' => function ($query) use ($quarter, $tahun) {
                        //     $query->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun) {
                        //         $query->where('quarter', $quarter);
                        //         $query->where('tahun', $tahun);
                        //     });
                        // }, 'perlakuanPenyebabMonitorings']);
                        $query->with([
                            'documents',
                            'perlakuanPenyebabMonitorings' => function ($query) use ($quarter, $tahun) {
                                $query->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun) {
                                    $query->where('quarter', $quarter)
                                        ->where('tahun', $tahun);
                                })->with('projectMonitoring');
                            },
                        ]);
                },
                'projectRiskMonitoring' => function ($query) use ($quarter, $tahun) {
                    $query->where('quarter', $quarter)
                        ->where('tahun', $tahun);
                },
            ])
            ->findOrFail(request()->route('monitoring'));

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
        
        if ($risk_tolerance != 0 && $sum_risk != 0) {
            $risk_limit = $risk_tolerance/$sum_risk;
        }
        else{
            $risk_limit = $risk_tolerance;
        }

        //dd($risk_limit);

        //dd($projectRisk->penyebabRisikoProjects);
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
        $projectRisk = $projectPeriode->projectRisks()
            ->with([
                'peristiwaRisiko',
                'penyebabRisikoProjects',
                'kriProjects.kriProjectMonitorings' => function ($query) {
                    $query->with('projectMonitoring');
                },
                'kriProjects',
                'penyebabRisikoProjects.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $tahun) {
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
                            'perlakuanPenyebabMonitorings' => function ($query) use ($quarter, $tahun) {
                                $query->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun) {
                                    $query->where('quarter', $quarter)
                                        ->where('tahun', $tahun);
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
        $projectMonitoring = $projectRisk->projectRiskMonitoring()->where('quarter', $quarter)->where('tahun', $tahun)->first();

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
            'files' => $files,
            'tahun' => $tahun,
        ]);
    }

    public function update(Request $request, $resource) {
        $quarter = request()->input('quarter') ?: 1;
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
            'tahun' => request()->input('tahun') ?: date('Y'),
            'nilai_dampak' => str_replace(['Rp', '.', ' '], '', ($request->realisasi_nilai_dampak ?: 0)),
            'skala_dampak' => $request->realisasi_skala_dampak ?? $request->realisasi_skala_dampak_hidden,
            'nilai_probabilitas' => $request->realisasi_nilai_probabilitas,
            'skala_probabilitas_id' => null,
            'skala_risiko' => $request->realisasi_skala_risiko ?? $request->realisasi_skala_risiko_hidden,
            'level_risiko' => $request->realisasi_level_risiko ?? $request->realisasi_level_risiko_hidden,
            'eksposure_risiko' => null,
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
            $toCreate['eksposur_risiko'] = floatval($toCreate['skala_dampak']) * (1/100) * floatval($toCreate['nilai_probabilitas']) * ($projectRisk->projectRiskAnalisa?->risk_limit ?: 0);
        } elseif ($projectRisk->projectRiskAnalisa?->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF) {
            $toCreate['eksposur_risiko'] = floatval($toCreate['nilai_dampak']) * floatval($toCreate['nilai_probabilitas']) / 100;
        }

        $projectMonitoring = $projectRisk->projectRiskMonitoring()->create($toCreate);

        $perlakuanPenyebabRequests = json_decode($request->perlakuan_penyebab_risikos, true);
        $kriProjectRequests = json_decode($request->kri_projects, true);

        foreach ($perlakuanPenyebabRequests as $id => $perlakuanPenyebabRequest) {
            if (is_string($perlakuanPenyebabRequest['timeline_perlakuan_risiko'])) {
                $perlakuanPenyebabRequest['timeline_perlakuan_risiko'] = explode(' - ', $perlakuanPenyebabRequest['timeline_perlakuan_risiko']);
            }
            
            if ($perlakuanPenyebabRequest['timeline_perlakuan_risiko'] && count($perlakuanPenyebabRequest['timeline_perlakuan_risiko']) === 1) {
                $perlakuanPenyebabRequest['timeline_perlakuan_risiko'][] = $perlakuanPenyebabRequest['timeline_perlakuan_risiko'][0];
            }
            $toCreate = [
                'perlakuan_penyebab_id' => $id,
                'progress_rencana_perlakuan_risiko' => $perlakuanPenyebabRequest['progress_rencana_perlakuan_risiko_q' . $quarter] ?? null,
                'realisasi_biaya_perlakuan_risiko' => $perlakuanPenyebabRequest['realisasi_biaya_perlakuan_risiko_q' . $quarter] ?? null,
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
                'status_kri_terkini' => $kriProjectRequest['status_kri_terkini_q' . $quarter],
                'nilai_kri_terkini' => $kriProjectRequest['nilai_kri_terkini_q' . $quarter],
            ];
            $projectMonitoring->kriProyekMonitorings()->create($toCreate);
        }

        //$projectRisk->refreshRealisasi();
        $projectPeriode->refreshNilai();

        return response()->json([
            'message' => 'Data berhasil disimpan',
        ]);
    }
}
