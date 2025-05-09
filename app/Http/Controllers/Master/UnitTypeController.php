<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UnitType;
use Illuminate\Support\Facades\Validator;

class UnitTypeController extends Controller
{
    public function index()
    {
        $unitType = UnitType::withTrashed()->get();
            return view('master.unit-type.index', compact('unitType'));
    }
    public function create()
    {
        return view('master.unit-type.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        UnitType::create([
            'name' => $request->name,
        ]);

        return redirect()->route('unit-type.index')->with('success', 'Unit Type created successfully!');
    }

    public function edit(UnitType $unitType)
    {
        return view('master.unit-type.edit', compact('unitType'));
    }

    public function update(Request $request, UnitType $unitType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $unitType->update([
            'name' => $request->name,
        ]);

        return redirect()->route('unit-type.index')->with('success', 'Unit Type updated successfully!');
    }

    public function destroy(UnitType $unitType)
    {
        $unitType->delete();

        return redirect()->route('unit-type.index')->with('success', 'Unit Type deleted successfully!');
    }

    public function restore($id)
    {
        $unitType = UnitType::withTrashed()->find($id);

        if ($unitType) {
            $unitType->restore();
            return redirect()->route('unit-type.index')->with('success', 'Unit Type restored successfully!');
        }

        return redirect()->route('unit-type.index')->with('error', 'Unit Type not found!');
    }
}
