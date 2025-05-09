<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SkalaProbabilitas;
use Illuminate\Support\Facades\Validator;

class SkalaProbabilitasController extends Controller
{
    public function index()
    {
        $skalaProbabilitas = SkalaProbabilitas::withTrashed()->get();
        return view('master.skala-probabilitas.index', compact('skalaProbabilitas'));
    }

    public function create()
    {
        return view('master.skala-probabilitas.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'min' => 'required',
            'max' => 'required',
            'type_risiko' => 'required',
            'tingkat' => 'required',
            'skala' => 'required',
            'deskripsi' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        SkalaProbabilitas::create([
            'min' => $request->min,
            'max' => $request->max,
            'type_risiko' => $request->type_risiko,
            'tingkat' => $request->tingkat,
            'skala' => $request->skala,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('skala-probabilitas.index')->with('success', 'Skala Probabilitas created successfully!');
    }

    public function edit(SkalaProbabilitas $skalaProbabilitas)
    {
        return view('master.skala-probabilitas.edit', compact('skalaProbabilitas'));
    }

    public function update(Request $request, SkalaProbabilitas $skalaProbabilitas)
    {
        $validator = Validator::make($request->all(), [
            'min' => 'required',
            'max' => 'required',
            'type_risiko' => 'required',
            'tingkat' => 'required',
            'skala' => 'required',
            'deskripsi' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $skalaProbabilitas->update([
            'min' => $request->min,
            'max' => $request->max,
            'type_risiko' => $request->type_risiko,
            'tingkat' => $request->tingkat,
            'skala' => $request->skala,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('skala-probabilitas.index')->with('success', 'Skala Probabilitas updated successfully!');
    }

    public function destroy(SkalaProbabilitas $skalaProbabilitas)
    {
        $skalaProbabilitas->delete();

        return redirect()->route('skala-probabilitas.index')->with('success', 'Skala Probabilitas deleted successfully!');
    }

    public function restore($id)
    {
        $skalaProbabilitas = SkalaProbabilitas::withTrashed()->find($id);

        if ($skalaProbabilitas) {
            $skalaProbabilitas->restore();
            return redirect()->route('skala-probabilitas.index')->with('success', 'Skala Probabilitas restored successfully!');
        }

        return redirect()->route('skala-probabilitas.index')->with('error', 'Skala Probabilitas not found!');
    }
}