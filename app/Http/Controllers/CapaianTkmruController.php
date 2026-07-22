<?php

namespace App\Http\Controllers;

use App\Models\CapaianTkmru;
use App\Models\Periode;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CapaianTkmruController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:capaian_tkmru');
    }

    public function index()
    {
        $capaians = CapaianTkmru::all();
        return view('capaian-tkmru.index', compact('capaians'));
    }

    public function create()
    {
        $periodes = Periode::pluck('tahun','id');
        $units = Unit::with('unitType')->where('unit_type_id', '<>', 4)->get()->groupBy('unit_type_id');
        return view('capaian-tkmru.create', compact('periodes','units'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:units,id',
            'periode_id' => 'required|exists:periodes,id',
            'capaian' => 'required|numeric',
            'description' => 'nullable',
            'tanggal_data' => 'required|date:Y-m-d',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        CapaianTkmru::create($request->all());

        return redirect()->route('capaian-tkmru.index')->with('success', 'Capaian TKMRU created successfully!');
    }

    public function edit(CapaianTkmru $capaianTkmru)
    {
        $periodes = Periode::pluck('tahun','id');
        $units = Unit::with('unitType')->where('unit_type_id', '<>', 4)->get()->groupBy('unit_type_id');
        return view('capaian-tkmru.edit', compact('capaianTkmru','periodes','units'));
    }

    public function update(Request $request, CapaianTkmru $capaianTkmru)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:units,id',
            'periode_id' => 'required|exists:periodes,id',
            'capaian' => 'required|numeric',
            'description' => 'nullable',
            'tanggal_data' => 'required|date:Y-m-d',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $capaianTkmru->update($request->all());

        return redirect()->route('capaian-tkmru.index')->with('success', 'Capaian TKMRU updated successfully!');
    }

    public function destroy(CapaianTkmru $capaianTkmru)
    {
        $capaianTkmru->delete();
        return redirect()->route('capaian-tkmru.index')->with('success', 'Capaian TKMRU deleted successfully!');
    }
}
