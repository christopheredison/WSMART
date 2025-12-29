<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\IdentifikasiRisiko;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\User;
use App\Models\Periode;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\PeristiwaRisiko;
use App\Models\Tck;
use App\Models\JenisKontrolEksisting;
use App\Models\PenilaianEfektivitasKontrol;
use App\Models\Draft;
use App\Models\MasterKRI;
use App\Models\KontrolEksisting;
use App\Models\AreaDampak;
use App\Models\PenyebabRisiko;
use App\Models\KRI;
use App\Models\PerlakuanPenyebabRisikoUnit;
use App\Models\SkalaProbabilitas;
use App\Models\RiskMap;
use App\Models\StrategiRisiko;
use App\Models\RiskLimitPeriode;
use App\Models\Jabatan;
use App\Models\DataBatch;
use App\Models\RiskNote;
use App\Models\ApprovalLog;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use App\Models\DataBatchNotes;
use App\Models\PerlakuanDampakRisikoUnit;

class RiskRegisterApController extends Controller
{
    public function index(Request $request)
    {
        //$unitId = auth()->user()->unit_id;
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

        // // Cari ApprovalFlow untuk unit ini
        // $approvalFlow = ApprovalFlow::where('unit_id', $unitId)
        //     ->whereNull('project_id')
        //     ->first();
        // $min_verification = 3;
        // if ($approvalFlow) {
        //     $min_verification = $approvalFlow->min_verification;
        // }

        $min_verification = 3;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $selectedUnit = Unit::find($unitId);

        $dataBatch = DataBatch::where('unit_id', $unitId)
                      ->where('periode_id', $periodeId)
                      ->where('type', 1)
                      ->where('finish', false)
                      ->orderBy('batch', 'desc')
                      ->first();

        if(!$dataBatch){
            $lastBatch = DataBatch::where('unit_id', $unitId)->where('periode_id', $periodeId)->where('type', 1)->orderBy('batch', 'desc')->first();
            $dataBatch = DataBatch::create([
                'unit_id' => $unitId,
                'periode_id' => $periodeId,
                'type' => 1,
                'status' => DataBatch::STATUS_PROSES,
                'batch' => $lastBatch ? $lastBatch->batch + 1 : 1,
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
            //'tck',
            'riskAnalysis',
        ])->where('unit_type_id', 2);

        // Filter berdasarkan periode jika ada
        if ($periodeId) {
            $risikoQuery->where('periode_id', $periodeId);
        }

        if ($unitId) {
            $risikoQuery->where('unit_id', $unitId);
        }

        // Ambil data risiko
        // $risiko = $risikoQuery->get();
        // Ambil data risiko dan urutkan berdasarkan skala risiko dan eksposur risiko
        $risiko = $risikoQuery
            ->join('risk_analyses', 'identifikasi_risikos.id', '=', 'risk_analyses.risiko_id')
            ->orderBy('risk_analyses.skala_risiko', 'desc')
            ->orderBy('risk_analyses.eksposur_risiko', 'desc')
            ->select('identifikasi_risikos.*')
            ->get();

        // Data untuk filter
        $unit = Unit::where('unit_type_id', 2)->pluck('name', 'id');
        $unitChild = Unit::where('parent_id', '!=', null)->pluck('name', 'id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
        $jenisRisiko = JenisRisiko::pluck('title','id');

        //hitung pending risk berdasarkan level id dan flow
        $pending_risk = 0;
        $step_order = 0;

        // get detail
        $verificationData = $this->getUserVerificationStep($levelId, $is_mr, $selectedUnit->unit_mr);
        $u_step = $verificationData['u_step'];
        $user_verification = $verificationData['user_verification'];

        $step_order = $u_step;

        $draft_risk = IdentifikasiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->where(function ($query) {
                $query->whereIn('status', [
                      IdentifikasiRisiko::STATUS_INPUT_DATA,
                      IdentifikasiRisiko::STATUS_REJECTED
                  ])
                  ->orWhereNull('status');
            })
            ->count();

        // Cek step user sedang aktif, jika user bukan sebagai officer yang Kirim Risiko
        if($step_order > 0){
            // Cek Pending Risk yang bisa di verifikasi dengan cek step_order
            $pending_risk = IdentifikasiRisiko::where(function($query) use ($step_order) {
                $query->where('step_verification', '<=', $step_order)
                    ->orWhereNull('step_verification');
                })
                ->where(function($query) {
                    $query->where('status_progress', '!=', IdentifikasiRisiko::PROGRESS_ON_ACCEPTED)
                        ->where('status_progress', '!=', IdentifikasiRisiko::PROGRESS_ON_FINAL);
                })
                ->where('unit_id', $unitId)
                ->where('periode_id', $periodeId)
                ->count();

            //cek batch notes
            $batchNotes = null;
            if ($dataBatch) {
                $batchNotes = DataBatchNotes::where('data_batch_id', $dataBatch->id)
                    ->where('step_order', $step_order)
                    ->where('unread', 1)
                    ->first();
            }
        }
        else {
            // Untuk levelId = 1 atau levelId = null (risk owner)
            if ($dataBatch && $dataBatch->status == DataBatch::STATUS_REVISI) { // status 5
                // Jika status dataBatch adalah 5 (STATUS_REVISI), hitung risiko dengan status_progress = 2
                $pending_risk = IdentifikasiRisiko::where('status_progress', IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED) // status_progress = 2
                        ->where('unit_id', $unitId)
                        ->where('periode_id', $periodeId)
                        ->count();
            } else {
                // Jika status dataBatch bukan 5 (Revisi), maka pending_risk = 0
                $pending_risk = 0;
            }
        }

        $avgQuantitativeExposure = null;
        $quantitativeRisks = $risiko->filter(function($risk) {
            return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kuantitatif';
        });

        if ($quantitativeRisks->count() > 0) {
            $avgQuantitativeExposure = $quantitativeRisks->avg(function($risk) {
                return $risk->riskAnalysis->eksposur_risiko ?? 0;
            });
        }

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
              'icon' => '<span class="bx-comment-dots"></span>',
              'label' => 'Catatan'
            ],
            [
              'icon' => '<span class="badge bg-primary">!</span>',
              'label' => 'Rekomendasi Risiko'
            ],
        ];

        // Hitung status expired divisi berdasarkan valid_to unit
        $currentUnit = Unit::find($unitId);
        $today = Carbon::today();
        $isStillValid = !$currentUnit?->valid_to || ($currentUnit?->valid_to && ($currentUnit->valid_to->isSameDay($today) || $currentUnit->valid_to->isAfter($today)));
        $unitExpired = !$isStillValid;

        $levelId = auth()->user()->level_id;

        return view('risk-register-ap.index', compact(
            'risiko',
            'unit',
            'unitChild',
            'peristiwaRisiko',
            'status',
            'selectedPeriode',
            'jenisRisiko',
            'pending_risk',
            'min_verification',
            'step_order',
            'dataBatch',
            'batchNotes',
            'levelId',
            'avgQuantitativeExposure',
            'unitId',
            'tableLegend',
            'unitExpired',
            'levelId',
            'draft_risk',
        ));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $unitId = $user->unit_id;
        // ambil pid di query string, fallback ke aktif
        $periodeId = $request->query('pid')
        ?? Periode::where('status', Periode::STATUS_ACTIVE)->value('id');

        $selectedPeriode = Periode::find($periodeId);

        $tck = Tck::where('unit_id', $unitId)->pluck('title', 'id');
        if ($tck->isEmpty()) {
            $parentUnitId = Unit::where('id', $unitId)->value('parent_id');

            if ($parentUnitId) {
                $tck = Tck::where('unit_id', $parentUnitId)->pluck('title', 'id');
            }
        }

        $masterKris = MasterKRI::get();
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $peristiwaRisikos = PeristiwaRisiko::get();
        $areaDampak = AreaDampak::pluck('type','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        //$periode = Periode::where('status','active')->first();

        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $kontrolEksistings = KontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        return view('risk-register-ap.create',compact('kategoriRisiko','peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings','areaDampak','jenisRisiko','tck','selectedPeriode'));
    }

    public function RiskPeriodeList(Request $request)
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
            [
                'icon' => '<span class="bx bx-target-lock"></span>',
                'label' => 'Risk Context'
            ],
        ];

        // Ambil semua data periode (untuk dropdown filter)
        $periodes = Periode::orderBy('tahun', 'desc')->get();

        // Ambil periode aktif jika ada dan set sebagai default pilihan
        $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
        $selectedPeriodeId = $request->query('pid') ?? ($activePeriode?->id);
        $selectedPeriode = $selectedPeriodeId ? Periode::find($selectedPeriodeId) : null;
        $userUnit = auth()->user()->unit;
        $units = [];

        $apAdmin = Gate::check('ap_admin');
        $dataToDisplay = collect();

        if ($apAdmin) {
            $units = Unit::where('unit_type_id', 2)->pluck('name', 'id');
            $displayUnits = Unit::where('unit_type_id', 2)->get();

            foreach ($displayUnits as $unit) {
                if ($selectedPeriode) {
                    $today = \Carbon\Carbon::today();
                    $isValid = ((is_null($unit->valid_to)) || $unit->valid_to->isSameDay($today) || $unit->valid_to->isAfter($today))
                        && ((is_null($unit->valid_from)) || $unit->valid_from->isBefore($today) || $unit->valid_from->isSameDay($today));
                    $unitStatus = $isValid ? 'valid' : 'expired';

                    $riskCount = IdentifikasiRisiko::where('unit_id', $unit->id)
                        ->where('periode_id', $selectedPeriode->id)
                        ->count();

                    $dataToDisplay->push([
                        'unit' => $unit,
                        'periode' => $selectedPeriode,
                        'unit_status' => $unitStatus,
                        'risk_count' => $riskCount,
                    ]);
                }
            }
        } else {
            $units = Unit::where('unit_type_id', 2)->where('id', $userUnit->id)->pluck('name', 'id');

            if ($userUnit && $userUnit->unit_type_id == 2 && $selectedPeriode) {
                $today = \Carbon\Carbon::today();
                $isValid = ((is_null($userUnit->valid_to)) || $userUnit->valid_to->isSameDay($today) || $userUnit->valid_to->isAfter($today))
                    && ((is_null($userUnit->valid_from)) || $userUnit->valid_from->isBefore($today) || $userUnit->valid_from->isSameDay($today));
                $unitStatus = $isValid ? 'valid' : 'expired';

                $riskCount = IdentifikasiRisiko::where('unit_id', $userUnit->id)
                    ->where('periode_id', $selectedPeriode->id)
                    ->count();

                $dataToDisplay->push([
                    'unit' => $userUnit,
                    'periode' => $selectedPeriode,
                    'unit_status' => $unitStatus,
                    'risk_count' => $riskCount,
                ]);

            }
        }

        return view('risk-register-ap.risk-period-list', compact(
            'periodes',
            'activePeriode',
            'selectedPeriode',
            'tableLegend',
            'dataToDisplay',
            'apAdmin',
            'units'
        ));
    }

    public function riskPeriodeDashboard(Request $request, $period)
    {
        $user    = request()->user()->load('unit');
        $periode = Periode::find($period);

        $targetUnitId = null;

        if (Gate::check('ap_admin') && $request->has('unit_id')) {
            $targetUnitId = $request->input('unit_id');
        } else {
            $targetUnitId = $user->unit_id;
        }

        $targetUnit = Unit::find($targetUnitId);

        if (!$targetUnit) {
            abort(404, 'Unit tidak ditemukan.');
        }

        $risikos = IdentifikasiRisiko::where('periode_id', $period)
            ->where('unit_id', $targetUnitId)
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

        return view('risk-register-ap.risk-period-dashboard', compact('user', 'periode', 'risikos', 'riskMaps', 'formattedCurrentRiskMaps', 'targetUnit'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            // 'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'wbs' => 'nullable|string',
            'dampak_risiko' => 'required|array|min:1',
            'dampak_risiko.*' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            'key_risk_indicator' => 'nullable|array',
            'key_risk_indicator.*' => 'nullable|string',
            'satuan_kri' => 'nullable|array',
            'satuan_kri.*' => 'nullable|string',
            'batas_aman' => 'nullable|array',
            'batas_aman.*' => 'nullable|string',
            'batas_waspada' => 'nullable|array',
            'batas_waspada.*' => 'nullable|string',
            'batas_bahaya' => 'nullable|array',
            'batas_bahaya.*' => 'nullable|string',
            // 'jenis_kontrol_eksisting_id' => 'nullable|exists:jenis_kontrol_eksistings,id',
            //'kontrol_eksisting_id' => 'nullable|array',
            //'kontrol_eksisting_id.*' => 'nullable|exists:kontrol_eksistings,id',
            //'kontrol_eksisting' => 'required|string',
            'kontrol_eksisting' => 'required|array',  // Ubah menjadi array
            'kontrol_eksisting.*' => 'required|string', // Validasi setiap item
            // 'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'unit_id' => 'nullable|exists:units,id',
        ]);

        $peristiwa_risiko = $request->peristiwa_risiko;
        $unitId = auth()->user()->unit_id;
        if ($request->has('unit_id') && Gate::check('ap_admin')) {
            $unitId = $request->unit_id;
        }

        // Pengecekan tidak boleh ada peristiwa risiko yang sama di periode dan unit yang sama
        $existingRisk = IdentifikasiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $request->periode_id)
            ->where('peristiwa_risiko', $peristiwa_risiko)
            ->first();

        if ($existingRisk) {
            return response()->json([
                'message' => 'Peristiwa risiko "' . $peristiwa_risiko . '" sudah ada untuk unit ini pada periode yang sama. Silakan gunakan peristiwa risiko yang berbeda atau edit data yang sudah ada.',
                'redirect' => 'back'
            ], 422);
        }

        try {
            // Konversi format tanggal
            $waktuMulai = null;
            $waktuSelesai = null;

            if ($request->perkiraan_waktu_mulai_terpapar_risiko) {
                $waktuMulai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_mulai_terpapar_risiko)->format('Y-m-d');
            }

            if ($request->perkiraan_waktu_selesai_terpapar_risiko) {
                $waktuSelesai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_selesai_terpapar_risiko)->format('Y-m-d');
            }

            // Simpan data risiko
            $identifikasiRisiko = new IdentifikasiRisiko();
            $identifikasiRisiko->periode_id = $request->periode_id;
            $identifikasiRisiko->target_capaian_kinerja = $request->target_capaian_kinerja;

            // Hilangkan jenis risiko dan kategori risiko
            // $identifikasiRisiko->jenis_risiko_id = $request->jenis_risiko_id;
            // $jenisRisiko = \App\Models\JenisRisiko::find($request->jenis_risiko_id);
            // if ($jenisRisiko) {
            //     $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            // }
            $identifikasiRisiko->jenis_risiko_id = 0;
            $identifikasiRisiko->kategori_risiko_id = 0;

            $identifikasiRisiko->peristiwa_risiko = $request->peristiwa_risiko;
            $identifikasiRisiko->deskripsi_peristiwa_risiko = $request->deskripsi_peristiwa_risiko;
            $identifikasiRisiko->wbs = $request->wbs;
            // $identifikasiRisiko->jenis_kontrol_eksisting_id = $request->jenis_kontrol_eksisting_id;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            $identifikasiRisiko->penilaian_efektifitas_kontrol = $request->penilaian_efektifitas_kontrol;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
            $identifikasiRisiko->user_id = auth()->id();
            $identifikasiRisiko->unit_id = $unitId;
            $unit = Unit::find($unitId);
            if ($unit) {
                $identifikasiRisiko->unit_type_id = $unit->unit_type_id;
            }
            else{
                $identifikasiRisiko->unit_type_id = 2;
            }

            // Simpan kontrol eksisting
            // if ($request->has('kontrol_eksisting_id') && is_array($request->kontrol_eksisting_id)) {
            //     // Ubah array menjadi string dengan pemisah koma
            //     $kontrolEksisting = implode(',', $request->kontrol_eksisting_id);
            //     // Simpan ke field kontrol_eksisting
            //     $identifikasiRisiko->kontrol_eksisting = $kontrolEksisting;
            // }
            $identifikasiRisiko->save();

            // Simpan kontrol eksisting ke model KontrolEksisting
            if ($request->has('kontrol_eksisting') && is_array($request->kontrol_eksisting)) {
                foreach ($request->kontrol_eksisting as $kontrolEksisting) {
                    if (!empty($kontrolEksisting)) {
                        $identifikasiRisiko->kontrolEksistings()->create([
                            'risiko_id' => $identifikasiRisiko->id,
                            'peristiwa_risiko_id' => null,
                            'kontrol_eksisting' => $kontrolEksisting,
                        ]);
                    }
                }
            }

            // Buat analisa dan perencanaan
            $identifikasiRisiko->riskAnalysis()->create([]);
            $identifikasiRisiko->rencanaPerlakuanRisiko()->create([]);

            // Simpan dampak risiko
            if ($request->has('dampak_risiko') && is_array($request->dampak_risiko)) {
                foreach ($request->dampak_risiko as $dampak) {
                    $identifikasiRisiko->dampakRisikos()->create([
                        'risiko_id' => $identifikasiRisiko->id,
                        'dampak_risiko' => $dampak,
                    ]);
                }
            }

            // Simpan penyebab risiko
            if ($request->has('penyebab_risiko') && is_array($request->penyebab_risiko)) {
                foreach ($request->penyebab_risiko as $penyebab) {
                    if (!empty($penyebab)) {
                        $identifikasiRisiko->penyebabRisiko()->create([
                            'penyebab_risiko' => $penyebab,
                            'risiko_id' => $identifikasiRisiko->id,
                        ]);
                    }
                }
            }

            // Simpan KRI
            if ($request->has('key_risk_indicator') && is_array($request->key_risk_indicator)) {
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

            // Tentukan redirect berdasarkan action
            $action = $request->input('action', 'save');

            if ($action === 'savenext') {
                // Redirect ke halaman analisis risiko
                return response()->json([
                    'message' => 'Data risiko berhasil disimpan',
                    'redirect' => route('risk-register-ap.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                // Redirect ke halaman index
                return response()->json([
                    'message' => 'Data risiko berhasil disimpan',
                    'redirect' => route('risk-register-ap.index', ['pid' => $request->periode_id, 'unit_id' => $unitId])
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
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

        return view('risk-register-ap.analisa', compact(
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

    public function perencanaan(Request $request, $riskRegisterId)
    {
        $identifikasiRisiko = IdentifikasiRisiko::with([
            'penyebabRisiko.perlakuanPenyebabRisiko',
            'dampakRisikos.perlakuanDampakRisikos',
            'kris',
            'riskAnalysis',
            'peristiwaRisiko',
            'unit',
            'periode'
        ])->findOrFail($riskRegisterId);

        $analisa = $identifikasiRisiko->riskAnalysis;

        return view('risk-register-ap.perencanaan', compact('identifikasiRisiko', 'analisa'));
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
            for ($i = 1; $i <= 4; $i++) {
                $calculateSkala = $this->calculateSkalaDampak($request->{'nilai_dampak_residual_q' . $i} * 100 / $risk_limit);
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

        $xrisk_limit = $this->cleanRupiah($request->_risk_limit);
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
            // Rumus: skalaDampak * (1/100) * (nilaiProbabilitas / 100) * riskTolerance
            $toUpdate['eksposur_risiko'] = floatval($toUpdate['skala_dampak']) * (1/100) * (floatval($toUpdate['nilai_probabilitas']) / 100) * ($riskLimitPeriode->risk_limit ?: 0);
        } else {
            // Rumus: (nilaiDampak * nilaiProbabilitas) / 100
            $toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * ($toUpdate['nilai_probabilitas'] / 100);
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
            'redirect' => route('risk-register-ap.index', ['pid' => $identifikasiRisiko->periode_id])
        ]);
    }

    protected function calculateSkalaDampak($percentage) {
        if ($percentage <= 20) return 1; // Low
        if ($percentage > 20 && $percentage <= 40) return 2; // Low To Moderate
        if ($percentage > 40 && $percentage <= 60) return 3; // Moderate
        if ($percentage > 60 && $percentage <= 80) return 4; // Moderate To High
        return 5; // High
    }

    public function edit($id)
    {
        $user = auth()->user();
        $unitId = $user->unit_id;

        // Ambil data identifikasi risiko
        $identifikasiRisiko = IdentifikasiRisiko::with([
          'dampakRisikos',
          'kontrolEksistings',
          'penyebabRisiko',
          'kris',
        ])->findOrFail($id);

        // Ambil periode yang dipilih
        $selectedPeriode = Periode::find($identifikasiRisiko->periode_id);

        $tck = Tck::where('unit_id', $unitId)->pluck('title', 'id');
        if ($tck->isEmpty()) {
            $parentUnitId = Unit::where('id', $unitId)->value('parent_id');

            if ($parentUnitId) {
                $tck = Tck::where('unit_id', $parentUnitId)->pluck('title', 'id');
            }
        }

        $masterKris = MasterKRI::get();
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $peristiwaRisikos = PeristiwaRisiko::get();
        $areaDampak = AreaDampak::pluck('type','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');

        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $kontrolEksistings = KontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        return view('risk-register-ap.edit', compact(
            'identifikasiRisiko',
            'kategoriRisiko',
            'peristiwaRisikos',
            'masterKris',
            'jenisKontrolEksistings',
            'penilaianEfektifitasKontrols',
            'kontrolEksistings',
            'areaDampak',
            'jenisRisiko',
            'tck',
            'selectedPeriode'
        ));
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'target_capaian_kinerja' => 'required|string',
            // 'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'wbs' => 'nullable|string',
            'dampak_risiko' => 'required|array|min:1',
            'dampak_risiko.*' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            'key_risk_indicator' => 'nullable|array',
            'key_risk_indicator.*' => 'nullable|string',
            'satuan_kri' => 'nullable|array',
            'satuan_kri.*' => 'nullable|string',
            'batas_aman' => 'nullable|array',
            'batas_aman.*' => 'nullable|string',
            'batas_waspada' => 'nullable|array',
            'batas_waspada.*' => 'nullable|string',
            'batas_bahaya' => 'nullable|array',
            'batas_bahaya.*' => 'nullable|string',
            // 'jenis_kontrol_eksisting_id' => 'nullable|exists:jenis_kontrol_eksistings,id',
            'kontrol_eksisting' => 'required|array',
            'kontrol_eksisting.*' => 'required|string',
            // 'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'nullable|date_format:d/m/Y',
        ]);

        try {
            // Konversi format tanggal
            $waktuMulai = null;
            $waktuSelesai = null;

            if ($request->perkiraan_waktu_mulai_terpapar_risiko) {
                $waktuMulai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_mulai_terpapar_risiko)->format('Y-m-d');
            }

            if ($request->perkiraan_waktu_selesai_terpapar_risiko) {
                $waktuSelesai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_selesai_terpapar_risiko)->format('Y-m-d');
            }

            // Ambil data risiko yang akan diupdate
            $identifikasiRisiko = IdentifikasiRisiko::findOrFail($id);

            // Update data risiko
            $identifikasiRisiko->periode_id = $request->periode_id;
            $identifikasiRisiko->target_capaian_kinerja = $request->target_capaian_kinerja;

            // Hilangkan jenis risiko dan kategori risiko
            // $identifikasiRisiko->jenis_risiko_id = $request->jenis_risiko_id;
            // $jenisRisiko = \App\Models\JenisRisiko::find($request->jenis_risiko_id);
            // if ($jenisRisiko) {
            //     $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            // }

            $identifikasiRisiko->peristiwa_risiko = $request->peristiwa_risiko;
            $identifikasiRisiko->deskripsi_peristiwa_risiko = $request->deskripsi_peristiwa_risiko;
            $identifikasiRisiko->wbs = $request->wbs;
            // $identifikasiRisiko->jenis_kontrol_eksisting_id = $request->jenis_kontrol_eksisting_id;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            // $identifikasiRisiko->penilaian_efektifitas_kontrol = $request->penilaian_efektifitas_kontrol;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;

            $identifikasiRisiko->save();

            // Hapus kontrol eksisting lama dan buat yang baru
            $identifikasiRisiko->kontrolEksistings()->delete();

            // Simpan kontrol eksisting ke model KontrolEksisting
            if ($request->has('kontrol_eksisting') && is_array($request->kontrol_eksisting)) {
                foreach ($request->kontrol_eksisting as $kontrolEksisting) {
                    if (!empty($kontrolEksisting)) {
                        $identifikasiRisiko->kontrolEksistings()->create([
                            'risiko_id' => $identifikasiRisiko->id,
                            'peristiwa_risiko_id' => null,
                            'kontrol_eksisting' => $kontrolEksisting,
                        ]);
                    }
                }
            }

            $dampakRisikoIds = [];
            foreach ($request->dampak_risiko as $dampakRisikoId => $dampakRisiko) {
                $exist = $identifikasiRisiko->dampakRisikos()->where('id', $dampakRisikoId)->first();

                if ($exist) {
                    $exist->update([
                        'dampak_risiko' => $dampakRisiko,
                    ]);
                } else {
                    $exist = $identifikasiRisiko->dampakRisikos()->create([
                        'dampak_risiko' => $dampakRisiko
                    ]);
                }
                $dampakRisikoIds[] = $exist->id;
            }
            $identifikasiRisiko->dampakRisikos()->whereNotIn('id', $dampakRisikoIds)->delete();

            $penyebabRisikoIds = [];
            foreach ($request->penyebab_risiko as $penyebabRisikoId => $penyebabRisiko) {
                $exist = $identifikasiRisiko->penyebabRisiko()->where('id', $penyebabRisikoId)->first();
                if ($exist) {
                    $exist->update([
                        'penyebab_risiko' => $penyebabRisiko,
                    ]);
                } else {
                    $exist = $identifikasiRisiko->penyebabRisiko()->create([
                        'penyebab_risiko' => $penyebabRisiko,
                    ]);
                }
                $penyebabRisikoIds[] = $exist->id;
            }
            $identifikasiRisiko->penyebabRisiko()->whereNotIn('id', $penyebabRisikoIds)->delete();

            $savedKriIds = [];
            foreach ($request->key_risk_indicator as $key => $kri) {
                $kriData = [
                    'kri_id' => 0,
                    'kri' => $kri,
                    'satuan_kri' => $request->satuan_kri[$key] ?? '',
                    'batas_aman' => $request->batas_aman[$key] ?? '',
                    'batas_waspada' => $request->batas_waspada[$key] ?? '',
                    'batas_bahaya' => $request->batas_bahaya[$key] ?? '',
                ];

                $existKri = $identifikasiRisiko->kris()->find($key);

                if ($existKri) {
                    $existKri->update($kriData);
                    $savedKriIds[] = $existKri->id;
                } else {
                    $newKri = $identifikasiRisiko->kris()->create($kriData);
                    $savedKriIds[] = $newKri->id;
                }
            }
            $identifikasiRisiko->kris()->whereNotIn('id', $savedKriIds)->delete();

            // Tentukan redirect berdasarkan action
            $action = $request->input('action', 'save');

            if ($action === 'savenext') {
                // Redirect ke halaman analisis risiko
                return response()->json([
                    'message' => 'Data risiko berhasil diperbarui',
                    'redirect' => route('risk-register-ap.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                // Redirect ke halaman index
                return response()->json([
                    'message' => 'Data risiko berhasil diperbarui',
                    'redirect' => route('risk-register-ap.index', ['pid' => $request->periode_id])
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Cari data identifikasi risiko
            $identifikasiRisiko = IdentifikasiRisiko::findOrFail($id);

            // Cek apakah user memiliki akses untuk menghapus
            if (!Gate::check('risk_register_delete') && $identifikasiRisiko->user_id != auth()->id()) {
                return redirect()->route('risk-register-ap.index')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
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

            return redirect()->route('risk-register-ap.index')->with('success', 'Data risiko berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('risk-register-ap.index')->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    public function send(Request $request)
    {
        // Dapatkan data unit dan periode
        $user = auth()->user();
        //$unit_id = $user->unit_id;
        $unit_id = $request->unit_id;
        $periode_id = $request->periode_id;

        if (!$unit_id) {
            return redirect()->route('risk-register-ap.index', [
                'pid' => $periode_id,
                'unit_id' => $user->unit_id
            ])
                ->with('error', 'Unit belum dipilih. Silahkan pilih unit terlebih dahulu.');
        }
        $level_id = $user->level_id;
        $periode_id = $request->periode_id;
        $send_type = $request->send_type ?? 'send';

        $min_verification = 3;
        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;

        if (!$periode_id) {
            return redirect()->route('risk-register-ap.index', [
                'unit_id' => $unit_id
            ])
                ->with('error', 'Periode tidak ditemukan');
        }

        $unit = Unit::find($unit_id);
        $verificationData = $this->getUserVerificationStep($level_id, $is_mr, $unit->unit_mr);
        $u_step = $verificationData['u_step'];
        $user_verification = $verificationData['user_verification'];
        $step_order = $u_step;

        if($send_type=='mainrisk'){
            $dataBatch = DataBatch::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where('type', 1) // type = 1 untuk unit/divisi
                ->orderBy('batch', 'desc')
                ->first();

            if ($dataBatch) {
                $dataBatch->update([
                    'status' => DataBatch::STATUS_FINISH,
                    'step_verification' => $step_order,
                    'finish' => true
                ]);
                $dataBatch->refresh();

                DataBatch::create([
                    'unit_id' => $unit_id,
                    'periode_id' => $periode_id,
                    'type' => 1,
                    'batch' => $dataBatch->batch + 1,
                    'status' => DataBatch::STATUS_PROSES,
                    'step_verification' => 0,
                    'finish' => false,
                ]);
            }

            IdentifikasiRisiko::where('unit_id', $unit_id)
                        ->where('periode_id', $periode_id)
                        ->update([
                            'status' => IdentifikasiRisiko::STATUS_PUBLISHED,
                            'step_verification' => $step_order
                        ]);

            //tetapkan project risk utama
            IdentifikasiRisiko::determineMainRisks($unit_id, $periode_id);

            return redirect()->route('risk-register-ap.index', [
                'pid' => $periode_id,
                'unit_id' => $unit_id
            ])
                ->with('success', 'Risiko berhasil dipublish');
        }
        else{
            // Cek apakah semua risiko sudah dianalisa dan dilakukan rencana perlakuan
            $identifikasiRisikos = IdentifikasiRisiko::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where(function ($query) {
                    $query->whereIn('status', [
                              IdentifikasiRisiko::STATUS_INPUT_DATA,
                              IdentifikasiRisiko::STATUS_REJECTED
                          ])
                          ->orWhereNull('status');
                })
                ->get();
            // dd($identifikasiRisikos, $unit_id, $periode_id);

            // Cek apakah ada risiko yang belum dianalisa atau belum memiliki rencana perlakuan
            $belumLengkap = false;
            $idRisikoBelumLengkap = [];

            // $appFlow = $this->getFlowData($unit_id, $level_id);
            // $step_order = $appFlow['step_order'];
            // $min_verification = $appFlow['min_verification'];
            // $approval_step_id = $appFlow['approval_step_id'];

            foreach ($identifikasiRisikos as $risiko) {
                // Cek apakah risiko memiliki analisis risiko
                if (!$risiko->riskAnalysis) {
                    $belumLengkap = true;
                    $idRisikoBelumLengkap[] = $risiko->id;
                    continue;
                }

                // Cek apakah semua penyebab risiko memiliki perlakuan
                $penyebabRisikos = $risiko->penyebabRisikos;
                if ($penyebabRisikos->isEmpty()) {
                    $belumLengkap = true;
                    $idRisikoBelumLengkap[] = $risiko->id;
                    continue;
                }

                // Cek apakah setiap penyebab risiko memiliki perlakuan
                foreach ($penyebabRisikos as $penyebabRisiko) {
                    if ($penyebabRisiko->perlakuanPenyebabRisikoUnit->isEmpty()) {
                        $belumLengkap = true;
                        $idRisikoBelumLengkap[] = $risiko->id;
                        break;
                    }
                }
            }

            if ($belumLengkap) {
                return redirect()->route('risk-register-ap.index', [
                    'pid' => $periode_id,
                    'unit_id' => $unit_id
                ])
                    ->with('error', 'Terdapat risiko yang belum dianalisa atau belum memiliki rencana perlakuan. Silahkan lengkapi terlebih dahulu.');
            }

            // Cek di data batch apakah sudah ada batch terkait unit_id dan periode_id dengan type = 1 (unit)
            $dataBatch = DataBatch::where('unit_id', $unit_id)
                ->where('periode_id', $periode_id)
                ->where('type', 1) // type = 1 untuk unit/divisi
                ->orderBy('batch', 'desc')
                ->first();

            //revisi atau kirim
            if($send_type == 'rev'){
                if($dataBatch->step_verification == 1){
                    $dataBatch->update(
                        [
                            'status' => DataBatch::STATUS_KIRIM,
                            'finish' => false
                        ]
                    );
                    $update_status = IdentifikasiRisiko::STATUS_DIKIRIM;
                }
                else{
                    $dataBatch->update(
                        [
                            'status' => DataBatch::STATUS_VERIFIKASI,
                            'finish' => false
                        ]
                    );
                    $update_status = IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI;
                }
                $dataBatch->refresh();

                //update identifikasi risiko yang statusnya bukan antara 2, 3, 4 dan 6 untuk dijadikan 2
                IdentifikasiRisiko::where('unit_id', $unit_id)
                    ->where('periode_id', $periode_id)
                    ->whereNotIn('status', [IdentifikasiRisiko::STATUS_DIKIRIM, IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI, IdentifikasiRisiko::STATUS_TERVERIFIKASI, IdentifikasiRisiko::STATUS_PUBLISHED])
                    ->update(['status' => $update_status,
                    'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW]);

                //isi batch notes
                if ($request->has('catatan_perbaikan') && !empty($request->catatan_perbaikan)) {
                    DataBatchNotes::create([
                        'data_batch_id' => $dataBatch->id,
                        'notes' => $request->catatan_perbaikan,
                        'step_order' => $dataBatch->step_verification,
                        'user_id' => auth()->id()
                    ]);
                }

                // Kembali ke halaman index dan informasi bahwa pengiriman risiko sudah dilakukan
                return redirect()->route('risk-register-ap.index', [
                    'pid' => $periode_id,
                    'unit_id' => $unit_id
                ])
                    ->with('success', 'Perbaikan risiko berhasil dilakukan. Risiko telah dikirim untuk diverifikasi.');
            }
            else{
                // Tentukan nilai batch
                if (!$dataBatch) {
                    // Jika belum ada, buat batch baru dengan nilai batch = 1
                    $batch = 1;

                    // Buat data batch baru
                    $newDataBatch = DataBatch::create([
                        'periode_id' => $periode_id,
                        'type' => 1, // type = 1 untuk unit/divisi
                        'unit_id' => $unit_id,
                        'batch' => $batch,
                        'status' => DataBatch::STATUS_KIRIM, // Status kirim
                        'step_verification' => 1,
                        'finish' => false
                    ]);

                }
                else if (!$dataBatch->finish) {
                    //jika pengiriman pertama
                    if($dataBatch->step_verification==null || $dataBatch->step_verification < 1){
                        // Jika sudah ada dan status belum finish
                        if($dataBatch->status != DataBatch::STATUS_PROSES){
                            return redirect()->route('risk-register-ap.index', ['pid' => $periode_id, 'unit_id' => $unit_id])
                                ->with('error', 'Masih ada data batch risiko yang sedang berproses. Silahkan tunggu hingga proses selesai.');
                        }
                        else{
                            if ($unit->unit_mr) {
                              // Khusus Divisi MR: Kirim Risiko pertama dari Officer Divisi MR
                              $dataBatch->update([
                                  'status' => DataBatch::STATUS_VERIFIKASI,
                                  'step_verification' => 3,
                                  'finish' => false
                              ]);

                              foreach ($identifikasiRisikos as $risiko) {
                                  $risiko->update([
                                      'status' => IdentifikasiRisiko::STATUS_DIKIRIM,
                                      'status_risiko' => 1,
                                      'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW,
                                      'step_verification' => 3
                                  ]);
                              }
                            } else {
                              $dataBatch->update([
                                  'status' => DataBatch::STATUS_KIRIM,
                                  'step_verification' => 1,
                                  'finish' => false
                              ]);
                            }
                            $dataBatch->refresh();
                        }
                    }
                    else{
                        //cek step order dan min verification
                        if($step_order >= $min_verification){//last send
                            $dataBatch->update([
                                    'status' => DataBatch::STATUS_RANKING,
                                    'finish' => false
                            ]);
                            $dataBatch->refresh();

                            $verifiedRisks = IdentifikasiRisiko::where('unit_id', $unit_id)
                            ->where('periode_id', $periode_id)
                            ->where('status', IdentifikasiRisiko::STATUS_TERVERIFIKASI)
                            ->with('riskAnalysis')
                            ->get();

                            $quantitativeRisks = $verifiedRisks->filter(function($risk) {
                                return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kuantitatif';
                            });

                            $qualitativeRisks = $verifiedRisks->filter(function($risk) {
                                return $risk->riskAnalysis && $risk->riskAnalysis->kategori_dampak === 'Kualitatif';
                            });

                            if ($quantitativeRisks->count() > 0) {
                                $avgExposure = $quantitativeRisks->avg(function($risk) {
                                    return $risk->riskAnalysis->eksposur_risiko ?? 0;
                                });

                                // Tandai risiko kuantitatif yang nilainya di atas rata-rata
                                foreach ($quantitativeRisks as $risk) {
                                    if (($risk->riskAnalysis->eksposur_risiko ?? 0) > $avgExposure) {
                                        $risk->update([
                                            'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_RECOMMENDATION
                                        ]);
                                    }
                                }
                            }

                            // Untuk risiko kualitatif, tandai yang nilai risikonya >= 20
                            foreach ($qualitativeRisks as $risk) {
                                if (($risk->riskAnalysis->skala_risiko ?? 0) >= 20) {
                                    $risk->update([
                                        'status_risiko' => IdentifikasiRisiko::STATUS_RISIKO_RECOMMENDATION
                                    ]);
                                }
                            }
                        }
                        else{
                            $dataBatch->update([
                                    'status' => DataBatch::STATUS_VERIFIKASI,
                                    'step_verification' => $dataBatch->step_verification + 1,
                                    'finish' => false
                            ]);
                            $dataBatch->refresh();
                        }
                    }
                }
                else {
                    if($dataBatch->finish){
                        // Jika sudah ada dan status sudah finish, nilai batch adalah batch sebelumnya + 1
                        $batch = $dataBatch->batch + 1;

                        $newDataBatch = DataBatch::create([
                            'periode_id' => $periode_id,
                            'type' => 1, // type = 1 untuk unit/divisi
                            'unit_id' => $unit_id,
                            'batch' => $batch,
                            'status' => DataBatch::STATUS_KIRIM, // Status kirim
                            'step_verification' => 1,
                            'finish' => false
                        ]);
                    }
                }

                if($dataBatch->status <= DataBatch::STATUS_KIRIM){
                    // Ubah semua risiko di identifikasi_risikos dengan status = 2 (Dikirim), status_risiko = 1, dan status_progress = 1
                    foreach ($identifikasiRisikos as $risiko) {
                        $risiko->update([
                            'status' => IdentifikasiRisiko::STATUS_DIKIRIM, // Status dikirim
                            'status_risiko' => 1,
                            'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW,
                            'step_verification' => 1
                        ]);
                    }
                }

                // Kembali ke halaman index dan informasi bahwa pengiriman risiko sudah dilakukan
                return redirect()->route('risk-register-ap.index', [
                    'pid' => $periode_id,
                    'unit_id' => $unit_id
                ])
                    ->with('success', 'Pengiriman risiko berhasil dilakukan. Risiko telah dikirim untuk diverifikasi.');
            }
        }
    }

    public function verifikasi(Request $request, $riskRegisterId)
    {
        $user = auth()->user();
        //$unit_id = $user->unit_id;
        $level_id = $user->level_id;
        // Cari data identifikasi risiko
        $identifikasiRisiko = IdentifikasiRisiko::findOrFail($riskRegisterId);
        $unit_id = $identifikasiRisiko->unit_id;
        $periode_id = $identifikasiRisiko->periode_id;

        // $appFlow = $this->getFlowData($unit_id, $level_id);
        // $step_order = $appFlow['step_order'];
        // $min_verification = $appFlow['min_verification'];
        // $approval_step_id = $appFlow['approval_step_id'];

        //batch perlu tahu bahwa masih ada risiko yang dikembalikan sehingga batch menjadi on revision
        $dataBatch = DataBatch::where('unit_id', $identifikasiRisiko->unit_id)
        ->where('periode_id', $identifikasiRisiko->periode_id)
        ->where('type', 1) // type = 1 untuk unit/divisi
        ->orderBy('batch', 'desc')
        ->first();

        $is_mr = $user->unit ? ($user->unit->unit_mr == 1) : false;
        $verificationData = $this->getUserVerificationStep($level_id, $is_mr);
        $u_step = $verificationData['u_step'];
        $user_verification = $verificationData['user_verification'];
        $step_order = $u_step;
        $min_verification = 3;

        if ($step_order == 0 || $dataBatch->step_verification != $step_order) {
            return redirect()->route('risk-register-ap.index')
                ->with('error', 'Anda tidak memiliki hak untuk melakukan verifikasi risiko');
        }

        // Validasi input
        $validated = $request->validate([
            'catatan_verifikasi' => 'required|string',
            'status_verifikasi' => 'required|in:terima,tolak',
        ]);

        // dd($step_order, $verificationData);
        try {
            // Cek apakah user memiliki izin verifikator_risiko
            if (!Gate::check('risk_register_verification')) {
                return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melakukan verifikasi risiko');
            }

            // catat log disini
            Log::channel('verification')->info('Verifikasi risiko dengan ID: ' . $identifikasiRisiko->id . ' oleh user dengan ID: ' . auth()->id());
            Log::channel('verification')->info('Step Order: ' . $step_order);
            Log::channel('verification')->info('Min Verification: ' . $min_verification);

            // Update status risiko berdasarkan hasil verifikasi
            if ($validated['status_verifikasi'] === 'terima') {
                //catat log disini
                Log::channel('verification')->info('Verifikasi risiko dengan ID: ' . $identifikasiRisiko->id . ' diterima oleh user dengan ID: ' . auth()->id());

                //pengecekan jika step_order yang dimiliki level_id adalah sama dengan min_verification
                if($step_order >= $min_verification){
                    // Jika diterima, update status menjadi terverifikasi
                    $identifikasiRisiko->update([
                        'status' => IdentifikasiRisiko::STATUS_TERVERIFIKASI,
                        'status_progress' => IdentifikasiRisiko::PROGRESS_ON_ACCEPTED,
                        'status_risiko' => 1, //valid
                        'step_verification' => $step_order
                    ]);
                }
                else{
                    // Jika diterima, update status menjadi terverifikasi
                    $identifikasiRisiko->update([
                        'status' => IdentifikasiRisiko::STATUS_TUNGGU_VERIFIKASI, //3
                        'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVIEW, //1
                        'step_verification' => $step_order + 1
                    ]);
                }

                //disini maka akan simpan approval logs
                // ApprovalLog::create([
                //     'risk_id' => $riskRegisterId,
                //     'approval_step_id' => $approval_step_id,
                //     'type' => 1, // 1 untuk unit
                //     'step_order' => $step_order,
                //     'approved_by' => auth()->id(),
                //     'approved_at' => now(),
                // ]);

                //semua batch notes perlu diupdate sudah read jadi unread menjadi false
                $batchNotes = DataBatchNotes::where('data_batch_id', $dataBatch->id)
                    ->where('step_order', $step_order)
                    ->update([
                        'unread' => false
                    ]);
            }
            else {//ditolak
                //catat log disini
                Log::channel('verification')->info('Verifikasi risiko dengan ID: ' . $identifikasiRisiko->id . ' ditolak oleh user dengan ID: ' . auth()->id());

                // Jika ditolak, kembalikan ke status input data
                $identifikasiRisiko->update([
                    'status' => IdentifikasiRisiko::STATUS_REJECTED,
                    'status_progress' => IdentifikasiRisiko::PROGRESS_ON_REVISION_DELETED // Kembali ke input data
                ]);

                $dataBatch->update([
                    'status' => DataBatch::STATUS_REVISI,
                ]);

                //semua batch notes perlu diupdate sudah read jadi unread menjadi false
                $batchNotes = DataBatchNotes::where('data_batch_id', $dataBatch->id)
                    ->where('step_order', $step_order)
                    ->update([
                        'unread' => false
                    ]);
            }

            // Simpan catatan verifikasi ke RiskNote
            $riskNote = new RiskNote([
                'risiko_id' => $riskRegisterId,
                'type' => 1, // 1 = unit/divisi
                'status' => $validated['status_verifikasi'] === 'terima' ? 1 : 2, // 1 = verifikasi, 2 = revisi/tolak
                'notes' => $validated['catatan_verifikasi'],
                'user_id' => auth()->id(),
            ]);

            $riskNote->save();

            // Redirect dengan pesan sukses
            return redirect()->route('risk-register-ap.index', [
                'pid' => $identifikasiRisiko->periode_id,
                'unit_id' => $identifikasiRisiko->unit_id
            ])
                ->with('success', 'Verifikasi risiko berhasil dilakukan');

        } catch (\Exception $e) {
            // Tangani kesalahan
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memverifikasi risiko: ' . $e->getMessage());
        }
    }

    /**
     * Mendapatkan step_order berdasarkan unit_id, periode_id, dan level_id
     *
     * @param int $unit_id ID unit
     * @param int $periode_id ID periode
     * @param int $level_id ID level
     * @return int step_order
     */
    private function getFlowData($unit_id, $level_id)
    {
        $step_order = 0;
        $min_verification = 3;
        $approval_step_id = null;

        // Jika bukan risk owner atau level_id null, kembalikan 0
        if ($level_id == 1 || $level_id == null) {
            return [
                'step_order' => $step_order,
                'min_verification' => $min_verification,
                'approval_step_id' => $approval_step_id
            ];
        }

        $approvalFlow = ApprovalFlow::where('unit_id', $unit_id)
            ->whereNull('project_id')
            ->first();

        if ($approvalFlow) {
            $min_verification = $approvalFlow->min_verification;
            $approvalStep = ApprovalStep::where('approval_flow_id', $approvalFlow->id)
                ->where('level_id', $level_id)
                ->first();
            if ($approvalStep) {
                $step_order = $approvalStep->step_order;
                $approval_step_id = $approvalStep->id;
            } else {
                //default step order
                if ($level_id == 2) {
                    $step_order = 1;
                } else if ($level_id == 3) {
                    $step_order = 2;
                } else if ($level_id == 4) {
                    $step_order = 3;
                }
            }
        } else {
            //default step order jika tidak ada approval flow
            if ($level_id == 2) {
                $step_order = 1;
            } else if ($level_id == 3) {
                $step_order = 2;
            } else if ($level_id == 4) {
                $step_order = 3;
            }
        }

        return [
            'step_order' => $step_order,
            'min_verification' => $min_verification,
            'approval_step_id' => $approval_step_id
        ];
    }

    public function view($id)
    {
        $user    = request()->user()->load('unit');
        $risikos = IdentifikasiRisiko::where('id', $id)
            ->with([
              // 'taksonomiRisiko',
              'dampakRisikos',
              'penyebabRisikos',
              // 'parameterRisikos',
              'riskAnalysis',
            ])
            ->get();

        $currentRiskMaps = $risikos->pluck('currentRiskMaps');
        $formattedCurrentRiskMaps = [];

        foreach ($risikos as $idx => $risk) {
            $getFallbackValue = function($targetQuarter) use ($risk) {
                if (isset($risk->current_risk_maps[$targetQuarter]) &&
                    !is_null($risk->current_risk_maps[$targetQuarter]['skala_dampak']) &&
                    !is_null($risk->current_risk_maps[$targetQuarter]['skala_probabilitas'])) {
                    return $risk->current_risk_maps[$targetQuarter];
                }

                for ($q = $targetQuarter - 1; $q >= 1; $q--) {
                    if (isset($risk->current_risk_maps[$q]) &&
                        !is_null($risk->current_risk_maps[$q]['skala_dampak']) &&
                        !is_null($risk->current_risk_maps[$q]['skala_probabilitas'])) {
                        return $risk->current_risk_maps[$q];
                    }
                }

                if (isset($risk->current_risk_maps['inherent']) &&
                    !is_null($risk->current_risk_maps['inherent']['skala_dampak']) &&
                    !is_null($risk->current_risk_maps['inherent']['skala_probabilitas'])) {
                    return $risk->current_risk_maps['inherent'];
                }

                if ($risk->riskAnalysis) {
                    return [
                        'skala_dampak' => $risk->riskAnalysis->skala_dampak,
                        'skala_probabilitas' => $risk->riskAnalysis->skala_probabilitas->tingkat ?? null,
                        'skala_risiko' => $risk->riskAnalysis->skala_risiko,
                        'level_risiko' => $risk->riskAnalysis->level_risiko,
                    ];
                }

                return null;
            };

            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $currentValue = $getFallbackValue($quarter);

                if ($currentValue &&
                    !is_null($currentValue['skala_dampak']) &&
                    !is_null($currentValue['skala_probabilitas'])) {
                    $currentValue['quarter'] = $quarter;
                    $formattedCurrentRiskMaps[$risk->id][] = $currentValue;
                }
            }
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        $risk_tolerance = 0;
        $risk_limit = 0;
        $risiko = $risikos->first();

        if ($risiko->riskAnalysis->kategori_dampak == 'Kuantitatif') {
            $unit = $risiko->unit;
            $periode = $risiko->periode;

            $riskLimitPeriode = RisklimitPeriode::where('unit_id', $unit->id)->where('periode_id', $periode->id)->first();
            if ($riskLimitPeriode) {
                $risk_limit = $riskLimitPeriode->risk_limit;
                $risk_tolerance = $riskLimitPeriode->risk_limit;
            }
        }

        // dd($risk_limit, $risk_tolerance);
        return view('risk-register-ap.view', compact('user', 'risikos', 'riskMaps', 'formattedCurrentRiskMaps', 'risk_limit', 'risk_tolerance'));
    }

    private function getUserVerificationStep($level_id, $is_mr = false, $unit_mr = false)
    {
        $u_step = 0;
        $user_verification = "";

        if($level_id == 2) { // RO Divisi
            if($is_mr) { // RO Divisi MR
                $u_step = 3; // step verifikasi user
                $user_verification = "Risk Owner Manajemen Risiko";
            } else {
                $u_step = 1; // step verifikasi user
                $user_verification = "Risk Owner Divisi";
            }
        }
        else if($level_id == 1 && $is_mr) { // RO MR
            if ($unit_mr) {
              $u_step = 0; // step kirim risiko khusus divisi MR
            } else {
              $u_step = 2; // step verifikasi user
            }
            $user_verification = "Risk Officer MR";
        }
        else{
            $u_step = 0;
            $user_verification = "Risk Officer Divisi";
        }

        return [
            'u_step' => $u_step,
            'user_verification' => $user_verification
        ];
    }

    public function getRiskNotes(Request $request, $risk)
    {
        try {
            $notes = RiskNote::with('user')
                ->where('risiko_id', $risk)
                ->where('type', 1)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($notes);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil data catatan.'], 500);
        }
    }
}
