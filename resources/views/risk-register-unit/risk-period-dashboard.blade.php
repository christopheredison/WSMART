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
            <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
                <h3 class="h4 mb-0">Daftar Risiko Divisi</h3>
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
                            @foreach($risikos as $risiko)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                  <a href="{{ route('risk-register-unit.view', ['riskRegister' => $risiko->id]) }}">
                                    {{ $risiko->peristiwa_risiko ?? '-' }}
                                  </a>
                                </td>
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
                                <td>
                                    @if ($risiko->is_closed)
                                        <span class="badge bg-danger rounded-pill px-2 mt-auto">Closed</span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-2 mt-auto">Open</span>
                                    @endif
                                </td>
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
                if (!cellC.data('kode-peristiwa-current-m' + currentRiskMap.month)) {
                    cellC.data('kode-peristiwa-current-m' + currentRiskMap.month, []);
                }

                cellC.data('kode-peristiwa-current-m' + currentRiskMap.month).push(code);
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
            for (let month = 1; month <= 12; month++) {
                kodePeristiwaCurrent = $(cell).data('kode-peristiwa-current-m' + month);
                if (kodePeristiwaCurrent && kodePeristiwaCurrent.length > 0) {
                    for (let i = 0; i < kodePeristiwaCurrent.length; i++) {
                        html += `<span class="box-current current-m${month}">R${kodePeristiwaCurrent[i]}</span>`;
                    }
                }
            }

            $(cell).find('.kode-peristiwa').html(html);
        });
    });

    $('#monthSelect,#tahunSelect').on('change', function() {
        const month = $('#monthSelect').val();
        const tahun = $('#tahunSelect').val();
        $('#currentMap').prop('class', 'table-risk-map');
        $('#currentMap').addClass('show-m' + month);
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
