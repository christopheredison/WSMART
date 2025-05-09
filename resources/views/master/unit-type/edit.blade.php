@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-lg-6">
    <form class="card" method="POST" action="{{ route('unit-type.update', $unitType) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="d-block col-10">
          <h2 class="h3 mb-2">Edit Unit Type</h2>
          <span class="ff-heading-med">
            {{ $unitType->name }}
          </span>
        </div>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body p-xxl-5">
        <div class="form-group">
          <label class="form-label d-none" for="name">Area</label>
          <input type="text" name="name" id="name" class="form-control" value="{{ $unitType->name }}">
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('unit-type.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
