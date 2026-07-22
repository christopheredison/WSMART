<?php

namespace App\Http\Controllers\Master;

use App\Models\ProjectDivisi;
use Illuminate\Support\Facades\Gate;

class ProjectDivisiController extends BasicCRUDController
{
    protected $model = ProjectDivisi::class;
    protected $basePermission = 'project_divisi';
    protected $resourceName = 'Divisi Project';
    protected $baseRoute = 'project-divisi.';

    protected $createFields = [
        [
            'name' => 'divisi_name',
            'type' => 'text',
            'label' => 'Nama Divisi',
            'parameters' => [
                'divisi_name',
                '',
                [
                    'class' => 'form-control',
                    'placeholder' => 'Masukkan Nama Divisi',
                    'required' => true,
                ]
            ],
        ]
    ];
    
    protected $editFields = [
        [
            'name' => 'divisi_name',
            'type' => 'text',
            'label' => 'Nama Divisi',
            'parameters' => [
                'divisi_name',
                '',
                [
                    'class' => 'form-control',
                    'placeholder' => 'Masukkan Nama Divisi',
                    'required' => true,
                ]
            ],
        ]
    ];

    protected $tableColumns = [
        'divisi_name' => [
            'label' => 'Nama Divisi',
            'data' => 'divisi_name',
        ],
    ];

    public function index()
    {
        $this->editFields = $this->createFields;

        if (Gate::check('project_divisi_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
                'permissions' => ['project_divisi_edit'],
            ];
        }

        if (Gate::check('project_divisi_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
                'permissions' => ['project_divisi_delete'],
            ];
        }

        return parent::index();
    }
}
