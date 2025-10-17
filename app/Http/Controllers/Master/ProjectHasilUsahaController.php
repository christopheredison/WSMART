<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectHasilUsaha;
use Illuminate\Http\Request;
use App\Supports\ApiWika;
use DataTables;

class ProjectHasilUsahaController extends Controller
{
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
            // ->editColumn('lsp_ri', fn($row) => 'Rp ' . number_format($row->lsp_ri, 0, ',', '.'))
            ->editColumn('kontrak_review', fn($row) => 'Rp ' . number_format($row->kontrak_review, 0, ',', '.'))
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
        
        ProjectHasilUsaha::create($request->all());

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
        $data->update($request->all());
        
        return response()->json(['success' => 'Data berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        ProjectHasilUsaha::destroy($id);
        return response()->json(['success' => 'Data berhasil dihapus.']);
    }

    public function syncAll(Request $request)
    {
        // Logika syncAll Anda tetap sama seperti sebelumnya
        $validated = $request->validate(['period' => 'required|string|size:6']);
        @set_time_limit(300);
        $period = $validated['period'];
        $projects = Project::whereNotNull('meta->profit_center')->get();
        $summary = ['success' => 0, 'failed' => 0, 'skipped' => 0, 'failed_rows' => []];

        foreach ($projects as $project) {
            $profitCenter = $project->meta['profit_center'] ?? null;
            if (!$profitCenter) { $summary['skipped']++; continue; }

            try {
                $apiResponse = (new ApiWika())->getHasilUsahaProject($period, $profitCenter);
                if ($apiResponse && $apiResponse['status'] && isset($apiResponse['data']['hasil_usaha'])) {
                    $apiData = $apiResponse['data']['hasil_usaha'];
                    ProjectHasilUsaha::updateOrCreate(
                        ['project_id' => $project->id, 'period' => $period],
                        ['profit_center' => $profitCenter, 'response_data' => $apiResponse['data'], 'kontrak_review' => $apiData['kontrak_review'] ?? 0, 'progress_fisik_ra' => $apiData['progress_fisik_ra'] ?? 0, 'progress_fisik_ri' => $apiData['progress_fisik_ri'] ?? 0, 'penjualan_ra' => $apiData['penjualan_ra'] ?? 0, 'penjualan_ri' => $apiData['penjualan_ri'] ?? 0, 'lsp_review' => $apiData['lsp_review'] ?? 0, 'lsp_ra' => $apiData['lsp_ra'] ?? 0, 'lsp_ri' => $apiData['lsp_ri'] ?? 0, 'lsp_proyeksi' => $apiData['lsp_proyeksi'] ?? 0]
                    );
                    $summary['success']++;
                } else {
                    $summary['failed']++; $summary['failed_rows'][] = "Proyek '{$project->project_name}': API call gagal.";
                }
            } catch (\Exception $e) {
                $summary['failed']++; $summary['failed_rows'][] = "Proyek '{$project->project_name}': " . $e->getMessage();
            }
        }
        return redirect()->route('project-hasil-usaha.index')->with('import_summary', $summary);
    }
}