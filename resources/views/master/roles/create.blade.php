@extends('layouts.default')
@section('dashboard')
<div class="row">
  <div class="col-12">
    <form class="card" method="POST" action="{{ route('roles.store') }}">
      @csrf
      <div class="card-header d-flex flex-between-center">
        <h2 class="h4">Buat Role Manajemen Baru</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-2 align-items-md-center mb-5">
          <div class="col-12 col-md-2">
            <p class="mb-0 fw-medium">Nama Role</p>
          </div>
          <div class="col-12 col-md-5">
            <label class="form-label d-none" for="name">Nama Role</label>
            <input class="form-control" id="name" name="name" type="text" placeholder="Masukkan nama role"
              value="{{ old('name') }}" />
          </div>
        </div>
        <div class="row g-2">
          <div class="col-12 col-md-2">
            <p class="mb-0 fw-medium">Role</p>
          </div>
          <div class="col-12 col-md-10">
            <div class="row gx-2">
              @foreach ($permissions as $key => $value)
              <div class="col-6 col-lg-4">
                <div class="form-check form-switch">
                  <input class="form-check-input" role="switch" type="checkbox" value="{{ $value->name }}"
                    name="permissions[]" id="permissions" @if (in_array($value->name,
                  old('permissions', []))) checked
                  @endif>
                  <label class="form-check-label" for="permissions">
                    {{ ucwords(str_replace('_', ' ', $value->name)) }}
                  </label>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('roles.index') }}" class="btn btn outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
