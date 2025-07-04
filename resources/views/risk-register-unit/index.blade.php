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
          <h2>Risk Register Divisi</h2>
          @if(isset($selectedPeriode)) 
          <div class="ff-preheading">Periode: {{ $selectedPeriode->tahun }}</div> 
          @endif
        </div>
      </div>
      <div class="card-body dt-header-true">
        <div id="tableExample3">
          <div class="row g-2 mb-1">
            @can('risk_register_all_unit')
            <div class="col-4 col-sm-2">
              <label for="filter-unit" class="form-label d-none">Unit</label>
              <select id="filter-unit" class="form-select select2">
                <option value="" selected>Unit</option>
                @foreach($unit as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            @endcan
            @can('risk_register_child_unit')
            <div class="col-4 col-sm-2">
              <label for="filter-unit" class="form-label d-none">Unit</label>
              <select id="filter-unit" class="form-select select2">
                <option value="" selected>Unit</option>
                @foreach($unitChild as $id => $name)
                <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
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
            <div class="col-auto ms-auto">
            @php
                // diasumsikan di view Anda ada $selectedPeriode
                $pid = $selectedPeriode->id;
            @endphp  
              <a id="add-risk-button" href="{{ route('risk-register-unit.create', ['pid' => $pid]) }}" type="button"
                class="btn btn-outline-info btn-sm d-flex flex-center" data-bs-toggle="tooltip"
                data-bs-title="Tambah Risiko">
                <i class="bx bx-plus"></i>
                <span class="ms-1">Tambah Risiko</span>
              </a>
            </div>
          </div>
          <table class="table dataTable" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="unit">Unit</th>
                <th class="sort" data-sort="unit_type">Sasaran</th>
                <th class="sort mw-20r" data-sort="kategori_jenis_risiko">T2 & T3 KBUMN</th>
                <th class="sort mw-10r" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                <th class="sort mw-10r" data-sort="deskripsi_peristiwa_risiko">Deskripsi Peristiwa Risiko</th>
                <th class="sort mw-10r" data-sort="kontrol_eksisting">Jenis Kontrol Eksisting</th>
                <th class="sort mw-15r" data-sort="waktu_terpapar">Waktu Terpapar</th>
                <th class="sort" data-sort="status">Status</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($risiko as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="unit">{{ $item->unit->name ?? '-' }}</td>
                <td class="unit_type">{{ $item->target_capaian_kinerja ?? '-' }}</td>
                <td class="kategori_jenis_risiko">{{ $item->kategoriRisiko->title ?? '-' }} - {{ $item->jenisRisiko->title ?? '-' }}</td>
                <td class="peristiwa_risiko">{{ $item->peristiwa_risiko ?? '-' }}</td>
                <td class="deskripsi_peristiwa_risiko">{{ $item->deskripsi_peristiwa_risiko }}</td>
                <td class="kontrol_eksisting">{{ $item->jenisKontrolEksisting->jenis_kontrol ?? '-' }}</td>
                <td class="waktu_terpapar">
                  {{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_mulai)->format('d/m/Y') }} - 
                  {{ \Carbon\Carbon::parse($item->perkiraan_waktu_terpapar_risiko_akhir)->format('d/m/Y') }}
                </td>
                <td class="status">
                  @switch($item->status ?? 0)
                  @case(1)
                  Proses
                  @break
                  @case(2)
                  Dikirim
                  @break
                  @case(3)
                  Tunggu Verifikasi
                  @break
                  @case(4)
                  Terverifikasi
                  @break
                  @default
                  -
                  @endswitch
                </td>
                <td class="white-space-nowrap">
                  {{--
                  @can('risk_register_view')
                  <a href="{{ route('risk-register.view', $item) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="View">
                    <span class="bx bx-show-alt"></span>
                  </a>
                  @endcan
                  --}}
                  @can('risk_register_edit')
                  <a href="{{ route('risk-register-unit.edit', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Edit"><span class="bx bx-message-square-edit"></span></a>
                  @endcan
                  <!-- Tambahkan tombol Analisa Risiko di sini -->
                  @can('risk_register_edit')
                  <a href="{{ route('risk-register-unit.analisa', $item->id) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Analisa Risiko">
                    <span class="bx bx-analyse text-warning"></span>
                  </a>
                  @endcan
                  @can('risk_register_delete')
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $item->id }}">
                    <span class="bx bx-trash" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $item->id;
                  $innerItemText = $item->peristiwaRisiko?->title ?: '-';
                  $formAction = route('risk-register-unit.destroy', $item->id);
                  @endphp
                  @include('partials.modal-delete-alert')
                  @endcan
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
        {{-- 
        @can('risk_register_send')
        <form id="send-form" action="{{ route('risk-register.send') }}" method="POST" class="d-inline-block">
          @csrf
          @if ($status !== null && $status != 1)
          <div class="alert alert-warning mb-2">
            Tidak bisa mengirim risiko karena sedang dalam proses konfirmasi Risk Champion.
          </div>
          @endif
          <button id="send-button" class="btn btn-submit btn-arrow-right" {{ $status !== 1 ? 'disabled' : '' }}>Kirim Risiko</button>
        </form>
        @endcan
        --}}
      </div>
    </div>
  </div>
</div>

@endsection
@section('scripts')
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
    var unitId = $(this).val(); // Mendapatkan nilai unit yang dipilih
    // Memfilter baris tabel berdasarkan nilai unit yang dipilih pada kolom 'Unit'
    table.column(1).search(unitId).draw();
  });

  $('#filter-risk-event').on('change', function() {
    var riskEvent = $(this).val();
    table.column(4).search(riskEvent).draw();
  });

  $('#filter-risk-t2t3').on('change', function() {
    var riskEvent = $(this).val();
    table.column(3).search(riskEvent).draw();
  });

  // Function to handle changes in the risk level filter
  $('#filter-risk-level').on('change', function() {
    var riskLevel = $(this).val();
    table.column(8).search(riskLevel).draw();
  });

  document.querySelector('#send-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Mencegah form submission otomatis

    Swal.fire({
      title: "Apakah Anda yakin?",
      text: "Semua Data Risiko akan dikirim ke Risk Champion dan anda tidak dapat melakukan penambahan risiko dan edit risiko sementara waktu",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, kirim risiko!",
      cancelButtonText: "Tidak, batal",
    }).then((result) => {
      if (result.isConfirmed) {
        this.submit(); // Kirim form jika dikonfirmasi
      }
    });
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const status = {{ $status ?? 'null' }}; // Menggunakan nilai status dari PHP
  const sendButton = document.getElementById('send-button');

  if (status === 1 || status === null) {
    // Enable button if status is 1 (proses) or no DataBatch exists
    sendButton.disabled = false;
  }

  const addRiskButton = document.getElementById('add-risk-button');

  addRiskButton.addEventListener('click', function(event) {
    // Cek apakah status berbeda dari 1
    if (status !== null && status != 1) {
      event.preventDefault(); // Mencegah link dibuka
      alert('Belum bisa menambah data risiko karena sedang dalam proses konfirmasi.');
    }
    // Jika status == 1 atau status null, link akan berjalan normal dan mengarah ke halaman buat risiko.
  });
});
</script>
@endsection