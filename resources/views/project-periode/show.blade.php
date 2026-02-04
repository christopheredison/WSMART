@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Data Proyek {{ $projectPeriode->project->project_name }}</h3>
        </div>
    </div>

    <div class="card">
        <div class="card-header stepper border-0 pb-0">
            <div class="nav-link active d-flex align-items-center p-0">
                <span class="h3 mb-0">Data Project</span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row d-flex align-items-center">
                        <label class="col-md-3">Kode Project</label>
                        <div class="col-md-9">
                            {{ Form::text('project_code', $projectPeriode->project->meta['profit_center'] ?? '-', ['class' => 'form-control', 'readonly']) }}
                        </div>
                    </div>
                    <div class="row d-flex align-items-center mt-3">
                        <label class="col-md-3">Nama Project</label>
                        <div class="col-md-9">
                            {{ Form::text('project_name', $projectPeriode->project->project_name, ['class' => 'form-control', 'readonly']) }}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row d-flex align-items-center mt-3 mt-md-0">
                        <label class="col-md-3">Risk Limit</label>
                        <div class="col-md-9">
                            {{ Form::text('risk_limit', ($projectPeriode->project->nk ?? 0) * 0.03, ['class' => 'form-control inputmask-general', 'readonly']) }}
                        </div>
                    </div>
                    <div class="row d-flex align-items-center mt-3">
                        <label class="col-md-3">Batas Nilai</label>
                        <div class="col-md-9">
                            {{ Form::text('batas_nilai', $projectPeriode->project->batas_nilai, ['class' => 'form-control inputmask-general', 'readonly']) }}
                        </div>
                    </div>
                </div>
            </div>
            @php
              $userProjects = $user->projects->pluck('id')->toArray();
            @endphp
            @if ($userProjects && in_array($projectPeriode->project->id, $userProjects))
              <div class="mb-2 mt-4">
                  <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalEdit">Edit</button>
              </div>
            @endif
        </div>
    </div>

    <div class="card mt-5">
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
                            <select class="form-select" id="monthSelect">
                                @for ($month = 1; $month <= 12; $month++)
                                    @php $quarter = ceil($month / 3); @endphp
                                    <option value="{{ $month }}">Q{{ $quarter }} - {{ __('basic.month.' . $month) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col">
                            <select class="form-select" id="tahunSelect">
                                @foreach ($tahunMonitorings as $tahun)
                                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                                @endforeach
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
            <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
                <h3 class="h4 mb-0">Daftar Risiko Proyek</h3>
                <div class="d-flex align-items-center">
                    <label for="statusFilter" class="me-2 fw-bold mb-0">Status Risiko:</label>
                    <select id="statusFilter" class="form-select form-select-sm w-auto">
                        <option value="">Semua</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
            </div>
            <div class="d-block mt-3">
                <div class="">
                    <table class="table table-responsive table-strategi">
                        <thead>
                            <tr>
                                <th>No</th>
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
                                <th>Status Risiko</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projectPeriode->projectRisks as $projectRisk)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                  <a href="{{ route('projects.risks.view', ['project' => $projectRisk->project_periode_list_id, 'risk' => $projectRisk->id]) }}">
                                    {{ $projectRisk->peristiwaRisiko?->title ?? '-' }}
                                  </a>
                                </td>
                                <td>{{ $projectRisk->deskripsi_peristiwa_risiko ?? '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak, 0, ',', '.') : '-' }}</td>
                                <td>
                                    {{ $projectRisk->projectRiskAnalisa?->skalaDampakObj?->tingkat
                                        ? '(' . $projectRisk->projectRiskAnalisa?->skalaDampakObj?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaDampakObj?->deskripsi
                                        : '-' }}
                                </td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_probabilitas ?? '-' }}</td>
                                <td>
                                    {{ $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->tingkat
                                        ? '(' . $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->skala
                                        : '-' }}
                                </td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->skala_risiko ?? '-' }}</td>
                                <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($projectRisk->projectRiskAnalisa?->level_risiko)))}}">{{ $projectRisk->projectRiskAnalisa?->level_risiko ?? '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak_residual ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak_residual, 0, ',', '.') : '-' }}</td>
                                <td>
                                    {{ $projectRisk->projectRiskAnalisa?->skalaDampakResidualObj?->tingkat
                                        ? '(' . $projectRisk->projectRiskAnalisa?->skalaDampakResidualObj?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaDampakResidualObj?->deskripsi
                                        : '-' }}
                                </td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_probabilitas_residual ?? '-' }}</td>
                                <td>
                                    {{ $projectRisk->projectRiskAnalisa?->skalaProbabilitasResidual?->tingkat
                                        ? '(' . $projectRisk->projectRiskAnalisa?->skalaProbabilitasResidual?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaProbabilitasResidual?->skala
                                        : '-' }}
                                </td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->skala_risiko_residual ?? '-' }}</td>
                                <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($projectRisk->projectRiskAnalisa?->level_risiko_residual)))}}">{{ $projectRisk->projectRiskAnalisa?->level_risiko_residual ?? '-' }}</td>
                                <td>
                                    @if ($projectRisk->is_closed)
                                        <span class="badge bg-danger rounded-pill px-2 mt-auto">Closed</span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-2 mt-auto">Open</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="16" class="text-center p-3">Tidak ada data</td>
                            </tr>
                            @endforelse
                            <tr id="no-data-filter" style="display: none;">
                                <td colspan="16" class="text-center p-3">Tidak ada data yang cocok dengan filter.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('master.basic-crud._modal_edit', ['fields' => $editFields, 'action' => route('projects.update', $projectPeriode->project->id), 'resourceName' => 'Project'])
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
#currentMap .current-m1, #currentMap .current-m2, #currentMap .current-m3, #currentMap .current-m4, #currentMap .current-m5, #currentMap .current-m6, #currentMap .current-m7, #currentMap .current-m8, #currentMap .current-m9, #currentMap .current-m10, #currentMap .current-m11, #currentMap .current-m12 {
    display: none;
}

@for ($month = 1; $month <= 12; $month++)
#currentMap.show-m{{ $month }} .current-m{{ $month }} {
    display: block;
}
@endfor
</style>

@foreach ($tahunMonitorings as $tahunMonitoring)
<style>
#currentMap .current-{{ $tahunMonitoring }}-m1, #currentMap .current-{{ $tahunMonitoring }}-m2, #currentMap .current-{{ $tahunMonitoring }}-m3, #currentMap .current-{{ $tahunMonitoring }}-m4, #currentMap .current-{{ $tahunMonitoring }}-m5, #currentMap .current-{{ $tahunMonitoring }}-m6, #currentMap .current-{{ $tahunMonitoring }}-m7, #currentMap .current-{{ $tahunMonitoring }}-m8, #currentMap .current-{{ $tahunMonitoring }}-m9, #currentMap .current-{{ $tahunMonitoring }}-m10, #currentMap .current-{{ $tahunMonitoring }}-m11, #currentMap .current-{{ $tahunMonitoring }}-m12 {
    display: none;
}

@for ($month = 1; $month <= 12; $month++)
#currentMap.show-{{ $tahunMonitoring }}-m{{ $month }} .current-{{ $tahunMonitoring }}-m{{ $month }} {
    display: block;
}
@endfor

</style>
@endforeach
@endpush

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(document).ready(function () {
    const inputmaskGeneral = $('.inputmask-general');
    const risks = @json($projectPeriode->projectRisks->values());

    $('#modalEdit input[name="nk_ppn"],#modalEdit input[name="rapk"]').on('change', function() {
        const nkPpn = $('#modalEdit input[name="nk_ppn"]').inputmask('unmaskedvalue');
        const rapk = $('#modalEdit input[name="rapk"]').inputmask('unmaskedvalue');
    });

    inputmaskGeneral.each(function() {
        const inputmask = $(this);
        const options = {
            alias: 'numeric',
            groupSeparator: '.',
            radixPoint: ',',
            autoGroup: true,
            digits: 2,
            digitsOptional: false,
            placeholder: '0',
            rightAlign: false,
            autoUnmask: true,
            removeMaskOnSubmit: true,
            onBeforeMask: function(maskedValue, opts) {
                return maskedValue.replace('.', ',');
            },
            onUnMask: function(maskedValue, unmaskedValue, opts) {
                return maskedValue.replaceAll('.', '').replace(',', '.');
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

    $('#statusFilter').on('change', function() {
        const selectedStatus = $(this).val();
        // Buat objek URL dari URL saat ini
        const currentUrl = new URL(window.location.href);

        if (selectedStatus) {
            // Jika ada status yang dipilih, set query parameter 'status'
            currentUrl.searchParams.set('status', selectedStatus);
        } else {
            // Jika memilih "Semua", hapus query parameter 'status'
            currentUrl.searchParams.delete('status');
        }

        // Arahkan browser ke URL yang baru
        window.location.href = currentUrl.toString();
    });


    const formattedCurrentRiskMaps = @json($formattedCurrentRiskMaps);
    risks.forEach((risk, idx) => {
        const matrixI = risk.project_risk_analisa?.skala_dampak + '-' + risk.project_risk_analisa?.skala_probabilitas?.tingkat;
        const matrixR = risk.project_risk_analisa?.skala_dampak_residual + '-' + risk.project_risk_analisa?.skala_probabilitas_residual?.tingkat;

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

        const formattedCurrentRiskMap = formattedCurrentRiskMaps[risk.id];
        Object.keys(formattedCurrentRiskMap).forEach((tahun) => {
            const currentRiskMaps = formattedCurrentRiskMap[tahun];
            currentRiskMaps.forEach((currentRiskMap) => {
                const matrixC = currentRiskMap.skala_dampak + '-' + currentRiskMap.skala_probabilitas;
                const cellC = $(`#currentMap.table-risk-map .data-cell[data-matrix="${matrixC}"]`);

                if (cellC.length) {
                    if (!cellC.data('kode-peristiwa-current-' + tahun + '-m' + currentRiskMap.month)) {
                        cellC.data('kode-peristiwa-current-' + tahun + '-m' + currentRiskMap.month, []);
                    }

                    cellC.data('kode-peristiwa-current-' + tahun + '-m' + currentRiskMap.month).push(code);
                    cellC.data('has-current', true);
                }
            });
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
            @foreach ($tahunMonitorings as $tahun)
            @for ($month = 1; $month <= 12; $month++)
            kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-{{ $tahun }}-m{{ $month }}');
            if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                    html += `<span class="box-current current-{{ $tahun }}-m{{ $month }}">R${kodePeristiwaCurrent[i]}</span>`;
                }
            }
            @endfor
            @endforeach

            $(cell).find('.kode-peristiwa').html(html);
        });
    });

    $('#monthSelect,#tahunSelect').on('change', function() {
        const month = $('#monthSelect').val();
        const tahun = $('#tahunSelect').val();
        $('#currentMap').prop('class', 'table-risk-map');
        $('#currentMap').addClass('show-' + tahun + '-m' + month);
    }).change();

    flatpickr('.flatpickr-range', {
        mode: 'range',
        dateFormat: 'd/m/Y',
        altInput: true,
        altFormat: 'd/m/Y',
        locale: {
            rangeSeparator: ' - '
        }
    });

    flatpickr('.flatpickr', {
        dateFormat: 'd/m/Y',
        altInput: true,
        altFormat: 'd/m/Y',
        locale: {
            rangeSeparator: ' - '
        }
    });

    const select2modals = $('.select2-modal');
    select2modals.each(function() {
        const select2modal = $(this);
        const parent = select2modal.closest('.modal-body');
        select2modal.select2({
            dropdownParent: parent,
        });
    });

    // $('#project_divisi_id').on('change', function() {
    //     var divisiId = $(this).val(); // Ambil nilai yang dipilih
    //     var sektorSelect = $('#project_sektor_id');

    //     if (divisiId) {
    //         $.ajax({
    //             url: '/get-sektors/' + divisiId, // Panggil endpoint
    //             type: 'GET',
    //             dataType: 'json',
    //             success: function(data) {
    //                 sektorSelect.empty().append('<option value="">Pilih Konstruksi Spesifik</option>');

    //                 $.each(data, function(id, name) {
    //                     sektorSelect.append('<option value="' + id + '">' + name + '</option>');
    //                 });
    //             }
    //         });
    //     } else {
    //         sektorSelect.empty().append('<option value="">Pilih Konstruksi Spesifik</option>');
    //     }
    // });
});
</script>
@endpush
