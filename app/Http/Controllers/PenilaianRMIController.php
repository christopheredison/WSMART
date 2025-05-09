<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RMIPeriod; // Perhatikan nama model yang benar (RMIPeriod bukan RmiPeriod)
use Illuminate\Support\Facades\DB;
use App\Models\Dimension;
use App\Models\ScoreCriteria;
use App\Models\MeasurementParameter;
use App\Models\ParameterCriteria;
use App\Models\ScoreParameter;
use App\Models\SubDimension;
use App\Models\DimensionAspectEvaluation;

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
        $periods = RMIPeriod::orderBy('year', 'desc')->get();
        
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
    public function show($id)
    {
        $period = RmiPeriod::findOrFail($id);
        
        // Ambil data dimensi, sub dimensi, parameter, dan kriteria
        $dimensions = Dimension::with([
            'subDimensions.parameters.criterias',
            'subDimensions.score',
            'parameters.score',
            'score'
        ])->where('period_id', $id)->get();
        
        return view('penilaian-rmi.show', compact('period', 'dimensions'));
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
        $scores = ScoreCriteria::where('period_id', $id)
            ->pluck('score', 'parameter_criteria_id')
            ->toArray();
        
        return view('penilaian-rmi.penilaian-aspek-dinamis', compact('period', 'dimensions', 'scores'));
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
        // Validasi input
        $request->validate([
            'scores' => 'array',
            'scores.*' => 'nullable|numeric',
            'action' => 'required|in:save,finish',
        ]);
        
        // Ambil periode
        $period = RMIPeriod::findOrFail($periodId);
        
        // Simpan skor kriteria
        if ($request->has('scores')) {
            foreach ($request->scores as $criteriaId => $score) {
                // Soft delete skor lama jika ada
                ScoreCriteria::where('period_id', $periodId)
                    ->where('parameter_criteria_id', $criteriaId)
                    ->delete();
                    
                // Buat skor baru
                ScoreCriteria::create([
                    'period_id' => $periodId,
                    'parameter_criteria_id' => $criteriaId,
                    'score' => $score,
                ]);
            }
        }
        
        // Jika action adalah finish, hitung skor parameter dan update status periode
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
            
            $message = 'Penilaian Aspek Dinamis berhasil diselesaikan.';
        } else {
            $message = 'Penilaian Aspek Dinamis berhasil disimpan sementara.';
        }
        
        return redirect()->route('penilaian-rmi.index')
            ->with('success', $message);
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
}