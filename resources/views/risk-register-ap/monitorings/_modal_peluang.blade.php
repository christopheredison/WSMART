<div class="modal fade" id="modalPeluang" tabindex="-1" role="dialog" aria-labelledby="modalPeluangLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPeluangLabel">Data Peluang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="mb-2">Risiko: <span id="peluang-risk-title"></span></h6>
                    <p class="text-muted" id="peluang-risk-desc"></p>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add-peluang">
                        <i class="mdi mdi-plus"></i> Tambah Peluang
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="peluang-table">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Penjelasan Peluang (Rencana)</th>
                                <th>Penjelasan Peluang (Realisasi)</th>
                                <th>Nilai Peluang (Rencana)</th>
                                <th>Nilai Peluang (Realisasi)</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="peluang-list">
                            <tr id="peluang-empty-row">
                                <td colspan="6" class="text-center">Belum ada data peluang</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Form untuk tambah/edit peluang -->
                <div id="peluang-form-container" class="d-none border p-3 rounded mt-3">
                    <h6 class="mb-3" id="peluang-form-title">Tambah Peluang Baru</h6>
                    <form id="peluang-form">
                        @csrf
                        <input type="hidden" id="peluang-id" name="peluang_id">
                        <input type="hidden" id="identifikasi-risiko-id" name="identifikasi_risiko_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="penjelasan_peluang_rencana" class="form-label">Penjelasan Peluang (Rencana)</label>
                                <textarea class="form-control" id="penjelasan_peluang_rencana" name="penjelasan_peluang_rencana" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="penjelasan_peluang_realisasi" class="form-label">Penjelasan Peluang (Realisasi)</label>
                                <textarea class="form-control" id="penjelasan_peluang_realisasi" name="penjelasan_peluang_realisasi" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nilai_peluang_rencana" class="form-label">Nilai Peluang (Rencana)</label>
                                <input type="text" class="form-control rupiah" id="nilai_peluang_rencana" name="nilai_peluang_rencana">
                            </div>
                            <div class="col-md-6">
                                <label for="nilai_peluang_realisasi" class="form-label">Nilai Peluang (Realisasi)</label>
                                <input type="text" class="form-control rupiah" id="nilai_peluang_realisasi" name="nilai_peluang_realisasi">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" id="btn-cancel-peluang">Batal</button>
                            <button type="submit" class="btn btn-primary" id="btn-save-peluang">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>