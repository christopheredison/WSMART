@extends('layouts.default')
@section('dashboard')
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center gap-3">
                <div class="lead__icon bg-warning-subtle">
                    <div class="svg-icon svg-icon-warning">
                        @include('partials.icon-layer')
                    </div>
                </div>
                <div>
                    <h2 class="h3">Master Dimensi</h2>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <button id="btn-tambah" class="btn btn-outline-primary btn-sm">
                        <span class="bx bx-plus"></span>
                        <span class="ms-1">Tambah Dimensi</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover w-100" id="dimensionTable">
                <thead class="bg-light">
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th>Nama Dimensi</th>
                        <th width="15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah & Edit --}}
    <div class="modal fade" id="dataModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataModalLabel">Form Dimensi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="dataForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Dimensi</label>
                            <input type="text" class="form-control" name="name" id="name" required placeholder="Masukkan Nama Dimensi">
                        </div>
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
    const table = $('#dimensionTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('dimension.data') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ],
        drawCallback: function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });
        }
    });

    $('#btn-tambah').click(function() {
        $('#dataForm').trigger("reset");
        $('#dataModalLabel').text("Tambah Dimensi");
        $('#id').val('');
        $('#dataModal').modal('show');
    });

    $('body').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.get("{{ url('master/dimension') }}/" + id + "/edit", function(data) {
            $('#dataModalLabel').text("Edit Dimensi");
            $('#dataModal').modal('show');
            $('#id').val(data.id);
            $('#name').val(data.name);
        });
    });

    $('#dataForm').submit(function(e) {
        e.preventDefault();
        $('#btn-save').html('Menyimpan...').prop('disabled', true);

        const id = $('#id').val();
        const url = id ? "{{ url('master/dimension') }}/" + id : "{{ route('dimension.store') }}";
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            data: $(this).serialize(),
            url: url,
            type: method,
            dataType: 'json',
            success: function(response) {
                $('#dataModal').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire("Sukses!", response.success, "success");
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).join('<br>') : 'Terjadi kesalahan server.';
                Swal.fire("Error!", errorMsg, "error");
            },
            complete: function() {
                $('#btn-save').html('Simpan').prop('disabled', false);
            }
        });
    });

    $('body').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: "Anda yakin?",
            text: "Data dimensi akan dihapus!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: "Ya, hapus!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: "{{ url('master/dimension') }}/" + id,
                    data: { "_token": "{{ csrf_token() }}" },
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
