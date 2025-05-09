<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AreaDampak;
use Illuminate\Support\Facades\Validator;

class AreaDampakController extends Controller
{
    public function index()
    {
        $areaDampak = AreaDampak::withTrashed()->get();
            return view('master.area-dampak.index', compact('areaDampak'));
    }

    public function create()
    {
        return view('master.area-dampak.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        AreaDampak::create([
            'title' => $request->title,
            'type' => $request->type
        ]);

        return redirect()->route('area-dampak.index')->with('success', 'Area Dampak created successfully!');
    }

    public function edit(AreaDampak $areaDampak)
    {
        return view('master.area-dampak.edit', compact('areaDampak'));
    }

    public function update(Request $request, AreaDampak $areaDampak)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $areaDampak->update([
            'title' => $request->title,
            'type' => $request->type
        ]);

        return redirect()->route('area-dampak.index')->with('success', 'Area Dampak updated successfully!');
    }

    public function destroy(AreaDampak $areaDampak)
    {
        $areaDampak->delete();

        return redirect()->route('area-dampak.index')->with('success', 'Area Dampak deleted successfully!');
    }

    public function restore($id)
    {
        $areaDampak = AreaDampak::withTrashed()->find($id);

        if ($areaDampak) {
            $areaDampak->restore();
            return redirect()->route('area-dampak.index')->with('success', 'Area Dampak restored successfully!');
        }

        return redirect()->route('area-dampak.index')->with('error', 'Area Dampak not found!');
    }
}