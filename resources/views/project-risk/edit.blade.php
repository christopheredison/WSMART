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
                                @php
                                    $selectedSasaranId = old('sasaran_proyek_id', $projectRisk->sasaran_proyek_id);
                                    $targetCapaianKinerja = old('target_capaian_kinerja', $projectRisk->target_capaian_kinerja);
                                    $hasLegacySasaran = false;
                                    $legacySasaranText = null;

                                    if ($selectedSasaranId && $selectedSasaranId !== 'legacy') {
                                        // Sasaran sudah terhubung ke master data
                                    } elseif (!empty($targetCapaianKinerja)) {
                                        $matchFound = false;
                                        foreach ($sasaranProyeks as $sasaranProyek) {
                                            if (trim($sasaranProyek->kpi_desc) == trim($targetCapaianKinerja)) {
                                                $selectedSasaranId = $sasaranProyek->id;
                                                $matchFound = true;
                                                break;
                                            }
                                        }

                                        if (!$matchFound) {
                                            $hasLegacySasaran = true;
                                            $legacySasaranText = $targetCapaianKinerja;
                                            $selectedSasaranId = 'legacy';
                                        }
                                    }
                                @endphp
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Sasaran Risiko</label>
                                <div class="w-100">
                                    <select class="form-select select2" id="sasaran_proyek_id" name="sasaran_proyek_id">
                                        <option value="">Pilih Sasaran Risiko</option>
                                        @if($hasLegacySasaran)
                                            <option value="legacy" data-kpi="{{ $legacySasaranText }}" {{ $selectedSasaranId == 'legacy' ? 'selected' : '' }}>
                                                {{ $legacySasaranText }} (Sasaran Saat Ini)
                                            </option>
                                        @endif
                                        @foreach($sasaranProyeks as $sasaranProyek)
                                            <option value="{{ $sasaranProyek->id }}" data-kpi="{{ $sasaranProyek->kpi_desc }}" {{ $selectedSasaranId == $sasaranProyek->id ? 'selected' : '' }}>
                                                {{ $sasaranProyek->kpi_desc }}
                                            </option>
                                        @endforeach
                                        <option value="other" {{ old('sasaran_proyek_id') === 'other' ? 'selected' : '' }}>Ajukan Sasaran Lainnya</option>
                                    </select>
                                    @if($hasLegacySasaran)
                                        <div class="alert alert-warning mt-2 mb-2" id="sasaran-legacy-info">
                                            Sasaran ini berasal dari data lama. Anda dapat mempertahankannya atau memilih sasaran yang sudah disetujui Divisi Manajemen Risiko.
                                        </div>
                                    @endif
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
                                @php
                                    $selectedPeristiwaId = old('peristiwa_risiko_id', $projectRisk->peristiwa_risiko_id);
                                    $manualPeristiwa = old('rencana_kegiatan', $projectRisk->rencana_kegiatan);
                                    $hasLegacyPeristiwa = false;
                                    $isOtherPeristiwa = old('peristiwa_risiko_id') === 'other';

                                    if (!$isOtherPeristiwa && (empty($selectedPeristiwaId) || $selectedPeristiwaId == 0) && !empty($manualPeristiwa)) {
                                        $hasLegacyPeristiwa = true;
                                        $selectedPeristiwaId = 'legacy';
                                    }
                                @endphp
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">Peristiwa Risiko</label>
                                <div class="w-100">
                                    <select class="form-select select2 js-select-hide-search" name="peristiwa_risiko_id" id="peristiwa_risiko" required>
                                        @if($hasLegacyPeristiwa)
                                            <option value="legacy" {{ $selectedPeristiwaId == 'legacy' ? 'selected' : '' }}>
                                                {{ $manualPeristiwa }} (Peristiwa Saat Ini)
                                            </option>
                                        @endif
                                        @foreach ($peristiwaRisikos as $peristiwaRisiko)
                                            <option value="{{ $peristiwaRisiko->id }}"
                                                {{ $selectedPeristiwaId == $peristiwaRisiko->id ? 'selected' : '' }}>
                                                {{ $peristiwaRisiko->title }}
                                            </option>
                                        @endforeach
                                        <option value="other" {{ $isOtherPeristiwa ? 'selected' : '' }}>Ajukan Peristiwa Lainnya</option>
                                    </select>
                                    @if($hasLegacyPeristiwa)
                                        <div class="alert alert-warning mt-2 mb-2" id="peristiwa-legacy-info">
                                            Peristiwa ini berasal dari data lama. Anda dapat mempertahankannya atau memilih peristiwa yang sudah disetujui Divisi Manajemen Risiko.
                                        </div>
                                    @endif
                                    <div class="alert alert-info mt-2 d-none mb-2" id="peristiwa-other-guide">
                                        Peristiwa ini perlu persetujuan Divisi Manajemen Risiko sebelum bisa digunakan.
                                    </div>
                                    <div class="d-none" id="peristiwa-other-box">
                                        <textarea class="form-control"
                                            id="peristiwa_risiko_lainnya"
                                            name="rencana_kegiatan"
                                            rows="3"
                                            placeholder="Masukkan usulan peristiwa risiko lainnya">{{ $isOtherPeristiwa ? $manualPeristiwa : '' }}</textarea>
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
                                <input type="hidden" name="kategori_risiko_id" value="{{ old('kategori_risiko_id', $projectRisk->kategori_risiko_id) }}" id="kategori_risiko_id">
                                <div class="w-100">
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
                        </div>
                        {{-- <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="wbs" name="wbs" rows="3"
                                    placeholder="WBS" required>{{ old('wbs', $projectRisk->wbs) }}</textarea>
                                <label for="wbs">WBS</label>
                            </div>
                        </div> --}}
                        <div class="col-12">
                            <div class="form-group d-lg-flex">
                                <label class="form-label label-lg-start col-lg-4 col-xxl-3 me-lg-2">WBS</label>
                                <div class="w-100">
                                    <select class="form-select select2" name="wbs_id" id="wbs_id" required>
                                        <option value="" disabled>Pilih WBS</option>
                                        @foreach ($wbs_data as $w)
                                            <option value="{{ $w->id }}"
                                                {{ old('wbs_id', $projectRisk->wbs_id) == $w->id ? 'selected' : '' }}>
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
                                    value="{{ old('deskripsi_dampak', $projectRisk->deskripsi_dampak) }}" placeholder="Deskripsi Dampak" required></textarea>
                                <label for="deskripsi_dampak">Deskripsi Dampak Risiko</label>
                            </div>
                        </div> --}}
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <textarea class="form-control" id="deskripsi_peristiwa_risiko" name="deskripsi_peristiwa_risiko" rows="3"
                                    placeholder="Deskripsi Peristiwa Risiko" required>{{ old('deskripsi_peristiwa_risiko', $projectRisk->deskripsi_peristiwa_risiko) }}</textarea>
                                <label for="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ::DataRisiko End -->

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
                        <div class="row g-2 mb-3 dampak-row-item" data-item-label="dampak risiko">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="hidden" name="penyebab_dampak_id[]">
                                    <textarea class="form-control input-dampak-risiko" name="dampak_risiko[]" placeholder="Masukkan Dampak Risiko" required></textarea>
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
                        <div class="row g-2 penyebab-row-item" data-item-label="penyebab risiko">
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
                        <div class="row g-2 kri-row-item" data-item-label="parameter / KRI">
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
                                <div class="w-100">
                                    <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko_mulai"
                                      id="timepicker2" type="text" placeholder="d/m/y"
                                      value="{{ old('perkiraan_waktu_terpapar_risiko_mulai') }}" />
                                </div>
                            </div>

                            <div class="form-group d-lg-flex mb-4">
                                <label class="form-label label-lg-start col-lg-5 col-xl-4" for="timepicker3">Perkiraan Waktu Selesai Terpapar Risiko</label>
                                <div class="w-100">
                                    <input class="form-control datetimepicker" name="perkiraan_waktu_terpapar_risiko_akhir"
                                        id="timepicker3" type="text" placeholder="d/m/y"
                                        value="{{ old('perkiraan_waktu_terpapar_risiko_akhir') }}" />
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
                @if(!$projectRisk->id)
                <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="draft" class="btn btn-warning bg-warning ms-auto btn-action">Save as Draft</button>
                </div>
                @endif
                <div class="col-auto order-3 px-0 px-md-1 d-flex">
                    <button type="button" data-action="save" class="btn btn-warning bg-warning ms-auto btn-action">Simpan dan Keluar</button>
                </div>
                @if($projectRisk->request_edit != 2)
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
    async function confirmMonitoredRowDeletion(row) {
        if (String(row.data('has-monitoring')) !== '1') {
            return true;
        }

        const itemLabel = row.data('item-label') || 'data';
        const result = await Swal.fire({
            title: 'Data Sudah Dimonitoring',
            text: `Data ${itemLabel} ini sudah memiliki history monitoring. Tetap hapus dari risiko aktif? History monitoring akan tetap tersimpan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        });

        return result.isConfirmed;
    }

    async function removeRow(event) {
        const row = $(event.target).closest('.row');
        if (!await confirmMonitoredRowDeletion(row)) {
            return;
        }

        row.remove();
    }

    let newRiskDetailKey = -1;

    function nextRiskDetailKey() {
        return newRiskDetailKey--;
    }

    $(document).ready(function() {
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

        $('#peristiwa_risiko').on('change', function() {
            const selectedValue = $(this).val();
            const otherTextarea = $('#peristiwa_risiko_lainnya');
            const otherGuide = $('#peristiwa-other-guide');
            const otherBox = $('#peristiwa-other-box');
            const refreshBtn = $('#btn-refresh-peristiwa');
            const legacyInfo = $('#peristiwa-legacy-info');

            if (selectedValue === 'other') {
                legacyInfo.addClass('d-none');
                otherGuide.removeClass('d-none');
                otherBox.removeClass('d-none');
                otherTextarea.removeClass('d-none').attr('required', false);
            } else if (selectedValue === 'legacy') {
                legacyInfo.removeClass('d-none');
                otherGuide.addClass('d-none');
                otherBox.addClass('d-none');
                otherTextarea.addClass('d-none').attr('required', false);
                refreshBtn.addClass('d-none');
            } else {
                legacyInfo.addClass('d-none');
                otherGuide.addClass('d-none');
                otherBox.addClass('d-none');
                otherTextarea.addClass('d-none').attr('required', false);
                refreshBtn.addClass('d-none');
                if (selectedValue !== "") {
                    otherTextarea.val('');
                }
            }
        });

        if ($('#peristiwa_risiko').val() === 'other') {
            $('#peristiwa-other-guide, #peristiwa-other-box').removeClass('d-none');
        }

        const submitSasaranUrl = @json(route('projects.sasaran-lainnya.submit', ['project' => $projectPeriodeList->id]));
        const submitPeristiwaUrl = @json(route('projects.peristiwa-lainnya.submit', ['project' => $projectPeriodeList->id]));

        $('#sasaran_proyek_id').on('change', function() {
            const selectedValue = $(this).val();
            const targetTextarea = $('#target_capaian_kinerja');
            const kpiDescSelected = $('#kpi_desc_selected');
            const otherGuide = $('#sasaran-other-guide');
            const otherBox = $('#sasaran-other-box');
            const refreshBtn = $('#btn-refresh-sasaran');
            const legacyInfo = $('#sasaran-legacy-info');

            if (selectedValue === 'other') {
                legacyInfo.addClass('d-none');
                otherGuide.removeClass('d-none');
                otherBox.removeClass('d-none');
                targetTextarea.removeClass('d-none').attr('required', false).val('');
                kpiDescSelected.val('');
            } else if (selectedValue === 'legacy') {
                legacyInfo.removeClass('d-none');
                otherGuide.addClass('d-none');
                otherBox.addClass('d-none');
                targetTextarea.addClass('d-none').attr('required', false).val('');
                refreshBtn.addClass('d-none');
                kpiDescSelected.val($(this).find('option:selected').data('kpi') || '');
            } else if (selectedValue) {
                legacyInfo.addClass('d-none');
                const kpiDesc = $(this).find('option:selected').data('kpi');
                otherGuide.addClass('d-none');
                otherBox.addClass('d-none');
                targetTextarea.addClass('d-none').attr('required', false).val('');
                refreshBtn.addClass('d-none');
                kpiDescSelected.val(kpiDesc);
            } else {
                legacyInfo.addClass('d-none');
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

        $('#add-dampak').click(function() {
            const itemKey = nextRiskDetailKey();
            let html = `
            <div class="row g-2 mb-3 dampak-row-item" data-item-label="dampak risiko">
                <div class="col">
                    <div class="form-floating">
                        <input type="hidden" name="penyebab_dampak_id[]">
                        <textarea class="form-control input-dampak-risiko" name="dampak_risiko[${itemKey}]" placeholder="Masukkan Dampak Risiko" required></textarea>
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

        // Add Column Penyebab Risiko
        let row = 0;
        $('#add-column').click(function() {
            row++;
            const itemKey = nextRiskDetailKey();
            let html = `
            <div class="row g-2 penyebab-row-item" data-item-label="penyebab risiko">
                <div class="col">
                <div class="form-floating">
                    <input type="hidden" name="penyebab_risiko_id[]" value="">
                    <input type="text" class="form-control input-penyebab-risiko" name="penyebab_risiko[${itemKey}]" placeholder="Masukkan Penyebab Risiko">
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
            const itemKey = nextRiskDetailKey();
            let html = `
                <div class="row g-2 kri-row-item" data-item-label="parameter / KRI">
                    <div class="col-12 col-lg-11">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="form-group form-floating">
                                    <input type="text" class="form-control" name="key_risk_indicator[${itemKey}]">
                                    <label for="key_risk_indicator_1">Key Risk Indicator</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating">
                                    <input type="text" class="form-control" name="satuan_kri[${itemKey}]">
                                    <label for="satuan_kri_1">Satuan KRI</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating text-center">
                                    <input type="text" class="form-control border-success" name="batas_aman[${itemKey}]">
                                    <label for="batas_aman_1">Batas Aman</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating text-center">
                                    <input type="text" class="form-control border-warning" name="batas_waspada[${itemKey}]">
                                    <label for="batas_waspada_1">Batas Siaga</label>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-auto flex-lg-grow-1">
                                <div class="form-group form-floating text-center">
                                    <input type="text" class="form-control border-danger" name="batas_bahaya[${itemKey}]">
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
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            //maxDate: endOfYear,
            defaultDate: "{{ $projectRisk->perkiraan_waktu_terpapar_risiko_mulai ? $projectRisk->perkiraan_waktu_terpapar_risiko_mulai->format('d/m/Y') : '' }}",
            disableMobile: true
        });

        var flatpickrIns2 = flatpickr("#timepicker3", {
            //mode: "range",
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "d/m/Y",
            //maxDate: endOfYear,
            defaultDate: "{{ $projectRisk->perkiraan_waktu_terpapar_risiko_akhir ? $projectRisk->perkiraan_waktu_terpapar_risiko_akhir->format('d/m/Y') : '' }}",
            disableMobile: true
        });

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

            const data = new FormData(form[0]);
            data.append('action', action);

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('.select2-selection').removeClass('border-danger');

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Pastikan data yang diubah sudah sesuai.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {

                    // Show Loading
                    // Swal.fire({
                    //     title: 'Menyimpan...',
                    //     allowOutsideClick: false,
                    //     didOpen: () => {
                    //         Swal.showLoading();
                    //     }
                    // });

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: data,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            Swal.close();
                            if (response.redirect) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message || 'Data berhasil diperbarui',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.href = response.redirect;
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.close();

                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                let firstErrorElement = null;

                                // --- AMBIL 1 PESAN ERROR PERTAMA UNTUK SWEETALERT ---
                                // Kita ambil key pertama, lalu ambil pesan pertamanya
                                const firstKey = Object.keys(errors)[0];
                                const specificErrorMessage = errors[firstKey][0];

                                // --- LOOPING UNTUK MEMBERI WARNA MERAH ---
                                $.each(errors, function(key, messages) {
                                    let message = messages[0];
                                    let inputElement = null;

                                    // Cek apakah error dari Array (dampak_risiko.0, penyebab_risiko.15, dll)
                                    if (key.includes('.')) {
                                        let parts = key.split('.');
                                        let fieldName = parts[0];
                                        let keyIndex = parts[1]; // Bisa berupa ID (125) atau Index Array (0, 1)

                                        // PRIORITAS 1: Cari berdasarkan ID Database (Format: name="field[ID]")
                                        // Ini untuk data yang sudah ada sebelumnya (edit)
                                        let selectorById = `[name="${fieldName}[${keyIndex}]"]`;
                                        inputElement = $(selectorById);

                                        // PRIORITAS 2: Cari berdasarkan Index Array (Format: name="field[]")
                                        // Ini untuk data BARU yang ditambah via tombol (+)
                                        if (inputElement.length === 0) {
                                            // Kita ambil semua elemen yang namanya "field[]"
                                            // Lalu ambil berdasarkan urutan (eq)
                                            inputElement = $(`[name="${fieldName}[]"]:eq(${keyIndex})`);

                                            // Fallback Ekstra: Jika Laravel mengembalikan index angka (misal .0)
                                            // tapi di HTML cuma ada satu input baru, ambil yang terakhir
                                            if(inputElement.length === 0) {
                                                inputElement = $(`[name="${fieldName}[]"]`).last();
                                            }
                                        }

                                    } else {
                                        // Input Biasa (Bukan Array)
                                        inputElement = $(`[name="${key}"]`);
                                        if (inputElement.length === 0) inputElement = $(`#${key}`);
                                    }

                                    // --- EKSEKUSI TAMPILAN ERROR ---
                                    if (inputElement && inputElement.length > 0) {

                                        // A. Handle Select2
                                        if (inputElement.hasClass('select2-hidden-accessible')) {
                                            inputElement.next('.select2-container').find('.select2-selection').addClass('border-danger');
                                            // Tambah pesan error di bawahnya
                                            if(inputElement.next('.select2-container').next('.invalid-feedback').length === 0){
                                                inputElement.next('.select2-container').after(`<div class="invalid-feedback d-block text-danger mt-1"><small>${message}</small></div>`);
                                            }
                                        }
                                        // B. Handle Input Biasa / Textarea
                                        else {
                                            inputElement.addClass('is-invalid');

                                            // Cek agar tidak double pesan error
                                            if(inputElement.next('.invalid-feedback').length === 0 && !inputElement.parent().next('.invalid-feedback').length) {
                                                if(inputElement.parent('.input-group').length) {
                                                    inputElement.parent().after(`<div class="invalid-feedback d-block">${message}</div>`);
                                                } else {
                                                    inputElement.after(`<div class="invalid-feedback d-block">${message}</div>`);
                                                }
                                            }
                                        }

                                        // Set elemen pertama untuk fokus scroll
                                        if (!firstErrorElement) {
                                            firstErrorElement = inputElement;
                                        }
                                    }
                                });

                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Data belum lengkap',
                                    html: 'Mohon periksa kembali form inputan.',
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
                                // Error Server Lain
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

            // $.ajax({
            //     url: url,
            //     type: 'POST',
            //     data: data,
            //     contentType: false,
            //     processData: false,
            //     success: function(response) {
            //         if (response.redirect) {
            //             Swal.fire({
            //                 icon: 'success',
            //                 title: 'Berhasil',
            //                 text: response.message || 'Data berhasil disimpan',
            //                 showConfirmButton: false,
            //                 timer: 1500
            //             }).then(() => {
            //                 window.location.href = response.redirect;
            //             });
            //         } else {
            //             Swal.fire({
            //                 icon: 'success',
            //                 title: 'Berhasil',
            //                 text: response.message || 'Data berhasil disimpan',
            //                 showConfirmButton: false,
            //                 timer: 1500
            //             });
            //         }
            //     },
            //     error: function(xhr) {
            //         const errors = xhr.responseJSON.errors;
            //         if (errors) {
            //             let message = '<ul>';
            //             for (const key in errors) {
            //                 message += `<li>${errors[key]}</li>`;
            //             }
            //             message += '</ul>';
            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Gagal',
            //                 html: message
            //             });
            //         } else {
            //             Swal.fire({
            //                 icon: 'error',
            //                 title: 'Gagal',
            //                 text: xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan data'
            //             });
            //         }
            //     }
            // });
        });

        const preloadedData = @json($projectRisk);

        for (const key in preloadedData) {
            if (preloadedData.hasOwnProperty(key)) {
                const value = preloadedData[key];
                if (key === 'perkiraan_waktu_terpapar_risiko') {
                    //flatpickrIns.setDate(value.split(' to '));
                }
                else if (key === 'perkiraan_waktu_terpapar_risiko_mulai') {
                    // flatpickrIns1.setDate(value);
                }
                else if (key === 'perkiraan_waktu_terpapar_risiko_akhir') {
                    // flatpickrIns2.setDate(value);
                }
                else if (key === 'peristiwa_risiko_id' || key === 'sasaran_proyek_id' || key === 'target_capaian_kinerja') {
                    continue;
                }
                else if (Array.isArray(value)) {
                    if (key === 'penyebab_risiko_projects') {
                        value.forEach((penyebab, index) => {
                            let currentRow;
                            if (index === 0) {
                                $('.input-penyebab-risiko').val(penyebab.penyebab_risiko).attr('name', `penyebab_risiko[${penyebab.id}]`);
                                currentRow = $('#penyebab-risiko-body > .penyebab-row-item').first();
                            } else {
                                $('#add-column').click();
                                $(`.input-penyebab-risiko`).last().val(penyebab.penyebab_risiko).attr('name', `penyebab_risiko[${penyebab.id}]`);
                                currentRow = $('#penyebab-risiko-body > .penyebab-row-item').last();
                            }
                            currentRow.data('has-monitoring', penyebab.monitorings_count > 0 ? '1' : '0');
                        });
                    } else if (key === 'dampak_risiko_projects') {
                        value.forEach((dampak, index) => {
                            let currentRow;
                            if (index === 0) {
                                $('.input-dampak-risiko').val(dampak.dampak_risiko).attr('name', `dampak_risiko[${dampak.id}]`);
                                currentRow = $('#dampak-risiko-body > .dampak-row-item').first();
                            } else {
                                $('#add-dampak').click();
                                $(`.input-dampak-risiko`).last().val(dampak.dampak_risiko).attr('name', `dampak_risiko[${dampak.id}]`);
                                currentRow = $('#dampak-risiko-body > .dampak-row-item').last();
                            }
                            currentRow.data('has-monitoring', dampak.monitorings_count > 0 ? '1' : '0');
                        });
                    } else if (key === 'kri_projects') {
                        value.forEach((kri, index) => {
                            let currentRow;
                            if (index === 0) {
                                currentRow = $('#kri-body > .kri-row-item').first();
                            } else {
                                $('#add-column-kri').click();
                                currentRow = $('#kri-body > .kri-row-item').last();
                            }

                            currentRow.find('[name^="key_risk_indicator["]')
                                .attr('name', `key_risk_indicator[${kri.id}]`)
                                .val(kri.kri);
                            currentRow.find('[name^="satuan_kri["]')
                                .attr('name', `satuan_kri[${kri.id}]`)
                                .val(kri.satuan_kri);
                            currentRow.find('[name^="batas_aman["]')
                                .attr('name', `batas_aman[${kri.id}]`)
                                .val(kri.batas_aman);
                            currentRow.find('[name^="batas_waspada["]')
                                .attr('name', `batas_waspada[${kri.id}]`)
                                .val(kri.batas_waspada);
                            currentRow.find('[name^="batas_bahaya["]')
                                .attr('name', `batas_bahaya[${kri.id}]`)
                                .val(kri.batas_bahaya);
                            currentRow.data('has-monitoring', kri.kri_project_monitorings_count > 0 ? '1' : '0');
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
                    } else if (key === 'parameter_risiko_projects') {
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

        $('#peristiwa_risiko').trigger('change');
        $('#sasaran_proyek_id').trigger('change');

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
