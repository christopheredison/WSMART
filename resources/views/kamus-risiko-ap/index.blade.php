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
        <h2>Kamus Risiko Anak Perusahaan</h2>
      </div>
    </div>
    <div class="card-body">
        <form id="filter-form">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="unit_id" class="form-label">Anak Perusahaan</label>
                    <select name="unit_id" id="unit_id" class="form-select select2">
                        <option value="">Semua Anak Perusahaan</option>
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
                <div class="col-md-4">
                    <label for="deskripsi_risiko" class="form-label">Deskripsi Risiko</label>
                    <input type="text" name="deskripsi_risiko" id="deskripsi_risiko" class="form-control" placeholder="Cari berdasarkan deskripsi...">
                </div>
                <div class="col-md-4">
                    <label for="efektivitas" class="form-label">Efektivitas Risiko</label>
                    <select name="efektivitas" id="efektivitas" class="form-select select2">
                        <option value="">Semua Status</option>
                        <option value="efektif">Efektif</option>
                        <option value="tidak_efektif">Tidak Efektif</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-between">
              <button type="button" id="export-excel-btn" class="btn btn-sm btn-danger d-flex align-items-center">
                  <span id="export-icon" class="bx bxs-file-export me-1"></span>
                  <span id="export-text">Export to Excel</span>
              </button>
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
        <table class="table table-bordered table-striped table-responsive" id="kamus-risiko-table" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Action</th>
                    <th>Proyek</th>
                    <th>Taksonomi Risiko</th>
                    <th>Peristiwa Risiko</th>
                    <th>Deskripsi Peristiwa Risiko</th>
                    <th>Nilai Dampak Inheren</th>
                    <th>Skala Dampak Inheren</th>
                    <th>Nilai Probabilitas Inheren</th>
                    <th>Eksposur Risiko Inheren</th>
                    <th>Level Risiko Inheren</th>
                    <th>Nilai Dampak Residual</th>
                    <th>Skala Dampak Residual</th>
                    <th>Nilai Probabilitas Residual</th>
                    <th>Eksposur Risiko Residual</th>
                    <th>Level Risiko Residual</th>
                    <th>Realisasi Nilai Dampak</th>
                    <th>Realisasi Skala Dampak</th>
                    <th>Realisasi Skala Probabilitas</th>
                    <th>Realisasi Level Risiko</th>
                    {{-- <th>Realisasi Eksposur Risiko</th> --}}
                    <th>Efektivitas Risiko</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>


<div class="modal fade" id="selectUnitModal" aria-labelledby="selectUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="selectUnitModalLabel">Pilih Anak Perusahaan Tujuan</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-risk-id" value="">
                
                <p>Pilih anak perusahaan di mana Anda ingin menambahkan risiko ini.</p>
                <div class="form-group">
                    <label for="modal-unit-select" class="form-label">Anak Perusahaan Tujuan</label>
                    <select id="modal-unit-select" class="form-select select2" style="width: 100%;">
                        <option value="" selected disabled>Pilih anak perusahaan...</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                    <div id="unit-select-error" class="text-danger small mt-2 d-none">
                        Anda harus memilih anak perusahaan tujuan.
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
    // 1. Setup CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const allUnits = @json($units);

    $('.select2').select2();
    $('#modal-unit-select').select2({
        dropdownParent: $('#selectUnitModal')
    });

    // 2. Init DataTable (POST)
    var table = $('#kamus-risiko-table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        ajax: {
            url: "{{ route('kamus-risiko-ap.index') }}", // Beda URL
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function (d) {
                d.unit_id = $('#unit_id').val();
                d.peristiwa_risiko = $('#peristiwa_risiko').val();
                d.jenis_risiko_id = $('#jenis_risiko_id').val();
                d.level_risiko = $('#level_risiko').val();
                d.deskripsi_risiko = $('#deskripsi_risiko').val();
                d.efektivitas = $('#efektivitas').val();
            },
            error: function (xhr) {
                if (xhr.status === 419) {
                    Swal.fire('Session Expired', 'Token kedaluwarsa.', 'warning');
                } else {
                    Swal.fire('Error', 'Gagal memuat data.', 'error');
                }
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'divisi', name: 'identifikasiRisiko.unit.name' },
            { data: 'taksonomi_risiko', name: 'identifikasiRisiko.jenisRisiko.title', orderable: false, searchable: false },
            { data: 'peristiwa_risiko', name: 'identifikasiRisiko.peristiwa_risiko' },
            { data: 'deskripsi_peristiwa_risiko', name: 'identifikasiRisiko.deskripsi_peristiwa_risiko' },
            // Inherent
            { data: 'nilai_dampak_inheren', name: 'identifikasiRisiko.riskAnalysis.nilai_dampak' },
            { data: 'skala_dampak_inheren', name: 'identifikasiRisiko.riskAnalysis.skala_dampak' },
            { data: 'nilai_probabilitas_inheren', name: 'identifikasiRisiko.riskAnalysis.nilai_probabilitas' },
            { data: 'eksposur_risiko_inheren', name: 'identifikasiRisiko.riskAnalysis.eksposur_risiko' },
            { data: 'level_risiko_inheren', name: 'identifikasiRisiko.riskAnalysis.level_risiko' },
            // Residual
            { data: 'nilai_dampak_residual', name: 'identifikasiRisiko.riskAnalysis.nilai_dampak_residual' },
            { data: 'skala_dampak_residual', name: 'identifikasiRisiko.riskAnalysis.skala_dampak_residual' },
            { data: 'nilai_probabilitas_residual', name: 'identifikasiRisiko.riskAnalysis.nilai_probabilitas_residual' },
            { data: 'eksposur_risiko_residual', name: 'identifikasiRisiko.riskAnalysis.eksposur_risiko_residual' },
            { data: 'level_risiko_residual', name: 'identifikasiRisiko.riskAnalysis.level_risiko_residual' },
            // Monitoring
            { data: 'realisasi_nilai_dampak', name: 'identifikasiRisiko.lastMonitoringRisiko.nilai_dampak' },
            { data: 'realisasi_skala_dampak', name: 'identifikasiRisiko.lastMonitoringRisiko.skala_dampak' },
            { data: 'realisasi_skala_probabilitas', name: 'identifikasiRisiko.lastMonitoringRisiko.skala_probabilitas' },
            { data: 'realisasi_level_risiko', name: 'identifikasiRisiko.lastMonitoringRisiko.level_risiko' },
            
            { data: 'efektivitas', name: 'identifikasiRisiko.efektivitas_perlakuan_risiko' },
        ],
        order: [[2, 'asc']]
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        table.draw();
    });

    $('#reset-filter-btn').on('click', function() {
        $('#filter-form')[0].reset();
        $('.select2').val(null).trigger('change');
        table.draw();
    });

    // 3. Export Logic (POST)
    $('#export-excel-btn').on('click', function(e) {
        e.preventDefault();
        showExportLoading();
        var formData = new FormData(document.getElementById('filter-form'));

        $.ajax({
            url: '{{ route("kamus-risiko-ap.export") }}', // Beda URL
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhrFields: { responseType: 'blob' },
            success: function(data, status, xhr) {
                hideExportLoading();
                const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'Kamus_Risiko_AP.xlsx'; // Beda nama file
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            },
            error: function(xhr) {
                hideExportLoading();
                Swal.fire('Error', 'Gagal export data.', 'error');
            }
        });
    });

    function showExportLoading() {
        $('#export-excel-btn').prop('disabled', true);
        $('#export-text').text('Generating...');
    }
    
    function hideExportLoading() {
        $('#export-excel-btn').prop('disabled', false);
        $('#export-text').text('Export to Excel');
    }

    // 4. Logic Ambil Risiko
    $('body').on('click', '.btn-ambil-risiko', function () {
        const riskId = $(this).data('id');
        const rowData = table.row($(this).closest('tr')).data();
        if(!rowData) return;

        $('#modal-risk-id').val(riskId);
        $('#selectUnitModal').data('risk-description', rowData.deskripsi_peristiwa_risiko);
        $('#modal-unit-select').val(null).trigger('change');
        $('#unit-select-error').addClass('d-none');

        const modal = new bootstrap.Modal(document.getElementById('selectUnitModal'));
        modal.show();
    });

    $('#confirm-unit-selection-btn').on('click', function() {
        const targetUnitId = $('#modal-unit-select').val();
        const originalRiskId = $('#modal-risk-id').val();
        const riskDescription = $('#selectUnitModal').data('risk-description');

        if (!targetUnitId) {
            $('#unit-select-error').removeClass('d-none');
            return;
        }

        const modalEl = document.getElementById('selectUnitModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        const targetUnit = allUnits.find(p => p.id == targetUnitId);

        Swal.fire({
            title: 'Konfirmasi',
            html: `Salin risiko <b>"${riskDescription}"</b> ke <b>"${targetUnit?.name}"</b>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Salin',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                sendAddRiskRequest(originalRiskId, targetUnitId, false);
            }
        });
    });

    function sendAddRiskRequest(originalRiskId, targetUnitId, isOverwriting) {
        Swal.fire({title: 'Memproses...', didOpen: () => Swal.showLoading()});
        
        $.ajax({
            url: "{{ route('kamus-risiko-ap.add-risk') }}", // Beda URL
            type: 'POST',
            data: {
                original_risk_id: originalRiskId,
                target_unit_id: targetUnitId,
                overwrite: isOverwriting ? 1 : 0
            },
            success: function(response) {
                Swal.fire('Berhasil!', response.message, 'success').then(() => {
                    window.location.href = response.redirect_url;
                });
            },
            error: function(xhr) {
                if (xhr.status === 409) {
                    Swal.fire({
                        title: 'Risiko Sudah Ada!',
                        text: xhr.responseJSON.message + " Timpa data lama?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Timpa!',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            sendAddRiskRequest(originalRiskId, targetUnitId, true);
                        }
                    });
                } else {
                    let msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                    Swal.fire('Gagal!', msg, 'error');
                }
            }
        });
    }
});
</script>
@endpush