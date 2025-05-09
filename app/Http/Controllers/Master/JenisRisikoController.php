<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use Illuminate\Support\Facades\Validator;

class JenisRisikoController extends Controller
{
    public function index()
    {
        $jenisRisiko = JenisRisiko::with('kategori')->withTrashed()->get();
        return view('master.jenis-risiko.index', compact('jenisRisiko'));
    }

    public function create()
    {
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        return view('master.jenis-risiko.create',compact('kategoriRisiko'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        JenisRisiko::create([
            'kategori_risiko_id' => $request->kategori_risiko_id,
            'title' => $request->title,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('jenis-risiko.index')->with('success', 'Jenis Risiko created successfully!');
    }

    public function edit(JenisRisiko $jenisRisiko)
    {
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        return view('master.jenis-risiko.edit', compact('jenisRisiko','kategoriRisiko'));
    }

    public function update(Request $request, JenisRisiko $jenisRisiko)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $jenisRisiko->update([
            'kategori_risiko_id' => $request->kategori_risiko_id,
            'title' => $request->title,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('jenis-risiko.index')->with('success', 'Jenis Risiko updated successfully!');
    }

    public function destroy(JenisRisiko $jenisRisiko)
    {
        $jenisRisiko->delete();

        return redirect()->route('jenis-risiko.index')->with('success', 'Jenis Risiko deleted successfully!');
    }

    public function restore($id)
    {
        $jenisRisiko = JenisRisiko::withTrashed()->find($id);

        if ($jenisRisiko) {
            $jenisRisiko->restore();
            return redirect()->route('jenis-risiko.index')->with('success', 'Jenis Risiko restored successfully!');
        }

        return redirect()->route('jenis-risiko.index')->with('error', 'Jenis Risiko not found!');
    }
}
