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
use App\Exports\LaporanLossEventUnitExport;
use App\Exports\LaporanRiskRegisterBaruExport;
use App\Exports\Support\MonitoringPeriodResolver;
use Illuminate\Support\Facades\Log;

class LaporanController extends Controller
{
    private function resolveUnitIdsForExport(Request $request, int $unitTypeId, string $allUnitsPermission): array
    {
        $unitId = $request->input('unit_id');

        if ($unitId === 'all') {
            if (!Gate::check($allUnitsPermission)) {
                abort(403, 'Anda tidak memiliki akses untuk export semua unit.');
            }

            $unitIds = Unit::where('unit_type_id', $unitTypeId)
                ->orderBy('name')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (empty($unitIds)) {
                abort(422, 'Tidak ada unit yang tersedia untuk di-export.');
            }

            $fileNameUnit = $unitTypeId === 2 ? 'Semua_Anak_Perusahaan' : 'Semua_Divisi';
            $unitColumnLabel = $unitTypeId === 2 ? 'Nama Anak Perusahaan' : 'Nama Divisi';

            return [
                'unitIds' => $unitIds,
                'includeUnitColumn' => true,
                'unitColumnLabel' => $unitColumnLabel,
                'fileNameUnit' => $fileNameUnit,
            ];
        }

        $unit = Unit::findOrFail($unitId);

        if ((int) $unit->unit_type_id !== $unitTypeId) {
            abort(422, 'Unit yang dipilih tidak valid untuk laporan ini.');
        }

        return [
            'unitIds' => [(int) $unit->id],
            'includeUnitColumn' => false,
            'unitColumnLabel' => $unitTypeId === 2 ? 'Nama Anak Perusahaan' : 'Nama Divisi',
            'fileNameUnit' => str_replace(' ', '_', $unit->name),
        ];
    }

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
            'month'      => 'nullable|integer|between:1,12',
        ]);

        try {
            $periodeId = $request->input('periode_id');
            $unitId    = $request->input('unit_id');
            $month     = $request->input('month');

            $periode = Periode::find($periodeId);
            $unit    = Unit::find($unitId);

            // Laporan memotret bulan monitoring terakhir yang sudah selesai seluruhnya.
            $month = MonitoringPeriodResolver::forUnits($periodeId, [(int) $unitId], $month);

            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $monthString = $month ? '_' . $namaBulan[(int)$month] : '';

            $fileName = 'Laporan_Risk_Register_' . str_replace(' ', '_', $unit->name) . $monthString . '_' . $periode->tahun . '.xlsx';

            $fileContents = Excel::raw(
                new LaporanUnitExport($periodeId, [(int) $unitId], $month),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal export laporan korporat: ' . $e->getMessage());

            return response()->json(['message' => 'Gagal membuat laporan Excel: ' . $e->getMessage()], 500);
        }
    }

    public function unit()
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $user = auth()->user();

        if (Gate::check('risk_register_all_unit')) {
            $units = Unit::where('unit_type_id', 1)
                         ->orderBy('status', 'desc')
                         ->orderBy('name', 'asc')
                         ->get();
        } else {
            // Ambil unit milik user dan unit-unit lama yang direlasikan
            $relatedUnitIds = \App\Models\UnitRelation::where('unit_id', $user->unit_id)->pluck('related_unit_id')->toArray();
            $myUnitIds = array_merge([$user->unit_id], $relatedUnitIds);

            $units = Unit::where('unit_type_id', 1)
                         ->whereIn('id', $myUnitIds)
                         ->orderBy('status', 'desc')
                         ->orderBy('name', 'asc')
                         ->get();
        }

        $canExportAll = Gate::check('view_all_division');

        return view('laporan.unit', compact('periodes', 'units', 'canExportAll'));
    }

    public function unitExport(Request $request)
    {
        $request->validate([
            'periode_id'     => 'required|exists:periodes,id',
            'unit_id'        => 'required',
            'month'          => 'nullable|integer|between:1,12',
            'format_laporan' => 'required|in:lama,baru',
        ]);

        try {
            $periodeId = (int) $request->input('periode_id');
            $month     = $request->input('month');
            $format    = $request->input('format_laporan');
            $resolved  = $this->resolveUnitIdsForExport($request, 1, 'view_all_division');

            $periode = Periode::findOrFail($periodeId);

            // Laporan memotret bulan monitoring terakhir yang sudah selesai seluruhnya.
            $month = MonitoringPeriodResolver::forUnits($periodeId, $resolved['unitIds'], $month);

            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $monthString = $month ? '_' . $namaBulan[(int)$month] : '';

            // Tentukan Class Export berdasarkan pilihan
            if ($format === 'baru') {
                $fileName = 'Laporan_Risk_Register_New_Format_' . $resolved['fileNameUnit'] . $monthString . '_' . $periode->tahun . '.xlsx';
                $exportClass = new LaporanRiskRegisterBaruExport(
                    $periodeId,
                    $resolved['unitIds'],
                    $month,
                    $resolved['includeUnitColumn'],
                    $resolved['unitColumnLabel']
                );
            } else {
                $fileName = 'Laporan_Risk_Register_' . $resolved['fileNameUnit'] . $monthString . '_' . $periode->tahun . '.xlsx';
                $exportClass = new LaporanUnitExport(
                    $periodeId,
                    $resolved['unitIds'],
                    $month,
                    $resolved['includeUnitColumn'],
                    $resolved['unitColumnLabel']
                );
            }

            $fileContents = Excel::raw(
                $exportClass,
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal export laporan unit: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal generate laporan: ' . $e->getMessage()], 500);
        }
    }

    public function ap()
    {
        $periodes = Periode::orderBy('tahun', 'desc')->get();
        $units = Gate::check('ap_admin') ? Unit::where('unit_type_id', 2)->get() : collect([auth()->user()->unit]);

        $canExportAll = Gate::check('ap_admin');

        return view('laporan.ap', compact('periodes', 'units', 'canExportAll'));
    }

    public function apExport(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'unit_id'    => 'required',
            'month'      => 'nullable|integer|between:1,12',
            'format_laporan' => 'required|in:lama,baru',
        ]);

        try {
            $periodeId = (int) $request->input('periode_id');
            $month     = $request->input('month');
            $format    = $request->input('format_laporan');
            $resolved  = $this->resolveUnitIdsForExport($request, 2, 'ap_admin');

            $periode = Periode::findOrFail($periodeId);

            // Laporan memotret bulan monitoring terakhir yang sudah selesai seluruhnya.
            $month = MonitoringPeriodResolver::forUnits($periodeId, $resolved['unitIds'], $month);

            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $monthString = $month ? '_' . $namaBulan[(int)$month] : '';

             // Tentukan Class Export berdasarkan pilihan
            if ($format === 'baru') {
                $fileName = 'Laporan_Risk_Register_New_Format_' . $resolved['fileNameUnit'] . $monthString . '_' . $periode->tahun . '.xlsx';
                $exportClass = new LaporanRiskRegisterBaruExport(
                    $periodeId,
                    $resolved['unitIds'],
                    $month,
                    $resolved['includeUnitColumn'],
                    $resolved['unitColumnLabel']
                );
            } else {
                $fileName = 'Laporan_Risk_Register_' . $resolved['fileNameUnit'] . $monthString . '_' . $periode->tahun . '.xlsx';
                $exportClass = new LaporanUnitExport(
                    $periodeId,
                    $resolved['unitIds'],
                    $month,
                    $resolved['includeUnitColumn'],
                    $resolved['unitColumnLabel']
                );
            }

            $fileContents = Excel::raw(
                $exportClass,
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal export laporan AP: ' . $e->getMessage());

            return response()->json(['message' => 'Gagal generate laporan: ' . $e->getMessage()], 500);
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

        $divisis = \App\Models\Project::query()
            ->join('units', 'projects.cost_center_parent', '=', 'units.cost_center')
            ->select('units.name', 'units.cost_center', 'units.id')
            ->distinct()
            ->orderBy('units.name', 'asc')
            ->get();

        return view('laporan.project', compact('projects', 'divisis'));
    }

    public function projectExport(Request $request)
    {
        $request->validate([
            'project_ids'   => 'required|array',
            'project_ids.*' => 'string',
            'month'         => 'nullable|integer|between:1,12',
            'tahun'         => 'nullable|integer',
        ]);

        try {
            $user = auth()->user();
            $inputIds = $request->input('project_ids');
            $finalProjectIds = [];
            $fileNameProject = '';

            $month = $request->input('month');
            $tahun = $request->input('tahun');

            $namaBulan = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ];

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
                    // Optimasi: untuk admin tidak perlu pluck semua ID (hemat memori & query whereIn besar).
                    $finalProjectIds = null;
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

                $finalProjectIds = collect($finalProjectIds)
                    ->filter(fn ($id) => $id !== null && $id !== '' && $id !== 'all')
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                if (count($finalProjectIds) === 1) {
                    $project = Project::find($finalProjectIds[array_key_first($finalProjectIds)]);
                    $fileNameProject = $project ? str_replace(' ', '_', $project->project_name) : 'Project';
                } else {
                    $fileNameProject = 'Selected_Projects_(' . count($finalProjectIds) . ')';
                }
            }

            if (is_array($finalProjectIds) && empty($finalProjectIds)) {
                return response()->json(['message' => 'Tidak ada project yang dapat diakses untuk di-export.'], 403);
            }

            // Laporan memotret bulan monitoring terakhir yang sudah selesai seluruhnya.
            $month = MonitoringPeriodResolver::forProjects($finalProjectIds, $month, $tahun);

            $monthString = $month ? $namaBulan[(int)$month] : '';

            // Rakit nama file
            $fileName = 'Laporan_Risk_Register_' . $fileNameProject . '_' . $monthString . '_' . $tahun . '.xlsx';

            // Bersihkan jika ada double underscore (misal jika month kosong)
            $fileName = str_replace('__', '_', $fileName);

            $fileContents = Excel::raw(
                new LaporanProjectExport($finalProjectIds, $month, $tahun),
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

    public function projectKonsolidasiExport(Request $request)
    {
        if (!\Illuminate\Support\Facades\Gate::check('report_consolidation')) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk membuat Laporan Konsolidasi.'], 403);
        }

        $request->validate([
            'divisi_ids'   => 'required|array',
            'month'        => 'required|integer|between:1,12',
            'tahun'        => 'required|integer',
            'status_publish' => 'nullable|string|in:published,unpublished,all'
        ]);

        try {
            $costCenters = $request->input('divisi_ids');
            $month = $request->input('month');
            $tahun = $request->input('tahun');

            // Sewaktu-waktu bisa diganti menjadi 'all', 'published' atau 'unpublished'
            $statusPublish = $request->input('status_publish', 'published');

            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            $monthString = $namaBulan[(int)$month] ?? $month;

            $fileName = 'Laporan_Konsolidasi_Operasi_' . $monthString . '_' . $tahun . '.xlsx';

            $fileContents = \Maatwebsite\Excel\Facades\Excel::raw(
                new \App\Exports\LaporanKonsolidasiExport($costCenters, $month, $tahun, $statusPublish),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal export laporan Konsolidasi: ' . $e->getMessage());
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

    public function unitLedExport(Request $request)
    {
        $request->validate([
            'unit_id'    => 'required',
            'periode_id' => 'required|exists:periodes,id',
        ]);

        try {
            $resolved  = $this->resolveUnitIdsForExport($request, 1, 'view_all_division');
            $periodeId = (int) $request->input('periode_id');
            $periode   = Periode::findOrFail($periodeId);

            $fileName = 'Laporan_Loss_Event_' . $resolved['fileNameUnit'] . '_' . $periode->tahun . '.xlsx';

            $fileContents = Excel::raw(
                new LaporanLossEventUnitExport($resolved['unitIds'], 'unit', $periodeId),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal export laporan LED divisi: ' . $e->getMessage());

            return response()->json(['message' => 'Gagal generate laporan LED: ' . $e->getMessage()], 500);
        }
    }

    public function apLedExport(Request $request)
    {
        $request->validate([
            'unit_id'    => 'required',
            'periode_id' => 'required|exists:periodes,id',
        ]);

        try {
            $resolved  = $this->resolveUnitIdsForExport($request, 2, 'ap_admin');
            $periodeId = (int) $request->input('periode_id');
            $periode   = Periode::findOrFail($periodeId);

            $fileName = 'Laporan_Loss_Event_' . $resolved['fileNameUnit'] . '_' . $periode->tahun . '.xlsx';

            $fileContents = Excel::raw(
                new LaporanLossEventUnitExport($resolved['unitIds'], 'ap', $periodeId),
                \Maatwebsite\Excel\Excel::XLSX
            );

            return response($fileContents, 200, [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal export laporan LED AP: ' . $e->getMessage());

            return response()->json(['message' => 'Gagal generate laporan LED: ' . $e->getMessage()], 500);
        }
    }
}
