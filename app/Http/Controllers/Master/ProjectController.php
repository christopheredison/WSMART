<?php

namespace App\Http\Controllers\Master;

use App\Models\Project;
use App\Models\ProjectLocation;
use App\Models\ProjectSektor;
use App\Models\ProjectType;
use App\Supports\ApiPP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Supports\ApiWika;
use App\Models\Unit;
use App\Models\ProjectPeriodeList;

class ProjectController extends BasicCRUDController
{
    protected $model = Project::class;
    protected $basePermission = 'project';
    protected $resourceName = 'Project';
    protected $apiPP;

    protected $tableColumns = [
        'project_name' => [
            'label' => 'Nama Project',
            'data' => 'project_name',
        ],
        'divisi_name' => [
            'label' => 'Divisi Project',
            'data' => 'projectDivisi.divisi_name',
            'render' => '(data, type, row) => row.project_divisi?.divisi_name || "-"',
        ],
        'sektor_name' => [
            'label' => 'Konstruksi Spesifik',
            'data' => 'projectSektor.sektor_name',
            'render' => '(data, type, row) => row.project_sektor?.sektor_name || "-"',
        ],
    ];

    public function __construct(ApiPP $apiPP)
    {
        $this->apiPP = $apiPP;

        parent::__construct();
    }

    public function index()
    {
        request()->merge(['append' => ['projectDivisi', 'projectSektor']]);
        $this->editFields = [
            [
                'name' => 'project_code',
                'type' => 'text',
                'label' => 'Kode Project',
                'parameters' => [
                    'project_code',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Kode Project',
                        'readonly' => true,
                        'required' => true,
                    ]
                ],
            ],
            [
                'name' => 'project_name',
                'type' => 'text',
                'label' => 'Nama Project',
                'parameters' => [
                    'project_name',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Nama Project',
                        'readonly' => true,
                        'required' => true,
                    ]
                ],
            ],
        ];

        if (Gate::check('project_edit') && false) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
                'permissions' => ['project_edit'],
            ];
        }

        // Tambahkan tombol "Sync WIKA" di footer halaman list proyek
        $this->cardFooter = '
            <div class="text-end">
                <button id="btn-sync-wika" class="btn btn-outline-info btn-sm">
                    <span class="bx bx-sync"></span>
                    <span class="ms-1">Sync WIKA</span>
                </button>
            </div>
        ';

        // Script untuk memicu sinkronisasi WIKA via AJAX
        $syncUrl = route('projects.sync-wika');
        $csrf = csrf_token();
        $this->extraScripts[] = <<<HTML
            <script>
            $(function() {
            $(document).on('click', '#btn-sync-wika', function() {
                Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Sinkronisasi WIKA akan menambah/memperbarui proyek tanpa menghapus data yang tidak ada di API.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, sinkronisasi!",
                cancelButtonText: "Batal",
                buttonsStyling: false,
                customClass: { confirmButton: 'btn btn-primary border-0', cancelButton: 'btn btn-muted border-0' }
                }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                    title: 'Sedang memproses...',
                    text: 'Mohon tunggu beberapa saat.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                    });
                    $.ajax({
                    url: '$syncUrl',
                    type: 'POST',
                    data: { _token: '$csrf' },
                    success: function (response) {
                        Swal.close();
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message || 'Sinkronisasi WIKA selesai.' });
                        $('.ajax-datatable').DataTable().ajax.reload();
                    },
                    error: function (xhr) {
                        Swal.close();
                        Swal.fire({ icon: 'error', title: 'Error', text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat sinkronisasi WIKA.' });
                    }
                    });
                }
                });
            });
            });
            </script>
            HTML;

        return parent::index();
    }

    public function store(Request $request)
    {
        $projects = $this->apiPP->getProjects();

        if (!($projects['data'] ?? [])) {
            throw new \Exception('Project List API return empty data. Message: ' . ($projects['message'] ?? 'No message'));
        }

        $availableProjectCodes = array_column($projects['data'], 'code');
        foreach ($projects['data'] as $projectData) {
            Project::updateOrCreate([
                'project_code' => $projectData['code'],
            ], [
                'project_name' => $projectData['name'],
                'type' => $projectData['rab'] + $projectData['rkn'] ? Project::TYPE_HAS_RKB_RKN : Project::TYPE_NO_RKB_RKN,
                'project_status' => 1,
                'meta' => $projectData,
            ]);
        }

        Project::whereNotIn('project_code', $availableProjectCodes)->orWhereNull('project_code')->delete();

        return response()->json([
            'message' => 'Data berhasil disinkronisasi',
        ]);
    }

    public function update(Request $request, $resource)
    {
        $request->validate([
            'batas_nilai' => 'numeric|min:0',
        ]);

        $toUpdate['batas_nilai'] = $request->batas_nilai;

        $data = $this->model::with('projectPeriodeList')->findOrfail($resource);

        $data->update($toUpdate);

        return $data;
    }

    // Tambahkan: sinkronisasi proyek dari ApiWika tanpa penghapusan
    public function syncWika(Request $request)
    {
        $projectDatas = (new ApiWika())->getProjects();

        if (!is_array($projectDatas) || empty($projectDatas)) {
            throw new \Exception('Project List WIKA API return empty data.');
        }

        foreach ($projectDatas as $projectData) {
            $project = Project::updateOrCreate([
                'project_code' => $projectData['kode_spk'],
            ], [
                'project_name' => $projectData['nama_spk_full'],
                'type' => Project::TYPE_HAS_RKB_RKN,
                'project_status' => 1,
                'nk' => 0,
                'meta' => $projectData,
            ]);

            // Cari Unit berdasarkan cost_center yang sama dengan divisisap
            $divisiUnit = null;
            if (!empty($projectData['divisisap'])) {
                $divisiUnit = Unit::where('cost_center', $projectData['divisisap'])->first();
            }

            ProjectPeriodeList::updateOrCreate([
                'project_id' => $project->id,
                'periode_id' => null,
            ], [
                'unit_id' => $divisiUnit?->id, // boleh null jika unit tidak ditemukan
            ]);
        }

        return response()->json([
            'message' => 'Sinkronisasi WIKA berhasil tanpa menghapus proyek yang tidak ada di API.',
        ]);
    }
}
