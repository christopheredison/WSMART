@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Edit Identifikasi Risiko</h3>
        </div>
    </div>

    <form id="main-form" action="{{ route('risk-register-ap.update', $identifikasiRisiko->id) }}" method="POST" class="row g-3">
        @csrf
        @method('PUT')
        <input type="hidden" name="periode_id" value="{{ $selectedPeriode->id }}">
        <!-- ::DataRisiko Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">1</span></span>
                        <span class="h3 mb-0">Data Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="divider mb-3 mb-md-5 mt-0">
                        <div class="divider-text"><h5 class="mb-0 ff-heading-sm">Periode Tahun {{ $selectedPeriode->tahun }}</h5></div>
                    </div>

                    <div class="row g-3 gx-md-5">
                        <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Sasaran Risiko <span class="text-danger">*</span></label>
                                <div class="w-100">
                                    <textarea class="form-control @error('target_capaian_kinerja') is-invalid @enderror"
                                        id="target_capaian_kinerja" name="target_capaian_kinerja" rows="3"
                                        placeholder="Masukkan Sasaran Risiko" required>{{ old('target_capaian_kinerja', $identifikasiRisiko->target_capaian_kinerja) }}</textarea>
                                    @error('target_capaian_kinerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Taksonomi Danantara</label>
                                <div class="w-100">
                                    <select class="form-select select2" name="taksonomi_risiko_id" required>
                                        <option value="">Pilih Taksonomi</option>
                                        @foreach($taksonomiRisikos as $tax)
                                            <option value="{{ $tax->id }}" {{ old('taksonomi_risiko_id', $identifikasiRisiko->taksonomi_risiko_id ?? '') == $tax->id ? 'selected' : '' }}>
                                                {{ $tax->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control @error('peristiwa_risiko') is-invalid @enderror"
                                    id="peristiwa_risiko" name="peristiwa_risiko" rows="3"
                                    placeholder="Peristiwa Risiko" required>{{ old('peristiwa_risiko', $identifikasiRisiko->peristiwa_risiko) }}</textarea>
                                <label for="peristiwa_risiko">Peristiwa Risiko <span class="text-danger">*</span></label>
                                @error('peristiwa_risiko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control @error('deskripsi_peristiwa_risiko') is-invalid @enderror"
                                    id="deskripsi_peristiwa_risiko" name="deskripsi_peristiwa_risiko" rows="3"
                                    placeholder="Deskripsi Peristiwa Risiko" required>{{ old('deskripsi_peristiwa_risiko', $identifikasiRisiko->deskripsi_peristiwa_risiko) }}</textarea>
                                <label for="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko <span class="text-danger">*</span></label>
                                @error('deskripsi_peristiwa_risiko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">2</span></span>
                        <span class="h3 mb-0">Dampak Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="dampak-risiko-body">
                        @forelse($identifikasiRisiko->dampakRisikos as $index => $dampak)
                        <div class="row g-2 mb-3 dampak-row-item">
                            <div class="col">
                                <div class="form-floating">
                                    <textarea class="form-control input-dampak-risiko" name="dampak_risiko[]" placeholder="Masukkan Dampak Risiko" required>{{ $dampak->dampak_risiko }}</textarea>
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
                        @empty
                        <div class="row g-2 mb-3 dampak-row-item">
                            <div class="col">
                                <div class="form-floating">
                                    <textarea class="form-control input-dampak-risiko" name="dampak_risiko[]" placeholder="Masukkan Dampak Risiko" required></textarea>
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
                        @endforelse
                    </div>
                    <div class="row">
                        <div class="col-auto ms-auto">
                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-dampak">
                                <i class='bx bx-plus fs-5'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">3</span></span>
                        <span class="h3 mb-0">Penyebab Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="penyebab-risiko-body">
                        @forelse($identifikasiRisiko->penyebabRisiko as $penyebab)
                        <div class="row g-2 mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <textarea class="form-control input-penyebab-risiko" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko" required>{{ $penyebab->penyebab_risiko }}</textarea>
                                    <label>Penyebab Risiko <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="row g-2 mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <textarea class="form-control input-penyebab-risiko" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko" required></textarea>
                                    <label>Penyebab Risiko <span class="text-danger">*</span></label>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeRow(event)">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    <div class="row">
                        <div class="col-auto ms-auto">
                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-column">
                                <i class='bx bx-plus fs-5'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                                            <textarea class="form-control" name="metode_pengukuran[]" cols="2" required>{{ $kri ? $kri->metode_pengukuran : '' }}</textarea>
                                            <label>Metode Pengukuran <span class="text-danger">*</span></label>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2 mb-1">
                                        <span class="text-muted fw-bold">Ambang Batas / Threshold KRI</span>
                                    </div>
                                    @php
                                        // Fungsi closure/helper untuk mengecek dan memformat batas KRI
                                        $formatKriValue = function($val) {
                                            if ($val === null || $val === '') return ['is_number' => true, 'value' => ''];
                                            $val = trim($val);
                                            
                                            // Cek jika datanya adalah angka (mendukung format desimal koma maupun titik)
                                            if (preg_match('/^-?\d+([.,]\d+)?$/', $val)) {
                                                // Pastikan format pemisah desimal menggunakan koma agar cocok dengan Inputmask
                                                $formattedVal = str_replace('.', ',', $val);
                                                return ['is_number' => true, 'value' => $formattedVal];
                                            }
                                            
                                            // Jika data berupa text/simbol murni (Contoh: "< 10%")
                                            return ['is_number' => false, 'value' => $val];
                                        };

                                        $aman = $formatKriValue($kri ? $kri->batas_aman : '');
                                        $waspada = $formatKriValue($kri ? $kri->batas_waspada : '');
                                        $bahaya = $formatKriValue($kri ? $kri->batas_bahaya : '');
                                    @endphp

                                    <div class="col-3">
                                        <div class="form-group form-floating">
                                            <input type="text" class="form-control satuan-kri-input" name="satuan_kri[]" value="{{ $kri ? $kri->satuan_kri : '' }}" placeholder="Satuan / Unit KRI" required>
                                            <label>Satuan / Unit KRI <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control {{ $aman['is_number'] ? 'decimal-input' : '' }} border-success" name="batas_aman[]" value="{{ $aman['value'] }}" placeholder="0" required>
                                            <label>Risk Limit <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control {{ $waspada['is_number'] ? 'decimal-input' : '' }} border-warning" name="batas_waspada[]" value="{{ $waspada['value'] }}" placeholder="0" required>
                                            <label>Risk Appetite <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="form-floating">
                                            <input type="text" class="form-control {{ $bahaya['is_number'] ? 'decimal-input' : '' }} border-danger" name="batas_bahaya[]" value="{{ $bahaya['value'] }}" placeholder="0" required>
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
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">5</span></span>
                        <span class="h3 mb-0">Kontrol</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-3 gx-xxl-6 mb-3">
                        <div class="col-md-6 col-lg-5 col-xxl-6">
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Kontrol Eksisting <span class="text-danger">*</span></label>
                                <div class="w-100">
                                    <div id="kontrol-eksisting-body">
                                        @forelse($identifikasiRisiko->kontrolEksistings as $kontrol)
                                        <div class="row g-2 mb-2">
                                            <div class="col">
                                                <textarea class="form-control" name="kontrol_eksisting[]" rows="3" placeholder="Masukkan kontrol eksisting">{{ $kontrol->kontrol_eksisting }}</textarea>
                                            </div>
                                            <div class="col-auto d-flex align-items-center">
                                                <button type="button" class="btn btn-icon-danger h-100" onclick="removeKontrolRow(event)">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @empty
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
                                        @endforelse
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-auto ms-auto">
                                            <button type="button" class="btn btn-outline-secondary rounded-pill p-2" id="add-kontrol-eksisting">
                                                <i class='bx bx-plus fs-5'></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-7 col-xxl-6">
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker2">Perkiraan Waktu Mulai <span class="text-danger">*</span></label>
                                <div class="w-100">
                                    <input class="form-control datetimepicker bg-white @error('perkiraan_waktu_mulai_terpapar_risiko') is-invalid @enderror"
                                        name="perkiraan_waktu_mulai_terpapar_risiko"
                                        id="timepicker2" type="text" placeholder="d/m/y"
                                        value="{{ old('perkiraan_waktu_mulai_terpapar_risiko', $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai ? \Carbon\Carbon::parse($identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y') : '') }}" />
                                    @error('perkiraan_waktu_mulai_terpapar_risiko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker3">Perkiraan Waktu Selesai <span class="text-danger">*</span></label>
                                <div class="w-100">
                                    <input class="form-control datetimepicker bg-white @error('perkiraan_waktu_selesai_terpapar_risiko') is-invalid @enderror"
                                        name="perkiraan_waktu_selesai_terpapar_risiko"
                                        id="timepicker3" type="text" placeholder="d/m/y"
                                        value="{{ old('perkiraan_waktu_selesai_terpapar_risiko', $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir ? \Carbon\Carbon::parse($identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y') : '') }}" />
                                    @error('perkiraan_waktu_selesai_terpapar_risiko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($projects) && $projects->isNotEmpty())
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="tabelRisikoProyekTerpilih">
                            <thead class="bg-light">
                                </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="col-12 mt-5">
            <div class="row g-2">
                <div class="col-auto order-1">
                    <a href="{{ route('risk-register-ap.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="save" class="btn btn-warning bg-warning ms-auto btn-action">Simpan dan Keluar</button>
                </div>
                @if($identifikasiRisiko->request_edit != 2)
                    <div class="col-auto order-3 px-0 px-md-1 d-flex">
                        <button type="button" data-action="savenext" class="btn btn-primary ms-auto btn-action">Simpan dan Lanjutkan Analisa</button>
                    </div>
                @else
                    <div class="col-auto order-3 px-0 px-md-1 d-flex">
                        <button type="button" data-action="savenext" class="btn btn-primary ms-auto btn-action">Simpan dan Lanjut Perencanaan</button>
                    </div>
                @endif
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

        const masterKris = @json($masterKris->keyBy('id'));
        const kontrolExistings = @json($kontrolEksistings->keyBy('id'));

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

        $('#add-dampak').click(function() {
            let html = `
            <div class="row g-2 mb-3 dampak-row-item">
                <div class="col">
                    <div class="form-floating">
                        <input type="hidden" name="penyebab_dampak_id[]">
                        <textarea class="form-control input-dampak-risiko" name="dampak_risiko[]" placeholder="Masukkan Dampak Risiko" required></textarea>
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

        // Add Column Penyebab Risiko
        let row = 0;
        $('#add-column').click(function() {
            row++;
            let html = `
            <div class="row g-2">
                <div class="col">
                <div class="form-floating">
                    <input type="hidden" name="penyebab_risiko_id[]" value="">
                    <textarea class="form-control input-penyebab-risiko" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko" required></textarea>
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

            // Enable all delete buttons when we have more than one row
            if ($('#penyebab-risiko-body .row').length > 1) {
                $('#penyebab-risiko-body .btn-icon-danger').prop('disabled', false);
            }
        });

        // Add Column Key Risk Indicator
        $('#add-column-kri').click(function() {
            row++;
            const peristiwaRisikoId = $('#peristiwa_risiko').val();
            let rowIdx = $('#kri-body .kri-row-item').length + 1;

            let html = `
                <div class="row g-2 mb-3 border-bottom pb-3">
                    <div class="col">
                        <input type="hidden" name="kri_ids[]" value="">
                        <div class="mb-2">
                            <h6 class="mb-0 fw-bold text-primary kri-header-title">Parameter / Key Risk Indicator ${rowIdx}</h6>
                        </div>
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
                                <div class="form-group form-floating">
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
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            minDate: startOfYear,
            maxDate: endOfYear,
            disableMobile: true
        });

        var flatpickrIns = flatpickr("#timepicker3", {
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            minDate: startOfYear,
            maxDate: endOfYear,
            disableMobile: true
        });

        $('.btn-action').on('click', function() {
            const action = $(this).data('action');
            const form = $('#main-form');
            const url = form.attr('action');
            const data = new FormData(form[0]);
            data.append('action', action);

            const $clickedButton = $(this);
            const originalText = $clickedButton.html();

            // Clear previous errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Apakah Anda yakin ingin memperbarui data ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $clickedButton.html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...').prop('disabled', true);

                    $.ajax({
                        url: url,
                        type: 'POST', // Method spoofing via @method('PUT') inside form handles PUT
                        data: data,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if (response.redirect) {
                                Swal.fire({
                                    icon: 'success', title: 'Berhasil',
                                    text: response.message, showConfirmButton: false, timer: 1500
                                }).then(() => { window.location.href = response.redirect; });
                            }
                        },
                        error: function(xhr) {
                            $clickedButton.html(originalText).prop('disabled', false);

                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                let firstError = null;

                                $.each(errors, function(key, messages) {
                                    // Handle Array Inputs (e.g., penyebab_risiko.0)
                                    let inputName = key;
                                    if(key.includes('.')) {
                                        let parts = key.split('.');
                                        // Convert "penyebab_risiko.0" to "penyebab_risiko[]" index 0
                                        let fieldName = parts[0];
                                        let index = parts[1];
                                        // Selector untuk elemen array ke-sekian
                                        let el = $(`[name="${fieldName}[]"]:eq(${index})`);

                                        el.addClass('is-invalid');
                                        if(el.parent('.form-floating').length) {
                                            el.parent().after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                                        } else {
                                            el.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                                        }
                                        if(!firstError) firstError = el;
                                    } else {
                                        // Standard Inputs
                                        let el = $(`[name="${key}"]`);
                                        el.addClass('is-invalid');
                                        el.after(`<div class="invalid-feedback d-block">${messages[0]}</div>`);
                                        if(!firstError) firstError = el;
                                    }
                                });

                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Validasi Gagal',
                                    text: 'Mohon periksa kembali inputan Anda yang berwarna merah.'
                                });

                                if(firstError) {
                                    $('html, body').animate({ scrollTop: firstError.offset().top - 150 }, 500);
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: xhr.responseJSON.message || 'Terjadi kesalahan pada server.'
                                });
                            }
                        }
                    });
                }
            });
        });

        const preloadedData = @json($identifikasiRisiko);

        for (const key in preloadedData) {
            if (preloadedData.hasOwnProperty(key)) {
                const value = preloadedData[key];
                if (Array.isArray(value)) {
                    if (key === 'penyebab_risiko') {
                        // value.forEach((penyebab, index) => {
                        //     if (index === 0) {
                        //         $('.input-penyebab-risiko').val(penyebab.penyebab_risiko).attr('name', `penyebab_risiko[${penyebab.id}]`);
                        //     } else {
                        //         $('#add-column').click();
                        //         $(`.input-penyebab-risiko`).last().val(penyebab.penyebab_risiko).attr('name', `penyebab_risiko[${penyebab.id}]`);
                        //     }
                        // });
                    } else if (key === 'dampak_risikos') {
                        // value.forEach((dampak, index) => {
                        //     if (index === 0) {
                        //         $('.input-dampak-risiko').val(dampak.dampak_risiko).attr('name', `dampak_risiko[${dampak.id}]`);
                        //     } else {
                        //         $('#add-dampak').click();
                        //         $(`.input-dampak-risiko`).last().val(dampak.dampak_risiko).attr('name', `dampak_risiko[${dampak.id}]`);
                        //     }
                        // });
                    } else if (key === 'kris') {
                        // value.forEach((kri, index) => {
                        //     if (index === 0) {
                        //         $(`[name="key_risk_indicator[]"]`).val(kri.kri);
                        //         $(`[name="satuan_kri[]"]`).val(kri.satuan_kri);
                        //         $(`[name="batas_aman[]"]`).val(kri.batas_aman);
                        //         $(`[name="batas_waspada[]"]`).val(kri.batas_waspada);
                        //         $(`[name="batas_bahaya[]"]`).val(kri.batas_bahaya);
                        //     } else {
                        //         $('#add-column-kri').click();
                        //         $(`[name="key_risk_indicator[]"]`).last().val(kri.kri);
                        //         $(`[name="satuan_kri[]"]`).last().val(kri.satuan_kri);
                        //         $(`[name="batas_aman[]"]`).last().val(kri.batas_aman);
                        //         $(`[name="batas_waspada[]"]`).last().val(kri.batas_waspada);
                        //         $(`[name="batas_bahaya[]"]`).last().val(kri.batas_bahaya);
                        //     }
                        // });
                    } else if (key === 'parameter_risikos') {
                        const params = value;
                        params.forEach((param, index) => {
                            if (index === 0) {
                                $('[name="parameter_risiko_id[]"]').first().val(param.id);
                                $('[name="param_nama[]"]').first().val(param.nama);
                                $('[name="param_formula[]"]').first().val(param.formula);
                                $('[name="param_satuan[]"]').first().val(param.satuan);
                            } else {
                                $('#add-parameter').click();
                                $('[name="parameter_risiko_id[]"]').last().val(param.id);
                                $('[name="param_nama[]"]').last().val(param.nama);
                                $('[name="param_formula[]"]').last().val(param.formula);
                                $('[name="param_satuan[]"]').last().val(param.satuan);
                            }
                        });
                    }
                }
                else {
                    $(`[name="${key}"]`).val(value).change();
                }
            }
        }

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
