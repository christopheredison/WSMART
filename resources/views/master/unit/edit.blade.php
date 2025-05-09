@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-7">
    <form class="card" method="POST" action="{{ route('unit.update', $unit) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="d-block col-10">
          <h2 class="h3 mb-2">Edit Unit</h2>
          <span class="ff-heading-med">{{ $unit->name }}</span>
        </div>
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
              <option selected disabled>Unit Type</option>
              @foreach($unitType as $id => $name)
              <option value="{{ $id }}" {{ $id == $unit->unit_type_id ? 'selected' : '' }}>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Nama Unit</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $unit->name }}">
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Parent</label>
            <select class="form-select js-select-hide-search" name="parent_id">
              <option selected disabled>Parent</option>
              @foreach($parent as $id => $name)
              <option value="{{ $id }}" {{ $id == $unit->parent_id ? 'selected' : '' }}>{{ $name }}</option>
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