<?php

namespace App\Http\Controllers\Universitas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdentifikasiRisiko;
use App\Models\JenisRisiko;
use App\Models\PeristiwaRisiko;
use App\Models\RiskAnalysis;
use App\Models\Unit;
use App\Models\User;
use App\Models\RiskMap;
use App\Models\Periode;
use App\Models\DataBatch;
use App\Models\RisikoUniversitas;
use App\Models\PrioritasRisiko;
use Auth;

class UniversitasController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $periodeActive = Periode::where('status','active')->first();
        $kategoriJenisRisiko = JenisRisiko::pluck('title','id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title','id');
        $riskAnalysis = RiskAnalysis::pluck('level_risiko','id');


        //$risiko = IdentifikasiRisiko::where('status_progress', 'universitas')->get();
        //get data batch universitas

        $dataBatch = DataBatch::where('unit_id', Auth::user()->unit_id)
            ->where('periode_id', $periodeActive->id)
            ->where('finish', false)
            ->first();  

        if (!$dataBatch) {
            $dataBatch = DataBatch::where('unit_id', Auth::user()->unit_id)
                ->where('periode_id', $periodeActive->id)
                ->orderBy('id', 'desc')
                ->first();
        }
        
        $status = $dataBatch ? $dataBatch->status : null;  
        
        if(!$dataBatch){
            $risiko = [];
            $risikoRanking = $risiko;
            $risikoPrioritas = [];
            $averageSkalaRisikoFloor = 0;
            $averageSkalaRisiko = 0;
        }
        else{
            $risiko = RisikoUniversitas::where('batch', $dataBatch->batch)
                ->where('periode_id', $periodeActive->id)
                ->where('status', '<=', 3)
                ->get();

            /*
            $risikoRanking = $risiko->filter(function ($risikos) {
                return $risikos->status_risiko === 'ranking_universitas';
            });

            if ($risikoRanking->isNotEmpty()) {
                $risikoRanking = $risikoRanking->sortByDesc(function ($item) {
                    return $item->riskAnalysis->skala_risiko;
                });
            }
            */

            $risikoRanking = $risiko;
            if ($risikoRanking->isNotEmpty()) {
                $risikoRanking = $risikoRanking->sortByDesc(function ($item) {
                    return $item->skala_risiko;
                });
            }
            
            /*
            $risiko = $risikoRanking->merge($risiko->filter(function ($risikos) {
                return $risikos->status_risiko !== 'ranking_universitas';
            }));

            $risikoPrioritas = IdentifikasiRisiko::where('status_risiko', 'ranking_universitas')->get();

            $averageSkalaRisiko = $risiko->avg(function ($item) {
                return $item->riskAnalysis->skala_risiko;
            });
            */

            //$risikoPrioritas = IdentifikasiRisiko::where('status_risiko', 'ranking_universitas')->get();

            $risikoPrioritas = RisikoUniversitas::where('batch', $dataBatch->batch)
            ->where('periode_id', $periodeActive->id)
            ->whereIn('status', [3, 4])
            ->get();

            $averageSkalaRisiko = $risiko->avg(function ($item) {
                return $item->skala_risiko;
            });
            

            // Ambil angka sebelum koma dari rata-rata skala risiko
            $averageSkalaRisikoFloor = floor($averageSkalaRisiko);
        }
        

        // Ambil data level_risiko dari tabel RiskMap berdasarkan nilai_risiko yang sesuai dengan angka sebelum koma dari averageSkalaRisiko
        $levelRisiko = RiskMap::where('nilai_risiko', $averageSkalaRisikoFloor)->value('level_risiko');

        $usersSameParent = User::where('parent_id', $user->id)->pluck('unit_id');

        // Ambil data unit_id dari variabel usersSameParent
        $unitIds = $usersSameParent->toArray();

        // Ambil data name dari tabel unit berdasarkan unit_id yang ada dalam $unitIds
        $unitChild = Unit::whereIn('id', $unitIds)->pluck('name', 'id');
        $unit = Unit::pluck('name', 'id');        

        // dd($levelRisiko);
        return view('universitas.index',compact('risiko','peristiwaRisiko','kategoriJenisRisiko','riskAnalysis','averageSkalaRisiko','risikoPrioritas','levelRisiko','unit','unitChild', 'dataBatch', 'status'));
    }

    public function rankingOld(Request $request)
    {
        $data = IdentifikasiRisiko::with('penyebabRisiko', 'riskAnalysis', 'rencanaPerlakuanRisiko')
        ->where('status_progress', 'universitas')
        ->get();

        $filteredData = $data->filter(function ($item) {
            return $item->riskAnalysis->skala_risiko !== null;
        });

        $averageSkalaRisiko = $filteredData->avg(function ($item) {
            return $item->riskAnalysis->skala_risiko;
        });

        $aboveAverageData = $filteredData->filter(function ($item) use ($averageSkalaRisiko) {
            return $item->riskAnalysis->skala_risiko > $averageSkalaRisiko;
        });

        $aboveAverageData->each(function ($item) {
            $item->update([
                'status_risiko' => 'ranking_universitas',
            ]);
        });

        return redirect()->route('universitas.index')->with('success', 'Ranking Risiko successfully!');
    }

    public function ranking(Request $request)
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        $data = RisikoUniversitas::where('status', '<=', '2')->where('periode_id', $periodeActive->id)
        ->get();

        $filteredData = $data->filter(function ($item) {
            return $item->skala_risiko !== null;
        });

        $averageSkalaRisiko = $filteredData->avg(function ($item) {
            return $item->skala_risiko;
        });

        $aboveAverageData = $filteredData->filter(function ($item) use ($averageSkalaRisiko) {
            return $item->skala_risiko > $averageSkalaRisiko;
        });

        $aboveAverageData->each(function ($item) {
            $item->update([
                'status' => '2',
            ]);
        });

        $dataBatch = DataBatch::updateOrCreate(
            [
                'unit_id' => Auth::user()->unit_id,
                'periode_id' => $periodeActive->id,
                'finish' => false,
            ],
            [
                'status' => DataBatch::STATUS_RANKING
            ]
        );

        return redirect()->route('universitas.index')->with('success', 'Ranking Risiko successfully!');
    }

    public function send(Request $request){
        // Retrieve the selected item IDs from the request
        $selectedItemIds = $request->input('selected_items', []);

        // Check if any items were selected
        if (empty($selectedItemIds)) {
            return redirect()->route('universitas.index')->with('error', 'Pilih Risiko yang Diterima!');
        }

        //dd($selectedItemIds);

        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status', 'active')->first();

        $risiko = RisikoUniversitas::whereIn('id', $selectedItemIds)
            ->where('periode_id', $periodeActive->id)
            ->get();

        //dd($risiko);

        foreach ($risiko as $data) {
            $data->update([
                'status' => 3
            ]);
        }   

        $dataBatch = DataBatch::updateOrCreate(
            [
                'unit_id' => Auth::user()->unit_id,
                'periode_id' => $periodeActive->id,
                'finish' => false,
            ],
            [
                'status' => DataBatch::STATUS_VERIFIKASI
            ]
        );

        return redirect()->route('universitas.index')->with('success', 'Risiko Diterima!');
    }

    public function konfirmasi(Request $request)//terima risiko
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        $risiko = RisikoUniversitas::where('periode_id', $periodeActive->id)
        ->where('status', '3')
        ->get();

        $dtBatch = DataBatch::where('periode_id', $periodeActive->id)->where('unit_id', $unitId)->where('finish', false)->first();

        foreach ($risiko as $identifikasiRisiko) {
            //$identifikasiRisiko->update(['status_progress' => 'universitas', 'status' => 4]);
            $identifikasiRisiko->update(['status' => 4]);
            //simpan ke prioritas risiko
            $prioritasRisiko = new PrioritasRisiko;
            $prioritasRisiko->unit_id = $unitId;
            $prioritasRisiko->periode_id = $periodeActive->id;
            $prioritasRisiko->risiko_id = $identifikasiRisiko->id;
            //$prioritasRisiko->batch = $next_batch;
            $prioritasRisiko->batch = $dtBatch->batch;
            $prioritasRisiko->kategori_risiko_id = $identifikasiRisiko->kategori_risiko_id;
            $prioritasRisiko->jenis_risiko_id = $identifikasiRisiko->jenis_risiko_id;
            $prioritasRisiko->peristiwa_risiko_id = $identifikasiRisiko->peristiwa_risiko_id;
            $prioritasRisiko->target_capaian_kinerja = $identifikasiRisiko->target_capaian_kinerja;
            $prioritasRisiko->rencana_kegiatan = $identifikasiRisiko->rencana_kegiatan;
            $prioritasRisiko->deskripsi_rencana_kegiatan = $identifikasiRisiko->deskripsi_rencana_kegiatan;
            $prioritasRisiko->deskripsi_peristiwa_risiko = $identifikasiRisiko->deskripsi_peristiwa_risiko;
            $prioritasRisiko->type = $identifikasiRisiko->type;
            $prioritasRisiko->kontrol_eksisting = $identifikasiRisiko->kontrol_eksisting;
            $prioritasRisiko->penilaian_efektifitas_kontrol = $identifikasiRisiko->penilaian_efektifitas_kontrol;
            $prioritasRisiko->perkiraan_waktu_terpapar_risiko_mulai = $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai;
            $prioritasRisiko->perkiraan_waktu_terpapar_risiko_akhir = $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir;
            $prioritasRisiko->skala_probabilitas_id = $identifikasiRisiko->skala_probabilitas_id;
            $prioritasRisiko->area_dampak = $identifikasiRisiko->area_dampak;
            $prioritasRisiko->kategori_dampak = $identifikasiRisiko->kategori_dampak;
            $prioritasRisiko->deskripsi_dampak = $identifikasiRisiko->deskripsi_dampak;
            $prioritasRisiko->nilai_dampak = $identifikasiRisiko->nilai_dampak;
            $prioritasRisiko->skala_dampak = $identifikasiRisiko->skala_dampak;
            $prioritasRisiko->nilai_probabilitas = $identifikasiRisiko->nilai_probabilitas;
            $prioritasRisiko->skala_risiko = $identifikasiRisiko->skala_risiko;
            $prioritasRisiko->level_risiko = $identifikasiRisiko->level_risiko;
            $prioritasRisiko->user_id = $user->id;
            $prioritasRisiko->save();
        }

        $dtBatch->update([
            'status' => DataBatch::STATUS_FINISH,
            'finish' => 1
        ]);

        return redirect()->route('universitas.index')->with('success', 'Konfirmasi Risiko Utama Berhasil!');
    }
}
