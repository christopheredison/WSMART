@extends('layouts.default')

@section('dashboard')
<div class="row mb-7">
    <div class="col-12">
        <div class="card border-0 dashboard-header">
            <img src="../assets/img/dashboard-header.webp" alt="dashboard">
            <div class="card-header border-0 justify-content-end">
                <h1 class="mb-0">Executive Summary Korporat</h1>
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
                    <label for="unit_selector" class="form-label fw-bold">Pilih Divisi / Anak Perusahaan</label>
                    <select name="unit_id" id="unit_selector" class="form-select select2">
                        <option value="">Pilih Divisi / Anak Perusahaan</option>
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
            {{-- LED Divisi Operasi --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-hard-hat me-2"></i>Loss Event Database (LED) Divisi Operasi</h5>
                </div>
                <div class="card-body py-2">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Total Kerugian Finansial</span>
                            <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_divisi_operasi_total'], 0, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- LED Divisi Fungsi --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-cogs me-2"></i>Loss Event Database (LED) Divisi Fungsi</h5>
                </div>
                <div class="card-body py-2">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Total Kerugian Finansial</span>
                            <span class="fw-bold fs-4 text-danger">Rp {{ number_format($summaryData['led_divisi_fungsi_total'], 0, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @if ($isProjectUnit)
            {{-- LED Proyek --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-project-diagram me-2"></i>Loss Event Database (LED) Proyek</h5>
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
                            <small class="text-muted">(LSP Realisasi - LED Proyek - LED Divisi Operasi - LED Divisi Fungsi)</small>
                            @else
                            <small class="text-muted">(Biaya Usaha - LED Divisi Operasi - LED Divisi Fungsi)</small>
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

    {{-- CHART SECTIONS (NEW) --}}
    @if ($isProjectUnit)
        <h2 class="my-5 text-primary fw-bold"><i class="fas fa-chart-bar me-2"></i>Analisis Risiko Proyek</h2>
        <hr class="mb-4">

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-danger-subtle">
                        <h5 class="mb-0 fw-bold text-danger-emphasis"><i class="fas fa-chart-pie me-2"></i>Top 10 Loss Event Database Proyek</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="top10LedProyekChart"></canvas>
                        @if($top10LedProyek->isEmpty())
                            <p class="text-center text-muted mt-3 mb-0">Tidak ada data Loss Event Proyek.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-warning-subtle">
                        <h5 class="mb-0 fw-bold text-warning-emphasis"><i class="fas fa-chart-line me-2"></i>Top 10 Eksposur Risiko Residual Proyek</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="top10EksposurResidualProyekChart"></canvas>
                        @if($top10EksposurResidualProyek->isEmpty())
                            <p class="text-center text-muted mt-3 mb-0">Tidak ada data Eksposur Risiko Proyek.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif


@else
    <div class="alert alert-info text-center mt-5" role="alert">
        <strong>Pilih Divisi / Anak Perusahaan</strong> untuk menampilkan Executive Summary.
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script type="text/javascript">
$(document).ready(function() {
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
                altInput: true,
            })
        ],
        maxDate: "today",
        defaultDate: "{{ $selectedPeriod }}",
        onChange: function(selectedDates, dateStr, instance) {
            if ($('#unit_selector').val()) {
                applyFilterAndRefresh();
            }
        }
    });

    $('#unit_selector').on('change', function() {
        applyFilterAndRefresh();
    });

    // --- CHART INITIALIZATION (NEW) ---
    @if ($selectedUnitId && $isProjectUnit)
        // Chart Top 10 LED Proyek
        const top10LedProyekCtx = document.getElementById('top10LedProyekChart');
        if (top10LedProyekCtx && {{ count($top10LedProyek) > 0 ? 'true' : 'false' }}) {
            new Chart(top10LedProyekCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($top10LedProyek->pluck('chart_label')) !!},
                    datasets: [{
                        label: 'Nilai Kerugian Finansial',
                        data: {!! json_encode($top10LedProyek->pluck('nilai_kerugian_finansial')) !!},
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Membuat bar horizontal
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nilai Kerugian (Rp)'
                            },
                            ticks: {
                                callback: function(value, index, values) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Peristiwa Loss Event'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.x !== null) {
                                        label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.x);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Chart Top 10 Eksposur Risiko Residual Proyek
        const top10EksposurResidualProyekCtx = document.getElementById('top10EksposurResidualProyekChart');
        if (top10EksposurResidualProyekCtx && {{ count($top10EksposurResidualProyek) > 0 ? 'true' : 'false' }}) {
            new Chart(top10EksposurResidualProyekCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($top10EksposurResidualProyek->pluck('risk_event')) !!},
                    datasets: [{
                        label: 'Total Eksposur Risiko',
                        data: {!! json_encode($top10EksposurResidualProyek->pluck('total_eksposure')) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Membuat bar horizontal
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nilai Eksposur (Rp)'
                            },
                            ticks: {
                                callback: function(value, index, values) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Peristiwa Risiko'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.x !== null) {
                                        label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.x);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    @endif
});
</script>
@endpush