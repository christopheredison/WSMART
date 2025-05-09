@extends('layouts.default')
@section('dashboard')
<!-- <div class="row">
  <div class="col-12">
    <div class="card mb-3 btn-reveal-trigger">
      <div class="card-header position-relative min-vh-25">
        <form method="POST" action="{{ route('skala-probabilitas.store') }}">
          @csrf
          <div class="form-group mb-3">
            <label class="form-label" for="min">Min</label>
            <input class="form-control" id="min" name="min" type="number" placeholder="Masukkan Min"
              value="{{ old('min') }}" />
          </div>
          <div class="form-group mb-3">
            <label class="form-label" for="max">Max</label>
            <input class="form-control" id="max" name="max" type="number" placeholder="Masukkan Max"
              value="{{ old('max') }}" />
          </div>
          <div class="form-group mb-3">
            <label class="form-label" for="type_risiko">Type Risiko</label>
            <input class="form-control" id="type_risiko" name="type_risiko" type="text"
              placeholder="Masukkan Type Risiko" value="{{ old('type_risiko') }}" />
          </div>
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
            <a href="{{ route('skala-probabilitas.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div> -->
@endsection