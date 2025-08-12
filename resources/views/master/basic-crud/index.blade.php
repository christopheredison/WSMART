@extends('layouts.default')
@section('dashboard')
    @include('partials.success-message')
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
                        <h2 class="h3">Data {!! $indexTitle ?? $resourceName !!}</h2>
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
                                <div id="bulk-select-replace-element" class="col-auto ms-auto">
                                    <a class="btn btn-outline-info btn-sm" href="{{ route($baseRoute . 'create', $baseRouteParams ?? []) }}">
                                        <span class="bx bx-plus"></span>
                                        <span class="ms-1">Tambah {{ $resourceName }}</span>
                                    </a>
                                </div>
                            @endif
                        @endif
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
                    @if ($availableFilters)
                    <div class="row" id="table-filter">
                        @foreach ($availableFilters as $filterName => $filter)
                            <div class="col-md-3 mb-3">
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
                    <table class="table table-bulk-select table-hover ajax-datatable" data-paging="true" data-scroll-y="false"
                        data-filter="true" data-info="true">
                        <thead>
                            <tr>
                                <th class="white-space-nowrap">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox"
                                            data-bulk-select='{"body":"bulk-select-body","actions":"bulk-select-actions","replacedElement":"bulk-select-replace-element"}' />
                                    </div>
                                </th>
                                <th class="white-space-nowrap">#</th>
                                @foreach ($tableColumns as $key => $column)
                                    <th class="sort" data-sort="{{ $key }}">
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
const fetchedData = [];
$(document).ready(function() {
    const datatableColumns = [
        {
            data: 'id',
            orderable: false,
            searchable: false,
            render: function(data, type, row, meta) {
                return '<div class="form-check mb-0"><input class="form-check-input" type="checkbox" value="' + data + '"></div>';
            }
        },
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
                    buttons.push(`@include('master.basic-crud._table_action', ['action' => $action, 'id' => ':id', 'code' => ':code'])`);
                }
            @endforeach

            let html = '<div style="white-space:nowrap" class="d-flex align-items-center">' + buttons.join('') + '</div>';

            return html.replaceAll(':id', data).replaceAll(':code', row.code || '');
        }
    });
    @endif
    // DataTable
    $('.ajax-datatable').DataTable({scrollY: 500,
        filter: false,
        info: true,
        select: true,
        paging: false,
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
        order: @json($defaultOrder ?? [[1, 'asc']]),
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
});
</script>
@if ($extraScripts)
@foreach ($extraScripts as $script)
{!! $script !!}
@endforeach
@endif
@endpush