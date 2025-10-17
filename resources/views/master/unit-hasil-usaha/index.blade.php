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
                    <h2 class="h3">Data Hasil Usaha Divisi</h2>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <button id="btn-tambah" class="btn btn-outline-info btn-sm">
                        <span class="bx bx-plus"></span>
                        <span class="ms-1">Tambah Data</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover w-100" id="unitHasilUsahaTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Divisi</th>
                        <th>Cost Center</th>
                        <th>Periode</th>
                        <th>Nilai Kontrak</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal untuk Tambah & Edit Data --}}
    <div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dataModalLabel">Form Hasil Usaha Divisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="dataForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        {{-- Memuat file partial yang berisi field-field form --}}
                        @include('master.unit-hasil-usaha._form')
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
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(document).ready(function() {
    // 1. Inisialisasi DataTable
    const table = $('#unitHasilUsahaTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('hasil-usaha-divisi.data') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'unit_name', name: 'unit.name' },
            { data: 'cost_center', name: 'cost_center' },
            { data: 'period', name: 'period' },
            { data: 'kontrak_review', name: 'kontrak_review' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    // 2. Inisialisasi InputMask untuk format Rupiah pada semua input yang relevan
    $('.inputmask-general').inputmask({
        alias: 'numeric', groupSeparator: '.', radixPoint: ',', autoGroup: true,
        digits: 0, digitsOptional: true, placeholder: '0', rightAlign: false,
        autoUnmask: true, removeMaskOnSubmit: true,
    });

    // 3. Logika Tombol Tambah Data
    $('#btn-tambah').click(function() {
        $('#dataForm').trigger("reset");
        $('#dataModalLabel').text("Tambah Data");
        $('#id').val('');
        $('[name="progress_fisik_ra"]').val('0.00 %');
        $('[name="progress_fisik_ri"]').val('0.00 %');
        $('#dataModal').modal('show');
    });

    // 4. Logika Tombol Edit Data
    $('body').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.get("{{ url('hasil-usaha-divisi') }}/" + id + "/edit", function(data) {
            $('#dataModalLabel').text("Edit Data");
            $('#dataModal').modal('show');
            for (const key in data) {
                $(`[name="${key}"]`).val(data[key]);
            }
            $('.inputmask-general').trigger('input');
            calculateProgress();
        });
    });

    // 5. Logika Simpan Data (Create & Update)
    $('#dataForm').submit(function(e) {
        e.preventDefault();
        $('#btn-save').html('Menyimpan...').prop('disabled', true);
        const id = $('#id').val();
        const url = id ? "{{ url('hasil-usaha-divisi') }}/" + id : "{{ route('hasil-usaha-divisi.store') }}";
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
                let errorMsg = Object.values(errors).join('<br>');
                Swal.fire("Error!", errorMsg, "error");
            },
            complete: function() {
                $('#btn-save').html('Simpan').prop('disabled', false);
            }
        });
    });

    // 6. Logika Hapus Data
    $('body').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: "Anda yakin?", text: "Data tidak dapat dikembalikan!", icon: "warning",
            showCancelButton: true, confirmButtonText: "Ya, hapus!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE", url: "{{ url('hasil-usaha-divisi') }}/" + id,
                    success: function(response) {
                        table.ajax.reload(null, false);
                        Swal.fire("Dihapus!", response.success, "success");
                    }
                });
            }
        });
    });

    // 7. Logika Kalkulasi Progress Fisik Otomatis
    function calculateProgress() {
        const kontrak = parseFloat($('[name="kontrak_review"]').inputmask('unmaskedvalue')) || 0;
        const penjualanRA = parseFloat($('[name="penjualan_ra"]').inputmask('unmaskedvalue')) || 0;
        const penjualanRI = parseFloat($('[name="penjualan_ri"]').inputmask('unmaskedvalue')) || 0;
        let progressRA = (kontrak > 0) ? (penjualanRA / kontrak) * 100 : 0;
        let progressRI = (kontrak > 0) ? (penjualanRI / kontrak) * 100 : 0;
        $('[name="progress_fisik_ra"]').val(progressRA.toFixed(2) + ' %');
        $('[name="progress_fisik_ri"]').val(progressRI.toFixed(2) + ' %');
    }
    const sourceFields = '[name="kontrak_review"], [name="penjualan_ra"], [name="penjualan_ri"]';
    $('#dataModal').on('keyup', sourceFields, calculateProgress);
    
    // 8. Logika Mengisi Cost Center Otomatis
    $('#unit_id').on('change', function() {
        const selectedCostCenter = $(this).find('option:selected').data('cost-center');
        $('#cost_center').val(selectedCostCenter || '');
    });
});
</script>
@endpush