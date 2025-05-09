@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
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
          <h2 class="h3">Parameter Pengukuran</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#tambahParameterModal">
              <span class="bx bx-plus"></span>
              <span class="ms-1">Tambah Data</span>
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="position-relative">
          <div class="row row-bulk-select g-2 mb-3">
            <div class="col-md-6">
              <form action="{{ route('measurement-parameter.index') }}" method="GET" class="d-flex">
                <input type="text" name="keyword" class="form-control me-2" placeholder="Cari parameter atau kriteria..." value="{{ request('keyword') }}">
                <button type="submit" class="btn btn-primary">Filter</button>
              </form>
            </div>
            <div class="col-6 col-md-4 col-lg-3 col-xxl-2 mb-3 d-none" id="bulk-select-actions">
              <div class="d-flex">
                <select class="form-select js-select-hide-search" aria-label="Bulk actions">
                  <option selected="selected">Bulk actions</option>
                  <option value="Delete">Delete</option>
                  <option value="Archive">Archive</option>
                </select>
                <button class="btn btn-muted btn-sm" type="submit">Apply</button>
              </div>
            </div>
          </div>
        </div>
        <table class="table table-bulk-select table-hover dataTable" data-paging="true" data-filter="true">
          <thead>
            <tr>
              <th class="white-space-nowrap no-sort">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox"
                    data-bulk-select='{"body":"bulk-select-body","actions":"bulk-select-actions","replacedElement":"bulk-select-replace-element"}' />
                </div>
              </th>
              <th class="white-space-nowrap">#</th>
              <th class="mw-10r">Dimensi</th>
              <th class="mw-10r">Sub Dimensi</th>
              <th class="mw-10r">Parameter</th>
              <th class="">Jumlah Kriteria</th>
              <th class="no-sort white-space-nowrap">Action</th>
            </tr>
          </thead>
          <tbody class="list" id="bulk-select-body">
            @foreach ($parameters as $index => $item)
            <tr>
              <td class="white-space-nowrap">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="checkbox-{{ $item->id }}"
                    data-bulk-select-row="data-bulk-select-row" />
                </div>
              </td>
              <td class="index-number">{{ $index + 1 }}</td>
              <td>{{ $item->subDimension->dimension->name ?? '-' }}</td>
              <td>{{ $item->subDimension->name ?? '-' }}</td>
              <td>{{ $item->statement }}</td>
              <td>{{ $item->criteria->count() }}</td>
              <td class="white-space-nowrap no-sort">
                @if ($item->trashed())
                <button type="submit" class="btn-input-icon ps-0" data-bs-toggle="modal"
                  data-bs-target="#modalRestore{{ $item->id }}">
                  <span class="bx bx-undo" data-bs-toggle="tooltip" title="Undo"></span>
                </button>
                @php
                $itemId = $item->id;
                $innerItemText = $item->statement;
                $formAction = route('measurement-parameter.restore', $item->id);
                @endphp
                @include('partials.modal-restore-alert')
                @else
                <a href="{{ route('measurement-parameter.show', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                  title="Lihat Detail">
                  <span class="bx bx-show"></span>
                </a>
                <a href="{{ route('measurement-parameter.set-criteria', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                  title="Set Kriteria">
                  <span class="bx bx-list-check"></span>
                </a>
                <a href="{{ route('measurement-parameter.edit', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                  title="Edit">
                  <span class="bx bx-edit"></span>
                </a>
                <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                  data-bs-target="#modalDelete{{ $item->id }}">
                  <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                </button>
                @php
                $itemId = $item->id;
                $innerItemText = $item->statement;
                $formAction = route('measurement-parameter.destroy', $item->id);
                @endphp
                @include('partials.modal-delete-alert')
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Parameter -->
<div class="modal fade" id="tambahParameterModal" tabindex="-1" aria-labelledby="tambahParameterModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tambahParameterModalLabel">Tambah Parameter Pengukuran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formTambahParameter" action="{{ route('measurement-parameter.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="sub_dimension_id" class="form-label">Sub Dimensi</label>
            <select class="form-select" id="sub_dimension_id" name="sub_dimension_id" required>
              <option value="" selected disabled>Pilih Sub Dimensi</option>
              @foreach($subDimensions ?? [] as $subDimension)
                <option value="{{ $subDimension->id }}">{{ $subDimension->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="statement" class="form-label">Parameter</label>
            <textarea class="form-control" id="statement" name="statement" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    // Form submit dengan AJAX
    $('#formTambahParameter').on('submit', function(e) {
      e.preventDefault();
      
      $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
          // Tutup modal
          $('#tambahParameterModal').modal('hide');
          
          // Reset form
          $('#formTambahParameter')[0].reset();
          
          // Tampilkan pesan sukses
          toastr.success('Parameter berhasil ditambahkan');
          
          // Reload halaman untuk menampilkan data baru
          location.reload();
        },
        error: function(xhr) {
          let errors = xhr.responseJSON.errors;
          
          // Tampilkan pesan error
          if (errors) {
            $.each(errors, function(key, value) {
              toastr.error(value[0]);
            });
          } else {
            toastr.error('Terjadi kesalahan. Silakan coba lagi.');
          }
        }
      });
    });
  });
</script>
@endpush