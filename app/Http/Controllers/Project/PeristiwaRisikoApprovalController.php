<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PeristiwaRisiko;
use App\Models\Project;
use App\Models\ProjectPeriodeList;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PeristiwaRisikoApprovalController extends Controller
{
    public function submit(Request $request, ProjectPeriodeList $project)
    {
        return $this->storeSubmission($request, $project, $project->project);
    }

    public function submitFromLed(Request $request, Project $project)
    {
        $periodeList = ProjectPeriodeList::where('project_id', $project->id)->latest('id')->first();

        return $this->storeSubmission($request, $periodeList, $project);
    }

    public function existing()
    {
        abort_unless($this->canVerify(), 403);

        $items = PeristiwaRisiko::with(['requester', 'verifier', 'project', 'projectPeriodeList.project'])
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('type', 2)
                        ->where(function ($q3) {
                            $q3->whereNull('status')
                                ->orWhere('status', PeristiwaRisiko::STATUS_MASTER);
                        });
                })->orWhere(function ($q2) {
                    $q2->where('status', PeristiwaRisiko::STATUS_CUSTOM)
                        ->where('approval_status', PeristiwaRisiko::APPROVAL_APPROVED);
                });
            })
            ->orderByRaw('CASE WHEN status = 2 THEN 0 ELSE 1 END')
            ->orderBy('title')
            ->get();

        return view('project-risk.sasaran-approval.existing-peristiwa', compact('items'));
    }

    public function approve(Request $request, PeristiwaRisiko $peristiwaRisiko)
    {
        abort_unless($this->canVerify(), 403);
        abort_unless((int) $peristiwaRisiko->status === PeristiwaRisiko::STATUS_CUSTOM, 404);

        if ((int) $peristiwaRisiko->approval_status !== PeristiwaRisiko::APPROVAL_PENDING) {
            return redirect()
                ->route('projects.verifikasi-pengajuan.index', ['tab' => 'peristiwa'])
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $peristiwaRisiko->update([
            'approval_status' => PeristiwaRisiko::APPROVAL_APPROVED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejected_reason' => null,
        ]);

        $this->notifyProjectUsers(
            $peristiwaRisiko,
            'Peristiwa Risiko Lainnya Disetujui',
            'Pengajuan peristiwa risiko lainnya untuk proyek ' . $this->projectName($peristiwaRisiko) . ' telah disetujui oleh ' . ($request->user()->name ?? 'MR') . ' dan sudah dapat dipilih pada form risiko / loss event proyek.',
            'bx bx-check-circle'
        );

        return redirect()
            ->route('projects.verifikasi-pengajuan.index', ['tab' => 'peristiwa'])
            ->with('success', 'Pengajuan peristiwa risiko berhasil disetujui oleh ' . ($request->user()->name ?? 'Anda') . '.');
    }

    public function reject(Request $request, PeristiwaRisiko $peristiwaRisiko)
    {
        abort_unless($this->canVerify(), 403);
        abort_unless((int) $peristiwaRisiko->status === PeristiwaRisiko::STATUS_CUSTOM, 404);

        $request->validate([
            'rejected_reason' => 'required|string|max:2000',
        ], [
            'rejected_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        if ((int) $peristiwaRisiko->approval_status !== PeristiwaRisiko::APPROVAL_PENDING) {
            return redirect()
                ->route('projects.verifikasi-pengajuan.index', ['tab' => 'peristiwa'])
                ->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $peristiwaRisiko->update([
            'approval_status' => PeristiwaRisiko::APPROVAL_REJECTED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejected_reason' => trim($request->rejected_reason),
        ]);

        $this->notifyProjectUsers(
            $peristiwaRisiko,
            'Peristiwa Risiko Lainnya Ditolak',
            'Pengajuan peristiwa risiko lainnya untuk proyek ' . $this->projectName($peristiwaRisiko) . ' ditolak oleh ' . ($request->user()->name ?? 'MR') . '. Alasan: ' . $peristiwaRisiko->rejected_reason,
            'bx bx-x-circle'
        );

        return redirect()
            ->route('projects.verifikasi-pengajuan.index', ['tab' => 'peristiwa'])
            ->with('success', 'Pengajuan peristiwa risiko berhasil ditolak oleh ' . ($request->user()->name ?? 'Anda') . '.');
    }

    private function storeSubmission(Request $request, ?ProjectPeriodeList $periodeList, ?Project $projectModel)
    {
        $request->validate([
            'title' => 'required|string|min:10|max:255',
        ], [
            'title.required' => 'Mohon isi deskripsi peristiwa risiko yang diajukan.',
            'title.min' => 'Deskripsi peristiwa risiko minimal 10 karakter.',
        ]);

        if (!$projectModel) {
            return response()->json([
                'success' => false,
                'message' => 'Proyek tidak ditemukan.',
            ], 404);
        }

        $item = PeristiwaRisiko::create([
            'title' => trim($request->title),
            'deskripsi' => trim($request->title),
            'type' => 2,
            'status' => PeristiwaRisiko::STATUS_CUSTOM,
            'approval_status' => PeristiwaRisiko::APPROVAL_PENDING,
            'project_id' => $projectModel->id,
            'project_periode_list_id' => $periodeList?->id,
            'requested_by' => $request->user()->id,
            'kategori_risiko_id' => null,
            'jenis_risiko_id' => null,
        ]);

        $link = route('projects.verifikasi-pengajuan.index', ['tab' => 'peristiwa']);
        $message = 'Ada pengajuan peristiwa risiko lainnya untuk proyek ' . ($projectModel->project_name ?? '-') . ' yang menunggu verifikasi Anda.';

        if ($periodeList) {
            $this->sendNotificationCustom('RO_MR', $periodeList->id, 'Pengajuan Peristiwa Risiko Lainnya', $message, $link, 'bx bx-bell');
            $this->sendNotificationCustom('RW_MR', $periodeList->id, 'Pengajuan Peristiwa Risiko Lainnya', $message, $link, 'bx bx-bell');
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan peristiwa risiko berhasil dikirim. Tunggu persetujuan Risk Officer / Risk Owner MR.',
            'data' => $item,
        ]);
    }

    private function notifyProjectUsers(PeristiwaRisiko $peristiwaRisiko, string $title, string $message, string $icon): void
    {
        $periodeListId = $peristiwaRisiko->project_periode_list_id;
        if (!$periodeListId) {
            return;
        }

        $link = route('projects.risks.create', ['project' => $periodeListId]);
        $this->sendNotificationCustom('RO_PROYEK', $periodeListId, $title, $message, $link, $icon);
        $this->sendNotificationCustom('RW_PROYEK', $periodeListId, $title, $message, $link, $icon);
    }

    private function projectName(PeristiwaRisiko $peristiwaRisiko): string
    {
        return optional($peristiwaRisiko->project)->project_name
            ?? optional(optional($peristiwaRisiko->projectPeriodeList)->project)->project_name
            ?? '-';
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
