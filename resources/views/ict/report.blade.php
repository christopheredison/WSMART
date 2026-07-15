@extends('layouts.default')
@section('dashboard')
<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <h5 class="mb-0">Laporan ICT</h5>
      </div>
      <div class="card-body">
        <form id="ictReportForm" action="{{ route('ict.store-report', $ictPlan->id) }}" method="POST">
          @csrf
          <!-- Informasi ICT Plan -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Sasaran BUMN</label>
                <input type="text" class="form-control" value="{{ $ictPlan->sasaran_bumn }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Peristiwa Risiko</label>
                <input type="text" class="form-control" value="{{ $peristiwaRisiko }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Lokasi Risiko</label>
                <input type="text" class="form-control" value="{{ $lokasiRisiko }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Business Process</label>
                <input type="text" class="form-control" value="{{ $ictPlan->business_process }}" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Metode Pengujian</label>
                <input type="text" class="form-control" value="{{ $ictPlan->metode_pengujian }}" readonly>
              </div>
            </div>
          </div>
          
          <!-- Rangkuman Keterangan dan Hasil Temuan -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label">Rangkuman Keterangan dan Hasil Temuan</label>
                <textarea class="form-control" rows="10" readonly>{{ $rangkuman }}</textarea>
              </div>
            </div>
          </div>
          
          <!-- Form Laporan -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label">Status Tindak Lanjut <span class="text-danger">*</span></label>
                <textarea class="form-control" name="status_tindak_lanjut" rows="5" required>{{ $ictReport->status_tindak_lanjut ?? '' }}</textarea>
              </div>
            </div>
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label">Rencana Tindak Lanjut <span class="text-danger">*</span></label>
                <textarea class="form-control" name="rencana_tindak_lanjut" rows="5" required>{{ old('rencana_tindak_lanjut', $ictReport->keterangan ?? '') }}</textarea>
              </div>
            </div>
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label">Realisasi Tindak Lanjut <span class="text-danger">*</span></label>
                <textarea class="form-control" name="realisasi_tindak_lanjut" rows="5" required>{{ old('realisasi_tindak_lanjut', $ictReport->realisasi_tindak_lanjut ?? '') }}</textarea>
              </div>
            </div>
          </div>
          
          <!-- Tombol Submit -->
          <div class="row">
            <div class="col-12 text-end">
              <a href="{{ route('ict.index') }}" class="btn btn-secondary me-2">Kembali</a>
              <button type="submit" class="btn btn-primary">Simpan Laporan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ictReportForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      Swal.fire({
        title: 'Konfirmasi',
        text: 'Apakah Anda yakin ingin menyimpan laporan ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
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