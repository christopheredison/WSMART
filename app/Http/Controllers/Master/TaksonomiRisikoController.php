<?php

namespace App\Http\Controllers\Master;

use App\Models\TaksonomiRisiko;
use Illuminate\Support\Facades\Gate;

class TaksonomiRisikoController extends BasicCRUDController
{
    protected $model = TaksonomiRisiko::class;
    protected $basePermission = 'taksonomi_risiko';
    protected $resourceName = 'Taksonomi Danantara';
    protected $baseRoute = 'taksonomi-risiko.';

    protected $tableColumns = [
        'nama' => [
            'label' => 'Nama Taksonomi Risiko',
            'data' => 'nama',
        ],
        'deskripsi' => [
            'label' => 'Deskripsi Taksonomi Risiko',
            'data' => 'deskripsi',
        ],
    ];

    public function index()
    {
        $this->createFields = [
            [
                'name' => 'nama',
                'type' => 'text',
                'label' => 'Nama Taksonomi Risiko',
                'parameters' => [
                    'nama',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Nama Taksonomi Risiko',
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'deskripsi',
                'type' => 'textarea',
                'label' => 'Deskripsi',
                'parameters' => [
                    'deskripsi',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Deskripsi Taksonomi Risiko',
                        'required' => true,
                        'rows' => 4,
                    ]
                ],
            ]
        ];

        $this->editFields = $this->createFields;

        if (Gate::check('taksonomi_risiko_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
            ];
        }

        if (Gate::check('taksonomi_risiko_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
            ];
        }

        return parent::index();
    }
}
