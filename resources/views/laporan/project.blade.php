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
        <div class="d-none alert alert-info d-none mb-4" id="info_publish">
          <div class="d-flex align-items-center">
            <i class="bx bx-info-circle fs-4 me-2"></i>
            <div>
              <strong>Informasi:</strong> Laporan menampilkan seluruh risiko project. Data monitoring memakai yang <strong>sudah terpublish</strong> pada bulan terpilih. Jika bulan tersebut belum publish, dipakai monitoring publish terakhir dari <strong>bulan sebelumnya</strong>.
            </div>
          </div>
        </div>
        <form id="exportForm">
          @csrf
          <div class="row g-3">
            <div class="col-md-4" id="container_project">
              <label for="project_ids" class="form-label">Project</label>
              <select name="project_ids[]" id="project_ids" class="form-select select2" multiple="multiple" data-placeholder="Pilih Project...">
                <option value="all">Pilih Semua Project</option>
                @if (is_iterable($projects))
                  @foreach ($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                  @endforeach
                @endif
              </select>
            </div>

            @can('report_consolidation')
            <div class="col-md-4 d-none" id="container_divisi">
                <label for="divisi_ids" class="form-label">Divisi Operasi</label>
                <select name="divisi_ids[]" id="divisi_ids" class="form-select select2" multiple="multiple" data-placeholder="Pilih Divisi...">
                    <option value="all">Pilih Semua Divisi</option>
                    @if (is_iterable($divisis))
                        @foreach ($divisis as $divisi)
                            <option value="{{ $divisi->id }}">{{ $divisi->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            @endcan

            <div class="col-md-3" id="container_jenis">
              <label for="report_type" class="form-label">Jenis Laporan</label>
              <select name="report_type" id="report_type" class="form-select" required>
                <option value="risk_register">Risk Register Project</option>
                <option value="loss_event">Loss Event Project (LED)</option>
                @can('report_consolidation')
                  <option value="konsolidasi">Laporan Konsolidasi</option>
                @endcan
              </select>
            </div>

            <div class="col-md-2 d-none" id="container_tahun">
              <label for="tahun" class="form-label">Tahun</label>
              <select name="tahun" id="tahun" class="form-select">
                @foreach(range(date('Y'), date('Y') - 5) as $y)
                  <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-2 d-none" id="container_month">
              <label for="month" class="form-label">Bulan Monitoring</label>
              <select name="month" id="month" class="form-select">
                @foreach([1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'] as $num => $name)
                  <option value="{{ $num }}" {{ date('n') == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3 d-flex align-items-end" id="container_btn">
              <button type="submit" id="exportBtn" class="btn btn-primary w-100 gap-1 d-flex flex-center">
                <span id="btnIcon"><i class="bx bx-spreadsheet"></i></span>
                <span id="btnText">Export Excel</span>
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
        closeOnSelect: false,
        placeholder: "Pilih Proyek..."
    });

    $('#report_type').on('change', function() {
        const val = $(this).val();

        $('#container_jenis').removeClass('col-md-3 col-md-5').addClass('col-md-3');
        $('#container_btn').removeClass('col-md-3 col-md-5').addClass('col-md-3');

        if (val === 'risk_register') {
            $('#container_project').removeClass('d-none');
            $('#container_divisi').addClass('d-none');
            $('#container_tahun, #container_month').removeClass('d-none');

            $('#info_publish').removeClass('d-none');
        } else if (val === 'loss_event') {
            $('#container_project').removeClass('d-none');
            $('#container_divisi').addClass('d-none');
            $('#container_tahun, #container_month').addClass('d-none');
            $('#container_jenis').removeClass('col-md-3').addClass('col-md-5');
            $('#container_btn').removeClass('col-md-3').addClass('col-md-3');

            $('#info_publish').addClass('d-none');
        } else if (val === 'konsolidasi') {
            $('#container_project').addClass('d-none');
            $('#container_divisi').removeClass('d-none');
            $('#container_tahun, #container_month').removeClass('d-none');

            $('#info_publish').addClass('d-none');
        }
    });

    $('#report_type').trigger('change');

    $('#exportForm').on('submit', function(e) {
        e.preventDefault();

        const reportType = $('#report_type').val();
        const projectIds = $('#project_ids').val();
        const divisiIds = $('#divisi_ids').val();
        const selectedMonth = $('#month').val();
        const selectedYear = $('#tahun').val();

        // Validasi: pastikan array tidak kosong
        if (reportType !== 'konsolidasi' && (!projectIds || projectIds.length === 0)) {
            Swal.fire('Perhatian', 'Harap pilih minimal satu project.', 'warning');
            return;
        }

        if (reportType === 'konsolidasi' && (!divisiIds || divisiIds.length === 0)) {
            Swal.fire('Perhatian', 'Harap pilih minimal satu divisi.', 'warning');
            return;
        }

        showLoading();

        let targetUrl = '{{ route("laporan.project.export") }}';
        let payload = {
            _token: $('input[name="_token"]').val(),
            month: selectedMonth,
            tahun: selectedYear,
        };

        if (reportType === 'loss_event') {
            targetUrl = '{{ route("laporan.project.export_led") }}';
            payload.project_ids = projectIds;
        } else if (reportType === 'konsolidasi') {
            targetUrl = '{{ route("laporan.project.export_konsolidasi") }}';
            payload.divisi_ids = divisiIds;
        } else {
            payload.project_ids = projectIds;
        }

        $.ajax({
            url: targetUrl,
            type: 'POST',
            data: payload,
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
