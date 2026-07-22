<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiskMap;
use Illuminate\Support\Facades\Validator;

class RiskMapController extends Controller
{
    public function index()
    {
        $riskMap = RiskMap::orderBy('id')->get();
        // dd($riskMap);
        return view('master.risk-map-setting.index', compact('riskMap'));
    }

    public function update(Request $request)
    {
        // dd($request->all());
        // Validasi permintaan
        $request->validate([
            'level_risiko' => 'required|array',
            'nilai_risiko' => 'required|array',
        ]);

        // Loop melalui data yang dikirimkan
        foreach ($request->level_risiko as $key => $level_risiko) {
            // Periksa apakah key ada dalam request nilai_risiko
            if (isset($request->nilai_risiko[$key])) {
                // Perbarui data berdasarkan key
                RiskMap::where('id', $key)->update([
                    'level_risiko' => $level_risiko,
                    'nilai_risiko' => $request->nilai_risiko[$key],
                ]);
            }
        }

        // Redirect ke halaman atau tindakan yang sesuai
        return redirect()->route('risk-map-setting.index')->with('success', 'Risk Map updated successfully!');
    }
}
