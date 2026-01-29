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

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control inputmask-rupiah" name="realisasi_biaya_dampak" required>
                                <label>Realisasi Biaya</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" name="progress_dampak" required max="100">
                                <label>Progress (%)</label>
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
                                <input type="text" class="form-control" id="timelineImpactInput" name="timeline_dampak" required>
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


@push('scripts')
<script>
    $(document).ready(function() {
        $('#btnTambahDokumenDampak').click(function() {
            const dampakRisikoId = $(this).closest('form').find('input[name="perlakuan_dampak_id"]').val();
            const domCell = $('#table-dampak-risiko tr[data-id="'+dampakRisikoId+'"] td.column-action-impact');
            const domEdited = domCell.find('.dom-edited');

            const uploadContainer = domEdited.find('.upload-container');
            const inputDescription = domEdited.find('.input-file-description');
            const tableDokumen = $('#modalUpdateRealisasiDampak .table-dokumen-dampak');
            const newId = 'dokumen-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

            if (tableDokumen.find('tr').length >= 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Maksimal 3 dokumen yang dapat diunggah.',
                });
                return;
            }

            uploadContainer.append('<input type="file" name="document_dampak_file_' + dampakRisikoId + '[' + newId + ']" id="'+newId+'" required>');

            tableDokumen.append(`
                <tr data-id="${newId}">
                    <td>
                        <span class="dokumen-filename">Pilih file</span>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="deskripsi_dokumen[]" placeholder="Deskripsi dokumen">
                    </td>
                    <td>
                        <button type="button" class="btn btn-link btn-sm text-danger delete-btn">Hapus</button>
                    </td>
                </tr>
            `);

            const appended = tableDokumen.find(`tr[data-id="${newId}"]`);

            domEdited.find(`#${newId}`).change(function() {
                const filename = $(this).prop('files')[0].name;
                appended.find(`.dokumen-filename`).text(filename);

                let totalSize = 0;
                domEdited.find('input[type="file"]').each(function() {
                    if (this.files[0]) {
                        totalSize += this.files[0].size;
                    }
                });

                if (totalSize > {{ config('filesystems.max_upload_size') }} * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Terlalu Besar',
                        text: 'Total ukuran file yang diunggah tidak boleh lebih dari {{ round(config('filesystems.max_upload_size')) }} MB.',
                    });
                    $('#modalUpdateRealisasiDampak .table-dokumen-dampak tr[data-id="'+newId+'"] .delete-btn').click();
                }
            });
            domEdited.find(`#${newId}`).on('cancel', function() {
                appended.find(`.delete-btn`).click();
            });

            domEdited.find(`#${newId}`).click();

            if (tableDokumen.find('tr').length >= 3) {
                tableDokumen.closest('table').find('tfoot').hide();
            }
        });
    });
</script>
@endpush
