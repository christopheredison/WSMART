<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WBS;
use DataTables;

class WBSController extends Controller
{
    public function index()
    {
        return view('master.wbs.index');
    }

    public function data()
    {
        // $query = WBS::query()->latest();
        $query = WBS::orderBy('id', 'asc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status', function($row){
                if($row->is_active){
                    return '<span class="badge bg-success">Aktif</span>';
                }
                return '<span class="badge bg-danger">Tidak Aktif</span>';
            })
            ->addColumn('action', function($row){
                $editBtn = '<button class="btn-input-icon btn-edit" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button class="btn-input-icon text-danger btn-delete ms-1" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Hapus"><i class="bx bx-trash"></i></button>';
                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:w_b_s,code|max:50',
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only(['code', 'name', 'is_active']);
        // Jika checkbox tidak dicentang, kirim 0
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        WBS::create($data);

        return response()->json(['success' => 'Data WBS berhasil disimpan.']);
    }

    public function edit($id)
    {
        $data = WBS::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:w_b_s,code,' . $id,
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $wbs = WBS::findOrFail($id);

        $data = $request->only(['code', 'name', 'is_active']);
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $wbs->update($data);

        return response()->json(['success' => 'Data WBS berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        WBS::destroy($id);
        return response()->json(['success' => 'Data WBS berhasil dihapus.']);
    }
}
