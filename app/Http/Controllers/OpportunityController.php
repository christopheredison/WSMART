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
            'identifikasi_risiko_id' => 'required',
        ]);

        $opportunity = new Opportunity();
        $opportunity->penjelasan_peluang_rencana = $request->description;
        $opportunity->penjelasan_peluang_realisasi = $request->penjelasan;
        $opportunity->nilai_peluang_rencana = $this->convertToNumeric($request->nilai);
        $opportunity->nilai_peluang_realisasi = $this->convertToNumeric($request->nilai_peluang_realisasi);
        $opportunity->identifikasi_risiko_id = $request->identifikasi_risiko_id;
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
            'identifikasi_risiko_id' => 'required',
        ]);

        $opportunity = Opportunity::findOrFail($id);
        $opportunity->penjelasan_peluang_rencana = $request->description;
        $opportunity->penjelasan_peluang_realisasi = $request->penjelasan;
        $opportunity->nilai_peluang_rencana = $this->convertToNumeric($request->nilai);
        $opportunity->nilai_peluang_realisasi = $this->convertToNumeric($request->nilai_peluang_realisasi);
        $opportunity->identifikasi_risiko_id = $request->identifikasi_risiko_id;
        $opportunity->save();

        return response()->json(['success' => true, 'message' => 'Peluang berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $opportunity = Opportunity::findOrFail($id);
        $opportunity->delete();

        return redirect()->back()->with('success', 'Data peluang berhasil dihapus');
    }

    public function getOpportunities($risikoId)
    {
        $opportunities = Opportunity::where('identifikasi_risiko_id', $risikoId)->get();
        return response()->json($opportunities);
    }

    private function convertToNumeric($value)
    {
        if (empty($value)) {
            return 0;
        }
        
        // Hapus semua karakter kecuali angka, titik, dan koma
        $cleanValue = preg_replace('/[^0-9.,]/', '', $value);
        
        // Ganti koma dengan titik untuk format desimal
        $cleanValue = str_replace(',', '.', $cleanValue);
        
        // Konversi ke float
        return (float) $cleanValue;
    }
}