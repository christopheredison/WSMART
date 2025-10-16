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
                                @if(empty($extraViewData['status']) || $extraViewData['status'] == \App\Models\DataBatch::STATUS_PROSES)
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
                                {{--
                                <th class="white-space-nowrap">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox"
                                            data-bulk-select='{"body":"bulk-select-body","actions":"bulk-select-actions","replacedElement":"bulk-select-replace-element"}' />
                                    </div>
                                </th>
                                --}}
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

    @if (!empty($importConfig))
    @include('project-risk._modal_import_tender', ['importConfig' => $importConfig])
    @endif

    @php
        $hasVerifikasiAction = collect($tableActions ?? [])->contains('action', 'verifikasi');
    @endphp

    @if($hasVerifikasiAction)
        @include('project-risk._modal_verifikasi')
    @endif

    @if (!empty($extraViewData['showVerifikasiModal']))
        @if (request()->route()->getName() === 'projects.monitorings.index')
            @include('project-monitorings._modal_verifikasi')
        @elseif (request()->route()->getName() === 'risk-register-unit.monitorings.index')
            @include('risk-register-unit.monitorings._modal_verifikasi')
        @endif
     @endif

    @if (!empty($extraViewData['showCatatanModal']))
        @if (request()->route()->getName() === 'projects.monitorings.index')
            @include('project-monitorings._modal_catatan')
        @elseif (request()->route()->getName() === 'risk-register-unit.monitorings.index')
            @include('risk-register-unit.monitorings._modal_catatan')
            @include('risk-register-unit.monitorings._modal_peluang')
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

function showPeluangModal(monitoringId, riskTitle, riskDesc) {
    console.log('showPeluangModal dipanggil dengan ID:', monitoringId);
    $('#peluang-risk-title').text(riskTitle);
    $('#peluang-risk-desc').text(riskDesc);
    
    // Pastikan monitoringId tidak 0 atau undefined
    if (monitoringId && monitoringId !== 0) {
        $('#identifikasi-risiko-id').val(monitoringId);
        loadOpportunities(monitoringId);
        console.log('ID Risiko yang digunakan:', monitoringId);
    } else {
        // Coba ambil dari data-id tombol yang memanggil fungsi ini
        console.error('ID risiko tidak valid:', monitoringId);
        alert('ID risiko tidak valid. Silakan coba lagi.');
        return;
    }
    
    $('#modalPeluang').modal('show');
}

function loadOpportunities(risikoId) {
    $.ajax({
        url: `/opportunities/monitoring/${risikoId}`,
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
                <td colspan="6" class="text-center">Belum ada data peluang</td>
            </tr>
        `);
        return;
    }
    
    opportunities.forEach((item, index) => {
        const formattedRencana = formatRupiah(item.nilai_peluang_rencana);
        const formattedRealisasi = formatRupiah(item.nilai_peluang_realisasi);
        
        tbody.append(`
            <tr>
                <td>${index + 1}</td>
                <td>${item.penjelasan_peluang_rencana || '-'}</td>
                <td>${item.penjelasan_peluang_realisasi || '-'}</td>
                <td>${formattedRencana}</td>
                <td>${formattedRealisasi}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-info btn-edit-peluang" 
                            data-id="${item.id}" 
                            data-rencana="${item.penjelasan_peluang_rencana || ''}" 
                            data-realisasi="${item.penjelasan_peluang_realisasi || ''}" 
                            data-nilai-rencana="${item.nilai_peluang_rencana || 0}" 
                            data-nilai-realisasi="${item.nilai_peluang_realisasi || 0}">
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
    });
    
    // Botón para cancelar formulario
    $(document).on('click', '#btn-cancel-peluang', function() {
        $('#peluang-form-container').addClass('d-none');
    });
    
    // Botón para editar oportunidad
    $(document).on('click', '.btn-edit-peluang', function() {
        const id = $(this).data('id');
        const rencana = $(this).data('rencana');
        const realisasi = $(this).data('realisasi');
        const nilaiRencana = $(this).data('nilai-rencana');
        const nilaiRealisasi = $(this).data('nilai-realisasi');
        
        $('#peluang-id').val(id);
        $('#penjelasan_peluang_rencana').val(rencana);
        $('#penjelasan_peluang_realisasi').val(realisasi);
        
        // Hilangkan desimal (,00) dari nilai sebelum mengisi form
        let nilaiRencanaBulat = Math.round(parseFloat(nilaiRencana));
        let nilaiRealisasiBulat = Math.round(parseFloat(nilaiRealisasi));
        
        $('#nilai_peluang_rencana').val(nilaiRencanaBulat).trigger('input');
        $('#nilai_peluang_realisasi').val(nilaiRealisasiBulat).trigger('input');
        
        $('#peluang-form-title').text('Edit Peluang');
        $('#peluang-form-container').removeClass('d-none');
    });
    
    // Fungsi untuk menghapus peluang
    $(document).on('click', '.btn-delete-peluang', function() {
        if (confirm('Apakah Anda yakin ingin menghapus data peluang ini?')) {
            const id = $(this).data('id');
            const riskId = $('#identifikasi-risiko-id').val();
            
            $.ajax({
                url: `/opportunities/${id}`,
                type: 'POST',
                data: {
                    _token: '{{csrf_token()}}',
                    _method: 'DELETE',
                    risk_id: riskId
                },
                success: function(response) {
                    loadOpportunities(riskId);
                },
                error: function(error) {
                    console.error('Error saat menghapus peluang:', error);
                    alert('Terjadi kesalahan saat menghapus data peluang');
                }
            });
        }
    });
    
    
    $(document).on('submit', '#peluang-form', function(e) {
        e.preventDefault();
        
        const peluangId = $('#peluang-id').val();
        const isUpdate = peluangId !== '';
        
        // Ambil identifikasi_risiko_id dari hidden field yang sudah diisi di showPeluangModal
        const risikoId = $('#identifikasi-risiko-id').val();
        
        // Pastikan risikoId ada dan valid
        if (!risikoId) {
            console.error('ID risiko tidak valid:', risikoId);
            alert('ID risiko tidak valid. Silakan coba lagi.');
            return;
        }
        
        // Menggunakan FormData untuk mengambil semua data form termasuk CSRF token
        const formData = new FormData(this);
        
        // Gunakan risikoId dari hidden field
        formData.set('identifikasi_risiko_id', risikoId);
        
        // Menyesuaikan nama field dengan yang diharapkan controller
        formData.set('description', formData.get('penjelasan_peluang_rencana'));
        formData.set('penjelasan', formData.get('penjelasan_peluang_realisasi'));
        formData.set('nilai', formData.get('nilai_peluang_rencana'));
        
        // Jika update, tambahkan method PUT karena FormData tidak mendukung PUT secara langsung
        if (isUpdate) {
            formData.append('_method', 'PUT');
        }
        
        $.ajax({
            url: isUpdate ? `/opportunities/${peluangId}` : '/opportunities',
            type: 'POST', // Selalu gunakan POST, untuk PUT kita sudah menambahkan _method di atas
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response) {
                $('#peluang-form-container').addClass('d-none');
                loadOpportunities($('#identifikasi-risiko-id').val());
            },
            error: function(error) {
                console.error('Error saat menyimpan peluang:', error);
                alert('Terjadi kesalahan saat menyimpan data peluang');
            }
        });
    });
    
    function resetPeluangForm() {
        $('#peluang-id').val('');
        $('#penjelasan_peluang_rencana').val('');
        $('#penjelasan_peluang_realisasi').val('');
        $('#nilai_peluang_rencana').val('0').trigger('input');
        $('#nilai_peluang_realisasi').val('0').trigger('input');
    }
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
                    let buttonHtml = `@include('master.basic-crud._table_action', ['action' => $action, 'id' => ':id', 'code' => ':code'])`;

                    const title = (row.peristiwa_risiko?.title || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");
                    const desc = (row.deskripsi_peristiwa_risiko || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");
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