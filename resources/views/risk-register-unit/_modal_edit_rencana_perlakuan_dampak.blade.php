<div class="modal fade" id="modalEditRencanaDampak" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formEditRencanaDampak">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="perlakuanDampakId">

                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4">Edit Rencana Perlakuan Dampak</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        @include('risk-register-unit.form-perencanaan-dampak-edit', ['suffix' => 'EditDampak', 'prefix' => 'x'])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnUpdateDampak">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
