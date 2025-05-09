@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-4 flex-between-center mb-7">
  <div class="col-12 col-md-auto d-flex align-items-center gap-3">
    <div class="bg-danger-subtle p-2 rounded-4">
      <div class="lead__icon">
        <div class="svg-icon svg-icon-3x svg-icon-danger">
          @include('partials.icon-compass')
        </div>
      </div>
    </div>
    <div class="d-block">
      <div class="ff-preheading">Input Data</div>
      <h2>Strategi Risiko</h2>
    </div>
  </div>
  <div class="col-12 col-md-auto">
    <a class="btn btn-outline-info btn-arrow-right" href="{{ route('strategi-risiko.create') }}" type="button">
      <span class="ms-1">Manage Strategi Risiko</span>
    </a>
  </div>
</div>
<div class="row g-4">
  <div class="col-md-6 col-lg-3">
    <div class="card bg-info shadow text-white text-center border-0 py-3 py-md-0">
      <div class="card-header border-0 pb-0">
        <h4 class="fw-bold">Total Anggaran Unit Kerja <small>(Rp)</small></h4>
      </div>
      <div class="card-body d-flex flex-column">
        <h1 class="my-auto">
          {{ number_format($data->total_anggaran_unit ?? NULL, 0, ',', '.') }}
        </h1>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card text-center py-3 py-md-0">
      <div class="card-header border-0 pb-0">
        <h4 class="fw-bold">Risk Capacity</h4>
      </div>
      <div class="card-body d-flex flex-column">
        <h1>100<span class="fw-medium">%</span></h1>
        <h6 class="mb-0">
          Rp {{ number_format($data->total_anggaran_unit ?? NULL, 0, ',', '.') }}</h6>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card text-center py-3 py-md-0">
      <div class="card-header border-0 pb-0">
        <h4 class="fw-bold">Risk Tolerance</h4>
      </div>
      <div class="card-body d-flex flex-column">
        <h1>{{ $data->persentase_risk_tolerance ?? NULL }}<span class="fw-medium">%</span></h1>
        <h6 class="mb-0">
          Rp {{ number_format($data->value_risk_tolerance ?? NULL, 0, ',', '.') }}</h6>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card text-center py-3 py-md-0">
      <div class="card-header border-0 pb-0">
        <h4 class="fw-bold">Risk Appetite</h4>
      </div>
      <div class="card-body d-flex flex-column">
        <h1>{{ $data->persentase_risk_appetite ?? NULL }}<span class="fw-medium">%</span></h1>
        <h6 class="mb-0">
          Rp {{ number_format($data->value_risk_appetite ?? NULL, 0, ',', '.') }}</h6>
      </div>
    </div>
  </div>
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-4">
          <div class="bg-info-subtle text-info rounded-pill p-1">
            <div class="d lead__icon lead__icon_sm fs-5 fw-bold fs-5 fw-bold">%</div>
          </div>
          <h4 class="fw-bold mb-0">Risk Appetite Sikap Terhadap Risiko</h5>
        </div>
        <canvas id="riskAppetiteChart"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-sm">
      <div class="card-header d-flex align-items-center gap-2">
        <div class="bg-soft-danger text-danger rounded-pill p-1">
          <div class="d lead__icon lead__icon_sm fs-5 fw-bold fs-5 fw-bold">%</div>
        </div>
        <h4 class="fw-bold">Risk Appetite Sikap Risiko</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <thead>
            <tr>
              <th class="col-5">Sikap Risiko</th>
              <th class="col-2 text-center">%</th>
              <th class="col-5 text-end">IDR</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Konservatif</td>
              <td class="text-center">
                {{ number_format($data->persentase_risk_appetite_konservatif ?? NULL, 0, ',', '.') }}</td>
              <td class="text-end">{{ number_format($data->value_risk_appetite_konservatif ?? NULL, 0, ',', '.') }}
              </td>
            </tr>
            <tr>
              <td>Moderat</td>
              <td class="text-center">
                {{ number_format($data->persentase_risk_appetite_moderat ?? NULL, 0, ',', '.') }}</td>
              <td class="text-end">{{ number_format($data->value_risk_appetite_moderat ?? NULL, 0, ',', '.') }}
              </td>
            </tr>
            <tr>
              <td>Agresif</td>
              <td class="text-center">
                {{ number_format($data->persentase_risk_appetite_agresif ?? NULL, 0, ',', '.') }}</td>
              <td class="text-end">{{ number_format($data->value_risk_appetite_agresif ?? NULL, 0, ',', '.') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card card-sm">
      <div class="card-header d-flex align-items-center gap-2">
        <div class="bg-warning-subtle text-warning rounded-pill p-1">
          <div class="d lead__icon lead__icon_sm fs-5 fw-bold fs-5 fw-bold">%</div>
        </div>
        <h4 class="fw-bold">Risk Tolerance Sikap Risiko</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <thead>
            <tr>
              <th>Sikap Risiko</th>
              <th class="text-center">%</th>
              <th class="text-end">IDR</th>
              <th class="text-end">Risk Tolerance AK</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Konservatif</td>
              <td class="text-center">
                {{ number_format($data->persentase_risk_tolerance_konservatif ?? NULL, 0, ',', '.') }}</td>
              <td class="text-end">{{ number_format($data->value_risk_tolerance_konservatif ?? NULL, 0, ',', '.') }}
              </td>
              <td class="text-end">
                {{ isset($data) ? number_format(($data->value_risk_tolerance_konservatif ?? 0) - ($data->value_risk_appetite_konservatif ?? 0), 0, ',', '.') : 'N/A' }}
              </td>
            </tr>
            <tr>
              <td>Moderat</td>
              <td class="text-center">
                {{ number_format($data->persentase_risk_tolerance_moderat ?? NULL, 0, ',', '.') }}</td>
              <td class="text-end">{{ number_format($data->value_risk_tolerance_moderat ?? NULL, 0, ',', '.') }}
              </td>
              <td class="text-end">
                {{ isset($data) ? number_format(($data->value_risk_tolerance_moderat ?? 0) - ($data->value_risk_appetite_moderat ?? 0), 0, ',', '.') : 'N/A' }}
              </td>
            </tr>
            <tr>
              <td>Agresif</td>
              <td class="text-center">
                {{ number_format($data->persentase_risk_tolerance_agresif ?? NULL, 0, ',', '.') }}</td>
              <td class="text-end">{{ number_format($data->value_risk_tolerance_agresif ?? NULL, 0, ',', '.') }}
              </td>
              <td class="text-end">
                {{ isset($data) ? number_format(($data->value_risk_tolerance_agresif ?? 0) - ($data->value_risk_appetite_agresif ?? 0), 0, ',', '.') : 'N/A' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-sm">
      <div class="card-header d-flex align-items-center gap-2">
        <div class="bg-success-subtle text-success rounded-pill p-1">
          <div class="d lead__icon lead__icon_sm fs-5 fw-bold fs-5 fw-bold">%</div>
        </div>
        <h4 class="fw-bold">Risk Limit</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th>Konservatif</th>
              <th class="text-center">%</th>
            </tr>
          <tbody>
            @foreach($riskLimitKonservatif as $riskLimit)
            <tr>
              <td>{{ $riskLimit->jenisRisiko->title }}</td>
              <td class="text-center">{{ $riskLimit->persentase_limit }}</td>
            </tr>
            @endforeach
          </tbody>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-sm">
      <div class="card-header d-flex align-items-center gap-2">
        <div class="bg-success-subtle text-success rounded-pill p-1">
          <div class="d lead__icon lead__icon_sm fs-5 fw-bold fs-5 fw-bold">%</div>
        </div>
        <h4 class="fw-bold">Risk Limit</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th>Moderat</th>
              <th class="text-center">%</th>
            </tr>
          <tbody>
            @foreach($riskLimitModerat as $riskLimit)
            <tr>
              <td>{{ $riskLimit->jenisRisiko->title }}</td>
              <td class="text-center">{{ $riskLimit->persentase_limit }}</td>
            </tr>
            @endforeach
          </tbody>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-sm">
      <div class="card-header d-flex align-items-center gap-2">
        <div class="bg-success-subtle text-success rounded-pill p-1">
          <div class="d lead__icon lead__icon_sm fs-5 fw-bold fs-5 fw-bold">%</div>
        </div>
        <h4 class="fw-bold">Risk Limit</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm table-hover mb-0">
          <thead>
            <tr>
              <th>Agresif</th>
              <th class="text-center">%</th>
            </tr>
          </thead>
          <tbody>
            @foreach($riskLimitAgresif as $riskLimit)
            <tr>
              <td>{{ $riskLimit->jenisRisiko->title }}</td>
              <td class="text-center">{{ $riskLimit->persentase_limit }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>




@endsection
@section('scripts')
<script src="vendors/list.js/list.min.js"></script>
<script src="/vendors/chart-js/chart.min.js"></script>
@include('chartjs.risk-appetite')
@endsection