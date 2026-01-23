@extends('layouts.default')
@section('dashboard')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <h5 class="mb-0">Pelaksanaan Pengujian ICT Plan</h5>
      </div>
      <div class="card-body">
        <form id="ictDoForm" action="{{ route('ict.store-testing', $ictPlan->id) }}" method="POST">
          @csrf
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Sasaran BUMN</label>
                <input type="text" class="form-control" value="{{ $ictPlan->sasaran_bumn }}" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Peristiwa Risiko</label>
                <input type="text" class="form-control" value="{{ $peristiwaRisiko }}" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Lokasi Risiko</label>
                <input type="text" class="form-control" value="{{ $lokasiRisiko }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Business Process</label>
                <input type="text" class="form-control" value="{{ $ictPlan->business_process }}" readonly>
              </div>
              <div class="mb-3">
                <label class="form-label">Metode Pengujian</label>
                <input type="text" class="form-control" value="{{ $ictPlan->metode_pengujian }}" readonly>
              </div>
            </div>
          </div>

          <!-- Key Controls -->
          @foreach($ictPlan->planControls as $index => $planControl)

          @php
            $do = $planControl->latestDo;
          @endphp

          <div class="key-control-section mb-4" data-index="{{ $index }}">
            <div class="card">
              <div class="card-header bg-light">
                <h5 class="mb-0">Key Control #{{ $index + 1 }}</h5>
              </div>
              <div class="card-body">
                <input type="hidden" name="plan_control_id[]" value="{{ $planControl->id }}">

                <!-- Key Control Info -->
                <div class="mb-4">
                  <label class="form-label fw-bold">Key Control</label>
                  <input type="text" class="form-control" value="{{ $planControl->key_control }}" readonly>
                </div>

                <!-- Jenis, Bentuk, Level Kontrol -->
                <div class="row mb-4">
                  <div class="col-md-4">
                    <label class="form-label">Jenis Kontrol <span class="text-danger">*</span></label>
                    <select name="jenis_kontrol[]" class="form-select" required>
                      <option value="">Pilih Jenis Kontrol</option>
                      <option value="1" {{ old('jenis_kontrol.'.$index, $do?->jenis_kontrol) == 1 ? 'selected' : '' }}>Kontrol Operasi</option>
                      <option value="2" {{ old('jenis_kontrol.'.$index, $do?->jenis_kontrol) == 2 ? 'selected' : '' }}>Kontrol Kepatuhan</option>
                      <option value="3" {{ old('jenis_kontrol.'.$index, $do?->jenis_kontrol) == 3 ? 'selected' : '' }}>Kontrol Pelaporan</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Bentuk Kontrol <span class="text-danger">*</span></label>
                    <select name="bentuk_kontrol[]" class="form-select" required>
                      <option value="">Pilih Bentuk Kontrol</option>
                      <option value="1" {{ old('bentuk_kontrol.'.$index, $do?->bentuk_kontrol) == 1 ? 'selected' : '' }}>SOP</option>
                      <option value="2" {{ old('bentuk_kontrol.'.$index, $do?->bentuk_kontrol) == 2 ? 'selected' : '' }}>Kebijakan</option>
                      <option value="3" {{ old('bentuk_kontrol.'.$index, $do?->bentuk_kontrol) == 3 ? 'selected' : '' }}>Sistem Informasi dan Komunikasi</option>
                      <option value="4" {{ old('bentuk_kontrol.'.$index, $do?->bentuk_kontrol) == 4 ? 'selected' : '' }}>Sistem Lainnya</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Level Pengendalian <span class="text-danger">*</span></label>
                    <select name="level_pengendalian[]" class="form-select" required>
                      <option value="">Pilih Level Pengendalian</option>
                      <option value="1" {{ old('level_pengendalian.'.$index, $do?->level_pengendalian) == 1 ? 'selected' : '' }}>Entitas</option>
                      <option value="2" {{ old('level_pengendalian.'.$index, $do?->level_pengendalian) == 2 ? 'selected' : '' }}>Operasional</option>
                    </select>
                  </div>
                </div>

                <!-- Kecukupan Desain Pengendalian -->
                <div class="mb-4">
                  <h6 class="mb-3 border-bottom pb-2">Kecukupan Desain Pengendalian</h6>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Efektivitas pelaksanaan pengendalian dalam mencapai tujuan pengendalian intern <span class="text-danger">*</span></label>
                      <select name="kecukupan_desain_pengendalian_1[]" class="form-select" required>
                        <option value="">Pilih Kecukupan</option>
                        <option value="1" {{ old('kecukupan_desain_pengendalian_1.'.$index, $do?->kecukupan_desain_pengendalian_1) == 1 ? 'selected' : '' }}>Cukup</option>
                        <option value="2" {{ old('kecukupan_desain_pengendalian_1.'.$index, $do?->kecukupan_desain_pengendalian_1) == 2 ? 'selected' : '' }}>Tidak Cukup</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Kecukupan desain pengendalian dalam memitigasi risiko <span class="text-danger">*</span></label>
                      <select name="kecukupan_desain_pengendalian_2[]" class="form-select" required>
                        <option value="">Pilih Kecukupan</option>
                        <option value="1" {{ old('kecukupan_desain_pengendalian_2.'.$index, $do?->kecukupan_desain_pengendalian_2) == 1 ? 'selected' : '' }}>Cukup</option>
                        <option value="2" {{ old('kecukupan_desain_pengendalian_2.'.$index, $do?->kecukupan_desain_pengendalian_2) == 2 ? 'selected' : '' }}>Tidak Cukup</option>
                      </select>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Kecukupan dokumentasi yang memadai untuk pengendalian <span class="text-danger">*</span></label>
                      <select name="kecukupan_desain_pengendalian_3[]" class="form-select" required>
                        <option value="">Pilih Kecukupan</option>
                        <option value="1" {{ old('kecukupan_desain_pengendalian_3.'.$index, $do?->kecukupan_desain_pengendalian_3) == 1 ? 'selected' : '' }}>Cukup</option>
                        <option value="2" {{ old('kecukupan_desain_pengendalian_3.'.$index, $do?->kecukupan_desain_pengendalian_3) == 2 ? 'selected' : '' }}>Tidak Cukup</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Keberadaan risiko perusahaan yang belum termitigasi <span class="text-danger">*</span></label>
                      <select name="kecukupan_desain_pengendalian_4[]" class="form-select" required>
                        <option value="">Pilih Kecukupan</option>
                        <option value="1" {{ old('kecukupan_desain_pengendalian_4.'.$index, $do?->kecukupan_desain_pengendalian_4) == 1 ? 'selected' : '' }}>Cukup</option>
                        <option value="2" {{ old('kecukupan_desain_pengendalian_4.'.$index, $do?->kecukupan_desain_pengendalian_4) == 2 ? 'selected' : '' }}>Tidak Cukup</option>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label class="form-label">Kesimpulan Kecukupan Desain Pengendalian <span class="text-danger">*</span></label>
                      <select name="kecukupan_desain_pengendalian_akhir[]" class="form-select" required>
                        <option value="">Pilih Kesimpulan</option>
                        <option value="1" {{ old('kecukupan_desain_pengendalian_akhir.'.$index, $do?->kecukupan_desain_pengendalian_akhir) == 1 ? 'selected' : '' }}>Cukup</option>
                        <option value="2" {{ old('kecukupan_desain_pengendalian_akhir.'.$index, $do?->kecukupan_desain_pengendalian_akhir) == 2 ? 'selected' : '' }}>Tidak Cukup</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Efektivitas Desain Pengendalian -->
                <div class="mb-4">
                  <h6 class="mb-3 border-bottom pb-2">Efektivitas Desain Pengendalian</h6>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Efektivitas pelaksanaan pengendalian dalam mencapai tujuan <span class="text-danger">*</span></label>
                      <select name="efektivitas_desain_pengendalian_1[]" class="form-select" required>
                        <option value="">Pilih Efektivitas</option>
                        <option value="1" {{ old('efektivitas_desain_pengendalian_1.'.$index, $do?->efektivitas_desain_pengendalian_1) == 1 ? 'selected' : '' }}>Efektif</option>
                        <option value="2" {{ old('efektivitas_desain_pengendalian_1.'.$index, $do?->efektivitas_desain_pengendalian_1) == 2 ? 'selected' : '' }}>Efektif Sebagian</option>
                        <option value="3" {{ old('efektivitas_desain_pengendalian_1.'.$index, $do?->efektivitas_desain_pengendalian_1) == 3 ? 'selected' : '' }}>Tidak Efektif</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Konsistensi pelaksanaan pengendalian di lapangan <span class="text-danger">*</span></label>
                      <select name="efektivitas_desain_pengendalian_2[]" class="form-select" required>
                        <option value="">Pilih Efektivitas</option>
                        <option value="1" {{ old('efektivitas_desain_pengendalian_2.'.$index, $do?->efektivitas_desain_pengendalian_2) == 1 ? 'selected' : '' }}>Efektif</option>
                        <option value="2" {{ old('efektivitas_desain_pengendalian_2.'.$index, $do?->efektivitas_desain_pengendalian_2) == 2 ? 'selected' : '' }}>Efektif Sebagian</option>
                        <option value="3" {{ old('efektivitas_desain_pengendalian_2.'.$index, $do?->efektivitas_desain_pengendalian_2) == 3 ? 'selected' : '' }}>Tidak Efektif</option>
                      </select>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Kesesuaian pelaksanaan tugas manajemen dan karyawan <span class="text-danger">*</span></label>
                      <select name="efektivitas_desain_pengendalian_3[]" class="form-select" required>
                        <option value="">Pilih Efektivitas</option>
                        <option value="1" {{ old('efektivitas_desain_pengendalian_3.'.$index, $do?->efektivitas_desain_pengendalian_3) == 1 ? 'selected' : '' }}>Efektif</option>
                        <option value="2" {{ old('efektivitas_desain_pengendalian_3.'.$index, $do?->efektivitas_desain_pengendalian_3) == 2 ? 'selected' : '' }}>Efektif Sebagian</option>
                        <option value="3" {{ old('efektivitas_desain_pengendalian_3.'.$index, $do?->efektivitas_desain_pengendalian_3) == 3 ? 'selected' : '' }}>Tidak Efektif</option>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label class="form-label">Kesimpulan Efektivitas Desain Pengendalian <span class="text-danger">*</span></label>
                      <select name="efektivitas_desain_pengendalian_akhir[]" class="form-select kesimpulan-efektivitas" data-index="{{ $index }}" required>
                        <option value="">Pilih Kesimpulan</option>
                        <option value="1" {{ old('efektivitas_desain_pengendalian_akhir.'.$index, $do?->efektivitas_desain_pengendalian_akhir) == 1 ? 'selected' : '' }}>Efektif</option>
                        <option value="2" {{ old('efektivitas_desain_pengendalian_akhir.'.$index, $do?->efektivitas_desain_pengendalian_akhir) == 2 ? 'selected' : '' }}>Efektif Sebagian</option>
                        <option value="3" {{ old('efektivitas_desain_pengendalian_akhir.'.$index, $do?->efektivitas_desain_pengendalian_akhir) == 3 ? 'selected' : '' }}>Tidak Efektif</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Kesimpulan dan Tindak Lanjut -->
                <div class="mb-4">
                  <h6 class="mb-3 border-bottom pb-2">Kesimpulan dan Tindak Lanjut</h6>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Kesimpulan Akhir <span class="text-danger">*</span></label>
                      <input type="text" class="form-control kesimpulan-akhir-{{ $index }}" name="kesimpulan_akhir[]" value="{{ old('kesimpulan_akhir.'.$index, $do?->kesimpulan_akhir) }}" readonly>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Hasil Temuan <span class="text-danger">*</span></label>
                      <textarea class="form-control" name="hasil_temuan[]" rows="3" required>{{ old('hasil_temuan.'.$index, $do?->hasil_temuan) }}</textarea>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Rencana Tindak Lanjut <span class="text-danger">*</span></label>
                      <textarea class="form-control" name="rencana_tindak_lanjut[]" rows="3" required>{{ old('rencana_tindak_lanjut.'.$index, $do?->rencana_tindak_lanjut) }}</textarea>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <label class="form-label">Batas Waktu Penyelesaian Tindak Lanjut <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control flatpickr-date"
                        name="batas_waktu_penyelesaian[]"
                        placeholder="Pilih Tanggal"
                        value="{{ old('batas_waktu_penyelesaian.'.$index, $do?->batas_waktu_penyelesaian ? $do->batas_waktu_penyelesaian->format('Y-m-d') : '') }}"
                        required
                      >
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Penanggung Jawab Tindak Lanjut <span class="text-danger">*</span></label>
                      <select name="penanggung_jawab_jabatan_id[]" class="form-select" required>
                        <option value="">Pilih Jabatan</option>
                        @foreach($jabatans as $jabatan)
                          <option value="{{ $jabatan->id }}" {{ old('penanggung_jawab_jabatan_id.'.$index, $do?->penanggung_jawab_jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                            {{ $jabatan->name }}
                          </option>
                        @endforeach
                      </select>
                      {{-- Jika ingin menghandle manual input penanggung jawab juga --}}
                      <input type="hidden" name="penanggung_jawab[]" value="{{ old('penanggung_jawab.'.$index, $do?->penanggung_jawab) }}">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach

          <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('ict.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" name="action" value="draft" class="btn bg-warning text-white" formnovalidate id="btnDraft">
              Simpan Sementara
            </button>
            <button type="submit" name="action" value="submit" class="btn btn-primary" id="btnSimpan">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    flatpickr('.flatpickr-date', {
        altInput: true,
        altFormat: "j F Y",
        dateFormat: "Y-m-d",
        disableMobile: "true",
    });

    // Fungsi untuk menghasilkan kesimpulan akhir
    function generateKesimpulanAkhir(index) {
      // Dapatkan section berdasarkan index
      const section = $('.key-control-section').eq(index);

      // Dapatkan nilai dari dropdown di dalam section yang spesifik
      // Gunakan selector yang sesuai dengan struktur HTML
      const kecukupanSelect = section.find('select[name="kecukupan_desain_pengendalian_akhir[]"]');
      const efektivitasSelect = section.find('select[name="efektivitas_desain_pengendalian_akhir[]"]');
      const kesimpulanInput = section.find('.kesimpulan-akhir-' + index);

      // Ambil nilai terlebih dahulu
      const kecukupanVal = kecukupanSelect.val();
      const efektivitasVal = efektivitasSelect.val();

      // Tambahkan debugging
      console.log('Generating kesimpulan for index:', index);
      console.log('Kecukupan value:', kecukupanVal);
      console.log('Efektivitas value:', efektivitasVal);

      let kecukupanText = '';
      let efektivitasText = '';

      if (kecukupanVal === '1') kecukupanText = 'cukup';
      else if (kecukupanVal === '2') kecukupanText = 'tidak cukup';

      if (efektivitasVal === '1') efektivitasText = 'efektif';
      else if (efektivitasVal === '2') efektivitasText = 'efektif sebagian';
      else if (efektivitasVal === '3') efektivitasText = 'tidak efektif';

      if (kecukupanText && efektivitasText) {
        kesimpulanInput.val(kecukupanText + ' dan ' + efektivitasText);
      } else {
        kesimpulanInput.val('');
      }
    }

    // Gunakan event delegation untuk menangani perubahan pada dropdown
    $(document).on('change', 'select[name="kecukupan_desain_pengendalian_akhir[]"]', function() {
      const index = $(this).closest('.key-control-section').data('index');
      console.log('Kecukupan changed, index:', index);
      generateKesimpulanAkhir(index);
    });



    // Gunakan event delegation untuk menangani perubahan pada dropdown efektivitas
    $(document).on('change', 'select[name="efektivitas_desain_pengendalian_akhir[]"]', function() {
      const index = $(this).closest('.key-control-section').data('index');
      console.log('Efektivitas changed, index:', index);
      generateKesimpulanAkhir(index);
    });

    // Inisialisasi kesimpulan akhir untuk semua key control saat halaman dimuat
    $('.key-control-section').each(function(index) {
      const kecukupanVal = $(this).find('select[name="kecukupan_desain_pengendalian_akhir[]"]').val();
      const efektivitasVal = $(this).find('select[name="efektivitas_desain_pengendalian_akhir[]"]').val();

      if (kecukupanVal && efektivitasVal) {
        generateKesimpulanAkhir(index);
      }
    });

    // Inisialisasi Select2
    $('select').select2({
        width: '100%',
        placeholder: 'Pilih Opsi'
    });

    let clickedButtonValue = '';

    $('button[type="submit"]').on('click', function(e) {
        e.preventDefault();
        clickedButtonValue = $(this).val(); // 'draft' atau 'submit'
        let form = $('#ictDoForm');

        let titleText = 'Konfirmasi';
        let bodyText = '';
        let confirmBtnText = '';

        if(clickedButtonValue === 'draft') {
            bodyText = 'Data akan disimpan sementara (DRAFT). Field yang kosong diperbolehkan. Lanjutkan?';
            confirmBtnText = 'Ya, Simpan Sementara';
        } else {
            bodyText = 'Apakah Anda yakin ingin menyimpan data pengujian ICT Plan ini secara Final? Semua field wajib diisi.';
            confirmBtnText = 'Ya, Simpan';
        }

        Swal.fire({
            title: titleText,
            text: bodyText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: confirmBtnText,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'action',
                    value: clickedButtonValue
                }).appendTo(form);
                form.submit();
            }
        });
    });

  });
</script>
@endpush
