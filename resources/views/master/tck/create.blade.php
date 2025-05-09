@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-7">
    <form class="card" method="POST" action="{{ route('tck.store') }}">
      @csrf
      <div class="card-header d-flex flex-between-center">
        <h2 class="h4">Buat Target Capaian Kinerja Baru</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row gx-0 gy-3">
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Periode</label>
            <select class="form-select js-select-hide-search" name="periode_id" required>
              <option selected disabled>Periode</option>
              @foreach($periode as $id => $tahun)
              <option value="{{ $id }}">{{ $tahun }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Unit</label>
            <select class="form-select js-select-hide-search" name="unit_id" required>
              <option selected disabled>Unit</option>
              @foreach($unit as $id => $name)
              <option value="{{ $id }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Target Capaian Kinerja</label>
            <input class="form-control" id="title" name="title" type="text"
              placeholder="Masukkan Target Capaian Kinerja" value="{{ old('tck') }}" />
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('tck.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection