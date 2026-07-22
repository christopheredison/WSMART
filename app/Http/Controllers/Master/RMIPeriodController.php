<?php

namespace App\Http\Controllers\Master;

use App\Models\Group;
use App\Models\PeriodQuestion;
use App\Models\Question;
use App\Models\RMIPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RMIPeriodController extends BasicCRUDController
{
    protected $model = RMIPeriod::class;
    protected $basePermission = 'rmi_period';
    protected $resourceName = 'Periode RMI';
    protected $baseRoute = 'rmi-period.';

    protected $createFields = [
        [
            'name' => 'year',
            'type' => 'number',
            'label' => 'Tahun',
            'parameters' => [
                'year',
                '',
                [
                    'class' => 'form-control',
                    'placeholder' => 'Masukkan Tahun',
                    'required' => true,
                ]
            ],
        ],
        [
            'name' => 'start_date',
            'type' => 'date',
            'label' => 'Tanggal Mulai',
            'parameters' => [
                'start_date',
                '',
                [
                    'class' => 'form-control',
                    'required' => true,
                ]
            ],
        ],
        [
            'name' => 'end_date',
            'type' => 'date',
            'label' => 'Tanggal Berakhir',
            'parameters' => [
                'end_date',
                '',
                [
                    'class' => 'form-control',
                    'required' => true,
                ]
            ],
        ],
    ];
    
    protected $editFields = [
        [
            'name' => 'year',
            'type' => 'number',
            'label' => 'Tahun',
            'parameters' => [
                'year',
                '',
                [
                    'class' => 'form-control',
                    'placeholder' => 'Masukkan Tahun',
                    'required' => true,
                ]
            ],
        ],
        [
            'name' => 'start_date',
            'type' => 'date',
            'label' => 'Tanggal Mulai',
            'parameters' => [
                'start_date',
                '',
                [
                    'class' => 'form-control',
                    'required' => true,
                ]
            ],
        ],
        [
            'name' => 'end_date',
            'type' => 'date',
            'label' => 'Tanggal Berakhir',
            'parameters' => [
                'end_date',
                '',
                [
                    'class' => 'form-control',
                    'required' => true,
                ]
            ],
        ],
    ];

    public function index()
    {
        $this->callbackQuery = function($query) {
            return $query->select('rmi_periods.*', DB::raw('0 as token'))->withCount('periodQuestions');
        };

        $this->editFields = $this->createFields;

        $this->tableColumns = [
            'year' => [
                'label' => 'Periode RMI',
                'data' => 'year',
            ],
            'question_count' => [
                'label' => 'Jumlah Pertanyaan Kuisioner',
                'data' => 'period_questions_count',
                'render' => '(data) => data ? data : "-"',
            ],
            'score_rmi' => [
                'label' => 'Score RMI',
                'data' => 'score_rmi',
                'render' => '(data) => data ? data : "-"',
            ],
            'score_rmi_desc' => [
                'label' => 'Score RMI Desc',
                'data' => 'score_rmi_desc',
                'render' => '(data) => data ? data : "-"',
            ],
            'start_date' => [
                'label' => 'Tanggal Mulai',
                'data' => 'start_date',
                'render' => <<<JS
                    function(data, type, row) {
                        if (type !== 'display') {
                            return data;
                        }
                        return data ? Intl.DateTimeFormat('id-ID', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit'
                        }).format(new Date(data)) : '-'
                    }
                JS,
            ],
            'end_date' => [
                'label' => 'Tanggal Berakhir',
                'data' => 'end_date',
                'render' => <<<JS
                    function(data, type, row) {
                        if (type !== 'display') {
                            return data;
                        }
                        return data ? Intl.DateTimeFormat('id-ID', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit'
                        }).format(new Date(data)) : '-'
                    }
                JS,
            ],
        ];

        if (Gate::check('rmi_period_view')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-show"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('rmi-period.show', ['rmi_period' => ':id']),
            ];
        }

        if (Gate::check('rmi_period_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
                'permissions' => ['rmi_period_edit'],
            ];
        }

        if (Gate::check('rmi_period_view')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-question-mark"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('rmi-period.question', ['id' => ':id']),
                'permissions' => ['rmi_period_view'],
            ];
            $this->tableActions[] = [
                'label' => '<span class="bx bx-globe"></span>',
                'btn_icon' => true,
                'action' => 'script',
                'permissions' => ['rmi_period_view'],
                'script' => 'const url = "' . route('kuesioner-publik.register', ':token') . '".replace(":token", "${row.token}"); navigator.clipboard.writeText(url); Swal.fire("URL Pendaftaran Pengisian Kuisioner", \'<input type="text" class="form-control" value="\' + url + \'" readonly><br>URL telah disalin ke clipboard.<br>Atau <a href="\' + url + \'" target="_blank">klik disini</a> untuk membukanya sekarang.\', "info");',
            ];
        }

        if (Gate::check('rmi_period_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash"></span>',
                'btn_icon' => true,
                'action' => 'delete',
                'permissions' => ['rmi_period_delete'],
            ];
        }

        return parent::index();
    }

    public function question($id)
    {
        $period = RMIPeriod::with('periodQuestions')->findOrFail($id);
        $groups = Group::with('questions.answerChoices', 'questions.measurementParameter')->get()->keyBy('id');
        return view('master.rmi-period.question', compact('period', 'groups'));
    }

    public function updateQuestion($id, Request $request)
    {
        $rmiPeriod = RMIPeriod::findOrFail($id);
        $questionIds = array_unique($request->input('questions'));
        foreach ($questionIds as $questionId) {
            PeriodQuestion::updateOrCreate([
                'period_id' => $rmiPeriod->id,
                'question_id' => $questionId,
            ]);
        }
        PeriodQuestion::whereNotIn('question_id', $questionIds)->where('period_id', $rmiPeriod->id)->delete();
        return redirect()->route('rmi-period.question', ['id' => $id])->with('success', 'Pertanyaan berhasil diubah');
    }

    public function show($resource)
    {
        $rmiPeriod = RMIPeriod::with('periodQuestions.question')->findOrFail($resource);
        return view('master.rmi-period.show', compact('rmiPeriod'));
    }
}
