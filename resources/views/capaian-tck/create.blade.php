@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-header d-flex flex-between-center">
        <h3>Create Capaian TCK</h3>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('capaian-tck.store') }}">
        @csrf
        <div class="card-body">
          <div class="row gy-3 gx-md-8">
            <div class="col-md-6">
              <div class="d-flex">
                <label class="form-label label-start col-3 col-md-4">Periode</label>
                <select class="form-select js-select-hide-search" name="periode_id">
                  <option selected disabled>Select Periode</option>
                  @foreach ($periodes as $id => $name)
                  <option value="{{ $id }}">{{ $name }}</option>
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
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
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
                  value="{{ old('tanggal_data', date('Y-m-d')) }}" />
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex">
                <label class="form-label label-start col-3 col-md-4" for="capaian">Capaian TCK</label>
                <input class="form-control" id="capaian" name="capaian" type="number" step="0.01"
                  value="{{ old('capaian') }}" />
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
