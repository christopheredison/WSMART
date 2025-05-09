@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row mb-5">
  <div class="col-12">
    <div class="ff-preheading">Risk Register</div>
    <h2>Kuantifikasi/Analisa Risiko</h2>
  </div>
</div>
<div class="alert alert-warning mb-2">
    Harap isi semua field yang tersedia
</div>
<div class="row g-3 mb-3">
  <!-- Penyebab Risiko -->
  <div class="col-md-6">
    <div class="card btn-reveal-trigger">
      <div class="card-header border-0 pb-0">
        <h5>Penyebab Risiko</h5>
      </div>
      <div class="card-body">
        <div class="row g-1">
          @foreach ($penyebabRisiko as $penyebab)
          <div class="col-12">
            <div class="alert alert-warning m-0 rounded-3 font-base d-flex">
              <i class='bx bx-check-circle me-2 fs-6'></i>
              <div>{{ $penyebab->penyebab_risiko }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
  <!-- Deskripsi Peristiwa Risiko -->
  <div class="col-md-6">
    <div class="card btn-reveal-trigger">
      <div class="card-header border-0 pb-0">
        <h5>Deskripsi Peristiwa Risiko</h5>
      </div>
      <div class="card-body">
        <div>{{ $identifikasiRisiko->deskripsi_peristiwa_risiko }}</div>
      </div>
    </div>
  </div>
</div>
<form class="row g-3" method="POST" action="{{ route('risk-register-kuantifikasi.update', $riskRegister) }}">
  @csrf
  @method('PUT')
  <div class="col-12">
    <div class="card">
      <div class="card-header border-0 pb-0">
        <h5>Kategori Dampak</h5>
      </div>
      <div class="card-body row g-3 g-lg-6">
        <div class="col-md-3">
          <div class="form-group">
            <select class="form-select select2 js-select-hide-search" name="kategori_dampak" id="kategori_dampak" required>
              <option selected value="" disabled>Kategori Dampak</option>
              <option value="Finansial" {{ $riskAnalysis->kategori_dampak == 'Finansial' ? 'selected' : '' }}>
                Finansial</option>
              <option value="Non Finansial" {{ $riskAnalysis->kategori_dampak == 'Non Finansial' ? 'selected' : '' }}>
                Non Finansial</option>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="row align-items-md-center g-3 g-md-2">
            <div class="col-md-5 col-lg-4 d-flex align-items-center">
              <label class="form-label label-start pt-0">
                Area Dampak
              </label>
              <span class="badge bg-danger ms-2">{{ $identifikasiRisiko->type }}</span>
            </div>
            <div class="col">
              <select class="form-select select2 select2" name="area_dampak" required>
                <option selected value="" disabled>Area Dampak</option>
                @foreach($areaDampak as $id => $title)
                <option value="{{ $id }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-3 d-flex">
          <button type="button" class="btn btn-muted-primary d-flex align-items-center py-2 px-0 my-auto"
            id="btn-show-kriteria-dampak">
            <i class="bx bxs-info-circle me-2"></i>
            Lihat Kriteria Dampak
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header border-0 pb-0">
        <h5>Pengukuran Risiko Inheren</h5>
      </div>
      <div class="card-body">
        <div class="row g-2 g-lg-5">
          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" id="deskripsi_dampak" name="deskripsi_dampak" rows="3"
                placeholder="Deskripsi Dampak" required>{{ $riskAnalysis->deskripsi_dampak }}</textarea>
              <label for="">Deskripsi Dampak</label>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb-2">
              <label class="label-start col-5">Nilai Dampak</label>
              <input class="form-control text-center" name="nilai_dampak" id="nilai_dampak" type="text"
                placeholder="Isi Nilai Dampak" value="{{ number_format($riskAnalysis->nilai_dampak, 0, ',', '.') }}"
                min="0" required/>
            </div>
            <div class="d-flex">
              <label class="label-start col-5">Skala Dampak</label>
              <div class="text-center w-100">
                <select class="form-select js-select-hide-search" name="skala_dampak" id="skala_dampak" oninput="calculate2()" required>
                  <option value="" selected disabled>Skala Dampak</option>
                  @foreach($skalaDampak as $id => $tingkat)
                  <option value="{{ $tingkat }}" {{ $riskAnalysis->skala_dampak == $tingkat ? 'selected' : '' }}>
                    {{ $tingkat }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb-2">
              <label class="label-start col-5">Nilai Probabilitas (%)</label>
              <input class="form-control text-center" name="nilai_probabilitas" id="nilai_probabilitas" type="text"
                placeholder="Isi Nilai Probabilitas" min="0" max="100" value="{{ $riskAnalysis->nilai_probabilitas }}"
                oninput="validateProbabilitas(this)" required/>
            </div>
            <div class="d-flex">
              <label class="label-start col-5">Skala Probabilitas</label>
              <input class="form-control text-center" name="skala_probabilitas_id" type="text" placeholder="N/A"
                value="{{ $riskAnalysis->skala_probabilitas_id }}" id="skala_probabilitas_id" oninput="calculate2()"
                readonly />
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb-2">
              <label class="label-start col-5">Skala risiko</label>
              <input class="form-control text-center" name="skala_risiko" id="skala_risiko" type="number" placeholder="N/A"
                value="{{ $riskAnalysis->skala_risiko }}" readonly />
            </div>
            <div class="d-flex">
              <label class="label-start col-5">Level risiko</label>
              <input class="form-control text-center" name="level_risiko" id="level_risiko" type="text" placeholder="N/A"
                value="{{ $riskAnalysis->level_risiko }}" readonly />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 pt-3 d-flex gap-2">
    <button type="submit" class="btn btn-submit">Simpan</button>
    <a href="{{ route('risk-register.index') }}" class="btn btn-outline-secondary">Batal</a>
  </div>
</form>
<!-- Include modal.blade.php -->
@include('modal-kriteria-dampak-finansial')
@include('modal-kriteria-dampak-non-finansial')
@include('modal-kriteria-dampak-non-finansial-medis')
@include('modal-kriteria-dampak-finansial-medis')
@endsection
@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
  const rupiahFields = document.querySelectorAll("#nilai_dampak");
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
function validateProbabilitas(input) {
  //alert("aa");
  const value = parseInt(input.value);
  if (value < 0 || value > 100 || isNaN(value)) {
    input.setCustomValidity("Nilai harus antara 0 dan 100");
  } else {
    input.setCustomValidity("");
  }
}
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const kategoriDampakSelect = document.getElementById('kategori_dampak');
  const deskripsiDampakTextarea = document.getElementById('deskripsi_dampak');
  const nilaiDampakInput = document.getElementById('nilai_dampak');

  // Check initial value on page load
  checkDeskripsiDampakRequired(kategoriDampakSelect.value);
  setNilaiDampakDefaultValue(kategoriDampakSelect.value);

  // Add event listener for change event on kategori dampak
  kategoriDampakSelect.addEventListener('change', function() {
    checkDeskripsiDampakRequired(this.value);
    setNilaiDampakDefaultValue(this.value);
  });

  // Function to check if deskripsi dampak should be required
  function checkDeskripsiDampakRequired(kategoriDampakValue) {
    if (kategoriDampakValue === 'Finansial') {
      deskripsiDampakTextarea.required = true;
      
    } else {
      //deskripsiDampakTextarea.required = false;
      deskripsiDampakTextarea.required = true;
      
    }
  }

  // Function to set default value for nilai dampak based on kategori dampak
  function setNilaiDampakDefaultValue(kategoriDampakValue) {
    if (kategoriDampakValue === 'Non Finansial') {
      nilaiDampakInput.value = 0;
    } else {
      nilaiDampakInput.value = ''; // Clear value if not 'Non Finansial'
    }
  }
});
</script>
<script>
$(document).ready(function() {
  // Mendapatkan nilai tipe risiko dari Blade ke JavaScript
  const riskType = "{{ $identifikasiRisiko->type }}".toLowerCase();;
  //alert(riskType);
  $('#nilai_probabilitas').on('input', function() {
    var nilaiProbabilitas = $(this).val();

    // Kirim permintaan AJAX untuk mendapatkan data skala probabilitas
    $.ajax({
      url: "{{ route('get.skala.probabilitas') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        nilai_probabilitas: nilaiProbabilitas,
        type_risiko: "{{ $identifikasiRisiko->type }}" // Tambahkan data type_risiko jika diperlukan
      },
      success: function(response) {
        // Mengisi nilai skala probabilitas jika respons tidak kosong
        if (response) {
          $('#skala_probabilitas_id').val(response.tingkat + ' - ' + response.skala);
          // Atur nilai skala risiko berdasarkan nilai skala probabilitas yang diperoleh
          calculate2();
        }
      },
      error: function(xhr, status, error) {
        // Tangani kesalahan jika terjadi
        console.error(xhr.responseText);
      }
    });
  });

  $('#btn-show-kriteria-dampak').click(function() {
    // Mendapatkan nilai dari dropdown kategori dampak
    const kategoriDampak = $('#kategori_dampak').val();
    //alert(riskType);
    if (riskType === 'umum') {
      if (kategoriDampak === 'Non Finansial') {
        $('#kriteriaDampakModal').modal('show'); // Menampilkan modal umum non finansial
      } 
      else if (kategoriDampak === 'Finansial') {
        $('#kriteriaDampakFinansialModal').modal('show'); // Menampilkan modal umum finansial
      } 
      else {
        // Jika kategori tidak dipilih, berikan pesan error atau handle lainnya
        alert('Silakan pilih kategori dampak terlebih dahulu.');
      }
    } 
    else {
      $('#kriteriaDampakMedisModal').modal('show'); // Menampilkan modal Medis
    }
  });

  // Menangani event change pada elemen Select2
  $('#kategori_dampak').on('select2:select', function(e) {
      const selectedValue = e.params.data.id;
      const nilaiDampakInput = $('#nilai_dampak');
      const skalaDampakSelect = $('#skala_dampak');
      const typeRisiko = "{{ $identifikasiRisiko->type }}";

      if(typeRisiko.toLowerCase() === "umum" ){
        if(selectedValue === 'Finansial'){
          nilaiDampakInput.val('').prop('readonly', false);
          skalaDampakSelect.prop('disabled', true);
          skalaDampakSelect.val(null).trigger('change');
        }
        else{
          nilaiDampakInput.val(0).prop('readonly', true);
          skalaDampakSelect.prop('disabled', false);
          skalaDampakSelect.trigger('change');
        }
      }
      else{
        if(selectedValue === 'Finansial'){
          nilaiDampakInput.val('').prop('readonly', false);
        }
        else{
          nilaiDampakInput.val(0).prop('readonly', true);
        }
      }

  });

  $('#nilai_dampak').on('change', function() {
    const selectedValue = $('#kategori_dampak').val();
    const typeRisiko = "{{ $identifikasiRisiko->type }}";
    if(typeRisiko.toLowerCase() === "umum" ){
      if(selectedValue === 'Finansial'){
        var nilaiDampak = $(this).val();
        if(nilaiDampak !== ""){
          /*
          console.log("Data yang akan dikirim:");
          console.log({
              _token: "{{ csrf_token() }}",
              nilai_dampak: nilaiDampak,
              type_risiko: "{{ $identifikasiRisiko->type }}",
              jenis_risiko: "{{ $identifikasiRisiko->jenis_risiko_id }}",
              unit_id: "{{ $identifikasiRisiko->unit_id }}",
              periode_id: "{{ $identifikasiRisiko->periode_id }}"
          });
          */

          $.ajax({
            url: "{{ route('get.skala.dampak') }}",
            type: "POST",
            data: {
              _token: "{{ csrf_token() }}",
              nilai_dampak: nilaiDampak,
              type_risiko: "{{ $identifikasiRisiko->type }}",
              jenis_risiko: "{{ $identifikasiRisiko->jenis_risiko_id }}",
              unit_id: "{{ $identifikasiRisiko->unit_id }}",
              periode_id: "{{ $identifikasiRisiko->periode_id }}"
            },
            success: function(response) {
              // Mengisi nilai skala probabilitas jika respons tidak kosong
              if (response) {
                // Tangkap skala_dampak dari response
                if (response && response.skala_dampak !== undefined) {
                    const skalaDampak = response.skala_dampak;
                    
                    // Mengisi nilai skala_dampak ke dalam elemen tertentu
                    $('#skala_dampak').val(skalaDampak).trigger('change');
                    
                    // Menampilkan skala dampak di konsol
                    console.log('Skala dampak:', skalaDampak);
                }
              }
            },
            error: function(xhr, status, error) {
              // Tangani kesalahan jika terjadi
              console.error(xhr.responseText);
            }
          });
        }
      }
    }
  });
});
</script>

<script>
// Ambil data dari PHP dan susun ke dalam array JavaScript
var level_risiko = {!! json_encode($level_risiko) !!};
var nilai_risiko = {!! json_encode($nilai_risiko) !!};

function calculate2() {
  var skala_dampak_value = parseFloat(document.getElementById('skala_dampak').value);
  var skala_probabilitas_value = parseFloat(document.getElementById('skala_probabilitas_id').value);

  if (!isNaN(skala_dampak_value) && !isNaN(skala_probabilitas_value)) {
    var result = level_risiko[skala_dampak_value][skala_probabilitas_value];
    console.log("skala dampak : "+skala_dampak_value);
    console.log("skala probabilitas : "+skala_probabilitas_value);
    var result2 = nilai_risiko[skala_dampak_value][skala_probabilitas_value];
    console.log("nilai_risiko : "+result2);
    document.getElementById('level_risiko').value = result;
    document.getElementById('skala_risiko').value = result2;

    updateBorderColor(result);
  }
}

function updateBorderColor(levelRisiko) {
  var levelRisikoInput = document.getElementById("level_risiko");
  var borderClasses = ['border-low', 'border-low-medium', 'border-medium', 'border-medium-high', 'border-high'];

  // Remove all previous border classes
  borderClasses.forEach(function(cls) {
    levelRisikoInput.classList.remove(cls);
  });

  // Determine new border class
  var borderClass = '';
  if (levelRisiko.toLowerCase() === "high") {
    borderClass = 'border-high';
  } else if (levelRisiko.toLowerCase() === "moderate to high") {
    borderClass = 'border-medium-high';
  } else if (levelRisiko.toLowerCase() === "moderate") {
    borderClass = 'border-medium';
  } else if (levelRisiko.toLowerCase() === "low to moderate") {
    borderClass = 'border-low-medium';
  } else if (levelRisiko.toLowerCase() === "low") {
    borderClass = 'border-low';
  }

  // Add new border class
  if (borderClass) {
    levelRisikoInput.classList.add(borderClass);
  }
}

function initialize2() {
  document.getElementById('skala_dampak').addEventListener('change', calculate2);
  document.getElementById('skala_probabilitas_id').addEventListener('change', calculate2);
}



document.addEventListener('DOMContentLoaded', initialize2);

$(document).ready(function() {
  $('.btn-submit').on('click', function() {

  });
});
</script>

@endsection