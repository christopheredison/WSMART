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
use App\Models\RiskMap;
use App\Models\SkalaProbabilitas;
use App\Models\PenyebabRisikoProject;
use App\Models\PerlakuanPenyebabRisiko;
use App\Models\TaksonomiRisiko;
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
            'render' => '(data, type, row) => row.peristiwa_risiko?.title || "-"',
            'class' => 'mw-10r',
        ],
        'deskripsi_peristiwa_risiko' => [
            'label' => 'Deskripsi Peristiwa Risiko',
            'data' => 'deskripsi_peristiwa_risiko',
            'render' => '(data, type, row) => data || "-"',
            'class' => 'mw-20r',
        ],
        'nilai_dampak' => [
            'label' => 'Nilai Dampak',
            'data' => 'projectRiskAnalisa.nilai_dampak',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => row.project_risk_analisa?.nilai_dampak || "-"',
            'class' => 'white-space-nowrap'
        ],
        'skala_dampak' => [
            'label' => 'Skala Dampak',
            'data' => 'projectRiskAnalisa.skala_dampak',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => row.project_risk_analisa?.skala_dampak || "-"',
        ],
        'skala_probabilitas' => [
            'label' => 'Skala Probabilitas',
            'data' => 'projectRiskAnalisa.skalaProbabilitas.tingkat',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => row.project_risk_analisa?.skala_probabilitas?.skala || "-"',
        ],
        //tambahkan untuk eksposure risiko
        'eksposur_risiko' => [
            'label' => 'Eksposur Risiko',
            'data' => 'projectRiskAnalisa.eksposur_risiko',
            'sortable' => true,
            'searchable' => false,
            'render' => '(data, type, row) => row.project_risk_analisa?.eksposur_risiko ? new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(row.project_risk_analisa.eksposur_risiko) : "-"',
            'class' => 'white-space-nowrap'
        ],
        'nilai_risiko' => [
            'label' => 'Skala Risiko',
            'data' => 'projectRiskAnalisa.skala_risiko',
            'sortable' => true,
            'searchable' => false,
            'render' => '(data, type, row) => row.project_risk_analisa?.skala_risiko || "-"',
        ],
        'level_risiko' => [
            'label' => 'Level Risiko',
            'data' => 'level_risiko',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => data || "-"',
        ],
        'status_risiko' => [
            'label' => 'Status',
            'data' => 'status',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => {
                if (data === 0 || data === 1) return "Draft";
                if (data === 2) return "On Review";
                if (data === 3) {
                    if (row.step_verification === 2) return "Accepted by Risk Owner Project";
                    if (row.step_verification === 3) return "Accepted by Risk Officer Divisi";
                    if (row.step_verification === 4) return "Accepted by Risk Officer MR";
                    return "Accepted";
                }
                if (data === 4) {
                    return "Accepted by Risk Owner MR";
                }
                if (data === 5) return "Need Revision or Rejected";
                if (data === 6) {
                    return "Published";
                }
                return "-";
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

        // Cari ApprovalFlow untuk unit ini
        // $approvalFlow = ApprovalFlow::where('project_id', $projectId)
        //     ->whereNull('unit_id')
        //     ->first();
        // $min_verification = 2;
        // if ($approvalFlow) {
        //     $min_verification = $approvalFlow->min_verification;
        // }

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

        //u step & b step
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = $this->getUserVerificationStep($levelId, $is_mr);
        $u_step = $verificationData['u_step'];
        $user_verification = $verificationData['user_verification'];

        //hitung pending risk
        $step_order = $u_step;
        $pending_risk = 0;
        $min_verification = 4;

        //dd($step_order, $levelId);
        if ($step_order >= 1) {
            // Hitung pending risk untuk step_order > 1
            $pending_risk = ProjectRisk::where('project_periode_list_id', request()->route('project'))
                ->where(function($query) use ($step_order) {
                    $query->where('step_verification', '<=', $step_order)
                        ->orWhereNull('step_verification');
                })
                ->where(function($query) {
                    $query->where('status_progress', '!=', 3) // Menggunakan nilai 3 untuk PROGRESS_ON_ACCEPTED
                        ->where('status_progress', '!=', 4); // Menggunakan nilai 4 untuk PROGRESS_ON_FINAL
                })
                ->count();
        } else if ($levelId == 6 || $levelId == null) {
            // Untuk risk owner (levelId = 1 atau null)
            if ($status == DataBatch::STATUS_REVISI) {
                // Jika status revisi, hitung risiko dengan status_progress = 2 (PROGRESS_ON_REVISION_DELETED)
                $pending_risk = ProjectRisk::where('project_periode_list_id', request()->route('project'))
                    ->where('status_progress', 2)
                    ->count();
            } else {
                $pending_risk = 0;
            }
        }
        //dd($pending_risk);
        $b_step = $dataBatch->step_verification;

        $this->baseRouteParams = ['project' => request()->route('project')];

        $projectPeriodeList = ProjectPeriodeList::with('project')->where('project_id', $projectId)->first();
        $this->indexSubtitle = $projectPeriodeList->project->project_name;

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList) ||
            ($user->unit && $projectPeriodeList->project && $projectPeriodeList->project->cost_center_parent == $user->unit->cost_center))) {
            abort(403);
        }

        // $this->callbackQuery = function($query) {
        //     $query->where('project_periode_list_id', request()->route('project'))
        //         ->with('peristiwaRisiko', 'projectRiskAnalisa.skalaProbabilitas');
        // };

        $this->callbackQuery = function($query) {
            $query->leftJoin('project_risk_analisas', 'project_risk_analisas.risiko_id', '=', 'project_risks.id')
                ->where('project_risks.project_periode_list_id', request()->route('project'))
                ->with('peristiwaRisiko', 'projectRiskAnalisa.skalaProbabilitas')
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
                    [
                        'class' => 'form-select select2',
                        'placeholder' => 'Peristiwa Risiko',
                    ]
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
                    [
                        'class' => 'form-select select2 js-select-hide-search',
                        'placeholder' => 'Level Risiko',
                    ]
                ],
            ],
        ];

        if (Gate::check('project_risk_edit')) {
            $this->tableLegend = [
                [
                    'icon' => '<span class="bx bx-show-alt"></span>',
                    'label' => 'View'
                ]
            ];

            $this->tableActions[] = [
                'label' => '<span class="bx bx-show-alt" title="View"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('projects.risks.view', ['project' => request()->route('project'), 'risk' => ':id']),
                'title' => 'View Risiko'
            ];

            //$levelId = 7;
            //dd($status, $levelId, $u_step, $b_step);

            if(($status==1 || $status==5) && $levelId==6){//on proses/revisi dan level = RO
                $active_state = null;
                if($status == 5) {
                    // Jika status batch 5, maka status risk juga harus 5
                    $active_state = 'function(id, type, row) { return row.status === 5; }';
                } else {
                    $active_state = 'function(id, type, row) { return row.status === 1; }';
                }

                $this->tableLegend = [
                    [
                        'icon' => '<span class="bx bx-analyse text-warning"></span>',
                        'label' => 'Analisa'
                    ],
                    [
                        'icon' => '<span class="bx bx-task text-primary"></span>',
                        'label' => 'Perencanaan'
                    ],
                    [
                        'icon' => '<span class="bx bx-edit"></span>',
                        'label' => 'Edit'
                    ]
                ];

                $this->tableActions[] = [
                    'label' => '<span class="bx bx-analyse text-warning"></span>',
                    'btn_icon' => true,
                    'action' => 'link',
                    'url' => route('projects.risks.analisa', ['project' => request()->route('project'), 'risk' => ':id']),
                    'title' => 'Analisa Risiko',
                    'active_state' => $active_state
                ];

                $this->tableActions[] = [
                    'label' => '<span class="bx bx-task text-primary"></span>',
                    'btn_icon' => true,
                    'action' => 'link',
                    'url' => route('projects.risks.rencana', ['project' => request()->route('project'), 'risk' => ':id']),
                    'title' => 'Rencana Perlakuan Risiko',
                    'active_state' => $active_state
                ];

                $this->tableActions[] = [
                    'label' => '<span class="bx bx-edit"></span>',
                    'btn_icon' => true,
                    'action' => 'edit',
                    'permissions' => ['project_risk_edit'],
                    'active_state' => $active_state
                ];


                if (Gate::check('project_risk_delete') && ($status==1 || $status==5)) {
                    $this->tableLegend[] = [
                        'icon' => '<span class="bx bx-trash text-danger"></span>',
                        'label' => 'Hapus'
                    ];

                    $this->tableActions[] = [
                        'label' => '<span class="bx bx-trash text-danger"></span>',
                        'btn_icon' => true,
                        'action' => 'delete',
                        'url' => route('projects.risks.destroy', ['project' => request()->route('project'), 'risk' => ':id']),
                        'title' => 'Hapus',
                        'permissions' => ['project_risk_delete'],
                        'active_state' => 'function(id, type, row) { return (row.status == 1 || row.status == 5); }'
                    ];
                }
            }
            //else if($status==2 && $levelId==7){//on verif && level = ROW/P
            else if($u_step == $b_step){//status batch dan step
                $active_state = null;
                //$active_state = 'function(id, type, row) { return row.status == 2 || row.status == 3; }';
                $active_state = 'function(id, type, row) { return (row.status == 2 || row.status == 3) && row.step_verification == ' . $u_step . '; }';
                $this->tableActions[] = [
                    'label' => '<span class="bx bx-check-shield text-success"></span>',
                    'btn_icon' => true,
                    'action' => 'verifikasi',
                    'title' => 'Verifikasi Risiko',
                    'active_state' => $active_state,
                    //'permissions' => ['project_risk_edit'],
                ];

                $this->tableLegend[] = [
                    'icon' => '<span class="bx bx-check-shield text-success"></span>',
                    'label' => 'Verifikasi Risiko'
                ];
            }
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

        $catatanRoute = route('projects.risks.notes', ['project' => request()->route('project'), 'risk' => ':id']);

        $this->extraScripts[] = <<<SCRIPT
        <script>
        function showCatatanRisiko(riskId) {
            const modalElement = document.getElementById('modalCatatan');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            const contentDiv = $('#catatan-content');

            // Tampilkan spinner loading
            contentDiv.html('<div class="d-flex justify-content-center my-4"><div class="spinner-border" role="status"><span class="visually-hidden">Memuat...</span></div></div>');

            // Gunakan route yang sudah di-generate dari PHP
            const url = "{$catatanRoute}".replace(':id', riskId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(notes) {
                    if (notes.length === 0) {
                        contentDiv.html('<div class="text-center my-4"><i class="fas fa-comment-slash fa-2x text-muted mb-2"></i><p>Belum ada catatan untuk risiko ini.</p></div>');
                    } else {
                        let html = '';
                        notes.forEach(note => {
                            const statusBadge = note.status == 1
                                ? '<span class="badge bg-success-subtle text-success">Diterima</span>'
                                : '<span class="badge bg-danger-subtle text-danger">Ditolak</span>';

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
                                        <small class="text-muted me-3">\${formattedDate}</small>
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
        </script>
        SCRIPT;

        $this->tableLegend[] = [
            'icon' => '<span class="badge bg-primary">!</span>',
            'label' => 'Rekomendasi Risiko'
        ];

        $average = (float) ProjectRisk::where('project_periode_list_id', request()->route('project'))->avg('skala_risiko');
        // Menghitung rata-rata eksposur risiko dari data dengan kategori dampak Kuantitatif
        $projectRiskIds = ProjectRisk::where('project_periode_list_id', request()->route('project'))
            ->pluck('id');

        $averageExposure = (float) ProjectRiskAnalisa::whereIn('risiko_id', $projectRiskIds)
            ->where('kategori_dampak', ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF)
            ->avg('eksposur_risiko');

        //dd($averageExposure);

        $this->tableColumns['peristiwa_risiko']['render'] = <<< JS
            (data, type, row) => {
                let add = '';
                // if (row.skala_risiko >= $average) {
                //     add = '<span class="badge bg-primary">!</span> ';
                // }
                if (row.project_risk_analisa?.kategori_dampak === 'Kuantitatif' &&
                    row.project_risk_analisa?.eksposur_risiko >= $averageExposure) {
                    add = '<span class="badge bg-primary">!</span> ';
                } else if (row.project_risk_analisa?.kategori_dampak === 'Kualitatif' &&
                    row.skala_risiko >= 20) {
                    add = '<span class="badge bg-primary">!</span> ';
                }
                return add + (row.peristiwa_risiko?.title || '-');
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

        $routeUrl = route('projects.risks.send');
        //$routeUrl = ($levelId == 7 && $status == 2) ? route('projects.risks.eskalasi') : route('projects.risks.send');

        $csrfToken = csrf_token();
        $disabledAttr = '';
        $viewAttr = "none";

        //if (!((($status == 1 || $status == 5) && $levelId == 6) || ($status == 2 && $levelId == 7))) {
        //jika risk officer project
        if (!(($status == 1 || $status == 5) && $levelId == 6)){
            $disabledAttr = ' disabled';
            $viewAttr = " style='display:none'";
        }

        //jika risk owner project, risk off div, risk ow div
        //dd($status, $u_step, $b_step);
        if(($status == 2 || $status == 3 || $status == 4) && $u_step == $b_step){
            $disabledAttr = '';
            $viewAttr = "";
            $routeUrl = route('projects.risks.eskalasi');
        }

        $buttonText = ($status == 5) ? 'Kirim Perbaikan' : 'Kirim Risiko';
        //jika sudah di level row mr
        if($status == 4 && $u_step >= $min_verification){
            $buttonText = "Publish Risiko";
        }

        $sendType = ($status == 5) ? 'perbaikan' : 'risiko';
        // Format nilai eksposur risiko dengan format Rupiah dan pemisah ribuan
        $formattedAverageExposure = 'Rp ' . number_format($averageExposure, 0, ',', '.');

        $this->cardFooter = <<<HTML
            <div class="d-flex flex-column">
                <div {$viewAttr}>
                  {$this->getPendingRiskAlert($pending_risk, $step_order, $dataBatch)}
                </div>
                <div>
                    <strong>Rata-rata Eksposure Risiko (Kuantitatif):</strong>
                    <span id="average-risk-value">{$formattedAverageExposure}</span>
                </div>
                <div class="mt-3"{$viewAttr}>
                    <form id="send-form" action="{$routeUrl}" method="POST" class="d-inline-block">
                        <input type="hidden" name="_token" value="{$csrfToken}">
                        <input type="hidden" name="project_id" value="{$projectPeriodeListId}">
                        <input type="hidden" name="send_type" value="{$sendType}">
                        <button id="send-button" type="button" class="btn btn-submit btn-arrow-right"{$disabledAttr}>{$buttonText}</button>
                    </form>
                </div>
            </div>
            <script>
                document.getElementById('send-button').addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: 'Apakah Anda yakin ingin mengirim {$sendType} ini?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Kirim',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('send-form').submit();
                        }
                    });
                });
            </script>
            HTML;

        $this->defaultOrder = [[7, 'desc']];

        // hide import tender
        // $this->importConfig = [
        //   'buttonText' => 'Upload Risiko Tender',
        //   'route' => route('projects.risks.import-tender', ['project' => $projectPeriodeList->id]),
        //   'title' => 'Upload Risiko Tender dari Excel',
        //   'instructions' => 'Pastikan file Excel Anda memiliki template yang sesuai.',
        //   'templateUrl' => route('download-tender-template'),
        // ];

        $this->extraViewData = [
            'status' => $status,
            'levelId' => $levelId,
            'pending_risk' => $pending_risk,
            // tambahkan data lain jika diperlukan
        ];

        return parent::index();
    }

    public function create() {
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

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

        return view('project-risk.create', compact('periode', 'project', 'peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings', 'projectPeriodeList', 'jenisRisikos', 'sasaranProyeks', 'taksonomiRisikos'));
    }

    public function store(Request $request)
    {
        //dd($request->kontrol_eksisting_id);
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

        $project = $projectPeriodeList->project;

        if ($request->action === 'save' || $request->action === 'savenext') {
            $request->validate([
                'peristiwa_risiko_id' => 'required',
                'kategori_risiko_id' => 'required',
                'jenis_risiko_id' => 'required',
                'deskripsi_peristiwa_risiko' => 'required',
                'deskripsi_dampak' => 'required',
                'wbs' => 'required',
                //'target_capaian_kinerja' => 'required',
                'jenis_kontrol_eksisting_id' => 'required',
                // 'penilaian_efektifitas_kontrol' => 'required',
                'perkiraan_waktu_mulai_terpapar_risiko' => 'required',
                'perkiraan_waktu_selesai_terpapar_risiko' => 'required',
                'penyebab_risiko' => 'required|array|min:1',
                'penyebab_risiko.*' => 'required|string',
            ]);
            //dd($request);
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

            $toStore = [
                'unit_type_id' => $user->unit_type_id,
                'unit_id' => $user->unit_id,
                'periode_id' => 0,
                'user_id' => $user->id,
                'project_id' => $project->id,
                'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
                'target_capaian_kinerja' => $targetCapaianKinerja,
                'sasaran_proyek_id' => $sasaranProyekId,
                'project_periode_list_id' => $projectPeriodeList->id,
                'deskripsi_peristiwa_risiko' => $request->deskripsi_peristiwa_risiko,
                'deskripsi_dampak' => $request->deskripsi_dampak,
                'jenis_kontrol_eksisting_id' => $request->jenis_kontrol_eksisting_id,
                'penilaian_efektifitas_kontrol' => 0,
                'perkiraan_waktu_terpapar_risiko_mulai' => $perkiraanWaktuTerpaparRisikoMulai,
                'perkiraan_waktu_terpapar_risiko_akhir' => $perkiraanWaktuTerpaparRisikoAkhir,
                'kategori_risiko_id' => $request->kategori_risiko_id,
                'jenis_risiko_id' => $request->jenis_risiko_id,
                'kontrol_eksisting' => '',
                'wbs' => $request->wbs,
                'status_risiko' => '0',
                'status_progress'  => '0',
                'taksonomi_risiko_id' => $request->taksonomi_risiko_id,
                'threshold_risk_limit' => $this->cleanRupiah($request->threshold_risk_limit),
                'threshold_risk_appetite' => $this->cleanRupiah($request->threshold_risk_appetite),
                'threshold_risk_tolerance' => $this->cleanRupiah($request->threshold_risk_tolerance),
                'status' => 1
            ];

            $projectRisk = ProjectRisk::create($toStore);

            if ($request->has('param_nama')) {
                foreach ($request->param_nama as $idx => $nama) {
                    if(!empty($nama)) {
                        $projectRisk->parameterRisikoProjects()->create([
                            'nama' => $nama,
                            'formula' => $request->param_formula[$idx] ?? '',
                            'satuan' => $request->param_satuan[$idx] ?? '',
                        ]);
                    }
                }
            }

            $projectRisk->projectRiskAnalisas()->create([]);
            $projectRisk->projectRiskRencanaPerlakuans()->create([]);

            foreach ($request->penyebab_risiko as $penyebabRisiko) {
                $projectRisk->penyebabRisikoProjects()->create([
                    'penyebab_risiko' => $penyebabRisiko,
                ]);
            }

            foreach ($request->key_risk_indicator as $idx => $kri) {
                $kriData = [
                    'kri' => $kri,
                    'satuan_kri' => $request->satuan_kri[$idx] ?? '',
                    'batas_aman' => $request->batas_aman[$idx] ?? '',
                    'batas_waspada' => $request->batas_waspada[$idx] ?? '',
                    'batas_bahaya' => $request->batas_bahaya[$idx] ?? '',
                ];

                $projectRisk->kriProjects()->create($kriData);
            }

            foreach ($request->kontrol_eksisting as $kontrolEksisting) {
                $projectRisk->projectKontrolEksistings()->create([
                    'kontrol_eksisting_desc' => $kontrolEksisting,
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
        $projectRisk = ProjectRisk::with('penyebabRisikoProjects', 'kriProjects', 'peristiwaRisiko', 'parameterRisikoProjects')->findOrFail(request()->route('risk'));

        //dd($projectRisk);
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

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

        return view('project-risk.edit', compact('projectRisk', 'project', 'peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings', 'projectPeriodeList', 'jenisRisikos', 'sasaranProyeks', 'taksonomiRisikos'));
    }

    public function update(Request $request, $resource)
    {
        $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

        $project = $projectPeriodeList->project;
        $periode = $projectPeriodeList->periode;

        if ($request->action === 'save' || $request->action === 'savenext') {
            $request->validate([
                'peristiwa_risiko_id' => 'required',
                'kategori_risiko_id' => 'required',
                'jenis_risiko_id' => 'required',
                'deskripsi_peristiwa_risiko' => 'required',
                'deskripsi_dampak' => 'required',
                'wbs' => 'required',
                'jenis_kontrol_eksisting_id' => 'required',
                // 'penilaian_efektifitas_kontrol' => 'required',
                'perkiraan_waktu_terpapar_risiko_mulai' => 'required',
                'perkiraan_waktu_terpapar_risiko_akhir' => 'required',
                'penyebab_risiko' => 'required|array|min:1',
                'penyebab_risiko.*' => 'required|string',
                // Tambahkan validasi lain jika perlu
            ]);

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
                $targetCapaianKinerja = $request->kpi_desc_selected;
            } else {
                $targetCapaianKinerja = $request->target_capaian_kinerja;
            }

            $toUpdate = [
                'unit_type_id' => $user->unit_type_id,
                'unit_id' => $user->unit_id,
                'periode_id' => 0,
                'user_id' => $user->id,
                'project_id' => $project->id,
                'kategori_risiko_id' => $request->kategori_risiko_id,
                'jenis_risiko_id' => $request->jenis_risiko_id,
                'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
                'project_periode_list_id' => $projectPeriodeList->id,
                'deskripsi_peristiwa_risiko' => $request->deskripsi_peristiwa_risiko,
                'deskripsi_dampak' => $request->deskripsi_dampak,
                'jenis_kontrol_eksisting_id' => $request->jenis_kontrol_eksisting_id,
                'penilaian_efektifitas_kontrol' => 0,
                'perkiraan_waktu_terpapar_risiko_mulai' => $perkiraanWaktuTerpaparRisikoMulai,
                'perkiraan_waktu_terpapar_risiko_akhir' => $perkiraanWaktuTerpaparRisikoAkhir,
                'wbs' => $request->wbs,
                'target_capaian_kinerja' => $targetCapaianKinerja,
                'sasaran_proyek_id' => $sasaranProyekId,
                'taksonomi_risiko_id' => $request->taksonomi_risiko_id,
                'threshold_risk_limit' => $this->cleanRupiah($request->threshold_risk_limit),
                'threshold_risk_appetite' => $this->cleanRupiah($request->threshold_risk_appetite),
                'threshold_risk_tolerance' => $this->cleanRupiah($request->threshold_risk_tolerance),
            ];

            $projectRisk->update($toUpdate);

            $savedParamIds = [];
            if ($request->has('param_nama')) {
                foreach ($request->param_nama as $key => $nama) {
                    $dataParam = [
                        'nama' => $nama,
                        'formula' => $request->param_formula[$key] ?? '',
                        'satuan' => $request->param_satuan[$key] ?? '',
                    ];

                    $paramId = $request->parameter_risiko_id[$key] ?? null;
                    $exist = $projectRisk->parameterRisikoProjects()->find($paramId);

                    if ($exist) {
                        $exist->update($dataParam);
                        $savedParamIds[] = $exist->id;
                    } else {
                        $newParam = $projectRisk->parameterRisikoProjects()->create($dataParam);
                        $savedParamIds[] = $newParam->id;
                    }
                }
            }
            $projectRisk->parameterRisikoProjects()->whereNotIn('id', $savedParamIds)->delete();

            $penyebabRisikoIds = [];
            foreach ($request->penyebab_risiko as $penyebabRisikoId => $penyebabRisiko) {
                $exist = $projectRisk->penyebabRisikoProjects()->where('id', $penyebabRisikoId)->first();
                if ($exist) {
                    $exist->update([
                        'penyebab_risiko' => $penyebabRisiko,
                    ]);
                } else {
                    $exist = $projectRisk->penyebabRisikoProjects()->create([
                        'penyebab_risiko' => $penyebabRisiko,
                    ]);
                }
                $penyebabRisikoIds[] = $exist->id;
            }
            $projectRisk->penyebabRisikoProjects()->whereNotIn('id', $penyebabRisikoIds)->delete();

            $savedKriIds = [];
            foreach ($request->key_risk_indicator as $key => $kri) {
                $kriData = [
                    'kri' => $kri,
                    'satuan_kri' => $request->satuan_kri[$key] ?? '',
                    'batas_aman' => $request->batas_aman[$key] ?? '',
                    'batas_waspada' => $request->batas_waspada[$key] ?? '',
                    'batas_bahaya' => $request->batas_bahaya[$key] ?? '',
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
                    $existKontrol->update(['kontrol_eksisting_desc' => $kontrolEksistingDesc]);
                    $savedKontrolIds[] = $existKontrol->id;
                } else {
                    $newKontrol = $projectRisk->projectKontrolEksistings()->create([
                        'kontrol_eksisting_desc' => $kontrolEksistingDesc
                    ]);
                    $savedKontrolIds[] = $newKontrol->id;
                }
            }
            $projectRisk->projectKontrolEksistings()->whereNotIn('id', $savedKontrolIds)->delete();


            if ($request->action === 'savenext') {
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
                'peristiwaRisiko',
                'taksonomiRisiko',
                'penyebabRisikoProjects',
                'projectRiskAnalisa.skalaDampakObj',
                'projectRiskAnalisa.skalaProbabilitas',
                'projectRiskAnalisa.skalaDampakResidualObj',
                'projectRiskAnalisa.skalaProbabilitasResidual',
                'projectRiskMonitorings' => function($query) {
                    $query->orderBy('id', 'desc')->with('skalaProbabilitas');
                },
            ])
            ->where('project_periode_list_id', request()->route('project'))
            ->findOrFail(request()->route('risk'));

        $projectRisk->append('currentRiskMapsMonth');

        $tahunMonitorings = $projectRisk->projectRiskMonitorings->pluck('tahun')->unique()->toArray();
        $tahunMonitorings[] = $projectRisk->created_at->year;
        sort($tahunMonitorings);
        $minTahun = !empty($tahunMonitorings) ? min($tahunMonitorings) : date('Y');
        $maxTahun = !empty($tahunMonitorings) ? max($tahunMonitorings) : date('Y');
        $tahunMonitorings = range($minTahun, $maxTahun);

        $currentRiskMaps = $projectRisk->currentRiskMapsMonth;
        $formattedCurrentRiskMaps = [];
        $currentValue = $projectRisk->currentRiskMaps['inherent'] ?? null;

        if ($currentValue) {
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

        if($projectRisk->projectRiskAnalisa->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF){
            $risk_limit = ($project->meta['omset'] ?? 0) * 0.03;
        } else{
            $risk_limit = 1/100*$risk_tolerance;
        }

        // dd($risk_limit, $risk_tolerance);
        return view('project-risk.view', compact(
            'projectRisk',
            'tahunMonitorings',
            'formattedCurrentRiskMaps',
            'riskMaps',
            'risk_limit',
            'risk_tolerance',
        ));
    }

    public function destroy($resource) {
        try {
            $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));

            if (!Gate::check('project_risk_delete')) {
                return response()->json([
                    'message' => 'Anda tidak memiliki izin untuk menghapus data ini'
                ], 403);
            }

            // $projectRisk->projectRiskAnalisas()->delete();
            // $projectRisk->penyebabRisikoProjects()->delete();
            // $projectRisk->kriProjects()->delete();
            // $projectRisk->projectRiskRencanaPerlakuans()->delete();
            // $projectRisk->projectRiskMonitorings()->delete();
            // $projectRisk->projectKontrolEksistings()->delete();
            $projectRisk->delete();

            return response()->json([
                'message' => 'Data risiko proyek berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data.'
            ], 500);
        }
    }

    public function analisa(Request $request, $resource) {
        $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

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
        $risk_limit = ($projectPeriodeList->project->meta['omset'] ?? 0) * 0.03;
        return view('project-risk.analisa', compact('projectRisk', 'project', 'periode', 'projectPeriodeList', 'skalaProbabilitas', 'riskMaps', 'analisa', 'areas', 'groupedAreas', 'risk_tolerance', 'risk_limit', 'parameterTypes', 'groupedSkalaParameters', 'selectedParameterType'));
    }

    public function doAnalisa(Request $request) {
        $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

        $analisa = $projectRisk->projectRiskAnalisa;
        if (!$analisa) {
            $analisa = $projectRisk->projectRiskAnalisa()->create([]);
        }

        $request->validate([
            //'area_dampak' => 'required',
            'kategori_dampak' => 'required|in:' . ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF . ',' . ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF,
            //'deskripsi_dampak' => 'required',
            // 'skala_dampak_hidden' => 'required|numeric',
            'nilai_probabilitas' => 'required|numeric',
            // 'skala_dampak_residual_hidden' => 'required|numeric|lte:skala_dampak_hidden',
            'skala_parameter_id' => 'required|exists:skala_parameters,id',
            'skala_parameter_residual_id' => 'required|exists:skala_parameters,id',
            'nilai_probabilitas_residual' => 'required|numeric|lte:nilai_probabilitas',
        ]);

        //dd($request);

        $xrisk_limit = $this->cleanRupiah($request->_risk_limit);
        $nilai_dampak = $this->cleanRupiah($request->nilai_dampak);
        $nilai_dampak_residual = $this->cleanRupiah($request->nilai_dampak_residual);

        $project = $projectPeriodeList->project;
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        //

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

        $analisa->update($toUpdate);

        $toUpdate = [
            'nilai_probabilitas' => $request->nilai_probabilitas,
            'nilai_probabilitas_residual' => $request->nilai_probabilitas_residual,
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

            //$risk_limit = $projectPeriodeList->risk_limit;
            $risk_limit = ($projectPeriodeList->project->meta['omset'] ?? 0) * 0.03;

            $skala_dampak = $this->hitungSkalaDampak($nilai_dampak, $risk_limit);
            $skala_dampak_residual = $this->hitungSkalaDampak($nilai_dampak_residual, $risk_limit);

            $toUpdate['skala_dampak'] = $skala_dampak;
            $toUpdate['skala_dampak_residual'] = $skala_dampak_residual;
            $toUpdate['nilai_dampak'] = $nilai_dampak;
            $toUpdate['nilai_dampak_residual'] = $nilai_dampak_residual;
            $toUpdate['risk_limit'] = $risk_limit;
        }
        else{
            $toUpdate['skala_dampak'] = $request->skala_dampak_hidden;
            $toUpdate['skala_dampak_residual'] = $request->skala_dampak_residual_hidden;
            $toUpdate['nilai_dampak'] = 0;
            $toUpdate['nilai_dampak_residual'] = 0;
            $toUpdate['risk_limit'] = 1/100*$risk_tolerance;
        }


        //dd($toUpdate);
        $skalaInherent = SkalaParameter::find($request->skala_parameter_id);
        $skalaResidual = SkalaParameter::find($request->skala_parameter_residual_id);

        // Validasi tingkat residual tidak boleh > inheren
        if ($skalaResidual->tingkat > $skalaInherent->tingkat) {
            return response()->json([
                'message' => 'Tingkat skala probabilitas residual tidak boleh lebih tinggi dari inheren.',
            ], 422);
        }

        $tingkatSkalaProbabilitas = SkalaProbabilitas::getSkalaByValue($request->nilai_probabilitas);
        $tingkatSkalaProbabilitasResidual = SkalaProbabilitas::getSkalaByValue($request->nilai_probabilitas_residual);

        if (!$tingkatSkalaProbabilitas || !$tingkatSkalaProbabilitasResidual) {
            return response()->json([
                'message' => 'Tidak ada data skala probabilitas yang sesuai',
            ], 422);
        }

        $toUpdate['skala_probabilitas_id'] = $tingkatSkalaProbabilitas->id;
        $toUpdate['skala_probabilitas_residual_id'] = $tingkatSkalaProbabilitasResidual->id;

        //$riskMap = $riskMaps[$toUpdate['skala_dampak'] . '-' . $tingkatSkalaProbabilitas->tingkat] ?? null;
        $riskMap = $riskMaps[$toUpdate['skala_dampak'] . '-' . $tingkatSkalaProbabilitas->tingkat] ?? null;
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
            //$toUpdate['nilai_dampak'] = 0;
            //$toUpdate['nilai_dampak_residual'] = 0;

            $toUpdate['eksposur_risiko'] = ($toUpdate['skala_dampak'] * (1/100)) * $toUpdate['nilai_probabilitas'] / 100 * $toUpdate['risk_limit'];
            $toUpdate['eksposur_risiko_residual'] = ($toUpdate['skala_dampak_residual'] * (1/100)) * $toUpdate['nilai_probabilitas_residual'] / 100 * $toUpdate['risk_limit'];
        } elseif ($request->kategori_dampak === ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF) {
            //$toUpdate['nilai_dampak'] = $request->nilai_dampak;
            //$toUpdate['nilai_dampak_residual'] = $request->nilai_dampak_residual;

            $toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * $toUpdate['nilai_probabilitas'] / 100;
            $toUpdate['eksposur_risiko_residual'] = $toUpdate['nilai_dampak_residual'] * $toUpdate['nilai_probabilitas_residual'] / 100;
        }

        $analisa->update($toUpdate);

        $projectRisk->update([
            'skala_risiko' => $toUpdate['skala_risiko'],
            'level_risiko' => $toUpdate['level_risiko'],
        ]);

        $projectPeriodeList->recalculateAnalisa($risk_limit);
        $projectPeriodeList->refreshNilai();
        //dd($toUpdate);

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

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

        $project = $projectPeriodeList->project;
        $periode = $projectPeriodeList->periode;
        $analisa = $projectRisk->projectRiskAnalisa;

        return view('project-risk.rencana', compact('projectRisk', 'project', 'periode', 'projectPeriodeList', 'analisa'));
    }

    public function doRencana(Request $request) {
        $projectRisk = ProjectRisk::findOrFail(request()->route('risk'));
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

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
        $perlakuan = PerlakuanDampakRisiko::with('projectRisk')->findOrFail($id);

        return response()->json([
            'id'                => $perlakuan->id,
            'risiko_id'         => $perlakuan->risiko_id,
            'deskripsi_dampak'  => $perlakuan->projectRisk->deskripsi_dampak,
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
            'xd_output_perlakuan_risiko' => 'required',
            'xd_output_perlakuan_risiko'  => 'required',
            'xd_opsi_perlakuan_risiko'    => 'required',
            'xd_biaya_perlakuan_risiko'   => 'required|numeric',
            'xd_pic'                      => 'required',
            'xd_divisi_terkait'           => 'nullable|array',
            'xd_timeline_mulai_perlakuan_risiko'   => 'required',
            'xd_timeline_selesai_perlakuan_risiko' => 'required',
        ]);

        $perlakuan = PerlakuanDampakRisiko::findOrFail($id);
        $jabatan = Jabatan::find($request->xpic);

        $perlakuan->update([
            'rencana_perlakuan_risiko' => $validated['xd_output_perlakuan_risiko'],
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
            $perlakuan = \App\Models\PerlakuanDampakRisiko::findOrFail($id);

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
        $project_periode_id = $request->input('project_id');
        $project_id = null;
        $projectPeriodeList = ProjectPeriodeList::findOrFail($request->input('project_id'));
        if ($projectPeriodeList) {
          $project_id = $projectPeriodeList->project_id;
        }
        // dd($project_id);
        $send_type = $request->input('send_type', 'risiko'); // Default ke 'risiko' jika tidak ada
        $project = Project::find($project_id);
        $level_id = $user->level_id;
        $periode_id = 0;

        $appFlow = $this->getFlowData($project_id, $level_id);
        $step_order = $appFlow['step_order'];
        $min_verification = $appFlow['min_verification'];
        $approval_step_id = $appFlow['approval_step_id'];

        $risikos = ProjectRisk::where('project_id', $project_id)
                ->where('periode_id', $periode_id)
                ->where('status', '!=', 6)
                ->get();

        if ($risikos->isEmpty()) {
          return redirect()->route('projects.risks.index', [
                        'project' => $project_periode_id
                    ])->with('error', 'Tidak ada risiko yang dapat dikirim.');
        }
        $belumLengkap = false;
        $idRisikoBelumLengkap = [];

        foreach ($risikos as $risiko) {
            //cek kelengkapan risiko
            // Cek apakah risiko memiliki analisis risiko
            if (!$risiko->projectRiskAnalisa) {
                $belumLengkap = true;
                $idRisikoBelumLengkap[] = $risiko->id;
                continue;
            }

            // Cek apakah semua penyebab risiko memiliki perlakuan
            $penyebabRisikos = $risiko->penyebabRisikoProjects;
            if ($penyebabRisikos->isEmpty()) {
                $belumLengkap = true;
                $idRisikoBelumLengkap[] = $risiko->id;
                continue;
            }

            // Cek apakah setiap penyebab risiko memiliki perlakuan
            foreach ($penyebabRisikos as $penyebabRisiko) {
                if ($penyebabRisiko->perlakuanPenyebabRisiko->isEmpty()) {
                    $belumLengkap = true;
                    $idRisikoBelumLengkap[] = $risiko->id;
                    break;
                }
            }
        }
        //dd($risikos);
        if ($belumLengkap) {
            // Kumpulkan deskripsi peristiwa risiko yang belum lengkap
            $risikoTidakLengkap = [];
            foreach ($idRisikoBelumLengkap as $id) {
                $risiko = $risikos->where('id', $id)->first();
                if ($risiko) {
                    // Ambil deskripsi peristiwa risiko
                    $deskripsi = $risiko->deskripsi_peristiwa_risiko ?:
                                 ($risiko->peristiwaRisiko ? $risiko->peristiwaRisiko->title : 'Risiko #' . $risiko->id);
                    $risikoTidakLengkap[] = $deskripsi;
                }
            }

            // Buat pesan error dengan daftar risiko yang belum lengkap
            $pesanError = 'Terdapat risiko yang belum dianalisa atau belum memiliki rencana perlakuan: <ul>';
            foreach ($risikoTidakLengkap as $deskripsi) {
                $pesanError .= '<li>' . $deskripsi . '</li>';
            }
            $pesanError .= '</ul>Silahkan lengkapi terlebih dahulu.';

            return redirect()->route('projects.risks.index', [
                'project' => $project_periode_id
            ])
                ->with('error', $pesanError);
        }

        $dataBatch = DataBatch::where('project_id', $project_id)
                ->where('periode_id', $periode_id)
                ->where('type', 2)
                ->orderBy('batch', 'desc')
                ->first();
        //dd($send_type);
        if ($send_type == 'perbaikan' && $dataBatch) {
            if ($dataBatch->step_verification == 1) {
                $dataBatch->update([
                    'status' => DataBatch::STATUS_KIRIM,
                ]);
            } else if ($dataBatch->step_verification > 1) {
                $dataBatch->update([
                    'status' => DataBatch::STATUS_VERIFIKASI,
                ]);
            }
            $dataBatch->refresh();

            //update semua project risk yang status=5 menjadi 2
            ProjectRisk::where('project_id', $project_id)
                ->where('periode_id', $periode_id)
                ->where('status', ProjectRisk::STATUS_REJECTED) // STATUS_REJECTED = 5
                ->update([
                    'status' => $dataBatch->step_verification == 1 ?
                        ProjectRisk::STATUS_DIKIRIM : // STATUS_DIKIRIM = 2
                        ProjectRisk::STATUS_TUNGGU_VERIFIKASI, // STATUS_TUNGGU_VERIFIKASI = 3
                        'status_progress' => 1,
                ]);

        } else if (!$dataBatch) {
            // Jika belum ada, buat batch baru dengan nilai batch = 1
            $batch = 1;

            // Buat data batch baru
            $newDataBatch = DataBatch::create([
                'periode_id' => $periode_id,
                'type' => 2, // type = 1 untuk unit/divisi
                'project_id' => $project_id,
                'batch' => $batch,
                'status' => DataBatch::STATUS_KIRIM, // Status kirim
                'step_verification' => 1,
                'finish' => false
            ]);
        }
        else if (!$dataBatch->finish) {
            if($dataBatch->step_verification==null || $dataBatch->step_verification < 1){
                // Jika sudah ada dan status belum finish
                if($dataBatch->status != DataBatch::STATUS_PROSES){
                    return redirect()->route('projects.risks.index', [
                        'project' => $project_periode_id
                    ])->with('error', 'Masih ada data batch risiko yang sedang berproses. Silahkan tunggu hingga proses selesai.');
                }
                else{
                    //update dataBatch
                    $dataBatch->update([
                        'status' => DataBatch::STATUS_KIRIM,
                        'step_verification' => 1,
                        'finish' => false
                    ]);
                    $dataBatch->refresh();
                }
            }
        }
        // dd($dataBatch);

        if($dataBatch && $dataBatch->status <= DataBatch::STATUS_KIRIM){
            // Ubah semua risiko di identifikasi_risikos dengan status = 2 (Dikirim), status_risiko = 1, dan status_progress = 1
            foreach ($risikos as $risiko) {
                if ($risiko->status == ProjectRisk::STATUS_INPUT_DATA) {
                    $risiko->update([
                        'status' => ProjectRisk::STATUS_DIKIRIM, // Status dikirim
                        'status_risiko' => 1,
                        'status_progress' => 1,
                        'step_verification' => 1
                    ]);
                }
            }
        }

        return redirect()->route('projects.risks.index', [
                    'project' => $project_periode_id
                ])
                    ->with('success', 'Pengiriman risiko berhasil dilakukan. Risiko telah dikirim untuk diverifikasi.');
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
        // $project_id = $request->input('project_id');
        // $risiko = ProjectRisk::find($id);
        // if (!$risiko) {
        //     return redirect()->route('projects.risks.index', [
        //         'project' => $project_id
        //     ])->with('error', 'Risiko tidak ditemukan.');
        // }
        $projectRisk = ProjectRisk::findOrFail($id);
        $project_id = $projectRisk->project_id;
        $periode_id = $projectRisk->periode_id;
        $projectPeriodeList = ProjectPeriodeList::where('project_id', $project_id)->first();

        $user = auth()->user();
        $level_id = $user->level_id;

        // $appFlow = $this->getFlowData($project_id, $level_id);
        // $step_order = $appFlow['step_order'];
        // $min_verification = $appFlow['min_verification'];
        // $approval_step_id = $appFlow['approval_step_id'];

        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        //cek databatch terkait dimana untuk step verification dan user flow step verification harus sama
        $verificationData = $this->getUserVerificationStep($level_id, $is_mr);
        $u_step = $verificationData['u_step'];
        $user_verification = $verificationData['user_verification'];
        $min_verification = 4;
        $step_order = $u_step;
        // Validasi input
        $request->validate([
            'status_verifikasi' => 'required|in:terima,tolak',
            'catatan_verifikasi' => 'required|string',
        ]);

        // if ($step_order == 0) {
        //     return redirect()->route('projects.risks.index', ['project' => $project_id])
        //         ->with('error', 'Anda tidak memiliki hak untuk melakukan verifikasi risiko');
        // }

        //$step_order = 1;

        //dd("step_order", $step_order);

        // Ambil data batch
        $dataBatch = DataBatch::where('project_id', $project_id)
            ->where('periode_id', $periode_id ?? 0)
            ->where('type', 2)
            ->where('finish', false)
            ->first();

        if (!$dataBatch) {
            return redirect()->back()->with('error', 'Data batch tidak ditemukan.');
        }
        $b_step = $dataBatch->step_verification;

        //cek untuk step user dengan step branch harus sama
        if ($u_step != $b_step) {
            return redirect()->route('projects.risks.index', [
                'project' => $projectPeriodeList->id
            ])->with('error', 'Anda tidak berhak melakukan verifikasi pada tahap ini. Saat ini tahap verifikasi hanya dilakukan oleh '.$user_verification);
        }

        // catat log disini
        Log::channel('verification')->info('Verifikasi risiko dengan ID: ' . $projectRisk->id . ' oleh user dengan ID: ' . auth()->id());
        Log::channel('verification')->info('Step Order: ' . $step_order);
        Log::channel('verification')->info('Min Verification: ' . $min_verification);

        // Proses verifikasi berdasarkan status
        if ($request->status_verifikasi === 'terima') {
            //catat log disini
            Log::channel('verification')->info('Verifikasi risiko dengan ID: ' . $projectRisk->id . ' diterima oleh user dengan ID: ' . auth()->id());

            //pengecekan jika step_order yang dimiliki level_id adalah sama dengan min_verification
            if($step_order >= $min_verification){
                // Jika diterima, update status menjadi terverifikasi
                $projectRisk->update([
                    'status' => ProjectRisk::STATUS_TERVERIFIKASI,
                    'status_progress' => 3,
                    'status_risiko' => 1, //valid
                    'step_verification' => $step_order
                ]);
            }
            else{
                $next_step_order = $step_order + 1;
                // Update status risiko menjadi terverifikasi
                $projectRisk->update([
                    'status' => ProjectRisk::STATUS_TUNGGU_VERIFIKASI,
                    'status_progress' => 1,
                    'step_verification' => $next_step_order
                ]);
            }

            //disini maka akan simpan approval logs (sementara dimatikan)
            // ApprovalLog::create([
            //     'risk_id' => $projectRisk->id,
            //     'approval_step_id' => $approval_step_id,
            //     'type' => 2, // 1 untuk unit, 2 untuk project
            //     'step_order' => $step_order,
            //     'approved_by' => auth()->id(),
            //     'approved_at' => now(),
            // ]);

            //semua batch notes perlu diupdate sudah read jadi unread menjadi false
            $batchNotes = DataBatchNotes::where('data_batch_id', $dataBatch->id)
                ->where('step_order', $step_order)
                ->update([
                    'unread' => false
                ]);

            // Tambahkan catatan verifikasi
            RiskNote::create([
                'risiko_id' => $projectRisk->id,
                'type' => 2,
                'status' => 1,
                'notes' => $request->catatan_verifikasi,
                'user_id' => $user->id,
            ]);

            $message = 'Risiko berhasil diverifikasi dan diterima.';
        } else {
            Log::channel('verification')->info('Verifikasi risiko project dengan ID: ' . $projectRisk->id . ' ditolak oleh user dengan ID: ' . auth()->id());
            // Update status risiko menjadi revisi (kembali ke draft)
            $projectRisk->update([
                'status' => ProjectRisk::STATUS_REJECTED,
                'status_progress' => 2
            ]);

            $dataBatch->update([
                    'status' => DataBatch::STATUS_REVISI,
                ]);

            //semua batch notes perlu diupdate sudah read jadi unread menjadi false
            $batchNotes = DataBatchNotes::where('data_batch_id', $dataBatch->id)
                ->where('step_order', $step_order)
                ->update([
                    'unread' => false
                ]);

            // Tambahkan catatan penolakan
            RiskNote::create([
                'risiko_id' => $projectRisk->id,
                'type' => 2,
                'status' => 2,
                'notes' => $request->catatan_verifikasi,
                'user_id' => $user->id,
            ]);

            // // Update status batch jika diperlukan
            // if ($dataBatch->status == DataBatch::STATUS_VERIFIKASI) {
            //     $dataBatch->update([
            //         'status' => DataBatch::STATUS_REVISI
            //     ]);
            // }

            $message = 'Risiko ditolak dan dikembalikan untuk revisi.';
        }

        // Cek apakah semua risiko sudah diverifikasi
        // $pendingRisks = ProjectRisk::where('project_periode_list_id', $projectRisk->project_periode_list_id)
        //     ->where('status', ProjectRisk::STATUS_DIKIRIM)
        //     ->count();

        // if ($pendingRisks == 0 && $dataBatch->status == DataBatch::STATUS_VERIFIKASI) {
        //     // Semua risiko sudah diverifikasi, update status batch
        //     $dataBatch->update([
        //         'status' => DataBatch::STATUS_FINISH,
        //         'finish' => true
        //     ]);
        // }

        return redirect()->route('projects.risks.index', ['project' => $projectPeriodeList->id])
            ->with('success', $message);
    }

    public function eskalasi(Request $request)
    {
        $user = auth()->user();
        $project_periode_id = $request->input('project_id');
        $project_id = null;
        $projectPeriodeList = ProjectPeriodeList::findOrFail($request->input('project_id'));
        if ($projectPeriodeList) {
          $project_id = $projectPeriodeList->project_id;
        }
        $project = Project::find($project_id);

        $level_id = $user->level_id;
        $periode_id = 0;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        //cek databatch terkait dimana untuk step verification dan user flow step verification harus sama
        $verificationData = $this->getUserVerificationStep($level_id, $is_mr);
        $u_step = $verificationData['u_step'];
        $user_verification = $verificationData['user_verification'];

        //get data batch step
        $dataBatch = DataBatch::where('project_id', $project_id)
                ->where('periode_id', $periode_id)
                ->where('type', 2)
                ->orderBy('batch', 'desc')
                ->first();

        $b_step = $dataBatch->step_verification;

        //cek untuk step user dengan step branch harus sama
        if ($u_step != $b_step) {
            return redirect()->route('projects.risks.index', [
                'project' => $project_periode_id
            ])->with('error', 'Anda tidak berhak melakukan eskalasi pada tahap ini. Saat ini tahap eskalasi hanya dilakukan oleh '.$user_verification);
        }


        // Cek apakah semua risiko sudah memiliki status 3
        $risikos = ProjectRisk::where('project_id', $project_id)
                ->where('periode_id', $periode_id)
                ->where('status', '!=', 6)
                ->get();

        $belumStatus3 = false;
        $idRisikoBelumStatus3 = [];
        foreach ($risikos as $risiko) {
            //jika bukan final
            if($u_step != 4){
                if ($risiko->status != ProjectRisk::STATUS_TUNGGU_VERIFIKASI || $risiko->step_verification <= $u_step) { // STATUS_TUNGGU_VERIFIKASI = 3
                    $belumStatus3 = true;
                    $idRisikoBelumStatus3[] = $risiko->id;
                }
            }
            else{
                if ($risiko->status != ProjectRisk::STATUS_TERVERIFIKASI && $risiko->status != ProjectRisk::STATUS_PUBLISHED) { // Cek jika bukan status 4 atau 6
                    $belumStatus3 = true;
                    $idRisikoBelumStatus3[] = $risiko->id;
                }
            }
        }

        if ($belumStatus3) {
            // Kumpulkan deskripsi peristiwa risiko yang belum status 3
            $risikoTidakStatus3 = [];
            foreach ($idRisikoBelumStatus3 as $id) {
                $risiko = $risikos->where('id', $id)->first();
                if ($risiko) {
                    // Ambil deskripsi peristiwa risiko
                    $deskripsi = $risiko->deskripsi_peristiwa_risiko ?:
                                ($risiko->peristiwaRisiko ? $risiko->peristiwaRisiko->title : 'Risiko #' . $risiko->id);
                    $risikoTidakStatus3[] = $deskripsi;
                }
            }

            // Buat pesan error dengan daftar risiko yang belum status 3
            $pesanError = 'Terdapat risiko yang perlu direvisi atau diverifikasi:<br /><ul>';
            foreach ($risikoTidakStatus3 as $deskripsi) {
                $pesanError .= '<li>' . $deskripsi . '</li>';
            }
            $pesanError .= '</ul><br />Silahkan pastikan semua risiko sudah diverifikasi terlebih dahulu.';

            return redirect()->route('projects.risks.index', [
                'project' => $project_periode_id
            ])->with('error', $pesanError);
        }
        else{
            //$appFlow = $this->getFlowData($project_id, $level_id);
            //$step_order = $appFlow['step_order'];//sementara diganti u_step
            $step_order = $u_step;
            //dd($step_order);
            // if ($dataBatch) {
            //     $dataBatch->update([
            //         'status' => DataBatch::STATUS_VERIFIKASI,
            //         'step_verification' => $step_order + 1
            //     ]);
            // }

            try {
                // Mulai transaksi database
                DB::beginTransaction();

                if($u_step==4){
                    if ($dataBatch) {
                        $dataBatch->update([
                            'status' => DataBatch::STATUS_FINISH,
                            'step_verification' => $step_order,
                            'finish' => true
                        ]);

                        DataBatch::create([
                            'project_id' => $project_id,
                            'periode_id' => $periode_id,
                            'type' => 2,
                            'batch' => $dataBatch->batch + 1,
                            'status' => DataBatch::STATUS_PROSES,
                            'step_verification' => 1,
                            'finish' => false
                        ]);
                    }

                    //update project risk terkait menjadi publish
                    ProjectRisk::where('project_id', $project_id)
                        ->where('periode_id', $periode_id)
                        ->update([
                            'status' => ProjectRisk::STATUS_PUBLISHED,
                            'step_verification' => $step_order
                        ]);

                    //tetapkan project risk utama
                    ProjectRisk::determineMainRisks($project_id, $periode_id);
                }
                else{
                    if ($dataBatch) {
                        $dataBatch->update([
                            'status' => DataBatch::STATUS_VERIFIKASI,
                            'step_verification' => $step_order + 1
                        ]);
                    }
                }

                // Update ProjectRisk dengan status=3 dan step_verification=u_step
                ProjectRisk::where('project_id', $project_id)
                    ->where('periode_id', $periode_id)
                    ->where('status', 3)
                    ->where('step_verification', $u_step)
                    ->update([
                        'step_verification' => $u_step + 1,
                        'status' => 2
                    ]);

                // Commit transaksi jika semua berhasil
                DB::commit();

                return redirect()->route('projects.risks.index', [
                    'project' => $project_periode_id
                ])->with('success', 'Semua risiko berhasil dieskalasi dan diverifikasi.');
            } catch (\Exception $e) {
                // Rollback transaksi jika terjadi error
                DB::rollBack();

                return redirect()->route('projects.risks.index', [
                    'project' => $project_periode_id
                ])->with('error', 'Terjadi kesalahan saat memproses data: ' . $e->getMessage());
            }

            return redirect()->route('projects.risks.index', [
                'project' => $project_periode_id
            ])->with('success', 'Semua risiko berhasil dieskalasi dan diverifikasi.');
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
}
