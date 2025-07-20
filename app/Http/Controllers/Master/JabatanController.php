<?php

namespace App\Http\Controllers\Master;

use App\Models\Jabatan;
use App\Models\Level;
use App\Supports\ApiHC;
use Illuminate\Http\Request;

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
        ],
        'code' => [
            'label' => 'Kode Jabatan',
            'data' => 'code',
        ],
        'description' => [
            'label' => 'Deskripsi',
            'data' => 'description',
        ],
    ];

    public function index()
    {
        $this->editFields = $this->createFields;

        return parent::index();
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
