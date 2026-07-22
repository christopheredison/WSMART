<?php

namespace App\Http\Controllers\Master;

use App\Models\OpsiPerlakuanRisiko;
use Illuminate\Support\Facades\Gate;

class OpsiPerlakuanRisikoController extends BasicCRUDController
{
    protected $model = OpsiPerlakuanRisiko::class;
    protected $basePermission = 'opsi_perlakuan_risiko';
    protected $resourceName = 'Opsi Perlakuan Risiko';
    protected $baseRoute = 'opsi-perlakuan-risiko.';

    protected $tableColumns = [
        'opsi_perlakuan_risiko' => [
            'label' => 'Opsi Perlakuan Risiko',
            'data' => 'opsi_perlakuan_risiko',
        ],
    ];

    public function index()
    {
        $this->createFields = [
            [
                'name' => 'opsi_perlakuan_risiko',
                'type' => 'textarea',
                'label' => 'Opsi Perlakuan Risiko',
                'parameters' => [
                    'opsi_perlakuan_risiko',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Opsi Perlakuan Risiko',
                        'required' => true,
                    ]
                ],
            ]
        ];
    
        $this->editFields = $this->createFields;

        if (Gate::check('opsi_perlakuan_risiko_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
            ];
        }

        if (Gate::check('opsi_perlakuan_risiko_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
            ];
        }

        return parent::index();
    }
}
