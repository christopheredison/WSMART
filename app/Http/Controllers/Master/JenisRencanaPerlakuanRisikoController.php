<?php

namespace App\Http\Controllers\Master;

use App\Models\JenisRencanaPerlakuanRisiko;
use Illuminate\Support\Facades\Gate;

class JenisRencanaPerlakuanRisikoController extends BasicCRUDController
{
    protected $model = JenisRencanaPerlakuanRisiko::class;
    protected $basePermission = 'jenis_rencana_perlakuan_risiko';
    protected $resourceName = 'Jenis Rencana Perlakuan Risiko';
    protected $baseRoute = 'jenis-rencana-perlakuan-risiko.';

    protected $tableColumns = [
        'jenis_rencana_perlakuan_risiko' => [
            'label' => 'Jenis Rencana Perlakuan Risiko',
            'data' => 'jenis_rencana_perlakuan_risiko',
        ],
    ];

    public function index()
    {
        $this->createFields = [
            [
                'name' => 'jenis_rencana_perlakuan_risiko',
                'type' => 'textarea',
                'label' => 'Jenis Rencana Perlakuan Risiko',
                'parameters' => [
                    'jenis_rencana_perlakuan_risiko',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Jenis Rencana Perlakuan Risiko',
                        'required' => true,
                    ]
                ],
            ]
        ];
    
        $this->editFields = $this->createFields;

        if (Gate::check('jenis_rencana_perlakuan_risiko_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
            ];
        }

        if (Gate::check('jenis_rencana_perlakuan_risiko_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
            ];
        }

        return parent::index();
    }
}
