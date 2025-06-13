@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row justify-content-center g-3 g-xl-5">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-warning-subtle">
            <div class="svg-icon svg-icon-warning">
              @include('partials.icon-layer')
            </div>
          </div>
          <h2 class="h3">Parameter Metrik Strategi Risiko</h2>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <h4>Detail Metrik</h4>
          <div class="table-responsive">
            <table class="table table-bordered">
              <tr>
                <th width="200">Periode</th>
                <td>{{ $metrik->periode->tahun }}</td>
              </tr>
              <tr>
                <th>T2 & T3 KBUMN</th>
                <td>{{ $metrik->kategoriRisiko->title }} - {{ $metrik->JenisRisiko->title }}</td>
              </tr>
              <tr>
                <th>Risk Appetite Statement</th>
                <td>{{ $metrik->risk_appetite_statement }}</td>
              </tr>
            </table>
          </div>
        </div>

        <div class="mb-3">
          <button type="button" class="btn btn-info" onclick="addParameter()">
            <i class="bx bx-plus"></i> Tambah Parameter
          </button>
        </div>

        <form action="{{ route('metrik-strategi-risiko.store-parameter', $metrik->id) }}" method="POST" id="parameterForm">
          @csrf
          <div class="table-responsive">
            <table class="table table-bordered" id="parameterTable">
              <thead>
                <tr>
                  <th>Parameter</th>
                  <th>Satuan Ukuran</th>
                  <th>Nilai Batasan</th>
                  <th width="100">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($metrik->parameterMetriks as $parameter)
                <tr>
                  <td>
                    <input type="hidden" name="parameter_id[]" value="{{ $parameter->id }}">
                    <input type="text" class="form-control" name="parameter[]" 
                           value="{{ $parameter->parameter }}" required>
                  </td>
                  <td>
                    <input type="text" class="form-control" name="satuan_ukuran[]" 
                           value="{{ $parameter->satuan_ukuran }}">
                  </td>
                  <td>
                    <input type="text" class="form-control" name="nilai_batasan[]" 
                           value="{{ $parameter->nilai_batasan }}">
                  </td>
                  <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">
                      <i class="bx bx-trash"></i>
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="text-end mt-3">
            <a href="{{ route('metrik-strategi-risiko.index') }}" class="btn btn-outline-secondary me-2">
              <i class="bx bx-arrow-back"></i> Kembali
            </a>
            <button type="button" class="btn btn-primary" onclick="confirmSubmit()">
              <i class="bx bx-save"></i> Simpan Parameter
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function addParameter() {
  const newRow = `
    <tr>
      <td><input type="text" class="form-control" name="parameter[]" required></td>
      <td><input type="text" class="form-control" name="satuan_ukuran[]"></td>
      <td><input type="text" class="form-control" name="nilai_batasan[]"></td>
      <td>
        <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">
          <i class="bx bx-trash"></i>
        </button>
      </td>
    </tr>
  `;
  $('#parameterTable tbody').append(newRow);
}

function deleteRow(button) {
  $(button).closest('tr').remove();
}

function confirmSubmit() {
  Swal.fire({
    title: 'Konfirmasi',
    text: 'Apakah Anda yakin ingin menyimpan parameter ini?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Simpan',
    cancelButtonText: 'Batal',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('parameterForm').submit();
    }
  });
}
</script>
@endpush
@endsection