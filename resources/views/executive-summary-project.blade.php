@extends('layouts.default')

@section('dashboard')
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="../assets/img/dashboard-header.webp" alt="dashboard">
      <div class="card-header border-0 justify-content-end">
        <h1 class="mb-0">Executive Summary Proyek</h1>
        <h4 id="selected-project-name" class="mb-4">{{ $selectedProject ? $selectedProject->project_name : '' }}</h4>
        <h6>Statistik per tanggal {{ now()->format('d M Y') }}</h6>
      </div>
    </div>
  </div>
</div>
<div class="row input-selector-rounded g-3 mb-5">
    <div class="col-12">
        <div class="card p-3 shadow-sm">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="period_selector" class="form-label fw-bold">Pilih Periode</label>
                    <input type="text" name="period" id="period_selector" class="form-control" placeholder="Pilih Bulan & Tahun" value="{{ $selectedPeriod }}">
                </div>

                <div class="col-md-4">
                    <label for="unit_selector" class="form-label fw-bold">Pilih Divisi</label>
                    <select name="unit_id" id="unit_selector" class="form-select select2">
                        <option value="" selected>Pilih Divisi</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ $unit->id == $selectedUnitId ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="project_selector" class="form-label fw-bold">Pilih Proyek</label>
                    <select name="project_id" id="project_selector" class="form-select select2">
                        <option value="" selected>Pilih Proyek</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" {{ $project->id == $selectedProjectId ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($selectedProjectId)
    {{-- Variabel untuk format periode --}}
    @php
        $formattedPeriod = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y');
        $currentYear = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->year;
    @endphp

    {{-- Header Utama --}}
    <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-shield-alt me-2"></i>Manajemen Kinerja Berbasis Risiko</h2>
    <hr class="mb-4">

    <div class="row g-4 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card card-body h-100 shadow-sm border-start border-4 border-success">
                <p class="text-muted text-uppercase mb-1">Omset Kontrak Total</p>
                <h3 class="fw-bold text-dark mb-0">Rp {{ number_format($summaryData['omset_kontrak_total'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card card-body h-100 shadow-sm border-start border-4 border-info">
                <p class="text-muted text-uppercase mb-1">Omset Penjualan s/d {{ $formattedPeriod }}</p>
                <h3 class="fw-bold text-dark mb-0">Rp {{ number_format($summaryData['omset_penjualan_sd_bulan'], 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card card-body h-100 shadow-sm border-start border-4 border-warning">
                <p class="text-muted text-uppercase mb-1">Progress s/d {{ $formattedPeriod }}</p>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($summaryData['progress_sd_bulan'], 2) }}%</h3>
            </div>
        </div>
    </div>


    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary-subtle">
                    <h5 class="mb-0 fw-bold text-primary-emphasis"><i class="fas fa-calendar-day me-2"></i>LSP s/d Bulan {{ $formattedPeriod}}</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <ul class="list-group list-group-flush flex-grow-1">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Omset Penjualan</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['omset_penjualan_sd_bulan'], 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">LSP Rencana</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['lsp_rencana_sd_bulan'], 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">LSP Realisasi</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['lsp_realisasi_sd_bulan'], 0, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-flag-checkered me-2"></i>Proyeksi LSP s/d Proyek Selesai</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <ul class="list-group list-group-flush flex-grow-1">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Omset Penjualan</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['omset_penjualan_sd_selesai'], 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">LSP Rencana</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['lsp_rencana_sd_selesai'], 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Proyeksi LSP</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['lsp_realisasi_sd_selesai'], 0, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>


    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-database me-2"></i>Loss Event Database (LED)</h5>
                </div>
                <div class="card-body d-flex align-items-center py-2">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <ul class="list-group list-group-flush flex-grow-1">
                          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                              <span class="text-muted">Total Kerugian Finansial</span>
                              <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_proyek_total'], 0, ',', '.') }}</span>
                          </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Eksposur Risiko Residual</h5>
                </div>
                <div class="card-body d-flex align-items-center py-2">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <ul class="list-group list-group-flush flex-grow-1">
                          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                              <span class="text-muted">Residual Realisasi Total</span>
                              <span class="fw-bold fs-4 text-warning">Rp {{ number_format($summaryData['eksposur_risiko_total'], 0, ',', '.') }}</span>
                          </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-success-emphasis mb-0">Potensi Hasil Usaha s/d Bulan {{ $formattedPeriod }}</h6>
                            <small class="text-muted">(LSP Realisasi + LED)<br><i>(jika LED tidak terjadi)</i></small>
                        </div>
                        <span class="fw-bold fs-4 text-success">Rp {{ number_format($summaryData['potensi_hasil_usaha_sd_bulan'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-primary-emphasis mb-0">Proyeksi Hasil Usaha s/d Des {{ $currentYear }}</h6>
                            <small class="text-muted">(Proyeksi LSP - Eksposur)</small>
                        </div>
                        <span class="fw-bold fs-4 text-primary">Rp {{ number_format($summaryData['proyeksi_lsp_incl_eksposur'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-chart-pie me-2"></i>Profil Risiko Proyek</h2>
    <hr class="mb-4">

    {{-- BAGIAN PETA RISIKO --}}
    <div class="card" id="mapCardContainer">
        <div class="card-header border-0 pb-0">
            <div class="d-flex justify-content-between align-items-start w-100">
                <div class="d-flex flex-column">
                    <span class="h3 mb-0">Peta Risiko</span>
                    <small class="text-muted mt-1">Gunakan tombol perbesar untuk melihat pemetaan dengan lebih jelas.</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary shadow-sm" id="btnFullscreenMap">
                    <span class="bx bx-fullscreen me-1"></span> Perbesar Peta
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="border p-3 mb-3 bg-light rounded">
                @foreach (['High', 'Moderate to High', 'Moderate', 'Low to Moderate', 'Low'] as $level)
                <div class="me-3 d-inline-flex align-items-center gap-2">
                    <span class="d-inline-block bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($level)))}}" style="width:20px; height:20px; border-radius: 3px;"></span>
                    <span>{{ $level }}</span>
                </div>
                @endforeach
            </div>

            <div class="row">
                {{-- PETA RISIKO INHEREN & RESIDUAL --}}
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="row mb-3">
                        <div class="col align-items-center d-flex"><h3 class="h4">Peta Risiko Inheren dan Residual</h3></div>
                    </div>
                    <div class="table-risk-map" id="inherentMap">
                        <table class="map-table w-100">
                            <tbody>
                                @for($likelihood = 5; $likelihood >= 1; $likelihood--)
                                <tr>
                                    @if ($likelihood == 5)
                                    <td rowspan="5" class="side-title"><div class="divider m-0"><div class="divider-text">LIKELIHOOD</div></div></td>
                                    @endif
                                    @for($impact = 1; $impact <= 5; $impact++)
                                        @php $riskMap = $riskMaps[$impact . '-' . $likelihood] ?? null; @endphp
                                        <td>
                                            <div class="data-cell {{strtolower(str_replace(' ', '-', $riskMap['level_risiko']))}}" data-matrix='{{ $impact }}-{{ $likelihood }}'>
                                                <div class="kode-peristiwa"></div>
                                                <div class="posisi-risiko">{{ $riskMap['nilai_risiko'] }}</div>
                                            </div>
                                        </td>
                                    @endfor
                                </tr>
                                @endfor
                                <tr>
                                    <td class="useless-cell"></td>
                                    <td colspan="5" class="footer-title"><div class="divider m-0"><div class="divider-text">IMPACT</div></div></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="risk-map-legend d-flex justify-content-center gap-4 mt-3">
                            <div class="d-flex align-items-center gap-1"><i class='bx bx-circle inherent fs-5'></i> Inherent</div>
                            <div class="d-flex align-items-center gap-1"><i class='bx bxs-circle residual fs-5'></i> Residual</div>
                        </div>
                    </div>
                </div>

                {{-- PETA RISIKO TERKINI (CURRENT) --}}
                <div class="col-md-6">
                    <div class="row mb-3 align-items-center">
                        <div class="col-12 col-xl-5 mb-2 mb-xl-0"><h3 class="h4 mb-0">Peta Risiko Terkini</h3></div>
                        <div class="col-6 col-xl-4">
                            <select class="form-select form-select-sm" id="monthSelect">
                                @for ($month = 1; $month <= 12; $month++)
                                    <option value="{{ $month }}" {{ $month == (int) \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->format('m') ? 'selected' : '' }}>
                                        Q{{ ceil($month / 3) }} - {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6 col-xl-3">
                            <select class="form-select form-select-sm" id="tahunSelect">
                                @foreach ($tahunMonitorings as $tahun)
                                <option value="{{ $tahun }}" {{ $tahun == \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->format('Y') ? 'selected' : '' }}>{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-risk-map" id="currentMap">
                        <table class="map-table w-100">
                          <tbody>
                                @for($likelihood = 5; $likelihood >= 1; $likelihood--)
                                <tr>
                                    @if ($likelihood == 5)
                                    <td rowspan="5" class="side-title"><div class="divider m-0"><div class="divider-text">LIKELIHOOD</div></div></td>
                                    @endif
                                    @for($impact = 1; $impact <= 5; $impact++)
                                        @php $riskMap = $riskMaps[$impact . '-' . $likelihood] ?? null; @endphp
                                        <td>
                                            <div class="data-cell {{strtolower(str_replace(' ', '-', $riskMap['level_risiko']))}}" data-matrix='{{ $impact }}-{{ $likelihood }}'>
                                                <div class="kode-peristiwa"></div>
                                                <div class="posisi-risiko">{{ $riskMap['nilai_risiko'] }}</div>
                                            </div>
                                        </td>
                                    @endfor
                                </tr>
                                @endfor
                                <tr>
                                    <td class="useless-cell"></td>
                                    <td colspan="5" class="footer-title"><div class="divider m-0"><div class="divider-text">IMPACT</div></div></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="risk-map-legend d-flex justify-content-center gap-4 mt-3">
                            <div class="d-flex align-items-center gap-1"><i class="bx bxs-circle current fs-5 text-primary"></i> Current</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ALERT INFO RISIKO BELUM UPDATE --}}
            <div class="alert alert-warning mt-4 mb-0 d-none shadow-sm" id="unmonitored-info">
                <div class="d-flex">
                    <i class="bx bx-error-circle fs-2 me-3 mt-1 text-warning"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">Informasi Status Realisasi Bulan <span id="info-month" class="text-dark"></span></h6>
                        <p class="mb-2">Risiko dengan tanda bintang merah (<span class="text-danger fw-bold fs-5">*</span>) pada Peta Risiko Current dan Tabel Daftar Risiko di bawah menandakan bahwa <strong>risiko tersebut belum dilakukan pembaruan / verifikasi pelaporan monitoring</strong> pada bulan cutoff yang dipilih. Posisi risiko yang ditampilkan adalah fallback dari data bulan sebelumnya atau data inheren.</p>
                        <p class="mb-0"><strong>Risiko yang belum ter-update:</strong> <span id="unmonitored-list" class="badge bg-warning text-dark fw-bold ms-1">-</span></p>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
                <h3 class="h4 mb-0">Daftar Risiko</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm table-strategi">
                    <thead class="text-center align-middle bg-light">
                        <tr>
                            <th rowspan="2">Kode</th>
                            <th rowspan="2" style="min-width: 200px;">Peristiwa Risiko</th>
                            <th colspan="6">Inherent</th>
                            <th colspan="6">Residual</th>
                            <th colspan="6" class="bg-primary-subtle">Realisasi (Current)</th>
                        </tr>
                        <tr>
                            {{-- Inherent --}}
                            <th style="min-width: 120px;">Nilai Dampak</th>
                            <th>Skala Dampak</th>
                            <th style="min-width: 100px;">Nilai Probabilitas</th>
                            <th>Skala Probabilitas</th>
                            <th>Nilai Risiko</th>
                            <th>Level Risiko</th>

                            {{-- Residual --}}
                            <th style="min-width: 120px;">Nilai Dampak</th>
                            <th>Skala Dampak</th>
                            <th style="min-width: 100px;">Nilai Probabilitas</th>
                            <th>Skala Probabilitas</th>
                            <th>Nilai Risiko</th>
                            <th>Level Risiko</th>

                            {{-- Realisasi --}}
                            <th style="min-width: 120px;" class="bg-primary-subtle">Nilai Dampak</th>
                            <th class="bg-primary-subtle">Skala Dampak</th>
                            <th style="min-width: 100px;" class="bg-primary-subtle">Nilai Probabilitas</th>
                            <th class="bg-primary-subtle">Skala Probabilitas</th>
                            <th class="bg-primary-subtle">Nilai Risiko</th>
                            <th class="bg-primary-subtle">Level Risiko</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($openRisks as $projectRisk)
                        <tr data-risk-id="{{ $projectRisk->id }}">
                            <td class="text-start fw-bold">
                              <a href="{{  route('projects.risks.view', ['project' => $projectRisk->project_periode_list_id, 'risk' => $projectRisk->id]) }}" class="text-primary text-decoration-underline">
                                R{{ $loop->iteration }}
                              </a>
                            </td>
                            <td class="text-start">
                                {{ $projectRisk->peristiwa_risiko_id === 0 ? $projectRisk->rencana_kegiatan : ($projectRisk->peristiwaRisiko->title ?? $projectRisk->peristiwa_risiko ?? '-') }}
                            </td>

                            {{-- =================== Inherent Risk Data =================== --}}
                            <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak, 0, ',', '.') : 'Rp 0' }}</td>
                            <td class="text-center fw-bold">{{ optional(optional($projectRisk->projectRiskAnalisa)->skalaDampakObj)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ optional($projectRisk->projectRiskAnalisa)->nilai_probabilitas ? optional($projectRisk->projectRiskAnalisa)->nilai_probabilitas . '%' : '-' }}</td>
                            <td class="text-center fw-bold">{{ optional(optional($projectRisk->projectRiskAnalisa)->skalaProbabilitas)->tingkat ?? '-' }}</td>
                            <td class="text-center fw-bold">{{ optional($projectRisk->projectRiskAnalisa)->skala_risiko ?? '-' }}</td>
                            <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($projectRisk->projectRiskAnalisa?->level_risiko)))}} fw-bold">{{ $projectRisk->projectRiskAnalisa?->level_risiko ?? '-' }}</td>

                            {{-- =================== Residual Risk Data =================== --}}
                            <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak_residual ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak_residual, 0, ',', '.') : 'Rp 0' }}</td>
                            <td class="text-center fw-bold">{{ optional(optional($projectRisk->projectRiskAnalisa)->skalaDampakResidualObj)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ optional($projectRisk->projectRiskAnalisa)->nilai_probabilitas_residual ? optional($projectRisk->projectRiskAnalisa)->nilai_probabilitas_residual . '%' : '-' }}</td>
                            <td class="text-center fw-bold">{{ optional(optional($projectRisk->projectRiskAnalisa)->skalaProbabilitasResidual)->tingkat ?? '-' }}</td>
                            <td class="text-center fw-bold">{{ optional($projectRisk->projectRiskAnalisa)->skala_risiko_residual ?? '-' }}</td>
                            <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($projectRisk->projectRiskAnalisa?->level_risiko_residual)))}} fw-bold">{{ $projectRisk->projectRiskAnalisa?->level_risiko_residual ?? '-' }}</td>

                            {{-- =================== Realisasi (Current) - Diisi oleh JavaScript =================== --}}
                            <td class="realisasi-nilai-dampak">-</td>
                            <td class="realisasi-skala-dampak text-center fw-bold">-</td>
                            <td class="realisasi-nilai-probabilitas text-center">-</td>
                            <td class="realisasi-skala-probabilitas text-center fw-bold">-</td>
                            <td class="realisasi-nilai-risiko text-center fw-bold">-</td>
                            <td class="realisasi-level-risiko fw-bold">-</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="20" class="text-center p-4">Tidak ada data risiko yang berstatus Open.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-tachometer-alt me-2"></i>Key Risk Indicator (KRI)</h2>
    <hr class="mb-4">

    <div class="card">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="h3 mb-0">Daftar KRI Proyek (Status Waspada & Bahaya)</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="text-center align-middle bg-light">
                        <tr>
                            <th style="min-width: 150px;">Risiko</th>
                            <th style="min-width: 200px;">Penyebab</th>
                            <th style="min-width: 150px;">KRI</th>
                            <th>Batas Aman</th>
                            <th>Batas Waspada</th>
                            <th>Batas Bahaya</th>
                            <th>Kondisi Saat Ini</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sortedKriData as $kri)
                        <tr>
                            <td>
                              <a href="{{  route('projects.risks.view', ['project' => $kri['project_id'], 'risk' => $kri['risiko_id']]) }}">
                                {{ $kri['risiko'] }}
                              </a>
                            </td>
                            <td>
                                @php
                                    $penyebabArray = json_decode($kri['penyebab'], true);
                                @endphp

                                @if(!empty($penyebabArray) && is_array($penyebabArray))
                                    <ul class="list-unstyled mb-0 ps-3">
                                        @foreach($penyebabArray as $item)
                                            <li>- {{ $item['penyebab_risiko'] }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $kri['kri'] ?? '-' }}</td>
                            <td class="text-center">{{ $kri['batas_aman'] ?? '-' }}</td>
                            <td class="text-center">{{ $kri['batas_waspada'] ?? '-' }}</td>
                            <td class="text-center">{{ $kri['batas_bahaya'] ?? '-' }}</td>
                            <td class="text-center fw-bold">{{ $kri['kondisi_saat_ini'] }}</td>
                            <td class="text-center pe-1">
                                @php
                                    $statusClass = '';
                                    $statusNumeric = $kri['status'] ?? 0;
                                    if ($statusNumeric == 3) { $statusClass = 'red'; }
                                    elseif ($statusNumeric == 2) { $statusClass = 'yellow'; }
                                    elseif ($statusNumeric == 1) { $statusClass = 'green'; }
                                @endphp
                                <div class="status-container text-center {{ $statusClass }}" style="min-width: 45px;">
                                    <div class="status-green"></div>
                                    <div class="status-yellow"></div>
                                    <div class="status-red"></div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center p-4">Tidak ada data KRI dengan status Waspada atau Bahaya.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="status-legend mt-3 d-flex justify-content-end gap-3">
                <span class="fw-bold align-self-center">
                  Status:
                </span>
                <div class="d-flex align-items-center gap-1 status-container green">
                    <div class="status-green"></div>
                    <span>Aman</span>
                </div>
                <div class="d-flex align-items-center gap-1 status-container yellow">
                    <div class="status-yellow"></div>
                    <span>Waspada</span>
                </div>
                <div class="d-flex align-items-center gap-1 status-container red">
                    <div class="status-red"></div>
                    <span>Bahaya</span>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-check-circle me-2"></i>Efektivitas Perlakuan Risiko</h2>
    <hr class="mb-4">

    <div class="row g-4">
        {{-- Kolom Kiri: Pie Chart --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 d-flex flex-between-center">
                    <h3 class="h4">Ringkasan Efektivitas</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            {{-- Legend Chart --}}
                            <div class="border rounded-3 p-3 mb-3 w-100 bg-light">
                                @foreach ($efektivitasPerlakuanData as $item)
                                    <div class="mb-1 d-flex align-items-center gap-2">
                                        <span class="d-inline-block shadow-sm" style="width:20px; height:20px; border-radius: 3px; background-color: {{$item['color']}}"></span>
                                        <span class="fw-medium">{{ $item['label'] }} ({{ $item['value'] }})</span>
                                    </div>
                                @endforeach
                            </div>
                            {{-- Container Chart --}}
                            <div class="min-vh-25">
                                <div id="efektivitas-perlakuan-chart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Detail Risiko --}}
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h3 class="h4">Detail Risiko Selesai (Closed)</h3>
                </div>
                <div class="card-body">
                    <div class="accordion shadow-sm" id="accordionEfektivitas">
                        {{-- Accordion untuk Risiko Efektif --}}
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header" id="headingEfektif">
                                <button class="accordion-button fs-6 py-3 rounded text-white" style="background-color: #5470C6;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEfektif" aria-expanded="true" aria-controls="collapseEfektif">
                                    <i class="bx bx-check-shield me-2 fs-5"></i> Perlakuan Efektif
                                    <span class="badge bg-white text-dark rounded-pill ms-auto">{{ $efektifRisks->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapseEfektif" class="accordion-collapse collapse show" aria-labelledby="headingEfektif">
                                <div class="accordion-body p-0 border border-top-0 rounded-bottom">
                                    <ul class="list-group list-group-flush">
                                        @forelse($efektifRisks as $risk)
                                            <a href="{{ route('projects.risks.view', ['project' => $risk->project_periode_list_id, 'risk' => $risk->id]) }}" target="_blank" class="list-group-item list-group-item-action">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span class="fw-medium">
                                                        {{ $risk->peristiwa_risiko_id === 0 ? $risk->rencana_kegiatan : ($risk->peristiwaRisiko->title ?? $risk->peristiwa_risiko ?? '-') }}
                                                        - <span class="text-muted">{{ $risk->deskripsi_peristiwa_risiko }}</span>
                                                    </span>
                                                    <span class="badge bg-light text-dark border">Nilai: {{ $risk->efektivitas_perlakuan_risiko ?? 0 }}</span>
                                                </div>
                                            </a>
                                        @empty
                                            <li class="list-group-item text-center text-muted py-3">Tidak ada risiko yang dinilai efektif.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        {{-- Accordion untuk Risiko Tidak Efektif --}}
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header" id="headingTidakEfektif">
                                <button class="accordion-button fs-6 py-3 collapsed rounded text-white" style="background-color: #EE6666;" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTidakEfektif" aria-expanded="false" aria-controls="collapseTidakEfektif">
                                    <i class="bx bx-x-circle me-2 fs-5"></i> Perlakuan Tidak Efektif
                                    <span class="badge bg-white text-dark rounded-pill ms-auto">{{ $tidakEfektifRisks->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapseTidakEfektif" class="accordion-collapse collapse" aria-labelledby="headingTidakEfektif">
                                <div class="accordion-body p-0 border border-top-0 rounded-bottom">
                                    <ul class="list-group list-group-flush">
                                        @forelse($tidakEfektifRisks as $risk)
                                            <a href="{{ route('projects.risks.view', ['project' => $risk->project_periode_list_id, 'risk' => $risk->id]) }}" target="_blank" class="list-group-item list-group-item-action">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span class="fw-medium">
                                                        {{ $risk->peristiwa_risiko_id === 0 ? $risk->rencana_kegiatan : ($risk->peristiwaRisiko->title ?? $risk->peristiwa_risiko ?? 'Risiko ID: '.$risk->id) }}
                                                    </span>
                                                    <span class="badge bg-light text-dark border">Nilai: {{ $risk->efektivitas_perlakuan_risiko ?? 0 }}</span>
                                                </div>
                                            </a>
                                        @empty
                                            <li class="list-group-item text-center text-muted py-3">Tidak ada risiko yang dinilai tidak efektif.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Top Loss Event Proyek</h2>
    <hr class="mb-4">

    <div class="card">
        <div class="card-header border-0 pb-0">
            <div class="d-flex align-items-center gap-3">
                <h3>Top Loss Event Proyek</h3>
            </div>
            <hr class="mb-0 mt-4">
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm table-bordered mt-3">
                    <thead class="bg-light text-center align-middle">
                        <tr>
                            <th>#</th>
                            <th>Nama Proyek</th>
                            <th>Tanggal Kejadian</th>
                            <th>Nama Kejadian</th>
                            <th>Deskripsi Kejadian</th>
                            <th>Kategori Kejadian</th>
                            <th class="text-end">Nilai Kerugian Finansial</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topLossEvents as $event)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ optional($event->project)->project_name ?? '-' }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($event->tanggal_kejadian)->format('d M Y') }}</td>
                                <td>{{ $event->nama_kejadian ?? '-' }}</td>
                                <td>
                                  {{
                                    $event->peristiwa_risiko_id == 0 ? $event->deskripsi_kejadian : ($event?->peristiwaRisiko?->title ?? '-')
                                  }}
                                </td>
                                <td class="text-center">{{ optional($event->kategoriKejadian)->kategori_kejadian ?? '-' }}</td>
                                <td class="text-end text-danger fw-bold">Rp {{ number_format($event->nilai_kerugian_finansial, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ url('project-led/' . $event->id) }}" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class='bx bx-show'></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center p-4 text-muted">
                                    Tidak ada data Loss Event untuk proyek ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-info text-center mt-5 shadow-sm border-0" role="alert">
        <i class="bx bx-info-circle fs-3 mb-2 d-block"></i>
        <strong>Pilih Proyek</strong> untuk menampilkan Executive Summary dan detail risiko.
    </div>
@endif

{{-- MODAL DETAIL PETA RISIKO --}}
<div class="modal fade" id="modalPetaRisiko" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-0 border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                  Daftar Risiko - Tingkat <span id="modalRiskLevel" class="fw-bold"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0" id="tableModalRisiko">
                        <thead class="bg-light text-center">
                            <tr>
                                <th width="15%">Kode & Tipe</th>
                                <th width="45%">Peristiwa Risiko</th>
                                <th width="25%">Nilai Dampak</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<style>
  #modalPetaRisiko {
    z-index: 10005 !important;
}

.modal-backdrop {
    z-index: 10004 !important;
}
.data-cell {
    position: relative;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
}
.data-cell:hover {
    box-shadow: inset 0 0 15px rgba(0,0,0,0.3);
    opacity: 0.9;
}

.fullscreen-container .row {
    height: calc(100vh - 150px);
}
.fullscreen-container .col-md-6 {
    height: 100%;
    display: flex;
    flex-direction: column;
}
.fullscreen-container .table-risk-map {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}
.fullscreen-container .map-table {
    height: 100%;
}
.fullscreen-container .data-cell {
    height: 100%;
    min-height: 80px;
}
.box-inherent, .box-residual, .box-current {
    padding: 2px 4px !important; /* Kurangi padding horizontal (sebelumnya 6px) */
    border-radius: 3px;
    font-weight: 700;
    font-size: 0.7rem !important; /* Perkecil font sedikit (sebelumnya 0.75rem) */
    line-height: 1.1;
    letter-spacing: -0.2px; /* Rapatkan jarak antar huruf sedikit */
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.box-inherent {
    background-color: #ffffff;
    color: #000000;
    border: 1px solid #000000;
}

.box-residual {
    background-color: #000000;
    color: #ffffff;
    border: 1px solid #000000;
}

.box-current {
    background-color: #007bff;
    color: #ffffff;
    border: 1px solid #007bff;
}

.kode-peristiwa {
    display: flex;
    flex-wrap: wrap;
    gap: 3px !important;
    position: absolute;
    bottom: 5px;
    right: 0;
    width: calc(100% - 5px) !important;
}

/* Fullscreen Peta Risiko */
.fullscreen-container {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100vw !important;
    height: 100vh !important;
    background: #ffffff;
    z-index: 9999;
    padding: 20px;
    overflow-y: auto;
    border-radius: 0 !important;
    box-shadow: none !important;
}

#currentMap .current-m1, #currentMap .current-m2, #currentMap .current-m3, #currentMap .current-m4, #currentMap .current-m5, #currentMap .current-m6, #currentMap .current-m7, #currentMap .current-m8, #currentMap .current-m9, #currentMap .current-m10, #currentMap .current-m11, #currentMap .current-m12 {
    display: none;
}

@for ($month = 1; $month <= 12; $month++)
#currentMap.show-m{{ $month }} .current-m{{ $month }} {
    display: block;
}
@endfor
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    function applyFilterAndRefresh() {
        var unitId = $('#unit_selector').val();
        var projectId = $('#project_selector').val();
        var period = $('#period_selector').val();

        if (projectId) {
            let baseUrl = '{{ url()->current() }}';
            let params = new URLSearchParams();

            if (unitId) params.append('unit_id', unitId);
            if (projectId) params.append('project_id', projectId);
            if (period) params.append('period', period);

            window.location.href = `${baseUrl}?${params.toString()}`;
        }
    }

    flatpickr("#period_selector", {
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y",
                altInput: true,
            })
        ],
        maxDate: "today",
        defaultDate: "{{ $selectedPeriod }}",
        onChange: function(selectedDates, dateStr, instance) {
            applyFilterAndRefresh();
        }
    });

    $('#unit_selector').on('change', function() {
        var selectedUnitId = $(this).val();
        var $projectSelector = $('#project_selector');
        $projectSelector.empty().append('<option value="" selected>Memuat Proyek...</option>').trigger('change.select2');

        if (selectedUnitId) {
            $.ajax({
                url: '{{ url()->current() }}',
                type: 'GET',
                data: { unit_id: selectedUnitId, ajax: 1 },
                success: function(response) {
                    var projects = response.projects;
                    $projectSelector.empty().append('<option value="" selected>Pilih Proyek</option>');

                    if (projects.length > 0) {
                        $.each(projects, function(key, project) {
                            $projectSelector.append(new Option(project.project_name, project.id));
                        });

                        if (projects.length === 1) {
                            $projectSelector.val(projects[0].id);
                            applyFilterAndRefresh();
                        }
                    } else {
                        $projectSelector.append('<option value="" disabled>Tidak ada proyek</option>');
                    }
                    $projectSelector.trigger('change.select2');
                }
            });
        } else {
            $projectSelector.empty().append('<option value="" selected>Pilih Divisi Dulu</option>').trigger('change.select2');
        }
    });

    $('#project_selector').on('change', function() {
        applyFilterAndRefresh();
    });

    // FULLSCREEN HANDLER PETA RISIKO
    $('#btnFullscreenMap').on('click', function() {
        const mapContainer = $('#mapCardContainer');
        mapContainer.toggleClass('fullscreen-container');

        if (mapContainer.hasClass('fullscreen-container')) {
            $(this).html('<span class="bx bx-exit-fullscreen me-1"></span> Tutup Layar Penuh');
            $(this).removeClass('btn-outline-primary').addClass('btn-danger');
            $('body').css('overflow', 'hidden');
        } else {
            $(this).html('<span class="bx bx-fullscreen me-1"></span> Perbesar Peta');
            $(this).removeClass('btn-danger').addClass('btn-outline-primary');
            $('body').css('overflow', '');
        }
    });

    @if ($selectedProjectId && !$projectRisksJs->isEmpty())
        const risks = @json($projectRisksJs);
        const formattedCurrentRiskMaps = @json($formattedCurrentRiskMaps);
        const baseUrl = `{{ url('projects') }}`; // URL dasar untuk detail risiko

        // Fungsi Helper untuk render badge di kotak peta
        function renderCellBadges(cell, risksArray, typeClass) {
            if (!risksArray || risksArray.length === 0) return;
            let html = '';

            // UBAH DISINI: Kembalikan maksimal display ke 6
            let maxDisplay = 6;

            let count = risksArray.length;

            for (let i = 0; i < Math.min(count, maxDisplay); i++) {
                html += `<span class="${typeClass}">${risksArray[i].riskNumber}${risksArray[i].displayMark || ''}</span>`;
            }

            if (count > maxDisplay) {
                // Tambahkan padding dan font-size seragam untuk badge sisa (+X)
                html += `<span class="${typeClass} bg-danger border-danger text-white" data-bs-toggle="tooltip" title="Ada ${count - maxDisplay} risiko lain">+${count - maxDisplay}</span>`;
            }
            cell.find('.kode-peristiwa').append(html);
        }

        function populateInherentMap() {
            $('#inherentMap .kode-peristiwa').empty();
            let mapInherent = {};
            let mapResidual = {};

            Object.values(risks).forEach(risk => {
                if(risk.project_risk_analisa) {
                    const riskNumber = $(`.table-strategi tbody tr[data-risk-id="${risk.id}"]`).find('td:first').text().trim();
                    if(!riskNumber) return;

                    let riskObj = { ...risk, riskNumber: riskNumber };

                    // Kelompokkan data Inherent
                    const matrixI = risk.project_risk_analisa.skala_dampak + '-' + risk.project_risk_analisa.skala_probabilitas?.tingkat;
                    if(!mapInherent[matrixI]) mapInherent[matrixI] = [];
                    mapInherent[matrixI].push(riskObj);

                    // Kelompokkan data Residual
                    const matrixR = risk.project_risk_analisa.skala_dampak_residual + '-' + risk.project_risk_analisa.skala_probabilitas_residual?.tingkat;
                    if(!mapResidual[matrixR]) mapResidual[matrixR] = [];
                    mapResidual[matrixR].push(riskObj);
                }
            });

            // Terapkan ke DOM HTML
            $('#inherentMap .data-cell').each(function() {
                let matrix = $(this).data('matrix');

                // Simpan data array ke dalam elemen untuk dipanggil di Modal
                $(this).data('risks-inherent', mapInherent[matrix] || []);
                $(this).data('risks-residual', mapResidual[matrix] || []);

                renderCellBadges($(this), mapInherent[matrix], 'box-inherent');
                renderCellBadges($(this), mapResidual[matrix], 'box-residual');
            });
        }

        function updateCurrentData() {
            const selectedMonth = parseInt($('#monthSelect').val());
            const selectedYear = parseInt($('#tahunSelect').val());
            $('#currentMap .kode-peristiwa').empty();

            let unmonitoredRisks = [];
            let mapCurrent = {};

            Object.values(risks).forEach(risk => {
                const riskId = risk.id;
                const tableRow = $(`.table-strategi tbody tr[data-risk-id="${riskId}"]`);
                const riskNumber = tableRow.find('td:first').text().trim();

                const riskMonitorings = risk.project_risk_monitorings || [];
                /* const hasMonitoringThisMonth = riskMonitorings.some(m => parseInt(m.tahun) === selectedYear && parseInt(m.month) === selectedMonth); */
                const hasMonitoringThisMonth = riskMonitorings.some(m =>
                    parseInt(m.tahun) === selectedYear &&
                    parseInt(m.month) === selectedMonth &&
                    (parseInt(m.status) === 100 || m.is_approved == 1 || m.is_approved === true)
                );

                let displayMark = '';
                if (!hasMonitoringThisMonth && riskNumber) {
                    displayMark = '<sup class="text-danger fw-bold ms-1" style="font-size: 0.8rem; top: -0.3em;" data-bs-toggle="tooltip" title="Belum di-update">*</sup>';
                    unmonitoredRisks.push(riskNumber);
                }

                if(risk.project_risk_analisa) {
                    const currentData = formattedCurrentRiskMaps[riskId]?.[selectedYear]?.[selectedMonth - 1];

                    if (currentData && riskNumber) {
                        // Kelompokkan data Current
                        const matrixC = currentData.skala_dampak + '-' + currentData.skala_probabilitas;
                        if(!mapCurrent[matrixC]) mapCurrent[matrixC] = [];

                        let riskObj = { ...risk, riskNumber: riskNumber, displayMark: displayMark, currentData: currentData };
                        mapCurrent[matrixC].push(riskObj);

                        // Update baris tabel
                        if (tableRow.length) {
                            const levelClass = (currentData.level_risiko || '').toLowerCase().replace(/ /g, '-').replace('to-', '');
                            tableRow.find('.realisasi-nilai-dampak').html(currentData.nilai_dampak_formatted);
                            tableRow.find('.realisasi-skala-dampak').html(currentData.skala_dampak_obj?.tingkat || '-');
                            tableRow.find('.realisasi-nilai-probabilitas').html((currentData.nilai_probabilitas ? currentData.nilai_probabilitas + '%' : '-'));
                            tableRow.find('.realisasi-skala-probabilitas').html(currentData.skala_probabilitas_obj?.tingkat || '-');
                            tableRow.find('.realisasi-nilai-risiko').html(currentData.nilai_risiko || '-');

                            tableRow.find('.realisasi-level-risiko').html((currentData.level_risiko || '-') + displayMark)
                                .removeClass('bg-high bg-moderate-high bg-moderate bg-low-moderate bg-low').addClass('bg-' + levelClass);
                        }
                    } else if (tableRow.length) {
                        tableRow.find('.realisasi-nilai-dampak, .realisasi-skala-dampak, .realisasi-nilai-probabilitas, .realisasi-skala-probabilitas, .realisasi-nilai-risiko, .realisasi-level-risiko').html('-');
                        tableRow.find('.realisasi-level-risiko').removeClass('bg-high bg-moderate-high bg-moderate bg-low-moderate bg-low');
                    }
                }
            });

            // Terapkan ke DOM HTML Current Map
            $('#currentMap .data-cell').each(function() {
                let matrix = $(this).data('matrix');

                // Simpan data array ke dalam elemen untuk dipanggil di Modal
                $(this).data('risks-current', mapCurrent[matrix] || []);
                renderCellBadges($(this), mapCurrent[matrix], 'box-current');
            });

            // Tampilkan list unmonitored risk di UI Alert Info
            if (unmonitoredRisks.length > 0) {
                $('#unmonitored-info').removeClass('d-none');
                $('#unmonitored-list').text(unmonitoredRisks.join(', '));
            } else {
                $('#unmonitored-info').addClass('d-none');
            }

            $('#info-month').text($('#monthSelect option:selected').text().trim());
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        populateInherentMap();
        updateCurrentData();

        $('#monthSelect, #tahunSelect').on('change', updateCurrentData);

        // =========================================================
        // KLIK PADA CELL PETA RISIKO UNTUK MEMBUKA MODAL
        // =========================================================
        $('.data-cell').on('click', function() {
            let isCurrentMap = $(this).closest('#currentMap').length > 0;
            let matrix = $(this).data('matrix');
            let levelText = $(this).find('.posisi-risiko').text() || '-';

            let tbody = $('#tableModalRisiko tbody');
            tbody.empty();
            let hasData = false;

            // Fungsi untuk membuat baris tabel modal
            const createModalRow = (r, type) => {
                let route = `${baseUrl}/${r.project_periode_list_id}/risks/${r.id}/view`;
                let badgeClass = type === 'Inherent' ? 'bg-light text-dark border' : (type === 'Residual' ? 'bg-dark text-white' : 'bg-primary text-white');

                // Pastikan Nilai Dampak terformat dengan benar
                let nilaiDampak = 'Rp 0';
                if (type === 'Current' && r.currentData) {
                    nilaiDampak = r.currentData.nilai_dampak_formatted || 'Rp 0';
                } else {
                    nilaiDampak = r.project_risk_analisa?.nilai_dampak
                                ? 'Rp ' + parseInt(r.project_risk_analisa.nilai_dampak).toLocaleString('id-ID')
                                : 'Rp 0';
                }

                // --- PERBAIKAN LOGIKA NAMA PERISTIWA ---
                let namaPeristiwa = '-';

                if (r.peristiwa_risiko_id === 0 || r.peristiwa_risiko_id === "0") {
                    namaPeristiwa = r.rencana_kegiatan || '-';
                } else {
                    // Cek satu per satu: utamakan title, jika tidak ada cek peristiwa_risiko (pastikan string)
                    if (r.peristiwaRisiko && typeof r.peristiwaRisiko === 'object' && r.peristiwaRisiko.title) {
                        namaPeristiwa = r.peristiwaRisiko.title;
                    } else if (r.peristiwa_risiko && typeof r.peristiwa_risiko === 'string') {
                        namaPeristiwa = r.peristiwa_risiko;
                    } else if (r.peristiwa_risiko && typeof r.peristiwa_risiko === 'object' && r.peristiwa_risiko.title) {
                        // Terkadang di JSON, peristiwa_risiko malah jadi object relasi
                        namaPeristiwa = r.peristiwa_risiko.title;
                    }
                }
                // Jika masih object, paksa jadi string kosong agar tidak muncul [object Object]
                if (typeof namaPeristiwa === 'object') namaPeristiwa = '-';

                return `
                    <tr>
                        <td class="text-center">
                            <div class="fw-bold mb-1">${r.riskNumber}</div>
                            <span class="badge ${badgeClass} w-100">${type}</span>
                        </td>
                        <td>${namaPeristiwa}</td>
                        <td class="text-end fw-medium">${nilaiDampak}</td>
                        <td class="text-center">
                            <a href="${route}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bx bx-link-external me-1"></i>Detail</a>
                        </td>
                    </tr>
                `;
            };

            if (isCurrentMap) {
                let currentRisks = $(this).data('risks-current') || [];
                if(currentRisks.length > 0) {
                    hasData = true;
                    currentRisks.forEach(r => tbody.append(createModalRow(r, 'Current')));
                }
            } else {
                let inherentRisks = $(this).data('risks-inherent') || [];
                let residualRisks = $(this).data('risks-residual') || [];

                if(inherentRisks.length > 0 || residualRisks.length > 0) hasData = true;

                inherentRisks.forEach(r => tbody.append(createModalRow(r, 'Inherent')));
                residualRisks.forEach(r => tbody.append(createModalRow(r, 'Residual')));
            }

            // Jika ada data di cell tersebut, buka modal
            if (hasData) {
                $('#modalRiskLevel').text(`${levelText} (Impact: ${matrix.split('-')[0]}, Likelihood: ${matrix.split('-')[1]})`);
                $('#modalPetaRisiko').modal('show');
            }
        });

    @endif

    function fillEfektivitasChart(data) {
        var chartDom = document.getElementById('efektivitas-perlakuan-chart');
        if (!chartDom) return;

        var myChart = echarts.init(chartDom);
        var option;

        option = {
            tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
            series: [{
                name: 'Efektivitas Perlakuan',
                type: 'pie',
                radius: '80%',
                center: ['50%', '50%'],
                data: data.map(function(item) {
                    return {
                        value: item.value,
                        name: item.label,
                        itemStyle: { color: item.color }
                    }
                }),
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                },
                label: {
                    show: true,
                    position: 'inside',
                    formatter: function(params) {
                        return params.value > 0 ? `${params.percent}%` : '';
                    },
                    fontSize: '16',
                    fontWeight: 'bold',
                    color: '#FFF'
                },
            }]
        };

        if (myChart) {
            myChart.setOption(option, true);
            $(window).on('resize', function(){
                myChart.resize();
            });
        }
    }

    const efektivitasData = @json($efektivitasPerlakuanData);
    if (efektivitasData.some(item => item.value > 0)) {
        fillEfektivitasChart(efektivitasData);
    } else {
        $('#efektivitas-perlakuan-chart').html('<div class="d-flex justify-content-center align-items-center h-100 text-muted border rounded">Belum ada data efektivitas.</div>');
    }
});
</script>
@endpush
