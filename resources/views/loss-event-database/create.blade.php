@extends('layouts.default')
@section('dashboard')
@if ($errors->any())
<div class="alert alert-warning d-lg-inline-block py-3">
  <div class="d-flex text-warning align-items-center gap-2 mb-2">
    <i class="bx bx-error-circle fs-3"></i>
    <h5 class="text-warning mb-0">Warning!</h5>
  </div>
  <ul class="mb-0 list-col-md-2">
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif
<div class="row">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex flex-between-center">
        <h3>Create Loss Event Database</h3>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <form class="needs-validation" novalidate="" method="POST" action="{{ route('loss-event-database.store') }}">
          @csrf
          <div class="row gy-4 gy-lg-3 gx-8">
            <div class="col-12">
              <div class="form-group row gx-0">
                <label class="form-label label-lg-start col-lg-2 me-0 me-lg-7" for="peristiwa_kerugian">Peristiwa
                  Kerugian</label>
                <div class="col has-validation">
                  <textarea class="form-control" name="peristiwa_kerugian" id="peristiwa_kerugian" rows="3"
                    placeholder="Isi peristiwa kerugian" required>{{ old('peristiwa_kerugian') }}</textarea>
                  <div class="invalid-feedback">Silakan isi peristiwa kerugian.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row gx-5">
                <label class="form-label label-lg-start col-lg-5 me-0" for="timepicker2">Rentang Waktu Kejadian
                  Risiko</label>
                <div class="col-lg-7 has-validation">
                  <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko" id="timepicker2"
                    type="text" placeholder="d/m/y to d/m/y" value="{{ old('perkiraan_waktu_terpapar_risiko') }}"
                    required />
                  <div class="invalid-feedback">Silakan isi rentang waktu kejadian risiko.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row gx-5 has-validation">
                <label class="form-label label-lg-start col-lg-5 me-0">Kategori Risiko</label>
                <div class="col-lg-7 has-validation">
                  <select class="form-select js-select-hide-search" name="kategori_risiko_id" id="kategori_risiko"
                    required>
                    <option selected disabled>Pilih kategori risiko</option>
                    @foreach($kategoriRisiko as $id => $title)
                    <option value="{{ $id }}" {{ old('kategori_risiko_id') == $id ? 'selected' : '' }}>{{ $title }}
                    </option>
                    @endforeach
                  </select>
                  <div class="invalid-feedback">Silakan pilih kategori risiko.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row gx-5 has-validation">
                <label class="form-label label-lg-start col-lg-5 me-0">Jenis Risiko</label>
                <div class="col-lg-7 has-validation">
                  <select class="form-select js-select-hide-search" name="jenis_risiko_id" id="jenis_risiko" required>
                    <option selected disabled>Pilih jenis risiko</option>
                    <!-- Options will be populated dynamically based on selected Kategori Risiko -->
                  </select>
                  <div class="invalid-feedback">Silakan pilih jenis risiko.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row gx-5 has-validation">
                <label class="form-label label-lg-start col-lg-5 me-0" for="nilai_kerugian_finansial">Nilai Kerugian
                  Finansial</label>
                <div class="col-lg-7 has-validation">
                  <input class="form-control" id="nilai_kerugian_finansial" name="nilai_kerugian_finansial" type="text"
                    placeholder="Input Numeral" value="{{ old('nilai_kerugian_finansial') }}" required />
                  <div class="invalid-feedback">Silakan isi nilai kerugian finansial.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row gx-5 has-validation">
                <label class="form-label label-lg-start col-lg-5 me-0" for="nilai_kerugian_non_fungsional">Nilai
                  Kerugian Non Finansial</label>
                <div class="col-lg-7 has-validation">
                  <input class="form-control" name="nilai_kerugian_non_fungsional" type="text" placeholder="Input"
                    value="{{ old('nilai_kerugian_non_fungsional') }}" required />
                  <div class="invalid-feedback">Silakan isi nilai kerugian finansial.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group row gx-5 has-validation">
                <label class="form-label label-lg-start col-lg-5 me-0" for="unit_penanggung_jawab">Unit Penanggung
                  Jawab</label>
                <div class="col-lg-7 has-validation">
                  <input class="form-control" name="unit_penanggung_jawab" type="text" placeholder="PIC" required />
                  <div class="invalid-feedback">Silakan isi PIC.</div>
                </div>
              </div>
            </div>
            <div class="col-12 py-4">
              <hr>
            </div>
            <div class="col-12 d-flex flex-between-center">
              <h6>Rekomendasi Perbaikan yang perlu ditindaklanjuti</h6>
            </div>
            <div class="col-12">
              <ol id="rekomendasi-body" class="list-input">
                <li id="row-0">
                  <div class="d-flex align-items-center gap-3">
                    <input type="text" class="form-control" name="rekomendasi_perbaikan[]"
                      placeholder="Masukkan Rekomendasi Perbaikan" required>
                    <button type="button" class="btn btn-muted-danger p-2" onclick="removeRow('row-0')">
                      <i class="bx bx-trash"></i>
                    </button>
                  </div>
                </li>
              </ol>
            </div>
            <div class="col-auto ms-auto mt-0">
              <button type="button" class="btn btn-outline-primary btn-icon" id="add-column" data-bs-toggle="tooltip"
                data-bs-placement="left" title="Tambah Rekomendasi">
                <i class="bx bx-plus"></i>
              </button>
            </div>
          </div>
          <div class="d-flex gap-1">
            <button type="submit" class="btn btn-submit">Simpan</button>
            <a href="{{ route('loss-event-database.index') }}" class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- <div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="{{ route('loss-event-database.index') }}" class="fs-2">Loss Event
            Database</a></li>
        <li class="breadcrumb-item active fs-2" aria-current="page">Create</li>
      </ol>
    </nav>
    <div class="card mb-3 btn-reveal-trigger">
      <div class="card-header position-relative min-vh-25">
        <form method="POST" action="{{ route('loss-event-database.store') }}">
          @csrf
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label" for="peristiwa_kerugian">Peristiwa Kerugian</label>
                <textarea class="form-control" name="peristiwa_kerugian" id="peristiwa_kerugian" cols="30" rows="10"
                  placeholder="Input Peristiwa Kerugian">{{ old('peristiwa_kerugian') }}</textarea>
              </div>
              <div class="form-group">
                <label class="form-label" for="timepicker2">Rentang Waktu Kejadian Risiko</label>
                <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko" id="timepicker2"
                  type="text" placeholder="d/m/y to d/m/y" value="{{ old('perkiraan_waktu_terpapar_risiko') }}" />
              </div>
              {{--
                                <div class="form-group" style="display: none;">
                                    <label class="form-label" for="tanggal_kejadian">Tanggal Kejadian</label>
                                    <input class="form-control" name="tanggal_kejadian" id="tanggal_kejadian" type="text" placeholder="Pilih Tanggal Kejadian" />
                                </div>
                                --}}
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Kategori Risiko</label>
                <select class="form-select" name="kategori_risiko_id" id="kategori_risiko">
                  <option selected disabled>Kategori Risiko</option>
                  @foreach($kategoriRisiko as $id => $title)
                  <option value="{{ $id }}" {{ old('kategori_risiko_id') == $id ? 'selected' : '' }}>{{ $title }}
                  </option>
                  @endforeach
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Jenis Risiko</label>
                <select class="form-select" name="jenis_risiko_id" id="jenis_risiko">
                  <option selected disabled>Pilih Jenis Risiko</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="nilai_kerugian_finansial">Nilai Kerugian Finansial</label>
                <input class="form-control" id="nilai_kerugian_finansial" name="nilai_kerugian_finansial" type="text"
                  placeholder="Input Numeral" value="{{ old('nilai_kerugian_finansial') }}" />
              </div>
              <div class="form-group">
                <label class="form-label" for="nilai_kerugian_non_fungsional">Nilai Kerugian Non Finansial</label>
                <input class="form-control" name="nilai_kerugian_non_fungsional" type="text" placeholder="Input"
                  value="{{ old('nilai_kerugian_non_fungsional') }}" />
              </div>
              <div class="form-group">
                <label class="form-label" for="unit_penanggung_jawab">Unit Penanggung Jawab</label>
                <input class="form-control" name="unit_penanggung_jawab" type="text" placeholder="PIC"
                  value="{{ old('unit_penanggung_jawab') }}" />
              </div>
            </div>
            <div class="col-md-12" style="margin-top:20px;">
              <div class="row mb-3">
                <div class="col-auto ms-auto">
                  <button type="button" class="btn btn-primary" id="add-column">
                    <i class="fas fa-solid fa-plus"></i> Tambah Rekomendasi
                  </button>

                </div>
              </div>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th><strong>Rekomendasi Perbaikan yang perlu ditindaklanjuti</strong></th>
                  </tr>
                </thead>
                <tbody id="rekomendasi-body">
                  @php
                  $row_id = 0;
                  @endphp
                  @if(old('rekomendasi_perbaikan'))
                  @foreach(old('rekomendasi_perbaikan') as $rekomendasi)
                  <tr id="row-{{ $row_id }}">'
                    <td class="col-md-12">
                      <input type="text" class="form-control" name="rekomendasi_perbaikan[]"
                        placeholder="Masukkan Rekomendasi Perbaikan" value="{{ $rekomendasi }}">
                    </td>
                    <td>
                      <button type="button" class="btn btn-danger" onclick="removeRow('row-{{ $row_id }}')"><i
                          class="fas fa-trash"></i></button>
                    </td>
                  </tr>
                  @php
                  $row_id++;
                  @endphp
                  @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
          <div class="d-flex justify-content-between">
            <a href="{{ route('loss-event-database.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div> -->
@endsection
@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
  const rupiahFields = document.querySelectorAll("#nilai_kerugian_finansial");
  rupiahFields.forEach(field => {
    field.addEventListener("input", function(e) {
      let value = e.target.value;
      value = value.replace(/[^,\d]/g, "").toString();
      let split = value.split(",");
      let sisa = split[0].length % 3;
      let rupiah = split[0].substr(0, sisa);
      let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

      if (ribuan) {
        let separator = sisa ? "." : "";
        rupiah += separator + ribuan.join(".");
      }

      rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
      e.target.value = rupiah;
    });
  });
});
</script>
<script>
var today = new Date();
var endOfYear = new Date(today.getFullYear(), 11, 31); // Mendapatkan tanggal terakhir dalam tahun ini

flatpickr("#timepicker2", {
  mode: "range",
  altInput: true,
  altFormat: "j F Y",
  dateFormat: "d/m/Y",
  disableMobile: true
});

var selectedDates = $("#timepicker2").val();

// Memisahkan tanggal awal dan akhir
var datesArray = selectedDates.split(" to ");
var startDate = datesArray[0];
var endDate = datesArray[1];

// Menyimpan nilai tanggal ke dalam variabel atau mengirimkannya ke server
var rentang_kejadian_awal = startDate;
var rentang_kejadian_akhir = endDate;

$(document).ready(function() {
  $('.js-example-basic-single').select2();
  $('.js-example-basic-multiple').select2();
});
</script>
<script>
var today = new Date();
var endOfYear = new Date(today.getFullYear(), 11, 31); // Mendapatkan tanggal terakhir dalam tahun ini

// Mendapatkan rentang tanggal perkiraan_waktu_terpapar_risiko
var perkiraan_waktu_terpapar_risiko = $("#timepicker2").val();
var perkiraan_waktu_terpapar_risiko_dates = perkiraan_waktu_terpapar_risiko.split(" to ");
var startDate = new Date(perkiraan_waktu_terpapar_risiko_dates[0]);
var endDate = new Date(perkiraan_waktu_terpapar_risiko_dates[1]);

flatpickr("#tanggal_kejadian", {
  altInput: true,
  altFormat: "j F Y",
  dateFormat: "d/m/Y",
  minDate: startDate,
  maxDate: endDate,
  disableMobile: true
});
</script>

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

  //handle add rekomendasi
  let columnCounter = "{{ $row_id }}" * 1;
  $("#add-column").click(function() {
    let rowId = 'row-' + columnCounter;
    $("#rekomendasi-body").append(
      '<li id="' + rowId + '">' +
      '<div class="d-flex align-items-center gap-3">' +
      '<input type="text" class="form-control" name="rekomendasi_perbaikan[]" placeholder="Masukkan Rekomendasi Perbaikan">' +
      '<button type="button" class="btn btn-muted-danger p-2" onclick="removeRow(\'' + rowId +
      '\')"><i class="bx bx-trash"></i></button>' +
      '</div>' +
      '</li>'
    );

    // $("#rekomendasi-body").append(
    //   '<tr id="' + rowId + '">' +
    //   '<td class="col-md-12"><input type="text" class="form-control" name="rekomendasi_perbaikan[]" placeholder="Masukkan Rekomendasi Perbaikan"></td>' +
    //   '<td><button type="button" class="btn btn-danger" onclick="removeRow(\'' + rowId +
    //   '\')"><i class="fas fa-trash"></i></button></td>' +
    //   '</tr>'
    // );

    columnCounter++;
  });

  window.removeRow = function(rowId) {
    $("#" + rowId).remove();
  };
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var oldJenisRisikoId = "{{ old('jenis_risiko_id') }}";
  var oldKategoriRisikoId = "{{ old('kategori_risiko_id') }}";

  // Populate Jenis Risiko options if there is an old value
  if (oldKategoriRisikoId) {
    fetchJenisRisiko(oldKategoriRisikoId, oldJenisRisikoId);
  }

  $('#kategori_risiko').change(function() {
    var kategoriRisikoId = $(this).val();
    if (kategoriRisikoId) {
      fetchJenisRisiko(kategoriRisikoId, null);
    } else {
      $('#jenis_risiko').empty();
      $('#jenis_risiko').append('<option selected disabled>Pilih Jenis Risiko</option>');
    }
  });
});

function fetchJenisRisiko(kategoriRisikoId, selectedJenisRisikoId) {
  $.ajax({
    url: '/get-jenis-risiko/' + kategoriRisikoId,
    type: 'GET',
    success: function(data) {
      $('#jenis_risiko').empty();
      $('#jenis_risiko').append('<option selected disabled>Pilih Jenis Risiko</option>');
      $.each(data, function(key, value) {
        var selected = (selectedJenisRisikoId == key) ? 'selected' : '';
        $('#jenis_risiko').append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
      });
    },
    error: function(xhr, status, error) {
      console.error('AJAX Error:', error);
    }
  });
}
</script>
@endsection