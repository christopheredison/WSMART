@extends('layouts.default')

@section('dashboard')
    @include('partials.success-message')

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center gap-3">
                <div class="lead__icon bg-warning-subtle">
                    <div class="svg-icon svg-icon-warning">
                        @include('partials.icon-layer')
                    </div>
                </div>
                <div>
                    <h2 class="h3">Master Data WBS</h2>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <button id="btn-tambah" class="btn btn-outline-primary btn-sm">
                        <span class="bx bx-plus"></span>
                        <span class="ms-1">Tambah WBS</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover w-100" id="wbsTable">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">Kode</th>
                        <th>WBS / Deskripsi</th>
                        <th width="15%">Status</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah & Edit --}}
    <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataModalLabel">Form Master WBS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="dataForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        @include('master.wbs._form')
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btn-save">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // 1. Init DataTables
    const table = $('#wbsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('wbs.data') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'status', name: 'is_active' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    // 2. Tombol Tambah
    $('#btn-tambah').click(function() {
        $('#dataForm').trigger("reset");
        $('#dataModalLabel').text("Tambah Data WBS");
        $('#id').val('');
        // Default Active Checked saat tambah baru
        $('#is_active').prop('checked', true);
        $('#dataModal').modal('show');
    });

    // 3. Tombol Edit
    $('body').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.get("{{ url('wbs') }}/" + id + "/edit", function(data) {
            $('#dataModalLabel').text("Edit Data WBS");
            $('#dataModal').modal('show');
            $('#id').val(data.id);
            $('[name="code"]').val(data.code);
            $('[name="name"]').val(data.name);

            // Set Checkbox Active
            if(data.is_active == 1) {
                $('#is_active').prop('checked', true);
            } else {
                $('#is_active').prop('checked', false);
            }
        });
    });

    // 4. Simpan Data
    $('#dataForm').submit(function(e) {
        e.preventDefault();
        $('#btn-save').html('Menyimpan...').prop('disabled', true);
        const id = $('#id').val();
        const url = id ? "{{ url('wbs') }}/" + id : "{{ route('wbs.store') }}";
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            data: $(this).serialize(), url: url, type: method, dataType: 'json',
            success: function(response) {
                $('#dataModal').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire("Sukses!", response.success, "success");
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMsg = '';
                if(errors) {
                    errorMsg = Object.values(errors).join('<br>');
                } else {
                    errorMsg = 'Terjadi kesalahan pada server.';
                }
                Swal.fire("Error!", errorMsg, "error");
            },
            complete: function() {
                $('#btn-save').html('Simpan').prop('disabled', false);
            }
        });
    });

    // 5. Hapus Data
    $('body').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: "Anda yakin?", text: "Data akan dihapus (Soft Delete)!", icon: "warning",
            showCancelButton: true, confirmButtonText: "Ya, hapus!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE", url: "{{ url('wbs') }}/" + id,
                    success: function(response) {
                        table.ajax.reload(null, false);
                        Swal.fire("Dihapus!", response.success, "success");
                    },
                    error: function() {
                        Swal.fire("Gagal", "Data tidak bisa dihapus.", "error");
                    }
                });
            }
        });
    });
});
</script>
@endpush
