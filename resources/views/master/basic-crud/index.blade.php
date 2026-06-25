@extends('layouts.default')
@section('dashboard')
    @include('partials.success-message')
    @if(session('import_summary'))
        @php $summary = session('import_summary'); @endphp
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <h4 class="alert-heading">Ringkasan Import</h4>
            <p>
                - <strong>Berhasil:</strong> {{ $summary['success'] }} baris <br>
                - <strong>Dilewati:</strong> {{ $summary['skipped'] }} baris <br>
                - <strong>Gagal:</strong> {{ $summary['failed'] }} baris
            </p>

            @if(!empty($summary['skipped_rows']))
                <hr>
                <h6>Detail Baris yang Dilewati:</h6>
                <ul class="mb-0 small" style="padding-left: 20px;">
                    @foreach($summary['skipped_rows'] as $skipped_info)
                        <li>{{ $skipped_info }}</li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($summary['failed_rows']))
                <hr>
                <h6>Detail Kegagalan:</h6>
                <ul class="mb-0 small" style="padding-left: 20px;">
                    @foreach($summary['failed_rows'] as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="lead__icon bg-warning-subtle">
                            <div class="svg-icon svg-icon-warning">
                                @include('partials.icon-layer')
                            </div>
                        </div>
                        <div>
                          <h2 class="h3">Data {!! $indexTitle ?? $resourceName !!}</h2>
                          @if (!empty($indexSubtitle))
                              <div class="ff-preheading mb-0 mt-1">{{ $indexSubtitle }}</div>
                          @endif
                        </div>
                        <div class="ms-auto d-flex align-items-center gap-2">
                        @if($createType && \Gate::check( $basePermission . '_create'))
                            @if ($createType == 'modal')
                                @if($createFields)
                                <div id="bulk-select-replace-element" class="col-auto ms-auto">
                                    <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#modalCreate">
                                        <span class="bx bx-plus"></span>
                                        <span class="ms-1">Tambah {{ $resourceName }}</span>
                                    </button>
                                </div>
                                @include('master.basic-crud._modal_create', ['fields' => $createFields, 'action' => route($baseRoute . 'store', $baseRouteParams ?? [])])
                                @endif
                            @elseif ($createType == 'sync')
                                <div id="bulk-select-replace-element" class="col-auto ms-auto">
                                    <a class="btn btn-outline-info btn-sm btn-action" data-action="sync_data">
                                        <span class="bx bx-sync"></span>
                                        <span class="ms-1">Sinkronsi {{ $resourceName }}</span>
                                    </a>
                                </div>
                            @elseif ($createType == 'script')
                                <script>
                                    function addData() {
                                        {!! $createScript !!}
                                    }
                                </script>
                                <div id="bulk-select-replace-element" class="col-auto ms-auto">
                                    <a class="btn btn-outline-info btn-sm btn-action" onclick="addData()">
                                        <span class="bx bx-plus"></span>
                                        <span class="ms-1">Tambah {{ $resourceName }}</span>
                                    </a>
                                </div>
                            @else
                                @if((empty($extraViewData['status']) || $extraViewData['status'] == \App\Models\DataBatch::STATUS_PROSES || $extraViewData['status'] == \App\Models\DataBatch::STATUS_REVISI) && (empty($extraViewData['levelId']) || $extraViewData['levelId'] == 6))
                                <div id="bulk-select-replace-element" class="col-auto ms-auto">
                                    <a class="btn btn-outline-info btn-sm" href="{{ route($baseRoute . 'create', $baseRouteParams ?? []) }}">
                                        <span class="bx bx-plus"></span>
                                        <span class="ms-1">Tambah {{ $resourceName }}</span>
                                    </a>
                                </div>
                                @endif
                            @endif
                        @endif
                        @if(empty($extraViewData['status']) || $extraViewData['status'] == \App\Models\DataBatch::STATUS_PROSES)
                            @if (!empty($importConfig))
                            <div id="bulk-select-replace-element" class="col-auto ms-auto">
                                <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#importDataModal">
                                <span class="bx bx-upload"></span>
                                <span class="ms-1">{{ $importConfig['buttonText'] ?? 'Import Data' }}</span>
                                </button>
                            </div>
                            @endif
                            @if (!empty($extraViewData['showKamusRisikoButton']))
                            <div class="col-auto ms-auto">
                                <a href="{{ route('kamus-risiko-project.index') }}" class="btn btn-outline-danger btn-sm">
                                <span class="bx bx-book-bookmark"></span>
                                <span class="ms-1">Kamus Risiko</span>
                                </a>
                            </div>
                            @endif
                        @endif
                        </div>
                    </div>
                </div>

                @if(!empty($tableLegend))
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <h6 class="mb-0">Keterangan :</h6>
                        <div class="d-flex gap-3">
                            @foreach($tableLegend as $legend)
                            <div class="d-flex align-items-center gap-1">
                                {!! $legend['icon'] !!}
                                <span>{{ $legend['label'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-end gap-3">
                        @if(!empty($extraViewData['summaryInfo']))
                            @php $summary = $extraViewData['summaryInfo']; @endphp
                            <div class="alert alert-{{ $summary['type'] }} alert-dismissible fade show d-flex align-items-center mt-0 mb-3 flex-grow-1" role="alert">
                                <div class="bg-{{ $summary['type'] }} text-white rounded-circle p-0 me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                    <i class="bx {{ $summary['icon'] }} text-white fs-4"></i>
                                </div>
                                <div class="flex-grow-1 pe-4">
                                    {!! $summary['message'] !!}
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if(isset($extraViewData['escalationConfig']) && $extraViewData['escalationConfig']['show'])
                            @php $esc = $extraViewData['escalationConfig']; @endphp
                            <form id="form-eskalasi-action" action="{{ $esc['route'] }}" method="POST" class="d-inline-block">
                                @csrf
                                @if(isset($esc['parameters']) && is_array($esc['parameters']))
                                    @foreach($esc['parameters'] as $name => $value)
                                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                    @endforeach
                                @endif

                                <button type="button"
                                    class="btn mb-2 {{ str_contains(strtolower($esc['label']), 'publish') ? 'btn-success' : 'btn-info' }} btn-arrow-right"
                                    onclick="submitEskalasiForm('form-eskalasi-action', '{{ $esc['label'] }}')"
                                    {{ ($esc['disabled'] ?? false) ? 'disabled' : '' }}>
                                    {{ $esc['label'] }}
                                </button>
                            </form>
                        @endif
                    </div>

                    @if ($availableFilters)
                    <div class="row" id="table-filter">
                        @foreach ($availableFilters as $filterName => $filter)
                            <div class="{{ $filter['classWrapper'] ?? 'col-md-3' }} mb-3">
                                <div class="form-group mb-0">
                                    {{ Form::{$filter['type']}(...$filter['parameters']) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif
                    <div class="position-relative">
                        <div class="row row-bulk-select g-2">
                            <div class="col-6 col-md-4 col-lg-3 col-xxl-2 mb-3 d-none" id="bulk-select-actions">
                                <div class="d-flex">
                                    <select class="form-select js-select-hide-search" aria-label="Bulk actions">
                                        <option selected="selected">Bulk actions</option>
                                        <option value="Delete">Delete</option>
                                        <option value="Archive">Archive</option>
                                    </select>
                                    <button class="btn btn-muted btn-sm" type="submit">Apply</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto mb-3 d-none" id="bulk-verify-container">
                        <button type="button" class="btn btn-success btn-sm align-self-center" onclick="handleBulkVerifikasiClick()">
                            <span class="bx bx-check-shield"></span> Verifikasi Risiko (<span id="count-checked">0</span>)
                        </button>
                    </div>
                    <table class="table table-bulk-select table-hover ajax-datatable" data-paging="true" data-scroll-y="false"
                        data-filter="true" data-info="true">
                        <thead>
                            <tr>
                                {{-- <th class="white-space-nowrap">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox"
                                            data-bulk-select='{"body":"bulk-select-body","actions":"bulk-select-actions","replacedElement":"bulk-select-replace-element"}' />
                                    </div>
                                </th> --}}
                                @if(!empty($extraViewData['showBulkCheckbox']))
                                <th class="white-space-nowrap">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="check-all-risiko" autocomplete="off" />
                                    </div>
                                </th>
                                @endif
                                <th class="white-space-nowrap">#</th>
                                @foreach ($tableColumns as $key => $column)
                                    <th class="sort" data-sort="{{ $key }}" class="{{ $column['class'] ?? '' }}">
                                        {{ $column['label'] }}
                                    </th>
                                @endforeach
                                @if ($tableActions ?? [])
                                <th class="white-space-nowrap" data-sort="action">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="list" id="bulk-select-body">
                        </tbody>
                    </table>
                </div>
                @if($cardFooter ?? false)
                <div class="card-footer">
                    {!! $cardFooter !!}
                </div>
                @endif
            </div>
        </div>
    </div>
    @if ($editFields)
    @include('master.basic-crud._modal_edit', ['fields' => $editFields, 'action' => route($baseRoute . 'update', array_merge($baseRouteParams ?? [], [':id']))])
    @endif

    @if (!empty($importConfig))
    @include('project-risk._modal_import_tender', ['importConfig' => $importConfig])
    @endif

    @php
        $hasVerifikasiAction = collect($tableActions ?? [])->contains('action', 'verifikasi');
    @endphp

    @if($hasVerifikasiAction)
        @include('project-risk._modal_verifikasi')
    @endif

    @if(request()->route()->getName() === 'projects.risks.index')
        @include('project-risk._modal_catatan')
    @endif

    @if (!empty($extraViewData['showVerifikasiModal']))
        @if (request()->route()->getName() === 'projects.monitorings.index')
            @include('project-monitorings._modal_verifikasi')
        @elseif (request()->route()->getName() === 'risk-register-unit.monitorings.index')
            @include('risk-register-unit.monitorings._modal_verifikasi')
        @elseif (request()->route()->getName() === 'risk-register-ap.monitorings.index')
            @include('risk-register-ap.monitorings._modal_verifikasi')
        @endif
    @endif

    @if (!empty($extraViewData['showCatatanModal']))
        @if (request()->route()->getName() === 'projects.monitorings.index')
            @include('project-monitorings._modal_catatan')
            @include('risk-register-unit.monitorings._modal_peluang')
        @elseif (request()->route()->getName() === 'risk-register-unit.monitorings.index')
            @include('risk-register-unit.monitorings._modal_catatan')
            @include('risk-register-unit.monitorings._modal_peluang')
        @elseif (request()->route()->getName() === 'risk-register-ap.monitorings.index')
            @include('risk-register-ap.monitorings._modal_catatan')
            @include('risk-register-ap.monitorings._modal_peluang')
        @endif
    @endif
@endsection

@push('styles')
<style>
    .hover-underline:hover {
        text-decoration: underline;
    }

    .alert-danger ul {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .alert-danger p {
        margin-bottom: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
const isProjectPage = {{ request()->route()->getName() === 'projects.monitorings.index' ? 'true' : 'false' }};

function showPeluangModal(monitoringId, riskTitle, riskDesc) {
    console.log('showPeluangModal dipanggil dengan ID:', monitoringId);
    $('#peluang-risk-title').text(riskTitle);
    $('#peluang-risk-desc').text(riskDesc);

    if (monitoringId && monitoringId !== 0) {
        $('#identifikasi-risiko-id').val(monitoringId);
        loadOpportunities(monitoringId);
        console.log('ID Risiko yang digunakan:', monitoringId);
    } else {
        console.error('ID risiko tidak valid:', monitoringId);
        alert('ID risiko tidak valid. Silakan coba lagi.');
        return;
    }

    $('#modalPeluang').modal('show');
}

function loadOpportunities(risikoId) {
    const urlType = isProjectPage ? '?type=project' : '';
    $.ajax({
        url: `/opportunities/monitoring/${risikoId}${urlType}`,
        type: 'GET',
        success: function(response) {
            renderOpportunities(response);
        },
        error: function(error) {
            console.error('Error saat memuat peluang:', error);
        }
    });
}

function renderOpportunities(opportunities) {
    const tbody = $('#peluang-list');
    tbody.empty();

    if (opportunities.length === 0) {
        tbody.append(`
            <tr id="peluang-empty-row">
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="bx bx-folder-open fs-3 mb-2 d-block"></i>
                    Belum ada data peluang
                </td>
            </tr>
        `);
        return;
    }

    opportunities.forEach((item, index) => {
        const formattedRencana = formatRupiah(item.nilai_peluang_rencana);
        const formattedRealisasi = formatRupiah(item.nilai_peluang_realisasi);

        let fileHtml = '<span class="text-muted">-</span>';
        if (item.file_path) {
            const fileUrl = `/storage/${item.file_path}`;
            fileHtml = `
                <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-info" title="Download Dokumen">
                    <i class="bx bx-download"></i>
                </a>
            `;
        }

        tbody.append(`
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>${item.penjelasan_peluang_rencana || '-'}</td>
                <td>${item.penjelasan_peluang_realisasi || '-'}</td>
                <td class="text-end">${formattedRencana}</td>
                <td class="text-end">${formattedRealisasi}</td>
                <td class="text-center">${fileHtml}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm bg-warning btn-edit-peluang"
                            data-id="${item.id}"
                            data-rencana="${item.penjelasan_peluang_rencana || ''}"
                            data-realisasi="${item.penjelasan_peluang_realisasi || ''}"
                            data-nilai-rencana="${item.nilai_peluang_rencana || 0}"
                            data-nilai-realisasi="${item.nilai_peluang_realisasi || 0}"
                            data-file-path="${item.file_path || ''}">
                            <i class="bx bx-edit-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger btn-delete-peluang" data-id="${item.id}">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    });
}

function formatRupiah(angka) {
    if (!angka) return 'Rp 0';
    // Menampilkan nilai asli tanpa dibagi 100
    return 'Rp ' + Math.round(parseFloat(angka)).toLocaleString('id-ID');
}

$(document).ready(function() {
    // Inicializar máscara para campos de moneda
    $('.rupiah').inputmask({
        alias: 'numeric',
        groupSeparator: '.',
        radixPoint: ',',
        autoGroup: true,
        digits: 0,
        digitsOptional: true,
        prefix: 'Rp ',
        placeholder: '0',
        rightAlign: false,
        autoUnmask: true,
        removeMaskOnSubmit: true,
        unmaskAsNumber: true
    });

    // Botón para añadir nueva oportunidad
    $(document).on('click', '#btn-add-peluang', function() {
        resetPeluangForm();
        $('#peluang-form-title').text('Tambah Peluang Baru');
        $('#peluang-form-container').removeClass('d-none');
        $('#peluang-form-container')[0].scrollIntoView({ behavior: 'smooth' });
    });

    // Botón para cancelar formulario
    $(document).on('click', '#btn-cancel-peluang', function() {
        $('#peluang-form-container').addClass('d-none');
        resetPeluangForm();
    });

    // Botón para editar oportunidad
    $(document).on('click', '.btn-edit-peluang', function() {
        const id = $(this).data('id');
        const rencana = $(this).data('rencana');
        const realisasi = $(this).data('realisasi');
        const nilaiRencana = $(this).data('nilai-rencana');
        const nilaiRealisasi = $(this).data('nilai-realisasi');
        const filePath = $(this).data('file-path');

        $('#peluang-id').val(id);
        $('#penjelasan_peluang_rencana').val(rencana);
        $('#penjelasan_peluang_realisasi').val(realisasi);

        // Hilangkan desimal (,00) dari nilai sebelum mengisi form
        let nilaiRencanaBulat = Math.round(parseFloat(nilaiRencana));
        let nilaiRealisasiBulat = Math.round(parseFloat(nilaiRealisasi));

        $('#nilai_peluang_rencana').val(nilaiRencanaBulat).trigger('input');
        $('#nilai_peluang_realisasi').val(nilaiRealisasiBulat).trigger('input');

        if (filePath) {
            const fileName = filePath.split('/').pop();
            const fileUrl = `/storage/${filePath}`;
            $('#current-filename').text(fileName);
            $('#current-file-link').attr('href', fileUrl);
            $('#current-file-display').removeClass('d-none');
        } else {
            $('#current-file-display').addClass('d-none');
            $('#current-file-link').attr('href', '#');
        }

        $('#peluang-form-title').html('<i class="bx bx-edit"></i> Edit Peluang');
        $('#peluang-form-container').removeClass('d-none');
        $('#peluang-form-container')[0].scrollIntoView({ behavior: 'smooth' });
    });

    function resetPeluangForm() {
        $('#peluang-id').val('');
        $('#penjelasan_peluang_rencana').val('');
        $('#penjelasan_peluang_realisasi').val('');
        $('#nilai_peluang_rencana').val('0').trigger('input');
        $('#nilai_peluang_realisasi').val('0').trigger('input');
        $('#file_peluang').val('');
        $('#current-file-display').addClass('d-none');
    }

    // Fungsi untuk menghapus peluang
    $(document).on('click', '.btn-delete-peluang', function() {
        const id = $(this).data('id');
        const riskId = $('#identifikasi-risiko-id').val();

        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data peluang yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Tidak, batal",
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary'
            },
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/opportunities/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{csrf_token()}}',
                        _method: 'DELETE',
                        risk_id: riskId
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data peluang berhasil dihapus',
                        });
                        loadOpportunities(riskId);
                    },
                    error: function(xhr) {
                        console.error('Error saat menghapus peluang:', xhr);
                        let errorMsg = 'Terjadi kesalahan saat menghapus data peluang.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: errorMsg,
                        });
                    }
                });
            }
        });
    });


    $(document).on('submit', '#peluang-form', function(e) {
        e.preventDefault();

        const btnSave = $('#btn-save-peluang');
        const originalText = btnSave.html();
        btnSave.prop('disabled', true).html('<span class="bx bx-loader-alt bx-spin" style="width: 0.7rem; height: 0.7rem;"></span> Menyimpan...');

        const peluangId = $('#peluang-id').val();
        const isUpdate = peluangId !== '';
        const risikoId = $('#identifikasi-risiko-id').val();

        if (!risikoId) {
            alert('ID risiko tidak valid.');
            btnSave.prop('disabled', false).html(originalText);
            return;
        }

        const formData = new FormData(this);

        if (isProjectPage) {
            formData.set('project_risk_id', risikoId);
            formData.delete('identifikasi_risiko_id');
        } else {
            formData.set('identifikasi_risiko_id', risikoId);
            formData.delete('project_risk_id');
        }

        // Mapping fields
        formData.set('description', formData.get('penjelasan_peluang_rencana'));
        formData.set('penjelasan', formData.get('penjelasan_peluang_realisasi'));
        formData.set('nilai', formData.get('nilai_peluang_rencana'));

        if (isUpdate) {
            formData.append('_method', 'POST');
            formData.set('_method', 'POST');
            formData.append('_method', 'PUT');
        }

        $.ajax({
            url: isUpdate ? `/opportunities/${peluangId}` : '/opportunities',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response) {
                $('#peluang-form-container').addClass('d-none');
                loadOpportunities(risikoId);
                resetPeluangForm();
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                let msg = 'Terjadi kesalahan saat menyimpan data.';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors)[0][0];
                }
                alert(msg);
            },
            complete: function() {
                btnSave.prop('disabled', false).html(originalText);
            }
        });
    });
});
@php
    $hasChangeToLedAction = collect($tableActions ?? [])->contains('action', 'change_to_led');
@endphp

@if($hasChangeToLedAction)
    const ledCreateRoute = "{{ route('projects.loss-events.create', ['project' => request()->route('project'), 'risk' => ':risk_id']) }}";
@endif

@php
    $hasChangeToLedUnitAction = collect($tableActions ?? [])->contains('action', 'change_to_led_unit');
@endphp

@if($hasChangeToLedUnitAction)
    const ledCreateRoute = "{{ route('risk-register-unit.loss-events.create', ['riskRegister' => ':riskRegister']) }}";
@endif

@php
    $hasChangeToLedApAction = collect($tableActions ?? [])->contains('action', 'change_to_led_ap');
@endphp

@if($hasChangeToLedApAction)
    const ledCreateRoute = "{{ route('risk-register-ap.loss-events.create', ['riskRegister' => ':riskRegister']) }}";
@endif

const fetchedData = [];
$(document).ready(function() {
    function cleanInputJS(value) {
        if (!value) return '';

        let stringValue = value.toString()
            .replace(/&quot;/g, '"')
            .replace(/&#039;/g, "'")
            .replace(/&amp;/g, '&')
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>');
        stringValue = stringValue.replace(/[\r\n]+/g, ' ');

        const cleanRegex = /[^a-zA-Z0-9\s.,\-_()\/%]/g;

        return stringValue.replace(cleanRegex, '');
    }

    $("body").tooltip({ selector: '[data-bs-toggle=tooltip]' });

    const datatableColumns = [
        // {
        //     data: 'id',
        //     orderable: false,
        //     searchable: false,
        //     render: function(data, type, row, meta) {
        //         return '<div class="form-check mb-0"><input class="form-check-input" type="checkbox" value="' + data + '"></div>';
        //     }
        // },
        @if(!empty($extraViewData['showBulkCheckbox']))
        {
            data: 'id',
            orderable: false,
            searchable: false,
            render: function(data, type, row, meta) {
              @if (request()->route()->getName() === 'projects.risks.index' || request()->route()->getName() === 'risk-register-ap.index')
                // Ambil data step dari PHP extraViewData
                const userStep = {{ $extraViewData['u_step'] ?? 0 }};
                const batchStep = {{ $extraViewData['b_step'] ?? 0 }};

                // Validasi: Risiko bisa dicentang jika status 2/3/5/7/8 dan step-nya cocok dengan user + batch
                let canVerify = (row.status == 2 ||
                    row.status == 3 ||
                    row.status == 5 ||
                    row.status == 7 ||
                    row.status == 8) &&
                    row.step_verification == userStep &&
                    userStep == batchStep;

                const isOwnerMR = {{ (!empty($extraViewData['levelId']) && $extraViewData['levelId'] == 2) ? 'true' : 'false' }};
                if (isOwnerMR && row.request_edit == 1) {
                    canVerify = true;
                }

                return `
                    <div class="form-check mb-0">
                        <input class="form-check-input row-checkbox" type="checkbox" value="${data}" ${canVerify ? '' : 'disabled'}>
                    </div>`;
              @elseif (request()->route()->getName() === 'projects.monitorings.index')
                // 1. Cek Dasar: Jika Risiko Closed, Disable
                if (row.is_closed) {
                    return '<div class="form-check mb-0"><input class="form-check-input row-checkbox" type="checkbox" disabled></div>';
                }

                // 2. Ambil Data Monitoring
                const monitoring = row.project_risk_monitoring;

                // Jika belum ada monitoring atau sudah diapprove, Disable
                if (!monitoring || monitoring.is_approved == 1) {
                    return '<div class="form-check mb-0"><input class="form-check-input row-checkbox" type="checkbox" disabled></div>';
                }

                // --- PERBAIKAN UTAMA DISINI ---
                // Kita gunakan monitoring.id sebagai value checkbox, BUKAN data (risk id)
                // Agar controller bisa langsung whereIn('id', $ids) ke tabel monitoring
                const monitoringId = monitoring.id;
                // ------------------------------

                // 3. Ambil Data User & Project dari Controller (extraViewData)
                const userLevel = {{ $extraViewData['currentUserLevel'] ?? 0 }};
                const hasVerificationMr = {{ $extraViewData['hasVerificationMr'] ? 'true' : 'false' }};
                const userCostCenter = "{{ $extraViewData['userUnitCostCenter'] ?? '' }}";
                const isUserUnitMr = {{ $extraViewData['isUserUnitMr'] ?? 0 }};

                const status = parseInt(monitoring.status);
                const projectCostCenter = row.project ? row.project.cost_center_parent : '';

                let canVerify = false;

                // --- LOGIKA VERIFIKASI (SAMA DENGAN CONTROLLER) ---

                // A. RO Project (Level 7) - Verifikasi Status 2
                if (userLevel == 7 && status == 2) {
                    canVerify = true;
                }

                // B. RO Divisi (Level 1) - Verifikasi Status 3
                // Syarat: Cost Center User == Cost Center Project
                else if (userLevel == 1 && status == 3 && userCostCenter == projectCostCenter) {
                    canVerify = true;
                }

                // C. RO Divisi MR (Level 1) - Verifikasi Status 4
                // Syarat: User adalah Unit MR & Punya Permission MR
                else if (userLevel == 1 && status == 4 && isUserUnitMr == 1 && hasVerificationMr) {
                    canVerify = true;
                }

                // D. ROW Divisi MR (Level 2) - Verifikasi Status 5
                // Syarat: Punya Permission MR
                else if (userLevel == 2 && status == 5 && hasVerificationMr) {
                    canVerify = true;
                }

                // Render Checkbox dengan Value Monitoring ID
                return `
                    <div class="form-check mb-0">
                        <input class="form-check-input row-checkbox" type="checkbox" value="${monitoringId}" ${canVerify ? '' : 'disabled'}>
                    </div>`;
              @elseif (request()->route()->getName() === 'risk-register-unit.monitorings.index')
                // --- LOGIKA CHECKBOX MONITORING UNIT ---
                if (row.is_closed) {
                    return '<div class="form-check mb-0"><input class="form-check-input row-checkbox" type="checkbox" disabled></div>';
                }

                const monitoring = row.last_monitoring_risiko;

                if (!monitoring || monitoring.is_approved == 1) {
                    return '<div class="form-check mb-0"><input class="form-check-input row-checkbox" type="checkbox" disabled></div>';
                }

                const monitoringId = monitoring.id;
                
                const userLevel = {{ $extraViewData['currentUserLevel'] ?? 0 }};
                const hasVerificationMr = {{ (!empty($extraViewData['hasVerificationMr']) && $extraViewData['hasVerificationMr']) ? 'true' : 'false' }};
                console.log(hasVerificationMr);
                const isUserUnitMr = {{ $extraViewData['isUserUnitMr'] ?? 0 }};

                const status = parseInt(monitoring.status);
                let canVerify = false;

                // Verifikasi Monitoring Unit
                // A. RO Divisi (Level 2) Verifikasi Status 2
                if (userLevel == 2 && status == 2) {
                    canVerify = true;
                }
                // B. RO MR (Level 1) Verifikasi Status 3
                else if (userLevel == 1 && status == 3 && isUserUnitMr == 1 && hasVerificationMr) {
                    canVerify = true;
                }
                // C. ROW MR (Level 2) Verifikasi Status 4
                else if (userLevel == 2 && status == 4 && isUserUnitMr == 1 && hasVerificationMr) {
                    canVerify = true;
                }

                return `
                    <div class="form-check mb-0">
                        <input class="form-check-input row-checkbox" type="checkbox" value="${monitoringId}" ${canVerify ? '' : 'disabled'}>
                    </div>`;
              @elseif (request()->route()->getName() === 'risk-register-ap.monitorings.index')
                // --- LOGIKA CHECKBOX MONITORING AP ---
                if (row.is_closed) {
                    return '<div class="form-check mb-0"><input class="form-check-input row-checkbox" type="checkbox" disabled></div>';
                }

                const monitoring = row.last_monitoring_risiko;

                if (!monitoring || monitoring.is_approved == 1) {
                    return '<div class="form-check mb-0"><input class="form-check-input row-checkbox" type="checkbox" disabled></div>';
                }

                const monitoringId = monitoring.id;
                
                const userLevel = {{ $extraViewData['currentUserLevel'] ?? 0 }};
                const hasVerificationMr = {{ (!empty($extraViewData['hasVerificationMr']) && $extraViewData['hasVerificationMr']) ? 'true' : 'false' }};
                const isUserUnitMr = {{ $extraViewData['isUserUnitMr'] ?? 0 }};

                const status = parseInt(monitoring.status);
                let canVerify = false;

                // Verifikasi Monitoring Unit
                // A. RO Divisi (Level 2) Verifikasi Status 2
                if (userLevel == 2 && status == 2) {
                    canVerify = true;
                }
                // B. RO MR (Level 1) Verifikasi Status 3
                else if (userLevel == 1 && status == 3 && isUserUnitMr == 1 && hasVerificationMr) {
                    canVerify = true;
                }
                // C. ROW MR (Level 2) Verifikasi Status 4
                else if (userLevel == 2 && status == 4 && isUserUnitMr == 1 && hasVerificationMr) {
                    canVerify = true;
                }

                return `
                    <div class="form-check mb-0">
                        <input class="form-check-input row-checkbox" type="checkbox" value="${monitoringId}" ${canVerify ? '' : 'disabled'}>
                    </div>`;
              @else
                  return '<div class="form-check mb-0"><input class="form-check-input row-checkbox" type="checkbox" value="' + data + '"></div>';
              @endif
            }
        },
        @endif
        {
            data: 'id',
            orderable: false,
            searchable: false,
            render: function(data, type, row, meta) {
                return meta.settings._iDisplayStart + meta.row + 1;
            }
        },
    ];

    let tempColumn = null;
    @foreach ($tableColumns as $key => $column)
        tempColumn = @json($column);
        @if ($column['render'] ?? false)
            tempColumn.render = {!! $column['render'] !!};
        @endif

        @if ($column['createdCell'] ?? false)
            tempColumn.createdCell = {!! $column['createdCell'] !!};
        @endif
        datatableColumns.push(tempColumn);
    @endforeach

    @if ($tableActions ?? [])
    datatableColumns.push({
        data: 'id',
        name: 'action',
        orderable: false,
        searchable: false,
        render: function(data, type, row, meta) {
            const buttons = [];
            let activeState;
            let activeStateCallback;
            @foreach ($tableActions as $action)
                @if ($action['active_state'] ?? false)
                activeStateCallback = {!! $action['active_state'] !!};
                activeState = activeStateCallback(data, type, row);
                @else
                activeState = true;
                @endif
                if (activeState) {
                    let buttonHtml = `@include('master.basic-crud._table_action', ['action' => $action, 'id' => ':id', 'code' => ':code'])`;

                    const title = cleanInputJS(row?.peristiwa_risiko_id == 0 ? row?.rencana_kegiatan : (row?.peristiwa_risiko?.title ? row?.peristiwa_risiko?.title : (row?.peristiwa_risiko ?? '-')));
                    const desc = cleanInputJS(row?.deskripsi_peristiwa_risiko);

                    const quarter = $('#table-filter select[name="quarter"]').val();
                    const tahun = $('#table-filter select[name="tahun"]').val();
                    const month = $('#table-filter select[name="month"]').val();
                    const monitoringId = row.last_monitoring_risiko?.id || row.project_risk_monitoring?.id || 0;

                    buttonHtml = buttonHtml.replaceAll('__RISK_ID__', row.id)
                                        .replaceAll('__MONITORING_ID__', monitoringId)
                                        .replaceAll('__RISK_TITLE__', title)
                                        .replaceAll('__RISK_DESC__', desc)
                                        .replaceAll(':quarter', quarter)
                                        .replaceAll(':tahun', tahun)
                                        .replaceAll(':month', month);

                    buttons.push(buttonHtml);
                }
            @endforeach

            let notificationDot = '';
            // if (row?.action_needed) {
            //     notificationDot = `
            //     <div class="position-absolute top-0 start-100 translate-middle">
            //         <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle animate-ping"></span>
            //         <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            //     </div>`;
            // }

            let html = '<div style="white-space:nowrap" class="d-flex align-items-center position-relative">' + buttons.join('') + notificationDot + '</div>';

            return html
              .replaceAll(':project_id', (row?.project_id || (row?.project && row?.project?.id) || ''))
              .replaceAll(':id', data)
              .replaceAll(':code', row.code || '');
        }
    });
    @endif
    // DataTable
    $('.ajax-datatable').DataTable({scrollY: 500,
        filter: false,
        info: true,
        select: true,
        paging: true,
        lengthChange: true,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        pageLength: 25,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
            decimal: ",",
            thousands: ".",
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route($baseRoute . 'index', $baseRouteParams ?? []) }}',
            type: 'GET',
            data: function (d) {
                d.filters = {};
                @foreach ($availableFilters as $filterName => $filter)
                    d.filters['{{ $filterName }}'] = $('#table-filter [name="{{ $filterName }}"]').val();
                @endforeach
            },
            dataSrc: function (json) {
                json.data.forEach(function (data) {
                    fetchedData[data.id] = data;
                });
                return json.data;
            }
        },
        autoWidth: false,
        columns: datatableColumns,
        // order: @json($defaultOrder ?? []),
        order: [],
        responsive: true,
        columnDefs: [
            {"width": "1%", "targets": 0},
            {"width": "1%", "targets": 1},
            {"width": "1%", "targets": -1},
            {"orderable": false, "targets": [0,1]} // Can't order
        ],
    });

    // Filter with timeout
    let filterTimeout;
    $('#table-filter :input').on('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            $('.ajax-datatable').DataTable().ajax.reload();
        }, 300);
    });

    // Action Button
    $(document).on('click', '.btn-action', function() {
        const action = $(this).data('action');
        const id = $(this).data('id');
        switch (action) {
            case 'edit_data': {
                $('#modalEdit').modal('show');
                const formEdit = $('#formEdit');
                formEdit.data('id', id);
                @foreach ($editFields as $field)
                if (formEdit.find('[name="{{ $field['name'] }}"]').is('.flatpickr-range')) {
                    (formEdit.find('[name="{{ $field['name'] }}"]')[0])._flatpickr.setDate(fetchedData[id].{{ $field['name'] }}.split(' - '), false, 'd/m/Y');
                } else {
                    formEdit.find('[name="{{ $field['name'] }}"]').val(fetchedData[id].{{ $field['name'] }});
                }
                @endforeach
                break;
            }
            @if(\Route::has($baseRoute . 'destroy'))
            case 'delete_data': {
                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus!",
                    cancelButtonText: "Tidak, batal",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-danger bg-danger border-0',
                        cancelButton: 'btn btn-muted border-0'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route($baseRoute . 'destroy', array_merge($baseRouteParams ?? [], [':id'])) }}'.replace(':id', id),
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message || 'Data berhasil dihapus',
                                });
                                $('.ajax-datatable').DataTable().ajax.reload();
                            },
                            error: function (xhr, status, error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON.message || 'Terjadi kesalahan saat menghapus data',
                                });
                            }
                        });
                    }
                });
                break;
            }
            @endif
            @if(\Route::has($baseRoute . 'store'))
            case 'sync_data': {
                Swal.fire({
                    title: "Apakah Anda yakin?",
                    text: "Proses sinkronsi akan menambahkan data baru, memperbarui data yang sudah ada, dan menghapus data yang tidak ada!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, sinkronisasi!",
                    cancelButtonText: "Tidak, batal",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-primary border-0',
                        cancelButton: 'btn btn-muted border-0'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Sedang memproses...',
                            text: 'Mohon tunggu beberapa saat.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        $.ajax({
                            url: '{{ route($baseRoute . 'store', $baseRouteParams ?? []) }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: response.message || 'Data berhasil disinkronisasi',
                                });
                                $('.ajax-datatable').DataTable().ajax.reload();
                            },
                            error: function (xhr, status, error) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON.message || 'Terjadi kesalahan saat menyinkronisasi data',
                                });
                            }
                        });
                    }
                });
                break;
            }
            @endif
            case 'change_to_led': {
                let label = 'Apakah Risiko ini terjadi dan menjadi Loss Event?';
                const rowData = fetchedData[id];
                if (rowData && rowData?.peristiwa_risiko?.title) {
                  label = `Apakah Risiko ${rowData?.peristiwa_risiko?.title} - ${rowData?.deskripsi_peristiwa_risiko} ini terjadi dan menjadi Loss Event?`;
                }
                Swal.fire({
                    title: 'Konfirmasi Perubahan',
                    html: label,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "Ya, Ubah ke Loss Event",
                    cancelButtonText: "Tidak, Batal",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success me-2',
                        cancelButton: 'btn btn-danger'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        const finalUrl = ledCreateRoute.replace(':risk_id', id);
                        window.location.href = finalUrl;
                    }
                });
                break;
            }
            case 'change_to_led_unit': {
                let label = 'Apakah Risiko ini terjadi dan menjadi Loss Event?';
                const rowData = fetchedData[id];
                if (rowData && rowData?.peristiwa_risiko) {
                  label = `Apakah Risiko ${rowData?.peristiwa_risiko} - ${rowData?.deskripsi_peristiwa_risiko} ini terjadi dan menjadi Loss Event?`;
                }
                Swal.fire({
                    title: 'Konfirmasi Perubahan',
                    html: label,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "Ya, Ubah ke Loss Event",
                    cancelButtonText: "Tidak, Batal",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success me-2',
                        cancelButton: 'btn btn-danger'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        const finalUrl = ledCreateRoute.replace(':riskRegister', id);
                        window.location.href = finalUrl;
                    }
                });
                break;
            }
            case 'change_to_led_ap': {
                let label = 'Apakah Risiko ini terjadi dan menjadi Loss Event?';
                const rowData = fetchedData[id];
                if (rowData && rowData?.peristiwa_risiko) {
                  label = `Apakah Risiko ${rowData?.peristiwa_risiko} - ${rowData?.deskripsi_peristiwa_risiko} ini terjadi dan menjadi Loss Event?`;
                }
                Swal.fire({
                    title: 'Konfirmasi Perubahan',
                    html: label,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "Ya, Ubah ke Loss Event",
                    cancelButtonText: "Tidak, Batal",
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success me-2',
                        cancelButton: 'btn btn-danger'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        const finalUrl = ledCreateRoute.replace(':riskRegister', id);
                        window.location.href = finalUrl;
                    }
                });
                break;
            }
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

    const inputmaskGeneral = $('.inputmask-general');

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

    flatpickr('.flatpickr-range', {
        mode: 'range',
        dateFormat: 'd/m/Y',
        altInput: true,
        altFormat: 'd/m/Y',
        locale: {
            rangeSeparator: ' - '
        }
    });

    @if ($availableFilters)
    $('#modalCreate').on('show.bs.modal', function() {
        let filter = null;
        @foreach ($availableFilters as $filterName => $filter)
        filter = $('#table-filter').find(':input[name="{{ $filterName }}"]').val();
        if (filter) {
            $(this).find(':input[name="{{ $filterName }}"]').val(filter).trigger('change');
        }
        @endforeach
    });
    @endif

    $('#import-form').on('submit', function() {
        $('#submit-import-btn').prop('disabled', true);
        $('#import-loading').removeClass('d-none');
        $('#import-text').text('Loading...');
    });

    $('#download-template-btn').on('click', function() {
        const button = $(this);
        const url = button.data('url');

        showDownloadLoading();

        $.ajax({
            url: url,
            type: 'GET',
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data, status, xhr) {
                hideDownloadLoading();

                const disposition = xhr.getResponseHeader('Content-Disposition');
                let filename = 'template.xlsx';
                if (disposition && disposition.indexOf('attachment') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }

                const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = downloadUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(downloadUrl);
                document.body.removeChild(a);
            },
            error: function(xhr, status, error) {
                hideDownloadLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengunduh',
                    text: 'Terjadi kesalahan saat menyiapkan file template. Silakan coba lagi.',
                });
            }
        });
    });

    function showDownloadLoading() {
        $('#download-template-btn').prop('disabled', true);
        $('#download-spinner').removeClass('d-none');
        $('#download-icon').addClass('d-none');
        $('#download-text').text('Menyiapkan...');
    }

    function hideDownloadLoading() {
        $('#download-template-btn').prop('disabled', false);
        $('#download-spinner').addClass('d-none');
        $('#download-icon').removeClass('d-none');
        $('#download-text').text('Download Template');
    }
});
</script>
@if ($extraScripts)
@foreach ($extraScripts as $script)
{!! $script !!}
@endforeach
@endif
@endpush
