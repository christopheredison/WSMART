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
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\AreaDampak;
use App\Models\AreaDampakDetail;
use App\Models\LossEventProject;
use App\Models\Jabatan;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Imports\ProjectTenderImport;
use App\Imports\TenderTemplateImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

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
        ],
        'deskripsi_peristiwa_risiko' => [
            'label' => 'Deskripsi Peristiwa Risiko',
            'data' => 'deskripsi_peristiwa_risiko',
            'render' => '(data, type, row) => data || "-"',
        ],
        'nilai_dampak' => [
            'label' => 'Nilai Dampak',
            'data' => 'projectRiskAnalisa.nilai_dampak',
            'sortable' => false,
            'searchable' => false,
            'render' => '(data, type, row) => row.project_risk_analisa?.nilai_dampak || "-"',
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
        'nilai_risiko' => [
            'label' => 'Nilai Risiko',
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
    ];

    public function index() {
        $this->baseRouteParams = ['project' => request()->route('project')];

        $projectPeriodeList = ProjectPeriodeList::with('project')->findOrFail(request()->route('project'));
        $user = request()->user();
        $this->indexSubtitle = $projectPeriodeList->project->project_name;

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

        // $this->callbackQuery = function($query) {
        //     $query->where('project_periode_list_id', request()->route('project'))
        //         ->with('peristiwaRisiko', 'projectRiskAnalisa.skalaProbabilitas');
        // };

        $this->callbackQuery = function($query) {
            $query->leftJoin('project_risk_analisas', 'project_risk_analisas.risiko_id', '=', 'project_risks.id')
                ->where('project_risks.project_periode_list_id', request()->route('project'))
                ->with('peristiwaRisiko', 'projectRiskAnalisa.skalaProbabilitas');
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
                        'class' => 'form-select',
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
                        'class' => 'form-select',
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
                ],
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
                'label' => '<span class="bx bx-show-alt"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('projects.risks.view', ['project' => request()->route('project'), 'risk' => ':id']),
                'title' => 'View Risiko'
            ];

            $this->tableActions[] = [
                'label' => '<span class="bx bx-analyse text-warning"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('projects.risks.analisa', ['project' => request()->route('project'), 'risk' => ':id']),
                'title' => 'Analisa Risiko'
            ];
            
            $this->tableActions[] = [
                'label' => '<span class="bx bx-task text-primary"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('projects.risks.rencana', ['project' => request()->route('project'), 'risk' => ':id']),
                'title' => 'Rencana Perlakuan Risiko'
            ];

            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
                'permissions' => ['project_risk_edit'],
            ];
        }

        if (Gate::check('project_risk_delete')) {
            $this->tableLegend[] = [
                'icon' => '<span class="bx bx-trash text-danger"></span>',
                'label' => 'Hapus'
            ];
            
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
                'url' => route('projects.risks.destroy', ['project' => request()->route('project'), 'risk' => ':id']),
                'permissions' => ['project_risk_delete'],
            ];
        }

        $average = (float) ProjectRisk::where('project_periode_list_id', request()->route('project'))->avg('skala_risiko');

        $this->tableColumns['peristiwa_risiko']['render'] = <<< JS
            (data, type, row) => {
                let add = '';
                if (row.skala_risiko >= $average) {
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

        $this->cardFooter = <<<HTML
            <div>
                <strong>Rata-rata:</strong> <span id="average-risk-value">$average</span>
            </div>
            HTML;

        $this->defaultOrder = [[7, 'desc']];

        $this->importConfig = [
          'buttonText' => 'Upload Risiko Tender',
          'route' => route('projects.risks.import-tender', ['project' => $projectPeriodeList->id]),
          'title' => 'Upload Risiko Tender dari Excel',
          'instructions' => 'Pastikan file Excel Anda memiliki template yang sesuai.',
          'templateUrl' => route('download-tender-template'),
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
        $periode = Periode::where('status','active')->first();

        $peristiwaRisikos = PeristiwaRisiko::where('type', 2)->get();

        $masterKris = MasterKRI::get();
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $kontrolEksistings = KontrolEksisting::get();
        $jenisRisikos = JenisRisiko::get();
        
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();
        
        return view('project-risk.create', compact('periode', 'project', 'peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings', 'projectPeriodeList', 'jenisRisikos'));
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
                'deskripsi_peristiwa_risiko' => [
                    'required',
                    Rule::unique('project_risks')->where(function ($query) use ($project) {
                        return $query->where('project_id', $project->id);
                    })
                ],
                'wbs' => 'required',
                'target_capaian_kinerja' => 'required',
                'jenis_kontrol_eksisting_id' => 'required',
                'penilaian_efektifitas_kontrol' => 'required',
                'perkiraan_waktu_mulai_terpapar_risiko' => 'required',
                'perkiraan_waktu_selesai_terpapar_risiko' => 'required',
                'penyebab_risiko' => 'required|array|min:1',
                'penyebab_risiko.*' => 'required|string',
            ]);

            $user = $request->user();

            //$perkiraanWaktuTerpaparRisiko = explode(' to ', $request->perkiraan_waktu_terpapar_risiko);

            $perkiraanWaktuMulaiTerpaparRisiko = $request->perkiraan_waktu_mulai_terpapar_risiko;
            $perkiraanWaktuSelesaiTerpaparRisiko = $request->perkiraan_waktu_selesai_terpapar_risiko;
            $perkiraanWaktuTerpaparRisikoMulai = DateTime::createFromFormat('d/m/Y', $perkiraanWaktuMulaiTerpaparRisiko)->format('Y-m-d');
            $perkiraanWaktuTerpaparRisikoAkhir = DateTime::createFromFormat('d/m/Y', $perkiraanWaktuSelesaiTerpaparRisiko)->format('Y-m-d');

            $toStore = [
                'unit_type_id' => $user->unit_type_id,
                'unit_id' => $user->unit_id,
                'periode_id' => 0,
                'user_id' => $user->id,
                'project_id' => $project->id,
                'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
                'target_capaian_kinerja' => $request->target_capaian_kinerja,
                'project_periode_list_id' => $projectPeriodeList->id,
                'deskripsi_peristiwa_risiko' => $request->deskripsi_peristiwa_risiko,
                'jenis_kontrol_eksisting_id' => $request->jenis_kontrol_eksisting_id,
                'penilaian_efektifitas_kontrol' => $request->penilaian_efektifitas_kontrol,
                'perkiraan_waktu_terpapar_risiko_mulai' => $perkiraanWaktuTerpaparRisikoMulai,
                'perkiraan_waktu_terpapar_risiko_akhir' => $perkiraanWaktuTerpaparRisikoAkhir,
                'kategori_risiko_id' => $request->kategori_risiko_id,
                'jenis_risiko_id' => $request->jenis_risiko_id,
                'kontrol_eksisting' => '',
                'wbs' => $request->wbs
            ];

            $projectRisk = ProjectRisk::create($toStore);

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
                'deskripsi_peristiwa_risiko' => 'required|unique:project_risks,deskripsi_peristiwa_risiko',
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
        $projectRisk = ProjectRisk::with('penyebabRisikoProjects', 'kriProjects', 'peristiwaRisiko')->findOrFail(request()->route('risk'));

        //dd($projectRisk);
        $projectPeriodeList = ProjectPeriodeList::findOrFail(request()->route('project'));

        $user = request()->user();

        if (!(Gate::check('project_admin_access') || $user->hasProject($projectPeriodeList))) {
            abort(403);
        }

        $project = $projectPeriodeList->project;

        $peristiwaRisikos = PeristiwaRisiko::get();

        $masterKris = MasterKRI::get();
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $kontrolEksistingIds = !empty($projectRisk->kontrol_eksisting) ? explode(',', $projectRisk->kontrol_eksisting) : [];

        $kontrolEksistings = KontrolEksisting::whereIn('id', $kontrolEksistingIds)->get();
        
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();
        $jenisRisikos = JenisRisiko::get();

        return view('project-risk.edit', compact('projectRisk', 'project', 'peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings', 'projectPeriodeList', 'jenisRisikos'));
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

        if ($request->action === 'save') {
            $request->validate([
                'peristiwa_risiko_id' => 'required',
                'kategori_risiko_id' => 'required',
                'jenis_risiko_id' => 'required',
                'deskripsi_peristiwa_risiko' => 'required|unique:project_risks,deskripsi_peristiwa_risiko,' . $projectRisk->id,
                'wbs' => 'required',
                'jenis_kontrol_eksisting_id' => 'required',
                'penilaian_efektifitas_kontrol' => 'required',
                //'perkiraan_waktu_terpapar_risiko' => 'required',
                'perkiraan_waktu_terpapar_risiko_mulai' => 'required',
                'perkiraan_waktu_terpapar_risiko_akhir' => 'required',
                'penyebab_risiko' => 'required|array|min:1',
                'penyebab_risiko.*' => 'required|string',
            ]);

            $user = $request->user();

            //$perkiraanWaktuTerpaparRisiko = explode(' to ', $request->perkiraan_waktu_terpapar_risiko);
            $perkiraanWaktuTerpaparRisikoMulai = DateTime::createFromFormat('d/m/Y', $request->perkiraan_waktu_terpapar_risiko_mulai)->format('Y-m-d');
            $perkiraanWaktuTerpaparRisikoAkhir = DateTime::createFromFormat('d/m/Y', $request->perkiraan_waktu_terpapar_risiko_akhir)->format('Y-m-d');

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
                'jenis_kontrol_eksisting_id' => $request->jenis_kontrol_eksisting_id,
                'penilaian_efektifitas_kontrol' => $request->penilaian_efektifitas_kontrol,
                'perkiraan_waktu_terpapar_risiko_mulai' => $perkiraanWaktuTerpaparRisikoMulai,
                'perkiraan_waktu_terpapar_risiko_akhir' => $perkiraanWaktuTerpaparRisikoAkhir,
                'wbs' => $request->wbs,
            ];

            $projectRisk->update($toUpdate);

            $projectRisk->penyebabRisikoProjects()->delete();

            foreach ($request->penyebab_risiko as $penyebabRisiko) {
                $projectRisk->penyebabRisikoProjects()->create([
                    'penyebab_risiko' => $penyebabRisiko,
                ]);
            }

            $projectRisk->kriProjects()->delete();

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

            $projectRisk->projectKontrolEksistings()->delete();

            foreach ($request->kontrol_eksisting as $kontrolEksisting) {
                $projectRisk->projectKontrolEksistings()->create([
                    'kontrol_eksisting_desc' => $kontrolEksisting,
                ]);
            }

            return [
                'redirect' => route('projects.risks.index', ['project' => $projectPeriodeList->id]),
            ];

        } elseif ($request->action === 'draft') {

            $request->validate([
                'deskripsi_peristiwa_risiko' => 'required|unique:project_risks,deskripsi_peristiwa_risiko,' . $projectRisk->id,
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
        return view('project-risk.analisa', compact('projectRisk', 'project', 'periode', 'projectPeriodeList', 'skalaProbabilitas', 'riskMaps', 'analisa', 'areas', 'groupedAreas', 'risk_tolerance', 'risk_limit'));
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
            'penyebabRisikoProjects.perlakuanPenyebabRisiko' // Eager load relasi
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
}
