<?php

namespace App\Http\Controllers\RiskChampion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdentifikasiRisiko;
use App\Models\JenisRisiko;
use App\Models\PeristiwaRisiko;
use App\Models\RiskAnalysis;
use App\Models\User;
use App\Models\Unit;
use App\Models\RiskMap;
use App\Models\Periode;
use App\Models\DataBatch;

use Auth;

class RankingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        /*
        $risiko = IdentifikasiRisiko::where('status_progress', 'risk_champion')
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

        $risiko = IdentifikasiRisiko::where('status', '>=', '2')
        ->where('periode_id', $periodeActive->id)
        ->where(function ($query) use ($unitId) {
            $query->where('unit_id', $unitId)
                  ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                      $subQuery->where('parent_id', $unitId);
                  });
        })
        ->orderBy('status', 'desc') 
        ->orderBy('skala_risiko', 'desc')
        ->get();

        $averageSkalaRisiko = $risiko->isEmpty() ? 0 : $risiko->avg(function ($item) {
            return $item->riskAnalysis->skala_risiko;
        });

        // Ambil angka sebelum koma dari rata-rata skala risiko
        $averageSkalaRisikoFloor = floor($averageSkalaRisiko);

        // Ambil data level_risiko dari tabel RiskMap berdasarkan nilai_risiko yang sesuai dengan angka sebelum koma dari averageSkalaRisiko
        $levelRisiko = RiskMap::where('nilai_risiko', $averageSkalaRisikoFloor)->value('level_risiko');


        $unitChild = Unit::where('parent_id', $unitId)->pluck('name', 'id');

        $unit = Unit::pluck('name', 'id');

        $kategoriJenisRisiko = JenisRisiko::pluck('title', 'id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
        $riskAnalysis = RiskAnalysis::pluck('level_risiko', 'id');

        //get databatch
        $dataBatch = DataBatch::where('unit_id', Auth::user()->unit_id)
            ->where('periode_id', $periodeActive->id)
            ->where('finish', false)
            ->first();

        $status = $dataBatch ? $dataBatch->status : null;

        // dd($risiko);
        return view('risk-champion.index', compact('risiko', 'peristiwaRisiko', 'kategoriJenisRisiko', 'riskAnalysis', 'averageSkalaRisiko', 'levelRisiko', 'unit', 'unitChild', 'status'))->with('averageSkalaRisiko', number_format($averageSkalaRisiko, 2));
        
    }

    public function ranking(Request $request)
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        $risiko = IdentifikasiRisiko::where('status_progress', 'risk_champion')
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
            return redirect()->route('risk-champion.index')->with('error', 'Tidak ada data risiko!');
        }

        // Filter data untuk memastikan skala_risiko tidak null
        $filteredData = $risiko->filter(function ($item) {
            return $item->riskAnalysis->skala_risiko !== null;
        });

        // Hitung rata-rata skala_risiko dari data yang telah difilter
        $averageSkalaRisiko = $filteredData->avg(function ($item) {
            return $item->riskAnalysis->skala_risiko;
        });

        // Filter data lagi untuk mendapatkan data di atas rata-rata
        $aboveAverageData = $filteredData->filter(function ($item) use ($averageSkalaRisiko) {
            return $item->riskAnalysis->skala_risiko > $averageSkalaRisiko;
        });

        // Update status_risiko menjadi 'ranking_risk_champion' untuk data di atas rata-rata
        $aboveAverageData->each(function ($item) {
            $item->update([
                'status_risiko' => 'ranking_risk_champion',
            ]);
        });

        // dd($risiko);

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

        return redirect()->route('risk-champion.index')->with('success', 'Ranking Risiko successfully!');
    }

    public function send(Request $request)
    {
        // Retrieve the selected item IDs from the request
        $selectedItemIds = $request->input('selected_items', []);

        // Check if any items were selected
        if (empty($selectedItemIds)) {
            return redirect()->route('risk-champion.index')->with('error', 'Tidak ada risiko yang dikirim!');
        }

        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status', 'active')->first();

        // Retrieve the selected risks that match the provided IDs and other conditions
        $risiko = IdentifikasiRisiko::whereIn('id', $selectedItemIds)
            //->where('status_risiko', 'ranking_risk_champion') //tidak harus yang terpilih sebagai ranking yang penting yang sudah dikirim
            ->where('status', 2)
            ->where('periode_id', $periodeActive->id)
            ->where(function ($query) use ($unitId) {
                $query->where('unit_id', $unitId)
                      ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                          $subQuery->where('parent_id', $unitId);
                      });
            })
            ->orderBy('skala_risiko', 'desc')
            ->get();

        // Update status_progress to 'risk_owner' for the selected risks
        foreach ($risiko as $identifikasiRisiko) {
            $identifikasiRisiko->update([
                'status_progress' => 'risk_owner',
                'status' => 3
            ]);
        }

        /*
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
        */

        return redirect()->route('risk-champion.index')->with('success', 'Selected risks have been sent to Risk Owner successfully!');
    }

    public function return(Request $request)
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $periodeActive = Periode::where('status','active')->first();

        /*
        $risiko = IdentifikasiRisiko::where('status_progress', 'risk_champion')
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
            $query->where('status_progress', 'risk_champion')
                  ->orWhere('status_risiko', 'ranking_risk_champion')
                  ->orWhere('status', 2);
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
            return redirect()->route('risk-champion.index')->with('error', 'Tidak ada data risiko yang dapat dikembalikan!');
        }
        else{
            foreach ($risiko as $item) {
                $item->update([
                    'status_risiko' => 'proses',
                    'status_progress' => 'risk_officer',
                    'status' => 1
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
                'status' => DataBatch::STATUS_PROSES
            ]
        );

        return redirect()->route('risk-champion.index')->with('success', 'Risiko Berhasil Dikembalikan ke Risk Officer!');
    }
}