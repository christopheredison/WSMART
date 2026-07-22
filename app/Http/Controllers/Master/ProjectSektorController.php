<?php

namespace App\Http\Controllers\Master;

use App\Models\ProjectDivisi;
use App\Models\ProjectSektor;
use Illuminate\Support\Facades\Gate;

class ProjectSektorController extends BasicCRUDController
{
    protected $model = ProjectSektor::class;
    protected $basePermission = 'project_sektor';
    protected $resourceName = 'Sektor Industri Project';
    protected $baseRoute = 'project-sektor.';

    protected $tableColumns = [
        'sektor_name' => [
            'label' => 'Sektor Industri',
            'data' => 'sektor_name',
        ],
        'divisi_name' => [
            'label' => 'Divisi Project',
            'data' => 'projectDivisi.divisi_name',
            'render' => '(data, type, row) => row.project_divisi.divisi_name',
        ],
    ];

    public function index()
    {
        request()->merge(['append' => ['projectDivisi']]);
        $this->createFields = [
            [
                'name' => 'sektor_name',
                'type' => 'text',
                'label' => 'Sektor Industri',
                'parameters' => [
                    'sektor_name',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Nama Sektor Industri',
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'project_divisi_id',
                'type' => 'select',
                'label' => 'Divisi Project',
                'parameters' => [
                    'project_divisi_id',
                    ProjectDivisi::select('id', 'divisi_name')->orderBy('divisi_name')->get()->pluck('divisi_name', 'id')->toArray(),
                    '',
                    [
                        'class' => 'form-select',
                        'placeholder' => 'Pilih Divisi Project',
                        'required' => true,
                    ]
                ],
            ],
        ];

        $this->editFields = $this->createFields;

        if (Gate::check('project_sektor_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
                'permissions' => ['project_sektor_edit'],
            ];
        }

        if (Gate::check('project_sektor_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
                'permissions' => ['project_sektor_delete'],
            ];
        }

        return parent::index();
    }
}
