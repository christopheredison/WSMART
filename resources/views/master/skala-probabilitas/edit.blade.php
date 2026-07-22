@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12">
    <form class="card" method="POST" action="{{ route('skala-probabilitas.update', $skalaProbabilitas) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex flex-between-center">
        <h2 class="h4">Edit Skala Probabilitas</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-2">
          <div class="col-6 col-lg-3">
            <div class="form-floating text-center">
              <input class="form-control" id="min" name="min" type="number" placeholder="Masukkan Min"
                value="{{ $skalaProbabilitas->min }}" />
              <label class="form-label pe-xl-3" for="min">Min</label>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="form-floating text-center">
              <input class="form-control" id="max" name="max" type="number" placeholder="Masukkan Max"
                value="{{ $skalaProbabilitas->max }}" />
              <label class="form-label pe-xl-3" for="max">Max</label>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="form-floating text-center">
              <input type="number" name="tingkat" id="tingkat" class="form-control"
                value="{{ $skalaProbabilitas->tingkat }}">
              <label class="form-label pe-xl-3" for="tingkat">Tingkat</label>
            </div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="form-floating text-center">
              <input type="text" name="skala" id="skala" class="form-control" value="{{ $skalaProbabilitas->skala }}">
              <label for="skala">Skala</label>
            </div>
          </div>
          <div class="col-12 d-flex align-items-center my-5">
            <label class="form-label mb-0 me-4 pt-1">Type</label>
            <div class="d-flex">
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type_risiko" id="type_umum" value="Umum"
                  {{ $skalaProbabilitas->type_risiko, 'Umum' === 'Umum' ? 'checked' : '' }}>
                <label class="form-check-label" for="type_umum">Umum</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type_risiko" id="type_medis" value="Medis"
                  {{ $skalaProbabilitas->type_risiko === 'Medis' ? 'checked' : '' }}>
                <label class="form-check-label" for="type_medis">Medis</label>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"
                placeholder="">{{ $skalaProbabilitas->deskripsi }}</textarea>
              <label class="form-label" for="deskripsi">Deskripsi</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('skala-probabilitas.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection