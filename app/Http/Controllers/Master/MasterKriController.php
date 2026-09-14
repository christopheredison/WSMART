<?php

namespace App\Http\Controllers\Master;

use App\Models\MasterKRI;
use App\Models\PeristiwaRisiko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MasterKriController extends BasicCRUDController
{
    protected $model = MasterKRI::class;
    protected $basePermission = 'master_kri';
    protected $resourceName = 'KRI';
    protected $baseRoute = 'master-kri.';

    protected $tableColumns = [
        'kri' => [
            'label' => 'KRI',
            'data' => 'kri',
        ],
        'peristiwa_risiko_id' => [
            'label' => 'Peristiwa Risiko',
            'data' => 'peristiwaRisiko.title',
            'render' => '(data, type, row) => row.peristiwa_risiko?.title || "-"',
        ],
        'batas_aman' => [
            'label' => 'Batas Aman',
            'data' => 'batas_aman',
        ],
        'batas_waspada' => [
            'label' => 'Batas Siaga',
            'data' => 'batas_waspada',
        ],
        'batas_bahaya' => [
            'label' => 'Batas Bahaya',
            'data' => 'batas_bahaya',
        ],
    ];

    public function index()
    {
        request()->merge(['append' => ['peristiwaRisiko']]);
        $this->createFields = [
            [
                'name' => 'peristiwa_risiko_id',
                'type' => 'select',
                'label' => 'Peristiwa Risiko',
                'parameters' => [
                    'peristiwa_risiko_id',
                    PeristiwaRisiko::select('id', 'title')->orderBy('title')->get()->pluck('title', 'id')->toArray(),
                    '',
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Peristiwa Risiko',
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'kri',
                'type' => 'textarea',
                'label' => 'Key Risk Indicator',
                'parameters' => [
                    'kri',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Key Risk Indicator',
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'satuan_kri',
                'type' => 'text',
                'label' => 'Satuan KRI',
                'parameters' => [
                    'satuan_kri',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Satuan KRI',
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'batas_aman',
                'type' => 'text',
                'label' => 'Batas Aman',
                'parameters' => [
                    'batas_aman',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Batas Aman',
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'batas_waspada',
                'type' => 'text',
                'label' => 'Batas Siaga',
                'parameters' => [
                    'batas_waspada',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Batas Siaga',
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'batas_bahaya',
                'type' => 'text',
                'label' => 'Batas Bahaya',
                'parameters' => [
                    'batas_bahaya',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Batas Bahaya',
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'jenis',
                'type' => 'select',
                'label' => 'Jenis',
                'parameters' => [
                    'jenis',
                    [
                        MasterKRI::JENIS_UNIT => 'Unit',
                        MasterKRI::JENIS_PROJECT => 'Project',
                    ],
                    '',
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Jenis',
                        'required' => true,
                    ]
                ],
            ],
        ];

        $this->editFields = $this->createFields;

        if (Gate::check('master_kri_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
                'permissions' => ['master_kri_edit'],
            ];
        }

        if (Gate::check('master_kri_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
                'permissions' => ['master_kri_delete'],
            ];
        }

        return parent::index();
    }

    public function store(Request $request)
    {
        $request->merge(['unit_type_id' => $request->user()->unit_type_id]);

        return parent::store($request);
    }

    public function getByPeristiwaRisiko($peristiwaRisikoId)
    {
        $masterKris = MasterKRI::where('peristiwa_risiko_id', $peristiwaRisikoId)->get();

        return response()->json($masterKris);
    }
}
