@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')

<div class="row">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header">
        <div class="row justify-content-between">
          <div class="col d-flex align-items-center gap-3">
            <div class="bg-info-subtle p-2 rounded-4">
              <div class="lead__icon">
                <div class="svg-icon svg-icon-2x svg-icon-info">
                  <!-- icon tetap sesuai partial kamu -->
                  @include('partials.icon-abs05')
                </div>
              </div>
            </div>
            <div class="d-block">
              <div class="ff-preheading">Input Data</div>
              <h2>Sasaran &amp; Strategi Bisnis Risiko</h2>
            </div>
          </div>
          <div class="col-auto">
            @can('sasaran_strategi_create')
            <a class="btn btn-outline-info btn-sm p-2"
               href="{{ route('sasaran-strategi.create') }}">
              <span class="bx bx-plus"></span>
              <span class="ms-1">Tambah Sasaran</span>
            </a>
            @endcan
          </div>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th class="text-center" style="width:50px">No</th>
              <th>Sasaran</th>
              <th>Expected Result (Rp)</th>
              <th>Nilai Risiko (Rp)</th>
              <th>Nilai Limit Risiko</th>
              <th>Strategi Bisnis</th>
              <th>Status</th>
              <th style="width: 100px">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($sasarans as $idx => $sasaran)
              @php
                $strategis = $sasaran->strategiBisnis;
                $count     = $strategis->count() ?: 1;
                $bgClass = $idx % 2 === 0 ? 'table-light' : '';
              @endphp

              @if($strategis->isNotEmpty())
                @foreach($strategis as $i => $str)
                  <tr class="{{ $bgClass }}">
                    @if($i === 0)
                      <td class="text-center" rowspan="{{ $count }}">
                        {{ $idx + 1 }}
                      </td>
                      <td rowspan="{{ $count }}">
                        {{ $sasaran->sasaran }}
                      </td>
                      <td class="text-end pe-3" rowspan="{{ $count }}">
                        {{ number_format($sasaran->expected_result, 0, ',', '.') }}
                      </td>
                      <td class="text-end pe-3" rowspan="{{ $count }}">
                        {{ number_format($sasaran->risk_value, 0, ',', '.') }}
                      </td>
                      <td rowspan="{{ $count }}">
                        @foreach($sasaran->metrikStrategiRisiko->parameterMetriks as $p)
                          &minus; {{ $p->parameter }} ({{ $p->satuan_ukuran }}): 
                          {{ $p->nilai_batasan }}<br>
                        @endforeach
                      </td>
                    @endif

                    <td>{{ $str->strategi }}</td>
                    <td class="text-end">
                      <div class="d-flex justify-content-end align-items-center gap-2">
                        <span class="badge {{ $str->status === 1 ? 'bg-success' : ($str->status === 2 ? 'bg-danger' : 'bg-primary') }}">
                          @switch($str->status)
                            @case(1) Accept @break
                            @case(2) Avoid  @break
                            @default Unset  @break
                          @endswitch
                        </span>
                        <button type="button" 
                                class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" 
                                data-bs-target="#modalUpdateStatus"
                                data-id="{{ $str->id }}"
                                data-status="{{ $str->status }}">
                          <i class="bx bx-edit-alt"></i>
                        </button>
                      </div>
                    </td>
                    <td class="text-center">
                      @if($i === 0)
                        <form action="{{ route('sasaran-strategi.destroy', $sasaran->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus sasaran ini?')">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bx bx-trash"></i>
                          </button>
                        </form>
                      @endif
                    </td>
                  </tr>
                @endforeach
              @else
                <tr class="{{ $bgClass }}">
                  <td class="text-center">{{ $idx + 1 }}</td>
                  <td>{{ $sasaran->sasaran }}</td>
                  <td class="text-end pe-3">
                    {{ number_format($sasaran->expected_result, 0, ',', '.') }}
                  </td>
                  <td class="text-end pe-3">
                    {{ number_format($sasaran->risk_value, 0, ',', '.') }}
                  </td>
                  <td>
                    @foreach($sasaran->metrikStrategiRisiko->parameterMetriks as $p)
                      &minus; {{ $p->parameter }} ({{ $p->satuan_ukuran }}): 
                      {{ $p->nilai_batasan }}<br>
                    @endforeach
                  </td>
                  <td colspan="3" class="text-center text-muted">
                    — belum ada strategi —
                  </td>
                </tr>
              @endif
            @empty
              <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                  <i class="bx bx-info-circle me-2"></i>Belum ada data sasaran & strategi bisnis risiko
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Status Strategi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="" method="POST" id="formUpdateStatus">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
              <option value="">-- Pilih Status --</option>
              <option value="1">Accept</option>
              <option value="2">Avoid</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handler untuk modal update status
    $('#modalUpdateStatus').on('show.bs.modal', function(e) {
        var button = $(e.relatedTarget);
        var id = button.data('id');
        var status = button.data('status');
        
        var form = $(this).find('#formUpdateStatus');
        form.attr('action', '/strategi-bisnis/' + id);
        form.find('select[name="status"]').val(status);
    });
});
</script>
@endpush

@endsection
