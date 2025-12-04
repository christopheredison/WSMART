
@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Input Monitor Risiko <small class="d-block mt-2">{{ $project->project_name }} - {{ $peristiwaRisiko->title }}</small></h3>
        </div>
    </div>

    <form class="row g-3" method="POST" action="{{ route('projects.risks.store', request()->route('project')) }}" id="main-form">
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
                    {{ $projectRisk->deskripsi_peristiwa_risiko ?: '-' }}
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card bg-primary shadow text-white text-center border-0">
                <div class="card-body">
                    <i class='bx bx-alarm-exclamation fs-1 mb-3 text-white'></i>
                    <h4>Periode Monitoring</h4>
                    <h3 class="mb-0">Quarter {{ $quarter }} - Tahun {{ $tahun }}<br/>{{ __('basic.month.' . $month) }}</h3>
                    <input type="hidden" name="periode_monitoring" value="{{ $quarter }}">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">
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
                                value="Rp {{ number_format($projectRiskAnalisa->nilai_dampak, strpos($projectRiskAnalisa->nilai_dampak, '.') !== false ? 2 : 0, ',', '.') }}">
                                <label class="form-label" for="">Nilai Dampak Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_dampak_inherent"
                                value="{{ $projectRiskAnalisa->skalaDampakObj ? $projectRiskAnalisa->skalaDampakObj?->tingkat . ' - ' . $projectRiskAnalisa->skalaDampakObj?->deskripsi : '-' }}">
                                <label for="">Skala Dampak Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="nilai_probabilitas_inherent"
                                value="{{ $projectRiskAnalisa->nilai_probabilitas }}">
                                <label for="">Nilai Probabilitas Inherent (%)</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_probabilitas_inherent"
                                value="{{ $projectRiskAnalisa->skalaProbabilitas?->tingkat." - " .$projectRiskAnalisa->skalaProbabilitas?->skala }}">
                                <label for="">Skala Probabilitas Inherent</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_risiko_inherent"
                                value="{{ $projectRiskAnalisa->skala_risiko }}">
                                <label for="">Skala Risiko Inherent</label>
                            </div>
                            <div class="form-group pt-3">
                                <p>Level Risiko Inherent: <span class="ff-heading fw-medium">{{ $projectRiskAnalisa->level_risiko }}</strong>
                                </p>
                                <label
                                class=" radio-label low-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'low' ? 'active' : '' }}"
                                for="low"></label>
                                <label
                                class="radio-label low-medium-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'low to moderate' ? 'active' : '' }}"
                                for="low-medium"></label>
                                <label
                                class="radio-label medium-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'moderate' ? 'active' : '' }}"
                                for="medium"></label>
                                <label
                                class="radio-label medium-high-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'moderate to high' ? 'active' : '' }}"
                                for="medium-high"></label>
                                <label
                                class="radio-label high-label {{ strtolower($projectRiskAnalisa->level_risiko) == 'high' ? 'active' : '' }}"
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
                                value="Rp {{ number_format($projectRiskAnalisa->nilai_dampak_residual, strpos($projectRiskAnalisa->nilai_dampak_residual, '.') !== false ? 2 : 0, ',', '.') }}">
                                <label for="">Target Nilai Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_skala_dampak"
                                name="target_skala_dampak"
                                value="{{ $projectRiskAnalisa->skalaDampakResidualObj ? $projectRiskAnalisa->skalaDampakResidualObj->tingkat . ' - ' . $projectRiskAnalisa->skalaDampakResidualObj->deskripsi : '-' }}">
                                <label for="">Target Skala Dampak</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_nilai_probabilitas"
                                name="target_nilai_probabilitas" value="{{ $projectRiskAnalisa->nilai_probabilitas_residual }}">
                                <label for="">Target Nilai Probabilitas (%)</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" name="skala_probabilitas_inherent"
                                value="{{ $projectRiskAnalisa->skalaProbabilitasResidual?->tingkat." - " .$projectRiskAnalisa->skalaProbabilitasResidual?->skala }}">
                                <label for="">Target Skala Probabilitas</label>
                            </div>
                            <div class="form-floating">
                                <input disabled="disabled" class="form-control" type="text" id="target_skala_risiko"
                                name="target_skala_risiko" value="{{ $projectRiskAnalisa->skala_risiko_residual }}">
                                <label for="">Target Skala Risiko</label>
                            </div>
                            <div class="form-group pt-3">
                                <p>Target Level Risiko: <span class="ff-heading fw-medium">{{ $projectRiskAnalisa->level_risiko_residual }}</span>
                                </p>
                                <label
                                class=" radio-label low-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'low' ? 'active' : '' }}"
                                for="low"></label>
                                <label
                                class="radio-label low-medium-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'low to moderate' ? 'active' : '' }}"
                                for="low-medium"></label>
                                <label
                                class="radio-label medium-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'moderate' ? 'active' : '' }}"
                                for="medium"></label>
                                <label
                                class="radio-label medium-high-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'moderate to high' ? 'active' : '' }}"
                                for="medium-high"></label>
                                <label
                                class="radio-label high-label {{ strtolower($projectRiskAnalisa->level_risiko_residual) == 'high' ? 'active' : '' }}"
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
                                  value="{{ $projectRiskAnalisa->kategori_dampak == 'Kualitatif' ? '0' : ($riskMonitoring?->nilai_dampak ?: '0') }}"
                                  {{ $projectRiskAnalisa->kategori_dampak == 'Kualitatif' ? 'disabled' : '' }}
                                  {{-- {{ $projectRiskAnalisa->kategori_dampak == 'Kuantitatif' ? 'max=' . $projectRiskAnalisa->nilai_dampak : '' }} --}}
                                  min="0"
                                  {{-- oninput="if(this.value > {{ $projectRiskAnalisa->nilai_dampak }} && '{{ $projectRiskAnalisa->kategori_dampak }}' === 'Kuantitatif') this.value = {{ $projectRiskAnalisa->nilai_dampak }};" --}}
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
                                      {{ $tingkat > $projectRiskAnalisa->skalaDampakObj?->tingkat ? 'disabled' : '' }}>
                                      {{ $tingkat }} - {{ $deskripsi }}
                                      {{ $tingkat > $projectRiskAnalisa->skalaDampakObj?->tingkat ? '(Melebihi Skala Inherent)' : '' }}
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
                                  {{-- data-max="{{ $projectRiskAnalisa->nilai_probabilitas }}"  --}}
                                  {{-- max="{{ $projectRiskAnalisa->nilai_probabilitas }}" --}}
                                  {{-- min="0" --}}
                                  {{-- oninput="if(this.value > {{ $projectRiskAnalisa->nilai_probabilitas }}) this.value = {{ $projectRiskAnalisa->nilai_probabilitas }};" --}}
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm">Realisasi Penanganan Risiko</h4>
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
                                <span>Update KRI</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2">
                <div class="card">
                    <div class="card-body">
                        <table class="table" id="table-penyebab-risiko">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Penyebab Risiko</th>
                                    <th>Rencana Perlakuan Risiko</th>
                                    <th>Biaya Perlakuan Risiko</th>
                                    <th>Progress Perlakuan Risiko</th>
                                    <th>Realisasi Biaya Perlakuan Risiko</th>
                                    <th>Waktu Perlakuan Risiko</th>
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penyebabRisikoProjects as $penyebabRisiko)
                                    @foreach ($penyebabRisiko->perlakuanPenyebabRisiko as $perlakuan)
                                        @if ($loop->index == 0)
                                        <tr data-id="{{ $perlakuan?->id }}">
                                            <td rowspan="{{ $penyebabRisiko->perlakuanPenyebabRisiko->count() }}">{{ $loop->iteration }}</td>
                                            <td rowspan="{{ $penyebabRisiko->perlakuanPenyebabRisiko->count() }}">{{ $penyebabRisiko->penyebab_risiko ?: '-' }}</td>
                                        @else
                                        <tr data-id="{{ $perlakuan->id }}">
                                        @endif
                                            <td>{{ $perlakuan->rencana_perlakuan_risiko ?: '-' }}</td>
                                            <td>
                                              <span class="inputmask-fixed">
                                                {{ $perlakuan->biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->biaya_perlakuan_risiko, 0, ',', '.') : '-' }}
                                              </span>
                                            </td>
                                            <td class="display-progress inputmask-fixed">{{ $perlakuan->progress_rencana_perlakuan_risiko ?? '-' }}</td>
                                            <td class="display-biaya inputmask-fixed">
                                              {{  $perlakuan->realisasi_biaya_perlakuan_risiko ? 'Rp ' . number_format($perlakuan->realisasi_biaya_perlakuan_risiko, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="display-timeline">{{ $perlakuan?->lastMonitoring?->timeline_perlakuan_risiko_start?->format('d/m/Y') ?: '-' }}</td>
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

                                @if($penyebabRisikoProjects->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <table class="table mt-7" id="table-kri">
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
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kriProjects as $kriProject)
                                    <tr data-id="{{ $kriProject->id }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $kriProject->kri ?: '-' }}</td>
                                        <td>{{ $kriProject->satuan_kri ?: '-' }}</td>
                                        <td>{{ $kriProject->batas_aman ?: '-' }}</td>
                                        <td>{{ $kriProject->batas_waspada ?: '-' }}</td>
                                        <td>{{ $kriProject->batas_bahaya ?: '-' }}</td>
                                        <td class="display-nilai-kri">
                                            {{ $kriProject->nilai_kri_terkini ?? '-' }}
                                        </td>
                                        <td class="display-kondisi">
                                            @php
                                                $statusMap = [
                                                    1 => 'Aman',
                                                    2 => 'Waspada',
                                                    3 => 'Bahaya',
                                                ];
                                                $status = $kriProject->status_kri_terkini;
                                                $displayStatus = $statusMap[$status] ?? '-';
                                            @endphp
                                            {{ $displayStatus }}
                                        </td>
                                        <td style="white-space:nowrap">
                                            {{-- <a href="javascript:void(0)" class="hover-underline px-1 btn-action" data-action="update-kri" data-id="{{ $kriProject->id }}">Update KRI</a> --}}
                                            <div class="text-center">
                                            <a href="javascript:void(0)"
                                              class="btn-input-icon btn-action"
                                              data-action="update-kri"
                                              data-bs-toggle="tooltip"
                                              title="Update KRI"
                                              data-id="{{ $kriProject->id }}">
                                                <span class="bx bx-chart text-primary"></span>
                                            </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if($kriProjects->isEmpty())
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="divider my-3 my-md-5">
                <div class="divider-text">
                    <h4 class="mb-0 ff-heading-sm">Log Perlakuan Risiko</h4>
                </div>
            </div>
            <div class="row g-2">
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
                                    <th>Anggaran (Rp)</th>
                                    <th>Deskripsi Perlakuan Risiko</th>
                                    <th>Realisasi Anggaran (Rp)</th>
                                    <th>Progress (%)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($penyebabRisikoProjects as $penyebabRisiko)
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
                    <a href="{{ route('projects.monitorings.index', ['project' => $projectPeriode->id]) }}" class="btn btn-outline-secondary">Batal</a>
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

    @include('project-monitorings._modal_kri')
    @include('project-monitorings._modal_penyebab')
    @include('project-monitorings._modal_mitigasi')
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
const penyebabRisikoProjects = @json($penyebabRisikoProjects->keyBy('id'));
const perlakuanPenyebabRisikos = @json($penyebabRisikoProjects->pluck('perlakuanPenyebabRisiko')->flatten()->keyBy('id'));
const kriProjects = @json($kriProjects->keyBy('id'));
const quarter = {{ $quarter }};
const namaRisiko = @json($peristiwaRisiko->title);
const month = @json($month);
const year = @json($tahun);
const paddedMonth = String(month).padStart(2, '0');
const minDateString = dayjs(`${year}-${paddedMonth}-01`, 'YYYY-MM-DD').toDate();

var flatpickrIns = flatpickr("#timelineInput", {
    mode: "single",
    altInput: true
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    minDate: minDateString,
    disableMobile: true
});

$("#timelineInput").data('_flatpickr', flatpickrIns);

var timeline1 = flatpickr("#perkiraan_waktu_terpapar_risiko_mulai", {
    mode: "single",
    altInput: true
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    //maxDate: endOfYear,
    disableMobile: true
});
var timeline2 = flatpickr("#perkiraan_waktu_terpapar_risiko_akhir", {
    mode: "single",
    altInput: true
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    //maxDate: endOfYear,
    disableMobile: true
});
var perlakuanWaktu1 = flatpickr("#timeline_perlakuan_risiko_start", {
    mode: "single",
    altInput: true
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    //maxDate: endOfYear,
    disableMobile: true
});
var perlakuanWaktu2 = flatpickr("#timeline_perlakuan_risiko_end", {
    mode: "single",
    altInput: true
    altFormat: "j F Y",
    dateFormat: "d/m/Y",
    //maxDate: endOfYear,
    disableMobile: true
});

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
        $('#realisasi_skala_probabilitas_hidden').val(skalaProbabilitas.tingkat);
    } else {
        domSkalaProbabilitas.val('');
        $('#realisasi_skala_probabilitas_hidden').val('');
    }

    const riskMap = riskMaps[skalaDampak + '-' + (skalaProbabilitas?.tingkat)];
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
    formData.append('kri_projects', JSON.stringify(kriProjects));
    formData.append('quarter', quarter);
    formData.append('_method', 'PUT');

    // Append new data for isClosed
    formData.append('is_closed', isClosed);

    $.ajax({
        url: '{{ route('projects.monitorings.update', ['project' => request()->route('project'), 'monitoring' => request()->route('monitoring')]) }}',
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
                // let baseUrl = '{{ route('projects.monitorings.index', ['project' => request()->route('project')]) }}';

                // const currentQuarter = '{{ $quarter }}';
                // const currentTahun = '{{ $tahun }}';
                // const currentMonth = '{{ $month }}';
                // const redirectUrl = `${baseUrl}?quarter=${currentQuarter}&tahun=${currentTahun}&month=${currentMonth}`;

                // window.location.href = redirectUrl;
                window.location.href = '{{ route('projects.monitorings.index', ['project' => request()->route('project')]) }}';
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
    const skalaDampakInherent = {{ $projectRiskAnalisa->skalaDampakObj?->tingkat ?? 0 }};
    const nilaiProbabilitasInherent = {{ $projectRiskAnalisa->nilai_probabilitas ?? 0 }};

    // Validasi saat memilih skala dampak
    $('#realisasi_skala_dampak').on('change', function(e) {
        // Skip validasi jika perubahan dari hitungRealisasiSkalaDampak()
        if (e.originalEvent === undefined) return;

        const selectedValue = parseInt($(this).val());
        if (selectedValue > skalaDampakInherent) {
            Swal.fire({
                title: 'Peringatan',
                text: 'Nilai skala dampak realisasi tidak boleh lebih besar dari skala dampak inherent',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            $(this).val('').trigger('change');
        }
    });

    // // Validasi nilai probabilitas
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
    }).change();

    $('.btn-action').on('click', function() {
        const action = $(this).data('action');
        if (action === 'save' || action === 'save-and-close') {
            // validasi terlebih dahulu
            if (!validateRealisasiForm()) {
                return;
            }

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
            //console.log(kriProject);
            const latestMonitoring = kriProject.kri_project_monitorings
                ?.sort((a, b) => b.id - a.id)[0];

            $('#modalUpdateKri input[name="kri_project_id"]').val($(this).data('id'));
            $('#modalUpdateKri input[name="key_risk_indicator"]').val(kriProject.kri);
            $('#modalUpdateKri input[name="batas_aman"]').val(kriProject.batas_aman);
            $('#modalUpdateKri input[name="batas_waspada"]').val(kriProject.batas_waspada);
            $('#modalUpdateKri input[name="batas_bahaya"]').val(kriProject.batas_bahaya);
            $('#modalUpdateKri input[name="nilai_kri"]').val(kriProject.nilai_kri_terkini);
            $('#modalUpdateKri :input[name="status_kri"]').val(kriProject.status_kri_terkini);
            $('#modalUpdateKri').modal('show');
        } else if (action === 'update-realisasi') {
            const projectRisk = @json($projectRisk);
            const perlakuanPenyebab = perlakuanPenyebabRisikos[$(this).data('id')];
            if (!perlakuanPenyebab) {
                Swal.fire('Error', 'Data perlakuan penyebab risiko tidak ditemukan', 'error');
                return;
            }
            const penyebabRisiko = penyebabRisikoProjects[perlakuanPenyebab.penyebab_risiko_id];
            $('#modalUpdateRealisasi :input[name="penyebab_risiko_id"]').val($(this).data('id'));
            $('#modalUpdateRealisasi :input[name="penyebab_risiko"]').val(penyebabRisiko.penyebab_risiko);

            if (projectRisk.perkiraan_waktu_terpapar_risiko_mulai) {
              timeline1.setDate(dayjs(projectRisk.perkiraan_waktu_terpapar_risiko_mulai).format('DD/MM/YYYY'));
            }

            if (projectRisk.perkiraan_waktu_terpapar_risiko_akhir) {
              timeline2.setDate(dayjs(projectRisk.perkiraan_waktu_terpapar_risiko_akhir).format('DD/MM/YYYY'));
            }

            if (perlakuanPenyebab.timeline_perlakuan_risiko_start) {
              perlakuanWaktu1.setDate(dayjs(perlakuanPenyebab.timeline_perlakuan_risiko_start).format('DD/MM/YYYY'));
            }

            if (perlakuanPenyebab.timeline_perlakuan_risiko_end) {
              perlakuanWaktu2.setDate(dayjs(perlakuanPenyebab.timeline_perlakuan_risiko_end).format('DD/MM/YYYY'));
            }

            $('#modalUpdateRealisasi :input[name="rencana_perlakuan_risiko"]').val(perlakuanPenyebab.rencana_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="biaya_perlakuan_risiko"]').val(perlakuanPenyebab.biaya_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="pic"]').val(perlakuanPenyebab.pic);
            // $('#modalUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val(perlakuanPenyebab.realisasi_biaya_perlakuan_risiko === null || perlakuanPenyebab.realisasi_biaya_perlakuan_risiko === '' ? perlakuanPenyebab.biaya_perlakuan_risiko : perlakuanPenyebab.realisasi_biaya_perlakuan_risiko);
            $('#modalUpdateRealisasi :input[name="realisasi_biaya_perlakuan_risiko"]').val(perlakuanPenyebab.realisasi_biaya_perlakuan_risiko ?? 0);
            $('#modalUpdateRealisasi :input[name="progress_perlakuan_risiko"]').val(perlakuanPenyebab.progress_rencana_perlakuan_risiko);
            // $('#modalUpdateRealisasi :input[name="jenis_program_rkap"]').val(perlakuanPenyebab.jenis_program_rkap);
            // $('#modalUpdateRealisasi :input[name="jenis_program_rkap_id"]').val(perlakuanPenyebab.jenis_program_rkap_id);
            $('#modalUpdateRealisasi :input[name="deskripsi_perlakuan_risiko"]').val(perlakuanPenyebab.deskripsi_perlakuan_risiko);
            if (perlakuanPenyebab.timeline_perlakuan_risiko?.length === 2) {
                $("#timelineInput").data('_flatpickr').setDate(perlakuanPenyebab.timeline_perlakuan_risiko[0]);
            } else if (perlakuanPenyebab.timeline_perlakuan_risiko) {
                $("#timelineInput").data('_flatpickr').setDate(perlakuanPenyebab.timeline_perlakuan_risiko);
            } else {
                $("#timelineInput").data('_flatpickr').clear();
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

                // console.log(id, domCell);

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
            // $('#modalMitigasi :input[name="jenis_program_rkap_id"]').val(perlakuanMonitoring.jenis_program_rkap_id).change();
            $('#modalMitigasi :input[name="timeline_perlakuan_risiko"]').val(perlakuanMonitoring.timeline_perlakuan_risiko_start ? Intl.DateTimeFormat('id-ID', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }).format(new Date(perlakuanMonitoring.timeline_perlakuan_risiko_start)) : '');

            const perlakuanDocuments = perlakuanPenyebab.documents.filter(d => d.project_monitoring_id == perlakuanMonitoring.project_monitoring_id);

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
        //const jenisProgramRkap = $('#formUpdateRealisasi :input[name="jenis_program_rkap"]').val();
        // const jenisProgramRkapId = $('#formUpdateRealisasi :input[name="jenis_program_rkap_id"]').val();
        // const jenisProgramRkap = $('#formUpdateRealisasi :input[name="jenis_program_rkap_id"] option:selected').text();
        const timelinePerlakuanRisiko = $('#formUpdateRealisasi :input[name="timeline_perlakuan_risiko"]').val();

        if (!timelinePerlakuanRisiko) {
            Swal.fire('Error', 'Timeline perlakuan risiko harus diisi', 'error');
            return;
        }

        // update perlakuan penyebab risiko
        perlakuanPenyebabRisikos[id]['realisasi_biaya_perlakuan_risiko'] = realisasiBiayaPerlakuanRisiko;
        perlakuanPenyebabRisikos[id]['progress_rencana_perlakuan_risiko'] = progressPerlakuanRisiko;
        perlakuanPenyebabRisikos[id]['deskripsi_perlakuan_risiko'] = deskripsiPerlakuanRisiko;
        // perlakuanPenyebabRisikos[id]['jenis_program_rkap'] = jenisProgramRkap;
        // perlakuanPenyebabRisikos[id]['jenis_program_rkap_id'] = jenisProgramRkapId;
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
        const statusMap = {
            '1': 'Aman',
            '2': 'Waspada',
            '3': 'Bahaya'
        };
        const statusText = statusMap[statusKri] || '-';

        // update kri
        kriProjects[id]['nilai_kri_terkini'] = nilaiKri;
        kriProjects[id]['status_kri_terkini'] = statusKri;

        // update DOM
        const tr = $('#table-kri tr[data-id="' + id + '"]');
        tr.find('.display-nilai-kri').text(nilaiKri);
        tr.find('.display-kondisi').text(statusText);

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
        const kategoriDampak = '{{ $projectRiskAnalisa->kategori_dampak }}';
        const nilaiDampak = parseFloat($('#realisasi_nilai_dampak').val()) || 0;
        const riskLimit = parseFloat('{{ $risk_limit }}') || 0;
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
            skalaDampakSelect.prop('disabled', true);
            skalaDampakHidden.val(skala);
        } else {
            // Enable select jika bukan Kuantitatif
            skalaDampakSelect.prop('disabled', false);
            // Kosongkan hidden input
            skalaDampakHidden.val('0');
        }
    }

    // Event listener untuk perubahan nilai dampak
    $('#realisasi_nilai_dampak').on('change', function() {
        console.log("hitung skala dampak");
        hitungRealisasiSkalaDampak();
        refreshSkalaAndLevelRisiko();
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
    hitungRealisasiSkalaDampak();
    refreshSkalaAndLevelRisiko();
});
</script>
@endpush
