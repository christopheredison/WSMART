<div class="modal fade" id="modalTambahRencana" tabindex="-1" role="dialog" aria-labelledby="modalTambahRencana"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formTambahRencana">
                @csrf
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4" id="modalTambahRencanaLabel">Tambah Rencana Perlakuan Risiko</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @include('risk-register-ap.form-perencanaan') <!-- Include the shared fields -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanTambahRencana">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .general-checkbox label.form-check {
        pointer-events: auto !important;
    }

    .general-checkbox .form-check input {
        pointer-events: auto !important; 
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(document).ready(function() {
    $('#btnSimpanTambahRencana').on('click', function() {
        const formData = $('#formTambahRencana').serialize(); // Ambil semua data dari form

        $.ajax({
            url: '{{ route('risk-register-ap.do-perencanaan', $identifikasiRisiko->id) }}', // Endpoint untuk menyimpan data
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#modalTambahRencana').modal('hide'); // Tutup modal
                Swal.fire({
                    title: 'Berhasil',
                    text: response.message, // Ambil pesan dari controller
                    icon: 'success',
                    confirmButtonText: 'OK',
                }).then(() => {
                    window.location.reload(); // Reload halaman
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message; // Ambil pesan error dari controller jika ada
                }
                Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK',
                });
            }
        });
    });

    $('.inputmask-rupiah').inputmask({
        alias: 'numeric',
        groupSeparator: '.',
        autoGroup: true,
        digits: 0,
        digitsOptional: false,
        prefix: 'Rpx ',
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
    }).on('keypress', function(e) {
        // Mencegah karakter minus
        if (e.key === '-') {
            e.preventDefault();
            return false;
        }
    });

    // Tambahkan validasi saat input berubah
    $('.inputmask-rupiah').on('input', function() {
        let value = $(this).val().replace(/[^0-9]/g, '');
        if (value < 0) {
            $(this).val(0);
        }
    });

    //var today = new Date();
    //var endOfYear = new Date(today.getFullYear(), 11, 31);
    const risk = @json($identifikasiRisiko);

    var flatpickrIns1 = flatpickr("#timelineRange1", {
        //mode: "range",
        altInput: false,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
        minDate: risk ? dayjs(risk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
        maxDate: risk ? dayjs(risk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
        disableMobile: true
    });

    var flatpickrIns2 = flatpickr("#timelineRange2", {
        //mode: "range",
        altInput: false,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
        minDate: risk ? dayjs(risk?.perkiraan_waktu_terpapar_risiko_mulai, 'YYYY-MM-DD').toDate() : null,
        maxDate: risk ? dayjs(risk?.perkiraan_waktu_terpapar_risiko_akhir, 'YYYY-MM-DD').toDate() : null,
        disableMobile: true
    });

    // Mendapatkan elemen select untuk opsi perlakuan risiko dan jenis rencana perlakuan risiko
    const opsiPerlakuanRisiko = document.getElementById('opsi_perlakuan_risiko');
    const jenisRencanaPerlakuanRisiko = document.getElementById('jenis_rencana_perlakuan_risiko');
    
    // Menambahkan event listener untuk perubahan pada opsi perlakuan risiko
    opsiPerlakuanRisiko.addEventListener('change', function() {
        // Jika opsi perlakuan risiko yang dipilih adalah ID 3 (Accept/monitor)
        if (this.value === '3') {
            // Set jenis rencana perlakuan risiko ke ID 8 (Lainnya)
            jenisRencanaPerlakuanRisiko.value = '8';
        }
    });
})
</script>
@endpush