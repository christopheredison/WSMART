@extends('layouts.default')
@section('dashboard')
<div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="{{ route('periode.index') }}" class="fs-2">Periode</a></li>
        <li class="breadcrumb-item active fs-2" aria-current="page">Create</li>
      </ol>
    </nav>
    <div class="card mb-3 btn-reveal-trigger">
      <div class="card-header position-relative min-vh-25">
        <form method="POST" action="{{ route('periode.store') }}">
          @csrf
          <div class="form-group mb-3">
            <label class="form-label" for="tahun">Tahun</label>
            <input class="form-control" id="tahun" name="tahun" type="number" placeholder="Masukkan Tahun"
              value="{{ old('tahun') }}" />
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('periode.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
