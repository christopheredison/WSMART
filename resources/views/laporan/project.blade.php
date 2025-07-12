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
              <label for="project_id" class="form-label">Project</label>
              <select name="project_id" id="project_id" class="form-select" required>
                <option value="">Pilih Project...</option>
                @if (is_iterable($projects))
                  @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                  @endforeach
                @endif
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
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
    $('#exportForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validasi form
        const projectId = $('#project_id').val();
        
        if (!projectId) {
            alert('Harap pilih project terlebih dahulu.');
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: '{{ route("laporan.project.export") }}',
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                project_id: projectId
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
                let filename = 'Laporan_Risk_Register.xlsx';
                
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
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    title: 'Error',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonText: 'OK',
                });
            }
        });
    });
    
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