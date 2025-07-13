<?php

namespace App\Http\Controllers\Project;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\ProjectRisk;
use App\Models\ProjectPeriodeList;
use App\Models\ProjectSektor;
use App\Models\PeristiwaRisiko;
use App\Models\LossEventProject;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KategoriKejadian;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\Jabatan;

class ProjectLEDController extends Controller
{
    //
    public function index(Request $request, $projectId = null)
    {
        if ($request->ajax()) {
            $data = LossEventProject::with(['peristiwaRisiko', 'kategoriKejadian']);

            if ($projectId) {
                $data->where('project_id', $projectId);
            }
            
            if ($request->filled('tahun') && $request->tahun !== '') {
                $data->where('tahun', $request->tahun);
            }
            if ($request->filled('peristiwa_id') && $request->peristiwa_id !== '') {
                $data->where('peristiwa_risiko_id', $request->peristiwa_id);
            }
            if ($request->filled('kategori_id') && $request->kategori_id !== '') {
                $data->where('kategori_kejadian_id', $request->kategori_id);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    return view('project-led._table_action', compact('row'))->render();
                })
                ->editColumn('nama_kejadian', function($row) {
                    return $row->nama_kejadian ?? '-';
                })
                ->editColumn('peristiwa_risiko', function($row) {
                    return $row->peristiwaRisiko ? $row->peristiwaRisiko->title : '-';
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
    
        $peristiwaRisikos = PeristiwaRisiko::where('type', 2)->get();
        $kategoriKejadians = KategoriKejadian::all();

        $project = null;
        if ($projectId) {
            $project = Project::find($projectId);
        }
    
        return view('project-led.index', compact('peristiwaRisikos', 'kategoriKejadians', 'projectId', 'project'));
    }

    public function create()
    {
        $project = null;
        $projectSektors = ProjectSektor::all();
        $peristiwaRisikos = PeristiwaRisiko::where('type', 2)->get(); // Filter for project type
        $projects = Project::with(['projectPeriodeList.projectRisks' => function($query) {
            $query->with('penyebabRisikoProjects.perlakuanPenyebabRisiko');
        }])->get();
        if (request()->project_id) {
            $project = $projects->where('id', request()->project_id)->first();
        }
        $kategoriKejadians = KategoriKejadian::all();
        $kategoriRisikos = KategoriRisiko::all();
        $jenisRisikos = JenisRisiko::all();
        $jabatans = Jabatan::all();

        return view('project-led.create', compact(
            'projectSektors', 
            'peristiwaRisikos', 
            'projects', 
            'kategoriKejadians',
            'kategoriRisikos',
            'jenisRisikos',
            'jabatans',
            'project'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kejadian' => 'required',
            'tanggal_kejadian' => 'required|date',
            'peristiwa_risiko_id' => 'required',
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
            'no_urut_risiko' => 'required_if:status_risk_register,1|nullable|exists:project_risks,id',
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

            $project = null;
            if ($request->status_risk_register == 1) {
                $riskRegister = ProjectRisk::find($request->no_urut_risiko);
                $project = Project::find($riskRegister->project_id);
                $data['project_id'] = $riskRegister->project_id;
            }
            
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
            
            LossEventProject::create($data);

            if ($data['project_id'] ?? false) {
                return redirect()
                ->route('project-led.index-by-project', ['projectId' => $data['project_id']])
                ->with('success', 'Data Loss Event Project berhasil ditambahkan');
            }

            return redirect()
                ->route('project-led.index')
                ->with('success', 'Data Loss Event Project berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data')
                ->withInput();
        }
    }
    
    public function edit($id)
    {
        $lossEvent = LossEventProject::findOrFail($id);
        $projectSektors = ProjectSektor::all();
        $peristiwaRisikos = PeristiwaRisiko::where('type', 2)->get(); // Filter for project type
        $projects = Project::with(['projectPeriodeList.projectRisks' => function($query) {
            $query->with('penyebabRisikoProjects.perlakuanPenyebabRisiko');
        }])->get();
        $project = $projects->where('id', $lossEvent->project_id)->first();
        $kategoriKejadians = KategoriKejadian::all();
        $kategoriRisikos = KategoriRisiko::all();
        $jenisRisikos = JenisRisiko::all();
        $jabatans = Jabatan::all();

        return view('project-led.edit', compact(
            'lossEvent', 
            'projectSektors', 
            'peristiwaRisikos', 
            'projects', 
            'kategoriKejadians',
            'kategoriRisikos',
            'jenisRisikos',
            'jabatans',
            'project'
        ));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_kejadian' => 'required',
            'tanggal_kejadian' => 'required|date',
            'peristiwa_risiko_id' => 'required',
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
            'no_urut_risiko' => 'required_if:status_risk_register,1|nullable|exists:project_risks,id',
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
            $lossEvent = LossEventProject::findOrFail($id);
            $data = $request->all();
            $data['tahun'] = Carbon::parse($request->tanggal_kejadian)->format('Y');

            $project = null;
            if ($request->status_risk_register == 1) {
                $riskRegister = ProjectRisk::find($request->no_urut_risiko);
                $project = Project::find($riskRegister->project_id);
                $data['project_id'] = $riskRegister->project_id;
            }
            
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
            
            $lossEvent->update($data);

            if ($data['project_id'] ?? false) {
                return redirect()
                ->route('project-led.index-by-project', ['projectId' => $data['project_id']])
                ->with('success', 'Data Loss Event Project berhasil diperbarui');
            }

            return redirect()
                ->route('project-led.index')
                ->with('success', 'Data Loss Event Project berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $lossEvent = LossEventProject::findOrFail($id);
            $lossEvent->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data Loss Event Project berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data Loss Event Project'
            ], 500);
        }
    }

    public function show($id)
    {
        $lossEvent = LossEventProject::with(['peristiwaRisiko', 'kategoriKejadian', 'kategoriRisiko', 'jenisRisiko'])->findOrFail($id);
        $project = Project::find($lossEvent->project_id);
        return view('project-led.show', compact('lossEvent', 'project'));
    }
}
