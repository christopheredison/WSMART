<?php

namespace App\Http\Controllers\RiskOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LossEvent;
use App\Models\LossEventChild;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\Unit;
use App\Models\Periode;
use Carbon\Carbon;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\LossEventFile;
use Illuminate\Support\Facades\Storage;

class LossEventDatabaseController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        $lossEvent = LossEvent::with('child')->where('periode_id', $periodeActive->id)->where('unit_id', $unitId)->get();
        return view('loss-event-database.index',compact('lossEvent'));
    }

    public function create()
    {
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        return view('loss-event-database.create',compact('kategoriRisiko','jenisRisiko'));
    }

    public function getJenisRisiko($kategoriRisikoId)
    {
        $jenisRisiko = JenisRisiko::where('kategori_risiko_id', $kategoriRisikoId)->pluck('title', 'id');
        return response()->json($jenisRisiko);
    }

    public function store(Request $request)
    {
        //dd($request->all());
        
        $validator = Validator::make($request->all(), [
            'kategori_risiko_id' => 'required',
            'jenis_risiko_id' => 'required',
            //'nilai_kerugian_finansial' => 'required',
            //'nilai_kerugian_non_fungsional' => 'required',
            'peristiwa_kerugian' => 'required',
            'unit_penanggung_jawab' => 'required',
            'perkiraan_waktu_terpapar_risiko' => 'required',
            'rekomendasi_perbaikan' => 'required|array|min:1', // Validasi untuk array
            'rekomendasi_perbaikan.*' => 'required|string', // Validasi untuk setiap elemen dalam array
        ]);
        

        if ($validator->fails()) {
            return redirect()->route('loss-event-database.create')
                             ->withErrors($validator)
                             ->withInput();
        }



        $periodeActive = Periode::where('status','active')->first();

        $perkiraan_waktu_terpapar = explode(" to ", $request->perkiraan_waktu_terpapar_risiko);
        if(count($perkiraan_waktu_terpapar)<2){
            $rentang_kejadian_awal = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[0])->format('Y-m-d');
            $rentang_kejadian_akhir = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[0])->format('Y-m-d');

        }
        else{
            $rentang_kejadian_awal = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[0])->format('Y-m-d');
            $rentang_kejadian_akhir = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[1])->format('Y-m-d');

        }
        //$tanggal_kejadian = Carbon::createFromFormat('d/m/Y', $request->tanggal_kejadian)->format('Y-m-d');

        $nilai_kerugian_finansial = preg_replace('/\D/', '', $request->nilai_kerugian_finansial);

        $lossEvent = LossEvent::create([
            'periode_id' => $periodeActive->id,
            'unit_id' => Auth::user()->unit_id,
            'user_id' => Auth::user()->id,
            'rentang_kejadian_awal' => $rentang_kejadian_awal,
            'rentang_kejadian_akhir' => $rentang_kejadian_akhir,
            'tanggal_kejadian' => $rentang_kejadian_awal,
            'kategori_risiko_id' => $request->kategori_risiko_id,
            'jenis_risiko_id' => $request->jenis_risiko_id,
            'nilai_kerugian_finansial' => $nilai_kerugian_finansial,
            'nilai_kerugian_non_fungsional' => $request->nilai_kerugian_non_fungsional,
            'peristiwa_kerugian' => $request->peristiwa_kerugian,
            'unit_penanggung_jawab' => $request->unit_penanggung_jawab,
        ]);

        $lossEventId = $lossEvent->id;

        foreach ($request->rekomendasi_perbaikan as $key => $rekomendasi_perbaikan) {
            $rekomendasiData = LossEventChild::create([
                'loss_event_id' => $lossEventId,
                'rekomendasi_perbaikan' => $rekomendasi_perbaikan
            ]);
        }

        return redirect()->route('loss-event-database.index')->with('success', 'Loss Event Database Created successfully!');
    }
    public function edit($id)
    {
        $lossEvent = LossEvent::findOrFail($id);
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $jenisRisiko = JenisRisiko::where('kategori_risiko_id', $lossEvent->kategori_risiko_id)->pluck('title','id');

        //get rekomendasi
        $rekomendasi = LossEventChild::where('loss_event_id', $id)->get();
        //dd($rekomendasi->count());
        return view('loss-event-database.edit', compact('lossEvent', 'kategoriRisiko', 'jenisRisiko', 'rekomendasi'));
    }

    public function update(Request $request, $id)
    {
        $lossEvent = LossEvent::findOrFail($id);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'kategori_risiko_id' => 'required',
            'jenis_risiko_id' => 'required',
            //'nilai_kerugian_finansial' => 'required',
            //'nilai_kerugian_non_fungsional' => 'required',
            'peristiwa_kerugian' => 'required',
            'unit_penanggung_jawab' => 'required',
            'perkiraan_waktu_terpapar_risiko' => 'required',
            'rekomendasi_perbaikan' => 'required|array|min:1', // Validasi untuk array
            'rekomendasi_perbaikan.*' => 'required|string', // Validasi untuk setiap elemen dalam array
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $perkiraan_waktu_terpapar = explode(" to ", $request->perkiraan_waktu_terpapar_risiko);
        $rentang_kejadian_awal = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[0])->format('Y-m-d');
        $rentang_kejadian_akhir = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[1])->format('Y-m-d');

        $nilai_kerugian_finansial = preg_replace('/\D/', '', $request->nilai_kerugian_finansial);

        // Proses update data
        $lossEvent->update([
            'rentang_kejadian_awal' => $rentang_kejadian_awal,
            'rentang_kejadian_akhir' => $rentang_kejadian_akhir,
            'tanggal_kejadian' => $rentang_kejadian_awal,
            'kategori_risiko_id' => $request->kategori_risiko_id,
            'jenis_risiko_id' => $request->jenis_risiko_id,
            'nilai_kerugian_finansial' => $nilai_kerugian_finansial,
            'nilai_kerugian_non_fungsional' => $request->nilai_kerugian_non_fungsional,
            'peristiwa_kerugian' => $request->peristiwa_kerugian,
            'unit_penanggung_jawab' => $request->unit_penanggung_jawab,
        ]);

        //delete all loss child
        LossEventChild::where('loss_event_id', $id)->delete();

        foreach ($request->rekomendasi_perbaikan as $key => $rekomendasi_perbaikan) {
            $rekomendasiData = LossEventChild::create([
                'loss_event_id' => $id,
                'rekomendasi_perbaikan' => $rekomendasi_perbaikan
            ]);
        }

        return redirect()->route('loss-event-database.index')->with('success', 'Loss Event Database updated successfully!');
    }

    public function destroy($lossEvent)
    {
        $lossEventId = LossEvent::where('id',$lossEvent)->first();
        $lossEventId->delete();

        return redirect()->route('loss-event-database.index')->with('success', 'Loss Event Database deleted successfully!');
    }

    public function editRekomendasi($id)
    {
        $lossEvent = LossEvent::with('child')->findOrFail($id);
        return response()->json($lossEvent);
    }

    public function updateRekomendasi(Request $request)
    {
        try {
            $id = $request->input('id');

            $lossEvent = LossEvent::findOrFail($id);

            // Update, delete, and add new child records
            if ($request->has('rekomendasi_perbaikan')) {
                foreach ($request->rekomendasi_perbaikan as $key => $rekomendasi_perbaikan) {
                    if (!empty($rekomendasi_perbaikan)) {
                        if (isset($lossEvent->child[$key])) {
                            // Update existing child record
                            $lossEvent->child[$key]->rekomendasi_perbaikan = $rekomendasi_perbaikan;
                            $lossEvent->child[$key]->save();
                        } else {
                            // Create new child record
                            $lossEventChild = new LossEventChild;
                            $lossEventChild->loss_event_id = $lossEvent->id;
                            $lossEventChild->rekomendasi_perbaikan = $rekomendasi_perbaikan;
                            $lossEventChild->save();
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

    public function storeFile(Request $request)
    {
        $request->validate([
            'files.*' => 'required|mimes:jpg,png,pdf,zip|max:2048', // Adjust file validation as per your requirements
            // 'loss_event_id' => 'required|exists:loss_events,id',
        ]);

        $loss_event_id = $request->input('id');
        foreach ($request->file('files') as $file) {
            $filePath = $file->store('uploads/loss_event_files', 'public');
            LossEventFile::create([
                'loss_event_id' => $loss_event_id,
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Files uploaded successfully.');
    }

    public function destroyFile(LossEventFile $file)
    {
        Storage::delete($file->file_path);
        $file->delete();

        return back()->with('success', 'File deleted successfully.');
    }
}
