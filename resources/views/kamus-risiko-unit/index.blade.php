@extends('layouts.default')

@section('dashboard')

<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-3">
      <div class="bg-info-subtle p-2 rounded-4">
        <div class="lead__icon">
          <div class="svg-icon svg-icon-2x svg-icon-info">
            @include('partials.icon-layer')
          </div>
        </div>
      </div>
      <div class="d-block">
        <h2>Kamus Risiko Divisi</h2>
        {{-- @if ($period)
          <div class="ff-preheading">Periode: {{ $period ? $period->periode : '' }}</div>
        @endif --}}
      </div>
    </div>
    <div class="card-body">
        <form id="filter-form">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="unit_id" class="form-label">Divisi</label>
                    <select name="unit_id" id="unit_id" class="form-select select2">
                        <option value="">Semua Divisi</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="peristiwa_risiko" class="form-label">Peristiwa Risiko</label>
                    <input type="text" name="peristiwa_risiko" id="peristiwa_risiko" class="form-control" placeholder="Peristiwa risiko...">
                </div>
                <div class="col-md-4">
                    <label for="jenis_risiko_id" class="form-label">Taksonomi Risiko</label>
                    <select name="jenis_risiko_id" id="jenis_risiko_id" class="form-select select2">
                        <option value="">Semua Taksonomi Risiko</option>
                        @foreach($jenisRisikos as $jenis)
                            <option value="{{ $jenis->id }}">{{ $jenis->kategoriRisiko->title }} - {{ $jenis->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="level_risiko" class="form-label">Level Risiko Inheren</label>
                    <select name="level_risiko" id="level_risiko" class="form-select select2">
                        <option value="">Semua Level Risiko</option>
                        @foreach($levelRisikos as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label for="deskripsi_risiko" class="form-label">Deskripsi Risiko</label>
                    <input type="text" name="deskripsi_risiko" id="deskripsi_risiko" class="form-control" placeholder="Cari berdasarkan deskripsi...">
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-between">
              <a href="{{ route('kamus-risiko-unit.export') }}" id="export-excel-btn" class="btn btn-sm btn-danger">
                  <span class="bx bxs-file-export me-1"></span> Export to Excel
              </a>
              <div class="d-flex justify-content-center gap-2">
                  <button type="button" id="reset-filter-btn" class="btn btn-sm btn-secondary">Reset Filter</button>
                  <button type="submit" class="btn btn-sm btn-primary">Filter Data Risiko</button>
              </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="kamus-risiko-table" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Action</th>
                        <th>Divisi</th>
                        <th>Taksonomi Risiko</th>
                        <th>Peristiwa Risiko</th>
                        <th>Deskripsi Peristiwa Risiko</th>
                        <th>Nilai Dampak Inheren</th>
                        <th>Skala Dampak Inheren</th>
                        <th>Nilai Probabilitas Inheren</th>
                        <th>Eksposur Risiko Inheren</th>
                        <th>Level Risiko Inheren</th>
                        <th>Realisasi Nilai Dampak</th>
                        <th>Realisasi Skala Dampak</th>
                        <th>Realisasi Skala Probabilitas</th>
                        <th>Realisasi Level Risiko</th>
                        <th>Realisasi Eksposur Risiko</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="modal fade" id="selectUnitModal" aria-labelledby="selectUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="selectUnitModalLabel">Pilih Divisi Tujuan</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-risk-id" value="">
                
                <p>Pilih divisi di mana Anda ingin menambahkan risiko ini.</p>
                <div class="form-group">
                    <label for="modal-unit-select" class="form-label">Divisi Tujuan</label>
                    <select id="modal-unit-select" class="form-select select2" style="width: 100%;">
                        <option value="" selected disabled>Pilih divisi...</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    <div id="unit-select-error" class="text-danger small mt-2 d-none">
                        Anda harus memilih divisi tujuan.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirm-unit-selection-btn">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const allUnits = @json($units);

    $('#modal-unit-select').select2({
        dropdownParent: $('#selectUnitModal')
    });

    // Initialize DataTable
    var table = $('#kamus-risiko-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('kamus-risiko-unit.index') }}",
            data: function (d) {
                d.unit_id = $('#unit_id').val();
                d.peristiwa_risiko = $('#peristiwa_risiko').val();
                d.jenis_risiko_id = $('#jenis_risiko_id').val();
                d.level_risiko = $('#level_risiko').val();
                d.deskripsi_risiko = $('#deskripsi_risiko').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'divisi', name: 'identifikasiRisiko.unit.name' },
            { data: 'taksonomi_risiko', name: 'identifikasiRisiko.jenisRisiko.title', orderable: false, searchable: false },
            { data: 'peristiwa_risiko', name: 'identifikasiRisiko.peristiwa_risiko' },
            { data: 'deskripsi_peristiwa_risiko', name: 'identifikasiRisiko.deskripsi_peristiwa_risiko' },
            { data: 'nilai_dampak_inheren', name: 'identifikasiRisiko.riskAnalysis.nilai_dampak' },
            { data: 'skala_dampak_inheren', name: 'identifikasiRisiko.riskAnalysis.skala_dampak' },
            { data: 'nilai_probabilitas_inheren', name: 'identifikasiRisiko.riskAnalysis.nilai_probabilitas' },
            { data: 'eksposur_risiko_inheren', name: 'identifikasiRisiko.riskAnalysis.eksposur_risiko' },
            { data: 'level_risiko_inheren', name: 'identifikasiRisiko.riskAnalysis.level_risiko' },
            { data: 'realisasi_nilai_dampak', name: 'identifikasiRisiko.riskAnalysis.nilai_dampak_residual' },
            { data: 'realisasi_skala_dampak', name: 'identifikasiRisiko.riskAnalysis.skala_dampak_residual' },
            { data: 'realisasi_skala_probabilitas', name: 'identifikasiRisiko.riskAnalysis.skala_probabilitas_residual' },
            { data: 'realisasi_level_risiko', name: 'identifikasiRisiko.riskAnalysis.level_risiko_residual' },
            { data: 'realisasi_eksposur_risiko', name: 'identifikasiRisiko.riskAnalysis.eksposur_risiko_residual' },
        ],
        order: [[2, 'asc']] // Default order by Divisi name
    });

    // Filter button logic
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    // Reset Filter button logic
    $('#reset-filter-btn').on('click', function() {
        $('#filter-form').trigger('reset');
        $('.select2').val(null).trigger('change');
        table.draw();
    });

    // Export excel
    $('#export-excel-btn').on('click', function(e) {
        e.preventDefault();

        const filterParams = $('#filter-form').serialize();
        const baseUrl = $(this).attr('href');
        const downloadUrl = baseUrl + '?' + filterParams;
        window.location.href = downloadUrl;
    });

    // Action button "Ambil Risiko"
    $('#kamus-risiko-table').on('click', '.btn-ambil-risiko', function () {
        const riskId = $(this).data('id');
        const rowData = table.row($(this).closest('tr')).data();
        const riskDescription = rowData.deskripsi_peristiwa_risiko;

        $('#modal-risk-id').val(riskId);
        $('#selectUnitModal').data('risk-description', riskDescription);

        // Reset pilihan
        $('#modal-unit-select').val(null).trigger('change');
        $('#unit-select-error').addClass('d-none');

        unitSelectModal = new bootstrap.Modal(document.getElementById('selectUnitModal'));
        unitSelectModal.show();
    });

    $('#confirm-unit-selection-btn').on('click', function() {
        const targetUnitId = $('#modal-unit-select').val();
        const originalRiskId = $('#modal-risk-id').val();
        const riskDescription = $('#selectUnitModal').data('risk-description');

        // Validasi
        if (!targetUnitId) {
            $('#unit-select-error').removeClass('d-none');
            return;
        }
        
        // unitSelectModal.hide();

        const targetUnit = allUnits.find(p => p.id == targetUnitId);
        const targetUnitName = targetUnit ? targetUnit.name : 'N/A';
        
        Swal.fire({
            title: 'Konfirmasi Ambil Risiko',
            html: `Anda yakin ingin menyalin risiko <br><b>"${riskDescription}"</b><br> ke proyek <br><b>"${targetUnitName}"</b>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Tambahkan!',
            cancelButtonText: 'Batal'
        }).then((confirmResult) => {
            if (confirmResult.isConfirmed) {
                unitSelectModal.hide();
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                $.ajax({
                    url: "{{ route('kamus-risiko-unit.add-risk') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        original_risk_id: originalRiskId,
                        target_unit_id: targetUnitId
                    },
                    success: function(response) {
                        Swal.fire({ 
                          icon: 'success', 
                          title: 'Berhasil!', 
                          text: response.message
                        }).then(() => {
                          // const redirectUrlTemplate = "{{ route('projects.risks.index', ['project' => ':projectId']) }}";
                          // const redirectUrl = redirectUrlTemplate.replace(':projectId', response.redirect_project_id);
                          // window.localtion.href = redirectUrl;
                        });
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: errorMsg });
                    }
                });
            }
        });
    });

});
</script>
@endpush