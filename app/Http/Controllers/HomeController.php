<?php

namespace App\Http\Controllers;

use App\Models\CapaianTck;
use App\Models\CapaianTkmru;
use App\Models\IdentifikasiRisiko;
use App\Models\JenisRisiko;
use App\Models\KRI;
use App\Models\KRIProject;
use App\Models\LossEvent;
use App\Models\LossEventProject;
use App\Models\Periode;
use App\Models\PeristiwaRisiko;
use App\Models\PrioritasRisiko;
use App\Models\Project;
use App\Models\ProjectPeriodeList;
use App\Models\RiskMap;
use App\Models\Unit;
use App\Models\RMIPeriod;
use App\Models\ProjectRisk;
use App\Models\ProjectRiskMonitoring;
use App\Models\ProjectHasilUsaha;
use App\Models\KRIUnitMonitoring;
use App\Models\KRIProjectMonitoring;
use App\Models\UnitRiskMonitoring;
use App\Models\UnitHasilUsaha;
use Illuminate\Http\Request;
use App\Supports\ApiWika;
use Auth;
use Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request, $data = null)
    {
        $allowedDashboard = [];
        if (Gate::check('dashboard_universitas') || Gate::check('dashboard_universitas_all_access')) {
            $allowedDashboard[] = 'universitas';
        }
        if (Gate::check('dashboard_fakultas') || Gate::check('dashboard_fakultas_all_access')) {
            $allowedDashboard[] = 'fakultas';
        }
        if (Gate::check('dashboard_biro') || Gate::check('dashboard_biro_all_access')) {
            $allowedDashboard[] = 'biro';
        }
        if (!$allowedDashboard) {
            if ($data) {
                return redirect()->route('home');
            }

            return view('home');
        }
        if (!$data || !in_array($data, ['universitas', 'fakultas', 'biro'])) {
            return redirect()->route('home', ['data' => $allowedDashboard[0] ?? 'universitas']);
        } elseif (!in_array($data, $allowedDashboard)) {
            return abort(403);
        }
        $dashboardType = $data;
        $riskMaps = RiskMap::select('id', 'level_risiko', 'nilai_risiko')->get()->keyBy('id');
        $periodes = Periode::orderBy('status')->orderBy('id', 'desc')->get();

        if (!$request->periode_id || !$periodes->pluck('id')->contains($request->periode_id)) {
            $request->merge(['periode_id' => $periodes->first()->id]);
        }

        $fakultases = [];
        $biros = [];
        $unitTypeId = null;
        $unitId = null;
        $unitIds = [];
        if ($dashboardType == 'universitas_all_access') {
            $unitTypeId = 1;
            $unitId = Unit::where('unit_type_id', 1)->first()->id;
            $unitIds = [$unitId];
        } elseif ($dashboardType == 'fakultas') {
            $unitTypeId = 2;
            if (Gate::check('dashboard_fakultas_all_access')) {
                $fakultases = Unit::where('unit_type_id', 2)->get();
            } else {
                $fakultases = Unit::where('unit_type_id', 2)->where('id', $request->user()->unit_id)->get();
            }
            if ($fakultases->count() && (!$request->fakultas_id || !$fakultases->pluck('id')->contains($request->fakultas_id))) {
                $request->merge(['fakultas_id' => $fakultases->first()->id]);
            }
            $unitId = $request->fakultas_id;

            $capaianTcks = CapaianTck::where('periode_id', $request->periode_id)
                ->whereHas('unit', function ($query) use ($request, $unitTypeId) {
                    $query->where('unit_type_id', $unitTypeId)->where('unit_id', $request->fakultas_id);
                })
                ->orderBy('tanggal_data', 'desc')
                ->limit(2)
                ->get();
            $capaianTck = $capaianTcks->first();
            $capaianTckBefore = $capaianTcks->get(1);
            $unitIds = Unit::where('id', $request->fakultas_id)->orWhere('parent_id', $request->fakultas_id)->pluck('id');
        } else {
            $unitTypeId = 3;
            if (Gate::check('dashboard_biro_all_access')) {
                $biros = Unit::where('unit_type_id', 3)->get();
            } else {
                $biros = Unit::where('unit_type_id', 3)->where('id', $request->user()->unit_id)->get();
            }
            if ($biros->count() && (!$request->biro_id || !$biros->pluck('id')->contains($request->biro_id))) {
                $request->merge(['biro_id' => $biros->first()->id]);
            }

            $unitId = $request->biro_id;
            $unitIds = [$unitId];

            $capaianTcks = CapaianTck::where('periode_id', $request->periode_id)
                ->whereHas('unit', function ($query) use ($request, $unitTypeId) {
                    $query->where('unit_type_id', $unitTypeId)->where('unit_id', $request->biro_id);
                })
                ->orderBy('tanggal_data', 'desc')
                ->limit(2)
                ->get();
            $capaianTck = $capaianTcks->first();
            $capaianTckBefore = $capaianTcks->get(1);
        }
        $capaianTcks = CapaianTck::where('periode_id', $request->periode_id)
            ->where('unit_id', $unitId)
            ->orderBy('tanggal_data', 'desc')
            ->orderBy('id', 'desc')
            ->limit(2)
            ->get();
        $capaianTck = $capaianTcks->first();
        $capaianTckBefore = $capaianTcks->get(1);

        try {
            $jkk = LossEvent::whereIn('unit_id', $unitIds)
                ->where('periode_id', $request->periode_id)
                ->count();
            $jkk_c = LossEvent::whereIn('unit_id', $unitIds)
                ->where('periode_id', $request->periode_id)
                ->whereDate('tanggal_kejadian', '>=', date('Y-m-01'))
                ->count();
            $jkk_date = LossEvent::whereIn('unit_id', $unitIds)
                ->where('periode_id', $request->periode_id)
                ->orderBy('tanggal_kejadian', 'desc')
                ->orderBy('id', 'desc')
                ->first()
                ?->tanggal_kejadian;
            if ($jkk_date) {
                $jkk_date = date('d M Y', strtotime($jkk_date));
            } else {
                $jkk_date = '';
            }
        } catch (QueryException $e) {
            $jkk = 0;
            $jkk_c = 0;
            $jkk_date = date('d M Y');
        }

        $capaianTkmrus = CapaianTkmru::where('periode_id', $request->periode_id)
            ->where('unit_id', $unitId)
            ->orderBy('tanggal_data', 'desc')
            ->orderBy('id', 'desc')
            ->limit(2)
            ->get();
        $capaianTkmru = optional($capaianTkmrus->first());
        $capaianTkmruBefore = optional($capaianTkmrus->get(1));

        $prir = PrioritasRisiko::where('unit_id', $unitId)
            ->where('periode_id', $request->periode_id)
            ->with('risiko.rencanaPerlakuanRisiko', 'risiko.penyebabRisiko', 'risiko.jenisRisiko', 'tck', 'unit', 'kategoriRisiko', 'jenisRisiko', 'skalaDampak', 'areaDampak', 'rencanaKegiatan')
            ->get();
        $lossEvents = LossEvent::whereIn('unit_id', $unitIds)
            ->where('periode_id', $request->periode_id)
            ->with('jenisRisiko')
            ->get();

        $finalPrir = $prir->map(function($item) {
            return [
                'kode' => 'R' . $item->risiko_id,
                'peristiwa' => $item->risiko->title,
                'ire' => $item->skala_risiko,
                'rre' => $item->rencanaPerlakuanRisiko ? $item->rencanaPerlakuanRisiko->rre : '-',
                'strategi' => $item->penyebabRisiko->pluck('rencana_perlakuan_risiko')->filter()->implode(', ') ?? '-',
                'tenggat' => $item->identifikasiRisiko->rencanaPerlakuanRisiko->tenggat_waktu,
                'full_data' => $item,
            ];
        })->sortByDesc('ire')->values()->map(function($item, $idx) {
            $item['kode'] = 'R' . ($idx + 1);
            return $item;
        });

        $dataDashboard = [
            'tck' => $capaianTck ? $capaianTck->capaian : 0,
            'tck_c' => round($capaianTck && $capaianTckBefore ? $capaianTck->capaian - $capaianTckBefore->capaian : ($capaianTck ? $capaianTck->capaian : 0), 1),
            'tck_date' => $capaianTck ? $capaianTck->tanggal_data->format('d M Y') : '',
            'rpr' => 0,
            'rpr_c' => 0,
            'rpr_date' => '',
            'jkk' => $jkk,
            'jkk_c' => $jkk_c,
            'jkk_date' => $jkk_date,
            'tkmru' => $capaianTkmru->capaian ?: 0,
            'tkmru_c' => round($capaianTkmru->capaian - $capaianTkmruBefore->capaian, 1),
            'tkmru_date' => $capaianTkmru->tanggal_data?->format('d M Y') ?: '',
            'tkmru_notes' => $capaianTkmru->description,
            'risk_maps' => $riskMaps,
            'prir' => $finalPrir,
            'prir_date' => $prir->sortByDesc('updated_at')->first()?->updated_at->format('d M Y') ?: '',
            'prsi' => $finalPrir->map(function($item) {
                $item['cre'] = $item['full_data']->risiko->monitoringRisiko()->orderBy('periode_monitoring', 'desc')->whereNotNull('realisasi_skala_risiko')->first()?->realisasi_skala_risiko ?: '';
                return $item;
            }),
            'prsi_date' => $prir->sortByDesc('updated_at')->first()?->updated_at->format('d M Y') ?: '',
            'top_risk' => $prir->sortByDesc('skala_risiko')->take(5)->map(function($item) use ($riskMaps) {
                return [
                    'peristiwa' => $item->risiko->title,
                    'deskripsi' => $item->risiko->deskripsi ?? $item->deskripsi_peristiwa_risiko,
                    'jenis_risiko' => $item->risiko->jenisRisiko->title,
                    'tingkat_risiko' => $item->skala_risiko,
                    'warna_tingkat_risiko' => strtolower(str_replace(' ', '-', $riskMaps->where('nilai_risiko', $item->skala_risiko)->pluck('level_risiko')->first())),
                    'tck_terpengaruh' => $item->tck?->title ?: '-',
                    'kri' => $item->risiko->kri->kri,
                    'status_kri' => ['bahaya' => 'red', 'waspada' => 'yellow', 'aman'=> 'green'][strtolower($item->risiko->kri->statusKri)] ?? '',
                    'risk_owner' => $item->unit->name,
                ];
            })->values(),
            'led' => $lossEvents->map(function($item) {
                return [
                    'tanggal_kejadian' => date('d/m/Y', strtotime($item->tanggal_kejadian)),
                    'peristiwa_kerugian' => $item->peristiwa_kerugian,
                    'jenis_risiko' => $item->jenisRisiko->title,
                    'nilai_kerugian_finansial' => is_numeric($item->nilai_kerugian_finansial) ? number_format($item->nilai_kerugian_finansial, 0, ',', '.') : $item->nilai_kerugian_finansial,
                    'nilai_kerugian_non_finansial' => is_numeric($item->nilai_kerugian_non_fungsional) ? number_format($item->nilai_kerugian_non_fungsional, 0, ',', '.') : $item->nilai_kerugian_non_fungsional,
                    'rekomendasi_perbaikan' => $item->child->pluck('rekomendasi_perbaikan'),
                    'unit_penanggung_jawab' => $item->unit_penanggung_jawab
                ];
            }),
        ];

        return view('dashboard', compact('riskMaps', 'periodes', 'dashboardType', 'fakultases', 'biros', 'dataDashboard'));
    }

    public function profile()
    {
        $data = Auth::user();
        return view('profile',compact('data'));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'password' => 'nullable|confirmed',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:255',
        ]);

        $input = $request->except(['password_confirmation']);

        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, ['password']);
        }

        $user = Auth::user();
        $user->update($input);

        return redirect()->route('profile')
                        ->with('success', 'Profile Anda berhasil diupdate');
    }

    public function profilRisiko(Request $request)
    {
        $userUnit = $request->user()->unit;

        if (!$userUnit) {
            return abort(403);
        }

        $riskMaps = RiskMap::select('id', 'level_risiko', 'nilai_risiko')->get()->keyBy('id');
        $periodes = Periode::orderBy('status')->orderBy('id', 'desc')->get();

        if (!$request->periode_id || !$periodes->pluck('id')->contains($request->periode_id)) {
            $request->merge(['periode_id' => $periodes->first()->id]);
        }

        $unitTypeId = $userUnit->unit_type_id;
        $unitId = $userUnit->id;
        $unitIds = [$unitId];

        $capaianTcks = CapaianTck::where('periode_id', $request->periode_id)
            ->where('unit_id', $unitId)
            ->orderBy('tanggal_data', 'desc')
            ->orderBy('id', 'desc')
            ->limit(2)
            ->get();

        $capaianTck = $capaianTcks->first();
        $capaianTckBefore = $capaianTcks->get(1);

        try {
            $jkk = LossEvent::whereIn('unit_id', $unitIds)
                ->where('periode_id', $request->periode_id)
                ->count();
            $jkk_c = LossEvent::whereIn('unit_id', $unitIds)
                ->where('periode_id', $request->periode_id)
                ->whereDate('tanggal_kejadian', '>=', date('Y-m-01'))
                ->count();
            $jkk_date = LossEvent::whereIn('unit_id', $unitIds)
                ->where('periode_id', $request->periode_id)
                ->orderBy('tanggal_kejadian', 'desc')
                ->orderBy('id', 'desc')
                ->first()
                ?->tanggal_kejadian;
            if ($jkk_date) {
                $jkk_date = date('d M Y', strtotime($jkk_date));
            } else {
                $jkk_date = '';
            }
        } catch (QueryException $e) {
            $jkk = 0;
            $jkk_c = 0;
            $jkk_date = date('d M Y');
        }

        $capaianTkmrus = CapaianTkmru::where('periode_id', $request->periode_id)
            ->where('unit_id', $unitId)
            ->orderBy('tanggal_data', 'desc')
            ->orderBy('id', 'desc')
            ->limit(2)
            ->get();
        $capaianTkmru = optional($capaianTkmrus->first());
        $capaianTkmruBefore = optional($capaianTkmrus->get(1));

        $prir = PrioritasRisiko::where('unit_id', $unitId)
            ->where('periode_id', $request->periode_id)
            ->with('risiko.rencanaPerlakuanRisiko', 'risiko.penyebabRisiko', 'risiko.jenisRisiko', 'tck', 'unit', 'kategoriRisiko', 'jenisRisiko', 'skalaDampak', 'areaDampak', 'rencanaKegiatan')
            ->get();
        $lossEvents = LossEvent::whereIn('unit_id', $unitIds)
            ->where('periode_id', $request->periode_id)
            ->with('jenisRisiko')
            ->get();

        $finalPrir = $prir->map(function($item) {
            return [
                'kode' => 'R' . $item->risiko_id,
                'peristiwa' => $item->risiko->title,
                'ire' => $item->skala_risiko,
                'rre' => $item->risiko->rencanaPerlakuanRisiko->rre,
                'strategi' => $item->risiko->penyebabRisiko->rencana_perlakuan_risiko ?: '-',
                'tenggat' => $item->risiko->rencanaPerlakuanRisiko->tenggat_waktu,
                'full_data' => $item,
            ];
        })->sortByDesc('ire')->values()->map(function($item, $idx) {
            $item['kode'] = 'R' . ($idx + 1);
            return $item;
        });

        $dataDashboard = [
            'tck' => $capaianTck ? $capaianTck->capaian : 0,
            'tck_c' => round($capaianTck && $capaianTckBefore ? $capaianTck->capaian - $capaianTckBefore->capaian : ($capaianTck ? $capaianTck->capaian : 0), 1),
            'tck_date' => $capaianTck ? $capaianTck->tanggal_data->format('d M Y') : '',
            'rpr' => 0,
            'rpr_c' => 0,
            'rpr_date' => '',
            'jkk' => $jkk,
            'jkk_c' => $jkk_c,
            'jkk_date' => $jkk_date,
            'tkmru' => $capaianTkmru->capaian ?: 0,
            'tkmru_c' => round($capaianTkmru->capaian - $capaianTkmruBefore->capaian, 1),
            'tkmru_date' => $capaianTkmru->tanggal_data?->format('d M Y') ?: '',
            'tkmru_notes' => $capaianTkmru->description,
            'risk_maps' => $riskMaps,
            'prir' => $finalPrir,
            'prir_date' => $prir->sortByDesc('updated_at')->first()?->updated_at->format('d M Y') ?: '',
            'prsi' => $finalPrir->map(function($item) {
                $item['cre'] = $item['full_data']->risiko->monitoringRisiko()->orderBy('periode_monitoring', 'desc')->whereNotNull('realisasi_skala_risiko')->first()?->realisasi_skala_risiko ?: '';

                return $item;
            }),
            'prsi_date' => $prir->sortByDesc('updated_at')->first()?->updated_at->format('d M Y') ?: '',
            'top_risk' => $prir->sortByDesc('skala_risiko')->take(5)->map(function($item) use ($riskMaps) {
                return [
                    'peristiwa' => $item->risiko->title,
                    'deskripsi' => $item->risiko->deskripsi ?? $item->deskripsi_peristiwa_risiko,
                    'jenis_risiko' => $item->risiko->jenisRisiko->title,
                    'tingkat_risiko' => $item->skala_risiko,
                    'warna_tingkat_risiko' => strtolower(str_replace(' ', '-', $riskMaps->where('nilai_risiko', $item->skala_risiko)->pluck('level_risiko')->first())),
                    'tck_terpengaruh' => $item->tck?->title ?: '-',
                    'kri' => $item->risiko->kri->kri,
                    'status_kri' => ['bahaya' => 'red', 'waspada' => 'yellow', 'aman'=> 'green'][strtolower($item->risiko->kri->statusKri)] ?? '',
                    'risk_owner' => $item->unit->name,
                ];
            })->values(),
            'led' => $lossEvents->map(function($item) {
                return [
                    'tanggal_kejadian' => date('d/m/Y', strtotime($item->tanggal_kejadian)),
                    'peristiwa_kerugian' => $item->peristiwa_kerugian,
                    'jenis_risiko' => $item->jenisRisiko->title,
                    'nilai_kerugian_finansial' => is_numeric($item->nilai_kerugian_finansial) ? number_format($item->nilai_kerugian_finansial, 0, ',', '.') : $item->nilai_kerugian_finansial,
                    'nilai_kerugian_non_finansial' => is_numeric($item->nilai_kerugian_non_fungsional) ? number_format($item->nilai_kerugian_non_fungsional, 0, ',', '.') : $item->nilai_kerugian_non_fungsional,
                    'rekomendasi_perbaikan' => $item->child->pluck('rekomendasi_perbaikan'),
                    'unit_penanggung_jawab' => $item->unit_penanggung_jawab
                ];
            }),
        ];

        return view('profil-risiko', compact('riskMaps', 'periodes', 'dataDashboard'));
    }

    public function dashboardUnit(Request $request)
    {
        $user = auth()->user();
        $isAllUnit = Gate::check('risk_register_all_unit');

        $periodes        = Periode::orderBy('status')->orderBy('id', 'desc')->get();
        $selectedPeriode = $request->periode_id ? $periodes->find($request->periode_id) : $periodes->first();
        $tahun = $selectedPeriode->tahun;

        $selectedUnitId = ($isAllUnit && $request->unit_id) ? $request->unit_id : $user->unit_id;
        $selectedUnit = Unit::find($selectedUnitId);

        $units = Unit::query()
            ->when(!$isAllUnit, function ($query) use ($user) {
                $query->where('id', $user->unit_id);
            })
            ->get();

        $risikos = IdentifikasiRisiko::with([
            'periode',
            'peristiwaRisiko',
            'riskAnalysis',
            'monitoringRisikos' => function ($query) {
                $query->orderBy('id', 'desc');
            },
        ])
            ->where('periode_id', $selectedPeriode->id)
            ->where('unit_id', $selectedUnitId)
            ->where('status_risiko', '=', 3)
            ->where('is_closed', false)
            ->whereNull('deleted_at')
            ->get()
            ->sortBy([
              ['riskAnalysis.skala_risiko', 'desc'],      // Prioritas 1
              ['riskAnalysis.eksposur_risiko', 'desc'],   // Prioritas 2
              ['id', 'asc'],                              // Prioritas 3
            ])
            ->values();

        $currentRiskMaps          = $risikos->pluck('currentRiskMapsMonth');
        $formattedCurrentRiskMaps = [];

        foreach ($risikos as $idx => $risk) {
            $currentValue = $risk->currentRiskMapsMonth['inherent'];
            for ($month = 1; $month <= 12; $month++) {
                if ($nextValue = ($risk->currentRiskMapsMonth[$month] ?? null)) {
                    $currentValue = $nextValue;
                }

                $currentValue['quarter'] = ceil($month / 3);
                $currentValue['month'] = $month;

                $formattedCurrentRiskMaps[$risk->id][] = $currentValue;
            }
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        $lossEvents = LossEvent::where('unit_id', $selectedUnitId)
            ->whereYear('tanggal_kejadian', $tahun)
            ->with(['kategoriRisiko', 'jenisRisiko'])
            ->get()
            ->map(function ($event) {
                $event->numeric_value = (int) preg_replace('/[^0-9]/', '', $event->nilai_kerugian_finansial);
                return $event;
            })
            ->sortByDesc('numeric_value')
            ->take(10)
            ->map(function ($led) {
                return [
                    'id' => $led->id,
                    'nama_divisi' => $led->unit?->name,
                    'tanggal_kejadian' => date('d/m/Y', strtotime($led->tanggal_kejadian)),
                    'nama_kejadian' => $led->nama_kejadian ?? '-',
                    'identifikasi_kejadian' => $led->identifikasi_kejadian ?? '-',
                    'kategori_kejadian' => $led->kategoriKejadian->kategori_kejadian ?? '-',
                    'nilai_kerugian' => is_numeric($led->nilai_kerugian_finansial) ? 'Rp ' . number_format($led->nilai_kerugian_finansial, 0, ',', '.') : $led->nilai_kerugian_finansial,
                    'unit_penanggung_jawab' => $led?->unitPenanggungJawabJabatan?->name ?? '-',
                ];
            })
            ->values()
            ->toArray();

        $topRisks = IdentifikasiRisiko::where('unit_id', $selectedUnit->id)
            ->where('periode_id', $selectedPeriode->id)
            ->where('is_closed', false)
            ->whereNull('deleted_at')
            ->with(['riskAnalysis.skalaProbabilitas', 'kris', 'unit', 'peristiwaRisiko', 'jenisRisiko', 'tck'])
            ->get()
            ->sortByDesc(fn($risk) => $risk->riskAnalysis?->skala_risiko)
            ->take(10)
            ->values()
            ->map(function($item) use ($riskMaps) {
                $skalaRisiko = $item->riskAnalysis?->skala_risiko;
                $levelRisiko = optional($riskMaps->where('nilai_risiko', $skalaRisiko)->first())->level_risiko;
                $warnaLevelRisiko = strtolower(str_replace(' ', '-', str_replace('to ', '', $levelRisiko ?? '')));

                $krisData = $item->kris->map(function($kri) {
                    $statusKri = $kri->statusKri;
                    $statusKriColor = match (strtolower($statusKri)) {
                        '3' => 'red',
                        '2' => 'yellow',
                        '1' => 'green',
                        default => 'neutral'
                    };

                    return [
                        'kri' => $kri->kri,
                        'status_kri' => $statusKri,
                        'status_kri_color' => $statusKriColor,
                    ];
                })->values();

                return [
                    'id' => $item->id,
                    'peristiwa_risiko' => $item->peristiwa_risiko,
                    'deskripsi_peristiwa_risiko' => $item->deskripsi_peristiwa_risiko,
                    'jenis_risiko' => $item->jenisRisiko?->title,
                    'skala_dampak' => $item?->riskAnalysis?->skala_dampak,
                    'nilai_dampak' => $item?->riskAnalysis?->nilai_dampak,
                    'nilai_risiko' => $item?->riskAnalysis?->skala_risiko,
                    'level_risiko' => $levelRisiko,
                    'warna_tingkat_risiko' => $warnaLevelRisiko,
                    'kris' => $krisData,
                    'divisi' => $item->unit->name,
                ];
            });

        $risikosEfektif = [];
        $risikosTidakEfektif = [];

        // Mengambil semua yang is_closed 1 untuk keperluan data pie chart efektivitas
        $allRisksForPie = IdentifikasiRisiko::with(['unit', 'peristiwaRisiko'])
            ->where('periode_id', $selectedPeriode->id)
            ->where('unit_id', $selectedUnitId)
            ->where('status_risiko', '=', 3)
            ->where('is_closed', 1)
            ->get();

        $allRisksForPie->each(function ($risiko) use (&$risikosEfektif, &$risikosTidakEfektif) {
            $data = [
                'peristiwa_risiko' => $risiko->peristiwa_risiko ?: $risiko->peristiwaRisiko->title,
                'unit_name' => $risiko->unit->name,
            ];
            if ($risiko->efektivitas_perlakuan_risiko >= 0) {
                $risikosEfektif[] = $data;
            } else {
                $risikosTidakEfektif[] = $data;
            }
        });

        $dashboardData = [
          'rpr_c' => null,
          'rpr_date' => null,
          'jkk_c' => null,
          'jkk_date' => null,
          'tkmru_c' => null,
          'tkmru_date' => null,
          'tkmru' => null,
          'tkmru_notes' => null,
          'top_risks' => $topRisks,
          'led' => $lossEvents,
          'efektivitas_perlakuan' => [
              ['label' => 'Efektif', 'value' => count($risikosEfektif), 'color' => '#5470C6'],
              ['label' => 'Tidak Efektif', 'value' => count($risikosTidakEfektif), 'color' => '#EE6666'],
          ],
          'risikos_efektif' => $risikosEfektif,
          'risikos_tidak_efektif' => $risikosTidakEfektif,
        ];

        return view('dashboard-unit', compact(
            'user',
            'periodes',
            'selectedPeriode',
            'units',
            'selectedUnit',
            'risikos',
            'formattedCurrentRiskMaps',
            'riskMaps',
            'dashboardData',
        ));
    }

    public function dashboardProyek(Request $request)
    {
        $selectedUnitId = $request->input('unit_id');
        $selectedProjectId = $request->input('project_id');
        $user = Auth::user();

        $allowedProjectIds = null;

        if ($user->can('view_all_project')) {
            $costCenterParents = Project::distinct()->pluck('cost_center_parent')->filter();
            $units = Unit::where('unit_type_id', 1)
                        ->whereIn('cost_center', $costCenterParents)
                        ->orderBy('name')
                        ->get();
        } else {
            $projectsFromUnit = $user->unit ? $user->unit->projects : collect();
            $projectsDirectlyAssigned = $user->projects;
            $allAllowedProjects = $projectsFromUnit->merge($projectsDirectlyAssigned)->unique('id');

            $allowedProjectIds = $allAllowedProjects->pluck('id');

            $costCenterParents = $allAllowedProjects->pluck('cost_center_parent')->unique()->filter();
            $units = Unit::where('unit_type_id', 1)
                        ->whereIn('cost_center', $costCenterParents)
                        ->orderBy('name')
                        ->get();
        }

        if ($selectedProjectId && $allowedProjectIds && !$allowedProjectIds->contains($selectedProjectId)) {
            return redirect()
                ->route('dashboard-proyek', $request->except('project_id'))
                ->with('error', 'Anda tidak memiliki hak akses untuk melihat proyek tersebut.');
        }

        $projectsQuery = Project::query()
            ->when($allowedProjectIds, function ($query, $ids) {
                $query->whereIn('id', $ids);
            })
            ->when($selectedUnitId, function ($query, $unitId) {
                $unit = Unit::find($unitId);
                if ($unit) {
                    $query->where('cost_center_parent', $unit->cost_center);
                }
            });
        $projects = $projectsQuery->get();

        $projectPeriodeQuery = ProjectPeriodeList::query()
            ->whereHas('project', function($query) {
                $query->where('type', Project::TYPE_OPERASIONAL);
            })
            ->when($allowedProjectIds, function ($query, $ids) {
                $query->whereIn('project_id', $ids);
            })
            ->when($selectedUnitId, function ($query, $unitId) {
                $query->whereHas('project', function ($q) use ($unitId) {
                    $unit = Unit::find($unitId);
                    if ($unit) $q->where('cost_center_parent', $unit->cost_center);
                });
            })
            ->when($selectedProjectId, function ($query, $projectId) {
                $query->where('project_id', $projectId);
            });

        $projectPeriodes = $projectPeriodeQuery
            ->orderBy(DB::raw('skala_risiko IS NULL'), 'ASC')
            ->orderByRaw('skala_risiko DESC NULLS LAST')
            ->with([
                'projectRisks' => function($query) {
                    $query->where('is_closed', false);
                },
                'projectRisks.projectRiskAnalisa',
                'projectRisks.projectRiskMonitorings',
                'project'
            ])
            ->take(10)
            ->get();

        $projectRisks = $projectPeriodes->pluck('projectRisks')->flatten();
        $projectRisksGroupByPeristiwaRisiko = $projectRisks->groupBy('peristiwa_risiko_id');

        $peristiwaRisikos = PeristiwaRisiko::whereIn('id', $projectRisks->pluck('peristiwa_risiko_id')->unique())->get()->keyBy('id');

        $tahunMonitorings = $projectRisks->pluck('projectRiskMonitorings')->flatten()->pluck('tahun')->unique()->toArray();
        foreach ($projectPeriodes as $projectPeriode) {
            $tahunMonitorings[] = $projectPeriode->created_at->format('Y');
        }
        sort($tahunMonitorings);
        if (empty($tahunMonitorings)) {
            $tahunMonitorings = [date('Y')];
        }
        $minTahun = min($tahunMonitorings);
        $maxTahun = max($tahunMonitorings);
        $tahunMonitorings = [];
        for ($tahun = $minTahun; $tahun <= $maxTahun; $tahun++) {
            $tahunMonitorings[] = $tahun;
        }

        $colors = [
            '#5470C6', '#91CC75', '#FAC858', '#EE6666', '#73C0DE',
            '#3BA272', '#FC8452', '#9A60B4', '#EA7CCC', '#FF9F7F',
            '#FFDB5C', '#37A2DA', '#32C5E9', '#9FE6B8', '#FF9F7F',
            '#FB7293', '#E062AE', '#E690D1', '#E7BCF3', '#9D96F5'
        ];
        $colorIndex = 0;

        $topLossEvents = LossEventProject::orderBy('nilai_kerugian_finansial', 'desc')
            ->when($selectedUnitId, function ($query, $selectedUnitId) {
                $unit = Unit::find($selectedUnitId);
                if ($unit) {
                    $query->whereHas('project', function ($q) use ($unit) {
                        $q->where('cost_center_parent', $unit->cost_center);
                    });
                }
            })
            ->when($selectedProjectId, function ($query, $selectedProjectId) {
                $query->where('project_id', $selectedProjectId);
            })
            ->with('project', 'kategoriKejadian')
            ->take(10)
            ->get();

        // Hitung efektivitas dari yang sudah closed
        $allClosedProjectRisks = ProjectRisk::where('is_closed', true)
            ->whereIn('project_id', $projectPeriodes->pluck('project_id'))
            ->get();

        $efektifCount = 0;
        $tidakEfektifCount = 0;
        foreach ($allClosedProjectRisks as $projectRisk) {
            if ($projectRisk->efektivitas_perlakuan_risiko >= 0) {
                $efektifCount++;
            } else {
                $tidakEfektifCount++;
            }
        }

        $dashboardData = [
            'prp' => $projectRisksGroupByPeristiwaRisiko->map(function($projectRiskGroups, $peristiwaRisikoId) use ($peristiwaRisikos, $colors, $projectRisks, &$colorIndex) {
                if ($colorIndex >= count($colors)) {
                    $colorIndex = 0;
                }
                $peristiwaRisiko = $peristiwaRisikos->get($peristiwaRisikoId);

                return [
                    'label' => $peristiwaRisiko->title,
                    'color' => $colors[$colorIndex++],
                    'jumlah_kejadian' => $projectRiskGroups->count(),
                    'nilai_dampak' => $projectRiskGroups->sum('projectRiskAnalisa.nilai_dampak'),
                ];
            })->values()->toArray(),
            'prir' => $projectPeriodes->map(function($projectPeriode, $idx) {
                return [
                    'kode' => 'P' . ($idx + 1),
                    'nama_proyek' => $projectPeriode->project->project_name,
                    'skala_risiko' => $projectPeriode->skala_risiko,
                    'level_risiko' => $projectPeriode->level_risiko ?: '',
                    'skala_risiko_residual' => $projectPeriode->skala_risiko_residual,
                    'level_risiko_residual' => $projectPeriode->level_risiko_residual ?: '',
                ];
            })->values()->toArray(),
            'prsi' => $projectPeriodes->map(function($projectPeriode, $idx) use($tahunMonitorings) {
                $monitorings = [];
                $currentMonitoring = [
                    'skala_risiko' => $projectPeriode->skala_risiko,
                    'level_risiko' => $projectPeriode->level_risiko,
                ];
                foreach ($tahunMonitorings as $tahun) {
                    for ($i = 1; $i <= 4; $i++) {
                        if ($projectPeriode->additional_data['data_risiko'][$tahun]['summary']['level_risiko_q' . $i] ?? false) {
                            $currentMonitoring = [
                                'skala_risiko' => $projectPeriode->additional_data['data_risiko'][$tahun]['summary']['skala_risiko_q' . $i],
                                'level_risiko' => $projectPeriode->additional_data['data_risiko'][$tahun]['summary']['level_risiko_q' . $i],
                            ];
                        }
                        $monitorings["skala_risiko_{$tahun}_q{$i}"] = $currentMonitoring['skala_risiko'];
                        $monitorings["level_risiko_{$tahun}_q{$i}"] = $currentMonitoring['level_risiko'];
                    }
                }

                return [
                    'kode' => 'P' . ($idx + 1),
                    'nama_proyek' => $projectPeriode->project->project_name,
                    'monitorings' => $monitorings,
                ];
            })->values()->toArray(),
            'top_risk' => $projectRisks->sortByDesc('projectRiskAnalisa.skala_risiko')->take(10)->map(function($projectRisk) {
                return [
                    'id' => $projectRisk->id,
                    'project_id' => $projectRisk->project_id,
                    'kode' => 'R' . $projectRisk->id,
                    'nama_proyek' => $projectRisk->project?->project_name,
                    'peristiwa_risiko' => $projectRisk->peristiwaRisiko?->title,
                    'deskripsi_peristiwa_risiko' => $projectRisk->deskripsi_peristiwa_risiko,
                    'nilai_dampak' => $projectRisk->projectRiskAnalisa?->nilai_dampak,
                    'skala_dampak' => $projectRisk->projectRiskAnalisa?->skala_dampak,
                    'jenis_risiko' => $projectRisk->jenisRisiko?->title,
                    'nilai_risiko' => $projectRisk->projectRiskAnalisa?->skala_risiko,
                    'level_risiko' => $projectRisk->projectRiskAnalisa?->level_risiko,
                ];
            })->values()->toArray(),
            'top_led' => $topLossEvents->map(function($led, $idx) {
                return [
                    'id' => $led->id,
                    'nama_proyek' => $led->project?->project_name,
                    'tanggal_kejadian' => date('d/m/Y', strtotime($led->tanggal_kejadian)),
                    'nama_kejadian' => $led->nama_kejadian,
                    'deskripsi_kejadian' => $led->peristiwaRisiko?->title,
                    'kategori_kejadian' => $led->kategoriKejadian?->kategori_kejadian,
                    'nilai_kerugian_finansial' => $led->nilai_kerugian_finansial,
                ];
            })->values()->toArray(),
            'efektivitas_perlakuan' => [
                ['label' => 'Efektif', 'value' => $efektifCount, 'color' => '#5470C6'],
                ['label' => 'Tidak Efektif', 'value' => $tidakEfektifCount, 'color' => '#EE6666'],
            ],
        ];

        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        return view('dashboard-proyek', compact('dashboardData', 'tahunMonitorings', 'riskMaps', 'units', 'projects', 'selectedUnitId', 'selectedProjectId'));
    }

    public function dashboardCorporate(Request $request)
    {
        $user = auth()->user();
        $isAllUnit = Gate::check('risk_register_all_unit');

        $periodes        = Periode::orderBy('status')->orderBy('id', 'desc')->get();
        $selectedPeriode = $request->periode_id ? $periodes->find($request->periode_id) : $periodes->first();
        $tahun = $selectedPeriode->tahun;

        $selectedUnitId = ($isAllUnit && $request->unit_id) ? $request->unit_id : $user->unit_id;
        $selectedUnit = Unit::find($selectedUnitId);

        $units = Unit::query()
          ->when(!$isAllUnit, function ($query) use ($user) {
              $query->where('id', $user->unit_id);
          })
          ->get();

        $risikos = IdentifikasiRisiko::with([
            'periode',
            'peristiwaRisiko',
            'riskAnalysis',
            'monitoringRisikos' => function ($query) {
                $query->orderBy('id', 'desc');
            },
        ])
            ->where('periode_id', $selectedPeriode->id)
            ->where('is_corporate', 1)
            ->where('is_closed', false)
            ->whereNull('deleted_at')
            ->get()
            ->sortBy([
              ['riskAnalysis.skala_risiko', 'desc'],      // Prioritas 1
              ['riskAnalysis.eksposur_risiko', 'desc'],   // Prioritas 2
              ['id', 'asc'],                              // Prioritas 3
            ])
            ->values();

        $currentRiskMaps          = $risikos->pluck('currentRiskMaps');
        $formattedCurrentRiskMaps = [];

        foreach ($risikos as $idx => $risk) {
            $getFallbackValue = function($targetQuarter) use ($risk) {
                if (isset($risk->current_risk_maps[$targetQuarter]) &&
                    !is_null($risk->current_risk_maps[$targetQuarter]['skala_dampak']) &&
                    !is_null($risk->current_risk_maps[$targetQuarter]['skala_probabilitas'])) {
                    return $risk->current_risk_maps[$targetQuarter];
                }

                for ($q = $targetQuarter - 1; $q >= 1; $q--) {
                    if (isset($risk->current_risk_maps[$q]) &&
                        !is_null($risk->current_risk_maps[$q]['skala_dampak']) &&
                        !is_null($risk->current_risk_maps[$q]['skala_probabilitas'])) {
                        return $risk->current_risk_maps[$q];
                    }
                }

                if (isset($risk->current_risk_maps['inherent']) &&
                    !is_null($risk->current_risk_maps['inherent']['skala_dampak']) &&
                    !is_null($risk->current_risk_maps['inherent']['skala_probabilitas'])) {
                    return $risk->current_risk_maps['inherent'];
                }

                if ($risk->riskAnalysis) {
                    return [
                        'skala_dampak' => $risk->riskAnalysis->skala_dampak,
                        'skala_probabilitas' => $risk->riskAnalysis->skala_probabilitas->tingkat ?? null,
                        'skala_risiko' => $risk->riskAnalysis->skala_risiko,
                        'level_risiko' => $risk->riskAnalysis->level_risiko,
                    ];
                }

                return null;
            };

            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $currentValue = $getFallbackValue($quarter);

                if ($currentValue &&
                    !is_null($currentValue['skala_dampak']) &&
                    !is_null($currentValue['skala_probabilitas'])) {
                    $currentValue['quarter'] = $quarter;
                    $formattedCurrentRiskMaps[$risk->id][] = $currentValue;
                }
            }
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        $period = RMIPeriod::with([
            'penilaianCapaianKinerja.details.pilihan'
          ])->where('year', $tahun)->first();

        if (!$period) {
            $period = (object) [
                'id' => null,
                'year' => $tahun,
                'status' => 0,
                'score_rmi' => '-',
                'score_rmi_desc' => '-',
                'final_score_rmi' => '-',
                'updated_at' => now(),
                'kinerja' => '-',
                'kpmr' => '-',
                'peringkat_komposit_risiko' => '-',
                'nilai_konversi' => '-',
                'penilaianCapaianKinerja' => (object) [
                    'capaian_kinerja' => 0,
                    'kpmr' => 0,
                ]
            ];
        }

        $lossEventsUnit = LossEvent::whereYear('tanggal_kejadian', $tahun)
          ->with(['kategoriRisiko', 'jenisRisiko'])
          ->get()
          ->map(function ($event) {
              $event->numeric_value = (int) preg_replace('/[^0-9]/', '', $event->nilai_kerugian_finansial);
              return $event;
          })
          ->sortByDesc('numeric_value')
          ->take(10)
          ->map(function ($led) {
              return [
                  'tanggal_kejadian' => date('d/m/Y', strtotime($led->tanggal_kejadian)),
                  'nama_kejadian' => $led->nama_kejadian ?? '-',
                  'identifikasi_kejadian' => $led->identifikasi_kejadian ?? '-',
                  'kategori_kejadian' => $led->kategoriKejadian->kategori_kejadian ?? '-',
                  'nilai_kerugian' => is_numeric($led->nilai_kerugian_finansial) ? 'Rp ' . number_format($led->nilai_kerugian_finansial, 0, ',', '.') : $led->nilai_kerugian_finansial,
                  'unit_penanggung_jawab' => $led?->unitPenanggungJawabJabatan?->name ?? '-',
              ];
          })
          ->values()
          ->toArray();

        $lossEventsProject = LossEventProject::with(['kategoriRisiko', 'jenisRisiko'])
          ->get()
          ->map(function ($event) {
              $event->numeric_value = (int) preg_replace('/[^0-9]/', '', $event->nilai_kerugian_finansial);
              return $event;
          })
          ->sortByDesc('numeric_value')
          ->take(10)
          ->map(function ($led) {
              return [
                  'tanggal_kejadian' => date('d/m/Y', strtotime($led->tanggal_kejadian)),
                  'nama_kejadian' => $led->nama_kejadian ?? '-',
                  'identifikasi_kejadian' => $led->peristiwaRisiko->title ?? '-',
                  'kategori_kejadian' => $led->kategoriKejadian->kategori_kejadian ?? '-',
                  'nilai_kerugian' => is_numeric($led->nilai_kerugian_finansial) ? 'Rp ' . number_format($led->nilai_kerugian_finansial, 0, ',', '.') : $led->nilai_kerugian_finansial,
                  'unit_penanggung_jawab' => $led?->unitPenanggungJawabJabatan?->name ?? '-',
              ];
          })
          ->values()
          ->toArray();

        $dashboardData = [
          'rpr_c' => null,
          'rpr_date' => null,
          'jkk_c' => null,
          'jkk_date' => null,
          'tkmru_c' => null,
          'tkmru_date' => null,
          'tkmru' => null,
          'tkmru_notes' => null,
          'led' => $lossEventsUnit,
          'ledProject' => $lossEventsProject,
        ];

        return view('dashboard-corporate', compact(
          'user',
          'periodes',
          'selectedPeriode',
          'units',
          'selectedUnit',
          'risikos',
          'formattedCurrentRiskMaps',
          'riskMaps',
          'period',
          'dashboardData',
        ));
    }

    public function dashboardKriUnit(Request $request)
    {
        $user = auth()->user();
        $isAllUnit = Gate::check('risk_register_all_unit');

        $periodes = Periode::orderBy('status')->orderBy('id', 'desc')->get();
        $selectedPeriode = $request->periode_id ? $periodes->find($request->periode_id) : $periodes->first();

        $units = Unit::query()
            ->when(!$isAllUnit, function ($query) use ($user) {
                $query->where('id', $user->unit_id);
            })
            ->get();

        $selectedUnitId = null;
        if ($isAllUnit) {
            $selectedUnitId = $request->unit_id; // null untuk "semua unit"
        } else {
            $selectedUnitId = $user->unit_id;
        }
        $selectedUnit = $selectedUnitId ? Unit::find($selectedUnitId) : null;

        $selectedQuarter = $request->quarter ?? 1;

        $kriQuery = KRI::with([
            'identifikasiRisiko' => function($query) {
                $query->with(['unit', 'jenisRisiko']);
            },
            'kriUnitMonitorings' => function($query) {
                $query->orderBy('id', 'desc');
            }
        ]);

        if ($selectedPeriode) {
            $kriQuery->whereHas('identifikasiRisiko', function($query) use ($selectedPeriode) {
                $query->where('periode_id', $selectedPeriode->id)
                      ->whereNull('deleted_at')
                      ->where('is_closed', false);
            });
        }

        if ($selectedUnitId) {
            $kriQuery->whereHas('identifikasiRisiko', function($query) use ($selectedUnitId) {
                $query->where('unit_id', $selectedUnitId)
                      ->whereNull('deleted_at')
                      ->where('is_closed', false);
            });
        } elseif (!$isAllUnit) {
            $kriQuery->whereHas('identifikasiRisiko', function($query) use ($user) {
                $query->where('unit_id', $user->unit_id)
                      ->whereNull('deleted_at')
                      ->where('is_closed', false);
            });
        }

        $kriData = $kriQuery->get();

        $processedKriData = $kriData->map(function($kri) use ($selectedQuarter) {
            return [
                'kri' => $kri->kri,
                'risiko' => $kri->identifikasiRisiko->peristiwa_risiko ?? '-',
                'pemilik_risiko' => $kri->identifikasiRisiko->unit->name ?? '-',
                'batas_aman' => $kri->batas_aman,
                'batas_waspada' => $kri->batas_waspada,
                'batas_bahaya' => $kri->batas_bahaya,
                'kondisi_saat_ini' => $kri->{"nilai_kri_terkini_q{$selectedQuarter}"} ?? '-',
                't2_t3_kbumn' => $kri->identifikasiRisiko->jenisRisiko->title ?? '-',
                'status' => $kri->{"status_kri_terkini_q{$selectedQuarter}"},
                'status_priority' => $this->getStatusPriorityFromKriStatus($kri->{"status_kri_terkini_q{$selectedQuarter}"}),
                'kri_object' => $kri
            ];
        });

        $sortedKriData = $processedKriData->sortBy('status_priority')->values();

        $jenisRisikoData = $kriData->groupBy('identifikasiRisiko.jenisRisiko.title')->map(function($group, $jenisRisiko) use ($selectedQuarter) {
            $amanCount = 0;
            $waspadaCount = 0;
            $bahayaCount = 0;

            foreach($group as $kri) {
                $currentStatusField = "status_kri_terkini_q{$selectedQuarter}";
                $statusNumeric = $kri->{$currentStatusField} ?? null;

                if (is_null($statusNumeric)) {
                    continue;
                }

                switch ((int)$statusNumeric) {
                    case 1:
                        $amanCount++;
                        break;
                    case 2:
                        $waspadaCount++;
                        break;
                    case 3:
                        $bahayaCount++;
                        break;
                }
            }

            return [
                'jenis_risiko_id' => $group->first()->identifikasiRisiko->jenis_risiko_id ?? null,
                'jenis_risiko' => $jenisRisiko ?: 'Tidak Diketahui',
                'aman' => $amanCount,
                'waspada' => $waspadaCount,
                'bahaya' => $bahayaCount,
                'total' => $amanCount + $waspadaCount + $bahayaCount
            ];
        })->values();

        $unitData = $kriData->groupBy('identifikasiRisiko.unit.name')->map(function($group, $unitName) use ($selectedQuarter) {
            $amanCount = 0;
            $waspadaCount = 0;
            $bahayaCount = 0;

            foreach($group as $kri) {
                $currentStatusField = "status_kri_terkini_q{$selectedQuarter}";
                $statusNumeric = $kri->{$currentStatusField} ?? null;

                if (is_null($statusNumeric)) {
                    continue;
                }

                switch ((int)$statusNumeric) {
                    case 1:
                        $amanCount++;
                        break;
                    case 2:
                        $waspadaCount++;
                        break;
                    case 3:
                        $bahayaCount++;
                        break;
                }
            }

            return [
                'unit_id' => $group->first()->identifikasiRisiko->unit_id ?? null,
                'unit_name' => $unitName ?: 'Tidak Diketahui',
                'aman' => $amanCount,
                'waspada' => $waspadaCount,
                'bahaya' => $bahayaCount,
                'total' => $amanCount + $waspadaCount + $bahayaCount
            ];
        })->values();

        $jenisRisikoList = JenisRisiko::orderBy('title')->get();

        return view('dashboard-kri-unit', compact(
            'periodes',
            'selectedPeriode',
            'units',
            'selectedUnit',
            'selectedUnitId',
            'selectedQuarter',
            'sortedKriData',
            'isAllUnit',
            'jenisRisikoData',
            'unitData',
            'jenisRisikoList'
        ));
    }

    public function dashboardKriProject(Request $request)
    {
        $user = auth()->user();
        $currentYear = date('Y');
        $yearList = [];
        for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
            $yearList[] = $i;
        }

        $selectedYear = $request->tahun ?? $currentYear;
        $selectedUnitId = $request->input('unit_id');
        $selectedProjectId = $request->input('project_id');

        $units = Unit::where('unit_type_id', 1)->get();

        $projects = collect([]);
        if ($selectedUnitId) {
            $unit = Unit::find($selectedUnitId);
            if ($unit) {
                $projects = Project::where('cost_center_parent', $unit->cost_center)->get();
            }
        } else {
            $projects = Project::all();
        }

        $selectedProject = $selectedProjectId ? Project::find($selectedProjectId) : null;

        $kriQuery = KRIProject::with([
            'risiko' => function($query) {
                $query->with(['project', 'peristiwaRisiko']);
            },
            'kriProjectMonitorings' => function($query) use ($selectedYear) {
                $query->whereHas('projectMonitoring', function($subQuery) use ($selectedYear) {
                    $subQuery->where('tahun', $selectedYear);
                })->orderBy('id', 'desc');
            }
        ])
        ->whereHas('risiko.project', function($q){
            $q->whereNull('deleted_at')->where('is_closed', false);
        });

        $kriQuery->when($selectedUnitId, function ($query, $selectedUnitId) {
            $unit = Unit::find($selectedUnitId);
            if ($unit) {
                $query->whereHas('risiko.project', function ($q) use ($unit) {
                    $q->where('cost_center_parent', $unit->cost_center);
                });
            }
        });

        $kriQuery->when($selectedProjectId, function ($query, $selectedProjectId) {
            $query->whereHas('risiko', function($q) use ($selectedProjectId) {
                $q->where('project_id', $selectedProjectId)
                  ->whereNull('deleted_at')
                  ->where('is_closed', false);
            });
        });

        $kriData = $kriQuery->get();

        $getLatestKriStatus = function($kri) {
            $latestMonitoring = $kri->kriProjectMonitorings->first();
            if ($latestMonitoring) {
                return $latestMonitoring->status_kri_terkini;
            }
            return $kri->status_kri_terkini_q4 ?? $kri->status_kri_terkini_q3 ?? $kri->status_kri_terkini_q2 ?? $kri->status_kri_terkini_q1 ?? 1;
        };

        $processedKriData = $kriData->map(function($kri) use ($getLatestKriStatus) {
            $finalStatusNumeric = $getLatestKriStatus($kri);
            return [
                'kri' => $kri->kri,
                'risiko' => $kri->risiko->peristiwaRisiko->title ?? '-',
                'pemilik_risiko' => $kri->risiko->project->project_name ?? '-',
                'batas_aman' => $kri->batas_aman,
                'batas_waspada' => $kri->batas_waspada,
                'batas_bahaya' => $kri->batas_bahaya,
                'kondisi_saat_ini' => $finalStatusNumeric ?? '-',
                'status' => $finalStatusNumeric,
                'status_priority' => $this->getStatusPriorityFromKriStatus($finalStatusNumeric),
                'kri_object' => $kri
            ];
        });

        $sortedKriData = $processedKriData->sortBy('status_priority')->values();

        $peristiwaRisikoData = $kriData->groupBy('risiko.peristiwaRisiko.title')->map(function($group) use ($getLatestKriStatus) {
            $counts = ['aman' => 0, 'waspada' => 0, 'bahaya' => 0];
            foreach($group as $kri) {
                $status = (int)$getLatestKriStatus($kri);
                if ($status === 1) $counts['aman']++;
                elseif ($status === 2) $counts['waspada']++;
                elseif ($status === 3) $counts['bahaya']++;
            }
            return [
                'peristiwa_risiko' => $group->first()->risiko->peristiwaRisiko->title ?? 'Tidak Diketahui',
                'aman' => $counts['aman'],
                'waspada' => $counts['waspada'],
                'bahaya' => $counts['bahaya'],
                'total' => array_sum($counts)
            ];
        })->values();

        $projectData = $kriData->groupBy('risiko.project.project_name')->map(function($group) use ($getLatestKriStatus) {
            $counts = ['aman' => 0, 'waspada' => 0, 'bahaya' => 0];
            foreach($group as $kri) {
                $status = (int)$getLatestKriStatus($kri);
                if ($status === 1) $counts['aman']++;
                elseif ($status === 2) $counts['waspada']++;
                elseif ($status === 3) $counts['bahaya']++;
            }
            return [
                'project_name' => $group->first()->risiko->project->project_name ?? 'Tidak Diketahui',
                'aman' => $counts['aman'],
                'waspada' => $counts['waspada'],
                'bahaya' => $counts['bahaya'],
                'total' => array_sum($counts)
            ];
        })->values();

        $peristiwaRisikoList = PeristiwaRisiko::orderBy('title')->get();

        return view('dashboard-kri-project', compact(
            'units',
            'selectedUnitId',
            'projects',
            'selectedProject',
            'selectedProjectId',
            'selectedYear',
            'yearList',
            'sortedKriData',
            'peristiwaRisikoData',
            'projectData',
            'peristiwaRisikoList'
        ));
    }

    public function executiveSummaryProject(Request $request)
    {
        $selectedUnitId = $request->input('unit_id');
        $selectedProjectId = $request->input('project_id');
        $selectedPeriod = $request->input('period', now()->format('Y-m'));
        $user = Auth::user();

        $projectsQuery = Project::query();

        if ($user->can('view_all_project')) {
            $costCenterParents = Project::distinct()->pluck('cost_center_parent');
            $units = Unit::where('unit_type_id', 1)
                        ->whereIn('cost_center', $costCenterParents)
                        ->orderBy('name')
                        ->get();
        } else {
            $projectsFromUnit = $user->unit ? $user->unit->projects : collect();
            $projectsDirectlyAssigned = $user->projects;
            $allAllowedProjects = $projectsFromUnit->merge($projectsDirectlyAssigned)->unique('id');

            $assignedProjectIds = $allAllowedProjects->pluck('id');
            $projectsQuery->whereIn('id', $assignedProjectIds);

            $costCenterParents = $allAllowedProjects->pluck('cost_center_parent')->unique()->filter();
            $units = Unit::where('unit_type_id', 1)
                        ->whereIn('cost_center', $costCenterParents)
                        ->orderBy('name')
                        ->get();
        }

        $projectsQuery->when($selectedUnitId, function ($query, $unitId) {
            $unitCostCenter = Unit::find($unitId)?->cost_center;
            return $query->where('cost_center_parent', $unitCostCenter);
        });

        $projects = $projectsQuery->get();

        if ($request->ajax() && $selectedUnitId) {
            return response()->json(['projects' => $projects->map(function($p) {
                return ['id' => $p->id, 'project_name' => $p->project_name];
            })]);
        }

        $selectedProject = $selectedProjectId ? Project::find($selectedProjectId) : null;
        $selectedProjectPeriode = $selectedProject ? $selectedProject->projectPeriodeList()->latest()->first() : null;
        $currentYear = $selectedProjectPeriode && $selectedProjectPeriode?->tahun ? $selectedProjectPeriode->tahun : now()->year;

        $summaryData = [
            'omset_kontrak_total' => 0,
            'omset_penjualan_sd_bulan' => 0,
            'progress_sd_bulan' => 0,
            'lsp_rencana_sd_bulan' => 0,
            'lsp_realisasi_sd_bulan' => 0,
            'led_proyek_total' => 0,
            'lsp_realisasi_incl_led' => 0,
            'omset_penjualan_sd_selesai' => 0,
            'lsp_rencana_sd_selesai' => 0,
            'lsp_realisasi_sd_selesai' => 0,
            'eksposur_risiko_total' => 0,
            'proyeksi_lsp_incl_eksposur' => 0,
        ];

        $highImpactRisks = collect();
        $riskMaps = collect();
        $tahunMonitorings = [date('Y')];
        $formattedCurrentRiskMaps = [];
        $projectRisksJs = collect();
        $openRisks = collect();

        if ($selectedProjectPeriode) {
            $allowedProjectIds = $user->can('view_all_project') ?
                Project::pluck('id') :
                ($user->unit ? $user->unit->projects->pluck('id') : collect())->merge($user->projects->pluck('id'))->unique();

            if (!$allowedProjectIds->contains($selectedProject->id)) {
                return redirect()
                    ->route('executive-summary-project', $request->except('project_id'))
                    ->with('error', 'Anda tidak memiliki hak akses untuk melihat proyek tersebut.');
            }

            try {
                $periodForApi = \Carbon\Carbon::parse($selectedPeriod)->format('Ym');
                $profitCenter = $selectedProject->meta['profit_center'] ?? null;
                $hasilUsahaRecord = null;

                if ($profitCenter) {
                    $hasilUsahaRecord = ProjectHasilUsaha::where('project_id', $selectedProject->id)->where('period', $periodForApi)->first();

                    $apiResponse = (new ApiWika())->getHasilUsahaProject($periodForApi, $profitCenter);

                    if ($apiResponse && $apiResponse['status'] && isset($apiResponse['data']['hasil_usaha'])) {
                        $apiData = $apiResponse['data']['hasil_usaha'];

                        $hasilUsahaRecord = ProjectHasilUsaha::updateOrCreate(
                            ['project_id' => $selectedProject->id, 'period' => $periodForApi],
                            [
                                'profit_center'   => $profitCenter,
                                'response_data'   => $apiResponse['data'],
                                'kontrak_review'    => $apiData['kontrak_review'] ?? 0,
                                'kontrak_review_total'    => $apiData['kontrak_review_total'] ?? 0,
                                'progress_fisik_ra' => $apiData['progress_fisik_ra'] ?? 0,
                                'progress_fisik_ri' => $apiData['progress_fisik_ri'] ?? 0,
                                'penjualan_ra'      => $apiData['penjualan_ra'] ?? 0,
                                'penjualan_ri'      => $apiData['penjualan_ri'] ?? 0,
                                'lsp_review'        => $apiData['lsp_review'] ?? 0,
                                'lsp_ra'            => $apiData['lsp_ra'] ?? 0,
                                'lsp_ri'            => $apiData['lsp_ri'] ?? 0,
                                'lsp_proyeksi'      => $apiData['lsp_proyeksi'] ?? 0,
                            ]
                        );
                    }

                    if ($hasilUsahaRecord) {
                        $summaryData['omset_kontrak_total'] = $hasilUsahaRecord->kontrak_review;
                        $summaryData['omset_penjualan_sd_bulan'] = $hasilUsahaRecord->penjualan_ri;
                        $summaryData['lsp_rencana_sd_bulan'] = $hasilUsahaRecord->lsp_ra;
                        $summaryData['lsp_realisasi_sd_bulan'] = $hasilUsahaRecord->lsp_ri;
                        $summaryData['omset_penjualan_sd_selesai'] = $hasilUsahaRecord->penjualan_ra;
                        $summaryData['lsp_rencana_sd_selesai'] = $hasilUsahaRecord->lsp_review;
                        $summaryData['lsp_realisasi_sd_selesai'] = $hasilUsahaRecord->lsp_proyeksi;

                        $summaryData['progress_sd_bulan'] = ($summaryData['omset_kontrak_total'] > 0)
                            ? ($summaryData['omset_penjualan_sd_bulan'] / $summaryData['omset_kontrak_total']) * 100
                            : 0;
                    }
                }
            } catch (\Exception $e) {
                Log::channel('wikaapi')->error("Gagal mengambil atau memproses data Hasil Usaha Project", [
                    'project_id' => $selectedProject->id,
                    'error' => $e->getMessage()
                ]);
            }

            $ledProyekTotal = LossEventProject::where('project_id', $selectedProjectId)->sum('nilai_kerugian_finansial');

            $activeRiskIds = \App\Models\ProjectRisk::where('project_id', $selectedProjectId)
                ->where('is_closed', false) // FIX: Pastikan risk closed tidak masuk kalkulasi
                ->whereNull('deleted_at')
                ->pluck('id');

            $latestMonitoringIds = \App\Models\ProjectRiskMonitoring::whereIn('risiko_id', $activeRiskIds)
                ->selectRaw('MAX(id) as id')
                ->groupBy('risiko_id')
                ->pluck('id');

            $eksposurRisikoTotal = (float) \App\Models\ProjectRiskMonitoring::whereIn('id', $latestMonitoringIds)
                ->sum('eksposure_risiko');

            $summaryData['led_proyek_total'] = $ledProyekTotal;
            $summaryData['eksposur_risiko_total'] = $eksposurRisikoTotal;

            $summaryData['potensi_hasil_usaha_sd_bulan'] = $summaryData['lsp_realisasi_sd_bulan'] + $summaryData['led_proyek_total'];
            $summaryData['proyeksi_lsp_incl_eksposur'] = $summaryData['lsp_realisasi_sd_selesai'] - $summaryData['eksposur_risiko_total'];

            $selectedProjectPeriode->load([
                'projectRisks' => fn($query) => $query->where('is_closed', false)->orderBy('id', 'asc'), // FIX
                'projectRisks.peristiwaRisiko',
                'projectRisks.projectRiskAnalisa.skalaDampakObj',
                'projectRisks.projectRiskAnalisa.skalaDampakResidualObj',
                'projectRisks.projectRiskAnalisa.skalaProbabilitas',
                'projectRisks.projectRiskAnalisa.skalaProbabilitasResidual',
                'projectRisks.projectRiskMonitorings' => fn($query) => $query->orderBy('id', 'desc')->with(['skalaProbabilitas', 'skalaDampakObj']),
            ]);

            $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
                ->get()->keyBy(fn($item) => $item->skala_dampak . '-' . $item->skala_probabilitas);

            $projectRisksJs = $selectedProjectPeriode->projectRisks
                ->where('is_closed', false)
                ->sortByDesc(function ($risk) {
                    return optional($risk->projectRiskAnalisa)->skala_risiko ?? -1;
                })
                ->values()
                ->mapWithKeys(function($risk, $index) {
                    $risk->nomor_urut = $index + 1;
                    return [$risk->id => $risk];
                });

            $openRisks = $projectRisksJs->values();

            $selectedProjectPeriode->projectRisks->each(fn($pr) => $pr->append('currentRiskMapsMonth'));

            $allYears = $selectedProjectPeriode->projectRisks->pluck('projectRiskMonitorings')->flatten()->pluck('tahun')->unique();
            $allYears->push(Carbon::parse($selectedProject->start_date)->year);
            $allYears->push(now()->year);
            if ($allYears->filter()->isNotEmpty()) {
                $tahunMonitorings = range($allYears->min(), $allYears->max());
            }

            // foreach ($selectedProjectPeriode->projectRisks as $projectRisk) {
            //     $riskMapData = $projectRisk->currentRiskMapsMonth;
            //     foreach ($tahunMonitorings as $tahun) {
            //         for ($month = 1; $month <= 12; $month++) {
            //             $currentValue = $riskMapData[$tahun . '-' . $month] ?? null;
            //             if (!$currentValue) {
            //                 $currentValue = $riskMapData['inherent'];
            //             }
            //             $currentValue['nilai_dampak_formatted'] = 'Rp ' . number_format($currentValue['nilai_dampak'] ?? 0, 0, ',', '.');
            //             $formattedCurrentRiskMaps[$projectRisk->id][$tahun][] = $currentValue;
            //         }
            //     }
            // }

            foreach ($selectedProjectPeriode->projectRisks as $projectRisk) {
                // 1. Ambil data Inheren sebagai fallback paling dasar
                $inherentData = null;
                if ($projectRisk->projectRiskAnalisa) {
                    $inherentData = [
                        'skala_dampak' => $projectRisk->projectRiskAnalisa->skala_dampak,
                        'skala_probabilitas' => $projectRisk->projectRiskAnalisa->skalaProbabilitas->tingkat ?? null,
                        'skala_probabilitas_obj' => $projectRisk->projectRiskAnalisa->skalaProbabilitas,
                        'skala_dampak_obj' => $projectRisk->projectRiskAnalisa->skalaDampakObj,
                        'level_risiko' => $projectRisk->projectRiskAnalisa->level_risiko,
                        'nilai_risiko' => $projectRisk->projectRiskAnalisa->skala_risiko,
                        'nilai_dampak' => $projectRisk->projectRiskAnalisa->nilai_dampak,
                        'nilai_probabilitas' => $projectRisk->projectRiskAnalisa->nilai_probabilitas,
                    ];
                }

                // 2. Filter HANYA monitoring yang sudah Published (status 100 / approved)
                $publishedMonitorings = $projectRisk->projectRiskMonitorings
                    ->filter(function($mon) {
                        return $mon->status == 100 || $mon->is_approved == 1;
                    })
                    ->sortBy(function($mon) {
                        // Urutkan dari yang terlama ke terbaru
                        return sprintf('%04d%02d', $mon->tahun, $mon->month);
                    });

                foreach ($tahunMonitorings as $tahun) {
                    for ($month = 1; $month <= 12; $month++) {
                        // 3. Cari monitoring Published terakhir sampai pada target bulan/tahun
                        $latestPublishedMon = $publishedMonitorings->filter(function($mon) use ($tahun, $month) {
                            if ($mon->tahun < $tahun) return true;
                            if ($mon->tahun == $tahun && $mon->month <= $month) return true;
                            return false;
                        })->last();

                        $currentValue = $inherentData; // Default ke Inheren

                        if ($latestPublishedMon) {
                            // Replace dengan data monitoring yang valid
                            $currentValue = [
                                'skala_dampak' => $latestPublishedMon->skala_dampak,
                                'skala_probabilitas' => $latestPublishedMon->skalaProbabilitas->tingkat ?? null,
                                'skala_probabilitas_obj' => $latestPublishedMon->skalaProbabilitas,
                                'skala_dampak_obj' => $latestPublishedMon->skalaDampakObj,
                                'level_risiko' => $latestPublishedMon->level_risiko,
                                'nilai_risiko' => $latestPublishedMon->skala_risiko,
                                'nilai_dampak' => $latestPublishedMon->nilai_dampak,
                                'nilai_probabilitas' => $latestPublishedMon->nilai_probabilitas,
                            ];
                        }

                        if ($currentValue) {
                            $currentValue['nilai_dampak_formatted'] = 'Rp ' . number_format($currentValue['nilai_dampak'] ?? 0, 0, ',', '.');
                        }

                        $formattedCurrentRiskMaps[$projectRisk->id][$tahun][] = $currentValue;
                    }
                }
            }
        }

        $sortedKriData = collect();
        $currentYear = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->year;

        if ($selectedProject) {
            $kriQuery = KRIProject::with([
                'risiko.peristiwaRisiko',
                'kriProjectMonitorings' => function($query) use ($currentYear) {
                    $query->whereHas('projectMonitoring', fn($sub) => $sub->where('tahun', $currentYear))
                          ->orderBy('id', 'desc');
                }
            ])
            ->whereHas('risiko', fn($q) => $q->where('project_id', $selectedProject->id)
                ->whereNull('deleted_at')
                ->where('is_closed', false));

            $kriData = $kriQuery->get();

            $getLatestKriStatus = function($kri) {
                $latestMonitoring = $kri->kriProjectMonitorings->first();
                return optional($latestMonitoring)->status_kri_terkini ?? 1;
            };

            $processedKriData = $kriData->map(function($kri) use ($getLatestKriStatus) {
                $finalStatusNumeric = $getLatestKriStatus($kri);
                return [
                    'project_id' => $kri->risiko?->project_id,
                    'risiko_id' => $kri->risiko_id,
                    'risiko' => $kri->risiko->peristiwa_risiko_id === 0 ? $kri->risiko->rencana_kegiatan : ($kri->risiko->peristiwaRisiko->title ?? $risk->peristiwa_risiko ?? '-'),
                    'penyebab' => $kri->risiko->penyebabRisikoProjects ?? [],
                    'kri' => $kri->kri,
                    'batas_aman' => $kri->batas_aman,
                    'batas_waspada' => $kri->batas_waspada,
                    'batas_bahaya' => $kri->batas_bahaya,
                    'kondisi_saat_ini' => optional($kri->kriProjectMonitorings->first())->nilai_kri_terkini ?? '-',
                    'status' => $finalStatusNumeric,
                ];
            });

            $sortedKriData = $processedKriData->filter(function($kri) {
                return in_array($kri['status'], [2, 3]);
            })->sortByDesc('status')->values();
        }

        $efektivitasPerlakuanData = [];
        $efektifRisks = collect();
        $tidakEfektifRisks = collect();
        $closedRisks = [];

        if ($selectedProject && $selectedProjectPeriode) {
            $allRisksForPie = ProjectRisk::where('project_periode_list_id', $selectedProjectPeriode->id)
                ->where('is_closed', true)
                ->get();

            list($efektifRisks, $tidakEfektifRisks) = $allRisksForPie->partition(function ($risk) {
                return $risk->efektivitas_perlakuan_risiko >= 0;
            });

            $efektivitasPerlakuanData = [
                ['label' => 'Efektif', 'value' => $efektifRisks->count(), 'color' => '#5470C6'],
                ['label' => 'Tidak Efektif', 'value' => $tidakEfektifRisks->count(), 'color' => '#EE6666'],
            ];
        }

        $topLossEvents = collect();
        if ($selectedProject) {
            $topLossEvents = \App\Models\LossEventProject::where('project_id', $selectedProject->id)
                ->with(['project', 'kategoriKejadian', 'peristiwaRisiko'])
                ->orderBy('nilai_kerugian_finansial', 'desc')
                ->take(10)
                ->get();
        }


        return view('executive-summary-project', compact(
            'units',
            'projects',
            'selectedUnitId',
            'selectedProjectId',
            'selectedProject',
            'selectedPeriod',
            'summaryData',
            'openRisks',
            'riskMaps',
            'tahunMonitorings',
            'formattedCurrentRiskMaps',
            'projectRisksJs',
            'sortedKriData',
            'closedRisks',
            'efektivitasPerlakuanData',
            'efektifRisks',
            'tidakEfektifRisks',
            'topLossEvents',
        ));
    }

    public function executiveSummaryUnit(Request $request)
    {
        $selectedUnitId = $request->input('unit_id');
        $selectedPeriod = $request->input('period', now()->format('Y-m'));
        $currentYear = Carbon::createFromFormat('Y-m', $selectedPeriod)->year;
        $currentMonth = Carbon::createFromFormat('Y-m', $selectedPeriod)->month;
        $currentQuarter = (int)ceil($currentMonth / 3);

        $user = auth()->user();
        if (Gate::check('view_all_division')) {
            $units = Unit::where('unit_type_id', 1)->orderBy('name')->get();
        } else {
            $units = Unit::where('id', $user->unit_id)->where('unit_type_id', 1)->get();
        }

        $requestedUnitId = $request->input('unit_id');
        $selectedUnit = $requestedUnitId ? $units->firstWhere('id', $requestedUnitId) : null;
        if (!$selectedUnit && $units->isNotEmpty()) {
            $selectedUnit = $units->first();
        }

        $selectedUnitId = $selectedUnit ? $selectedUnit->id : null;

        $summaryData = [
            'omset_penjualan_sd_bulan'    => 0,
            'lsp_rencana_sd_bulan'        => 0,
            'lsp_realisasi_sd_bulan'      => 0,
            'omset_penjualan_sd_des'      => 0,
            'lsp_rencana_sd_des'          => 0,
            'proyeksi_lsp_sd_des'         => 0,
            'led_proyek_total'            => 0,
            'eksposur_risiko_annual'      => 0,
            'eksposur_risiko_total'       => 0,
            'led_divisi_total'            => 0,
            'hasil_usaha_sd_bulan'        => 0,
            'proyeksi_hasil_usaha_sd_des' => 0,
        ];

        $isProjectUnit = false;
        $openRisks = collect();
        $riskMaps = collect();
        $tahunMonitorings = [$currentYear];
        $formattedCurrentRiskMaps = [];
        $openRisksJs = collect();
        $sortedKriData = collect();
        $closedRisks = collect();
        $efektivitasPerlakuanData = [];
        $efektifRisks = collect();
        $tidakEfektifRisks = collect();
        $topLedProjects = collect();
        $topLedDivisi = collect();
        $topEksposurAnnual = collect();
        $topEksposurTotal = collect();

        if ($selectedUnit) {
            $projectHandlingCostCenters = Project::distinct()->pluck('cost_center_parent');
            $isProjectUnit = $projectHandlingCostCenters->contains($selectedUnit->cost_center);

            $periode = Periode::where('tahun', $currentYear)->first();
            $unitRisks = collect();
            if ($periode) {
                $unitRisks = IdentifikasiRisiko::with([
                    'riskAnalysis.skalaProbabilitas',
                    'riskAnalysis.skalaDampakObj',
                    'riskAnalysis.skalaDampakResidualQ1Obj',
                    'riskAnalysis.skalaDampakResidualQ2Obj',
                    'riskAnalysis.skalaDampakResidualQ3Obj',
                    'riskAnalysis.skalaDampakResidualQ4Obj',
                    'riskAnalysis.skalaProbabilitasResidualQ1',
                    'riskAnalysis.skalaProbabilitasResidualQ2',
                    'riskAnalysis.skalaProbabilitasResidualQ3',
                    'riskAnalysis.skalaProbabilitasResidualQ4',
                    'monitoringRisikos' => function($q) {
                        $q->where(function($sq) {
                            $sq->where('status', 100)->orWhere('is_approved', true);
                        })->orderBy('month', 'desc')->orderBy('id', 'desc');
                    },
                    'monitoringRisikos.skalaProbabilitas',
                    'monitoringRisikos.skalaDampakObj',
                    'peristiwaRisiko',
                    'kris.kriUnitMonitorings.unitRiskMonitoring',
                    'penyebabRisiko',
                ])
                ->where('unit_id', $selectedUnit->id)
                ->where('periode_id', $periode->id)
                ->where('status', 6)
                ->where('is_closed', false)
                ->whereNull('deleted_at')
                ->get();
            }

            try {
                $periodForQuery = Carbon::createFromFormat('Y-m', $selectedPeriod)->format('Ym');

                $summaryRecord = UnitHasilUsaha::where('unit_id', $selectedUnit->id)->where('period', $periodForQuery)->first();

                if ($summaryRecord) {
                    $summaryData['omset_penjualan_sd_bulan'] = $summaryRecord->penjualan_ri ?? 0;
                    $summaryData['lsp_rencana_sd_bulan']     = $summaryRecord->lsp_ra ?? 0;
                    $summaryData['lsp_realisasi_sd_bulan']   = $summaryRecord->lsp_ri ?? 0;
                    $summaryData['omset_penjualan_sd_des']   = $summaryRecord->penjualan_ra ?? 0;
                    $summaryData['lsp_rencana_sd_des']       = $summaryRecord->lsp_review ?? 0;
                    $summaryData['proyeksi_lsp_sd_des']      = $summaryRecord->lsp_proyeksi ?? 0;
                }

            } catch (\Exception $e) {
                Log::error("Gagal mengambil data Hasil Usaha Unit", [
                    'unit_id' => $selectedUnit->id,
                    'period' => $periodForQuery ?? $selectedPeriod,
                    'error' => $e->getMessage()
                ]);
            }

            $summaryData['led_proyek_total'] = $isProjectUnit ? LossEventProject::whereHas('project', fn($q) => $q->where('cost_center_parent', $selectedUnit->cost_center))->whereYear('tanggal_kejadian', $currentYear)->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)')) : 0;
            $summaryData['led_divisi_total'] = LossEvent::where('unit_id', $selectedUnit->id)->whereYear('tanggal_kejadian', $currentYear)->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)'));

            $baseEksposurQuery = UnitRiskMonitoring::whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $selectedUnitId)->where('periode_id', optional($periode)->id)->where('is_closed', false));
            $summaryData['eksposur_risiko_annual'] = (clone $baseEksposurQuery)->sum('eksposure_risiko');
            $summaryData['eksposur_risiko_total'] = (clone $baseEksposurQuery)->where('month', '<=', $currentMonth)->sum('eksposure_risiko');

            $summaryData['hasil_usaha_sd_bulan'] = $summaryData['lsp_realisasi_sd_bulan'] - $summaryData['led_proyek_total'] - $summaryData['led_divisi_total'];
            $summaryData['proyeksi_hasil_usaha_sd_des'] = $summaryData['proyeksi_lsp_sd_des'] - $summaryData['eksposur_risiko_annual'];

            $openRisks = $unitRisks->sortByDesc(fn($risk) => optional($risk->riskAnalysis)->skala_risiko ?? -1);

            $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')->get()->keyBy(fn($item) => $item->skala_dampak . '-' . $item->skala_probabilitas);

            $unitRisks->each(fn($risk) => $risk->append('currentRiskMapsMonth'));

            foreach ($unitRisks as $risk) {
                $riskMapData = $risk->currentRiskMapsMonth;

                foreach ($riskMapData as $monthKey => $mapData) {
                    if ($monthKey === 'inherent' || !isset($mapData['month'])) continue;

                    $mapData['nilai_dampak_formatted'] = 'Rp ' . number_format($mapData['nilai_dampak'] ?? 0, 0, ',', '.');
                    $mapData['nilai_probabilitas_formatted'] = $mapData['nilai_probabilitas'] ?? '-';
                    $mapData['nilai_risiko_formatted'] = $mapData['skala_risiko'] ?? '-';
                    $mapData['level_risiko_formatted'] = $mapData['level_risiko'] ?? '-';

                    $formattedCurrentRiskMaps[$risk->id][$currentYear][] = $mapData;
                }
            }

            $openRisksJs = $openRisks->values()->mapWithKeys(function($risk, $index) {
                $risk->nomor_urut_js = $index + 1;
                return [$risk->id => $risk];
            });

            $allKRI = $unitRisks->pluck('kris')->flatten();
            $processedKri = $allKRI->map(function($kri) use ($currentMonth) {
                $monitoringForPeriod = $kri->kriUnitMonitorings->sortByDesc('id')->first();

                $status = optional($monitoringForPeriod)->status_kri_terkini ?? 1;
                return [
                    'risiko' => optional(optional($kri->identifikasiRisiko)->peristiwaRisiko)->title ?? $kri->identifikasiRisiko->peristiwa_risiko,
                    'penyebab' => ($kri->identifikasiRisiko && is_array($kri->identifikasiRisiko->penyebab)) ? array_column($kri->identifikasiRisiko->penyebab, 'penyebab_risiko') : [],
                    'kri' => $kri->kri, 'batas_aman' => $kri->batas_aman, 'batas_waspada' => $kri->batas_waspada, 'batas_bahaya' => $kri->batas_bahaya,
                    'kondisi_saat_ini' => optional($monitoringForPeriod)->nilai_kri_terkini ?? '-',
                    'status' => $status,
                ];
            });
            $sortedKriData = $processedKri->filter(fn($k) => in_array($k['status'], [2, 3]))->sortByDesc('status')->values();

            // Ambil semua risiko yang tertutup di periode dan unit ini
            $allRisksForPie = IdentifikasiRisiko::where('unit_id', $selectedUnitId)
                ->where('periode_id', optional($periode)->id)
                ->where('is_closed', true)
                ->get();

            list($efektifRisks, $tidakEfektifRisks) = $allRisksForPie->partition(fn ($risk) => $risk->efektivitas_perlakuan_risiko >= 0);
            $efektivitasPerlakuanData = [
                ['label' => 'Efektif', 'value' => $efektifRisks->count(), 'color' => '#5470C6'],
                ['label' => 'Tidak Efektif', 'value' => $tidakEfektifRisks->count(), 'color' => '#EE6666'],
            ];

            if ($isProjectUnit) {
                $projectIds = Project::where('cost_center_parent', $selectedUnit->cost_center)->pluck('id');
                if ($projectIds->isNotEmpty()) {
                    $topLedProjects = LossEventProject::select('project_id', DB::raw('SUM(CAST(nilai_kerugian_finansial AS NUMERIC)) as total_kerugian'))
                        ->whereIn('project_id', $projectIds)->whereYear('tanggal_kejadian', $currentYear)
                        ->groupBy('project_id')->orderByDesc('total_kerugian')->take(5)->with('project')->get();
                }
            }

            $topLedDivisi = LossEvent::where('unit_id', $selectedUnitId)->whereYear('tanggal_kejadian', $currentYear)
              ->with(['kategoriKejadian', 'jenisRisiko'])
              ->orderByRaw('CAST(nilai_kerugian_finansial AS NUMERIC) DESC')
              ->take(10)
              ->get();

            if ($isProjectUnit) {
                $topEksposurAnnual = ProjectRiskMonitoring::query()
                    ->select('projects.project_name', DB::raw('MAX(project_risk_monitorings.eksposure_risiko) as max_eksposur'))
                    ->join('project_risks', 'project_risk_monitorings.risiko_id', '=', 'project_risks.id')
                    ->join('projects', 'project_risks.project_id', '=', 'projects.id')
                    ->where('projects.cost_center_parent', $selectedUnit->cost_center)
                    ->where('project_risk_monitorings.tahun', $currentYear)
                    ->whereNull('project_risks.deleted_at') // FIX
                    ->where('project_risks.is_closed', false) // FIX
                    ->groupBy('projects.id', 'projects.project_name')
                    ->orderByDesc('max_eksposur')
                    ->take(5)
                    ->get();

                $topEksposurTotal = ProjectRiskMonitoring::query()
                    ->select('projects.project_name', DB::raw('SUM(project_risk_monitorings.eksposure_risiko) as total_eksposur'))
                    ->join('project_risks', 'project_risk_monitorings.risiko_id', '=', 'project_risks.id')
                    ->join('projects', 'project_risks.project_id', '=', 'projects.id')
                    ->where('projects.cost_center_parent', $selectedUnit->cost_center)
                    ->where('project_risk_monitorings.tahun', $currentYear)
                    ->where('project_risk_monitorings.quarter', '<=', $currentQuarter)
                    ->whereNull('project_risks.deleted_at') // FIX
                    ->where('project_risks.is_closed', false) // FIX
                    ->groupBy('projects.id', 'projects.project_name')
                    ->orderByDesc('total_eksposur')
                    ->take(5)
                    ->get();

            } else {
                $topEksposurAnnual = UnitRiskMonitoring::select('identifikasi_risiko_id', DB::raw('MAX(eksposure_risiko) as max_eksposur'))
                    ->whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $selectedUnitId)->where('periode_id', optional($periode)->id)->where('is_closed', false)) // FIX
                    ->groupBy('identifikasi_risiko_id')->orderByDesc('max_eksposur')->take(5)
                    ->with('identifikasiRisiko.peristiwaRisiko')->get();

                $topEksposurTotal = UnitRiskMonitoring::select('identifikasi_risiko_id', DB::raw('SUM(eksposure_risiko) as total_eksposur'))
                    ->whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $selectedUnitId)->where('periode_id', optional($periode)->id)->where('is_closed', false)) // FIX
                    ->where('month', '<=', $currentMonth)
                    ->groupBy('identifikasi_risiko_id')->orderByDesc('total_eksposur')->take(5)
                    ->with('identifikasiRisiko.peristiwaRisiko')->get();
            }
        }

        return view('executive-summary-unit', compact(
            'units',
            'selectedUnitId',
            'selectedUnit',
            'selectedPeriod',
            'summaryData',
            'isProjectUnit',
            'openRisks',
            'riskMaps',
            'tahunMonitorings',
            'formattedCurrentRiskMaps',
            'openRisksJs',
            'sortedKriData',
            'closedRisks',
            'efektivitasPerlakuanData',
            'efektifRisks',
            'tidakEfektifRisks',
            'topLedProjects',
            'topLedDivisi',
            'topEksposurAnnual',
            'topEksposurTotal',
            'currentQuarter',
        ));
    }

    public function executiveSummaryAnper(Request $request)
    {
        $selectedUnitId = $request->input('unit_id');
        $selectedPeriod = $request->input('period', now()->format('Y-m'));
        $currentYear = Carbon::createFromFormat('Y-m', $selectedPeriod)->year;
        $currentMonth = Carbon::createFromFormat('Y-m', $selectedPeriod)->month;
        $currentQuarter = (int)ceil($currentMonth / 3);

        $user = auth()->user();
        if (Gate::check('ap_admin')) {
            $units = Unit::where('unit_type_id', 2)->orderBy('name')->get();
        } else {
            $units = Unit::where('id', $user->unit_id)->where('unit_type_id', 2)->get();
        }

        $requestedUnitId = $request->input('unit_id');
        $selectedUnit = $requestedUnitId ? $units->firstWhere('id', $requestedUnitId) : null;

        if (!$selectedUnit && $units->isNotEmpty()) {
            $selectedUnit = $units->first();
        }

        $selectedUnitId = $selectedUnit ? $selectedUnit->id : null;

        $summaryData = [
            'omset_penjualan_sd_bulan'    => 0,
            'lsp_rencana_sd_bulan'        => 0,
            'lsp_realisasi_sd_bulan'      => 0,
            'omset_penjualan_sd_des'      => 0,
            'lsp_rencana_sd_des'          => 0,
            'proyeksi_lsp_sd_des'         => 0,
            'led_proyek_total'            => 0,
            'eksposur_risiko_annual'      => 0,
            'eksposur_risiko_total'       => 0,
            'led_divisi_total'            => 0,
            'hasil_usaha_sd_bulan'        => 0,
            'proyeksi_hasil_usaha_sd_des' => 0,
        ];

        $isProjectUnit = false;
        $openRisks = collect();
        $riskMaps = collect();
        $tahunMonitorings = [$currentYear];
        $formattedCurrentRiskMaps = [];
        $openRisksJs = collect();
        $sortedKriData = collect();
        $closedRisks = collect();
        $efektivitasPerlakuanData = [];
        $efektifRisks = collect();
        $tidakEfektifRisks = collect();
        $topLedProjects = collect();
        $topLedDivisi = collect();
        $topEksposurAnnual = collect();
        $topEksposurTotal = collect();

        if ($selectedUnit) {
            $projectHandlingCostCenters = Project::distinct()->pluck('cost_center_parent');
            $isProjectUnit = $projectHandlingCostCenters->contains($selectedUnit->cost_center);

            $periode = Periode::where('tahun', $currentYear)->first();
            $unitRisks = collect();
            if ($periode) {
                $unitRisks = IdentifikasiRisiko::with([
                    'riskAnalysis.skalaProbabilitas',
                    'riskAnalysis.skalaDampakObj',
                    'riskAnalysis.skalaDampakResidualQ1Obj',
                    'riskAnalysis.skalaDampakResidualQ2Obj',
                    'riskAnalysis.skalaDampakResidualQ3Obj',
                    'riskAnalysis.skalaDampakResidualQ4Obj',
                    'riskAnalysis.skalaProbabilitasResidualQ1',
                    'riskAnalysis.skalaProbabilitasResidualQ2',
                    'riskAnalysis.skalaProbabilitasResidualQ3',
                    'riskAnalysis.skalaProbabilitasResidualQ4',
                    'monitoringRisikos' => function($q) {
                        $q->where(function($sq) {
                            $sq->where('status', 100)->orWhere('is_approved', true);
                        })->orderBy('month', 'desc')->orderBy('id', 'desc');
                    },
                    'monitoringRisikos.skalaProbabilitas',
                    'monitoringRisikos.skalaDampakObj',
                    'peristiwaRisiko',
                    'kris.kriUnitMonitorings.unitRiskMonitoring',
                    'penyebabRisiko',
                ])
                ->where('unit_id', $selectedUnit->id)
                ->where('periode_id', $periode->id)
                ->where('status', 6)
                ->where('is_closed', false)
                ->whereNull('deleted_at')
                ->get();
            }

            try {
                $periodForQuery = Carbon::createFromFormat('Y-m', $selectedPeriod)->format('Ym');
                $summaryRecord = UnitHasilUsaha::where('unit_id', $selectedUnit->id)->where('period', $periodForQuery)->first();

                if ($summaryRecord) {
                    $summaryData['omset_penjualan_sd_bulan'] = $summaryRecord->penjualan_ri ?? 0;
                    $summaryData['lsp_rencana_sd_bulan']     = $summaryRecord->lsp_ra ?? 0;
                    $summaryData['lsp_realisasi_sd_bulan']   = $summaryRecord->lsp_ri ?? 0;
                    $summaryData['omset_penjualan_sd_des']   = $summaryRecord->penjualan_ra ?? 0;
                    $summaryData['lsp_rencana_sd_des']       = $summaryRecord->lsp_review ?? 0;
                    $summaryData['proyeksi_lsp_sd_des']      = $summaryRecord->lsp_proyeksi ?? 0;
                }
            } catch (\Exception $e) {
                Log::error("Gagal mengambil data Hasil Usaha Unit", [
                    'unit_id' => $selectedUnit->id,
                    'period' => $periodForQuery ?? $selectedPeriod,
                    'error' => $e->getMessage()
                ]);
            }

            $summaryData['led_proyek_total'] = $isProjectUnit ? LossEventProject::whereHas('project', fn($q) => $q->where('cost_center_parent', $selectedUnit->cost_center))->whereYear('tanggal_kejadian', $currentYear)->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)')) : 0;
            $summaryData['led_divisi_total'] = LossEvent::where('unit_id', $selectedUnit->id)->whereYear('tanggal_kejadian', $currentYear)->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)'));

            $baseEksposurQuery = UnitRiskMonitoring::whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $selectedUnitId)->where('periode_id', optional($periode)->id)->where('is_closed', false));
            $summaryData['eksposur_risiko_annual'] = (clone $baseEksposurQuery)->sum('eksposure_risiko');
            $summaryData['eksposur_risiko_total'] = (clone $baseEksposurQuery)->where('month', '<=', $currentMonth)->sum('eksposure_risiko');

            $summaryData['hasil_usaha_sd_bulan'] = $summaryData['lsp_realisasi_sd_bulan'] - $summaryData['led_proyek_total'] - $summaryData['led_divisi_total'];
            $summaryData['proyeksi_hasil_usaha_sd_des'] = $summaryData['proyeksi_lsp_sd_des'] - $summaryData['eksposur_risiko_annual'];

            $openRisks = $unitRisks->sortByDesc(fn($risk) => optional($risk->riskAnalysis)->skala_risiko ?? -1);

            $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')->get()->keyBy(fn($item) => $item->skala_dampak . '-' . $item->skala_probabilitas);

            $unitRisks->each(fn($risk) => $risk->append('currentRiskMapsMonth'));

            foreach ($unitRisks as $risk) {
                $riskMapData = $risk->currentRiskMapsMonth;

                foreach ($riskMapData as $monthKey => $mapData) {
                    if ($monthKey === 'inherent' || !isset($mapData['month'])) continue;

                    $mapData['nilai_dampak_formatted'] = 'Rp ' . number_format($mapData['nilai_dampak'] ?? 0, 0, ',', '.');
                    $mapData['nilai_probabilitas_formatted'] = $mapData['nilai_probabilitas'] ?? '-';
                    $mapData['nilai_risiko_formatted'] = $mapData['skala_risiko'] ?? '-';
                    $mapData['level_risiko_formatted'] = $mapData['level_risiko'] ?? '-';

                    $formattedCurrentRiskMaps[$risk->id][$currentYear][] = $mapData;
                }
            }

            $openRisksJs = $openRisks->values()->mapWithKeys(function($risk, $index) {
                $risk->nomor_urut_js = $index + 1;
                return [$risk->id => $risk];
            });

            $allKRI = $unitRisks->pluck('kris')->flatten();
            $processedKri = $allKRI->map(function($kri) use ($currentMonth) {
                $monitoringForPeriod = $kri->kriUnitMonitorings->sortByDesc('id')->first();
                $status = optional($monitoringForPeriod)->status_kri_terkini ?? 1;
                return [
                    'risiko' => optional(optional($kri->identifikasiRisiko)->peristiwaRisiko)->title ?? $kri->identifikasiRisiko->peristiwa_risiko,
                    'penyebab' => ($kri->identifikasiRisiko && is_array($kri->identifikasiRisiko->penyebab)) ? array_column($kri->identifikasiRisiko->penyebab, 'penyebab_risiko') : [],
                    'kri' => $kri->kri, 'batas_aman' => $kri->batas_aman, 'batas_waspada' => $kri->batas_waspada, 'batas_bahaya' => $kri->batas_bahaya,
                    'kondisi_saat_ini' => optional($monitoringForPeriod)->nilai_kri_terkini ?? '-',
                    'status' => $status,
                ];
            });
            $sortedKriData = $processedKri->filter(fn($k) => in_array($k['status'], [2, 3]))->sortByDesc('status')->values();

            $allRisksForPie = IdentifikasiRisiko::where('unit_id', $selectedUnitId)
                ->where('periode_id', optional($periode)->id)
                ->where('is_closed', true)
                ->get();

            list($efektifRisks, $tidakEfektifRisks) = $allRisksForPie->partition(fn ($risk) => $risk->efektivitas_perlakuan_risiko >= 0);
            $efektivitasPerlakuanData = [
                ['label' => 'Efektif', 'value' => $efektifRisks->count(), 'color' => '#5470C6'],
                ['label' => 'Tidak Efektif', 'value' => $tidakEfektifRisks->count(), 'color' => '#EE6666'],
            ];

            if ($isProjectUnit) {
                $projectIds = Project::where('cost_center_parent', $selectedUnit->cost_center)->pluck('id');
                if ($projectIds->isNotEmpty()) {
                    $topLedProjects = LossEventProject::select('project_id', DB::raw('SUM(CAST(nilai_kerugian_finansial AS NUMERIC)) as total_kerugian'))
                        ->whereIn('project_id', $projectIds)->whereYear('tanggal_kejadian', $currentYear)
                        ->groupBy('project_id')->orderByDesc('total_kerugian')->take(5)->with('project')->get();
                }
            }

            $topLedDivisi = LossEvent::where('unit_id', $selectedUnitId)->whereYear('tanggal_kejadian', $currentYear)
              ->with(['kategoriKejadian', 'jenisRisiko'])
              ->orderByRaw('CAST(nilai_kerugian_finansial AS NUMERIC) DESC')
              ->take(10)
              ->get();

            if ($isProjectUnit) {
                $topEksposurAnnual = ProjectRiskMonitoring::query()
                    ->select('projects.project_name', DB::raw('MAX(project_risk_monitorings.eksposure_risiko) as max_eksposur'))
                    ->join('project_risks', 'project_risk_monitorings.risiko_id', '=', 'project_risks.id')
                    ->join('projects', 'project_risks.project_id', '=', 'projects.id')
                    ->where('projects.cost_center_parent', $selectedUnit->cost_center)
                    ->where('project_risk_monitorings.tahun', $currentYear)
                    ->whereNull('project_risks.deleted_at')
                    ->where('project_risks.is_closed', false)
                    ->groupBy('projects.id', 'projects.project_name')
                    ->orderByDesc('max_eksposur')
                    ->take(5)
                    ->get();

                $topEksposurTotal = ProjectRiskMonitoring::query()
                    ->select('projects.project_name', DB::raw('SUM(project_risk_monitorings.eksposure_risiko) as total_eksposur'))
                    ->join('project_risks', 'project_risk_monitorings.risiko_id', '=', 'project_risks.id')
                    ->join('projects', 'project_risks.project_id', '=', 'projects.id')
                    ->where('projects.cost_center_parent', $selectedUnit->cost_center)
                    ->where('project_risk_monitorings.tahun', $currentYear)
                    ->where('project_risk_monitorings.quarter', '<=', $currentQuarter)
                    ->whereNull('project_risks.deleted_at')
                    ->where('project_risks.is_closed', false)
                    ->groupBy('projects.id', 'projects.project_name')
                    ->orderByDesc('total_eksposur')
                    ->take(5)
                    ->get();
            } else {
                $topEksposurAnnual = UnitRiskMonitoring::select('identifikasi_risiko_id', DB::raw('MAX(eksposure_risiko) as max_eksposur'))
                    ->whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $selectedUnitId)->where('periode_id', optional($periode)->id)->where('is_closed', false))
                    ->groupBy('identifikasi_risiko_id')->orderByDesc('max_eksposur')->take(5)
                    ->with('identifikasiRisiko.peristiwaRisiko')->get();

                $topEksposurTotal = UnitRiskMonitoring::select('identifikasi_risiko_id', DB::raw('SUM(eksposure_risiko) as total_eksposur'))
                    ->whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $selectedUnitId)->where('periode_id', optional($periode)->id)->where('is_closed', false))
                    ->where('month', '<=', $currentMonth)
                    ->groupBy('identifikasi_risiko_id')->orderByDesc('total_eksposur')->take(5)
                    ->with('identifikasiRisiko.peristiwaRisiko')->get();
            }
        }

        return view('executive-summary-anper', compact(
            'units',
            'selectedUnitId',
            'selectedUnit',
            'selectedPeriod',
            'summaryData',
            'isProjectUnit',
            'openRisks',
            'riskMaps',
            'tahunMonitorings',
            'formattedCurrentRiskMaps',
            'openRisksJs',
            'sortedKriData',
            'closedRisks',
            'efektivitasPerlakuanData',
            'efektifRisks',
            'tidakEfektifRisks',
            'topLedProjects',
            'topLedDivisi',
            'topEksposurAnnual',
            'topEksposurTotal',
            'currentQuarter',
        ));
    }

    public function executiveSummaryCorporate()
    {
        $selectedPeriod = now()->format('Y-m');
        $currentYear = Carbon::createFromFormat('Y-m', $selectedPeriod)->year;
        $currentMonth = Carbon::createFromFormat('Y-m', $selectedPeriod)->month;
        $currentQuarter = (int)ceil($currentMonth / 3);
        $periode = Periode::where('tahun', $currentYear)->first();

        $summaryData = [
            'omset_penjualan_sd_bulan'    => 0,
            'lsp_rencana_sd_bulan'        => 0,
            'lsp_realisasi_sd_bulan'      => 0,
            'omset_penjualan_sd_des'      => 0,
            'lsp_rencana_sd_des'          => 0,
            'proyeksi_lsp_sd_des'         => 0,
            'kontrak_review'              => 0,

            'led_proyek_total'            => 0,
            'led_divisi_operasi_total'    => 0,
            'led_divisi_fungsi_total'     => 0,
            'eksposur_risiko_total'       => 0,
            'eksposur_risiko_annual'      => 0,
            'hasil_usaha_aktual'          => 0,
            'proyeksi_hasil_usaha_des'    => 0,
        ];

        try {
            $periodForQuery = Carbon::createFromFormat('Y-m', $selectedPeriod)->format('Ym');

            $corporateHasilUsaha = UnitHasilUsaha::where('unit_id', 1)->where('period', $periodForQuery)->first();

            if ($corporateHasilUsaha) {
                $summaryData['omset_penjualan_sd_bulan'] = $corporateHasilUsaha->penjualan_ri ?? 0;
                $summaryData['lsp_rencana_sd_bulan']     = $corporateHasilUsaha->lsp_ra ?? 0;
                $summaryData['lsp_realisasi_sd_bulan']   = $corporateHasilUsaha->lsp_ri ?? 0;
                $summaryData['omset_penjualan_sd_des']   = $corporateHasilUsaha->penjualan_ra ?? 0;
                $summaryData['lsp_rencana_sd_des']       = $corporateHasilUsaha->lsp_review ?? 0;
                $summaryData['proyeksi_lsp_sd_des']      = $corporateHasilUsaha->lsp_proyeksi ?? 0;
                $summaryData['kontrak_review']           = $corporateHasilUsaha->kontrak_review ?? 0;
            }

        } catch (\Exception $e) {
            Log::error("Gagal mengambil data Hasil Usaha Korporat (unit_id=1)", [
                'period' => $periodForQuery ?? $selectedPeriod,
                'error' => $e->getMessage()
            ]);
        }

        $corporateUnitIds = Unit::where('unit_type_id', 4)->pluck('id');
        $operasiUnitIds = Unit::where('unit_type_id', 2)->pluck('id');
        $fungsiUnitIds = Unit::where('unit_type_id', 3)->pluck('id');

        $summaryData['led_proyek_total'] = LossEventProject::whereYear('tanggal_kejadian', $currentYear)->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)'));
        $summaryData['led_divisi_operasi_total'] = LossEvent::whereIn('unit_id', $operasiUnitIds)->whereYear('tanggal_kejadian', $currentYear)->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)'));
        $summaryData['led_divisi_fungsi_total'] = LossEvent::whereIn('unit_id', $fungsiUnitIds)->whereYear('tanggal_kejadian', $currentYear)->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)'));

        $baseEksposurQuery = UnitRiskMonitoring::whereHas('identifikasiRisiko', fn($q) => $q->whereIn('unit_id', $corporateUnitIds)->where('periode_id', optional($periode)->id)->where('is_closed', false));
        $summaryData['eksposur_risiko_total'] = (clone $baseEksposurQuery)->where('month', '<=', $currentMonth)->sum('eksposure_risiko');
        $summaryData['eksposur_risiko_annual'] = (clone $baseEksposurQuery)->sum('eksposure_risiko');

        $total_led = $summaryData['led_proyek_total'] + $summaryData['led_divisi_operasi_total'] + $summaryData['led_divisi_fungsi_total'];

        $summaryData['hasil_usaha_aktual'] = $summaryData['lsp_realisasi_sd_bulan'] - $total_led;
        $summaryData['proyeksi_hasil_usaha_des'] = $summaryData['proyeksi_lsp_sd_des'] - $summaryData['eksposur_risiko_annual'];

        $openRisks = collect();
        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')->get()->keyBy(fn($item) => $item->skala_dampak . '-' . $item->skala_probabilitas);
        $openRisksJs = collect();
        $formattedCurrentRiskMaps = [];
        $sortedKriData = collect();
        $efektivitasPerlakuanData = [];
        $efektifRisks = collect();
        $tidakEfektifRisks = collect();
        $tahunMonitorings = [$currentYear];

        if ($periode && $corporateUnitIds->isNotEmpty()) {
            $baseRisks = IdentifikasiRisiko::with([
                'riskAnalysis.skalaProbabilitas', 'riskAnalysis.skalaDampakObj', 'riskAnalysis.skalaDampakResidualQ1Obj', 'riskAnalysis.skalaDampakResidualQ2Obj', 'riskAnalysis.skalaDampakResidualQ3Obj', 'riskAnalysis.skalaDampakResidualQ4Obj','riskAnalysis.skalaProbabilitasResidualQ1', 'riskAnalysis.skalaProbabilitasResidualQ2', 'riskAnalysis.skalaProbabilitasResidualQ3', 'riskAnalysis.skalaProbabilitasResidualQ4','monitoringRisikos.skalaProbabilitas','peristiwaRisiko','kris.kriUnitMonitorings.unitRiskMonitoring','penyebabRisiko',
            ])
              ->whereIn('unit_id', $corporateUnitIds)
              ->where('periode_id', $periode->id)
              // ->where('status_risiko', 6)
              ->where('is_closed', false)
              ->whereNull('deleted_at');

            $openRisks = (clone $baseRisks)->get()->sortByDesc(fn($risk) => optional($risk->riskAnalysis)->skala_risiko ?? -1);

            $openRisks->each(fn($risk) => $risk->append('currentRiskMapsMonth'));
            foreach ($openRisks as $risk) {
                foreach ($risk->currentRiskMapsMonth as $month => $mapData) {
                    if ($month === 'inherent' || !isset($mapData['month'])) continue;

                    $mapData['nilai_dampak_formatted'] = 'Rp ' . number_format($mapData['nilai_dampak'] ?? 0, 0, ',', '.');
                    $mapData['nilai_probabilitas_formatted'] = $mapData['nilai_probabilitas'] ?? '-';
                    $mapData['nilai_risiko_formatted'] = $mapData['skala_risiko'] ?? '-';
                    $mapData['level_risiko_formatted'] = $mapData['level_risiko'] ?? '-';

                    $formattedCurrentRiskMaps[$risk->id][$currentYear][] = $mapData;
                }
            }
            $openRisksJs = $openRisks->values()->mapWithKeys(function($risk, $index) {
                $risk->nomor_urut_js = $index + 1;
                return [$risk->id => $risk];
            });

            $kriUnitMonitoringTable = (new \App\Models\KRIUnitMonitoring)->getTable();
            $latestKriUnitMonitorings = \App\Models\KRIUnitMonitoring::from(
                    DB::raw('(SELECT *, ROW_NUMBER() OVER (PARTITION BY key_risk_indicator_id ORDER BY id DESC) as rn FROM ' . $kriUnitMonitoringTable . ') as krum')
                )
                ->where('rn', 1)
                ->whereIn('status_kri_terkini', [2, 3])
                ->get()
                ->keyBy('key_risk_indicator_id');

            $kriFromUnits = \App\Models\KRI::whereIn('id', $latestKriUnitMonitorings->pluck('key_risk_indicator_id'))
                ->with([
                    'identifikasiRisiko.unit',
                    'identifikasiRisiko.peristiwaRisiko',
                    'identifikasiRisiko.penyebabRisiko',
                ])
                ->whereHas('identifikasiRisiko', fn($q) => $q->where('is_closed', false)->whereNull('deleted_at'))
                ->get()
                ->map(function ($kri) use ($latestKriUnitMonitorings) {
                    $monitoringForPeriod = $latestKriUnitMonitorings[$kri->id] ?? null;
                    if (!$monitoringForPeriod) return null;

                    $identifikasi = $kri->identifikasiRisiko;

                    $penyebabList = [];
                    if ($identifikasi && $identifikasi->penyebabRisiko->isNotEmpty()) {
                        $penyebabList = $identifikasi->penyebabRisiko->pluck('penyebab_risiko')->all();
                    } elseif (is_array(optional($identifikasi)->penyebab)) {
                        $penyebabList = array_column($identifikasi->penyebab, 'penyebab_risiko');
                    }

                    return [
                        'is_project' => false,
                        'unit_type_id' => optional(optional($identifikasi)->unit)->unit_type_id ?? null,
                        'risiko' => optional(optional($identifikasi)->peristiwaRisiko)->title ?? optional($identifikasi)->peristiwa_risiko ?? 'N/A',
                        'penyebab' => $penyebabList,
                        'kri' => $kri->kri,
                        'batas_aman' => $kri->batas_aman,
                        'batas_waspada' => $kri->batas_waspada,
                        'batas_bahaya' => $kri->batas_bahaya,
                        'kondisi_saat_ini' => $monitoringForPeriod->nilai_kri_terkini ?? '-',
                        'status' => $monitoringForPeriod->status_kri_terkini ?? 1,
                    ];
                })
                ->filter();

            $kriProjectMonitoringTable = (new \App\Models\KRIProjectMonitoring)->getTable();
            $latestKriProjectMonitorings = \App\Models\KRIProjectMonitoring::from(
                    DB::raw('(SELECT *, ROW_NUMBER() OVER (PARTITION BY kri_project_id ORDER BY id DESC) as rn FROM ' . $kriProjectMonitoringTable . ') as krpm')
                )
                ->where('rn', 1)
                ->whereIn('status_kri_terkini', [2, 3])
                ->get()
                ->keyBy('kri_project_id');

            $kriFromProjects = \App\Models\KRIProject::whereIn('id', $latestKriProjectMonitorings->pluck('kri_project_id'))
                ->with([
                    'risiko.project',
                    'risiko.peristiwaRisiko',
                    'risiko.penyebabRisikoProjects',
                ])
                ->whereHas('risiko', fn($q) => $q->where('is_closed', false)->whereNull('deleted_at'))
                ->get()
                ->map(function ($kri) use ($latestKriProjectMonitorings) {
                    $monitoringForPeriod = $latestKriProjectMonitorings[$kri->id] ?? null;
                    if (!$monitoringForPeriod) return null;

                    $risikoProject = $kri->risiko;

                    return [
                        'is_project' => true,
                        'unit_type_id' => null,
                        'risiko' => optional(optional($risikoProject)->peristiwaRisiko)->title ?? optional($risikoProject)->nama_risiko ?? 'N/A',
                        'penyebab' => ($risikoProject && $risikoProject->penyebabRisikoProjects->isNotEmpty())
                                        ? $risikoProject->penyebabRisikoProjects->pluck('penyebab_risiko')->all()
                                        : [],
                        'kri' => $kri->kri,
                        'batas_aman' => $kri->batas_aman,
                        'batas_waspada' => $kri->batas_waspada,
                        'batas_bahaya' => $kri->batas_bahaya,
                        'kondisi_saat_ini' => $monitoringForPeriod->nilai_kri_terkini ?? '-',
                        'status' => $monitoringForPeriod->status_kri_terkini ?? 1,
                    ];
                })
                ->filter();

            $allKriData = $kriFromUnits->concat($kriFromProjects);

            $kriKorporat = $allKriData
                ->where('is_project', false)
                ->where('unit_type_id', 4)
                ->sortByDesc('status')
                ->values();

            $kriProyek = $allKriData
                ->where('is_project', true)
                ->sortByDesc('status')
                ->values();

            $kriDivisi = $allKriData
                ->where('is_project', false)
                ->whereIn('unit_type_id', [1, 3])
                ->sortByDesc('status')
                ->values();

            $kriAnakPerusahaan = $allKriData
                ->where('is_project', false)
                ->where('unit_type_id', 2)
                ->sortByDesc('status')
                ->values();

            // Efektivitas pie chart mengambil dari risk yang closed khusus corporate unit
            $allRisksCollectionForPie = IdentifikasiRisiko::whereIn('unit_id', $corporateUnitIds)
                ->where('periode_id', optional($periode)->id)
                ->where('is_closed', true)
                ->get();

            list($efektifRisks, $tidakEfektifRisks) = $allRisksCollectionForPie->partition(fn ($risk) => $risk->efektivitas_perlakuan_risiko >= 0);
            $efektivitasPerlakuanData = [
                ['label' => 'Efektif', 'value' => $efektifRisks->count(), 'color' => '#5470C6'],
                ['label' => 'Tidak Efektif', 'value' => $tidakEfektifRisks->count(), 'color' => '#EE6666'],
            ];
        }

        $topLossEventsCorporate = LossEvent::where('unit_id', 1)
            ->whereYear('tanggal_kejadian', $currentYear)
            ->with(['kategoriKejadian'])
            ->orderByRaw('CAST(nilai_kerugian_finansial AS NUMERIC) DESC')
            ->take(10)
            ->get();

        $lossEventsUnit = LossEvent::whereYear('tanggal_kejadian', $currentYear)
          ->with(['kategoriKejadian', 'jenisRisiko', 'unit'])
          ->get()
          ->map(function ($event) {
              $event->numeric_value = (int) preg_replace('/[^0-9]/', '', $event->nilai_kerugian_finansial);
              return $event;
          })
          ->sortByDesc('numeric_value')
          ->take(10)
          ->map(function ($led) {
              return [
                  'id' => $led->id,
                  'tanggal_kejadian' => date('d M Y', strtotime($led->tanggal_kejadian)),
                  'nama_kejadian' => $led->nama_kejadian ?? '-',
                  'deskripsi_kejadian' => $led?->identifikasi_kejadian ?? '-',
                  'kategori_kejadian' => $led->kategoriKejadian->kategori_kejadian ?? '-',
                  'nilai_kerugian' => is_numeric($led->nilai_kerugian_finansial) ? 'Rp ' . number_format($led->nilai_kerugian_finansial, 0, ',', '.') : $led->nilai_kerugian_finansial,
                  'unit_name' => $led->unit->name ?? '-',
              ];
          })
          ->values()
          ->toArray();

        $lossEventsProject = LossEventProject::whereYear('tanggal_kejadian', $currentYear)
          ->with(['kategoriKejadian', 'jenisRisiko', 'project', 'peristiwaRisiko'])
          ->get()
          ->map(function ($event) {
              $event->numeric_value = (int) preg_replace('/[^0-9]/', '', $event->nilai_kerugian_finansial);
              return $event;
          })
          ->sortByDesc('numeric_value')
          ->take(10)
          ->map(function ($led) {
              return [
                  'id' => $led->id,
                  'tanggal_kejadian' => date('d M Y', strtotime($led->tanggal_kejadian)),
                  'nama_kejadian' => $led->nama_kejadian ?? '-',
                  'deskripsi_kejadian' => $led->peristiwa_risiko_id == 0 ? $led->deskripsi_kejadian : ($led->peristiwaRisiko->title ?? '-'),
                  'kategori_kejadian' => $led->kategoriKejadian->kategori_kejadian ?? '-',
                  'nilai_kerugian' => is_numeric($led->nilai_kerugian_finansial) ? 'Rp ' . number_format($led->nilai_kerugian_finansial, 0, ',', '.') : $led->nilai_kerugian_finansial,
                  'project_name' => $led->project->project_name ?? '-',
              ];
          })
          ->values()
          ->toArray();

        return view('executive-summary-corporate', compact(
            'selectedPeriod',
            'currentYear',
            'currentMonth',
            'currentQuarter',
            'summaryData',
            'openRisks',
            'riskMaps',
            'tahunMonitorings',
            'openRisksJs',
            'formattedCurrentRiskMaps',
            'kriKorporat',
            'kriProyek',
            'kriDivisi',
            'kriAnakPerusahaan',
            'efektivitasPerlakuanData',
            'efektifRisks',
            'tidakEfektifRisks',
            'topLossEventsCorporate',
            'lossEventsUnit',
            'lossEventsProject'
        ));
    }

    public function executiveSummaryCorporatePopulation(Request $request)
    {
        $currentYear = now()->year;
        $currentQuarter = now()->quarter;
        $currentMonth = now()->month;

        // =========================================================
        // 1. MANAJEMEN KINERJA BERBASIS RISIKO
        // =========================================================
        $kinerjaData = [
            'lsp_rencana' => 4500000000, // Dummy
            'lsp_realisasi' => 4100000000, // Dummy
            'led_divisi_operasi' => LossEvent::whereHas('unit', fn ($q) => $q->where('unit_type_id', 1))->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)')),
            'led_divisi_fungsi' => LossEvent::whereHas('unit', fn ($q) => $q->whereIn('unit_type_id', [2, 3, 4]))->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)')),
            'led_proyek' => LossEventProject::sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)')),
        ];

        // =========================================================
        // 2. KEY RISK INDICATOR (KRI)
        // =========================================================
        $kriUnitMonitoringTable = (new \App\Models\KRIUnitMonitoring)->getTable();

        $latestKriUnitMonitorings = \App\Models\KRIUnitMonitoring::from(
                DB::raw('(SELECT *, ROW_NUMBER() OVER (PARTITION BY key_risk_indicator_id ORDER BY id DESC) as rn FROM ' . $kriUnitMonitoringTable . ') as krum')
            )
            ->where('rn', 1)
            ->whereIn('status_kri_terkini', [2, 3])
            ->get()
            ->keyBy('key_risk_indicator_id');

        $kriUnits = \App\Models\KRI::whereIn('id', $latestKriUnitMonitorings->pluck('key_risk_indicator_id'))
            ->whereHas('identifikasiRisiko', fn($q) => $q->where('is_closed', false)->whereNull('deleted_at')) // FIX Filter
            ->with('identifikasiRisiko.unit')
            ->get()->map(function ($kri) use ($latestKriUnitMonitorings) {
                return [
                    'nama_sumber' => optional(optional($kri->identifikasiRisiko)->unit)->name ?? 'Unit Tidak Ditemukan',
                    'nama_kri' => $kri->kri,
                    'status' => $latestKriUnitMonitorings[$kri->id]->status_kri_terkini
                ];
            });

        // KRI Proyek
        $kriProjectMonitoringTable = (new \App\Models\KRIProjectMonitoring)->getTable();

        $latestKriProjectMonitorings = \App\Models\KRIProjectMonitoring::from(
                DB::raw('(SELECT *, ROW_NUMBER() OVER (PARTITION BY kri_project_id ORDER BY id DESC) as rn FROM ' . $kriProjectMonitoringTable . ') as krpm')
            )
            ->where('rn', 1)
            ->whereIn('status_kri_terkini', [2, 3])
            ->get()
            ->keyBy('kri_project_id');

        $kriProjects = \App\Models\KRIProject::whereIn('id', $latestKriProjectMonitorings->pluck('kri_project_id'))
            ->whereHas('risiko', fn($q) => $q->where('is_closed', false)->whereNull('deleted_at')) // FIX Filter
            ->with('risiko.project')
            ->get()->map(function ($kri) use ($latestKriProjectMonitorings) {
                return [
                    'nama_sumber' => optional(optional($kri->risiko)->project)->project_name ?? 'Proyek Tidak Ditemukan',
                    'nama_kri' => $kri->kri,
                    'status' => $latestKriProjectMonitorings[$kri->id]->status_kri_terkini
                ];
            });

        $waspadaKRI = $kriUnits->concat($kriProjects)->filter(fn ($kri) => $kri['status'] == 2);
        $bahayaKRI = $kriUnits->concat($kriProjects)->filter(fn ($kri) => $kri['status'] == 3);

        // =========================================================
        // 3. RISIKO HIGH & MODERATE TO HIGH
        // =========================================================
        $divisiList = Unit::whereIn('unit_type_id', [1, 2, 3, 4])->get();
        $riskProfileByDivision = [];

        foreach ($divisiList as $divisi) {
            $unitRisks = IdentifikasiRisiko::where('unit_id', $divisi->id)
              ->whereNull('deleted_at')
              ->where('is_closed', false) // FIX Filter
              ->with('riskAnalysis', 'penyebabRisiko.perlakuanPenyebabRisiko', 'monitoringRisikos')
              ->get();

          $projectRisks = ProjectRisk::whereHas('project', fn($q) => $q->where('cost_center_parent', $divisi->cost_center))
              ->whereNull('deleted_at')
              ->where('is_closed', false) // FIX Filter
              ->with('projectRiskAnalisa', 'penyebabRisikoProjects.perlakuanPenyebabRisiko', 'projectRiskMonitorings')
              ->get();

            $totalRisks = $unitRisks->count() + $projectRisks->count();
            if ($totalRisks === 0) continue;

            $highRisks = $unitRisks->whereIn('riskAnalysis.level_risiko', ['High', 'Moderate to High'])->count()
                      + $projectRisks->whereIn('projectRiskAnalisa.level_risiko', ['High', 'Moderate to High'])->count();

            $biayaPerlakuanRealisasi = $unitRisks->pluck('penyebabRisiko.*.perlakuanPenyebabRisiko.*.perlakuanPenyebabMonitorings')->flatten()->sum('realisasi_biaya_perlakuan_risiko')
                                    + $projectRisks->pluck('penyebabRisikoProjects.*.perlakuanPenyebabRisiko.*.perlakuanPenyebabMonitorings')->flatten()->sum('realisasi_biaya_perlakuan_risiko');

            $dampakResidualRealisasi = $unitRisks->pluck('monitoringRisikos')->flatten()->where('month', '<=', $currentMonth)->last()->nilai_dampak ?? 0 + $projectRisks->pluck('projectRiskMonitorings')->flatten()->where('tahun', $currentYear)->where('quarter', '<=', $currentQuarter)->last()?->nilai_dampak ?? 0;

            $riskProfileByDivision[] = [
                'periode' => Periode::where('status', Periode::STATUS_ACTIVE)->first()->id ?? null,
                'unit_id' => $divisi->id,
                'nama_divisi' => $divisi->name,
                'total_risiko' => $totalRisks,
                'total_risiko_high' => $highRisks,
                'dampak_inheren' => $unitRisks->sum('riskAnalysis.nilai_dampak') + $projectRisks->sum('projectRiskAnalisa.nilai_dampak'),
                'biaya_perlakuan_rencana' => $unitRisks->pluck('penyebabRisiko.*.perlakuanPenyebabRisiko')->flatten()->sum('biaya_perlakuan_risiko')
                                          + $projectRisks->pluck('penyebabRisikoProjects.*.perlakuanPenyebabRisiko')->flatten()->sum('biaya_perlakuan_risiko'),
                'dampak_residual_rencana' => $unitRisks->sum('riskAnalysis.nilai_dampak_residual') + $projectRisks->sum('projectRiskAnalisa.nilai_dampak_residual'),
                'biaya_perlakuan_realisasi' => $biayaPerlakuanRealisasi,
                'dampak_residual_realisasi' => $dampakResidualRealisasi,
            ];
        }

        // =========================================================
        // 4. HEATMAP RISIKO
        // =========================================================
        $riskMaps = RiskMap::all()->keyBy(fn($item) => $item->skala_dampak . '-' . $item->skala_probabilitas);
        $allProjectRisks = ProjectRisk::with('projectRiskAnalisa.skalaProbabilitas', 'projectRiskAnalisa.skalaProbabilitasResidual', 'projectRiskMonitorings.skalaProbabilitas', 'peristiwaRisiko')
            ->whereNull('deleted_at')
            ->where('is_closed', false)
            ->get();
        $allUnitRisks = IdentifikasiRisiko::with('riskAnalysis.skalaProbabilitas', 'riskAnalysis.skalaProbabilitasResidual', 'monitoringRisikos.skalaProbabilitas', 'peristiwaRisiko')
            ->whereNull('deleted_at')
            ->where('is_closed', false)
            ->get();

        $heatmapData = [
            'inherent' => [],
            'rencana' => [],
            'realisasi' => []
        ];

        $processRiskForHeatmap = function ($risk, $type) use (&$heatmapData, $currentYear) {
            $analysis = ($type === 'project') ? $risk->projectRiskAnalisa : $risk->riskAnalysis;
            if (!$analysis) return;

            $riskDetail = [
                'name' => $type === 'project'
                    ? 'Project: ' . optional($risk->project)->project_name . ' - ' . optional($risk->peristiwaRisiko)->title
                    : 'Divisi:' . optional($risk->unit)->name . ' - ' . $risk->peristiwa_risiko,
                'url' => $type === 'project'
                    ? route('projects.risks.show', [$risk->project_id, $risk->id])
                    : route('risk-register-unit.view', [$risk->id]),
            ];

            if ($analysis->skala_dampak && optional($analysis->skalaProbabilitas)->tingkat) {
                $key = $analysis->skala_dampak . '-' . $analysis->skalaProbabilitas->tingkat;
                $heatmapData['inherent'][$key][] = $riskDetail;
            }

            if ($analysis->skala_dampak_residual && optional($analysis->skalaProbabilitasResidual)->tingkat) {
                $key = $analysis->skala_dampak_residual . '-' . $analysis->skalaProbabilitasResidual->tingkat;
                $heatmapData['rencana'][$key][] = $riskDetail;
            }

            $monitorings = ($type === 'project') ? $risk->projectRiskMonitorings : $risk->monitoringRisikos;
            $latestMonitoring = $monitorings->where('tahun', $currentYear)->sortByDesc('quarter')->sortByDesc('id')->first();

            if ($latestMonitoring && $latestMonitoring->skala_dampak && optional($latestMonitoring->skalaProbabilitas)->tingkat) {
                $key = $latestMonitoring->skala_dampak . '-' . $latestMonitoring->skalaProbabilitas->tingkat;
                $heatmapData['realisasi'][$key][] = $riskDetail;
            }
        };

        $allProjectRisks->each(fn($risk) => $processRiskForHeatmap($risk, 'project'));
        $allUnitRisks->each(fn($risk) => $processRiskForHeatmap($risk, 'unit'));

        // =========================================================
        // 5. EFEKTIVITAS RISIKO
        // =========================================================
        $closedRisks = IdentifikasiRisiko::where('is_corporate', true)->where('is_closed', true)->get();
        list($efektifRisks, $tidakEfektifRisks) = $closedRisks->partition(fn ($risk) => $risk->efektivitas_perlakuan_risiko >= 0);
        $efektivitasData = [
            'efektif' => $efektifRisks->count(),
            'tidak_efektif' => $tidakEfektifRisks->count(),
        ];

        // =========================================================
        // 6. TOP 10 LED PROYEK
        // =========================================================
        $topLedProyek = LossEventProject::select('peristiwa_risiko_id', DB::raw('COUNT(*) as total_kejadian'))
            ->groupBy('peristiwa_risiko_id')
            ->orderByDesc('total_kejadian')
            ->limit(10)
            ->with('peristiwaRisiko')
            ->get();

        // =========================================================
        // 8. TOP 10 EKSPOSUR RISIKO RESIDUAL PROYEK
        // =========================================================
        // FIX: Tambahkan filter active risks untuk Top Eksposur Proyek (is_closed = false & tidak terhapus)
        $topEksposurProyek = ProjectRiskMonitoring::from(
                DB::raw('(SELECT *, ROW_NUMBER() OVER (PARTITION BY risiko_id ORDER BY tahun DESC, quarter DESC, id DESC) as rn FROM project_risk_monitorings) as latest_monitorings')
            )
            ->where('rn', 1)
            ->whereIn('risiko_id', function($query) {
                $query->select('id')
                      ->from('project_risks')
                      ->where('is_closed', false)
                      ->whereNull('deleted_at');
            })
            ->orderByDesc('eksposure_risiko')
            ->limit(10)
            ->with('projectRisk.peristiwaRisiko')
            ->get();


        return view('executive-summary-corporate-population', compact(
            'kinerjaData',
            'waspadaKRI',
            'bahayaKRI',
            'riskProfileByDivision',
            'efektivitasData',
            'efektifRisks',
            'tidakEfektifRisks',
            'topLedProyek',
            'topEksposurProyek',
            'riskMaps',
            'heatmapData',
        ));
    }

    public function executiveSummaryKonsolidasi(Request $request)
    {
        $selectedUnitId = $request->input('unit_id');
        $selectedPeriod = $request->input('period', now()->format('Y-m'));
        $selectedPeristiwaId = $request->input('peristiwa_risiko_id');

        $currentYear = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->year;
        $currentMonth = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->month;
        $endOfSelectedPeriod = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->endOfMonth();
        $startOfSelectedPeriod = Carbon::createFromFormat('Y-m', $selectedPeriod)
            ->startOfMonth()
            ->toDateString();

        $user = auth()->user();

        // 1. FILTER DIVISI OPERASI
        $units = Unit::where('unit_type_id', 1)
                     ->whereHas('projects')
                     ->orderBy('name')
                     ->get();
        $listPeristiwa = PeristiwaRisiko::orderBy('title')->get();

        // 2. FILTER PROYEK AKTIF BERDASARKAN DIVISI & PERIODE CUTOFF
        $projectQuery = Project::query();
        if ($selectedUnitId) {
            $selectedUnit = Unit::find($selectedUnitId);
            if ($selectedUnit) {
                $projectQuery->where('cost_center_parent', $selectedUnit->cost_center);
            }
        }

        $hariIni = Carbon::today()->toDateString();

        // $projectQuery->where(function($q) use ($endOfSelectedPeriod) {
        //     $q->whereNull('masa_pelaksanaan_end')
        //       ->orWhereDate('masa_pelaksanaan_end', '>=', $endOfSelectedPeriod);
        // });

        // $projectQuery->where(function($q) use ($startOfSelectedPeriod) {
        //     $q->whereNull('masa_pelaksanaan_end')
        //     ->orWhereDate('masa_pelaksanaan_end', '>=', $startOfSelectedPeriod);
        // });

        // Hanya yang tanggalnya tidak kosong dan >= hari ini yang dianggap Aktif
        $projectQuery->whereNotNull('masa_pelaksanaan_end')
                     ->whereDate('masa_pelaksanaan_end', '>=', $hariIni);

        $projects = $projectQuery->get();
        $activeProjectIds = $projects->pluck('id');
        $totalProyekAktif = $projects->count();
        $activeProjectsPerDivisi = $projects->groupBy('cost_center_parent')->map->count();
        $totalNilaiKontrak = $projects->sum('nk');

        // Inisialisasi semua divisi berdasarkan proyek aktif,
        // termasuk proyek yang belum punya risiko open / belum punya data risiko.
        $divisiExposures = [];

        foreach ($projects->groupBy('cost_center_parent') as $costCenterParent => $projectGroup) {
            $unit = $units->where('cost_center', $costCenterParent)->first();
            $divName = $unit->name ?? 'Divisi Lainnya';

            $divisiExposures[$divName] = [
                'total_exposure' => 0,
                'total_dampak_realisasi' => 0,
                'jumlah_risiko_open' => 0,
                'project_count' => $projectGroup->count(),
                'project_without_open_risk' => $projectGroup->count(),
                'project_ids_with_open_risk' => [],
            ];
        }

        // 3. AMBIL SEMUA RISIKO PROYEK YANG MASIH OPEN
        // Catatan:
        // - status = 6 tetap dipakai karena dashboard ini memakai risiko terpublish.
        // - is_closed = 0 memastikan hanya risiko yang masih open yang dihitung.
        
        // =========================================================================
        // BAGIAN YANG DIUBAH: MENERAPKAN LOGIKA CUTOFF PADA PENGAMBILAN RISIKO
        // =========================================================================
        
        // 1. Dapatkan tanggal akhir bulan dari periode cutoff jam 23:59:59
        $cutoffDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->endOfMonth()->format('Y-m-d 23:59:59');

        // 2. Ambil data risiko dengan kondisi is_closed yang sudah disesuaikan
        $risksQuery = ProjectRisk::with([
            'project',
            'projectRiskAnalisa.skalaDampakObj',
            'projectRiskAnalisa.skalaProbabilitas',
            'projectRiskAnalisa.skalaDampakResidualObj',
            'projectRiskAnalisa.skalaProbabilitasResidual',
            'peristiwaRisiko',
            'projectRiskMonitorings' => function ($query) use ($currentYear, $currentMonth) {
                $query->where(function ($q) {
                        $q->where('status', 100)
                        ->orWhere('is_approved', 1);
                    })
                    ->where(function ($q) use ($currentYear, $currentMonth) {
                        $q->where('tahun', '<', $currentYear)
                        ->orWhere(function ($sq) use ($currentYear, $currentMonth) {
                            $sq->where('tahun', $currentYear)
                                ->where('month', '<=', $currentMonth);
                        });
                    })
                    ->orderByDesc('tahun')
                    ->orderByDesc('month')
                    ->orderByDesc('id');
            },
            'projectRiskMonitorings.skalaProbabilitas',
        ])
        ->whereIn('project_id', $activeProjectIds)
        ->where('status', 6)
        ->whereNull('deleted_at')
        ->where(function ($query) use ($cutoffDate) {
            $query->where(function ($q1) use ($cutoffDate) {
                // Kondisi 1: is_closed = 0 && created_at <= periode cutoff
                $q1->where('is_closed', 0)
                   ->where('created_at', '<=', $cutoffDate);
            })->orWhere(function ($q2) use ($cutoffDate) {
                // Kondisi 2: is_closed = 1 && created_at <= periode cutoff && updated_at > periode cutoff
                $q2->where('is_closed', 1)
                   ->where('created_at', '<=', $cutoffDate)
                   ->where('updated_at', '>', $cutoffDate);
            });
        });

        // Filter Peristiwa Risiko jika ada
        if ($selectedPeristiwaId !== null && $selectedPeristiwaId !== '') {
            $risksQuery->where('peristiwa_risiko_id', $selectedPeristiwaId);
        }

        $risks = $risksQuery->get();

        // Helper agar nilai rupiah/string tetap aman dijumlahkan
        $toNumber = function ($value) {
            if ($value === null || $value === '') {
                return 0;
            }

            if (is_numeric($value)) {
                return (float) $value;
            }

            return (float) preg_replace('/[^0-9\-]/', '', (string) $value);
        };

        // 4. DATA PIE CHART & CARD RINGKASAN
        $totalRisikoSemua = $risks->count();

        $totalDampakInherentSemua = 0;
        $totalEksposurInherentSemua = 0;

        $totalDampakResidualSemua = 0;
        $totalEksposurResidualSemua = 0;

        $totalDampakRealisasiSemua = 0;
        $totalEksposurRealisasiSemua = 0;

        $projectExposures = [];

        foreach ($projects as $proj) {
            $projectExposures[$proj->id] = [
                'name' => $proj->project_name,
                'value' => 0,
            ];
        }

        foreach ($risks as $risk) {
            if (!$risk->projectRiskAnalisa || $risk->projectRiskAnalisa->kategori_dampak !== 'Kuantitatif') {
                continue;
            }

            $ccParent = $risk->project?->cost_center_parent;
            $divName = $units->where('cost_center', $ccParent)->first()->name ?? 'Divisi Lainnya';

            $analisa = $risk->projectRiskAnalisa;

            // Inheren
            $dampakInherent = $toNumber($analisa->nilai_dampak ?? 0);
            $eksposurInherent = $toNumber($analisa->eksposur_risiko ?? 0);

            // Rencana Residual
            $dampakResidual = $toNumber($analisa->nilai_dampak_residual ?? 0);
            $eksposurResidual = $toNumber($analisa->eksposur_risiko_residual ?? 0);

            // Realisasi terbaru dari monitoring terpublish/approved sampai cutoff
            $latestMon = $risk->projectRiskMonitorings->first();

            $dampakRealisasi = $latestMon ? $toNumber($latestMon->nilai_dampak ?? 0) : 0;
            $eksposurRealisasi = $latestMon ? $toNumber($latestMon->eksposure_risiko ?? 0) : 0;

            // Pie chart per divisi memakai eksposur realisasi
            if (!isset($divisiExposures[$divName])) {
                $divisiExposures[$divName] = [
                    'total_exposure' => 0,
                    'total_dampak_realisasi' => 0,
                    'jumlah_risiko_open' => 0,
                    'project_count' => 0,
                    'project_without_open_risk' => 0,
                    'project_ids_with_open_risk' => [],
                ];
            }

            $divisiExposures[$divName]['total_exposure'] += $eksposurRealisasi;
            $divisiExposures[$divName]['total_dampak_realisasi'] += $dampakRealisasi;

            // Risiko ini sudah open karena query utama memakai is_closed = 0
            $divisiExposures[$divName]['jumlah_risiko_open'] += 1;

            // Catat proyek yang punya risiko open
            $divisiExposures[$divName]['project_ids_with_open_risk'][] = $risk->project_id;

            // Bar chart per proyek memakai eksposur realisasi
            $projectId = $risk->project_id;

            if (isset($projectExposures[$projectId])) {
                $projectExposures[$projectId]['value'] += $eksposurRealisasi;
            }

            // Total card
            $totalDampakInherentSemua += $dampakInherent;
            $totalEksposurInherentSemua += $eksposurInherent;

            $totalDampakResidualSemua += $dampakResidual;
            $totalEksposurResidualSemua += $eksposurResidual;

            $totalDampakRealisasiSemua += $dampakRealisasi;
            $totalEksposurRealisasiSemua += $eksposurRealisasi;

            // Simpan monitoring terbaru untuk mapping Top 10 dan peta risiko
            $risk->current_monitoring = $latestMon;
        }

        // Format Pie Chart
        $pieChartData = [];

        foreach ($divisiExposures as $name => $data) {
            $projectWithOpenRisk = collect($data['project_ids_with_open_risk'] ?? [])
                ->unique()
                ->count();

            $projectCount = $data['project_count'] ?? 0;

            $pieChartData[] = [
                'name' => $name,

                // Nilai pie tetap berdasarkan eksposur realisasi.
                // Jika divisi/proyek belum punya risiko open, nilainya 0.
                'value' => $data['total_exposure'],

                'total_dampak_realisasi' => $data['total_dampak_realisasi'],
                'project_count' => $projectCount,
                'project_with_open_risk' => $projectWithOpenRisk,
                'project_without_open_risk' => max($projectCount - $projectWithOpenRisk, 0),
                'risk_count_open' => $data['jumlah_risiko_open'],
            ];
        }

        // Format Bar Chart
        $projectExposures = array_values(array_filter($projectExposures, function ($item) {
            return (float) $item['value'] > 0;
        }));

        usort($projectExposures, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        $top10ProjectsExposure = array_slice($projectExposures, 0, 10);
        $top10ProjectsExposure = array_reverse($top10ProjectsExposure);

        // 5. DATA TOP 10 RISIKO TERTINGGI (Realisasi)
        $mappedRisks = $risks->map(function ($risk) use ($toNumber) {
            $analisa = $risk->projectRiskAnalisa;
            $latestMon = $risk->current_monitoring ?? null;

            if (!$analisa || !$latestMon) {
                return null;
            }

            // Data Inherent
            $risk->inherent_dampak = $toNumber($analisa->nilai_dampak ?? 0);
            $risk->inherent_eksposur = $toNumber($analisa->eksposur_risiko ?? 0);
            $risk->inherent_level = $analisa->level_risiko ?? '-';
            $risk->inherent_skala = $analisa->skala_risiko ?? '-';

            // Data Rencana Residual
            $risk->residual_dampak = $toNumber($analisa->nilai_dampak_residual ?? 0);
            $risk->residual_eksposur = $toNumber($analisa->eksposur_risiko_residual ?? 0);
            $risk->residual_level = $analisa->level_risiko_residual ?? '-';
            $risk->residual_skala = $analisa->skala_risiko_residual ?? '-';

            // Data Realisasi
            $risk->current_dampak = $toNumber($latestMon->nilai_dampak ?? 0);
            $risk->current_eksposur = $toNumber($latestMon->eksposure_risiko ?? 0);
            $risk->current_level = $latestMon->level_risiko ?? '-';
            $risk->current_skala = $latestMon->skala_risiko ?? '-';

            return $risk;
        })
        ->filter()
        ->sortByDesc('current_eksposur')
        ->take(10)
        ->values();

        $top10Risks = $mappedRisks;

        // 6. PETA RISIKO UNTUK TOP 10
        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
                ->get()->keyBy(fn($item) => $item->skala_dampak . '-' . $item->skala_probabilitas);

        $formattedCurrentRiskMaps = ['inherent' => [], 'residual' => [], 'current' => []];

        foreach ($top10Risks as $idx => $risk) {
            $riskNumber = 'R' . ($idx + 1);
            $peristiwa = $risk->peristiwa_risiko_id === 0 ? $risk->rencana_kegiatan : ($risk->peristiwaRisiko->title ?? '-');

            $baseData = [
                'riskNumber' => $riskNumber,
                'peristiwa' => $peristiwa,
                'project_name' => $risk->project->project_name,
                'risk_id' => $risk->id,
                'project_periode_list_id' => $risk->project_periode_list_id
            ];

            if ($risk->projectRiskAnalisa && $risk->projectRiskAnalisa->skala_dampak && $risk->projectRiskAnalisa->skalaProbabilitas) {
                $formattedCurrentRiskMaps['inherent'][] = array_merge($baseData, [
                    'skala_dampak' => $risk->projectRiskAnalisa->skala_dampak,
                    'skala_probabilitas' => $risk->projectRiskAnalisa->skalaProbabilitas->tingkat,
                    'level_risiko' => $risk->projectRiskAnalisa->level_risiko,
                ]);
            }

            if ($risk->projectRiskAnalisa && $risk->projectRiskAnalisa->skala_dampak_residual && $risk->projectRiskAnalisa->skalaProbabilitasResidual) {
                $formattedCurrentRiskMaps['residual'][] = array_merge($baseData, [
                    'skala_dampak' => $risk->projectRiskAnalisa->skala_dampak_residual,
                    'skala_probabilitas' => $risk->projectRiskAnalisa->skalaProbabilitasResidual->tingkat,
                    'level_risiko' => $risk->projectRiskAnalisa->level_risiko_residual,
                ]);
            }

            if ($risk->current_monitoring && $risk->current_monitoring->skala_dampak && $risk->current_monitoring->skalaProbabilitas) {
                $formattedCurrentRiskMaps['current'][] = array_merge($baseData, [
                    'skala_dampak' => $risk->current_monitoring->skala_dampak,
                    'skala_probabilitas' => $risk->current_monitoring->skalaProbabilitas->tingkat,
                    'level_risiko' => $risk->current_monitoring->level_risiko,
                ]);
            } else {
                if ($risk->projectRiskAnalisa && $risk->projectRiskAnalisa->skala_dampak && $risk->projectRiskAnalisa->skalaProbabilitas) {
                    $formattedCurrentRiskMaps['current'][] = array_merge($baseData, [
                        'skala_dampak' => $risk->projectRiskAnalisa->skala_dampak,
                        'skala_probabilitas' => $risk->projectRiskAnalisa->skalaProbabilitas->tingkat,
                        'level_risiko' => $risk->projectRiskAnalisa->level_risiko,
                        'displayMark' => '<sup class="text-danger fw-bold ms-1" style="font-size: 0.8rem; top: -0.3em;" data-bs-toggle="tooltip" title="Belum ada monitoring terpublish">*</sup>'
                    ]);
                }
            }
        }

        // 7. DATA TOP 10 LOSS EVENT (LED) PROYEK AKTIF
        $ledQuery = LossEventProject::whereIn('project_id', $activeProjectIds)
            ->whereYear('tanggal_kejadian', $currentYear)
            ->whereMonth('tanggal_kejadian', '<=', $currentMonth);

        // PERBAIKAN: Filter berdasarkan Peristiwa Risiko jika ada yang dipilih (Termasuk jika nilainya 0)
        if ($selectedPeristiwaId !== null && $selectedPeristiwaId !== '') {
            $ledQuery->where('peristiwa_risiko_id', $selectedPeristiwaId);
        }

        $topLedProyek = $ledQuery->orderByRaw('CAST(nilai_kerugian_finansial AS NUMERIC) DESC')
            ->with(['project', 'peristiwaRisiko', 'kategoriKejadian'])
            ->take(10)
            ->get();

        return view('executive-summary-konsolidasi', compact(
            'units',
            'listPeristiwa',
            'selectedUnitId',
            'selectedPeristiwaId',
            'selectedPeriod',
            'currentYear',
            'pieChartData',
            'top10ProjectsExposure',
            'top10Risks',
            'totalDampakRealisasiSemua',
            'totalDampakInherentSemua',
            'totalDampakResidualSemua',
            'totalEksposurInherentSemua',
            'totalEksposurResidualSemua',
            'totalEksposurRealisasiSemua',
            'totalProyekAktif',
            'totalRisikoSemua',
            'totalNilaiKontrak',
            'projects',
            'formattedCurrentRiskMaps',
            'riskMaps',
            'topLedProyek'
        ));
    }

    private function getStatusPriorityFromKriStatus($statusNumeric)
    {
        switch ((int)$statusNumeric) {
            case 3:
                return 1;
            case 2:
                return 2;
            case 1:
                return 3;
            default:
                return 4;
        }
    }

    private function getStatusPriority($status)
    {
        $statusLower = strtolower($status);
        switch ($statusLower) {
            case 'bahaya':
                return 1;
            case 'waspada':
                return 2;
            case 'aman':
                return 3;
            default:
                return 4;
        }
    }
}
