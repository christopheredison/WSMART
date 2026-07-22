<div class="modal fade" id="modalPeluang" tabindex="-1" role="dialog" aria-labelledby="modalPeluangLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content p-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalPeluangLabel">
                    Data Peluang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">

                <div class="card mb-3 shadow-sm border-0">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-primary">Risiko Terkait:</h6>
                                <span id="peluang-risk-title" class="fw-bold text-dark"></span>
                                <p class="text-muted small mb-0" id="peluang-risk-desc"></p>
                            </div>
                            <div class="ms-3">
                                <button type="button" class="btn btn-primary btn-sm shadow-sm" id="btn-add-peluang">
                                    <span class="bx bx-plus"></span> Tambah Peluang Baru
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0" id="peluang-table">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="25%">Penjelasan (Rencana)</th>
                                        <th width="25%">Penjelasan (Realisasi)</th>
                                        <th width="15%">Nilai (Rencana)</th>
                                        <th width="15%">Nilai (Realisasi)</th>
                                        <th width="5%" class="text-center">File</th>
                                        <th width="6%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="peluang-list">
                                    <tr id="peluang-empty-row">
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data peluang</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="peluang-form-container" class="d-none">
                    <div class="card shadow-sm border-primary border-top border-3">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 text-primary fw-bold" id="peluang-form-title">
                                <i class="bx bx-edit-alt"></i> Form Peluang
                            </h6>
                        </div>
                        <div class="card-body">
                            <form id="peluang-form" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" id="peluang-id" name="peluang_id">
                                <input type="hidden" id="identifikasi-risiko-id" name="identifikasi_risiko_id">

                                <div class="row g-4">
                                    <div class="col-lg-6 border-end">
                                        <h6 class="text-info border-bottom pb-2 mb-3">Data Rencana</h6>
                                        <div class="mb-3">
                                            <label for="penjelasan_peluang_rencana" class="form-label small fw-bold">Penjelasan Rencana</label>
                                            <textarea class="form-control" id="penjelasan_peluang_rencana" name="penjelasan_peluang_rencana" rows="3" placeholder="Masukkan detail rencana..."></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nilai_peluang_rencana" class="form-label small fw-bold">Nilai Rencana (Rp)</label>
                                            <input type="text" class="form-control rupiah" id="nilai_peluang_rencana" name="nilai_peluang_rencana">
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <h6 class="text-success border-bottom pb-2 mb-3">Data Realisasi</h6>
                                        <div class="mb-3">
                                            <label for="penjelasan_peluang_realisasi" class="form-label small fw-bold">Penjelasan Realisasi</label>
                                            <textarea class="form-control" id="penjelasan_peluang_realisasi" name="penjelasan_peluang_realisasi" rows="3" placeholder="Masukkan detail realisasi..."></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nilai_peluang_realisasi" class="form-label small fw-bold">Nilai Realisasi (Rp)</label>
                                            <input type="text" class="form-control rupiah" id="nilai_peluang_realisasi" name="nilai_peluang_realisasi">
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <label for="file_peluang" class="form-label fw-bold mb-1">Dokumen Pendukung (Opsional)</label>
                                        <input type="file" class="form-control" id="file_peluang" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                        <div class="form-text text-muted mb-0 small">
                                            Maksimal 5MB. Format: PDF, Office, Gambar.
                                        </div>
                                        <div id="current-file-display" class="mt-2 d-none">
                                            <a href="#" id="current-file-link" target="_blank" class="badge bg-info text-white text-decoration-none fw-normal p-2">
                                                <i class="bx bx-file"></i> File saat ini: <span id="current-filename" class="fw-bold"></span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end mt-3 mt-md-0">
                                        <button type="button" class="btn btn-light border me-2" id="btn-cancel-peluang">Batal</button>
                                        <button type="submit" class="btn btn-primary px-4" id="btn-save-peluang">
                                            <span class="bx bx-save"></span> Simpan Data
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
