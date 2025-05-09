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

class ProjectLEDController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = LossEventProject::with(['projectSektor', 'peristiwaRisiko', 'project']);
            
            if ($request->filled('tahun') && $request->tahun !== '') {
                $data->where('tahun', $request->tahun);
            }
            if ($request->filled('konstruksi_id') && $request->konstruksi_id !== '') {
                $data->where('project_sektor_id', $request->konstruksi_id);
            }
            if ($request->filled('peristiwa_id') && $request->peristiwa_id !== '') {
                $data->where('peristiwa_risiko_id', $request->peristiwa_id);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    return view('project-led._table_action', compact('row'))->render();
                })
                ->editColumn('tahun_kejadian', function($row) {
                    return $row->tahun;
                })
                ->editColumn('konstruksi_spesifik', function($row) {
                    return $row->projectSektor ? $row->projectSektor->sektor_name : 'Non Project';
                })
                ->editColumn('peristiwa_risiko', function($row) {
                    return $row->peristiwaRisiko ? $row->peristiwaRisiko->title : '-';
                })
                ->editColumn('project', function($row) {
                    return $row->project ? $row->project->project_name : 'Non Project';
                })
                ->editColumn('deskripsi', function($row) {
                    return $row->deskripsi_kejadian ?? '-';
                })
                ->editColumn('pic', function($row) {
                    return $row->unit_penanggung_jawab ?? '-';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    
        $projectSektors = ProjectSektor::all();
        $peristiwaRisikos = PeristiwaRisiko::all();
    
        return view('project-led.index', compact('projectSektors', 'peristiwaRisikos'));
    }

    public function create()
    {
        $projectSektors = ProjectSektor::all();
        $peristiwaRisikos = PeristiwaRisiko::all();
        $projects = Project::all();

        return view('project-led.create', compact('projectSektors', 'peristiwaRisikos', 'projects'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_sektor_id' => 'required',
            'peristiwa_risiko_id' => 'required',
            'project_id' => 'required',
            'tanggal_kejadian' => 'required|date',
            'deskripsi_kejadian' => 'required',
            'nilai_kerugian_finansial' => 'required_without:nilai_kerugian_non_finansial|nullable|numeric',
            'nilai_kerugian_non_finansial' => 'required_without:nilai_kerugian_finansial|nullable',
            'unit_penanggung_jawab' => 'required'
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
            
            // Set default values
            $data['nilai_kerugian_finansial'] = $request->nilai_kerugian_finansial ?: 0;
            $data['nilai_kerugian_non_finansial'] = $request->nilai_kerugian_non_finansial ?: '-';
            
            LossEventProject::create($data);

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
        $peristiwaRisikos = PeristiwaRisiko::all();
        $projects = Project::all();

        return view('project-led.edit', compact('lossEvent', 'projectSektors', 'peristiwaRisikos', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'project_sektor_id' => 'required',
            'peristiwa_risiko_id' => 'required',
            'project_id' => 'required',
            'tanggal_kejadian' => 'required|date',
            'deskripsi_kejadian' => 'required',
            'nilai_kerugian_finansial' => 'required_without:nilai_kerugian_non_finansial|nullable|numeric',
            'nilai_kerugian_non_finansial' => 'required_without:nilai_kerugian_finansial|nullable',
            'unit_penanggung_jawab' => 'required'
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
            
            // Set default values
            $data['nilai_kerugian_finansial'] = $request->nilai_kerugian_finansial ?: 0;
            $data['nilai_kerugian_non_finansial'] = $request->nilai_kerugian_non_finansial ?: '-';
            
            $lossEvent->update($data);

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
}
