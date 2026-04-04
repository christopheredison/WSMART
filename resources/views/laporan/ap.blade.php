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
          <h2>Laporan Anak Perusahaan</h2>
        </div>
      </div>
      <div class="card-body">
        <form id="exportForm">
          @csrf
          <div class="row g-3">
            <div class="col-md-3">
              <label for="periode_id" class="form-label">Periode</label>
              <select name="periode_id" id="periode_id" class="form-select select2" required>
                <option value="">Pilih Periode...</option>
                @foreach ($periodes as $periode)
                  <option value="{{ $periode->id }}">{{ $periode->tahun }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
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
            <div class="col-md-4">
              <label for="unit_id" class="form-label">Anak Perusahaan</label>
              <select name="unit_id" id="unit_id" class="form-select select2" required>
                <option value="">Pilih AP...</option>
                @if (is_iterable($units))
                  @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                  @endforeach
                @else
                  <option value="{{ $units->id }}" selected>{{ $units->name }}</option>
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
        const periodeId = $('#periode_id').val();
        const unitId = $('#unit_id').val();

        if (!periodeId || !unitId) {
            alert('Harap pilih periode dan unit terlebih dahulu.');
            return;
        }

        showLoading();

        $.ajax({
            url: '{{ route("laporan.ap.export") }}',
            type: 'POST',
            data: {
                _token: $('input[name="_token"]').val(),
                periode_id: periodeId,
                unit_id: unitId,
                month: $('#month').val(),
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
