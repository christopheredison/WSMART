<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SikapRisiko;
use Illuminate\Support\Facades\Validator;

class SikapRisikoController extends Controller
{
    public function index()
    {
        $sikapRisiko = SikapRisiko::withTrashed()->get();
        return view('master.sikap-risiko.index', compact('sikapRisiko'));
    }

    public function create()
    {
        return view('master.sikap-risiko.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_sikap' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        SikapRisiko::create([
            'jenis_sikap' => $request->jenis_sikap
        ]);

        return redirect()->route('sikap-risiko.index')->with('success', 'Sikap Risiko created successfully!');
    }

    public function edit(SikapRisiko $sikapRisiko)
    {
        return view('master.sikap-risiko.edit', compact('sikapRisiko'));
    }

    public function update(Request $request, SikapRisiko $sikapRisiko)
    {
        $validator = Validator::make($request->all(), [
            'jenis_sikap' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $sikapRisiko->update([
            'jenis_sikap' => $request->jenis_sikap
        ]);

        return redirect()->route('sikap-risiko.index')->with('success', 'Sikap Risiko updated successfully!');
    }

    public function destroy(SikapRisiko $sikapRisiko)
    {
        $sikapRisiko->delete();

        return redirect()->route('sikap-risiko.index')->with('success', 'Sikap Risiko deleted successfully!');
    }

    public function restore($id)
    {
        $sikapRisiko = SikapRisiko::withTrashed()->find($id);

        if ($sikapRisiko) {
            $sikapRisiko->restore();
            return redirect()->route('sikap-risiko.index')->with('success', 'Sikap Risiko restored successfully!');
        }

        return redirect()->route('sikap-risiko.index')->with('error', 'Sikap Risiko not found!');
    }
}
