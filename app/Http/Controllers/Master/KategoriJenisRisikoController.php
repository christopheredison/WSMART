<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriJenisRisiko;
use Illuminate\Support\Facades\Validator;

class KategoriJenisRisikoController extends Controller
{
    public function index()
    {
        $kategoriRisiko = KategoriJenisRisiko::get();
        return view('master.kategori-risiko.index', compact('kategoriRisiko'));
    }

    public function create()
    {
        return view('master.kategori-risiko.create');
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

        KategoriJenisRisiko::create([
            'title' => $request->title,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('kategori-risiko.index')->with('success', 'Kategori Risiko created successfully!');
    }

    public function edit(KategoriJenisRisiko $kategoriRisiko)
    {
        return view('master.kategori-risiko.edit', compact('kategoriRisiko'));
    }

    public function update(Request $request, KategoriJenisRisiko $kategoriRisiko)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $kategoriRisiko->update([
            'title' => $request->title,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('kategori-risiko.index')->with('success', 'Kategori Risiko updated successfully!');
    }

    public function destroy(KategoriJenisRisiko $kategoriRisiko)
    {
        $kategoriRisiko->delete();

        return redirect()->route('kategori-risiko.index')->with('success', 'Kategori Risiko deleted successfully!');
    }
}
