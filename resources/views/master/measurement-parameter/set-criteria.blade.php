@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-lg-10">
    @php
      $isEdit = isset($parameterCriteria) && $parameterCriteria->id;
      $formAction = $isEdit ? route('measurement-parameter.update-criteria', [$parameter->id, $parameterCriteria->id]) : route('measurement-parameter.store-criteria', $parameter->id);
    @endphp

    <form class="card needs-validation" id="formCriteria" novalidate="" method="POST" action="{{ $formAction }}">
      @csrf
      @if($isEdit) @method('PUT') @endif
      <div class="card-header d-flex flex-between-center">
        <h2 class="h3">{{ $isEdit ? 'Edit Kriteria Parameter' : 'Set Kriteria Parameter' }}</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <p class="text-dark">Dimensi: {{ $parameter?->subDimension?->dimension?->name ?? '-' }}</p>
          <p class="text-dark">Sub Dimensi: {{ $parameter?->subDimension?->name ?? '-' }}</p>
          <h5>Parameter: {{ $parameter->statement }}</h5>
        </div>

        <div class="alert alert-info">
          <ul class="mb-0">
            <li>Kriteria level 5 (Best Practice) wajib diisi</li>
            <li>Jika mengisi kriteria level tertentu, maka kriteria level selanjutnya wajib diisi</li>
            <li>Contoh: Jika mengisi level 2, maka level 3, 4, dan 5 wajib diisi</li>
            <li>Min Score tidak boleh lebih besar dari Max Score</li>
          </ul>
        </div>

        <!-- Input Score -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="min_score" class="form-label fw-bold">Min Score <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="min_score" name="min_score" min="1" max="5" value="{{ old('min_score', $parameterCriteria->min_score ?? 1) }}" required>
            </div>
            <div class="col-md-6">
                <label for="max_score" class="form-label fw-bold">Max Score <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="max_score" name="max_score" min="1" max="5" value="{{ old('max_score', $parameterCriteria->max_score ?? 5) }}" required>
            </div>
        </div>

        <div class="row gx-0 gy-4">
          <!-- Level 1: Initial Phase -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_1" name="criteria_statement[1]" rows="3"
                placeholder="Masukkan kriteria level 1 (Initial Phase)">{{ old('criteria_statement.1', $criteriaByLevel[1]->criteria ?? '') }}</textarea>
              <label for="criteria_statement_1">Kriteria Level 1: Initial Phase</label>
            </div>
          </div>

          <!-- Level 2: Emerging State -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_2" name="criteria_statement[2]" rows="3"
                placeholder="Masukkan kriteria level 2 (Emerging State)">{{ old('criteria_statement.2', $criteriaByLevel[2]->criteria ?? '') }}</textarea>
              <label for="criteria_statement_2">Kriteria Level 2: Emerging State</label>
            </div>
          </div>

          <!-- Level 3: Good Practice -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_3" name="criteria_statement[3]" rows="3"
                placeholder="Masukkan kriteria level 3 (Good Practice)">{{ old('criteria_statement.3', $criteriaByLevel[3]->criteria ?? '') }}</textarea>
              <label for="criteria_statement_3">Kriteria Level 3: Good Practice</label>
            </div>
          </div>

          <!-- Level 4: Strong Practice -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_4" name="criteria_statement[4]" rows="3"
                placeholder="Masukkan kriteria level 4 (Strong Practice)">{{ old('criteria_statement.4', $criteriaByLevel[4]->criteria ?? '') }}</textarea>
              <label for="criteria_statement_4">Kriteria Level 4: Strong Practice</label>
            </div>
          </div>

          <!-- Level 5: Best Practice (Wajib) -->
          <div class="col-12">
            <div class="form-floating has-validation">
              <textarea class="form-control" id="criteria_statement_5" name="criteria_statement[5]" rows="3"
                placeholder="Masukkan kriteria level 5 (Best Practice)" required>{{ old('criteria_statement.5', $criteriaByLevel[5]->criteria ?? '') }}</textarea>
              <label for="criteria_statement_5">Kriteria Level 5: Best Practice (Wajib)</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('measurement-parameter.show', $parameter->id) }}" class="btn btn-outline-secondary me-2">Batal</a>
        <button type="submit" id="btnSubmit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    $('#btnSubmit').on('click', function(e) {
      e.preventDefault(); // Mencegah submit otomatis

      let isValid = true;
      let errorMessage = '';

      let minScore = parseInt($('#min_score').val());
      let maxScore = parseInt($('#max_score').val());

      if(isNaN(minScore) || minScore < 1) {
          errorMessage += 'Min Score harus diisi angka minimal 1.<br>';
          isValid = false;
      }
      if(isNaN(maxScore) || maxScore < 1) {
          errorMessage += 'Max Score harus diisi angka minimal 1.<br>';
          isValid = false;
      }
      if(!isNaN(minScore) && !isNaN(maxScore) && maxScore < minScore) {
          errorMessage += 'Max Score tidak boleh lebih kecil dari Min Score.<br>';
          isValid = false;
      }

      // Cek apakah level 5 diisi
      if (!$('#criteria_statement_5').val().trim()) {
        $('#criteria_statement_5').addClass('is-invalid');
        errorMessage += 'Kriteria level 5 (Best Practice) wajib diisi.<br>';
        isValid = false;
      }

      if (isValid) {
        Swal.fire({
          title: 'Konfirmasi',
          html: 'Apakah Anda yakin ingin menyimpan kriteria ini?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, Simpan',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            $('#formCriteria').submit();
          }
        });
      } else {
        Swal.fire({
          title: 'Validasi Gagal',
          html: errorMessage,
          icon: 'error',
          confirmButtonText: 'OK'
        });
      }
    });

    $('textarea').on('input', function() {
      $(this).removeClass('is-invalid');
    });

    @if($errors->any())
      Swal.fire({
        title: 'Validasi Gagal',
        html: `@foreach($errors->all() as $error){{ $error }}<br>@endforeach`,
        icon: 'error',
        confirmButtonText: 'OK'
      });
    @endif
  });
</script>
@endpush
@endsection
