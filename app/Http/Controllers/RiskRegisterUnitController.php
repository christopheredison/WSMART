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
use App\Models\SkalaProbabilitas;
use App\Models\RiskMap;

class RiskRegisterUnitController extends Controller
{
    public function index(Request $request)
    {
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
        
        // Ambil data risiko
        $risiko = $risikoQuery->get();
    
        // Data untuk filter
        $unit = Unit::pluck('name', 'id');
        $unitChild = Unit::where('parent_id', '!=', null)->pluck('name', 'id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
    
        // Status untuk kontrol tombol kirim
        $status = 1; // Defaultnya proses
    
        return view('risk-register-unit.index', compact(
            'risiko',
            'unit',
            'unitChild',
            'peristiwaRisiko',
            'status',
            'selectedPeriode'
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

        // $draft = null;
        // if (request()->draft_key) {
        //     $draft = Draft::where('type', IdentifikasiRisiko::class)
        //         ->where('key', request()->draft_key)
        //         ->first();
            
        //     if ($draft) {
        //         $draft = $draft->data;
        //     }
        // }

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
            'target_capaian_kinerja' => 'required|exists:tcks,id',
            'peristiwa_risiko_id' => 'required|exists:peristiwa_risikos,id',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            'master_kri_id' => 'nullable|array',
            'master_kri_id.*' => 'nullable|exists:master_kri,id',
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
            'kontrol_eksisting_id' => 'nullable|array',
            'kontrol_eksisting_id.*' => 'nullable|exists:kontrol_eksistings,id',
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
            $identifikasiRisiko->tck_id = $request->target_capaian_kinerja;
            $tck = Tck::where('id', $request->target_capaian_kinerja)->first();

            if ($tck) {
                $identifikasiRisiko->target_capaian_kinerja = $tck->title;
            }
            
            $identifikasiRisiko->peristiwa_risiko_id = $request->peristiwa_risiko_id;
            $peristiwaRisiko = PeristiwaRisiko::find($request->peristiwa_risiko_id);
            if ($peristiwaRisiko) {
                $identifikasiRisiko->kategori_risiko_id = $peristiwaRisiko->kategori_risiko_id;
                $identifikasiRisiko->jenis_risiko_id = $peristiwaRisiko->jenis_risiko_id;
            }
            $identifikasiRisiko->deskripsi_peristiwa_risiko = $request->deskripsi_peristiwa_risiko;
            $identifikasiRisiko->jenis_kontrol_eksisting_id = $request->jenis_kontrol_eksisting_id;
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
            if ($request->has('kontrol_eksisting_id') && is_array($request->kontrol_eksisting_id)) {
                // Ubah array menjadi string dengan pemisah koma
                $kontrolEksisting = implode(',', $request->kontrol_eksisting_id);
                // Simpan ke field kontrol_eksisting
                $identifikasiRisiko->kontrol_eksisting = $kontrolEksisting;
            }

            $identifikasiRisiko->save();
    
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
            if ($request->has('master_kri_id') && is_array($request->master_kri_id)) {
                $masterKriObj = MasterKRI::whereIn('id', $request->master_kri_id)->get()->keyBy('id');
                
                foreach ($request->master_kri_id as $masterKriId) {
                    if (!empty($masterKriId)) {
                        $kriObj = $masterKriObj[$masterKriId];
                        $identifikasiRisiko->kris()->create([
                            'kri_id' => $masterKriId,  // Ubah kri_id menjadi master_kri_id jika diperlukan
                            'risiko_id' => $identifikasiRisiko->id,  // Tetap sertakan risiko_id
                            'kri' => $kriObj->kri,
                            'satuan_kri' => $kriObj->satuan_kri,
                            'batas_aman' => $kriObj->batas_aman,
                            'batas_waspada' => $kriObj->batas_waspada,
                            'batas_bahaya' => $kriObj->batas_bahaya,
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
                    'redirect' => route('risk-analysis.create', ['risiko_id' => $identifikasiRisiko->id])
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

        $autoCalculate = false; // Flag untuk menentukan apakah perhitungan harus dilakukan secara otomatis

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
        
        // Validasi input
        $validated = $request->validate([
            'kategori_dampak'                    => 'required|string|in:Kualitatif,Kuantitatif',
            'area_dampak'                        => 'nullable|string',
            //'risk_limit'                         => 'required|numeric',
            'nilai_dampak'                       => 'required',
            'nilai_probabilitas'                 => 'required|numeric',
            'skala_dampak'                       => 'required|integer',
            'skala_dampak_hidden'                => 'required|integer',
            'deskripsi_dampak'                   => 'nullable|string',
            'asumsi_perhitungan_dampak'          => 'nullable|string',

            'nilai_dampak_residual'              => 'nullable',
            'nilai_probabilitas_residual'        => 'nullable|numeric',
            'skala_dampak_residual'              => 'nullable|integer',
            'skala_dampak_residual_hidden'       => 'nullable|integer',
            'deskripsi_dampak_residual'          => 'nullable|string',
            'asumsi_perhitungan_dampak_residual' => 'nullable|string',
        ]);

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

        $analisa->update($toUpdate);
        
        $tingkatSkalaProbabilitas = SkalaProbabilitas::getSkalaByValue($request->nilai_probabilitas);
        $tingkatSkalaProbabilitasResidual = SkalaProbabilitas::getSkalaByValue($request->nilai_probabilitas_residual);

        $toUpdate = [
            'nilai_probabilitas' => $request->nilai_probabilitas,
            'nilai_probabilitas_residual' => $request->nilai_probabilitas_residual,
            'skala_dampak' => $request->skala_dampak_hidden,
            'skala_dampak_residual' => $request->skala_dampak_residual_hidden,
            'nilai_dampak' => $nilai_dampak,
            'nilai_dampak_residual' => $nilai_dampak_residual,
            //'risk_limit' => $request->risk_limit,
            //'risk_tolerance' => null, // calculated [Done]
        ];

        $toUpdate['skala_probabilitas_id'] = $tingkatSkalaProbabilitas->id;
        $toUpdate['skala_probabilitas_residual_id'] = $tingkatSkalaProbabilitasResidual->id;

        $riskMap = $riskMaps[$toUpdate['skala_dampak'] . '-' . $tingkatSkalaProbabilitas->tingkat] ?? null;
        if (!$riskMap) {
            return response()->json([
                'message' => 'Tidak ada data risk map untuk skala dampak dan probabilitas yang dipilih',
            ], 422);
        }

        $riskMapResidual = $riskMaps[$toUpdate['skala_dampak_residual'] . '-' . $tingkatSkalaProbabilitasResidual->tingkat] ?? null;

        if (!$riskMapResidual) {
            return response()->json([
                'message' => 'Tidak ada data risk map untuk skala dampak residual dan probabilitas residual yang dipilih',
            ], 422);
        }

        $toUpdate['skala_risiko'] = $riskMap->nilai_risiko;
        $toUpdate['level_risiko'] = $riskMap->level_risiko;
        $toUpdate['skala_risiko_residual'] = $riskMapResidual->nilai_risiko;
        $toUpdate['level_risiko_residual'] = $riskMapResidual->level_risiko;

        $toUpdate['eksposur_risiko'] = $toUpdate['nilai_dampak'] * $toUpdate['nilai_probabilitas'];

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
}
