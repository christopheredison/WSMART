@extends('layouts.default')
@section('dashboard')
    <div class="row mb-5">
        <div class="col-12 d-flex align-items-center gap-3 position-relative">
            <div class="svg-icon svg-icon-secondary">
                @include('partials.icon-tool')
            </div>
            <h3 class="mb-0">Data {{ $targetUnit->name }} Periode {{ $periode->tahun }}</h3>
            <div class="ms-auto">
                <button id="exportPdfBtn" class="btn btn-sm btn-danger ms-auto d-flex align-items-center gap-1">
                    <i class='bx bxs-file-pdf'></i> Export PDF
                </button>
            </div>
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
                            <select class="form-select" id="monthSelect">
                                @for ($month = 1; $month <= 12; $month++)
                                    @php $quarter = ceil($month / 3); @endphp
                                    <option value="{{ $month }}">Q{{ $quarter }} - {{ __('basic.month.' . $month) }}</option>
                                @endfor
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
                                <th>No</th>
                                <th>Peristiwa Risiko</th>
                                <th>Deskripsi Peristiwa Risiko</th>
                                <th>Nilai Dampak Inherent</th>
                                <th>Skala Dampak Inherent</th>
                                <th>Nilai Probabilitas Inherent</th>
                                <th>Skala Probabilitas Inherent</th>
                                <th>Nilai Risiko Inherent</th>
                                <th>Level Risiko Inherent</th>
                                <th>Nilai Dampak Residual <small>(Sesuai Kuartal)</small></th>
                                <th>Skala Dampak Residual</th>
                                <th>Nilai Probabilitas Residual</th>
                                <th>Skala Probabilitas Residual</th>
                                <th>Nilai Risiko Residual</th>
                                <th>Level Risiko Residual</th>
                                <th>Status Risiko</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($risikos as $risiko)
                            <tr data-risk-id="{{ $risiko->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $risiko->peristiwa_risiko ?? '-' }}</td>
                                <td>{{ $risiko->deskripsi_peristiwa_risiko ?? '-' }}</td>
                                <td>{{ $risiko->riskAnalysis?->nilai_dampak ? 'Rp ' . number_format($risiko->riskAnalysis->nilai_dampak, 0, ',', '.') : '-' }}</td>
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
                                <td class="residual-nilai-dampak">-</td>
                                <td class="residual-skala-dampak">-</td>
                                <td class="residual-nilai-prob">-</td>
                                <td class="residual-skala-prob">-</td>
                                <td class="residual-nilai-risiko">-</td>
                                <td class="residual-level-risiko">-</td>
                                <td>
                                    @if ($risiko->is_closed)
                                        <span class="badge bg-danger rounded-pill px-2 mt-auto d-inline-flex align-items-center">
                                            Closed
                                            @if ($risiko->closed_at_formatted)
                                                <i class="bx bx-info-circle ms-1" style="cursor:pointer;font-size:0.95em;"
                                                   data-bs-toggle="popover"
                                                   data-bs-trigger="hover focus"
                                                   data-bs-placement="top"
                                                   data-bs-content="Ditutup pada: {{ $risiko->closed_at_formatted }}"
                                                   title=""></i>
                                            @endif
                                        </span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-2 mt-auto">Open</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            @if ($risikos->isEmpty())
                            <tr>
                                <td colspan="16" class="text-center p-3">Tidak ada data</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
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
#currentMap .current-m1, #currentMap .current-m2, #currentMap .current-m3, #currentMap .current-m4, #currentMap .current-m5, #currentMap .current-m6, #currentMap .current-m7, #currentMap .current-m8, #currentMap .current-m9, #currentMap .current-m10, #currentMap .current-m11, #currentMap .current-m12 {
    display: none;
}

@for ($month = 1; $month <= 12; $month++)
#currentMap.show-m{{ $month }} .current-m{{ $month }} {
    display: block;
}
@endfor
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

    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
        new bootstrap.Popover(el);
    });

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
    const riskResidualData = @json($riskResidualData);

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
    };

    const getLevelClass = (levelName) => {
        if (!levelName) return '';
        return 'bg-' + levelName.toLowerCase().replace('to ', '').replace(/\s+/g, '-');
    };

    function getResidualMatrix(risk, quarter) {
        const analysis = risk.risk_analysis;
        if (!analysis) {
            return null;
        }

        const skalaDampak = analysis[`skala_dampak_residual_q${quarter}`];
        const skalaProb = analysis[`skala_probabilitas_residual_q${quarter}`]?.tingkat;

        if (skalaDampak == null || skalaProb == null) {
            return null;
        }

        return `${skalaDampak}-${skalaProb}`;
    }

    function renderInherentMapMarkers() {
        $('#inherentMap.table-risk-map .data-cell').each(function() {
            $(this).removeData('kode-peristiwa-inherent');
            $(this).removeData('kode-peristiwa-residual');
            $(this).removeData('has-inherent');
            $(this).removeData('has-residual');
            $(this).find('.kode-peristiwa').empty();
        });

        risks.forEach((risk, idx) => {
            const matrixI = risk.risk_analysis?.skala_dampak + '-' + risk.risk_analysis?.skala_probabilitas?.tingkat;
            const cellI = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixI}"]`);
            const code = (idx + 1).toString();

            if (cellI.length) {
                if (!cellI.data('kode-peristiwa-inherent')) {
                    cellI.data('kode-peristiwa-inherent', []);
                }
                cellI.data('kode-peristiwa-inherent').push(code);
                cellI.data('has-inherent', true);
            }
        });

        paintInherentResidualMapCells();
    }

    function renderResidualMapMarkers(quarter) {
        $('#inherentMap.table-risk-map .data-cell').each(function() {
            $(this).removeData('kode-peristiwa-residual');
            $(this).removeData('has-residual');
        });

        risks.forEach((risk, idx) => {
            const matrixR = getResidualMatrix(risk, quarter);
            if (!matrixR) {
                return;
            }

            const cellR = $(`#inherentMap.table-risk-map .data-cell[data-matrix="${matrixR}"]`);
            const code = (idx + 1).toString();

            if (cellR.length) {
                if (!cellR.data('kode-peristiwa-residual')) {
                    cellR.data('kode-peristiwa-residual', []);
                }
                cellR.data('kode-peristiwa-residual').push(code);
                cellR.data('has-residual', true);
            }
        });

        paintInherentResidualMapCells();
    }

    function paintInherentResidualMapCells() {
        $('#inherentMap.table-risk-map .data-cell').each(function() {
            const cell = $(this);
            let html = '';

            const kodePeristiwaInherent = cell.data('kode-peristiwa-inherent');
            if (kodePeristiwaInherent && kodePeristiwaInherent.length > 0) {
                for (let i = 0; i < kodePeristiwaInherent.length; i++) {
                    html += `<span class="box-inherent">R${kodePeristiwaInherent[i]}</span>`;
                }
            }

            const kodePeristiwaResidual = cell.data('kode-peristiwa-residual');
            if (kodePeristiwaResidual && kodePeristiwaResidual.length > 0) {
                for (let i = 0; i < kodePeristiwaResidual.length; i++) {
                    html += `<span class="box-residual">R${kodePeristiwaResidual[i]}</span>`;
                }
            }

            cell.find('.kode-peristiwa').html(html);
        });
    }

    function renderCurrentMapMarkers() {
        $('#currentMap.table-risk-map .data-cell').each(function() {
            for (let month = 1; month <= 12; month++) {
                $(this).removeData('kode-peristiwa-current-m' + month);
            }
            $(this).removeData('has-current');
            $(this).find('.kode-peristiwa').empty();
        });

        risks.forEach((risk, idx) => {
            const currentRiskMaps = formattedCurrentRiskMaps[risk.id] || [];
            const code = (idx + 1).toString();

            currentRiskMaps.forEach((currentRiskMap) => {
                if (!currentRiskMap?.month) {
                    return;
                }

                const matrixC = currentRiskMap.skala_dampak + '-' + currentRiskMap.skala_probabilitas;
                const cellC = $(`#currentMap.table-risk-map .data-cell[data-matrix="${matrixC}"]`);

                if (cellC.length) {
                    const monthKey = 'kode-peristiwa-current-m' + currentRiskMap.month;
                    if (!cellC.data(monthKey)) {
                        cellC.data(monthKey, []);
                    }
                    cellC.data(monthKey).push(code);
                    cellC.data('has-current', true);
                }
            });
        });

        $('#currentMap.table-risk-map .data-cell').each(function() {
            const cell = $(this);
            let html = '';

            for (let month = 1; month <= 12; month++) {
                const kodePeristiwaCurrent = cell.data('kode-peristiwa-current-m' + month);
                if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                    for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                        html += `<span class="box-current current-m${month}">R${kodePeristiwaCurrent[i]}</span>`;
                    }
                }
            }

            cell.find('.kode-peristiwa').html(html);
        });
    }

    function updateDashboard(month) {
        const quarter = Math.ceil(month / 3);

        $('#currentMap').prop('class', 'table-risk-map');
        $('#currentMap').addClass('show-m' + month);

        renderResidualMapMarkers(quarter);

        $('tr[data-risk-id]').each(function() {
            const tr = $(this);
            const riskId = tr.data('risk-id');

            if (riskResidualData[riskId] && riskResidualData[riskId][quarter]) {
                const dataRes = riskResidualData[riskId][quarter];

                tr.find('.residual-nilai-dampak').text(dataRes.nilai_dampak ? formatRupiah(dataRes.nilai_dampak) : '-');
                tr.find('.residual-skala-dampak').text(dataRes.skala_dampak ?? '-');
                tr.find('.residual-nilai-prob').text(dataRes.nilai_prob ?? '-');
                tr.find('.residual-skala-prob').text(dataRes.skala_prob ?? '-');
                tr.find('.residual-nilai-risiko').text(dataRes.skala_risiko ?? '-');

                const tdResLevel = tr.find('.residual-level-risiko');
                tdResLevel.text(dataRes.level_risiko ?? '-');
                tdResLevel.removeClass(function (index, className) {
                    return (className.match(/(^|\s)bg-\S+/g) || []).join(' ');
                });
                if (dataRes.level_risiko) tdResLevel.addClass(getLevelClass(dataRes.level_risiko));
            }
        });
    }

    const currentMonth = new Date().getMonth() + 1;
    $('#monthSelect').val(currentMonth);
    renderInherentMapMarkers();
    renderCurrentMapMarkers();
    updateDashboard(currentMonth);

    $('#monthSelect,#tahunSelect').on('change', function() {
        updateDashboard($('#monthSelect').val());
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

    $('#exportPdfBtn').on('click', function() {
        const { jsPDF } = window.jspdf;
        const exportArea = document.getElementById('exportArea');
        const button = $(this);

        const namaUnit = "{{ str_replace(' ', '-', strtolower($targetUnit->name)) }}";
        const tahun = "{{ $periode->tahun }}";
        const month = $('#monthSelect').val();
        const fileName = `peta-risiko-${namaUnit}-tahun-${tahun}-q${month}.pdf`;

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
