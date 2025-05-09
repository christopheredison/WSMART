<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiskSetting;
use App\Models\RiskSettingChild;

class RiskSettingController extends Controller
{
    public function index()
    {
        $riskSettings = RiskSetting::with('child')->get();
        return view('master.risk-control-setting.index', compact('riskSettings'));
    }

    public function edit($id)
    {
        $riskSetting = RiskSetting::with('child')->findOrFail($id);
        return response()->json($riskSetting);
    }

    public function update(Request $request)
    {
        try {
            $id = $request->input('id');

            // Find the RiskSetting by ID
            $riskSetting = RiskSetting::findOrFail($id);

            // Update the efektivitas_control
            $riskSetting->efektivitas_control = $request->efektivitas_control;
            $riskSetting->save();

            // Update, delete, and add new child records
            if ($request->has('keterangan')) {
                foreach ($request->keterangan as $key => $keterangan) {
                    if (!empty($keterangan)) {
                        if (isset($riskSetting->child[$key])) {
                            // Update existing child record
                            $riskSetting->child[$key]->keterangan = $keterangan;
                            $riskSetting->child[$key]->save();
                        } else {
                            // Create new child record
                            $riskSettingChild = new RiskSettingChild;
                            $riskSettingChild->risk_setting_id = $riskSetting->id;
                            $riskSettingChild->keterangan = $keterangan;
                            $riskSettingChild->save();
                        }
                    }
                }
            }

            return response()->json(['status' => 200]);
        } catch (\Exception $e) {
            // If an error occurs, return an error message to be displayed
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $riskSettingChild = RiskSettingChild::find($id);
        $riskSettingChild->delete();
        return redirect()->route('risk-control-setting.index')->with('success', 'Risk Control Setting deleted successfully!');
    }

}
