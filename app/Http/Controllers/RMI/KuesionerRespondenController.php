<?php

namespace App\Http\Controllers\RMI;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\KuesionerResponden;
use Illuminate\Http\Request;

class KuesionerRespondenController extends BasicCRUDController
{
    protected $model = KuesionerResponden::class;
    protected $resourceName = 'Responden Kuisioner';
    protected $baseRoute = 'kuesioner-responden.';

    public function __construct()
    {
        $this->middleware('can:kuesioner_responden');

        if (!$this->baseViewPath) {
            $this->baseViewPath = 'master.basic-crud';
        }

        if (!$this->baseRoute) {
            $this->baseRoute = str_replace('_', '-', (new $this->model())->getTable()) . '.';
        }

        if (!$this->resourceName) {
            $this->resourceName = ucwords(\Illuminate\Support\Str::singular(str_replace('_', ' ', (new $this->model())->getTable())));
        }
    }

    public function index()
    {
        $this->callbackQuery = function($query) {
            return $query->with(['rmiPeriod', 'group', 'approver', 'userSurvey']);
        };

        $this->datatableCallback = function($datatable) {
            $datatable->addColumn('periode_name', function($row) {
                return $row->rmiPeriod?->year ?? '-';
            });
        };

        $showRoute = route('kuesioner-responden.show', ':id');
        $approveRoute = route('kuesioner-responden.approve', ':id');
        $rejectRoute = route('kuesioner-responden.reject', ':id');
        $resetRoute = route('kuesioner-responden.reset', ':id');

        $this->tableColumns = [
            'periode_name' => [
                'label' => 'Periode RMI',
                'searchable' => false,
                'data' => 'periode_name',
            ],
            'name' => [
                'label' => 'Nama Responden',
                'data' => 'name',
            ],
            'email' => [
                'label' => 'Email',
                'data' => 'email',
            ],
            'group' => [
                'label' => 'Group',
                'searchable' => false,
                'data' => 'group.name',
                'render' => '(data) => data ? data : "-"',
            ],
            'verified_at' => [
                'label' => 'Status Verifikasi',
                'searchable' => false,
                'data' => 'verified_at',
                'render' => <<<JS
                    (data) => data ? '<span class="badge bg-success">Terverifikasi</span>' : '<span class="badge bg-warning">Belum Terverifikasi</span>';
                JS,
            ],
            'approval_status' => [
                'label' => 'Status Approval',
                'searchable' => false,
                'data' => 'approval_status',
                'render' => <<<JS
                    (data) => {
                        if (data === 'approved') {
                            return '<span class="badge bg-success">Disetujui</span>';
                        } else if (data === 'rejected') {
                            return '<span class="badge bg-danger">Ditolak</span>';
                        } else {
                            return '<span class="badge bg-warning">Pending</span>';
                        }
                    };
                JS,
            ],
            'approved_by' => [
                'label' => 'Diterima/Tolak Oleh',
                'searchable' => false,
                'data' => 'approver.name',
                'render' => '(data) => data ? data : "-"',
            ],
            'survey_status' => [
                'label' => 'Status Survey',
                'searchable' => false,
                'orderable' => false,
                'data' => 'survey_status',
                'render' => <<<JS
                    (data, type, row) => {
                        if (row.user_survey?.status == 1) {
                            return '<span class="badge bg-success">Selesai</span>';
                        } else {
                            return '<span class="badge bg-warning">Pending</span>';
                        }
                    };
                JS,
            ],
            'action' => [
                'label' => 'Action',
                'searchable' => false,
                'orderable' => false,
                'data' => 'action',
                'render' => <<<JS
                    function(data, type, row) {
                        let actions = '';
                        
                        if (row.verified_at) {
                            if (row.approval_status !== 'approved') {
                                actions += '<button class="btn-input-icon" data-bs-toggle="tooltip" title="" data-bs-original-title="Terima" onclick="approveResponden(' + row.id + ')"><span class="bx bx-check text-success"></span></button>';
                            }
                            if (row.approval_status !== 'rejected') {
                                actions += '<button class="btn-input-icon" data-bs-toggle="tooltip" title="" data-bs-original-title="Tolak" onclick="rejectResponden(' + row.id + ')"><span class="bx bx-x text-danger"></span></button>';
                            }
                        }

                        if (row.user_survey?.status == 1) {
                            showRoute = "{$showRoute}".replace(':id', row.id);
                            actions += '<a class="btn-input-icon" data-bs-toggle="tooltip" title="" data-bs-original-title="Lihat Jawaban" href="' + showRoute + '"><span class="bx bx-show"></span></a><button class="btn-input-icon" data-bs-toggle="tooltip" title="" data-bs-original-title="Reset Status" onclick="resetResponden(' + row.id + ')"><span class="bx bx-refresh"></span></button>';
                        }
                        
                        return actions;
                    }
                JS,
            ],
        ];

        $approveRoute = route('kuesioner-responden.approve', ':id');
        $rejectRoute = route('kuesioner-responden.reject', ':id');

        $this->extraScripts = [
            <<<HTML
            <script>
            function approveResponden(id) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Anda akan menyetujui responden kuesioner ini.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, setujui!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: (modal) => {
                                Swal.showLoading();
                            }
                        });
                        fetch("{$approveRoute}".replace(":id", id), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.close();
                            if (data.message) {
                                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                    $('.ajax-datatable').DataTable().ajax.reload();
                                });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire('Error!', 'Terjadi kesalahan saat memproses permintaan.', 'error');
                        });
                    }
                });
            }
            function rejectResponden(id) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Anda akan menolak responden kuesioner ini.",
                    icon: 'warning',
                    input: 'textarea',
                    inputLabel: 'Catatan Penolakan',
                    inputPlaceholder: 'Masukkan alasan penolakan (opsional)',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, tolak!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: (modal) => {
                                Swal.showLoading();
                            }
                        });
                        fetch("{$rejectRoute}".replace(":id", id), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                rejection_notes: result.value
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.close();
                            if (data.message) {
                                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                    $('.ajax-datatable').DataTable().ajax.reload();
                                });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire('Error!', 'Terjadi kesalahan saat memproses permintaan.', 'error');
                        });
                    }
                });
            }
            function resetResponden(id) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Anda akan mereset status pengisian kuesioner responden ini.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, reset!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: (modal) => {
                                Swal.showLoading();
                            }
                        });
                        
                        fetch("{$resetRoute}".replace(":id", id), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.close();
                            if (data.message) {
                                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                                    $('.ajax-datatable').DataTable().ajax.reload();
                                });
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire('Error!', 'Terjadi kesalahan saat memproses permintaan.', 'error');
                        });
                    }
                });
            }
            </script>
            HTML,
        ];

        return parent::index();
    }

    public function show($resource)
    {
        $user = KuesionerResponden::findOrFail($resource);
        $periode = $user->rmiPeriod;
        $periode->load([
            'periodQuestions' => function($query) use ($user) {
                $query->with(['question' => function($query) use ($user) {
                    $query->with('answerChoices');
                }])->whereHas('question', function($query) use ($user) {
                    $query->where('group_id', $user->group_id);
                });
            },
            'userSurvey' => function($query) use ($user) {
                $query->where('kuesioner_responden_id', $user->id)->with('userAnswers');
            }
        ]);
        $questions = $periode->periodQuestions;
        $userSurvey = $periode->userSurvey;
        $answers = [];
        if ($userSurvey && $userSurvey->userAnswers) {
            foreach ($userSurvey->userAnswers as $answer) {
                $answers[$answer->period_question_id] = $answer->level;
            }
        }
        if ($userSurvey?->status == 0) {
            return redirect()->back()->with('error', 'Kuesioner belum selesai diisi.');
        }
        $type = 'responden';
        return view('kuesioner.show', compact('periode', 'questions', 'answers', 'type'));
    }

    public function approve($resource)
    {
        $responden = $this->model::findOrFail($resource);

        $responden->approve(auth()->user()->id);

        return response()->json([
            'message' => 'Responden kuesioner berhasil diterima.',
        ]);
    }

    public function reject(Request $request, $resource)
    {
        $request->validate([
            'rejection_notes' => 'nullable|string',
        ]);

        $responden = $this->model::findOrFail($resource);

        $responden->reject(auth()->user()->id, $request->input('rejection_notes'));
        
        return response()->json([
            'message' => 'Responden kuesioner berhasil ditolak.',
        ]);
    }

    public function reset($resource)
    {
        $responden = $this->model::findOrFail($resource);

        $responden->userSurvey()->update(['status' => 0]);

        return response()->json([
            'message' => 'Status pengisian kuesioner responden berhasil direset.',
        ]);
    }
}