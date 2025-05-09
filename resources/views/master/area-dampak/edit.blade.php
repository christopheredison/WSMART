@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-xxl-6">
    <form class="card" method="POST" action="{{ route('area-dampak.update', $areaDampak) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="d-block col-10">
          <h2 class="h3 mb-2">Edit Area Dampak</h2>
          <span class="ff-heading-med">{{ $areaDampak->title }}</span>
        </div>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row gx-0 gy-4">
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-2" for="title">Area</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ $areaDampak->title }}">
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-2 pt-0">Type</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" id="type_umum" type="radio" name="type" value="Umum"
                {{ $areaDampak->type === 'Umum' ? 'checked' : '' }} />
              <label class="form-check-label" for="type_umum">Umum</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" id="type_medis" type="radio" name="type" value="Medis"
                {{ $areaDampak->type === 'Medis' ? 'checked' : '' }} />
              <label class="form-check-label" for="type_medis">Medis</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('area-dampak.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
