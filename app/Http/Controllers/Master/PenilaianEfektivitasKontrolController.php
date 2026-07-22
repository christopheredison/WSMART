<?php

namespace App\Http\Controllers\Master;

use App\Models\PenilaianEfektivitasKontrol;
use Illuminate\Support\Facades\Gate;

class PenilaianEfektivitasKontrolController extends BasicCRUDController
{
    protected $model = PenilaianEfektivitasKontrol::class;
    protected $basePermission = 'penilaian_efektivitas_kontrol';
    protected $resourceName = 'Penilaian Efektivitas Kontrol';
    protected $baseRoute = 'penilaian-efektivitas-kontrol.';

    protected $tableColumns = [
        'efektivitas_kontrol' => [
            'label' => 'Efektivitas Kontrol',
            'data' => 'efektivitas_kontrol',
        ],
    ];

    public function index()
    {
        $this->createFields = [
            [
                'name' => 'efektivitas_kontrol',
                'type' => 'text',
                'label' => 'Efektivitas Kontrol',
                'parameters' => [
                    'efektivitas_kontrol',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Efektivitas Kontrol',
                        'required' => true,
                    ]
                ],
            ]
        ];
    
        $this->editFields = $this->createFields;

        if (Gate::check('penilaian_efektivitas_kontrol_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
            ];
        }

        if (Gate::check('penilaian_efektivitas_kontrol_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
            ];
        }

        return parent::index();
    }
}
