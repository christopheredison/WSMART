@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-8 col-xxl-6">
    <form class="card" method="POST" action="{{ route('sikap-risiko.update', $sikapRisiko) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="col-10 d-block">
          <h2 class="h3 mb-2">Edit Sikap Risiko</h2>
          <span class="ff-heading-med">{{ $sikapRisiko->jenis_sikap }}</span>
        </div>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="form-group d-md-flex">
          <label class="form-label label-md-start col-md-2" for="jenis_sikap">Jenis Sikap</label>
          <input type="text" name="jenis_sikap" id="jenis_sikap" class="form-control"
            value="{{ $sikapRisiko->jenis_sikap }}">
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <button type="submit" class="btn btn-submit">Simpan</button>
        <a href="{{ route('sikap-risiko.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection