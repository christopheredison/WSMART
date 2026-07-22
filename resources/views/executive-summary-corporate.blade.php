@extends('layouts.default')

@section('dashboard')
{{-- ========================================================================= --}}
{{-- ============================ HEADER UTAMA =============================== --}}
{{-- ========================================================================= --}}
<div class="row mb-7">
    <div class="col-12">
        <div class="card border-0 dashboard-header shadow-sm">
            <img src="{{ asset('assets/img/dashboard-header.webp') }}" alt="dashboard">
            <div class="card-header border-0 justify-content-end">
                <h1 class="mb-0">Executive Summary Korporat</h1>
                <h6 class="mt-3">Statistik per tanggal {{ now()->translatedFormat('d F Y') }}</h6>
            </div>
        </div>
    </div>
</div>

@php
    $formattedPeriod = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y');
@endphp

{{-- ========================================================================= --}}
{{-- ================= SECTION KINERJA BERBASIS RISIKO ================= --}}
{{-- ========================================================================= --}}
<h2 class="mb-4 text-primary fw-bold"><i class="fas fa-shield-alt me-2"></i>Manajemen Kinerja Berbasis Risiko Korporat</h2>
<hr class="mb-4">

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary-subtle">
                <h5 class="mb-0 fw-bold text-primary-emphasis"><i class="fas fa-calendar-day me-2"></i>Hasil Usaha s/d {{ $formattedPeriod}}</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">Omzet Penjualan</span>
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
                <h5 class="mb-0 fw-bold"><i class="fas fa-flag-checkered me-2"></i>Hasil Usaha s/d Desember {{ $currentYear }}</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">Omset Penjualan</span>
                        <span class="fw-medium fs-5">Rp {{ number_format($summaryData['omset_penjualan_sd_des'], 0, ',', '.') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">LSP Rencana</span>
                        <span class="fw-medium fs-5">Rp {{ number_format($summaryData['lsp_rencana_sd_des'], 0, ',', '.') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">Proyeksi LSP</span>
                        <span class="fw-medium fs-5">Rp {{ number_format($summaryData['proyeksi_lsp_sd_des'], 0, ',', '.') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6 d-flex flex-column">
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-hard-hat me-2"></i>Loss Event Database Divisi / AP</h5></div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">Total Kerugian Finansial</span>
                    <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_divisi_operasi_total'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        {{-- <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-cogs me-2"></i>Loss Event Database Divisi Fungsi</h5></div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">Total Kerugian Finansial</span>
                    <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_divisi_fungsi_total'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div> --}}
        <div class="card shadow-sm">
            <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-project-diagram me-2"></i>Loss Event Database Proyek</h5></div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center px-0 py-2">
                    <span class="text-muted">Total Kerugian Finansial</span>
                    <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_proyek_total'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header"><h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Eksposur Risiko Residual (Unit Korporat)</h5></div>
            <div class="card-body py-2">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">Residual Realisasi Annual {{ $currentYear }}</span>
                        <span class="fw-bold fs-5 text-warning">Rp {{ number_format($summaryData['eksposur_risiko_annual'] ?? 0, 0, ',', '.') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">Residual Residual Total s/d {{ $formattedPeriod }}</span>
                        <span class="fw-bold fs-5 text-warning">Rp {{ number_format($summaryData['eksposur_risiko_total'] ?? 0, 0, ',', '.') }}</span>
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
                        <small class="text-muted">(Biaya Usaha Aktual - Total Kerugian LED)</small>
                    </div>
                    <span class="fw-bold fs-4 text-success">Rp {{ number_format($summaryData['hasil_usaha_aktual'], 0, ',', '.') }}</span>
                    {{-- <span class="fw-bold fs-4 text-success">Rp -46.164.154.604</span> --}}
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
                        <small class="text-muted">(Proyeksi Biaya Usaha - Eksposur Risiko Annual)</small>
                    </div>
                    <span class="fw-bold fs-4 text-primary">Rp {{ number_format($summaryData['proyeksi_hasil_usaha_des'], 0, ',', '.') }}</span>
                    {{-- <span class="fw-bold fs-4 text-primary">Rp 0</span> --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- ======================== SECTION PROFIL RISIKO ======================== --}}
{{-- ========================================================================= --}}
<h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-chart-pie me-2"></i>Profil Risiko Korporat</h2>
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
        <div class="border p-3 mb-3 bg-light rounded d-flex flex-wrap">
            @foreach (['High', 'Moderate to High', 'Moderate', 'Low to Moderate', 'Low'] as $level)
            <div class="me-3 d-inline-flex align-items-center gap-2">
                <span class="d-inline-block bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($level)))}}" style="width:20px; height:20px; border-radius: 3px;"></span>
                <span style="font-size: 0.85rem">{{ $level }}</span>
            </div>
            @endforeach
        </div>

        <div class="row">
            {{-- PETA RISIKO INHEREN & RESIDUAL --}}
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="row mb-3">
                    <div class="col align-items-center d-flex"><h3 class="h4 mb-0">Peta Risiko Inheren dan Residual</h3></div>
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
                                <option value="{{ $month }}" {{ $month == (int) $currentMonth ? 'selected' : '' }}>
                                    Q{{ ceil($month / 3) }} - {{ \Carbon\Carbon::create()->month($month)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-6 col-xl-3">
                        <select class="form-select form-select-sm" id="tahunSelect">
                            @foreach ($tahunMonitorings as $tahun)
                            <option value="{{ $tahun }}" {{ $tahun == $currentYear ? 'selected' : '' }}>{{ $tahun }}</option>
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
                    <div class="risk-map-legend d-flex justify-content-center gap-4 mt-3">
                        <div class="d-flex align-items-center gap-1"><i class="bx bxs-circle current fs-5 text-primary"></i> Current</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ALERT INFO RISIKO BELUM UPDATE --}}
        <div class="alert alert-warning mt-4 mb-0 d-none shadow-sm border-0" id="unmonitored-info">
            <div class="d-flex">
                <i class="bx bx-error-circle fs-2 me-3 mt-1 text-warning"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Informasi Status Realisasi Bulan <span id="info-month" class="text-dark"></span></h6>
                    <p class="mb-2">Risiko dengan tanda bintang merah (<span class="text-danger fw-bold fs-5">*</span>) pada Peta Risiko Current dan Tabel Daftar Risiko menandakan bahwa <strong>risiko tersebut belum dilakukan verifikasi pelaporan monitoring</strong> pada bulan cutoff. Data menggunakan fallback dari bulan sebelumnya.</p>
                    <p class="mb-0"><strong>Risiko yang belum ter-update:</strong> <span id="unmonitored-list" class="badge bg-warning text-dark fw-bold ms-1">-</span></p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
            <h3 class="h4 mb-0">Daftar Risiko Korporat</h3>
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
                        <th style="min-width: 120px;">Nilai Dampak</th><th>Skala Dampak</th><th style="min-width: 100px;">Nilai Probabilitas</th><th>Skala Probabilitas</th><th>Nilai Risiko</th><th>Level Risiko</th>
                        {{-- Residual --}}
                        <th style="min-width: 120px;">Nilai Dampak</th><th>Skala Dampak</th><th style="min-width: 100px;">Nilai Probabilitas</th><th>Skala Probabilitas</th><th>Nilai Risiko</th><th>Level Risiko</th>
                        {{-- Realisasi --}}
                        <th style="min-width: 120px;" class="bg-primary-subtle">Nilai Dampak</th><th class="bg-primary-subtle">Skala Dampak</th><th style="min-width: 100px;" class="bg-primary-subtle">Nilai Probabilitas</th><th class="bg-primary-subtle">Skala Probabilitas</th><th class="bg-primary-subtle">Nilai Risiko</th><th class="bg-primary-subtle">Level Risiko</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($openRisks as $risk)
                    <tr data-risk-id="{{ $risk->id }}">
                        <td class="text-start fw-bold">
                          <a href="{{ route('risk-register-unit.view', ['riskRegister' => $risk->id]) }}" class="text-primary text-decoration-underline" target="_blank">
                            R{{ $loop->iteration }}
                          </a>
                        </td>
                        <td class="text-start">{{ optional($risk->peristiwaRisiko)->title ?? $risk->peristiwa_risiko }}</td>

                        {{-- Inherent --}}
                        <td class="text-end">{{ optional($risk->riskAnalysis)->nilai_dampak ? 'Rp ' . number_format(optional($risk->riskAnalysis)->nilai_dampak, 0, ',', '.') : 'Rp 0' }}</td>
                        <td class="text-center fw-bold">{{ optional(optional($risk->riskAnalysis)->skalaDampakObj)->tingkat ?? '-' }}</td>
                        <td class="text-center">{{ optional($risk->riskAnalysis)->nilai_probabilitas ? optional($risk->riskAnalysis)->nilai_probabilitas . '%' : '-' }}</td>
                        <td class="text-center fw-bold">{{ optional(optional($risk->riskAnalysis)->skalaProbabilitas)->tingkat ?? '-' }}</td>
                        <td class="text-center fw-bold">{{ optional($risk->riskAnalysis)->skala_risiko ?? '-' }}</td>
                        <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($risk->riskAnalysis?->level_risiko)))}} fw-bold">{{ $risk->riskAnalysis?->level_risiko ?? '-' }}</td>

                        {{-- Residual --}}
                        @php
                            $nilai_dampak_residual = optional($risk->riskAnalysis)->{'nilai_dampak_residual_q'.$currentQuarter};
                            $skala_prob_residual = optional($risk->riskAnalysis)->{'skalaProbabilitasResidualQ'.$currentQuarter};
                            $skala_dampak_residual_obj = optional($risk->riskAnalysis)->{'skalaDampakResidualQ'.$currentQuarter.'Obj'};
                            $nilai_prob_residual = optional($risk->riskAnalysis)->{'nilai_probabilitas_residual_q'.$currentQuarter};
                            $skala_risiko_residual = optional($risk->riskAnalysis)->{'skala_risiko_residual_q'.$currentQuarter};
                            $level_risiko_residual = optional($risk->riskAnalysis)->{'level_risiko_residual_q'.$currentQuarter};
                        @endphp
                        <td class="text-end">{{ $nilai_dampak_residual ? 'Rp ' . number_format($nilai_dampak_residual, 0, ',', '.') : 'Rp 0' }}</td>
                        <td class="text-center fw-bold">{{ optional($skala_dampak_residual_obj)->tingkat ?? '-' }}</td>
                        <td class="text-center">{{ $nilai_prob_residual ? $nilai_prob_residual . '%' : '-' }}</td>
                        <td class="text-center fw-bold">{{ optional($skala_prob_residual)->tingkat ?? '-' }}</td>
                        <td class="text-center fw-bold">{{ $skala_risiko_residual ?? '-' }}</td>
                        <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($level_risiko_residual)))}} fw-bold">{{ $level_risiko_residual ?? '-' }}</td>

                        {{-- Realisasi --}}
                        <td class="realisasi-nilai-dampak text-end">-</td>
                        <td class="realisasi-skala-dampak text-center fw-bold">-</td>
                        <td class="realisasi-nilai-probabilitas text-center">-</td>
                        <td class="realisasi-skala-probabilitas text-center fw-bold">-</td>
                        <td class="realisasi-nilai-risiko text-center fw-bold">-</td>
                        <td class="realisasi-level-risiko text-center fw-bold">-</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="20" class="text-center p-4 text-muted">Tidak ada data risiko yang berstatus Open.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- ======================== SECTION KEY RISK INDICATOR (KRI) ======================= --}}
{{-- ========================================================================= --}}
<h2 class="mt-7 mb-4 text-primary fw-bold">
  <i class="fas fa-tachometer-alt me-2"></i>
  Key Risk Indicator (KRI)
</h2>
<hr class="mb-4">
{{-- ======================== 1. KRI KORPORAT ======================== --}}
<div class="card shadow-sm mb-4">
    <div class="card-header stepper border-0 pb-0">
        <div class="nav-link active d-flex align-items-center p-0">
            <span class="h3 mb-0">Daftar KRI Korporat (Status Waspada & Bahaya)</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-bordered table-hover table-sm">
                <thead class="text-center align-middle">
                    <tr>
                        <th style="min-width: 150px;">Risiko</th><th style="min-width: 200px;">Penyebab</th><th style="min-width: 150px;">KRI</th><th>Batas Aman</th><th>Batas Waspada</th><th>Batas Bahaya</th><th>Kondisi Saat Ini</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kriKorporat as $kri)
                    <tr>
                        <td>{{ $kri['risiko'] }}</td>
                        <td>
                            @if(!empty($kri['penyebab']) && is_array($kri['penyebab']))
                                <ul class="list-unstyled mb-0 ps-3">
                                @foreach($kri['penyebab'] as $penyebab)<li>- {{ $penyebab }}</li>@endforeach
                                </ul>
                            @else - @endif
                        </td>
                        <td>{{ $kri['kri'] ?? '-' }}</td>
                        <td class="text-center">{{ $kri['batas_aman'] ?? '-' }}</td><td class="text-center">{{ $kri['batas_waspada'] ?? '-' }}</td><td class="text-center">{{ $kri['batas_bahaya'] ?? '-' }}</td><td class="text-center fw-bold">{{ $kri['kondisi_saat_ini'] }}</td>
                        <td class="text-center">
                            @php
                                $statusClass = '';
                                $statusNumeric = $kri['status'] ?? 0;
                                if ($statusNumeric == 3) { $statusClass = 'red'; }
                                elseif ($statusNumeric == 2) { $statusClass = 'yellow'; }
                                elseif ($statusNumeric == 1) { $statusClass = 'green'; }
                            @endphp
                            <div class="status-container {{ $statusClass }}"><div class="status-green"></div><div class="status-yellow"></div><div class="status-red"></div></div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center p-4">Tidak ada data KRI Korporat dengan status Waspada atau Bahaya.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ======================== 2. KRI PROYEK ======================== --}}
<div class="card shadow-sm mb-4">
    <div class="card-header stepper border-0 pb-0">
        <div class="nav-link active d-flex align-items-center p-0">
            <span class="h3 mb-0">Daftar KRI Proyek (Status Waspada & Bahaya)</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-bordered table-hover table-sm">
                <thead class="text-center align-middle">
                    <tr>
                        <th style="min-width: 150px;">Risiko</th><th style="min-width: 200px;">Penyebab</th><th style="min-width: 150px;">KRI</th><th>Batas Aman</th><th>Batas Waspada</th><th>Batas Bahaya</th><th>Kondisi Saat Ini</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kriProyek as $kri)
                    <tr>
                        <td>{{ $kri['risiko'] }}</td>
                        <td>
                            @if(!empty($kri['penyebab']) && is_array($kri['penyebab']))
                                <ul class="list-unstyled mb-0 ps-3">
                                @foreach($kri['penyebab'] as $penyebab)<li>- {{ $penyebab }}</li>@endforeach
                                </ul>
                            @else - @endif
                        </td>
                        <td>{{ $kri['kri'] ?? '-' }}</td>
                        <td class="text-center">{{ $kri['batas_aman'] ?? '-' }}</td><td class="text-center">{{ $kri['batas_waspada'] ?? '-' }}</td><td class="text-center">{{ $kri['batas_bahaya'] ?? '-' }}</td><td class="text-center fw-bold">{{ $kri['kondisi_saat_ini'] }}</td>
                        <td class="text-center">
                            @php
                                $statusClass = '';
                                $statusNumeric = $kri['status'] ?? 0;
                                if ($statusNumeric == 3) { $statusClass = 'red'; }
                                elseif ($statusNumeric == 2) { $statusClass = 'yellow'; }
                                elseif ($statusNumeric == 1) { $statusClass = 'green'; }
                            @endphp
                            <div class="status-container {{ $statusClass }}"><div class="status-green"></div><div class="status-yellow"></div><div class="status-red"></div></div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center p-4">Tidak ada data KRI Proyek dengan status Waspada atau Bahaya.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ======================== 3. KRI DIVISI ======================== --}}
<div class="card shadow-sm mb-4">
    <div class="card-header stepper border-0 pb-0">
        <div class="nav-link active d-flex align-items-center p-0">
            <span class="h3 mb-0">Daftar KRI Divisi (Status Waspada & Bahaya)</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-bordered table-hover table-sm">
                <thead class="text-center align-middle">
                    <tr>
                        <th style="min-width: 150px;">Risiko</th><th style="min-width: 200px;">Penyebab</th><th style="min-width: 150px;">KRI</th><th>Batas Aman</th><th>Batas Waspada</th><th>Batas Bahaya</th><th>Kondisi Saat Ini</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kriDivisi as $kri)
                    <tr>
                        <td>{{ $kri['risiko'] }}</td>
                        <td>
                            @if(!empty($kri['penyebab']) && is_array($kri['penyebab']))
                                <ul class="list-unstyled mb-0 ps-3">
                                @foreach($kri['penyebab'] as $penyebab)<li>- {{ $penyebab }}</li>@endforeach
                                </ul>
                            @else - @endif
                        </td>
                        <td>{{ $kri['kri'] ?? '-' }}</td>
                        <td class="text-center">{{ $kri['batas_aman'] ?? '-' }}</td><td class="text-center">{{ $kri['batas_waspada'] ?? '-' }}</td><td class="text-center">{{ $kri['batas_bahaya'] ?? '-' }}</td><td class="text-center fw-bold">{{ $kri['kondisi_saat_ini'] }}</td>
                        <td class="text-center">
                            @php
                                $statusClass = '';
                                $statusNumeric = $kri['status'] ?? 0;
                                if ($statusNumeric == 3) { $statusClass = 'red'; }
                                elseif ($statusNumeric == 2) { $statusClass = 'yellow'; }
                                elseif ($statusNumeric == 1) { $statusClass = 'green'; }
                            @endphp
                            <div class="status-container {{ $statusClass }}"><div class="status-green"></div><div class="status-yellow"></div><div class="status-red"></div></div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center p-4">Tidak ada data KRI Divisi dengan status Waspada atau Bahaya.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ======================== 4. KRI ANAK PERUSAHAAN ======================== --}}
<div class="card shadow-sm mb-4">
    <div class="card-header stepper border-0 pb-0">
        <div class="nav-link active d-flex align-items-center p-0">
            <span class="h3 mb-0">Daftar KRI Anak Perusahaan (Status Waspada & Bahaya)</span>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-bordered table-hover table-sm">
                <thead class="text-center align-middle">
                    <tr>
                        <th style="min-width: 150px;">Risiko</th><th style="min-width: 200px;">Penyebab</th><th style="min-width: 150px;">KRI</th><th>Batas Aman</th><th>Batas Waspada</th><th>Batas Bahaya</th><th>Kondisi Saat Ini</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kriAnakPerusahaan as $kri)
                    <tr>
                        <td>{{ $kri['risiko'] }}</td>
                        <td>
                            @if(!empty($kri['penyebab']) && is_array($kri['penyebab']))
                                <ul class="list-unstyled mb-0 ps-3">
                                @foreach($kri['penyebab'] as $penyebab)<li>- {{ $penyebab }}</li>@endforeach
                                </ul>
                            @else - @endif
                        </td>
                        <td>{{ $kri['kri'] ?? '-' }}</td>
                        <td class="text-center">{{ $kri['batas_aman'] ?? '-' }}</td><td class="text-center">{{ $kri['batas_waspada'] ?? '-' }}</td><td class="text-center">{{ $kri['batas_bahaya'] ?? '-' }}</td><td class="text-center fw-bold">{{ $kri['kondisi_saat_ini'] }}</td>
                        <td class="text-center">
                            @php
                                $statusClass = '';
                                $statusNumeric = $kri['status'] ?? 0;
                                if ($statusNumeric == 3) { $statusClass = 'red'; }
                                elseif ($statusNumeric == 2) { $statusClass = 'yellow'; }
                                elseif ($statusNumeric == 1) { $statusClass = 'green'; }
                            @endphp
                            <div class="status-container {{ $statusClass }}"><div class="status-green"></div><div class="status-yellow"></div><div class="status-red"></div></div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center p-4">Tidak ada data KRI Anak Perusahaan dengan status Waspada atau Bahaya.</td></tr>
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
        <div class="card h-100 shadow-sm">
            <div class="card-header border-0 pb-0 d-flex flex-between-center"><h3 class="h4">Ringkasan Efektivitas</h3></div>
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
        <div class="card h-100 shadow-sm">
            <div class="card-header border-0 pb-0"><h3 class="h4">Detail Risiko Selesai (Closed)</h3></div>
            <div class="card-body">
                <div class="accordion" id="accordionEfektivitas">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingEfektif"><button class="accordion-button fs-6 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEfektif" aria-expanded="true">Perlakuan Efektif <span class="badge rounded-pill bg-info ms-2">{{ $efektifRisks->count() }}</span></button></h2>
                        <div id="collapseEfektif" class="accordion-collapse collapse show" aria-labelledby="headingEfektif">
                            <div class="accordion-body p-0">
                                <ul class="list-group list-group-flush">
                                    @forelse($efektifRisks as $risk)
                                    <a href="#" class="list-group-item list-group-item-action"><div class="d-flex justify-content-between align-items-center w-100"><span>{{ optional($risk->peristiwaRisiko)->title ?? 'Risiko ID: '.$risk->id }}</span><span class="badge bg-light text-dark">Nilai: {{ $risk->efektivitas_perlakuan_risiko ?? 0 }}</span></div></a>
                                    @empty<li class="list-group-item">Tidak ada risiko yang dinilai efektif.</li>@endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTidakEfektif"><button class="accordion-button fs-6 py-2 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTidakEfektif" aria-expanded="false">Perlakuan Tidak Efektif <span class="badge rounded-pill bg-danger ms-2">{{ $tidakEfektifRisks->count() }}</span></button></h2>
                        <div id="collapseTidakEfektif" class="accordion-collapse collapse" aria-labelledby="headingTidakEfektif">
                            <div class="accordion-body p-0">
                                <ul class="list-group list-group-flush">
                                    @forelse($tidakEfektifRisks as $risk)
                                    <a href="#" class="list-group-item list-group-item-action"><div class="d-flex justify-content-between align-items-center w-100"><span>{{ optional($risk->peristiwaRisiko)->title ?? 'Risiko ID: '.$risk->id }}</span><span class="badge bg-light text-dark">Nilai: {{ $risk->efektivitas_perlakuan_risiko ?? 0 }}</span></div></a>
                                    @empty<li class="list-group-item">Tidak ada risiko yang dinilai tidak efektif.</li>@endforelse
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
{{-- ======================== SECTION TOP LOSS EVENT ========================= --}}
{{-- ========================================================================= --}}
<h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-list-ol me-2"></i>Top Loss Events</h2>
<hr class="mb-4">

<div class="col-12 mb-3">
  <div class="card shadow-sm">
    <div class="card-header border-0 pb-0">
      <div class="d-flex align-items-center gap-3">
        <h3>Top 10 Loss Event Data Korporat</h3>
      </div>
      <hr class="mb-2 mt-4">
    </div>
    <div class="card-body pt-0">
      <div class="table-responsive">
        <table class="table table-hover table-sm table-bordered mt-3">
          <thead class="bg-light text-center align-middle">
            <tr>
              <th>#</th>
              <th>Tanggal Kejadian</th>
              <th>Nama Kejadian</th>
              <th>Deskripsi Kejadian</th>
              <th>Kategori Kejadian</th>
              <th class="text-end">Nilai Kerugian</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($topLossEventsCorporate as $event)
              <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($event->tanggal_kejadian)->format('d M Y') }}</td>
                <td>{{ $event?->nama_kejadian ?? '-' }}</td>
                <td>{{ $event?->identifikasi_kejadian ?? '-' }}</td>
                <td class="text-center">{{ optional($event->kategoriKejadian)->kategori_kejadian ?? '-' }}</td>
                <td class="text-end text-danger fw-bold">Rp {{ number_format($event->nilai_kerugian_finansial, 0, ',', '.') }}</td>
                <td class="text-center">
                    <a href="{{ url('loss-event/' . $event->id) }}" class="btn btn-sm btn-light-primary btn-icon" target="_blank" data-bs-toggle="tooltip" title="Lihat Detail">
                        <i class='bx bx-show'></i>
                    </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted p-4">Tidak ada data Loss Event untuk Korporat.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="col-12 mb-3">
  <div class="card shadow-sm" id="led-card">
    <div class="card-header border-0 pb-0">
      <div class="d-flex align-items-center gap-3">
        <h3>Top 10 Loss Event Data Divisi</h3>
      </div>
      <hr class="mb-2 mt-4">
    </div>
    <div class="card-body pt-0">
      <div class="table-responsive">
        <table class="table table-hover table-sm table-bordered mt-3">
          <thead class="bg-light text-center align-middle">
            <tr>
              <th>#</th>
              <th>Nama Divisi</th>
              <th>Tanggal Kejadian</th>
              <th>Nama Kejadian</th>
              <th>Deskripsi Kejadian</th>
              <th>Kategori Kejadian</th>
              <th class="text-end">Nilai Kerugian</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="col-12">
  <div class="card shadow-sm" id="led-project-card">
    <div class="card-header border-0 pb-0">
      <div class="d-flex align-items-center gap-3">
        <h3>Top 10 Loss Event Data Project</h3>
      </div>
      <hr class="mb-2 mt-4">
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
              <th class="text-end">Nilai Kerugian</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection


@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
<style>
.status-container {
	display: flex;
	justify-content: center;
	gap: 4px
}

.status-container div {
	width: 20px;
	height: 20px;
	border-radius: 50%;
	background-color: #e9ecef;
	border: 1px solid #ced4da
}

.status-container.green .status-green {
	background-color: #28a745
}

.status-container.yellow .status-yellow {
	background-color: #ffc107
}

.status-container.red .status-red {
	background-color: #dc3545
}

.kode-peristiwa {
	display: flex;
	flex-wrap: wrap;
	gap: 5px;
	position: absolute;
	bottom: 5px;
	right: 0;
	width: calc(100% - 5px) !important
}

.box-inherent {
	background-color: #fff;
	color: #000;
	padding: 2px 5px;
	border-radius: 5px
}

.box-residual {
	background-color: #000;
	color: #fff;
	padding: 2px 5px;
	border-radius: 5px
}

.box-current {
	background-color: #007bff;
	color: #fff;
	padding: 2px 5px;
	border-radius: 5px
}

.bg-high {
	background-color: rgba(220, 53, 69, .8)
}

.bg-moderate-high {
	background-color: rgba(255, 193, 7, .8)
}

.bg-moderate {
	background-color: rgba(253, 247, 155, .8)
}

.bg-low-moderate {
	background-color: rgba(167, 214, 169, .8)
}

.bg-low {
	background-color: rgba(212, 237, 218, .8)
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    // ==============================================================
    // 1. FILTER PERIODE & DIVISI/PROYEK (Jika Diperlukan)
    // ==============================================================
    function applyFilterAndRefresh() {
        var period = $('#period_selector').val();
        let baseUrl = '{{ url()->current() }}';
        let params = new URLSearchParams();
        if (period) params.append('period', period);
        window.location.href = `${baseUrl}?${params.toString()}`;
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

    // ==============================================================
    // 2. FULLSCREEN HANDLER PETA RISIKO
    // ==============================================================
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

    // ==============================================================
    // 3. LOGIKA PETA RISIKO (INHERENT, RESIDUAL, CURRENT)
    // ==============================================================
    const openRisksJs = @json($openRisksJs ?? []);
    const formattedCurrentRiskMaps = @json($formattedCurrentRiskMaps ?? []);
    const currentYear = '{{ $currentYear }}';
    const currentQuarter = {{ $currentQuarter }};
    const baseUrl = `{{ url('risk-register-unit') }}`;

    // Fungsi Render Badge ke dalam Cell Peta
    function renderCellBadges(cell, risksArray, typeClass) {
        if (!risksArray || risksArray.length === 0) return;
        let html = '';
        let maxDisplay = 6;
        let count = risksArray.length;

        for (let i = 0; i < Math.min(count, maxDisplay); i++) {
            html += `<span class="${typeClass}">${risksArray[i].riskNumber}${risksArray[i].displayMark || ''}</span>`;
        }
        if (count > maxDisplay) {
            html += `<span class="${typeClass} bg-danger border-danger text-white" data-bs-toggle="tooltip" title="Ada ${count - maxDisplay} risiko lain">+${count - maxDisplay}</span>`;
        }
        cell.find('.kode-peristiwa').append(html);
    }

    // Fungsi Mengisi Peta Inherent & Residual
    function populateInherentMap() {
        $('#inherentMap .kode-peristiwa').empty();
        let mapInherent = {};
        let mapResidual = {};

        Object.values(openRisksJs).forEach(risk => {
            if(risk.risk_analysis) {
                const riskNumber = $(`.table-strategi tbody tr[data-risk-id="${risk.id}"]`).find('td:first').text().trim();
                if(!riskNumber) return;

                let riskObj = { ...risk, riskNumber: riskNumber };

                // Kelompokkan Inherent
                const matrixI = risk.risk_analysis.skala_dampak + '-' + risk.risk_analysis.skala_probabilitas?.tingkat;
                if(!mapInherent[matrixI]) mapInherent[matrixI] = [];
                mapInherent[matrixI].push(riskObj);

                // Kelompokkan Residual
                const probResidualRel = risk.risk_analysis['skala_probabilitas_residual_q' + currentQuarter];
                const dampakResidualObj = risk.risk_analysis['skala_dampak_residual_q' + currentQuarter + '_obj'];

                if(dampakResidualObj && probResidualRel) {
                    const matrixR = dampakResidualObj.tingkat + '-' + probResidualRel.tingkat;
                    if(!mapResidual[matrixR]) mapResidual[matrixR] = [];
                    mapResidual[matrixR].push(riskObj);
                }
            }
        });

        // Tempelkan data ke HTML DOM
        $('#inherentMap .data-cell').each(function() {
            let matrix = $(this).data('matrix');
            $(this).data('risks-inherent', mapInherent[matrix] || []);
            $(this).data('risks-residual', mapResidual[matrix] || []);
            renderCellBadges($(this), mapInherent[matrix], 'box-inherent');
            renderCellBadges($(this), mapResidual[matrix], 'box-residual');
        });
    }

    // Fungsi Mengisi Peta Current (Realisasi Terkini)
    function updateCurrentData() {
        const selectedMonth = parseInt($('#monthSelect').val());
        const selectedYear = parseInt($('#tahunSelect').val());
        $('#currentMap .kode-peristiwa').empty();

        let unmonitoredRisks = [];
        let mapCurrent = {};

        Object.values(openRisksJs).forEach(risk => {
            const riskId = risk.id;
            const tableRow = $(`.table-strategi tbody tr[data-risk-id="${riskId}"]`);
            const riskNumber = tableRow.find('td:first').text().trim();

            const riskMonitorings = risk.monitoring_risikos || [];
            // Cek apakah ada update monitoring di bulan dan tahun yang dipilih
            const hasMonitoringThisMonth = riskMonitorings.some(m => parseInt(m.month) === selectedMonth && parseInt(m.tahun) === selectedYear);

            let displayMark = '';
            if (!hasMonitoringThisMonth && riskNumber) {
                displayMark = '<sup class="text-danger fw-bold ms-1" style="font-size: 0.8rem; top: -0.3em;" data-bs-toggle="tooltip" title="Belum diverifikasi/diupdate bulan ini">*</sup>';
                unmonitoredRisks.push(riskNumber);
            }

            if(risk.risk_analysis) {
                const currentDataArray = formattedCurrentRiskMaps[riskId]?.[selectedYear] || [];
                const currentData = currentDataArray[selectedMonth - 1]; // Array di controller diisi dari index 0 s/d 11

                if (currentData && riskNumber) {
                    const matrixC = currentData.skala_dampak + '-' + currentData.skala_probabilitas;
                    if(!mapCurrent[matrixC]) mapCurrent[matrixC] = [];

                    let riskObj = { ...risk, riskNumber: riskNumber, displayMark: displayMark, currentData: currentData };
                    mapCurrent[matrixC].push(riskObj);

                    // Update UI Baris Tabel Strategi di bawah Peta
                    if (tableRow.length) {
                        const levelClass = (currentData.level_risiko_formatted || '').toLowerCase().replace(/ /g, '-').replace('to-', '');

                        tableRow.find('.realisasi-nilai-dampak').html(currentData.nilai_dampak_formatted);
                        tableRow.find('.realisasi-skala-dampak').html(currentData.skala_dampak_obj?.tingkat || '-');

                        let probText = currentData.nilai_probabilitas_formatted || '-';
                        if (probText !== '-') probText += '%';
                        tableRow.find('.realisasi-nilai-probabilitas').html(probText);

                        tableRow.find('.realisasi-skala-probabilitas').html(currentData.skala_probabilitas_obj?.tingkat || '-');
                        tableRow.find('.realisasi-nilai-risiko').html(currentData.nilai_risiko_formatted || '-');

                        tableRow.find('.realisasi-level-risiko').html((currentData.level_risiko_formatted || '-') + displayMark)
                            .removeClass('bg-high bg-moderate-high bg-moderate bg-low-moderate bg-low').addClass('bg-' + levelClass);
                    }
                } else if (tableRow.length) {
                    tableRow.find('.realisasi-nilai-dampak, .realisasi-skala-dampak, .realisasi-nilai-probabilitas, .realisasi-skala-probabilitas, .realisasi-nilai-risiko, .realisasi-level-risiko').html('-');
                    tableRow.find('.realisasi-level-risiko').removeClass('bg-high bg-moderate-high bg-moderate bg-low-moderate bg-low');
                }
            }
        });

        // Tempelkan Data Current ke Peta HTML
        $('#currentMap .data-cell').each(function() {
            let matrix = $(this).data('matrix');
            $(this).data('risks-current', mapCurrent[matrix] || []);
            renderCellBadges($(this), mapCurrent[matrix], 'box-current');
        });

        // Tampilkan/Sembunyikan Info Fallback Unmonitored
        if (unmonitoredRisks.length > 0) {
            $('#unmonitored-info').removeClass('d-none');
            $('#unmonitored-list').text(unmonitoredRisks.join(', '));
        } else {
            $('#unmonitored-info').addClass('d-none');
        }

        $('#info-month').text($('#monthSelect option:selected').text().trim().split('-').pop());
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    // Jalankan inisiasi peta
    populateInherentMap();
    updateCurrentData();

    // Event listener jika user mengganti Bulan / Tahun pada peta Current
    $('#monthSelect, #tahunSelect').on('change', updateCurrentData);

    // ==============================================================
    // 4. MODAL POP-UP DETAIL PETA (KETIKA CELL DI-KLIK)
    // ==============================================================
    $('.data-cell').on('click', function() {
        let isCurrentMap = $(this).closest('#currentMap').length > 0;
        let matrix = $(this).data('matrix');
        let levelText = $(this).find('.posisi-risiko').text() || '-';

        let tbody = $('#tableModalRisiko tbody');
        tbody.empty();
        let hasData = false;

        const createModalRow = (r, type) => {
            let route = `${baseUrl}/${r.id}/view`;
            let badgeClass = type === 'Inherent' ? 'bg-light text-dark border' : (type === 'Residual' ? 'bg-dark text-white' : 'bg-primary text-white');

            let nilaiDampak = 'Rp 0';
            if (type === 'Current' && r.currentData) {
                nilaiDampak = r.currentData.nilai_dampak_formatted || 'Rp 0';
            } else {
                nilaiDampak = r.risk_analysis?.nilai_dampak
                            ? 'Rp ' + parseInt(r.risk_analysis.nilai_dampak).toLocaleString('id-ID')
                            : 'Rp 0';
            }

            let namaPeristiwa = r.peristiwaRisiko?.title || r.peristiwa_risiko || '-';

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

        if (hasData) {
            $('#modalRiskLevel').text(`${levelText} (Impact: ${matrix.split('-')[0]}, Likelihood: ${matrix.split('-')[1]})`);
            $('#modalPetaRisiko').modal('show');
        }
    });

    // ==============================================================
    // 5. ECHARTS: EFEKTIVITAS PERLAKUAN RISIKO
    // ==============================================================
    const efektivitasData = @json($efektivitasPerlakuanData ?? []);

    function fillEfektivitasChart(data) {
        var chartDom = document.getElementById('efektivitas-perlakuan-chart');
        if (!chartDom) return;

        var myChart = echarts.init(chartDom);
        var option = {
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

        myChart.setOption(option, true);
        $(window).on('resize', function(){
            myChart.resize();
        });
    }

    if (efektivitasData.length > 0 && efektivitasData.some(item => item.value > 0)) {
        fillEfektivitasChart(efektivitasData);
    } else {
        $('#efektivitas-perlakuan-chart').html('<div class="d-flex justify-content-center align-items-center h-100 text-muted border rounded">Belum ada data efektivitas yang ditutup.</div>');
    }

    // ==============================================================
    // 6. TOP LOSS EVENT DATABASE (LED) DIVISI (VIA JS)
    // ==============================================================
    const lossEventsUnit = @json($lossEventsUnit ?? []);
    $('#led-card .table tbody').html('');

    if (lossEventsUnit.length == 0) {
      $('#led-card .table tbody').append(`
        <tr>
            <td colspan="8" class="text-center text-muted p-4">Tidak ada data Loss Event Divisi.</td>
        </tr>
      `);
    } else {
      lossEventsUnit.forEach((led, index) => {
        $('#led-card .table tbody').append(`
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>${led.unit_name}</td>
                <td class="text-center">${led.tanggal_kejadian}</td>
                <td>${led.nama_kejadian}</td>
                <td>${led.deskripsi_kejadian}</td>
                <td class="text-center">${led.kategori_kejadian}</td>
                <td class="text-end text-danger fw-bold">${led.nilai_kerugian}</td>
                <td class="text-center">
                    <a href="{{ url('unit-led') }}/${led.id}" class="btn btn-sm btn-light-primary btn-icon" target="_blank" data-bs-toggle="tooltip" title="Lihat Detail">
                        <i class='bx bx-show'></i>
                    </a>
                </td>
            </tr>
        `);
      });
    }

    // ==============================================================
    // 7. TOP LOSS EVENT DATABASE (LED) PROJECT (VIA JS)
    // ==============================================================
    const lossEventsProject = @json($lossEventsProject ?? []);
    $('#led-project-card .table tbody').html('');

    if (lossEventsProject.length == 0) {
      $('#led-project-card .table tbody').append(`
        <tr>
            <td colspan="8" class="text-center text-muted p-4">Tidak ada data Loss Event Project.</td>
        </tr>
      `);
    } else {
      lossEventsProject.forEach((led, index) => {
        $('#led-project-card .table tbody').append(`
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>${led.project_name}</td>
                <td class="text-center">${led.tanggal_kejadian}</td>
                <td>${led.nama_kejadian}</td>
                <td>${led.deskripsi_kejadian}</td>
                <td class="text-center">${led.kategori_kejadian}</td>
                <td class="text-end text-danger fw-bold">${led.nilai_kerugian}</td>
                <td class="text-center">
                    <a href="{{ url('project-led') }}/${led.id}" class="btn btn-sm btn-light-primary btn-icon" target="_blank" data-bs-toggle="tooltip" title="Lihat Detail">
                        <i class='bx bx-show'></i>
                    </a>
                </td>
            </tr>
        `);
      });
    }

    // Inisialisasi Tooltip Bootstrap (jika digunakan)
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
