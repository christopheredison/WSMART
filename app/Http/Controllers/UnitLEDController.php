<?php

namespace App\Http\Controllers;

use App\Models\IdentifikasiRisiko;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\LossEvent;
use App\Models\Periode;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KategoriKejadian;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\Jabatan;
use App\Models\PenyebabRisikoUnitLed;
use App\Models\PerlakuanPenyebabRisikoUnitLed;
use App\Models\RiskAnalysis;
use App\Models\PenyebabRisiko;
use App\Models\JenisKontrolEksisting;
use App\Models\KamusRisikoUnit;
use Illuminate\Support\Facades\DB;

class UnitLEDController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param string|null $unitId, $unitId is periode_id
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $unitId = null)
    {
        if ($request->ajax()) {
            $data = LossEvent::with(['kategoriKejadian']);

            $user = $request->user();

            $data->where('unit_id', $user->unit_id);
            
            if ($request->filled('periode_id') && $request->periode_id !== '') {
                $data->where('periode_id', $request->periode_id);
            }
            if ($request->filled('kategori_id') && $request->kategori_id !== '') {
                $data->where('kategori_kejadian_id', $request->kategori_id);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    return view('unit-led._table_action', compact('row'))->render();
                })
                ->editColumn('nama_kejadian', function($row) {
                    return $row->nama_kejadian ?? '-';
                })
                ->editColumn('identifikasi_kejadian', function($row) {
                    return $row->identifikasi_kejadian ?? '-';
                })
                ->editColumn('kategori_kejadian', function($row) {
                    return $row->kategoriKejadian ? $row->kategoriKejadian->kategori_kejadian : '-';
                })
                ->editColumn('nilai_kerugian', function($row) {
                    if ($row->nilai_kerugian_finansial && $row->nilai_kerugian_finansial > 0) {
                        return 'Rp ' . number_format($row->nilai_kerugian_finansial, 0, ',', '.');
                    }
                    return 'Rp 0';
                })
                // ->editColumn('unit_penanggung_jawab', function($row) {
                //     return $row->unit_penanggung_jawab ?? '-';
                // })
                ->rawColumns(['action'])
                ->make(true);
        }
    
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $kategoriKejadians = KategoriKejadian::all();
        $periode = null;
        if ($unitId) {
            $periode = Periode::findOrFail($unitId);
        }
    
        return view('unit-led.index', compact('periodes', 'kategoriKejadians', 'periode'));
    }

    public function create($periode)
    {
        $periodes = Periode::orderBy('tahun', 'desc')->with('identifikasiRisikos.penyebabRisikos.perlakuanPenyebabRisiko')->get();
        $kategoriKejadians = KategoriKejadian::all();
        $jenisRisikos = JenisRisiko::with('kategoriRisiko')->get();
        $jabatans = Jabatan::all();
        $periode = null;
        $identifikasiRisikos = [];
        $user = request()->user();
        $unitId = $user->unit_id;
        if (request()->periode) {
            $periode = Periode::findOrFail(request()->periode);
        }
        
        return view('unit-led.create', compact('periodes', 'kategoriKejadians', 'jenisRisikos', 'jabatans', 'periode', 'unitId'));
    }

    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'periode_id' => 'required',
    //         'nama_kejadian' => 'required',
    //         'tanggal_kejadian' => 'required|date',
    //         'identifikasi_kejadian' => 'required',
    //         'kategori_kejadian_id' => 'required',
    //         'sumber_penyebab_kejadian' => 'required|in:1,2',
    //         'penyebab_masalah' => 'required',
    //         'penanganan_kejadian' => 'required',
    //         'deskripsi_kejadian' => 'required',
    //         'kategori_risiko_bumn' => 'required|in:1,2,3',
    //         'kategori_risiko_id' => 'required',
    //         'jenis_risiko_id' => 'required',
    //         'penjelasan_kerugian' => 'required',
    //         'nilai_kerugian_finansial' => 'nullable|numeric',
    //         'kejadian_berulang' => 'required|in:0,1',
    //         'frekuensi_kejadian' => 'required_if:kejadian_berulang,1|nullable|in:1,2,3,4,5,6',
    //         'rencana_mitigasi' => 'required',
    //         'realisasi_mitigasi' => 'required',
    //         'perbaikan_mendatang' => 'required',
    //         'unit_penanggung_jawab' => 'required',
    //         'status_asuransi' => 'required|in:0,1',
    //         'nilai_premi' => 'required_if:status_asuransi,1|nullable|numeric',
    //         'nilai_klaim' => 'required_if:status_asuransi,1|nullable|numeric',
    //         'status_risk_register' => 'required|in:0,1',
    //         'no_urut_risiko' => 'required_if:status_risk_register,1|nullable',
    //         'biaya_risiko_inheren' => 'nullable|numeric',
    //         'biaya_upaya_perbaikan' => 'nullable|numeric',
    //         'hasil_perbaikan' => 'nullable|numeric',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()
    //             ->back()
    //             ->withErrors($validator)
    //             ->withInput();
    //     }

    //     try {
    //         $data = $request->all();
    //         $data['tahun'] = Carbon::parse($request->tanggal_kejadian)->format('Y');
    //         $data['unit_id'] = Auth::user()->unit_id; // Mengambil unit_id dari user yang login
            
    //         // Set default values for numeric fields
    //         $data['nilai_kerugian_finansial'] = $request->nilai_kerugian_finansial ?: 0;
    //         $data['nilai_premi'] = $request->nilai_premi ?: 0;
    //         $data['nilai_klaim'] = $request->nilai_klaim ?: 0;
    //         $data['biaya_risiko_inheren'] = $request->biaya_risiko_inheren ?: 0;
    //         $data['biaya_upaya_perbaikan'] = $request->biaya_upaya_perbaikan ?: 0;
    //         $data['hasil_perbaikan'] = $request->hasil_perbaikan ?: 0;

    //         $data['unit_penanggung_jawab_jabatan_id'] = $request->unit_penanggung_jawab;
        
    //         // Ambil nama jabatan berdasarkan ID
    //         $jabatan = Jabatan::find($request->unit_penanggung_jawab);
    //         if ($jabatan) {
    //             $data['unit_penanggung_jawab'] = $jabatan->name;
    //         }
    //         else {
    //             $data['unit_penanggung_jawab'] = '-';
    //         }
            
    //         LossEvent::create($data);

    //         return redirect()
    //             ->route('unit-led.index')
    //             ->with('success', 'Data Loss Event Unit berhasil ditambahkan');
    //     } catch (\Exception $e) {
    //         return redirect()
    //             ->back()
    //             ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
    //             ->withInput();
    //     }
    // }

    public function store(Request $request)
    {
        $request->merge([
            'nilai_kerugian_finansial' => $this->cleanRupiah($request->nilai_kerugian_finansial),
            'nilai_premi' => $this->cleanRupiah($request->nilai_premi),
            'nilai_klaim' => $this->cleanRupiah($request->nilai_klaim),
        ]);

        $validator = Validator::make($request->all(), [
            'periode_id' => 'required',
            'nama_kejadian' => 'required|string',
            'identifikasi_kejadian' => 'required',
            'tanggal_kejadian' => 'required|date',
            'kategori_kejadian_id' => 'required|exists:kategori_kejadians,id',
            'sumber_penyebab_kejadian' => 'required|in:1,2',
            'kategori_risiko_bumn' => 'required|in:1,2,3',
            'jenis_risiko_id' => 'required|exists:jenis_risikos,id',
            'kategori_risiko_id' => 'required|exists:kategori_risikos,id',
            'penjelasan_kerugian' => 'required|string',
            'nilai_kerugian_finansial' => 'nullable|numeric|min:0',
            'kejadian_berulang' => 'required|in:0,1',
            'frekuensi_kejadian' => 'required_if:kejadian_berulang,1|nullable|in:1,2,3,4,5,6',
            'status_asuransi' => 'required|in:0,1',
            'nilai_premi' => 'nullable|numeric|min:0',
            'nilai_klaim' => 'nullable|numeric|min:0',
            'penyebab_data' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $led = LossEvent::create([
                'periode_id' => $request->periode_id,
                'nama_kejadian' => $request->nama_kejadian,
                'identifikasi_kejadian' => $request->identifikasi_kejadian,
                'tanggal_kejadian' => $request->tanggal_kejadian,
                'tahun' => Carbon::parse($request->tanggal_kejadian)->format('Y'),
                'kategori_kejadian_id' => $request->kategori_kejadian_id,
                'sumber_penyebab_kejadian' => $request->sumber_penyebab_kejadian,
                'kategori_risiko_bumn' => $request->kategori_risiko_bumn,
                'jenis_risiko_id' => $request->jenis_risiko_id,
                'kategori_risiko_id' => $request->kategori_risiko_id,
                'penjelasan_kerugian' => $request->penjelasan_kerugian,
                'nilai_kerugian_finansial' => $request->nilai_kerugian_finansial ?? 0,
                'kejadian_berulang' => $request->kejadian_berulang,
                'frekuensi_kejadian' => $request->kejadian_berulang == '1' ? $request->frekuensi_kejadian : null,
                'status_asuransi' => $request->status_asuransi,
                'nilai_premi' => $request->status_asuransi == '1' ? ($request->nilai_premi ?? 0) : 0,
                'nilai_klaim' => $request->status_asuransi == '1' ? ($request->nilai_klaim ?? 0) : 0,
                'version' => 1,
                'unit_id' => $user->unit_id,
            ]);

            // Simpan data penyebab dan perlakuan untuk LED
            $penyebabData = json_decode($request->input('penyebab_data'), true);
            if (is_array($penyebabData)) {
                foreach ($penyebabData as $penyebab) {
                    $ledPenyebab = PenyebabRisikoUnitLed::create([
                        'loss_event_unit_id' => $led->id,
                        'penyebab_risiko' => $penyebab['penyebab_risiko'],
                    ]);

                    if (!empty($penyebab['perlakuan']) && is_array($penyebab['perlakuan'])) {
                        foreach ($penyebab['perlakuan'] as $perlakuan) {
                            $jabatan = Jabatan::find($perlakuan['pic']);
                            $ledPenyebab->perlakuanPenyebabRisiko()->create([
                                'penyebab_risiko_led_id' => $led->id,
                                'rencana_perlakuan_risiko' => $perlakuan['rencana_perlakuan_risiko'],
                                'output_perlakuan_risiko' => $perlakuan['output_perlakuan_risiko'],
                                'biaya_perlakuan_risiko' => $this->cleanRupiah($perlakuan['biaya_perlakuan_risiko']),
                                'pic' => $jabatan ? $jabatan->name : '',
                                'pic_jabatan_id' => $jabatan ? $jabatan->id : null,
                                'timeline_perlakuan_risiko_start' => Carbon::createFromFormat('d/m/Y', $perlakuan['timeline_mulai_perlakuan_risiko'])->format('Y-m-d'),
                                'timeline_perlakuan_risiko_end' => Carbon::createFromFormat('d/m/Y', $perlakuan['timeline_selesai_perlakuan_risiko'])->format('Y-m-d'),
                                'opsi_perlakuan_risiko' => $perlakuan['opsi_perlakuan_risiko'],
                                // 'jenis_rencana_perlakuan_risiko' => $perlakuan['jenis_rencana_perlakuan_risiko'],
                            ]);
                        }
                    }
                }
            }
            
            // 2. JIKA USER MEMILIH "YA", BUAT UNIT RISK BARU
            if ($request->input('create_risk_from_led') == '1') {
                // Buat Unit Risk
                $newUnitRisk = IdentifikasiRisiko::create([
                    'unit_type_id' => $user->unit_type_id,
                    'unit_id' => $user->unit_id,
                    'periode_id' => $request->periode_id,
                    'user_id' => $user->id,
                    'peristiwa_risiko' => $request->identifikasi_kejadian,
                    'deskripsi_peristiwa_risiko' => $request->nama_kejadian,
                    'jenis_risiko_id' => $request->jenis_risiko_id,
                    'kategori_risiko_id' => $request->kategori_risiko_id,
                    'perkiraan_waktu_terpapar_risiko_mulai' => $request->tanggal_kejadian,
                    'perkiraan_waktu_terpapar_risiko_akhir' => $request->tanggal_kejadian,
                ]);

                // Buat riskAnalysis
                $kategoriDampak = ($request->nilai_kerugian_finansial > 0) ? 'Kuantitatif' : 'Kualitatif';
                $newUnitRisk->riskAnalysis()->create([
                    'kategori_dampak' => $kategoriDampak,
                    'deskripsi_dampak' => ($kategoriDampak == 'Kualitatif') ? $request->penjelasan_kerugian : null,
                    'asumsi_perhitungan_dampak' => ($kategoriDampak == 'Kuantitatif') ? $request->penjelasan_kerugian : null,
                    'nilai_dampak' => $request->nilai_kerugian_finansial ?? 0,
                ]);

                // Simpan data penyebab dan perlakuan untuk UnitRisk
                if (is_array($penyebabData)) {
                    foreach ($penyebabData as $penyebab) {
                        $riskPenyebab = $newUnitRisk->penyebabRisikos()->create([
                            'penyebab_risiko' => $penyebab['penyebab_risiko'],
                        ]);
                        if (!empty($penyebab['perlakuan']) && is_array($penyebab['perlakuan'])) {
                            foreach ($penyebab['perlakuan'] as $perlakuan) {
                                $jabatan = Jabatan::find($perlakuan['pic']);

                                $riskPenyebab->perlakuanPenyebabRisiko()->create([
                                    'penyebab_risiko_id' => $riskPenyebab->id,
                                    'rencana_perlakuan_risiko' => $perlakuan['rencana_perlakuan_risiko'],
                                    'output_perlakuan_risiko' => $perlakuan['output_perlakuan_risiko'],
                                    'biaya_perlakuan_risiko' => $this->cleanRupiah($perlakuan['biaya_perlakuan_risiko']),
                                    'pic' => $jabatan ? $jabatan->name : '',
                                    'pic_jabatan_id' => $jabatan ? $jabatan->id : null,
                                    'timeline_perlakuan_risiko_start' => Carbon::createFromFormat('d/m/Y', $perlakuan['timeline_mulai_perlakuan_risiko'])->format('Y-m-d'),
                                    'timeline_perlakuan_risiko_end' => Carbon::createFromFormat('d/m/Y', $perlakuan['timeline_selesai_perlakuan_risiko'])->format('Y-m-d'),
                                    'opsi_perlakuan_risiko' => $perlakuan['opsi_perlakuan_risiko'],
                                    // 'jenis_rencana_perlakuan_risiko' => $perlakuan['jenis_rencana_perlakuan_risiko'],
                                ]);
                            }
                        }
                    }
                }

                DB::commit();
                return redirect()->route('risk-register-unit.edit', ['riskRegister' => $newUnitRisk->id])
                    ->with('success', 'Loss Event berhasil dibuat dan Divisi Risk baru telah ditambahkan.');
            }

            // Jika user memilih "Tidak", cukup simpan LED
            DB::commit();
            return redirect()->route('unit-led.index-by-periode', ['periode' => $request->periode_id])
                ->with('success', 'Data Loss Event Divisi berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($periode, $id)
    {
        $lossEvent = LossEvent::with('penyebabRisikoLeds.perlakuanPenyebabRisiko')->findOrFail($id);
        $periodes = Periode::orderBy('tahun', 'desc')->with('identifikasiRisikos.penyebabRisikos.perlakuanPenyebabRisiko')->get();
        $kategoriKejadians = KategoriKejadian::all();
        $jenisRisikos = JenisRisiko::with('kategoriRisiko')->get();
        $jabatans = Jabatan::all();
        $periode = $lossEvent->periode;
        $analisa = null;

        $penyebabData = $lossEvent->penyebabRisikoLeds->map(function ($penyebab) {
            return [
                'id' => $penyebab->id,
                'penyebab_risiko' => $penyebab->penyebab_risiko,
                'perlakuan' => $penyebab->perlakuanPenyebabRisiko->map(function ($perlakuan) {
                    return [
                        'id' => $perlakuan->id,
                        'rencana_perlakuan_risiko' => $perlakuan->rencana_perlakuan_risiko,
                        'output_perlakuan_risiko' => $perlakuan->output_perlakuan_risiko,
                        'biaya_perlakuan_risiko' => $perlakuan->biaya_perlakuan_risiko,
                        'pic' => $perlakuan->pic_jabatan_id,
                        'pic_name' => $perlakuan->pic,
                        'timeline_mulai_perlakuan_risiko' => $perlakuan->timeline_perlakuan_risiko_start ? Carbon::parse($perlakuan->timeline_perlakuan_risiko_start)->format('d/m/Y') : '',
                        'timeline_selesai_perlakuan_risiko' => $perlakuan->timeline_perlakuan_risiko_end ? Carbon::parse($perlakuan->timeline_perlakuan_risiko_end)->format('d/m/Y') : '',
                        'opsi_perlakuan_risiko' => $perlakuan->opsi_perlakuan_risiko,
                        // 'jenis_rencana_perlakuan_risiko' => $perlakuan->jenis_rencana_perlakuan_risiko,
                    ];
                })
            ];
        });

        return view('unit-led.edit', compact(
          'lossEvent',
          'periodes',
          'kategoriKejadians',
          'jenisRisikos',
          'jabatans',
          'periode',
          'analisa',
          'penyebabData',
        ));
    }

    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'periode_id' => 'required',
    //         'nama_kejadian' => 'required',
    //         'tanggal_kejadian' => 'required|date',
    //         'identifikasi_kejadian' => 'required',
    //         'kategori_kejadian_id' => 'required',
    //         'sumber_penyebab_kejadian' => 'required|in:1,2',
    //         'penyebab_masalah' => 'required',
    //         'penanganan_kejadian' => 'required',
    //         'deskripsi_kejadian' => 'required',
    //         'kategori_risiko_bumn' => 'required|in:1,2,3',
    //         'kategori_risiko_id' => 'required',
    //         'jenis_risiko_id' => 'required',
    //         'penjelasan_kerugian' => 'required',
    //         'nilai_kerugian_finansial' => 'nullable|numeric',
    //         'kejadian_berulang' => 'required|in:0,1',
    //         'frekuensi_kejadian' => 'required_if:kejadian_berulang,1|nullable|in:1,2,3,4,5,6',
    //         'rencana_mitigasi' => 'required',
    //         'realisasi_mitigasi' => 'required',
    //         'perbaikan_mendatang' => 'required',
    //         'unit_penanggung_jawab' => 'required',
    //         'status_asuransi' => 'required|in:0,1',
    //         'nilai_premi' => 'required_if:status_asuransi,1|nullable|numeric',
    //         'nilai_klaim' => 'required_if:status_asuransi,1|nullable|numeric',
    //         'status_risk_register' => 'required|in:0,1',
    //         'no_urut_risiko' => 'required_if:status_risk_register,1|nullable',
    //         'biaya_risiko_inheren' => 'nullable|numeric',
    //         'biaya_upaya_perbaikan' => 'nullable|numeric',
    //         'hasil_perbaikan' => 'nullable|numeric',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()
    //             ->back()
    //             ->withErrors($validator)
    //             ->withInput();
    //     }

    //     try {
    //         $lossEvent = LossEvent::findOrFail($id);
    //         $data = $request->all();
    //         $data['tahun'] = Carbon::parse($request->tanggal_kejadian)->format('Y');
            
    //         // Set default values for numeric fields
    //         $data['nilai_kerugian_finansial'] = $request->nilai_kerugian_finansial ?: 0;
    //         $data['nilai_premi'] = $request->nilai_premi ?: 0;
    //         $data['nilai_klaim'] = $request->nilai_klaim ?: 0;
    //         $data['biaya_risiko_inheren'] = $request->biaya_risiko_inheren ?: 0;
    //         $data['biaya_upaya_perbaikan'] = $request->biaya_upaya_perbaikan ?: 0;
    //         $data['hasil_perbaikan'] = $request->hasil_perbaikan ?: 0;

    //         $data['unit_penanggung_jawab_jabatan_id'] = $request->unit_penanggung_jawab;
        
    //         // Ambil nama jabatan berdasarkan ID
    //         $jabatan = Jabatan::find($request->unit_penanggung_jawab);
    //         if ($jabatan) {
    //             $data['unit_penanggung_jawab'] = $jabatan->name;
    //         }
    //         else {
    //             $data['unit_penanggung_jawab'] = '-';
    //         }
            
    //         $lossEvent->update($data);

    //         return redirect()
    //             ->route('unit-led.index')
    //             ->with('success', 'Data Loss Event Unit berhasil diperbarui');
    //     } catch (\Exception $e) {
    //         return redirect()
    //             ->back()
    //             ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())
    //             ->withInput();
    //     }
    // }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $lossEvent = LossEvent::findOrFail($id);

        $request->merge([
            'nilai_kerugian_finansial' => $this->cleanRupiah($request->nilai_kerugian_finansial),
            'nilai_premi' => $this->cleanRupiah($request->nilai_premi),
            'nilai_klaim' => $this->cleanRupiah($request->nilai_klaim),
        ]);
        
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required',
            'nama_kejadian' => 'required|string|max:255',
            'identifikasi_kejadian' => 'required|string',
            'tanggal_kejadian' => 'required|date',
            'kategori_kejadian_id' => 'required|exists:kategori_kejadians,id',
            'sumber_penyebab_kejadian' => 'required|in:1,2',
            'kategori_risiko_bumn' => 'required|in:1,2,3',
            'jenis_risiko_id' => 'required|exists:jenis_risikos,id',
            'kategori_risiko_id' => 'required|exists:kategori_risikos,id',
            'penjelasan_kerugian' => 'required|string',
            'nilai_kerugian_finansial' => 'nullable|numeric|min:0',
            'kejadian_berulang' => 'required|in:0,1',
            'frekuensi_kejadian' => 'required_if:kejadian_berulang,1|nullable|in:1,2,3,4,5,6',
            'status_asuransi' => 'required|in:0,1',
            'nilai_premi' => 'required_if:status_asuransi,1|nullable|numeric|min:0',
            'nilai_klaim' => 'required_if:status_asuransi,1|nullable|numeric|min:0',
            'penyebab_data' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        DB::beginTransaction();
        try {
            $lossEvent->update($request->except(['_token', '_method', 'penyebab_data', 'create_risk_from_led']));

            $penyebabDataFromRequest = json_decode($request->input('penyebab_data'), true) ?? [];
            $existingPenyebabIds = $lossEvent->penyebabRisikoLeds()->pluck('id')->toArray();
            $requestPenyebabIds = [];

            // dd($penyebabDataFromRequest);
            foreach ($penyebabDataFromRequest as $penyebabItem) {
                $isNewPenyebab = !isset($penyebabItem['id']) || str_starts_with($penyebabItem['id'], 'temp_');
                
                $penyebabData = [
                    'loss_event_unit_id' => $lossEvent->id,
                    'penyebab_risiko' => $penyebabItem['penyebab_risiko'],
                ];

                if ($isNewPenyebab) {
                    $penyebabRecord = PenyebabRisikoUnitLed::create($penyebabData);
                } else {
                    $penyebabRecord = PenyebabRisikoUnitLed::find($penyebabItem['id']);
                    if ($penyebabRecord) {
                        $penyebabRecord->update($penyebabData);
                    }
                }

                if ($penyebabRecord) {
                    $requestPenyebabIds[] = $penyebabRecord->id;

                    if (!empty($penyebabItem['perlakuan']) && is_array($penyebabItem['perlakuan'])) {
                        $existingPerlakuanIds = $penyebabRecord->perlakuanPenyebabRisiko()->pluck('id')->toArray();
                        $requestPerlakuanIds = [];
                        foreach ($penyebabItem['perlakuan'] as $perlakuanItem) {
                            $isNewPerlakuan = !isset($perlakuanItem['id']) || str_starts_with($perlakuanItem['id'], 'temp_p_');
                            $jabatan = Jabatan::find($perlakuanItem['pic']);
                            $startDate = !empty($perlakuanItem['timeline_mulai_perlakuan_risiko']) ? Carbon::createFromFormat('d/m/Y', $perlakuanItem['timeline_mulai_perlakuan_risiko'])->format('Y-m-d') : null;
                            $endDate = !empty($perlakuanItem['timeline_selesai_perlakuan_risiko']) ? Carbon::createFromFormat('d/m/Y', $perlakuanItem['timeline_selesai_perlakuan_risiko'])->format('Y-m-d') : null;

                            $perlakuanData = [
                                'rencana_perlakuan_risiko' => $perlakuanItem['rencana_perlakuan_risiko'],
                                'output_perlakuan_risiko' => $perlakuanItem['output_perlakuan_risiko'],
                                'biaya_perlakuan_risiko' => $this->cleanRupiah($perlakuanItem['biaya_perlakuan_risiko']),
                                'pic' => $jabatan ? $jabatan->name : null,
                                'pic_jabatan_id' => $jabatan ? $jabatan->id : null,
                                'timeline_perlakuan_risiko_start' => $startDate,
                                'timeline_perlakuan_risiko_end' => $endDate,
                                'opsi_perlakuan_risiko' => $perlakuanItem['opsi_perlakuan_risiko'],
                                // 'jenis_rencana_perlakuan_risiko' => $perlakuanItem['jenis_rencana_perlakuan_risiko'],
                            ];

                            if ($isNewPerlakuan) {
                                $perlakuanRecord = $penyebabRecord->perlakuanPenyebabRisiko()->create($perlakuanData);
                            } else {
                                $perlakuanRecord = PerlakuanPenyebabRisikoUnitLed::find($perlakuanItem['id']);
                                if ($perlakuanRecord) {
                                    $perlakuanRecord->update($perlakuanData);
                                }
                            }

                            if ($perlakuanRecord) {
                                $requestPerlakuanIds[] = $perlakuanRecord->id;
                            }
                        }

                        $perlakuanToDelete = array_diff($existingPerlakuanIds, $requestPerlakuanIds);
                        if (!empty($perlakuanToDelete)) {
                            PerlakuanPenyebabRisikoUnitLed::destroy($perlakuanToDelete);
                        }

                    } else if ($penyebabRecord) {
                        $penyebabRecord->perlakuanPenyebabRisiko()->delete();
                    }
                }
            }
            
            $penyebabToDelete = array_diff($existingPenyebabIds, $requestPenyebabIds);
            if (!empty($penyebabToDelete)) {
              PenyebabRisikoUnitLed::destroy($penyebabToDelete);
            }

            // JIKA USER MEMILIH "YA", BUAT PROJECT RISK BARU
            if ($request->input('create_risk_from_led') == '1') {

                // Buat ProjectRisk baru
                $newUnitRisk = IdentifikasiRisiko::create([
                    'unit_type_id' => $user->unit_type_id,
                    'unit_id' => $user->unit_id,
                    'periode_id' => $request->periode_id,
                    'user_id' => $user->id,
                    'peristiwa_risiko' => $request->identifikasi_kejadian,
                    'deskripsi_peristiwa_risiko' => $request->nama_kejadian,
                    'jenis_risiko_id' => $request->jenis_risiko_id,
                    'kategori_risiko_id' => $request->kategori_risiko_id,
                    'perkiraan_waktu_terpapar_risiko_mulai' => $request->tanggal_kejadian,
                    'perkiraan_waktu_terpapar_risiko_akhir' => $request->tanggal_kejadian,
                ]);

                // Buat riskAnalysis
                $kategoriDampak = ($request->nilai_kerugian_finansial > 0) ? 'Kuantitatif' : 'Kualitatif';
                $newUnitRisk->riskAnalysis()->create([
                    'kategori_dampak' => $kategoriDampak,
                    'deskripsi_dampak' => ($kategoriDampak == 'Kualitatif') ? $request->penjelasan_kerugian : null,
                    'asumsi_perhitungan_dampak' => ($kategoriDampak == 'Kuantitatif') ? $request->penjelasan_kerugian : null,
                    'nilai_dampak' => $request->nilai_kerugian_finansial ?? 0,
                ]);

                // Salin data penyebab dan perlakuan ke ProjectRisk yang baru
                if (is_array($penyebabDataFromRequest)) {
                    foreach ($penyebabDataFromRequest as $penyebab) {
                        $riskPenyebab = $newUnitRisk->penyebabRisiko()->create([
                            'penyebab_risiko' => $penyebab['penyebab_risiko'],
                        ]);
                        if (!empty($penyebab['perlakuan']) && is_array($penyebab['perlakuan'])) {
                            foreach ($penyebab['perlakuan'] as $perlakuan) {
                                $jabatan = Jabatan::find($perlakuan['pic']);
                                $riskPenyebab->perlakuanPenyebabRisiko()->create([
                                    'penyebab_risiko_id' => $riskPenyebab->id,
                                    'rencana_perlakuan_risiko' => $perlakuan['rencana_perlakuan_risiko'],
                                    'output_perlakuan_risiko' => $perlakuan['output_perlakuan_risiko'],
                                    'biaya_perlakuan_risiko' => $this->cleanRupiah($perlakuan['biaya_perlakuan_risiko']),
                                    'pic' => $jabatan ? $jabatan->name : '',
                                    'pic_jabatan_id' => $jabatan ? $jabatan->id : null,
                                    'timeline_perlakuan_risiko_start' => Carbon::createFromFormat('d/m/Y', $perlakuan['timeline_mulai_perlakuan_risiko'])->format('Y-m-d'),
                                    'timeline_perlakuan_risiko_end' => Carbon::createFromFormat('d/m/Y', $perlakuan['timeline_selesai_perlakuan_risiko'])->format('Y-m-d'),
                                    'opsi_perlakuan_risiko' => $perlakuan['opsi_perlakuan_risiko'],
                                    // 'jenis_rencana_perlakuan_risiko' => $perlakuan['jenis_rencana_perlakuan_risiko'],
                                ]);
                            }
                        }
                    }
                }

                DB::commit();
                return redirect()->route('risk-register-unit.edit', ['riskRegister' => $newUnitRisk->id])
                    ->with('success', 'Loss Event berhasil diperbarui dan Divisi Risk baru telah ditambahkan.');
            }

            DB::commit();
            return redirect()->route('unit-led.index-by-periode', ['periode' => $request->periode_id])
                ->with('success', 'Data Loss Event Divisi berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $lossEvent = LossEvent::findOrFail($id);
            $lossEvent->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data Loss Event Unit berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data Loss Event Unit'
            ], 500);
        }
    }

    public function show($periode, $id)
    {
        $lossEvent = LossEvent::with([
          'periode',
          'unit',
          'kategoriKejadian', 
          'kategoriRisiko', 
          'jenisRisiko',
          'penyebabRisikoLeds.perlakuanPenyebabRisiko',
        ])->findOrFail($id);
        
        return view('unit-led.show', compact('lossEvent'));
    }

    public function riskChangeToLed(IdentifikasiRisiko $riskRegister)
    {
        $risiko = $riskRegister->load([
            'jenisRisiko.kategoriRisiko',
            'riskAnalysis',
            'penyebabRisikos.perlakuanPenyebabRisikoUnit'
        ]);

        $penyebabData = $risiko->penyebabRisikos->map(function ($penyebab) {
        return [
                'id' => $penyebab->id,
                'penyebab_risiko' => $penyebab->penyebab_risiko,
                'perlakuan' => $penyebab->perlakuanPenyebabRisikoUnit->map(function ($perlakuan) {
                    return [
                        'id' => $perlakuan->id,
                        'rencana_perlakuan_risiko' => $perlakuan->rencana_perlakuan_risiko,
                        'output_perlakuan_risiko' => $perlakuan->output_perlakuan_risiko,
                        'biaya_perlakuan_risiko' => $perlakuan->biaya_perlakuan_risiko,
                        'pic' => $perlakuan->pic_jabatan_id,
                        'pic_name' => $perlakuan->pic,
                        'timeline_mulai_perlakuan_risiko' => \Carbon\Carbon::parse($perlakuan->timeline_perlakuan_risiko_start)->format('d/m/Y'),
                        'timeline_selesai_perlakuan_risiko' => \Carbon\Carbon::parse($perlakuan->timeline_perlakuan_risiko_end)->format('d/m/Y'),
                        'opsi_perlakuan_risiko' => $perlakuan->opsi_perlakuan_risiko,
                        // 'jenis_rencana_perlakuan_risiko' => $perlakuan->jenis_rencana_perlakuan_risiko,
                    ];
                })
            ];
        });
      
        $kategoriKejadians = KategoriKejadian::all();
        $jenisRisikos = JenisRisiko::with('kategoriRisiko')->get();
        $analisa = $risiko->riskAnalysis;
        $jabatans = Jabatan::all();

        return view('unit-led.change-to-led', compact(
            'risiko',
            'kategoriKejadians',
            'jenisRisikos',
            'analisa',
            'jabatans',
            'penyebabData',
        ));
    }

    public function riskChangeToLedStore(Request $request, IdentifikasiRisiko $riskRegister)
    {
        $request->merge([
          'nilai_kerugian_finansial' => $this->cleanRupiah($request->nilai_kerugian_finansial),
          'nilai_premi' => $this->cleanRupiah($request->nilai_premi),
          'nilai_klaim' => $this->cleanRupiah($request->nilai_klaim),
        ]);

        $validator = Validator::make($request->all(), [
            'nama_kejadian' => 'required|string',
            'identifikasi_kejadian' => 'required|string',
            'tanggal_kejadian' => 'required|date',
            'kategori_kejadian_id' => 'required|exists:kategori_kejadians,id',
            'sumber_penyebab_kejadian' => 'required|in:1,2',
            'kategori_risiko_bumn' => 'required|in:1,2,3',
            'jenis_risiko_id' => 'required|exists:jenis_risikos,id',
            'kategori_risiko_id' => 'required|exists:kategori_risikos,id',
            'penjelasan_kerugian' => 'required|string',
            'nilai_kerugian_finansial' => 'nullable|numeric',
            'kejadian_berulang' => 'required|in:0,1',
            'frekuensi_kejadian' => 'required_if:kejadian_berulang,1|nullable|in:1,2,3,4,5,6',
            'status_asuransi' => 'required|in:0,1',
            'nilai_premi' => 'nullable|numeric',
            'nilai_klaim' => 'nullable|numeric',
            'penyebab_data' => 'required|json'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        try {
            $led = LossEvent::create([
                'unit_id' => $riskRegister->unit_id,
                'periode_id' => $riskRegister->periode_id,
                'nama_kejadian' => $request->nama_kejadian,
                'identifikasi_kejadian' => $request->identifikasi_kejadian,
                'tanggal_kejadian' => $request->tanggal_kejadian,
                'tahun' => Carbon::parse($request->tanggal_kejadian)->format('Y'),
                'kategori_kejadian_id' => $request->kategori_kejadian_id,
                'sumber_penyebab_kejadian' => $request->sumber_penyebab_kejadian,
                'kategori_risiko_bumn' => $request->kategori_risiko_bumn,
                'jenis_risiko_id' => $request->jenis_risiko_id,
                'kategori_risiko_id' => $request->kategori_risiko_id,
                'penjelasan_kerugian' => $request->penjelasan_kerugian,
                'nilai_kerugian_finansial' => $request->nilai_kerugian_finansial ?? 0,
                'kejadian_berulang' => $request->kejadian_berulang,
                'frekuensi_kejadian' => $request->kejadian_berulang == '1' ? $request->frekuensi_kejadian : null,
                'status_asuransi' => $request->status_asuransi,
                'nilai_premi' => $request->status_asuransi == '1' ? ($request->nilai_premi ?? 0) : 0,
                'nilai_klaim' => $request->status_asuransi == '1' ? ($request->nilai_klaim ?? 0) : 0,
                'version' => 1,
            ]);
    
            $penyebabData  = json_decode($request->input('penyebab_data'), true);
            if (is_array($penyebabData)) {
                foreach ($penyebabData as $penyebabItem) {
                    $newLedPenyebab = PenyebabRisikoUnitLed::create([
                        'loss_event_unit_id' => $led->id,
                        'penyebab_risiko' => $penyebabItem['penyebab_risiko'],
                    ]);
    
                    if (!empty($penyebabItem['perlakuan']) && is_array($penyebabItem['perlakuan'])) {
                        foreach ($penyebabItem['perlakuan'] as $perlakuanItem) {
                            $jabatan = Jabatan::find($perlakuanItem['pic']);
    
                            PerlakuanPenyebabRisikoUnitLed::create([
                                'penyebab_risiko_led_id' => $newLedPenyebab->id,
                                'rencana_perlakuan_risiko' => $perlakuanItem['rencana_perlakuan_risiko'],
                                'output_perlakuan_risiko' => $perlakuanItem['output_perlakuan_risiko'],
                                'biaya_perlakuan_risiko' => $this->cleanRupiah($perlakuanItem['biaya_perlakuan_risiko']),
                                'pic' => $jabatan ? $jabatan->name : '',
                                'pic_jabatan_id' => $jabatan ? $jabatan->id : null,
                                'timeline_perlakuan_risiko_start' => Carbon::createFromFormat('d/m/Y', $perlakuanItem['timeline_mulai_perlakuan_risiko'])->format('Y-m-d'),
                                'timeline_perlakuan_risiko_end' => Carbon::createFromFormat('d/m/Y', $perlakuanItem['timeline_selesai_perlakuan_risiko'])->format('Y-m-d'),
                                'opsi_perlakuan_risiko' => $perlakuanItem['opsi_perlakuan_risiko'],
                                // 'jenis_rencana_perlakuan_risiko' => $perlakuanItem['jenis_rencana_perlakuan_risiko'],
                            ]);
                        }
                    }
                }
            }
    
            // Cek apakah risiko perlu di-close
            if ($request->input('is_closed') == '1') {
                $efektivitas = 0;

                $analisa = $riskRegister->riskAnalysis;
                $monitoring = $riskRegister?->lastMonitoringRisiko;
                $quarter = $monitoring?->quarter ?: 1;
                $skala_risiko_inherent = (float) optional($analisa)->skala_risiko;
                $skala_risiko_rencana = (float) optional($analisa)['skala_risiko_residual_q' . $quarter];
                $skala_risiko_realisasi = (float) optional($monitoring)->skala_risiko;

                $selisih_inherent_rencana = $skala_risiko_inherent - $skala_risiko_rencana;

                // Hindari pembagian dengan nol
                if ($selisih_inherent_rencana != 0) {
                    $efektivitas = ($skala_risiko_rencana - $skala_risiko_realisasi) / $selisih_inherent_rencana;
                }

                $riskRegister->update([
                  'is_closed' => true,
                  'efektivitas_perlakuan_risiko' => $efektivitas
                ]);

                KamusRisikoUnit::updateOrCreate(
                    ['risiko_id' => $riskRegister->id],
                );
            }

            DB::commit();

            if ($request->input('create_new_risk') == '1') {
                $penyebabText = "Risiko " . $request->nama_kejadian;
                return redirect()->route(
                    'risk-register-unit.create', 
                    [
                        'penyebab_risiko' => $penyebabText,
                        'pid' => $riskRegister->periode_id
                    ]
                )->with('success', 'Loss Event berhasil dibuat. Silakan tambahkan risiko baru.');
            }

            return redirect()->route('risk-register-unit.monitorings.index', ['period' => $riskRegister->periode_id])
            ->with('success', 'Risiko berhasil diubah menjadi Loss Event.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())->withInput();
        }
    }

    private function cleanRupiah($value) {
      if (is_null($value) || $value === '') {
          return null;
      }
      
      return (float) str_replace(['Rp', '.', ','], ['', '', ''], $value);
    }
}
