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
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\JenisKontrolEksisting;
use App\Models\PenilaianEfektivitasKontrol;
use App\Models\SkalaProbabilitas;
use App\Models\RiskMap;
use App\Models\AreaDampak;
use App\Models\RiskLimitPeriode;
use App\Models\PerlakuanPenyebabRisikoUnit;
use App\Models\Jabatan;
use App\Models\MasterKRI;
use App\Models\KontrolEksisting;

class CorporateRiskController extends Controller
{
    // public function index(Request $request)
    // {
    //     // Ambil periode_id dari parameter URL
    //     $periodeId = $request->query('pid');
    //     $unitId = $request->query('unit_id');
        
    //     // Jika tidak ada parameter periode, gunakan periode aktif
    //     if (!$periodeId) {
    //         $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
    //         $periodeId = $activePeriode ? $activePeriode->id : null;
    //     }

    //     // Ambil data periode yang dipilih
    //     $selectedPeriode = Periode::find($periodeId);
        
    //     // Query untuk risiko utama (main)
    //     $risikoMainQuery = IdentifikasiRisiko::with([
    //         'unit',
    //         'user',
    //         'periode',
    //         'kategoriRisiko',
    //         'jenisRisiko',
    //         'peristiwaRisiko',
    //         'riskAnalysis',
    //     ])
    //     ->whereIn('status_risiko', [
    //         IdentifikasiRisiko::STATUS_RISIKO_MAIN,
    //         IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION
    //     ]);

    //     // Query untuk risiko corporate
    //     $risikoCorporateQuery = IdentifikasiRisiko::with([
    //         'unit',
    //         'user',
    //         'periode',
    //         'kategoriRisiko',
    //         'jenisRisiko',
    //         'peristiwaRisiko',
    //         'riskAnalysis',
    //     ])
    //     ->where('status_risiko', IdentifikasiRisiko::STATUS_RISIKO_CORPORATE);

    //     // Filter berdasarkan periode jika ada
    //     if ($periodeId) {
    //         $risikoMainQuery->where('periode_id', $periodeId);
    //         $risikoCorporateQuery->where('periode_id', $periodeId);
    //     }

    //     // Filter berdasarkan unit jika ada
    //     if ($unitId) {
    //         $risikoMainQuery->where('unit_id', $unitId);
    //         $risikoCorporateQuery->where('unit_id', $unitId);
    //     }

    //     // Ambil data risiko
    //     $risikoMain = $risikoMainQuery->get();
    //     $risikoCorporate = $risikoCorporateQuery->get();

    //     // Urutkan risiko berdasarkan status, skala risiko, dan eksposur risiko
    //     $risikoMain = $risikoMain->sortBy(function($risk) {
    //         // Prioritaskan risiko rekomendasi korporat
    //         $statusPriority = $risk->status_risiko == IdentifikasiRisiko::STATUS_RISIKO_CORPORATE_RECOMMENDATION ? 0 : 1;
            
    //         // Kemudian urutkan berdasarkan skala risiko (nilai lebih tinggi lebih dulu)
    //         $skalaRisiko = -1 * ($risk->riskAnalysis->skala_risiko ?? 0);
            
    //         // Jika skala risiko sama, urutkan berdasarkan eksposur risiko (nilai lebih tinggi lebih dulu)
    //         $eksposurRisiko = -1 * ($risk->riskAnalysis->eksposur_risiko ?? 0);
            
    //         return [$statusPriority, $skalaRisiko, $eksposurRisiko];
    //     })->values();

    //     // Urutkan risiko corporate berdasarkan skala risiko dan eksposur risiko
    //     $risikoCorporate = $risikoCorporate->sortBy(function($risk) {
    //         // Urutkan berdasarkan skala risiko (nilai lebih tinggi lebih dulu)
    //         $skalaRisiko = -1 * ($risk->riskAnalysis->skala_risiko ?? 0);
            
    //         // Jika skala risiko sama, urutkan berdasarkan eksposur risiko (nilai lebih tinggi lebih dulu)
    //         $eksposurRisiko = -1 * ($risk->riskAnalysis->eksposur_risiko ?? 0);
            
    //         return [$skalaRisiko, $eksposurRisiko];
    //     })->values();

    //     // Data untuk filter
    //     $unit = Unit::pluck('name', 'id');
    //     $periodes = Periode::orderBy('tahun', 'desc')->pluck('tahun', 'id');
    //     $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
    //     $jenisRisiko = JenisRisiko::pluck('title','id');

    //     // Cek status DataBatch untuk menentukan apakah tombol ranking dan jadikan risiko corporate ditampilkan
    //     $dataBatch = null;
    //     if ($periodeId) {
    //         $dataBatch = DataBatch::where('periode_id', $periodeId)
    //             ->where('type', 1) // type = 1 untuk unit/divisi
    //             ->orderBy('batch', 'desc')
    //             ->first();
    //     }

    //     //$showRankingButton = $dataBatch && $dataBatch->status == DataBatch::STATUS_UTAMA;
    //     //$showCorporateButton = $dataBatch && $dataBatch->status == DataBatch::STATUS_VERIFIKASI_CORPORATE;

    //     $showRankingButton = false;
    //     $showCorporateButton = false;
        
    //     if ($selectedPeriode) {
    //         // Jika status_progress null, maka bisa ranking
    //         $showRankingButton = is_null($selectedPeriode->status_progress);
    //         // Jika status_progress = 1, maka verifikasi corporate
    //         $showCorporateButton = $selectedPeriode->status_progress == 1;
    //     }

    //     return view('corporate-risk.index', compact(
    //         'risikoMain', 
    //         'risikoCorporate',
    //         'unit', 
    //         'periodes',
    //         'peristiwaRisiko', 
    //         'jenisRisiko', 
    //         'selectedPeriode',
    //         'unitId',
    //         'showRankingButton',
    //         'showCorporateButton'
    //     ));
    // }

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

    public function RiskPeriodeList()
    {
        $tableLegend = [
            [
              'icon' => '<span class="bx bx-show"></span>',
              'label' => 'View'
            ],
            [
              'icon' => '<span class="bx bx-list-check"></span>',
              'label' => 'Risk Register'
            ],
            [
              'icon' => '<span class="bx bx-radar"></span>',
              'label' => 'Monitoring'
            ],
            [
              'icon' => '<span class="bx bx-dock-bottom"></span>',
              'label' => 'Loss Event'
            ],
        ];
        // Ambil semua data periode
        $periodes = Periode::orderBy('tahun', 'desc')->get();

        // Ambil periode aktif jika ada
        $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
        $userUnit = auth()->user()->unit;
        $units = [];

        $dataToDisplay = collect();

        $units = Unit::where('unit_type_id', 4)->where('id', $userUnit->id)->pluck('name', 'id');
        $periodes = Periode::orderBy('tahun', 'desc')->get();

        if ($userUnit) {
            foreach ($periodes as $periode) {
                $dataToDisplay->push([
                    'unit' => $userUnit,
                    'periode' => $periode,
                ]);
            }
        }

        return view('corporate-risk.risk-period-list', compact(
          'periodes', 
          'activePeriode', 
          'tableLegend', 
          'dataToDisplay',
          'units',
        ));
    }

    public function riskPeriodeDashboard(Request $request, $period)
    {
        $user    = request()->user()->load('unit');
        $periode = Periode::find($period);

        $targetUnitId = null;
        $targetUnitId = $user->unit_id;

        $targetUnit = Unit::find($targetUnitId);

        if (!$targetUnit) {
            abort(404, 'Unit tidak ditemukan.');
        }

        $risikos = IdentifikasiRisiko::where('periode_id', $period)
            ->where('unit_id', auth()->user()->unit_id)
            ->with('riskAnalysis')
            ->get();

        $currentRiskMaps = $risikos->pluck('currentRiskMapsMonth');
        $formattedCurrentRiskMaps = [];
        foreach ($risikos as $idx => $risiko) {
            $currentValue = $risiko->currentRiskMapsMonth['inherent'];
            for ($month = 1; $month <= 12; $month++) {
                if ($nextValue = ($risiko->currentRiskMapsMonth[$month] ?? null)) {
                    $currentValue = $nextValue;
                }

                $currentValue['quarter'] = ceil($month / 3);
                $currentValue['month'] = $month;

                $formattedCurrentRiskMaps[$risiko->id][] = $currentValue;
            }
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        return view('corporate-risk.risk-period-dashboard', compact('user', 'periode', 'risikos', 'riskMaps', 'formattedCurrentRiskMaps', 'targetUnit'));
    }

    public function index(Request $request)
    {
        $unitId = $request->query('unit_id') ?? auth()->user()->unit_id;
        // Ambil periode_id dari parameter URL
        $periodeId = $request->query('pid');
        $batchNotes = null;
        // Jika tidak ada parameter periode, gunakan periode aktif
        if (!$periodeId) {
            $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
            $periodeId = $activePeriode ? $activePeriode->id : null;
        }

        // Ambil data periode yang dipilih
        $selectedPeriode = Periode::find($periodeId);
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;//not use
        //$unitId = $user->unit_id;
        $levelId = $user->level_id;
        $min_verification = 3;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        $dataBatch = DataBatch::where('unit_id', $unitId)
                      ->where('periode_id', $periodeId)
                      ->where('type', 1)
                      ->where('finish', false)
                      ->first();

        if(!$dataBatch){
            $dataBatch = DataBatch::create([
                'unit_id' => $unitId,
                'periode_id' => $periodeId,
                'type' => 1,
                'status' => DataBatch::STATUS_PROSES,
                'step_verification' => 0,
                'finish' => false
            ]);
        }
        $status = $dataBatch->status;
        // Query dasar untuk identifikasi risiko
        $risikoQuery = IdentifikasiRisiko::with([
            'unit',
            'user',
            'periode',
            'kategoriRisiko',
            'jenisRisiko',
            'peristiwaRisiko',
            'riskAnalysis',
        ])->where('unit_type_id', 4);

        // Filter berdasarkan periode jika ada
        if ($periodeId) {
            $risikoQuery->where('periode_id', $periodeId);
        }

        if ($unitId) {
            $risikoQuery->where('unit_id', $unitId);
        }

        // Ambil data risiko
        //$risiko = $risikoQuery->get();
        // Ambil data risiko dan urutkan berdasarkan skala risiko dan eksposur risiko
        $risiko = $risikoQuery
            ->join('risk_analyses', 'identifikasi_risikos.id', '=', 'risk_analyses.risiko_id')
            ->orderBy('risk_analyses.skala_risiko', 'desc')
            ->orderBy('risk_analyses.eksposur_risiko', 'desc')
            ->select('identifikasi_risikos.*')
            ->get();

        // Data untuk filter
        $unit = Unit::where('unit_type_id', 1)->pluck('name', 'id');
        $unitChild = Unit::where('parent_id', '!=', null)->pluck('name', 'id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
        $jenisRisiko = JenisRisiko::pluck('title','id');

        $avgQuantitativeExposure = null;
        //if ($status == DataBatch::STATUS_RANKING) {
            $quantitativeRisks = $risiko->filter(function($risk) {
                return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kuantitatif';
            });

            if ($quantitativeRisks->count() > 0) {
                $avgQuantitativeExposure = $quantitativeRisks->avg(function($risk) {
                    return $risk->riskAnalysis->eksposur_risiko ?? 0;
                });
            }
        //}

        $tableLegend = [
            [
              'icon' => '<span class="bx bx-show-alt"></span>',
              'label' => 'View'
            ],
            [
              'icon' => '<span class="bx bx-message-square-edit"></span>',
              'label' => 'Edit'
            ],
            [
              'icon' => '<span class="bx bx-analyse text-warning"></span>',
              'label' => 'Analisa'
            ],
            [
              'icon' => '<span class="bx bx-task text-primary"></span>',
              'label' => 'Perencanaan'
            ],
            [
              'icon' => '<span class="bx bx-trash text-danger"></span>',
              'label' => 'Hapus'
            ],
            [
              'icon' => '<span class="badge bg-primary">!</span>',
              'label' => 'Rekomendasi Risiko'
            ],
        ];

        return view('corporate-risk.index', compact(
            'risiko',
            'unit',
            'peristiwaRisiko',
            'status',
            'selectedPeriode',
            'jenisRisiko',
            'levelId',
            'avgQuantitativeExposure',
            'unitId',
            'tableLegend',
        ));
    }

    public function create(Request $request)
    {
        $periodeId = $request->query('pid') ?? Periode::where('status', Periode::STATUS_ACTIVE)->value('id');
        $selectedPeriode = Periode::find($periodeId);

        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        $masterKris = MasterKRI::get();
        $kontrolEksistings = KontrolEksisting::get();

        $units = Unit::where('unit_type_id', 1)->orderBy('name')->get();

        // Ambil Risiko Divisi (unit_type_id = 1) yang sudah final untuk dipilih
        $divisiRisks = IdentifikasiRisiko::where('unit_type_id', 1)
            ->where('status', IdentifikasiRisiko::STATUS_PUBLISHED)
            ->with(['unit', 'kategoriRisiko', 'riskAnalysis'])
            ->get();

        return view('corporate-risk.create', compact(
            'kategoriRisiko', 'jenisKontrolEksistings', 
            'penilaianEfektifitasKontrols', 'jenisRisiko', 'selectedPeriode', 
            'divisiRisks','units', // Kirim daftar unit/divisi ke view
            'masterKris', // Kirim master KRI
            'kontrolEksistings' // Kirim kontrol eksisting
        ));
    }

    public function getDivisionRisks(Unit $unit)
    {
        $divisiRisks = IdentifikasiRisiko::where('unit_id', $unit->id)
            ->where('unit_type_id', 1)
            ->where('status', IdentifikasiRisiko::STATUS_PUBLISHED)
            ->with(['unit', 'kategoriRisiko', 'riskAnalysis'])
            ->get();
            
        // Kita akan merender HTML dari sisi server agar lebih mudah di client-side
        return response()->json([
            'html' => view('corporate-risk._ajax_risk_options', compact('divisiRisks'))->render()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            'key_risk_indicator' => 'nullable|array',
            'key_risk_indicator.*' => 'nullable|string',
            'satuan_kri.*' => 'nullable|string',
            'batas_aman.*' => 'nullable|string',
            'batas_waspada.*' => 'nullable|string',
            'batas_bahaya.*' => 'nullable|string',
            'kontrol_eksisting' => 'required|array',
            'kontrol_eksisting.*' => 'required|string',
            'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'divisi_risk_ids' => 'nullable|array',
            'divisi_risk_ids.*' => 'exists:identifikasi_risikos,id'
        ]);

        try {
            $waktuMulai = $request->perkiraan_waktu_mulai_terpapar_risiko ? Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_mulai_terpapar_risiko)->format('Y-m-d') : null;
            $waktuSelesai = $request->perkiraan_waktu_selesai_terpapar_risiko ? Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_selesai_terpapar_risiko)->format('Y-m-d') : null;

            $identifikasiRisiko = new IdentifikasiRisiko();
            $identifikasiRisiko->fill($validated);
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
            $identifikasiRisiko->user_id = auth()->id();
            $identifikasiRisiko->unit_type_id = 4; // KORPORAT
            $identifikasiRisiko->unit_id = auth()->user()->unit_id; 
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';

            $jenisRisiko = JenisRisiko::find($request->jenis_risiko_id);
            if ($jenisRisiko) {
                $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            }

            $identifikasiRisiko->save();

            // Simpan relasi
            if ($request->has('penyebab_risiko')) {
                foreach ($request->penyebab_risiko as $penyebab) {
                    if (!empty($penyebab)) $identifikasiRisiko->penyebabRisiko()->create(['penyebab_risiko' => $penyebab]);
                }
            }

            if ($request->has('key_risk_indicator')) {
                for ($i = 0; $i < count($request->key_risk_indicator); $i++) {
                    if (!empty($request->key_risk_indicator[$i])) {
                        $identifikasiRisiko->kris()->create([
                            'kri_id' => 0, // Karena tidak menggunakan master_kri_id lagi
                            'risiko_id' => $identifikasiRisiko->id,
                            'kri' => $request->key_risk_indicator[$i],
                            'satuan_kri' => $request->satuan_kri[$i] ?? null,
                            'batas_aman' => $request->batas_aman[$i] ?? null,
                            'batas_waspada' => $request->batas_waspada[$i] ?? null,
                            'batas_bahaya' => $request->batas_bahaya[$i] ?? null,
                        ]);
                    }
                }
            }

            if ($request->has('kontrol_eksisting')) {
                foreach ($request->kontrol_eksisting as $kontrol) {
                    if (!empty($kontrol)) $identifikasiRisiko->kontrolEksistings()->create(['kontrol_eksisting' => $kontrol]);
                }
            }
            
            if ($request->has('divisi_risk_ids')) {
                $identifikasiRisiko->divisiRisks()->attach($request->divisi_risk_ids);
            }

            $identifikasiRisiko->riskAnalysis()->create([]);
            $identifikasiRisiko->rencanaPerlakuanRisiko()->create([]);

            $action = $request->input('action', 'save');
            if ($action === 'savenext') {
                return response()->json([
                    'message' => 'Data risiko korporat berhasil disimpan',
                    'redirect' => route('corporate-risk.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                return response()->json([
                    'message' => 'Data risiko korporat berhasil disimpan',
                    'redirect' => route('corporate-risk.index', ['pid' => $request->periode_id])
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $identifikasiRisiko = IdentifikasiRisiko::with([
            'kontrolEksistings', 
            'penyebabRisiko', 
            'kris', 
            'divisiRisks' // Eager load relasi divisi risks
        ])->findOrFail($id);

        $selectedPeriode = Periode::find($identifikasiRisiko->periode_id);
    
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        // --- PERBAIKAN: Tambahkan variabel yang hilang ---
        $masterKris = MasterKRI::get();
        $kontrolEksistings = KontrolEksisting::get();
        // --- END PERBAIKAN ---

        // Ambil daftar Divisi untuk filter modal
        $units = Unit::where('unit_type_id', 1)->orderBy('name')->get();

        // Ambil semua risiko divisi untuk modal (akan difilter oleh AJAX)
        $divisiRisks = collect();

        return view('corporate-risk.edit', compact(
            'identifikasiRisiko', 'kategoriRisiko', 'jenisKontrolEksistings', 
            'penilaianEfektifitasKontrols', 'jenisRisiko', 'selectedPeriode', 
            'divisiRisks', 'units', 'masterKris', 'kontrolEksistings'
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            'key_risk_indicator' => 'nullable|array',
            'key_risk_indicator.*' => 'nullable|string',
            'kontrol_eksisting' => 'required|array',
            'kontrol_eksisting.*' => 'required|string',
            'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'divisi_risk_ids' => 'nullable|array',
            'divisi_risk_ids.*' => 'exists:identifikasi_risikos,id'
        ]);

        try {
            $identifikasiRisiko = IdentifikasiRisiko::findOrFail($id);
            $waktuMulai = $request->perkiraan_waktu_mulai_terpapar_risiko ? Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_mulai_terpapar_risiko)->format('Y-m-d') : null;
            $waktuSelesai = $request->perkiraan_waktu_selesai_terpapar_risiko ? Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_selesai_terpapar_risiko)->format('Y-m-d') : null;
            
            $identifikasiRisiko->fill($validated);
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            
            $jenisRisiko = JenisRisiko::find($request->jenis_risiko_id);
            if ($jenisRisiko) {
                $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            }

            $identifikasiRisiko->save();

            // Hapus dan buat ulang relasi
            $identifikasiRisiko->penyebabRisiko()->delete();
            if ($request->has('penyebab_risiko')) {
                foreach ($request->penyebab_risiko as $penyebab) {
                    if (!empty($penyebab)) $identifikasiRisiko->penyebabRisiko()->create(['penyebab_risiko' => $penyebab]);
                }
            }
            
            $identifikasiRisiko->kris()->delete();
            if ($request->has('key_risk_indicator')) {
                for ($i = 0; $i < count($request->key_risk_indicator); $i++) {
                    if (!empty($request->key_risk_indicator[$i])) {
                        $identifikasiRisiko->kris()->create([
                            'kri' => $request->key_risk_indicator[$i],
                            'satuan_kri' => $request->satuan_kri[$i] ?? null,
                            'batas_aman' => $request->batas_aman[$i] ?? null,
                            'batas_waspada' => $request->batas_waspada[$i] ?? null,
                            'batas_bahaya' => $request->batas_bahaya[$i] ?? null,
                        ]);
                    }
                }
            }
            
            $identifikasiRisiko->kontrolEksistings()->delete();
            if ($request->has('kontrol_eksisting')) {
                foreach ($request->kontrol_eksisting as $kontrol) {
                    if (!empty($kontrol)) $identifikasiRisiko->kontrolEksistings()->create(['kontrol_eksisting' => $kontrol]);
                }
            }
            
            $identifikasiRisiko->divisiRisks()->sync($request->divisi_risk_ids ?? []);
            
            $action = $request->input('action', 'save');
            if ($action === 'savenext') {
                return response()->json([
                    'message' => 'Data risiko korporat berhasil diperbarui',
                    'redirect' => route('corporate-risk.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                return response()->json([
                    'message' => 'Data risiko korporat berhasil diperbarui',
                    'redirect' => route('corporate-risk.index', ['pid' => $request->periode_id])
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function analisa(Request $request, $riskRegisterId)
    {
        $identifikasiRisiko = IdentifikasiRisiko::with([
            'penyebabRisiko',
            'kris',
            'riskAnalysis',
            'peristiwaRisiko',
            'unit',
            'periode'
        ])->findOrFail($riskRegisterId);

        $user = $request->user();

        // Cek akses
        if (!(Gate::check('risk_register_edit') || $identifikasiRisiko->user_id == $user->id)) {
            abort(403);
        }

        $unit = $identifikasiRisiko->unit;
        $periode = $identifikasiRisiko->periode;

        // Ambil data analisa jika sudah ada
        $analisa = $identifikasiRisiko->riskAnalysis;
        if (!$analisa) {
            // Jika belum ada, buat baru
            $analisa = $identifikasiRisiko->riskAnalysis()->create([]);
        }

        // Ambil data skala probabilitas
        $skalaProbabilitas = SkalaProbabilitas::umum()->orderBy('min', 'desc')->get();

        // Ambil data risk map
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        // Ambil data area dampak
        $areas = AreaDampak::with('details')->get();
        $groupedAreas = $areas->groupBy('risk_category');

        $risk_tolerance = 0;
        $risk_limit = 0;

        // $strategiRisiko = StrategiRisiko::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
        // if ($strategiRisiko) {
        //     $totalAnggaranUnit = $strategiRisiko->total_anggaran_unit;
        //     $riskLimitPercentage = config('risk_limit.percentage');
        //     $risk_limit = $totalAnggaranUnit * $riskLimitPercentage / 100;

        //     $totalOtherIdentifikasiRisiko = IdentifikasiRisiko::where('unit_id', $unit->id)
        //         ->where('periode_id', $periode->id)
        //         ->count();

        //     if ($totalOtherIdentifikasiRisiko > 0) {
        //         $risk_limit = $risk_limit / $totalOtherIdentifikasiRisiko;
        //     }
        // }

        $riskLimitPeriode = RisklimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
        if ($riskLimitPeriode) {
            $risk_limit = $riskLimitPeriode->risk_limit;
            $risk_tolerance = $riskLimitPeriode->risk_limit;
            // $totalOtherIdentifikasiRisiko = IdentifikasiRisiko::where('unit_id', $unit->id)
            //     ->where('periode_id', $periode->id)
            //     ->whereHas('riskAnalysis', function($query) {
            //         $query->where('kategori_dampak', 'Kuantitatif');
            //     })
            //     ->count();

            // if ($totalOtherIdentifikasiRisiko > 0) {
            //     $risk_limit = $risk_limit / $totalOtherIdentifikasiRisiko;
            // }
        }

        $autoCalculate = true; // Flag untuk menentukan apakah perhitungan harus dilakukan secara otomatis

        return view('corporate-risk.analisa', compact(
            'identifikasiRisiko',
            'unit',
            'periode',
            'analisa',
            'skalaProbabilitas',
            'riskMaps',
            'areas',
            'groupedAreas',
            'risk_tolerance',
            'risk_limit',
            'autoCalculate'
        ));
    }

    
    public function doAnalisa(Request $request, $riskRegisterId)
    {
        $identifikasiRisiko = IdentifikasiRisiko::findOrFail($riskRegisterId);

        $user = $request->user();

        // Cek akses
        // if (!(Gate::check('risk_register_edit') || $identifikasiRisiko->user_id == $user->id)) {
        //     abort(403);
        // }

        $request->merge([
            'nilai_dampak_residual'              => $this->cleanRupiah($request->nilai_dampak_residual_q4),
            'nilai_probabilitas_residual'        => $request->nilai_probabilitas_residual_q4,
            'skala_dampak_residual'              => $request->skala_dampak_residual_q4,
            'skala_dampak_residual_hidden'       => $request->skala_dampak_residual_q4,
            'deskripsi_dampak_residual'          => $request->deskripsi_dampak_residual_q4,
            'asumsi_perhitungan_dampak_residual' => $request->asumsi_perhitungan_dampak_residual_q4,

            'nilai_dampak_residual_q1' => $this->cleanRupiah($request->nilai_dampak_residual_q1),
            'nilai_dampak_residual_q2' => $this->cleanRupiah($request->nilai_dampak_residual_q2),
            'nilai_dampak_residual_q3' => $this->cleanRupiah($request->nilai_dampak_residual_q3),
            'nilai_dampak_residual_q4' => $this->cleanRupiah($request->nilai_dampak_residual_q4),
            'nilai_dampak' => $this->cleanRupiah($request->nilai_dampak),
        ]);

        $validationRules = [
            'kategori_dampak'                    => 'required|string|in:Kualitatif,Kuantitatif',
            'area_dampak'                        => 'nullable|string',
            //'risk_limit'                         => 'required|numeric',
            'nilai_dampak'                       => 'required',
            'nilai_probabilitas'                 => 'required|numeric',
            'skala_dampak'                       => 'required_if:kategori_dampak,Kualitatif|integer',
            'skala_dampak_hidden'                => 'required_if:kategori_dampak,Kualitatif|integer',
            'deskripsi_dampak'                   => 'nullable|string',
            'asumsi_perhitungan_dampak'          => 'nullable|string',
        ];

        for ($i = 1; $i <= 4; $i++) {
            $validationRules['nilai_dampak_residual_q' . $i] = 'nullable';
            $validationRules['nilai_probabilitas_residual_q' . $i] = 'nullable|numeric';
            $validationRules['skala_dampak_residual_q' . $i] = 'nullable|integer';
            $validationRules['skala_dampak_residual_hidden_q' . $i] = 'nullable|integer';
            $validationRules['deskripsi_dampak_residual_q' . $i] = 'nullable|string';
        }

        // Validasi input
        $validated = $request->validate($validationRules);
        $unit = $identifikasiRisiko->unit;
        $periode = $identifikasiRisiko->periode;
        $riskLimitPeriode = RisklimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
        if ($request->kategori_dampak == 'Kuantitatif') {
            
            $risk_limit = 0;

            
            if ($riskLimitPeriode) {
                $risk_limit = $riskLimitPeriode->risk_limit;
                $risk_tolerance = $riskLimitPeriode->risk_limit;
                // $totalOtherIdentifikasiRisiko = IdentifikasiRisiko::where('unit_id', $unit->id)
                //     ->where('periode_id', $periode->id)
                //     ->whereHas('riskAnalysis', function($query) {
                //         $query->where('kategori_dampak', 'Kuantitatif');
                //     })
                //     ->count();

                // if ($totalOtherIdentifikasiRisiko > 0) {
                //     $risk_limit = $risk_limit / $totalOtherIdentifikasiRisiko;
                // }
            }

            $toMerge = [
                'skala_dampak' => $this->calculateSkalaDampak($request->nilai_dampak * 100 / $risk_limit),
            ];
            //echo "risk limit = " . $risk_limit . "\n";
            for ($i = 1; $i <= 4; $i++) {
                //echo "nilai_dampak_residual_q" . $i . " = " . $request->{'nilai_dampak_residual_q' . $i} . "\n";
                $calculateSkala = $this->calculateSkalaDampak($request->{'nilai_dampak_residual_q' . $i} * 100 / $risk_limit);
                //echo "skala dampak residual q" . $i . " = " . $calculateSkala . "\n";
                $toMerge['skala_dampak_residual_q' . $i] = $calculateSkala;
            }

            $request->merge($toMerge);
        }

        // validate q4 < q3 < q2 < q1 < inherent
        $lastValues = [
            'nilai_dampak' => $request->nilai_dampak,
            'nilai_probabilitas' => $request->nilai_probabilitas,
            'skala_dampak' => $request->skala_dampak,
        ];
        for ($i = 0; $i < 4; $i++) {
            if ($request->{'nilai_dampak_residual_q' . ($i + 1)} > $lastValues['nilai_dampak']) {
                return response()->json([
                    'message' => 'Nilai dampak residual q' . ($i + 1) . ' tidak boleh lebih besar dari ' . ($i ? 'q' . $i : 'inherent'),
                ], 422);
            }
            $lastValues['nilai_dampak'] = $request->{'nilai_dampak_residual_q' . ($i + 1)};

            if ($request->{'nilai_probabilitas_residual_q' . ($i + 1)} > $lastValues['nilai_probabilitas']) {
                return response()->json([
                    'message' => 'Nilai probabilitas residual q' . ($i + 1) . ' tidak boleh lebih besar dari ' . ($i ? 'q' . $i : 'inherent'),
                ], 422);
            }
            $lastValues['nilai_probabilitas'] = $request->{'nilai_probabilitas_residual_q' . ($i + 1)};

            if ($request->{'skala_dampak_residual_q' . ($i + 1)} > $lastValues['skala_dampak']) {
                return response()->json([
                    'message' => 'Skala dampak residual q' . ($i + 1) . ' tidak boleh lebih besar dari ' . ($i ? 'q' . $i : 'inherent'),
                ], 422);
            }
            $lastValues['skala_dampak'] = $request->{'skala_dampak_residual_q' . ($i + 1)};
        }

        $nilai_dampak = $this->cleanRupiah($request->nilai_dampak);
        $nilai_dampak_residual = $this->cleanRupiah($request->nilai_dampak_residual);

        // Ambil atau buat data analisa
        $analisa = $identifikasiRisiko->riskAnalysis;
        if (!$analisa) {
            $analisa = $identifikasiRisiko->riskAnalysis()->create([]);
        }

        // Ambil data risk map
        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        // Update data analisa
        //$analisa->update($validated);
        $toUpdate = [
            'skala_probabilitas_id' => null, // calculated [Done]
            'area_dampak' => $request->area_dampak ?? null,
            'kategori_dampak' => $request->kategori_dampak,
            'deskripsi_dampak' => $request->deskripsi_dampak,
            'deskripsi_dampak_residual' => $request->deskripsi_dampak_residual,
            'asumsi_perhitungan_dampak' => $request->asumsi_perhitungan_dampak,
            'asumsi_perhitungan_dampak_residual' => $request->asumsi_perhitungan_dampak_residual,
            'nilai_dampak' => null, // calculated [Done]
            'nilai_probabilitas' => $request->nilai_probabilitas,
            'skala_probabilitas_residual_id' => null, // calculated [Done]
            'nilai_dampak_residual' => null, // calculated [Done]
            'nilai_probabilitas_residual' => $request->nilai_probabilitas_residual,
        ];

        for ($i = 1; $i <= 4; $i++) {
            $toUpdate['deskripsi_dampak_residual_q' . $i] = $request->{'deskripsi_dampak_residual_q' . $i};
            $toUpdate['asumsi_perhitungan_dampak_residual_q' . $i] = $request->{'asumsi_perhitungan_dampak_residual_q' . $i};
            $toUpdate['skala_probabilitas_residual_id_q' . $i] = null;
            $toUpdate['nilai_dampak_residual_q' . $i] = null;
            $toUpdate['nilai_probabilitas_residual_q' . $i] = $request->{'nilai_probabilitas_residual_q' . $i};
        }

        $analisa->update($toUpdate);

        $toUpdate = [
            'nilai_probabilitas' => $request->nilai_probabilitas,
            'skala_dampak' => $request->skala_dampak_hidden,
            'nilai_dampak' => $nilai_dampak,
            //'risk_limit' => $request->risk_limit,
            //'risk_tolerance' => null, // calculated [Done]
        ];

        $tingkatSkalaProbabilitas = SkalaProbabilitas::getSkalaByValue($request->nilai_probabilitas);
        $toUpdate['skala_probabilitas_id'] = $tingkatSkalaProbabilitas->id;

        $riskMap = $riskMaps[$toUpdate['skala_dampak'] . '-' . $tingkatSkalaProbabilitas->tingkat] ?? null;
        if (!$riskMap) {
            return response()->json([
                'message' => 'Tidak ada data risk map untuk skala dampak dan probabilitas yang dipilih',
            ], 422);
        }
        $toUpdate['skala_risiko'] = $riskMap->nilai_risiko;
        $toUpdate['level_risiko'] = $riskMap->level_risiko;

        //$toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * $toUpdate['nilai_probabilitas'];
        if ($request->kategori_dampak == 'Kualitatif') {
            // Untuk kualitatif, gunakan skala dampak * skala probabilitas
            // Rumus: skalaDampak * (1/100) * (nilaiProbabilitas / 100) * riskTolerance
            $toUpdate['eksposur_risiko'] = floatval($toUpdate['skala_dampak']) * (1/100) * (floatval($toUpdate['nilai_probabilitas']) / 100) * ($riskLimitPeriode->risk_limit ?: 0);
        } else {
            // Untuk kuantitatif, gunakan nilai dampak * probabilitas
            // Rumus: (nilaiDampak * nilaiProbabilitas) / 100
            $toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * $toUpdate['nilai_probabilitas'];
        }

        for ($i = 1; $i <= 4; $i++) {
            $nilaiProbResidual = $request->{'nilai_probabilitas_residual_q' . $i};
            
            // Lanjutkan perhitungan hanya jika ada nilai probabilitas di kuartal ini
            if (!is_null($nilaiProbResidual) && $nilaiProbResidual !== '') {
                if ($request->kategori_dampak == 'Kualitatif') {
                    $skalaDampakResidual = $request->{'skala_dampak_residual_q' . $i};
                    $toUpdate['eksposur_risiko_residual_q' . $i] = floatval($skalaDampakResidual) * (1/100) * (floatval($nilaiProbResidual) / 100) * ($riskLimitPeriode->risk_limit ?: 0);
                } else { // Kategori Kuantitatif
                    $nilaiDampakResidual = $request->{'nilai_dampak_residual_q' . $i};
                    $toUpdate['eksposur_risiko_residual_q' . $i] = $nilaiDampakResidual * ($nilaiProbResidual / 100);
                }
            } else {
                // Jika tidak ada nilai probabilitas, set eksposur ke null
                $toUpdate['eksposur_risiko_residual_q' . $i] = null;
            }
        }

        $tingkatSkalaProbabilitasResiduals = [];
        for ($i = 1; $i <= 4; $i++) {
            $tingkatSkalaProbabilitasResiduals[$i] = SkalaProbabilitas::getSkalaByValue($request->{'nilai_probabilitas_residual_q' . $i});
            $toUpdate['skala_probabilitas_residual_id_q' . $i] = optional($tingkatSkalaProbabilitasResiduals[$i])->id;
            $toUpdate['nilai_probabilitas_residual_q' . $i] = $request->{'nilai_probabilitas_residual_q' . $i};
            $toUpdate['skala_dampak_residual_q' . $i] = $request->{'skala_dampak_residual_q' . $i};
            $toUpdate['nilai_dampak_residual_q' . $i] = $request->{'nilai_dampak_residual_q' . $i};

            if ($toUpdate['skala_dampak_residual_q' . $i] && $tingkatSkalaProbabilitasResiduals[$i]) {
                $riskMapResidual = $riskMaps[$toUpdate['skala_dampak_residual_q' . $i] . '-' . $tingkatSkalaProbabilitasResiduals[$i]->tingkat] ?? null;
                if (!$riskMapResidual) {
                    return response()->json([
                        'message' => 'Tidak ada data risk map untuk skala dampak residual dan probabilitas residual yang dipilih',
                    ], 422);
                }
            }

            if ($riskMapResidual) {
                $toUpdate['skala_risiko_residual_q' . $i] = $riskMapResidual->nilai_risiko;
                $toUpdate['level_risiko_residual_q' . $i] = $riskMapResidual->level_risiko;
            } else {
                $toUpdate['skala_risiko_residual_q' . $i] = null;
                $toUpdate['level_risiko_residual_q' . $i] = null;
            }
        }

        $toUpdate['skala_probabilitas_residual_id'] = $toUpdate['skala_probabilitas_residual_id_q4'];
        $toUpdate['nilai_probabilitas_residual'] = $toUpdate['nilai_probabilitas_residual_q4'];
        $toUpdate['skala_dampak_residual'] = $toUpdate['skala_dampak_residual_q4'];
        $toUpdate['nilai_dampak_residual'] = $toUpdate['nilai_dampak_residual_q4'];
        $toUpdate['skala_risiko_residual'] = $toUpdate['skala_risiko_residual_q4'];
        $toUpdate['level_risiko_residual'] = $toUpdate['level_risiko_residual_q4'];

        $analisa->update($toUpdate);

        $identifikasiRisiko->update([
            'skala_risiko' => $toUpdate['skala_risiko'],
            'level_risiko' => $toUpdate['level_risiko'],
        ]);

        return response()->json([
            'message' => 'Analisa risiko berhasil disimpan',
            'redirect' => route('corporate-risk.index', ['pid' => $identifikasiRisiko->periode_id])
        ]);
    }

    public function perencanaan(Request $request, $riskRegisterId)
    {
        $identifikasiRisiko = IdentifikasiRisiko::with([
            'penyebabRisiko.perlakuanPenyebabRisiko',
            'kris',
            'riskAnalysis',
            'peristiwaRisiko',
            'unit',
            'periode'
        ])->findOrFail($riskRegisterId);

        $analisa = $identifikasiRisiko->riskAnalysis;

        return view('corporate-risk.perencanaan', compact('identifikasiRisiko', 'analisa'));
    }

    public function doPerencanaan(Request $request, $riskRegisterId)
    {
        $validated = $request->validate([
            'penyebab_risiko_id' => 'required|exists:penyebab_risikos,id',
            'rencana_perlakuan_risiko' => 'required|string',
            'output_perlakuan_risiko' => 'required|string',
            'biaya_perlakuan_risiko' => 'required|numeric|min:0',
            'pic' => 'required',
            'timeline_mulai_perlakuan_risiko' => 'required',
            'timeline_selesai_perlakuan_risiko' => 'required',
            'opsi_perlakuan_risiko' => 'required|exists:opsi_perlakuan_risikos,id',
            // 'jenis_rencana_perlakuan_risiko' => 'required|exists:jenis_rencana_perlakuan_risikos,id',
        ]);

        $startDate = $validated['timeline_mulai_perlakuan_risiko'] ?? null;
        $endDate = $validated['timeline_selesai_perlakuan_risiko']?? null;

        if (!$endDate) {
            $endDate = $startDate;
        }

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Format tanggal tidak valid. Pastikan rentang tanggal dipilih dengan benar.',
            ], 422);
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            return response()->json([
                'message' => 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.',
            ], 422);
        }

        $jabatan = Jabatan::find($validated['pic']);
        $jabatan_name = "-";
        if($jabatan){
            $jabatan_name = $jabatan->name;
        }

        $perlakuan = PerlakuanPenyebabRisikoUnit::create([
            'penyebab_risiko_id' => $validated['penyebab_risiko_id'],
            'rencana_perlakuan_risiko' => $validated['rencana_perlakuan_risiko'],
            'output_perlakuan_risiko' => $validated['output_perlakuan_risiko'],
            'biaya_perlakuan_risiko' => $validated['biaya_perlakuan_risiko'],
            'pic' => $jabatan_name,
            'pic_jabatan_id' => $validated['pic'],
            'timeline_perlakuan_risiko_start' => $startDate,
            'timeline_perlakuan_risiko_end' => $endDate,
            'opsi_perlakuan_risiko' => $validated['opsi_perlakuan_risiko'],
            // 'jenis_rencana_perlakuan_risiko' => $validated['jenis_rencana_perlakuan_risiko'],
        ]);

        return response()->json([
            'message' => 'Rencana untuk penyebab risiko berhasil ditambahkan!',
        ]);
    }

    public function hapusRencanaPerlakuan($riskRegisterId, $id) {
        try {
            $perlakuan = PerlakuanPenyebabRisikoUnit::findOrFail($id);
            $perlakuan->delete();

            return response()->json([
                'message' => 'Rencana perlakuan berhasil dihapus.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus data. '.$e->getMessage()
            ], 500);
        }
    }

    public function editRencanaPerlakuan($riskRegisterId, $id)
    {
        $perlakuan = PerlakuanPenyebabRisikoUnit::with('penyebabRisiko')->findOrFail($id);

        return response()->json([
            'id' => $perlakuan->id,
            'penyebab_risiko_id' => $perlakuan->penyebab_risiko_id,
            'penyebab_risiko' => $perlakuan->penyebabRisiko->penyebab_risiko ?? null, // Dapatkan nama penyebab risiko
            'rencana_perlakuan_risiko' => $perlakuan->rencana_perlakuan_risiko,
            'output_perlakuan_risiko' => $perlakuan->output_perlakuan_risiko,
            'opsi_perlakuan_risiko' => $perlakuan->opsi_perlakuan_risiko,
            // 'jenis_rencana_perlakuan_risiko' => $perlakuan->jenis_rencana_perlakuan_risiko,
            'biaya_perlakuan_risiko' => $perlakuan->biaya_perlakuan_risiko,
            'pic' => $perlakuan->pic,
            'pic_jabatan_id' => $perlakuan->pic_jabatan_id,
            'timeline_perlakuan_risiko_start' => $perlakuan->timeline_perlakuan_risiko_start ? $perlakuan->timeline_perlakuan_risiko_start->format('d/m/Y') : null,
            'timeline_perlakuan_risiko_end' => $perlakuan->timeline_perlakuan_risiko_end ? $perlakuan->timeline_perlakuan_risiko_end->format('d/m/Y') : null,
        ]);
    }

    public function updateRencanaPerlakuan(Request $request, $riskRegisterId, $id)
    {
        $validated = $request->validate([
            'xrencana_perlakuan_risiko' => 'required',
            'xoutput_perlakuan_risiko' => 'required',
            'xopsi_perlakuan_risiko' => 'required',
            // 'xjenis_rencana_perlakuan_risiko' => 'required',
            'xbiaya_perlakuan_risiko' => 'required|numeric',
            'xpic' => 'required',
            'xtimeline_mulai_perlakuan_risiko' => 'required',
            'xtimeline_selesai_perlakuan_risiko' => 'required',
        ]);

        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $validated['xtimeline_mulai_perlakuan_risiko']);
            $endDate = Carbon::createFromFormat('d/m/Y', $validated['xtimeline_selesai_perlakuan_risiko']);

            if ($startDate > $endDate) {
                return response()->json([
                    'message' => 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.'
                ], 422);
            }

            $jabatan = Jabatan::find($validated['xpic']);
            $jabatan_name = "-";
            if($jabatan){
                $jabatan_name = $jabatan->name;
            }

            $data = [
                'rencana_perlakuan_risiko' => $validated['xrencana_perlakuan_risiko'],
                'output_perlakuan_risiko' => $validated['xoutput_perlakuan_risiko'],
                'opsi_perlakuan_risiko' => $validated['xopsi_perlakuan_risiko'],
                // 'jenis_rencana_perlakuan_risiko' => $validated['xjenis_rencana_perlakuan_risiko'],
                'biaya_perlakuan_risiko' => $validated['xbiaya_perlakuan_risiko'],
                'pic' => $jabatan_name,
                'pic_jabatan_id' => $validated['xpic'],
                'timeline_perlakuan_risiko_start' => $startDate->format('Y-m-d'),
                'timeline_perlakuan_risiko_end' => $endDate->format('Y-m-d'),
            ];

            $perlakuan = PerlakuanPenyebabRisikoUnit::findOrFail($id);

            $perlakuan->update($data);

            return response()->json([
                'message' => 'Rencana perlakuan berhasil diperbarui.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function cleanRupiah($value) {
        return (float) str_replace(['Rp', '.', ','], ['', '', ''], $value);
    }

    protected function calculateSkalaDampak($percentage) {
        if ($percentage <= 20) return 1; // Low
        if ($percentage > 20 && $percentage <= 40) return 2; // Low To Moderate
        if ($percentage > 40 && $percentage <= 60) return 3; // Moderate
        if ($percentage > 60 && $percentage <= 80) return 4; // Moderate To High
        return 5; // High
    }

    public function destroy($id)
    {
        try {
            // Cari data identifikasi risiko
            $identifikasiRisiko = IdentifikasiRisiko::findOrFail($id);

            // Cek apakah user memiliki akses untuk menghapus
            if (!Gate::check('risk_register_delete') && $identifikasiRisiko->user_id != auth()->id()) {
                return redirect()->route('corporate-risk.index')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
            }

            // Hapus data terkait
            // Hapus kontrol eksisting
            // $identifikasiRisiko->kontrolEksistings()->delete();

            // // Hapus penyebab risiko
            // $identifikasiRisiko->penyebabRisiko()->delete();

            // // Hapus KRI
            // $identifikasiRisiko->kris()->delete();

            // // Hapus analisis risiko jika ada
            // if ($identifikasiRisiko->riskAnalysis) {
            //     $identifikasiRisiko->riskAnalysis->delete();
            // }

            // Hapus data identifikasi risiko
            $identifikasiRisiko->delete();

            return redirect()->route('corporate-risk.index')->with('success', 'Data risiko berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('corporate-risk.index')->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}