@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Edit Identifikasi Risiko Korporat</h3>
        </div>
    </div>

    <form class="row g-3" method="POST" action="{{ route('corporate-risk.update', $identifikasiRisiko->id) }}" id="main-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="periode_id" value="{{ $selectedPeriode->id }}">

        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">1</span></span>
                        <span class="h3 mb-0">Data Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="divider mb-3 mb-md-5 mt-0">
                                <div class="divider-text"><h5 class="mb-0 ff-heading-sm">Periode Tahun {{ $selectedPeriode->tahun }}</h5></div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 gx-md-5">
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="target_capaian_kinerja" name="target_capaian_kinerja" rows="3" placeholder="Sasaran" required>{{ old('target_capaian_kinerja', $identifikasiRisiko->target_capaian_kinerja) }}</textarea>
                                <label for="target_capaian_kinerja">Sasaran Risiko</label>
                            </div>
                        </div>

                        <div class="col-12">
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
                        </div>
                        {{-- <div class="col-12">
                            <div class="form-group d-lg-flex">
                                <div class="form-floating flex-grow-1">
                                    <select class="form-select select2" id="jenis_risiko_id" name="jenis_risiko_id" required>
                                        <option value="" disabled>Pilih Jenis Risiko</option>
                                        @foreach($jenisRisiko as $id => $title)
                                            @php
                                                $kategori = \App\Models\JenisRisiko::find($id)->kategoriRisiko;
                                                $kategoriTitle = $kategori ? $kategori->title : '';
                                            @endphp
                                            <option value="{{ $id }}" {{ old('jenis_risiko_id', $identifikasiRisiko->jenis_risiko_id) == $id ? 'selected' : '' }}>
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
                                <textarea class="form-control" id="peristiwa_risiko" name="peristiwa_risiko" rows="3" placeholder="Peristiwa Risiko" required>{{ old('peristiwa_risiko', $identifikasiRisiko->peristiwa_risiko) }}</textarea>
                                <label for="peristiwa_risiko">Peristiwa Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="deskripsi_peristiwa_risiko" name="deskripsi_peristiwa_risiko" rows="3" placeholder="Deskripsi Peristiwa Risiko" required>{{ old('deskripsi_peristiwa_risiko', $identifikasiRisiko->deskripsi_peristiwa_risiko) }}</textarea>
                                <label for="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- ::ParameeterRisiko Start -->
        <div class="col-12">
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
        </div>
        <!-- ::ParameeterRisiko End -->

        <!-- ::Threshold Start -->
        <div class="col-12">
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
        </div>
        <!-- ::Threshold End -->

        <!-- ::Penyebab Risiko Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent">
                            <span class="nav-item-circle">4</span>
                        </span>
                        <span class="h3 mb-0">Penyebab Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="penyebab-risiko-body">
                        <div class="row g-2">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="hidden" name="penyebab_risiko_id[]">
                                    <input type="text" class="form-control input-penyebab-risiko" name="penyebab_risiko[]"
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
        <!-- ::Penyebab Risiko End -->

        <!-- ::Key Risk Indicator Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">5</span></span>
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
        <!-- ::Key Risk Indicator End -->

        <!-- ::Kontrol Start -->
        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">6</span></span>
                        <span class="h3 mb-0">Kontrol</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row gy-3 gx-xxl-6 mb-3">
                        <div class="col-md-6 col-lg-5 col-xxl-6">
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Jenis Kontrol Eksisting</label>
                                <select name="jenis_kontrol_eksisting_id" class="form-select select2">
                                    <option value="" selected disabled>Jenis Kontrol Eksisting</option>
                                    @foreach ($jenisKontrolEksistings as $jenisKontrolEksisting)
                                        <option value="{{ $jenisKontrolEksisting->id }}" {{ $identifikasiRisiko->jenis_kontrol_eksisting_id == $jenisKontrolEksisting->id ? 'selected' : '' }}>
                                            {{ $jenisKontrolEksisting->jenis_kontrol }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Kontrol Eksisting</label>
                                <div class="w-100">
                                    <div id="kontrol-eksisting-body">
                                        @forelse($identifikasiRisiko->kontrolEksistings as $index => $kontrol)
                                            <div class="row g-2 mb-2">
                                                <div class="col">
                                                    <textarea class="form-control" name="kontrol_eksisting[]" rows="3" placeholder="Masukkan kontrol eksisting">{{ $kontrol->kontrol_eksisting }}</textarea>
                                                </div>
                                                <div class="col-auto d-flex align-items-center">
                                                    <button type="button" class="btn btn-icon-danger h-100" onclick="removeKontrolRow(event)" {{ count($identifikasiRisiko->kontrolEksistings) <= 1 ? 'disabled' : '' }}>
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="row g-2 mb-2">
                                                <div class="col">
                                                    <textarea class="form-control" name="kontrol_eksisting[]" rows="3" placeholder="Masukkan kontrol eksisting">{{ $identifikasiRisiko->kontrol_eksisting }}</textarea>
                                                </div>
                                                <div class="col-auto d-flex align-items-center">
                                                    <button type="button" class="btn btn-icon-danger h-100" onclick="removeKontrolRow(event)" disabled>
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforelse
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
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4">Penilaian Efektivitas
                                    Kontrol</label>
                                <select class="form-select select2" name="penilaian_efektifitas_kontrol">
                                    <option selected disabled>Penilaian Efektivitas Kontrol</option>
                                    @foreach ($penilaianEfektifitasKontrols as $efektivitasKontrol)
                                        <option value="{{ $efektivitasKontrol->id }}" {{ $identifikasiRisiko->penilaian_efektifitas_kontrol == $efektivitasKontrol->id ? 'selected' : '' }}>
                                            {{ $efektivitasKontrol->efektivitas_kontrol }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker2">Perkiraan Waktu Mulai Terpapar Risiko</label>
                                <input class="form-control datetimepicker" name="perkiraan_waktu_mulai_terpapar_risiko"
                                    id="timepicker2" type="text" placeholder="d/m/y"
                                    value="{{ $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai ? \Carbon\Carbon::parse($identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y') : '' }}" />
                            </div>
                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker3">Perkiraan Waktu Selesai Terpapar Risiko</label>
                                <input class="form-control datetimepicker" name="perkiraan_waktu_selesai_terpapar_risiko"
                                    id="timepicker3" type="text" placeholder="d/m/y"
                                    value="{{ $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir ? \Carbon\Carbon::parse($identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y') : '' }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ::Kontrol End -->

        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">5</span></span>
                        <span class="h3 mb-0">Pilih Risiko Divisi Terkait</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>Pilih risiko divisi yang terkait dengan risiko korporat ini:</p>
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalPilihRisikoDivisi">
                        <span class="bx bx-plus"></span> Pilih Risiko Divisi
                    </button>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="tabelRisikoDivisiTerpilih">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 15%">Divisi</th>
                                    <th style="width: 20%">Peristiwa Risiko</th>
                                    <th style="width: 25%">Penyebab Risiko</th>
                                    <th style="width: 10%" class="text-center">Level</th>
                                    <th style="width: 10%" class="text-center">Nilai</th>
                                    <th style="width: 10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="empty-row">
                                    <td colspan="6" class="text-center text-muted">Belum ada risiko divisi yang dipilih.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalPilihRisikoDivisi" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pilih Risiko Divisi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="divisi_filter" class="form-label">Filter Berdasarkan Divisi:</label>
                            <select id="divisi_filter" class="form-select select2" style="width: 100%;">
                                <option value="">Pilih Divisi</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="tabelRisikoDivisi">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center" width="5%">Pilih</th>
                                        <th width="15%">Divisi</th>
                                        <th width="20%">Peristiwa Risiko</th>
                                        <th width="30%">Penyebab Risiko</th>
                                        <th class="text-center" width="10%">Level</th>
                                        <th class="text-center" width="10%">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center">Pilih divisi terlebih dahulu.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="btnPilihRisiko">Pilih</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="nav-item-circle-parent"><span class="nav-item-circle">6</span></span>
                        <span class="h3 mb-0">Pilih Risiko Anak Perusahaan Terkait</span>
                    </div>
                </div>
                <div class="card-body">
                    <p>Pilih risiko anak perusahaan yang terkait dengan risiko korporat ini:</p>
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalPilihRisikoAp">
                        <span class="bx bx-plus"></span> Pilih Risiko Anak Perusahaan
                    </button>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="tabelRisikoApTerpilih">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 15%">Anak Perusahaan</th>
                                    <th style="width: 20%">Peristiwa Risiko</th>
                                    <th style="width: 25%">Penyebab Risiko</th>
                                    <th style="width: 10%" class="text-center">Level</th>
                                    <th style="width: 10%" class="text-center">Nilai</th>
                                    <th style="width: 10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="empty-row-ap">
                                    <td colspan="6" class="text-center text-muted">Belum ada risiko anak perusahaan yang dipilih.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalPilihRisikoAp" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pilih Risiko Anak Perusahaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="ap_filter" class="form-label">Filter Berdasarkan Anak Perusahaan:</label>
                            <select id="ap_filter" class="form-select select2" style="width: 100%;">
                                <option value="">Pilih Anak Perusahaan</option>
                                @foreach($apUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="tabelRisikoAp">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center" width="5%">Pilih</th>
                                        <th width="15%">Unit/AP</th>
                                        <th width="20%">Peristiwa Risiko</th>
                                        <th width="30%">Penyebab Risiko</th>
                                        <th class="text-center" width="10%">Level</th>
                                        <th class="text-center" width="10%">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="6" class="text-center">Pilih Anak Perusahaan terlebih dahulu.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="btnPilihRisikoAp">Pilih</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-5">
            <div class="row g-2">
                <div class="col-auto order-1">
                    <a href="{{ route('corporate-risk.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
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

    $(document).ready(function() {
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
        // Add Column Penyebab Risiko
        let row = 0;

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
                    <input type="hidden" name="penyebab_risiko_id[]" value="">
                    <input type="text" class="form-control input-penyebab-risiko" name="penyebab_risiko[]" placeholder="Masukkan Penyebab Risiko">
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

            // Enable all delete buttons when we have more than one row
            if ($('#penyebab-risiko-body .row').length > 1) {
                $('#penyebab-risiko-body .btn-icon-danger').prop('disabled', false);
            }
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
                }
            });
        });

        const preloadedData = @json($identifikasiRisiko);

        for (const key in preloadedData) {
            if (preloadedData.hasOwnProperty(key)) {
                const value = preloadedData[key];
                if (Array.isArray(value)) {
                    if (key === 'penyebab_risiko') {
                        value.forEach((penyebab, index) => {
                            if (index === 0) {
                                $('.input-penyebab-risiko').val(penyebab.penyebab_risiko).attr('name', `penyebab_risiko[${penyebab.id}]`);
                            } else {
                                $('#add-column').click();
                                $(`.input-penyebab-risiko`).last().val(penyebab.penyebab_risiko).attr('name', `penyebab_risiko[${penyebab.id}]`);
                            }
                        });
                    } else if (key === 'kris') {
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

        function getUnmaskedValue(selector) {
            let val = $(selector).inputmask('unmaskedvalue');
            return parseFloat(val) || 0;
        }

        function setMaskedValue(element, value) {
            $(element).val(value).trigger('input');
        }

        // Validasi hierarki: Limit < Appetite < Tolerance
        $('input[name="threshold_risk_limit"], input[name="threshold_risk_appetite"], input[name="threshold_risk_tolerance"]').on('change', function() {
            let limit = getUnmaskedValue('input[name="threshold_risk_limit"]');
            let appetite = getUnmaskedValue('input[name="threshold_risk_appetite"]');
            let tolerance = getUnmaskedValue('input[name="threshold_risk_tolerance"]');

            const targetName = $(this).attr('name');

            // Cek jika Appetite <= Limit
            if (targetName === 'threshold_risk_appetite' && appetite > 0 && appetite <= limit) {
                Swal.fire({
                    icon: 'error',
                    title: 'Batas Nilai Tidak Valid',
                    text: 'Nilai Risk Appetite harus lebih besar dari Risk Limit (Rp ' + limit.toLocaleString('id-ID') + ').',
                });
                // Set nilai minimal: Limit + 1
                setMaskedValue(this, limit + 1);
                return;
            }

            // Cek jika Tolerance <= Appetite
            if (targetName === 'threshold_risk_tolerance' && tolerance > 0 && tolerance <= appetite) {
                Swal.fire({
                    icon: 'error',
                    title: 'Batas Nilai Tidak Valid',
                    text: 'Nilai Risk Tolerance harus lebih besar dari Risk Appetite (Rp ' + appetite.toLocaleString('id-ID') + ').',
                });
                // Set nilai minimal: Appetite + 1
                setMaskedValue(this, appetite + 1);
                return;
            }

            // Cek jika Tolerance <= Limit (kasus jika Appetite nol)
            if (targetName === 'threshold_risk_tolerance' && appetite === 0 && tolerance > 0 && tolerance <= limit) {
                Swal.fire({
                    icon: 'error',
                    title: 'Batas Nilai Tidak Valid',
                    text: 'Nilai Risk Tolerance harus lebih besar dari Risk Limit (Rp ' + limit.toLocaleString('id-ID') + ').',
                });
                setMaskedValue(this, limit + 1);
                return;
            }

            // Cek jika Limit diubah menjadi lebih besar dari Appetite/Tolerance yang sudah ada
            if (targetName === 'threshold_risk_limit') {
                if (appetite > 0 && limit >= appetite) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Penyesuaian Nilai',
                        text: 'Risk Limit tidak boleh melampaui Appetite. Nilai Appetite dan Tolerance akan disesuaikan otomatis.',
                    });
                    setMaskedValue('input[name="threshold_risk_appetite"]', limit + 1);
                    setMaskedValue('input[name="threshold_risk_tolerance"]', limit + 2);
                } else if (tolerance > 0 && limit >= tolerance) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Penyesuaian Nilai',
                        text: 'Risk Limit tidak boleh melampaui Tolerance. Nilai Tolerance akan disesuaikan otomatis.',
                    });
                    setMaskedValue('input[name="threshold_risk_tolerance"]', limit + 1);
                }
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
<script>
$(document).ready(function() {
    let selectedRisks = [];

    @foreach($identifikasiRisiko->divisiRisks as $risk)
        @php
            $existingPenyebabs = $risk->penyebabRisiko->pluck('penyebab_risiko')->toArray();
            if(empty($existingPenyebabs)) $existingPenyebabs = ['-'];

            $existingNilai = optional($risk->riskAnalysis)->skala_risiko ?? '-';
            $existingLevel = optional($risk->riskAnalysis)->level_risiko ?? '-';
        @endphp

        selectedRisks.push({
            id: '{{ $risk->id }}',
            divisi: '{{ optional($risk->unit)->name ?? "-" }}',
            peristiwa: @json($risk->peristiwa_risiko),
            level: '{{ $existingLevel }}',
            nilai: '{{ $existingNilai }}',
            penyebab: @json($existingPenyebabs)
        });
    @endforeach

    updateSelectedRisksTable();

    $('#modalPilihRisikoDivisi').on('shown.bs.modal', function () {
        $('#divisi_filter').select2({
            dropdownParent: $('#modalPilihRisikoDivisi')
        });
    });

    $('#divisi_filter').on('change', function() {
        const unitId = $(this).val();
        const tbody = $('#tabelRisikoDivisi tbody');

        if (!unitId) {
            tbody.html('<tr><td colspan="6" class="text-center">Pilih divisi terlebih dahulu.</td></tr>');
            return;
        }

        tbody.html('<tr><td colspan="6" class="text-center">Memuat data...</td></tr>');

        $.ajax({
            url: `{{ route('corporate-risk.get-division-risks', ['unit' => ':unitId']) }}`.replace(':unitId', unitId),
            type: 'GET',
            success: function(response) {
                tbody.html(response.html);
                selectedRisks.forEach(function(risk) {
                    $(`#modalPilihRisikoDivisi #risk-${risk.id}`).prop('checked', true);
                });
            },
            error: function() {
                tbody.html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        });
    });

    $('#btnPilihRisiko').on('click', function() {
        const currentlyChecked = new Set();

        $('#tabelRisikoDivisi .pilih-risiko:checked').each(function() {
            const riskId = $(this).val();
            currentlyChecked.add(riskId);

            if (!selectedRisks.some(risk => risk.id === riskId)) {
                const rawPenyebab = $(this).data('penyebab');
                selectedRisks.push({
                    id: riskId,
                    divisi: $(this).data('divisi'),
                    peristiwa: $(this).data('peristiwa'),
                    level: $(this).data('level'),
                    nilai: $(this).data('nilai'),
                    penyebab: rawPenyebab
                });
            }
        });

        selectedRisks = selectedRisks.filter(risk => {
            const checkboxExistsInModal = $(`#tabelRisikoDivisi #risk-${risk.id}`).length > 0;
            if (checkboxExistsInModal) {
                return currentlyChecked.has(risk.id);
            }
            return true;
        });

        updateSelectedRisksTable();
        $('#modalPilihRisikoDivisi').modal('hide');
    });

    function updateSelectedRisksTable() {
        const tbody = $('#tabelRisikoDivisiTerpilih tbody');
        tbody.empty();

        if (selectedRisks.length === 0) {
            tbody.html('<tr id="empty-row"><td colspan="6" class="text-center text-muted">Belum ada risiko divisi yang dipilih.</td></tr>');
            return;
        }

        selectedRisks.forEach(function(risk, index) {
            const count = (risk.penyebab && risk.penyebab.length > 0) ? risk.penyebab.length : 1;
            const firstPenyebab = (risk.penyebab && risk.penyebab.length > 0) ? risk.penyebab[0] : '-';
            const detailUrl = `/risk-register-unit/${risk.id}/view`;

            let html = `
                <tr>
                    <td rowspan="${count}" class="bg-white">${risk.divisi}</td>
                    <td rowspan="${count}" class="bg-white">
                        <a href="${detailUrl}" target="_blank" class="text-primary fw-bold text-decoration-underline" title="Lihat Detail Risiko">
                            ${risk.peristiwa} <i class='bx bx-link-external small'></i>
                        </a>
                    </td>
                    <td>${firstPenyebab}</td>
                    <td rowspan="${count}" class="text-center bg-white">${risk.level}</td>
                    <td rowspan="${count}" class="text-center bg-white">${risk.nilai}</td>
                    <td rowspan="${count}" class="text-center bg-white">
                        <button type="button" class="btn btn-sm btn-outline-danger hapus-risiko" data-index="${index}">
                            <i class="bx bx-trash"></i>
                        </button>
                        <input type="hidden" name="divisi_risk_ids[]" value="${risk.id}">
                    </td>
                </tr>
            `;

            if (count > 1) {
                for (let i = 1; i < count; i++) {
                    html += `
                        <tr>
                            <td>${risk.penyebab[i]}</td>
                        </tr>
                    `;
                }
            }

            tbody.append(html);
        });
    }

    $(document).on('click', '.hapus-risiko', function() {
        const index = $(this).data('index');

        Swal.fire({
            title: 'Hapus?',
            text: "Hapus risiko ini dari daftar terpilih?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                const riskIdToRemove = selectedRisks[index].id;
                $(`#risk-${riskIdToRemove}`).prop('checked', false);

                selectedRisks.splice(index, 1);
                updateSelectedRisksTable();
            }
        });
    });

    let selectedRisksAp = [];

    @foreach($identifikasiRisiko->apRisks as $risk)
        @php
            $existingPenyebabsAp = $risk->penyebabRisiko->pluck('penyebab_risiko')->toArray();
            if(empty($existingPenyebabsAp)) $existingPenyebabsAp = ['-'];

            $existingNilaiAp = optional($risk->riskAnalysis)->skala_risiko ?? '-';
            $existingLevelAp = optional($risk->riskAnalysis)->level_risiko ?? '-';
        @endphp

        selectedRisksAp.push({
            id: '{{ $risk->id }}',
            unit_name: '{{ optional($risk->unit)->name ?? "-" }}',
            peristiwa: @json($risk->peristiwa_risiko),
            level: '{{ $existingLevelAp }}',
            nilai: '{{ $existingNilaiAp }}',
            penyebab: @json($existingPenyebabsAp)
        });
    @endforeach

    updateSelectedRisksApTable();

    $('#modalPilihRisikoAp').on('shown.bs.modal', function () {
        $('#ap_filter').select2({ dropdownParent: $('#modalPilihRisikoAp') });
    });

    $('#ap_filter').on('change', function() {
        const unitId = $(this).val();
        const tbody = $('#tabelRisikoAp tbody');

        if (!unitId) {
            tbody.html('<tr><td colspan="6" class="text-center">Pilih Anak Perusahaan terlebih dahulu.</td></tr>');
            return;
        }

        tbody.html('<tr><td colspan="6" class="text-center">Memuat data...</td></tr>');

        $.ajax({
            url: `{{ route('corporate-risk.get-ap-risks', ['unit' => ':unitId']) }}`.replace(':unitId', unitId),
            type: 'GET',
            success: function(response) {
                if(response.html.trim() === "") {
                    tbody.html('<tr><td colspan="6" class="text-center text-muted">Tidak ada data risiko main yang published pada unit ini.</td></tr>');
                } else {
                    tbody.html(response.html);
                    // Re-check checkbox AP
                    selectedRisksAp.forEach(function(risk) {
                        $(`#modalPilihRisikoAp #risk-${risk.id}`).prop('checked', true);
                    });
                }
            },
            error: function() {
                tbody.html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        });
    });

    $('#btnPilihRisikoAp').on('click', function() {
        const currentlyChecked = new Set();
        $('#tabelRisikoAp .pilih-risiko:checked').each(function() {
            const riskId = $(this).val();
            currentlyChecked.add(riskId);

            if (!selectedRisksAp.some(risk => risk.id === riskId)) {
                const rawPenyebab = $(this).data('penyebab');
                selectedRisksAp.push({
                    id: riskId,
                    unit_name: $(this).data('divisi'),
                    peristiwa: $(this).data('peristiwa'),
                    level: $(this).data('level'),
                    nilai: $(this).data('nilai'),
                    penyebab: rawPenyebab
                });
            }
        });

        selectedRisksAp = selectedRisksAp.filter(risk => {
            const checkboxExistsInModal = $(`#modalPilihRisikoAp #risk-${risk.id}`).length > 0;
            if (checkboxExistsInModal) {
                return currentlyChecked.has(risk.id);
            }
            return true;
        });

        updateSelectedRisksApTable();
        $('#modalPilihRisikoAp').modal('hide');
    });

    function updateSelectedRisksApTable() {
        const tbody = $('#tabelRisikoApTerpilih tbody');
        tbody.empty();

        if (selectedRisksAp.length === 0) {
            tbody.html('<tr id="empty-row-ap"><td colspan="6" class="text-center text-muted">Belum ada risiko anak perusahaan yang dipilih.</td></tr>');
            return;
        }

        selectedRisksAp.forEach(function(risk, index) {
            const count = (risk.penyebab && risk.penyebab.length > 0) ? risk.penyebab.length : 1;
            const firstPenyebab = (risk.penyebab && risk.penyebab.length > 0) ? risk.penyebab[0] : '-';
            const detailUrl = `/risk-register-ap/${risk.id}/view`;

            let html = `
                <tr>
                    <td rowspan="${count}" class="bg-white">${risk.unit_name}</td>
                    <td rowspan="${count}" class="bg-white">
                        <a href="${detailUrl}" target="_blank" class="text-primary fw-bold text-decoration-underline" title="Lihat Detail">
                            ${risk.peristiwa} <i class='bx bx-link-external small'></i>
                        </a>
                    </td>
                    <td>${firstPenyebab}</td>
                    <td rowspan="${count}" class="text-center bg-white">${risk.level}</td>
                    <td rowspan="${count}" class="text-center bg-white">${risk.nilai}</td>
                    <td rowspan="${count}" class="text-center bg-white">
                        <button type="button" class="btn btn-sm btn-outline-danger hapus-risiko-ap" data-index="${index}">
                            <i class="bx bx-trash"></i>
                        </button>
                        <input type="hidden" name="ap_risk_ids[]" value="${risk.id}">
                    </td>
                </tr>
            `;

            if (count > 1) {
                for (let i = 1; i < count; i++) {
                    html += `<tr><td>${risk.penyebab[i]}</td></tr>`;
                }
            }
            tbody.append(html);
        });
    }

    $(document).on('click', '.hapus-risiko-ap', function() {
        const index = $(this).data('index');
        Swal.fire({
            title: 'Hapus?',
            text: "Hapus risiko ini dari daftar terpilih?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                selectedRisksAp.splice(index, 1);
                updateSelectedRisksApTable();
            }
        });
    });
});
</script>
@endpush
