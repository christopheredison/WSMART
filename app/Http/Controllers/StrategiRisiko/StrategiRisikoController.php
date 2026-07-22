<?php

namespace App\Http\Controllers\StrategiRisiko;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiskLimit;
use App\Models\StrategiRisiko;
use App\Models\Periode;
use App\Models\Unit;
use App\Models\JenisRisiko;
use Auth;

class StrategiRisikoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $periode = Periode::where('status','active')->pluck('id')->first();
        $data = StrategiRisiko::with('riskLimit')
                                ->where('unit_id', $user->unit_id)
                                ->where('periode_id',$periode)
                                ->orderBy('created_at', 'desc')
                                ->first();

        if ($data) {
            $riskLimitKonservatif = RiskLimit::where('strategi_risiko_id', $data->id)->where('sikap_risiko', 1)->get();
            $riskLimitModerat = RiskLimit::where('strategi_risiko_id', $data->id)->where('sikap_risiko', 2)->get();
            $riskLimitAgresif = RiskLimit::where('strategi_risiko_id', $data->id)->where('sikap_risiko', 3)->get();
        } else {
            $riskLimitKonservatif = collect();
            $riskLimitModerat = collect();
            $riskLimitAgresif = collect();
        }

        return view('strategi-risiko.index', compact('data', 'riskLimitKonservatif', 'riskLimitModerat', 'riskLimitAgresif'));
    }

    public function create()
    {
        $user = auth()->user();
        $periode = Periode::where('status', 'active')->pluck('id')->first();
        $data = StrategiRisiko::with('riskLimit')
                                ->where('unit_id', $user->unit_id)
                                ->where('periode_id', $periode)
                                ->orderBy('created_at', 'desc')
                                ->first();

        if ($data) {
            $jenisRisikoKonservatif = $data->riskLimit->where('sikap_risiko', 1);
            $jenisRisikoModerat = $data->riskLimit->where('sikap_risiko', 2);
            $jenisRisikoAgresif = $data->riskLimit->where('sikap_risiko', 3);
        } else {
            $jenisRisikoKonservatif = JenisRisiko::where('sikap_risiko', 1)->get();
            $jenisRisikoModerat = JenisRisiko::where('sikap_risiko', 2)->get();
            $jenisRisikoAgresif = JenisRisiko::where('sikap_risiko', 3)->get();
        }
        // dd($jenisRisikoKonservatif);

        return view('strategi-risiko.create', compact('jenisRisikoKonservatif', 'jenisRisikoModerat', 'jenisRisikoAgresif', 'data'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $unit = $user->unit_id;
        $periode = Periode::where('status','active')->pluck('id')->first();

        $total_anggaran_unit = preg_replace('/\D/', '', $request->total_anggaran_unit);
        $value_risk_tolerance = ($request->persentase_risk_tolerance / 100) * $total_anggaran_unit;
        $value_risk_appetite = ($request->persentase_risk_appetite / 100) * $total_anggaran_unit;

        $value_risk_tolerance_konservatif = ($request->persentase_risk_tolerance_konservatif / 100) * $value_risk_tolerance;
        $value_risk_tolerance_moderat = ($request->persentase_risk_tolerance_moderat / 100) * $value_risk_tolerance;
        $value_risk_tolerance_agresif = ($request->persentase_risk_tolerance_agresif / 100) * $value_risk_tolerance;

        $value_risk_appetite_konservatif = ($request->persentase_risk_appetite_konservatif / 100) * $value_risk_appetite;
        $value_risk_appetite_moderat = ($request->persentase_risk_appetite_moderat / 100) * $value_risk_appetite;
        $value_risk_appetite_agresif = ($request->persentase_risk_appetite_agresif / 100) * $value_risk_appetite;

        $StrategiRisiko = StrategiRisiko::create([
            'periode_id' => $periode,
            'unit_id' => $unit,
            'total_anggaran_unit' => $total_anggaran_unit,
            'persentase_risk_tolerance' => $request->persentase_risk_tolerance,
            'persentase_risk_appetite' => $request->persentase_risk_appetite,
            'persentase_risk_tolerance_konservatif' => $request->persentase_risk_tolerance_konservatif,
            'persentase_risk_appetite_konservatif' => $request->persentase_risk_appetite_konservatif,
            'persentase_risk_tolerance_moderat' => $request->persentase_risk_tolerance_moderat,
            'persentase_risk_appetite_moderat' => $request->persentase_risk_appetite_moderat,
            'persentase_risk_tolerance_agresif' => $request->persentase_risk_tolerance_agresif,
            'persentase_risk_appetite_agresif' => $request->persentase_risk_appetite_agresif,
            'value_risk_tolerance' => $value_risk_tolerance,
            'value_risk_appetite' => $value_risk_appetite,
            'value_risk_tolerance_konservatif' => $value_risk_tolerance_konservatif,
            'value_risk_appetite_konservatif' => $value_risk_appetite_konservatif,
            'value_risk_tolerance_moderat' => $value_risk_tolerance_moderat,
            'value_risk_appetite_moderat' => $value_risk_appetite_moderat,
            'value_risk_tolerance_agresif' => $value_risk_tolerance_agresif,
            'value_risk_appetite_agresif' => $value_risk_appetite_agresif,
            'batas_konservatif' => $request->batas_konservatif,
            'batas_moderat' => $request->batas_moderat,
            'batas_agresif' => $request->batas_agresif
        ]);

        $jenisRisiko = $request->input('jenis_risiko');
        $persentase = $request->input('persentase');

        foreach ($jenisRisiko as $index => $jenis) {
            $riskLimit = new RiskLimit();
            $riskLimit->strategi_risiko_id = $StrategiRisiko->id;
            $riskLimit->jenis_risiko_id = $jenis;
            $riskLimit->persentase_limit = $persentase[$index];

            $sikapRisiko = JenisRisiko::where('id',$jenis)->pluck('sikap_risiko')->first();
            $riskLimit->sikap_risiko = $sikapRisiko;
            if($sikapRisiko == 1){
                $riskLimit->nominal_limit = $value_risk_appetite_konservatif * ($riskLimit->persentase_limit / 100);
            }elseif($sikapRisiko == 2){
                $riskLimit->nominal_limit = $value_risk_appetite_moderat * ($riskLimit->persentase_limit / 100);
            }elseif($sikapRisiko == 3){
                $riskLimit->nominal_limit = $value_risk_appetite_agresif * ($riskLimit->persentase_limit / 100);
            }
            $riskLimit->save();
        }

        return redirect()->route('strategi-risiko.index')->with('success', 'Manage Strategi Risiko successfully!');
    }

    public function update(Request $request)
    {
        // dd($request->all());
        $user = auth()->user();
        $periode = Periode::where('status', 'active')->pluck('id')->first();
        $strategiRisiko = StrategiRisiko::with('riskLimit')
                                ->where('unit_id', $user->unit_id)
                                ->where('periode_id', $periode)
                                ->orderBy('created_at', 'desc')
                                ->first();
        $unit = $user->unit_id;

        $total_anggaran_unit = preg_replace('/\D/', '', $request->total_anggaran_unit);
        $value_risk_tolerance = ($request->persentase_risk_tolerance / 100) * $total_anggaran_unit;
        $value_risk_appetite = ($request->persentase_risk_appetite / 100) * $total_anggaran_unit;

        $value_risk_tolerance_konservatif = ($request->persentase_risk_tolerance_konservatif / 100) * $value_risk_tolerance;
        $value_risk_tolerance_moderat = ($request->persentase_risk_tolerance_moderat / 100) * $value_risk_tolerance;
        $value_risk_tolerance_agresif = ($request->persentase_risk_tolerance_agresif / 100) * $value_risk_tolerance;

        $value_risk_appetite_konservatif = ($request->persentase_risk_appetite_konservatif / 100) * $value_risk_appetite;
        $value_risk_appetite_moderat = ($request->persentase_risk_appetite_moderat / 100) * $value_risk_appetite;
        $value_risk_appetite_agresif = ($request->persentase_risk_appetite_agresif / 100) * $value_risk_appetite;

        $strategiRisiko->update([
            'periode_id' => $periode,
            'unit_id' => $unit,
            'total_anggaran_unit' => $total_anggaran_unit,
            'persentase_risk_tolerance' => $request->persentase_risk_tolerance,
            'persentase_risk_appetite' => $request->persentase_risk_appetite,
            'persentase_risk_tolerance_konservatif' => $request->persentase_risk_tolerance_konservatif,
            'persentase_risk_appetite_konservatif' => $request->persentase_risk_appetite_konservatif,
            'persentase_risk_tolerance_moderat' => $request->persentase_risk_tolerance_moderat,
            'persentase_risk_appetite_moderat' => $request->persentase_risk_appetite_moderat,
            'persentase_risk_tolerance_agresif' => $request->persentase_risk_tolerance_agresif,
            'persentase_risk_appetite_agresif' => $request->persentase_risk_appetite_agresif,
            'value_risk_tolerance' => $value_risk_tolerance,
            'value_risk_appetite' => $value_risk_appetite,
            'value_risk_tolerance_konservatif' => $value_risk_tolerance_konservatif,
            'value_risk_appetite_konservatif' => $value_risk_appetite_konservatif,
            'value_risk_tolerance_moderat' => $value_risk_tolerance_moderat,
            'value_risk_appetite_moderat' => $value_risk_appetite_moderat,
            'value_risk_tolerance_agresif' => $value_risk_tolerance_agresif,
            'value_risk_appetite_agresif' => $value_risk_appetite_agresif,
            'batas_konservatif' => $request->batas_konservatif,
            'batas_moderat' => $request->batas_moderat,
            'batas_agresif' => $request->batas_agresif
        ]);

        $jenisRisiko = $request->input('jenis_risiko');
        $persentase = $request->input('persentase');

        // Remove old risk limits
        RiskLimit::where('strategi_risiko_id', $strategiRisiko->id)->delete();

        // Update risk limits
        foreach ($jenisRisiko as $index => $jenis) {
            $riskLimit = new RiskLimit();
            $riskLimit->strategi_risiko_id = $strategiRisiko->id;
            $riskLimit->jenis_risiko_id = $jenis;
            $riskLimit->persentase_limit = $persentase[$index];

            $sikapRisiko = JenisRisiko::where('id',$jenis)->pluck('sikap_risiko')->first();
            $riskLimit->sikap_risiko = $sikapRisiko;
            if($sikapRisiko == 1){
                $riskLimit->nominal_limit = $value_risk_appetite_konservatif * ($riskLimit->persentase_limit / 100);
            }elseif($sikapRisiko == 2){
                $riskLimit->nominal_limit = $value_risk_appetite_moderat * ($riskLimit->persentase_limit / 100);
            }elseif($sikapRisiko == 3){
                $riskLimit->nominal_limit = $value_risk_appetite_agresif * ($riskLimit->persentase_limit / 100);
            }
            $riskLimit->save();
        }

        return redirect()->route('strategi-risiko.index')->with('success', 'Manage Strategi Risiko successfully!');
    }

}
