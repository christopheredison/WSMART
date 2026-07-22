@extends('layouts.default')

@section('dashboard')
<!-- <div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="{{ route('sikap-risiko.index') }}" class="fs-2">Sikap Risiko</a></li>
        <li class="breadcrumb-item active fs-2" aria-current="page">Create</li>
      </ol>
    </nav>
    <div class="card mb-3 btn-reveal-trigger">
      <div class="card-header position-relative min-vh-25">
        <form method="POST" action="{{ route('sikap-risiko.store') }}">
          @csrf
          <div class="form-group mb-3">
            <label class="form-label" for="jenis_sikap">Jenis Sikap</label>
            <input class="form-control" id="jenis_sikap" name="jenis_sikap" type="text"
              placeholder="Masukkan Jenis Sikap" value="{{ old('jenis_sikap') }}" />
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('sikap-risiko.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div> -->
@endsection