@extends('layouts.default')
@section('dashboard')

@include('partials.success-message')

<div class="container my-4">
  <h2 class="mb-4">Daftar Periode RMI</h2>

  <form method="GET" action="{{ route('penilaian-rmi.index') }}" class="mb-3 d-flex">
    <input type="text" name="q" value="{{ request('q') }}" class="form-control me-2" placeholder="Cari periode RMI...">
    <button type="submit" class="btn btn-primary">Filter</button>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Tahun Periode RMI</th>
          <th>Status</th>
          <th>Score RMI</th>
          <th>Score Aspek Dimensi</th>
          <th>Deskripsi</th>
          <th>Kinerja</th>
          <th>KPMR</th>
          <th>Peringkat Komposit Risiko</th>
          <th>Nilai Konversi</th>
          <th>Score Aspek Kinerja</th>
          <th>Score Penyesuaian</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($periods as $index => $period)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $period->year }}</td>
            <td>
              @if($period->status == 1)
                <span class="badge bg-warning text-dark">Dalam Proses</span>
              @else
                <span class="badge bg-success">Selesai</span>
              @endif
            </td>
            <td>{{ $period->final_score_rmi }}</td>
            <td>{{ $period->score_rmi }}</td>
            <td>{{ $period->score_rmi_desc }}</td>
            <td>
              {{-- Tampilkan deskripsi Kinerja dari relasi skala_kinerjas --}}
              {{ $period->kinerja ?? '-' }}
            </td>
            <td>
              {{-- Deskripsi KPMR dari relasi skala_kpmrs --}}
              {{ $period->kpmr ?? '-' }}
            </td>
            <td>{{ $period->peringkat_komposit_risiko ?? '-' }}</td>
            <td>{{ $period->nilai_konversi ?? '-' }}</td>
            <td>{{ $period->score_aspek_kinerja ?? '-' }}</td>
            <td>{{ $period->adjusment_score ?? '-' }}</td>
            <td>
              <a href="{{ route('penilaian-rmi.show', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                title="Lihat Detail">
                <span class="bx bx-show"></span>
              </a>
              <a href="{{ route('penilaian-rmi.aspek-dinamis', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                title="Penilaian Aspek Dimensi">
                <span class="bx bx-bar-chart-alt-2"></span>
              </a>
              <a href="{{ route('penilaian-rmi.aspek-kinerja', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                title="Penilaian Aspek Kinerja">
                <span class="bx bx-line-chart"></span>
              </a>
              <button 
                  type="button" 
                  class="btn-input-icon"
                  data-bs-toggle="modal"
                  data-bs-target="#penilaianModal"
                  data-period-id="{{ $period->id }}"
                  data-penilaian="{{ $period->penilaian }}"
                  data-tipe-penilaian="{{ $period->tipe_penilaian }}"
                  data-period-year="{{ $period->year }}"
                  title="Atur Data Penilaian"
              >
                <span class="bx bxs-edit-alt"></span>
              </button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{ $periods->links() }}
</div>

<div class="modal fade" id="penilaianModal" tabindex="-1" aria-labelledby="penilaianModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="penilaianModalLabel">Atur Data Penilaian Periode <span id="modalPeriodYear"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Form akan di-submit ke route yang kita buat -->
      <form id="penilaianForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
              <label for="penilaian" class="form-label">Input Penilai</label>
              <input type="text" class="form-control" id="penilaian" name="penilaian" placeholder="Masukkan nama penilai...">
          </div>
          <div class="mb-3">
              <label for="tipe_penilaian" class="form-label">Tipe Penilai</label>
              <select class="form-select" id="tipe_penilaian" name="tipe_penilaian">
                  <option value="">-- Pilih Tipe --</option>
                  <option value="1">Eksternal</option>
                  <option value="2">Internal</option>
              </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const penilaianModal = document.getElementById('penilaianModal');
  if (penilaianModal) {
    penilaianModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;

      const periodId = button.getAttribute('data-period-id');
      const periodYear = button.getAttribute('data-period-year');
      const penilaian = button.getAttribute('data-penilaian');
      const tipePenilaian = button.getAttribute('data-tipe-penilaian');

      const form = penilaianModal.querySelector('#penilaianForm');
      const modalTitleYear = penilaianModal.querySelector('#modalPeriodYear');
      const penilaianInput = penilaianModal.querySelector('#penilaian');
      const tipePenilaianSelect = penilaianModal.querySelector('#tipe_penilaian');
      const actionUrl = `{{ url('penilaian-rmi') }}/${periodId}/update-penilaian`;

      form.setAttribute('action', actionUrl);

      modalTitleYear.textContent = periodYear;
      penilaianInput.value = penilaian;
      tipePenilaianSelect.value = tipePenilaian;
    });
  }
});
</script>
@endpush