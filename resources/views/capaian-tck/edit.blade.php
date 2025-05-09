@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header d-flex flex-between-center">
        <h3>Edit Capaian TCK</h3>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('capaian-tck.update', $capaianTck) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
          <div class="row gy-3 gx-md-8">
            <div class="col-md-6">
              <div class="d-flex">
                <label class="form-label label-start col-3 col-md-4">Periode</label>
                <select class="form-select js-select-hide-search" name="periode_id">
                  <option selected disabled>Select Periode</option>
                  @foreach ($periodes as $id => $name)
                  <option value="{{ $id }}" {{ $id == $capaianTck->periode_id ? 'selected' : '' }}>{{ $name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex">
                <label class="form-label label-start col-3 col-md-4">Unit</label>
                <select class="form-select select2" name="unit_id">
                  <option selected disabled>Select Unit</option>
                  @foreach ($units as $unitType)
                  <optgroup label="{{ $unitType->first()->unitType->name }}">
                    @foreach ($unitType as $unit)
                    <option value="{{ $unit->id }}" {{ $unit->id == $capaianTck->unit_id ? 'selected' : '' }}>
                      {{ $unit->name }}</option>
                    @endforeach
                  </optgroup>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex">
                <label class="form-label label-start col-3 col-md-4" for="tanggal_data">Tanggal Data</label>
                <input class="form-control datetimepicker" id="tanggal_data" name="tanggal_data" type="text"
                  value="{{ old('tanggal_data', $capaianTck->tanggal_data->format('Y-m-d')) }}" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex">
                <label class="form-label label-start col-3 col-md-4" for="capaian">Capaian TCK</label>
                <input class="form-control" id="capaian" name="capaian" type="number" step="0.01"
                  value="{{ old('capaian', $capaianTck->capaian) }}" />
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer border-0 pt-0">
          <button type="submit" class="btn btn-submit">Simpan</button>
          <a href="{{ route('capaian-tck.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- <main class="main" id="top">
  <div class="container-fluid" data-layout="container-fluid">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item"><a href="{{ route('capaian-tck.index') }}" class="fs-2">Capaian TCK</a></li>
            <li class="breadcrumb-item active fs-2" aria-current="page">Edit</li>
          </ol>
        </nav>
        <div class="card mb-3 btn-reveal-trigger">
          <div class="card-header position-relative min-vh-25">
            <form method="POST" action="{{ route('capaian-tck.update', $capaianTck) }}">
              @csrf
              @method('PUT')

              <div class="form-group mb-3">
                <label class="form-label">Periode</label>
                <select class="form-select js-example-basic-single" name="periode_id">
                  <option selected disabled>Select Periode</option>
                  @foreach ($periodes as $id => $name)
                  <option value="{{ $id }}" {{ $id == $capaianTck->periode_id ? 'selected' : '' }}>{{ $name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-3">
                <label class="form-label">Unit</label>
                <select class="form-select js-example-basic-single" name="unit_id">
                  <option selected disabled>Select Unit</option>
                  @foreach ($units as $unitType)
                  <optgroup label="{{ $unitType->first()->unitType->name }}">
                    @foreach ($unitType as $unit)
                    <option value="{{ $unit->id }}" {{ $unit->id == $capaianTck->unit_id ? 'selected' : '' }}>
                      {{ $unit->name }}</option>
                    @endforeach
                  </optgroup>
                  @endforeach
                </select>
              </div>
              <div class="form-group mb-3">
                <label class="form-label" for="tanggal_data">Tanggal Data</label>
                <input class="form-control" id="tanggal_data" name="tanggal_data" type="date"
                  value="{{ old('tanggal_data', $capaianTck->tanggal_data->format('Y-m-d')) }}" />
              </div>
              <div class="form-group mb-3">
                <label class="form-label" for="capaian">Capaian TCK</label>
                <input class="form-control" id="capaian" name="capaian" type="number" step="0.01"
                  value="{{ old('capaian', $capaianTck->capaian) }}" />
              </div>
              <div class="d-flex justify-content-between">
                <a href="{{ route('capaian-tck.index') }}" class="btn btn-secondary">Kembali</a>
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
@section('scripts')
<script>
flatpickr("#tanggal_data", {
  altInput: true,
  altFormat: "j F Y",
  dateFormat: "Y-m-d",
  disableMobile: true
});
</script>
@endsection