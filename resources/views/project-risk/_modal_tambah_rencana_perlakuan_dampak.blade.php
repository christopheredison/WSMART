<div class="modal fade" id="modalTambahRencanaDampak" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formTambahRencanaDampak">
                @csrf
                <input type="hidden" name="risiko_id" id="risikoIdDampak">

                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4">Tambah Rencana Perlakuan Dampak</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        @include('project-risk.form-perencanaan-dampak', ['suffix' => 'Dampak'])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanTambahDampak">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
