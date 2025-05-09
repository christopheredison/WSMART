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
                        <h2 class="h3">Data Loss Event Project</h2>
                        <div id="bulk-select-replace-element" class="col-auto ms-auto">
                            <a class="btn btn-outline-info btn-sm" href="{{ route('project-led.create') }}">
                                <span class="bx bx-plus"></span>
                                <span class="ms-1">Tambah Data Loss Event</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="table-filter">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tahun</label>
                            <select class="form-select" id="filter-tahun">
                                <option value="">Semua</option>
                                @php
                                    $currentYear = date('Y');
                                    for($i = 0; $i <= 10; $i++) {
                                        $year = $currentYear - $i;
                                        echo "<option value='$year'" . ($i === 0 ? " selected" : "") . ">$year</option>";
                                    }
                                @endphp
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Konstruksi Spesifik</label>
                            <select class="form-select" id="filter-konstruksi">
                                <option value="">Semua</option>
                                @foreach($projectSektors as $sektor)
                                    <option value="{{ $sektor->id }}">{{ $sektor->sektor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Peristiwa Risiko</label>
                            <select class="form-select" id="filter-peristiwa">
                                <option value="">Semua</option>
                                @foreach($peristiwaRisikos as $risiko)
                                    <option value="{{ $risiko->id }}">{{ $risiko->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button class="btn btn-primary" id="btn-filter">Filter</button>
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
                                <th class="sort" data-sort="tahun">Tahun Kejadian</th>
                                <th class="sort" data-sort="konstruksi_spesifik">Konstruksi Spesifik</th>
                                <th class="sort" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                                <th class="sort" data-sort="project">Project</th>
                                <th class="sort" data-sort="deskripsi_kejadian">Deskripsi</th>
                                <th class="sort" data-sort="unit_penanggung_jawab">PIC</th>
                                <th class="white-space-nowrap" data-sort="action">Action</th>
                            </tr>
                        </thead>
                        <tbody class="list" id="bulk-select-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    let table = $('.ajax-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('project-led.index') }}',
            type: 'GET',
            data: function(d) {
                d.tahun = $('#filter-tahun').val();
                d.konstruksi_id = $('#filter-konstruksi').val();
                d.peristiwa_id = $('#filter-peristiwa').val();
            }
        },
        columns: [
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return '<div class="form-check mb-0"><input class="form-check-input" type="checkbox" value="' + data + '"></div>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'tahun', name: 'tahun'},
            {data: 'konstruksi_spesifik', name: 'konstruksi_spesifik'},
            {data: 'peristiwa_risiko', name: 'peristiwa_risiko'},
            {data: 'project', name: 'project'},
            {data: 'deskripsi_kejadian', name: 'deskripsi_kejadian'},
            {data: 'unit_penanggung_jawab', name: 'unit_penanggung_jawab'},
            {
                data: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return data;
                }
            }
            // {
            //     data: null,
            //     orderable: false,
            //     searchable: false,
            //     render: function(data) {
            //         return `
            //             <div class="d-flex gap-2">
            //                 <a href="/project-led/${data.id}/edit" class="btn btn-sm btn-info">
            //                     <i class="bx bx-edit"></i>
            //                 </a>
            //                 <button class="btn btn-sm btn-danger" onclick="deleteData(${data.id})">
            //                     <i class="bx bx-trash"></i>
            //                 </button>
            //             </div>
            //         `;
            //     }
            // }
        ],
        order: [[2, 'desc']],
        language: {
            processing: "Memproses...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ data keseluruhan)",
            infoPostFix: "",
            loadingRecords: "Memuat...",
            zeroRecords: "Tidak ditemukan data yang sesuai",
            emptyTable: "Tidak ada data yang tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        }
    });

    // Filter button click
    $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });

    // Delete function
    window.deleteData = function(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/project-led/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(result) {
                        if (result.success) {
                            Swal.fire(
                                'Terhapus!',
                                result.message,
                                'success'
                            );
                            $('.ajax-datatable').DataTable().ajax.reload();
                        } else {
                            Swal.fire(
                                'Gagal!',
                                result.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            'Terjadi kesalahan saat menghapus data',
                            'error'
                        );
                    }
                });
            }
        });
    }
});
</script>
@endpush