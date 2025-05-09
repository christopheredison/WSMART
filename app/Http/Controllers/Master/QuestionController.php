<?php

namespace App\Http\Controllers\Master;

use App\Models\Group;
use App\Models\MeasurementParameter;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuestionController extends BasicCRUDController
{
    protected $model = Question::class;
    protected $basePermission = 'question';
    protected $resourceName = 'Pertanyaan Survey';
    protected $baseRoute = 'question.';

    public function index()
    {
        $this->callbackQuery = function ($query) {
            $query->with('answerChoices', 'measurementParameter')
                ->select('questions.*');
        };

        $this->datatableCallback = function ($datatable) {
            $datatable->addColumn('answer_1', function ($row) {
                return $row->answerChoices->where('level', 1)->first()?->answer;
            });
            $datatable->addColumn('answer_2', function ($row) {
                return $row->answerChoices->where('level', 2)->first()?->answer;
            });
            $datatable->addColumn('answer_3', function ($row) {
                return $row->answerChoices->where('level', 3)->first()?->answer;
            });
            $datatable->addColumn('answer_4', function ($row) {
                return $row->answerChoices->where('level', 4)->first()?->answer;
            });
            $datatable->addColumn('answer_5', function ($row) {
                return $row->answerChoices->where('level', 5)->first()?->answer;
            });
        };

        $groupOptions = Group::select('id', 'name')->orderBy('name')->get()->pluck('name', 'id')->toArray();
        $this->createFields = [
            [
                'name' => 'group_id',
                'type' => 'select',
                'label' => 'Kelompok',
                'parameters' => [
                    'group_id',
                    $groupOptions,
                    '',
                    [
                        'class' => 'form-select',
                        'required' => true,
                    ],
                ],
            ],
            [
                'name' => 'parameter_id',
                'type' => 'select',
                'label' => 'Parameter',
                'parameters' => [
                    'parameter_id',
                    MeasurementParameter::select('id', 'statement')->pluck('statement', 'id'),
                    '',
                    [
                        'class' => 'form-select',
                        'required' => true,
                    ],
                ],
            ],
            [
                'name' => 'question',
                'type' => 'textarea',
                'label' => 'Pertanyaan',
                'parameters' => [
                    'question',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Pertanyaan',
                        'required' => true,
                        'rows' => 3,
                    ]
                ],
            ],
            [
                'name' => 'answer_1',
                'type' => 'textarea',
                'label' => 'Jawaban 1',
                'parameters' => [
                    'answer_1',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Jawaban 1',
                        'required' => true,
                        'rows' => 3,
                    ]
                ],
            ],
            [
                'name' => 'answer_2', 
                'type' => 'textarea',
                'label' => 'Jawaban 2',
                'parameters' => [
                    'answer_2',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Jawaban 2',
                        'required' => true,
                        'rows' => 3,
                    ]
                ],
            ],
            [
                'name' => 'answer_3',
                'type' => 'textarea', 
                'label' => 'Jawaban 3',
                'parameters' => [
                    'answer_3',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Jawaban 3',
                        'required' => true,
                        'rows' => 3,
                    ]
                ],
            ],
            [
                'name' => 'answer_4',
                'type' => 'textarea',
                'label' => 'Jawaban 4',
                'parameters' => [
                    'answer_4',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Jawaban 4',
                        'required' => true,
                        'rows' => 3,
                    ]
                ],
            ],
            [
                'name' => 'answer_5',
                'type' => 'textarea',
                'label' => 'Jawaban 5',
                'parameters' => [
                    'answer_5',
                    '',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Masukkan Jawaban 5',
                        'required' => true,
                        'rows' => 3,
                    ]
                ],
            ]
        ];
        
        $this->editFields = $this->createFields;

        $this->availableFilters = [
            'group_id' => [
                'label' => 'Kelompok',
                'type' => 'select',
                'parameters' => [
                    'group_id',
                    $groupOptions,
                    '',
                    [
                        'class' => 'form-select',
                    ]
                ],
            ],
        ];

        $this->tableColumns = [
            'question' => [
                'label' => 'Question',
                'data' => 'question',
            ],
            'answer_1' => [
                'label' => 'Jawaban 1',
                'data' => 'answer_1',
                'searchable' => false,
                'orderable' => false,
                'render' => <<<JS
                    function (data, type, row) {
                        return row.answer_choices.find(choice => choice.level == 1)?.answer || '-';
                    }
                JS,
            ],
            'answer_2' => [
                'label' => 'Jawaban 2', 
                'data' => 'answer_2',
                'searchable' => false,
                'orderable' => false,
                'render' => <<<JS
                    function (data, type, row) {
                        return row.answer_choices.find(choice => choice.level == 2)?.answer || '-';
                    }
                JS,
            ],
            'answer_3' => [
                'label' => 'Jawaban 3',
                'data' => 'answer_3', 
                'searchable' => false,
                'orderable' => false,
                'render' => <<<JS
                    function (data, type, row) {
                        return row.answer_choices.find(choice => choice.level == 3)?.answer || '-';
                    }
                JS,
            ],
            'answer_4' => [
                'label' => 'Jawaban 4',
                'data' => 'answer_4',
                'searchable' => false,
                'orderable' => false,
                'render' => <<<JS
                    function (data, type, row) {
                        return row.answer_choices.find(choice => choice.level == 4)?.answer || '-';
                    }
                JS,
            ],
            'answer_5' => [
                'label' => 'Jawaban 5',
                'data' => 'answer_5',
                'searchable' => false,
                'orderable' => false,
                'render' => <<<JS
                    function (data, type, row) {
                        return row.answer_choices.find(choice => choice.level == 5)?.answer || '-';
                    }
                JS,
            ],
            'parameter' => [
                'label' => 'Parameter',
                'data' => 'measurementParameter.statement',
                'searchable' => false,
                'orderable' => false,
                'render' => <<<JS
                    function (data, type, row) {
                        return row.measurement_parameter?.statement || '-';
                    }
                JS,
            ],
        ];

        if (Gate::check('question_view')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-show"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('question.show', ['question' => ':id']),
            ];
        }

        if (Gate::check('question_edit')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-edit"></span>',
                'btn_icon' => true,
                'action' => 'edit',
                'permissions' => ['question_edit'],
            ];
        }

        if (Gate::check('question_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash"></span>',
                'btn_icon' => true,
                'action' => 'delete',
                'permissions' => ['question_delete'],
            ];
        }

        return parent::index();
    }

    public function store(Request $request)
    {
        $result = parent::store($request);
        for ($i = 1; $i <= 5; $i++) {
            $result->answerChoices()->create([
                'level' => $i,
                'answer' => $request->input("answer_{$i}"),
            ]);
        }
        return $result;
    }

    public function update(Request $request, $id)
    {
        $result = parent::update($request, $id);
        for ($i = 1; $i <= 5; $i++) {
            $result->answerChoices()->updateOrCreate([
                'level' => $i,
            ], [
                'answer' => $request->input("answer_{$i}"),
            ]);
        }
        return $result;
    }
    
    public function show($id)
    {
        $question = Question::with(['group', 'measurementParameter', 'answerChoices'])
            ->findOrFail($id);
        return view('master.question.show', compact('question'));
    }
}
