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

    <form class="row g-3" method="POST" action="{{ route('projects.risks.store', request()->route('project')) }}" id="main-form">
        <input type="hidden" name="draft_key" value="{{ request()->draft_key }}">
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
                                <div class="w-100">
                                    <select class="form-select select2" id="sasaran_proyek_id" name="sasaran_proyek_id">
                                        <option value="">Pilih Sasaran Risiko</option>
                                        @foreach($sasaranProyeks as $sasaranProyek)
                                            <option value="{{ $sasaranProyek->id }}" data-kpi="{{ $sasaranProyek->kpi_desc }}">{{ $sasaranProyek->kpi_desc }}</option>
                                        @endforeach
                                        <option value="other">Ajukan Sasaran Lainnya</option>
                                    </select>
                                    <div class="alert alert-info mt-2 d-none mb-2" id="sasaran-other-guide">
                                        Sasaran ini perlu persetujuan Divisi Manajemen Risiko sebelum bisa digunakan.
                                    </div>
                                    <div class="d-none" id="sasaran-other-box">
                                        <textarea class="form-control" id="target_capaian_kinerja" name="target_capaian_kinerja" rows="3"
                                            placeholder="Masukkan usulan sasaran risiko lainnya"></textarea>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-submit-sasaran-lainnya">
                                                <i class="bx bx-send me-1"></i>Ajukan Persetujuan
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="btn-refresh-sasaran">
                                                <i class="bx bx-refresh me-1"></i>Muat Ulang Pilihan Sasaran
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Setelah disetujui Divisi Manajemen Risiko, pilih kembali sasaran dari dropdown di atas.</small>
                                    </div>
                                    <input type="hidden" id="kpi_desc_selected" name="kpi_desc_selected">
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Taksonomi Danantara</label>
                                <div class="w-100">
                                    <select class="form-select select2" name="taksonomi_risiko_id" required>
                                        <option value="">Pilih Taksonomi</option>
                                        @foreach($taksonomiRisikos as $tax)
                                            <option value="{{ $tax->id }}" {{ old('taksonomi_risiko_id', $projectRisk->taksonomi_risiko_id ?? '') == $tax->id ? 'selected' : '' }}>
                                                {{ $tax->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div> --}}
                        <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Peristiwa Risiko</label>
                                <div class="w-100">
                                    <select class="form-select select2 js-select-hide-search" name="peristiwa_risiko_id"
                                        id="peristiwa_risiko" required>
                                        <option selected>Pilih Peristiwa Risiko</option>
                                        @foreach ($peristiwaRisikos as $peristiwaRisiko)
                                            <option value="{{ $peristiwaRisiko->id }}">{{ $peristiwaRisiko->title }}</option>
                                        @endforeach
                                        <option value="other" {{ old('peristiwa_risiko_id') == 'other' ? 'selected' : '' }}>Ajukan Peristiwa Lainnya</option>
                                    </select>
                                    <div class="alert alert-info mt-2 d-none mb-2" id="peristiwa-other-guide">
                                        Peristiwa ini perlu persetujuan Divisi Manajemen Risiko sebelum bisa digunakan.
                                    </div>
                                    <div class="d-none" id="peristiwa-other-box">
                                        <textarea
                                          class="form-control"
                                          id="peristiwa_risiko_lainnya"
                                          name="rencana_kegiatan"
                                          rows="3"
                                          placeholder="Masukkan usulan peristiwa risiko lainnya"
                                        >{{ old('rencana_kegiatan', $projectRisk->rencana_kegiatan ?? '') }}</textarea>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-submit-peristiwa-lainnya">
                                                <i class="bx bx-send me-1"></i>Ajukan Persetujuan
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="btn-refresh-peristiwa">
                                                <i class="bx bx-refresh me-1"></i>Muat Ulang Pilihan Peristiwa
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Setelah disetujui Divisi Manajemen Risiko, pilih kembali peristiwa dari dropdown di atas.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Jenis Risiko T2 & T3 KBUMN</label>
                                <input type="hidden" name="kategori_risiko_id" value="{{ old('kategori_risiko_id') }}" id="kategori_risiko_id">
                                <div class="w-100">
                                    <select class="form-select select2 @error('jenis_risiko_id') is-invalid @enderror" name="jenis_risiko_id" id="jenis_risiko_id" required>
                                        <option value="">Pilih Jenis Risiko</option>
                                        @foreach($jenisRisikos as $jenis)
                                            <option value="{{ $jenis->id }}"
                                                data-kategori="{{ $jenis->kategori_risiko_id }}"
                                                {{ old('jenis_risiko_id') == $jenis->id ? 'selected' : '' }}>
                                                {{ $jenis->kategoriRisiko->title ?? '' }} – {{ $jenis->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="wbs" name="wbs" rows="3"
                                    value="{{ old('wbs') }}" placeholder="Deskripsi Peristiwa Risiko" required></textarea>
                                <label for="wbs">WBS</label>
                            </div>
                        </div> --}}
                        <div class="col-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">WBS</label>
                                <div class="w-100">
                                    <select class="form-select select2" name="wbs_id" id="wbs_id" required>
                                        <option value="" selected disabled>Pilih WBS</option>
                                        @foreach ($wbs_data as $w)
                                            <option value="{{ $w->id }}" {{ old('wbs_id') == $w->id ? 'selected' : '' }}>
                                                {{ $w->code }} - {{ $w->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="deskripsi_dampak" name="deskripsi_dampak" rows="3"
                                    value="{{ old('deskripsi_dampak') }}" placeholder="Deskripsi Dampak" required></textarea>
                                <label for="deskripsi_dampak">Deskripsi Dampak Risiko</label>
                            </div>
                        </div> --}}
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="deskripsi_peristiwa_risiko" name="deskripsi_peristiwa_risiko" rows="3"
                                    value="{{ old('deskripsi_peristiwa_risiko') }}" placeholder="Deskripsi Peristiwa Risiko" required></textarea>
                                <label for="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ::DataRisiko End -->

        <!-- ::DampakRisiko Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent">
                            <span class="nav-item-circle">2</span>
                        </span>
                        <span class="h3 mb-0">Dampak Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="dampak-risiko-body">
                        <div class="row g-2 mb-3 dampak-row-item">
                            <div class="col">
                                <div class="form-floating">
                                    <textarea class="form-control" name="dampak_risiko[]" placeholder="Masukkan Dampak Risiko" required></textarea>
                                    <label>Dampak Risiko</label>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-auto ms-auto">
                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-dampak"
                                data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah Dampak Risiko">
                                <i class='bx bx-plus fs-5'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ::DampakRisiko End -->

        <!-- ::ParameterRisiko Start -->
        {{-- <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent">
                            <span class="nav-item-circle">2</span>
                        </span>
                        <span class="h3 mb-0">Parameter Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="parameter-risiko-body">
                        <div class="row g-2 mb-3 parameter-row-item">
                            <div class="col-md-1 text-center d-flex justify-content-center align-items-center">
                              <span class="number-pill-info">1</span>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="hidden" name="parameter_risiko_id[]">
                                    <input type="text" class="form-control" name="param_nama[]" placeholder="Nama">
                                    <label>Nama Parameter</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="param_formula[]" placeholder="Formula">
                                    <label>Formula</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="param_satuan[]" placeholder="Satuan">
                                    <label>Satuan</label>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)"><i class="bx bx-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary rounded-pill p-2 float-end" id="add-parameter"><i class='bx bx-plus fs-5'></i></button>
                </div>
            </div>
        </div> --}}
        <!-- ::ParameterRisiko End -->

        <!-- ::Threshold Start -->
        {{-- <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">3</span></span>
                        <span class="h3 mb-0">Threshold</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Risk Limit</label>
                            <input type="text" class="form-control rupiah-input" name="threshold_risk_limit" value="{{ old('threshold_risk_limit', $projectRisk->threshold_risk_limit ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Risk Appetite</label>
                            <input type="text" class="form-control rupiah-input" name="threshold_risk_appetite" value="{{ old('threshold_risk_appetite', $projectRisk->threshold_risk_appetite ?? 0) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Risk Tolerance</label>
                            <input type="text" class="form-control rupiah-input" name="threshold_risk_tolerance" value="{{ old('threshold_risk_tolerance', $projectRisk->threshold_risk_tolerance ?? 0) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
        <!-- ::Threshold End -->

        <!-- ::PenyebabRisiko Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent">
                            <span class="nav-item-circle">3</span>
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
                            <span class="nav-item-circle">4</span>
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
                                            <input type="text" class="form-control" name="key_risk_indicator[]" id="key_risk_indicator">
                                            <label for="key_risk_indicator_1">Key Risk Indicator</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                        <div class="form-group form-floating">
                                            <input type="text" class="form-control" name="satuan_kri[]" id="satuan_kri">
                                            <label for="satuan_kri_1">Satuan KRI</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                        <div class="form-group form-floating text-center">
                                            <input type="text" class="form-control border-success" name="batas_aman[]" id="batas_aman">
                                            <label for="batas_aman_1">Batas Aman</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                        <div class="form-group form-floating text-center">
                                            <input type="text" class="form-control border-warning" name="batas_waspada[]" id="batas_waspada">
                                            <label for="batas_waspada_1">Batas Siaga</label>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                        <div class="form-group form-floating text-center">
                                            <input type="text" class="form-control border-danger" name="batas_bahaya[]" id="batas_bahaya">
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
                            <span class="nav-item-circle">5</span>
                        </span>
                        <span class="h3 mb-0">Kontrol</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-3 gx-xxl-6 mb-3">
                        <div class="col-md-6 col-lg-5 col-xxl-6">
                            <select name="jenis_kontrol_eksisting_id" class="form-select select2">
                                <option value="">Jenis Kontrol Eksisting</option>
                                @foreach ($jenisKontrolEksistings as $jenisKontrolEksisting)
                                    <option value="{{ $jenisKontrolEksisting->id }}">
                                        {{ $jenisKontrolEksisting->jenis_kontrol }}</option>
                                @endforeach
                            </select>

                            <div id="kontrol-eksisting-container">
                                <div class="input-group mt-3">
                                    <input type="text" class="form-control" name="kontrol_eksisting[]" placeholder="Masukkan Kontrol Eksisting">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('.input-group').remove();">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
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
                            {{-- <div class="form-group d-lg-flex mb-4">
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
                            </div> --}}
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker2">Perkiraan Waktu Mulai Terpapar Risiko</label>
                                <div class="w-100">
                                    <input class="form-control datetimepicker" name="perkiraan_waktu_mulai_terpapar_risiko"
                                        id="timepicker2" type="text" placeholder="d/m/y"
                                        value="{{ old('perkiraan_waktu_mulai_terpapar_risiko') }}" />
                                </div>
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker3">Perkiraan Waktu Selesai Terpapar Risiko</label>
                                <div class="w-100">
                                    <input class="form-control datetimepicker" name="perkiraan_waktu_selesai_terpapar_risiko"
                                        id="timepicker3" type="text" placeholder="d/m/y"
                                        value="{{ old('perkiraan_waktu_selesai_terpapar_risiko') }}" />
                                </div>
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
                <!-- <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="draft" class="btn btn-warning bg-warning ms-auto btn-action">Save as Draft</button>
                </div> -->
                <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="save" class="btn btn-warning bg-warning ms-auto btn-action">Simpan dan Keluar</button>
                </div>
                <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="savenext" class="btn btn-primary ms-auto btn-action">Simpan dan Lanjutkan Analisa</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
    function removeRow(event) {
        let row = $(event.target).closest('.row');
        row.remove();
    }

    function loadKriOptions(dropdown, peristiwaRisikoId) {
        dropdown.innerHTML = '<option value="">Pilih</option>';
        if (peristiwaRisikoId) {
            fetch(`/master-kri/${peristiwaRisikoId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(kri => {
                        const option = document.createElement('option');
                        option.value = kri.id;
                        option.textContent = kri.kri;
                        dropdown.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }
    }

    $('#peristiwa_risiko').on('change', function() {
        const selectedValue = $(this).val();
        const otherTextarea = $('#peristiwa_risiko_lainnya');
        const otherGuide = $('#peristiwa-other-guide');
        const otherBox = $('#peristiwa-other-box');
        const refreshBtn = $('#btn-refresh-peristiwa');

        if (selectedValue === 'other') {
            otherGuide.removeClass('d-none');
            otherBox.removeClass('d-none');
            otherTextarea.removeClass('d-none').attr('required', false);
        } else {
            otherGuide.addClass('d-none');
            otherBox.addClass('d-none');
            otherTextarea.addClass('d-none').attr('required', false).val('');
            refreshBtn.addClass('d-none');
        }
    });

    $(document).ready(function() {
        const submitSasaranUrl = @json(route('projects.sasaran-lainnya.submit', ['project' => $projectPeriodeList->id]));
        const submitPeristiwaUrl = @json(route('projects.peristiwa-lainnya.submit', ['project' => $projectPeriodeList->id]));
        $('.select2').select2({
            width: '100%',
        });

        if ($('#peristiwa_risiko').val() === 'other') {
            $('#peristiwa_risiko').trigger('change');
        }

        $('.rupiah-input').inputmask({
            alias: 'numeric',
            groupSeparator: '.',
            autoGroup: true,
            digits: 0,
            digitsOptional: false,
            prefix: 'Rp ',
            placeholder: '0',
            rightAlign: false,
            autoUnmask: true,
            removeMaskOnSubmit: true,
            min: 0,
            allowMinus: false,
            onKeyDown: function(e) {
            if (e.key === 'Backspace' || e.keyCode === 8) {
                // tunda eksekusi sampai mask selesai di-apply
                setTimeout(() => {
                    const unmasked = this.inputmask.unmaskedvalue();
                    // kalau masih ada angka tersisa
                    if (unmasked.length > 0) {
                    // cek posisi cursor
                    const pos = this.selectionStart;
                    if (pos === 0) {
                        // pindahkan ke paling kanan
                        const end = this.value.length;
                        this.setSelectionRange(end, end);
                    }
                    }
                }, 0);
                }
            }
        });

        // Handle perubahan pada dropdown sasaran_proyek_id
        $('#sasaran_proyek_id').on('change', function() {
            const selectedValue = $(this).val();
            const targetTextarea = $('#target_capaian_kinerja');
            const kpiDescSelected = $('#kpi_desc_selected');
            const otherGuide = $('#sasaran-other-guide');
            const otherBox = $('#sasaran-other-box');
            const refreshBtn = $('#btn-refresh-sasaran');

            if (selectedValue === 'other') {
                otherGuide.removeClass('d-none');
                otherBox.removeClass('d-none');
                targetTextarea.removeClass('d-none').attr('required', false);
                kpiDescSelected.val('');
            } else if (selectedValue) {
                const kpiDesc = $(this).find('option:selected').data('kpi');
                otherGuide.addClass('d-none');
                otherBox.addClass('d-none');
                targetTextarea.addClass('d-none').attr('required', false).val('');
                refreshBtn.addClass('d-none');
                kpiDescSelected.val(kpiDesc);
            } else {
                otherGuide.addClass('d-none');
                otherBox.addClass('d-none');
                targetTextarea.addClass('d-none').attr('required', false).val('');
                refreshBtn.addClass('d-none');
                kpiDescSelected.val('');
            }
        });

        $('#btn-submit-sasaran-lainnya').on('click', function() {
            const value = $('#target_capaian_kinerja').val().trim();
            const btn = $(this);

            if (!value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Mohon isi deskripsi sasaran lainnya terlebih dahulu.',
                });
                return;
            }

            Swal.fire({
                title: 'Ajukan sasaran ini?',
                html: `Sasaran <strong>${$('<div>').text(value).html()}</strong> akan dikirim ke Divisi Manajemen Risiko untuk diverifikasi.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Ajukan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                btn.prop('disabled', true);

                $.ajax({
                    url: submitSasaranUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        kpi_desc: value,
                    },
                    success: function(response) {
                        $('#btn-refresh-sasaran').removeClass('d-none');
                        Swal.fire({
                            icon: 'success',
                            title: 'Pengajuan Berhasil',
                            text: response.message || 'Pengajuan telah dikirim ke MR.',
                        });
                    },
                    error: function(xhr) {
                        const msg = xhr?.responseJSON?.message || 'Gagal mengirim pengajuan sasaran. Silakan coba lagi.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Pengajuan Gagal',
                            text: msg,
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });
        });

        $('#btn-refresh-sasaran').on('click', function() {
            window.location.reload();
        });

        $('#btn-submit-peristiwa-lainnya').on('click', function() {
            const value = $('#peristiwa_risiko_lainnya').val().trim();
            const btn = $(this);

            if (!value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Mohon isi deskripsi peristiwa risiko lainnya terlebih dahulu.',
                });
                return;
            }

            Swal.fire({
                title: 'Ajukan peristiwa ini?',
                html: `Peristiwa <strong>${$('<div>').text(value).html()}</strong> akan dikirim ke Divisi Manajemen Risiko untuk diverifikasi.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Ajukan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                btn.prop('disabled', true);

                $.ajax({
                    url: submitPeristiwaUrl,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        title: value,
                    },
                    success: function(response) {
                        $('#btn-refresh-peristiwa').removeClass('d-none');
                        Swal.fire({
                            icon: 'success',
                            title: 'Pengajuan Berhasil',
                            text: response.message || 'Pengajuan telah dikirim ke MR.',
                        });
                    },
                    error: function(xhr) {
                        const msg = xhr?.responseJSON?.message || 'Gagal mengirim pengajuan peristiwa. Silakan coba lagi.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Pengajuan Gagal',
                            text: msg,
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });
        });

        $('#btn-refresh-peristiwa').on('click', function() {
            window.location.reload();
        });

        try {
            const urlParams = new URLSearchParams(window.location.search);
            const penyebabRisikoFromUrl = urlParams.get('penyebab_risiko');

            if (penyebabRisikoFromUrl) {
                const firstPenyebabInput = $('input[name="penyebab_risiko[]"]').first();

                if (firstPenyebabInput.length) {
                    firstPenyebabInput.val(penyebabRisikoFromUrl);
                }
            }
        } catch (e) {
            console.error("Gagal membaca parameter URL:", e);
        }

        const masterKris = @json($masterKris->keyBy('id'));
        const kontrolExistings = @json($kontrolEksistings->keyBy('id'));

        $('#jenis_risiko_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var kategoriId = selectedOption.data('kategori');
            $('#kategori_risiko_id').val(kategoriId);
        });

        // Add Column Penyebab Risiko
        let row = 0;

        console.log('Document Ready');

        $('#add-dampak').click(function() {
            let html = `
            <div class="row g-2 mb-3 dampak-row-item">
                <div class="col">
                    <div class="form-floating">
                        <textarea class="form-control" name="dampak_risiko[]" placeholder="Masukkan Dampak Risiko" required></textarea>
                        <label>Dampak Risiko</label>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </div>`;
            $('#dampak-risiko-body').append(html);
        });

        $('#add-parameter').click(function() {
            let rowIdx = $('.parameter-row-item').length + 1;
            let html = `
            <div class="row g-2 mb-3 parameter-row-item">
                <div class="col-md-1 text-center d-flex justify-content-center align-items-center">
                  <span class="number-pill-info">${rowIdx}</span>
                </div>
                <div class="col-md-4">
                    <div class="form-floating">
                        <input type="hidden" name="parameter_risiko_id[]" value="">
                        <input type="text" class="form-control" name="param_nama[]" placeholder="Nama">
                        <label>Nama Parameter</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating"><input type="text" class="form-control" name="param_formula[]" placeholder="Formula"><label>Formula</label></div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating"><input type="text" class="form-control" name="param_satuan[]" placeholder="Satuan"><label>Satuan</label></div>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)"><i class="bx bx-trash"></i></button>
                </div>
            </div>`;
            $('#parameter-risiko-body').append(html);
        });

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
            const peristiwaRisikoId = $('#peristiwa_risiko').val();

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
                                    <label for="batas_waspada_1">Batas Siaga</label>
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

            // Muat data KRI pada dropdown yang baru ditambahkan
            const newDropdown = $('#kri-body').find('select[name="master_kri_id[]"]').last()[0];
            loadKriOptions(newDropdown, peristiwaRisikoId);
        });

        $('#kri-body').on('change', '[name="master_kri_id[]"]', function() {
            const row = $(this).closest('.row');
            const kri = masterKris[$(this).val()];
            row.find('[name="key_risk_indicator[]"]').val(kri.kri);
            row.find('[name="satuan_kri[]"]').val(kri.satuan_kri);
            row.find('[name="batas_aman[]"]').val(kri.batas_aman);
            row.find('[name="batas_waspada[]"]').val(kri.batas_waspada);
            row.find('[name="batas_bahaya[]"]').val(kri.batas_bahaya);
        });
        /*
        $('#refresh-kontrol-eksisting-btn').on('click', function() {
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
        });
        */

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

        var flatpickrIns = flatpickr("#timepicker2", {
            //mode: "range",
            altInput: false,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            //maxDate: endOfYear,
            disableMobile: true
        });

        var flatpickrIns = flatpickr("#timepicker3", {
            //mode: "range",
            altInput: false,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            //maxDate: endOfYear,
            disableMobile: true
        });
        /*
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
        */

        $('.btn-action').on('click', function() {
            const action = $(this).data('action');
            const form = $('#main-form');
            const url = form.attr('action');
            const selectedSasaran = $('#sasaran_proyek_id').val();

            if (selectedSasaran === 'other') {
                Swal.fire({
                    icon: 'info',
                    title: 'Sasaran Belum Bisa Dipakai',
                    text: 'Silakan ajukan sasaran lainnya terlebih dahulu, lalu tunggu persetujuan MR sebelum menyimpan risiko.',
                });
                return;
            }

            // Penting: inputmask kadang perlu di unmask manual jika tidak autoUnmask
            const data = new FormData(form[0]);
            data.append('action', action);

            // Bersihkan error lama sebelum submit baru
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('.select2-selection').removeClass('border-danger');

            // Tampilkan konfirmasi sebelum mengirim request
            Swal.fire({
                title: 'Simpan Data?',
                text: "Apakah Anda yakin ingin menyimpan data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user menekan "Ya", lanjutkan request AJAX
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
                            if (xhr.status === 422) {
                                // Error Validasi Laravel
                                const errors = xhr.responseJSON.errors;
                                let firstErrorElement = null;

                                $.each(errors, function(key, messages) {
                                    let message = messages[0]; // Ambil pesan error pertama
                                    let inputElement;

                                    // 1. Cek apakah ini Array Input (contoh: penyebab_risiko.0)
                                    if (key.includes('.')) {
                                        let parts = key.split('.');
                                        let name = parts[0];
                                        let index = parts[1];
                                        // Cari input array berdasarkan urutan index
                                        inputElement = $(`[name="${name}[]"]:eq(${index})`);
                                    } else {
                                        // 2. Input Biasa (sasaran_proyek_id, peristiwa_risiko_id, dll)
                                        inputElement = $(`[name="${key}"]`);
                                        // Fallback cari by ID jika name tidak ketemu
                                        if (inputElement.length === 0) inputElement = $(`#${key}`);
                                    }

                                    if (inputElement.length > 0) {
                                        // A. Handle Select2 (Khusus dropdown)
                                        if (inputElement.hasClass('select2-hidden-accessible')) {
                                            // Beri border merah pada container Select2 tampilannya
                                            inputElement.next('.select2-container').find('.select2-selection').addClass('border-danger');
                                            // Tambah pesan error di bawah dropdown
                                            inputElement.next('.select2-container').after(`<div class="invalid-feedback d-block text-danger mt-1"><small>${message}</small></div>`);
                                        }
                                        // B. Handle Input Biasa / Textarea
                                        else {
                                            inputElement.addClass('is-invalid');

                                            // Cek lokasi pesan error (khusus input group atau table)
                                            if(inputElement.parent('.input-group').length) {
                                                inputElement.parent().after(`<div class="invalid-feedback d-block">${message}</div>`);
                                            } else {
                                                inputElement.after(`<div class="invalid-feedback d-block">${message}</div>`);
                                            }
                                        }

                                        // Simpan elemen error pertama untuk auto-scroll
                                        if (!firstErrorElement) {
                                            firstErrorElement = inputElement;
                                        }
                                    }
                                });

                                // Tampilkan Notifikasi Swal
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Data belum lengkap',
                                    html: 'Mohon periksa kembali form inputan.',
                                }).then(() => {
                                    // Auto Scroll ke error pertama
                                    if (firstErrorElement) {
                                        // Jika elemennya Select2, scroll ke containernya
                                        let targetScroll = firstErrorElement.hasClass('select2-hidden-accessible')
                                            ? firstErrorElement.next('.select2-container')
                                            : firstErrorElement;

                                        $('html, body').animate({
                                            scrollTop: targetScroll.offset().top - 150 // Buffer header
                                        }, 500);
                                    }
                                });

                            } else {
                                // Error Server Lain (500 dll)
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: xhr.responseJSON.message || 'Error Server Internal'
                                });
                            }
                            // const errors = xhr.responseJSON.errors;
                            // if (errors) {
                            //     let message = '<ul>';
                            //     for (const key in errors) {
                            //         message += `<li>${errors[key]}</li>`;
                            //     }
                            //     message += '</ul>';
                            //     Swal.fire({
                            //         icon: 'error',
                            //         title: 'Gagal',
                            //         html: message
                            //     });
                            // } else {
                            //     Swal.fire({
                            //         icon: 'error',
                            //         title: 'Gagal',
                            //         text: xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan data'
                            //     });
                            // }
                        }
                    });
                }
            });
        });

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
