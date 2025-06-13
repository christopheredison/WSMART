@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<style>
/* Style untuk Select2 */
.select2-container--bootstrap-5 .select2-selection {
    border: 1px solid #dee2e6 !important;
    border-radius: 0.25rem !important;
    padding: 0.375rem 0.75rem;
    min-height: 38px;
}

.select2-container--bootstrap-5 .select2-selection--single {
    background-color: #fff;
    border: 1px solid #dee2e6 !important;
}

.select2-container--bootstrap-5.select2-container--focus .select2-selection {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.select2-container--bootstrap-5 .select2-dropdown {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
}

.select2-container--bootstrap-5 .select2-search__field {
    border: 1px solid #dee2e6 !important;
    border-radius: 0.25rem;
    padding: 0.375rem 0.75rem;
}
</style>
<div class="row justify-content-center g-3 g-xl-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-warning-subtle">
            <div class="svg-icon svg-icon-warning">
              @include('partials.icon-layer')
            </div>
          </div>
          <h2 class="h3">Metrik Strategi Risiko</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <a href="{{ route('metrik-strategi-risiko.create') }}" class="btn btn-outline-info btn-sm">
              <span class="bx bx-plus"></span>
              <span class="ms-1">Tambah Data Metrik</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="position-relative">
          <div class="row" id="table-filter">
            <div class="col-md-3 mb-3">
              <label class="form-label">Tahun</label>
              <select name="periode_id" class="form-select select2" id="periode-filter">
                <option value="">Semua</option>
                @foreach($periodes as $periode)
                  <option value="{{ $periode->id }}" {{ request('periode_id') == $periode->id ? 'selected' : '' }}>
                    {{ $periode->tahun }}
                  </option>
                @endforeach
              </select>
            </div>
            {{-- 
            <div class="col-md-3 mb-3">
              <label class="form-label">Peristiwa Risiko</label>
              <select name="peristiwa_risiko_id" class="form-select select2" id="peristiwa-risiko-filter">
                <option value="">Semua</option>
                @foreach($peristiwaRisikos as $peristiwaRisiko)
                  <option value="{{ $peristiwaRisiko->id }}" {{ request('peristiwa_risiko_id') == $peristiwaRisiko->id ? 'selected' : '' }}>
                    {{ $peristiwaRisiko->title }}
                  </option>
                @endforeach
              </select>
            </div>
            --}}
            <div class="col-md-3 mb-3">
              <label class="form-label">&nbsp;</label>
              <button type="button" id="filterButton" class="btn btn-primary d-flex align-items-center" style="height: 38px; padding: 0.375rem 0.75rem;">
                <i class="bx bx-filter-alt me-2"></i>
                <span>Filter</span>
              </button>
            </div>
          </div>
        </div>
        
        <table class="table table-bulk-select table-hover dataTable" id="metrik-table">
          <thead>
            <tr>
              <th class="white-space-nowrap">#</th>
              <th>Periode</th>
              <th>T2 & T3 KBUMN</th>
              <th>Risk Appetite Statement</th>
              <th>Sikap Risiko</th>
              <th>Jumlah Parameter</th>
              <th class="no-sort white-space-nowrap">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($metriks as $index => $metrik)
            <tr>
              <td class="index-number">{{ $index + 1 }}</td>
              <td>{{ $metrik->periode->tahun }}</td>
              <td>{{ $metrik->kategoriRisiko->title ?? '-' }} - {{ $metrik->jenisRisiko->title ?? '-' }}</td>
              <td>{{ $metrik->risk_appetite_statement ?? '-' }}</td>
              <td>{{ $metrik->sikapRisiko->jenis_sikap ?? '-' }}</td>
              <td class="text-center">{{ $metrik->parameterMetriks->count() ?? '0' }}</td>
              <td class="white-space-nowrap no-sort">
                @if($metrik->id)
                <a href="{{ route('metrik-strategi-risiko.parameter', $metrik->id) }}" 
                   class="btn-input-icon" title="Kelola Parameter">
                  <span class="bx bx-list-check text-info"></span>
                </a>
                <!-- <a href="{{ route('metrik-strategi-risiko.edit', $metrik->id) }}" 
                   class="btn-input-icon" data-bs-toggle="tooltip" title="Edit">
                  <span class="bx bx-edit"></span>
                </a> -->
                <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                  data-bs-target="#modalDelete{{ $metrik->id }}">
                  <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                </button>
                @else
                <a href="{{ route('metrik-strategi-risiko.create') }}?periode_id={{ $metrik->periode->id }}" 
                   class="btn-input-icon" data-bs-toggle="tooltip" title="Tambah Metrik">
                  <span class="bx bx-plus-circle text-success"></span>
                </a>
                @endif
              </td>
            </tr>

            <!-- Modal Delete -->
            @php
            $itemId = $metrik->id;
            $innerItemText = "Metrik Risiko periode " . $metrik->periode->tahun;
            $formAction = route('metrik-strategi-risiko.destroy', $metrik->id);
            @endphp
            @include('partials.modal-delete-alert')
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Parameter -->
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  // Inisialisasi Select2 dengan style yang lebih baik
  $('.select2').select2({
    placeholder: 'Pilih Opsi',
    allowClear: true,
    theme: 'bootstrap-5',
    width: '100%'
  }).on('select2:open', function() {
    // Tambahkan class untuk styling
    $('.select2-container--open').addClass('select2-container--custom-border');
  });

  // Handle filter button click
  $('#filterButton').click(function() {
    const periodeId = $('#periode-filter').val();
    const peristiwaRisikoId = $('#peristiwa-risiko-filter').val();
    
    // Redirect dengan parameter filter
    window.location.href = `{{ route('metrik-strategi-risiko.index') }}?periode_id=${periodeId}&peristiwa_risiko_id=${peristiwaRisikoId}`;
  });

  // DataTable configuration
  if ($.fn.DataTable.isDataTable('#metrik-table')) {
    $('#metrik-table').DataTable().destroy();
  }
  
  $('#metrik-table').DataTable({
    responsive: true,
    pageLength: 10,
    order: [[1, 'desc']], 
    columnDefs: [
      { orderable: false, targets: 'no-sort' }
    ],
    language: {
      emptyTable: "Tidak ada data metrik yang sesuai dengan filter"
    }
  });
  
  $('[data-bs-toggle="tooltip"]').tooltip();

  // Handle delete row in parameter table
  $(document).on('click', '.delete-row', function() {
    $(this).closest('tr').remove();
  });
});
</script>
@endpush