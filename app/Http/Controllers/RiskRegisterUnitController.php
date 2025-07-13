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

class RiskRegisterUnitController extends Controller
{
    public function index(Request $request)
    {
        $unitId = auth()->user()->unit_id;
        // Ambil periode_id dari parameter URL
        $periodeId = $request->query('pid');
        
        // Jika tidak ada parameter periode, gunakan periode aktif
        if (!$periodeId) {
            $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
            $periodeId = $activePeriode ? $activePeriode->id : null;
        }
        
        // Ambil data periode yang dipilih
        $selectedPeriode = Periode::find($periodeId);
        
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
        ]);
        
        // Filter berdasarkan periode jika ada
        if ($periodeId) {
            $risikoQuery->where('periode_id', $periodeId);
        }
        
        // Filter berdasarkan unit_id
        $risikoQuery->where('unit_id', $unitId);

        // Ambil data risiko
        $risiko = $risikoQuery->get();
    
        // Data untuk filter
        $unit = Unit::pluck('name', 'id');
        $unitChild = Unit::where('parent_id', '!=', null)->pluck('name', 'id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
    
        // Status untuk kontrol tombol kirim
        $status = 1; // Defaultnya proses
    
        return view('risk-register-unit.index', compact(
            'risiko',
            'unit',
            'unitChild',
            'peristiwaRisiko',
            'status',
            'selectedPeriode',
            'jenisRisiko'
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

        return view('risk-register-unit.create',compact('kategoriRisiko','peristiwaRisikos', 'masterKris', 'jenisKontrolEksistings', 'penilaianEfektifitasKontrols', 'kontrolEksistings','areaDampak','jenisRisiko','tck','selectedPeriode'));
    }

    public function RiskPeriodeList()
    {
        // Ambil semua data periode
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        
        // Ambil periode aktif jika ada
        $activePeriode = Periode::where('status', Periode::STATUS_ACTIVE)->first();
        
        return view('risk-register-unit.risk-period-list', compact('periodes', 'activePeriode'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            //'target_capaian_kinerja' => 'required|exists:tcks,id',
            //'peristiwa_risiko_id' => 'required|exists:peristiwa_risikos,id',
            'target_capaian_kinerja' => 'required|string',
            'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            //'master_kri_id' => 'nullable|array',
            //'master_kri_id.*' => 'nullable|exists:master_kri,id',
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
            'jenis_kontrol_eksisting_id' => 'nullable|exists:jenis_kontrol_eksistings,id',
            //'kontrol_eksisting_id' => 'nullable|array',
            //'kontrol_eksisting_id.*' => 'nullable|exists:kontrol_eksistings,id',
            //'kontrol_eksisting' => 'required|string',
            'kontrol_eksisting' => 'required|array',  // Ubah menjadi array
            'kontrol_eksisting.*' => 'required|string', // Validasi setiap item
            'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
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
    
            // Simpan data risiko
            $identifikasiRisiko = new IdentifikasiRisiko();
            $identifikasiRisiko->periode_id = $request->periode_id;
            // $identifikasiRisiko->tck_id = $request->target_capaian_kinerja;
            // $tck = Tck::where('id', $request->target_capaian_kinerja)->first();

            // if ($tck) {
            //     $identifikasiRisiko->target_capaian_kinerja = $tck->title;
            // }
            $identifikasiRisiko->target_capaian_kinerja = $request->target_capaian_kinerja;
            // $identifikasiRisiko->peristiwa_risiko_id = $request->peristiwa_risiko_id;
            // $peristiwaRisiko = PeristiwaRisiko::find($request->peristiwa_risiko_id);
            // if ($peristiwaRisiko) {
            //     $identifikasiRisiko->kategori_risiko_id = $peristiwaRisiko->kategori_risiko_id;
            //     $identifikasiRisiko->jenis_risiko_id = $peristiwaRisiko->jenis_risiko_id;
            // }
            $identifikasiRisiko->jenis_risiko_id = $request->jenis_risiko_id;
            $jenisRisiko = \App\Models\JenisRisiko::find($request->jenis_risiko_id);
            if ($jenisRisiko) {
                $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            }

            $identifikasiRisiko->peristiwa_risiko = $request->peristiwa_risiko;
            $identifikasiRisiko->deskripsi_peristiwa_risiko = $request->deskripsi_peristiwa_risiko;
            $identifikasiRisiko->jenis_kontrol_eksisting_id = $request->jenis_kontrol_eksisting_id;
            //$identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            $identifikasiRisiko->penilaian_efektifitas_kontrol = $request->penilaian_efektifitas_kontrol;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
            $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
            $identifikasiRisiko->user_id = auth()->id();
            $unitId = auth()->user()->unit_id;
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
            // if ($request->has('master_kri_id') && is_array($request->master_kri_id)) {
            //     $masterKriObj = MasterKRI::whereIn('id', $request->master_kri_id)->get()->keyBy('id');
                
            //     foreach ($request->master_kri_id as $masterKriId) {
            //         if (!empty($masterKriId)) {
            //             $kriObj = $masterKriObj[$masterKriId];
            //             $identifikasiRisiko->kris()->create([
            //                 'kri_id' => $masterKriId,  // Ubah kri_id menjadi master_kri_id jika diperlukan
            //                 'risiko_id' => $identifikasiRisiko->id,  // Tetap sertakan risiko_id
            //                 'kri' => $kriObj->kri,
            //                 'satuan_kri' => $kriObj->satuan_kri,
            //                 'batas_aman' => $kriObj->batas_aman,
            //                 'batas_waspada' => $kriObj->batas_waspada,
            //                 'batas_bahaya' => $kriObj->batas_bahaya,
            //             ]);
            //         }
            //     }
            // }
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
                    'redirect' => route('risk-register-unit.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                // Redirect ke halaman index
                return response()->json([
                    'message' => 'Data risiko berhasil disimpan',
                    'redirect' => route('risk-register-unit.index', ['pid' => $request->periode_id])
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

        return view('risk-register-unit.analisa', compact(
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
            'kris',
            'riskAnalysis',
            'peristiwaRisiko',
            'unit',
            'periode'
        ])->findOrFail($riskRegisterId);

        $analisa = $identifikasiRisiko->riskAnalysis;

        return view('risk-register-unit.perencanaan', compact('identifikasiRisiko', 'analisa'));
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
            'jenis_rencana_perlakuan_risiko' => 'required|exists:jenis_rencana_perlakuan_risikos,id',
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
            'jenis_rencana_perlakuan_risiko' => $validated['jenis_rencana_perlakuan_risiko'],
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
            'jenis_rencana_perlakuan_risiko' => $perlakuan->jenis_rencana_perlakuan_risiko,
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
            'xjenis_rencana_perlakuan_risiko' => 'required',
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
                'jenis_rencana_perlakuan_risiko' => $validated['xjenis_rencana_perlakuan_risiko'],
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

        if ($request->kategori_dampak == 'Kuantitatif') {
            $unit = $identifikasiRisiko->unit;
            $periode = $identifikasiRisiko->periode;
            $risk_limit = 0;

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
        $toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * $toUpdate['nilai_probabilitas'];

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
            'redirect' => route('risk-register-unit.index', ['pid' => $identifikasiRisiko->periode_id])
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
        $identifikasiRisiko = IdentifikasiRisiko::with(['kontrolEksistings', 'penyebabRisiko', 'kris'])->findOrFail($id);
        
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

        return view('risk-register-unit.edit', compact(
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
            'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
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
            'jenis_kontrol_eksisting_id' => 'nullable|exists:jenis_kontrol_eksistings,id',
            'kontrol_eksisting' => 'required|array',
            'kontrol_eksisting.*' => 'required|string',
            'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
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
            $identifikasiRisiko->jenis_risiko_id = $request->jenis_risiko_id;
            
            $jenisRisiko = \App\Models\JenisRisiko::find($request->jenis_risiko_id);
            if ($jenisRisiko) {
                $identifikasiRisiko->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
            }

            $identifikasiRisiko->peristiwa_risiko = $request->peristiwa_risiko;
            $identifikasiRisiko->deskripsi_peristiwa_risiko = $request->deskripsi_peristiwa_risiko;
            $identifikasiRisiko->jenis_kontrol_eksisting_id = $request->jenis_kontrol_eksisting_id;
            $identifikasiRisiko->kontrol_eksisting = $request->kontrol_eksisting[0] ?? '';
            $identifikasiRisiko->penilaian_efektifitas_kontrol = $request->penilaian_efektifitas_kontrol;
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

            // Hapus penyebab risiko lama dan buat yang baru
            $identifikasiRisiko->penyebabRisiko()->delete();
            
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

            // Hapus KRI lama dan buat yang baru
            $identifikasiRisiko->kris()->delete();
            
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
                    'message' => 'Data risiko berhasil diperbarui',
                    'redirect' => route('risk-register-unit.analisa', ['riskRegister' => $identifikasiRisiko->id])
                ]);
            } else {
                // Redirect ke halaman index
                return response()->json([
                    'message' => 'Data risiko berhasil diperbarui',
                    'redirect' => route('risk-register-unit.index', ['pid' => $request->periode_id])
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
                return redirect()->route('risk-register-unit.index')->with('error', 'Anda tidak memiliki izin untuk menghapus data ini');
            }
            
            // Hapus data terkait
            // Hapus kontrol eksisting
            $identifikasiRisiko->kontrolEksistings()->delete();
            
            // Hapus penyebab risiko
            $identifikasiRisiko->penyebabRisiko()->delete();
            
            // Hapus KRI
            $identifikasiRisiko->kris()->delete();
            
            // Hapus analisis risiko jika ada
            if ($identifikasiRisiko->riskAnalysis) {
                $identifikasiRisiko->riskAnalysis->delete();
            }
            
            // Hapus data identifikasi risiko
            $identifikasiRisiko->delete();
            
            return redirect()->route('risk-register-unit.index')->with('success', 'Data risiko berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('risk-register-unit.index')->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
}
