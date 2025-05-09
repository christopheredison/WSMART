<?php

namespace App\Http\Controllers\Master;

use App\Models\Project;
use App\Models\ProjectLocation;
use App\Models\ProjectSektor;
use App\Models\ProjectType;
use App\Supports\ApiPP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends BasicCRUDController
{
    protected $model = Project::class;
    protected $basePermission = 'project';
    protected $resourceName = 'Project';
    protected $createType = 'sync';
    protected $apiPP;

    protected $tableColumns = [
        'project_name' => [
            'label' => 'Nama Project',
            'data' => 'project_name',
        ],
        'divisi_name' => [
            'label' => 'Divisi Project',
            'data' => 'projectDivisi.divisi_name',
            'render' => '(data, type, row) => row.project_divisi?.divisi_name || "-"',
        ],
        'sektor_name' => [
            'label' => 'Konstruksi Spesifik',
            'data' => 'projectSektor.sektor_name',
            'render' => '(data, type, row) => row.project_sektor?.sektor_name || "-"',
        ],
    ];

    public function __construct(ApiPP $apiPP)
    {
        $this->apiPP = $apiPP;

        parent::__construct();
    }

    public function index()
    {
        request()->merge(['append' => ['projectDivisi', 'projectSektor']]);
        $this->editFields = [
            [
                'name' => 'project_code',
                'type' => 'text',
                'label' => 'Kode Project',
                'parameters' => [
                    'project_code',
                    '',
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
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Nama Project',
                        'readonly' => true,
                        'required' => true,
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
                    '',
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Konstruksi Spesifik',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'tender_status',
                'type' => 'select',
                'label' => 'Status Tender',
                'parameters' => [
                    'tender_status',
                    __('project.tender_statuses'),
                    '',
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Status Tender',
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
                    '',
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
                    '',
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
                    '',
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
                    '',
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
                    '',
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
                    '',
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
                    '',
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
                    '',
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
                'label' => 'Nilai Kontrak PPN',
                'parameters' => [
                    'nk_ppn',
                    '',
                    [
                        'class' => 'form-control inputmask-general',
                        'placeholder' => 'Masukkan Nilai Kontrak PPN',
                        //'required' => true,
                        'step' => '0.01',
                    ]
                ],
            ],
            [
                'name' => 'masa_pelaksanaan',
                'type' => 'text',
                'label' => 'Masa Pelaksanaan',
                'parameters' => [
                    'masa_pelaksanaan',
                    '',
                    [
                        'class' => 'form-control flatpickr-range',
                        'placeholder' => 'Masukkan Masa Pelaksanaan',
                        //'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'rapt',
                'type' => 'text',
                'label' => 'RAPT (Rp)',
                'parameters' => [
                    'rapt',
                    '',
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
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan RAPT (%)',
                        //'required' => true,
                        'max' => 100,
                        'min' => 0,
                    ]
                ],
            ],[
                'name' => 'rapk',
                'type' => 'text',
                'label' => 'RAPK (Rp)',
                'parameters' => [
                    'rapk',
                    '',
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
                'type' => 'number',
                'label' => 'RAPK (%)',
                'parameters' => [
                    'rapk_persentase',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan RAPK (%)',
                        //'required' => true,
                        'max' => 100,
                        'min' => 0,
                    ]
                ],
            ],
            [
                'name' => 'rapk_0_10_rp',
                'type' => 'text',
                'label' => 'RAPK Hold Point 0-10% (Rp)',
                'parameters' => [
                    'rapk_0_10_rp',
                    '',
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
                'label' => 'RAPK Hold Point 0-10% (%)',
                'parameters' => [
                    'rapk_0_10_persen',
                    '',
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
                'label' => 'RAPK Hold Point 30-50% (Rp)',
                'parameters' => [
                    'rapk_30_50_rp',
                    '',
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
                'label' => 'RAPK Hold Point 30-50% (%)',
                'parameters' => [
                    'rapk_30_50_persen',
                    '',
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
                'label' => 'RAPK Hold Point 70-90% (Rp)',
                'parameters' => [
                    'rapk_70_90_rp',
                    '',
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
                'label' => 'RAPK Hold Point 70-90% (%)',
                'parameters' => [
                    'rapk_70_90_persen',
                    '',
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
                'label' => 'RAPK Hold Point 100% (Rp)',
                'parameters' => [
                    'rapk_100_rp',
                    '',
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
                'label' => 'RAPK Hold Point 100% (%)',
                'parameters' => [
                    'rapk_100_persen',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan RAPK Hold Point 100% (%)',
                        //'required' => true,
                        'max' => 100,
                        'min' => 0,
                    ]
                ],
            ],
        ];

        if (Gate::check('project_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
                'permissions' => ['project_edit'],
            ];
        }

        return parent::index();
    }

    public function store(Request $request)
    {
        $projects = $this->apiPP->getProjects();

        if (!($projects['data'] ?? [])) {
            throw new \Exception('Project List API return empty data. Message: ' . ($projects['message'] ?? 'No message'));
        }

        $availableProjectCodes = array_column($projects['data'], 'code');
        foreach ($projects['data'] as $projectData) {
            Project::updateOrCreate([
                'project_code' => $projectData['code'],
            ], [
                'project_name' => $projectData['name'],
                'type' => $projectData['rab'] + $projectData['rkn'] ? Project::TYPE_HAS_RKB_RKN : Project::TYPE_NO_RKB_RKN,
                'project_status' => 1,
                'meta' => $projectData,
            ]);
        }

        Project::whereNotIn('project_code', $availableProjectCodes)->orWhereNull('project_code')->delete();

        return response()->json([
            'message' => 'Data berhasil disinkronisasi',
        ]);
    }

    public function update(Request $request, $resource)
    {
        // $request->validate([
        //     'project_sektor_id' => 'required|exists:project_sektors,id',
        //     'tender_status' => 'required|in:' . implode(',', array_keys(__('project.tender_statuses'))),
        //     'owner' => 'required',
        //     'owner_category' => 'required|in:' . implode(',', array_keys(__('project.owner_categories'))),
        //     'sumber_dana' => 'required|in:' . implode(',', array_keys(__('project.sumber_danas'))),
        //     'project_location_id' => 'required|exists:project_locations,id',
        //     'project_type_id' => 'required|exists:project_types,id',
        //     'jenis_kontrak' => 'required|in:' . implode(',', array_keys(__('project.jenis_kontraks'))),
        //     'cara_pembayaran' => 'required|in:' . implode(',', array_keys(__('project.cara_pembayarans'))),
        //     'scope_pekerjaan' => 'required|in:' . implode(',', array_keys(__('project.scope_pekerjaans'))),
        //     'nk_ppn' => 'required|numeric',
        //     'masa_pelaksanaan' => 'required',
        //     'rapt' => 'required|numeric',
        //     'rapt_persentase' => 'required|integer|min:0|max:100',
        //     'rapk_0_10_rp' => 'required|numeric',
        //     'rapk_0_10_persen' => 'required|integer|min:0|max:100',
        //     'rapk_30_50_rp' => 'required|numeric',
        //     'rapk_30_50_persen' => 'required|integer|min:0|max:100',
        //     'rapk_70_90_rp' => 'required|numeric',
        //     'rapk_70_90_persen' => 'required|integer|min:0|max:100',
        //     'rapk_100_rp' => 'required|numeric',
        //     'rapk_100_persen' => 'required|integer|min:0|max:100',
        // ]);

        $request->validate([
            'project_sektor_id' => 'nullable|exists:project_sektors,id',
            'tender_status' => 'nullable|in:' . implode(',', array_keys(__('project.tender_statuses'))),
            'owner' => '',
            'owner_category' => 'nullable|in:' . implode(',', array_keys(__('project.owner_categories'))),
            'sumber_dana' => 'nullable|in:' . implode(',', array_keys(__('project.sumber_danas'))),
            'project_location_id' => 'nullable|exists:project_locations,id',
            'project_type_id' => 'nullable|exists:project_types,id',
            'jenis_kontrak' => 'nullable|in:' . implode(',', array_keys(__('project.jenis_kontraks'))),
            'cara_pembayaran' => 'nullable|in:' . implode(',', array_keys(__('project.cara_pembayarans'))),
            'scope_pekerjaan' => 'nullable|in:' . implode(',', array_keys(__('project.scope_pekerjaans'))),
            'nk_ppn' => 'nullable|numeric',
            'masa_pelaksanaan' => '',
            'masa_pelaksanaan_awal' => '',
            'masa_pelaksanaan_akhir' => '',
            'rapt' => 'nullable|numeric',
            'rapt_persentase' => 'nullable|min:0|max:100',
            'rapk' => 'nullable|numeric',
            'rapk_persentase' => 'nullable|min:0|max:100',
            'rapk_0_10_rp' => 'nullable|numeric',
            'rapk_0_10_persen' => 'nullable|min:0|max:100',
            'rapk_30_50_rp' => 'nullable|numeric',
            'rapk_30_50_persen' => 'nullable|min:0|max:100',
            'rapk_70_90_rp' => 'nullable|numeric',
            'rapk_70_90_persen' => 'nullable|min:0|max:100',
            'rapk_100_rp' => 'nullable|numeric',
            'rapk_100_persen' => 'nullable|min:0|max:100',
        ]);

        $projectSektor = ProjectSektor::find($request->project_sektor_id);

        $toUpdate = $request->except(['project_code', 'project_name', 'project_status', 'type', 'meta']);

        $toUpdate['project_sektor_id'] = $projectSektor?->id;
        $toUpdate['project_divisi_id'] = $projectSektor?->project_divisi_id;
        
        try {
            $toUpdate['masa_pelaksanaan_start'] = !empty($request->masa_pelaksanaan_awal) ? 
                \Carbon\Carbon::createFromFormat('d/m/Y', $request->masa_pelaksanaan_awal) : null;
            $toUpdate['masa_pelaksanaan_end'] = !empty($request->masa_pelaksanaan_akhir) ? 
                \Carbon\Carbon::createFromFormat('d/m/Y', $request->masa_pelaksanaan_akhir) : null;
        } catch (\Exception $e) {
            $toUpdate['masa_pelaksanaan_start'] = null;
            $toUpdate['masa_pelaksanaan_end'] = null;
        }

        $data = $this->model::findOrfail($resource);

        // $modelFillable = $data->getFillable();

        // foreach ($toUpdate as $key => $value) {
        //     if (!in_array($key, $modelFillable)) {
        //         continue;
        //     }
        //     $data->$key = $value;
        // }
        // $data->save();

        $data->update($toUpdate);

        return $data;
    }
}
