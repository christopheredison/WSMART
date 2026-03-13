@extends('layouts.default')
@section('dashboard')
    <style>
        .select-risk {
            display: block; /* Biar seluruh text bisa diklik */
            text-decoration: none; /* Hilangkan underline */
            color: #333; /* Warna teks */
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
            max-width: 200px; /* Bisa disesuaikan */
        }

        .select-risk:hover {
            text-decoration: underline; /* Efek hover */
            color: #007bff; /* Warna hover */
        }

        .select-risk-res {
            display: block; /* Biar seluruh text bisa diklik */
            text-decoration: none; /* Hilangkan underline */
            color: #333; /* Warna teks */
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
            max-width: 200px; /* Bisa disesuaikan */
        }

        .select-risk-res:hover {
            text-decoration: underline; /* Efek hover */
            color: #007bff; /* Warna hover */
        }
    </style>
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Analisa Risiko</h3>
        </div>
    </div>

    <form id="mainForm">
        @csrf
        <div class="card mb-5">
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
                    <div class="col-12">
                        <h3>Deskripsi Peristiwa Risiko</h3>
                        <p>{{ $projectRisk->deskripsi_peristiwa_risiko ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h3>Dampak Risiko</h3>
                        <ul>
                            @forelse ($projectRisk->dampakRisikoProjects as $dampak)
                                <li>{{ $dampak->dampak_risiko }}</li>
                            @empty
                                <li class="text-muted italic">Tidak ada dampak risiko</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h3>Penyebab Risiko</h3>
                        <ul>
                            @foreach ($projectRisk->penyebabRisikoProjects as $penyebab)
                                <li>{{ $penyebab->penyebab_risiko }}</li>
                            @endforeach
                        </ul>
                        @if ($projectRisk->penyebabRisikoProjects->isEmpty())
                            <em class="text-muted">Tidak ada penyebab risiko</em>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">2</span>
                    </span>
                    <span class="h3 mb-0">Dampak Risiko</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Kategori Dampak</label>
                        {{ Form::select('kategori_dampak',
                            [
                                \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF => 'Kuantitatif'
                            ],
                            $analisa->kategori_dampak ?? \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF,
                            [
                                'id' => 'kategoriDampak',
                                // 'placeholder' => 'Pilih kategori Dampak',
                                'class' => 'form-select',
                                'required' => true
                            ]
                        ) }}
                    </div>
                    <div class="col-md-4" id="divAreaDampak">
                        <div class="d-flex align-items-center">
                            <label for="area_dampak" class="me-2 mb-0">Area Dampak</label>
                            <button type="button" class="btn btn-link p-0" id="btnShowKualitatif" title="Lihat Risiko Kualitatif">
                                <i class='bx bx-show bx-sm'></i> <!-- Boxicons Eye Icon -->
                            </button>
                        </div>
                        {{ Form::select('area_dampak', \App\Models\AreaDampak::project()->get()->pluck('title', 'id'),  $analisa->area_dampak, ['placeholder' => 'Pilih area Dampak', 'class' => 'form-select', 'required' => true, 'id' => 'area_dampak']) }}
                        <input type="hidden" name="skala_dampak_hidden" id="skala_dampak_hidden" value="{{ $analisa->skala_dampak }}">
                    </div>
                    <div class="col-md-4" id="div_risk_limit">
                        <label>Risk Limit</label>
                        {{ Form::text('_risk_limit', $risk_limit, ['class' => 'form-control inputmask-rupiah', 'readonly' => true, 'required' => true, 'id' => 'risk_limit', 'autocomplete' => 'off']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">3</span>
                    </span>
                    <span class="h3 mb-0">Pengukuran Risiko Inheren</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Nilai Dampak</label>
                        {{ Form::text('nilai_dampak', $analisa->nilai_dampak, ['class' => 'form-control inputmask-rupiah', 'required' => true, 'id' => 'nilai_dampak', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                          <label for="skala_parameter_type" class="">
                              <span>Parameter Probabilitas</span>
                          </label>
                          <button type="button" class="btn btn-link p-0 ms-2" id="btnShowSkalaInfo" data-bs-toggle="modal" data-bs-target="#modalSkalaInfo" title="Lihat Panduan Parameter">
                              <i class='bx bx-show bx-sm'></i>
                          </button>
                        </div>
                        <select name="skala_parameter_type" id="skala_parameter_type" class="form-select" required>
                            {{-- <option value="">Pilih Parameter...</option> --}}
                            @foreach($parameterTypes as $type)
                                <option value="{{ $type }}" {{ ($selectedParameterType ?? null) == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Eksposur Risiko</label>
                        {{ Form::text('eksposur_risiko', '', ['class' => 'form-control inputmask-rupiah', 'disabled' => true, 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Skala Dampak</label>
                        {{ Form::select('skala_dampak', \App\Models\SkalaDampak::get()->mapWithKeys(function($item) { return [$item->tingkat => $item->tingkat . ' - ' . $item->deskripsi]; }), $analisa->skala_dampak, [
                            'class' => 'form-select',
                            'placeholder' => 'Pilih Skala Dampak',
                            'required' => true,
                            'id' => 'skala_dampak'
                          ]
                        ) }}
                    </div>
                    <div class="col-md-4 d-none">
                        <label>Skala Probabilitas</label>
                        {{ Form::text('skala_probabilitas', '', ['class' => 'form-control', 'disabled' => false, 'required' => true]) }}
                    </div>
                    <div class="col-md-3">
                        <label for="skala_parameter_id">Skala Probabilitas</label>
                        <select name="skala_parameter_id" id="skala_parameter_id" class="form-select" required>
                            <option value="">Pilih Skala...</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Nilai Probabilitas (%)</label>
                        {{ Form::number('nilai_probabilitas', $analisa->nilai_probabilitas, ['class' => 'form-control', 'required' => true, 'step' => '0.01', 'min' => 0, 'max' => 100, 'disabled' => false, 'autocomplete' => 'off']) }}
                    </div>
                    <div class="col-md-2">
                        <label>Skala Risiko</label>
                        {{ Form::text('skala_risiko', '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                    <div class="col-md-2">
                        <label>Level Risiko</label>
                        {{ Form::text('level_risiko', '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                </div>
                <div class="row" id="divDeskripsiDampak">
                    <div class="col-md-12">
                        <label id="labelDeskripsiDampak">Deskripsi Dampak</label>
                        {{ Form::textarea('deskripsi_dampak', $analisa->deskripsi_dampak, ['class' => 'form-control', 'rows' => 5]) }}
                        <input type="hidden" name="risk_tolerance" id="risk_tolerance" value="{{ $risk_tolerance }}" />
                    </div>
                </div>
                <div class="row" id="divAsumsiDampak">
                    <div class="col-md-12">
                        <label>Asumsi Perhitungan Dampak & Probabilitas Inherent</label>
                        {{ Form::textarea('asumsi_perhitungan_dampak', $analisa->asumsi_perhitungan_dampak, ['class' => 'form-control', 'rows' => 5]) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">4</span>
                    </span>
                    <span class="h3 mb-0">Pengukuran Risiko Residual</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3 gx-3">
                    <div class="col-md-4">
                        <label>Nilai Dampak</label>
                        {{ Form::text('nilai_dampak_residual', $analisa->nilai_dampak_residual, ['class' => 'form-control inputmask-rupiah', 'required' => true, 'id' => 'nilai_dampak_residual', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="col-md-4">
                        <label for="skala_parameter_type_residual">Parameter Probabilitas</label>
                        <select name="skala_parameter_type_residual" id="skala_parameter_type_residual" class="form-select" required disabled>
                            {{-- <option value="">Pilih Parameter...</option> --}}
                            @foreach($parameterTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Eksposur Risiko</label>
                        {{ Form::text('eksposur_risiko_residual', '', ['class' => 'form-control inputmask-rupiah', 'disabled' => true, 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="row mb-3 gx-3">
                    <div class="col-md-3">
                        <label>Skala Dampak Residual</label>
                        {{ Form::select('skala_dampak_residual', \App\Models\SkalaDampak::get()->mapWithKeys(function($item) { return [$item->tingkat => $item->tingkat . ' - ' . $item->deskripsi]; }), $analisa->skala_dampak_residual, ['class' => 'form-select', 'placeholder' => 'Pilih Skala Dampak Residual', 'required' => true, 'id' => 'skala_dampak_residual']) }}
                    </div>
                    <div class="col-md-4 d-none">
                        <label>Skala Probabilitas</label>
                        {{ Form::text('skala_probabilitas_residual', '', ['class' => 'form-control', 'disabled' => false, 'required' => true]) }}
                    </div>
                    <div class="col-md-3">
                        <label for="skala_parameter_residual_id">Skala Probabilitas</label>
                        <select name="skala_parameter_residual_id" id="skala_parameter_residual_id" class="form-select" required>
                            <option value="">Pilih Skala...</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Nilai Probabilitas (%)</label>
                        {{ Form::number('nilai_probabilitas_residual', $analisa->nilai_probabilitas_residual, ['class' => 'form-control', 'required' => true, 'step' => '0.01', 'min' => 0, 'max' => 100, 'disabled' => false, 'autocomplete' => 'off']) }}
                    </div>
                    <div class="col-md-2">
                        <label>Skala Risiko</label>
                        {{ Form::text('skala_risiko_residual', '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                    <div class="col-md-2">
                        <label>Level Risiko</label>
                        {{ Form::text('level_risiko_residual', '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                </div>
                <div class="row" id="divDeskripsiDampakResidual">
                    <div class="col-md-12">
                        <label id="labelDeskripsiDampak">Deskripsi Dampak Residual</label>
                        {{ Form::textarea('deskripsi_dampak_residual', $analisa->deskripsi_dampak_residual, ['class' => 'form-control', 'rows' => 5]) }}
                    </div>
                </div>
                <div class="row" id="divAsumsiDampakResidual">
                    <div class="col-md-12">
                        <label>Asumsi Perhitungan Dampak & Probabilitas Residual</label>
                        {{ Form::textarea('asumsi_perhitungan_dampak_residual', $analisa->asumsi_perhitungan_dampak_residual, ['class' => 'form-control', 'rows' => 5]) }}
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="col-12">
        <div class="row g-2 justify-content-between">
            <div class="col-auto">
                <a href="{{ route('projects.risks.edit', ['project' => $projectPeriodeList->id, 'risk' => $projectRisk->id]) }}" class="btn btn-secondary me-2">
                    <span class="bx bx-chevron-left" style="line-height: 0.8;"></span> Kembali ke Identifikasi
                </a>
                <a href="{{ route('projects.risks.index', ['project' => $projectPeriodeList->id]) }}" class="btn btn-outline-secondary">Batal</a>
            </div>

            <div class="col-auto">
                <button type="button" data-action="save" class="btn btn-warning bg-warning ms-auto btn-action">Simpan dan Keluar</button>
                <button type="button" data-action="savenext" class="btn btn-primary ms-auto btn-action">Simpan dan Lanjut Ke Rencana Perlakuan</button>
            </div>
        </div>
    </div>
    @include('project-risk._modal_kualitatif')
    @include('project-risk._modal_kualitatif_res')
    @include('project-risk._modal_skala_parameter')
@endsection
@push('styles')
<style>
    #btnShowKualitatif i, #btnShowSkalaInfo i {
        font-size: 1.2rem; /* Ukuran ikon */
        color: #007bff; /* Warna biru */
        cursor: pointer;
    }

    #btnShowKualitatif:hover i, , #btnShowSkalaInfo:hover i {
        color: #0056b3; /* Warna lebih gelap saat hover */
    }

    #btnShowKualitatifRes i {
        font-size: 1.2rem; /* Ukuran ikon */
        color: #007bff; /* Warna biru */
        cursor: pointer;
    }

    #btnShowKualitatifRes:hover i {
        color: #0056b3; /* Warna lebih gelap saat hover */
    }

    .modal-80 {
        max-width: 80%; /* Modal akan menempati 80% lebar layar */
    }

    .fw-bold {
        font-weight: bold;
        text-align: left;
    }

    .table td.text-start {
        text-align: left !important;
    }

    .loading-spinner {
        display: inline-block;
        width: 1.2rem;
        height: 1.2rem;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush
@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
const savedSkalaRisiko = "{{ $analisa->skala_risiko ?? '' }}";
const savedLevelRisiko = "{{ $analisa->level_risiko ?? '' }}";
const savedSkalaRisikoResidual = "{{ $analisa->skala_risiko_residual ?? '' }}";
const savedLevelRisikoResidual = "{{ $analisa->level_risiko_residual ?? '' }}";

function getSkalaProbabilitasByValue(value) {
    const skalaProbabilitases = @json($skalaProbabilitas);
    for (const index in skalaProbabilitases) {
        const skalaProbabilitas = skalaProbabilitases[index];
        if (value >= skalaProbabilitas.min) {
            return skalaProbabilitas;
        }
    }
}

function refreshEksposureRisiko(residual = false) {
    const domEksposurRisiko = $('[name="eksposur_risiko' + (residual ? '_residual' : '') + '"]');
    const kategoriDampak = $('[name="kategori_dampak"]').val();
    const nilaiProbabilitas = parseFloat($('[name="nilai_probabilitas' + (residual ? '_residual' : '') + '"]').val());
    const riskTolerance = parseFloat($('[name="risk_tolerance"]').val());

    if (kategoriDampak === "{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF }}") {
        const nilaiDampak = parseFloat($('[name="nilai_dampak' + (residual ? '_residual' : '') + '"]').val());
        if (isNaN(nilaiDampak) || isNaN(nilaiProbabilitas)) {
            domEksposurRisiko.val('');
        } else {
            domEksposurRisiko.val(nilaiDampak * nilaiProbabilitas / 100);
        }
    } else if (kategoriDampak === "{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF }}") {
        const skalaDampak = parseFloat($('[name="skala_dampak' + (residual ? '_residual' : '') + '"]').val());
        domEksposurRisiko.val('');
        const riskLimit = parseFloat($('[name="_risk_limit"]').val());
        if (isNaN(skalaDampak) || isNaN(nilaiProbabilitas) || isNaN(riskLimit)) {
            domEksposurRisiko.val('');
        } else {
            domEksposurRisiko.val(skalaDampak * (1 / 100) * nilaiProbabilitas / 100 * riskTolerance);
        }
    }
}

function refreshSkalaAndLevelRisiko(residual = false, isInit = false) {
    const riskMaps = @json($riskMaps);

    // 1. Ambil Elemen Input Output (Target)
    const domSkalaRisiko = $('[name="skala_risiko' + (residual ? '_residual' : '') + '"]');
    const domLevelRisiko = $('[name="level_risiko' + (residual ? '_residual' : '') + '"]');

    // 2. Ambil Input Skala Dampak
    const skalaDampak = $('[name="skala_dampak' + (residual ? '_residual' : '') + '"]').val();

    // 3. Ambil Tingkat dari Dropdown Skala Parameter (Bukan dari input hidden/text)
    let skalaProbabilitas = 0;
    if (residual) {
        // Ambil dari dropdown Residual
        skalaProbabilitas = $('#skala_parameter_residual_id').find(':selected').data('tingkat');
    } else {
        // Ambil dari dropdown Inheren
        skalaProbabilitas = $('#skala_parameter_id').find(':selected').data('tingkat');
    }

    if (isInit) {
        if (!residual && savedSkalaRisiko && savedLevelRisiko) {
            domSkalaRisiko.val(savedSkalaRisiko);
            domLevelRisiko.val(savedLevelRisiko);
            return;
        }
        if (residual && savedSkalaRisikoResidual && savedLevelRisikoResidual) {
            domSkalaRisiko.val(savedSkalaRisikoResidual);
            domLevelRisiko.val(savedLevelRisikoResidual);
            return;
        }
    }

    if (!skalaDampak || !skalaProbabilitas) {
        domSkalaRisiko.val('');
        domLevelRisiko.val('');
        return;
    }

    // 4. Mapping Risk Map (Kunci: "SkalaDampak-SkalaProbabilitas")
    const key = skalaDampak + '-' + skalaProbabilitas;
    const riskMap = riskMaps[key];

    // console.log('Check Map:', key, riskMap); // Debugging

    if (riskMap) {
        domSkalaRisiko.val(riskMap.nilai_risiko);
        domLevelRisiko.val(riskMap.level_risiko);
    } else {
        domSkalaRisiko.val('');
        domLevelRisiko.val('');
    }
}

$(document).ready(function() {
    const groupedSkalaParameters = @json($groupedSkalaParameters);
    const riskMaps = @json($riskMaps);

    const $paramTypeInherent = $('#skala_parameter_type');
    const $scaleInherent = $('#skala_parameter_id');
    const $nilaiProbInherent = $('[name="nilai_probabilitas"]');

    const $paramTypeResidual = $('#skala_parameter_type_residual');
    const $scaleResidual = $('#skala_parameter_residual_id');
    const $nilaiProbResidual = $('[name="nilai_probabilitas_residual"]');

    const savedSkalaDampak = "{{ $analisa->skala_dampak ?? '' }}";
    const savedSkalaDampakResidual = "{{ $analisa->skala_dampak_residual ?? '' }}";

    // === FITUR 1: Auto Set Parameter Type jika opsi hanya 1 (selain placeholder) ===
    if ($paramTypeInherent.find('option').length === 1) {
        $paramTypeInherent.prop('selectedIndex', 0).trigger('change');
        populateSkalaDropdown('Persentase Kemungkinan Terjadi', $scaleInherent);
    }

    if ($paramTypeResidual.find('option').length === 1) {
        $paramTypeResidual.prop('selectedIndex', 0).trigger('change');
        populateSkalaDropdown('Persentase Kemungkinan Terjadi', $scaleResidual);
    }

    function populateSkalaDropdown(selectedType, $scaleSelect, selectedValue = null) {
        $scaleSelect.prop('disabled', true).html('<option value="">Pilih Skala...</option>');
        if (!selectedType) return;

        const scales = groupedSkalaParameters[selectedType] || [];
        let options = '<option value="">Pilih Skala...</option>';
        scales.forEach(function(scale) {
            options += `<option value="${scale.id}" data-min="${scale.min}" data-max="${scale.max}" data-tingkat="${scale.tingkat}">${scale.tingkat} - ${scale.skala}</option>`;
        });

        $scaleSelect.html(options).prop('disabled', false);

        if (selectedValue && $scaleSelect.find(`option[value="${selectedValue}"]`).length > 0) {
            $scaleSelect.val(selectedValue);
        }
    }

    // === FITUR 2: Validasi Terbalik (Probabilitas -> Skala) ===
    // Update Skala Dropdown otomatis ketika Nilai Probabilitas berubah
    $nilaiProbInherent.on('input change', function() {
        const value = parseFloat($(this).val());
        if (isNaN(value)) return;

        const matchedScale = getSkalaProbabilitasByValue(value);
        if (matchedScale) {
            const $option = $scaleInherent.find(`option[data-tingkat="${matchedScale.tingkat}"]`);
            if ($option.length > 0) {
                if ($scaleInherent.val() != $option.val()) {
                    $scaleInherent.val($option.val()).trigger('change.selectOnly');
                }
            }
        }
        refreshEksposureRisiko();
    });

    $nilaiProbResidual.on('input change', function() {
        const value = parseFloat($(this).val());
        if (isNaN(value)) return;

        const matchedScale = getSkalaProbabilitasByValue(value);
        if (matchedScale) {
            const $option = $scaleResidual.find(`option[data-tingkat="${matchedScale.tingkat}"]`);
            if ($option.length > 0) {
                if ($scaleResidual.val() != $option.val()) {
                    $scaleResidual.val($option.val()).trigger('change.selectOnly');
                }
            }
        }
        refreshEksposureRisiko(true);
    });

    function validateNilaiProbabilitas($input, $scaleSelect) {
        const $selectedOption = $scaleSelect.find('option:selected');
        if (!$selectedOption.val()) return;

        const min = parseFloat($selectedOption.data('min'));
        const max = parseFloat($selectedOption.data('max'));
        let currentValue = parseFloat($input.val());

        if (isNaN(currentValue)) return;

        let correctedValue = null;
        if (currentValue < min) correctedValue = min;
        if (currentValue > max) correctedValue = max;

        if (correctedValue !== null) {
            Swal.fire({
                title: 'Peringatan!',
                text: `Nilai probabilitas harus berada di antara ${min}% dan ${max}%. Nilai otomatis disesuaikan.`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $input.val(correctedValue).trigger('change');
        }
    }

    $('#btnShowSkalaInfo').on('click', function() {
        $('#modalSkalaInfo').modal('show');
    });

    $('#modalSkalaInfo').on('click', '.selectable-cell', function() {
        const selectedType = $(this).data('parameter-type');
        const selectedSkalaId = $(this).data('skala-id');

        $paramTypeInherent.val(selectedType).trigger('change');
        $scaleInherent.val(selectedSkalaId).trigger('change');

        $('#modalSkalaInfo').modal('hide');
    });

    $paramTypeInherent.on('change', function() {
        const selectedType = $(this).val();
        $paramTypeResidual.val(selectedType);

        const savedInherentId = '{{ $analisa->skala_parameter_id ?? '' }}';
        const savedResidualId = '{{ $analisa->skala_parameter_residual_id ?? '' }}';

        populateSkalaDropdown(selectedType, $scaleInherent, savedInherentId);
        populateSkalaDropdown(selectedType, $scaleResidual, savedResidualId);

        // Trigger perubahan skala residual untuk disable tingkat yang tidak valid
        $scaleInherent.trigger('change');

        // populateSkalaDropdown(selectedType, $scaleInherent);
        // populateSkalaDropdown(selectedType, $scaleResidual);

        // // $scaleInherent.val('').trigger('change');

        // // Jika data lama ada, set ulang (agar tidak reset saat auto select)
        // const savedInherentId = '{{ $analisa->skala_parameter_id ?? '' }}';
        // if(savedInherentId && $scaleInherent.find(`option[value="${savedInherentId}"]`).length) {
        //     $scaleInherent.val(savedInherentId);
        // } else {
        //     $scaleInherent.val('').trigger('change');
        // }

        // const savedResidualId = '{{ $analisa->skala_parameter_residual_id ?? '' }}';
        // if(savedResidualId && $scaleResidual.find(`option[value="${savedResidualId}"]`).length) {
        //     $scaleResidual.val(savedResidualId);
        // } else {
        //     $scaleResidual.val('').trigger('change');
        // }
    });

    $paramTypeResidual.on('change', function() {
        // Jika data lama ada, set ulang (agar tidak reset saat auto select)
        const savedResidualId = '{{ $analisa->skala_parameter_residual_id ?? '' }}';
        console.log(savedResidualId)
        if(savedResidualId && $scaleResidual.find(`option[value="${savedResidualId}"]`).length) {
            $scaleResidual.val(savedResidualId);
        } else {
            $scaleResidual.val('').trigger('change');
        }
    });

    $scaleInherent.on('change', function() {
        const $selectedOption = $(this).find('option:selected');
        const tingkatInherent = parseInt($selectedOption.data('tingkat')) || 0;
        const currentResidualValue = $scaleResidual.val();
        const $selectedResidualOption = $scaleResidual.find('option:selected');
        const tingkatResidualsaatIni = parseInt($selectedResidualOption.data('tingkat')) || 0;

        // 1. Update status disabled pada opsi Residual
        $scaleResidual.find('option').each(function() {
            const tingkatOption = parseInt($(this).data('tingkat')) || 0;
            if (tingkatOption > 0) {
                // [UPDATE] disabled tingkat skala probabilitas residual validasi
                // Disable jika tingkat residual > tingkat inherent
                // $(this).prop('disabled', tingkatOption > tingkatInherent);
            }
        });

        // 2. LOGIKA KRUSIAL: Jika nilai residual saat ini tidak valid (lebih tinggi dari inherent)
        // atau jika belum dipilih, maka paksa samakan dengan Inherent agar tidak NULL
        if (tingkatResidualsaatIni > tingkatInherent || currentResidualValue === "") {
            // Cari ID di dropdown residual yang punya tingkat yang sama dengan Inherent
            const fallbackId = $scaleResidual.find(`option[data-tingkat="${tingkatInherent}"]`).val();
            if (fallbackId) {
                $scaleResidual.val(fallbackId).trigger('change');
            }
        }

        refreshSkalaAndLevelRisiko(false);
    });

    $scaleResidual.on('change', function() {
        // const $selectedOption = $(this).find('option:selected');
        // const min = $selectedOption.data('min');
        // const max = $selectedOption.data('max');

        // if ($(this).val()) {
        //     $nilaiProbResidual.prop('disabled', false).attr({ min, max }).val('');
        // } else {
        //     $nilaiProbResidual.prop('disabled', true).val('').attr({ min: 0, max: 100 });
        // }
        // $nilaiProbResidual.trigger('change');

        refreshSkalaAndLevelRisiko(true);
    });

    $nilaiProbInherent.on('blur', function() {
        const $input = $(this);
        let currentValue = parseFloat($input.val());
        if (isNaN(currentValue)) return;

        const $scaleSelect = $scaleInherent;
        const min = parseFloat($scaleSelect.find('option:selected').data('min'));
        const max = parseFloat($scaleSelect.find('option:selected').data('max'));

        let correctedValue = null;

        if (!isNaN(min) && currentValue < min) correctedValue = min;
        if (!isNaN(max) && currentValue > max) correctedValue = max;

        if (correctedValue !== null) {
            Swal.fire({
                title: 'Peringatan!',
                text: `Nilai probabilitas harus berada di antara ${min}% dan ${max}%. Nilai otomatis disesuaikan.`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $input.val(correctedValue).trigger('change');
        }
    });

    $nilaiProbResidual.on('blur', function() {
        const $input = $(this);
        let currentValue = parseFloat($input.val());
        if (isNaN(currentValue)) return;

        const $scaleSelect = $scaleResidual;
        const min = parseFloat($scaleSelect.find('option:selected').data('min'));
        const max = parseFloat($scaleSelect.find('option:selected').data('max'));
        const inherentValue = parseFloat($nilaiProbInherent.val());

        let correctedValue = null;
        let reason = '';

        if (!isNaN(min) && currentValue < min) {
            correctedValue = min;
            reason = `Nilai harus lebih besar atau sama dengan ${min}%.`;
        }
        if (!isNaN(max) && currentValue > max) {
            correctedValue = max;
            reason = `Nilai harus lebih kecil atau sama dengan ${max}%.`;
        }

        const valueToCompare = (correctedValue !== null) ? correctedValue : currentValue;

        if (!isNaN(inherentValue) && valueToCompare > inherentValue) {
            correctedValue = inherentValue;
            reason = 'Nilai Probabilitas Residual tidak boleh lebih besar dari Inherent.';
        }
        if (correctedValue !== null) {
            Swal.fire({
                title: 'Peringatan!',
                text: `${reason} Nilai otomatis disesuaikan.`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $input.val(correctedValue).trigger('change');
        }
    });

    const savedParameterType = '{{ $selectedParameterType ?? '' }}';
    const savedInherentScaleId = '{{ $analisa->skala_parameter_id ?? '' }}';
    const savedResidualScaleId = '{{ $analisa->skala_parameter_residual_id ?? '' }}';

    if (savedParameterType) {
        $paramTypeInherent.val(savedParameterType);
        $paramTypeResidual.val(savedParameterType);

        populateSkalaDropdown(savedParameterType, $scaleInherent);
        populateSkalaDropdown(savedParameterType, $scaleResidual);

        $scaleInherent.val(savedInherentScaleId);
        $scaleResidual.val(savedResidualScaleId);

        if (savedInherentScaleId) {
            const $selectedInherent = $scaleInherent.find('option:selected');
            // $nilaiProbInherent.prop('disabled', false).attr({
            //     min: $selectedInherent.data('min'),
            //     max: $selectedInherent.data('max')
            // });
        }
        if (savedResidualScaleId) {
            const $selectedResidual = $scaleResidual.find('option:selected');
            // $nilaiProbResidual.prop('disabled', false).attr({
            //     min: $selectedResidual.data('min'),
            //     max: $selectedResidual.data('max')
            // });
        }

        const tingkatInherent = parseInt($scaleInherent.find('option:selected').data('tingkat')) || 0;
        if (tingkatInherent > 0) {
            $scaleResidual.find('option').each(function() {
                const tingkatOption = parseInt($(this).data('tingkat')) || 0;
                $(this).prop('disabled', tingkatOption > tingkatInherent);
            });
        }
    }

    $('[name="nilai_probabilitas"], [name="nilai_probabilitas_residual"]').on('input change blur', function() {
        const input = $(this);
        const isResidual = input.attr('name') === 'nilai_probabilitas_residual';

        const nilaiProbabilitasInput = $('[name="nilai_probabilitas"]');
        const nilaiProbabilitasResidualInput = $('[name="nilai_probabilitas_residual"]');

        const nilaiProbabilitas = parseFloat(nilaiProbabilitasInput.val());
        const nilaiResidual = parseFloat(nilaiProbabilitasResidualInput.val());

        if (isResidual && !isNaN(nilaiProbabilitas) && nilaiResidual > nilaiProbabilitas) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Nilai Probabilitas Residual tidak boleh lebih besar dari Nilai Probabilitas Inherent.',
                icon: 'warning',
                confirmButtonText: 'OK'
            }).then(() => {
                nilaiProbabilitasResidualInput.val(nilaiProbabilitas).trigger('change');
                nilaiProbabilitasResidualInput.focus();
            });
        }

        if (!isResidual && !isNaN(nilaiResidual) && nilaiResidual > nilaiProbabilitas) {
            nilaiProbabilitasResidualInput.val(nilaiProbabilitas).trigger('change');
        }
    });

    $('[name="kategori_dampak"]').on('change', function() {
        const value = $(this).val();

        if (value === "{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF }}") {
            $('[name="nilai_dampak"]').prop('readonly', true).val(0);
            $('[name="nilai_dampak_residual"]').prop('readonly', true).val(0);
        } else {
            $('[name="nilai_dampak"]').prop('readonly', false);
            $('[name="nilai_dampak_residual"]').prop('readonly', false);
        }
        refreshEksposureRisiko();
        refreshEksposureRisiko(true);
        refreshSkalaAndLevelRisiko();
    }).change();

    $('[name="nilai_probabilitas"]').on('input change', function() {
        const value = $(this).val();
        const skalaProbabilitas = getSkalaProbabilitasByValue(value);

        if (!skalaProbabilitas || value === '') {
            $('[name="skala_probabilitas"]').val('').change();
            return;
        }
        $('[name="skala_probabilitas"]').val('(' + skalaProbabilitas.tingkat + ') ' + skalaProbabilitas.skala).data('tingkat', skalaProbabilitas.tingkat).change();
        refreshEksposureRisiko();
    }).change();

    $('[name="nilai_probabilitas_residual"]').on('input change', function() {
        const value = $(this).val();
        const skalaProbabilitas = getSkalaProbabilitasByValue(value);

        if (!skalaProbabilitas || value === '') {
            $('[name="skala_probabilitas_residual"]').val('').change();
            return;
        }
        $('[name="skala_probabilitas_residual"]').val('(' + skalaProbabilitas.tingkat + ') ' + skalaProbabilitas.skala).data('tingkat', skalaProbabilitas.tingkat).change();
        refreshEksposureRisiko(true);
    }).change();

    $('[name="nilai_dampak"]').on('input change', function() {
        refreshEksposureRisiko();
        refreshSkalaAndLevelRisiko();
    }).change();

    $('[name="nilai_dampak_residual"]').on('input change', function() {
        refreshEksposureRisiko(true);
        refreshSkalaAndLevelRisiko(true);
    }).change();

    $('[name="skala_probabilitas"]').on('input change', function() {
        refreshSkalaAndLevelRisiko();
    }).change();

    $('[name="skala_probabilitas_residual"]').on('input change', function() {
        refreshSkalaAndLevelRisiko(true);
    }).change();

    /*
    $('.btn-action').on('click', function() {
        const action = $(this).data('action');

        if (action === 'save') {
            const form = $('#mainForm');

            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }

            const formData = new FormData(form[0]);
            const url = "{{ route('projects.risks.do-analisa', ['project' => $projectPeriodeList->id, 'risk' => $projectRisk->id]) }}";

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: response.message || 'Data berhasil disimpan',
                        icon: 'success',
                        confirmButtonText: 'OK',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('projects.risks.index', ['project' => $projectPeriodeList->id]) }}";
                        }
                    });
                },
                error: function(response) {
                    Swal.fire({
                        title: 'Gagal',
                        text: response.responseJSON.message || 'Terjadi kesalahan saat menyimpan data',
                        icon: 'error',
                        confirmButtonText: 'OK',
                    });
                }
            });
        }
    });
    */

    $('.btn-action').on('click', function() {
        const action = $(this).data('action'); // Ambil action dari tombol yang ditekan
        const form = $('#mainForm');

        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        // Tampilkan konfirmasi Swal sebelum menyimpan data
        Swal.fire({
            title: 'Simpan Data Analisa?',
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
                const formData = new FormData(form[0]);
                formData.append('action', action); // Tambahkan action ke formData

                const url = "{{ route('projects.risks.do-analisa', ['project' => $projectPeriodeList->id, 'risk' => $projectRisk->id]) }}";

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil',
                            text: response.message || 'Data berhasil disimpan',
                            icon: 'success',
                            confirmButtonText: 'OK',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Redirect sesuai action
                                if (action === 'savenext') {
                                    window.location.href = "{{ route('projects.risks.rencana', ['project' => $projectPeriodeList->id, 'risk' => $projectRisk->id]) }}";
                                } else {
                                    window.location.href = "{{ route('projects.risks.index', ['project' => $projectPeriodeList->id]) }}";
                                }
                            }
                        });
                    },
                    error: function(response) {
                        Swal.fire({
                            title: 'Gagal',
                            text: response.responseJSON.message || 'Terjadi kesalahan saat menyimpan data',
                            icon: 'error',
                            confirmButtonText: 'OK',
                        });
                    }
                });
            }
        });
    });

    // Old Code
    // $('#skala_dampak').on('change', function () {
    //     if ($('#kategoriDampak').val() !== '{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF }}') {
    //         return;
    //     }

    //     const selectedSkalaDampak = parseInt($(this).val());
    //     const $skalaDampakResidual = $('#skala_dampak_residual');
    //     const currentValueResidual = parseInt($skalaDampakResidual.val());

    //     if (isNaN(selectedSkalaDampak)) {
    //         $skalaDampakResidual.find('option').prop('disabled', false);
    //         return;
    //     }

    //     if (currentValueResidual > selectedSkalaDampak) {
    //         $skalaDampakResidual.val('').trigger('change');
    //     }

    //     $skalaDampakResidual.find('option').each(function () {
    //         if (!$(this).val()) return;

    //         const optionValue = parseInt($(this).val());
    //         $(this).prop('disabled', optionValue > selectedSkalaDampak);
    //     });
    // });

    $('#skala_dampak').on('change', function () {
        // Ambil nilai integer (1-5) dari dropdown
        const inherentVal = parseInt($(this).val()) || 0;

        const $residualSelect = $('#skala_dampak_residual');
        const residualVal = parseInt($residualSelect.val()) || 0;

        // 1. Loop semua opsi residual untuk disable yang lebih besar dari inheren
        $residualSelect.find('option').each(function () {
            const optVal = parseInt($(this).val());
            if (optVal > 0) { // Skip placeholder value ""
                if (inherentVal > 0 && optVal > inherentVal) {
                    $(this).prop('disabled', true); // Disable jika > Inheren
                } else {
                    $(this).prop('disabled', false); // Enable jika <= Inheren
                }
            }
        });

        // 2. Cek Validasi Nilai: Jika Residual saat ini melebihi Inheren baru
        if (residualVal > inherentVal && inherentVal > 0) {
            // Set nilai residual SAMA dengan inheren (sesuai request)
            $residualSelect.val(inherentVal).trigger('change');

            // Opsional: Beri notifikasi toast/console agar user sadar ada perubahan otomatis
            // console.log('Skala Dampak Residual disesuaikan otomatis karena melebihi Inheren');
        }

        // 3. Update Input Hidden (Untuk Kualitatif)
        $('#skala_dampak_hidden').val($(this).val());

        // 4. Refresh Kalkulasi Risk Map
        refreshEksposureRisiko();
        refreshSkalaAndLevelRisiko(false); // Refresh Inheren
    });

    $('#skala_dampak_residual').on('change', function () {
        const $inherentSelect = $('#skala_dampak');
        const inherentVal = parseInt($inherentSelect.val()) || 0;
        const residualVal = parseInt($(this).val()) || 0;

        // 1. Validasi Manual: Cegah user memilih nilai > Inheren
        // (Meskipun sudah di-disable, validasi ini untuk double protection)
        if (inherentVal > 0 && residualVal > inherentVal) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Skala Dampak Residual tidak boleh lebih besar dari Skala Dampak Inheren.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });

            // Reset ke nilai inheren (max allowed)
            $(this).val(inherentVal).trigger('change');
            return;
        }

        // 2. Update Input Hidden (Untuk Kualitatif)
        $('#skala_dampak_residual_hidden').val($(this).val());

        // 3. Refresh Kalkulasi Risk Map
        refreshEksposureRisiko(true);
        refreshSkalaAndLevelRisiko(true); // Refresh Residual
    });

    // Event listener untuk dropdown kategori dampak
    $('#kategoriDampak').on('change', function () {
        const selectedValue = $(this).val(); // Ambil nilai yang dipilih

        if (selectedValue == '{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF }}') {
            //$('#labelDeskripsiDampak').text('Asumsi Perhitungan Dampak');
            $('#divDeskripsiDampak').hide();
            $('#divDeskripsiDampakResidual').hide();
            $('#btnShowKualitatifRes').hide();
            $('#divAsumsiDampak').show();
            $('#divAsumsiDampakResidual').show();
            $('#divAreaDampak').hide();
            $('#div_risk_limit').show();
            $('#area_dampak').prop('required', false);
            $('#asumsi_perhitungan_dampak').prop('required', true);
            $('#asumsi_perhitungan_dampak_residual').prop('required', true);
        } else if (selectedValue == '{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF }}') {
            //$('#labelDeskripsiDampak').text('Deskripsi Dampak');
            $('#divDeskripsiDampak').show();
            $('#divDeskripsiDampakResidual').show();
            $('#btnShowKualitatifRes').show();
            $('#divAsumsiDampak').hide();
            $('#divAsumsiDampakResidual').hide();
            $('#divAreaDampak').show();
            //$('#div_risk_limit').hide();
            $('#div_risk_limit').show();
            $('#area_dampak').prop('required', true);
            $('#asumsi_perhitungan_dampak').prop('required', false);
            $('#asumsi_perhitungan_dampak_residual').prop('required', false);
        } else {
            //$('#labelDeskripsiDampak').text('Deskripsi Dampak'); // Default
            $('#divDeskripsiDampak').show();
            $('#divDeskripsiDampakResidual').show();
            $('#btnShowKualitatifRes').show();
            $('#divAreaDampak').show();
            $('#div_risk_limit').show();
            $('#divAsumsiDampak').hide();
            $('#divAsumsiDampakResidual').hide();
            $('#area_dampak').prop('required', true);
            $('#asumsi_perhitungan_dampak').prop('required', false);
            $('#asumsi_perhitungan_dampak_residual').prop('required', false);
        }

        $('#skala_dampak').trigger('change');
    });

    // Trigger perubahan awal untuk set label saat halaman dimuat
    $('#kategoriDampak').trigger('change');

    $('#btnShowKualitatif').on('click', function () {
        $('#modalKualitatif').modal('show'); // Tampilkan modal
    });

    $('#btnShowKualitatifRes').on('click', function () {
        var areaId = $("#area_dampak").val();
        var skalaDampakIn = $("#skala_dampak").val();

        $.ajax({
            url: "{{ route('get.kualitatif.res') }}",
            type: "GET",
            data: {
                areaId: areaId,
                skalaDampak: skalaDampakIn
            },
            success: function (response) {
                if (response.html) {
                    $('#modalKualitatifRes .modal-body').html(response.html); // Isi modal dengan data dari controller
                    $('#modalKualitatifRes').modal('show'); // Tampilkan modal
                }
            },
            error: function (xhr) {
                console.log("Error:", xhr);
            }
        });
    });

    $(".select-risk").click(function (e) {
        e.preventDefault(); // Mencegah reload karena <a>

        // Ambil data dari elemen yang diklik
        var areaId = $(this).data("area-id");
        var areaTitle = $(this).data("area-title");
        var skala = $(this).data("skala");
        var skalaDesc = $(this).data("skala-desc");

        // Set nilai di form dropdown
        $("#area_dampak").val(areaId).trigger("change");
        $("#skala_dampak").val(skala).trigger("change");

        // Tutup modal
        $("#modalKualitatif").modal("hide");
    });

    function updateSkalaDampak(isInit = false) {
        const kategoriDampak = $('[name="kategori_dampak"]').val();
        const riskLimit = parseFloat($('#risk_limit').val()) || 0;
        const nilaiDampak = parseFloat($('[name="nilai_dampak"]').val().replace(/[^0-9.-]+/g, '')) || 0;
        const nilaiDampakResidual = parseFloat($('[name="nilai_dampak_residual"]').val().replace(/[^0-9.-]+/g, '')) || 0;

        const $skalaDampak = $('[name="skala_dampak"]');
        const $skalaDampakHidden = $('#skala_dampak_hidden');

        const $skalaDampakResidual = $('[name="skala_dampak_residual"]');
        const $skalaDampakResidualHidden = $('#skala_dampak_residual_hidden');

        let skala = 5; // Default ke High jika risk limit = 0
        let skalaResidual = 5; // Default ke High jika risk limit = 0

        if (kategoriDampak === "{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF }}") {
            if (riskLimit > 0) {
                const percentage = (nilaiDampak / riskLimit) * 100;
                const percentageResidual = (nilaiDampakResidual / riskLimit) * 100;

                skala = calculateSkalaDampak(percentage);
                skalaResidual = calculateSkalaDampak(percentageResidual);
            }

            if (isInit) {
                if (savedSkalaDampak) skala = savedSkalaDampak;
                if (savedSkalaDampakResidual) skalaResidual = savedSkalaDampakResidual;
            }

            // Old Code: Set nilai skala dampak dan buat readonly
            // $skalaDampak.val(skala).prop('disabled', true);
            // $skalaDampakResidual.val(skalaResidual).prop('disabled', true);

            $skalaDampak.val(skala).prop('disabled', false);
            $skalaDampakResidual.val(skalaResidual).prop('disabled', false);
        } else {
            // Jika kategori dampak Kualitatif, skala dampak bisa dipilih manual
            // $skalaDampak.prop('disabled', false);
            // $skalaDampakResidual.prop('disabled', false);
        }

        // Pastikan nilai tetap dikirim ke server meskipun disabled
        $skalaDampakHidden.val($skalaDampak.val());
        $skalaDampakResidualHidden.val($skalaDampakResidual.val());
    }

    function calculateSkalaDampak(percentage) {
        if (percentage <= 20) return 1; // Low
        if (percentage > 20 && percentage <= 40) return 2; // Low To Moderate
        if (percentage > 40 && percentage <= 60) return 3; // Moderate
        if (percentage > 60 && percentage <= 80) return 4; // Moderate To High
        return 5; // High
    }

    // Event listener untuk perubahan nilai dampak, nilai dampak residual, kategori dampak, dan risk limit
    $('[name="nilai_dampak"], [name="nilai_dampak_residual"], [name="kategori_dampak"]').on('input change', function() {
        updateSkalaDampak();
    });

    // Event listener jika pengguna mengubah skala dampak secara manual (hanya untuk Kualitatif)
    $('[name="skala_dampak"], [name="skala_dampak_residual"]').on('change', function() {
        const targetHiddenField = $(this).attr('name') === 'skala_dampak' ? '#skala_dampak_hidden' : '#skala_dampak_residual_hidden';
        $(targetHiddenField).val($(this).val());
    });

    function parseRupiahToNumber(value) {
        return parseFloat(value.replace(/[^0-9,-]/g, '').replace(',', '.')) || 0;
    }

    $('#nilai_dampak').on('input', function () {
        var nilaiDampak = $(this).val().replace(/[^\d.-]/g, ''); // Ambil hanya angka dari input text
        $('#nilai_dampak_residual').val(''); // Reset nilai dampak residual setiap kali nilai dampak diubah
    });

    $('#nilai_dampak_residual').on('input change', function () {
        var nilaiDampak = parseRupiahToNumber($('#nilai_dampak').val()); // Ambil nilai dampak tanpa format
        var nilaiResidual = parseRupiahToNumber($(this).val()); // Ambil nilai dampak residual tanpa format

        // console.log(nilaiDampak);

        if (nilaiResidual > nilaiDampak) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Nilai Dampak Residual tidak boleh lebih besar dari Nilai Dampak Inheren.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $(this).val($('#nilai_dampak').val()); // Set nilainya sama dengan nilai dampak
        }
    });

    // Panggil fungsi saat halaman pertama kali dimuat
    updateSkalaDampak();

    $('[name="nilai_probabilitas"], [name="nilai_probabilitas_residual"]').on('input change blur', function() {
        const input = $(this);
        const isResidual = input.attr('name') === 'nilai_probabilitas_residual';

        const maxValue = 100;
        let currentValue = parseFloat(input.val());

        if (!isNaN(currentValue) && currentValue > maxValue) {
            input.val(maxValue);
            input.trigger('change');
        }

        const nilaiProbabilitasInput = $('[name="nilai_probabilitas"]');
        const nilaiProbabilitasResidualInput = $('[name="nilai_probabilitas_residual"]');

        const nilaiProbabilitas = parseFloat(nilaiProbabilitasInput.val()) || 0;
        const nilaiResidual = parseFloat(nilaiProbabilitasResidualInput.val()) || 0;

        if (isResidual && nilaiProbabilitasResidualInput.val() !== '' && nilaiResidual > nilaiProbabilitas) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Nilai Probabilitas Residual tidak boleh lebih besar dari Nilai Probabilitas Inherent.',
                icon: 'warning',
                confirmButtonText: 'OK'
            }).then(() => {
                nilaiProbabilitasResidualInput.val(nilaiProbabilitas).trigger('change');
                nilaiProbabilitasResidualInput.focus();
            });
        }

        if (!isResidual) {
            validateProbabilitasResidual();
        }
    });

    // Fungsi pembantu untuk dipanggil saat nilai inherent berubah
    function validateProbabilitasResidual() {
        const nilaiProbabilitasInput = $('[name="nilai_probabilitas"]');
        const nilaiProbabilitasResidualInput = $('[name="nilai_probabilitas_residual"]');
        const nilaiProbabilitas = parseFloat(nilaiProbabilitasInput.val()) || 0;
        const nilaiResidual = parseFloat(nilaiProbabilitasResidualInput.val()) || 0;

        if (nilaiProbabilitasResidualInput.val() !== '' && nilaiResidual > nilaiProbabilitas) {
            nilaiProbabilitasResidualInput.val(nilaiProbabilitas).trigger('change');
        }
    }

    $('#btnCalculatePoisson').on('click', function() {
        const $button = $(this);
        const $icon = $button.find('i');
        const $nilaiProbabilitas = $('[name="nilai_probabilitas"]');

        // Tampilkan loading spinner
        $icon.removeClass('bx-calculator').addClass('loading-spinner');
        $button.prop('disabled', true);

        // Kirim request AJAX untuk perhitungan Poisson
        $.ajax({
            url: "{{ route('calculate-poisson') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                project_risk_id: "{{ $projectRisk->id }}"
            },
            success: function(response) {
                if (response.success) {
                    // Update nilai probabilitas
                    $nilaiProbabilitas.val(response.probability).trigger('change');

                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Perhitungan Poisson berhasil dilakukan',
                        icon: 'success'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Gagal melakukan perhitungan Poisson',
                    icon: 'error'
                });
            },
            complete: function() {
                // Kembalikan tampilan tombol
                $icon.removeClass('loading-spinner').addClass('bx-calculator');
                $button.prop('disabled', false);
            }
        });
    });

    $('#btnCalculatePoissonRes').on('click', function() {
        const $button = $(this);
        const $icon = $button.find('i');
        const $nilaiProbabilitas = $('[name="nilai_probabilitas_residual"]');

        // Tampilkan loading spinner
        $icon.removeClass('bx-calculator').addClass('loading-spinner');
        $button.prop('disabled', true);

        // Kirim request AJAX untuk perhitungan Poisson
        $.ajax({
            url: "{{ route('calculate-poisson-residual') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                project_risk_id: "{{ $projectRisk->id }}"
            },
            success: function(response) {
                if (response.success) {
                    // Update nilai probabilitas
                    $nilaiProbabilitas.val(response.probability).trigger('change');

                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Perhitungan Poisson berhasil dilakukan',
                        icon: 'success'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Gagal melakukan perhitungan Poisson',
                    icon: 'error'
                });
            },
            complete: function() {
                // Kembalikan tampilan tombol
                $icon.removeClass('loading-spinner').addClass('bx-calculator');
                $button.prop('disabled', false);
            }
        });
    });

    setTimeout(() => {
        const savedParameterType = '{{ $selectedParameterType ?? '' }}';
        if (savedParameterType) {
             // Trigger change untuk populate
            $paramTypeInherent.val(savedParameterType).trigger('change');
             // Value skala_parameter_id akan diset di dalam event handler .on('change') via savedInherentId
        }
        updateSkalaDampak(true);

        refreshSkalaAndLevelRisiko(false, true); // Inheren
        refreshSkalaAndLevelRisiko(true, true);  // Residual
    }, 500);
});

document.addEventListener('DOMContentLoaded', function() {
    $('.inputmask-rupiah').inputmask({
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
});
</script>
@endpush
