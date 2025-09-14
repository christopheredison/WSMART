<?php

namespace App\Http\Controllers;

use App\Models\IdentifikasiRisiko;
use App\Models\Periode;
use App\Models\RekomendasiRisiko;
use App\Models\Unit;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\JenisKontrolEksisting;
use App\Models\PenilaianEfektivitasKontrol;
use App\Models\PenyebabRisiko;
use App\Models\KRI;
use App\Models\KontrolEksisting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekomendasiRisikoController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $units = Unit::where('unit_type_id', 1)->get();

        $selectedPeriodeId = $request->input('periode_id', Periode::where('status', 'active')->value('id'));
        $selectedUnitId = $request->input('unit_id');

        $query = Unit::withCount(['rekomendasiRisikos' => function ($query) use ($selectedPeriodeId) {
            $query->where('periode_id', $selectedPeriodeId);
        }])->where('unit_type_id', 1);

        if ($selectedUnitId) {
            $query->where('id', $selectedUnitId);
        }

        $dataUnits = $query->get();

        $tableLegend = [
            [
                'icon' => '<span class="bx bx-list-check"></span>',
                'label' => 'Rekomendasi Risiko'
            ]
        ];

        return view('rekomendasi-risiko.index', compact('dataUnits', 'periodes', 'units', 'selectedPeriodeId', 'selectedUnitId', 'tableLegend'));
    }

    public function show(Request $request, Unit $unit, Periode $periode)
    {
        $statusFilter = $request->input('status');
        $jenisRisikoFilter = $request->input('jenis_risiko_id'); // Filter baru

        $query = RekomendasiRisiko::where('unit_id', $unit->id)
            ->where('periode_id', $periode->id)
            ->with('unit', 'kategoriRisiko', 'jenisRisiko', 'jenisKontrolEksisting'); // Eager load 'unit'

        // Terapkan filter jika ada
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($jenisRisikoFilter) {
            $query->where('jenis_risiko_id', $jenisRisikoFilter);
        }

        $rekomendasiRisikos = $query->get();

        // Data untuk dropdown filter
        $jenisRisiko = \App\Models\JenisRisiko::pluck('title','id');

        // Keterangan untuk tabel
        $tableLegend = [
            ['icon' => '<span class="bx bx-show-alt"></span>', 'label' => 'View'],
            ['icon' => '<span class="bx bx-message-square-edit"></span>', 'label' => 'Edit'],
            ['icon' => '<span class="bx bx-trash text-danger"></span>', 'label' => 'Delete'],
            ['icon' => '<span class="bx bx-send text-success"></span>', 'label' => 'Publish'],
        ];

        return view('rekomendasi-risiko.show', compact(
            'rekomendasiRisikos', 
            'unit', 
            'periode', 
            'jenisRisiko', 
            'statusFilter',
            'jenisRisikoFilter',
            'tableLegend'
        ));
    }

    public function create(Unit $unit, Periode $periode)
    {
        $kategoriRisiko = KategoriRisiko::pluck('title','id');
        $jenisRisiko = JenisRisiko::pluck('title','id');
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        return view('rekomendasi-risiko.create', compact(
            'unit', 
            'periode', 
            'kategoriRisiko', 
            'jenisRisiko', 
            'jenisKontrolEksistings', 
            'penilaianEfektifitasKontrols'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'unit_id' => 'required|exists:units,id',
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
            'batas_waspada' => 'nullable|array',
            'batas_bahaya' => 'nullable|array',
            'jenis_kontrol_eksisting_id' => 'nullable|exists:jenis_kontrol_eksistings,id',
            'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'required|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'required|date_format:d/m/Y|after_or_equal:perkiraan_waktu_mulai_terpapar_risiko',
        ]);
        
        try {
            $rekomendasi = DB::transaction(function () use ($request) {
                $waktuMulai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_mulai_terpapar_risiko)->format('Y-m-d');
                $waktuSelesai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_selesai_terpapar_risiko)->format('Y-m-d');

                $rekomendasi = new RekomendasiRisiko($request->except(['penyebab_risiko', 'key_risk_indicator', 'kontrol_eksisting']));
                
                $rekomendasi->user_id = auth()->id();
                $rekomendasi->status = RekomendasiRisiko::STATUS_DRAFT;

                $unit = Unit::find($request->unit_id);
                if ($unit) {
                    $rekomendasi->unit_type_id = $unit->unit_type_id;
                } else {
                    $rekomendasi->unit_type_id = 1;
                }
                
                $jenisRisiko = JenisRisiko::find($request->jenis_risiko_id);
                if ($jenisRisiko) {
                    $rekomendasi->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
                }
                
                $rekomendasi->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
                $rekomendasi->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
                
                $rekomendasi->save();

                // Simpan Penyebab Risiko
                foreach ($request->penyebab_risiko as $penyebab) {
                    if (!empty($penyebab)) {
                        $rekomendasi->penyebabRisikos()->create(['penyebab_risiko' => $penyebab]);
                    }
                }

                // Simpan KRI
                if ($request->has('key_risk_indicator')) {
                    foreach ($request->key_risk_indicator as $index => $kri) {
                        if (!empty($kri)) {
                            $rekomendasi->kris()->create([
                                'kri' => $kri,
                                'satuan_kri' => $request->satuan_kri[$index] ?? null,
                                'batas_aman' => $request->batas_aman[$index] ?? null,
                                'batas_waspada' => $request->batas_waspada[$index] ?? null,
                                'batas_bahaya' => $request->batas_bahaya[$index] ?? null,
                            ]);
                        }
                    }
                }

                foreach ($request->kontrol_eksisting as $kontrol) {
                    if (!empty($kontrol)) {
                        $rekomendasi->kontrolEksistings()->create(['kontrol_eksisting' => $kontrol]);
                    }
                }
                
                return $rekomendasi;
            });

            return response()->json([
                'message' => 'Rekomendasi Risiko berhasil disimpan sebagai draft.',
                'redirect' => route('rekomendasi-risiko.show', ['unit' => $rekomendasi->unit_id, 'periode' => $rekomendasi->periode_id])
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function view(RekomendasiRisiko $rekomendasi)
    {
        $rekomendasi->load(
            'unit', 
            'periode', 
            'jenisRisiko.kategoriRisiko', 
            'jenisKontrolEksisting', 
            'penilaianEfektifitasKontrol',
            'penyebabRisikos',
            'kris',
            'kontrolEksistings'
        );

        return view('rekomendasi-risiko.view', compact('rekomendasi'));
    }

    public function edit(RekomendasiRisiko $rekomendasi)
    {
        $rekomendasi->load('penyebabRisikos', 'kris', 'kontrolEksistings');
        
        $jenisRisiko = JenisRisiko::pluck('title','id');
        $jenisKontrolEksistings = JenisKontrolEksisting::get();
        $penilaianEfektifitasKontrols = PenilaianEfektivitasKontrol::get();

        return view('rekomendasi-risiko.edit', [
            'rekomendasi' => $rekomendasi,
            'unit' => $rekomendasi->unit,
            'periode' => $rekomendasi->periode,
            'jenisRisiko' => $jenisRisiko,
            'jenisKontrolEksistings' => $jenisKontrolEksistings,
            'penilaianEfektifitasKontrols' => $penilaianEfektifitasKontrols,
        ]);
    }

    public function update(Request $request, RekomendasiRisiko $rekomendasi)
    {
        $validated = $request->validate([
            'target_capaian_kinerja' => 'required|string',
            'jenis_risiko_id' =>'required|exists:jenis_risikos,id',
            'peristiwa_risiko' => 'required|string',
            'deskripsi_peristiwa_risiko' => 'required|string',
            'penyebab_risiko' => 'required|array',
            'penyebab_risiko.*' => 'required|string',
            'key_risk_indicator' => 'nullable|array',
            'key_risk_indicator.*' => 'nullable|string',
            'jenis_kontrol_eksisting_id' => 'nullable|exists:jenis_kontrol_eksistings,id',
            'kontrol_eksisting' => 'required|array',
            'kontrol_eksisting.*' => 'required|string',
            'penilaian_efektifitas_kontrol' => 'nullable|exists:penilaian_efektivitas_kontrols,id',
            'perkiraan_waktu_mulai_terpapar_risiko' => 'required|date_format:d/m/Y',
            'perkiraan_waktu_selesai_terpapar_risiko' => 'required|date_format:d/m/Y|after_or_equal:perkiraan_waktu_mulai_terpapar_risiko',
        ]);

        try {
            DB::transaction(function () use ($request, $rekomendasi) {
                $waktuMulai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_mulai_terpapar_risiko)->format('Y-m-d');
                $waktuSelesai = Carbon::createFromFormat('d/m/Y', $request->perkiraan_waktu_selesai_terpapar_risiko)->format('Y-m-d');

                $rekomendasi->fill($request->except(['penyebab_risiko', 'key_risk_indicator', 'kontrol_eksisting']));
                
                $jenisRisiko = JenisRisiko::find($request->jenis_risiko_id);
                if ($jenisRisiko) {
                    $rekomendasi->kategori_risiko_id = $jenisRisiko->kategori_risiko_id;
                }
                $rekomendasi->perkiraan_waktu_terpapar_risiko_mulai = $waktuMulai;
                $rekomendasi->perkiraan_waktu_terpapar_risiko_akhir = $waktuSelesai;
                $rekomendasi->save();

                $rekomendasi->penyebabRisikos()->delete();
                $rekomendasi->kris()->delete();
                $rekomendasi->kontrolEksistings()->delete();

                // Buat ulang Penyebab Risiko
                foreach ($request->penyebab_risiko as $penyebab) {
                    if (!empty($penyebab)) {
                        $rekomendasi->penyebabRisikos()->create(['penyebab_risiko' => $penyebab]);
                    }
                }

                // Buat ulang KRI
                if ($request->has('key_risk_indicator')) {
                    foreach ($request->key_risk_indicator as $index => $kri) {
                        if (!empty($kri)) {
                            $rekomendasi->kris()->create([
                                'kri' => $kri,
                                'satuan_kri' => $request->satuan_kri[$index] ?? null,
                                'batas_aman' => $request->batas_aman[$index] ?? null,
                                'batas_waspada' => $request->batas_waspada[$index] ?? null,
                                'batas_bahaya' => $request->batas_bahaya[$index] ?? null,
                            ]);
                        }
                    }
                }
                
                // Buat ulang Kontrol Eksisting
                foreach ($request->kontrol_eksisting as $kontrol) {
                    if (!empty($kontrol)) {
                        $rekomendasi->kontrolEksistings()->create(['kontrol_eksisting' => $kontrol]);
                    }
                }
            });

            return response()->json([
                'message' => 'Rekomendasi Risiko berhasil diperbarui.',
                'redirect' => route('rekomendasi-risiko.show', ['unit' => $rekomendasi->unit_id, 'periode' => $rekomendasi->periode_id])
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(RekomendasiRisiko $rekomendasi)
    {
        try {
            $rekomendasi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rekomendasi Risiko berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function publish(RekomendasiRisiko $rekomendasi)
    {
        DB::transaction(function () use ($rekomendasi) {
            $rekomendasi->load('penyebabRisikos', 'kris', 'kontrolEksistings');
            
            $attributes = $rekomendasi->getAttributes();
            unset($attributes['id'], $attributes['created_at'], $attributes['updated_at'], $attributes['deleted_at']);
            
            $attributes['status'] = IdentifikasiRisiko::STATUS_INPUT_DATA;
            $attributes['status_progress'] = IdentifikasiRisiko::PROGRESS_ON_REVIEW;
            $attributes['status_risiko'] = IdentifikasiRisiko::STATUS_RISIKO_RECOMMENDATION;

            $identifikasiRisikoBaru = IdentifikasiRisiko::create($attributes);

            foreach ($rekomendasi->penyebabRisikos as $penyebab) {
                PenyebabRisiko::create([
                    'risiko_id' => $identifikasiRisikoBaru->id,
                    'penyebab_risiko' => $penyebab->penyebab_risiko,
                ]);
            }

            foreach ($rekomendasi->kris as $kri) {
                KRI::create([
                    'kri_id' => $kri->kri_id,
                    'risiko_id' => $identifikasiRisikoBaru->id,
                    'kri' => $kri->kri,
                    'satuan_kri' => $kri->satuan_kri,
                    'batas_aman' => $kri->batas_aman,
                    'batas_waspada' => $kri->batas_waspada,
                    'batas_bahaya' => $kri->batas_bahaya,
                ]);
            }

            foreach ($rekomendasi->kontrolEksistings as $kontrol) {
                KontrolEksisting::create([
                    'risiko_id' => $identifikasiRisikoBaru->id,
                    'kontrol_eksisting' => $kontrol->kontrol_eksisting,
                ]);
            }
            
            $rekomendasi->status = RekomendasiRisiko::STATUS_PUBLISHED;
            $rekomendasi->save();
        });

        return redirect()->back()->with('success', 'Rekomendasi Risiko berhasil di-publish.');
    }
}
