<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PeristiwaRisiko;
use App\Models\ProjectPeriodeList;
use App\Models\SasaranProyek;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SasaranProyekApprovalController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($this->canVerify(), 403);

        $sasaranItems = SasaranProyek::with(['requester', 'verifier', 'projectPeriodeList.project'])
            ->where('status', 2)
            ->orderByRaw('CASE WHEN approval_status = 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->get();

        $peristiwaItems = PeristiwaRisiko::with(['requester', 'verifier', 'project', 'projectPeriodeList.project'])
            ->where('status', PeristiwaRisiko::STATUS_CUSTOM)
            ->orderByRaw('CASE WHEN approval_status = 0 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingSasaranCount = $sasaranItems->where('approval_status', SasaranProyek::APPROVAL_PENDING)->count();
        $pendingPeristiwaCount = $peristiwaItems->where('approval_status', PeristiwaRisiko::APPROVAL_PENDING)->count();
        $activeTab = in_array($request->get('tab'), ['sasaran', 'peristiwa'], true)
            ? $request->get('tab')
            : 'sasaran';

        return view('project-risk.sasaran-approval.index', compact(
            'sasaranItems',
            'peristiwaItems',
            'pendingSasaranCount',
            'pendingPeristiwaCount',
            'activeTab'
        ));
    }

    public function existing()
    {
        abort_unless($this->canVerify(), 403);

        $items = SasaranProyek::with(['requester', 'verifier', 'projectPeriodeList.project'])
            ->where(function ($q) {
                $q->where('status', 1)
                    ->orWhere(function ($q2) {
                        $q2->where('status', 2)
                            ->where('approval_status', SasaranProyek::APPROVAL_APPROVED);
                    });
            })
            ->orderByRaw('CASE WHEN status = 2 THEN 0 ELSE 1 END')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('project-risk.sasaran-approval.existing', compact('items'));
    }

    public function submit(Request $request, ProjectPeriodeList $project)
    {
        $request->validate([
            'kpi_desc' => 'required|string|min:10|max:1000',
        ], [
            'kpi_desc.required' => 'Mohon isi deskripsi sasaran yang diajukan.',
            'kpi_desc.min' => 'Deskripsi sasaran minimal 10 karakter.',
        ]);

        $projectModel = $project->project;
        $profitCenter = $projectModel->meta['profit_center'] ?? $projectModel->costcenter_code ?? null;

        $item = SasaranProyek::create([
            'costcenter_code' => $profitCenter,
            'project_periode_list_id' => $project->id,
            'requested_by' => $request->user()->id,
            'kpi_desc' => trim($request->kpi_desc),
            'status' => 2,
            'approval_status' => SasaranProyek::APPROVAL_PENDING,
        ]);

        $link = route('projects.verifikasi-pengajuan.index');
        $message = 'Ada pengajuan sasaran lainnya untuk proyek ' . ($projectModel->project_name ?? '-') . ' yang menunggu verifikasi Anda.';
        $this->sendNotificationCustom('RO_MR', $project->id, 'Pengajuan Sasaran Lainnya', $message, $link, 'bx bx-bell');
        $this->sendNotificationCustom('RW_MR', $project->id, 'Pengajuan Sasaran Lainnya', $message, $link, 'bx bx-bell');

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan sasaran berhasil dikirim. Tunggu persetujuan Risk Officer / Risk Owner MR.',
            'data' => $item,
        ]);
    }

    public function approve(Request $request, SasaranProyek $sasaranProyek)
    {
        abort_unless($this->canVerify(), 403);
        abort_unless($sasaranProyek->status == 2, 404);

        if ($sasaranProyek->approval_status !== SasaranProyek::APPROVAL_PENDING) {
            return redirect()
                ->route('projects.verifikasi-pengajuan.index', ['tab' => 'sasaran'])
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $sasaranProyek->update([
            'approval_status' => SasaranProyek::APPROVAL_APPROVED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejected_reason' => null,
        ]);

        if ($sasaranProyek->project_periode_list_id) {
            $projectName = optional(optional($sasaranProyek->projectPeriodeList)->project)->project_name ?? '-';
            $verifierName = $request->user()->name ?? 'MR';
            $msg = 'Pengajuan sasaran lainnya untuk proyek ' . $projectName . ' telah disetujui oleh ' . $verifierName . ' dan sudah dapat dipilih pada form risiko proyek.';
            $link = route('projects.risks.create', ['project' => $sasaranProyek->project_periode_list_id]);
            $this->sendNotificationCustom('RO_PROYEK', $sasaranProyek->project_periode_list_id, 'Sasaran Lainnya Disetujui', $msg, $link, 'bx bx-check-circle');
            $this->sendNotificationCustom('RW_PROYEK', $sasaranProyek->project_periode_list_id, 'Sasaran Lainnya Disetujui', $msg, $link, 'bx bx-check-circle');
        }

        return redirect()
            ->route('projects.verifikasi-pengajuan.index', ['tab' => 'sasaran'])
            ->with('success', 'Pengajuan sasaran berhasil disetujui oleh ' . ($request->user()->name ?? 'Anda') . '.');
    }

    public function reject(Request $request, SasaranProyek $sasaranProyek)
    {
        abort_unless($this->canVerify(), 403);
        abort_unless($sasaranProyek->status == 2, 404);

        $request->validate([
            'rejected_reason' => 'required|string|max:2000',
        ], [
            'rejected_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if ($sasaranProyek->approval_status !== SasaranProyek::APPROVAL_PENDING) {
            return redirect()
                ->route('projects.verifikasi-pengajuan.index', ['tab' => 'sasaran'])
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $sasaranProyek->update([
            'approval_status' => SasaranProyek::APPROVAL_REJECTED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejected_reason' => trim($request->rejected_reason),
        ]);

        if ($sasaranProyek->project_periode_list_id) {
            $projectName = optional(optional($sasaranProyek->projectPeriodeList)->project)->project_name ?? '-';
            $verifierName = $request->user()->name ?? 'MR';
            $msg = 'Pengajuan sasaran lainnya untuk proyek ' . $projectName . ' ditolak oleh ' . $verifierName . '. Alasan: ' . $sasaranProyek->rejected_reason;
            $link = route('projects.risks.create', ['project' => $sasaranProyek->project_periode_list_id]);
            $this->sendNotificationCustom('RO_PROYEK', $sasaranProyek->project_periode_list_id, 'Sasaran Lainnya Ditolak', $msg, $link, 'bx bx-x-circle');
            $this->sendNotificationCustom('RW_PROYEK', $sasaranProyek->project_periode_list_id, 'Sasaran Lainnya Ditolak', $msg, $link, 'bx bx-x-circle');
        }

        return redirect()
            ->route('projects.verifikasi-pengajuan.index', ['tab' => 'sasaran'])
            ->with('success', 'Pengajuan sasaran berhasil ditolak oleh ' . ($request->user()->name ?? 'Anda') . '.');
    }

    private function canVerify(): bool
    {
        $user = request()->user();

        return Gate::allows('verification_mr')
            && in_array($user->level_id, [1, 2], true)
            && optional($user->unit)->unit_mr == 1;
    }

    private function sendNotificationCustom($target, $projectPeriodeListId, $title, $message, $link, $icon): void
    {
        $users = collect();
        $projectPeriodeList = ProjectPeriodeList::with('project')->find($projectPeriodeListId);
        if (!$projectPeriodeList || !$projectPeriodeList->project) {
            return;
        }

        $project = $projectPeriodeList->project;
        $unitId = Unit::where('cost_center', $project->cost_center_parent)->first()?->id;

        if ($target === 'RO_PROYEK') {
            $users = User::where('level_id', 6)
                ->whereHas('projects', function ($q) use ($project) {
                    $q->where('projects.id', $project->id);
                })->get();
        } elseif ($target === 'RW_PROYEK') {
            $users = User::where('level_id', 7)
                ->whereHas('projects', function ($q) use ($project) {
                    $q->where('projects.id', $project->id);
                })->get();
        } elseif ($target === 'RO_MR') {
            $users = User::permission('mr_notification_project')
                ->where('level_id', 1)
                ->whereHas('unit', function ($q) {
                    $q->where('unit_mr', 1);
                })->get();
        } elseif ($target === 'RW_MR') {
            $users = User::permission('mr_notification_project')
                ->where('level_id', 2)
                ->whereHas('unit', function ($q) {
                    $q->where('unit_mr', 1);
                })->get();
        } elseif ($target === 'RO_DIVISI' && $unitId) {
            $users = User::where('level_id', 1)->where('unit_id', $unitId)->get();
        }

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'icon' => $icon,
                'link' => $link,
                'read_at' => null,
            ]);
        }
    }
}
