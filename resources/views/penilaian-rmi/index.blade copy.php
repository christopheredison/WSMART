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
          <h2 class="h3">Penilaian RMI</h2>
        </div>
      </div>
      <div class="card-body">
        <div class="position-relative">
          <div class="row row-bulk-select g-2 mb-3">
            <div class="col-md-6">
              <form action="{{ route('penilaian-rmi.index') }}" method="GET" class="d-flex">
                <input type="text" name="keyword" class="form-control me-2" placeholder="Cari periode RMI..." value="{{ request('keyword') }}">
                <button type="submit" class="btn btn-primary">Filter</button>
              </form>
            </div>
          </div>
        </div>
        <table class="table table-hover" id="penilaian-rmi-table">
          <thead>
            <tr>
              <th class="white-space-nowrap">#</th>
              <th>Tahun Periode RMI</th>
              <th>Status</th>
              <th>Score RMI</th>
              <th>Score RMI Deskripsi</th>
              <th>Tanggal Update</th>
              <th class="no-sort white-space-nowrap">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($periods as $index => $period)
            <tr>
              <td class="index-number">{{ $index + 1 }}</td>
              <td>{{ $period->year }}</td>
              <td>
                @if($period->status == 1)
                  <span class="badge bg-warning">Dalam Proses</span>
                @else
                  <span class="badge bg-success">Selesai</span>
                @endif
              </td>
              <td class="text-center">{{ $period->score_rmi ?? '-' }}</td>
              <td>{{ $period->score_rmi_desc ?? 'Belum Dinilai' }}</td>
              <td>{{ $period->updated_at->format('d M Y H:i') }}</td>
              <td class="white-space-nowrap no-sort">
                <a href="{{ route('penilaian-rmi.show', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                  title="Lihat Detail">
                  <span class="bx bx-show"></span>
                </a>
                <!-- <a href="#" class="btn-input-icon" data-bs-toggle="tooltip"
                  title="Set Kriteria Parameter">
                  <span class="bx bx-list-check"></span>
                </a> -->
                <a href="{{ route('penilaian-rmi.aspek-dinamis', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                  title="Penilaian Aspek Dinamis">
                  <span class="bx bx-bar-chart-alt-2"></span>
                </a>
                <a href="{{ route('penilaian-rmi.aspek-kinerja', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                  title="Penilaian Aspek Kinerja">
                  <span class="bx bx-line-chart"></span>
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    // Inisialisasi DataTable dengan ID yang spesifik
    if ($.fn.DataTable.isDataTable('#penilaian-rmi-table')) {
      $('#penilaian-rmi-table').DataTable().destroy();
    }
    
    $('#penilaian-rmi-table').DataTable({
      responsive: true,
      lengthChange: false,
      pageLength: 10,
      columnDefs: [
        { orderable: false, targets: 'no-sort' }
      ]
    });
    
    // Tooltip
    $('[data-bs-toggle="tooltip"]').tooltip();
  });
</script>
@endpush