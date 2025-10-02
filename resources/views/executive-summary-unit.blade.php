@extends('layouts.default')

@section('dashboard')
<div class="row mb-7">
    <div class="col-12">
        <div class="card border-0 dashboard-header">
            <img src="../assets/img/dashboard-header.webp" alt="dashboard">
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

    <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-shield-alt me-2"></i>Manajemen Kinerja Berbasis Risiko</h2>
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
                    <h5 class="mb-0 fw-bold"><i class="fas fa-flag-checkered me-2"></i>Hasil Usaha s/d Desember {{ $currentYear }}</h5>
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
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-database me-2"></i>Loss Event Database (LED) Proyek</h5>
                </div>
                <div class="card-body py-2">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Total Kerugian Finansial</span>
                            <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_proyek_total'], 0, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-building me-2"></i>Loss Event Database (LED) Divisi</h5>
                </div>
                <div class="card-body py-2">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Total Kerugian Finansial</span>
                            <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_divisi_total'], 0, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm" style="height: auto;">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Eksposur Risiko Residual</h5>
                </div>
                <div class="card-body py-2">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Realisasi Total s/d {{ $formattedPeriod }}</span>
                            <span class="fw-bold fs-5 text-warning">Rp {{ number_format($summaryData['eksposur_risiko_total'], 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Realisasi Annual {{ $currentYear }}</span>
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
                            <h6 class="fw-bold text-success-emphasis mb-0">Hasil Usaha s/d {{ $formattedPeriod }}</h6>
                            @if($isProjectUnit)
                            <small class="text-muted">(LSP Realisasi - LED Proyek - LED Divisi)</small>
                            @else
                            <small class="text-muted">(Biaya Usaha - LED Divisi)</small>
                            @endif
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
                            @if($isProjectUnit)
                            <small class="text-muted">(Proyeksi LSP - Eksposur Annual)</small>
                            @else
                            <small class="text-muted">(Biaya Usaha - Eksposur Annual)</small>
                            @endif
                        </div>
                        <span class="fw-bold fs-4 text-primary">Rp {{ number_format($summaryData['proyeksi_hasil_usaha_sd_des'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
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
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    function applyFilterAndRefresh() {
        var unitId = $('#unit_selector').val();
        var period = $('#period_selector').val();

        if (unitId) { // Hanya refresh jika unit dipilih
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
        applyFilterAndRefresh();
    });
});
</script>
@endpush