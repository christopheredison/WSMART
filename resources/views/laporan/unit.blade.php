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
          <h2>Laporan Divisi</h2>
        </div>
      </div>
      <div class="card-body">
        <form id="exportForm">
          @csrf
          <div class="row g-3">
            <div class="col-md-3">
              <label for="report_type" class="form-label">Jenis Laporan</label>
              <select name="report_type" id="report_type" class="form-select" required>
                <option value="risk_register">Risk Register Divisi</option>
                <option value="loss_event">Loss Event Database (LED)</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="periode_id" class="form-label">Periode (Tahun)</label>
              <select name="periode_id" id="periode_id" class="form-select select2" required>
                <option value="">Pilih Periode...</option>
                @foreach ($periodes as $periode)
                  <option value="{{ $periode->id }}">{{ $periode->tahun }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2" id="container_month">
              <label for="month" class="form-label">Bulan Monitoring</label>
              <select name="month" id="month" class="form-select select2">
                <option value="">Pilih Bulan (Opsional)...</option>
                @php
                  $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                  ];
                @endphp
                @foreach($months as $num => $name)
                  <option value="{{ $num }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label for="unit_id" class="form-label">Divisi / Unit</label>
              <select name="unit_id" id="unit_id" class="form-select select2" required>
                <option value="">Pilih Divisi...</option>
                @if (!empty($canExportAll))
                  <option value="all">Semua Divisi</option>
                @endif
                @if (is_iterable($units))
                  @foreach ($units as $unit)
                    @php
                      $isExpired = ($unit->valid_to && $unit->valid_to->isPast() && !$unit->valid_to->isToday()) || $unit->status == 0;
                    @endphp
                    <option value="{{ $unit->id }}">{{ $unit->name }} {{ $isExpired ? '(Expired)' : '' }}</option>
                  @endforeach
                @else
                  <option value="{{ $units->id }}" selected>{{ $units->name }}</option>
                @endif
              </select>
            </div>
            <div class="col-md-2" id="container_format">
              <label for="format_laporan" class="form-label">Format Laporan</label>
              <select name="format_laporan" id="format_laporan" class="form-select select2" required>
                <option value="lama">Format Lama</option>
                <option value="baru">Format Baru</option>
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
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
    $('#report_type').on('change', function() {
        const isLed = $(this).val() === 'loss_event';

        if (isLed) {
            $('#container_month, #container_format').addClass('d-none');
            $('#format_laporan').prop('required', false);
        } else {
            $('#container_month, #container_format').removeClass('d-none');
            $('#format_laporan').prop('required', true);
        }
    });

    $('#report_type').trigger('change');

    $('#exportForm').on('submit', function(e) {
        e.preventDefault();

        const reportType = $('#report_type').val();
        const periodeId = $('#periode_id').val();
        const unitId = $('#unit_id').val();

        if (!periodeId || !unitId) {
            Swal.fire('Perhatian', 'Harap pilih periode dan divisi terlebih dahulu.', 'warning');
            return;
        }

        showLoading();

        let targetUrl = '{{ route("laporan.unit.export") }}';
        const payload = {
            _token: $('input[name="_token"]').val(),
            periode_id: periodeId,
            unit_id: unitId,
        };

        if (reportType === 'loss_event') {
            targetUrl = '{{ route("laporan.unit.export_led") }}';
        } else {
            payload.month = $('#month').val();
            payload.format_laporan = $('#format_laporan').val();
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

                // Coba baca error dari blob response
                if (xhr.responseText) {
                    try {
                        const json = JSON.parse(xhr.responseText);
                        if (json.message) {
                            errorMsg = json.message;
                        }
                    } catch (e) {
                        errorMsg = xhr.statusText || errorMsg;
                    }
                    showError(errorMsg);
                } else if (xhr.response) {
                    const reader = new FileReader();
                    reader.onload = function() {
                        try {
                            const json = JSON.parse(reader.result);
                            if (json.message) {
                                errorMsg = json.message;
                            }
                        } catch (e) {
                            errorMsg = xhr.statusText || errorMsg;
                        }
                        showError(errorMsg);
                    };
                    reader.readAsText(xhr.response);
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                    showError(errorMsg);
                } else {
                    showError(errorMsg);
                }

                function showError(msg) {
                    Swal.fire({
                        title: 'Error',
                        text: msg,
                        icon: 'error',
                        confirmButtonText: 'OK',
                    });
                }
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
