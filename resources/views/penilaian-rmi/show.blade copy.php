@extends('layouts.default')
@section('dashboard')
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
          <h2 class="h3">Detail Penilaian RMI</h2>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <a href="{{ route('penilaian-rmi.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Kembali
          </a>
        </div>

        <!-- Informasi Periode RMI -->
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Informasi Periode RMI</h4>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <th width="40%">Tahun Periode</th>
                    <td>: {{ $period->year }}</td>
                  </tr>
                  <tr>
                    <th>Status</th>
                    <td>: 
                      @if($period->status == 1)
                        <span class="badge bg-warning">Dalam Proses</span>
                      @else
                        <span class="badge bg-success">Selesai</span>
                      @endif
                    </td>
                  </tr>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-borderless">
                  <tr>
                    <th width="40%">Score RMI</th>
                    <td>: {{ $period->score_rmi ?? '-' }}</td>
                  </tr>
                  <tr>
                    <th>Deskripsi Score</th>
                    <td>: {{ $period->score_rmi_desc ?? 'Belum Dinilai' }}</td>
                  </tr>
                  <tr>
                    <th>Tanggal Update</th>
                    <td>: {{ $period->updated_at->format('d M Y H:i') }}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Daftar Dimensi -->
        <div class="card mb-4">
          <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Daftar Dimensi</h4>
          </div>
          <div class="card-body">
            @forelse($dimensions as $dimension)
            <div class="dimension-item mb-4">
              <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                <h5 class="mb-0">{{ $dimension->name }}</h5>
                <div>
                  <span class="badge bg-primary">Score: {{ $dimension->score ?? '-' }}</span>
                  <span class="ms-2">{{ $dimension->score_desc ?? '' }}</span>
                </div>
              </div>

              <!-- Sub Dimensi -->
              @forelse($dimension->subDimensions as $subDimension)
              <div class="sub-dimension-item ms-4 mt-3">
                <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                  <h6 class="mb-0">{{ $subDimension->name }}</h6>
                  <div>
                    <span class="badge bg-info">Score: {{ $subDimension->score ?? '-' }}</span>
                    <span class="ms-2">{{ $subDimension->score_desc ?? '' }}</span>
                  </div>
                </div>

                <!-- Parameter -->
                @forelse($subDimension->parameters as $parameter)
                <div class="parameter-item ms-4 mt-2">
                  <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                    <h6 class="mb-0">{{ $parameter->name }}</h6>
                    <div>
                      <span class="badge bg-secondary">Score: {{ $parameter->score ?? '-' }}</span>
                      <span class="ms-2">{{ $parameter->score_desc ?? '' }}</span>
                    </div>
                  </div>

                  <!-- Kriteria -->
                  <div class="kriteria-list ms-4 mt-2">
                    <div class="table-responsive">
                      <table class="table table-bordered table-hover">
                        <thead class="table-light">
                          <tr>
                            <th width="5%">#</th>
                            <th>Kriteria</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse($parameter->criterias as $index => $criteria)
                          <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $criteria->name }}</td>
                          </tr>
                          @empty
                          <tr>
                            <td colspan="2" class="text-center">Tidak ada kriteria</td>
                          </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
                @empty
                <div class="alert alert-info ms-4 mt-2">
                  Tidak ada parameter untuk sub dimensi ini
                </div>
                @endforelse
              </div>
              @empty
              <div class="alert alert-info ms-4 mt-2">
                Tidak ada sub dimensi untuk dimensi ini
              </div>
              @endforelse
            </div>
            @empty
            <div class="alert alert-info">
              Tidak ada data dimensi untuk periode ini
            </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    // Tooltip
    $('[data-bs-toggle="tooltip"]').tooltip();
  });
</script>
@endpush