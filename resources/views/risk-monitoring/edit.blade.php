@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row mb-5">
  <div class="col-12">
    <div class="ff-preheading">Risk Monitoring</div>
    <h2>Input Monitoring Form</h2>
    <h5>{{ $risiko->peristiwaRisiko->title }}</h5>
  </div>
</div>
<div class="row g-3">
  <div class="col-12 col-md-4">
    <div class="card bg-primary shadow text-white text-center border-0">
      <div class="card-body">
        <i class='bx bx-alarm-exclamation fs-1 mb-3 text-white'></i>
        <h4>Periode Monitoring</h4>
        <h3 class="mb-0">{{ $riskMonitoring->periode_monitoring }}</h3>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-8">
    <div class="card">
      <div class="card-header border-0 pb-0 d-flex align-items-center gap-3">
        <div class="lead__icon bg-soft-danger rounded-pill">
          <i class='bx bx-radar text-danger'></i>
        </div>
        <h4>Deskripsi Peristiwa Risiko</h4>
      </div>
      <div class="card-body ff-heading">
        {{ $risiko->deskripsi_peristiwa_risiko }}
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-body">
        <div class="table-responsive-sm">
          <table class="table" id="table-penyebab-risiko">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th>Penyebab Risiko</th>
                <th class="col-4">Progress Perlakuan Risiko</th>
                <th class="col-3">Tindakan</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($penyebabRisiko as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td>{{ $item->penyebab_risiko }}</td>
                <td>
                  @if ($riskMonitoring->periode_monitoring == 'Quarter 1')
                  {{ $item->progress_rencana_perlakuan_risiko_q1 }}
                  @elseif ($riskMonitoring->periode_monitoring == 'Quarter 2')
                  {{ $item->progress_rencana_perlakuan_risiko_q2 }}
                  @elseif ($riskMonitoring->periode_monitoring == 'Quarter 3')
                  {{ $item->progress_rencana_perlakuan_risiko_q3 }}
                  @elseif ($riskMonitoring->periode_monitoring == 'Quarter 4')
                  {{ $item->progress_rencana_perlakuan_risiko_q4 }}
                  @else
                  {{ '-' }}
                  @endif
                </td>
                <td class="white-space-nowrap">
                  {{-- <button class="btn btn-outline-primary status-btn" data-id="{{ $item->id }}">Status
                  KRI</button> --}}
                  <button class="btn btn-muted-primary btn-arrow-right py-0 ps-0 realisasi-btn"
                    data-id="{{ $item->id }}"
                    data-periode-monitoring="{{ $riskMonitoring->periode_monitoring }}">Realisasi</button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="table-responsive-sm">
          <table class="table" id="table-key-risk-indicator">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th>Key Risk Indicator</th>
                <th class="col-4">Kondisi Terkini</th>
                <th class="col-3">Tindakan</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($keyRiskIndicator as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td>{{ $item->kri }}</td>
                <td>
                  @if ($riskMonitoring->periode_monitoring == 'Quarter 1')
                  {{ $item->status_kri_terkini_q1 }}
                  @elseif ($riskMonitoring->periode_monitoring == 'Quarter 2')
                  {{ $item->status_kri_terkini_q2 }}
                  @elseif ($riskMonitoring->periode_monitoring == 'Quarter 3')
                  {{ $item->status_kri_terkini_q3 }}
                  @elseif ($riskMonitoring->periode_monitoring == 'Quarter 4')
                  {{ $item->status_kri_terkini_q4 }}
                  @else
                  {{ '-' }}
                  @endif
                </td>
                <td class="white-space-nowrap">
                  <button class="btn btn-muted-primary btn-arrow-right py-0 ps-0 status-btn status-kri-btn"
                    data-id="{{ $item->id }}" data-periode-monitoring="{{ $riskMonitoring->periode_monitoring }}">Status
                    KRI</button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <form class="col-12" method="POST" action="{{ route('risk-monitoring.update', $riskMonitoring) }}">
    @csrf
    @method('PUT')
    <div class="divider my-3 my-md-5">
      <div class="divider-text">
        <h4 class="mb-0 ff-heading-sm">Realisasi Nilai Risiko Residual</h4>
      </div>
    </div>
    <div class="row g-2">
      <div class="col-md-4">
        <div class="card btn-reveal-trigger">
          <div class="card-header d-flex align-items-center gap-2 pb-0 border-0">
            <div class="lead__icon bg-danger rounded-pill">
              <i class='bx bx-cube-alt text-white'></i>
            </div>
            <h5>Inherent</h5>
          </div>
          <div class="card-body d-flex flex-column gap-2">
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" name="nilai_dampak_inherent"
                value="{{ number_format($riskAnalysis->nilai_dampak, strpos($riskAnalysis->nilai_dampak, '.') !== false ? 2 : 0, ',', '.') }}">
              <label class="form-label" for="">Nilai Dampak Inherent</label>
            </div>
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" name="skala_dampak_inherent"
                value="{{ number_format($riskAnalysis->skala_dampak, strpos($riskAnalysis->skala_dampak, '.') !== false ? 2 : 0, ',', '.') }}">
              <label for="">Skala Dampak Inherent</label>
            </div>
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" name="nilai_probabilitas_inherent"
                value="{{ $riskAnalysis->nilai_probabilitas }}">
              <label for="">Nilai Probabilitas Inherent (%)</label>
            </div>
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" name="skala_probabilitas_inherent"
                value="{{ $riskAnalysis->skalaProbabilitas->tingkat." - " .$riskAnalysis->skalaProbabilitas->skala }}">
              <label for="">Skala Probabilitas Inherent</label>
            </div>
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" name="skala_risiko_inherent"
                value="{{ $riskAnalysis->skala_risiko }}">
              <label for="">Skala Risiko Inherent</label>
            </div>
            <div class="form-group pt-3">
              <p>Level Risiko Inherent: <span class="ff-heading fw-medium">{{ $riskAnalysis->level_risiko }}</strong>
              </p>
              <label
                class=" radio-label low-label {{ strtolower($riskAnalysis->level_risiko) == 'low' ? 'active' : '' }}"
                for="low"></label>
              <label
                class="radio-label low-medium-label {{ strtolower($riskAnalysis->level_risiko) == 'low to moderate' ? 'active' : '' }}"
                for="low-medium"></label>
              <label
                class="radio-label medium-label {{ strtolower($riskAnalysis->level_risiko) == 'moderate' ? 'active' : '' }}"
                for="medium"></label>
              <label
                class="radio-label medium-high-label {{ strtolower($riskAnalysis->level_risiko) == 'moderate to high' ? 'active' : '' }}"
                for="medium-high"></label>
              <label
                class="radio-label high-label {{ strtolower($riskAnalysis->level_risiko) == 'high' ? 'active' : '' }}"
                for="high"></label>
            </div>
          </div>
        </div>
      </div>
      @php
      $targetNilaiDampakKey = 'target_nilai_dampak_q' . substr($riskMonitoring->periode_monitoring, -1);
      $targetNilaiDampakValue = $riskPlan->$targetNilaiDampakKey ?? '0';

      $targetSkalaDampakKey = 'target_skala_dampak_q'.substr($riskMonitoring->periode_monitoring, -1);
      $targetSkalaDampakValue = $riskPlan->$targetSkalaDampakKey ?? '0';

      $targetNilaiProbabilitasKey = 'target_nilai_probabilitas_q'.substr($riskMonitoring->periode_monitoring, -1);
      $targetNilaiProbabilitasValue = $riskPlan->$targetNilaiProbabilitasKey ?? '0';

      $targetSkalaProbabilitasKey = 'target_skala_probabilitas_q'.substr($riskMonitoring->periode_monitoring, -1);
      $targetSkalaProbabilitasValue = $riskPlan->$targetSkalaProbabilitasKey ?? '0';

      $targetSkalaRisikoKey = 'target_skala_risiko_q'.substr($riskMonitoring->periode_monitoring, -1);
      $targetSkalaRisikoValue = $riskPlan->$targetSkalaRisikoKey ?? '0';

      $targetLevelRisikoKey = 'target_level_risiko_q'.substr($riskMonitoring->periode_monitoring, -1);
      $targetLevelRisikoValue = $riskPlan->$targetLevelRisikoKey ?? '0';

      @endphp
      <div class="col-md-4">
        <div class="card btn-reveal-trigger">
          <div class="card-header d-flex align-items-center gap-2 pb-0 border-0">
            <div class="lead__icon bg-warning rounded-pill">
              <i class='bx bx-cube text-white'></i>
            </div>
            <h5>Target Residual</h5>
          </div>
          <div class="card-body d-flex flex-column gap-2">
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" id="target_nilai_dampak"
                name="target_nilai_dampak"
                value="{{ number_format($targetNilaiDampakValue, strpos($targetNilaiDampakValue, '.') !== false ? 2 : 0, ',', '.') }}">
              <label for="">Target Nilai Dampak</label>
            </div>
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" id="target_skala_dampak"
                name="target_skala_dampak"
                value="{{ number_format($targetSkalaDampakValue, strpos($targetSkalaDampakValue, '.') !== false ? 2 : 0, ',', '.') }}">
              <label for="">Target Skala Dampak</label>
            </div>
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" id="target_nilai_probabilitas"
                name="target_nilai_probabilitas" value="{{ $targetNilaiProbabilitasValue }}">
              <label for="">Target Nilai Probabilitas (%)</label>
            </div>
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" id="target_skala_probabilitas"
                name="target_skala_probabilitas" value="{{ $targetSkalaProbabilitasValue }}">
              <label for="">Target Skala Probabilitas</label>
            </div>
            <div class="form-floating">
              <input disabled="disabled" class="form-control" type="text" id="target_skala_risiko"
                name="target_skala_risiko" value="{{ $targetSkalaRisikoValue }}">
              <label for="">Target Skala Risiko</label>
            </div>
            <div class="form-group pt-3">
              <p>Target Level Risiko: <span class="ff-heading fw-medium">{{ $targetLevelRisikoValue }}</span>
              </p>
              <label class="radio-label low-label {{ strtolower($targetLevelRisikoValue) == 'low' ? 'active' : '' }}"
                for="low"></label>
              <label
                class="radio-label low-medium-label {{ strtolower($targetLevelRisikoValue) == 'low to moderate' ? 'active' : '' }}"
                for="low-medium"></label>
              <label
                class="radio-label medium-label {{ strtolower($targetLevelRisikoValue) == 'moderate' ? 'active' : '' }}"
                for="medium"></label>
              <label
                class="radio-label medium-high-label {{ strtolower($targetLevelRisikoValue) == 'moderate to high' ? 'active' : '' }}"
                for="medium-high"></label>
              <label class="radio-label high-label {{ strtolower($targetLevelRisikoValue) == 'high' ? 'active' : '' }}"
                for="high"></label>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card btn-reveal-trigger">
          <div class="card-header d-flex align-items-center gap-2 pb-0 border-0">
            <div class="lead__icon bg-info rounded-pill">
              <i class='bx bxs-cube text-white'></i>
            </div>
            <h5>Realisasi</h5>
          </div>
          <div class="card-body d-flex flex-column gap-2">
            <div class="form-floating">
              <input class="form-control" type="text" id="realisasi_nilai_dampak" name="realisasi_nilai_dampak"
                value="{{ $riskMonitoring->realisasi_nilai_dampak }}" data-max="{{ $riskAnalysis->nilai_dampak }}">
              <label for="">Realisasi Nilai Dampak</label>
            </div>
            <div class="form-floating">
              <select class="form-select js-select-hide-search" name="realisasi_skala_dampak"
                id="realisasi_skala_dampak" oninput="calculateRealisasi()">
                <option selected disabled>Skala Dampak</option>
                @foreach($skalaDampak as $id => $tingkat)
                <option value="{{ $id }}" {{ $riskMonitoring->realisasi_skala_dampak == $id ? 'selected' : '' }}>
                  {{ $tingkat }}</option>
                @endforeach
              </select>
              <label for="">Realisasi Skala Dampak</label>
            </div>
            <div class="form-floating">
              <input class="form-control" type="text" id="realisasi_nilai_probabilitas"
                name="realisasi_nilai_probabilitas" value="{{ $riskMonitoring->realisasi_nilai_probabilitas }}"
                data-max="{{ $riskAnalysis->nilai_probabilitas }}">
              <label for="">Realisasi Nilai Probabilitas (%)</label>
            </div>
            <div class="form-floating">
              <input class="form-control" name="realisasi_skala_probabilitas" type="text" placeholder=""
                value="{{ $riskMonitoring->realisasi_skala_probabilitas }}" id="realisasi_skala_probabilitas"
                oninput="calculateRealisasi()" readonly />
              <label for="">Realisasi Skala Probabilitas</label>
            </div>
            <div class="form-floating">
              <input class="form-control" type="text" name="realisasi_skala_risiko" id="realisasi_skala_risiko"
                value="{{ $riskMonitoring->realisasi_skala_risiko }}" placeholder="" readonly>
              <label for="">Realisasi Skala Risiko (%)</label>
            </div>
            <div class="form-floating">
              <input class="form-control" name="realisasi_level_risiko" id="realisasi_level_risiko" type="text"
                placeholder="" value="{{ $riskMonitoring->realisasi_level_risiko }}" readonly />
              <label for="">Realisasi Level Risiko</label>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 d-flex mt-4 gap-1">
        <button type="submit" class="btn btn-submit">Simpan</button>
        <a href="{{ route('risk-monitoring.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </div>
  </form>
</div>

<!--=============== Modal Status KRI ===============-->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex flex-between-center">
        <h4 class="modal-title">Status KRI Terkini</h4>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form id="statusForm" action="{{ route('risk-monitoring.updateStatusKri', ['id' => ':id']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body d-flex flex-column gap-4">
          <input type="hidden" name="id" id="kriId">
          <input type="hidden" name="mrid" id="mrid">
          <div class="form-floating">
            <input type="text" disabled="disabled" name="key_risk_indicator" class="form-control"
              placeholder="Key Risk Indicator" id="key_risk_indicator">
            <label for="penyebab_risiko">Key Risk Indicator</label>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <div class="form-floating text-center">
                <input class="form-control text-center alert-success" disabled="disabled" name="batas_aman"
                  id="batas_aman" rows="3" placeholder="Batas Aman">
                <label for="batas_aman">Batas Aman</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating text-center">
                <input class="form-control alert-warning" disabled="disabled" name="batas_waspada" id="batas_waspada"
                  rows="3" placeholder="Batas Waspada">
                <label for="batas_waspada">Batas Waspada</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating text-center">
                <input class="form-control text-center alert-danger" disabled="disabled" name="batas_bahaya"
                  id="batas_bahaya" rows="3" placeholder="Batas Bahaya">
                <label for="batas_bahaya">Batas Bahaya</label>
              </div>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-lg-6">
              <div class="form-floating">
                <input type="text" class="form-control" name="nilai_kri" placeholder="Nilai KRI" id="nilai_kri">
                <label for="nilai_kri" class="form-label">Nilai KRI</label>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-floating">
                <select class="form-select" name="status_kri" id="status_kri">
                  <option value="" selected disabled>---</option>
                  <option value="Aman">Aman</option>
                  <option value="Waspada">Waspada</option>
                  <option value="Bahaya">Bahaya</option>
                </select>
                <label for="status_kri">Status KRI</label>
              </div>
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
<!--============= Modal Rencana Perlakuan Risiko =============-->
<div class="modal fade" id="realisasiModal" tabindex="-1" role="dialog" aria-labelledby="realisasiModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex flex-between-center">
        <h4 class="modal-title">Realisasi Perlakuan Risiko</h4>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form id="realisasiForm" action="{{ route('risk-monitoring.updateRealisasi', ['id' => ':id']) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body d-flex flex-column gap-4">
          <input type="hidden" name="id" id="realisasiId">
          <input type="hidden" name="mrid2" id="mrid2">
          <div class="form-floating">
            <input type="text" disabled="disabled" name="penyebab_risiko" class="form-control" id="penyebab_risiko2">
            <label for="penyebab_risiko">Penyebab Risiko</label>
          </div>
          <div class="form-floating">
            <textarea class="form-control" disabled="disabled" name="rencana_perlakuan_risiko"
              id="rencana_perlakuan_risiko2" rows="3"></textarea>
            <label for="rencana_perlakuan_risiko">Rencana Perlakuan Risiko</label>
          </div>
          <div class="form-floating">
            <textarea class="form-control" name="progress_rencana_perlakuan_risiko"
              placeholder="Progress Rencana Perlakuan Risiko" id="progress_rencana_perlakuan_risiko"
              rows="3"></textarea>
            <label for="progress_rencana_perlakuan_risiko">Progress Perlakuan Risiko</label>
          </div>
          <div class="form-floating">
            <input type="text" class="form-control" name="biaya_perlakuan_risiko" placeholder="Biaya Perlakuan Risiko"
              id="biaya_perlakuan_risiko">
            <label for="biaya_perlakuan_risiko">Biaya Perlakuan Risiko</label>
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
$(document).ready(function() {
  $('#realisasi_nilai_probabilitas').on('input', function() {
    var nilaiProbabilitas = $(this).val();

    // Kirim permintaan AJAX untuk mendapatkan data skala probabilitas
    $.ajax({
      url: "{{ route('get.realisasi.skala.probabilitas') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        realisasi_nilai_probabilitas: nilaiProbabilitas,
        type_risiko: "{{ $risiko->type }}" // Tambahkan data type_risiko jika diperlukan
      },
      success: function(response) {
        // Mengisi nilai skala probabilitas jika respons tidak kosong
        if (response) {
          $('#realisasi_skala_probabilitas').val(response.tingkat + ' - ' + response.skala);
          // Atur nilai skala risiko berdasarkan nilai skala probabilitas yang diperoleh
          calculateRealisasi();
        }
      },
      error: function(xhr, status, error) {
        // Tangani kesalahan jika terjadi
        console.error(xhr.responseText);
      }
    });
  });
});

// Ambil data dari PHP dan susun ke dalam array JavaScript
var level_risiko = {!! json_encode($level_risiko) !!};
var nilai_risiko = {!! json_encode($nilai_risiko) !!};

function calculateRealisasi() {
  var skala_dampak_value = parseFloat(document.getElementById('realisasi_skala_dampak').value);
  var skala_probabilitas_value = parseFloat(document.getElementById('realisasi_skala_probabilitas').value);

  if (!isNaN(skala_dampak_value) && !isNaN(skala_probabilitas_value)) {
    var result = level_risiko[skala_dampak_value][skala_probabilitas_value];
    var result2 = nilai_risiko[skala_dampak_value][skala_probabilitas_value];

    document.getElementById('realisasi_level_risiko').value = result;
    document.getElementById('realisasi_skala_risiko').value = result2;
  }
}

function initializeMonitoring() {
  document.getElementById('realisasi_skala_dampak').addEventListener('change', calculateMonitoring);
  document.getElementById('realisasi_skala_probabilitas').addEventListener('change', calculateMonitoring);
}

document.addEventListener('DOMContentLoaded', initializeMonitoring);
</script>
<script>
$(document).ready(function() {
  $('.status-kri-btn').click(function() {
    var id = $(this).data('id');
    var periode_monitoring = $(this).data('periode-monitoring');
    console.log("ID yang diambil:", id);
    console.log("Periode Monitoring:", periode_monitoring);

    $.get("{{ url('risk-monitoring') }}" + '/' + id + '/' + periode_monitoring + '/editStatusKri', function(
      data) {
      $('#kriId').val(data.id);
      $('#key_risk_indicator').val(data.kri);
      $('#batas_aman').val(data.batas_aman);
      $('#batas_waspada').val(data.batas_waspada);
      $('#batas_bahaya').val(data.batas_bahaya);
      //get from quarters
      $('#nilai_kri').val(data.nilai_kri);
      $('#status_kri').val(data.status_kri);
      $('#mrid').val(data.mrid);
      $('#statusModal').modal('show');
    });

  });



  $('.realisasi-btn').click(function() {
    var id = $(this).data('id');
    var periode_monitoring = $(this).data('periode-monitoring');
    console.log("ID yang diambil:", id);
    $.get("{{ url('risk-monitoring') }}" + '/' + id + '/' + periode_monitoring + '/editRealisasi', function(
      data) {
      $('#realisasiId').val(data.id);
      $('#penyebab_risiko2').val(data.penyebab_risiko);
      $('#rencana_perlakuan_risiko2').val(data.rencana_perlakuan_risiko);
      $('#progress_rencana_perlakuan_risiko').val(data.progress_rencana_perlakuan_risiko);
      $('#biaya_perlakuan_risiko').val(data.realisasi_biaya_perlakuan_risiko);
      $('#mrid2').val(data.mrid);
      $('#realisasiModal').modal('show');
    });
  });

  $('#statusForm').submit(function(e) {
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
        if (response.status == 200) {
          $('#statusModal').modal('hide');
          location.reload();
        } else {
          alert('Terjadi Kesalahan');
        }
      },
      error: function(error) {
        console.log(error);
      }
    });
  });

  $('#realisasiForm').submit(function(e) {
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
        if (response.status == 200) {
          $('#realisasiModal').modal('hide');
          location.reload();
        } else {
          alert('Terjadi Kesalahan');
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
function validateInputs() {
  var inputIds = ['realisasi_nilai_dampak', 'realisasi_nilai_probabilitas'];
  var selectIds = ['realisasi_skala_dampak'];

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
      select.value = maxValue;
      alert("Value exceeds maximum allowable limit.");
    }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('realisasi_nilai_dampak').addEventListener('input', validateInputs);
  document.getElementById('realisasi_nilai_probabilitas').addEventListener('input', validateInputs);
  document.getElementById('realisasi_skala_dampak').addEventListener('change', validateInputs);
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const rupiahFields = document.querySelectorAll("#realisasi_nilai_dampak");
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
@endsection
