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
        $user = request()->user();
        // 1. Cek Permission Admin: Ambil Semua Data
        if (Gate::check('project_admin_access')) {
            $projects = Project::all();
        }
        // 2. Jika bukan admin, filter berdasarkan akses
        else {
            $projects = Project::where(function ($query) use ($user) {

                // A. Logika "hasProject": Ambil project yang di-assign ke user ini
                // Pastikan di Model 'Project' ada relasi public function users()
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });

                // B. Logika "can_access_project_under_division"
                // Jika punya permission DAN user punya unit
                if (Gate::check('can_access_project_under_division') && $user->unit) {
                    // Gunakan orWhere karena ini opsi tambahan (User Assigned ATAU Satu Cost Center)
                    $query->orWhere('cost_center_parent', $user->unit->cost_center);
                }

            })->get();
        }

        return view('laporan.project', compact('projects'));
    }

    public function projectExport(Request $request)
    {
        $request->validate([
            'project_ids'   => 'required|array',
            'project_ids.*' => 'string',
        ]);

        try {
            $inputIds = $request->input('project_ids');
            $finalProjectIds = [];
            $fileNameProject = '';

            // LOGIKA: Cek apakah user memilih "Pilih Semua Project" ('all')
            if (in_array('all', $inputIds)) {
                // Ambil semua ID project dari database
                $finalProjectIds = Project::pluck('id')->toArray();
                $fileNameProject = 'All_Projects';
            } else {
                // Gunakan ID yang dipilih saja
                $finalProjectIds = $inputIds;

                // Logika Penamaan File
                if (count($finalProjectIds) === 1) {
                    // Jika cuma 1 project, pakai nama projectnya
                    $project = Project::find($finalProjectIds[0]);
                    $fileNameProject = $project ? str_replace(' ', '_', $project->project_name) : 'Project';
                } else {
                    // Jika banyak project
                    $fileNameProject = 'Multiple_Projects_(' . count($finalProjectIds) . ')';
                }
            }

            $fileName = 'Laporan_Risk_Register_' . $fileNameProject . '.xlsx';

            // Pastikan class LaporanProjectExport sudah support constructor array (sesuai jawaban sebelumnya)
            $fileContents = Excel::raw(
                new LaporanProjectExport($finalProjectIds),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            // dd($e->getMessage()); // Debugging only
            Log::error('Gagal export laporan project: ' . $e->getMessage());

            // Karena request via AJAX dan response blob, return JSON error code 500
            return response()->json(['message' => 'Gagal generate laporan: ' . $e->getMessage()], 500);
        }
    }

    public function projectLedExport(Request $request)
    {
        // 1. Validasi array project_ids (bukan project_id singular)
        $request->validate([
            'project_ids'   => 'required|array',
            'project_ids.*' => 'string', // Bisa 'all' atau ID numeric
        ]);

        try {
            $inputIds = $request->input('project_ids');
            $finalProjectIds = [];
            $fileNameProject = '';

            // 2. LOGIKA PILIH PROJECT (Sama persis dengan projectExport)
            if (in_array('all', $inputIds)) {
                // Ambil semua ID project dari database
                $finalProjectIds = Project::pluck('id')->toArray();
                $fileNameProject = 'All_Projects';
            } else {
                // Gunakan ID yang dipilih saja
                $finalProjectIds = $inputIds;

                // Logika Penamaan File
                if (count($finalProjectIds) === 1) {
                    // Jika cuma 1 project, pakai nama projectnya
                    $project = Project::find($finalProjectIds[0]);
                    $fileNameProject = $project ? str_replace(' ', '_', $project->project_name) : 'Project';
                } else {
                    // Jika banyak project
                    $fileNameProject = 'Multiple_Projects_(' . count($finalProjectIds) . ')';
                }
            }

            $fileName = 'Laporan_Loss_Event_' . $fileNameProject . '.xlsx';

            // 3. Generate Excel dengan Array ID
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
            // Return JSON error agar ditangkap oleh AJAX di frontend
            return response()->json(['message' => 'Gagal generate laporan LED: ' . $e->getMessage()], 500);
        }
    }
}
