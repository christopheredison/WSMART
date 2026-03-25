<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\UnitRiskMonitoring;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'penjelasan' => 'required|string',
            'nilai' => 'required',
            'nilai_peluang_realisasi' => 'required',
            'identifikasi_risiko_id' => 'nullable|integer',
            'project_risk_id' => 'nullable|integer',
            'file' => 'nullable|file|max:5120',
        ]);

        $opportunity = new Opportunity();
        $opportunity->penjelasan_peluang_rencana = $request->description;
        $opportunity->penjelasan_peluang_realisasi = $request->penjelasan;
        $opportunity->nilai_peluang_rencana = $this->convertToNumeric($request->nilai);
        $opportunity->nilai_peluang_realisasi = $this->convertToNumeric($request->nilai_peluang_realisasi);
        $opportunity->identifikasi_risiko_id = $request->identifikasi_risiko_id;
        $opportunity->project_risk_id = $request->project_risk_id;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('opportunities', $filename, 'public');
            $opportunity->file_path = $path;
        }

        $opportunity->save();

        return response()->json(['success' => true, 'message' => 'Peluang berhasil disimpan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|string',
            'penjelasan' => 'required|string',
            'nilai' => 'required',
            'nilai_peluang_realisasi' => 'required',
            'identifikasi_risiko_id' => 'nullable|integer',
            'project_risk_id' => 'nullable|integer',
        ]);

        $opportunity = Opportunity::findOrFail($id);
        $opportunity->penjelasan_peluang_rencana = $request->description;
        $opportunity->penjelasan_peluang_realisasi = $request->penjelasan;
        $opportunity->nilai_peluang_rencana = $this->convertToNumeric($request->nilai);
        $opportunity->nilai_peluang_realisasi = $this->convertToNumeric($request->nilai_peluang_realisasi);

        $opportunity->identifikasi_risiko_id = $request->identifikasi_risiko_id;
        $opportunity->project_risk_id = $request->project_risk_id;

        if ($request->hasFile('file')) {
            if ($opportunity->file_path && Storage::disk('public')->exists($opportunity->file_path)) {
                Storage::disk('public')->delete($opportunity->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('opportunities', $filename, 'public');
            $opportunity->file_path = $path;
        }

        $opportunity->save();

        return response()->json(['success' => true, 'message' => 'Peluang berhasil diperbarui']);
    }

    public function destroy(Request $request, $id)
    {
        $opportunity = Opportunity::findOrFail($id);

        if ($opportunity->file_path && Storage::disk('public')->exists($opportunity->file_path)) {
            Storage::disk('public')->delete($opportunity->file_path);
        }

        $opportunity->delete();

        if($request->ajax()){
            return response()->json(['success' => true, 'message' => 'Data peluang berhasil dihapus']);
        }

        return redirect()->back()->with('success', 'Data peluang berhasil dihapus');
    }

    public function getOpportunities(Request $request, $risikoId)
    {
        $query = Opportunity::query();

        if ($request->query('type') === 'project') {
            $query->where('project_risk_id', $risikoId);
        } else {
            $query->where('identifikasi_risiko_id', $risikoId);
        }

        $opportunities = $query->orderBy('id', 'desc')->get();

        return response()->json($opportunities);
    }

    private function convertToNumeric($value)
    {
        if (empty($value)) {
            return 0;
        }
        $cleanValue = preg_replace('/[^0-9.,]/', '', $value);
        $cleanValue = str_replace('.', '', $cleanValue);
        $cleanValue = str_replace(',', '.', $cleanValue);

        return (float) $cleanValue;
    }
}
