@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-7 col-lg-6">
    <form class="card needs-validation" novalidate="" method="POST"
      action="{{ route('rencana-kegiatan.update', $rencanaKegiatan) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="d-block col-10">
          <h2 class="h3 mb-2">Edit Rencana Kegiatan</h2>
          <span class="ff-heading-med">{{ $rencanaKegiatan->title }}</span>
        </div>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <div class="form-floating has-validation">
              <input class="form-control" type="text" name="title" id="title" placeholder="Rencana Kegiatan"
                value="{{ $rencanaKegiatan->title }}">
              <label for="title" for="title">Rencana Kegiatan</label>
              <div class="invalid-feedback">Silakan isi rencana kegiatan.</div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('rencana-kegiatan.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection