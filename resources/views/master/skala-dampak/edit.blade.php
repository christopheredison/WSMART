@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-6 col-xxl-4">
    <form class="card" method="POST" action="{{ route('skala-dampak.update', $skalaDampak) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="d-block col-10">
          <h2 class="h3 mb-2">Edit Skala Dampak</h2>
          <span class="ff-heading-med">{{ $skalaDampak->tingkat }} - {{ $skalaDampak->deskripsi }}</span>
        </div>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row gx-0 gy-3">
          <div class="form-group d-flex">
            <label class="form-label label-start col-3" for="type">Tingkat</label>
            <input type="number" name="tingkat" id="tingkat" class="form-control" value="{{ $skalaDampak->tingkat }}">
          </div>
          <div class="form-group d-flex">
            <label class="form-label label-start col-3" for="title">Deskripsi</label>
            <input type="text" class="form-control" id="deskripsi" name="deskripsi"
              value="{{ $skalaDampak->deskripsi }}"></input>
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('skala-dampak.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection