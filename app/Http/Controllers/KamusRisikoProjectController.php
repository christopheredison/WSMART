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
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;

class KamusRisikoProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $user->load('projects', 'unit'); // Eager load relasi

        // A. Ambil Project yang di-assign langsung ke User
        $userProjectIds = $user->projects->pluck('id');

        // B. Ambil Project dibawah Unit/Divisi (Cek Permission)
        $unitProjectIds = collect([]);
        if ($user->unit && Gate::check('can_access_project_under_division')) {
            // Asumsi relasi unit->projects() mengambil project berdasarkan cost_center_parent
            $unitProjectIds = $user->unit->projects()->pluck('id');
        }

        // C. Gabungkan ID (Assign + Unit)
        $allAllowedIds = $userProjectIds->merge($unitProjectIds)->unique();

        if ($request->ajax()) {
            $query = KamusRisikoProject::with([
                'project',
                'projectRisk.peristiwaRisiko',
                'projectRisk.jenisRisiko.kategoriRisiko',
                'projectRisk.projectRiskAnalisa',
                'projectRisk.projectRiskMonitoring',
            ]);

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            // Filter Relation via ProjectRisk
            $query->whereHas('projectRisk', function ($q) use ($request) {
                if ($request->filled('peristiwa_risiko_id')) {
                    $q->where('peristiwa_risiko_id', $request->peristiwa_risiko_id);
                }
                if ($request->filled('jenis_risiko_id')) {
                    $q->where('jenis_risiko_id', $request->jenis_risiko_id);
                }
                if ($request->filled('level_risiko')) {
                    $q->where('level_risiko', $request->level_risiko);
                }
                if ($request->filled('deskripsi_risiko')) {
                    $q->where('deskripsi_peristiwa_risiko', 'like', '%' . $request->deskripsi_risiko . '%');
                }
                if ($request->filled('efektivitas')) {
                    if ($request->efektivitas == 'efektif') {
                        $q->where('efektivitas_perlakuan_risiko', '>', 0);
                    } elseif ($request->efektivitas == 'tidak_efektif') {
                        $q->where('efektivitas_perlakuan_risiko', '<=', 0);
                    }
                }
            });

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $projectPeriodeId = $row->projectRisk->project_periode_list_id ?? $row->project_id;

                    $detailUrl = route('projects.risks.view', ['project' => $projectPeriodeId, 'risk' => $row->project_risk_id]);

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

                // Inherent
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

                // Residual
                ->addColumn('nilai_dampak_residual', function ($row) {
                    return 'Rp ' . number_format($row->projectRisk->projectRiskAnalisa->nilai_dampak_residual ?? 0, 0, ',', '.');
                })
                ->addColumn('skala_dampak_residual', function ($row) {
                    return $row->projectRisk->projectRiskAnalisa->skala_dampak_residual ?? '-';
                })
                ->addColumn('nilai_probabilitas_residual', function ($row) {
                    return ($row->projectRisk->projectRiskAnalisa->nilai_probabilitas_residual ?? 0) . ' %';
                })
                ->addColumn('eksposur_risiko_residual', function ($row) {
                    return 'Rp ' . number_format($row->projectRisk->projectRiskAnalisa->eksposur_risiko_residual ?? 0, 0, ',', '.');
                })
                ->addColumn('level_risiko_residual', function ($row) {
                    $analisa = $row->projectRisk->projectRiskAnalisa;
                    if (!$analisa || !$analisa->level_risiko_residual) return '-';
                    $css_class = str_replace(' ', '.', $analisa->level_risiko_residual);
                    return '<span class="badge-level ' . e($css_class) . '">' . e($analisa->level_risiko_residual) . ' (' . e($analisa->skala_risiko_residual) . ')</span>';
                })

                // Monitoring
                ->addColumn('realisasi_nilai_dampak', function ($row) {
                    return 'Rp ' . number_format($row->projectRisk->projetRiskMonitoring->nilai_dampak ?? 0, 0, ',', '.');
                })
                ->addColumn('realisasi_skala_dampak', function ($row) {
                    return $row->projectRisk->projectRiskMonitoring->skala_dampak ?? '-';
                })
                ->addColumn('realisasi_skala_probabilitas', function ($row) {
                    return $row->projectRisk->projectRiskMonitoring?->skalaProbabilitas?->tingkat ?? '-';
                })
                ->addColumn('realisasi_level_risiko', function ($row) {
                    $monitoring = $row->projectRisk->projectRiskMonitoring;
                    if (!$monitoring || !$monitoring->level_risiko) return '-';
                    $css_class = str_replace(' ', '.', $monitoring->level_risiko);
                    return '<span class="badge-level ' . e($css_class) . '">' . e($monitoring->level_risiko) . ' (' . e($monitoring->skala_risiko) . ')</span>';
                })
                // ->addColumn('realisasi_eksposur_risiko', function ($row) {
                //     return 'Rp ' . number_format($row->projectRisk->projectRiskMonitoring->eksposur_risiko ?? 0, 0, ',', '.');
                // })
                ->addColumn('efektivitas', function ($row) {
                    $efektivitas = $row->projectRisk->efektivitas_perlakuan_risiko;

                    if (is_null($efektivitas)) {
                        return '-';
                    }

                    $class = $efektivitas > 0 ? 'text-success' : ($efektivitas < 0 ? 'text-danger' : 'text-warning');

                    return '<span class="fw-bold ' . $class . '">' . $efektivitas . '%</span>';
                })
                ->rawColumns(['action', 'level_risiko_inheren', 'level_risiko_residual', 'realisasi_level_risiko', 'efektivitas'])
                ->make(true);
        }

        $projectsQuery = Project::orderBy('project_name');

        if (!Gate::check('project_admin_access')) {
            $projectsQuery->whereIn('id', $allAllowedIds);
        }

        $projectUser = $projectsQuery->get(['id', 'project_name']);

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

        return view('kamus-risiko-project.index', compact('projects', 'projectUser', 'peristiwaRisikos', 'jenisRisikos', 'levelRisikos'));
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
                'dampakRisikoProjects.perlakuanDampakRisiko',
                'kriProjects',
                'projectRiskAnalisa'
            ])->findOrFail($request->original_risk_id);

            $targetPeriodeList = ProjectPeriodeList::where('project_id', $request->target_project_id)->first();

            if (!$targetPeriodeList) {
                return response()->json(['message' => 'Proyek tujuan tidak ditemukan.'], 422);
            }

            // Cek duplikasi berdasarkan deskripsi di proyek tujuan
            // $isExist = ProjectRisk::where('project_id', $targetPeriodeList->project_id)
            //     ->where('deskripsi_peristiwa_risiko', $originalRisk->deskripsi_peristiwa_risiko)
            //     ->where('is_closed', 0)
            //     ->exists();

            // if ($isExist) {
            //     return response()->json(['message' => 'Risiko dengan deskripsi yang sama sudah ada di proyek tujuan.'], 422);
            // }

            // 1. Duplikasi ProjectRisk
            $newRisk = $originalRisk->replicate()->fill([
                'project_id' => $targetPeriodeList->project_id,
                'project_periode_list_id' => $targetPeriodeList->id,
                'user_id' => auth()->id(),
                'is_closed' => 0,
                'status_progress' => ProjectRisk::STATUS_INPUT_DATA,
                'status' => ProjectRisk::STATUS_INPUT_DATA,
                'step_verification' => 0,
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

            // 4. Duplikasi Dampak Risiko
            foreach ($originalRisk->dampakRisikoProjects as $originalDampak) {
                $newDampak = $originalDampak->replicate();
                $newDampak->risiko_id = $newRisk->id;
                $newDampak->save();

                // 2. Duplikasi Perlakuan Dampak (Jika ada)
                foreach ($originalDampak->perlakuanDampakRisiko as $originalPerlakuanDampak) {
                    $newPerlakuanDampak = $originalPerlakuanDampak->replicate();

                    $newPerlakuanDampak->risiko_id = $newRisk->id;
                    $newPerlakuanDampak->dampak_risiko_id = $newDampak->id;

                    // Opsional: Reset tanggal jika diperlukan (tergantung kebutuhan bisnis)
                    // $newPerlakuanDampak->timeline_perlakuan_risiko_start = now();

                    $newPerlakuanDampak->save();
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

            return response()->json([
              'message' => 'Risiko berhasil ditambahkan ke proyek yang dipilih.',
              'redirect_project_id' => $targetPeriodeList->id,
              'risk_id' => $newRisk->id
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal mengambil risiko dari kamus: ' . $th->getMessage());
            dd($th->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server saat mencoba mengambil risiko.'], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $filters = $request->only([
                'project_id',
                'peristiwa_risiko_id',
                'jenis_risiko_id',
                'level_risiko',
                'deskripsi_risiko',
                'efektivitas',
            ]);

            $fileName = 'Kamus_Risiko_Proyek_' . date('d-m-Y_H-i-s') . '.xlsx';

            $fileContents = Excel::raw(
                new KamusRisikoProjectExport($filters),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal export Kamus Risiko Proyek: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat membuat file Excel.'], 500);
        }
    }
}
