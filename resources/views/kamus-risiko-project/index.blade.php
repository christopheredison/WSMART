@extends('layouts.default')

@section('dashboard')

<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-3">
      <div class="lead__icon bg-warning-subtle">
        <div class="svg-icon svg-icon svg-icon-warning">
          @include('partials.icon-layer')
        </div>
      </div>
      <div class="d-block">
        <h2>Kamus Risiko Project</h2>
        {{-- <div class="ff-preheading">Project</div> --}}
      </div>
    </div>
    <div class="card-body">
        <form id="filter-form">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="project_id" class="form-label">Proyek</label>
                    <select name="project_id" id="project_id" class="form-select select2">
                        <option value="">Semua Proyek</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="peristiwa_risiko_id" class="form-label">Peristiwa Risiko</label>
                    <select name="peristiwa_risiko_id" id="peristiwa_risiko_id" class="form-select select2">
                        <option value="">Semua Peristiwa Risiko</option>
                        @foreach($peristiwaRisikos as $peristiwa)
                            <option value="{{ $peristiwa->id }}">{{ $peristiwa->title }}</option>
                        @endforeach
                    </select>
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


<div class="modal fade" id="selectProjectModal" aria-labelledby="selectProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="selectProjectModalLabel">Pilih Proyek Tujuan</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal-risk-id" value="">
                
                <p>Pilih proyek di mana Anda ingin menambahkan risiko ini.</p>
                <div class="form-group">
                    <label for="modal-project-select" class="form-label">Proyek Tujuan</label>
                    <select id="modal-project-select" class="form-select select2" style="width: 100%;">
                        <option value="" selected disabled>Pilih proyek...</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                    <div id="project-select-error" class="text-danger small mt-2 d-none">
                        Anda harus memilih proyek tujuan.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirm-project-selection-btn">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const allProjects = @json($projects);

    $('#modal-project-select').select2({
        dropdownParent: $('#selectProjectModal')
    });

    // Initialize DataTable
    var table = $('#kamus-risiko-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('kamus-risiko-project.index') }}",
            data: function (d) {
                d.project_id = $('#project_id').val();
                d.peristiwa_risiko_id = $('#peristiwa_risiko_id').val();
                d.jenis_risiko_id = $('#jenis_risiko_id').val();
                d.level_risiko = $('#level_risiko').val();
                d.deskripsi_risiko = $('#deskripsi_risiko').val();
                d.efektivitas = $('#efektivitas').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
            { data: 'proyek', name: 'project.project_name' },
            { data: 'taksonomi_risiko', name: 'projectRisk.jenisRisiko.title', orderable: false, searchable: false },
            { data: 'peristiwa_risiko', name: 'projectRisk.peristiwaRisiko.title' },
            { data: 'deskripsi_peristiwa_risiko', name: 'projectRisk.deskripsi_peristiwa_risiko' },

            // Inherent
            { data: 'nilai_dampak_inheren', name: 'projectRisk.projectRiskAnalisa.nilai_dampak' },
            { data: 'skala_dampak_inheren', name: 'projectRisk.projectRiskAnalisa.skala_dampak' },
            { data: 'nilai_probabilitas_inheren', name: 'projectRisk.projectRiskAnalisa.nilai_probabilitas' },
            { data: 'eksposur_risiko_inheren', name: 'projectRisk.projectRiskAnalisa.eksposur_risiko' },
            { data: 'level_risiko_inheren', name: 'projectRisk.projectRiskAnalisa.level_risiko' },
            
            // Residual
            { data: 'nilai_dampak_residual', name: 'projectRisk.projectRiskAnalisa.nilai_dampak_residual' },
            { data: 'skala_dampak_residual', name: 'projectRisk.projectRiskAnalisa.skala_dampak_residual' },
            { data: 'nilai_probabilitas_residual', name: 'projectRisk.projectRiskAnalisa.nilai_probabilitas_residual' },
            { data: 'eksposur_risiko_residual', name: 'projectRisk.projectRiskAnalisa.eksposur_risiko_residual' },
            { data: 'level_risiko_residual', name: 'projectRisk.projectRiskAnalisa.level_risiko_residual' },

            // Monitoring
            { data: 'realisasi_nilai_dampak', name: 'projectRisk.projectRiskMonitoring.nilai_dampak' },
            { data: 'realisasi_skala_dampak', name: 'projectRisk.projectRiskMonitoring.skala_dampak' },
            { data: 'realisasi_skala_probabilitas', name: 'projectRisk.projectRiskMonitoring.skala_probabilitas' },
            { data: 'realisasi_level_risiko', name: 'projectRisk.projectRiskMonitoring.level_risiko' },
            // { data: 'realisasi_eksposur_risiko', name: 'projectRisk.projectRiskMonitoring.eksposur_risiko' },
            
            { data: 'efektivitas', name: 'projectRisk.efektivitas_perlakuan_risiko' },
        ],
        order: [[2, 'asc']] // Default order by Proyek name
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

        showExportLoading();

        $.ajax({
            url: '{{ route("kamus-risiko-project.export") }}',
            type: 'POST',
            data: $('#filter-form').serialize(), // Mengambil semua data filter dari form
            xhrFields: {
                responseType: 'blob' // Response akan berupa file binary
            },
            success: function(data, status, xhr) {
                hideExportLoading();
                const blob = new Blob([data], { 
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
                });
                
                const disposition = xhr.getResponseHeader('Content-Disposition');
                let filename = 'Kamus_Risiko_Proyek.xlsx'; // Nama default
                
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }
                
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            },
            error: function(xhr, status, error) {
                hideExportLoading();
                let errorMsg = 'Gagal membuat laporan Excel. Silakan coba lagi.';
                // Coba parse response error jika ada
                const reader = new FileReader();
                reader.onload = function() {
                    try {
                        const json = JSON.parse(this.result);
                        if (json && json.message) {
                            errorMsg = json.message;
                        }
                    } catch (e) {
                        // Biarkan pesan error default
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'OK',
                    });
                }
                reader.readAsText(xhr.response);
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

    // Action button "Ambil Risiko"
    $('#kamus-risiko-table').on('click', '.btn-ambil-risiko', function () {
        const riskId = $(this).data('id');
        const rowData = table.row($(this).closest('tr')).data();
        const riskDescription = rowData.deskripsi_peristiwa_risiko;

        $('#modal-risk-id').val(riskId);
        $('#selectProjectModal').data('risk-description', riskDescription);

        // Reset pilihan
        $('#modal-project-select').val(null).trigger('change');
        $('#project-select-error').addClass('d-none');

        projectSelectModal = new bootstrap.Modal(document.getElementById('selectProjectModal'));
        projectSelectModal.show();
    });

    $('#confirm-project-selection-btn').on('click', function() {
        const targetProjectId = $('#modal-project-select').val();
        const originalRiskId = $('#modal-risk-id').val();
        const riskDescription = $('#selectProjectModal').data('risk-description');

        // Validasi
        if (!targetProjectId) {
            $('#project-select-error').removeClass('d-none');
            return;
        }
        
        // projectSelectModal.hide();

        const targetProject = allProjects.find(p => p.id == targetProjectId);
        const targetProjectName = targetProject ? targetProject.project_name : 'N/A';
        
        Swal.fire({
            title: 'Konfirmasi Ambil Risiko',
            html: `Anda yakin ingin menyalin risiko <br><b>"${riskDescription}"</b><br> ke proyek <br><b>"${targetProjectName}"</b>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Tambahkan!',
            cancelButtonText: 'Batal'
        }).then((confirmResult) => {
            if (confirmResult.isConfirmed) {
                projectSelectModal.hide();
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                
                $.ajax({
                    url: "{{ route('kamus-risiko-project.add-risk') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        original_risk_id: originalRiskId,
                        target_project_id: targetProjectId
                    },
                    success: function(response) {
                        Swal.fire({ 
                          icon: 'success', 
                          title: 'Berhasil!', 
                          text: response.message
                        }).then(() => {
                          const redirectUrlTemplate = "{{ route('projects.risks.index', ['project' => ':projectId']) }}";
                          const redirectUrl = redirectUrlTemplate.replace(':projectId', response.redirect_project_id);
                          window.localtion.href = redirectUrl;
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