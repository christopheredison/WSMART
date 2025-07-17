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

class UnitLEDController extends Controller
{
    public function index(Request $request, $unitId = null)
    {
        if ($request->ajax()) {
            $data = LossEvent::with(['kategoriKejadian']);
            
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
                    return '-';
                })
                ->editColumn('unit_penanggung_jawab', function($row) {
                    return $row->unit_penanggung_jawab ?? '-';
                })
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

    public function create()
    {
        $periodes = Periode::orderBy('tahun', 'desc')->with('identifikasiRisikos.penyebabRisikos.perlakuanPenyebabRisiko')->get();
        $kategoriKejadians = KategoriKejadian::all();
        $jenisRisikos = JenisRisiko::with('kategoriRisiko')->get();
        $jabatans = Jabatan::all();
        $periode = null;
        $identifikasiRisikos = [];
        if (request()->periode_id) {
            $user = request()->user();
            $periode = Periode::findOrFail(request()->periode_id);
        }
        
        return view('unit-led.create', compact('periodes', 'kategoriKejadians', 'jenisRisikos', 'jabatans', 'periode'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required',
            'nama_kejadian' => 'required',
            'tanggal_kejadian' => 'required|date',
            'identifikasi_kejadian' => 'required',
            'kategori_kejadian_id' => 'required',
            'sumber_penyebab_kejadian' => 'required|in:1,2',
            'penyebab_masalah' => 'required',
            'penanganan_kejadian' => 'required',
            'deskripsi_kejadian' => 'required',
            'kategori_risiko_bumn' => 'required|in:1,2,3',
            'kategori_risiko_id' => 'required',
            'jenis_risiko_id' => 'required',
            'penjelasan_kerugian' => 'required',
            'nilai_kerugian_finansial' => 'nullable|numeric',
            'kejadian_berulang' => 'required|in:0,1',
            'frekuensi_kejadian' => 'required_if:kejadian_berulang,1|nullable|in:1,2,3,4,5,6',
            'rencana_mitigasi' => 'required',
            'realisasi_mitigasi' => 'required',
            'perbaikan_mendatang' => 'required',
            'unit_penanggung_jawab' => 'required',
            'status_asuransi' => 'required|in:0,1',
            'nilai_premi' => 'required_if:status_asuransi,1|nullable|numeric',
            'nilai_klaim' => 'required_if:status_asuransi,1|nullable|numeric',
            'status_risk_register' => 'required|in:0,1',
            'no_urut_risiko' => 'required_if:status_risk_register,1|nullable',
            'biaya_risiko_inheren' => 'nullable|numeric',
            'biaya_upaya_perbaikan' => 'nullable|numeric',
            'hasil_perbaikan' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->all();
            $data['tahun'] = Carbon::parse($request->tanggal_kejadian)->format('Y');
            $data['unit_id'] = Auth::user()->unit_id; // Mengambil unit_id dari user yang login
            
            // Set default values for numeric fields
            $data['nilai_kerugian_finansial'] = $request->nilai_kerugian_finansial ?: 0;
            $data['nilai_premi'] = $request->nilai_premi ?: 0;
            $data['nilai_klaim'] = $request->nilai_klaim ?: 0;
            $data['biaya_risiko_inheren'] = $request->biaya_risiko_inheren ?: 0;
            $data['biaya_upaya_perbaikan'] = $request->biaya_upaya_perbaikan ?: 0;
            $data['hasil_perbaikan'] = $request->hasil_perbaikan ?: 0;

            $data['unit_penanggung_jawab_jabatan_id'] = $request->unit_penanggung_jawab;
        
            // Ambil nama jabatan berdasarkan ID
            $jabatan = Jabatan::find($request->unit_penanggung_jawab);
            if ($jabatan) {
                $data['unit_penanggung_jawab'] = $jabatan->name;
            }
            else {
                $data['unit_penanggung_jawab'] = '-';
            }
            
            LossEvent::create($data);

            return redirect()
                ->route('unit-led.index')
                ->with('success', 'Data Loss Event Unit berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $lossEvent = LossEvent::findOrFail($id);
        $periodes = Periode::orderBy('tahun', 'desc')->with('identifikasiRisikos.penyebabRisikos.perlakuanPenyebabRisiko')->get();
        $kategoriKejadians = KategoriKejadian::all();
        $jenisRisikos = JenisRisiko::with('kategoriRisiko')->get();
        $jabatans = Jabatan::all();
        $periode = $lossEvent->periode;

        return view('unit-led.edit', compact('lossEvent', 'periodes', 'kategoriKejadians', 'jenisRisikos', 'jabatans', 'periode'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required',
            'nama_kejadian' => 'required',
            'tanggal_kejadian' => 'required|date',
            'identifikasi_kejadian' => 'required',
            'kategori_kejadian_id' => 'required',
            'sumber_penyebab_kejadian' => 'required|in:1,2',
            'penyebab_masalah' => 'required',
            'penanganan_kejadian' => 'required',
            'deskripsi_kejadian' => 'required',
            'kategori_risiko_bumn' => 'required|in:1,2,3',
            'kategori_risiko_id' => 'required',
            'jenis_risiko_id' => 'required',
            'penjelasan_kerugian' => 'required',
            'nilai_kerugian_finansial' => 'nullable|numeric',
            'kejadian_berulang' => 'required|in:0,1',
            'frekuensi_kejadian' => 'required_if:kejadian_berulang,1|nullable|in:1,2,3,4,5,6',
            'rencana_mitigasi' => 'required',
            'realisasi_mitigasi' => 'required',
            'perbaikan_mendatang' => 'required',
            'unit_penanggung_jawab' => 'required',
            'status_asuransi' => 'required|in:0,1',
            'nilai_premi' => 'required_if:status_asuransi,1|nullable|numeric',
            'nilai_klaim' => 'required_if:status_asuransi,1|nullable|numeric',
            'status_risk_register' => 'required|in:0,1',
            'no_urut_risiko' => 'required_if:status_risk_register,1|nullable',
            'biaya_risiko_inheren' => 'nullable|numeric',
            'biaya_upaya_perbaikan' => 'nullable|numeric',
            'hasil_perbaikan' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $lossEvent = LossEvent::findOrFail($id);
            $data = $request->all();
            $data['tahun'] = Carbon::parse($request->tanggal_kejadian)->format('Y');
            
            // Set default values for numeric fields
            $data['nilai_kerugian_finansial'] = $request->nilai_kerugian_finansial ?: 0;
            $data['nilai_premi'] = $request->nilai_premi ?: 0;
            $data['nilai_klaim'] = $request->nilai_klaim ?: 0;
            $data['biaya_risiko_inheren'] = $request->biaya_risiko_inheren ?: 0;
            $data['biaya_upaya_perbaikan'] = $request->biaya_upaya_perbaikan ?: 0;
            $data['hasil_perbaikan'] = $request->hasil_perbaikan ?: 0;

            $data['unit_penanggung_jawab_jabatan_id'] = $request->unit_penanggung_jawab;
        
            // Ambil nama jabatan berdasarkan ID
            $jabatan = Jabatan::find($request->unit_penanggung_jawab);
            if ($jabatan) {
                $data['unit_penanggung_jawab'] = $jabatan->name;
            }
            else {
                $data['unit_penanggung_jawab'] = '-';
            }
            
            $lossEvent->update($data);

            return redirect()
                ->route('unit-led.index')
                ->with('success', 'Data Loss Event Unit berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())
                ->withInput();
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

    public function show($id)
    {
        $lossEvent = LossEvent::with(['kategoriKejadian', 'kategoriRisiko', 'jenisRisiko'])->findOrFail($id);
        
        return view('unit-led.show', compact('lossEvent'));
    }
}
