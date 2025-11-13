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
          <h2>Risk Register Anak Perusahaan</h2>
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
        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label d-none" for="unit_status_filter">Filter Status Anak Perusahaan</label>
            <select id="unit_status_filter" class="form-select select2">
              <option value="Valid">Valid</option>
              <option value="Expired">Expired</option>
            </select>
          </div>
          @if($apAdmin)
            <div class="col-md-4">
              <label class="form-label d-none" for="unit_id_filter">Filter Anak Perusahaan</label>
              <select id="unit_id_filter" class="form-select select2">
                <option value="">Semua Anak Perusahaan</option>
                @foreach ($units as $id => $name)
                  <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
          @endif
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
        </div>
        <div class="table-responsive-sm">
          <table class="table table-hover" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="unit">Anak Perusahaan</th>
                <th class="sort" data-sort="tahun">Tahun</th>
                <th class="sort text-center" data-sort="risk_count">Total Risiko</th>
                <th class="sort text-center" data-sort="unit_status">Status Anak Perusahaan</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @forelse ($dataToDisplay as $index => $item)
              @php
                  $unit = $item['unit'];
                  $periode = $item['periode'];
                  $unitStatus = $item['unit_status'] ?? 'active';
              @endphp
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="unit">{{ $unit->name }}</td>
                <td class="tahun">{{ $periode->tahun }}</td>
                <td class="risk_count text-center">{{ $item['risk_count'] ?? 0 }}</td>
                <td class="unit_status text-center">
                  @php $unitStatusLabel = $unitStatus === 'expired' ? 'Expired' : 'Valid'; @endphp
                  <figure class="badge {{ $unitStatus === 'expired' ? 'bg-danger' : 'bg-success' }}">
                    {{ $unitStatusLabel }}
                  </figure>
                </td>
                <td class="white-space-nowrap">
                  @if ($apAdmin)
                    <a href="{{ route('risk-register-ap.periods.show', ['period' => $periode->id, 'unit_id' => $unit->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="View">
                      <span class="bx bx-show"></span>
                    </a>
                    <a href="{{ route('risk-register-ap.index', ['pid' => $periode->id, 'unit_id' => $unit->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Risk Register">
                      <span class="bx bx-list-check"></span>
                    </a>
                    <a href="{{ route('risk-register-ap.monitorings.index', ['period' => $periode->id, 'unit_id' => $unit->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Monitoring">
                      <span class="bx bx-radar"></span>
                    </a>
                    <a href="{{ route('ap-led.index-by-periode', ['periode' => $periode->id, 'unit_id' => $unit->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                      title="Loss Event">
                      <span class="bx bx-dock-bottom"></span>
                    </a>
                    <a href="{{ route('risk-context-anper.detail', ['periodeId' => $periode->id, 'unitId' => $unit->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                      title="Risk Context">
                      <span class="bx bx-target-lock"></span>
                    </a>
                  @else
                    <a href="{{ route('risk-register-ap.periods.show', ['period' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="View">
                      <span class="bx bx-show"></span>
                    </a>
                    <a href="{{ route('risk-register-ap.index', ['pid' => $periode->id, 'unit_id' => $unit->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Risk Register">
                       <span class="bx bx-list-check"></span>
                     </a>
                    <a href="{{ route('risk-register-ap.monitorings.index', ['period' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Monitoring">
                      <span class="bx bx-radar"></span>
                    </a>
                    <a href="{{ route('ap-led.index-by-periode', ['periode' => $periode->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                      title="Loss Event">
                      <span class="bx bx-dock-bottom"></span>
                    </a>
                    {{-- Asumsi route risk-context sama --}}
                    <a href="{{ route('risk-context-anper.detail-anper', ['periodeId' => $periode->id, 'unitId' => $unit->id]) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                      title="Risk Context">
                      <span class="bx bx-target-lock"></span>
                    </a>
                  @endif
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

    // Build units with status mapping for dynamic division options
    @php
      $unitsWithStatus = [];
      foreach($dataToDisplay as $item) {
        $unitsWithStatus[] = [
          'name' => $item['unit']->name,
          'status' => ($item['unit_status'] === 'expired' ? 'Expired' : 'Valid')
        ];
      }
    @endphp
    const unitsWithStatus = @json($unitsWithStatus);

    function updateDivisionOptions(selectedStatus) {
      @if($apAdmin)
        const $select = $('#unit_id_filter');
        const current = $select.val();
        // Preserve placeholder
        const placeholder = '<option value="">Semua Anak Perusahaan</option>';
        $select.empty();
        $select.append(placeholder);
        const filtered = selectedStatus
          ? unitsWithStatus.filter(u => u.status === selectedStatus)
          : unitsWithStatus;
        const namesSeen = new Set();
        filtered.forEach(u => {
          if (!namesSeen.has(u.name)) {
            namesSeen.add(u.name);
            $select.append(`<option value="${u.name}">${u.name}</option>`);
          }
        });
        // reset selection to placeholder
        $select.val('');
        $select.trigger('change');
      @endif
    }

    // Initialize division options based on current status selection
    const initialStatus = $('#unit_status_filter').val();
    updateDivisionOptions(initialStatus);
    // Apply initial table filter to show only current status (default: Valid)
    table.column(4).search(initialStatus || '', false, false).draw();

    @if($apAdmin)
      $('#unit_id_filter').on('change', function() {
        const searchTerm = $(this).val();
        table.column(1).search(searchTerm ? '^' + searchTerm + '$' : '', true, false).draw();
      });
    @endif

    $('#periode_filter').on('change', function() {
      const pid = $(this).val();
      const url = new URL(window.location.href);
      url.searchParams.set('pid', pid);
      window.location.href = url.toString();
    });

    $('#unit_status_filter').on('change', function() {
      const searchTerm = $(this).val();
      // Use non-regex search to avoid whitespace/newline mismatch
      table.column(4).search(searchTerm || '', false, false).draw();
      // Update division options to reflect selected status
      updateDivisionOptions(searchTerm);
    });
  });
</script>
@endpush