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
use App\Models\SkalaParameter;
use App\Models\ProjectRiskContext;
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
        $this->baseRouteParams = [
            'project' => request()->route('project'),
            'quarter' => request()->query('quarter', 1),
            'tahun'   => request()->query('tahun', date('Y')),
            'month'   => request()->query('month'),
        ];

        $projectPeriode = ProjectPeriodeList::with('project', 'projectRisks.peristiwaRisiko')->findOrfail(request()->route('project'));
        $cb = fn ($fn) => $fn;
        $this->indexSubtitle = $projectPeriode->project->project_name;

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriode))) {
        //     abort(403);
        // }

        // Cek sudah ada Risk Context belum
        $riskContext = ProjectRiskContext::where('project_id', $projectPeriode->project_id)->first();
        if (!$riskContext || $riskContext->status != ProjectRiskContext::STATUS_VERIFIED) {
            return redirect()->route('project-periode-list.index')->with('error', 'Silahkan buat Risk Context terlebih dahulu pada Project ' . $projectPeriode->project->project_name . '.');
        }

        $userLevel = Auth::user()->level_id;
        $quarter = request()->input('filters.quarter', request()->query('quarter', 1));
        $tahun   = request()->input('filters.tahun', request()->query('tahun', date('Y')));

        $defaultMonth = match((int)$quarter) {
            2 => '4', 3 => '7', 4 => '10', default => '1'
        };

        $validMonths = match((int)$quarter) {
            1 => ['1','2','3'], 2 => ['4','5','6'], 3 => ['7','8','9'], 4 => ['10','11','12'], default => []
        };


        $filterMonth = request()->input('filters.month');
        $queryMonth  = request()->query('month');
        $monthRaw = $filterMonth ?: ($queryMonth ?: $defaultMonth);
        $month = (string) $monthRaw;

        if (!in_array($month, $validMonths)) {
            $month = (string) ($validMonths[0] ?? '1');
        }

        request()->merge(['month' => $month, 'quarter' => $quarter, 'tahun' => $tahun]);

        // if (request()->ajax()) {
        //   // Cek apa yang sebenarnya dikirim oleh browser
        //   return response()->json(request()->all());
        // }

        $this->callbackQuery = function ($query) use ($projectPeriode, $quarter, $tahun, $month) {
          $query->where('project_periode_list_id', $projectPeriode->id)
                ->leftJoin('project_risk_analisas as pra', 'project_risks.id', '=', 'pra.risiko_id')
                ->leftJoin('peristiwa_risikos as pr', 'project_risks.peristiwa_risiko_id', '=', 'pr.id')
                ->with([
                  'peristiwaRisiko',
                  'projectRiskAnalisa',
                  'projectRiskAnalisa.risiko',
                  'projectRiskAnalisa.skalaProbabilitas',
                  'projectRiskAnalisa.skalaProbabilitasResidual',
                  'projectRiskAnalisa.skalaDampakObj',
                  'projectRiskAnalisa.skalaDampakResidualObj',
                  'projectRiskMonitoring.skalaProbabilitas',
                  'projectRiskMonitoring' => function ($q) use ($quarter, $tahun, $month) {
                      $q->where('quarter', $quarter)
                        ->where('tahun', $tahun)
                        ->where('month', $month)
                        ->with(['skalaProbabilitas', 'skalaDampakObj'])
                        ->orderBy('id', 'desc');
              }])
              ->orderBy('project_risks.is_closed', 'asc')
              ->orderBy('pra.skala_risiko', 'desc')
              ->orderBy('pra.eksposur_risiko', 'desc')
              ->select('project_risks.*');
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
                    function (data, type, row) {
                        const monthNames = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

                        const m = row.project_risk_monitoring;
                        const monthIdx = m?.month || $('#table-filter select[name="month"]').val();
                        const quarter = m?.quarter || $('#table-filter select[name="quarter"]').val();
                        const tahun = m?.tahun || $('#table-filter select[name="tahun"]').val();

                        const monthName = monthNames[parseInt(monthIdx)] || "";
                        return `\${monthName} (Q\${quarter}) - \${tahun}`;
                    }
                JS,
            ],
            'peristiwa_risiko' => [
                'label' => 'Peristiwa Risiko',
                'data' => 'peristiwaRisiko.title',
                'render' => '(data, type, row) => {
                    return row.peristiwa_risiko?.title || row.rencana_kegiatan || "-";
                }',
                'class' => 'mw-10r',
            ],
            'deskripsi_peristiwa_risiko' => [
                'label' => 'Deskripsi Peristiwa Risiko',
                'data' => 'deskripsi_peristiwa_risiko',
                'sortable' => false,
                'searchable' => true,
                'class' => 'mw-20r',
            ],
            'nilai_dampak' => [
                'label' => 'Nilai Dampak Inheren',
                'data' => 'projectRiskAnalisa.nilai_dampak',
                'sortable' => true,
                'searchable' => false,
                'render' => '(data, type, row) => "Rp" + Intl.NumberFormat("id-ID").format(row.project_risk_analisa?.nilai_dampak) || "0"',
            ],
            'skala_dampak' => [
                'label' => 'Skala Dampak Inheren',
                'data' => 'projectRiskAnalisa.skala_dampak',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => {
                    const analisa = row.project_risk_analisa;
                    return analisa?.skala_dampak ? `(${analisa.skala_dampak}) ${analisa.skala_dampak_obj?.deskripsi || ""}` : "-";
                }',
            ],
            'skala_probabilitas' => [
                'label' => 'Skala Probabilitas',
                'data' => 'projectRiskAnalisa.skalaProbabilitas.tingkat',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => {
                    const prob = row.project_risk_analisa?.skala_probabilitas;
                    return prob ? `(${prob.tingkat}) ${prob.skala || ""}` : "-";
                }',
            ],
            'eksposur_risiko_inheren' => [
                'label' => 'Eksposur Risiko Inheren',
                'data' => null,
                'sortable' => false,
                'searchable' => false,
                'class' => 'white-space-nowrap text-end',
                'render' => '(data, type, row) => {
                    const val = row.project_risk_analisa?.eksposur_risiko;
                    return (val !== null && val !== undefined) ? "Rp " + Intl.NumberFormat("id-ID").format(val) : "-";
                }',
            ],
            'skala_risiko' => [
                'label' => 'Level Risiko',
                'data' => 'id',
                'sortable' => false,
                'searchable' => false,
                'class' => 'text-center align-middle',
                'render' => '(data, type, row) => {
                    const analisa = row.project_risk_analisa;
                    return analisa?.skala_risiko ? (analisa.skala_risiko + " - " + analisa.level_risiko) : "-";
                }',
                'createdCell' => 'function (td, cellData, rowData, row, col) {
                    const level = rowData.project_risk_analisa?.level_risiko;
                    if (level) {
                        const colorClass = "bg-" + level.toLowerCase().replace(/to\s+/g, "").replace(/\s+/g, "-");
                        $(td).addClass(colorClass).addClass("text-white");
                    }
                }'
            ],
            'nilai_dampak_residual' => [
                'label' => 'Nilai Dampak Residual',
                'data' => null,
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => "Rp" + Intl.NumberFormat("id-ID").format(row.project_risk_analisa?.nilai_dampak_residual) || "0"',
            ],
            'skala_dampak_residual' => [
                'label' => 'Skala Dampak Residual',
                'data' => 'projectRiskAnalisa.skala_dampak_residual',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => {
                    const analisa = row.project_risk_analisa;
                    return analisa?.skala_dampak_residual ? `(${analisa.skala_dampak_residual}) ${analisa.skala_dampak_residual_obj?.deskripsi || ""}` : "-";
                }',
            ],
            'skala_probabilitas_residual' => [
                'label' => 'Skala Probabilitas Residual',
                'data' => 'projectRiskAnalisa.skalaProbabilitasResidual.tingkat',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => {
                    const prob = row.project_risk_analisa?.skala_probabilitas_residual;
                    return prob ? `(${prob.tingkat}) ${prob.skala || ""}` : "-";
                }',
            ],
            'eksposur_risiko_residual_rencana' => [
                'label' => 'Eksposur Risiko Residual Rencana',
                'data' => null,
                'sortable' => false,
                'searchable' => false,
                'class' => 'white-space-nowrap text-end',
                'render' => '(data, type, row) => {
                    const val = row.project_risk_analisa?.eksposur_risiko_residual;
                    return (val !== null && val !== undefined) ? "Rp " + Intl.NumberFormat("id-ID").format(val) : "-";
                }',
            ],
            'skala_risiko_residual' => [
                'label' => 'Level Risiko Residual',
                'data' => 'id',
                'sortable' => false,
                'searchable' => false,
                'class' => 'text-center align-middle',
                'render' => '(data, type, row) => {
                    const analisa = row.project_risk_analisa;
                    return analisa?.skala_risiko_residual ? (analisa.skala_risiko_residual + " - " + analisa.level_risiko_residual) : "-";
                }',
                'createdCell' => 'function (td, cellData, rowData, row, col) {
                    const level = rowData.project_risk_analisa?.level_risiko_residual;
                    if (level) {
                        const colorClass = "bg-" + level.toLowerCase().replace(/to\s+/g, "").replace(/\s+/g, "-");
                        $(td).addClass(colorClass).addClass("text-white");
                    }
                }'
            ],
            'nilai_dampak_monitoring' => [
                'label' => 'Nilai Dampak Realisasi',
                'data' => 'projectRiskMonitoring.nilai_dampak',
                'sortable' => true,
                'searchable' => false,
                'render' => '(data, type, row) => (!!row.project_risk_monitoring?.nilai_dampak ? "Rp" + Intl.NumberFormat("id-ID").format(row.project_risk_monitoring?.nilai_dampak) : "-")',
            ],
            'skala_dampak_monitoring' => [
                'label' => 'Skala Dampak Realisasi',
                'data' => 'projectRiskMonitoring.skala_dampak',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => {
                    const monitoring = row.project_risk_monitoring;
                    return monitoring?.skala_dampak ? `(${monitoring.skala_dampak}) ${monitoring.skala_dampak_obj?.deskripsi || ""}` : "-";
                }',
            ],
            'skala_probabilitas_monitoring' => [
                'label' => 'Skala Probabilitas Realisasi',
                'data' => 'projectRiskMonitoring.skalaProbabilitas.tingkat',
                'sortable' => false,
                'searchable' => false,
                'render' => '(data, type, row) => {
                    const prob = row.project_risk_monitoring?.skala_probabilitas;
                    return prob ? `(${prob.tingkat}) ${prob.skala || ""}` : "-";
                }',
            ],
            'eksposur_risiko_realisasi' => [
                'label' => 'Eksposur Risiko Residual Realisasi',
                'data' => null,
                'sortable' => false,
                'searchable' => false,
                'class' => 'white-space-nowrap text-end',
                'render' => '(data, type, row) => {
                    const val = row.project_risk_monitoring?.eksposure_risiko;
                    return (val !== null && val !== undefined) ? "Rp " + Intl.NumberFormat("id-ID").format(val) : "-";
                }',
            ],
            'skala_risiko_monitoring' => [
                'label' => 'Level Risiko Realisasi',
                'data' => 'id',
                'sortable' => false,
                'searchable' => false,
                'class' => 'text-center align-middle',
                'render' => '(data, type, row) => {
                    const analisa = row.project_risk_monitoring;
                    return analisa?.skala_risiko ? (analisa.skala_risiko + " - " + analisa.level_risiko) : "-";
                }',
                'createdCell' => 'function (td, cellData, rowData, row, col) {
                    const level = rowData.project_risk_monitoring?.level_risiko;
                    if (level) {
                        const colorClass = "bg-" + level.toLowerCase().replace(/to\s+/g, "").replace(/\s+/g, "-");
                        $(td).addClass(colorClass).addClass("text-white");
                    }
                }'
            ],
            'is_closed' => [
                'label' => 'Status Risiko',
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
                'label' => 'Status Approval',
                'render' => '(data, type, row) => {
                    if (row.is_closed) return `<div class="badge text-danger bg-danger-subtle">Dihentikan</div>`;
                    if (!row.project_risk_monitoring) return `<div class="badge bg-light text-dark border">Belum Dimonitor</div>`;

                    const status = parseInt(row.project_risk_monitoring.status);
                    const isRevision = row.project_risk_monitoring.is_revision;
                    const isApproved = row.project_risk_monitoring.is_approved;
                    const verificatorMap = ' . json_encode($verificatorMap) . ';

                    // 1. Logika Jika Status REVISI / DITOLAK (Status 1, 3, atau 4 dengan flag is_revision)
                    if (isRevision && !isApproved) {
                        let rejectSource = "";
                        if (status === 1) rejectSource = "Risk Owner Project / Officer Divisi";
                        if (status === 3) rejectSource = "Risk Officer MR";
                        if (status === 4) rejectSource = "Risk Owner MR";

                        return `<div class="badge bg-danger">
                            <i class="bx bx-undo me-1"></i>Ditolak ${rejectSource}
                        </div>`;
                    }

                    // 2. Logika Jika sedang Draft Baru (Status 1 tanpa flag is_revision)
                    if (status === 1) {
                        return `<div class="badge bg-warning text-dark">Draft</div>`;
                    }

                    // 3. Logika Verifikasi Berjalan (Status 2-5)
                    if (verificatorMap[status]) {
                        const verificatorName = verificatorMap[status];
                        if (isApproved) {
                            return `<div class="badge bg-info">
                                <i class="bx bx-check-circle me-1"></i>Terverifikasi ${verificatorName}
                            </div>`;
                        } else {
                            return `<div class="badge border border-info text-info bg-white">
                                <i class="bx bx-time-five me-1"></i>Menunggu Verifikasi ${verificatorName}
                            </div>`;
                        }
                    }

                    // 4. Selesai
                    if (status === 100) {
                        return `<div class="badge bg-success">Selesai</div>`;
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
                'active_state' => '(data, type, row) => {
                  return !row.is_closed && (!row.project_risk_monitoring || row.project_risk_monitoring.status == ' . ProjectRiskMonitoring::STATUS_DRAFT_REVISI . ');
                }',
            ];

            $this->tableActions[] = [
                'label' => 'Change to LED',
                'btn_icon' => false,
                'action' => 'change_to_led',
                'active_state' => '(data, type, row) => {
                    return !row.is_closed && (row.project_risk_monitoring?.status == ' . ProjectRiskMonitoring::STATUS_PUBLISHED . ');
                }',
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
                    $tahun,
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
                    $quarter,
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
                    $month,
                    [
                        'class' => 'form-select select2 js-select-hide-search',
                    ]
                ],
                'handler' => function ($query, $key, $value) {
                    // handled outside
                },
            ],
        ];

        $bulkRoute = route("projects.monitorings.bulk-verify", ["project" => request()->route('project')]);

        // Pastikan nama route ini sesuai dengan route yang Anda definisikan di web.php
        $catatanRoute = route('projects.monitorings.notes', ['project' => request()->route('project'), 'riskId' => ':id']);

        $csrfToken = csrf_token();
        $phpQuarter = request()->query('quarter', 1);
        $phpMonth = request()->query('month', '');

        // --- 2. EXTRASCRIPTS (GABUNGAN LAMA & BARU) ---
        $this->extraScripts[] = <<<SCRIPT
            <script>
            // --- BAGIAN 1: LOGIKA ESKALASI (Script Lama) ---
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
                                    location.reload(); // Reload untuk refresh status
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

            // --- BAGIAN 2: LOGIKA BULK VERIFY (Script Baru) ---
            let currentIds = [];

            // Handler Checkbox Select All
            $(document).on('change', '#check-all-risiko', function() {
                $('.row-checkbox:not(:disabled)').prop('checked', this.checked);
                toggleBulkButton();
            });

            // Handler Checkbox per Row
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

            // Handler Tombol "Verifikasi Risiko" (Membuka Modal)
            function handleBulkVerifikasiClick() {
                currentIds = [];
                $('.row-checkbox:checked').each(function() {
                    currentIds.push($(this).val());
                });

                if (currentIds.length === 0) return;

                // Reset Form
                $('#catatan-verifikasi').val('');

                // Update Info Jumlah Data di Modal
                if($('#modal-bulk-info').length == 0) {
                    $('.modal-body').prepend(`
                        <div id="modal-bulk-info" class="alert alert-info mt-0 mb-3">
                            <i class="bx bx-info-circle"></i> Memverifikasi <strong>\${currentIds.length}</strong> data terpilih.
                        </div>
                    `);
                } else {
                    $('#modal-bulk-info strong').text(currentIds.length);
                }

                const modalEl = document.getElementById('modalVerifikasiRisiko');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                // Override tombol Modal untuk memanggil fungsi submitBulk
                $('#btn-terima-risiko').off('click').on('click', function() { submitBulk('terima'); });
                $('#btn-tolak-risiko').off('click').on('click', function() { submitBulk('tolak'); });
            }

            // Fungsi Submit AJAX Bulk Verify
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
                    confirmButtonColor: status === 'terima' ? '#198754' : '#dc3545'
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

            // --- BAGIAN 3: LOGIKA CATATAN (Notes) ---
            function showCatatanRisiko(riskId) {
                const modalElement = document.getElementById('modalCatatan');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                const contentDiv = $('#catatan-content');

                contentDiv.html('<div class="d-flex justify-content-center my-4"><div class="spinner-border" role="status"></div></div>');

                // Gunakan route monitoring notes
                const url = "{$catatanRoute}".replace(':id', riskId);

                // Tambahkan parameter query untuk filter catatan spesifik tahun/bulan/quarter monitoring
                const quarter = $('select[name="quarter"]').val();
                const tahun = $('select[name="tahun"]').val();
                const month = $('select[name="month"]').val();
                const fullUrl = `\${url}?quarter=\${quarter}&tahun=\${tahun}&month=\${month}`;

                $.ajax({
                    url: fullUrl,
                    type: 'GET',
                    success: function(notes) {
                        if (notes.length === 0) {
                            contentDiv.html('<div class="text-center my-4"><p>Belum ada catatan.</p></div>');
                        } else {
                            let html = '';
                            notes.forEach(note => {
                                const statusBadge = note.status == 1
                                    ? '<span class="badge bg-success-subtle text-success">Diterima</span>'
                                    : '<span class="badge bg-danger-subtle text-danger">Ditolak</span>';

                                const date = new Date(note.created_at).toLocaleDateString('id-ID');

                                html += `
                                <div class="card mb-2 shadow-sm">
                                    <div class="card-header bg-light d-flex justify-content-between p-2">
                                        <strong>\${note.user ? note.user.name : 'System'}</strong>
                                        <small>\${date} \${statusBadge}</small>
                                    </div>
                                    <div class="card-body p-2">
                                        \${note.notes || '-'}
                                    </div>
                                </div>`;
                            });
                            contentDiv.html(html);
                        }
                        modal.show();
                    },
                    error: function() {
                        contentDiv.html('<div class="text-center text-danger">Gagal memuat catatan.</div>');
                        modal.show();
                    }
                });
            }

            // --- BAGIAN 4: DROPDOWN & URL UPDATE (Script Lama) ---
            $(document).ready(function() {
                const allMonths = {
                    '1': {'1': 'Januari', '2': 'Februari', '3': 'Maret'},
                    '2': {'4': 'April', '5': 'Mei', '6': 'Juni'},
                    '3': {'7': 'Juli', '8': 'Agustus', '9': 'September'},
                    '4': {'10': 'Oktober', '11': 'November', '12': 'Desember'},
                };

                function updateMonthDropdown(quarter, selectedMonth = null) {
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
                }

                const activeQuarter = "{$phpQuarter}";
                const activeMonth   = "{$phpMonth}";

                // Init Dropdown saat halaman load
                $('select[name="quarter"]').val(activeQuarter).trigger('change.select2');
                updateMonthDropdown(activeQuarter, activeMonth);

                // Handler Change Quarter
                $('select[name="quarter"]').on('change', function() {
                    const newQuarter = $(this).val();
                    let firstMonthOfQuarter = null;
                    if (allMonths[newQuarter]) {
                        firstMonthOfQuarter = Object.keys(allMonths[newQuarter])[0];
                    }
                    updateMonthDropdown(newQuarter, firstMonthOfQuarter);
                });

                // Handler Update URL
                $('select[name="tahun"], select[name="quarter"], select[name="month"]').on('change', function() {
                    updateUrlParams();
                });

                function updateUrlParams() {
                    const q = $('select[name="quarter"]').val();
                    const t = $('select[name="tahun"]').val();
                    const m = $('select[name="month"]').val();

                    if (!q || !t || !m) return;

                    const url = new URL(window.location.href);
                    url.searchParams.set('quarter', q);
                    url.searchParams.set('tahun', t);
                    url.searchParams.set('month', m);

                    window.history.pushState({path: url.href}, '', url.href);
                }
            });
            </script>
        SCRIPT;

        // $this->extraScripts[] = $this->getFilterScripts($quarter, $month);

        $workflow = ProjectRiskMonitoring::getWorkflow();

        // 1. Filter Risiko: Hanya ambil yang aktif (is_closed = 0)
        // Risiko yang sudah closed tidak perlu dimonitor lagi
        $allRisks = ProjectRisk::where('project_periode_list_id', $projectPeriode->id)
            ->where('is_closed', 0)
            ->get();

        $riskIds = $allRisks->pluck('id');

        // 2. Ambil data Monitoring untuk risiko-risiko aktif tersebut
        $latestMonitoringIds = ProjectRiskMonitoring::query()
            ->select(DB::raw('MAX(id) as id'))
            ->whereIn('risiko_id', $riskIds)
            ->where('quarter', $quarter)
            ->where('tahun', $tahun)
            ->where('month', $month)
            ->groupBy('risiko_id')
            ->pluck('id');

        $activeMonitorings = ProjectRiskMonitoring::whereIn('id', $latestMonitoringIds)
            ->get()
            ->keyBy('risiko_id');
        // dd($activeMonitorings, $quarter, $tahun, $month);

        $selectedStep = null;
        $firstEligibleStep = null;

        foreach ($workflow as $step => $info) {
            if ($this->checkUserEligibility($user, $info, $projectPeriode->project)) {
                if (!$firstEligibleStep) $firstEligibleStep = $step;

                // Hitung data di step ini
                $countInStep = $allRisks->filter(function($risk) use ($step, $activeMonitorings) {
                    $m = $activeMonitorings[$risk->id] ?? null;
                    if ($step == 1) return !$m || $m->status == 1;
                    return $m && $m->status == $step;
                })->count();

                if ($countInStep > 0) {
                    $selectedStep = $step;
                    break;
                }
            }
        }
        if (!$selectedStep) $selectedStep = $firstEligibleStep;

        // Generate Info & Config
        $summaryInfo = null;
        $escalationConfig = [
            'show' => false,
            'label' => 'Proses',
            'disabled' => true,
            'parameters' => [
                'quarter' => $quarter,
                'tahun' => $tahun,
                'month' => $month
            ]
        ];

        if ($selectedStep) {
            $step = $selectedStep;

            // Filter risiko yang BENAR-BENAR ada di step ini
            $risksInMyStep = $allRisks->filter(function($risk) use ($step, $activeMonitorings) {
                $m = $activeMonitorings[$risk->id] ?? null;
                if ($step == 1) return !$m || $m->status == 1;
                return $m && $m->status == $step;
            });

            // === GUARD CLAUSE (PERBAIKAN UTAMA) ===
            // Jika tidak ada risiko di step ini (kosong karena sudah naik level semua),
            // Langsung hentikan proses logic button.
            if ($risksInMyStep->isEmpty()) {
                $this->extraViewData['summaryInfo'] = null;
                $this->extraViewData['escalationConfig'] = ['show' => false, 'label' => '', 'disabled' => true];
                return parent::index();
            }
            // ======================================

            $escalationConfig['route'] = route('projects.monitorings.send.all', ['project' => request()->route('project')]);

            // --- STEP 1: INPUT / DRAFTER ---
            if ($step == 1) {
                $unstartedCount = $risksInMyStep->filter(fn($r) => !isset($activeMonitorings[$r->id]))->count();
                $revisionCount = $risksInMyStep->filter(fn($r) => isset($activeMonitorings[$r->id]) && $activeMonitorings[$r->id]->status == 1 && $activeMonitorings[$r->id]->is_revision)->count();
                $readyCount = $risksInMyStep->filter(fn($r) => isset($activeMonitorings[$r->id]) && $activeMonitorings[$r->id]->status == 1 && !$activeMonitorings[$r->id]->is_revision)->count();

                $targetLabel = 'Kirim ke Risk Owner Project';

                if ($revisionCount > 0) {
                    $summaryInfo = ['type' => 'danger', 'icon' => 'bx-undo', 'message' => "Terdapat <strong>{$revisionCount}</strong> monitoring risiko yang <strong>dikembalikan (revisi)</strong>. Mohon perbaiki data."];
                    $escalationConfig['show'] = true;
                    $escalationConfig['disabled'] = true;
                } elseif ($unstartedCount > 0) {
                    $summaryInfo = ['type' => 'warning', 'icon' => 'bx-info-circle', 'message' => "Terdapat <strong>{$unstartedCount}</strong> risiko aktif belum di-monitoring."];
                    $escalationConfig['show'] = true;
                    $escalationConfig['disabled'] = true;
                } else {
                    // Hanya tampil jika readyCount > 0 (Data sudah siap)
                    $summaryInfo = [
                        'type' => 'success',
                        'icon' => 'bx-check-double',
                        'message' => "Seluruh monitoring siap. Silahkan klik tombol <strong>{$targetLabel}</strong> untuk melanjutkan."
                    ];
                    $escalationConfig['show'] = true;
                    $escalationConfig['disabled'] = false;
                }
                $escalationConfig['label'] = $targetLabel;
            }
            // --- STEP > 1: VERIFIKATOR ---
            else {
                // Tentukan Label Tombol
                if (isset($workflow[$step + 1])) {
                    $btnLabel = "Kirim ke " . $workflow[$step + 1]['label'];
                } else {
                    $btnLabel = "Tetapkan Monitoring";
                }
                $escalationConfig['label'] = $btnLabel;

                $unapprovedCount = $risksInMyStep->filter(fn($r) => !$activeMonitorings[$r->id]->is_approved)->count();
                $returnedCount = $risksInMyStep->filter(fn($r) => $activeMonitorings[$r->id]->is_revision == true)->count();

                if ($unapprovedCount > 0) {
                    $escalationConfig['show'] = true;
                    $escalationConfig['disabled'] = true;

                    if ($returnedCount > 0) {
                        $rejectorLabel = $workflow[$step + 1]['label'] ?? 'Verifikator Selanjutnya';
                        $summaryInfo = [
                            'type' => 'danger',
                            'icon' => 'bx-undo',
                            'message' => "Terdapat <strong>{$returnedCount}</strong> monitoring yang <strong>dikembalikan oleh {$rejectorLabel}</strong>. Mohon verifikasi ulang."
                        ];
                    } else {
                        $senderLabel = $workflow[$step - 1]['label'] ?? 'Tahap Sebelumnya';
                        $summaryInfo = [
                            'type' => 'warning',
                            'icon' => 'bxs-error-circle',
                            'message' => "Terdapat <strong>{$unapprovedCount}</strong> monitoring aktif dari <strong>{$senderLabel}</strong> menunggu verifikasi Anda."
                        ];
                    }
                } else {
                    // Semua sudah diapprove DAN risksInMyStep > 0 (karena Guard Clause di atas)
                    // Artinya: user sudah selesai verifikasi semua tapi BELUM klik kirim.
                    $summaryInfo = [
                        'type' => 'success',
                        'icon' => 'bx-check-double',
                        'message' => "Seluruh monitoring telah diverifikasi. Silahkan klik tombol <strong>{$btnLabel}</strong> untuk melanjutkan."
                    ];
                    $escalationConfig['show'] = true;
                    $escalationConfig['disabled'] = false;
                }
            }
        }

        $this->extraViewData['summaryInfo'] = $summaryInfo;
        $this->extraViewData['escalationConfig'] = $escalationConfig;

        $this->extraViewData['hasVerificationMr'] = Gate::allows('verification_mr');
        $this->extraViewData['userUnitCostCenter'] = $user->unit ? $user->unit->cost_center : null;
        $this->extraViewData['isUserUnitMr'] = $user->unit ? $user->unit->unit_mr : 0;
        $this->extraViewData['currentUserLevel'] = $userLevel;
        $this->extraViewData['showBulkCheckbox'] = true;

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
                // 'taksonomiRisiko',
                // 'parameterRisikoProjects',
                'projectRiskAnalisa',
                'projectRiskAnalisa.skalaDampakObj',
                'projectRiskAnalisa.skalaProbabilitas',
                'projectRiskAnalisa.skalaDampakResidualObj',
                'projectRiskAnalisa.skalaProbabilitasResidual',
                'projectRiskAnalisa.skalaParameterObj',
                'projectRiskAnalisa.skalaParameterResidualObj',
                'peristiwaRisiko',
                'kriProjects' => function ($query) use ($quarter, $tahun, $month) {
                    $query->select('k_r_i_projects.*', 'id as status_kri_terkini', 'id as nilai_kri_terkini');
                    $query->with('kriProjectMonitorings', function ($query) use ($quarter, $tahun, $month) {
                        $query->with('projectMonitoring')->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun, $month) {
                            $query->where('quarter', $quarter)
                                ->where('tahun', $tahun)
                                ->where('month', $month)
                                ->with('skalaParameter');
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
                        ->where('month', $month)
                        ->with(['pengendalians', 'skalaProbabilitas', 'perlakuanDampakRisikoDocuments']);
                },
                'perlakuanDampakRisikos' => function ($query) use ($quarter, $tahun, $month) {
                    $query->with(['lastMonitoring' => function ($q) use ($quarter, $tahun, $month) {
                        $q->whereHas('projectMonitoring', function ($sq) use ($quarter, $tahun, $month) {
                            $sq->where('quarter', $quarter)->where('tahun', $tahun)->where('month', $month);
                        });
                    }, 'picJabatan']);
                },
            ])
            ->findOrFail(request()->route('monitoring'));

        // [HIDE] Template Danantara
        // $currentDate = \Carbon\Carbon::create($tahun, $month, 1);
        // $dateM1 = $currentDate->copy()->subMonth();
        // $dateM2 = $currentDate->copy()->subMonths(2);

        // // Ambil nilai aktual bulan-bulan sebelumnya
        // $monitoringM1 = $projectRisk->projectRiskMonitorings()
        //     ->where('month', $dateM1->month)->where('tahun', $dateM1->year)->first();
        // $monitoringM2 = $projectRisk->projectRiskMonitorings()
        //     ->where('month', $dateM2->month)->where('tahun', $dateM2->year)->first();

        // // Cari Data Pengendalian Terakhir (dari entri terbaru di database sebelum bulan ini)
        // $lastMonitoringEntry = $projectRisk->projectRiskMonitorings()
        //     ->with('pengendalians')
        //     ->where(function($q) use ($tahun, $month) {
        //         $q->where('tahun', '<', $tahun)
        //           ->orWhere(function($q2) use ($tahun, $month) {
        //               $q2->where('tahun', $tahun)->where('month', '<', $month);
        //           });
        //     })
        //     ->orderByDesc('tahun')
        //     ->orderByDesc('month')
        //     ->orderByDesc('id')
        //     ->first();
        // $historicalPengendalians = $lastMonitoringEntry ? $lastMonitoringEntry->pengendalians->keyBy('parameter_id') : collect();

        $analisa = $projectRisk->projectRiskAnalisa;
        $namaRisikoLengkap = $projectRisk->peristiwa_risiko_id ? $projectRisk?->peristiwaRisiko?->title : $projectRisk->rencana_kegiatan;
        if (!empty($projectRisk->deskripsi_peristiwa_risiko)) {
            $namaRisikoLengkap .= ' - ' . $projectRisk->deskripsi_peristiwa_risiko;
        }

        // Validasi Analisa Risiko
        if (!$analisa) {
            return redirect()->route('projects.monitorings.index', ['project' => $projectPeriode->id])
                ->with('error', 'Risiko "' . $namaRisikoLengkap . '" belum dianalisa. Harap lengkapi analisa risiko terlebih dahulu.');
        }

        // Validasi Publish Risiko
        if ($projectRisk->status != ProjectRisk::STATUS_PUBLISHED) {
            return redirect()->route('projects.monitorings.index', ['project' => $projectPeriode->id])
                ->with('error', 'Risiko "' . $namaRisikoLengkap . '" belum terpublikasi. Harap minta persetujuan risiko terlebih dahulu.');
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
        // $penyebabRisikos = $projectRisk->penyebabRisikoProjects;

        // if ($penyebabRisikos->isEmpty()) {
        //     return redirect()->route('projects.monitorings.index', ['project' => $projectPeriode->id])
        //         ->with('error', 'Risiko "' . $namaRisikoLengkap . '" belum memiliki data penyebab dan rencana perlakuan.');
        // }

        // $hasValidPerlakuan = false;
        // foreach ($penyebabRisikos as $penyebab) {
        //     if (!empty($penyebab->penyebab_risiko) && $penyebab->perlakuanPenyebabRisiko->isNotEmpty()) {
        //         foreach ($penyebab->perlakuanPenyebabRisiko as $perlakuan) {
        //             if (!empty($perlakuan->rencana_perlakuan_risiko)) {
        //                 $hasValidPerlakuan = true;
        //                 break 2;
        //             }
        //         }
        //     }
        // }

        // if (!$hasValidPerlakuan) {
        //     return redirect()->route('projects.monitorings.index', ['project' => $projectPeriode->id])
        //         ->with('error', 'Risiko "' . $namaRisikoLengkap . '" harus memiliki minimal satu penyebab dengan rencana perlakuan yang sudah diisi.');
        // }

        $peristiwaRisiko = $projectRisk->peristiwaRisiko;
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        $skalaParameters = SkalaParameter::all();
        $groupedSkalaParameters = $skalaParameters->groupBy('type_parameter');
        $selectedParameterType = null;
        if ($projectRisk->projectRiskAnalisa && $projectRisk->projectRiskAnalisa->skalaParameterObj) {
            $selectedParameterType = $projectRisk->projectRiskAnalisa->skalaParameterObj->type_parameter;
        }

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
        $risk_limit = ($projectPeriode->project->nk ?? 0) * 0.03;
        // dd($projectRisk->projectRiskMonitoring);

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
            // 'monitoringM1' => $monitoringM1, // template Danatara
            // 'monitoringM2' => $monitoringM2,
            // 'historicalPengendalians' => $historicalPengendalians,
            'skalaDampaks' => SkalaDampak::pluck('deskripsi', 'tingkat'),
            'riskMaps' => $riskMaps,
            'skalaProbabilitas' => $skalaProbabilitas,
            'quarter' => $quarter,
            'tahun' => $tahun,
            'month' => $month,
            'risk_tolerance' => $risk_tolerance,
            'risk_limit' => $risk_limit,
            'groupedSkalaParameters' => $groupedSkalaParameters,
            'selectedParameterType' => $selectedParameterType,
        ]);
    }

    public function show($resource)
    {
        $projectPeriode = ProjectPeriodeList::findOrfail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriode))) {
        //     abort(403);
        // }

        $quarter = request()->input('quarter') ?: 1;
        $tahun = request()->input('tahun') ?: date('Y');
        $month = request()->input('month') ?: '';
        $projectRisk = $projectPeriode->projectRisks()
            ->with([
                'taksonomiRisiko',
                'parameterRisikoProjects',
                'peristiwaRisiko',
                'penyebabRisikoProjects',
                'projectRiskAnalisa.skalaParameterObj',
                'projectRiskAnalisa.skalaParameterResidualObj',
                'kriProjects' => function ($query) use ($quarter, $tahun, $month) {
                    $query->with('kriProjectMonitorings', function ($query) use ($quarter, $tahun, $month) {
                        $query->with('projectMonitoring')->whereHas('projectMonitoring', function ($query) use ($quarter, $tahun, $month) {
                            $query->where('quarter', $quarter)
                                ->where('tahun', $tahun)
                                ->where('month', $month)
                                ->with('skalaParameter');
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

        $historyMonitorings = ProjectRiskMonitoring::where('risiko_id', $projectRisk->id)
            ->with([
                'skalaDampakObj',
                'skalaProbabilitas',
                'perlakuanPenyebabMonitorings.perlakuanPenyebab.penyebabRisikoProject',
                'perlakuanDampakMonitorings.perlakuanDampak.dampakRisikoProject',
                'perlakuanPenyebabRisikoDocuments',
                'perlakuanDampakRisikoDocuments',
                'kriProyekMonitorings.kriProject'
            ])
            ->orderBy('tahun', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $peristiwaRisiko = $projectRisk->peristiwaRisiko;
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });
        $projectMonitoring = $projectRisk->projectRiskMonitoring()->where('quarter', $quarter)->where('tahun', $tahun)->where('month', $month)->first();

        $files = $projectMonitoring?->perlakuanPenyebabRisikoDocuments->groupBy('perlakuan_penyebab_risiko_id') ?: [];

        $currentDate = \Carbon\Carbon::create($tahun, $month, 1);
        $dateM1 = $currentDate->copy()->subMonth();
        $dateM2 = $currentDate->copy()->subMonths(2);

        $monitoringM1 = $projectRisk->projectRiskMonitorings()
            ->where('month', $dateM1->month)->where('tahun', $dateM1->year)->first();
        $monitoringM2 = $projectRisk->projectRiskMonitorings()
            ->where('month', $dateM2->month)->where('tahun', $dateM2->year)->first();

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
            'monitoringM1' => $monitoringM1,
            'monitoringM2' => $monitoringM2,
            'dateCurrent' => $currentDate,
            'dateM1' => $dateM1,
            'dateM2' => $dateM2,
            'historyMonitorings' => $historyMonitorings,
        ]);
    }

    public function update(Request $request, $resource) {
        $quarter = request()->input('quarter') ?: 1;
        $tahun = request()->input('tahun') ?: date('Y');
        $month = request()->input('month') ?: '';
        $projectPeriode = ProjectPeriodeList::findOrfail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriode))) {
        //     abort(403);
        // }

        $projectRisk = $projectPeriode->projectRisks()
            ->with('peristiwaRisiko', 'penyebabRisikoProjects', 'penyebabRisikoProjects.perlakuanPenyebabRisiko', 'kriProjects', 'projectRiskAnalisa')
            ->findOrFail(request()->route('monitoring'));

        // 1. Ambil Skala Dampak (Prioritaskan Input Manual Dropdown)
        $skalaDampak = $request->realisasi_skala_dampak ?? $request->realisasi_skala_dampak_hidden;

        // 2. Ambil Nilai Probabilitas & Skala Probabilitas
        $nilaiProbabilitas = $request->realisasi_nilai_probabilitas;
        // Prioritaskan input dropdown ('realisasi_skala_probabilitas') dari form
        $skalaProbabilitasId = $request->realisasi_skala_probabilitas ?? $request->realisasi_skala_probabilitas_hidden;

        // Logic Kalkulasi Otomatis hanya jika Dropdown Kosong tapi Nilai Ada
        if (empty($skalaProbabilitasId) && !is_null($nilaiProbabilitas)) {
            $tingkatSkalaProbabilitas = SkalaProbabilitas::getSkalaByValue($nilaiProbabilitas);
            if ($tingkatSkalaProbabilitas) {
                $skalaProbabilitasId = $tingkatSkalaProbabilitas->id; // Sesuaikan column ID atau Tingkat
            }
        }

        // 3. Cari Risk Map berdasarkan Skala Dampak & Skala Probabilitas (Manual/Auto)
        $skalaRisiko = null;
        $levelRisiko = null;

        if ($skalaDampak && $skalaProbabilitasId) {
            $riskMap = RiskMap::where('skala_dampak', $skalaDampak)
                ->where('skala_probabilitas', $skalaProbabilitasId)
                ->first();

            if ($riskMap) {
                $skalaRisiko = $riskMap->nilai_risiko;
                $levelRisiko = $riskMap->level_risiko;
            }
        }

        $skalaRisiko = $request->realisasi_skala_risiko ?? $request->realisasi_skala_risiko_hidden ?? $skalaRisiko;
        $levelRisiko = $request->realisasi_level_risiko ?? $request->realisasi_level_risiko_hidden ?? $levelRisiko;

        $toCreate = [
            'quarter' => $quarter,
            'tahun' => $tahun,
            'nilai_dampak' => str_replace(['Rp', '.', ' '], '', ($request->realisasi_nilai_dampak ?: 0)),
            'skala_dampak' => $skalaDampak,
            'nilai_probabilitas' => $nilaiProbabilitas,
            'skala_probabilitas_id' => $skalaProbabilitasId,
            'skala_risiko' => $skalaRisiko,
            'level_risiko' => $levelRisiko,
            'skala_parameter_id' => $request->realisasi_skala_parameter_id,
            'eksposure_risiko' => null,
            'month' => $month,
            // 'aktual_current' => $this->cleanRupiah($request->aktual_current), // [HIDE] Template Danantara
            // 'aktual_month_1' => $this->cleanRupiah($request->aktual_month_1),
            // 'aktual_month_2' => $this->cleanRupiah($request->aktual_month_2),
            // 'aktual_status' => $request->aktual_status,
        ];

        $riskLimit = ($projectPeriode->project->nk ?? 0) * 0.03;

        //perhitungan eksposur risiko
        if ($projectRisk->projectRiskAnalisa?->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF) {
            $toCreate['eksposure_risiko'] = floatval($toCreate['skala_dampak']) * (1/100) * floatval($toCreate['nilai_probabilitas']) * $riskLimit;
        } elseif ($projectRisk->projectRiskAnalisa?->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF) {
            $toCreate['eksposure_risiko'] = floatval($toCreate['nilai_dampak']) * floatval($toCreate['nilai_probabilitas']) / 100;
        }

        $projectMonitoring = $projectRisk->projectRiskMonitoring()->create($toCreate);

        // [HIDE] Simpan Rencana & Realisasi Pengendalian jika status Siaga/Bahaya
        // // $projectMonitoring->pengendalians()->delete();
        // if (in_array($request->aktual_status, ['Siaga', 'Bahaya'])) {
        //     $paramIds = $request->input('pengendalian_parameter_id', []);
        //     $rencana = $request->input('rencana_pengendalian', []);
        //     $realisasi = $request->input('realisasi_pengendalian', []);

        //     foreach ($paramIds as $key => $pId) {
        //         if (!empty($rencana[$key])) {
        //             $projectMonitoring->pengendalians()->create([
        //                 'parameter_id' => $pId,
        //                 'rencana_pengendalian' => $rencana[$key],
        //                 'realisasi_pengendalian' => $realisasi[$key] ?? null,
        //             ]);
        //         }
        //     }
        // }

        $perlakuanDampakReq = json_decode($request->perlakuan_dampak_risikos, true);
        if($perlakuanDampakReq) {
            foreach ($perlakuanDampakReq as $id => $item) {
                $perlakuanModel = \App\Models\PerlakuanDampakRisiko::find($id);
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

                $documentFiles = $request->file('document_dampak_file_' . $id);
                if ($documentFiles = $request->file('document_dampak_file_' . $id)) {
                    $documentDescriptions = $request->input('document_description_' . $id, []);

                    foreach ($documentFiles as $idx => $documentFile) {
                        if ($documentFile) {
                            $storeFile = $documentFile->store('project-monitoring-documents', 'public');
                            $projectMonitoring->perlakuanDampakRisikoDocuments()->create([
                                'perlakuan_dampak_risiko_id' => $id,
                                'user_id' => request()->user()->id,
                                'file_name' => $documentFile->getClientOriginalName(),
                                'file_path' => $storeFile,
                                'mimetype' => $documentFile->getClientMimeType(),
                                'description' => $documentDescriptions[$idx] ?? '',
                            ]);
                        }
                    }
                }
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
                'perlakuan_penyebab_id' => $id,
                'progress_rencana_perlakuan_risiko' => $perlakuanPenyebabRequest['progress_rencana_perlakuan_risiko'] ?? null,
                'realisasi_biaya_perlakuan_risiko' => $perlakuanPenyebabRequest['realisasi_biaya_perlakuan_risiko'] ?? null,
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
                    $storeFile = $documentFile->store('project-monitoring-documents', 'public');
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

        $kriProjectRequests = json_decode($request->kri_projects, true);
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

        $efektivitas = 0.0;

        $analisa = $projectRisk->projectRiskAnalisa;
        $skala_risiko_inherent = (float) optional($analisa)->skala_risiko;
        $skala_risiko_rencana = (float) optional($analisa)->skala_risiko_residual;
        $skala_risiko_realisasi = (float) ($request->realisasi_skala_risiko ?? $request->realisasi_skala_risiko_hidden ?? 0);

        $selisih_inherent_rencana = $skala_risiko_inherent - $skala_risiko_rencana;

        // Hindari pembagian dengan nol
        if ($selisih_inherent_rencana != 0) {
            $efektivitas = (($skala_risiko_rencana - $skala_risiko_realisasi) / $selisih_inherent_rencana) * 100;
        }

        if ($projectMonitoring) {
            $projectMonitoring->update([
                'efektivitas_perlakuan_risiko' => round($efektivitas, 2)
            ]);
        }

        $projectRisk->update([
            'efektivitas_perlakuan_risiko' => round($efektivitas, 2)
        ]);

        if ($request->is_closed == '1') {
            $projectRisk->update([
                'is_closed' => true,
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

    private function getFilterScripts($quarter, $month)
    {
        $phpQuarter = $quarter;
        $phpMonth   = $month;

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
                const allMonths = {
                    '1': {'1': 'Januari', '2': 'Februari', '3': 'Maret'},
                    '2': {'4': 'April', '5': 'Mei', '6': 'Juni'},
                    '3': {'7': 'Juli', '8': 'Agustus', '9': 'September'},
                    '4': {'10': 'Oktober', '11': 'November', '12': 'Desember'},
                };

                function updateMonthDropdown(quarter, selectedMonth = null) {
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
                }

                const activeQuarter = "{$phpQuarter}";
                const activeMonth   = "{$phpMonth}";

                $('select[name="quarter"]').val(activeQuarter).trigger('change.select2');

                updateMonthDropdown(activeQuarter, activeMonth);

                $('select[name="quarter"]').on('change', function() {
                    const newQuarter = $(this).val();
                    let firstMonthOfQuarter = null;
                    if (allMonths[newQuarter]) {
                        firstMonthOfQuarter = Object.keys(allMonths[newQuarter])[0];
                    }
                    updateMonthDropdown(newQuarter, firstMonthOfQuarter);
                });
                $('select[name="month"]').val(activeMonth).trigger('change');

                $('select[name="tahun"], select[name="quarter"], select[name="month"]').on('change', function() {
                    updateUrlParams();
                });

                function updateUrlParams() {
                    const q = $('select[name="quarter"]').val();
                    const t = $('select[name="tahun"]').val();
                    const m = $('select[name="month"]').val();

                    if (!q || !t || !m) return;

                    const url = new URL(window.location.href);
                    url.searchParams.set('quarter', q);
                    url.searchParams.set('tahun', t);
                    url.searchParams.set('month', m);

                    window.history.pushState({path: url.href}, '', url.href);
                }
            });
            </script>
        JS;
    }

    public function sendAllMonitoring(Request $request, ProjectPeriodeList $project)
    {
        $validated = $request->validate([
            'quarter' => 'required|integer',
            'tahun' => 'required|integer',
            'month' => 'required|integer',
        ]);

        $user = Auth::user();
        $workflow = ProjectRiskMonitoring::getWorkflow();
        $riskIds = $project->projectRisks()->where('is_closed', 0)->pluck('id');

        // 1. Cari Step mana yang menjadi hak user saat ini berdasarkan konfigurasi workflow
        $currentStep = null;
        foreach ($workflow as $step => $info) {
            if ($this->checkUserEligibility($user, $info, $project->project)) {
                $currentStep = $step;
                break;
            }
        }

        if (!$currentStep) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki otoritas untuk mengirim data pada tahap ini.'], 403);
        }

        // 2. Ambil hanya ID terbaru (MAX ID) per risiko
        $subQuery = ProjectRiskMonitoring::select(DB::raw('MAX(id) as last_id'))
            ->whereIn('risiko_id', $riskIds)
            ->where('quarter', $validated['quarter'])
            ->where('tahun', $validated['tahun'])
            ->where('month', $validated['month'])
            ->groupBy('risiko_id');

        // Ambil record monitoring lengkap berdasarkan ID terbaru yang statusnya sesuai
        $latestMonitorings = ProjectRiskMonitoring::whereIn('id', $subQuery)
            ->where('status', $currentStep)
            ->get();

        if ($latestMonitorings->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada monitoring yang siap dikirim.'], 422);
        }

        // 3. Validasi approval untuk verifikator (Step > 1)
        if ($currentStep > 1) {
            $unapprovedCount = $latestMonitorings->where('is_approved', false)->count();
            if ($unapprovedCount > 0) {
                return response()->json(['success' => false, 'message' => "Terdapat {$unapprovedCount} risiko yang belum diverifikasi."], 422);
            }
        }

        // 4. Tentukan target status selanjutnya
        $nextStep = $currentStep + 1;
        $isFinal = !isset($workflow[$nextStep]);
        $targetStatus = $isFinal ? ProjectRiskMonitoring::STATUS_PUBLISHED : $nextStep;

        DB::beginTransaction();
        try {
            // Update hanya record terbaru yang ditemukan
            ProjectRiskMonitoring::whereIn('id', $latestMonitorings->pluck('id'))->update([
                'status' => $targetStatus,
                'is_approved' => $isFinal,
                'is_revision' => false, // Reset flag revisi saat dikirim ke atas
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Berhasil mengirim monitoring.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal sistem: ' . $e->getMessage()], 500);
        }
    }

    public function bulkVerifyMonitoring(Request $request, ProjectPeriodeList $project)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:project_risk_monitorings,id',
            'status_verifikasi' => 'required|in:terima,tolak',
            'catatan_verifikasi' => 'required_if:status_verifikasi,tolak|nullable|string|max:2000',
        ]);

        $user = Auth::user();
        $count = 0;

        DB::beginTransaction();
        try {
            $monitorings = ProjectRiskMonitoring::whereIn('id', $validated['ids'])->get();

            foreach ($monitorings as $monitoring) {
                if ($validated['status_verifikasi'] == 'terima') {
                    // Jika Diterima
                    $monitoring->update(['is_approved' => true]);
                } else {
                    $targetStatus = ProjectRiskMonitoring::getReturnStatus($monitoring->status);

                    $monitoring->update([
                        'status' => $targetStatus,
                        'is_approved' => false,
                        'is_revision' => true,
                    ]);
                }

                RiskMonitoringNote::create([
                    'risiko_id' => $monitoring->risiko_id,
                    'type' => 2,
                    'user_id' => $user->id,
                    'status' => $validated['status_verifikasi'] == 'terima' ? 1 : 0,
                    'notes' => $validated['catatan_verifikasi'],
                    'quarter' => $monitoring->quarter,
                    'month' => $monitoring->month,
                    'year' => $monitoring->tahun,
                ]);

                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil memverifikasi ' . $count . ' data monitoring.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses data: ' . $e->getMessage()
            ], 500);
        }
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
                $targetStatus = ProjectRiskMonitoring::getReturnStatus($monitoring->status);

                $monitoring->update([
                    'status' => $targetStatus,
                    'is_approved' => false,
                    'is_revision' => true,
                ]);
            }

            RiskMonitoringNote::create([
                'risiko_id' => $monitoring->risiko_id,
                'type' => 2,
                'user_id' => Auth::id(),
                'status' => $validated['status_verifikasi'] == 'terima' ? 1 : 0,
                'notes' => $validated['notes'],
                'quarter' => $monitoring->quarter,
                'month' => $monitoring->month,
                'year' => $monitoring->tahun,
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

    private function cleanRupiah($value) {
        return (float) str_replace(['Rp', '.', ','], ['', '', ''], $value);
    }

    private function checkUserEligibility($user, $stepInfo)
    {
        if ($stepInfo['level'] != $user->level_id) return false;
        if (isset($stepInfo['permission']) && !Gate::check($stepInfo['permission'])) return false;
        if (isset($stepInfo['unit_mr'])) {
            $isUserUnitMR = (bool)($user->unit && $user->unit->unit_mr);
            if ($stepInfo['unit_mr'] !== $isUserUnitMR) return false;
        }
        return true;
    }
}
