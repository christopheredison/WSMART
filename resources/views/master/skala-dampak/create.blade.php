@extends('layouts.default')

@section('dashboard')
<!-- <div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="{{ route('skala-dampak.index') }}" class="fs-2">Skala Dampak</a></li>
        <li class="breadcrumb-item active fs-2" aria-current="page">Create</li>
      </ol>
    </nav>
    <div class="card mb-3 btn-reveal-trigger">
      <div class="card-header position-relative min-vh-25">
        <form method="POST" action="{{ route('skala-dampak.store') }}">
          @csrf
          <div class="form-group mb-3">
            <label class="form-label" for="tingkat">Tingkat</label>
            <input class="form-control" id="tingkat" name="tingkat" type="number" placeholder="Masukkan Tingkat"
              value="{{ old('tingkat') }}" />
          </div>
          <div class="form-group mb-3">
            <label class="form-label" for="title">Deskripsi</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"
              value="{{ old('deskripsi') }}"></textarea>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('skala-dampak.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div> -->
@endsection