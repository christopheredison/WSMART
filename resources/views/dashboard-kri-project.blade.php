@extends('layouts.default')

@section('dashboard')
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="../assets/img/dashboard-header4.webp" alt="dashboard">
      <div class="card-header border-0">
        <h1 class="mb-auto mt-3 mt-md-6">Risk Dashboard KRI Proyek</h1>
        <h6>Statistik per tanggal {{ now()->format('d M Y') }}</h6>
      </div>
    </div>
  </div>
</div>

<div class="row input-selector-rounded g-3 mb-3">
  <div class="col-12">
    <form id="filter-form" action="{{ url()->current() }}" method="GET">
      <div class="row g-3 g-xxl-2 input-filter-container">
        <div class="col-auto">
          <select name="tahun" id="tahun_selector" class="form-select select2 js-select-hide-search" onchange="this.form.submit()">
            @foreach($yearList as $year)
              <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                {{ $year }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="unit_id" id="unit_selector" class="form-select select2" onchange="this.form.submit()">
            <option value="">Semua Divisi</option>
            @foreach ($units as $unit)
              <option value="{{ $unit->id }}" {{ $unit->id == $selectedUnitId ? 'selected' : '' }}>
                {{ $unit->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="project_id" id="project_selector" class="form-select select2" onchange="this.form.submit()">
            <option value="">Semua Proyek</option>
            @foreach($projects as $project)
              <option value="{{ $project->id }}" {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                {{ $project->project_name }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 dashboard-content">
  <div class="col-12">
    <div class="card card-sm">
      <div class="card-header pb-0 border-0">
        <h3>Daftar KRI Proyek</h3>
      </div>
      <div class="card-body">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th>KRI</th>
                <th>Risiko</th>
                <th>Proyek</th>
                <th class="text-center white-space-nowrap">Batas Aman</th>
                <th class="text-center white-space-nowrap">Batas Siaga</th>
                <th class="text-center white-space-nowrap">Batas Bahaya</th>
                <th class="text-center white-space-nowrap">Kondisi Saat Ini</th>
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
                <td colspan="8" class="text-center">Tidak ada data KRI</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card card-sm">
      <div class="card-header pb-xxl-0 border-0 gap-3">
        <div class="row w-100 g-3">
          <div class="col-md-8 col-lg-12 col-xl-7 d-flex align-items-center">
            <h3 class="h4">KRI per Peristiwa Risiko</h3>
          </div>
          <div class="col-md-4 col-lg-12 col-xl-5">
            <select name="peristiwa_risiko_filter" id="peristiwa_risiko_filter" class="form-select select2">
              <option value="">Semua Peristiwa Risiko</option>
                @foreach($peristiwaRisikoList as $peristiwa)
                    <option value="{{ $peristiwa->title }}">{{ $peristiwa->title }}</option>
                @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="card-body px-md-0 py-lg-6 py-xxl-8">
        <div class="ratio ratio-1x1 ratio-md-4x3">
          <div class="d-flex justify-content-center">
            <canvas id="kri_peristiwa_risiko_chart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card card-sm">
      <div class="card-header pb-xxl-0 border-0 gap-3">
        <div class="row w-100 g-3">
          <div class="col-md-8 col-lg-12 col-xl-7 d-flex align-items-center">
            <h3 class="h4">KRI per Proyek</h3>
          </div>
          <div class="col-md-4 col-lg-12 col-xl-5">
            <select name="project_filter" id="project_filter" class="form-select select2">
              <option value="">Semua Proyek</option>
              @foreach($projects as $project)
                  <option value="{{ $project->project_name }}">{{ $project->project_name }}</option>
                @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="card-body px-md-0 py-lg-6 py-xxl-8">
        <div class="ratio ratio-1x1 ratio-md-4x3">
          <div class="d-flex justify-content-center">
            <canvas id="kri_project_chart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@section('scripts')
<script src="/vendors/chart-js/chart.min.js"></script>
<script src="/vendors/echarts/echarts.min.js"></script>
<script type="text/javascript">

const peristiwaRisikoData = @json($peristiwaRisikoData);
const projectData = @json($projectData);

let peristiwaRisikoChartInstance;
let projectChartInstance;

const peristiwaRisikoChartCanvas = document.getElementById('kri_peristiwa_risiko_chart');
const projectChartCanvas = document.getElementById('kri_project_chart');

//=================== KRI per Peristiwa Risiko Chart ===================
function updatePeristiwaRisikoChart(selectedPeristiwaName = null) {
    if (!peristiwaRisikoChartCanvas) return;

    let filteredData = peristiwaRisikoData;
    if (selectedPeristiwaName) {
        filteredData = peristiwaRisikoData.filter(item => item.peristiwa_risiko == selectedPeristiwaName);
    }

    let totalAman = 0, totalSiaga = 0, totalBahaya = 0;
    filteredData.forEach(function(item) {
        totalAman += item.aman;
        totalSiaga += item.waspada;
        totalBahaya += item.bahaya;
    });

    if (peristiwaRisikoChartInstance) {
        peristiwaRisikoChartInstance.destroy();
    }

    peristiwaRisikoChartInstance = new Chart(peristiwaRisikoChartCanvas, {
        type: 'doughnut',
        data: {
            labels: ['Aman', 'Siaga', 'Bahaya'],
            datasets: [{
                data: [totalAman, totalSiaga, totalBahaya],
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

//=================== KRI per Proyek Chart ===================
function updateProjectChart(selectedProjectName = null) {
    if (!projectChartCanvas) return;

    let filteredData = projectData;
    if (selectedProjectName) {
        filteredData = projectData.filter(item => item.project_name == selectedProjectName);
    }

    let totalAman = 0, totalSiaga = 0, totalBahaya = 0;
    filteredData.forEach(function(item) {
        totalAman += item.aman;
        totalSiaga += item.waspada;
        totalBahaya += item.bahaya;
    });

    if (projectChartInstance) {
        projectChartInstance.destroy();
    }

    projectChartInstance = new Chart(projectChartCanvas, {
        type: 'doughnut',
        data: {
            labels: ['Aman', 'Siaga', 'Bahaya'],
            datasets: [{
                data: [totalAman, totalSiaga, totalBahaya],
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
    updatePeristiwaRisikoChart();
    updateProjectChart();

    // Event listener untuk filter Peristiwa Risiko
    $('#peristiwa_risiko_filter').on('change', function() {
        const selectedName = $(this).val();
        updatePeristiwaRisikoChart(selectedName);
    });

    // Event listener untuk filter Proyek
    $('#project_filter').on('change', function() {
        const selectedName = $(this).val();
        updateProjectChart(selectedName);
    });
});
</script>

@endsection