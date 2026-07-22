<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tck;
use App\Models\Unit;
use App\Models\Periode;
use Illuminate\Support\Facades\Validator;

class TckController extends Controller
{
    public function index()
    {
        $tck = Tck::withTrashed()->get();
        return view('master.tck.index', compact('tck'));
    }
    public function create()
    {
        $unit = Unit::where('parent_id',0)->pluck('name','id');
        $periode = Periode::pluck('tahun','id');
        return view('master.tck.create', compact('unit','periode'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required',
            'unit_id' => 'required',
            'title' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // $periodeActive = Periode::where('status','active')->first();

        Tck::create([
            'periode_id' => $request->periode_id,
            'unit_id' => $request->unit_id,
            'title' => $request->title,
        ]);

        return redirect()->route('tck.index')->with('success', 'Tck created successfully!');
    }

    public function edit(Tck $tck)
    {
        $unit = Unit::where('parent_id',0)->pluck('name','id');
        $periode = Periode::pluck('tahun','id');
        return view('master.tck.edit', compact('tck','unit','periode'));
    }

    public function update(Request $request, Tck $tck)
    {
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required',
            'unit_id' => 'required',
            'title' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // $periodeActive = Periode::where('status','active')->first();

        $tck->update([
            'periode_id' => $request->periode_id,
            'unit_id' => $request->unit_id,
            'title' => $request->title,
        ]);

        return redirect()->route('tck.index')->with('success', 'Tck updated successfully!');
    }

    public function destroy(Tck $tck)
    {
        $tck->delete();

        return redirect()->route('tck.index')->with('success', 'Tck deleted successfully!');
    }

    public function restore($id)
    {
        $tck = Tck::withTrashed()->find($id);

        if ($tck) {
            $tck->restore();
            return redirect()->route('tck.index')->with('success', 'Tck restored successfully!');
        }

        return redirect()->route('tck.index')->with('error', 'Tck not found!');
    }
}
