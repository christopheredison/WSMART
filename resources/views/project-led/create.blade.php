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
                        <h2 class="h3">Tambah Loss Event Project: {{ $project->project_name }}</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('project-led.store') }}" method="post" id="form-create-led">
                        @csrf
                        {{-- Hidden input untuk menyimpan pilihan dari sweetalert --}}
                        <input type="hidden" name="create_risk_from_led" id="create_risk_from_led_input" value="0">
                        <input type="hidden" name="project_id" id="project_id" value="{{ $project->id }}">

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="nama_kejadian" class="form-label">Nama Kejadian <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="nama_kejadian" name="nama_kejadian" rows="3" required>{{ old('nama_kejadian') }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="peristiwa_risiko_id" class="form-label">Identifikasi Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select select2" name="peristiwa_risiko_id" id="peristiwa_risiko_id" required>
                                    <option value="">Pilih Identifikasi Kejadian</option>
                                    @foreach($peristiwaRisikos as $risiko)
                                        <option value="{{ $risiko->id }}" {{ old('peristiwa_risiko_id') == $risiko->id ? 'selected' : '' }}>
                                            {{ $risiko->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tanggal_kejadian" class="form-label">Tanggal Kejadian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-date" id="tanggal_kejadian" name="tanggal_kejadian"
                                value="{{ old('tanggal_kejadian') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select select2 js-select-hide-search" name="kategori_kejadian_id">
                                    <option selected disabled>Pilih Kategori Kejadian Kontrol</option>
                                    @foreach($kategoriKejadians as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_kejadian_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->kategori_kejadian }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sumber Penyebab Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select" name="sumber_penyebab_kejadian" required>
                                    <option value="">Pilih Sumber Penyebab</option>
                                    <option value="1" {{ old('sumber_penyebab_kejadian') == '1' ? 'selected' : '' }}>Internal</option>
                                    <option value="2" {{ old('sumber_penyebab_kejadian') == '2' ? 'selected' : '' }}>Eksternal</option>
                                </select>
                            </div>

                            {{-- <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Risiko BUMN <span class="text-danger">*</span></label>
                                <select class="form-select" name="kategori_risiko_bumn" required>
                                    <option value="">Pilih Kategori Risiko BUMN</option>
                                    <option value="1" {{ old('kategori_risiko_bumn') == '1' ? 'selected' : '' }}>Financial</option>
                                    <option value="2" {{ old('kategori_risiko_bumn') == '2' ? 'selected' : '' }}>Operational</option>
                                    <option value="3" {{ old('kategori_risiko_bumn') == '3' ? 'selected' : '' }}>Public & Legal</option>
                                </select>
                            </div> --}}

                            {{-- <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Risiko T2 & T3 BUMN <span class="text-danger">*</span></label>
                                <input type="hidden" name="kategori_risiko_id" id="kategori_risiko_id" value="{{ old('kategori_risiko_id') }}">
                                <select class="form-select select2" name="jenis_risiko_id" id="jenis_risiko_id" required>
                                    <option value="">Pilih Jenis Risiko</option>
                                    @foreach($jenisRisikos as $jenis)
                                        <option value="{{ $jenis->id }}"
                                            data-kategori="{{ $jenis->kategori_risiko_id }}"
                                            {{ old('jenis_risiko_id') == $jenis->id ? 'selected' : '' }}>
                                            {{ $jenis->kategoriRisiko->title ?? '' }} – {{ $jenis->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div> --}}
                        </div>

                        <input type="hidden" name="penyebab_data" id="penyebab_data_input">
                        <div class="card mt-3">
                            <div class="card-header p-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Penyebab dan Penanganan Saat Kejadian</h5>
                                <button type="button" class="btn btn-outline-primary" id="btn-tambah-penyebab">
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
                                <textarea class="form-control" name="penjelasan_kerugian" rows="3" required>{{ old('penjelasan_kerugian') }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Nilai Kerugian (IDR)</label>
                                <input type="text" class="form-control inputmask-rupiah" name="nilai_kerugian_finansial" value="{{ old('nilai_kerugian_finansial') }}">
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="form-label">Kejadian Berulang <span class="text-danger">*</span></label>
                                        <select class="form-select" name="kejadian_berulang" id="kejadian_berulang" required>
                                            <option value="0" @if(old('kejadian_berulang', '0') == '0') selected @endif>Tidak</option>
                                            <option value="1" @if(old('kejadian_berulang') == '1') selected @endif>Ya</option>
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
                                    <option value="0" @if(old('status_asuransi', '0') == '0') selected @endif>Tidak</option>
                                    <option value="1" @if(old('status_asuransi') == '1') selected @endif>Ya</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-sm-6" id="premi_container" style="display: none;">
                                        <label class="form-label">Nilai Premi (IDR)</span></label>
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
                            <a href="{{ route('project-led.index-by-project', ['projectId' => $project->id]) }}" class="btn btn-secondary">Batal</a>
                            <button type="button" class="btn btn-primary" id="save-led-button">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk Tambah Penyebab --}}
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

    {{-- Modal untuk Penanganan Saat Kejadian --}}
    <div class="modal fade" id="modalRencana" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="formRencana">
                    <div class="modal-header"><h5 class="modal-title" id="modalRencanaLabel">Tambah Penanganan Saat Kejadian</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        @include('project-led._form-perencanaan')
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

    let newPenyebabData = [];
    let currentPenyebabId = null;
    let currentPerlakuanId = null;

    function renderPenyebabTable() {
        const tbody = $('#penyebab-risiko-tbody');
        tbody.empty();
        let counter = 1;

        if (newPenyebabData.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">Belum ada data penyebab. Klik "Tambah Penyebab" untuk memulai.</td></tr>');
            $('#penyebab_data_input').val('');
            return;
        }

        newPenyebabData.forEach(penyebab => {
            let perlakuanHtml = '-';
            let picHtml = '-';
            if (penyebab.perlakuan && penyebab.perlakuan.length > 0) {
                perlakuanHtml = '<ul class="list-unstyled mb-0">';
                picHtml = '<ul class="list-unstyled mb-0">';
                penyebab.perlakuan.forEach(p => {
                    perlakuanHtml += `<li class="d-flex justify-content-between align-items-center">
                        ${p.rencana_perlakuan_risiko}
                        <span class="d-flex gap-2">
                            <button type="button" class="btn btn-link p-0 btn-edit-perlakuan" data-penyebab-id="${penyebab.id}" data-perlakuan-id="${p.id}" title="Edit Rencana"><i class="bx bx-edit-alt"></i></button>
                            <button type="button" class="btn btn-link text-danger p-0 btn-hapus-perlakuan" data-penyebab-id="${penyebab.id}" data-perlakuan-id="${p.id}" title="Hapus Rencana"><i class="bx bx-trash"></i></button>
                        </span>
                    </li>`;
                    picHtml += `<li>${p.pic_name}</li>`;
                });
                perlakuanHtml += '</ul>';
                picHtml += '</ul>';
            }

            const row = `
                <tr class="table">
                    <td style="place-content: center;">${counter++}</td>
                    <td class="">
                      <div class="d-flex justify-content-between align-items-center">
                        ${penyebab.penyebab_risiko}
                        <span class="d-flex gap-2">
                            <button type="button" class="btn btn-link p-0 btn-tambah-rencana" data-penyebab-id="${penyebab.id}" title="Tambah Rencana"><i class="bx bx-plus-circle"></i></button>
                            <button type="button" class="btn btn-link text-danger p-0 btn-hapus-penyebab" data-id="${penyebab.id}" title="Hapus Penyebab"><i class="bx bx-trash"></i></button>
                        </span>
                      </div>
                    </td>
                    <td>${perlakuanHtml}</td>
                    <td>${picHtml}</td>
                </tr>
            `;
            tbody.append(row);
        });

        $('#penyebab_data_input').val(JSON.stringify(newPenyebabData));
    }

    renderPenyebabTable();
    flatpickr(".flatpickr-date",
      {
        altInput: true,
        altFormat: "d/m/Y",
        dateFormat: "Y-m-d",
        disableMobile: true,
        maxDate: "today",
      }
    );
    $('.inputmask-rupiah').inputmask({ alias: 'numeric', groupSeparator: '.', autoGroup: true, digits: 0, prefix: 'Rp ', placeholder: '0', rightAlign: false, autoUnmask: true, removeMaskOnSubmit: true });

    $('#btn-tambah-penyebab').on('click', function() {
        $('#modalPenyebabLabel').text('Tambah Penyebab Baru');
        $('#input-penyebab-risiko').val('');
        $('#modalPenyebab').modal('show');
    });

    $('#btn-simpan-penyebab').on('click', function() {
        const penyebabText = $('#input-penyebab-risiko').val();
        if (!penyebabText.trim()) {
            Swal.fire('Gagal', 'Nama penyebab tidak boleh kosong.', 'error');
            return;
        }
        newPenyebabData.push({ id: `temp_${new Date().getTime()}`, penyebab_risiko: penyebabText, perlakuan: [] });
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
                newPenyebabData = newPenyebabData.filter(p => p.id != idToDelete);
                renderPenyebabTable();
                // Swal.fire('Terhapus!', 'Penyebab berhasil dihapus.', 'success');
            }
        });
    });

    $('body').on('click', '.btn-tambah-rencana', function() {
        currentPenyebabId = $(this).data('penyebab-id');
        currentPerlakuanId = null;

        const penyebab = newPenyebabData.find(p => p.id == currentPenyebabId);

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

        const penyebab = newPenyebabData.find(p => p.id == currentPenyebabId);
        const perlakuan = penyebab.perlakuan.find(pl => pl.id == currentPerlakuanId);

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
        };

        const penyebab = newPenyebabData.find(p => p.id == currentPenyebabId);

        if (currentPerlakuanId) {
            const perlakuanIndex = penyebab.perlakuan.findIndex(pl => pl.id == currentPerlakuanId);
            perlakuanData.id = currentPerlakuanId;
            penyebab.perlakuan[perlakuanIndex] = { ...penyebab.perlakuan[perlakuanIndex], ...perlakuanData };
        } else {
            perlakuanData.id = `temp_p_${new Date().getTime()}`;
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
                const penyebab = newPenyebabData.find(p => p.id == penyebabId);
                penyebab.perlakuan = penyebab.perlakuan.filter(pl => pl.id != perlakuanId);
                renderPenyebabTable();
                // Swal.fire('Terhapus!', 'Penanganan Saat Kejadian berhasil dihapus.', 'success');
            }
        });
    });

    $('#jenis_risiko_id').on('change', function() {
        var kategoriId = $(this).find('option:selected').data('kategori');
        $('#kategori_risiko_id').val(kategoriId);
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
        const form = document.getElementById('form-create-led');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Penyimpanan',
            text: "Apakah Loss Event ini akan menjadi Risiko baru di Project?",
            icon: 'question',
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: 'Ya, Jadikan Risiko',
            denyButtonText: `Tidak, Simpan LED Saja`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#create_risk_from_led_input').val('1');
                form.submit();
            } else if (result.isDenied) {
                $('#create_risk_from_led_input').val('0');
                form.submit();
            }
        });
    });
});
</script>
@endpush
