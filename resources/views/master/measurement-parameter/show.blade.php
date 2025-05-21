@extends('layouts.default')
@section('dashboard')
<div class="row justify-content-center">
  <div class="col-12 col-lg-10">
    <div class="card">
      <div class="card-header d-flex flex-between-center">
        <h2 class="h3">Detail Parameter Pengukuran</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <div class="row mb-3">
            <div class="col-md-3 fw-bold">Dimensi</div>
            <div class="col-md-9">{{ $parameter->subDimension->dimension->name ?? '-' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3 fw-bold">Sub Dimensi</div>
            <div class="col-md-9">{{ $parameter->subDimension->name ?? '-' }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3 fw-bold">Parameter</div>
            <div class="col-md-9">{{ $parameter->statement }}</div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3 fw-bold">Jumlah Kriteria</div>
            <div class="col-md-9">{{ $criteriaCount }}</div>
          </div>
        </div>
        
        <h4 class="mb-3">Kriteria Penilaian</h4>
        
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="bg-light">
              <tr>
                <th width="5%">No</th>
                <th width="19%">Kriteria Initial Phase</th>
                <th width="19%">Kriteria Emerging State</th>
                <th width="19%">Kriteria Good Practice</th>
                <th width="19%">Kriteria Strong Practice</th>
                <th width="19%">Kriteria Best Practice</th>
                <th width="5%">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($parameterCriterias as $index => $criteria)
              <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                  @if($criteria->details->where('level', 1)->first())
                    {{ $criteria->details->where('level', 1)->first()->criteria }}
                  @else
                    <span class="text-muted fst-italic">Tidak ada kriteria</span>
                  @endif
                </td>
                <td>
                  @if($criteria->details->where('level', 2)->first())
                    {{ $criteria->details->where('level', 2)->first()->criteria }}
                  @else
                    <span class="text-muted fst-italic">Tidak ada kriteria</span>
                  @endif
                </td>
                <td>
                  @if($criteria->details->where('level', 3)->first())
                    {{ $criteria->details->where('level', 3)->first()->criteria }}
                  @else
                    <span class="text-muted fst-italic">Tidak ada kriteria</span>
                  @endif
                </td>
                <td>
                  @if($criteria->details->where('level', 4)->first())
                    {{ $criteria->details->where('level', 4)->first()->criteria }}
                  @else
                    <span class="text-muted fst-italic">Tidak ada kriteria</span>
                  @endif
                </td>
                <td>
                  @if($criteria->details->where('level', 5)->first())
                    {{ $criteria->details->where('level', 5)->first()->criteria }}
                  @else
                    <span class="text-muted fst-italic">Tidak ada kriteria</span>
                  @endif
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-sm btn-danger delete-criteria" 
                          data-id="{{ $criteria->id }}" 
                          data-parameter-id="{{ $parameter->id }}">
                    <i class="bx bx-trash"></i>
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center">Belum ada kriteria yang ditambahkan</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer border-none flex-end-center">
        <a href="{{ route('measurement-parameter.index') }}" class="btn btn-outline-secondary me-2">
          <i class="bx bx-arrow-back"></i> Kembali
        </a>
        <a href="{{ route('measurement-parameter.set-criteria', $parameter->id) }}" class="btn btn-primary">
          <i class="bx bx-edit"></i> Set Kriteria
        </a>
      </div>
    </div>
  </div>
</div>
@section('scripts')
<script>
  $(document).ready(function() {
    // Inisialisasi tombol delete dengan SweetAlert
    $('.delete-criteria').on('click', function() {
      const criteriaId = $(this).data('id');
      const parameterId = $(this).data('parameter-id');
      
      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Kriteria yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          // Kirim request delete ke server
          window.location.href = `{{ url('master/measurement-parameter') }}/${parameterId}/delete-criteria/${criteriaId}`;
        }
      });
    });
  });
</script>
@endsection
@endsection