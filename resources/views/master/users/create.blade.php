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
    <form class="needs-validation" novalidate="" method="POST" action="{{ route('users.store') }}">
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
          <div class="row g-3">
            <div class="form-group col-12 col-md-7 d-flex">
              <label class="form-label label-md-start col-md-4">Unit</label>
              <div class="input-group has-validation">
                <select class="form-select select2" name="unit_id" required>
                  <option selected disabled>Unit</option>
                  @foreach($unit as $id => $name)
                  <option value="{{ $id }}">{{ $name }}</option>
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
                        <input id="roles" class="form-check-input" role="switch" type="checkbox" name="roles[]"
                          value="{{ $value }}" @if (in_array($value, old('roles', []))) checked @endif>
                        </input>
                        <label class="form-check-label" for="roles">{{ ucwords(str_replace('_', ' ', $value)) }}</label>
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