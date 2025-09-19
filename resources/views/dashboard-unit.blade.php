@extends('layouts.default')

@section('dashboard')
<!--========================= Dashboard Header =========================-->
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="../assets/img/dashboard-header2.webp" alt="dashboard">
      <div class="card-header border-0">
        <h1 class="mb-auto mt-3 mt-md-6">Risk Dashboard Divisi</h1>
        <h6>Statistik per tanggal {{ now()->format('d M Y') }}</h6>
      </div>
    </div>
  </div>
</div>

<!--========================= Input Filter =========================-->
<div class="row input-selector-rounded g-3 mb-3">
  <div class="col-12">
    <form id="filterForm" action="{{ url()->current() }}" method="GET">
      <div class="row g-3 g-xxl-2 input-filter-container">
        <div class="col-auto">
          <select name="periode_id" id="periode_selector" class="form-select select2 js-select-hide-search">
            <option value="" selected disabled>Periode</option>
            @foreach ($periodes as $periode)
              <option value="{{ $periode->id }}" {{$periode->id == $selectedPeriode->id ? 'selected' : ''}}>{{ $periode->tahun }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-5 col-xxl-4">
          <select name="unit_id" id="unit_selector" class="form-select select2 js-select-hide-search">
            <option value="" selected disabled>Divisi</option>
            @foreach ($units as $unit)
              <option value="{{ $unit->id }}" {{$unit->id == $selectedUnit->id ? 'selected' : ''}}>{{ $unit->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </form>
  </div>

  <!--========================= Dashboard Chart Start =========================-->
  {{-- <div class="col-12">
    <div class="row g-3">
      <div class="col-6 col-md-3">
        <div class="card card-sm" id="kpi-card">
          <div class="card-header d-flex align-items-center pb-xxl-0 gap-2">
            <div class="bg-warning-subtle rounded-3 p-1">
              <div class="lead__icon lead__icon_sm">
                <span class="svg-icon svg-icon-warning">
                  @include('partials.icon-abs02')
                </span>
              </div>
            </div>
            <div class="title">% Capaian KPI</div>
          </div>
          <div class="card-body">
            <div class="ratio ratio-1x1 ratio-lg-4x3 ratio-xxl-16x9 ratio-xxxl-21x9">
              <div id="kpi-chart"></div>
            </div>
          </div>
          <div class="card-footer">
            <div class="row w-100 gx-0">
              <div class="col-6 col-md-auto changes-summary">
                <span class="up-label">15%</span>
              </div>
              <div class="col-6 col-md-auto last-changes">
                04 Nov 2024
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-sm" id="rpr-card">
          <div class="card-header d-flex align-items-center pb-xxl-0 gap-2">
            <div class="bg-primary-subtle rounded-3 p-1">
              <div class="lead__icon lead__icon_sm">
                <span class="svg-icon svg-icon-primary">
                  @include('partials.icon-abs03')
                </span>
              </div>
            </div>
            <div class="title">% Realisasi Perlakuan Risiko</div>
          </div>
          <div class="card-body">
            <div class="ratio ratio-1x1 ratio-lg-4x3 ratio-xxl-16x9 ratio-xxxl-21x9">
              <div id="rpr-chart"></div>
            </div>
          </div>
          <div class="card-footer">
            <div class="row w-100 gx-0">
              <div class="col-6 col-md-auto changes-summary">
                <span class="up-label">28%</span>
              </div>
              <div class="col-6 col-md-auto last-changes">
                04 Nov 2024
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-sm" id="jkk-card">
          <div class="card-header d-flex align-items-center gap-2">
            <div class="bg-danger-subtle rounded-3 p-1">
              <div class="lead__icon lead__icon_sm">
                <span class="svg-icon svg-icon-danger">
                  @include('partials.icon-barchart01')
                </span>
              </div>
            </div>
            <div class="title">Jumlah Kejadian Kerugian</div>
          </div>
          <div class="card-body d-flex-center align-items-center">
            <div id="jkk-chart" class="d-block text-center">
              <div class="counter" id="jkk-counter">1</div>
            </div>
          </div>
          <div class="card-footer">
            <div class="row w-100 gx-0">
              <div class="col-6 col-md-auto changes-summary">
                <span class="up-label">1</span>
              </div>
              <div class="col-6 col-md-auto last-changes">
                04 Nov 2024
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card card-sm" id="tkmru-card">
          <div class="card-header d-flex align-items-center gap-2">
            <div class="bg-success-subtle rounded-3 p-1">
              <div class="lead__icon lead__icon_sm">
                <span class="svg-icon svg-icon-success">
                  @include('partials.icon-piechart3')
                </span>
              </div>
            </div>
            <div class="title">RMI SCORE</div>
          </div>
          <div class="card-body d-flex align-items-center">
            <div class="d-block w-100">
              <div id="tkmru-chart" class="d-flex flex-column flex-center gap-2">
                <div class="counter" id="tkmru-counter">0</div>
                <span class="text-center" id="tkmru-notes"></span>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="row w-100 gx-0">
              <div class="col-6 col-md-auto changes-summary">
                <span class="up-label">0</span>
              </div>
              <div class="col-6 col-md-auto last-changes">
                04 Nov 2024
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> --}}
</div>
<!--========================= Dashboard Chart End =========================-->

<!--========================= Dashboard Content Start =========================-->
<div class="col-12 g-3 mb-3 dashboard-content">
  <!-- Profil Risiko -->
  <div class="col-12 mb-3">
    <div class="card">
      <div class="card-header stepper border-0 pb-0">
        <div class="nav-link active d-flex align-items-center p-0">
          <span class="h3 mb-0">Profil Risiko</span>
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
                  <select class="form-select" id="quarterSelect">
                    <option value="1">Quarter 1</option>
                    <option value="2">Quarter 2</option>
                    <option value="3">Quarter 3</option>
                    <option value="4">Quarter 4</option>
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
                  <th>No</th>
                  <th>Peristiwa Risiko</th>
                  <th>Deskripsi Peristiwa Risiko</th>
                  <th>Nilai Dampak Inherent</th>
                  <th>Skala Dampak Inherent</th>
                  <th>Nilai Probabilitas Inherent</th>
                  <th>Skala Probabilitas Inherent</th>
                  <th>Nilai Risiko Inherent</th>
                  <th>Eksposur Risiko Inherent</th>
                  <th>Level Risiko Inherent</th>
                  <th>Nilai Dampak Residual</th>
                  <th>Skala Dampak Residual</th>
                  <th>Nilai Probabilitas Residual</th>
                  <th>Skala Probabilitas Residual</th>
                  <th>Nilai Risiko Residual</th>
                  <th>Eksposur Risiko Residual</th>
                  <th>Level Risiko Residual</th>
                </tr>
              </thead>
              <tbody>
                @foreach($risikos as $risiko)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <a href="{{ route('risk-register-unit.view', $risiko->id) }}">
                      {{ $risiko->peristiwa_risiko ?? '-' }}
                    </a>
                  </td>
                  <td>{{ $risiko->deskripsi_peristiwa_risiko ?? '-' }}</td>
                  <td>{{ $risiko->riskAnalysis?->nilai_dampak ? 'Rp ' . number_format($risiko->riskAnalysis->nilai_dampak, 0, ',', '.') : 'Rp 0' }}</td>
                  <td>
                    {{ $risiko->riskAnalysis?->skalaDampakObj?->tingkat 
                      ? '(' . $risiko->riskAnalysis?->skalaDampakObj?->tingkat . ') ' . $risiko->riskAnalysis?->skalaDampakObj?->deskripsi 
                      : '-' }}
                  </td>
                  <td>{{ $risiko->riskAnalysis?->nilai_probabilitas ?? '-' }}</td>
                  <td>
                    {{ $risiko->riskAnalysis?->skalaProbabilitas?->tingkat 
                        ? '(' . $risiko->riskAnalysis?->skalaProbabilitas?->tingkat . ') ' . $risiko->riskAnalysis?->skalaProbabilitas?->skala 
                        : '-' }}
                  </td>
                  <td>{{ $risiko->riskAnalysis?->skala_risiko ?? '-' }}</td>
                  <td>{{ $risiko->riskAnalysis?->eksposur_risiko ? 'Rp ' . number_format($risiko->riskAnalysis->eksposur_risiko, 0, ',', '.') : 'Rp 0' }}</td>
                  <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($risiko->riskAnalysis?->level_risiko)))}}">{{ $risiko->riskAnalysis?->level_risiko ?? '-' }}</td>
                  <td>{{ $risiko->riskAnalysis?->nilai_dampak_residual ? 'Rp ' . number_format($risiko->riskAnalysis->nilai_dampak_residual, 0, ',', '.') : 'Rp 0' }}</td>
                  <td>
                    {{ $risiko->riskAnalysis?->skalaDampakResidualQ4Obj?->tingkat 
                      ? '(' . $risiko->riskAnalysis?->skalaDampakResidualQ4Obj?->tingkat . ') ' . $risiko->riskAnalysis?->skalaDampakResidualQ4Obj?->deskripsi 
                      : '-' }}
                  </td>
                  <td>{{ $risiko->riskAnalysis?->nilai_probabilitas_residual ?? '-' }}</td>
                  <td>
                    {{ $risiko->riskAnalysis?->skalaProbabilitasResidualQ4?->tingkat 
                      ? '(' . $risiko->riskAnalysis?->skalaProbabilitasResidualQ4?->tingkat . ') ' . $risiko->riskAnalysis?->skalaProbabilitasResidualQ4?->skala 
                      : '-' }}
                  </td>
                  <td>{{ $risiko->riskAnalysis?->skala_risiko_residual ?? '-' }}</td>
                  <td>{{ $risiko->riskAnalysis?->eksposur_risiko_residual ? 'Rp ' . number_format($risiko->riskAnalysis->eksposur_risiko_residual, 0, ',', '.') : 'Rp 0' }}</td>
                  <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($risiko->riskAnalysis?->level_risiko_residual)))}}">{{ $risiko->riskAnalysis?->level_risiko_residual ?? '-' }}</td>
                </tr>
                @endforeach
                @if ($risikos->isEmpty())
                <tr>
                  <td colspan="17" class="text-center p-3">Tidak ada data</td>
                </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--============================ Top 10 High Risk ============================-->
  <div class="col-12 mb-3">
    <div class="card" id="top-risk-card">
      <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-danger-subtle rounded-3 p-2">
            <div class="lead__icon lead__icon_sm">
              <span class="svg-icon svg-icon-2x svg-icon-danger">
                @include('partials.icon-abs04')
              </span>
            </div>
          </div>
          <h3>Top High Risk Divisi</h3>
        </div>
        <hr class="mb-0 mt-xxl-5">
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th class="white-space-nowrap">Divisi</th>
                <th>Peristiwa Risiko</th>
                <th>Deskripsi Peristiwa Risiko</th>
                <th>Jenis Risiko</th>
                <th>Nilai Dampak</th>
                <th>Skala Dampak</th>
                <th>Nilai Risiko</th>
                <th>Level Risiko</th>
                <th>KRI</th>
                <th class="text-center white-space-nowrap">Status KRI</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!--============================ Loss Event Data ============================-->
  <div class="col-12 mb-3">
    <div class="card" id="led-card">
      <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-info-subtle rounded-3 p-2">
            <div class="lead__icon lead__icon_sm">
              <span class="svg-icon svg-icon-2x svg-icon-info">
                @include('partials.icon-abs05')
              </span>
            </div>
          </div>
          <h3>Top 10 Loss Event Data</h3>
        </div>
        <hr class="mb-2 mt-xxl-5">
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Divisi</th>
                <th>Tanggal Kejadian</th>
                <th>Nama Kejadian</th>
                <th>Identifikasi Kejadian</th>
                <th>Kategori Kejadian</th>
                <th>Nilai Kerugian</th>
              </tr>
              {{-- <tr>
                <th class="no-sort text-center py-2">Finansial (IDR)</th>
                <th class="no-sort text-center py-2">Non Finansial</th>
              </tr> --}}
            </thead>
            <tbody>
              {{-- <tr>
                <td>04/11/2024</td>
                <td>Lorem ipsum odor amet, consectetuer adipiscing elit. Natoque habitant habitant donec sodales
                  porttitor dictumst.</td>
                <td>Risiko Pendidikan</td>
                <td>5.000.000.000</td>
                <td>2.000.000.000</td>
                <td>Consectetur mauris praesent risus condimentum libero diam aenean.</td>
                <td>LKPG</td>
              </tr> --}}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card" id="efektivitas-perlakuan-card">
        <div class="card-header border-0 pb-0 d-flex flex-between-center">
            <h3 class="h4">Efektivitas Perlakuan Risiko</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="border rounded-3 p-3 mb-3 w-100">
                      @if(isset($dashboardData['efektivitas_perlakuan']))
                        @foreach ($dashboardData['efektivitas_perlakuan'] as $item)
                        <div class="mb-1 d-flex align-items-center gap-2">
                            <span class="d-inline-block" style="width:25px; height:25px; border-radius: 3px; background-color: {{$item['color']}}"></span>
                            <span>{{ $item['label'] }}</span>
                        </div>
                        @endforeach
                      @endif
                    </div>
                    <div class="ratio ratio-1x1 ratio-lg-4x3 ratio-xxl-16x9 ratio-xxxl-21x9">
                        <div id="efektivitas-perlakuan-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="detailRisikoModal" tabindex="-1" aria-labelledby="detailRisikoModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="detailRisikoModalLabel">Detail Peristiwa Risiko</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body scrollbar">
      </div>
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
#currentMap .current-q1, #currentMap .current-q2, #currentMap .current-q3, #currentMap .current-q4 {
    display: none;
}

#currentMap.show-q1 .current-q1 {
    display: block;
}

#currentMap.show-q2 .current-q2 {
    display: block;
}

#currentMap.show-q3 .current-q3 {
    display: block;
}

#currentMap.show-q4 .current-q4 {
    display: block;
}
</style>
@endpush

@section('scripts')
<script src="/vendors/chart-js/chart.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  const dashboardData = @json($dashboardData);
  const risks = @json($risikos);
  const formattedCurrentRiskMaps = @json($formattedCurrentRiskMaps);
  const riskMatrix = @json($riskMaps);

  const fillTopRisks = (data) => {
    const tableBody = $('#top-risk-card table tbody');
    tableBody.empty();
    
    if (!data || data.length === 0) {
        tableBody.append(`
            <tr>
                <td colspan="11" class="text-center">No data available</td>
            </tr>
        `);
        return;
    }

    data.forEach((risk, index) => {
        const rowSpan = risk.kris.length || 1;
        let krisHtml = '';

        if (risk.kris.length > 0) {
            krisHtml += `
                <td>${risk.kris[0].kri || '-'}</td>
                <td class="text-center">
                    <div class="status-container ${risk.kris[0].status_kri_color}">
                        <div class="status-red"></div>
                        <div class="status-yellow"></div>
                        <div class="status-green"></div>
                    </div>
                </td>
            `;
        } else {
            krisHtml += `
                <td>-</td>
                <td class="text-center">-</td>
            `;
        }

        let mainRow = `
            <tr>
                <td rowspan="${rowSpan}">${index + 1}</td>
                <td rowspan="${rowSpan}">${risk.divisi || '-'}</td>
                <td rowspan="${rowSpan}">${risk.peristiwa_risiko || '-'}</td>
                <td rowspan="${rowSpan}">${risk.deskripsi_peristiwa_risiko || '-'}</td>
                <td rowspan="${rowSpan}">${risk.jenis_risiko || '-'}</td>
                <td rowspan="${rowSpan}">${risk.nilai_dampak ? 'Rp ' + Intl.NumberFormat('id-ID').format(risk.nilai_dampak) : 'Rp 0'}</td>
                <td rowspan="${rowSpan}">${risk.skala_dampak || '-'}</td>
                <td rowspan="${rowSpan}">${risk.nilai_risiko || '-'}</td>
                <td rowspan="${rowSpan}" class="bg-${risk.level_risiko?.toLowerCase().replaceAll('to', '').replaceAll(' ', '-')}">${risk.level_risiko || '-'}</td>
                
                ${krisHtml}
            </tr>
        `;

        tableBody.append(mainRow);

        if (risk.kris.length > 1) {
            for (let i = 1; i < risk.kris.length; i++) {
                let kriRow = `
                    <tr>
                        <td>${risk.kris[i].kri || '-'}</td>
                        <td class="text-center">
                            <div class="status-container ${risk.kris[i].status_kri_color}">
                                <div class="status-red"></div>
                                <div class="status-yellow"></div>
                                <div class="status-green"></div>
                            </div>
                        </td>
                    </tr>
                `;
                tableBody.append(kriRow);
            }
        }
    });
  }

  const fillEfektivitasChart = (data) => {
    var chartDom = document.getElementById('efektivitas-perlakuan-chart');
    if (!chartDom) return;
    var myChart = echarts.init(chartDom);
    var option;

    option = {
        tooltip: {
            trigger: 'item',
            formatter: '{b}: {c} ({d}%)'
        },
        series: [
            {
            name: 'Efektivitas Perlakuan',
            type: 'pie',
            radius: '90%',
            center: ['50%', '50%'],
            data: data.map(function(item) {
                return {
                    value: item.value,
                    name: item.label,
                    itemStyle: {
                        color: item.color
                    }
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
                formatter: '{d}%',
                fontSize: '14',
                color: '#fff'
            },
            }
        ]
    };

    if (myChart) {
        myChart.setOption(option, true);
    }
  }

  const fillEfektivitasDetails = (efektifData, tidakEfektifData) => {
      const efektifBody = $('#efektif-details-body');
      const tidakEfektifBody = $('#tidak-efektif-details-body');

      efektifBody.empty();
      tidakEfektifBody.empty();

      if (efektifData.length > 0) {
          efektifData.forEach(item => {
              efektifBody.append(`<tr><td>${item.peristiwa_risiko}</td></tr>`);
          });
      } else {
          efektifBody.append('<tr><td>Tidak ada data</td></tr>');
      }

      if (tidakEfektifData.length > 0) {
          tidakEfektifData.forEach(item => {
              tidakEfektifBody.append(`<tr><td>${item.peristiwa_risiko}</td></tr>`);
          });
      } else {
          tidakEfektifBody.append('<tr><td>Tidak ada data</td></tr>');
      }
  }

  const refreshSummary = () => {
    // Capaian KPI
    // if (dashboardData.tck_c >= 0) {
    //   $('#kpi-card .changes-summary').html(`<span class="up-label">15%</span> Since last month`);
    // } else {
    //   $('#kpi-card .changes-summary').html(`<span class="down-label">15%</span> Since last month`);
    // }
    // $('#kpi-card .last-changes').html(dashboardData.tck_date);

    // Realisasi Perlakuan Risiko
    // if (dashboardData.rpr_c >= 0) {
    //   $('#rpr-card .changes-summary').html(`<span class="up-label">${dashboardData.rpr_c}%</span> Since last month`);
    // } else {
    //   $('#rpr-card .changes-summary').html(`<span class="down-label">${dashboardData.rpr_c}%</span> Since last month`);
    // }
    // $('#rpr-card .last-changes').html(dashboardData.rpr_date);

    // Jumlah Kejadian Kerusakan
    // if (dashboardData.jkk_c >= 0) {
    //   $('#jkk-card .changes-summary').html(`<span class="up-label">${dashboardData.jkk_c}</span> Since last month`);
    // } else {
    //   $('#jkk-card .changes-summary').html(`<span class="down-label">${dashboardData.jkk_c}</span> Since last month`);
    // }
    // $('#jkk-card .last-changes').html(dashboardData.jkk_date);
    // $('#jkk-card #jkk-counter').html(1);
    
    // Realisasi Perlakuan Kerusakan
    // const jkkCount = dashboardData.led.length;
    // $('#jkk-card #jkk-counter').html(jkkCount);
    // let latestLedDate = 'N/A';
    // let previousJkkCount = dashboardData.led;

    // if (dashboardData.led.length > 0) {
    //   const dates = dashboardData.led.map(item => new Date(item.tanggal_kejadian));
    //   const maxDate = new Date(Math.max(...dates));
    //   latestLedDate = maxDate.toLocaleDateString('en-GB', {
    //     day: '2-digit',
    //     month: 'short',
    //     year: 'numeric'
    //   });
    // }

    // $('#jkk-card .last-changes').html(latestLedDate);

    // if (typeof previousJkkCount === 'number' && previousJkkCount !== null) {
    //   if (jkkCount > previousJkkCount) {
    //     $('#jkk-card .changes-summary').html(
    //       `<span class="up-label">${jkkCount}</span>`);
    //   } else if (jkkCount < previousJkkCount) {
    //     $('#jkk-card .changes-summary').html(
    //       `<span class="down-label">${jkkCount}</span>`);
    //   } else {
    //     $('#jkk-card .changes-summary').html(
    //       `<span class="neutral-label">${jkkCount}</span>`);
    //   }
    // } else {
    //   // Jika tidak ada data pembanding
    //   $('#jkk-card .changes-summary').html(
    //     `<span class="up-label">${jkkCount}</span>`);
    // }

    // Capaian TKMRU
    // if (dashboardData.tkmru_c >= 0) {
    //   $('#tkmru-card .changes-summary').html(`<span class="up-label">${dashboardData.tkmru_c}</span> Since last month`);
    // } else {
    //   $('#tkmru-card .changes-summary').html(
    //     `<span class="down-label">${dashboardData.tkmru_c}</span> Since last month`);
    // }
    // $('#tkmru-card .last-changes').html(dashboardData.tkmru_date);
    // $('#tkmru-card #tkmru-counter').html(dashboardData.tkmru);
    // $('#tkmru-card #tkmru-notes').html(dashboardData.tkmru_notes);

    // TOP RISK
    // $('#top-risk-card .table tbody').html('');
    // if (!dashboardData.top_risk.length) {
    //   $('#top-risk-card .table tbody').append(`
    //           <tr>
    //               <td colspan="8" class="dt-empty">No data available</td>
    //           </tr>
    //       `);
    // } else {
    //   dashboardData.top_risk.forEach((risk) => {
    //     $('#top-risk-card .table tbody').append(`
    //             <tr>
    //                 <td>${risk.peristiwa}</td>
    //                 <td>${risk.deskripsi}</td>
    //                 <td>${risk.jenis_risiko}</td>
    //                 <td class="level_risiko text-center">
    //                     <div class="badge ${risk.warna_tingkat_risiko}">${risk.tingkat_risiko}</div>
    //                 </td>
    //                 <td>${risk.sasaran}</td>
    //                 <td>${risk.kri}</td>
    //                 <td class="text-center">
    //                     <div class="status-container ${risk.status_kri}">
    //                         <div class="status-green"></div>
    //                         <div class="status-yellow"></div>
    //                         <div class="status-red"></div>
    //                     </div>
    //                 </td>
    //                 <td>${risk.risk_owner}</td>
    //             </tr>
    //         `);
    //   });
    // }

    // Lost Event Data
    $('#led-card .table tbody').html('');
    if (dashboardData.led.length == 0) {
      $('#led-card .table tbody').append(`
              <tr>
                  <td colspan="7" class="dt-empty text-center">No data available</td>
              </tr>
          `);
    } else {
      dashboardData.led.forEach((led, index) => {
        $('#led-card .table tbody').append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>${led.nama_divisi}</td>
                    <td>${led.tanggal_kejadian}</td>
                    <td>${led.nama_kejadian}</td>
                    <td>${led.identifikasi_kejadian}</td>
                    <td>${led.kategori_kejadian}</td>
                    <td>${led.nilai_kerugian}</td>
                </tr>
            `);
      });
    }

    fillTopRisks(dashboardData.top_risks);
    fillEfektivitasChart(dashboardData.efektivitas_perlakuan);
    // fillEfektivitasDetails(dashboardData.risikos_efektif, dashboardData.risikos_tidak_efektif);
  }

  const initKpiChart = () => {
    // Initialize the echarts instance based on the prepared dom
    var myChart = echarts.init(document.getElementById('kpi-chart'));
    // Specify the configuration items and data for the chart
    var option = {
      series: [{
        type: 'gauge',
        startAngle: 90,
        endAngle: -270,
        radius: '90%',
        pointer: {
          show: false
        },
        progress: {
          show: true,
          overlap: false,
          roundCap: true,
          clip: false,
          itemStyle: {
            color: {
              type: 'linear',
              x: 0,
              y: 0,
              x2: 1,
              y2: 0,
              colorStops: [{
                offset: 0,
                color: '#ffc700'
              }, ]
            }
          }
        },
        axisLine: {
          lineStyle: {
            width: 17,
            color: [
              [1, '#eff2f5']
            ]
          }
        },
        splitLine: {
          show: false
        },
        axisTick: {
          show: false
        },
        axisLabel: {
          show: false
        },
        data: [{
          value: 89,
          detail: {
            offsetCenter: ['7%', '4%']
          }
        }],
        detail: {
          width: 50,
          height: 14,
          fontSize: 20,
          fontWeight: 700,
          fontFamily: 'Poppins',
          color: '#181c32',
          formatter: '89%',
          valueAnimation: true
        },
        animationDuration: 3000,
      }]
    };

    // Display the chart using the configuration items and data just specified.
    myChart.setOption(option);
  }

  const initRprChart = () => {
    // Initialize the echarts instance based on the prepared dom
    var myChart = echarts.init(document.getElementById('rpr-chart'));

    // Specify the configuration items and data for the chart
    var option = {
      series: [{
        type: 'gauge',
        startAngle: 90,
        endAngle: -270,
        radius: '90%',
        pointer: {
          show: false
        },
        progress: {
          show: true,
          overlap: false,
          roundCap: true,
          clip: false,
          itemStyle: {
            color: {
              type: 'linear',
              x: 0,
              y: 0,
              x2: 1,
              y2: 0,
              colorStops: [{
                offset: 0,
                color: '#4f55da'
              }, ]
            }
          }
        },
        axisLine: {
          lineStyle: {
            width: 17,
            color: [
              [1, '#eff2f5']
            ]
          }
        },
        splitLine: {
          show: false
        },
        axisTick: {
          show: false
        },
        axisLabel: {
          show: false
        },
        data: [{
          value: 62,
          detail: {
            offsetCenter: ['7%', '4%']
          }
        }],
        detail: {
          width: 50,
          height: 14,
          fontSize: 20,
          fontWeight: 700,
          fontFamily: 'Poppins',
          color: '#181c32',
          formatter: '62%',
          valueAnimation: true
        },
        animationDuration: 3000
      }]
    };

    // Display the chart using the configuration items and data just specified.
    myChart.setOption(option);
  }

  const initSelect2 = () => {
    $('.select2').select2({
      width: '100%',
      minimumResultsForSearch: Infinity,
    });
  }

  const initInputFilter = () => {
    $('#periode_selector, #unit_selector').on('change', function() {
      $('#filterForm').submit();
    });
  }

  function initializeMaps() {
    risks.forEach((risk, idx) => {
        const matrixI = risk.risk_analysis?.skala_dampak + '-' + risk.risk_analysis?.skala_probabilitas?.tingkat;
        const matrixR = risk.risk_analysis?.skala_dampak_residual + '-' + risk.risk_analysis?.skala_probabilitas_residual_q4?.tingkat;

        const cellI = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixI}"]`);
        const cellR = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixR}"]`);

        const code = (idx + 1).toString();
        
        if (cellI.length) {
            if (!cellI.data('kode-peristiwa-inherent')) {
                cellI.data('kode-peristiwa-inherent', []);
            }
            
            cellI.data('kode-peristiwa-inherent').push(code);
            cellI.data('has-inherent', true);
        }

        if (cellR.length) {
            if (!cellR.data('kode-peristiwa-residual')) {
                cellR.data('kode-peristiwa-residual', []);
            }
            
            cellR.data('kode-peristiwa-residual').push(code);
            cellR.data('has-residual', true);
        }

        const currentRiskMaps = formattedCurrentRiskMaps[risk.id];
        currentRiskMaps.forEach((currentRiskMap) => {
            const matrixC = currentRiskMap.skala_dampak + '-' + currentRiskMap.skala_probabilitas;
            const cellC = $(`#currentMap.table-risk-map .data-cell[data-matrix="${matrixC}"]`);

            if (cellC.length) {
                if (!cellC.data('kode-peristiwa-current-q' + currentRiskMap.quarter)) {
                    cellC.data('kode-peristiwa-current-q' + currentRiskMap.quarter, []);
                }
                
                cellC.data('kode-peristiwa-current-q' + currentRiskMap.quarter).push(code);
                cellC.data('has-current', true);
            }
        });

        const cells = $('#inherentMap.table-risk-map .data-cell');
        cells.each((index, cell) => {
            let html = '';
            let kodePeristiwaInherent = $(cell).data('kode-peristiwa-inherent');
            let kodePeristiwaResidual = $(cell).data('kode-peristiwa-residual');
            if (kodePeristiwaInherent && kodePeristiwaInherent.length > 0) {
                for (let i = 0; i < kodePeristiwaInherent.length; i++) {
                    html += `<span class="box-inherent">R${kodePeristiwaInherent[i]}</span>`;
                }
            }

            if (kodePeristiwaResidual && kodePeristiwaResidual.length > 0) {
                for (let i = 0; i < kodePeristiwaResidual.length; i++) {
                    html += `<span class="box-residual">R${kodePeristiwaResidual[i]}</span>`;
                }
            }

            $(cell).find('.kode-peristiwa').html(html);
        });

        const cellsC = $('#currentMap.table-risk-map .data-cell');
        cellsC.each((index, cell) => {
            let html = '';
            let kodePeristiwaCurrent = null;
            kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-q1');
            if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                    html += `<span class="box-current current-q1">R${kodePeristiwaCurrent[i]}</span>`;
                }
            }

            kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-q2');
            if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                    html += `<span class="box-current current-q2">R${kodePeristiwaCurrent[i]}</span>`;
                }
            }

            kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-q3');
            if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                    html += `<span class="box-current current-q3">R${kodePeristiwaCurrent[i]}</span>`;
                }
            }

            kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-q4');
            if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                    html += `<span class="box-current current-q4">R${kodePeristiwaCurrent[i]}</span>`;
                }
            }

            $(cell).find('.kode-peristiwa').html(html);
        });
    });
  }

  function showRisiko(kode) {
    const risiko = dashboardData.prir.find(item => item.kode === kode);
    console.log(risiko);
    $('#detailRisikoModal .modal-title').html(`${kode}: ${risiko.peristiwa}`);
    $('#detailRisikoModal .modal-body').html(`
      <div class="row gx-0 gy-2 gy-md-3">
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Kategori Risiko
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.full_data?.kategori_risiko?.title || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Jenis Risiko
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.full_data?.jenis_risiko?.title || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Peristiwa Risiko
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.peristiwa || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Deskripsi Peristiwa Risiko
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.full_data?.deskripsi_peristiwa_risiko || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Target Capaian Kinerja
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.full_data?.tck?.title || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Rencana Kegiatan
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.full_data?.rencana_kegiatan?.title || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Area Dampak
          <span>:</span>
        </div>
          <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
        ${risiko?.full_data?.area_dampak?.title || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Deskripsi Dampak
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.full_data?.deskripsi_dampak || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Skala Dampak Inherent
          <span>:</span>
          </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">${risiko?.full_data?.skala_dampak?.deskripsi || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Skala Probabilitas Inherent
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.full_data?.skala_probabilitas_id || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Nilai Risiko Inherent
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.full_data?.skala_risiko || '-'}
        </div>
        <div class="col-md-5 col-lg-3 d-flex justify-content-md-between fw-bold pe-md-2">
          Level Risiko
          <span>:</span>
        </div>
        <div class="col-md-7 col-lg-9 border-bottom pb-2 pb-md-3">
          ${risiko?.full_data?.level_risiko || '-'}
        </div>
      </div>
    `);
    $('#detailRisikoModal').modal('show');
  }

  $('#quarterSelect,#tahunSelect').on('change', function() {
      const quarter = $('#quarterSelect').val();
      $('#currentMap').prop('class', 'table-risk-map');
      $('#currentMap').addClass('show-q' + quarter);
  }).change();

  // initKpiChart();
  // initRprChart();
  refreshSummary();
  initInputFilter();
  initializeMaps();
});
</script>

@endsection
