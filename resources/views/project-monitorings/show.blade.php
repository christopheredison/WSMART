@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Monitor Risiko <small class="d-block mt-2">{{ $project->project_name }} - {{ $peristiwaRisiko->title }}</small></h3>
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
                    <h3 class="mb-0">Quarter {{ $quarter }} - {{ $tahun }}<br/>{{ __('basic.month.' . $month) }}</h3>
                    <input type="hidden" name="periode_monitoring" value="{{ $quarter }}">
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table" id="table-penyebab-risiko">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Penyebab Risiko</th>
                                <th>Rencana Perlakuan Risiko</th>
                                <th>Biaya Perlakuan Risiko</th>
                                <th>Progress Perlakuan Risiko</th>
                                <th>Realisasi Biaya Perlakuan Risiko</th>
                                <th>Waktu Perlakuan Risiko</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($penyebabRisikoProjects as $penyebabRisiko)
                                @php $rowSpan = $penyebabRisiko->perlakuanPenyebabRisiko->count() ?: 1 @endphp
                                <tr data-id="{{ ($penyebabRisiko->perlakuanPenyebabRisiko[0] ?? null)?->id }}">
                                    <td rowspan="{{ $rowSpan }}">{{ $loop->iteration }}</td>
                                    <td rowspan="{{ $rowSpan }}">{{ $penyebabRisiko->penyebab_risiko ?: '-' }}</td>
                                    <td>{{ ($penyebabRisiko->perlakuanPenyebabRisiko[0] ?? null)?->rencana_perlakuan_risiko ?: '-' }}</td>
                                    <td>
                                      <span class="inputmask-fixed">
                                        {{ isset(($penyebabRisiko->perlakuanPenyebabRisiko[0] ?? null)?->biaya_perlakuan_risiko) ? 'Rp ' . number_format($penyebabRisiko->perlakuanPenyebabRisiko[0]->biaya_perlakuan_risiko, 0, ',', '.') : '-' }}
                                      </span>
                                    </td>
                                    {{-- <td class="display-progress inputmask-fixed">{{ ($penyebabRisiko->perlakuanPenyebabRisiko[0] ?? null)?->{'progress_rencana_perlakuan_risiko_q' . $quarter } ?: '-' }}</td>
                                    <td class="display-biaya inputmask-fixed">{{ ($penyebabRisiko->perlakuanPenyebabRisiko[0] ?? null)?->{'realisasi_biaya_perlakuan_risiko_q' . $quarter } ?: '-' }}</td> --}}
                                    <td class="display-progress inputmask-fixed">
                                        {{ ($penyebabRisiko->perlakuanPenyebabRisiko[0] ?? null)?->last_monitoring?->progress_rencana_perlakuan_risiko ?? '-' }}
                                    </td>
                                    <td class="display-biaya inputmask-fixed">
                                        {{ ($penyebabRisiko->perlakuanPenyebabRisiko[0] ?? null)?->last_monitoring?->realisasi_biaya_perlakuan_risiko ?? '-' }}
                                    </td>
                                    <td class="display-timeline">{{ ($penyebabRisiko->perlakuanPenyebabRisiko[0]?? null)?->last_monitoring?->waktu_perlakuan_risiko?: '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-link lihat-file-btn" title="Lihat File">
                                            <i class='bx bx-file fs-5'></i>
                                        </button>
                                    </td>
                                </tr>
                                @if ($penyebabRisiko->perlakuanPenyebabRisiko->count() > 1)
                                    @foreach ($penyebabRisiko->perlakuanPenyebabRisiko as $perlakuan)
                                        @if ($loop->index == 0)
                                            @continue
                                        @endif
                                        <tr data-id="{{ $perlakuan->id }}">
                                            <td>{{ $perlakuan->rencana_perlakuan_risiko ?: '-' }}</td>
                                            <td><span class="inputmask-fixed">{{ $perlakuan->biaya_perlakuan_risiko ?: '-' }}</span></td>
                                            {{-- <td class="display-progress inputmask-fixed">{{ $perlakuan->{'progress_rencana_perlakuan_risiko_q' . $quarter } ?: '-' }}</td>
                                            <td class="display-biaya inputmask-fixed">{{ $perlakuan->{'realisasi_biaya_perlakuan_risiko_q' . $quarter } ?: '-' }}</td> --}}
                                            <td class="display-progress inputmask-fixed">
                                                {{ $perlakuan->last_monitoring?->progress_rencana_perlakuan_risiko ?? '-' }}
                                            </td>
                                            <td class="display-biaya inputmask-fixed">
                                                {{
                                                  $perlakuan?->last_monitoring?->realisasi_biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->last_monitoring->realisasi_biaya_perlakuan_risiko, 0, ',', '.') : '-'
                                                }}
                                            </td>
                                            <td class="display-timeline">{{ $perlakuan?->last_monitoring?->waktu_perlakuan_risiko?: '-' }}</td>
                                            <td>
                                                <!-- <button type="button" class="btn btn-sm btn-link lihat-file-btn">Lihat File</button> -->
                                                <button type="button" class="btn btn-sm btn-link lihat-file-btn" title="Lihat File">
                                                    <i class='bx bx-file fs-5'></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach

                            @if($penyebabRisikoProjects->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <table class="table mt-7" id="table-kri">
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
                                    {{-- <td class="display-nilai-kri">{{ $kriProject->{'nilai_kri_terkini_q' . $quarter} ?: '-' }}</td>
                                    <td class="display-kondisi">{{ $kriProject->{'status_kri_terkini_q' . $quarter} ?: '-' }}</td> --}}
                                    <td class="display-nilai-kri">
                                        {{ $kriProject->last_monitoring?->nilai_kri_terkini ?? '-' }}
                                    </td>
                                    <td class="display-kondisi">
                                        @php
                                            $statusMap = [
                                                1 => 'Aman',
                                                2 => 'Waspada',
                                                3 => 'Bahaya',
                                            ];
                                            $status = $kriProject->last_monitoring?->status_kri_terkini;
                                            $displayStatus = $statusMap[$status] ?? '-';
                                        @endphp
                                        {{ $displayStatus }}
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
                            <h5 class="text-muted fw-bold mb-4 small text-uppercase text-center">Nilai Threshold</h5>
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
                                    <div class="p-3 bg-light rounded border-primary border">
                                        <strong>Rp {{ number_format($riskMonitoring->aktual_current ?? 0, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small">Bulan -1 ({{ $dateM1->translatedFormat('F Y') }})</label>
                                    <div class="p-3 bg-light rounded border italic text-muted">
                                        Rp {{ number_format($riskMonitoring->aktual_month_1 ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted small">Bulan -2 ({{ $dateM2->translatedFormat('F Y') }})</label>
                                    <div class="p-3 bg-light rounded border italic text-muted">
                                        Rp {{ number_format($riskMonitoring->aktual_month_2 ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Status Hasil Monitoring</label>
                                    @php
                                        $status = $riskMonitoring->aktual_status ?? 'N/A';
                                        $color = 'secondary';
                                        if($status == 'Aman') $color = 'success';
                                        if($status == 'Siaga') $color = 'warning';
                                        if($status == 'Bahaya') $color = 'danger';
                                    @endphp
                                    <div class="p-2 rounded text-center fw-bold fs-6 border bg-{{ $color }}-subtle text-{{ $color == 'warning' ? 'dark' : $color }} border-{{ $color }}">
                                        {{ strtoupper($status) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($riskMonitoring && $riskMonitoring->pengendalians->isNotEmpty())
        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm text-danger">Rencana Pengendalian Risiko</h4>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-danger">
                        <div class="card-body">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="30%">Parameter Risiko</th>
                                        <th>Rencana Pengendalian</th>
                                        <th>Realisasi Pengendalian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($riskMonitoring->pengendalians as $pengendalian)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="bg-light"><strong>{{ $pengendalian->parameter->nama ?? '-' }}</strong></td>
                                        <td>{{ $pengendalian->rencana_pengendalian ?? '-' }}</td>
                                        <td>{{ $pengendalian->realisasi_pengendalian ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm">Nilai Risiko Residual Realisasi</h4>
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
                                value="{{ $projectRisk->projectRiskAnalisa?->nilai_dampak ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak, 0, ',', '.') : '-' }}">
                                <label class="form-label" for="">Nilai Dampak Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_dampak_inherent"
                                value="{{ $projectRiskAnalisa->skalaDampakObj ? $projectRiskAnalisa->skalaDampakObj->tingkat . ' - ' . $projectRiskAnalisa->skalaDampakObj->deskripsi : '-' }}">
                                <label for="">Skala Dampak Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text"
                                value="{{ $projectRiskAnalisa->skalaParameterObj ? $projectRiskAnalisa->skalaParameterObj->type_parameter . ' - ' . $projectRiskAnalisa->skalaParameterObj->skala : '-' }}">
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
                                value="{{ $projectRiskAnalisa->skalaParameterResidualObj ? $projectRiskAnalisa->skalaParameterResidualObj->type_parameter . ' - ' . $projectRiskAnalisa->skalaParameterResidualObj->skala : '-' }}">
                                <label>Target Parameter Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_nilai_probabilitas"
                                name="target_nilai_probabilitas" value="{{ $projectRiskAnalisa->nilai_probabilitas_residual }}">
                                <label for="">Target Nilai Probabilitas (%)</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_probabilitas_inherent"
                                value="{{ $projectRiskAnalisa->skalaProbabilitasResidual?->tingkat." - " .$projectRiskAnalisa->skalaProbabilitasResidual?->skala }}">
                                <label for="">Target Skala Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_skala_risiko"
                                name="target_skala_risiko" value="{{ $projectRiskAnalisa->skala_risiko_residual }}">
                                <label for="">Target Skala Risiko</label>
                            </div>
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
                                <input class="form-control update-trigger" type="text" id="realisasi_nilai_dampak" name="realisasi_nilai_dampak"
                                value="{{ isset($riskMonitoring?->nilai_dampak) ? 'Rp ' . number_format($riskMonitoring->nilai_dampak, 0, ',', '.') : '-' }}" disabled>
                                <label for="">Realisasi Nilai Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control update-trigger" type="text" id="realisasi_skala_dampak" name="realisasi_skala_dampak"
                                value="{{ $riskMonitoring?->skalaDampakObj?->tingkat . ' - ' . $riskMonitoring?->skalaDampakObj?->deskripsi }}" disabled>
                                <label for="">Realisasi Skala Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text"
                                value="{{ $riskMonitoring?->skalaParameter ? $riskMonitoring->skalaParameter->type_parameter . ' - ' . $riskMonitoring->skalaParameter->skala : '-' }}">
                                <label>Realisasi Parameter Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control update-trigger" type="number" id="realisasi_nilai_probabilitas"
                                name="realisasi_nilai_probabilitas" value="{{ $riskMonitoring?->nilai_probabilitas }}" disabled>
                                <label for="">Realisasi Nilai Probabilitas (%)</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control" name="realisasi_skala_probabilitas" type="text" placeholder=""
                                value="{{ $riskMonitoring?->skalaProbabilitas?->tingkat }} - {{ $riskMonitoring?->skalaProbabilitas?->skala }}" id="realisasi_skala_probabilitas"
                                readonly />
                                <label for="">Realisasi Skala Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control" type="text" name="realisasi_skala_risiko" id="realisasi_skala_risiko" placeholder=""
                                value="{{ $riskMonitoring?->skala_risiko }}" readonly>
                                <label for="">Realisasi Skala Risiko</label>
                            </div>
                            <div class="form-group pt-3">
                                <p>Realisasi Level Risiko: <span class="ff-heading fw-medium">{{ $riskMonitoring?->level_risiko }}</span>
                                </p>
                                @php $lowerLevel = strtolower($riskMonitoring?->level_risiko) @endphp
                                <label
                                class=" radio-label low-label {{ $lowerLevel == 'low' ? 'active' : '' }}"
                                for="low"></label>
                                <label
                                class="radio-label low-medium-label {{ $lowerLevel == 'low to moderate' ? 'active' : '' }}"
                                for="low-medium"></label>
                                <label
                                class="radio-label medium-label {{ $lowerLevel == 'moderate' ? 'active' : '' }}"
                                for="medium"></label>
                                <label
                                class="radio-label medium-high-label {{ $lowerLevel == 'moderate to high' ? 'active' : '' }}"
                                for="medium-high"></label>
                                <label
                                class="radio-label high-label {{ $lowerLevel == 'high' ? 'active' : '' }}"
                                for="high"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal Lihat File -->
    <div class="modal fade" id="modalLihatFile" tabindex="-1" aria-labelledby="modalLihatFileLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLihatFileLabel">Lihat File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="divider my-3 my-md-5">
            <div class="divider-text">
                <h4 class="mb-0 ff-heading-sm">Log Perlakuan Risiko</h4>
            </div>
        </div>
        <div class="row g-2">
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
                                <th>Anggaran (Rp)</th>
                                <th>Deskripsi Perlakuan Risiko</th>
                                <th>Realisasi Anggaran (Rp)</th>
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

    @include('project-monitorings._modal_mitigasi')
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
const penyebabRisikoProjects = @json($penyebabRisikoProjects->keyBy('id'));
const perlakuanPenyebabRisikos = @json($penyebabRisikoProjects->pluck('perlakuanPenyebabRisiko')->flatten()->keyBy('id'));
const kriProjects = @json($kriProjects->keyBy('id'));
const quarter = {{ $quarter }};
function getSkalaProbabilitasByValue(value) {
    const skalaProbabilitases = @json($skalaProbabilitas);
    for (index in skalaProbabilitases) {
        skalaProbabilitas = skalaProbabilitases[index];
        if (value >= skalaProbabilitas.min) {
            return skalaProbabilitas;
        }
    }
}
const riskMonitoring = @json($riskMonitoring);
console.log(riskMonitoring);

function refreshSkalaAndLevelRisiko() {
    const riskMaps = @json($riskMaps);
    const nilaiDampak = parseFloat(riskMonitoring?.nilai_dampak || 0);
    const nilaiProbabilitas = parseFloat(riskMonitoring?.nilai_probabilitas || 0);
    const skalaDampak = riskMonitoring?.skalaDampakObj?.tingkat || 0;
    const skalaProbabilitas = getSkalaProbabilitasByValue(nilaiProbabilitas);

    const domSkalaProbabilitas = $('#realisasi_skala_probabilitas');
    const domSkalaRisiko = $('#realisasi_skala_risiko');
    const domLevelRisiko = $('#realisasi_level_risiko');

    if (skalaProbabilitas) {
        domSkalaProbabilitas.val(skalaProbabilitas.tingkat + ' - ' + skalaProbabilitas.skala);
    } else {
        domSkalaProbabilitas.val('');
    }

    const riskMap = riskMaps[skalaDampak + '-' + (skalaProbabilitas?.tingkat)];
    if  (riskMap) {
        domSkalaRisiko.val(riskMap.nilai_risiko);
        domLevelRisiko.val(riskMap.level_risiko);
    } else {
        domSkalaRisiko.val('');
        domLevelRisiko.val('');
    }
}

$(document).ready(function() {
    $('#section-realisasi').on('change', '.update-trigger', function() {
        refreshSkalaAndLevelRisiko();
    }).change();

    $('.btn-action').on('click', function() {
        const action = $(this).data('action');
        if (action === 'save') {
            if (!$('#main-form')[0].checkValidity()) {
                $('#main-form')[0].reportValidity();
                return;
            }

            for (let i in perlakuanPenyebabRisikos) {
                let perlakuanPenyebabRisiko = perlakuanPenyebabRisikos[i];
                if (!perlakuanPenyebabRisiko.deskripsi_perlakuan_risiko) {
                    Swal.fire('Error', 'Semua update realisasi harus diisi', 'error');
                    return;
                }
            }

            for (let i in kriProjects) {
                let kriProject = kriProjects[i];
                if (!kriProject.status_kri_terkini_q{{$quarter}}) {
                    Swal.fire('Error', 'Semua update kri harus diisi', 'error');
                    return;
                }
            }

            const formData = new FormData($('#main-form')[0]);
            formData.append('perlakuan_penyebab_risikos', JSON.stringify(perlakuanPenyebabRisikos));
            formData.append('kri_projects', JSON.stringify(kriProjects));
            formData.append('quarter', quarter);
            formData.append('_method', 'PUT');
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
                        window.location.href = '{{ route('projects.monitorings.index', ['project' => request()->route('project')]) }}';
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan.';
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
        } else if (action === 'update-kri') {
            const kriProject = kriProjects[$(this).data('id')];
            $('#modalUpdateKri input[name="kri_project_id"]').val($(this).data('id'));
            $('#modalUpdateKri input[name="key_risk_indicator"]').val(kriProject.kri);
            $('#modalUpdateKri input[name="batas_aman"]').val(kriProject.batas_aman);
            $('#modalUpdateKri input[name="batas_waspada"]').val(kriProject.batas_waspada);
            $('#modalUpdateKri input[name="batas_bahaya"]').val(kriProject.batas_bahaya);
            $('#modalUpdateKri input[name="nilai_kri"]').val(kriProject['nilai_kri_terkini_q' + quarter]);
            $('#modalUpdateKri input[name="status_kri"]').val(kriProject['status_kri_terkini_q' + quarter]);
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
            $('#modalUpdateRealisasi :input[name="rencana_perlakuan_risiko"]').val(perlakuanPenyebab.rencana_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="biaya_perlakuan_risiko"]').val(perlakuanPenyebab.biaya_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="pic"]').val(perlakuanPenyebab.pic);
            $('#modalUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val(perlakuanPenyebab['realisasi_biaya_perlakuan_risiko_q' + quarter]);
            $('#modalUpdateRealisasi :input[name="progress_perlakuan_risiko"]').val(perlakuanPenyebab['progress_rencana_perlakuan_risiko_q' + quarter]);
            $('#modalUpdateRealisasi :input[name="jenis_program_rkap"]').val(perlakuanPenyebab.jenis_program_rkap);
            $('#modalUpdateRealisasi :input[name="deskripsi_perlakuan_risiko"]').val(perlakuanPenyebab.deskripsi_perlakuan_risiko);
            if (perlakuanPenyebab.timeline_perlakuan_risiko?.length === 2) {
                $("#timelineRange").data('_flatpickr').setDate(perlakuanPenyebab.timeline_perlakuan_risiko);
            } else {
                $("#timelineRange").data('_flatpickr').clear();
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
            $('#modalMitigasi :input[name="jenis_program_rkap_id"]').val(perlakuanMonitoring.jenis_program_rkap_id).change();
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
        }
    });

    $('#btnSimpanUpdateRealisasi').on('click', function() {
        if (!$('#formUpdateRealisasi')[0].checkValidity()) {
            $('#formUpdateRealisasi')[0].reportValidity();
            return;
        }
        const id = $('#formUpdateRealisasi :input[name="penyebab_risiko_id"]').val();
        const realisasiBiayaPerlakuanRisiko = $('#formUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val();
        const progressPerlakuanRisiko = $('#formUpdateRealisasi :input[name="progress_perlakuan_risiko"]').val();
        const deskripsiPerlakuanRisiko = $('#formUpdateRealisasi :input[name="deskripsi_perlakuan_risiko"]').val();
        const jenisProgramRkap = $('#formUpdateRealisasi :input[name="jenis_program_rkap"]').val();
        const timelinePerlakuanRisiko = $('#formUpdateRealisasi :input[name="timeline_perlakuan_risiko"]').val();

        if (!timelinePerlakuanRisiko) {
            Swal.fire('Error', 'Timeline perlakuan risiko harus diisi', 'error');
            return;
        }

        // update perlakuan penyebab risiko
        perlakuanPenyebabRisikos[id]['realisasi_biaya_perlakuan_risiko_q' + quarter] = realisasiBiayaPerlakuanRisiko;
        perlakuanPenyebabRisikos[id]['progress_rencana_perlakuan_risiko_q' + quarter] = progressPerlakuanRisiko;
        perlakuanPenyebabRisikos[id]['deskripsi_perlakuan_risiko'] = deskripsiPerlakuanRisiko;
        perlakuanPenyebabRisikos[id]['jenis_program_rkap'] = jenisProgramRkap;
        perlakuanPenyebabRisikos[id]['timeline_perlakuan_risiko'] = timelinePerlakuanRisiko.split(' to ');

        // update DOM
        const tr = $('#table-penyebab-risiko tr[data-id="' + id + '"]');
        tr.find('.display-biaya').text('Rp' + Intl.NumberFormat('id-ID').format(realisasiBiayaPerlakuanRisiko));
        tr.find('.display-progress').text(progressPerlakuanRisiko);

        $('#modalUpdateRealisasi').modal('hide');
    });

    $('#btnSimpanUpdateKri').on('click', function() {
        if (!$('#formUpdateKri')[0].checkValidity()) {
            $('#formUpdateKri')[0].reportValidity();
            return;
        }

        const id = $('#formUpdateKri :input[name="kri_project_id"]').val();
        const nilaiKri = $('#formUpdateKri :input[name="nilai_kri"]').val();
        const statusKri = $('#formUpdateKri :input[name="status_kri"]').val();

        // update kri
        kriProjects[id]['nilai_kri_terkini_q' + quarter] = nilaiKri;
        kriProjects[id]['status_kri_terkini_q' + quarter] = statusKri;

        // update DOM
        const tr = $('#table-kri tr[data-id="' + id + '"]');
        tr.find('.display-nilai-kri').text(nilaiKri);
        tr.find('.display-kondisi').text(statusKri);

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
    });

    var flatpickrIns = flatpickr("#timelineRange", {
        mode: "range",
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
        //maxDate: endOfYear,
        disableMobile: true
    });

    $("#timelineRange").data('_flatpickr', flatpickrIns);

    const files = @json($files);
    $('.lihat-file-btn').on('click', function() {
        const id = $(this).closest('tr').data('id');
        const filteredFiles = files[id];

        const tbody = $('#modalLihatFile tbody');
        tbody.empty();

        if (filteredFiles) {
            for (let i in filteredFiles) {
                const file = filteredFiles[i];
                const tr = $('<tr></tr>');
                tr.append('<td>' + file.file_name + '</td>');
                tr.append('<td>' + (file.description || '-') + '</td>');
                tr.append('<td><a href="' + file.url + '" download="' + file.file_name + '">Download</a></td>');
                tbody.append(tr);
            }
        } else {
            tbody.append('<tr><td colspan="4" class="text-center">Tidak ada data</td></tr>');
        }

        $('#modalLihatFile').modal('show');
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
});
</script>
@endpush
