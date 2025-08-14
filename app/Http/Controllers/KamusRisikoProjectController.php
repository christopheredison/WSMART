<?php

namespace App\Http\Controllers;

use App\Models\JenisRisiko;
use App\Models\KamusRisikoProject;
use App\Models\PeristiwaRisiko;
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\ProjectPeriodeList;
use Illuminate\Http\Request;
use App\Exports\KamusRisikoProjectExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KamusRisikoProjectController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = KamusRisikoProject::with([
                'project',
                'projectRisk.peristiwaRisiko',
                'projectRisk.jenisRisiko.kategoriRisiko',
                'projectRisk.projectRiskAnalisa'
            ]);

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->filled('peristiwa_risiko_id')) {
                $query->whereHas('projectRisk', function ($q) use ($request) {
                    $q->where('peristiwa_risiko_id', $request->peristiwa_risiko_id);
                });
            }

            if ($request->filled('jenis_risiko_id')) {
                $query->whereHas('projectRisk', function ($q) use ($request) {
                    $q->where('jenis_risiko_id', $request->jenis_risiko_id);
                });
            }

            if ($request->filled('level_risiko')) {
                $query->whereHas('projectRisk', function ($q) use ($request) {
                    $q->where('level_risiko', $request->level_risiko);
                });
            }

            if ($request->filled('deskripsi_risiko')) {
                $query->whereHas('projectRisk', function ($q) use ($request) {
                    $q->where('deskripsi_peristiwa_risiko', 'like', '%' . $request->deskripsi_risiko . '%');
                });
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $detailUrl = route('projects.risks.view', ['project' => $row->project_id, 'risk' => $row->project_risk_id]);
                    
                    $btn_view = '<a href="' . $detailUrl . '" target="_blank" class="btn btn-sm btn-info d-flex align-items-center justify-content-center" title="View Detail">
                                    <span class="bx bx-show me-1"></span>
                                    <span>View Detail</span>
                                </a>';

                    $btn_ambil = '<button type="button" class="btn btn-sm btn-success d-flex align-items-center justify-content-center btn-ambil-risiko" data-id="' . $row->project_risk_id . '" title="Ambil Risiko">
                                    <span class="bx bx-plus me-1"></span>
                                    <span>Ambil Risiko</span>
                                </button>';
                    
                    return '<div class="d-flex flex-column gap-1">' . $btn_view . $btn_ambil . '</div>';
                })
                ->addColumn('proyek', function ($row) {
                    return $row->project->project_name ?? '-';
                })
                ->addColumn('taksonomi_risiko', function ($row) {
                    $kategori = $row->projectRisk->jenisRisiko->kategoriRisiko->title ?? 'N/A';
                    $jenis = $row->projectRisk->jenisRisiko->title ?? 'N/A';
                    return $kategori . ' - ' . $jenis;
                })
                ->addColumn('peristiwa_risiko', function ($row) {
                    return $row->projectRisk->peristiwaRisiko->title ?? '-';
                })
                ->addColumn('deskripsi_peristiwa_risiko', function ($row) {
                    return $row->projectRisk->deskripsi_peristiwa_risiko ?? '-';
                })
                ->addColumn('nilai_dampak_inheren', function ($row) {
                    return 'Rp ' . number_format($row->projectRisk->projectRiskAnalisa->nilai_dampak ?? 0, 0, ',', '.');
                })
                ->addColumn('skala_dampak_inheren', function ($row) {
                    return $row->projectRisk->projectRiskAnalisa->skala_dampak ?? '-';
                })
                ->addColumn('nilai_probabilitas_inheren', function ($row) {
                    return ($row->projectRisk->projectRiskAnalisa->nilai_probabilitas ?? 0) . ' %';
                })
                ->addColumn('eksposur_risiko_inheren', function ($row) {
                    return 'Rp ' . number_format($row->projectRisk->projectRiskAnalisa->eksposur_risiko ?? 0, 0, ',', '.');
                })
                ->addColumn('level_risiko_inheren', function ($row) {
                    $analisa = $row->projectRisk->projectRiskAnalisa;
                    if (!$analisa || !$analisa->level_risiko) return '-';
                    $css_class = str_replace(' ', '.', $analisa->level_risiko);
                    return '<span class="badge-level ' . e($css_class) . '">' . e($analisa->level_risiko) . ' (' . e($analisa->skala_risiko) . ')</span>';
                })
                ->addColumn('realisasi_nilai_dampak', function ($row) {
                    return 'Rp ' . number_format($row->projectRisk->projectRiskAnalisa->nilai_dampak_residual ?? 0, 0, ',', '.');
                })
                ->addColumn('realisasi_skala_dampak', function ($row) {
                    return $row->projectRisk->projectRiskAnalisa->skala_dampak_residual ?? '-';
                })
                ->addColumn('realisasi_skala_probabilitas', function ($row) {
                    return $row->projectRisk->projectRiskAnalisa->skala_probabilitas_residual ?? '-';
                })
                ->addColumn('realisasi_level_risiko', function ($row) {
                    $analisa = $row->projectRisk->projectRiskAnalisa;
                    if (!$analisa || !$analisa->level_risiko_residual) return '-';
                    $css_class = str_replace(' ', '.', $analisa->level_risiko_residual);
                    return '<span class="badge-level ' . e($css_class) . '">' . e($analisa->level_risiko_residual) . ' (' . e($analisa->skala_risiko_residual) . ')</span>';
                })
                ->addColumn('realisasi_eksposur_risiko', function ($row) {
                    return 'Rp ' . number_format($row->projectRisk->projectRiskAnalisa->eksposur_risiko_residual ?? 0, 0, ',', '.');
                })
                ->rawColumns(['action', 'level_risiko_inheren', 'realisasi_level_risiko'])
                ->make(true);
        }

        // Data untuk filter
        $projects = Project::orderBy('project_name')->get(['id', 'project_name']);
        $peristiwaRisikos = PeristiwaRisiko::where('type', 2)->orderBy('title')->get(['id', 'title']);
        $jenisRisikos = JenisRisiko::with('kategoriRisiko')->get();
        $levelRisikos = [
            ProjectRisk::LEVEL_RISIKO_LOW => 'Low',
            ProjectRisk::LEVEL_RISIKO_LOW_TO_MODERATE => 'Low To Moderate',
            ProjectRisk::LEVEL_RISIKO_MODERATE => 'Moderate',
            ProjectRisk::LEVEL_RISIKO_MODERATE_TO_HIGH => 'Moderate To High',
            ProjectRisk::LEVEL_RISIKO_HIGH => 'High',
        ];

        return view('kamus-risiko-project.index', compact('projects', 'peristiwaRisikos', 'jenisRisikos', 'levelRisikos'));
    }

    public function addRisk(Request $request)
    {
        $request->validate([
            'original_risk_id' => 'required|exists:project_risks,id',
            'target_project_id' => 'required|exists:projects,id',
        ]);

        try {
            DB::beginTransaction();

            $originalRisk = ProjectRisk::with([
                'penyebabRisikoProjects.perlakuanPenyebabRisiko', 
                'kriProjects',
                'projectRiskAnalisa'
            ])->findOrFail($request->original_risk_id);
            
            $targetPeriodeList = ProjectPeriodeList::where('project_id', $request->target_project_id)->first();

            if (!$targetPeriodeList) {
                return response()->json(['message' => 'Proyek tujuan tidak ditemukan.'], 422);
            }

            // Cek duplikasi berdasarkan deskripsi di proyek tujuan
            $isExist = ProjectRisk::where('project_id', $targetPeriodeList->project_id)
                ->where('deskripsi_peristiwa_risiko', $originalRisk->deskripsi_peristiwa_risiko)
                ->exists();
                
            if ($isExist) {
                return response()->json(['message' => 'Risiko dengan deskripsi yang sama sudah ada di proyek tujuan.'], 422);
            }

            // 1. Duplikasi ProjectRisk
            $newRisk = $originalRisk->replicate()->fill([
                'project_id' => $targetPeriodeList->project_id,
                'project_periode_list_id' => $targetPeriodeList->id,
                'user_id' => auth()->id(),
                'is_closed' => 0,
                'status_progress' => ProjectRisk::STATUS_INPUT_DATA,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $newRisk->save();

            // 2. Duplikasi ProjectRiskAnalisa
            if ($originalRisk->projectRiskAnalisa) {
                $newAnalisa = $originalRisk->projectRiskAnalisa->replicate()->fill([
                    'risiko_id' => $newRisk->id
                ]);
                $newAnalisa->save();
            }

            // 3. Duplikasi Penyebab Risiko
            foreach ($originalRisk->penyebabRisikoProjects as $originalPenyebab) {
                $newPenyebab = $originalPenyebab->replicate();
                $newPenyebab->risiko_id = $newRisk->id;
                $newPenyebab->save();

                foreach ($originalPenyebab->perlakuanPenyebabRisiko as $originalPerlakuan) {
                    $newPerlakuan = $originalPerlakuan->replicate();
                    $newPerlakuan->penyebab_risiko_id = $newPenyebab->id;
                    $newPerlakuan->save();
                }
            }

            // 4. Duplikasi KRI
            foreach ($originalRisk->kriProjects as $originalKri) {
                $newKri = $originalKri->replicate()->fill([
                    'risiko_id' => $newRisk->id
                ]);
                $newKri->save();
            }
            
            DB::commit();

            return response()->json(['message' => 'Risiko berhasil ditambahkan ke proyek yang dipilih.']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal mengambil risiko dari kamus: ' . $th->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server saat mencoba mengambil risiko.'], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'project_id',
            'peristiwa_risiko_id',
            'jenis_risiko_id',
            'level_risiko',
            'deskripsi_risiko',
        ]);

        $fileName = 'Kamus_Risiko_Proyek_' . date('d-m-Y') . '.xlsx';

        return Excel::download(new KamusRisikoProjectExport($filters), $fileName);
    }
}