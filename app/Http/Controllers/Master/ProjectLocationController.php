<?php

namespace App\Http\Controllers\Master;

use App\Models\ProjectLocation;
use Illuminate\Support\Facades\Gate;

class ProjectLocationController extends BasicCRUDController
{
    protected $model = ProjectLocation::class;
    protected $basePermission = 'project_location';
    protected $resourceName = 'Lokasi Proyek';
    protected $baseRoute = 'project-location.';

    protected $tableColumns = [
        'location' => [
            'label' => 'Lokasi Proyek',
            'data' => 'location',
        ],
    ];

    public function index()
    {
        $this->createFields = [
            [
                'name' => 'location',
                'type' => 'text',
                'label' => 'Lokasi Proyek',
                'parameters' => [
                    'location',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Lokasi Proyek',
                        'required' => true,
                    ]
                ],
            ],
        ];
    
        $this->editFields = $this->createFields;

        if (Gate::check('project_location_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
            ];
        }

        if (Gate::check('project_location_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
            ];
        }

        return parent::index();
    }
}
