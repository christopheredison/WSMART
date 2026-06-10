<div class="modal fade" id="modalUpdateKri" tabindex="-1" role="dialog" aria-labelledby="modalUpdateKri" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content p-0">
            <form method="POST" id="formUpdateKri">
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4" id="modalUpdateKriLabel">Update Realisasi KRI</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{ Form::hidden('kri_project_id', '') }}
                <div class="modal-body bg-light">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase mb-3">Informasi Key Risk Indicator</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-group form-floating">
                                        <input type="text" class="form-control" name="key_risk_indicator" disabled>
                                        <label>Parameter / Key Risk Indicator</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group form-floating">
                                        <input type="text" class="form-control fw-bold" name="kri_tren_parameter" disabled>
                                        <label>Tren Parameter</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-floating">
                                        <textarea class="form-control" name="kri_metode_pengukuran" cols="2" disabled></textarea>
                                        <label>Metode Pengukuran</label>
                                    </div>
                                </div>

                                <!-- <div class="col-12 kri-new-fields d-none mt-4 mb-1">
                                    <span class="text-muted fw-bold">Ambang Batas / Threshold KRI</span>
                                </div>
                                <div class="col-4 kri-new-fields d-none">
                                    <div class="form-group form-floating text-center">
                                        <input type="text" class="form-control fw-bold" name="kri_risk_limit" disabled>
                                        <label>Risk Limit</label>
                                    </div>
                                </div>
                                <div class="col-4 kri-new-fields d-none">
                                    <div class="form-group form-floating text-center">
                                        <input type="text" class="form-control fw-bold" name="kri_risk_appetite" disabled>
                                        <label>Risk Appetite</label>
                                    </div>
                                </div>
                                <div class="col-4 kri-new-fields d-none">
                                    <div class="form-group form-floating text-center">
                                        <input type="text" class="form-control fw-bold" name="kri_risk_tolerance" disabled>
                                        <label>Risk Tolerance</label>
                                    </div>
                                </div> -->

                                <div class="col-12 mt-4 mb-1">
                                    <span class="text-muted fw-bold">Ambang Batas / Threshold KRI</span>
                                </div>
                                <div class="col-4">
                                    <div class="form-group form-floating text-center">
                                        <input type="text" class="form-control border-success text-success fw-bold" name="batas_aman" disabled>
                                        <label>Risk Limit</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group form-floating text-center">
                                        <input type="text" class="form-control border-warning text-warning fw-bold" name="batas_waspada" disabled>
                                        <label>Risk Appetite</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group form-floating text-center">
                                        <input type="text" class="form-control border-danger text-danger fw-bold" name="batas_bahaya" disabled>
                                        <label>Risk Tolerance</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-primary text-uppercase mb-3">Input Realisasi</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group form-floating">
                                        <input type="text" class="form-control border-primary" name="nilai_kri" required>
                                        <label>Nilai Realisasi KRI <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group form-floating">
                                        <select class="form-select border-primary" name="status_kri" id="status_kri_select" required>
                                            <option value="" disabled selected>Pilih Status</option>
                                            <option value="1">Aman</option>
                                            <option value="2">Waspada (Siaga)</option>
                                            <option value="3">Bahaya</option>
                                        </select>
                                        <label>Status KRI <span class="text-danger">*</span></label>
                                    </div>
                                </div>

                                <div class="col-12 d-none mt-4 animate__animated animate__fadeIn" id="kri-pengendalian-section">
                                    <div class="p-3 border border-danger rounded bg-danger-subtle bg-opacity-10">
                                        <h6 class="text-danger mb-3"><i class="bx bx-error-circle"></i> Pengendalian Parameter KRI</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-group form-floating">
                                                    <textarea class="form-control bg-white shadow-none" name="kri_rencana_pengendalian" style="height: 80px;"></textarea>
                                                    <label>Rencana Pengendalian</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-floating">
                                                    <textarea class="form-control bg-white shadow-none" name="kri_realisasi_pengendalian" style="height: 80px;"></textarea>
                                                    <label>Realisasi Pengendalian</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-floating">
                                                    <input type="text" class="form-control bg-white inputmask-rupiah" name="kri_biaya_rencana_pengendalian" value="0">
                                                    <label>Biaya Rencana Pengendalian</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-floating">
                                                    <input type="text" class="form-control bg-white inputmask-rupiah" name="kri_biaya_realisasi_pengendalian" value="0">
                                                    <label>Biaya Realisasi Pengendalian</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanUpdateKri">Simpan KRI</button>
                </div>
            </form>
        </div>
    </div>
</div>