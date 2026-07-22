@extends('layouts.default')

@section('dashboard')
<div class="row justify-center g-3 g-xxl-2 mb-5">
  <div class="col d-flex align-items-center gap-3">
    <div class="bg-info-subtle rounded-3 p-2">
      <div class="lead__icon">
        <span class="svg-icon svg-icon-2x svg-icon-info">
          @include('partials.icon-piechart2')
        </span>
      </div>
    </div>
    <h2 class="mb-0">Profil Risiko</h2>
  </div>
  <div class="col-auto input-selector-rounded">
    <form class="input-filter-container" action="{{ url()->current() }}">
      <select name="periode_id" id="periode_selector" class="form-select select2 js-select-hide-search">
        <option selected disabled>Periode</option>
        @foreach ($periodes as $periode)
        <option value="{{ $periode->id }}" {{ $periode->id == request()->periode_id ? 'selected' : '' }}>
          {{ $periode->tahun }}</option>
        @endforeach
      </select>
    </form>
  </div>
</div>
<div class="dashboard-header p-3 mb-4 d-none">
  <div class="row">
    <div class="col-md-2">
      <div class="p-3">
        <h4 class="fs-1 fw-bold">Profil Risiko</h4>
        <small>Statistik per tanggal {{ now()->format('d/m/Y') }}</small>
      </div>
    </div>
    <div class="col-md-10">
      <div class="row">
        <div class="col-md-3 mb-3 mb-md-0">
          <div class="card" id="tck-card">
            <div class="card-header py-3 d-flex align-items-center">
              <div class="me-2"
                style="background-color: #fcf8e8;color: #eccf4a;padding: .3125rem .6rem; border-radius: 5px; display:inline-block">
                <i class="fa fa-arrow-up-from-bracket"></i>
              </div>
              <h6 class="mb-0 d-inline-block" style="width: calc(100% - 50px)">
                % Capaian TCK
              </h6>
            </div>
            <div class="card-body">
              <div id="tck-chart" style="height: 90px; width: 90px; margin: 0 auto;"></div>
            </div>
            <div class="card-footer">
              <div class="d-flex flex-between-center" style="font-size:8px">
                <div class="text-500 changes-summary"></div>
                <div class="text-500 last-changes"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
          <div class="card" id="rpr-card">
            <div class="card-header py-3 d-flex align-items-center">
              <div class="me-2"
                style="background-color: #e9f3ff;color: #346ae0;padding: .3125rem .6rem; border-radius: 5px; display:inline-block">
                <i class="fa-solid fa-arrow-down-up-across-line"></i>
              </div>
              <h6 class="mb-0 d-inline-block" style="width: calc(100% - 50px)">
                % Realisasi Perlakuan Risiko
              </h6>
            </div>
            <div class="card-body">
              <div id="rpr-chart" style="height: 90px; width: 90px; margin: 0 auto;"></div>
            </div>
            <div class="card-footer">
              <div class="d-flex flex-between-center" style="font-size:8px">
                <div class="text-500 changes-summary"></div>
                <div class="text-500 last-changes"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
          <div class="card" id="jkk-card">
            <div class="card-header py-3 d-flex align-items-center">
              <div class="me-2"
                style="background-color: #fcf2e8;color: #e78b2f;padding: .3125rem .6rem; border-radius: 5px; display:inline-block">
                <i class="fa fa-calculator"></i>
              </div>
              <h6 class="mb-0 d-inline-block" style="width: calc(100% - 50px)">
                Jumlah Kejadiian Kerugian
              </h6>
            </div>
            <div class="card-body">
              <div id="jkk-chart" style="height: 90px;" class="d-flex align-items-center justify-content-center">
                <h6 class="fs-5 fw-bold" id="jkk-counter"></h6>
              </div>
            </div>
            <div class="card-footer">
              <div class="d-flex flex-between-center" style="font-size:8px">
                <div class="text-500 changes-summary"></div>
                <div class="text-500 last-changes"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card" id="tkmru-card">
            <div class="card-header py-3 d-flex align-items-center">
              <div class="me-2"
                style="background-color: #fff2f1;color: #f14c41;padding: .3125rem .6rem; border-radius: 5px; display:inline-block">
                <i class="fa fa-gauge-high"></i>
              </div>
              <h6 class="mb-0 d-inline-block" style="width: calc(100% - 50px)">
                Capaian TKMRU
              </h6>
            </div>
            <div class="card-body">
              <div id="tkmru-chart" style="height: 90px;"
                class="d-flex flex-column align-items-center justify-content-center">
                <h3 class="fs-5 fw-bold" id="tkmru-counter"></h3>
                <h4 class="fw-bold" id="tkmru-notes" style="font-size: .6em"></h4>
              </div>
            </div>
            <div class="card-footer">
              <div class="d-flex flex-between-center" style="font-size:8px">
                <div class="text-500 changes-summary"></div>
                <div class="text-500 last-changes"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

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
                  <div class="data-cell" data-id="1">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="2">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="3">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="4">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="5">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell" data-id="6">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="7">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="8">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="9">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="10">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell" data-id="11">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="12">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="13">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="14">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="15">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell" data-id="16">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="17">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="18">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="19">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="20">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell" data-id="21">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="22">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="23">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="24">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="25">
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
  <!--============================ Peta Risiko Inheren dan Residual ============================-->
  <div class="col-lg-6">
    <div class="card" id="prsi-card">
      <div class="card-header border-0 pb-0 d-flex flex-between-center">
        <h3 class="h4">Peta Risiko Inheren dan Residual</h3>
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
                  <div class="data-cell" data-id="1">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="2">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="3">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="4">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="5">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell" data-id="6">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="7">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="8">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="9">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="10">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell" data-id="11">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="12">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="13">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="14">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="15">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell" data-id="16">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="17">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="18">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="19">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="20">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
              </tr>
              <tr>
                <td>
                  <div class="data-cell" data-id="21">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="22">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="23">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="24">
                    <div class="kode-peristiwa"></div>
                    <div class="posisi-risiko"></div>
                  </div>
                </td>
                <td>
                  <div class="data-cell" data-id="25">
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
        <table class="table">
          <tbody>
            <tr>
              <th>Kategori Risiko</th>
              <td></td>
            </tr>
            <tr>
              <th>Jenis Risiko</th>
              <td></td>
            </tr>
            <tr>
              <th>Peristiwa Risiko</th>
              <td></td>
            </tr>
            <tr>
              <th>Deskripsi Peristiwa Risiko</th>
              <td></td>
            </tr>
            <tr>
              <th>Target Capaian Kinerja</th>
              <td></td>
            </tr>
            <tr>
              <th>Rencana Kegiatan</th>
              <td></td>
            </tr>
            <tr>
              <th>Area Dampak</th>
              <td></td>
            </tr>
            <tr>
              <th>Deskripsi Dampak</th>
              <td></td>
            </tr>
            <tr>
              <th>Skala Dampak Inherent</th>
              <td></td>
            </tr>
            <tr>
              <th>Skala Probabilitas Inherent</th>
              <td></td>
            </tr>
            <tr>
              <th>Nilai Risiko Inherent</th>
              <td></td>
            </tr>
            <tr>
              <th>Level Risiko</th>
              <td></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endsection
  @section('scripts')
  <script type="text/javascript">
  const dashboardData = @json($dataDashboard);

  const refreshSummary = () => {
    // Summary
    if (dashboardData.tck_c >= 0) {
      $('#tck-card .changes-summary').html(`<span class="up-label">${dashboardData.tck_c}%</span> Since last month`);
    } else {
      $('#tck-card .changes-summary').html(
        `<span class="down-label">${dashboardData.tck_c}%</span> Since last month`);
    }
    $('#tck-card .last-changes').html(dashboardData.tck_date);

    if (dashboardData.rpr_c >= 0) {
      $('#rpr-card .changes-summary').html(`<span class="up-label">${dashboardData.rpr_c}%</span> Since last month`);
    } else {
      $('#rpr-card .changes-summary').html(
        `<span class="down-label">${dashboardData.rpr_c}%</span> Since last month`);
    }
    $('#rpr-card .last-changes').html(dashboardData.rpr_date);

    if (dashboardData.jkk_c >= 0) {
      $('#jkk-card .changes-summary').html(`<span class="up-label">${dashboardData.jkk_c}</span> Since last month`);
    } else {
      $('#jkk-card .changes-summary').html(`<span class="down-label">${dashboardData.jkk_c}</span> Since last month`);
    }
    $('#jkk-card .last-changes').html(dashboardData.jkk_date);
    $('#jkk-card #jkk-counter').html(dashboardData.jkk);

    if (dashboardData.tkmru_c >= 0) {
      $('#tkmru-card .changes-summary').html(
        `<span class="up-label">${dashboardData.tkmru_c}</span> Since last month`);
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
          <td colspan="5" class="text-center">No data available</td>
        </tr>
      `);
    }
    dashboardData.prir.forEach((prir) => {
      $('#prir-card .table tbody').append(`
        <tr>
            <td class="left-align clickable-show-risiko" style="cursor:pointer" onclick="showRisiko('${prir.kode}')">${prir.kode}: ${prir.peristiwa}</td>
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

      $(`#prir-card .data-cell[data-posisi-risiko="${prir.rre}"]`).data('kode-peristiwa').push(`<span class="residual">${prir.kode}</span>`);
      $(`#prir-card .data-cell[data-posisi-risiko="${prir.ire}"]`).data('has-residual', true);
    });

    const cells = $('#prir-card .data-cell');
    cells.each((index, cell) => {
      const kodePeristiwa = $(cell).data('kode-peristiwa');
      if (kodePeristiwa && kodePeristiwa.length > 0) {
        $(cell).find('.kode-peristiwa').html(kodePeristiwa.filter((item,
          index) => kodePeristiwa.indexOf(item) === index).join(', '));
        $(cell).find('.posisi-risiko').html($(cell).data('posisi-risiko'));
      }

      if (!$(cell).data('has-inherent')) {
        $(cell).addClass('residual');
      } else {
        $(cell).removeClass('residual');
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
              <td colspan="5" class="text-center">No data available</td>
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
            <td colspan="8" class="text-center">No data available</td>
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
          <td colspan="7" class="text-center">No data available</td>
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
            ` : led.rekomendasi_perbaikan.join('')}
          </td>
          <td>${led.unit_penanggung_jawab}</td>
        </tr>
      `);
    });
  }

  const initTckChart = () => {
    // Initialize the echarts instance based on the prepared dom
    var myChart = echarts.init(document.getElementById('tck-chart'));
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
          value: dashboardData.tck,
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
          formatter: '{value}%',
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
          value: dashboardData.rpr,
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
          formatter: '{value}%',
          valueAnimation: true
        },
        animationDuration: 3000
      }]
    };

    // Display the chart using the configuration items and data just specified.
    myChart.setOption(option);
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
    initTckChart();
    initRprChart();
    refreshSummary();
    initInputFilter();
  });
  </script>
  @endsection