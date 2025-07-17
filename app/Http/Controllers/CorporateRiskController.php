<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IdentifikasiRisiko;
use App\Models\Unit;
use App\Models\Periode;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\PeristiwaRisiko;
use App\Models\DataBatch;

class CorporateRiskController extends Controller
{
    public function index(Request $request)
    {
        // Ambil periode_id dari parameter URL
        $periodeId = $request->query('pid');
        $unitId = $request->query('unit_id');
        
        // Jika tidak ada parameter periode, gunakan periode aktif
        if (!$periodeId) {
            $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
            $periodeId = $activePeriode ? $activePeriode->id : null;
        }

        // Ambil data periode yang dipilih
        $selectedPeriode = Periode::find($periodeId);
        
        // Query untuk risiko utama (main)
        $risikoMainQuery = IdentifikasiRisiko::with([
            'unit',
            'user',
            'periode',
            'kategoriRisiko',
            'jenisRisiko',
            'peristiwaRisiko',
            'riskAnalysis',
        ])
        ->whereIn('status_risiko', [
            IdentifikasiRisiko::STATUS_RISIKO_MAIN,
            IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION
        ]);

        // Query untuk risiko corporate
        $risikoCorporateQuery = IdentifikasiRisiko::with([
            'unit',
            'user',
            'periode',
            'kategoriRisiko',
            'jenisRisiko',
            'peristiwaRisiko',
            'riskAnalysis',
        ])
        ->where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_CORPORATE);

        // Filter berdasarkan periode jika ada
        if ($periodeId) {
            $risikoMainQuery->where('periode_id', $periodeId);
            $risikoCorporateQuery->where('periode_id', $periodeId);
        }

        // Filter berdasarkan unit jika ada
        if ($unitId) {
            $risikoMainQuery->where('unit_id', $unitId);
            $risikoCorporateQuery->where('unit_id', $unitId);
        }

        // Ambil data risiko
        $risikoMain = $risikoMainQuery->get();
        $risikoCorporate = $risikoCorporateQuery->get();

        // Urutkan risiko berdasarkan status, skala risiko, dan eksposur risiko
        $risikoMain = $risikoMain->sortBy(function($risk) {
            // Prioritaskan risiko rekomendasi korporat
            $statusPriority = $risk->status_risiko == IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION ? 0 : 1;
            
            // Kemudian urutkan berdasarkan skala risiko (nilai lebih tinggi lebih dulu)
            $skalaRisiko = -1 * ($risk->riskAnalysis->skala_risiko ?? 0);
            
            // Jika skala risiko sama, urutkan berdasarkan eksposur risiko (nilai lebih tinggi lebih dulu)
            $eksposurRisiko = -1 * ($risk->riskAnalysis->eksposur_risiko ?? 0);
            
            return [$statusPriority, $skalaRisiko, $eksposurRisiko];
        })->values();

        // Urutkan risiko corporate berdasarkan skala risiko dan eksposur risiko
        $risikoCorporate = $risikoCorporate->sortBy(function($risk) {
            // Urutkan berdasarkan skala risiko (nilai lebih tinggi lebih dulu)
            $skalaRisiko = -1 * ($risk->riskAnalysis->skala_risiko ?? 0);
            
            // Jika skala risiko sama, urutkan berdasarkan eksposur risiko (nilai lebih tinggi lebih dulu)
            $eksposurRisiko = -1 * ($risk->riskAnalysis->eksposur_risiko ?? 0);
            
            return [$skalaRisiko, $eksposurRisiko];
        })->values();

        // Data untuk filter
        $unit = Unit::pluck('name', 'id');
        $periodes = Periode::orderBy('tahun', 'desc')->pluck('tahun', 'id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
        $jenisRisiko = JenisRisiko::pluck('title','id');

        // Cek status DataBatch untuk menentukan apakah tombol ranking dan jadikan risiko corporate ditampilkan
        $dataBatch = null;
        if ($periodeId) {
            $dataBatch = DataBatch::where('periode_id', $periodeId)
                ->where('type', 1) // type = 1 untuk unit/divisi
                ->orderBy('batch', 'desc')
                ->first();
        }

        //$showRankingButton = $dataBatch && $dataBatch->status == DataBatch::STATUS_UTAMA;
        //$showCorporateButton = $dataBatch && $dataBatch->status == DataBatch::STATUS_VERIFIKASI_CORPORATE;

        $showRankingButton = false;
        $showCorporateButton = false;
        
        if ($selectedPeriode) {
            // Jika status_progress null, maka bisa ranking
            $showRankingButton = is_null($selectedPeriode->status_progress);
            // Jika status_progress = 1, maka verifikasi corporate
            $showCorporateButton = $selectedPeriode->status_progress == 1;
        }

        return view('corporate-risk.index', compact(
            'risikoMain', 
            'risikoCorporate',
            'unit', 
            'periodes',
            'peristiwaRisiko', 
            'jenisRisiko', 
            'selectedPeriode',
            'unitId',
            'showRankingButton',
            'showCorporateButton'
        ));
    }

    public function updateToCorporate(Request $request)
    {
        // Validasi request
        $request->validate([
            'selected_risks' => 'required|array',
            'selected_risks.*' => 'exists:identifikasi_risikos,id',
            'periode_id' => 'required|exists:periodes,id'
        ]);

        // Ambil risiko yang dipilih
        $selectedRisks = $request->input('selected_risks', []);
        
        if (empty($selectedRisks)) {
            return redirect()->route('corporate-risk.index')
                ->with('error', 'Tidak ada risiko yang dipilih');
        }
        
        // Ambil risiko yang akan diupdate
        $risikos = IdentifikasiRisiko::whereIn('id', $selectedRisks)->get();
        
        // Update status risiko menjadi corporate dan simpan status sebelumnya
        foreach ($risikos as $risiko) {
            $risiko->update([
                'previous_status_risiko' => $risiko->status_risiko,
                'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_CORPORATE
            ]);
        }

        return redirect()->route('corporate-risk.index', ['pid' => $request->periode_id])
            ->with('success', 'Risiko utama berhasil diubah menjadi risiko corporate');
    }

    public function rankingRisiko(Request $request)
    {
        // Validasi request
        $request->validate([
            'periode_id' => 'required|exists:periodes,id'
        ]);

        $periodeId = $request->periode_id;

        // Ambil semua risiko utama untuk periode yang dipilih
        $risikoMain = IdentifikasiRisiko::where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_MAIN)
            ->where('periode_id', $periodeId)
            ->with('riskAnalysis')
            ->get();

        // Filter risiko berdasarkan kategori dampak
        $quantitativeRisks = $risikoMain->filter(function($risk) {
            return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kuantitatif';
        });

        $qualitativeRisks = $risikoMain->filter(function($risk) {
            return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kualitatif';
        });

        // Hitung rata-rata eksposur risiko untuk risiko kuantitatif
        if ($quantitativeRisks->count() > 0) {
            $avgExposure = $quantitativeRisks->avg(function($risk) {
                return $risk->riskAnalysis->eksposur_risiko ?? 0;
            });
            
            // Update risiko kuantitatif yang nilainya di atas rata-rata
            foreach ($quantitativeRisks as $risk) {
                if (($risk->riskAnalysis->eksposur_risiko ?? 0) > $avgExposure) {
                    $risk->update([
                        'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION
                    ]);
                }
            }
        }
        
        // Update risiko kualitatif yang nilai risikonya >= 20
        foreach ($qualitativeRisks as $risk) {
            if (($risk->riskAnalysis->skala_risiko ?? 0) >= 20) {
                $risk->update([
                    'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION
                ]);
            }
        }

        // Update status DataBatch menjadi STATUS_VERIFIKASI_CORPORATE (7)
        // $dataBatch = DataBatch::where('periode_id', $periodeId)
        //     ->where('type', 1) // type = 1 untuk unit/divisi
        //     ->orderBy('batch', 'desc')
        //     ->first();

        // if ($dataBatch) {
        //     $dataBatch->update([
        //         'status' => DataBatch::STATUS_VERIFIKASI_CORPORATE
        //     ]);
        // }

        //update status_progress di periode
        Periode::where('id', $periodeId)
            ->update([
                'status_progress' => 1
            ]);

        return redirect()->route('corporate-risk.index', ['pid' => $periodeId])
            ->with('success', 'Ranking risiko berhasil dilakukan. Risiko yang memenuhi kriteria telah dijadikan risiko corporate.');
    }

    public function revertFromCorporate(Request $request)
    {
        // Validasi request
        $request->validate([
            'risk_id' => 'required|exists:identifikasi_risikos,id',
            'periode_id' => 'required|exists:periodes,id'
        ]);

        $riskId = $request->input('risk_id');
        $periodeId = $request->input('periode_id');
        
        // Ambil risiko yang akan dikembalikan
        $risiko = IdentifikasiRisiko::find($riskId);
        
        if (!$risiko) {
            return redirect()->route('corporate-risk.index', ['pid' => $periodeId])
                ->with('error', 'Risiko tidak ditemukan');
        }
        
        // Kembalikan ke status sebelumnya jika ada, jika tidak kembalikan ke STATUS_RISIKO_MAIN
        $previousStatus = $risiko->previous_status_risiko ?? IdentifikasiRisiko::STATUS_RISIKO_MAIN;
        
        $risiko->update([
            'status_risiko' => $previousStatus,
            'previous_status_risiko' => null
        ]);

        return redirect()->route('corporate-risk.index', ['pid' => $periodeId])
            ->with('success', 'Risiko berhasil dikembalikan ke status sebelumnya');
    }

    public function confirmCorporateRisks(Request $request)
    {
        // Validasi request
        $request->validate([
            'periode_id' => 'required|exists:periodes,id'
        ]);

        $periodeId = $request->periode_id;

        // Update status DataBatch menjadi STATUS_FINISH (8)
        // $dataBatch = DataBatch::where('periode_id', $periodeId)
        //     ->where('type', 1) // type = 1 untuk unit/divisi
        //     ->orderBy('batch', 'desc')
        //     ->first();

        // if ($dataBatch) {
        //     $dataBatch->update([
        //         'status' => DataBatch::STATUS_FINISH
        //     ]);
        // }

        Periode::where('id', $periodeId)
            ->update([
                'status_progress' => 2
            ]);

        // Update is_proyek menjadi 1 untuk risiko dengan status_risiko corporate
        IdentifikasiRisiko::where('periode_id', $periodeId)
            ->where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_CORPORATE)
            ->update(['is_corporate' => 1]);

        return redirect()->route('corporate-risk.index', ['pid' => $periodeId])
            ->with('success', 'Konfirmasi risiko corporate berhasil dilakukan.');
    }
}