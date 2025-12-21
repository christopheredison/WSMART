<?php

namespace App\Http\Controllers\RMI;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\RMIPeriod;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

class KuesionerController extends BasicCRUDController
{
    protected $model = RMIPeriod::class;
    protected $resourceName = 'Kuesioner RMI';
    protected $baseRoute = 'kuesioner.';

    public function __construct()
    {
        $this->middleware('can:kuesioner');

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
            return $query->with(['userSurvey' => function($query) {
                $query->where('user_id', request()->user()->id)
                    ->with('userAnswers');
            }]);
        };

        $this->datatableCallback = function($datatable) {
            $datatable->addColumn('count_answer_1', function($row) {
                return $row->userSurvey?->userAnswers->where('level', 1)->count() ?? '-';
            });

            $datatable->addColumn('count_answer_2', function($row) {
                return $row->userSurvey?->userAnswers->where('level', 2)->count() ?? '-';
            });

            $datatable->addColumn('count_answer_3', function($row) {
                return $row->userSurvey?->userAnswers->where('level', 3)->count() ?? '-';
            });

            $datatable->addColumn('count_answer_4', function($row) {
                return $row->userSurvey?->userAnswers->where('level', 4)->count() ?? '-';
            });

            $datatable->addColumn('count_answer_5', function($row) {
                return $row->userSurvey?->userAnswers->where('level', 5)->count() ?? '-';
            });
        };

        $showRoute = route('kuesioner.show', ':id');
        $editRoute = route('kuesioner.edit', ':id');

        $this->tableColumns = [
            'year' => [
                'label' => 'Periode RMI',
                'data' => 'year',
            ],
            'status' => [
                'label' => 'Status Kuesioner',
                'searchable' => false,
                'data' => 'userSurvey.status',
                'render' => <<<JS
                    (data, type, row) => row.user_survey?.status === null || row.user_survey?.status === undefined ? 'Belum Mengisi' : (row.user_survey.status == 1 ? 'Sudah Mengisi' : 'Dalam Proses');
                JS,
            ],
            'count_answer_1' => [
                'label' => 'Jumlah Jawaban 1',
                'searchable' => false,
                'orderable' => false,
                'data' => 'count_answer_1',
                'render' => '(data) => data ? data : "-"',
            ],
            'count_answer_2' => [
                'label' => 'Jumlah Jawaban 2', 
                'searchable' => false,
                'orderable' => false,
                'data' => 'count_answer_2',
                'render' => '(data) => data ? data : "-"',
            ],
            'count_answer_3' => [
                'label' => 'Jumlah Jawaban 3',
                'searchable' => false,
                'orderable' => false,
                'data' => 'count_answer_3', 
                'render' => '(data) => data ? data : "-"',
            ],
            'count_answer_4' => [
                'label' => 'Jumlah Jawaban 4',
                'searchable' => false,
                'orderable' => false,
                'data' => 'count_answer_4',
                'render' => '(data) => data ? data : "-"',
            ],
            'count_answer_5' => [
                'label' => 'Jumlah Jawaban 5',
                'searchable' => false,
                'orderable' => false,
                'data' => 'count_answer_5',
                'render' => '(data) => data ? data : "-"',
            ],
            'tanggal_pengisian' => [
                'label' => 'Tanggal Pengisian',
                'searchable' => false,
                'data' => 'userSurvey.updated_at',
                'render' => <<<JS
                    (data, type, row) => row.user_survey?.updated_at === null || row.user_survey?.updated_at === undefined ? '-' : Intl.DateTimeFormat('id-ID', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    }).format(new Date(row.user_survey.updated_at));
                JS,
            ],
            'action' => [
                'label' => 'Action',
                'searchable' => false,
                'orderable' => false,
                'data' => 'action',
                'render' => <<<JS
                    function(data, type, row) {
                        if (row.user_survey?.status) {
                            return '<a href="' + '$showRoute'.replace(':id', row.id) + '">Lihat</a>';
                        }
                        return '<a href="' + '$editRoute'.replace(':id', row.id) + '">Isi Survey</a>';
                    }
                JS,
            ],
        ];

        return parent::index();
    }

    public function show($resource)
    {
        $user = request()->user();
        $periode = $this->model::findOrFail($resource);
        $periode->load([
            'periodQuestions' => function($query) use ($user) {
                $query->with(['question' => function($query) use ($user) {
                    $query->with('answerChoices');
                }])->whereHas('question', function($query) use ($user) {
                    $query->where('group_id', $user->group_id);
                });
            },
            'userSurvey' => function($query) use ($user) {
                $query->where('user_id', $user->id)->with('userAnswers');
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
            return redirect()->route('kuesioner.edit', $periode->id);
        }
        return view('kuesioner.show', compact('periode', 'questions', 'answers'));
    }

    public function edit($resource)
    {
        $user = request()->user();
        $user->load('group');
        $periode = $this->model::findOrFail($resource);
        $periode->load(['periodQuestions' => function($query) use ($user) {
            $query->with(['question' => function($query) use ($user) {
                $query->where('group_id', $user->group_id);
                $query->with('answerChoices');
            }])->whereHas('question', function($query) use ($user) {
                $query->where('group_id', $user->group_id);
            });
        },'userSurvey' => function($query) use ($user) {
            $query->where('user_id', $user->id);
        }]);

        if ($periode->userSurvey?->status == 1) {
            return redirect()->route('kuesioner.show', $periode->id);
        }

        $questions = $periode->periodQuestions;
        $currentAnswers = $periode->userSurvey?->userAnswers->pluck('level', 'period_question_id');
        return view('kuesioner.edit', compact('periode', 'questions', 'currentAnswers'));
    }

    public function update(Request $request, $resource)
    {
        $user = $request->user();
        $periode = $this->model::with([
            'userSurvey' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }, 
            'periodQuestions' => function($query) use ($user) {
                $query->whereHas('question', function($query) use ($user) {
                    $query->where('group_id', $user->group_id);
                })->with('question.answerChoices');
            }
        ])->findOrFail($resource);
        $answers = $request->answers;
        $userSurvey = $periode->userSurvey;

        if (!$userSurvey) {
            $userSurvey = $periode->userSurvey()->create([
                'user_id' => request()->user()->id,
                'status' => '0',
            ]);
        }

        if ($userSurvey->status == 1) {
            return response()->json([
                'message' => 'Kuesioner sudah selesai diisi',
            ], 400);
        }

        foreach ($answers ?? [] as $questionId => $answer) {
            $periodeQuestion = $periode->periodQuestions->where('id', $questionId)->first();

            if (!$periodeQuestion) {
                continue;
            }

            UserAnswer::updateOrCreate([
                'period_question_id' => $periodeQuestion->id,
                'user_survey_id' => $userSurvey->id,
            ], [
                'answer_choice_id' => $periodeQuestion->question->answerChoices->where('level', $answer)->first()->id,
                'level' => $answer,
                'user_id' => $user->id,
                'question_notes' => null,
                'answer_notes' => null,
            ]);
        }

        if ($request->complete) {
            $userSurvey->update([
                'status' => '1',
            ]);
        }

        return response()->json([
            'message' => 'Kuesioner berhasil disimpan',
        ]);
    }
}
