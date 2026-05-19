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
              <label for="risiko_id" class="form-label">Peristiwa Risiko <span class="text-danger">*</span></label>
              <select class="form-select select2" id="risiko_id" name="risiko_id" required>
                <option value="" selected disabled>Pilih Peristiwa Risiko</option>
                {{-- Tampilkan langsung data dari controller --}}
                @foreach($identifikasiRisikos as $risiko)
                  <option value="{{ $risiko->id }}" {{ old('risiko_id') == $risiko->id ? 'selected' : '' }}>
                    {{ $risiko->peristiwa_risiko }}
                  </option>
                @endforeach
              </select>
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
              <label class="form-label">Key Control</label>
              <div class="table-responsive">
                <table class="table table-bordered" id="keyControlTable">
                  <thead>
                    <tr>
                      <th width="100%">Key Control</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Key control akan ditambahkan di sini via AJAX -->
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
    // Inisialisasi Select2
    $('.select2').select2({
      placeholder: "Pilih Peristiwa Risiko",
      allowClear: true,
      width: '100%'
    });

    // Cek jika ada old('risiko_id') agar key control ter-load jika validasi gagal
    if ($('#risiko_id').val()) {
        $('#risiko_id').trigger('change');
    }

    // Ketika peristiwa risiko berubah
    $('#risiko_id').on('change', function() {
      const risikoId = $(this).val();
      const type = 1; // Hardcode type 1 (Korporat) karena dropdown type sudah dihilangkan

      if (!risikoId) return;

      // Kosongkan tabel key control
      $('#keyControlTable tbody').empty();

      // Ambil key control berdasarkan type dan risiko_id
      $.ajax({
        url: '/api/key-controls',
        type: 'GET',
        data: {
          type: type,
          risiko_id: risikoId
        },
        success: function(response) {
          if (response.data && response.data.length > 0) {
            // Tambahkan key control ke tabel
            response.data.forEach(function(item) {
              addKeyControlRow(item.id, item.kontrol_eksisting || item.kontrol_eksisting_desc);
            });
          } else {
            // Jika tidak ada key control, tambahkan baris kosong
            addEmptyKeyControlRow();
          }
        },
        error: function(xhr) {
          console.error('Error fetching key controls:', xhr);
          addEmptyKeyControlRow();
        }
      });
    });

    // Fungsi untuk menambahkan baris key control
    function addKeyControlRow(id, text) {
      const row = `
        <tr>
          <td>
            <input type="hidden" name="key_control_id[]" value="${id}">
            <input type="text" class="form-control" name="key_control[]" value="${text}" readonly required>
          </td>
        </tr>
      `;
      $('#keyControlTable tbody').append(row);
    }

    // Fungsi untuk menambahkan baris key control kosong
    function addEmptyKeyControlRow() {
      const row = `
        <tr>
          <td>
            <input type="hidden" name="key_control_id[]" value="0">
            <input type="text" class="form-control" name="key_control[]" readonly required>
          </td>
        </tr>
      `;
      $('#keyControlTable tbody').append(row);
    }

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
