<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Master\BasicCRUDController;
use App\Models\ProjectRisk;
use App\Models\ProjectRiskMonitoringDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProjectRiskMonitoringDocumentController extends BasicCRUDController
{
    protected $model = ProjectRiskMonitoringDocument::class;
    protected $basePermission = 'project_monitoring_document';
    protected $resourceName = 'Dokumen Monitoring Risiko';
    protected $baseRoute = 'projects.monitorings.documents.';

    protected $tableColumns = [
        'created_at' => [
            'label' => 'Tanggal Unggah',
            'data' => 'created_at',
            'render' => '(data) => data ? Intl.DateTimeFormat("id-ID", { dateStyle: "full", timeStyle: "short" }).format(new Date(data)) : "-"',
        ],
        'user_id' => [
            'label' => 'User',
            'data' => 'user.name',
        ],
        'file_name' => [
            'label' => 'Nama Dokumen',
            'data' => 'file_name',
        ],
        'type' => [
            'label' => 'Jenis',
            'data' => 'mimetype',
        ],
        'description' => [
            'label' => 'Deskripsi',
            'data' => 'description',
            'orderable' => false,
            'searchable' => false,
        ],
    ];


    public function index() {
        $this->baseRouteParams = [
            'quarter' => request()->route('quarter'),
            'monitoring' => request()->route('monitoring'),
        ];
        $this->callbackQuery = function($query) {
            $query->with('user')
                ->where('quarter', request()->route('quarter'))
                ->where('project_risk_id', request()->route('monitoring'));
        };

        if (!request()->ajax()) {
            $projectRisk = ProjectRisk::findOrFail(request()->route('monitoring'));
            $this->indexTitle = 'Dokumen ' . ($projectRisk->peristiwaRisiko?->title) . '<br/><small>Quarter ' . request()->route('quarter') . '</small>';
        }

        $this->createFields = [
            [
                'name' => 'file',
                'type' => 'file',
                'label' => 'Dokumen',
                'parameters' => [
                    'file',
                    [
                        'class' => 'form-control',
                        'placeholder' => 'Pilih Dokumen',
                        'required' => true,
                    ],
                ],
            ],
            [
                'name' => 'description',
                'type' => 'textarea',
                'label' => 'Deskripsi',
                'parameters' => [
                    'description',
                    '',
                    [
                        'class' => 'form-control',
                        'rows' => 3,
                    ]
                ],
            ],
        ];

        if (Gate::check('project_monitoring_document_view')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-download text-success"></span>',
                'btn_icon' => true,
                'action' => 'link',
                'url' => route('projects.monitorings.documents.show', ['document' => ':id', 'quarter' => request()->route('quarter'), 'monitoring' => request()->route('monitoring')]),
            ];
        }

        if (Gate::check('project_monitoring_document_delete')) {
            $this->tableActions[] = [
                'label' => '<span class="bx bx-trash text-danger"></span>',
                'btn_icon' => true,
                'action' => 'delete',
            ];
        }

        return parent::index();
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'description' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('project_risk_monitoring_documents');

        $data = $request->only(['description']);
        $data['file_name'] = $fileName;
        $data['file_path'] = $filePath;
        $data['mimetype'] = $file->getMimeType();
        $data['quarter'] = request()->route('quarter');
        $data['project_risk_id'] = request()->route('monitoring');
        $data['user_id'] = auth()->id();

        $document = ProjectRiskMonitoringDocument::create($data);

        return response()->json([
            'message' => 'Dokumen berhasil diunggah',
            'data' => $document,
        ]);
    }

    public function show($resource)
    {
        $document = ProjectRiskMonitoringDocument::findOrfail(request()->route('document'));

        $fileContent = Storage::get($document->file_path);
        return response($fileContent)
            ->header('Content-Type', $document->mimetype)
            ->header('Content-Disposition', 'attachment; filename="' . $document->file_name . '"');
    }

    public function destroy($resource)
    {
        $document = ProjectRiskMonitoringDocument::findOrfail(request()->route('document'));
        $document->delete();

        return response()->json(['message' => 'Dokumen berhasil dihapus']);
    }
}
