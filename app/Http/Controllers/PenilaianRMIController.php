<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RMIPeriod; // Perhatikan nama model yang benar (RMIPeriod bukan RmiPeriod)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Dimension;
use App\Models\ScoreCriteria;
use App\Models\MeasurementParameter;
use App\Models\ParameterCriteria;
use App\Models\ScoreParameter;
use App\Models\SubDimension;
use App\Models\DimensionAspectEvaluation;
use App\Models\ScoreCriteriaDoc;
use App\Models\ParameterKinerja;
use App\Models\PilihanParameterKinerja;
use App\Models\PenilaianCapaianKinerja;
use App\Models\Periode;
use App\Models\DetailPenilaianCapaianKinerja;
use App\Models\SkalaKinerja;
use App\Models\SkalaKPMR;
use App\Models\FinalRating;
use App\Models\FinalRatingPeriod;
use App\Models\RMIPeriodDocument;
use App\Models\ParameterKinerjaDocument;
use Illuminate\Support\Facades\Validator;

class PenilaianRMIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $periods = RMIPeriod::orderBy('year', 'desc')->get();

        // Tambahkan status untuk setiap periode
        foreach ($periods as $period) {
            if ($period->status == 1) {
                $period->status_text = 'Dalam Proses';
            } else {
                $period->status_text = 'Selesai';
            }
        }

        $skalaKinerjas = SkalaKinerja::orderBy('id', 'asc')->get();
        $skalaKpmrs    = SkalaKPMR::orderBy('id', 'asc')->get();
        $tableLegend = [
            [
              'icon' => '<span class="bx bx-show"></span>',
              'label' => 'Detail'
            ],
            [
              'icon' => '<span class="bx bx-bar-chart-alt-2 text-primary"></span>',
              'label' => 'Penilaian Aspek Dimensi'
            ],
            [
              'icon' => '<span class="bx bx-analyse text-success"></span>',
              'label' => 'Penilaian Aspek Kinerja'
            ],
            [
              'icon' => '<span class="bx bxs-edit-alt text-warning"></span>',
              'label' => 'Atur Data Penilaian'
            ],
        ];

        return view('penilaian-rmi.index', compact('periods', 'skalaKinerjas', 'skalaKpmrs', 'tableLegend'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showxx($id)
    {
        $period = RMIPeriod::findOrFail($id);

        // Ambil semua dimensi dengan sub dimensi dan parameter
        $dimensions = Dimension::with([
            'subDimensions' => function($query) {
                $query->orderBy('id', 'asc');
            },
            'subDimensions.measurementParameters' => function($query) {
                $query->orderBy('id', 'asc');
            },
            'subDimensions.measurementParameters.criteria.details' => function($query) {
                $query->orderBy('id', 'asc');
            },
            'subDimensions.measurementParameters.criteria' => function($query) {
                $query->orderBy('id', 'asc');
            }
        ])->orderBy('id', 'asc')->get();

        // Ambil skor parameter untuk periode ini
        $parameterScores = ScoreParameter::where('period_id', $id)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('parameter_id');

        // Ambil skor kriteria untuk periode ini
        $criteriaScores = ScoreCriteria::where('period_id', $id)
            ->whereNull('deleted_at')
            ->with('documents') // Ambil dokumen terkait
            ->get()
            ->keyBy('parameter_criteria_id');

        // Ambil skor dimensi
        $dimensionScores = DimensionAspectEvaluation::whereNull('deleted_at')
            ->get()
            ->keyBy('sub_dimension_id');

        return view('penilaian-rmi.show', compact('period', 'dimensions', 'parameterScores', 'criteriaScores', 'dimensionScores'));
    }

    public function show($id)
    {
        // 1. Ambil periode + eager load penilaian kinerja + detail + pilihan
        $period = RMIPeriod::with([
            'penilaianCapaianKinerja.details.pilihan',
            'documents'
        ])->findOrFail($id);

        $evidenceMap = collect();
        if ($period->penilaianCapaianKinerja) {
            $penilaianId = $period->penilaianCapaianKinerja->id;

            $evidenceMap = ParameterKinerjaDocument::where('penilaian_capaian_kinerja_id', $penilaianId)->get()->groupBy('parameter_id');
        }

        // 2. Aspek Dimensi (tetap seperti existing)
        $dimensions = Dimension::with([
            'subDimensions' => fn($q)=> $q->orderBy('id'),
            'subDimensions.measurementParameters' => fn($q)=> $q->orderBy('id'),
            'subDimensions.measurementParameters.criteria' => fn($q)=> $q->orderBy('id'),
            'subDimensions.measurementParameters.criteria.details' => fn($q)=> $q->orderBy('id'),
        ])->orderBy('id')->get();

        $parameterScores = ScoreParameter::where('period_id', $id)
                            ->whereNull('deleted_at')
                            ->get()
                            ->keyBy('parameter_id');

        $criteriaScores  = ScoreCriteria::where('period_id', $id)
                            ->whereNull('deleted_at')
                            ->with('documents')
                            ->get()
                            ->keyBy('parameter_criteria_id');

        $dimensionScores = DimensionAspectEvaluation::where('period_id', $id)
                            ->whereNull('deleted_at')
                            ->get();

        // 3. **Baru**: ambil parameter kinerja top-level
        $paramsCapaian = ParameterKinerja::capaian()
            ->with('children.options','options')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $paramsKpmr = ParameterKinerja::kpmr()
            ->with('children.options','options')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        // 4. Kirim semua ke view
        return view('penilaian-rmi.show', compact(
            'period',
            'dimensions',
            'parameterScores',
            'criteriaScores',
            'dimensionScores',
            'paramsCapaian',
            'paramsKpmr',
            'evidenceMap'
        ));
    }


    /**
     * Menampilkan halaman penilaian aspek dinamis
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function aspekDinamis($id)
    {
        // Ambil data periode
        $period = RMIPeriod::findOrFail($id);

        // Ambil semua dimensi dengan sub dimensi dan parameter
        $dimensions = Dimension::with([
            'subDimensions' => function($query) {
                $query->orderBy('id', 'asc');
            },
            'subDimensions.measurementParameters',
            'subDimensions.measurementParameters.criteria' => function($query) {
                $query->with(['details' => function($q) {
                    $q->orderBy('level', 'asc');
                }]);
            }
        ])->orderBy('id', 'asc')->get();

        // Ambil skor yang sudah ada
        // $scores = ScoreCriteria::where('period_id', $id)
        //     ->pluck('score', 'parameter_criteria_id')
        //     ->toArray();

        $scoreCriterias = ScoreCriteria::where('period_id', $id)->get();
        $scores = [];
        $gapAnalysis = [];
        foreach ($scoreCriterias as $sc) {
            $scores[$sc->parameter_criteria_id] = $sc->score;
            $gapAnalysis[$sc->parameter_criteria_id] = $sc->gap_analysis;
        }

        return view('penilaian-rmi.penilaian-aspek-dinamis', compact('period', 'dimensions', 'scores', 'gapAnalysis'));
    }

    /**
     * Menyimpan hasil penilaian aspek dinamis
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Menyimpan penilaian aspek dinamis
     */
    public function saveAspekDinamis(Request $request, $periodId)
    {
        if ($request->action === 'finish') {
            $validator = Validator::make($request->all(), [
                'scores' => 'array',
                'scores.*' => 'required|numeric|min:0|max:5',
                'gap_analysis' => 'array',
                'gap_analysis.*' => 'nullable|string',
                'action' => 'required|in:save,finish',
            ]);

            $validator->after(function ($validator) use ($request) {
                if (!$request->has('scores')) {
                    return;
                }

                foreach ($request->scores as $criteriaId => $score) {
                    if ($this->isScorableCriteriaScore($score)) {
                        $gap = trim($request->gap_analysis[$criteriaId] ?? '');
                        if ($gap === '') {
                            $validator->errors()->add(
                                "gap_analysis.{$criteriaId}",
                                'Gap analysis wajib diisi untuk kriteria yang tidak di-skip.'
                            );
                        }
                    }
                }
            });

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
        } else {
            $request->validate([
                'scores' => 'array',
                'scores.*' => 'nullable|numeric|min:0|max:5',
                'action' => 'required|in:save,finish',
                'gap_analysis' => 'array',
                'gap_analysis.*' => 'nullable|string',
            ]);
        }

        // Ambil periode
        $period = RMIPeriod::findOrFail($periodId);

        // 1. Simpan skor kriteria dan gap analysis
        if ($request->has('scores')) {
            \Log::info('masuk ke dalam blok hasScores');
            foreach ($request->scores as $criteriaId => $score) {
                $gap = $request->gap_analysis[$criteriaId] ?? null;

                // Jika score null (belum diisi) dan action save, biarkan saja tersimpan null / di-skip
                if ($score === null && $request->action === 'save') {
                    continue;
                }

                $existing = ScoreCriteria::where('period_id', $periodId)
                    ->where('parameter_criteria_id', $criteriaId)
                    ->first();

                // Cek apakah ada perubahan
                if ($existing && $existing->score == $score && $existing->gap_analysis == $gap) {
                    continue;
                }

                // Hapus data lama
                ScoreCriteria::where('period_id', $periodId)
                    ->where('parameter_criteria_id', $criteriaId)
                    ->delete();

                // Buat skor baru
                ScoreCriteria::create([
                    'period_id' => $periodId,
                    'parameter_criteria_id' => $criteriaId,
                    'score' => $score,
                    'gap_analysis' => $gap
                ]);
            }
        }

        // 2. Handle upload dokumen jika ada
        if ($request->has('files')) {
            \Log::info('masuk ke dalam blok has files');
            foreach ($request->file('files') as $criteriaId => $fileArray) {
                foreach ($fileArray as $file) {
                    if ($file && $file->isValid()) {
                        $filename = $file->getClientOriginalName();
                        $path = $file->store('gap-analysis-docs', 'public');
                        $scoreCriteria = ScoreCriteria::where('period_id', $periodId)
                            ->where('parameter_criteria_id', $criteriaId)
                            ->whereNull('deleted_at')
                            ->first();

                        if ($scoreCriteria) {
                            ScoreCriteriaDoc::create([
                                'score_criteria_id' => $scoreCriteria->id,
                                'filename' => $filename,
                                'path' => $path
                            ]);
                        }
                    }
                }
            }
        }

        // =========================================================================
        // 3. KALKULASI PARAMETER, DIMENSI, & RMI (Berlaku untuk Save & Finish)
        // =========================================================================
        $parameters = MeasurementParameter::all();

        foreach ($parameters as $parameter) {
            $criterias = ParameterCriteria::where('parameter_id', $parameter->id)->get();

            if ($criterias->isEmpty()) {
                ScoreParameter::where('period_id', $periodId)->where('parameter_id', $parameter->id)->delete();
                ScoreParameter::create([
                    'period_id' => $periodId,
                    'parameter_id' => $parameter->id,
                    'sub_dimension_id' => $parameter->sub_dimension_id,
                    'score' => null,
                    'score_parameter_desc' => null,
                    'parameter_wawancara' => null,
                ]);
                continue;
            }

            $lowestScore = null;
            $isAllCriteriaFilled = true; // Flag untuk memastikan kriteria lengkap

            foreach ($criterias as $criteria) {
                $criteriaScore = ScoreCriteria::where('period_id', $periodId)
                    ->where('parameter_criteria_id', $criteria->id)
                    ->whereNull('deleted_at')
                    ->first();

                if ($criteriaScore && $this->isCriteriaFilled($criteriaScore->score)) {
                    if ($this->isScorableCriteriaScore($criteriaScore->score)) {
                        if ($lowestScore === null || $criteriaScore->score < $lowestScore) {
                            $lowestScore = $criteriaScore->score;
                        }
                    }
                } else {
                    $isAllCriteriaFilled = false;
                }
            }

            if ($request->action === 'finish' || ($request->action === 'save' && $isAllCriteriaFilled)) {
                ScoreParameter::where('period_id', $periodId)->where('parameter_id', $parameter->id)->delete();

                if ($lowestScore !== null) {
                    $scoreDesc = $this->getScoreParameterDesc($lowestScore);
                    $prioritasWawancara = $this->getPrioritasWawancara($lowestScore);

                    ScoreParameter::create([
                        'period_id' => $periodId,
                        'parameter_id' => $parameter->id,
                        'sub_dimension_id' => $parameter->sub_dimension_id,
                        'score' => $lowestScore,
                        'score_parameter_desc' => $scoreDesc,
                        'parameter_wawancara' => $prioritasWawancara,
                    ]);
                }
            } else {
                // (Opsional) Jika action Save dan kriteria belum lengkap, hapus nilai parameter sementara yang sebelumnya pernah ada agar tidak bias
                ScoreParameter::where('period_id', $periodId)->where('parameter_id', $parameter->id)->delete();
            }
        }

        // Kalkulasi Skor Dimensi dan Skor RMI berjalan tiap kali ada simpan data
        $this->calculateDimensionScores($periodId);
        $this->calculateRMIScore($periodId);

        // =========================================================================
        // 4. RESPON BERDASARKAN ACTION
        // =========================================================================

        if ($request->action === 'finish') {
            // Update status periode menjadi selesai
            $period->update([
                'status' => 2,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penilaian Aspek Dimensi berhasil diselesaikan.'
                ]);
            }

            return redirect()->route('penilaian-rmi.index')
                ->with('success', 'Penilaian Aspek Dimensi berhasil diselesaikan.');
        }

        // Response untuk simpan sementara
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan sementara.'
            ]);
        }

        return redirect()->route('penilaian-rmi.index')
            ->with('success', 'Penilaian Aspek Dimensi berhasil disimpan sementara.');
    }

    /**
     * Menghitung skor dimensi berdasarkan skor parameter
     */
    private function calculateDimensionScores_old($periodId)
    {
        // Ambil semua sub dimensi
        $subDimensions = SubDimension::all();

        foreach ($subDimensions as $subDimension) {
            // Ambil semua parameter untuk sub dimensi ini
            $parameters = MeasurementParameter::where('sub_dimension_id', $subDimension->id)->get();

            if ($parameters->isEmpty()) {
                continue;
            }

            // Ambil skor parameter untuk periode ini
            $parameterScores = ScoreParameter::where('period_id', $periodId)
                ->whereIn('parameter_id', $parameters->pluck('id'))
                ->whereNull('deleted_at')
                ->get();

            if ($parameterScores->isEmpty()) {
                continue;
            }

            // Hitung rata-rata skor parameter
            $totalScore = 0;
            $countScore = 0;

            foreach ($parameterScores as $parameterScore) {
                if ($parameterScore->score !== null) {
                    $totalScore += $parameterScore->score;
                    $countScore++;
                }
            }

            if ($countScore > 0) {
                $averageScore = $totalScore / $countScore;

                // Tentukan deskripsi skor dimensi
                $scoreDimensionDesc = $this->getScoreDimensionDesc($averageScore);

                // Soft delete skor dimensi lama jika ada
                DimensionAspectEvaluation::where('sub_dimension_id', $subDimension->id)
                    ->delete();

                // Buat skor dimensi baru
                DimensionAspectEvaluation::create([
                    'sub_dimension_id' => $subDimension->id,
                    'score_dimension' => $averageScore,
                    'score_dimension_desc' => $scoreDimensionDesc,
                ]);
            }
        }
    }

    private function calculateDimensionScores($periodId)
    {
        // Ambil semua dimensi
        $dimensions = Dimension::all();

        foreach ($dimensions as $dimension) {
            // Ambil semua sub dimensi untuk dimensi ini
            $subDimensions = SubDimension::where('dimension_id', $dimension->id)->get();

            if ($subDimensions->isEmpty()) {
                continue;
            }

            // Ambil semua parameter untuk sub dimensi ini
            $parameterIds = MeasurementParameter::whereIn('sub_dimension_id', $subDimensions->pluck('id'))->pluck('id');

            if ($parameterIds->isEmpty()) {
                continue;
            }

            // Ambil skor parameter untuk periode ini
            $parameterScores = ScoreParameter::where('period_id', $periodId)
                ->whereIn('parameter_id', $parameterIds)
                ->whereNull('deleted_at')
                ->get();

            if ($parameterScores->isEmpty()) {
                continue;
            }

            // Hitung rata-rata skor parameter untuk dimensi ini
            $totalScore = 0;
            $countScore = 0;

            foreach ($parameterScores as $parameterScore) {
                if ($this->isScorableCriteriaScore($parameterScore->score)) {
                    $totalScore += $parameterScore->score;
                    $countScore++;
                }
            }

            if ($countScore > 0) {
                $averageScore = $totalScore / $countScore;

                $scoreDimensionDesc = $this->getScoreDimensionDesc($averageScore);

                DimensionAspectEvaluation::where('period_id', $periodId)
                    ->where('dimension_id', $dimension->id)
                    ->delete();

                DimensionAspectEvaluation::create([
                    'period_id' => $periodId,
                    'dimension_id' => $dimension->id,
                    'score_dimension' => $averageScore,
                    'score_dimension_desc' => $scoreDimensionDesc,
                ]);
            } else {
                DimensionAspectEvaluation::where('period_id', $periodId)
                    ->where('dimension_id', $dimension->id)
                    ->delete();
            }
        }
    }

    /**
     * Menghitung skor RMI berdasarkan skor dimensi
     */
    private function calculateRMIScore_old($periodId)
    {
        // Ambil semua dimensi
        $dimensions = Dimension::all();
        $totalScore = 0;
        $countDimension = 0;

        foreach ($dimensions as $dimension) {
            // Ambil semua sub dimensi untuk dimensi ini
            $subDimensions = SubDimension::where('dimension_id', $dimension->id)->get();

            if ($subDimensions->isEmpty()) {
                continue;
            }

            // Ambil skor dimensi untuk sub dimensi ini
            $dimensionScores = DimensionAspectEvaluation::whereIn('sub_dimension_id', $subDimensions->pluck('id'))
                ->whereNull('deleted_at')
                ->get();

            if ($dimensionScores->isEmpty()) {
                continue;
            }

            // Hitung rata-rata skor dimensi
            $dimensionTotalScore = 0;
            $dimensionCount = 0;

            foreach ($dimensionScores as $dimensionScore) {
                if ($dimensionScore->score_dimension !== null) {
                    $dimensionTotalScore += $dimensionScore->score_dimension;
                    $dimensionCount++;
                }
            }

            if ($dimensionCount > 0) {
                $dimensionAverageScore = $dimensionTotalScore / $dimensionCount;
                $totalScore += $dimensionAverageScore;
                $countDimension++;
            }
        }

        // Hitung skor RMI
        if ($countDimension > 0) {
            $rmiScore = $totalScore / $countDimension;

            // Tentukan deskripsi skor RMI
            $rmiScoreDesc = $this->getScoreRMIDesc($rmiScore);

            // Update skor RMI pada periode
            $period = RMIPeriod::findOrFail($periodId);
            $period->update([
                'score_rmi' => $rmiScore,
                'score_rmi_desc' => $rmiScoreDesc,
            ]);
        }
    }

    /**
     * Menghitung skor RMI berdasarkan skor parameter secara keseluruhan
     */
    private function calculateRMIScore($periodId)
    {
        // Ambil semua skor parameter untuk periode ini
        $parameterScores = ScoreParameter::where('period_id', $periodId)
            ->whereNull('deleted_at')
            ->get();

        $totalScore = 0;
        $countScore = 0;

        foreach ($parameterScores as $parameterScore) {
            if ($this->isScorableCriteriaScore($parameterScore->score)) {
                $totalScore += $parameterScore->score;
                $countScore++;
            }
        }

        // Hitung skor RMI
        if ($countScore > 0) {
            $rmiScore = $totalScore / $countScore;

            // Tentukan deskripsi skor RMI
            $rmiScoreDesc = $this->getScoreRMIDesc($rmiScore);

            // Update skor RMI pada periode
            $period = RMIPeriod::findOrFail($periodId);
            $period->update([
                'score_rmi' => $rmiScore,
                'score_rmi_desc' => $rmiScoreDesc,
            ]);
        }
    }

    private function isCriteriaFilled($score): bool
    {
        return $score !== null && $score !== '';
    }

    private function isScorableCriteriaScore($score): bool
    {
        return is_numeric($score) && (int) $score >= 1 && (int) $score <= 5;
    }

    /**
     * Mendapatkan deskripsi skor dimensi berdasarkan nilai skor
     */
    private function getScoreDimensionDesc($score)
    {
        if ($score > 0 && $score <= 1.395) {
            return "Fase Awal";
        } elseif ($score > 1.395 && $score <= 1.795) {
            return "Fase Awal (+)";
        } elseif ($score > 1.795 && $score <= 2.395) {
            return "Fase Berkembang";
        } elseif ($score > 2.395 && $score <= 2.795) {
            return "Fase Berkembang (+)";
        } elseif ($score > 2.795 && $score <= 3.395) {
            return "Fase Praktik yang Baik";
        } elseif ($score > 3.395 && $score <= 3.795) {
            return "Fase Praktik yang Baik (+)";
        } elseif ($score > 3.795 && $score <= 4.395) {
            return "Fase Praktik yang Lebih Baik";
        } elseif ($score > 4.395 && $score <= 4.795) {
            return "Fase Praktik yang Lebih Baik (+)";
        } elseif ($score > 4.795 && $score <= 5) {
            return "Fase Praktik Terbaik";
        } else {
            return "n/a";
        }
    }

    /**
     * Mendapatkan deskripsi skor RMI berdasarkan nilai skor
     */
    private function getScoreRMIDesc($score)
    {
        if ($score > 0 && $score <= 1.395) {
            return "Fase Awal";
        } elseif ($score > 1.395 && $score <= 1.795) {
            return "Fase Awal (+)";
        } elseif ($score > 1.795 && $score <= 2.395) {
            return "Fase Berkembang";
        } elseif ($score > 2.395 && $score <= 2.795) {
            return "Fase Berkembang (+)";
        } elseif ($score > 2.795 && $score <= 3.395) {
            return "Fase Praktik yang Baik";
        } elseif ($score > 3.395 && $score <= 3.795) {
            return "Fase Praktik yang Baik (+)";
        } elseif ($score > 3.795 && $score <= 4.395) {
            return "Fase Praktik yang Lebih Baik";
        } elseif ($score > 4.395 && $score <= 4.795) {
            return "Fase Praktik yang Lebih Baik (+)";
        } elseif ($score > 4.795 && $score <= 5) {
            return "Fase Praktik Terbaik";
        } else {
            return "n/a";
        }
    }

    /**
     * Mendapatkan deskripsi skor parameter berdasarkan nilai skor
     */
    private function getScoreParameterDesc($score)
    {
        if ($score > 0 && $score <= 1.395) {
            return "Fase Awal";
        } elseif ($score > 1.395 && $score <= 1.795) {
            return "Fase Awal (+)";
        } elseif ($score > 1.795 && $score <= 2.395) {
            return "Fase Berkembang";
        } elseif ($score > 2.395 && $score <= 2.795) {
            return "Fase Berkembang (+)";
        } elseif ($score > 2.795 && $score <= 3.395) {
            return "Fase Praktik yang Baik";
        } elseif ($score > 3.395 && $score <= 3.795) {
            return "Fase Praktik yang Baik (+)";
        } elseif ($score > 3.795 && $score <= 4.395) {
            return "Fase Praktik yang Lebih Baik";
        } elseif ($score > 4.395 && $score <= 4.795) {
            return "Fase Praktik yang Lebih Baik (+)";
        } elseif ($score > 4.795 && $score <= 5) {
            return "Fase Praktik Terbaik";
        } else {
            return "n/a";
        }
    }

    /**
     * Mendapatkan prioritas wawancara berdasarkan nilai skor
     */
    private function getPrioritasWawancara($score)
    {
        if ($score >= 1 && $score <= 2) {
            return "Prioritas Tinggi";
        } elseif ($score > 2 && $score <= 3) {
            return "Prioritas Menengah";
        } elseif ($score > 3 && $score <= 4) {
            return "Prioritas Rendah";
        } elseif ($score > 4 && $score <= 5) {
            return "Tidak Wawancara";
        } else {
            return null;
        }
    }

    /**
     * Mendapatkan data gap analysis dan dokumen untuk suatu kriteria
     */
    public function getGapAnalysis($criteriaId)
    {
        $scoreCriteria = ScoreCriteria::with('documents')
            ->where('parameter_criteria_id', $criteriaId)
            ->whereNull('deleted_at')
            ->first();

        return response()->json([
            'gap_analysis' => $scoreCriteria ? $scoreCriteria->gap_analysis : '',
            'documents' => $scoreCriteria ? $scoreCriteria->documents : []
        ]);
    }

    /**
     * Menghapus dokumen gap analysis
     */
    public function deleteDocument($docId)
    {
        $document = ScoreCriteriaDoc::findOrFail($docId);
        Storage::disk('public')->delete($document->path);
        $document->delete();

        return response()->json(['success' => true]);
    }

    //Aspek Kinerja
    public function aspekKinerja($id)
    {
        // 1. Ambil periode
        $period = RMIPeriod::findOrFail($id);

        // 2. Ambil parameter untuk dua jenis form
        $paramsCapaian = ParameterKinerja::capaian()
            ->with('children.options','options')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $paramsKpmr = ParameterKinerja::kpmr()
            ->with('children.options','options')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        // 3. Cek apakah user sudah pernah menyimpan penilaian ini
        $penilaian = PenilaianCapaianKinerja::firstWhere([
            'user_id'       => auth()->id(),
            'rmi_period_id' => $id,
        ]);

        // 4. Siapkan array existing untuk prefill
        $existing = $penilaian
            ? $penilaian->details->pluck('option_id','parameter_id')->toArray()
            : [];

        $existingComments = $penilaian
            ? $penilaian->details->pluck('comment','parameter_id')->toArray()
            : [];


        $finalRatings = FinalRating::orderBy('rating')->get();
        $finalRatingPeriod = FinalRatingPeriod::where('rmi_period_id', $id)->first();

        $units = \App\Models\Unit::whereIn('unit_type_id', [1, 2, 4])
                ->orderBy('unit_type_id', 'desc')
                ->orderBy('name')
                ->get();

        $corporateUnit = $units->firstWhere('unit_type_id', 4);
        $defaultUnitId = $corporateUnit ? $corporateUnit->id : ($units->first()->id ?? 0);

        return view('penilaian-rmi.penilaian-aspek-kinerja', compact(
            'period',
            'paramsCapaian',
            'paramsKpmr',
            'existing',
            'existingComments',
            'finalRatings',
            'finalRatingPeriod',
            'units',
            'defaultUnitId',
        ));
    }

    public function storeAspekKinerja(Request $request, $periodId)
    {
        // 1. Validasi
        // $data = $request->validate([
        //     'responses'   => 'required|array',
        //     'responses.*' => 'required|integer|exists:pilihan_parameter_kinerjas,id',
        //     'comments.*'  => 'nullable|string',
        //     'action'      => 'required|string|in:save_capaian,back_to_capaian,finish,save_kpmr,finish_final_rating,back_to_kpmr',
        //     'final_rating_id' => 'required_if:action,finish_final_rating|exists:final_ratings,id',

        // ]);

        // Validasi dasar
        $rules = [
            'responses'   => 'required|array',
            'responses.*' => 'required|integer|exists:pilihan_parameter_kinerjas,id',
            'comments.*'  => 'nullable|string',
            'action'      => 'required|string|in:save_capaian,back_to_capaian,finish,save_kpmr,finish_final_rating,back_to_kpmr',
        ];

        // Tambahkan validasi final_rating_id hanya jika action adalah finish_final_rating
        if ($request->action == 'finish_final_rating') {
            $rules['final_rating_id'] = 'required|exists:final_ratings,id';
            $rules['documents'] = 'nullable|array';
            $rules['documents.*'] = 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120';
            $rules['document_descriptions'] = 'nullable|array';
            $rules['document_descriptions.*'] = 'nullable|string|max:255';
        }

        $data = $request->validate($rules);

        // 2. Ambil atau buat master Penilaian
        $penilaian = PenilaianCapaianKinerja::updateOrCreate(
            ['user_id'=>auth()->id(), 'rmi_period_id'=>$periodId],
            []
        );

        // 3. Differential update detail
        $existing = $penilaian->details->keyBy('parameter_id');
        foreach($data['responses'] as $paramId => $optId) {
            $comment = $data['comments'][$paramId] ?? null;

            if ($existing->has($paramId)) {
                $det = $existing->get($paramId);
                if ($det->option_id!=$optId || $det->comment!=$comment) {
                    $det->update(['option_id'=>$optId,'comment'=>$comment]);
                }
                $existing->forget($paramId);
            } else {
                $penilaian->details()->create([
                    'parameter_id'=>$paramId,
                    'option_id'=>$optId,
                    'comment'=>$comment,
                ]);
            }
        }
        // Hapus yang terhapus user
        foreach($existing as $det) {
            $det->delete();
        }

        // 4. Hitung total & skala Capaian Kinerja
        $totalCapaian = 0;
        $paramsCap = ParameterKinerja::capaian()
                    ->with('children')
                    ->whereNull('parent_id')
                    ->orderBy('code')->get();

        foreach($paramsCap as $param) {
            if ($param->children->isEmpty()) {
                $d = $penilaian->details->firstWhere('parameter_id',$param->id);
                if ($d) {
                    $totalCapaian += $d->pilihan->score * ($param->weight/100);
                }
            } else {
                $subSum = 0;
                foreach($param->children as $child) {
                    $d = $penilaian->details->firstWhere('parameter_id',$child->id);
                    if ($d) {
                        $subSum += $d->pilihan->score * ($child->weight/100);
                    }
                }
                $totalCapaian += $subSum * ($param->weight/100);
            }
        }

        $totalCapaian = round($totalCapaian,2);

        $skalaCap = SkalaKinerja::where(function($q) use($totalCapaian){
                        $q->whereNull('min')->orWhere('min','<=',(int) floor($totalCapaian));
                    })
                    ->where(function($q) use($totalCapaian){
                        $q->whereNull('max')->orWhere('max','>',(int) floor($totalCapaian));
                    })
                    ->first();

        // 5. Hitung total & skala KPMR
        $totalKpmr = 0;
        $paramsKpmr = ParameterKinerja::kpmr()
                    ->with('children')
                    ->whereNull('parent_id')
                    ->orderBy('code')->get();

        foreach($paramsKpmr as $param) {
            if ($param->children->isEmpty()) {
                $d = $penilaian->details->firstWhere('parameter_id',$param->id);
                if ($d) {
                    $totalKpmr += $d->pilihan->score * ($param->weight/100);
                }
            } else {
                $subSum = 0;
                foreach($param->children as $child) {
                    $d = $penilaian->details->firstWhere('parameter_id',$child->id);
                    if ($d) {
                        $subSum += $d->pilihan->score * ($child->weight/100);
                    }
                }
                $totalKpmr += $subSum * ($param->weight/100);
            }
        }

        $totalKpmr = round($totalKpmr,2);

        $skalaKpmr = SkalaKPMR::where(function($q) use($totalKpmr){
                        $q->whereNull('min')->orWhere('min','<=',(int) floor($totalKpmr));
                    })
                    ->where(function($q) use($totalKpmr){
                        $q->whereNull('max')->orWhere('max','>',(int) floor($totalKpmr));
                    })
                    ->first();

        // 6. Simpan total & skala di Penilaian
        $penilaian->update([
            'total_nilai_capaian_kinerja' => $totalCapaian,
            'capaian_kinerja'             => $skalaCap?->id,
            'total_nilai_kpmr'            => $totalKpmr,
            'kpmr'                         => $skalaKpmr?->id,
        ]);

        // 7. Hitung Composite Rank & Conversion
        $map = [
        1=>[1=>1,2=>1,3=>2,4=>3,5=>3],
        2=>[1=>1,2=>2,3=>2,4=>3,5=>4],
        3=>[1=>2,2=>2,3=>3,4=>4,5=>4],
        4=>[1=>2,2=>3,3=>4,4=>4,5=>5],
        5=>[1=>3,2=>3,3=>4,4=>5,5=>5],
        ];
        $tCap  = $skalaCap?->id;
        $tKpmr = $skalaKpmr?->id;
        $rank  = $map[$tCap][$tKpmr] ?? null;

        $convMap = [1=>100,2=>78,3=>55,4=>33,5=>10];
        $conv  = $convMap[$rank] ?? 0;

        // 8. Update RMIPeriod
        RMIPeriod::where('id',$periodId)->update([
            'kinerja'                   => $skalaCap?->tingkat,
            'kpmr'                      => $skalaKpmr?->tingkat,
            'peringkat_komposit_risiko'=> $rank,
            'nilai_konversi'            => $conv,
        ]);

        if ($data['action'] == 'finish_final_rating' && isset($data['final_rating_id'])) {
            $finalRatingId = $data['final_rating_id'];
            $finalRating = FinalRating::findOrFail($finalRatingId);
            $period = RMIPeriod::findOrFail($periodId);

            // Hitung score_bobot_konversi (50% dari conversion_score)
            $scoreBobotKonversi = $finalRating->conversion_score * 0.5;

            // Hitung total_score_kinerja (score_bobot_konversi + 50% dari nilai_konversi)
            $totalScoreKinerja = $scoreBobotKonversi + ($period->nilai_konversi * 0.5);

            // Tentukan penyesuaian_skor_aspek_dimensi berdasarkan total_score_kinerja
            $penyesuaianSkor = 0;
            if ($totalScoreKinerja > 90) {
                $penyesuaianSkor = 0;
            } elseif ($totalScoreKinerja > 80) {
                $penyesuaianSkor = -0.25;
            } elseif ($totalScoreKinerja > 65) {
                $penyesuaianSkor = -0.5;
            } elseif ($totalScoreKinerja > 50) {
                $penyesuaianSkor = -0.75;
            } else {
                $penyesuaianSkor = -1;
            }

            // Hitung final_score_rmi jika nilai_score_rmi >= 3
            $finalScoreRmi = $period->score_rmi;
            if ($period->score_rmi >= 3) {
                $finalScoreRmi = $period->score_rmi + $penyesuaianSkor;
            }

            // Simpan atau update FinalRatingPeriod
            FinalRatingPeriod::updateOrCreate(
                ['rmi_period_id' => $periodId],
                [
                    'final_rating_id' => $finalRatingId,
                    'bobot' => 50, // 50%
                    'score_bobot_konversi' => $scoreBobotKonversi,
                    'total_score_kinerja' => $totalScoreKinerja
                ]
            );

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $file) {
                    if ($file && $file->isValid()) {
                        $originalFilename = $file->getClientOriginalName();
                        $path = $file->store("rmi_period_docs/{$periodId}", 'public');

                        RMIPeriodDocument::create([
                            'rmi_period_id' => $periodId,
                            'file_name' => $originalFilename,
                            'file_path' => $path,
                            'mimetype' => $file->getMimeType(),
                            'description' => $data['document_descriptions'][$index] ?? null,
                        ]);
                    }
                }
            }

            // Update RMIPeriod
            $period->update([
                'score_aspek_kinerja' => $totalScoreKinerja,
                'adjusment_score' => $penyesuaianSkor,
                'final_score_rmi' => $finalScoreRmi
            ]);

            return redirect()->route('penilaian-rmi.index')
                            ->with('success', 'Penilaian Aspek Kinerja, KPMR, dan Final Rating selesai disimpan.');
        }

        // 9. Redirect sesuai action
        switch($data['action']) {
        case 'save_capaian':
            return redirect()->route('penilaian-rmi.aspek-kinerja',$periodId)
                            ->with('success','Capaian Kinerja disimpan.')
                            ->with('active_tab','kpmr')
                            ->withInput();
        case 'back_to_capaian':
            return redirect()->route('penilaian-rmi.aspek-kinerja',$periodId)
                            ->with('active_tab','capaian');

        case 'save_kpmr':
            return redirect()->route('penilaian-rmi.aspek-kinerja',$periodId)
                            ->with('success','Penilaian KPMR disimpan.')
                            ->with('active_tab','final_rating')
                            ->withInput();
        case 'back_to_kpmr':
            return redirect()->route('penilaian-rmi.aspek-kinerja',$periodId)
                            ->with('active_tab','kpmr');
        case 'finish_final_rating':
            // Tambahkan logika untuk menyimpan final rating jika diperlukan
            return redirect()->route('penilaian-rmi.index')
                            ->with('success','Penilaian Aspek Kinerja, KPMR, dan Final Rating sudah selesai disimpan.');
        case 'finish':
        default:
            return redirect()->route('penilaian-rmi.index')
                            ->with('success','Penilaian Aspek Kinerja & KPMR selesai disimpan.');
        }
    }

    public function deleteAspekKinerjaDocument($id, $docId)
    {
        $document = RMIPeriodDocument::findOrFail($docId);
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['success' => true, 'message' => 'Dokumen berhasil dihapus.']);
    }

    public function updatePenilaian(Request $request, RMIPeriod $period)
    {
        // 1. Validasi Input
        $rules = [
            // --- Data Internal (Metadata) ---
            'penilaian'     => 'nullable|string|max:255', // Nama Penilai Internal
            'tahun_dinilai' => 'nullable|integer|digits:4',

            // --- Data Eksternal (Input Manual) ---
            'penilai_external'             => 'nullable|string|max:255',
            'score_rmi_external'           => 'nullable|numeric|min:0',
            'score_aspek_kinerja_external' => 'nullable|numeric', // Input Manual

            // Dropdown Pilihan (ID dari tabel master)
            'kinerja_external_id'          => 'nullable|exists:skala_kinerjas,id',
            'kpmr_external_id'             => 'nullable|exists:skala_kpmrs,id',
        ];

        $validated = $request->validate($rules);

        // 2. Prepare Data Update (Internal)
        $dataToUpdate = [
            'penilaian'     => $validated['penilaian'],
            'tahun_dinilai' => $validated['tahun_dinilai'],
            'penilai_external' => $validated['penilai_external'],
        ];

        // 3. Logika Perhitungan Otomatis Data Eksternal

        // A. Score RMI & Deskripsi
        if (!is_null($request->score_rmi_external)) {
            $dataToUpdate['score_rmi_external'] = $request->score_rmi_external;
            // Generate Deskripsi otomatis sesuai range nilai (sama dengan logic internal)
            $dataToUpdate['score_rmi_external_desc'] = $this->getScoreRMIDesc($request->score_rmi_external);
        }

        // B. Matriks Kinerja & KPMR (Peringkat & Konversi)
        $adjusmentExternal = 0; // Default 0

        if ($request->filled('kinerja_external_id') && $request->filled('kpmr_external_id')) {
            $kId = $request->kinerja_external_id;
            $pId = $request->kpmr_external_id;

            // Simpan Label Text (Sesuai struktur DB existing)
            $skalaKinerja = SkalaKinerja::find($kId);
            $skalaKpmr    = SkalaKPMR::find($pId);

            $dataToUpdate['kinerja_external'] = $skalaKinerja->tingkat ?? null;
            $dataToUpdate['kpmr_external']    = $skalaKpmr->tingkat ?? null;

            // Hitung Peringkat Komposit (Matriks 5x5)
            // 1=Sangat Baik ... 5=Buruk
            $matrix = [
                1 => [1=>1, 2=>1, 3=>2, 4=>3, 5=>3],
                2 => [1=>1, 2=>2, 3=>2, 4=>3, 5=>4],
                3 => [1=>2, 2=>2, 3=>3, 4=>4, 5=>4],
                4 => [1=>2, 2=>3, 3=>4, 4=>4, 5=>5],
                5 => [1=>3, 2=>3, 3=>4, 4=>5, 5=>5],
            ];
            $peringkat = $matrix[$kId][$pId] ?? null;
            $dataToUpdate['peringkat_komposit_risiko_external'] = $peringkat;

            // Hitung Nilai Konversi
            $convMap = [1=>100, 2=>78, 3=>55, 4=>33, 5=>10];
            $dataToUpdate['nilai_konversi_external'] = $convMap[$peringkat] ?? 0;
        }

        // C. Adjustment Score (Berdasarkan Score Aspek Kinerja External)
        // Logika sama dengan 'storeAspekKinerja'
        if (!is_null($request->score_aspek_kinerja_external)) {
            $sak = $request->score_aspek_kinerja_external;
            $dataToUpdate['score_aspek_kinerja_external'] = $sak;

            if ($sak > 90)     $adjusmentExternal = 0;
            elseif ($sak > 80) $adjusmentExternal = -0.25;
            elseif ($sak > 65) $adjusmentExternal = -0.50;
            elseif ($sak > 50) $adjusmentExternal = -0.75;
            else               $adjusmentExternal = -1.00;

            $dataToUpdate['adjusment_score_external'] = $adjusmentExternal;
        }

        // D. Final Score RMI External
        // Logika: Final = Score RMI + Adjustment (Hanya jika Score RMI >= 3.00)
        if (!is_null($request->score_rmi_external)) {
            $scoreRmiExt = $request->score_rmi_external;
            $finalScore  = $scoreRmiExt;

            if ($scoreRmiExt >= 3) {
                $finalScore = $scoreRmiExt + $adjusmentExternal;
            }

            // Cap Max/Min
            if($finalScore > 5) $finalScore = 5;
            if($finalScore < 0) $finalScore = 0;

            $dataToUpdate['final_score_rmi_external'] = $finalScore;
        }

        // 4. Eksekusi Update
        $period->update($dataToUpdate);

        return redirect()->route('penilaian-rmi.index')
            ->with('success', 'Data Penilaian (Internal & Eksternal) berhasil diperbarui.');
    }

    public function getRiskData(Request $request, $id)
    {
        $unitId = $request->query('unit_id');

        if (!$unitId) {
            return response()->json(['error' => 'Unit ID required'], 400);
        }
        $period = RMIPeriod::findOrFail($id);
        $periode = Periode::where('tahun', $period->year)->first();

        if (!$periode) {
            return response()->json([
                'success' => false,
                'message' => 'Master data Periode untuk tahun ' . $period->year . ' tidak ditemukan di sistem.'
            ], 404);
        }

        // Ambil Risiko berdasarkan Periode dan Unit
        $risks = \App\Models\IdentifikasiRisiko::where('periode_id', $periode->id)
            ->where('unit_id', $unitId)
            ->whereNull('deleted_at')
            // ->whereIn('status_risiko', [3, 4, 5])
            ->with([
                'riskAnalysis',
                'penyebabRisiko.perlakuanPenyebabRisikoUnit.perlakuanPenyebabUnitMonitorings' => function($q) {
                    $q->orderBy('id', 'desc'); // Monitoring perlakuan terakhir
                },
                'monitoringRisikos' => function($q) {
                    $q->orderBy('quarter', 'desc')->orderBy('id', 'desc'); // Monitoring risiko terakhir
                }
            ])
            ->get();

        $data = [];
        $totalProgress = 0;
        $countPerlakuan = 0;

        foreach ($risks as $risk) {
            // Data untuk Tabel 1 (Eksposur)
            $lastMonitoring = $risk->monitoringRisikos->first();

            $detailUrl = '#';

            switch ($risk->unit_type_id) {
                case 4: // Korporat
                    $detailUrl = route('corporate-risk.view', $risk->id);
                    break;
                case 1: // Divisi / Unit
                    $detailUrl = route('risk-register-unit.view', $risk->id);
                    break;
                case 2: // Anak Perusahaan
                    $detailUrl = route('risk-register-ap.view', $risk->id);
                    break;
                default:
                    $detailUrl = route('risk-register-unit.view', $risk->id);
                    break;
            }

            $riskData = [
                'id' => $risk->id,
                'peristiwa_risiko' => $risk->peristiwa_risiko,
                'deskripsi' => $risk->deskripsi_peristiwa_risiko,
                'penyebab' => $risk->penyebabRisiko->pluck('penyebab_risiko')->toArray(),
                'inheren' => $risk->riskAnalysis->eksposur_risiko ?? 0,
                'residual_target' => $risk->riskAnalysis->eksposur_risiko_residual_q4 ?? 0,
                'realisasi' => $lastMonitoring ? ($lastMonitoring->eksposure_risiko ?? 0) : null,
                'realisasi_quarter' => $lastMonitoring ? $lastMonitoring->quarter : null,
                'detail_url' => $detailUrl,
                'perlakuans' => []
            ];

            // Data untuk Tabel 2 (Progress)
            foreach ($risk->penyebabRisiko as $penyebab) {
                foreach ($penyebab->perlakuanPenyebabRisikoUnit as $perlakuan) {
                    $lastProgress = $perlakuan->perlakuanPenyebabUnitMonitorings->first();
                    $progressVal = $lastProgress ? $lastProgress->progress_rencana_perlakuan_risiko : 0;

                    $riskData['perlakuans'][] = [
                        'penyebab' => $penyebab->penyebab_risiko,
                        'rencana' => $perlakuan->rencana_perlakuan_risiko,
                        'progress' => $progressVal
                    ];

                    $totalProgress += $progressVal;
                    $countPerlakuan++;
                }
            }

            $data[] = $riskData;
        }

        $averageProgress = $countPerlakuan > 0 ? round($totalProgress / $countPerlakuan, 2) : 0;

        return response()->json([
            'risks' => $data,
            'average_progress' => $averageProgress
        ]);
    }

    public function getEvidence($periodId, $parameterId)
    {
        $penilaian = PenilaianCapaianKinerja::where('user_id', auth()->id())
            ->where('rmi_period_id', $periodId)
            ->first();

        if (!$penilaian) {
            return response()->json(['documents' => []]);
        }

        $documents = ParameterKinerjaDocument::where('penilaian_capaian_kinerja_id', $penilaian->id)
            ->where('parameter_id', $parameterId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['documents' => $documents]);
    }

    public function storeEvidence(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period_id'     => 'required|exists:rmi_periods,id',
            'parameter_id'  => 'required|exists:parameter_kinerjas,id',
            'file'          => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120', // Max 5MB
            'description'   => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $penilaian = PenilaianCapaianKinerja::updateOrCreate(
                [
                    'user_id'       => auth()->id(),
                    'rmi_period_id' => $request->period_id
                ],
                []
            );

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $path = $file->store("evidence/{$request->period_id}/" . auth()->id(), 'public');

            $doc = ParameterKinerjaDocument::create([
                'penilaian_capaian_kinerja_id' => $penilaian->id,
                'parameter_id' => $request->parameter_id,
                'filename'     => $originalName,
                'file_path'    => $path,
                'mimetype'     => $file->getMimeType(),
                'description'  => $request->description,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diunggah.',
                'data'    => $doc
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function deleteEvidence($id)
    {
        $doc = ParameterKinerjaDocument::find($id);

        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak ditemukan.'], 404);
        }

        if ($doc->penilaian->user_id != auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            Storage::disk('public')->delete($doc->file_path);

            $doc->delete();

            return response()->json(['success' => true, 'message' => 'Dokumen dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }
}
