<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sasaran;
use App\Models\StrategiBisnis;
use App\Models\Periode;
use App\Models\MetrikStrategiRisiko;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SasaranStrategiBisnisController extends Controller
{
    //
    public function index()
    {
        $latest = Periode::latest('id')->first();
        $sasarans = Sasaran::with('strategiBisnis', 'metrikStrategiRisiko.parameterMetriks')
                        ->where('periode_id', $latest->id)
                        ->get();
        return view('sasaran-strategi-bisnis-risiko.index', compact('sasarans'));
    }

    public function create()
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $metrikStrategiRisikos = MetrikStrategiRisiko::with('parameterMetriks')->get();
        return view('sasaran-strategi-bisnis-risiko.create', compact('periodes', 'metrikStrategiRisikos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required|exists:periodes,id',
            'metrik_strategi_risiko_id' => 'required|exists:metrik_strategi_risikos,id',
            'sasaran' => 'required|string',
            'expected_result' => 'required|numeric',
            'risk_value' => 'required|numeric',
            'strategi' => 'required|array|min:1',
            'strategi.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Create Sasaran
            $sasaran = Sasaran::create([
                'periode_id' => $request->periode_id,
                'metrik_strategi_risiko_id' => $request->metrik_strategi_risiko_id,
                'sasaran' => $request->sasaran,
                'expected_result' => $request->expected_result,
                'risk_value' => $request->risk_value
            ]);

            // Create Strategi Bisnis
            foreach ($request->strategi as $strategi) {
                StrategiBisnis::create([
                    'sasaran_id' => $sasaran->id,
                    'strategi' => $strategi,
                    'status' => 0 // Default status
                ]);
            }

            DB::commit();
            return redirect()->route('sasaran-strategi.index')
                ->with('success', 'Sasaran dan Strategi Bisnis berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data')
                ->withInput();
        }
    }

    public function destroy(Sasaran $sasaran)
    {
        $sasaran->delete();
        return redirect()->route('sasaran-strategi.index')
            ->with('success', 'Sasaran berhasil dihapus');
    }

    // Di StrategiBisnisController
    public function updateStatus(Request $request, StrategiBisnis $strategiBisnis)
    {
        $strategiBisnis->update([
            'status' => $request->status
        ]);
        return redirect()->route('sasaran-strategi.index')
            ->with('success', 'Status strategi berhasil diupdate');
    }
}
