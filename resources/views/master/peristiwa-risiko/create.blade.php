@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-7 col-lg-6">
    <form class="card  needs-validation" novalidate="" method="POST" action="{{ route('peristiwa-risiko.store') }}">
      @csrf
      <div class="card-header d-flex flex-between-center">
        <h2 class="h3">Tambah Peristiwa Risiko Baru</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row gx-0 gy-3">
          <div class="form-floating has-validation">
            <select class="form-select js-select-hide-search" name="kategori_risiko_id" id="kategori_risiko" required>
              <option value="" selected disabled>---</option>
              @foreach($kategoriRisiko as $id => $title)
              <option value="{{ $id }}">{{ $title }}</option>
              @endforeach
            </select>
            <label class="form-label">Kategori Risiko</label>
            <div class="invalid-feedback">Silakan pilih kategori risiko.</div>
          </div>
          <div class="form-floating has-validation">
            <select class="form-select js-select-hide-search" name="jenis_risiko_id" id="jenis_risiko" required>
              <option value="" selected disabled>---</option>
              <!-- Options will be populated dynamically based on selected Kategori Risiko -->
            </select>
            <label class="form-label">Jenis Risiko</label>
            <div class="invalid-feedback">Silakan pilih jenis risiko.</div>
          </div>
          <div class="form-floating has-validation">
            <textarea class="form-control" id="title" name="title" type="text" rows="3" placeholder="Masukkan Peristiwa"
              value="{{ old('title') }}" required></textarea>
            <label for="title">Peristiwa Risiko</label>
            <div class="invalid-feedback">Peristiwa risiko wajib diisi.</div>
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('peristiwa-risiko.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
  // Handle change event on Kategori Risiko dropdown
  $('#kategori_risiko').change(function() {
    var kategoriRisikoId = $(this).val();
    console.log('Selected Kategori Risiko ID:', kategoriRisikoId);
    if (kategoriRisikoId) {
      // Fetch Jenis Risiko options based on selected Kategori Risiko
      $.ajax({
        url: '/get-jenis-risiko/' + kategoriRisikoId,
        type: 'GET',
        success: function(data) {
          console.log('Received Jenis Risiko data:', data);
          // Clear previous options and add new options
          $('#jenis_risiko').empty();
          $('#jenis_risiko').append('<option selected disabled>Pilih Jenis Risiko</option>');
          $.each(data, function(key, value) {
            $('#jenis_risiko').append('<option value="' + key + '">' + value + '</option>');
          });
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', error);
        }
      });
    } else {
      // Clear Jenis Risiko options if no Kategori Risiko is selected
      $('#jenis_risiko').empty();
    }
  });
});
</script>
@endsection