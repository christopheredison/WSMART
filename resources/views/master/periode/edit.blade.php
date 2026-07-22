@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-5">
    <form class="card" method="POST" action="{{ route('periode.update', $periode) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex flex-between-center">
        <h2 class="h3">Edit Periode</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="form-group d-md-flex">
          <label class="form-label label-md-start col-md-3" for="tahun">Tahun</label>
          <input type="number" name="tahun" id="tahun" class="form-control" value="{{ $periode->tahun }}">
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('periode.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection