<?php

namespace App\Http\Controllers\Master;

use App\Models\Jabatan;
use App\Models\Level;
use App\Supports\ApiHC;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JabatanController extends BasicCRUDController
{
    protected $model = Jabatan::class;
    protected $basePermission = 'jabatan';
    protected $resourceName = 'Jabatan';
    protected $baseRoute = 'jabatan.';
    protected $createType = 'sync';

    protected $tableColumns = [
        'name' => [
            'label' => 'Nama Jabatan',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
        ],
        'code' => [
            'label' => 'Kode Jabatan',
            'data' => 'code',
            'orderable' => true,
            'searchable' => true,
        ],
        'jabatan_type' => [
            'label' => 'Tipe Jabatan',
            'data' => 'jabatan_type_label',
            'orderable' => true,
            'searchable' => true,
        ],
    ];

    public function __construct()
    {
        $this->basePermission = 'jabatan';

        if ($this->basePermission) {
            $this->middleware('can:' . $this->basePermission . '_list', ['only' => ['index']]);
            $this->middleware('can:' . $this->basePermission . '_view', ['only' => ['show']]);
            $this->middleware('can:' . $this->basePermission . '_create', ['only' => ['store', 'create']]);
            $this->middleware('can:' . $this->basePermission . '_delete', ['only' => ['destroy']]);
        }

        if (!$this->baseViewPath) {
            $this->baseViewPath = 'master.basic-crud';
        }

        if (!$this->baseRoute) {
            $this->baseRoute = 'jabatan.';
        }

        if (!$this->resourceName) {
            $this->resourceName = 'Jabatan';
        }
    }

    public function index()
    {
        $this->editFields = [
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Nama Jabatan',
                'parameters' => [
                    'name',
                    null,
                    [
                        'class' => 'form-control',
                        'readonly' => true,
                    ]
                ],
            ],
            [
                'name' => 'code',
                'type' => 'text',
                'label' => 'Kode Jabatan',
                'parameters' => [
                    'code',
                    null,
                    [
                        'class' => 'form-control',
                        'readonly' => true,
                    ]
                ],
            ],
            [
                'name' => 'jabatan_type',
                'type' => 'select',
                'label' => 'Tipe Jabatan',
                'parameters' => [
                    'jabatan_type',
                    [
                        Jabatan::TIPE_BUKAN_PROJECT => 'Bukan Project',
                        Jabatan::TIPE_PROJECT => 'Project',
                    ],
                    null,
                    [
                        'class' => 'form-select',
                        'required' => true,
                    ]
                ],
            ],
        ];

        $this->tableActions[] = [
            'label' => '<span class="bx bx-edit"></span>',
            'btn_icon' => true,
            'action' => 'edit',
        ];

        $this->datatableCallback = function ($datatable) {
            $datatable->addColumn('jabatan_type_label', function ($row) {
                return $row->jabatan_type_label;
            });
        };

        return parent::index();
    }

    public function update(Request $request, $resource)
    {
        $validated = $request->validate([
            'jabatan_type' => 'required|in:1,2',
        ]);

        try {
            $item = $this->model::findOrFail($resource);
            $item->update($validated);

            return response()->json([
                'message' => $this->resourceName . ' berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui ' . $this->resourceName . ': ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $apiHC = new ApiHC();
        $response = $apiHC->apiRequest('GET', '/', [
            'client' => 'risk',
            'method' => 'get_jabportal',
            'key' => 'E6HtZkuG',
            'page' => 1,
            'limit' => 999999,
        ]);
        
        $jabatanData = $response['data'];

        $this->model::onlyTrashed()
            ->whereIn('code', collect($jabatanData)->pluck('id_jabatan'))
            ->restore();

        foreach ($jabatanData as $jabatan) {
            $this->model::updateOrCreate([
                'code' => $jabatan['id_jabatan'],
            ], [
                'name' => $jabatan['nama_jabatan'],
                'description' => '',
            ]);
        }

        $existingCodes = collect($jabatanData)->pluck('id_jabatan')->toArray();

        $this->model::whereNotIn('code', $existingCodes)->delete();

        return [
            'message' => 'Data berhasil disinkronisasi. Total data: ' . count($jabatanData),
        ];
    }
}
