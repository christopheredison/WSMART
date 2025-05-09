@extends('layouts.default')

@section('dashboard')
<!-- <main class="main" id="top">
  <div class="container-fluid" data-layout="container-fluid">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item"><a href="{{ route('area-dampak.index') }}" class="fs-2">Area Dampak</a></li>
            <li class="breadcrumb-item active fs-2" aria-current="page">Create</li>
          </ol>
        </nav>
        <div class="card mb-3 btn-reveal-trigger">
          <div class="card-header position-relative min-vh-25">
            <form method="POST" action="{{ route('area-dampak.store') }}">
              @csrf
              <div class="form-group mb-3">
                <label class="form-label" for="title">Area</label>
                <input class="form-control" id="title" name="title" type="text" placeholder="Masukkan Area"
                  value="{{ old('title') }}" />
              </div>
              <div class="form-group mb-3">
                <label class="form-label" for="type">Type</label>
                <select class="form-select" name="type">
                  <option selected disabled></option>
                  <option value="Umum">Umum</option>
                  <option value="Medis">Medis</option>
                </select>
              </div>
              <div class="d-flex justify-content-between">
                <a href="{{ route('area-dampak.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main> -->
@endsection
