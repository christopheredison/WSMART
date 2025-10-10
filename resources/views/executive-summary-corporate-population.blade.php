@extends('layouts.default')

@section('dashboard')
{{-- HEADER --}}
<div class="row mb-5">
    <div class="col-12">
        <div class="card border-0 dashboard-header">
            <img src="{{ asset('assets/img/dashboard-header.webp') }}" alt="dashboard" style="object-fit: cover;">
            <div class="card-header border-0 justify-content-end">
                <h1 class="mb-0">Executive Summary Korporat</h1>
                <h6>Statistik per tanggal {{ now()->isoFormat('D MMMM YYYY') }}</h6>
            </div>
        </div>
    </div>
</div>

{{-- SECTION: MANAJEMEN KINERJA BERBASIS RISIKO --}}
<h2 class="mb-4 text-primary fw-bold"><i class="fas fa-shield-alt me-2"></i>Manajemen Kinerja Berbasis Risiko</h2>
<hr class="mb-4">
<div class="row g-4 mb-5">
    {{-- Hasil Usaha --}}
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-primary text-white"><h5 class="mb-0 fw-bold">Hasil Usaha</h5></div>
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2"><span>LSP Rencana</span> <span class="fw-bold fs-5">Rp {{ number_format($kinerjaData['lsp_rencana']) }}</span></div>
                <div class="d-flex justify-content-between align-items-center"><span>LSP Realisasi</span> <span class="fw-bold fs-5">Rp {{ number_format($kinerjaData['lsp_realisasi']) }}</span></div>
            </div>
        </div>
    </div>
    {{-- LED Divisi --}}
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-danger text-white"><h5 class="mb-0 fw-bold">Loss Event Database (LED) Divisi</h5></div>
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2"><span>Divisi Operasi</span> <span class="fw-bold fs-5">Rp {{ number_format($kinerjaData['led_divisi_operasi']) }}</span></div>
                <div class="d-flex justify-content-between align-items-center"><span>Divisi Fungsi</span> <span class="fw-bold fs-5">Rp {{ number_format($kinerjaData['led_divisi_fungsi']) }}</span></div>
            </div>
        </div>
    </div>
    {{-- LED Proyek --}}
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-warning text-dark"><h5 class="mb-0 fw-bold">Loss Event Database (LED) Proyek</h5></div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div class="text-center">
                    <div class="fw-bold fs-5">Rp {{ number_format($kinerjaData['led_proyek']) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION: KEY RISK INDICATOR (KRI) --}}
<h2 class="mb-4 text-primary fw-bold"><i class="fas fa-tachometer-alt me-2"></i>Key Risk Indicator (KRI)</h2>
<hr class="mb-4">
<div class="row g-4 mb-5">
    {{-- KRI Waspada --}}
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-warning text-dark"><h5 class="mb-0 fw-bold">Hati-hati (Waspada)</h5></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($waspadaKRI as $kri)
                    <li class="list-group-item">
                      <strong>
                        <a href="">
                          {{ $kri['nama_sumber'] }}:
                        </a>
                      </strong>
                      {{ $kri['nama_kri'] }}
                    </li>
                    @empty
                    <li class="list-group-item text-muted">Tidak ada KRI berstatus waspada.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    {{-- KRI Bahaya --}}
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-danger text-white"><h5 class="mb-0 fw-bold">Bahaya</h5></div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($bahayaKRI as $kri)
                    <li class="list-group-item">
                      <strong>
                        <a href="">
                          {{ $kri['nama_sumber'] }}:
                        </a>
                      </strong> 
                      {{ $kri['nama_kri'] }}
                    </li>
                    @empty
                    <li class="list-group-item text-muted">Tidak ada KRI berstatus bahaya.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- SECTION: RISIKO HIGH & MODERATE TO HIGH --}}
<h2 class="mb-4 text-primary fw-bold"><i class="fas fa-arrow-up me-2"></i>Risiko High & Moderate to High</h2>
<hr class="mb-4">
<div class="card shadow-sm mb-5">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" class="align-middle text-center">Divisi</th>
                        <th colspan="2" class="text-center">Total Event Risiko</th>
                        <th rowspan="2" class="text-center">Dampak Risiko Inheren (Rp.)</th>
                        <th rowspan="2" class="text-center">Biaya Perlakuan Risiko Rencana (Rp.)</th>
                        <th rowspan="2" class="text-center">Dampak Risiko Residual Rencana (Rp.)</th>
                        <th rowspan="2" class="text-center">Biaya Perlakuan Risiko Realisasi (Rp.)</th>
                        <th rowspan="2" class="text-center">Dampak Risiko Residual Realisasi (Rp.)</th>
                    </tr>
                    <tr>
                        <th class="text-center">Total</th>
                        <th class="text-center bg-danger text-white" style="width: 100px;">High & Moderate to High</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riskProfileByDivision as $profile)
                    <tr>
                        <td>
                          <a href="{{ route('risk-register-unit.periods.show', ['period' => $profile['periode'], 'unit_id' => $profile['unit_id']]) }}" target="_blank">
                            {{ $profile['nama_divisi'] }}
                          </a>
                        </td>
                        <td class="text-center">{{ $profile['total_risiko'] }}</td>
                        <td class="text-center fw-bold">{{ $profile['total_risiko_high'] }}</td>
                        <td class="text-end">{{ number_format($profile['dampak_inheren']) }}</td>
                        <td class="text-end">{{ number_format($profile['biaya_perlakuan_rencana']) }}</td>
                        <td class="text-end">{{ number_format($profile['dampak_residual_rencana']) }}</td>
                        <td class="text-end">{{ number_format($profile['biaya_perlakuan_realisasi']) }}</td>
                        <td class="text-end">{{ number_format($profile['dampak_residual_realisasi']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted">Tidak ada data risiko.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- SECTION: HEATMAP --}}
<div class="row g-4 mb-5">
    {{-- Heatmap --}}
    <div class="col-md-12">
        <div class="card h-100 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-th me-2"></i>Heatmap Risiko</h5>
            </div>
            <div class="card-body">
              <div class="table-risk-map">
                  <table class="map-table">
                      <tbody>
                          @for ($likelihood = 5; $likelihood >= 1; $likelihood--)
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
                              @for ($impact = 1; $impact <= 5; $impact++)
                                  @php
                                      $matrixKey = $impact . '-' . $likelihood;
                                      $riskMap = $riskMaps->get($matrixKey);
                                      $level = optional($riskMap)->level_risiko ?? '';
                                      $nilai = optional($riskMap)->nilai_risiko ?? '';
                                      $bgColorClass = 'bg-' . str_replace([' to ', ' '], ['-', ''], strtolower($level));

                                      $risksInherent = $heatmapData['inherent'][$matrixKey] ?? [];
                                      $risksRencana = $heatmapData['rencana'][$matrixKey] ?? [];
                                      $risksRealisasi = $heatmapData['realisasi'][$matrixKey] ?? [];
                                      $allCellRisks = array_merge($risksInherent, $risksRencana, $risksRealisasi);
                                  @endphp
                                  <td>
                                      <div class="data-cell {{ $bgColorClass }}"
                                        @if(count($allCellRisks) > 0)
                                            data-bs-toggle="modal" 
                                            data-bs-target="#heatmapDetailModal"
                                            data-risks='@json($allCellRisks)'
                                            data-title="Risiko pada Level '{{$level}}' ({{$nilai}})"
                                        @endif
                                      >
                                          <div class="posisi-risiko">
                                            <span class="d-block mb-1">
                                              {{ $riskMap['level_risiko'] }}
                                            </span>
                                            {{ $riskMap['nilai_risiko'] }}
                                          </div>

                                          {{-- Badge ditampilkan berjajar di tengah --}}
                                          @if(count($risksInherent) > 0) <span class="box-inherent">{{ count($risksInherent) }}</span> @endif
                                          @if(count($risksRencana) > 0) <span class="box-rencana">{{ count($risksRencana) }}</span> @endif
                                          @if(count($risksRealisasi) > 0) <span class="box-realisasi">{{ count($risksRealisasi) }}</span> @endif
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
                      <i class='bx bxs-circle current'></i>
                      Residual Rencana
                    </div>
                    <div class="d-flex align-items-center gap-1">
                      <i class='bx bxs-circle residual'></i>
                      Residual Realisasi
                    </div>
                  </div>
                  <!-- end::Legend -->
              </div>
            </div>
        </div>
    </div>
</div>

<h2 class="mt-7 mb-4 text-primary fw-bold"><i class="fas fa-check-circle me-2"></i>Efektivitas Risiko</h2>
<hr class="mb-4">
<div class="row g-4 mb-5">
    {{-- Kolom Kiri: Ringkasan & Chart --}}
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header border-0 pb-0">
                <h5 class="mb-0 fw-bold">Ringkasan Efektivitas</h5>
            </div>
            <div class="card-body">
                {{-- Data untuk legenda chart --}}
                <div class="border rounded-3 p-3 mb-3 w-100">
                    <div class="mb-2 d-flex align-items-center gap-2">
                        <span class="d-inline-block" style="width:20px; height:20px; border-radius: 3px; background-color: #5470C6;"></span>
                        <span>Efektif ({{ $efektifRisks->count() }})</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-block" style="width:20px; height:20px; border-radius: 3px; background-color: #EE6666;"></span>
                        <span>Tidak Efektif ({{ $tidakEfektifRisks->count() }})</span>
                    </div>
                </div>
                {{-- Wadah untuk chart --}}
                <div class="min-vh-25">
                    <div id="efektivitasChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Detail Risiko dalam Akordeon --}}
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header border-0 pb-0">
                <h5 class="mb-0 fw-bold">Detail Risiko Selesai (Closed)</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordionEfektivitas">
                    {{-- Akordeon untuk Risiko Efektif --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingEfektif">
                            <button class="accordion-button fs-6 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEfektif" aria-expanded="true">
                                Perlakuan Efektif <span class="badge rounded-pill bg-info ms-2">{{ $efektifRisks->count() }}</span>
                            </button>
                        </h2>
                        <div id="collapseEfektif" class="accordion-collapse collapse show" data-bs-parent="#accordionEfektivitas">
                            <div class="accordion-body p-0" style="max-height: 250px; overflow-y: auto;">
                                <ul class="list-group list-group-flush">
                                    @forelse($efektifRisks as $risk)
                                        <li class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <span>{{ optional($risk->peristiwaRisiko)->title ?? 'Risiko ID: '.$risk->id }}</span>
                                                <span class="badge bg-light text-dark">Nilai: {{ $risk->efektivitas_perlakuan_risiko ?? 0 }}</span>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-muted">Tidak ada risiko yang dinilai efektif.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                    {{-- Akordeon untuk Risiko Tidak Efektif --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTidakEfektif">
                            <button class="accordion-button fs-6 py-3 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTidakEfektif" aria-expanded="false">
                                Perlakuan Tidak Efektif <span class="badge rounded-pill bg-danger ms-2">{{ $tidakEfektifRisks->count() }}</span>
                            </button>
                        </h2>
                        <div id="collapseTidakEfektif" class="accordion-collapse collapse" data-bs-parent="#accordionEfektivitas">
                            <div class="accordion-body p-0" style="max-height: 250px; overflow-y: auto;">
                                <ul class="list-group list-group-flush">
                                    @forelse($tidakEfektifRisks as $risk)
                                        <li class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <span>{{ optional($risk->peristiwaRisiko)->title ?? 'Risiko ID: '.$risk->id }}</span>
                                                <span class="badge bg-light text-dark">Nilai: {{ $risk->efektivitas_perlakuan_risiko ?? 0 }}</span>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-muted">Tidak ada risiko yang dinilai tidak efektif.</li>
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

{{-- SECTION: ANALISIS RISIKO PROYEK --}}
<h2 class="mb-4 text-primary fw-bold"><i class="fas fa-chart-pie me-2"></i>Analisis Risiko Proyek</h2>
<hr class="mb-4">
<div class="row g-4">
    {{-- Top 10 LED Proyek --}}
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header"><h5 class="mb-0 fw-bold">Top 10 LED Proyek (by Peristiwa)</h5></div>
            <div class="card-body" style="height: 300px;"><canvas id="topLedProyekChart"></canvas></div>
        </div>
    </div>
    {{-- Top 10 Eksposur Risiko --}}
    <div class="col-md-6">
        <div class="card h-100 shadow-sm">
            <div class="card-header"><h5 class="mb-0 fw-bold">Top 10 Eksposur Risiko Residual Proyek</h5></div>
            <div class="card-body" style="height: 300px;"><canvas id="topEksposurProyekChart"></canvas></div>
        </div>
    </div>
</div>

<div class="modal fade" id="heatmapDetailModal" tabindex="-1" aria-labelledby="heatmapDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="heatmapDetailModalLabel">Detail Risiko</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Berikut adalah daftar risiko yang berada di sel ini:</p>
        <ul class="list-group" id="risk-detail-list">
          {{-- Konten akan diisi oleh JavaScript --}}
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
    .box-inherent, .box-rencana, .box-realisasi {
        color: #fff; 
        padding: 2px 6px; 
        border-radius: 4px; 
        font-size: 0.75rem; 
        font-weight: bold; 
        min-width: 25px;
    }
    .box-inherent {
      color: #000;
      background-color: #FFF;
      position: absolute;
      top: 0.5em;
      left: 0.5em;
    }
    .box-rencana { 
      background-color: #007bff;
      position: absolute;
      top: 0.5em;
      right: 0.5em;
    }
    .box-realisasi { 
      background-color: #000000;
      position: absolute;
      bottom: 0.5em;
      right: 0.5em;
    }

    .map-table .data-cell {
      justify-content: center;
      align-items: center;
    }
    .map-table .data-cell .posisi-risiko {
      top: unset;
      right: unset;
      max-width: 75px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const renderPieChart = (canvasId, chartData, chartLabel) => {
        const ctx = document.getElementById(canvasId);
        if (!ctx || !chartData.labels || chartData.labels.length === 0) return;
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: chartLabel,
                    data: chartData.data,
                    backgroundColor: ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E7E9ED', '#8DDF3C', '#F56565', '#4299E1'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const currentValue = context.raw;
                                const percentage = ((currentValue / total) * 100).toFixed(2) + '%';
                                
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('id-ID').format(currentValue) + ' (' + percentage + ')';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    };
    
    // 1. Efektivitas Chart
    const efektivitasCtx = document.getElementById('efektivitasChart');
    if (efektivitasCtx) {
        // Cek jika ada data untuk ditampilkan
        if ({{ $efektivitasData['efektif'] }} > 0 || {{ $efektivitasData['tidak_efektif'] }} > 0) {
            new Chart(efektivitasCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Efektif', 'Tidak Efektif'],
                    datasets: [{
                        data: [{{ $efektivitasData['efektif'] }}, {{ $efektivitasData['tidak_efektif'] }}],
                        backgroundColor: ['#5470C6', '#EE6666'],
                        borderWidth: 0,
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Legenda sudah kita buat manual di HTML
                        },
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                return percentage;
                            },
                            color: '#fff',
                            font: { weight: 'bold', size: 14 }
                        }
                    }
                }
            });
        } else {
            // Tampilkan pesan jika tidak ada data sama sekali
            efektivitasCtx.parentElement.innerHTML = '<div class="d-flex justify-content-center align-items-center h-100 text-muted">Tidak ada risiko yang telah ditutup.</div>';
        }
    }

    // 2. Top 10 LED Proyek Chart
    renderPieChart('topLedProyekChart', {
        labels: {!! json_encode($topLedProyek->pluck('peristiwaRisiko.title')) !!},
        data: {!! json_encode($topLedProyek->pluck('total_kejadian')) !!}
    }, 'Total Kejadian');

    // 4. Top 10 Eksposur Proyek Chart
    renderPieChart('topEksposurProyekChart', {
        labels: {!! json_encode($topEksposurProyek->map(fn($item) => $item->projectRisk->peristiwaRisiko->title ?? 'N/A')) !!},
        data: {!! json_encode($topEksposurProyek->pluck('eksposure_risiko')) !!}
    }, 'Eksposur Risiko');

    const heatmapDetailModal = document.getElementById('heatmapDetailModal');
    if (heatmapDetailModal) {
        heatmapDetailModal.addEventListener('show.bs.modal', function (event) {
            const cell = event.relatedTarget; // sel <td> yang di-klik
            
            // Ambil data dari atribut data-*
            const risks = JSON.parse(cell.getAttribute('data-risks'));
            const title = cell.getAttribute('data-title');

            // Update judul modal
            const modalTitle = heatmapDetailModal.querySelector('.modal-title');
            modalTitle.textContent = title;

            // Dapatkan elemen list di body modal
            const riskList = heatmapDetailModal.querySelector('#risk-detail-list');
            
            // Kosongkan list sebelumnya
            riskList.innerHTML = '';

            // Jika tidak ada risiko, tampilkan pesan
            if (risks.length === 0) {
                riskList.innerHTML = '<li class="list-group-item">Tidak ada detail risiko di sel ini.</li>';
                return;
            }

            // Buat daftar link untuk setiap risiko
            risks.forEach(risk => {
                const listItem = document.createElement('li');
                listItem.classList.add('list-group-item');
                
                const link = document.createElement('a');
                link.href = risk.url;
                link.textContent = risk.name;
                link.target = "_blank"; // Buka di tab baru

                listItem.appendChild(link);
                riskList.appendChild(listItem);
            });
        });
    }
});
</script>
@endpush