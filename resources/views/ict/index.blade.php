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
          <h2>ICT Plan</h2>
        </div>
      </div>
      <div class="card-body dt-header-true">
        <div id="tableExample3">
          <div class="row g-2 mb-1">
            @can('ict_input')
            <div class="col-auto ms-auto">
              <a id="add-ict-plan-button" href="{{ route('ict.create') }}" type="button"
                class="btn btn-outline-info btn-sm d-flex flex-center" data-bs-toggle="tooltip"
                data-bs-title="Tambah ICT Plan">
                <i class="bx bx-plus"></i>
                <span class="ms-1">Tambah ICT Plan</span>
              </a>
            </div>
            @endcan
          </div>
          <table class="table dataTable" id="example" data-paging="true" data-info="true" data-filter="true">
            <thead>
              <tr>
                <th class="white-space-nowrap">#</th>
                <th class="sort" data-sort="sasaran_bumn">Sasaran BUMN</th>
                <th class="sort" data-sort="peristiwa_risiko">Peristiwa Risiko</th>
                <th class="sort" data-sort="lokasi_risiko">Lokasi Risiko</th>
                <th class="sort" data-sort="business_process">Business Process</th>
                <th class="sort" data-sort="key_controls">Key Control</th>
                <th class="sort" data-sort="metode_pengujian">Metode Pengujian</th>
                <th class="sort" data-sort="status">Status</th>
                <th class="no-sort white-space-nowrap" data-sort="action">Action</th>
              </tr>
            </thead>
            <tbody class="list" id="bulk-select-body">
              @foreach ($data as $index => $item)
              <tr>
                <td class="index-number">{{ $index + 1 }}</td>
                <td class="sasaran_bumn">{{ $item['sasaran_bumn'] }}</td>
                <td class="peristiwa_risiko">{{ $item['peristiwa_risiko'] }}</td>
                <td class="lokasi_risiko">{{ $item['lokasi_risiko'] }}</td>
                <td class="business_process">{{ $item['business_process'] }}</td>
                <td class="key_controls">{{ $item['key_controls'] }}</td>
                <td class="metode_pengujian">{{ $item['metode_pengujian'] }}</td>
                <td class="status">
                  @if($item['status'] == 'draft')
                    <div class="badge bg-info">Draft</div>
                  @elseif($item['status'] == 'pending_approval')
                    <div class="badge bg-warning">Pending Approval</div>
                  @elseif($item['status'] == 'approved')
                    <div class="badge bg-success">Approved</div>
                  @elseif($item['status'] == 'rejected')
                    {{-- <div class="badge bg-danger" data-bs-toggle="tooltip" title="Alasan: {{ $item['rejection_reason'] }}">
                      Rejected
                    </div> --}}
                    <button 
                      type="button" 
                      class="badge bg-danger btn-sm border-0" 
                      data-bs-toggle="modal" 
                      data-bs-target="#rejectionReasonModal" 
                      data-reason="{{ $item['rejection_reason'] }}"
                    >
                      Rejected
                      <span class="bx bx-info-circle ms-1"></span>
                    </button>
                  @endif
                </td>
                <td class="white-space-nowrap">
                  <a href="{{ route('ict.show', $item['id']) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="View">
                    <span class="bx bx-show-alt"></span>
                  </a>
                  {{--
                  <a href="{{ route('ict.edit', $item['id']) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Edit">
                    <span class="bx bx-message-square-edit"></span>
                  </a>
                  --}}
                  <a href="{{ route('ict.testing', $item['id']) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Testing">
                    <span class="bx bx-test-tube text-warning"></span>
                  </a>
                  <a href="{{ route('ict.report', $item['id']) }}" class="btn-input-icon" data-bs-toggle="tooltip"
                    title="Report">
                    <span class="bx bx-file text-success"></span>
                  </a>
                  <button type="button" class="btn-input-icon" data-bs-toggle="modal"
                    data-bs-target="#modalDelete{{ $item['id'] }}">
                    <span class="bx bx-trash text-danger" data-bs-toggle="tooltip" title="Delete"></span>
                  </button>
                  @php
                  $itemId = $item['id'];
                  $innerItemText = $item['sasaran_bumn'];
                  $formAction = route('ict.destroy', $item['id']);
                  @endphp
                  @include('partials.modal-delete-alert')
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
        {{-- Tombol Aksi di Kanan --}}
        <div class="col-auto ms-auto">
          <div class="d-block gap-2">
            {{-- Tombol Kirim untuk ict_input --}}
            @can('ict_input')
              @if(($hasDrafts || $hasRejected) && !$hasPendingApproval)
              <div class="d-block">
                <small class="text-muted d-block mb-2">
                  Kirim semua data 'Draft' & 'Rejected' untuk memulai proses verifikasi.
                </small>
                <form id="submit-all-form" action="{{ route('ict.submitAll') }}" method="POST" class="d-block">
                  @csrf
                  <button type="button" id="submit-all-btn" class="btn btn-primary btn-sm">
                    Kirim ICT <span class="bx bx-send ms-1"></span>
                  </button>
                </form>
              </div>
              @endif
            @endcan

            {{-- Tombol Approve & Reject untuk ict_approval --}}
            @can('ict_approval')
              @if($hasPendingApproval)
                <small class="text-muted d-block mb-2">
                  Terdapat pengajuan yang memerlukan tindakan verifikasi Anda.
                </small>
                <div class="d-block gap-2">
                  <form id="approve-all-form" action="{{ route('ict.approveAll') }}" method="POST" class="d-inline">
                      @csrf
                      <button type="button" id="approve-all-btn" class="btn btn-success btn-sm">
                        <span class="bx bx-check-circle me-1"></span> Approve
                      </button>
                  </form>
                  <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalReject">
                      <span class="bx bx-x-circle me-1"></span> Reject
                  </button>
              </div>
              @endif
            @endcan
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal untuk Reject --}}
@can('ict_approval')
<div class="modal fade" id="modalReject" tabindex="-1" aria-labelledby="modalRejectLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRejectLabel">Tolak Pengajuan ICT Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('ict.rejectAll') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="rejection_reason" class="form-label">Keterangan Penolakan</label>
            <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4"
              placeholder="Masukkan alasan penolakan untuk semua data ICT yang diajukan..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Tolak Semua</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="rejectionReasonModal" tabindex="-1" aria-labelledby="rejectionReasonModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rejectionReasonModalLabel">
          <i class="bx bx-info-circle me-2 text-danger"></i>Alasan Penolakan
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="rejectionReasonText"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endcan
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

  // Konfirmasi untuk Kirim ICT
  $('#submit-all-btn').on('click', function(e) {
      e.preventDefault();
      Swal.fire({
          title: 'Apakah Anda yakin?',
          text: "Semua data dengan status 'Draft' dan 'Rejected' akan dikirim untuk diverifikasi. Anda tidak dapat mengubah data ini selama proses verifikasi.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Ya, Kirim Semua!',
          cancelButtonText: 'Batal'
      }).then((result) => {
          if (result.isConfirmed) {
              $('#submit-all-form').submit();
          }
      })
  });

  // Konfirmasi untuk Approve ICT
  $('#approve-all-btn').on('click', function(e) {
      e.preventDefault();
      Swal.fire({
          title: 'Apakah Anda yakin?',
          text: "Anda akan menyetujui semua data ICT yang diajukan.",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#28a745',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Ya, Setujui Semua!',
          cancelButtonText: 'Batal'
      }).then((result) => {
          if (result.isConfirmed) {
              $('#approve-all-form').submit();
          }
      })
  });

  const rejectionModal = document.getElementById('rejectionReasonModal');
    if (rejectionModal) {
        rejectionModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const reason = button.getAttribute('data-reason');
            const modalBody = rejectionModal.querySelector('#rejectionReasonText');
            modalBody.textContent = reason;
        });
    }
});
</script>
@endsection