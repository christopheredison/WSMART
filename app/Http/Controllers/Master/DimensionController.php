<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Dimension;
use Illuminate\Http\Request;
use DataTables;

class DimensionController extends Controller
{
    public function index()
    {
        return view('master.dimension.index');
    }

    public function data()
    {
        $query = Dimension::orderBy('id', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
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
        $request->validate(['name' => 'required|string|max:255']);
        Dimension::create($request->only('name'));
        return response()->json(['success' => 'Data Dimensi berhasil disimpan.']);
    }

    public function edit($id)
    {
        $data = Dimension::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Dimension::findOrFail($id)->update($request->only('name'));
        return response()->json(['success' => 'Data Dimensi berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        Dimension::findOrFail($id)->delete();
        return response()->json(['success' => 'Data Dimensi berhasil dihapus.']);
    }
}
