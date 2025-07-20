@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <div class="bg-info-subtle p-2 rounded-4">
          <div class="lead__icon">
            <div class="svg-icon svg-icon-2x svg-icon-info">
              @include('partials.icon-layer')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">Daftar Periode</div>
          <h2>Risk Register Divisi</h2>
        </div>
      </div>
      <div class="card-body dt-header-true">
        <div class="table-responsive-sm scrollbar">
          <table class="table table-hover dataTable" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="tahun">Tahun</th>
                <th class="sort text-center" data-sort="status">Status</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($periodes as $index => $periode)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="tahun">{{ $periode->tahun }}</td>
                <td class="status text-center">
                  <figure class="badge {{ $periode->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                    {{ $periode->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}
                  </figure>
                </td>
                <td class="white-space-nowrap">
                  {{--
                  <a href="javascript:void(0)" class="btn-input-icon" data-bs-toggle="tooltip" title="Pengaturan Periode Divisi" onclick="openPeriodeUnitSettings({{ $periode->id }})">
                    <span class="bx bx-cog"></span>
                  </a>
                  --}}
                  <a href="{{ route('risk-register-unit.periods.show', ['period' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="View">
                    <span class="bx bx-show"></span>
                  </a>
                  <a href="{{ route('risk-register-unit.index', ['pid' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Risk Register">
                    <span class="bx bx-list-check"></span>
                  </a>
                  <a href="{{ route('risk-register-unit.monitorings.index', ['period' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Monitoring">
                    <span class="bx bx-radar"></span>
                  </a>
                  <a href="{{ route('unit-led.index-by-unit', ['unitId' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Loss Event">
                    <span class="bx bx-dock-bottom"></span>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
