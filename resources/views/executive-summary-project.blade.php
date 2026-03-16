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
                            <h6 class="fw-bold text-success-emphasis mb-0">Hasil Usaha s/d Bulan {{ $formattedPeriod }}</h6>
                            <small class="text-muted">(LSP Realisasi - LED)</small>
                        </div>
                        <span class="fw-bold fs-4 text-success">Rp {{ number_format($summaryData['lsp_realisasi_incl_led'], 0, ',', '.') }}</span>
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

    <div class="card">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="h3 mb-0">Peta Risiko</span>
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
                {{-- PETA RISIKO INHEREN & RESIDUAL --}}
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col align-items-center d-flex"><h3 class="h4">Peta Risiko Inheren dan Residual</h3></div>
                    </div>
                    <div class="table-risk-map" id="inherentMap">
                        <table class="map-table">
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
                        <div class="risk-map-legend d-flex flex-center gap-3">
                            <div class="d-flex align-items-center gap-1"><i class='bx bx-circle inherent'></i> Inherent</div>
                            <div class="d-flex align-items-center gap-1"><i class='bx bxs-circle residual'></i> Residual</div>
                        </div>
                    </div>
                </div>
                {{-- PETA RISIKO TERKINI (CURRENT) --}}
                <div class="col-md-6">
                    <div class="row mb-3 align-items-center">
                        <div class="col"><h3 class="h4">Peta Risiko Terkini (Current)</h3></div>
                        <div class="col">
                            <select class="form-select" id="monthSelect">
                                @for ($month = 1; $month <= 12; $month++)
                                    <option value="{{ $month }}" {{ $month == (int) \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->format('m') ? 'selected' : '' }}>
                                        Q{{ ceil($month / 3) }} - {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-select" id="tahunSelect">
                                @foreach ($tahunMonitorings as $tahun)
                                <option value="{{ $tahun }}" {{ $tahun == \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->format('Y') ? 'selected' : '' }}>{{ $tahun }}</option>
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
                        <div class="risk-map-legend d-flex flex-center gap-3">
                            <div class="d-flex align-items-center gap-1"><i class="bx bxs-circle current"></i> Current</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
                <h3 class="h4 mb-0">Daftar Risiko</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm table-strategi">
                    <thead class="text-center align-middle">
                        <tr>
                            <th rowspan="2">Kode</th>
                            <th rowspan="2" style="min-width: 200px;">Peristiwa Risiko</th>
                            <th colspan="6">Inherent</th>
                            <th colspan="6">Residual</th>
                            <th colspan="6">Realisasi (Current)</th>
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
                            <th style="min-width: 120px;">Nilai Dampak</th>
                            <th>Skala Dampak</th>
                            <th style="min-width: 100px;">Nilai Probabilitas</th>
                            <th>Skala Probabilitas</th>
                            <th>Nilai Risiko</th>
                            <th>Level Risiko</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($openRisks as $projectRisk)
                        <tr data-risk-id="{{ $projectRisk->id }}">
                            <td class="text-start fw-bold">
                              <a href="{{  route('projects.risks.view', ['project' => $projectRisk->project_periode_list_id, 'risk' => $projectRisk->id]) }}">
                                R{{ $loop->iteration }}
                              </a>
                            </td>
                            <td class="text-start">{{ $projectRisk->peristiwaRisiko?->title ?? '-' }}</td>

                            {{-- =================== Inherent Risk Data =================== --}}
                            <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak, 0, ',', '.') : 'Rp 0' }}</td>
                            <td class="text-center">{{ optional(optional($projectRisk->projectRiskAnalisa)->skalaDampakObj)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ optional($projectRisk->projectRiskAnalisa)->nilai_probabilitas ? optional($projectRisk->projectRiskAnalisa)->nilai_probabilitas . '%' : '-' }}</td>
                            <td class="text-center">{{ optional(optional($projectRisk->projectRiskAnalisa)->skalaProbabilitas)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ optional($projectRisk->projectRiskAnalisa)->skala_risiko ?? '-' }}</td>
                            <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($projectRisk->projectRiskAnalisa?->level_risiko)))}}">{{ $projectRisk->projectRiskAnalisa?->level_risiko ?? '-' }}</td>

                            {{-- =================== Residual Risk Data =================== --}}
                            <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak_residual ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak_residual, 0, ',', '.') : 'Rp 0' }}</td>
                            <td class="text-center">{{ optional(optional($projectRisk->projectRiskAnalisa)->skalaDampakResidualObj)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ optional($projectRisk->projectRiskAnalisa)->nilai_probabilitas_residual ? optional($projectRisk->projectRiskAnalisa)->nilai_probabilitas_residual . '%' : '-' }}</td>
                            <td class="text-center">{{ optional(optional($projectRisk->projectRiskAnalisa)->skalaProbabilitasResidual)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ optional($projectRisk->projectRiskAnalisa)->skala_risiko_residual ?? '-' }}</td>
                            <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($projectRisk->projectRiskAnalisa?->level_risiko_residual)))}}">{{ $projectRisk->projectRiskAnalisa?->level_risiko_residual ?? '-' }}</td>

                            {{-- =================== Realisasi (Current) - Diisi oleh JavaScript =================== --}}
                            <td class="realisasi-nilai-dampak">-</td>
                            <td class="realisasi-skala-dampak text-center">-</td>
                            <td class="realisasi-nilai-probabilitas text-center">-</td>
                            <td class="realisasi-skala-probabilitas text-center">-</td>
                            <td class="realisasi-nilai-risiko text-center">-</td>
                            <td class="realisasi-level-risiko">-</td>
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
                    <thead class="text-center align-middle">
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
                            <div class="border rounded-3 p-3 mb-3 w-100">
                                @foreach ($efektivitasPerlakuanData as $item)
                                    <div class="mb-1 d-flex align-items-center gap-2">
                                        <span class="d-inline-block" style="width:20px; height:20px; border-radius: 3px; background-color: {{$item['color']}}"></span>
                                        <span>{{ $item['label'] }} ({{ $item['value'] }})</span>
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
                    <div class="accordion" id="accordionEfektivitas">
                        {{-- Accordion untuk Risiko Efektif --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingEfektif">
                                <button class="accordion-button fs-6 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEfektif" aria-expanded="true" aria-controls="collapseEfektif">
                                    Perlakuan Efektif
                                    <span class="badge rounded-pill ms-2" style="background-color: #5470C6">{{ $efektifRisks->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapseEfektif" class="accordion-collapse collapse show" aria-labelledby="headingEfektif">
                                <div class="accordion-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @forelse($efektifRisks as $risk)
                                            <a href="{{ route('projects.risks.view', ['project' => $risk->project_periode_list_id, 'risk' => $risk->id]) }}" target="_blank" class="list-group-item list-group-item-action">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span>{{ optional($risk->peristiwaRisiko)->title ?? $risk->rencana_kegiatan }} - {{$risk->deskripsi_peristiwa_risiko }}</span>
                                                    <span class="badge bg-light text-dark">Nilai: {{ $risk->efektivitas_perlakuan_risiko ?? 0 }}</span>
                                                </div>
                                            </a>
                                        @empty
                                            <li class="list-group-item">Tidak ada risiko yang dinilai efektif.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        {{-- Accordion untuk Risiko Tidak Efektif --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTidakEfektif">
                                <button class="accordion-button fs-6 py-2 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTidakEfektif" aria-expanded="false" aria-controls="collapseTidakEfektif">
                                    Perlakuan Tidak Efektif
                                    <span class="badge rounded-pill bg-danger ms-2">{{ $tidakEfektifRisks->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapseTidakEfektif" class="accordion-collapse collapse" aria-labelledby="headingTidakEfektif">
                                <div class="accordion-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @forelse($tidakEfektifRisks as $risk)
                                            <a href="{{ route('projects.risks.view', ['project' => $risk->project_periode_list_id, 'risk' => $risk->id]) }}" target="_blank" class="list-group-item list-group-item-action">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span>{{ optional($risk->peristiwaRisiko)->title ?? 'Risiko ID: '.$risk->id }}</span>
                                                    <span class="badge bg-light text-dark">Nilai: {{ $risk->efektivitas_perlakuan_risiko ?? 0 }}</span>
                                                </div>
                                            </a>
                                        @empty
                                            <li class="list-group-item">Tidak ada risiko yang dinilai tidak efektif.</li>
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
                <table class="table table-hover table-sm">
                    <thead>
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
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($event->project)->project_name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($event->tanggal_kejadian)->format('d M Y') }}</td>
                                <td>{{ $event->nama_kejadian ?? '-' }}</td>
                                <td>{{ optional($event->peristiwaRisiko)->title ?? '-' }}</td>
                                <td>{{ optional($event->kategoriKejadian)->kategori_kejadian ?? '-' }}</td>
                                <td class="text-end text-danger fw-bold">Rp {{ number_format($event->nilai_kerugian_finansial, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ url('project-led/' . $event->id) }}" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class='bx bx-show'></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center p-4">
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
    <div class="alert alert-info text-center mt-5" role="alert">
        <strong>Pilih Proyek</strong> untuk menampilkan Executive Summary dan detail risiko.
    </div>
@endif

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
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
    background-color: #ffffff;
    color: #000000;
    border: 1px solid #000000; /* Border hitam agar terlihat jelas */
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 0.75rem;
    line-height: 1;
}

.box-residual {
    background-color: #000000;
    color: #ffffff;
    border: 1px solid #000000;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 0.75rem;
    line-height: 1;
}

.box-current {
    background-color: #007bff;
    color: #ffffff;
    border: 1px solid #007bff;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 0.75rem;
    line-height: 1;
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

    // Handler untuk perubahan Divisi
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

                        // Auto-select jika hanya ada 1 proyek
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

    @if ($selectedProjectId && !$projectRisksJs->isEmpty())
        const risks = @json($projectRisksJs);
        const formattedCurrentRiskMaps = @json($formattedCurrentRiskMaps);

        function populateInherentMap() {
            Object.values(risks).forEach(risk => {
                // [FIX] Cek level risiko inheren sebelum menampilkan di peta
                if(risk.project_risk_analisa) {
                    // Tampilkan di Peta Inheren
                    const matrixI = risk.project_risk_analisa.skala_dampak + '-' + risk.project_risk_analisa.skala_probabilitas?.tingkat;
                    const cellI = $(`#inherentMap .data-cell[data-matrix="${matrixI}"]`);
                    if (cellI.length) {
                        // Menggunakan nomor urut dari tabel
                        const riskNumber = $(`.table-strategi tbody tr[data-risk-id="${risk.id}"]`).find('td:first').text().trim();
                        if(riskNumber) {
                          cellI.find('.kode-peristiwa').append(`<span class="box-inherent">${riskNumber}</span>`);
                        }
                    }

                    // Tampilkan di Peta Residual
                    const matrixR = risk.project_risk_analisa.skala_dampak_residual + '-' + risk.project_risk_analisa.skala_probabilitas_residual?.tingkat;
                    const cellR = $(`#inherentMap .data-cell[data-matrix="${matrixR}"]`);
                    if (cellR.length) {
                        const riskNumber = $(`.table-strategi tbody tr[data-risk-id="${risk.id}"]`).find('td:first').text().trim();
                        if(riskNumber) {
                          cellR.find('.kode-peristiwa').append(`<span class="box-residual">${riskNumber}</span>`);
                        }
                    }
                }
            });
        }

        function updateCurrentData() {
            const selectedMonth = $('#monthSelect').val();
            const selectedYear = $('#tahunSelect').val();
            $('#currentMap .kode-peristiwa').empty();

            Object.values(risks).forEach(risk => {
                const riskId = risk.id;
                const tableRow = $(`.table-strategi tbody tr[data-risk-id="${riskId}"]`);
                const riskNumber = tableRow.find('td:first').text().trim();

                // [FIX] Cek level risiko inheren sebelum menampilkan di peta dan tabel
                if(risk.project_risk_analisa) {
                    const currentData = formattedCurrentRiskMaps[riskId]?.[selectedYear]?.[selectedMonth - 1];

                    if (currentData && riskNumber) {
                        // Tampilkan di Peta Current
                        const matrixC = currentData.skala_dampak + '-' + currentData.skala_probabilitas;
                        const cellC = $(`#currentMap .data-cell[data-matrix="${matrixC}"]`);
                        if (cellC.length) {
                            cellC.find('.kode-peristiwa').append(`<span class="box-current">${riskNumber}</span>`);
                        }

                        // Update baris tabel
                        if (tableRow.length) {
                            const levelClass = (currentData.level_risiko || '').toLowerCase().replace(/ /g, '-').replace('to-', '');
                            tableRow.find('.realisasi-nilai-dampak').html(currentData.nilai_dampak_formatted);
                            tableRow.find('.realisasi-skala-dampak').html(currentData.skala_dampak_obj?.tingkat || '-');
                            tableRow.find('.realisasi-nilai-probabilitas').html((currentData.nilai_probabilitas ? currentData.nilai_probabilitas + '%' : '-'));
                            tableRow.find('.realisasi-skala-probabilitas').html(currentData.skala_probabilitas_obj?.tingkat || '-');
                            tableRow.find('.realisasi-nilai-risiko').html(currentData.nilai_risiko || '-');
                            tableRow.find('.realisasi-level-risiko').html(currentData.level_risiko || '-')
                                .removeClass('bg-high bg-moderate-high bg-moderate bg-low-moderate bg-low').addClass('bg-' + levelClass);
                        }
                    } else if (tableRow.length) {
                        // Kosongkan data jika tidak ada data current untuk periode terpilih
                        tableRow.find('.realisasi-nilai-dampak, .realisasi-skala-dampak, .realisasi-nilai-probabilitas, .realisasi-skala-probabilitas, .realisasi-nilai-risiko, .realisasi-level-risiko').html('-');
                        tableRow.find('.realisasi-level-risiko').removeClass('bg-high bg-moderate-high bg-moderate bg-low-moderate bg-low');
                    }
                }
            });
        }

        populateInherentMap();
        updateCurrentData();

        $('#monthSelect, #tahunSelect').on('change', updateCurrentData);
    @endif

    function fillEfektivitasChart(data) {
        var chartDom = document.getElementById('efektivitas-perlakuan-chart');
        if (!chartDom) return; // Hentikan jika elemen tidak ditemukan

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
                        // Hanya tampilkan persentase jika nilainya > 0
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
            // Resize chart saat ukuran window berubah
            $(window).on('resize', function(){
                myChart.resize();
            });
        }
    }

    // Ambil data dari controller dan panggil fungsi chart
    const efektivitasData = @json($efektivitasPerlakuanData);

    // Panggil fungsi hanya jika ada data untuk ditampilkan
    if (efektivitasData.some(item => item.value > 0)) {
        fillEfektivitasChart(efektivitasData);
    } else {
        // Jika semua nilai 0, tampilkan pesan
        $('#efektivitas-perlakuan-chart').html('<div class="d-flex justify-content-center align-items-center h-100 text-muted">Belum ada data efektivitas.</div>');
    }
});
</script>
@endpush
