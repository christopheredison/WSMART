@extends('layouts.default')

@section('dashboard')
<!--========================= Dashboard Header =========================-->
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="../assets/img/dashboard-header2.webp" alt="dashboard">
      <div class="card-header border-0">
        <h1 class="mb-auto mt-3 mt-md-6">Risk Dashboard Corporate</h1>
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
<div class="col-12 g-3 dashboard-content">
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
                  <th>Nilai Dampak Residual <small>(Sesuai Kuartal)</small></th>
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
                <tr data-risk-id="{{ $risiko->id }}">
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
                  <td class="residual-nilai-dampak">-</td>
                  <td class="residual-skala-dampak">-</td>
                  <td class="residual-nilai-prob">-</td>
                  <td class="residual-skala-prob">-</td>
                  <td class="residual-nilai-risiko">-</td>
                  <td class="residual-eksposur-risiko">-</td>
                  <td class="residual-level-risiko">-</td>
                </tr>
                @endforeach
                @if ($risikos->isEmpty())
                <tr>
                  <td colspan="15" class="text-center p-3">Tidak ada data</td>
                </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--============================ Penilaian RMI ============================-->
  <div class="col-12 mb-3">
    <div class="card">
      <div class="card-header stepper border-0 pb-0">
        <div class="nav-link active d-flex align-items-center p-0">
          <span class="h3 mb-0">Peta Komposit Risiko</span>
        </div>
      </div>
      <div class="card-body">
        {{-- 1. Informasi Periode & Ringkasan --}}
        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <div class="card border-primary shadow-sm">
              <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                Informasi Periode RMI
                @if ($period->id)
                <a href="{{ route('penilaian-rmi.show', $period->id) }}" class="btn-input-icon text-white" data-bs-toggle="tooltip"
                    title="Lihat Detail">
                    <span class="bx bx-show"></span>
                </a>
                @endif
              </div>
              <div class="card-body">
                <dl class="row mb-0">
                  <dt class="col-sm-4">Tahun Periode</dt><dd class="col-sm-8">{{ $period->year }}</dd>
                  <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                      @if($period->status==1)
                        <span class="badge bg-warning">Dalam Proses</span>
                      @else
                        <span class="badge bg-success">Selesai</span>
                      @endif
                    </dd>
                  <dt class="col-sm-4">Score Dimension</dt><dd class="col-sm-8">{{ $period->score_rmi }}</dd>
                  <dt class="col-sm-4">Deskripsi Score</dt><dd class="col-sm-8">{{ $period->score_rmi_desc }}</dd>
                  <dt class="col-sm-4">Score RMI</dt><dd class="col-sm-8">{{ $period->final_score_rmi }}</dd>
      
                  <dt class="col-sm-4">Tanggal Update</dt><dd class="col-sm-8">{{ $period->updated_at->format('d M Y H:i') }}</dd>
                </dl>
              </div>
            </div>
          </div>
      
          <div class="col-md-6">
            <div class="card border-primary shadow-sm">
              <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                Ringkasan Kinerja &amp; KPMR
                @if ($period->id)
                <a href="{{ route('penilaian-rmi.show', $period->id) }}" class="btn-input-icon text-white" data-bs-toggle="tooltip"
                    title="Lihat Detail">
                    <span class="bx bx-show"></span>
                </a>
                @endif
              </div>
              <div class="card-body">
                <dl class="row mb-0">
                  <dt class="col-sm-6">Kinerja</dt><dd class="col-sm-6">{{ $period->kinerja }}</dd>
                  <dt class="col-sm-6">KPMR</dt><dd class="col-sm-6">{{ $period->kpmr }}</dd>
                  <dt class="col-sm-6">Peringkat Komposit Risiko</dt><dd class="col-sm-6">{{ $period->peringkat_komposit_risiko }}</dd>
                  <dt class="col-sm-6">Nilai Konversi</dt><dd class="col-sm-6">{{ $period->nilai_konversi }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        {{-- Peta Komposit Risiko --}}
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            Peta Komposit Risiko
            @if ($period->id)
            <a href="{{ route('penilaian-rmi.show', $period->id) }}" class="btn-input-icon text-white" data-bs-toggle="tooltip"
                title="Lihat Detail">
                <span class="bx bx-show"></span>
            </a>
            @endif
          </div>
          <div class="card-body table-responsive p-0">
            <table class="table table-bordered text-center mb-0">
              <thead>
                <tr class="bg-teal text-white">
                  <th rowspan="2" class="align-middle">Kinerja →</th>
                  <th colspan="5">Kualitas Penerapan Manajemen Risiko →</th>
                </tr>
                <tr class="bg-navy text-white">
                  <th class="map-cell">Strong</th>
                  <th class="map-cell">Satisfactory</th>
                  <th class="map-cell">Fair</th>
                  <th class="map-cell">Marginal</th>
                  <th class="map-cell">Unsatisfactory</th>
                </tr>
              </thead>
              <tbody>
                @php
                  // Matriks 5×5
                  $matrix = [
                    [1,1,2,3,3],
                    [1,2,2,3,4],
                    [2,2,3,4,4],
                    [2,3,4,4,5],
                    [3,3,4,5,5],
                  ];
      
                  $rowLabels   = ['Sangat Baik','Baik','Cukup','Kurang','Buruk'];
                  $valueClasses = [
                    1 => 'bg-strong',
                    2 => 'bg-satisfactory',
                    3 => 'bg-fair',
                    4 => 'bg-marginal',
                    5 => 'bg-unsatisfactory',
                  ];
      
                  // Dapatkan ID skala (1–5) dari PenilaianCapaianKinerja
                  $pen  = $period->penilaianCapaianKinerja;
                  $kRow = ($pen?->capaian_kinerja ?? 1) - 1;
                  $kCol = ($pen?->kpmr ?? 1) - 1;
                @endphp
      
                @foreach($matrix as $r => $cols)
                  <tr>
                    {{-- Label baris --}}
                    <th class="align-middle">{{ $rowLabels[$r] }}</th>
      
                    @foreach($cols as $c => $cell)
                      @php
                        // cek sel aktif berdasarkan (baris=kRow, kolom=kCol)
                        $isCurrent = ($r === $kRow && $c === $kCol);
                        // kelas berdasarkan nilai sel (1–5)
                        $bgClass = $valueClasses[$cell] ?? '';
                      @endphp
      
                      <td class="map-cell {{ $bgClass }}{{ $isCurrent ? ' current' : '' }}">
                        {{ $cell }}
                        @if($isCurrent)
                          <i class="bi bi-geo-alt-fill text-dark"></i>
                        @endif
                      </td>
                    @endforeach
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--============================ Top 5 Risk ============================-->
  {{-- <div class="col-12">
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
          <h3>Top 5 Risk</h3>
        </div>
        <hr class="mb-0 mt-xxl-5">
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th>Peristiwa Risiko</th>
                <th>Deskripsi peristiwa risiko</th>
                <th>Jenis Risiko</th>
                <th class="text-center white-space-nowrap">Tingkat Risiko</th>
                <th class="white-space-nowrap">Sasaran</th>
                <th>KRI</th>
                <th class="text-center white-space-nowrap">Status KRI</th>
                <th class="white-space-nowrap">Risk Owner</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div> --}}

  <!--============================ Loss Event Data Unit ============================-->
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
          <h3>Top 10 Loss Event Data Divisi</h3>
        </div>
        <hr class="mb-2 mt-xxl-5">
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th rowspan="2">Tanggal Kejadian</th>
                <th rowspan="2">Nama Kejadian</th>
                <th rowspan="2">Identifikasi Kejadian</th>
                <th rowspan="2">Kategori Kejadian</th>
                <th rowspan="2">Nilai Kerugian</th>
                <th rowspan="2">Pihak Terkait</th>
              </tr>
              {{-- <tr>
                <th class="no-sort text-center py-2">Finansial (IDR)</th>
                <th class="no-sort text-center py-2">Non Finansial</th>
              </tr> --}}
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!--============================ Loss Event Data Project ============================-->
  <div class="col-12">
    <div class="card" id="led-project-card">
      <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-info-subtle rounded-3 p-2">
            <div class="lead__icon lead__icon_sm">
              <span class="svg-icon svg-icon-2x svg-icon-info">
                @include('partials.icon-abs05')
              </span>
            </div>
          </div>
          <h3>Top 10 Loss Event Data Project</h3>
        </div>
        <hr class="mb-2 mt-xxl-5">
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th rowspan="2">Tanggal Kejadian</th>
                <th rowspan="2">Nama Kejadian</th>
                <th rowspan="2">Identifikasi Kejadian</th>
                <th rowspan="2">Kategori Kejadian</th>
                <th rowspan="2">Nilai Kerugian</th>
                <th rowspan="2">Pihak Terkait</th>
              </tr>
              {{-- <tr>
                <th class="no-sort text-center py-2">Finansial (IDR)</th>
                <th class="no-sort text-center py-2">Non Finansial</th>
              </tr> --}}
            </thead>
            <tbody>
            </tbody>
          </table>
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

/* Style Penilaian PMI */
/* 1. Layout fixed & kolom pertama sempit */
.card .table-peta {
  table-layout: fixed;
  width: 100%;
}
.table-peta th:first-child,
.table-peta td:first-child {
  width: 120px;
  white-space: nowrap;
}

/* 2. Semua kolom peta dibagi rata */
.table-peta th:not(:first-child),
.table-peta td.map-cell {
  width: calc((100% - 120px)/5);
}

/* 3. Kotak peta */
.map-cell {
  padding: .25rem;
  height: 40px;
  vertical-align: middle;
  text-align: center;
  white-space: normal;
  word-break: break-word;
}

/* 4. Warna berdasarkan nilai */
.bg-strong         { background-color: #3cbf87 !important; }  /* untuk “1” */
.bg-satisfactory   { background-color: #a8e6cf !important; }  /* untuk “2” */
.bg-fair           { background-color: #ffec99 !important; }  /* untuk “3” */
.bg-marginal       { background-color: #ffc078 !important; }  /* untuk “4” */
.bg-unsatisfactory { background-color: #ffa8a8 !important; }  /* untuk “5” */

/* 5. Highlight sel terpilih */
.map-cell.current {
  border: 2px solid #d63384 !important;
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
  const riskResidualData = @json($riskResidualData);
  const riskMatrix = @json($riskMaps);

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
    
    // Lost Event Data Divisi
    $('#led-card .table tbody').html('');
    if (dashboardData.led.length == 0) {
      $('#led-card .table tbody').append(`
              <tr>
                  <td colspan="7" class="dt-empty text-center">No data available</td>
              </tr>
          `);
    } else {
      dashboardData.led.forEach((led) => {
        $('#led-card .table tbody').append(`
                <tr>
                    <td>${led.tanggal_kejadian}</td>
                    <td>${led.nama_kejadian}</td>
                    <td>${led.identifikasi_kejadian}</td>
                    <td>${led.kategori_kejadian}</td>
                    <td>${led.nilai_kerugian}</td>
                    <td>${led.unit_penanggung_jawab}</td>
                </tr>
            `);
      });
    }

    // Lost Event Data Project
    $('#led-project-card .table tbody').html('');
    if (dashboardData.ledProject.length == 0) {
      $('#led-project-card .table tbody').append(`
              <tr>
                  <td colspan="7" class="dt-empty text-center">No data available</td>
              </tr>
          `);
    } else {
      dashboardData.ledProject.forEach((led) => {
        $('#led-project-card .table tbody').append(`
                <tr>
                    <td>${led.tanggal_kejadian}</td>
                    <td>${led.nama_kejadian}</td>
                    <td>${led.identifikasi_kejadian}</td>
                    <td>${led.kategori_kejadian}</td>
                    <td>${led.nilai_kerugian}</td>
                    <td>${led.unit_penanggung_jawab}</td>
                </tr>
            `);
      });
    }
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
    $('#periode_selector').on('change', function() {
      $('#filterForm').submit();
    });
  }

  const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
  };

  const getLevelClass = (levelName) => {
    if (!levelName) return '';
    return 'bg-' + levelName.toLowerCase().replace('to ', '').replace(/\s+/g, '-');
  };

  function getResidualMatrix(risk, quarter) {
    const analysis = risk.risk_analysis;
    if (!analysis) {
      return null;
    }

    const skalaDampak = analysis[`skala_dampak_residual_q${quarter}`];
    const skalaProb = analysis[`skala_probabilitas_residual_q${quarter}`]?.tingkat;

    if (skalaDampak == null || skalaProb == null) {
      return null;
    }

    return `${skalaDampak}-${skalaProb}`;
  }

  function renderInherentMapMarkers() {
    $('#inherentMap.table-risk-map .data-cell').each(function() {
      $(this).removeData('kode-peristiwa-inherent');
      $(this).removeData('kode-peristiwa-residual');
      $(this).removeData('has-inherent');
      $(this).removeData('has-residual');
      $(this).find('.kode-peristiwa').empty();
    });

    risks.forEach((risk, idx) => {
      const matrixI = risk.risk_analysis?.skala_dampak + '-' + risk.risk_analysis?.skala_probabilitas?.tingkat;
      const cellI = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixI}"]`);
      const code = (idx + 1).toString();

      if (cellI.length) {
        if (!cellI.data('kode-peristiwa-inherent')) {
          cellI.data('kode-peristiwa-inherent', []);
        }
        cellI.data('kode-peristiwa-inherent').push(code);
        cellI.data('has-inherent', true);
      }
    });

    paintInherentResidualMapCells();
  }

  function renderResidualMapMarkers(quarter) {
    $('#inherentMap.table-risk-map .data-cell').each(function() {
      $(this).removeData('kode-peristiwa-residual');
      $(this).removeData('has-residual');
    });

    risks.forEach((risk, idx) => {
      const matrixR = getResidualMatrix(risk, quarter);
      if (!matrixR) {
        return;
      }

      const cellR = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixR}"]`);
      const code = (idx + 1).toString();

      if (cellR.length) {
        if (!cellR.data('kode-peristiwa-residual')) {
          cellR.data('kode-peristiwa-residual', []);
        }
        cellR.data('kode-peristiwa-residual').push(code);
        cellR.data('has-residual', true);
      }
    });

    paintInherentResidualMapCells();
  }

  function paintInherentResidualMapCells() {
    $('#inherentMap.table-risk-map .data-cell').each(function() {
      const cell = $(this);
      let html = '';

      const kodePeristiwaInherent = cell.data('kode-peristiwa-inherent');
      if (kodePeristiwaInherent && kodePeristiwaInherent.length > 0) {
        for (let i = 0; i < kodePeristiwaInherent.length; i++) {
          html += `<span class="box-inherent">R${kodePeristiwaInherent[i]}</span>`;
        }
      }

      const kodePeristiwaResidual = cell.data('kode-peristiwa-residual');
      if (kodePeristiwaResidual && kodePeristiwaResidual.length > 0) {
        for (let i = 0; i < kodePeristiwaResidual.length; i++) {
          html += `<span class="box-residual">R${kodePeristiwaResidual[i]}</span>`;
        }
      }

      cell.find('.kode-peristiwa').html(html);
    });
  }

  function renderCurrentMapMarkers() {
    $('#currentMap.table-risk-map .data-cell').each(function() {
      for (let quarter = 1; quarter <= 4; quarter++) {
        $(this).removeData('kode-peristiwa-current-q' + quarter);
      }
      $(this).removeData('has-current');
      $(this).find('.kode-peristiwa').empty();
    });

    risks.forEach((risk, idx) => {
      const currentRiskMaps = formattedCurrentRiskMaps[risk.id] || [];
      const code = (idx + 1).toString();

      currentRiskMaps.forEach((currentRiskMap) => {
        if (!currentRiskMap?.quarter) {
          return;
        }

        const matrixC = currentRiskMap.skala_dampak + '-' + currentRiskMap.skala_probabilitas;
        const cellC = $(`#currentMap.table-risk-map .data-cell[data-matrix="${matrixC}"]`);

        if (cellC.length) {
          const quarterKey = 'kode-peristiwa-current-q' + currentRiskMap.quarter;
          if (!cellC.data(quarterKey)) {
            cellC.data(quarterKey, []);
          }
          cellC.data(quarterKey).push(code);
          cellC.data('has-current', true);
        }
      });
    });

    $('#currentMap.table-risk-map .data-cell').each(function() {
      const cell = $(this);
      let html = '';

      for (let quarter = 1; quarter <= 4; quarter++) {
        const kodePeristiwaCurrent = cell.data('kode-peristiwa-current-q' + quarter);
        if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
          for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
            html += `<span class="box-current current-q${quarter}">R${kodePeristiwaCurrent[i]}</span>`;
          }
        }
      }

      cell.find('.kode-peristiwa').html(html);
    });
  }

  function updateSummaryTable(quarter) {
    $('tr[data-risk-id]').each(function() {
      const tr = $(this);
      const riskId = tr.data('risk-id');

      if (riskResidualData[riskId] && riskResidualData[riskId][quarter]) {
        const dataRes = riskResidualData[riskId][quarter];

        tr.find('.residual-nilai-dampak').text(dataRes.nilai_dampak ? formatRupiah(dataRes.nilai_dampak) : '-');
        tr.find('.residual-skala-dampak').text(dataRes.skala_dampak ?? '-');
        tr.find('.residual-nilai-prob').text(dataRes.nilai_prob ?? '-');
        tr.find('.residual-skala-prob').text(dataRes.skala_prob ?? '-');
        tr.find('.residual-nilai-risiko').text(dataRes.skala_risiko ?? '-');
        tr.find('.residual-eksposur-risiko').text(dataRes.eksposur_risiko ? formatRupiah(dataRes.eksposur_risiko) : '-');

        const tdResLevel = tr.find('.residual-level-risiko');
        tdResLevel.text(dataRes.level_risiko ?? '-');
        tdResLevel.removeClass(function (index, className) {
          return (className.match(/(^|\s)bg-\S+/g) || []).join(' ');
        });
        if (dataRes.level_risiko) tdResLevel.addClass(getLevelClass(dataRes.level_risiko));
      }
    });
  }

  function updateDashboard(quarter) {
    $('#currentMap').prop('class', 'table-risk-map');
    $('#currentMap').addClass('show-q' + quarter);
    renderResidualMapMarkers(quarter);
    updateSummaryTable(quarter);
  }

  function initializeMaps() {
    const currentQuarter = Math.ceil((new Date().getMonth() + 1) / 3);
    $('#quarterSelect').val(String(currentQuarter));
    renderInherentMapMarkers();
    renderCurrentMapMarkers();
    updateDashboard(currentQuarter);
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
      updateDashboard($('#quarterSelect').val());
  });

  // initKpiChart();
  // initRprChart();
  refreshSummary();
  initInputFilter();
  initializeMaps();
});
</script>

@endsection
