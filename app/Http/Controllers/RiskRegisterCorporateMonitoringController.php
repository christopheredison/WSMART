<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\DataBatch;
use App\Models\IdentifikasiRisiko;
use App\Models\Notification;
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
use App\Models\User;
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
        $period = Periode::with('identifikasiRisikos.peristiwaRisiko')->findOrfail(request()->route('period'));
        $cb = fn ($fn) => $fn;

        $user = request()->user();
        $userLevel = Auth::user()->level_id;

        $quarter = request()->input('filters.quarter', request()->query('quarter', 1));

        $defaultMonth = match((int) $quarter) {
            2 => '4', 3 => '7', 4 => '10', default => '1'
        };
        $validMonths = match((int) $quarter) {
            1 => ['1', '2', '3'], 2 => ['4', '5', '6'], 3 => ['7', '8', '9'], 4 => ['10', '11', '12'], default => []
        };

        $filterMonth = request()->input('filters.month');
        $queryMonth = request()->query('month');
        $monthRaw = $filterMonth ?: ($queryMonth ?: $defaultMonth);
        $month = (string) $monthRaw;

        if (!in_array($month, $validMonths, true)) {
            $month = (string) ($validMonths[0] ?? '1');
        }

        $this->baseRouteParams = [
            'period' => request()->route('period'),
            'quarter' => $quarter,
            'month' => $month,
        ];

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
                    'unit',
                    'riskAnalysis.skalaDampakObj',
                    'riskAnalysis.skalaProbabilitas',
                    'riskAnalysis.skalaProbabilitasResidualQ' . $quarter,
                    'riskAnalysis.skalaDampakResidualQ' . $quarter . 'Obj',
                    'riskAnalysis.skalaProbabilitasResidual',
                    'penyebabRisikos.perlakuanPenyebabRisiko',
                    'perlakuanDampakRisikos',
                ])
                ->with(['lastMonitoringRisiko' => function ($query) use ($quarter, $month) {
                    $query->where('quarter', $quarter)
                        ->when($month, function ($q) use ($month) {
                            return $q->where('month', $month);
                        })
                        ->with(['skalaProbabilitas', 'skalaDampakObj'])
                        ->orderBy('id', 'desc');
                }])
                ->leftJoin('risk_analyses as ra', 'identifikasi_risikos.id', '=', 'ra.risiko_id')
                ->orderBy('identifikasi_risikos.is_closed', 'asc')
                ->orderByDesc('ra.skala_risiko')
                ->orderBy('identifikasi_risikos.id', 'asc')
                ->select('identifikasi_risikos.*');
        };

        $this->datatableCallback = function ($datatable) use ($quarter, $month, $period) {
            $tahun = (int) $period->tahun;
            $mon = (int) $month;
            $datatable->addColumn('is_closed_for_period', function ($row) use ($tahun, $mon) {
                    return $row->isClosedAsOf($tahun, $mon) ? 1 : 0;
                })
                ->addColumn('can_monitor', function ($row) use ($quarter) {
                    return $this->getMonitoringPrerequisiteError($row, $quarter) === null ? 1 : 0;
                })
                ->addColumn('nilai_dampak_residual', function ($row) use ($quarter) {
                    return $row->riskAnalysis?->{'nilai_dampak_residual_q' . $quarter};
                })->addColumn('skala_dampak_residual', function ($row) use ($quarter) {
                    return $row->riskAnalysis?->{'skala_dampak_residual_q' . $quarter};
                })->addColumn('skala_probabilitas_residual', function ($row) use ($quarter) {
                    return $row->riskAnalysis?->{'skalaProbabilitasResidualQ' . $quarter}?->tingkat;
                })->addColumn('skala_risiko_residual', function ($row) use ($quarter) {
                    return $row->riskAnalysis?->{'skala_risiko_residual_q' . $quarter};
                });
        };

        $tahunPeriode = $period->tahun;
        $verificatorMap = [
            UnitRiskMonitoring::STATUS_VERIFIKASI_RO_DIVISI_MR => 'Risk Officer MR',
            UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR => 'Risk Owner MR',
        ];

        $this->tableColumns = [
            'quarter' => [
                'label' => 'Periode Monitoring',
                'orderable' => false,
                'searchable' => false,
                'render' => <<<JS
                    function (data, type, row) {
                        const monthNames = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                        const m = row.last_monitoring_risiko;

                        const monthIdx = m?.month || $('#table-filter select[name="month"]').val();
                        const quarter = m?.quarter || $('#table-filter select[name="quarter"]').val();
                        const tahun = '$tahunPeriode';

                        const monthName = monthNames[parseInt(monthIdx)] || "";
                        return `\${monthName} (Q\${quarter}) - \${tahun}`;
                    }
                JS,
            ],
            'peristiwa_risiko' => [
                'label' => 'Peristiwa Risiko',
                'data' => 'peristiwa_risiko',
                'render' => '(data, type, row) => row.peristiwa_risiko || "-"',
                'class' => 'mw-10r',
            ],
            'deskripsi_peristiwa_risiko' => [
                'label' => 'Deskripsi',
                'data' => 'deskripsi_peristiwa_risiko',
                'sortable' => false,
                'class' => 'mw-20r',
            ],
            'nilai_dampak' => [
                'label' => 'Nilai Dampak Inheren',
                'data' => 'risk_analysis.nilai_dampak',
                'name' => 'ra.nilai_dampak',
                'class' => 'white-space-nowrap',
                'defaultContent' => '-',
                'sortable' => true,
                'render' => '(data, type, row) => "Rp " + new Intl.NumberFormat("id-ID").format(row.risk_analysis?.nilai_dampak || 0)',
            ],
            'skala_dampak' => [
                'label' => 'Skala Dampak Inheren',
                'data' => 'risk_analysis.skala_dampak',
                'name' => 'ra.skala_dampak',
                'defaultContent' => '-',
                'sortable' => true,
                'render' => '(data, type, row) => {
                    const analisa = row.risk_analysis;
                    return analisa?.skala_dampak ? `(${analisa.skala_dampak}) ${analisa.skala_dampak_obj?.deskripsi || ""}` : "-";
                }',
            ],
            'skala_probabilitas' => [
                'label' => 'Skala Probabilitas Inheren',
                'data' => 'risk_analysis.skala_probabilitas.tingkat',
                'name' => 'ra.skala_probabilitas_id',
                'defaultContent' => '-',
                'sortable' => false,
                'render' => '(data, type, row) => {
                    const prob = row.risk_analysis?.skala_probabilitas;
                    return prob ? `(${prob.tingkat}) ${prob.skala || ""}` : "-";
                }',
            ],
            'skala_risiko' => [
                'label' => 'Level Risiko Inheren',
                'data' => 'risk_analysis.skala_risiko',
                'name' => 'ra.skala_risiko',
                'defaultContent' => '-',
                'sortable' => true,
                'class' => 'text-center align-middle',
                'render' => '(data, type, row) => {
                    const analisa = row.risk_analysis;
                    return analisa?.skala_risiko ? (analisa.skala_risiko + " - " + analisa.level_risiko) : "-";
                }',
                'createdCell' => 'function (td, cellData, rowData, row, col) {
                    const level = rowData.risk_analysis?.level_risiko;
                    if (level) {
                        const colorClass = "bg-" + level.toLowerCase().replace(/to\s+/g, "").replace(/\s+/g, "-");
                        $(td).addClass(colorClass).addClass("text-white");
                    }
                }'
            ],
            'nilai_dampak_residual' => [
                'label' => 'Nilai Dampak Residual',
                'data' => null,
                'defaultContent' => '-',
                'sortable' => false,
                'searchable' => false,
                'class' => 'white-space-nowrap',
                'render' => '(data, type, row) => {
                    const q = $(\'select[name="quarter"]\').val() || 1;
                    const val = row.risk_analysis ? row.risk_analysis["nilai_dampak_residual_q" + q] : 0;
                    return "Rp " + new Intl.NumberFormat("id-ID").format(val || 0);
                }',
            ],
            'skala_dampak_residual' => [
                'label' => 'Skala Dampak Residual',
                'data' => null,
                'defaultContent' => '-',
                'sortable' => false,
                'render' => '(data, type, row) => {
                    const q = $(\'select[name="quarter"]\').val() || 1;
                    const analisa = row.risk_analysis;
                    if (!analisa) return "-";

                    const val = analisa["skala_dampak_residual_q" + q];
                    const obj = analisa["skala_dampak_residual_q" + q + "_obj"];
                    return val ? `(${val}) ${obj?.deskripsi || ""}` : "-";
                }',
            ],
            'skala_probabilitas_residual' => [
                'label' => 'Skala Probabilitas Residual',
                'data' => null,
                'defaultContent' => '-',
                'sortable' => false,
                'render' => '(data, type, row) => {
                    const q = $(\'select[name="quarter"]\').val() || 1;
                    const analisa = row.risk_analysis;
                    if (!analisa) return "-";

                    const prob = analisa["skala_probabilitas_residual_q" + q];
                    return prob ? `(${prob.tingkat}) ${prob.skala || ""}` : "-";
                }',
            ],
            'skala_risiko_residual' => [
                'label' => 'Level Risiko Residual',
                'data' => null,
                'defaultContent' => '-',
                'sortable' => false,
                'class' => 'text-center align-middle',
                'render' => '(data, type, row) => {
                    const q = $(\'select[name="quarter"]\').val() || 1;
                    const analisa = row.risk_analysis;
                    if (!analisa) return "-";

                    const val = analisa["skala_risiko_residual_q" + q];
                    const level = analisa["level_risiko_residual_q" + q];
                    return val ? (val + " - " + (level || "")) : "-";
                }',
                'createdCell' => 'function (td, cellData, rowData, row, col) {
                    const q = $(\'select[name="quarter"]\').val() || 1;
                    const level = rowData.risk_analysis?.["level_risiko_residual_q" + q];
                    if (level) {
                        const colorClass = "bg-" + level.toLowerCase().replace(/to\s+/g, "").replace(/\s+/g, "-");
                        $(td).addClass(colorClass).addClass("text-white");
                    }
                }'
            ],
            'nilai_dampak_monitoring' => [
                'label' => 'Nilai Dampak Realisasi',
                'data' => 'last_monitoring_risiko.nilai_dampak',
                'defaultContent' => '-',
                'sortable' => false,
                'render' => '(data, type, row) => (!!row.last_monitoring_risiko?.nilai_dampak ? "Rp " + new Intl.NumberFormat("id-ID").format(row.last_monitoring_risiko?.nilai_dampak) : "-")',
            ],
            'skala_dampak_monitoring' => [
                'label' => 'Skala Dampak Realisasi',
                'data' => 'last_monitoring_risiko.skala_dampak',
                'defaultContent' => '-',
                'sortable' => false,
                'render' => '(data, type, row) => {
                    const m = row.last_monitoring_risiko;
                    return m?.skala_dampak ? `(${m.skala_dampak}) ${m.skala_dampak_obj?.deskripsi || ""}` : "-";
                }',
            ],
            'skala_probabilitas_monitoring' => [
                'label' => 'Skala Probabilitas Realisasi',
                'data' => 'last_monitoring_risiko.skala_probabilitas.tingkat',
                'defaultContent' => '-',
                'sortable' => false,
                'render' => '(data, type, row) => {
                    const p = row.last_monitoring_risiko?.skala_probabilitas;
                    return p ? `(${p.tingkat}) ${p.skala || ""}` : "-";
                }',
            ],
            'skala_risiko_monitoring' => [
                'label' => 'Level Risiko Realisasi',
                'data' => 'last_monitoring_risiko.skala_risiko',
                'defaultContent' => '-',
                'sortable' => false,
                'class' => 'text-center align-middle',
                'render' => '(data, type, row) => {
                    const m = row.last_monitoring_risiko;
                    return m?.skala_risiko ? (m.skala_risiko + " - " + m.level_risiko) : "-";
                }',
                'createdCell' => 'function (td, cellData, rowData, row, col) {
                    const level = rowData.last_monitoring_risiko?.level_risiko;
                    if (level) {
                        const colorClass = "bg-" + level.toLowerCase().replace(/to\s+/g, "").replace(/\s+/g, "-");
                        $(td).addClass(colorClass).addClass("text-white");
                    }
                }'
            ],
            'is_closed' => [
                'label' => 'Status Risiko',
                'data' => 'is_closed_for_period',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => {
                    const isClosed = row.is_closed_for_period == 1 || row.is_closed_for_period === true;
                    if (!isClosed) {
                        return `<div class="badge bg-success rounded-pill px-2">Open</div>`;
                    }
                    const closedDate = row.closed_at_formatted || "";
                    const dateLabel = closedDate ? ` · ${closedDate}` : "";
                    return `<div class="badge bg-danger rounded-pill px-2" title="${closedDate ? "Ditutup pada " + closedDate : "Closed"}">Closed${dateLabel}</div>`;
                }',
            ],
            'status_monitoring' => [
                'label' => 'Status Approval',
                'render' => '(data, type, row) => {
                    if (row.is_closed_for_period == 1 || row.is_closed_for_period === true) return `<div class="badge text-danger bg-danger-subtle">Dihentikan</div>`;
                    if (!row.last_monitoring_risiko) return `<div class="badge bg-light text-dark border">Belum Dimonitor</div>`;

                    const m = row.last_monitoring_risiko;
                    const status = parseInt(m.status);
                    const isRevision = parseInt(m.is_revision) || 0;
                    const isApproved = m.is_approved;
                    const map = ' . json_encode($verificatorMap) . ';

                    if (isRevision > 0 && !isApproved) {
                        let source = "Risk Owner MR";
                        if (isRevision === 3) source = "Risk Officer MR";
                        if (isRevision === 4) source = "Risk Owner MR";

                        return `<div class="badge bg-danger"><i class="bx bx-undo me-1"></i>Ditolak ${source}</div>`;
                    }

                    if (status === 1) return `<div class="badge bg-warning text-dark">Draft</div>`;

                    if (map[status]) {
                        const name = map[status];
                        if (isApproved) {
                            return `<div class="badge bg-info"><i class="bx bx-check-circle me-1"></i>Terverifikasi ${name}</div>`;
                        } else {
                            return `<div class="badge border border-info text-info bg-white"><i class="bx bx-time-five me-1"></i>Menunggu Verifikasi ${name}</div>`;
                        }
                    }

                    if (status === 100) return `<div class="badge bg-success">Selesai</div>`;

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
        // Corporate flow: Risk Officer MR (level 1 + unit_mr) yang input monitoring.
        // Jangan bandingkan unit_mr milik unit korporat (sering 0) dengan unit user MR.
        if (Gate::check('risk_monitoring_input') && (int) $userLevel === 1 && $isUserUnitMr) {
            $monitoringRoute = route('corporate-risk.monitorings.edit', ['period' => request()->route('period'), 'monitoring' => ':id', 'quarter' => ':quarter', 'month' => ':month']);
            $this->tableActions[] = [
                'label' => 'Monitoring',
                'btn_icon' => false,
                'action' => 'script',
                'script' => <<<JS
                    window.location.href = "$monitoringRoute".replace(':id', $(this).data('id')).replace('%3Aquarter', $('#table-filter select[name="quarter"]').val()).replace('%3Amonth', $('#table-filter select[name="month"]').val());
                JS,
                'active_state' => '(data, type, row) => row.can_monitor == 1 && !(row.is_closed_for_period == 1 || row.is_closed_for_period === true) && (!row.last_monitoring_risiko || row.last_monitoring_risiko.status == '.UnitRiskMonitoring::STATUS_DRAFT_REVISI.')'
            ];

            $this->tableActions[] = [
                'label' => 'Change to LED',
                'btn_icon' => false,
                'action' => 'change_to_led_unit',
                'active_state' => '(data, type, row) => !(row.is_closed_for_period == 1 || row.is_closed_for_period === true)',
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
        $verificatorLevels = [2];
        if (in_array($user->level_id, $verificatorLevels) && $isUserUnitMr) {
            $this->tableActions[] = [
                'label' => 'Verifikasi',
                'btn_class' => 'btn-warning btn-sm',
                'action' => 'script',
                'script' => "showVerifikasiModal(__MONITORING_ID__, '__RISK_TITLE__', '__RISK_DESC__')",
                'active_state' => '(data, type, row) => {
                    if (row.is_closed_for_period == 1 || row.is_closed_for_period === true) return false;

                    const monitoring = row.last_monitoring_risiko;
                    if (!monitoring || monitoring.is_approved) return false;

                    const userLevel = ' . $user->level_id . ';
                    const isUserUnitMr = ' . ($isUserUnitMr ? 'true' : 'false') . ';
                    const status = monitoring.status;

                    // Corporate: Risk Owner MR verifies status 4
                    if (userLevel == 2 && isUserUnitMr && status == '.UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR.') return true;

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

        // --- SUMMARY INFO & ESCALATION (Corporate MR flow) ---
        $allRisks = IdentifikasiRisiko::where('periode_id', $period->id)
            ->where('unit_id', $targetUnitId)
            ->where('is_closed', 0)
            ->get();

        $riskIds = $allRisks->pluck('id');

        $latestMonitoringIds = UnitRiskMonitoring::query()
            ->select(DB::raw('MAX(id) as id'))
            ->whereIn('identifikasi_risiko_id', $riskIds)
            ->where('quarter', $quarter)
            ->where('month', $month)
            ->groupBy('identifikasi_risiko_id')
            ->pluck('id');

        $activeMonitorings = UnitRiskMonitoring::whereIn('id', $latestMonitoringIds)
            ->get()
            ->keyBy('identifikasi_risiko_id');

        $resolvedStep = DataBatch::resolveCorporateMonitoringStep((int) $userLevel, true);
        $selectedStatus = $resolvedStep['status'] ?? null;
        $stepConfig = $resolvedStep['config'] ?? null;

        $summaryInfo = null;
        $escalationConfig = [
            'show' => false,
            'label' => 'Proses',
            'disabled' => true,
            'parameters' => ['quarter' => $quarter, 'month' => $month, 'unit_id' => $targetUnitId],
        ];

        if ($selectedStatus && $stepConfig) {
            $risksInMyStep = $allRisks->filter(function ($risk) use ($selectedStatus, $activeMonitorings) {
                $m = $activeMonitorings[$risk->id] ?? null;
                if ($selectedStatus == UnitRiskMonitoring::STATUS_DRAFT_REVISI) {
                    return !$m || $m->status == UnitRiskMonitoring::STATUS_DRAFT_REVISI;
                }
                return $m && $m->status == $selectedStatus;
            });

            if ($risksInMyStep->isNotEmpty()) {
                $escalationConfig['route'] = route('corporate-risk.monitorings.send.all', ['period' => $period->id]);

                if ($selectedStatus == UnitRiskMonitoring::STATUS_DRAFT_REVISI) {
                    $unstartedCount = $risksInMyStep->filter(fn($r) => !isset($activeMonitorings[$r->id]))->count();
                    $revisionCount = $risksInMyStep->filter(fn($r) => isset($activeMonitorings[$r->id]) && $activeMonitorings[$r->id]->status == UnitRiskMonitoring::STATUS_DRAFT_REVISI && $activeMonitorings[$r->id]->is_revision > 0)->count();
                    $targetLabel = $stepConfig['next_label'] ?? 'Kirim ke Risk Owner MR';

                    if ($revisionCount > 0) {
                        $summaryInfo = [
                            'type' => 'danger',
                            'icon' => 'bx-undo',
                            'message' => "Terdapat <strong>{$revisionCount}</strong> monitoring risiko yang <strong>dikembalikan (revisi)</strong>. Mohon perbaiki data.",
                        ];
                        $escalationConfig['show'] = true;
                        $escalationConfig['disabled'] = false;
                        $escalationConfig['label'] = 'Kirim Perbaikan';
                    } elseif ($unstartedCount > 0) {
                        $summaryInfo = [
                            'type' => 'warning',
                            'icon' => 'bx-info-circle',
                            'message' => "Terdapat <strong>{$unstartedCount}</strong> risiko aktif belum di-monitoring.",
                        ];
                        $escalationConfig['show'] = true;
                        $escalationConfig['disabled'] = true;
                        $escalationConfig['label'] = $targetLabel;
                    } else {
                        $summaryInfo = [
                            'type' => 'success',
                            'icon' => 'bx-check-double',
                            'message' => "Seluruh monitoring siap. Silahkan klik tombol <strong>{$targetLabel}</strong> untuk melanjutkan.",
                        ];
                        $escalationConfig['show'] = true;
                        $escalationConfig['disabled'] = false;
                        $escalationConfig['label'] = $targetLabel;
                    }
                } else {
                    $nextLabel = $stepConfig['next_label'] ?? 'Tetapkan Monitoring';
                    $escalationConfig['label'] = $nextLabel;

                    $unapprovedCount = $risksInMyStep->filter(fn($r) => !$activeMonitorings[$r->id]->is_approved)->count();
                    $returnedCount = $risksInMyStep->filter(fn($r) => $activeMonitorings[$r->id]->is_revision > 0)->count();

                    if ($unapprovedCount > 0) {
                        $escalationConfig['show'] = true;
                        $escalationConfig['disabled'] = true;

                        if ($returnedCount > 0) {
                            $summaryInfo = [
                                'type' => 'danger',
                                'icon' => 'bx-undo',
                                'message' => "Terdapat <strong>{$returnedCount}</strong> monitoring yang <strong>dikembalikan. Mohon verifikasi ulang.",
                            ];
                        } else {
                            $summaryInfo = [
                                'type' => 'warning',
                                'icon' => 'bxs-error-circle',
                                'message' => "Terdapat <strong>{$unapprovedCount}</strong> monitoring aktif menunggu verifikasi Anda.",
                            ];
                        }
                    } else {
                        $summaryInfo = [
                            'type' => 'success',
                            'icon' => 'bx-check-double',
                            'message' => "Seluruh monitoring telah diverifikasi. Silahkan klik tombol <strong>{$nextLabel}</strong> untuk melanjutkan.",
                        ];
                        $escalationConfig['show'] = true;
                        $escalationConfig['disabled'] = false;
                    }
                }
            }
        }

        $this->extraViewData['summaryInfo'] = $summaryInfo;
        $this->extraViewData['escalationConfig'] = $escalationConfig;
        $this->extraViewData['showBulkCheckbox'] = true;
        $this->extraViewData['isProjectMonitoringPage'] = true;
        $this->extraViewData['currentUserLevel'] = $userLevel;
        $this->extraViewData['hasVerificationMr'] = $hasVerificationMr;
        $this->extraViewData['isUserUnitMr'] = $isUserUnitMr ? 1 : 0;
        $this->extraViewData['showVerifikasiModal'] = in_array($userLevel, $verificatorLevels) && $isUserUnitMr;
        $this->extraViewData['showCatatanModal'] = true;

        $bulkRoute = route('corporate-risk.monitorings.bulk-verify', ['period' => $period->id]);
        $csrfToken = csrf_token();

        $existingDatatableCallback = $this->datatableCallback;
        $this->datatableCallback = function ($datatable) use ($existingDatatableCallback, $summaryInfo, $escalationConfig) {
            if (is_callable($existingDatatableCallback)) {
                $existingDatatableCallback($datatable);
            }
            $datatable->with('summaryInfo', $summaryInfo);
            $datatable->with('escalationConfig', $escalationConfig);
        };

        $this->extraScripts[] = <<<SCRIPT
        <script>
        $(document).on('xhr.dt', function (e, settings, json, xhr) {
            if (json && 'summaryInfo' in json) {
                let container = $('.card-body > .d-flex.align-items-center.justify-content-end.gap-3').first();
                container.empty();

                if (json.summaryInfo) {
                    let s = json.summaryInfo;
                    container.append(`
                        <div class="alert alert-\${s.type} alert-dismissible fade show d-flex align-items-center mt-0 mb-3 flex-grow-1" role="alert">
                            <div class="bg-\${s.type} text-white rounded-circle p-0 me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="bx \${s.icon} text-white fs-4"></i>
                            </div>
                            <div class="flex-grow-1 pe-4">
                                \${s.message}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                }

                if (json.escalationConfig && json.escalationConfig.show) {
                    let e = json.escalationConfig;
                    let disabledAttr = e.disabled ? 'disabled' : '';
                    let btnClass = e.label.toLowerCase().includes('publish') || e.label.toLowerCase().includes('tetapkan') ? 'btn-success' : 'btn-info';

                    let paramsHtml = '';
                    if (e.parameters) {
                        for (const [key, value] of Object.entries(e.parameters)) {
                            paramsHtml += `<input type="hidden" name="\${key}" value="\${value}">`;
                        }
                    }

                    container.append(`
                        <form id="form-eskalasi-action" action="\${e.route}" method="POST" class="d-inline-block">
                            <input type="hidden" name="_token" value="{$csrfToken}">
                            \${paramsHtml}
                            <button type="button"
                                class="btn mb-2 \${btnClass} btn-arrow-right"
                                onclick="submitEskalasiForm('form-eskalasi-action', '\${e.label}')"
                                \${disabledAttr}>
                                \${e.label}
                            </button>
                        </form>
                    `);
                }
            }
        });

        let currentIds = [];

        $(document).on('change', '#check-all-risiko', function() {
            $('.row-checkbox:not(:disabled)').prop('checked', this.checked);
            toggleBulkButton();
        });

        $(document).on('change', '.row-checkbox', function() {
            toggleBulkButton();
            if(!this.checked) {
                $('#check-all-risiko').prop('checked', false);
            }
        });

        function toggleBulkButton() {
            const checkedCount = $('.row-checkbox:checked').length;
            if (checkedCount > 0) {
                $('#bulk-verify-container').removeClass('d-none');
                $('#count-checked').text(checkedCount);
            } else {
                $('#bulk-verify-container').addClass('d-none');
            }
        }

        function handleBulkVerifikasiClick() {
            currentIds = [];
            $('.row-checkbox:checked').each(function() {
                currentIds.push($(this).val());
            });

            if (currentIds.length === 0) return;

            $('#catatan-verifikasi').val('');

            if($('#modal-bulk-info').length == 0) {
                $('#modalVerifikasi .modal-body').prepend(`
                    <div id="modal-bulk-info" class="alert alert-info mt-0 mb-3">
                        <i class="bx bx-info-circle"></i> Memverifikasi <strong>\${currentIds.length}</strong> data terpilih.
                    </div>
                `);
            } else {
                $('#modal-bulk-info strong').text(currentIds.length);
            }

            const modalEl = document.getElementById('modalVerifikasi');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            let btnTerima = $('#btn-terima-verifikasi');
            let btnTolak = $('#btn-tolak-verifikasi');
            btnTerima.replaceWith(btnTerima.clone());
            btnTolak.replaceWith(btnTolak.clone());

            $('#btn-terima-verifikasi').on('click', function() { submitBulk('terima'); });
            $('#btn-tolak-verifikasi').on('click', function() { submitBulk('tolak'); });
        }

        function submitBulk(status) {
            const catatan = $('#catatan-verifikasi').val().trim();

            if (status === 'tolak' && !catatan) {
                Swal.fire('Peringatan', 'Catatan verifikasi wajib diisi jika menolak.', 'warning');
                return;
            }

            Swal.fire({
                title: status === 'terima' ? 'Terima Monitoring Terpilih?' : 'Kembalikan Monitoring Terpilih?',
                text: `Anda akan memproses \${currentIds.length} data.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{$bulkRoute}',
                        type: 'POST',
                        data: {
                            _token: '{$csrfToken}',
                            ids: currentIds,
                            status_verifikasi: status,
                            catatan_verifikasi: catatan
                        },
                        beforeSend: function() {
                            Swal.fire({ title: 'Sedang memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                        },
                        success: function(res) {
                            Swal.fire('Berhasil', res.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem';
                            Swal.fire('Gagal', msg, 'error');
                        }
                    });
                }
            });
        }
        </script>
        SCRIPT;

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

        $monthOptions = match((int) $quarter) {
            1 => ['1' => 'Januari', '2' => 'Februari', '3' => 'Maret'],
            2 => ['4' => 'April', '5' => 'Mei', '6' => 'Juni'],
            3 => ['7' => 'Juli', '8' => 'Agustus', '9' => 'September'],
            4 => ['10' => 'Oktober', '11' => 'November', '12' => 'Desember'],
            default => []
        };

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
                $quarter,
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
                $monthOptions,
                $month,
                [
                    'class' => 'form-select select2 js-select-hide-search',
                ]
            ],
            'handler' => function ($query, $key, $value) {
                // handled outside
            },
        ];

        $this->availableFilters = $filters;

        $this->extraScripts[] = $this->getFilterScripts($quarter, $month);

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
            'penyebabRisikos.perlakuanPenyebabRisiko' => function ($query) use ($quarter, $month) {
                $query->with([
                    'opsiPerlakuan',
                    'picJabatan',
                    'lastMonitoring' => function ($q) use ($quarter, $month) {
                        $q->whereHas('unitRiskMonitoring', function ($sq) use ($quarter, $month) {
                            $sq->where('quarter', $quarter);
                            if ($month) $sq->where('month', $month);
                        });
                    },
                    'documents',
                    'perlakuanPenyebabMonitorings',
                ]);
            },
            // Load Perlakuan Dampak
            'perlakuanDampakRisikos' => function ($query) use ($quarter, $month) {
                $query->with([
                    'picJabatan',
                    'dampakRisikoUnit',
                    'opsiPerlakuan',
                    'lastMonitoring' => function ($q) use ($quarter, $month) {
                        $q->whereHas('unitRiskMonitoring', function ($sq) use ($quarter, $month) {
                            $sq->where('quarter', $quarter);
                            if ($month) $sq->where('month', $month);
                        });
                    }
                ]);
            },
            'dampakRisikos.perlakuanDampakRisikos' => function ($query) use ($quarter, $month) {
                $query->with([
                    'picJabatan',
                    'opsiPerlakuan',
                    'lastMonitoring' => function ($q) use ($quarter, $month) {
                        $q->whereHas('unitRiskMonitoring', function ($sq) use ($quarter, $month) {
                            $sq->where('quarter', $quarter);
                            if ($month) $sq->where('month', $month);
                        });
                    }
                ]);
            },
            'kris',
            'taksonomiRisiko',
            'parameterRisikos',
        ]);

        $prerequisiteError = $this->getMonitoringPrerequisiteError($risk, $quarter);
        if ($prerequisiteError) {
            return redirect()->route('corporate-risk.monitorings.index', [
                'period' => $period->id,
                'quarter' => $quarter,
                'month' => $month,
            ])->with('error', $prerequisiteError);
        }

        $unit = $risk->unit;
        $periode = $risk->periode;
        $currentYear = $period->tahun;

        $currentDate = \Carbon\Carbon::create($currentYear, $month ?: 1, 1);
        $dateM1 = $currentDate->copy()->subMonth();
        $dateM2 = $currentDate->copy()->subMonths(2);

        $monitoringM1 = $risk->monitoringRisikos()
            ->where('month', $dateM1->month)
            ->with(['perlakuanPenyebabMonitorings', 'perlakuanDampakMonitorings', 'kriUnitMonitorings'])
            ->latest('id')
            ->first();
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

        $riskLimitPeriode = RiskLimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
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

        $opportunities = \App\Models\Opportunity::where('identifikasi_risiko_id', $risk->id)
            ->orderBy('id', 'desc')
            ->get();

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
            'opportunities' => $opportunities
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
            ->with('peristiwaRisiko', 'penyebabRisikos', 'penyebabRisikos.perlakuanPenyebabRisiko', 'perlakuanDampakRisikos', 'kris', 'riskAnalysis')
            ->findOrFail(request()->route('monitoring'));

        $prerequisiteError = $this->getMonitoringPrerequisiteError($risk, $quarter);
        if ($prerequisiteError) {
            return response()->json([
                'message' => $prerequisiteError,
            ], 422);
        }

        $toCreate = [
            'quarter' => $quarter,
            'month' => $month,
            'tahun' => request()->input('tahun') ?: date('Y'),
            'nilai_dampak' => str_replace(['Rp', '.', ' '], '', ($request->realisasi_nilai_dampak ?: 0)),
            'skala_dampak' => $request->input('realisasi_skala_dampak') ?: $request->input('realisasi_skala_dampak_hidden'),
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

        $latestMonitoringSnapshot = $risk->monitoringRisikos()
            ->where('quarter', $quarter)
            ->where('month', $month)
            ->with(['kriUnitMonitorings'])
            ->latest('id')
            ->first();

        $latestKriMonitorings = $latestMonitoringSnapshot
            ? $latestMonitoringSnapshot->kriUnitMonitorings->keyBy('key_risk_indicator_id')
            : collect();

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
                    $storeFile = $documentFile->store('corporate-risk-monitoring-documents', 'public');
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

        $kriProjectRequests = json_decode($request->kri_projects, true) ?: [];
        foreach ($risk->kris as $kri) {
            $kriProjectRequest = $kriProjectRequests[$kri->id] ?? [];
            $latestKriMonitoring = $latestKriMonitorings->get($kri->id);

            $statusKriVal = $kriProjectRequest['status_kri_terkini_q' . $quarter]
                ?? $latestKriMonitoring?->status_kri_terkini
                ?? null;

            $nilaiKriVal = $kriProjectRequest['nilai_kri_terkini_q' . $quarter]
                ?? $latestKriMonitoring?->nilai_kri_terkini
                ?? null;

            $projectMonitoring->kriUnitMonitorings()->create([
                'key_risk_indicator_id' => $kri->id,
                'status_kri_terkini' => $statusKriVal,
                'nilai_kri_terkini' => $nilaiKriVal,
            ]);
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
            $wasClosed = (bool) $risk->is_closed;
            $updateData = ['is_closed' => true];
            if (!$wasClosed) {
                // Penutupan mengikuti bulan monitoring, bukan tanggal hari ini.
                $mon = max(1, min(12, (int) ($month ?: now()->month)));
                $updateData['closed_at'] = \Carbon\Carbon::create(
                    (int) ($period->tahun ?: now()->year), $mon, 1
                )->endOfMonth();
            }
            $risk->update($updateData);

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
            ->openAsOf((int) $period->tahun, (int) $month)
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

    private function getFilterScripts($quarter, $month)
    {
        $phpQuarter = $quarter;
        $phpMonth = $month;

        return <<<JS
            <script>
            function submitEskalasiForm(formId, actionText) {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: `Apakah Anda yakin ingin melakukan "\${actionText}"?`,
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
                const allMonths = {
                    '1': {'1': 'Januari', '2': 'Februari', '3': 'Maret'},
                    '2': {'4': 'April', '5': 'Mei', '6': 'Juni'},
                    '3': {'7': 'Juli', '8': 'Agustus', '9': 'September'},
                    '4': {'10': 'Oktober', '11': 'November', '12': 'Desember'},
                };

                function updateMonthDropdown(quarter, selectedMonth = null, triggerReload = false) {
                    const monthSelect = $('select[name="month"]');
                    monthSelect.empty();
                    if (quarter && allMonths[quarter]) {
                        $.each(allMonths[quarter], function(key, value) {
                            const isSelected = (String(key) === String(selectedMonth)) ? 'selected' : '';
                            monthSelect.append(`<option value="\${key}" \${isSelected}>\${value}</option>`);
                        });
                    } else {
                        monthSelect.append('<option value="">Pilih Quarter</option>');
                    }
                    if (monthSelect.hasClass('select2-hidden-accessible')) {
                        monthSelect.trigger('change.select2');
                    }

                    if (triggerReload) {
                        monthSelect.trigger('change');
                    }
                }

                const activeQuarter = "$phpQuarter";
                const activeMonth = "$phpMonth";

                $('select[name="quarter"]').val(activeQuarter);
                if ($('select[name="quarter"]').hasClass('select2-hidden-accessible')) {
                    $('select[name="quarter"]').trigger('change.select2');
                }

                updateMonthDropdown(activeQuarter, activeMonth, false);

                $('select[name="quarter"]').on('change', function() {
                    const newQuarter = $(this).val();
                    let firstMonth = null;
                    if (allMonths[newQuarter]) firstMonth = Object.keys(allMonths[newQuarter])[0];
                    updateMonthDropdown(newQuarter, firstMonth, true);
                });
            });
            </script>
        JS;
    }

    public function sendAllMonitoring(Request $request, Periode $period)
    {
        $validated = $request->validate([
            'unit_id' => 'required|integer|exists:units,id',
            'quarter' => 'required|integer',
            'month' => 'required|integer',
        ]);

        $user = Auth::user();
        $unit = Unit::find($validated['unit_id']);
        $isMr = true;

        $resolved = DataBatch::resolveCorporateMonitoringStep((int) $user->level_id, $isMr);
        if (!$resolved) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki otoritas.'], 403);
        }

        $currentStatus = (int) $resolved['status'];
        $stepConfig = $resolved['config'];
        $targetStatus = $stepConfig['next_status'] ?? null;
        $isFinal = (int) $targetStatus === (int) ($resolved['final_status'] ?? UnitRiskMonitoring::STATUS_PUBLISHED);

        if (!$targetStatus) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi escalation tidak valid.'], 422);
        }

        $riskIds = IdentifikasiRisiko::where('periode_id', $period->id)
            ->where('unit_id', $validated['unit_id'])
            ->openAsOf((int) $period->tahun, (int) $validated['month'])
            ->pluck('id');

        $subQuery = UnitRiskMonitoring::select(DB::raw('MAX(id) as last_id'))
            ->whereIn('identifikasi_risiko_id', $riskIds)
            ->where('quarter', $validated['quarter'])
            ->where('month', $validated['month'])
            ->groupBy('identifikasi_risiko_id');

        $latestMonitorings = UnitRiskMonitoring::whereIn('id', $subQuery)
            ->where('status', $currentStatus)
            ->get();

        if ($latestMonitorings->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada monitoring yang siap dikirim.'], 422);
        }

        // Verifikator (status > draft) wajib sudah approve semua
        if ($currentStatus > UnitRiskMonitoring::STATUS_DRAFT_REVISI) {
            $unapprovedCount = $latestMonitorings->where('is_approved', false)->count();
            if ($unapprovedCount > 0) {
                return response()->json(['success' => false, 'message' => "Terdapat {$unapprovedCount} risiko belum diverifikasi."], 422);
            }
        }

        DB::beginTransaction();
        try {
            UnitRiskMonitoring::whereIn('id', $latestMonitorings->pluck('id'))->update([
                'status' => $targetStatus,
                'is_approved' => $isFinal,
                'is_revision' => 0,
            ]);

            $this->upsertMonitoringDataBatch(
                $period,
                $validated['unit_id'],
                $validated['quarter'],
                $validated['month'],
                $targetStatus,
                $isFinal
            );

            DB::commit();

            $targetLink = route('corporate-risk.monitorings.index', [
                'period' => $period->id,
                'filters' => [
                    'quarter' => $validated['quarter'],
                    'month' => $validated['month'],
                ],
            ]);

            if ($isFinal) {
                $msg = 'Monitoring Risiko Korporat telah disetujui penuh & berhasil dipublish.';
                $this->sendNotificationCustom('RO_MR', $unit->id, 'Monitoring Dipublish', $msg, $targetLink, 'bx bx-check-shield');
                $this->sendNotificationCustom('RW_MR', $unit->id, 'Monitoring Dipublish', $msg, $targetLink, 'bx bx-check-shield');
            } else {
                $targetNotif = $stepConfig['role'] === 'RO_MR' ? 'RW_MR' : null;
                if ($currentStatus === UnitRiskMonitoring::STATUS_DRAFT_REVISI) {
                    $targetNotif = 'RW_MR';
                }

                if ($targetNotif) {
                    $msg = 'Terdapat pengajuan monitoring risiko korporat yang butuh verifikasi Anda.';
                    $this->sendNotificationCustom($targetNotif, $unit->id, 'Menunggu Verifikasi Monitoring', $msg, $targetLink, 'bx bx-bell');
                }
            }

            return response()->json(['success' => true, 'message' => 'Berhasil mengirim monitoring.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal sistem: ' . $e->getMessage()], 500);
        }
    }

    public function verifyMonitoring(Request $request, Periode $period, UnitRiskMonitoring $monitoring)
    {
        $validated = $request->validate([
            'status_verifikasi' => 'required|in:terima,tolak',
            'notes' => 'required_if:status_verifikasi,tolak|nullable|string|max:2000',
        ]);

        $currentStatus = $monitoring->status;
        $targetLink = route('corporate-risk.monitorings.index', ['period' => $period->id]);

        DB::transaction(function () use ($validated, $monitoring, $currentStatus) {
            if ($validated['status_verifikasi'] == 'terima') {
                $monitoring->update(['is_approved' => true]);
            } else {
                $targetStatus = UnitRiskMonitoring::getReturnStatus($monitoring->status, true);
                $monitoring->update([
                    'status' => $targetStatus,
                    'is_approved' => false,
                    'is_revision' => $currentStatus,
                ]);
            }

            RiskMonitoringNote::create([
                'risiko_id' => $monitoring->identifikasi_risiko_id,
                'type' => 1,
                'user_id' => Auth::id(),
                'status' => $validated['status_verifikasi'] == 'terima' ? 1 : 2,
                'notes' => $validated['notes'],
                'quarter' => $monitoring->quarter,
                'month' => $monitoring->month,
                'year' => null,
            ]);
        });

        if ($validated['status_verifikasi'] == 'tolak') {
            $unitId = $monitoring->identifikasiRisiko->unit_id ?? null;
            $msg = 'Monitoring Risiko Korporat ditolak dan dikembalikan untuk revisi. Catatan: ' . $validated['notes'];
            $this->sendNotificationCustom('RO_MR', $unitId, 'Monitoring Ditolak', $msg, $targetLink, 'bx bx-x-circle');
        }

        return back()->with('success', 'Verifikasi berhasil disimpan.');
    }

    public function bulkVerifyMonitoring(Request $request, Periode $period)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:unit_risk_monitorings,id',
            'status_verifikasi' => 'required|in:terima,tolak',
            'catatan_verifikasi' => 'required_if:status_verifikasi,tolak|nullable|string|max:2000',
        ]);

        $user = Auth::user();
        $count = 0;

        $firstMonitoring = UnitRiskMonitoring::with('identifikasiRisiko.unit')->find($validated['ids'][0]);
        $unitId = $firstMonitoring?->identifikasiRisiko?->unit_id;
        $quarter = $firstMonitoring?->quarter ?? 1;
        $month = $firstMonitoring?->month ?? 1;

        DB::beginTransaction();
        try {
            $monitorings = UnitRiskMonitoring::whereIn('id', $validated['ids'])->get();

            foreach ($monitorings as $monitoring) {
                if ($validated['status_verifikasi'] == 'terima') {
                    $monitoring->update(['is_approved' => true]);
                } else {
                    $currentStatus = $monitoring->status;
                    $targetStatus = UnitRiskMonitoring::getReturnStatus($currentStatus, true);
                    $monitoring->update([
                        'status' => $targetStatus,
                        'is_approved' => false,
                        'is_revision' => $currentStatus,
                    ]);
                }

                RiskMonitoringNote::create([
                    'risiko_id' => $monitoring->identifikasi_risiko_id,
                    'type' => 1,
                    'user_id' => $user->id,
                    'status' => $validated['status_verifikasi'] == 'terima' ? 1 : 2,
                    'notes' => $validated['catatan_verifikasi'],
                    'quarter' => $monitoring->quarter,
                    'month' => $monitoring->month,
                    'year' => null,
                ]);

                $count++;
            }

            DB::commit();

            $targetLink = route('corporate-risk.monitorings.index', [
                'period' => $period->id,
                'filters' => [
                    'quarter' => $quarter,
                    'month' => $month,
                ],
            ]);

            if ($validated['status_verifikasi'] == 'tolak') {
                $msg = $count . ' Monitoring Risiko Korporat ditolak secara masal. Catatan: ' . $validated['catatan_verifikasi'];
                $this->sendNotificationCustom('RO_MR', $unitId, 'Monitoring Masal Ditolak', $msg, $targetLink, 'bx bx-x-circle');
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memverifikasi ' . $count . ' data monitoring.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getNotes(Request $request, Periode $period, IdentifikasiRisiko $risk)
    {
        $validated = $request->validate([
            'quarter' => 'required|integer',
            'month'   => 'required|integer',
        ]);

        $notes = RiskMonitoringNote::where('risiko_id', $risk->id)
            ->where('type', 1)
            ->where('quarter', $validated['quarter'])
            ->where('month', $validated['month'])
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json($notes);
    }

    private function upsertMonitoringDataBatch(Periode $period, int $unitId, int $quarter, int $month, int $stepVerification, bool $isFinal = false): void
    {
        $batchKey = ($quarter * 100) + $month;

        $dataBatch = DataBatch::where('periode_id', $period->id)
            ->where('unit_id', $unitId)
            ->where('type', DataBatch::TYPE_RISK_MONITORING)
            ->where('batch', $batchKey)
            ->first();

        $payload = [
            'status' => $isFinal ? DataBatch::STATUS_FINISH : DataBatch::STATUS_VERIFIKASI,
            'step_verification' => $stepVerification,
            'finish' => $isFinal,
        ];

        if ($dataBatch) {
            $dataBatch->update($payload);
            return;
        }

        DataBatch::create(array_merge($payload, [
            'periode_id' => $period->id,
            'unit_id' => $unitId,
            'type' => DataBatch::TYPE_RISK_MONITORING,
            'batch' => $batchKey,
        ]));
    }

    private function sendNotificationCustom($target, $unitId, $title, $message, $link, $icon)
    {
        $users = collect();

        if ($target === 'RO_MR') {
            $users = User::permission('mr_notification_division')
                ->where('level_id', 1)
                ->whereHas('unit', function ($q) {
                    $q->where('unit_mr', 1);
                })->get();
        } elseif ($target === 'RW_MR') {
            $users = User::permission('mr_notification_division')
                ->where('level_id', 2)
                ->whereHas('unit', function ($q) {
                    $q->where('unit_mr', 1);
                })->get();
        }

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title'   => $title,
                'message' => $message,
                'icon'    => $icon,
                'link'    => $link,
                'read_at' => null,
            ]);
        }
    }

    private function getMonitoringPrerequisiteError(IdentifikasiRisiko $risk, $quarter = 1): ?string
    {
        $namaRisikoLengkap = $risk->peristiwa_risiko;
        if (!empty($risk->deskripsi_peristiwa_risiko)) {
            $namaRisikoLengkap .= ' - ' . $risk->deskripsi_peristiwa_risiko;
        }

        $analisa = $risk->riskAnalysis;
        if (!$analisa) {
            return 'Risiko "' . $namaRisikoLengkap . '" belum dianalisa. Harap lengkapi analisa risiko terlebih dahulu.';
        }

        $requiredAnalisaFields = [
            'kategori_dampak', 'nilai_dampak', 'nilai_probabilitas', 'skala_dampak',
        ];

        foreach ($requiredAnalisaFields as $field) {
            if (is_null($analisa->{$field})) {
                return 'Analisa untuk risiko "' . $namaRisikoLengkap . '" belum lengkap. Harap lengkapi semua field analisa inheren dan residual.';
            }
        }

        $hasResidual = !is_null($analisa->nilai_dampak_residual)
            && !is_null($analisa->nilai_probabilitas_residual)
            && !is_null($analisa->skala_dampak_residual);

        if (!$hasResidual) {
            $q = (int) $quarter ?: 1;
            $hasResidual = !is_null($analisa->{'nilai_dampak_residual_q' . $q})
                && !is_null($analisa->{'nilai_probabilitas_residual_q' . $q})
                && !is_null($analisa->{'skala_dampak_residual_q' . $q});
        }

        if (!$hasResidual) {
            return 'Analisa untuk risiko "' . $namaRisikoLengkap . '" belum lengkap. Harap lengkapi semua field analisa inheren dan residual.';
        }

        if (!$this->hasRencanaPerlakuan($risk)) {
            return 'Risiko "' . $namaRisikoLengkap . '" belum memiliki rencana perlakuan. Harap lengkapi rencana perlakuan risiko terlebih dahulu.';
        }

        return null;
    }

    private function hasRencanaPerlakuan(IdentifikasiRisiko $risk): bool
    {
        foreach ($risk->penyebabRisikos as $penyebab) {
            foreach ($penyebab->perlakuanPenyebabRisiko as $perlakuan) {
                if (!empty($perlakuan->rencana_perlakuan_risiko)) {
                    return true;
                }
            }
        }

        foreach ($risk->perlakuanDampakRisikos as $perlakuan) {
            if (!empty($perlakuan->rencana_perlakuan_risiko)) {
                return true;
            }
        }

        return false;
    }

    private function cleanRupiah($value) {
        return (float) str_replace(['Rp', '.', ','], ['', '', ''], $value);
    }
}
