@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Identifikasi Risiko {{ $project->project_name }}</h3>
        </div>
    </div>

    <form class="row g-3" method="POST" action="{{ route('projects.risks.update', ['project' => request()->route('project'), 'risk' => request()->route('risk')]) }}" id="main-form">
        <input type="hidden" name="draft_key" value="{{ request()->draft_key }}">
        @method('PUT')
        @csrf

        <!-- ::DataRisiko Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent">
                            <span class="nav-item-circle">1</span>
                        </span>
                        <span class="h3 mb-0">Data Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 gx-md-5">
                        <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Sasaran Risiko</label>
                                <textarea class="form-control" id="target_capaian_kinerja" name="target_capaian_kinerja" rows="3"
                                    value="{{ old('target_capaian_kinerja', $projectRisk->target_capaian_kinerja) }}" placeholder="Sasaran Risiko" disabled></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Peristiwa Risiko</label>
                                <input type="hidden" name="peristiwa_risiko_id" id="peristiwa_risiko" value="{{ old('peristiwa_risiko_id', $projectRisk->peristiwa_risiko_id) }}">
                                <input type="text" class="form-control" 
                                    value="{{ $projectRisk->peristiwaRisiko?->title }}" 
                                    readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Jenis Risiko T2 & T3 KBUMN</label>
                                <input type="hidden" name="kategori_risiko_id" value="{{ old('kategori_risiko_id', $projectRisk->kategori_risiko_id) }}" id="kategori_risiko_id">
                                <select class="form-select select2 @error('jenis_risiko_id') is-invalid @enderror" name="jenis_risiko_id" id="jenis_risiko_id" required>
                                    <option value="">Pilih Jenis Risiko</option>
                                    @foreach($jenisRisikos as $jenis)
                                        <option value="{{ $jenis->id }}" 
                                            data-kategori="{{ $jenis->kategori_risiko_id }}"
                                            {{ old('jenis_risiko_id', $projectRisk->jenis_risiko_id) == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->kategoriRisiko->title ?? '' }} – {{ $jenis->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="deskripsi_peristiwa_risiko" name="deskripsi_peristiwa_risiko" rows="3"
                                    value="{{ old('deskripsi_peristiwa_risiko') }}" placeholder="Deskripsi Peristiwa Risiko" required></textarea>
                                <label for="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="wbs" name="wbs" rows="3"
                                    value="{{ old('wbs') }}" placeholder="WBS" required></textarea>
                                <label for="wbs">WBS</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ::DataRisiko End -->

        <!-- ::PenyebabRisiko Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent">
                            <span class="nav-item-circle">2</span>
                        </span>
                        <span class="h3 mb-0">Penyebab Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="penyebab-risiko-body">
                        <div class="row g-2">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="penyebab_risiko[]"
                                        placeholder="Masukkan Penyebab Risiko">
                                    <label>Penyebab Risiko</label>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                            <div class="col-12 mt-0">
                                <hr>
                            </div>
                        </div>
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
            </div>
        </div>
        <!-- ::PenyebabRisiko End -->

        <!-- ::KeyRiskIndicator Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent">
                            <span class="nav-item-circle">3</span>
                        </span>
                        <span class="h3 mb-0">Key Risk Indicator</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="kri-body">
                        <div class="row g-2">
                            <div class="col-12 col-lg-11">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="form-group form-floating">
                                            <input type="text" class="form-control" name="key_risk_indicator[]">
                                            <label for="key_risk_indicator_1">Key Risk Indicator</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                        <div class="form-group form-floating">
                                            <input type="text" class="form-control" name="satuan_kri[]">
                                            <label for="satuan_kri_1">Satuan KRI</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                        <div class="form-group form-floating text-center">
                                            <input type="text" class="form-control border-success" name="batas_aman[]">
                                            <label for="batas_aman_1">Batas Aman</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                        <div class="form-group form-floating text-center">
                                            <input type="text" class="form-control border-warning" name="batas_waspada[]">
                                            <label for="batas_waspada_1">Batas Waspada</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                        <div class="form-group form-floating text-center">
                                            <input type="text" class="form-control border-danger" name="batas_bahaya[]">
                                            <label for="batas_bahaya_1">Batas Bahaya</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center ms-auto">
                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                            <div class="col-12 mt-0">
                                <hr>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto ms-auto d-flex">
                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-column-kri"
                                data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah KRI">
                                <i class='bx bx-plus fs-5'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ::KeyRiskIndicator End -->

        <!-- ::Kontrol Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent">
                            <span class="nav-item-circle">4</span>
                        </span>
                        <span class="h3 mb-0">Kontrol</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-3 gx-xxl-6 mb-3">
                        <div class="col-md-6 col-lg-5 col-xxl-6">
                            <select name="jenis_kontrol_eksisting_id" class="form-select">
                                <option value="">Jenis Kontrol Eksisting</option>
                                @foreach ($jenisKontrolEksistings as $jenisKontrolEksisting)
                                    <option value="{{ $jenisKontrolEksisting->id }}">
                                        {{ $jenisKontrolEksisting->jenis_kontrol }}</option>
                                @endforeach
                            </select>

                            <div id="kontrol-eksisting-container">
                                @foreach ($projectRisk->projectKontrolEksistings as $kontrolEksisting)
                                <div class="input-group mt-3">
                                    <input type="text" class="form-control" name="kontrol_eksisting[]" value="{{ $kontrolEksisting->kontrol_eksisting_desc }}" placeholder="Masukkan Kontrol Eksisting">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('.input-group').remove();">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-auto d-flex ms-auto mt-3">
                                    <button type="button" class="btn btn-outline-secondary p-2" id="add-kontrol-eksisting"
                                        data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah Kontrol Eksisting">
                                        <i class='bx bx-plus fs-5'></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-7 col-xxl-6">
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Penilaian Efektivitas
                                    Kontrol</label>
                                <select class="form-select select2" name="penilaian_efektifitas_kontrol">
                                    <option selected disabled>Penilaian Efektivitas Kontrol</option>
                                    @foreach ($penilaianEfektifitasKontrols as $efektivitasKontrol)
                                        <option value="{{ $efektivitasKontrol->id }}"
                                            {{ old('penilaian_efektifitas_kontrol') == $efektivitasKontrol->efektivitas_kontrol ? 'selected' : '' }}>
                                            {{ $efektivitasKontrol->efektivitas_kontrol }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker2">Perkiraan
                                    Waktu
                                    Terpapar Risiko</label>
                                <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko"
                                    id="timepicker2" type="text" placeholder="d/m/y to d/m/y"
                                    value="{{ old('perkiraan_waktu_terpapar_risiko') }}" />
                            </div> --}}

                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker2">Perkiraan Waktu Mulai Terpapar Risiko</label>
                                <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko_mulai"
                                    id="timepicker2" type="text" placeholder="d/m/y"
                                    value="{{ old('perkiraan_waktu_terpapar_risiko_mulai') }}" />
                            </div>

                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker3">Perkiraan Waktu Selesai Terpapar Risiko</label>
                                <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko_akhir"
                                    id="timepicker3" type="text" placeholder="d/m/y"
                                    value="{{ old('perkiraan_waktu_terpapar_risiko_akhir') }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ::Kontrol End -->

        <div class="col-12 mt-5">
            <div class="row g-2">
                <div class="col-auto order-1">
                    <a href="{{ route('projects.risks.index', ['project' => $projectPeriodeList->id]) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                @if(!$projectRisk->id)
                <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="draft" class="btn btn-warning bg-warning ms-auto btn-action">Save as Draft</button>
                </div>
                @endif
                <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="save" class="btn btn-primary ms-auto btn-action">Save</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function removeRow(event) {
        let row = $(event.target).closest('.row');
        row.remove();
    }

    $(document).ready(function() {
        const masterKris = @json($masterKris->keyBy('id'));
        const kontrolExistings = @json($kontrolEksistings->keyBy('id'));
        
        $('#jenis_risiko_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var kategoriId = selectedOption.data('kategori');
            $('#kategori_risiko_id').val(kategoriId);
        });

        $('#jenis_risiko_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var kategoriId = selectedOption.data('kategori');
            $('#kategori_risiko_id').val(kategoriId);
        });

        // Add Column Penyebab Risiko
        let row = 0;
        $('#add-column').click(function() {
            row++;
            let html = `
            <div class="row g-2">
                <div class="col">
                <div class="form-floating">
                    <input type="text" class="form-control" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko">
                    <label>Penyebab Risiko</label>
                </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                    <i class="bx bx-trash"></i>
                </button>
                </div>
                <div class="col-12 mt-0">
                <hr>
                </div>
            </div>
            `;
            $('#penyebab-risiko-body').append(html);
        });

        // Add Column Key Risk Indicator
        $('#add-column-kri').click(function() {
            row++;
            let html = `
                <div class="row g-2">
                    <div class="col-12 col-lg-11">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="form-group form-floating">
                                    <input type="text" class="form-control" name="key_risk_indicator[]">
                                    <label for="key_risk_indicator_1">Key Risk Indicator</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating">
                                    <input type="text" class="form-control" name="satuan_kri[]">
                                    <label for="satuan_kri_1">Satuan KRI</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating text-center">
                                    <input type="text" class="form-control border-success" name="batas_aman[]">
                                    <label for="batas_aman_1">Batas Aman</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating text-center">
                                    <input type="text" class="form-control border-warning" name="batas_waspada[]">
                                    <label for="batas_waspada_1">Batas Waspada</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating text-center">
                                    <input type="text" class="form-control border-danger" name="batas_bahaya[]">
                                    <label for="batas_bahaya_1">Batas Bahaya</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center ms-auto">
                        <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                    <div class="col-12 mt-0">
                        <hr>
                    </div>
                </div>`;
            $('#kri-body').append(html);
        });

        $('#kri-body').on('change', '[name="master_kri_id[]"]', function() {
            const row = $(this).closest('.row');
            const kri = masterKris[$(this).val()];
            if (kri) {
                row.find('[name="key_risk_indicator[]"]').val(kri.kri);
                row.find('[name="satuan_kri[]"]').val(kri.satuan_kri);
                row.find('[name="batas_aman[]"]').val(kri.batas_aman);
                row.find('[name="batas_waspada[]"]').val(kri.batas_waspada);
                row.find('[name="batas_bahaya[]"]').val(kri.batas_bahaya);
            } else {
                row.find('[name="key_risk_indicator[]"]').val('');
                row.find('[name="satuan_kri[]"]').val('');
                row.find('[name="batas_aman[]"]').val('');
                row.find('[name="batas_waspada[]"]').val('');
                row.find('[name="batas_bahaya[]"]').val('');
            }
        });

        /*$('#refresh-kontrol-eksisting-btn').on('click', function() {
            $('#table-kontrol tbody').empty();

            let count = 0;
            $.each(kontrolExistings, function(id, kontrol) {
                $('#table-kontrol tbody').append(`
                    <tr>
                        <td>
                            ${count+1}
                            <input type="text" name="kontrol_eksisting_id[]" value="${kontrol.id}" hidden>
                        </td>
                        <td>${kontrol.kontrol_eksisting}</td>
                        <td>
                            <button type="button" class="btn btn-icon-danger h-100" onclick="$(this).closest('tr').remove(); if ($('#table-kontrol tbody tr').length == 0) $('.table-empty').show();">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                count++;
            });

            if ($('#table-kontrol tbody tr').length > 0) {
                $('.table-empty').hide();
            }
        });*/

        function fetchKontrolEksisting() {
            let peristiwaRisikoId = $('#peristiwa_risiko').val();
            
            if (!peristiwaRisikoId) {
                $('#table-kontrol tbody').empty();
                $('.table-empty').show();
                return;
            }

            $.ajax({
                url: "/get-kontrol-eksisting",
                type: "GET",
                data: { peristiwa_risiko_id: peristiwaRisikoId },
                success: function (response) {
                    let tableBody = $('#table-kontrol tbody');
                    tableBody.empty();

                    if (response.length > 0) {
                        $('.table-empty').hide();
                        $.each(response, function (index, item) {
                            tableBody.append(`
                                <tr>
                                    <td>${index + 1}
                                        <input type="hidden" name="kontrol_eksisting_id[]" value="${item.id}">
                                    </td>
                                    <td>${item.kontrol_eksisting}</td>
                                    <td>
                                        <button type="button" class="btn btn-icon-danger h-100"
                                            onclick="$(this).closest('tr').remove(); if ($('#table-kontrol tbody tr').length == 0) $('.table-empty').show();">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        $('.table-empty').show();
                    }
                },
                error: function () {
                    alert("Gagal mengambil data kontrol eksisting.");
                }
            });
        }

        $('#refresh-kontrol-eksisting-btn').on('click', function() {
            fetchKontrolEksisting();
        });

        var today = new Date();
        var endOfYear = new Date(today.getFullYear(), 11, 31);

        var flatpickrIns1 = flatpickr("#timepicker2", {
            //mode: "range",
            altInput: false,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            //maxDate: endOfYear,
            disableMobile: true
        });

        var flatpickrIns2 = flatpickr("#timepicker3", {
            //mode: "range",
            altInput: false,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            //maxDate: endOfYear,
            disableMobile: true
        });

        $('.btn-action').on('click', function() {
            const action = $(this).data('action');
            const form = $('#main-form');
            const url = form.attr('action');
            const data = new FormData(form[0]);

            data.append('action', action);

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.redirect) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Data berhasil disimpan',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message || 'Data berhasil disimpan',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON.errors;
                    if (errors) {
                        let message = '<ul>';
                        for (const key in errors) {
                            message += `<li>${errors[key]}</li>`;
                        }
                        message += '</ul>';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: message
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan data'
                        });
                    }
                }
            });
        });

        const preloadedData = @json($projectRisk);

        for (const key in preloadedData) {
            if (preloadedData.hasOwnProperty(key)) {
                const value = preloadedData[key];
                if (key === 'perkiraan_waktu_terpapar_risiko') {
                    //flatpickrIns.setDate(value.split(' to '));
                }
                else if (key === 'perkiraan_waktu_terpapar_risiko_mulai') {
                    flatpickrIns1.setDate(value);     
                }
                else if (key === 'perkiraan_waktu_terpapar_risiko_akhir') {
                    flatpickrIns2.setDate(value);
                }
                else if (Array.isArray(value)) {
                    if (key === 'penyebab_risiko_projects') {
                        value.forEach((penyebab, index) => {
                            if (index === 0) {
                                $(`[name="penyebab_risiko[]"]`).val(penyebab.penyebab_risiko);
                            } else {
                                $('#add-column').click();
                                $(`[name="penyebab_risiko[]"]`).last().val(penyebab.penyebab_risiko);
                            }
                        });
                    } else if (key === 'kri_projects') {
                        value.forEach((kri, index) => {
                            if (index === 0) {
                                $(`[name="key_risk_indicator[]"]`).val(kri.kri);
                                $(`[name="satuan_kri[]"]`).val(kri.satuan_kri);
                                $(`[name="batas_aman[]"]`).val(kri.batas_aman);
                                $(`[name="batas_waspada[]"]`).val(kri.batas_waspada);
                                $(`[name="batas_bahaya[]"]`).val(kri.batas_bahaya);
                            } else {
                                $('#add-column-kri').click();
                                $(`[name="key_risk_indicator[]"]`).last().val(kri.kri);
                                $(`[name="satuan_kri[]"]`).last().val(kri.satuan_kri);
                                $(`[name="batas_aman[]"]`).last().val(kri.batas_aman);
                                $(`[name="batas_waspada[]"]`).last().val(kri.batas_waspada);
                                $(`[name="batas_bahaya[]"]`).last().val(kri.batas_bahaya);
                            }
                        });
                    } else if (key === 'kontrol_eksisting_projects') {
                        value.forEach((kontrol, index) => {
                            $('#table-kontrol tbody').append(`
                                <tr>
                                    <td>
                                        ${index+1}
                                        <input type="text" name="kontrol_eksisting_id[]" value="${kontrol.kontrol_eksisting_id}" hidden>
                                    </td>
                                    <td>${kontrol.kontrol_eksisting}</td>
                                    <td>
                                        <button type="button" class="btn btn-icon-danger h-100" onclick="$(this).closest('tr').remove(); if ($('#table-kontrol tbody tr').length == 0) $('.table-empty').show();">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });

                        if ($('#table-kontrol tbody tr').length > 0) {
                            $('.table-empty').hide();
                        }
                    }
                } else {
                    $(`[name="${key}"]`).val(value).change();
                }
            }
        }

        $('#add-kontrol-eksisting').click(function() {
            const html = `
                <div class="input-group mt-3">
                    <input type="text" class="form-control" name="kontrol_eksisting[]" placeholder="Masukkan Kontrol Eksisting">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('.input-group').remove();">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            `;

            $('#kontrol-eksisting-container').append(html);
        });
    });
</script>
@endpush
