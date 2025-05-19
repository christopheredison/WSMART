<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Periode;
use Illuminate\Support\Facades\Validator;

class PeriodeController extends Controller
{
    public function index()
    {
        $periode = Periode::withTrashed()->get();
        return view('master.periode.index', compact('periode'));
    }

    public function changeActivePeriod(Request $request)
    {
        $periode = Periode::find($request->periode);
        if ($periode) {
            Periode::where('status', 'active')->update(['status' => 'non-active']);
            $periode->update(['status' => 'active']);
            return redirect()->back()->with('success', 'Status periode berhasil diubah.');
        } else {
            return redirect()->back()->with('error', 'Periode tidak ditemukan.');
        }
    }

    public function create()
    {
        return view('master.periode.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Periode::create([
            'tahun' => $request->tahun,
            'status' => 'non-active'
        ]);

        return redirect()->route('periode.index')->with('success', 'Periode created successfully!');
    }

    public function edit(Periode $periode)
    {
        return view('master.periode.edit', compact('periode'));
    }

    public function update(Request $request, Periode $periode)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $periode->update([
            'tahun' => $request->tahun
        ]);

        return redirect()->route('periode.index')->with('success', 'Periode updated successfully!');
    }

    public function destroy(Periode $periode)
    {
        $periode->delete();

        return redirect()->route('periode.index')->with('success', 'Periode deleted successfully!');
    }

    public function restore($id)
    {
        $periode = Periode::withTrashed()->find($id);

        if ($periode) {
            $periode->restore();
            return redirect()->route('periode.index')->with('success', 'Periode restored successfully!');
        }

        return redirect()->route('periode.index')->with('error', 'Periode not found!');
    }

    public function getAmbangBatas(Periode $periode)
    {
        $ambangBatas = $periode->ambangBatasRisiko;
        return response()->json($ambangBatas);
    }

    public function updateAmbangBatas(Request $request, Periode $periode)
    {
        $validator = Validator::make($request->all(), [
            'nilai_kapasitas_risiko' => 'required|numeric',
            'nilai_selera_risiko' => 'required|numeric',
            'nilai_toleransi_risiko' => 'required|numeric',
            'nilai_batasan_risiko' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Soft delete data lama jika ada
        if ($periode->ambangBatasRisiko) {
            $periode->ambangBatasRisiko->delete();
        }

        // Create data baru
        $periode->ambangBatasRisiko()->create([
            'nilai_kapasitas_risiko' => $request->nilai_kapasitas_risiko,
            'nilai_selera_risiko' => $request->nilai_selera_risiko,
            'nilai_toleransi_risiko' => $request->nilai_toleransi_risiko,
            'nilai_batasan_risiko' => $request->nilai_batasan_risiko,
        ]);

        return redirect()->route('periode.index')
            ->with('success', 'Ambang batas risiko berhasil diperbarui!');
    }
}