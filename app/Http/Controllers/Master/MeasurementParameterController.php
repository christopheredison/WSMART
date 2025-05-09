<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MeasurementParameter;
use App\Models\ParameterCriteria;
use App\Models\SubDimension;
use Illuminate\Support\Facades\Validator;
use App\Models\ParameterCriteriaDetail;

class MeasurementParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = MeasurementParameter::with([
            'criteria' => function($query) {
                $query->orderBy('id', 'asc');
            }, 
            'subDimension',
            'subDimension.dimension'
        ]);
    
        // Filter berdasarkan kata kunci
        if ($request->has('keyword') && !empty($request->keyword)) {
            $keyword = $request->keyword;
            $query->where('statement', 'like', "%{$keyword}%")
                ->orWhereHas('criteria', function($q) use ($keyword) {
                    $q->where('criteria_statement', 'like', "%{$keyword}%");
                })
                ->orWhereHas('subDimension', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                })
                ->orWhereHas('subDimension.dimension', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
        }
    
        $parameters = $query->get();
    
        // Ambil data SubDimension untuk dropdown di modal
        $subDimensions = \App\Models\SubDimension::all();
    
        return view('master.measurement-parameter.index', compact('parameters', 'subDimensions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('master.measurement-parameter.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'sub_dimension_id' => 'required|exists:sub_dimensions,id',
            'statement' => 'required|string'
        ]);
        
        // Buat parameter baru
        $parameter = new MeasurementParameter();
        $parameter->sub_dimension_id = $request->sub_dimension_id;
        $parameter->statement = $request->statement;
        $parameter->min_score = null; // Awalnya null, akan diisi dari criteria
        $parameter->max_score = null; // Awalnya null, akan diisi dari criteria
        $parameter->save();
        
        // Jika request AJAX, kembalikan response JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Parameter berhasil ditambahkan',
                'data' => $parameter
            ]);
        }
        
        // Jika bukan AJAX, redirect dengan pesan sukses
        return redirect()->route('measurement-parameter.index')
            ->with('success', 'Parameter berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $parameter = MeasurementParameter::with([
            'criteria' => function($query) {
                $query->orderBy('id', 'asc');
            },
            'subDimension',
            'subDimension.dimension'
        ])->findOrFail($id);
    
        // Hitung jumlah kriteria
        $criteriaCount = $parameter->criteria->count();
        
        // Ambil semua kriteria parameter
        $parameterCriterias = ParameterCriteria::where('parameter_id', $id)
            ->with(['details' => function($query) {
                $query->orderBy('level', 'asc');
            }])
            ->get();
    
        return view('master.measurement-parameter.show', compact('parameter', 'criteriaCount', 'parameterCriterias'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $parameter = MeasurementParameter::with(['criteria' => function($query) {
            $query->orderBy('level', 'asc');
        }])->findOrFail($id);

        return view('master.measurement-parameter.edit', compact('parameter'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Implementasi update data
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $parameter = MeasurementParameter::findOrFail($id);
        $parameter->delete();

        return redirect()->route('measurement-parameter.index')
            ->with('success', 'Parameter berhasil dihapus');
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $parameter = MeasurementParameter::withTrashed()->findOrFail($id);
        $parameter->restore();

        return redirect()->route('measurement-parameter.index')
            ->with('success', 'Parameter berhasil dipulihkan');
    }

    /**
     * Menampilkan form untuk mengatur kriteria parameter
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function setCriteria($id)
    {
        $parameter = MeasurementParameter::findOrFail($id);
        
        // Ambil parameter criteria jika sudah ada
        $parameterCriteria = ParameterCriteria::where('parameter_id', $id)->first();
        
        // Siapkan array untuk menyimpan kriteria berdasarkan level
        $criteriaByLevel = [];
        
        // Jika parameter criteria sudah ada, ambil detailnya
        if ($parameterCriteria) {
            // Ambil semua detail kriteria
            $criteriaDetails = $parameterCriteria->details()->orderBy('level', 'asc')->get();
            
            // Susun berdasarkan level
            foreach ($criteriaDetails as $detail) {
                $criteriaByLevel[$detail->level] = $detail;
            }
        }
        
        return view('master.measurement-parameter.set-criteria', compact('parameter', 'parameterCriteria', 'criteriaByLevel'));
    }

    /**
     * Menyimpan kriteria parameter
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeCriteria(Request $request, $parameterId)
    {
        // Validasi input
        $validated = $request->validate([
            'min_score' => 'required|numeric|min:1', // Minimal 1
            'max_score' => 'required|numeric|gt:min_score',
            'criteria_statement.5' => 'required|string', // Level 5 wajib diisi
            'criteria_statement.*' => 'nullable|string',
        ]);
        
        // Cari parameter
        $parameter = MeasurementParameter::findOrFail($parameterId);
        
        // Hitung jumlah kriteria yang sudah ada untuk parameter ini
        $criteriaCount = ParameterCriteria::where('parameter_id', $parameterId)->count();
        
        // Buat format increment untuk kriteria baru (0001, 0002, dst)
        $incrementNumber = str_pad($criteriaCount + 1, 4, '0', STR_PAD_LEFT);
        
        // Buat kriteria baru (tidak menggunakan updateOrCreate)
        $parameterCriteria = ParameterCriteria::create([
            'parameter_id' => $parameterId,
            'criteria_statement' => $parameter->statement . '_' . $incrementNumber,
            'min_score' => $validated['min_score'],
            'max_score' => $validated['max_score'],
        ]);
        
        // Simpan detail untuk setiap level
        foreach ($request->criteria_statement as $level => $criteria) {
            if (!empty($criteria)) {
                ParameterCriteriaDetail::create([
                    'parameter_criteria_id' => $parameterCriteria->id,
                    'level' => $level,
                    'criteria' => $criteria,
                ]);
            }
        }
        
        return redirect()->route('measurement-parameter.index')
            ->with('success', 'Kriteria parameter berhasil disimpan.');
    }
}
