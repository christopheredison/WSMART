<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-labelledby="modalCreate"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg " role="document">
        <div class="modal-content">
            <form method="POST" action="{{ $action }}" id="formCreate">
                @csrf
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4" id="newSkalaDampakLabel">Buat {{ $resourceName }} Baru</h3>
                    <div class="lead__icon lead__icon_sm">
                        <div class="svg-icon svg-icon-secondary">
                            @include('partials.icon-tool')
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    @foreach ($fields as $field)
                    <div class="form-group d-md-flex mb-4">
                        <label class="form-label label-md-start col-md-2">{{ $field['label'] }}</label>
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
    $('#formCreate').submit(function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(form[0]);
        $.ajax({
            url: $(this).attr('action'),
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
                $('#modalCreate').modal('hide');
                $('#formCreate').trigger('reset');
                $('.ajax-datatable').DataTable().ajax.reload();
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
});
</script>
@endpush