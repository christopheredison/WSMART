@section('scripts')
<script>
const initDraftLogic = () => {
  const draft = @json((object)($draft ?? []));
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
      const batasWaspada = draft['batas_waspada'];
      const batasBahaya = draft['batas_bahaya'];
      draft[key].forEach((value, idx) => {
        $('#add-column-kri').click();
        $('[name="key_risk_indicator[]"]').last().val(value !== undefined ? value : '');
        $('[name="satuan_kri[]"]').last().val(satuanKri[idx] !== undefined ? satuanKri[idx] : '');
        $('[name="batas_aman[]"]').last().val(batasAman[idx] !== undefined ? batasAman[idx] : '');
        $('[name="batas_waspada[]"]').last().val(batasWaspada[idx] !== undefined ? batasWaspada[idx] : '');
        $('[name="batas_bahaya[]"]').last().val(batasBahaya[idx] !== undefined ? batasBahaya[idx] : '');
      });
    } else if (key === 'perkiraan_waktu_terpapar_risiko') {
      if (draft[key]) {
        flatpickrIns.setDate(draft[key].split(' to '));
      }
    } else {
      $(`[name="${key}"]`).val(draft[key]).change();
    }
  });

  $('#draft_button').on('click', saveAsDraft);
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
  let columnCounter = 2;

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
      columnCounter + '" placeholder="Batas Waspada">' +
      '<label for="batas_waspada_' + columnCounter + '">Batas Waspada</label></div></div>' +
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
      '<div class="col-12 mt-0"><hr></div>'
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