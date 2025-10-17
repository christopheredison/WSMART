<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\UnitHasilUsaha;
use Illuminate\Http\Request;
use DataTables;

class UnitHasilUsahaController extends Controller
{
    public function index()
    {
        return view('master.unit-hasil-usaha.index');
    }

    public function data()
    {
        $query = UnitHasilUsaha::with('unit');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('unit_name', fn($row) => $row->unit->name ?? '-')
            ->addColumn('cost_center', fn($row) => $row->cost_center)
            ->editColumn('lsp_ri', fn($row) => 'Rp ' . number_format($row->lsp_ri, 0, ',', '.'))
            ->addColumn('action', function($row){
                $editBtn = '<button class="btn-input-icon btn-edit" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Edit"><i class="bx bx-edit"></i></button>';
                $deleteBtn = '<button class="btn-input-icon text-danger btn-delete ms-1" data-id="'.$row->id.'" data-bs-toggle="tooltip" title="Hapus"><i class="bx bx-trash"></i></button>';
                return $editBtn . $deleteBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'cost_center' => 'required|string|max:255',
            'period' => 'required|string|size:6|unique:unit_hasil_usaha,period,NULL,id,unit_id,' . $request->unit_id,
        ]);
        
        $data = $request->only((new UnitHasilUsaha)->getFillable());
        
        $kontrak = (float)($data['kontrak_review'] ?? 0);
        $penjualanRA = (float)($data['penjualan_ra'] ?? 0);
        $penjualanRI = (float)($data['penjualan_ri'] ?? 0);

        $data['progress_fisik_ra'] = ($kontrak > 0) ? ($penjualanRA / $kontrak) * 100 : 0;
        $data['progress_fisik_ri'] = ($kontrak > 0) ? ($penjualanRI / $kontrak) * 100 : 0;

        UnitHasilUsaha::create($data);

        return response()->json(['success' => 'Data berhasil disimpan.']);
    }

    public function edit($id)
    {
        $data = UnitHasilUsaha::findOrFail($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'cost_center' => 'required|string|max:255',
            'period' => 'required|string|size:6|unique:unit_hasil_usaha,period,' . $id . ',id,unit_id,' . $request->unit_id,
        ]);
        
        $dataToUpdate = $request->only((new UnitHasilUsaha)->getFillable());
        
        $kontrak = (float)($dataToUpdate['kontrak_review'] ?? 0);
        $penjualanRA = (float)($dataToUpdate['penjualan_ra'] ?? 0);
        $penjualanRI = (float)($dataToUpdate['penjualan_ri'] ?? 0);

        $dataToUpdate['progress_fisik_ra'] = ($kontrak > 0) ? ($penjualanRA / $kontrak) * 100 : 0;
        $dataToUpdate['progress_fisik_ri'] = ($kontrak > 0) ? ($penjualanRI / $kontrak) * 100 : 0;

        $data = UnitHasilUsaha::findOrFail($id);
        $data->update($dataToUpdate);
        
        return response()->json(['success' => 'Data berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        UnitHasilUsaha::destroy($id);
        return response()->json(['success' => 'Data berhasil dihapus.']);
    }
}