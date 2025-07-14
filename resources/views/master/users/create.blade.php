@extends('layouts.default')
@section('dashboard')
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
  <!--===================== Create User =====================-->
  <div class="col-12">
    <form class="needs-validation" novalidate="" method="POST" action="{{ route('users.store') }}" id="form-add-user">
      @csrf
      <div class="card">
        <div class="card-header d-flex flex-between-center">
          <h2 class="h4">Buat User Baru</h2>
          <div class="lead__icon lead__icon_sm">
            <div class="svg-icon svg-icon-secondary">
              @include('partials.icon-tool')
            </div>
          </div>
        </div>
        <div class="card-body">
          @if($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif
          <div class="row g-3">
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4">Cari</label>
              <div class="input-group">
                <input class="form-control" name="search" type="text" placeholder="Masukkan NIP / Email"
                  value="{{ old('search') }}" style="border-top-right-radius: 0; border-bottom-right-radius: 0;" onkeydown="if(event.key === 'Enter') { event.preventDefault(); document.getElementById('search-button').click(); }"/>
                <button class="btn btn-primary py-2" type="button" id="search-button">
                  <i class="bx bx-search"></i>
                </button>
              </div>
            </div>
          </div>
          <hr>
          <div class="row g-3">
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4">Unit</label>
              <div class="input-group has-validation">
                <select class="form-select select2" name="unit_id" required data-placeholder="Unit">
                  <option></option>
                  @foreach($unit as $id => $name)
                  <option value="{{ $id }}" {{ old('unit_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback">Silakan pilih unit.</div>
              </div>
            </div>
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4" for="name">Nama</label>
              <div class="input-group has-validation">
                <input class="form-control" id="name" name="name" type="text" placeholder="Masukkan Nama"
                  value="{{ old('name') }}" required />
                <div class="invalid-feedback">Silakan isi nama.</div>
              </div>
            </div>
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4" for="nip">NIP</label>
              <div class="input-group has-validation">
                <input class="form-control" id="nip" name="nip" type="text" placeholder="Masukkan NIP"
                  value="{{ old('nip') }}" />
                <div class="invalid-feedback">Silakan isi NIP.</div>
              </div>
            </div>
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4">Jabatan</label>
              <div class="input-group has-validation">
                <select class="form-select select2" name="jabatan_id" data-placeholder="Jabatan">
                  <option></option>
                  @foreach($jabatans as $jabatan)
                  <option value="{{ $jabatan->id }}" {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}">{{ $jabatan->name }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback">Silakan pilih jabatan.</div>
              </div>
            </div>
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4">Level</label>
              <div class="input-group has-validation">
                <select class="form-select select2" name="level_id" required data-placeholder="Level">
                  <option></option>
                  @foreach($levels as $level)
                  <option value="{{ $level->id }}" {{ old('level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                  @endforeach
                </select>
                <div class="invalid-feedback">Silakan pilih level.</div>
              </div>
            </div>
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4" for="email">Email</label>
              <div class="input-group has-validation">
                <input class="form-control" id="email" name="email" type="email" placeholder="Masukkan Email"
                  value="{{ old('email') }}" required />
                <div class="invalid-feedback">Silakan isi email.</div>
              </div>
            </div>
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4" for="password">Password</label>
              <div class="input-group password-input-col has-validation">
                <input class="form-control form_password" id="password" name="password" type="password"
                  placeholder="Masukkan Password" required />
                <i id="pass" class='bx bx-show-alt'></i>
                <div class="invalid-feedback">Silakan isi password.</div>
              </div>
            </div>
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4" for="password_confirmation">Konfirmasi
                Password</label>
              <div class="input-group password-input-col has-validation">
                <input class="form-control form_confirmation" id="password_confirmation" name="password_confirmation"
                  type="password" placeholder="Konfirmasi Password" required />
                <i id="conf_pass" class='bx bx-show-alt'></i>
                <div class="invalid-feedback">Silakan isi password.</div>
              </div>
            </div>
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4" for="roles">User Projects</label>
              <div class="input-group col-auto">
                <select name="user_projects[]" class="form-select select2" multiple>
                  @foreach ($projects as $key => $value)
                      <option value="{{ $key }}"
                              @if (in_array($key, old('user_projects', []))) selected @endif>
                          {{ ucwords(str_replace('_', ' ', $value)) }}
                      </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-12 col-md-10 col-lg-12">
              <div class="row justify-content-between pt-3 gx-lg-0">
                <div class="col-md-3 col-lg-2">
                  <label class="form-label">Roles</label>
                </div>
                <div class="col-md-9 me-lg-7 me-xxl-8">
                  <div class="row gx-2 gx-md-4">
                    @foreach ($roles as $key => $value)
                    <div class="col-6 col-md-6 col-lg-3">
                      <div class="form-check form-switch">
                        <input id="roles{{ $key }}" class="form-check-input" role="switch" type="checkbox" name="roles[]"
                          value="{{ $value }}" @if (in_array($value, old('roles', []))) checked @endif>
                        </input>
                        <label class="form-check-label" for="roles{{ $key }}">{{ ucwords(str_replace('_', ' ', $value)) }}</label>
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
          <button type="submit" class="btn btn-submit">Simpan</button>
          <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $('#search-button').click(function() {
    Swal.fire({
      title: 'Mencari user...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading()
      }
    });
    $.ajax({
      url: '{{ route('users.search-remote-user') }}',
      type: 'GET',
      data: {
        search: $('input[name="search"]').val(),
        not_registered_only: 1
      },
      success: function(response) {
        const data = response.data;
        $('#name').val(data.nm_peg);
        $('#name').prop('readonly', true);
        $('#nip').val(data.nip);
        $('#nip').prop('readonly', true);
        if (data.email) {
          $('#email').val(data.email);
          $('#email').prop('readonly', true);
        } else {
          $('#email').val('');
          $('#email').prop('readonly', false);
        }
        
        $('select[name="unit_id"] option').each(function() {
          if (this.value && isNaN(this.value)) {
            $(this).remove();
          }
        });

        if (data.nm_unit) {
          const existingUnit = $('select[name="unit_id"] option').filter(function() {
            return $(this).text() === data.nm_unit;
          }).val();
          if (existingUnit) {
            $('select[name="unit_id"]').val(existingUnit).change();
          } else {
            $('select[name="unit_id"]').append(`<option value="${data.nm_unit}">${data.nm_unit}</option>`);
            $('select[name="unit_id"]').val(data.nm_unit).change();
          }
        }
        $('select[name="jabatan_id"]').val(response.jabatan?.id || '').change();
        Swal.close();
      },
      error: function(xhr, status, error) {
        $('#form-add-user :input:not([type="hidden"]):not([name="search"])').val('').change();
        $('#name').prop('readonly', false);
        $('#nip').prop('readonly', false);
        $('#email').prop('readonly', false);
        Swal.close();
        Swal.fire({
          title: 'Error',
          text: xhr.responseJSON.message || 'Terjadi kesalahan saat mencari user',
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    });
  });

  if ($('#form-add-user :input[name="search"]').val()) {
    $('#search-button').click();
  }

  $(document).ready(function() {
    // $('select[name="jabatan_id"]').change(function() {
    //   const level = $(this).find('option:selected').data('level');
    //   $('input[name="level"]').val(level);
    // });
  });
</script>
@endpush