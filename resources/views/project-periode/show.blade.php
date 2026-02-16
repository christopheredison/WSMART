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
                    {{-- <div class="row d-flex align-items-center mt-3 mt-md-0">
                        <label class="col-md-3">Nilai OK</label>
                        <div class="col-md-9">
                            {{ $projectPeriode->project->nk }}
                        </div>
                    </div> --}}
                    <div class="row d-flex align-items-center mt-3">
                        <label class="col-md-3">Risk Limit</label>
                        <div class="col-md-9">
                            {{ Form::text('risk_limit', ($projectPeriode->project->nk ?? 0) * 0.03, ['class' => 'form-control inputmask-general', 'readonly']) }}
                        </div>
                    </div>
                    {{-- <div class="row d-flex align-items-center mt-3">
                        <label class="col-md-3">Batas Nilai</label>
                        <div class="col-md-9">
                            {{ Form::text('batas_nilai', $projectPeriode->project->batas_nilai, ['class' => 'form-control inputmask-general', 'readonly']) }}
                        </div>
                    </div> --}}
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
                                                <div class="divider-text">LIKELIHOOD</div>
                                            </div>
                                        </td>
                                    @endif
                                    @for($impact = 1; $impact <= 5; $impact++)
                                        @php $riskMap = $riskMaps[$impact . '-' . $likelihood] ?? null; @endphp
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
                                        <div class="divider m-0"><div class="divider-text">IMPACT</div></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="risk-map-legend d-flex flex-center gap-3">
                            <div class="d-flex align-items-center gap-1"><i class='bx bx-circle inherent'></i> Inherent</div>
                            <div class="d-flex align-items-center gap-1"><i class='bx bxs-circle residual'></i> Residual</div>
                            <div class="d-flex align-items-center gap-1"><i class="bx bxs-circle current"></i> Current</div>
                        </div>
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
                                                <div class="divider-text">LIKELIHOOD</div>
                                            </div>
                                        </td>
                                    @endif
                                    @for($impact = 1; $impact <= 5; $impact++)
                                        @php $riskMap = $riskMaps[$impact . '-' . $likelihood] ?? null; @endphp
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
                                        <div class="divider m-0"><div class="divider-text">IMPACT</div></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                         <div class="risk-map-legend d-flex flex-center gap-3">
                            <div class="d-flex align-items-center gap-1"><i class='bx bx-circle inherent'></i> Inherent</div>
                            <div class="d-flex align-items-center gap-1"><i class='bx bxs-circle residual'></i> Residual</div>
                            <div class="d-flex align-items-center gap-1"><i class="bx bxs-circle current"></i> Current</div>
                        </div>
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
                                <th rowspan="2" class="align-middle">No</th>
                                <th rowspan="2" class="align-middle">Peristiwa Risiko</th>
                                <th rowspan="2" class="align-middle">Deskripsi Peristiwa Risiko</th>

                                <th colspan="6" class="text-center bg-light">Inherent</th>
                                <th colspan="6" class="text-center" style="background-color: #e8f4fd;">Realisasi / Current</th>
                                <th colspan="6" class="text-center bg-light">Residual</th>
                                <th rowspan="2" class="align-middle">Status</th>
                            </tr>
                            <tr>
                                {{-- Inherent --}}
                                <th class="bg-light">Nilai Dampak</th>
                                <th class="bg-light">Skala Dampak</th>
                                <th class="bg-light">Nilai Prob.</th>
                                <th class="bg-light">Skala Prob.</th>
                                <th class="bg-light">Nilai Risiko</th>
                                <th class="bg-light">Level Risiko</th>

                                {{-- Realisasi (Kolom Baru) --}}
                                <th style="background-color: #e8f4fd;">Nilai Dampak</th>
                                <th style="background-color: #e8f4fd;">Skala Dampak</th>
                                <th style="background-color: #e8f4fd;">Nilai Prob.</th>
                                <th style="background-color: #e8f4fd;">Skala Prob.</th>
                                <th style="background-color: #e8f4fd;">Nilai Risiko</th>
                                <th style="background-color: #e8f4fd;">Level Risiko</th>

                                {{-- Residual --}}
                                <th class="bg-light">Nilai Dampak</th>
                                <th class="bg-light">Skala Dampak</th>
                                <th class="bg-light">Nilai Prob.</th>
                                <th class="bg-light">Skala Prob.</th>
                                <th class="bg-light">Nilai Risiko</th>
                                <th class="bg-light">Level Risiko</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projectPeriode->projectRisks as $projectRisk)
                            <tr data-risk-id="{{ $projectRisk->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                  <a href="{{ route('projects.risks.view', ['project' => $projectRisk->project_periode_list_id, 'risk' => $projectRisk->id]) }}">
                                    {{ $projectRisk->peristiwa_risiko_id == 0 ? $projectRisk->rencana_kegiatan : ($projectRisk->peristiwaRisiko?->title ?? '-') }}
                                  </a>
                                </td>
                                <td>{{ $projectRisk->deskripsi_peristiwa_risiko ?? '-' }}</td>

                                {{-- INHERENT --}}
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak, 0, ',', '.') : '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->skalaDampakObj?->tingkat ? '(' . $projectRisk->projectRiskAnalisa?->skalaDampakObj?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaDampakObj?->deskripsi : '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_probabilitas ?? '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->tingkat ? '(' . $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaProbabilitas?->skala : '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->skala_risiko ?? '-' }}</td>
                                <td class="bg-{{str_replace(' ', '-', str_replace('to ', '', strtolower($projectRisk->projectRiskAnalisa?->level_risiko)))}}">{{ $projectRisk->projectRiskAnalisa?->level_risiko ?? '-' }}</td>

                                {{-- REALISASI / CURRENT (Diisi via JS) --}}
                                <td class="realisasi-nilai-dampak">-</td>
                                <td class="realisasi-skala-dampak">-</td>
                                <td class="realisasi-nilai-prob">-</td>
                                <td class="realisasi-skala-prob">-</td>
                                <td class="realisasi-nilai-risiko">-</td>
                                <td class="realisasi-level-risiko">-</td>

                                {{-- RESIDUAL --}}
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_dampak_residual ? 'Rp ' . number_format($projectRisk->projectRiskAnalisa->nilai_dampak_residual, 0, ',', '.') : '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->skalaDampakResidualObj?->tingkat ? '(' . $projectRisk->projectRiskAnalisa?->skalaDampakResidualObj?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaDampakResidualObj?->deskripsi : '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->nilai_probabilitas_residual ?? '-' }}</td>
                                <td>{{ $projectRisk->projectRiskAnalisa?->skalaProbabilitasResidual?->tingkat ? '(' . $projectRisk->projectRiskAnalisa?->skalaProbabilitasResidual?->tingkat . ') ' . $projectRisk->projectRiskAnalisa?->skalaProbabilitasResidual?->skala : '-' }}</td>
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
                                <td colspan="22" class="text-center p-3">Tidak ada data</td>
                            </tr>
                            @endforelse
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
/* Style sama seperti sebelumnya */
.kode-peristiwa { display: flex; flex-wrap: wrap; gap: 5px; position: absolute; bottom: 5px; right: 0; width: calc(100% - 5px) !important; }
.box-inherent { background-color: #fff; color: #000; padding: 2px 5px; border-radius: 5px; }
.box-residual { background-color: #000; color: #fff; padding: 2px 5px; border-radius: 5px; }
.box-current { background-color: #007bff; color: #fff; padding: 2px 5px; border-radius: 5px; }

/* Menyembunyikan/Menampilkan PIN pada Peta Risiko Current sesuai pilihan */
#currentMap .current-item { display: none; }

@foreach ($tahunMonitorings as $tahun)
    @for ($month = 1; $month <= 12; $month++)
    #currentMap.show-{{ $tahun }}-m{{ $month }} .current-{{ $tahun }}-m{{ $month }} { display: block; }
    @endfor
@endforeach
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(document).ready(function () {
    // --- Inisialisasi Data ---
    const risks = @json($projectPeriode->projectRisks->values());
    const formattedCurrentRiskMaps = @json($formattedCurrentRiskMaps);
    const riskRealisasiData = @json($riskRealisasiData);

    // --- Helper Formatting Currency ---
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    };

    const getLevelClass = (levelName) => {
        if (!levelName) return '';
        return 'bg-' + levelName.toLowerCase().replace('to ', '').replace(' ', '-');
    };

    // --- 1. Render Peta Inheren & Residual (Statis) ---
    risks.forEach((risk, idx) => {
        const matrixI = risk.project_risk_analisa?.skala_dampak + '-' + risk.project_risk_analisa?.skala_probabilitas?.tingkat;
        const matrixR = risk.project_risk_analisa?.skala_dampak_residual + '-' + risk.project_risk_analisa?.skala_probabilitas_residual?.tingkat;
        const code = (idx + 1).toString();

        const cellI = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixI}"]`);
        if (cellI.length) {
            let html = cellI.find('.kode-peristiwa').html();
            html += `<span class="box-inherent">R${code}</span>`;
            cellI.find('.kode-peristiwa').html(html);
        }

        const cellR = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixR}"]`);
        if (cellR.length) {
            let html = cellR.find('.kode-peristiwa').html();
            html += `<span class="box-residual">R${code}</span>`;
            cellR.find('.kode-peristiwa').html(html);
        }
    });

    // --- 2. Render Peta Current (PIN disiapkan semua, hidden by default via CSS) ---
    Object.keys(formattedCurrentRiskMaps).forEach((riskId) => {
        // Cari index risiko untuk mendapatkan kode R1, R2, dll
        const riskIdx = risks.findIndex(r => r.id == riskId);
        const code = (riskIdx + 1).toString();
        const yearsData = formattedCurrentRiskMaps[riskId];

        Object.keys(yearsData).forEach((tahun) => {
            yearsData[tahun].forEach((mapData) => {
                const matrixC = mapData.skala_dampak + '-' + mapData.skala_probabilitas;
                const cellC = $(`#currentMap.table-risk-map .data-cell[data-matrix="${matrixC}"]`);

                if (cellC.length) {
                    let html = cellC.find('.kode-peristiwa').html();
                    // Tambahkan class spesifik: current-2026-m1, dll
                    html += `<span class="box-current current-item current-${tahun}-m${mapData.month}">R${code}</span>`;
                    cellC.find('.kode-peristiwa').html(html);
                }
            });
        });
    });

    // --- 3. Fungsi Update Tampilan (Peta & Tabel) saat Filter Berubah ---
    function updateDashboard(month, year) {
        // A. Update Peta Risiko
        $('#currentMap').prop('class', 'table-risk-map');
        $('#currentMap').addClass('show-' + year + '-m' + month);

        // B. Update Tabel Risiko (Kolom Realisasi)
        $('tr[data-risk-id]').each(function() {
            const tr = $(this);
            const riskId = tr.data('risk-id');
            const key = year + '-' + month;

            // Default values
            let valDampak = '-', valSkalaDampak = '-', valProb = '-', valSkalaProb = '-', valRisiko = '-', valLevel = '-', classLevel = '';

            if (riskRealisasiData[riskId] && riskRealisasiData[riskId][key]) {
                const data = riskRealisasiData[riskId][key];

                valDampak = data.nilai_dampak ? formatRupiah(data.nilai_dampak) : '-';
                valSkalaDampak = data.skala_dampak ? `(${data.skala_dampak}) ${data.skala_dampak_desc ?? ''}` : '-';
                valProb = data.nilai_probabilitas ?? '-';
                valSkalaProb = data.skala_probabilitas ? `(${data.skala_probabilitas}) ${data.skala_probabilitas_desc ?? ''}` : '-';
                valRisiko = data.nilai_risiko ?? '-';
                valLevel = data.level_risiko ?? '-';
                classLevel = getLevelClass(data.level_risiko);
            }

            tr.find('.realisasi-nilai-dampak').text(valDampak);
            tr.find('.realisasi-skala-dampak').text(valSkalaDampak);
            tr.find('.realisasi-nilai-prob').text(valProb);
            tr.find('.realisasi-skala-prob').text(valSkalaProb);
            tr.find('.realisasi-nilai-risiko').text(valRisiko);

            const tdLevel = tr.find('.realisasi-level-risiko');
            tdLevel.text(valLevel);
            // Reset class bg-* lalu tambah class baru
            tdLevel.removeClass(function (index, className) {
                return (className.match (/(^|\s)bg-\S+/g) || []).join(' ');
            });
            if (classLevel) tdLevel.addClass(classLevel);
        });
    }

    // Event Listener Filter
    $('#monthSelect, #tahunSelect').on('change', function() {
        updateDashboard($('#monthSelect').val(), $('#tahunSelect').val());
    });

    // Trigger pertama kali load (ambil tanggal sekarang atau default selected)
    // Jika current month belum ada di opsi select (misal masuk tahun depan), sesuaikan logic
    const currentMonth = new Date().getMonth() + 1;
    const currentYear = new Date().getFullYear();

    // Set default value jika ada di opsi, jika tidak biarkan default blade
    if ($("#tahunSelect option[value='"+currentYear+"']").length > 0) {
        $('#tahunSelect').val(currentYear);
    }
    $('#monthSelect').val(currentMonth);

    // Jalankan update
    updateDashboard($('#monthSelect').val(), $('#tahunSelect').val());

    // --- Script Input Mask & Lainnya (Bawaan) ---
    $('.inputmask-general').inputmask({
        alias: 'numeric', groupSeparator: '.', radixPoint: ',', autoGroup: true, digits: 2,
        autoUnmask: true, removeMaskOnSubmit: true
    });

    $('#statusFilter').on('change', function() {
        const currentUrl = new URL(window.location.href);
        if ($(this).val()) currentUrl.searchParams.set('status', $(this).val());
        else currentUrl.searchParams.delete('status');
        window.location.href = currentUrl.toString();
    });
});
</script>
@endpush
