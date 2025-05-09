@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-lg-10">
    <form class="card needs-validation" id="formCriteria" novalidate="" method="POST" action="{{ route('measurement-parameter.store-criteria', $parameter->id) }}">
      @csrf
      <div class="card-header d-flex flex-between-center">
        <h2 class="h3">Set Kriteria Parameter</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <h5>Parameter: {{ $parameter->statement }}</h5>
          <p class="text-muted">Sub Dimensi: {{ $parameter->subDimension->name ?? '-' }}</p>
        </div>
        
        <div class="alert alert-info">
          <ul class="mb-0">
            <li>Kriteria level 5 (Best Practice) wajib diisi</li>
            <li>Jika mengisi kriteria level tertentu, maka kriteria level selanjutnya wajib diisi</li>
            <li>Contoh: Jika mengisi level 2, maka level 3, 4, dan 5 wajib diisi</li>
            <li>Min Score akan otomatis dihitung sebagai level terendah yang terisi dikurangi 1 (minimal 1)</li>
            <li>Max Score akan otomatis dihitung sebagai level tertinggi yang terisi (5)</li>
          </ul>
        </div>
        
        <div class="row gx-0 gy-4">
          <!-- Level 1: Initial Phase -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_1" name="criteria_statement[1]" rows="3" 
                placeholder="Masukkan kriteria level 1 (Initial Phase)">{{ $criteriaByLevel[1]->criteria_statement ?? '' }}</textarea>
              <label for="criteria_statement_1">Kriteria Level 1: Initial Phase</label>
              @error('criteria_statement.1')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <!-- Level 2: Emerging State -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_2" name="criteria_statement[2]" rows="3" 
                placeholder="Masukkan kriteria level 2 (Emerging State)">{{ $criteriaByLevel[2]->criteria_statement ?? '' }}</textarea>
              <label for="criteria_statement_2">Kriteria Level 2: Emerging State</label>
              @error('criteria_statement.2')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <!-- Level 3: Good Practice -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_3" name="criteria_statement[3]" rows="3" 
                placeholder="Masukkan kriteria level 3 (Good Practice)">{{ $criteriaByLevel[3]->criteria_statement ?? '' }}</textarea>
              <label for="criteria_statement_3">Kriteria Level 3: Good Practice</label>
              @error('criteria_statement.3')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <!-- Level 4: Strong Practice -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_4" name="criteria_statement[4]" rows="3" 
                placeholder="Masukkan kriteria level 4 (Strong Practice)">{{ $criteriaByLevel[4]->criteria_statement ?? '' }}</textarea>
              <label for="criteria_statement_4">Kriteria Level 4: Strong Practice</label>
              @error('criteria_statement.4')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <!-- Level 5: Best Practice (Wajib) -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_5" name="criteria_statement[5]" rows="3" 
                placeholder="Masukkan kriteria level 5 (Best Practice)" required>{{ $criteriaByLevel[5]->criteria_statement ?? '' }}</textarea>
              <label for="criteria_statement_5">Kriteria Level 5: Best Practice (Wajib)</label>
              @error('criteria_statement.5')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
          
          <!-- Hidden fields for min_score and max_score -->
          <input type="hidden" id="min_score" name="min_score" value="{{ $parameterCriteria->min_score ?? 0 }}">
          <input type="hidden" id="max_score" name="max_score" value="{{ $parameterCriteria->max_score ?? 5 }}">
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('measurement-parameter.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="button" id="btnSubmit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    // Validasi form client-side
    $('#btnSubmit').on('click', function(e) {
      let isValid = true;
      let errorMessage = '';
      
      // Cek apakah level 5 diisi
      if (!$('#criteria_statement_5').val().trim()) {
        $('#criteria_statement_5').addClass('is-invalid');
        errorMessage += 'Kriteria level 5 (Best Practice) wajib diisi.<br>';
        isValid = false;
      }
      
      // Cek validasi level berurutan
      for (let i = 1; i <= 4; i++) {
        if ($('#criteria_statement_' + i).val().trim()) {
          // Jika level ini diisi, cek level selanjutnya
          for (let j = i + 1; j <= 5; j++) {
            if (!$('#criteria_statement_' + j).val().trim()) {
              $('#criteria_statement_' + j).addClass('is-invalid');
              errorMessage += `Kriteria level ${j} wajib diisi karena level ${i} sudah diisi.<br>`;
              isValid = false;
            }
          }
          break;
        }
      }
      
      if (isValid) {
        // Hitung min_score dan max_score berdasarkan level yang terisi
        let lowestFilledLevel = 5; // Default ke level tertinggi
        let highestFilledLevel = 5; // Default ke level tertinggi (karena level 5 wajib diisi)
        
        // Cari level terendah yang terisi
        for (let i = 1; i <= 5; i++) {
          if ($('#criteria_statement_' + i).val().trim()) {
            lowestFilledLevel = i;
            break;
          }
        }
        
        // Set min_score = level terendah yang terisi dikurangi 1 (minimal 1)
        let minScore = Math.max(1, lowestFilledLevel - 1);
        $('#min_score').val(minScore);
        
        // Set max_score = level tertinggi yang terisi (selalu 5 karena level 5 wajib diisi)
        $('#max_score').val(highestFilledLevel);
        
        // Tampilkan Sweet Alert untuk konfirmasi
        Swal.fire({
          title: 'Konfirmasi',
          html: `Apakah Anda yakin ingin menyimpan kriteria ini?<br>
                Min Score: ${minScore}<br>
                Max Score: ${highestFilledLevel}`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, Simpan',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            // Submit form
            $('#formCriteria').submit();
          }
        });
      } else {
        // Tampilkan Sweet Alert untuk error validasi
        Swal.fire({
          title: 'Validasi Gagal',
          html: errorMessage,
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    });
    
    // Reset validasi saat input berubah
    $('textarea').on('input', function() {
      $(this).removeClass('is-invalid');
    });
    
    // Tampilkan Sweet Alert jika ada error dari server
    @if($errors->any())
      Swal.fire({
        title: 'Validasi Gagal',
        html: `@foreach($errors->all() as $error){{ $error }}<br>@endforeach`,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    @endif
    
    // Tampilkan Sweet Alert jika berhasil (dari session)
    @if(session('success'))
      Swal.fire({
        title: 'Berhasil',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonText: 'OK'
      });
    @endif
  });
</script>
@endpush
@endsection