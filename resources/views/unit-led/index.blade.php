@extends('layouts.default')
@section('dashboard')
    @include('partials.success-message')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-info-subtle p-2 rounded-4">
                          <div class="lead__icon">
                            <div class="svg-icon svg-icon-2x svg-icon-info">
                              @include('partials.icon-layer')
                            </div>
                          </div>
                        </div>
                        <div class="d-block">
                          <h2 class="">Data Loss Event Unit</h2>
                          @if(isset($periode))
                          <div class="ff-preheading">Periode: {{ $periode->tahun }}</div>
                          @endif
                        </div>
                        
                        <div class="col-auto ms-auto">
                            @php
                                $createRouteParams = ['periode' => $periode->id ?? null];
                                if ($viewAllDivision && isset($targetUnitId)) {
                                    $createRouteParams['unit_id'] = $targetUnitId;
                                }
                            @endphp
                            <a class="btn btn-outline-info btn-sm" href="{{ route('unit-led.create', $createRouteParams) }}">
                                <span class="bx bx-plus"></span>
                                <span class="ms-1">Tambah Data Loss Event</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row" id="table-filter">
                        @if ($periode)
                            <input type="hidden" name="periode_id" id="filter-periode" value="{{ $periode->id }}">
                        @else
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Periode</label>
                            <select class="form-select" id="filter-periode">
                                <option value="">Semua</option>
                                @foreach($periodes as $periode)
                                    <option value="{{ $periode->id }}">{{ $periode->tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Divisi</label>
                            @if($viewAllDivision)
                                <select class="form-select select2" id="filter-unit">
                                    @foreach($units as $id => $name)
                                        <option value="{{ $id }}" {{ $targetUnitId == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <select class="form-select" id="filter-unit" disabled>
                                    <option value="{{ auth()->user()->unit_id }}">{{ auth()->user()->unit->name }}</option>
                                </select>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Kategori Kejadian</label>
                            <select class="form-select select2" id="filter-kategori">
                                <option value="">Semua</option>
                                @foreach($kategoriKejadians as $kategori)
                                    <option value="{{ $kategori->id }}">{{ $kategori->kategori_kejadian }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button class="btn btn-primary" id="btn-filter">Filter</button>
                        </div>
                    </div>
                    <table class="table table-hover ajax-datatable" data-paging="true" data-scroll-y="false"
                        data-filter="true" data-info="true">
                        <thead>
                            <tr>
                                <th class="white-space-nowrap">#</th>
                                <th class="sort" data-sort="tahun">Tahun Kejadian</th>
                                <th class="sort" data-sort="nama_kejadian">Nama Kejadian</th>
                                <th class="sort" data-sort="identifikasi_kejadian">Identifikasi Kejadian</th>
                                <th class="sort" data-sort="kategori_kejadian">Kategori Kejadian</th>
                                <th class="sort" data-sort="nilai_kerugian">Nilai Kerugian</th>
                                {{-- <th class="sort" data-sort="unit_penanggung_jawab">Pihak Terkait</th> --}}
                                <th class="white-space-nowrap" data-sort="action">Action</th>
                            </tr>
                        </thead>
                        <tbody class="list">
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
            url: '{{ route("unit-led.index") }}',
            type: 'GET',
            data: function(d) {
                d.periode_id = $('#filter-periode').val();
                d.kategori_id = $('#filter-kategori').val();
                d.unit_id = $('#filter-unit').val();
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'tahun', name: 'tahun'},
            {data: 'nama_kejadian', name: 'nama_kejadian'},
            {data: 'identifikasi_kejadian', name: 'identifikasi_kejadian'},
            {data: 'kategori_kejadian', name: 'kategori_kejadian'},
            {data: 'nilai_kerugian', name: 'nilai_kerugian'},
            // {data: 'unit_penanggung_jawab', name: 'unit_penanggung_jawab'},
            {
                data: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return data;
                }
            }
        ],
        order: [[1, 'desc']],
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
                    url: `/unit-led/${id}`,
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