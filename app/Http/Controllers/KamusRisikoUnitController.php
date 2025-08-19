<?php

namespace App\Http\Controllers;

use App\Models\IdentifikasiRisiko;
use App\Models\JenisRisiko;
use App\Models\KamusRisikoUnit;
use App\Models\Periode;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Exports\KamusRisikoUnitExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KamusRisikoUnitController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = KamusRisikoUnit::with([
                'identifikasiRisiko.unit',
                'identifikasiRisiko.jenisRisiko.kategoriRisiko',
                'identifikasiRisiko.riskAnalysis'
            ]);

            $query->whereHas('identifikasiRisiko');

            if ($request->filled('unit_id')) {
                $query->whereHas('identifikasiRisiko', function ($q) use ($request) {
                    $q->where('unit_id', $request->unit_id);
                });
            }
            
            if ($request->filled('peristiwa_risiko')) {
                $query->whereHas('identifikasiRisiko', function ($q) use ($request) {
                    $q->where('peristiwa_risiko', 'like', '%' . $request->peristiwa_risiko . '%');
                });
            }

            if ($request->filled('jenis_risiko_id')) {
                $query->whereHas('identifikasiRisiko', function ($q) use ($request) {
                    $q->where('jenis_risiko_id', $request->jenis_risiko_id);
                });
            }

            if ($request->filled('level_risiko')) {
                $query->whereHas('identifikasiRisiko', function ($q) use ($request) {
                    $q->where('level_risiko', $request->level_risiko);
                });
            }

            if ($request->filled('deskripsi_risiko')) {
                $query->whereHas('identifikasiRisiko', function ($q) use ($request) {
                    $q->where('deskripsi_peristiwa_risiko', 'like', '%' . $request->deskripsi_risiko . '%');
                });
            }

            if ($request->filled('efektivitas')) {
                $query->whereHas('identifikasiRisiko', function ($q) use ($request) {
                    if ($request->efektivitas == 'efektif') {
                        // 'Efektif' jika nilainya lebih dari 0
                        $q->where('efektivitas_perlakuan_risiko', '>', 0);
                    } elseif ($request->efektivitas == 'tidak_efektif') {
                        // 'Tidak Efektif' jika nilainya 0 atau kurang dari 0 (negatif)
                        $q->where('efektivitas_perlakuan_risiko', '<=', 0);
                    }
                });
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                      $detailUrl = route('risk-register-unit.view', ['riskRegister' => $row->risiko_id]);

                      $btn_view = '<a href="' . $detailUrl . '" target="_blank" class="btn btn-sm btn-info d-flex align-items-center justify-content-center" title="View Detail">
                                      <span class="bx bx-show me-1"></span>
                                      <span>View Detail</span>
                                  </a>';

                      $btn_ambil = '<button type="button" class="btn btn-sm btn-success d-flex align-items-center justify-content-center btn-ambil-risiko" data-id="' . $row->risiko_id . '" title="Ambil Risiko">
                                      <span class="bx bx-plus me-1"></span>
                                      <span>Ambil Risiko</span>
                                  </button>';
                      
                      return '<div class="d-flex flex-column gap-1">' . $btn_view . $btn_ambil . '</div>';
                })
                ->addColumn('divisi', function ($row) {
                    return $row->identifikasiRisiko->unit->name ?? '-';
                })
                ->addColumn('taksonomi_risiko', function ($row) {
                    $kategori = $row->identifikasiRisiko->jenisRisiko->kategoriRisiko->title ?? 'N/A';
                    $jenis = $row->identifikasiRisiko->jenisRisiko->title ?? 'N/A';
                    return $kategori . ' - ' . $jenis;
                })
                ->addColumn('peristiwa_risiko', function ($row) {
                    return $row->identifikasiRisiko->peristiwa_risiko ?? '-';
                })
                ->addColumn('deskripsi_peristiwa_risiko', function ($row) {
                    return $row->identifikasiRisiko->deskripsi_peristiwa_risiko ?? '-';
                })
                // Mengambil data dari relasi riskAnalysis
                ->addColumn('nilai_dampak_inheren', fn($row) => 'Rp ' . number_format($row->identifikasiRisiko->riskAnalysis->nilai_dampak ?? 0, 0, ',', '.'))
                ->addColumn('skala_dampak_inheren', fn($row) => $row->identifikasiRisiko->riskAnalysis->skala_dampak ?? '-')
                ->addColumn('nilai_probabilitas_inheren', fn($row) => ($row->identifikasiRisiko->riskAnalysis->nilai_probabilitas ?? 0) . ' %')
                ->addColumn('eksposur_risiko_inheren', fn($row) => 'Rp ' . number_format($row->identifikasiRisiko->riskAnalysis->eksposur_risiko ?? 0, 0, ',', '.'))
                ->addColumn('level_risiko_inheren', function ($row) {
                    $analisa = $row->identifikasiRisiko->riskAnalysis;
                    if (!$analisa || !$analisa->level_risiko) return '-';
                    $css_class = str_replace(' ', '.', $analisa->level_risiko);
                    return '<span class="badge-level ' . e($css_class) . '">' . e($analisa->level_risiko) . ' (' . e($analisa->skala_risiko) . ')</span>';
                })
                ->addColumn('realisasi_nilai_dampak', fn($row) => 'Rp ' . number_format($row->identifikasiRisiko->riskAnalysis->nilai_dampak_residual ?? 0, 0, ',', '.'))
                ->addColumn('realisasi_skala_dampak', fn($row) => $row->identifikasiRisiko->riskAnalysis->skala_dampak_residual ?? '-')
                ->addColumn('realisasi_skala_probabilitas', fn($row) => $row->identifikasiRisiko->riskAnalysis->skala_probabilitas_residual ?? '-')
                ->addColumn('realisasi_level_risiko', function ($row) {
                    $analisa = $row->identifikasiRisiko->riskAnalysis;
                    if (!$analisa || !$analisa->level_risiko_residual) return '-';
                    $css_class = str_replace(' ', '.', $analisa->level_risiko_residual);
                    return '<span class="badge-level ' . e($css_class) . '">' . e($analisa->level_risiko_residual) . ' (' . e($analisa->skala_risiko_residual) . ')</span>';
                })
                ->addColumn('realisasi_eksposur_risiko', fn($row) => 'Rp ' . number_format($row->identifikasiRisiko->riskAnalysis->eksposur_risiko_residual ?? 0, 0, ',', '.'))
                ->addColumn('efektivitas', function ($row) {
                    $efektivitas = $row->identifikasiRisiko->efektivitas_perlakuan_risiko;

                    if (is_null($efektivitas)) {
                        return '-';
                    }

                    $class = $efektivitas > 0 ? 'text-success' : ($efektivitas < 0 ? 'text-danger' : 'text-warning');
                    
                    return '<span class="fw-bold ' . $class . '">' . $efektivitas . '</span>';
                })
                ->rawColumns(['action', 'level_risiko_inheren', 'realisasi_level_risiko', 'efektivitas'])
                ->make(true);
        }

        // Data untuk filter
        $units = Unit::orderBy('name')->get(['id', 'name']);
        $jenisRisikos = JenisRisiko::with('kategoriRisiko')->get();
        $levelRisikos = [
            IdentifikasiRisiko::LEVEL_RISIKO_LOW => 'Low',
            IdentifikasiRisiko::LEVEL_RISIKO_LOW_TO_MODERATE => 'Low To Moderate',
            IdentifikasiRisiko::LEVEL_RISIKO_MODERATE => 'Moderate',
            IdentifikasiRisiko::LEVEL_RISIKO_MODERATE_TO_HIGH => 'Moderate To High',
            IdentifikasiRisiko::LEVEL_RISIKO_HIGH => 'High',
        ];

        return view('kamus-risiko-unit.index', compact('units', 'jenisRisikos', 'levelRisikos'));
    }

    public function addRisk(Request $request)
    {
        $request->validate([
            'original_risk_id' => 'required|exists:identifikasi_risikos,id',
            'target_unit_id' => 'required|exists:units,id',
            'overwrite' => 'sometimes|boolean',
        ]);

        try {
            DB::beginTransaction();

            $originalRisk = IdentifikasiRisiko::with([
                'penyebabRisiko.perlakuanPenyebabRisikoUnit', 
                'kris',
                'riskAnalysis'
            ])->findOrFail($request->original_risk_id);

            $activePeriode = Periode::where('status', 'active')->firstOrFail();

            // Cari risiko yang mungkin sudah ada
            $existingRisk = IdentifikasiRisiko::where('unit_id', $request->target_unit_id)
                ->where('periode_id', $activePeriode->id)
                ->where('peristiwa_risiko', $originalRisk->peristiwa_risiko)
                ->first();

            // Jika risiko sudah ada dan pengguna belum menyetujui overwrite
            if ($existingRisk && !$request->input('overwrite', false)) {
                return response()->json([
                    'conflict' => true,
                    'message' => 'Peristiwa risiko "' . $originalRisk->peristiwa_risiko . '" sudah ada untuk unit/divisi ini pada periode yang sama.'
                ], 409);
            }

            // Jika pengguna setuju untuk overwrite, hapus data lama
            if ($existingRisk && $request->input('overwrite') == true) {
                // foreach($existingRisk->penyebabRisiko as $penyebab) {
                //     $penyebab->perlakuanPenyebabRisikoUnit()->delete();
                // }
                // $existingRisk->penyebabRisiko()->delete();
                // $existingRisk->kris()->delete();
                // $existingRisk->riskAnalysis()->delete();
                $existingRisk->delete();
            }

            // 1. Duplikasi IdentifikasiRisiko
            $newRisk = $originalRisk->replicate()->fill([
                'unit_id' => $request->target_unit_id,
                'periode_id' => $activePeriode->id,
                'user_id' => auth()->id(),
                'is_closed' => 0,
                'status_progress' => IdentifikasiRisiko::STATUS_INPUT_DATA,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $newRisk->save();

            // 2. Duplikasi RiskAnalysis
            if ($originalRisk->riskAnalysis) {
                $newAnalisa = $originalRisk->riskAnalysis->replicate()->fill([
                    'risiko_id' => $newRisk->id
                ]);
                $newAnalisa->save();
            }

            // 3. Duplikasi Penyebab Risiko
            foreach ($originalRisk->penyebabRisiko as $originalPenyebab) {
                $newPenyebab = $originalPenyebab->replicate();
                $newPenyebab->risiko_id = $newRisk->id;
                $newPenyebab->save();

                foreach ($originalPenyebab->perlakuanPenyebabRisikoUnit as $originalPerlakuan) {
                    $newPerlakuan = $originalPerlakuan->replicate();
                    $newPerlakuan->penyebab_risiko_id = $newPenyebab->id;
                    $newPerlakuan->save();
                }
            }

            // 4. Duplikasi KRI
            foreach ($originalRisk->kris as $originalKri) {
                $newKri = $originalKri->replicate()->fill([
                    'risiko_id' => $newRisk->id
                ]);
                $newKri->save();
            }
            
            DB::commit();

            $redirectUrl = route('risk-register-unit.index', ['pid' => $newRisk->period_id]);
            $message = $request->input('overwrite') ? 'Risiko lama berhasil diganti dengan risiko baru.' : 'Risiko berhasil ditambahkan ke divisi yang dipilih.';

            return response()->json([
                'message' => $message,
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Gagal mengambil risiko dari kamus: ' . $th->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan pada server saat mencoba mengambil risiko.'], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $filters = $request->only([
                'unit_id',
                'peristiwa_risiko',
                'jenis_risiko_id',
                'level_risiko',
                'deskripsi_risiko',
            ]);
    
            $fileName = 'Kamus_Risiko_Divisi_' . date('d-m-Y_H-i-s') . '.xlsx';
    
            $fileContents = Excel::raw(
                new KamusRisikoUnitExport($filters),
                \Maatwebsite\Excel\Excel::XLSX
            );
    
            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Gagal export Kamus Risiko Divisi: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat membuat file Excel.'], 500);
        }
    }
}