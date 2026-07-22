<?php

namespace App\Http\Controllers\RiskOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdentifikasiRisiko;
use App\Models\PenyebabRisiko;
use App\Models\KRI;
use App\Models\RencanaKegiatan;
use App\Models\SikapRisiko;
use App\Models\JenisRisiko;
use App\Models\KategoriRisiko;
use App\Models\PeristiwaRisiko;
use App\Models\AreaDampak;
use App\Models\Draft;
use App\Models\SkalaDampak;
use App\Models\SkalaProbabilitas;
use App\Models\Periode;
use App\Models\RiskAnalysis;
use App\Models\MasterRisiko;
use App\Models\RencanaPerlakuanRisiko;
use App\Models\RiskSetting;
use App\Models\MonitoringRisiko;
use App\Models\RiskMap;
use App\Models\Tck;
use App\Models\User;
use App\Models\Unit;
use App\Models\StrategiRisiko;
use App\Models\RiskLimit;
use App\Models\DataBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class RiskRegisterController extends Controller
{
    public function tempSave(Request $request)
    {
        $request->session()->put('temp_form_data', $request->all());

        return response()->json(['success' => true, 'message' => 'Data temporarily saved']);
    }

    public function index()
    {
        $user = auth()->user();
        $unitTypeId = $user->unit_type_id;
        $unitId = $user->unit_id;
        $query = IdentifikasiRisiko::query();

        $periodeActive = Periode::where('status','active')->first();
        // Jika pengguna memiliki izin 'risk_register_all_unit', mereka dapat melihat keseluruhan data risiko
        if ($user->can('risk_register_all_unit')) {
            // Tidak ada pemfilteran tambahan diperlukan untuk pengguna dengan izin ini
        }
        else if($user->can('risk_register_child_unit')){
            $query->where(function ($query) use ($unitId) {
                $query->where('unit_id', $unitId)
                      ->orWhereHas('unit', function ($subQuery) use ($unitId) {
                          $subQuery->where('parent_id', $unitId);
                      });
            });
        }
        else {
            // Logika pemfilteran risiko berdasarkan unit pengguna
            /*
            if ($unitTypeId === 4) {
                $query->where(function ($q) use ($user) {
                    $q->where('unit_type_id', $user->unit_type_id)
                        ->where('user_id', $user->id)
                        ->orWhere(function ($qq) use ($user) {
                            $qq->whereIn('unit_type_id', [1, 3])
                                ->where('user_id', $user->parent_id);
                        });
                });
            } elseif ($unitTypeId === 2) {
                $usersWithSameParent = User::where('parent_id', $user->id)->get();
                $userIds = $usersWithSameParent->pluck('id')->toArray();

                $query->whereIn('user_id', $userIds);
            } else {
                $query->where('user_id', $user->id);
            }
            */
            $query->where('unit_id', $unitId);
        }

        $query->orderBy('created_at', 'desc');

        // $usersSameParent = User::where('parent_id', $user->id)->pluck('unit_id');
        // $unitChild = Unit::whereIn('id', $unitIds)->pluck('name', 'id');

        $unitChild = Unit::where('parent_id', $unitId)->pluck('name', 'id');
        $unit = Unit::pluck('name', 'id');

        // Ambil data risiko sesuai dengan query yang telah dibangun
        $risiko = $query->get();
        // Ambil data lain yang diperlukan untuk tampilan
        $kategoriJenisRisiko = JenisRisiko::pluck('title', 'id');
        $unit = Unit::pluck('name', 'id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title', 'id');
        $riskAnalysis = RiskAnalysis::pluck('level_risiko', 'id');
        $risikoId = IdentifikasiRisiko::where('status_progress', 'risk_owner')->first();
        $drafts = [];
        if (Gate::check('risk_register_create')) {
            $draftData = Draft::where('type', IdentifikasiRisiko::class)
                ->where('user_id', request()->user()->id)
                ->get();
            foreach ($draftData as $draft) {
                $draftObj = new IdentifikasiRisiko($draft->data);
                $draftObj->draft_key = $draft->key;
                $drafts[] = $draftObj;
            }
        }

        // dd($risiko);
        //get databatch
        $dataBatch = DataBatch::where('unit_id', Auth::user()->unit_id)
            ->where('periode_id', $periodeActive->id)
            ->where('finish', false)
            ->first();

        $status = $dataBatch ? $dataBatch->status : null;
        
        // Kirimkan data ke tampilan
        return response()->view('risk-officer.risk-register.index', compact('risiko', 'peristiwaRisiko', 'kategoriJenisRisiko', 'riskAnalysis', 'risikoId', 'unit', 'unitChild', 'drafts', 'dataBatch', 'status'));
    }

    public function create()
    {
        $user = auth()->user();
        $unitId = $user->unit_id;
        $tck = Tck::where('unit_id', $unitId)->pluck('title', 'id');
        if ($tck->isEmpty()) {
            $parentUnitId = Unit::where('id', $unitId)->value('parent_id');

            if ($parentUnitId) {
                $tck = Tck::where('unit_id', $parentUnitId)->pluck('title', 'id');
            }
        }

        $rencanaKegiatan = RencanaKegiatan::pluck('title','id');
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title','id');
        $riskSetting = RiskSetting::pluck('efektivitas_control','id');
        $areaDampak = AreaDampak::pluck('type','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        $periode = Periode::where('status','active')->first();

        $draft = null;
        if (request()->draft_key) {
            $draft = Draft::where('type', IdentifikasiRisiko::class)
                ->where('key', request()->draft_key)
                ->first();
            
            if ($draft) {
                $draft = $draft->data;
            }
        }

        return view('risk-officer.risk-register.create',compact('rencanaKegiatan','kategoriRisiko','peristiwaRisiko','riskSetting','areaDampak','jenisRisiko','tck','periode', 'draft'));
    }

    public function edit(Request $request, $riskRegister) {
        $riskRegister = IdentifikasiRisiko::where('id', $riskRegister)->firstOrFail();
        $unitId = $request->user()->unit_id;
        $tck = Tck::where('unit_id', $unitId)->pluck('title', 'id');
        if ($tck->isEmpty()) {
            $parentUnitId = Unit::where('id', $unitId)->value('parent_id');

            if ($parentUnitId) {
                $tck = Tck::where('unit_id', $parentUnitId)->pluck('title', 'id');
            }
        }

        $rencanaKegiatan = RencanaKegiatan::pluck('title','id');
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $peristiwaRisiko = PeristiwaRisiko::pluck('title','id');
        $riskSetting = RiskSetting::pluck('efektivitas_control','id');
        $areaDampak = AreaDampak::pluck('type','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        $periode = Periode::where('status','active')->first();

        return view('risk-officer.risk-register.edit2',compact('rencanaKegiatan','kategoriRisiko','peristiwaRisiko','riskSetting','areaDampak','jenisRisiko','tck','periode',  'riskRegister'));
    }

    public function getJenisRisiko($kategoriRisikoId)
    {
        $jenisRisiko = JenisRisiko::where('kategori_risiko_id', $kategoriRisikoId)->pluck('title', 'id');
        return response()->json(['jenisRisiko' => $jenisRisiko]);
    }

    public function getPeristiwaRisiko($jenisRisikoId)
    {
        $peristiwaRisiko = PeristiwaRisiko::where('jenis_risiko_id', $jenisRisikoId)->pluck('title', 'id');
        return response()->json(['peristiwaRisiko' => $peristiwaRisiko]);
    }

    public function getRiskMapData()
    {
        $riskMap = RiskMap::select('level_risiko', 'nilai_risiko')->get();
        return response()->json($riskMap);
    }

    public function store(Request $request)
    {
        if (MasterRisiko::where('deskripsi_peristiwa_risiko', $request->deskripsi_peristiwa_risiko)->exists()) {
            return redirect()
                ->route('risk-register.create', ['draft_key' => $request->draft_key])
                ->withErrors(['Deskripsi Peristiwa Risiko sudah ada'])
                ->withInput();
        }
        $draft = $this->storeAsDraft($request);

        //dd($draft);
        
        $validator = Validator::make($request->all(), [
            'deskripsi_rencana_kegiatan' => 'required',
            // 'periode_id' => 'required',
            'target_capaian_kinerja' => 'required',
            // 'rencana_kegiatan' => 'required',
            'kategori_risiko_id' => 'required',
            'jenis_risiko_id' => 'required',
            'peristiwa_risiko_id' => 'required',
            'deskripsi_peristiwa_risiko' => 'required',
            'kontrol_eksisting' => 'required',
            'type' => 'required',
            'penilaian_efektifitas_kontrol' => 'required',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required',
            'key_risk_indicator' => 'required|array',
            'key_risk_indicator.*' => 'required',
            'satuan_kri.*' => 'required',
            'batas_aman.*' => 'required',
            //'batas_waspada.*' => 'required',
            'batas_bahaya.*' => 'required',
        ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'is_success' => false,
        //         'errors' => $validator->errors()->all(),
        //         'message' => 'Invalid request data',
        //     ], 422);
        // }

        if ($validator->fails()) {
            return redirect()
                ->route('risk-register.create', ['draft_key' => $draft['key']])
                ->withErrors($validator)
                ->withInput();
        }

        $data = [];
        $dataKRI = [];

        foreach ($request->penyebab_risiko as $key => $penyebabRisiko) {
            $penyebabRisikoData = PenyebabRisiko::create([
                'penyebab_risiko' => $penyebabRisiko,
                //'key_risk_indicator' => $request->key_risk_indicator[$key],
                //'satuan_kri' => $request->satuan_kri[$key],
                //'batas_aman' => $request->batas_aman[$key],
                //'batas_waspada' => $request->batas_waspada[$key],
                //'batas_bahaya' => $request->batas_bahaya[$key],
            ]);
            $data[] = $penyebabRisikoData;
        }

        //save kri
        foreach ($request->key_risk_indicator as $key => $key_risk_indicator) {
            $kriData = KRI::create([
                'kri' => $key_risk_indicator,
                'satuan_kri' => $request->satuan_kri[$key],
                'batas_aman' => $request->batas_aman[$key],
                'batas_waspada' => $request->batas_waspada[$key],
                'batas_bahaya' => $request->batas_bahaya[$key],
            ]);
            $dataKRI[] = $kriData;
        }

        $perkiraan_waktu_terpapar = explode(" to ", $request->perkiraan_waktu_terpapar_risiko);
        $perkiraan_waktu_terpapar_risiko_mulai = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[0])->format('Y-m-d');
        $perkiraan_waktu_terpapar_risiko_akhir = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[1] ?? $perkiraan_waktu_terpapar[0])->format('Y-m-d');
        // $rencana_kegiatan = implode(", ", $request->rencana_kegiatan);
        $periodeActive = Periode::where('status','active')->first();
        $unitId = Auth::user()->unit_id;
        $identifikasiRisiko = IdentifikasiRisiko::create([
            'periode_id' => $periodeActive->id,
            'user_id' => Auth::user()->id,
            'unit_id' => Auth::user()->unit_id,
            'unit_type_id' => Auth::user()->unit_type_id,
            'target_capaian_kinerja' => $request->target_capaian_kinerja,
            'type' => $request->type,
            // 'rencana_kegiatan' => $rencana_kegiatan,
            'deskripsi_rencana_kegiatan' => $request->deskripsi_rencana_kegiatan,
            'kategori_risiko_id' => $request->kategori_risiko_id,
            'jenis_risiko_id' => $request->jenis_risiko_id,
            'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
            'deskripsi_peristiwa_risiko' => $request->deskripsi_peristiwa_risiko,
            'kontrol_eksisting' => $request->kontrol_eksisting,
            'penilaian_efektifitas_kontrol' => $request->penilaian_efektifitas_kontrol,
            'perkiraan_waktu_terpapar_risiko_mulai' => $perkiraan_waktu_terpapar_risiko_mulai,
            'perkiraan_waktu_terpapar_risiko_akhir' => $perkiraan_waktu_terpapar_risiko_akhir,
        ]);

        $identifikasiRisikoId = $identifikasiRisiko->id;

        MasterRisiko::create([
            'risiko_id' => $identifikasiRisikoId,
            'kategori_risiko_id' => $request->kategori_risiko_id,
            'jenis_risiko_id' => $request->jenis_risiko_id,
            'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
            'deskripsi_peristiwa_risiko' => $request->deskripsi_peristiwa_risiko
        ]);

        RiskAnalysis::create([
            'risiko_id' => $identifikasiRisikoId
        ]);

        RencanaPerlakuanRisiko::create([
            'risiko_id' => $identifikasiRisikoId
        ]);

        //add data batch
        $dtBatch = DataBatch::where('periode_id', $periodeActive->id)->where('unit_id', $unitId)->where('finish', false)->first();

        $next_batch = 1;
        if(!$dtBatch){
            $last_batch = DataBatch::where('unit_id', $unitId)
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
        
        //dd($last_batch);

        $dataBatch = DataBatch::updateOrCreate(
            [
                'unit_id' => Auth::user()->unit_id,
                'periode_id' => $periodeActive->id,
                'finish' => false,
            ],
            [
                'status' => DataBatch::STATUS_PROSES,
                'batch' => $next_batch
            ]
        );

        foreach ($data as $penyebabRisikoData) {
            $penyebabRisikoData->update(['risiko_id' => $identifikasiRisikoId]);
        }

        foreach ($dataKRI as $kriData) {
            $kriData->update(['risiko_id' => $identifikasiRisikoId]);
        }

        //Draft::where('type', IdentifikasiRisiko::class)->where('key', $request->draft_key)->delete();

        if (empty($request->draft_key)) {
            // Jika kosong atau null, gunakan draft_key dari $draft
            Draft::where('type', IdentifikasiRisiko::class)->where('key', $draft['key'])->delete();

            //log draft
            Log::channel('inputlog')->info('Draft Key : '.$draft['key']);
        } 
        else {
            // Jika tidak kosong, gunakan draft_key dari $request
            Draft::where('type', IdentifikasiRisiko::class)->where('key', $request->draft_key)->delete();

            //log draft
            Log::channel('inputlog')->info('Request Draft Key : '.$request->draft_key);
        }

        return redirect()->route('risk-register.index')->with('success', 'Risk Register successfully!');
    }

    public function update(Request $request, $riskRegister)
    {
        $riskRegister = IdentifikasiRisiko::where('id', $riskRegister)->firstOrFail();

        $validator = Validator::make($request->all(), [
            // 'periode_id' => 'required',
            'target_capaian_kinerja' => 'required',
            // 'rencana_kegiatan' => 'required',
            'kategori_risiko_id' => 'required',
            'jenis_risiko_id' => 'required',
            'peristiwa_risiko_id' => 'required',
            'kontrol_eksisting' => 'required',
            'type' => 'required',
            'penilaian_efektifitas_kontrol' => 'required',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required',
            'key_risk_indicator' => 'required|array',
            'key_risk_indicator.*' => 'required',
            'satuan_kri.*' => 'required',
            'batas_aman.*' => 'required',
            //'batas_waspada.*' => 'required',
            'batas_bahaya.*' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        unset($data['deskripsi_peristiwa_risiko']);

        $perkiraan_waktu_terpapar = explode(" to ", $request->perkiraan_waktu_terpapar_risiko);
        $perkiraan_waktu_terpapar_risiko_mulai = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[0])->format('Y-m-d');
        $perkiraan_waktu_terpapar_risiko_akhir = Carbon::createFromFormat('d/m/Y', $perkiraan_waktu_terpapar[1] ?? $perkiraan_waktu_terpapar[0])->format('Y-m-d');

        $riskRegister->update([
            'target_capaian_kinerja' => $request->target_capaian_kinerja,
            'deskripsi_rencana_kegiatan' => $request->deskripsi_rencana_kegiatan,
            'kategori_risiko_id' => $request->kategori_risiko_id,
            'jenis_risiko_id' => $request->jenis_risiko_id,
            'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
            'type' => $request->type,
            'kontrol_eksisting' => $request->kontrol_eksisting,
            'penilaian_efektifitas_kontrol' => $request->penilaian_efektifitas_kontrol,
            'perkiraan_waktu_terpapar_risiko_mulai' => $perkiraan_waktu_terpapar_risiko_mulai,
            'perkiraan_waktu_terpapar_risiko_akhir' => $perkiraan_waktu_terpapar_risiko_akhir,
        ]);

        $penyebabRisikoIds = [];
        foreach ($request->penyebab_risiko as $key => $penyebabRisiko) {
            $penyebabRisikoId = $request->penyebab_risiko_id[$key] ?? null;

            if ($penyebabRisikoId) {
                $penyebabRisikoData = PenyebabRisiko::where('id', $penyebabRisikoId)->update([
                    'penyebab_risiko' => $penyebabRisiko,
                ]);
                $penyebabRisikoIds[] = $penyebabRisikoId;
            } else {
                $penyebabRisikoData = PenyebabRisiko::create([
                    'risiko_id' => $riskRegister->id,
                    'penyebab_risiko' => $penyebabRisiko,
                ]);
                $penyebabRisikoIds[] = $penyebabRisikoData->id;
            }
        }
        PenyebabRisiko::where('risiko_id', $riskRegister->id)->whereNotIn('id', $penyebabRisikoIds)->delete();

        //save kri
        $keyRiskIndicatorIds = [];
        foreach ($request->key_risk_indicator as $key => $key_risk_indicator) {
            $keyRiskIndicatorId = $request->key_risk_indicator_ids[$key] ?? null;

            if ($keyRiskIndicatorId) {
                $kriData = KRI::where('id', $keyRiskIndicatorId)->update([
                    'kri' => $key_risk_indicator,
                    'satuan_kri' => $request->satuan_kri[$key],
                    'batas_aman' => $request->batas_aman[$key],
                    'batas_waspada' => $request->batas_waspada[$key],
                    'batas_bahaya' => $request->batas_bahaya[$key],
                ]);
                $keyRiskIndicatorIds[] = $keyRiskIndicatorId;
            } else {
                $kriData = KRI::create([
                    'risiko_id' => $riskRegister->id,
                    'kri' => $key_risk_indicator,
                    'satuan_kri' => $request->satuan_kri[$key],
                    'batas_aman' => $request->batas_aman[$key],
                    'batas_waspada' => $request->batas_waspada[$key],
                    'batas_bahaya' => $request->batas_bahaya[$key],
                ]);
                $keyRiskIndicatorIds[] = $kriData->id;
            }
        }
        KRI::where('risiko_id', $riskRegister->id)->whereNotIn('id', $keyRiskIndicatorIds)->delete();

        return redirect()->route('risk-register.index')->with('success', 'Risk Register updated successfully!');
    }

    public function storeAsDraft(Request $request)
    {
        if (MasterRisiko::where('deskripsi_peristiwa_risiko', $request->deskripsi_peristiwa_risiko)->exists()) {
            return response()->json(['error' => 'Deskripsi Peristiwa Risiko sudah ada'], 422);
        }
        $key = $request->draft_key ?: uniqid();
        $data = $request->except('_token', 'draft_key');

        $periodeActive = Periode::where('status','active')->first();
        $data['periode_id'] = $periodeActive->id;
        $data['user_id'] = $request->user()->id;
        $data['unit_id'] = $request->user()->unit_id;
        $data['unit_type_id'] = $request->user()->unit_type_id;

        Draft::updateOrCreate(
            ['type' => IdentifikasiRisiko::class, 'key' => $key],
            ['data' => $data, 'user_id' => $request->user()->id],
        );

        return [
            'success' => true,
            'message' => 'Berhasil menyimpan draft',
            'key' => $key
        ];
    }

    public function destroy($riskRegister)
    {
        $user = auth()->user();
        $unitId = $user->unit_id;

        if (!is_numeric($riskRegister)) {
            Draft::where('type', IdentifikasiRisiko::class)->where('key', $riskRegister)->delete();
            return redirect()->route('risk-register.index')->with('success', 'Draft deleted successfully!');
        }

        // Cek apakah IdentifikasiRisiko ditemukan
        if (!$identifikasiRisiko) {
            return redirect()->route('risk-register.index')->with('error', 'Risiko tidak ditemukan!');
        }

        if ($identifikasiRisiko->unit_id != $unitId) {
            return redirect()->route('risk-register.index')->with('error', 'Anda tidak memiliki ijin untuk menghapus risiko di unit ini!');
        }

        // Cek kondisi status_risiko, status_progress, dan status
        if ($identifikasiRisiko->status_risiko !== 'proses' || 
            $identifikasiRisiko->status_progress !== 'risk_champion' || 
            $identifikasiRisiko->status != 2) {
            return redirect()->route('risk-register.index')->with('error', 'Risiko sudah terproses, kembalikan terlebih dahulu risiko ke Risk Officer masing-masing');
        }

        //cek di table monitoring
        $isUsedInMonitoring = MonitoringRisiko::where('risiko_id', $riskRegister)->exists();

        if ($isUsedInMonitoring) {
            return redirect()->route('risk-register.index')->with('error', 'Risiko ini tidak dapat dihapus, karena sudah dilakukan monitoring atas risiko! (Silahkan hapus dulu monitoring risiko terkait)');
        }

        //delete penyebab risiko
        PenyebabRisiko::where('risiko_id', $riskRegister)->delete();

        //delete perencanaan risiko
        RencanaPerlakuanRisiko::where('risiko_id', $riskRegister)->delete();

        //delete KRI risiko
        KRI::where('risiko_id', $riskRegister)->delete();

        $masterRegisterId = MasterRisiko::where('risiko_id',$riskRegister)->first();
        $masterRegisterId->delete();

        $riskRegisterId = IdentifikasiRisiko::where('id',$riskRegister)->first();
        $riskRegisterId->delete();
        
        return redirect()->route('risk-register.index')->with('success', 'Risk Register deleted successfully!');
    }

    public function view($riskRegister)
    {
        $identifikasiRisiko = IdentifikasiRisiko::where('id',$riskRegister)->first();
        $penyebabRisiko = PenyebabRisiko::where('risiko_id',$riskRegister)->get();
        $rencana = RencanaPerlakuanRisiko::where('risiko_id',$riskRegister)->first();
        $kri = KRI::where('risiko_id',$riskRegister)->get();
        $riskAnalysis = RiskAnalysis::where('risiko_id',$riskRegister)->first();

        return view('risk-officer.risk-register.view',compact('identifikasiRisiko','penyebabRisiko','kri', 'rencana', 'riskAnalysis'));
    }

    public function kuantifikasi($riskRegister)
    {
        $identifikasiRisiko = IdentifikasiRisiko::where('id',$riskRegister)->first();
        $skalaDampak = SkalaDampak::pluck('tingkat','id');
        $riskAnalysis = RiskAnalysis::where('risiko_id',$riskRegister)->first();
        $skalaProbabilitas = SkalaProbabilitas::where('type_risiko',$identifikasiRisiko->type)->pluck('tingkat','skala');
        $minSkalaProbabilitas = SkalaProbabilitas::where('type_risiko',$identifikasiRisiko->type)->pluck('min','id');
        $maxSkalaProbabilitas = SkalaProbabilitas::where('type_risiko',$identifikasiRisiko->type)->pluck('max','id');
        $areaDampak = AreaDampak::where('type',$identifikasiRisiko->type)->pluck('title','id');
        $identifikasiRisiko = IdentifikasiRisiko::where('id',$riskRegister)->first();
        $penyebabRisiko = PenyebabRisiko::where('risiko_id',$riskRegister)->get();

        $riskMaps = RiskMap::orderBy('nilai_risiko')->get();

        // Inisialisasi array
        $level_risiko = array_fill(0, 6, array_fill(0, 6, ""));
        $nilai_risiko = array_fill(0, 6, array_fill(0, 6, ""));

        

        // Susun data ke dalam array
        foreach ($riskMaps as $riskMap) {
            $nilai = $riskMap->nilai_risiko;
            if ($nilai > 0 && $nilai <= 25) {
                $row = ceil($nilai / 5);
                $col = $nilai % 5 == 0 ? 5 : $nilai % 5;

                $level_risiko[$row][$col] = $riskMap->level_risiko;
                $nilai_risiko[$row][$col] = $riskMap->nilai_risiko;
            }
        }

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

        //dd($level_risiko);

        $user = auth()->user();
        $periode = Periode::where('status','active')->pluck('id')->first();
        $strategiRisiko = StrategiRisiko::with('riskLimit')
                                ->where('unit_id', $user->unit_id)
                                ->where('periode_id',$periode)
                                ->orderBy('created_at', 'desc')
                                ->first();

        
        
        if (!$strategiRisiko) {
            $parentId = Unit::where('id',$user->unit_id)->pluck('parent_id')->first();
            $strategiRisiko = StrategiRisiko::with('riskLimit')
            ->where('unit_id', $parentId)
            ->where('periode_id', $periode)
            ->orderBy('created_at', 'desc')
            ->first();
        }

        if (!$strategiRisiko) {
            return redirect()->route('risk-register.index')->with('error', 'Harap isi dulu strategi risiko unit Anda!');
        }
        
        $jenisRisikoFromIdentifikasiRisiko = IdentifikasiRisiko::where('id',$riskRegister)->pluck('jenis_risiko_id')->first();

        // $persentaseLimit = RiskLimit::where('strategi_risiko_id', $strategiRisiko->id)
        //                         ->where('jenis_risiko_id', $jenisRisikoFromIdentifikasiRisiko)
        //                         ->pluck('persentase_limit')
        //                         ->first();
        //dd($persentaseLimit);
        //dd($minSkalaProbabilitas);
        //dd($maxSkalaProbabilitas);
        /*
        $tingkat = null;
        foreach ($minSkalaProbabilitas as $id => $min) {
            $max = $maxSkalaProbabilitas[$id];
            if ($persentaseLimit >= $min && $persentaseLimit <= $max) {
                $tingkat = SkalaProbabilitas::where('id', $id)->pluck('tingkat')->first();
                break;
            }
        }
        */
        //dd($tingkat);
        //dd($skalaDampak);
        return view('risk-officer.risk-register.kuantifikasi',compact('skalaDampak','riskRegister','riskAnalysis','skalaProbabilitas','areaDampak','identifikasiRisiko','penyebabRisiko','minSkalaProbabilitas','maxSkalaProbabilitas','riskMaps','level_risiko','nilai_risiko'));
    }

    public function getSkalaProbabilitas(Request $request)
    {
        // Ambil nilai probabilitas dari request
        $nilaiProbabilitas = $request->input('nilai_probabilitas');

        // Cari skala probabilitas berdasarkan nilai yang dimasukkan
        $skalaProbabilitas = SkalaProbabilitas::where('type_risiko', $request->input('type_risiko'))
            ->where('min', '<=', $nilaiProbabilitas)
            ->where('max', '>=', $nilaiProbabilitas)
            ->first();

        // Kembalikan data skala probabilitas dalam format JSON
        return response()->json($skalaProbabilitas);
    }


    public function updateKuantifikasi(Request $request, $riskRegister)
    {
        // dd($request->all());
        $riskAnalysis = RiskAnalysis::where('risiko_id',$riskRegister)->first();
        $nilai_dampak = preg_replace('/\D/', '', $request->nilai_dampak);
        $riskAnalysis->update([
            'kategori_dampak' => $request->kategori_dampak,
            'deskripsi_dampak' => $request->deskripsi_dampak,
            'nilai_dampak' => $nilai_dampak,
            'skala_dampak' => $request->skala_dampak,
            'nilai_probabilitas' => $request->nilai_probabilitas,
            'skala_probabilitas_id' => $request->skala_probabilitas_id,
            'skala_risiko' => $request->skala_risiko,
            'level_risiko' => $request->level_risiko,
            'area_dampak' => $request->area_dampak,
        ]);

        $result = IdentifikasiRisiko::where('id', $riskRegister)->update([
            'skala_risiko' => $request->skala_risiko,
            'level_risiko' => $request->level_risiko,
        ]);

        return redirect()->route('risk-register.index')->with('success', 'Update Kuantifikasi successfully!');
    }

    public function perencanaan($riskRegister)
    {
        $identifikasiRisiko = IdentifikasiRisiko::where('id',$riskRegister)->first();
        
        $riskAnalysis = RiskAnalysis::where('risiko_id', $riskRegister)->first();
        
        if (!$riskAnalysis || !$riskAnalysis->skala_probabilitas_id || !$riskAnalysis->skala_dampak || !$riskAnalysis->nilai_probabilitas || !$riskAnalysis->skala_risiko || !$riskAnalysis->level_risiko) {
            return redirect()->route('risk-register.index')->with('error', 'Harap Lakukan Analisa Risiko Terlebih Dahulu!');
        }

        $penyebabRisiko = PenyebabRisiko::where('risiko_id',$riskRegister)->get();
        $rencana = RencanaPerlakuanRisiko::where('risiko_id',$riskRegister)->first();
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

        return view('risk-officer.risk-register.perencanaan',compact('riskRegister','rencana','penyebabRisiko','identifikasiRisiko','level_risiko','nilai_risiko','riskAnalysis'));
    }

    public function getSkalaProbabilitasQ1(Request $request)
    {
        // Ambil nilai probabilitas dari request
        $nilaiProbabilitasQ1 = $request->input('target_nilai_probabilitas_q1');

        // Cari skala probabilitas berdasarkan nilai yang dimasukkan
        $skalaProbabilitasQ1 = SkalaProbabilitas::where('type_risiko', $request->input('type_risiko'))
            ->where('min', '<=', $nilaiProbabilitasQ1)
            ->where('max', '>=', $nilaiProbabilitasQ1)
            ->first();

        // Kembalikan data skala probabilitas dalam format JSON
        return response()->json($skalaProbabilitasQ1);
    }

    public function getSkalaProbabilitasQ2(Request $request)
    {
        // Ambil nilai probabilitas dari request
        $nilaiProbabilitasQ2 = $request->input('target_nilai_probabilitas_q2');

        // Cari skala probabilitas berdasarkan nilai yang dimasukkan
        $skalaProbabilitasQ2 = SkalaProbabilitas::where('type_risiko', $request->input('type_risiko'))
            ->where('min', '<=', $nilaiProbabilitasQ2)
            ->where('max', '>=', $nilaiProbabilitasQ2)
            ->first();

        // Kembalikan data skala probabilitas dalam format JSON
        return response()->json($skalaProbabilitasQ2);
    }

    public function getSkalaProbabilitasQ3(Request $request)
    {
        // Ambil nilai probabilitas dari request
        $nilaiProbabilitasQ3 = $request->input('target_nilai_probabilitas_q3');

        // Cari skala probabilitas berdasarkan nilai yang dimasukkan
        $skalaProbabilitasQ3 = SkalaProbabilitas::where('type_risiko', $request->input('type_risiko'))
            ->where('min', '<=', $nilaiProbabilitasQ3)
            ->where('max', '>=', $nilaiProbabilitasQ3)
            ->first();

        // Kembalikan data skala probabilitas dalam format JSON
        return response()->json($skalaProbabilitasQ3);
    }

    public function getSkalaProbabilitasQ4(Request $request)
    {
        // Ambil nilai probabilitas dari request
        $nilaiProbabilitasQ4 = $request->input('target_nilai_probabilitas_q4');

        // Cari skala probabilitas berdasarkan nilai yang dimasukkan
        $skalaProbabilitasQ4 = SkalaProbabilitas::where('type_risiko', $request->input('type_risiko'))
            ->where('min', '<=', $nilaiProbabilitasQ4)
            ->where('max', '>=', $nilaiProbabilitasQ4)
            ->first();

        // Kembalikan data skala probabilitas dalam format JSON
        return response()->json($skalaProbabilitasQ4);
    }

    public function editPerlakuanRisiko($id)
    {
        $risiko = PenyebabRisiko::findOrFail($id);
        // dd($risiko);
        return response()->json($risiko);
    }

    public function updatePerlakuanRisiko(Request $request)
    {
        
        try {
            $id = $request->input('id');
            $risiko = PenyebabRisiko::findOrFail($id);
            //$biaya_perlakuan_risiko = preg_replace('/\D/', '', $request->biaya_perlakuan_risiko);
            $biaya_perlakuan_risiko = $request->biaya_perlakuan_risiko;
            $risiko->rencana_perlakuan_risiko = $request->rencana_perlakuan_risiko;
            $risiko->output_perlakuan_risiko = $request->output_perlakuan_risiko;
            $risiko->biaya_perlakuan_risiko = $biaya_perlakuan_risiko;
            $risiko->pic = $request->pic;
            $risiko->save();
            return response()->json(['status' => 200]);
        } catch (\Exception $e) {
            // If an error occurs, return an error message to be displayed
            return response()->json(['error' => $e->getMessage(), 'status' => 500], 500);
        }
    }

    public function updatePerencanaan(Request $request, $riskRegister)
    {
        $rencana = RencanaPerlakuanRisiko::where('risiko_id',$riskRegister)->first();
        $target_nilai_dampak_q1 = preg_replace('/\D/', '', $request->target_nilai_dampak_q1);
        $target_nilai_dampak_q2 = preg_replace('/\D/', '', $request->target_nilai_dampak_q2);
        $target_nilai_dampak_q3 = preg_replace('/\D/', '', $request->target_nilai_dampak_q3);
        $target_nilai_dampak_q4 = preg_replace('/\D/', '', $request->target_nilai_dampak_q4);

        //get data inherent :
        $risiko = IdentifikasiRisiko::with('riskAnalysis')->where('id', $riskRegister)->first();

        $nilai_dampak = $risiko->riskAnalysis->nilai_dampak;
        $skala_dampak = $risiko->riskAnalysis->skala_dampak;
        $nilai_probabilitas = $risiko->riskAnalysis->nilai_probabilitas;
        $skala_probabilitas = $risiko->riskAnalysis->skala_probabilitas_id;
        $skala_risiko = $risiko->riskAnalysis->skala_risiko;
        $level_risiko = $risiko->riskAnalysis->level_risiko;

        //$nilai_dampak = $risiko

        $rencana->update([
            'opsi_perlakuan_risiko' => $request->opsi_perlakuan_risiko,
            'target_nilai_dampak_q1' => $target_nilai_dampak_q1 ?? $nilai_dampak,
            'target_skala_dampak_q1' => $request->target_skala_dampak_q1 ??$skala_dampak,
            'target_nilai_probabilitas_q1' => $request->target_nilai_probabilitas_q1 ?? $nilai_probabilitas,
            'target_skala_probabilitas_q1' => $request->target_skala_probabilitas_q1 ?? $skala_probabilitas,
            'target_skala_risiko_q1' => $request->target_skala_risiko_q1 ?? $skala_risiko,
            'target_level_risiko_q1' => $request->target_level_risiko_q1 ?? $level_risiko,
            /*
            'target_nilai_dampak_q2' => $target_nilai_dampak_q2,
            'target_skala_dampak_q2' => $request->target_skala_dampak_q2,
            'target_nilai_probabilitas_q2' => $request->target_nilai_probabilitas_q2,
            'target_skala_probabilitas_q2' => $request->target_skala_probabilitas_q2,
            'target_skala_risiko_q2' => $request->target_skala_risiko_q2,
            'target_level_risiko_q2' => $request->target_level_risiko_q2,
            'target_nilai_dampak_q3' => $target_nilai_dampak_q3,
            'target_skala_dampak_q3' => $request->target_skala_dampak_q3,
            'target_nilai_probabilitas_q3' => $request->target_nilai_probabilitas_q3,
            'target_skala_probabilitas_q3' => $request->target_skala_probabilitas_q3,
            'target_skala_risiko_q3' => $request->target_skala_risiko_q3,
            'target_level_risiko_q3' => $request->target_level_risiko_q3,
            'target_nilai_dampak_q4' => $target_nilai_dampak_q4,
            'target_skala_dampak_q4' => $request->target_skala_dampak_q4,
            'target_nilai_probabilitas_q4' => $request->target_nilai_probabilitas_q4,
            'target_skala_probabilitas_q4' => $request->target_skala_probabilitas_q4,
            'target_skala_risiko_q4' => $request->target_skala_risiko_q4,
            'target_level_risiko_q4' => $request->target_level_risiko_q4,
            */
            // Kuartal 2
            'target_nilai_dampak_q2' => $target_nilai_dampak_q2 ??  $target_nilai_dampak_q1 ??$nilai_dampak,
            'target_skala_dampak_q2' => $request->target_skala_dampak_q2 ?? $request->target_skala_dampak_q1 ?? $skala_dampak,
            'target_nilai_probabilitas_q2' => $request->target_nilai_probabilitas_q2 ?? $request->target_nilai_probabilitas_q1 ?? $nilai_probabilitas,
            'target_skala_probabilitas_q2' => $request->target_skala_probabilitas_q2 ?? $request->target_skala_probabilitas_q1 ?? $skala_probabilitas,
            'target_skala_risiko_q2' => $request->target_skala_risiko_q2 ?? $request->target_skala_risiko_q1 ?? $skala_risiko,
            'target_level_risiko_q2' => $request->target_level_risiko_q2 ?? $request->target_level_risiko_q1 ?? $level_risiko,

            // Kuartal 3
            'target_nilai_dampak_q3' => $target_nilai_dampak_q3 ?? $target_nilai_dampak_q2 ?? $target_nilai_dampak_q1 ?? $nilai_dampak,
            'target_skala_dampak_q3' => $request->target_skala_dampak_q3 ?? $request->target_skala_dampak_q2 ?? $request->target_skala_dampak_q1 ?? $skala_dampak,
            'target_nilai_probabilitas_q3' => $request->target_nilai_probabilitas_q3 ?? $request->target_nilai_probabilitas_q2 ?? $request->target_nilai_probabilitas_q1 ?? $nilai_probabilitas,
            'target_skala_probabilitas_q3' => $request->target_skala_probabilitas_q3 ?? $request->target_skala_probabilitas_q2 ?? $request->target_skala_probabilitas_q1 ?? $skala_probabilitas,
            'target_skala_risiko_q3' => $request->target_skala_risiko_q3 ?? $request->target_skala_risiko_q2 ?? $request->target_skala_risiko_q1 ?? $skala_risiko,
            'target_level_risiko_q3' => $request->target_level_risiko_q3 ?? $request->target_level_risiko_q2 ?? $request->target_level_risiko_q1 ?? $level_risiko,

            // Kuartal 4
            'target_nilai_dampak_q4' => $target_nilai_dampak_q4 ?? $target_nilai_dampak_q3 ?? $target_nilai_dampak_q2 ?? $target_nilai_dampak_q1 ?? $nilai_dampak,
            'target_skala_dampak_q4' => $request->target_skala_dampak_q4 ?? $request->target_skala_dampak_q3 ?? $request->target_skala_dampak_q2 ?? $request->target_skala_dampak_q1 ?? $skala_dampak,
            'target_nilai_probabilitas_q4' => $request->target_nilai_probabilitas_q4 ?? $request->target_nilai_probabilitas_q3 ?? $request->target_nilai_probabilitas_q2 ?? $request->target_nilai_probabilitas_q1 ?? $nilai_probabilitas,
            'target_skala_probabilitas_q4' => $request->target_skala_probabilitas_q4 ?? $request->target_skala_probabilitas_q3 ?? $request->target_skala_probabilitas_q2 ?? $request->target_skala_probabilitas_q1 ?? $skala_probabilitas,
            'target_skala_risiko_q4' => $request->target_skala_risiko_q4 ?? $request->target_skala_risiko_q3 ?? $request->target_skala_risiko_q2 ?? $request->target_skala_risiko_q1 ?? $skala_risiko,
            'target_level_risiko_q4' => $request->target_level_risiko_q4 ?? $request->target_level_risiko_q3 ?? $request->target_level_risiko_q2 ?? $request->target_level_risiko_q1 ?? $level_risiko,
            
        ]);

        /*
        if ($request->target_nilai_dampak_q1 !== null) {
            MonitoringRisiko::create([
                'risiko_id' => $riskRegister,
                'rencana_perlakuan_risiko_id' => $rencana->id,
                'periode_monitoring' => 'Quarter 1'
            ]);
        }
        if ($request->target_nilai_dampak_q2 !== null) {
            MonitoringRisiko::create([
                'risiko_id' => $riskRegister,
                'rencana_perlakuan_risiko_id' => $rencana->id,
                'periode_monitoring' => 'Quarter 2'
            ]);
        }
        if ($request->target_nilai_dampak_q3 !== null) {
            MonitoringRisiko::create([
                'risiko_id' => $riskRegister,
                'rencana_perlakuan_risiko_id' => $rencana->id,
                'periode_monitoring' => 'Quarter 3'
            ]);
        }
        if ($request->target_nilai_dampak_q4 !== null) {
            MonitoringRisiko::create([
                'risiko_id' => $riskRegister,
                'rencana_perlakuan_risiko_id' => $rencana->id,
                'periode_monitoring' => 'Quarter 4'
            ]);
        }
        */

        $quarters = [
            'Quarter 1', 'Quarter 2', 'Quarter 3', 'Quarter 4'
        ];

        foreach ($quarters as $quarter) {
            MonitoringRisiko::updateOrCreate(
                [
                    'risiko_id' => $riskRegister,
                    'periode_monitoring' => $quarter
                ],
                [
                    'rencana_perlakuan_risiko_id' => $rencana->id
                ]
            );
        }


        return redirect()->route('risk-register.index')->with('success', 'Update Rencana Perlakuan Risiko successfully!');
    }

    public function checkDeskripsiPeristiwa(Request $request)
    {
        $deskripsi_peristiwa_risiko = $request->deskripsi_peristiwa_risiko;

        $exists = MasterRisiko::where('deskripsi_peristiwa_risiko', $deskripsi_peristiwa_risiko)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function send(Request $request)
    {
        $user = auth()->user();
        $unitId = $user->unit_id;

        $periodeActive = Periode::where('status', 'active')->first();
        // Ambil data identifikasi risiko yang memiliki status_progress 'risk_officer' dan user_id terkait
        $identifikasiRisikos = IdentifikasiRisiko::where('status_progress', 'risk_officer')
            ->where('periode_id', $periodeActive->id)
            ->where('unit_id', $unitId)
            //->where('user_id', $user_id)
            ->get();

        $risikoIds = collect($identifikasiRisikos)->pluck('id')->unique()->toArray();    

        //cek risk analysis
        $riskAnalysesWithNull = RiskAnalysis::whereIn('risiko_id', $risikoIds)
            ->where(function($query) {
                $query->whereNull('level_risiko')
                    ->orWhereNull('skala_risiko');
            })
            ->exists();

        if ($riskAnalysesWithNull) {
            // return response()->json([
            //     'error' => 'Ada risiko yang belum dilakukan kuantifikasi atau analisa secara lengkap',
            // ], 400);
            return redirect()->route('risk-register.index')->with('error', 'Ada risiko yang belum dilakukan kuantifikasi atau analisa secara lengkap');
        }

        $rencanaPerlakuanWithNull = RencanaPerlakuanRisiko::whereIn('risiko_id', $risikoIds)
        ->where(function($query) {
            $query->whereNull('target_skala_risiko_q1')
              ->orWhereNull('target_level_risiko_q1')
              ->orWhereNull('target_skala_risiko_q2')
              ->orWhereNull('target_level_risiko_q2')
              ->orWhereNull('target_skala_risiko_q3')
              ->orWhereNull('target_level_risiko_q3')
              ->orWhereNull('target_skala_risiko_q4')
              ->orWhereNull('target_level_risiko_q4');
        })
        ->exists();

        if ($rencanaPerlakuanWithNull) {
            // return response()->json([
            //     'error' => 'Ada Risiko yang belum dilakukan perencanaan perlakuan secara lengkap.',
            // ], 400);
            return redirect()->route('risk-register.index')->with('error', 'Ada Risiko yang belum dilakukan perencanaan perlakuan secara lengkap');
        }

        // Perbarui status_progress menjadi 'risk_champion' untuk risiko yang dipilih
        foreach ($identifikasiRisikos as $identifikasiRisiko) {
            $identifikasiRisiko->update([
                'status_progress' => 'risk_champion',
                'status' => 2,
                'catatan' => null
            ]);
        }

        // Ambil data yang telah diperbarui
        $updatedData = IdentifikasiRisiko::whereIn('status_progress', ['risk_champion'])
            ->where('periode_id', $periodeActive->id)
            ->where('unit_id', $unitId)
            //->where('user_id', $user_id)
            ->get();


        //update databranch set status kirim
        $dtBatch = DataBatch::where('periode_id', $periodeActive->id)->where('unit_id', $unitId)->where('finish', false)->first();

        $next_batch = 1;
        if(!$dtBatch){
            $last_batch = DataBatch::where('unit_id', $unitId)
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
        
        //dd($last_batch);

        $dataBatch = DataBatch::updateOrCreate(
            [
                'unit_id' => Auth::user()->unit_id,
                'periode_id' => $periodeActive->id,
                'finish' => false,
            ],
            [
                'status' => DataBatch::STATUS_KIRIM,
                'batch' => $next_batch
            ]
        );

        // Redirect kembali ke halaman risiko dengan pesan sukses
        return redirect()->route('risk-register.index')->with('success', 'Kirim Risiko ke Risk Champion successfully!');
    }

    public function getSkalaDampak(Request $request)
    {
        $nilaiDampak = $request->input('nilai_dampak');
        $jenisRisiko = $request->input('jenis_risiko');
        $unitId = $request->input('unit_id');
        $periodeId = $request->input('periode_id');

        // Cari Strategi Risiko berdasarkan unit_id dan periode_id
        $strategiRisiko = StrategiRisiko::where('unit_id', $unitId)
            ->where('periode_id', $periodeId)
            ->first();

        if (!$strategiRisiko) {
            // Kembalikan respons jika tidak ada strategi risiko yang ditemukan
            return response()->json(['error' => 'Strategi risiko tidak ditemukan.'], 404);
        }

        // Cari Risk Limit berdasarkan strategi_risiko_id dan jenis_risiko_id
        $riskLimit = RiskLimit::where('strategi_risiko_id', $strategiRisiko->id)
            ->where('jenis_risiko_id', $jenisRisiko)
            ->first();

        if (!$riskLimit) {
            // Kembalikan respons jika tidak ada risk limit yang ditemukan
            return response()->json(['error' => 'Risk limit tidak ditemukan.'], 404);
        }

        // Ambil nominal limit dari risk limit
        $nominalLimit = $riskLimit->nominal_limit;

        // Hilangkan pemisah ribuan dari nilai dampak
        $nilaiDampakCleaned = str_replace(['.', ','], '', $nilaiDampak);

        // Ubah nilai dampak menjadi integer untuk perbandingan
        $nilaiDampakNumeric = (int) $nilaiDampakCleaned;

        // Hitung skala dampak berdasarkan nilai dampak dan nominal limit
        $skalaDampak = $this->calculateSkalaDampak($nilaiDampakNumeric, $nominalLimit);

        // Kembalikan skala dampak sebagai respons JSON
        return response()->json(['skala_dampak' => $skalaDampak], 200);
    }

    private function calculateSkalaDampak($nilaiDampak, $nominalLimit)
    {
        // Hitung presentase dari nominal limit
        $persentaseLimit = $nilaiDampak / $nominalLimit * 100;

        if ($persentaseLimit <= 20) {
            return 1;
        } elseif ($persentaseLimit > 20 && $persentaseLimit <= 40) {
            return 2;
        } elseif ($persentaseLimit > 40 && $persentaseLimit <= 60) {
            return 3;
        } elseif ($persentaseLimit > 60 && $persentaseLimit <= 80) {
            return 4;
        } else {
            return 5;
        }
    }
}
