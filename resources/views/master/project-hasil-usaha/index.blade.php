@extends('layouts.default')

@section('dashboard')
    @include('partials.success-message')
    @if(session('import_summary'))
        @php $summary = session('import_summary'); @endphp
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <h4 class="alert-heading">Ringkasan Sinkronisasi</h4>
            <p>
                - <strong>Berhasil:</strong> {{ $summary['success'] }} proyek <br>
                - <strong>Diperbarui dari kosong:</strong> {{ $summary['refreshed_empty'] ?? 0 }} proyek <br>
                - <strong>Dilewati:</strong> {{ $summary['skipped'] }} proyek <br>
                - <strong>Gagal:</strong> {{ $summary['failed'] }} proyek
            </p>
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

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center gap-3">
                <div class="lead__icon bg-warning-subtle">
                    <div class="svg-icon svg-icon-warning">
                        @include('partials.icon-layer')
                    </div>
                </div>
                <div>
                  <h2 class="h3">Data Hasil Usaha Project</h2>
                </div>
                <div class="ms-auto d-flex align-items-center gap-2">

                    @include('master.project-hasil-usaha._sync_button')

                    <button id="btn-tambah" class="btn btn-outline-info btn-sm">
                        <span class="bx bx-plus"></span>
                        <span class="ms-1">Tambah Data</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover w-100" id="hasilUsahaTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Proyek</th>
                        <th>Periode</th>
                        <th>Nilai Kontrak Total</th>
                        <th>Nilai Kontrak Porsi</th>
                        <th>LSP Review</th>
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
                    <h5 class="modal-title" id="dataModalLabel">Form Hasil Usaha Proyek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="dataForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        @include('master.project-hasil-usaha._form')
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
    const table = $('#hasilUsahaTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('project-hasil-usaha.data') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'project_name', name: 'project.project_name' },
            { data: 'period', name: 'period' },
            { data: 'kontrak_review_total', name: 'kontrak_review_total' },
            { data: 'kontrak_review', name: 'kontrak_review' },
            { data: 'lsp_review', name: 'lsp_review' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ]
    });

    // 2. Inisialisasi InputMask untuk format Rupiah
    $('.inputmask-general').inputmask({
        alias: 'numeric',
        groupSeparator: '.',
        radixPoint: ',',
        autoGroup: true,
        digits: 2,
        digitsOptional: false,
        placeholder: '0',
        rightAlign: false,
        autoUnmask: false,
        removeMaskOnSubmit: false,
    });

    // 3. Tombol Tambah Data
    $('#btn-tambah').click(function() {
        $('#dataForm').trigger("reset");
        $('#dataModalLabel').text("Tambah Data");
        $('#id').val('');
        $('[name="progress_fisik_ra"]').val('0.00 %');
        $('[name="progress_fisik_ri"]').val('0.00 %');
        $('#dataModal').modal('show');
        $('#project_id').select2({
          dropdownParent: $('#dataModal')
        });
    });

    // 4. Tombol Edit Data
    $('body').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        $.get("{{ url('project-hasil-usaha') }}/" + id + "/edit", function(data) {
            $('#dataModalLabel').text("Edit Data");
            $('#dataModal').modal('show');

            for (const key in data) {
                if (['kontrak_review', 'penjualan_ra', 'penjualan_ri', 'lsp_review', 'lsp_proyeksi', 'lsp_ra', 'lsp_ri'].includes(key)) {
                    let value = data[key];
                    if (value !== null) {
                        value = String(value).replace('.', ',');
                        $(`[name="${key}"]`).val(value);
                    }
                } else {
                    $(`[name="${key}"]`).val(data[key]);
                }
            }

            $('#project_id').select2({ dropdownParent: $('#dataModal') });
            calculateProgress();
        });
    });

    // 5. Simpan Data (Create & Update)
    $('#dataForm').submit(function(e) {
        e.preventDefault();
        $('#btn-save').html('Menyimpan...').prop('disabled', true);

        const id = $('#id').val();
        const url = id ? "{{ url('project-hasil-usaha') }}/" + id : "{{ route('project-hasil-usaha.store') }}";
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            data: $(this).serialize(),
            url: url,
            type: method,
            dataType: 'json',
            success: function(response) {
                $('#dataModal').modal('hide');
                table.ajax.reload();
                Swal.fire("Sukses!", response.success, "success");
            },
            error: function(xhr) {
                // Tampilkan error validasi
                let errors = xhr.responseJSON.errors;
                let errorMsg = '';
                $.each(errors, function(key, value) {
                    errorMsg += value[0] + '<br>';
                });
                Swal.fire("Error!", errorMsg, "error");
            },
            complete: function() {
                $('#btn-save').html('Simpan').prop('disabled', false);
            }
        });
    });

    // 6. Hapus Data
    $('body').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, hapus!",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: "{{ url('project-hasil-usaha') }}/" + id,
                    success: function(response) {
                        table.ajax.reload();
                        Swal.fire("Dihapus!", response.success, "success");
                    }
                });
            }
        });
    });

    // 7. Logika Kalkulasi Progress Fisik
    function calculateProgress() {
        function parseMaskedNumber(val) {
            if (!val) return 0;
            let clean = val.replace(/\./g, '');
            clean = clean.replace(',', '.');
            return parseFloat(clean) || 0;
        }

        const kontrak = parseMaskedNumber($('[name="kontrak_review"]').val());
        const penjualanRA = parseMaskedNumber($('[name="penjualan_ra"]').val());
        const penjualanRI = parseMaskedNumber($('[name="penjualan_ri"]').val());

        let progressRA = (kontrak > 0) ? (penjualanRA / kontrak) * 100 : 0;
        let progressRI = (kontrak > 0) ? (penjualanRI / kontrak) * 100 : 0;

        $('[name="progress_fisik_ra"]').val(progressRA.toFixed(2) + ' %');
        $('[name="progress_fisik_ri"]').val(progressRI.toFixed(2) + ' %');
    }

    const sourceFields = '[name="kontrak_review"], [name="penjualan_ra"], [name="penjualan_ri"]';
    $('#dataModal').on('keyup', sourceFields, calculateProgress);

    // 8. Logika Tombol Sinkronisasi
    $('#syncForm').on('submit', function() {
        $('#submitSyncBtn').prop('disabled', true);
        $('#sync-loading').removeClass('d-none');
        $('#sync-text').text('Memproses...');
    });
});
</script>
@endpush
