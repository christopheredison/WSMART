@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-lg-8">
    @php
      $isEdit = isset($parameter);
      $formAction = $isEdit ? route('measurement-parameter.update', $parameter->id) : route('measurement-parameter.store');
    @endphp
    <div class="card">
      <div class="card-header">
        <h2 class="h3 mb-0">{{ $isEdit ? 'Edit Parameter Pengukuran' : 'Tambah Parameter Pengukuran' }}</h2>
      </div>
      <form action="{{ $formAction }}" method="POST">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Dimensi</label>
            <select class="form-select" id="dimension_id" required>
              <option value="" disabled selected>Pilih Dimensi</option>
              @foreach($dimensions as $dim)
                @php $selectedDim = $isEdit && $parameter->subDimension->dimension_id == $dim->id ? 'selected' : ''; @endphp
                <option value="{{ $dim->id }}" {{ $selectedDim }}>{{ $dim->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Sub Dimensi</label>
            <select class="form-select" id="sub_dimension_id" name="sub_dimension_id" required disabled>
              <option value="" selected disabled>Pilih Dimensi Terlebih Dahulu</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Parameter Statement</label>
            <textarea class="form-control" name="statement" rows="3" required>{{ $isEdit ? $parameter->statement : '' }}</textarea>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
          <a href="{{ route('measurement-parameter.index') }}" class="btn btn-secondary me-2">Batal</a>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  $(document).ready(function() {
    const dimensionsData = @json($dimensions);
    const selectedSubId = "{{ $isEdit ? $parameter->sub_dimension_id : '' }}";

    function populateSubDimensions(dimensionId, selectedSub = null) {
      const subSelect = $('#sub_dimension_id');
      subSelect.empty().append('<option value="" selected disabled>Pilih Sub Dimensi</option>');

      if (dimensionId) {
        const dimData = dimensionsData.find(d => d.id == dimensionId);
        if (dimData && dimData.sub_dimensions.length > 0) {
          dimData.sub_dimensions.forEach(sub => {
            let isSelected = (selectedSub == sub.id) ? 'selected' : '';
            subSelect.append(`<option value="${sub.id}" ${isSelected}>${sub.name}</option>`);
          });
          subSelect.prop('disabled', false);
        } else {
          subSelect.empty().append('<option value="" disabled>Tidak ada Sub Dimensi</option>');
          subSelect.prop('disabled', true);
        }
      }
    }

    // Event on change
    $('#dimension_id').on('change', function() {
      populateSubDimensions($(this).val());
    });

    // On Load (Khusus saat Edit)
    if ($('#dimension_id').val()) {
      populateSubDimensions($('#dimension_id').val(), selectedSubId);
    }
  });
</script>
@endpush
@endsection
