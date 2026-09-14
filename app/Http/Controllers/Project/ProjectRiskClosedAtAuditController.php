<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Project;
use App\Models\ProjectRisk;
use Illuminate\Http\Request;

class ProjectRiskClosedAtAuditController extends Controller
{
    public function index(Request $request)
    {
        $projectId = $request->query('project_id');
        $riskId = $request->query('risk_id');

        $projects = Project::query()
            ->orderBy('project_name')
            ->get(['id', 'project_code', 'project_name']);

        $audits = Audit::query()
            ->with([
                'user',
                'projectRisk' => function ($query) {
                    $query->withTrashed()->with('project');
                },
            ])
            ->where('auditable_type', ProjectRisk::class)
            ->where('tags', 'like', '%sync-closed-at%')
            ->when($riskId, function ($query) use ($riskId) {
                $query->where('auditable_id', $riskId);
            })
            ->when($projectId, function ($query) use ($projectId) {
                $query->whereHas('projectRisk', function ($riskQuery) use ($projectId) {
                    $riskQuery->withTrashed()->where('project_id', $projectId);
                });
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('project-risk.closed-at-audits', compact('audits', 'projects', 'projectId', 'riskId'));
    }
}
