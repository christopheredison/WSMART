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
                      @if (!$loop->last)
                      ,
                      @endif
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
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <a class="btn btn-outline-info btn-sm" href="{{ route('users.create') }}">
              <span class="bx bx-plus"></span>
              <span class="ms-1">New</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
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
        $('#userTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('users.index') }}",
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'nip', name: 'nip', defaultContent: '-' },
                { data: 'level', name: 'level.name', defaultContent: '-' },
                { data: 'role_names', name: 'roles.name', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            autoWidth: false
        });
    });

    // You'll need to adapt your delete/restore functions to handle AJAX or form submission dynamically
    function deleteUser(id) {
        if(confirm('Are you sure?')) {
            // Create a temporary form to submit the DELETE request
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '/users/' + id; // Adjust route as needed
            form.innerHTML = '@csrf @method("DELETE")';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function restoreUser(id) {
        // Similar logic for restore
        if(confirm('Restore this user?')) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '/users/' + id + '/restore';
            form.innerHTML = '@csrf'; // POST method is usually enough for restore
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection
