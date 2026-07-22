@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row mb-5">
  <div class="col-12">
    <div class="ff-preheading">Risk Register</div>
    <h2>Data Rencana Perlakuan Risiko</h2>
  </div>
</div>
<div class="alert alert-warning mb-2">
    Harap isi semua field yang tersedia
</div>
<div class="row g-3 mb-7">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table" id="example">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th>Penyebab Risiko</th>
                <th>Rencana Perlakuan Risiko</th>
                <th>Biaya</th>
                <th>PIC</th>
                <th class="white-space-nowrap">Tindakan</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($penyebabRisiko as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="mw-10r">{{ $item->penyebab_risiko }}</td>
                <td class="mw-10r">{{ $item->rencana_perlakuan_risiko }}</td>
                <td class="mw-10r">{{ $item->biaya_perlakuan_risiko }}</td>
                <td class="mw-10r">{{ $item->pic }}</td>
                <td class="white-space-nowrap">
                  <button
                    class="btn {{ empty($item->rencana_perlakuan_risiko) ? 'btn-muted-danger' : 'btn-muted-primary' }} btn-arrow-right py-0 ps-0 edit-btn"
                    data-id="{{ $item->id }}">
                    Rencana Perlakuan Risiko
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row gy-5 gx-0">
          <div class="col-7 d-flex align-items-center">
            Nilai Dampak Inherent
          </div>
          <div class="col text-center">
            <h2 class="mb-0">
              {{ strpos($riskAnalysis->nilai_dampak, '.') !== false ? number_format($riskAnalysis->nilai_dampak, 2, ',', '.') : number_format($riskAnalysis->nilai_dampak, 0, ',', '.') }}
            </h2>
          </div>
          <div class="col-7 d-flex align-items-center">
            Skala Dampak Inherent
          </div>
          <div class="col text-center">
            <h2 class="mb-0">
              {{ $riskAnalysis->skala_dampak }}
            </h2>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row gy-5 gx-0">
          <div class="col-7 d-flex align-items-center">
            Nilai Probabilitas Inherent (%)
          </div>
          <div class="col text-center">
            <h2 class="mb-0">
              {{ $riskAnalysis->nilai_probabilitas }}
            </h2>
          </div>
          <div class="col-7 d-flex align-items-center">
            Skala Probabilitas Inherent
          </div>
          <div class="col text-center">
            <h2 class="mb-0">
              {{ $riskAnalysis->skalaProbabilitas->tingkat }}
            </h2>
            <span class="ff-heading-med text-capitalize">{{ $riskAnalysis->skalaProbabilitas->skala }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-sm">
      <div class="card-body">
        <div class="row gy-5 gx-0">
          <div class="col-7 d-flex align-items-center">
            Skala Risiko Inherent
          </div>
          <div class="col text-center">
            <h2 class="mb-0">
              {{ $riskAnalysis->skala_risiko }}
            </h2>
          </div>
          <div class="col-7 d-flex align-items-center">
            Level Risiko Inherent
          </div>
          <div class="col text-center">
            <div class="gap-2">
              <label class="radio-label low-label {{ strtolower($riskAnalysis->level_risiko) == 'low' ? 'active' : '' }}"
                for="low"></label>
              <label
                class="radio-label low-medium-label {{ strtolower($riskAnalysis->level_risiko) == 'low to moderate' ? 'active' : '' }}"
                for="low-medium"></label>
              <label class="radio-label medium-label {{ strtolower($riskAnalysis->level_risiko) == 'moderate' ? 'active' : '' }}"
                for="medium"></label>
              <label
                class="radio-label medium-high-label {{ strtolower($riskAnalysis->level_risiko) == 'moderate to high' ? 'active' : '' }}"
                for="medium-high"></label>
              <label class="radio-label high-label {{ strtolower($riskAnalysis->level_risiko) == 'high' ? 'active' : '' }}"
                for="high"></label>
            </div>
            <span class="ff-heading-med">{{ $riskAnalysis->level_risiko }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<form class="row g-3" method="POST" action="{{ route('risk-register-perencanaan.update', $riskRegister) }}">
  @csrf
  @method('PUT')
  <div class="col-12">
    <div class="divider mb-3 mb-md-5">
      <div class="divider-text">
        <h4 class="mb-0 ff-heading-sm">Pengukuran Risiko Residual</h4>
      </div>
    </div>
  </div>
  <!----------------------------------------------- Quarter 1 ----------------------------------------------->
  <div class="col-md-6 col-lg-3">
    <div class="card card-sm">
      <div class="card-header d-flex align-items-center">
        <span class="badge lead__icon badge-soft-warning me-3">
          <i class="bx bx-pie-chart fs-2"></i>
        </span>
        <h6>Quarter 1</h6>
      </div>
      <div class="card-body">
        <div class="row g-2">
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Dampak Q1</label>
            <input class="form-control" type="text" id="target_nilai_dampak_q1" name="target_nilai_dampak_q1"
              value="{{ number_format($rencana->target_nilai_dampak_q1, 0, ',', '.') }}"
              placeholder="Target Nilai Dampak Q1" data-max="{{ $riskAnalysis->nilai_dampak }}" required>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Skala Dampak Q1</label>
            <select class="form-select js-select-hide-search" name="target_skala_dampak_q1"
              placeholder="Target Skala Dampak Q1" id="target_skala_dampak_q1"
              data-max="{{ $riskAnalysis->skala_dampak }}" required>
              <option selected value="" disabled>-</option>
              <option value="1" {{ $rencana->target_skala_dampak_q1 == '1' ? 'selected' : '' }}>1</option>
              <option value="2" {{ $rencana->target_skala_dampak_q1 == '2' ? 'selected' : '' }}>2</option>
              <option value="3" {{ $rencana->target_skala_dampak_q1 == '3' ? 'selected' : '' }}>3</option>
              <option value="4" {{ $rencana->target_skala_dampak_q1 == '4' ? 'selected' : '' }}>4</option>
              <option value="5" {{ $rencana->target_skala_dampak_q1 == '5' ? 'selected' : '' }}>5</option>
            </select>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Probabilitas Q1</label>
            <input class="form-control" type="text" name="target_nilai_probabilitas_q1"
              value="{{ $rencana->target_nilai_probabilitas_q1 }}" placeholder="#"
              id="target_nilai_probabilitas_q1" data-max="{{ $riskAnalysis->nilai_probabilitas }}" required>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_probabilitas_q1"
              value="{{ $rencana->target_skala_probabilitas_q1 }}" id="target_skala_probabilitas_q1" placeholder=""
              oninput="validateInputsQ1()" readonly>
            <label>Target Skala Probabilitas Q1</label>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_risiko_q1" id="target_skala_risiko_q1"
              value="{{ $rencana->target_skala_risiko_q1 }}" placeholder="" readonly>
            <label>Target Skala Risiko Q1</label>
          </div>
          <div class="form-floating level_risiko text-center">
            <input class="form-control" type="text" name="target_level_risiko_q1" id="target_level_risiko_q1" value="{{ $rencana->target_level_risiko_q1 }}" placeholder="" readonly>
            <label>Target Level Risiko Q1</label>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!----------------------------------------------- Quarter 2 ----------------------------------------------->
  <div class="col-md-6 col-lg-3">
    <div class="card card-sm">
      <div class="card-header d-flex align-items-center">
        <span class="badge lead__icon badge-soft-warning me-3">
          <i class="bx bx-pie-chart fs-2 rotate-90"></i>
        </span>
        <h6>Quarter 2</h6>
      </div>
      <div class="card-body">
        <div class="row g-2">
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Dampak Q2</label>
            <input class="form-control" type="text" id="target_nilai_dampak_q2"" name="target_nilai_dampak_q2"
              value="{{ number_format($rencana->target_nilai_dampak_q2, 0, ',', '.') }}"
              placeholder="Target Nilai Dampak Q2" required>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Skala Dampak Q2</label>
            <select class="form-select js-select-hide-search" name="target_skala_dampak_q2"
              placeholder="Target Nilai Dampak Q2" id="target_skala_dampak_q2"
              data-max="{{ $riskAnalysis->skala_dampak }}" required>
              <option selected value="" disabled>-</option>
              <option value="1" {{ $rencana->target_skala_dampak_q2 == '1' ? 'selected' : '' }}>1</option>
              <option value="2" {{ $rencana->target_skala_dampak_q2 == '2' ? 'selected' : '' }}>2</option>
              <option value="3" {{ $rencana->target_skala_dampak_q2 == '3' ? 'selected' : '' }}>3</option>
              <option value="4" {{ $rencana->target_skala_dampak_q2 == '4' ? 'selected' : '' }}>4</option>
              <option value="5" {{ $rencana->target_skala_dampak_q2 == '5' ? 'selected' : '' }}>5</option>
            </select>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Probabilitas Q2</label>
            <input class="form-control" type="text" name="target_nilai_probabilitas_q2"
              value="{{ $rencana->target_nilai_probabilitas_q2 }}" placeholder="#"
              id="target_nilai_probabilitas_q2" data-max="{{ $riskAnalysis->nilai_probabilitas }}" required>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_probabilitas_q2"
              value="{{ $rencana->target_skala_probabilitas_q2 }}" id="target_skala_probabilitas_q2" placeholder=""
              oninput="validateInputsQ2()" readonly>
            <label>Target Skala Probabilitas Q2</label>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_risiko_q2" id="target_skala_risiko_q2"
              value="{{ $rencana->target_skala_risiko_q2 }}" placeholder="" readonly>
            <label>Target Skala Risiko Q2</label>
          </div>
          <div class="form-floating level_risiko text-center">
            <input class="form-control" type="text" name="target_level_risiko_q2" id="target_level_risiko_q2" value="{{ $rencana->target_level_risiko_q2 }}" placeholder="" readonly>
            <label>Target Level Risiko Q2</label>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!----------------------------------------------- Quarter 3 ----------------------------------------------->
  <div class="col-md-6 col-lg-3">
    <div class="card card-sm">
      <div class="card-header d-flex align-items-center">
        <span class="badge lead__icon badge-soft-warning me-3">
          <i class="bx bx-pie-chart fs-2 rotate-180"></i>
        </span>
        <h6>Quarter 3</h6>
      </div>
      <div class="card-body">
        <div class="row g-2">
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Dampak Q3</label>
            <input class="form-control" type="text" id="target_nilai_dampak_q3" name="target_nilai_dampak_q3"
              value="{{ number_format($rencana->target_nilai_dampak_q3, 0, ',', '.') }}"
              placeholder="Target Nilai Dampak Q3" required>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Skala Dampak Q3</label>
            <select class="form-select js-select-hide-search" name="target_skala_dampak_q3"
              placeholder="Target Nilai Dampak Q3" id="target_skala_dampak_q3"
              data-max="{{ $riskAnalysis->skala_dampak }}" required>
              <option selected value="" disabled>-</option>
              <option value="1" {{ $rencana->target_skala_dampak_q3 == '1' ? 'selected' : '' }}>1</option>
              <option value="2" {{ $rencana->target_skala_dampak_q3 == '2' ? 'selected' : '' }}>2</option>
              <option value="3" {{ $rencana->target_skala_dampak_q3 == '3' ? 'selected' : '' }}>3</option>
              <option value="4" {{ $rencana->target_skala_dampak_q3 == '4' ? 'selected' : '' }}>4</option>
              <option value="5" {{ $rencana->target_skala_dampak_q3 == '5' ? 'selected' : '' }}>5</option>
            </select>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Probabilitas Q3</label>
            <input class="form-control" type="text" name="target_nilai_probabilitas_q3"
              value="{{ $rencana->target_nilai_probabilitas_q3 }}" placeholder="#"
              id="target_nilai_probabilitas_q3" data-max="{{ $riskAnalysis->nilai_probabilitas }}" required>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_probabilitas_q3"
              value="{{ $rencana->target_skala_probabilitas_q3 }}" id="target_skala_probabilitas_q3" placeholder=""
              oninput="validateInputsQ3()" readonly>
            <label>Target Skala Probabilitas Q3</label>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_risiko_q3" id="target_skala_risiko_q3"
              value="{{ $rencana->target_skala_risiko_q3 }}" placeholder="" readonly>
            <label>Target Skala Risiko Q3</label>
          </div>
          <div class="form-floating level_risiko text-center">
            <input class="form-control" type="text" name="target_level_risiko_q3" id="target_level_risiko_q3" value="{{ $rencana->target_level_risiko_q3 }}" placeholder="" readonly>
            <label>Target Level Risiko Q3</label>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!----------------------------------------------- Quarter 4 ----------------------------------------------->
  <div class="col-md-6 col-lg-3">
    <div class="card card-sm">
      <div class="card-header d-flex align-items-center">
        <span class="badge lead__icon badge-soft-warning me-3">
          <i class="bx bx-pie-chart fs-2 rotate-270"></i>
        </span>
        <h6>Quarter 4</h6>
      </div>
      <div class="card-body">
        <div class="row g-2">
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Dampak Q4</label>
            <input class="form-control" type="text"  id="target_nilai_dampak_q4" name="target_nilai_dampak_q4"
              value="{{ number_format($rencana->target_nilai_dampak_q4, 0, ',', '.') }}"
              placeholder="Target Nilai Dampak Q4" required>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Skala Dampak Q4</label>
            <select class="form-select js-select-hide-search" name="target_skala_dampak_q4"
              placeholder="Target Nilai Dampak Q4" id="target_skala_dampak_q4"
              data-max="{{ $riskAnalysis->skala_dampak }}" required>
              <option selected value="" disabled>-</option>
              <option value="1" {{ $rencana->target_skala_dampak_q4 == '1' ? 'selected' : '' }}>1</option>
              <option value="2" {{ $rencana->target_skala_dampak_q4 == '2' ? 'selected' : '' }}>2</option>
              <option value="3" {{ $rencana->target_skala_dampak_q4 == '3' ? 'selected' : '' }}>3</option>
              <option value="4" {{ $rencana->target_skala_dampak_q4 == '4' ? 'selected' : '' }}>4</option>
              <option value="5" {{ $rencana->target_skala_dampak_q4 == '5' ? 'selected' : '' }}>5</option>
            </select>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Probabilitas Q4</label>
            <input class="form-control" type="text" name="target_nilai_probabilitas_q4"
              value="{{ $rencana->target_nilai_probabilitas_q4 }}" placeholder="#"
              id="target_nilai_probabilitas_q4" data-max="{{ $riskAnalysis->nilai_probabilitas }}" required>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_probabilitas_q4"
              value="{{ $rencana->target_skala_probabilitas_q4 }}" id="target_skala_probabilitas_q4" placeholder=""
              oninput="validateInputsQ4()" readonly>
            <label>Target Skala Probabilitas Q4</label>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_risiko_q4" id="target_skala_risiko_q4"
              value="{{ $rencana->target_skala_risiko_q4 }}" placeholder="" readonly>
            <label>Target Skala Risiko Q4</label>
          </div>
          <div class="form-floating level_risiko text-center">
            <input class="form-control" type="text" name="target_level_risiko_q4" id="target_level_risiko_q4" value="{{ $rencana->target_level_risiko_q4 }}" placeholder="" readonly>
            <label>Target Level Risiko Q4</label>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 d-flex gap-1 pt-4">
    <button type="submit" class="btn btn-submit">Simpan</button>
    <a href="{{ route('risk-register.index') }}" class="btn btn-outline-secondary">Batal</a>
  </div>
</form>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex flex-between-center">
        <h4 class="modal-title">Rencana Perlakuan Risiko</h4>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form id="editForm" action="{{ route('risk-register.updatePerlakuanRisiko', ['id' => ':id']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body row g-2">
          <input type="hidden" name="id" id="editId">
          <div class="col-12">
            <div class="form-floating">
              <input type="text" disabled="disabled" name="penyebab_risiko" class="form-control" id="penyebab_risiko"
                placeholder="Penyebab Risiko">
              <label for="penyebab_risiko">Penyebab Risiko</label>
            </div>
          </div>
          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" name="rencana_perlakuan_risiko" id="rencana_perlakuan_risiko" rows="3"
                placeholder="Rencana Perlakuan Risiko" required></textarea>
              <label for="rencana_perlakuan_risiko" class="form-label">Rencana Perlakuan Risiko</label>
            </div>
          </div>
          <div class="col-12">
            <div class="form-floating">
              <textarea class="form-control" name="output_perlakuan_risiko" id="output_perlakuan_risiko" rows="3"
                placeholder="Output Perlakuan Risiko" required></textarea>
              <label for="output_perlakuan_risiko" class="form-label">Output Perlakuan Risiko</label>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="form-floating">
              <input type="text" class="form-control" name="biaya_perlakuan_risiko" id="biaya_perlakuan_risiko"
                placeholder="Masukkan biaya dalam rupiah" required>
              <label for="biaya_perlakuan_risiko" class="form-label">Biaya Perlakuan Risiko (dalam rupiah)</label>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="form-floating">
              <input type="text" class="form-control" name="pic" id="pic" placeholder="PIC" required>
              <label for="pic" class="form-label">PIC</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script>
    function validateInputsQ1() {
        console.log("validateInputsQ1");
        var inputIds = ['target_nilai_dampak_q1', 'target_nilai_probabilitas_q1'];
        var selectIds = ['target_skala_dampak_q1'];

        inputIds.forEach(function(id) {
            var input = document.getElementById(id);
            var maxValue = parseInt(input.getAttribute('data-max'), 10);
            var value = input.value.replace(/\./g, ''); // Remove dots
            value = parseInt(value, 10);
            if (!isNaN(value) && value > maxValue) {
                input.value = maxValue.toLocaleString('id-ID'); // Format as currency
                alert("Value exceeds maximum allowable limit.");
            }
        });

        selectIds.forEach(function(id) {
            var select = document.getElementById(id);
            var maxValue = parseInt(select.getAttribute('data-max'), 10);
            var value = parseInt(select.value, 10);
            if (!isNaN(value) && value > maxValue) {
                //select.value = maxValue;
                select.value = maxValue; // Mengatur nilai select
                // Memicu event 'change'
                var event = new Event('change', { bubbles: true });
                select.dispatchEvent(event);
                alert("Value exceeds maximum allowable limit.");
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('target_nilai_dampak_q1').addEventListener('input', validateInputsQ1);
        document.getElementById('target_nilai_probabilitas_q1').addEventListener('input', validateInputsQ1);
        //document.getElementById('target_skala_dampak_q1').addEventListener('change', validateInputsQ1);
    });
</script>

<script>
    function validateInputsQ2() {
        var inputIds = ['target_nilai_dampak_q2', 'target_nilai_probabilitas_q2'];
        var selectIds = ['target_skala_dampak_q2'];

        inputIds.forEach(function(id) {
            var input = document.getElementById(id);
            var maxValue = parseInt(input.getAttribute('data-max'), 10);
            var value = input.value.replace(/\./g, ''); // Remove dots
            value = parseInt(value, 10);
            if (!isNaN(value) && value > maxValue) {
                input.value = maxValue.toLocaleString('id-ID'); // Format as currency
                alert("Value exceeds maximum allowable limit.");
            }
        });

        selectIds.forEach(function(id) {
            var select = document.getElementById(id);
            var maxValue = parseInt(select.getAttribute('data-max'), 10);
            var value = parseInt(select.value, 10);
            if (!isNaN(value) && value > maxValue) {
                //select.value = maxValue;
                select.value = maxValue; // Mengatur nilai select
                // Memicu event 'change'
                var event = new Event('change', { bubbles: true });
                select.dispatchEvent(event);
                alert("Value exceeds maximum allowable limit.");
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('target_nilai_dampak_q2').addEventListener('input', validateInputsQ2);
        document.getElementById('target_nilai_probabilitas_q2').addEventListener('input', validateInputsQ2);
        //document.getElementById('target_skala_dampak_q2').addEventListener('change', validateInputsQ2);
    });
</script>
<script>
    function validateInputsQ3() {
        var inputIds = ['target_nilai_dampak_q3', 'target_nilai_probabilitas_q3'];
        var selectIds = ['target_skala_dampak_q3'];

        inputIds.forEach(function(id) {
            var input = document.getElementById(id);
            var maxValue = parseInt(input.getAttribute('data-max'), 10);
            var value = input.value.replace(/\./g, ''); // Remove dots
            value = parseInt(value, 10);
            if (!isNaN(value) && value > maxValue) {
                input.value = maxValue.toLocaleString('id-ID'); // Format as currency
                alert("Value exceeds maximum allowable limit.");
            }
        });

        selectIds.forEach(function(id) {
            var select = document.getElementById(id);
            var maxValue = parseInt(select.getAttribute('data-max'), 10);
            var value = parseInt(select.value, 10);
            if (!isNaN(value) && value > maxValue) {
                //select.value = maxValue;
                select.value = maxValue; // Mengatur nilai select
                // Memicu event 'change'
                var event = new Event('change', { bubbles: true });
                select.dispatchEvent(event);
                alert("Value exceeds maximum allowable limit.");
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('target_nilai_dampak_q3').addEventListener('input', validateInputsQ3);
        document.getElementById('target_nilai_probabilitas_q3').addEventListener('input', validateInputsQ3);
        //document.getElementById('target_skala_dampak_q3').addEventListener('change', validateInputsQ3);
    });
</script>
<script>
    function validateInputsQ4() {
        var inputIds = ['target_nilai_dampak_q4', 'target_nilai_probabilitas_q4'];
        var selectIds = ['target_skala_dampak_q4'];

        inputIds.forEach(function(id) {
            var input = document.getElementById(id);
            var maxValue = parseInt(input.getAttribute('data-max'), 10);
            var value = input.value.replace(/\./g, ''); // Remove dots
            value = parseInt(value, 10);
            if (!isNaN(value) && value > maxValue) {
                input.value = maxValue.toLocaleString('id-ID'); // Format as currency
                alert("Value exceeds maximum allowable limit.");
            }
        });

        selectIds.forEach(function(id) {
            var select = document.getElementById(id);
            var maxValue = parseInt(select.getAttribute('data-max'), 10);
            var value = parseInt(select.value, 10);
            if (!isNaN(value) && value > maxValue) {
                //select.value = maxValue;
                select.value = maxValue; // Mengatur nilai select
                // Memicu event 'change'
                var event = new Event('change', { bubbles: true });
                select.dispatchEvent(event);
                alert("Value exceeds maximum allowable limit.");
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('target_nilai_dampak_q4').addEventListener('input', validateInputsQ4);
        document.getElementById('target_nilai_probabilitas_q4').addEventListener('input', validateInputsQ4);
        //document.getElementById('target_skala_dampak_q4').addEventListener('change', validateInputsQ4);
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rupiahFields = document.querySelectorAll("#target_nilai_dampak_q1");
        rupiahFields.forEach(field => {
            field.addEventListener("input", function(e) {
                let value = e.target.value;
                value = value.replace(/[^,\d]/g, "").toString();
                let split = value.split(",");
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? "." : "";
                    rupiah += separator + ribuan.join(".");
                }

                rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
                e.target.value = rupiah;
            });
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rupiahFields = document.querySelectorAll("#target_nilai_dampak_q2");
        rupiahFields.forEach(field => {
            field.addEventListener("input", function(e) {
                let value = e.target.value;
                value = value.replace(/[^,\d]/g, "").toString();
                let split = value.split(",");
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? "." : "";
                    rupiah += separator + ribuan.join(".");
                }

                rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
                e.target.value = rupiah;
            });
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rupiahFields = document.querySelectorAll("#target_nilai_dampak_q3");
        rupiahFields.forEach(field => {
            field.addEventListener("input", function(e) {
                let value = e.target.value;
                value = value.replace(/[^,\d]/g, "").toString();
                let split = value.split(",");
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? "." : "";
                    rupiah += separator + ribuan.join(".");
                }

                rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
                e.target.value = rupiah;
            });
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rupiahFields = document.querySelectorAll("#target_nilai_dampak_q4");
        rupiahFields.forEach(field => {
            field.addEventListener("input", function(e) {
                let value = e.target.value;
                value = value.replace(/[^,\d]/g, "").toString();
                let split = value.split(",");
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? "." : "";
                    rupiah += separator + ribuan.join(".");
                }

                rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
                e.target.value = rupiah;
            });
        });
    });
</script>
<script>
    /*
    document.addEventListener("DOMContentLoaded", function() {
        const rupiahFields = document.querySelectorAll("#biaya_perlakuan_risiko");
        rupiahFields.forEach(field => {
            field.addEventListener("input", function(e) {
                let value = e.target.value;
                value = value.replace(/[^,\d]/g, "").toString();
                let split = value.split(",");
                let sisa = split[0].length % 3;
                let rupiah = split[0].substr(0, sisa);
                let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                if (ribuan) {
                    let separator = sisa ? "." : "";
                    rupiah += separator + ribuan.join(".");
                }

                rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
                e.target.value = rupiah;
            });
        });
    });
    */
</script>
<script>
    $(document).ready(function() {
        $('#target_skala_dampak_q1').select2();
        $('#target_skala_dampak_q1').on('select2:select', function (e) {
          calculate(e);
          validateInputsQ1(e);
        });

        $('#target_skala_dampak_q2').select2();
        $('#target_skala_dampak_q2').on('select2:select', function (e) {
          calculate2(e);
          validateInputsQ2(e);
        });

        $('#target_skala_dampak_q3').select2();
        $('#target_skala_dampak_q3').on('select2:select', function (e) {
          calculate3(e);
          validateInputsQ3(e);
        });

        $('#target_skala_dampak_q4').select2();
        $('#target_skala_dampak_q4').on('select2:select', function (e) {
          calculate4(e);
          validateInputsQ4(e);
        });


        $('.edit-btn').click(function() {
            var id = $(this).data('id');
            console.log("ID yang diambil:", id);
            $.get("{{ url('risk-register') }}" + '/' + id + '/editPerlakuanRisiko', function(data) {
                $('#editId').val(data.id);
                $('#penyebab_risiko').val(data.penyebab_risiko);
                $('#rencana_perlakuan_risiko').val(data.rencana_perlakuan_risiko);
                $('#output_perlakuan_risiko').val(data.output_perlakuan_risiko);
                $('#biaya_perlakuan_risiko').val(data.biaya_perlakuan_risiko);
                $('#pic').val(data.pic);

                $('#editModal').modal('show');
            });
        });

        $('#editForm').submit(function(e) {
            
            e.preventDefault();
            
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    if (response.status == 200) {
                        $('#editModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Terjadi Kesalahan '+ response.error);
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
    $('#target_nilai_probabilitas_q1').on('input', function() {
        var nilaiProbabilitasQ1 = $(this).val();

        // Kirim permintaan AJAX untuk mendapatkan data skala probabilitas
        $.ajax({
            url: "{{ route('get.skala.probabilitas.q1') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                target_nilai_probabilitas_q1: nilaiProbabilitasQ1,
                type_risiko: "{{ $identifikasiRisiko->type }}" // Tambahkan data type_risiko jika diperlukan
            },
            success: function(response) {
                // Mengisi nilai skala probabilitas jika respons tidak kosong
                if (response) {
                    $('#target_skala_probabilitas_q1').val(response.tingkat + ' - ' + response.skala);
                    // Atur nilai skala risiko berdasarkan nilai skala probabilitas yang diperoleh
                    calculate();
                }
            },
            error: function(xhr, status, error) {
                // Tangani kesalahan jika terjadi
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
<script>
    $(document).ready(function() {
    $('#target_nilai_probabilitas_q2').on('input', function() {
        var nilaiProbabilitasQ2 = $(this).val();

        // Kirim permintaan AJAX untuk mendapatkan data skala probabilitas
        $.ajax({
            url: "{{ route('get.skala.probabilitas.q2') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                target_nilai_probabilitas_q2: nilaiProbabilitasQ2,
                type_risiko: "{{ $identifikasiRisiko->type }}" // Tambahkan data type_risiko jika diperlukan
            },
            success: function(response) {
                // Mengisi nilai skala probabilitas jika respons tidak kosong
                if (response) {
                    $('#target_skala_probabilitas_q2').val(response.tingkat + ' - ' + response.skala);
                    // Atur nilai skala risiko berdasarkan nilai skala probabilitas yang diperoleh
                    calculate2();
                }
            },
            error: function(xhr, status, error) {
                // Tangani kesalahan jika terjadi
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
<script>
    $(document).ready(function() {
    $('#target_nilai_probabilitas_q3').on('input', function() {
        var nilaiProbabilitasQ3 = $(this).val();

        // Kirim permintaan AJAX untuk mendapatkan data skala probabilitas
        $.ajax({
            url: "{{ route('get.skala.probabilitas.q3') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                target_nilai_probabilitas_q3: nilaiProbabilitasQ3,
                type_risiko: "{{ $identifikasiRisiko->type }}" // Tambahkan data type_risiko jika diperlukan
            },
            success: function(response) {
                // Mengisi nilai skala probabilitas jika respons tidak kosong
                if (response) {
                    $('#target_skala_probabilitas_q3').val(response.tingkat + ' - ' + response.skala);
                    // Atur nilai skala risiko berdasarkan nilai skala probabilitas yang diperoleh
                    calculate3();
                }
            },
            error: function(xhr, status, error) {
                // Tangani kesalahan jika terjadi
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
<script>
    $(document).ready(function() {
    $('#target_nilai_probabilitas_q4').on('input', function() {
        var nilaiProbabilitasQ4 = $(this).val();

        // Kirim permintaan AJAX untuk mendapatkan data skala probabilitas
        $.ajax({
            url: "{{ route('get.skala.probabilitas.q4') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                target_nilai_probabilitas_q4: nilaiProbabilitasQ4,
                type_risiko: "{{ $identifikasiRisiko->type }}" // Tambahkan data type_risiko jika diperlukan
            },
            success: function(response) {
                // Mengisi nilai skala probabilitas jika respons tidak kosong
                if (response) {
                    $('#target_skala_probabilitas_q4').val(response.tingkat + ' - ' + response.skala);
                    // Atur nilai skala risiko berdasarkan nilai skala probabilitas yang diperoleh
                    calculate4();
                }
            },
            error: function(xhr, status, error) {
                // Tangani kesalahan jika terjadi
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
<script>

    // Definisi array multidimensi untuk level_risiko
    var level_risiko = {!! json_encode($level_risiko) !!};

    // Definisi array multidimensi untuk skala_risiko
    var skala_risiko = {!! json_encode($nilai_risiko) !!};

    // Mendefinisikan fungsi calculate()
    function calculate() {
        //console.log("calculate 1");
        //console.log(document.getElementById('target_skala_dampak_q1'));
        var a = parseFloat(document.getElementById('target_skala_dampak_q1').value);
        var b = parseFloat(document.getElementById('target_skala_probabilitas_q1').value);

        if (!isNaN(a) && !isNaN(b)) {
            //var result = a * b;
            var result = level_risiko[a][b];
            var result2 = skala_risiko[a][b];

            document.getElementById('target_level_risiko_q1').value = result;
            document.getElementById('target_skala_risiko_q1').value = result2;
        }
    }

    // Memanggil fungsi calculate() saat nilai input berubah
    function initialize() {
        //document.getElementById('target_skala_dampak_q1').addEventListener('change', calculate);
        document.getElementById('target_skala_probabilitas_q1').addEventListener('change', calculate);
    }

    // Memanggil fungsi initialize() setelah dokumen selesai dimuat
    document.addEventListener('DOMContentLoaded', initialize);
    // Mendefinisikan fungsi calculate()
    function calculate2() {
        var a = parseFloat(document.getElementById('target_skala_dampak_q2').value);
        var b = parseFloat(document.getElementById('target_skala_probabilitas_q2').value);

        if (!isNaN(a) && !isNaN(b)) {
            //var result = a * b;
            var result = level_risiko[a][b];
            var result2 = skala_risiko[a][b];

            document.getElementById('target_skala_risiko_q2').value = result2;
            document.getElementById('target_level_risiko_q2').value = result;
        }
    }

    // Memanggil fungsi calculate() saat nilai input berubah
    function initialize2() {
        //document.getElementById('target_skala_dampak_q2').addEventListener('change', calculate2);
        document.getElementById('target_skala_probabilitas_q2').addEventListener('change', calculate2);
    }

    // Memanggil fungsi initialize() setelah dokumen selesai dimuat
    document.addEventListener('DOMContentLoaded', initialize2);
    // Mendefinisikan fungsi calculate()
    function calculate3() {
        var a = parseFloat(document.getElementById('target_skala_dampak_q3').value);
        var b = parseFloat(document.getElementById('target_skala_probabilitas_q3').value);

        if (!isNaN(a) && !isNaN(b)) {
            //var result = a * b;
            var result = level_risiko[a][b];
            var result2 = skala_risiko[a][b];

            document.getElementById('target_skala_risiko_q3').value = result2;
            document.getElementById('target_level_risiko_q3').value = result;
        }
    }

    // Memanggil fungsi calculate() saat nilai input berubah
    function initialize3() {
        //document.getElementById('target_skala_dampak_q3').addEventListener('change', calculate3);
        document.getElementById('target_skala_probabilitas_q3').addEventListener('change', calculate3);
    }

    // Memanggil fungsi initialize() setelah dokumen selesai dimuat
    document.addEventListener('DOMContentLoaded', initialize3);
    // Mendefinisikan fungsi calculate()
    function calculate4() {
        var a = parseFloat(document.getElementById('target_skala_dampak_q4').value);
        var b = parseFloat(document.getElementById('target_skala_probabilitas_q4').value);

        if (!isNaN(a) && !isNaN(b)) {
            //var result = a * b;
            var result = level_risiko[a][b];
            var result2 = skala_risiko[a][b];

            document.getElementById('target_skala_risiko_q4').value = result2
            ;
            document.getElementById('target_level_risiko_q4').value = result;
        }
    }

    // Memanggil fungsi calculate() saat nilai input berubah
    function initialize4() {
        //document.getElementById('target_skala_dampak_q4').addEventListener('change', calculate4);
        document.getElementById('target_skala_probabilitas_q4').addEventListener('change', calculate4);
    }

    // Memanggil fungsi initialize() setelah dokumen selesai dimuat
    document.addEventListener('DOMContentLoaded', initialize4);
</script>


@endsection