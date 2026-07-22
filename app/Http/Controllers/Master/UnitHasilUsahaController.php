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
            ->addColumn('period', fn($row) => $row->period ? preg_replace('/(\d{4})(\d{2})/', '$1-$2', $row->period) : '-')
            ->editColumn('kontrak_review', fn($row) => 'Rp ' . number_format($row->kontrak_review, 0, ',', '.'))
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
        $data = $this->sanitizeCurrency($data);

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
        $dataToUpdate = $this->sanitizeCurrency($dataToUpdate);

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

    private function sanitizeCurrency($data)
    {
        $currencyFields = [
            'kontrak_review',
            'penjualan_ra',
            'penjualan_ri',
            'lsp_review',
            'lsp_proyeksi',
            'lsp_ra',
            'lsp_ri'
        ];

        foreach ($currencyFields as $field) {
            if (isset($data[$field])) {
                $clean = str_replace('.', '', $data[$field]);
                $clean = str_replace(',', '.', $clean);
                $data[$field] = $clean;
            }
        }

        return $data;
    }
}
