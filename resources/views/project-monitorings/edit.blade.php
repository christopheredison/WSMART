
@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Input Monitor Risiko <small class="d-block mt-2">{{ $project->project_name }} - {{ $projectRisk->peristiwa_risiko_id ? $project?->peristiwaRisiko?->title :  $projectRisk->rencana_kegiatan }}</small></h3>
        </div>
    </div>

    <form class="row g-3" method="POST" action="{{ route('projects.risks.store', request()->route('project')) }}" id="main-form">
        <input type="hidden" name="draft_key" value="{{ request()->draft_key }}">
        @csrf

        <div class="col-md-8">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="h3 mb-0">Deskripsi Peristiwa Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    {{ $projectRisk->deskripsi_peristiwa_risiko ?: '-' }}
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card bg-primary shadow text-white text-center border-0">
                <div class="card-body">
                    <i class='bx bx-alarm-exclamation fs-1 mb-3 text-white'></i>
                    <h4>Periode Monitoring</h4>
                    <h3 class="mb-0">Quarter {{ $quarter }} - Tahun {{ $tahun }}<br/>{{ __('basic.month.' . $month) }}</h3>
                    <input type="hidden" name="periode_monitoring" value="{{ $quarter }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm">Nilai Risiko Residual Realisasi</h4>
                </div>
            </div>

            {{-- PHP Helpers untuk Previous Data --}}
            @php
                $prevExists = $previousMonitoring !== null;
                $monthNamesArray = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

                $prevPeriode = $prevExists ? ($monthNamesArray[(int)$previousMonitoring->month] . ' ' . $previousMonitoring->tahun) : '-';
                $prevDate = $prevExists ? \Carbon\Carbon::parse($previousMonitoring->created_at)->translatedFormat('d F Y') : '-';

                $prevImpactVal   = $prevExists ? $previousMonitoring->nilai_dampak : 0;
                $prevImpactScale = $prevExists ? $previousMonitoring->skala_dampak : '-';
                $prevImpactScaleDesc = $prevExists ? ($previousMonitoring->skalaDampakObj?->deskripsi ?? '-') : '-';

                $prevProbVal     = $prevExists ? $previousMonitoring->nilai_probabilitas : 0;
                $prevProbScale   = $prevExists ? ($previousMonitoring->skalaProbabilitas?->tingkat ?? '-') : '-';
                $prevProbScaleDesc= $prevExists ? ($previousMonitoring->skalaProbabilitas?->skala ?? '-') : '-';

                $prevExposure    = $prevExists ? $previousMonitoring->eksposure_risiko : 0;
                $prevRiskLevel   = $prevExists ? $previousMonitoring->level_risiko : '-';
                $prevRiskScale   = $prevExists ? $previousMonitoring->skala_risiko : '-';
            @endphp

            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #696cff !important; background: linear-gradient(135deg, #f5f5ff 0%, #eef1ff 100%);">
                    <div class="card-body py-3 px-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: #696cff;">
                                <i class='bx bx-history text-white fs-5'></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold" style="color: #696cff;">Realisasi Residual Sebelumnya</h5>
                                <medium class="text-gray d-block">
                                    Periode: <strong>{{ $prevPeriode }}</strong> &bull; Diinput: <strong>{{ $prevDate }}</strong>
                                </medium>
                            </div>
                        </div>

                        @if($prevExists)
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <medium class="text-gray d-block">Nilai Dampak</medium>
                                <span class="fw-semibold text-dark">Rp {{ number_format($prevImpactVal, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <medium class="text-gray d-block">Skala Dampak</medium>
                                <span class="fw-semibold text-dark">{{ $prevImpactScale }} - {{ $prevImpactScaleDesc }}</span>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <medium class="text-gray d-block">Nilai Probabilitas</medium>
                                <span class="fw-semibold text-dark">{{ $prevProbVal }}%</span>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <medium class="text-gray d-block">Skala Probabilitas</medium>
                                <span class="fw-semibold text-dark">{{ $prevProbScale }} - {{ $prevProbScaleDesc }}</span>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <medium class="text-gray d-block">Eksposur Risiko</medium>
                                <span class="fw-semibold text-dark">Rp {{ number_format($prevExposure, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <medium class="text-gray d-block">Level Risiko</medium>
                                <span class="fw-semibold text-dark">
                                    {{ $prevRiskScale }} - {{ $prevRiskLevel }}
                                </span>
                            </div>
                        </div>
                        @else
                        <div class="text-center py-2">
                            <i class='bx bx-info-circle fs-4 text-gray mb-1'></i>
                            <p class="text-gray mb-0">Belum ada data realisasi residual dari bulan sebelumnya.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="card btn-reveal-trigger">
                        <div class="card-header d-flex align-items-center gap-2 pb-0 border-0">
                            <div class="lead__icon bg-danger rounded-pill">
                                <i class='bx bx-cube-alt text-white'></i>
                            </div>
                            <h5>Inherent</h5>
                        </div>
                        <div class="card-body d-flex flex-column gap-2">
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="nilai_dampak_inherent"
                                value="Rp {{ number_format($projectRiskAnalisa->nilai_dampak, strpos($projectRiskAnalisa->nilai_dampak, '.') !== false ? 2 : 0, ',', '.') }}">
                                <label class="form-label" for="">Nilai Dampak Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_dampak_inherent"
                                value="{{ $projectRiskAnalisa->skalaDampakObj ? $projectRiskAnalisa->skalaDampakObj?->tingkat . ' - ' . $projectRiskAnalisa->skalaDampakObj?->deskripsi : '-' }}">
                                <label for="">Skala Dampak Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text"
                                value="{{ $projectRiskAnalisa->skalaParameterObj ? $projectRiskAnalisa->skalaParameterObj->type_parameter : '-' }}">
                                <label>Parameter Probabilitas Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="nilai_probabilitas_inherent"
                                value="{{ $projectRiskAnalisa->nilai_probabilitas }}">
                                <label for="">Nilai Probabilitas Inherent (%)</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_probabilitas_inherent"
                                value="{{ $projectRiskAnalisa->skalaProbabilitas?->tingkat." - " .$projectRiskAnalisa->skalaProbabilitas?->skala }}">
                                <label for="">Skala Probabilitas Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_risiko_inherent"
                                value="{{ $projectRiskAnalisa->skala_risiko }}">
                                <label for="">Skala Risiko Inherent</label>
                            </div>
                            {{-- <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="eksposur_risiko_inherent"
                                value="{{ $projectRiskAnalisa->eksposur_risiko ? 'Rp ' . number_format($projectRiskAnalisa->eksposur_risiko, 0, ',', '.') : '-' }}">
                                <label for="">Eksposur Risiko Inherent</label>
                            </div> --}}
                            <div class="form-group pt-3">
                                <p>Level Risiko Inherent: <span class="ff-heading fw-medium">{{ $projectRiskAnalisa->level_risiko }}</strong>
                                </p>
                                <label
                                class=" radio-label low-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'low' ? 'active' : '' }}"
                                for="low"></label>
                                <label
                                class="radio-label low-medium-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'low to moderate' ? 'active' : '' }}"
                                for="low-medium"></label>
                                <label
                                class="radio-label medium-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'moderate' ? 'active' : '' }}"
                                for="medium"></label>
                                <label
                                class="radio-label medium-high-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'moderate to high' ? 'active' : '' }}"
                                for="medium-high"></label>
                                <label
                                class="radio-label high-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'high' ? 'active' : '' }}"
                                for="high"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card btn-reveal-trigger">
                        <div class="card-header d-flex align-items-center gap-2 pb-0 border-0">
                            <div class="lead__icon bg-warning rounded-pill">
                                <i class='bx bx-cube text-white'></i>
                            </div>
                            <h5>Residual Rencana</h5>
                        </div>
                        <div class="card-body d-flex flex-column gap-2">
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="nilai_dampak_inherent"
                                value="Rp {{ number_format($projectRiskAnalisa->nilai_dampak_residual, strpos($projectRiskAnalisa->nilai_dampak_residual, '.') !== false ? 2 : 0, ',', '.') }}">
                                <label for="">Target Nilai Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_skala_dampak"
                                name="target_skala_dampak"
                                value="{{ $projectRiskAnalisa->skalaDampakResidualObj ? $projectRiskAnalisa->skalaDampakResidualObj->tingkat . ' - ' . $projectRiskAnalisa->skalaDampakResidualObj->deskripsi : '-' }}">
                                <label for="">Target Skala Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text"
                                value="{{ $projectRiskAnalisa->skalaParameterResidualObj ? $projectRiskAnalisa->skalaParameterResidualObj->type_parameter : '-' }}">
                                <label>Target Parameter Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_nilai_probabilitas"
                                name="target_nilai_probabilitas" value="{{ $projectRiskAnalisa->nilai_probabilitas_residual }}">
                                <label for="">Target Nilai Probabilitas (%)</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_probabilitas_residual"
                                value="{{ $projectRiskAnalisa->skalaProbabilitasResidual?->tingkat." - " .$projectRiskAnalisa->skalaProbabilitasResidual?->skala }}">
                                <label for="">Target Skala Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_skala_risiko"
                                name="target_skala_risiko" value="{{ $projectRiskAnalisa->skala_risiko_residual }}">
                                <label for="">Target Skala Risiko</label>
                            </div>
                            {{-- <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_eksposur_risiko"
                                name="target_eksposur_risiko" value="{{ $projectRiskAnalisa->eksposur_risiko_residual ? 'Rp ' . number_format($projectRiskAnalisa->eksposur_risiko_residual, 0, ',', '.') : '-' }}">
                                <label for="">Target Eksposur Risiko</label>
                            </div> --}}
                            <div class="form-group pt-3">
                                <p>Target Level Risiko: <span class="ff-heading fw-medium">{{ $projectRiskAnalisa->level_risiko_residual }}</span>
                                </p>
                                <label
                                class=" radio-label low-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'low' ? 'active' : '' }}"
                                for="low"></label>
                                <label
                                class="radio-label low-medium-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'low to moderate' ? 'active' : '' }}"
                                for="low-medium"></label>
                                <label
                                class="radio-label medium-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'moderate' ? 'active' : '' }}"
                                for="medium"></label>
                                <label
                                class="radio-label medium-high-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'moderate to high' ? 'active' : '' }}"
                                for="medium-high"></label>
                                <label
                                class="radio-label high-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'high' ? 'active' : '' }}"
                                for="high"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="section-realisasi">
                    <div class="card btn-reveal-trigger">
                        <div class="card-header d-flex align-items-center gap-2 pb-0 border-0">
                            <div class="lead__icon bg-info rounded-pill">
                                <i class='bx bxs-cube text-white'></i>
                            </div>
                            <h5>Residual Realisasi</h5>
                        </div>
                        <div class="card-body d-flex flex-column gap-2">
                            <div class="form-floating">
                                <input
                                  class="form-control update-trigger inputmask-rupiah"
                                  type="text"
                                  id="realisasi_nilai_dampak"
                                  name="realisasi_nilai_dampak"
                                  value="{{ $projectRiskAnalisa->kategori_dampak == 'Kualitatif' ? '0' : ($riskMonitoring?->nilai_dampak ?: '0') }}"
                                  {{ $projectRiskAnalisa->kategori_dampak == 'Kualitatif' ? 'disabled' : '' }}
                                  {{-- {{ $projectRiskAnalisa->kategori_dampak == 'Kuantitatif' ? 'max=' . $projectRiskAnalisa->nilai_dampak : '' }} --}}
                                  min="0"
                                  {{-- oninput="if(this.value > {{ $projectRiskAnalisa->nilai_dampak }} && '{{ $projectRiskAnalisa->kategori_dampak }}' === 'Kuantitatif') this.value = {{ $projectRiskAnalisa->nilai_dampak }};" --}}
                                  required
                                >
                                <label for="">Realisasi Nilai Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input type="hidden" name="realisasi_skala_dampak" id="realisasi_skala_dampak_hidden">
                                <select
                                  class="form-select js-select-hide-search update-trigger"
                                  name="realisasi_skala_dampak"
                                  id="realisasi_skala_dampak"
                                >
                                  <option selected disabled>Skala Dampak</option>
                                  @foreach($skalaDampaks as $tingkat => $deskripsi)
                                  <option value="{{ $tingkat }}"
                                      {{ (old('realisasi_skala_dampak') == $tingkat) ? 'selected' : (
                                        ($riskMonitoring?->skala_dampak == $tingkat) ? 'selected' : ''
                                        )
                                      }}>
                                      {{ $tingkat }} - {{ $deskripsi }}
                                      {{ $tingkat > $projectRiskAnalisa->skalaDampakObj?->tingkat ? '(Melebihi Skala Inherent)' : '' }}
                                  </option>
                                  @endforeach
                                </select>
                                <label for="">Realisasi Skala Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text"
                                value="{{ $selectedParameterType ?? '-' }}">
                                <label>Realisasi Parameter Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input
                                  class="form-control update-trigger"
                                  type="number"
                                  id="realisasi_nilai_probabilitas"
                                  name="realisasi_nilai_probabilitas"
                                  value="{{ $riskMonitoring?->nilai_probabilitas }}"
                                  {{-- data-max="{{ $projectRiskAnalisa->nilai_probabilitas }}"  --}}
                                  {{-- max="{{ $projectRiskAnalisa->nilai_probabilitas }}" --}}
                                  {{-- min="0" --}}
                                  {{-- oninput="if(this.value > {{ $projectRiskAnalisa->nilai_probabilitas }}) this.value = {{ $projectRiskAnalisa->nilai_probabilitas }};" --}}
                                  data-max="100"
                                  max="100"
                                  min="0"
                                  oninput="if(this.value > 100) this.value = 100;"
                                >
                                <label for="">Realisasi Nilai Probabilitas (%)</label>
                            </div>
                            <div class="form-floating">
                                <select class="form-select update-trigger" name="realisasi_skala_probabilitas" id="realisasi_skala_probabilitas" required>
                                    <option value="" selected disabled>Pilih Skala Probabilitas</option>
                                    @if($selectedParameterType && isset($groupedSkalaParameters[$selectedParameterType]))
                                        @foreach($groupedSkalaParameters[$selectedParameterType] as $param)
                                            <option value="{{ $param->tingkat }}"
                                                {{-- data-min="{{ $param->min }}"
                                                data-max="{{ $param->max }}" --}}
                                                {{ (old('realisasi_skala_probabilitas') == $param->tingkat) ? 'selected' : (
                                                    ($riskMonitoring?->skala_probabilitas_id == $param->tingkat) ? 'selected' : ''
                                                  )
                                                }}>
                                                {{ $param->tingkat }} - {{ $param->skala }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <label for="">Realisasi Skala Probabilitas</label>
                                <input type="hidden" name="realisasi_skala_probabilitas_hidden" id="realisasi_skala_probabilitas_hidden">
                            </div>
                            <div class="form-floating">
                                <input class="form-control" type="text" name="realisasi_skala_risiko" id="realisasi_skala_risiko" placeholder="" readonly>
                                <input type="hidden" name="realisasi_skala_risiko_hidden" id="realisasi_skala_risiko_hidden">
                                <label for="">Realisasi Skala Risiko</label>
                            </div>
                            {{-- <div class="form-floating">
                                <input class="form-control" type="text" name="realisasi_eksposure_risiko" id="realisasi_eksposure_risiko" placeholder=""
                                value="0" readonly>
                                <label for="">Realisasi Eksposur Risiko</label>
                            </div> --}}
                            <div class="form-floating">
                                <input class="form-control" name="realisasi_level_risiko" id="realisasi_level_risiko" type="text"
                                placeholder="" readonly />
                                <input type="hidden" name="realisasi_level_risiko_hidden" id="realisasi_level_risiko_hidden">
                                <label for="">Realisasi Level Risiko</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- @php
            use Carbon\Carbon;
            // Asumsi $month dan $tahun dikirim dari controller
            $dateCurrent = Carbon::create($tahun, $month, 1);
            $dateM1 = $dateCurrent->copy()->subMonth();
            $dateM2 = $dateCurrent->copy()->subMonths(2);
        @endphp
        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm">Informasi Taksonomi & Paramter</h4>
                </div>
            </div>
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="fw-bold">Taksonomi Danantara</label>
                                <p class="p-2 bg-light rounded">{{ $projectRisk->taksonomiRisiko->nama ?? '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Daftar Parameter Risiko</label>
                                <ol class="list-input">
                                    @foreach($projectRisk->parameterRisikoProjects as $param)
                                        <li class="list-group-item bg-light border-0 mb-1">
                                            <strong>{{ $param->nama }}</strong> (Formula: {{ $param->formula }}, Satuan: {{ $param->satuan }})
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm">Monitoring Nilai Aktual</h4>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-gray fw-bold mb-4 small text-uppercase text-center">Nilai Threshold</h5>
                            <div class="row text-center g-3 mb-4">
                                <div class="col-md-4 border-end">
                                    <div class="text-success small fw-bold mb-1">Risk Limit (Aman)</div>
                                    <div class="fs-4 fw-bolder text-success">Rp {{ number_format($projectRisk->threshold_risk_limit, 0, ',', '.') }}</div>
                                </div>
                                <div class="col-md-4 border-end">
                                    <div class="text-warning small fw-bold mb-1">Risk Appetite (Siaga)</div>
                                    <div class="fs-4 fw-bolder text-warning">Rp {{ number_format($projectRisk->threshold_risk_appetite, 0, ',', '.') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-danger small fw-bold mb-1">Risk Tolerance (Bahaya)</div>
                                    <div class="fs-4 fw-bolder text-danger">Rp {{ number_format($projectRisk->threshold_risk_tolerance, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Aktual ({{ $dateCurrent->translatedFormat('F Y') }})</label>
                                    <input type="text" class="form-control inputmask-rupiah aktual-trigger border-primary shadow-sm"
                                          name="aktual_current" id="aktual_current"
                                          value="{{ $riskMonitoring->aktual_current ?? 0 }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-gray small">Bulan -1 ({{ $dateM1->translatedFormat('F Y') }})</label>
                                    <input type="text" class="form-control inputmask-rupiah border-light bg-light"
                                          name="aktual_month_1"
                                          value="{{ $riskMonitoring->aktual_month_1 ?? ($monitoringM1->aktual_current ?? 0) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-gray small">Bulan -2 ({{ $dateM2->translatedFormat('F Y') }})</label>
                                    <input type="text" class="form-control inputmask-rupiah border-light bg-light"
                                          name="aktual_month_2"
                                          value="{{ $riskMonitoring->aktual_month_2 ?? ($monitoringM2->aktual_current ?? 0) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Status Monitoring</label>
                                    <div id="status-badge-container" class="p-2 rounded text-center fw-bold fs-6 border" style="background: #fdfdfd; min-height: 40px;">
                                        MENUNGGU INPUT...
                                    </div>
                                    <input type="hidden" name="aktual_status" id="aktual_status_hidden">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="pengendalian-section" class="col-12 d-none">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm text-danger">Rencana Pengendalian Risiko</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-danger">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="table-pengendalian">
                                  <thead class="bg-light">
                                      <tr>
                                          <th width="5%" class="text-center py-3">No</th>
                                          <th width="25%" class="py-3">Parameter Risiko</th>
                                          <th class="py-3">Rencana Pengendalian</th>
                                          <th class="py-3">Realisasi Pengendalian</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      @foreach($projectRisk->parameterRisikoProjects as $param)
                                      @php
                                          // 1. Cek apakah ada data pengendalian untuk bulan yang sedang diedit saat ini
                                          $currentP = $projectRisk->projectRiskMonitoring ?
                                                      $projectRisk->projectRiskMonitoring->pengendalians->where('parameter_id', $param->id)->first() : null;

                                          // 2. Jika tidak ada, gunakan data historis terakhir yang ditemukan di controller
                                          $rencanaVal = $currentP->rencana_pengendalian ?? ($historicalPengendalians->get($param->id)->rencana_pengendalian ?? '');
                                          $realisasiVal = $currentP->realisasi_pengendalian ?? ($historicalPengendalians->get($param->id)->realisasi_pengendalian ?? '');
                                      @endphp
                                      <tr>
                                          <td class="text-center">{{ $loop->iteration }}</td>
                                          <td class="bg-light">
                                              <input type="hidden" name="pengendalian_parameter_id[]" value="{{ $param->id }}">
                                              <strong>{{ $param->nama }}</strong>
                                          </td>
                                          <td>
                                              <textarea class="form-control shadow-none" name="rencana_pengendalian[]" rows="2">{{ $rencanaVal }}</textarea>
                                          </td>
                                          <td>
                                              <textarea class="form-control shadow-none" name="realisasi_pengendalian[]" rows="2">{{ $realisasiVal }}</textarea>
                                          </td>
                                      </tr>
                                      @endforeach
                                  </tbody>
                              </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm">Realisasi Perlakuan Risiko</h4>
                </div>
            </div>

            {{-- Tambahkan card untuk legend/keterangan --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-gray">Keterangan :</span>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-edit-alt text-primary"></i>
                                <span>Update Realisasi</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-chart text-primary"></i>
                                <span>Update KRI</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-2">Perlakuan terhadap Dampak Risiko</h5>
                        <table class="table table-bordered" id="table-dampak-risiko">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Dampak Risiko</th>
                                    <th>Rencana Perlakuan</th>
                                    <th>Biaya Perlakuan</th>
                                    <th>Progress (%)</th>
                                    <th>Realisasi Biaya</th>
                                    <th>Waktu Realisasi</th>
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalBiayaDampak = 0; @endphp
                                @foreach ($projectRisk->dampakRisikoProjects as $dampak)
                                    @php
                                        $perlakuans = $projectRisk->perlakuanDampakRisikos->where('dampak_risiko_id', $dampak->id);
                                        $rowSpan = max($perlakuans->count(), 1);
                                    @endphp

                                    @foreach ($perlakuans->isEmpty() ? [null] : $perlakuans as $perlakuan)
                                        @if ($loop->index == 0)
                                            <tr data-id="{{ $perlakuan?->id }}">
                                                <td rowspan="{{ $rowSpan }}">{{ $loop->parent->iteration }}</td>
                                                <td rowspan="{{ $rowSpan }}">{{ $dampak->dampak_risiko }}</td>
                                        @else
                                            <tr data-id="{{ $perlakuan->id }}">
                                        @endif

                                        @if($perlakuan)
                                            @php $totalBiayaDampak += $perlakuan->biaya_perlakuan_risiko ?? 0; @endphp
                                            <td>{{ $perlakuan->rencana_perlakuan_risiko ?: '-' }}</td>
                                            <td>
                                                <span class="inputmask-fixed">
                                                    {{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : 'Rp 0' }}
                                                </span>
                                            </td>
                                            <td class="display-progress inputmask-fixed text-center">
                                                {{ $perlakuan->lastMonitoring?->progress_rencana_perlakuan_risiko ?? '-' }}
                                            </td>
                                            <td class="display-biaya inputmask-fixed">
                                                {{ $perlakuan->lastMonitoring?->realisasi_biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->lastMonitoring->realisasi_biaya_perlakuan_risiko, 0, ',', '.') : 'Rp 0' }}
                                            </td>
                                            <td class="display-timeline text-center">
                                                {{ $perlakuan->lastMonitoring?->timeline_perlakuan_risiko_start?->format('d/m/Y') ?: '-' }}
                                            </td>
                                            <td style="white-space:nowrap" class="column-action-impact">
                                                <div class="d-none dom-saved-impact">
                                                    <div class="upload-container">
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <a href="javascript:void(0)"
                                                      class="btn-input-icon btn-action"
                                                      data-action="update-realisasi-dampak"
                                                      data-bs-toggle="tooltip"
                                                      title="Update Realisasi Dampak"
                                                      data-id="{{ $perlakuan->id }}"
                                                      data-dampak-text="{{ $dampak->dampak_risiko }}">
                                                        <span class="bx bx-edit-alt text-primary"></span>
                                                    </a>
                                                </div>
                                            </td>
                                        @else
                                            <td colspan="6" class="text-center text-gray italic">Belum ada rencana perlakuan</td>
                                        @endif
                                        </tr>
                                    @endforeach
                                @endforeach

                                @if($projectRisk->dampakRisikoProjects->isEmpty())
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data dampak risiko</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <h5 class="mt-6 mb-2">Perlakuan terhadap Penyebab Risiko</h5>
                        <table class="table" id="table-penyebab-risiko">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Penyebab Risiko</th>
                                    <th>Rencana Perlakuan</th>
                                    <th>Biaya Perlakuan</th>
                                    <th>Progress (%)</th>
                                    <th>Realisasi Biaya</th>
                                    <th>Waktu Realisasi</th>
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penyebabRisikoProjects as $penyebabRisiko)
                                    @foreach ($penyebabRisiko->perlakuanPenyebabRisiko as $perlakuan)
                                        @if ($loop->index == 0)
                                        <tr data-id="{{ $perlakuan?->id }}">
                                            <td rowspan="{{ $penyebabRisiko->perlakuanPenyebabRisiko->count() }}">{{ $loop->iteration }}</td>
                                            <td rowspan="{{ $penyebabRisiko->perlakuanPenyebabRisiko->count() }}">{{ $penyebabRisiko->penyebab_risiko ?: '-' }}</td>
                                        @else
                                        <tr data-id="{{ $perlakuan->id }}">
                                        @endif
                                            <td>{{ $perlakuan->rencana_perlakuan_risiko ?: '-' }}</td>
                                            <td>
                                              <span class="inputmask-fixed">
                                                {{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : 'Rp 0' }}
                                              </span>
                                            </td>
                                            <td class="display-progress inputmask-fixed text-center">{{ $perlakuan->progress_rencana_perlakuan_risiko ?? '-' }}</td>
                                            <td class="display-biaya inputmask-fixed">
                                              {{  $perlakuan->realisasi_biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->realisasi_biaya_perlakuan_risiko, 0, ',', '.') : 'Rp 0' }}
                                            </td>
                                            <td class="display-timeline text-center">{{ $perlakuan?->lastMonitoring?->timeline_perlakuan_risiko_start?->format('d/m/Y') ?: '-' }}</td>
                                            <td style="white-space:nowrap" class="column-action">
                                                <div class="d-none dom-saved">
                                                    <div class="upload-container">
                                                    </div>
                                                    <input type="textarea" class="input-file-description" name="document_description_{{ $perlakuan->id }}" id="deskripsi_perlakuan_risiko_{{ $perlakuan->id }}">
                                                </div>
                                                <div class="text-center">
                                                    <a href="javascript:void(0)"
                                                    class="btn-input-icon btn-action"
                                                    data-action="update-realisasi"
                                                    data-bs-toggle="tooltip"
                                                    title="Update Penyebab Realisasi"
                                                    data-id="{{ $perlakuan->id }}">
                                                        <span class="bx bx-edit-alt text-primary"></span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach

                                @if($penyebabRisikoProjects->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <h5 class="mt-6 mb-2">Perlakuan terhadap KRI</h5>
                        <table class="table" id="table-kri">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Key Risk Indicator</th>
                                    <th>Satuan KRI</th>
                                    <th>Batas Aman</th>
                                    <th>Batas Waspada</th>
                                    <th>Batas Bahaya</th>
                                    <th>Nilai KRI</th>
                                    <th>Kondisi</th>
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kriProjects as $kriProject)
                                    <tr data-id="{{ $kriProject->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $kriProject->kri ?: '-' }}</td>
                                        <td>{{ $kriProject->satuan_kri ?: '-' }}</td>
                                        <td>{{ $kriProject->batas_aman ?: '-' }}</td>
                                        <td>{{ $kriProject->batas_waspada ?: '-' }}</td>
                                        <td>{{ $kriProject->batas_bahaya ?: '-' }}</td>
                                        <td class="display-nilai-kri">
                                            {{ $kriProject->nilai_kri_terkini ?? '-' }}
                                        </td>
                                        <td class="display-kondisi">
                                            @php
                                                $statusMap = [
                                                    1 => 'Aman',
                                                    2 => 'Waspada',
                                                    3 => 'Bahaya',
                                                ];
                                                $status = $kriProject->status_kri_terkini;
                                                $displayStatus = $statusMap[$status] ?? '-';
                                            @endphp
                                            {{ $displayStatus }}
                                        </td>
                                        <td style="white-space:nowrap">
                                            {{-- <a href="javascript:void(0)" class="hover-underline px-1 btn-action" data-action="update-kri" data-id="{{ $kriProject->id }}">Update KRI</a> --}}
                                            <div class="text-center">
                                            <a href="javascript:void(0)"
                                              class="btn-input-icon btn-action"
                                              data-action="update-kri"
                                              data-bs-toggle="tooltip"
                                              title="Update KRI"
                                              data-id="{{ $kriProject->id }}">
                                                <span class="bx bx-chart text-primary"></span>
                                            </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($kriProjects->isEmpty())
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text d-flex align-items-center justify-content-between cursor-pointer" data-bs-toggle="collapse" data-bs-target="#logPerlakuanRisiko" aria-expanded="false">
                    <h4 class="mb-0 ff-heading-sm">Log Perlakuan Risiko</h4>
                    <span class="toggle-text ms-2"><i class='bx bx-chevron-down'></i> Show Log</span>
                </div>
            </div>
            <div class="row g-2 collapse" id="logPerlakuanRisiko">
                <div class="card">
                    <div class="card-body">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Penyebab Risiko</th>
                                    <th>Tanggal Input</th>
                                    <th>Waktu Perlakuan Risiko</th>
                                    <th>PIC</th>
                                    <th>Rencana Perlakuan Risiko</th>
                                    <th>Rencana Biaya Perlakuan Risiko (Rp)</th>
                                    <th>Deskripsi Perlakuan Risiko</th>
                                    <th>Realisasi Biaya Perlakuan Risiko (Rp)</th>
                                    <th>Progress (%)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penyebabRisikoProjects as $penyebabRisiko)
                                @php
                                    $perlakuanPenyebabMonitorings = $penyebabRisiko->perlakuanPenyebabRisiko->pluck('perlakuanPenyebabMonitorings')->flatten();
                                @endphp

                                @foreach ($penyebabRisiko->perlakuanPenyebabRisiko as $perlakuanPenyebab)
                                    @foreach ($perlakuanPenyebab->perlakuanPenyebabMonitorings as $perlakuanMonitoring)
                                        <tr>
                                            <td>{{ $loop->iteration + 1 }}</td>
                                            <td>{{ $penyebabRisiko->penyebab_risiko }}</td>
                                            <td>{{ $perlakuanMonitoring->created_at }}</td>
                                            <td>{{ $perlakuanMonitoring->timeline_perlakuan_risiko_start }}</td>
                                            <td>{{ $perlakuanPenyebab->pic }}</td>
                                            <td>{{ $perlakuanPenyebab->rencana_perlakuan_risiko }}</td>
                                            <td>{{ $perlakuanPenyebab->biaya_perlakuan_risiko }}</td>
                                            <td>{{ $perlakuanMonitoring->deskripsi_perlakuan_risiko }}</td>
                                            <td>{{ $perlakuanMonitoring->realisasi_biaya_perlakuan_risiko }}</td>
                                            <td>{{ $perlakuanMonitoring->progress_rencana_perlakuan_risiko }}</td>
                                            <td>
                                                <button type="button"
                                                    class="btn-input-icon btn-action"
                                                    data-action="view-details"
                                                    data-bs-toggle="tooltip"
                                                    title="Detail Mitigasi"
                                                    data-perlakuan-id="{{ $perlakuanPenyebab->id }}"
                                                    data-id="{{ $perlakuanMonitoring->id }}">
                                                    <span class="bx bx-show text-primary"></span>
                                            </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-5">
            <div class="row g-2">
                <div class="col-auto">
                    <a href="{{ route('projects.monitorings.index', ['project' => $projectPeriode->id, 'tahun' => $tahun, 'quarter' => $quarter, 'month' => $month]) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                <div class="col-auto">
                    <button type="button" data-action="save" class="btn btn-primary ms-auto btn-action">Simpan</button>
                </div>
                <div class="col-auto ms-auto">
                    <button type="button" data-action="save-and-close" class="btn btn-danger btn-action">Simpan dan Close Risiko</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Modal Update Realisasi KRI --}}
    <div class="modal fade" id="modalUpdateKri" tabindex="-1" role="dialog" aria-labelledby="modalUpdateKri" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" id="formUpdateKri">
                    <div class="modal-header d-flex flex-between-center">
                        <h3 class="modal-title h4" id="modalUpdateKriLabel">Edit Rencana Terhadap KRI</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    {{ Form::hidden('kri_project_id', '') }}
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-12 mb-4">
                                <div class="card border-0 shadow-sm" style="border-left: 4px solid #696cff !important; background: linear-gradient(135deg, #f5f5ff 0%, #eef1ff 100%); height: fit-content;">
                                    <div class="card-body py-3 px-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div>
                                                <h5 class="mb-0 fw-bold" style="color: #696cff;">Realisasi KRI Sebelumnya</h5>
                                                <medium class="text-muted d-block" id="info_prev_kri_period">-</medium>
                                            </div>
                                        </div>

                                        <div class="row g-3" id="container_prev_kri_data">
                                            <div class="col-md-4">
                                                <medium class="text-muted d-block">Nilai Realisasi KRI</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_kri_value">-</span>
                                            </div>
                                            <div class="col-md-4">
                                                <medium class="text-muted d-block">Status KRI</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_kri_status">-</span>
                                            </div>
                                        </div>

                                        <div id="container_prev_kri_empty" class="text-center py-2 d-none">
                                            <i class='bx bx-info-circle fs-3 text-muted mb-1'></i>
                                            <p class="text-muted mb-0">Belum ada data realisasi KRI sebelumnya.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group form-floating">
                                    <input type="text" class="form-control" name="key_risk_indicator" disabled>
                                    <label for="key_risk_indicator_1">Key Risk Indicator</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating text-center">
                                    <input type="text" class="form-control border-success" name="batas_aman" disabled>
                                    <label for="batas_aman_1">Batas Aman</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating text-center">
                                    <input type="text" class="form-control border-warning" name="batas_waspada" disabled>
                                    <label for="batas_waspada_1">Batas Waspada</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating text-center">
                                    <input type="text" class="form-control border-danger" name="batas_bahaya" disabled>
                                    <label for="batas_bahaya_1">Batas Bahaya</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating">
                                    <input type="text" class="form-control" name="nilai_kri" required>
                                    <label for="batas_bahaya_1">Nilai KRI</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating">
                                    <select class="form-select" name="status_kri" required>
                                        <option value="1">Aman</option>
                                        <option value="2">Waspada</option>
                                        <option value="3">Bahaya</option>
                                    </select>
                                    <label for="batas_bahaya_1">Status KRI</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSimpanUpdateKri">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Modal Detail Mitigasi. --}}
    <div class="modal fade" id="modalMitigasi" tabindex="-1" role="dialog" aria-labelledby="modalMitigasi" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" id="formMitigasi">
                    @csrf
                    @method('PUT')
                    <div class="modal-header d-flex flex-between-center">
                        <h3 class="modal-title h4" id="modalMitigasiLabel">Detail Mitigasi Data</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <!-- Hidden Input for penyebab_risiko_id -->
                            {{ Form::hidden('penyebab_risiko_id', '') }}

                            <div class="col-12">
                                <div class="form-floating">
                                    {{ Form::text('penyebab_risiko', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                    <label>Penyebab Risiko</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    {{ Form::textarea('rencana_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'disabled' => 'disabled']) }}
                                    <label>Rencana Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    {{ Form::text('biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'disabled' => 'disabled']) }}
                                    <label>Biaya Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    {{ Form::text('pic', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                    <label>PIC</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <h5 class="mt-3 mb-0">Realisasi</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    {{ Form::text('realisasi_biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'disabled' => 'disabled']) }}
                                    <label>Realisasi Biaya Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    {{ Form::number('progress_perlakuan_risiko', null, ['class' => 'form-control', 'disabled' => 'disabled', 'max' => 100]) }}
                                    <label>Progress Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    {{ Form::textarea('deskripsi_perlakuan_risiko', '', ['class' => 'form-control', 'required', 'rows' => 3, 'disabled' => 'disabled']) }}
                                    <label for="deskripsi_perlakuan_risiko">Deskripsi Perlakuan Risiko</label>
                                </div>
                            </div>
                            {{-- <div class="col-12">
                                <div class="form-floating">
                                    {{ Form::select('jenis_program_rkap_id', \App\Models\JenisProgramDalamRKAP::pluck('jenis_program_rkap', 'id'), '', ['class' => 'form-select', 'required', 'disabled' => 'disabled']) }}
                                    <label for="jenis_program_rkap_id">Jenis Program RKAP</label>
                                </div>
                            </div> --}}
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="timeline_perlakuan_risiko" disabled>
                                    <label>Waktu Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h5 class="mt-3 mb-0">Dokumen</h5>
                            </div>
                            <div class="col-md-3 col-auto text-end justify-content-end d-flex flex-column">
                                <div>

                                </div>
                            </div>
                            <div class="col-12">
                                <table class="table tabel-dokumen-mitigasi">
                                    <thead>
                                        <tr>
                                            <th scope="col">Dokumen</th>
                                            <th scope="col">Deskripsi</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Update Realisasi Penyebab --}}
    <div class="modal fade" id="modalUpdateRealisasi" tabindex="-1" role="dialog" aria-labelledby="modalUpdateRealisasi" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" id="formUpdateRealisasi">
                    @csrf
                    @method('PUT')
                    <div class="modal-header d-flex flex-between-center">
                        <h3 class="modal-title h4" id="modalUpdateRealisasiLabel">Realisasi Perlakuan Risiko</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <!-- Hidden Input for penyebab_risiko_id -->
                            {{ Form::hidden('penyebab_risiko_id', '') }}

                            <div class="col-12">
                                <div class="form-floating">
                                    {{ Form::text('penyebab_risiko', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                    <label>Penyebab Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="perkiraan_waktu_terpapar_risiko_mulai" name="perkiraan_waktu_terpapar_risiko_mulai" required disabled>
                                    <label for="perkiraan_waktu_terpapar_risiko_mulai">Perkiraan Waktu Mulai Terpapar Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="perkiraan_waktu_terpapar_risiko_akhir" name="perkiraan_waktu_terpapar_risiko_akhir" required disabled>
                                    <label for="perkiraan_waktu_terpapar_risiko_akhir">Perkiraan Waktu Selesai Terpapar Risiko</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    {{ Form::textarea('rencana_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'disabled' => 'disabled']) }}
                                    <label>Rencana Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    {{ Form::text('biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'disabled' => 'disabled']) }}
                                    <label>Biaya Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    {{ Form::text('pic', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                    <label>PIC</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="timeline_perlakuan_risiko_start" name="timeline_perlakuan_risiko_start" required disabled>
                                    <label for="timeline_perlakuan_risiko_start">Waktu Mulai Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="timeline_perlakuan_risiko_end" name="timeline_perlakuan_risiko_end" required disabled>
                                    <label for="timeline_perlakuan_risiko_end">Waktu Selesai Perlakuan Risiko</label>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #696cff !important; background: linear-gradient(135deg, #f5f5ff 0%, #eef1ff 100%); height: fit-content;">
                                    <div class="card-body py-3 px-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div>
                                                <h5 class="mb-0 fw-bold" style="color: #696cff;">Realisasi Sebelumnya</h5>
                                                <medium class="text-muted d-block" id="info_prev_penyebab_period">-</medium>
                                            </div>
                                        </div>

                                        <div class="row g-3" id="container_prev_penyebab_data">
                                            <div class="col-md-6 col-lg-4">
                                                <medium class="text-muted d-block">Progress</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_penyebab_progress">-</span>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <medium class="text-muted d-block">Biaya Realisasi</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_penyebab_cost">-</span>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <medium class="text-muted d-block">Tgl Pelaksanaan</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_penyebab_date">-</span>
                                            </div>
                                            <div class="col-md-12">
                                                <medium class="text-muted d-block">Catatan</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_penyebab_notes">-</span>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <medium class="text-muted d-block mb-1">Evidence Sebelumnya</medium>
                                                <div id="info_prev_penyebab_evidence">-</div>
                                            </div>
                                        </div>

                                        <div id="container_prev_penyebab_empty" class="text-center py-2 d-none">
                                            <i class='bx bx-info-circle fs-3 text-muted mb-1'></i>
                                            <p class="text-muted mb-0">Belum ada data realisasi mitigasi sebelumnya.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <h5 class="mt-3 mb-0">Realisasi</h5>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    {{ Form::text('realisasi_biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'required' => 'required']) }}
                                    <label>Realisasi Biaya Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    {{ Form::number('progress_perlakuan_risiko', null, ['class' => 'form-control', 'required' => 'required', 'min' => 0, 'max' => 100, 'oninput' => 'if(this.value < 0) this.value = 0; if(this.value > 100) this.value = 100;']) }}
                                    <label>Progress Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    {{ Form::textarea('deskripsi_perlakuan_risiko', '', ['class' => 'form-control', 'required', 'rows' => 3, 'required' => 'required']) }}
                                    <label for="deskripsi_perlakuan_risiko">Deskripsi Perlakuan Risiko</label>
                                </div>
                            </div>
                            {{-- <div class="col-12">
                                <div class="form-floating">
                                    {{ Form::select('jenis_program_rkap_id', \App\Models\JenisProgramDalamRKAP::pluck('jenis_program_rkap', 'id'), '', ['class' => 'form-select', 'required']) }}
                                    <label for="jenis_program_rkap_id">Jenis Program RKAP</label>
                                </div>
                            </div> --}}
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control bg-white" id="timelineInput" name="timeline_perlakuan_risiko" required>
                                    <label for="timelineInput">Waktu Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h5 class="mt-3 mb-0">Dokumen</h5>
                            </div>
                            <div class="col-md-3 col-auto text-end justify-content-end d-flex flex-column">
                                <div>

                                </div>
                            </div>
                            <div class="col-12">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Dokumen</th>
                                            <th scope="col">Deskripsi</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-dokumen">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-center"><button type="button" class="btn btn-link btn-sm py-1" id="btnTambahDokumen">Tambah Dokumen</button></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSimpanUpdateRealisasi">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- Modal Update Realisasi Dampak --}}
    <div class="modal fade" id="modalUpdateRealisasiDampak" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <form method="POST" id="formUpdateRealisasiDampak">
                    @csrf
                    @method('PUT')
                    <div class="modal-header d-flex flex-between-center">
                        <h3 class="modal-title h4">Realisasi Perlakuan Dampak</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <input type="hidden" name="perlakuan_dampak_id" id="impact_id">

                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="impact_name" disabled>
                                    <label>Dampak Risiko</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" name="impact_plan" id="impact_plan" rows="3" disabled></textarea>
                                    <label>Rencana Perlakuan</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="impact_cost" id="impact_cost" disabled>
                                    <label>Biaya Rencana</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="impact_pic" id="impact_pic" disabled>
                                    <label>PIC</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="timeline_perlakuan_risiko_dampak_start" name="timeline_perlakuan_risiko_dampak_start" required disabled>
                                    <label for="timeline_perlakuan_risiko_dampak_start">Waktu Mulai Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="timeline_perlakuan_risiko_dampak_end" name="timeline_perlakuan_risiko_dampak_end" required disabled>
                                    <label for="timeline_perlakuan_risiko_dampak_end">Waktu Selesai Perlakuan Risiko</label>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #696cff !important; background: linear-gradient(135deg, #f5f5ff 0%, #eef1ff 100%); height: fit-content;">
                                    <div class="card-body py-3 px-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div>
                                                <h5 class="mb-0 fw-bold" style="color: #696cff;">Realisasi Sebelumnya</h5>
                                                <medium class="text-muted d-block" id="info_prev_dampak_period">-</medium>
                                            </div>
                                        </div>

                                        <div class="row g-3" id="container_prev_dampak_data">
                                            <div class="col-md-6 col-lg-4">
                                                <medium class="text-muted d-block">Progress</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_dampak_progress">-</span>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <medium class="text-muted d-block">Biaya Realisasi</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_dampak_cost">-</span>
                                            </div>
                                            <div class="col-md-6 col-lg-4">
                                                <medium class="text-muted d-block">Tgl Pelaksanaan</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_dampak_date">-</span>
                                            </div>
                                            <div class="col-md-12">
                                                <medium class="text-muted d-block">Catatan</medium>
                                                <span class="fw-semibold text-dark" id="info_prev_dampak_notes">-</span>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <medium class="text-muted d-block mb-1">Evidence Sebelumnya</medium>
                                                <div id="info_prev_dampak_evidence">-</div>
                                            </div>
                                        </div>

                                        <div id="container_prev_dampak_empty" class="text-center py-2 d-none">
                                            <i class='bx bx-info-circle fs-3 text-muted mb-1'></i>
                                            <p class="text-muted mb-0">Belum ada data realisasi mitigasi sebelumnya.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12"><h5 class="mt-3 mb-0">Realisasi Dampak</h5></div>

                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control inputmask-rupiah" name="realisasi_biaya_dampak" required>
                                    <label>Realisasi Biaya Perlakuan Risiko</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="progress_dampak" required min="0" max="100" oninput="if(this.value < 0) this.value = 0; if(this.value > 100) this.value = 100;">
                                    <label>Progress Perlakuan Risiko (%)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" name="deskripsi_dampak" rows="3" required></textarea>
                                    <label>Deskripsi Realisasi</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control bg-white" id="timelineImpactInput" name="timeline_dampak" required>
                                    <label>Waktu Realisasi</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <table class="table mt-3">
                                    <thead>
                                        <tr>
                                            <th>Dokumen</th>
                                            <th>Deskripsi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-dokumen-dampak"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-center">
                                                <button type="button" class="btn btn-link btn-sm" id="btnTambahDokumenDampak">Tambah Dokumen</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnSimpanUpdateRealisasiDampak">Simpan Realisasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .hover-underline:hover {
        text-decoration: underline;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
const previousMonitoring = @json($previousMonitoring);
const monthNamesArray = ["", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
const routeDeleteDoc = "{{ route('projects.monitorings.document.destroy', ['project' => request()->route('project'), 'monitoring' => request()->route('monitoring'), 'documentId' => ':id']) }}";
const acceptedFiles = ".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx";
const maxFileSize = 10 * 1024 * 1024; // 10MB dalam Bytes
const maxFilesCount = 10; // Maksimal 10 file
const existingPenyebabDocs = @json($projectRisk->projectRiskMonitoring?->perlakuanPenyebabRisikoDocuments->groupBy('perlakuan_penyebab_risiko_id') ?? []);
const existingDampakDocs = @json($projectRisk->projectRiskMonitoring?->perlakuanDampakRisikoDocuments->groupBy('perlakuan_dampak_risiko_id') ?? []);

const projectRisk = @json($projectRisk);
const penyebabRisikoProjects = @json($penyebabRisikoProjects->keyBy('id'));
const jsonPerlakuanPenyebabRisikos = @json($penyebabRisikoProjects->pluck('perlakuanPenyebabRisiko')->flatten()->keyBy('id'));
const perlakuanPenyebabRisikos = Object.fromEntries(
    Object.entries(jsonPerlakuanPenyebabRisikos).map(([key, value]) => {
        return [
            key,
            {
                ...value,
                "realisasi_biaya_perlakuan_risiko": value?.last_monitoring?.realisasi_biaya_perlakuan_risiko ? parseFloat(value?.last_monitoring?.realisasi_biaya_perlakuan_risiko) : 0.00,
                "progress_rencana_perlakuan_risiko": value?.last_monitoring?.progress_rencana_perlakuan_risiko ?? 0,
                "deskripsi_perlakuan_risiko": value?.last_monitoring?.deskripsi_perlakuan_risiko ?? "",
                "timeline_perlakuan_risiko": value?.last_monitoring?.timeline_perlakuan_risiko_start ?? ""
            }
        ];
    })
);

function validateFormSweetAlert(formId) {
    let isValid = true;
    $(`#${formId} [required]`).each(function() {
        if ($(this).val() === '' || $(this).val() === null) {
            isValid = false;
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    if (!isValid) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Harap lengkapi semua field yang wajib diisi!',
        });
    }
    return isValid;
}

function renderExistingDocuments(docs, tbodyElement) {
    tbodyElement.empty();
    if(docs && docs.length > 0) {
        docs.forEach(function(doc) {
            tbodyElement.append(`
                <tr class="existing-doc" data-doc-id="${doc.id}">
                    <td><span class="text-primary text-truncate d-block" style="max-width: 200px;"><a href="${doc.file_path}" target="_blank">${doc.file_name}</a></span></td>
                    <td><input type="text" class="form-control form-control-sm" value="${doc.description || ''}" disabled></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-delete-db-doc" data-id="${doc.id}">
                            <i class="bx bx-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
            `);
        });
    }
}

// Event Hapus File dari Database (Poin 1)
$(document).on('click', '.btn-delete-db-doc', function() {
    const docId = $(this).data('id');
    const docType = $(this).data('type'); // Ambil tipe dari atribut HTML
    const tr = $(this).closest('tr');

    Swal.fire({
        title: 'Hapus Dokumen?',
        text: "Dokumen yang dihapus dari server tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                // Sertakan parameter type ke URL
                url: routeDeleteDoc.replace(':id', docId) + '?type=' + docType,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    Swal.fire('Terhapus!', res.message, 'success');
                    tr.remove(); // Hapus baris tabel jika berhasil
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus.', 'error');
                }
            });
        }
    });
});

const jsonPerlakuanDampakRisikos = @json($projectRisk->perlakuanDampakRisikos->keyBy('id'));
const perlakuanDampakRisikos = Object.fromEntries(
    Object.entries(jsonPerlakuanDampakRisikos).map(([key, value]) => {
        return [
            key,
            {
                ...value,
                "realisasi_biaya_perlakuan_risiko": value?.last_monitoring?.realisasi_biaya_perlakuan_risiko ? parseFloat(value?.last_monitoring?.realisasi_biaya_perlakuan_risiko) : 0.00,
                "progress_rencana_perlakuan_risiko": value?.last_monitoring?.progress_rencana_perlakuan_risiko ?? 0,
                "deskripsi_perlakuan_risiko": value?.last_monitoring?.deskripsi_perlakuan_risiko ?? "",
                "timeline_perlakuan_risiko": value?.last_monitoring?.timeline_perlakuan_risiko_start ?? ""
            }
        ];
    })
);

// 2. Buka Modal Update Dampak
$(document).on('click', '[data-action="update-realisasi-dampak"]', function() {
    const id = $(this).data('id');
    const perlakuan = perlakuanDampakRisikos[id];
    const dampakText = $(this).data('dampak-text');

    if (!perlakuan) return;

    $('#impact_id').val(id);
    $('#impact_name').val(dampakText);
    $('#impact_plan').val(perlakuan.rencana_perlakuan_risiko);
    $('#impact_cost').val('Rp ' + Intl.NumberFormat('id-ID').format(perlakuan.biaya_perlakuan_risiko));
    $('#impact_pic').val(perlakuan?.pic_jabatan?.name);

    $('#timeline_perlakuan_risiko_dampak_start').val(dayjs(perlakuan.timeline_perlakuan_risiko_start).format('DD/MM/YYYY'));
    $('#timeline_perlakuan_risiko_dampak_end').val(dayjs(perlakuan.timeline_perlakuan_risiko_end).format('DD/MM/YYYY'));

    const form = $('#formUpdateRealisasiDampak');
    form.find('[name="realisasi_biaya_dampak"]').val(perlakuan.realisasi_biaya_perlakuan_risiko ?? 0);
    form.find('[name="progress_dampak"]').val(perlakuan.progress_rencana_perlakuan_risiko ?? 0);
    form.find('[name="deskripsi_dampak"]').val(perlakuan.deskripsi_perlakuan_risiko ?? '');

    if (perlakuan.timeline_perlakuan_risiko) {
        if (perlakuan.timeline_perlakuan_risiko.includes('-')) {
            impactFlatpickr.setDate(dayjs(perlakuan.timeline_perlakuan_risiko).format('DD/MM/YYYY'));
        } else {
            impactFlatpickr.setDate(perlakuan.timeline_perlakuan_risiko, true, "d/m/Y");
        }
    } else {
        impactFlatpickr.clear();
    }

    // Copy elemen file sementara dari tabel utama ke Modal
    const trImpact = $(`#table-dampak-risiko tr[data-id="${id}"]`);
    const domCell = trImpact.find('td.column-action-impact');
    const domSaved = domCell.find('.dom-saved-impact');
    domCell.find('.dom-edited-impact').remove();

    const domEdited = domSaved.clone().addClass('dom-edited-impact').removeClass('dom-saved-impact');

    // Copy native files dari clone
    const originalFileInputs = domSaved.find('input[type="file"]');
    domEdited.find('input[type="file"]').each(function(index) {
        if (originalFileInputs[index].files && originalFileInputs[index].files.length > 0) {
            this.files = originalFileInputs[index].files;
        }
    });
    domCell.append(domEdited);

    // Tampilkan Tabel File
    const tableDocument = $('#modalUpdateRealisasiDampak .table-dokumen-dampak');
    tableDocument.empty();

    // Tampilkan dokumen existing dari database
    const savedDocs = existingDampakDocs[id] || [];
    savedDocs.forEach(function(doc) {
        const descText = doc.description ? doc.description : '';
        tableDocument.append(`
            <tr class="existing-doc" data-doc-id="${doc.id}">
                <td>
                    <span class="text-primary text-truncate d-block" style="max-width: 200px;">
                        <a href="${doc.url}" target="_blank">${doc.file_name}</a>
                    </span>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" value="${descText}" placeholder="Tidak ada deskripsi" disabled>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger btn-delete-db-doc" data-id="${doc.id}" data-type="dampak">
                        <i class="bx bx-trash"></i> Hapus
                    </button>
                </td>
            </tr>
        `);
    });

    domEdited.find('input[type=file]').each(function() {
        const fileName = $(this).prop('files')[0]?.name;
        const docId = $(this).prop('id');
        const descInput = domEdited.find(`input.input-file-description-array[data-ref="${docId}"]`);
        const description = descInput.val() || '';

        tableDocument.append(`
            <tr data-id="${docId}">
                <td>
                    <span class="dokumen-filename text-truncate d-block" style="max-width: 200px;">${fileName}</span>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm input-desc-impact" value="${description}" placeholder="Keterangan...">
                </td>
                <td>
                    <button type="button" class="btn btn-link btn-sm text-danger btn-delete-doc-impact">Hapus</button>
                </td>
            </tr>
        `);
    });

    if (previousMonitoring) {
        const prevDataDampak = previousMonitoring.perlakuan_dampak_monitorings?.find(m => m.perlakuan_dampak_id == id);

        $('#info_prev_dampak_period').html(`Periode: <strong>${monthNamesArray[previousMonitoring.month]} ${previousMonitoring.tahun}</strong>`);

        if (prevDataDampak) {
            console.log(prevDataDampak)
            $('#container_prev_dampak_data').removeClass('d-none');
            $('#container_prev_dampak_empty').addClass('d-none');

            $('#info_prev_dampak_progress').text((prevDataDampak.progress_rencana_perlakuan_risiko || 0) + '%');
            $('#info_prev_dampak_cost').text('Rp ' + Intl.NumberFormat('id-ID').format(prevDataDampak.realisasi_biaya_perlakuan_risiko || 0));
            $('#info_prev_dampak_date').text(prevDataDampak.timeline_perlakuan_risiko_start ? dayjs(prevDataDampak.timeline_perlakuan_risiko_start).format('DD/MM/YYYY') : '-');
            $('#info_prev_dampak_notes').text(prevDataDampak.deskripsi_perlakuan_risiko || '-');

            // Ambil dan buat list dokumen evidence
            const prevDocsDampak = previousMonitoring.perlakuan_dampak_risiko_documents?.filter(d => d.perlakuan_dampak_risiko_id == id) || [];
            let docsHtmlDampak = prevDocsDampak.length > 0
                ? '<ul class="ps-3 mb-0">' + prevDocsDampak.map(d => `<li><a href="/storage/${d.file_path}" target="_blank" class="text-primary">${d.file_name}</a> <small class="text-muted">(${d.description || '-'})</small></li>`).join('') + '</ul>'
                : '<em class="text-muted small">Tidak ada dokumen evidence.</em>';
            $('#info_prev_dampak_evidence').html(docsHtmlDampak);
        } else {
            $('#container_prev_dampak_data').addClass('d-none');
            $('#container_prev_dampak_empty').removeClass('d-none');
        }
    } else {
        $('#info_prev_dampak_period').html(`-`);
        $('#container_prev_dampak_data').addClass('d-none');
        $('#container_prev_dampak_empty').removeClass('d-none');
    }

    toggleAddDocButtonDampak();
    $('#modalUpdateRealisasiDampak').modal('show');
});


// 3. Tambah Dokumen Dampak
$('#btnTambahDokumenDampak').click(function() {
    const dampakRisikoId = $('#impact_id').val();
    const domCell = $(`#table-dampak-risiko tr[data-id="${dampakRisikoId}"] td.column-action-impact`);
    const domEdited = domCell.find('.dom-edited-impact');
    const uploadContainer = domEdited.find('.upload-container');
    const tableDokumen = $('#modalUpdateRealisasiDampak .table-dokumen-dampak');

    // if (tableDokumen.find('tr').length >= 3) {
    //    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Maksimal 3 dokumen yang dapat diunggah.' });
    //    return;
    // }

    const newId = 'dokumen-dampak-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

    // Bikin input file array biasa []
    uploadContainer.append(`<input type="file" style="display:none;" name="document_dampak_file_${dampakRisikoId}[]" id="${newId}" required>`);

    // Bikin hidden input untuk array description
    uploadContainer.append(`<input type="hidden" class="input-file-description-array" name="document_description_${dampakRisikoId}[]" data-ref="${newId}">`);

    tableDokumen.append(`
        <tr data-id="${newId}">
            <td><span class="dokumen-filename text-truncate d-block" style="max-width: 200px;">Pilih file...</span></td>
            <td>
                <input type="text" class="form-control form-control-sm input-desc-impact" placeholder="Keterangan...">
            </td>
            <td>
                <button type="button" class="btn btn-link btn-sm text-danger btn-delete-doc-impact">Hapus</button>
            </td>
        </tr>
    `);

    const appended = tableDokumen.find(`tr[data-id="${newId}"]`);
    const fileInput = domEdited.find(`#${newId}`);

    // Event on Change FIle
    fileInput.change(function() {
        if (this.files && this.files[0]) {
            appended.find(`.dokumen-filename`).text(this.files[0].name);

            let totalSize = 0;
            domEdited.find('input[type="file"]').each(function() {
                if (this.files && this.files[0]) totalSize += this.files[0].size;
            });

            const maxSizeLimit = {{ config('filesystems.max_upload_size', 10) }} * 1024 * 1024;
            if (totalSize > maxSizeLimit) {
                Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Total ukuran maksimal ' + (maxSizeLimit / (1024 * 1024)) + ' MB.' });
                appended.find('.btn-delete-doc-impact').click();
            }
        }
    });

    // Deteksi Cancel File Explorer
    window.addEventListener('focus', function detectCancel() {
        setTimeout(function() {
            if (fileInput.length && fileInput[0].files.length === 0) {
                appended.find('.btn-delete-doc-impact').click();
            }
        }, 300);
        window.removeEventListener('focus', detectCancel);
    }, { once: true });

    fileInput.click();
    toggleAddDocButtonDampak();
});

// 4. Update Realtime Text Deskripsi ke input hidden
$(document).on('input', '.input-desc-impact', function() {
    const tr = $(this).closest('tr');
    const fileId = tr.data('id');
    const dampakId = $('#impact_id').val();
    const val = $(this).val();

    const domEdited = $(`#table-dampak-risiko tr[data-id="${dampakId}"] td.column-action-impact .dom-edited-impact`);
    domEdited.find(`input.input-file-description-array[data-ref="${fileId}"]`).val(val);
});

// 5. Hapus Baris Dokumen
$(document).on('click', '.btn-delete-doc-impact', function() {
    const tr = $(this).closest('tr');
    const fileId = tr.data('id');
    const dampakId = $('#impact_id').val();

    const domEdited = $(`#table-dampak-risiko tr[data-id="${dampakId}"] td.column-action-impact .dom-edited-impact`);
    domEdited.find(`#${fileId}`).remove(); // hapus input file
    domEdited.find(`input.input-file-description-array[data-ref="${fileId}"]`).remove(); // hapus input deskripsi
    tr.remove();

    toggleAddDocButtonDampak();
});

function toggleAddDocButtonDampak() {
    const rowCount = $('#modalUpdateRealisasiDampak .table-dokumen-dampak tr').length;
    if (rowCount >= 3) {
        $('#modalUpdateRealisasiDampak table tfoot').hide();
    } else {
        $('#modalUpdateRealisasiDampak table tfoot').show();
    }
}

// 6. Simpan Form Realisasi Dampak
$('#btnSimpanUpdateRealisasiDampak').on('click', function() {
    if (!validateFormSweetAlert('formUpdateRealisasiDampak')) return; // Poin 5

    const id = $('#impact_id').val();
    let realisasiBiaya = $('#formUpdateRealisasiDampak [name="realisasi_biaya_dampak"]').inputmask('unmaskedvalue') || 0;

    perlakuanDampakRisikos[id]['realisasi_biaya_perlakuan_risiko'] = realisasiBiaya;
    perlakuanDampakRisikos[id]['progress_rencana_perlakuan_risiko'] = $('#formUpdateRealisasiDampak [name="progress_dampak"]').val();
    perlakuanDampakRisikos[id]['deskripsi_perlakuan_risiko'] = $('#formUpdateRealisasiDampak [name="deskripsi_dampak"]').val();
    perlakuanDampakRisikos[id]['timeline_perlakuan_risiko'] = $('#timelineImpactInput').val();

    const tr = $(`#table-dampak-risiko tr[data-id="${id}"]`);
    tr.find('.display-biaya').text('Rp ' + Intl.NumberFormat('id-ID').format(realisasiBiaya));
    tr.find('.display-progress').text(perlakuanDampakRisikos[id]['progress_rencana_perlakuan_risiko']);
    tr.find('.display-timeline').text(perlakuanDampakRisikos[id]['timeline_perlakuan_risiko']);

    tr.find('.dom-saved-impact').remove();
    tr.find('.dom-edited-impact').removeClass('dom-edited-impact').addClass('dom-saved-impact');

    $('#modalUpdateRealisasiDampak').modal('hide');
    Swal.fire({ icon: 'success', title: 'Tersimpan', text: 'Data realisasi sementara disimpan. Jangan lupa klik tombol Simpan utama.', timer: 2000, showConfirmButton: false });
});

const kriProjects = @json($kriProjects->keyBy('id'));
const quarter = {{ $quarter }};
const namaRisiko = @json($projectRisk->peristiwa_risiko_id ? $peristiwaRisiko->title : $projectRisk->rencana_kegiatan);
const month = @json($month);
const year = @json($tahun);

var flatpickrIns = flatpickr("#timelineInput", {
    mode: "single",
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
    // maxDate: dayjs().toDate(),
    // maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
    disableMobile: true
});

$("#timelineInput").data('_flatpickr', flatpickrIns);

var timeline1 = flatpickr("#perkiraan_waktu_terpapar_risiko_mulai", {
    mode: "single",
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    //maxDate: endOfYear,
    disableMobile: true
});
var timeline2 = flatpickr("#perkiraan_waktu_terpapar_risiko_akhir", {
    mode: "single",
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    //maxDate: endOfYear,
    disableMobile: true
});
var perlakuanWaktu1 = flatpickr("#timeline_perlakuan_risiko_start", {
    mode: "single",
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    //maxDate: endOfYear,
    disableMobile: true
});
var perlakuanWaktu2 = flatpickr("#timeline_perlakuan_risiko_end", {
    mode: "single",
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    //maxDate: endOfYear,
    disableMobile: true
});

// Flatpickr untuk Dampak
var impactFlatpickr = flatpickr("#timelineImpactInput", {
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    // minDate: minDateString,
    minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
    // maxDate: dayjs().toDate(),
    // maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
    disableMobile: true
});

function getSkalaProbabilitasByValue(value) {
    const skalaProbabilitases = @json($skalaProbabilitas);
    for (index in skalaProbabilitases) {
        skalaProbabilitas = skalaProbabilitases[index];
        if (value >= skalaProbabilitas.min) {
            return skalaProbabilitas;
        }
    }
}

function refreshSkalaAndLevelRisiko() {
    const riskMaps = @json($riskMaps);

    // 1. Ambil Skala Dampak (Input Dropdown)
    const skalaDampak = parseInt($('#realisasi_skala_dampak').val());

    // 2. Ambil Skala Probabilitas (Input Dropdown) - PERBAIKAN DISINI
    // Kita ambil langsung valuenya (tingkat), bukan hitung dari persen
    const skalaProbabilitas = parseInt($('#realisasi_skala_probabilitas').val());

    // Update Hidden Input untuk keperluan submit form
    $('#realisasi_skala_probabilitas_hidden').val(skalaProbabilitas);
    $('#realisasi_skala_dampak_hidden').val(skalaDampak);

    const domSkalaRisiko = $('#realisasi_skala_risiko');
    const domLevelRisiko = $('#realisasi_level_risiko');

    // Validasi jika belum dipilih
    if (!skalaDampak || !skalaProbabilitas) {
        domSkalaRisiko.val('');
        domLevelRisiko.val('');
        $('#realisasi_skala_risiko_hidden').val('');
        $('#realisasi_level_risiko_hidden').val('');
        return;
    }

    // 3. Mapping Risk Map (Kunci: "SkalaDampak-SkalaProbabilitas")
    const key = skalaDampak + '-' + skalaProbabilitas;
    const riskMap = riskMaps[key];

    // console.log('Mapping Key:', key, 'Result:', riskMap); // Debugging

    if (riskMap) {
        domSkalaRisiko.val(riskMap.nilai_risiko);
        domLevelRisiko.val(riskMap.level_risiko);
        $('#realisasi_skala_risiko_hidden').val(riskMap.nilai_risiko);
        $('#realisasi_level_risiko_hidden').val(riskMap.level_risiko);
    } else {
        domSkalaRisiko.val('');
        domLevelRisiko.val('');
        $('#realisasi_skala_risiko_hidden').val('');
        $('#realisasi_level_risiko_hidden').val('');
    }
}

function validateRealisasiForm() {
    let isValid = true;
    let firstErrorField = null;

    const fieldsToValidate = [
        '#realisasi_nilai_dampak',
        '#realisasi_skala_dampak',
        '#realisasi_nilai_probabilitas'
    ];

    fieldsToValidate.forEach(function(fieldSelector) {
        const field = $(fieldSelector);

        if (field.is(':disabled')) {
            // Hapus error sebelumnya jika ada
            field.removeClass('is-invalid');
            field.closest('.form-floating').find('.invalid-feedback').remove();
            return; // Lanjut ke field berikutnya
        }

        field.removeClass('is-invalid');
        field.closest('.form-floating').find('.invalid-feedback').remove();

        if (field.val() === '' || field.val() === null) {
            isValid = false;
            field.addClass('is-invalid');
            field.closest('.form-floating').append('<div class="invalid-feedback d-block">Field ini wajib diisi.</div>');

            if (firstErrorField === null) {
                firstErrorField = field;
            }
        }

    });

    if (!isValid && firstErrorField) {
    $('html, body').animate({
            scrollTop: firstErrorField.offset().top - 150
        }, 500);

        firstErrorField.focus();
    }

    return isValid;
}

function submitForm(isClosed) {
    if (!validateRealisasiForm()) return; // Pastikan validasi utama sukses

    Swal.fire({
        title: 'Menyimpan Data...',
        text: 'Mohon tunggu, sedang mengupload dokumen dan menyimpan data.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $('.dom-edited').remove();
    $('.dom-edited-impact').remove();

    const formData = new FormData($('#main-form')[0]);
    formData.append('perlakuan_penyebab_risikos', JSON.stringify(perlakuanPenyebabRisikos));
    formData.append('perlakuan_dampak_risikos', JSON.stringify(perlakuanDampakRisikos));
    formData.append('kri_projects', JSON.stringify(kriProjects));
    formData.append('quarter', quarter);
    formData.append('_method', 'PUT');
    formData.append('is_closed', isClosed);

    $.ajax({
        url: '{{ route('projects.monitorings.update', ['project' => request()->route('project'), 'monitoring' => request()->route('monitoring')]) }}',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            Swal.fire({
                title: 'Berhasil',
                text: response.message,
                icon: 'success',
                confirmButtonText: 'OK',
            }).then(() => {
                let baseUrl = '{{ route('projects.monitorings.index', ['project' => request()->route('project')]) }}';
                const redirectUrl = `${baseUrl}?quarter={{ $quarter }}&tahun={{ $tahun }}&month={{ $month }}`;
                window.location.href = redirectUrl;
            });
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'OK',
            });
        }
    });
}

$(document).ready(function() {
    const thresholds = {
        limit: parseFloat("{{ $projectRisk->threshold_risk_limit }}") || 0,
        appetite: parseFloat("{{ $projectRisk->threshold_risk_appetite }}") || 0,
        tolerance: parseFloat("{{ $projectRisk->threshold_risk_tolerance }}") || 0
    };

    function updateMonitoringStatus() {
        const val = $('#aktual_current').val();
        // Bersihkan mask jika perlu (atau gunakan autoUnmask dari inputmask)
        const aktual = parseFloat(val) || 0;

        let status = "Aman";
        let colorClass = "border-success text-success bg-success-subtle";

        if (aktual > thresholds.tolerance) {
            status = "Bahaya";
            colorClass = "border-danger text-danger bg-danger-subtle";
        } else if (aktual >= thresholds.appetite) {
            status = "Siaga";
            colorClass = "border-warning text-warning bg-warning-subtle";
        }

        // Update Tampilan Badge Status
        $('#status-badge-container').text(status).removeClass().addClass('p-2 rounded text-center fw-bold fs-6 border ' + colorClass);
        $('#aktual_status_hidden').val(status);

        // Tampilkan tabel pengendalian jika status Siaga/Bahaya
        if (status === "Siaga" || status === "Bahaya") {
            $('#pengendalian-section').removeClass('d-none').addClass('animate__animated animate__fadeIn');
        } else {
            $('#pengendalian-section').addClass('d-none');
        }
    }

    // Trigger saat input aktual berubah
    $('#aktual_current').on('change keyup', updateMonitoringStatus);

    // Jalankan saat pertama load
    updateMonitoringStatus();

    // Simpan nilai skala dampak inherent dan probabilitas inherent
    const skalaDampakInherent = {{ $projectRiskAnalisa->skalaDampakObj?->tingkat ?? 0 }};
    const nilaiProbabilitasInherent = {{ $projectRiskAnalisa->nilai_probabilitas ?? 0 }};

    // Validasi saat memilih skala dampak
    $('#realisasi_skala_dampak').on('change', function(e) {
        // Skip validasi jika perubahan dari hitungRealisasiSkalaDampak()
        if (e.originalEvent === undefined) return;

        const selectedValue = parseInt($(this).val());
        if (selectedValue > skalaDampakInherent) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Nilai skala dampak realisasi tidak boleh lebih besar dari skala dampak inherent',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $(this).val('').trigger('change');
        }
    });

    // // Validasi nilai probabilitas
    // $('#realisasi_nilai_probabilitas').on('change', function() {
    //     const value = parseFloat($(this).val());
    //     if (value > nilaiProbabilitasInherent) {
    //         Swal.fire({
    //             title: 'Peringatan',
    //             text: 'Nilai probabilitas realisasi tidak boleh lebih besar dari nilai probabilitas inherent',
    //             icon: 'warning',
    //             confirmButtonText: 'OK'
    //         });
    //         $(this).val(nilaiProbabilitasInherent).trigger('change');
    //     }
    // });

    $('#realisasi_skala_probabilitas').on('change', function() {
        const $selectedOption = $(this).find('option:selected');
        const min = parseFloat($selectedOption.data('min'));
        const max = parseFloat($selectedOption.data('max'));
        const $inputProb = $('#realisasi_nilai_probabilitas');

        if (!isNaN(min) && !isNaN(max)) {
            // Set atribut biar user tau (optional)
            $inputProb.attr('min', min);
            $inputProb.attr('max', max);

            // Trigger validasi nilai jika sudah ada isinya
            if ($inputProb.val() !== '') {
                $inputProb.trigger('blur');
            }
        }
    });

    $('#realisasi_skala_dampak, #realisasi_skala_probabilitas').on('change', function() {
        refreshSkalaAndLevelRisiko();
    });

    // 2. Validasi Nilai Probabilitas saat diketik/blur
    $('#realisasi_nilai_probabilitas').on('keyup change', function() {
        const val = parseFloat($(this).val());
        const skalaProbabilitas = getSkalaProbabilitasByValue(val);

        if (skalaProbabilitas) {
            // Set value dropdown
            $('#realisasi_skala_probabilitas').val(skalaProbabilitas.tingkat).trigger('change');
            // Isi hidden input
            $('#realisasi_skala_probabilitas_hidden').val(skalaProbabilitas.tingkat);
        }

        // const $input = $(this);
        // let currentValue = parseFloat($input.val());
        // if (isNaN(currentValue)) return;

        // const $scaleSelect = $('#realisasi_skala_probabilitas');
        // const $selectedOption = $scaleSelect.find('option:selected');

        // // Jika belum pilih parameter, skip validasi range spesifik (atau paksa user pilih dulu)
        // if (!$selectedOption.val()) return;

        // const min = parseFloat($selectedOption.data('min'));
        // const max = parseFloat($selectedOption.data('max'));

        // let correctedValue = null;

        // if (!isNaN(min) && currentValue < min) correctedValue = min;
        // if (!isNaN(max) && currentValue > max) correctedValue = max;

        // if (correctedValue !== null) {
        //     Swal.fire({
        //         title: 'Peringatan!',
        //         text: `Nilai probabilitas untuk parameter ini harus berada di antara ${min}% dan ${max}%. Nilai otomatis disesuaikan.`,
        //         icon: 'warning',
        //         confirmButtonText: 'OK'
        //     });
        //     $input.val(correctedValue).trigger('change');
        // }
    });

    $('#section-realisasi').on('change', '.update-trigger', function() {
        refreshSkalaAndLevelRisiko();
    }).change();

    // Toggle logic for log section
    $('#logPerlakuanRisiko').on('show.bs.collapse', function () {
        const toggle = $(this).prev('.divider').find('.toggle-text');
        toggle.html("<i class='bx bx-chevron-up'></i> Hide Log");
    }).on('hide.bs.collapse', function () {
        const toggle = $(this).prev('.divider').find('.toggle-text');
        toggle.html("<i class='bx bx-chevron-down'></i> Show Log");
    });

    $('.btn-action').on('click', function() {
        const action = $(this).data('action');
        if (action === 'save' || action === 'save-and-close') {
            // validasi terlebih dahulu
            if (!validateRealisasiForm()) {
                return;
            }

            const isClosing = (action === 'save-and-close');
            const swalConfig = {
                title: 'Konfirmasi',
                text: isClosing
                    ? `Apakah Anda yakin ingin menyimpan dan menutup risiko "${namaRisiko}" ini?`
                    : 'Apakah Anda yakin ingin menyimpan data ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
            };

            Swal.fire(swalConfig).then((result) => {
                if (result.isConfirmed) {
                    submitForm(isClosing ? 1 : 0);
                }
            });
        } else if (action === 'update-kri') {
            const kriId = $(this).data('id');
            const kriProject = kriProjects[kriId];

            if (previousMonitoring) {
                const prevKriData = previousMonitoring.kri_proyek_monitorings?.find(k => k.kri_project_id == kriId);

                $('#info_prev_kri_period').html(`Periode: <strong>${monthNamesArray[previousMonitoring.month]} ${previousMonitoring.tahun}</strong>`);

                if (prevKriData) {
                    $('#container_prev_kri_data').removeClass('d-none');
                    $('#container_prev_kri_empty').addClass('d-none');

                    $('#info_prev_kri_value').text(prevKriData.nilai_kri_terkini || '-');

                    // Set Status KRI Badge
                    let statusLabel = '-';
                    let badgeClass = 'gray';
                    if (prevKriData.status_kri_terkini == '1') { statusLabel = 'Aman'; badgeClass = 'success'; }
                    else if (prevKriData.status_kri_terkini == '2') { statusLabel = 'Waspada'; badgeClass = 'warning'; }
                    else if (prevKriData.status_kri_terkini == '3') { statusLabel = 'Bahaya'; badgeClass = 'danger'; }

                    $('#info_prev_kri_status').html(`<span class="badge bg-${badgeClass}">${statusLabel}</span>`);
                } else {
                    $('#container_prev_kri_data').addClass('d-none');
                    $('#container_prev_kri_empty').removeClass('d-none');
                }
            } else {
                $('#info_prev_kri_period').html(`-`);
                $('#container_prev_kri_data').addClass('d-none');
                $('#container_prev_kri_empty').removeClass('d-none');
            }

            $('#modalUpdateKri input[name="kri_project_id"]').val($(this).data('id'));
            $('#modalUpdateKri input[name="key_risk_indicator"]').val(kriProject.kri);
            $('#modalUpdateKri input[name="batas_aman"]').val(kriProject.batas_aman);
            $('#modalUpdateKri input[name="batas_waspada"]').val(kriProject.batas_waspada);
            $('#modalUpdateKri input[name="batas_bahaya"]').val(kriProject.batas_bahaya);
            $('#modalUpdateKri input[name="nilai_kri"]').val(kriProject.nilai_kri_terkini);
            $('#modalUpdateKri :input[name="status_kri"]').val(kriProject.status_kri_terkini);
            $('#modalUpdateKri').modal('show');
        } else if (action === 'update-realisasi') {
            const perlakuanPenyebab = perlakuanPenyebabRisikos[$(this).data('id')];
            if (!perlakuanPenyebab) {
                Swal.fire('Error', 'Data perlakuan penyebab risiko tidak ditemukan', 'error');
                return;
            }
            const penyebabRisiko = penyebabRisikoProjects[perlakuanPenyebab.penyebab_risiko_id];
            $('#modalUpdateRealisasi :input[name="penyebab_risiko_id"]').val($(this).data('id'));
            $('#modalUpdateRealisasi :input[name="penyebab_risiko"]').val(penyebabRisiko.penyebab_risiko);

            if (projectRisk.perkiraan_waktu_terpapar_risiko_mulai) {
              timeline1.setDate(dayjs(projectRisk.perkiraan_waktu_terpapar_risiko_mulai).format('DD/MM/YYYY'));
            }

            if (projectRisk.perkiraan_waktu_terpapar_risiko_akhir) {
              timeline2.setDate(dayjs(projectRisk.perkiraan_waktu_terpapar_risiko_akhir).format('DD/MM/YYYY'));
            }

            if (perlakuanPenyebab.timeline_perlakuan_risiko_start) {
              perlakuanWaktu1.setDate(dayjs(perlakuanPenyebab.timeline_perlakuan_risiko_start).format('DD/MM/YYYY'));
            }

            if (perlakuanPenyebab.timeline_perlakuan_risiko_end) {
              perlakuanWaktu2.setDate(dayjs(perlakuanPenyebab.timeline_perlakuan_risiko_end).format('DD/MM/YYYY'));
            }

            $('#modalUpdateRealisasi :input[name="rencana_perlakuan_risiko"]').val(perlakuanPenyebab.rencana_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="biaya_perlakuan_risiko"]').val(perlakuanPenyebab.biaya_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="pic"]').val(perlakuanPenyebab.pic);
            // $('#modalUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val(perlakuanPenyebab.realisasi_biaya_perlakuan_risiko === null || perlakuanPenyebab.realisasi_biaya_perlakuan_risiko === '' ? perlakuanPenyebab.biaya_perlakuan_risiko : perlakuanPenyebab.realisasi_biaya_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val(perlakuanPenyebab.realisasi_biaya_perlakuan_risiko ?? 0);
            $('#modalUpdateRealisasi :input[name="progress_perlakuan_risiko"]').val(perlakuanPenyebab.progress_rencana_perlakuan_risiko);
            // $('#modalUpdateRealisasi :input[name="jenis_program_rkap"]').val(perlakuanPenyebab.jenis_program_rkap);
            // $('#modalUpdateRealisasi :input[name="jenis_program_rkap_id"]').val(perlakuanPenyebab.jenis_program_rkap_id);
            $('#modalUpdateRealisasi :input[name="deskripsi_perlakuan_risiko"]').val(perlakuanPenyebab.deskripsi_perlakuan_risiko);
            if (perlakuanPenyebab.timeline_perlakuan_risiko?.length === 2) {
                $("#timelineInput").data('_flatpickr').setDate(perlakuanPenyebab.timeline_perlakuan_risiko[0]);
            } else if (perlakuanPenyebab.timeline_perlakuan_risiko) {
                $("#timelineInput").data('_flatpickr').setDate(perlakuanPenyebab.timeline_perlakuan_risiko);
            } else {
                $("#timelineInput").data('_flatpickr').clear();
            }

            $('#modalUpdateRealisasi').modal('show');

            const tableDocument = $('#modalUpdateRealisasi .table-dokumen');

            tableDocument.empty();

            const domCell = $('#table-penyebab-risiko tr[data-id="'+$(this).data('id')+'"] td.column-action');
            const domSaved = domCell.find('.dom-saved');
            domCell.find('.dom-edited').remove();
            const domEdited = domSaved.clone().addClass('dom-edited').removeClass('dom-saved');
            const originalFileInputs = domSaved.find('input[type="file"]');
            domEdited.find('input[type="file"]').each(function(index) {
                if (originalFileInputs[index].files && originalFileInputs[index].files.length > 0) {
                    this.files = originalFileInputs[index].files;
                }
            });

            domCell.append(domEdited);

            const documentDescriptions = domEdited.find('.input-file-description').val() ? JSON.parse(domEdited.find('.input-file-description').val()) : {};
            domEdited.find('input[type=file]').each(function() {
                const fileName = $(this).prop('files')[0]?.name;
                const id = $(this).prop('id');
                const description = documentDescriptions[id] || '';
                const appended = tableDocument.append(`
                    <tr data-id="${id}">
                    <td>
                        <span class="dokumen-filename">${fileName}</span>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="deskripsi_dokumen[]" placeholder="Deskripsi dokumen" value="${description}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-link btn-sm text-danger delete-btn">Hapus</button>
                    </td>
                </tr>
                `);
            });

            if (tableDocument.find('tr').length > 2) {
                tableDocument.closest('table').find('tfoot').hide();
            } else {
                tableDocument.closest('table').find('tfoot').show();
            }

            tableDocument.off('input', '[name="deskripsi_dokumen[]"]').on('input', '[name="deskripsi_dokumen[]"]', function() {
                const value = $(this).val();
                const rowId = $(this).closest('tr').data('id');
                const penyebabRisikoId = $('#formUpdateRealisasi :input[name="penyebab_risiko_id"]').val();
                const domCell = $('#table-penyebab-risiko tr[data-id="'+penyebabRisikoId+'"] td.column-action');
                const domEdited = domCell.find('.dom-edited');

                const domDeskripsi = domEdited.find('.input-file-description');
                const deskripsi = domDeskripsi.val() ? JSON.parse(domDeskripsi.val()) : {};
                deskripsi[rowId] = value;
                domDeskripsi.val(JSON.stringify(deskripsi));
            });

            tableDocument.off('click', '.delete-btn').on('click', '.delete-btn', function() {
                const rowId = $(this).closest('tr').data('id');
                const penyebabRisikoId = $('#formUpdateRealisasi :input[name="penyebab_risiko_id"]').val();
                const domCell = $('#table-penyebab-risiko tr[data-id="'+penyebabRisikoId+'"] td.column-action');
                const domEdited = domCell.find('.dom-edited');

                $(this).closest('tr').remove();
                domEdited.find(`#${rowId}`).remove();

                const domDeskripsi = domEdited.find('.input-file-description');
                const deskripsi = domDeskripsi.val() ? JSON.parse(domDeskripsi.val()) : {};
                delete deskripsi[rowId];
                domDeskripsi.val(JSON.stringify(deskripsi));

                if (tableDocument.find('tr').length < 3) {
                    tableDocument.closest('table').find('tfoot').show();
                }
            });

            const savedPenyebabDocs = existingPenyebabDocs[perlakuanPenyebab.id] || [];
              savedPenyebabDocs.forEach(function(doc) {
                  tableDocument.append(`
                      <tr class="existing-doc" data-doc-id="${doc.id}">
                          <td><span class="text-primary text-truncate d-block" style="max-width: 200px;"><a href="${doc.url}" target="_blank">${doc.file_name}</a></span></td>
                          <td><input type="text" class="form-control form-control-sm" value="${doc.description || ''}" disabled></td>
                          <td>
                              <button type="button" class="btn btn-sm btn-danger btn-delete-db-doc" data-id="${doc.id}" data-type="penyebab">
                                  <i class="bx bx-trash"></i> Hapus
                              </button>
                          </td>
                      </tr>
                  `);
            });

            // --- POPULATE PREVIOUS DATA UI ---
            if (previousMonitoring) {
                const prevData = previousMonitoring.perlakuan_penyebab_monitorings?.find(m => m.perlakuan_penyebab_id == perlakuanPenyebab.id);

                $('#info_prev_penyebab_period').html(`Periode: <strong>${monthNamesArray[previousMonitoring.month]} ${previousMonitoring.tahun}</strong>`);

                if (prevData) {
                    $('#container_prev_penyebab_data').removeClass('d-none');
                    $('#container_prev_penyebab_empty').addClass('d-none');

                    $('#info_prev_penyebab_progress').text((prevData.progress_rencana_perlakuan_risiko || 0) + '%');
                    $('#info_prev_penyebab_cost').text('Rp ' + Intl.NumberFormat('id-ID').format(prevData.realisasi_biaya_perlakuan_risiko || 0));
                    $('#info_prev_penyebab_date').text(prevData.timeline_perlakuan_risiko_start ? dayjs(prevData.timeline_perlakuan_risiko_start).format('DD/MM/YYYY') : '-');
                    $('#info_prev_penyebab_notes').text(prevData.deskripsi_perlakuan_risiko || '-');

                    // Ambil dan buat list dokumen evidence
                    const prevDocs = previousMonitoring.perlakuan_penyebab_risiko_documents?.filter(d => d.perlakuan_penyebab_risiko_id == perlakuanPenyebab.id) || [];
                    let docsHtml = prevDocs.length > 0
                        ? '<ul class="ps-3 mb-0">' + prevDocs.map(d => `<li><a href="/storage/${d.file_path}" target="_blank" class="text-primary">${d.file_name}</a> <small class="text-muted">(${d.description || '-'})</small></li>`).join('') + '</ul>'
                        : '<em class="text-muted small">Tidak ada dokumen evidence.</em>';
                    $('#info_prev_penyebab_evidence').html(docsHtml);
                } else {
                    $('#container_prev_penyebab_data').addClass('d-none');
                    $('#container_prev_penyebab_empty').removeClass('d-none');
                }
            } else {
                $('#info_prev_penyebab_period').html(`-`);
                $('#container_prev_penyebab_data').addClass('d-none');
                $('#container_prev_penyebab_empty').removeClass('d-none');
            }

            $('#modalUpdateRealisasi').modal('show');
        } else if (action === 'view-details') {
            const id = $(this).data('id');
            const perlakuanId = $(this).data('perlakuan-id');
            const perlakuanPenyebab = perlakuanPenyebabRisikos[perlakuanId];
            const penyebabRisiko = penyebabRisikoProjects[perlakuanPenyebab.penyebab_risiko_id];
            const perlakuanMonitoring = perlakuanPenyebab?.perlakuan_penyebab_monitorings?.find(m => m.id == id);

            if (!perlakuanMonitoring) {
                Swal.fire('Error', 'Data perlakuan penyebab risiko tidak ditemukan', 'error');
                return;
            }

            $('#modalMitigasi :input[name="penyebab_risiko_id"]').val(perlakuanPenyebab.id);
            $('#modalMitigasi :input[name="penyebab_risiko"]').val(penyebabRisiko.penyebab_risiko);
            $('#modalMitigasi :input[name="rencana_perlakuan_risiko"]').val(perlakuanPenyebab.rencana_perlakuan_risiko);
            $('#modalMitigasi :input[name="biaya_perlakuan_risiko"]').val(perlakuanPenyebab.biaya_perlakuan_risiko);
            $('#modalMitigasi :input[name="pic"]').val(perlakuanPenyebab.pic);

            $('#modalMitigasi :input[name="realisasi_biaya_perlakuan_risiko"]').val(perlakuanMonitoring.realisasi_biaya_perlakuan_risiko);
            $('#modalMitigasi :input[name="progress_perlakuan_risiko"]').val(perlakuanMonitoring.progress_rencana_perlakuan_risiko);
            $('#modalMitigasi :input[name="deskripsi_perlakuan_risiko"]').val(perlakuanMonitoring.deskripsi_perlakuan_risiko);
            // $('#modalMitigasi :input[name="jenis_program_rkap_id"]').val(perlakuanMonitoring.jenis_program_rkap_id).change();
            $('#modalMitigasi :input[name="timeline_perlakuan_risiko"]').val(perlakuanMonitoring.timeline_perlakuan_risiko_start ? Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }).format(new Date(perlakuanMonitoring.timeline_perlakuan_risiko_start)) : '');

            const perlakuanDocuments = perlakuanPenyebab.documents.filter(d => d.project_monitoring_id == perlakuanMonitoring.project_monitoring_id);

            $('.tabel-dokumen-mitigasi tbody').empty();
            perlakuanDocuments.forEach(function(document) {
                const appended = $('.tabel-dokumen-mitigasi tbody').append(`
                    <tr>
                        <td>
                            ${document.file_name}
                        </td>
                        <td>
                            ${document.description || '-'}
                        </td>
                        <td>
                            <a href="${document.url}" download="${document.file_name}">Download</a>
                        </td>
                    </tr>
                `);
            });

            if (perlakuanDocuments.length === 0) {
                $('.tabel-dokumen-mitigasi tbody').append(`
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada dokumen</td>
                    </tr>
                `);
            }

            $('#modalMitigasi').modal('show');
        } else if (action === 'update-realisasi-dampak') {
            // // 1. Ambil ID perlakuan dampak dari tombol yang diklik
            // const id = $(this).data('id');

            // // 2. Definisikan elemen tabel tempat dokumen akan di-render
            // const tableDocument = $('#modalUpdateRealisasiDampak .table-dokumen-dampak');

            // // Kosongkan tabel dulu jika sebelumnya ada isinya (mencegah duplikat saat buka-tutup modal)
            // tableDocument.empty();

            // // 3. Render dokumen dari database
            // const savedDocs = existingDampakDocs[id] || [];
            // savedDocs.forEach(function(doc) {
            //     tableDocument.append(`
            //         <tr class="existing-doc" data-doc-id="${doc.id}">
            //             <td><span class="text-primary text-truncate d-block" style="max-width: 200px;"><a href="${doc.url}" target="_blank">${doc.file_name}</a></span></td>
            //             <td><input type="text" class="form-control form-control-sm" value="${doc.description || ''}" disabled></td>
            //             <td>
            //                 <button type="button" class="btn btn-sm btn-danger btn-delete-db-doc" data-id="${doc.id}" data-type="dampak">
            //                     <i class="bx bx-trash"></i> Hapus
            //                 </button>
            //             </td>
            //         </tr>
            //     `);
            // });
        }
    });

    // --- PENYEBAB RISIKO LOGIC ---
    $('#btnTambahDokumen').click(function() {
        const penyebabRisikoId = $('#formUpdateRealisasi :input[name="penyebab_risiko_id"]').val();
        const domEdited = $('#table-penyebab-risiko tr[data-id="'+penyebabRisikoId+'"] td.column-action .dom-edited');
        const uploadContainer = domEdited.find('.upload-container');
        const tableDokumen = $('#modalUpdateRealisasi .table-dokumen');

        // Poin 2: Batasan 10 Dokumen
        const currentCount = tableDokumen.find('tr').length;
        if (currentCount >= maxFilesCount) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: `Maksimal ${maxFilesCount} dokumen yang dapat diunggah.` });
            return;
        }

        const newId = 'dokumen-' + Date.now() + '-' + Math.floor(Math.random() * 1000);
        // Poin 2: Filter Accept extension
        uploadContainer.append(`<input type="file" style="display:none;" accept="${acceptedFiles}" name="document_file_${penyebabRisikoId}[${newId}]" id="${newId}" required>`);

        tableDokumen.append(`
            <tr data-id="${newId}">
                <td><span class="dokumen-filename text-truncate d-block" style="max-width: 200px;">Pilih file...</span></td>
                <td><input type="text" class="form-control" name="deskripsi_dokumen[]" placeholder="Deskripsi dokumen"></td>
                <td><button type="button" class="btn btn-sm btn-danger delete-btn"><i class="bx bx-trash"></i> Batal</button></td>
            </tr>
        `);

        const fileInput = domEdited.find(`#${newId}`);

        fileInput.change(function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                // Poin 2: Batasan Ukuran 10MB
                if (file.size > maxFileSize) {
                    Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Ukuran file maksimal adalah 10 MB.' });
                    tableDokumen.find(`tr[data-id="${newId}"]`).remove();
                    $(this).remove();
                    return;
                }
                tableDokumen.find(`tr[data-id="${newId}"] .dokumen-filename`).text(file.name);
            }
        });

        fileInput.click();
    });

    $('#btnSimpanUpdateRealisasi').on('click', function() {
        if (!validateFormSweetAlert('formUpdateRealisasi')) return; // Poin 5

        const id = $('#formUpdateRealisasi :input[name="penyebab_risiko_id"]').val();

        // Set data ke memory
        perlakuanPenyebabRisikos[id]['realisasi_biaya_perlakuan_risiko'] = $('#formUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').inputmask('unmaskedvalue') || 0;
        perlakuanPenyebabRisikos[id]['progress_rencana_perlakuan_risiko'] = $('#formUpdateRealisasi :input[name="progress_perlakuan_risiko"]').val();
        perlakuanPenyebabRisikos[id]['deskripsi_perlakuan_risiko'] = $('#formUpdateRealisasi :input[name="deskripsi_perlakuan_risiko"]').val();
        perlakuanPenyebabRisikos[id]['timeline_perlakuan_risiko'] = $('#timelineInput').val();

        // Update DOM table belakang
        const tr = $('#table-penyebab-risiko tr[data-id="' + id + '"]');
        tr.find('.display-biaya').text('Rp ' + Intl.NumberFormat('id-ID').format(perlakuanPenyebabRisikos[id]['realisasi_biaya_perlakuan_risiko']));
        tr.find('.display-progress').text(perlakuanPenyebabRisikos[id]['progress_rencana_perlakuan_risiko']);
        tr.find('.display-timeline').text(perlakuanPenyebabRisikos[id]['timeline_perlakuan_risiko']);

        tr.find('.dom-saved').remove();
        tr.find('.dom-edited').removeClass('dom-edited').addClass('dom-saved');

        $('#modalUpdateRealisasi').modal('hide');
        Swal.fire({ icon: 'success', title: 'Tersimpan', text: 'Data realisasi sementara disimpan. Jangan lupa klik tombol Simpan utama.', timer: 2000, showConfirmButton: false });
    });

    $('#btnSimpanUpdateKri').on('click', function() {
        if (!$('#formUpdateKri')[0].checkValidity()) {
            $('#formUpdateKri')[0].reportValidity();
            return;
        }

        const id = $('#formUpdateKri :input[name="kri_project_id"]').val();
        const nilaiKri = $('#formUpdateKri :input[name="nilai_kri"]').val();
        const statusKri = $('#formUpdateKri :input[name="status_kri"]').val();
        const statusMap = {
            '1': 'Aman',
            '2': 'Waspada',
            '3': 'Bahaya'
        };
        const statusText = statusMap[statusKri] || '-';

        // update kri
        kriProjects[id]['nilai_kri_terkini'] = nilaiKri;
        kriProjects[id]['status_kri_terkini'] = statusKri;

        // update DOM
        const tr = $('#table-kri tr[data-id="' + id + '"]');
        tr.find('.display-nilai-kri').text(nilaiKri);
        tr.find('.display-kondisi').text(statusText);

        $('#modalUpdateKri').modal('hide');
    });

    const inputmaskFixeds = $('.inputmask-fixed');
    inputmaskFixeds.each(function() {
        if (!isNaN($(this).text())) {
            $(this).inputmask({
                alias: 'numeric',
                groupSeparator: '.',
                autoGroup: true,
                digits: 0,
                digitsOptional: false,
                placeholder: '0',
                rightAlign: false,
                min: 0,
                allowMinus: false,
                onKeyDown: function(e) {
                if (e.key === 'Backspace' || e.keyCode === 8) {
                    // tunda eksekusi sampai mask selesai di-apply
                    setTimeout(() => {
                        const unmasked = this.inputmask.unmaskedvalue();
                        // kalau masih ada angka tersisa
                        if (unmasked.length > 0) {
                        // cek posisi cursor
                        const pos = this.selectionStart;
                        if (pos === 0) {
                            // pindahkan ke paling kanan
                            const end = this.value.length;
                            this.setSelectionRange(end, end);
                        }
                        }
                    }, 0);
                    }
                }
            });
        }
    });

    $('.inputmask-rupiah').inputmask({
        alias: 'numeric',
        groupSeparator: '.',
        autoGroup: true,
        digits: 0,
        digitsOptional: false,
        prefix: 'Rp ',
        placeholder: '0',
        rightAlign: false,
        autoUnmask: true,
        removeMaskOnSubmit: true,
        min: 0,
        allowMinus: false,
        onKeyDown: function(e) {
        if (e.key === 'Backspace' || e.keyCode === 8) {
            // tunda eksekusi sampai mask selesai di-apply
            setTimeout(() => {
                const unmasked = this.inputmask.unmaskedvalue();
                // kalau masih ada angka tersisa
                if (unmasked.length > 0) {
                // cek posisi cursor
                const pos = this.selectionStart;
                if (pos === 0) {
                    // pindahkan ke paling kanan
                    const end = this.value.length;
                    this.setSelectionRange(end, end);
                }
                }
            }, 0);
            }
        }
    });

    // Fungsi untuk menghitung skala dampak berdasarkan persentase
    function hitungSkalaDampak(percentage) {
        if (percentage <= 20) return 1;
        if (percentage <= 40) return 2;
        if (percentage <= 60) return 3;
        if (percentage <= 80) return 4;
        return 5;
    }

    // Fungsi untuk menghitung dan mengatur realisasi_skala_dampak
    function hitungRealisasiSkalaDampak() {
        const kategoriDampak = '{{ $projectRiskAnalisa->kategori_dampak }}';

        // const nilaiDampak = parseFloat($('#realisasi_nilai_dampak').val()) || 0;
        let rawValue = $('#realisasi_nilai_dampak').inputmask('unmaskedvalue');
        // Fallback jika inputmask belum init atau kosong, replace karakter non-digit manual
        if (!rawValue && rawValue !== 0) {
            rawValue = $('#realisasi_nilai_dampak').val().replace(/[^0-9,-]+/g,"").replace(",",".");
        }
        const nilaiDampak = parseFloat(rawValue) || 0;

        const riskLimit = parseFloat('{{ $risk_limit }}') || 0;
        const skalaDampakSelect = $('#realisasi_skala_dampak');
        const skalaDampakHidden = $('#realisasi_skala_dampak_hidden');

        if (kategoriDampak === 'Kuantitatif') {
            // console.log("risk limit : " + riskLimit);
            var percentage = 100;
            var skala = 5;

            if(riskLimit>0){
                // Hitung persentase
                percentage = (nilaiDampak / riskLimit) * 100;
                // Hitung skala berdasarkan persentase
                skala = hitungSkalaDampak(percentage);

                // console.log("percentage : " + percentage);
                // console.log("skala : " + skala);
            }

            // Set nilai skala dampak dan trigger change event
            skalaDampakSelect.val(skala).trigger('change');
            // Disable select dan pindahkan nilai ke hidden input
            // skalaDampakSelect.prop('disabled', true);
            skalaDampakSelect.prop('disabled', false);
            skalaDampakHidden.val(skala);
        } else {
            // Enable select jika bukan Kuantitatif
            skalaDampakSelect.prop('disabled', false);
            // Kosongkan hidden input
            skalaDampakHidden.val('0');
        }
    }

    // Event listener untuk perubahan nilai dampak
    $('#realisasi_nilai_dampak').on('keyup change', function() {
        // console.log("hitung skala dampak");
        hitungRealisasiSkalaDampak();
        refreshSkalaAndLevelRisiko();
    });

    $('.datatable').DataTable({
        paging: true,
        info: false,
        searching: false,
        ordering: true,
        autoWidth: true,
        order: [[2, 'desc']],
        rowCallback: function(row, data, index) {
            $('td:eq(0)', row).html(index + 1); // Assign row index
        },
        columnDefs: [
            {
                width: '5%',
                targets: 0,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return meta.row + 1;
                }
            },
            {
                width: '15%',
                targets: 2,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return data ? new Date(data).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
                }
            },
            {
                width: '15%',
                targets: 3,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return data ? new Date(data).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
                }
            },
            {
                width: '10%',
                targets: 6,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return 'Rp' + Intl.NumberFormat('id-ID').format(data);
                }
            },
            {
                width: '10%',
                targets: 8,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return 'Rp' + Intl.NumberFormat('id-ID').format(data);
                }
            },
            {
                width: '10%',
                targets: 9,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return Intl.NumberFormat('id-ID').format(data);
                }
            },
        ],
    });

    // Panggil fungsi saat halaman dimuat
    // hitungRealisasiSkalaDampak();
    refreshSkalaAndLevelRisiko();
});
</script>
@endpush
