<?php

namespace App\Http\Controllers\Master;

use App\Models\KontrolEksisting;
use App\Models\PeristiwaRisiko;
use Illuminate\Support\Facades\Gate;

class KontrolEksistingController extends BasicCRUDController
{
    protected $model = KontrolEksisting::class;
    protected $basePermission = 'kontrol_eksisting';
    protected $resourceName = 'Eksisting Kontrol';
    protected $baseRoute = 'kontrol-eksisting.';

    protected $tableColumns = [
        'kontrol_eksisting' => [
            'label' => 'Eksisting Kontrol',
            'data' => 'kontrol_eksisting',
        ],
        'peristiwa_risiko_id' => [
            'label' => 'Peristiwa Risiko',
            'data' => 'peristiwaRisiko.title',
            'render' => '(data, type, row) => row.peristiwa_risiko?.title || "-"',
        ],
    ];

    public function index()
    {
        $this->callbackQuery = function ($query) {
            $query->with('peristiwaRisiko');
        };
        $this->createFields = [
            [
                'name' => 'peristiwa_risiko_id',
                'type' => 'select',
                'label' => 'Peristiwa Risiko',
                'parameters' => [
                    'peristiwa_risiko_id',
                    PeristiwaRisiko::pluck('title', 'id')->toArray(),
                    '',
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Peristiwa Risiko',
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'kontrol_eksisting',
                'type' => 'textarea',
                'label' => 'Eksisting Kontrol',
                'parameters' => [
                    'kontrol_eksisting',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Eksisting Kontrol',
                        'required' => true,
                    ]
                ],
            ]
        ];
    
        $this->editFields = $this->createFields;

        if (Gate::check('kontrol_eksisting_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
            ];
        }

        if (Gate::check('kontrol_eksisting_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
            ];
        }

        return parent::index();
    }
}
