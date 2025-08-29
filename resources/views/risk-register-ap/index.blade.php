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
              @include('partials.icon-pyramid')
            </div>
          </div>
        </div>
        <div class="d-block">
          <div class="ff-preheading">Input Data</div>
          <h2>Risk Register Anak Perusahaan</h2>
          @if(isset($selectedPeriode))
          <div class="ff-preheading">Periode: {{ $selectedPeriode->tahun }}</div>
          @endif
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
      <div class="card-body dt-header-true">
        <div id="tableExample3">
          <div class="row g-2 mb-1">
            @can('risk_register_all_unit')
            <div class="col-4 col-sm-2">
              <label for="filter-unit" class="form-label d-none">Unit</label>
              <select id="filter-unit" class="form-select select2">
                @foreach($unit as $id => $name)
                <option value="{{ $id }}" {{ $unitId == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @else
            <div class="col-4 col-sm-2">
              <label for="filter-unit" class="form-label d-none">Unit</label>
              <select id="filter-unit" class="form-select select2" disabled>
                @php
                $userUnitId = auth()->user()->unit_id;
                $userUnitName = $unit[$userUnitId] ?? 'Unit Tidak Ditemukan';
                @endphp
                <option value="{{ $userUnitName }}" selected>{{ $userUnitName }}</option>
              </select>
            </div>
            @endcan
            <div class="col-12 col-sm-4" style="display:none;">
              <label for="filter-risk-event" class="form-label d-none">Peristiwa Risiko</label>
              <select id="filter-risk-event" class="form-select select2">
                <option value="" selected>Peristiwa Risiko</option>
                @foreach($peristiwaRisiko as $id => $title)
                <option value="{{ $title }}">{{ $title }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-12 col-sm-4">
              <label for="filter-risk-t2t3" class="form-label d-none">T2 & T3 KBUMN</label>
              <select id="filter-risk-t2t3" class="form-select select2">
                <option value="" selected>T2 & T3 KBUMN</option>
                @foreach($jenisRisiko as $id => $title)
                    @php
                        $kategori = \App\Models\JenisRisiko::find($id)->kategoriRisiko;
                        $kategoriTitle = $kategori ? $kategori->title : '';
                    @endphp
                    <option value="{{ $kategoriTitle }} - {{ $title }}" {{ old('jenis_risiko_id') == $id ? 'selected' : '' }}>
                        {{ $kategoriTitle }} - {{ $title }}
                    </option>
                @endforeach
              </select>
            </div>

            <div class="col-5 col-sm-2" style="display:none;">
              <label for="filter-risk-level" class="form-label d-none">Level Risiko</label>
              <select id="filter-risk-level" class="form-select js-select-hide-search">
                <option value="" selected>Level Risiko</option>
                <option value="High">High</option>
                <option value="Moderate To High">Moderate To High</option>
                <option value="Moderate">Moderate</option>
                <option value="Low To Moderate">Low To Moderate</option>
                <option value="Low">Low</option>
              </select>
            </div>
            <div class="col-auto ms-auto d-flex gap-2 align-items-center">
              <div class="col-auto ms-auto">
                <a href="{{ route('kamus-risiko-ap.index') }}" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" data-bs-title="Kamus Risiko">
                  <span class="bx bx-book-bookmark"></span>
                  <span class="ms-1">Kamus Risiko</span>
                </a>
              </div>
              <div class="col-auto ms-auto">
              @php
                  // diasumsikan di view Anda ada $selectedPeriode
                  $pid = $selectedPeriode->id;
              @endphp
              @can('risk_register_create')
                @if($status == null || $status == 1 || $status == 5)
                <a id="add-risk-button" href="{{ route('risk-register-ap.create', ['pid' => $pid]) }}" type="button"
                  class="btn btn-outline-info btn-sm d-flex flex-center" data-bs-toggle="tooltip"
                  data-bs-title="Tambah Risiko">
                  <span class="bx bx-plus"></span>
                  <span class="ms-1">Tambah Risiko</span>
                </a>
                @endif
              @endcan
              </div>
            </div>
          </div>
          <table class="table dataTable" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="no-sort white-space-nowrap">
                   @if($status == \App\Models\DataBatch::STATUS_RANKING && isset($avgQuantitativeExposure))
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="select-all" />
                  </div>
                  @endif
                </th>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="unit">Unit</th>
                <th class="sort" data-sort="unit_type">Sasaran</th>
                <th class="sort mw-20r" data-sort="kategori_jenis_risiko">T2 & T3 KBUMN</th>
                <th class="sort mw-10r" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                <th class="sort mw-10r" data-sort="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</th>
                <th class="sort mw-10r" data-sort="kontrol_eksisting">Jenis Kontrol Eksisting</th>
                <th class="sort mw-10r" data-sort="kategori dampak">Kategori Dampak</th>
                <th class="sort mw-10r" data-sort="nilai_risiko_inherent">Nilai Risiko</th>
                <th class="sort mw-10r" data-sort="eksposure_risiko_inherent">Eksposure Risiko</th>
                <th class="sort mw-10r" data-sort="total_biaya_rencana_perlakuan">Total Biaya Rencana Perlakuan</th>
                <th class="sort mw-15r" data-sort="waktu_terpapar">Waktu Terpapar</th>
                <th class="sort" data-sort="status">Status</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($risiko as $index => $item)
              <tr>
                <td class="white-space-nowrap">
                  @if($status == \App\Models\DataBatch::STATUS_RANKING && isset($avgQuantitativeExposure))
                  <div class="form-check mb-0"> 
                    <input class="form-check-input select-item" type="checkbox" name="selected_items[]"
                      value="{{ $item->id }}" />
                  </div>
                  @endif
                </td>
                <td class="index-number">
                  @if($item->status_risiko== 2)
                    <span class="badge bg-primary">Rekomendasi</span> 
                  @elseif($item->status_risiko == 3 || $item->status_risiko == 4 || $item->status_risiko == 5)
                    <span class="badge bg-danger">Risiko Utama</span>
                  @endif
                  {{ $index + 1 }}
                </td>
                <td class="unit">
                  {{ $item->unit->name ?? '-' }}
                </td>
                <td class="unit_type">{{ $item->target_capaian_kinerja ?? '-' }}</td>
                <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title ?? '-' }} - {{ $item->jenisRisiko->title ?? '-' }}</td>
                <td class="peristiwa_risiko">{{ $item->peristiwa_risiko ?? '-' }}</td>
                <td class="deskripsi_peristiwa_risiko">{{ $item->deskripsi_peristiwa_risiko }}</td>
                <td class="kontrol_eksisting">{{ $item->jenisKontrolEksisting->jenis_kontrol ?? '-' }}</td>
                <td class="kategori_dampak">{{ $item->riskAnalysis->kategori_dampak ?? '-' }}</td>
                <td class="nilai_risiko" @if($item->riskAnalysis && $item->riskAnalysis->level_risiko)
                    style="background-color: 
                    @switch(strtolower($item->riskAnalysis->level_risiko))
                        @case('low')
                            #14A20E
                            @break
                        @case('low to moderate')
                            #7CD020
                            @break
                        @case('moderate')
                            #FCDB2C
                            @break
                        @case('moderate to high')
                            #FEAC17
                            @break
                        @case('high')
                            #EF6345
                            @break
                        @default
                            transparent
                    @endswitch
                    ; color: @if(in_array(strtolower($item->riskAnalysis->level_risiko), ['low', 'low to moderate', 'moderate'])) #000000 @else #FFFFFF @endif;"
                @endif
                >{{ $item->riskAnalysis->skala_risiko ?? '-' }}</td>
                <td class="eksposure_risiko">{{ $item->riskAnalysis && $item->riskAnalysis->eksposur_risiko ? 'Rp ' . number_format($item->riskAnalysis->eksposur_risiko, 0, ',', '.') : '-' }}</td>
                <td class="total_biaya_rencana_perlakuan">
                  @php
                    $totalBiaya = 0;
                    if ($item->penyebabRisiko->count() > 0) {
                      foreach ($item->penyebabRisiko as $penyebab) {
                        foreach ($penyebab->perlakuanPenyebabRisikoUnit as $perlakuan) {
                          $totalBiaya += $perlakuan->biaya_perlakuan_risiko ?? 0;
                        }
                      }
                    }
                  @endphp
                  {{ $totalBiaya > 0 ? 'Rp ' . number_format($totalBiaya, 0, ',', '.') : '-' }}
                </td>
                <td class="waktu_terpapar">
                  {{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y') }} -
                  {{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y') }}
                </td>
                <td class="status">
                  @switch($item->status ?? 0)
                  @case(1)
                  Draft
                  @break
                  @case(2)
                  On Review
                  @break
                  @case(3)
                  @php
                    $lastApproval = \App\Models\ApprovalLog::where('risk_id', $item->id)
                      ->where('type', 1) // 1 untuk unit
                      ->orderBy('approved_at', 'desc')
                      ->with('approver')
                      ->first();
                    $levelName = '-';
                    if ($lastApproval && $lastApproval->approvalStep && $lastApproval->approvalStep->level) {
                      $levelName = $lastApproval->approvalStep->level->name;
                    }
                  @endphp
                  Accepted By {{ $levelName }}
                  @break
                  @case(4)
                  Accepted
                  @break
                  @case(5)
                  Need Revision or Rejected
                  @break
                  @default
                  Draft
                  @endswitch
                </td>
                <td class="white-space-nowrap">
                  @can('risk_register_view')
                  <a href="{{ route('risk-register-ap.view', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="View">
                    <span class="bx bx-show-alt"></span>
                  </a>
                  @endcan
                  @include('risk-register-ap._table_action', ['item' => $item])
                </td>
              </tr>
              @endforeach
              @php
              if (!isset($index)) {
              $index = 0;
              }
              @endphp
            </tbody>
          </table>
        </div>

        {{-- Tampilkan data batch notes jika ada --}}
        @if(isset($batchNotes) && $batchNotes)
        <div class="alert alert-warning mb-3">
          <strong>Catatan Perbaikan:</strong> {{ $batchNotes->notes }}
        </div>
        @endif

        @if(isset($pending_risk) && $pending_risk > 0)
        <div class="alert alert-info mb-3">
          <strong>Informasi:</strong> Terdapat {{ $pending_risk }} risiko yang menunggu verifikasi/revisi.
        </div>
        @endif
        {{-- Informasi Average Eksposure Risiko Unit --}}
        @if($status == 3 && isset($avgQuantitativeExposure))
        <div class="alert alert-primary mb-3">
          <strong>Informasi:</strong> Rata-rata eksposur risiko kuantitatif: Rp {{ number_format($avgQuantitativeExposure, 0, ',', '.') }}
        </div>
        @endif

        @can('risk_register_send')
        <form id="send-form" action="{{ route('risk-register-ap.send') }}" method="POST" class="d-inline-block">
          @csrf
          <input type="hidden" name="periode_id" value="{{ $selectedPeriode->id ?? '' }}">
          @if($status == 5 && ($step_order == 0 || $step_order == null))
              <input type="hidden" name="unit_id" value="{{ $unitId }}">
              <button id="revise-button" class="btn btn-submit btn-arrow-right">Kirim Perbaikan Risiko</button>
          @else
            <div style="display: none;">
              levelId: {{ $levelId }}, status: {{ $status }}, tipe status: {{ gettype($status) }}<br>
              kondisi 1: {{ ($levelId > 1 && $status == 1) ? 'true' : 'false' }}<br>
              kondisi 2: {{ ($levelId > 1 && intval($status) === 1) ? 'true' : 'false' }}<br>
              kondisi lengkap: {{ ($dataBatch && ($dataBatch->step_verification ?? 0) != $step_order) || (isset($pending_risk) && $pending_risk > 0) || ($levelId > 1 && intval($status) === 1) ? 'true' : 'false' }}
            </div>
            @if($status==3 && ($step_order>=$min_verification))
            <input type="hidden" name="send_type" value="mainrisk">
            <input type="hidden" name="unit_id" value="{{ $unitId }}">
            <button id="accept-button" class="btn btn-submit btn-arrow-right">Konfirmasi Risiko Utama</button>
            @elseif($status==6 && ($step_order>=$min_verification))
            <input type="hidden" name="send_type" value="corporate-risk">
            <button id="accept-button" class="btn btn-submit btn-arrow-right">Atur Risiko Korporat</button>
            @else
            <input type="hidden" name="unit_id" value="{{ $unitId }}">
            <button id="send-button" class="btn btn-submit btn-arrow-right" {{ ($dataBatch && ($dataBatch->step_verification ?? 0) != $step_order) || (isset($pending_risk) && $pending_risk > 0) || ($levelId > 1 && $status == 1) || $status==5 ? 'disabled' : '' }}>Kirim Risiko</button>
            @endif
          @endif
        </form>
        @endcan
      </div>
    </div>
  </div>
</div>
<!-- Modal Verifikasi Risiko (Single Modal) -->
<div class="modal fade" id="modalVerifikasiRisiko" tabindex="-1" aria-labelledby="verifikasiRisikoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verifikasiRisikoLabel">Verifikasi Risiko</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <h6 id="modal-peristiwa-risiko">Peristiwa Risiko: </h6>
          <p id="modal-deskripsi-risiko"></p>
        </div>
        <form id="form-verifikasi" action="" method="POST">
          @csrf
          <div class="mb-3">
            <label for="catatan-verifikasi" class="form-label">Catatan Verifikasi</label>
            <textarea class="form-control" id="catatan-verifikasi" name="catatan_verifikasi" rows="4" placeholder="Masukkan catatan verifikasi..."></textarea>
          </div>
          <input type="hidden" name="status_verifikasi" id="status-verifikasi" value="">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="btn-terima-risiko">Terima Risiko</button>
        <button type="button" class="btn btn-danger" id="btn-tolak-risiko">Tolak Risiko</button>
        <button type="button" class="btn btn-muted" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal Kirim Perbaikan Risiko -->
<div class="modal fade" id="modalKirimPerbaikanRisiko" tabindex="-1" aria-labelledby="kirimPerbaikanRisikoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="kirimPerbaikanRisikoLabel">Kirim Perbaikan Risiko</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-kirim-perbaikan" action="{{ route('risk-register-ap.send') }}" method="POST">
          @csrf
          <input type="hidden" name="periode_id" value="{{ $selectedPeriode->id ?? '' }}">
          <input type="hidden" name="unit_id" value="{{ $unitId }}">
          <input type="hidden" name="send_type" value="rev">
          <div class="mb-3">
            <label for="catatan-perbaikan" class="form-label">Catatan Perbaikan</label>
            <textarea class="form-control" id="catatan-perbaikan" name="catatan_perbaikan" rows="4" placeholder="Masukkan catatan perbaikan..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-submit" id="btn-kirim-perbaikan">Kirim Perbaikan</button>
        <button type="button" class="btn btn-muted" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script>
// Fungsi untuk menampilkan modal verifikasi dengan data risiko yang sesuai
function showVerifikasiModal(id, peristiwaRisiko, deskripsiRisiko) {
  // Set data risiko ke dalam modal
  document.getElementById('modal-peristiwa-risiko').textContent = 'Peristiwa Risiko: ' + peristiwaRisiko;
  document.getElementById('modal-deskripsi-risiko').textContent = deskripsiRisiko;

  // Set action form dengan ID risiko yang dipilih
  const form = document.getElementById('form-verifikasi');
  form.action = '{{ url("risk-register-ap") }}/' + id + '/verifikasi';

  // Reset form
  form.reset();
  document.getElementById('status-verifikasi').value = '';

  // Tampilkan modal
  const modal = new bootstrap.Modal(document.getElementById('modalVerifikasiRisiko'));
  modal.show();

  // Set event listener untuk tombol terima dan tolak
  document.getElementById('btn-terima-risiko').onclick = function() {
    submitVerifikasi(id, 'terima');
  };

  document.getElementById('btn-tolak-risiko').onclick = function() {
    submitVerifikasi(id, 'tolak');
  };
}

// Fungsi untuk submit verifikasi
function submitVerifikasi(id, status) {
  // Ambil form verifikasi
  const form = document.getElementById('form-verifikasi');
  const statusInput = document.getElementById('status-verifikasi');
  const catatanInput = document.getElementById('catatan-verifikasi');

  // Validasi catatan verifikasi
  if (!catatanInput.value.trim()) {
    Swal.fire({
      title: 'Peringatan',
      text: 'Catatan verifikasi tidak boleh kosong',
      icon: 'warning',
      confirmButtonText: 'OK'
    });
    return;
  }

  // Set nilai status verifikasi
  statusInput.value = status;

  // Konfirmasi dengan SweetAlert
  Swal.fire({
    title: status === 'terima' ? 'Terima Risiko?' : 'Kembalikan Risiko?',
    text: status === 'terima' ? 'Risiko akan diverifikasi dan diterima' : 'Risiko akan dikembalikan ke status proses',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: status === 'terima' ? 'Ya, Terima' : 'Ya, Kembalikan',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      // Submit form jika dikonfirmasi
      form.submit();
    }
  });
}
</script>
<script>
const table = new DataTable('#example');
table.on('mouseenter', 'td', function() {
  let colIdx = table.cell(this).index().column;

  table
    .cells()
    .nodes()
    .each((el) => el.classList.remove('highlight'));

  table
    .column(colIdx)
    .nodes()
    .each((el) => el.classList.add('highlight'));
});

function deleteItem(element) {
  if (confirm('Are you sure you want to delete?')) {
    // Ambil form yang berisi tombol hapus
    const form = element.parentNode;
    // Submit form untuk menghapus item
    form.submit();
  }
}
</script>
<script>
$(document).ready(function() {
  // Inisialisasi DataTable
  const table = $('#example').DataTable();

  // Fungsi untuk menangani perubahan nilai di select unit
  $('#filter-unit').on('change', function() {
    //var unitId = $(this).val(); // Mendapatkan nilai unit yang dipilih
    // Memfilter baris tabel berdasarkan nilai unit yang dipilih pada kolom 'Unit'
    //table.column(1).search(unitId).draw();
    const selectedUnitId = $(this).val();
    const currentUrl = new URL(window.location.href);
    
    // Hapus parameter unit_id jika "Semua Unit" dipilih
    if (selectedUnitId === '') {
        currentUrl.searchParams.delete('unit_id');
    } else {
        currentUrl.searchParams.set('unit_id', selectedUnitId);
    }
    
    // Refresh halaman dengan parameter baru
    window.location.href = currentUrl.toString();
  });

  $('#filter-risk-event').on('change', function() {
    var riskEvent = $(this).val();
    table.column(4).search(riskEvent).draw();
  });

  $('#filter-risk-t2t3').on('change', function() {
    var riskEvent = $(this).val();
    table.column(4).search(riskEvent).draw();
  });

  // Function to handle changes in the risk level filter
  $('#filter-risk-level').on('change', function() {
    var riskLevel = $(this).val();
    table.column(8).search(riskLevel).draw();
  });

  document.querySelector('#send-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Mencegah form submission otomatis

    const status = {{ $status ?? 'null' }};
    const stepOrder = {{ $step_order ?? 'null' }};

    if(status === 6){
      //redirect ke halaman risk corporate
      window.location.href = "{{ route('corporate-risk.index') }}";
    }
    else if(status===3){
      const selectedRisks = [];
      document.querySelectorAll('.select-item:checked').forEach(function(checkbox) {
        selectedRisks.push(checkbox.value);
      });
      
      // Jika tidak ada risiko yang dipilih, tampilkan peringatan
      if(selectedRisks.length === 0) {
        Swal.fire({
          title: "Peringatan",
          text: "Silakan pilih minimal satu risiko untuk dijadikan risiko utama",
          icon: "warning",
        });
        return;
      }

      // Hapus input hidden yang mungkin sudah ada sebelumnya
      document.querySelectorAll('input[name="selected_risks[]"]').forEach(function(input) {
        input.remove();
      });

      // Tambahkan input hidden untuk setiap risiko yang dipilih
      selectedRisks.forEach(function(riskId) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_risks[]';
        input.value = riskId;
        this.appendChild(input);
      }, this);

      Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Terima Risiko Terpilih sebagai Risiko Utama?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, terima risiko!",
        cancelButtonText: "Tidak, batal",
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit(); // Kirim form jika dikonfirmasi
        }
      });
    }
    else if (status === 5 && (stepOrder === 0 || stepOrder === null)) {// Jika status adalah 5 (revisi) dan step_order adalah 0 atau null, tampilkan modal kirim perbaikan
      const modal = new bootstrap.Modal(document.getElementById('modalKirimPerbaikanRisiko'));
      modal.show();
    } else {
      // Jika tidak, tampilkan konfirmasi SweetAlert seperti biasa
      Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Semua Data Risiko akan dikirim untuk dilakukan verifikasi selanjutnya dan anda tidak dapat melakukan penambahan risiko dan edit risiko sementara waktu",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, kirim risiko!",
        cancelButtonText: "Tidak, batal",
      }).then((result) => {
        if (result.isConfirmed) {
          this.submit(); // Kirim form jika dikonfirmasi
        }
      });
    }
  });

  // Select/Deselect all checkboxes
  $('#select-all').on('click', function() {
    var rows = table.rows({
      'search': 'applied'
    }).nodes();
    $('input[type="checkbox"]', rows).prop('checked', this.checked);
  });

  // Handle individual row selection
  $('#example tbody').on('change', 'input[type="checkbox"]', function() {
    if (!this.checked) {
      var el = $('#select-all').get(0);
      if (el && el.checked && ('indeterminate' in el)) {
        el.indeterminate = true;
      }
    }
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const status = {{ $status ?? 'null' }}; // Menggunakan nilai status dari PHP
  const sendButton = document.getElementById('send-button');

  //if (status === 1 || status === null) {
    //// Enable button if status is 1 (proses) or no DataBatch exists
    //sendButton.disabled = false;
  //}

  const addRiskButton = document.getElementById('add-risk-button');

  addRiskButton.addEventListener('click', function(event) {
    // Cek apakah status berbeda dari 1
    if (status !== null && status != 1 && status != 5) {
      event.preventDefault(); // Mencegah link dibuka
      alert('Belum bisa menambah data risiko karena sedang dalam proses konfirmasi.');
    }
    // Jika status == 1 atau status null, link akan berjalan normal dan mengarah ke halaman buat risiko.
  });
});

// Tambahkan event listener untuk tombol kirim perbaikan
document.addEventListener('DOMContentLoaded', function() {
  // ... existing code ...

  // Event listener untuk tombol kirim perbaikan di dalam modal
  const btnKirimPerbaikan = document.getElementById('btn-kirim-perbaikan');
  if (btnKirimPerbaikan) {
    btnKirimPerbaikan.addEventListener('click', function() {
      const catatanInput = document.getElementById('catatan-perbaikan');

      // Validasi catatan perbaikan
      if (!catatanInput.value.trim()) {
        Swal.fire({
          title: 'Peringatan',
          text: 'Catatan perbaikan tidak boleh kosong',
          icon: 'warning',
          confirmButtonText: 'OK'
        });
        return;
      }

      // Konfirmasi dengan SweetAlert
      Swal.fire({
        title: 'Kirim Perbaikan Risiko?',
        text: 'Perbaikan risiko akan dikirim',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          // Submit form jika dikonfirmasi
          document.getElementById('form-kirim-perbaikan').submit();
        }
      });
    });
  }
});
</script>
@endsection
