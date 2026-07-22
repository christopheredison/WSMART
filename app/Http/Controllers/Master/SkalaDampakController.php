<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SkalaDampak;
use Illuminate\Support\Facades\Validator;

class SkalaDampakController extends Controller
{
    public function index()
    {
        $skalaDampak = SkalaDampak::withTrashed()->get();
        return view('master.skala-dampak.index', compact('skalaDampak'));
    }

    public function create()
    {
        return view('master.skala-dampak.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tingkat' => 'required|integer|max:255',
            'deskripsi' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        SkalaDampak::create([
            'tingkat' => $request->tingkat,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('skala-dampak.index')->with('success', 'Skala Dampak created successfully!');
    }

    public function edit(SkalaDampak $skalaDampak)
    {
        return view('master.skala-dampak.edit', compact('skalaDampak'));
    }

    public function update(Request $request, SkalaDampak $skalaDampak)
    {
        $validator = Validator::make($request->all(), [
            'tingkat' => 'required|integer|max:255',
            'deskripsi' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $skalaDampak->update([
            'tingkat' => $request->tingkat,
            'deskripsi' => $request->deskripsi
        ]);

        return redirect()->route('skala-dampak.index')->with('success', 'Skala Dampak updated successfully!');
    }

    public function destroy(SkalaDampak $skalaDampak)
    {
        $skalaDampak->delete();

        return redirect()->route('skala-dampak.index')->with('success', 'Skala Dampak deleted successfully!');
    }

    public function restore($id)
    {
        $skalaDampak = SkalaDampak::withTrashed()->find($id);

        if ($skalaDampak) {
            $skalaDampak->restore();
            return redirect()->route('skala-dampak.index')->with('success', 'Skala Dampak restored successfully!');
        }

        return redirect()->route('skala-dampak.index')->with('error', 'Skala Dampak not found!');
    }
}
