@extends('layouts.default')
@php
if (!function_exists('formatKriBatasJs')) {
    function formatKriBatasJs($value) {
        if ($value === null || $value === '') return '-';
        // Cek apakah data murni angka atau desimal dari DB (contoh: 100, 15.50)
        if (preg_match('/^-?\d+(\.\d+)?$/', trim($value))) {
            return number_format((float)$value, 2, ',', '.');
        }
        // Jika ada huruf/simbol, kembalikan string aslinya
        return $value;
    }
}
@endphp
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Input Monitor Risiko</h3>
        </div>
    </div>

    <form class="row g-3" method="POST" id="main-form">
        <input type="hidden" name="draft_key" value="{{ request()->draft_key }}">
        @csrf

        <div class="col-md-8">
            <div class="card">
                <div class="card-header stepper border-0 pb-0">
                    <div class="nav-link active d-flex align-items-center p-0">
                        <span class="h3 mb-0">Deskripsi Peristiwa Risiko</span>
                    </div>
                </div>
                <div class="card-body">
                    {{ $risk->deskripsi_peristiwa_risiko ?: '-' }}
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card bg-primary shadow text-white text-center border-0">
                <div class="card-body">
                    <i class='bx bx-alarm-exclamation fs-1 mb-3 text-white'></i>
                    <h4>Periode Monitoring</h4>
                    <h3 class="mb-0">Quarter {{ $quarter }} - {{ __('basic.month.' . $month) }}</h3>
                    <input type="hidden" name="periode_monitoring" value="{{ $quarter }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm">Nilai Risiko Residual Realisasi</h4>
                </div>
            </div>
            <div class="alert alert-info d-flex align-items-center mb-4 py-2 border-0 shadow-sm" role="alert">
                <i class='bx bx-info-circle fs-4 me-2'></i>
                <div>
                    Informasi Risk Limit: <strong>Rp {{ number_format($riskLimit ?? 0, 0, ',', '.') }}</strong>
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
                                value="Rp {{ number_format($riskAnalysis->nilai_dampak, strpos($riskAnalysis->nilai_dampak, '.') !== false ? 2 : 0, ',', '.') }}">
                                <label class="form-label" for="">Nilai Dampak Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_dampak_inherent"
                                value="{{ $riskAnalysis->skalaDampakObj ? $riskAnalysis->skalaDampakObj?->tingkat . ' - ' . $riskAnalysis->skalaDampakObj?->deskripsi : '-' }}">
                                <label for="">Skala Dampak Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="nilai_probabilitas_inherent"
                                value="{{ $riskAnalysis->nilai_probabilitas }}">
                                <label for="">Nilai Probabilitas Inherent (%)</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_probabilitas_inherent"
                                value="{{ $riskAnalysis->skalaProbabilitas?->tingkat." - " .$riskAnalysis->skalaProbabilitas?->skala }}">
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
                <div class="col-md-4">
                    <div class="card btn-reveal-trigger">
                        <div class="card-header d-flex align-items-center gap-2 pb-0 border-0">
                            <div class="lead__icon bg-warning rounded-pill">
                                <i class='bx bx-cube text-white'></i>
                            </div>
                            <h5>Residual Rencana</h5>
                        </div>
                        <div class="card-body d-flex flex-column gap-2">
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="nilai_dampak_inherent"
                                value="Rp {{ number_format($riskAnalysis->{'nilai_dampak_residual_q' . $quarter}, strpos($riskAnalysis->{'nilai_dampak_residual_q' . $quarter}, '.') !== false ? 0 : 0, ',', '.') }}">
                                <label for="">Target Nilai Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_skala_dampak"
                                name="target_skala_dampak"
                                value="{{ $riskAnalysis->{'skalaDampakResidualQ' . $quarter . 'Obj'} ? $riskAnalysis->{'skalaDampakResidualQ' . $quarter . 'Obj'}->tingkat . ' - ' . $riskAnalysis->{'skalaDampakResidualQ' . $quarter . 'Obj'}->deskripsi : '-' }}">
                                <label for="">Target Skala Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_nilai_probabilitas"
                                name="target_nilai_probabilitas" value="{{ $riskAnalysis->{'nilai_probabilitas_residual_q' . $quarter} }}">
                                <label for="">Target Nilai Probabilitas (%)</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_probabilitas_inherent"
                                value="{{ $riskAnalysis->{'skalaProbabilitasResidualQ' . $quarter} ? $riskAnalysis->{'skalaProbabilitasResidualQ' . $quarter}->tingkat . ' - ' . $riskAnalysis->{'skalaProbabilitasResidualQ' . $quarter}->deskripsi : '-' }}">
                                <label for="">Target Skala Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_skala_risiko"
                                name="target_skala_risiko" value="{{ $riskAnalysis->{'skala_risiko_residual_q' . $quarter} }}">
                                <label for="">Target Skala Risiko</label>
                            </div>
                            <div class="form-group pt-3">
                                <p>Target Level Risiko: <span class="ff-heading fw-medium">{{ $riskAnalysis->{'level_risiko_residual_q' . $quarter} }}</span>
                                </p>
                                <label
                                class=" radio-label low-label {{ strtolower($riskAnalysis->{'level_risiko_residual_q' . $quarter}) == 'low' ? 'active' : '' }}"
                                for="low"></label>
                                <label
                                class="radio-label low-medium-label {{ strtolower($riskAnalysis->{'level_risiko_residual_q' . $quarter}) == 'low to moderate' ? 'active' : '' }}"
                                for="low-medium"></label>
                                <label
                                class="radio-label medium-label {{ strtolower($riskAnalysis->{'level_risiko_residual_q' . $quarter}) == 'moderate' ? 'active' : '' }}"
                                for="medium"></label>
                                <label
                                class="radio-label medium-high-label {{ strtolower($riskAnalysis->{'level_risiko_residual_q' . $quarter}) == 'moderate to high' ? 'active' : '' }}"
                                for="medium-high"></label>
                                <label
                                class="radio-label high-label {{ strtolower($riskAnalysis->{'level_risiko_residual_q' . $quarter}) == 'high' ? 'active' : '' }}"
                                for="high"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" id="section-realisasi">
                    <div class="card btn-reveal-trigger">
                        <div class="card-header d-flex align-items-center gap-2 pb-0 border-0">
                            <div class="lead__icon bg-info rounded-pill">
                                <i class='bx bxs-cube text-white'></i>
                            </div>
                            <h5>Residual Realisasi</h5>
                        </div>
                        <div class="card-body d-flex flex-column gap-2">
                            <div class="form-floating">
                                <input
                                  class="form-control update-trigger inputmask-rupiah"
                                  type="text"
                                  id="realisasi_nilai_dampak"
                                  name="realisasi_nilai_dampak"
                                  value="{{ $riskAnalysis->kategori_dampak == 'Kualitatif' ? '0' : ($riskMonitoring?->nilai_dampak ?: '0') }}"
                                  {{ $riskAnalysis->kategori_dampak == 'Kualitatif' ? 'disabled' : '' }}
                                  {{-- {{ $riskAnalysis->kategori_dampak == 'Kuantitatif' ? 'max=' . $riskAnalysis->nilai_dampak : '' }} --}}
                                  min="0"
                                  {{-- oninput="if(this.value > {{ $riskAnalysis->nilai_dampak }} && '{{ $riskAnalysis->kategori_dampak }}' === 'Kuantitatif') this.value = {{ $riskAnalysis->nilai_dampak }};" --}}
                                  required
                                >
                                <label for="">Realisasi Nilai Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input type="hidden" name="realisasi_skala_dampak" id="realisasi_skala_dampak_hidden">
                                <select
                                  class="form-select js-select-hide-search update-trigger"
                                  name="realisasi_skala_dampak"
                                  id="realisasi_skala_dampak"
                                >
                                  <option selected disabled>Skala Dampak</option>
                                  @foreach($skalaDampaks as $tingkat => $deskripsi)
                                  <option value="{{ $tingkat }}"
                                      {{ $riskMonitoring?->skala_dampak == $tingkat ? 'selected' : '' }}
                                      {{ $tingkat > $riskAnalysis->skalaDampakObj?->tingkat ? 'disabled' : '' }}>
                                      {{ $tingkat }} - {{ $deskripsi }}
                                      {{ $tingkat > $riskAnalysis->skalaDampakObj?->tingkat ? '(Melebihi Skala Inherent)' : '' }}
                                  </option>
                                  @endforeach
                                </select>
                                <label for="">Realisasi Skala Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input
                                  class="form-control update-trigger"
                                  type="number"
                                  id="realisasi_nilai_probabilitas"
                                  name="realisasi_nilai_probabilitas"
                                  value="{{ $riskMonitoring?->nilai_probabilitas }}"
                                  {{-- data-max="{{ $riskAnalysis->nilai_probabilitas }}"  --}}
                                  {{-- max="{{ $riskAnalysis->nilai_probabilitas }}" --}}
                                  {{-- oninput="if(this.value > {{ $riskAnalysis->nilai_probabilitas }}) this.value = {{ $riskAnalysis->nilai_probabilitas }};" --}}
                                  data-max="100"
                                  max="100"
                                  min="0"
                                  oninput="if(this.value > 100) this.value = 100;"
                                >
                                <label for="">Realisasi Nilai Probabilitas (%)</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control" name="realisasi_skala_probabilitas" type="text" placeholder=""
                                value="{{ $riskMonitoring?->skala_probabilitas }}" id="realisasi_skala_probabilitas"
                                readonly />
                                <input type="hidden" name="realisasi_skala_probabilitas_hidden" id="realisasi_skala_probabilitas_hidden">
                                <label for="">Realisasi Skala Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control" type="text" name="realisasi_skala_risiko" id="realisasi_skala_risiko" placeholder="" readonly>
                                <input type="hidden" name="realisasi_skala_risiko_hidden" id="realisasi_skala_risiko_hidden">
                                <label for="">Realisasi Skala Risiko</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control" name="realisasi_level_risiko" id="realisasi_level_risiko" type="text"
                                placeholder="" readonly />
                                <input type="hidden" name="realisasi_level_risiko_hidden" id="realisasi_level_risiko_hidden">
                                <label for="">Realisasi Level Risiko</label>
                            </div>
                            <div class="form-floating mt-2">
                                <input class="form-control inputmask-rupiah" type="text" name="realisasi_eksposur_risiko" id="realisasi_eksposur_risiko" placeholder="" readonly>
                                <label for="">Realisasi Eksposur Risiko</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm">Realisasi Perlakuan Risiko</h4>
                </div>
            </div>

            {{-- Tambahkan card untuk legend/keterangan --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted">Keterangan :</span>
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-edit-alt text-primary"></i>
                                <span>Update Realisasi</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-chart text-primary"></i>
                                <span>Update Parameter / KRI</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-2">Perlakuan terhadap Dampak Risiko</h5>
                        <table class="table table-bordered" id="table-dampak-risiko">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Dampak Risiko</th>
                                    <th>Rencana Perlakuan</th>
                                    <th>Biaya Perlakuan</th>
                                    <th>Progress (%)</th>
                                    <th>Realisasi Biaya</th>
                                    <th>Waktu Realisasi</th>
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalBiayaDampak = 0; @endphp
                                @foreach ($risk->dampakRisikos as $dampak)
                                    @php
                                        $perlakuans = $dampak->perlakuanDampakRisikos->where('dampak_risiko_id', $dampak->id);
                                        $rowSpan = max($perlakuans->count(), 1);
                                    @endphp

                                    @foreach ($perlakuans->isEmpty() ? [null] : $perlakuans as $perlakuan)
                                        @if ($loop->index == 0)
                                            <tr data-id="{{ $perlakuan?->id }}">
                                                <td rowspan="{{ $rowSpan }}">{{ $loop->parent->iteration }}</td>
                                                <td rowspan="{{ $rowSpan }}">{{ $dampak->dampak_risiko }}</td>
                                        @else
                                            <tr data-id="{{ $perlakuan->id }}">
                                        @endif

                                        @if($perlakuan)
                                            @php $totalBiayaDampak += $perlakuan->biaya_perlakuan_risiko ?? 0; @endphp
                                            <td>{{ $perlakuan->rencana_perlakuan_risiko ?: '-' }}</td>
                                            <td>
                                                <span class="inputmask-fixed">
                                                    {{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : '-' }}
                                                </span>
                                            </td>
                                            <td class="display-progress inputmask-fixed">
                                                {{ $perlakuan->lastMonitoring?->progress_rencana_perlakuan_risiko ?? '-' }}
                                            </td>
                                            <td class="display-biaya inputmask-fixed">
                                                {{ $perlakuan->lastMonitoring?->realisasi_biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->lastMonitoring->realisasi_biaya_perlakuan_risiko, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="display-timeline">
                                                {{ $perlakuan->lastMonitoring?->timeline_perlakuan_risiko_start?->format('d/m/Y') ?: '-' }}
                                            </td>
                                            <td class="column-action-impact">
                                                <div class="d-none dom-saved-impact">
                                                    <div class="upload-container-impact"></div>
                                                    <input type="hidden" class="input-file-description-impact">
                                                </div>
                                                <div class="text-center">
                                                    <a href="javascript:void(0)"
                                                      class="btn-input-icon btn-action"
                                                      data-action="update-realisasi-dampak"
                                                      data-id="{{ $perlakuan->id }}"
                                                      data-dampak-text="{{ $dampak->dampak_risiko }}">
                                                        <span class="bx bx-edit-alt text-primary"></span>
                                                    </a>
                                                </div>
                                            </td>
                                        @else
                                            <td colspan="6" class="text-center text-muted italic">Belum ada rencana perlakuan</td>
                                        @endif
                                        </tr>
                                    @endforeach
                                @endforeach

                                @if($risk->dampakRisikos->isEmpty())
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data dampak risiko</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <h5 class="mt-6 mb-2">Perlakuan terhadap Penyebab Risiko</h5>
                        <table class="table" id="table-penyebab-risiko">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Penyebab Risiko</th>
                                    <th>Rencana Perlakuan</th>
                                    <th>Biaya Perlakuan</th>
                                    <th>Progress (%)</th>
                                    <th>Realisasi Biaya</th>
                                    <th>Waktu Realisasi</th>
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($risk->penyebabRisikos as $penyebabRisiko)
                                    @php
                                        $rowSpan = $penyebabRisiko->perlakuanPenyebabRisiko->count() ?: 1;
                                        $outLoop = $loop;
                                    @endphp
                                    @foreach ($penyebabRisiko->perlakuanPenyebabRisiko as $perlakuan)
                                        @php
                                            // $lastMonitoring = $riskMonitoring?->perlakuanPenyebabMonitorings->where('perlakuan_penyebab_risiko_unit_id', $perlakuan->id)->first();
                                        @endphp
                                        @if ($loop->index == 0)
                                        <tr data-id="{{ $perlakuan?->id }}">
                                            <td rowspan="{{ $rowSpan }}">{{ $outLoop->iteration }}</td>
                                            <td rowspan="{{ $rowSpan }}">{{ $penyebabRisiko->penyebab_risiko ?: '-' }}</td>
                                        @else
                                        <tr data-id="{{ $perlakuan->id }}">
                                        @endif
                                            <td>{{ $perlakuan->rencana_perlakuan_risiko ?: '-' }}</td>
                                            <td>
                                              <span class="inputmask-fixed">
                                                {{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') :  '-' }}
                                              </span>
                                            </td>
                                            <td class="display-progress inputmask-fixed">
                                                {{ $perlakuan->{'progress_rencana_perlakuan_risiko_q' . $quarter} ?? '-' }}
                                            </td>
                                            <td class="display-biaya inputmask-fixed">
                                                {{ $perlakuan->{'realisasi_biaya_perlakuan_risiko_q' . $quarter} ? 'Rp ' . number_format($perlakuan->{'realisasi_biaya_perlakuan_risiko_q' . $quarter}, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="display-timeline">
                                              {{ $perlakuan?->lastMonitoring?->timeline_perlakuan_risiko_start?->format('d/m/Y') ?: '-' }}
                                            </td>
                                            <td style="white-space:nowrap" class="column-action">
                                                <div class="d-none dom-saved">
                                                    <div class="upload-container">
                                                    </div>
                                                    <input type="textarea" class="input-file-description" name="document_description_{{ $perlakuan->id }}" id="deskripsi_perlakuan_risiko_{{ $perlakuan->id }}">
                                                </div>
                                                <div class="text-center">
                                                    <a href="javascript:void(0)"
                                                    class="btn-input-icon btn-action"
                                                    data-action="update-realisasi"
                                                    data-bs-toggle="tooltip"
                                                    title="Update Realisasi"
                                                    data-id="{{ $perlakuan->id }}">
                                                        <span class="bx bx-edit-alt text-primary"></span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach

                                @if($risk->penyebabRisikos->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <h5 class="mt-6 mb-2">Perlakuan terhadap Paramter / KRI</h5>
                        <table class="table table-bordered align-middle" id="table-kri">
                            <thead class="bg-light small fw-bold text-center">
                                <tr>
                                    <th rowspan="2" class="align-middle">#</th>
                                    <th rowspan="2" class="align-middle">Key Risk Indicator</th>
                                    <th rowspan="2" class="align-middle">Tren Parameter</th>
                                    <th rowspan="2" class="align-middle">Metode Pengukuran</th>
                                    <th colspan="3">Ambang Batas / Threshold</th>
                                    <th rowspan="2" class="align-middle">Nilai Realisasi</th>
                                    <th rowspan="2" class="align-middle">Status</th>
                                    <th rowspan="2" class="align-middle">Action</th>
                                </tr>
                                <tr>
                                    <th class="align-middle">Risk Limit</th>
                                    <th class="align-middle">Risk Appetite</th>
                                    <th class="align-middle">Risk Tolerance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($risk->kris as $kriProject)
                                    @php
                                        $lastMonitoring = $riskMonitoring?->kriUnitMonitorings->where('key_risk_indicator_id', $kriProject->id)->first();
                                    @endphp
                                    <tr data-id="{{ $kriProject->id }}">
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $kriProject->kri ?: '-' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info">{{ $kriProject->tren_parameter ?? '-' }} </span>
                                        </td>
                                        <td>
                                            {{ $kriProject->metode_pengukuran ?? '-' }}
                                        </td>
                                        <td class="text-center text-nowrap">{{ formatKriBatasJs($kriProject->batas_aman) }} {{ $kriProject->satuan_kri ?: '-' }}</td>
                                        <td class="text-center text-nowrap">{{ formatKriBatasJs($kriProject->batas_waspada) }} {{ $kriProject->satuan_kri ?: '-' }}</td>
                                        <td class="text-center text-nowrap">{{ formatKriBatasJs($kriProject->batas_bahaya) }} {{ $kriProject->satuan_kri ?: '-' }}</td>
                                        
                                        <td class="display-nilai-kri fw-bold text-center text-primary">
                                            {{ $lastMonitoring?->nilai_kri_terkini ?? '-' }} {{ $kriProject->satuan_kri ?: '-' }}
                                        </td>
                                        <td class="display-kondisi text-center">
                                            @php
                                                $statusMap = [1 => 'Aman', 2 => 'Siaga', 3 => 'Bahaya'];
                                                $statusColor = [1 => 'success', 2 => 'warning', 3 => 'danger'];
                                                $status = $lastMonitoring?->status_kri_terkini;
                                            @endphp
                                            <span class="badge bg-{{ $statusColor[$status] ?? 'light text-dark border' }} p-2">
                                                {{ $statusMap[$status] ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="javascript:void(0)"
                                              class="btn-input-icon btn-action"
                                              data-action="update-kri"
                                              data-bs-toggle="tooltip"
                                              title="Update Parameter / KRI"
                                              data-id="{{ $kriProject->id }}">
                                                <span class="bx bx-chart text-primary"></span>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text d-flex align-items-center justify-content-between cursor-pointer" data-bs-toggle="collapse" data-bs-target="#logPerlakuanRisiko" aria-expanded="false">
                    <h4 class="mb-0 ff-heading-sm">Log Perlakuan Risiko</h4>
                    <span class="toggle-text ms-2"><i class='bx bx-chevron-down'></i> Show Log</span>
                </div>
            </div>
            <div class="row g-2 collapse" id="logPerlakuanRisiko">
                <div class="card">
                    <div class="card-body">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Penyebab Risiko</th>
                                    <th>Tanggal Input</th>
                                    <th>Waktu Perlakuan Risiko</th>
                                    <th>PIC</th>
                                    <th>Rencana Perlakuan Risiko</th>
                                    <th>Rencana Biaya Perlakuan Risiko (Rp)</th>
                                    <th>Deskripsi Perlakuan Risiko</th>
                                    <th>Realisasi Biaya Perlakuan Risiko (Rp)</th>
                                    <th>Progress (%)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($risk->penyebabRisikos as $penyebabRisiko)
                                @php
                                    $perlakuanPenyebabMonitorings = $penyebabRisiko->perlakuanPenyebabRisiko->pluck('perlakuanPenyebabMonitorings')->flatten();
                                @endphp

                                @foreach ($penyebabRisiko->perlakuanPenyebabRisiko as $perlakuanPenyebab)
                                    @foreach ($perlakuanPenyebab->perlakuanPenyebabMonitorings as $perlakuanMonitoring)
                                        <tr>
                                            <td>{{ $loop->iteration + 1 }}</td>
                                            <td>{{ $penyebabRisiko->penyebab_risiko }}</td>
                                            <td>{{ $perlakuanMonitoring->created_at }}</td>
                                            <td>{{ $perlakuanMonitoring->timeline_perlakuan_risiko_start }}</td>
                                            <td>{{ $perlakuanPenyebab->pic }}</td>
                                            <td>{{ $perlakuanPenyebab->rencana_perlakuan_risiko }}</td>
                                            <td>{{ $perlakuanPenyebab->biaya_perlakuan_risiko }}</td>
                                            <td>{{ $perlakuanMonitoring->deskripsi_perlakuan_risiko }}</td>
                                            <td>{{ $perlakuanMonitoring->realisasi_biaya_perlakuan_risiko }}</td>
                                            <td>{{ $perlakuanMonitoring->progress_rencana_perlakuan_risiko }}</td>
                                            <td>
                                                <button type="button"
                                                    class="btn-input-icon btn-action"
                                                    data-action="view-details"
                                                    data-bs-toggle="tooltip"
                                                    title="Detail Mitigasi"
                                                    data-perlakuan-id="{{ $perlakuanPenyebab->id }}"
                                                    data-id="{{ $perlakuanMonitoring->id }}">
                                                     <span class="bx bx-show text-primary"></span>
                                            </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-5">
            <div class="row g-2">
                <div class="col-auto">
                    <a href="{{ route('risk-register-ap.monitorings.index', ['unit_id' => $risk->unit_id, 'period' => request()->route('period'), 'quarter' => $quarter, 'month' => $month]) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
                <div class="col-auto">
                    <button type="button" data-action="save" class="btn btn-primary ms-auto btn-action">Simpan</button>
                </div>
                <div class="col-auto ms-auto">
                    <button type="button" data-action="save-and-close" class="btn btn-danger btn-action">Simpan dan Close Risiko</button>
                </div>
            </div>
        </div>
    </form>

    @include('risk-register-ap.monitorings._modal_kri')
    @include('risk-register-unit.monitorings._modal_penyebab')
    @include('risk-register-unit.monitorings._modal_mitigasi')
    @include('risk-register-unit.monitorings._modal_update_realisasi_dampak')
@endsection

@push('styles')
<style>
    .hover-underline:hover {
        text-decoration: underline;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
// Fungsi untuk memformat angka menjadi format Rupiah
function formatRupiah(angka, prefix = 'Rp ') {
    if (angka === null || angka === undefined || angka === '' || angka === '-') {
        return '-';
    }

    // Pastikan angka dalam bentuk string dan hapus semua karakter non-numerik
    let number_string = angka.toString().replace(/[^,\d]/g, '');

    // Jika angka sudah dalam format dengan titik sebagai pemisah ribuan, hapus titiknya
    number_string = number_string.replace(/\./g, '');

    // Pisahkan desimal jika ada
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    // Tambahkan titik untuk ribuan
    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    // Gabungkan dengan desimal jika ada
    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

    // Tambahkan prefix Rp
    return prefix + rupiah;
}

// Fungsi untuk mengkonversi format Rupiah kembali ke angka
function parseRupiah(rupiahString) {
    if (!rupiahString || rupiahString === '-') {
        return 0;
    }

    // Hapus semua karakter kecuali angka dan koma
    return parseFloat(rupiahString.replace(/[^\d,]/g, '').replace(/\./g, '').replace(',', '.')) || 0;
}

const risk = @json($risk);
const penyebabRisikoProjects = @json($risk->penyebabRisikos->keyBy('id'));
const jsonPerlakuanPenyebabRisikos = @json($risk->penyebabRisikos->pluck('perlakuanPenyebabRisiko')->flatten()->keyBy('id'));
const perlakuanPenyebabRisikos = Object.fromEntries(
    Object.entries(jsonPerlakuanPenyebabRisikos).map(([key, value]) => {
        return [
            key,
            {
                ...value,
                "realisasi_biaya_perlakuan_risiko": value?.last_monitoring?.realisasi_biaya_perlakuan_risiko ? parseFloat(value?.last_monitoring?.realisasi_biaya_perlakuan_risiko) : 0.00,
                "progress_rencana_perlakuan_risiko": value?.last_monitoring?.progress_rencana_perlakuan_risiko ?? 0,
                "deskripsi_perlakuan_risiko": value?.last_monitoring?.deskripsi_perlakuan_risiko ?? "",
                "timeline_perlakuan_risiko": value?.last_monitoring?.timeline_perlakuan_risiko_start ?? ""
            }
        ];
    })
);

const jsonPerlakuanDampakRisikos = @json($risk->perlakuanDampakRisikos->keyBy('id'));
const perlakuanDampakRisikos = Object.fromEntries(
    Object.entries(jsonPerlakuanDampakRisikos).map(([key, value]) => {
        return [
            key,
            {
                ...value,
                "realisasi_biaya_perlakuan_risiko": value?.last_monitoring?.realisasi_biaya_perlakuan_risiko ? parseFloat(value?.last_monitoring?.realisasi_biaya_perlakuan_risiko) : 0.00,
                "progress_rencana_perlakuan_risiko": value?.last_monitoring?.progress_rencana_perlakuan_risiko ?? 0,
                "deskripsi_perlakuan_risiko": value?.last_monitoring?.deskripsi_perlakuan_risiko ?? "",
                "timeline_perlakuan_risiko": value?.last_monitoring?.timeline_perlakuan_risiko_start ?? ""
            }
        ];
    })
);

const kriProjects = @json($risk->kris->keyBy('id'));
const quarter = {{ $quarter }};
const riskMonitoring = @json($riskMonitoring);
const namaRisiko = @json($risk->peristiwa_risiko);
const kriPengendalians = @json($kriPengendalians ?? '{}');

// Flatpickr untuk penyebab
var flatpickrIns = flatpickr("#timelineInput", {
    mode: "single",
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    minDate: risk ? dayjs(risk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
    // maxDate: dayjs().toDate(),
    disableMobile: true
});

$("#timelineInput").data('_flatpickr', flatpickrIns);

// Flatpickr untuk Dampak
var impactFlatpickr = flatpickr("#timelineImpactInput", {
    altInput: true,
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    minDate: risk ? dayjs(risk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
    // maxDate: dayjs().toDate(),
    disableMobile: true
});

function formatKriBatasJS(value) {
    if (value === null || value === undefined || value === '') return '-';
    
    // Ubah ke string dan hapus spasi di awal/akhir (padanan trim() di JS)
    let valStr = String(value).trim();
    
    // Regex padanan preg_match() di JS untuk ngecek apakah murni angka/desimal
    if (/^-?\d+(\.\d+)?$/.test(valStr)) {
        // Jika angka, ubah titik (format DB) menjadi koma agar sesuai dengan UI
        return valStr.replace('.', ',');
    }
    
    // Jika ada teks/simbol (misal: "< 10%"), kembalikan apa adanya
    return valStr;
}

function getSkalaProbabilitasByValue(value) {
    const skalaProbabilitases = @json($skalaProbabilitas);
    for (index in skalaProbabilitases) {
        skalaProbabilitas = skalaProbabilitases[index];
        if (value >= skalaProbabilitas.min) {
            return skalaProbabilitas;
        }
    }
}

function refreshSkalaAndLevelRisiko() {
    const riskMaps = @json($riskMaps);
    const nilaiDampak = parseFloat($('#realisasi_nilai_dampak').val());
    const nilaiProbabilitas = parseFloat($('#realisasi_nilai_probabilitas').val());
    let skalaDampak = parseFloat($('#realisasi_skala_dampak').val());
    if (isNaN(skalaDampak)) {
        skalaDampak = parseFloat($('#realisasi_skala_dampak_hidden').val());
    }
    console.log('skalaDampak : ' + skalaDampak);
    const skalaProbabilitas = getSkalaProbabilitasByValue(nilaiProbabilitas);

    const domSkalaProbabilitas = $('#realisasi_skala_probabilitas');
    const domSkalaRisiko = $('#realisasi_skala_risiko');
    const domLevelRisiko = $('#realisasi_level_risiko');

    console.log('skalaProbabilitas : ' + skalaProbabilitas);

    if (skalaProbabilitas) {
        domSkalaProbabilitas.val(skalaProbabilitas.tingkat + ' - ' + skalaProbabilitas.skala);
        $('#realisasi_skala_probabilitas_hidden').val(skalaProbabilitas.tingkat);
    } else {
        domSkalaProbabilitas.val('');
        $('#realisasi_skala_probabilitas_hidden').val('');
    }

    console.log('skalaDampak : ' + skalaDampak);
    console.log('skalaProbabilitas : ' + skalaProbabilitas?.tingkat);
    const riskMap = riskMaps[skalaDampak + '-' + (skalaProbabilitas?.tingkat)];
    console.log('riskMap : ' + riskMap);
    if  (riskMap) {
        domSkalaRisiko.val(riskMap.nilai_risiko);
        domLevelRisiko.val(riskMap.level_risiko);
        $('#realisasi_skala_risiko_hidden').val(riskMap.nilai_risiko);
        $('#realisasi_level_risiko_hidden').val(riskMap.level_risiko);
    } else {
        domSkalaRisiko.val('');
        domLevelRisiko.val('');
        $('#realisasi_skala_risiko_hidden').val('');
        $('#realisasi_level_risiko_hidden').val('');
    }
}

function refreshEksposurRisiko() {
    const kategoriDampak = '{{ $riskAnalysis->kategori_dampak }}';
    const riskLimit = parseFloat('{{ $riskLimit }}') || 0;
    const nilaiProbabilitas = parseFloat($('#realisasi_nilai_probabilitas').val()) || 0;
    let eksposurVal = 0;

    if (kategoriDampak === 'Kuantitatif') {
        // Parse format inputmask rupiah jadi angka murni
        let nilaiDampakStr = $('#realisasi_nilai_dampak').val().replace(/[^0-9,-]/g, '').replace(',', '.');
        let nilaiDampak = parseFloat(nilaiDampakStr) || 0;
        
        eksposurVal = nilaiDampak * (nilaiProbabilitas / 100);
    } else if (kategoriDampak === 'Kualitatif') {
        let skalaDampak = parseFloat($('#realisasi_skala_dampak').val());
        if (isNaN(skalaDampak)) {
            skalaDampak = parseFloat($('#realisasi_skala_dampak_hidden').val()) || 0;
        }
        
        eksposurVal = skalaDampak * (1 / 100) * (nilaiProbabilitas / 100) * riskLimit;
    }

    // Set valuenya ke form (inputmask akan auto format)
    $('#realisasi_eksposur_risiko').val(eksposurVal);
}

function convertDateFormat(dateStr) {
  const [year, month, day] = dateStr.split('-');
  return `${day}/${month}/${year}`;
}

function validateRealisasiForm() {
    let isValid = true;
    let firstErrorField = null;

    const fieldsToValidate = [
        '#realisasi_nilai_dampak',
        '#realisasi_skala_dampak',
        '#realisasi_nilai_probabilitas'
    ];

    fieldsToValidate.forEach(function(fieldSelector) {
        const field = $(fieldSelector);

        if (field.is(':disabled')) {
            // Hapus error sebelumnya jika ada
            field.removeClass('is-invalid');
            field.closest('.form-floating').find('.invalid-feedback').remove();
            return; // Lanjut ke field berikutnya
        }

        field.removeClass('is-invalid');
        field.closest('.form-floating').find('.invalid-feedback').remove();

        if (field.val() === '' || field.val() === null) {
            isValid = false;
            field.addClass('is-invalid');
            field.closest('.form-floating').append('<div class="invalid-feedback d-block">Field ini wajib diisi.</div>');

            if (firstErrorField === null) {
                firstErrorField = field;
            }
        }

    });

    if (!isValid && firstErrorField) {
    $('html, body').animate({
            scrollTop: firstErrorField.offset().top - 150
        }, 500);

        firstErrorField.focus();
    }

    return isValid;
}

function submitForm(isClosed) {
    $('.dom-edited').remove();

    const formData = new FormData($('#main-form')[0]);
    formData.append('perlakuan_penyebab_risikos', JSON.stringify(perlakuanPenyebabRisikos));
    formData.append('perlakuan_dampak_risikos', JSON.stringify(perlakuanDampakRisikos));
    formData.append('kri_projects', JSON.stringify(kriProjects));
    formData.append('quarter', quarter);
    formData.append('_method', 'PUT');

    // Append new data for isClosed
    formData.append('is_closed', isClosed);

    $.ajax({
        url: '{{ route('risk-register-ap.monitorings.update', ['period' => request()->route('period'), 'monitoring' => request()->route('monitoring')]) }}',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            Swal.fire({
                title: 'Berhasil',
                text: response.message,
                icon: 'success',
                confirmButtonText: 'OK',
            }).then(() => {
                window.location.href = '{!! route('risk-register-ap.monitorings.index', ['period' => request()->route('period'), 'unit_id' => $risk?->unit_id, 'quarter' => $quarter, 'month' => $month]) !!}';
            });
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: 'OK',
            });
        }
    });
}

$(document).ready(function() {
    // Simpan nilai skala dampak inherent dan probabilitas inherent
    const skalaDampakInherent = {{ $riskAnalysis->skalaDampakObj?->tingkat ?? 0 }};
    const nilaiProbabilitasInherent = {{ $riskAnalysis->nilai_probabilitas ?? 0 }};

    // Validasi saat memilih skala dampak
    // $('#realisasi_skala_dampak').on('change', function(e) {
    //     // Skip validasi jika perubahan dari hitungRealisasiSkalaDampak()
    //     if (e.originalEvent === undefined) return;

    //     const selectedValue = parseInt($(this).val());
    //     if (selectedValue > skalaDampakInherent) {
    //         Swal.fire({
    //             title: 'Peringatan',
    //             text: 'Nilai skala dampak realisasi tidak boleh lebih besar dari skala dampak inherent',
    //             icon: 'warning',
    //             confirmButtonText: 'OK'
    //         });
    //         $(this).val('').trigger('change');
    //     }
    // });

    // Validasi nilai probabilitas
    // $('#realisasi_nilai_probabilitas').on('change', function() {
    //     const value = parseFloat($(this).val());
    //     if (value > nilaiProbabilitasInherent) {
    //         Swal.fire({
    //             title: 'Peringatan',
    //             text: 'Nilai probabilitas realisasi tidak boleh lebih besar dari nilai probabilitas inherent',
    //             icon: 'warning',
    //             confirmButtonText: 'OK'
    //         });
    //         $(this).val(nilaiProbabilitasInherent).trigger('change');
    //     }
    // });

    $('#section-realisasi').on('change', '.update-trigger', function() {
        refreshSkalaAndLevelRisiko();
        refreshEksposurRisiko();
    }).change();

    // Toggle logic for log section
    $('#logPerlakuanRisiko').on('show.bs.collapse', function () {
        const toggle = $(this).prev('.divider').find('.toggle-text');
        toggle.html("<i class='bx bx-chevron-up'></i> Hide Log");
    }).on('hide.bs.collapse', function () {
        const toggle = $(this).prev('.divider').find('.toggle-text');
        toggle.html("<i class='bx bx-chevron-down'></i> Show Log");
    });

    // Listener Toggle Dropdown Modal KRI
    $('#status_kri_select').on('change', function() {
        const val = $(this).val();
        if (val === '2' || val === '3') {
            $('#kri-pengendalian-section').removeClass('d-none');
        } else {
            $('#kri-pengendalian-section').addClass('d-none');
        }
    });

    $('.btn-action').on('click', function() {
        const action = $(this).data('action');
        if (action === 'save' || action === 'save-and-close') {
            // validasi terlebih dahulu
            // if (!validateRealisasiForm()) {
            //     return;
            // }

            const isClosing = (action === 'save-and-close');
            const swalConfig = {
                title: 'Konfirmasi',
                text: isClosing
                    ? `Apakah Anda yakin ingin menyimpan dan menutup risiko "${namaRisiko}" ini?`
                    : 'Apakah Anda yakin ingin menyimpan data ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
            };

            Swal.fire(swalConfig).then((result) => {
                if (result.isConfirmed) {
                    submitForm(isClosing ? 1 : 0);
                }
            });
        } else if (action === 'update-kri') {
            const kriProject = kriProjects[$(this).data('id')];
            const latestMonitoring = riskMonitoring?.kri_unit_monitorings?.find(m => m.key_risk_indicator_id == kriProject.id);
            const kriPeng = kriPengendalians ? (kriPengendalians[kriProject.id] || {}) : {};
            let satuan = kriProject.satuan_kri ? ' ' + kriProject.satuan_kri : '';

            // Deteksi apakah format baru
            const isNewFormat = kriProject.tren_parameter && kriProject.tren_parameter.trim() !== '';
            $('#modalUpdateKri').data('is-new-format', isNewFormat);

            $('#modalUpdateKri input[name="kri_project_id"]').val($(this).data('id'));
            $('#modalUpdateKri input[name="key_risk_indicator"]').val(kriProject.kri);
            $('#modalUpdateKri input[name="batas_aman"]').val(formatKriBatasJS(kriProject.batas_aman) + satuan);
            $('#modalUpdateKri input[name="batas_waspada"]').val(formatKriBatasJS(kriProject.batas_waspada) + satuan);
            $('#modalUpdateKri input[name="batas_bahaya"]').val(formatKriBatasJS(kriProject.batas_bahaya) + satuan);

            // Populate Tren & Metode
            $('#modalUpdateKri input[name="kri_tren_parameter"]').val(kriProject.tren_parameter || '-');
            $('#modalUpdateKri textarea[name="kri_metode_pengukuran"]').val(kriProject.metode_pengukuran || '-');

            // Set Data Monitoring Sebelumnya
            let valKri = kriProject['nilai_kri_terkini_q' + quarter] || latestMonitoring?.nilai_kri_terkini || '';
            let statusVal = kriProject['status_kri_terkini_q' + quarter] || latestMonitoring?.status_kri_terkini || '';
            $('#modal_satuan_addon').text(kriProject.satuan_kri || '-');

            if (valKri) {
                valKri = valKri.toString().replace('.', ',');
            }
            
            $('#modalUpdateKri input[name="nilai_kri"]').val(valKri);
            $('#modalUpdateKri select[name="status_kri"]').val(statusVal).trigger('change');

            // Field Pengendalian
            $('#modalUpdateKri [name="kri_rencana_pengendalian"]').val(kriProject.rencana_pengendalian || kriPeng.rencana_pengendalian || '');
            $('#modalUpdateKri [name="kri_biaya_rencana_pengendalian"]').val(kriProject.biaya_rencana_pengendalian || kriPeng.biaya_rencana_pengendalian || 0);
            $('#modalUpdateKri [name="kri_realisasi_pengendalian"]').val(kriProject.realisasi_pengendalian || kriPeng.realisasi_pengendalian || '');
            $('#modalUpdateKri [name="kri_biaya_realisasi_pengendalian"]').val(kriProject.biaya_realisasi_pengendalian || kriPeng.biaya_realisasi_pengendalian || 0);

            $('#modalUpdateKri').modal('show');
        } else if (action === 'update-realisasi') {
            const perlakuanPenyebab = perlakuanPenyebabRisikos[$(this).data('id')];
            if (!perlakuanPenyebab) {
                Swal.fire('Error', 'Data perlakuan penyebab risiko tidak ditemukan', 'error');
                return;
            }
            const penyebabRisiko = penyebabRisikoProjects[perlakuanPenyebab.penyebab_risiko_id];
            $('#modalUpdateRealisasi :input[name="penyebab_risiko_id"]').val($(this).data('id'));
            $('#modalUpdateRealisasi :input[name="penyebab_risiko"]').val(penyebabRisiko.penyebab_risiko);
            $('#modalUpdateRealisasi :input[name="rencana_perlakuan_risiko"]').val(perlakuanPenyebab.rencana_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="biaya_perlakuan_risiko"]').val(perlakuanPenyebab.biaya_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="pic"]').val(perlakuanPenyebab.pic);
            $('#modalUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val(perlakuanPenyebab['realisasi_biaya_perlakuan_risiko_q' + quarter] || '');
            $('#modalUpdateRealisasi :input[name="progress_perlakuan_risiko"]').val
            (perlakuanPenyebab['progress_rencana_perlakuan_risiko_q' + quarter] || '');

            const form = $('#formUpdateRealisasi');
            form.find('[name="deskripsi_perlakuan_risiko"]').val(perlakuanPenyebab['deskripsi_perlakuan_risiko'] || '');

            // Isi data realisasi sebelumnya
            const latestMonitoring = riskMonitoring?.perlakuan_penyebab_monitorings
                ?.sort((a, b) => b.id - a.id)[0];

            if (latestMonitoring) {
                // Pastikan nilai yang diambil adalah nilai numerik murni
                //let realisasiBiaya = parseFloat(latestMonitoring.realisasi_biaya_perlakuan_risiko.replace(/[^\d]/g, '')) || 0;
                let realisasiBiaya = 0;
                if (latestMonitoring.realisasi_biaya_perlakuan_risiko) {
                    // Jika menggunakan titik sebagai pemisah ribuan (format Indonesia)
                    if (latestMonitoring.realisasi_biaya_perlakuan_risiko.includes('.') &&
                        latestMonitoring.realisasi_biaya_perlakuan_risiko.split('.').length > 2) {
                        // Format dengan titik sebagai pemisah ribuan (mis: 2.000.000)
                        realisasiBiaya = parseFloat(latestMonitoring.realisasi_biaya_perlakuan_risiko.replace(/\./g, '').replace(',', '.'));
                    } else {
                        // Format dengan titik sebagai pemisah desimal (mis: 2000.00)
                        realisasiBiaya = parseFloat(latestMonitoring.realisasi_biaya_perlakuan_risiko);
                    }
                }
                console.log(realisasiBiaya);
                // Simpan nilai asli di hidden field
                $('#previous_realisasi_biaya_value').val(realisasiBiaya);

                // Format untuk ditampilkan di UI
                $('#previous_realisasi_biaya').text(formatRupiah(realisasiBiaya));

                let progress = parseFloat(latestMonitoring.progress_rencana_perlakuan_risiko) || 0;
                $('#previous_progress').text(progress + '%');
                $('#previous_progress_value').val(progress);
            } else {
                $('#previous_realisasi_biaya').text('Belum ada realisasi');
                $('#previous_realisasi_biaya_value').val(0);

                $('#previous_progress').text('Belum ada progress');
                $('#previous_progress_value').val(0);
            }

            $('#modalUpdateRealisasi :input[name="jenis_program_rkap"]').val(perlakuanPenyebab.jenis_program_rkap);
            $('#modalUpdateRealisasi :input[name="jenis_program_rkap_id"]').val(perlakuanPenyebab.jenis_program_rkap_id);
            $('#modalUpdateRealisasi :input[name="deskripsi_perlakuan_risiko"]').val(perlakuanPenyebab.deskripsi_perlakuan_risiko);

            // if (perlakuanPenyebab.timeline_perlakuan_risiko?.length === 2) {
            //     // console.log(perlakuanPenyebab.timeline_perlakuan_risiko[0]);
            //     $("#timelineInput").data('_flatpickr').setDate(perlakuanPenyebab.timeline_perlakuan_risiko[0]);
            // } else if (perlakuanPenyebab.timeline_perlakuan_risiko) {
            //     $("#timelineInput").data('_flatpickr').setDate(perlakuanPenyebab.timeline_perlakuan_risiko);
            // } else {
            //     $("#timelineInput").data('_flatpickr').clear();
            // }
            if (perlakuanPenyebab?.timeline_perlakuan_risiko) {
                flatpickrIns.setDate(dayjs(perlakuanPenyebab.timeline_perlakuan_risiko).format('DD/MM/YYYY'));
            } else {
                flatpickrIns.clear();
            }

            $('#modalUpdateRealisasi').modal('show');

            const tableDocument = $('#modalUpdateRealisasi .table-dokumen');

            tableDocument.empty();

            const domCell = $('#table-penyebab-risiko tr[data-id="'+$(this).data('id')+'"] td.column-action');
            const domSaved = domCell.find('.dom-saved');
            domCell.find('.dom-edited').remove();
            const domEdited = domSaved.clone().addClass('dom-edited').removeClass('dom-saved');
            domCell.append(domEdited);

            const documentDescriptions = domEdited.find('.input-file-description').val() ? JSON.parse(domEdited.find('.input-file-description').val()) : {};
            domEdited.find('input[type=file]').each(function() {
                const fileName = $(this).prop('files')[0]?.name;
                const id = $(this).prop('id');
                const description = documentDescriptions[id] || '';
                const appended = tableDocument.append(`
                    <tr data-id="${id}">
                    <td>
                        <span class="dokumen-filename">${fileName}</span>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="deskripsi_dokumen[]" placeholder="Deskripsi dokumen" value="${description}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-link btn-sm text-danger delete-btn">Hapus</button>
                    </td>
                </tr>
                `);
            });

            if (tableDocument.find('tr').length > 2) {
                tableDocument.closest('table').find('tfoot').hide();
            } else {
                tableDocument.closest('table').find('tfoot').show();
            }

            tableDocument.on('input', '[name="deskripsi_dokumen[]"]', function() {
                const value = $(this).val();
                const id = $(this).closest('tr').data('id');
                const penyebabRisikoId = $('#formUpdateRealisasi :input[name="penyebab_risiko_id"]').val();
                const domCell = $('#table-penyebab-risiko tr[data-id="'+penyebabRisikoId+'"] td.column-action');
                const domEdited = domCell.find('.dom-edited');

                console.log(id, domCell);

                const domDeskripsi = domEdited.find('.input-file-description');
                console.log(domDeskripsi.length);
                const deskripsi = domDeskripsi.val() ? JSON.parse(domDeskripsi.val()) : {};
                deskripsi[id] = value;
                domDeskripsi.val(JSON.stringify(deskripsi));
            });

            tableDocument.on('click', '.delete-btn', function() {
                const id = $(this).closest('tr').data('id');
                const penyebabRisikoId = $('#formUpdateRealisasi :input[name="penyebab_risiko_id"]').val();
                const domCell = $('#table-penyebab-risiko tr[data-id="'+penyebabRisikoId+'"] td.column-action');
                const domEdited = domCell.find('.dom-edited');
                $(this).closest('tr').remove();
                domEdited.find(`#${id}`).remove();
                if (tableDocument.find('tr').length < 3) {
                    tableDocument.closest('table').find('tfoot').show();
                }
            });

            $('#modalUpdateRealisasi').modal('show');
        } else if (action === 'view-details') {
            const id = $(this).data('id');
            const perlakuanId = $(this).data('perlakuan-id');
            const perlakuanPenyebab = perlakuanPenyebabRisikos[perlakuanId];
            const penyebabRisiko = penyebabRisikoProjects[perlakuanPenyebab.penyebab_risiko_id];
            const perlakuanMonitoring = perlakuanPenyebab?.perlakuan_penyebab_monitorings?.find(m => m.id == id);

            if (!perlakuanMonitoring) {
                Swal.fire('Error', 'Data perlakuan penyebab risiko tidak ditemukan', 'error');
                return;
            }

            $('#modalMitigasi :input[name="penyebab_risiko_id"]').val(perlakuanPenyebab.id);
            $('#modalMitigasi :input[name="penyebab_risiko"]').val(penyebabRisiko.penyebab_risiko);
            $('#modalMitigasi :input[name="rencana_perlakuan_risiko"]').val(perlakuanPenyebab.rencana_perlakuan_risiko);
            $('#modalMitigasi :input[name="biaya_perlakuan_risiko"]').val(perlakuanPenyebab.biaya_perlakuan_risiko);
            $('#modalMitigasi :input[name="pic"]').val(perlakuanPenyebab.pic);

            $('#modalMitigasi :input[name="realisasi_biaya_perlakuan_risiko"]').val(perlakuanMonitoring.realisasi_biaya_perlakuan_risiko);
            $('#modalMitigasi :input[name="progress_perlakuan_risiko"]').val(perlakuanMonitoring.progress_rencana_perlakuan_risiko);
            $('#modalMitigasi :input[name="deskripsi_perlakuan_risiko"]').val(perlakuanMonitoring.deskripsi_perlakuan_risiko);
            $('#modalMitigasi :input[name="jenis_program_rkap_id"]').val(perlakuanMonitoring.jenis_program_rkap_id).change();
            $('#modalMitigasi :input[name="timeline_perlakuan_risiko"]').val(perlakuanMonitoring.timeline_perlakuan_risiko_start ? Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }).format(new Date(perlakuanMonitoring.timeline_perlakuan_risiko_start)) : '');

            const perlakuanDocuments = perlakuanPenyebab.documents.filter(d => d.unit_risk_monitoring_id == perlakuanMonitoring.unit_risk_monitoring_id);

            $('.tabel-dokumen-mitigasi tbody').empty();
            perlakuanDocuments.forEach(function(document) {
                const appended = $('.tabel-dokumen-mitigasi tbody').append(`
                    <tr>
                        <td>
                            ${document.file_name}
                        </td>
                        <td>
                            ${document.description || '-'}
                        </td>
                        <td>
                            <a href="${document.url}" download="${document.file_name}">Download</a>
                        </td>
                    </tr>
                `);
            });

            if (perlakuanDocuments.length === 0) {
                $('.tabel-dokumen-mitigasi tbody').append(`
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada dokumen</td>
                    </tr>
                `);
            }

            $('#modalMitigasi').modal('show');
        }
    });

    $(document).on('click', '[data-action="update-realisasi-dampak"]', function() {
        const id = $(this).data('id');
        const perlakuan = perlakuanDampakRisikos[id];
        const dampakText = $(this).data('dampak-text');

        if (!perlakuan) return;

        // Mapping Data ke Modal
        $('#impact_id').val(id);
        $('#impact_name').val(dampakText);
        $('#impact_plan').val(perlakuan.rencana_perlakuan_risiko);
        $('#impact_cost').val('Rp ' + Intl.NumberFormat('id-ID').format(perlakuan.biaya_perlakuan_risiko));
        $('#impact_pic').val(perlakuan?.pic_jabatan?.name);

        $('#timeline_perlakuan_risiko_dampak_start').val(dayjs(perlakuan.timeline_perlakuan_risiko_start).format('DD/MM/YYYY'));
        $('#timeline_perlakuan_risiko_dampak_end').val(dayjs(perlakuan.timeline_perlakuan_risiko_end).format('DD/MM/YYYY'));

        // Load Realisasi Sebelumnya jika ada
        const lastMon = perlakuan.last_monitoring;
        const form = $('#formUpdateRealisasiDampak');

        form.find('[name="realisasi_biaya_dampak"]').val(lastMon?.realisasi_biaya_perlakuan_risiko ?? 0);
        form.find('[name="progress_dampak"]').val(lastMon?.progress_rencana_perlakuan_risiko ?? 0);
        form.find('[name="deskripsi_dampak"]').val(lastMon?.deskripsi_perlakuan_risiko ?? '');

        if(lastMon?.timeline_perlakuan_risiko_start) {
            impactFlatpickr.setDate(dayjs(lastMon.timeline_perlakuan_risiko_start).format('DD/MM/YYYY'));
        } else {
            impactFlatpickr.clear();
        }

        // Handle Dokumen (mirip penyebab tapi beda selector)
        const trImpact = $(`#table-dampak-risiko tr[data-id="${id}"]`);
        const domCell = trImpact.find('td.column-action-impact');
        const domSaved = domCell.find('.dom-saved-impact');
        domCell.find('.dom-edited-impact').remove();
        const domEdited = domSaved.clone().addClass('dom-edited-impact').removeClass('dom-saved-impact');
        domCell.append(domEdited);

        // Render ulang list dokumen di modal
        const tableDocument = $('#modalUpdateRealisasiDampak .table-dokumen-dampak');
        tableDocument.empty();

        // Ambil data deskripsi dari input hidden di baris tabel
        const domDeskripsiInput = domEdited.find('.input-file-description-impact');
        const documentDescriptions = domDeskripsiInput.val() ? JSON.parse(domDeskripsiInput.val()) : {};

        // Loop setiap input file yang sudah ada di dom-edited (file yang baru terpilih tapi belum di-save ke server)
        domEdited.find('input[type=file]').each(function() {
            const fileName = $(this).prop('files')[0]?.name;
            const fileId = $(this).prop('id');
            const description = documentDescriptions[fileId] || '';

            tableDocument.append(`
                <tr data-id="${fileId}">
                    <td><span class="dokumen-filename text-truncate d-block" style="max-width: 200px;">${fileName}</span></td>
                    <td>
                        <input type="text" class="form-control form-control-sm input-desc-impact"
                              placeholder="Keterangan..." value="${description}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-link btn-sm text-danger btn-delete-doc-impact">Hapus</button>
                    </td>
                </tr>
            `);
        });

        if (tableDocument.find('tr').length >= 3) {
            tableDocument.closest('table').find('tfoot').hide();
        } else {
            tableDocument.closest('table').find('tfoot').show();
        }

        $('#modalUpdateRealisasiDampak').modal('show');
    });

    $('#btnSimpanUpdateRealisasiDampak').on('click', function() {
        const id = $('#impact_id').val();
        const form = $('#formUpdateRealisasiDampak');

        if(!form[0].checkValidity()) { form[0].reportValidity(); return; }

        perlakuanDampakRisikos[id]['realisasi_biaya_perlakuan_risiko'] = form.find('[name="realisasi_biaya_dampak"]').val();
        perlakuanDampakRisikos[id]['progress_rencana_perlakuan_risiko'] = form.find('[name="progress_dampak"]').val();
        perlakuanDampakRisikos[id]['deskripsi_perlakuan_risiko'] = form.find('[name="deskripsi_dampak"]').val();
        perlakuanDampakRisikos[id]['timeline_perlakuan_risiko'] = form.find('[name="timeline_dampak"]').val();

        const tr = $(`#table-dampak-risiko tr[data-id="${id}"]`);
        tr.find('.display-biaya').text('Rp ' + Intl.NumberFormat('id-ID').format(perlakuanDampakRisikos[id]['realisasi_biaya_perlakuan_risiko']));
        tr.find('.display-progress').text(perlakuanDampakRisikos[id]['progress_rencana_perlakuan_risiko']);
        tr.find('.display-timeline').text(perlakuanDampakRisikos[id]['timeline_perlakuan_risiko']);

        tr.find('.dom-saved-impact').remove();
        tr.find('.dom-edited-impact').removeClass('dom-edited-impact').addClass('dom-saved-impact');

        $('#modalUpdateRealisasiDampak').modal('hide');
    });

    $('#btnSimpanUpdateRealisasi').on('click', function() {
        if (!$('#formUpdateRealisasi')[0].checkValidity()) {
            $('#formUpdateRealisasi')[0].reportValidity();
            return;
        }
        const id = $('#formUpdateRealisasi :input[name="penyebab_risiko_id"]').val();
        const realisasiBiayaPerlakuanRisiko = $('#formUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val();
        const progressPerlakuanRisiko = $('#formUpdateRealisasi :input[name="progress_perlakuan_risiko"]').val();
        const deskripsiPerlakuanRisiko = $('#formUpdateRealisasi :input[name="deskripsi_perlakuan_risiko"]').val();
        //const jenisProgramRkap = $('#formUpdateRealisasi :input[name="jenis_program_rkap"]').val();
        const jenisProgramRkapId = $('#formUpdateRealisasi :input[name="jenis_program_rkap_id"]').val();
        const jenisProgramRkap = $('#formUpdateRealisasi :input[name="jenis_program_rkap_id"] option:selected').text();
        const timelinePerlakuanRisiko = $('#formUpdateRealisasi :input[name="timeline_perlakuan_risiko"]').val();

        if (!timelinePerlakuanRisiko) {
            Swal.fire('Error', 'Timeline perlakuan risiko harus diisi', 'error');
            return;
        }

        // update perlakuan penyebab risiko
        perlakuanPenyebabRisikos[id]['realisasi_biaya_perlakuan_risiko_q' + quarter] = realisasiBiayaPerlakuanRisiko;
        perlakuanPenyebabRisikos[id]['progress_rencana_perlakuan_risiko_q' + quarter] = progressPerlakuanRisiko;
        perlakuanPenyebabRisikos[id]['deskripsi_perlakuan_risiko'] = deskripsiPerlakuanRisiko;
        perlakuanPenyebabRisikos[id]['jenis_program_rkap'] = jenisProgramRkap;
        perlakuanPenyebabRisikos[id]['jenis_program_rkap_id'] = jenisProgramRkapId;
        //perlakuanPenyebabRisikos[id]['timeline_perlakuan_risiko'] = timelinePerlakuanRisiko.split(' to ');
        perlakuanPenyebabRisikos[id]['timeline_perlakuan_risiko'] = timelinePerlakuanRisiko;

        // update DOM
        const tr = $('#table-penyebab-risiko tr[data-id="' + id + '"]');
        tr.find('.display-biaya').text('Rp' + Intl.NumberFormat('id-ID').format(realisasiBiayaPerlakuanRisiko));
        tr.find('.display-progress').text(progressPerlakuanRisiko);
        tr.find('.display-timeline').text(timelinePerlakuanRisiko);

        tr.find('.dom-saved').remove();
        tr.find('.dom-edited').removeClass('dom-edited').addClass('dom-saved');

        $('#modalUpdateRealisasi').modal('hide');
    });

    $('#btnSimpanUpdateKri').on('click', function() {
        if (!$('#formUpdateKri')[0].checkValidity()) {
            $('#formUpdateKri')[0].reportValidity();
            return;
        }

        const id = $('#formUpdateKri :input[name="kri_project_id"]').val();
        const nilaiKri = $('#formUpdateKri :input[name="nilai_kri"]').val();
        const statusKri = $('#formUpdateKri :input[name="status_kri"]').val();

        // Update kri object
        kriProjects[id]['nilai_kri_terkini_q' + quarter] = nilaiKri;
        kriProjects[id]['status_kri_terkini_q' + quarter] = statusKri;
        
        // Ambil data pengendalian
        if (statusKri === '2' || statusKri === '3') {
            kriProjects[id]['rencana_pengendalian'] = $('#formUpdateKri [name="kri_rencana_pengendalian"]').val();
            kriProjects[id]['biaya_rencana_pengendalian'] = $('#formUpdateKri [name="kri_biaya_rencana_pengendalian"]').val();
            kriProjects[id]['realisasi_pengendalian'] = $('#formUpdateKri [name="kri_realisasi_pengendalian"]').val();
            kriProjects[id]['biaya_realisasi_pengendalian'] = $('#formUpdateKri [name="kri_biaya_realisasi_pengendalian"]').val();
        }

        // Update DOM status badge
        const statusMap = {'1': 'Aman', '2': 'Siaga', '3': 'Bahaya'};
        const colorMap = {'1': 'success', '2': 'warning', '3': 'danger'};
        const statusText = statusMap[statusKri] || '-';
        const colorClass = colorMap[statusKri] || 'secondary';

        const satuanTeks = kriProjects[id]['satuan_kri'] ? ' ' + kriProjects[id]['satuan_kri'] : '';
        const tr = $('#table-kri tr[data-id="' + id + '"]');
        tr.find('.display-nilai-kri').text(nilaiKri);
        tr.find('.display-nilai-kri').text(`${nilaiKri} ${satuanTeks}`);
        tr.find('.display-kondisi').html(`<span class="badge bg-${colorClass} p-2">${statusText}</span>`);

        $('#modalUpdateKri').modal('hide');
    });

    const inputmaskFixeds = $('.inputmask-fixed');
    inputmaskFixeds.each(function() {
        if (!isNaN($(this).text())) {
            $(this).inputmask({
                alias: 'numeric',
                groupSeparator: '.',
                autoGroup: true,
                digits: 0,
                digitsOptional: false,
                placeholder: '0',
                rightAlign: false,
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
        }
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

    // Fungsi untuk menghitung skala dampak berdasarkan persentase
    function hitungSkalaDampak(percentage) {
        if (percentage <= 20) return 1;
        if (percentage <= 40) return 2;
        if (percentage <= 60) return 3;
        if (percentage <= 80) return 4;
        return 5;
    }

    // Fungsi untuk menghitung dan mengatur realisasi_skala_dampak
    function hitungRealisasiSkalaDampak() {
        const kategoriDampak = '{{ $riskAnalysis->kategori_dampak }}';
        //console.log(kategoriDampak);

        const nilaiDampak = parseFloat($('#realisasi_nilai_dampak').val()) || 0;
        const riskLimit = parseFloat('{{ $riskLimit }}') || 0;
        const skalaDampakSelect = $('#realisasi_skala_dampak');
        const skalaDampakHidden = $('#realisasi_skala_dampak_hidden');

        if (kategoriDampak === 'Kuantitatif') {
            console.log("risk limit : " + riskLimit);
            var percentage = 100;
            var skala = 5;

            if(riskLimit>0){
                // Hitung persentase
                percentage = (nilaiDampak / riskLimit) * 100;
                // Hitung skala berdasarkan persentase
                skala = hitungSkalaDampak(percentage);

                console.log("percentage : " + percentage);
                console.log("skala : " + skala);
            }

            // Set nilai skala dampak dan trigger change event
            skalaDampakSelect.val(skala).trigger('change');
            // Disable select dan pindahkan nilai ke hidden input
            skalaDampakSelect.prop('disabled', false);
            skalaDampakHidden.val(skala);
        } else {
            // Enable select jika bukan Kuantitatif
            skalaDampakSelect.prop('disabled', false);
            // Kosongkan hidden input
            skalaDampakHidden.val('0');
        }
    }

    /*
    $('#realisasi_skala_dampak').on('change', function() {

        const value = $(this).val();
        if (value) {
            $('#realisasi_skala_dampak_hidden').val(value);
        }

    });
    */

    // Event listener untuk perubahan nilai dampak
    $('#realisasi_nilai_dampak').on('change', function() {
        console.log("hitung skala dampak");
        hitungRealisasiSkalaDampak();
        refreshSkalaAndLevelRisiko();
        refreshEksposurRisiko();
    });

    $('#realisasi_skala_dampak').on('change', function() {
        console.log("update dari skala dampak");
        const value = $(this).val();
        if (value) {
            $('#realisasi_skala_dampak_hidden').val(value);
        }
        refreshSkalaAndLevelRisiko();
        refreshEksposurRisiko();
    });

    $('.datatable').DataTable({
        paging: true,
        info: false,
        searching: false,
        ordering: true,
        autoWidth: true,
        order: [[2, 'desc']],
        rowCallback: function(row, data, index) {
            $('td:eq(0)', row).html(index + 1); // Assign row index
        },
        columnDefs: [
            {
                width: '5%',
                targets: 0,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return meta.row + 1;
                }
            },
            {
                width: '15%',
                targets: 2,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return data ? new Date(data).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
                }
            },
            {
                width: '15%',
                targets: 3,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return data ? new Date(data).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';
                }
            },
            {
                width: '10%',
                targets: 6,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return 'Rp' + Intl.NumberFormat('id-ID').format(data);
                }
            },
            {
                width: '10%',
                targets: 8,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return 'Rp' + Intl.NumberFormat('id-ID').format(data);
                }
            },
            {
                width: '10%',
                targets: 9,
                render: function(data, type, row, meta) {
                    if (type !== 'display') {
                        return data;
                    }
                    return Intl.NumberFormat('id-ID').format(data);
                }
            },
        ],
    });

    // Panggil fungsi saat halaman dimuat
    // hitungRealisasiSkalaDampak();
    refreshSkalaAndLevelRisiko();
    refreshEksposurRisiko();
});
</script>
@endpush
