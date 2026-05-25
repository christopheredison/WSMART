<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MeasurementParameter;
use App\Models\ParameterCriteria;
use App\Models\ParameterCriteriaDetail;
use App\Models\Dimension;
use DataTables;

class MeasurementParameterController extends Controller
{
    public function index()
    {
        $dimensions = Dimension::with('subDimensions')->orderBy('name', 'asc')->get();
        return view('master.measurement-parameter.index', compact('dimensions'));
    }

    public function data()
    {
        $query = MeasurementParameter::select('measurement_parameters.*')
            ->join('sub_dimensions', 'measurement_parameters.sub_dimension_id', '=', 'sub_dimensions.id')
            ->join('dimensions', 'sub_dimensions.dimension_id', '=', 'dimensions.id')
            ->with(['subDimension.dimension'])
            ->withCount('criteria')
            ->orderBy('dimensions.id', 'asc')
            ->orderBy('sub_dimensions.id', 'asc')
            ->orderBy('measurement_parameters.id', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('dimensi', function($row){
                return $row->subDimension->dimension->name ?? '-';
            })
            ->addColumn('sub_dimensi', function($row){
                return $row->subDimension->name ?? '-';
            })
            ->addColumn('criteria_count', function($row){
                $badgeColor = $row->criteria_count > 0 ? 'bg-success' : 'bg-secondary';
                return '<span class="badge ' . $badgeColor . '">' . $row->criteria_count . ' Kriteria</span>';
            })
            ->addColumn('action', function($row){
                $showBtn = '<a href="'.route('measurement-parameter.show', $row->id).'" class="btn-input-icon btn-show text-info" data-bs-toggle="tooltip" title="Lihat Detail & Set Kriteria"><i class="bx bx-list-check"></i></a>';
                $editBtn = '<button class="btn-input-icon btn-edit text-warning ms-1" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Edit Parameter"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button class="btn-input-icon text-danger btn-delete ms-1" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Hapus Parameter"><i class="bx bx-trash"></i></button>';

                return $showBtn . $editBtn . $deleteBtn;
            })
            ->rawColumns(['criteria_count', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_dimension_id' => 'required|exists:sub_dimensions,id',
            'statement' => 'required|string'
        ]);

        MeasurementParameter::create($request->only('sub_dimension_id', 'statement'));
        return response()->json(['success' => 'Parameter Pengukuran berhasil disimpan.']);
    }

    public function edit($id)
    {
        $data = MeasurementParameter::with('subDimension')->findOrFail($id);
        $data->dimension_id = $data->subDimension->dimension_id ?? null;
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sub_dimension_id' => 'required|exists:sub_dimensions,id',
            'statement' => 'required|string'
        ]);

        MeasurementParameter::findOrFail($id)->update($request->only('sub_dimension_id', 'statement'));
        return response()->json(['success' => 'Parameter Pengukuran berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        MeasurementParameter::findOrFail($id)->delete();
        return response()->json(['success' => 'Parameter Pengukuran berhasil dihapus.']);
    }

    public function show($id)
    {
        $parameter = MeasurementParameter::with('subDimension.dimension')->findOrFail($id);
        $criteriaCount = ParameterCriteria::where('parameter_id', $id)->count();
        $parameterCriterias = ParameterCriteria::where('parameter_id', $id)
            ->with(['details' => function($query) { $query->orderBy('level', 'asc'); }])
            ->get();

        return view('master.measurement-parameter.show', compact('parameter', 'criteriaCount', 'parameterCriterias'));
    }

    public function createCriteria($id)
    {
        $parameter = MeasurementParameter::findOrFail($id);
        $parameterCriteria = null;
        $criteriaByLevel = [];
        return view('master.measurement-parameter.set-criteria', compact('parameter', 'parameterCriteria', 'criteriaByLevel'));
    }

    public function storeCriteria(Request $request, $parameterId)
    {
        $request->validate([
            'min_score' => 'required|numeric|min:1',
            'max_score' => 'required|numeric|gte:min_score',
            'criteria_statement.5' => 'required|string',
            'criteria_statement.*' => 'nullable|string',
        ]);

        $parameter = MeasurementParameter::findOrFail($parameterId);
        $criteriaCount = ParameterCriteria::where('parameter_id', $parameterId)->count();
        $incrementNumber = str_pad($criteriaCount + 1, 4, '0', STR_PAD_LEFT);

        $parameterCriteria = ParameterCriteria::create([
            'parameter_id' => $parameterId,
            'criteria_statement' => $parameter->statement . '_' . $incrementNumber,
            'min_score' => $request->min_score,
            'max_score' => $request->max_score,
        ]);

        foreach ($request->criteria_statement as $level => $criteria) {
            if (!empty($criteria)) {
                ParameterCriteriaDetail::create([
                    'parameter_criteria_id' => $parameterCriteria->id,
                    'level' => $level,
                    'criteria' => $criteria,
                ]);
            }
        }

        return redirect()->route('measurement-parameter.show', $parameterId)->with('success', 'Kriteria berhasil ditambahkan.');
    }

    public function editCriteria($parameterId, $criteriaId)
    {
        $parameter = MeasurementParameter::findOrFail($parameterId);
        $parameterCriteria = ParameterCriteria::where('parameter_id', $parameterId)->findOrFail($criteriaId);

        $criteriaByLevel = [];
        foreach ($parameterCriteria->details()->get() as $detail) {
            $criteriaByLevel[$detail->level] = $detail;
        }

        return view('master.measurement-parameter.set-criteria', compact('parameter', 'parameterCriteria', 'criteriaByLevel'));
    }

    public function updateCriteria(Request $request, $parameterId, $criteriaId)
    {
        $request->validate([
            'min_score' => 'required|numeric|min:1',
            'max_score' => 'required|numeric|gte:min_score',
            'criteria_statement.5' => 'required|string',
            'criteria_statement.*' => 'nullable|string',
        ]);

        $parameterCriteria = ParameterCriteria::where('parameter_id', $parameterId)->findOrFail($criteriaId);
        $parameterCriteria->update([
            'min_score' => $request->min_score,
            'max_score' => $request->max_score,
        ]);

        foreach ($request->criteria_statement as $level => $criteria) {
            if (!empty($criteria)) {
                ParameterCriteriaDetail::updateOrCreate(
                    ['parameter_criteria_id' => $parameterCriteria->id, 'level' => $level],
                    ['criteria' => $criteria]
                );
            } else {
                ParameterCriteriaDetail::where('parameter_criteria_id', $parameterCriteria->id)->where('level', $level)->delete();
            }
        }

        return redirect()->route('measurement-parameter.show', $parameterId)->with('success', 'Kriteria berhasil diperbarui.');
    }

    public function deleteCriteria($parameterId, $criteriaId)
    {
        $criteria = ParameterCriteria::findOrFail($criteriaId);
        $criteria->details()->delete();
        $criteria->delete();

        return redirect()->back()->with('success', 'Kriteria berhasil dihapus.');
    }
}
