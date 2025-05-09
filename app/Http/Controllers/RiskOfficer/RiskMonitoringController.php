<?php

namespace App\Http\Controllers\RiskOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdentifikasiRisiko;
use App\Models\PenyebabRisiko;
use App\Models\MonitoringRisiko;
use App\Models\RiskAnalysis;
use App\Models\RencanaPerlakuanRisiko;
use App\Models\SkalaDampak;
use App\Models\SkalaProbabilitas;
use App\Models\KRI;
use App\Models\RiskMap;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\MonitoringRisikoFile;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Models\Periode;
use App\Models\User;
use App\Models\Unit;

class RiskMonitoringController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();
        
        $identifikasiRisikoIds = IdentifikasiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeActive->id)
            ->pluck('id');

        //$riskMonitoring = MonitoringRisiko::where('periode_id', $periodeActive->id)->where('unit_id', $unitId)->with('identifikasiRisiko','rencanaPerlakuanRisiko')->get();
        $riskMonitoring = MonitoringRisiko::whereIn('risiko_id', $identifikasiRisikoIds)
            ->with('identifikasiRisiko', 'rencanaPerlakuanRisiko')
            ->get();


        $uploadedFiles = MonitoringRisikoFile::all();
        // $periode_monitoring = "Quarter 1";
        // $riskMonitoring = MonitoringRisiko::with('identifikasiRisiko','rencanaPerlakuanRisiko')->where('periode_monitoring',$periode_monitoring)->get();
        // dd($risiko);
        return view('risk-monitoring.index',compact('riskMonitoring','uploadedFiles'));
    }

    public function getRealisasiSkalaProbabilitas(Request $request)
    {
        // Ambil nilai probabilitas dari request
        $nilaiProbabilitas = $request->input('realisasi_nilai_probabilitas');

        // Cari skala probabilitas berdasarkan nilai yang dimasukkan
        $skalaProbabilitas = SkalaProbabilitas::where('type_risiko', $request->input('type_risiko'))
            ->where('min', '<=', $nilaiProbabilitas)
            ->where('max', '>=', $nilaiProbabilitas)
            ->first();

        // Kembalikan data skala probabilitas dalam format JSON
        return response()->json($skalaProbabilitas);
    }

    public function getRiskMapData()
    {
        $riskMap = RiskMap::select('level_risiko', 'nilai_risiko')->get();
        return response()->json($riskMap);
    }

    public function edit(MonitoringRisiko $riskMonitoring)
    {
        $penyebabRisiko = PenyebabRisiko::where('risiko_id',$riskMonitoring->risiko_id)->get();
        $keyRiskIndicator = KRI::where('risiko_id', $riskMonitoring->risiko_id)->get();
        $risiko = IdentifikasiRisiko::where('id', $riskMonitoring->risiko_id)->first();
        $riskAnalysis = RiskAnalysis::where('risiko_id', $riskMonitoring->risiko_id)->first();
        $riskPlan = RencanaPerlakuanRisiko::where('risiko_id', $riskMonitoring->risiko_id)->first();
        $skalaDampak = SkalaDampak::pluck('tingkat','id');
        //$skalaProbabilitas = SkalaProbabilitas::where('type_risiko', $risiko->type)->pluck('tingkat','id', 'skala');
        $skalaProbabilitas = SkalaProbabilitas::where('type_risiko', $risiko->type)
                                      ->get(['id', 'tingkat', 'skala']) // Ambil semua kolom yang diperlukan
                                      ->mapWithKeys(function ($item) {
                                          return [$item->id => $item->tingkat . ' - ' . $item->skala];
                                      });

        $riskMaps = RiskMap::orderBy('nilai_risiko')->get();

        // Inisialisasi array
        $level_risiko = array_fill(0, 6, array_fill(0, 6, ""));
        $nilai_risiko = array_fill(0, 6, array_fill(0, 6, ""));

        // Susun data ke dalam array
        // foreach ($riskMaps as $riskMap) {
        //     $nilai = $riskMap->nilai_risiko;
        //     if ($nilai > 0 && $nilai <= 25) {
        //         $row = ceil($nilai / 5);
        //         $col = $nilai % 5 == 0 ? 5 : $nilai % 5;

        //         $level_risiko[$row][$col] = $riskMap->level_risiko;
        //         $nilai_risiko[$row][$col] = $riskMap->nilai_risiko;
        //     }
        // }

        $nilai_risiko[1]=['', '1', '2', '3', '4', '7'];
        $nilai_risiko[2]=['', '5', '6', '8', '9', '12'];
        $nilai_risiko[3]=['', '10', '11', '13', '14', '17'];
        $nilai_risiko[4]=['', '15', '16', '18', '19', '22'];
        $nilai_risiko[5]=['', '20', '21', '23', '24', '25'];

        $level_risiko[1]=['', 'Low', 'Low', 'Low', 'Low', 'Low to Moderate'];
        $level_risiko[2]=['', 'Low', 'Low to Moderate', 'Low to Moderate', 'Low to Moderate', 'Moderate'];
        $level_risiko[3]=['', 'Low to Moderate', 'Low to Moderate', 'Moderate', 'Moderate', 'Moderate to High'];
        $level_risiko[4]=['', 'Moderate', 'Moderate to High', 'Moderate to High', 'Moderate to High', 'High'];
        $level_risiko[5]=['', 'High', 'High', 'High', 'High', 'High'];

        // dd($level_risiko);

        return view('risk-monitoring.edit', compact('riskMonitoring','penyebabRisiko','keyRiskIndicator','risiko','riskAnalysis','riskPlan','skalaDampak','skalaProbabilitas','riskMaps','level_risiko','nilai_risiko'));
    }

    public function update(Request $request, MonitoringRisiko $riskMonitoring)
    {
        $riskMonitoring->update([
            'realisasi_nilai_dampak' => $request->realisasi_nilai_dampak,
            'realisasi_skala_dampak' => $request->realisasi_skala_dampak,
            'realisasi_nilai_probabilitas' => $request->realisasi_nilai_probabilitas,
            'realisasi_skala_probabilitas' => $request->realisasi_skala_probabilitas,
            'realisasi_skala_risiko' => $request->realisasi_skala_risiko,
            'realisasi_level_risiko' => $request->realisasi_level_risiko
        ]);

        return redirect()->route('risk-monitoring.index')->with('success', 'Risk Monitoring updated successfully!');
    }

    public function editStatusKri($id, $periode_monitoring)
    {

        //$risiko = PenyebabRisiko::findOrFail($id);
        // dd($risiko);
        $kri = KRI::findOrFail($id);
        //$monitoring = MonitoringRisiko::where('id', $id)->first();

        $monitoring = MonitoringRisiko::where('risiko_id', $kri->risiko_id)
                                       ->where('periode_monitoring', $periode_monitoring)
                                       ->first();

        if (!$monitoring) {
            return response()->json(['error' => 'Data monitoring tidak ditemukan'], 404);
        }

        $status_kri_terkini = "";
        $nilai_kri_terkini = "";

        if ($periode_monitoring == "Quarter 1") {
            $status_kri_terkini = $kri->status_kri_terkini_q1;
            $nilai_kri_terkini = $kri->nilai_kri_terkini_q1;
        }
        else if ($periode_monitoring == "Quarter 2") {
            $status_kri_terkini = $kri->status_kri_terkini_q2;
            $nilai_kri_terkini = $kri->nilai_kri_terkini_q2;
        }
        else if ($periode_monitoring == "Quarter 3") {
            $status_kri_terkini = $kri->status_kri_terkini_q3;
            $nilai_kri_terkini = $kri->nilai_kri_terkini_q3;
        }
        else if ($periode_monitoring == "Quarter 4") {
            $status_kri_terkini = $kri->status_kri_terkini_q4;
            $nilai_kri_terkini = $kri->nilai_kri_terkini_q4;
        }

        $data = [
            'id' => $kri->id,
            'kri' => $kri->kri,
            'satuan_kri' => $kri->satuan_kri,
            'batas_aman' => $kri->batas_aman,
            'batas_waspada' => $kri->batas_waspada,
            'batas_bahaya' => $kri->batas_bahaya,
            'periode_monitoring' => $monitoring->periode_monitoring,
            'status_kri_terkini' => $status_kri_terkini,
            'nilai_kri_terkini' => $nilai_kri_terkini,
            'mrid' => $monitoring->id
        ];

        return response()->json($data);
    }

    public function updateStatusKri(Request $request)
    {
        try {
            $id = $request->input('id');
            $mrid = $request->input('mrid');

            /*
            $risiko = PenyebabRisiko::findOrFail($id);
            $risiko->status_kri = $request->status_kri;
            $risiko->nilai_kri = $request->nilai_kri;
            $risiko->save();
            */

            $monitoring = MonitoringRisiko::findOrFail($mrid);
            $periode_monitoring = $monitoring->periode_monitoring;

            $kri = KRI::findOrFail($id);
            if($periode_monitoring=="Quarter 1"){
                $kri->status_kri_terkini_q1 = $request->status_kri;
                $kri->nilai_kri_terkini_q1 = $request->nilai_kri;
            }
            else if($periode_monitoring=="Quarter 2"){
                $kri->status_kri_terkini_q2 = $request->status_kri;
                $kri->nilai_kri_terkini_q2 = $request->nilai_kri;
            }
            else if($periode_monitoring=="Quarter 3"){
                $kri->status_kri_terkini_q3 = $request->status_kri;
                $kri->nilai_kri_terkini_q3 = $request->nilai_kri;
            }
            else if($periode_monitoring=="Quarter 4"){
                $kri->status_kri_terkini_q4 = $request->status_kri;
                $kri->nilai_kri_terkini_q4 = $request->nilai_kri;
            }

            $kri->save();

            return response()->json(['status' => 200]);
        } catch (\Exception $e) {
            // If an error occurs, return an error message to be displayed
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function editRealisasi($id, $periode_monitoring)
    {
        $penyebabRisiko = PenyebabRisiko::findOrFail($id);

        $monitoring = MonitoringRisiko::where('risiko_id', $penyebabRisiko->risiko_id)
            ->where('periode_monitoring', $periode_monitoring)
            ->first();
        // dd($risiko);

        if($periode_monitoring=="Quarter 1"){
            $progress_rencana_perlakuan_risiko = $penyebabRisiko->progress_rencana_perlakuan_risiko_q1;
            $realisasi_biaya_perlakuan_risiko = $penyebabRisiko->realisasi_biaya_perlakuan_risiko_q1;
        }
        else if($periode_monitoring=="Quarter 2"){
            $progress_rencana_perlakuan_risiko = $penyebabRisiko->progress_rencana_perlakuan_risiko_q2;
            $realisasi_biaya_perlakuan_risiko = $penyebabRisiko->realisasi_biaya_perlakuan_risiko_q2;
        }
        else if($periode_monitoring=="Quarter 3"){
            $progress_rencana_perlakuan_risiko = $penyebabRisiko->progress_rencana_perlakuan_risiko_q3;
            $realisasi_biaya_perlakuan_risiko = $penyebabRisiko->realisasi_biaya_perlakuan_risiko_q3;
        }
        else if($periode_monitoring=="Quarter 4"){
            $progress_rencana_perlakuan_risiko = $penyebabRisiko->progress_rencana_perlakuan_risiko_q4;
            $realisasi_biaya_perlakuan_risiko = $penyebabRisiko->realisasi_biaya_perlakuan_risiko_q4;
        }

        $data = [
            'id' => $penyebabRisiko->id,
            'penyebab_risiko' => $penyebabRisiko->penyebab_risiko,
            'rencana_perlakuan_risiko' => $penyebabRisiko->rencana_perlakuan_risiko,
            'periode_monitoring' => $monitoring->periode_monitoring,
            'progress_rencana_perlakuan_risiko' => $progress_rencana_perlakuan_risiko,
            'realisasi_biaya_perlakuan_risiko' => $realisasi_biaya_perlakuan_risiko,
            'mrid' => $monitoring->id
        ];

        return response()->json($data);
    }

    public function updateRealisasi(Request $request)
    {
        try {
            $id = $request->input('id');
            $mrid = $request->input('mrid2');

            $monitoring = MonitoringRisiko::findOrFail($mrid);
            $periode_monitoring = $monitoring->periode_monitoring;

            $risiko = PenyebabRisiko::findOrFail($id);

            if($periode_monitoring=="Quarter 1"){
                $risiko->progress_rencana_perlakuan_risiko_q1 = $request->progress_rencana_perlakuan_risiko;
                $risiko->realisasi_biaya_perlakuan_risiko_q1 = $request->realisasi_biaya_perlakuan_risiko;
            }
            else if($periode_monitoring=="Quarter 2"){
                $risiko->progress_rencana_perlakuan_risiko_q2 = $request->progress_rencana_perlakuan_risiko;
                $risiko->realisasi_biaya_perlakuan_risiko_q2 = $request->realisasi_biaya_perlakuan_risiko;
            }
            else if($periode_monitoring=="Quarter 3"){
                $risiko->progress_rencana_perlakuan_risiko_q3 = $request->progress_rencana_perlakuan_risiko;
                $risiko->realisasi_biaya_perlakuan_risiko_q3 = $request->realisasi_biaya_perlakuan_risiko;
            }
            else if($periode_monitoring=="Quarter 4"){
                $risiko->progress_rencana_perlakuan_risiko_q4 = $request->progress_rencana_perlakuan_risiko;
                $risiko->realisasi_biaya_perlakuan_risiko_q4 = $request->realisasi_biaya_perlakuan_risiko;
            }

            $risiko->save();

            return response()->json(['status' => 200]);
        } catch (\Exception $e) {
            // If an error occurs, return an error message to be displayed
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|mimes:jpg,png,pdf,zip|max:2048',
        ]);

        $monitoringId = $request->input('id');
        $files = $request->file('files');

        foreach ($files as $file) {
            // Store the file
            $filePath = $file->store('uploads/monitoring_files', 'public');

            // Save file information to the database
            MonitoringRisikoFile::create([
                'monitoring_risiko_id' => $monitoringId,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->back()->with('success', 'Files uploaded successfully.');
    }

    public function deleteFile($id)
    {
        // Find the file
        $file = MonitoringRisikoFile::findOrFail($id);

        // Delete the file from storage
        if (\Storage::disk('public')->exists($file->file_path)) {
            \Storage::disk('public')->delete($file->file_path);
        }

        // Delete the record from database
        $file->delete();

        // Redirect back with success message
        return back()->with('success', 'File deleted successfully.');
    }

}
