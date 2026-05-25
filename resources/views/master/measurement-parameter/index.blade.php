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
                    <h2 class="h3">Master Parameter Pengukuran</h2>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <button id="btn-tambah" class="btn btn-outline-primary btn-sm">
                        <span class="bx bx-plus"></span>
                        <span class="ms-1">Tambah Parameter</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover w-100" id="parameterTable">
                <thead class="bg-light">
                    <tr>
                        <th width="5%" class="text-center">#</th>
                        <th width="15%">Dimensi</th>
                        <th width="15%">Sub Dimensi</th>
                        <th>Parameter Statement</th>
                        <th width="12%" class="text-center">Jumlah Kriteria</th>
                        <th width="12%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Form Tambah/Edit --}}
    <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataModalLabel">Form Parameter Pengukuran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="dataForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Dimensi</label>
                                <select class="form-select" id="dimension_id" required>
                                    <option value="" disabled selected>-- Pilih Dimensi --</option>
                                    @foreach($dimensions as $dim)
                                        <option value="{{ $dim->id }}">{{ $dim->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Sub Dimensi</label>
                                <select class="form-select" id="sub_dimension_id" name="sub_dimension_id" required disabled>
                                    <option value="" selected disabled>Pilih Dimensi Terlebih Dahulu</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Parameter Statement</label>
                            <textarea class="form-control" id="statement" name="statement" rows="4" placeholder="Masukkan detail parameter pengukuran..." required></textarea>
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
    const dimensionsData = @json($dimensions);

    function populateSubDimensions(dimensionId, selectedSubId = null) {
        const subSelect = $('#sub_dimension_id');
        subSelect.empty().append('<option value="" selected disabled>-- Pilih Sub Dimensi --</option>');

        if (dimensionId) {
            const selectedDim = dimensionsData.find(d => d.id == dimensionId);
            if (selectedDim && selectedDim.sub_dimensions.length > 0) {
                selectedDim.sub_dimensions.forEach(sub => {
                    let isSelected = (selectedSubId == sub.id) ? 'selected' : '';
                    subSelect.append(`<option value="${sub.id}" ${isSelected}>${sub.name}</option>`);
                });
                subSelect.prop('disabled', false);
            } else {
                subSelect.empty().append('<option value="" disabled>Tidak ada Sub Dimensi di Dimensi ini</option>');
                subSelect.prop('disabled', true);
            }
        }
    }

    $('#dimension_id').on('change', function() {
        populateSubDimensions($(this).val());
    });

    const table = $('#parameterTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('measurement-parameter.data') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'dimensi', name: 'subDimension.dimension.name' },
            { data: 'sub_dimensi', name: 'subDimension.name' },
            { data: 'statement', name: 'statement' },
            { data: 'criteria_count', name: 'criteria_count', searchable: false, className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ],
        drawCallback: function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });
        }
    });

    $('#btn-tambah').click(function() {
        $('#dataForm').trigger("reset");
        $('#dataModalLabel').text("Tambah Parameter Pengukuran");
        $('#id').val('');
        $('#sub_dimension_id').empty().append('<option value="" selected disabled>Pilih Dimensi Terlebih Dahulu</option>').prop('disabled', true);
        $('#dataModal').modal('show');
    });

    $('body').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.get("{{ url('master/measurement-parameter') }}/" + id + "/edit", function(data) {
            $('#dataModalLabel').text("Edit Parameter Pengukuran");
            $('#dataModal').modal('show');
            $('#id').val(data.id);
            $('#statement').val(data.statement);
            $('#dimension_id').val(data.dimension_id);
            populateSubDimensions(data.dimension_id, data.sub_dimension_id);
        });
    });

    $('#dataForm').submit(function(e) {
        e.preventDefault();
        $('#btn-save').html('Menyimpan...').prop('disabled', true);

        const id = $('#id').val();
        const url = id ? "{{ url('master/measurement-parameter') }}/" + id : "{{ route('measurement-parameter.store') }}";
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            data: $(this).serialize(), url: url, type: method, dataType: 'json',
            success: function(response) {
                $('#dataModal').modal('hide');
                table.ajax.reload(null, false);
                Swal.fire("Sukses!", response.success, "success");
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).join('<br>') : 'Terjadi kesalahan server.';
                Swal.fire("Error!", errorMsg, "error");
            },
            complete: function() { $('#btn-save').html('Simpan').prop('disabled', false); }
        });
    });

    $('body').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: "Anda yakin?", text: "Data parameter akan dihapus!", icon: "warning",
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: "Ya, hapus!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE", url: "{{ url('master/measurement-parameter') }}/" + id,
                    data: { "_token": "{{ csrf_token() }}" },
                    success: function(response) {
                        table.ajax.reload(null, false);
                        Swal.fire("Dihapus!", response.success, "success");
                    }
                });
            }
        });
    });
});
</script>
@endpush
