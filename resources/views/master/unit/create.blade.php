@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-7">
    <form class="card" method="POST" action="{{ route('unit.store') }}">
      @csrf
      <div class="card-header d-flex flex-between-center">
        <h2 class="h4">Buat Divisi Baru</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger" role="alert">
          <strong>Terjadi kesalahan:</strong>
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif
        <div class="row gx-0 gy-3">
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Tipe Divisi</label>
            <select class="form-select js-select-hide-search" name="unit_type_id">
              <option value="Tipe Unit" selected disabled>Pilih</option>
              @foreach($unitType as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Cost Center</label>
            <div class="w-100">
              <input class="form-control @error('cost_center') is-invalid @enderror" id="cost_center" name="cost_center" type="text" placeholder="Isi Cost Center"
                value="{{ old('cost_center') }}" />
              @error('cost_center')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Nama Divisi</label>
            <input class="form-control" id="name" name="name" type="text" placeholder="Isi Nama Divisi"
              value="{{ old('name') }}" />
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Parent</label>
            <select class="form-select js-select-hide-search" name="parent_id">
              <option selected disabled>Pilih</option>
              @foreach($parent as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('unit.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection