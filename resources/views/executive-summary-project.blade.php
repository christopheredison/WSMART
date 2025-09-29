@extends('layouts.default')

@section('dashboard')
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="../assets/img/dashboard-header4.webp" alt="dashboard">
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
            <form id="filter-form" action="{{ url()->current() }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="period_selector" class="form-label fw-bold">Pilih Periode</label>
                        <input type="text" name="period" id="period_selector" class="form-control" placeholder="Pilih Bulan & Tahun" value="{{ $selectedPeriod }}" disabled>
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
                        <select name="project_id" id="project_selector" class="form-select select2" onchange="this.form.submit()">
                            <option value="" selected>Pilih Proyek</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" {{ $project->id == $selectedProjectId ? 'selected' : '' }}>
                                    {{ $project->project_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
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
                <p class="text-muted text-uppercase mb-1">Omset Kontrak</p>
                <h3 class="fw-bold text-dark mb-0">Rp {{ number_format($summaryData['omset_kontrak'], 0, ',', '.') }}</h3>
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
                              <span class="text-muted">Realisasi Total</span>
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

@else
    {{-- Alert jika proyek belum dipilih --}}
    <div class="alert alert-info text-center mt-5" role="alert">
        <strong>Pilih Proyek</strong> untuk menampilkan Executive Summary dan detail risiko.
    </div>
@endif

@endsection

@push('styles')
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
@endpush

@section('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    flatpickr("#period_selector", {
        plugins: [
            new monthSelectPlugin({
                shorthand: true,
                dateFormat: "Y-m",
                altFormat: "F Y",
                altInput: true,
            })
        ],
        maxDate: "today", // Batasi pemilihan maksimal bulan ini
        onChange: function(selectedDates, dateStr, instance) {
            // Auto-submit form ketika periode diganti
            $('#filter-form').submit();
        }
    });

    // Handler untuk auto-submit filter unit
    $('#unit_selector').on('change', function() {
        // Setelah unit dipilih, kita ingin memuat ulang halaman agar daftar proyek diperbarui.
        // Tidak perlu langsung submit, karena kita akan melakukan auto-select proyek jika hanya ada 1.
        // Biarkan controller yang menangani, kita hanya perlu tambahkan logic auto-select di JS.
        var selectedUnitId = $(this).val();
        if (selectedUnitId) {
            // Lakukan AJAX call untuk mendapatkan proyek berdasarkan unit
            $.ajax({
                url: '{{ url()->current() }}',
                type: 'GET',
                data: {
                    unit_id: selectedUnitId,
                    ajax: 1 // Flag untuk request AJAX
                },
                success: function(response) {
                    var projects = response.projects;
                    var $projectSelector = $('#project_selector');
                    $projectSelector.empty().append('<option value="" selected>Pilih Proyek</option>');

                    if (projects.length === 1) {
                        // Auto-select proyek jika hanya ada satu
                        var projectId = projects[0].id;
                        $projectSelector.append(new Option(projects[0].project_name, projectId, true, true));
                        // Auto-submit form
                        $('#filter-form').submit();
                    } else if (projects.length > 0) {
                        // Isi opsi proyek
                        $.each(projects, function(key, project) {
                            $projectSelector.append(new Option(project.project_name, project.id));
                        });
                        $projectSelector.val(''); // Reset project selection
                        // Submit form HANYA jika ada project_id yang sudah terpilih sebelumnya
                        // atau jika unit berubah dan tidak ada auto-select (agar daftar project terisi)
                        // Untuk simplicity, kita submit form setelah Unit diubah,
                        // entah itu ter-auto-select atau tidak, agar URL terupdate.
                        if ('{{ $selectedProjectId }}') {
                            // Jika ada proyek terpilih sebelumnya, biarkan proses filter di backend yang mengembalikan nilai.
                            // Kita hanya submit form ketika project selector berubah.
                        } else {
                            // Biarkan user memilih proyek jika lebih dari satu
                            // Atau biarkan perubahan unit mengarahkan ke dashboard tanpa proyek terpilih.
                            $('#filter-form').submit();
                        }
                    } else {
                        // Jika tidak ada proyek, submit form untuk mereset tampilan
                        $('#filter-form').submit();
                    }
                    $projectSelector.trigger('change.select2');
                },
                error: function() {
                    console.error("Gagal mengambil daftar proyek.");
                    $('#filter-form').submit(); // Tetap submit jika gagal
                }
            });
        } else {
            // Jika unit dikosongkan, submit form
            $('#filter-form').submit();
        }
    });
});
</script>
@endsection