@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Rencana Perlakuan Risiko</h3>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">1</span>
                </span>
                <span class="h3 mb-0">Deskripsi Peristiwa Risiko</span>
            </div>
        </div>
        <div class="card-body">
            <p>{{ $projectRisk->deskripsi_peristiwa_risiko }}</p>
        </div>
    </div>


    <div class="card mb-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">2</span>
                </span>
                <span class="h3 mb-0">Pengukuran Risiko Inheren</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">Nilai Dampak</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->nilai_dampak ? 'Rp' . number_format($analisa->nilai_dampak, 0, ',', '.') : 'Rp 0' }}</div>
                <div class="col-md-2">Nilai Probabilitas (%)</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->nilai_probabilitas ?: '-' }}</div>
                <div class="col-md-2">Skala Risiko</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->skala_risiko ?: '-' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2">Skala Dampak</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->skala_dampak ?: '-' }}</div>
                <div class="col-md-2">Skala Probabilitas</div>
                <div class="col-md-2 fw-bold">{{ ($analisa?->skalaProbabilitas? '(' . $analisa->skalaProbabilitas->tingkat . ') ' . $analisa->skalaProbabilitas->skala : null) ?: '-' }}</div>
                <div class="col-md-2">Level Risiko</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->level_risiko ?: '-' }}</div>
            </div>
            @if(($analisa?->kategori_dampak ?? null) === \App\Models\ProjectRiskAnalisa::KATEGORI_DAMPAK_KUANTITATIF && $analisa?->impactDetails?->isNotEmpty())
                <div class="row">
                    <div class="col-12">
                        <label class="fw-semibold mb-2">Rincian Perhitungan Dampak Inheren</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Uraian</th>
                                        <th class="text-end">Volume</th>
                                        <th>Satuan</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Jumlah (Vol x HS)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analisa->impactDetails as $detail)
                                        <tr>
                                            <td>{{ $detail->uraian }}</td>
                                            <td class="text-end">{{ $detail->formattedVolume() }}</td>
                                            <td>{{ $detail->satuan ?? '-' }}</td>
                                            <td class="text-end">Rp {{ number_format((float) $detail->harga_satuan, 2, ',', '.') }}</td>
                                            <td class="text-end fw-semibold">Rp {{ number_format((float) $detail->subtotal, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>


    <div class="card mb-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">3</span>
                </span>
                <span class="h3 mb-0">Perlakuan Risiko terhadap Penyebab Risiko</span>
            </div>
        </div>
        <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Penyebab Risiko</th>
                    <th>Rencana Perlakuan</th>
                    <th>Biaya</th>
                    <th>PIC</th>
                    <th>Divisi Terkait</th>
                    <th>Timeline Perlakuan Risiko</th>
                    <th>Action</th>
                    <th></th> <!-- Kolom untuk "Rencana Perlakuan Risiko" -->
                </tr>
            </thead>
            <tbody>
                @php
                    $totalBiaya = 0;
                @endphp
                @foreach ($projectRisk->penyebabRisikoProjects as $penyebab)
                    @php
                        // Tentukan kelas striping untuk penyebab risiko
                        $stripingClass = $loop->iteration % 2 === 0 ? 'xtable-light' : 'xtable-light';
                    @endphp
                    <tr class="{{ $stripingClass }}">
                        <td rowspan="{{ max($penyebab->perlakuanPenyebabRisiko->count(), 1) }}">{{ $loop->iteration }}</td>
                        <td rowspan="{{ max($penyebab->perlakuanPenyebabRisiko->count(), 1) }}">{{ $penyebab->penyebab_risiko }}</td>
                        @if ($penyebab->perlakuanPenyebabRisiko->isNotEmpty())
                            @foreach ($penyebab->perlakuanPenyebabRisiko as $index => $perlakuan)
                                @php
                                    $totalBiaya += $perlakuan->biaya_perlakuan_risiko ?? 0;
                                @endphp
                                @if ($index > 0)
                                    <tr class="{{ $stripingClass }}">
                                @endif
                                <td>{{ $perlakuan->rencana_perlakuan_risiko }}</td>
                                <td>{{ $perlakuan->biaya_perlakuan_risiko ? 'Rp' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : '-' }}</td>
                                <td>{{ $perlakuan->pic }}</td>
                                <td>
                                    {{ $perlakuan?->divisiTerkaitUnits->pluck('name')->implode(', ') ?: '-' }}
                                </td>
                                <td>{{ $perlakuan->waktu_perlakuan_risiko }}</td>
                                <td class="action-cell">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-link text-primary p-0" type="button" data-action="edit" data-id="{{ $perlakuan->id }}" data-bs-toggle="tooltip" data-bs-title="Edit Rencana Perlakuan Penyebab">
                                            <i class="bx bx-edit-alt fs-5"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-0" type="button" data-action="delete" data-id="{{ $perlakuan->id }}" data-bs-toggle="tooltip" data-bs-title="Hapus Rencana Perlakuan Penyebab">
                                            <i class="bx bx-trash fs-5"></i>
                                        </button>
                                    </div>
                                </td>
                                @if ($index === 0)
                                    <td rowspan="{{ $penyebab->perlakuanPenyebabRisiko->count() }}" class="text-center">
                                        <button class="btn btn-primary btn-sm" type="button" data-action="add" data-id="{{ $penyebab->id }}" data-penyebab="{{ $penyebab->penyebab_risiko }}" data-bs-toggle="tooltip" data-bs-title="Tambah Rencana Penyebab">
                                            <i class="bx bx-plus-circle"></i>
                                        </button>
                                    </td>
                                @endif
                                @if ($index > 0)
                                    </tr>
                                @endif
                            @endforeach
                        @else
                            <td colspan="5" class="text-center">Belum ada rencana perlakuan risiko</td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm" type="button" data-action="add" data-id="{{ $penyebab->id }}" data-penyebab="{{ $penyebab->penyebab_risiko }}" data-bs-toggle="tooltip" data-bs-title="Tambah Rencana  Penyebab">
                                    <i class="bx bx-plus-circle"></i>
                                </button>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-bold">Total Biaya Perlakuan:</td>
                    <td class="fw-bold">{{ 'Rp' . number_format($totalBiaya, 0, ',', '.') }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">4</span>
                </span>
                <span class="h3 mb-0">Perlakuan Risiko terhadap Dampak Risiko</span>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Dampak Risiko</th>
                        <th>Rencana Perlakuan</th>
                        <th>Biaya</th>
                        <th>PIC</th>
                        <th>Divisi Terkait</th>
                        <th>Timeline Perlakuan Risiko</th>
                        <th>Action</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalBiayaDampak = 0; @endphp
                        @foreach($projectRisk->dampakRisikoProjects as $dampak)
                            @php
                                $perlakuans = \App\Models\PerlakuanDampakRisiko::where('dampak_risiko_id', $dampak->id)->get();
                                $rowSpan = max($perlakuans->count(), 1);
                            @endphp
                            <tr>
                                <td rowspan="{{ $rowSpan }}">{{ $loop->iteration }}</td>
                                <td rowspan="{{ $rowSpan }}">{{ $dampak->dampak_risiko }}</td>

                                @if($perlakuans->isNotEmpty())
                                    @foreach($perlakuans as $idx => $p)
                                        @php $totalBiayaDampak += $p->biaya_perlakuan_risiko; @endphp
                                        @if($idx > 0) <tr> @endif
                                        <td>{{ $p->rencana_perlakuan_risiko }}</td>
                                        <td>{{ 'Rp' . number_format($p->biaya_perlakuan_risiko, 0, ',', '.') }}</td>
                                        <td>{{ $p->pic }}</td>
                                        <td>{{ $p->divisi_terkait_units->pluck('name')->implode(', ') ?: '-' }}</td>
                                        <td>{{ $p->waktu_perlakuan_risiko }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <button class="btn btn-link text-primary p-0" type="button"
                                                    data-action="edit-dampak" data-id="{{ $p->id }}"
                                                    data-risiko-id="{{ $projectRisk->id }}" data-dampak-risiko-id="{{ $dampak->id }}"
                                                    data-bs-toggle="tooltip" title="Edit">
                                                    <i class="bx bx-edit-alt fs-5"></i>
                                                </button>
                                                <button class="btn btn-link text-danger p-0" type="button"
                                                    data-action="delete-dampak" data-id="{{ $p->id }}"
                                                    data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bx bx-trash fs-5"></i>
                                                </button>
                                            </div>
                                        </td>
                                        @if($idx === 0)
                                            <td rowspan="{{ $rowSpan }}" class="text-center align-middle">
                                                <button class="btn btn-primary btn-sm" type="button"
                                                    data-action="add-dampak" data-risiko-id="{{ $projectRisk->id }}" data-id="{{ $dampak->id }}"
                                                    data-dampak="{{ $dampak->dampak_risiko }}"
                                                    data-bs-toggle="tooltip" title="Tambah Rencana Dampak">
                                                    <i class="bx bx-plus-circle"></i>
                                                </button>
                                            </td>
                                        @endif
                                        @if($idx > 0) </tr> @endif
                                    @endforeach
                                @else
                                    <td colspan="5" class="text-center text-muted italic">Belum ada perlakuan</td>
                                    <td class="text-center">
                                        <button class="btn btn-primary btn-sm" type="button"
                                            data-action="add-dampak" data-id="{{ $dampak->id }}"
                                            data-dampak="{{ $dampak->dampak_risiko }}">
                                            <i class="bx bx-plus-circle"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total Biaya Perlakuan:</td>
                        <td class="fw-bold">{{ 'Rp' . number_format($totalBiayaDampak, 0, ',', '.') }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">5</span>
                </span>
                <span class="h3 mb-0">Pengukuran Risiko Residual</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-2">Nilai Dampak</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->nilai_dampak_residual ? 'Rp' . number_format($analisa->nilai_dampak_residual, 0, ',', '.') : 'Rp 0' }}</div>
                <div class="col-md-2">Nilai Probabilitas (%)</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->nilai_probabilitas_residual ?: '-' }}</div>
                <div class="col-md-2">Skala Risiko</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->skala_risiko_residual ?: '-' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2">Skala Dampak</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->skala_dampak_residual ?: '-' }}</div>
                <div class="col-md-2">Skala Probabilitas</div>
                <div class="col-md-2 fw-bold">{{ ($analisa?->skalaProbabilitasResidual? '(' . $analisa->skalaProbabilitasResidual->tingkat . ') ' . $analisa->skalaProbabilitasResidual->skala : null) ?: '-' }}</div>
                <div class="col-md-2">Level Risiko</div>
                <div class="col-md-2 fw-bold">{{ $analisa?->level_risiko_residual ?: '-' }}</div>
            </div>
        </div>
    </div>

    {{-- <div class="card mb-5 d-none">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="nav-item-circle-parent">
                    <span class="nav-item-circle">5</span>
                </span>
                <span class="h3 mb-0">Rencana Pengukuran Risiko</span>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <span class="badge lead__icon badge-soft-warning me-3">
                  <i class="bx bx-pie-chart fs-2"></i>
                </span>
                <h6 class="fs-5 fw-bold">Quarter 1</h6>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Nilai Dampak</label>
                    <input type="text" class="form-control" name="nilai_dampak">
                </div>
                <div class="col-md-4">
                    <label>Nilai Probabilitas (%)</label>
                    <input type="text" class="form-control" name="nilai_probabilitas">
                </div>
                <div class="col-md-4">
                    <label>Skala Risiko</label>
                    <input type="text" class="form-control" readonly>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Skala Dampak</label>
                    <input type="text" class="form-control" name="nilai_dampak">
                </div>
                <div class="col-md-4">
                    <label>Skala Probabilitas</label>
                    <input type="text" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label>Level Risiko</label>
                    <input type="text" class="form-control" readonly>
                </div>
            </div>
            <div class="d-flex align-items-center mb-3">
                <span class="badge lead__icon badge-soft-warning me-3">
                  <i class="bx bx-pie-chart fs-2 rotate-90"></i>
                </span>
                <h6 class="fs-5 fw-bold">Quarter 2</h6>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Nilai Dampak</label>
                    <input type="text" class="form-control" name="nilai_dampak">
                </div>
                <div class="col-md-4">
                    <label>Nilai Probabilitas (%)</label>
                    <input type="text" class="form-control" name="nilai_probabilitas">
                </div>
                <div class="col-md-4">
                    <label>Skala Risiko</label>
                    <input type="text" class="form-control" readonly>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Skala Dampak</label>
                    <input type="text" class="form-control" name="nilai_dampak">
                </div>
                <div class="col-md-4">
                    <label>Skala Probabilitas</label>
                    <input type="text" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label>Level Risiko</label>
                    <input type="text" class="form-control" readonly>
                </div>
            </div>
            <div class="d-flex align-items-center mb-3">
                <span class="badge lead__icon badge-soft-warning me-3">
                  <i class="bx bx-pie-chart fs-2 rotate-180"></i>
                </span>
                <h6 class="fs-5 fw-bold">Quarter 3</h6>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Nilai Dampak</label>
                    <input type="text" class="form-control" name="nilai_dampak">
                </div>
                <div class="col-md-4">
                    <label>Nilai Probabilitas (%)</label>
                    <input type="text" class="form-control" name="nilai_probabilitas">
                </div>
                <div class="col-md-4">
                    <label>Skala Risiko</label>
                    <input type="text" class="form-control" readonly>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Skala Dampak</label>
                    <input type="text" class="form-control" name="nilai_dampak">
                </div>
                <div class="col-md-4">
                    <label>Skala Probabilitas</label>
                    <input type="text" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label>Level Risiko</label>
                    <input type="text" class="form-control" readonly>
                </div>
            </div>
            <div class="d-flex align-items-center mb-3">
                <span class="badge lead__icon badge-soft-warning me-3">
                  <i class="bx bx-pie-chart fs-2 rotate-270"></i>
                </span>
                <h6 class="fs-5 fw-bold">Quarter 4</h6>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Nilai Dampak</label>
                    <input type="text" class="form-control" name="nilai_dampak">
                </div>
                <div class="col-md-4">
                    <label>Nilai Probabilitas (%)</label>
                    <input type="text" class="form-control" name="nilai_probabilitas">
                </div>
                <div class="col-md-4">
                    <label>Skala Risiko</label>
                    <input type="text" class="form-control" readonly>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Skala Dampak</label>
                    <input type="text" class="form-control" name="nilai_dampak">
                </div>
                <div class="col-md-4">
                    <label>Skala Probabilitas</label>
                    <input type="text" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                    <label>Level Risiko</label>
                    <input type="text" class="form-control" readonly>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="col-12">
        <div class="d-flex justify-content-between">
            <div>
                @if($projectRisk->request_edit != 2)
                    <a href="{{ route('projects.risks.analisa', ['project' => $projectPeriodeList->id, 'risk' => $projectRisk->id]) }}" class="btn btn-secondary me-2">
                        <span class="bx bx-chevron-left" style="line-height: 0.8;"></span> Kembali ke Analisa
                    </a>
                @endif
                <a href="{{ route('projects.risks.index', ['project' => $projectPeriodeList->id]) }}" class="btn btn-outline-secondary">Selesai</a>
            </div>
            <div>
                <a href="{{ route('projects.risks.create', ['project' => $projectPeriodeList->id]) }}" class="btn btn-primary ms-auto btn-action">Lanjut Ke Pengisian Risiko Baru</a>
            </div>
        </div>
    </div>

    @include('project-risk._modal_rencana_perlakuan_risiko')
    @include('project-risk._modal_tambah_rencana_perlakuan_risiko')
    @include('project-risk._modal_edit_rencana_perlakuan_risiko')

    @include('project-risk._modal_tambah_rencana_perlakuan_dampak')
    @include('project-risk._modal_edit_rencana_perlakuan_dampak')
@endsection
@push('styles')
<style>
.xtable-light {
    background-color: #F8FAFB !important; /* Warna striping terang */
}

.xtable-white {
    background-color: #ffffff !important; /* Warna putih */
}

.xtable-light tr, .xtable-white tr {
    background-color: inherit !important; /* Warna pewarisan sama */
    opacity: 1 !important; /* Pastikan tidak ada opacity */
}

.action-cell {
    vertical-align: top !important; /* Konten berada di atas secara vertikal */
}

.action-cell .d-flex {
    align-items: flex-start !important; /* Konten tetap berada di atas */
}

</style>
@endpush
@push('scripts')
<script>
const penyebabRisikoProject = @json($projectRisk->penyebabRisikoProjects->keyBy('id'));
$(document).ready(function() {
    $('#picTambah, #divisiTerkaitTambah').select2({ dropdownParent: $('#modalTambahRencana') });
    $('#picEdit, #divisiTerkaitEdit').select2({ dropdownParent: $('#modalEditRencana') });
    $('#picTambahDampak, #divisiTerkaitTambahDampak').select2({ dropdownParent: $('#modalTambahRencanaDampak') });
    $('#picEditDampak, #divisiTerkaitEditDampak').select2({ dropdownParent: $('#modalEditRencanaDampak') });

    const projectRisk = @json($projectRisk);
    // Flatpickr Instance - Penyebab
    const flatpickrIns1 = flatpickr("#timelineRange1", {
        //mode: "range",
        altInput: false,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
        minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
        maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
        disableMobile: true
    });

    const flatpickrIns2 = flatpickr("#timelineRange2", {
        //mode: "range",
        altInput: false,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
        minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
        maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
        disableMobile: true
    });

    const fpCause1 = flatpickr("#xtimelineRange1", {
      dateFormat: "d/m/Y",
      minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
      maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
      disableMobile: true
    });
    const fpCause2 = flatpickr("#xtimelineRange2", {
      dateFormat: "d/m/Y",
      minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
      maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
      disableMobile: true
    });

    // Flatpickr Instance - Dampak
    const fpImpactNew1 = flatpickr("#timelineDampak1", {
        altInput: false,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
        minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
        maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
        disableMobile: true
    });
    const fpImpactNew2 = flatpickr("#timelineDampak2", {
        altInput: false,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
        minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
        maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
        disableMobile: true
    });

    const fpImpact1 = flatpickr("#xtimelineDampak1", {
      dateFormat: "d/m/Y",
      minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
      maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
      disableMobile: true
    });
    const fpImpact2 = flatpickr("#xtimelineDampak2", {
      dateFormat: "d/m/Y",
      minDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
      maxDate: projectRisk ? dayjs(projectRisk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
      disableMobile: true
    });

    const kategoriDampak = '{{ $analisa->kategori_dampak ?? "" }}';
    const nilaiDampak = {{ $analisa->nilai_dampak ?? 0 }};

    // Fungsi untuk validasi biaya
    function validateBiaya(input) {
            // Skip validasi jika kategori dampak Kualitatif atau nilai dampak 0/null
            if (kategoriDampak === 'Kualitatif' || !nilaiDampak) {
                return true;
            }

            const biayaValue = parseFloat(input.val().replace(/[^0-9.-]+/g, '')) || 0;

            if (biayaValue > nilaiDampak) {
                Swal.fire({
                    title: 'Peringatan!',
                    text: `Biaya perlakuan risiko (${biayaValue.toLocaleString('id-ID')}) tidak boleh melebihi nilai dampak (${nilaiDampak.toLocaleString('id-ID')})`,
                    icon: 'warning'
                });
                input.val('');
                return false;
            }
            return true;
        };


    // Event handler untuk form tambah dan edit
    $(document).on('change', 'input[name="biaya_perlakuan_risiko"], input[name="xbiaya_perlakuan_risiko"]', function() {
        validateBiaya($(this));
    });

    // Validasi saat submit form
    $('#formTambahRencana, #formEditRencana').on('submit', function(e) {
        const biayaInput = $(this).find('input[name="biaya_perlakuan_risiko"], input[name="xbiaya_perlakuan_risiko"]');
        if (!validateBiaya(biayaInput)) {
            e.preventDefault();
            return false;
        }
    });

    $(document).on('click', 'button[data-action="add"]', function() {
        const penyebabId = $(this).data('id'); // Ambil ID penyebab risiko dari tombol
        const penyebabNama = $(this).data('penyebab');

        // Reset form
        $('#formTambahRencana')[0].reset();

        // Reset select2 jika ada
        $('#formTambahRencana select').each(function() {
            $(this).val('').trigger('change');
        });

        // Reset flatpickr
        // if ($('#timelineRange')[0]._flatpickr) {
        //     $('#timelineRange')[0]._flatpickr.clear();
        // }

        // if ($('#timelineRange1')[0]._flatpickr) {
        //     $('#timelineRange1')[0]._flatpickr.clear();
        // }

        // if ($('#timelineRange2')[0]._flatpickr) {
        //     $('#timelineRange2')[0]._flatpickr.clear();
        // }

        // Reset input rupiah
        $('#formTambahRencana .inputmask-rupiah').val('0');

        $('#penyebabRisikoId').val(penyebabId);
        $('input[name="penyebab_risiko"]').val(penyebabNama);

        $('#modalTambahRencana').modal('show'); // Tampilkan modal
    });

    $('button[data-action="save"]').on('click', function() {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Data yang sudah disimpan tidak dapat diubah kembali',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Data berhasil disimpan', '', 'success');
            }
        });
    });

    $(document).on('click', 'button[data-action="delete"]', function() {
        const rencanaId = $(this).data('id'); // Ambil ID rencana dari tombol

        // Tampilkan SweetAlert untuk konfirmasi
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Rencana perlakuan ini akan dihapus secara permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika konfirmasi "Ya", kirimkan permintaan AJAX untuk menghapus
                $.ajax({
                    url: `/rencana-perlakuan/${rencanaId}`, // Endpoint hapus
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}' // Kirim token CSRF untuk otentikasi
                    },
                    success: function(response) {
                        // Tampilkan pesan berhasil
                        Swal.fire(
                            'Dihapus!',
                            response.message || 'Rencana perlakuan berhasil dihapus.',
                            'success'
                        ).then(() => {
                            // Refresh halaman atau hapus baris tabel
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        // Tampilkan pesan error
                        Swal.fire(
                            'Gagal!',
                            xhr.responseJSON.message || 'Terjadi kesalahan saat menghapus data.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    $(document).on('click', 'button[data-action="edit"]', function() {
        const id = $(this).data('id');
        $.get(`/rencana-perlakuan/${id}`, function(response) {
            $('#formEditRencana #xperlakuanId').val(response.id);
            $('#formEditRencana [name="xpenyebab_risiko"]').val(response.penyebab_risiko);
            $('#formEditRencana [name="xrencana_perlakuan_risiko"]').val(response.rencana_perlakuan_risiko);
            $('#formEditRencana [name="xoutput_perlakuan_risiko"]').val(response.output_perlakuan_risiko);
            $('#formEditRencana [name="xopsi_perlakuan_risiko"]').val(response.opsi_perlakuan_risiko);
            $('#formEditRencana [name="xbiaya_perlakuan_risiko"]').val(response.biaya_perlakuan_risiko);

            $('#picEdit').val(response.pic_jabatan_id).trigger('change');
            $('#divisiTerkaitEdit').val(response.divisi_terkait).trigger('change');

            fpCause1.setDate(response.timeline_perlakuan_risiko_start);
            fpCause2.setDate(response.timeline_perlakuan_risiko_end);

            $('#modalEditRencana').modal('show');
        });
    });

    // Click Add Dampak
    $(document).on('click', 'button[data-action="add-dampak"]', function() {
        const id = $(this).data('id');
        const risiko = $(this).data('risiko-id');
        const dampak = $(this).data('dampak');

        $('#formTambahRencanaDampak')[0].reset();
        $('#dampakRisikoIdInput').val(id);
        $('#risikoIdDampak').val(risiko);
        $('#formTambahRencanaDampak input[name="deskripsi_dampak"]').val(dampak);

        $('#formTambahRencanaDampak select').each(function() {
            $(this).val('').trigger('change');
        });
        $('#formTambahRencanaDampak .inputmask-rupiah').val('0');

        $('#modalTambahRencanaDampak').modal('show');
    });

    // Submit Tambah Dampak
    $('#btnSimpanTambahDampak').on('click', function() {
        $.ajax({
            url: '/rencana-perlakuan-dampak/tambah',
            type: 'POST',
            data: $('#formTambahRencanaDampak').serialize() + "&_token={{ csrf_token() }}",
            success: function(response) {
                $('#modalTambahRencanaDampak').modal('hide');
                Swal.fire('Berhasil', response.message, 'success').then(() => location.reload());
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON.message || 'Terjadi kesalahan', 'error');
            }
        });
    });

    // Click Edit Dampak
    $(document).on('click', 'button[data-action="edit-dampak"]', function() {
        const id = $(this).data('id');
        const risikoId = $(this).data('risiko-id');
        const dampakRisikoId = $(this).data('dampak-risiko-id');
        $.get(`/rencana-perlakuan-dampak/${id}`, function(data) {
            $('#xdPerlakuanId').val(data.id);
            $('#xdRisikoId').val(risikoId);
            $('#xdDampakRisikoId').val(dampakRisikoId);

            $('#formEditRencanaDampak [name="xd_deskripsi_dampak"]').val(data.deskripsi_dampak);
            $('#formEditRencanaDampak [name="xd_rencana_perlakuan_risiko"]').val(data.rencana_perlakuan_risiko);
            $('#formEditRencanaDampak [name="xd_output_perlakuan_risiko"]').val(data.output_perlakuan_risiko);
            $('#formEditRencanaDampak [name="xd_biaya_perlakuan_risiko"]').val(data.biaya_perlakuan_risiko);
            $('#formEditRencanaDampak [name="xd_opsi_perlakuan_risiko"]').val(data.opsi_perlakuan_risiko);

            // Select2
            $('#picEditDampak').val(data.pic_jabatan_id).trigger('change');
            $('#divisiTerkaitEditDampak').val(data.divisi_terkait).trigger('change');

            fpImpact1.setDate(data.timeline_perlakuan_risiko_start);
            fpImpact2.setDate(data.timeline_perlakuan_risiko_end);

            $('#modalEditRencanaDampak').modal('show');
        });
    });

    // Submit Update Dampak
    $('#btnUpdateDampak').on('click', function() {
        const id = $('#xdPerlakuanId').val();
        $.ajax({
            url: `/rencana-perlakuan-dampak/${id}`,
            type: 'PUT',
            data: $('#formEditRencanaDampak').serialize() + "&_token={{ csrf_token() }}",
            success: function(response) {
                $('#modalEditRencanaDampak').modal('hide');
                Swal.fire('Berhasil', response.message, 'success').then(() => location.reload());
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON.message || 'Gagal update', 'error');
            }
        });
    });

    // Delete Dampak
    $(document).on('click', 'button[data-action="delete-dampak"]', function() {
        const rencanaId = $(this).data('id');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data rencana perlakuan dampak ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();

                $.ajax({
                    url: `/rencana-perlakuan-dampak/${rencanaId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Dihapus!',
                            response.message,
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Gagal!',
                            xhr.responseJSON.message || 'Terjadi kesalahan saat menghapus data.',
                            'error'
                        );
                    }
                });
            }
        });
    });
});
</script>
@endpush
