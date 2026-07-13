<?php

namespace App\Http\Controllers\Master;

use App\Models\Project;
use App\Models\Audit;
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
            'label' => 'Nilai OK Total',
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

        if (Gate::check('project_list')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-show"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('projects.detail', ':id'),
                'title' => 'Lihat Detail',
                'class' => 'btn-outline-primary btn-sm'
            ];
            $this->tableActions[] = [
                'label' => '<span class="bx bx-history"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('projects.logs.show', ':id'),
                'title' => 'Lihat Log Perubahan',
                'class' => 'btn-outline-primary btn-sm'
            ];
        }

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
            <div class="d-flex justify-content-end gap-2">
                <a href="' . route('projects.logs') . '" class="btn btn-outline-secondary btn-sm">
                    <span class="bx bx-history"></span>
                    <span class="ms-1">Log Perubahan</span>
                </a>
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
                            customClass: { confirmButton: 'btn btn-primary border-0 me-2', cancelButton: 'btn btn-secondary border-0' }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Sedang memproses...',
                                    text: 'Mohon tunggu, proses ini membutuhkan waktu karena mengecek satu per satu project.',
                                    allowOutsideClick: false,
                                    didOpen: () => { Swal.showLoading(); }
                                });

                                $.ajax({
                                    url: '$syncUrl',
                                    type: 'POST',
                                    data: { _token: '$csrf' },
                                    success: function (response) {
                                        Swal.close();

                                        // Jika ada data yang gagal masuk
                                        if (response.count_failed > 0) {
                                            let errorHtml = '<div style="max-height: 250px; overflow-y: auto; text-align: left;" class="mt-3">';
                                            errorHtml += '<table class="table table-sm table-bordered" style="font-size: 13px;">';
                                            errorHtml += '<thead class="table-light"><tr><th width="15%">Kode SPK</th><th width="35%">Project</th><th>Alasan Gagal</th></tr></thead><tbody>';

                                            response.failed_list.forEach(item => {
                                                errorHtml += `<tr><td>\${item.kode}</td><td>\${item.nama}</td><td class="text-danger">\${item.alasan}</td></tr>`;
                                            });

                                            errorHtml += '</tbody></table></div>';

                                            Swal.fire({
                                                icon: 'warning',
                                                title: 'Sinkronisasi Selesai Sebagian',
                                                html: `<p>Berhasil: <b>\${response.count_updated}</b> | Gagal Ditarik: <b class="text-danger">\${response.count_failed}</b></p>\${errorHtml}`,
                                                width: '800px',
                                                confirmButtonText: 'Tutup'
                                            });
                                        } else {
                                            // Jika semua berhasil 100%
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Berhasil',
                                                text: `\${response.count_updated} Project berhasil disinkronisasi.`
                                            });
                                        }

                                        $('.ajax-datatable').DataTable().ajax.reload();
                                    },
                                    error: function (xhr) {
                                        Swal.close();
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error Sistem',
                                            text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan saat menghubungi API.'
                                        });
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
        if ($request->has('biaya_perlakuan_risiko_rkp') && $request->biaya_perlakuan_risiko_rkp !== null) {
            $cleanRkp = str_replace('.', '', $request->biaya_perlakuan_risiko_rkp);
            $cleanRkp = str_replace(',', '.', $cleanRkp);
            $request->merge(['biaya_perlakuan_risiko_rkp' => $cleanRkp]);
        }

        $request->validate([
            // 'batas_nilai' => 'nullable|numeric|min:0',
            'biaya_perlakuan_risiko_rkp' => 'nullable|numeric|min:0',
        ]);

        $toUpdate = [];

        if ($request->has('batas_nilai')) {
            $toUpdate['batas_nilai'] = $request->batas_nilai;
        }

        if ($request->has('biaya_perlakuan_risiko_rkp')) {
            $toUpdate['biaya_perlakuan_risiko_rkp'] = $request->biaya_perlakuan_risiko_rkp;
        }

        $data = $this->model::with('projectPeriodeList')->findOrFail($resource);

        $data->update($toUpdate);

        return $data;
    }

    public function detail($id)
    {
        // Load relasi jika diperlukan (divisi, sektor, tipe)
        $project = Project::with(['divisi', 'projectSektor', 'projectType'])->findOrFail($id);

        // Data dari API WIKA tersimpan di kolom 'meta'
        $meta = $project->meta ?? [];

        return view('master.project.detail', compact('project', 'meta'));
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

        $countUpdated = 0;
        $failedProjects = []; // Array penampung project yang gagal
        $projectsBeforeUpdate = [];

        foreach ($projectDatas as $projectData) {
            // Gunakan Transaction per-project agar jika gagal, db tetap bersih
            \Illuminate\Support\Facades\DB::beginTransaction();

            try {
                $profitCenter = $projectData['profit_center'] ?? null;
                $kodeSpkRaw = (string) ($projectData['kode_spk'] ?? '-');
                $kodeSpk = strtoupper(trim($kodeSpkRaw));
                $namaSpk = $projectData['nama_spk_full'] ?? ($projectData['project_name'] ?? '-');
                $existingProject = Project::where('project_code', $kodeSpk)->first();

                $projectsBeforeUpdate[] = [
                    'project_code_raw' => $kodeSpkRaw,
                    'project_code' => $kodeSpk,
                    'project_name_api' => $namaSpk,
                    'profit_center_api' => $profitCenter,
                    'cost_center_parent_api' => $projectData['divisisap'] ?? null,
                    'api_payload' => $projectData,
                    'existing_project' => $existingProject ? [
                        'id' => $existingProject->id,
                        'project_code' => $existingProject->project_code,
                        'project_name' => $existingProject->project_name,
                        'project_status' => $existingProject->project_status,
                        'profit_center' => $existingProject->profit_center,
                        'nk' => $existingProject->nk,
                        'nilai_ok_porsi' => $existingProject->nilai_ok_porsi,
                        'batasan_biaya_perlakuan_risiko' => $existingProject->batasan_biaya_perlakuan_risiko,
                        'cost_center_parent' => $existingProject->cost_center_parent,
                        'updated_at' => optional($existingProject->updated_at)->toDateTimeString(),
                    ] : null,
                ];

                // 1. CEK UNIT / DIVISI TERLEBIH DAHULU
                $divisiUnit = null;
                $costCenterParent = $projectData['divisisap'] ?? null;

                if (!empty($costCenterParent)) {
                    $today = now()->toDateString();

                    $divisiUnit = Unit::query()
                        ->where('cost_center', $costCenterParent)
                        ->whereNull('deleted_at')
                        ->where('status', true)
                        ->where(function ($query) use ($today) {
                            // Unit aktif berdasarkan masa berlaku
                            $query->whereNull('valid_to')
                                ->orWhereDate('valid_to', '>=', $today);
                        })
                        ->first();
                }

                // Jika Unit tidak ditemukan, langsung lemparkan exception agar masuk ke blok catch
                if (!$divisiUnit) {
                    throw new \Exception("Unit/Divisi dengan Cost Center '{$costCenterParent}' tidak ditemukan di sistem.");
                }

                $nilaiKontrak = ['nk' => 0, 'nilai_ok_porsi' => 0];
                if ($profitCenter) {
                    $nilaiKontrak = $this->fetchNilaiKontrakRecursive($apiWika, $profitCenter);
                }

                $nkTotal = (float) $nilaiKontrak['nk'];

                $jenisKontrakName = empty($projectData['jenis_kontrak_name']) ? '' : (is_array($projectData['jenis_kontrak_name']) ? implode(', ', $projectData['jenis_kontrak_name']) : $projectData['jenis_kontrak_name']);
                $pembayaranName = empty($projectData['pembayaran_name']) ? '' : (is_array($projectData['pembayaran_name']) ? implode(', ', $projectData['pembayaran_name']) : $projectData['pembayaran_name']);

                $persentase = $this->hitungPersentaseBatasanBiaya($jenisKontrakName, $pembayaranName);
                // Samakan presisi dengan kolom DB numeric(20,2) agar tidak memicu audit palsu.
                $batasanBiaya = round($nkTotal * $persentase, 2);

                // Simpan / Update Project
                $project = Project::updateOrCreate([
                    'project_code' => $kodeSpk,
                ], [
                    'project_name'   => $namaSpk,
                    'type'           => Project::TYPE_HAS_RKB_RKN,
                    'project_status' => 1,
                    'profit_center'  => $profitCenter,
                    'nk'             => $nkTotal,
                    'nilai_ok_porsi' => $nilaiKontrak['nilai_ok_porsi'],
                    'batasan_biaya_perlakuan_risiko' => $batasanBiaya,
                    'cost_center_parent' => $costCenterParent,
                    'masa_pelaksanaan_start' => $projectData['tgl_mulai'] ?? null,
                    'masa_pelaksanaan_end' => $projectData['bast1'] ?? null,
                    'tanggal_mulai' => $projectData['tanggal_mulai'] ?? null,
                    'meta'           => $projectData,
                ]);

                // Update Periode List (Karena pengecekan Unit di awal, ini aman dari error Not Null)
                $projectPeriodeList = ProjectPeriodeList::updateOrCreate([
                    'project_id' => $project->id,
                    'periode_id' => null,
                ], [
                    'unit_id' => $divisiUnit->id,
                ]);

                // Recalculate Risks
                if ($projectPeriodeList) {
                    $projectPeriodeList->recalculateAllRisks();
                }

                \Illuminate\Support\Facades\DB::commit();
                $countUpdated++;

            } catch (\Exception $e) {
                // Jika terjadi error apapun (Unit null, masalah db, dll)
                \Illuminate\Support\Facades\DB::rollBack();

                $failedProjects[] = [
                    'kode' => strtoupper(trim((string) ($projectData['kode_spk'] ?? '-'))),
                    'nama' => $projectData['nama_spk_full'] ?? ($projectData['project_name'] ?? '-'),
                    'alasan' => $e->getMessage()
                ];
            }
        }

        $projectCodesFromApi = collect($projectsBeforeUpdate)
            ->pluck('project_code')
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'success' => true,
            'count_updated' => $countUpdated,
            'count_failed' => count($failedProjects),
            'failed_list' => $failedProjects,
            'project_codes_from_api' => $projectCodesFromApi,
            'projects_before_update' => $projectsBeforeUpdate,
            'message' => "Sinkronisasi Selesai."
        ]);
    }

    private function fetchNilaiKontrak($apiWika, $profitCenter, $period)
    {
        try {
            $response = $apiWika->getHasilUsahaProject($period, $profitCenter);

            if (isset($response['status']) &&
                $response['status'] &&
                isset($response['data']['hasil_usaha']['kontrak_review_total'])) {

                return (float) $response['data']['hasil_usaha']['kontrak_review_total'];
            }
        } catch (\Exception $e) {
            return 0;
        }

        return 0;
    }

    private function fetchNilaiKontrakRecursive($apiWika, $profitCenter)
    {
        $dateCheck = Carbon::now();
        $maxRetries = 10;

        for ($i = 0; $i < $maxRetries; $i++) {
            $currentPeriod = $dateCheck->format('Ym');

            try {
                $response = $apiWika->getHasilUsahaProject($currentPeriod, $profitCenter);

                if (isset($response['status']) && $response['status'] && isset($response['data'])) {

                    $data = $response['data'];
                    $statusAutorisasi = $data['status_autorisasi'] ?? 'OPEN';
                    $kontrakReviewTotal = $data['hasil_usaha']['kontrak_review_total'] ?? 0;
                    $kontrakReviewPorsi = $data['hasil_usaha']['kontrak_review'] ?? 0;

                    if ($statusAutorisasi === 'AUTORISASI' && ($kontrakReviewTotal !== 0 || $kontrakReviewPorsi !== 0)) {
                        return [
                            'nk' => (float) $kontrakReviewTotal,
                            'nilai_ok_porsi' => (float) $kontrakReviewPorsi,
                        ];
                    }
                }

            } catch (\Exception $e) {
            }

            $dateCheck->subMonth();
        }

        return [
            'nk' => 0,
            'nilai_ok_porsi' => 0
        ];
    }

    private function hitungPersentaseBatasanBiaya($jenisKontrak, $caraPembayaran)
    {
        // Normalisasi input ke huruf kecil agar mudah dicocokkan
        $kontrak = strtolower(trim($jenisKontrak));
        $bayar = strtolower(trim($caraPembayaran));

        $persentase = 0.0;

        // Matriks berdasarkan tabel:
        // Baris: Lumpsum, Mix, Cost-Plus, O & M, Unit Price
        // Kolom: Monthly, Milestone, CPF (Turn Key)

        // Logika Lumpsum
        if (str_contains($kontrak, 'lumpsum') || str_contains($kontrak, 'lump sum')) {
            if (str_contains($bayar, 'monthly')) $persentase = 1.0;
            elseif (str_contains($bayar, 'milestone')) $persentase = 1.25;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 1.50;
        }
        // Logika Mix (Gabungan)
        elseif (str_contains($kontrak, 'mix') || str_contains($kontrak, 'gabungan')) {
            if (str_contains($bayar, 'monthly')) $persentase = 0.75;
            elseif (str_contains($bayar, 'milestone')) $persentase = 1.0;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 1.25;
        }
        // Logika Cost-Plus
        elseif (str_contains($kontrak, 'cost-plus') || str_contains($kontrak, 'cost plus')) {
            if (str_contains($bayar, 'monthly')) $persentase = 0.13;
            elseif (str_contains($bayar, 'milestone')) $persentase = 0.25;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 0.50;
        }
        // Logika O & M
        elseif (str_contains($kontrak, 'o & m') || str_contains($kontrak, 'o&m') || str_contains($kontrak, 'operasional')) {
            if (str_contains($bayar, 'monthly')) $persentase = 0.25;
            elseif (str_contains($bayar, 'milestone')) $persentase = 0.50;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 0.75;
        }
        // Logika Unit Price
        elseif (str_contains($kontrak, 'unit price') || str_contains($kontrak, 'harga satuan')) {
            if (str_contains($bayar, 'monthly')) $persentase = 0.25;
            elseif (str_contains($bayar, 'milestone')) $persentase = 0.50;
            elseif (str_contains($bayar, 'cpf') || str_contains($bayar, 'turn key') || str_contains($bayar, 'turnkey')) $persentase = 0.75;
        }

        return $persentase / 100; // Mengubah misalnya 1.25 menjadi 0.0125 untuk pengali
    }

    public function logs(Request $request)
    {
        abort_unless(Gate::allows('project_list'), 403);

        $event = $request->query('event');
        $projectId = $request->query('project_id');

        $projects = Project::query()
            ->orderBy('project_name')
            ->get(['id', 'project_code', 'project_name']);

        $audits = Audit::query()
            ->with(['user', 'auditable'])
            ->where('auditable_type', Project::class)
            ->when($event, function ($query) use ($event) {
                $query->where('event', $event);
            })
            ->when($projectId, function ($query) use ($projectId) {
                $query->where('auditable_id', $projectId);
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('master.project.logs', compact('audits', 'projects', 'event', 'projectId'));
    }

    public function logsByProject(Request $request, Project $project)
    {
        $request->merge(['project_id' => $project->id]);

        return $this->logs($request);
    }
}
