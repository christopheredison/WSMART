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
            data-bs-target="#collapse1">Roles and Permissions</button>
        </h2>
        <div class="accordion-collapse collapse" id="collapse1" data-bs-parent="#accordionRoles">
          <div class="accordion-body py-xxl-4 px-xxl-6">
            <div class="table-responsive scrollbar">
              <table class="table">
                <thead><tr><th>Role</th><th>Permissions</th></tr></thead>
                <tbody class="list">
                  @foreach ($roless as $role)
                  <tr>
                    <td class="fw-bold">{{ ucwords(str_replace('_', ' ', $role->name)) }}</td>
                    <td>
                      @foreach ($role->permissions as $permission)
                        {{ ucwords(str_replace('_', ' ', $permission->name)) }} @if (!$loop->last) , @endif
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

  <!--===================== Edit User =====================-->
  <div class="col-12">
    <form method="POST" action="{{ route('users.update', $user) }}">
      @csrf
      @method('PUT')
      <div class="card">
        <div class="card-header d-flex flex-between-center">
          <h2 class="h4">Edit User</h2>
        </div>
        <div class="card-body">
          @if($errors->any())
          <div class="alert alert-danger">
            <ul>@foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
          </div>
          @endif

          <div class="row g-3">
            <!-- NIP disabled & Sync Button -->
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4">NIP</label>
              <div class="input-group">
                <input
                  class="form-control bg-light"
                  name="search"
                  type="text"
                  value="{{ $user->nip }}"
                  readonly
                  style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
                />
                <button class="btn btn-info py-2" type="button" id="btn-sync-hc">
                  <span class="bx bx-sync"></span> Sync Data
                </button>
              </div>
            </div>

            <!-- Unit -->
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4">Unit</label>
              <div class="input-group has-validation">
                <select class="form-select select2" name="unit_id" id="unit_id" required>
                  <option selected disabled>Unit</option>
                  @foreach($unit as $id => $name)
                  <option value="{{ $id }}" {{ $user->unit_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <!-- Nama -->
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4" for="name">Nama Lengkap</label>
              <div class="input-group has-validation">
                <input class="form-control" id="name" name="name" type="text" placeholder="Masukkan Nama"
                  value="{{ $user->name }}" required />
              </div>
            </div>

            <!-- Jabatan (Fix HTML Typo Syntax) -->
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4">Jabatan</label>
              <div class="input-group has-validation">
                <select class="form-select select2" name="jabatan_id" id="jabatan_id" data-placeholder="Jabatan">
                  <option></option>
                  @foreach($jabatans as $jabatan)
                  <!-- Fixed kelebihan kurung tutup `}}"` yang merusak tampilan HTML select -->
                  <option value="{{ $jabatan->id }}" {{ $user->jabatan_id == $jabatan->id ? 'selected' : '' }}>{{ $jabatan->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <!-- Level -->
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4">Level</label>
              <div class="input-group has-validation">
                <select class="form-select select2" name="level_id">
                  <option selected disabled>Pilih Level</option>
                  @foreach($levels as $level)
                  <option value="{{ $level->id }}" {{ $user->level_id == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <!-- User Projects dengan Profit Center -->
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4" for="roles">User Projects</label>
              <div class="input-group col-auto">
                @php $userProjects = $user->projects->pluck('id')->toArray(); @endphp
                <select name="user_projects[]" class="form-select select2" multiple data-placeholder="Pilih Project">
                  @foreach ($projects as $project)
                      <option value="{{ $project->id }}" @if (in_array($project->id, old('user_projects', $userProjects))) selected @endif>
                          {{ $project->profit_center ? '['.$project->profit_center.'] ' : '' }}{{ ucwords(str_replace('_', ' ', $project->project_name)) }}
                      </option>
                  @endforeach
                </select>
              </div>
            </div>

            <!-- Email dipindah ke paling bawah sebelum roles -->
            <div class="form-group col-12 col-md-7 d-flex mt-4">
              <label class="form-label label-md-start col-md-4" for="email">Email</label>
              <div class="input-group has-validation">
                <input class="form-control" id="email" name="email" type="email" placeholder="Masukkan Email"
                  value="{{ $user->email }}" required />
              </div>
            </div>

            <!-- Roles -->
            <div class="col-12 col-md-10 col-lg-12 border-top pt-3 mt-4">
              <div class="row justify-content-between gx-lg-0">
                <div class="col-md-3 col-lg-2">
                  <label class="form-label fw-bold">Pilih Roles Akun:</label>
                </div>
                <div class="col-md-9 me-lg-7 me-xxl-8">
                  <div class="row gx-2 gx-md-4">
                    @foreach ($roles as $key => $value)
                    <div class="col-6 col-md-6 col-lg-3 mb-2">
                      <div class="form-check form-switch">
                        <input id="roles_{{ $key }}" class="form-check-input" role="switch" type="checkbox" name="roles[]"
                          value="{{ $value }}" {{ (isset($userRoles) && in_array($key, $userRoles)) ? 'checked' : '' }}>
                        <label class="form-check-label" style="cursor:pointer;" for="roles_{{ $key }}">{{ ucwords(str_replace('_', ' ', $value)) }}</label>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <div class="card-footer border-none">
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {

    // Fungsi Sync NIP di halaman Edit
    $('#btn-sync-hc').click(function() {
      let nip = $('input[name="search"]').val();

      if(!nip) {
        Swal.fire('Perhatian', 'NIP User tidak ditemukan, tidak bisa melakukan sync.', 'warning');
        return;
      }

      Swal.fire({
        title: 'Sinkronisasi Data...',
        text: 'Mengambil data terbaru dari HC',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading() }
      });

      $.ajax({
        url: '{{ route('users.search-remote-user') }}',
        type: 'GET',
        data: {
          search: nip
          // Catatan: parameter 'not_registered_only' sengaja TIDAK dikirim agar bisa menarik data pegawai yang sudah ada.
        },
        success: function(response) {
          const data = response.data;

          // Update Field Name & Email
          $('#name').val(data.nm_peg);
          if (data.email) $('#email').val(data.email);

          // Update Unit
          if (response.resolved_unit && response.resolved_unit.id) {
            $('#unit_id').val(response.resolved_unit.id).trigger('change');
          }

          // Update Jabatan
          if(response.jabatan && response.jabatan.id) {
            $('#jabatan_id').val(response.jabatan.id).trigger('change');
          }

          // Update Project
          if (response.resolved_projects && response.resolved_projects.length > 0) {
            $('select[name="user_projects[]"]').val(response.resolved_projects).trigger('change');
          } else {
            $('select[name="user_projects[]"]').val([]).trigger('change');
          }

          Swal.close();
          Swal.fire({
            icon: 'success',
            title: 'Sinkronisasi Berhasil!',
            text: 'Data Nama, Jabatan, Unit, dan Email telah diperbarui di form. Silakan klik Simpan.',
            timer: 3000
          });
        },
        error: function(xhr) {
          Swal.close();
          Swal.fire({
            title: 'Error',
            text: xhr.responseJSON.message || 'Terjadi kesalahan saat memanggil API HC',
            icon: 'error',
            confirmButtonText: 'OK'
          });
        }
      });
    });

  });
</script>
@endpush
