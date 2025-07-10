<?php

namespace App\Http\Controllers;

use App\Models\CapaianTck;
use App\Models\CapaianTkmru;
use App\Models\IdentifikasiRisiko;
use App\Models\LossEvent;
use App\Models\Periode;
use App\Models\PeristiwaRisiko;
use App\Models\PrioritasRisiko;
use App\Models\Project;
use App\Models\ProjectPeriodeList;
use App\Models\RiskMap;
use App\Models\Unit;
use Illuminate\Http\Request;
use Auth;
use Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Arr;

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
            ->get();

        $currentRiskMaps          = $risikos->pluck('currentRiskMaps');
        $formattedCurrentRiskMaps = [];
        foreach ($risikos as $idx => $risk) {
            $currentValue = $risk->current_risk_maps['inherent'];
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                if ($nextValue = ($risk->current_risk_maps[$quarter] ?? null)) {
                    $currentValue = $nextValue;
                }

                $currentValue['quarter'] = $quarter;

                $formattedCurrentRiskMaps[$risk->id][] = $currentValue;
            }
        }

        $riskMaps = RiskMap::select('skala_dampak', 'skala_probabilitas', 'nilai_risiko', 'level_risiko')
            ->get()
            ->keyBy(function ($item) {
                return $item->skala_dampak . '-' . $item->skala_probabilitas;
            });

        $lossEvents = LossEvent::with(['kategoriRisiko', 'jenisRisiko'])
          ->where('unit_id', $selectedUnitId)
          ->whereYear('tanggal_kejadian', $tahun)
          ->get();

        $dashboardData = [
          'rpr_c' => null,
          'rpr_date' => null,
          'jkk_c' => null,
          'jkk_date' => null,
          'tkmru_c' => null,
          'tkmru_date' => null,
          'tkmru' => null,
          'tkmru_notes' => null,
          'top_risk' => $risikos->sortByDesc('riskAnalysis.skala_risiko')->take(5)->map(function ($item) use ($riskMaps) {
            return [
              'peristiwa' => $item->peristiwaRisiko->title ?? '-',
              'deskripsi' => $item->deskripsi_peristiwa_risiko ?? '-',
              'jenis_risiko' => $item->jenisRisiko->title ?? '-',
              'tingkat_risiko' => $item->skala_risiko,
              'warna_tingkat_risiko' => strtolower(str_replace(' ', '-', $riskMaps->where('nilai_risiko', $item->skala_risiko)->pluck('level_risiko')->first())),
              'tck_terpengaruh' => '-',
              'kri' => $item->kris->first()?->kri,
              'status_kri' => $item->kris->first()?->status_kri_terkini_q4,
              'risk_owner' => '-'
            ];
          })->values(),
          'led' => $lossEvents->map(function ($led) {
            return [
              'tanggal_kejadian' => date('d/m/Y', strtotime($led->tanggal_kejadian)),
              'kategori_risiko' => $led->kategoriRisiko->title ?? '-',
              'jenis_risiko' => $led->jenisRisiko->title ?? '-',
              'nilai_kerugian_finansial' => is_numeric($led->nilai_kerugian_finansial) ? number_format($led->nilai_kerugian_finansial, 0, ',', '.') : $led->nilai_kerugian_finansial,
              'nilai_kerugian_non_finansial' => is_numeric($led->nilai_kerugian_non_finansial) ? number_format($led->nilai_kerugian_non_finansial, 0, ',', '.') : $led->nilai_kerugian_non_finansial,
              'peristiwa_kerugian' => $led->peristiwa_kerugian,
              'unit_penanggung_jawab' => $led->unit_penanggung_jawab,
            ];
          }),
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
        $projectPeriodes = ProjectPeriodeList::whereHas('project', function($query) {
                $query->where('type', Project::TYPE_OPERASIONAL);
            })
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
            'top_risk' => $projectRisks->sortByDesc('projectRiskAnalisa.nilai_risiko')->take(10)->map(function($projectRisk) {
                return [
                    'kode' => 'R' . $projectRisk->id,
                    'nama_proyek' => $projectRisk->project?->project_name,
                    'peristiwa_risiko' => $projectRisk->peristiwaRisiko?->title,
                    'deskripsi_peristiwa_risiko' => $projectRisk->deskripsi_peristiwa_risiko,
                    'nilai_dampak' => $projectRisk->projectRiskAnalisa?->nilai_dampak,
                    'skala_dampak' => $projectRisk->projectRiskAnalisa?->skala_dampak,
                    'jenis_risiko' => $projectRisk->jenis_risiko,
                    'nilai_risiko' => $projectRisk->projectRiskAnalisa?->nilai_risiko,
                    'level_risiko' => $projectRisk->projectRiskAnalisa?->level_risiko,
                ];
            })->values()->toArray(),
        ];

        $riskMaps = RiskMap::get()->keyBy(function($item) {
            return $item->skala_dampak . '-' . $item->skala_probabilitas;
        });

        return view('dashboard-proyek', compact('dashboardData', 'tahunMonitorings', 'riskMaps'));
    }
}
