<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriRisiko;
use Illuminate\Support\Facades\Validator;

class KategoriRisikoController extends Controller
{
    public function index()
    {
        $kategoriRisiko = KategoriRisiko::withTrashed()->get();
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

        KategoriRisiko::create([
            'title' => $request->title,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('kategori-risiko.index')->with('success', 'Kategori Risiko created successfully!');
    }

    public function edit(KategoriRisiko $kategoriRisiko)
    {
        return view('master.kategori-risiko.edit', compact('kategoriRisiko'));
    }

    public function update(Request $request, KategoriRisiko $kategoriRisiko)
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

    public function destroy(KategoriRisiko $kategoriRisiko)
    {
        $kategoriRisiko->delete();

        return redirect()->route('kategori-risiko.index')->with('success', 'Kategori Risiko deleted successfully!');
    }

    public function restore($id)
    {
        $kategoriRisiko = KategoriRisiko::withTrashed()->find($id);

        if ($kategoriRisiko) {
            $kategoriRisiko->restore();
            return redirect()->route('kategori-risiko.index')->with('success', 'Kategori Risiko restored successfully!');
        }

        return redirect()->route('kategori-risiko.index')->with('error', 'Kategori Risiko not found!');
    }
}
