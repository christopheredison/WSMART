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
use App\Models\LossEventProjectFile;
use Yajra\DataTables\Facades\DataTables;
use App\Models\KategoriKejadian;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\Jabatan;
use App\Models\PenyebabRisikoProjectLed;
use App\Models\PerlakuanPenyebabRisikoProjectLed;
use App\Models\ProjectRiskAnalisa;
use App\Models\PenyebabRisikoProject;
use App\Models\PerlakuanPenyebabRisikoProject;
use App\Models\KamusRisikoProject;
use App\Models\DataBatch;
use Illuminate\Support\Facades\DB;

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
                    return 'Rp 0';
                })
                // ->editColumn('unit_penanggung_jawab', function($row) {
                //     return $row->unit_penanggung_jawab ?? '-';
                // })
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
        if (request()->project) {
            $project = $projects->where('id', request()->project)->first();
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
        $request->merge([
            'nilai_kerugian_finansial' => $this->cleanRupiah($request->nilai_kerugian_finansial),
            'nilai_premi' => $this->cleanRupiah($request->nilai_premi),
            'nilai_klaim' => $this->cleanRupiah($request->nilai_klaim),
        ]);

        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'nama_kejadian' => 'required|string',
            'peristiwa_risiko_id' => 'required',
            'deskripsi_kejadian' => $request->peristiw_risiko_id == 'other' ? 'required|string' : 'nullable|string',
            'tanggal_kejadian' => 'required',
            // 'kategori_kejadian_id' => 'required|exists:kategori_kejadians,id',
            'sumber_penyebab_kejadian' => 'required|in:1,2',
            // 'kategori_risiko_bumn' => 'required|in:1,2,3',
            // 'jenis_risiko_id' => 'required|exists:jenis_risikos,id',
            // 'kategori_risiko_id' => 'required|exists:kategori_risikos,id',
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

        DB::beginTransaction();
        try {
            $led = LossEventProject::create([
                'project_id' => $request->project_id,
                'nama_kejadian' => $request->nama_kejadian,
                'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
                'deskripsi_kejadian' => $request->deskripsi_kejadian ?? null,
                'tanggal_kejadian' => $request->tanggal_kejadian,
                'tahun' => Carbon::parse($request->tanggal_kejadian)->format('Y'),
                'kategori_kejadian_id' => $request->kategori_kejadian_id,
                'sumber_penyebab_kejadian' => $request->sumber_penyebab_kejadian,
                'kategori_risiko_bumn' => $request->kategori_risiko_bumn ?? 1,
                'jenis_risiko_id' => $request->jenis_risiko_id ?? 1,
                'kategori_risiko_id' => $request->kategori_risiko_id ?? 1,
                'penjelasan_kerugian' => $request->penjelasan_kerugian,
                'nilai_kerugian_finansial' => $request->nilai_kerugian_finansial ?? 0,
                'kejadian_berulang' => $request->kejadian_berulang,
                'frekuensi_kejadian' => $request->kejadian_berulang == '1' ? $request->frekuensi_kejadian : null,
                'status_asuransi' => $request->status_asuransi,
                'nilai_premi' => $request->status_asuransi == '1' ? ($request->nilai_premi ?? 0) : 0,
                'nilai_klaim' => $request->status_asuransi == '1' ? ($request->nilai_klaim ?? 0) : 0,
                'version' => 1,
            ]);

            // Simpan data penyebab dan perlakuan untuk LED
            $penyebabData = json_decode($request->input('penyebab_data'), true);
            if (is_array($penyebabData)) {
                foreach ($penyebabData as $penyebab) {
                    $ledPenyebab = PenyebabRisikoProjectLed::create([
                        'loss_event_project_id' => $led->id,
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

            // 2. JIKA USER MEMILIH "YA", BUAT PROJECT RISK BARU
            if ($request->input('create_risk_from_led') == '1') {
                // Cek batch terakhir yang belum finish
                $activeBatch = DataBatch::where('project_id', $request->project_id)
                    ->where('type', 2)
                    ->where('finish', false)
                    ->orderBy('batch', 'desc')
                    ->first();

                // cek activeBatch sudah selesai atau belum
                if ($activeBatch && $activeBatch->status != 1 && $activeBatch->status != 8) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal membuat risiko baru: Proyek sedang dalam proses validasi risiko. Harap tunggu hingga proses validasi selesai.');
                }

                $projectPeriodeList = ProjectPeriodeList::findOrFail($request->project_id);
                $project = $projectPeriodeList->project;
                $user = auth()->user();

                // Buat ProjectRisk
                $newProjectRisk = $projectPeriodeList->projectRisks()->create([
                    'unit_type_id' => $user->unit_type_id,
                    'unit_id' => $user->unit_id,
                    'periode_id' => 0,
                    'user_id' => $user->id,
                    'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
                    'deskripsi_peristiwa_risiko' => $request->nama_kejadian,
                    'jenis_risiko_id' => $request->jenis_risiko_id ?? 1,
                    'kategori_risiko_id' => $request->kategori_risiko_id ?? 1,
                    'perkiraan_waktu_terpapar_risiko_mulai' => $request->tanggal_kejadian,
                    'perkiraan_waktu_terpapar_risiko_akhir' => $request->tanggal_kejadian,
                    'project_id' => $project->id,
                    'target_capaian_kinerja' => '',
                    'project_periode_list_id' => $projectPeriodeList->id,
                    'jenis_kontrol_eksisting_id' => 0,
                    'penilaian_efektifitas_kontrol' => 0,
                    'kontrol_eksisting' => '',
                    'status_risiko' => '0',
                    'status_progress'  => '0',
                    'status' => 1,
                    'step_verification' => 0
                ]);

                // Buat ProjectRiskAnalisa
                $kategoriDampak = ($request->nilai_kerugian_finansial > 0) ? 'Kuantitatif' : 'Kualitatif';
                $newProjectRisk->projectRiskAnalisa()->create([
                    'kategori_dampak' => $kategoriDampak,
                    'deskripsi_dampak' => ($kategoriDampak == 'Kualitatif') ? $request->penjelasan_kerugian : null,
                    'asumsi_perhitungan_dampak' => ($kategoriDampak == 'Kuantitatif') ? $request->penjelasan_kerugian : null,
                    'nilai_dampak' => $request->nilai_kerugian_finansial ?? 0,
                ]);

                // Simpan data penyebab dan perlakuan untuk ProjectRisk
                if (is_array($penyebabData)) {
                    foreach ($penyebabData as $penyebab) {
                        $riskPenyebab = $newProjectRisk->penyebabRisikoProjects()->create([
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
                return redirect()->route('projects.risks.edit', ['project' => $projectPeriodeList->id, 'risk' => $newProjectRisk->id])
                    ->with('success', 'Loss Event berhasil dibuat dan Project Risk baru telah ditambahkan.');
            }

            // Jika user memilih "Tidak", cukup simpan LED
            DB::commit();
            return redirect()->route('project-led.index-by-project', ['projectId' => $request->project_id])
                ->with('success', 'Data Loss Event Project berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($project, $id)
    {
        $lossEvent = LossEventProject::with('penyebabRisikoProjectLeds.perlakuanPenyebabRisiko')->findOrFail($id);
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
        $analisa = null;

        $penyebabData = $lossEvent->penyebabRisikoProjectLeds->map(function ($penyebab) {
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

        return view('project-led.edit', compact(
            'lossEvent',
            'projectSektors',
            'peristiwaRisikos',
            'projects',
            'kategoriKejadians',
            'kategoriRisikos',
            'jenisRisikos',
            'jabatans',
            'project',
            'analisa',
            'penyebabData',
        ));
    }

    public function update(Request $request, $id)
    {
        $lossEvent = LossEventProject::findOrFail($id);

        $request->merge([
            'nilai_kerugian_finansial' => $this->cleanRupiah($request->nilai_kerugian_finansial),
            'nilai_premi' => $this->cleanRupiah($request->nilai_premi),
            'nilai_klaim' => $this->cleanRupiah($request->nilai_klaim),
        ]);
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'nama_kejadian' => 'required|string|max:255',
            'peristiwa_risiko_id' => 'required|exists:peristiwa_risikos,id',
            'tanggal_kejadian' => 'required',
            // 'kategori_kejadian_id' => 'required|exists:kategori_kejadians,id',
            'sumber_penyebab_kejadian' => 'required|in:1,2',
            // 'kategori_risiko_bumn' => 'required|in:1,2,3',
            // 'jenis_risiko_id' => 'required|exists:jenis_risikos,id',
            // 'kategori_risiko_id' => 'required|exists:kategori_risikos,id',
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

        DB::beginTransaction();
        try {
            $lossEvent->update($request->except(['_token', '_method', 'penyebab_data', 'create_risk_from_led']));

            $penyebabDataFromRequest = json_decode($request->input('penyebab_data'), true) ?? [];
            $existingPenyebabIds = $lossEvent->penyebabRisikoProjectLeds()->pluck('id')->toArray();
            $requestPenyebabIds = [];

            // dd($penyebabDataFromRequest);
            foreach ($penyebabDataFromRequest as $penyebabItem) {
                $isNewPenyebab = !isset($penyebabItem['id']) || str_starts_with($penyebabItem['id'], 'temp_');

                $penyebabData = [
                    'loss_event_project_id' => $lossEvent->id,
                    'penyebab_risiko' => $penyebabItem['penyebab_risiko'],
                ];

                if ($isNewPenyebab) {
                    $penyebabRecord = PenyebabRisikoProjectLed::create($penyebabData);
                } else {
                    $penyebabRecord = PenyebabRisikoProjectLed::find($penyebabItem['id']);
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
                                $perlakuanRecord = PerlakuanPenyebabRisikoProjectLed::find($perlakuanItem['id']);
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
                            PerlakuanPenyebabRisikoProjectLed::destroy($perlakuanToDelete);
                        }

                    } else if ($penyebabRecord) {
                        $penyebabRecord->perlakuanPenyebabRisiko()->delete();
                    }
                }
            }

            $penyebabToDelete = array_diff($existingPenyebabIds, $requestPenyebabIds);
            if (!empty($penyebabToDelete)) {
              PenyebabRisikoProjectLed::destroy($penyebabToDelete);
            }

            // JIKA USER MEMILIH "YA", BUAT PROJECT RISK BARU
            if ($request->input('create_risk_from_led') == '1') {
                // Cek batch terakhir yang belum finish
                $activeBatch = DataBatch::where('project_id', $request->project_id)
                    ->where('type', 2)
                    ->where('finish', false)
                    ->orderBy('batch', 'desc')
                    ->first();

                // cek activeBatch sudah selesai atau belum
                if ($activeBatch && $activeBatch->status != 1 && $activeBatch->status != 8) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal membuat risiko baru: Proyek sedang dalam proses validasi risiko. Harap tunggu hingga proses validasi selesai.');
                }

                $projectPeriodeList = ProjectPeriodeList::with('project')->findOrFail($lossEvent->project_id);
                $project = $projectPeriodeList->project;
                $user = auth()->user();

                // Buat ProjectRisk baru
                $newProjectRisk = $projectPeriodeList->projectRisks()->create([
                    'unit_type_id' => $user->unit_type_id,
                    'unit_id' => $user->unit_id,
                    'periode_id' => 0,
                    'user_id' => $user->id,
                    'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
                    'deskripsi_peristiwa_risiko' => $request->nama_kejadian,
                    'jenis_risiko_id' => $request->jenis_risiko_id ?? 0,
                    'kategori_risiko_id' => $request->kategori_risiko_id ?? 0,
                    'perkiraan_waktu_terpapar_risiko_mulai' => $request->tanggal_kejadian,
                    'perkiraan_waktu_terpapar_risiko_akhir' => $request->tanggal_kejadian,
                    'project_id' => $project->id,
                    'target_capaian_kinerja' => '',
                    'project_periode_list_id' => $projectPeriodeList->id,
                    'jenis_kontrol_eksisting_id' => 0,
                    'penilaian_efektifitas_kontrol' => 0,
                    'kontrol_eksisting' => '',
                    'status_risiko' => '0',
                    'status_progress'  => '0',
                    'status' => 1,
                    'step_verification' => 0
                ]);

                // Hubungkan Loss Event ke Project Risk yang baru dibuat
                $lossEvent->update(['project_risk_id' => $newProjectRisk->id]);

                // Buat ProjectRiskAnalisa
                $kategoriDampak = ($request->nilai_kerugian_finansial > 0) ? 'Kuantitatif' : 'Kualitatif';
                $newProjectRisk->projectRiskAnalisa()->create([
                    'kategori_dampak' => $kategoriDampak,
                    'deskripsi_dampak' => ($kategoriDampak == 'Kualitatif') ? $request->penjelasan_kerugian : null,
                    'asumsi_perhitungan_dampak' => ($kategoriDampak == 'Kuantitatif') ? $request->penjelasan_kerugian : null,
                    'nilai_dampak' => $request->nilai_kerugian_finansial ?? 0,
                ]);

                // Salin data penyebab dan perlakuan ke ProjectRisk yang baru
                if (is_array($penyebabDataFromRequest)) {
                    foreach ($penyebabDataFromRequest as $penyebab) {
                        $riskPenyebab = $newProjectRisk->penyebabRisikoProjects()->create([
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
                return redirect()->route('projects.risks.edit', ['project' => $projectPeriodeList->id, 'risk' => $newProjectRisk->id])
                    ->with('success', 'Loss Event berhasil diperbarui dan Project Risk baru telah ditambahkan.');
            }

            DB::commit();
            return redirect()->route('project-led.index-by-project', ['projectId' => $lossEvent->project_id])
                ->with('success', 'Data Loss Event Project berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage())->withInput();
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
        $lossEvent = LossEventProject::with([
          'peristiwaRisiko',
          'kategoriKejadian',
          'kategoriRisiko',
          'jenisRisiko',
          'penyebabRisikoProjectLeds.perlakuanPenyebabRisiko',
          'risiko',
        ])->findOrFail($id);

        $project = Project::find($lossEvent->project_id);
        return view('project-led.show', compact('lossEvent', 'project'));
    }

    public function riskChangeToLed(Project $project, ProjectRisk $risk)
    {
        $projectRisk = $risk->load([
            'peristiwaRisiko',
            'jenisRisiko.kategoriRisiko',
            'projectRiskAnalisa',
            'penyebabRisikoProjects.perlakuanPenyebabRisiko'
        ]);

        $penyebabData = $projectRisk->penyebabRisikoProjects->map(function ($penyebab) {
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
                        'pic_name' => $perlakuan->pic ?? '',
                        'timeline_mulai_perlakuan_risiko' => Carbon::parse($perlakuan->timeline_perlakuan_risiko_start)->format('d/m/Y'),
                        'timeline_selesai_perlakuan_risiko' => Carbon::parse($perlakuan->timeline_perlakuan_risiko_end)->format('d/m/Y'),
                        'opsi_perlakuan_risiko' => $perlakuan->opsi_perlakuan_risiko,
                    ];
                })
            ];
        });

        $peristiwaRisikos = PeristiwaRisiko::where('type', 2)->get();
        $kategoriKejadians = KategoriKejadian::all();
        $jenisRisikos = JenisRisiko::with('kategoriRisiko')->get();
        $analisa = $projectRisk->projectRiskAnalisa;
        $jabatans = Jabatan::all();

        return view('project-led.change-to-led', compact(
            'project',
            'projectRisk',
            'peristiwaRisikos',
            'kategoriKejadians',
            'jenisRisikos',
            'analisa',
            'jabatans',
            'penyebabData',
        ));
    }

    public function riskChangeToLedStore(Request $request, Project $project, ProjectRisk $risk)
    {
        $request->merge([
          'nilai_kerugian_finansial' => $this->cleanRupiah($request->nilai_kerugian_finansial),
          'nilai_premi' => $this->cleanRupiah($request->nilai_premi),
          'nilai_klaim' => $this->cleanRupiah($request->nilai_klaim),
        ]);

        $validator = Validator::make($request->all(), [
            'nama_kejadian' => 'required|string',
            'peristiwa_risiko_id' => 'required',
            'deskripsi_kejadian' => $request->peristiw_risiko_id == 'other' ? 'required|string' : 'nullable|string',
            'tanggal_kejadian' => 'required|date',
            // 'kategori_kejadian_id' => 'required|exists:kategori_kejadians,id',
            'sumber_penyebab_kejadian' => 'required|in:1,2',
            // 'kategori_risiko_bumn' => 'required|in:1,2,3',
            // 'jenis_risiko_id' => 'required|exists:jenis_risikos,id',
            // 'kategori_risiko_id' => 'required|exists:kategori_risikos,id',
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
            if ($request->input('create_new_risk') == '1') {
                $activeBatch = DataBatch::where('project_id', $project->id)
                    ->where('type', 2)
                    ->where('finish', false)
                    ->orderBy('batch', 'desc')
                    ->first();

                if ($activeBatch && $activeBatch->status != 1 && $activeBatch->status != 8) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Gagal membuat risiko baru: Proyek sedang dalam proses validasi risiko. Harap tunggu hingga proses validasi selesai.');
                }
            }
            $led = LossEventProject::create([
                'project_id' => $project->id,
                'project_risk_id' => $risk->id,
                'nama_kejadian' => $request->nama_kejadian,
                'peristiwa_risiko_id' => $request->peristiwa_risiko_id,
                'deskripsi_kejadian' => $request->deskripsi_kejadian ?? null,
                'tanggal_kejadian' => $request->tanggal_kejadian,
                'tahun' => Carbon::parse($request->tanggal_kejadian)->format('Y'),
                'kategori_kejadian_id' => $request->kategori_kejadian_id,
                'sumber_penyebab_kejadian' => $request->sumber_penyebab_kejadian,
                'kategori_risiko_bumn' => $request->kategori_risiko_bumn ?? 1,
                'jenis_risiko_id' => $request->jenis_risiko_id ?? 1,
                'kategori_risiko_id' => $request->kategori_risiko_id ?? 1,
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
                    $newLedPenyebab = PenyebabRisikoProjectLed::create([
                        'loss_event_project_id' => $led->id,
                        'penyebab_risiko' => $penyebabItem['penyebab_risiko'],
                    ]);

                    if (!empty($penyebabItem['perlakuan']) && is_array($penyebabItem['perlakuan'])) {
                        foreach ($penyebabItem['perlakuan'] as $perlakuanItem) {
                            $jabatan = Jabatan::find($perlakuanItem['pic']);

                            PerlakuanPenyebabRisikoProjectLed::create([
                                'penyebab_risiko_led_id' => $newLedPenyebab->id,
                                'rencana_perlakuan_risiko' => $perlakuanItem['rencana_perlakuan_risiko'],
                                'output_perlakuan_risiko' => $perlakuanItem['output_perlakuan_risiko'],
                                'biaya_perlakuan_risiko' => $this->cleanRupiah($perlakuanItem['biaya_perlakuan_risiko']),
                                'pic' => $jabatan ? $jabatan->name : '',
                                'pic_jabatan_id' => $jabatan ? $jabatan->id : null,
                                'timeline_perlakuan_risiko_start' => Carbon::createFromFormat('d/m/Y', $perlakuanItem['timeline_mulai_perlakuan_risiko'])->format('Y-m-d'),
                                'timeline_perlakuan_risiko_end' => Carbon::createFromFormat('d/m/Y', $perlakuanItem['timeline_selesai_perlakuan_risiko'])->format('Y-m-d'),
                                'opsi_perlakuan_risiko' => $perlakuanItem['opsi_perlakuan_risiko'],
                            ]);
                        }
                    }
                }
            }

            $efektivitas = 0.0;

            $analisa = $risk?->projectRiskAnalisa;
            $monitoring = $risk?->projectRiskMonitoring;
            $skala_risiko_inherent = (float) optional($analisa)->skala_risiko;
            $skala_risiko_rencana = (float) optional($analisa)->skala_risiko_residual;
            $skala_risiko_realisasi = (float) optional($monitoring)->skala_risiko;

            $selisih_inherent_rencana = $skala_risiko_inherent - $skala_risiko_rencana;

            // Hindari pembagian dengan nol
            if ($selisih_inherent_rencana != 0) {
                $efektivitas = (($skala_risiko_rencana - $skala_risiko_realisasi) / $selisih_inherent_rencana) * 100;
            }

            $risk->update([
              'efektivitas_perlakuan_risiko' => $efektivitas,
            ]);

            // Cek apakah risiko perlu di-close
            if ($request->input('is_closed') == '1') {
                $risk->update([
                  'is_closed' => true,
                ]);

                KamusRisikoProject::updateOrCreate(
                    [
                      'project_id' => $project->id,
                      'project_risk_id' => $risk->id,
                    ],
                );
            }

            DB::commit();

            if ($request->input('create_new_risk') == '1') {
                $penyebabText = "Risiko " . $request->nama_kejadian;
                return redirect()->route(
                    'projects.risks.create',
                    [
                        'project' => $project->id,
                        'penyebab_risiko' => $penyebabText
                    ]
                )->with('success', 'Loss Event berhasil dibuat. Silakan tambahkan risiko baru.');
            }

            return redirect()->route('projects.monitorings.index', ['project' => $project->id])
            ->with('success', 'Project Risk berhasil diubah menjadi Loss Event.');
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

    public function getFiles($id)
    {
        $files = LossEventProjectFile::where('loss_event_project_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $files->map(function($file) {
                return [
                    'id' => $file->id,
                    'file_name' => $file->file_name,
                    'file_type' => $file->file_type,
                    'file_size' => number_format($file->file_size / 1024, 2) . ' KB',
                    'file_url' => asset('storage/' . $file->file_path),
                    'created_at' => $file->created_at->format('d M Y H:i')
                ];
            })
        ]);
    }

    public function storeFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'loss_event_id' => 'required|exists:loss_event_projects,id',
            'file_dokumen' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            if ($request->hasFile('file_dokumen')) {
                $file = $request->file('file_dokumen');
                $originalName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
                $fileType = $file->getClientMimeType();

                $path = $file->store('loss-event-project', 'public');

                LossEventProjectFile::create([
                    'loss_event_project_id' => $request->loss_event_id,
                    'file_name' => $originalName,
                    'file_path' => $path,
                    'file_type' => $fileType,
                    'file_size' => $fileSize
                ]);

                return response()->json(['success' => true, 'message' => 'File berhasil diunggah']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengunggah file: ' . $e->getMessage()]);
        }
    }

    public function destroyFile($id)
    {
        try {
            $file = LossEventProjectFile::findOrFail($id);

            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            $file->delete();

            return response()->json(['success' => true, 'message' => 'File berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus file']);
        }
    }
}
