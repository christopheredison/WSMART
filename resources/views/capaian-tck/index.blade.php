@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="row justify-content-between">
          <div class="col d-flex align-items-center gap-3">
            <div class="bg-warning-subtle p-2 rounded-4">
              <div class="lead__icon">
                <div class="svg-icon svg-icon-2x svg-icon-warning">
                  @include('partials.icon-abs02')
                </div>
              </div>
            </div>
            <div class="d-block">
              <div class="ff-preheading">Input Data</div>
              <h2>Capaian TCK</h2>
            </div>
          </div>
          <div class="col-auto">
            <div id="bulk-select-replace-element">
              @can('lost_event_create')
              <a class="btn btn-outline-info btn-sm p-2" href="{{ route('capaian-tck.create') }}" type="button">
                <span class="bx bx-plus"></span>
                <span class="ms-1">Add New</span>
              </a>
              @endcan
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div id="tableExample3">
          <table class="table dataTable" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th>Tanggal Data</th>
                <th class="text-center">Periode</th>
                <th>Unit</th>
                <th class="text-center">Capaian</th>
                <th class="no-sort">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($capaians ?? [] as $capaian)
              <tr>
                <td>{{ $capaian->tanggal_data->format('j F Y') }}</td>
                <td class="text-center">{{ $capaian->periode->tahun }}</td>
                <td>{{ $capaian->unit->name }}</td>
                <td class="text-center">{{ number_format($capaian->capaian, 2, ',', '.') }}</td>
                <td class="white-space-nowrap">
                  <a href="{{ route('capaian-tck.edit', $capaian->id) }}" class="btn-input-icon"
                    data-bs-toggle="tooltip" title="Edit">
                    <span class="bx bx-message-square-edit"></span>
                  </a>
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $capaian->id }}">
                    <span class="bx bx-trash" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $capaian->id;
                  $innerItemText = $capaian->unit->name;
                  $formAction = route('capaian-tck.destroy', $capaian->id);
                  @endphp
                  @include('partials.modal-delete-alert')
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

@endsection
