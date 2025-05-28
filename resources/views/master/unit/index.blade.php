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
</script>
@endpush