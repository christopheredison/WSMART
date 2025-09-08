<div class="modal fade" id="modalEditRencana" role="dialog" aria-labelledby="modalEditRencana" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formEditRencana">
                @csrf
                @method('PUT') <!-- Metode PUT untuk update -->
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4" id="modalEditRencanaLabel">Edit Rencana Perlakuan Risiko</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('project-risk.form-perencanaan-edit') <!-- Include shared form -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanEditRencana">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('#formEditRencana').on('submit', function (event) {
        event.preventDefault(); // Cegah submit default
        return false; // Paksa tidak melakukan submit
    });
    
    $('#btnSimpanEditRencana').on('click', function(event) {
        event.preventDefault();
        
        const formData = $('#formEditRencana').serialize(); // Ambil semua data form
        const penyebabRisikoId = $('#formEditRencana #xpenyebabRisikoId').val(); // Ambil ID
        const perlakuanId = $('#formEditRencana #xperlakuanId').val(); // Ambil ID
        console.log('penyebabRisikoId:', penyebabRisikoId);

        $.ajax({
            url: `/rencana-perlakuan/${perlakuanId}`,
            type: 'PUT',
            data: formData,
            success: function(response) {
                Swal.fire('Berhasil!', response.message || 'Rencana perlakuan berhasil diperbarui.', 'success')
                    .then(() => {
                        window.location.reload(); // Refresh halaman
                    });
            },
            error: function(xhr) {
                Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan perubahan.', 'error');
            }
        });
    });

    $('.inputmask-rupiah').inputmask({
        alias: 'numeric',
        groupSeparator: '.',
        autoGroup: true,
        digits: 0,
        digitsOptional: false,
        prefix: 'Rp ',
        placeholder: '0',
        rightAlign: false,
        autoUnmask: true,
        removeMaskOnSubmit: true,
        min: 0,
        allowMinus: false,
        onKeyDown: function(e) {
        if (e.key === 'Backspace' || e.keyCode === 8) {
            // tunda eksekusi sampai mask selesai di-apply
            setTimeout(() => {
                const unmasked = this.inputmask.unmaskedvalue();
                // kalau masih ada angka tersisa
                if (unmasked.length > 0) {
                // cek posisi cursor
                const pos = this.selectionStart;
                if (pos === 0) {
                    // pindahkan ke paling kanan
                    const end = this.value.length;
                    this.setSelectionRange(end, end);
                }
                }
            }, 0);
            }
        }
    });

    var flatpickrIns = flatpickr("#xtimelineRange", {
        mode: "range",
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
        disableMobile: true
    });
})
</script>
@endpush
