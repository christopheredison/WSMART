@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="lead__icon bg-warning-subtle">
                            <div class="svg-icon svg-icon-warning">@include('partials.icon-tool')</div>
                        </div>
                        <h2 class="h3">Ubah Risiko Menjadi Loss Event Divisi</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('risk-register-unit.loss-events.store', ['riskRegister' => $risiko->id]) }}" method="post" id="form-change-to-led">
                        @csrf
                        <input type="hidden" name="is_closed" id="is_closed_input" value="0">
                        <input type="hidden" name="create_new_risk" id="create_new_risk_input" value="0">

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="nama_kejadian" class="form-label">Nama Kejadian <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="nama_kejadian" name="nama_kejadian" rows="3" required>{{ $risiko->peristiwa_risiko }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="peristiwa_risiko_id" class="form-label">Identifikasi Kejadian <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="identifikasi_kejadian" name="identifikasi_kejadian" rows="3" required>{{ $risiko->deskripsi_peristiwa_risiko }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tanggal_kejadian" class="form-label">Tanggal Kejadian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-white" id="tanggal_kejadian" name="tanggal_kejadian" 
                                      value="{{ \Carbon\Carbon::parse($risiko->perkiraan_waktu_terpapar_risiko_mulai)->format('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="kategori_kejadian_id" required>
                                    <option value="">Pilih Kategori Kejadian</option>
                                    @foreach($kategoriKejadians as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_kejadian_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->kategori_kejadian }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sumber Penyebab Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="sumber_penyebab_kejadian" required>
                                    <option value="">Pilih Sumber Penyebab</option>
                                    <option value="1" {{ old('sumber_penyebab_kejadian') == '1' ? 'selected' : '' }}>Internal</option>
                                    <option value="2" {{ old('sumber_penyebab_kejadian') == '2' ? 'selected' : '' }}>Eksternal</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Risiko BUMN <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="kategori_risiko_bumn" required>
                                    <option value="">Pilih Kategori Risiko BUMN</option>
                                    <option value="1" {{ old('kategori_risiko_bumn') == '1' ? 'selected' : '' }}>Financial</option>
                                    <option value="2" {{ old('kategori_risiko_bumn') == '2' ? 'selected' : '' }}>Operational</option>
                                    <option value="3" {{ old('kategori_risiko_bumn') == '3' ? 'selected' : '' }}>Public & Legal</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Risiko T2 & T3 BUMN <span class="text-danger">*</span></label>
                                
                                @if($risiko->jenis_risiko_id)
                                    <select class="form-select" required disabled>
                                        <option selected>{{ $risiko->jenisRisiko->kategoriRisiko->title ?? '' }} – {{ $risiko->jenisRisiko->title ?? '' }}</option>
                                    </select>
                                    <input type="hidden" name="kategori_risiko_id" value="{{ $risiko->jenisRisiko->kategori_risiko_id ?? '' }}">
                                    <input type="hidden" name="jenis_risiko_id" value="{{ $risiko->jenis_risiko_id }}">

                                @else
                                    <input type="hidden" name="kategori_risiko_id" id="kategori_risiko_id_dynamic">
                                    <select class="form-select select2" name="jenis_risiko_id" id="jenis_risiko_id_dynamic" required>
                                        <option value="">Pilih Jenis Risiko...</option>
                                        @foreach($jenisRisikos as $jenis)
                                            <option value="{{ $jenis->id }}" data-kategori="{{ $jenis->kategori_risiko_id }}">
                                                {{ $jenis->kategoriRisiko->title ?? '' }} – {{ $jenis->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>

                        <input type="hidden" name="penyebab_data" id="penyebab_data_input">
                        <div class="card mt-3">
                            <div class="card-header p-3 d-flex justify-content-between align-items-center">
                              <h5 class="mb-0">Penyebab dan Penanganan Saat Kejadian</h5>
                              <button type="button" class="btn btn-outline-primary" id="btn-tambah-penyebab">
                                  <span class="bx bx-plus"></span> 
                                  Tambah Penyebab
                              </button>
                          </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;">NO</th>
                                            <th>Penyebab</th>
                                            <th>Penanganan Saat Kejadian</th>
                                            <th style="width: 200px;">PIC</th>
                                            <th style="width: 50px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="penyebab-risiko-tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12 mb-3">
                                <label for="penjelasan_kerugian" class="form-label">Penjelasan Kerugian <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="penjelasan_kerugian" rows="3" required>@if($risiko->riskAnalysis?->kategori_dampak == 'Kuantitatif'){{ $risiko->riskAnalysis->asumsi_perhitungan_dampak }}@else{{ $risiko->riskAnalysis?->deskripsi_dampak }}@endif</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Nilai Kerugian (IDR)</label>
                                <input type="text" class="form-control inputmask-rupiah" name="nilai_kerugian_finansial"
                                      value="@if($risiko->riskAnalysis?->kategori_dampak == 'Kuantitatif'){{ $risiko->riskAnalysis->nilai_dampak }}@else{{ 0 }}@endif">
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="form-label">Kejadian Berulang <span class="text-danger">*</span></label>
                                        <select class="form-select" name="kejadian_berulang" id="kejadian_berulang" required>
                                            <option value="0" {{ old('kejadian_berulang') == '0' ? 'selected' : '' }}>Tidak</option>
                                            <option value="1" {{ old('kejadian_berulang') == '1' ? 'selected' : '' }}>Ya</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6" id="frekuensi_container" style="display: none;">
                                        <label class="form-label">Frekuensi Kejadian <span class="text-danger">*</span></label>
                                        <select class="form-select" name="frekuensi_kejadian">
                                            <option value="">Pilih</option>
                                            @for($i=1; $i<=5; $i++)
                                                <option value="{{ $i }}" {{ old('frekuensi_kejadian') == $i ? 'selected' : '' }}>{{ $i }} kali per tahun</option>
                                            @endfor
                                            <option value="6" {{ old('frekuensi_kejadian') == '6' ? 'selected' : '' }}>6 kali atau lebih per tahun</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Asuransi <span class="text-danger">*</span></label>
                                <select class="form-select" name="status_asuransi" id="status_asuransi" required>
                                    <option value="0" {{ old('status_asuransi') == '0' ? 'selected' : '' }}>Tidak</option>
                                    <option value="1" {{ old('status_asuransi') == '1' ? 'selected' : '' }}>Ya</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-sm-6" id="premi_container" style="display: none;">
                                        <label class="form-label">Nilai Premi (IDR)</label>
                                        <input type="text" class="form-control inputmask-rupiah" name="nilai_premi" value="{{ old('nilai_premi') }}">
                                    </div>
                                    <div class="col-sm-6" id="klaim_container" style="display: none;">
                                        <label class="form-label">Nilai Klaim (IDR)</label>
                                        <input type="text" class="form-control inputmask-rupiah" name="nilai_klaim" value="{{ old('nilai_klaim') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <a href="{{ route('risk-register-unit.monitorings.index', ['period' => $risiko->periode_id]) }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" id="save-led-button">Simpan LED</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPenyebab" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="modalPenyebabLabel">Tambah Penyebab Baru</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="form-floating"><input type="text" class="form-control" id="input-penyebab-risiko" placeholder="Masukkan penyebab risiko" required><label for="input-penyebab-risiko">Nama Penyebab</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="btn-simpan-penyebab">Simpan</button></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRencana" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formRencana">
                    <div class="modal-header"><h5 class="modal-title" id="modalRencanaLabel">Tambah Penanganan Saat Kejadian</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        @include('unit-led._form-perencanaan')
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="button" class="btn btn-primary" id="btn-simpan-rencana">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(document).ready(function() {
    flatpickr("#tanggal_kejadian", {
        altInput: true,
        altFormat: "d/m/Y",
        dateFormat: "Y-m-d",
        disableMobile: true,
        maxDate: "today",
    });

    var flatpickrMulai = flatpickr("#timelineRange1", {
        altInput: true,
        altFormat: "d/m/Y",
        dateFormat: "d/m/Y",
        disableMobile: true
    });

    var flatpickrSelesai = flatpickr("#timelineRange2", {
        altInput: true,
        altFormat: "d/m/Y",
        dateFormat: "d/m/Y",
        disableMobile: true
    });

    let penyebabData = @json($penyebabData ?? []);
    let currentPenyebabId = null;
    let currentPerlakuanId = null;
    
    function renderPenyebabTable() {
        const tbody = $('#penyebab-risiko-tbody');
        tbody.empty();
        let counter = 1;

        if (penyebabData.length === 0) {
            tbody.html('<tr><td colspan="5" class="text-center">Belum ada data penyebab. Klik "Tambah Penyebab" untuk memulai.</td></tr>');
            $('#penyebab_data_input').val('[]');
            return;
        }
    
        penyebabData.forEach(penyebab => {
            const perlakuanList = penyebab.perlakuan || [];
            const rowspanCount = perlakuanList.length > 0 ? perlakuanList.length : 1;

            const noCell = `<td class="align-middle text-center" rowspan="${rowspanCount}">${counter}</td>`;
            const penyebabCell = `<td class="align-middle" rowspan="${rowspanCount}">${penyebab.penyebab_risiko}</td>`;
            
            const actionMenu = `
                <div class="dropdown">
                    <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-toggle="tooltip" title="Pilihan Aksi">
                        <i class="bx bx-dots-vertical-rounded fs-4"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item btn-edit-penyebab" href="#" data-id="${penyebab.id}"><i class="bx bx-edit-alt me-2"></i>Edit Penyebab</a>
                        <a class="dropdown-item text-danger btn-hapus-penyebab" href="#" data-id="${penyebab.id}"><i class="bx bx-trash me-2"></i>Hapus Penyebab</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item btn-tambah-rencana" href="#" data-penyebab-id="${penyebab.id}"><i class="bx bx-plus me-2"></i>Tambah Penanganan</a>
                    </div>
                </div>`;
            const actionCell = `<td class="align-middle text-center" rowspan="${rowspanCount}">${actionMenu}</td>`;
            
            let rowsHtml = '';

            if (perlakuanList.length > 0) {
                perlakuanList.forEach((perlakuan, index) => {
                    const perlakuanActionButtons = `
                        <span class="d-flex gap-2">
                            <button type="button" class="btn btn-link p-0 btn-edit-perlakuan" data-penyebab-id="${penyebab.id}" data-perlakuan-id="${perlakuan.id}" data-bs-toggle="tooltip" title="Edit Penanganan"><i class="bx bx-edit-alt"></i></button>
                            <button type="button" class="btn btn-link text-danger p-0 btn-hapus-perlakuan" data-penyebab-id="${penyebab.id}" data-perlakuan-id="${perlakuan.id}" data-bs-toggle="tooltip" title="Hapus Penanganan"><i class="bx bx-trash"></i></button>
                        </span>`;

                    const perlakuanCellContent = `<div class="d-flex justify-content-between align-items-center">${perlakuan.rencana_perlakuan_risiko || ''}${perlakuanActionButtons}</div>`;
                    const picCellContent = perlakuan.pic_name || '-';

                    if (index === 0) {
                        rowsHtml += `<tr>
                            ${noCell}
                            ${penyebabCell}
                            <td class="align-middle">${perlakuanCellContent}</td>
                            <td class="align-middle">${picCellContent}</td>
                            ${actionCell}
                        </tr>`;
                    } else {
                        rowsHtml += `<tr>
                            <td class="align-middle">${perlakuanCellContent}</td>
                            <td class="align-middle">${picCellContent}</td>
                        </tr>`;
                    }
                });
            } else {
                const tambahPerlakuanBtnHtml = `
                    <button type="button" class="btn btn-outline-info btn-sm btn-tambah-rencana" 
                            data-penyebab-id="${penyebab.id}" data-bs-toggle="tooltip" title="Tambah Penanganan Baru untuk penyebab ini">
                        <i class="bx bx-plus"></i> Tambah Penanganan
                    </button>`;

                rowsHtml = `<tr>
                    ${noCell}
                    ${penyebabCell}
                    <td class="align-middle text-center">${tambahPerlakuanBtnHtml}</td>
                    <td class="align-middle text-center">-</td>
                    ${actionCell}
                </tr>`;
            }
            
            tbody.append(rowsHtml);
            counter++;
        });

        $('#penyebab_data_input').val(JSON.stringify(penyebabData));

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    renderPenyebabTable();
    $('.inputmask-rupiah').inputmask({
        alias: 'numeric', groupSeparator: '.', autoGroup: true, digits: 0,
        prefix: 'Rp ', placeholder: '0', rightAlign: false,
        autoUnmask: true, removeMaskOnSubmit: true,
    });

    $('#btn-tambah-penyebab').on('click', function() {
        $('#modalPenyebabLabel').text('Tambah Penyebab Baru');
        $('#input-penyebab-risiko').val('');
        $('#btn-simpan-penyebab').data('mode', 'add');
        $('#modalPenyebab').modal('show');
    });

    $('body').on('click', '.btn-edit-penyebab', function() {
        const idToEdit = $(this).data('id');
        const penyebab = penyebabData.find(p => p.id == idToEdit);
        if (penyebab) {
            $('#modalPenyebabLabel').text('Edit Penyebab');
            $('#input-penyebab-risiko').val(penyebab.penyebab_risiko);
            $('#btn-simpan-penyebab').data('mode', 'edit').data('id', idToEdit);
            $('#modalPenyebab').modal('show');
        }
    });

    $('#btn-simpan-penyebab').on('click', function() {
        const penyebabText = $('#input-penyebab-risiko').val();
        if (!penyebabText.trim()) { 
            Swal.fire('Gagal', 'Nama penyebab tidak boleh kosong.', 'error');
            return; 
        }

        const mode = $(this).data('mode');
        if (mode === 'edit') {
            const idToUpdate = $(this).data('id');
            const penyebabIndex = penyebabData.findIndex(p => p.id == idToUpdate);
            if (penyebabIndex > -1) {
                penyebabData[penyebabIndex].penyebab_risiko = penyebabText;
            }
        } else {
            penyebabData.push({ 
                id: `temp_${new Date().getTime()}`, 
                penyebab_risiko: penyebabText, 
                perlakuan: []
            });
        }

        renderPenyebabTable();
        $('#modalPenyebab').modal('hide');
    });

    $('body').on('click', '.btn-hapus-penyebab', function() {
        const idToDelete = $(this).data('id');
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda akan menghapus penyebab ini beserta semua Penanganan Saat Kejadiannya!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                penyebabData = penyebabData.filter(p => p.id != idToDelete);
                renderPenyebabTable();
                // Swal.fire('Terhapus!', 'Penyebab berhasil dihapus.', 'success');
            }
        });
    });

    $('body').on('click', '.btn-tambah-rencana', function() {
        currentPenyebabId = $(this).data('penyebab-id');
        currentPerlakuanId = null;

        const penyebab = penyebabData.find(p => p.id == currentPenyebabId);
        if (!penyebab) return;
        
        $('#modalRencanaLabel').text('Tambah Penanganan Saat Kejadian');
        
        const form = $('#formRencana');
        form[0].reset();
        form.find('select').val('').trigger('change');
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('[name="penyebab_risiko_text"]').val(penyebab.penyebab_risiko);
        
        flatpickrMulai.clear();
        flatpickrSelesai.clear();
        
        $('#modalRencana').modal('show');
    });

    $('body').on('click', '.btn-edit-perlakuan', function() {
        currentPenyebabId = $(this).data('penyebab-id');
        currentPerlakuanId = $(this).data('perlakuan-id');
        
        const penyebab = penyebabData.find(p => p.id == currentPenyebabId);
        const perlakuan = penyebab ? penyebab.perlakuan.find(pl => pl.id == currentPerlakuanId) : null;
        if (!perlakuan) return; 
        
        const form = $('#formRencana');
        form.find('.is-invalid').removeClass('is-invalid');
        
        $('#modalRencanaLabel').text('Edit Penanganan Saat Kejadian');
        
        form.find('[name="penyebab_risiko_text"]').val(penyebab.penyebab_risiko);
        form.find('[name="rencana_perlakuan_risiko"]').val(perlakuan.rencana_perlakuan_risiko);
        form.find('[name="output_perlakuan_risiko"]').val(perlakuan.output_perlakuan_risiko);
        form.find('[name="biaya_perlakuan_risiko"]').val(perlakuan.biaya_perlakuan_risiko);
        form.find('[name="pic"]').val(perlakuan.pic).trigger('change');
        form.find('[name="opsi_perlakuan_risiko"]').val(perlakuan.opsi_perlakuan_risiko);
        form.find('[name="jenis_rencana_perlakuan_risiko"]').val(perlakuan.jenis_rencana_perlakuan_risiko);
        
        flatpickrMulai.setDate(perlakuan.timeline_mulai_perlakuan_risiko, true, 'd/m/Y');
        flatpickrSelesai.setDate(perlakuan.timeline_selesai_perlakuan_risiko, true, 'd/m/Y');
        
        $('#modalRencana').modal('show');
    });
    
    $('#btn-simpan-rencana').on('click', function() {
        const form = $('#formRencana');
        let isValid = true;
        let errorMessages = [];

        form.find('.is-invalid').removeClass('is-invalid');

        form.find('input[required], textarea[required], select[required]').each(function() {
            if (!$(this).val() || $(this).val().trim() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
                const label = $(this).closest('.form-floating').find('label').text().replace('*', '').trim();
                errorMessages.push(label);
            }
        });

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Form Tidak Lengkap',
                html: 'Harap isi semua bidang yang wajib diisi:<br><ul class="text-start mt-2">' + errorMessages.map(e => `<li>${e}</li>`).join('') + '</ul>',
            });
            return;
        }
        
        const perlakuanData = {
            rencana_perlakuan_risiko: form.find('[name="rencana_perlakuan_risiko"]').val(),
            output_perlakuan_risiko: form.find('[name="output_perlakuan_risiko"]').val(),
            biaya_perlakuan_risiko: form.find('[name="biaya_perlakuan_risiko"]').val(),
            pic: form.find('[name="pic"]').val(),
            pic_name: form.find('[name="pic"] option:selected').text().trim(),
            timeline_mulai_perlakuan_risiko: form.find('[name="timeline_mulai_perlakuan_risiko"]').val(),
            timeline_selesai_perlakuan_risiko: form.find('[name="timeline_selesai_perlakuan_risiko"]').val(),
            opsi_perlakuan_risiko: form.find('[name="opsi_perlakuan_risiko"]').val(),
            jenis_rencana_perlakuan_risiko: form.find('[name="jenis_rencana_perlakuan_risiko"]').val(),
            is_original: false
        };
        
        const penyebab = penyebabData.find(p => p.id == currentPenyebabId);

        if (currentPerlakuanId) {
            const perlakuanIndex = penyebab.perlakuan.findIndex(pl => pl.id == currentPerlakuanId);
            penyebab.perlakuan[perlakuanIndex] = { ...penyebab.perlakuan[perlakuanIndex], ...perlakuanData };
        } else {
            perlakuanData.id = `temp_p_${new Date().getTime()}`;
            if (!penyebab.perlakuan) {
                penyebab.perlakuan = [];
            }
            penyebab.perlakuan.push(perlakuanData);
        }
        
        renderPenyebabTable();
        $('#modalRencana').modal('hide');
    });

    $('body').on('click', '.btn-hapus-perlakuan', function() {
        const penyebabId = $(this).data('penyebab-id');
        const perlakuanId = $(this).data('perlakuan-id');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda akan menghapus Penanganan Saat Kejadian ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const penyebab = penyebabData.find(p => p.id == penyebabId);
                penyebab.perlakuan = penyebab.perlakuan.filter(pl => pl.id != perlakuanId);
                renderPenyebabTable();
                // Swal.fire('Terhapus!', 'Penanganan Saat Kejadian berhasil dihapus.', 'success');
            }
        });
    });

    
    $('#jenis_risiko_id_dynamic').on('change', function() {
        var kategoriId = $(this).find('option:selected').data('kategori');
        $('#kategori_risiko_id_dynamic').val(kategoriId);
    });

    $('#kejadian_berulang').on('change', function() {
        if (this.value == '1') {
            $('#frekuensi_container').slideDown();
            $('#frekuensi_container select').prop('required', true);
        } else {
            $('#frekuensi_container').slideUp();
            $('#frekuensi_container select').prop('required', false);
        }
    }).trigger('change');

    $('#status_asuransi').on('change', function() {
        if (this.value == '1') {
            $('#premi_container, #klaim_container').slideDown();
        } else {
            $('#premi_container, #klaim_container').slideUp();
        }
    }).trigger('change');

    
    $('#save-led-button').on('click', function(e) {
      e.preventDefault();
      const form = document.getElementById('form-change-to-led');
  
      if (!form.checkValidity()) {
          form.reportValidity();
          return;
      }
  
      Swal.fire({
          title: 'Apakah Risiko akan di-close?',
          text: "Memilih 'Ya' akan menutup risiko ini setelah LED dibuat.",
          icon: 'question',
          showDenyButton: true,
          showCancelButton: false,
          confirmButtonText: 'Ya, Close Risiko',
          denyButtonText: `Tidak`,
          // cancelButtonText: 'Batal'
      }).then((result) => {
          if (result.isConfirmed) {
              // User chose "Ya, Close Risiko"
              document.getElementById('is_closed_input').value = '1';
              form.submit();
  
          } else if (result.isDenied) {
              // User chose "Tidak"
              Swal.fire({
                  title: 'Apakah menjadi Penyebab Risiko Baru?',
                  text: "Ini akan mengarahkan Anda ke halaman tambah risiko baru dengan beberapa data terisi.",
                  icon: 'warning',
                  showDenyButton: true,
                  showCancelButton: false,
                  confirmButtonText: 'Ya, Risiko Baru',
                  denyButtonText: 'Tidak, Buat LED Saja',
                  cancelButtonText: 'Batal'
              }).then((result2) => {
                  if (result2.isConfirmed) {
                      // User memilih "Ya, jadi Risiko Baru"
                      // Tandai form untuk memberitahu controller agar me-redirect ke halaman create risk
                      document.getElementById('create_new_risk_input').value = '1';
                      document.getElementById('is_closed_input').value = '0'; // Pastikan is_closed tidak diset
                      form.submit(); // Submit form untuk menyimpan LED terlebih dahulu
                  } else if (result2.isDenied) {
                      // User chose "Tidak, Buat LED Saja" (by clicking outside or X)
                      document.getElementById('is_closed_input').value = '0';
                      form.submit();
                  }
              });
          }
      });
    });
});
</script>
@endpush