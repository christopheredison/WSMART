@extends('layouts.default')

@section('dashboard')
<!--========================= Dashboard Header =========================-->
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="../assets/img/dashboard-header4.webp" alt="dashboard">
      <div class="card-header border-0">
        <h1 class="mb-auto mt-3 mt-md-6">Risk Dashboard KRI Unit</h1>
        <h6>Statistik per tanggal {{ now()->format('d M Y') }}</h6>
      </div>
    </div>
  </div>
</div>

<!--========================= Input Filter =========================-->
<div class="row input-selector-rounded g-3 mb-3">
  <div class="col-12">
    <form action="{{ url()->current() }}" method="GET">
      <div class="row g-3 g-xxl-2 input-filter-container">
        <div class="col-auto">
          <select name="periode_id" id="periode_selector" class="form-select select2 js-select-hide-search" onchange="this.form.submit()">
            <option value="" {{ !$selectedPeriode ? 'selected' : '' }} disabled>Semua Periode</option>
            @foreach($periodes as $periode)
              <option value="{{ $periode->id }}" {{ $selectedPeriode && $selectedPeriode->id == $periode->id ? 'selected' : '' }}>
                {{ $periode->tahun }}
              </option>
            @endforeach
          </select>
        </div>
        @if($isAllUnit)
        <div class="col-6 col-md-3 col-lg-3">
          <select name="unit_id" id="unit_selector" class="form-select select2 js-select-hide-search" onchange="this.form.submit()">
            <option value="" {{ !$selectedUnitId ? 'selected' : '' }}>Semua Unit</option>
            @foreach($units as $unit)
              <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>
                {{ $unit->name }}
              </option>
            @endforeach
          </select>
        </div>
        @endif
        <div class="col-auto">
          <select name="quarter" id="quarter_selector" class="form-select select2 js-select-hide-search" onchange="this.form.submit()">
            <option value="" disabled>Quarter</option>
            <option value="1" {{ $selectedQuarter == 1 ? 'selected' : '' }}>Quarter 1</option>
            <option value="2" {{ $selectedQuarter == 2 ? 'selected' : '' }}>Quarter 2</option>
            <option value="3" {{ $selectedQuarter == 3 ? 'selected' : '' }}>Quarter 3</option>
            <option value="4" {{ $selectedQuarter == 4 ? 'selected' : '' }}>Quarter 4</option>
          </select>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 dashboard-content">

  <!--========================= Dashboard Tren Kategori Risiko Start =========================-->
  {{--
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3>Tren KRI</h3>
      </div>
      <div class="card-body">
        <div class="ratio ratio-1x1 ratio-lg-21x9">
          <div id="kri_chart"></div>
        </div>
      </div>
    </div>
  </div>
  --}}
  <!--========================= Dashboard Tren Kategori Risiko End =========================-->

  <div class="col-12">
    <div class="card card-sm">
      <div class="card-header pb-0 border-0">
        <h3>Daftar Risiko</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th>KRI</th>
                <th>Risiko</th>
                <th>Unit</th>
                <th class="text-center white-space-nowrap">Batas Aman</th>
                <th class="text-center white-space-nowrap">Batas Siaga</th>
                <th class="text-center white-space-nowrap">Batas Bahaya</th>
                <th class="text-center white-space-nowrap">Kondisi Saat Ini</th>
                <th>T2 & T3 KBUMN</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($sortedKriData as $kri)
              <tr>
                <td>{{ $kri['kri'] ?? '-' }}</td>
                <td>{{ $kri['risiko'] }}</td>
                <td>{{ $kri['pemilik_risiko'] }}</td>
                <td class="text-center">{{ $kri['batas_aman'] ?? '-' }}</td>
                <td class="text-center">{{ $kri['batas_waspada'] ?? '-' }}</td>
                <td class="text-center">{{ $kri['batas_bahaya'] ?? '-' }}</td>
                <td class="text-center fw-bold ff-heading">{{ $kri['kondisi_saat_ini'] }}</td>
                <td>{{ $kri['t2_t3_kbumn'] }}</td>
                <td class="text-center">
                  @php
                    $statusClass = '';
                    $statusNumeric = $kri['status'] ?? 0;
                    if ($statusNumeric == 3) {
                        $statusClass = 'red';
                    } elseif ($statusNumeric == 2) {
                        $statusClass = 'yellow';
                    } elseif ($statusNumeric == 1) {
                        $statusClass = 'green';
                    }
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
                <td colspan="9" class="text-center">Tidak ada data KRI</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- KRI per Jenis Risiko -->
  <div class="col-md-6">
    <div class="card card-sm">
      <div class="card-header pb-xxl-0 border-0 gap-3">
        <div class="row w-100 g-3">
          <div class="col-md-8 col-lg-12 col-xl-7 d-flex align-items-center">
            <h3 class="h4">KRI per Jenis Risiko</h3>
          </div>
          <div class="col-md-4 col-lg-12 col-xl-5">
            <select name="jenis_risiko_filter" id="jenis_risiko_filter" class="form-select select2 js-select-hide-search">
              <option value="">Semua Jenis Risiko</option>
              @foreach($jenisRisikoList as $jenisRisiko)
                <option value="{{ $jenisRisiko->id }}">{{ $jenisRisiko->title }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="card-body px-md-0 py-lg-6 py-xxl-8">
        <div class="ratio ratio-1x1 ratio-md-4x3">
          <div class="d-flex justify-content-center">
            <canvas class="" id="kri_jenis_risiko"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- KRI per Unit -->
  <div class="col-md-6">
    <div class="card card-sm">
      <div class="card-header pb-xxl-0 border-0 gap-3">
        <div class="row w-100 g-3">
          <div class="col-md-8 col-lg-12 col-xl-7 d-flex align-items-center">
            <h3 class="h4">KRI per Unit</h3>
          </div>
          <div class="col-md-4 col-lg-12 col-xl-5">
            <select name="unit_filter" id="unit_filter" class="form-select select2 js-select-hide-search">
              <option value="">Semua Unit</option>
              @foreach($units as $unit)
                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="card-body px-md-0 py-lg-6 py-xxl-8">
        <div class="ratio ratio-1x1 ratio-md-4x3">
          <div class="d-flex justify-content-center">
            <canvas class="" id="kri_unit"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Modal -->
<div class="modal fade" id="trenRisikoModal" tabindex="-1" aria-labelledby="trenRisikoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="trenRisikoModalLabel">Tren Risiko</h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <canvas class="w-100 h-auto" id="trend_chart"></canvas>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="/vendors/chart-js/chart.min.js"></script>
<script src="/vendors/echarts/echarts.min.js"></script>
<script type="text/javascript">
/*
//=================== KRI Chart (Hidden) ===================
var dom = document.getElementById('kri_chart');
var kriChart = echarts.init(dom, null, {
  renderer: 'canvas',
  useDirtyRect: false
});
var app = {};

var option;

option = {
  tooltip: {
    trigger: 'axis'
  },
  legend: {
    data: ['Risiko Kredit', 'Risiko Pasar', 'Risiko Likuiditas', 'Risiko Operasional', 'Risiko Hukum',
      'Risiko Reputasi', 'Risiko Stratejik', 'Risiko Kepatuhan'
    ],
    textStyle: {
      fontSize: 10
    }
  },
  grid: {
    left: '4%',
    right: '1%',
    bottom: '4%',
    containLabel: true
  },
  toolbox: {
    feature: {
      saveAsImage: {}
    }
  },
  xAxis: [{
    type: 'category',
    boundaryGap: false,
    position: 'bottom',
    name: 'Bulan',
    nameLocation: 'center',
    data: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
    nameGap: 25,
    nameTextStyle: {
      fontFamily: "Poppins",
      fontSize: 11,
      fontWeight: "bold"
    }
  }],
  yAxis: [{
    type: 'value',
    name: 'Skor Risiko',
    offset: 20,
    nameLocation: "middle",
    nameGap: 30,
    nameTextStyle: {
      fontFamily: "Poppins",
      fontSize: 11,
      fontWeight: "bold"
    }
  }],
  series: [{
      name: 'Risiko Kredit',
      type: 'line',
      data: [12, 13, 10, 13, 9, 23, 21, 32, 41, 48, 39, 88]
    },
    {
      name: 'Risiko Pasar',
      type: 'line',
      data: [33, 42, 46, 76, 45, 65, 46, 52, 76, 92, 98, 68]
    },
    {
      name: 'Risiko Likuiditas',
      type: 'line',
      data: [25, 23, 44, 52, 96, 74, 85, 54, 32, 22, 48, 59]
    },
    {
      name: 'Risiko Operasional',
      type: 'line',
      data: [42, 36, 35, 45, 74, 55, 52, 69, 42, 38, 25, 36]
    },
    {
      name: 'Risiko Hukum',
      type: 'line',
      data: [16, 24, 46, 25, 43, 52, 28, 24, 43, 58, 78, 62]
    },
    {
      name: 'Risiko Reputasi',
      type: 'line',
      data: [29, 22, 6, 16, 26, 49, 62, 72, 36, 29, 46, 12]
    },
    {
      name: 'Risiko Stratejik',
      type: 'line',
      data: [36, 56, 19, 22, 37, 43, 26, 39, 46, 54, 48, 22]
    },
    {
      name: 'Risiko Kepatuhan',
      type: 'line',
      data: [15, 46, 49, 59, 65, 72, 47, 36, 49, 58, 42, 85]
    }
  ]
};


if (option && typeof option === 'object') {
  kriChart.setOption(option);
}

window.addEventListener('resize', kriChart.resize);
*/
//=================== Trend Chart ===================
const ctx = document.getElementById('trend_chart');
if (ctx) {
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
      datasets: [{
        label: 'Tren Risiko',
        data: [10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65],
        borderWidth: 2
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      },
      plugins: {
        legend: {
          display: false
        },

        tooltip: {
          enabled: false
        },
      },
    }
  });
}

//=================== KRI Chart ===================
const jenisRisikoData = @json($jenisRisikoData);
const unitData = @json($unitData);

let jenisRisikoChartInstance;
let unitChartInstance;

const jenisRisikoChartCanvas = document.getElementById('kri_jenis_risiko');
const unitChartCanvas = document.getElementById('kri_unit');

//=================== KRI per Jenis Risiko Chart ===================
function updateJenisRisikoChart(selectedJenisRisikoId = null) {
    if (!jenisRisikoChartCanvas) return;

    let filteredData = jenisRisikoData;
    if (selectedJenisRisikoId) {
        filteredData = jenisRisikoData.filter(item => item.jenis_risiko_id == selectedJenisRisikoId);
    }

    let totalAmanJenis = 0, totalSiagaJenis = 0, totalBahayaJenis = 0;
    filteredData.forEach(function(item) {
        totalAmanJenis += item.aman;
        totalSiagaJenis += item.waspada;
        totalBahayaJenis += item.bahaya;
    });

    if (jenisRisikoChartInstance) {
        jenisRisikoChartInstance.destroy();
    }

    jenisRisikoChartInstance = new Chart(jenisRisikoChartCanvas, {
        type: 'doughnut',
        data: {
            labels: ['Aman', 'Siaga', 'Bahaya'],
            datasets: [{
                data: [totalAmanJenis, totalSiagaJenis, totalBahayaJenis],
                backgroundColor: ['rgb(25, 163, 0)', 'rgb(253, 220, 34)', 'rgb(240, 100, 69)'],
                hoverOffset: 10
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { padding: 20 } }
            },
        }
    });
}

//=================== KRI per Unit Chart ===================
function updateUnitChart(selectedUnitId = null) {
    if (!unitChartCanvas) return;

    let filteredData = unitData;
    if (selectedUnitId) {
        filteredData = unitData.filter(item => item.unit_id == selectedUnitId);
    }

    let totalAmanUnit = 0, totalSiagaUnit = 0, totalBahayaUnit = 0;
    filteredData.forEach(function(item) {
        totalAmanUnit += item.aman;
        totalSiagaUnit += item.waspada;
        totalBahayaUnit += item.bahaya;
    });

    if (unitChartInstance) {
        unitChartInstance.destroy();
    }

    unitChartInstance = new Chart(unitChartCanvas, {
        type: 'doughnut',
        data: {
            labels: ['Aman', 'Siaga', 'Bahaya'],
            datasets: [{
                data: [totalAmanUnit, totalSiagaUnit, totalBahayaUnit],
                backgroundColor: ['rgb(25, 163, 0)', 'rgb(253, 220, 34)', 'rgb(240, 100, 69)'],
                hoverOffset: 10
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { padding: 20 } }
            },
        }
    });
}

$(document).ready(function() {
    // Inisialisasi Chart Doughnut saat halaman dimuat
    updateJenisRisikoChart();
    updateUnitChart();

    // Event listener untuk filter Jenis Risiko menggunakan jQuery
    $('#jenis_risiko_filter').on('change', function() {
        const selectedId = $(this).val();
        updateJenisRisikoChart(selectedId);
    });

    // Event listener untuk filter Unit menggunakan jQuery
    $('#unit_filter').on('change', function() {
        const selectedId = $(this).val();
        updateUnitChart(selectedId);
    });

    // Kode filter ro_select Anda yang sudah ada
    $("#ro_select").on("change", function() {
        var value = this.value;
        $('[data-show]').hide().filter(function() {
            return $(this).data('show') === value;
        }).show();
        $('[data-hide]').show().filter(function() {
            return $(this).data('hide') === value;
        }).hide();
    }).change();
});
</script>

@endsection
