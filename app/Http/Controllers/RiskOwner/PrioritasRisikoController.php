<?php

namespace App\Http\Controllers\RiskOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdentifikasiRisiko;
use App\Models\JenisRisiko;
use App\Models\PeristiwaRisiko;
use App\Models\RiskAnalysis;
use App\Models\User;
use App\Models\KRI;
use App\Models\PenyebabRisiko;
use App\Models\Unit;
use App\Models\Periode;
use App\Models\PrioritasRisiko;
use App\Models\DataBatch;
use App\Models\RisikoUniversitas;
use Auth;

class PrioritasRisikoController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        /*
        $risiko = IdentifikasiRisiko::where(function ($query) {
            $query->where('status_progress', 'risk_owner')
                  ->orWhere('status_progress', 'universitas');
        })
        ->where('periode_id', $periodeActive->id)
        ->where(function ($query) use ($unitId) {
            $query->where('unit_id', $unitId)
                  ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                      $subQuery->where('parent_id', $unitId);
                  });
        })
        ->orderBy('skala_risiko', 'desc')
        ->get();
        */

        $risiko = IdentifikasiRisiko::where(function ($query) {
            $query->where('status', '>=','2');
                  //->orWhere('status_progress', 'universitas');
        })
        ->where('periode_id', $periodeActive->id)
        ->where(function ($query) use ($unitId) {
            $query->where('unit_id', $unitId)
                  ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                      $subQuery->where('parent_id', $unitId);
                  });
        })
        ->orderBy('status_risiko', 'desc')
        ->orderBy('status', 'desc')
        ->orderBy('skala_risiko', 'desc')
        ->get();

        //->get();

        //->pluck('deskripsi_peristiwa_risiko', 'id');
        /*
        $identifikasiRisiko = IdentifikasiRisiko::where('status_progress', 'risk_owner')
        ->orWhere('status_progress', 'universitas')
        ->get();



        $risikoRanking = $identifikasiRisiko->filter(function ($risiko) {
            return $risiko->status_risiko === 'ranking_risk_champion';
        });

        //dd($risikoRanking);

        if ($risikoRanking->isNotEmpty()) {
            $risikoRanking = $risikoRanking->sortByDesc(function ($item) {
                return $item->riskAnalysis->skala_risiko;
            });
        }

        $identifikasiRisiko = $risikoRanking->merge($identifikasiRisiko->filter(function ($risiko) {
            return $risiko->status_risiko !== 'ranking_risk_champion';
        }));

        $risiko = collect();

        if ($unitTypeId == 2 || $unitTypeId == 4) {
            $usersWithSameParent = User::where('parent_id', $user->id)->get();
            $userIds = $usersWithSameParent->pluck('id')->toArray();

            $risiko = $identifikasiRisiko->filter(function ($risiko) use ($userIds) {
                return in_array($risiko->user_id, $userIds);
            });
        } else {
            $risiko = $identifikasiRisiko;
        }

        $usersSameParent = User::where('parent_id', $user->id)->pluck('unit_id');

        // Ambil data unit_id dari variabel usersSameParent
        $unitIds = $usersSameParent->toArray();

        // Ambil data name dari tabel unit berdasarkan unit_id yang ada dalam $unitIds
        $unitChild = Unit::whereIn('id', $unitIds)->pluck('name', 'id');
        */
        $unitChild = Unit::where('parent_id', $unitId)->pluck('name', 'id');
        $unit = Unit::pluck('name', 'id');

        $kategoriJenisRisiko = JenisRisiko::pluck('title','id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title','id');
        $riskAnalysis = RiskAnalysis::pluck('level_risiko','id');
        $risikoId = IdentifikasiRisiko::where('status_progress', 'risk_owner')->first();
        // dd($risiko);

        //get databatch
        $dataBatch = DataBatch::where('unit_id', Auth::user()->unit_id)
            ->where('periode_id', $periodeActive->id)
            ->where('finish', false)
            ->first();

        $status = $dataBatch ? $dataBatch->status : null;

        return view('risk-owner.index',compact('risiko','peristiwaRisiko','kategoriJenisRisiko','riskAnalysis','risikoId', 'unit', 'unitChild', 'status', 'dataBatch'));
    }

    public function view($id)
    {
        $identifikasiRisiko = IdentifikasiRisiko::where('id',$id)->first();
        $penyebabRisiko = PenyebabRisiko::where('risiko_id',$id)->get();
        $kri = KRI::where('risiko_id',$id)->get();

        return view('risk-owner.view',compact('identifikasiRisiko','penyebabRisiko','kri'));
    }

    public function send(Request $request)
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();
        
        /*
        $risiko = IdentifikasiRisiko::where('status_progress', 'risk_owner')
        ->where('periode_id', $periodeActive->id)
        ->where('status_risiko', 'verification')
        ->where(function ($query) use ($unitId) {
            $query->where('unit_id', $unitId)
                ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                    $subQuery->where('parent_id', $unitId);
                });
        })
        ->get();
        */

        $risiko = IdentifikasiRisiko::where('periode_id', $periodeActive->id)
        ->where('status_risiko', 'verification')
        ->where(function ($query) use ($unitId) {
            $query->where('unit_id', $unitId)
                ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                    $subQuery->where('parent_id', $unitId);
                });
        })
        ->get();

        //dd($risiko);

        if ($risiko->isEmpty()) {
            return redirect()->route('risk-owner.index')->with('error', 'Belum ada Risiko yang diterima');
        }

        /*
        $risiko = collect();
        if ($unitTypeId == 2 || $unitTypeId == 4) {
            $usersWithSameParent = User::where('parent_id', $user->id)->get();
            $userIds = $usersWithSameParent->pluck('id')->toArray();

            $risiko = $identifikasiRisiko->filter(function ($risiko) use ($userIds) {
                return in_array($risiko->user_id, $userIds);
            });
        }
        else {
            $risiko = $identifikasiRisiko;
        }
        */

        /*
        $last_batch = PrioritasRisiko::where('unit_id', $unitId)
        ->where('periode_id', $periodeActive->id)
        ->max('batch');
        //dd($last_batch);

        if($last_batch){
            PrioritasRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeActive->id)->where('batch', '<=', $last_batch)->update(['status' => 2]);
        }

        $next_batch = $last_batch + 1;
        */

        $dtBatch = DataBatch::where('periode_id', $periodeActive->id)->where('unit_id', $unitId)->where('finish', false)->first();

        // Perbarui status_progress menjadi 'universitas' untuk risiko yang dipilih
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
            $prioritasRisiko->skala_probabilitas_id = $identifikasiRisiko->riskAnalysis->skala_probabilitas_id;
            $prioritasRisiko->area_dampak = $identifikasiRisiko->riskAnalysis->area_dampak;
            $prioritasRisiko->kategori_dampak = $identifikasiRisiko->riskAnalysis->kategori_dampak;
            $prioritasRisiko->deskripsi_dampak = $identifikasiRisiko->riskAnalysis->deskripsi_dampak;
            $prioritasRisiko->nilai_dampak = $identifikasiRisiko->riskAnalysis->nilai_dampak;
            $prioritasRisiko->skala_dampak = $identifikasiRisiko->riskAnalysis->skala_dampak;
            $prioritasRisiko->nilai_probabilitas = $identifikasiRisiko->riskAnalysis->nilai_probabilitas;
            $prioritasRisiko->skala_risiko = $identifikasiRisiko->riskAnalysis->skala_risiko;
            $prioritasRisiko->level_risiko = $identifikasiRisiko->riskAnalysis->level_risiko;
            $prioritasRisiko->user_id = $user->id;
            $prioritasRisiko->save();

            $identifikasiRisiko->update(['status_risiko' => 'verification']);
        }

        
        //add data batch
        $dataBatch = DataBatch::updateOrCreate(
            [
                'unit_id' => Auth::user()->unit_id,
                'periode_id' => $periodeActive->id,
                'finish' => false,
            ],
            [
                'status' => DataBatch::STATUS_UTAMA
            ]
        );

        return redirect()->route('risk-owner.index')->with('success', 'Konfirmasi Risiko Utama Berhasil!');
    }

    public function edit($id)
    {
        $risiko = IdentifikasiRisiko::findOrFail($id);
        return response()->json($risiko);
    }

    public function verif(Request $request)
    {
        try {
            $id = $request->input('id');
            $risiko = IdentifikasiRisiko::findOrFail($id);
            $risiko->catatan = $request->catatan;
            $risiko->save();
            return response()->json(['status' => 200]);
        } catch (\Exception $e) {
            // If an error occurs, return an error message to be displayed
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function konfirmasi(Request $request)//terima risiko
    {
        // Retrieve the selected item IDs from the request
        $selectedItemIds = $request->input('selected_items', []);

        // Check if any items were selected
        if (empty($selectedItemIds)) {
            return redirect()->route('risk-owner.index')->with('error', 'Pilih Risiko yang hendak diterima');
        }

        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        /*
        $risiko = IdentifikasiRisiko::whereIn('id', $selectedItemIds)
            ->where('periode_id', $periodeActive->id)
            ->where(function ($query) {
                $query->where('status_progress', 'risk_owner')
                    ->orWhere('status_progress', 'universitas');
            })
            ->where(function ($query) use ($unitId) {
                $query->where('unit_id', $unitId)
                    ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                        $subQuery->where('parent_id', $unitId);
                    });
            })
            ->get();
        */

        $risiko = IdentifikasiRisiko::whereIn('id', $selectedItemIds)
            ->where('periode_id', $periodeActive->id)
            ->where('status', '>=', '2')
            ->where(function ($query) use ($unitId) {
                $query->where('unit_id', $unitId)
                    ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                        $subQuery->where('parent_id', $unitId);
                    });
            })
            ->get();

        foreach ($risiko as $identifikasiRisiko) {
            $identifikasiRisiko->update(['status_risiko' => 'verification']);
        }
        /*
        //simpan ke table prioritas risiko
        $last_batch = PrioritasRisiko::where('unit_id', $unitId)
        ->where('periode_id', $periodeActive->id)
        ->max('batch');
        //dd($last_batch);

        if($last_batch){
            PrioritasRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeActive->id)->where('batch', '<=', $last_batch)->update(['status' => 2]);
        }

        $next_batch = $last_batch + 1;

        foreach ($risiko as $identifikasiRisiko) {
            $prioritasRisiko = new PrioritasRisiko;
            $prioritasRisiko->unit_id = $unitId;
            $prioritasRisiko->periode_id = $periodeActive->id;
            $prioritasRisiko->risiko_id = $identifikasiRisiko->id;
            $prioritasRisiko->batch = $next_batch;
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
            $prioritasRisiko->skala_probabilitas_id = $identifikasiRisiko->riskAnalysis->skala_probabilitas_id;
            $prioritasRisiko->area_dampak = $identifikasiRisiko->riskAnalysis->area_dampak;
            $prioritasRisiko->kategori_dampak = $identifikasiRisiko->riskAnalysis->kategori_dampak;
            $prioritasRisiko->deskripsi_dampak = $identifikasiRisiko->riskAnalysis->deskripsi_dampak;
            $prioritasRisiko->nilai_dampak = $identifikasiRisiko->riskAnalysis->nilai_dampak;
            $prioritasRisiko->skala_dampak = $identifikasiRisiko->riskAnalysis->skala_dampak;
            $prioritasRisiko->nilai_probabilitas = $identifikasiRisiko->riskAnalysis->nilai_probabilitas;
            $prioritasRisiko->skala_risiko = $identifikasiRisiko->riskAnalysis->skala_risiko;
            $prioritasRisiko->level_risiko = $identifikasiRisiko->riskAnalysis->level_risiko;
            $prioritasRisiko->user_id = $user->id;
            $prioritasRisiko->save();

            $identifikasiRisiko->update(['status_risiko' => 'verification']);
        }
        */

        return redirect()->route('risk-owner.index')->with('success', 'Risiko Berhasil Diverifikasi/Diterima!');
    }

    public function sendUniversitas(Request $request){
        //cek dulu data batch universitas, jika ada update jika tidak maka create
        $univ = Unit::where('unit_type_id', 1)->first();
        $periodeActive = Periode::where('status','active')->first();
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $selectedItemIds = $request->input('selected_items', []);
        if (empty($selectedItemIds)) {
            return redirect()->route('risk-owner.index')->with('error', 'Pilih Risiko yang hendak dikirim ke Universitas');
        }

        if($univ){
            //add data batch
            $dtBatch = DataBatch::where('periode_id', $periodeActive->id)->where('unit_id', $univ->id)->where('finish', false)->first();

            $next_batch = 1;
            if(!$dtBatch){
                $last_batch = DataBatch::where('unit_id', $univ->id)
                ->where('periode_id', $periodeActive->id)
                ->where('finish', true)
                ->max('batch');
                
                if($last_batch){
                    $next_batch = $last_batch + 1;
                }
                else{
                    $next_batch = 1;
                }
            }
            else{
                $next_batch = $dtBatch->batch;
            }

            $dataBatch = DataBatch::updateOrCreate(
                [
                    'unit_id' => $univ->id,//id universitas
                    'periode_id' => $periodeActive->id,
                    'finish' => false,
                    'batch' => $next_batch
                ],
                [
                    'status' => DataBatch::STATUS_PROSES
                ]
            );

            //dapatkan id batch
            $batch_id = $dataBatch->batch;

            //masukkan data risiko_universitas sesuai unit dan batch dengan cek id risiko, unit, periode, batch
            $risiko = IdentifikasiRisiko::whereIn('id', $selectedItemIds)
            ->where('periode_id', $periodeActive->id)
            ->get();

            foreach ($risiko as $identifikasiRisiko) {
                $risikoUniversitas = new RisikoUniversitas;
                $risikoUniversitas->unit_id = $unitId;
                $risikoUniversitas->periode_id = $periodeActive->id;
                $risikoUniversitas->risiko_id = $identifikasiRisiko->id;
                $risikoUniversitas->batch = $batch_id;
                $risikoUniversitas->kategori_risiko_id = $identifikasiRisiko->kategori_risiko_id;
                $risikoUniversitas->jenis_risiko_id = $identifikasiRisiko->jenis_risiko_id;
                $risikoUniversitas->peristiwa_risiko_id = $identifikasiRisiko->peristiwa_risiko_id;
                $risikoUniversitas->target_capaian_kinerja = $identifikasiRisiko->target_capaian_kinerja;
                $risikoUniversitas->rencana_kegiatan = $identifikasiRisiko->rencana_kegiatan;
                $risikoUniversitas->deskripsi_rencana_kegiatan = $identifikasiRisiko->deskripsi_rencana_kegiatan;
                $risikoUniversitas->deskripsi_peristiwa_risiko = $identifikasiRisiko->deskripsi_peristiwa_risiko;
                $risikoUniversitas->type = $identifikasiRisiko->type;
                $risikoUniversitas->kontrol_eksisting = $identifikasiRisiko->kontrol_eksisting;
                $risikoUniversitas->penilaian_efektifitas_kontrol = $identifikasiRisiko->penilaian_efektifitas_kontrol;
                $risikoUniversitas->perkiraan_waktu_terpapar_risiko_mulai = $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai;
                $risikoUniversitas->perkiraan_waktu_terpapar_risiko_akhir = $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir;
                $risikoUniversitas->skala_probabilitas_id = $identifikasiRisiko->riskAnalysis->skala_probabilitas_id;
                $risikoUniversitas->area_dampak = $identifikasiRisiko->riskAnalysis->area_dampak;
                $risikoUniversitas->kategori_dampak = $identifikasiRisiko->riskAnalysis->kategori_dampak;
                $risikoUniversitas->deskripsi_dampak = $identifikasiRisiko->riskAnalysis->deskripsi_dampak;
                $risikoUniversitas->nilai_dampak = $identifikasiRisiko->riskAnalysis->nilai_dampak;
                $risikoUniversitas->skala_dampak = $identifikasiRisiko->riskAnalysis->skala_dampak;
                $risikoUniversitas->nilai_probabilitas = $identifikasiRisiko->riskAnalysis->nilai_probabilitas;
                $risikoUniversitas->skala_risiko = $identifikasiRisiko->riskAnalysis->skala_risiko;
                $risikoUniversitas->level_risiko = $identifikasiRisiko->riskAnalysis->level_risiko;
                $risikoUniversitas->user_id = $user->id;
                $risikoUniversitas->save();
                
                //update is university, status_progress di identifikasi risiko
                $identifikasiRisiko->update(['status_progress' => 'universitas', 'is_university' => 1]);
            }
            
            //kunci unit supaya tidak bisa kirim risiko ke universitas sebelum universitas selesai
            $dataBatch = DataBatch::updateOrCreate(
                [
                    'unit_id' => $unitId,//id unit
                    'periode_id' => $periodeActive->id,
                    'finish' => false,
                ],
                [
                    'status' => DataBatch::STATUS_VERIFIKASI_UNIVERSITAS
                ]
            );

            return redirect()->route('risk-owner.index')->with('success', 'Risiko Berhasil Dikirimkan ke Universitas!');
        }
        else{
            return redirect()->route('risk-owner.index')->with('error', 'Unit Universitas Tidak Ada');
        }
    }

    public function return(Request $request)
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        $risiko = IdentifikasiRisiko::where(function ($query) {
            $query->where('status_progress', 'risk_owner')
                  ->orWhere('status_risiko', 'ranking_risk_champion')
                  ->orWhere('status', 3);
        })
        ->where('periode_id', $periodeActive->id)
        ->where(function ($query) use ($unitId) {
            $query->where('unit_id', $unitId)
                  ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                      $subQuery->where('parent_id', $unitId);
                  });
        })
        ->orderBy('skala_risiko', 'desc')
        ->get();

        if ($risiko->isEmpty()) {
            return redirect()->route('risk-owner.index')->with('error', 'Tidak ada data risiko yang dapat dikembalikan!');
        }
        else{
            foreach ($risiko as $item) {
                $item->update([
                    'status_risiko' => 'proses',
                    'status_progress' => 'risk_champion',
                    'status' => 2
                ]);
            }
        }

        $dataBatch = DataBatch::updateOrCreate(
            [
                'unit_id' => Auth::user()->unit_id,
                'periode_id' => $periodeActive->id,
                'finish' => false,
            ],
            [
                'status' => DataBatch::STATUS_KIRIM
            ]
        );

        return redirect()->route('risk-owner.index')->with('success', 'Risiko Berhasil Dikembalikan ke Risk Champion!');
    }
}