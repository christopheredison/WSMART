@extends('layouts.default')

@section('dashboard')
<div class="row mb-7">
    <div class="col-12">
        <div class="card border-0 dashboard-header">
            <img src="{{ asset('assets/img/dashboard-header.webp') }}" alt="dashboard">
            <div class="card-header border-0 justify-content-end">
                <h1 class="mb-0">Executive Summary Divisi</h1>
                <h4 id="selected-unit-name" class="mb-4">{{ $selectedUnit ? $selectedUnit->name : '' }}</h4>
                <h6>Statistik per tanggal {{ now()->format('d M Y') }}</h6>
            </div>
        </div>
    </div>
</div>
<div class="row input-selector-rounded g-3 mb-5">
    <div class="col-12">
        <div class="card p-3 shadow-sm">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="period_selector" class="form-label fw-bold">Pilih Periode</label>
                    <input type="text" name="period" id="period_selector" class="form-control" placeholder="Pilih Bulan & Tahun" value="{{ $selectedPeriod }}">
                </div>
                <div class="col-md-6">
                    <label for="unit_selector" class="form-label fw-bold">Pilih Divisi</label>
                    <select name="unit_id" id="unit_selector" class="form-select select2">
                        <option value="">Pilih Divisi</option>
                        @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" {{ $unit->id == $selectedUnitId ? 'selected' : '' }}>
                            {{ $unit->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($selectedUnitId)
    @php
        $formattedPeriod = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y');
        $currentYear = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->year;
    @endphp

    {{-- ========================================================================= --}}
    {{-- ================= SECTION KINERJA BERBASIS RISIKO ================= --}}
    {{-- ========================================================================= --}}
    <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-shield-alt me-2"></i>Manajemen Kinerja Berbasis Risiko</h2>
    <hr class="mb-4">
    @php
        $formattedPeriod = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y');
        $currentYear = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->year;
    @endphp
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary-subtle">
                    <h5 class="mb-0 fw-bold text-primary-emphasis"><i class="fas fa-calendar-day me-2"></i>Hasil Usaha s/d {{ $formattedPeriod}}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">{{ $isProjectUnit ? 'Omset Penjualan' : 'Biaya Usaha' }}</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['omset_penjualan_sd_bulan'], 0, ',', '.') }}</span>
                        </li>
                        @if ($isProjectUnit)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">LSP Rencana</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['lsp_rencana_sd_bulan'], 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">LSP Realisasi</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['lsp_realisasi_sd_bulan'], 0, ',', '.') }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-flag-checkered me-2"></i>Proyeksi Hasil Usaha s/d Desember {{ $currentYear }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">{{ $isProjectUnit ? 'Omset Penjualan' : 'Biaya Usaha' }}</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['omset_penjualan_sd_des'], 0, ',', '.') }}</span>
                        </li>
                        @if ($isProjectUnit)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">LSP Rencana</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['lsp_rencana_sd_des'], 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Proyeksi LSP</span>
                            <span class="fw-medium fs-5">Rp {{ number_format($summaryData['proyeksi_lsp_sd_des'], 0, ',', '.') }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6 d-flex flex-column">
            @if ($isProjectUnit)
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-database me-2"></i>Loss Event Database (LED) Proyek</h5></div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted">Total Kerugian Finansial</span>
                        <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_proyek_total'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endif
            <div class="card shadow-sm flex-grow-1">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-building me-2"></i>Loss Event Database (LED) Divisi</h5></div>
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted">Total Kerugian Finansial</span>
                        <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_divisi_total'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Eksposur Risiko Residual</h5></div>
                <div class="card-body py-2">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Realisasi Total s/d {{ $formattedPeriod }}</span>
                            <span class="fw-bold fs-5 text-warning">Rp {{ number_format($summaryData['eksposur_risiko_total'], 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Proyeksi Annual {{ $currentYear }}</span>
                            <span class="fw-bold fs-5 text-warning">Rp {{ number_format($summaryData['eksposur_risiko_annual'], 0, ',', '.') }}</span>
                        </li>
                    </ul>
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
                            <h6 class="fw-bold text-success-emphasis mb-0">Hasil Usaha Aktual s/d {{ $formattedPeriod }}</h6>
                            @if($isProjectUnit) <small class="text-muted">(LSP Realisasi - LED Proyek - LED Divisi)</small>
                            @else <small class="text-muted">(Biaya Usaha - LED Divisi)</small> @endif
                        </div>
                        <span class="fw-bold fs-4 text-success">Rp {{ number_format($summaryData['hasil_usaha_sd_bulan'], 0, ',', '.') }}</span>
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
                            @if($isProjectUnit) <small class="text-muted">(Proyeksi LSP - Eksposur Annual)</small>
                            @else <small class="text-muted">(Biaya Usaha - Eksposur Annual)</small> @endif
                        </div>
                        <span class="fw-bold fs-4 text-primary">Rp {{ number_format($summaryData['proyeksi_hasil_usaha_sd_des'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- ======================== SECTION PROFIL RISIKO ======================== --}}
    {{-- ========================================================================= --}}
    <h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-chart-pie me-2"></i>Profil Risiko</h2>
    <hr class="mb-4">
    <div class="card">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="h3 mb-0">Peta Risiko Divisi</span>
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
                        <div class="col align-items-center d-flex"><h3 class="h4">Peta Risiko Inheren dan Residual</h3></div>
                    </div>
                    <div class="table-risk-map" id="inherentMap">
                        {{-- KODE PETA RISIKO DIMULAI DI SINI --}}
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
                                            <div class="data-cell {{strtolower(str_replace(' ', '-', optional($riskMap)['level_risiko']))}}" data-matrix='{{ $impact }}-{{ $likelihood }}'>
                                                <div class="kode-peristiwa"></div>
                                                <div class="posisi-risiko">{{ optional($riskMap)['nilai_risiko'] }}</div>
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
                        {{-- KODE PETA RISIKO SELESAI --}}
                    </div>
                </div>
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
                        {{-- KODE PETA RISIKO DIMULAI DI SINI --}}
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
                                            <div class="data-cell {{strtolower(str_replace(' ', '-', optional($riskMap)['level_risiko']))}}" data-matrix='{{ $impact }}-{{ $likelihood }}'>
                                                <div class="kode-peristiwa"></div>
                                                <div class="posisi-risiko">{{ optional($riskMap)['nilai_risiko'] }}</div>
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
                <h3 class="h4 mb-0">Daftar Risiko (Level Inheren: Moderate to High & High)</h3>
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
                        @forelse($highImpactRisks as $risk)
                        <tr data-risk-id="{{ $risk->id }}">
                            <td class="text-center fw-bold">
                              <a href="{{  route('risk-register-unit.view', ['riskRegister' => $risk->id]) }}">
                                R{{ $loop->iteration }}
                              </a>
                            </td>
                            <td>{{ optional($risk->peristiwaRisiko)->title ?? $risk->peristiwa_risiko }}</td>
                            {{-- Inherent --}}
                            <td>{{ optional($risk->riskAnalysis)->nilai_dampak ? 'Rp ' . number_format(optional($risk->riskAnalysis)->nilai_dampak, 0, ',', '.') : 'Rp 0' }}</td>
                            <td class="text-center">{{ optional(optional($risk->riskAnalysis)->skalaDampakObj)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ optional($risk->riskAnalysis)->nilai_probabilitas ? optional($risk->riskAnalysis)->nilai_probabilitas . '%' : '-' }}</td>
                            <td class="text-center">{{ optional(optional($risk->riskAnalysis)->skalaProbabilitas)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ optional($risk->riskAnalysis)->skala_risiko ?? '-' }}</td>
                            <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($risk->riskAnalysis?->level_risiko)))}}">{{ $risk->riskAnalysis?->level_risiko ?? '-' }}</td>

                            {{-- Residual --}}
                            @php
                                $nilai_dampak_residual = optional($risk->riskAnalysis)->{'nilai_dampak_residual_q'.$currentQuarter};
                                $skala_prob_residual = optional($risk->riskAnalysis)->{'skalaProbabilitasResidualQ'.$currentQuarter};
                                $skala_dampak_residual_obj = optional($risk->riskAnalysis)->{'skalaDampakResidualQ'.$currentQuarter.'Obj'};
                                $nilai_prob_residual = optional($risk->riskAnalysis)->{'nilai_probabilitas_residual_q'.$currentQuarter};
                                $skala_risiko_residual = optional($risk->riskAnalysis)->{'skala_risiko_residual_q'.$currentQuarter};
                                $level_risiko_residual = optional($risk->riskAnalysis)->{'level_risiko_residual_q'.$currentQuarter};
                            @endphp
                            <td>{{ $nilai_dampak_residual ? 'Rp ' . number_format($nilai_dampak_residual, 0, ',', '.') : 'Rp 0' }}</td>
                            <td class="text-center">{{ optional($skala_dampak_residual_obj)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ $nilai_prob_residual ? $nilai_prob_residual . '%' : '-' }}</td>
                            <td class="text-center">{{ optional($skala_prob_residual)->tingkat ?? '-' }}</td>
                            <td class="text-center">{{ $skala_risiko_residual ?? '-' }}</td>
                            <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($level_risiko_residual)))}}">{{ $level_risiko_residual ?? '-' }}</td>

                            {{-- Realisasi --}}
                            <td class="realisasi-nilai-dampak">-</td>
                            <td class="realisasi-skala-dampak text-center">-</td>
                            <td class="realisasi-nilai-probabilitas text-center">-</td>
                            <td class="realisasi-skala-probabilitas text-center">-</td>
                            <td class="realisasi-nilai-risiko text-center">-</td>
                            <td class="realisasi-level-risiko text-center">-</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="20" class="text-center p-4">Tidak ada data risiko dengan level 'Moderate to High' atau 'High'.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- ======================== SECTION KEY RISK INDICATOR (KRI) ======================= --}}
    {{-- ========================================================================= --}}
    <h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-tachometer-alt me-2"></i>Key Risk Indicator (KRI)</h2>
    <hr class="mb-4">
    <div class="card">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="h3 mb-0">Daftar KRI (Status Waspada & Bahaya)</span>
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
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sortedKriData as $kri)
                        <tr>
                            <td>{{ $kri['risiko'] }}</td>
                            <td>
                                @if(!empty($kri['penyebab']) && is_array($kri['penyebab']))
                                    <ul class="list-unstyled mb-0 ps-3">
                                        @foreach($kri['penyebab'] as $penyebab)
                                            <li>- {{ $penyebab }}</li>
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
                            <td class="text-center">
                                @php
                                    $statusClass = '';
                                    $statusNumeric = $kri['status'] ?? 0;
                                    if ($statusNumeric == 3) { $statusClass = 'red'; } 
                                    elseif ($statusNumeric == 2) { $statusClass = 'yellow'; } 
                                    elseif ($statusNumeric == 1) { $statusClass = 'green'; }
                                @endphp
                                <div class="status-container {{ $statusClass }}">
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
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- ================= SECTION EFEKTIVITAS PERLAKUAN RISIKO ================== --}}
    {{-- ========================================================================= --}}
    <h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-check-circle me-2"></i>Efektivitas Perlakuan Risiko</h2>
    <hr class="mb-4">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 d-flex flex-between-center">
                    <h3 class="h4">Ringkasan Efektivitas</h3>
                </div>
                <div class="card-body">
                    <div class="border rounded-3 p-3 mb-3 w-100">
                        @foreach ($efektivitasPerlakuanData as $item)
                            <div class="mb-1 d-flex align-items-center gap-2">
                                <span class="d-inline-block" style="width:20px; height:20px; border-radius: 3px; background-color: {{$item['color']}}"></span>
                                <span>{{ $item['label'] }} ({{ $item['value'] }})</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="min-vh-25">
                        <div id="efektivitas-perlakuan-chart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h3 class="h4">Detail Risiko Selesai (Closed)</h3>
                </div>
                <div class="card-body">
                    <div class="accordion" id="accordionEfektivitas">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingEfektif">
                                <button class="accordion-button fs-6 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEfektif" aria-expanded="true">
                                    Perlakuan Efektif <span class="badge rounded-pill bg-info ms-2">{{ $efektifRisks->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapseEfektif" class="accordion-collapse collapse show" aria-labelledby="headingEfektif">
                                <div class="accordion-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @forelse($efektifRisks as $risk)
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <div class="d-flex justify-content-between align-items-center w-100">
                                                    <span>{{ optional($risk->peristiwaRisiko)->title ?? 'Risiko ID: '.$risk->id }}</span>
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
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTidakEfektif">
                                <button class="accordion-button fs-6 py-2 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTidakEfektif" aria-expanded="false">
                                    Perlakuan Tidak Efektif <span class="badge rounded-pill bg-danger ms-2">{{ $tidakEfektifRisks->count() }}</span>
                                </button>
                            </h2>
                            <div id="collapseTidakEfektif" class="accordion-collapse collapse" aria-labelledby="headingTidakEfektif">
                                <div class="accordion-body p-0">
                                    <ul class="list-group list-group-flush">
                                        @forelse($tidakEfektifRisks as $risk)
                                            <a href="#" class="list-group-item list-group-item-action">
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

    {{-- ========================================================================= --}}
    {{-- =================== SECTION TOP LISTS (LED & EKSPOSUR) ================== --}}
    {{-- ========================================================================= --}}
    <h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-list-ol me-2"></i>Ringkasan Top Risiko</h2>
    <hr class="mb-4">
    <div class="row g-4">
        @if($isProjectUnit)
        <div class="col-lg-6">
          <div class="card h-100">
              <div class="card-header">
                  <h5 class="mb-0 fw-bold">Top 5 Loss Event Project ({{$currentYear}})</h5>
              </div>
              <div class="card-body">
                  <ul class="list-group list-group-flush">
                      @forelse($topLedProjects as $item)
                          <li class="list-group-item d-flex justify-content-between align-items-center ps-0">
                              <span>
                                <a href="">
                                  {{ optional($item->project)->project_name }}
                                </a>
                              </span>
                              <span class="badge bg-danger rounded-pill">Rp {{ number_format($item->total_kerugian, 0, ',', '.') }}</span>
                          </li>
                      @empty
                          <li class="list-group-item text-muted ps-0">Tidak ada data.</li>
                      @endforelse
                  </ul>
              </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card h-100">
              <div class="card-header">
                  <h5 class="mb-0 fw-bold">Top 10 Loss Event {{ $isProjectUnit ? 'Divisi Operasi' : 'Divisi Fungsi' }} ({{$currentYear}})</h5>
              </div>
              <div class="card-body">
                  <div class="table-responsive">
                      <table class="table table-hover table-sm">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Nama Kejadian</th>
                                  <th class="text-end">Nilai Kerugian</th>
                              </tr>
                          </thead>
                          <tbody>
                              @forelse ($topLedDivisi as $event)
                                  <tr>
                                      <td>{{ $loop->iteration }}</td>
                                      <td>{{ $event->nama_kejadian ?? '-' }}</td>
                                      <td class="text-end text-danger">Rp {{ number_format($event->nilai_kerugian_finansial, 0, ',', '.') }}</td>
                                  </tr>
                              @empty
                                  <tr>
                                      <td colspan="3" class="text-center text-muted">Tidak ada data.</td>
                                  </tr>
                              @endforelse
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
        </div>
        @else
        <div class="col-12">
          <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">Top 10 Loss Event {{ $isProjectUnit ? 'Divisi Operasi' : 'Divisi Fungsi' }} ({{$currentYear}})</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Kejadian</th>
                                <th class="text-end">Nilai Kerugian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topLedDivisi as $event)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $event->nama_kejadian ?? '-' }}</td>
                                    <td class="text-end text-danger">Rp {{ number_format($event->nilai_kerugian_finansial, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
        @endif
        @if ($isProjectUnit)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">Top 5 Eksposur Risiko Proyek (Annual {{ $currentYear }})</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse($topEksposurAnnual as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0">
                                    <span>
                                      <a href="">
                                        {{ $item->project_name }}
                                      </a>
                                    </span>
                                    <span class="badge bg-warning text-dark rounded-pill">Rp {{ number_format($item->max_eksposur, 0, ',', '.') }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted ps-0">Tidak ada data.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">Top 5 Eksposur Risiko Proyek (Total s/d {{ $formattedPeriod }})</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse($topEksposurTotal as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0">
                                    <span>
                                      <a href="">
                                        {{ $item->project_name }}
                                      </a>
                                    </span>
                                    <span class="badge bg-warning text-dark rounded-pill">Rp {{ number_format($item->total_eksposur, 0, ',', '.') }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted ps-0">Tidak ada data.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <div class="col-lg-6">
              <div class="card h-100">
                  <div class="card-header">
                      <h5 class="mb-0 fw-bold">Top 5 Eksposur Risiko (Annual {{ $currentYear }})</h5>
                  </div>
                  <div class="card-body">
                      <ul class="list-group list-group-flush">
                            @forelse($topEksposurAnnual as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0">
                                    <span>
                                      <a href="">
                                        {{ optional($item->identifikasiRisiko)->peristiwa_risiko ?? 'Risiko ID: '.$item->identifikasi_risiko_id }}
                                      </a>
                                    </span>
                                    <span class="badge bg-warning text-dark rounded-pill">Rp {{ number_format($item->max_eksposur, 0, ',', '.') }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted ps-0">Tidak ada data.</li>
                            @endforelse
                      </ul>
                  </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card h-100">
                  <div class="card-header">
                      <h5 class="mb-0 fw-bold">Top 5 Eksposur Risiko (Total s/d {{ $formattedPeriod }})</h5>
                  </div>
                  <div class="card-body">
                      <ul class="list-group list-group-flush">
                            @forelse($topEksposurTotal as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0">
                                    <span>
                                      <a href="">
                                        {{ optional($item->identifikasiRisiko)->peristiwa_risiko ?? 'Risiko ID: '.$item->identifikasi_risiko_id }}
                                      </a>
                                    </span>
                                    <span class="badge bg-warning text-dark rounded-pill">Rp {{ number_format($item->total_eksposur, 0, ',', '.') }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted ps-0">Tidak ada data.</li>
                            @endforelse
                      </ul>
                  </div>
              </div>
            </div>
        @endif
    </div>
@else
    <div class="alert alert-info text-center mt-5" role="alert">
        <strong>Pilih Divisi</strong> untuk menampilkan Executive Summary.
    </div>
@endif

@endsection


@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<style>
.status-container {
  display:flex;
  justify-content:center;
  gap:4px
}
.status-container div {
  width:20px;
  height:20px;
  border-radius:50%;
  background-color:#e9ecef;
  border:1px solid #ced4da
}
.status-container.green .status-green { 
  background-color:#28a745
}
.status-container.yellow .status-yellow {
  background-color:#ffc107
}
.status-container.red .status-red {
  background-color:#dc3545
}
.kode-peristiwa {
  display:flex;
  flex-wrap:wrap;
  gap:5px;
  position:absolute;
  bottom:5px;
  right:0;
  width:calc(100% - 5px) !important
}
.box-inherent {
  background-color:#fff;
  color:#000;
  padding:2px 5px;
  border-radius:5px
}
.box-residual {
  background-color:#000;
  color:#fff;
  padding:2px 5px;
  border-radius:5px
}
.box-current {
  background-color:#007bff;
  color:#fff;
  padding:2px 5px;
  border-radius:5px
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    // Fungsi filter utama
    function applyFilterAndRefresh() {
        var unitId = $('#unit_selector').val();
        var period = $('#period_selector').val();
        if (unitId) {
            let baseUrl = '{{ url()->current() }}';
            let params = new URLSearchParams();
            params.append('unit_id', unitId);
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
            altInput: true
          })
        ],
        maxDate: "today", defaultDate: "{{ $selectedPeriod }}",
        onChange: function(d,s,i) { applyFilterAndRefresh(); }
    });
    $('#unit_selector').on('change', function() { applyFilterAndRefresh(); });

    // Inisialisasi semua chart dan peta jika ada data
    @if ($selectedUnitId)
        const highImpactRisksJs = @json($highImpactRisksJs); 
        const formattedCurrentRiskMaps = @json($formattedCurrentRiskMaps);
        const highImpactLevels = ['High', 'Moderate to High'];
        const currentYear = '{{ $currentYear }}';
        const currentQuarter = {{ $currentQuarter }};

        function populateInherentMap() {
            Object.values(highImpactRisksJs).forEach(risk => {
                if(risk.risk_analysis) {
                    const riskNumber = 'R' + risk.nomor_urut_js;
                    const matrixI = risk.risk_analysis.skala_dampak + '-' + risk.risk_analysis.skala_probabilitas?.tingkat;
                    const cellI = $(`#inherentMap .data-cell[data-matrix="${matrixI}"]`);
                    if (cellI.length) cellI.find('.kode-peristiwa').append(`<span class="box-inherent">${riskNumber}</span>`);
                    
                    const probResidualRel = risk.risk_analysis['skala_probabilitas_residual_q' + currentQuarter];
                    const probResidualTingkat = probResidualRel ? probResidualRel.tingkat : null;
                    const dampakResidual = risk.risk_analysis['nilai_dampak_residual_q' + currentQuarter];
                    
                    const dampakResidualObj = risk.risk_analysis['skala_dampak_residual_q' + currentQuarter + '_obj'];
                    const dampakResidualTingkat = dampakResidualObj ? dampakResidualObj.tingkat : null;

                    const matrixR = dampakResidualTingkat + '-' + probResidualTingkat;
                    
                    const cellR = $(`#inherentMap .data-cell[data-matrix="${matrixR}"]`);
                    if (cellR.length) cellR.find('.kode-peristiwa').append(`<span class="box-residual">${riskNumber}</span>`);
                }
            });
        }

        function updateCurrentData() {
            const selectedMonth = $('#monthSelect').val();
            const selectedYear = $('#tahunSelect').val();
            $('#currentMap .kode-peristiwa').empty();
            
            Object.values(highImpactRisksJs).forEach(risk => {
                const riskId = risk.id;
                const riskNumber = 'R' + risk.nomor_urut_js;
                const tableRow = $(`.table-strategi tbody tr[data-risk-id="${riskId}"]`);
                const currentData = formattedCurrentRiskMaps[riskId]?.[selectedYear]?.[selectedMonth - 1];

                if (currentData && tableRow.length) {
                    const matrixC = currentData.skala_dampak + '-' + currentData.skala_probabilitas;
                    const cellC = $(`#currentMap .data-cell[data-matrix="${matrixC}"]`);
                    if (cellC.length) cellC.find('.kode-peristiwa').append(`<span class="box-current">${riskNumber}</span>`);

                    const levelClass = (currentData.level_risiko_formatted || '').toLowerCase().replace(/ /g, '-').replace('to-', '');
                    const td = tableRow.find('.realisasi-level-risiko');

                    tableRow.find('.realisasi-nilai-dampak').html(currentData.nilai_dampak_formatted);
                    tableRow.find('.realisasi-skala-dampak').html(currentData.skala_dampak_obj?.tingkat || '-');
                    tableRow.find('.realisasi-nilai-probabilitas').html((currentData.nilai_probabilitas_formatted || '-') + '%');
                    tableRow.find('.realisasi-skala-probabilitas').html(currentData.skala_probabilitas_obj?.tingkat || '-');
                    tableRow.find('.realisasi-nilai-risiko').html(currentData.nilai_risiko_formatted);
                    
                    td.html(currentData.level_risiko_formatted || '-');
                    td.removeClass('bg-high bg-moderate-high bg-moderate bg-low-moderate bg-low');
                    if (levelClass) {
                        td.addClass('bg-' + levelClass);
                    }
                }
            });
        }

        populateInherentMap();
        updateCurrentData();
        $('#monthSelect, #tahunSelect').on('change', updateCurrentData);

        // Logika untuk Chart Efektivitas
        const efektivitasData = @json($efektivitasPerlakuanData);
        function fillEfektivitasChart(data) {
            var chartDom = document.getElementById('efektivitas-perlakuan-chart');
            if (!chartDom) return;
            var myChart = echarts.init(chartDom);
            var option = {
                tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
                series: [{
                    name: 'Efektivitas Perlakuan', type: 'pie', radius: '80%', center: ['50%', '50%'],
                    data: data.map(item => ({ value: item.value, name: item.label, itemStyle: { color: item.color } })),
                    emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0, 0, 0, 0.5)'}},
                    label: {
                        show: true, position: 'inside',
                        formatter: params => (params.value > 0 ? `${params.percent}%` : ''),
                        fontSize: '16', fontWeight: 'bold', color: '#FFF'
                    },
                }]
            };
            myChart.setOption(option, true);
            $(window).on('resize', () => myChart.resize());
        }
        if (efektivitasData.some(item => item.value > 0)) {
            fillEfektivitasChart(efektivitasData);
        } else {
            $('#efektivitas-perlakuan-chart').html('<div class="d-flex justify-content-center align-items-center h-100 text-muted">Tidak ada risiko yang telah ditutup.</div>');
        }
    @endif
});
</script>
@endpush