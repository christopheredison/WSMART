@extends('layouts.default')
@section('dashboard')
<div class="row mb-5">
  <div class="col-12">
    <div class="ff-preheading">Risk Register</div>
    <h2>View Detail</h2>
  </div>
</div>
<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center border-0 pb-0">
        <div class="lead__icon alert-primary rounded-pill me-3">
          <i class="bx bx-rocket fs-4"></i>
        </div>
        <h5>Target</h5>
      </div>
      <div class="card-body">
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Target Capaian Kinerja
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->tck->title }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Rencana Kegiatan
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->deskripsi_rencana_kegiatan }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Kategori dan Jenis Risiko
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->kategoriRisiko->title }} -
            {{ $identifikasiRisiko->jenisRisiko->title }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Peristiwa Risiko
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->peristiwaRisiko->title }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Deskripsi Peristiwa Risiko
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->deskripsi_peristiwa_risiko }}</p>
        </div>
        <div class="row gx-0">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Tipe Risiko
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $identifikasiRisiko->type }}</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center border-0 pb-0">
        <div class="lead__icon alert-success rounded-pill me-3">
          <i class='bx bx-bar-chart-square fs-4'></i>
        </div>
        <h5>Key Risk Indicator</h5>
      </div>
      <div class="card-body pt-2">
        <div class="table-responsive-sm">
          <table class="table table-md mb-md-0">
            <thead>
              <tr>
                <th class="index-number align-middle">#</th>
                <th class="key_risk_indicatorl mw-10r align-middle">Key Risk Indicator</th>
                <th class="satuan_kri align-middle">Satuan KRI</th>
                <th class="batas_aman">
                  <div class="alert alert-success text-nowrap">Batas Aman</div>
                </th>
                <th class="batas_waspada">
                  <div class="alert alert-warning text-nowrap">Batas Siaga</div>
                </th>
                <th class="batas_bahaya">
                  <div class="alert alert-danger text-nowrap">Batas Bahaya</div>
                </th>
              </tr>
            </thead>
            <tbody id="kri-body">
              @foreach ($kri as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="key_risk_indicator">{{ $item->kri }}</td>
                <td class="satuan_kri">{{ $item->satuan_kri }}</td>
                <td class="batas_aman">{{ $item->batas_aman }}</td>
                <td class="batas_waspada">{{ $item->batas_waspada }}</td>
                <td class="batas_bahaya">{{ $item->batas_bahaya }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header d-flex align-items-center border-0 pb-0">
        <div class="lead__icon alert-danger rounded-pill me-3">
          <i class='bx bx-briefcase-alt-2 fs-4'></i>
        </div>
        <h5>Penyebab Risiko</h5>
      </div>
      <div class="card-body">
        <ol>
          @foreach ($penyebabRisiko as $index => $item)
          <li>{{ $item->penyebab_risiko }}</li>
          @endforeach
        </ol>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-6">
    <div class="card">
      <div class="card-header d-flex align-items-center border-0 pb-0">
        <div class="lead__icon alert-info rounded-pill me-3">
          <i class='bx bx-cog fs-4'></i>
        </div>
        <h5>Kontrol</h5>
      </div>
      <div class="card-body">
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-6 col-xxl-4 mb-1 mb-md-0">
            Kontrol Eksisting
          </label>
          <p class="col-12 col-md-6 col-xxl-8 mb-0">{{ $identifikasiRisiko->kontrol_eksisting ?? NULL }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-6 col-xxl-4 mb-1 mb-md-0">
            Penilaian Efektivitas Kontrol
          </label>
          <p class="col-12 col-md-6 col-xxl-8 mb-0">{{ $identifikasiRisiko->penilaian_efektifitas_kontrol ?? NULL }}
          </p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-6 col-xxl-4 mb-1 mb-md-0">
            Perkiraan Waktu Terpapar Risiko
          </label>
          <p class="col-12 col-md-6 col-xxl-8 mb-0">{{ $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_mulai }}
            s/d {{ $identifikasiRisiko->perkiraan_waktu_terpapar_risiko_akhir }}</p>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex align-items-center border-0 pb-0">
        <div class="lead__icon alert-primary rounded-pill me-3">
          <i class="bx bx-briefcase-alt-2 fs-4"></i>
        </div>
        <h5>Kuantifikasi/Analisa Risiko</h5>
      </div>
      <div class="card-body">
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Kategori Dampak
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $riskAnalysis->kategori_dampak }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Area Dampak
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $riskAnalysis->areaDampakObj?->title }}</p>
        </div>
        <div class="row border-bottom gx-0 mb-2 pb-2">
          <label class="form-label fw-semibold col-12 col-md-4 col-lg-3 col-xxl-2 mb-1 mb-md-0">
            Deskripsi Dampak
          </label>
          <p class="col-12 col-md-6 mb-0">{{ $riskAnalysis->deskripsi_dampak }}</p>
        </div>
        <div class="row g-2 g-lg-5">
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb-2">
              <label class="label-start col-5">Nilai Dampak</label>
              <input class="form-control text-center" name="nilai_dampak" id="nilai_dampak" type="text" placeholder=""
                value="{{ number_format($riskAnalysis->nilai_dampak, 0, ',', '.') }}" min="0" disabled />
            </div>
            <div class="d-flex">
              <label class="label-start col-5">Skala Dampak</label>
              <div class="text-center w-100">
                <input class="form-control text-center" name="skala_dampak" id="skala_dampak" type="text"
                  value="{{ $riskAnalysis->skala_dampak }}" disabled />
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb-2">
              <label class="label-start col-5">Nilai Probabilitas (%)</label>
              <input class="form-control text-center" name="nilai_probabilitas" id="nilai_probabilitas" type="text"
                placeholder="" min="0" max="100" value="{{ $riskAnalysis->nilai_probabilitas }}"
                oninput="validateProbabilitas(this)" disabled />
            </div>
            <div class="d-flex">
              <label class="label-start col-5">Skala Probabilitas</label>
              <input class="form-control text-center" name="skala_probabilitas_id" type="text" placeholder="N/A"
                value="{{ $riskAnalysis->skala_probabilitas_id }}" id="skala_probabilitas_id" oninput="calculate2()"
                disabled />
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb-2">
              <label class="label-start col-5">Skala risiko</label>
              <input class="form-control text-center" name="skala_risiko" id="skala_risiko" type="number"
                placeholder="N/A" value="{{ $riskAnalysis->skala_risiko }}" disabled />
            </div>
            <div class="d-flex">
              <label class="label-start col-5">Level risiko</label>
              <input class="form-control text-center" name="level_risiko" id="level_risiko" type="text"
                placeholder="N/A" value="{{ $riskAnalysis->level_risiko }}" disabled />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="divider mb-3 mt-7">
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
              value="{{ number_format($rencana->target_nilai_dampak_q1, 0, ',', '.') }}" placeholder="" disabled>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Skala Dampak Q1</label>
            <input class="form-control" type="text" id="target_nilai_dampak_q1" name="target_nilai_dampak_q1"
              value="{{ $rencana->target_skala_dampak_q1 }}" placeholder="" disabled>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Probabilitas Q1</label>
            <input class="form-control" type="text" name="target_nilai_probabilitas_q1"
              value="{{ $rencana->target_nilai_probabilitas_q1 }}" placeholder="" id="target_nilai_probabilitas_q1"
              disabled>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_probabilitas_q1"
              value="{{ $rencana->target_skala_probabilitas_q1 }}" id="target_skala_probabilitas_q1" placeholder=""
              oninput="validateInputsQ1()" disabled>
            <label>Target Skala Probabilitas Q1</label>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_risiko_q1" id="target_skala_risiko_q1"
              value="{{ $rencana->target_skala_risiko_q1 }}" placeholder="" disabled>
            <label>Target Skala Risiko Q1</label>
          </div>
          <div class="form-floating level_risiko text-center">
            <input class="form-control" type="text" name="target_level_risiko_q1" id="target_level_risiko_q1"
              value="{{ $rencana->target_level_risiko_q1 }}" placeholder="" disabled>
            <label class="text-white">Target Level Risiko Q1</label>
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
            <input class="form-control" type="text" id="target_nilai_dampak_q2" name="target_nilai_dampak_q2"
              value="{{ number_format($rencana->target_nilai_dampak_q2, 0, ',', '.') }}" placeholder="" disabled>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Skala Dampak Q2</label>
            <input class="form-control" type="text" id="target_nilai_dampak_q2" name="target_nilai_dampak_q2"
              value="{{ $rencana->target_skala_dampak_q2 }}" placeholder="" disabled>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Probabilitas Q2</label>
            <input class="form-control" type="text" name="target_nilai_probabilitas_q2"
              value="{{ $rencana->target_nilai_probabilitas_q2 }}" placeholder="" id="target_nilai_probabilitas_q2"
              disabled>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_probabilitas_q2"
              value="{{ $rencana->target_skala_probabilitas_q2 }}" id="target_skala_probabilitas_q2" placeholder=""
              oninput="validateInputsQ2()" disabled>
            <label>Target Skala Probabilitas Q2</label>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_risiko_q2" id="target_skala_risiko_q2"
              value="{{ $rencana->target_skala_risiko_q2 }}" placeholder="" disabled>
            <label>Target Skala Risiko Q2</label>
          </div>
          <div class="form-floating level_risiko text-center">
            <input class="form-control" type="text" name="target_level_risiko_q2" id="target_level_risiko_q2"
              value="{{ $rencana->target_level_risiko_q2 }}" placeholder="" disabled>
            <label class="text-white">Target Level Risiko Q2</label>
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
              value="{{ number_format($rencana->target_nilai_dampak_q3, 0, ',', '.') }}" placeholder="" disabled>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Skala Dampak Q3</label>
            <input class="form-control" type="text" id="target_nilai_dampak_q3" name="target_nilai_dampak_q3"
              value="{{ $rencana->target_skala_dampak_q3 }}" placeholder="" disabled>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Probabilitas Q3</label>
            <input class="form-control" type="text" name="target_nilai_probabilitas_q3"
              value="{{ $rencana->target_nilai_probabilitas_q3 }}" placeholder="" id="target_nilai_probabilitas_q3"
              disabled>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_probabilitas_q3"
              value="{{ $rencana->target_skala_probabilitas_q3 }}" id="target_skala_probabilitas_q3" placeholder=""
              oninput="validateInputsQ3()" disabled>
            <label>Target Skala Probabilitas Q3</label>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_risiko_q3" id="target_skala_risiko_q3"
              value="{{ $rencana->target_skala_risiko_q3 }}" placeholder="" disabled>
            <label>Target Skala Risiko Q3</label>
          </div>
          <div class="form-floating level_risiko text-center">
            <input class="form-control" type="text" name="target_level_risiko_q3" id="target_level_risiko_q3"
              value="{{ $rencana->target_level_risiko_q3 }}" placeholder="" disabled>
            <label class="text-white">Target Level Risiko Q3</label>
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
            <input class="form-control" type="text" id="target_nilai_dampak_q4" name="target_nilai_dampak_q4"
              value="{{ number_format($rencana->target_nilai_dampak_q4, 0, ',', '.') }}" placeholder="" disabled>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Skala Dampak Q4</label>
            <input class="form-control" type="text" id="target_nilai_dampak_q4" name="target_nilai_dampak_q4"
              value="{{ $rencana->target_skala_dampak_q4 }}" placeholder="" disabled>
          </div>
          <div class="d-flex">
            <label class="label-start col-9 col-md-8">Target Nilai Probabilitas Q4</label>
            <input class="form-control" type="text" name="target_nilai_probabilitas_q4"
              value="{{ $rencana->target_nilai_probabilitas_q4 }}" placeholder="" id="target_nilai_probabilitas_q4"
              disabled>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_probabilitas_q4"
              value="{{ $rencana->target_skala_probabilitas_q4 }}" id="target_skala_probabilitas_q4" placeholder=""
              oninput="validateInputsQ4()" disabled>
            <label>Target Skala Probabilitas Q4</label>
          </div>
          <div class="form-floating text-center">
            <input class="form-control" type="text" name="target_skala_risiko_q4" id="target_skala_risiko_q4"
              value="{{ $rencana->target_skala_risiko_q4 }}" placeholder="" disabled>
            <label>Target Skala Risiko Q4</label>
          </div>
          <div class="form-floating level_risiko text-center">
            <input class="form-control" type="text" name="target_level_risiko_q4" id="target_level_risiko_q4"
              value="{{ $rencana->target_level_risiko_q4 }}" placeholder="" disabled>
            <label class="text-white">Target Level Risiko Q4</label>
          </div>
        </div>
      </div>
    </div>
  </div>
  {{--
  <div class="pt-3">
    <a href="{{ route('risk-register.index') }}" class="btn btn-primary">Kembali</a>
</div>
--}}
<div class="pt-3">
  <a href="{{ url()->previous() }}" class="btn btn-primary">Kembali</a>
</div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
  var current = 0;
  var tabs = $(".tab");
  var tabs_pill = $(".tab-pills");

  loadFormData(current);

  function loadFormData(n) {
    $(tabs_pill[n]).addClass("active");
    $(tabs[n]).removeClass("d-none");
    $("#back_button").prop("disabled", n === 0);
    $("#next_button").text(n === tabs.length - 1 ? "Kembali" : "Next");
  }

  function next() {
    if (current < tabs.length - 1) {
      $(tabs[current]).addClass("d-none");
      $(tabs_pill[current]).removeClass("active");

      current++;
      loadFormData(current);
    } else {
      // Jika sudah di tab terakhir, redirect ke route risk-register.index
      window.location.href = "{{ route('risk-register.index') }}";
    }
  }

  function back() {
    if (current > 0) {
      $(tabs[current]).addClass("d-none");
      $(tabs_pill[current]).removeClass("active");

      current--;
      loadFormData(current);
    }
  }

  // Menambahkan event listener untuk tombol "Next"
  $("#next_button").on("click", next);

  // Menambahkan event listener untuk tombol "Back"
  $("#back_button").on("click", back);
});
</script>
@endsection