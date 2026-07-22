<?php

namespace App\Http\Controllers;

use App\Models\CapaianTck;
use App\Models\Periode;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CapaianTckController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:capaian_tck');
    }

    public function index()
    {
        $capaians = CapaianTck::all();
        return view('capaian-tck.index', compact('capaians'));
    }

    public function create()
    {
        $periodes = Periode::pluck('tahun','id');
        $units = Unit::with('unitType')->where('unit_type_id', '<>', 4)->get()->groupBy('unit_type_id');
        return view('capaian-tck.create', compact('periodes','units'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:units,id',
            'periode_id' => 'required|exists:periodes,id',
            'capaian' => 'required|numeric',
            'tanggal_data' => 'required|date:Y-m-d',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        CapaianTck::create($request->all());

        return redirect()->route('capaian-tck.index')->with('success', 'Capaian TCK created successfully!');
    }

    public function edit(CapaianTck $capaianTck)
    {
        $periodes = Periode::pluck('tahun','id');
        $units = Unit::with('unitType')->where('unit_type_id', '<>', 4)->get()->groupBy('unit_type_id');
        return view('capaian-tck.edit', compact('capaianTck','periodes','units'));
    }

    public function update(Request $request, CapaianTck $capaianTck)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:units,id',
            'periode_id' => 'required|exists:periodes,id',
            'capaian' => 'required|numeric',
            'tanggal_data' => 'required|date:Y-m-d',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $capaianTck->update($request->all());

        return redirect()->route('capaian-tck.index')->with('success', 'Capaian TCK updated successfully!');
    }

    public function destroy(CapaianTck $capaianTck)
    {
        $capaianTck->delete();
        return redirect()->route('capaian-tck.index')->with('success', 'Capaian TCK deleted successfully!');
    }
}
