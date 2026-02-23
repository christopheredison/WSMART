@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Data Risiko: {{ $risikos->first()->peristiwa_risiko }}</h3>
            {{-- <div class="ms-auto">
                <button id="exportPdfBtn" class="btn btn-sm btn-danger ms-auto d-flex align-items-center gap-1">
                    <i class='bx bxs-file-pdf'></i> Export PDF
                </button>
            </div> --}}
        </div>
    </div>

    <div id="exportArea" class="card mt-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="h3 mb-0">Peta Risiko Inheren dan Residual</span>
            </div>
        </div>
        <div class="card-body">
            <div class="border p-3 mb-3">
                @foreach (['High', 'Moderate to High', 'Moderate', 'Low to Moderate', 'Low'] as $level)
                <div class="me-3 d-inline-flex align-items-center gap-2">
                    <span class="d-inline-block bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($level)))}}" style="width:20px; height:20px; border-radius: 3px;"></span>
                    <span>{{ $level }}</span>
                </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col align-items-center d-flex">
                            <h3 class="h4">Peta Risiko Inheren dan Residual</h3>
                        </div>
                        <div class="col-1">
                            <select class="form-select" style="visibility: hidden;">
                            </select>
                        </div>
                    </div>
                    <div class="table-risk-map" id="inherentMap">
                        <table class="map-table">
                            <tbody>
                                @for($likelihood = 5; $likelihood >= 1; $likelihood--)
                                    <tr>
                                    @if ($likelihood == 5)
                                        <td rowspan="5" class="side-title">
                                            <div class="divider m-0">
                                                <div class="divider-text">
                                                    LIKELIHOOD
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                    @for($impact = 1; $impact <= 5; $impact++)
                                        @php
                                        $riskMap = $riskMaps[$impact . '-' . $likelihood] ?? null;
                                        @endphp
                                        <td>
                                            <div class="data-cell {{strtolower(str_replace(' ', '-', $riskMap['level_risiko']))}}" data-id="{{ ($likelihood - 1) * 5 + $impact }}" data-posisi-risiko="{{ $riskMap['nilai_risiko'] }}" data-matrix='{{ $impact }}-{{ $likelihood }}'>
                                                <div class="kode-peristiwa"></div>
                                                <div class="posisi-risiko">{{ $riskMap['nilai_risiko'] }}</div>
                                            </div>
                                        </td>
                                    @endfor
                                    </tr>
                                @endfor
                                <tr>
                                    <td class="useless-cell"></td>
                                    <td colspan="5" class="footer-title">
                                        <div class="divider m-0">
                                            <div class="divider-text">
                                                IMPACT
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- begin::Legend -->
                        <div class="risk-map-legend d-flex flex-center gap-3">
                            <div class="d-flex align-items-center gap-1">
                                <i class='bx bx-circle inherent'></i>
                                Inherent
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i class='bx bxs-circle residual'></i>
                                Residual
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bx bxs-circle current"></i>
                                Current
                            </div>
                        </div>
                        <!-- end::Legend -->
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row mb-3">
                        <div class="col align-items-center d-flex">
                            <h3 class="h4">Peta Risiko Terkini (Current)</h3>
                        </div>
                        <div class="col">
                            <select class="form-select" id="quarterSelect">
                                <option value="1">Quarter 1</option>
                                <option value="2">Quarter 2</option>
                                <option value="3">Quarter 3</option>
                                <option value="4">Quarter 4</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-risk-map" id="currentMap">
                        <table class="map-table">
                            <tbody>
                                @for($likelihood = 5; $likelihood >= 1; $likelihood--)
                                    <tr>
                                    @if ($likelihood == 5)
                                        <td rowspan="5" class="side-title">
                                            <div class="divider m-0">
                                                <div class="divider-text">
                                                    LIKELIHOOD
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                    @for($impact = 1; $impact <= 5; $impact++)
                                        @php
                                        $riskMap = $riskMaps[$impact . '-' . $likelihood] ?? null;
                                        @endphp
                                        <td>
                                            <div class="data-cell {{strtolower(str_replace(' ', '-', $riskMap['level_risiko']))}}" data-id="{{ ($likelihood - 1) * 5 + $impact }}" data-posisi-risiko="{{ $riskMap['nilai_risiko'] }}" data-matrix='{{ $impact }}-{{ $likelihood }}'>
                                                <div class="kode-peristiwa"></div>
                                                <div class="posisi-risiko">{{ $riskMap['nilai_risiko'] }}</div>
                                            </div>
                                        </td>
                                    @endfor
                                    </tr>
                                @endfor
                                <tr>
                                    <td class="useless-cell"></td>
                                    <td colspan="5" class="footer-title">
                                        <div class="divider m-0">
                                            <div class="divider-text">
                                                IMPACT
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- begin::Legend -->
                        <div class="risk-map-legend d-flex flex-center gap-3">
                            <div class="d-flex align-items-center gap-1">
                                <i class='bx bx-circle inherent'></i>
                                Inherent
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i class='bx bxs-circle residual'></i>
                                Residual
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i class="bx bxs-circle current"></i>
                                Current
                            </div>
                        </div>
                        <!-- end::Legend -->
                    </div>
                </div>
            </div>
            <div class="d-block mt-3">
                <div class="table-responsive scrollbar">
                    <table class="table table-strategi">
                        <thead>
                            <tr>
                                {{-- <th>No</th>  --}}
                                <th>Peristiwa Risiko</th>
                                <th>Deskripsi Peristiwa Risiko</th>
                                <th>Nilai Dampak Inherent</th>
                                <th>Skala Dampak Inherent</th>
                                <th>Nilai Probabilitas Inherent</th>
                                <th>Skala Probabilitas Inherent</th>
                                <th>Nilai Risiko Inherent</th>
                                <th>Level Risiko Inherent</th>
                                <th>Nilai Dampak Residual</th>
                                <th>Skala Dampak Residual</th>
                                <th>Nilai Probabilitas Residual</th>
                                <th>Skala Probabilitas Residual</th>
                                <th>Nilai Risiko Residual</th>
                                <th>Level Risiko Residual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($risikos as $risiko)
                            <tr>
                                {{-- <td>{{ $loop->iteration }}</td> --}}
                                <td>{{ $risiko->peristiwa_risiko ?? '-' }}</td>
                                <td>{{ $risiko->deskripsi_peristiwa_risiko ?? '-' }}</td>
                                <td>{{ $risiko->riskAnalysis?->nilai_dampak ? 'Rp ' . number_format($risiko->riskAnalysis->nilai_dampak, 0, ',', '.') : 'Rp 0' }}</td>
                                <td>
                                    {{ $risiko->riskAnalysis?->skalaDampakObj?->tingkat
                                        ? '(' . $risiko->riskAnalysis?->skalaDampakObj?->tingkat . ') ' . $risiko->riskAnalysis?->skalaDampakObj?->deskripsi
                                        : '-' }}
                                </td>
                                <td>{{ $risiko->riskAnalysis?->nilai_probabilitas ?? '-' }}</td>
                                <td>
                                    {{ $risiko->riskAnalysis?->skalaProbabilitas?->tingkat
                                        ? '(' . $risiko->riskAnalysis?->skalaProbabilitas?->tingkat . ') ' . $risiko->riskAnalysis?->skalaProbabilitas?->skala
                                        : '-' }}
                                </td>
                                <td>{{ $risiko->riskAnalysis?->skala_risiko ?? '-' }}</td>
                                <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($risiko->riskAnalysis?->level_risiko)))}}">{{ $risiko->riskAnalysis?->level_risiko ?? '-' }}</td>
                                <td>{{ $risiko->riskAnalysis?->nilai_dampak_residual ? 'Rp ' . number_format($risiko->riskAnalysis->nilai_dampak_residual, 0, ',', '.') : '-' }}</td>
                                <td>
                                    {{ $risiko->riskAnalysis?->skalaDampakResidualQ4Obj?->tingkat
                                        ? '(' . $risiko->riskAnalysis?->skalaDampakResidualQ4Obj?->tingkat . ') ' . $risiko->riskAnalysis?->skalaDampakResidualQ4Obj?->deskripsi
                                        : '-' }}
                                </td>
                                <td>{{ $risiko->riskAnalysis?->nilai_probabilitas_residual ?? '-' }}</td>
                                <td>
                                    {{ $risiko->riskAnalysis?->skalaProbabilitasResidualQ4?->tingkat
                                        ? '(' . $risiko->riskAnalysis?->skalaProbabilitasResidualQ4?->tingkat . ') ' . $risiko->riskAnalysis?->skalaProbabilitasResidualQ4?->skala
                                        : '-' }}
                                </td>
                                <td>{{ $risiko->riskAnalysis?->skala_risiko_residual ?? '-' }}</td>
                                <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($risiko->riskAnalysis?->level_risiko_residual)))}}">{{ $risiko->riskAnalysis?->level_risiko_residual ?? '-' }}</td>
                            </tr>
                            @endforeach
                            @if ($risikos->isEmpty())
                            <tr>
                                <td colspan="15" class="text-center p-3">Tidak ada data</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @php
        $risiko = $risikos->first();
        $analisa = $risiko->riskAnalysis;
    @endphp

    <!-- ::DataRisiko Start -->
    <div class="col-12 mt-4 mb-4">
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
                <div class="row">
                    <div class="col-12">
                        <div class="divider mb-3 mb-md-5 mt-0">
                            <div class="divider-text">
                                <h5 class="mb-0 ff-heading-sm">Periode Tahun {{ $risiko->periode->tahun ?? '-' }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 gx-md-5">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Nama Anak Perusahaan</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risiko?->unit?->name ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Sasaran Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risiko->target_capaian_kinerja ?? '-' }}
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Jenis Risiko T2 & T3 KBUMN</label>
                            <div class="p-3 bg-light rounded">
                                {{ optional($risiko->jenisRisiko->kategoriRisiko)->title ?? '-' }} - {{ optional($risiko->jenisRisiko)->title ?? '-' }}
                            </div>
                        </div>
                    </div> --}}
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Peristiwa Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risiko->peristiwa_risiko ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Deskripsi Peristiwa Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risiko->deskripsi_peristiwa_risiko ?? '-' }}
                            </div>
                        </div>
                    </div>
                    {{-- <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">WBS</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risiko->wbs ?? '-' }}
                            </div>
                        </div>
                    </div> --}}
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Peristiwa Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risiko->peristiwa_risiko ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold">Deskripsi Peristiwa Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risiko->deskripsi_peristiwa_risiko ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ::DataRisiko End -->

    <!-- ::DampakRisiko Start -->
    <div class="col-12 mb-4">
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
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Dampak Risiko</th>
                                <th width="25%">Rencana Perlakuan Risiko</th>
                                <th width="20%">Output Perlakuan Risiko</th>
                                <th width="20%">Biaya Perlakuan Risiko</th>
                                <th width="10%" class="text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalBiaya = 0; @endphp
                            @forelse($risiko->dampakRisikos as $dampak)
                                @if($dampak->perlakuanDampakRisikos && $dampak->perlakuanDampakRisikos->isNotEmpty())
                                    @foreach($dampak->perlakuanDampakRisikos as $perlakuan)
                                        @php $totalBiaya += $perlakuan->biaya_perlakuan_risiko ?? 0; @endphp
                                        <tr>
                                            @if($loop->first)
                                                <td rowspan="{{ $dampak->perlakuanDampakRisikos->count() }}">{{ $loop->parent->iteration }}</td>
                                                <td rowspan="{{ $dampak->perlakuanDampakRisikos->count() }}">{{ $dampak->dampak_risiko }}</td>
                                            @endif
                                            <td>{{ $perlakuan->rencana_perlakuan_risiko ?? '-' }}</td>
                                            <td>{{ $perlakuan->output_perlakuan_risiko ?? '-' }}</td>
                                            <td>{{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : '-' }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetailDampak{{ $perlakuan->id }}" title="Lihat Detail">
                                                    <span class='bx bx-show'></span>
                                                </button>

                                                <div class="modal fade text-start" id="modalDetailDampak{{ $perlakuan->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content border-0 shadow p-0">
                                                            <div class="modal-header border-bottom bg-light">
                                                                <h5 class="modal-title fw-bold">Detail Perlakuan Dampak Risiko</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="row g-4">
                                                                    <div class="col-12">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Dampak Risiko</span>
                                                                        <div class="text-dark fs-6">{{ $dampak->dampak_risiko ?? '-' }}</div>
                                                                    </div>
                                                                    <div class="col-12 col-md-6">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Rencana Perlakuan Risiko</span>
                                                                        <div class="text-dark">{{ $perlakuan->rencana_perlakuan_risiko ?? '-' }}</div>
                                                                    </div>
                                                                    <div class="col-12 col-md-6">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Output Perlakuan Risiko</span>
                                                                        <div class="text-dark">{{ $perlakuan->output_perlakuan_risiko ?? '-' }}</div>
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <hr class="my-2 text-muted">
                                                                    </div>

                                                                    <div class="col-12 col-md-6">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Opsi Perlakuan Risiko</span>
                                                                        <div class="text-dark">
                                                                            {{ $perlakuan->opsiPerlakuan->opsi_perlakuan_risiko ?? $perlakuan->opsi_perlakuan_risiko ?? '-' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12 col-md-6">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">PIC</span>
                                                                        <div class="text-dark">{{ $perlakuan->picJabatan->name ?? $perlakuan->pic ?? '-' }}</div>
                                                                    </div>
                                                                    <div class="col-12 col-md-4">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Biaya Perlakuan</span>
                                                                        <div class="text-primary fw-bold">
                                                                            {{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : 'Rp 0' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12 col-md-4">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Waktu Mulai</span>
                                                                        <div class="text-dark">
                                                                            <i class='bx bx-calendar-event me-1 text-muted'></i>
                                                                            {{ $perlakuan->timeline_perlakuan_risiko_start ? $perlakuan->timeline_perlakuan_risiko_start->format('d F Y') : '-' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12 col-md-4">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Waktu Selesai</span>
                                                                        <div class="text-dark">
                                                                            <i class='bx bx-calendar-check me-1 text-muted'></i>
                                                                            {{ $perlakuan->timeline_perlakuan_risiko_end ? $perlakuan->timeline_perlakuan_risiko_end->format('d F Y') : '-' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light border-top-0">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $dampak->dampak_risiko }}</td>
                                        <td colspan="4" class="text-center text-muted">Belum ada rencana perlakuan</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada dampak risiko</td>
                                </tr>
                            @endforelse
                            @if($totalBiaya > 0)
                                <tr class="table-warning">
                                    <td colspan="4" class="text-end fw-bold">Total Biaya Perlakuan:</td>
                                    <td colspan="2" class="fw-bold">{{ 'Rp ' . number_format($totalBiaya, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- ::DampakRisiko End -->

    <!-- ::PenyebabRisiko Start -->
    <div class="col-12 mb-4">
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
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Penyebab Risiko</th>
                                <th width="25%">Rencana Perlakuan Risiko</th>
                                <th width="20%">Output Perlakuan Risiko</th>
                                <th width="20%">Biaya Perlakuan Risiko</th>
                                <th width="10%" class="text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalBiaya = 0; @endphp
                            @forelse($risiko->penyebabRisiko as $penyebab)
                                @if($penyebab->perlakuanPenyebabRisiko && $penyebab->perlakuanPenyebabRisiko->isNotEmpty())
                                    @foreach($penyebab->perlakuanPenyebabRisiko as $perlakuan)
                                        @php $totalBiaya += $perlakuan->biaya_perlakuan_risiko ?? 0; @endphp
                                        <tr>
                                            @if($loop->first)
                                                <td rowspan="{{ $penyebab->perlakuanPenyebabRisiko->count() }}">{{ $loop->parent->iteration }}</td>
                                                <td rowspan="{{ $penyebab->perlakuanPenyebabRisiko->count() }}">{{ $penyebab->penyebab_risiko }}</td>
                                            @endif
                                            <td>{{ $perlakuan->rencana_perlakuan_risiko ?? '-' }}</td>
                                            <td>{{ $perlakuan->output_perlakuan_risiko ?? '-' }}</td>
                                            <td>{{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : '-' }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetailPenyebab{{ $perlakuan->id }}" title="Lihat Detail">
                                                    <span class='bx bx-show'></span>
                                                </button>

                                                <div class="modal fade text-start" id="modalDetailPenyebab{{ $perlakuan->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content border-0 shadow p-0">
                                                            <div class="modal-header border-bottom bg-light">
                                                                <h5 class="modal-title fw-bold">Detail Perlakuan Penyebab Risiko</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="row g-4">
                                                                    <div class="col-12">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Penyebab Risiko</span>
                                                                        <div class="text-dark fs-6">{{ $penyebab->penyebab_risiko ?? '-' }}</div>
                                                                    </div>
                                                                    <div class="col-12 col-md-6">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Rencana Perlakuan Risiko</span>
                                                                        <div class="text-dark">{{ $perlakuan->rencana_perlakuan_risiko ?? '-' }}</div>
                                                                    </div>
                                                                    <div class="col-12 col-md-6">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Output Perlakuan Risiko</span>
                                                                        <div class="text-dark">{{ $perlakuan->output_perlakuan_risiko ?? '-' }}</div>
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <hr class="my-2 text-muted">
                                                                    </div>

                                                                    <div class="col-12 col-md-6">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Opsi Perlakuan Risiko</span>
                                                                        <div class="text-dark">
                                                                            {{ $perlakuan->opsiPerlakuan->opsi_perlakuan_risiko ?? $perlakuan->opsi_perlakuan_risiko ?? '-' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12 col-md-6">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">PIC</span>
                                                                        <div class="text-dark">{{ $perlakuan->picJabatan->name ?? $perlakuan->pic ?? '-' }}</div>
                                                                    </div>
                                                                    <div class="col-12 col-md-4">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Biaya Perlakuan</span>
                                                                        <div class="text-primary fw-bold">
                                                                            {{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : 'Rp 0' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12 col-md-4">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Waktu Mulai</span>
                                                                        <div class="text-dark">
                                                                            <i class='bx bx-calendar-event me-1 text-muted'></i>
                                                                            {{ $perlakuan->timeline_perlakuan_risiko_start ? $perlakuan->timeline_perlakuan_risiko_start->format('d F Y') : '-' }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12 col-md-4">
                                                                        <span class="fw-bold d-block text-muted small text-uppercase mb-1">Waktu Selesai</span>
                                                                        <div class="text-dark">
                                                                            <i class='bx bx-calendar-check me-1 text-muted'></i>
                                                                            {{ $perlakuan->timeline_perlakuan_risiko_end ? $perlakuan->timeline_perlakuan_risiko_end->format('d F Y') : '-' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light border-top-0">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                              </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $penyebab->penyebab_risiko }}</td>
                                        <td colspan="4" class="text-center text-muted">Belum ada rencana perlakuan</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada penyebab risiko</td>
                                </tr>
                            @endforelse
                            @if($totalBiaya > 0)
                                <tr class="table-warning">
                                    <td colspan="4" class="text-end fw-bold">Total Biaya Perlakuan:</td>
                                    <td colspan="2" class="fw-bold">{{ 'Rp ' . number_format($totalBiaya, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- ::PenyebabRisiko End -->

    <!-- ::KeyRiskIndicator Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">5</span>
                    </span>
                    <span class="h3 mb-0">Key Risk Indicator</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="35%">Key Risk Indicator</th>
                                <th width="15%">Satuan KRI</th>
                                <th width="15%">Batas Aman</th>
                                <th width="15%">Batas Waspada</th>
                                <th width="15%">Batas Bahaya</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($risiko->kris as $kri)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $kri->kri ?? '-' }}</td>
                                    <td>{{ $kri->satuan_kri ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $kri->batas_aman ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">{{ $kri->batas_waspada ?? '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ $kri->batas_bahaya ?? '-' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada Key Risk Indicator</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- ::KeyRiskIndicator End -->

    <!-- ::Kontrol Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">6</span>
                    </span>
                    <span class="h3 mb-0">Kontrol</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row gy-3 gx-xxl-6">
                    <div class="col-md-6">
                        {{-- <div class="form-group mb-4">
                            <label class="form-label fw-bold">Jenis Kontrol Eksisting</label>
                            <div class="p-3 bg-light rounded">
                                {{ optional($risiko->jenisKontrolEksisting)->jenis_kontrol ?? '-' }}
                            </div>
                        </div> --}}
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Kontrol Eksisting</label>
                            @if($risiko->kontrolEksistings && $risiko->kontrolEksistings->isNotEmpty())
                                @foreach($risiko->kontrolEksistings as $key=>$kontrol)
                                    <div class="p-3 bg-light rounded mb-2">
                                      {{$key + 1}}. {{ $kontrol->kontrol_eksisting }}
                                    </div>
                                @endforeach
                            @else
                                <div class="p-3 bg-light rounded">
                                    Tidak ada kontrol eksisting
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- <div class="form-group mb-4">
                            <label class="form-label fw-bold">Penilaian Efektivitas Kontrol</label>
                            <div class="p-3 bg-light rounded">
                                {{ optional($risiko->penilaianEfektifitasKontrol)->efektivitas_kontrol ?? '-' }}
                            </div>
                        </div> --}}
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Perkiraan Waktu Mulai Terpapar Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risiko->perkiraan_waktu_terpapar_risiko_mulai ? \Carbon\Carbon::parse($risiko->perkiraan_waktu_terpapar_risiko_mulai)->format('d F Y') : '-' }}
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Perkiraan Waktu Selesai Terpapar Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risiko->perkiraan_waktu_terpapar_risiko_akhir ? \Carbon\Carbon::parse($risiko->perkiraan_waktu_terpapar_risiko_akhir)->format('d F Y') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ::Kontrol End -->

    <!-- ::AnalisaRisiko Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">7</span>
                    </span>
                    <span class="h3 mb-0">Analisa Risiko</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 gx-md-5">
                    <div class="col-md-4">
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Kategori Dampak</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->kategori_dampak ?? '-' }}
                            </div>
                        </div>
                    </div>
                    @if($analisa->kategori_dampak === 'Kualitatif')
                      <div class="col-md-4">
                          <div class="form-group mb-4">
                              <label class="form-label fw-bold">Area Dampak</label>
                              <div class="p-3 bg-light rounded">
                                  {{ optional($analisa->areaDampakObj)->title ?? '-' }}
                              </div>
                          </div>
                      </div>
                    @endif
                    <div class="col-md-4">
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">Risk Limit</label>
                            <div class="p-3 bg-light rounded">
                                {{ $risk_limit ? 'Rp ' . number_format($risk_limit, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ::AnalisaRisiko End -->

    <!-- ::PengukuranRisikoInheren Start -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">8</span>
                    </span>
                    <span class="h3 mb-0">Pengukuran Risiko Inheren</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nilai Dampak</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->nilai_dampak ? 'Rp ' . number_format($analisa->nilai_dampak, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nilai Probabilitas (%)</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->nilai_probabilitas ?? '-' }}%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Eksposur Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->eksposur_risiko ? 'Rp ' . number_format($analisa->eksposur_risiko, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Dampak</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skala_dampak ? '(' . $analisa->skala_dampak . ') ' . optional($analisa->skalaDampakObj)->deskripsi : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Probabilitas</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skalaProbabilitas ? '(' . $analisa->skalaProbabilitas->tingkat . ') ' . $analisa->skalaProbabilitas->skala : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->skala_risiko ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Level Risiko</label>
                            <div class="p-3 rounded bg-{{ str_replace(' ', '-', str_replace('to ', '', strtolower($analisa->level_risiko))) }}">
                                <span class="text-white fw-bold">{{ $analisa->level_risiko ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    @if($analisa->kategori_dampak === 'Kualitatif')
                        <div class="col-12">
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">Deskripsi Dampak</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $analisa->deskripsi_dampak ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-12">
                            <div class="form-group mb-4">
                                <label class="form-label fw-bold">Asumsi Perhitungan Dampak & Probabilitas Inherent</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $analisa->asumsi_perhitungan_dampak ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- ::PengukuranRisikoInheren End -->

    <!-- ::PengukuranRisikoResidual Start -->
    @for ($i = 1; $i <= 4; $i++)
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">{{ $i + 8 }}</span>
                    </span>
                    <span class="h3 mb-0">Pengukuran Risiko Residual - Quarter {{ $i }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nilai Dampak</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->{'nilai_dampak_residual_q' . $i} ? 'Rp ' . number_format($analisa->{'nilai_dampak_residual_q' . $i}, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Nilai Probabilitas (%)</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->{'nilai_probabilitas_residual_q' . $i} ?? '-' }}%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Eksposur Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->{'eksposur_risiko_residual_q' . $i} ? 'Rp ' . number_format($analisa->{'eksposur_risiko_residual_q' . $i}, 0, ',', '.') : 'Rp 0' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Dampak Residual</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->{'skala_dampak_residual_q' . $i} ? '(' . $analisa->{'skala_dampak_residual_q' . $i} . ') ' . optional($analisa->{'skalaDampakResidualQ' . $i . 'Obj'})->deskripsi : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Probabilitas</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->{'skalaProbabilitasResidualQ' . $i} ? '(' . $analisa->{'skalaProbabilitasResidualQ' . $i}->tingkat . ') ' . $analisa->{'skalaProbabilitasResidualQ' . $i}->skala : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Skala Risiko</label>
                            <div class="p-3 bg-light rounded">
                                {{ $analisa->{'skala_risiko_residual_q' . $i} ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-3">
                            <label class="form-label fw-bold">Level Risiko</label>
                            <div class="p-3 rounded bg-{{ str_replace(' ', '-', str_replace('to ', '', strtolower($analisa->{'level_risiko_residual_q' . $i}))) }}">
                                <span class="text-white fw-bold">{{ $analisa->{'level_risiko_residual_q' . $i} ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    @if($analisa->kategori_dampak === 'Kualitatif')
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Deskripsi Dampak Residual Q{{ $i }}</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $analisa->{'deskripsi_dampak_residual_q' . $i} ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Asumsi Perhitungan Dampak & Probabilitas Residual Q{{ $i }}</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $analisa->{'asumsi_perhitungan_dampak_residual_q' . $i} ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endfor
    <!-- ::PengukuranRisikoResidual End -->

    @if($historyMonitorings && $historyMonitorings->isNotEmpty())
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header stepper border-0 pb-0">
                <div class="nav-link active d-flex align-items-center p-0">
                    <span class="nav-item-circle-parent">
                        <span class="nav-item-circle">13</span>
                    </span>
                    <span class="h3 mb-0">History Monitoring Risiko</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center fw-bold text-uppercase">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Periode</th>
                                <th width="15%">Realisasi Dampak</th>
                                <th width="15%">Realisasi Probabilitas</th>
                                <th width="15%">Realisasi Eksposur</th>
                                <th width="15%">Realisasi Level Risiko</th>
                                <th width="10%">Efektivitas</th>
                                <th width="10%">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historyMonitorings as $monitoring)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        <div class="fw-bold">{{ $risiko->periode->tahun }} - Q{{ $monitoring->quarter }}</div>
                                        <div class="fw-normal">
                                            @if($monitoring->month)
                                                @lang('basic.month.' . $monitoring->month)
                                            @else - @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">
                                                {{ $monitoring->nilai_dampak ? 'Rp ' . number_format($monitoring->nilai_dampak, 0, ',', '.') : '-' }}
                                            </span>
                                            <span class="text-muted">
                                                {{ $monitoring->skalaDampakObj ? '('.$monitoring->skalaDampakObj->tingkat.') '.$monitoring->skalaDampakObj->deskripsi : '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $monitoring->nilai_probabilitas ?? '-' }}%</span>
                                            <span class="text-muted">
                                                {{ $monitoring->skalaProbabilitas ? '('.$monitoring->skalaProbabilitas->tingkat.') '.$monitoring->skalaProbabilitas->skala : '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ $monitoring->eksposure_risiko ? 'Rp ' . number_format($monitoring->eksposure_risiko, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @php $lvlColor = str_replace(' ', '-', str_replace('to ', '', strtolower($monitoring->level_risiko))); @endphp
                                        <div class="badge p-2 w-100 bg-{{ $lvlColor ?: 'secondary' }}">
                                            {{ $monitoring->skala_risiko }} - {{ $monitoring->level_risiko ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="text-center fw-bold">
                                        {{ $monitoring->efektivitas_perlakuan_risiko ?? 0 }}%
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalDetailMonitoring{{ $monitoring->id }}">
                                            <span class="bx bx-show"></span>
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

    {{-- MODAL SECTION UNTUK DETAIL MONITORING --}}
    @foreach($historyMonitorings as $monitoring)
    <div class="modal fade" id="modalDetailMonitoring{{ $monitoring->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content p-0">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">
                        Detail Realisasi: Quarter {{ $monitoring->quarter }} Tahun {{ $risiko->periode->tahun ?? date('Y') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">

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
                                                        <td>
                                                            <strong class="text-primary">{{ $realisasi->perlakuanPenyebabRisikoUnit->rencana_perlakuan_risiko ?? '-' }}</strong>
                                                            <div class="mt-3">
                                                                <div class="text-muted">Anggaran: <span class="text-dark fw-bold">Rp {{ number_format($realisasi->perlakuanPenyebabRisikoUnit->biaya_perlakuan_risiko ?? 0, 0, ',', '.') }}</span></div>
                                                                <div class="text-muted">PIC: <span class="text-dark">{{ $realisasi->perlakuanPenyebabRisikoUnit->pic ?? '-' }}</span></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="mb-2"><strong>Deskripsi:</strong> {{ $realisasi->deskripsi_perlakuan_risiko ?? '-' }}</div>
                                                            <div class="bg-light p-2 border rounded mb-2">
                                                                <div class="row text-center">
                                                                    <div class="col-4 border-end">Progress: <br><strong>{{ $realisasi->progress_rencana_perlakuan_risiko ?? 0 }}%</strong></div>
                                                                    <div class="col-4 border-end">Biaya: <br><strong>Rp {{ number_format($realisasi->realisasi_biaya_perlakuan_risiko ?? 0, 0, ',', '.') }}</strong></div>
                                                                    <div class="col-4">Tgl Realisasi: <br><strong>{{ $realisasi->timeline_perlakuan_risiko_start ? \Carbon\Carbon::parse($realisasi->timeline_perlakuan_risiko_start)->format('d/m/Y') : '-' }}</strong></div>
                                                                </div>
                                                            </div>
                                                            @php
                                                              $docs = $monitoring->perlakuanPenyebabRisikoDocuments->where('perlakuan_penyebab_risiko_unit_id', $realisasi->perlakuan_penyebab_risiko_unit_id);
                                                            @endphp
                                                            @if($docs->isNotEmpty())
                                                                <div class="mt-2"><strong>Dokumen:</strong><br>
                                                                    @foreach($docs as $doc)
                                                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="badge bg-secondary text-primary text-decoration-none mt-1 mr-1 p-2"><i class="bx bx-paperclip"></i> {{ $doc->file_name }}</a>
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
                                    // PERUBAHAN DI SINI
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
                                                        <td>
                                                            <strong class="text-warning text-dark">{{ $realisasi->perlakuanDampak->rencana_perlakuan_risiko ?? '-' }}</strong>
                                                            <div class="mt-3">
                                                                <div class="text-muted">Anggaran: <span class="text-dark fw-bold">Rp {{ number_format($realisasi->perlakuanDampak->biaya_perlakuan_risiko ?? 0, 0, ',', '.') }}</span></div>
                                                                <div class="text-muted">PIC: <span class="text-dark">{{ $realisasi->perlakuanDampak->pic ?? '-' }}</span></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="mb-2"><strong>Deskripsi:</strong> {{ $realisasi->deskripsi_perlakuan_risiko ?? '-' }}</div>
                                                            <div class="bg-light p-2 border rounded mb-2">
                                                                <div class="row text-center">
                                                                    <div class="col-4 border-end">Progress: <br><strong>{{ $realisasi->progress_rencana_perlakuan_risiko ?? 0 }}%</strong></div>
                                                                    <div class="col-4 border-end">Biaya: <br><strong>Rp {{ number_format($realisasi->realisasi_biaya_perlakuan_risiko ?? 0, 0, ',', '.') }}</strong></div>
                                                                    <div class="col-4">Tgl Realisasi: <br><strong>{{ $realisasi->timeline_perlakuan_risiko_start ? \Carbon\Carbon::parse($realisasi->timeline_perlakuan_risiko_start)->format('d/m/Y') : '-' }}</strong></div>
                                                                </div>
                                                            </div>
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
                                <h5 class="mb-3 text-info">Monitoring Key Risk Indicator (KRI)</h5>
                                <div class="table-responsive">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    @endif

    <div class="col-12 mt-5">
        <div class="row g-2">
            <div class="col-auto order-1">
                <a href="{{ route('risk-register-ap.index', ['pid' => $risiko->periode_id, 'unit_id' => $risiko->unit_id]) }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.kode-peristiwa {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    position: absolute;
    bottom: 5px;
    right: 0;
    width: calc(100% - 5px) !important;
}
.box-inherent {
    background-color: #fff;
    color: #000;
    padding: 2px 5px;
    border-radius: 5px;
}
.box-residual {
    background-color: #000;
    color: #fff;
    padding: 2px 5px;
    border-radius: 5px;
}
.box-current {
    background-color: #007bff;
    color: #fff;
    padding: 2px 5px;
    border-radius: 5px;
}
#currentMap .current-q1, #currentMap .current-q2, #currentMap .current-q3, #currentMap .current-q4 {
    display: none;
}

#currentMap.show-q1 .current-q1 {
    display: block;
}

#currentMap.show-q2 .current-q2 {
    display: block;
}

#currentMap.show-q3 .current-q3 {
    display: block;
}

#currentMap.show-q4 .current-q4 {
    display: block;
}

.table-warning {
    background-color: #fff3cd !important;
}
.form-group {
    margin-bottom: 1rem;
}
.rounded {
    border-radius: 0.375rem !important;
}
.bg-light {
    background-color: #f8f9fa !important;
}

</style>

@endpush

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
$(document).ready(function () {
    const inputmaskGeneral = $('.inputmask-general');
    const risks = @json($risikos);

    inputmaskGeneral.each(function() {
        const inputmask = $(this);
        const options = {
            alias: 'numeric',
            groupSeparator: '.',
            radixPoint: ',',
            autoGroup: true,
            digits: 0,
            digitsOptional: true,
            placeholder: '0',
            rightAlign: false,
            autoUnmask: true,
            removeMaskOnSubmit: true,
            min: 0,
            allowMinus: false,
            onBeforeMask: function(maskedValue, opts) {
                return maskedValue.replace('.', ',');
            },
            onUnMask: function(maskedValue, unmaskedValue, opts) {
                return maskedValue.replaceAll('.', '').replace(',', '.');
            },
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
        };

        if (inputmask.attr('step')) {
            options.digits = -Math.log10(inputmask.attr('step'));
        }
        if (inputmask.attr('max')) {
            options.max = inputmask.attr('max');
        }
        if (inputmask.attr('min')) {
            options.min = inputmask.attr('min');
        }

        inputmask.inputmask(options);
    });


    const formattedCurrentRiskMaps = @json($formattedCurrentRiskMaps);
    risks.forEach((risk, idx) => {
        const matrixI = risk.risk_analysis?.skala_dampak + '-' + risk.risk_analysis?.skala_probabilitas?.tingkat;
        const matrixR = risk.risk_analysis?.skala_dampak_residual + '-' + risk.risk_analysis?.skala_probabilitas_residual_q4?.tingkat;

        const cellI = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixI}"]`);
        const cellR = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixR}"]`);

        const code = (idx + 1).toString();

        if (cellI.length) {
            if (!cellI.data('kode-peristiwa-inherent')) {
                cellI.data('kode-peristiwa-inherent', []);
            }

            cellI.data('kode-peristiwa-inherent').push(code);
            cellI.data('has-inherent', true);
        }

        if (cellR.length) {
            if (!cellR.data('kode-peristiwa-residual')) {
                cellR.data('kode-peristiwa-residual', []);
            }

            cellR.data('kode-peristiwa-residual').push(code);
            cellR.data('has-residual', true);
        }

        const currentRiskMaps = formattedCurrentRiskMaps[risk.id];
        currentRiskMaps.forEach((currentRiskMap) => {
            const matrixC = currentRiskMap.skala_dampak + '-' + currentRiskMap.skala_probabilitas;
            const cellC = $(`#currentMap.table-risk-map .data-cell[data-matrix="${matrixC}"]`);

            if (cellC.length) {
                if (!cellC.data('kode-peristiwa-current-q' + currentRiskMap.quarter)) {
                    cellC.data('kode-peristiwa-current-q' + currentRiskMap.quarter, []);
                }

                cellC.data('kode-peristiwa-current-q' + currentRiskMap.quarter).push(code);
                cellC.data('has-current', true);
            }
        });

        const cells = $('#inherentMap.table-risk-map .data-cell');
        cells.each((index, cell) => {
            let html = '';
            let kodePeristiwaInherent = $(cell).data('kode-peristiwa-inherent');
            let kodePeristiwaResidual = $(cell).data('kode-peristiwa-residual');
            if (kodePeristiwaInherent && kodePeristiwaInherent.length > 0) {
                for (let i = 0; i < kodePeristiwaInherent.length; i++) {
                    html += `<span class="box-inherent">R${kodePeristiwaInherent[i]}</span>`;
                }
            }

            if (kodePeristiwaResidual && kodePeristiwaResidual.length > 0) {
                for (let i = 0; i < kodePeristiwaResidual.length; i++) {
                    html += `<span class="box-residual">R${kodePeristiwaResidual[i]}</span>`;
                }
            }

            $(cell).find('.kode-peristiwa').html(html);
        });

        const cellsC = $('#currentMap.table-risk-map .data-cell');
        cellsC.each((index, cell) => {
            let html = '';
            let kodePeristiwaCurrent = null;
            kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-q1');
            if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                    html += `<span class="box-current current-q1">R${kodePeristiwaCurrent[i]}</span>`;
                }
            }

            kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-q2');
            if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                    html += `<span class="box-current current-q2">R${kodePeristiwaCurrent[i]}</span>`;
                }
            }

            kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-q3');
            if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                    html += `<span class="box-current current-q3">R${kodePeristiwaCurrent[i]}</span>`;
                }
            }

            kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-q4');
            if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                    html += `<span class="box-current current-q4">R${kodePeristiwaCurrent[i]}</span>`;
                }
            }

            $(cell).find('.kode-peristiwa').html(html);
        });
    });

    $('#quarterSelect').on('change', function() {
        const quarter = $('#quarterSelect').val();
        $('#currentMap').prop('class', 'table-risk-map');
        $('#currentMap').addClass('show-q' + quarter);
    }).change();

    $('#exportPdfBtn').on('click', function() {
        const { jsPDF } = window.jspdf;
        const exportArea = document.getElementById('exportArea');
        const button = $(this);

        const namaRisiko = "{{ str_replace(' ', '-', strtolower($risikos->first()->peristiwa_risiko)) }}";
        const quarter = $('#quarterSelect').val();
        const fileName = `peta-risiko-${namaRisiko}-q${quarter}.pdf`;

        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');

        html2canvas(exportArea, {
            scale: 2, // Meningkatkan resolusi gambar 2x
            useCORS: true,
            logging: false,
        }).then(canvas => {
            // Mengambil data gambar dari canvas
            const imgData = canvas.toDataURL('image/png');

            // dimensi dari gambar
            const imgWidth = canvas.width;
            const imgHeight = canvas.height;

            const pdf = new jsPDF({
                orientation: imgWidth > imgHeight ? 'landscape' : 'portrait',
                unit: 'px',
                format: [imgWidth, imgHeight] // Mengatur ukuran PDF sama persis dengan ukuran gambar
            });

            pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
            pdf.save(fileName);

            button.prop('disabled', false).html("<i class='bx bxs-file-pdf'></i> Export PDF");
        }).catch(err => {
            console.error("Gagal membuat PDF:", err);
            alert("Maaf, terjadi kesalahan saat membuat PDF.");
            button.prop('disabled', false).html("<i class='bx bxs-file-pdf'></i> Export PDF");
        });
    });
});
</script>
@endpush
