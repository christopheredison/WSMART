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
                        <h2 class="h3">Edit Loss Event Project: {{ $project->project_name }}</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('project-led.update', $lossEvent->id) }}" method="post" id="form-edit-led">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="create_risk_from_led" id="create_risk_from_led_input" value="0">
                        <input type="hidden" name="project_id" value="{{ $lossEvent->project_id }}">

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="nama_kejadian" class="form-label">Nama Kejadian <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="nama_kejadian" rows="3" required>{{ old('nama_kejadian', $lossEvent->nama_kejadian) }}</textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="peristiwa_risiko_id" class="form-label">Identifikasi Kejadian <span class="text-danger">*</span></label>
                                <div class="w-100">
                                  @php
                                      $selectedPeristiwaId = old('peristiwa_risiko_id', $lossEvent->peristiwa_risiko_id);
                                      $manualPeristiwa = old('deskripsi_kejadian', $lossEvent->deskripsi_kejadian);
                                      $hasLegacyPeristiwa = false;
                                      $isOtherPeristiwa = old('peristiwa_risiko_id') === 'other';

                                      if (!$isOtherPeristiwa && (empty($selectedPeristiwaId) || $selectedPeristiwaId == 0) && !empty($manualPeristiwa)) {
                                          $hasLegacyPeristiwa = true;
                                          $selectedPeristiwaId = 'legacy';
                                      }
                                  @endphp
                                  <select class="form-select select2" name="peristiwa_risiko_id" id="peristiwa_risiko_id" required>
                                      <option value="">Pilih Identifikasi Kejadian</option>
                                      @if($hasLegacyPeristiwa)
                                          <option value="legacy" {{ $selectedPeristiwaId == 'legacy' ? 'selected' : '' }}>
                                              {{ $manualPeristiwa }} (Peristiwa Saat Ini)
                                          </option>
                                      @endif
                                      @foreach($peristiwaRisikos as $peristiwa)
                                          <option value="{{ $peristiwa->id }}" @if($selectedPeristiwaId == $peristiwa->id) selected @endif>
                                              {{ $peristiwa->title }}
                                          </option>
                                      @endforeach
                                      <option value="other" {{ $isOtherPeristiwa ? 'selected' : '' }}>Ajukan Peristiwa Lainnya</option>
                                  </select>
                                  @if($hasLegacyPeristiwa)
                                      <div class="alert alert-warning mt-2 mb-2" id="peristiwa-legacy-info">
                                          Peristiwa ini berasal dari data lama. Anda dapat mempertahankannya atau memilih peristiwa yang sudah disetujui Divisi Manajemen Risiko.
                                      </div>
                                  @endif
                                  <div class="alert alert-info mt-2 d-none mb-2" id="peristiwa-other-guide">
                                      Peristiwa ini perlu persetujuan Divisi Manajemen Risiko sebelum bisa digunakan.
                                  </div>
                                  <div class="d-none" id="peristiwa-other-box">
                                    <textarea
                                      class="form-control"
                                      id="peristiwa_risiko_lainnya"
                                      name="deskripsi_kejadian"
                                      rows="3"
                                      placeholder="Masukkan usulan identifikasi kejadian / peristiwa risiko lainnya"
                                    >{{ $isOtherPeristiwa ? $manualPeristiwa : '' }}</textarea>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-submit-peristiwa-lainnya">
                                            <i class="bx bx-send me-1"></i>Ajukan Persetujuan
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="btn-refresh-peristiwa">
                                            <i class="bx bx-refresh me-1"></i>Muat Ulang Pilihan Peristiwa
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-2">Setelah disetujui Divisi Manajemen Risiko, pilih kembali peristiwa dari dropdown di atas.</small>
                                  </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tanggal_kejadian" class="form-label">Tanggal Kejadian <span class="text-danger">*</span></label>
                                <input type="text" class="form-control flatpickr-date bg-white" name="tanggal_kejadian" value="{{ old('tanggal_kejadian', $lossEvent->tanggal_kejadian) }}" required placeholder="Pilih Tanggal Kejadian" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select" name="kategori_kejadian_id" required>
                                    <option value="">Pilih Kategori Kejadian</option>
                                    @foreach($kategoriKejadians as $kategori)
                                        <option value="{{ $kategori->id }}" @if(old('kategori_kejadian_id', $lossEvent->kategori_kejadian_id) == $kategori->id) selected @endif>
                                            {{ $kategori->kategori_kejadian }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sumber Penyebab Kejadian <span class="text-danger">*</span></label>
                                <select class="form-select" name="sumber_penyebab_kejadian" required>
                                    <option value="">Pilih Sumber Penyebab</option>
                                    <option value="1" @if(old('sumber_penyebab_kejadian', $lossEvent->sumber_penyebab_kejadian) == 1) selected @endif>Internal</option>
                                    <option value="2" @if(old('sumber_penyebab_kejadian', $lossEvent->sumber_penyebab_kejadian) == 2) selected @endif>Eksternal</option>
                                </select>
                            </div>

                            {{-- <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Risiko BUMN <span class="text-danger">*</span></label>
                                <select class="form-select" name="kategori_risiko_bumn" required>
                                    <option value="">Pilih Kategori Risiko BUMN</option>
                                    <option value="1" @if(old('kategori_risiko_bumn', $lossEvent->kategori_risiko_bumn) == 1) selected @endif>Financial</option>
                                    <option value="2" @if(old('kategori_risiko_bumn', $lossEvent->kategori_risiko_bumn) == 2) selected @endif>Operational</option>
                                    <option value="3" @if(old('kategori_risiko_bumn', $lossEvent->kategori_risiko_bumn) == 3) selected @endif>Public & Legal</option>
                                </select>
                            </div> --}}

                            {{-- <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori Risiko T2 & T3 BUMN <span class="text-danger">*</span></label>
                                <input type="hidden" name="kategori_risiko_id" id="kategori_risiko_id" value="{{ old('kategori_risiko_id', $lossEvent->kategori_risiko_id) }}">
                                <select class="form-select" name="jenis_risiko_id" id="jenis_risiko_id" required>
                                    <option value="">Pilih Jenis Risiko</option>
                                    @foreach($jenisRisikos as $jenis)
                                        <option value="{{ $jenis->id }}" data-kategori="{{ $jenis->kategori_risiko_id }}" @if(old('jenis_risiko_id', $lossEvent->jenis_risiko_id) == $jenis->id) selected @endif>
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
                                <button type="button" class="btn btn-outline-primary" id="btn-tambah-penyebab">Tambah Penyebab</button>
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
                                    <tbody id="penyebab-risiko-tbody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 mb-3">
                                <label for="penjelasan_kerugian" class="form-label">Penjelasan Kerugian <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="penjelasan_kerugian" rows="3" required>{{ old('penjelasan_kerugian', $lossEvent->penjelasan_kerugian) }}</textarea>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Nilai Kerugian (IDR)</label>
                                <input type="text" class="form-control inputmask-rupiah" name="nilai_kerugian_finansial" value="{{ old('nilai_kerugian_finansial', $lossEvent->nilai_kerugian_finansial) }}">
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <label class="form-label">Kejadian Berulang <span class="text-danger">*</span></label>
                                        <select class="form-select" name="kejadian_berulang" id="kejadian_berulang" required>
                                            <option value="0" @if(old('kejadian_berulang', $lossEvent->kejadian_berulang) == 0) selected @endif>Tidak</option>
                                            <option value="1" @if(old('kejadian_berulang', $lossEvent->kejadian_berulang) == 1) selected @endif>Ya</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-6" id="frekuensi_container" style="display: none;">
                                        <label class="form-label">Frekuensi Kejadian <span class="text-danger">*</span></label>
                                        <select class="form-select" name="frekuensi_kejadian">
                                            <option value="">Pilih</option>
                                            @for($i=1; $i<=5; $i++)
                                                <option value="{{ $i }}" @if(old('frekuensi_kejadian', $lossEvent->frekuensi_kejadian) == $i) selected @endif>{{ $i }} kali per tahun</option>
                                            @endfor
                                            <option value="6" @if(old('frekuensi_kejadian', $lossEvent->frekuensi_kejadian) == 6) selected @endif>6 kali atau lebih per tahun</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status Asuransi <span class="text-danger">*</span></label>
                                <select class="form-select" name="status_asuransi" id="status_asuransi" required>
                                    <option value="0" @if(old('status_asuransi', $lossEvent->status_asuransi) == 0) selected @endif>Tidak</option>
                                    <option value="1" @if(old('status_asuransi', $lossEvent->status_asuransi) == 1) selected @endif>Ya</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-sm-6" id="premi_container" style="display: none;">
                                        <label class="form-label">Nilai Premi (IDR)</label>
                                        <input type="text" class="form-control inputmask-rupiah" name="nilai_premi" value="{{ old('nilai_premi', $lossEvent->nilai_premi) }}">
                                    </div>
                                    <div class="col-sm-6" id="klaim_container" style="display: none;">
                                        <label class="form-label">Nilai Klaim (IDR)</label>
                                        <input type="text" class="form-control inputmask-rupiah" name="nilai_klaim" value="{{ old('nilai_klaim', $lossEvent->nilai_klaim) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="row g-2">
                                <div class="col-auto">
                                    <a href="{{ route('project-led.index-by-project', ['projectId' => $project->id]) }}" class="btn btn-secondary">Batal</a>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary" id="save-led-button">Update Data</button>
                                </div>
                                <div class="col-auto ms-auto">
                                    @if(empty($lossEvent->project_risk_id))
                                        <button type="submit" class="btn btn-danger" id="save-led-risiko-button">Update & Jadikan Risiko</button>
                                    @endif
                                </div>
                            </div>
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
    const submitPeristiwaUrl = @json($project ? route('project-led.peristiwa-lainnya.submit', ['project' => $project->id]) : null);

    $('#peristiwa_risiko_id').on('change', function() {
        const selectedValue = $(this).val();
        const otherTextarea = $('#peristiwa_risiko_lainnya');
        const otherGuide = $('#peristiwa-other-guide');
        const otherBox = $('#peristiwa-other-box');
        const refreshBtn = $('#btn-refresh-peristiwa');
        const legacyInfo = $('#peristiwa-legacy-info');

        if (selectedValue === 'other') {
            legacyInfo.addClass('d-none');
            otherGuide.removeClass('d-none');
            otherBox.removeClass('d-none');
            otherTextarea.removeClass('d-none').attr('required', false);
        } else if (selectedValue === 'legacy') {
            legacyInfo.removeClass('d-none');
            otherGuide.addClass('d-none');
            otherBox.addClass('d-none');
            otherTextarea.addClass('d-none').attr('required', false);
            refreshBtn.addClass('d-none');
        } else {
            legacyInfo.addClass('d-none');
            otherGuide.addClass('d-none');
            otherBox.addClass('d-none');
            otherTextarea.addClass('d-none').attr('required', false).val('');
            refreshBtn.addClass('d-none');
        }
    }).trigger('change');

    $('#btn-submit-peristiwa-lainnya').on('click', function() {
        const value = $('#peristiwa_risiko_lainnya').val().trim();
        const btn = $(this);

        if (!submitPeristiwaUrl) {
            Swal.fire('Gagal', 'Proyek tidak ditemukan untuk pengajuan peristiwa.', 'error');
            return;
        }

        if (!value) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Mohon isi deskripsi peristiwa risiko lainnya terlebih dahulu.',
            });
            return;
        }

        Swal.fire({
            title: 'Ajukan peristiwa ini?',
            html: `Peristiwa <strong>${$('<div>').text(value).html()}</strong> akan dikirim ke Divisi Manajemen Risiko untuk diverifikasi.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Ajukan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            btn.prop('disabled', true);

            $.ajax({
                url: submitPeristiwaUrl,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    title: value,
                },
                success: function(response) {
                    $('#btn-refresh-peristiwa').removeClass('d-none');
                    Swal.fire({
                        icon: 'success',
                        title: 'Pengajuan Berhasil',
                        text: response.message || 'Pengajuan telah dikirim ke MR.',
                    });
                },
                error: function(xhr) {
                    const msg = xhr?.responseJSON?.message || 'Gagal mengirim pengajuan peristiwa. Silakan coba lagi.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Pengajuan Gagal',
                        text: msg,
                    });
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });
    });

    $('#btn-refresh-peristiwa').on('click', function() {
        window.location.reload();
    });

    let penyebabData = @json($penyebabData);

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

    let currentPenyebabId = null;
    let currentPerlakuanId = null;

    function renderPenyebabTable() {
        const tbody = $('#penyebab-risiko-tbody');
        tbody.empty();
        let counter = 1;
        if (penyebabData.length === 0) {
            tbody.html('<tr><td colspan="4" class="text-center">Belum ada data penyebab. Klik "Tambah Penyebab" untuk memulai.</td></tr>');
            $('#penyebab_data_input').val('[]');
            return;
        }
        penyebabData.forEach(penyebab => {
            let perlakuanHtml = '-';
            let picHtml = '-';
            if (penyebab.perlakuan && penyebab.perlakuan.length > 0) {
                perlakuanHtml = '<ul class="list-unstyled mb-0">';
                picHtml = '<ul class="list-unstyled mb-0">';
                penyebab.perlakuan.forEach(p => {
                    perlakuanHtml += `<li class="d-flex justify-content-between align-items-center">
                        ${p.rencana_perlakuan_risiko || ''}
                        <span class="d-flex gap-2">
                            <button type="button" class="btn btn-link p-0 btn-edit-perlakuan" data-penyebab-id="${penyebab.id}" data-perlakuan-id="${p.id}" title="Edit Rencana"><i class="bx bx-edit-alt"></i></button>
                            <button type="button" class="btn btn-link text-danger p-0 btn-hapus-perlakuan" data-penyebab-id="${penyebab.id}" data-perlakuan-id="${p.id}" title="Hapus Rencana"><i class="bx bx-trash"></i></button>
                        </span>
                    </li>`;
                    picHtml += `<li>${p.pic_name || ''}</li>`;
                });
                perlakuanHtml += '</ul>';
                picHtml += '</ul>';
            }
            const row = `<tr class="table">
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
                </tr>`;
            tbody.append(row);
        });
        $('#penyebab_data_input').val(JSON.stringify(penyebabData));
    }

    renderPenyebabTable();
    flatpickr(".flatpickr-date",
      {
        altInput: true,
        altFormat: "d/m/Y",
        dateFormat: "Y-m-d",
        disableMobile: true,
        maxDate: 'today',
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
        penyebabData.push({ id: `temp_${new Date().getTime()}`, penyebab_risiko: penyebabText, perlakuan: [] });
        renderPenyebabTable();
        $('#modalPenyebab').modal('hide');
    });

    $('body').on('click', '.btn-hapus-penyebab', function() {;
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

        const penyebab = penyebabData.find(p => p.id == currentPenyebabId);

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

    // $('body').on('click', '.btn-hapus-perlakuan', function() {
    //     const penyebabId = $(this).data('penyebab-id');
    //     const perlakuanId = $(this).data('perlakuan-id');
    //     const penyebab = penyebabData.find(p => p.id === penyebabId);
    //     penyebab.perlakuan = penyebab.perlakuan.filter(pl => pl.id !== perlakuanId);
    //     renderPenyebabTable();
    // });
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

    // Form Field Handlers
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
        const form = document.getElementById('form-edit-led');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Update Data?',
            text: "Apakah Anda yakin ingin menyimpan perubahan ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#create_risk_from_led_input').val('0');
                form.submit();
            }
        });
    });

    $('#save-led-risiko-button').on('click', function(e) {
        e.preventDefault();
        const form = document.getElementById('form-edit-led');

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
            denyButtonText: 'Tidak, Update LED Saja'
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
