<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ProjectHasilUsaha;
use App\Services\ProjectHasilUsahaSyncService;
use Illuminate\Http\Request;
use DataTables;

class ProjectHasilUsahaController extends Controller
{
    public function __construct(
        private readonly ProjectHasilUsahaSyncService $syncService
    ) {
    }

    public function index()
    {
        return view('master.project-hasil-usaha.index');
    }

    public function data()
    {
        $query = ProjectHasilUsaha::with('project');
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('project_name', fn($row) => $row->project->project_name ?? '-')
            ->addColumn('period', fn($row) => $row->period ? preg_replace('/(\d{4})(\d{2})/', '$1-$2', $row->period) : '-')
            ->editColumn('kontrak_review_total', fn($row) => 'Rp ' . number_format($row->kontrak_review_total, 0, ',', '.'))
            ->editColumn('kontrak_review', fn($row) => 'Rp ' . number_format($row->kontrak_review, 0, ',', '.'))
            ->editColumn('lsp_review', fn($row) => 'Rp ' . number_format($row->lsp_review ?? 0, 0, ',', '.'))
            ->addColumn('action', function($row){
                $editBtn = '<button class="btn-input-icon btn-edit" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button class="btn-input-icon text-danger btn-delete ms-1" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Hapus"><i class="bx bx-trash"></i></button>';

                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'period' => 'required|string|size:6',
        ]);

        $data = $request->all();
        $data = $this->sanitizeCurrency($data);

        ProjectHasilUsaha::create($data);

        return response()->json(['success' => 'Data berhasil disimpan.']);
    }

    public function edit($id)
    {
        $data = ProjectHasilUsaha::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'period' => 'required|string|size:6',
        ]);

        $data = ProjectHasilUsaha::findOrFail($id);

        $input = $request->all();
        $input = $this->sanitizeCurrency($input);

        $data->update($input);

        return response()->json(['success' => 'Data berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        ProjectHasilUsaha::destroy($id);
        return response()->json(['success' => 'Data berhasil dihapus.']);
    }

    public function syncAll(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string|size:6',
            'force' => 'nullable|boolean',
        ]);

        $summary = $this->syncService->syncAll(
            $validated['period'],
            (bool) ($validated['force'] ?? false)
        );

        return redirect()->route('project-hasil-usaha.index')->with('import_summary', $summary);
    }

    private function sanitizeCurrency($data)
    {
        $currencyFields = [
            'kontrak_review_total',
            'kontrak_review',
            'penjualan_ra',
            'penjualan_ri',
            'lsp_review',
            'lsp_proyeksi',
            'lsp_ra',
            'lsp_ri'
        ];

        foreach ($currencyFields as $field) {
            if (isset($data[$field])) {
                $clean = str_replace('.', '', $data[$field]);
                $clean = str_replace(',', '.', $clean);
                $data[$field] = $clean;
            }
        }

        return $data;
    }
}
