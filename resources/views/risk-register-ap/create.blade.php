@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Identifikasi Risiko</h3>
        </div>
    </div>

    <form class="row g-3" method="POST" action="{{ route('risk-register-ap.store', request()->route('pid')) }}" id="main-form">
        <input type="hidden" name="draft_key" value="{{ request()->draft_key }}">
        @csrf
        <input type="hidden" name="periode_id" value="{{ $selectedPeriode->id }}">
        @if(request()->has('unit_id'))
            <input type="hidden" name="unit_id" value="{{ request('unit_id') }}">
        @endif

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
                    <div class="row">
                        <div class="col-12">
                            <div class="divider mb-3 mb-md-5 mt-0">
                                <div class="divider-text">
                                    <h5 class="mb-0 ff-heading-sm">Periode Tahun {{ $selectedPeriode->tahun }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 gx-md-5">
                        {{-- <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="target_capaian_kinerja" name="target_capaian_kinerja" rows="3" placeholder="Sasaran" required>{{ old('target_capaian_kinerja') }}</textarea>
                                <label for="target_capaian_kinerja">Sasaran Risiko</label>
                            </div>
                        </div> --}}

                        <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Sasaran Risiko <span class="text-danger">*</span></label>
                                <div class="w-100">
                                    <textarea class="form-control @error('target_capaian_kinerja') is-invalid @enderror"
                                        id="target_capaian_kinerja" name="target_capaian_kinerja" rows="3"
                                        placeholder="Masukkan Sasaran Risiko" required>{{ old('target_capaian_kinerja') }}</textarea>
                                    @error('target_capaian_kinerja')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Taksonomi Risiko</label>
                                <div class="w-100">
                                    <select class="form-select select2" name="taksonomi_risiko_id" required>
                                        <option value="">Pilih Taksonomi</option>
                                        @foreach($taksonomiRisikos as $tax)
                                            <option value="{{ $tax->id }}">
                                                {{ $tax->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-12">
                            <div class="form-group d-lg-flex">
                                <div class="form-floating flex-grow-1">
                                    <select class="form-select select2" id="jenis_risiko_id" name="jenis_risiko_id" required>
                                        <option value="" selected disabled>Pilih Jenis Risiko</option>
                                        @foreach($jenisRisiko as $id => $title)
                                            @php
                                                $kategori = \App\Models\JenisRisiko::find($id)->kategoriRisiko;
                                                $kategoriTitle = $kategori ? $kategori->title : '';
                                            @endphp
                                            <option value="{{ $id }}" {{ old('jenis_risiko_id') == $id ? 'selected' : '' }}>
                                                {{ $kategoriTitle }} - {{ $title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="jenis_risiko_id">Jenis Risiko T2 & T3 KBUMN</label>
                                </div>
                            </div>
                        </div> --}}
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control @error('peristiwa_risiko') is-invalid @enderror"
                                    id="peristiwa_risiko" name="peristiwa_risiko" rows="3"
                                    placeholder="Peristiwa Risiko" required>{{ old('peristiwa_risiko') }}</textarea>
                                <label for="peristiwa_risiko">Peristiwa Risiko <span class="text-danger">*</span></label>
                                @error('peristiwa_risiko')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control @error('deskripsi_peristiwa_risiko') is-invalid @enderror"
                                    id="deskripsi_peristiwa_risiko" name="deskripsi_peristiwa_risiko" rows="3"
                                    placeholder="Deskripsi Peristiwa Risiko" required>{{ old('deskripsi_peristiwa_risiko') }}</textarea>
                                <label for="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko <span class="text-danger">*</span></label>
                                @error('deskripsi_peristiwa_risiko')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="wbs" name="wbs" rows="3"
                                    value="{{ old('wbs') }}" placeholder="WBS" required></textarea>
                                <label for="wbs">WBS</label>
                            </div>
                        </div> --}}
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
                                    <label>Dampak Risiko <span class="text-danger">*</span></label>
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
                                    <textarea class="form-control" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko" required></textarea>
                                    <label>Penyebab Risiko <span class="text-danger">*</span></label>
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
                        <span class="h3 mb-0">Parameter / KRI</span>
                    </div>
                </div>
                
                <div class="card-body">
                    <div id="kri-body">
                        @php
                            $kriItems = isset($identifikasiRisiko) && $identifikasiRisiko->kris->count() > 0 ? $identifikasiRisiko->kris : [null];
                        @endphp
                        
                        @foreach($kriItems as $kri)
                        <div class="row g-2 mb-3 border-bottom pb-3">
                            <div class="col">
                                <input type="hidden" name="kri_ids[]" value="{{ $kri ? $kri->id : '' }}">

                                <div class="mb-2">
                                    <h6 class="mb-0 fw-bold text-primary kri-header-title">Parameter / Key Risk Indicator {{ $loop->iteration }}</h6>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="form-group form-floating">
                                            <input type="text" class="form-control" name="key_risk_indicator[]" placeholder="Parameter / Key Risk Indicator" value="{{ $kri ? $kri->kri : '' }}" required>
                                            <label>Parameter / Key Risk Indicator <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="form-group form-floating">
                                            <select class="form-select" name="tren_parameter[]" required>
                                                <option value="" {{ !$kri ? 'selected' : '' }} disabled>Pilih Tren Parameter</option>
                                                <option value="Higher is Better" {{ ($kri && $kri->tren_parameter == 'Higher is Better') ? 'selected' : '' }}>Higher is Better</option>
                                                <option value="Lower is Better" {{ ($kri && $kri->tren_parameter == 'Lower is Better') ? 'selected' : '' }}>Lower is Better</option>
                                            </select>
                                            <label>Tren Parameter <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-group form-floating">
                                            <textarea class="form-control" name="metode_pengukuran[]" placeholder="Metode Pengukuran" cols="2" required>{{ $kri ? $kri->metode_pengukuran : '' }}</textarea>
                                            <label>Metode Pengukuran <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2 mb-1">
                                        <span class="text-muted fw-bold">Ambang Batas / Threshold KRI</span>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control satuan-kri-input" name="satuan_kri[]" value="{{ $kri ? $kri->satuan_kri : '' }}" placeholder="Satuan / Unit KRI" required>
                                            <label>Satuan / Unit KRI <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control decimal-input border-success" name="batas_aman[]" value="{{ $kri ? $kri->batas_aman : '' }}" placeholder="0">
                                            <label>Risk Limit <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control decimal-input border-warning" name="batas_waspada[]" value="{{ $kri ? $kri->batas_waspada : '' }}" placeholder="0">
                                            <label>Risk Appetite <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control decimal-input border-danger" name="batas_bahaya[]" value="{{ $kri ? $kri->batas_bahaya : '' }}" placeholder="0">
                                            <label>Risk Tolerance <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center ms-auto">
                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
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
                            {{-- <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Jenis Kontrol Eksisting</label>
                                <select name="jenis_kontrol_eksisting_id" class="form-select select2">
                                    <option value="" selected disabled>Jenis Kontrol Eksisting</option>
                                    @foreach ($jenisKontrolEksistings as $jenisKontrolEksisting)
                                        <option value="{{ $jenisKontrolEksisting->id }}">
                                            {{ $jenisKontrolEksisting->jenis_kontrol }}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Kontrol Eksisting <span class="text-danger">*</span></label>
                                <div class="w-100">
                                    <div id="kontrol-eksisting-body">
                                        <div class="row g-2 mb-2">
                                            <div class="col">
                                                <textarea class="form-control" name="kontrol_eksisting[]" rows="3" placeholder="Masukkan kontrol eksisting">{{ old('kontrol_eksisting.0') }}</textarea>
                                            </div>
                                            <div class="col-auto d-flex align-items-center">
                                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeKontrolRow(event)" disabled>
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-auto ms-auto">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-kontrol-eksisting"
                                                data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Tambah Kontrol Eksisting">
                                                <i class='bx bx-plus fs-5'></i>
                                            </button>
                                        </div>
                                    </div>
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
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker2">Perkiraan Waktu Mulai Terpapar Risiko <span class="text-danger">*</span></label>
                                <div class="w-100">
                                    <input class="form-control datetimepicker bg-white @error('perkiraan_waktu_mulai_terpapar_risiko') is-invalid @enderror"
                                        name="perkiraan_waktu_mulai_terpapar_risiko"
                                        id="timepicker2" type="text" placeholder="d/m/y"
                                        value="{{ old('perkiraan_waktu_mulai_terpapar_risiko') }}" />
                                    @error('perkiraan_waktu_mulai_terpapar_risiko')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker3">Perkiraan Waktu Selesai Terpapar Risiko <span class="text-danger">*</span></label>
                                <div class="w-100">
                                    <input class="form-control datetimepicker bg-white @error('perkiraan_waktu_selesai_terpapar_risiko') is-invalid @enderror"
                                        name="perkiraan_waktu_selesai_terpapar_risiko"
                                        id="timepicker3" type="text" placeholder="d/m/y"
                                        value="{{ old('perkiraan_waktu_selesai_terpapar_risiko') }}" />
                                    @error('perkiraan_waktu_selesai_terpapar_risiko')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                    <a href="{{ route('risk-register-ap.index') }}" class="btn btn-outline-secondary">Batal</a>
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
        reindexKri();
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

    function initKriMasks() {
        $('.decimal-input').inputmask({
            alias: 'numeric',
            groupSeparator: '.',
            radixPoint: ',',
            autoGroup: true,
            digits: 2,
            digitsOptional: false,
            placeholder: '0',
            rightAlign: false,
            autoUnmask: true,
            removeMaskOnSubmit: true
        });
    }

    $(document).ready(function() {
        initKriMasks();

        // Event listener saat satuan diubah agar ambang batas otomatis berganti unitnya
        $(document).on('input', '.satuan-kri-input', function() {
            let val = $(this).val() || '-';
            $(this).closest('.row').find('.satuan-addon').text(val);
        });

        $('.select2').select2({
            width: '100%',
        });

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
        // Add Column Penyebab Risiko
        let row = 0;

        console.log('Document Ready');

        const peristiwaRisikoElement = document.getElementById('peristiwa_risiko');
        if (peristiwaRisikoElement) {
            console.log('Element #peristiwa_risiko ditemukan.');

            // Pasang event listener
            $('#peristiwa_risiko').on('change', function () {
                const peristiwaRisikoId = $(this).val(); // Mendapatkan value dari dropdown
                console.log('Peristiwa Risiko ID:', peristiwaRisikoId);

                // Pastikan dropdown KRI di-reset
                //const kriDropdowns = $('select[name="master_kri_id[]"]');
                //kriDropdowns.html('<option value="">Pilih</option>');

                // $('input[name="key_risk_indicator[]"]').val('').prop('disabled', true);
                // $('input[name="satuan_kri[]"]').val('').prop('disabled', true);
                // $('input[name="batas_aman[]"]').val('').prop('disabled', true);
                // $('input[name="batas_waspada[]"]').val('').prop('disabled', true);
                // $('input[name="batas_bahaya[]"]').val('').prop('disabled', true);

                // Lakukan fetch untuk mendapatkan data KRI
                if (peristiwaRisikoId) {
                    fetch(`/master-kri/${peristiwaRisikoId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('HTTP error ' + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Data KRI:', data); // Debug respons dari server
                            kriDropdowns.each(function () {
                                const dropdown = $(this);
                                data.forEach(kri => {
                                    dropdown.append(new Option(kri.kri, kri.id));
                                });
                            });
                        })
                        .catch(error => console.error('Fetch Error:', error));
                }

                fetchKontrolEksisting();
            });

            //console.log('Event listener dipasang.');

        } else {
            console.error('Element #peristiwa_risiko tidak ditemukan.');
        }

        $('#add-dampak').click(function() {
            let html = `
            <div class="row g-2 mb-3 dampak-row-item">
                <div class="col">
                    <div class="form-floating">
                        <textarea class="form-control" name="dampak_risiko[]" placeholder="Masukkan Dampak Risiko" required></textarea>
                        <label>Dampak Risiko <span class="text-danger">*</span></label>
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
                    <textarea class="form-control" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko" required></textarea>
                    <label>Penyebab Risiko <span class="text-danger">*</span></label>
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
                <div class="row g-2 mb-3 border-bottom pb-3">
                    <div class="col">
                        <input type="hidden" name="kri_ids[]" value="">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="form-group form-floating">
                                    <input type="text" class="form-control" name="key_risk_indicator[]" placeholder="Parameter / Key Risk Indicator" required>
                                    <label>Parameter / Key Risk Indicator <span class="text-danger">*</span></label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-group form-floating">
                                    <select class="form-select" name="tren_parameter[]" required>
                                        <option value="" selected disabled>Pilih Tren Parameter</option>
                                        <option value="Higher is Better">Higher is Better</option>
                                        <option value="Lower is Better">Lower is Better</option>
                                    </select>
                                    <label>Tren Parameter <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group form-floating">
                                    <textarea class="form-control" name="metode_pengukuran[]" placeholder="Metode Pengukuran" cols="2" required></textarea>
                                    <label>Metode Pengukuran <span class="text-danger">*</span></label>
                                </div>
                            </div>

                            <div class="col-12 mt-2 mb-1">
                                <span class="text-muted fw-bold">Ambang Batas / Threshold KRI</span>
                            </div>
                            <div class="col-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control satuan-kri-input" name="satuan_kri[]" placeholder="Satuan / Unit KRI" required>
                                    <label>Satuan / Unit KRI <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control decimal-input border-success" name="batas_aman[]" placeholder="0">
                                    <label>Risk Limit <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control decimal-input border-warning" name="batas_waspada[]" placeholder="0">
                                    <label>Risk Appetite <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control decimal-input border-danger" name="batas_bahaya[]" placeholder="0">
                                    <label>Risk Tolerance <span class="text-danger">*</span></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto d-flex align-items-center ms-auto">
                        <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>`;
            $('#kri-body').append(html);
            
            initKriMasks(); // Pasang mask untuk elemen yang baru saja ditambahkan

            // Enable all delete buttons when we have more than one row
            if ($('#kri-body .row.border-bottom').length > 1) {
                $('#kri-body .btn-icon-danger').prop('disabled', false);
            }

            // const newDropdown = $('#kri-body').find('select[name="master_kri_id[]"]').last()[0];
            // if (newDropdown) {
            //     loadKriOptions(newDropdown, peristiwaRisikoId);
            // }
        });

        function reindexKri() {
            $('#kri-body .kri-row-item').each(function(index) {
                let number = index + 1; // Mulai urut dari 1
                
                // Update teks di header KRI
                $(this).find('.kri-header-title').text('Parameter / Key Risk Indicator ' + number);
                
                // Update teks di span Threshold
                $(this).find('.kri-threshold-title').text('Ambang Batas / Threshold KRI ' + number);
            });
        }

        $('#kri-body').on('change', '[name="master_kri_id[]"]', function() {
            const row = $(this).closest('.row');
            const kri = masterKris[$(this).val()];
            row.find('[name="key_risk_indicator[]"]').val(kri.kri);
            row.find('[name="satuan_kri[]"]').val(kri.satuan_kri);
            row.find('[name="batas_aman[]"]').val(kri.batas_aman);
            row.find('[name="batas_waspada[]"]').val(kri.batas_waspada);
            row.find('[name="batas_bahaya[]"]').val(kri.batas_bahaya);
        });


        //var today = new Date();
        //var endOfYear = new Date(today.getFullYear(), 11, 31);

        var periodeYear = {{ $selectedPeriode->tahun }};
        console.log('Periode Year:', periodeYear);

        var today = new Date();
        var startOfYear = new Date(periodeYear, 0, 1); // 1 Januari tahun periode
        var endOfYear = new Date(periodeYear, 11, 31); // 31 Desember tahun periode
        var defaultDate = today;
        if (today < startOfYear || today > endOfYear) {
            defaultDate = startOfYear;
        }

        var flatpickrIns = flatpickr("#timepicker2", {
            //mode: "range",
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            //maxDate: endOfYear,
            minDate: startOfYear,
            maxDate: endOfYear,
            //defaultDate: defaultDate,
            disableMobile: true
        });

        var flatpickrIns = flatpickr("#timepicker3", {
            //mode: "range",
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            //maxDate: endOfYear,
            minDate: startOfYear,
            maxDate: endOfYear,
            //defaultDate: defaultDate,
            disableMobile: true
        });

        $('.btn-action').on('click', function() {
            const action = $(this).data('action');
            const form = $('#main-form');
            const url = form.attr('action');
            const data = new FormData(form[0]);
            const $clickedButton = $(this);
            const originalText = $clickedButton.html();

            data.append('action', action);

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
                    if (window.isSubmitting) return false;
                    window.isSubmitting = true;

                    // Tampilkan loading state dan nonaktifkan tombol
                    $clickedButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
                    $('.btn-action').prop('disabled', true);
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
                                    timer: 4500
                                }).then(() => {
                                    window.location.href = response.redirect;
                                });
                            } else {
                                $('.btn-action').prop('disabled', false);
                                $clickedButton.html(originalText);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message || 'Data berhasil disimpan',
                                    showConfirmButton: false,
                                    timer: 4500
                                });

                            }
                        },
                        error: function(xhr) {
                            window.isSubmitting = false;
                            $('.btn-action').prop('disabled', false);
                            $clickedButton.html(originalText);

                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                let firstErrorElement = null;

                                $('.is-invalid').removeClass('is-invalid');
                                $('.invalid-feedback').remove();
                                $('.select2-selection').removeClass('border-danger');

                                $.each(errors, function(key, messages) {
                                    let message = messages[0];
                                    let inputElement;

                                    if (key.includes('.')) {
                                        let parts = key.split('.');
                                        let name = parts[0];
                                        let index = parts[1];
                                        inputElement = $(`[name="${name}[]"]:eq(${index})`);
                                    } else {
                                        inputElement = $(`[name="${key}"]`);
                                        if (inputElement.length === 0) inputElement = $(`#${key}`);
                                    }

                                    if (inputElement.length > 0) {
                                        if (inputElement.hasClass('select2-hidden-accessible')) {
                                            inputElement.next('.select2-container').find('.select2-selection').addClass('border-danger');
                                            inputElement.next('.select2-container').after(`<div class="invalid-feedback d-block text-danger mt-1"><small>${message}</small></div>`);
                                        }
                                        else {
                                            inputElement.addClass('is-invalid');

                                            if(inputElement.parent('.input-group').length) {
                                                inputElement.parent().after(`<div class="invalid-feedback d-block">${message}</div>`);
                                            } else {
                                                inputElement.after(`<div class="invalid-feedback d-block">${message}</div>`);
                                            }
                                        }

                                        if (!firstErrorElement) {
                                            firstErrorElement = inputElement;
                                        }
                                    }
                                });

                                const errorMessage = xhr.responseJSON?.message || ''
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Validasi Gagal',
                                    text:  errorMessage ?? 'Mohon periksa kembali isian form yang berwarna merah.',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    if (firstErrorElement) {
                                        let targetScroll = firstErrorElement.hasClass('select2-hidden-accessible')
                                            ? firstErrorElement.next('.select2-container')
                                            : firstErrorElement;

                                        $('html, body').animate({
                                            scrollTop: targetScroll.offset().top - 150
                                        }, 500);
                                    }
                                });

                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: xhr.responseJSON.message || 'Terjadi kesalahan sistem, silakan coba lagi.'
                                });
                            }
                        }
                    });
                }
            });
        });

        $('#add-kontrol-eksisting').click(function() {
            let html = `
                <div class="row g-2 mb-2">
                    <div class="col">
                        <textarea class="form-control" name="kontrol_eksisting[]" rows="3" placeholder="Masukkan kontrol eksisting"></textarea>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <button type="button" class="btn btn-icon-danger h-100" onclick="removeKontrolRow(event)">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#kontrol-eksisting-body').append(html);

            // Enable all delete buttons when we have more than one row
            if ($('#kontrol-eksisting-body .row').length > 1) {
                $('#kontrol-eksisting-body .btn-icon-danger').prop('disabled', false);
            }
        });
    });

    function removeKontrolRow(event) {
        let row = $(event.target).closest('.row');
        row.remove();

        // If only one row remains, disable its delete button
        if ($('#kontrol-eksisting-body .row').length === 1) {
            $('#kontrol-eksisting-body .btn-icon-danger').prop('disabled', true);
        }
    }
</script>
@endpush
