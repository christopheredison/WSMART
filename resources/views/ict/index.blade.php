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
              @include('partials.icon-pyramid')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">Input Data</div>
          <h2>ICT Plan</h2>
        </div>
      </div>
      <div class="card-body dt-header-true">
        <div id="tableExample3">
          <div class="row g-2 mb-1">
            <div class="col-auto ms-auto">
              <a id="add-ict-plan-button" href="{{ route('ict.create') }}" type="button"
                class="btn btn-outline-info btn-sm d-flex flex-center" data-bs-toggle="tooltip"
                data-bs-title="Tambah ICT Plan">
                <i class="bx bx-plus"></i>
                <span class="ms-1">Tambah ICT Plan</span>
              </a>
            </div>
          </div>
          <table class="table dataTable" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="sasaran_bumn">Sasaran BUMN</th>
                <th class="sort" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                <th class="sort" data-sort="lokasi_risiko">Lokasi Risiko</th>
                <th class="sort" data-sort="business_process">Business Process</th>
                <th class="sort" data-sort="key_controls">Key Control</th>
                <th class="sort" data-sort="metode_pengujian">Metode Pengujian</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($data as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="sasaran_bumn">{{ $item['sasaran_bumn'] }}</td>
                <td class="peristiwa_risiko">{{ $item['peristiwa_risiko'] }}</td>
                <td class="lokasi_risiko">{{ $item['lokasi_risiko'] }}</td>
                <td class="business_process">{{ $item['business_process'] }}</td>
                <td class="key_controls">{{ $item['key_controls'] }}</td>
                <td class="metode_pengujian">{{ $item['metode_pengujian'] }}</td>
                <td class="white-space-nowrap">
                  <a href="{{ route('ict.show', $item['id']) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="View">
                    <span class="bx bx-show-alt"></span>
                  </a>
                  {{--
                  <a href="{{ route('ict.edit', $item['id']) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Edit">
                    <span class="bx bx-message-square-edit"></span>
                  </a>
                  --}}
                  <a href="{{ route('ict.testing', $item['id']) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Testing">
                    <span class="bx bx-test-tube text-warning"></span>
                  </a>
                  <a href="{{ route('ict.report', $item['id']) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Report">
                    <span class="bx bx-file text-success"></span>
                  </a>
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $item['id'] }}">
                    <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $item['id'];
                  $innerItemText = $item['sasaran_bumn'];
                  $formAction = route('ict.destroy', $item['id']);
                  @endphp
                  @include('partials.modal-delete-alert')
                </td>
              </tr>
              @endforeach
              @php
              if (!isset($index)) {
              $index = 0;
              }
              @endphp
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script>
const table = new DataTable('#example');
table.on('mouseenter', 'td', function() {
  let colIdx = table.cell(this).index().column;

  table
    .cells()
    .nodes()
    .each((el) => el.classList.remove('highlight'));

  table
    .column(colIdx)
    .nodes()
    .each((el) => el.classList.add('highlight'));
});

function deleteItem(element) {
  if (confirm('Are you sure you want to delete?')) {
    // Ambil form yang berisi tombol hapus
    const form = element.parentNode;
    // Submit form untuk menghapus item
    form.submit();
  }
}
</script>
<script>
$(document).ready(function() {
  // Inisialisasi DataTable
  const table = $('#example').DataTable();
});
</script>
@endsection