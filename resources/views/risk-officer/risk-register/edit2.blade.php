@extends('layouts.default')
@section('dashboard')
<div class="row mb-5">
  <div class="col-12 d-flex align-items-center gap-3 position-relative">
    <div class="svg-icon svg-icon-secondary">
      @include('partials.icon-tool')
    </div>
    <h3 class="mb-0">Edit Risk Register</h3>
  </div>

  @if (session('success'))
  <div class="flash-message alert alert-success">
    {{ session('success') }}
  </div>
  @endif

  @if ($errors->any())
  <div class="flash-message alert alert-danger" id="flash-message">
    <ul class="list-col-md-2">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
</div>
<div class="row">
  <div class="col-12">
    <form class="card" method="POST" action="{{ route('risk-register.update', $riskRegister->id) }}" id="main-form">
      <input type="hidden" name="draft_key" value="{{ request()->draft_key }}">
      @csrf
      @method('PUT')
      <div class="card-header stepper pb-0 border-0">
        <nav class="nav nav-pills nav-fill">
          <a class="nav-link tab-pills" href="#">
            <span class="nav-item-circle-parent">
              <span class="nav-item-circle">1</span>
            </span>
            <span class="d-none d-lg-block">Data Risiko</span>
          </a>
          <a class="nav-link tab-pills" href="#">
            <span class="nav-item-circle-parent">
              <span class="nav-item-circle">2</span>
            </span>
            <span class="d-none d-lg-block">Penyebab Risiko</span>
          </a>
          <a class="nav-link tab-pills" href="#">
            <span class="nav-item-circle-parent">
              <span class="nav-item-circle">3</span>
            </span>
            <span class="d-none d-lg-block">Key Risk Indicator</span>
          </a>
          <a class="nav-link tab-pills" href="#">
            <span class="nav-item-circle-parent">
              <span class="nav-item-circle">4</span>
            </span>
            <span class="d-none d-lg-block">Kontrol</span>
          </a>
        </nav>
      </div>
      <div class="card-body">
        <!-- Target -->
        <div class="tab d-none">
          <div class="row d-lg-none mb-4">
            <div class="col">
              <h3>Target</h3>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="divider mb-3 mb-md-5 mt-0">
                <div class="divider-text">
                  <h5 class="mb-0 ff-heading-sm">Periode Tahun {{ $periode->tahun }}</h5>
                </div>
              </div>
            </div>
          </div>
          <div class="row g-3 gx-md-5">
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Target Capaian Kinerja</label>
                <select class="form-select select2 js-select-hide-search" name="target_capaian_kinerja" required>
                  <option selected disabled>Pilih</option>
                  @foreach($tck as $id => $title)
                  <option value="{{ $id }}" {{ old('target_capaian_kinerja') == $id ? 'selected' : '' }}>
                    {{ $title }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Rencana Kegiatan</label>
                <textarea class="form-control" id="deskripsi_rencana_kegiatan" name="deskripsi_rencana_kegiatan" rows="3" value="{{ old('deskripsi_rencana_kegiatan') }}" required></textarea>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Kategori Risiko</label>
                <select class="form-select select2 js-select-hide-search" name="kategori_risiko_id" id="kategori_risiko"
                  required>
                  <option selected disabled>Pilih</option>
                  @foreach($kategoriRisiko as $id => $title)
                  <option value="{{ $id }}" {{ old('kategori_risiko_id') == $id ? 'selected' : '' }}>{{ $title }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Jenis Risiko</label>
                <select class="form-select select2 js-select-hide-search" name="jenis_risiko_id" id="jenis_risiko"
                  required>
                  <option selected disabled>Pilih</option>
                  <!-- Options will be populated dynamically based on selected Kategori Risiko -->
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Peristiwa Risiko</label>
                <select class="form-select select2 js-select-hide-search" name="peristiwa_risiko_id"
                  id="peristiwa_risiko" required>
                  <option selected disabled>Pilih</option>
                  <!-- Options will be populated dynamically based on selected Jenis Risiko -->
                </select>
              </div>
            </div>
            <div class="col-md-6 d-flex align-items-center">
              <label class="form-label col-lg-4 col-xxl-3 mb-0 me-4 me-md-2">Type</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="type_umum" value="Umum"
                  {{ old('type', 'Umum') === 'Umum' ? 'checked' : '' }}>
                <label class="form-check-label" for="type_umum">Umum</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="type" id="type_medis" value="Medis"
                  {{ old('type') === 'Medis' ? 'checked' : '' }}>
                <label class="form-check-label" for="type_medis">Medis</label>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group form-floating">
                <textarea class="form-control" id="deskripsi_peristiwa_risiko" name="deskripsi_peristiwa_risiko"
                  rows="3" value="{{ old('deskripsi_peristiwa_risiko') }}" placeholder="Deskripsi Peristiwa Risiko"
                  required></textarea>
                <label for="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</label>
              </div>
            </div>
          </div>
        </div>

        <!-- Penyebab Risiko -->
        <div class="tab d-none">
          <div class="row align-items-center d-lg-none mb-4">
            <div class="col">
              <h3 class="mb-0">Penyebab Risiko</h3>
            </div>
          </div>
          <div id="penyebab-risiko-body">
          </div>
          <div class="row">
            <div class="col-auto ms-auto">
              <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-column"
                data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah Penyebab Risiko">
                <i class='bx bx-plus fs-5'></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Key Risk Indicator -->
        <div class="tab d-none">
          <div class="row align-items-center d-lg-none mb-4">
            <div class="col">
              <h3 class="mb-0">Key Risk Indicator</h3>
            </div>
          </div>
          <div id="kri-body"></div>
          <div class="row">
            <div class="col-auto ms-auto d-flex">
              <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-column-kri"
                data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah KRI">
                <i class='bx bx-plus fs-5'></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Kontrol Eksisting -->
        <div class="tab d-none">
          <div class="row align-items-center d-lg-none mb-4">
            <div class="col">
              <h3 class="mb-0">Kontrol</h3>
            </div>
          </div>
          <div class="row gy-3 gx-xxl-6 mb-3">
            <div class="col-md-6 col-lg-5 col-xxl-6">
              <div class="form-group form-floating">
                <textarea class="form-control" id="kontrol_eksisting" name="kontrol_eksisting" rows="4"
                  placeholder="Kontrol Eksisting" value="{{ old('kontrol_eksisting') }}"></textarea>
                <label for="kontrol_eksisting" class="form-label">Kontrol Eksisting</label>
              </div>
            </div>
            <div class="col-md-6 col-lg-7 col-xxl-6">
              <div class="form-group d-lg-flex mb-4">
                <label class="form-label label-lg-start col-lg-5 col-xl-4">Penilaian Efektivitas Kontrol</label>
                <select class="form-select select2" name="penilaian_efektifitas_kontrol">
                  <option selected disabled>Penilaian Efektivitas Kontrol</option>
                  @foreach($riskSetting as $id => $efektivitas_control)
                  <option value="{{ $efektivitas_control }}"
                    {{ old('penilaian_efektifitas_kontrol') == $efektivitas_control ? 'selected' : '' }}>
                    {{ $efektivitas_control }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group d-lg-flex">
                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker2">Perkiraan Waktu
                  Terpapar Risiko</label>
                <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko" id="timepicker2"
                  type="text" placeholder="d/m/y to d/m/y" value="{{ old('perkiraan_waktu_terpapar_risiko') }}" />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer stepper-footer pt-0 border-0 d-block">
        <div class="row g-2">
          <div class="col-auto order-0 px-0 px-md-1">
            <button type="button" id="back_button" class="btn btn-outline-info btn-arrow-left">Back</button>
          </div>
          <div class="col-6 col-md-auto order-2 order-md-last ms-auto d-flex">
            <button type="button" id="next_button" class="btn btn-submit btn-arrow-right ms-auto">Next</button>
          </div>
          <div class="col-auto order-1">
            <a href="{{ route('risk-register.index') }}" class="btn btn-outline-secondary">Batal</a>
          </div>
          <div class="col-auto order-3 px-0 px-md-1 d-flex">
            <button type="button" id="save_button" class="btn btn-danger ms-auto" onclick="submitForm()">Save</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection
@section('scripts')
<script>
const initDraftLogic = () => {
  const draft = @json((object)($riskRegister->toDraftStructure()));
  const saveAsDraft = () => {
    Swal.fire({
      title: 'Simpan sebagai draft?',
      text: 'Data draft tidak akan tersedia di daftar risiko sebelum disimpan secara resmi. Lanjutkan?',
      icon: 'warning',
      iconHtml: '<i class="bx bx-pencil"></i>',
      showCancelButton: true,
      confirmButtonText: 'Ya',
      cancelButtonText: 'Tidak',
    }).then((result) => {
      if (result.isConfirmed) {
        const formData = new FormData(document.getElementById('main-form'));

        if (
          !$('[name=deskripsi_peristiwa_risiko]').val()?.trim() ||
          !$('[name=target_capaian_kinerja]').val()?.trim() ||
          !$('[name=deskripsi_rencana_kegiatan]').val()?.trim() ||
          !$('[name=kategori_risiko_id]').val()?.trim() ||
          !$('[name=jenis_risiko_id]').val()?.trim() ||
          !$('[name=peristiwa_risiko_id]').val()?.trim()
        ) {
          Swal.fire('Error', 'Semua input data risiko wajib diisi sebelum menyimpan draft.', 'error');
          return;
        }

        if ($('#main-form [name=draft_key]').val()) {
          formData.append('draft_key', $('#main-form [name=draft_key]').val());
        }

        $.ajax({
          url: '{{ route('risk-register.store-as-draft') }}',
          type: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
            if (response.success) {
              Swal.fire('Sukses', response.message, 'success');
              $('#main-form [name=draft_key]').val(response.key);
            } else {
              Swal.fire('Kesalahan', response.message, 'error');
            }
          },
          error: function(xhr, status, error) {
            Swal.fire('Error', xhr.responseJSON.error || 'Terjadi kesalahan. Silahkan ulangi lagi',
              'error');
          }
        });
      }
    });
  }

  Object.keys(draft).forEach((key) => {
    if (key === 'jenis_risiko_id' || key === 'peristiwa_risiko_id') {
      $(`[name="${key}"]`).data('value', draft[key]);
    } else if (key === 'type') {
      $(`[name="${key}"][value="${draft[key]}"]`).prop('checked', true);
    } else if (key === 'penyebab_risiko') {
      draft[key].forEach((value) => {
        $('#add-column').click();
        $('[name="penyebab_risiko[]"]').last().val(value);
      });
    } else if (key === 'key_risk_indicator') {
      const keyRiskIndicator = draft[key];
      const satuanKri = draft['satuan_kri'];
      const batasAman = draft['batas_aman'];
      const batasSiaga = draft['batas_waspada'];
      const batasBahaya = draft['batas_bahaya'];
      draft[key].forEach((value, idx) => {
        $('#add-column-kri').click();
        $('[name="key_risk_indicator[]"]').last().val(value !== undefined ? value : '');
        $('[name="satuan_kri[]"]').last().val(satuanKri[idx] !== undefined ? satuanKri[idx] : '');
        $('[name="batas_aman[]"]').last().val(batasAman[idx] !== undefined ? batasAman[idx] : '');
        $('[name="batas_waspada[]"]').last().val(batasSiaga[idx] !== undefined ? batasSiaga[idx] : '');
        $('[name="batas_bahaya[]"]').last().val(batasBahaya[idx] !== undefined ? batasBahaya[idx] : '');
      });
    } else if (key === 'perkiraan_waktu_terpapar_risiko') {
      flatpickrIns.setDate(draft[key].split(' to '));
    } else {
      $(`[name="${key}"]`).val(draft[key]).change();
    }
  });

  $('#draft_button').on('click', saveAsDraft);
}

function submitForm() {
  Swal.fire({
    title: "Apakah Anda yakin?",
    text: "Data akan disimpan ke dalam database",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, simpan!",
    cancelButtonText: "Tidak, batal",
  }).then((result) => {
    if (result.isConfirmed) {
      $("form").submit();
    }
  });
}
var current;
var tabs;
var tabs_pill;

function validateCurrentTab() {
  var isValid = true;
  var currentTab = $(tabs[current]);

  currentTab.find(':input[required]').each(function() {
    if ($(this).val() === '') {
      isValid = false;
      showFlashMessage('Please fill out all required fields.', 'danger');
      return false;
    }
  });

  return isValid;
}

function validateAllTabs() {
  var allValid = true;

  tabs.each(function(index, tab) {
    $(tab).find(':input[required]').each(function() {
      if ($(this).val() === '') {
        allValid = false;
        return false;
      }
    });

    if (!allValid) {
      return false;
    }
  });

  return allValid;
}

function showFlashMessage(message, type) {
  var flashMessage = $('#flash-message');
  flashMessage.removeClass('d-none').addClass('alert-' + type);
  $('#flash-message-text').text(message);
  setTimeout(hideFlashMessage, 5000);
}

function hideFlashMessage() {
  $('#flash-message').addClass('d-none').removeClass('alert-success alert-danger');
}


/**
 * why...?
$(document).ready(function() {
  loadFormData(current);

  function loadFormData(n) {
    $(tabs_pill[n]).addClass("active");
    $(tabs[n]).removeClass("d-none");
    $("#back_button").attr("disabled", n == 0 ? true : false);
    n == tabs.length - 1 ?
      $("#next_button").text("Save") :
      $("#next_button")
      .attr("type", "button")
      .text("Next")
      .attr("onclick", "next()");
    toggleSubmitButton();
  }

  function next() {
    if (!validateCurrentTab()) {
      return;
    }

    if (current < tabs.length - 1) {
      $(tabs[current]).addClass("d-none");
      $(tabs_pill[current]).removeClass("active");

      current++;
      loadFormData(current);
    } else {
      // Show the modal instead of submitting the form
      var emptyFields = getEmptyFields();
      if (emptyFields.length > 0) {
        $('#empty-fields-list').empty();
        emptyFields.forEach(function(field) {
          $('#empty-fields-list').append('<li>' + field + '</li>');
        });
        $('#empty-fields-alert').removeClass('d-none');
      } else {
        $('#empty-fields-alert').addClass('d-none');
      }
      $('#confirmationModal').modal('show');
    }
  }

  function back() {
    if (current > 0) {
      $(tabs[current]).addClass("d-none");
      $(tabs_pill[current]).removeClass("active");

      current--;
      loadFormData(current);
    }
  }

  $('#next_button').on("click", next);
  $('#back_button').on("click", back);

  $('#confirm-submit').on("click", function() {
    $('#confirmationModal').modal('hide');
    $('form').submit();
  });

});
  */
</script>
<script>
$(document).ready(function() {
  // Handle change event on Kategori Risiko dropdown
  $('#kategori_risiko').change(function() {
    var kategoriRisikoId = $(this).val();
    if (kategoriRisikoId) {
      // Fetch Jenis Risiko options based on selected Kategori Risiko
      $.ajax({
        url: '/get-jenis-risiko/' + kategoriRisikoId,
        type: 'GET',
        success: function(data) {
          // Clear previous options and add new options
          $('#jenis_risiko').empty();
          $('#jenis_risiko').append('<option selected disabled>Jenis Risiko</option>');
          $.each(data, function(key, value) {
            $('#jenis_risiko').append('<option value="' + key + '">' + value + '</option>');
          });

          if ($('#jenis_risiko').data('value')) {
            $('#jenis_risiko').val($('#jenis_risiko').data('value')).change();
            $('#jenis_risiko').data('value', null);
          } else {
            $('#jenis_risiko').val(null).change();
          }

          // Clear Peristiwa Risiko options
          $('#peristiwa_risiko').empty();
          $('#peristiwa_risiko').append('<option selected disabled>Peristiwa Risiko</option>');
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', error);
        }
      });
    } else {
      // Clear Jenis Risiko and Peristiwa Risiko options if no Kategori Risiko is selected
      $('#jenis_risiko').empty();
      $('#peristiwa_risiko').empty();
    }
  });

  // Handle change event on Jenis Risiko dropdown
  $('#jenis_risiko').change(function() {
    var jenisRisikoId = $(this).val();
    if (jenisRisikoId) {
      // Fetch Peristiwa Risiko options based on selected Jenis Risiko
      $.ajax({
        url: '/get-peristiwa-risiko/' + jenisRisikoId,
        type: 'GET',
        success: function(data) {
          // Clear previous options and add new options
          $('#peristiwa_risiko').empty();
          $('#peristiwa_risiko').append('<option selected disabled>Peristiwa Risiko</option>');
          $.each(data.peristiwaRisiko, function(key, value) {
            $('#peristiwa_risiko').append('<option value="' + key + '">' + value + '</option>');
          });

          if ($('#peristiwa_risiko').data('value')) {
            $('#peristiwa_risiko').val($('#peristiwa_risiko').data('value')).change();
            $('#peristiwa_risiko').data('value', null);
          } else {
            $('#peristiwa_risiko').val(null).change();
          }
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', error);
        }
      });
    } else {
      // Clear Peristiwa Risiko options if no Jenis Risiko is selected
      $('#peristiwa_risiko').empty();
    }
  });
});
</script>
<script>
var today = new Date();
var endOfYear = new Date(today.getFullYear(), 11, 31); // Mendapatkan tanggal terakhir dalam tahun ini

var flatpickrIns = flatpickr("#timepicker2", {
  mode: "range",
  altInput: true,
  altFormat: "j F Y",
  dateFormat: "d/m/Y",
  //maxDate: endOfYear,
  disableMobile: true
});

/**
 * why...?
$(document).ready(function() {
  // Fungsi untuk menangani klik tombol "Next"
  function next() {
    // Mengambil nilai tanggal yang dipilih
    var selectedDates = $("#timepicker2").val();

    // Memisahkan tanggal awal dan akhir
    var datesArray = selectedDates.split(" to ");
    var startDate = datesArray[0];
    var endDate = datesArray[1];

    // Menyimpan nilai tanggal ke dalam variabel atau mengirimkannya ke server
    var perkiraan_waktu_terpapar_risiko_mulai = startDate;
    var perkiraan_waktu_terpapar_risiko_akhir = endDate;

    // Melanjutkan ke tab berikutnya atau menyimpan data ke server
    // Implementasikan logika sesuai kebutuhan Anda di sini
  }

  // Fungsi untuk menangani klik tombol "Back"
  function back() {
    // Kembali ke tab sebelumnya
    // Implementasikan logika sesuai kebutuhan Anda di sini
  }

  // Menambahkan event listener untuk tombol "Next"
  $("#next_button").on("click", next);

  // Menambahkan event listener untuk tombol "Back"
  $("#back_button").on("click", back);
});
*/

$(document).ready(function() {
  let columnCounter = 0;

  $("#add-column-kri").click(function() {
    // Create a unique row ID
    let rowId = 'row-' + columnCounter;

    // Append a new KRI row with inputs and delete button
    $("#kri-body").append(
      '<div class="row g-2" id="' + rowId + '">' +
      '<div class="col-12 col-lg-11">' +
      '<div class="row g-2">' +
      '<div class="col-12"><div class="form-group form-floating"><input type="text" class="form-control" name="key_risk_indicator[]" id="key_risk_indicator_' +
      columnCounter + '" placeholder="Key Risk Indicator">' +
      '<label for="key_risk_indicator_' + columnCounter + '">Key Risk Indicator</label></div></div>' +
      '<div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating"><input type="text" class="form-control" name="satuan_kri[]" id="satuan_kri_' +
      columnCounter + '" placeholder="Satuan KRI">' +
      '<label for="satuan_kri_' + columnCounter + '">Satuan KRI</label></div></div>' +
      '<div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating text-center"><input type="text" class="form-control border-success" name="batas_aman[]" id="batas_aman_' +
      columnCounter + '" placeholder="Batas Aman">' +
      '<label for="batas_aman_' + columnCounter + '">Batas Aman</label></div></div>' +
      '<div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating text-center"><input type="text" class="form-control border-warning" name="batas_waspada[]" id="batas_waspada_' +
      columnCounter + '" placeholder="Batas Siaga">' +
      '<label for="batas_waspada_' + columnCounter + '">Batas Siaga</label></div></div>' +
      '<div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1"><div class="form-group form-floating text-center"><input type="text" class="form-control border-danger" name="batas_bahaya[]" id="batas_bahaya_' +
      columnCounter + '" placeholder="Batas Bahaya">' +
      '<label for="batas_bahaya_' + columnCounter + '">Batas Bahaya</label></div></div>' +
      '</div>' +
      '</div>' +
      '<div class="col-auto d-flex align-items-center ms-auto">' +
      '<button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(\'' + rowId + '\')">' +
      '<i class="bx bx-trash"></i></button></div>' +
      '<div class="col-12 mt-0"><hr></div>' +
      '</div>'
    );

    columnCounter++;
  });

  // Add other row handling functions
  $("#add-column").click(function() {
    let rowId = 'row-' + columnCounter;
    $("#penyebab-risiko-body").append(
      '<div class="row g-2" id="' + rowId + '">' +
      '<div class="col"><div class="form-floating">' +
      '<input type="text" class="form-control" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko">' +
      '<label>Penyebab Risiko</label></div></div>' +
      '<div class="col-auto d-flex align-items-center">' +
      '<button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(\'' + rowId + '\')">' +
      '<i class="bx bx-trash"></i></button></div>' +
      '<div class="col-12 mt-0"><hr></div>' +
      '</div>'
    );

    columnCounter++;
  });

  // Function to remove a row
  window.removeRow = function(rowId) {
    $("#" + rowId).remove();
  };
});

$(document).ready(function() {
  current = 0;
  tabs = $(".tab");
  tabs_pill = $(".tab-pills");

  if ($('.flash-message').length) {
    setTimeout(hideFlashMessage, 5000);
  }

  function toggleSubmitButton() {
    if (validateAllTabs()) {
      $('#next_button').prop('disabled', false);
    } else {
      $('#next_button').prop('disabled', true);
    }
  }

  function getEmptyFields() {
    var emptyFields = [];

    tabs.each(function(index, tab) {
      $(tab).find(':input[required]').each(function() {
        if ($(this).val() === '') {
          var label = $(this).closest('.form-group, .mb-3').find('label').text();
          emptyFields.push(label);
        }
      });
    });

    return emptyFields;
  }

  $('input, select, textarea').on("change keyup", toggleSubmitButton);
  toggleSubmitButton();

  function loadFormData(n) {
    $(tabs_pill[n]).addClass("active");
    $(tabs[n]).removeClass("d-none");
    $("#back_button").attr("disabled", n == 0);
    $("#next_button").text(n == tabs.length - 1 ? "Save" : "Next") // Mengubah teks tombol menjadi "Save"
  }

  loadFormData(current);

  function next() {
    if ($(tabs[current]).find('input').length === 0) {
      Swal.fire('Data Kosong', 'Mohon tambahkan setidaknya satu ' + (current === 1 ? 'penyebab risiko' : 'KRI'), 'error');
      return;
    } else if ($(tabs[current]).find('input,textarea,select').filter(function() {
        const value = $(this).val()?.trim();
        return value === '' || value === null || value === undefined;
      }).length > 0) {
      Swal.fire('Masih ada data kosong', 'Mohon isi semua field yang tersedia', 'error');
      return;
    }

    if (current < tabs.length - 1) {

      $(tabs[current]).addClass("d-none");
      $(tabs_pill[current]).removeClass("active");

      current++;
      loadFormData(current);
    } else {
      Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Data akan disimpan ke dalam database",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, simpan!",
        cancelButtonText: "Tidak, batal",
      }).then((result) => {
        if (result.isConfirmed) {
          $("form").submit();
        }
      });
    }
  }

  function back() {
    if (current > 0) {
      $(tabs[current]).addClass("d-none");
      $(tabs_pill[current]).removeClass("active");

      current--;
      loadFormData(current);
    }
  }

  // Menambahkan event listener untuk tombol "Next"
  $("#next_button").on("click", next);

  // Menambahkan event listener untuk tombol "Back"
  $("#back_button").on("click", back);
});

$(document).ready(function() {
  $('.js-example-basic-single').select2();
  $('.js-example-basic-multiple').select2();
  initDraftLogic();

  $('button').on('click', function() {
    $(this)
      .blur();
  });

});
</script>



@endsection
