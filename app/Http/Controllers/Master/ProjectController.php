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
use Carbon\Carbon;

class ProjectController extends BasicCRUDController
{
    protected $model = Project::class;
    protected $basePermission = 'project';
    protected $resourceName = 'Project';
    protected $apiPP;

    protected $tableColumns = [
        'project_code' => [
            'label' => 'Kode Project',
            'data' => 'project_code_display',
            'name' => 'meta->profit_center',
            'class' => 'mw-10r'
        ],
        'project_name' => [
            'label' => 'Nama Project',
            'data' => 'project_name',
            'class' => 'mw-10r'
        ],
        'divisi_name' => [
            'label' => 'Divisi Project',
            'data' => 'divisi.name',
            'render' => '(data, type, row) => row.divisi?.name || "-"',
            'class' => 'mw-10r'
        ],
        'nilai_ok' => [
            'label' => 'Nilai OK',
            'data' => 'nilai_ok_display',
            'name' => 'nk',
            'render' => '(data, type, row) => row.nk ? Intl.NumberFormat(\'id-ID\').format(row.nk) : "-"',
        ],
    ];

    public function __construct(ApiPP $apiPP)
    {
        $this->apiPP = $apiPP;

        parent::__construct();
    }

    public function index()
    {
        request()->merge(['append' => ['projectDivisi', 'projectSektor', 'divisi']]);
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

        $this->datatableCallback = function($datatable) {
            $datatable->addColumn('project_code_display', function($row) {
                return $row->meta['profit_center'] ?? '-';
            });

            $datatable->addColumn('nilai_ok_display', function($row) {
                return $row->nk ?? 0;
            });

            return $datatable;
        };

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
        @set_time_limit(600);

        $apiWika = new ApiWika();

        try {
            $projectDatas = $apiWika->getProjects();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data project: ' . $e->getMessage()], 500);
        }

        if (empty($projectDatas)) {
            return response()->json(['message' => 'Data Project dari API WIKA kosong.'], 200);
        }

        $currentPeriod = Carbon::now()->format('Ym');
        $previousPeriod = Carbon::now()->subMonth()->format('Ym');

        $countUpdated = 0;

        foreach ($projectDatas as $projectData) {
            $profitCenter = $projectData['profit_center'] ?? null;
            $nilaiKontrak = 0;

            if ($profitCenter) {
                $nilaiKontrak = $this->fetchNilaiKontrak($apiWika, $profitCenter, $currentPeriod);

                if ($nilaiKontrak == 0) {
                    $nilaiKontrak = $this->fetchNilaiKontrak($apiWika, $profitCenter, $previousPeriod);
                }
            }

            $project = Project::updateOrCreate([
                'project_code' => $projectData['kode_spk'],
            ], [
                'project_name'   => $projectData['nama_spk_full'] ?? $projectData['project_name'],
                'type'           => Project::TYPE_HAS_RKB_RKN,
                'project_status' => 1,
                'profit_center'  => $profitCenter,
                'nk'             => $nilaiKontrak,
                'cost_center_parent' => $projectData['divisisap'] ?? null,
                'masa_pelaksanaan_start' => $projectData['tgl_mulai'] ?? null,
                'masa_pelaksanaan_end' => $projectData['tgl_selesai'] ?? null,
                'tanggal_mulai' => $projectData['tanggal_mulai'] ?? null,
                'meta'           => $projectData,
            ]);

            $divisiUnit = null;
            if (!empty($projectData['divisisap'])) {
                $divisiUnit = Unit::where('cost_center', $projectData['divisisap'])->first();
            }

            ProjectPeriodeList::updateOrCreate([
                'project_id' => $project->id,
                'periode_id' => null,
            ], [
                'unit_id' => $divisiUnit?->id,
            ]);

            $countUpdated++;
        }

        return response()->json([
            'message' => "Sinkronisasi Berhasil. {$countUpdated} data project diperbarui. (NK menggunakan data bulan {$currentPeriod} atau fallback ke {$previousPeriod})",
        ]);
    }

    private function fetchNilaiKontrak($apiWika, $profitCenter, $period)
    {
        try {
            $response = $apiWika->getHasilUsahaProject($period, $profitCenter);

            if (isset($response['status']) &&
                $response['status'] &&
                isset($response['data']['hasil_usaha']['kontrak_review'])) {

                return (float) $response['data']['hasil_usaha']['kontrak_review'];
            }
        } catch (\Exception $e) {
            return 0;
        }

        return 0;
    }
}
