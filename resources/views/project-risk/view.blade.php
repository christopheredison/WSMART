@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Detail Risiko Project: {{ $projectRisk->peristiwaRisiko?->title ?? 'N/A' }}</h3>
        </div>
    </div>

    <div class="card mt-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="h3 mb-0">Peta Risiko Inheren dan Residual</span>
            </div>
        </div>
        <div class="card-body">
            <div class="border p-3 mb-3">
                @foreach (['High', 'Moderate to High', 'Moderate', 'Low to Moderate', 'Low'] as $level)
                <div class="me-3 d-inline-flex align-items-center gap-2">
                    <span class="d-inline-block bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($level)))}}" style="width:20px; height:20px; border-radius: 3px;"></span>
                    <span>{{ $level }}</span>
                </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col align-items-center d-flex">
                            <h3 class="h4">Peta Risiko Inheren dan Residual</h3>
                        </div>
                        <div class="col-1">
                            <select class="form-select" style="visibility: hidden;">
                            </select>
                        </div>
                    </div>
                    <div class="table-risk-map" id="inherentMap">
                        <table class="map-table">
                            <tbody>
                                @for($likelihood = 5; $likelihood >= 1; $likelihood--)
                                    <tr>
                                    @if ($likelihood == 5)
                                        <td rowspan="5" class="side-title">
                                            <div class="divider m-0">
                                                <div class="divider-text">
                                                    LIKELIHOOD
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                    @for($impact = 1; $impact <= 5; $impact++)
                                        @php
                                        $riskMap = $riskMaps[$impact . '-' . $likelihood] ?? null;
                                        @endphp
                                        <td>
                                            <div class="data-cell {{strtolower(str_replace(' ', '-', $riskMap['level_risiko']))}}" data-id="{{ ($likelihood - 1) * 5 + $impact }}" data-posisi-risiko="{{ $riskMap['nilai_risiko'] }}" data-matrix='{{ $impact }}-{{ $likelihood }}'>
                                                <div class="kode-peristiwa"></div>
                                                <div class="posisi-risiko">{{ $riskMap['nilai_risiko'] }}</div>
                                            </div>
                                        </td>
                                    @endfor
                                    </tr>
                                @endfor
                                <tr>
                                    <td class="useless-cell"></td>
                                    <td colspan="5" class="footer-title">
                                        <div class="divider m-0">
                                            <div class="divider-text">
                                                IMPACT
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- begin::Legend -->
                        <div class="risk-map-legend d-flex flex-center gap-3">
                            <div class="d-flex align-items-center gap-1">
                                <i class='bx bx-circle inherent'></i>
                                Inherent
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i class='bx bxs-circle residual'></i>
                                Residual
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bx bxs-circle current"></i>
                                Current
                            </div>
                        </div>
                        <!-- end::Legend -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col align-items-center d-flex">
                            <h3 class="h4">Peta Risiko Terkini (Current)</h3>
                        </div>
                        <div class="col">
                            <select class="form-select" id="monthSelect">
                                @for ($month = 1; $month <= 12; $month++)
                                    @php $quarter = ceil($month / 3); @endphp
                                    <option value="{{ $month }}">Q{{ $quarter }} - {{ __('basic.month.' . $month) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-select" id="tahunSelect">
                                @foreach ($tahunMonitorings as $tahun)
                                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-risk-map" id="currentMap">
                        <table class="map-table">
                            <tbody>
                                @for($likelihood = 5; $likelihood >= 1; $likelihood--)
                                    <tr>
                                    @if ($likelihood == 5)
                                        <td rowspan="5" class="side-title">
                                            <div class="divider m-0">
                                                <div class="divider-text">
                                                    LIKELIHOOD
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                    @for($impact = 1; $impact <= 5; $impact++)
                                        @php
                                        $riskMap = $riskMaps[$impact . '-' . $likelihood] ?? null;
                                        @endphp
                                        <td>
                                            <div class="data-cell {{strtolower(str_replace(' ', '-', $riskMap['level_risiko']))}}" data-id="{{ ($likelihood - 1) * 5 + $impact }}" data-posisi-risiko="{{ $riskMap['nilai_risiko'] }}" data-matrix='{{ $impact }}-{{ $likelihood }}'>
                                                <div class="kode-peristiwa"></div>
                                                <div class="posisi-risiko">{{ $riskMap['nilai_risiko'] }}</div>
                                            </div>
                                        </td>
                                    @endfor
                                    </tr>
                                @endfor
                                <tr>
                                    <td class="useless-cell"></td>
                                    <td colspan="5" class="footer-title">
                                        <div class="divider m-0">
                                            <div class="divider-text">
                                                IMPACT
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- begin::Legend -->
                        <div class="risk-map-legend d-flex flex-center gap-3">
                            <div class="d-flex align-items-center gap-1">
                                <i class='bx bx-circle inherent'></i>
                                Inherent
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i class='bx bxs-circle residual'></i>
                                Residual
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bx bxs-circle current"></i>
                                Current
                            </div>
                        </div>
                        <!-- end::Legend -->
                    </div>
                </div>
            </div>
            <div class="d-block mt-3">
                <div class="table-responsive scrollbar">
                    <table class="table table-strategi">
                        <thead>
                            <tr>
                                <th>Peristiwa Risiko</th>
                                <th>Deskripsi Peristiwa Risiko</th>
                                <th>Nilai Dampak Inherent</th>
                                <th>Skala Dampak Inherent</th>
                                <th>Nilai Probabilitas Inherent</th>
                                <th>Skala Probabilitas Inherent</th>
                                <th>Nilai Risiko Inherent</th>
                                <th>Level Risiko Inherent</th>
                                <th>Nilai Dampak Residual</th>
                                <th>Skala Dampak Residual</th>
                                <th>Nilai Probabilitas Residual</th>
                                <th>Skala Probabilitas Residual</th>
                                <th>Nilai Risiko Residual</th>
                                <th>Level Risiko Residual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $projectRisk->peristiwaRisiko?->title ?? '-' }}</td>
                                <td>{{ $projectRisk->deskripsi_peristiwa_risiko ?? '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak, 0, ',', '.') : '-' }}</td>
                                <td>
                                    {{ $projectRisk->projectRiskAnalisa?->skalaDampakObj?->tingkat
                                        ? '(' . $projectRisk->projectRiskAnalisa?->skalaDampakObj?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaDampakObj?->deskripsi
                                        : '-' }}
                                </td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_probabilitas ?? '-' }}</td>
                                <td>
                                    {{ $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->tingkat
                                        ? '(' . $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->skala
                                        : '-' }}
                                </td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->skala_risiko ?? '-' }}</td>
                                <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($projectRisk->projectRiskAnalisa?->level_risiko)))}}">{{ $projectRisk->projectRiskAnalisa?->level_risiko ?? '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak_residual ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak_residual, 0, ',', '.') : '-' }}</td>
                                <td>
                                    {{ $projectRisk->projectRiskAnalisa?->skalaDampakResidualObj?->tingkat
                                        ? '(' . $projectRisk->projectRiskAnalisa?->skalaDampakResidualObj?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaDampakResidualObj?->deskripsi
                                        : '-' }}
                                </td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_probabilitas_residual ?? '-' }}</td>
                                <td>
                                    {{ $projectRisk->projectRiskAnalisa?->skalaProbabilitasResidual?->tingkat
                                        ? '(' . $projectRisk->projectRiskAnalisa?->skalaProbabilitasResidual?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaProbabilitasResidual?->skala
                                        : '-' }}
                                </td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->skala_risiko_residual ?? '-' }}</td>
                                <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($projectRisk->projectRiskAnalisa?->level_risiko_residual)))}}">{{ $projectRisk->projectRiskAnalisa?->level_risiko_residual ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @php
        $analisa = $projectRisk->projectRiskAnalisa;
        $project = $projectRisk->projectPeriodeList->project;
    @endphp

    <!-- ::DataRisiko Start -->
    <div class="col-12 mt-4 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">1</span>
                    </span>
                    <span class="h3 mb-0">Data Risiko</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 gx-md-5">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Nama Project</label>
                            <div class="p-3 bg-light rounded">
                                {{ $project->project_name ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Sasaran Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $projectRisk->target_capaian_kinerja ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Peristiwa Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $projectRisk->peristiwaRisiko->title ?? $projectRisk->peristiwa_risiko ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Jenis Risiko T2 & T3 KBUMN</label>
                            <div class="p-3 bg-light rounded">
                                {{ $projectRisk->kategoriRisiko->title ?? '-' }} – {{ $projectRisk->jenisRisiko->title ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Deskripsi Peristiwa Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $projectRisk->deskripsi_peristiwa_risiko ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">WBS</label>
                            <div class="p-3 bg-light rounded">
                                {{ $projectRisk->wbs ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ::DataRisiko End -->

    <!-- ::Kontrol Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">2</span>
                    </span>
                    <span class="h3 mb-0">Kontrol</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row gy-3 gx-xxl-6">
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Jenis Kontrol Eksisting</label>
                            <div class="p-3 bg-light rounded">
                                {{ optional($projectRisk->jenisKontrolEksisting)->jenis_kontrol ?? '-' }}
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Kontrol Eksisting</label>
                            @if($projectRisk->projectKontrolEksistings && $projectRisk->projectKontrolEksistings->isNotEmpty())
                                @foreach($projectRisk->projectKontrolEksistings as $key=>$kontrol)
                                    <div class="p-3 bg-light rounded mb-2">
                                        {{ $key + 1 }}. {{ $kontrol->kontrol_eksisting_desc }}
                                    </div>
                                @endforeach
                            @else
                                <div class="p-3 bg-light rounded">
                                    Tidak ada kontrol eksisting
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- <div class="form-group mb-4">
                            <label class="form-label fw-bold">Penilaian Efektivitas Kontrol</label>
                            <div class="p-3 bg-light rounded">
                                {{ optional($projectRisk->penilaianEfektivitasKontrolObj)->efektivitas_kontrol ?? '-' }}
                            </div>
                        </div> --}}
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Perkiraan Waktu Mulai Terpapar Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $projectRisk->perkiraan_waktu_terpapar_risiko_mulai ? \Carbon\Carbon::parse($projectRisk->perkiraan_waktu_terpapar_risiko_mulai)->format('d F Y') : '-' }}
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Perkiraan Waktu Selesai Terpapar Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $projectRisk->perkiraan_waktu_terpapar_risiko_akhir ? \Carbon\Carbon::parse($projectRisk->perkiraan_waktu_terpapar_risiko_akhir)->format('d F Y') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ::Kontrol End -->

    <!-- ::PenyebabRisiko Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">3</span>
                    </span>
                    <span class="h3 mb-0">Penyebab Risiko</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Penyebab Risiko</th>
                                <th width="25%">Rencana Perlakuan Risiko</th>
                                <th width="25%">Output Perlakuan Risiko</th>
                                <th width="20%">Biaya Perlakuan Risiko</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalBiaya = 0; @endphp
                            @forelse($projectRisk->penyebabRisikoProjects as $penyebab)
                                @if($penyebab->perlakuanPenyebabRisiko && $penyebab->perlakuanPenyebabRisiko->isNotEmpty())
                                    @foreach($penyebab->perlakuanPenyebabRisiko as $perlakuan)
                                        @php $totalBiaya += $perlakuan->biaya_perlakuan_risiko ?? 0; @endphp
                                        <tr>
                                            @if($loop->first)
                                                <td rowspan="{{ $penyebab->perlakuanPenyebabRisiko->count() }}">{{ $loop->parent->iteration }}</td>
                                                <td rowspan="{{ $penyebab->perlakuanPenyebabRisiko->count() }}">{{ $penyebab->penyebab_risiko }}</td>
                                            @endif
                                            <td>{{ $perlakuan->rencana_perlakuan_risiko ?? '-' }}</td>
                                            <td>{{ $perlakuan->output_perlakuan_risiko ?? '-' }}</td>
                                            <td>{{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : '-' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $penyebab->penyebab_risiko }}</td>
                                        <td colspan="3" class="text-center text-muted">Belum ada rencana perlakuan</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada penyebab risiko</td>
                                </tr>
                            @endforelse
                            @if($totalBiaya > 0)
                                <tr class="table-warning">
                                    <td colspan="4" class="text-end fw-bold">Total Biaya Perlakuan:</td>
                                    <td class="fw-bold">{{ 'Rp ' . number_format($totalBiaya, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- ::PenyebabRisiko End -->

    <!-- ::KeyRiskIndicator Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">4</span>
                    </span>
                    <span class="h3 mb-0">Key Risk Indicator</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">Key Risk Indicator</th>
                                <th width="15%">Satuan KRI</th>
                                <th width="15%">Batas Aman</th>
                                <th width="15%">Batas Waspada</th>
                                <th width="15%">Batas Bahaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projectRisk->kriProjects as $kri)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $kri->kri ?? '-' }}</td>
                                    <td>{{ $kri->satuan_kri ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $kri->batas_aman ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">{{ $kri->batas_waspada ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ $kri->batas_bahaya ?? '-' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada Key Risk Indicator</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- ::KeyRiskIndicator End -->

    <!-- ::AnalisaRisiko Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">5</span>
                    </span>
                    <span class="h3 mb-0">Analisa Risiko</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 gx-md-5">
                    <div class="col-md-4">
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Kategori Dampak</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->kategori_dampak ?? '-' }}
                            </div>
                        </div>
                    </div>
                    @if($analisa->kategori_dampak === \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF)
                        <div class="col-md-4">
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">Area Dampak</label>
                                <div class="p-3 bg-light rounded">
                                    {{ optional($analisa->areaDampakObj)->title ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-4">
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Risk Limit</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risk_limit ? 'Rp ' . number_format($risk_limit, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                    {{-- @if($analisa->kategori_dampak === \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF)
                      <div class="col-md-4">
                          <div class="form-group mb-4">
                              <label class="form-label fw-bold">Risk Tolerance</label>
                              <div class="p-3 bg-light rounded">
                                  {{ $risk_tolerance ? 'Rp ' . number_format($risk_tolerance, 0, ',', '.') : 'Rp 0' }}
                              </div>
                          </div>
                      </div>
                    @endif --}}
                </div>
            </div>
        </div>
    </div>
    <!-- ::AnalisaRisiko End -->

    <!-- ::PengukuranRisikoInheren Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">6</span>
                    </span>
                    <span class="h3 mb-0">Pengukuran Risiko Inheren</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nilai Dampak</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->nilai_dampak ? 'Rp ' . number_format($analisa->nilai_dampak, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Parameter Probabilitas</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skalaParameterObj->type_parameter ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nilai Probabilitas (%)</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->nilai_probabilitas ?? '-' }}%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Eksposur Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->eksposur_risiko ? 'Rp ' . number_format($analisa->eksposur_risiko, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Dampak</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skala_dampak ? '(' . $analisa->skala_dampak . ') ' . optional($analisa->skalaDampakObj)->deskripsi : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Probabilitas</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skalaProbabilitas ? '(' . $analisa->skalaProbabilitas->tingkat . ') ' . $analisa->skalaProbabilitas->skala : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skala_risiko ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Level Risiko</label>
                            <div class="p-3 rounded bg-{{ str_replace(' ', '-', str_replace('to ', '', strtolower($analisa->level_risiko))) }}">
                                <span class="text-white fw-bold">{{ $analisa->level_risiko ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($analisa->kategori_dampak === \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF)
                        <div class="col-12">
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">Deskripsi Dampak</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $analisa->deskripsi_dampak ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-12">
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">Asumsi Perhitungan Dampak & Probabilitas Inherent</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $analisa->asumsi_perhitungan_dampak ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ::PengukuranRisikoInheren End -->

    <!-- ::PengukuranRisikoResidual Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">7</span>
                    </span>
                    <span class="h3 mb-0">Pengukuran Risiko Residual</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nilai Dampak</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->nilai_dampak_residual ? 'Rp ' . number_format($analisa->nilai_dampak_residual, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Parameter Probabilitas</label>
                            <div class="p-3 bg-light rounded">
                                {{-- {{ $analisa->skalaParameterResidualObj ? '(' . $analisa->skalaParameterResidualObj->tingkat . ') ' . $analisa->skalaParameterResidualObj->skala : '-' }} --}}
                                {{ $analisa->skalaParameterObj->type_parameter ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nilai Probabilitas (%)</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->nilai_probabilitas_residual ?? '-' }}%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Eksposur Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->eksposur_risiko_residual ? 'Rp ' . number_format($analisa->eksposur_risiko_residual, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Dampak Residual</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skala_dampak_residual ? '(' . $analisa->skala_dampak_residual . ') ' . optional($analisa->skalaDampakResidualObj)->deskripsi : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Probabilitas</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skalaProbabilitasResidual ? '(' . $analisa->skalaProbabilitasResidual->tingkat . ') ' . $analisa->skalaProbabilitasResidual->skala : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skala_risiko_residual ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Level Risiko</label>
                            <div class="p-3 rounded bg-{{ str_replace(' ', '-', str_replace('to ', '', strtolower($analisa->level_risiko_residual))) }}">
                                <span class="text-white fw-bold">{{ $analisa->level_risiko_residual ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    @if($analisa->kategori_dampak === \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF)
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Deskripsi Dampak Residual</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $analisa->deskripsi_dampak_residual ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Asumsi Perhitungan Dampak & Probabilitas Residual</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $analisa->asumsi_perhitungan_dampak_residual ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ::PengukuranRisikoResidual End -->

    <!-- ::MonitoringRisiko Start -->
    @if($projectRisk->projectRiskMonitorings && $projectRisk->projectRiskMonitorings->isNotEmpty())
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">8</span>
                    </span>
                    <span class="h3 mb-0">Monitoring Risiko</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Tahun</th>
                                <th width="15%">Quarter</th>
                                <th width="15%">Bulan</th>
                                <th width="20%">Realisasi Nilai Dampak</th>
                                <th width="15%">Realisasi Nilai Probabilitas</th>
                                <th width="15%">Realisasi Level Risiko</th>
                                {{-- <th width="15%">Status</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projectRisk->projectRiskMonitorings as $monitoring)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $monitoring->tahun ?? '-' }}</td>
                                    <td>Q{{ $monitoring->quarter ?? '-' }}</td>
                                    <td>
                                        @if($monitoring->month)
                                            @lang('basic.month.' . $monitoring->month)
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $monitoring->nilai_dampak ? 'Rp ' . number_format($monitoring->nilai_dampak, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $monitoring->nilai_probabilitas ?? '-' }}%</td>
                                    <td>
                                        <div class="p-2 rounded bg-{{ str_replace(' ', '-', str_replace('to ', '', strtolower($monitoring->level_risiko))) }}">
                                            <span class="text-white fw-bold">{{ $monitoring->level_risiko ?? '-' }}</span>
                                        </div>
                                    </td>
                                    {{-- <td>
                                        @if($monitoring->status)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Non-Aktif</span>
                                        @endif
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- ::MonitoringRisiko End -->

    <div class="col-12 mt-5">
        <div class="row g-2">
            <div class="col-auto order-1">
                <a href="{{ route('projects.risks.index', ['project' => $projectRisk->projectPeriodeList->id]) }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.kode-peristiwa {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    position: absolute;
    bottom: 5px;
    right: 0;
    width: calc(100% - 5px) !important;
}
.box-inherent {
    background-color: #fff;
    color: #000;
    padding: 2px 5px;
    border-radius: 5px;
}
.box-residual {
    background-color: #000;
    color: #fff;
    padding: 2px 5px;
    border-radius: 5px;
}
.box-current {
    background-color: #007bff;
    color: #fff;
    padding: 2px 5px;
    border-radius: 5px;
}
#currentMap .current-m1, #currentMap .current-m2, #currentMap .current-m3, #currentMap .current-m4, #currentMap .current-m5, #currentMap .current-m6, #currentMap .current-m7, #currentMap .current-m8, #currentMap .current-m9, #currentMap .current-m10, #currentMap .current-m11, #currentMap .current-m12 {
    display: none;
}

@for ($month = 1; $month <= 12; $month++)
#currentMap.show-m{{ $month }} .current-m{{ $month }} {
    display: block;
}
@endfor

.table-warning {
    background-color: #fff3cd !important;
}
.form-group {
    margin-bottom: 1rem;
}
.rounded {
    border-radius: 0.375rem !important;
}
.bg-light {
    background-color: #f8f9fa !important;
}
</style>

@foreach ($tahunMonitorings as $tahunMonitoring)
<style>
#currentMap .current-{{ $tahunMonitoring }}-m1, #currentMap .current-{{ $tahunMonitoring }}-m2, #currentMap .current-{{ $tahunMonitoring }}-m3, #currentMap .current-{{ $tahunMonitoring }}-m4, #currentMap .current-{{ $tahunMonitoring }}-m5, #currentMap .current-{{ $tahunMonitoring }}-m6, #currentMap .current-{{ $tahunMonitoring }}-m7, #currentMap .current-{{ $tahunMonitoring }}-m8, #currentMap .current-{{ $tahunMonitoring }}-m9, #currentMap .current-{{ $tahunMonitoring }}-m10, #currentMap .current-{{ $tahunMonitoring }}-m11, #currentMap .current-{{ $tahunMonitoring }}-m12 {
    display: none;
}

@for ($month = 1; $month <= 12; $month++)
#currentMap.show-{{ $tahunMonitoring }}-m{{ $month }} .current-{{ $tahunMonitoring }}-m{{ $month }} {
    display: block;
}
@endfor

</style>
@endforeach
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    const risks = [@json($projectRisk)]; // Buat jadi array berisi 1 objek
    const formattedCurrentRiskMaps = @json($formattedCurrentRiskMaps);

    function initializeMaps() {
        $('#inherentMap .kode-peristiwa, #currentMap .kode-peristiwa').empty();

        risks.forEach((risk, idx) => {
            const code = (idx + 1).toString();

            // Peta Inheren & Residual
            const matrixI = risk.project_risk_analisa?.skala_dampak + '-' + risk.project_risk_analisa?.skala_probabilitas?.tingkat;
            const matrixR = risk.project_risk_analisa?.skala_dampak_residual + '-' + risk.project_risk_analisa?.skala_probabilitas_residual?.tingkat;

            $(`#inherentMap .data-cell[data-matrix="${matrixI}"]`).find('.kode-peristiwa').append(`<span class="box-inherent">R${code}</span>`);
            $(`#inherentMap .data-cell[data-matrix="${matrixR}"]`).find('.kode-peristiwa').append(`<span class="box-residual">R${code}</span>`);

            // Peta Current
            if (formattedCurrentRiskMaps[risk.id]) {
                Object.keys(formattedCurrentRiskMaps[risk.id]).forEach((tahun) => {
                    formattedCurrentRiskMaps[risk.id][tahun].forEach((currentRiskMap) => {
                        const matrixC = currentRiskMap.skala_dampak + '-' + currentRiskMap.skala_probabilitas;
                        $(`#currentMap .data-cell[data-matrix="${matrixC}"]`)
                            .find('.kode-peristiwa')
                            .append(`<span class="box-current current-${tahun}-m${currentRiskMap.month}">R${code}</span>`);
                    });
                });
            }
        });
    }

    $('#monthSelect, #tahunSelect').on('change', function() {
        const month = $('#monthSelect').val();
        const tahun = $('#tahunSelect').val();
        $('#currentMap').prop('class', 'table-risk-map');
        $('#currentMap').addClass('show-' + tahun + '-m' + month);
    }).change();

    initializeMaps();
});
</script>
@endpush
