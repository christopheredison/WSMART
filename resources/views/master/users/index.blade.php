@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-3">
  <!--===================== Roles and Permissions =====================-->
  <div class="col-12">
    <div class="accordion" id="accordionRoles">
      <div class="accordion-item">
        <h2 class="h6 accordion-header" id="headingRoles">
          <button class="accordion-button collapsed py-xxl-4 px-xxl-6" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">Roles and
            Permissions</button>
        </h2>
        <div class="accordion-collapse collapse" id="collapse1" aria-labelledby="headingRoles"
          data-bs-parent="#accordionRoles">
          <div class="accordion-body py-xxl-4 px-xxl-6">
            <div class="table-responsive scrollbar">
              <table class="table">
                <thead>
                  <tr>
                    <th>Role</th>
                    <th>Permissions</th>
                  </tr>
                </thead>
                <tbody class="list" id="bulk-select-body">
                  @foreach ($roless as $role)
                  <tr>
                    <td class="white-space-nowrap fw-bold">{{ ucwords(str_replace('_', ' ', $role->name)) }}</td>
                    <td>
                      @foreach ($role->permissions as $permission)
                      {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                      @if (!$loop->last) , @endif
                      @endforeach
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
  </div>
  <!--===================== Manajemen User =====================-->
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-warning-subtle">
            <div class="svg-icon svg-icon-warning">
              @include('partials.icon-user')
            </div>
          </div>
          <h2 class="h3">Manajemen User</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto d-flex gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('users.logs') }}">
              <span class="bx bx-history"></span>
              <span class="ms-1">Log Perubahan</span>
            </a>
            @can('manajemen_user')
              <a class="btn btn-outline-info btn-sm" href="{{ route('users.create') }}">
                <span class="bx bx-plus"></span>
                <span class="ms-1">New</span>
              </a>
            @endcan
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-5">
                <label class="form-label fw-bold">Filter Divisi</label>
                <select id="filter_unit" class="form-select select2" data-placeholder="Semua Divisi">
                    <option value="">Semua Divisi</option>
                    @foreach($units as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold">Filter Project</label>
                <select id="filter_project" class="form-select select2" data-placeholder="Semua Project">
                    <option value="">Semua Project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">
                             {{ $project->profit_center ? '['.$project->profit_center.'] ' : '' }}{{ ucwords(str_replace('_', ' ', $project->project_name)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" id="btn-reset-filter" class="btn btn-sm btn-secondary w-100">
                    <span class="bx bx-reset"></span> Reset
                </button>
            </div>
        </div>

        <div id="tableExample3">
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
          <table id="userTable" class="table table-hover" style="width:100%">
            <thead>
                <tr>
                    <th class="no-sort white-space-nowrap">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="bulk-select-all" />
                        </div>
                    </th>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>NIP</th>
                    <th>Divisi</th>
                    <th>Level</th>
                    <th>Roles</th>
                    <th class="no-sort">Action</th>
                </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#userTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('users.index') }}",
                data: function (d) {
                    d.filter_unit = $('#filter_unit').val();
                    d.filter_project = $('#filter_project').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'nip', name: 'nip', defaultContent: '-' },
                { data: 'unit_name', name: 'unit.name', defaultContent: '-' },
                { data: 'level', name: 'level.name', defaultContent: '-' },
                { data: 'role_names', name: 'roles.name', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            autoWidth: false
        });

        $('#userTable').on('click', '.btn-ghost-login', function() {
            const id = $(this).data('user-id');
            const name = $(this).data('user-name');
            ghostLogin(id, name);
        });

        // Trigger reload DataTables saat dropdown filter berubah
        $('#filter_unit, #filter_project').on('change', function() {
            table.draw();
        });

        // Tombol Reset Filter
        $('#btn-reset-filter').click(function() {
            $('#filter_unit').val('').trigger('change');
            $('#filter_project').val('').trigger('change');
        });
    });

    // --- SWEETALERT UNTUK DELETE ---
    function deleteUser(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "User ini akan dihapus dari sistem (Soft Delete).",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true // Membalik posisi tombol agar 'Batal' di kiri
        }).then((result) => {
            if (result.isConfirmed) {
                // Menampilkan loading state
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                // Create a temporary form to submit the DELETE request
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '/users/' + id;
                form.innerHTML = '@csrf @method("DELETE")';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // --- SWEETALERT UNTUK RESTORE ---
    function restoreUser(id) {
        Swal.fire({
            title: 'Kembalikan User?',
            text: "User yang dihapus akan diaktifkan kembali.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, kembalikan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Menampilkan loading state
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '/users/' + id + '/restore';
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function ghostLogin(id, name) {
        Swal.fire({
            title: 'Masuk sebagai user ini?',
            text: 'Anda akan ghost login sebagai ' + name + '.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, ghost login',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '/ghost-login/' + id;
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endsection
