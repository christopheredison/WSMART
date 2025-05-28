@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-7">
    <form class="card" method="POST" action="{{ route('unit.store') }}">
      @csrf
      <div class="card-header d-flex flex-between-center">
        <h2 class="h4">Buat Unit Baru</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row gx-0 gy-3">
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Tipe Unit</label>
            <select class="form-select js-select-hide-search" name="unit_type_id">
              <option value="Tipe Unit" selected disabled>Pilih</option>
              @foreach($unitType as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Unit ID</label>
            <input class="form-control" id="unit_api_id" name="unit_api_id" type="text" placeholder="Isi Unit ID"
              value="{{ old('unit_api_id') }}" />
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Nama Unit</label>
            <input class="form-control" id="name" name="name" type="text" placeholder="Isi Nama Unit"
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