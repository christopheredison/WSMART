<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\Draft;
use App\Models\JenisKontrolEksisting;
use App\Models\JenisRisiko;
use App\Models\KontrolEksisting;
use App\Models\MasterKRI;
use App\Models\PenilaianEfektivitasKontrol;
use App\Models\Periode;
use App\Models\PeristiwaRisiko;
use App\Models\Project;
use App\Models\ProjectPeriodeList;
use App\Models\ProjectRisk;
use App\Models\ProjectRiskAnalisa;
use App\Models\ProjectRiskContext;
use App\Models\RiskMap;
use App\Models\SkalaProbabilitas;
use App\Models\PenyebabRisikoProject;
use App\Models\PerlakuanPenyebabRisiko;
use App\Models\TaksonomiRisiko;
use App\Models\WBS;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\AreaDampak;
use App\Models\AreaDampakDetail;
use App\Models\LossEventProject;
use App\Models\Jabatan;
use App\Models\SkalaParameter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Imports\ProjectTenderImport;
use App\Imports\TenderTemplateImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Models\SasaranProyek;
use App\Models\DataBatch;
use App\Models\RiskNote;
use App\Models\ApprovalLog;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\DataBatchNotes;
use App\Supports\ApiHC;
use App\Supports\ApiWika;
use App\Models\PerlakuanDampakRisiko;


class ProjectRiskController extends BasicCRUDController
{
    protected $model = ProjectRisk::class;
    protected $basePermission = 'project_risk';
    protected $resourceName = 'Risiko Proyek';
    protected $baseRoute = 'projects.risks.';
    protected $createType = 'link';
    protected $editType = 'link';

    protected $tableColumns = [
        'created_at' => [
            'label' => 'Tanggal',
            'data' => 'created_at',
            'render' => '(data, type, row) => {
                if (!row.created_at) return "-";
                const date = new Date(row.created_at);
                const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                const pad = (num) => String(num).padStart(2, "0");
                return `${pad(date.getDate())} ${months[date.getMonth()]} ${date.getFullYear()} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
            }',
        ],
        'peristiwa_risiko' => [
            'label' => 'Peristiwa Risiko',
            'data' => 'peristiwaRisiko.title',
            'render' => '(data, type, row) => {
                return row.peristiwa_risiko_id === 0 ? row.rencana_kegiatan : (row.peristiwa_risiko?.title ?? row.peristiwa_risiko ?? "-");
            }',
            'class' => 'mw-10r',
        ],
        'deskripsi_peristiwa_risiko' => [
            'label' => 'Deskripsi Peristiwa Risiko',
            'data' => 'deskripsi_peristiwa_risiko',
            'render' => '(data, type, row) => data || "-"',
            'class' => 'mw-20r',
        ],
        // --- INHERENT ---
        'nilai_dampak' => [
            'label' => 'Nilai Dampak Inheren',
            'data' => 'projectRiskAnalisa.nilai_dampak',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                const val = row.project_risk_analisa?.nilai_dampak;
                return val ? "Rp " + parseInt(val).toLocaleString("id-ID") : "-";
            }',
            'class' => 'white-space-nowrap text-end'
        ],
        'skala_dampak' => [
            'label' => 'Skala Dampak Inheren',
            'data' => 'projectRiskAnalisa.skala_dampak',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                const obj = row.project_risk_analisa?.skala_dampak_obj || row.project_risk_analisa?.skalaDampakObj;
                return obj ? `(${obj.tingkat}) ${obj.deskripsi}` : "-";
            }',
        ],
        'skala_probabilitas' => [
            'label' => 'Skala Probabilitas Inheren',
            'data' => 'projectRiskAnalisa.skalaProbabilitas.tingkat',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                const obj = row.project_risk_analisa?.skala_probabilitas || row.project_risk_analisa?.skalaProbabilitas;
                return obj ? `(${obj.tingkat}) ${obj.skala}` : "-";
            }',
        ],
        'eksposur_risiko' => [
            'label' => 'Eksposur Risiko Inheren',
            'data' => 'projectRiskAnalisa?.eksposur_risiko',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                const val = row.project_risk_analisa?.eksposur_risiko;
                return val ? "Rp " + parseInt(val).toLocaleString("id-ID") : "-";
            }',
            'class' => 'white-space-nowrap text-end'
        ],
        'level_risiko' => [
            'label' => 'Level Risiko Inheren',
            'data' => 'level_risiko',
            'sortable' => false,
            'searchable' => false,
            'class' => 'text-center align-middle white-space-nowrap',
            'render' => '(data, type, row) => {
                const level = row.project_risk_analisa?.level_risiko || row.level_risiko;
                const skala = row.project_risk_analisa?.skala_risiko || "-";
                return level ? `${level} - ${skala}` : "-";
            }',
            'createdCell' => 'function (td, cellData, rowData, row, col) {
                const level = rowData.project_risk_analisa?.level_risiko || rowData.level_risiko;
                if (level) {
                    const colorClass = "bg-" + level.toLowerCase().replace(/to\s+/g, "").replace(/\s+/g, "-");
                    $(td).addClass(colorClass).addClass("text-white");
                }
            }'
        ],
        // --- RESIDUAL ---
        'nilai_dampak_residual' => [
            'label' => 'Nilai Dampak Residual',
            'data' => 'projectRiskAnalisa?.nilai_dampak_residual',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                const val = row.project_risk_analisa?.nilai_dampak_residual;
                return val ? "Rp " + parseInt(val).toLocaleString("id-ID") : "-";
            }',
            'class' => 'white-space-nowrap text-end'
        ],
        'skala_dampak_residual' => [
            'label' => 'Skala Dampak Residual',
            'data' => 'projectRiskAnalisa?.skala_dampak_residual',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                const obj = row.project_risk_analisa?.skala_dampak_residual_obj || row.project_risk_analisa?.skalaDampakResidualObj;
                return obj ? `(${obj.tingkat}) ${obj.deskripsi}` : "-";
            }',
        ],
        'skala_probabilitas_residual' => [
            'label' => 'Skala Probabilitas Residual',
            'data' => 'projectRiskAnalisa?.skalaProbabilitasResidual?.tingkat',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                const obj = row.project_risk_analisa?.skala_probabilitas_residual || row.project_risk_analisa?.skalaProbabilitasResidual;
                return obj ? `(${obj.tingkat}) ${obj.skala}` : "-";
            }',
        ],
        'eksposur_risiko_residual' => [
            'label' => 'Eksposur Risiko Residual',
            'data' => 'projectRiskAnalisa?.eksposur_risiko_residual',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                const val = row.project_risk_analisa?.eksposur_risiko_residual;
                return val ? "Rp " + parseInt(val).toLocaleString("id-ID") : "-";
            }',
            'class' => 'white-space-nowrap text-end'
        ],
        'level_risiko_residual' => [
            'label' => 'Level Risiko Residual',
            'data' => null,
            'sortable' => false,
            'searchable' => false,
            'class' => 'text-center align-middle white-space-nowrap',
            'render' => '(data, type, row) => {
                const level = row.project_risk_analisa?.level_risiko_residual;
                const skala = row.project_risk_analisa?.skala_risiko_residual || "-";
                return level ? `${level} - ${skala}` : "-";
            }',
            'createdCell' => 'function (td, cellData, rowData, row, col) {
                const level = rowData.project_risk_analisa?.level_risiko_residual;
                if (level) {
                    const colorClass = "bg-" + level.toLowerCase().replace(/to\s+/g, "").replace(/\s+/g, "-");
                    $(td).addClass(colorClass).addClass("text-white");
                }
            }'
        ],
        'status_risiko' => [
            'label' => 'Status Approval',
            'data' => 'status',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                if (data === 0 || data === 1) return "Draft";
                if (data === 2) return "On Review";
                if (data === 3) {
                    let canVerify = false;
                    if (row.step_verification === 2) return "Accepted by Risk Owner Project";
                    if (row.step_verification === 3) return "Accepted by Risk Officer Divisi";
                    if (row.step_verification === 4) return "Accepted by Risk Officer MR";
                    return "Accepted";
                }
                if (data === 4) {
                    return "Accepted by Risk Owner MR";
                }
                if (data === 5) {
                    const stepVerification = row.step_verification;
                    let rejectedText = "Need Revision or Rejected";

                    if (stepVerification == 1) {
                      rejectedText = "Rejected by Risk Owner Proyek";
                    } else if (stepVerification == 2) {
                      rejectedText = "Rejected by Risk Officer Divisi";
                    } else if (stepVerification == 3) {
                      rejectedText = "Rejected by Risk Officer MR";
                    } else if (stepVerification == 4) {
                      rejectedText = "Rejected by Risk Owner MR";
                    }
                    return rejectedText;
                }
                if (data === 6) {
                    return "Published";
                }
                if (data === 6) {
                    statusText = "Published";
                    if (row.request_edit == 1) statusText += " (Request Edit)";
                    else if (row.request_edit == 3) statusText += " (Request Edit Ditolak)";
                }
                if (data === 7) {
                    return "Rejected by Risk Officer MR";
                }
                if (data === 8) {
                    return "Rejected by Risk Owner MR";
                }
                return "-";
            }',
        ],
        'is_closed' => [
            'label' => 'Status Risiko',
            'data' => 'is_closed',
            'sortable' => false,
            'searchable' => false,
            'class' => 'text-center align-start',
            'render' => '(data, type, row) => {
                // Cek nilai is_closed (biasanya 1 untuk closed, 0 untuk open)
                return (row.is_closed == 1)
                    ? `<div class="badge bg-danger rounded-pill px-2 mt-auto">
                        Closed
                      </div>`
                    : `<div class="badge bg-success rounded-pill px-2 mt-auto">
                        Open
                      </div>`;
            }',
        ],
    ];

    public function index() {
        $projectPeriodeListId = request()->route('project');
        $projectPeriodeList = ProjectPeriodeList::findOrFail($projectPeriodeListId);
        $projectId = $projectPeriodeList->project_id;
        $periodeId = 0;
        $batchNotes = null;
        $user = request()->user();
        $levelId = $user->level_id;

        $dataBatch = DataBatch::where('project_id', $projectId)
                      ->where('periode_id', $periodeId)
                      ->where('type', 2)
                      ->where('finish', false)
                      ->orderBy('batch', 'desc')
                      ->first();

        if(!$dataBatch){
            $dataBatch = DataBatch::create([
                'project_id' => $projectId,
                'periode_id' => $periodeId,
                'type' => 2,
                'status' => DataBatch::STATUS_PROSES,
                'step_verification' => 0,
                'finish' => false
            ]);
        }
        $status = $dataBatch->status;

        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = $this->getUserVerificationStep($levelId, $is_mr);
        $u_step = $verificationData['u_step'];
        $b_step = $dataBatch->step_verification;

        $this->baseRouteParams = ['project' => request()->route('project')];
        $this->indexSubtitle = $projectPeriodeList->project->project_name;

        // Cek sudah ada Risk Context belum
        $riskContext = ProjectRiskContext::where('project_id', $projectId)->first();
        if (!$riskContext || $riskContext->status != ProjectRiskContext::STATUS_VERIFIED) {
            return redirect()->route('project-periode-list.index')->with('error', 'Silahkan buat Risk Context terlebih dahulu pada Project ' . $projectPeriodeList->project->project_name . '.');
        }

        $this->callbackQuery = function($query) use ($projectPeriodeListId) {
            $query->leftJoin('project_risk_analisas', 'project_risk_analisas.risiko_id', '=', 'project_risks.id')
                ->where('project_risks.project_periode_list_id', $projectPeriodeListId)
                ->with([
                    'peristiwaRisiko',
                    'projectRiskAnalisa.skalaProbabilitas',
                    'projectRiskAnalisa.skalaProbabilitasResidual',
                    'projectRiskAnalisa.skalaDampakObj',
                    'projectRiskAnalisa.skalaDampakResidualObj',
                ])
                ->orderBy('project_risks.is_closed', 'asc')
                ->orderBy('project_risk_analisas.skala_risiko', 'desc')
                ->orderBy('project_risk_analisas.eksposur_risiko', 'desc');
        };

        $this->availableFilters = [
            'peristiwa_risiko_id' => [
                'label' => 'Peristiwa Risiko',
                'type' => 'select',
                'parameters' => [
                    'peristiwa_risiko_id',
                    PeristiwaRisiko::select('id', 'title')->orderBy('title')->get()->pluck('title', 'id')->toArray(),
                    '',
                    ['class' => 'form-select select2', 'placeholder' => 'Peristiwa Risiko']
                ],
            ],
            'project_risk_analisas.level_risiko' => [
                'label' => 'Level Risiko',
                'type' => 'select',
                'parameters' => [
                    'project_risk_analisas.level_risiko',
                    [
                        ProjectRisk::LEVEL_RISIKO_LOW => 'Low',
                        ProjectRisk::LEVEL_RISIKO_LOW_TO_MODERATE => 'Low to Moderate',
                        ProjectRisk::LEVEL_RISIKO_MODERATE => 'Moderate',
                        ProjectRisk::LEVEL_RISIKO_MODERATE_TO_HIGH => 'Moderate to High',
                        ProjectRisk::LEVEL_RISIKO_HIGH => 'High',
                    ],
                    '',
                    ['class' => 'form-select select2 js-select-hide-search', 'placeholder' => 'Level Risiko']
                ],
            ],
        ];

        // --- BUTTON ACTIONS ---
        if (Gate::check('project_risk_edit')) {
            // Default Action: View
            $this->tableLegend = [['icon' => '<span class="bx bx-show-alt"></span>', 'label' => 'View']];
            $this->tableActions[] = ['label' => '<span class="bx bx-show-alt" title="View"></span>', 'btn_icon' => true, 'action' => 'link', 'url' => route('projects.risks.view', ['project' => request()->route('project'), 'risk' => ':id']), 'title' => 'View Risiko'];

            // CASE 1: RISK OFFICER PROJECT (Draft/Revisi)
            if (($status == DataBatch::STATUS_PROSES || $status == DataBatch::STATUS_REVISI) && $levelId == 6) {
                // Edit Info dan Rencana bisa diakses saat Draft/Revisi (1, 5) ATAU jika Unlocked Edit (request_edit == 2)
                $active_state_edit = 'function(id, type, row) { return row.status === 1 || row.status === 5; }';

                // Analisa HANYA bisa jika tidak dalam mode Unlocked Edit (request_edit != 2)
                $active_state_analisa = 'function(id, type, row) { return (row.status === 1 || row.status === 5) && row.request_edit != 2; }';

                $this->tableLegend = array_merge($this->tableLegend, [
                    ['icon' => '<span class="bx bx-analyse text-warning"></span>', 'label' => 'Analisa'],
                    ['icon' => '<span class="bx bx-task text-primary"></span>', 'label' => 'Perencanaan'],
                    ['icon' => '<span class="bx bx-edit"></span>', 'label' => 'Edit']
                ]);
                $this->tableActions[] = ['label' => '<span class="bx bx-analyse text-warning"></span>', 'btn_icon' => true, 'action' => 'link', 'url' => route('projects.risks.analisa', ['project' => request()->route('project'), 'risk' => ':id']), 'title' => 'Analisa Risiko', 'active_state' => $active_state_analisa];
                $this->tableActions[] = ['label' => '<span class="bx bx-task text-primary"></span>', 'btn_icon' => true, 'action' => 'link', 'url' => route('projects.risks.rencana', ['project' => request()->route('project'), 'risk' => ':id']), 'title' => 'Rencana Perlakuan Risiko', 'active_state' => $active_state_edit];
                $this->tableActions[] = ['label' => '<span class="bx bx-edit"></span>', 'btn_icon' => true, 'action' => 'edit', 'permissions' => ['project_risk_edit'], 'active_state' => $active_state_edit];
            }

            // --- TAMBAHAN TOMBOL REQUEST EDIT & APPROVE ---
            // Tombol Request Edit untuk Risk Officer Project (Level 6) untuk Risiko Published
            if ($levelId == 6) {
                $this->tableActions[] = [
                    'label' => '<span class="bx bx-message-square-edit text-info"></span>',
                    'btn_icon' => true,
                    'action' => 'script',
                    'script' => 'showRequestEditModal($(this).data("id"))',
                    'title' => 'Request Edit Risiko',
                    // 'active_state' => 'function(id, type, row) { return row.status === 6 && row.request_edit != 1 && row.request_edit != 2; }'
                    // Tombol muncul jika status Published, DAN request_edit BUKAN 1 (Pending) atau 2 (Approved)
                    'active_state' => 'function(id, type, row) { return row.status === 6 && row.request_edit !== 1 && row.request_edit !== 2; }'
                ];
                $this->tableLegend[] = ['icon' => '<span class="bx bx-message-square-edit text-info"></span>', 'label' => 'Request Edit Risiko'];
            }

            // Tombol Approve Request Edit untuk Risk Owner MR (Level 2 + MR)
            if ($levelId == 2 && $is_mr) {
                $this->tableActions[] = [
                    'label' => '<span class="bx bx-check-double text-success"></span>',
                    'btn_icon' => true,
                    'action' => 'script',
                    'script' => 'approveRequestEdit($(this).data("id"))',
                    'title' => 'Setujui Request Edit',
                    'active_state' => 'function(id, type, row) { return row.status === 6 && row.request_edit == 1; }'
                ];
                $this->tableLegend[] = ['icon' => '<span class="bx bx-check-double text-success"></span>', 'label' => 'Setujui Request Edit'];
            }


            // CASE 2: VERIFIKATOR (Termasuk Pengembalian MR)
            if (true) {
                $active_state = <<<JS
                    function(id, type, row) {
                        const validStatus = [2, 3, 7, 8];
                        return validStatus.includes(parseInt(row.status)) && row.step_verification == {$u_step};
                    }
                JS;

                $this->tableActions[] = [
                    'label' => '<span class="bx bx-check-shield text-success"></span>',
                    'btn_icon' => true,
                    'action' => 'verifikasi',
                    'title' => 'Verifikasi Risiko',
                    'active_state' => $active_state,
                ];
                $this->tableLegend[] = ['icon' => '<span class="bx bx-check-shield text-success"></span>', 'label' => 'Verifikasi Risiko'];
            }

            $isInputter = ($status == DataBatch::STATUS_PROSES || $status == DataBatch::STATUS_REVISI) && $levelId == 6;
            $hasDeletePermission = Gate::check('project_risk_delete_admin');
            $deleteJsLogic = $hasDeletePermission ? 'true' : ($isInputter && Gate::check('project_risk_delete') ? 'row.status == 0 || row.status == 1 || row.status == 5' : 'false');

            $this->tableLegend[] = ['icon' => '<span class="bx bx-trash text-danger"></span>', 'label' => 'Hapus'];
            $this->tableActions[] = ['label' => '<span class="bx bx-trash text-danger"></span>', 'btn_icon' => true, 'action' => 'delete', 'url' => route('projects.risks.destroy', ['project' => request()->route('project'), 'risk' => ':id']), 'title' => 'Hapus', 'active_state' => "(data, type, row) => $deleteJsLogic"];
        }

        $this->tableActions[] = [
            'label' => '<span class="bx bx-comment-dots"></span>',
            'btn_icon' => true,
            'action' => 'script',
            'script' => 'showCatatanRisiko($(this).data("id"))',
            'title' => 'Lihat Catatan',
            'active_state' => 'function(id, type, row) { return true; }',
        ];
        $this->tableLegend[] = [
            'icon' => '<span class="bx bx-comment-dots"></span>',
            'label' => 'Lihat Catatan'
        ];

        $escalationConfig = [
            'show' => false,
            'label' => 'Kirim Risiko',
            'disabled' => true,
            'route' => route('projects.risks.send'),
            'parameters' => [
                'project_id' => $projectPeriodeListId,
                'send_type' => 'risiko',
            ]
        ];
        $pending_risk = 0;

        // Hitung Pending Risk
        if ($u_step >= 1) {
            $pending_risk = ProjectRisk::where('project_periode_list_id', $projectPeriodeListId)
                ->where('step_verification', $u_step)
                ->whereIn('status', [2, 3, 7, 8]) // 2=Dikirim, 3=Tunggu, 7=TolakRO, 8=TolakOwner
                ->count();
        } else if ($levelId == 6 && $status == DataBatch::STATUS_REVISI) {
            $pending_risk = ProjectRisk::where('project_periode_list_id', $projectPeriodeListId)
                ->where('status_progress', 2)->count();
        }

        $routeUrl = route('projects.risks.send');
        $csrfToken = csrf_token();
        $disabledAttr = 'disabled';
        $viewAttr = " style='display:none'";
        $buttonText = 'Kirim Risiko';
        $sendType = 'risiko';

        // --- LOGIKA UTAMA TOMBOL FOOTER ---
        if (($status == DataBatch::STATUS_PROSES || $status == DataBatch::STATUS_REVISI) && $levelId == 6) {
            $disabledAttr = '';
            $viewAttr = "";
            if ($status == DataBatch::STATUS_REVISI) {
                $buttonText = 'Kirim Perbaikan';
                $sendType = 'perbaikan';
                $escalationConfig['parameters']['send_type'] = 'perbaikan';
            }
        }
        else if ($u_step == $b_step) {
            $escalationConfig['show'] = true;
            $escalationConfig['route'] = route('projects.risks.eskalasi');

            if ($status == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR) {
                $escalationConfig['label'] = 'Kirim Perbaikan (Ke MR)';
                $escalationConfig['parameters']['send_type'] = 'perbaikan';
            }

            $validBatchStatus = [
                DataBatch::STATUS_KIRIM, // 2
                DataBatch::STATUS_VERIFIKASI, // 4
                DataBatch::STATUS_REJECTED_FROM_OFFICER_MR // 9
            ];

            if (in_array($status, $validBatchStatus)) {
                $disabledAttr = '';
                $viewAttr = "";
                $routeUrl = route('projects.risks.eskalasi');

                if ($status == 4 && $u_step >= 4) {
                    $buttonText = "Publish Risiko";
                }
            }
        }
        else if ($u_step == 2 && $status == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR) {
            $disabledAttr = '';
            $viewAttr = "";
            $routeUrl = route('projects.risks.eskalasi');
            $buttonText = 'Kirim Perbaikan (Ke MR)';
            $sendType = 'perbaikan';
            $escalationConfig['parameters']['send_type'] = 'perbaikan';
        }

        $projectRiskIds = ProjectRisk::where('project_periode_list_id', $projectPeriodeListId)->pluck('id');
        $averageExposure = (float) ProjectRiskAnalisa::whereIn('risiko_id', $projectRiskIds)
            ->where('kategori_dampak', ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF)
            ->avg('eksposur_risiko');
        $formattedAverageExposure = 'Rp ' . number_format($averageExposure, 0, ',', '.');

        $this->tableLegend[] = [
            'icon' => '<span class="badge bg-primary">!</span>',
            'label' => 'Rekomendasi Risiko'
        ];
        $this->tableColumns['peristiwa_risiko']['render'] = <<< JS
            (data, type, row) => {
                let add = '';
                if (row.project_risk_analisa?.kategori_dampak === 'Kuantitatif' && row.project_risk_analisa?.eksposur_risiko >= $averageExposure) {
                    add = '<span class="badge bg-primary" data-bs-toggle="tooltip" title="Rekomendasi Risiko di atas rata-rata Eksposure Risiko">!</span> ';
                } else if (row.project_risk_analisa?.kategori_dampak === 'Kualitatif' && row.skala_risiko >= 20) {
                    add = '<span class="badge bg-primary" data-bs-toggle="tooltip" title="Rekomendasi Risiko di atas rata-rata Eksposure Risiko">!</span> ';
                }
                return add + (row.peristiwa_risiko_id === 0 ? row.rencana_kegiatan : (row.peristiwa_risiko?.title || row?.peristiwa_risiko || '-'));
            }
        JS;
        $this->tableColumns['nilai_dampak']['render'] = <<<JS
            (data, type, row) => {
                const formatCurrency = (value) => {
                    if (value === null || value === '') return '-';
                    return 'Rp ' + parseInt(value).toLocaleString('id-ID', { minimumFractionDigits: 0 });
                };
                return formatCurrency(row.project_risk_analisa?.nilai_dampak);
            }
        JS;

        $summaryInfo = null;
        $stepLabels = [
            1 => 'Risk Owner Project',
            2 => 'Risk Officer Divisi',
            3 => 'Risk Officer MR',
            4 => 'Risk Owner MR',
        ];

        // LOGIKA BUTTON FOOTER (INPUTTER)
        if ($levelId == 6) {
            $escalationConfig['show'] = true;

            $risksDraft = ProjectRisk::where('project_periode_list_id', $projectPeriodeListId)->where('status', 1)->count();
            $risksRevised = ProjectRisk::where('project_periode_list_id', $projectPeriodeListId)->where('status', 5)->count();

            if ($status == DataBatch::STATUS_REVISI) {
                $escalationConfig['label'] = 'Kirim Perbaikan';
                $escalationConfig['parameters']['send_type'] = 'perbaikan';

                if ($risksRevised > 0) {
                    $summaryInfo = [
                        'type' => 'danger', 'icon' => 'bx-undo',
                        'message' => "Terdapat <strong>{$risksRevised}</strong> risiko yang <strong>dikembalikan (revisi)</strong>. Mohon perbaiki data."
                    ];
                    $escalationConfig['disabled'] = false;
                } else {
                    $summaryInfo = [
                        'type' => 'success', 'icon' => 'bx-check-double',
                        'message' => "Seluruh perbaikan telah selesai. Silahkan klik tombol <strong>Kirim Perbaikan</strong> untuk melanjutkan."
                    ];
                    $escalationConfig['disabled'] = false;
                }
            }
            else if ($status == DataBatch::STATUS_PROSES) {
                if ($risksDraft > 0) {
                    $summaryInfo = [
                        'type' => 'success', 'icon' => 'bx-check-double',
                        'message' => "Data risiko siap dikirim. Silahkan klik tombol <strong>Kirim Risiko</strong> untuk melanjutkan ke Risk Owner Project."
                    ];
                    $escalationConfig['disabled'] = false;
                } else {
                    $summaryInfo = [
                        'type' => 'info', 'icon' => 'bx-info-circle',
                        'message' => "Belum ada data risiko. Silahkan tambah risiko baru."
                    ];
                    $escalationConfig['disabled'] = true;
                }
            } else {
                $escalationConfig['show'] = false;
            }
        }
        // LOGIKA BUTTON FOOTER (VERIFIKATOR)
        else if (
            ($u_step == $b_step) ||
            ($u_step == 2 && $status == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR)
        ) {
            $escalationConfig['show'] = true;
            $escalationConfig['route'] = route('projects.risks.eskalasi');

            $pendingRiskCount = ProjectRisk::where('project_periode_list_id', $projectPeriodeListId)
                ->where('status', '!=', ProjectRisk::STATUS_PUBLISHED)
                ->where(function($query) use ($u_step) {
                    $query->where('step_verification', $u_step)
                          ->where('status', '!=', ProjectRisk::STATUS_TERVERIFIKASI);
                })
                ->count();

            $isRejectionMode = ($status == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR || $status == DataBatch::STATUS_REJECTED_FROM_OWNER_MR);
            $rejectorLabel = '';

            if ($isRejectionMode) {
                $escalationConfig['label'] = 'Kirim Perbaikan (Ke ' . ($status == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR ? 'Risk Officer MR' : 'Risk Owner MR') . ')';
                $escalationConfig['parameters']['send_type'] = 'perbaikan';
                $rejectorLabel = $status == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR ? 'Risk Officer Manajemen Risiko' : 'Risk Owner Manajemen Risiko';
            } else {
                $nextLabel = $stepLabels[$u_step + 1] ?? 'Selesai';
                $escalationConfig['label'] = ($u_step == 4) ? 'Publish Risiko' : "Kirim ke {$nextLabel}";
            }

            if ($pendingRiskCount > 0) {
                $escalationConfig['disabled'] = true;
                if ($isRejectionMode) {
                    $summaryInfo = [
                        'type' => 'danger', 'icon' => 'bx-undo',
                        'message' => "Terdapat <strong>{$pendingRiskCount}</strong> risiko yang <strong>dikembalikan oleh {$rejectorLabel}</strong>. Mohon verifikasi ulang (Terima/Tolak)."
                    ];
                } else {
                    $senderLabel = $stepLabels[$u_step - 1] ?? 'Risk Officer Project';
                    $summaryInfo = [
                        'type' => 'warning', 'icon' => 'bxs-error-circle',
                        'message' => "Terdapat <strong>{$pendingRiskCount}</strong> risiko aktif dari <strong>{$senderLabel}</strong> menunggu verifikasi Anda."
                    ];

                    if ($status === DataBatch::STATUS_REVISI) {
                      $escalationConfig['show'] = false;
                      $summaryInfo = null;
                    }
                }
            } else {
                $escalationConfig['disabled'] = false;
                $btnName = $escalationConfig['label'];
                $summaryInfo = [
                    'type' => 'success', 'icon' => 'bx-check-double',
                    'message' => "Seluruh risiko telah diverifikasi. Silahkan klik tombol <strong>{$btnName}</strong> untuk melanjutkan."
                ];
            }
        }

        $this->cardFooter = <<<HTML
            <div class="d-flex flex-column">
                <div>
                    <strong>Rata-rata Eksposure Risiko (Kuantitatif):</strong>
                    <span id="average-risk-value">{$formattedAverageExposure}</span>
                </div>
            </div>
        HTML;

        $catatanRoute = route('projects.risks.notes', ['project' => request()->route('project'), 'risk' => ':id']);
        $bulkRoute = route("projects.risks.bulk-verifikasi", ["project" => $projectPeriodeListId]);

        $submitRequestRoute = route('projects.risks.submit-request-edit');
        $approveRequestRoute = route('projects.risks.approve-request-edit');
        $rejectRequestRoute = route('projects.risks.reject-request-edit');

        // CEK APAKAH ADA PERMINTAAN AUTO-VERIFY DARI URL
        $autoVerifyJs = '';
        $autoVerifyId = request()->query('verify_request_edit');

        if ($autoVerifyId) {
            // agar jika page ter-reload, SweetAlert tidak muncul lagi.
            $autoVerifyJs .= "
                $(document).ready(function() {
                    if (typeof clearUrlParam === 'function') {
                        clearUrlParam();
                    }
                });
            ";

            if ($levelId == 2 && $is_mr) {
                $riskToVerify = ProjectRisk::with('peristiwaRisiko')->find($autoVerifyId);

                if ($riskToVerify && $riskToVerify->request_edit == 1) {
                    $reasonSafe = json_encode($riskToVerify->request_edit_reason);

                    $riskNameStr = $riskToVerify->peristiwa_risiko_id == 0
                        ? $riskToVerify->rencana_kegiatan
                        : ($riskToVerify->peristiwaRisiko->title ?? 'Risiko Proyek');
                    $riskNameSafe = json_encode($riskNameStr);

                    $autoVerifyJs .= "
                        $(document).ready(function() {
                            setTimeout(function() {
                                approveRequestEdit({$autoVerifyId}, {$reasonSafe}, {$riskNameSafe});
                            }, 700);
                        });
                    ";
                }
            }
        }

        // INJEKSI SCRIPT JAVASCRIPT & MODAL REQUEST EDIT
        $this->extraScripts[] = <<<SCRIPT
            <div class="modal fade" id="modalRequestEdit" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content p-0">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title text-white">Request Edit Risiko</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="request_risk_id">
                            <div class="alert alert-info mb-3">
                                Risiko ini telah di-Publish. Silakan ajukan request jika perlu melakukan perubahan. Request akan dikirimkan ke <strong>Risk Owner MR</strong>.
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold">Alasan Perubahan Data Risiko <span class="text-danger">*</span></label>
                                <textarea id="request_reason" class="form-control" rows="4" placeholder="Tuliskan alasan yang jelas mengapa risiko ini perlu diubah..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-info" onclick="submitRequestEdit()"><span class="bx bx-send me-1"></span>Kirim Request</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function showRequestEditModal(id) {
                $('#request_risk_id').val(id);
                $('#request_reason').val('');
                $('#modalRequestEdit').modal('show');
            }

            function submitRequestEdit() {
                const id = $('#request_risk_id').val();
                const reason = $('#request_reason').val().trim();

                if(!reason) {
                    Swal.fire('Peringatan', 'Alasan perubahan wajib diisi!', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Kirim Request?',
                    text: 'Request edit risiko akan dikirimkan ke Risk Owner MR.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Kirim',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if(result.isConfirmed) {
                        Swal.fire({title: 'Memproses...', didOpen: () => {Swal.showLoading()}});
                        $.ajax({
                            url: '{$submitRequestRoute}',
                            type: 'POST',
                            data: {
                                _token: '{$csrfToken}',
                                risk_id: id,
                                reason: reason
                            },
                            success: function(res) {
                                $('#modalRequestEdit').modal('hide');
                                Swal.fire('Berhasil', res.message, 'success').then(() => {
                                    $('.ajax-datatable').DataTable().ajax.reload(null, false);
                                });
                            },
                            error: function(err) {
                                Swal.fire('Gagal', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                            }
                        });
                    }
                });
            }

            function clearUrlParam() {
                if (window.location.search.includes('verify_request_edit')) {
                    const url = new URL(window.location);
                    url.searchParams.delete('verify_request_edit');
                    window.history.replaceState({}, '', url);
                }
            }

            // Menerima parameter kedua (manualReason) jika ditrigger dari URL
            function approveRequestEdit(id, manualReason = null, manualRiskName = null) {
                const table = $('.ajax-datatable').DataTable();
                const rowData = table.rows().data().toArray().find(r => r.id == id);

                let reason = 'Tidak ada informasi alasan.';
                let riskName = 'Risiko Proyek';

                // Tentukan Alasan
                if (manualReason !== null) {
                    reason = manualReason; // Pakai alasan dari backend jika auto-trigger
                } else if (rowData && rowData.request_edit_reason) {
                    reason = rowData.request_edit_reason; // Pakai data Datatable jika manual klik
                }

                // Tentukan Nama Risiko
                if (manualRiskName !== null) {
                    riskName = manualRiskName; // Dari auto-trigger URL
                } else if (rowData) {
                    // Dari klik tombol Datatable
                    if (rowData.peristiwa_risiko_id == 0) {
                        riskName = rowData.rencana_kegiatan || 'Risiko Proyek';
                    } else {
                        riskName = (rowData.peristiwa_risiko?.title) ? rowData.peristiwa_risiko.title : (rowData.peristiwa_risiko || 'Risiko Proyek');
                    }
                }

                Swal.fire({
                    title: 'Tindak Lanjut Request Edit',
                    html: `Apakah Anda ingin menyetujui atau menolak request edit untuk risiko <strong>\${riskName}</strong> ini?<br><br>` +
                          '<div class="p-3 mt-2 rounded bg-light border border-info text-start">' +
                              '<strong>Alasan Request Edit:</strong><br>' +
                              '<span class="text-dark">' + reason + '</span>' +
                          '</div>',
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: '<span class="bx bx-check"></span> Setujui',
                    denyButtonText: '<span class="bx bx-x"></span> Tolak',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-success me-2',
                        denyButton: 'btn btn-danger me-2',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({title: 'Menyetujui...', didOpen: () => {Swal.showLoading()}});
                        $.ajax({
                            url: '{$approveRequestRoute}',
                            type: 'POST',
                            data: { _token: '{$csrfToken}', risk_id: id },
                            success: function(res) {
                                Swal.fire('Berhasil', res.message, 'success').then(() => {
                                    clearUrlParam(); // Bersihkan URL agar tidak auto-trigger lagi saat refresh
                                    table.ajax.reload(null, false);
                                });
                            },
                            error: function(err) {
                                Swal.fire('Gagal', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                            }
                        });
                    } else if (result.isDenied) {
                        Swal.fire({
                            title: 'Konfirmasi Penolakan',
                            text: 'Yakin ingin menolak request ini? Status akan tetap Published.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Tolak',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#dc3545'
                        }).then((denyResult) => {
                            if (denyResult.isConfirmed) {
                                Swal.fire({title: 'Menolak...', didOpen: () => {Swal.showLoading()}});
                                $.ajax({
                                    url: '{$rejectRequestRoute}',
                                    type: 'POST',
                                    data: { _token: '{$csrfToken}', risk_id: id },
                                    success: function(res) {
                                        Swal.fire('Ditolak', res.message, 'info').then(() => {
                                            clearUrlParam(); // Bersihkan URL
                                            table.ajax.reload(null, false);
                                        });
                                    },
                                    error: function(err) {
                                        Swal.fire('Gagal', err.responseJSON?.message || 'Terjadi kesalahan', 'error');
                                    }
                                });
                            }
                        });
                    }
                });
            }

            function submitEskalasiForm(formId, actionText) {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin melakukan "' + actionText + '"?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Mohon tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });
                        $('#' + formId).submit();
                    }
                });
            }

            function showCatatanRisiko(riskId) {
                const modalElement = document.getElementById('modalCatatan');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                const contentDiv = $('#catatan-content');

                contentDiv.html('<div class="d-flex justify-content-center my-4"><div class="spinner-border" role="status"><span class="visually-hidden">Memuat...</span></div></div>');

                const url = "{$catatanRoute}".replace(':id', riskId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(notes) {
                        if (notes.length === 0) {
                            contentDiv.html('<div class="text-center my-4"><i class="fas fa-comment-slash fa-2x text-black mb-2"></i><p>Belum ada catatan untuk risiko ini.</p></div>');
                        } else {
                            let html = '';
                            notes.forEach(note => {
                                // Penyesuaian Badge Status Note
                                let statusBadge = '';
                                if (note.status == 1) {
                                    statusBadge = '<span class="badge bg-success-subtle text-success">Diterima / Disetujui</span>';
                                } else if (note.status == 2) {
                                    statusBadge = '<span class="badge bg-danger-subtle text-danger">Ditolak</span>';
                                } else if (note.status == 3) {
                                    statusBadge = '<span class="badge bg-warning-subtle text-warning">Request Edit</span>';
                                } else {
                                    statusBadge = '<span class="badge bg-secondary-subtle text-secondary">Info</span>';
                                }

                                const formattedDate = new Date(note.created_at).toLocaleString('id-ID', {
                                    day: '2-digit', month: 'short', year: 'numeric',
                                    hour: '2-digit', minute: '2-digit'
                                });

                                html += `
                                <div class="card mb-3 shadow-sm">
                                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                                        <div class="fw-bold">
                                            \${note.user ? note.user.name : 'User Tidak Ditemukan'}
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <small class="text-black me-3">\${formattedDate}</small>
                                            \${statusBadge}
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text mb-0">\${note.notes || '<i>Tidak ada catatan.</i>'}</p>
                                    </div>
                                </div>
                                `;
                            });
                            contentDiv.html(html);
                        }
                        modal.show();
                    },
                    error: function() {
                        contentDiv.html('<div class="text-center my-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Gagal memuat catatan.</p></div>');
                        modal.show();
                    }
                });
            }

            let currentIds = [];
            let isBulkMode = false;

            $(document).on('change', '#check-all-risiko', function() {
                $('.row-checkbox:not(:disabled)').prop('checked', this.checked);
                toggleBulkButton();
            });

            $(document).on('change', '.row-checkbox', function() {
                toggleBulkButton();
            });

            function toggleBulkButton() {
                const checkedCount = $('.row-checkbox:checked').length;
                if (checkedCount > 0) {
                    $('#bulk-verify-container').removeClass('d-none');

                    // Deteksi apakah ada Request Edit yang dicentang
                    let hasRequestEdit = false;
                    $('.row-checkbox:checked').each(function() {
                        const id = $(this).val();
                        if (fetchedData[id] && fetchedData[id].request_edit == 1) {
                            hasRequestEdit = true;
                        }
                    });

                    // UBAH WARNA & TEKS TOMBOL DINAMIS
                    const btn = $('#bulk-verify-container button');
                    if (hasRequestEdit) {
                        btn.removeClass('btn-success').addClass('btn-info')
                           .html(`<span class="bx bx-message-square-edit"></span> Tindak Lanjut Request Edit (<span id="count-checked">\${checkedCount}</span>)`);
                    } else {
                        btn.removeClass('btn-info').addClass('btn-success')
                           .html(`<span class="bx bx-check-shield"></span> Verifikasi Risiko (<span id="count-checked">\${checkedCount}</span>)`);
                    }
                } else {
                    $('#bulk-verify-container').addClass('d-none');
                    $('#check-all-risiko').prop('checked', false);
                }
            }

            function handleBulkVerifikasiClick() {
                currentIds = [];
                let isRequestEditBulk = false;

                $('.row-checkbox:checked').each(function() {
                    const id = $(this).val();
                    currentIds.push(id);
                    if (fetchedData[id] && fetchedData[id].request_edit == 1) {
                        isRequestEditBulk = true;
                    }
                });

                if (isRequestEditBulk) {
                    // MUNCULKAN SWEETALERT LANGSUNG (TANPA MODAL)
                    Swal.fire({
                        title: 'Tindak Lanjut Request Edit',
                        html: `Apakah Anda ingin menyetujui atau menolak <strong>\${currentIds.length}</strong> request edit ini sekaligus?`,
                        icon: 'question',
                        showCancelButton: true,
                        showDenyButton: true,
                        confirmButtonText: '<span class="bx bx-check"></span> Setujui Semua',
                        denyButtonText: '<span class="bx bx-x"></span> Tolak Semua',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'btn btn-success me-2',
                            denyButton: 'btn btn-danger me-2',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitBulkRequestEdit('approve');
                        } else if (result.isDenied) {
                            // Konfirmasi tambahan jika tolak (Sama seperti single)
                            Swal.fire({
                                title: 'Konfirmasi Penolakan',
                                text: 'Yakin ingin menolak semua request ini? Status akan tetap Published.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Tolak Semua',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#dc3545'
                            }).then((denyResult) => {
                                if (denyResult.isConfirmed) {
                                    submitBulkRequestEdit('reject');
                                }
                            });
                        }
                    });
                } else {
                    // BUKA MODAL UNTUK VERIFIKASI NORMAL
                    isBulkMode = true;
                    $('#modal-peristiwa-risiko').closest('.mb-4').addClass('d-none');

                    // Pastikan Modal dalam kondisi default untuk Verifikasi Normal
                    const modalTarget = $('#modalVerifikasiRisiko');
                    modalTarget.find('.modal-title').html('Verifikasi Risiko');
                    document.getElementById('btn-terima-risiko').innerHTML = '<span class="bx bx-check-shield"></span> Terima Risiko';
                    document.getElementById('btn-tolak-risiko').innerHTML = '<span class="bx bx-undo"></span> Kembalikan Risiko';
                    document.getElementById('btn-tolak-risiko').className = 'btn btn-warning';

                    const infoText = `<i class="bx bx-info-circle"></i> Anda akan memverifikasi <strong>\${currentIds.length}</strong> data risiko proyek sekaligus.`;
                    if($('#modal-bulk-info').length == 0) {
                        modalTarget.find('.modal-body').prepend(`<div id="modal-bulk-info" class="alert alert-info mt-0 mb-4">\${infoText}</div>`);
                    } else {
                        $('#modal-bulk-info').html(infoText).removeClass('alert-warning').addClass('alert-info');
                    }

                    const modal = new bootstrap.Modal(document.getElementById('modalVerifikasiRisiko'));
                    modal.show();

                    document.getElementById('btn-terima-risiko').onclick = function() { submitBulk('terima'); };
                    document.getElementById('btn-tolak-risiko').onclick = function() { submitBulk('tolak'); };
                }
            }

            function submitBulk(status) {
                const catatan = $('#catatan-verifikasi').val().trim();

                if (!catatan) {
                    Swal.fire('Peringatan', 'Catatan verifikasi tidak boleh kosong', 'warning');
                    return;
                }

                Swal.fire({
                    title: status === 'terima' ? 'Terima Risiko Terpilih?' : 'Kembalikan Risiko Terpilih?',
                    text: `Memproses \${currentIds.length} data.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan'
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
                            error: function(err) {
                                Swal.fire('Gagal', 'Terjadi kesalahan sistem', 'error');
                            }
                        });
                    }
                });
            }

            function submitBulkRequestEdit(action) {
                const url = action === 'approve' ? '{$approveRequestRoute}' : '{$rejectRequestRoute}';
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{$csrfToken}',
                        risk_id: currentIds // Kirim sebagai Array
                    },
                    success: function(res) {
                        Swal.fire('Berhasil', res.message, 'success').then(() => {
                            clearUrlParam();
                            location.reload();
                        });
                    },
                    error: function(err) {
                        Swal.fire('Gagal', err.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }

            {$autoVerifyJs}
            </script>
        SCRIPT;

        $this->defaultOrder = [[7, 'desc']];

        $this->extraViewData = [
            'status' => $status,
            'levelId' => $levelId,
            'pending_risk' => $pending_risk,
            'u_step' => $u_step,
            'b_step' => $b_step,
            'showBulkCheckbox' => true,
            'showVerifikasiModal' => true,
            'summaryInfo' => $summaryInfo,
            'escalationConfig' => $escalationConfig,
        ];

        return parent::index();
    }

    public function submitRequestEdit(Request $request)
    {
        $request->validate([
            'risk_id' => 'required',
            'reason' => 'required'
        ]);

        $risk = ProjectRisk::with(['peristiwaRisiko', 'project'])->findOrFail($request->risk_id);

        $risk->update([
            'request_edit' => 1,
            'request_edit_reason' => $request->reason
        ]);

        // Simpan Log ke Risk Note
        RiskNote::create([
            'risiko_id' => $risk->id,
            'type' => 2, // 2 = Project
            'status' => 3, // Status 3 Khusus untuk penanda Request Edit
            'notes' => 'Mengajukan Request Edit. Alasan: ' . $request->reason,
            'user_id' => $request->user()->id,
        ]);

        $riskName = $risk->peristiwa_risiko_id == 0
            ? $risk->rencana_kegiatan
            : ($risk->peristiwaRisiko->title ?? 'Risiko Proyek');

        $projectName = $risk->project->project_name ?? 'Proyek Tidak Diketahui';

        $targetLink = route('projects.risks.index', ['project' => $risk->project_periode_list_id]) . '?verify_request_edit=' . $risk->id;

        $this->sendNotificationCustom(
            'RW_MR',
            $risk->project_periode_list_id,
            'Request Edit Risiko',
            'Risk Officer Proyek mengajukan request edit untuk risiko (' . $riskName . ') pada proyek ' . $projectName . '. Alasan: ' . $request->reason,
            $targetLink,
            'bx bx-message-square-edit'
        );

        return response()->json(['message' => 'Request edit berhasil dikirim']);
    }

    public function approveRequestEdit(Request $request)
    {
        $request->validate(['risk_id' => 'required']);

        $riskIds = is_array($request->risk_id) ? $request->risk_id : [$request->risk_id];
        $user = $request->user();

        $catatan = $request->notes ?? 'Menyetujui Request Edit Risiko. Akses telah dibuka (Unlocked).';

        DB::beginTransaction();
        try {
            foreach ($riskIds as $id) {
                $risk = ProjectRisk::with('peristiwaRisiko')->findOrFail($id);
                $risk->update([
                    'status' => ProjectRisk::STATUS_INPUT_DATA, // 1
                    'status_progress' => 1,
                    'step_verification' => 0,
                    'request_edit' => 2 // 2 = Approved / Unlocked mode
                ]);

                // Simpan Log ke Risk Note
                RiskNote::create([
                    'risiko_id' => $risk->id,
                    'type' => 2,
                    'status' => 1, // Status 1 = Diterima/Disetujui
                    'notes' => $catatan,
                    'user_id' => $user->id,
                ]);

                $dataBatch = DataBatch::where('project_id', $risk->project_id)
                        ->where('type', 2)
                        ->where('finish', false)
                        ->orderBy('batch', 'desc')
                        ->first();

                if (!$dataBatch) {
                    DataBatch::create([
                        'project_id' => $risk->project_id,
                        'periode_id' => $risk->periode_id,
                        'type' => 2,
                        'status' => DataBatch::STATUS_PROSES,
                        'step_verification' => 0,
                        'finish' => false
                    ]);
                }

                $riskName = $risk->peristiwa_risiko_id == 0
                    ? $risk->rencana_kegiatan
                    : ($risk->peristiwaRisiko->title ?? 'Risiko Proyek');

                $targetLink = route('projects.risks.index', ['project' => $risk->project_periode_list_id]);

                $this->sendNotificationCustom(
                    'RO_PROYEK',
                    $risk->project_periode_list_id,
                    'Request Edit Disetujui',
                    'Request edit risiko Anda (' . $riskName . ') telah disetujui.',
                    $targetLink,
                    'bx bx-check-double'
                );
            }
            DB::commit();
            return response()->json(['message' => 'Request edit berhasil disetujui.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyetujui request: ' . $e->getMessage()], 500);
        }
    }

    public function rejectRequestEdit(Request $request)
    {
        $request->validate(['risk_id' => 'required']);

        // Ubah input menjadi array
        $riskIds = is_array($request->risk_id) ? $request->risk_id : [$request->risk_id];
        $user = $request->user();

        $catatan = $request->notes ?? 'Menolak Request Edit Risiko.';

        DB::beginTransaction();
        try {
            foreach ($riskIds as $id) {
                $risk = ProjectRisk::with('peristiwaRisiko')->findOrFail($id);
                $risk->update([
                    'request_edit' => 3, // REQ_EDIT_REJECTED
                ]);

                // Simpan Log ke Risk Note
                RiskNote::create([
                    'risiko_id' => $risk->id,
                    'type' => 2,
                    'status' => 2, // Status 2 = Ditolak
                    'notes' => $catatan,
                    'user_id' => $user->id,
                ]);

                $riskName = $risk->peristiwa_risiko_id == 0
                    ? $risk->rencana_kegiatan
                    : ($risk->peristiwaRisiko->title ?? 'Risiko Proyek');

                $targetLink = route('projects.risks.index', ['project' => $risk->project_periode_list_id]);

                $this->sendNotificationCustom(
                    'RO_PROYEK',
                    $risk->project_periode_list_id,
                    'Request Edit Ditolak',
                    'Request edit risiko Anda (' . $riskName . ') telah ditolak oleh Risk Owner MR.',
                    $targetLink,
                    'bx bx-x-circle'
                );
            }
            DB::commit();
            return response()->json(['message' => 'Request edit berhasil ditolak. Status risiko tetap Published.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menolak request: ' . $e->getMessage()], 500);
        }
    }

    private function generateRiskStatus($row, $user, $u_step, $levelId)
    {
        if ($row->project_risks_count == 0) {
            return '<span class="badge bg-light text-dark border border-dark">Tidak Aktif</span>';
        }

        $lastBatch = $row->project->dataBatches->sortByDesc('id')->first();
        $allRisks = $row->projectRisks;
        $totalRisk = $allRisks->count();
        $publishedCount = $allRisks->where('status', ProjectRisk::STATUS_PUBLISHED)->count();

        // Cek jika status risiko adalah 6 (Published)
        if ($row->status == 6 || ($lastBatch && $lastBatch->finish) || ($totalRisk > 0 && $totalRisk === $publishedCount)) {
            $positionHtml = '<div class="mt-2 text-dark fw-bold" style="font-size: 11px;">Posisi: Selesai</div>';

            // Tambahkan Badge Informasi jika sedang request edit
            $requestEditHtml = '';
            if ($row->request_edit == 1) {
                $requestEditHtml = '<div class="mt-1"><span class="badge bg-warning text-dark border border-warning" data-bs-toggle="tooltip" title="Alasan Request: '.$row->request_edit_reason.'"><i class="bx bx-time-five"></i> Menunggu Approval Edit</span></div>';
            }

            return '<div class="d-flex flex-column align-items-start">
                        <span class="badge bg-success" data-bs-toggle="tooltip" title="Status: Published / Selesai">Published</span>
                        '.$requestEditHtml.'
                        '.$positionHtml.'
                    </div>';
        }

        $batchStep = $lastBatch ? $lastBatch->step_verification : 0;
        $batchStatus = $lastBatch ? $lastBatch->status : 1;

        $stepLabels = [
            0 => 'Risk Officer Proyek',
            1 => 'Risk Owner Proyek',
            2 => 'Risk Officer Divisi',
            3 => 'Risk Officer MR',
            4 => 'Risk Owner MR',
        ];
        $currentLabel = $stepLabels[$batchStep] ?? 'Verifikator';
        if ($batchStatus == 5) $currentLabel = 'Dikembalikan ke Officer Proyek';
        if ($batchStatus == 9) $currentLabel = 'Dikembalikan ke Officer Divisi';
        if ($batchStatus == 10) $currentLabel = 'Dikembalikan ke Officer MR';

        $positionHtml = '
        <div class="mt-2 text-dark fw-bold" style="font-size: 11px;">
            Posisi: ' . $currentLabel . '
        </div>';

        $isMyTurn = false;
        if ($levelId == 6) {
            if (in_array($batchStatus, [1, 5])) $isMyTurn = true;
        } else {
            if (($u_step == $batchStep) || ($u_step == 2 && $batchStatus == 9) || ($u_step == 3 && $batchStatus == 10)) {
                $isMyTurn = true;
            }
        }

        $pulseDot = '
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>';

        if ($isMyTurn && $this->userHasAccessToProject($user, $row)) {
            $redirectUrl = route('projects.risks.index', ['project' => $row->id]);

            if ($levelId == 6) {
                if ($batchStatus == 5) {
                    return '
                    <div class="d-flex flex-column align-items-start">
                        <a href="'.$redirectUrl.'" class="text-decoration-none">
                            <span class="badge bg-danger cursor-pointer border border-danger text-white position-relative"
                                  data-bs-toggle="tooltip"
                                  title="Status: Dikembalikan. Mohon perbaiki data risiko sesuai catatan.">
                                Perlu Revisi
                                '.$pulseDot.'
                            </span>
                        </a>
                        '.$positionHtml.'
                    </div>';
                }
                else {
                    $unlockedHtml = '';
                    if ($row->request_edit == 2) {
                        $unlockedHtml = '<div class="mt-1"><span class="badge bg-secondary"><i class="bx bx-lock-open-alt"></i> Unlocked</span></div>';
                    }
                    return '
                    <div class="d-flex flex-column align-items-start">
                        <a href="'.$redirectUrl.'" class="text-decoration-none">
                            <span class="badge bg-info cursor-pointer border border-info text-white position-relative"
                                  data-bs-toggle="tooltip"
                                  title="Status: Draft. Silakan lengkapi dan ajukan.">
                                Draft / Input Risiko
                                '.$pulseDot.'
                            </span>
                        </a>
                        '.$unlockedHtml.'
                        '.$positionHtml.'
                    </div>';
                }

            } else {
                return '
                <div class="d-flex flex-column align-items-start">
                    <a href="'.$redirectUrl.'" class="text-decoration-none">
                        <span class="badge bg-warning text-dark border border-warning shadow-sm cursor-pointer position-relative"
                              data-bs-toggle="tooltip"
                              title="Klik untuk verifikasi: '.$currentLabel.'">
                            <i class="bx bx-error-circle bx-flashing me-1"></i> Perlu Verifikasi
                            '.$pulseDot.'
                        </span>
                    </a>
                    '.$positionHtml.'
                </div>';
            }
        } else {
            return '
            <div class="d-flex flex-column align-items-start">
                <div class="d-inline-block position-relative"
                    data-bs-toggle="tooltip"
                    title="Posisi saat ini: '.$currentLabel.'">
                    <span class="badge bg-info bg-opacity-10 text-info border border-info">
                        <i class="bx bx-time-five me-1"></i> Proses Validasi
                    </span>
                </div>
                '.$positionHtml.'
            </div>';
        }
    }

    public function create() {
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriodeList))) {
        //     abort(403);
        // }

        $project = $projectPeriodeList->project;

        //get project profit_center
        $profitCenter = $project->meta['profit_center'] ?? null;
        //get project bulan mulai dan tahun mulai
        $tanggalMulai = $project->meta['tanggal_mulai'] ?? null;
        $bulanMulai = null;
        $tahunMulai = null;

        if ($tanggalMulai) {
            $date = \Carbon\Carbon::parse($tanggalMulai);
            $bulanMulai = $date->month;
            $tahunMulai = $date->year;
        }

        //$sasaranProyeks = SasaranProyek::get();
        $sasaranProyeks = collect();
        if ($profitCenter) {
            // Ambil tahun berjalan
            $tahunBerjalan = date('Y');

            // Cari data sasaran proyek berdasarkan profit_center dan tahun berjalan
            $sasaranProyeks = SasaranProyek::where('costcenter_code', $profitCenter)
                                          // ->where('tahun', $tahunBerjalan)
                                          ->get();

            // Jika tidak ada data, lakukan sinkronisasi dengan API
            if ($sasaranProyeks->isEmpty()) {
                try {
                    Log::channel('wikaapi')->info("Memulai request API KPI Rev dengan tahun: {$tahunBerjalan} dan profit_center: {$profitCenter}");
                    $kpiResult = (new \App\Supports\ApiWika())->getKPIRev($tahunBerjalan, $profitCenter);

                    Log::channel('wikaapi')->info("Request API KPI Rev berhasil", [
                        'tahun' => $tahunBerjalan,
                        'profit_center' => $profitCenter,
                        'status' => $kpiResult['status'] ?? false,
                        'message' => $kpiResult['message'] ?? ''
                    ]);

                    // Periksa apakah ada data KPI
                    if (isset($kpiResult['data']) && isset($kpiResult['data']['kpi']) && is_array($kpiResult['data']['kpi'])) {
                        $kpiData = $kpiResult['data']['kpi'];

                        Log::channel('wikaapi')->info("Data KPI ditemukan", [
                            'jumlah_data' => count($kpiData)
                        ]);

                        foreach ($kpiData as $kpi) {
                            // Cek apakah data dengan kpi_name, tahun, dan profit_center yang sama sudah ada
                            $existingKpi = SasaranProyek::where('costcenter_code', $profitCenter)
                                                      // ->where('tahun', $tahunBerjalan)
                                                      ->where('kpi_desc', $kpi['kpi_name'] ?? '')
                                                      ->first();

                            $kpiData = [
                                'costcenter_code' => $profitCenter,
                                'kpi_desc' => $kpi['kpi_name'] ?? '',
                                'status' => 1, // 1 = API
                                'tahun' => $tahunBerjalan,
                                'kpi_id' => $kpi['kpi_id'] ?? null,
                                'target_akhir_tahun' => $kpi['target_akhir_tahun'] ?? null,
                                'satuan' => $kpi['satuan'] ?? null
                            ];

                            if ($existingKpi) {
                                // Update data yang sudah ada
                                $existingKpi->update($kpiData);
                                Log::channel('wikaapi')->info("Data KPI diupdate", [
                                    'kpi_id' => $kpi['kpi_id'] ?? null,
                                    'kpi_name' => $kpi['kpi_name'] ?? ''
                                ]);
                            } else {
                                // Buat data baru
                                SasaranProyek::create($kpiData);
                                Log::channel('wikaapi')->info("Data KPI baru disimpan", [
                                    'kpi_id' => $kpi['kpi_id'] ?? null,
                                    'kpi_name' => $kpi['kpi_name'] ?? ''
                                ]);
                            }
                        }
                    } else {
                        Log::channel('wikaapi')->warning("Tidak ada data KPI dalam response", [
                            'response' => $kpiResult
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::channel('wikaapi')->error("Request API KPI Rev gagal", [
                        'tahun' => $tahunBerjalan,
                        'profit_center' => $profitCenter,
                        'error' => $e->getMessage()
                    ]);
                }

                // Ambil data yang baru disimpan
                $sasaranProyeks = SasaranProyek::where('costcenter_code', $profitCenter)
                                              // ->where('tahun', $tahunBerjalan)
                                              ->get();
            }
        }

        //tambahkan sasaran proyek yang ada di database dengan sasaran default (tanpa ada id)
        $defaultSasaran = SasaranProyek::getDefaults();
        $sasaranProyeks = $sasaranProyeks->concat($defaultSasaran);

        $periode = Periode::where('status','active')->first();

        $peristiwaRisikos = PeristiwaRisiko::where('type', 2)->get();

        $masterKris = MasterKRI::get();
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $kontrolEksistings = KontrolEksisting::get();
        $jenisRisikos = JenisRisiko::where('kategori_risiko_id', 6)->get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();
        $taksonomiRisikos = TaksonomiRisiko::all();
        $wbs_data = WBS::where('is_active', 1)->orderBy('code', 'asc')->get();

        return view('project-risk.create', compact('periode', 'project', 'peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings', 'projectPeriodeList', 'jenisRisikos', 'sasaranProyeks', 'taksonomiRisikos', 'wbs_data'));
    }

    public function store(Request $request)
    {
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriodeList))) {
        //     abort(403);
        // }

        $project = $projectPeriodeList->project;

        if ($request->action === 'save' || $request->action === 'savenext') {
            // 1. Definisikan Rules
            $rules = [
                'sasaran_proyek_id' => 'nullable',
                'peristiwa_risiko_id' => 'required',
                'rencana_kegiatan' => $request->peristiwa_risiko_id === 'other' ? 'required' : 'nullable',

                'kategori_risiko_id' => 'nullable',
                'jenis_risiko_id' => 'required',
                'deskripsi_peristiwa_risiko' => 'required',
                'wbs_id' => 'required|exists:w_b_s,id',

                'jenis_kontrol_eksisting_id' => 'required',
                'perkiraan_waktu_mulai_terpapar_risiko' => 'required',
                'perkiraan_waktu_selesai_terpapar_risiko' => 'required',

                'penyebab_risiko' => 'required|array|min:1',
                'penyebab_risiko.*' => 'required|string',

                'dampak_risiko' => 'required|array|min:1',
                'dampak_risiko.*' => 'required|string',

                'key_risk_indicator' => 'required|array|min:1',
                'key_risk_indicator.*' => 'required|string',
                'satuan_kri.*' => 'required|string',
                'batas_aman.*' => 'required',
                'batas_waspada.*' => 'required',
                'batas_bahaya.*' => 'required',

                'kontrol_eksisting' => 'required|array|min:1',
                'kontrol_eksisting.*' => 'required|string',
            ];

            // Tambahan Logic: Pastikan Sasaran Risiko terisi
            // Jika dropdown kosong DAN input manual kosong, maka error
            if (empty($request->sasaran_proyek_id) && empty($request->target_capaian_kinerja)) {
                $rules['sasaran_proyek_id'] = 'required';
            }
            // Jika pilih "Lainnya" tapi textarea kosong
            if ($request->sasaran_proyek_id === 'other' && empty($request->target_capaian_kinerja)) {
                $rules['target_capaian_kinerja'] = 'required';
            }

            // 2. Custom Error Messages (Lebih Manusiawi)
            $messages = [
                'sasaran_proyek_id.required' => 'Mohon pilih Sasaran Risiko terlebih dahulu.',
                'target_capaian_kinerja.required' => 'Karena Anda memilih "Sasaran Lainnya", mohon deskripsikan sasaran tersebut.',

                'peristiwa_risiko_id.required' => 'Silakan pilih Peristiwa Risiko dari daftar.',
                'rencana_kegiatan.required' => 'Mohon tuliskan nama Peristiwa Risiko (karena Anda memilih "Lainnya").',

                'kategori_risiko_id.required' => 'Kategori Risiko belum terdeteksi (pilih Jenis Risiko dulu).',
                'jenis_risiko_id.required' => 'Mohon pilih Jenis Risiko T2 & T3 KBUMN.',
                'deskripsi_peristiwa_risiko.required' => 'Deskripsi detail peristiwa risiko wajib diisi agar lebih jelas.',
                'wbs_id.required' => 'Mohon tentukan WBS (Work Breakdown Structure).',
                'wbs_id.exists' => 'Data WBS yang dipilih tidak valid.',

                'jenis_kontrol_eksisting_id.required' => 'Silakan pilih Jenis Kontrol Eksisting.',
                'perkiraan_waktu_mulai_terpapar_risiko.required' => 'Tanggal mulai terpapar risiko wajib diisi.',
                'perkiraan_waktu_selesai_terpapar_risiko.required' => 'Tanggal selesai terpapar risiko wajib diisi.',

                // Array Messages
                'penyebab_risiko.required' => 'Minimal harus ada satu Penyebab Risiko.',
                'penyebab_risiko.*.required' => 'Penyebab risiko tidak boleh ada yang kosong.',

                'dampak_risiko.required' => 'Minimal harus ada satu Dampak Risiko.',
                'dampak_risiko.*.required' => 'Dampak risiko tidak boleh ada yang kosong.',

                'key_risk_indicator.required' => 'Mohon masukkan minimal satu KRI.',
                'key_risk_indicator.*.required' => 'Nama KRI wajib diisi.',
                'satuan_kri.*.required' => 'Satuan KRI wajib diisi.',
                'batas_aman.*.required' => 'Batas Aman wajib diisi.',
                'batas_waspada.*.required' => 'Batas Waspada wajib diisi.',
                'batas_bahaya.*.required' => 'Batas Bahaya wajib diisi.',

                'kontrol_eksisting.required' => 'Mohon masukkan minimal satu Kontrol Eksisting.',
                'kontrol_eksisting.*.required' => 'Deskripsi kontrol eksisting tidak boleh kosong.',
            ];

            $request->validate($rules, $messages);

            // $request->validate([
            //     'peristiwa_risiko_id' => 'required',
            //     'peristiwa_risiko_id' => 'required',
            //     'rencana_kegiatan' => $request->peristiwa_risiko_id === 'other' ? 'required' : 'nullable',
            //     // 'kategori_risiko_id' => 'required',
            //     'jenis_risiko_id' => 'required',
            //     'deskripsi_peristiwa_risiko' => 'required',
            //     // 'deskripsi_dampak' => 'required',
            //     // 'wbs' => 'required',
            //     'wbs_id' => 'required|exists:w_b_s,id',
            //     //'target_capaian_kinerja' => 'required',
            //     'jenis_kontrol_eksisting_id' => 'required',
            //     // 'penilaian_efektifitas_kontrol' => 'required',
            //     'perkiraan_waktu_mulai_terpapar_risiko' => 'required',
            //     'perkiraan_waktu_selesai_terpapar_risiko' => 'required',
            //     'penyebab_risiko' => 'required|array|min:1',
            //     'penyebab_risiko.*' => 'required|string',
            //     'dampak_risiko' => 'required|array|min:1',
            //     'dampak_risiko.*' => 'required|string',
            // ]);

            $user = $request->user();

            //$perkiraanWaktuTerpaparRisiko = explode(' to ', $request->perkiraan_waktu_terpapar_risiko);

            $perkiraanWaktuMulaiTerpaparRisiko = $request->perkiraan_waktu_mulai_terpapar_risiko;
            $perkiraanWaktuSelesaiTerpaparRisiko = $request->perkiraan_waktu_selesai_terpapar_risiko;
            $perkiraanWaktuTerpaparRisikoMulai = DateTime::createFromFormat('d/m/Y', $perkiraanWaktuMulaiTerpaparRisiko)->format('Y-m-d');
            $perkiraanWaktuTerpaparRisikoAkhir = DateTime::createFromFormat('d/m/Y', $perkiraanWaktuSelesaiTerpaparRisiko)->format('Y-m-d');

            // Handle sasaran_proyek_id
            $sasaranProyekId = null;
            $targetCapaianKinerja = '';

            if ($request->sasaran_proyek_id === 'other') {
                // Jika opsi "Lainnya" dipilih, buat data SasaranProyek baru
                // $sasaranProyek = SasaranProyek::create([
                //     'costcenter_code' => $project->costcenter_code ?? 'MANUAL-INPUT',
                //     'kpi_desc' => $request->target_capaian_kinerja,
                // ]);
                //$sasaranProyekId = $sasaranProyek->id;
                $targetCapaianKinerja = $request->target_capaian_kinerja;
            } else if ($request->sasaran_proyek_id) {
                // Jika opsi yang sudah ada dipilih
                $sasaranProyekId = $request->sasaran_proyek_id;
                // Cek jika ini adalah sasaran default (yang memiliki ID fake 'default_')
                if (str_starts_with($sasaranProyekId, 'default_')) {
                    $sasaranProyekId = null;
                }
                $targetCapaianKinerja = $request->kpi_desc_selected;
            } else {
                // Fallback jika tidak ada yang dipilih
                $targetCapaianKinerja = $request->target_capaian_kinerja;
            }

            $peristiwaRisikoId = $request->peristiwa_risiko_id;
            $rencanaKegiatan = null;

            if ($peristiwaRisikoId === 'other') {
                $peristiwaRisikoId = 0;
                $rencanaKegiatan = $request->rencana_kegiatan;
            }

            $cleanRencanaKegiatan = $this->cleanInput($rencanaKegiatan);
            $cleanTargetCapaian = $this->cleanInput($targetCapaianKinerja);
            $cleanDeskripsiPeristiwa = $this->cleanInput($request->deskripsi_peristiwa_risiko);
            $cleanDeskripsiDampak = $this->cleanInput($request->deskripsi_dampak);

            $toStore = [
                'unit_type_id' => $user->unit_type_id,
                'unit_id' => $user->unit_id,
                'periode_id' => 0,
                'user_id' => $user->id,
                'project_id' => $project->id,
                'peristiwa_risiko_id' => $peristiwaRisikoId,
                'rencana_kegiatan' => $cleanRencanaKegiatan,
                'target_capaian_kinerja' => $cleanTargetCapaian,
                'sasaran_proyek_id' => $sasaranProyekId,
                'project_periode_list_id' => $projectPeriodeList->id,
                'deskripsi_peristiwa_risiko' => $cleanDeskripsiPeristiwa,
                'deskripsi_dampak' => $cleanDeskripsiDampak,
                'jenis_kontrol_eksisting_id' => $request->jenis_kontrol_eksisting_id,
                'penilaian_efektifitas_kontrol' => 0,
                'perkiraan_waktu_terpapar_risiko_mulai' => $perkiraanWaktuTerpaparRisikoMulai,
                'perkiraan_waktu_terpapar_risiko_akhir' => $perkiraanWaktuTerpaparRisikoAkhir,
                'kategori_risiko_id' => $request->kategori_risiko_id,
                'jenis_risiko_id' => $request->jenis_risiko_id,
                'kontrol_eksisting' => '',
                'wbs' => null,
                'wbs_id' => $request->wbs_id,
                'status_risiko' => '0',
                'status_progress'  => '0',
                'step_verification' => 0,
                // 'taksonomi_risiko_id' => $request->taksonomi_risiko_id,
                // 'threshold_risk_limit' => $this->cleanRupiah($request->threshold_risk_limit),
                // 'threshold_risk_appetite' => $this->cleanRupiah($request->threshold_risk_appetite),
                // 'threshold_risk_tolerance' => $this->cleanRupiah($request->threshold_risk_tolerance),
                'status' => 1
            ];

            $projectRisk = ProjectRisk::create($toStore);

            // if ($request->has('param_nama')) {
            //     foreach ($request->param_nama as $idx => $nama) {
            //         if(!empty($nama)) {
            //             $projectRisk->parameterRisikoProjects()->create([
            //                 'nama' => $nama,
            //                 'formula' => $request->param_formula[$idx] ?? '',
            //                 'satuan' => $request->param_satuan[$idx] ?? '',
            //             ]);
            //         }
            //     }
            // }

            $projectRisk->projectRiskAnalisa()->create([]);
            $projectRisk->projectRiskRencanaPerlakuan()->create([]);

            foreach ($request->dampak_risiko as $textDampak) {
                $projectRisk->dampakRisikoProjects()->create([
                    'dampak_risiko' => $this->cleanInput($textDampak),
                ]);
            }

            foreach ($request->penyebab_risiko as $penyebabRisiko) {
                $projectRisk->penyebabRisikoProjects()->create([
                    'penyebab_risiko' => $this->cleanInput($penyebabRisiko),
                ]);
            }

            foreach ($request->key_risk_indicator as $idx => $kri) {
                $kriData = [
                    'kri' => $this->cleanInput($kri),
                    'satuan_kri' => $this->cleanInput($request->satuan_kri[$idx]) ?? '',
                    'batas_aman' => $this->cleanInput($request->batas_aman[$idx]) ?? '',
                    'batas_waspada' => $this->cleanInput($request->batas_waspada[$idx]) ?? '',
                    'batas_bahaya' => $this->cleanInput($request->batas_bahaya[$idx]) ?? '',
                ];

                $projectRisk->kriProjects()->create($kriData);
            }

            foreach ($request->kontrol_eksisting as $kontrolEksisting) {
                $projectRisk->projectKontrolEksistings()->create([
                    'kontrol_eksisting_desc' => $this->cleanInput($kontrolEksisting),
                ]);
            }

            if ($request->action === 'savenext') {
                return [
                    'redirect' => route('projects.risks.analisa', [
                        'project' => $projectPeriodeList->id,
                        'risk' => $projectRisk->id,
                    ]),
                ];
            }

            return [
                'redirect' => route('projects.risks.index', ['project' => $projectPeriodeList->id]),
            ];

        } elseif ($request->action === 'draft') {
            $request->validate([
                'deskripsi_peristiwa_risiko' => 'required',
            ]);

            $key = $request->draft_key ?: uniqid();
            $data = $request->except('_token', 'draft_key');

            $data['periode_id'] = 0;
            $data['user_id'] = $request->user()->id;
            $data['unit_id'] = $request->user()->unit_id;
            $data['unit_type_id'] = $request->user()->unit_type_id;

            Draft::updateOrCreate(
                ['type' => ProjectRisk::class, 'key' => $key],
                ['data' => $data, 'user_id' => $request->user()->id],
            );

            return [
                'message' => 'Data berhasil disimpan sebagai draft',
                'redirect' => route('projects.risks.index', ['project' => $projectPeriodeList->id]),
            ];
        }
    }

    public function edit($resource)
    {
        $projectRisk = ProjectRisk::with([
          'penyebabRisikoProjects',
          'kriProjects',
          'peristiwaRisiko',
          'parameterRisikoProjects',
          'dampakRisikoProjects',
        ])->findOrFail(request()->route('risk'));

        //dd($projectRisk);
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriodeList))) {
        //     abort(403);
        // }

        $project = $projectPeriodeList->project;

        //get project profit_center
        $profitCenter = $project->meta['profit_center'] ?? null;

        $sasaranProyeks = collect();
        if ($profitCenter) {
            $sasaranProyeks = SasaranProyek::where('costcenter_code', $profitCenter)->get();
        }

        //tambahkan sasaran proyek yang ada di database dengan sasaran default (tanpa ada id)
        $defaultSasaran = SasaranProyek::getDefaults();
        $sasaranProyeks = $sasaranProyeks->concat($defaultSasaran);

        $peristiwaRisikos = PeristiwaRisiko::get();

        $masterKris = MasterKRI::get();
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $kontrolEksistingIds = !empty($projectRisk->kontrol_eksisting) ? explode(',', $projectRisk->kontrol_eksisting) : [];

        $kontrolEksistings = KontrolEksisting::whereIn('id', $kontrolEksistingIds)->get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();
        $jenisRisikos = JenisRisiko::where('kategori_risiko_id', 6)->get();
        $taksonomiRisikos = TaksonomiRisiko::all();
        $wbs_data = WBS::orderBy('code', 'asc')->get();

        return view('project-risk.edit', compact('projectRisk', 'project', 'peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings', 'projectPeriodeList', 'jenisRisikos', 'sasaranProyeks', 'taksonomiRisikos', 'wbs_data'));
    }

    public function update(Request $request, $resource)
    {
        $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriodeList))) {
        //     abort(403);
        // }

        $project = $projectPeriodeList->project;
        $periode = $projectPeriodeList->periode;

        if ($request->action === 'save' || $request->action === 'savenext') {
              $rules = [
                'peristiwa_risiko_id' => 'required',
                // Jika pilih "Lainnya", rencana_kegiatan wajib diisi
                'rencana_kegiatan' => $request->peristiwa_risiko_id === 'other' ? 'required' : 'nullable',

                'kategori_risiko_id' => 'nullable',
                'jenis_risiko_id' => 'required',
                'deskripsi_peristiwa_risiko' => 'required',
                'wbs_id' => 'required|exists:w_b_s,id',
                'jenis_kontrol_eksisting_id' => 'required',

                'perkiraan_waktu_terpapar_risiko_mulai' => 'required',
                'perkiraan_waktu_terpapar_risiko_akhir' => 'required',

                // Validasi Array
                'penyebab_risiko' => 'required|array|min:1',
                'penyebab_risiko.*' => 'required|string',

                'dampak_risiko' => 'required|array|min:1',
                'dampak_risiko.*' => 'required|string',

                'key_risk_indicator' => 'required|array|min:1',
                'key_risk_indicator.*' => 'required|string',
                'satuan_kri.*' => 'required|string',
                'batas_aman.*' => 'required',
                'batas_waspada.*' => 'required',
                'batas_bahaya.*' => 'required',

                'kontrol_eksisting' => 'required|array|min:1',
                'kontrol_eksisting.*' => 'required|string',
            ];

            // Validasi Sasaran (Logic Khusus)
            if (empty($request->sasaran_proyek_id) && empty($request->target_capaian_kinerja)) {
                $rules['sasaran_proyek_id'] = 'required';
            }
            if ($request->sasaran_proyek_id === 'other' && empty($request->target_capaian_kinerja)) {
                $rules['target_capaian_kinerja'] = 'required';
            }

            // --- 2. PESAN ERROR CUSTOM ---
            $messages = [
                'sasaran_proyek_id.required' => 'Mohon pilih Sasaran Risiko.',
                'target_capaian_kinerja.required' => 'Mohon deskripsikan sasaran risiko lainnya.',

                'peristiwa_risiko_id.required' => 'Peristiwa Risiko wajib dipilih.',
                'rencana_kegiatan.required' => 'Nama Peristiwa Risiko (Lainnya) wajib diisi.',

                'jenis_risiko_id.required' => 'Jenis Risiko T2 & T3 wajib dipilih.',
                'deskripsi_peristiwa_risiko.required' => 'Deskripsi peristiwa risiko wajib diisi.',
                'wbs_id.required' => 'WBS wajib dipilih.',
                'jenis_kontrol_eksisting_id.required' => 'Jenis kontrol eksisting wajib dipilih.',

                'perkiraan_waktu_terpapar_risiko_mulai.required' => 'Tanggal mulai terpapar wajib diisi.',
                'perkiraan_waktu_terpapar_risiko_akhir.required' => 'Tanggal selesai terpapar wajib diisi.',

                'penyebab_risiko.required' => 'Minimal satu penyebab risiko harus diisi.',
                'penyebab_risiko.*.required' => 'Penyebab risiko tidak boleh kosong.',

                'dampak_risiko.required' => 'Minimal satu dampak risiko harus diisi.',
                'dampak_risiko.*.required' => 'Dampak risiko tidak boleh kosong.',

                'key_risk_indicator.required' => 'Minimal satu KRI harus diisi.',
                'key_risk_indicator.*.required' => 'KRI tidak boleh kosong.',
                'satuan_kri.*.required' => 'Satuan KRI wajib diisi.',
                'batas_aman.*.required' => 'Batas Aman wajib diisi.',
                'batas_waspada.*.required' => 'Batas Waspada wajib diisi.',
                'batas_bahaya.*.required' => 'Batas Bahaya wajib diisi.',

                'kontrol_eksisting.required' => 'Minimal satu kontrol eksisting harus diisi.',
                'kontrol_eksisting.*.required' => 'Kontrol eksisting tidak boleh kosong.',
            ];

            // Jalankan Validasi
            $request->validate($rules, $messages);

            // $request->validate([
            //     'peristiwa_risiko_id' => 'required',
            //     'kategori_risiko_id' => 'required',
            //     'jenis_risiko_id' => 'required',
            //     'deskripsi_peristiwa_risiko' => 'required',
            //     // 'deskripsi_dampak' => 'required',
            //     // 'wbs' => 'required',
            //     'wbs_id' => 'required|exists:w_b_s,id',
            //     'jenis_kontrol_eksisting_id' => 'required',
            //     // 'penilaian_efektifitas_kontrol' => 'required',
            //     'perkiraan_waktu_terpapar_risiko_mulai' => 'required',
            //     'perkiraan_waktu_terpapar_risiko_akhir' => 'required',
            //     'penyebab_risiko' => 'required|array|min:1',
            //     'penyebab_risiko.*' => 'required|string',
            //     'dampak_risiko' => 'required|array|min:1',
            //     'dampak_risiko.*' => 'required|string',
            // ]);

            $user = $request->user();

            $perkiraanWaktuTerpaparRisikoMulai = DateTime::createFromFormat('d/m/Y', $request->perkiraan_waktu_terpapar_risiko_mulai)->format('Y-m-d');
            $perkiraanWaktuTerpaparRisikoAkhir = DateTime::createFromFormat('d/m/Y', $request->perkiraan_waktu_terpapar_risiko_akhir)->format('Y-m-d');

            $sasaranProyekId = null;
            $targetCapaianKinerja = '';

            if ($request->sasaran_proyek_id === 'other') {
                $sasaranProyekId = null;
                $targetCapaianKinerja = $request->target_capaian_kinerja;
            } else if ($request->sasaran_proyek_id) {
                $sasaranProyekId = $request->sasaran_proyek_id;
                // Cek jika ini adalah sasaran default (yang memiliki ID fake 'default_')
                if (str_starts_with($sasaranProyekId, 'default_')) {
                    $sasaranProyekId = null;
                }
                $targetCapaianKinerja = $request->kpi_desc_selected;
            } else {
                $targetCapaianKinerja = $request->target_capaian_kinerja;
            }

            $peristiwaRisikoId = $request->peristiwa_risiko_id;
            $rencanaKegiatan = null;

            if ($peristiwaRisikoId === 'other') {
                $peristiwaRisikoId = 0;
                $rencanaKegiatan = $request->rencana_kegiatan;
            }

            $cleanRencanaKegiatan = $this->cleanInput($rencanaKegiatan);
            $cleanTargetCapaian = $this->cleanInput($targetCapaianKinerja);
            $cleanDeskripsiPeristiwa = $this->cleanInput($request->deskripsi_peristiwa_risiko);
            $cleanDeskripsiDampak = $this->cleanInput($request->deskripsi_dampak);

            $toUpdate = [
                'unit_type_id' => $user->unit_type_id,
                'unit_id' => $user->unit_id,
                'periode_id' => 0,
                'user_id' => $user->id,
                'project_id' => $project->id,
                'kategori_risiko_id' => $request->kategori_risiko_id,
                'jenis_risiko_id' => $request->jenis_risiko_id,
                'peristiwa_risiko_id' => $peristiwaRisikoId,
                'rencana_kegiatan' => $cleanRencanaKegiatan,
                'project_periode_list_id' => $projectPeriodeList->id,
                'deskripsi_peristiwa_risiko' => $cleanDeskripsiPeristiwa,
                'deskripsi_dampak' => $cleanDeskripsiDampak,
                'jenis_kontrol_eksisting_id' => $request->jenis_kontrol_eksisting_id,
                'penilaian_efektifitas_kontrol' => 0,
                'perkiraan_waktu_terpapar_risiko_mulai' => $perkiraanWaktuTerpaparRisikoMulai,
                'perkiraan_waktu_terpapar_risiko_akhir' => $perkiraanWaktuTerpaparRisikoAkhir,
                'wbs' => null,
                'wbs_id' => $request->wbs_id,
                'target_capaian_kinerja' => $cleanTargetCapaian,
                'sasaran_proyek_id' => $sasaranProyekId,
                // 'taksonomi_risiko_id' => $request->taksonomi_risiko_id,
                // 'threshold_risk_limit' => $this->cleanRupiah($request->threshold_risk_limit),
                // 'threshold_risk_appetite' => $this->cleanRupiah($request->threshold_risk_appetite),
                // 'threshold_risk_tolerance' => $this->cleanRupiah($request->threshold_risk_tolerance),
            ];

            $projectRisk->update($toUpdate);

            // $savedParamIds = [];
            // if ($request->has('param_nama')) {
            //     foreach ($request->param_nama as $key => $nama) {
            //         $dataParam = [
            //             'nama' => $nama,
            //             'formula' => $request->param_formula[$key] ?? '',
            //             'satuan' => $request->param_satuan[$key] ?? '',
            //         ];

            //         $paramId = $request->parameter_risiko_id[$key] ?? null;
            //         $exist = $projectRisk->parameterRisikoProjects()->find($paramId);

            //         if ($exist) {
            //             $exist->update($dataParam);
            //             $savedParamIds[] = $exist->id;
            //         } else {
            //             $newParam = $projectRisk->parameterRisikoProjects()->create($dataParam);
            //             $savedParamIds[] = $newParam->id;
            //         }
            //     }
            // }
            // $projectRisk->parameterRisikoProjects()->whereNotIn('id', $savedParamIds)->delete();

            $dampakRisikoIds = [];
            foreach ($request->dampak_risiko as $dampakRisikoId => $dampakRisiko) {
                $exist = $projectRisk->dampakRisikoProjects()->where('id', $dampakRisikoId)->first();

                if ($exist) {
                    $exist->update([
                        'dampak_risiko' => $this->cleanInput($dampakRisiko),
                    ]);
                } else {
                    $exist = $projectRisk->dampakRisikoProjects()->create([
                        'dampak_risiko' => $this->cleanInput($dampakRisiko)
                    ]);
                }
                $dampakRisikoIds[] = $exist->id;
            }
            $projectRisk->dampakRisikoProjects()->whereNotIn('id', $dampakRisikoIds)->delete();

            $penyebabRisikoIds = [];
            foreach ($request->penyebab_risiko as $penyebabRisikoId => $penyebabRisiko) {
                $exist = $projectRisk->penyebabRisikoProjects()->where('id', $penyebabRisikoId)->first();
                if ($exist) {
                    $exist->update([
                        'penyebab_risiko' => $this->cleanInput($penyebabRisiko),
                    ]);
                } else {
                    $exist = $projectRisk->penyebabRisikoProjects()->create([
                        'penyebab_risiko' => $this->cleanInput($penyebabRisiko),
                    ]);
                }
                $penyebabRisikoIds[] = $exist->id;
            }
            $projectRisk->penyebabRisikoProjects()->whereNotIn('id', $penyebabRisikoIds)->delete();

            $savedKriIds = [];
            foreach ($request->key_risk_indicator as $key => $kri) {
                $kriData = [
                    'kri' => $this->cleanInput($kri),
                    'satuan_kri' => $this->cleanInput($request->satuan_kri[$key]) ?? '',
                    'batas_aman' => $this->cleanInput($request->batas_aman[$key]) ?? '',
                    'batas_waspada' => $this->cleanInput($request->batas_waspada[$key]) ?? '',
                    'batas_bahaya' => $this->cleanInput($request->batas_bahaya[$key]) ?? '',
                ];

                $existKri = $projectRisk->kriProjects()->find($key);

                if ($existKri) {
                    $existKri->update($kriData);
                    $savedKriIds[] = $existKri->id;
                } else {
                    $newKri = $projectRisk->kriProjects()->create($kriData);
                    $savedKriIds[] = $newKri->id;
                }
            }
            $projectRisk->kriProjects()->whereNotIn('id', $savedKriIds)->delete();

            $savedKontrolIds = [];
            foreach ($request->kontrol_eksisting as $key => $kontrolEksistingDesc) {
                $existKontrol = $projectRisk->projectKontrolEksistings()->find($key);

                if ($existKontrol) {
                    $existKontrol->update(['kontrol_eksisting_desc' => $this->cleanInput($kontrolEksistingDesc)]);
                    $savedKontrolIds[] = $existKontrol->id;
                } else {
                    $newKontrol = $projectRisk->projectKontrolEksistings()->create([
                        'kontrol_eksisting_desc' => $this->cleanInput($kontrolEksistingDesc),
                    ]);
                    $savedKontrolIds[] = $newKontrol->id;
                }
            }
            $projectRisk->projectKontrolEksistings()->whereNotIn('id', $savedKontrolIds)->delete();


            if ($request->action === 'savenext') {
                if ($projectRisk->request_edit == 2) {
                    return [
                        'redirect' => route('projects.risks.rencana', ['project' => $projectPeriodeList->id, 'risk' => $projectRisk->id]),
                    ];
                }

                return [
                    'redirect' => route('projects.risks.analisa', ['project' => $projectPeriodeList->id, 'risk' => $projectRisk->id]),
                ];
            } else {
                return [
                    'redirect' => route('projects.risks.index', ['project' => $projectPeriodeList->id]),
                ];
            }

        } elseif ($request->action === 'draft') {
            // Logic Draft tetap sama seperti kodemu
            $request->validate([
                'deskripsi_peristiwa_risiko' => 'required',
            ]);

            $key = $request->draft_key ?: uniqid();
            $data = $request->except('_token', 'draft_key');

            $data['periode_id'] = $periode->id;
            $data['user_id'] = $request->user()->id;
            $data['unit_id'] = $request->user()->unit_id;
            $data['unit_type_id'] = $request->user()->unit_type_id;

            Draft::updateOrCreate(
                ['type' => ProjectRisk::class, 'key' => $key],
                ['data' => $data, 'user_id' => $request->user()->id],
            );

            return [
                'message' => 'Data berhasil disimpan sebagai draft',
                'redirect' => route('projects.risks.index', ['project' => $projectPeriodeList->id]),
            ];
        }
    }

    public function view(Request $request, $resource)
    {
        $projectRisk = ProjectRisk::with([
                'projectPeriodeList.project',
                'wbsMaster',
                'peristiwaRisiko',
                'taksonomiRisiko',
                'dampakRisikoProjects',
                'penyebabRisikoProjects',
                'projectRiskAnalisa.skalaDampakObj',
                'projectRiskAnalisa.skalaProbabilitas',
                'projectRiskAnalisa.skalaDampakResidualObj',
                'projectRiskAnalisa.skalaProbabilitasResidual',
                'projectRiskMonitorings' => function($query) {
                    $query->orderBy('tahun', 'desc')
                        ->orderBy('month', 'desc')
                        ->orderBy('id', 'desc')
                        ->with([
                            'skalaProbabilitas',
                            'skalaDampakObj',
                            'kriProyekMonitorings.kriProject',
                            'perlakuanPenyebabMonitorings.perlakuanPenyebab.penyebabRisikoProject',
                            'perlakuanDampakMonitorings.perlakuanDampak.dampakRisikoProject',
                            'perlakuanPenyebabRisikoDocuments',
                            'perlakuanDampakRisikoDocuments'
                        ]);
                },
            ])
            ->where('project_periode_list_id', request()->route('project'))
            ->findOrFail(request()->route('risk'));

        // $projectRisk->append('currentRiskMapsMonth');

        $tahunMonitorings = $projectRisk->projectRiskMonitorings->pluck('tahun')->unique()->toArray();
        $tahunMonitorings[] = $projectRisk->created_at->year;
        sort($tahunMonitorings);

        $minTahun = !empty($tahunMonitorings) ? min($tahunMonitorings) : date('Y');
        $maxTahun = !empty($tahunMonitorings) ? max($tahunMonitorings) : date('Y');
        $tahunMonitorings = range($minTahun, $maxTahun);

        $currentState = [
            'skala_dampak' => $projectRisk->projectRiskAnalisa?->skala_dampak,
            'skala_probabilitas' => $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->tingkat,
            'nilai_dampak' => $projectRisk->projectRiskAnalisa?->nilai_dampak,
            'skala_dampak_desc' => $projectRisk->projectRiskAnalisa?->skalaDampakObj?->deskripsi,
            'nilai_probabilitas' => $projectRisk->projectRiskAnalisa?->nilai_probabilitas,
            'skala_probabilitas_desc' => $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->skala,
            'nilai_risiko' => $projectRisk->projectRiskAnalisa?->skala_risiko,
            'level_risiko' => $projectRisk->projectRiskAnalisa?->level_risiko,
            'month' => 0
        ];

        $formattedCurrentRiskMaps = [];
        $riskRealisasiData = [];

        // Karena data sudah diurutkan dari yang terbaru (desc), first() akan mengambil data terakhir/terbaru untuk bulan & tahun tersebut
        $monitorings = $projectRisk->projectRiskMonitorings->groupBy(function($item) {
            return $item->tahun . '-' . $item->month;
        })->map(function($group) {
            return $group->first();
        });

        foreach ($tahunMonitorings as $tahun) {
            for ($month = 1; $month <= 12; $month++) {
                $key = $tahun . '-' . $month;

                // Jika ada monitoring di bulan ini, update current state
                if (isset($monitorings[$key])) {
                    $m = $monitorings[$key];
                    $currentState = [
                        'skala_dampak' => $m->skala_dampak,
                        'skala_probabilitas' => $m->skalaProbabilitas?->tingkat,
                        'nilai_dampak' => $m->nilai_dampak,
                        'skala_dampak_desc' => $m->skalaDampakObj?->deskripsi,
                        'nilai_probabilitas' => $m->nilai_probabilitas,
                        'skala_probabilitas_desc' => $m->skalaProbabilitas?->skala,
                        'nilai_risiko' => $m->skala_risiko,
                        'level_risiko' => $m->level_risiko,
                    ];
                }

                // Data untuk Peta (Maps)
                $mapData = $currentState;
                $mapData['tahun'] = $tahun;
                $mapData['month'] = $month;
                $mapData['quarter'] = ceil($month / 3);
                $formattedCurrentRiskMaps[$projectRisk->id][$tahun][] = $mapData;

                // Data untuk Tabel Realisasi
                $riskRealisasiData[$projectRisk->id][$key] = $currentState;
            }
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        $risk_tolerance = 0;
        $risk_limit = 0;
        $project = $projectRisk->projectPeriodeList->project;

        if($project->type==2){
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
        } else if($project->type==1){
            $risk_tolerance = $project->rapt ?? 0;
            $risk_tolerance = (2*$risk_tolerance/100);
        } else{
            $risk_tolerance = 0;
        }

        if($projectRisk->projectRiskAnalisa?->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF){
            $risk_limit = ($project->nk ?? 0) * 0.03;
        } else{
            $risk_limit = 1/100*$risk_tolerance;
        }

        return view('project-risk.view', compact(
            'projectRisk',
            'tahunMonitorings',
            'formattedCurrentRiskMaps',
            'riskRealisasiData',
            'riskMaps',
            'risk_limit',
            'risk_tolerance',
        ));
    }

    public function destroy($resource) {
        DB::beginTransaction();
        try {
            $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));
            $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

            if (!Gate::check('project_risk_delete')) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk menghapus data ini'
                ], 403);
            }

            $projectRisk->projectRiskMonitorings()->delete();
            $projectRisk->projectRiskRencanaPerlakuans()->delete();
            $projectRisk->perlakuanDampakRisikos()->delete();
            $projectRisk->penyebabRisikoProjects()->delete();
            $projectRisk->dampakRisikoProjects()->delete();
            $projectRisk->kriProjects()->delete();
            $projectRisk->projectRiskAnalisas()->delete();
            $projectRisk->projectKontrolEksistings()->delete();
            $projectRisk->delete();

            $projectPeriodeList = $projectPeriodeList->fresh();
            // $projectPeriodeList->recalculateAnalisa();
            // $projectPeriodeList->refreshNilai();
            DB::commit();

            return response()->json([
                'message' => 'Data risiko proyek berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function analisa(Request $request, $resource) {
        $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriodeList))) {
        //     abort(403);
        // }

        $project = $projectPeriodeList->project;
        $periode = $projectPeriodeList->periode;
        $analisa = $projectRisk->projectRiskAnalisa;
        if (!$analisa) {
            $analisa = $projectRisk->projectRiskAnalisa()->create([]);
        }

        $selectedParameterType = null;
        if ($analisa && $analisa->skalaParameterObj) {
            $selectedParameterType = $analisa->skalaParameterObj->type_parameter;
        }

        $skalaParameters = SkalaParameter::all();
        $parameterTypes = $skalaParameters->pluck('type_parameter')->unique();
        $groupedSkalaParameters = $skalaParameters->groupBy('type_parameter');
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        $areas = AreaDampak::with('details')->get();

        $groupedAreas = $areas->groupBy('risk_category');

        $risk_tolerance = 0;
        $risk_limit = 0;
        $sum_risk = ProjectRisk::where('project_id', $project->id)
            ->where('id', '!=', $projectRisk->id)
            ->whereHas('projectRiskAnalisa', function ($query) {
                $query->where('kategori_dampak', 'Kuantitatif');
            })
            ->count();
        $sum_risk = $sum_risk + 1;
        //dd($project);
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
        }
        else if($project->type==1){
            $risk_tolerance = $project->rapt ?? 0;
            $risk_tolerance = (2*$risk_tolerance/100);
        }
        else{
            $risk_tolerance = 0;
        }

        //$risk_limit = $projectPeriodeList->risk_limit;
        $risk_limit = ($projectPeriodeList->project->nk ?? 0) * 0.03;
        // dd($projectRisk);

        return view('project-risk.analisa', compact('projectRisk', 'project', 'periode', 'projectPeriodeList', 'skalaProbabilitas', 'riskMaps', 'analisa', 'areas', 'groupedAreas', 'risk_tolerance', 'risk_limit', 'parameterTypes', 'groupedSkalaParameters', 'selectedParameterType'));
    }

    public function doAnalisa(Request $request) {
        $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriodeList))) {
        //     abort(403);
        // }

        $analisa = $projectRisk->projectRiskAnalisa;
        if (!$analisa) {
            $analisa = $projectRisk->projectRiskAnalisa()->create([]);
        }

        $request->validate([
            'kategori_dampak' => 'required|in:' . ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF . ',' . ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF,
            'nilai_probabilitas' => 'required|numeric',
            'skala_parameter_id' => 'required|exists:skala_parameters,id',
            // 'skala_parameter_residual_id' => 'required|exists:skala_parameters,id',
            'nilai_probabilitas_residual' => 'required|numeric|lte:nilai_probabilitas',
            'skala_dampak' => 'required',
            'skala_dampak_residual' => 'required',
        ]);

        //dd($request);

        $xrisk_limit = $this->cleanRupiah($request->_risk_limit);
        $nilai_dampak = $this->cleanRupiah($request->nilai_dampak);
        $nilai_dampak_residual = $this->cleanRupiah($request->nilai_dampak_residual);

        $project = $projectPeriodeList->project;
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        // Ambil object Skala Parameter berdasarkan ID yang dipilih user
        $skalaInherent = SkalaParameter::find($request->skala_parameter_id);
        $skalaResidual = SkalaParameter::find($request->skala_parameter_residual_id);

        if (!$skalaInherent || !$skalaResidual) {
            return response()->json([
                'message' => 'Gagal memproses data: Parameter skala tidak lengkap. Silakan pilih ulang Skala Probabilitas.'
            ], 422);
        }

        // Pastikan kita menggunakan 'tingkat' dari SkalaParameter yang dipilih user
        $tingkatInherent = $skalaInherent->tingkat;
        $tingkatResidual = $skalaResidual->tingkat;

        // Validasi logic (Residual tidak boleh > Inheren)
        // if ($tingkatResidual > $tingkatInherent) {
        //     return response()->json([
        //         'message' => 'Tingkat skala probabilitas residual tidak boleh lebih tinggi dari inheren.',
        //     ], 422);
        // }

        $toUpdate = [
            'skala_probabilitas_id' => null, // calculated [Done]
            'area_dampak' => $request->area_dampak ?? null,
            'kategori_dampak' => $request->kategori_dampak,
            'deskripsi_dampak' => $request->deskripsi_dampak,
            'deskripsi_dampak_residual' => $request->deskripsi_dampak_residual,
            'asumsi_perhitungan_dampak' => $request->asumsi_perhitungan_dampak,
            'asumsi_perhitungan_dampak_residual' => $request->asumsi_perhitungan_dampak_residual,
            'nilai_dampak' => null, // calculated [Done]
            //'skala_dampak' => $request->skala_dampak_hidden,
            'nilai_probabilitas' => $request->nilai_probabilitas,
            //'skala_risiko' => null, // calculated [Done]
            //'level_risiko' => null, // calculated [Done]
            //'risk_limit' => $project->risk_limit, // calculated [Done]
            //'eksposur_risiko' => null, // calculated
            'skala_probabilitas_residual_id' => null, // calculated [Done]
            'nilai_dampak_residual' => null, // calculated [Done]
            //'skala_dampak_residual' => $request->skala_dampak_residual_hidden,
            'nilai_probabilitas_residual' => $request->nilai_probabilitas_residual,
            //'skala_risiko_residual' => null, // calculated [Done]
            //'level_risiko_residual' => null, // calculated [Done]
            //'eksposur_risiko_residual' => null, // calculated
            'skala_parameter_id' => $request->skala_parameter_id,
            'skala_parameter_residual_id' => $request->skala_parameter_residual_id,
        ];

        $risk_tolerance = 0;
        $risk_limit = 0;

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
        }
        else if($project->type==1){
            $risk_tolerance = $project->rapt ?? 0;
            $risk_tolerance = (2*$risk_tolerance/100);
        }
        else{
            $risk_tolerance = 0;
        }

        if($request->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF){
            $sum_risk = ProjectRisk::where('periode_id', $projectPeriodeList->periode_id)
                ->where('project_id', $projectPeriodeList->project_id)
                ->where('id', '!=', $projectRisk->id)
                ->whereHas('projectRiskAnalisa', function ($query) {
                    $query->where('kategori_dampak', 'Kuantitatif');
                })
                ->count();
            $sum_risk = $sum_risk + 1;

            $risk_limit = ($projectPeriodeList->project->nk ?? 0) * 0.03;


            $skalaDampakFinal = $request->skala_dampak;
            $skalaDampakResidualFinal = $request->skala_dampak_residual;
            // $toUpdate['skala_dampak'] = $request->skala_dampak;
            // $toUpdate['skala_dampak_residual'] = $request->skala_dampak_residual;

            $toUpdate['nilai_dampak'] = $nilai_dampak;
            $toUpdate['nilai_dampak_residual'] = $nilai_dampak_residual;
            $toUpdate['risk_limit'] = $risk_limit;
        }
        else{
            $skalaDampakFinal = $request->skala_dampak_hidden ?: $request->skala_dampak;
            $skalaDampakResidualFinal = $request->skala_dampak_residual_hidden ?: $request->skala_dampak_residual;

            // $toUpdate['skala_dampak'] = $request->skala_dampak_hidden;
            // $toUpdate['skala_dampak_residual'] = $request->skala_dampak_residual_hidden;

            $toUpdate['nilai_dampak'] = 0;
            $toUpdate['nilai_dampak_residual'] = 0;
            $toUpdate['risk_limit'] = 1/100*$risk_tolerance;
        }

        $toUpdate['skala_dampak'] = $skalaDampakFinal;
        $toUpdate['skala_dampak_residual'] = $skalaDampakResidualFinal;

        //dd($toUpdate);
        $skalaInherent = SkalaParameter::find($request->skala_parameter_id);
        $skalaResidual = SkalaParameter::find($request->skala_parameter_residual_id);

        // [UPDATE] disabled tingkat skala probabilitas residual validasi
        // // Validasi tingkat residual tidak boleh > inheren
        // if ($skalaResidual->tingkat > $skalaInherent->tingkat) {
        //     return response()->json([
        //         'message' => 'Tingkat skala probabilitas residual tidak boleh lebih tinggi dari inheren.',
        //     ], 422);
        // }

        // $tingkatSkalaProbabilitas = SkalaProbabilitas::getSkalaByValue($request->nilai_probabilitas);
        // $tingkatSkalaProbabilitasResidual = SkalaProbabilitas::getSkalaByValue($request->nilai_probabilitas_residual);

        $tingkatSkalaProbabilitas = SkalaProbabilitas::where('tingkat', $skalaInherent->tingkat)->first();
        $tingkatSkalaProbabilitasResidual = SkalaProbabilitas::where('tingkat', $skalaResidual->tingkat)->first();

        if (!$tingkatSkalaProbabilitas || !$tingkatSkalaProbabilitasResidual) {
            return response()->json([
                'message' => 'Tidak ada data skala probabilitas yang sesuai',
            ], 422);
        }

        $toUpdate['skala_probabilitas_id'] = $tingkatSkalaProbabilitas->id ?? null;
        $toUpdate['skala_probabilitas_residual_id'] = $tingkatSkalaProbabilitasResidual->id ?? null;

        $riskMap = $riskMaps[$skalaDampakFinal . '-' . $tingkatInherent] ?? null;

        if (!$riskMap) {
            return response()->json([
                'message' => 'Tidak ada data risk map untuk skala dampak dan probabilitas yang dipilih',
            ], 422);
        }

        $riskMapResidual = $riskMaps[$toUpdate['skala_dampak_residual'] . '-' . $tingkatSkalaProbabilitasResidual->tingkat] ?? null;
        if (!$riskMapResidual) {
            return response()->json([
                'message' => 'Tidak ada data risk map untuk skala dampak residual dan probabilitas residual yang dipilih',
            ], 422);
        }

        $toUpdate['skala_risiko'] = $riskMap->nilai_risiko;
        $toUpdate['level_risiko'] = $riskMap->level_risiko;
        $toUpdate['skala_risiko_residual'] = $riskMapResidual->nilai_risiko;
        $toUpdate['level_risiko_residual'] = $riskMapResidual->level_risiko;

        if ($request->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF) {
            $toUpdate['eksposur_risiko'] = ($toUpdate['skala_dampak'] * (1/100)) * $toUpdate['nilai_probabilitas'] / 100 * $toUpdate['risk_limit'];
            $toUpdate['eksposur_risiko_residual'] = ($toUpdate['skala_dampak_residual'] * (1/100)) * $toUpdate['nilai_probabilitas_residual'] / 100 * $toUpdate['risk_limit'];
        } elseif ($request->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF) {
            $toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * $toUpdate['nilai_probabilitas'] / 100;
            $toUpdate['eksposur_risiko_residual'] = $toUpdate['nilai_dampak_residual'] * $toUpdate['nilai_probabilitas_residual'] / 100;
        }

        $analisa->update($toUpdate);

        $projectRisk->update([
            'skala_risiko' => $toUpdate['skala_risiko'],
            'level_risiko' => $toUpdate['level_risiko'],
        ]);

        // $projectPeriodeList->recalculateAnalisa($risk_limit);
        $projectPeriodeList->refreshNilai();

        return [
            'message' => 'Analisa berhasil disimpan',
        ];
    }

    public function rencana(Request $request, $resource) {
        $projectRisk = ProjectRisk::with([
            'penyebabRisikoProjects.perlakuanPenyebabRisiko',
            'perlakuanDampakRisikos',
        ])->findOrFail(request()->route('risk'));

        //dd($projectRisk->penyebabRisikoProjects);

        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriodeList))) {
        //     abort(403);
        // }

        $project = $projectPeriodeList->project;
        $periode = $projectPeriodeList->periode;
        $analisa = $projectRisk->projectRiskAnalisa;

        return view('project-risk.rencana', compact('projectRisk', 'project', 'periode', 'projectPeriodeList', 'analisa'));
    }

    public function doRencana(Request $request) {
        $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        // if (!(Gate::check('project_admin_access') || !$user->hasProject($projectPeriodeList))) {
        //     abort(403);
        // }

        $penyebabRisiko = $projectRisk->penyebabRisikoProjects()->where('id', $request->id)->firstOrFail();

        $penyebabRisiko->update($request->all());

        return [
            'messages' => 'Berhasil Tersimpan',
            'penyebab' => $penyebabRisiko
        ];
    }

    public function simpanRencanaPerlakuan(Request $request) {

        $validated = $request->validate([
            'penyebab_risiko_id' => 'required|exists:penyebab_risiko_projects,id',
            'rencana_perlakuan_risiko' => 'required|string',
            'output_perlakuan_risiko' => 'required|string',
            'biaya_perlakuan_risiko' => 'required|numeric|min:0',
            'pic' => 'required',
            'divisi_terkait' => 'nullable|array',
            'divisi_terkait.*' => 'exists:units,id',
            //'timeline_perlakuan_risiko' => 'required|string',
            'timeline_mulai_perlakuan_risiko' => 'required',
            'timeline_selesai_perlakuan_risiko' => 'required',
            'opsi_perlakuan_risiko' => 'required|exists:opsi_perlakuan_risikos,id',
            // 'jenis_rencana_perlakuan_risiko' => 'required|exists:jenis_rencana_perlakuan_risikos,id',
        ]);

        $penyebabRisiko = PenyebabRisikoProject::with('risiko.project')->findOrFail($validated['penyebab_risiko_id']);

        $project = $penyebabRisiko->risiko->project;
        $batasNilai = $project->batas_nilai;

        if ($batasNilai > 0) {
            $biayaPerlakuanRisiko = $validated['biaya_perlakuan_risiko'];
            $totalBiayaPerlakuanRisiko = $penyebabRisiko->perlakuanPenyebabRisiko()->sum('biaya_perlakuan_risiko') + $biayaPerlakuanRisiko;
            if ($totalBiayaPerlakuanRisiko > $batasNilai) {
                return response()->json([
                    'message' => 'Total biaya perlakuan risiko tidak boleh lebih besar dari Rp. ' . number_format($batasNilai, 0, ',', '.'),
                ], 422);
            }
        }

        //$timeline = explode(' to ', $validated['timeline_perlakuan_risiko']);

        //$startDate = $timeline[0] ?? null;
        //$endDate = $timeline[1] ?? null;

        $startDate = $validated['timeline_mulai_perlakuan_risiko'] ?? null;
        $endDate = $validated['timeline_selesai_perlakuan_risiko']?? null;

        if (!$endDate) {
            $endDate = $startDate;
        }

        // Validasi format tanggal
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Format tanggal tidak valid. Pastikan rentang tanggal dipilih dengan benar.',
            ], 422);
        }

        // Validasi bahwa startDate <= endDate
        if (strtotime($startDate) > strtotime($endDate)) {
            return response()->json([
                'message' => 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.',
            ], 422);
        }

        $jabatan = Jabatan::find($validated['pic']);
        $jabatan_name = "-";
        if($jabatan){
            $jabatan_name = $jabatan->name;
        }

        $perlakuan = PerlakuanPenyebabRisiko::create([
            'penyebab_risiko_id' => $validated['penyebab_risiko_id'],
            'rencana_perlakuan_risiko' => $validated['rencana_perlakuan_risiko'],
            'output_perlakuan_risiko' => $validated['output_perlakuan_risiko'],
            'biaya_perlakuan_risiko' => $validated['biaya_perlakuan_risiko'],
            'pic' => $jabatan_name,
            'pic_jabatan_id' => $validated['pic'],
            'divisi_terkait' => $validated['divisi_terkait'] ?? [],
            'timeline_perlakuan_risiko_start' => $startDate,
            'timeline_perlakuan_risiko_end' => $endDate,
            'opsi_perlakuan_risiko' => $validated['opsi_perlakuan_risiko'],
            // 'jenis_rencana_perlakuan_risiko' => $validated['jenis_rencana_perlakuan_risiko'],
        ]);

        //return response()->json(['message' => 'Rencana untuk penyebab risiko id : '.$request->penyebab_risiko_id.'  berhasil ditambahkan! ']);

        return response()->json([
            'message' => 'Rencana untuk penyebab risiko berhasil ditambahkan!',
            //'data' => $perlakuan,
        ]);
    }

    public function hapusRencanaPerlakuan($id) {
        try {
            // Cari data berdasarkan ID dan hapus
            $perlakuan = PerlakuanPenyebabRisiko::findOrFail($id);
            $perlakuan->delete();

            // Kembalikan respons JSON
            return response()->json([
                'message' => 'Rencana perlakuan berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            // Tangani error dan kembalikan respons JSON
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data. '.$e->getMessage()
            ], 500);
        }
    }

    public function editRencanaPerlakuan($id)
    {
        $perlakuan = PerlakuanPenyebabRisiko::with('penyebabRisikoProject')->findOrFail($id);

        return response()->json([
            'id' => $perlakuan->id,
            'penyebab_risiko_id' => $perlakuan->penyebab_risiko_id,
            'penyebab_risiko' => $perlakuan->penyebabRisikoProject->penyebab_risiko ?? null, // Dapatkan nama penyebab risiko
            'rencana_perlakuan_risiko' => $perlakuan->rencana_perlakuan_risiko,
            'output_perlakuan_risiko' => $perlakuan->output_perlakuan_risiko,
            'opsi_perlakuan_risiko' => $perlakuan->opsi_perlakuan_risiko,
            'divisi_terkait' => $perlakuan->divisi_terkait,
            // 'jenis_rencana_perlakuan_risiko' => $perlakuan->jenis_rencana_perlakuan_risiko,
            'biaya_perlakuan_risiko' => $perlakuan->biaya_perlakuan_risiko,
            'pic' => $perlakuan->pic,
            'pic_jabatan_id' => $perlakuan->pic_jabatan_id,
            'timeline_perlakuan_risiko_start' => $perlakuan->timeline_perlakuan_risiko_start ? $perlakuan->timeline_perlakuan_risiko_start->format('d/m/Y') : null,
            'timeline_perlakuan_risiko_end' => $perlakuan->timeline_perlakuan_risiko_end ? $perlakuan->timeline_perlakuan_risiko_end->format('d/m/Y') : null,
        ]);
    }

    public function updateRencanaPerlakuan(Request $request, $id)
    {
        $validated = $request->validate([
            'xrencana_perlakuan_risiko' => 'required',
            'xoutput_perlakuan_risiko' => 'required',
            'xopsi_perlakuan_risiko' => 'required',
            // 'xjenis_rencana_perlakuan_risiko' => 'required',
            'xbiaya_perlakuan_risiko' => 'required|numeric',
            'xpic' => 'required',
            'xdivisi_terkait' => 'nullable|array',
            'xdivisi_terkait.*' => 'exists:units,id',
            //'xtimeline_perlakuan_risiko' => 'required',
            'xtimeline_mulai_perlakuan_risiko' => 'required',
            'xtimeline_selesai_perlakuan_risiko' => 'required',
        ]);

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $validated['xtimeline_mulai_perlakuan_risiko']);
            $endDate = Carbon::createFromFormat('d/m/Y', $validated['xtimeline_selesai_perlakuan_risiko']);

            if ($startDate > $endDate) {
                return response()->json([
                    'message' => 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.'
                ], 422);
            }

            $jabatan = Jabatan::find($validated['xpic']);
            $jabatan_name = "-";
            if($jabatan){
                $jabatan_name = $jabatan->name;
            }

            $data = [
                'rencana_perlakuan_risiko' => $validated['xrencana_perlakuan_risiko'],
                'output_perlakuan_risiko' => $validated['xoutput_perlakuan_risiko'],
                'opsi_perlakuan_risiko' => $validated['xopsi_perlakuan_risiko'],
                // 'jenis_rencana_perlakuan_risiko' => $validated['xjenis_rencana_perlakuan_risiko'],
                'biaya_perlakuan_risiko' => $validated['xbiaya_perlakuan_risiko'],
                'pic' => $jabatan_name,
                'pic_jabatan_id' => $validated['xpic'],
                'divisi_terkait' => $request->input('xdivisi_terkait', []),
                'pic_jabatan_id' => $validated['xpic'],
                'timeline_perlakuan_risiko_start' => $startDate->format('Y-m-d'),
                'timeline_perlakuan_risiko_end' => $endDate->format('Y-m-d'),
            ];

            //dd($startDate);

            $perlakuan = PerlakuanPenyebabRisiko::findOrFail($id);

            // Pisahkan timeline
            //$timeline = explode(' to ', $validated['xtimeline_perlakuan_risiko']);
            // $tl1 = $validated['xtimeline_mulai_perlakuan_risiko'];
            // $tl2 = $validated['xtimeline_selesai_perlakuan_risiko'];
            // $timeline = [$tl1, $tl2];

            // $data['timeline_perlakuan_risiko_start'] = isset($timeline[0]) ? \Carbon\Carbon::createFromFormat('d/m/Y', $timeline[0])->format('Y-m-d') : null;
            // $data['timeline_perlakuan_risiko_end'] = isset($timeline[1]) ? \Carbon\Carbon::createFromFormat('d/m/Y', $timeline[1])->format('Y-m-d') : null;

            // Update data
            $perlakuan->update($data);

            return response()->json([
                'message' => 'Rencana perlakuan berhasil diperbarui.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function simpanRencanaPerlakuanDampak(Request $request)
    {
        $validated = $request->validate([
            'risiko_id' => 'required|exists:project_risks,id',
            'dampak_risiko_id' => 'required|exists:dampak_risiko_projects,id',
            'rencana_perlakuan_risiko' => 'required',
            'output_perlakuan_risiko' => 'required',
            'biaya_perlakuan_risiko' => 'required|numeric',
            'pic' => 'required',
            'opsi_perlakuan_risiko' => 'required',
            'timeline_mulai_perlakuan_risiko' => 'required',
            'timeline_selesai_perlakuan_risiko' => 'required',
        ]);

        $jabatan = Jabatan::find($request->pic);

        PerlakuanDampakRisiko::create([
            'risiko_id' => $request->risiko_id,
            'dampak_risiko_id' => $request->dampak_risiko_id,
            'rencana_perlakuan_risiko' => $request->rencana_perlakuan_risiko,
            'output_perlakuan_risiko' => $request->output_perlakuan_risiko,
            'biaya_perlakuan_risiko' => $request->biaya_perlakuan_risiko,
            'pic' => $jabatan?->name ?? '-',
            'pic_jabatan_id' => $request->pic,
            'divisi_terkait' => $request->divisi_terkait ?? [],
            'opsi_perlakuan_risiko' => $request->opsi_perlakuan_risiko,
            'timeline_perlakuan_risiko_start' => Carbon::createFromFormat('d/m/Y', $request->timeline_mulai_perlakuan_risiko)->format('Y-m-d'),
            'timeline_perlakuan_risiko_end' => Carbon::createFromFormat('d/m/Y', $request->timeline_selesai_perlakuan_risiko)->format('Y-m-d'),
        ]);

        return response()->json(['message' => 'Rencana Perlakuan Dampak berhasil ditambahkan!']);
    }

    public function editRencanaPerlakuanDampak($id)
    {
        $perlakuan = PerlakuanDampakRisiko::with('projectRisk', 'dampakRisikoProject')->findOrFail($id);

        return response()->json([
            'id'                => $perlakuan->id,
            'risiko_id'         => $perlakuan->risiko_id,
            'dampak_risiko_id' => $perlakuan->dampak_risiko_id,
            'deskripsi_dampak'  => $perlakuan->dampakRisikoProject->dampak_risiko,
            'rencana_perlakuan_risiko'           => $perlakuan->rencana_perlakuan_risiko,
            'output_perlakuan_risiko'            => $perlakuan->output_perlakuan_risiko,
            'opsi_perlakuan_risiko'              => $perlakuan->opsi_perlakuan_risiko,
            'biaya_perlakuan_risiko'             => $perlakuan->biaya_perlakuan_risiko,
            'pic_jabatan_id'            => $perlakuan->pic_jabatan_id,
            'divisi_terkait'    => $perlakuan->divisi_terkait,
            'timeline_perlakuan_risiko_start'         => $perlakuan->timeline_perlakuan_risiko_start ? $perlakuan->timeline_perlakuan_risiko_start->format('d/m/Y') : null,
            'timeline_perlakuan_risiko_end'       => $perlakuan->timeline_perlakuan_risiko_end ? $perlakuan->timeline_perlakuan_risiko_end->format('d/m/Y') : null,
        ]);
    }

    public function updateRencanaPerlakuanDampak(Request $request, $id)
    {
        $validated = $request->validate([
            'xd_rencana_perlakuan_risiko' => 'required',
            'xd_output_perlakuan_risiko'  => 'required',
            'xd_opsi_perlakuan_risiko'    => 'required',
            'xd_biaya_perlakuan_risiko'   => 'required|numeric',
            'xd_pic'                      => 'required',
            'xd_divisi_terkait'           => 'nullable|array',
            'xd_timeline_mulai_perlakuan_risiko'   => 'required',
            'xd_timeline_selesai_perlakuan_risiko' => 'required',
        ]);

        $perlakuan = PerlakuanDampakRisiko::findOrFail($id);
        $jabatan = Jabatan::find($request->xd_pic);

        $perlakuan->update([
            'rencana_perlakuan_risiko' => $validated['xd_rencana_perlakuan_risiko'],
            'output_perlakuan_risiko'  => $validated['xd_output_perlakuan_risiko'],
            'opsi_perlakuan_risiko'    => $validated['xd_opsi_perlakuan_risiko'],
            'biaya_perlakuan_risiko'   => $validated['xd_biaya_perlakuan_risiko'],
            'pic'                      => $jabatan?->name ?? '-',
            'pic_jabatan_id'           => $validated['xd_pic'],
            'divisi_terkait'           => $request->xd_divisi_terkait ?? [],
            'timeline_perlakuan_risiko_start' => Carbon::createFromFormat('d/m/Y', $validated['xd_timeline_mulai_perlakuan_risiko'])->format('Y-m-d'),
            'timeline_perlakuan_risiko_end'   => Carbon::createFromFormat('d/m/Y', $validated['xd_timeline_selesai_perlakuan_risiko'])->format('Y-m-d'),
        ]);

        return response()->json(['message' => 'Rencana perlakuan dampak berhasil diperbarui.']);
    }

    public function hapusRencanaPerlakuanDampak($id)
    {
        try {
            $perlakuan = PerlakuanDampakRisiko::findOrFail($id);

            // Eksekusi penghapusan
            $perlakuan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Rencana perlakuan dampak berhasil dihapus.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importTender(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $projectPeriodeListId = $request->route('project');
        $projectPeriodeList = ProjectPeriodeList::with('project')->findOrFail($projectPeriodeListId);

        DB::beginTransaction();
        try {
            $import = new ProjectTenderImport(
                $projectPeriodeList,
                auth()->user()
            );

            Excel::import($import, $request->file('file'));

            DB::commit();

            $summary = [
                'success' => $import->getSuccessCount(),
                'skipped' => $import->getSkippedCount(),
                'failed' => $import->getFailedCount(),
                'skipped_rows' => $import->getSkippedRows(),
                'failed_rows' => $import->getFailedRows()
            ];

            return redirect()->back()->with('import_summary', $summary);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }
            return back()->with('error', 'Terjadi kesalahan validasi: ' . implode(' | ', $errorMessages));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import Tender Gagal: ' . $e->getMessage() . ' di baris ' . $e->getLine());
            return back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function downloadTenderTemplate(Request $request)
    {
        try {
            $peristiwaRisikoData = PeristiwaRisiko::where('type', 2)->get();
            $jenisRisikoData = JenisRisiko::with('kategoriRisiko')->get();
            // $templatePath = storage_path('app/public/templates/template-tender.xlsx');
            $templatePath = resource_path('templates/template-tender.xlsx');

            // Pastikan file template ada
            if (!file_exists($templatePath)) {
                return response()->json(['error' => 'Template file not found'], 404);
            }

            $outputFileName = 'Dokumen Upload Tender.xlsx';
            $modifier = new TenderTemplateImport($templatePath, $peristiwaRisikoData, $jenisRisikoData);
            $spreadsheet = $modifier->getModifiedSpreadsheet();
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

            ob_start();
            $writer->save('php://output');
            $fileContents = ob_get_clean();

            return response($fileContents, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $outputFileName . '"',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error processing template: ' . $e->getMessage()], 500);
        }
    }

    public function getKontrolEksisting(Request $request)
    {
        $kontrolEksistings = KontrolEksisting::where('peristiwa_risiko_id', $request->peristiwa_risiko_id)->get();

        return response()->json($kontrolEksistings);
    }

    // Fungsi cleanRupiah diletakkan di dalam controller
    private function cleanRupiah($value) {
        if (empty($value)) return 0;
        return (float) str_replace(['Rp', '.', ','], ['', '', ''], $value);
    }

    function hitungSkalaDampak($nilai_dampak, $risk_limit) {
        if ($risk_limit <= 0) {
            return 5; // Jika risk limit 0 atau negatif, default ke High (5)
        }

        $persentase = ($nilai_dampak / $risk_limit) * 100;

        if ($persentase <= 20) {
            return 1; // Low
        } elseif ($persentase > 20 && $persentase <= 40) {
            return 2; // Low to Moderate
        } elseif ($persentase > 40 && $persentase <= 60) {
            return 3; // Moderate
        } elseif ($persentase > 60 && $persentase <= 80) {
            return 4; // Moderate to High
        } else {
            return 5; // High
        }
    }

    function getSkalaDampakResidual(Request $request){
        $areaId = $request->input('areaId');
        $skalaDampakIn = $request->input('skalaDampak');

        // Ambil area dampak beserta detailnya yang sesuai dengan areaId
        $areas = AreaDampak::where('id', $areaId)
                    ->with('details')
                    ->get();

        // Group berdasarkan kategori risiko
        $groupedAreas = $areas->groupBy('risk_category');

        // Render blade sebagai response AJAX
        $html = view('partials.kualitatif_table', compact('groupedAreas', 'skalaDampakIn'))->render();

        return response()->json(['html' => $html]);
    }

    public function calculatePoisson(Request $request)
    {
        try {
            $projectRisk = ProjectRisk::findOrFail($request->project_risk_id);

            // Lakukan perhitungan Poisson di sini
            // Contoh sederhana (sesuaikan dengan kebutuhan):
            $probability = $this->calculatePoissonProbability($projectRisk);

            return response()->json([
                'success' => true,
                'probability' => $probability
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan perhitungan Poisson'
            ], 500);
        }
    }

    public function calculatePoissonRes(Request $request)
    {
        try {
            $projectRisk = ProjectRisk::findOrFail($request->project_risk_id);

            // Lakukan perhitungan Poisson di sini
            // Contoh sederhana (sesuaikan dengan kebutuhan):
            $probability = $this->calculatePoissonProbabilityRes($projectRisk);

            return response()->json([
                'success' => true,
                'probability' => $probability
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan perhitungan Poisson'
            ], 500);
        }
    }

    private function calculatePoissonProbability($projectRisk)
    {
        try {
            // 1. Ambil project_sektor_id dari Project
            $project = Project::find($projectRisk->project_id);
            if (!$project) {
                throw new \Exception('Project tidak ditemukan');
            }
            $projectSektorId = $project->project_sektor_id;

            // 2. Ambil tahun dari meta project atau gunakan tahun berjalan
            $tahun = date('Y'); // default tahun berjalan
            // if ($project->meta && isset($project->meta['start_date'])) {
            //     $tahun = Carbon::parse($project->meta['start_date'])->year;
            // }

            // 3. Ambil peristiwa_risiko_id
            $peristiwaRisikoId = $projectRisk->peristiwa_risiko_id;

            // 4 & 5. Ambil data kejadian 5 tahun kebelakang
            $tahunMulai = $tahun - 5;
            $tahunAkhir = $tahun - 1;

            // Ambil data kejadian dan group by tahun
            $kejadianPerTahun = LossEventProject::where('peristiwa_risiko_id', $peristiwaRisikoId)
                ->where('project_sektor_id', $projectSektorId)
                ->where('tahun', '>=', $tahunMulai)
                ->where('tahun', '<=', $tahunAkhir)
                ->selectRaw('tahun, COUNT(*) as jumlah')
                ->groupBy('tahun')
                ->pluck('jumlah', 'tahun')
                ->toArray();

            // 6. Isi tahun yang kosong dengan nilai 0
            $dataKejadian = [];
            for ($t = $tahunMulai; $t <= $tahunAkhir; $t++) {
                $dataKejadian[$t] = $kejadianPerTahun[$t] ?? 0;
            }

            // 7. Hitung probabilitas Poisson
            $lambda = array_sum($dataKejadian) / count($dataKejadian); // rata-rata kejadian per tahun

            // Hitung probabilitas minimal 1 kejadian (P(X ≥ 1) = 1 - P(X = 0))
            $probabilitas = (1 - exp(-$lambda)) * 100;
            $probabilitasFinal = min(round($probabilitas, 2), 100);

            // Log calculation details
            $logData = [
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'project_risk_id' => $projectRisk->id,
                'project_id' => $project->id,
                'tahun_start_date' => $tahun,
                'peristiwa_risiko_id' => $peristiwaRisikoId,
                'project_sektor_id' => $projectSektorId,
                'data_kejadian' => $dataKejadian,
                'lambda' => $lambda,
                'hasil_poisson' => $probabilitasFinal,
                'query' => LossEventProject::where('peristiwa_risiko_id', $peristiwaRisikoId)
                    ->where('project_sektor_id', $projectSektorId)
                    ->where('tahun', '>=', $tahunMulai)
                    ->where('tahun', '<=', $tahunAkhir)
                    ->toSql()
            ];

            // Write to poisson.log
            Log::channel('poisson')->info('Poisson Calculation', $logData);

            return $probabilitasFinal;

        } catch (\Exception $e) {
            \Log::error('Error in calculatePoissonProbability: ' . $e->getMessage());
            throw new \Exception('Gagal menghitung probabilitas: ' . $e->getMessage());
        }
    }

    private function calculatePoissonProbabilityRes($projectRisk)
    {
        try {
            // 1. Ambil project_sektor_id dari Project
            $project = Project::find($projectRisk->project_id);
            if (!$project) {
                throw new \Exception('Project tidak ditemukan');
            }
            $projectSektorId = $project->project_sektor_id;

            // 2. Ambil tahun dari meta project atau gunakan tahun berjalan
            $tahun = date('Y'); // default tahun berjalan
            // if ($project->meta && isset($project->meta['start_date'])) {
            //     $tahun = Carbon::parse($project->meta['start_date'])->year;
            // }

            // 3. Ambil peristiwa_risiko_id
            $peristiwaRisikoId = $projectRisk->peristiwa_risiko_id;

            // 4 & 5. Ambil data kejadian 5 tahun kebelakang
            $tahunMulai = $tahun - 4;
            $tahunAkhir = $tahun - 1;

            // Ambil data kejadian dan group by tahun
            $kejadianPerTahun = LossEventProject::where('peristiwa_risiko_id', $peristiwaRisikoId)
            ->where('project_sektor_id', $projectSektorId)
            ->where('tahun', '>=', $tahunMulai)
            ->where('tahun', '<=', $tahunAkhir)
            ->selectRaw('tahun, COUNT(*) as jumlah')
            ->groupBy('tahun')
            ->pluck('jumlah', 'tahun')
            ->toArray();

            //Isi tahun yang kosong dengan nilai 0 (termasuk tahun berjalan)
            $dataKejadian = [];
            for ($t = $tahunMulai; $t <= $tahun; $t++) {
                $dataKejadian[$t] = $kejadianPerTahun[$t] ?? 0;
            }

            //Hitung probabilitas Poisson
            $lambda = array_sum($dataKejadian) / count($dataKejadian); // rata-rata kejadian per tahun

            // Hitung probabilitas minimal 1 kejadian (P(X ≥ 1) = 1 - P(X = 0))
            $probabilitas = (1 - exp(-$lambda)) * 100;
            $probabilitasFinal = min(round($probabilitas, 2), 100);

            // Log calculation details
            $logData = [
                'timestamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'project_risk_id' => $projectRisk->id,
                'project_id' => $project->id,
                'tahun_start_date' => $tahun,
                'peristiwa_risiko_id' => $peristiwaRisikoId,
                'project_sektor_id' => $projectSektorId,
                'data_kejadian' => $dataKejadian,
                'lambda' => $lambda,
                'hasil_poisson' => $probabilitasFinal,
                'query' => LossEventProject::where('peristiwa_risiko_id', $peristiwaRisikoId)
                    ->where('project_sektor_id', $projectSektorId)
                    ->where('tahun', '>=', $tahunMulai)
                    ->where('tahun', '<=', $tahunAkhir)
                    ->toSql()
            ];

            // Write to poisson.log
            Log::channel('poisson')->info('Poisson Calculation', $logData);

            return $probabilitasFinal;

        } catch (\Exception $e) {
            \Log::error('Error in calculatePoissonProbability: ' . $e->getMessage());
            throw new \Exception('Gagal menghitung probabilitas: ' . $e->getMessage());
        }
    }

    public function send(Request $request)
    {
        $user = auth()->user();
        $projectPeriodeListId = $request->input('project_id');
        $projectPeriodeList = ProjectPeriodeList::findOrFail($projectPeriodeListId);
        $project_id = $projectPeriodeList->project_id;
        $send_type = $request->input('send_type', 'risiko');
        $periode_id = 0; // Default

        // 1. Validasi Data Risiko (Harus Ada & Lengkap)
        $risikos = ProjectRisk::with([
                'projectRiskAnalisa',
                'penyebabRisikoProjects.perlakuanPenyebabRisiko',
                'dampakRisikoProjects.perlakuanDampakRisikos',
            ])
            ->where('project_id', $project_id)
            ->where('periode_id', $periode_id)
            ->where('status', '!=', 6) // Bukan Published
            ->get();

        if ($risikos->isEmpty()) {
            return redirect()->route('projects.risks.index', ['project' => $projectPeriodeListId])
                ->with('error', 'Tidak ada risiko yang dapat dikirim.');
        }

        // Cek Kelengkapan (Analisa & Perlakuan)
        $errBelumAnalisa = [];
        $errBelumAdaPerlakuanPenyebab = [];
        $errBelumAdaDampak = [];
        $errBelumAdaPerlakuanDampak = [];

        foreach ($risikos as $risiko) {
            // Ambil deskripsi risiko untuk pesan error
            $deskripsi = $risiko->deskripsi_peristiwa_risiko ?: ($risiko->peristiwaRisiko ? $risiko->peristiwaRisiko->title : 'Risiko #' . $risiko->id);

            // A. Cek Analisa
            if (!$risiko->projectRiskAnalisa) {
                $errBelumAnalisa[] = $deskripsi;
            }

            // B. Cek Perlakuan Penyebab (Jika penyebab ada, perlakuan harus ada)
            if ($risiko->penyebabRisikoProjects->isNotEmpty()) {
                foreach ($risiko->penyebabRisikoProjects as $penyebab) {
                    if ($penyebab->perlakuanPenyebabRisiko->isEmpty()) {
                        $errBelumAdaPerlakuanPenyebab[] = $deskripsi;
                        break;
                    }
                }
            }

            // C. Cek Dampak Risiko (Harus ada)
            if ($risiko->dampakRisikoProjects->isEmpty()) {
                $errBelumAdaDampak[] = $deskripsi;
            } else {
                // D. Cek Perlakuan Dampak (Jika dampak ada, perlakuan harus ada)
                foreach ($risiko->dampakRisikoProjects as $dampak) {
                    if ($dampak->perlakuanDampakRisikos->isEmpty()) {
                        $errBelumAdaPerlakuanDampak[] = $deskripsi;
                        break;
                    }
                }
            }
        }

        // Susun Pesan Error jika ada temuan
        $pesanError = '';

        if (!empty($errBelumAnalisa)) {
            $pesanError .= '<strong>Risiko berikut belum dianalisa:</strong><ul>';
            foreach ($errBelumAnalisa as $d) { $pesanError .= "<li>$d</li>"; }
            $pesanError .= '</ul>';
        }

        if (!empty($errBelumAdaPerlakuanPenyebab)) {
            $pesanError .= '<strong>Risiko berikut belum memiliki rencana perlakuan penyebab:</strong><ul>';
            foreach ($errBelumAdaPerlakuanPenyebab as $d) { $pesanError .= "<li>$d</li>"; }
            $pesanError .= '</ul>';
        }

        if (!empty($errBelumAdaDampak)) {
            $pesanError .= '<strong>Risiko berikut belum memiliki daftar dampak:</strong><ul>';
            foreach ($errBelumAdaDampak as $d) { $pesanError .= "<li>$d</li>"; }
            $pesanError .= '</ul>';
        }

        if (!empty($errBelumAdaPerlakuanDampak)) {
            $pesanError .= '<strong>Risiko berikut belum memiliki rencana perlakuan dampak:</strong><ul>';
            foreach ($errBelumAdaPerlakuanDampak as $d) { $pesanError .= "<li>$d</li>"; }
            $pesanError .= '</ul>';
        }

        // Jika pesan error tidak kosong, kembalikan dengan pesan error
        if (!empty($pesanError)) {
            $pesanError .= 'Silahkan lengkapi data tersebut terlebih dahulu.';
            return redirect()->route('projects.risks.index', ['project' => $projectPeriodeListId])
                ->with('error', $pesanError);
        }

        // 2. Kelola Data Batch
        $dataBatch = DataBatch::where('project_id', $project_id)
                ->where('periode_id', $periode_id)
                ->where('type', 2)
                ->orderBy('batch', 'desc')
                ->first();

        // dd($dataBatch, $send_type);

        // KASUS A: Kirim Perbaikan (Revisi)
        if ($send_type == 'perbaikan' && $dataBatch && !$dataBatch->finish) {
            if ($dataBatch->step_verification == 1) {
                // Revisi dari Step 1 -> Kirim Ulang ke Step 1 (Owner Project)
                $dataBatch->update(['status' => DataBatch::STATUS_KIRIM]); // Status 2
            } else if ($dataBatch->step_verification > 1) {
                // Revisi dari Step > 1 (Divisi) -> Kembalikan ke Flow Normal (Verifikasi)
                $dataBatch->update(['status' => DataBatch::STATUS_VERIFIKASI]); // Status 4
            }
            $dataBatch->refresh();

            // Update status risiko yang tadinya REJECTED (5) menjadi DIKIRIM (2) atau TUNGGU VERIF (3)
            ProjectRisk::where('project_id', $project_id)
                ->where('periode_id', $periode_id)
                ->whereIn('status', [ProjectRisk::STATUS_REJECTED, ProjectRisk::STATUS_INPUT_DATA, 0]) // 5 dan 1 dan 0
                ->update([
                    'status' => ($dataBatch->step_verification == 1) ? ProjectRisk::STATUS_DIKIRIM : ProjectRisk::STATUS_TUNGGU_VERIFIKASI,
                    'status_progress' => 1,
                    'step_verification' => 1,
                ]);
        }
        // KASUS B: Kirim Baru (Pertama Kali atau Batch Baru)
        else {
            // Cek apakah batch terakhir sudah finish? Jika belum, pakai itu. Jika sudah/tidak ada, buat baru.
            if ($dataBatch && !$dataBatch->finish) {
                // Batch ada dan belum finish -> Update statusnya saja
                if ($dataBatch->status == DataBatch::STATUS_PROSES) {
                    $dataBatch->update([
                        'status' => DataBatch::STATUS_KIRIM, // 2
                        'step_verification' => 1 // Masuk ke Risk Owner Project
                    ]);
                } else {
                    // Safety check: jika statusnya aneh tapi user kirim (mungkin refresh page)
                    // Biarkan saja atau return warning
                }
            } else {
                // Buat Batch Baru
                $newBatchNo = ($dataBatch) ? $dataBatch->batch + 1 : 1;

                $dataBatch = DataBatch::create([
                    'periode_id' => $periode_id,
                    'type' => 2,
                    'project_id' => $project_id,
                    'batch' => $newBatchNo,
                    'status' => DataBatch::STATUS_KIRIM, // Langsung set status KIRIM (2)
                    'step_verification' => 1,
                    'finish' => false
                ]);
            }

            // Update Semua Risiko Baru (Status Input -> Dikirim)
            // Pastikan kita menggunakan $dataBatch yang valid (baru atau existing)
            if ($dataBatch->status <= DataBatch::STATUS_KIRIM) {
                ProjectRisk::where('project_id', $project_id)
                    ->where('periode_id', $periode_id)
                    ->where('status', ProjectRisk::STATUS_INPUT_DATA) // 1
                    ->update([
                        'status' => ProjectRisk::STATUS_DIKIRIM, // 2
                        'status_risiko' => 1,
                        'status_progress' => 1,
                        'step_verification' => 1 // Set ke Step 1 (Owner Project)
                    ]);
            }
        }

        $targetLink = route('projects.risks.index', ['project' => $projectPeriodeListId]);
        $msg = 'Risk Officer Proyek telah mengirimkan / memperbaiki risiko untuk diverifikasi.';

        // Jika dikirim dari step awal (Risk Officer ke Risk Owner Project)
        if ($dataBatch->step_verification == 1) {
            $this->sendNotificationCustom('RW_PROYEK', $projectPeriodeListId, 'Menunggu Verifikasi', $msg, $targetLink, 'bx bx-bell');
        }
        // Bisa tambahkan ElseIf jika Drafter langsung lompat ke step divisi/MR di skenario revisi.
        elseif ($dataBatch->step_verification == 2) {
            $this->sendNotificationCustom('RO_DIVISI', $projectPeriodeListId, 'Perbaikan Dikirim', $msg, $targetLink, 'bx bx-bell');
        }

        return redirect()->route('projects.risks.index', ['project' => $projectPeriodeListId])->with('success', 'Pengiriman risiko berhasil dilakukan. Risiko telah dikirim untuk diverifikasi.');
    }

    private function getFlowData($project_id, $level_id)
    {
        $step_order = 0;
        $min_verification = 4;
        $approval_step_id = null;

        // Jika bukan risk owner atau level_id null, kembalikan 0
        if ($level_id == 1 || $level_id == null) {
            return [
                'step_order' => $step_order,
                'min_verification' => $min_verification,
                'approval_step_id' => $approval_step_id
            ];
        }

        $approvalFlow = ApprovalFlow::where('project_id', $project_id)
            ->whereNull('unit_id')
            ->first();

        if ($approvalFlow) {
            $min_verification = $approvalFlow->min_verification;
            $approvalStep = ApprovalStep::where('approval_flow_id', $approvalFlow->id)
                ->where('level_id', $level_id)
                ->first();
            if ($approvalStep) {
                $step_order = $approvalStep->step_order;
                $approval_step_id = $approvalStep->id;
            } else {
                //default step order
                if ($level_id == 2) {
                    $step_order = 1;
                } else if ($level_id == 3) {
                    $step_order = 2;
                }
            }
        } else {
            //default step order jika tidak ada approval flow
            if ($level_id == 2) {
                $step_order = 1;
            } else if ($level_id == 3) {
                $step_order = 2;
            }
        }

        return [
            'step_order' => $step_order,
            'min_verification' => $min_verification,
            'approval_step_id' => $approval_step_id
        ];
    }

    //verifikasi
    public function verifikasi(Request $request, $id)
    {
        $projectRisk = ProjectRisk::findOrFail($id);
        $project_id = $projectRisk->project_id;

        $user = auth()->user();
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        // Dapatkan Posisi User (u_step)
        $verificationData = $this->getUserVerificationStep($user->level_id, $is_mr);
        $u_step = $verificationData['u_step'];
        $min_verification = 4;

        $request->validate([
            'status_verifikasi' => 'required|in:terima,tolak',
            'catatan_verifikasi' => 'required|string',
        ]);

        $dataBatch = DataBatch::where('project_id', $project_id)
            ->where('type', 2)
            ->where('finish', false)
            ->orderBy('batch', 'desc')
            ->first();

        if (!$dataBatch) return redirect()->back()->with('error', 'Data batch tidak ditemukan.');

        DB::beginTransaction();
        try {
            // == JIKA DITERIMA ==
            if ($request->status_verifikasi === 'terima') {

                // Jika User Step >= Min Verification (Final Step / Owner MR)
                if($u_step >= $min_verification){
                    $projectRisk->update([
                        'status' => ProjectRisk::STATUS_TERVERIFIKASI, // 4
                        'status_progress' => 3, // Accepted
                        'status_risiko' => 1,
                        'step_verification' => $u_step
                    ]);
                }
                // Jika Step Menengah (Owner Project, Officer Divisi, Officer MR)
                else {
                    $next_step = $u_step + 1;
                    $projectRisk->update([
                        'status' => ProjectRisk::STATUS_TUNGGU_VERIFIKASI, // 3
                        'status_progress' => 1, // On Review
                        'step_verification' => $next_step
                    ]);
                }

                $statusNote = 1;
            }
            // == JIKA DITOLAK ==
            else {
                // LOGIKA PENGEMBALIAN (REJECTION FLOW)
                $targetLink = route('projects.risks.index', ['project' => $projectRisk->project_periode_list_id]);
                $msg = 'Risiko ditolak dan dikembalikan untuk revisi. Catatan: ' . $request->catatan_verifikasi;

                // Kasus 1: Ditolak oleh Officer MR (Step 3) -> Kembali ke Officer Divisi (Step 2)
                if ($u_step == 3) {
                    $targetStep = 2; // Risk Officer Divisi
                    $projectRisk->update([
                        'status' => ProjectRisk::STATUS_REJECTED_FROM_OFFICER_MR, // 7
                        'status_progress' => 2, // Revision Needed
                        'step_verification' => $targetStep
                    ]);

                    // Ubah status Batch menjadi 9 (Rejected from MR)
                    // Supaya Officer Divisi bisa akses, dan Officer MR tidak bisa akses dulu
                    $dataBatch->update([
                        'step_verification' => $targetStep,
                        'status' => DataBatch::STATUS_REJECTED_FROM_OFFICER_MR // 9
                    ]);

                    // NOTIFIKASI: Jika Revisi, kasih notif ke Officer Divisi
                    $this->sendNotificationCustom('RO_DIVISI', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
                }
                // Kasus 2: Ditolak oleh Owner MR (Step 4) -> Kembali ke Officer MR (Step 3)
                else if ($u_step == 4) {
                    $targetStep = 3; // Risk Officer MR
                    $projectRisk->update([
                        'status' => ProjectRisk::STATUS_REJECTED_FROM_OWNER_MR, // 8
                        'status_progress' => 2, // Revision Needed
                        'step_verification' => $targetStep
                    ]);

                    // Ubah status Batch menjadi 10 (Rejected from MR)
                    // Supaya Officer MR bisa akses, dan Owner MR tidak bisa akses dulu
                    $dataBatch->update([
                        'step_verification' => $targetStep,
                        'status' => DataBatch::STATUS_REJECTED_FROM_OWNER_MR // 10
                    ]);

                    // NOTIFIKASI: Jika Revisi, kasih notif ke Officer MR
                    $this->sendNotificationCustom('RO_MR', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
                }
                // Kasus 3: Ditolak oleh Officer Divisi (Step 2) atau Owner Project (Step 1) -> Kembali ke Draft (Step 0/1)
                else {
                    $targetStep = 1; // Risk Officer Project (Input)

                    $projectRisk->update([
                        'status' => ProjectRisk::STATUS_REJECTED, // 5
                        'status_progress' => 2,
                        'step_verification' => $targetStep
                    ]);

                    $dataBatch->update([
                        'step_verification' => $targetStep,
                        'status' => DataBatch::STATUS_REVISI // 5
                    ]);

                    if ($u_step == 2) {
                        // NOTIFIKASI: Tolak dari Officer Divisi, Notif ke Owner Project & Officer Project
                        $this->sendNotificationCustom('RO_PROYEK', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
                        $this->sendNotificationCustom('RW_PROYEK', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
                    } else if ($u_step == 1) {
                        // NOTIFIKASI: Tolak dari Owner Project, Notif ke Officer Project saja
                        $this->sendNotificationCustom('RO_PROYEK', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
                    }
                }

                $statusNote = 2;
            }

            // Catat Log Note
            RiskNote::create([
                'risiko_id' => $projectRisk->id,
                'type' => 2,
                'status' => $statusNote,
                'notes' => $request->catatan_verifikasi,
                'user_id' => $user->id,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Verifikasi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function bulkVerifikasi(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status_verifikasi' => 'required|in:terima,tolak',
            'catatan_verifikasi' => 'required|string',
        ]);

        $user = auth()->user();

        DB::beginTransaction();
        try {
            foreach ($request->ids as $id) {
                $risk = ProjectRisk::findOrFail($id);
                // Panggil fungsi proses dengan logika Step 2
                $this->processProjectRiskVerification($risk, $request->status_verifikasi, $request->catatan_verifikasi, $user);
            }
            DB::commit();
            return response()->json(['message' => 'Berhasil memverifikasi ' . count($request->ids) . ' risiko proyek.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    private function processProjectRiskVerification($projectRisk, $status, $catatan, $user)
    {
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = $this->getUserVerificationStep($user->level_id, $is_mr);
        $u_step = $verificationData['u_step'];
        $min_verification = 4;

        $dataBatch = DataBatch::where('project_id', $projectRisk->project_id)
            ->where('type', 2)
            ->where('finish', false)
            ->orderBy('batch', 'desc')
            ->first();

        if ($status === 'terima') {
            if ($u_step >= $min_verification) {
                $projectRisk->update([
                    'status' => ProjectRisk::STATUS_TERVERIFIKASI,
                    'status_progress' => 3,
                    'status_risiko' => 1,
                    'step_verification' => $u_step
                ]);
            } else {
                $projectRisk->update([
                    'status' => ProjectRisk::STATUS_TUNGGU_VERIFIKASI,
                    'status_progress' => 1,
                    'step_verification' => $u_step + 1
                ]);
            }
            $noteStatus = 1;
        } else {
            // LOGIKA REJECT SAMA DENGAN VERIFIKASI SINGLE
            $targetLink = route('projects.risks.index', ['project' => $projectRisk->project_periode_list_id]);
            $msg = 'Risiko ditolak dan dikembalikan untuk revisi. Catatan: ' . $catatan;

            if ($u_step == 3) {
                // Reject dari Officer MR -> Ke Officer Divisi (Step 2)
                $targetStep = 2;
                $projectRisk->update([
                    'status' => ProjectRisk::STATUS_REJECTED_FROM_OFFICER_MR,
                    'status_progress' => 2,
                    'step_verification' => $targetStep
                ]);

                if ($dataBatch) {
                    $dataBatch->update([
                        'step_verification' => $targetStep,
                        'status' => DataBatch::STATUS_REJECTED_FROM_OFFICER_MR // 9
                    ]);
                }

                $this->sendNotificationCustom('RO_DIVISI', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
            } else if ($u_step == 4) {
                // Reject dari Owner MR -> Ke Officer MR (Step 3)
                $targetStep = 3;
                $projectRisk->update([
                    'status' => ProjectRisk::STATUS_REJECTED_FROM_OWNER_MR,
                    'status_progress' => 2,
                    'step_verification' => $targetStep
                ]);

                if ($dataBatch) {
                    $dataBatch->update([
                        'step_verification' => $targetStep,
                        'status' => DataBatch::STATUS_REJECTED_FROM_OWNER_MR // 10
                    ]);
                }

                $this->sendNotificationCustom('RO_MR', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
            } else {
                // Reject dari Divisi/Project Owner -> Ke Draft (Step 1)
                $targetStep = 1;
                $projectRisk->update([
                    'status' => ProjectRisk::STATUS_REJECTED,
                    'status_progress' => 2,
                    'step_verification' => $targetStep
                ]);

                if ($dataBatch) {
                    $dataBatch->update([
                        'step_verification' => $targetStep,
                        'status' => DataBatch::STATUS_REVISI // 5
                    ]);
                }

                if ($u_step == 2) {
                    $this->sendNotificationCustom('RO_PROYEK', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
                    $this->sendNotificationCustom('RW_PROYEK', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
                } else if ($u_step == 1) {
                    $this->sendNotificationCustom('RO_PROYEK', $projectRisk->project_periode_list_id, 'Risiko Ditolak', $msg, $targetLink, 'bx bx-x-circle');
                }
            }
            $noteStatus = 2;
        }

        RiskNote::create([
            'risiko_id' => $projectRisk->id,
            'type' => 2,
            'status' => $noteStatus,
            'notes' => $catatan,
            'user_id' => $user->id,
        ]);
    }

    public function eskalasi(Request $request)
    {
        $user = auth()->user();
        $projectPeriodeList = ProjectPeriodeList::findOrFail($request->input('project_id'));
        $project_id = $projectPeriodeList->project_id;
        $periode_id = 0;

        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = $this->getUserVerificationStep($user->level_id, $is_mr);
        $u_step = $verificationData['u_step'];

        $dataBatch = DataBatch::where('project_id', $project_id)
                ->where('type', 2)
                ->where('finish', false)
                ->orderBy('batch', 'desc')
                ->first();

        // Validasi Hak Akses (Tetap sama)
        $canEscalate = ($u_step == $dataBatch->step_verification) ||
                      ($u_step == 2 && $dataBatch->status == DataBatch::STATUS_REJECTED_FROM_OFFICER_MR);

        if (!$canEscalate) {
            return redirect()->back()->with('error', 'Anda tidak berhak melakukan eskalasi pada tahap ini.');
        }

        // --- PERBAIKAN BUG DISINI ---
        // Hitung risiko yang benar-benar BELUM diproses di meja user ini.
        // Kriterianya:
        // 1. Step verifikasi masih di user (u_step)
        // 2. TAPI statusnya BUKAN 'Terverifikasi' (4)

        $pendingRiskCount = ProjectRisk::where('project_id', $project_id)
            ->where('periode_id', $periode_id)
            ->where('status', '!=', ProjectRisk::STATUS_PUBLISHED) // 6
            ->where(function($query) use ($u_step) {
                $query->where('step_verification', $u_step)
                      // FIX: Jangan hitung risiko yang sudah statusnya 4 (Terverifikasi)
                      ->where('status', '!=', ProjectRisk::STATUS_TERVERIFIKASI);
            })
            ->count();

        if ($pendingRiskCount > 0) {
            return redirect()->back()->with('error', 'Masih ada ' . $pendingRiskCount . ' risiko yang belum diverifikasi (Terima/Tolak). Harap selesaikan verifikasi terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            $next_step = $u_step + 1;

            // JIKA FINAL STEP (Owner MR - Step 4) -> Finish Batch
            if ($u_step >= 4) {
                $dataBatch->update([
                    'status' => DataBatch::STATUS_FINISH,
                    'step_verification' => $u_step,
                    'finish' => true
                ]);

                // Publish Semua Risiko
                ProjectRisk::where('project_id', $project_id)
                    ->where('periode_id', $periode_id)
                    // ->where('status', '!=', ProjectRisk::STATUS_REJECTED)
                    ->where('status', ProjectRisk::STATUS_TERVERIFIKASI)
                    ->where('step_verification', 4)
                    ->update([
                        'status' => ProjectRisk::STATUS_PUBLISHED, // 6
                        'status_progress' => 4 // Final
                    ]);

                ProjectRisk::determineMainRisks($project_id, $periode_id);

                // NOTIFIKASI: Saat publish, beritahu Officer Project dan Owner Project
                $targetLink = route('projects.risks.index', ['project' => $request->input('project_id')]);
                $msg = 'Risiko Proyek Anda telah dipublish secara penuh oleh Owner Manajemen Risiko.';
                $this->sendNotificationCustom('RO_PROYEK', $request->input('project_id'), 'Risiko Dipublish', $msg, $targetLink, 'bx bx-check-shield');
                $this->sendNotificationCustom('RW_PROYEK', $request->input('project_id'), 'Risiko Dipublish', $msg, $targetLink, 'bx bx-check-shield');
            }
            // JIKA STEP BIASA
            else {
                // Tentukan Status Baru Batch (Normal Flow)
                $dataBatch->update([
                    'status' => DataBatch::STATUS_VERIFIKASI, // 4
                    'step_verification' => $next_step
                ]);

                // NOTIFIKASI ESKALASI
                $targetLink = route('projects.risks.index', ['project' => $request->input('project_id')]);
                $msg = 'Terdapat data risiko baru/revisi yang membutuhkan verifikasi Anda.';

                if ($next_step == 1) {
                    $this->sendNotificationCustom('RW_PROYEK', $request->input('project_id'), 'Menunggu Verifikasi', $msg, $targetLink, 'bx bx-bell');
                } elseif ($next_step == 2) {
                    $this->sendNotificationCustom('RO_DIVISI', $request->input('project_id'), 'Menunggu Verifikasi', $msg, $targetLink, 'bx bx-bell');
                } elseif ($next_step == 3) {
                    $this->sendNotificationCustom('RO_MR', $request->input('project_id'), 'Menunggu Verifikasi', $msg, $targetLink, 'bx bx-bell');
                } elseif ($next_step >= 4) {
                    $this->sendNotificationCustom('RW_MR', $request->input('project_id'), 'Menunggu Verifikasi', $msg, $targetLink, 'bx bx-bell');
                }
            }

            DB::commit();
            return redirect()->route('projects.risks.index', [
                'project' => $request->input('project_id')
            ])->with('success', 'Eskalasi berhasil. Dokumen dikirim ke tahap selanjutnya.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal eskalasi: ' . $e->getMessage());
        }
    }

    /**
     * Mendapatkan step verifikasi dan label user berdasarkan level_id
     *
     * @param int $level_id Level ID user
     * @param bool $is_mr Flag apakah user adalah Management Risk
     * @return array Array berisi u_step dan user_verification
     */
    private function getUserVerificationStep($level_id, $is_mr = false)
    {
        $u_step = 0;
        $user_verification = "";

        if($level_id == 7) { // ROWP
            $u_step = 1; // step verifikasi user
            $user_verification = "Risk Owner Project";
        }
        else if($level_id == 1) { // RO Divisi
            if($is_mr) { // RO Divisi MR
                $u_step = 3; // step verifikasi user
                $user_verification = "Risk Officer Manajemen Risiko";
            } else {
                $u_step = 2; // step verifikasi user
                $user_verification = "Risk Officer Divisi";
            }
        }
        else if($level_id == 2 && $is_mr) { // ROW MR
            $u_step = 4; // step verifikasi user
            $user_verification = "Risk Owner MR";
        }

        return [
            'u_step' => $u_step,
            'user_verification' => $user_verification
        ];
    }

    private function getPendingRiskAlert($pending_risk, $step_order, $dataBatch)
    {
        if (isset($pending_risk) && $pending_risk > 0 && $step_order == $dataBatch->step_verification) {
            return <<<HTML
            <div class="alert alert-info mb-3">
              <strong>Informasi:</strong> Terdapat {$pending_risk} risiko yang menunggu verifikasi/revisi.
            </div>
            HTML;
        }

        return '';
    }
    public function getRiskNotes(Request $request, $project, $risk)
    {
        try {
            $notes = RiskNote::with('user')
                ->where('risiko_id', $risk)
                ->where('type', 2) // Type 2 untuk Project
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($notes);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil data catatan.'], 500);
        }
    }

    private function cleanInput($value)
    {
        if (empty($value)) return $value;

        // Regex ini berarti: GANTI semua karakter YANG BUKAN (^) a-z, A-Z, 0-9, spasi, dan simbol2 standar DENGAN string kosong.
        // Simbol yang dibolehkan: . , - _ ( ) / %
        return preg_replace('/[^a-zA-Z0-9\s\.\,\-\_\(\)\/\%]/', '', $value);
    }

    /**
     * Helper untuk mengirim notifikasi berdasarkan Role pada Project
     * Target: RO_PROYEK, RW_PROYEK, RO_DIVISI, RO_MR, RW_MR
     */
    private function sendNotificationCustom($target, $projectPeriodeListId, $title, $message, $link, $icon)
    {
        $users = collect();

        // Ambil data project untuk mengetahui unit divisi yang menaungi project ini
        $projectPeriodeList = \App\Models\ProjectPeriodeList::with('project')->find($projectPeriodeListId);
        if (!$projectPeriodeList || !$projectPeriodeList->project) return;

        $project = $projectPeriodeList->project;
        $unitId = $project->unit_id; // Sesuaikan dengan kolom relasi unit di tabel projects (misal: unit_id / cost_center)

        if ($target === 'RO_PROYEK') {
            // Risk Officer Project (Level 6) YANG di-assign ke project ini
            $users = \App\Models\User::where('level_id', 6)
                ->whereHas('projects', function ($q) use ($project) {
                    $q->where('projects.id', $project->id);
                })->get();
        }
        elseif ($target === 'RW_PROYEK') {
            // Risk Owner Project (Level 7) YANG di-assign ke project ini
            $users = \App\Models\User::where('level_id', 7)
                ->whereHas('projects', function ($q) use ($project) {
                    $q->where('projects.id', $project->id);
                })->get();
        }
        elseif ($target === 'RO_DIVISI') {
            // Risk Officer Divisi (Level 1, unit terkait project)
            if ($unitId) {
                $users = \App\Models\User::where('level_id', 1)->where('unit_id', $unitId)->get();
            }
        }
        elseif ($target === 'RO_MR') {
            // Risk Officer MR: level 1, unit_mr = 1
            $users = \App\Models\User::where('level_id', 1)->whereHas('unit', function($q) {
                $q->where('unit_mr', 1);
            })->get();
        }
        elseif ($target === 'RW_MR') {
            // Risk Owner MR: level 2, unit_mr = 1
            $users = \App\Models\User::where('level_id', 2)->whereHas('unit', function($q) {
                $q->where('unit_mr', 1);
            })->get();
        }

        foreach ($users as $user) {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'title'   => $title,
                'message' => $message,
                'icon'    => $icon,
                'link'    => $link,
                'read_at' => null,
            ]);
        }
    }
}
