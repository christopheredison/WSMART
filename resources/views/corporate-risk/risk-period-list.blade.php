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
        <div class="row g-2 mb-4">
          <div class="col-md-4">
            <label class="form-label d-none" for="periode_filter">Filter Periode</label>
            <select id="periode_filter" class="form-select select2">
              @foreach($periodes as $p)
                <option value="{{ $p->id }}" {{ ($selectedPeriode && $selectedPeriode->id == $p->id) ? 'selected' : '' }}>
                  {{ $p->tahun }} {{ $p->status == 'active' ? '(Aktif)' : '' }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label d-none" for="month_filter">Filter Bulan</label>
            <select id="month_filter" class="form-select select2">
              @php
                $months = [
                  1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                  5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                  9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
              @endphp
              @foreach($months as $num => $name)
                <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>
                  {{ $name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="table-responsive-sm">
          <table class="table table-hover" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="unit">Korporat</th>
                <th class="sort" data-sort="tahun">Tahun</th>
                <th class="sort text-center" data-sort="status_risiko">Status Risiko</th>
                <th class="sort text-center" data-sort="status_monitoring">Status Monitoring</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            @php
                $monMonth = $selectedMonth ?? date('n');
                $monQuarter = ceil($monMonth / 3);
            @endphp
            <tbody class="list" id="bulk-select-body">
              @forelse ($dataToDisplay as $index => $item)
                @php
                    $unit = $item['unit'];
                    $periode = $item['periode'];
                @endphp
                <tr>
                  <td class="index-number">{{ $index + 1 }}</td>
                  <td class="unit">{{ $unit }}</td>
                  <td class="tahun">{{ $periode->tahun }}</td>
                  <td class="text-center">
                    {!! $item['risk_status_html'] !!}
                  </td>
                  <td class="text-center">
                    {!! $item['mon_status_html'] !!}
                  </td>
                  <td class="white-space-nowrap">
                    <a href="{{ route('corporate-risk.periods.show', ['period' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="View">
                      <span class="bx bx-show"></span>
                    </a>
                    <a href="{{ route('corporate-risk.index', ['pid' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Risk Register">
                      <span class="bx bx-list-check"></span>
                    </a>
                    <a href="{{ route('corporate-risk.monitorings.index', ['period' => $periode->id, 'quarter' => $monQuarter, 'month' => $monMonth]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Monitoring">
                      <span class="bx bx-radar"></span>
                    </a>
                    <a href="{{ route('corporate-led.index', ['periode' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                      title="Loss Event">
                      <span class="bx bx-dock-bottom"></span>
                    </a>
                  </td>
                </tr>
              @empty
              <tr>
                <td colspan="6" class="text-center">Tidak ada data untuk ditampilkan.</td>
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
    $("body").tooltip({ selector: '[data-bs-toggle=tooltip]' });

    let table = $('#example').DataTable({
      "paging": true,
      "info": true,
      "searching": true,
      "columnDefs": [
        {
          "searchable": false,
          "orderable": false,
          "targets": 0
        }
      ],
      "order": [[1, 'asc']],
      "layout": {
        "topEnd": {
            "search": {
                "placeholder": 'Search...'
            }
        },
      }
    });

    table.on('order.dt search.dt', function () {
      let i = 1;
      table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
        this.data(i++);
      });
    }).draw();

    $('#periode_filter, #month_filter').on('change', function() {
      const pid = $('#periode_filter').val();
      const month = $('#month_filter').val();

      const url = new URL(window.location.href);
      if(pid) url.searchParams.set('pid', pid);
      if(month) url.searchParams.set('month', month);

      window.location.href = url.toString();
    });
  });
</script>
@endpush
