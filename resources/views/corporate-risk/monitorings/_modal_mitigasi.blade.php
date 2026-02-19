<div class="modal fade" id="modalMitigasi" tabindex="-1" role="dialog" aria-labelledby="modalMitigasi" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" id="formMitigasi">
                @csrf
                @method('PUT')
                <div class="modal-header d-flex flex-between-center">
                    <h3 class="modal-title h4" id="modalMitigasiLabel">Detail Mitigasi Data</h3>
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
                        <div class="col-12">
                            <h5 class="mt-3 mb-0">Realisasi</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                {{ Form::text('realisasi_biaya_perlakuan_risiko', null, ['class' => 'form-control inputmask-rupiah', 'disabled' => 'disabled']) }}
                                <label>Realisasi Biaya Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                {{ Form::number('progress_perlakuan_risiko', null, ['class' => 'form-control', 'disabled' => 'disabled', 'max' => 100]) }}
                                <label>Progress Perlakuan Risiko</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                {{ Form::textarea('deskripsi_perlakuan_risiko', '', ['class' => 'form-control', 'required', 'rows' => 3, 'disabled' => 'disabled']) }}
                                <label for="deskripsi_perlakuan_risiko">Deskripsi Perlakuan Risiko</label>
                            </div>
                        </div>
                        {{-- <div class="col-12">
                            <div class="form-floating">
                                {{ Form::select('jenis_program_rkap_id', \App\Models\JenisProgramDalamRKAP::pluck('jenis_program_rkap', 'id'), '', ['class' => 'form-select', 'required', 'disabled' => 'disabled']) }}
                                <label for="jenis_program_rkap_id">Jenis Program RKAP</label>
                            </div>
                        </div> --}}
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="timeline_perlakuan_risiko" disabled>
                                <label>Waktu Perlakuan Risiko</label>
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
                            <table class="table tabel-dokumen-mitigasi">
                                <thead>
                                    <tr>
                                        <th scope="col">Dokumen</th>
                                        <th scope="col">Deskripsi</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>