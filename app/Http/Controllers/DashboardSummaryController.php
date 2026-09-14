<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\Project;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardSummaryController extends Controller
{
    public function index(Request $request)
    {
        $periodes = Periode::query()
            ->whereNull('deleted_at')
            ->where('status', Periode::STATUS_ACTIVE)
            ->orderByDesc('tahun')
            ->get(['id', 'tahun']);

        $apUnits = Unit::query()
            ->where('unit_type_id', 2)
            ->orderBy('name')
            ->get(['id', 'name']);

        $divisions = Unit::query()
            ->where('unit_type_id', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'cost_center']);

        $defaultPeriode = $periodes->firstWhere('tahun', now()->year)
            ?? $periodes->first(fn (Periode $periode) => (int) $periode->tahun <= now()->year)
            ?? $periodes->first();

        $defaultMonth = (int) $defaultPeriode?->tahun === now()->year ? now()->month : 12;
        $defaultPeriod = $defaultPeriode
            ? sprintf('%04d-%02d', $defaultPeriode->tahun, $defaultMonth)
            : '';
        $requestedPeriod = (string) $request->input('periode', $defaultPeriod);
        $selectedPeriode = null;
        $selectedPeriod = $defaultPeriod;

        if (preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $requestedPeriod, $matches)) {
            $requestedYear = (int) $matches[1];
            $requestedYearMonth = sprintf('%04d-%02d', $requestedYear, (int) $matches[2]);
            $matchingPeriode = $periodes->firstWhere('tahun', $requestedYear);

            if ($matchingPeriode && $requestedYearMonth <= now()->format('Y-m')) {
                $selectedPeriode = $matchingPeriode;
                $selectedPeriod = $requestedYearMonth;
            }
        }

        $selectedPeriode ??= $defaultPeriode;
        $selectedPeriodeId = (string) $selectedPeriode?->id;
        $selectedPeriodDisplay = $selectedPeriod
            ? Carbon::createFromFormat('Y-m', $selectedPeriod)->locale('id')->translatedFormat('F Y')
            : 'Pilih bulan dan tahun';
        $minimumPeriod = $periodes->min('tahun')
            ? sprintf('%04d-01', $periodes->min('tahun'))
            : '';
        $maximumPeriod = now()->format('Y-m');

        $selectedKorporasi = (string) $request->input('korporasi', 'induk');
        $validApIds = $apUnits->pluck('id')->map(fn ($id) => (string) $id);

        if ($selectedKorporasi !== 'induk' && !$validApIds->contains($selectedKorporasi)) {
            $selectedKorporasi = 'induk';
        }

        $isApSelected = $selectedKorporasi !== 'induk';
        $selectedDivision = $isApSelected
            ? 'none'
            : (string) $request->input('divisi', 'none');

        if (
            !in_array($selectedDivision, ['none', 'all'], true)
            && !$divisions->contains(fn (Unit $division) => (string) $division->id === $selectedDivision)
        ) {
            $selectedDivision = 'none';
        }

        $projects = collect();
        $requestedProject = (string) $request->input('proyek', 'none');
        $selectedProject = $selectedDivision === 'all' && in_array($requestedProject, ['none', 'all'], true)
            ? $requestedProject
            : 'none';
        $selectedDivisionModel = null;

        if (!$isApSelected && !in_array($selectedDivision, ['none', 'all'], true)) {
            $selectedDivisionModel = $divisions->first(
                fn (Unit $division) => (string) $division->id === $selectedDivision
            );

            $projects = Project::query()
                ->where('cost_center_parent', $selectedDivisionModel->cost_center)
                ->orderBy('project_name')
                ->get(['id', 'project_name']);

            if (
                in_array($requestedProject, ['none', 'all'], true)
                || $projects->contains(fn (Project $project) => (string) $project->id === $requestedProject)
            ) {
                $selectedProject = $requestedProject;
            }
        }

        $dashboardCategory = match (true) {
            $isApSelected => 'ap',
            $selectedDivision === 'none' => 'corporate',
            $selectedDivision === 'all' && $selectedProject === 'all' => 'consolidated-all-projects',
            $selectedDivision === 'all' => 'consolidated-division',
            $selectedProject === 'all' => 'consolidated-project',
            $selectedProject === 'none' => 'division',
            default => 'project',
        };

        $selectedApUnit = $isApSelected
            ? $apUnits->first(fn (Unit $unit) => (string) $unit->id === $selectedKorporasi)
            : null;
        $selectedProjectModel = !in_array($selectedProject, ['none', 'all'], true)
            ? $projects->first(fn (Project $project) => (string) $project->id === $selectedProject)
            : null;

        $dashboardTitle = match ($dashboardCategory) {
            'ap' => 'Dashboard AP — ' . ($selectedApUnit?->name ?? 'Anak Perusahaan'),
            'consolidated-division' => 'Dashboard Konsolidasi Divisi',
            'consolidated-all-projects' => 'Dashboard Konsolidasi Seluruh Proyek',
            'division' => 'Dashboard Divisi — ' . ($selectedDivisionModel?->name ?? 'Divisi'),
            'consolidated-project' => 'Dashboard Konsolidasi Project — ' . ($selectedDivisionModel?->name ?? 'Divisi'),
            'project' => 'Dashboard Project — ' . ($selectedProjectModel?->project_name ?? 'Proyek'),
            default => 'Dashboard Induk',
        };

        return view('dashboard-summary.dashboard-summary', compact(
            'periodes',
            'apUnits',
            'divisions',
            'projects',
            'selectedPeriodeId',
            'selectedPeriod',
            'selectedPeriodDisplay',
            'minimumPeriod',
            'maximumPeriod',
            'selectedKorporasi',
            'selectedDivision',
            'selectedProject',
            'isApSelected',
            'dashboardCategory',
            'dashboardTitle',
            'selectedApUnit',
            'selectedDivisionModel',
            'selectedProjectModel',
        ));
    }

    public function projects(Request $request): JsonResponse
    {
        $division = Unit::query()
            ->where('unit_type_id', 1)
            ->findOrFail($request->integer('division_id'));

        $projects = Project::query()
            ->where('cost_center_parent', $division->cost_center)
            ->orderBy('project_name')
            ->get(['id', 'project_name']);

        return response()->json($projects);
    }
}
