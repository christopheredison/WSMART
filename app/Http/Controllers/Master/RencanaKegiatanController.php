<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RencanaKegiatan;
use Illuminate\Support\Facades\Validator;

class RencanaKegiatanController extends Controller
{
    public function index()
    {
        $rencanaKegiatan = RencanaKegiatan::withTrashed()->get();
        return view('master.rencana-kegiatan.index', compact('rencanaKegiatan'));
    }

    public function create()
    {
        return view('master.rencana-kegiatan.create');
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

        RencanaKegiatan::create([
            'title' => $request->title,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('rencana-kegiatan.index')->with('success', 'Rencana Kegiatan created successfully!');
    }

    public function edit(RencanaKegiatan $rencanaKegiatan)
    {
        return view('master.rencana-kegiatan.edit', compact('rencanaKegiatan'));
    }

    public function update(Request $request, RencanaKegiatan $rencanaKegiatan)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $rencanaKegiatan->update([
            'title' => $request->title,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('rencana-kegiatan.index')->with('success', 'Rencana Kegiatan updated successfully!');
    }

    public function destroy(RencanaKegiatan $rencanaKegiatan)
    {
        $rencanaKegiatan->delete();

        return redirect()->route('rencana-kegiatan.index')->with('success', 'Rencana Kegiatan deleted successfully!');
    }

    public function restore($id)
    {
        $rencanaKegiatan = RencanaKegiatan::withTrashed()->find($id);

        if ($rencanaKegiatan) {
            $rencanaKegiatan->restore();
            return redirect()->route('rencana-kegiatan.index')->with('success', 'Rencana Kegiatan restored successfully!');
        }

        return redirect()->route('rencana-kegiatan.index')->with('error', 'Rencana Kegiatan not found!');
    }
}
