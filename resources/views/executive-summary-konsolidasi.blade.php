@extends('layouts.default')

@section('dashboard')
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="{{ asset('assets/img/dashboard-header.webp') }}" alt="dashboard" onerror="this.src='https://via.placeholder.com/1200x200?text=Dashboard+Konsolidasi'">
      <div class="card-header border-0 justify-content-end">
        <h1 class="mb-0">Pelaporan Konsolidasi Risiko</h1>
        <h4 class="mb-4">Lintas Divisi Operasi & Proyek</h4>
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
                    <label for="period_selector" class="form-label fw-bold">Pilih Periode Cutoff</label>
                    <input type="text" name="period" id="period_selector" class="form-control" placeholder="Pilih Bulan & Tahun" value="{{ $selectedPeriod }}">
                </div>
                <div class="col-md-6">
                    <label for="unit_selector" class="form-label fw-bold">Filter Divisi Operasi</label>
                    <select name="unit_id" id="unit_selector" class="form-select select2">
                        <option value="" selected>Semua Divisi Operasi</option>
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

@php
    $formattedPeriod = \Carbon\Carbon::createFromFormat('Y-m', $selectedPeriod)->translatedFormat('F Y');
@endphp

{{-- CARD RINGKASAN STATISTIK --}}
<div class="row g-4 mb-4">
    {{-- CARD YANG BARU: Eksposur Residual Total --}}
    <div class="col-md-3">
        <div class="card card-body h-100 shadow-sm border-start border-4 border-info">
            <p class="text-muted text-uppercase mb-1" style="font-size: 0.8rem">Total Risiko Terpublish</p>
            <h4 class="fw-bold text-dark mb-0">{{ number_format($totalRisikoSemua, 0, ',', '.') }} Risiko</h4>
            <small class="text-muted mt-2">Masih berstatus Open</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body h-100 shadow-sm border-start border-4 border-warning">
            <p class="text-muted text-uppercase mb-1" style="font-size: 0.8rem">Eksposur Inheren Total</p>
            <h4 class="fw-bold text-dark mb-0">Rp {{ number_format($totalEksposurInherentSemua, 0, ',', '.') }}</h4>
            <small class="text-muted mt-2">Dari {{ $totalProyekAktif }} proyek aktif</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body h-100 shadow-sm border-start border-4 border-success">
            <p class="text-muted text-uppercase mb-1" style="font-size: 0.8rem">Eksposur Residual Total</p>
            <h4 class="fw-bold text-dark mb-0">Rp {{ number_format($totalEksposurResidualSemua, 0, ',', '.') }}</h4>
            <small class="text-muted mt-2">Dari {{ $totalProyekAktif }} proyek aktif</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body h-100 shadow-sm border-start border-4 border-danger">
            <p class="text-muted text-uppercase mb-1" style="font-size: 0.8rem">Eksposur Realisasi Top 10</p>
            <h4 class="fw-bold text-danger mb-0">Rp {{ number_format($totalEksposurTop10, 0, ',', '.') }}</h4>
            <small class="text-muted mt-2">Dari 10 risiko tertinggi</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- PIE CHART EKSPOSUR --}}
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pb-0">
                <h4 class="fw-bold mb-0">Distribusi Total Eksposur Realisasi</h4>
                <small class="text-muted">Per Divisi | Berdasarkan Risiko Kuantitatif Ter-update</small>
            </div>
            <div class="card-body min-vh-25">
                <div id="pieChartEksposur" style="height: 400px;"></div>
            </div>
        </div>
    </div>

    {{-- BAR CHART TOP 10 PROYEK --}}
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header border-0 pb-0">
                <h4 class="fw-bold mb-0">10 Proyek Eksposur Realisasi Tertinggi</h4>
                <small class="text-muted">
                  {{-- Di bulan {{ $formattedPeriod }} |  --}}
                  Berdasarkan Proyek dan Risiko Aktif
                </small>
            </div>
            <div class="card-body min-vh-25">
                <div id="barChartEksposurProyek" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

<h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-chart-pie me-2"></i>Peta & Daftar Top 10 Risiko Tertinggi</h2>
<hr class="mb-4">

{{-- PETA RISIKO --}}
<div class="card mb-4" id="mapCardContainer">
    <div class="card-header border-0 pb-0">
        <div class="d-flex justify-content-between align-items-start w-100">
            <div class="d-flex flex-column">
                <span class="h3 mb-0">Peta 10 Risiko Tertinggi Lintas Proyek</span>
                <small class="text-muted mt-1">Hanya menampilkan 10 risiko dengan nilai eksposur realisasi tertinggi di bulan {{ $formattedPeriod }}.</small>
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
            {{-- Peta Inheren & Residual --}}
            <div class="col-md-6 mb-4 mb-md-0">
                <div class="row mb-3"><div class="col"><h3 class="h5 mb-0">Inheren dan Residual</h3></div></div>
                <div class="table-risk-map" id="inherentMap">
                    <table class="map-table w-100">
                        <tbody>
                            @for($likelihood = 5; $likelihood >= 1; $likelihood--)
                            <tr>
                                @if ($likelihood == 5) <td rowspan="5" class="side-title"><div class="divider m-0"><div class="divider-text">LIKELIHOOD</div></div></td> @endif
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
                            <tr><td class="useless-cell"></td><td colspan="5" class="footer-title"><div class="divider m-0"><div class="divider-text">IMPACT</div></div></td></tr>
                        </tbody>
                    </table>
                    <div class="risk-map-legend d-flex justify-content-center gap-4 mt-3">
                        <div class="d-flex align-items-center gap-1"><i class='bx bx-circle inherent fs-5'></i> Inherent</div>
                        <div class="d-flex align-items-center gap-1"><i class='bx bxs-circle residual fs-5'></i> Residual</div>
                    </div>
                </div>
            </div>

            {{-- Peta Realisasi (Current) --}}
            <div class="col-md-6">
                <div class="row mb-3"><div class="col"><h3 class="h5 mb-0">Realisasi (Current)</h3></div></div>
                <div class="table-risk-map" id="currentMap">
                    <table class="map-table w-100">
                      <tbody>
                            @for($likelihood = 5; $likelihood >= 1; $likelihood--)
                            <tr>
                                @if ($likelihood == 5) <td rowspan="5" class="side-title"><div class="divider m-0"><div class="divider-text">LIKELIHOOD</div></div></td> @endif
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
                            <tr><td class="useless-cell"></td><td colspan="5" class="footer-title"><div class="divider m-0"><div class="divider-text">IMPACT</div></div></td></tr>
                        </tbody>
                    </table>
                    <div class="risk-map-legend d-flex justify-content-center gap-4 mt-3">
                        <div class="d-flex align-items-center gap-1"><i class="bx bxs-circle current fs-5 text-primary"></i> Realisasi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TABEL TOP 10 RISIKO --}}
<div class="card shadow-sm mb-5">
    <div class="card-header bg-light">
        <h5 class="mb-0 fw-bold">Daftar 10 Risiko Teratas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm mb-0 align-middle">
                <thead class="text-center bg-secondary text-white align-middle">
                    <tr>
                        <th rowspan="2" width="5%">Kode</th>
                        <th rowspan="2" width="15%">Nama Proyek</th>
                        <th rowspan="2" width="20%">Peristiwa Risiko</th>
                        <th colspan="3" class="bg-gray">Inherent</th>
                        <th colspan="3" class="bg-primary-subtle">Realisasi (Terpublish)</th>
                        <th rowspan="2" width="5%">Aksi</th>
                    </tr>
                    <tr>
                        <th class="bg-gray" style="min-width: 110px;">Nilai Dampak</th>
                        <th class="bg-gray" style="min-width: 110px;">Eksposur</th>
                        <th class="bg-gray" style="min-width: 130px;">Level Risiko</th>

                        <th class="bg-primary-subtle" style="min-width: 110px;">Nilai Dampak</th>
                        <th class="bg-primary-subtle" style="min-width: 110px;">Eksposur</th>
                        <th class="bg-primary-subtle" style="min-width: 130px;">Level Risiko</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($top10Risks as $idx => $risk)
                        @php
                            $namaPeristiwa = $risk->peristiwa_risiko_id === 0 ? $risk->rencana_kegiatan : ($risk->peristiwaRisiko->title ?? '-');
                            $routeDetail = url('projects/' . $risk->project_periode_list_id . '/risks/' . $risk->id . '/view');

                            $inherentClass = strtolower(str_replace(' ', '-', str_replace('to ', '', $risk->inherent_level)));
                            $currentClass = strtolower(str_replace(' ', '-', str_replace('to ', '', $risk->current_level)));
                        @endphp
                        <tr>
                            <td class="text-center fw-bold">R{{ $idx + 1 }}</td>
                            <td>{{ $risk->project->project_name }}</td>
                            <td>{{ $namaPeristiwa }}</td>

                            {{-- INHERENT --}}
                            <td class="text-end">Rp {{ number_format($risk->inherent_dampak, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($risk->inherent_eksposur, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $inherentClass }} w-100">{{ $risk->inherent_level }} - {{ $risk->inherent_skala }}</span>
                            </td>

                            {{-- REALISASI --}}
                            <td class="text-end fw-bold">Rp {{ number_format($risk->current_dampak, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-danger">Rp {{ number_format($risk->current_eksposur, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $currentClass }} w-100">{{ $risk->current_level }} - {{ $risk->current_skala }}</span>
                            </td>

                            <td class="text-center">
                                <a href="{{ $routeDetail }}" target="_blank" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Detail"><i class="bx bx-link-external"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center p-4">Tidak ada data risiko terpublish di periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Top Loss Event Proyek</h2>
<hr class="mb-4">

<div class="row g-4 mb-4">
    {{-- TABEL TOP 10 LED PROYEK --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0 fw-bold">Top 10 Loss Event Database (LED) Lintas Proyek</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm table-bordered mb-0 align-middle">
                        <thead class="bg-light text-center">
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
                            @forelse ($topLedProyek as $event)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ optional($event->project)->project_name ?? '-' }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($event->tanggal_kejadian)->format('d M Y') }}</td>
                                    <td>{{ $event->nama_kejadian ?? '-' }}</td>
                                    <td>{{ $event->peristiwa_risiko_id == 0 ? $event->deskripsi_kejadian : ($event?->peristiwaRisiko?->title ?? '-') }}</td>
                                    <td class="text-center">{{ optional($event->kategoriKejadian)->kategori_kejadian ?? '-' }}</td>
                                    <td class="text-end text-danger fw-bold">Rp {{ number_format($event->nilai_kerugian_finansial, 0, ',', '.') }}</td>

                                    {{-- AKSI MENUJU HALAMAN DETAIL LED --}}
                                    <td class="text-center">
                                        <a href="{{ url('project-led/' . $event->id) }}" target="_blank" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Lihat Detail LED">
                                            <i class='bx bx-show'></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center p-4 text-muted">
                                        Tidak ada data Loss Event untuk periode dan filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL PETA RISIKO --}}
<div class="modal fade" id="modalPetaRisiko" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-0  border-0 shadow">
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
                                <th width="30%">Proyek</th>
                                <th width="40%">Peristiwa Risiko</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle"></tbody>
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
  #modalPetaRisiko { z-index: 10005 !important; }
  .modal-backdrop { z-index: 10004 !important; }
  .data-cell { position: relative; transition: all 0.2s ease-in-out; cursor: pointer; }
  .data-cell:hover { box-shadow: inset 0 0 15px rgba(0,0,0,0.3); opacity: 0.9; }

  .fullscreen-container .row { height: calc(100vh - 150px); }
  .fullscreen-container .col-md-6 { height: 100%; display: flex; flex-direction: column; }
  .fullscreen-container .table-risk-map { flex-grow: 1; display: flex; flex-direction: column; }
  .fullscreen-container .map-table { height: 100%; }
  .fullscreen-container .data-cell { height: 100%; min-height: 80px; }

  .box-inherent, .box-residual, .box-current {
      padding: 2px 4px !important;
      border-radius: 3px;
      font-weight: 700;
      font-size: 0.7rem !important;
      line-height: 1.1;
      letter-spacing: -0.2px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
  }
  .box-inherent { background-color: #ffffff; color: #000000; border: 1px solid #000000; }
  .box-residual { background-color: #000000; color: #ffffff; border: 1px solid #000000; }
  .box-current { background-color: #007bff; color: #ffffff; border: 1px solid #007bff; }

  .kode-peristiwa {
      display: flex;
      flex-wrap: wrap;
      gap: 3px !important;
      position: absolute;
      bottom: 5px;
      right: 0;
      width: calc(100% - 5px) !important;
  }
  .fullscreen-container {
      position: fixed !important;
      top: 0; left: 0;
      width: 100vw !important; height: 100vh !important;
      background: #ffffff;
      z-index: 9999;
      padding: 20px;
      overflow-y: auto;
      border-radius: 0 !important;
      box-shadow: none !important;
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

<script type="text/javascript">
$(document).ready(function() {
    function applyFilterAndRefresh() {
        var unitId = $('#unit_selector').val();
        var period = $('#period_selector').val();
        let baseUrl = '{{ route("executive-summary-konsolidasi") }}';
        let params = new URLSearchParams();

        if (unitId) params.append('unit_id', unitId);
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
        onChange: function(selectedDates, dateStr, instance) { applyFilterAndRefresh(); }
    });

    $('#unit_selector').on('change', function() { applyFilterAndRefresh(); });

    // ============================================
    // PIE CHART EKSPOSUR DIVISI
    // ============================================
    const pieData = @json($pieChartData);
    if(pieData.length > 0) {
        var pieChartDom = document.getElementById('pieChartEksposur');
        var myPieChart = echarts.init(pieChartDom);

        var option = {
            tooltip: {
                trigger: 'item',
                formatter: function (params) {
                    let val = 'Rp ' + params.data.value.toLocaleString('id-ID');
                    let proy = params.data.project_count;
                    let risk = params.data.risk_count;
                    return `<b>${params.name}</b><br/>
                            Total Eksposur: ${val}<br/>
                            Total Risiko: ${risk} (${params.percent}%)<br/>
                            Proyek Aktif: ${proy}`;
                }
            },
            legend: {
                top: 'bottom',
                type: 'scroll',
                formatter: function (name) {
                    let item = pieData.find(x => x.name === name);
                    if(item) {
                        let compactVal = (item.value / 1000000000).toFixed(1) + ' Miliar';
                        return name + ' - ' + compactVal;
                    }
                    return name;
                }
            },
            series: [{
                name: 'Eksposur Realisasi',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 10,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: { show: false, position: 'center' },
                emphasis: {
                    label: { show: true, fontSize: 16, fontWeight: 'bold' }
                },
                labelLine: { show: false },
                data: pieData
            }]
        };
        myPieChart.setOption(option);
        $(window).on('resize', function(){ myPieChart.resize(); });
    } else {
        $('#pieChartEksposur').html('<div class="d-flex h-100 align-items-center justify-content-center text-muted border rounded bg-light"><div class="text-center"><i class="bx bx-folder-open fs-1 mb-2"></i><br>Tidak ada data risiko kuantitatif di divisi terkait.</div></div>');
    }

    // ============================================
    // BAR CHART TOP 10 PROYEK
    // ============================================
    const barData = @json($top10ProjectsExposure);

    if(barData.length > 0) {
        var barChartDom = document.getElementById('barChartEksposurProyek');
        var myBarChart = echarts.init(barChartDom);

        // Fungsi format nilai uang agar tidak menumpuk di Sumbu X
        function formatRupiahKompak(value, isAxis = false) {
            if (value === 0 || value == null) return isAxis ? '0' : 'Rp 0';
            let num = Math.abs(value);

            if (num >= 1e12) return (isAxis ? '' : 'Rp ') + (value / 1e12).toFixed(isAxis ? 0 : 2) + ' T';
            if (num >= 1e9) return (isAxis ? '' : 'Rp ') + (value / 1e9).toFixed(isAxis ? 0 : 2) + ' M';
            if (num >= 1e6) return (isAxis ? '' : 'Rp ') + (value / 1e6).toFixed(isAxis ? 0 : 2) + ' Jt';

            return (isAxis ? '' : 'Rp ') + value.toLocaleString('id-ID');
        }

        var optionBar = {
            tooltip: {
                trigger: 'axis',
                axisPointer: { type: 'shadow' },
                formatter: function (params) {
                    let val = formatRupiahKompak(params[0].value);
                    // Tampilkan nama lengkap hanya saat di-hover
                    return `<b style="white-space: normal; max-width: 250px; display: block;">${params[0].name}</b><hr style="margin: 5px 0;">Total Eksposur: ${val}`;
                }
            },
            grid: {
                left: '2%',
                right: '15%', // Sediakan ruang di kanan agar label angka tidak terpotong
                bottom: '5%',
                top: '5%',
                containLabel: true
            },
            xAxis: {
                type: 'value',
                axisLabel: {
                    hideOverlap: true,
                    formatter: function (value) {
                        return formatRupiahKompak(value, true);
                    }
                },
                splitLine: {
                    lineStyle: { type: 'dashed', color: '#eeeeee' }
                }
            },
            yAxis: {
                type: 'category',
                data: barData.map(item => item.name),
                axisLabel: {
                    interval: 0,
                    fontSize: 11,
                    // Fallback pemotong teks otomatis jika nama proyek sangat panjang
                    formatter: function (value) {
                        if (value.length > 25) {
                            return value.substring(0, 25) + '...';
                        }
                        return value;
                    }
                }
            },
            series: [{
                name: 'Eksposur Realisasi',
                type: 'bar',
                // MENGATASI TIPIS VERTIKAL: Kunci ketebalan bar ke 22px
                barWidth: 22,

                // MENGATASI TAMPILAN KOSONG: Efek Full Width Track
                showBackground: true,
                backgroundStyle: {
                    color: 'rgba(180, 180, 180, 0.15)', // Track abu-abu transparan
                    borderRadius: [0, 4, 4, 0]
                },

                data: barData.map(item => item.value),
                itemStyle: {
                    color: '#EE6666',
                    borderRadius: [0, 4, 4, 0]
                },
                label: {
                    show: true,
                    position: 'right',
                    formatter: function (params) {
                        return formatRupiahKompak(params.value);
                    },
                    fontSize: 11,
                    fontWeight: 'bold',
                    color: '#333'
                }
            }]
        };
        myBarChart.setOption(optionBar);
        $(window).on('resize', function(){ myBarChart.resize(); });
    } else {
        $('#barChartEksposurProyek').html('<div class="d-flex h-100 align-items-center justify-content-center text-muted border rounded bg-light"><div class="text-center"><i class="bx bx-folder-open fs-1 mb-2"></i><br>Tidak ada data proyek aktif.</div></div>');
    }

    // 3. FULLSCREEN MAP
    $('#btnFullscreenMap').on('click', function() {
        const mapContainer = $('#mapCardContainer');
        mapContainer.toggleClass('fullscreen-container');
        if (mapContainer.hasClass('fullscreen-container')) {
            $(this).html('<span class="bx bx-exit-fullscreen me-1"></span> Tutup Layar Penuh').removeClass('btn-outline-primary').addClass('btn-danger');
            $('body').css('overflow', 'hidden');
        } else {
            $(this).html('<span class="bx bx-fullscreen me-1"></span> Perbesar Peta').removeClass('btn-danger').addClass('btn-outline-primary');
            $('body').css('overflow', '');
        }
    });

    // 4. RENDER PETA RISIKO DARI JAVASCRIPT
    const formattedMaps = @json($formattedCurrentRiskMaps);
    const baseUrl = `{{ url('projects') }}`;

    function renderCellBadges(cell, risksArray, typeClass) {
        if (!risksArray || risksArray.length === 0) return;
        let html = '';
        let maxDisplay = 6;
        let count = risksArray.length;

        for (let i = 0; i < Math.min(count, maxDisplay); i++) {
            html += `<span class="${typeClass}" data-bs-toggle="tooltip" title="${risksArray[i].project_name}">${risksArray[i].riskNumber}${risksArray[i].displayMark || ''}</span>`;
        }
        if (count > maxDisplay) {
            html += `<span class="${typeClass} bg-secondary border-secondary text-white" data-bs-toggle="tooltip" title="Ada ${count - maxDisplay} risiko lain">+${count - maxDisplay}</span>`;
        }
        cell.find('.kode-peristiwa').append(html);
    }

    function populateMapData() {
        let mapInherent = {}; let mapResidual = {}; let mapCurrent = {};

        (formattedMaps.inherent || []).forEach(r => {
            const matrix = r.skala_dampak + '-' + r.skala_probabilitas;
            if(!mapInherent[matrix]) mapInherent[matrix] = [];
            mapInherent[matrix].push(r);
        });

        (formattedMaps.residual || []).forEach(r => {
            const matrix = r.skala_dampak + '-' + r.skala_probabilitas;
            if(!mapResidual[matrix]) mapResidual[matrix] = [];
            mapResidual[matrix].push(r);
        });

        (formattedMaps.current || []).forEach(r => {
            const matrix = r.skala_dampak + '-' + r.skala_probabilitas;
            if(!mapCurrent[matrix]) mapCurrent[matrix] = [];
            mapCurrent[matrix].push(r);
        });

        $('#inherentMap .data-cell').each(function() {
            let matrix = $(this).data('matrix');
            $(this).data('risks-inherent', mapInherent[matrix] || []);
            $(this).data('risks-residual', mapResidual[matrix] || []);
            renderCellBadges($(this), mapInherent[matrix], 'box-inherent');
            renderCellBadges($(this), mapResidual[matrix], 'box-residual');
        });

        $('#currentMap .data-cell').each(function() {
            let matrix = $(this).data('matrix');
            $(this).data('risks-current', mapCurrent[matrix] || []);
            renderCellBadges($(this), mapCurrent[matrix], 'box-current');
        });

        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    populateMapData();

    // 5. MODAL DETAIL SAAT CELL DIKLIK
    $('.data-cell').on('click', function() {
        let isCurrentMap = $(this).closest('#currentMap').length > 0;
        let matrix = $(this).data('matrix');
        let levelText = $(this).find('.posisi-risiko').text() || '-';
        let tbody = $('#tableModalRisiko tbody');

        tbody.empty();
        let hasData = false;

        const createModalRow = (r, type) => {
            let route = `${baseUrl}/${r.project_periode_list_id}/risks/${r.risk_id}/view`;
            let badgeClass = type === 'Inherent' ? 'bg-white text-dark border border-dark' : (type === 'Residual' ? 'bg-dark text-white' : 'bg-primary text-white');
            return `
                <tr>
                    <td class="text-center">
                        <div class="fw-bold mb-1">${r.riskNumber}</div>
                        <span class="badge ${badgeClass} w-100">${type}</span>
                    </td>
                    <td><span class="text-dark w-100 text-wrap text-start">${r.project_name}</span></td>
                    <td>${r.peristiwa}</td>
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
});
</script>
@endpush
