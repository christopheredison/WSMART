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
    <div class="card">
      <div class="card-header d-flex flex-between-center">
        <h3>Edit Loss Event Database</h3>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <form class="needs-validation" novalidate="" method="POST"
          action="{{ route('loss-event-database.update', $lossEvent->id) }}">
          @csrf
          @method('PUT')
          <div class="row gy-4 gy-lg-3 gx-lg-8">
            <div class="col-12">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-2" for="peristiwa_kerugian">Peristiwa Kerugian</label>
                <div class="col has-validation">
                  <textarea class="form-control" name="peristiwa_kerugian" id="peristiwa_kerugian" rows="3"
                    placeholder="Peristiwa Kerugian" required>{{ $lossEvent->peristiwa_kerugian }}</textarea>
                  <div class="invalid-feedback">Silakan isi peristiwa kerugian.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 me-3" for="timepicker2">Rentang Waktu Terpapar
                  Risiko</label>
                <div class="col has-validation">
                  <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko" id="timepicker2"
                    type="text"
                    value="{{ date('d/m/Y', strtotime($lossEvent->rentang_kejadian_awal)) }} to {{ date('d/m/Y', strtotime($lossEvent->rentang_kejadian_akhir)) }}"
                    placeholder="d/m/y to d/m/y" />
                  <div class="invalid-feedback">Silakan isi rentang waktu kejadian risiko.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 me-3">Kategori Risiko</label>
                <select class="form-select js-select-hide-search" name="kategori_risiko_id" id="kategori_risiko">
                  <option selected disabled>Kategori Risiko</option>
                  @foreach($kategoriRisiko as $id => $title)
                  <option value="{{ $id }}" @if($id==$lossEvent->kategori_risiko_id) selected @endif>{{ $title }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 me-3">Jenis Risiko</label>
                <select class="form-select js-select-hide-search" name="jenis_risiko_id" id="jenis_risiko">
                  <option selected disabled>Pilih Jenis Risiko</option>
                  @foreach($jenisRisiko as $id => $title)
                  <option value="{{ $id }}" @if($id==$lossEvent->jenis_risiko_id) selected @endif>{{ $title }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 me-3" for="nilai_kerugian_finansial">Nilai Kerugian
                  Finansial</label>
                <div class="col has-validation">
                  <input class="form-control" id="nilai_kerugian_finansial" name="nilai_kerugian_finansial" type="text"
                    value="{{ number_format($lossEvent->nilai_kerugian_finansial, 0, ',', '.') }}"
                    placeholder="Input Numeral" required />
                  <div class="invalid-feedback">Silakan isi nilai kerugian finansial.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 me-3" for="nilai_kerugian_non_fungsional">Nilai
                  Kerugian Non Finansial</label>
                <div class="col has-validation">
                  <input class="form-control" name="nilai_kerugian_non_fungsional" type="text"
                    value="{{ $lossEvent->nilai_kerugian_non_fungsional }}" placeholder="Input" required />
                  <div class="invalid-feedback">Silakan isi nilai kerugian finansial.</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 me-3" for="unit_penanggung_jawab">Unit Penanggung
                  Jawab</label>
                <div class="col has-validation">
                  <input class="form-control" name="unit_penanggung_jawab" type="text"
                    value="{{ $lossEvent->unit_penanggung_jawab }}" placeholder="PIC" required />
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
                @php
                $row_id = 0;
                @endphp
                @foreach($rekomendasi as $rek)
                <li id="row-{{ $row_id }}">
                  <div class="d-flex align-items-center gap-3">
                    <input type="text" class="form-control" name="rekomendasi_perbaikan[]"
                      placeholder="Masukkan Rekomendasi Perbaikan" value="{{ $rek->rekomendasi_perbaikan }}" required>
                    <button type="button" class="btn btn-muted-danger p-2" onclick="removeRow('row-{{ $row_id }}')">
                      <i class="bx bx-trash"></i>
                    </button>
                  </div>
                </li>
                @php
                $row_id++;
                @endphp
                @endforeach
              </ol>
            </div>
            <div class="col-auto ms-auto mt-0">
              <button type="button" class="btn btn-outline-primary btn-icon" id="add-column" data-bs-toggle="tooltip"
                data-bs-placement="left" title="Tambah Rekomendasi">
                <i class="bx bx-plus"></i>
              </button>
            </div>
          </div>
          <div class="d-flex gap-1 mt-5">
            <button type="submit" class="btn btn-submit">Update</button>
            <a href="{{ route('loss-event-database.index') }}" class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
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
  dateFormat: "d/m/Y",
  minDate: today,
  //maxDate: endOfYear,
  defaultDate: [
    "{{ $lossEvent->rentang_kejadian_awal }}",
    "{{ $lossEvent->rentang_kejadian_akhir }}"
  ],
  disableMobile: true,
  onChange: function(selectedDates, dateStr, instance) {
    var startDate = selectedDates[0];
    var endDate = selectedDates[1];
    var formattedStartDate = formatDate(startDate);
    var formattedEndDate = formatDate(endDate);

    $("#perkiraan_waktu_terpapar_risiko").val(formattedStartDate + " to " + formattedEndDate);
  }
});

function formatDate(date) {
  var day = date.getDate();
  var month = date.getMonth() + 1;
  var year = date.getFullYear();
  return day + "/" + month + "/" + year;
}

$(document).ready(function() {
  $('.js-example-basic-single').select2();
  $('.js-example-basic-multiple').select2();

  var startDate = flatpickr.parseDate("{{ $lossEvent->rentang_kejadian_awal }}", "d/m/Y");
  var endDate = flatpickr.parseDate("{{ $lossEvent->rentang_kejadian_akhir }}", "d/m/Y");

  flatpickr("#tanggal_kejadian", {
    dateFormat: "d/m/Y",
    minDate: startDate,
    maxDate: endDate,
    defaultDate: "{{ $lossEvent->tanggal_kejadian }}",
    disableMobile: true
  });

  //handle add rekomendasi
  let columnCounter = "{{ $rekomendasi->count() }}" * 1;
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

    columnCounter++;
  });

  window.removeRow = function(rowId) {
    $("#" + rowId).remove();
  };
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
});
</script>

@endsection