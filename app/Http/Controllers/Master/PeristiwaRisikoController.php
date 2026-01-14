<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\PeristiwaRisiko;
use Illuminate\Support\Facades\Validator;

class PeristiwaRisikoController extends Controller
{
    public function index()
    {
        $peristiwaRisiko = PeristiwaRisiko::with('kategoriRisiko','jenisRisiko')->withTrashed()->get();
        return view('master.peristiwa-risiko.index', compact('peristiwaRisiko'));
    }

    public function create()
    {
        $kategoriRisiko = KategoriRisiko::pluck('title', 'id');
        $jenisRisiko = JenisRisiko::pluck('title', 'id');
        return view('master.peristiwa-risiko.create', compact('kategoriRisiko','jenisRisiko'));
    }

    public function getJenisRisiko($kategoriRisikoId)
    {
        $jenisRisiko = JenisRisiko::where('kategori_risiko_id', $kategoriRisikoId)->pluck('title', 'id');
        return response()->json($jenisRisiko);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'kategori_risiko_id' => 'required',
            // 'jenis_risiko_id' => 'required',
            'title' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        PeristiwaRisiko::create([
            'kategori_risiko_id' => $request->kategori_risiko_id ?? null,
            'jenis_risiko_id' => $request->jenis_risiko_id ?? null,
            'title' => $request->title,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('peristiwa-risiko.index')->with('success', 'Peristiwa Risiko created successfully!');
    }

    public function edit(PeristiwaRisiko $peristiwaRisiko)
    {
        $kategoriRisiko = KategoriRisiko::pluck('title', 'id');
        $jenisRisiko = JenisRisiko::pluck('title', 'id');
        return view('master.peristiwa-risiko.edit', compact('kategoriRisiko', 'peristiwaRisiko','jenisRisiko'));
    }

    public function update(Request $request, PeristiwaRisiko $peristiwaRisiko)
    {
        $validator = Validator::make($request->all(), [
            // 'kategori_risiko_id' => 'required',
            // 'jenis_risiko_id' => 'required',
            'title' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $peristiwaRisiko->update([
            'kategori_risiko_id' => $request->kategori_risiko_id ?? null,
            'jenis_risiko_id' => $request->jenis_risiko_id ?? null,
            'title' => $request->title,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('peristiwa-risiko.index')->with('success', 'Peristiwa Risiko updated successfully!');
    }

    public function destroy(PeristiwaRisiko $peristiwaRisiko)
    {
        $peristiwaRisiko->delete();

        return redirect()->route('peristiwa-risiko.index')->with('success', 'Peristiwa Risiko deleted successfully!');
    }

    public function restore($id)
    {
        $peristiwaRisiko = PeristiwaRisiko::withTrashed()->find($id);

        if ($peristiwaRisiko) {
            $peristiwaRisiko->restore();
            return redirect()->route('peristiwa-risiko.index')->with('success', 'Peristiwa Risiko restored successfully!');
        }

        return redirect()->route('peristiwa-risiko.index')->with('error', 'Peristiwa Risiko not found!');
    }
}
