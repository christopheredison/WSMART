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
          <th>Tahun Dinilai</th>
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
            <td>{{ $period->tahun_dinilai ?? '-' }}</td>
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

              @php
                  $extKinerjaId = $skalaKinerjas->firstWhere('tingkat', $period->kinerja_external)?->id ?? '';
                  $extKpmrId    = $skalaKpmrs->firstWhere('tingkat', $period->kpmr_external)?->id ?? '';
              @endphp

              <button
                  type="button"
                  class="btn-input-icon"
                  data-bs-toggle="modal"
                  data-bs-target="#penilaianModal"
                  
                  data-period-id="{{ $period->id }}"
                  data-period-year="{{ $period->year }}"
                  data-tahun-dinilai="{{ $period->tahun_dinilai }}"
                  
                  {{-- Data Internal --}}
                  data-penilaian="{{ $period->penilaian }}"
                  
                  {{-- Data Eksternal --}}
                  data-penilai-external="{{ $period->penilai_external }}"
                  data-score-rmi-ext="{{ $period->score_rmi_external }}"
                  data-kinerja-ext-id="{{ $extKinerjaId }}"
                  data-kpmr-ext-id="{{ $extKpmrId }}"
                  data-score-aspek-kinerja-ext="{{ $period->score_aspek_kinerja_external }}"
                  
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-0">
      <div class="modal-header bg-light">
        <h5 class="modal-title">Setting Penilaian Periode <span id="modalPeriodYear" class="fw-bold"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form id="penilaianForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-body">
          
          <div class="card mb-4 border">
              <div class="card-header bg-white fw-bold text-primary">
                  Data Internal
              </div>
              <div class="card-body">
                  <div class="row">
                      <div class="col-md-6 mb-3">
                          <label class="form-label">Tahun Dinilai</label>
                          <input type="number" class="form-control" id="tahun_dinilai" name="tahun_dinilai" placeholder="Contoh: 2024">
                      </div>
                      <div class="col-md-6 mb-3">
                          <label class="form-label">Nama Penilai (Internal)</label>
                          <input type="text" class="form-control" id="penilaian" name="penilaian" placeholder="Unit/Divisi Penilai">
                      </div>
                  </div>
              </div>
          </div>

          <div class="card border">
              <div class="card-header bg-white fw-bold text-success">
                  Data Eksternal (Input Manual)
              </div>
              <div class="card-body">
                  <div class="mb-3">
                      <label class="form-label">Nama Lembaga Penilai Eksternal</label>
                      <input type="text" class="form-control" id="penilai_external" name="penilai_external" placeholder="Contoh: BPKP">
                  </div>

                  <div class="row">
                      <div class="col-md-4 mb-3">
                          <label class="form-label">Score RMI (Awal) <span class="text-danger">*</span></label>
                          <input type="number" step="0.01" class="form-control bg-light" id="score_rmi_external" name="score_rmi_external" placeholder="0.00">
                          <div class="form-text">Deskripsi akan otomatis diisi sistem.</div>
                      </div>
                      <div class="col-md-4 mb-3">
                          <label class="form-label">Predikat Kinerja <span class="text-danger">*</span></label>
                          <select class="form-select" id="kinerja_external_id" name="kinerja_external_id">
                              <option value="">-- Pilih --</option>
                              @foreach($skalaKinerjas as $skala)
                                  <option value="{{ $skala->id }}">{{ $skala->tingkat }}</option>
                              @endforeach
                          </select>
                      </div>
                      <div class="col-md-4 mb-3">
                          <label class="form-label">Predikat KPMR <span class="text-danger">*</span></label>
                          <select class="form-select" id="kpmr_external_id" name="kpmr_external_id">
                              <option value="">-- Pilih --</option>
                              @foreach($skalaKpmrs as $skala)
                                  <option value="{{ $skala->id }}">{{ $skala->tingkat }}</option>
                              @endforeach
                          </select>
                      </div>
                  </div>

                  <div class="row">
                      <div class="col-md-12 mb-3">
                          <label class="form-label">Score Total Aspek Kinerja</label>
                          <div class="input-group">
                              <input type="number" step="0.01" class="form-control" id="score_aspek_kinerja_external" name="score_aspek_kinerja_external" placeholder="0.00">
                              <span class="input-group-text">Poin</span>
                          </div>
                          <div class="form-text">
                              Digunakan untuk menghitung <strong>Adjustment Score</strong> secara otomatis.<br>
                              <i>(>90: 0, >80: -0.25, >65: -0.5, >50: -0.75, else: -1)</i>
                          </div>
                      </div>
                  </div>
                  
                  <div class="alert alert-success d-flex align-items-center mt-2 p-2" role="alert">
                      <i class="bx bx-check-circle me-2"></i>
                      <div class="small">
                          Sistem akan otomatis menghitung: <strong>Peringkat Komposit, Nilai Konversi, Adjustment Score,</strong> dan <strong>Final Score RMI</strong> setelah disimpan.
                      </div>
                  </div>
              </div>
          </div>

        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4">Simpan Data</button>
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
      
      // Update Action Form
      const form = penilaianModal.querySelector('#penilaianForm');
      form.setAttribute('action', `{{ url('penilaian-rmi') }}/${periodId}/update-penilaian`);

      // 1. Populate Header & Internal
      penilaianModal.querySelector('#modalPeriodYear').textContent = button.getAttribute('data-period-year');
      penilaianModal.querySelector('#tahun_dinilai').value = button.getAttribute('data-tahun-dinilai');
      penilaianModal.querySelector('#penilaian').value = button.getAttribute('data-penilaian');

      // 2. Populate External
      penilaianModal.querySelector('#penilai_external').value = button.getAttribute('data-penilai-external');
      penilaianModal.querySelector('#score_rmi_external').value = button.getAttribute('data-score-rmi-ext');
      penilaianModal.querySelector('#score_aspek_kinerja_external').value = button.getAttribute('data-score-aspek-kinerja-ext');

      // 3. Dropdowns
      penilaianModal.querySelector('#kinerja_external_id').value = button.getAttribute('data-kinerja-ext-id');
      penilaianModal.querySelector('#kpmr_external_id').value = button.getAttribute('data-kpmr-ext-id');
    });
  }
});
</script>
@endpush
