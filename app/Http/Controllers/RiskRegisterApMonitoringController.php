<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\IdentifikasiRisiko;
use App\Models\Periode;
use App\Models\PerlakuanPenyebabRisikoUnitDocument;
use App\Models\ProjectRiskAnalisa;
use App\Models\RiskMap;
use App\Models\SkalaDampak;
use App\Models\SkalaProbabilitas;
use App\Models\StrategiRisiko;
use App\Models\Unit;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\RiskLimitPeriode;
use App\Models\KamusRisikoAp;

class RiskRegisterApMonitoringController extends BasicCRUDController
{
    protected $model = IdentifikasiRisiko::class;
    protected $basePermission = 'risk_monitoring';
    protected $resourceName = 'Monitoring Risiko';
    protected $baseRoute = 'risk-register-ap.monitorings.';
    protected $editType = 'link';

    public function index() {
        $this->baseRouteParams = ['period' => request()->route('period')];
        $period = Periode::with('identifikasiRisikos.peristiwaRisiko')->findOrfail(request()->route('period'));
        $cb = fn ($fn) => $fn;

        $user = request()->user();
        $quarter = request()->input('filters.quarter') ?: 1;
        
        $defaultMonth = '1';
        if ($quarter == 2) $defaultMonth = '4';
        if ($quarter == 3) $defaultMonth = '7';
        if ($quarter == 4) $defaultMonth = '10';
        $month = request()->input('filters.month', $defaultMonth);

        $targetUnitId = null;
        $isApAdmin = Gate::check('ap_admin');

        if ($isApAdmin) {
            if (request()->filled('filters.unit_id')) {
                $targetUnitId = request()->input('filters.unit_id');
            } elseif (request()->filled('unit_id')) {
                $targetUnitId = request()->input('unit_id');
            } else {
                $firstUnit = Unit::where('unit_type_id', 2)->orderBy('id', 'asc')->first();
                $targetUnitId = $firstUnit ? $firstUnit->id : null;
            }
        } else {
            $targetUnitId = $user->unit_id;
        }

        $this->callbackQuery = function ($query) use ($period, $quarter, $user, $month, $targetUnitId) {
            $query->where('periode_id', $period->id)
                ->where('unit_id', $targetUnitId)
                ->where('unit_type_id', 2)
                ->with([
                  // 'peristiwaRisiko', 
                  'riskAnalysis.skalaProbabilitasResidualQ' . $quarter]
                )
                ->with(['lastMonitoringRisiko' => function ($query) use ($quarter, $month) {
                    $query->where('quarter', $quarter)
                        ->when($month, function ($q) use ($month) {
                            return $q->where('month', $month);
                        })
                        ->with('skalaProbabilitas');
                }]);
        };

        $unitFilterOptions = [];
        $unitFilterAttributes = ['class' => 'form-select select2'];

        if ($isApAdmin) {
            $unitFilterOptions = Unit::where('unit_type_id', 2)->pluck('name', 'id')->toArray();
        } else {
            if ($user->unit) {
                $unitFilterOptions = [$user->unit_id => $user->unit->name];
            }
            $unitFilterAttributes['disabled'] = true;
        }

        $filters = [];
        $filters['unit_id'] = [
            'label' => 'Anak Perusahaan',
            'type' => 'select',
            'parameters' => [
                'unit_id',
                $unitFilterOptions,
                $targetUnitId,
                $unitFilterAttributes,
            ],
            'handler' => function ($query, $key, $value) { /* handled outside */ },
        ];

        $filters['quarter'] = [
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
                ['class' => 'form-select select2 js-select-hide-search']
            ],
            'handler' => function ($query, $key, $value) { /* handled outside */ },
        ];
        $filters['month'] = [
            'label' => 'Bulan',
            'type' => 'select',
            'parameters' => [ 'month', [], '', ['class' => 'form-select select2 js-select-hide-search']],
            'handler' => function ($query, $key, $value) { /* handled outside */ },
        ];

        $this->availableFilters = $filters;

        $this->datatableCallback = function ($datatable) use ($quarter, $month) {
            $datatable->addColumn('nilai_dampak_residual', function ($row) use ($quarter) {
                    return $row->riskAnalysis?->{'nilai_dampak_residual_q' . $quarter};
                })->addColumn('skala_dampak_residual', function ($row) use ($quarter) {
                    return $row->riskAnalysis?->{'skala_dampak_residual_q' . $quarter};
                })->addColumn('skala_probabilitas_residual', function ($row) use ($quarter) {
                    return $row->riskAnalysis?->{'skalaProbabilitasResidualQ' . $quarter}?->tingkat;
                })->addColumn('skala_risiko_residual', function ($row) use ($quarter) {
                    return $row->riskAnalysis?->{'skala_risiko_residual_q' . $quarter};
                });
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
                'data' => 'peristiwa_risiko',
                'render' => '(data, type, row) => row.peristiwa_risiko || "-"',
            ],
            'deskripsi_peristiwa_risiko' => [
                'label' => 'Deskripsi Peristiwa Risiko',
                'data' => 'deskripsi_peristiwa_risiko',
                'sortable' => false,
                'searchable' => true,
            ],
            'nilai_dampak' => [
                'label' => 'Nilai Dampak Residual',
                'data' => 'nilai_dampak_residual',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => "Rp" + Intl.NumberFormat("id-ID").format(data) || "-"',
            ],
            'skala_dampak' => [
                'label' => 'Skala Dampak Residual',
                'data' => 'skala_dampak_residual',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => data || "-"',
            ],
            'skala_probabilitas' => [
                'label' => 'Skala Probabilitas Residual',
                'data' => 'skala_probabilitas_residual',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => data || "-"',
            ],
            'skala_risiko' => [
                'label' => 'Skala Risiko Residual',
                'data' => 'skala_risiko_residual',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => data || "-"',
            ],
            'skala_dampak_monitoring' => [
                'label' => 'Skala Dampak Monitoring',
                'data' => 'lastMonitoringRisiko.skala_dampak',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.last_monitoring_risiko?.skala_dampak || "-"',
            ],
            'skala_probabilitas_monitoring' => [
                'label' => 'Skala Probabilitas Monitoring',
                'data' => 'lastMonitoringRisiko.skalaProbabilitas.tingkat',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.last_monitoring_risiko?.skala_probabilitas?.tingkat || "-"',
            ],
            'skala_risiko_monitoring' => [
                'label' => 'Skala Risiko Monitoring',
                'data' => 'lastMonitoringRisiko.skala_risiko',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row.last_monitoring_risiko?.skala_risiko || "-"',
            ],
            'is_closed' => [
                'label' => 'Status',
                'data' => 'is_closed',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => row?.is_closed ? `<div class="badge bg-danger rounded-pill px-2 mt-auto">
                  Closed
                </div>` : `<div class="badge bg-success rounded-pill px-2 mt-auto">
                  Open
                </div>`',
            ],
        ];

        if (Gate::check('risk_monitoring_view')) {
            $showRoute = route('risk-register-ap.monitorings.show', ['period' => request()->route('period'), 'monitoring' => ':id', 'quarter' => ':quarter', 'month' => ':month']);
            $this->tableActions[] = [
                'label' => 'View',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$showRoute".replace(':id', $(this).data('id')).replace('%3Aquarter', $('#table-filter select[name="quarter"]').val()).replace('%3Amonth', $('#table-filter select[name="month"]').val());
                JS,
            ];
        }

        if (Gate::check('risk_monitoring_input')) {
            $monitoringRoute = route('risk-register-ap.monitorings.edit', ['period' => request()->route('period'), 'monitoring' => ':id', 'quarter' => ':quarter', 'month' => ':month']);
            $this->tableActions[] = [
                'label' => 'Monitoring',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$monitoringRoute".replace(':id', $(this).data('id')).replace('%3Aquarter', $('#table-filter select[name="quarter"]').val()).replace('%3Amonth', $('#table-filter select[name="month"]').val());
                JS,
                'active_state' => '(data, type, row) => row.is_closed != 1',
            ];

            if (request()->routeIs('risk-register-ap.monitorings.index')) {
                $this->tableActions[] = [
                    'label' => 'Change',
                    'btn_icon' => false,
                    'action' => 'change_to_led_ap',
                    'active_state' => '(data, type, row) => row.is_closed != 1',
                ];
            }
        }

        $peristiwaRisikos = $period->identifikasiRisikos->map(function($identifikasiRisiko) {
            return $identifikasiRisiko->peristiwaRisiko;
        })->flatten()->unique('id');

        $this->extraScripts[] = <<<HTML
            <script>
                $(document).ready(function() {
                    $('#table-filter select[name="quarter"]').on('change', function() {
                        const quarter = $(this).val();
                        if (quarter) {
                            const allMonths = {
                                '1': {
                                    '1': 'Januari',
                                    '2': 'Februari',
                                    '3': 'Maret',
                                },
                                '2': {
                                    '4': 'April',
                                    '5': 'Mei',
                                    '6': 'Juni',
                                },
                                '3': {
                                    '7': 'Juli',
                                    '8': 'Agustus',
                                    '9': 'September',
                                },
                                '4': {
                                    '10': 'Oktober',
                                    '11': 'November',
                                    '12': 'Desember',
                                },
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

        return parent::index();
    }

    public function edit($resource)
    {
        $period = Periode::findOrfail(request()->route('period'));
        $user = request()->user();
        $month = request()->input('month') ?: '';
        // if (!(Gate::check('risk_monitoring_edit') || $user->hasProject($period))) {
        //     abort(403);
        // }

        $quarter = request()->input('quarter') ?: 1;
        $risk = $period->identifikasiRisikos()
            ->findOrFail(request()->route('monitoring'));

        $unit = $risk->unit;
        $periode = $risk->periode;

        $risk->load(['lastMonitoringRisiko' => function ($query) use ($quarter, $month) {
            $query->where('quarter', $quarter);
            $query->where('month', $month);
            $query->with('perlakuanPenyebabRisikos', 'perlakuanPenyebabMonitorings', 'kriUnitMonitorings');
        }])->load(['penyebabRisikos.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $month) {
            $query->select('perlakuan_penyebab_risiko_units.*', 'id as deskripsi_perlakuan_risiko', 'id as jenis_program_rkap', 'id as jenis_program_rkap_id', 'id as timeline_perlakuan_risiko');
            $query->with(['lastMonitoring' => function ($query) use ($quarter, $month) {
                $query->whereHas('unitRiskMonitoring', function ($query) use ($quarter, $month) {
                    $query->where('quarter', $quarter);
                    $query->where('month', $month);
                });
            }]);
            $query->with(['perlakuanPenyebabMonitorings' => function ($query) use ($quarter, $month) {
                $query->whereHas('unitRiskMonitoring', function ($query) use ($quarter, $month) {
                    $query->where('quarter', $quarter);
                    $query->where('month', $month);
                });
            }]);
            $query->with(['documents']);
        }]);

        $skalaDampaks = SkalaDampak::pluck('deskripsi', 'tingkat');
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        //$riskLimit = 0;
        $risk_tolerance = 0;
        $riskLimit = 0;

        $riskLimitPeriode = RisklimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
        if ($riskLimitPeriode) {
            $riskLimit = $riskLimitPeriode->risk_limit;
            $risk_tolerance = $riskLimitPeriode->risk_limit;
            // $totalOtherIdentifikasiRisiko = IdentifikasiRisiko::where('unit_id', $unit->id)
            //     ->where('periode_id', $periode->id)
            //     ->whereHas('riskAnalysis', function($query) {
            //         $query->where('kategori_dampak', 'Kuantitatif');
            //     })
            //     ->count();

            // if ($totalOtherIdentifikasiRisiko > 0) {
            //     $risk_limit = $risk_limit / $totalOtherIdentifikasiRisiko;
            // }
        }

        // $strategiRisiko = StrategiRisiko::where('unit_id', $risk->unit_id)->where('periode_id', $period->id)->first();
        // if ($strategiRisiko) {
        //     $totalAnggaranUnit = $strategiRisiko->total_anggaran_unit;
        //     $riskLimitPercentage = config('risk_limit.percentage');
        //     $riskLimit = $totalAnggaranUnit * $riskLimitPercentage / 100;

        //     $totalOtherIdentifikasiRisiko = IdentifikasiRisiko::where('unit_id', $risk->unit_id)
        //         ->where('periode_id', $period->id)
        //         ->whereHas('riskAnalysis', function ($query) {
        //             $query->where('kategori_dampak', 'Kuantitatif');
        //         })
        //         ->count();

        //     if ($totalOtherIdentifikasiRisiko > 0) {
        //         $riskLimit = $riskLimit / $totalOtherIdentifikasiRisiko;
        //     }
        // }

        return view('risk-register-ap.monitorings.edit', [
            'period' => $period,
            'risk' => $risk,
            'quarter' => $quarter,
            'month' => $month,
            'riskAnalysis' => optional($risk->riskAnalysis),
            'riskMonitoring' => $risk->lastMonitoringRisiko,
            'skalaDampaks' => $skalaDampaks,
            'skalaProbabilitas' => $skalaProbabilitas,
            'riskMaps' => $riskMaps,
            'riskLimit' => $riskLimit,
        ]);
    }

    public function show($resource)
    {
        $period = Periode::findOrfail(request()->route('period'));
        $user = request()->user();
        $month = request()->input('month') ?: '';

        if (!(Gate::check('risk_monitoring_view') || $user->hasProject($period))) {
            abort(403);
        }

        $quarter = request()->input('quarter') ?: 1;
        $risk = $period->identifikasiRisikos()
            ->findOrFail(request()->route('monitoring'));

        $risk->load(['lastMonitoringRisiko' => function ($query) use ($quarter, $month) {
            $query->where('quarter', $quarter);
            $query->where('month', $month);
            $query->with('perlakuanPenyebabRisikos', 'perlakuanPenyebabMonitorings', 'kriUnitMonitorings');
        }])->load(['penyebabRisikos.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $month) {
            $query->select('perlakuan_penyebab_risiko_units.*', 'id as deskripsi_perlakuan_risiko', 'id as jenis_program_rkap', 'id as jenis_program_rkap_id', 'id as timeline_perlakuan_risiko');
            $query->with(['lastMonitoring' => function ($query) use ($quarter, $month) {
                $query->whereHas('unitRiskMonitoring', function ($query) use ($quarter, $month) {
                    $query->where('quarter', $quarter);
                    $query->where('month', $month);
                });
            }]);
            $query->with(['perlakuanPenyebabMonitorings' => function ($query) use ($quarter, $month) {
                $query->whereHas('unitRiskMonitoring', function ($query) use ($quarter, $month) {
                    $query->where('quarter', $quarter);
                    $query->where('month', $month);
                });
            }]);
            $query->with(['documents']);
        }]);

        $skalaDampaks = SkalaDampak::pluck('deskripsi', 'tingkat');
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        $files = $risk->lastMonitoringRisiko?->perlakuanPenyebabRisikoDocuments->groupBy('perlakuan_penyebab_risiko_unit_id') ?: [];

        return view('risk-register-ap.monitorings.show', [
            'period' => $period,
            'risk' => $risk,
            'quarter' => $quarter,
            'month' => $month,
            'riskAnalysis' => optional($risk->riskAnalysis),
            'riskMonitoring' => $risk->lastMonitoringRisiko,
            'skalaDampaks' => $skalaDampaks,
            'skalaProbabilitas' => $skalaProbabilitas,
            'riskMaps' => $riskMaps,
            'files' => $files,
        ]);
    }

    public function update(Request $request, $resource) {
        // dd($request->all());
        $quarter = request()->input('quarter') ?: 1;
        $period = Periode::findOrfail(request()->route('period'));
        $month = request()->input('month') ?: '';

        $user = request()->user();

        if (!(Gate::check('risk_monitoring_edit') || $user->hasProject($period))) {
            abort(403);
        }

        $risk = $period->identifikasiRisikos()
            ->with('peristiwaRisiko', 'penyebabRisikos', 'penyebabRisikos.perlakuanPenyebabRisiko', 'kris', 'riskAnalysis')
            ->findOrFail(request()->route('monitoring'));
        $toCreate = [
            'quarter' => $quarter,
            'month' => $month,
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
        if ($risk->riskAnalysis?->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF) {
            $toCreate['eksposur_risiko'] = floatval($toCreate['skala_dampak']) * (1/100) * floatval($toCreate['nilai_probabilitas']) * ($risk->riskAnalysis?->risk_limit ?: 0);
        } elseif ($risk->riskAnalysis?->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF) {
            $toCreate['eksposur_risiko'] = floatval($toCreate['nilai_dampak']) * floatval($toCreate['nilai_probabilitas']) / 100;
        }

        $projectMonitoring = $risk->monitoringRisikos()->create($toCreate);

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
                'perlakuan_penyebab_risiko_unit_id' => $id,
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
                    $storeFile = $documentFile->store('risk-register-ap-monitoring-documents');
                    PerlakuanPenyebabRisikoUnitDocument::create([
                        'perlakuan_penyebab_risiko_unit_id' => $id,
                        'unit_risk_monitoring_id' => $projectMonitoring->id,
                        'user_id' => request()->user()->id,
                        'quarter' => $quarter,
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
                'key_risk_indicator_id' => $kriProjectRequest['id'],
                'status_kri_terkini' => $kriProjectRequest['status_kri_terkini_q' . $quarter],
                'nilai_kri_terkini' => $kriProjectRequest['nilai_kri_terkini_q' . $quarter],
            ];
            $projectMonitoring->kriUnitMonitorings()->create($toCreate);
        }

        $risk->refreshRealisasi();

        if ($request->is_closed == '1') {
          $efektivitas = 0;

          $analisa = $risk->riskAnalysis;
          $skala_risiko_inherent = (float) optional($analisa)->skala_risiko;
          $skala_risiko_rencana = (float) optional($analisa)['skala_risiko_residual_q' . $quarter];
          $skala_risiko_realisasi = (float) ($request->realisasi_skala_risiko ?? $request->realisasi_skala_risiko_hidden ?? 0);

          $selisih_inherent_rencana = $skala_risiko_inherent - $skala_risiko_rencana;

          // Hindari pembagian dengan nol
          if ($selisih_inherent_rencana != 0) {
              $efektivitas = ($skala_risiko_rencana - $skala_risiko_realisasi) / $selisih_inherent_rencana;
          }

          $risk->update([
              'is_closed' => true,
              'efektivitas_perlakuan_risiko' => $efektivitas
          ]);

          KamusRisikoAp::updateOrCreate(
              ['risiko_id' => $risk->id],
          );
        }

        return response()->json([
            'message' => 'Data berhasil disimpan',
        ]);
    }
}
