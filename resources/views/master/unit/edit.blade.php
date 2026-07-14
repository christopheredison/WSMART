@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-7">
    <form id="editUnitForm" class="card" method="POST" action="{{ route('unit.update', $unit) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="d-block col-10">
          <h2 class="h3 mb-2">Edit Unit</h2>
          <span class="ff-heading-med">{{ $unit->name }}</span>
        </div>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success" role="alert">
          {{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger" role="alert">
          <strong>Terjadi kesalahan:</strong>
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif
        <div class="row gx-0 gy-3">
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Tipe Unit</label>
            <select class="form-select js-select-hide-search" name="unit_type_id">
              <option selected disabled>Unit Type</option>
              @foreach($unitType as $id => $name)
              <option value="{{ $id }}" {{ (int) old('unit_type_id', $unit->unit_type_id) === (int) $id ? 'selected' : '' }}>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group d-none" id="unit_api_id_group">
            <label class="form-label label-md-start col-md-3">Unit ID</label>
            <input type="text" name="unit_api_id" id="unit_api_id" class="form-control" value="{{ $unit->unit_api_id }}">
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Nama Unit</label>
            <input type="text" id="name_display" class="form-control form-control-plain" value="{{ old('name', $unit->name) }}" disabled>
            <input type="hidden" name="name" id="name" value="{{ old('name', $unit->name) }}">
          </div>
          <div class="form-group d-none" id="parent_group">
            <label class="form-label label-md-start col-md-3">Parent</label>
            <select class="form-select js-select-hide-search" name="parent_id">
              <option selected disabled>Parent</option>
              @foreach($parent as $id => $name)
              <option value="{{ $id }}" {{ (int) old('parent_id', $unit->parent_id) === (int) $id ? 'selected' : '' }}>{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Valid From</label>
            <input class="form-control datetimepicker" name="valid_from" id="valid_from" type="text" placeholder="d/m/y" value="{{ old('valid_from', $unit->valid_from ? $unit->valid_from->format('d/m/Y') : '') }}">
          </div>
          <div class="form-group d-md-flex">
            <label class="form-label label-md-start col-md-3">Valid To</label>
            <input class="form-control datetimepicker" name="valid_to" id="valid_to" type="text" placeholder="d/m/y" value="{{ old('valid_to', $unit->valid_to ? $unit->valid_to->format('d/m/Y') : '') }}">
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('unit.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
@push('styles')
<style>
  /* Buat input readonly/disabled tampil seperti enabled */
  .form-control-plain:disabled { background-color: #fff !important; opacity: 1; color: inherit; }
  .flatpickr-input[readonly] { background-color: #fff !important; opacity: 1; color: inherit; }
</style>
@endpush
<script>
  // Samakan dengan halaman corporate-risk/create: gunakan flatpickr pada .datetimepicker
  flatpickr('#valid_from', {
    altInput: true,
    altFormat: 'j F Y',
    dateFormat: 'd/m/Y',
    disableMobile: true,
    allowInput: true
  });
  flatpickr('#valid_to', {
    altInput: true,
    altFormat: 'j F Y',
    dateFormat: 'd/m/Y',
    disableMobile: true,
    allowInput: true
  });

  $('#editUnitForm').on('submit', function(e) {
    const validToVal = $('#valid_to').val();
    if (validToVal) {
      const parts = validToVal.split('/');
      const validToDate = new Date(parts[2], parts[1] - 1, parts[0]);
      const today = new Date();
      today.setHours(0, 0, 0, 0);

      if (validToDate < today) {
        e.preventDefault();
        Swal.fire({
          title: 'Apakah anda yakin?',
          text: "Unit akan menjadi expired/invalid karena tanggal Valid To sudah terlewati.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Ya, Simpan!',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            this.submit();
          }
        });
      }
    }
  });
</script>
@endpush