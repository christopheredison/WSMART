<?php

namespace App\Http\Controllers;

use App\Models\ProjectRiskContext;
use App\Models\ProjectRiskContextMember;
use App\Models\ProjectRiskContextStakeholderInternal;
use App\Models\ProjectRiskContextStakeholderExternal;
use App\Models\Project;
use App\Models\ProjectPeriodeList;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Hapus Auth jika tidak perlukan scoping by unit

class ProjectRiskContextController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('project_name', 'asc')->get();

        $projectRiskContexts = ProjectRiskContext::with([
                'project',
                'pimpinanTertinggi',
                'members.jabatan',
                'stakeholderInternals',
                'stakeholderExternals'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Ganti view dan compact
        return view('project-risk-context.index', compact('projectRiskContexts', 'projects'));
    }

    public function indexByProjectPeriode($projectPeriodeId)
    {
        $projectPeriodeList = ProjectPeriodeList::findOrFail($projectPeriodeId);
        $project = $projectPeriodeList->project;

        $riskContexts = ProjectRiskContext::where('project_id', $project->id)
            ->with([
                'pimpinanTertinggi', 
                'members.jabatan', 
                'stakeholderInternals', 
                'stakeholderExternals',
                'verifier'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('project-risk-context.index-by-project-periode', compact('riskContexts', 'project'));
    }

    public function create(Request $request)
    {
        // Ganti logika
        $projects = Project::orderBy('project_name', 'asc')->get();
        $jabatans = Jabatan::where('jabatan_type', 2)->orderBy('name')->get();

        $selectedProject = null;
        if ($request->project_id) {
            $selectedProject = Project::find($request->project_id);
        } else {
            $selectedProject = Project::orderBy('project_name', 'asc')->first();
        }

        if ($selectedProject) {
            // Check existing context
            $existingContext = ProjectRiskContext::where('project_id', $selectedProject->id)
                ->first();

            if ($existingContext) {
                return redirect()->route('project-risk-context.edit', $existingContext->id)
                    ->with('info', 'Risk Context untuk project ini sudah ada. Anda dapat mengeditnya.');
            }
        }

        return view('project-risk-context.create', compact('projects', 'jabatans', 'selectedProject'));
    }

    public function edit($id)
    {
        $projectRiskContext = ProjectRiskContext::with([
                'members.jabatan',
                'stakeholderInternals',
                'stakeholderExternals',
                'project',
                'pimpinanTertinggi'
            ])
            ->findOrFail($id);

        $projects = Project::orderBy('project_name', 'asc')->get();
        $jabatans = Jabatan::where('jabatan_type', 2)->orderBy('name')->get();

        return view('project-risk-context.edit', compact('projectRiskContext', 'projects', 'jabatans'));
    }

    public function updateOrCreate(Request $request)
    {
        $projects = Project::orderBy('project_name', 'asc')->get();
        $jabatans = Jabatan::where('jabatan_type', 2)->orderBy('name')->get();

        $selectedProject = null;
        if ($request->project_id) {
            $selectedProject = Project::find($request->project_id);
        } else {
            $selectedProject = Project::orderBy('project_name', 'asc')->first();
        }

        $projectRiskContext = null;
        if ($selectedProject) {
            $projectRiskContext = ProjectRiskContext::where('project_id', $selectedProject->id)
                ->with(['members.jabatan', 'stakeholderInternals', 'stakeholderExternals', 'project', 'pimpinanTertinggi'])
                ->first();
        }

        $projectPeriodeList = ProjectPeriodeList::where('project_id', $selectedProject->id)->first();

        $isEdit = $projectRiskContext ? true : false;

        return view('project-risk-context.update', compact('projects', 'jabatans', 'selectedProject', 'projectPeriodeList', 'projectRiskContext', 'isEdit'));
    }

    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'nilai' => 'nullable|string',
            'pimpinan_tertinggi_jabatan_id' => 'nullable|exists:jabatans,id',
            'sponsor' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'lingkup_pekerjaan' => 'nullable|string',
            'pekerjaan_luar_lingkup' => 'nullable|string',
            'sasaran' => 'nullable|string',
            'batasan' => 'nullable|string',
            'asumsi_dasar' => 'nullable|string',
            'member_nama.*' => 'nullable|string',
            'member_jabatan_id.*' => 'nullable|exists:jabatans,id',
            'stakeholder_internal_stakeholder.*' => 'nullable|string',
            'stakeholder_internal_peran.*' => 'nullable|string',
            'stakeholder_internal_komunikasi.*' => 'nullable|string',
            'stakeholder_external_stakeholder.*' => 'nullable|string',
            'stakeholder_external_peran.*' => 'nullable|string',
            'stakeholder_external_komunikasi.*' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Data utama
            $contextData = [
                'project_id' => $request->project_id,
                'nilai' => $request->nilai,
                'pimpinan_tertinggi_jabatan_id' => $request->pimpinan_tertinggi_jabatan_id,
                'sponsor' => $request->sponsor,
                'deskripsi' => $request->deskripsi,
                'tujuan' => $request->tujuan,
                'lingkup_pekerjaan' => $request->lingkup_pekerjaan,
                'pekerjaan_luar_lingkup' => $request->pekerjaan_luar_lingkup,
                'sasaran' => $request->sasaran,
                'batasan' => $request->batasan,
                'asumsi_dasar' => $request->asumsi_dasar,
            ];

            // Update or Create
            $projectRiskContext = ProjectRiskContext::where('project_id', $request->project_id)->first();

            if ($projectRiskContext) {
                $contextData['status'] = ProjectRiskContext::STATUS_DRAFT;

                // Update
                $projectRiskContext->update($contextData);
                $projectRiskContext->members()->delete();
                $projectRiskContext->stakeholderInternals()->delete();
                $projectRiskContext->stakeholderExternals()->delete();
                $message = 'Project Risk Context berhasil diperbarui.';
            } else {
                // Create
                $projectRiskContext = ProjectRiskContext::create($contextData);
                $message = 'Project Risk Context berhasil dibuat.';
            }

            // Save members
            if ($request->member_nama) {
                foreach ($request->member_nama as $index => $nama) {
                    if ($nama && isset($request->member_jabatan_id[$index])) {
                        ProjectRiskContextMember::create([
                            'project_risk_context_id' => $projectRiskContext->id,
                            'nama' => $nama,
                            'jabatan_id' => $request->member_jabatan_id[$index],
                        ]);
                    }
                }
            }

            // Save stakeholder internal
            if ($request->stakeholder_internal_stakeholder) {
                foreach ($request->stakeholder_internal_stakeholder as $index => $stakeholder) {
                    if ($stakeholder && isset($request->stakeholder_internal_peran[$index]) && isset($request->stakeholder_internal_komunikasi[$index])) {
                        ProjectRiskContextStakeholderInternal::create([
                            'project_risk_context_id' => $projectRiskContext->id,
                            'stakeholder' => $stakeholder,
                            'peran' => $request->stakeholder_internal_peran[$index],
                            'komunikasi' => $request->stakeholder_internal_komunikasi[$index],
                        ]);
                    }
                }
            }

            // Save stakeholder external
            if ($request->stakeholder_external_stakeholder) {
                foreach ($request->stakeholder_external_stakeholder as $index => $stakeholder) {
                    if ($stakeholder && isset($request->stakeholder_external_peran[$index]) && isset($request->stakeholder_external_komunikasi[$index])) {
                        ProjectRiskContextStakeholderExternal::create([
                            'project_risk_context_id' => $projectRiskContext->id,
                            'stakeholder' => $stakeholder,
                            'peran' => $request->stakeholder_external_peran[$index],
                            'komunikasi' => $request->stakeholder_external_komunikasi[$index],
                        ]);
                    }
                }
            }

            $projectPeriodeList = ProjectPeriodeList::findOrFail($request->project_id);

            DB::commit();
            return redirect()->route('project-risk-context.index-by-project-periode', $projectPeriodeList->project_id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $projectRiskContext = ProjectRiskContext::with([
                'members.jabatan',
                'stakeholderInternals',
                'stakeholderExternals',
                'project', // ganti dari unit
                'pimpinanTertinggi'
            ])
            ->findOrFail($id);

        return view('project-risk-context.show', compact('projectRiskContext'));
    }

    public function destroy($id)
    {
        $projectRiskContext = ProjectRiskContext::findOrFail($id);
        $projectRiskContext->delete();

        return redirect()->route('project-risk-context.index')
            ->with('success', 'Project Risk Context berhasil dihapus.');
    }

    // Risk Officer Mengajukan (Submit)
    public function submit($id)
    {
        $user = auth()->user();
        
        // Cek Hak Akses Risk Officer
        if (!($user->level_id == 6 || is_null($user->level_id))) {
            return back()->with('error', 'Akses Ditolak. Hanya Risk Officer yang dapat mengajukan verifikasi.');
        }

        $context = ProjectRiskContext::findOrFail($id);

        // Cek Status Dokumen (Hanya boleh Draft atau Revision)
        if ($context->status !== ProjectRiskContext::STATUS_DRAFT && $context->status !== ProjectRiskContext::STATUS_REVISION) {
            return back()->with('error', 'Status dokumen tidak valid untuk diajukan.');
        }

        $context->update([
            'status' => ProjectRiskContext::STATUS_SUBMITTED,
            'catatan_perbaikan' => null
        ]);

        return back()->with('success', 'Risk Context berhasil diajukan ke Risk Owner.');
    }

    // Risk Owner Menyetujui (Verify)
    public function verify($id)
    {
        $user = auth()->user();

        // Cek Hak Akses Risk Owner
        if ($user->level_id != 7) {
            return back()->with('error', 'Akses Ditolak. Hanya Risk Owner yang dapat menyetujui dokumen ini.');
        }

        $context = ProjectRiskContext::findOrFail($id);
        
        if ($context->status !== ProjectRiskContext::STATUS_SUBMITTED) {
            return back()->with('error', 'Dokumen belum diajukan.');
        }

        $context->update([
            'status' => ProjectRiskContext::STATUS_VERIFIED,
            'verified_by' => $user->id,
            'verified_at' => now(),
            'catatan_perbaikan' => null
        ]);

        return back()->with('success', 'Risk Context berhasil diverifikasi.');
    }

    // Risk Owner Menolak/Minta Revisi (Reject)
    public function reject(Request $request, $id)
    {
        $user = auth()->user();

        // Cek Hak Akses Risk Owner
        if ($user->level_id != 7) {
            return back()->with('error', 'Akses Ditolak. Hanya Risk Owner yang dapat melakukan revisi.');
        }

        $request->validate([
            'catatan_perbaikan' => 'required|string'
        ]);

        $context = ProjectRiskContext::findOrFail($id);

        // if ($context->status !== ProjectRiskContext::STATUS_SUBMITTED) {
        //     return back()->with('error', 'Dokumen belum diajukan.');
        // }

        $context->update([
            'status' => ProjectRiskContext::STATUS_REVISION,
            'catatan_perbaikan' => $request->catatan_perbaikan,
            'verified_by' => null,
            'verified_at' => null
        ]);

        return back()->with('success', 'Risk Context dikembalikan untuk perbaikan.');
    }
}
