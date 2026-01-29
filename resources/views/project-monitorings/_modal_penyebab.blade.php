<div class="modal fade" id="modalUpdateRealisasi" tabindex="-1" role="dialog" aria-labelledby="modalUpdateRealisasi" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formUpdateRealisasi">
                @csrf
                @method('PUT')
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4" id="modalUpdateRealisasiLabel">Realisasi Perlakuan Risiko</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <!-- Hidden Input for penyebab_risiko_id -->
                        {{ Form::hidden('penyebab_risiko_id', '') }}

                        <div class="col-12">
                            <div class="form-floating">
                                {{ Form::text('penyebab_risiko', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                <label>Penyebab Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="perkiraan_waktu_terpapar_risiko_mulai" name="perkiraan_waktu_terpapar_risiko_mulai" required disabled>
                                <label for="perkiraan_waktu_terpapar_risiko_mulai">Perkiraan Waktu Mulai Terpapar Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="perkiraan_waktu_terpapar_risiko_akhir" name="perkiraan_waktu_terpapar_risiko_akhir" required disabled>
                                <label for="perkiraan_waktu_terpapar_risiko_akhir">Perkiraan Waktu Selesai Terpapar Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                {{ Form::textarea('rencana_perlakuan_risiko', null, ['class' => 'form-control', 'rows' => 3, 'disabled' => 'disabled']) }}
                                <label>Rencana Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                {{ Form::text('biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'disabled' => 'disabled']) }}
                                <label>Biaya Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                {{ Form::text('pic', null, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                <label>PIC</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="timeline_perlakuan_risiko_start" name="timeline_perlakuan_risiko_start" required disabled>
                                <label for="timeline_perlakuan_risiko_start">Waktu Mulai Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="timeline_perlakuan_risiko_end" name="timeline_perlakuan_risiko_end" required disabled>
                                <label for="timeline_perlakuan_risiko_end">Waktu Selesai Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <h5 class="mt-3 mb-0">Realisasi</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                {{ Form::text('realisasi_biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'required' => 'required']) }}
                                <label>Realisasi Biaya Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                {{ Form::number('progress_perlakuan_risiko', null, ['class' => 'form-control', 'required' => 'required', 'max' => 100]) }}
                                <label>Progress Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                {{ Form::textarea('deskripsi_perlakuan_risiko', '', ['class' => 'form-control', 'required', 'rows' => 3, 'required' => 'required']) }}
                                <label for="deskripsi_perlakuan_risiko">Deskripsi Perlakuan Risiko</label>
                            </div>
                        </div>
                        {{-- <div class="col-12">
                            <div class="form-floating">
                                {{ Form::select('jenis_program_rkap_id', \App\Models\JenisProgramDalamRKAP::pluck('jenis_program_rkap', 'id'), '', ['class' => 'form-select', 'required']) }}
                                <label for="jenis_program_rkap_id">Jenis Program RKAP</label>
                            </div>
                        </div> --}}
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="timelineInput" name="timeline_perlakuan_risiko" required>
                                <label for="timelineInput">Waktu Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <h5 class="mt-3 mb-0">Dokumen</h5>
                        </div>
                        <div class="col-md-3 col-auto text-end justify-content-end d-flex flex-column">
                            <div>

                            </div>
                        </div>
                        <div class="col-12">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Dokumen</th>
                                        <th scope="col">Deskripsi</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="table-dokumen">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-center"><button type="button" class="btn btn-link btn-sm py-1" id="btnTambahDokumen">Tambah Dokumen</button></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanUpdateRealisasi">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btnTambahDokumen').click(function() {
            const penyebabRisikoId = $(this).closest('form').find('input[name="penyebab_risiko_id"]').val();
            const domCell = $('#table-penyebab-risiko tr[data-id="'+penyebabRisikoId+'"] td.column-action');
            const domEdited = domCell.find('.dom-edited');

            const uploadContainer = domEdited.find('.upload-container');
            const inputDescription = domEdited.find('.input-file-description');
            const tableDokumen = $('#modalUpdateRealisasi .table-dokumen');
            const newId = 'dokumen-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

            if (tableDokumen.find('tr').length >= 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Maksimal 3 dokumen yang dapat diunggah.',
                });
                // alert('Maksimal 3 dokumen yang dapat diunggah.');
                return;
            }

            uploadContainer.append('<input type="file" name="document_file_' + penyebabRisikoId + '[' + newId + ']" id="'+newId+'" required>');

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
                    // alert('Total ukuran file yang diunggah tidak boleh lebih dari {{ round(config('filesystems.max_upload_size')) }} MB.');
                    $('#modalUpdateRealisasi .table-dokumen tr[data-id="'+newId+'"] .delete-btn').click();
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
