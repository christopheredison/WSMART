<?php

namespace App\Http\Controllers\Master;

use App\Models\ProjectType;
use Illuminate\Support\Facades\Gate;

class ProjectTypeController extends BasicCRUDController
{
    protected $model = ProjectType::class;
    protected $basePermission = 'project_type';
    protected $resourceName = 'Tipe Proyek';
    protected $baseRoute = 'project-type.';

    protected $tableColumns = [
        'name' => [
            'label' => 'Tipe Proyek',
            'data' => 'name',
        ],
    ];

    public function index()
    {
        $this->createFields = [
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Tipe Proyek',
                'parameters' => [
                    'name',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Tipe Proyek',
                        'required' => true,
                    ]
                ],
            ],
        ];
    
        $this->editFields = $this->createFields;

        if (Gate::check('project_type_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
            ];
        }

        if (Gate::check('project_type_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
            ];
        }

        return parent::index();
    }
}
