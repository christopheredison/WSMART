@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center gap-3">
          <div class="lead__icon bg-warning-subtle">
            <div class="svg-icon svg-icon-warning">
              @include('partials.icon-layer')
            </div>
          </div>
          <h2 class="h3">Periode</h2>
          <div id="bulk-select-replace-element" class="col-auto ms-auto">
            <a class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#newPeriode">
              <span class="bx bx-plus"></span>
              <span class="ms-1">Tambah Data Periode</span>
            </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-0 mb-3">
          <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <form action="{{ route('change-active-period') }}" method="POST">
              @csrf
              <div class="btn-group w-100">
                <select class="form-select js-select-hide-search" name="periode" id="periode">
                  <option selected disabled>Periode aktif</option>
                  @foreach ($periode as $item)
                  <option value="{{ $item->id }}">{{ $item->tahun }}</option>
                  @endforeach
                </select>
                <button type="submit" class="btn btn-secondary">Ganti Status</button>
              </div>
            </form>
          </div>
        </div>
        <div class="table-responsive-sm scrollbar">
          <table class="table table-hover mb-md-0">
            <thead>
              <tr>
                <th class="white-space-nowrap" data-sort="no">#</th>
                <th>Tahun</th>
                <th class="text-center" data-sort="status">Status</th>
                <th class="white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($periode as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="tahun">{{ $item->tahun }}</td>
                <td class="status text-center">
                  <figure class="badge bg-success">
                    @if($item->status == 'active')
                    Aktif
                    @elseif($item->status == 'non-active')
                    Tidak Aktif
                    @endif
                  </figure>
                </td>
                <td class="white-space-nowrap">
                  @if ($item->trashed())
                  <button type="submit" class="btn-input-icon ps-0" data-bs-toggle="modal"
                    data-bs-target="#modalRestore{{ $item->id }}">
                    <span class="bx bx-undo" data-bs-toggle="tooltip" title="Undo"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->tahun;
                  $formAction = route('periode.restore', $item->id);
                  @endphp
                  @include('partials.modal-restore-alert')
                  @else
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal" data-bs-target="#modalAmbangBatas{{ $item->id }}" data-periode-id="{{ $item->id }}" title="Atur Ambang Batas">
                    <span class="bx bx-slider text-info"></span>
                  </button>
                  <button type="button" class="btn-input-icon btn-action" data-action="risk-limit" data-periode-id="{{ $item->id }}" title="Atur Risk Limit">
                    <span class="bx bx-slider-alt text-warning"></span>
                  </button>
                  <a href="{{ route('periode.edit', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Edit">
                    <span class="bx bx-edit"></span>
                  </a>
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $item->id }}">
                    <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->tahun;
                  $formAction = route('periode.destroy', $item->id);
                  @endphp
                  @include('partials.modal-delete-alert')
                  @endif
                </td>
              </tr>
              <!-- Modal Ambang Batas -->
              <div class="modal fade" id="modalAmbangBatas{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="modalAmbangBatasLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog modal-md" role="document">
                  <div class="modal-content">
                    <div class="modal-header flex-between-center">
                      <h2 class="h4">Pengaturan Ambang Batas Risiko - Periode {{ $item->tahun }}</h2>
                      <div class="lead__icon lead__icon_sm">
                        <div class="svg-icon svg-icon-secondary">
                          @include('partials.icon-tool')
                        </div>
                      </div>
                    </div>
                    <form id="formAmbangBatas" method="POST" action="{{ route('periode.update-ambang-batas', $item->id) }}">
                      @csrf
                      @method('PUT')
                      <div class="modal-body">
                        <div class="form-group mb-3">
                          <label class="form-label" for="nilai_kapasitas_risiko">Nilai Kapasitas Risiko</label>
                          <input type="text" class="form-control inputmask-rupiah" id="nilai_kapasitas_risiko" name="nilai_kapasitas_risiko" 
                                 value="{{ old('nilai_kapasitas_risiko', $item->ambangBatasRisiko->nilai_kapasitas_risiko ?? '') }}" 
                                 step="1" required>
                        </div>
                        <div class="form-group mb-3">
                          <label class="form-label" for="nilai_selera_risiko">Nilai Selera Risiko</label>
                          <input type="text" class="form-control inputmask-rupiah" id="nilai_selera_risiko" name="nilai_selera_risiko" 
                                 value="{{ old('nilai_selera_risiko', $item->ambangBatasRisiko->nilai_selera_risiko ?? '') }}" 
                                 step="1" required>
                        </div>
                        <div class="form-group mb-3">
                          <label class="form-label" for="nilai_toleransi_risiko">Nilai Toleransi Risiko</label>
                          <input type="text" class="form-control inputmask-rupiah" id="nilai_toleransi_risiko" name="nilai_toleransi_risiko" 
                                 value="{{ old('nilai_toleransi_risiko', $item->ambangBatasRisiko->nilai_toleransi_risiko ?? '') }}" 
                                 step="1" required>
                        </div>
                        <div class="form-group mb-3">
                          <label class="form-label" for="nilai_batasan_risiko">Nilai Batasan Risiko</label>
                          <input type="text" class="form-control inputmask-rupiah" id="nilai_batasan_risiko" name="nilai_batasan_risiko" 
                                 value="{{ old('nilai_batasan_risiko', $item->ambangBatasRisiko->nilai_batasan_risiko ?? '') }}" 
                                 step="1" required>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-submit">Simpan</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal New Periode -->
<div class="modal fade" id="newPeriode" tabindex="-1" role="dialog" aria-labelledby="newPeriodeLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header flex-between-center">
        <h2 class="h4">Buat Periode Baru</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('periode.store') }}">
        @csrf
        <div class="modal-body">
          <div class="form-group d-flex">
            <label class="form-label label-start col-4" for="tahun">Tahun</label>
            <input class="form-control" id="tahun" name="tahun" type="number" placeholder="Masukkan Tahun"
              value="{{ old('tahun') }}" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Risk Limit -->
<div class="modal fade" id="modalRiskLimit" tabindex="-1" role="dialog" aria-labelledby="modalRiskLimitLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header flex-between-center">
        <h2 class="h4">Set Risk Limit</h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('periode.update-risk-limit', ':id') }}" id="formRiskLimit">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <table class="table table-hover mb-md-0">
            <thead>
              <tr>
                <th>Unit</th>
                <th>Risk Limit</th>
              </tr>
            </thead>
            <tbody>
              @foreach($unitWithRiskLimit as $unit)
              <tr>
                <td>{{ $unit->name }}</td>
                <td>
                  <input type="text" class="form-control inputmask-rupiah" id="risk_limit_{{ $unit->id }}" name="risk_limits[{{ $unit->id }}]" required>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
@section('scripts')
<script src="{{ asset('vendors/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
let periodes = @json($periode);
// Get badge status
var elList = Array.prototype.slice.call(
  document.querySelectorAll("figure"));

elList.forEach(function(el) {
  if (el.textContent.indexOf("Tidak Aktif") > -1) {
    console.log(el);
    el.classList.remove("bg-success");
    el.classList.add("tx-g400");
  }
});
</script>
<script>
// Tambahkan fungsi untuk mengambil data ambang batas 
function getAmbangBatas(periodeId) { 
    fetch(`/periode/${periodeId}/ambang-batas`) 
        .then(response => response.json()) 
        .then(data => { 
            if (data) { 
                document.getElementById('nilai_kapasitas_risiko').value = data.nilai_kapasitas_risiko; 
                document.getElementById('nilai_selera_risiko').value = data.nilai_selera_risiko; 
                document.getElementById('nilai_toleransi_risiko').value = data.nilai_toleransi_risiko; 
                document.getElementById('nilai_batasan_risiko').value = data.nilai_batasan_risiko; 
            } 
        }); 
} 

// Tambahkan event listener untuk modal 
document.querySelectorAll('[data-bs-target^="#modalAmbangBatas"]').forEach(button => { 
    button.addEventListener('click', function() { 
        const periodeId = this.getAttribute('data-periode-id'); 
        getAmbangBatas(periodeId); 
    }); 
});

// Tambahkan event listener untuk form submit dengan SweetAlert
document.querySelector('#formAmbangBatas').addEventListener('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin untuk melakukan update data?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            // Unmask all inputmask fields before submitting
            $('.inputmask-rupiah').each(function() {
                $(this).inputmask('remove');
            });
            this.submit();
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    $('.inputmask-rupiah').inputmask({
        alias: 'numeric',
        groupSeparator: '.',
        autoGroup: true,
        digits: 0,
        digitsOptional: false,
        prefix: '',
        placeholder: '0',
        rightAlign: false,
        autoUnmask: true,
        removeMaskOnSubmit: true,
        min: 0,
        allowMinus: false,
        onKeyDown: function(e) {
        if (e.key === 'Backspace' || e.keyCode === 8) {
            // tunda eksekusi sampai mask selesai di-apply
            setTimeout(() => {
                const unmasked = this.inputmask.unmaskedvalue();
                // kalau masih ada angka tersisa
                if (unmasked.length > 0) {
                // cek posisi cursor
                const pos = this.selectionStart;
                if (pos === 0) {
                    // pindahkan ke paling kanan
                    const end = this.value.length;
                    this.setSelectionRange(end, end);
                }
                }
            }, 0);
            }
        }
    });

    $('.btn-action').on('click', function() {
        const periodeId = $(this).data('periode-id');
        const action = $(this).data('action');
        if (action === 'risk-limit') {
            $('#formRiskLimit').attr('action', '{{ route('periode.update-risk-limit', ':id') }}'.replace(':id', periodeId));
            let periode = periodes.find(periode => periode.id === periodeId);
            @foreach($unitWithRiskLimit as $unit)
            $('#risk_limit_{{ $unit->id }}').val((periode.risk_limit_periodes.find(riskLimit => riskLimit.unit_id === {{ $unit->id }})?.risk_limit) || 0);
            @endforeach
            $('#modalRiskLimit').modal('show');
        }
    });
});
</script>
@endsection
