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
              <label for="sasaran_bumn" class="form-label">Sasaran BUMN</label>
              <textarea class="form-control" id="sasaran_bumn" name="sasaran_bumn" rows="3" required>{{ old('sasaran_bumn', $ictPlan->sasaran_bumn) }}</textarea>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="type" class="form-label">Type</label>
              <select class="form-select" id="type" name="type" required>
                <option value="" disabled>Pilih Type</option>
                @foreach($types as $key => $value)
                  <option value="{{ $key }}" {{ old('type', $ictPlan->type) == $key ? 'selected' : '' }}>{{ $value }}</option>
                @endforeach
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="risiko_id" class="form-label">Peristiwa Risiko</label>
              <select class="form-select select2" id="risiko_id" name="risiko_id" required>
                <option value="" disabled>Pilih Peristiwa Risiko</option>
                @if($ictPlan->type == 1)
                    @foreach($identifikasiRisikos as $risiko)
                        <option value="{{ $risiko->id }}" {{ $ictPlan->risiko_id == $risiko->id ? 'selected' : '' }}>
                            {{ $risiko->peristiwa_risiko }}
                        </option>
                    @endforeach
                @elseif($ictPlan->type == 2)
                    @foreach($projectRisks as $risiko)
                        @if($risiko->peristiwaRisiko)
                            <option value="{{ $risiko->id }}" {{ $ictPlan->risiko_id == $risiko->id ? 'selected' : '' }}>
                                {{ $risiko->peristiwaRisiko->title }}
                            </option>
                        @endif
                    @endforeach
                @endif
              </select>
            </div>
            
            <div class="col-12 mb-3">
              <label for="business_process" class="form-label">Business Process</label>
              <textarea class="form-control" id="business_process" name="business_process" rows="3" required>{{ old('business_process', $ictPlan->business_process) }}</textarea>
            </div>
            
            <div class="col-12 mb-3">
              <label for="metode_pengujian" class="form-label">Metode Pengujian</label>
              <textarea class="form-control" id="metode_pengujian" name="metode_pengujian" rows="3" required>{{ old('metode_pengujian', $ictPlan->metode_pengujian) }}</textarea>
            </div>
            
            <div class="col-12 mb-3">
              <label class="form-label">Key Control</label>
              <div class="table-responsive">
                <table class="table table-bordered" id="keyControlTable">
                  <thead>
                    <tr>
                      <th width="90%">Key Control</th>
                      {{-- <th width="10%">Aksi</th> --}}
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($ictPlan->planControls as $control)
                    <tr>
                        <td>
                            <input type="hidden" name="key_control_id[]" value="{{ $control->key_control_id }}">
                            <input type="hidden" name="ict_plan_control_id[]" value="{{ $control->id }}">
                            
                            <input type="text" class="form-control" name="key_control[]" value="{{ $control->key_control }}" readonly required>
                        </td>
                    </tr>
                    @endforeach
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
    
    // Simpan nilai awal risiko_id untuk mendeteksi perubahan
    let initialRisikoId = '{{ $ictPlan->risiko_id }}';
    let initialType = '{{ $ictPlan->type }}';

    // Handler Type Change (Sama seperti create)
    $('#type').on('change', function() {
      const type = $(this).val();
      const risikoSelect = $('#risiko_id');
      
      risikoSelect.empty().append('<option value="" selected disabled>Pilih Peristiwa Risiko</option>');
      
      // Jika user mengubah Type kembali ke awal, kembalikan data awal (opsional)
      // Tapi biasanya user ingin ganti total, jadi kita load ulang list
      
      if (type == 1) { 
        @foreach($identifikasiRisikos as $risiko)
          risikoSelect.append(new Option('{{ $risiko->peristiwa_risiko }}', '{{ $risiko->id }}'));
        @endforeach
      } else if (type == 2) { 
        @foreach($projectRisks as $risiko)
          @if($risiko->peristiwaRisiko)
            risikoSelect.append(new Option('{{ $risiko->peristiwaRisiko->title }}', '{{ $risiko->id }}'));
          @endif
        @endforeach
      }
      
      // Jika type sama dengan initial, set selected risiko kembali
      if(type == initialType) {
         risikoSelect.val(initialRisikoId).trigger('change.select2'); // Jangan trigger 'change' biasa agar tabel tidak terhapus dulu
      } else {
         $('#keyControlTable tbody').empty(); // Type beda, pasti kontrol beda -> hapus tabel
        risikoSelect.trigger('change');
      }
    });
    
    // Handler Peristiwa Risiko Change
    $('#risiko_id').on('change', function() {
      const risikoId = $(this).val();
      const type = $('#type').val();
      
      if (!risikoId) return;

      // CEK: Apakah risiko_id yang dipilih SAMA dengan yang ada di database saat load?
      if (risikoId == initialRisikoId && type == initialType) {
         // Jika sama, JANGAN load dari API. Biarkan tabel yang dirender server-side (Blade) tetap ada.
         // Ini penting agar ID ict_plan_control_id tidak hilang.
        return; 
      }
      
      // Jika BEDA, baru kita wipe tabel dan ambil dari API (karena struktur berubah)
      $('#keyControlTable tbody').empty();
      
      $.ajax({
        url: '/api/key-controls',
        type: 'GET',
        data: { type: type, risiko_id: risikoId },
        success: function(response) {
          if (response.data.length > 0) {
            response.data.forEach(function(item) {
              addKeyControlRow(item.id, item.kontrol_eksisting || item.kontrol_eksisting_desc);
            });
          } else {
            addEmptyKeyControlRow();
          }
        },
        error: function(xhr) {
            // handle error
        }
      });
    });
    
    // Fungsi Add Row (Tanpa ID existing, karena ini baru dari API)
    function addKeyControlRow(id, text) {
      const row = `
        <tr>
          <td>
            <input type="hidden" name="key_control_id[]" value="${id}">
            {{-- Tidak ada input hidden ict_plan_control_id[] karena ini baris baru --}}
            <input type="text" class="form-control" name="key_control[]" value="${text}" readonly required>
          </td>
        </tr>
      `;
      $('#keyControlTable tbody').append(row);
    }

    function addEmptyKeyControlRow() {
       // ... logic sama ...
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

    // Submit confirm
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