<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEdit"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg " role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $action }}" id="formEdit">
                @csrf
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4" id="newSkalaDampakLabel">Edit {{ $resourceName }}</h3>
                    <div class="lead__icon lead__icon_sm">
                        <div class="svg-icon svg-icon-secondary">
                            @include('partials.icon-tool')
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    @foreach ($fields as $field)
                    <div class="form-group d-md-flex mb-4">
                        <label class="form-label label-md-start col-md-2" for="tingkat">{{ $field['label'] }}</label>
                        {{ Form::{$field['type']}(...$field['parameters']) }}
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    $('#formEdit').submit(function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(form[0]);
        formData.append('_method', 'PUT');

        const inputmaskInputs = form.find('.inputmask-general');

        inputmaskInputs.each(function () {
            const input = $(this);
            const inputmask = input.inputmask('unmaskedvalue');
            formData.set(input.attr('name'), inputmask);
        });

        $.ajax({
            url: $(this).attr('action').replace(':id', $('#formEdit').data('id')),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.message || 'Data berhasil disimpan',
                });
                $('#modalEdit').modal('hide');
                if ($('.ajax-datatable').length) {
                    $('.ajax-datatable').DataTable().ajax.reload();
                } else {
                    window.location.reload();
                }
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan data',
                });
            }
        });
    });

    // Fungsi untuk menghitung rapk_persentase
    function hitungRapkPersentase() {
        const nkPpn = parseFloat($('#formEdit input[name="nk_ppn"]').inputmask('unmaskedvalue') || 0);
        const rapk = parseFloat($('#formEdit input[name="rapk"]').inputmask('unmaskedvalue') || 0);
        
        console.log('nkPpn : ' + nkPpn);
        console.log('rapk : ' + rapk);

        if (nkPpn > 0 && rapk > 0) {
            const persentase = (rapk / nkPpn) * 100;
            console.log('rapk %' + persentase);
            $('#formEdit input[name="rapk_persentase"]').val(persentase.toFixed(2));
        }
    }

    // Event listener untuk perubahan nilai nk_ppn dan rapk
    $('#formEdit input[name="nk_ppn"],#formEdit input[name="rapk"]').on('change', function() {
        console.log('hitung rapk %');
        hitungRapkPersentase();
    });
});
</script>
@endpush