@extends('layouts.default')

@section('dashboard')
<main class="main" id="top">
    <div class="container-fluid" data-layout="container-fluid">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-3">
                    <li class="breadcrumb-item"><a href="{{ route('risk-register.index') }}" class="fs-2">Risk Register</a></li>
                    <li class="breadcrumb-item active fs-2" aria-current="page">Edit</li>
                </ol>
            </nav>
            <form class="card" method="POST" action="{{ route('risk-register.store') }}">
                @csrf
                <div class="card-header">
                    <nav class="nav nav-pills nav-fill">
                        <a class="nav-link tab-pills" href="#"><span class="nav-item-circle-parent"><span class="nav-item-circle"></span></span><span class="d-none d-md-block mt-1 fs--1">Target</span></a>
                        <a class="nav-link tab-pills" href="#"><span class="nav-item-circle-parent"><span class="nav-item-circle"></span></span><span class="d-none d-md-block mt-1 fs--1">Penyebab Risiko</span></a>
                        <a class="nav-link tab-pills" href="#"><span class="nav-item-circle-parent"><span class="nav-item-circle"></span></span><span class="d-none d-md-block mt-1 fs--1">Kontrol</span></a>
                    </nav>
                </div>
                <div class="card-body">
                    <div class="tab d-none">
                        <div class="mb-3">
                          <label class="form-label">Target Capaian Kinerja</label>
                          <select class="form-select js-example-basic-multiple" name="target_capaian_kinerja[]" multiple="multiple">
                            <option selected disabled>Target Capaian Kinerja</option>
                            <option value="RCK1">Persentase program studi sarjana, sarjana terapan yang melaksanakan kerja sama
                                dengan mitra (IKU 6)</option>
                            <option value="TCK2">Persentase mata kuliah sarjana dan sarjana terapan yang menggunakan metode
                                pembelajaran pemecahan kasus (case method) atau pembelajaran kelompok berbasis projek (team-based
                                project) sebagai sebagian bobot evaluasi (IKU 7)</option>
                          </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Rencana Kegiatan</label>
                                <select class="form-select js-example-basic-single" name="rencana_kegiatan">
                                    <option selected disabled>Rencana Kegiatan</option>
                                    @foreach($rencanaKegiatan as $id => $title)
                                        <option value="{{ $id }}">{{ $title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori dan Jenis Risiko</label>
                                <select class="form-select js-example-basic-single" name="kategori_jenis_risiko">
                                    <option selected disabled>Kategori dan Jenis Risiko</option>
                                    @foreach($kategoriJenisRisiko as $id => $title)
                                        <option value="{{ $id }}">{{ $title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Periode</label>
                                <select class="form-select js-example-basic-single" name="periode_id">
                                    <option selected disabled>Periode</option>
                                    @foreach($periode as $id => $tahun)
                                        <option value="{{ $id }}">{{ $tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Peristiwa Risiko</label>
                                <select class="form-select js-example-basic-single" name="peristiwa_risiko">
                                    <option selected disabled>Peristiwa Risiko</option>
                                    @foreach($peristiwaRisiko as $id => $title)
                                        <option value="{{ $id }}">{{ $title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label" for="title">Deskripsi Peristiwa Risiko</label>
                            <textarea class="form-control" id="deskripsi_peristiwa_risiko" name="deskripsi_peristiwa_risiko" rows="3" value="{{ old('deskripsi_peristiwa_risiko') }}"></textarea>
                        </div>
                    </div>

                    <div class="tab d-none" id="penyebab-risiko-tab">
                        <div class="row mb-3">
                            <div class="col-auto ms-auto"> <!-- Menggunakan col-auto untuk membuat tombol berada di sebelah kanan -->
                                <button type="button" class="btn btn-primary" id="add-column"><i class="fas fa-solid fa-plus"></i></button>
                            </div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Penyebab Risiko</th>
                                    <th>Key Risk Indicator</th>
                                    <th>Satuan KRI</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="penyebab-risiko-body">
                            </tbody>
                        </table>
                    </div>

                    <div class="tab d-none">
                        <div class="mb-3">
                            <label for="kontrol_eksisting" class="form-label">Kontrol Eksisting</label>
                            <input type="text" class="form-control" name="kontrol_eksisting" id="kontrol_eksisting" placeholder="Kontrol Eksisting">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Penilaian Efektivitas Kontrol</label>
                                <select class="form-select" name="penilaian_efektifitas_kontrol">
                                  <option selected disabled>Penilaian Efektivitas Kontrol</option>
                                  <option value="Penilaian Efektivitas Kontrol 1">Penilaian Efektivitas Kontrol 1</option>
                                  <option value="Penilaian Efektivitas Kontrol 2">Penilaian Efektivitas Kontrol 2</option>
                                  <option value="Penilaian Efektivitas Kontrol 3">Penilaian Efektivitas Kontrol 3</option>
                                  <option value="Penilaian Efektivitas Kontrol 4">Penilaian Efektivitas Kontrol 4</option>
                                  <option value="Penilaian Efektivitas Kontrol 5">Penilaian Efektivitas Kontrol 5</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Area Dampak</label>
                                <select class="form-select" name="area_dampak">
                                    <option selected disabled>Area Dampak</option>
                                    @foreach($areaDampak as $id => $title)
                                        <option value="{{ $id }}">{{ $title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="title">Dampak Inherent</label>
                            <textarea class="form-control" id="dampak_inheren" name="dampak_inheren" rows="3" placeholder="Dampak Inherent" value="{{ old('deskripsi') }}"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="timepicker2">Perkiraan Waktu Terpapar Risiko</label>
                            <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko" id="timepicker2" type="text" placeholder="d/m/y to d/m/y" />
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <div class="d-flex">
                        <button type="button" id="back_button" class="btn btn-link" onclick="back()">Back</button>
                        <button type="submit" id="next_button" class="btn btn-primary ms-auto">Next</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

@endsection

@push('scripts')
<script>
  var today = new Date();
  var endOfYear = new Date(today.getFullYear(), 11, 31); // Mendapatkan tanggal terakhir dalam tahun ini

  flatpickr("#timepicker2", {
    mode: "range",
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    minDate: today,
    //maxDate: endOfYear,
    disableMobile: true
  });

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
    $(document).ready(function() {
        let columnCounter = 0;

        // Ketika tombol "Tambah Kolom Data Baru" diklik
        $("#add-column").click(function() {
            // Tambahkan kolom data baru ke dalam tabel
            $("#penyebab-risiko-body").append(
                '<tr id="column-' + columnCounter + '">' +
                '<td><input type="text" class="form-control" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko"></td>' +
                '<td><input type="text" class="form-control" name="key_risk_indicator[]" placeholder="Masukkan Key Risk Indicator"></td>' +
                '<td><input type="text" class="form-control" name="satuan_kri[]" placeholder="Masukkan Satuan KRI"></td>' +
                '<td><button type="button" class="btn btn-danger" onclick="removeColumn(' + columnCounter + ')"><i class="fas fa-solid fa-trash"></i></button></td>' +
                '</tr>'
            );

            columnCounter++;
        });

        // Fungsi untuk menghapus kolom data
        window.removeColumn = function(index) {
            $("#column-" + index).remove();
        };
    });
    $(document).ready(function() {
    var current = 0;
    var tabs = $(".tab");
    var tabs_pill = $(".tab-pills");

    loadFormData(current);

    function loadFormData(n) {
        $(tabs_pill[n]).addClass("active");
        $(tabs[n]).removeClass("d-none");
        $("#back_button").attr("disabled", n == 0 ? true : false);
        n == tabs.length - 1
            ? $("#next_button").text("Save") // Mengubah teks tombol menjadi "Save"
            : $("#next_button")
                .attr("type", "button") // Mengubah atribut type menjadi "button"
                .text("Next")
                .attr("onclick", "next()");
    }

    function next() {
        if (current < tabs.length - 1) {
            $(tabs[current]).addClass("d-none");
            $(tabs_pill[current]).removeClass("active");

            current++;
            loadFormData(current);
        } else {
            // Jika sudah di tab terakhir, submit formulir
            $("form").submit();
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
    });
</script>
@endpush
