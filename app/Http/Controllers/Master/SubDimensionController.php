<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SubDimension;
use App\Models\Dimension;
use Illuminate\Http\Request;
use DataTables;

class SubDimensionController extends Controller
{
    public function index()
    {
        $dimensions = Dimension::orderBy('id', 'asc')->get();
        return view('master.sub-dimension.index', compact('dimensions'));
    }

    public function data()
    {
        $query = SubDimension::select('sub_dimensions.*')
            ->join('dimensions', 'sub_dimensions.dimension_id', '=', 'dimensions.id')
            ->with('dimension')
            ->orderBy('dimensions.id', 'asc')
            ->orderBy('sub_dimensions.id', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('dimension_name', function($row){
                return $row->dimension->name ?? '-';
            })
            ->addColumn('action', function($row){
                $editBtn = '<button class="btn-input-icon btn-edit text-warning" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button class="btn-input-icon text-danger btn-delete ms-1" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Hapus"><i class="bx bx-trash"></i></button>';
                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dimension_id' => 'required|exists:dimensions,id',
            'name' => 'required|string|max:255'
        ]);
        SubDimension::create($request->only(['dimension_id', 'name']));
        return response()->json(['success' => 'Data Sub Dimensi berhasil disimpan.']);
    }

    public function edit($id)
    {
        $data = SubDimension::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'dimension_id' => 'required|exists:dimensions,id',
            'name' => 'required|string|max:255'
        ]);
        SubDimension::findOrFail($id)->update($request->only(['dimension_id', 'name']));
        return response()->json(['success' => 'Data Sub Dimensi berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        SubDimension::findOrFail($id)->delete();
        return response()->json(['success' => 'Data Sub Dimensi berhasil dihapus.']);
    }
}
