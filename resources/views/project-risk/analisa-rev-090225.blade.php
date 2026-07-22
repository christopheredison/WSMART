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
                        {{ Form::select('kategori_dampak', [\App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF => 'Kuantitatif', \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF => 'Kualitatif'],  $analisa->kategori_dampak, ['id' => 'kategoriDampak','placeholder' => 'Pilih kategori Dampak', 'class' => 'form-select', 'required' => true]) }}
                    </div>
                    <div class="col-md-4" id="divAreaDampak">
                        <div class="d-flex align-items-center">
                            <label for="area_dampak" class="me-2 mb-0">Area Dampak</label>
                            <button type="button" class="btn btn-link p-0" id="btnShowKualitatif" title="Lihat Risiko Kualitatif">
                                <i class='bx bx-show bx-sm'></i> <!-- Boxicons Eye Icon -->
                            </button>
                        </div>
                        {{ Form::select('area_dampak', \App\Models\AreaDampak::project()->get()->pluck('title', 'id'),  $analisa->area_dampak, ['placeholder' => 'Pilih area Dampak', 'class' => 'form-select', 'required' => true, 'id' => 'area_dampak']) }}
                    </div>
                    <div class="col-md-4">
                        <label>Risk Limit</label>
                        {{ Form::text('_risk_limit', $projectRisk->project->risk_limit, ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label id="labelDeskripsiDampak">Deskripsi Dampak</label>
                        {{ Form::textarea('deskripsi_dampak', $analisa->deskripsi_dampak, ['class' => 'form-control', 'required' => true, 'rows' => 5]) }}
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
                        {{ Form::text('nilai_dampak', $analisa->nilai_dampak, ['class' => 'form-control inputmask-rupiah', 'required' => true]) }}
                    </div>
                    <div class="col-md-4">
                        <label>Nilai Probabilitas (%)</label>
                        {{ Form::number('nilai_probabilitas', $analisa->nilai_probabilitas, ['class' => 'form-control', 'required' => true, 'step' => 0.01, 'min' => 0, 'max' => 100]) }}
                    </div>
                    <div class="col-md-4">
                        <label>Eksposur Risiko</label>
                        {{ Form::text('eksposur_risiko', '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label>Skala Dampak</label>
                        {{ Form::select('skala_dampak', \App\Models\SkalaDampak::pluck('deskripsi', 'tingkat'), $analisa->skala_dampak, ['class' => 'form-select', 'placeholder' => 'Pilih Skala Dampak', 'required' => true, 'id' => 'skala_dampak']) }}
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
                        {{ Form::text('nilai_dampak_residual', $analisa->nilai_dampak_residual, ['class' => 'form-control', 'required' => true]) }}
                    </div>
                    <div class="col-md-4">
                        <label>Nilai Probabilitas (%)</label>
                        {{ Form::number('nilai_probabilitas_residual', $analisa->nilai_probabilitas_residual, ['class' => 'form-control', 'required' => true, 'step' => 0.01, 'min' => 0, 'max' => 100]) }}
                    </div>
                    <div class="col-md-4">
                        <label>Eksposur Risiko</label>
                        {{ Form::text('eksposur_risiko_residual', $analisa->eksposur_risiko_residual, ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label>Skala Dampak</label>
                        {{ Form::select('skala_dampak_residual', \App\Models\SkalaDampak::pluck('deskripsi', 'tingkat'), $analisa->skala_dampak_residual, ['class' => 'form-select', 'placeholder' => 'Pilih Skala Dampak', 'required' => true]) }}
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
            </div>
        </div>
    </form>

    <div class="col-12">
        <div class="row g-2">
            <div class="col-auto order-1">
                <a href="" class="btn btn-outline-secondary">Batal</a>
            </div>
            <div class="col-auto order-3 px-0 px-md-1 d-flex">
                <button type="button" data-action="save" class="btn btn-primary ms-auto btn-action">Simpan</button>
            </div>
        </div>
    </div>
    @include('project-risk._modal_kualitatif')
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

    if (kategoriDampak === "{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF }}") {
        const nilaiDampak = parseFloat($('[name="nilai_dampak' + (residual ? '_residual' : '') + '"]').val());
        if (isNaN(nilaiDampak) || isNaN(nilaiProbabilitas)) {
            domEksposurRisiko.val('');
        } else {
            domEksposurRisiko.val(nilaiDampak * nilaiProbabilitas);
        }
    } else if (kategoriDampak === "{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF }}") {
        const skalaDampak = parseFloat($('[name="skala_dampak' + (residual ? '_residual' : '') + '"]').val());
        domEksposurRisiko.val('');
        const riskLimit = parseFloat($('[name="_risk_limit"]').val());
        if (isNaN(skalaDampak) || isNaN(nilaiProbabilitas) || isNaN(riskLimit)) {
            domEksposurRisiko.val('');
        } else {
            domEksposurRisiko.val(skalaDampak * (1/100) * nilaiProbabilitas * riskLimit);
        }
    }
}

function refreshSkalaAndLevelRisiko(residual = false) {
    const riskMaps = @json($riskMaps);
    const domSkalaRisiko = $('[name="skala_risiko' + (residual ? '_residual' : '') + '"]');
    const domLevelRisiko = $('[name="level_risiko' + (residual ? '_residual' : '') + '"]');
    const skalaDampak = $('[name="skala_dampak' + (residual ? '_residual' : '') + '"]').val();
    const skalaProbabilitas = $('[name="skala_probabilitas' + (residual ? '_residual' : '') + '"]').data('tingkat');

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
    }).change();

    $('[name="nilai_probabilitas"]').on('change', function() {
        const value = $(this).val();
        const skalaProbabilitas = getSkalaProbabilitasByValue(value);

        if (!skalaProbabilitas || value === '') {
            $('[name="skala_probabilitas"]').val('').change();
            return;
        }
        $('[name="skala_probabilitas"]').val('(' + skalaProbabilitas.tingkat + ') ' + skalaProbabilitas.skala).data('tingkat', skalaProbabilitas.tingkat).change();
        refreshEksposureRisiko();
    }).change();

    $('[name="nilai_probabilitas_residual"]').on('change', function() {
        const value = $(this).val();
        const skalaProbabilitas = getSkalaProbabilitasByValue(value);

        if (!skalaProbabilitas || value === '') {
            $('[name="skala_probabilitas_residual"]').val('').change();
            return;
        }
        $('[name="skala_probabilitas_residual"]').val('(' + skalaProbabilitas.tingkat + ') ' + skalaProbabilitas.skala).data('tingkat', skalaProbabilitas.tingkat).change();
        refreshEksposureRisiko(true);
    }).change();

    $('[name="nilai_dampak"],[name="skala_dampak"]').on('change', function() {
        refreshEksposureRisiko();
        refreshSkalaAndLevelRisiko();
    }).change();

    $('[name="nilai_dampak_residual"],[name="skala_dampak_residual"]').on('change', function() {
        refreshEksposureRisiko(true);
        refreshSkalaAndLevelRisiko(true);
    }).change();

    $('[name="skala_probabilitas"]').on('change', function() {
        refreshSkalaAndLevelRisiko();
    }).change();

    $('[name="skala_probabilitas_residual"]').on('change', function() {
        refreshSkalaAndLevelRisiko(true);
    }).change();

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

    // Event listener untuk dropdown kategori dampak
    $('#kategoriDampak').on('change', function () {
        const selectedValue = $(this).val(); // Ambil nilai yang dipilih

        if (selectedValue == '{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF }}') {
            $('#labelDeskripsiDampak').text('Asumsi Perhitungan Dampak');
            $('#divAreaDampak').hide();
            $('#area_dampak').prop('required', false);
        } else if (selectedValue == '{{ \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUALITATIF }}') {
            $('#labelDeskripsiDampak').text('Deskripsi Dampak');
            $('#divAreaDampak').show();
            $('#area_dampak').prop('required', true);
        } else {
            $('#labelDeskripsiDampak').text('Deskripsi Dampak'); // Default
            $('#divAreaDampak').show();
            $('#area_dampak').prop('required', true);
        }
    });

    // Trigger perubahan awal untuk set label saat halaman dimuat
    $('#kategoriDampak').trigger('change');

    $('#btnShowKualitatif').on('click', function () {
        $('#modalKualitatif').modal('show'); // Tampilkan modal
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
    });
});
</script>
@endpush