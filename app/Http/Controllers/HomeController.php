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
use App\Models\ProjectRiskMonitoring;
use App\Models\UnitRiskMonitoring;
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
            // handle jika unit id dan periode id belum ada di tabel loss event
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
                //'rre' => $item->rencanaPerlakuanRisiko->rre,
                'rre' => $item->rencanaPerlakuanRisiko ? $item->rencanaPerlakuanRisiko->rre : '-',
                //'strategi' => $item->risiko->penyebabRisiko->rencana_perlakuan_risiko ?: '-',
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
        /*
        $this->validate($request, [
            'password' => 'same:confirm-password',
        ]);

        $input = $request->all();
        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input,array('password'));
        }

        $data = Auth::user();
        $data->update($input);
        return redirect()->route('profile')
        ->with('success','Password updated successfully');
        */

        // Validasi input
        $this->validate($request, [
            'password' => 'nullable|confirmed', // Validate that password matches confirm-password
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:255',
        ]);

        // Collect the input data
        // Collect the input data except password_confirmation
        $input = $request->except(['password_confirmation']);

        // If the password field is filled, hash the password
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            // If the password field is empty, remove it from the input array
            $input = Arr::except($input, ['password']);
        }

        // Update the user data
        $user = Auth::user();
        $user->update($input);

        // Redirect to the profile page with a success message
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
            // handle jika unit id dan periode id belum ada di tabel loss event
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

            // $getFallbackValue = function($targetQuarter) use ($risk) {
            //     // Cek apakah quarter target memiliki data yang valid
            //     if (isset($risk->current_risk_maps[$targetQuarter]) &&
            //         !is_null($risk->current_risk_maps[$targetQuarter]['skala_dampak']) &&
            //         !is_null($risk->current_risk_maps[$targetQuarter]['skala_probabilitas'])) {
            //         return $risk->current_risk_maps[$targetQuarter];
            //     }

            //     // Jika tidak ada, cari dari quarter sebelumnya secara mundur
            //     for ($q = $targetQuarter - 1; $q >= 1; $q--) {
            //         if (isset($risk->current_risk_maps[$q]) && !is_null($risk->current_risk_maps[$q]['skala_dampak']) && !is_null($risk->current_risk_maps[$q]['skala_probabilitas'])) {
            //             return $risk->current_risk_maps[$q];
            //         }
            //     }

            //     // Jika semua quarter tidak ada, ambil dari inherent
            //     if (isset($risk->current_risk_maps['inherent']) &&
            //         !is_null($risk->current_risk_maps['inherent']['skala_dampak']) &&
            //         !is_null($risk->current_risk_maps['inherent']['skala_probabilitas'])) {
            //         return $risk->current_risk_maps['inherent'];
            //     }

            //     // Jika inherent juga tidak ada, fallback ke riskAnalysis
            //     if ($risk->riskAnalysis) {
            //         return [
            //             'skala_dampak' => $risk->riskAnalysis->skala_dampak,
            //             'skala_probabilitas' => $risk->riskAnalysis->skala_probabilitas->tingkat ?? null,
            //             'skala_risiko' => $risk->riskAnalysis->skala_risiko,
            //             'level_risiko' => $risk->riskAnalysis->level_risiko,
            //         ];
            //     }

            //     return null;
            // };

            // // Generate data untuk setiap quarter
            // for ($quarter = 1; $quarter <= 4; $quarter++) {
            //     $currentValue = $getFallbackValue($quarter);

            //     // Hanya tambahkan jika currentValue tidak null dan valid
            //     if ($currentValue &&
            //         !is_null($currentValue['skala_dampak']) &&
            //         !is_null($currentValue['skala_probabilitas'])) {
            //         $currentValue['quarter'] = $quarter;
            //         $formattedCurrentRiskMaps[$risk->id][] = $currentValue;
            //     }
            // }
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
            // ->where('status_risiko', '>=', 3)
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

        $risikos->where('is_closed', 1)->each(function ($risiko) use (&$risikosEfektif, &$risikosTidakEfektif) {
            $data = [
                'peristiwa_risiko' => $risiko->peristiwa_risiko ?: $risiko->peristiwaRisiko->title,
                'unit_name' => $risiko->unit->name,
            ];
            if ($risiko->efektivitas_perlakuan_risiko > 0) {
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
            // 1. Gabungkan proyek dari unit dan yang di-assign langsung
            $projectsFromUnit = $user->unit ? $user->unit->projects : collect();
            $projectsDirectlyAssigned = $user->projects;
            $allAllowedProjects = $projectsFromUnit->merge($projectsDirectlyAssigned)->unique('id');

            // 2. Simpan ID proyek yang diizinkan
            $allowedProjectIds = $allAllowedProjects->pluck('id');

            // 3. Ambil unit yang relevan dari gabungan proyek tersebut
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
            ->orderBy('skala_risiko', 'desc')
            ->with('projectRisks', 'projectRisks.projectRiskAnalisa', 'projectRisks.projectRiskMonitorings', 'project')
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

        // Loss Event Data Query
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

        $efektifCount = 0;
        $tidakEfektifCount = 0;
        foreach ($projectRisks as $projectRisk) {
            if ($projectRisk->is_closed) {
                if ($projectRisk->efektivitas_perlakuan_risiko > 0) {
                    $efektifCount++;
                } else {
                    $tidakEfektifCount++;
                }
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
                // Cek apakah quarter target memiliki data yang valid
                if (isset($risk->current_risk_maps[$targetQuarter]) &&
                    !is_null($risk->current_risk_maps[$targetQuarter]['skala_dampak']) &&
                    !is_null($risk->current_risk_maps[$targetQuarter]['skala_probabilitas'])) {
                    return $risk->current_risk_maps[$targetQuarter];
                }

                // Jika tidak ada, cari dari quarter sebelumnya secara mundur
                for ($q = $targetQuarter - 1; $q >= 1; $q--) {
                    if (isset($risk->current_risk_maps[$q]) &&
                        !is_null($risk->current_risk_maps[$q]['skala_dampak']) &&
                        !is_null($risk->current_risk_maps[$q]['skala_probabilitas'])) {
                        return $risk->current_risk_maps[$q];
                    }
                }

                // Jika semua quarter tidak ada, ambil dari inherent
                if (isset($risk->current_risk_maps['inherent']) &&
                    !is_null($risk->current_risk_maps['inherent']['skala_dampak']) &&
                    !is_null($risk->current_risk_maps['inherent']['skala_probabilitas'])) {
                    return $risk->current_risk_maps['inherent'];
                }

                // Jika inherent juga tidak ada, fallback ke riskAnalysis
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

            // Generate data untuk setiap quarter
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $currentValue = $getFallbackValue($quarter);

                // Hanya tambahkan jika currentValue tidak null dan valid
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

        // Jika period RMI tidak ada
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
          // 'top_risk' => $risikos->sortByDesc('riskAnalysis.skala_risiko')->take(5)->map(function ($item) use ($riskMaps, $selectedUnit) {
          //   return [
          //     'peristiwa' => $item->peristiwa_risiko ?? '-',
          //     'deskripsi' => $item->deskripsi_peristiwa_risiko ?? '-',
          //     'jenis_risiko' => $item->jenisRisiko->title ?? '-',
          //     'tingkat_risiko' => $item->skala_risiko,
          //     'warna_tingkat_risiko' => strtolower(str_replace(' ', '-', $riskMaps->where('nilai_risiko', $item->skala_risiko)->pluck('level_risiko')->first())),
          //     'sasaran' => $item->target_capaian_kinerja ?? '-',
          //     'kri' => $item->kris->first()?->kri,
          //     'status_kri' => $item->kris->first()?->status_kri_terkini_q4,
          //     'risk_owner' => $selectedUnit->name,
          //   ];
          // })->values(),
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
                $query->where('periode_id', $selectedPeriode->id);
            });
        }

        if ($selectedUnitId) {
            $kriQuery->whereHas('identifikasiRisiko', function($query) use ($selectedUnitId) {
                $query->where('unit_id', $selectedUnitId);
            });
        } elseif (!$isAllUnit) {
            $kriQuery->whereHas('identifikasiRisiko', function($query) use ($user) {
                $query->where('unit_id', $user->unit_id);
            });
        }

        $kriData = $kriQuery->get();

        $processedKriData = $kriData->map(function($kri) use ($selectedQuarter) {
            // Ambil monitoring status terakhir
            // $latestMonitoring = $kri->kriUnitMonitorings->first();
            // $monitoringStatusNumeric = $latestMonitoring ? $latestMonitoring->status_kri_terkini : $currentStatus;

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

                // Kalau pakai monitoring
                // $currentStatusField = "status_kri_terkini_q{$selectedQuarter}";
                // $currentStatusNumeric = $kri->$currentStatusField ?? $kri->getStatusKriAttribute();

                // $latestMonitoring = $kri->kriUnitMonitorings->first();
                // $finalStatusNumeric = $latestMonitoring ? $latestMonitoring->status_kri_terkini : $currentStatusNumeric;
                // $finalStatusNumeric = $finalStatusNumeric ?? 1;

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

                // Kalau pakai monitoring
                // $currentStatusField = "status_kri_terkini_q{$selectedQuarter}";
                // $currentStatusNumeric = $kri->$currentStatusField ?? $kri->getStatusKriAttribute();

                // $latestMonitoring = $kri->kriUnitMonitorings->first();
                // $finalStatusNumeric = $latestMonitoring ? $latestMonitoring->status_kri_terkini : $currentStatusNumeric;
                // $finalStatusNumeric = $finalStatusNumeric ?? 1;

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
        ->whereHas('risiko.project');

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
                $q->where('project_id', $selectedProjectId);
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

        // $costCenterParents = Project::distinct()->pluck('cost_center_parent');
        // $units = Unit::where('unit_type_id', 1)
        //             ->whereIn('cost_center', $costCenterParents)
        //             ->orderBy('name')
        //             ->get();

        $projectsQuery = Project::query();

        if ($user->can('view_all_project')) {
            $costCenterParents = Project::distinct()->pluck('cost_center_parent');
            $units = Unit::where('unit_type_id', 1)
                        ->whereIn('cost_center', $costCenterParents)
                        ->orderBy('name')
                        ->get();
        } else {
            // 1. Ambil proyek dari unit
            $projectsFromUnit = $user->unit ? $user->unit->projects : collect();

            // 2. Ambil proyek yang di-assign langsung ke user
            $projectsDirectlyAssigned = $user->projects;

            // 3. Gabungkan kedua koleksi dan hapus duplikat berdasarkan ID
            $allAllowedProjects = $projectsFromUnit->merge($projectsDirectlyAssigned)->unique('id');

            // 4. Ambil ID proyek yang diizinkan untuk membatasi query utama
            $assignedProjectIds = $allAllowedProjects->pluck('id');
            $projectsQuery->whereIn('id', $assignedProjectIds);

            // 5. Ambil unit yang relevan dari gabungan proyek tersebut untuk dropdown Divisi
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

        // Inisialisasi data summary
        $summaryData = [
            'omset_kontrak' => 0,
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
                $periodForApi = Carbon::createFromFormat('Y-m', $selectedPeriod)->format('Ym');
                $profitCenter = $selectedProject->meta['profit_center'] ?? null;

                if ($profitCenter) {
                    $hasilUsaha = (new ApiWika())->getHasilUsahaProject($periodForApi, $profitCenter);

                    // dd($hasilUsaha);
                    if ($hasilUsaha && $hasilUsaha['status'] && isset($hasilUsaha['data']['hasil_usaha'])) {
                        $apiData = $hasilUsaha['data']['hasil_usaha'];

                        $summaryData['omset_kontrak'] = $apiData['kontrak_review'] ?? 0;
                        $summaryData['omset_penjualan_sd_bulan'] = $apiData['penjualan_ri'] ?? 0;                        
                        $summaryData['progress_sd_bulan'] = ($summaryData['omset_kontrak'] > 0) 
                            ? ($summaryData['omset_penjualan_sd_bulan'] / $summaryData['omset_kontrak']) * 100 
                            : 0;

                        $summaryData['lsp_rencana_sd_bulan'] = $apiData['lsp_ra'] ?? 0;
                        $summaryData['lsp_realisasi_sd_bulan'] = $apiData['lsp_ri'] ?? 0;

                        $summaryData['lsp_rencana_sd_selesai'] = $apiData['lsp_review'] ?? 0;
                        $summaryData['lsp_realisasi_sd_selesai'] = $apiData['lsp_proyeksi'] ?? 0;
                        $summaryData['omset_penjualan_sd_selesai'] = $apiData['penjualan_ra'];
                    }
                }
            } catch (\Exception $e) {
                Log::channel('wikaapi')->error("Request API Hasil Usaha Project gagal", [
                    'project_id' => $selectedProject->id,
                    'error' => $e->getMessage()
                ]);
            }

            // --- Perhitungan dari Database Lokal (LED & Eksposur Risiko) ---
            $ledProyekTotal = LossEventProject::where('project_id', $selectedProjectId)->sum('nilai_kerugian_finansial');
            $eksposurRisikoTotal = ProjectRiskMonitoring::whereHas('projectRisk', function($q) use ($selectedProjectId) {
                $q->where('project_id', $selectedProjectId);
            })->sum('eksposure_risiko');
            
            // --- Perhitungan LED dan Eksposur ---
            $summaryData['led_proyek_total'] = $ledProyekTotal;
            $summaryData['eksposur_risiko_total'] = $eksposurRisikoTotal;

            $summaryData['lsp_realisasi_incl_led'] = $summaryData['lsp_realisasi_sd_bulan'] - $summaryData['led_proyek_total'];
            $summaryData['proyeksi_lsp_incl_eksposur'] = $summaryData['lsp_realisasi_sd_selesai'] - $summaryData['eksposur_risiko_total'];

            $selectedProjectPeriode->load([
                'projectRisks' => fn($query) => $query->orderBy('id', 'asc'),
                'projectRisks.peristiwaRisiko',
                'projectRisks.projectRiskAnalisa.skalaDampakObj',
                'projectRisks.projectRiskAnalisa.skalaDampakResidualObj',
                'projectRisks.projectRiskAnalisa.skalaProbabilitas',
                'projectRisks.projectRiskAnalisa.skalaProbabilitasResidual',
                'projectRisks.projectRiskMonitorings' => fn($query) => $query->orderBy('id', 'desc')->with(['skalaProbabilitas']),
            ]);

            $highImpactRisks = $selectedProjectPeriode->projectRisks->filter(function ($risk) {
                $level = optional($risk->projectRiskAnalisa)->level_risiko;
                return in_array($level, ['High', 'Moderate to High']);
            })->sortByDesc(function ($risk) {
                return optional($risk->projectRiskAnalisa)->skala_risiko ?? -1;
            });
            
            $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
                ->get()->keyBy(fn($item) => $item->skala_dampak . '-' . $item->skala_probabilitas);

            $selectedProjectPeriode->projectRisks->each(fn($pr) => $pr->append('currentRiskMapsMonth'));

            $allYears = $selectedProjectPeriode->projectRisks->pluck('projectRiskMonitorings')->flatten()->pluck('tahun')->unique();
            $allYears->push(Carbon::parse($selectedProject->start_date)->year);
            $allYears->push(now()->year);
            if ($allYears->filter()->isNotEmpty()) {
                $tahunMonitorings = range($allYears->min(), $allYears->max());
            }

            foreach ($selectedProjectPeriode->projectRisks as $projectRisk) {
                $currentValue = $projectRisk->currentRiskMaps['inherent'];
                foreach ($tahunMonitorings as $tahun) {
                    for ($month = 1; $month <= 12; $month++) {
                        if ($nextValue = ($projectRisk->currentRiskMapsMonth[$tahun . '-' . $month] ?? null)) {
                            $currentValue = $nextValue;
                        }
                        $currentValue['nilai_dampak_formatted'] = 'Rp ' . number_format($currentValue['nilai_dampak'] ?? 0, 0, ',', '.');

                        $formattedCurrentRiskMaps[$projectRisk->id][$tahun][] = $currentValue;
                    }
                }
            }
            
            $projectRisksJs = $selectedProjectPeriode->projectRisks->mapWithKeys(function($risk, $index) {
                $risk->nomor_urut = $index + 1;
                return [$risk->id => $risk];
            });
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
            ->whereHas('risiko', fn($q) => $q->where('project_id', $selectedProject->id));

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
                    'risiko' => $kri->risiko->peristiwaRisiko->title ?? '-',
                    'penyebab' => $kri->risiko->penyebabRisikoProjects ?? [],
                    'kri' => $kri->kri,
                    'batas_aman' => $kri->batas_aman,
                    'batas_waspada' => $kri->batas_waspada,
                    'batas_bahaya' => $kri->batas_bahaya,
                    'kondisi_saat_ini' => optional($kri->kriProjectMonitorings->first())->nilai_kri_terkini ?? '-',
                    'status' => $finalStatusNumeric,
                ];
            });
            // dd($processedKriData);

            // Filter hanya untuk status Waspada (2) dan Bahaya (3), lalu urutkan
            $sortedKriData = $processedKriData->filter(function($kri) {
                return in_array($kri['status'], [2, 3]);
            })->sortByDesc('status')->values();
        }

        $efektivitasPerlakuanData = [];
        $efektifRisks = collect();
        $tidakEfektifRisks = collect();
        $closedRisks = [];

        if ($selectedProject) {
          // 1. Ambil semua risiko yang sudah ditutup (is_closed = true)
          $closedRisks = $selectedProjectPeriode->projectRisks->where('is_closed', true);
  
          // 2. Pisahkan menjadi dua grup. Jika $closedRisks kosong, keduanya akan menjadi collection kosong.
          list($efektifRisks, $tidakEfektifRisks) = $closedRisks->partition(function ($risk) {
              return $risk->efektivitas_perlakuan_risiko > 0;
          });
  
          // 3. Siapkan data untuk ECharts Pie Chart. Count akan otomatis menjadi 0 jika collection kosong.
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
            'highImpactRisks', 
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
        $units = Unit::whereIn('unit_type_id', [1, 2, 3])->orderBy('name')->get();
        $selectedUnit = $selectedUnitId ? Unit::find($selectedUnitId) : null;

        $summaryData = [
            'omset_penjualan_sd_bulan'      => 0,
            'lsp_rencana_sd_bulan'          => 0,
            'lsp_realisasi_sd_bulan'        => 0,
            'omset_penjualan_sd_des'        => 0,
            'lsp_rencana_sd_des'            => 0,
            'proyeksi_lsp_sd_des'           => 0,
            'led_proyek_total'              => 0,
            'eksposur_risiko_annual'        => 0,
            'eksposur_risiko_total'         => 0,
            'led_divisi_total'              => 0,
            'hasil_usaha_sd_bulan'          => 0,
            'proyeksi_hasil_usaha_sd_des'   => 0,
        ];

        $isProjectUnit = false;
        $highImpactRisks = collect();
        $riskMaps = collect();
        $tahunMonitorings = [$currentYear];
        $formattedCurrentRiskMaps = [];
        $highImpactRisksJs = collect();
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
            // Cek apakah unit ini menangani proyek
            $projectHandlingCostCenters = Project::distinct()->pluck('cost_center_parent');
            $isProjectUnit = $projectHandlingCostCenters->contains($selectedUnit->cost_center);

            // =========================================================
            // A. PENGAMBILAN DATA UTAMA (RISIKO UNIT)
            // =========================================================
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
                    'monitoringRisikos.skalaProbabilitas',
                    'peristiwaRisiko',
                    'kris.kriUnitMonitorings.unitRiskMonitoring',
                    'penyebabRisiko',
                ])
                ->where('unit_id', $selectedUnit->id)
                ->where('periode_id', $periode->id)
                ->get();
            }

            // =========================================================
            // B. KALKULASI DATA SUMMARY (API & LOKAL)
            // =========================================================
            // NOTE: Di sini Anda bisa mengganti data dummy dengan call API sesungguhnya
            $summaryData['omset_penjualan_sd_bulan']    = $isProjectUnit ? 15000000000 : 1200000000;
            $summaryData['lsp_rencana_sd_bulan']        = $isProjectUnit ? 2500000000 : 0;
            $summaryData['lsp_realisasi_sd_bulan']      = $isProjectUnit ? 2300000000 : 0;
            $summaryData['omset_penjualan_sd_des']      = $isProjectUnit ? 25000000000 : 2200000000;
            $summaryData['lsp_rencana_sd_des']          = $isProjectUnit ? 4500000000 : 0;
            $summaryData['proyeksi_lsp_sd_des']         = $isProjectUnit ? 4100000000 : 0;
            
            $summaryData['led_proyek_total'] = $isProjectUnit ? LossEventProject::whereHas('project', fn($q) => $q->where('cost_center_parent', $selectedUnit->cost_center))->whereYear('tanggal_kejadian', $currentYear)->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)')) : 0;
            $summaryData['led_divisi_total'] = LossEvent::where('unit_id', $selectedUnit->id)->whereYear('tanggal_kejadian', $currentYear)->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)'));

            $baseEksposurQuery = UnitRiskMonitoring::whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $selectedUnitId)->where('periode_id', optional($periode)->id));
            $summaryData['eksposur_risiko_annual'] = (clone $baseEksposurQuery)->sum('eksposure_risiko');
            $summaryData['eksposur_risiko_total'] = (clone $baseEksposurQuery)->where('month', '<=', $currentMonth)->sum('eksposure_risiko');

            $summaryData['hasil_usaha_sd_bulan'] = $summaryData['lsp_realisasi_sd_bulan'] - $summaryData['led_proyek_total'] - $summaryData['led_divisi_total'];
            $summaryData['proyeksi_hasil_usaha_sd_des'] = $summaryData['proyeksi_lsp_sd_des'] - $summaryData['eksposur_risiko_annual'];


            // =========================================================
            // 1. PROFIL RISIKO
            // =========================================================
            $highImpactRisks = $unitRisks->filter(fn($risk) => in_array(optional($risk->riskAnalysis)->level_risiko, ['High', 'Moderate to High']))->sortByDesc(fn($risk) => optional($risk->riskAnalysis)->skala_risiko ?? -1);
            
            $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')->get()->keyBy(fn($item) => $item->skala_dampak . '-' . $item->skala_probabilitas);

            $unitRisks->each(fn($risk) => $risk->append('currentRiskMapsMonth'));

            foreach ($unitRisks as $risk) {
                foreach ($risk->currentRiskMapsMonth as $month => $mapData) {
                    if ($month === 'inherent' || !isset($mapData['month'])) continue;
                    $formattedCurrentRiskMaps[$risk->id][$currentYear][] = $mapData;
                }
            }

            $highImpactRisksJs = $highImpactRisks->values()->mapWithKeys(function($risk, $index) {
                $risk->nomor_urut_js = $index + 1;
                return [$risk->id => $risk];
            });


            // =========================================================
            // 2. KEY RISK INDICATOR (KRI)
            // =========================================================
            $allKRI = $unitRisks->pluck('kris')->flatten();
            $processedKri = $allKRI->map(function($kri) use ($currentMonth) {
                $monitoringForPeriod = $kri->kriUnitMonitorings->where('unitRiskMonitoring.month', $currentMonth)->first();
                $status = optional($monitoringForPeriod)->status_kri_terkini ?? 1;
                return [
                    'risiko' => optional(optional($kri->identifikasiRisiko)->peristiwaRisiko)->title ?? $kri->identifikasiRisiko->peristiwa_risiko,
                    'penyebab' => ($kri->identifikasiRisiko && is_array($kri->identifikasiRisiko->penyebab)) ? array_column($kri->identifikasiRisiko->penyebab, 'penyebab_risiko') : [],
                    'kri' => $kri->kri, 'batas_aman' => $kri->batas_aman, 'batas_waspada' => $kri->batas_waspada, 'batas_bahaya' => $kri->batas_bahaya,
                    'kondisi_saat_ini' => optional($monitoringForPeriod)->realisasi_kri_terkini ?? 'N/A',
                    'status' => $status,
                ];
            });
            $sortedKriData = $processedKri->filter(fn($k) => in_array($k['status'], [2, 3]))->sortByDesc('status')->values();


            // =========================================================
            // 3. EFEKTIVITAS PERLAKUAN RISIKO
            // =========================================================
            $closedRisks = $unitRisks->where('is_closed', true);
            list($efektifRisks, $tidakEfektifRisks) = $closedRisks->partition(fn ($risk) => $risk->efektivitas_perlakuan_risiko > 0);
            $efektivitasPerlakuanData = [
                ['label' => 'Efektif', 'value' => $efektifRisks->count(), 'color' => '#5470C6'],
                ['label' => 'Tidak Efektif', 'value' => $tidakEfektifRisks->count(), 'color' => '#EE6666'],
            ];

            // =========================================================
            // 4. TOP 5 LOSS EVENT PROJECT
            // =========================================================
            if ($isProjectUnit) {
                $projectIds = Project::where('cost_center_parent', $selectedUnit->cost_center)->pluck('id');
                if ($projectIds->isNotEmpty()) {
                    $topLedProjects = LossEventProject::select('project_id', DB::raw('SUM(CAST(nilai_kerugian_finansial AS NUMERIC)) as total_kerugian'))
                        ->whereIn('project_id', $projectIds)->whereYear('tanggal_kejadian', $currentYear)
                        ->groupBy('project_id')->orderByDesc('total_kerugian')->take(5)->with('project')->get();
                }
            }
            
            // =========================================================
            // 5. TOP 10 LOSS EVENT DIVISI
            // =========================================================
            $topLedDivisi = LossEvent::where('unit_id', $selectedUnitId)->whereYear('tanggal_kejadian', $currentYear)
              ->with(['peristiwaRisiko', 'kategoriKejadian'])
              ->orderByRaw('CAST(nilai_kerugian_finansial AS NUMERIC) DESC')
              ->take(10)
              ->get();

            // =========================================================
            // 6. TOP 5 EKSPOSUR RISIKO (ANNUAL)
            // =========================================================
            $topEksposurAnnual = UnitRiskMonitoring::select('identifikasi_risiko_id', DB::raw('MAX(eksposure_risiko) as max_eksposur'))
                ->whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $selectedUnitId)->where('periode_id', optional($periode)->id))
                ->groupBy('identifikasi_risiko_id')->orderByDesc('max_eksposur')->take(5)
                ->with('identifikasiRisiko.peristiwaRisiko')->get();
                
            // =========================================================
            // 7. TOP 5 EKSPOSUR RISIKO (TOTAL)
            // =========================================================
            $topEksposurTotal = UnitRiskMonitoring::select('identifikasi_risiko_id', DB::raw('SUM(eksposure_risiko) as total_eksposur'))
                ->whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $selectedUnitId)->where('periode_id', optional($periode)->id))
                ->where('month', '<=', $currentMonth)
                ->groupBy('identifikasi_risiko_id')->orderByDesc('total_eksposur')->take(5)
                ->with('identifikasiRisiko.peristiwaRisiko')->get();

        }

        return view('executive-summary-unit', compact(
            'units',
            'selectedUnitId',
            'selectedUnit',
            'selectedPeriod',
            'summaryData',
            'isProjectUnit',
            'highImpactRisks', 
            'riskMaps', 
            'tahunMonitorings', 
            'formattedCurrentRiskMaps', 
            'highImpactRisksJs',
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

    public function executiveSummaryCorporate(Request $request)
    {
        $selectedUnitId = $request->input('unit_id');
        $selectedPeriod = $request->input('period', now()->format('Y-m'));

        $units = Unit::whereIn('unit_type_id', [1, 2, 3, 4])->orderBy('name')->get();

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
            'led_divisi_operasi_total'    => 0,
            'led_divisi_fungsi_total'     => 0,
            'hasil_usaha_sd_bulan'        => 0,
            'proyeksi_hasil_usaha_sd_des' => 0,
        ];

        $isProjectUnit = false;
        $selectedUnit = null;

        $top10LedProyek = [];
        $top10EksposurResidualProyek = [];

        if ($selectedUnitId) {
            $selectedUnit = Unit::find($selectedUnitId);
            $year = Carbon::createFromFormat('Y-m', $selectedPeriod)->year;
            $month = Carbon::createFromFormat('Y-m', $selectedPeriod)->month;

            $projectHandlingCostCenters = Project::distinct()->pluck('cost_center_parent');
            if ($selectedUnit && $projectHandlingCostCenters->contains($selectedUnit->cost_center)) {
                $isProjectUnit = true;
            }

            if ($isProjectUnit) {
                $summaryData['omset_penjualan_sd_bulan'] = 15000000000;
                $summaryData['lsp_rencana_sd_bulan']     = 2500000000;
                $summaryData['lsp_realisasi_sd_bulan']   = 2300000000;
                $summaryData['omset_penjualan_sd_des']   = 25000000000;
                $summaryData['lsp_rencana_sd_des']       = 4500000000;
                $summaryData['proyeksi_lsp_sd_des']      = 4100000000;
                $summaryData['led_proyek_total']         = 75000000;
            } else {
                $summaryData['omset_penjualan_sd_bulan'] = 1200000000;
                $summaryData['omset_penjualan_sd_des']   = 2200000000;
            }

            $periode = Periode::where('tahun', $year)->first();
            
            if ($periode) {
                // LED Divisi Operasi
                $summaryData['led_divisi_operasi_total'] = LossEvent::where('unit_id', $selectedUnitId)
                    ->where('periode_id', $periode->id)
                    ->whereHas('unit', function ($q) {
                        $q->where('unit_type_id', 1);
                    })
                    ->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)'));

                // LED Divisi Fungsi
                $summaryData['led_divisi_fungsi_total'] = LossEvent::where('unit_id', $selectedUnitId)
                    ->where('periode_id', $periode->id)
                    ->whereHas('unit', function ($q) {
                        $q->where('unit_type_id', 2);
                    })
                    ->sum(DB::raw('CAST(nilai_kerugian_finansial AS NUMERIC)'));

                $baseMonitoringQuery = UnitRiskMonitoring::whereHas('identifikasiRisiko', function ($query) use ($selectedUnitId, $periode) {
                    $query->where('unit_id', $selectedUnitId)->where('periode_id', $periode->id);
                });
                
                $summaryData['eksposur_risiko_annual'] = (clone $baseMonitoringQuery)->sum('eksposure_risiko');
                $summaryData['eksposur_risiko_total'] = (clone $baseMonitoringQuery)->where('month', '<=', $month)->sum('eksposure_risiko');

                $top10LedProyek = LossEventProject::query()
                  ->join('kategori_kejadians', 'loss_event_projects.kategori_kejadian_id', '=', 'kategori_kejadians.id')
                  ->select(
                      'kategori_kejadians.kategori_kejadian as chart_label', 
                      'loss_event_projects.nilai_kerugian_finansial'
                  )
                  ->whereHas('project', function ($q) use ($selectedUnit) {
                      if ($selectedUnit) {
                          $q->where('cost_center_parent', $selectedUnit->cost_center);
                      }
                  })
                  ->where('loss_event_projects.tahun', $year)
                  ->orderByDesc(DB::raw('CAST(loss_event_projects.nilai_kerugian_finansial AS NUMERIC)'))
                  ->limit(10)
                  ->get();

                $top10EksposurResidualProyekData = ProjectRiskMonitoring::with([
                        // Eager load relasi yang kita butuhkan untuk mengambil nama
                        'projectRisk.peristiwaRisiko'
                    ])
                    ->select(
                        'risiko_id', // Kita butuh foreign key untuk grouping
                        DB::raw('SUM(eksposure_risiko) as total_eksposure')
                    )
                    // Gunakan whereHas untuk memfilter berdasarkan unit melalui relasi
                    ->whereHas('projectRisk.project', function ($q) use ($selectedUnit) {
                        if ($selectedUnit) {
                            $q->where('cost_center_parent', $selectedUnit->cost_center);
                        }
                    })
                    ->where('tahun', $year)
                    ->groupBy('risiko_id') // Group berdasarkan ID risikonya
                    ->orderByDesc('total_eksposure')
                    ->limit(10)
                    ->get();
                    
                    // Karena hasil query di atas tidak memiliki nama, kita format di sini
                $top10EksposurResidualProyek = $top10EksposurResidualProyekData->map(function ($item) {
                    return [
                        'risk_event' => $item->projectRisk->peristiwaRisiko->title ?? 'Nama Risiko Tidak Ditemukan',
                        'total_eksposure' => $item->total_eksposure,
                    ];
                });
            }

            $summaryData['hasil_usaha_sd_bulan'] = $summaryData['lsp_realisasi_sd_bulan'] - $summaryData['led_proyek_total'] - $summaryData['led_divisi_operasi_total'] - $summaryData['led_divisi_fungsi_total'];
            $summaryData['proyeksi_hasil_usaha_sd_des'] = $summaryData['proyeksi_lsp_sd_des'] - $summaryData['eksposur_risiko_annual'];
        }

        return view('executive-summary-corporate', compact(
            'units',
            'selectedUnitId',
            'selectedUnit',
            'selectedPeriod',
            'summaryData',
            'isProjectUnit',
            'top10LedProyek',
            'top10EksposurResidualProyek'
        ));
    }

    private function getStatusPriorityFromKriStatus($statusNumeric)
    {
        switch ((int)$statusNumeric) {
            case 3: // bahaya
                return 1;
            case 2: // waspada
                return 2;
            case 1: // aman
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
