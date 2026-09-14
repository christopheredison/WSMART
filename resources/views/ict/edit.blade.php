@extends('layouts.default')
@section('dashboard')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <div class="bg-warning-subtle p-2 rounded-4">
          <div class="lead__icon">
            <div class="svg-icon svg-icon-2x svg-icon-warning">
              {{-- Ganti icon edit jika ada, pakai pyramid juga oke --}}
              @include('partials.icon-pyramid')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">Edit Data</div>
          <h2>Edit ICT Plan</h2>
        </div>
      </div>
      <div class="card-body">
        <form id="ictPlanForm" action="{{ route('ict.update', $ictPlan->id) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row mb-4">
            <div class="col-12 mb-3">
              <label for="sasaran_bumn" class="form-label">Sasaran BUMN  <span class="text-danger">*</span></label>
              <textarea class="form-control" id="sasaran_bumn" name="sasaran_bumn" rows="3" required>{{ old('sasaran_bumn', $ictPlan->sasaran_bumn) }}</textarea>
            </div>

            {{-- <div class="col-md-6 mb-3">
              <label for="type" class="form-label">Type</label>
              <select class="form-select" id="type" name="type" required>
                <option value="" disabled>Pilih Type</option>
                @foreach($types as $key => $value)
                  <option value="{{ $key }}" {{ old('type', $ictPlan->type) == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div> --}}

            <div class="col-md-6 mb-3">
              <label for="tahun_pelaporan" class="form-label">Tahun Pelaporan <span class="text-danger">*</span></label>
              <select class="form-select select2-general" id="tahun_pelaporan" name="tahun_pelaporan" required>
                <option value="" disabled>Pilih Tahun Pelaporan</option>
                @for($i = 2025; $i <= (date('Y') + 5); $i++)
                  <option value="{{ $i }}" {{ old('tahun_pelaporan', $ictPlan->tahun_pelaporan) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label for="risiko_mode" class="form-label">Peristiwa Risiko <span class="text-danger">*</span></label>
              <select class="form-select" id="risiko_mode" name="risiko_mode" required>
                <option value="existing" {{ old('risiko_mode', empty($ictPlan->peristiwa_risiko) ? 'existing' : 'manual') === 'existing' ? 'selected' : '' }}>Pilih dari daftar</option>
                <option value="manual" {{ old('risiko_mode', empty($ictPlan->peristiwa_risiko) ? 'existing' : 'manual') === 'manual' ? 'selected' : '' }}>Input manual</option>
              </select>
            </div>

            <div class="col-12 mb-3" id="existing-risiko-wrapper">
              <label for="risiko_id" class="form-label">Pilih Peristiwa Risiko</label>
              <select class="form-select select2" id="risiko_id" name="risiko_id">
                <option value="">Pilih Peristiwa Risiko</option>
                @foreach($identifikasiRisikos as $risiko)
                    <option value="{{ $risiko->id }}" {{ old('risiko_id', $ictPlan->risiko_id) == $risiko->id ? 'selected' : '' }}>
                        {{ $risiko->peristiwa_risiko }}
                    </option>
                @endforeach
              </select>
            </div>

            <div class="col-12 mb-3 d-none" id="manual-risiko-wrapper">
              <label for="peristiwa_risiko_manual" class="form-label">Peristiwa Risiko (Manual) <span class="text-danger">*</span></label>
              <textarea class="form-control" id="peristiwa_risiko_manual" name="peristiwa_risiko_manual" rows="3">{{ old('peristiwa_risiko_manual', $ictPlan->peristiwa_risiko) }}</textarea>
            </div>

            <div class="col-12 mb-3">
              <label for="lokasi_risiko" class="form-label">Lokasi Risiko <span class="text-danger">*</span></label>
              <textarea class="form-control" id="lokasi_risiko" name="lokasi_risiko" rows="3" required>{{ old('lokasi_risiko', $ictPlan->lokasi_risiko) }}</textarea>
            </div>

            <div class="col-12 mb-3">
              <label for="business_process" class="form-label">Business Process <span class="text-danger">*</span></label>
              <textarea class="form-control" id="business_process" name="business_process" rows="3" required>{{ old('business_process', $ictPlan->business_process) }}</textarea>
            </div>

            <div class="col-12 mb-3">
              <label for="metode_pengujian" class="form-label">Metode Pengujian <span class="text-danger">*</span></label>
              <textarea class="form-control" id="metode_pengujian" name="metode_pengujian" rows="3" required>{{ old('metode_pengujian', $ictPlan->metode_pengujian) }}</textarea>
            </div>

            <div class="col-12 mb-3">
              <div class="d-flex justify-content-between align-items-center">
                <label class="form-label mb-0">Key Control <span class="text-danger">*</span></label>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addManualKeyControlBtn">
                  <span class="bx bx-plus"></span> Tambah Key Control Manual
                </button>
              </div>
              <div class="table-responsive">
                <table class="table table-bordered" id="keyControlTable">
                  <thead>
                    <tr>
                      <th width="90%">Key Control</th>
                      <th width="10%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach(old('key_control', []) as $index => $oldControl)
                    @php $isLocked = old('key_control_id.'.$index, 0) > 0; @endphp
                    <tr>
                        <td>
                            <input type="hidden" name="key_control_id[]" value="{{ old('key_control_id.'.$index, 0) }}">
                            <input type="hidden" name="ict_plan_control_id[]" value="{{ old('ict_plan_control_id.'.$index) }}">
                            <input type="text" class="form-control key-control-input" name="key_control[]" value="{{ $oldControl }}" {{ $isLocked ? 'readonly' : '' }} required>
                        </td>
                        <td class="text-center align-middle">
                          <div class="d-inline-flex gap-1">
                            @if($isLocked)
                              <button type="button" class="btn btn-sm btn-outline-secondary unlock-key-control" title="Klik untuk unlock edit">
                                <span class="bx bx-lock-alt"></span>
                              </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-danger remove-key-control" title="Hapus">
                              <span class="bx bx-trash"></span>
                            </button>
                          </div>
                        </td>
                    </tr>
                    @endforeach
                    @if(!count(old('key_control', [])))
                      @foreach($ictPlan->planControls as $control)
                      <tr>
                          <td>
                              <input type="hidden" name="key_control_id[]" value="{{ $control->key_control_id }}">
                              <input type="hidden" name="ict_plan_control_id[]" value="{{ $control->id }}">
                              <input type="text" class="form-control key-control-input" name="key_control[]" value="{{ $control->key_control }}" readonly required>
                          </td>
                          <td class="text-center align-middle">
                            <div class="d-inline-flex gap-1">
                              <button type="button" class="btn btn-sm btn-outline-secondary unlock-key-control" title="Klik untuk unlock edit">
                                <span class="bx bx-lock-alt"></span>
                              </button>
                              <button type="button" class="btn btn-sm btn-outline-danger remove-key-control" title="Hapus">
                                <span class="bx bx-trash"></span>
                              </button>
                            </div>
                          </td>
                      </tr>
                      @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('ict.show', $ictPlan->id) }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-info" id="btnSubmit">Update</button>
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
    $('.select2').select2({
      placeholder: "Pilih Peristiwa Risiko",
      allowClear: true,
      width: '100%'
    });

    const hasOldInput = @json(count(old('key_control', [])) > 0);
    const initialRisikoId = @json($ictPlan->risiko_id);
    const initialMode = @json(empty($ictPlan->peristiwa_risiko) ? 'existing' : 'manual');

    function toggleRiskMode() {
      const mode = $('#risiko_mode').val();
      const isManual = mode === 'manual';

      $('#existing-risiko-wrapper').toggleClass('d-none', isManual);
      $('#manual-risiko-wrapper').toggleClass('d-none', !isManual);
      $('#risiko_id').prop('required', !isManual);
      $('#peristiwa_risiko_manual').prop('required', isManual);

      if (isManual) {
        $('#risiko_id').val(null).trigger('change');
        if ($('#keyControlTable tbody tr').length === 0) {
          addKeyControlRow(0, '', null, false);
        }
      } else if (!hasOldInput) {
        loadKeyControlsByRisiko(false);
      }
    }

    function getKeyControlActionsHtml(isReadonly) {
      if (isReadonly) {
        return `
          <div class="d-inline-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-secondary unlock-key-control" title="Klik untuk unlock edit">
              <span class="bx bx-lock-alt"></span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger remove-key-control" title="Hapus">
              <span class="bx bx-trash"></span>
            </button>
          </div>
        `;
      }

      return `
        <div class="d-inline-flex gap-1">
          <button type="button" class="btn btn-sm btn-outline-danger remove-key-control" title="Hapus">
            <span class="bx bx-trash"></span>
          </button>
        </div>
      `;
    }

    function addKeyControlRow(id, text, planControlId, isReadonly) {
      const readonlyAttr = isReadonly ? 'readonly' : '';
      const row = `
        <tr>
          <td>
            <input type="hidden" name="key_control_id[]" value="${id}">
            <input type="hidden" name="ict_plan_control_id[]" value="${planControlId || ''}">
            <input type="text" class="form-control key-control-input" name="key_control[]" value="${text || ''}" ${readonlyAttr} required>
          </td>
          <td class="text-center align-middle">${getKeyControlActionsHtml(isReadonly)}</td>
        </tr>
      `;
      $('#keyControlTable tbody').append(row);
    }

    function loadKeyControlsByRisiko(forceLoad = true) {
      const risikoId = $('#risiko_id').val();
      const mode = $('#risiko_mode').val();

      if (mode === 'manual') {
        return;
      }

      if (!risikoId) {
        return;
      }

      if (!forceLoad && mode === initialMode && String(risikoId) === String(initialRisikoId)) {
        return;
      }

      $('#keyControlTable tbody').empty();

      $.ajax({
        url: '/api/key-controls',
        type: 'GET',
        data: { type: 1, risiko_id: risikoId },
        success: function(response) {
          if (response.data && response.data.length > 0) {
            response.data.forEach(function(item) {
              addKeyControlRow(item.id, item.kontrol_eksisting || item.kontrol_eksisting_desc, null, true);
            });
          } else {
            addKeyControlRow(0, '', null, false);
          }
        },
        error: function() {
          addKeyControlRow(0, '', null, false);
        }
      });
    }

    $('#risiko_mode').on('change', function() {
      toggleRiskMode();
    });

    $('#risiko_id').on('change', function() {
      if ($('#risiko_mode').val() === 'existing') {
        loadKeyControlsByRisiko(true);
      }
    });

    $('#addManualKeyControlBtn').on('click', function() {
      addKeyControlRow(0, '', null, false);
    });

    $(document).on('click', '.unlock-key-control', function() {
      const $btn = $(this);
      const $row = $btn.closest('tr');
      const $input = $row.find('.key-control-input');

      $input.prop('readonly', false).focus();
      $btn
        .removeClass('btn-outline-secondary unlock-key-control')
        .addClass('btn-success lock-key-control')
        .attr('title', 'Kunci kembali')
        .html('<span class="bx bx-lock-open-alt"></span>');
    });

    $(document).on('click', '.lock-key-control', function() {
      const $btn = $(this);
      const $row = $btn.closest('tr');
      const $input = $row.find('.key-control-input');

      $input.prop('readonly', true);
      $btn
        .removeClass('btn-success lock-key-control')
        .addClass('btn-outline-secondary unlock-key-control')
        .attr('title', 'Klik untuk unlock edit')
        .html('<span class="bx bx-lock-alt"></span>');
    });

    $(document).on('click', '.remove-key-control', function() {
      const $row = $(this).closest('tr');
      const keyControlText = ($row.find('.key-control-input').val() || '').trim();

      Swal.fire({
        title: 'Hapus Key Control?',
        text: keyControlText
          ? `Key Control "${keyControlText}" akan dihapus dari form ini.`
          : 'Baris Key Control ini akan dihapus dari form.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (!result.isConfirmed) {
          return;
        }

        $row.remove();
        if ($('#keyControlTable tbody tr').length === 0) {
          addKeyControlRow(0, '', null, false);
        }
      });
    });

    toggleRiskMode();

    $('#ictPlanForm').on('submit', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Konfirmasi Update',
        text: 'Perubahan pada peristiwa risiko akan menghapus data pengujian sebelumnya. Lanjutkan?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Update',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit();
        }
      });
    });
  });
</script>
@endpush
