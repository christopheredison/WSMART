<div class="modal fade" id="modalUpdateRealisasiDampak" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formUpdateRealisasiDampak">
                @csrf
                @method('PUT')
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4">Realisasi Perlakuan Dampak</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <input type="hidden" name="perlakuan_dampak_id" id="impact_id">

                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="impact_name" disabled>
                                <label>Dampak Risiko</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="opsi_perlakuan_risiko_dampak" id="impact_opsi" disabled>
                                <label>Opsi Perlakuan Risiko</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" name="impact_plan" id="impact_plan" rows="3" disabled></textarea>
                                <label>Rencana Perlakuan</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="impact_cost" id="impact_cost" disabled>
                                <label>Biaya Rencana</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="impact_pic" id="impact_pic" disabled>
                                <label>PIC</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="timeline_perlakuan_risiko_dampak_start" name="timeline_perlakuan_risiko_dampak_start" required disabled>
                                <label for="timeline_perlakuan_risiko_dampak_start">Waktu Mulai Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="timeline_perlakuan_risiko_dampak_end" name="timeline_perlakuan_risiko_dampak_end" required disabled>
                                <label for="timeline_perlakuan_risiko_dampak_end">Waktu Selesai Perlakuan Risiko</label>
                            </div>
                        </div>

                        <div class="col-12"><h5 class="mt-3 mb-0">Realisasi Dampak</h5></div>

                        <div class="col-12">
                            <div class="alert alert-info mb-3">
                                <div class="d-flex align-items-center">
                                    <i class='bx bx-info-circle me-2'></i>
                                    <strong>Informasi Realisasi Sebelumnya</strong>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small">Realisasi Biaya Sebelumnya:</label>
                                            <p class="mb-0" id="previous_realisasi_biaya_dampak">-</p>
                                            <input type="hidden" name="previous_realisasi_biaya_dampak_value" id="previous_realisasi_biaya_dampak_value" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="small">Progress Sebelumnya:</label>
                                            <p class="mb-0" id="previous_progress_dampak">-</p>
                                            <input type="hidden" name="previous_progress_dampak_value" id="previous_progress_dampak_value" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control inputmask-rupiah" name="realisasi_biaya_dampak" required>
                                <label>Realisasi Biaya Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" name="progress_dampak" required min="0" max="100" oninput="if(this.value < 0) this.value = 0; if(this.value > 100) this.value = 100;">
                                <label>Progress Perlakuan Risiko (%)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" name="deskripsi_dampak" rows="3" required></textarea>
                                <label>Deskripsi Realisasi</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control bg-white" id="timelineImpactInput" name="timeline_dampak" required>
                                <label>Waktu Realisasi</label>
                            </div>
                        </div>

                        <div class="col-12 d-none">
                            <table class="table mt-3">
                                <thead>
                                    <tr>
                                        <th>Dokumen</th>
                                        <th>Deskripsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="table-dokumen-dampak"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            <button type="button" class="btn btn-link btn-sm" id="btnTambahDokumenDampak">Tambah Dokumen</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanUpdateRealisasiDampak">Simpan Realisasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
