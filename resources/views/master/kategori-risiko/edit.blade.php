@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-xl-6">
    <form class="card" method="POST" action="{{ route('kategori-risiko.update', $kategoriRisiko) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="col-10 d-block">
          <h2 class="h3 mb-2">Edit Kategori Risiko</h2>
          <span class="ff-heading-med">{{ $kategoriRisiko->title }}</span>
        </div>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="form-floating">
          <input type="text" name="title" id="title" class="form-control" value="{{ $kategoriRisiko->title }}">
          <label for="title">Kategori Risiko</label>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('kategori-risiko.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection