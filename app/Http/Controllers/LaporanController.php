<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Unit;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanUnitExport;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function unit()
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $units = Gate::check('risk_register_all_unit') ? Unit::all() : collect([auth()->user()->unit]);

        return view('laporan.unit', compact('periodes', 'units'));
    }

    public function unitExport(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'unit_id'    => 'required|exists:units,id',
        ]);

        try {
            $periodeId = $request->input('periode_id');
            $unitId    = $request->input('unit_id');

            $periode = Periode::find($periodeId);
            $unit    = Unit::find($unitId);

            $fileName = 'Laporan_Risk_Register_' . str_replace(' ', '_', $unit->name) . '_' . $periode->tahun . '.xlsx';

            $fileContents = Excel::raw(
                new LaporanUnitExport($periodeId, $unitId),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
            
            // return Excel::download(new LaporanUnitExport($periodeId, $unitId), $fileName);

        } catch (\Exception $e) {
            dd($e->getMessage());
            // Log::error('Gagal export laporan unit: ' . $e->getMessage());
            // return back()->with('error', 'Gagal membuat laporan Excel. Silakan coba lagi.');
        }
    }
}