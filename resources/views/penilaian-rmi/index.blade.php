@extends('layouts.default')

@section('dashboard')
@include('partials.success-message')

<div class="row g-5 mb-5">
  <div class="col-12">
    <div class="card btn-reveal-trigger">
      <div class="card-header d-flex align-items-center gap-3">
        <div class="bg-primary-subtle p-2 rounded-4">
          <div class="lead__icon">
            <div class="svg-icon svg-icon-2x svg-icon-info">
              @include('partials.icon-pyramid')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">Daftar Data</div>
          <h2>Periode RMI</h2>
        </div>
        <div class="ms-auto">
          @can('rmi_period_logs')
          <a class="btn btn-outline-secondary btn-sm" href="{{ route('penilaian-rmi.logs') }}">
            <span class="bx bx-history"></span>
            <span class="ms-1">Log Perubahan</span>
          </a>
          @endcan
        </div>
      </div>
      <div class="card-header border-bottom">
          <div class="d-flex align-items-center gap-3">
              <h6 class="mb-0">Keterangan :</h6>
              <div class="d-flex gap-3">
                  @foreach($tableLegend as $legend)
                  <div class="d-flex align-items-center gap-1">
                      {!! $legend['icon'] !!}
                      <span>{{ $legend['label'] }}</span>
                  </div>
                  @endforeach
              </div>
          </div>
      </div>
      <div class="card-body">
        <div id="tableExample3">
          <div class="">
            <table class="table align-middle" id="example" data-paging="true" data-info="true" data-filter="true">
              <thead class="table-light">
                <tr>
                  <th class="white-space-nowrap">#</th>
                  <th class="sort white-space-nowrap" data-sort="tahun">Tahun RMI</th>
                  <th class="sort white-space-nowrap" data-sort="dinilai">Tahun Dinilai</th>
                  <th class="sort" data-sort="status">Status</th>
                  <th class="sort white-space-nowrap" data-sort="final_score">Final Score RMI</th>
                  <th class="sort white-space-nowrap" data-sort="score_dimensi">Score Dimensi</th>
                  {{-- <th class="sort" data-sort="deskripsi">Deskripsi</th> --}}
                  <th class="sort" data-sort="kinerja">Kinerja</th>
                  <th class="sort" data-sort="kpmr">KPMR</th>
                  {{-- <th class="sort white-space-nowrap" data-sort="komposit">Peringkat Risiko</th> --}}
                  {{-- <th class="sort white-space-nowrap" data-sort="konversi">Nilai Konversi</th> --}}
                  <th class="sort white-space-nowrap" data-sort="score_kinerja">Score Kinerja</th>
                  {{-- <th class="sort white-space-nowrap" data-sort="penyesuaian">Penyesuaian</th> --}}
                  <th class="no-sort white-space-nowrap text-center" data-sort="action">Action</th>
                </tr>
              </thead>
              <tbody class="list">
                @foreach($periods as $index => $period)
                  <tr>
                    <td class="index-number">{{ $index + 1 }}</td>
                    <td class="tahun fw-bold text-primary">{{ $period->year }}</td>
                    <td class="dinilai">{{ $period->tahun_dinilai ?? '-' }}</td>
                    <td class="status">
                      @if($period->status == 1)
                        <span class="badge bg-warning text-dark">Dalam Proses</span>
                      @else
                        <span class="badge bg-success">Selesai</span>
                      @endif
                    </td>
                    <td class="final_score fw-bold">{{ $period->final_score_rmi ?? '-' }}</td>
                    <td class="score_dimensi">{{ $period->score_rmi ?? '-' }}</td>
                    {{-- <td class="deskripsi">{{ $period->score_rmi_desc ?? '-' }}</td> --}}
                    <td class="kinerja">{{ $period->kinerja ?? '-' }}</td>
                    <td class="kpmr">{{ $period->kpmr ?? '-' }}</td>
                    {{-- <td class="komposit">{{ $period->peringkat_komposit_risiko ?? '-' }}</td> --}}
                    {{-- <td class="konversi">{{ $period->nilai_konversi ?? '-' }}</td> --}}
                    <td class="score_kinerja">{{ $period->score_aspek_kinerja ?? '-' }}</td>
                    {{-- <td class="penyesuaian text-danger">{{ $period->adjusment_score ?? '-' }}</td> --}}
                    <td class="white-space-nowrap">
                      <a href="{{ route('penilaian-rmi.show', $period->id) }}" class="btn-input-icon" data-bs-toggle="tooltip" title="Lihat Detail">
                        <span class="bx bx-show"></span>
                      </a>
                      <a href="{{ route('penilaian-rmi.aspek-dinamis', $period->id) }}" class="btn-input-icon text-primary" data-bs-toggle="tooltip" title="Penilaian Aspek Dimensi">
                        <span class="bx bx-bar-chart-alt-2"></span>
                      </a>
                      <a href="{{ route('penilaian-rmi.aspek-kinerja', $period->id) }}" class="btn-input-icon text-success" data-bs-toggle="tooltip" title="Penilaian Aspek Kinerja">
                        <span class="bx bx-line-chart"></span>
                      </a>
                      @can('rmi_period_logs')
                      <a href="{{ route('penilaian-rmi.logs.show', $period->id) }}" class="btn-input-icon text-secondary" data-bs-toggle="tooltip" title="Log Perubahan">
                        <span class="bx bx-history"></span>
                      </a>
                      @endcan

                      @php
                        $extKinerjaId = $skalaKinerjas->firstWhere('tingkat', $period->kinerja_external)?->id ?? '';
                        $extKpmrId    = $skalaKpmrs->firstWhere('tingkat', $period->kpmr_external)?->id ?? '';
                      @endphp

                      <button
                        type="button"
                        class="btn-input-icon text-warning border-0 bg-transparent p-0 ms-1"
                        data-bs-toggle="modal"
                        data-bs-target="#penilaianModal"
                        data-period-id="{{ $period->id }}"
                        data-period-year="{{ $period->year }}"
                        data-tahun-dinilai="{{ $period->tahun_dinilai }}"
                        data-penilaian="{{ $period->penilaian }}"
                        data-penilai-external="{{ $period->penilai_external }}"
                        data-score-rmi-ext="{{ $period->score_rmi_external }}"
                        data-kinerja-ext-id="{{ $extKinerjaId }}"
                        data-kpmr-ext-id="{{ $extKpmrId }}"
                        data-score-aspek-kinerja-ext="{{ $period->score_aspek_kinerja_external }}"
                      >
                        <span data-bs-toggle="tooltip" title="Atur Data Penilaian" class="bx bxs-edit-alt"></span>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Setting Penilaian -->
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
          <div class="card mb-4 border shadow-none">
              <div class="card-header bg-white fw-bold text-primary border-bottom">
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

          <div class="card border shadow-none">
              <div class="card-header bg-white fw-bold text-success border-bottom">
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
                          <div class="form-text">Deskripsi otomatis diisi sistem.</div>
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
                      <i class="bx bx-check-circle me-2 fs-4"></i>
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

@section('scripts')
<script>
$(document).ready(function() {
  // 1. Inisialisasi DataTable
  const table = $('#example').DataTable({
    destroy: true,
    language: {
      search: "Cari:",
      lengthMenu: "Tampilkan _MENU_ data",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
      paginate: {
        first: "Awal",
        last: "Akhir",
        next: "Selanjutnya",
        previous: "Sebelumnya"
      }
    }
  });

  // 2. Highlight Kolom saat di-hover (Sesuai Referensi)
  table.on('mouseenter', 'td', function() {
    let colIdx = table.cell(this).index().column;

    table.cells().nodes().each((el) => el.classList.remove('highlight'));
    table.column(colIdx).nodes().each((el) => el.classList.add('highlight'));
  });

  // 3. Script untuk Modal Penilaian
  const penilaianModal = document.getElementById('penilaianModal');
  if (penilaianModal) {
    penilaianModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const periodId = button.getAttribute('data-period-id');

      // Update Action Form
      const form = penilaianModal.querySelector('#penilaianForm');
      form.setAttribute('action', `{{ url('penilaian-rmi') }}/${periodId}/update-penilaian`);

      // Populate Header & Internal
      penilaianModal.querySelector('#modalPeriodYear').textContent = button.getAttribute('data-period-year');
      penilaianModal.querySelector('#tahun_dinilai').value = button.getAttribute('data-tahun-dinilai');
      penilaianModal.querySelector('#penilaian').value = button.getAttribute('data-penilaian');

      // Populate External
      penilaianModal.querySelector('#penilai_external').value = button.getAttribute('data-penilai-external');
      penilaianModal.querySelector('#score_rmi_external').value = button.getAttribute('data-score-rmi-ext');
      penilaianModal.querySelector('#score_aspek_kinerja_external').value = button.getAttribute('data-score-aspek-kinerja-ext');

      // Dropdowns
      penilaianModal.querySelector('#kinerja_external_id').value = button.getAttribute('data-kinerja-ext-id');
      penilaianModal.querySelector('#kpmr_external_id').value = button.getAttribute('data-kpmr-ext-id');
    });
  }
});
</script>
@endsection
