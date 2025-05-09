@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-8">
    <form class="card needs-validation" novalidate="" method="POST"
      action="{{ route('jenis-risiko.update', $jenisRisiko) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="col-10 d-block">
          <h2 class="h3 mb-2">Edit Jenis Risiko</h2>
          <span class="ff-heading-med">{{ $jenisRisiko->title }}</span>
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
            <div class="form-floating">
              <select class="form-select js-select-hide-search" name="kategori_risiko_id" required>
                <option selected disabled>Kategori Risiko</option>
                @foreach($kategoriRisiko as $id => $title)
                <option value="{{ $id }}" {{ $id == $jenisRisiko->kategori_risiko_id ? 'selected' : '' }}>
                  {{ $title }}
                </option>
                @endforeach
              </select>
              <label class="form-label">Kategori Risiko</label>
              <div class="invalid-feedback">Silakan pilih kategori risiko.</div>
            </div>
          </div>
          <div class="col-12">
            <div class="form-floating">
              <input type="text" name="title" id="title" class="form-control" value="{{ $jenisRisiko->title }}"
                required>
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