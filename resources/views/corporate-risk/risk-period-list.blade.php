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
          <h2>Risk Register Korporat</h2>
        </div>
      </div>
      <div class="card-header border-bottom">
          <div class="d-flex align-items-center gap-3">
              <h6 class="mb-0">Keterangan :</h6>
              <div class="d-flex gap-3">
                  @foreach($tableLegend as $legend)
                  <div class="d-flex align-items-center gap-1">
                      {!! $legend['icon'] !!}
                      <span>{{ $legend['label'] }}</span>
                  </div>
                  @endforeach
              </div>
          </div>
      </div>
      <div class="card-body dt-header-true">
        <div class="table-responsive-sm">
          <table class="table table-hover" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="unit">Korporat</th>
                <th class="sort" data-sort="tahun">Tahun</th>
                <th class="sort text-center" data-sort="status">Status</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @forelse ($dataToDisplay as $index => $item)
              @php

                    $unit = $item['unit'];
                    $periode = $item['periode'];
                @endphp
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="unit">{{ $unit->name }}</td>
                <td class="tahun">{{ $periode->tahun }}</td>
                <td class="status text-center">
                  <figure class="badge {{ $periode->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                    {{ $periode->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}
                  </figure>
                </td>
                <td class="white-space-nowrap">
                  <a href="{{ route('corporate-risk.periods.show', ['period' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="View">
                    <span class="bx bx-show"></span>
                  </a>
                  <a href="{{ route('corporate-risk.index', ['pid' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Risk Register">
                    <span class="bx bx-list-check"></span>
                  </a>
                  <a href="{{ route('risk-register-unit.monitorings.index', ['period' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Monitoring">
                    <span class="bx bx-radar"></span>
                  </a>
                  <a href="{{ route('unit-led.index-by-periode', ['periode' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Loss Event">
                    <span class="bx bx-dock-bottom"></span>
                  </a>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center">Tidak ada data untuk ditampilkan.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    let table = $('#example').DataTable({
      "paging": true,
      "info": true,
      "searching": true,
      "layout": {
        "topEnd": {
            "search": {
                "placeholder": 'Search...'
            }
        },
      }
    });
  });
</script>
@endpush