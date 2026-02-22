@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
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
                        <p>{{ $identifikasiRisiko->deskripsi_peristiwa_risiko ?: '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h3>Dampak Risiko</h3>
                        <ul>
                            @forelse ($identifikasiRisiko->dampakRisikos as $dampak)
                                <li>{{ $dampak->dampak_risiko }}</li>
                            @empty
                                <li class="text-muted italic">Tidak ada dampak risiko</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h3>Penyebab Risiko</h3>
                        <ul>
                            @foreach ($identifikasiRisiko->penyebabRisiko as $penyebab)
                                <li>{{ $penyebab->penyebab_risiko }}</li>
                            @endforeach
                        </ul>
                        @if ($identifikasiRisiko->penyebabRisiko->isEmpty())
                            <em class="text-muted italic">Tidak ada penyebab risiko</em>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Dampak Risiko -->
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
                        <label>Kategori Dampak <span class="text-danger">*</span></label>
                        {{ Form::select('kategori_dampak',
                            [
                                'Kuantitatif' => 'Kuantitatif',
                                'Kualitatif' => 'Kualitatif'
                            ],
                            $analisa->kategori_dampak ?? 'Kuantitatif',
                            ['id' => 'kategoriDampak', 'placeholder' => 'Pilih kategori Dampak', 'class' => 'form-select', 'required' => true]
                        ) }}
                    </div>
                    <div class="col-md-4" id="divAreaDampak">
                        <div class="d-flex align-items-center">
                            <label for="area_dampak" class="me-2 mb-0">Area Dampak <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link p-0" id="btnShowKualitatif" title="Lihat Risiko Kualitatif">
                                <i class='bx bx-show bx-sm'></i> <!-- Boxicons Eye Icon -->
                            </button>
                        </div>
                        {{ Form::select('area_dampak', \App\Models\AreaDampak::get()->pluck('title', 'id'),  $analisa->area_dampak, ['placeholder' => 'Pilih area Dampak', 'class' => 'form-select', 'required' => true, 'id' => 'area_dampak']) }}
                    </div>
                    <div class="col-md-4" id="div_risk_limit">
                        <label>Risk Limit</label>
                        {{ Form::text('_risk_limit', $risk_limit, ['class' => 'form-control inputmask-rupiah', 'readonly' => true, 'required' => true, 'id' => 'risk_limit', 'autocomplete' => 'off']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-5" data-content="analisa" data-type="inherent" data-level="1">
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
                        <label>Nilai Dampak <span class="text-danger">*</span></label>
                        {{ Form::text('nilai_dampak', $analisa->nilai_dampak, ['class' => 'form-control inputmask-rupiah', 'required' => true, 'id' => 'nilai_dampak', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <label>Nilai Probabilitas (%) <span class="text-danger">*</span></label>
                            <!-- <button type="button" class="btn btn-link p-0" id="btnCalculatePoisson" title="Hitung">
                                <i class='bx bx-calculator bx-sm'></i>
                            </button> -->
                        </div>
                        {{ Form::number('nilai_probabilitas', $analisa->nilai_probabilitas, ['class' => 'form-control', 'required' => true, 'step' => 0.01, 'min' => 0, 'max' => 100, 'onkeypress' => 'return isNumberKey(event)', 'id' => 'nilai_probabilitas']) }}
                    </div>
                    <div class="col-md-4">
                        <label>Eksposur Risiko</label>
                        {{ Form::text('eksposur_risiko', '', ['class' => 'form-control inputmask-rupiah', 'disabled' => true, 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Skala Dampak <span class="text-danger">*</span></label>
                        {{ Form::select('skala_dampak',
                            \App\Models\SkalaDampak::get()->mapWithKeys(function($item) {
                                return [$item->tingkat => $item->tingkat . ' - ' . $item->deskripsi];
                            }),
                            $analisa->skala_dampak,
                            ['class' => 'form-select', 'placeholder' => 'Pilih Skala Dampak', 'required' => true, 'id' => 'skala_dampak']
                        ) }}
                        <input type="hidden" name="skala_dampak_hidden" id="skala_dampak_hidden" value="{{ $analisa->skala_dampak }}">
                    </div>
                    <div class="col-md-4">
                        <label>Skala Probabilitas <span class="text-danger">*</span></label>
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
                        <label id="labelDeskripsiDampak">Deskripsi Dampak <span class="text-danger">*</span></label>
                        {{ Form::textarea('deskripsi_dampak', $analisa->deskripsi_dampak, ['class' => 'form-control', 'rows' => 5]) }}
                        <input type="hidden" name="risk_tolerance" id="risk_tolerance" value="{{ $risk_tolerance }}" />
                    </div>
                </div>
                <div class="row" id="divAsumsiDampak">
                    <div class="col-md-12">
                        <label>Asumsi Perhitungan Dampak & Probabilitas Inherent <span class="text-danger">*</span></label>
                        {{ Form::textarea('asumsi_perhitungan_dampak', $analisa->asumsi_perhitungan_dampak, ['class' => 'form-control', 'rows' => 5]) }}
                    </div>
                </div>
            </div>
        </div>

        @for ($i = 1; $i <= 4; $i++)
        <!-- Q1 -->
        <div class="card mb-5" data-content="analisa" data-type="residual" data-level="{{ $i + 1 }}">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">{{ $i + 3 }}</span>
                    </span>
                    <span class="h3 mb-0">Pengukuran Risiko Residual Q{{ $i }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Nilai Dampak <span class="text-danger">*</span></label>
                        {{ Form::text('nilai_dampak_residual_q' . $i, $analisa->{'nilai_dampak_residual_q' . $i} ?? '', ['class' => 'form-control inputmask-rupiah', 'required' => true, 'id' => 'nilai_dampak_residual_q' . $i, 'autocomplete' => 'off']) }}
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <label>Nilai Probabilitas (%) <span class="text-danger">*</span></label>
                        </div>
                        {{ Form::number('nilai_probabilitas_residual_q' . $i, $analisa->{'nilai_probabilitas_residual_q' . $i} ?? '', ['class' => 'form-control', 'required' => true, 'step' => 0.01, 'min' => 0, 'max' => 100, 'onkeypress' => 'return isNumberKey(event)', 'id' => 'nilai_probabilitas_residual_q' . $i]) }}
                    </div>
                    <div class="col-md-4">
                        <label>Eksposur Risiko</label>
                        {{ Form::text('eksposur_risiko_residual_q' . $i, $analisa->{'eksposur_risiko_residual_q' . $i} ?? '', ['class' => 'form-control inputmask-rupiah', 'disabled' => true, 'required' => true, 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <label>Skala Dampak Residual <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link p-0 btnShowKualitatifRes" id="btnShowKualitatifResQ{{ $i }}" title="Skala Dampak">
                                <i class='bx bx-show bx-sm'></i> <!-- Boxicons Eye Icon -->
                            </button>
                        </div>
                        {{ Form::select('skala_dampak_residual_q' . $i,
                                            \App\Models\SkalaDampak::get()->mapWithKeys(function($item) {
                                                return [$item->tingkat => $item->tingkat . ' - ' . $item->deskripsi];
                                            }),
                                            $analisa->{'skala_dampak_residual_q' . $i} ?? '',
                                            ['class' => 'form-select', 'placeholder' => 'Pilih Skala Dampak Residual Q' . $i, 'required' => true, 'id' => 'skala_dampak_residual_q' . $i]
                                        ) }}
                        <input type="hidden" name="skala_dampak_residual_q{{ $i }}_hidden" id="skala_dampak_residual_q{{ $i }}_hidden" value="{{ $analisa->{'skala_dampak_residual_q' . $i} ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label>Skala Probabilitas <span class="text-danger">*</span></label>
                        {{ Form::text('skala_probabilitas_residual_q' . $i, '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                    <div class="col-md-2">
                        <label>Skala Risiko</label>
                        {{ Form::text('skala_risiko_residual_q' . $i, '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                    <div class="col-md-2">
                        <label>Level Risiko</label>
                        {{ Form::text('level_risiko_residual_q' . $i, '', ['class' => 'form-control', 'disabled' => true, 'required' => true]) }}
                    </div>
                </div>
                <div class="row" id="divDeskripsiDampakResidualQ{{ $i }}">
                    <div class="col-md-12">
                        <label>Deskripsi Dampak Residual Q{{ $i }} <span class="text-danger">*</span></label>
                        {{ Form::textarea('deskripsi_dampak_residual_q' . $i, $analisa->{'deskripsi_dampak_residual_q' . $i} ?? '', ['class' => 'form-control', 'rows' => 5]) }}
                    </div>
                </div>
                <div class="row" id="divAsumsiDampakResidualQ{{ $i }}">
                    <div class="col-md-12">
                        <label>Asumsi Perhitungan Dampak & Probabilitas Residual Q{{ $i }} <span class="text-danger">*</span></label>
                        {{ Form::textarea('asumsi_perhitungan_dampak_residual_q' . $i, $analisa->{'asumsi_perhitungan_dampak_residual_q' . $i} ?? '', ['class' => 'form-control', 'rows' => 5]) }}
                    </div>
                </div>
            </div>
        </div>
        @endfor
    </form>

    <div class="col-12">
        <div class="row g-2 justify-content-between">
            <div class="col-auto">
                <a href="{{ route('risk-register-ap.edit', $identifikasiRisiko->id) }}" class="btn btn-secondary me-2">
                    <span class="bx bx-chevron-left" style="line-height: 0.8;"></span> Kembali ke Identifikasi
                </a>
                <a href="{{ route('risk-register-ap.index', ['pid' => $identifikasiRisiko->periode_id]) }}" class="btn btn-outline-secondary">Batal</a>
            </div>

            <div class="col-auto">
                <button type="button" data-action="save" class="btn btn-warning bg-warning me-2 btn-action">Simpan dan Keluar</button>
                <button type="button" data-action="savenext" class="btn btn-primary btn-action">Simpan dan Lanjut Ke Rencana Perlakuan</button>
            </div>
        </div>
    </div>
    @include('risk-register-unit._modal_kualitatif')
    @include('risk-register-unit._modal_kualitatif_res')
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

    /* Styling untuk tombol kualitatif residual Q1-Q4 */
    #btnShowKualitatifResQ1 i,
    #btnShowKualitatifResQ2 i,
    #btnShowKualitatifResQ3 i,
    #btnShowKualitatifResQ4 i {
        font-size: 1.2rem; /* Ukuran ikon */
        color: #007bff; /* Warna biru */
        cursor: pointer;
    }

    #btnShowKualitatifResQ1:hover i,
    #btnShowKualitatifResQ2:hover i,
    #btnShowKualitatifResQ3:hover i,
    #btnShowKualitatifResQ4:hover i {
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

function refreshEksposureRisiko(residual = false, quarter = null) {

  let suffix = '';
  if (residual) {
    if (typeof quarter === 'number' && quarter >= 1 && quarter <= 4) {
      suffix = `_residual_q${quarter}`;
    } else {
      suffix = `_residual`;
    }
  }

  const domEksposurRisiko = $(`[name="eksposur_risiko${suffix}"]`);
  const kategoriDampak = $('[name="kategori_dampak"]').val();
  const nilaiProbabilitas = parseFloat(
    $(`[name="nilai_probabilitas${suffix}"]`).val()
  );

  const riskTolerance = parseFloat($('[name="risk_tolerance"]').val());

  if (kategoriDampak === "Kuantitatif") {

    const nilaiDampak = parseFloat(
      $(`[name="nilai_dampak${suffix}"]`).val()
    );

    if (isNaN(nilaiDampak) || isNaN(nilaiProbabilitas)) {
      domEksposurRisiko.val('');
    } else {
      // Rumus: (nilaiDampak * nilaiProbabilitas) / 100
      const eksposurVal = (nilaiDampak * nilaiProbabilitas) / 100;
      domEksposurRisiko.val(eksposurVal);
    }

  } else if (kategoriDampak === "Kualitatif") {

    const skalaDampak = parseFloat(
      $(`[name="skala_dampak${suffix}"]`).val()
    );
    // Risk limit kita asumsikan tidak per‐quarter → name="_risk_limit"
    const riskLimit = parseFloat($('[name="_risk_limit"]').val());

    if (isNaN(skalaDampak) || isNaN(nilaiProbabilitas) || isNaN(riskLimit)) {
      domEksposurRisiko.val('');
    } else {
      // Rumus: skalaDampak * (1/100) * (nilaiProbabilitas / 100) * riskTolerance
      const eksposurVal = skalaDampak * (1 / 100) * (nilaiProbabilitas / 100) * riskTolerance;
      domEksposurRisiko.val(eksposurVal);
    }

  } else {
    // Jika kategori_dampak kosong atau tidak sesuai
    domEksposurRisiko.val('');
  }
}

function refreshSkalaAndLevelRisiko(residual = false, quarter = null) {
  let suffix = '';
  if (residual) {
    if (typeof quarter === 'number' && quarter >= 1 && quarter <= 4) {
      suffix = `_residual_q${quarter}`;
    } else {
      suffix = `_residual`;
    }
  }
  // Jika residual === false, suffix tetap ""

  const riskMaps = @json($riskMaps);
  const domSkalaRisiko = $(`[name="skala_risiko${suffix}"]`);
  const domLevelRisiko = $(`[name="level_risiko${suffix}"]`);

  const skalaDampak = $(`[name="skala_dampak${suffix}"]`).val();
  const skalaProbabilitas = parseFloat(
    $(`[name="skala_probabilitas${suffix}"]`).data('tingkat')
  );

  const mapKey = `${skalaDampak}-${skalaProbabilitas}`;
  const riskMap = riskMaps[mapKey];

  if (riskMap) {
    domSkalaRisiko.val(riskMap.nilai_risiko);
    domLevelRisiko.val(riskMap.level_risiko);
  } else {
    domSkalaRisiko.val('');
    domLevelRisiko.val('');
  }
}

function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    // Hanya izinkan angka (0-9) dan titik (.)
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

$(document).ready(function() {
    const autoCalculate = @json($autoCalculate);

    function updateSkalaDampak() {
        const kategoriDampak = $('[name="kategori_dampak"]').val();
        if (kategoriDampak === "Kualitatif") {
            return;
        }
        // Hapus semua karakter non‐angka, lalu parseFloat, default 0
        const riskLimit      = parseFloat($('#risk_limit').val().replace(/[^0-9.-]+/g, '')) || 0;
        const suffixes = [
        '',
        '_residual',
        '_residual_q1',
        '_residual_q2',
        '_residual_q3',
        '_residual_q4'
        ];
        suffixes.forEach(function(suffix) {
            const $nilaiDampakInput = $(`[name="nilai_dampak${suffix}"]`);
            if ($nilaiDampakInput.length === 0) {
                // Jika elemen tidak ada di DOM, skip
                return;
            }
            // Ambil nilai, bersihkan non‐angka, parseFloat (default 0 jika NaN)
            const nilaiDampak = parseFloat(
                $nilaiDampakInput.val().replace(/[^0-9.-]+/g, '')
            ) || 0;


            const $skalaDampak       = $(`[name="skala_dampak${suffix}"]`);
            const $skalaDampakHidden = $(`#skala_dampak${suffix}_hidden`);

            // Jika dropdown skala_dampak (suffix) tidak ada di halaman, skip
            if ($skalaDampak.length === 0 || $skalaDampakHidden.length === 0) {
                return;
            }

            let skala = $skalaDampak.val() || '';
            // Jika Anda ingin default 5 ketika riskLimit = 0 dan autoCalculate = true:
            if (typeof autoCalculate !== 'undefined' && autoCalculate && riskLimit === 0) {
                skala = 5;
            }

            if (riskLimit > 0) {
                // Hitung persentase: (nilaiDampak / riskLimit) * 100
                const percentage = (nilaiDampak / riskLimit) * 100;
                // Panggil fungsi calculateSkalaDampak → harus mengembalikan 1..5
                skala = calculateSkalaDampak(percentage);
            }

            $skalaDampakHidden.val($skalaDampak.val());
            $skalaDampak.val(skala).change();
        });
    }

    function calculateSkalaDampak(percentage) {
        if (percentage <= 20) return 1; // Low
        if (percentage > 20 && percentage <= 40) return 2; // Low To Moderate
        if (percentage > 40 && percentage <= 60) return 3; // Moderate
        if (percentage > 60 && percentage <= 80) return 4; // Moderate To High
        return 5; // High
    }

    $('[name="kategori_dampak"]').on('change', function() {
        const value = $(this).val();
        if (value === "Kualitatif") {
            // $('#risk_limit').data('oldValue', $('#risk_limit').val()).val(0);
            $('#risk_limit').data('oldValue', $('#risk_limit').val());
            $('[name="nilai_dampak"]').prop('readonly', true).data('oldValue', $('[name="nilai_dampak"]').val()).val(0);
            if ($('[name="skala_dampak"]').data('oldValue')) {
                $('[name="skala_dampak"]').prop('disabled', false).val($('[name="skala_dampak"]').data('oldValue'));
            }
            for (let i = 1; i <= 4; i++) {
                $(`[name="nilai_dampak_residual_q${i}"]`).prop('readonly', true).data('oldValue', $(`[name="nilai_dampak_residual_q${i}"]`).val()).val(0);
                if ($(`[name="skala_dampak_residual_q${i}"]`).data('oldValue')) {
                    $(`[name="skala_dampak_residual_q${i}"]`).prop('disabled', false).val($(`[name="skala_dampak_residual_q${i}"]`).data('oldValue'));
                }
            }
        } else {
            let oldValueRiskLimit = $('#risk_limit').data('oldValue');
            if (oldValueRiskLimit) {
                $('#risk_limit').val(oldValueRiskLimit);
            }
            $('[name="nilai_dampak"]').prop('readonly', false);
            if (typeof $('[name="nilai_dampak"]').data('oldValue') !== 'undefined') {
                $('[name="nilai_dampak"]').val($('[name="nilai_dampak"]').data('oldValue'));
            }
            $('[name="skala_dampak"]').prop('disabled', true).data('oldValue', $('[name="skala_dampak"]').val());
            for (let i = 1; i <= 4; i++) {
                $(`[name="nilai_dampak_residual_q${i}"]`).prop('readonly', false);
                if (typeof $(`[name="nilai_dampak_residual_q${i}"]`).data('oldValue') !== 'undefined') {
                    $(`[name="nilai_dampak_residual_q${i}"]`).val($(`[name="nilai_dampak_residual_q${i}"]`).data('oldValue'));
                }
                $(`[name="skala_dampak_residual_q${i}"]`).prop('disabled', true).data('oldValue', $(`[name="skala_dampak_residual_q${i}"]`).val());
            }
        }
        updateSkalaDampak();
        refreshEksposureRisiko();
        refreshEksposureRisiko(true);
    }).change();

    $('#kategoriDampak').on('change', function () {
        const selectedValue = $(this).val(); // Ambil nilai yang dipilih
        console.log("Change 2 : " + selectedValue);
        if (selectedValue == 'Kuantitatif') {
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
           //$('#asumsi_perhitungan_dampak_residual').prop('required', true);

            // Sembunyikan deskripsi dampak residual Q1-Q4
            for (let i = 1; i <= 4; i++) {
                // Sembunyikan deskripsi dampak residual
                $(`#divDeskripsiDampakResidualQ${i}`).hide();

                // Tampilkan asumsi dampak residual
                $(`#divAsumsiDampakResidualQ${i}`).show();

                // Set required untuk asumsi perhitungan
                //$(`#asumsi_perhitungan_dampak_residual_q${i}`).prop('required', true);

                $(`#btnShowKualitatifResQ${i}`).hide();
            }


        } else if (selectedValue == 'Kualitatif') {
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

            // Menggunakan loop untuk quarters 1-4
            for (let i = 1; i <= 4; i++) {
                // Tampilkan deskripsi dampak residual
                $(`#divDeskripsiDampakResidualQ${i}`).show();

                // Sembunyikan asumsi dampak residual
                $(`#divAsumsiDampakResidualQ${i}`).hide();

                // Set not required untuk asumsi perhitungan
                $(`#asumsi_perhitungan_dampak_residual_q${i}`).prop('required', false);

                $(`#btnShowKualitatifResQ${i}`).show();
            }
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

            // Menggunakan loop untuk quarters 1-4
            for (let i = 1; i <= 4; i++) {
                // Tampilkan deskripsi dampak residual
                $(`#divDeskripsiDampakResidualQ${i}`).show();

                // Sembunyikan asumsi dampak residual
                $(`#divAsumsiDampakResidualQ${i}`).hide();

                // Set not required untuk asumsi perhitungan
                $(`#asumsi_perhitungan_dampak_residual_q${i}`).prop('required', false);

                $(`#btnShowKualitatifResQ${i}`).show();
            }
        }
    });

    // Event listener untuk perubahan nilai dampak, nilai dampak residual, kategori dampak, dan risk limit
    $('[name="nilai_dampak"], [name="nilai_dampak_residual_q1"], [name="nilai_dampak_residual_q2"], [name="nilai_dampak_residual_q3"], [name="nilai_dampak_residual_q4"], [name="kategori_dampak"]').on('change', function() {
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

    $('[name="nilai_dampak"],[name="skala_dampak"]').on('change', function() {
        //console.log('change nilai dampak');
        refreshEksposureRisiko();
        refreshSkalaAndLevelRisiko();

    }).change();

    // Add event listeners for all quarter residual inputs
    for (let i = 1; i <= 4; i++) {
        $(`[name="nilai_dampak_residual_q${i}"],[name="skala_dampak_residual_q${i}"]`).on('change', function() {
            refreshEksposureRisiko(true, i);
            refreshSkalaAndLevelRisiko(true, i);
        }).change();

        $(`[name="nilai_probabilitas_residual_q${i}"]`).on('change', function() {
            const value = $(this).val();
            const skalaProbabilitas = getSkalaProbabilitasByValue(value);

            if (!skalaProbabilitas || value === '') {
                $(`[name="skala_probabilitas_residual_q${i}"]`).val('').change();
                return;
            }
            $(`[name="skala_probabilitas_residual_q${i}"]`).val('(' + skalaProbabilitas.tingkat + ') ' + skalaProbabilitas.skala).data('tingkat', skalaProbabilitas.tingkat).change();
            refreshEksposureRisiko(true, i);
        }).change();

        $(`[name="skala_probabilitas_residual_q${i}"]`).on('change', function() {
            refreshSkalaAndLevelRisiko(true, i);
        }).change();

        // Add input validation for residual quarter values
        $(`#nilai_dampak_residual_q${i}`).on('blur', function () {
            var nilaiDampak = parseRupiahToNumber($('#nilai_dampak').val());
            var nilaiResidual = parseRupiahToNumber($(this).val());

            // For Q1, validate against inherent value
            if (i === 1) {
                if (nilaiResidual > nilaiDampak) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: 'Nilai Dampak Residual Q1 tidak boleh lebih besar dari Nilai Dampak Inheren!',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    $(this).val($('#nilai_dampak').val()).change();
                    return;
                }
            }

            // For other quarters, validate against previous quarter
            if (i > 1) {
                var prevQuarterValue = parseRupiahToNumber($(`#nilai_dampak_residual_q${i-1}`).val());
                if (nilaiResidual > prevQuarterValue) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: `Nilai Dampak Residual Q${i} tidak boleh lebih besar dari Q${i-1}!`,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    $(this).val($(`#nilai_dampak_residual_q${i-1}`).val()).change();
                }
            }
        });

        // Add validation for probability values
        $(`#nilai_probabilitas_residual_q${i}`).on('blur', function() {
            var currentValue = parseFloat($(this).val()) || 0;
            var inherentProb = parseFloat($('#nilai_probabilitas').val()) || 0;

            if (currentValue < 0) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: `Nilai Probabilitas Q${i} tidak boleh kurang dari 0!`,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $(this).val(0).change();
                return;
            } else if (currentValue > 100) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: `Nilai Probabilitas Q${i} tidak boleh lebih dari 100!`,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                $(this).val(100).change();
                currentValue = 100;
            }

            // For Q1, validate against inherent probability
            if (i === 1) {
                if (currentValue > inherentProb) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: 'Nilai Probabilitas Q1 tidak boleh lebih besar dari Nilai Probabilitas Inheren!',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    $(this).val(inherentProb).change();
                    return;
                }
            }

            // For other quarters, validate against previous quarter
            if (i > 1) {
                var prevQuarterValue = parseFloat($(`#nilai_probabilitas_residual_q${i-1}`).val()) || 0;
                if (currentValue > prevQuarterValue) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: `Nilai Probabilitas Q${i} tidak boleh lebih besar dari Q${i-1}!`,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    $(this).val(prevQuarterValue).change();
                }
            }
        });

        // Add validation for impact scale values
        $(`#skala_dampak_residual_q${i}`).on('change', function() {
            var currentValue = parseInt($(this).val());
            var inherentScale = parseInt($('#skala_dampak').val());

            // For Q1, validate against inherent scale
            if (i === 1) {
                if (currentValue > inherentScale) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: 'Skala Dampak Q1 tidak boleh lebih besar dari Skala Dampak Inheren!',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    $(this).val(inherentScale).change();
                    return;
                }
            }

            // For other quarters, validate against previous quarter
            if (i > 1) {
                var prevQuarterValue = parseInt($(`#skala_dampak_residual_q${i-1}`).val());
                if (currentValue > prevQuarterValue) {
                    Swal.fire({
                        title: 'Peringatan!',
                        text: `Skala Dampak Q${i} tidak boleh lebih besar dari Q${i-1}!`,
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    $(this).val(prevQuarterValue).change();
                }
            }
        });
    }

    $('#nilai_probabilitas').on('blur', function() {
        let value = parseFloat($(this).val()) || 0;

        if (value < 0) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Nilai Probabilitas Inheren tidak boleh kurang dari 0!',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $(this).val(0).change(); // Reset ke 0 dan trigger change
        } else if (value > 100) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'Nilai Probabilitas Inheren tidak boleh lebih dari 100!',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $(this).val(100).change(); // Reset ke 100 dan trigger change
        }
    });

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

    $('[name="skala_probabilitas"]').on('change', function() {
        refreshSkalaAndLevelRisiko();
    }).change();

    $('#btnShowKualitatif').on('click', function () {
        console.log('show modal');
        $('#modalKualitatif').modal('show'); // Tampilkan modal
    });

    $('.btnShowKualitatifRes').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
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

        return false;
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

    function parseRupiahToNumber(value) {
        return parseFloat(value.replace(/[^0-9,-]/g, '').replace(',', '.')) || 0;
    }

    $('#nilai_dampak').on('input', function () {
        var nilaiDampak = $(this).val().replace(/[^\d.-]/g, ''); // Ambil hanya angka dari input text
        $('#nilai_dampak_residual').val(''); // Reset nilai dampak residual setiap kali nilai dampak diubah
    });

    // Trigger perubahan awal untuk set label saat halaman dimuat
    $('#kategoriDampak').trigger('change');
    // Panggil fungsi saat halaman pertama kali dimuat
    updateSkalaDampak();

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
                for (const [key, value] of formData.entries()) {
                    console.log(key, value);
                }

                formData.append('action', action); // Tambahkan action ke formData

                const url = "{{ route('risk-register-ap.do-analisa', $identifikasiRisiko->id) }}";

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
                                    window.location.href = "{{ route('risk-register-ap.perencanaan', $identifikasiRisiko->id) }}";
                                } else {
                                    window.location.href = "{{ route('risk-register-ap.index') }}";
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
