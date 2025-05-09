<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    public function index()
    {
        $unit = Unit::with('unitType')->withTrashed()->get();

        return view('master.unit.index', compact('unit'));
    }

    public function create()
    {
        $unitType = UnitType::pluck('name','id');
        $parent = Unit::pluck('name','id');
        return view('master.unit.create', compact('unitType','parent'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'unit_type_id' => 'required',
            // 'parent_id' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Unit::create([
            'name' => $request->name,
            'unit_type_id' => $request->unit_type_id,
            'parent_id' => $request->parent_id ?? 0
        ]);

        return redirect()->route('unit.index')->with('success', 'Unit created successfully!');
    }

    public function edit(Unit $unit)
    {
        $unitType = UnitType::pluck('name','id');
        $parent = Unit::pluck('name','id');
        return view('master.unit.edit', compact('unit','unitType','parent'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'unit_type_id' => 'required',
            // 'parent_id' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $unit->update([
            'name' => $request->name,
            'unit_type_id' => $request->unit_type_id,
            'parent_id' => $request->parent_id ?? 0
        ]);

        return redirect()->route('unit.index')->with('success', 'Unit updated successfully!');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('unit.index')->with('success', 'Unit deleted successfully!');
    }

    public function restore($id)
    {
        $unit = Unit::withTrashed()->find($id);

        if ($unit) {
            $unit->restore();
            return redirect()->route('unit.index')->with('success', 'Unit restored successfully!');
        }

        return redirect()->route('unit.index')->with('error', 'Unit not found!');
    }
}
