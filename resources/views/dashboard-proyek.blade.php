@extends('layouts.default')

@section('dashboard')
<!--========================= Dashboard Header =========================-->
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="../assets/img/dashboard-header3.webp" alt="dashboard">
      <div class="card-header border-0">
        <h1 class="mb-auto mt-3 mt-md-6">Risk Dashboard Proyek</h1>
        <h6>Statistik per tanggal {{ now()->format('d M Y') }}</h6>
      </div>
    </div>
  </div>
</div>

<!--========================= Input Filter =========================-->
<div class="row input-selector-rounded g-3 mb-3">
  <div class="col-12">
    <form id="filter-form" action="{{ url()->current() }}" method="GET">
      <div class="row g-3">
        <div class="col-md-4">
          <select name="unit_id" id="unit_selector" class="form-select select2  js-select-hide-search" onchange="this.form.submit()">
            <option value="" selected>Semua Divisi</option>
            @foreach ($units as $unit)
              <option value="{{ $unit->id }}" {{ $unit->id == $selectedUnitId ? 'selected' : '' }}>
                {{ $unit->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <select name="project_id" id="project_selector" class="form-select select2" onchange="this.form.submit()">
            <option value="" selected>Semua Proyek</option>
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
  
<div class="card mb-3">
  <div class="card-header border-0 pb-0 d-flex flex-between-center">
    <h3 class="h4">Persentase Risk Profil</h3>
  </div>
  <div class="card-body">
    <div class="border rounded-3 p-3 mb-3">
      @php $counter = 0; @endphp
      @foreach ($dashboardData['prp'] as $riskProfil)
      <div class="mb-1 d-flex align-items-center gap-2">
        <span class="d-inline-block" style="width:25px; height:25px; border-radius: 3px; background-color: {{$riskProfil['color']}}"></span>
        <span>{{ $riskProfil['label'] }}</span>
      </div>
      @php $counter++; @endphp
      @endforeach
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="text-center fw-bold">Data % Per Jumlah Kejadian</div>
        <div class="ratio ratio-1x1 ratio-lg-4x3 ratio-xxl-16x9 ratio-xxxl-21x9">
          <div id="persentase-kejadian-chart"></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="text-center fw-bold">Data % Per Nilai Dampak</div>
        <div class="ratio ratio-1x1 ratio-lg-4x3 ratio-xxl-16x9 ratio-xxxl-21x9">
          <div id="nilai-dampak-chart"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!--========================= Dashboard Content Start =========================-->
<div class="row g-3 dashboard-content">
  <!-- Peta Risiko Inheren dan Residual -->
  <div class="col-lg-6">
    <div class="card" id="prir-card">
      <div class="card-header border-0 pb-0">
        <div class="row">
          <div class="col align-items-center d-flex">
            <h3 class="h4">Peta Risiko Inheren dan Residual</h3>
          </div>
          <div class="col">
            <select class="form-select" style="visibility: hidden"></select>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-risk-map">
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
              <i class='bx bxs-circle current'></i>
              Current
            </div>
          </div>
          <!-- end::Legend -->

        </div>
        <div class="d-block">
          <div class="alert alert-info">Project</div>
          <div class="table-responsive scrollbar">
            <table class="table table-strategi">
              <thead>
                <tr>
                  <th class="white-space-nowrap" style="width:1%">Kode</th>
                  <th class="text-start">Nama Proyek</th>
                  <th class="white-space-nowrap" style="width:1%">Level Risiko</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--============================ Peta Risiko Terkini (Current) ============================-->
  <div class="col-lg-6">
    <div class="card" id="prsi-card">
      <div class="card-header border-0 pb-0">
        <div class="row">
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
          <div class="col">
              <select class="form-select" id="tahunSelect">
                  @foreach ($tahunMonitorings as $tahun)
                      <option value="{{ $tahun }}">{{ $tahun }}</option>
                  @endforeach
              </select>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-risk-map">
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
                    <div class="divider-text">IMPACT</div>
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
              <i class='bx bxs-circle current'></i>
              Current
            </div>
          </div>
          <!-- end::Legend -->

        </div>
        <div class="d-block">
          <div class="alert alert-info">Project</div>
          <div class="table-responsive scrollbar">
            <table class="table table-strategi">
              <thead>
                <tr>
                  <th class="white-space-nowrap" style="width:1%">Kode</th>
                  <th class="text-start">Nama Proyek</th>
                  <th class="white-space-nowrap" style="width:1%">Level Risiko</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--============================ Top 5 Risk ============================-->
  <div class="col-12">
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
          <h3>Top High Risk Project</h3>
        </div>
        <hr class="mb-0 mt-xxl-5">
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Proyek</th>
                <th>Peristiwa Risiko</th>
                <th>Deskripsi Peristiwa Risiko</th>
                <th>Jenis Risiko</th>
                <th>Nilai Dampak</th>
                <th>Skala Dampak</th>
                <th>Nilai Risiko</th>
                <th>Level Risiko</th>
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

  <!--============================ Top 10 Loss Event Data ============================-->
  <div class="col-12">
    <div class="card">
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
        <hr class="mb-0 mt-xxl-5">
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Proyek</th>
                <th>Tanggal Kejadian</th>
                <th>Nama Kejadian</th>
                <th>Deskripsi Kejadian</th>
                <th>Kategori Kejadian</th>
                <th>Nilai Kerugian Finansial</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="top-led-body">
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!--============================ Efektivitas Perlakuan Risiko ============================-->
  <div class="col-md-6">
    <div class="card" id="efektivitas-perlakuan-card">
      <div class="card-header border-0 pb-0 d-flex flex-between-center">
        <h3 class="h4">Efektivitas Perlakuan Risiko</h3>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-12 d-flex align-items-center">
            <div class="border rounded-3 p-3 mb-3 w-100">
              @foreach ($dashboardData['efektivitas_perlakuan'] as $item)
                <div class="mb-1 d-flex align-items-center gap-2">
                  <span class="d-inline-block" style="width:25px; height:25px; border-radius: 3px; background-color: {{$item['color']}}"></span>
                  <span>{{ $item['label'] }}</span>
                </div>
              @endforeach
            </div>
          </div>
          <div class="col-12">
            <div class="ratio ratio-1x1 ratio-lg-4x3 ratio-xxl-16x9 ratio-xxxl-21x9">
              <div id="efektivitas-perlakuan-chart"></div>
            </div>
          </div>
        </div>
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
    right: -5px;
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

.quarter-data {
    display: none;
}

.cell-level-risiko {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  align-items: center;
  justify-content: center;
}
</style>

@foreach ($tahunMonitorings as $tahunMonitoring)
<style>
.show-{{ $tahunMonitoring }}-q1 .quarter-1-{{ $tahunMonitoring }} {
    display: flex;
}

.show-{{ $tahunMonitoring }}-q2 .quarter-2-{{ $tahunMonitoring }} {
    display: flex;
}

.show-{{ $tahunMonitoring }}-q3 .quarter-3-{{ $tahunMonitoring }} {
    display: flex;
}

.show-{{ $tahunMonitoring }}-q4 .quarter-4-{{ $tahunMonitoring }} {
    display: flex;
}
</style>
@endforeach

@endpush

@section('scripts')
<script src="/vendors/chart-js/chart.min.js"></script>
<script type="text/javascript">
const refreshSummary = (data) => {
  fillPersentaseKejadian(data['prp']);
  fillNilaiDampak(data['prp']);
  fillPrir(data['prir']);
  fillPrsi(data['prsi']);
  fillTopRisk(data['top_risk']);
  fillTopLed(data['top_led']);
  fillEfektivitasChart(data['efektivitas_perlakuan']);
}

function fillPersentaseKejadian(data) {
  var chartDom = document.getElementById('persentase-kejadian-chart');
  var myChart = echarts.init(chartDom);
  var option;

  option = {
    tooltip: {
      trigger: 'item'
    },
    series: [
      {
        name: 'Jumlah Kejadian',
        type: 'pie',
        radius: '90%',
        data: data.map(function(item) {
          return {
            value: item.jumlah_kejadian,
            name: item.label,
            itemStyle: {
              color: item.color
            }
          }
        }),
        label: {
          position: 'inside',
          formatter: '{d}%'
        },
      }
    ]
  };

  option && myChart.setOption(option);
}

function fillNilaiDampak(data) {
  var chartDom = document.getElementById('nilai-dampak-chart');
  var myChart = echarts.init(chartDom);
  var option;

  option = {
    tooltip: {
      trigger: 'item'
    },
    series: [
      {
        name: 'Nilai Dampak',
        type: 'pie',
        radius: '90%',
        data: data.map(function(item) {
          return {
            value: item.nilai_dampak,
            name: item.label,
            itemStyle: {
              color: item.color
            }
          }
        }),
        label: {
          position: 'inside',
          formatter: '{d}%'
        },
      }
    ]
  };

  option && myChart.setOption(option);
}

function fillPrir(data) {
  if (data.length === 0) {
    $('#prir-card table.table-strategi tbody').html('<tr><td colspan="3" class="text-center">Tidak ada data</td></tr>');
    return;
  }

  var table = $('#prir-card table.table-strategi tbody');
  table.empty();
  data.forEach(function(item) {
    var row = $('<tr></tr>');
    row.append('<td>' + item.kode + '</td>');
    row.append('<td class="text-start">' + item.nama_proyek + '</td>');
    row.append(`<td class="bg-${item.level_risiko.toLowerCase().replaceAll('to ', '').replaceAll(' ', '-')}">` + (item.skala_risiko || '-') + '</td>');
    table.append(row);

    const cellI = $(`#prir-card .table-risk-map .data-cell[data-posisi-risiko="${item.skala_risiko}"]`);
    const cellR = $(`#prir-card .table-risk-map .data-cell[data-posisi-risiko="${item.skala_risiko_residual}"]`);

    if (cellI.length) {
      if (!cellI.data('kode-peristiwa-inherent')) {
        cellI.data('kode-peristiwa-inherent', []);
      }
      
      cellI.data('kode-peristiwa-inherent').push(item.kode);
      cellI.data('has-inherent', true);
    }

    if (cellR.length) {
      if (!cellR.data('kode-peristiwa-residual')) {
        cellR.data('kode-peristiwa-residual', []);
      }
      
      cellR.data('kode-peristiwa-residual').push(item.kode);
      cellR.data('has-residual', true);
    }
  });

  const cells = $('#prir-card .table-risk-map .data-cell');
  cells.each((index, cell) => {
    let html = '';
    let kodePeristiwaInherent = $(cell).data('kode-peristiwa-inherent');
    let kodePeristiwaResidual = $(cell).data('kode-peristiwa-residual');
    if (kodePeristiwaInherent && kodePeristiwaInherent.length > 0) {
        for (let i = 0; i < kodePeristiwaInherent.length; i++) {
            html += `<span class="box-inherent">${kodePeristiwaInherent[i]}</span>`;
        }
    }

    if (kodePeristiwaResidual && kodePeristiwaResidual.length > 0) {
        for (let i = 0; i < kodePeristiwaResidual.length; i++) {
            html += `<span class="box-residual">${kodePeristiwaResidual[i]}</span>`;
        }
    }

    $(cell).find('.kode-peristiwa').html(html);
  });
}

function fillPrsi(data) {
  if (data.length === 0) {
    $('#prsi-card table.table-strategi tbody').html('<tr><td colspan="3" class="text-center">Tidak ada data</td></tr>');
    return;
  }

  var table = $('#prsi-card table.table-strategi tbody');
  table.empty();
  data.forEach(function(item) {
    const selectedQuarter = $('#quarterSelect').val() || 1;
    var row = $('<tr></tr>');
    row.append('<td>' + item.kode + '</td>');
    row.append('<td class="text-start">' + item.nama_proyek + '</td>');
    row.append(`<td style="position: relative">
      @foreach ($tahunMonitorings as $tahunMonitoring)
      <span class="cell-level-risiko quarter-data quarter-1-{{ $tahunMonitoring }} bg-${item.monitorings.level_risiko_{{ $tahunMonitoring }}_q1?.toLowerCase().replaceAll('to ', '').replaceAll(' ', '-')}">${item.monitorings.skala_risiko_{{ $tahunMonitoring }}_q1 || '-'}</span>
      <span class="cell-level-risiko quarter-data quarter-2-{{ $tahunMonitoring }} bg-${item.monitorings.level_risiko_{{ $tahunMonitoring }}_q2?.toLowerCase().replaceAll('to ', '').replaceAll(' ', '-')}">${item.monitorings.skala_risiko_{{ $tahunMonitoring }}_q2 || '-'}</span>
      <span class="cell-level-risiko quarter-data quarter-3-{{ $tahunMonitoring }} bg-${item.monitorings.level_risiko_{{ $tahunMonitoring }}_q3?.toLowerCase().replaceAll('to ', '').replaceAll(' ', '-')}">${item.monitorings.skala_risiko_{{ $tahunMonitoring }}_q3 || '-'}</span>
      <span class="cell-level-risiko quarter-data quarter-4-{{ $tahunMonitoring }} bg-${item.monitorings.level_risiko_{{ $tahunMonitoring }}_q4?.toLowerCase().replaceAll('to ', '').replaceAll(' ', '-')}">${item.monitorings.skala_risiko_{{ $tahunMonitoring }}_q4 || '-'}</span>
      @endforeach
      </td>`);
    // row.append(`<td class="bg-${item.level_risiko?.toLowerCase().replaceAll('to ', '').replaceAll(' ', '-')}">` + (item.skala_risiko || '-') + '</td>');
    table.append(row);

    @foreach ($tahunMonitorings as $tahunMonitoring)
    for (let i = 1; i <= 4; i++) {
      const cell = $(`#prsi-card .table-risk-map .data-cell[data-posisi-risiko="${item['monitorings'][`skala_risiko_{{ $tahunMonitoring }}_q${i}`]}"]`);
      if (cell.length) {
        if (!cell.data('kode-peristiwa-{{ $tahunMonitoring }}-q' + i)) {
          cell.data('kode-peristiwa-{{ $tahunMonitoring }}-q' + i, []);
        }
        
        cell.data('kode-peristiwa-{{ $tahunMonitoring }}-q' + i).push(item.kode);
      }
    }
    @endforeach
  });

  const cells = $('#prsi-card .table-risk-map .data-cell');
  cells.each((index, cell) => {
    let html = '';
    let kodePeristiwaQ1 = null;
    let kodePeristiwaQ2 = null;
    let kodePeristiwaQ3 = null;
    let kodePeristiwaQ4 = null;

    @foreach ($tahunMonitorings as $tahunMonitoring)
    kodePeristiwaQ1 = $(cell).data('kode-peristiwa-{{ $tahunMonitoring }}-q1');
    kodePeristiwaQ2 = $(cell).data('kode-peristiwa-{{ $tahunMonitoring }}-q2');
    kodePeristiwaQ3 = $(cell).data('kode-peristiwa-{{ $tahunMonitoring }}-q3');
    kodePeristiwaQ4 = $(cell).data('kode-peristiwa-{{ $tahunMonitoring }}-q4');

    if (kodePeristiwaQ1 && kodePeristiwaQ1.length > 0) {
      for (let i = 0; i < kodePeristiwaQ1.length; i++) {
        html += `<span class="box-current quarter-data quarter-1-{{ $tahunMonitoring }}">${kodePeristiwaQ1[i]}</span>`;
      }
    }

    if (kodePeristiwaQ2 && kodePeristiwaQ2.length > 0) {
      for (let i = 0; i < kodePeristiwaQ2.length; i++) {
        html += `<span class="box-current quarter-data quarter-2-{{ $tahunMonitoring }}">${kodePeristiwaQ2[i]}</span>`;
      }
    }

    if (kodePeristiwaQ3 && kodePeristiwaQ3.length > 0) {
      for (let i = 0; i < kodePeristiwaQ3.length; i++) {
        html += `<span class="box-current quarter-data quarter-3-{{ $tahunMonitoring }}">${kodePeristiwaQ3[i]}</span>`;
      }
    }

    if (kodePeristiwaQ4 && kodePeristiwaQ4.length > 0) {
      for (let i = 0; i < kodePeristiwaQ4.length; i++) {
        html += `<span class="box-current quarter-data quarter-4-{{ $tahunMonitoring }}">${kodePeristiwaQ4[i]}</span>`;
      }
    }
    @endforeach

    $(cell).find('.kode-peristiwa').html(html);
  });
}

function fillTopRisk(data) {
  if (data.length === 0) {
    $('#top-risk-card table tbody').html('<tr><td colspan="10" class="text-center">Tidak ada data</td></tr>');
    return;
  }

  var table = $('#top-risk-card table tbody');
  table.empty();
  data.forEach(function(item, index) {
    const riskDetailUrl = `{{ url('projects') }}/${item.project_id}/risks/${item.id}/view`;

    var row = $('<tr></tr>');
    row.append('<td>' + (index + 1) + '</td>');
    row.append('<td>' + item.nama_proyek + '</td>');
    row.append('<td>' + item.peristiwa_risiko + '</td>');
    row.append('<td>' + item.deskripsi_peristiwa_risiko + '</td>');
    row.append('<td>' + (item.jenis_risiko || '-') + '</td>');
    row.append('<td>' + (item.nilai_dampak ? 'Rp ' + Intl.NumberFormat('id-ID').format(item.nilai_dampak) : 'Rp 0') + '</td>');
    row.append('<td>' + (item.skala_dampak || '-') + '</td>');
    row.append('<td>' + (item.nilai_risiko || '-') + '</td>');
    row.append(`<td class="bg-${item.level_risiko?.toLowerCase().replaceAll('to ', '').replaceAll(' ', '-')}">` + (item.level_risiko || '-') + '</td>');
    row.append(`
      <td class="text-center">
        <a href="${riskDetailUrl}" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="tooltip" title="Lihat Detail">
          <i class='bx bx-show'></i>
        </a>
      </td>
    `);

    table.append(row);
  });
}

function fillTopLed(data) {
    const tableBody = $('#top-led-body');
    tableBody.empty(); // Kosongkan tabel sebelumnya

    if (data.length === 0) {
        tableBody.append(`
            <tr>
                <td colspan="8" class="dt-empty text-center">No data available</td>
            </tr>
        `);
        return;
    }

    data.forEach((item, index) => {
        const ledDetailUrl = `{{ url('project-led') }}/${item.id}`;

        const formattedKerugian = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(item.nilai_kerugian_finansial);

        tableBody.append(`
            <tr>
                <td>${index + 1}</td>
                <td>${item.nama_proyek || '-'}</td>
                <td>${item.tanggal_kejadian || '-'}</td>
                <td>${item.nama_kejadian || '-'}</td>
                <td>${item.deskripsi_kejadian || '-'}</td>
                <td>${item.kategori_kejadian || '-'}</td>
                <td>${formattedKerugian || 'Rp 0'}</td>
                <td class="text-center">
                  <a href="${ledDetailUrl}" class="btn btn-sm btn-light-primary btn-icon" data-bs-toggle="tooltip" title="Lihat Detail">
                    <i class='bx bx-show'></i>
                  </a>
                </td>
            </tr>
        `);
    });
}

function fillEfektivitasChart(data) {
  var chartDom = document.getElementById('efektivitas-perlakuan-chart');
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
            fontSize: '16',
            fontWeight: 'normal',
            color: '#FFF'
        },
      }
    ]
  };

  if (myChart) {
      myChart.setOption(option, true);
  }
}

$(document).ready(function() {
  refreshSummary(@json($dashboardData));

  $('#quarterSelect,#tahunSelect').on('change', function() {
    const quarter = $('#quarterSelect').val();
    const tahun = $('#tahunSelect').val();
    $('#prsi-card').prop('class', 'card');
    $('#prsi-card').addClass('show-' + tahun + '-q' + quarter);
  }).change();
});
</script>
@endsection