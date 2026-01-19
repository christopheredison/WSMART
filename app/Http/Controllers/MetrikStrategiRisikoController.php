<?php

namespace App\Http\Controllers;

use App\Models\MetrikStrategiRisiko;
use App\Models\ParameterMetrik;
use App\Models\Periode;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\SikapRisiko;
use App\Models\PeristiwaRisiko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MetrikStrategiRisikoController extends Controller
{
    public function index(Request $request)
    {
        // Get periode untuk dropdown (hanya periode terbaru)
        $periodes = Periode::orderBy('tahun', 'desc')->take(5)->get();
        
        // Get peristiwa risiko untuk dropdown
        $peristiwaRisikos = PeristiwaRisiko::orderBy('id')->get();
        
        // Base query dengan eager loading
        $query = MetrikStrategiRisiko::with(['periode', 'kategoriRisiko', 'jenisRisiko', 'sikapRisiko', 'parameterMetriks', 'peristiwaRisiko'])
            ->orderBy('periode_id', 'desc');
        
        // Filter berdasarkan periode
        if ($request->has('periode_id') && $request->periode_id) {
            $query->where('periode_id', $request->periode_id);
        }
        
        // Filter berdasarkan peristiwa risiko
        if ($request->has('peristiwa_risiko_id') && $request->peristiwa_risiko_id) {
            $query->where('peristiwa_risiko_id', $request->peristiwa_risiko_id);
        }
        
        $metriks = $query->get();
        
        return view('metrik-strategi-risiko.index', compact('metriks', 'periodes', 'peristiwaRisikos'));
    }

    public function create()
    {
        $periodes = Periode::all();
        $kategoriRisikos = KategoriRisiko::all();
        $jenisRisikos = JenisRisiko::all();
        $sikapRisikos = SikapRisiko::all();
        $peristiwaRisikos = PeristiwaRisiko::all();
        
        return view('metrik-strategi-risiko.create', compact('periodes', 'kategoriRisikos', 'jenisRisikos', 'sikapRisikos', 'peristiwaRisikos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required|exists:periodes,id',
            'risk_appetite_statement' => 'required|string',
            'sikap_risiko_id' => 'required|exists:sikap_risikos,id',
            'jenis_risiko' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Ambil data peristiwa risiko
        $jenisRisiko = JenisRisiko::findOrFail($request->jenis_risiko);
        
        // Buat data dengan kategori dan jenis risiko dari peristiwa
        MetrikStrategiRisiko::create([
            'periode_id' => $request->periode_id,
            'kategori_risiko_id' => $jenisRisiko->kategori_risiko_id,
            'jenis_risiko_id' => $jenisRisiko->id,
            'risk_appetite_statement' => $request->risk_appetite_statement,
            'sikap_risiko_id' => $request->sikap_risiko_id,
        ]);

        return redirect()->route('metrik-strategi-risiko.index')
            ->with('success', 'Metrik Strategi Risiko berhasil ditambahkan!');
    }

    public function edit(MetrikStrategiRisiko $metrikStrategiRisiko)
    {
        $periodes = Periode::all();
        $kategoriRisikos = KategoriRisiko::all();
        $jenisRisikos = JenisRisiko::all();
        $sikapRisikos = SikapRisiko::all();
        $peristiwaRisikos = PeristiwaRisiko::all();
        
        return view('metrik-strategi-risiko.edit', compact('metrikStrategiRisiko', 'periodes', 'kategoriRisikos', 'jenisRisikos', 'sikapRisikos', 'peristiwaRisikos'));
    }

    public function update(Request $request, MetrikStrategiRisiko $metrikStrategiRisiko)
    {
        $validator = Validator::make($request->all(), [
            'periode_id' => 'required|exists:periodes,id',
            'jenis_risiko_id' => 'required|exists:jenis_risikos,id',
            'risk_appetite_statement' => 'required|string',
            'sikap_risiko_id' => 'required|exists:sikap_risikos,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Ambil data jenis risiko untuk update kategori
        $jenisRisiko = JenisRisiko::findOrFail($request->jenis_risiko_id);

        $metrikStrategiRisiko->update([
            'periode_id' => $request->periode_id,
            'kategori_risiko_id' => $jenisRisiko->kategori_risiko_id,
            'jenis_risiko_id' => $request->jenis_risiko_id,
            'risk_appetite_statement' => $request->risk_appetite_statement,
            'sikap_risiko_id' => $request->sikap_risiko_id,
        ]);
        
        return redirect()->route('metrik-strategi-risiko.index')->with('success', 'Metrik Strategi Risiko berhasil diperbarui!');
    }

    public function destroy(MetrikStrategiRisiko $metrikStrategiRisiko)
    {
        $metrikStrategiRisiko->delete();
        return redirect()->route('metrik-strategi-risiko.index')->with('success', 'Metrik Strategi Risiko berhasil dihapus!');
    }

    /**
     * Menampilkan halaman kelola parameter untuk metrik strategi risiko tertentu
     */
    public function parameter($id)
    {
        // $metrikStrategiRisiko->load(['periode', 'kategoriRisiko', 'jenisRisiko', 'sikapRisiko', 'parameterMetriks', 'peristiwaRisiko']);

        // dd($metrikStrategiRisiko);

        $metrikStrategiRisiko = MetrikStrategiRisiko::with(['periode', 'kategoriRisiko', 'jenisRisiko', 'sikapRisiko', 'parameterMetriks'])
        ->findOrFail($id);
        
        return view('metrik-strategi-risiko.parameter', ['metrik' => $metrikStrategiRisiko]);
    }

    /**
     * Menyimpan parameter baru untuk metrik strategi risiko
     */
    public function storeParameter(Request $request, MetrikStrategiRisiko $metrikStrategiRisiko)
    {
        $validator = Validator::make($request->all(), [
            'parameter' => 'required|array',
            'parameter.*' => 'required|string',
            'satuan_ukuran' => 'required|array',
            'satuan_ukuran.*' => 'nullable|string',
            'nilai_batasan' => 'required|array',
            'nilai_batasan.*' => 'nullable|string',
            'parameter_id' => 'nullable|array',
            'parameter_id.*' => 'nullable|exists:parameter_metriks,id'
        ]);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
    
        // Dapatkan ID parameter yang ada di form
        $existingIds = array_filter($request->parameter_id ?? []);
        
        // Hapus parameter yang tidak ada di form
        $metrikStrategiRisiko->parameterMetriks()
            ->whereNotIn('id', $existingIds)
            ->delete();
        
        // Update atau tambah parameter baru
        foreach ($request->parameter as $index => $parameter) {
            if (!empty($parameter)) {
                $parameterId = $request->parameter_id[$index] ?? null;
                
                // Data parameter
                $parameterData = [
                    'parameter' => $parameter,
                    'satuan_ukuran' => $request->satuan_ukuran[$index] ?? null,
                    'nilai_batasan' => $request->nilai_batasan[$index] ?? null,
                ];
    
                if ($parameterId) {
                    // Update parameter yang sudah ada
                    $metrikStrategiRisiko->parameterMetriks()
                        ->where('id', $parameterId)
                        ->update($parameterData);
                } else {
                    // Tambah parameter baru
                    $metrikStrategiRisiko->parameterMetriks()
                        ->create($parameterData);
                }
            }
        }
    
        return redirect()->route('metrik-strategi-risiko.parameter', $metrikStrategiRisiko->id)
            ->with('success', 'Parameter berhasil disimpan!');
    }

    /**
     * Menghapus parameter tertentu
     */
    public function destroyParameter(MetrikStrategiRisiko $metrikStrategiRisiko, ParameterMetrik $parameter)
    {
        if ($parameter->metrik_strategi_risiko_id !== $metrikStrategiRisiko->id) {
            return redirect()->route('metrik-strategi-risiko.parameter', $metrikStrategiRisiko->id)
                ->with('error', 'Parameter tidak ditemukan!');
        }

        $parameter->delete();
        return redirect()->route('metrik-strategi-risiko.parameter', $metrikStrategiRisiko->id)
            ->with('success', 'Parameter berhasil dihapus!');
    }
}