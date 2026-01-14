@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-md-7 col-lg-6">
    <form class="card needs-validation" novalidate="" method="POST"
      action="{{ route('peristiwa-risiko.update', $peristiwaRisiko) }}">
      @csrf
      @method('PUT')
      <div class="card-header d-flex justify-content-between">
        <div class="d-block col-10">
          <h2 class="h3 mb-2">Edit Peristiwa Risiko</h2>
          <span class="ff-heading-med">
            {{ $peristiwaRisiko->title }}
          </span>
        </div>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body p-xxl-5">
        <div class="row gx-0 gy-3">
          <div class="form-floating has-validation">
            <select class="form-select js-select-hide-search" name="kategori_risiko_id" id="kategori_risiko">
              <option selected disabled>-</option>
              @foreach($kategoriRisiko as $id => $title)
              <option value="{{ $id }}" {{ $peristiwaRisiko->kategori_risiko_id == $id ? 'selected' : '' }}>
                {{ $title }}</option>
              @endforeach
            </select>
            <label class="form-label">Kategori Risiko</label>
            <div class="invalid-feedback">Silakan pilih kategori risiko.</div>
          </div>
          <div class="form-floating has-validation">
            <select class="form-select js-select-hide-search" name="jenis_risiko_id" id="jenis_risiko">
              <option selected disabled>-</option>
            </select>
            <label class="form-label">Jenis Risiko</label>
            <div class="invalid-feedback">Silakan pilih jenis risiko.</div>
          </div>
          <div class="form-floating has-validation">
            <textarea class="form-control" id="title" name="title" type="text" rows="3" placeholder="Masukkan Peristiwa"
              value="{{ $peristiwaRisiko->title }}" required>{{ $peristiwaRisiko->title }}</textarea>
            <label for="title" for="title">Peristiwa Risiko</label>
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
    // console.log('Selected Kategori Risiko ID:', kategoriRisikoId);
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

          // Set the selected Jenis Risiko if it matches the existing value
          var selectedJenisRisikoId = '{{ $peristiwaRisiko->jenis_risiko_id }}';
          $('#jenis_risiko').val(selectedJenisRisikoId);
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

  // Trigger change event on Kategori Risiko dropdown to populate Jenis Risiko initially
  $('#kategori_risiko').trigger('change');
});
</script>
@endsection
