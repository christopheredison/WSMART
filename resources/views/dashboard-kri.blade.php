@extends('layouts.default')

@section('dashboard')
<!--========================= Dashboard Header =========================-->
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="../assets/img/dashboard-header4.webp" alt="dashboard">
      <div class="card-header border-0">
        <h1 class="mb-auto mt-3 mt-md-6">Risk Dashboard KRI</h1>
        <h6>Statistik per tanggal {{ now()->format('d M Y') }}</h6>
      </div>
    </div>
  </div>
</div>

<!--========================= Input Filter =========================-->
<div class="row input-selector-rounded g-3 mb-3">
  <div class="col-12">
    <form action="{{ url()->current() }}">
      <div class="row g-3 g-xxl-2 input-filter-container">
        <div class="col-auto">
          <select name="periode_id" id="periode_selector" class="form-select select2 js-select-hide-search">
            <option value="" selected disabled>Periode</option>
            <option value="2023">2023</option>
            <option value="2024">2024</option>
            <option value="2025">2025</option>
          </select>
        </div>
        <div class="col-6 col-md-3 col-lg-3">
          <select name="" id="ro_select" class="form-select select2 js-select-hide-search">
            <option value="" selected disabled>Pemilik Risiko</option>
            <option value="semua">Semua</option>
            <option value="corporate">Corporate</option>
            <option value="unit">Unit</option>
            <option value="proyek">Proyek</option>
            <option value="anper">Anak Perusahaan</option>
          </select>
        </div>
        <!-- Unit Selector -->
        <div class="col-12 col-md-6 col-xl-4" data-show="unit">
          <select name="" id="ro_item" class="form-select select2 js-select-hide-search">
            <option value="" selected disabled>Pilih Unit</option>
            <option value="">Semua</option>
            <option value="">Unit A</option>
            <option value="">Unit B</option>
            <option value="">Unit C</option>
            <option value="">Unit D</option>
          </select>
        </div>
        <!-- Proyek Selector -->
        <div class="col-12 col-md-6 col-xl-4" data-show="proyek">
          <select name="" id="ro_item" class="form-select select2 js-select-hide-search">
            <option value="" selected disabled>Pilih Proyek</option>
            <option value="">Semua</option>
            <option value="">Proyek A</option>
            <option value="">Proyek B</option>
            <option value="">Proyek C</option>
            <option value="">Proyek D</option>
          </select>
        </div>
        <!-- Anak Perusahaan Selector -->
        <div class="col-12 col-md-6 col-xl-4" data-show="anper">
          <select name="" id="ro_item" class="form-select select2 js-select-hide-search">
            <option value="" selected disabled>Pilih Anak Perusahaan</option>
            <option value="">Semua</option>
            <option value="">Anak Perusahaan A</option>
            <option value="">Anak Perusahaan B</option>
            <option value="">Anak Perusahaan C</option>
            <option value="">Anak Perusahaan D</option>
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
        <h3>Tren Kategori Risiko</h3>
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
                <th>Pemilik Risiko</th>
                <th class="text-center white-space-nowrap">Batas Aman</th>
                <th class="text-center white-space-nowrap">Batas Waspada</th>
                <th class="text-center white-space-nowrap">Batas Bahaya</th>
                <th class="text-center white-space-nowrap">Kondisi Saat Ini</th>
                <th>Kategori Risiko</th>
                <th class="text-center">Status</th>
                <th class="text-center white-space-nowrap">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>KRI 1</td>
                <td>Risiko 1</td>
                <td>RO 1</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">1</td>
                <td>Risiko Kredit</td>
                <td class="text-center">
                  <div class="status-container red">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center white-space-nowrap">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
              <tr>
                <td>KRI 2</td>
                <td>Risiko 2</td>
                <td>RO 2</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">3</td>
                <td>Risiko Pasar</td>
                <td class="text-center">
                  <div class="status-container yellow">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
              <tr>
                <td>KRI 3</td>
                <td>Risiko 3</td>
                <td>RO 3</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">2</td>
                <td>Risiko Likuiditas</td>
                <td class="text-center">
                  <div class="status-container green">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center white-space-nowrap">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
              <tr>
                <td>KRI 4</td>
                <td>Risiko 4</td>
                <td>RO 4</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">2</td>
                <td>Risiko Hukum</td>
                <td class="text-center">
                  <div class="status-container green">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center white-space-nowrap">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
              <tr>
                <td>KRI 5</td>
                <td>Risiko 5</td>
                <td>RO 5</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">1</td>
                <td>Risiko Stratejik</td>
                <td class="text-center">
                  <div class="status-container yellow">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center white-space-nowrap">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
              <tr>
                <td>KRI 6</td>
                <td>Risiko 6</td>
                <td>RO 6</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">3</td>
                <td>Risiko Kepatuhan</td>
                <td class="text-center">
                  <div class="status-container red">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center white-space-nowrap">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
              <tr>
                <td>KRI 7</td>
                <td>Risiko 7</td>
                <td>RO 7</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">2</td>
                <td>Risiko Reputasi</td>
                <td class="text-center">
                  <div class="status-container yellow">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center white-space-nowrap">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
              <tr>
                <td>KRI 8</td>
                <td>Risiko 8</td>
                <td>RO 9</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">1</td>
                <td>Risiko Hukum</td>
                <td class="text-center">
                  <div class="status-container green">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center white-space-nowrap">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
              <tr>
                <td>KRI 9</td>
                <td>Risiko 9</td>
                <td>RO 9</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">1</td>
                <td>Risiko Kepatuhan</td>
                <td class="text-center">
                  <div class="status-container green">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center white-space-nowrap">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
              <tr>
                <td>KRI 10</td>
                <td>Risiko 10</td>
                <td>RO 10</td>
                <td class="text-center">1</td>
                <td class="text-center">2</td>
                <td class="text-center">3</td>
                <td class="text-center fw-bold ff-heading">3</td>
                <td>Risiko Kredit</td>
                <td class="text-center">
                  <div class="status-container red">
                    <div class="status-green"></div>
                    <div class="status-yellow"></div>
                    <div class="status-red"></div>
                  </div>
                </td>
                <td class="text-center white-space-nowrap">
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#trenRisikoModal">
                    <span class="bx bx-show-alt" data-bs-toggle="tooltip" title="View"></span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- KRI per Kategori Risiko -->
  <div class="col-md-6">
    <div class="card card-sm">
      <div class="card-header pb-xxl-0 border-0 gap-3">
        <div class="row w-100 g-3">
          <div class="col-md-8 col-lg-12 col-xl-7 d-flex align-items-center">
            <h3 class="h4">KRI per Kategori Risiko</h3>
          </div>
          <div class="col-md-4 col-lg-12 col-xl-5">
            <select name="" id="" class="form-select select2 js-select-hide-search">
              <option value="" selected disabled>Kategori Risiko</option>
              <option value="1">Risiko Kredit</option>
              <option value="2">Risiko Pasar</option>
              <option value="3">Risiko Likuiditas</option>
              <option value="4">Risiko Operasinal</option>
              <option value="5">Risiko Hukum</option>
              <option value="6">Risiko Reputasi</option>
              <option value="7">Risiko Stratejik</option>
              <option value="8">Risiko Kepatuhan</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-body px-md-0 py-lg-6 py-xxl-8">
        <div class="ratio ratio-1x1 ratio-md-4x3">
          <div class="d-flex justify-content-center">
            <canvas class="" id="kri_kategori"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- KRI per Pemilik Risiko -->
  <div class="col-md-6">
    <div class="card card-sm">
      <div class="card-header pb-xxl-0 border-0 gap-3">
        <div class="row w-100 g-3">
          <div class="col-md-8 col-lg-12 col-xl-7 d-flex align-items-center">
            <h3 class="h4">KRI per Pemilik Risiko</h3>
          </div>
          <div class="col-md-4 col-lg-12 col-xl-5">
            <select name="" id="" class="form-select select2 js-select-hide-search">
              <option value="" selected disabled>Pemilik Risiko</option>
              <option value="1">RO 1</option>
              <option value="2">RO 2</option>
              <option value="3">RO 3</option>
              <option value="4">RO 4</option>
              <option value="5">RO 5</option>
              <option value="6">RO 6</option>
              <option value="7">RO 7</option>
              <option value="8">RRO 8</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-body px-md-0 py-lg-6 py-xxl-8">
        <div class="ratio ratio-1x1 ratio-md-4x3">
          <div class="d-flex justify-content-center">
            <canvas class="" id="kri_pemilik"></canvas>
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
//=================== KRI Chart ===================
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

//=================== KRI per Kategori Chart ===================
const kategoriChart = document.getElementById('kri_kategori');
new Chart(kategoriChart, {
  type: 'doughnut',
  data: {
    labels: ['Aman', 'Waspada', 'Bahaya'],
    datasets: [{
      data: [45, 30, 25],
      backgroundColor: [
        'rgb(25, 163, 0)',
        'rgb(253, 220, 34)',
        'rgb(240, 100, 69)'
      ],
      hoverOffset: 10
    }]
  },
  options: {
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          padding: 20
        }
      }
    },
  }

});

//=================== KRI per Pemilik Risiko Chart ===================
const pemilikChart = document.getElementById('kri_pemilik');
new Chart(pemilikChart, {
  type: 'doughnut',
  data: {
    labels: ['Aman', 'Waspada', 'Bahaya'],
    datasets: [{
      data: [33, 12, 55],
      backgroundColor: [
        'rgb(25, 163, 0)',
        'rgb(253, 220, 34)',
        'rgb(240, 100, 69)'
      ],
      hoverOffset: 10
    }]
  },
  options: {
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          padding: 20
        }
      }
    },
  }

});

$("#ro_select").on("change", function() {
  var value = this.value
  // hide all data-show
  $('[data-show]').hide().filter(function() {
    return $(this).data('show') === value;
  }).show() // only show matching ones

  $('[data-hide]').show().filter(function() {
    return $(this).data('hide') === value;
  }).hide()

}).change()
</script>

@endsection