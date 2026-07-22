<div class="modal fade" id="modalUpdateKri" tabindex="-1" role="dialog" aria-labelledby="modalUpdateKri" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formUpdateKri">
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4" id="modalUpdateKriLabel">Edit Rencana Terhadap KRI</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{ Form::hidden('kri_project_id', '') }}
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="form-group form-floating">
                                <input type="text" class="form-control" name="key_risk_indicator" disabled>
                                <label for="key_risk_indicator_1">Key Risk Indicator</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                            <div class="form-group form-floating text-center">
                                <input type="text" class="form-control border-success" name="batas_aman" disabled>
                                <label for="batas_aman_1">Batas Aman</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                            <div class="form-group form-floating text-center">
                                <input type="text" class="form-control border-warning" name="batas_waspada" disabled>
                                <label for="batas_waspada_1">Batas Waspada</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                            <div class="form-group form-floating text-center">
                                <input type="text" class="form-control border-danger" name="batas_bahaya" disabled>
                                <label for="batas_bahaya_1">Batas Bahaya</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                            <div class="form-group form-floating">
                                <input type="text" class="form-control" name="nilai_kri" required>
                                <label for="batas_bahaya_1">Nilai KRI</label>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-auto flex-lg-grow-1">
                            <div class="form-group form-floating">
                                <select class="form-select" name="status_kri" required>
                                    <option value="1">Aman</option>
                                    <option value="2">Waspada</option>
                                    <option value="3">Bahaya</option>
                                </select>
                                <label for="batas_bahaya_1">Status KRI</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanUpdateKri">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
