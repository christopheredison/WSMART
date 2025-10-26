@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-warning-subtle">
            <div class="svg-icon svg-icon-warning">
              @include('partials.icon-layer')
            </div>
          </div>
          <h2 class="h3">Unit</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <button class="btn btn-outline-info btn-sm" onclick="syncUnit()">
              <span class="bx bx-sync"></span>
              <span class="ms-1">Sync</span>
            </button>
            <a class="btn btn-outline-info btn-sm" href="{{ route('unit.create') }}">
              <span class="bx bx-plus"></span>
              <span class="ms-1">New</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-3" style="max-width: 360px;">
          <form method="GET" action="{{ route('unit.index') }}">
            <select name="status" class="form-select" onchange="this.form.submit()">
              <option value="valid" {{ ($status ?? 'valid') === 'valid' ? 'selected' : '' }}>Valid</option>
              <option value="invalid" {{ ($status ?? 'valid') === 'invalid' ? 'selected' : '' }}>Invalid</option>
              <option value="all" {{ ($status ?? 'valid') === 'all' ? 'selected' : '' }}>All</option>
            </select>
          </form>
        </div>
        <div class="position-relative">
          <div class="row row-bulk-select g-2">
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
        <table class="table table-bulk-select table-hover dataTable" data-paging="true" data-scroll-y="false"
          data-filter="true" data-info="true">
          <thead>
            <tr>
              <th class="no-sort white-space-nowrap">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox"
                    data-bulk-select='{"body":"bulk-select-body","actions":"bulk-select-actions","replacedElement":"bulk-select-replace-element"}' />
                </div>
              </th>
              <th class="sort white-space-nowrap" data-sort="no">#</th>
              <th class="sort" data-sort="unit_type_id">Unit Type</th>
              <th class="sort" data-sort="name">Name</th>
              <th class="sort" data-sort="parent_id">Parent</th>
              <th class="sort" data-sort="valid_from">Valid From</th>
              <th class="sort" data-sort="valid_to">Valid To</th>
              <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
            </tr>
          </thead>
          <tbody class="list" id="bulk-select-body">
            @foreach ($unit as $index => $item)
            <tr>
              <td class="white-space-nowrap">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="checkbox-1"
                    data-bulk-select-row="data-bulk-select-row" />
                </div>
              </td>
              <td class="index-number">{{ $index + 1 }}</td>
              <td class="title">{{ $item->unitType->name }}</td>
              <td class="name">{{ $item->name }}</td>
              <td class="parent_id">
                @if ($item->parent)
                {{ $item->parent->name }}
                @else
                N/A
                @endif
              </td>
              <td class="valid_from">{{ $item->valid_from ? $item->valid_from->format('Y-m-d') : 'N/A' }}</td>
              <td class="valid_to">{{ $item->valid_to ? $item->valid_to->format('Y-m-d') : 'N/A' }}</td>
              <td class="white-space-nowrap">
                @if ($item->trashed())
                <button type="submit" class="btn-input-icon ps-0" data-bs-toggle="modal"
                  data-bs-target="#modalRestore{{ $item->id }}">
                  <span class="bx bx-undo" data-bs-toggle="tooltip" title="Undo"></span>
                </button>
                @php
                $itemId = $item->id;
                $innerItemText = $item->name;
                $formAction = route('unit.restore', $item->id);
                @endphp
                @include('partials.modal-restore-alert')
                @else
                <a href="{{ route('unit.edit', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Edit">
                  <span class="bx bx-edit"></span>
                </a>
                <button type="button" class="btn-input-icon btn-manage-relation" data-unit-id="{{ $item->id }}" data-unit-name="{{ $item->name }}" data-bs-toggle="modal" data-bs-target="#modalManageRelation" title="Manage relation">
                  <span class="bx bx-link-alt"></span>
                </button>
                <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                  data-bs-target="#modalDelete{{ $item->id }}">
                  <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                </button>
                @php
                $itemId = $item->id;
                $innerItemText = $item->name;
                $formAction = route('unit.destroy', $item->id);
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

<!-- Manage Relation Modal -->
<div class="modal fade" id="modalManageRelation" tabindex="-1" aria-labelledby="modalManageRelationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header d-flex flex-between-center">
        <h4 class="modal-title" id="modalManageRelationLabel">Manage Relation</h4>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <h5 class="mb-2">Unit terelasi</h5>
          <div class="table-responsive">
            <table class="table table-hover" id="relationTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nama Unit</th>
                  <th>Valid To</th>
                  <th>Status</th>
                  <th class="no-sort">Action</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
        <div class="border-top pt-3">
          <h5 class="mb-2">Tambah relasi</h5>
          <div class="row g-2 align-items-center">
            <div class="col-md-8">
              <select id="invalidUnitSelect" class="form-select">
                <option value="">Pilih unit tidak valid...</option>
              </select>
            </div>
            <div class="col-md-4 text-end">
              <!-- Tombol Tambah dipindah ke footer agar sejajar dengan Tutup -->
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer pt-0 d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <span class="bx bx-x"></span>
          <span class="ms-1">Tutup</span>
        </button>
        <button type="button" id="btnAddRelation" class="btn btn-submit">
          <span class="bx bx-plus"></span>
          <span class="ms-1">Tambah</span>
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function syncUnit() {
    Swal.fire({
      title: 'Are you sure?',
      text: 'This action will sync all units from API',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sync',
      cancelButtonText: 'Cancel',
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Syncing unit...',
          text: 'Please wait...',
          icon: 'info',
          showCancelButton: false,
          showConfirmButton: false,
        });
        $.ajax({
          url: '{{ route('unit.sync') }}',
          type: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
          },
          success: function(response) {
            Swal.close();
            Swal.fire({
              title: 'Success',
              text: 'Unit synced successfully',
              icon: 'success',
            }).then(() => {
              location.reload();
            });
          },
          error: function(xhr, status, error) {
            Swal.close();
            Swal.fire({
              title: 'Error',
              text: xhr.responseJSON?.message || 'Failed to sync units',
              icon: 'error',
            }).then(() => {
              location.reload();
            });
          }
        });
      }
    });
  }

  // Manage Relation logic
  let currentUnitId = null;
  let currentUnitName = null;

  $(document).on('click', '.btn-manage-relation', function() {
    currentUnitId = $(this).data('unit-id');
    currentUnitName = $(this).data('unit-name');
    $('#modalManageRelationLabel').text('Manage Relation: ' + currentUnitName);
    loadRelations(currentUnitId);
  });

  function loadRelations(unitId) {
    $('#relationTable tbody').html('<tr><td colspan="5">Loading...</td></tr>');
    $('#invalidUnitSelect').empty().append('<option value="">Pilih unit tidak valid...</option>');
    $.ajax({
      url: '/unit/' + unitId + '/relations',
      type: 'GET',
      success: function(resp) {
        // Fill table
        const rows = resp.relations.map((rel, idx) => `
          <tr>
            <td>${idx + 1}</td>
            <td>${rel.related_unit_name || '-'}</td>
            <td>${rel.valid_to || '-'}</td>
            <td>${rel.status ? 'Valid' : 'Invalid'}</td>
            <td>
              <button type="button" class="btn btn-sm btn-outline-danger btn-remove-relation" data-relation-id="${rel.id}">
                <span class="bx bx-unlink"></span>
                <span class="ms-1">Hapus</span>
              </button>
            </td>
          </tr>
        `);
        $('#relationTable tbody').html(rows.join('') || '<tr><td colspan="5">Belum ada relasi</td></tr>');

        // Fill select
        resp.invalid_units.forEach(u => {
          $('#invalidUnitSelect').append(`<option value="${u.id}">${u.name}</option>`);
        });
      },
      error: function(xhr) {
        $('#relationTable tbody').html('<tr><td colspan="5">Gagal memuat data</td></tr>');
      }
    });
  }

  $('#btnAddRelation').on('click', function() {
    const relatedId = $('#invalidUnitSelect').val();
    if (!currentUnitId || !relatedId) {
      Swal.fire({ icon: 'info', title: 'Pilih unit tidak valid terlebih dahulu' });
      return;
    }
    $.ajax({
      url: '/unit/' + currentUnitId + '/relations',
      type: 'POST',
      data: { related_unit_id: relatedId, _token: '{{ csrf_token() }}' },
      success: function(resp) {
        Swal.fire({ icon: 'success', title: 'Relasi ditambahkan' });
        loadRelations(currentUnitId);
      },
      error: function(xhr) {
        Swal.fire({ icon: 'error', title: 'Gagal menambahkan relasi', text: xhr.responseJSON?.message || 'Error' });
      }
    });
  });

  $(document).on('click', '.btn-remove-relation', function() {
    const relationId = $(this).data('relation-id');
    if (!currentUnitId || !relationId) return;
    $.ajax({
      url: '/unit/' + currentUnitId + '/relations/' + relationId,
      type: 'DELETE',
      data: { _token: '{{ csrf_token() }}' },
      success: function(resp) {
        Swal.fire({ icon: 'success', title: 'Relasi dihapus' });
        loadRelations(currentUnitId);
      },
      error: function(xhr) {
        Swal.fire({ icon: 'error', title: 'Gagal menghapus relasi', text: xhr.responseJSON?.message || 'Error' });
      }
    });
  });
</script>
@endpush