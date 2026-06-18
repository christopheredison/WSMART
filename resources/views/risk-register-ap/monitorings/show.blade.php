@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Monitor Risiko</h3>
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
                    <h4 class="mb-0 ff-heading-sm">Realisasi Perlakuan Risiko</h4>
                </div>
            </div>

            {{-- Tambahkan card untuk legend/keterangan --}}
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
                                    <th>Realisasi Perlakuan</th>
                                    <th>Progress (%)</th>
                                    <th>Realisasi Biaya</th>
                                    <th>Waktu Realisasi</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalBiayaDampak = 0; @endphp
                                @foreach ($risk->dampakRisikos as $dampak)
                                    @php
                                        $perlakuans = $risk->perlakuanDampakRisikos->where('dampak_risiko_id', $dampak->id);
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
                                                    {{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : 'Rp 0' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $perlakuan->lastMonitoring?->deskripsi_perlakuan_risiko ?? '-' }}
                                            </td>
                                            <td class="display-progress inputmask-fixed">
                                                {{ $perlakuan->lastMonitoring?->progress_rencana_perlakuan_risiko ?? '-' }}
                                            </td>
                                            <td class="display-biaya inputmask-fixed">
                                                {{ $perlakuan->lastMonitoring?->realisasi_biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->lastMonitoring->realisasi_biaya_perlakuan_risiko, 0, ',', '.') : 'Rp 0' }}
                                            </td>
                                            <td class="display-timeline">
                                                {{ $perlakuan->lastMonitoring?->timeline_perlakuan_risiko_start?->format('d/m/Y') ?: '-' }}
                                            </td>
                                            <td>
                                                  <button type="button" class="btn btn-sm btn-link lihat-file-btn" title="Lihat File" data-bs-toggle="tooltip">
                                                      <i class='bx bx-file fs-5'></i>
                                                  </button>
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
                                    <th>Realisasi Perlakuan</th>
                                    <th>Progress (%)</th>
                                    <th>Realisasi Biaya</th>
                                    <th>Waktu Realisasi</th>
                                    <th></th>
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
                                            $lastMonitoring = $riskMonitoring?->perlakuanPenyebabMonitorings->where('perlakuan_penyebab_risiko_unit_id', $perlakuan->id)->first();
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
                                                {{ isset($perlakuan->biaya_perlakuan_risiko) ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : 'Rp 0' }}
                                              </span>
                                            </td>
                                            <td>
                                                {{ $perlakuan?->deskripsi_perlakuan_risiko ?? '-' }}
                                            </td>
                                            <td class="display-progress inputmask-fixed">
                                                {{ $perlakuan->{'progress_rencana_perlakuan_risiko_q' . $quarter} ?? '-' }}
                                            </td>
                                            <td class="display-biaya inputmask-fixed">
                                                {{ isset($perlakuan->{'realisasi_biaya_perlakuan_risiko_q' . $quarter}) ? 'Rp ' . number_format($perlakuan->{'realisasi_biaya_perlakuan_risiko_q' . $quarter}, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="display-timeline">{{ $lastMonitoring?->timeline_perlakuan_risiko_start?->format('d/m/Y') ?: '-' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-link lihat-file-btn" title="Lihat File" data-bs-toggle="tooltip">
                                                    <i class='bx bx-file fs-5'></i>
                                                </button>
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

                        <h5 class="mt-6 mb-2">Perlakuan terhadap KRI</h5>
                        <table class="table" id="table-kri">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Key Risk Indicator</th>
                                    <th>Satuan KRI</th>
                                    <th>Batas Aman</th>
                                    <th>Batas Waspada</th>
                                    <th>Batas Bahaya</th>
                                    <th>Nilai KRI</th>
                                    <th>Kondisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($risk->kris as $kriProject)
                                    @php
                                        $lastMonitoring = $riskMonitoring?->kriUnitMonitorings->where('key_risk_indicator_id', $kriProject->id)->first();
                                    @endphp
                                    <tr data-id="{{ $kriProject->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $kriProject->kri ?: '-' }}</td>
                                        <td>{{ $kriProject->satuan_kri ?: '-' }}</td>
                                        <td>{{ $kriProject->batas_aman ?? '-' }}</td>
                                        <td>{{ $kriProject->batas_waspada ?? '-' }}</td>
                                        <td>{{ $kriProject->batas_bahaya ?? '-' }}</td>
                                        <td class="display-nilai-kri">
                                            {{ $lastMonitoring?->nilai_kri_terkini ?? '-' }}
                                        </td>
                                        <td class="display-kondisi">
                                            @php
                                                $statusMap = [
                                                    1 => 'Aman',
                                                    2 => 'Waspada',
                                                    3 => 'Bahaya',
                                                ];
                                                $status = $lastMonitoring?->status_kri_terkini;
                                                $displayStatus = $statusMap[$status] ?? '-';
                                            @endphp
                                            {{ $displayStatus }}
                                        </td>
                                    </tr>
                                @endforeach
                                @if($risk->kris->isEmpty())
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <h5 class="mt-6 mb-2">Peluang (Opportunity)</h5>
                        <table class="table table-bordered align-middle" id="table-peluang">
                            <thead class="bg-light small fw-bold">
                                <tr>
                                    <th width="5%">#</th>
                                    <th>Penjelasan Rencana</th>
                                    <th width="15%">Nilai Rencana</th>
                                    <th>Penjelasan Realisasi</th>
                                    <th width="15%">Nilai Realisasi</th>
                                    <th width="10%">Dokumen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($opportunities as $peluang)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $peluang->penjelasan_peluang_rencana ?: '-' }}</td>
                                        <td class="fw-medium text-dark">
                                            {{ $peluang->nilai_peluang_rencana ? 'Rp ' . number_format($peluang->nilai_peluang_rencana, 0, ',', '.') : 'Rp 0' }}
                                        </td>
                                        <td>{{ $peluang->penjelasan_peluang_realisasi ?: '-' }}</td>
                                        <td class="fw-bold text-success">
                                            {{ $peluang->nilai_peluang_realisasi ? 'Rp ' . number_format($peluang->nilai_peluang_realisasi, 0, ',', '.') : 'Rp 0' }}
                                        </td>
                                        <td class="text-center">
                                            @if($peluang->file_path)
                                                <a href="{{ asset('storage/' . $peluang->file_path) }}" target="_blank" class="btn btn-sm btn-link lihat-file-btn" data-bs-toggle="tooltip" title="Download Dokumen">
                                                    <span class="bx bx-download fs-5"></>
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted fst-italic py-3">Belum ada data peluang yang ditambahkan.</td>
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
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="eksposur_risiko_inherent"
                                value="{{ $riskAnalysis->eksposur_risiko ? 'Rp ' . number_format($riskAnalysis->eksposur_risiko, 0, ',', '.') : '-' }}">
                                <label for="">Eksposur Risiko Inherent</label>
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
                                <input disabled="disabled" class="form-control" type="text" name="nilai_dampak_residual"
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
                                <input disabled="disabled" class="form-control" type="text" id="target_eksposur_risiko"
                                name="target_eksposur_risiko" value="{{ $riskAnalysis->{'eksposur_risiko_residual_q' . $quarter} ? 'Rp ' . number_format($riskAnalysis->{'eksposur_risiko_residual_q' . $quarter}, 0, ',', '.') : '-' }}">
                                <label for="">Target Eksposur Risiko</label>
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
                                <input class="form-control update-trigger inputmask-rupiah" type="text" id="realisasi_nilai_dampak" name="realisasi_nilai_dampak"
                                value="Rp {{ $riskAnalysis->kategori_dampak == 'Kualitatif' ? '0' : ($riskMonitoring?->nilai_dampak ?: '0') }}"
                                data-max="{{ $riskAnalysis->nilai_dampak }}"
                                {{ $riskAnalysis->kategori_dampak == 'Kualitatif' ? 'disabled' : '' }}
                                {{ $riskAnalysis->kategori_dampak == 'Kuantitatif' ? 'max=' . $riskAnalysis->nilai_dampak : '' }}
                                min="0"
                                oninput="if(this.value > {{ $riskAnalysis->nilai_dampak }} && '{{ $riskAnalysis->kategori_dampak }}' === 'Kuantitatif') this.value = {{ $riskAnalysis->nilai_dampak }};"
                                required disabled>
                                <label for="">Realisasi Nilai Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input type="hidden" name="realisasi_skala_dampak_hidden">
                                <select class="form-select js-select-hide-search update-trigger" name="realisasi_skala_dampak"
                                id="realisasi_skala_dampak" disabled>
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
                                <input class="form-control" type="text" name="realisasi_eksposure_risiko" id="realisasi_eksposure_risiko" placeholder=""
                                value="{{ isset($riskMonitoring?->eksposure_risiko) ? 'Rp ' . number_format($riskMonitoring->eksposure_risiko, 0, ',', '.') : '-' }}" readonly>
                                <label for="">Realisasi Eksposur Risiko</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control update-trigger" type="number" id="realisasi_nilai_probabilitas"
                                name="realisasi_nilai_probabilitas" value="{{ $riskMonitoring?->nilai_probabilitas }}"
                                data-max="{{ $riskAnalysis->nilai_probabilitas }}"
                                max="{{ $riskAnalysis->nilai_probabilitas }}"
                                min="0"
                                oninput="if(this.value > {{ $riskAnalysis->nilai_probabilitas }}) this.value = {{ $riskAnalysis->nilai_probabilitas }};" value="{{ $riskMonitoring?->nilai_probabilitas }}" disabled>
                                <label for="">Realisasi Nilai Probabilitas (%)</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control" name="realisasi_skala_probabilitas" type="text" placeholder=""
                                value="{{ $riskMonitoring?->skalaProbabilitas?->tingkat . ' - ' . $riskMonitoring?->skalaProbabilitas?->skala }}" id="realisasi_skala_probabilitas"
                                readonly />
                                <input type="hidden" name="realisasi_skala_probabilitas_hidden" id="realisasi_skala_probabilitas_hidden">
                                <label for="">Realisasi Skala Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control" type="text" name="realisasi_skala_risiko" id="realisasi_skala_risiko" placeholder="" readonly value="{{ $riskMonitoring?->skala_risiko }}">
                                <input type="hidden" name="realisasi_skala_risiko_hidden" id="realisasi_skala_risiko_hidden">
                                <label for="">Realisasi Skala Risiko</label>
                            </div>
                            <div class="form-floating">
                                <input class="form-control" name="realisasi_level_risiko" id="realisasi_level_risiko" type="text"
                                placeholder="" readonly value="{{ $riskMonitoring?->level_risiko }}" />
                                <input type="hidden" name="realisasi_level_risiko_hidden" id="realisasi_level_risiko_hidden">
                                <label for="">Realisasi Level Risiko</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal Lihat File -->
    <div class="modal fade" id="modalLihatFile" tabindex="-1" aria-labelledby="modalLihatFileLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLihatFileLabel">Lihat File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="divider my-3 my-md-5">
        <div class="divider-text">
            <h4 class="mb-0 ff-heading-sm">Log Perubahan Monitoring Risiko</h4>
        </div>
    </div>

    <div class="col-12 mt-5 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light text-center small fw-bold text-uppercase">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">Periode</th>
                                <th width="15%">Tanggal Input</th>
                                <th width="15%">Realisasi Dampak</th>
                                <th width="15%">Realisasi Probabilitas</th>
                                <th width="15%">Realisasi Eksposur</th>
                                <th width="13%">Realisasi Level</th>
                                <th width="10%">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historyMonitorings as $hLog)
                                <tr class="{{ ($hLog->month == $month && $hLog->quarter == $quarter) ? 'table-primary' : '' }}">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        <div class="fw-bold">Quarter {{ $hLog->quarter }}</div>
                                        <div class="fw-normal">
                                            @if($hLog->month)
                                                @lang('basic.month.' . $hLog->month)
                                            @else - @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($hLog->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">Rp {{ number_format($hLog->nilai_dampak, 0, ',', '.') }}</span>
                                            <span class="text-muted">
                                              {{ $hLog->skalaDampakObj ? '('.$hLog->skalaDampakObj->tingkat.') '.$hLog->skalaDampakObj->deskripsi : '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $hLog->nilai_probabilitas ?? '-' }}%</span>
                                            <span class="text-muted">
                                              {{ $hLog->skalaProbabilitas ? '('.$hLog->skalaProbabilitas->tingkat.') '.$hLog->skalaProbabilitas->skala : '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold">
                                        Rp {{ number_format($hLog->eksposure_risiko, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @php $lvlColor = str_replace(' ', '-', str_replace('to ', '', strtolower($hLog->level_risiko))); @endphp
                                        <div class="badge p-2 w-100 bg-{{ $lvlColor ?: 'secondary' }}">
                                            {{ $hLog->skala_risiko }} - {{ $hLog->level_risiko ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetailMonitoring{{ $hLog->id }}">
                                            <span class="bx bx-show"></span> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted fst-italic">Belum ada history monitoring untuk risiko ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach($historyMonitorings as $monitoring)
        <div class="modal fade" id="modalDetailMonitoring{{ $monitoring->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content p-0">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title">
                            Detail Realisasi: Q{{ $monitoring->quarter }} Tahun (@lang('basic.month.' . $monitoring->month))
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bg-light p-4">

                        <ul class="nav nav-tabs nav-line-tabs mb-4">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tab_penyebab_{{ $monitoring->id }}">Perlakuan Penyebab</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab_dampak_{{ $monitoring->id }}">Perlakuan Dampak</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab_kri_{{ $monitoring->id }}">Realisasi KRI</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tab_penyebab_{{ $monitoring->id }}" role="tabpanel">
                                @php
                                    $groupedPenyebab = $monitoring->perlakuanPenyebabMonitorings->groupBy(function($item) {
                                        return $item->perlakuanPenyebabRisikoUnit->penyebabRisiko->penyebab_risiko ?? 'Lainnya';
                                    });
                                @endphp
                                @forelse($groupedPenyebab as $penyebabName => $items)
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="mb-3 border-bottom pb-2">
                                                <label class="text-muted fw-bold small text-uppercase">Penyebab Risiko</label>
                                                <div class="fw-bold text-dark">{{ $penyebabName }}</div>
                                            </div>
                                            <table class="table table-bordered align-top small">
                                                <thead class="bg-light fw-bold text-muted">
                                                    <tr>
                                                        <th width="45%">Rencana Perlakuan</th>
                                                        <th width="55%">Realisasi & Dokumen</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($items as $realisasi)
                                                        <tr>
                                                            <td class="bg-white">
                                                                <strong class="text-primary">{{ $realisasi->perlakuanPenyebabRisikoUnit->rencana_perlakuan_risiko ?? '-' }}</strong>
                                                                <div class="mt-3 small">
                                                                    <div class="text-muted">Anggaran: <span class="text-dark fw-bold">Rp {{ number_format($realisasi->perlakuanPenyebabRisikoUnit->biaya_perlakuan_risiko ?? 0, 0, ',', '.') }}</span></div>
                                                                    <div class="text-muted">PIC: <span class="text-dark">{{ $realisasi->perlakuanPenyebabRisikoUnit->pic ?? '-' }}</span></div>
                                                                </div>
                                                            </td>
                                                            <td class="bg-white">
                                                                <div class="mb-2"><strong>Deskripsi:</strong> {{ $realisasi->deskripsi_perlakuan_risiko ?? '-' }}</div>
                                                                <div class="bg-light p-2 border rounded mb-2">
                                                                    <div class="row text-center">
                                                                        <div class="col-4 border-end">Progress:<br><strong>{{ $realisasi->progress_rencana_perlakuan_risiko ?? 0 }}%</strong></div>
                                                                        <div class="col-4 border-end">Biaya:<br><strong>Rp {{ number_format($realisasi->realisasi_biaya_perlakuan_risiko ?? 0, 0, ',', '.') }}</strong></div>
                                                                        <div class="col-4">Tgl Realisasi:<br><strong>{{ $realisasi->timeline_perlakuan_risiko_start ? \Carbon\Carbon::parse($realisasi->timeline_perlakuan_risiko_start)->format('d/m/Y') : '-' }}</strong></div>
                                                                    </div>
                                                                </div>
                                                                @php $docs = $monitoring->perlakuanPenyebabRisikoDocuments->where('perlakuan_penyebab_risiko_unit_id', $realisasi->perlakuan_penyebab_risiko_unit_id); @endphp
                                                                @if($docs->isNotEmpty())
                                                                    <div class="small mt-2"><strong>Dokumen:</strong><br>
                                                                        @foreach($docs as $doc)
                                                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="badge bg-secondary text-primary text-decoration-none mt-1 me-1 p-2"><i class="bx bx-paperclip"></i> {{ $doc->file_name }}</a>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-light text-center text-muted">Belum ada realisasi perlakuan penyebab.</div>
                                @endforelse
                            </div>

                            <div class="tab-pane fade" id="tab_dampak_{{ $monitoring->id }}" role="tabpanel">
                                @php
                                    $groupedDampak = $monitoring->perlakuanDampakMonitorings->groupBy(function($item) {
                                        return $item->perlakuanDampak->dampakRisikoUnit->dampak_risiko ?? 'Lainnya';
                                    });
                                @endphp
                                @forelse($groupedDampak as $dampakName => $items)
                                    <div class="card mb-3 border-0 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="mb-3 border-bottom pb-2">
                                                <label class="text-muted fw-bold small text-uppercase">Dampak Risiko</label>
                                                <div class="fw-bold text-dark">{{ $dampakName }}</div>
                                            </div>
                                            <table class="table table-bordered align-top small">
                                                <thead class="bg-light fw-bold text-muted">
                                                    <tr>
                                                        <th width="45%">Rencana Perlakuan</th>
                                                        <th width="55%">Realisasi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($items as $realisasi)
                                                        <tr>
                                                            <td class="bg-white">
                                                                <strong class="text-warning text-dark">{{ $realisasi->perlakuanDampak->rencana_perlakuan_risiko ?? '-' }}</strong>
                                                                <div class="mt-3 small">
                                                                    <div class="text-muted">Anggaran: <span class="text-dark fw-bold">Rp {{ number_format($realisasi->perlakuanDampak->biaya_perlakuan_risiko ?? 0, 0, ',', '.') }}</span></div>
                                                                    <div class="text-muted">PIC: <span class="text-dark">{{ $realisasi->perlakuanDampak->pic ?? '-' }}</span></div>
                                                                </div>
                                                            </td>
                                                            <td class="bg-white">
                                                                <div class="mb-2"><strong>Deskripsi:</strong> {{ $realisasi->deskripsi_perlakuan_risiko ?? '-' }}</div>
                                                                <div class="bg-light p-2 border rounded mb-2">
                                                                    <div class="row text-center">
                                                                        <div class="col-4 border-end">Progress:<br><strong>{{ $realisasi->progress_rencana_perlakuan_risiko ?? 0 }}%</strong></div>
                                                                        <div class="col-4 border-end">Biaya:<br><strong>Rp {{ number_format($realisasi->realisasi_biaya_perlakuan_risiko ?? 0, 0, ',', '.') }}</strong></div>
                                                                        <div class="col-4">Tgl Realisasi:<br><strong>{{ $realisasi->timeline_perlakuan_risiko_start ? \Carbon\Carbon::parse($realisasi->timeline_perlakuan_risiko_start)->format('d/m/Y') : '-' }}</strong></div>
                                                                    </div>
                                                                </div>

                                                                {{-- DOKUMEN DAMPAK (Jika ada relasinya di Model) --}}
                                                                @if(isset($monitoring->perlakuanDampakRisikoDocuments))
                                                                    @php $docsDampak = $monitoring->perlakuanDampakRisikoDocuments->where('perlakuan_dampak_risiko_unit_id', $realisasi->perlakuan_dampak_risiko_unit_id); @endphp
                                                                    @if($docsDampak->isNotEmpty())
                                                                        <div class="small mt-2"><strong>Dokumen:</strong><br>
                                                                            @foreach($docsDampak as $doc)
                                                                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="badge bg-secondary text-primary text-decoration-none mt-1 me-1 p-2"><i class="bx bx-paperclip"></i> {{ $doc->file_name }}</a>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-light text-center text-muted">Belum ada realisasi perlakuan dampak.</div>
                                @endforelse
                            </div>

                            <div class="tab-pane fade" id="tab_kri_{{ $monitoring->id }}" role="tabpanel">
                                <div class="card card-body shadow-sm border-0">
                                    <table class="table table-bordered align-middle">
                                        <thead class="bg-light text-center small fw-bold">
                                            <tr>
                                                <th rowspan="2" class="align-middle">Indikator (KRI)</th>
                                                <th colspan="3">Target Threshold</th>
                                                <th rowspan="2" class="align-middle">Nilai Realisasi</th>
                                                <th rowspan="2" class="align-middle">Status</th>
                                            </tr>
                                            <tr>
                                                <th class="bg-success text-white">Aman</th>
                                                <th class="bg-warning text-dark">Waspada</th>
                                                <th class="bg-danger text-white">Bahaya</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($monitoring->kriUnitMonitorings as $realisasiKri)
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold">{{ $realisasiKri->keyRiskIndicator->kri ?? '-' }}</div>
                                                        <small class="text-muted">Satuan: {{ $realisasiKri->keyRiskIndicator->satuan_kri ?? '-' }}</small>
                                                    </td>
                                                    <td class="text-center small">{{ $realisasiKri->keyRiskIndicator->batas_aman ?? '-' }}</td>
                                                    <td class="text-center small">{{ $realisasiKri->keyRiskIndicator->batas_waspada ?? '-' }}</td>
                                                    <td class="text-center small">{{ $realisasiKri->keyRiskIndicator->batas_bahaya ?? '-' }}</td>
                                                    <td class="fw-bold text-center text-primary">{{ $realisasiKri->nilai_kri_terkini ?? '-' }}</td>
                                                    <td class="text-center">
                                                        @php
                                                            $statusMap = [1 => 'Aman', 2 => 'Waspada', 3 => 'Bahaya'];
                                                            $statusColor = [1 => 'success', 2 => 'warning', 3 => 'danger'];
                                                            $status = $realisasiKri->status_kri_terkini;
                                                        @endphp
                                                        <span class="badge bg-{{ $statusColor[$status] ?? 'light text-dark border' }} p-2">
                                                            {{ $statusMap[$status] ?? '-' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center text-muted">Belum ada KRI yang dimonitoring</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="col-12 mt-5">
        <div class="row g-2">
            <div class="col-auto order-1">
                <a href="{{ route('risk-register-ap.monitorings.index', ['unit_id' => $risk->unit_id, 'period' => request()->route('period'), 'quarter' => $quarter, 'month' => $month]) }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </div>

    @include('risk-register-unit.monitorings._modal_kri')
    @include('risk-register-unit.monitorings._modal_penyebab')
    @include('risk-register-unit.monitorings._modal_mitigasi')
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
const penyebabRisikoProjects = @json($risk->penyebabRisikos->keyBy('id'));
const perlakuanPenyebabRisikos = @json($risk->penyebabRisikos->pluck('perlakuanPenyebabRisiko')->flatten()->keyBy('id'));
const kriProjects = @json($risk->kris->keyBy('id'));
const quarter = {{ $quarter }};
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
    const skalaDampak = parseFloat($('#realisasi_skala_dampak').val());
    const skalaProbabilitas = getSkalaProbabilitasByValue(nilaiProbabilitas);

    const domSkalaProbabilitas = $('#realisasi_skala_probabilitas');
    const domSkalaRisiko = $('#realisasi_skala_risiko');
    const domLevelRisiko = $('#realisasi_level_risiko');

    if (skalaProbabilitas) {
        domSkalaProbabilitas.val(skalaProbabilitas.tingkat + ' - ' + skalaProbabilitas.skala);
    } else {
        domSkalaProbabilitas.val('');
    }

    const riskMap = riskMaps[skalaDampak + '-' + (skalaProbabilitas?.tingkat)];
    if  (riskMap) {
        domSkalaRisiko.val(riskMap.nilai_risiko);
        domLevelRisiko.val(riskMap.level_risiko);
    } else {
        domSkalaRisiko.val('');
        domLevelRisiko.val('');
    }
}

$(document).ready(function() {
    $('#section-realisasi').on('change', '.update-trigger', function() {
        refreshSkalaAndLevelRisiko();
    }).change();

    $('.btn-action').on('click', function() {
        const action = $(this).data('action');
        if (action === 'save') {
            if (!$('#main-form')[0].checkValidity()) {
                $('#main-form')[0].reportValidity();
                return;
            }

            for (let i in perlakuanPenyebabRisikos) {
                let perlakuanPenyebabRisiko = perlakuanPenyebabRisikos[i];
                if (!perlakuanPenyebabRisiko.deskripsi_perlakuan_risiko) {
                    Swal.fire('Error', 'Semua update realisasi harus diisi', 'error');
                    return;
                }
            }

            for (let i in kriProjects) {
                let kriProject = kriProjects[i];
                if (!kriProject.status_kri_terkini_q{{$quarter}}) {
                    Swal.fire('Error', 'Semua update kri harus diisi', 'error');
                    return;
                }
            }

            const formData = new FormData($('#main-form')[0]);
            formData.append('perlakuan_penyebab_risikos', JSON.stringify(perlakuanPenyebabRisikos));
            formData.append('kri_projects', JSON.stringify(kriProjects));
            formData.append('quarter', quarter);
            formData.append('_method', 'PUT');
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
                        window.location.href = '{{ route('risk-register-ap.monitorings.index', ['period' => request()->route('period')]) }}';
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
        } else if (action === 'update-kri') {
            const kriProject = kriProjects[$(this).data('id')];
            const latestMonitoring = riskMonitoring?.kri_unit_monitorings?.find(m => m.key_risk_indicator_id == kriProject.id);

            $('#modalUpdateKri input[name="kri_project_id"]').val($(this).data('id'));
            $('#modalUpdateKri input[name="key_risk_indicator"]').val(kriProject.kri);
            $('#modalUpdateKri input[name="batas_aman"]').val(kriProject.batas_aman);
            $('#modalUpdateKri input[name="batas_waspada"]').val(kriProject.batas_waspada);
            $('#modalUpdateKri input[name="batas_bahaya"]').val(kriProject.batas_bahaya);
            $('#modalUpdateKri input[name="nilai_kri"]').val(latestMonitoring?.nilai_kri_terkini || '');
            $('#modalUpdateKri :input[name="status_kri"]').val(latestMonitoring?.status_kri_terkini || '').change();
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
            $('#modalUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val(perlakuanPenyebab['realisasi_biaya_perlakuan_risiko_q' + quarter]);
            $('#modalUpdateRealisasi :input[name="progress_perlakuan_risiko"]').val(perlakuanPenyebab['progress_rencana_perlakuan_risiko_q' + quarter]);
            $('#modalUpdateRealisasi :input[name="jenis_program_rkap"]').val(perlakuanPenyebab.jenis_program_rkap);
            $('#modalUpdateRealisasi :input[name="deskripsi_perlakuan_risiko"]').val(perlakuanPenyebab.deskripsi_perlakuan_risiko);
            if (perlakuanPenyebab.timeline_perlakuan_risiko?.length === 2) {
                $("#timelineRange").data('_flatpickr').setDate(perlakuanPenyebab.timeline_perlakuan_risiko);
            } else {
                $("#timelineRange").data('_flatpickr').clear();
            }
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

    $('#btnSimpanUpdateRealisasi').on('click', function() {
        if (!$('#formUpdateRealisasi')[0].checkValidity()) {
            $('#formUpdateRealisasi')[0].reportValidity();
            return;
        }
        const id = $('#formUpdateRealisasi :input[name="penyebab_risiko_id"]').val();
        const realisasiBiayaPerlakuanRisiko = $('#formUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val();
        const progressPerlakuanRisiko = $('#formUpdateRealisasi :input[name="progress_perlakuan_risiko"]').val();
        const deskripsiPerlakuanRisiko = $('#formUpdateRealisasi :input[name="deskripsi_perlakuan_risiko"]').val();
        const jenisProgramRkap = $('#formUpdateRealisasi :input[name="jenis_program_rkap"]').val();
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
        perlakuanPenyebabRisikos[id]['timeline_perlakuan_risiko'] = timelinePerlakuanRisiko.split(' to ');

        // update DOM
        const tr = $('#table-penyebab-risiko tr[data-id="' + id + '"]');
        tr.find('.display-biaya').text('Rp' + Intl.NumberFormat('id-ID').format(realisasiBiayaPerlakuanRisiko));
        tr.find('.display-progress').text(progressPerlakuanRisiko);

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

        // update kri
        kriProjects[id]['nilai_kri_terkini_q' + quarter] = nilaiKri;
        kriProjects[id]['status_kri_terkini_q' + quarter] = statusKri;

        // update DOM
        const tr = $('#table-kri tr[data-id="' + id + '"]');
        tr.find('.display-nilai-kri').text(nilaiKri);
        tr.find('.display-kondisi').text(statusKri);

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
    });

    var flatpickrIns = flatpickr("#timelineRange", {
        mode: "range",
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
        //maxDate: endOfYear,
        disableMobile: true
    });

    $("#timelineRange").data('_flatpickr', flatpickrIns);

    const files = @json($files ?? []);
    $('.lihat-file-btn').on('click', function() {
        const id = $(this).closest('tr').data('id');
        const filteredFiles = files[id];

        const tbody = $('#modalLihatFile tbody');
        tbody.empty();

        if (filteredFiles) {
            for (let i in filteredFiles) {
                const file = filteredFiles[i];
                const tr = $('<tr></tr>');
                tr.append('<td>' + file.file_name + '</td>');
                tr.append('<td>' + (file.description || '-') + '</td>');
                tr.append('<td><a href="' + file.url + '" download="' + file.file_name + '">Download</a></td>');
                tbody.append(tr);
            }
        } else {
            tbody.append('<tr><td colspan="4" class="text-center">Tidak ada data</td></tr>');
        }

        $('#modalLihatFile').modal('show');
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
});
</script>
@endpush
