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
              <label for="sasaran_bumn" class="form-label">Sasaran BUMN</label>
              <textarea class="form-control" id="sasaran_bumn" name="sasaran_bumn" rows="3" required>{{ old('sasaran_bumn') }}</textarea>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="type" class="form-label">Type</label>
              <select class="form-select" id="type" name="type" required>
                <option value="" selected disabled>Pilih Type</option>
                @foreach($types as $key => $value)
                  <option value="{{ $key }}">{{ $value }}</option>
                @endforeach
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="risiko_id" class="form-label">Peristiwa Risiko</label>
              <select class="form-select select2" id="risiko_id" name="risiko_id" required>
                <option value="" selected disabled>Pilih Peristiwa Risiko</option>
              </select>
            </div>
            
            <div class="col-12 mb-3">
              <label for="business_process" class="form-label">Business Process</label>
              <textarea class="form-control" id="business_process" name="business_process" rows="3" required>{{ old('business_process') }}</textarea>
            </div>
            
            <div class="col-12 mb-3">
              <label for="metode_pengujian" class="form-label">Metode Pengujian</label>
              <textarea class="form-control" id="metode_pengujian" name="metode_pengujian" rows="3" required>{{ old('metode_pengujian') }}</textarea>
            </div>
            
            <div class="col-12 mb-3">
              <label class="form-label">Key Control</label>
              <div class="table-responsive">
                <table class="table table-bordered" id="keyControlTable">
                  <thead>
                    <tr>
                      <th width="90%">Key Control</th>
                      <!-- <th width="20%">Aksi</th> -->
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Key control akan ditambahkan di sini -->
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
    
    // Ketika type berubah
    $('#type').on('change', function() {
      const type = $(this).val();
      const risikoSelect = $('#risiko_id');
      
      // Reset select dan table
      risikoSelect.empty().append('<option value="" selected disabled>Pilih Peristiwa Risiko</option>');
      $('#keyControlTable tbody').empty();
      
      if (type == 1) { // Unit
        // Tambahkan opsi dari IdentifikasiRisiko
        @foreach($identifikasiRisikos as $risiko)
          risikoSelect.append(new Option(
            '{{ str_replace(["\r", "\n"], " ", $risiko->peristiwa_risiko) }}', 
            '{{ $risiko->id }}'
          ));
        @endforeach
      } else if (type == 2) { // Proyek
        // Tambahkan opsi dari ProjectRisk
        @foreach($projectRisks as $risiko)
          @if($risiko->peristiwaRisiko)
            risikoSelect.append(new Option(
              '{{ str_replace(["\r", "\n"], " ", $risiko->peristiwaRisiko->title) }}', 
              '{{ $risiko->id }}'
            ));
          @endif
        @endforeach
      }
      
      // Refresh Select2
      risikoSelect.trigger('change');
    });
    
    // Ketika peristiwa risiko berubah
    $('#risiko_id').on('change', function() {
      const risikoId = $(this).val();
      const type = $('#type').val();
      
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
          if (response.data.length > 0) {
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
      {{-- 
      const row = `
        <tr>
          <td>
            <input type="hidden" name="key_control_id[]" value="${id}">
            <input type="text" class="form-control" name="key_control[]" value="${text}" readonly required>
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm btn-delete-row">Hapus</button>
          </td>
        </tr>
      `;
      --}}
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
      /*const row = `
        <tr>
          <td>
            <input type="hidden" name="key_control_id[]" value="0">
            <input type="text" class="form-control" name="key_control[]" readonly required>
          </td>
          <td>
            <button type="button" class="btn btn-danger btn-sm btn-delete-row">Hapus</button>
          </td>
        </tr>
      `;*/

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
    
    // Tambahkan baris key control baru
    $(document).on('click', '.btn-add-row', function() {
      addEmptyKeyControlRow();
    });
    
    // Hapus baris key control
    $(document).on('click', '.btn-delete-row', function() {
      const tbody = $('#keyControlTable tbody');
      $(this).closest('tr').remove();
      
      // Jika tidak ada baris tersisa, tambahkan baris kosong
      if (tbody.children().length === 0) {
        addEmptyKeyControlRow();
      }
    });
    
    // Tambahkan tombol untuk menambah baris baru
    //$('#keyControlTable').after('<button type="button" class="btn btn-info btn-sm mt-2 btn-add-row">Tambah Key Control</button>');
    
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