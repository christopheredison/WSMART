@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
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
                    <select name="jenis_kontrol[]" class="form-select js-required" data-fieldname="Jenis Kontrol (Key Control #{{ $index + 1 }})">
                      <option value="">Pilih Jenis Kontrol</option>
                      <option value="1" {{ old('jenis_kontrol.'.$index, $do?->jenis_kontrol) == 1 ? 'selected' : '' }}>Kontrol Operasi</option>
                      <option value="2" {{ old('jenis_kontrol.'.$index, $do?->jenis_kontrol) == 2 ? 'selected' : '' }}>Kontrol Kepatuhan</option>
                      <option value="3" {{ old('jenis_kontrol.'.$index, $do?->jenis_kontrol) == 3 ? 'selected' : '' }}>Kontrol Pelaporan</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Bentuk Kontrol <span class="text-danger">*</span></label>
                    <select name="bentuk_kontrol[]" class="form-select js-required" data-fieldname="Bentuk Kontrol (Key Control #{{ $index + 1 }})">
                      <option value="">Pilih Bentuk Kontrol</option>
                      <option value="1" {{ old('bentuk_kontrol.'.$index, $do?->bentuk_kontrol) == 1 ? 'selected' : '' }}>SOP</option>
                      <option value="2" {{ old('bentuk_kontrol.'.$index, $do?->bentuk_kontrol) == 2 ? 'selected' : '' }}>Kebijakan</option>
                      <option value="3" {{ old('bentuk_kontrol.'.$index, $do?->bentuk_kontrol) == 3 ? 'selected' : '' }}>Sistem Informasi dan Komunikasi</option>
                      <option value="4" {{ old('bentuk_kontrol.'.$index, $do?->bentuk_kontrol) == 4 ? 'selected' : '' }}>Sistem Lainnya</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Level Pengendalian <span class="text-danger">*</span></label>
                    <select name="level_pengendalian[]" class="form-select js-required" data-fieldname="Level Pengendalian (Key Control #{{ $index + 1 }})">
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
                      <select name="kecukupan_desain_pengendalian_1[]" class="form-select js-required" data-fieldname="Kecukupan Desain 1 (Key Control #{{ $index + 1 }})">
                        <option value="">Pilih Kecukupan</option>
                        <option value="1" {{ old('kecukupan_desain_pengendalian_1.'.$index, $do?->kecukupan_desain_pengendalian_1) == 1 ? 'selected' : '' }}>Cukup</option>
                        <option value="2" {{ old('kecukupan_desain_pengendalian_1.'.$index, $do?->kecukupan_desain_pengendalian_1) == 2 ? 'selected' : '' }}>Tidak Cukup</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Kecukupan desain pengendalian dalam memitigasi risiko <span class="text-danger">*</span></label>
                      <select name="kecukupan_desain_pengendalian_2[]" class="form-select js-required" data-fieldname="Kecukupan Desain 2 (Key Control #{{ $index + 1 }})">
                        <option value="">Pilih Kecukupan</option>
                        <option value="1" {{ old('kecukupan_desain_pengendalian_2.'.$index, $do?->kecukupan_desain_pengendalian_2) == 1 ? 'selected' : '' }}>Cukup</option>
                        <option value="2" {{ old('kecukupan_desain_pengendalian_2.'.$index, $do?->kecukupan_desain_pengendalian_2) == 2 ? 'selected' : '' }}>Tidak Cukup</option>
                      </select>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Kecukupan dokumentasi yang memadai untuk pengendalian <span class="text-danger">*</span></label>
                      <select name="kecukupan_desain_pengendalian_3[]" class="form-select js-required" data-fieldname="Kecukupan Desain 3 (Key Control #{{ $index + 1 }})">
                        <option value="">Pilih Kecukupan</option>
                        <option value="1" {{ old('kecukupan_desain_pengendalian_3.'.$index, $do?->kecukupan_desain_pengendalian_3) == 1 ? 'selected' : '' }}>Cukup</option>
                        <option value="2" {{ old('kecukupan_desain_pengendalian_3.'.$index, $do?->kecukupan_desain_pengendalian_3) == 2 ? 'selected' : '' }}>Tidak Cukup</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Keberadaan risiko perusahaan yang belum termitigasi <span class="text-danger">*</span></label>
                      <select name="kecukupan_desain_pengendalian_4[]" class="form-select js-required" data-fieldname="Kecukupan Desain 4 (Key Control #{{ $index + 1 }})">
                        <option value="">Pilih Kecukupan</option>
                        <option value="1" {{ old('kecukupan_desain_pengendalian_4.'.$index, $do?->kecukupan_desain_pengendalian_4) == 1 ? 'selected' : '' }}>Cukup</option>
                        <option value="2" {{ old('kecukupan_desain_pengendalian_4.'.$index, $do?->kecukupan_desain_pengendalian_4) == 2 ? 'selected' : '' }}>Tidak Cukup</option>
                      </select>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <label class="form-label">Kesimpulan Kecukupan Desain Pengendalian <span class="text-danger">*</span></label>
                      <select name="kecukupan_desain_pengendalian_akhir[]" class="form-select js-required" data-fieldname="Kesimpulan Kecukupan (Key Control #{{ $index + 1 }})">
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
                      <select name="efektivitas_desain_pengendalian_1[]" class="form-select js-required" data-fieldname="Efektivitas Desain 1 (Key Control #{{ $index + 1 }})">
                        <option value="">Pilih Efektivitas</option>
                        <option value="1" {{ old('efektivitas_desain_pengendalian_1.'.$index, $do?->efektivitas_desain_pengendalian_1) == 1 ? 'selected' : '' }}>Efektif</option>
                        <option value="2" {{ old('efektivitas_desain_pengendalian_1.'.$index, $do?->efektivitas_desain_pengendalian_1) == 2 ? 'selected' : '' }}>Efektif Sebagian</option>
                        <option value="3" {{ old('efektivitas_desain_pengendalian_1.'.$index, $do?->efektivitas_desain_pengendalian_1) == 3 ? 'selected' : '' }}>Tidak Efektif</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Konsistensi pelaksanaan pengendalian di lapangan <span class="text-danger">*</span></label>
                      <select name="efektivitas_desain_pengendalian_2[]" class="form-select js-required" data-fieldname="Efektivitas Desain 2 (Key Control #{{ $index + 1 }})">
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
                      <select name="efektivitas_desain_pengendalian_3[]" class="form-select js-required" data-fieldname="Efektivitas Desain 3 (Key Control #{{ $index + 1 }})">
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
                      <select name="efektivitas_desain_pengendalian_akhir[]" class="form-select kesimpulan-efektivitas js-required" data-index="{{ $index }}" data-fieldname="Kesimpulan Efektivitas (Key Control #{{ $index + 1 }})">
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
                      <input type="text" class="form-control kesimpulan-akhir-{{ $index }} js-required" name="kesimpulan_akhir[]" value="{{ old('kesimpulan_akhir.'.$index, $do?->kesimpulan_akhir) }}" data-fieldname="Kesimpulan Akhir (Key Control #{{ $index + 1 }})" readonly>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Hasil Temuan <span class="text-danger">*</span></label>
                      <textarea class="form-control js-required" name="hasil_temuan[]" rows="3" data-fieldname="Hasil Temuan (Key Control #{{ $index + 1 }})">{{ old('hasil_temuan.'.$index, $do?->hasil_temuan) }}</textarea>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-12">
                      <label class="form-label">Rencana Tindak Lanjut <span class="text-danger">*</span></label>
                      <textarea class="form-control js-required" name="rencana_tindak_lanjut[]" rows="3" data-fieldname="Rencana Tindak Lanjut (Key Control #{{ $index + 1 }})">{{ old('rencana_tindak_lanjut.'.$index, $do?->rencana_tindak_lanjut) }}</textarea>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <label class="form-label">Batas Waktu Penyelesaian Tindak Lanjut <span class="text-danger">*</span></label>
                      <input
                        type="text"
                        class="form-control flatpickr-date bg-white js-required"
                        name="batas_waktu_penyelesaian[]"
                        placeholder="Pilih Tanggal"
                        value="{{ old('batas_waktu_penyelesaian.'.$index, $do?->batas_waktu_penyelesaian ? $do->batas_waktu_penyelesaian->format('Y-m-d') : '') }}"
                        data-fieldname="Batas Waktu Penyelesaian (Key Control #{{ $index + 1 }})"
                      >
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Penanggung Jawab Tindak Lanjut <span class="text-danger">*</span></label>
                      <select name="penanggung_jawab_jabatan_id[]" class="form-select js-required" data-fieldname="Penanggung Jawab (Key Control #{{ $index + 1 }})">
                        <option value="">Pilih Jabatan</option>
                        @foreach($jabatans as $jabatan)
                          <option value="{{ $jabatan->id }}" {{ old('penanggung_jawab_jabatan_id.'.$index, $do?->penanggung_jawab_jabatan_id) == $jabatan->id ? 'selected' : '' }}>
                            {{ $jabatan->name }}
                          </option>
                        @endforeach
                      </select>
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

@push('styles')
<style>
  .select2-container .select2-selection.is-invalid,
  .select2-container--default .select2-selection--single.is-invalid {
    border-color: #dc3545 !important;
  }
  .flatpickr-input.is-invalid + input.form-control,
  input.form-control.is-invalid {
    border-color: #dc3545 !important;
  }
  .invalid-feedback.d-block {
    display: block !important;
  }
</style>
@endpush

@push('scripts')
<script>
  $(document).ready(function() {
    flatpickr('.flatpickr-date', {
      altInput: true,
      altFormat: "j F Y",
      dateFormat: "Y-m-d",
      disableMobile: "true",
    });

    function generateKesimpulanAkhir(index) {
      const section = $('.key-control-section').eq(index);
      const kecukupanSelect = section.find('select[name="kecukupan_desain_pengendalian_akhir[]"]');
      const efektivitasSelect = section.find('select[name="efektivitas_desain_pengendalian_akhir[]"]');
      const kesimpulanInput = section.find('.kesimpulan-akhir-' + index);

      const kecukupanVal = kecukupanSelect.val();
      const efektivitasVal = efektivitasSelect.val();

      let kecukupanText = '';
      let efektivitasText = '';

      if (kecukupanVal === '1') kecukupanText = 'cukup';
      else if (kecukupanVal === '2') kecukupanText = 'tidak cukup';

      if (efektivitasVal === '1') efektivitasText = 'efektif';
      else if (efektivitasVal === '2') efektivitasText = 'efektif sebagian';
      else if (efektivitasVal === '3') efektivitasText = 'tidak efektif';

      if (kecukupanText && efektivitasText) {
        kesimpulanInput.val(kecukupanText + ' dan ' + efektivitasText);
        clearFieldError(kesimpulanInput);
      } else {
        kesimpulanInput.val('');
      }
    }

    function markFieldError($el, message) {
      $el.addClass('is-invalid');

      if ($el.hasClass('select2-hidden-accessible')) {
        $el.next('.select2-container').find('.select2-selection').addClass('is-invalid');
      }

      if ($el.hasClass('flatpickr-input') && $el[0]._flatpickr) {
        const altInput = $el[0]._flatpickr.altInput;
        if (altInput) {
          $(altInput).addClass('is-invalid');
        }
      }

      let $feedback = $el.siblings('.invalid-feedback');
      if (!$feedback.length && $el.hasClass('select2-hidden-accessible')) {
        $feedback = $el.next('.select2-container').siblings('.invalid-feedback');
      }
      if (!$feedback.length && $el.hasClass('flatpickr-input')) {
        $feedback = $el.parent().find('.invalid-feedback');
      }

      if (!$feedback.length) {
        $feedback = $('<div class="invalid-feedback d-block"></div>');
        if ($el.hasClass('select2-hidden-accessible')) {
          $el.next('.select2-container').after($feedback);
        } else if ($el.hasClass('flatpickr-input') && $el[0]._flatpickr && $el[0]._flatpickr.altInput) {
          $($el[0]._flatpickr.altInput).after($feedback);
        } else {
          $el.after($feedback);
        }
      }

      $feedback.text(message || 'Field ini wajib diisi.');
    }

    function clearFieldError($el) {
      $el.removeClass('is-invalid');

      if ($el.hasClass('select2-hidden-accessible')) {
        $el.next('.select2-container').find('.select2-selection').removeClass('is-invalid');
      }

      if ($el.hasClass('flatpickr-input') && $el[0]._flatpickr) {
        const altInput = $el[0]._flatpickr.altInput;
        if (altInput) {
          $(altInput).removeClass('is-invalid');
        }
      }

      $el.siblings('.invalid-feedback').remove();
      if ($el.hasClass('select2-hidden-accessible')) {
        $el.next('.select2-container').siblings('.invalid-feedback').remove();
      }
      if ($el.hasClass('flatpickr-input')) {
        $el.parent().find('.invalid-feedback').remove();
      }
    }

    function clearAllErrors() {
      $('#ictDoForm .js-required').each(function() {
        clearFieldError($(this));
      });
    }

    function validateRequiredFields() {
      clearAllErrors();

      let isValid = true;
      let firstInvalid = null;
      const errorMessages = [];

      // Pastikan kesimpulan akhir ter-generate dulu
      $('.key-control-section').each(function(index) {
        generateKesimpulanAkhir(index);
      });

      $('#ictDoForm .js-required').each(function() {
        const $el = $(this);
        const value = ($el.val() || '').toString().trim();

        if (!value) {
          isValid = false;
          const fieldName = $el.data('fieldname') || 'Field wajib';
          markFieldError($el, fieldName + ' wajib diisi.');
          errorMessages.push('<li>' + fieldName + '</li>');

          if (!firstInvalid) {
            firstInvalid = $el.hasClass('select2-hidden-accessible')
              ? $el.next('.select2-container')
              : ($el.hasClass('flatpickr-input') && $el[0]._flatpickr && $el[0]._flatpickr.altInput
                  ? $($el[0]._flatpickr.altInput)
                  : $el);
          }
        }
      });

      return { isValid, firstInvalid, errorMessages };
    }

    $(document).on('change', 'select[name="kecukupan_desain_pengendalian_akhir[]"]', function() {
      const index = $(this).closest('.key-control-section').data('index');
      generateKesimpulanAkhir(index);
    });

    $(document).on('change', 'select[name="efektivitas_desain_pengendalian_akhir[]"]', function() {
      const index = $(this).closest('.key-control-section').data('index');
      generateKesimpulanAkhir(index);
    });

    $('.key-control-section').each(function(index) {
      const kecukupanVal = $(this).find('select[name="kecukupan_desain_pengendalian_akhir[]"]').val();
      const efektivitasVal = $(this).find('select[name="efektivitas_desain_pengendalian_akhir[]"]').val();
      if (kecukupanVal && efektivitasVal) {
        generateKesimpulanAkhir(index);
      }
    });

    $('select').select2({
      width: '100%',
      placeholder: 'Pilih Opsi'
    });

    $(document).on('change', '#ictDoForm .js-required', function() {
      clearFieldError($(this));
    });

    $(document).on('input', '#ictDoForm textarea.js-required, #ictDoForm input.js-required', function() {
      clearFieldError($(this));
    });

    function submitForm(actionValue) {
      const form = $('#ictDoForm');
      form.find('input[name="action"][type="hidden"]').remove();
      $('<input>').attr({
        type: 'hidden',
        name: 'action',
        value: actionValue
      }).appendTo(form);
      form.submit();
    }

    $('#btnDraft, #btnSimpan').on('click', function(e) {
      e.preventDefault();
      const actionValue = $(this).val();

      if (actionValue === 'submit') {
        const validation = validateRequiredFields();
        if (!validation.isValid) {
          const errorHtml = '<div class="text-start" style="max-height: 250px; overflow-y: auto;">' +
            '<p class="mb-2 text-dark">Mohon lengkapi bagian berikut:</p>' +
            '<ul class="text-danger ps-3 mb-0" style="font-size: 0.95rem;">' +
              validation.errorMessages.join('') +
            '</ul></div>';

          Swal.fire({
            icon: 'error',
            title: 'Penilaian Belum Lengkap!',
            html: errorHtml,
            confirmButtonText: 'Mengerti'
          }).then(() => {
            if (validation.firstInvalid && validation.firstInvalid.length) {
              $('html, body').animate({
                scrollTop: validation.firstInvalid.offset().top - 150
              }, 400);
            }
          });
          return;
        }
      }

      const isDraft = actionValue === 'draft';
      Swal.fire({
        title: 'Konfirmasi',
        text: isDraft
          ? 'Data akan disimpan sementara (DRAFT). Field yang kosong diperbolehkan. Lanjutkan?'
          : 'Apakah Anda yakin ingin menyimpan data pengujian ICT Plan ini secara Final?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: isDraft ? 'Ya, Simpan Sementara' : 'Ya, Simpan',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          submitForm(actionValue);
        }
      });
    });
  });
</script>
@endpush
