@extends('layouts.default')
@section('dashboard')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <div class="bg-info-subtle p-2 rounded-4">
          <div class="lead__icon">
            <div class="svg-icon svg-icon-2x svg-icon-info">
              @include('partials.icon-pyramid')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">Input Data</div>
          <h2>Tambah ICT Plan</h2>
        </div>
      </div>
      <div class="card-body">
        <form id="ictPlanForm" action="{{ route('ict.store') }}" method="POST">
          @csrf
          <div class="row mb-4">
            <div class="col-12 mb-3">
              <label for="sasaran_bumn" class="form-label">Sasaran BUMN <span class="text-danger">*</span></label>
              <textarea class="form-control" id="sasaran_bumn" name="sasaran_bumn" rows="3" required>{{ old('sasaran_bumn') }}</textarea>
            </div>

            <div class="col-md-6 mb-3">
              <label for="tahun_pelaporan" class="form-label">Tahun Pelaporan <span class="text-danger">*</span></label>
              <select class="form-select select2-general" id="tahun_pelaporan" name="tahun_pelaporan" required>
                <option value="" selected disabled>Pilih Tahun Pelaporan</option>
                @for($i = 2025; $i <= (date('Y') + 5); $i++)
                  <option value="{{ $i }}" {{ old('tahun_pelaporan', date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label for="risiko_mode" class="form-label">Peristiwa Risiko <span class="text-danger">*</span></label>
              <select class="form-select" id="risiko_mode" name="risiko_mode" required>
                <option value="existing" {{ old('risiko_mode', 'existing') === 'existing' ? 'selected' : '' }}>Pilih dari daftar</option>
                <option value="manual" {{ old('risiko_mode') === 'manual' ? 'selected' : '' }}>Input manual</option>
              </select>
            </div>

            <div class="col-md-6 mb-3" id="existing-risiko-wrapper">
              <label for="risiko_id" class="form-label">Pilih Peristiwa Risiko</label>
              <select class="form-select select2" id="risiko_id" name="risiko_id">
                <option value="" selected>Pilih Peristiwa Risiko</option>
                @foreach($identifikasiRisikos as $risiko)
                  <option value="{{ $risiko->id }}" {{ old('risiko_id') == $risiko->id ? 'selected' : '' }}>
                    {{ $risiko->peristiwa_risiko }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-12 mb-3 d-none" id="manual-risiko-wrapper">
              <label for="peristiwa_risiko_manual" class="form-label">Peristiwa Risiko (Manual) <span class="text-danger">*</span></label>
              <textarea class="form-control" id="peristiwa_risiko_manual" name="peristiwa_risiko_manual" rows="3">{{ old('peristiwa_risiko_manual') }}</textarea>
            </div>

            <div class="col-12 mb-3">
              <label for="lokasi_risiko" class="form-label">Lokasi Risiko <span class="text-danger">*</span></label>
              <textarea class="form-control" id="lokasi_risiko" name="lokasi_risiko" rows="3" required>{{ old('lokasi_risiko') }}</textarea>
            </div>

            <div class="col-12 mb-3">
              <label for="business_process" class="form-label">Business Process <span class="text-danger">*</span></label>
              <textarea class="form-control" id="business_process" name="business_process" rows="3" required>{{ old('business_process') }}</textarea>
            </div>

            <div class="col-12 mb-3">
              <label for="metode_pengujian" class="form-label">Metode Pengujian <span class="text-danger">*</span></label>
              <textarea class="form-control" id="metode_pengujian" name="metode_pengujian" rows="3" required>{{ old('metode_pengujian') }}</textarea>
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
                    <!-- Key control dari daftar/manual -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('ict.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan</button>
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

    const oldKeyControlIds = @json(old('key_control_id', []));
    const oldKeyControls = @json(old('key_control', []));
    const oldRisikoMode = @json(old('risiko_mode', 'existing'));
    const hasOldInput = oldKeyControls.length > 0;

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
          addKeyControlRow(0, '', false);
        }
      } else if (!hasOldInput) {
        loadKeyControlsByRisiko();
      }
    }

    function addKeyControlRow(id, text, isReadonly) {
      const readonlyAttr = isReadonly ? 'readonly' : '';
      const removeBtn = isReadonly
        ? '<button type="button" class="btn btn-sm btn-light text-muted" disabled><span class="bx bx-lock-alt"></span></button>'
        : '<button type="button" class="btn btn-sm btn-outline-danger remove-key-control"><span class="bx bx-trash"></span></button>';

      const row = `
        <tr>
          <td>
            <input type="hidden" name="key_control_id[]" value="${id}">
            <input type="text" class="form-control" name="key_control[]" value="${text || ''}" ${readonlyAttr} required>
          </td>
          <td class="text-center align-middle">${removeBtn}</td>
        </tr>
      `;
      $('#keyControlTable tbody').append(row);
    }

    function loadKeyControlsByRisiko() {
      const risikoId = $('#risiko_id').val();
      const mode = $('#risiko_mode').val();

      $('#keyControlTable tbody').empty();

      if (mode === 'manual') {
        addKeyControlRow(0, '', false);
        return;
      }

      if (!risikoId) {
        return;
      }

      $.ajax({
        url: '/api/key-controls',
        type: 'GET',
        data: {
          type: 1,
          risiko_id: risikoId
        },
        success: function(response) {
          if (response.data && response.data.length > 0) {
            response.data.forEach(function(item) {
              addKeyControlRow(item.id, item.kontrol_eksisting || item.kontrol_eksisting_desc, true);
            });
          } else {
            addKeyControlRow(0, '', false);
          }
        },
        error: function(xhr) {
          console.error('Error fetching key controls:', xhr);
          addKeyControlRow(0, '', false);
        }
      });
    }

    if (hasOldInput) {
      $('#keyControlTable tbody').empty();
      oldKeyControls.forEach(function(text, index) {
        const id = oldKeyControlIds[index] ?? 0;
        const isReadonly = Number(id) > 0;
        addKeyControlRow(id, text, isReadonly);
      });
    } else if ($('#risiko_id').val()) {
      loadKeyControlsByRisiko();
    } else {
      addKeyControlRow(0, '', false);
    }

    $('#risiko_mode').val(oldRisikoMode);
    toggleRiskMode();

    $('#risiko_mode').on('change', function() {
      toggleRiskMode();
    });

    $('#risiko_id').on('change', function() {
      if ($('#risiko_mode').val() === 'existing') {
        loadKeyControlsByRisiko();
      }
    });

    $('#addManualKeyControlBtn').on('click', function() {
      addKeyControlRow(0, '', false);
    });

    $(document).on('click', '.remove-key-control', function() {
      $(this).closest('tr').remove();
      if ($('#keyControlTable tbody tr').length === 0) {
        addKeyControlRow(0, '', false);
      }
    });

    // Konfirmasi submit form dengan SweetAlert
    $('#ictPlanForm').on('submit', function(e) {
      e.preventDefault();

      Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menyimpan data ICT Plan ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit();
        }
      });
    });
  });
</script>
@endpush
