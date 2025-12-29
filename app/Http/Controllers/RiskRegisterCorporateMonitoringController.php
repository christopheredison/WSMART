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
use App\Models\Level;
use App\Models\UnitRiskMonitoring;
use App\Models\Unit;
use App\Models\RiskMonitoringNote;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\RiskLimitPeriode;
use App\Models\KamusRisikoUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RiskRegisterCorporateMonitoringController extends BasicCRUDController
{
    protected $model = IdentifikasiRisiko::class;
    protected $basePermission = 'risk_monitoring';
    protected $resourceName = 'Monitoring Risiko';
    protected $baseRoute = 'corporate-risk.monitorings.';
    protected $editType = 'link';

    public function index() {
        $this->baseRouteParams = [
          'period' => request()->route('period')
        ];
        $period = Periode::with('identifikasiRisikos.peristiwaRisiko')->findOrfail(request()->route('period'));
        $cb = fn ($fn) => $fn;

        $user = request()->user();
        $userLevel = Auth::user()->level_id;
        $quarter = request()->input('filters.quarter') ?: 1;

        $defaultMonth = '1';
        if ($quarter == 2) $defaultMonth = '4';
        if ($quarter == 3) $defaultMonth = '7';
        if ($quarter == 4) $defaultMonth = '10';
        $month = request()->input('filters.month', $defaultMonth);

        $unit = Unit::where('unit_type_id', 4)->first();
        $targetUnitId = $unit->id;
        if ($unit) {
          $this->indexSubtitle = $unit->name;
        }
        $isUnitMr = $unit->unit_mr;

        // if (!(Gate::check('risk_monitoring_list') || $user->hasProject($period))) {
        //     abort(403);
        // }

        $this->callbackQuery = function ($query) use ($period, $quarter, $user, $month, $targetUnitId) {
            $query->where('periode_id', $period->id)
                ->where('unit_id', $targetUnitId)
                ->where('unit_type_id', 4)
                // ->where('is_corporate', 1)
                ->with([
                  // 'peristiwaRisiko',
                  'unit',
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

        $unitMr = Unit::where('unit_mr', 1)->first();
        $unitMrName = $unitMr?->name ?? 'Divisi MR';
        // $levelNames = Level::whereIn('id', [1, 2])->pluck('name', 'id');
        // $verificatorMap = [
        //     UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI => $levelNames[2] ?? 'Risk Owner Divisi',
        //     UnitRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR => 'Risk Officer Divisi MR',
        //     UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR => 'Risk Owner Divisi MR',
        // ];

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
            'status_monitoring' => [
                'label' => 'Status Monitoring',
                'render' => '(data, type, row) => {
                    if (row.is_closed) return `<div class="badge text-danger bg-danger-subtle">Dihentikan</div>`;
                    if (!row.last_monitoring_risiko) return `<div class="badge bg-light text-dark">Belum Dimonitor</div>`;

                    const monitoring = row.last_monitoring_risiko;
                    let statusText = "";
                    const unitMrName = "' . $unitMrName . '";

                    const STATUS_DRAFT = ' . UnitRiskMonitoring::STATUS_DRAFT_REVISI . ';
                    const STATUS_ROW_DIVISI = ' . UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI . ';
                    const STATUS_RO_MR = ' . UnitRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR . ';
                    const STATUS_ROW_MR = ' . UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR . ';
                    const STATUS_PUBLISHED = ' . UnitRiskMonitoring::STATUS_PUBLISHED . ';

                    switch(monitoring.status) {
                        case STATUS_DRAFT:
                            return monitoring.is_revision ? `<div class="badge bg-danger">Revisi</div>` : `<div class="badge bg-warning">Draft</div>`;
                        case STATUS_ROW_DIVISI:
                            statusText = `Risk Owner Divisi ${row.unit?.name || ""}`;
                            break;
                        case STATUS_RO_MR:
                            statusText = `Risk Officer ${unitMrName}`;
                            break;
                        case STATUS_ROW_MR:
                            statusText = `Risk Owner ${unitMrName}`;
                            break;
                        case STATUS_PUBLISHED:
                            return `<div class="badge bg-primary">Terverifikasi</div>`;
                        default:
                            return "-";
                    }

                    if (statusText) {
                        return monitoring.is_approved
                            ? `<div class="badge bg-info">Terverifikasi ${statusText}</div>`
                            : `<div class="badge border border-info text-info">Menunggu Verifikasi ${statusText}</div>`;
                    }

                    return "-";
                }',
            ],
        ];

        if (Gate::check('risk_monitoring_view')) {
            $showRoute = route('corporate-risk.monitorings.show', ['period' => request()->route('period'), 'monitoring' => ':id', 'quarter' => ':quarter', 'month' => ':month']);
            $this->tableActions[] = [
                'label' => 'View',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$showRoute".replace(':id', $(this).data('id')).replace('%3Aquarter', $('#table-filter select[name="quarter"]').val()).replace('%3Amonth', $('#table-filter select[name="month"]').val());
                JS,
            ];
        }

        $isUserUnitMr = (bool) $user->unit?->unit_mr;
        if (Gate::check('risk_monitoring_input') && $userLevel == 1 && ($isUnitMr == $isUserUnitMr)) {
            $monitoringRoute = route('corporate-risk.monitorings.edit', ['period' => request()->route('period'), 'monitoring' => ':id', 'quarter' => ':quarter', 'month' => ':month']);
            $this->tableActions[] = [
                'label' => 'Monitoring',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$monitoringRoute".replace(':id', $(this).data('id')).replace('%3Aquarter', $('#table-filter select[name="quarter"]').val()).replace('%3Amonth', $('#table-filter select[name="month"]').val());
                JS,
                'active_state' => '(data, type, row) => !row.is_closed && (!row.last_monitoring_risiko || row.last_monitoring_risiko.status == '.UnitRiskMonitoring::STATUS_DRAFT_REVISI.')'
            ];

            $this->tableActions[] = [
                'label' => 'Change to LED',
                'btn_icon' => false,
                'action' => 'change_to_led_unit',
                'active_state' => '(data, type, row) => row.is_closed != 1',
                'extra_attrs' => [ 'style' => 'font-size: 14px; font-weight: 400;' ]
            ];

            $this->tableActions[] = [
                'label' => 'Peluang',
                'btn_icon' => false,
                'action' => 'script',
                'script' => "showPeluangModal($(this).data('id'), '__RISK_TITLE__', '__RISK_DESC__')",
                'active_state' => '(data, type, row) => true',
                'extra_attrs' => [ 'style' => 'font-size: 14px; font-weight: 400;', 'data-id' => 'row.id' ]
            ];
        }

        $hasVerificationMr = Gate::allows('verification_mr');
        $verificatorLevels = [2, 1];
        if (in_array($user->level_id, $verificatorLevels)) {
            $this->tableActions[] = [
                'label' => 'Verifikasi',
                'btn_class' => 'btn-warning btn-sm',
                'action' => 'script',
                'script' => "showVerifikasiModal(__MONITORING_ID__, '__RISK_TITLE__', '__RISK_DESC__')",
                'active_state' => '(data, type, row) => {
                    if (row.is_closed) return false;

                    const monitoring = row.last_monitoring_risiko;
                    if (!monitoring || monitoring.is_approved) return false;

                    const userLevel = ' . $user->level_id . ';
                    const isUserUnitMr = ' . ($isUserUnitMr ? 'true' : 'false') . ';
                    const hasVerificationMr = ' . ($hasVerificationMr ? 'true' : 'false') . ';
                    const status = monitoring.status;

                    // Verifier for Step 2 (Risk Owner Divisi -> Risk Officer Divisi MR)
                    if (userLevel == 2 && status == '.UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI.') return true;

                    // Verifier for Step 3 (Risk Officer Divisi MR -> Risk Owner Divisi MR)
                    if (userLevel == 1 && isUserUnitMr && hasVerificationMr && status == '.UnitRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR.') return true;

                    // Verifier for Step 4 (Risk Owner Divisi MR -> Publish)
                    if (userLevel == 2 && isUserUnitMr && hasVerificationMr && status == '.UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR.') return true;

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
            'active_state' => '(data, type, row) => row.last_monitoring_risiko !== null',
        ];

        $this->cardFooter = $this->generateFooter($period, $user, compact('quarter', 'month', 'targetUnitId'));

        $this->extraViewData['isProjectMonitoringPage'] = true;
        $this->extraViewData['currentUserLevel'] = $userLevel;
        $this->extraViewData['showVerifikasiModal'] = in_array($userLevel, $verificatorLevels);
        $this->extraViewData['showCatatanModal'] = true;

        $peristiwaRisikos = $period->identifikasiRisikos->map(function($identifikasiRisiko) {
            return $identifikasiRisiko->peristiwaRisiko;
        })->flatten()->unique('id');

        $unitFilterAttributes = ['class' => 'form-select select2'];
        $unitFilterOptions = Unit::where('unit_type_id', 4)->pluck('name', 'id')->toArray();
        $unitFilterAttributes['disabled'] = true;

        $filters = [];
        $filters['unit_id'] = [
            'label' => 'Divisi',
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
                [
                    'class' => 'form-select select2 js-select-hide-search',
                ]
            ],
            'handler' => function ($query, $key, $value) {
                // handled outside
            },
        ];
        $filters['month'] = [
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
        ];

        $this->availableFilters = $filters;

        $this->extraScripts[] = $this->getFilterScripts();

        return parent::index();
    }

    public function edit($resource)
    {
        $period = Periode::findOrfail(request()->route('period'));
        $user = request()->user();
        $quarter = request()->input('quarter') ?: 1;
        $month = request()->input('month') ?: '';

        // $risk = $period->identifikasiRisikos()
        //     ->findOrFail(request()->route('monitoring'));

        // $risk->load(['lastMonitoringRisiko' => function ($query) use ($quarter, $month) {
        //     $query->where('quarter', $quarter);
        //     $query->where('month', $month);
        //     $query->with([
        //       'perlakuanPenyebabRisikos',
        //       'perlakuanPenyebabMonitorings',
        //       'kriUnitMonitorings',
        //       'pengendalians'
        //     ]);
        // }])->load(['penyebabRisikos.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $month) {
        //     $query->select('perlakuan_penyebab_risiko_units.*', 'id as deskripsi_perlakuan_risiko', 'id as jenis_program_rkap', 'id as jenis_program_rkap_id', 'id as timeline_perlakuan_risiko');
        //     $query->with(['lastMonitoring' => function ($query) use ($quarter, $month) {
        //         $query->whereHas('unitRiskMonitoring', function ($query) use ($quarter, $month) {
        //             $query->where('quarter', $quarter);
        //             $query->where('month', $month);
        //         });
        //     }]);
        //     $query->with(['perlakuanPenyebabMonitorings' => function ($query) use ($quarter, $month) {
        //         $query->whereHas('unitRiskMonitoring', function ($query) use ($quarter, $month) {
        //             $query->where('quarter', $quarter);
        //             $query->where('month', $month);
        //         });
        //     }]);
        //     $query->with(['documents']);
        // }])->load(['taksonomiRisiko', 'parameterRisikos']);

        $risk = $period->identifikasiRisikos()
            ->with([
                'unit',
                'periode',
                'riskAnalysis',
                'peristiwaRisiko',
            ])
            ->findOrFail(request()->route('monitoring'));

        $risk->load([
            'lastMonitoringRisiko' => function ($query) use ($quarter, $month) {
                $query->where('quarter', $quarter);
                if ($month) $query->where('month', $month);
                $query->with([
                    'perlakuanPenyebabRisikos',
                    'perlakuanPenyebabMonitorings',
                    'kriUnitMonitorings',
                    'pengendalians'
                ]);
            },
            // Load Perlakuan Penyebab
            'penyebabRisikos.perlakuanPenyebabRisikoUnit' => function ($query) use ($quarter, $month) {
                $query->with(['lastMonitoring' => function ($q) use ($quarter, $month) {
                    $q->whereHas('unitRiskMonitoring', function ($sq) use ($quarter, $month) {
                        $sq->where('quarter', $quarter);
                        if ($month) $sq->where('month', $month);
                    });
                }]);
            },
            // Load Perlakuan Dampak
            'perlakuanDampakRisikos' => function ($query) use ($quarter, $month) {
                $query->with([
                    'picJabatan',
                    'dampakRisikoUnit',
                    'lastMonitoring' => function ($q) use ($quarter, $month) {
                        $q->whereHas('unitRiskMonitoring', function ($sq) use ($quarter, $month) {
                            $sq->where('quarter', $quarter);
                            if ($month) $sq->where('month', $month);
                        });
                    }
                ]);
            },
            'taksonomiRisiko',
            'parameterRisikos',
        ]);

        $unit = $risk->unit;
        $periode = $risk->periode;
        $currentYear = $period->tahun;

        $currentDate = \Carbon\Carbon::create($currentYear, $month, 1);
        $dateM1 = $currentDate->copy()->subMonth();
        $dateM2 = $currentDate->copy()->subMonths(2);

        $monitoringM1 = $risk->monitoringRisikos()->where('month', $dateM1->month)->first();
        $monitoringM2 = $risk->monitoringRisikos()->where('month', $dateM2->month)->first();

        $lastEntry = $risk->monitoringRisikos()
            ->with('pengendalians')
            ->where('month', '<', $month)
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->first();

        $historicalPengendalians = $lastEntry ? $lastEntry->pengendalians->keyBy('parameter_id') : collect();

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

        return view('corporate-risk.monitorings.edit', [
            'period' => $period,
            'risk' => $risk,
            'quarter' => $quarter,
            'month' => $month,
            'tahun' => $currentYear,
            'riskAnalysis' => optional($risk->riskAnalysis),
            'riskMonitoring' => $risk->lastMonitoringRisiko,
            'monitoringM1' => $monitoringM1,
            'monitoringM2' => $monitoringM2,
            'historicalPengendalians' => $historicalPengendalians,
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
        // $risk = $period->identifikasiRisikos()
        //     ->findOrFail(request()->route('monitoring'));

        // $risk->load(['lastMonitoringRisiko' => function ($query) use ($quarter, $month) {
        //     $query->where('quarter', $quarter);
        //     $query->where('month', $month);
        //     $query->with('perlakuanPenyebabRisikos', 'perlakuanPenyebabMonitorings', 'kriUnitMonitorings');
        // }])->load(['penyebabRisikos.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $month) {
        //     $query->select('perlakuan_penyebab_risiko_units.*', 'id as deskripsi_perlakuan_risiko', 'id as jenis_program_rkap', 'id as jenis_program_rkap_id', 'id as timeline_perlakuan_risiko');
        //     $query->with(['lastMonitoring' => function ($query) use ($quarter, $month) {
        //         $query->whereHas('unitRiskMonitoring', function ($query) use ($quarter, $month) {
        //             $query->where('quarter', $quarter);
        //             $query->where('month', $month);
        //         });
        //     }]);
        //     $query->with(['perlakuanPenyebabMonitorings' => function ($query) use ($quarter, $month) {
        //         $query->whereHas('unitRiskMonitoring', function ($query) use ($quarter, $month) {
        //             $query->where('quarter', $quarter);
        //             $query->where('month', $month);
        //         });
        //     }]);
        //     $query->with(['documents']);
        // }]);

        $risk = $period->identifikasiRisikos()
        ->with([
            'taksonomiRisiko',
            'parameterRisikos',
            'peristiwaRisiko',
            'riskAnalysis',
            'lastMonitoringRisiko' => function ($query) use ($quarter, $month) {
                $query->where('quarter', $quarter)
                      ->where('month', $month)
                      ->with('pengendalians.parameter');
            },
            'penyebabRisikos.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $month) {
                $query->with(['lastMonitoring' => function ($q) use ($quarter, $month) {
                    $q->whereHas('unitRiskMonitoring', function ($q2) use ($quarter, $month) {
                        $q2->where('quarter', $quarter)->where('month', $month);
                    });
                }]);
            },
            'kris.kriUnitMonitorings' => function ($query) use ($quarter, $month) {
                $query->whereHas('unitRiskMonitoring', function ($q) use ($quarter, $month) {
                    $q->where('quarter', $quarter)->where('month', $month);
                });
            }
        ])
        ->findOrFail(request()->route('monitoring'));

        $skalaDampaks = SkalaDampak::pluck('deskripsi', 'tingkat');
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        $files = $risk->lastMonitoringRisiko?->perlakuanPenyebabRisikoDocuments->groupBy('perlakuan_penyebab_risiko_unit_id') ?: [];

        $currentYear = $period->tahun;
        $dateCurrent = \Carbon\Carbon::create($currentYear, $month, 1);
        $dateM1 = $dateCurrent->copy()->subMonth();
        $dateM2 = $dateCurrent->copy()->subMonths(2);

        $monitoringM1 = $risk->monitoringRisikos()->where('month', $dateM1->month)->first();
        $monitoringM2 = $risk->monitoringRisikos()->where('month', $dateM2->month)->first();

        return view('corporate-risk.monitorings.show', [
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
            'monitoringM1' => $monitoringM1,
            'monitoringM2' => $monitoringM2,
            'dateCurrent' => $dateCurrent,
            'dateM1' => $dateM1,
            'dateM2' => $dateM2,
        ]);
    }

    public function update(Request $request, $resource) {
        //dd($request->all());
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
            'aktual_current' => $this->cleanRupiah($request->aktual_current),
            'aktual_month_1' => $this->cleanRupiah($request->aktual_month_1),
            'aktual_month_2' => $this->cleanRupiah($request->aktual_month_2),
            'aktual_status' => $request->aktual_status,
        ];

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
            $toCreate['eksposure_risiko'] = floatval($toCreate['skala_dampak']) * (1/100) * floatval($toCreate['nilai_probabilitas']) * ($risk->riskAnalysis?->risk_limit ?: 0);
        } elseif ($risk->riskAnalysis?->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF) {
            $toCreate['eksposure_risiko'] = floatval($toCreate['nilai_dampak']) * floatval($toCreate['nilai_probabilitas']) / 100;
        }

        $projectMonitoring = $risk->monitoringRisikos()->create($toCreate);

        // Simpan Rencana & Realisasi Pengendalian jika status Siaga/Bahaya
        // $projectMonitoring->pengendalians()->delete();
        if (in_array($request->aktual_status, ['Siaga', 'Bahaya'])) {
            $paramIds = $request->input('pengendalian_parameter_id', []);
            $rencana = $request->input('rencana_pengendalian', []);
            $realisasi = $request->input('realisasi_pengendalian', []);

            foreach ($paramIds as $key => $pId) {
                if (!empty($rencana[$key])) {
                    $projectMonitoring->pengendalians()->create([
                        'parameter_id' => $pId,
                        'rencana_pengendalian' => $rencana[$key],
                        'realisasi_pengendalian' => $realisasi[$key] ?? null,
                    ]);
                }
            }
        }

        $perlakuanDampakReq = json_decode($request->perlakuan_dampak_risikos, true);
        if($perlakuanDampakReq) {
            foreach ($perlakuanDampakReq as $id => $item) {
                $perlakuanModel = \App\Models\PerlakuanDampakRisikoUnit::find($id);
                if (!$perlakuanModel) continue;

                $start = null;
                if (!empty($item['timeline_perlakuan_risiko'])) {
                    try {
                        $start = \Carbon\Carbon::createFromFormat('d/m/Y', $item['timeline_perlakuan_risiko'])->format('Y-m-d');
                    } catch(\Exception $e) {
                        $start = \Carbon\Carbon::parse($item['timeline_perlakuan_risiko'])->format('Y-m-d');
                    }
                }

                $projectMonitoring->perlakuanDampakMonitorings()->create([
                    'perlakuan_dampak_id' => $id,
                    'dampak_risiko_id' => $perlakuanModel->dampak_risiko_id,
                    'progress_rencana_perlakuan_risiko' => $item['progress_rencana_perlakuan_risiko'] ?? 0,
                    'realisasi_biaya_perlakuan_risiko' => $this->cleanRupiah($item['realisasi_biaya_perlakuan_risiko'] ?? 0),
                    'deskripsi_perlakuan_risiko' => $item['deskripsi_perlakuan_risiko'] ?? '',
                    'timeline_perlakuan_risiko_start' => $start,
                    'timeline_perlakuan_risiko_end' => $start,
                ]);
            }
        }

        $perlakuanPenyebabRequests = json_decode($request->perlakuan_penyebab_risikos, true);
        foreach ($perlakuanPenyebabRequests as $id => $perlakuanPenyebabRequest) {
            $start = null;
            if (!empty($perlakuanPenyebabRequest['timeline_perlakuan_risiko'])) {
                try {
                    $start = \Carbon\Carbon::createFromFormat('d/m/Y', $perlakuanPenyebabRequest['timeline_perlakuan_risiko'])->format('Y-m-d');
                } catch(\Exception $e) {
                    $start = \Carbon\Carbon::parse($perlakuanPenyebabRequest['timeline_perlakuan_risiko'])->format('Y-m-d');
                }
            }

            $toCreate = [
                'perlakuan_penyebab_risiko_unit_id' => $id,
                'progress_rencana_perlakuan_risiko' => $perlakuanPenyebabRequest['progress_rencana_perlakuan_risiko_q' . $quarter] ?? 0,
                'realisasi_biaya_perlakuan_risiko' => $perlakuanPenyebabRequest['realisasi_biaya_perlakuan_risiko_q' . $quarter] ?? 0,
                'deskripsi_perlakuan_risiko' => $perlakuanPenyebabRequest['deskripsi_perlakuan_risiko'] ?? null,
                'jenis_program_rkap' => $perlakuanPenyebabRequest['jenis_program_rkap'] ?? null,
                'jenis_program_rkap_id' => $perlakuanPenyebabRequest['jenis_program_rkap_id'] ?? null,
                'timeline_perlakuan_risiko_start' => $start,
                'timeline_perlakuan_risiko_end' => $start,
            ];

            $projectMonitoring->perlakuanPenyebabMonitorings()->create($toCreate);

            if ($documentFiles = $request->{'document_file_' . $id}) {
                $documentDescriptions = json_decode($request->input('document_description_' . $id, '[]'), true) ?: [];
                foreach ($documentFiles as $idx => $documentFile) {
                    $storeFile = $documentFile->store('corporate-risk-monitoring-documents');
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

        $kriProjectRequests = json_decode($request->kri_projects, true);
        foreach ($kriProjectRequests as $id => $kriProjectRequest) {
            $toCreate = [
                'key_risk_indicator_id' => $kriProjectRequest['id'],
                'status_kri_terkini' => $kriProjectRequest['status_kri_terkini_q' . $quarter],
                'nilai_kri_terkini' => $kriProjectRequest['nilai_kri_terkini_q' . $quarter],
            ];
            $projectMonitoring->kriUnitMonitorings()->create($toCreate);
        }

        $risk->refreshRealisasi();

        $efektivitas = 0;

        $analisa = $risk->riskAnalysis;
        $skala_risiko_inherent = (float) optional($analisa)->skala_risiko;
        $skala_risiko_rencana = (float) optional($analisa)['skala_risiko_residual_q' . $quarter];
        $skala_risiko_realisasi = (float) ($request->realisasi_skala_risiko ?? $request->realisasi_skala_risiko_hidden ?? 0);

        $selisih_inherent_rencana = $skala_risiko_inherent - $skala_risiko_rencana;

        // Hindari pembagian dengan nol
        if ($selisih_inherent_rencana != 0) {
            $efektivitas = (($skala_risiko_rencana - $skala_risiko_realisasi) / $selisih_inherent_rencana) * 100;
        }

        $risk->update([
            'efektivitas_perlakuan_risiko' => round($efektivitas, 2)
        ]);

        if ($request->is_closed == '1') {
            $risk->update([
                'is_closed' => true,
            ]);

            KamusRisikoUnit::updateOrCreate(
                ['risiko_id' => $risk->id],
            );
        }

        return response()->json([
            'message' => 'Data berhasil disimpan',
        ]);
    }

    private function generateFooter($period, $user, $filters)
    {
        extract($filters);
        $userLevel = $user->level_id;
        $hasVerificationMr = Gate::allows('verification_mr');
        $isUnitMr = (bool) $user->unit?->unit_mr;

        $currentUserUnitName = $user->unit?->name ?? 'Anda';

        $unitMr = Unit::where('unit_mr', 1)->first();
        $unitMrName = $unitMr?->name ?? 'Divisi MR';

        $riskIds = IdentifikasiRisiko::where('periode_id', $period->id)
            ->where('unit_id', $targetUnitId)
            ->where('is_closed', false)
            ->pluck('id');

        $latestMonitorings = collect([]);

        if ($riskIds->isNotEmpty()) {
            $latestMonitoringIds = UnitRiskMonitoring::select(DB::raw('MAX(id) as last_id'))
                ->whereIn('identifikasi_risiko_id', $riskIds)
                ->where('quarter', $quarter)
                ->where('month', $month)
                ->groupBy('identifikasi_risiko_id')
                ->pluck('last_id');

            if ($latestMonitoringIds->isNotEmpty()) {
                $latestMonitorings = UnitRiskMonitoring::whereIn('id', $latestMonitoringIds)->get();
            }
        }

        $buttonText = '';
        $params = [];
        $disabled = 'disabled';

        switch ($userLevel) {
            case 1:
                if ($isUnitMr && $hasVerificationMr) {
                    // Step 3: Kirim ke Risk Owner Divisi MR
                    $allApproved = $latestMonitorings->where('status', UnitRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR)->isNotEmpty() && $latestMonitorings->where('status', UnitRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR)->every('is_approved', true);

                    $buttonText = "Kirim ke Risk Owner {$unitMrName}";

                    $params = ['status_dari' => UnitRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR, 'status_ke' => UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR];
                    $disabled = $allApproved ? '' : 'disabled';
                } else {
                    // Step 1: Kirim ke Risk Owner Divisi
                    $allRisksMonitored = $riskIds->isNotEmpty() && $riskIds->diff($latestMonitorings->pluck('identifikasi_risiko_id'))->isEmpty();
                    $hasItemsToSend = $latestMonitorings->where('status', UnitRiskMonitoring::STATUS_DRAFT_REVISI)->isNotEmpty();

                    if ($allRisksMonitored && $hasItemsToSend) {
                        $buttonText = "Kirim ke Risk Owner Divisi {$currentUserUnitName}";

                        $params = ['status_dari' => UnitRiskMonitoring::STATUS_DRAFT_REVISI, 'status_ke' => UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI];
                        $disabled = '';
                    }
                }
                break;

            case 2:
                if ($isUnitMr && $hasVerificationMr) {
                    // Step 4: Publish Monitoring
                    $allApproved = $latestMonitorings->where('status', UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR)->isNotEmpty() && $latestMonitorings->where('status', UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR)->every('is_approved', true);

                    $buttonText = 'Verifikasi Monitoring';

                    $params = ['status_dari' => UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR, 'status_ke' => UnitRiskMonitoring::STATUS_PUBLISHED, 'final' => true];
                    $disabled = $allApproved ? '' : 'disabled';
                } else {
                    // Step 2: Kirim ke Risk Officer Divisi MR
                    $allApproved = $latestMonitorings->where('status', UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI)->isNotEmpty() && $latestMonitorings->where('status', UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI)->every('is_approved', true);

                    $buttonText = "Kirim ke Risk Officer {$unitMrName}";

                    $params = ['status_dari' => UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI, 'status_ke' => UnitRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR];
                    $disabled = $allApproved ? '' : 'disabled';
                }
                break;
        }

        if ($buttonText) {
            return "<div>" . $this->buildEskalasiForm($buttonText, 'corporate-risk.monitorings.send.all', $period->id, $period->tahun, $params, $disabled, $targetUnitId) . "</div>";
        }
        return null;
    }

    private function buildEskalasiForm($buttonText, $routeName, $periodId, $tahunPeriode, $params, $disabled, $targetUnitId)
    {
        $route = route($routeName, ['period' => $periodId]);
        $csrf = csrf_token();
        $quarter = request()->input('filters.quarter', 1);
        $month = request()->input('filters.month', '1');
        $statusDari = $params['status_dari'];
        $statusKe = $params['status_ke'];
        $isFinal = $params['final'] ?? false;
        $uniqueId = "form-eskalasi-{$statusDari}";

        return <<<HTML
            <form id="{$uniqueId}" action="{$route}" method="POST" class="d-inline-block">
                <input type="hidden" name="_token" value="{$csrf}">
                <input type="hidden" name="quarter" value="{$quarter}">
                <input type="hidden" name="month" value="{$month}">
                <input type="hidden" name="status_dari" value="{$statusDari}">
                <input type="hidden" name="status_ke" value="{$statusKe}">
                <input type="hidden" name="is_final" value="{$isFinal}">
                <input type="hidden" name="unit_id" value="{$targetUnitId}">
                <button type="button" class="btn btn-info btn-arrow-right" onclick="submitEskalasiForm('{$uniqueId}', '{$buttonText}')" {$disabled}>{$buttonText}</button>
            </form>
        HTML;
    }

    private function getFilterScripts()
    {
        // Menggunakan Nowdoc (kutip tunggal) untuk keamanan
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

                        Swal.fire({ title: 'Memproses...', text: 'Mohon tunggu sebentar.', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                        $.ajax({
                            url: url, type: 'POST', data: data,
                            success: function(response) {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: response.message, })
                                .then(() => { $('.ajax-datatable').DataTable().ajax.reload(); form.find('button').prop('disabled', true); });
                            },
                            error: function(xhr) {
                                const errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                                Swal.fire({ icon: 'error', title: 'Gagal!', text: errorMsg, });
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
                    }
                }).change();
            });
            </script>
        HTML;
    }

    public function sendAllMonitoring(Request $request, Periode $period)
    {
        $validated = $request->validate([
            'unit_id' => 'required|integer|exists:units,id',
            'quarter' => 'required|integer',
            'month' => 'required|integer',
            'status_dari' => 'required|integer',
            'status_ke' => 'required|integer',
            'is_final' => 'nullable|boolean',
        ]);

        $riskIds = IdentifikasiRisiko::where('periode_id', $period->id)
            ->where('unit_id', $validated['unit_id'])
            ->pluck('id');

        $latestMonitoringIds = UnitRiskMonitoring::select(DB::raw('MAX(id) as last_id'))
            ->whereIn('identifikasi_risiko_id', $riskIds)
            ->where('quarter', $validated['quarter'])
            ->where('month', $validated['month'])
            ->groupBy('identifikasi_risiko_id')->pluck('last_id');

        if ($latestMonitoringIds->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data monitoring yang ditemukan untuk dikirim.'], 422);
        }

        $query = UnitRiskMonitoring::whereIn('id', $latestMonitoringIds)->where('status', $validated['status_dari']);

        if ($validated['status_dari'] > UnitRiskMonitoring::STATUS_DRAFT_REVISI) {
            $query->where('is_approved', true);
        }

        $updated = $query->update([
            'status' => $validated['status_ke'],
            'is_approved' => $validated['is_final'] ?? false,
        ]);

        return $updated
            ? response()->json(['success' => true, 'message' => 'Monitoring berhasil dieskalasi.'])
            : response()->json(['success' => false, 'message' => 'Tidak ada monitoring yang memenuhi syarat untuk dieskalasi.'], 422);
    }

    public function verifyMonitoring(Request $request, Periode $period, UnitRiskMonitoring $monitoring)
    {
        $validated = $request->validate([
            'status_verifikasi' => 'required|in:terima,tolak',
            'notes' => 'required_if:status_verifikasi,tolak|nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($validated, $monitoring) {
            if ($validated['status_verifikasi'] == 'terima') {
                $monitoring->update(['is_approved' => true]);
            } else {
                $monitoring->update([
                    'status' => UnitRiskMonitoring::STATUS_DRAFT_REVISI,
                    'is_approved' => false,
                    'is_revision' => true,
                ]);
            }

            RiskMonitoringNote::create([
                'risiko_id' => $monitoring->identifikasi_risiko_id,
                'type' => 1, // Menggunakan type 1 untuk Divisi
                'user_id' => Auth::id(),
                'status' => $validated['status_verifikasi'] == 'terima' ? 1 : 0,
                'notes' => $validated['notes'],
                'quarter' => $monitoring->quarter,
                'month' => $monitoring->month,
                'year' => null,
            ]);
        });

        return back()->with('success', 'Verifikasi berhasil disimpan.');
    }

    public function getNotes(Request $request, Periode $period, IdentifikasiRisiko $risk)
    {
        $validated = $request->validate([
            'quarter' => 'required|integer',
            'month'   => 'required|integer',
        ]);

        $notes = RiskMonitoringNote::where('risiko_id', $risk->id)
            ->where('type', 1) // Tipe 1 untuk monitoring Divisi
            ->where('quarter', $validated['quarter'])
            ->where('month', $validated['month'])
            ->with('user:id,name') // Ambil hanya id dan nama user
            ->latest()
            ->get();

        return response()->json($notes);
    }

    private function cleanRupiah($value) {
        return (float) str_replace(['Rp', '.', ','], ['', '', ''], $value);
    }
}
