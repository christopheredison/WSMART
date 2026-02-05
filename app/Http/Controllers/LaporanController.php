<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Unit;
use App\Models\Project;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanUnitExport;
use App\Exports\LaporanProjectExport;
use App\Exports\LaporanLossEventProjectExport;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    public function korporat()
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $units = Unit::where('unit_type_id', 4)->get();
        $unitId = Unit::where('unit_type_id', 4)->first()->id;

        return view('laporan.korporat', compact('periodes', 'units', 'unitId'));
    }

    public function korporatExport(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'unit_id'    => 'required|exists:units,id',
        ]);

        try {
            $periodeId = $request->input('periode_id');
            $unitId    = $request->input('unit_id');

            $periode = Periode::find($periodeId);
            $unit    = Unit::find($unitId);

            $fileName = 'Laporan_Risk_Register_' . str_replace(' ', '_', $unit->name) . '_' . $periode->tahun . '.xlsx';

            $fileContents = Excel::raw(
                new LaporanUnitExport($periodeId, $unitId),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal export laporan unit: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat laporan Excel. Silakan coba lagi.');
        }
    }

    public function unit()
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $units = Gate::check('risk_register_all_unit') ? Unit::where('unit_type_id', 1)->get() : collect([auth()->user()->unit]);

        return view('laporan.unit', compact('periodes', 'units'));
    }

    public function unitExport(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'unit_id'    => 'required|exists:units,id',
        ]);

        try {
            $periodeId = $request->input('periode_id');
            $unitId    = $request->input('unit_id');

            $periode = Periode::find($periodeId);
            $unit    = Unit::find($unitId);

            $fileName = 'Laporan_Risk_Register_' . str_replace(' ', '_', $unit->name) . '_' . $periode->tahun . '.xlsx';

            $fileContents = Excel::raw(
                new LaporanUnitExport($periodeId, $unitId),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal export laporan unit: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat laporan Excel. Silakan coba lagi.');
        }
    }

    public function ap()
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $units = Gate::check('risk_register_all_unit') ? Unit::where('unit_type_id', 2)->get() : collect([auth()->user()->unit]);

        return view('laporan.ap', compact('periodes', 'units'));
    }

    public function apExport(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'unit_id'    => 'required|exists:units,id',
        ]);

        try {
            $periodeId = $request->input('periode_id');
            $unitId    = $request->input('unit_id');

            $periode = Periode::find($periodeId);
            $unit    = Unit::find($unitId);

            $fileName = 'Laporan_Risk_' . str_replace(' ', '_', $unit->name) . '_' . $periode->tahun . '.xlsx';

            $fileContents = Excel::raw(
                new LaporanUnitExport($periodeId, $unitId),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal export laporan unit: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat laporan Excel. Silakan coba lagi.');
        }
    }

    public function project()
    {
        $user = auth()->user();
        $user->load('projects', 'unit');

        // 1. Ambil ID project yang di-assign langsung (Many-to-Many)
        $userProjectIds = $user->projects->pluck('id');

        // 2. Jika punya akses divisi, ambil project berdasarkan kesamaan Cost Center
        $unitProjectIds = collect([]);
        if ($user->unit && Gate::check('can_access_project_under_division')) {
            $unitProjectIds = \App\Models\Project::where('cost_center_parent', $user->unit->cost_center)
                                ->pluck('id');
        }

        // 3. Gabungkan ID unik yang boleh diakses
        $allAccessibleIds = $userProjectIds->merge($unitProjectIds)->unique();

        // 4. Query untuk dropdown Blade
        $projects = \App\Models\Project::query()
            // Jika punya permission admin, tampilkan SEMUA.
            // Jika TIDAK, filter hanya ID yang boleh diakses.
            ->when(!Gate::check('project_admin_access'), function ($query) use ($allAccessibleIds) {
                return $query->whereIn('id', $allAccessibleIds);
            })
            ->orderBy('project_name', 'asc')
            ->get();

        return view('laporan.project', compact('projects'));
    }

    public function projectExport(Request $request)
    {
        $request->validate([
            'project_ids'   => 'required|array',
            'project_ids.*' => 'string',
        ]);

        try {
            $user = auth()->user();
            $inputIds = $request->input('project_ids');
            $finalProjectIds = [];
            $fileNameProject = '';

            // --- LOGIKA FILTER AKSES (SAMA DENGAN INDEX) ---
            $userProjectIds = $user->projects->pluck('id');
            $unitProjectIds = collect([]);
            if ($user->unit && Gate::check('can_access_project_under_division')) {
                $unitProjectIds = Project::where('cost_center_parent', $user->unit->cost_center)->pluck('id');
            }
            $allAccessibleIds = $userProjectIds->merge($unitProjectIds)->unique()->toArray();

            if (in_array('all', $inputIds)) {
                // Jika pilih 'all', gunakan hanya ID yang boleh diakses user (atau semua jika Admin)
                if (Gate::check('project_admin_access')) {
                    $finalProjectIds = Project::pluck('id')->toArray();
                    $fileNameProject = 'All_Projects';
                } else {
                    $finalProjectIds = $allAccessibleIds;
                    $fileNameProject = 'My_Projects';
                }
            } else {
                // Jika user memilih ID spesifik, validasi ID tersebut apakah memang boleh diakses (Security Check)
                if (!Gate::check('project_admin_access')) {
                    $finalProjectIds = array_intersect($inputIds, $allAccessibleIds);
                } else {
                    $finalProjectIds = $inputIds;
                }

                if (count($finalProjectIds) === 1) {
                    $project = Project::find($finalProjectIds[array_key_first($finalProjectIds)]);
                    $fileNameProject = $project ? str_replace(' ', '_', $project->project_name) : 'Project';
                } else {
                    $fileNameProject = 'Selected_Projects_(' . count($finalProjectIds) . ')';
                }
            }

            if (empty($finalProjectIds)) {
                return response()->json(['message' => 'Tidak ada project yang dapat diakses untuk di-export.'], 403);
            }

            $fileName = 'Laporan_Risk_Register_' . $fileNameProject . '.xlsx';

            $fileContents = Excel::raw(
                new LaporanProjectExport($finalProjectIds),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal export laporan project: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal generate laporan: ' . $e->getMessage()], 500);
        }
    }

    public function projectLedExport(Request $request)
    {
        $request->validate([
            'project_ids'   => 'required|array',
            'project_ids.*' => 'string',
        ]);

        try {
            $user = auth()->user();
            $inputIds = $request->input('project_ids');
            $finalProjectIds = [];
            $fileNameProject = '';

            // --- LOGIKA FILTER AKSES (SAMA DENGAN INDEX) ---
            $userProjectIds = $user->projects->pluck('id');
            $unitProjectIds = collect([]);
            if ($user->unit && Gate::check('can_access_project_under_division')) {
                $unitProjectIds = Project::where('cost_center_parent', $user->unit->cost_center)->pluck('id');
            }
            $allAccessibleIds = $userProjectIds->merge($unitProjectIds)->unique()->toArray();

            if (in_array('all', $inputIds)) {
                if (Gate::check('project_admin_access')) {
                    $finalProjectIds = Project::pluck('id')->toArray();
                    $fileNameProject = 'All_Database_LED';
                } else {
                    $finalProjectIds = $allAccessibleIds;
                    $fileNameProject = 'My_Database_LED';
                }
            } else {
                // Security Check: Hanya izinkan ID yang memang punya akses
                if (!Gate::check('project_admin_access')) {
                    $finalProjectIds = array_intersect($inputIds, $allAccessibleIds);
                } else {
                    $finalProjectIds = $inputIds;
                }

                if (count($finalProjectIds) === 1) {
                    $project = Project::find($finalProjectIds[array_key_first($finalProjectIds)]);
                    $fileNameProject = $project ? str_replace(' ', '_', $project->project_name) : 'Project';
                } else {
                    $fileNameProject = 'Selected_LED_(' . count($finalProjectIds) . ')';
                }
            }

            if (empty($finalProjectIds)) {
                return response()->json(['message' => 'Tidak ada project yang dapat diakses.'], 403);
            }

            $fileName = 'Laporan_Loss_Event_' . $fileNameProject . '.xlsx';

            $fileContents = Excel::raw(
                new LaporanLossEventProjectExport($finalProjectIds),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal export laporan LED: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal generate laporan LED: ' . $e->getMessage()], 500);
        }
    }
}
