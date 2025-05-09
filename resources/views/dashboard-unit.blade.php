@extends('layouts.default')

@section('dashboard')
<!--========================= Dashboard Header =========================-->
<div class="row mb-7">
  <div class="col-12">
    <div class="card border-0 dashboard-header">
      <img src="../assets/img/dashboard-header6.webp" alt="dashboard">
      <div class="card-header text-white border-0 mt-auto mb-5">
        <h1 class="mb-2">Risk Dashboard Unit</h1>
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
        <div class="col-md-3 col-xxl-2">
          <select name="unit_id" id="" class="form-select select2 js-select-hide-search">
            <option value="" selected disabled>Unit</option>
            <option value="a">Unit A</option>
            <option value="b">Unit B</option>
            <option value="c">Unit C</option>
          </select>
        </div>
      </div>
    </form>
  </div>

  <!--========================= Dashboard Chart Start =========================-->
  <div class="col-12">
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
            <div class="title">Capaian TKMRU</div>
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
  </div>
</div>
<!--========================= Dashboard Chart End =========================-->

<!--========================= Dashboard Content Start =========================-->
<div class="row g-3 dashboard-content">
  <!-- Peta Risiko Inheren dan Residual -->
  <div class="col-lg-6">
    <div class="card" id="prir-card">
      <div class="card-header border-0 pb-0 d-flex flex-between-center">
        <h3 class="h4">Peta Risiko Inheren dan Residual</h3>
        <span class="card-subtitle my-auto"></span>
      </div>
      <div class="card-body">
        <div class="table-risk-map">
          <table class="map-table">
            <tbody>
              <tr>
                <td rowspan="5" class="side-title">
                  <div class="divider m-0">
                    <div class="divider-text">
                      LIKELIHOOD
                    </div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="1" data-posisi-risiko="7">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate" data-id="2" data-posisi-risiko="12">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate-to-high" data-id="3" data-posisi-risiko="17">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="4" data-posisi-risiko="22">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="5" data-posisi-risiko="25">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell low" data-id="6" data-posisi-risiko="4">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="7" data-posisi-risiko="9">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate" data-id="8" data-posisi-risiko="14">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate-to-high" data-id="9" data-posisi-risiko="19">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="10" data-posisi-risiko="24">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell low" data-id="11" data-posisi-risiko="3">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="12" data-posisi-risiko="8">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate" data-id="13" data-posisi-risiko="13">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate-to-high" data-id="14" data-posisi-risiko="18">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="15" data-posisi-risiko="23">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell low" data-id="16" data-posisi-risiko="2">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="17" data-posisi-risiko="6">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="18" data-posisi-risiko="11">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate-to-high" data-id="19" data-posisi-risiko="16">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="20" data-posisi-risiko="21">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell low" data-id="21" data-posisi-risiko="1">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low" data-id="22" data-posisi-risiko="5">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="23" data-posisi-risiko="10">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate" data-id="24" data-posisi-risiko="15">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="25" data-posisi-risiko="20">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
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
          <div class="alert alert-info">Strategi Perlakuan</div>
          <div class="table-responsive scrollbar">
            <table class="table table-strategi">
              <thead>
                <tr>
                  <th rowspan="2" class="left-align">Kode & Peristiwa Risiko</th>
                  <th colspan="2" class="white-space-nowrap">Posisi Risiko
                  </th>
                  <th rowspan="2" class="left-align">Strategi Perlakuan Risiko (Rencana)</th>
                  <th rowspan="2" class="white-space-nowrap">Tenggat Waktu</th>
                </tr>
                <tr>
                  <th>IRE</th>
                  <th>RRE</th>
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
      <div class="card-header border-0 pb-0 d-flex flex-between-center">
        <h3 class="h4">Peta Risiko Terkini (Current)</h3>
        <span class="card-subtitle"></span>
      </div>
      <div class="card-body">
        <div class="table-risk-map">
          <table class="map-table">
            <tbody>
              <tr>
                <td rowspan="5" class="side-title">
                  <div class="divider m-0">
                    <div class="divider-text">
                      LIKELIHOOD
                    </div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="1" data-posisi-risiko="7">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate" data-id="2" data-posisi-risiko="12">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate-to-high" data-id="3" data-posisi-risiko="17">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="4" data-posisi-risiko="22">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="5" data-posisi-risiko="25">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell low" data-id="6" data-posisi-risiko="4">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="7" data-posisi-risiko="9">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate" data-id="8" data-posisi-risiko="14">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate-to-high" data-id="9" data-posisi-risiko="19">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="10" data-posisi-risiko="24">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell low" data-id="11" data-posisi-risiko="3">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="12" data-posisi-risiko="8">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate" data-id="13" data-posisi-risiko="13">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate-to-high" data-id="14" data-posisi-risiko="18">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="15" data-posisi-risiko="23">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell low" data-id="16" data-posisi-risiko="2">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="17" data-posisi-risiko="6">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="18" data-posisi-risiko="11">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate-to-high" data-id="19" data-posisi-risiko="16">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="20" data-posisi-risiko="21">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell low" data-id="21" data-posisi-risiko="1">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low" data-id="22" data-posisi-risiko="5">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell low-to-moderate" data-id="23" data-posisi-risiko="10">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell moderate" data-id="24" data-posisi-risiko="15">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell high" data-id="25" data-posisi-risiko="20">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
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
          <div class="alert alert-info">Strategi Perlakuan</div>
          <div class="table-responsive scrollbar">
            <table class="table table-strategi">
              <thead>
                <tr>
                  <th rowspan="2" class="left-align">Kode & Peristiwa Risiko</th>
                  <th colspan="2" class="white-space-nowrap">Posisi Risiko</th>
                  <th rowspan="2" class="left-align">Strategi Perlakuan Risiko (Rencana)</th>
                  <th rowspan="2" class="white-space-nowrap">Tenggat Waktu</th>
                </tr>
                <tr>
                  <th>CRE</th>
                  <th>RRE</th>
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
                <th class="white-space-nowrap">TCK terpengaruh</th>
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
  </div>

  <!--============================ Loss Event Data ============================-->
  <div class="col-12">
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
          <h3>Loss Event Data</h3>
        </div>
        <hr class="mb-2 mt-xxl-5">
      </div>
      <div class="card-body pt-0">
        <div class="table-responsive scrollbar">
          <table class="table">
            <thead>
              <tr>
                <th rowspan="2">Tanggal Kejadian</th>
                <th rowspan="2">Peristiwa Kerugian</th>
                <th rowspan="2">Jenis Risiko</th>
                <th colspan="2" class="no-sort white-space-nowrap py-1">
                  <div class="alert alert-danger fw-semibold text-center py-2 my-0">
                    Nilai Kerugian
                  </div>
                </th>
                <th rowspan="2">Rekomendasi Perbaikan yang perlu Ditindaklanjuti</th>
                <th rowspan="2">Unit Penanggung Jawab</th>
              </tr>
              <tr>
                <th class="no-sort text-center py-2">Finansial (IDR)</th>
                <th class="no-sort text-center py-2">Non Finansial</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>04/11/2024</td>
                <td>Lorem ipsum odor amet, consectetuer adipiscing elit. Natoque habitant habitant donec sodales
                  porttitor dictumst.</td>
                <td>Risiko Pendidikan</td>
                <td>5.000.000.000</td>
                <td>2.000.000.000</td>
                <td>Consectetur mauris praesent risus condimentum libero diam aenean.</td>
                <td>LKPG</td>
              </tr>
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

@section('scripts')
<script src="/vendors/chart-js/chart.min.js"></script>
<script type="text/javascript">
const refreshSummary = () => {
  // Summary
  // if (dashboardData.tck_c >= 0) {
  //   $('#kpi-card .changes-summary').html(`<span class="up-label">15%</span> Since last month`);
  // } else {
  //   $('#kpi-card .changes-summary').html(`<span class="down-label">15%</span> Since last month`);
  // }
  // $('#kpi-card .last-changes').html(dashboardData.tck_date);

  if (dashboardData.rpr_c >= 0) {
    $('#rpr-card .changes-summary').html(`<span class="up-label">${dashboardData.rpr_c}%</span> Since last month`);
  } else {
    $('#rpr-card .changes-summary').html(`<span class="down-label">${dashboardData.rpr_c}%</span> Since last month`);
  }
  $('#rpr-card .last-changes').html(dashboardData.rpr_date);

  if (dashboardData.jkk_c >= 0) {
    $('#jkk-card .changes-summary').html(`<span class="up-label">${dashboardData.jkk_c}</span> Since last month`);
  } else {
    $('#jkk-card .changes-summary').html(`<span class="down-label">${dashboardData.jkk_c}</span> Since last month`);
  }
  $('#jkk-card .last-changes').html(dashboardData.jkk_date);
  $('#jkk-card #jkk-counter').html(1);

  if (dashboardData.tkmru_c >= 0) {
    $('#tkmru-card .changes-summary').html(`<span class="up-label">${dashboardData.tkmru_c}</span> Since last month`);
  } else {
    $('#tkmru-card .changes-summary').html(
      `<span class="down-label">${dashboardData.tkmru_c}</span> Since last month`);
  }
  $('#tkmru-card .last-changes').html(dashboardData.tkmru_date);
  $('#tkmru-card #tkmru-counter').html(dashboardData.tkmru);
  $('#tkmru-card #tkmru-notes').html(dashboardData.tkmru_notes);

  // Peta Risiko Inheren dan Residual
  Object.keys(dashboardData.risk_maps).forEach((key) => {
    const riskMap = dashboardData.risk_maps[key];
    const className = riskMap.level_risiko.toLowerCase().replaceAll(' ', '-');
    $(`#prir-card .data-cell[data-id="${key}"]`).addClass(className).attr('data-posisi-risiko', riskMap
      .nilai_risiko);
  });

  $('#prir-card .table tbody').html('');
  if (!dashboardData.prir.length) {
    $('#prir-card .table tbody').append(`
            <tr>
                <td colspan="5" class="dt-empty">No data available</td>
            </tr>
        `);
  }
  dashboardData.prir.forEach((prir) => {
    $('#prir-card .table tbody').append(`
        <tr>
          <td class="left-align" onclick="showRisiko('${prir.kode}')">
            <span class="btn-link">${prir.kode}: ${prir.peristiwa}</span>
          </td>
          <td>${prir.ire}</td>
          <td>${prir.rre}</td>
          <td class="left-align">${prir.strategi}</td>
          <td>${prir.tenggat}</td>
        </tr>
    `);
    if (!$(`#prir-card .data-cell[data-posisi-risiko="${prir.ire}"]`).data('kode-peristiwa')) {
      $(`#prir-card .data-cell[data-posisi-risiko="${prir.ire}"]`).data('kode-peristiwa', []);
    }

    $(`#prir-card .data-cell[data-posisi-risiko="${prir.ire}"]`).data('kode-peristiwa').push(prir.kode);
    $(`#prir-card .data-cell[data-posisi-risiko="${prir.ire}"]`).data('has-inherent', true);

    if (!$(`#prir-card .data-cell[data-posisi-risiko="${prir.rre}"]`).data('kode-peristiwa')) {
      $(`#prir-card .data-cell[data-posisi-risiko="${prir.rre}"]`).data('kode-peristiwa', []);
    }

    $(`#prir-card .data-cell[data-posisi-risiko="${prir.rre}"]`).data('kode-peristiwa').push(
      `<span class="residual">${prir.kode}</span>`);
    $(`#prir-card .data-cell[data-posisi-risiko="${prir.ire}"]`).data('has-residual', true);
  });

  const cells = $('#prir-card .data-cell');
  cells.each((index, cell) => {
    const kodePeristiwa = $(cell).data('kode-peristiwa');
    if (kodePeristiwa && kodePeristiwa.length > 0) {
      $(cell).find('.kode-peristiwa').html(kodePeristiwa.filter((item,
        index) => kodePeristiwa.indexOf(item) === index).join(', '));
      $(cell).find('.posisi-risiko').html($(cell).data('posisi-risiko'));

      if (!$(cell).data('has-inherent')) {
        $(cell).addClass('residual');
      } else {
        $(cell).removeClass('residual');
      }
    }
  });

  $('#prir-card .card-subtitle').html(`${dashboardData.prir_date}`);

  // Peta Risiko Saat Ini
  Object.keys(dashboardData.risk_maps).forEach((key) => {
    const riskMap = dashboardData.risk_maps[key];
    const className = riskMap.level_risiko.toLowerCase().replaceAll(' ', '-');
    $(`#prsi-card .data-cell[data-id="${key}"]`).addClass(className).attr('data-posisi-risiko', riskMap
      .nilai_risiko);
  });

  $('#prsi-card .table tbody').html('');
  if (!dashboardData.prsi.length) {
    $('#prsi-card .table tbody').append(`
            <tr>
                <td colspan="5" class="dt-empty">No data available</td>
            </tr>
        `);
  }
  dashboardData.prsi.forEach((prsi) => {
    $('#prsi-card .table tbody').append(`
            <tr>
                <td class="left-align" onclick="showRisiko('${prsi.kode}')">
                  <span class="btn-link">${prsi.kode}: ${prsi.peristiwa}</span>
                </td>
                <td>${prsi.cre || '-'}</td>
                <td>${prsi.rre}</td>
                <td class="left-align">${prsi.strategi}</td>
                <td>${prsi.tenggat}</td>
            </tr>
        `);
    if (prsi.cre) {
      if (!$(`#prsi-card .data-cell[data-posisi-risiko="${prsi.cre}"]`).data('kode-peristiwa')) {
        $(`#prsi-card .data-cell[data-posisi-risiko="${prsi.cre}"]`).data('kode-peristiwa', []);
      }
      $(`#prsi-card .data-cell[data-posisi-risiko="${prsi.cre}"]`).data('kode-peristiwa').push(prsi.kode);
    }

    // if (prsi.rre) {
    //   if (!$(`#prsi-card .data-cell[data-posisi-risiko="${prsi.rre}"]`).data('kode-peristiwa')) {
    //     $(`#prsi-card .data-cell[data-posisi-risiko="${prsi.rre}"]`).data('kode-peristiwa', []);
    //   }

    //   $(`#prsi-card .data-cell[data-posisi-risiko="${prsi.rre}"]`).data('kode-peristiwa').push(prsi.kode);
    // }
  });

  const cells2 = $('#prsi-card .data-cell');
  cells2.each((index, cell) => {
    const kodePeristiwa = $(cell).data('kode-peristiwa');
    if (kodePeristiwa && kodePeristiwa.length > 0) {
      $(cell).find('.kode-peristiwa').html(kodePeristiwa.filter((item,
        index) => kodePeristiwa.indexOf(item) === index).join(', '));
      $(cell).find('.posisi-risiko').html($(cell).data('posisi-risiko'));
    }
  });

  $('#prsi-card .card-subtitle').html(`${dashboardData.prsi_date}`);

  // TOP RISK
  $('#top-risk-card .table tbody').html('');
  if (!dashboardData.top_risk.length) {
    $('#top-risk-card .table tbody').append(`
            <tr>
                <td colspan="8" class="dt-empty">No data available</td>
            </tr>
        `);
  }
  dashboardData.top_risk.forEach((risk) => {
    $('#top-risk-card .table tbody').append(`
            <tr>
                <td>${risk.peristiwa}</td>
                <td>${risk.deskripsi}</td>
                <td>${risk.jenis_risiko}</td>
                <td class="level_risiko text-center">
                    <div class="badge ${risk.warna_tingkat_risiko}">${risk.tingkat_risiko}</div>
                </td>
                <td>${risk.tck_terpengaruh}</td>
                <td>${risk.kri}</td>
                <td class="text-center">
                    <div class="status-container ${risk.status_kri}">
                        <div class="status-green"></div>
                        <div class="status-yellow"></div>
                        <div class="status-red"></div>
                    </div>
                </td>
                <td>${risk.risk_owner}</td>
            </tr>
        `);
  });

  // Lost Event Data
  $('#led-card .table tbody').html('');
  if (!dashboardData.led.length) {
    $('#led-card .table tbody').append(`
            <tr>
                <td colspan="7" class="dt-empty">No data available</td>
            </tr>
        `);
  }
  dashboardData.led.forEach((led) => {
    $('#led-card .table tbody').append(`
            <tr>
                <td>${led.tanggal_kejadian}</td>
                <td>${led.peristiwa_kerugian}</td>
                <td>${led.jenis_risiko}</td>
                <td>${led.nilai_kerugian_finansial}</td>
                <td>${led.nilai_kerugian_non_finansial}</td>
                <td>${led.rekomendasi_perbaikan.length > 1 ? `
                    <ol>${led.rekomendasi_perbaikan.map(item => `<li>${item}</li>`).join('')}</ol>
                    ` : led.rekomendasi_perbaikan.join('')}</td>
                <td>${led.unit_penanggung_jawab}</td>
            </tr>
        `);
  });
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
  $('.input-filter-container :input').on('change', function() {
    console.log('change');
    $(this).parents('form').submit();
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

$(document).ready(function() {
  initKpiChart();
  initRprChart();
  refreshSummary();
  initInputFilter();
});
</script>

@endsection