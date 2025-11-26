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
                          <h2 class="h3">Data Loss Event Korporat</h2>
                          @if(isset($periode))
                          <div class="ff-preheading">Periode: {{ $periode->tahun }}</div>
                          @endif
                        </div>
                        
                        <div class="col-auto ms-auto">
                            <a class="btn btn-outline-info btn-sm" href="{{ route('corporate-led.create', ['periode' => $periode->id ?? null]) }}">
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
                            <select class="form-select select2" id="filter-periode">
                                <option value="">Semua</option>
                                @foreach($periodes as $periode)
                                    <option value="{{ $periode->id }}">{{ $periode->tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
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
    <div class="modal fade" id="modalUploadDoc" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kelola Dokumen Pendukung</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <form id="form-upload-doc" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="loss_event_id" id="upload_loss_event_id">
                                <div class="input-group">
                                    <input type="file" class="form-control" name="file_dokumen" id="file_dokumen" required>
                                    <button class="btn btn-primary" type="submit" id="btn-simpan-file">
                                        <span class="bx bx-upload"></span> Upload
                                    </button>
                                </div>
                                <small class="text-muted">Format: PDF, Doc, Excel, Gambar (Max 10MB)</small>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" style="table-layout: fixed; width: 100%">
                            <thead>
                                <tr>
                                    <th style="width: 5%" class="text-center">#</th>
                                    <th style="width: 55%">Nama File</th>
                                    <th style="width: 25%">Tanggal Upload</th>
                                    <th style="width: 15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="list-files-body">
                            </tbody>
                        </table>
                    </div>
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
            url: '{{ route("corporate-led.index", ["periode" => $periode->id]) }}',
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
                    url: `/corporate-led/${id}`,
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

    // 1. Event saat tombol Upload Dokumen di klik (Buka Modal)
    $(document).on('click', '.btn-upload-doc', function() {
        let id = $(this).data('id');
        $('#upload_loss_event_id').val(id);
        $('#file_dokumen').val('');
        loadFiles(id);
        $('#modalUploadDoc').modal('show');
    });

    // 2. Fungsi Load List File
    function loadFiles(id) {
        $('#list-files-body').html('<tr><td colspan="4" class="text-center">Memuat data...</td></tr>');
        
        $.ajax({
            url: '/corporate-led/files/' + id,
            type: 'GET',
            success: function(response) {
                let html = '';
                if(response.data.length > 0) {
                    $.each(response.data, function(i, file) {
                        html += `
                            <tr>
                                <td class="text-center align-middle">${i+1}</td>
                                
                                <td class="text-break align-middle">
                                    <a href="${file.file_url}" target="_blank" class="text-decoration-none fw-bold" data-bs-toggle="tooltip" title="Klik untuk melihat file">
                                        ${file.file_name}
                                    </a>
                                </td>
                                
                                <td class="align-middle">${file.created_at}</td>
                                
                                <td class="text-center align-middle">
                                    <button class="btn btn-sm btn-outline-danger btn-delete-file" type="button" data-id="${file.id}" data-bs-toggle="tooltip" title="Hapus File">
                                        <span class="bx bx-trash"></span> Hapus
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center text-muted py-3">Belum ada dokumen yang diunggah.</td></tr>';
                }
                $('#list-files-body').html(html);
                $('[data-bs-toggle="tooltip"]').tooltip();
            },
            error: function() {
                $('#list-files-body').html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        })
    }

    // 3. Proses Upload File
    $('#form-upload-doc').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let btn = $('#btn-simpan-file');
        let originalText = btn.html();

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" style="width: 0.75rem; height: 0.75rem;" role="status"></span> Uploading...');

        $.ajax({
            url: "{{ route('corporate-led.files.store') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if(res.success) {
                    loadFiles($('#upload_loss_event_id').val());
                    $('#file_dokumen').val('');
                    Swal.fire('Berhasil', res.message, 'success');
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Terjadi kesalahan server', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // 4. Proses Hapus File
    $(document).on('click', '.btn-delete-file', function() {
        let fileId = $(this).data('id');
        let currentLossEventId = $('#upload_loss_event_id').val();

        Swal.fire({
            title: 'Hapus File?',
            text: "File yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/corporate-led/files/' + fileId,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if(res.success) {
                            loadFiles(currentLossEventId);
                            Swal.fire('Terhapus!', res.message, 'success');
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghapus file', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush