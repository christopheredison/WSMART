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
                    <div class="col-6">
                        <h3>Deskripsi Peristiwa Risiko</h3>
                        <p>{{ $projectRisk->deskripsi_peristiwa_risiko ?: '-' }}</p>
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
                                \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF => 'Kuantitatif', 
                                \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF => 'Kualitatif' 
                            ],  
                            $analisa->kategori_dampak ?? \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF, 
                            ['id' => 'kategoriDampak', 'placeholder' => 'Pilih kategori Dampak', 'class' => 'form-select', 'required' => true]
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
                        {{ Form::text('_risk_limit', $risk_limit, ['class' => 'form-control inputmask-rupiah', 'readonly' => true, 'required' => true, 'id' => 'risk_limit']) }}
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
                        {{ Form::text('nilai_dampak', $analisa->nilai_dampak, ['class' => 'form-control inputmask-rupiah', 'required' => true, 'id' => 'nilai_dampak']) }}
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <label>Nilai Probabilitas (%)</label>
                            <button type="button" class="btn btn-link p-0" id="btnCalculatePoisson" title="Hitung">
                                <i class='bx bx-calculator bx-sm'></i> <!-- Boxicons Eye Icon -->
                            </button>
                        </div>
                        {{ Form::number('nilai_probabilitas', $analisa->nilai_probabilitas, ['class' => 'form-control', 'required' => true, 'step' => 0.01, 'min' => 0, 'max' => 100]) }}
                    </div>
                    <div class="col-md-4">
                        <label>Eksposur Risiko</label>
                        {{ Form::text('eksposur_risiko', '', ['class' => 'form-control inputmask-rupiah', 'disabled' => true, 'required' => true]) }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Skala Dampak</label>
                        {{ Form::select('skala_dampak', 
                            \App\Models\SkalaDampak::get()->mapWithKeys(function($item) {
                                return [$item->tingkat => $item->tingkat . ' - ' . $item->deskripsi];
                            }), 
                            $analisa->skala_dampak, 
                            ['class' => 'form-select', 'placeholder' => 'Pilih Skala Dampak', 'required' => true, 'id' => 'skala_dampak']
                        ) }}
                    </div>
                    <div class="col-md-4">
                        <label>Skala Probabilitas</label>
                        {{ Form::text('skala_probabilitas', '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
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
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Nilai Dampak</label>
                        {{ Form::text('nilai_dampak_residual', $analisa->nilai_dampak_residual, ['class' => 'form-control inputmask-rupiah', 'required' => true, 'id' => 'nilai_dampak_residual']) }}
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <label>Nilai Probabilitas (%)</label>
                            <button type="button" class="btn btn-link p-0" id="btnCalculatePoissonRes" title="Hitung">
                                <i class='bx bx-calculator bx-sm'></i> <!-- Boxicons Eye Icon -->
                            </button>
                        </div>
                        {{ Form::number('nilai_probabilitas_residual', $analisa->nilai_probabilitas_residual, ['class' => 'form-control', 'required' => true, 'step' => 0.01, 'min' => 0, 'max' => 100]) }}
                    </div>
                    <div class="col-md-4">
                        <label>Eksposur Risiko</label>
                        {{ Form::text('eksposur_risiko_residual', $analisa->eksposur_risiko_residual, ['class' => 'form-control inputmask-rupiah', 'disabled' => true, 'required' => true]) }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <label>Skala Dampak Residual</label>
                            <button type="button" class="btn btn-link p-0" id="btnShowKualitatifRes" title="Skala Dampak">
                                <i class='bx bx-show bx-sm'></i> <!-- Boxicons Eye Icon -->
                            </button>
                        </div>
                        {{ Form::select('skala_dampak_residual', 
                                            \App\Models\SkalaDampak::get()->mapWithKeys(function($item) {
                                                return [$item->tingkat => $item->tingkat . ' - ' . $item->deskripsi];
                                            }), 
                                            $analisa->skala_dampak_residual, 
                                            ['class' => 'form-select', 'placeholder' => 'Pilih Skala Dampak Residual', 'required' => true, 'id' => 'skala_dampak_residual']
                                        ) }}
                        <input type="hidden" name="skala_dampak_residual_hidden" id="skala_dampak_residual_hidden" value="{{ $analisa->skala_dampak_residual }}">
                    </div>
                    <div class="col-md-4">
                        <label>Skala Probabilitas</label>
                        {{ Form::text('skala_probabilitas_residual', '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
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
        <div class="row g-2">
            <div class="col-auto order-1">
                <a href="{{ route('projects.risks.index', ['project' => $projectPeriodeList->id]) }}" class="btn btn-outline-secondary">Batal</a>
            </div>
            <div class="col-auto order-3 px-0 px-md-1 d-flex">
                <button type="button" data-action="save" class="btn btn-warning bg-warning ms-auto btn-action">Simpan dan Keluar</button>
            </div>
            <div class="col-auto order-3 px-0 px-md-1 d-flex">
                <button type="button" data-action="savenext" class="btn btn-primary ms-auto btn-action">Simpan dan Lanjut Ke Rencana Perlakuan</button>
            </div>
        </div>
    </div>
    @include('project-risk._modal_kualitatif')
    @include('project-risk._modal_kualitatif_res')
@endsection
@push('styles')
<style>
    #btnShowKualitatif i {
        font-size: 1.2rem; /* Ukuran ikon */
        color: #007bff; /* Warna biru */
        cursor: pointer;
    }

    #btnShowKualitatif:hover i {
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
function getSkalaProbabilitasByValue(value) {
    const skalaProbabilitases = @json($skalaProbabilitas);
    for (index in skalaProbabilitases) {
        skalaProbabilitas = skalaProbabilitases[index];
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

        //console.log(nilaiDampak);
        
        if (isNaN(nilaiDampak) || isNaN(nilaiProbabilitas)) {
            domEksposurRisiko.val('');
        } else {
            domEksposurRisiko.val(nilaiDampak * nilaiProbabilitas /100);
        }
    } else if (kategoriDampak === "{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF }}") {
        const skalaDampak = parseFloat($('[name="skala_dampak' + (residual ? '_residual' : '') + '"]').val());
        domEksposurRisiko.val('');
        const riskLimit = parseFloat($('[name="_risk_limit"]').val());
        if (isNaN(skalaDampak) || isNaN(nilaiProbabilitas) || isNaN(riskLimit)) {
            domEksposurRisiko.val('');
        } else {
            domEksposurRisiko.val(skalaDampak * (1/100) * nilaiProbabilitas/100 * riskTolerance);
        }
    }
}

function refreshSkalaAndLevelRisiko(residual = false) {
    const riskMaps = @json($riskMaps);
    const domSkalaRisiko = $('[name="skala_risiko' + (residual ? '_residual' : '') + '"]');
    const domLevelRisiko = $('[name="level_risiko' + (residual ? '_residual' : '') + '"]');
    const skalaDampak = $('[name="skala_dampak' + (residual ? '_residual' : '') + '"]').val();
    const skalaProbabilitas = $('[name="skala_probabilitas' + (residual ? '_residual' : '') + '"]').data('tingkat');

    if (!skalaDampak || !skalaProbabilitas) {
        return; 
    }

    const riskMap = riskMaps[skalaDampak + '-' + skalaProbabilitas];
    if  (riskMap) {
        domSkalaRisiko.val(riskMap.nilai_risiko);
        domLevelRisiko.val(riskMap.level_risiko);
    } else {
        domSkalaRisiko.val('');
        domLevelRisiko.val('');
    }
}

$(document).ready(function() {
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

    $('[name="nilai_dampak"],[name="skala_dampak"]').on('input change', function() {
        //console.log('change nilai dampak');
        refreshEksposureRisiko();
        refreshSkalaAndLevelRisiko();
    }).change();

    $('[name="nilai_dampak_residual"],[name="skala_dampak_residual"]').on('input change', function() {
        refreshEksposureRisiko(true);
        refreshSkalaAndLevelRisiko(true);
    }).change();

    $('[name="skala_probabilitas"]').on('change', function() {
        refreshSkalaAndLevelRisiko();
    }).change();

    $('[name="skala_probabilitas_residual"]').on('change', function() {
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

    $('#skala_dampak').on('change', function () {
        if ($('#kategoriDampak').val() !== '{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF }}') {
            return;
        }

        const selectedSkalaDampak = parseInt($(this).val());
        const $skalaDampakResidual = $('#skala_dampak_residual');
        const currentValueResidual = parseInt($skalaDampakResidual.val());

        if (isNaN(selectedSkalaDampak)) {
            $skalaDampakResidual.find('option').prop('disabled', false);
            return;
        }

        if (currentValueResidual > selectedSkalaDampak) {
            $skalaDampakResidual.val('').trigger('change');
        }
        
        $skalaDampakResidual.find('option').each(function () {
            if (!$(this).val()) return;

            const optionValue = parseInt($(this).val());
            $(this).prop('disabled', optionValue > selectedSkalaDampak);
        });
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

    function updateSkalaDampak() {
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

            // Set nilai skala dampak dan buat readonly
            $skalaDampak.val(skala).prop('disabled', true);
            $skalaDampakResidual.val(skalaResidual).prop('disabled', true);
        } else {
            // Jika kategori dampak Kualitatif, skala dampak bisa dipilih manual
            $skalaDampak.prop('disabled', false);
            $skalaDampakResidual.prop('disabled', false);
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

    $('#skala_dampak').change(function () {
        var selectedSkalaDampak = parseInt($(this).val()); // Ambil nilai skala dampak yang dipilih
        
        // Reset skala dampak residual ke placeholder
        $('#skala_dampak_residual').val('').change();
        
        // Nonaktifkan opsi skala dampak residual yang lebih besar dari skala dampak
        $('#skala_dampak_residual option').each(function () {
            var optionValue = parseInt($(this).val());
            if (optionValue > selectedSkalaDampak) {
                $(this).prop('disabled', true); // Nonaktifkan opsi yang lebih besar
            } else {
                $(this).prop('disabled', false); // Aktifkan opsi yang sesuai
            }
        });
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
                confirmButtonText: 'Mengerti'
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
                confirmButtonText: 'Mengerti'
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