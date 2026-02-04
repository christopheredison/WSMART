@extends('layouts.default')
@section('dashboard')
@include('partials.success-message')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <div class="bg-info-subtle p-2 rounded-4">
          <div class="lead__icon">
            <div class="svg-icon svg-icon-2x svg-icon-info">
              @include('partials.icon-layer')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">export excel</div>
          <h2>Laporan Project</h2>
        </div>
      </div>
      <div class="card-body">
        <form id="exportForm">
          @csrf
          <div class="row g-3">
            <div class="col-md-5">
              <label for="project_ids" class="form-label">Project</label>
              <select name="project_ids[]" id="project_ids" class="form-select select2" multiple="multiple" data-placeholder="Pilih Project..." required>
                <option value="all">Pilih Semua Project</option>
                @if (is_iterable($projects))
                  @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                  @endforeach
                @endif
              </select>
            </div>

            <div class="col-md-4">
                <label for="report_type" class="form-label">Jenis Laporan</label>
                <select name="report_type" id="report_type" class="form-select" required>
                    <option value="risk_register">Risk Register Project</option>
                    <option value="loss_event">Loss Event Project (LED)</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
              <button type="submit" id="exportBtn" class="btn btn-primary w-100 gap-1 d-flex flex-center">
                <span id="btnIcon">
                  <i class="bx bx-spreadsheet"></i>
                </span>
                <span id="btnText">
                  Export Excel
                </span>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(document).ready(function() {
    // Inisialisasi Select2 agar support placeholder multiple
    $('.select2').select2({
        width: '100%',
        closeOnSelect: false, // Opsional: agar dropdown tidak nutup pas pilih banyak
        placeholder: "Pilih Project..."
    });

    $('#exportForm').on('submit', function(e) {
        e.preventDefault();

        // 1. Ambil value sebagai Array
        const projectIds = $('#project_ids').val();
        const reportType = $('#report_type').val();

        // Validasi: pastikan array tidak kosong
        if (!projectIds || projectIds.length === 0) {
            Swal.fire('Perhatian', 'Harap pilih minimal satu project.', 'warning');
            return;
        }

        showLoading();

        let targetUrl = '{{ route("laporan.project.export") }}';
        if (reportType === 'loss_event') {
            targetUrl = '{{ route("laporan.project.export_led") }}';
        }

        $.ajax({
            url: targetUrl,
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                // Kirim array ID project
                project_ids: projectIds
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data, status, xhr) {
                hideLoading();
                const blob = new Blob([data], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });

                const disposition = xhr.getResponseHeader('Content-Disposition');
                let filename = 'Laporan.xlsx';

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
                hideLoading();
                let errorMsg = 'Gagal membuat laporan Excel. Silakan coba lagi.';

                // Handle response blob error (perlu reader karena responseType='blob')
                if (xhr.response instanceof Blob) {
                    const reader = new FileReader();
                    reader.onload = function() {
                        try {
                            const errorJson = JSON.parse(this.result);
                            if(errorJson.message) errorMsg = errorJson.message;
                        } catch(e) {}
                        showError(errorMsg);
                    };
                    reader.readAsText(xhr.response);
                } else {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showError(errorMsg);
                }
            }
        });
    });

    function showError(msg) {
        Swal.fire({
            title: 'Error',
            text: msg,
            icon: 'error',
            confirmButtonText: 'OK',
        });
    }

    function showLoading() {
        $('#exportBtn').prop('disabled', true);
        $('#btnIcon').html('<div class="spinner-border spinner-border-sm me-1" role="status"></div>');
        $('#btnText').text('Generating...');
    }

    function hideLoading() {
        $('#exportBtn').prop('disabled', false);
        $('#btnIcon').html('<i class="bx bx-spreadsheet"></i>');
        $('#btnText').text('Export Excel');
    }
});
</script>
@endsection
