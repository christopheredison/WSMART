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
use App\Models\DetailPenilaianCapaianKinerja;
use App\Models\SkalaKinerja;
use App\Models\SkalaKPMR;
use App\Models\FinalRating;
use App\Models\FinalRatingPeriod;

class PenilaianRMIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Ambil data periode RMI
        //$periods = RMIPeriod::orderBy('year', 'desc')->get();
        $periods = RMIPeriod::orderBy('year', 'desc')
               ->paginate(10);
        
        // Tambahkan status untuk setiap periode
        foreach ($periods as $period) {
            // Tentukan status periode berdasarkan field status di model
            if ($period->status == 1) {
                $period->status_text = 'Dalam Proses';
            } else {
                $period->status_text = 'Selesai';
            }
        }
        
        return view('penilaian-rmi.index', compact('periods'));
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
            'penilaianCapaianKinerja.details.pilihan'
        ])->findOrFail($id);

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

        $dimensionScores = DimensionAspectEvaluation::whereNull('deleted_at')
                            ->get()
                            ->keyBy('sub_dimension_id');

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
        'paramsKpmr'
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
        //\Log::info('Request files: ' . json_encode($request->allFiles()));
        //dd($request);
        // Validasi input berdasarkan action
        if ($request->action === 'finish') {
            $request->validate([
                'scores' => 'array',
                'scores.*' => 'required|numeric',
                'gap_analysis' => 'array',
                'gap_analysis.*' => 'required|string'
            ]);
        } else {
            // Validasi lebih longgar untuk simpan sementara
            $request->validate([
                'scores' => 'array',
                'scores.*' => 'nullable|numeric',
                'action' => 'required|in:save,finish',
                'gap_analysis' => 'array',
                'gap_analysis.*' => 'nullable|string'
            ]);
        }
        
        // Ambil periode
        $period = RMIPeriod::findOrFail($periodId);
        
        // Simpan skor kriteria dan gap analysis
        if ($request->has('scores')) {
            \Log::info('masuk ke dalam blok hasScores');
            foreach ($request->scores as $criteriaId => $score) {
                $gap = $request->gap_analysis[$criteriaId] ?? null;
                // Ambil data lama
                $existing = ScoreCriteria::where('period_id', $periodId)
                    ->where('parameter_criteria_id', $criteriaId)
                    ->first();
            
                // Cek apakah ada perubahan
                if ($existing && $existing->score == $score && $existing->gap_analysis == $gap) {
                    // Tidak ada perubahan, skip proses hapus dan insert
                    continue;
                }
            
                // Jika ada, hapus data lama
                ScoreCriteria::where('period_id', $periodId)
                    ->where('parameter_criteria_id', $criteriaId)
                    ->delete();
            
                // Buat skor baru dengan gap analysis
                ScoreCriteria::create([
                    'period_id' => $periodId,
                    'parameter_criteria_id' => $criteriaId,
                    'score' => $score,
                    'gap_analysis' => $gap
                ]);
            }
        }

        // Handle upload dokumen jika ada
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
        
        // Jika action adalah finish
        if ($request->action === 'finish') {
            // Ambil semua parameter
            $parameters = MeasurementParameter::all();
            
            // Hitung skor untuk setiap parameter
            foreach ($parameters as $parameter) {
                // Ambil semua kriteria untuk parameter ini
                $criterias = ParameterCriteria::where('parameter_id', $parameter->id)->get();
                
                // Jika parameter tidak memiliki kriteria, set skor parameter menjadi null
                if ($criterias->isEmpty()) {
                    // Soft delete skor parameter lama jika ada
                    ScoreParameter::where('period_id', $periodId)
                        ->where('parameter_id', $parameter->id)
                        ->delete();
                        
                    // Buat skor parameter baru
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
                
                // Ambil skor terendah dari semua kriteria parameter
                $lowestScore = null;
                
                foreach ($criterias as $criteria) {
                    $criteriaScore = ScoreCriteria::where('period_id', $periodId)
                        ->where('parameter_criteria_id', $criteria->id)
                        ->whereNull('deleted_at')
                        ->first();
                    
                    if ($criteriaScore) {
                        if ($lowestScore === null || $criteriaScore->score < $lowestScore) {
                            $lowestScore = $criteriaScore->score;
                        }
                    }
                }
                
                // Jika ada skor terendah, simpan ke score_parameters
                if ($lowestScore !== null) {
                    // Tentukan deskripsi skor parameter
                    $scoreDesc = $this->getScoreParameterDesc($lowestScore);
                    
                    // Tentukan prioritas wawancara
                    $prioritasWawancara = $this->getPrioritasWawancara($lowestScore);
                    
                    // Soft delete skor parameter lama jika ada
                    ScoreParameter::where('period_id', $periodId)
                        ->where('parameter_id', $parameter->id)
                        ->delete();
                        
                    // Buat skor parameter baru
                    ScoreParameter::create([
                        'period_id' => $periodId,
                        'parameter_id' => $parameter->id,
                        'sub_dimension_id' => $parameter->sub_dimension_id,
                        'score' => $lowestScore,
                        'score_parameter_desc' => $scoreDesc,
                        'parameter_wawancara' => $prioritasWawancara,
                    ]);
                }
            }
            
            // Hitung skor dimensi
            $this->calculateDimensionScores($periodId);
            
            // Hitung skor RMI
            $this->calculateRMIScore($periodId);
            
            // Update status periode menjadi selesai
            $period->update([
                'status' => 2, // Selesai
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penilaian Aspek Dinamis berhasil diselesaikan.'
                ]);
            }
            
            return redirect()->route('penilaian-rmi.index')
                ->with('success', 'Penilaian Aspek Dinamis berhasil diselesaikan.');
        }
        
        // Untuk simpan sementara
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan sementara'
            ]);
        }
        
        return redirect()->route('penilaian-rmi.index')
            ->with('success', 'Penilaian Aspek Dinamis berhasil disimpan sementara.');
    }

    /**
     * Menghitung skor dimensi berdasarkan skor parameter
     */
    private function calculateDimensionScores($periodId)
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

    /**
     * Menghitung skor RMI berdasarkan skor dimensi
     */
    private function calculateRMIScore($periodId)
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
        
        return view('penilaian-rmi.penilaian-aspek-kinerja', compact(
            'period', 'paramsCapaian', 'paramsKpmr', 'existing', 'existingComments',
            'finalRatings', 'finalRatingPeriod'
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
            if ($totalScoreKinerja >= 90) {
                $penyesuaianSkor = 0.1;
            } elseif ($totalScoreKinerja >= 80) {
                $penyesuaianSkor = 0.075;
            } elseif ($totalScoreKinerja >= 70) {
                $penyesuaianSkor = 0.05;
            } elseif ($totalScoreKinerja >= 60) {
                $penyesuaianSkor = 0.025;
            } elseif ($totalScoreKinerja >= 50) {
                $penyesuaianSkor = 0;
            } elseif ($totalScoreKinerja >= 40) {
                $penyesuaianSkor = -0.025;
            } elseif ($totalScoreKinerja >= 30) {
                $penyesuaianSkor = -0.05;
            } elseif ($totalScoreKinerja >= 20) {
                $penyesuaianSkor = -0.075;
            } else {
                $penyesuaianSkor = -0.1;
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
}