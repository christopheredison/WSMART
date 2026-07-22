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
            <form action="{{ route('change-active-period') }}" method="POST" class="no-swal">
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
                    <span class="bx bx-slider text-info" data-bs-toggle="tooltip" title="Atur Ambang Batas"></span>
                  </button>
                  <button type="button" class="btn-input-icon btn-action" data-action="risk-limit" data-periode-id="{{ $item->id }}" title="Atur Risk Limit">
                    <span class="bx bx-slider-alt text-warning" data-bs-toggle="tooltip" title="Atur Risk Limit"></span>
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
        <h2 class="h4">Set Risk Limit - Periode <span id="periode-tahun-title"></span></h2>
        <div class="lead__icon lead__icon_sm">
          <div class="svg-icon svg-icon-secondary">
            @include('partials.icon-tool')
          </div>
        </div>
      </div>
      <form method="POST" action="{{ route('periode.update-risk-limit', ':id') }}" id="formRiskLimit">
        @csrf
        @method('PUT')
        <div id="hidden-inputs-container">
            @foreach ($unitsType1 as $unit)
            <input type="hidden" class="holder-risk-limit" name="risk_limits[{{ $unit->id }}]" id="holder_risk_limit_{{ $unit->id }}">
            @endforeach
            @foreach ($unitsType2 as $unit)
            <input type="hidden" class="holder-risk-limit" name="risk_limits[{{ $unit->id }}]" id="holder_risk_limit_{{ $unit->id }}">
            @endforeach
        </div>

        <div class="modal-body">
          <ul class="nav nav-tabs" id="riskLimitTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="type1-tab" data-bs-toggle="tab" data-bs-target="#type1" type="button" role="tab" aria-controls="type1" aria-selected="true">Divisi</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="type2-tab" data-bs-toggle="tab" data-bs-target="#type2" type="button" role="tab" aria-controls="type2" aria-selected="false">Anak Perusahaan</button>
            </li>
          </ul>

          <div class="tab-content pt-3">
            <div class="tab-pane active" id="type1" role="tabpanel" aria-labelledby="type1-tab">
              <table class="table table-hover mb-md-0" id="tableRiskLimitType1" style="width:100%">
                <thead>
                  <tr>
                    <th>Unit</th>
                    <th>Risk Limit</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($unitsType1 as $unit)
                  <tr>
                    <td>{{ $unit->name }}</td>
                    <td>
                      <input type="text" class="form-control inputmask-rupiah input-risk-limit" id="risk_limit_input_{{ $unit->id }}" data-unit-id="{{ $unit->id }}">
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div class="tab-pane" id="type2" role="tabpanel" aria-labelledby="type2-tab">
              <table class="table table-hover mb-md-0" id="tableRiskLimitType2" style="width:100%">
                <thead>
                  <tr>
                    <th>Unit</th>
                    <th>Risk Limit</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($unitsType2 as $unit)
                  <tr>
                    <td>{{ $unit->name }}</td>
                    <td>
                      <input type="text" class="form-control inputmask-rupiah input-risk-limit" id="risk_limit_input_{{ $unit->id }}" data-unit-id="{{ $unit->id }}">
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
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

    $('.btn-action[data-action="risk-limit"]').on('click', function() {
      const periodeId = $(this).data('periode-id');
      const form = $('#formRiskLimit');
      const modal = $('#modalRiskLimit');
      const selectedPeriode = periodes.find(p => p.id === periodeId);

      if (!selectedPeriode) {
        console.error('Data periode tidak ditemukan untuk ID:', periodeId);
        return;
      }

      // Set action URL form secara dinamis
      const actionUrl = '{{ route('periode.update-risk-limit', ':id') }}'.replace(':id', periodeId);
       form.attr('action', actionUrl);

      // Set judul modal
      modal.find('#periode-tahun-title').text(selectedPeriode.tahun);

      $(tableRiskLimitType1.rows().nodes()).find('.input-risk-limit').val('');
      $(tableRiskLimitType2.rows().nodes()).find('.input-risk-limit').val('');

      // Reset input yang tersembunyi
      form.find('.holder-risk-limit').val('');

      // Isi input dengan data yang sudah ada
      if (selectedPeriode.risk_limit_periodes && selectedPeriode.risk_limit_periodes.length > 0) {
          selectedPeriode.risk_limit_periodes.forEach(function(riskLimit) {
              // Cari input di SEMUA HALAMAN DataTables, bukan hanya yang terlihat
              const inputInTable1 = $(tableRiskLimitType1.rows().nodes()).find(`#risk_limit_input_${riskLimit.unit_id}`);
              const inputInTable2 = $(tableRiskLimitType2.rows().nodes()).find(`#risk_limit_input_${riskLimit.unit_id}`);

              if (inputInTable1.length) {
                  inputInTable1.val(riskLimit.risk_limit);
              }
              if (inputInTable2.length) {
                  inputInTable2.val(riskLimit.risk_limit);
              }

              // Isi juga input yang tersembunyi (ini sudah benar)
              $(`#holder_risk_limit_${riskLimit.unit_id}`).val(riskLimit.risk_limit);
          });
      }
      // Memicu re-apply mask setelah nilai di set
      $('.inputmask-rupiah').trigger('input');
      modal.modal('show');
    });

    $('#formRiskLimit').on('input', '.input-risk-limit', function() {
        const unitId = $(this).data('unit-id');
        const unmaskedValue = $(this).inputmask('unmaskedvalue');

        // Targetkan input tersembunyi yang benar menggunakan ID
        const targetHiddenInput = $('#holder_risk_limit_' + unitId);

        if(targetHiddenInput.length) {
            targetHiddenInput.val(unmaskedValue);
        }
    });

    $('.input-risk-limit').on('input', function() {
      const name = $(this).attr('name');
      $('.holder-risk-limit[name="' + name + '"]').val($(this).val());
    });

    let tableRiskLimitType1 = $('#tableRiskLimitType1').DataTable({
        paging: true,
        searching: true,
        info: true,
        order: [],
        lengthChange: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ orderable: false, targets: [0, 1] }],
    });
    let tableRiskLimitType2 = $('#tableRiskLimitType2').DataTable({
        paging: true,
        searching: true,
        info: true,
        order: [],
        lengthChange: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ orderable: false, targets: [0, 1] }],
    });
    $('#formRiskLimit').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah Anda yakin untuk menyimpan perubahan Risk Limit?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
