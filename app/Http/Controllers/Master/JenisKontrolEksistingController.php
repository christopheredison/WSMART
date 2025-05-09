<?php

namespace App\Http\Controllers\Master;

use App\Models\JenisKontrolEksisting;
use Illuminate\Support\Facades\Gate;

class JenisKontrolEksistingController extends BasicCRUDController
{
    protected $model = JenisKontrolEksisting::class;
    protected $basePermission = 'jenis_kontrol_eksisting';
    protected $resourceName = 'Jenis Eksisting Kontrol';
    protected $baseRoute = 'jenis-kontrol-eksisting.';

    protected $createFields = [
        [
            'name' => 'jenis_kontrol',
            'type' => 'textarea',
            'label' => 'Jenis Eksisting Kontrol',
            'parameters' => [
                'jenis_kontrol',
                '',
                [
                    'class' => 'form-control',
                    'placeholder' => 'Masukkan Jenis Eksisting Kontrol',
                    'required' => true,
                ]
            ],
        ]
    ];

    protected $tableColumns = [
        'jenis_kontrol' => [
            'label' => 'Jenis Eksisting Kontrol',
            'data' => 'jenis_kontrol',
        ],
    ];

    public function index()
    {
        $this->editFields = $this->createFields;

        if (Gate::check('jenis_kontrol_eksisting_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
            ];
        }

        if (Gate::check('jenis_kontrol_eksisting_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
            ];
        }

        return parent::index();
    }
}
