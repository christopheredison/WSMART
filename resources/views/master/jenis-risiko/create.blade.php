@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-8">
    <form class="card needs-validation" novalidate="" method="POST" action="{{ route('jenis-risiko.store') }}">
      @csrf
      <div class="card-header d-flex flex-between-center">
        <h2 class="h3">Buat Jenis Risiko Baru</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <div class="form-floating">
              <select class="form-select js-select-hide-search" name="kategori_risiko_id" required>
                <option value="" selected disabled>---</option>
                @foreach($kategoriRisiko as $id => $title)
                <option value="{{ $id }}">{{ $title }}</option>
                @endforeach
              </select>
              <label class="form-label">Kategori Risiko</label>
              <div class="invalid-feedback">Silakan pilih kategori risiko.</div>
            </div>
          </div>
          <div class="col-12">
            <div class="form-floating">
              <input class="form-control" id="title" name="title" type="text" placeholder="Masukkan Jenis Risiko"
                value="{{ old('title') }}" required />
              <label for="title">Jenis Risiko</label>
              <div class="invalid-feedback">Jenis risiko wajib diisi.</div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('jenis-risiko.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
